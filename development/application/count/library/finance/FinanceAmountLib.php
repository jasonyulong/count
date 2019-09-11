<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    yang
 */

namespace app\count\library\finance;

use app\common\library\ToolsLib;
use app\count\model\FinanceAmount;

/**
 * Class FinanceAmountLib
 * @package app\count\library\finance
 */
class FinanceAmountLib extends FinanceAmount
{
    /**
     * 获取列表
     * @param $params
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList($params)
    {
        $params['type'] = isset($params['type']) && $params['type'] ? $params['type'] : 'date';
        if ($params['type'] != 'date' && !isset($params['checkDate'])) {
            $params['day_start'] = date('Y-m-d', strtotime('-1 day'));
            $params['day_end']   = date('Y-m-d', strtotime('-1 day'));
            $params['checkDate'] = 'yesterday';
        }

        $params['day_start']    = isset($params['day_start']) && $params['day_start'] ? $params['day_start'] : date('Y-m-d', strtotime('-15 day'));
        $params['day_end']      = isset($params['day_end']) && $params['day_end'] ? $params['day_end'] : date('Y-m-d', strtotime('-1 day'));
        $params['month_start']  = isset($params['month_start']) && $params['month_start'] ? $params['month_start'] : date('Y-m', strtotime('-1 month'));
        $params['month_end']    = isset($params['month_end']) && $params['month_end'] ? $params['month_end'] : date('Y-m');
        $params['checkDate']    = isset($params['checkDate']) && $params['checkDate'] ? $params['checkDate'] : 'day';
        $params['platform']     = isset($params['platform']) && $params['platform'] ? $params['platform'] : [];
        $params['develop_user'] = isset($params['develop_user']) && $params['develop_user'] ? $params['develop_user'] : [];
        $params['sales_user']   = isset($params['sales_user']) && $params['sales_user'] ? $params['sales_user'] : [];
        $params['account']   = isset($params['account']) && $params['account'] ? $params['account'] : [];

        $map = [];
        if ($params['checkDate'] == 'month') {
            $startTime = strtotime($params['month_start'] . "-01");
            $endTime   = strtotime($params['month_end'] . '-01+1 month') - 1;
        } else {
            $startTime = strtotime($params['day_start']);
            $endTime   = strtotime($params['day_end'] . ' 23:59:59');
        }

        if ($params['platform']) $map['platform'] = array('in', $params['platform']);
        if ($params['develop_user']) $map['develop_user'] = array('in', $params['develop_user']);
        if ($params['sales_user']) $map['sales_user'] = array('in', $params['sales_user']);
        if ($params['account']) $map['platform_account'] = array('in', $params['account']);

        if ($params['type'] == 'date') $group = ($params['checkDate'] == 'month') ? 'month,year' : 'days,month,year';
        if ($params['type'] == 'platform') $group = 'platform';
        if ($params['type'] == 'account') 
        {
            $group = 'platform_account';
            if (!isset($map['platform_account'])) $map['platform_account'] = ['NEQ', ''];
        }
        if ($params['type'] == 'kfuser') $group = 'develop_user';
        // if ($params['type'] == 'seller') $group = 'sales_user';
        if ($params['type'] == 'organ' || $params['type'] == 'seller') 
        {
            $map['sales_branch_id'] = ['NEQ', 0];
            $group = 'sales_branch_id, sales_user';

            if (!empty($params['organ'])) {
                $all_sub_org_ids = ToolsLib::getInstance()->getSubOrgIds($params['organ'][0]);
                if ($all_sub_org_ids) $map['sales_branch_id'] = ['IN', $all_sub_org_ids];
            }
        }

        $fieldStr = "{$group},sum(total) as total,sum(cost) as cost,sum(freight) as freight,sum(material) as material,sum(platform_fee) as platform_fee,sum(paypal) as paypal,sum(commission) as commission,sum(refunds) as refunds,sum(gross) as gross, SUM(onlinefee) as onlinefee";

        $start = ($params['p'] - 1) * $params['ps'];
        $count = $this->whereTime('datetime', 'between', [$startTime, $endTime])->field($fieldStr)->where($map)->group($group)->count();
        
        $data  = $this->whereTime('datetime', 'between', [$startTime, $endTime])->field($fieldStr)->where($map)->group($group)->order("datetime desc")->select();

        if (isset($params['debug']) && $params['debug'] == 'sql')
        {
            echo '<pre>';print_r($this->getLastSql());echo '</pre>';
            exit;
        }

        $dataArr = replace_query($data);

        $totalArr  = ['total' => 0, 'cost' => 0, 'freight' => 0, 'material' => 0, 'platform_fee' => 0, 'paypal' => 0, 'commission' => 0, 'refunds' => 0, 'refunds_rate' => 0, 'gross' => 0, 'gross_rate' => 0, 'onlinefee' => 0];
        $jsonName  = [];
        $jsonTotal = [];
        $jsonGross = [];
        foreach ($dataArr as $key => &$val) {
            if ($params['checkDate'] != 'month' && $params['type'] == 'date') $dataArr[$key]['date'] = $val['year'] . "-" . $val['month'] . "-" . $val['days'];
            if ($params['checkDate'] == 'month' && $params['type'] == 'date') $dataArr[$key]['date'] = $val['year'] . "-" . $val['month'];
            $dataArr[$key]['refunds_rate'] = ($val['total'] == 0) ? '0' : round($val['refunds'] / $val['total'], 4) * 100;
            $dataArr[$key]['gross_rate']   = ($val['total'] == 0) ? '0' : round($val['gross'] / $val['total'], 4) * 100;
            if (isset($params['model']) && $params['model'] == 'chart') {
                if (!isset($params['type']) || $params['type'] == 'date') {
                    if ($params['checkDate'] == 'day') {
                        $jsonName[] = $val['month'] . '-' . $val['days'];
                    } else {
                        $jsonName[] = $val['year'] . '-' . $val['month'];
                    }
                } elseif ($params['type'] == 'platform') {
                    $jsonName[] = $val['platform'];
                } elseif ($params['type'] == 'kfuser') {
                    $jsonName[] = $val['develop_user'];
                } elseif ($params['type'] == 'seller') {
                    $jsonName[] = $val['sales_user'];
                }elseif ($params['type'] == 'account') {
                    $jsonName[] = $val['platform_account'];
                }
                $jsonTotal[] = round($val['total'], 3);
                $jsonGross[] = round($val['gross'], 3);
            }
            $totalArr['total']        += isset($val['total']) ? $val['total'] : 0;
            $totalArr['cost']         += isset($val['cost']) ? $val['cost'] : 0;
            $totalArr['freight']      += isset($val['freight']) ? $val['freight'] : 0;
            $totalArr['material']     += isset($val['material']) ? round($val['material'], 3) : 0;
            $totalArr['platform_fee'] += isset($val['platform_fee']) ? $val['platform_fee'] : 0;
            $totalArr['paypal']       += isset($val['paypal']) ? $val['paypal'] : 0;
            $totalArr['commission']   += isset($val['commission']) ? $val['commission'] : 0;
            $totalArr['refunds']      += isset($val['refunds']) ? $val['refunds'] : 0;
            $totalArr['gross']        += isset($val['gross']) ? $val['gross'] : 0;
            $totalArr['onlinefee']    += isset($val['onlinefee']) ? $val['onlinefee'] : 0;
        }

        if (count($dataArr) > 0) {
            $totalArr['refunds_rate'] = round(($totalArr['refunds'] / $totalArr['total']), 4) * 100;
            $totalArr['gross_rate']   = round(($totalArr['gross'] / $totalArr['total']), 4) * 100;
        }

        $return_data = [
            'data'      => $dataArr,
            'jsonName'  => json_encode($jsonName),
            'jsonTotal' => json_encode($jsonTotal),
            'jsonGross' => json_encode($jsonGross),
            'params'    => $params,
            'total'     => $totalArr,
            'count'     => $count,
        ];

        return $return_data;
    }
}