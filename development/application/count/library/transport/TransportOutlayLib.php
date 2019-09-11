<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/6
 * Time: 16:24
 */

namespace app\count\library\transport;


use app\count\model\TransportOutlay;

class TransportOutlayLib extends TransportOutlay
{
    public function getList($params)
    {
        $map                       = [];
        $params['day_start']       = isset($params['day_start']) && $params['day_start'] ? $params['day_start'] : date('Y-m-d', strtotime('-15 day'));
        $params['day_end']         = isset($params['day_end']) && $params['day_end'] ? $params['day_end'] : date('Y-m-d');
        $params['month_start']     = isset($params['month_start']) && $params['month_start'] ? $params['month_start'] : date('Y-m', strtotime('-1 month'));
        $params['month_end']       = isset($params['month_end']) && $params['month_end'] ? $params['month_end'] : date('Y-m');
        $params['checkDate']       = isset($params['checkDate']) && $params['checkDate'] ? $params['checkDate'] : 'day';
        $params['carrier_company'] = isset($params['carrier_company']) && $params['carrier_company'] ? $params['carrier_company'] : [];

        //时间查询处理
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

        if ($params['carrier_company']) $map['carrier_company'] = array('in', $params['carrier_company']);
        if ($params['type'] == 'company') {
            $group = 'carrier_company';
            if (!isset($map['carrier_company'])) $map['carrier_company'] = ['neq', ''];
        }
        if ($params['type'] == 'date') $group = ($params['checkDate'] == 'month') ? 'month,year' : 'days,month,year';
        $fieldStr = "$group,sum(finish_orders) as finish_orders,sum(finish_money) as finish_money,sum(bepaid_orders) as bepaid_orders,sum(bepaid_money) as bepaid_money,sum(apply_into) as apply_into,
        sum(apply_pay) as apply_pay,sum(wait_pay) as wait_pay,sum(finish_pay) as finish_pay,sum(chase_money) as chase_money,sum(apply_end) as apply_end,sum(out_total) as pay_sum";


        $start = ($params['p'] - 1) * $params['ps'];
        $count = $this->where($map)->field($fieldStr)->whereTime('datetime', 'between', [$startTime, $endTime])->group($group)->count();

        $data = $this->where($map)->field($fieldStr)->whereTime('datetime', 'between', [$startTime, $endTime])->order('datetime asc')->group($group)->limit($start, $params['ps'])->select()->toArray();

        //如果是按物流公司查询、获取历史总支付、历史总充值
        if ($params['type'] == 'company') {
            $companyArr  = array_column($data, 'carrier_company');
            $companyData = [];
            if ($companyArr) {
                $query       = ['carrier_company' => ['in', $companyArr]];
                $fieldString = "sum(finish_pay) as total_pay,sum(apply_end) as total_apply,carrier_company";
                $company     = $this->field($fieldString)->where($query)->group('carrier_company')->select()->toArray();
                foreach ($company as $key => $val) {
                    $companyData[$val['carrier_company']] = $val;
                }
            }
        }

        //循环处理数据
        foreach ($data as $key => $val) {
            if (!isset($params['type']) || $params['type'] == 'date') {
                if (!is_array($val)) continue;
                if ($params['checkDate'] == 'month') {
                    $data[$key]['one'] = $val['year'] . '-' . $val['month'];
                } else {
                    $data[$key]['one'] = $val['year'] . '-' . $val['month'] . '-' . $val['days'];
                }
            }
            if (!isset($params['type']) || $params['type'] == 'company') {
                $data[$key]['one']         = $val['carrier_company'];
                $data[$key]['total_pay']   = isset($companyData[$val['carrier_company']]) ? $companyData[$val['carrier_company']]['total_pay'] : 0;
                $data[$key]['total_apply'] = isset($companyData[$val['carrier_company']]) ? $companyData[$val['carrier_company']]['total_apply'] : 0;
            }
        }

        //循环统计数据
        $totalArr = [
            'total_finish_orders' => 0,
            'total_finish_money'  => 0,
            'total_bepaid_orders' => 0,
            'total_bepaid_money'  => 0,
            'total_apply_into'    => 0,
            'total_apply_end'     => 0,
            'total_apply_pay'     => 0,
            'total_finish_pay'    => 0,
            'total_wait_pay'      => 0,
            'total_chase_money'   => 0,
            'total_pay_sum'       => 0,
            'pay_total'           => 0,
            'apply_total'         => 0,
        ];
        foreach ($data as $key => $val) {
            if (!is_array($val)) continue;
            $totalArr['total_finish_orders'] += $val['finish_orders'];
            $totalArr['total_finish_money']  += $val['finish_money'];
            $totalArr['total_bepaid_orders'] += $val['bepaid_orders'];
            $totalArr['total_bepaid_money']  += $val['bepaid_money'];
            $totalArr['total_apply_into']    += $val['apply_into'];
            $totalArr['total_apply_pay']     += $val['apply_pay'];
            $totalArr['total_wait_pay']      += $val['wait_pay'];
            $totalArr['total_finish_pay']    += $val['finish_pay'];
            $totalArr['total_chase_money']   += $val['chase_money'];
            $totalArr['total_pay_sum']       += $val['pay_sum'];
            $totalArr['total_apply_end']     += $val['apply_end'];
            if ($params['type'] == 'company') {
                $totalArr['pay_total']   += $val['total_pay'];
                $totalArr['apply_total'] += $val['total_apply'];
            }
        }

        //返回信息
        $return_data = [
            'data'   => $data,
            'params' => $params,
            'total'  => $totalArr,
            'count'  => $count,
        ];
        return $return_data;
    }
}