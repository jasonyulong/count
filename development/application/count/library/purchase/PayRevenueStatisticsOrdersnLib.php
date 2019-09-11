<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    leo
 */

namespace app\count\library\purchase;


use app\common\model\EbayPartner;
use app\count\model\PayRevenueStatisticsOrdersn;
use app\common\model\PurchasePayType;

class PayRevenueStatisticsOrdersnLib extends PayRevenueStatisticsOrdersn
{
    public function getList($params)
    {
        $params['paytype']   = isset($params['paytype']) && $params['paytype'] ? $params['paytype'] : '';
        $params['day_start'] = isset($params['day_start']) && $params['day_start'] ? $params['day_start'] : date('Y-m-d',strtotime("-1 day"));
        $params['day_end']   = isset($params['day_end']) && $params['day_end'] ? $params['day_end'] : date('Y-m-d',strtotime("-1 day"));
        $params['cguser']    = isset($params['cguser']) && $params['cguser'] ? $params['cguser'] : '';
        $startTime           = strtotime($params['day_start']);
        $endTime             = strtotime($params['day_end'] . ' 23:59:59');
        $params['order_type'] = $params['order_type'] ?? '';
        $orderStr = "purchase_time desc";
        if($params['sortkey'] &&  $params['sort']){
            $orderStr = "{$params['sortkey']} {$params['sort']}";
        }
        $partnerModel = new EbayPartner();
        $map               = [];
        $params['factory'] = $params['factory'] ?? '';
        if ($params['partner_id']) {
            $partner_id = $partnerModel->field('id')->where(['company_name' => trim($params['partner_id'])])->find();
            $map['partner_id'] = $partner_id['id'];
        }
        if (isset($params['order_type']) && $params['order_type']) $map['ordertype'] = trim($params['order_type']);
        if (isset($params['paytype'])  && $params['paytype']) $map['pay_type'] = trim($params['paytype']);
        if (isset($params['cguser'])  && $params['cguser']) $map['cguser'] = trim($params['cguser']);
        $start = ($params['p'] - 1) * $params['ps'];
        if ($params['type'] == 'ordersn') {
            $field = '*,(paid-refound) as real_pay,(revenued+equivalent_change) as total_collected';
            $count = $this->whereTime('purchase_time', 'between', [$startTime, $endTime])->where($map)->count();
            if (isset($params['is_export']) && $params['is_export'] == 1){
                $data  = $this->field($field)->whereTime('purchase_time', 'between', [$startTime, $endTime])->where($map)->order($orderStr)->select();
            }else{
                $data  = $this->field($field)->whereTime('purchase_time', 'between', [$startTime, $endTime])->where($map)->order($orderStr)->limit($start, $params['ps'])->select();
            }
        } elseif ($params['type'] == 'partner') {
            if($params['sortkey'] == 'real_pay' || $params['sortkey'] == 'total_collected'){
                $orderStr = "purchase_time desc";
            }
            $field = 'sum(amount) as amount,sum(paid) as paid,sum(wait_pay) as wait_pay,sum(revenued) as revenued,sum(refound) as refound,partner_id,sum(loss) as loss,sum(equivalent_change) as equivalent_change,sum(collected) as collected,pay_type,sum(ship_fee) as ship_fee';
            $count = $this->whereTime('purchase_time', 'between', [$startTime, $endTime])->where($map)->group("partner_id")->count();
            if (isset($params['is_export']) && $params['is_export'] == 1) {
                $data = $this->field($field)->whereTime('purchase_time', 'between', [$startTime, $endTime])->where($map)->group("partner_id")->order($orderStr)->select();
            }else{
                $data = $this->field($field)->whereTime('purchase_time', 'between', [$startTime, $endTime])->where($map)->group("partner_id")->order($orderStr)->limit($start, $params['ps'])->select();
            }
        }

        $countField = 'sum(amount) as amount,sum(paid) as paid,sum(wait_pay) as wait_pay,sum(revenued) as revenued,sum(refound) as refound,sum(loss) as loss,sum(equivalent_change) as equivalent_change,sum(collected) as collected,sum(ship_fee) as ship_fee';
        $dataCount = $this->field($countField)->whereTime('purchase_time', 'between', [$startTime, $endTime])->where($map)->find();

        $payTypeModel = new PurchasePayType();
        $all_pay_type = $payTypeModel->column('id,payname', 'id');
        foreach ($data as $key => $val) {
            $partnerName                   = $partnerModel->field('company_name')->where(['id' => $val['partner_id']])->find();
            $data[$key]['partnerName']     = $partnerName['company_name'];
            $data[$key]['real_pay']        = number_format($val['paid'] - $val['refound'], 3);
            $data[$key]['total_collected'] = number_format($val['revenued'] + $val['equivalent_change'], 3);
            $data[$key]['paytype']         = $all_pay_type[$val['pay_type']] ?? '';
        }
        $dataArr     = replace_query($data);
        $dataCount     = replace_query($dataCount);
        $totalArr = ['total_amount'=>0,'total_paid'=>0,'total_wait_pay'=>0,'total_revenued'=>0,'total_collected'=>0,'total_real_pay'=>0, 'total_total_collected'=>0,'total_fee'=>0];
        $totalArr['total_amount']          += $dataCount['amount'];
        $totalArr['total_paid']            += $dataCount['paid'];
        $totalArr['total_wait_pay']        += $dataCount['wait_pay'];
        $totalArr['total_revenued']        += $dataCount['revenued'];
        $totalArr['total_collected']       += $dataCount['collected'];
        $totalArr['total_fee']             += $dataCount['ship_fee'];
        $totalArr['total_real_pay']        = number_format($dataCount['paid'] - $dataCount['refound'], 3);
        $totalArr['total_total_collected'] = number_format($dataCount['revenued'] + $dataCount['equivalent_change'], 3);
        $return_data = [
            'data'         => $dataArr,
            'params'       => $params,
            'count'        => $count,
            'total'        => $totalArr,
            'all_pay_type' => $all_pay_type,
        ];
        return $return_data;
    }


    public function getPartnerTrend($params)
    {
        $startTime           = strtotime(date('Y-m-d', strtotime('-30 day')) . ' 00:00:00');
        $endTime             = strtotime(date('Y-m-d') . ' 23:59:59');
        $partner_id          = $params['partner_id'];
        $map['a.partner_id'] = $partner_id;
        
        $fieldT               = "sum(a.amount) as amount,from_unixtime(a.purchase_time, '%Y-%m-%d') as time_date";
        $dataT                = $this->alias('a')->field($fieldT)->whereTime('a.purchase_time', 'between', [$startTime, $endTime])->where($map)->group("time_date")->select();
        $dataTArr             = replace_query($dataT);

        $fieldQ               = "from_unixtime(a.purchase_time, '%Y-%m-%d') as time_date,count(b.id) as qty";
        $dataQ                = $this->alias('a')->field($fieldQ)->whereTime('a.purchase_time', 'between', [$startTime, $endTime])->where($map)
            ->join('erp_pay_revenue_statistics_sku b', 'a.ordersn = b.ordersn', 'left')
            ->group("time_date")->select();
        $dataQArr             = replace_query($dataQ);

        $dataTArr             = array_column($dataTArr, NULL, 'time_date');
        $dataQArr             = array_column($dataQArr, NULL, 'time_date');

        $jsonName            = $this->getDateFromRange($startTime, $endTime);
        $jsonTotal           = [];
        $jsonQty             = [];
        foreach ($jsonName as $val) {
            $jsonTotal[] = $dataTArr[$val]['amount'] ?? 0;
            $jsonQty[]   = $dataQArr[$val]['qty'] ?? 0;
        }

        $partnerModel = new EbayPartner();
        $partnerName  = $partnerModel->field('company_name')->where(['id' => $partner_id])->find();
        $parname      = $partnerName['company_name'];

        $return_data = [
            'jsonName'  => json_encode($jsonName),
            'jsonTotal' => json_encode($jsonTotal),
            'jsonQty'   => json_encode($jsonQty),
            'parname'   => $parname
        ];
        return $return_data;
    }

    function getDateFromRange($startdate, $enddate)
    {
        $days = ($enddate - $startdate) / 86400; // 保存每天日期
        $date = array();
        for ($i = 0; $i <= $days; $i++) {
            $date[] = date('Y-m-d', $startdate + (86400 * $i));
        }
        return $date;
    }
}