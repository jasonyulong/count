<?php

namespace app\count\library\order;

use think\Db;
use think\Model;
use app\common\library\ToolsLib;
use app\count\model\OrderProfit;
use app\count\model\OrderPreProfit;

/**
 * 订单的预利润，确定利润，放在这里
 * Class OrderProfitLib
 * @package app\count\library\order
 */
class OrderProfitLib
{
    private static $instance = null;

    public function __construct()
    {
        $this->orderPreprofitModel = new OrderPreProfit();
        $this->orderProfitModel    = new OrderProfit();
    }

    /**
     * single pattern
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-09-12 12:02:35
     */
    public static function getInstance(): OrderProfitLib
    {
        if (!static::$instance) {
            static::$instance = new OrderProfitLib();
        }
        return static::$instance;
    }


    /**
     * 获取利润 信息
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-29 01:57:43
     * $params 请求参数
     * $type: 请求的 类型。比如 按日期，按平台，按账号
     * $is_pre: 是否预利润，否则是确定利润
     */
    public function getProfitList($params, $type = 'date', $is_preprofit = true)
    {
        $model = $is_preprofit ? $this->orderPreprofitModel : $this->orderProfitModel;
        $where = [];

        if ($params['checkDate'] == 'day') $range = range_day($params['scantime_end'], $params['scantime_start'], true);
        else $range = range_month($params['scandate_end'], $params['scandate_start']);

        if (isset($params['scantime_start'])) $params['scantime_start'] .= ' 00:00:00';
        if (isset($params['scantime_end'])) $params['scantime_end'] .= ' 23:59:59';
        if (isset($params['scandate_start'])) $params['scandate_start'] .= '-01';
        if (isset($params['scandate_end'])) $params['scandate_end'] .= '-' . get_day_of_month(date('m', strtotime($params['scandate_end']))) . " 23:59:59";
        if ($params['checkDate'] == 'day') {
            if (isset($params['scantime_start'])) $where['datetime'][] = ['EGT', strtotime($params['scantime_start'])];
            if (isset($params['scantime_end'])) $where['datetime'][] = ['LT', strtotime($params['scantime_end'])];
        } else {
            if (isset($params['scandate_start'])) $where['datetime'][] = ['EGT', strtotime($params['scandate_start'])];
            if (isset($params['scandate_end'])) $where['datetime'][] = ['Lt', strtotime($params['scandate_end'])];
        }
        if (!empty($_SESSION['truename'])) {
            $platform = ToolsLib::getInstance()->getCanViewPlatform($_SESSION['truename']);
            if ($platform) $where['platform'] = ['IN', $platform];
        }
        if (isset($params['platform']) && !empty($params['platform'])) $where['platform'] = ['IN', $params['platform']];
        if (isset($params['account'])) $where['platform_account'] = ['IN', $params['account']];

        $sort_arr = explode(',', $params['sort_field']);
        $order_by = implode(',', array_map(function ($val) use ($params) {
            return "{$val} {$params['sort']}";
        }, $sort_arr));
        $start    = ($params['p'] - 1) * $params['ps'];

        if (!empty($_SESSION['truename'])) {
            $org_list = ToolsLib::getInstance()->getLevel1Orgs($_SESSION['truename']);
            $sellers  = ToolsLib::getInstance()->getSellerByOrg(array_column($org_list, 'name'));
            if ($sellers) $where['seller'] = ['IN', $sellers];
        }

        if (!empty($params['organ'])) {
            $sub_org_ids        = ToolsLib::getInstance()->getSubOrgIds(array_shift($params['organ']));
            $where['branch_id'] = $sub_org_ids ? ['IN', $sub_org_ids] : -1;
        }

        if (isset($params['seller']) && !empty($params['seller'])) $where['seller'] = ['IN', $params['seller']];

        if (isset($params['where_sql_str']) && !empty($params['where_sql_str'])) $where[] = ['EXP', Db::raw($params['where_sql_str'])];

        $ret_data = [];
        switch ($type) {
            case 'date':
                $_group_by = $params['checkDate'] == 'day' ? 'days, month, year' : 'month, year';
                $_fields   = "{$_group_by}, SUM(profit) as sum_profit, SUM(sales) as sum_sales, SUM(profit_totals) AS sum_profit_totals";
                $ret_data  = OrderLib::getInstance()->_getGroupByCountAndList($model, $where, $_group_by, $_fields, $start, $params['ps'], $order_by);

                // todo：将 date 放到 key 的位置上
                $tmp              = $ret_data['list'];
                $ret_data['list'] = [];
                foreach ($tmp as $key => $value) {
                    $tmp_key = $params['checkDate'] == 'day' ? "{$value['year']}-{$value['month']}-{$value['days']}" : "{$value['year']}-{$value['month']}";

                    $ret_data['list'][$tmp_key] = $value;
                }

                break;
            case 'account':
                $_group_by = 'platform_account,days, month, year';
                if ($params['checkDate'] == 'month') $_group_by = 'platform_account, month, year';
                $_fields = "{$_group_by}, SUM(profit) as sum_profit, SUM(sales) as sum_sales, SUM(profit_totals) AS sum_profit_totals";

                $ret_data = OrderLib::getInstance()->_getGroupByCountAndList($model, $where, $_group_by, $_fields, $start, $params['ps'], $order_by);

                // todo:重组数据 (就算没有数据 也要默认给 空数组)
                $ret_data_reshape = [];
                foreach ($ret_data['list'] as $value) {
                    foreach ($range as $v) {
                        $ret_data_reshape[$value['platform_account']][$v] = ['sum_profit' => '0.00', 'sum_sales' => '0.00', 'sum_profit_totals' => '0'];
                    }
                }

                foreach ($ret_data['list'] as $value) {
                    if ($params['checkDate'] == 'month') $ret_data_reshape[$value['platform_account']]["{$value['year']}-{$value['month']}"] = $value;
                    else $ret_data_reshape[$value['platform_account']]["{$value['year']}-{$value['month']}-{$value['days']}"] = $value;
                }
                $ret_data['list']  = $ret_data_reshape;
                $ret_data['count'] = count($ret_data_reshape);

                break;
            case 'platform':
                $_group_by = 'platform,days, month, year';
                if ($params['checkDate'] == 'month') $_group_by = 'platform, month, year';
                $_fields  = "{$_group_by}, SUM(profit) as sum_profit, SUM(sales) as sum_sales, SUM(profit_totals) AS sum_profit_totals";
                $ret_data = OrderLib::getInstance()->_getGroupByCountAndList($model, $where, $_group_by, $_fields, $start, $params['ps'], $order_by);

                // todo:重组数据 (就算没有数据 也要默认给 空数组)
                $ret_data_reshape = [];
                foreach ($ret_data['list'] as $value) {
                    foreach ($range as $v) {
                        $ret_data_reshape[$value['platform']][$v] = ['sum_profit' => '0.00', 'sum_sales' => '0.00', 'sum_profit_totals' => '0'];
                    }
                }

                foreach ($ret_data['list'] as $value) {
                    if ($params['checkDate'] == 'month') $ret_data_reshape[$value['platform']]["{$value['year']}-{$value['month']}"] = $value;
                    else $ret_data_reshape[$value['platform']]["{$value['year']}-{$value['month']}-{$value['days']}"] = $value;
                }
                $ret_data['list']  = $ret_data_reshape;
                $ret_data['count'] = count($ret_data_reshape);
                break;
            case 'seller':
            case 'organ':
                $_group_by = 'branch_id, seller,days, month, year';
                if ($params['checkDate'] == 'month') $_group_by = 'branch_id, seller, month, year';
                $_fields      = "{$_group_by}, SUM(profit) as sum_profit, SUM(profit_totals) AS sum_profit_totals, SUM(sales) as sum_sales, SUM(totals) as sum_totals, SUM(totals_ship) as sum_ships";
                $ret_data     = OrderLib::getInstance()->_getGroupByCountAndList($model, $where, $_group_by, $_fields, $start, $params['ps'], $order_by);
                $default_data = ['branch_id' => 0, 'sum_profit' => '0.00', 'sum_profit_totals' => '0', 'sum_sales' => '0.00', 'sum_totals' => '0', 'sum_ships' => '0'];
                // todo:重组数据 (就算没有数据 也要默认给 空数组)
                $ret_data_reshape = [];
                foreach ($ret_data['list'] as $value) {
                    foreach ($range as $v) {
                        $tmp_key = trim($value['seller']) . "___{$value['branch_id']}";

                        $ret_data_reshape[$tmp_key][$v] = $default_data;
                    }
                }

                foreach ($ret_data['list'] as $value) {
                    $tmp_key   = trim($value['seller']) . "___{$value['branch_id']}";
                    $_date_key = $params['checkDate'] == 'month' ? "{$value['year']}-{$value['month']}" : "{$value['year']}-{$value['month']}-{$value['days']}";

                    $ret_data_reshape[$tmp_key][$_date_key] = $value;
                }
                $ret_data['list']  = $ret_data_reshape;
                $ret_data['count'] = count($ret_data_reshape);

                break;
        }

        return $ret_data;
    }

    /**
     * 获取利润 信息 （第二版）, 老板说 之前的 没什么用
     * @AUTHOR: Lamkakyun
     * @DATE: 2019-03-22 14:42:52
     * $params 请求参数
     * $type: 请求的 类型。比如 按日期，按平台，按账号
     * $is_pre: 是否预利润，否则是确定利润
     */
    public function getProfitListV2($params, $type = 'date', $is_preprofit = true)
    {
        $model = $is_preprofit ? $this->orderPreprofitModel : $this->orderProfitModel;
        $where = [];

        if ($params['checkDate'] == 'day') $range = range_day($params['scantime_end'], $params['scantime_start'], true);
        else $range = range_month($params['scandate_end'], $params['scandate_start']);

        if (isset($params['scantime_start'])) $params['scantime_start'] .= ' 00:00:00';
        if (isset($params['scantime_end'])) $params['scantime_end'] .= ' 23:59:59';
        if (isset($params['scandate_start'])) $params['scandate_start'] .= '-01';
        if (isset($params['scandate_end'])) $params['scandate_end'] .= '-' . get_day_of_month(date('m', strtotime($params['scandate_end']))) . " 23:59:59";
        if ($params['checkDate'] == 'day') {
            if (isset($params['scantime_start'])) $where['datetime'][] = ['EGT', strtotime($params['scantime_start'])];
            if (isset($params['scantime_end'])) $where['datetime'][] = ['LT', strtotime($params['scantime_end'])];
        } else {
            if (isset($params['scandate_start'])) $where['datetime'][] = ['EGT', strtotime($params['scandate_start'])];
            if (isset($params['scandate_end'])) $where['datetime'][] = ['Lt', strtotime($params['scandate_end'])];
        }
        if (!empty($_SESSION['truename'])) {
            $platform = ToolsLib::getInstance()->getCanViewPlatform($_SESSION['truename']);
            if ($platform) $where['platform'] = ['IN', $platform];
        }
        if (isset($params['platform']) && !empty($params['platform'])) $where['platform'] = ['IN', $params['platform']];
        if (isset($params['account'])) $where['platform_account'] = ['IN', $params['account']];

        $sort_arr = explode(',', $params['sort_field']);
        $order_by = implode(',', array_map(function ($val) use ($params) {
            return "{$val} {$params['sort']}";
        }, $sort_arr));
        $start    = ($params['p'] - 1) * $params['ps'];

        if (!empty($_SESSION['truename'])) {
            $org_list = ToolsLib::getInstance()->getLevel1Orgs($_SESSION['truename']);
            $sellers  = ToolsLib::getInstance()->getSellerByOrg(array_column($org_list, 'name'));
            if ($sellers) $where['seller'] = ['IN', $sellers];
        }

        if (!empty($params['organ'])) {
            if ($params['organ'][0] == -1)
            {
                $where['branch_id'] = 0;
                // $where['branch_id|seller'] = 0;
                // $where[] = ['EXP', Db::raw("branch_id = 0 or seller = ''")];
            }
            else
            {
                $sub_org_ids        = ToolsLib::getInstance()->getSubOrgIds(array_shift($params['organ']));
                $where['branch_id'] = $sub_org_ids ? ['IN', $sub_org_ids] : -1;
            }
        }

        if (isset($params['seller']) && !empty($params['seller'])) $where['seller'] = ['IN', $params['seller']];

        if (isset($params['where_sql_str']) && !empty($params['where_sql_str'])) $where[] = ['EXP', Db::raw($params['where_sql_str'])];

        $ret_data = [];
        switch ($type) {
            case 'date':
            case 'account':
            case 'platform':
            case 'seller':
            case 'organ':
                if ($type == 'date') $_group_by = 'year, month, days';
                if ($type == 'platform') $_group_by = 'platform';
                if ($type == 'account') $_group_by = 'platform_account';
                if ($type == 'seller') $_group_by = 'seller, branch_id';
                if ($type == 'organ') $_group_by = 'seller, branch_id';

                $_fields  = "{$_group_by}, SUM(totals) as sum_totals, SUM(totals_ship) as sum_ships, SUM(profit) as sum_profit, SUM(sales) as sum_sales, SUM(profit_totals) AS sum_profit_totals, SUM(cost) as sum_cost, SUM(carrier_freight) sum_carrier_freight, SUM(onlinefee) as sum_onlinefee, SUM(package_fee) as sum_package_fee, SUM(platform_fee) as sum_platform_fee, SUM(paypal_fee) as paypal_fee, SUM(brokerage_fee) as brokerage_fee";
                
                $ret_data = OrderLib::getInstance()->_getGroupByCountAndList($model, $where, $_group_by, $_fields, $start, $params['ps'], $order_by);

                break;
        }

        return $ret_data;
    }
}