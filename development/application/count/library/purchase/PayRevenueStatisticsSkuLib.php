<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    leo
 */

namespace app\count\library\purchase;


use app\count\library\sku\SkuLib;
use app\common\model\EbayPartner;
use app\count\model\PayRevenueStatisticsSku;
use app\common\model\PurchasePayType;

class PayRevenueStatisticsSkuLib extends PayRevenueStatisticsSku
{
    public function getList($params)
    {
        $params['paytype'] = isset($params['paytype']) && $params['paytype'] ? $params['paytype'] : '';
        $params['day_start']   = isset($params['day_start']) && $params['day_start'] ? $params['day_start'] : date('Y-m-d',strtotime("-1 day"));
        $params['day_end']     = isset($params['day_end']) && $params['day_end'] ? $params['day_end'] : date('Y-m-d',strtotime("-1 day"));
        $params['cguser']    = isset($params['cguser']) && $params['cguser'] ? $params['cguser'] : '';
        $startTime = strtotime($params['day_start']);
        $endTime   = strtotime($params['day_end'] . ' 23:59:59');

        $orderStr = "purchase_time desc";
        if($params['sortkey'] &&  $params['sort']){
            $orderStr = "{$params['sortkey']} {$params['sort']}";
        }

        $partnerModel = new EbayPartner();
        $map = [];
        $params['factory'] = $params['factory'] ?? '';
        $params['order_type'] = $params['order_type'] ?? '';
        if ($params['partner_id']) {
            $partner_id = $partnerModel->field('id')->where(['company_name' => trim($params['partner_id'])])->find();
            $map['partner_id'] = $partner_id['id'];
        }
        if (isset($params['order_type']) && $params['order_type']) $map['ordertype'] = trim($params['order_type']);
        if (isset($params['paytype'])  && $params['paytype']) $map['pay_type'] = trim($params['paytype']);
        if (isset($params['sku'])  && $params['sku']) $map['sku'] = ['like','%'.trim($params['sku']).'%'];
        if (isset($params['cguser'])  && $params['cguser']) $map['cguser'] = trim($params['cguser']);
        $count = $this->whereTime('purchase_time', 'between', [$startTime, $endTime])->where($map)->count();
        $start = ($params['p'] - 1) * $params['ps'];
        $field = '*,(paid-refound) as real_pay,(revenued+equivalent_change) as total_collected';
        if (isset($params['is_export']) && $params['is_export'] == 1) {
            $data = $this->field($field)->whereTime('purchase_time', 'between', [$startTime, $endTime])->where($map)->order($orderStr)->select();
        }else{
            $data = $this->field($field)->whereTime('purchase_time', 'between', [$startTime, $endTime])->where($map)->order($orderStr)->limit($start, $params['ps'])->select();
        }
        $skuLib    = new SkuLib();
        $store_arr = $skuLib->getStore();
        $payTypeModel = new PurchasePayType();
        $all_pay_type = $payTypeModel->column('id,payname', 'id');
        foreach ($data as $key => $val) {
            $partnerName                   = $partnerModel->field('company_name')->where(['id' => $val['partner_id']])->find();
            $data[$key]['partnerName']     = $partnerName['company_name'];
            $data[$key]['storeName']       = $store_arr[$val['storeid']]['store_name'];
            $data[$key]['real_pay']        = number_format($val['paid'] - $val['refound'], 3);
            $data[$key]['total_collected'] = number_format($val['revenued'] + $val['equivalent_change'], 3);
        }

        $countField = 'sum(qty) as qty,sum(amount) as amount,sum(paid) as paid,sum(wait_pay) as wait_pay,sum(revenued) as revenued,sum(refound) as refound,sum(loss) as loss,sum(equivalent_change) as equivalent_change,sum(collected) as collected';
        $dataCount = $this->field($countField)->whereTime('purchase_time', 'between', [$startTime, $endTime])->where($map)->find();
        $dataCount = replace_query($dataCount);
        $totalArr = ['total_amount'=>0,'total_paid'=>0,'total_wait_pay'=>0,'total_revenued'=>0,'total_collected'=>0,'total_real_pay'=>0, 'total_total_collected'=>0,'total_qty'=>0];
        $totalArr['total_qty']             += $dataCount['qty'];
        $totalArr['total_amount']          += $dataCount['amount'];
        $totalArr['total_paid']            += $dataCount['paid'];
        $totalArr['total_wait_pay']        += $dataCount['wait_pay'];
        $totalArr['total_revenued']        += $dataCount['revenued'];
        $totalArr['total_collected']       += $dataCount['collected'];
        $totalArr['total_real_pay']        = number_format($dataCount['paid'] - $dataCount['refound'], 3);
        $totalArr['total_total_collected'] = number_format($dataCount['revenued'] + $dataCount['equivalent_change'], 3);

        $dataArr = replace_query($data);
        $return_data = [
            'data'      => $dataArr,
            'params'    => $params,
            'count'     => $count,
            'total'     => $totalArr,
            'all_pay_type' => $all_pay_type,
        ];
        return $return_data;
    }
}