<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    leo
 */

namespace app\count\library\purchase;


use app\common\model\EbayCgpaydata;
use app\common\model\EbayEnums;
use app\common\model\EbayPartner;

class CgpaydataLib extends EbayCgpaydata
{
    private $pay_type = array('1' => '采购付款', '2' => '公司自担损失', '3' => '员工自担损失', '4' => '下次抵扣', '5' => '退货退款', '6' => '已使用的抵扣');

    public function getList($params)
    {
        $params['ordersn']    = isset($params['ordersn']) && $params['ordersn'] ? $params['ordersn'] : '';
        $params['cguser']     = isset($params['cguser']) && $params['cguser'] ? $params['cguser'] : '';
        $params['min_money']  = isset($params['min_money']) && $params['min_money'] ? $params['min_money'] : '';
        $params['max_money']  = isset($params['max_money']) && $params['max_money'] ? $params['max_money'] : '';
        $params['day_start']  = isset($params['day_start']) && $params['day_start'] ? $params['day_start'] : date('Y-m-d',strtotime("-1 day"));
        $params['day_end']    = isset($params['day_end']) && $params['day_end'] ? $params['day_end'] : date('Y-m-d',strtotime("-1 day"));
        $params['factory']    = $params['factory'] ?? '';
        $params['order_type'] = $params['order_type'] ?? '';
        $startTime            = strtotime($params['day_start']);
        $endTime              = strtotime($params['day_end'] . ' 23:59:59');

        $orderStr = "a.addtime desc";
        if($params['sortkey'] &&  $params['sort']){
            $orderStr = "{$params['sortkey']} {$params['sort']}";
        }
        $map = [];
        if ($params['factory'] && $params['partner_id']) $map['a.provider_id'] = trim($params['factory']);
        if ($params['cguser']) $map['b.cguser'] = trim($params['cguser']);
        if ($params['ordersn']) $map['a.ordersn'] = trim($params['ordersn']);
        if ($params['order_type']) $map['b.ordertype'] = trim($params['order_type']);
        if ($params['max_money'] && $params['min_money']) $map['a.payamount'] = ['between',[trim($params['min_money']), trim($params['max_money'])]];
        $field = 'a.*,b.cguser,b.shipfee';
        $count = $this->alias('a')->whereTime('a.addtime', 'between', [$startTime, $endTime])->where($map)
            ->join('ebay_cgorder b', 'a.ordersn=b.ordersn')->count();
        $start = ($params['p'] - 1) * $params['ps'];
        if($count < $start){
            $start = 0;$params['p'] = 1;
        }
        if (isset($params['is_export']) && $params['is_export'] == 1) {
            $data = $this->alias('a')->field($field)->whereTime('a.addtime', 'between', [$startTime, $endTime])->where($map)
                ->join('ebay_cgorder b', 'a.ordersn=b.ordersn')
                ->order($orderStr)->select();
        }else{
            $data = $this->alias('a')->field($field)->whereTime('a.addtime', 'between', [$startTime, $endTime])->where($map)
                ->join('ebay_cgorder b', 'a.ordersn=b.ordersn')
                ->order($orderStr)->limit($start, $params['ps'])->select();
        }
        $partnerModel = new EbayPartner();
        $enums        = new EbayEnums();
        $payways      = $enums->where(['type' => 1])->column('id,display', 'id');
        foreach ($data as $key => $val) {
            $partnerName               = $partnerModel->field('company_name')->where(['id' => $val['provider_id']])->find();
            $data[$key]['partnerName'] = $partnerName['company_name'];
            $data[$key]['dateTime']    = date('Y-m-d H:i:s', $val['addtime']);
            $data[$key]['payType']     = $this->pay_type[$val['type']];
            $data[$key]['paywayName']  = $payways[$val['payway']] ?? '';
            $data[$key]['ship_fee']    = round($val['shipfee'],3);
        }

        $dataArr     = replace_query($data);
        $countField = 'sum(a.payamount) as total_payamount,sum(b.shipfee) as total_fee';
        $dataCount = $this->alias('a')->field($countField)->whereTime('a.addtime', 'between', [$startTime, $endTime])->where($map)
            ->join('ebay_cgorder b', 'a.ordersn=b.ordersn')->find();
        $dataCount = replace_query($dataCount);

        $return_data = [
            'data'   => $dataArr,
            'params' => $params,
            'count'  => $count,
            'total'  => $dataCount,
        ];
        return $return_data;
    }
}