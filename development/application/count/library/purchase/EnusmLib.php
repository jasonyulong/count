<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    leo
 */

namespace app\count\library\purchase;

use app\common\model\EbayCgpaydata;
use app\common\model\EbayEnums;

class EnusmLib extends EbayEnums
{
    public function getList($params)
    {
        $params['pay_type']  = isset($params['pay_type']) && $params['pay_type'] ? $params['pay_type'] : '';
        $params['account']   = isset($params['account']) && $params['account'] ? $params['account'] : '';
        $params['min_money'] = isset($params['min_money']) && $params['min_money'] ? $params['min_money'] : '';
        $params['max_money'] = isset($params['max_money']) && $params['max_money'] ? $params['max_money'] : '';
        $params['day_start'] = isset($params['day_start']) && $params['day_start'] ? $params['day_start'] : date('Y-m-d',strtotime("-1 day"));
        $params['day_end']   = isset($params['day_end']) && $params['day_end'] ? $params['day_end'] : date('Y-m-d',strtotime("-1 day"));

        $startTime = strtotime($params['day_start']);
        $endTime   = strtotime($params['day_end'] . ' 23:59:59');

        $map              = [];
        $map['b.addtime'] = ['between', [$startTime, $endTime]];
        $map['b.type']    = ['in', ['1', '5']];
        if($params['account']) $map['a.display'] = trim($params['account']);
        if($params['pay_type']) $map['b.type'] = trim($params['pay_type']);
        $field            = 'sum(b.payamount) as amount,a.display,a.id,b.type';
        $data             = $this->alias('a')->field($field)->where($map)
            ->join('ebay_cgpaydata b', 'b.payway=a.id')
            ->group('a.id,b.type')
            ->select();
        $dataArr    = replace_query($data);
        $dataNewArr = [];
        foreach ($dataArr as $key => $val) {
            $dataNewArr[$val['display']]['payName'] = $val['display'];
            $dataNewArr[$val['display']]['id']      = $val['id'];
            if ($val['type'] == '1') {
                $dataNewArr[$val['display']]['amount1'] = $val['amount'];
            }
            if ($val['type'] == '5') {
                $dataNewArr[$val['display']]['amount5'] = $val['amount'];
            }
        }
        if(($params['max_money'] && $params['min_money'])){
            $m = $params['pay_type'];
            foreach($dataNewArr as $key => $val){
                if ($m) {
                    if($val['amount'.$m] < $params['min_money'] || $val['amount'.$m] > $params['max_money']){
                        unset($dataNewArr[$key]);
                    }
                }else{
                    if($val['amount1'] < $params['min_money'] || $val['amount1'] > $params['max_money'] || $val['amount5'] < $params['min_money'] || $val['amount5'] > $params['max_money']){
                        unset($dataNewArr[$key]);
                    }
                }
            }
        }
        $dataNewArr[] = [
            'id' =>'汇总',
            'payName' => '',
            'amount1' => array_sum(array_column($dataNewArr,'amount1')),
            'amount5' => array_sum(array_column($dataNewArr,'amount5'))
        ];
        unset($params['s']);
        $return_data = [
            'data'   => $dataNewArr,
            'params' => $params,
        ];
        return $return_data;
    }

    public function getDetiledList($params)
    {
        $params['day_start'] = isset($params['day_start']) && $params['day_start'] ? $params['day_start'] : date('Y-m-d');
        $params['day_end']   = isset($params['day_end']) && $params['day_end'] ? $params['day_end'] : date('Y-m-d');
        $startTime = strtotime($params['day_start']);
        $endTime   = strtotime($params['day_end'] . ' 23:59:59');

        $map=[];
        $map['payway'] =  $params['id'];
        $cgpaydataModel = new EbayCgpaydata();
        $map['type']    = ['in', ['1','5']];
        if($params['pay_type']) $map['type'] = trim($params['pay_type']);
        $field = 'type,payamount,addtime,adduser,ordersn';
        $data = $cgpaydataModel->field($field)->whereTime('addtime', 'between', [$startTime, $endTime])->where($map)->select();
        $dataArr    = replace_query($data);
        $return_data = [
            'data'   => $dataArr,
            'params' => $params,
        ];
        return $return_data;
    }
}