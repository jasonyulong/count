<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    yang
 */

namespace app\count\library\transport;


use app\count\model\TransportBill;
use app\count\model\TransportOutlay;

class TransportBillLib extends TransportBill
{
    public function getList($params)
    {
        $map                       = [];
        $params['day_start']       = isset($params['day_start']) && $params['day_start'] ? $params['day_start'] : date('Y-m-d', strtotime('-15 day'));
        $params['day_end']         = isset($params['day_end']) && $params['day_end'] ? $params['day_end'] : date('Y-m-d');
        $params['month_start']     = isset($params['month_start']) && $params['month_start'] ? $params['month_start'] : date('Y-m', strtotime('-1 month'));
        $params['month_end']       = isset($params['month_end']) && $params['month_end'] ? $params['month_end'] : date('Y-m');
        $params['checkDate']       = isset($params['checkDate']) && $params['checkDate'] ? $params['checkDate'] : 'day';
        $params['platform']        = isset($params['platform']) && $params['platform'] ? $params['platform'] : [];
        $params['carrier']         = isset($params['carrier']) && $params['carrier'] ? $params['carrier'] : [];
        $params['carrier_company'] = isset($params['carrier_company']) && $params['carrier_company'] ? $params['carrier_company'] : [];

        if ($params['checkDate'] == 'day') {
            $startTime = strtotime($params['day_start']);
            $endTime   = strtotime($params['day_end'] . ' 23:59:59');
        } elseif ($params['checkDate'] == 'today') {
            $today     = date('Y-m-d');
            $startTime = strtotime($today);
            $endTime   = strtotime($today . " 23:59:59");
        } elseif ($params['checkDate'] == 'yesterday') {
            $day       = date('Y-m-d', strtotime("- 1 day"));
            $startTime = strtotime($day);
            $endTime   = strtotime($day . " 23:59:59");
        } elseif ($params['checkDate'] == 'recently3day') {
            $day       = date('Y-m-d', strtotime("- 2 day"));
            $startTime = strtotime($day);
            $today     = date('Y-m-d');
            $endTime   = strtotime($today . " 23:59:59");
        } elseif ($params['checkDate'] == 'month') {
            $startTime = strtotime($params['month_start'] . "-01");
            $endTime   = strtotime($params['month_end'] . '-01+1 month') - 1;
        }
        if ($params['platform']) $map['platform'] = array('in', $params['platform']);
        if ($params['carrier']) $map['carrier'] = array('in', $params['carrier']);
        if ($params['carrier_company']) $map['carrier_company'] = array('in', $params['carrier_company']);

        if ($params['type'] == 'platform') {
            $group = 'platform';
            if (!isset($map['platform'])) $map['platform'] = ['neq', ''];
        }
        if ($params['type'] == 'company') {
            $group = 'carrier_company';
            if (!isset($map['carrier_company'])) $map['carrier_company'] = ['neq', ''];
        }
        if ($params['type'] == 'carrier') {
            $group = 'carrier';
            if (!isset($map['carrier'])) $map['carrier'] = ['neq', ''];
        }

        if ($params['type'] == 'date') $group = ($params['checkDate'] == 'month') ? 'month,year' : 'days,month,year';
        $fieldStr = "$group,sum(finish_orders) as finish_orders,sum(finish_money) as finish_money,sum(billing_orders) as billing_orders,sum(billing_money) as billing_money,sum(bepaid_orders) as bepaid_orders,sum(bepaid_money) as bepaid_money,
        sum(finish_weight) as finish_weight,sum(bepaid_weight) as bepaid_weight";

        $start = ($params['p'] - 1) * $params['ps'];
        $count = $this->where($map)->field($fieldStr)->whereTime('datetime', 'between', [$startTime, $endTime])->group($group)->count();
        $data  = $this->where($map)->field($fieldStr)->whereTime('datetime', 'between', [$startTime, $endTime])->order('datetime asc')->group($group)->limit($start, $params['ps'])->order('finish_orders desc')->select()->toArray();

        ksort($data);
        if (request()->get('debug') == 'sql') {
            echo $this->getLastSql();
            print_r($data);
        }
        $groupArr = explode(',', $group);

        //获取金额数据
        if ($group != 'platform' && $group != 'carrier' && $group != 'carrier_company') {
            $TransportOutlay = new TransportOutlay();
            $billField       = "$group,sum(apply_into) as apply_into,sum(apply_pay) as apply_pay,sum(wait_pay) as wait_pay,sum(chase_money) as chase_money";
            $billData        = $TransportOutlay->where($map)->field($billField)->whereTime('datetime', 'between', [$startTime, $endTime])->order('datetime asc')->group($group)->limit($start, $params['ps'])->select()->toArray();

            $bill = [];
            foreach ($billData as $val) {
                $groupStr = '';
                foreach ($val as $k => $v) {
                    if (in_array($k, $groupArr)) {
                        $groupStr .= $v;
                    }
                }
                $md5        = md5($groupStr);
                $bill[$md5] = $val;
            }
        }

        //组装数据
        foreach ($data as $key => $val) {
            $groupStr = '';
            foreach ($val as $k => $v) {
                if (in_array($k, $groupArr)) {
                    $groupStr .= $v;
                }
            }
            $md5 = md5($groupStr);
            if (isset($bill[$md5])) {
                $data[$key]['apply_into']  = $bill[$md5]['apply_into'];
                $data[$key]['apply_pay']   = $bill[$md5]['apply_pay'];
                $data[$key]['wait_pay']    = $bill[$md5]['wait_pay'];
                $data[$key]['chase_money'] = $bill[$md5]['chase_money'];
            } else {
                $data[$key]['apply_into']  = 0;
                $data[$key]['apply_pay']   = 0;
                $data[$key]['wait_pay']    = 0;
                $data[$key]['chase_money'] = 0;
            }
            if (!isset($params['type']) || $params['type'] == 'date') {
                if (!is_array($val)) continue;
                if ($params['checkDate'] == 'month') {
                    $data[$key]['one'] = $val['year'] . '-' . $val['month'];
                } else {
                    $data[$key]['one'] = $val['year'] . '-' . $val['month'] . '-' . $val['days'];
                }
            } else {
                $data[$key]['avg_weight'] = $val['finish_orders'] > 0 ? round($val['finish_weight'] / $val['finish_orders'], 3) : 0;
            }
            if (!isset($params['type']) || $params['type'] == 'platform') $data[$key]['one'] = $val['platform'];
            if (!isset($params['type']) || $params['type'] == 'company') $data[$key]['one'] = $val['carrier_company'];
            if (!isset($params['type']) || $params['type'] == 'carrier') $data[$key]['one'] = $val['carrier'];
        }

        $totalArr = [
            'total_finish_orders'  => 0,
            'total_finish_money'   => 0,
            'total_billing_orders' => 0,
            'total_billing_money'  => 0,
            'total_bepaid_orders'  => 0,
            'total_bepaid_money'   => 0,
            'total_apply_into'     => 0,
            'total_apply_pay'      => 0,
            'total_wait_pay'       => 0,
            'total_chase_money'    => 0,
            'total_finish_weight'  => 0,
            'total_bepaid_weight'  => 0,
            'total_avg_weight'     => 0
        ];
        foreach ($data as $key => $val) {
            if (!is_array($val)) continue;
            $totalArr['total_finish_orders']  += $val['finish_orders'];
            $totalArr['total_finish_money']   += $val['finish_money'];
            $totalArr['total_billing_orders'] += $val['billing_orders'];
            $totalArr['total_billing_money']  += $val['billing_money'];
            $totalArr['total_bepaid_orders']  += $val['bepaid_orders'];
            $totalArr['total_bepaid_money']   += $val['bepaid_money'];
            $totalArr['total_apply_into']     += $val['apply_into']; //申请充值
            $totalArr['total_apply_pay']      += $val['apply_pay'];  //申请支付
            $totalArr['total_wait_pay']       += $val['wait_pay'];   //等待支付
            $totalArr['total_chase_money']    += $val['chase_money']; //追款金额
            $totalArr['total_finish_weight']  += $val['finish_weight'];
            $totalArr['total_bepaid_weight']  += $val['bepaid_weight'];
            if (isset($val['avg_weight'])) $totalArr['total_avg_weight'] += $val['avg_weight'];
        }
        $return_data = [
            'data'   => $data,
            'params' => $params,
            'total'  => $totalArr,
            'count'  => $count,
        ];

        return $return_data;
    }
}