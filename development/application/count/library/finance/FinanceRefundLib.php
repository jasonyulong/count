<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    yang
 */

namespace app\count\library\finance;

use app\count\model\FinanceRefund;

class FinanceRefundLib extends FinanceRefund
{
    public function getList($params)
    {
        $map            = [];
        $params['type'] = isset($params['type']) && $params['type'] ? $params['type'] : 'date';
        if ($params['type'] != 'date' && !isset($params['checkDate'])) {
            $params['day_start'] = date('Y-m-d', strtotime('-1 day'));
            $params['day_end']   = date('Y-m-d', strtotime('-1 day'));
            $params['checkDate'] = 'yesterday';
        }
        $params['day_start']   = isset($params['day_start']) && $params['day_start'] ? $params['day_start'] : date('Y-m-d', strtotime('-15 day'));
        $params['day_end']     = isset($params['day_end']) && $params['day_end'] ? $params['day_end'] : date('Y-m-d');
        $params['month_start'] = isset($params['month_start']) && $params['month_start'] ? $params['month_start'] : date('Y-m', strtotime('-1 month'));
        $params['month_end']   = isset($params['month_end']) && $params['month_end'] ? $params['month_end'] : date('Y-m');
        $params['checkDate']   = isset($params['checkDate']) && $params['checkDate'] ? $params['checkDate'] : 'day';

        $params['platform']     = isset($params['platform']) && $params['platform'] ? $params['platform'] : [];
        $params['account']      = isset($params['account']) && $params['account'] ? $params['account'] : [];
        $params['carrier']      = isset($params['carrier']) && $params['carrier'] ? $params['carrier'] : [];
        $params['country']      = isset($params['country']) && $params['country'] ? $params['country'] : [];
        $params['develop_user'] = isset($params['develop_user']) && $params['develop_user'] ? $params['develop_user'] : [];
        $params['sales_user']   = isset($params['sales_user']) && $params['sales_user'] ? $params['sales_user'] : [];

        if ($params['checkDate'] == 'month') {
            $startTime = strtotime($params['month_start'] . "-01");
            $endTime   = strtotime($params['month_end'] . '-01+1 month') - 1;
        } else {
            $startTime = strtotime($params['day_start']);
            $endTime   = strtotime($params['day_end'] . ' 23:59:59');
        }

        if ($params['platform']) $map['platform'] = array('in', $params['platform']);
        if ($params['account']) $map['platform_account'] = array('in', $params['account']);
        if ($params['carrier']) $map['carrier'] = array('in', $params['carrier']);
        if ($params['country']) $map['couny'] = array('in', $params['country']);
        if ($params['develop_user']) $map['develop_user'] = array('in', $params['develop_user']);
        if ($params['sales_user']) $map['sales_user'] = array('in', $params['sales_user']);
        if ($params['type'] == 'date') $group = ($params['checkDate'] == 'month') ? 'month,year' : 'days,month,year';
        if ($params['type'] == 'platform') $group = 'platform';
        if ($params['type'] == 'kfuser') $group = 'develop_user';
        if ($params['type'] == 'seller') $group = 'sales_user';
        if ($params['type'] == 'account') $group = 'platform_account';
        if ($params['type'] == 'trench') $group = 'carrier';
        if ($params['type'] == 'country') $group = 'couny';

        $fieldStr = "{$group},sum(reissue_num) as reissue_num,sum(reissue_total) as reissue_total,sum(return_num) as return_num,sum(return_total) as return_total,sum(refund_num) as refund_num,sum(refund_total) as refund_total,sum(gift_num) as gift_num,sum(gift_total) as gift_total";


        $start = ($params['p'] - 1) * $params['ps'];

        $count = $this->field($fieldStr)->where($map)->whereTime('datetime', 'between', [$startTime, $endTime])->group($group)->count();
        $data  = $this->field($fieldStr)->where($map)->whereTime('datetime', 'between', [$startTime, $endTime])->group($group)->order("datetime desc")->select();

        if (request()->get('debug') == 'sql') {
            echo $this->getLastSql();
            exit;
        }

        $dataArr = replace_query($data);

        $totalArr        = [
            'num'           => 0,
            'total'         => 0,
            'reissue_num'   => 0,
            'reissue_total' => 0,
            'return_num'    => 0,
            'return_total'  => 0,
            'refund_num'    => 0,
            'refund_total'  => 0,
            'gift_num'      => 0,
            'gift_total'    => 0,
            'refund_rate'   => 0
        ];
        $jsonName        = [];
        $jsonNum         = [];
        $jsonTotal       = [];
        $jsonRefundNum   = [];
        $jsonRefundTotal = [];
        foreach ($dataArr as $key => $val) {
            if ($params['checkDate'] != 'month' && $params['type'] == 'date') $dataArr[$key]['date'] = $val['year'] . "-" . $val['month'] . "-" . $val['days'];
            if ($params['checkDate'] == 'month' && $params['type'] == 'date') $dataArr[$key]['date'] = $val['year'] . "-" . $val['month'];
            if (is_array($val)) {
                $dataArr[$key]['total']       = $val['refund_total'] + $val['return_total'] + $val['reissue_total'];
                $dataArr[$key]['num']         = $val['refund_num'] + $val['return_num'] + $val['reissue_num'];
                $dataArr[$key]['refund_rate'] = $val['refund_num'] ? round($val['refund_num'] / $dataArr[$key]['num'], 4) * 100 : '0';
                $totalArr['reissue_num']      += $val['reissue_num'];
                $totalArr['reissue_total']    += $val['reissue_total'];
                $totalArr['return_num']       += $val['return_num'];
                $totalArr['return_total']     += $val['return_total'];
                $totalArr['refund_num']       += $val['refund_num'];
                $totalArr['refund_total']     += $val['refund_total'];
                $totalArr['gift_num']         += $val['gift_num'];
                $totalArr['gift_total']       += $val['gift_total'];
            }
            if (isset($params['model']) && $params['model'] == 'chart') {
                if (!isset($params['type']) || $params['type'] == 'date') {
                    if ($params['checkDate'] == 'month') {
                        $jsonName[] = $val['year'] . '-' . $val['month'];
                    } else {
                        $jsonName[] = $val['month'] . '-' . $val['days'];
                    }
                } elseif ($params['type'] == 'platform') {
                    $jsonName[] = $val['platform'];
                } elseif ($params['type'] == 'kfuser') {
                    $jsonName[] = $val['develop_user'];
                } elseif ($params['type'] == 'seller') {
                    $jsonName[] = $val['sales_user'];
                } elseif ($params['type'] == 'account') {
                    $jsonName[] = $val['platform_account'];
                } elseif ($params['type'] == 'trench') {
                    $jsonName[] = $val['carrier'];
                } elseif ($params['type'] == 'country') {
                    $jsonName[] = $val['couny'];
                }
                if (is_array($val)) {
                    $jsonTotal[]       = round(($val['reissue_total'] + $val['return_total'] + $val['refund_total']), 3);
                    $jsonNum[]         = $val['reissue_num'] + $val['return_num'] + $val['refund_num'];
                    $jsonRefundNum[]   = $val['refund_num'];
                    $jsonRefundTotal[] = $val['refund_total'];
                }
            }
        }

        if (count($dataArr) > 0) {
            $totalArr['refund_rate'] = $totalArr['refund_num'] ? round(($totalArr['refund_num'] / ($totalArr['refund_num'] + $totalArr['return_num'] + $totalArr['reissue_num'])), 4) * 100 : '0';
            $totalArr['total']       = $totalArr['reissue_total'] + $totalArr['return_total'] + $totalArr['refund_total'];
            $totalArr['num']         = $totalArr['reissue_num'] + $totalArr['return_num'] + $totalArr['refund_num'];
        }
        $return_data = [
            'data'            => $dataArr,
            'jsonName'        => json_encode($jsonName),
            'jsonTotal'       => json_encode($jsonTotal),
            'jsonNum'         => json_encode($jsonNum),
            'jsonRefundNum'   => json_encode($jsonRefundNum),
            'jsonRefundTotal' => json_encode($jsonRefundTotal),
            'params'          => $params,
            'total'           => $totalArr,
            'count'           => $count,
        ];

        return $return_data;
    }
}