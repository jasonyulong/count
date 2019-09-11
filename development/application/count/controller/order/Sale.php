<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    jason
 */

namespace app\count\controller\order;

use think\Config;
use app\count\model\sales;
use app\count\library\OrgLib;
use think\cache\driver\Redis;
use app\common\library\ToolsLib;
use app\common\controller\Common;
use app\count\library\order\OrderLib;
use app\count\library\order\TargetLib;
use app\count\model\OrderSellerTarget;
use app\count\model\OrderTargetSeller;
use app\count\model\OrderTargetAccount;
use app\common\controller\AuthController;

/**
 * 销售额报表
 * @package app\count\controller\sale
 */
class Sale extends AuthController
{

    public function __construct()
    {
        parent::__construct();
        $this->manage_info = $this->auth->erp_id ? OrgLib::getInstance()->getManageInfo($this->auth->username) : false;
    }

    /**
     * 初始化，参数默认值
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-23 11:51:58
     */
    private function _index_init(&$params)
    {
        $params['type'] = $params['type'] ?? 'date';
        $params['model'] = $params['model'] ?? 'table';

        // 为了方便，设置一个后门，设置登录的用户
        if (!empty($params['login_name'])) $_SESSION['truename'] = $params['login_name'];

        // $params['is_trend']       = $params['is_trend'] ?? 0; // 环比增长走势
        $params['checkDate'] = $params['checkDate'] ?? 'day';
        $params['scandate_start'] = $params['scandate_start'] ?? date('Y-m');
        $params['scandate_end'] = $params['scandate_end'] ?? date('Y-m');
        if ($params['type'] == 'date') {
            $params['scantime_start'] = $params['scantime_start'] ?? date('Y-m-d', strtotime('-15 day'));
            $params['scantime_end'] = $params['scantime_end'] ?? date('Y-m-d', strtotime('today'));
        } else {
            $params['scantime_start'] = $params['scantime_start'] ?? date('Y-m-d', strtotime('-7 day'));
            $params['scantime_end'] = $params['scantime_end'] ?? date('Y-m-d', strtotime('-1 day'));
        }

        if ($params['checkDate'] == 'day' && strtotime($params['scantime_start']) > strtotime($params['scantime_end'])) return $this->error('开始时间不能大于结束时间');
        if ($params['checkDate'] == 'month' && strtotime($params['scandate_start']) > strtotime($params['scandate_end'])) return $this->error('开始时间不能大于结束时间');

        $params['sort'] = $params['sort'] ?? 'desc';
        $params['sort_field'] = $params['sort_field'] ?? 'year, month, days';
        $params['p'] = $params['p'] ?? 1;
        if ($params['type'] == 'date') $params['ps'] = 100;
        elseif ($params['type'] == 'account' || $params['type'] == 'platform') $params['ps'] = 100000;
        $params['ps'] = $params['ps'] ?? 15000;
        $params['platform'] = $params['platform'] ?? null; // null 似乎好用一点

        if (!empty($params['seller'])) {
            $params['seller'] = is_array($params['seller']) ? $params['seller'] : explode(',', $params['seller']);
            $params['seller'] = array_filter($params['seller'], function ($val) {
                return !empty($val);
            });
        }

        // if (isset($params['is_export']) && $params['is_export'] == 1) $params['ps'] = 10000;
        if (request()->get('debug') == 'erp') {
            print_r($this->manage_info);
            exit;
        }

        if ($this->auth->erp_id) {
            $platforms = $this->manage_info['manage_platforms'];
        } else {
            $platforms = ToolsLib::getInstance()->getAllPlatforms($_SESSION['truename'] ?? '');
        }

        // 默认展示第一个平台
        if ($params['type'] == 'account' && !isset($params['platform']) && !empty($platforms)) {
            $params['platform'] = $platforms[0];
        }

        $all_accounts = ToolsLib::getInstance()->getAllAccounts(3);
        $account_list = [];
        if (!empty($params['platform']) && $params['type'] == 'account') $account_list = $all_accounts[$params['platform']];
        if ($this->auth->erp_id) {
            $account_list = array_intersect($account_list, $this->manage_info['manage_accounts']);
        }

        // $org_list = ToolsLib::getInstance()->getLevel1Orgs($_SESSION['truename'] ?? '林嘉权');
        $org_list = OrgLib::getInstance()->getTopBussinessOrgs($this->manage_info);

        // todo: 需求， 默认展示第一个部门
        if ($params['type'] == 'seller' && !isset($params['organ'])) {
            $params['organ'][] = array_column($org_list, 'id')[0];
        }

        $this->assign('org_list', $org_list);

        $this->assign('account_list', $account_list);
        $this->assign('platforms', $platforms);
        $this->assign('type', $params['type']);
        $this->assign('model', $params['model']);
        $this->assign('params', $params);
        $this->assign('module', 'order');
    }

    /**
     * 查看
     * @access auth
     * @return string
     * @throws \think\Exception
     */
    public function index()
    {
        $params = input('get.');
        $this->_index_init($params);

        if ($params['checkDate'] == 'day') {
            $range = range_day($params['scantime_end'], $params['scantime_start'], false);
        } else {
            $range = range_month($params['scandate_end'], $params['scandate_start']);
        }
        $this->assign('range', $range);

        // TODO: 如果是ERP 同步过来的用户(业务部)，只能看到自己的统计信息
        $is_top_manager = true; // 是否顶级的业务部管理者
        if ($this->auth->erp_id) {
            // 为销售报表 添加过滤
            $manage_info = $this->manage_info;
            $is_top_manager = $manage_info['is_top_manager'];
            $_current_user = $manage_info['current_user_info'];
            // $_tmp_platform_accounts = explode(',', $_current_user['ebayaccounts']);
            $_tmp_platform_accounts = $manage_info['manage_accounts'];

            if (isset($params['account'])) {
                $acount = array_intersect($params['account'], $_tmp_platform_accounts);
                $params['account'] = empty($acount) ? ['-1'] : $acount;
            } else {
                $params['account'] = $_tmp_platform_accounts;
            }

            // 为销售员报表 添加过滤
            $_is_manager = $manage_info['is_manager'];

            $_manage_users = $manage_info['manage_users'];
            $_manage_users = array_map(function ($v) {
                return "'{$v}'";
            }, $_manage_users);
            $_manage_users_str = implode(',', $_manage_users);

            $_manage_organ_ids = $manage_info['manage_org_ids'];
            $_manage_organ_ids_str = implode(',', $_manage_organ_ids);

            $_belong_org_ids = $manage_info['belong_org_ids'];
            $_belong_org_ids_str = implode(',', $_belong_org_ids);

            $_unmanage_org_ids = array_diff($_belong_org_ids, $_manage_organ_ids);
            $_unmanage_org_ids_str = implode(',', $_unmanage_org_ids);

            $_tmp_sql_where = "seller = '{$_current_user['username']}'";
            if ($_unmanage_org_ids_str) $_tmp_sql_where .= " AND branch_id IN ({$_unmanage_org_ids_str})";
            if ($_is_manager) {
                $_tmp_sql_where .= " OR (branch_id IN ({$_manage_organ_ids_str}) AND seller IN ({$_manage_users_str}))";
            }

            $params['where_sql_for_seller'] = $_tmp_sql_where;
        }

        switch ($params['type']) {
            case 'date':
            case 'account':
            case 'platform':
                $month = $this_month = date('Y-m');
                $before_month = date('Y-m', strtotime($this_month . ' -3 month'));
                $months = range_month($this_month, $before_month);
                $this->assign('months', $months);
                // echo '<pre>';var_dump($params);echo '</pre>';
                // exit;
                $data = OrderLib::getInstance()->getOrderSaleList($params, $params['type']);
                break;
            case 'seller':
            case 'organ':
                // todo: 获取目标值
                $month = $this_month = date('Y-m');
                $before_month = date('Y-m', strtotime($this_month . ' -3 month'));
                $months = range_month($this_month, $before_month);
                $this->assign('months', $months);

                $tmp_where = [];
                foreach ($months as $v) {
                    $tmp = explode('-', $v);
                    $tmp_year = $tmp[0];
                    $tmp_month = $tmp[1];

                    $tmp_where[] = "(year = {$tmp_year} AND month = {$tmp_month})";
                }
                $where_target = implode(' OR ', $tmp_where);
                $target_model = new OrderSellerTarget();
                $tmp_data = $target_model->where($where_target)->select();
                $target_map = [];
                foreach ($tmp_data as $v) {
                    $tmp_key = "{$v['org_id']}-{$v['year']}-{$v['month']}";
                    $target_map[$tmp_key] = $v['target_value'];
                }
                $this->assign('target_map', $target_map);

                $data = OrderLib::getInstance()->getOrderSellerList($params, $params['type']);
                break;
            case 'store':
                $data = OrderLib::getInstance()->getOrderStoreSaleList($params);
                break;
            case 'location':
                $data = OrderLib::getInstance()->getOrderLocationSaleList($params);
                break;
        }
        switch ($params['type']) {
            case 'date':
                $this->_index_date($params, $data);
                break;
            case 'account':
            case 'platform':
                $this->_index_account_or_platform($params, $data, $range);
                break;
            case 'seller':
                $this->_index_seller($params, $data, $range);
                break;
            case 'organ':
                // todo: 组织架构这个比较复杂，先获取所有销售员的数据，再手动统计，不然呢？
                $manage_info = $this->manage_info;
                $this->_index_organ($params, $data, $range);
                break;
            case 'store':
                $this->_index_store($params, $data, $range);
                break;
            case 'location':
                $this->_index_location($params, $data, $range);
                break;
        }
        $this->_assignPagerData($this, $params, $data['count']);
        $this->assign('list', $data['list']);
        $this->assign('list_total', $data['count']);
        $this->assign('is_top_manager', $is_top_manager);

        return $this->view->fetch("index");
    }

    /**
     * 获取上周的数据，或者，或者上个月的数据
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-31 02:55:25
     */
    private function _calc_data_day($params, &$data, $average_sale)
    {
        $day_diff = day_diff($params['scantime_start'], $params['scantime_end']);

        // todo: 计算遗漏的单量增幅，销售额增幅，  获取上周同期单量， 因为最后的7天，获取不了，所以要单独获取
        // 上周的数据
        $_day_of_week = 7;
        if ($day_diff >= $_day_of_week) {
            $_7_start = date('Y-m-d', strtotime($params['scantime_start']) - $_day_of_week * 86400);
            $_7_end = date('Y-m-d', strtotime($params['scantime_start']) - 1 * 86400);
        } else {
            $_7_start = date('Y-m-d', strtotime($params['scantime_start']) - $_day_of_week * 86400);
            $_7_end = date('Y-m-d', strtotime($params['scantime_start']) - 1 * 86400);
        }

        $tmp_params = $params;
        $tmp_params['scantime_start'] = $_7_start;
        $tmp_params['scantime_end'] = $_7_end;
        $_7_day_data = OrderLib::getInstance()->getOrderSaleList($tmp_params, $tmp_params['type']);
        $_7_day_data = $_7_day_data['list'];


        // TODO: GET 上一个月的数据
        $day_of_month = 31; // 默认一个月31 天，这样容错性比较高
        if ($day_diff >= $day_of_month) {
            $_31_start = date('Y-m-d', strtotime($params['scantime_start']) - $day_of_month * 86400);
            $_31_end = date('Y-m-d', strtotime($params['scantime_start']) - 1 * 86400);
        } else {
            $_31_start = date('Y-m-d', strtotime($params['scantime_start']) - $day_of_month * 86400);
            $_31_end = date('Y-m-d', strtotime($params['scantime_end']) - $day_of_month * 86400);
        }

        $tmp_params = $params;
        $tmp_params['scantime_start'] = $_31_start;
        $tmp_params['scantime_end'] = $_31_end;
        $_31_day_data = OrderLib::getInstance()->getOrderSaleList($tmp_params, $tmp_params['type']);
        $_31_day_data = $_31_day_data['list'];

        // todo: 计算  单量增幅，销售额增幅
        foreach ($data['list'] as $key => $value) {
            $_today = $value;

            $_yesterday_key = date('Y-m-d', strtotime($key) - 1 * 86400);
            $_last_week_key = date('Y-m-d', strtotime($key) - 7 * 86400);

            $_last_month = $value['month'] == 1 ? 12 : $value['month'] - 1;
            $_last_month_span = get_day_of_month($_last_month, $value['year']);

            $_last_month_key = date('Y-m-d', strtotime($key) - $_last_month_span * 86400);

            // todo: 计算 昨天，单量增幅，销售额增幅
            $_yesterday = [];
            if (!isset($data['list'][$_yesterday_key])) {
                if (isset($_7_day_data[$_yesterday_key])) $_yesterday = $_7_day_data[$_yesterday_key];
            } else {
                $_yesterday = $data['list'][$_yesterday_key];
            }
            if ($_yesterday) {
                $_total_percent = $_sum_percent = '0%';
                if ($_today['sum_totals'] == 0 && $_yesterday['sum_totals'] != 0) $_total_percent = "-100.0000%";
                if ($_today['sum_totals'] != 0 && $_yesterday['sum_totals'] == 0) $_total_percent = "100.0000%";
                if ($_today['sum_totals'] != 0 && $_yesterday['sum_totals'] != 0) $_total_percent = round((($_today['sum_totals'] - $_yesterday['sum_totals']) / $_yesterday['sum_totals']) * 100, 4) . "%";
                $data['list'][$key]['total_percent'] = $_total_percent;

                if ($_today['sum_sales'] == 0 && $_yesterday['sum_sales'] != 0) $_sum_percent = "-100.0000%";
                if ($_today['sum_sales'] != 0 && $_yesterday['sum_sales'] == 0) $_sum_percent = "100.0000%";
                if ($_today['sum_sales'] != 0 && $_yesterday['sum_sales'] != 0) $_sum_percent = round((($_today['sum_sales'] - $_yesterday['sum_sales']) / $_yesterday['sum_sales']) * 100, 4) . "%";
                $data['list'][$key]['sum_percent'] = $_sum_percent;
            } else {
                $data['list'][$key]['total_percent'] = '-';
                $data['list'][$key]['sum_percent'] = '-';
            }

            // todo: 计算 上周同期单量
            $last_week = [];
            if (!isset($data['list'][$_last_week_key])) {
                if (isset($_7_day_data[$_last_week_key])) $last_week = $_7_day_data[$_last_week_key];
            } else {
                $last_week = $data['list'][$_last_week_key];
            }
            if ($last_week) $data['list'][$key]['week_increment'] = $last_week['sum_sales'];
            else $data['list'][$key]['week_increment'] = '0.00';

            // todo: 计算 上月同期销售额
            $last_month = [];
            if (!isset($data['list'][$_last_month_key])) {
                if (isset($_31_day_data[$_last_month_key])) $last_month = $_31_day_data[$_last_month_key];
            } else {
                $last_month = $data['list'][$_last_month_key];
            }

            if ($last_month) $data['list'][$key]['month_sales_increment'] = $last_month['sum_sales'];
            else $data['list'][$key]['month_sales_increment'] = '-';


            // todo: 计算 相对于平均销售额增幅
            if ($average_sale) {
                if ($_today['sum_sales'] == 0 && $average_sale != 0) $average_percent = "-100.0000%";
                else $average_percent = round((($_today['sum_sales'] - $average_sale) / $average_sale) * 100, 4) . "%";
                $data['list'][$key]['average_percent'] = $average_percent;
            }
        }
    }

    /**
     * 获取上个月的数据
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-31 03:50:06
     */
    private function _calc_data_month($params, &$data, $average_sale)
    {
        // todo: 获取最前面的一个月的数据
        $pre_month_date = date('Y-m', strtotime($params['scandate_start']) - 86400);
        $tmp_params = $params;
        $tmp_params['scandate_start'] = $pre_month_date;
        $tmp_params['scandate_end'] = $pre_month_date;

        $pre_data = OrderLib::getInstance()->getOrderSaleList($tmp_params, $tmp_params['type']);

        // todo: 计算  单量增幅，销售额增幅
        foreach ($data['list'] as $key => $value) {
            $this_month_data = $value;
            $_last_month = $value['month'] == 1 ? 12 : $value['month'] - 1;
            $_last_month_span = get_day_of_month($_last_month, $value['year']);
            $_last_month_key = date('Y-m', strtotime($key) - $_last_month_span * 86400);

            $last_month_data = [];
            if (!isset($data['list'][$_last_month_key])) {
                if (isset($pre_data[$_last_month_key])) $last_month_data = $pre_data[$_last_month_key];
            } else {
                $last_month_data = $data['list'][$_last_month_key];
            }

            if ($last_month_data) {
                $_total_percent = $_sum_percent = '0%';
                if ($this_month_data['sum_totals'] == 0 && $last_month_data['sum_totals'] != 0) $_total_percent = "-100.0000%";
                if ($this_month_data['sum_totals'] != 0 && $last_month_data['sum_totals'] == 0) $_total_percent = "100.0000%";
                if ($this_month_data['sum_totals'] != 0 && $last_month_data['sum_totals'] != 0) $_total_percent = round((($this_month_data['sum_totals'] - $last_month_data['sum_totals']) / $last_month_data['sum_totals']) * 100, 4) . "%";
                $data['list'][$key]['total_percent'] = $_total_percent;

                if ($this_month_data['sum_sales'] == 0 && $last_month_data['sum_sales'] != 0) $_sum_percent = "-100.0000%";
                if ($this_month_data['sum_sales'] != 0 && $last_month_data['sum_sales'] == 0) $_sum_percent = "100.0000%";
                if ($this_month_data['sum_sales'] != 0 && $last_month_data['sum_sales'] != 0) $_sum_percent = round((($this_month_data['sum_sales'] - $last_month_data['sum_sales']) / $last_month_data['sum_sales']) * 100, 4) . "%";
                $data['list'][$key]['sum_percent'] = $_sum_percent;
                $data['list'][$key]['month_sales_increment'] = $last_month_data['sum_totals'];
            } else {
                $data['list'][$key]['total_percent'] = '-';
                $data['list'][$key]['sum_percent'] = '-';
                $data['list'][$key]['month_sales_increment'] = '-';
            }

            // todo: 计算 相对于平均销售额增幅
            if ($average_sale) {
                if ($this_month_data['sum_sales'] == 0 && $average_sale != 0) $average_percent = "-100.0000%";
                else $average_percent = round((($this_month_data['sum_sales'] - $average_sale) / $average_sale) * 100, 4) . "%";
                $data['list'][$key]['average_percent'] = $average_percent;
            }
        }
    }

    /**
     * index 太长了，因为 实现的功能太多,而且返回的数据差别大，需要将其分成几块
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-23 11:42:05
     */
    private function _index_date($params, &$data)
    {
        // todo: 计算，合计数，平均数
        $sum_function = function ($v1, $v2) {
            return $v1 + $v2;
        };
        $total_data = [];
        $total_data['sum_totals'] = array_reduce(array_column($data['list'], 'sum_totals'), $sum_function);
        $total_data['sum_sales'] = array_reduce(array_column($data['list'], 'sum_sales'), $sum_function);

        $average_sale = count($data['list']) ? $total_data['sum_sales'] / count($data['list']) : 0;
        $this->assign('total_data', $total_data);

        if ($params['checkDate'] == 'day') $this->_calc_data_day($params, $data, $average_sale);
        if ($params['checkDate'] == 'month') $this->_calc_data_month($params, $data, $average_sale);

        // todo: 为图表 构造数据
        if ($params['model'] == 'chart') {

            $chart_type = 'line';
            $x_data = [];
            foreach ($data['list'] as $value) {
                $tmp_str = "{$value['year']}-{$value['month']}";
                if ($params['checkDate'] == 'day') $tmp_str .= "-{$value['days']}";
                $x_data[] = $tmp_str;
            }

            $x_data_names = ['销售单数量', '销售额', '上周同期销售额', '上月同期销售额',];
            $y_data[] = array_column($data['list'], 'sum_totals');
            $y_data[] = array_column($data['list'], 'sum_sales');
            $y_data[] = array_column($data['list'], 'week_increment');
            $y_data[] = array_column($data['list'], 'month_sales_increment');

            $this->assign('chart_type', $chart_type);
            $this->assign('x_data', json_encode($x_data));
            $this->assign('y_data', json_encode($y_data));
            $this->assign('x_data_names', json_encode($x_data_names));
        }

        // TODO: 导出EXCEL
        if (isset($params['is_export']) && $params['is_export'] == 1) {
            $filename = "销售额报表(按日期)-" . date('Y-m-d');
            $export_data = [];

            foreach ($data['list'] as $key => $value) {
                $export_data[$key] = $value;
            }

            foreach ($export_data as $key => $value) {
                $tmp_str = "{$value['year']}-{$value['month']}";
                if ($params['checkDate'] == 'day') $tmp_str .= "-{$value['days']}";
                $export_data[$key]['date'] = $tmp_str;
            }

            $headers = [
                'date'                  => '日期',
                'sum_totals'            => '销售单量',
                'total_percent'         => '单量增幅',
                'sum_sales'             => '销售额($)',
                'sum_percent'           => '销售额增幅',
                'month_sales_increment' => '上月同期销售额($)',
                'average_percent'       => '相对于平均',
            ];
            if ($params['checkDate'] == 'day') $headers['week_increment'] = '上周同期销售额($)';

            ToolsLib::getInstance()->exportExcel($filename, $headers, $export_data, false);
        }
    }


    /**
     * index 太长了，因为 实现的功能太多,而且返回的数据差别大，需要将其分成几块
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-23 11:42:05
     */
    private function _index_account_or_platform($params, &$data, $range)
    {
        // 添加月份统计数据
        if ($params['type'] == 'account') $this->_addMonthlyDataForAccount($data);

        // TODO: 平台需要 添加 合计栏 （task 1097）
        if ($params['type'] == 'platform' || $params['type'] == 'account') {
            $total_data = [];
            // todo: 计算合计数
            foreach ($data['list'] as $_tmp_platform_data) {
                foreach ($_tmp_platform_data['dates'] as $_tmp_date => $_tmp_value) {
                    if ($params['checkDate'] == 'day') $_tmp_date = substr($_tmp_date, 5);
                    if (!isset($total_data[$_tmp_date]['sum_sales'])) $total_data[$_tmp_date]['sum_sales'] = 0;
                    if (!isset($total_data[$_tmp_date]['sum_totals'])) $total_data[$_tmp_date]['sum_totals'] = 0;
                    $total_data[$_tmp_date]['sum_sales'] += $_tmp_value['sum_sales'];
                    $total_data[$_tmp_date]['sum_totals'] += $_tmp_value['sum_totals'];
                }
            }
            $this->assign('total_data', $total_data);
        }

        $date_total_map = [];
        $date_aver_map = [];
        foreach ($data['list'] as $key => $value) {
            $date_total_map[$key]['sum_totals'] = 0;
            $date_total_map[$key]['sum_sales'] = 0;
            foreach ($value['dates'] as $k => $v) {
                $date_total_map[$key]['sum_totals'] += $v['sum_totals'];
                $date_total_map[$key]['sum_sales'] += $v['sum_sales'];
            }
        }
        foreach ($date_total_map as $key => $value) {
            $date_aver_map[$key]['average_totals'] = ceil($value['sum_totals'] / count($range));
            $date_aver_map[$key]['average_sales'] = round($value['sum_sales'] / count($range), 4);
        }

        $this->assign('date_total_map', $date_total_map);
        $this->assign('date_aver_map', $date_aver_map);

        // TODO: 计算当月达标率
        if ($params['type'] == 'account') {
            $_tmp_accounts = array_keys($data['list']);
            $_tmp_accounts_target_list = ToolsLib::getInstance()->getAccountsTarget($_tmp_accounts);

            foreach ($data['list'] as $key => $value) {
                foreach ($value['monthly_data'] as $k => $v) {
                    $_tmp_key = "{$key}___{$k}";

                    $_target_value = $_tmp_accounts_target_list[$_tmp_key] ?? '';
                    $_target_achive = $_target_value ? round($v['sales'] / $_target_value, 4) * 100 . '%' : '--';

                    $data['list'][$key]['monthly_data'][$k]['target_value'] = $_target_value;
                    $data['list'][$key]['monthly_data'][$k]['target_achive'] = $_target_achive;
                }
            }
        }

        $export_data = [];

        // todo: 为导出和图表做准备
        if ($params['model'] == 'chart' || (isset($params['is_export']) && $params['is_export'] == 1)) {
            foreach ($data['list'] as $key => $value) {
                $_tmp = [];
                $_tmp['account'] = $key;
                $date_format = $params['checkDate'] == 'day' ? 'm-d' : 'Y-m';
                foreach ($value['dates'] as $k => $v) {
                    if ($params['model'] == 'chart') {
                        $_tmp[date($date_format, strtotime($k))] = $v['sum_sales'];
                    } else {
                        $_tmp[date($date_format, strtotime($k)) . '_sum_sales'] = $v['sum_sales'];
                        $_tmp[date($date_format, strtotime($k)) . '_sum_totals'] = $v['sum_totals'];
                    }
                }
                $_tmp['sum_sales'] = $date_total_map[$key]['sum_sales'];
                $_tmp['average_sales'] = $date_aver_map[$key]['average_sales'];
                $export_data[] = $_tmp;
            }

            if (isset($params['debug']) && $params['debug'] == 'export') {
                echo '<pre>';
                var_dump($export_data);
                echo '</pre>';
                exit;
            }
        }

        // todo: js 图表展示
        if ($params['model'] == 'chart') {
            $x_data = array_keys($data['list']);
            $chart_type = 'bar';
            $x_data_names = array_merge($range, ['合计', '当前平均']);
            $y_data = [];
            foreach ($range as $key => $value) {
                $y_data[] = array_column($export_data, $value);
            }
            $this->assign('chart_type', $chart_type);
            $this->assign('x_data', json_encode($x_data));
            $this->assign('y_data', json_encode($y_data));
            $this->assign('x_data_names', json_encode($x_data_names));
        }

        // todo: 导出Excel
        if (isset($params['is_export']) && $params['is_export'] == 1) {
            $filename = "销售额报表(按" . ($params['type'] == 'account' ? '账号' : '平台') . ")-" . date('Y-m-d');

            $headers = ['account' => '账号'];
            foreach ($range as $value) {
                // $headers[$value] = $value;
                $headers[$value . '_sum_sales'] = $value . "(销售额)";
                $headers[$value . '_sum_totals'] = $value . "(销售单量)";
            }
            $headers['sum_sales'] = '合计';
            $headers['average_sales'] = '当前平均';

            ToolsLib::getInstance()->exportExcel($filename, $headers, $export_data, false);
        }
    }


    /**
     * index 太长了，因为 实现的功能太多,而且返回的数据差别大，需要将其分成几块
     * @AUTHOR: Lamkakyun
     * @DATE: 2019-01-25 13:46:10
     */
    private function _index_store($params, &$data, $range)
    {
        $all_stores = ToolsLib::getInstance()->getStoreCache();
        $this->assign('all_stores', $all_stores);

        // TODO: 平台需要 添加 合计栏 （task 1097）
        $total_data = [];
        // todo: 计算合计数
        foreach ($data['list'] as $_tmp_platform_data) {
            foreach ($_tmp_platform_data['dates'] as $_tmp_date => $_tmp_value) {
                if ($params['checkDate'] == 'day') $_tmp_date = substr($_tmp_date, 5);
                if (!isset($total_data[$_tmp_date]['sum_sales'])) $total_data[$_tmp_date]['sum_sales'] = 0;
                if (!isset($total_data[$_tmp_date]['sum_totals'])) $total_data[$_tmp_date]['sum_totals'] = 0;
                $total_data[$_tmp_date]['sum_sales'] += $_tmp_value['sum_sales'];
                $total_data[$_tmp_date]['sum_totals'] += $_tmp_value['sum_totals'];
            }
        }
        $this->assign('total_data', $total_data);

        $date_total_map = [];
        $date_aver_map = [];
        foreach ($data['list'] as $key => $value) {
            $date_total_map[$key]['sum_totals'] = 0;
            $date_total_map[$key]['sum_sales'] = 0;
            foreach ($value['dates'] as $k => $v) {
                $date_total_map[$key]['sum_totals'] += $v['sum_totals'];
                $date_total_map[$key]['sum_sales'] += $v['sum_sales'];
            }
        }
        foreach ($date_total_map as $key => $value) {
            $date_aver_map[$key]['average_totals'] = ceil($value['sum_totals'] / count($range));
            $date_aver_map[$key]['average_sales'] = round($value['sum_sales'] / count($range), 4);
        }

        $this->assign('date_total_map', $date_total_map);
        $this->assign('date_aver_map', $date_aver_map);

        $export_data = [];

        // todo: 为导出和图表做准备
        if ($params['model'] == 'chart' || (isset($params['is_export']) && $params['is_export'] == 1)) {
            foreach ($data['list'] as $key => $value) {
                $_tmp = [];
                $_tmp['store_name'] = $all_stores[$key]['store_name'] ?? $key;
                $date_format = $params['checkDate'] == 'day' ? 'm-d' : 'Y-m';
                foreach ($value['dates'] as $k => $v) {
                    if ($params['model'] == 'chart') {
                        $_tmp[date($date_format, strtotime($k))] = $v['sum_sales'];
                    } else {
                        $_tmp[date($date_format, strtotime($k)) . '_sum_sales'] = $v['sum_sales'];
                        $_tmp[date($date_format, strtotime($k)) . '_sum_totals'] = $v['sum_totals'];
                    }
                }
                $_tmp['sum_sales'] = $date_total_map[$key]['sum_sales'];
                $_tmp['average_sales'] = $date_aver_map[$key]['average_sales'];
                $export_data[] = $_tmp;
            }
        }

        // echo '<pre>';var_dump($export_data);echo '</pre>';
        // exit;

        // todo: js 图表展示
        if ($params['model'] == 'chart') {
            $x_data = [];
            $tmp = array_keys($data['list']);
            foreach ($tmp as $v) {
                $x_data[] = $all_stores[$v]['store_name'] ?? $v;
            }

            $chart_type = 'bar';
            $x_data_names = array_merge($range, ['合计', '当前平均']);
            $y_data = [];
            foreach ($range as $key => $value) {
                $y_data[] = array_column($export_data, $value);
            }
            $this->assign('chart_type', $chart_type);
            $this->assign('x_data', json_encode($x_data));
            $this->assign('y_data', json_encode($y_data));
            $this->assign('x_data_names', json_encode($x_data_names));
        }

        // todo: 导出Excel
        if (isset($params['is_export']) && $params['is_export'] == 1) {
            $filename = "销售额报表(按仓库)-" . date('Y-m-d');

            $headers = ['store_name' => '仓库'];
            foreach ($range as $value) {
                // $headers[$value] = $value;
                $headers[$value . '_sum_sales'] = $value . "(销售额)";
                $headers[$value . '_sum_totals'] = $value . "(销售单量)";
            }
            $headers['sum_sales'] = '合计';
            $headers['average_sales'] = '当前平均';

            ToolsLib::getInstance()->exportExcel($filename, $headers, $export_data, false);
        }
    }

    /**
     * index 太长了，因为 实现的功能太多,而且返回的数据差别大，需要将其分成几块
     * @AUTHOR: Lamkakyun
     * @DATE: 2019-01-25 13:46:06
     */
    private function _index_location($params, &$data, $range)
    {
        // TODO: 平台需要 添加 合计栏 （task 1097）
        $total_data = [];
        // todo: 计算合计数
        foreach ($data['list'] as $_tmp_platform_data) {
            foreach ($_tmp_platform_data['dates'] as $_tmp_date => $_tmp_value) {
                if ($params['checkDate'] == 'day') $_tmp_date = substr($_tmp_date, 5);
                if (!isset($total_data[$_tmp_date]['sum_sales'])) $total_data[$_tmp_date]['sum_sales'] = 0;
                if (!isset($total_data[$_tmp_date]['sum_totals'])) $total_data[$_tmp_date]['sum_totals'] = 0;
                $total_data[$_tmp_date]['sum_sales'] += $_tmp_value['sum_sales'];
                $total_data[$_tmp_date]['sum_totals'] += $_tmp_value['sum_totals'];
            }
        }
        $this->assign('total_data', $total_data);

        $date_total_map = [];
        $date_aver_map = [];
        foreach ($data['list'] as $key => $value) {
            $date_total_map[$key]['sum_totals'] = 0;
            $date_total_map[$key]['sum_sales'] = 0;
            foreach ($value['dates'] as $k => $v) {
                $date_total_map[$key]['sum_totals'] += $v['sum_totals'];
                $date_total_map[$key]['sum_sales'] += $v['sum_sales'];
            }
        }
        foreach ($date_total_map as $key => $value) {
            $date_aver_map[$key]['average_totals'] = ceil($value['sum_totals'] / count($range));
            $date_aver_map[$key]['average_sales'] = round($value['sum_sales'] / count($range), 4);
        }

        $this->assign('date_total_map', $date_total_map);
        $this->assign('date_aver_map', $date_aver_map);

        $export_data = [];

        // todo: 为导出和图表做准备
        if ($params['model'] == 'chart' || (isset($params['is_export']) && $params['is_export'] == 1)) {
            foreach ($data['list'] as $key => $value) {
                $_tmp = [];
                $_tmp['location'] = $key;
                $date_format = $params['checkDate'] == 'day' ? 'm-d' : 'Y-m';
                foreach ($value['dates'] as $k => $v) {
                    if ($params['model'] == 'chart') {
                        $_tmp[date($date_format, strtotime($k))] = $v['sum_sales'];
                    } else {
                        $_tmp[date($date_format, strtotime($k)) . '_sum_sales'] = $v['sum_sales'];
                        $_tmp[date($date_format, strtotime($k)) . '_sum_totals'] = $v['sum_totals'];
                    }
                }
                $_tmp['sum_sales'] = $date_total_map[$key]['sum_sales'];
                $_tmp['average_sales'] = $date_aver_map[$key]['average_sales'];
                $export_data[] = $_tmp;
            }
        }

        // echo '<pre>';var_dump($export_data);echo '</pre>';
        // exit;

        // todo: js 图表展示
        if ($params['model'] == 'chart') {
            $x_data = array_keys($data['list']);
            $chart_type = 'bar';
            $x_data_names = array_merge($range, ['合计', '当前平均']);
            $y_data = [];
            foreach ($range as $key => $value) {
                $y_data[] = array_column($export_data, $value);
            }
            $this->assign('chart_type', $chart_type);
            $this->assign('x_data', json_encode($x_data));
            $this->assign('y_data', json_encode($y_data));
            $this->assign('x_data_names', json_encode($x_data_names));
        }

        // todo: 导出Excel
        if (isset($params['is_export']) && $params['is_export'] == 1) {
            $filename = "销售额报表(按Location)-" . date('Y-m-d');

            $headers = ['location' => 'Location'];
            foreach ($range as $value) {
                $headers[$value . '_sum_sales'] = $value . "(销售额)";
                $headers[$value . '_sum_totals'] = $value . "(销售单量)";
            }
            $headers['sum_sales'] = '合计';
            $headers['average_sales'] = '当前平均';

            ToolsLib::getInstance()->exportExcel($filename, $headers, $export_data, false);
        }
    }


    /**
     * index 太长了，因为 实现的功能太多,而且返回的数据差别大，需要将其分成几块
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-23 05:00:35
     */
    private function _index_seller($params, &$data, $range)
    {
        $org_parent_name_map = ToolsLib::getInstance()->getAllOrgParentNameMap();
        // todo: 获取销售员的组织架构名称, 直接放到 key 后面（因为不清楚，是否有组织id，没有组织id，就没有组织架构，导致显示问题）
        $all_orgs = ToolsLib::getInstance()->getAllOrg(1);
        $tmp = $data['list'];

        $data['list'] = [];
        foreach ($tmp as $key => $value) {
            $seller_and_org_id = explode('___', $key);
            $tmp_seller = $seller_and_org_id[0];
            $tmp_org_id = $seller_and_org_id[1];
            $tmp_org_name = '';
            if ($tmp_org_id != 0) {
                $_tmp_org = $all_orgs[$tmp_org_id];
                $tmp_org_name = $_tmp_org['name'];
            }
            foreach ($value as $k => $v) {
                $value[$k]['org_name'] = $tmp_org_name;
                $value[$k]['org_parent_name'] = $tmp_org_name ? ($org_parent_name_map[$tmp_org_name] ?? '-') : '-';
            }
            // $data['list'][$tmp_seller] = $value;
            $data['list'][$key]['org_id'] = $tmp_org_id;
            $data['list'][$key]['org_name'] = $tmp_org_name;
            $data['list'][$key]['dates'] = $value;
        }
        $this->_addMonthlyDataForSeller($data);

        ksort($data['list']);

        $total_data = [];
        // todo: 计算合计数 (按日期)
        foreach ($data['list'] as $_tmp_platform_data) {
            foreach ($_tmp_platform_data['dates'] as $_tmp_date => $_tmp_value) {
                if ($params['checkDate'] == 'day') $_tmp_date = substr($_tmp_date, 5);
                if (!isset($total_data[$_tmp_date]['sum_sales'])) $total_data[$_tmp_date]['sum_sales'] = 0;
                if (!isset($total_data[$_tmp_date]['sum_totals'])) $total_data[$_tmp_date]['sum_totals'] = 0;
                $total_data[$_tmp_date]['sum_sales'] += $_tmp_value['sum_sales'];
                $total_data[$_tmp_date]['sum_totals'] += $_tmp_value['sum_totals'];
            }
        }

        // echo '<pre>';var_dump($total_data);echo '</pre>';
        // exit;

        $this->assign('total_data', $total_data);

        // todo: 合计数，平均数 (按销售员)
        $date_total_map = [];
        $date_aver_map = [];
        foreach ($data['list'] as $key => $value) {
            $date_total_map[$key]['sum_totals'] = 0;
            $date_total_map[$key]['sum_sales'] = 0;
            foreach ($value['dates'] as $k => $v) {
                $date_total_map[$key]['sum_totals'] += $v['sum_totals'];
                $date_total_map[$key]['sum_sales'] += $v['sum_sales'];
            }
        }
        foreach ($date_total_map as $key => $value) {
            $date_aver_map[$key]['average_totals'] = ceil($value['sum_totals'] / count($range));
            $date_aver_map[$key]['average_sales'] = round($value['sum_sales'] / count($range), 4);
        }

        $this->assign('date_total_map', $date_total_map);
        $this->assign('date_aver_map', $date_aver_map);


        if ($this->auth->erp_id) {
            $manage_info = $this->manage_info;
            $sellers = array_unique(array_merge($manage_info['manage_users'], [$manage_info['current_user_info']['username']]));
        } else {
            $sellers = !empty($params['organ']) ? ToolsLib::getInstance()->getSellerByOrg(array_column(ToolsLib::getInstance()->getOrgById($params['organ']), 'name')) : [];
        }

        $this->assign('sellers', $sellers);
        $this->assign('seller_arr', []);


        // TODO: 计算当月达标率
        $_tmp_sellers = array_keys($data['list']);
        $_tmp_sellers_target_list = ToolsLib::getInstance()->getSellersTarget($_tmp_sellers);
        foreach ($data['list'] as $key => $value) {
            foreach ($value['monthly_data'] as $k => $v) {
                $_tmp_key = "{$key}___{$k}";
                $_target_value = $_tmp_sellers_target_list[$_tmp_key] ?? '';
                $_target_achive = $_target_value ? round($v['sales'] / $_target_value, 4) * 100 . '%' : '--';

                $data['list'][$key]['monthly_data'][$k]['target_value'] = $_target_value;
                $data['list'][$key]['monthly_data'][$k]['target_achive'] = $_target_achive;
            }
        }

        // todo: 为导出和图表做准备
        $export_data = [];
        if ($params['model'] == 'chart' || (isset($params['is_export']) && $params['is_export'] == 1)) {
            foreach ($data['list'] as $key => $value) {
                $_tmp = [];

                if ($params['checkDate'] == 'day') {
                    $_tmp['organ'] = isset($value['dates'][$params['scantime_start']]) ? trim($value['dates'][$params['scantime_start']]['org_parent_name']) . trim($value['dates'][$params['scantime_start']]['org_name']) : '';
                } else {
                    $_tmp['organ'] = isset($value['dates'][$params['scandate_start']]) ? trim($value['dates'][$params['scandate_start']]['org_parent_name']) . trim($value['dates'][$params['scandate_start']]['org_name']) : '';
                }

                $_tmp['seller'] = trim(mb_substr($key, 0, 3), '_');
                $date_format = $params['checkDate'] == 'day' ? 'm-d' : 'Y-m';
                foreach ($value['dates'] as $k => $v) {
                    if ($params['model'] == 'chart') {
                        $_tmp[date($date_format, strtotime($k))] = $v['sum_sales'];
                    } else {
                        $_tmp[date($date_format, strtotime($k)) . '_sum_sales'] = $v['sum_sales'] ?? 0;
                        $_tmp[date($date_format, strtotime($k)) . '_sum_totals'] = $v['sum_totals'] ?? 0;
                    }
                }
                $_tmp['sum_sales'] = $date_total_map[$key]['sum_sales'];
                $_tmp['average_sales'] = $date_aver_map[$key]['average_sales'];
                $_current_month = date('Y-m');
                $_tmp['target_achive'] = $value['monthly_data'][$_current_month]['target_achive'];
                $_tmp['target_value'] = $value['monthly_data'][$_current_month]['target_value'];
                $export_data[] = $_tmp;
            }
        }


        // todo: js 图表展示
        if ($params['model'] == 'chart') {

            $x_data = array_keys($data['list']);
            $x_data = array_map(function ($val) {
                return preg_replace('/___\d+$/', '', $val);
            }, $x_data);

            $chart_type = 'bar';
            $x_data_names = array_merge($range, ['合计', '当前平均']);


            $y_data = [];
            foreach ($range as $key => $value) {
                $y_data[] = array_column($export_data, $value);
            }
            $this->assign('chart_type', $chart_type);
            $this->assign('x_data', json_encode($x_data));
            $this->assign('y_data', json_encode($y_data));
            $this->assign('x_data_names', json_encode($x_data_names));
        }


        // todo: 导出Excel
        if (isset($params['is_export']) && $params['is_export'] == 1) {
            $filename = "销售额报表(按销售员)-" . date('Y-m-d');

            $headers = ['organ' => '组织架构', 'seller' => '销售员'];
            foreach ($range as $value) {
                // $headers[$value] = $value;
                $headers[$value . '_sum_sales'] = $value . "(销售额)";
                $headers[$value . '_sum_totals'] = $value . "(销售单量)";
            }
            $headers['sum_sales'] = '合计';
            $headers['average_sales'] = '当前平均';
            $headers['target_value'] = '目标值';
            $headers['target_achive'] = '当月达标率';

            ToolsLib::getInstance()->exportExcel($filename, $headers, $export_data, false);
        }
    }


    /**
     * index 太长了，因为 实现的功能太多,而且返回的数据差别大，需要将其分成几块
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-24 09:42:53
     */
    public function _index_organ($params, &$data, $range)
    {
        $data = $this->_reshapeSellerDataForOrgan($data, $params);
        $this->_addMonthlyDataForOrgan($data);

        // TODO: 计算合计数
        $level1_orgs_ids = array_column(ToolsLib::getInstance()->getLevel1Orgs(), 'id');

        $total_data = [];
        foreach ($data['list'] as $_tmp_organ_data) {
            if (!in_array($_tmp_organ_data['org_id'], $level1_orgs_ids)) continue;
            foreach ($_tmp_organ_data['dates'] as $_tmp_date => $value) {
                if ($params['checkDate'] == 'day') $_tmp_date = substr($_tmp_date, 5);
                if (!isset($total_data[$_tmp_date]['sum_totals'])) $total_data[$_tmp_date]['sum_totals'] = 0;
                if (!isset($total_data[$_tmp_date]['sum_sales'])) $total_data[$_tmp_date]['sum_sales'] = 0;
                $total_data[$_tmp_date]['sum_totals'] += $value['sum_totals'];
                $total_data[$_tmp_date]['sum_sales'] += $value['sum_sales'];
            }
        }
        $this->assign('total_data', $total_data);

        $model = new OrderSellerTarget();

        // todo: 添加 达成目标
        $all_target = [];
        $tmp_data = $model->where(['year' => date('Y'), 'month' => date('m')])->select()->toArray();
        foreach ($tmp_data as $key => $value) {
            $all_target[$value['org_id']] = $value['target_value'];
        }

        foreach ($data['list'] as $key => $value) {
            if (!isset($all_target[$value['org_id']])) {
                $data['list'][$key]['target_value'] = 0;
                $data['list'][$key]['success_precent'] = '--';
            } else {
                $data['list'][$key]['target_value'] = $all_target[$value['org_id']];
                $data['list'][$key]['success_precent'] = ($all_target[$value['org_id']] > 0 ? intval($value['all_sums'] / $all_target[$value['org_id']] * 100) : 0) . "%";
            }
            $data['list'][$key]['target_value'] = isset($all_target[$value['org_id']]) ? $all_target[$value['org_id']] : 0;
        }

        // todo: 为导出和图表做准备
        $export_data = [];
        if ($params['model'] == 'chart' || (isset($params['is_export']) && $params['is_export'] == 1)) {
            foreach ($data['list'] as $key => $value) {
                $_tmp = [];
                $_tmp['organ_name'] = $value['organ_name'];
                $_tmp['username'] = $value['username'];
                $date_format = $params['checkDate'] == 'day' ? 'm-d' : 'Y-m';
                foreach ($value['dates'] as $k => $v) {
                    // $_tmp[date($date_format, strtotime($k))] = $v['sum_sales'];
                    $_tmp[date($date_format, strtotime($k)) . '_sum_sales'] = $v['sum_sales'];
                    $_tmp[date($date_format, strtotime($k)) . '_sum_totals'] = $v['sum_totals'];
                }
                $_tmp['sum_sales'] = $value['all_sums'];
                $_tmp['average_sales'] = $value['average_sales'];
                $_tmp['target_value'] = $value['target_value'];
                $_tmp['success_precent'] = $value['success_precent'];
                $export_data[] = $_tmp;
            }
        }

        // todo: js 图表展示
        if ($params['model'] == 'chart') {
            // 组织架构 的图表好像没什么用，不做了
        }

        // todo: 导出Excel
        if (isset($params['is_export']) && $params['is_export'] == 1) {
            $filename = "销售额报表(按组织架构)-" . date('Y-m-d');

            $headers = ['organ_name' => '组织架构', 'username' => '负责人'];
            foreach ($range as $value) {
                // $headers[$value] = $value;
                $headers[$value . '_sum_sales'] = $value . "(销售额)";
                $headers[$value . '_sum_totals'] = $value . "(销售单量)";
            }
            $headers['sum_sales'] = '合计';
            $headers['average_sales'] = '当前平均';
            $headers['target_value'] = '月目标';
            if ($params['checkDate'] == 'day') $headers['success_precent'] = '达成率';

            ToolsLib::getInstance()->exportExcel($filename, $headers, $export_data, false);
        }
    }


    /**
     * 将销售员数据重组，给组织架构用(组织架构重构了，这个方法需要重新写)
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-26 06:38:06
     * $data array: erp_order_seller 取出来的数据
     * return : 返回以组织架构的销售数据
     */
    private function _reshapeSellerDataForOrgan($data, $params)
    {
        if ($params['checkDate'] == 'day') $tmp_range = range_day($params['scantime_end'], $params['scantime_start']);
        else $tmp_range = range_month($params['scandate_end'], $params['scandate_start']);

        $days_num = count($tmp_range);

        $zero_total = [];
        foreach ($tmp_range as $tmp_v) {
            $zero_total[$tmp_v] = ['sum_totals' => '0', 'sum_sales' => '0.00'];
        }

        // $org_tree = ToolsLib::getInstance()->getBusinessOrgTree();
        // $org_arr  = ToolsLib::getInstance()->treeToArray($org_tree);

        $org_arr = OrgLib::getInstance()->getBussinessOrgArray();
        if (isset($org_arr[19])) unset($org_arr[19]);

        foreach ($org_arr as $key => $value) {
            $_seller_list = [];
            foreach ($value['org_full_user_list'] as $k => $v) {
                $_seller_list[] = $v['user_name'] . '___' . $v['organize_id'];
            }
            $org_arr[$key]['seller_list'] = $_seller_list;
            unset($org_arr[$key]['org_full_user_list']);
        }
        // echo '<pre>';var_dump($org_arr);echo '</pre>';
        // exit;

        // 只显示最顶级的组织架构
        if (isset($params['is_only_top']) && $params['is_only_top'] == true) {
            $tmp = $org_arr;
            $org_arr = [];
            foreach ($tmp as $v) {
                if ($v['level'] == 2) $org_arr[] = $v;
            }
        }

        $all_sellers = array_keys($data['list']);

        $manage_org_ids = array_column($org_arr, 'id');
        if ($this->auth->erp_id) {
            $manage_info = $this->manage_info;
            $manage_org_ids = $manage_info['manage_org_ids'];
        }

        $tmp = $org_arr;
        foreach ($tmp as $key => $value) {
            if (!in_array($value['id'], $manage_org_ids)) {
                unset($org_arr[$key]);
                continue;
            }
            // todo: 定义默认参数
            $org_arr[$key]['org_id'] = $value['id'];
            $org_arr[$key]['organ_name'] = $value['name'];
            $org_arr[$key]['username'] = $value['manage'];

            $org_arr[$key]['dates'] = $zero_total;
            $org_arr[$key]['all_sums'] = '0.00';
            $org_arr[$key]['all_total'] = '0';
            $org_arr[$key]['average_sales'] = '0.00';
            $org_arr[$key]['average_totals'] = '0';

            if (!$value['seller_list']) continue;

            // todo: 当前组织架构 与 有销售 额数据的 销售员 交集,如果有
            $seller_intersect = array_intersect($all_sellers, $value['seller_list']);

            if (!$seller_intersect) continue;

            $tmp_total = $zero_total;
            $all_sums = '0.00';
            $all_total = '0';
            foreach ($seller_intersect as $v) {
                $seller_sale_data = $data['list'][$v];
                foreach ($seller_sale_data as $_date => $sale_data) {
                    $tmp_total[$_date]['sum_totals'] += $sale_data['sum_totals'];
                    $tmp_total[$_date]['sum_sales'] += $sale_data['sum_sales'];
                    $all_sums += $sale_data['sum_sales'];
                    $all_total += $sale_data['sum_totals'];
                }
            }
            $org_arr[$key]['dates'] = $tmp_total;
            $org_arr[$key]['all_sums'] = $all_sums;
            $org_arr[$key]['all_total'] = $all_total;
            $org_arr[$key]['average_sales'] = round($all_sums / $days_num, 4);
            $org_arr[$key]['average_totals'] = ceil($all_total / $days_num);
        }
        return ['list' => $org_arr, 'count' => count($org_arr)];
    }


    /**
     * 合并数据 月份统计数据
     * @description: 凡是涉及组织架构的东西，都极为麻烦。逐级累加，太复杂。改用用组织架构下面的销售员来计算这个组织下面的数据(组织架构 就是 销售员的合集)
     * @return void
     * @author lamkakyun
     * @date 2018-12-19 18:33:36
     */
    private function _addMonthlyDataForOrgan(&$data)
    {
        $month = $this_month = date('Y-m');
        $before_month = date('Y-m', strtotime($this_month . ' -3 month'));
        $months = range_month($this_month, $before_month);
        $monthly_sales = OrderLib::getInstance()->getMonthlySaleForSeller($months);

        $default_data = [];
        foreach ($months as $m) {
            $default_data[$m] = [
                'sales'  => 0,
                'totals' => 0,
            ];
        }

        foreach ($data['list'] as $key => $value) {
            $data['list'][$key]['monthly_data'] = $default_data;
            foreach ($monthly_sales as $k => $v) {
                if (in_array($k, $value['seller_list'])) {
                    foreach ($v as $_k => $_v) {
                        $data['list'][$key]['monthly_data'][$_k]['sales'] += $_v['sales'];
                        $data['list'][$key]['monthly_data'][$_k]['totals'] += $_v['totals'];
                    }
                }
            }
        }

        // 计算当月平均
        foreach ($data['list'] as $key => $value) {
            foreach ($value['monthly_data'] as $k => $v) {
                $tmp_arr = explode('-', $k);
                $tmp_year = $tmp_arr[0];
                $tmp_month = $tmp_arr[1];

                // 获取当月天数，如果当月，不计算今天
                $days_amount = (date('Y-m') == $k) ? (date('d') - 1) : get_day_of_month($tmp_month);

                $data['list'][$key]['monthly_data'][$k]['day_amount'] = $days_amount;
                $data['list'][$key]['monthly_data'][$k]['month_avg'] = $days_amount > 0 ? round($v['sales'] / $days_amount, 0) : 0;
            }
        }
    }


    /**
     * 合并数据 月份统计数据
     * @return void
     * @author lamkakyun
     * @date 2018-12-20 10:29:34
     */
    private function _addMonthlyDataForSeller(&$data)
    {
        $month = $this_month = date('Y-m');
        $before_month = date('Y-m', strtotime($this_month . ' -3 month'));
        $months = range_month($this_month, $before_month);
        $monthly_sales = OrderLib::getInstance()->getMonthlySaleForSeller($months);

        $default_data = [];
        foreach ($months as $m) {
            $default_data[$m] = [
                'sales'  => 0,
                'totals' => 0,
            ];
        }

        foreach ($data['list'] as $key => $value) {
            $data['list'][$key]['monthly_data'] = $default_data;
            foreach ($monthly_sales as $k => $v) {
                if ($k == $key) {
                    foreach ($v as $_k => $_v) {
                        $data['list'][$key]['monthly_data'][$_k]['sales'] += $_v['sales'];
                        $data['list'][$key]['monthly_data'][$_k]['totals'] += $_v['totals'];
                    }
                }
            }
        }

        // 计算当月平均
        foreach ($data['list'] as $key => $value) {
            foreach ($value['monthly_data'] as $k => $v) {
                $tmp_arr = explode('-', $k);
                $tmp_year = $tmp_arr[0];
                $tmp_month = $tmp_arr[1];
                // 获取当月天数，如果当月，不计算今天
                $days_amount = (date('Y-m') == $k) ? (date('d') - 1) : get_day_of_month($tmp_month);

                $data['list'][$key]['monthly_data'][$k]['day_amount'] = $days_amount;
                $data['list'][$key]['monthly_data'][$k]['month_avg'] = $days_amount > 0 ? round($v['sales'] / $days_amount, 0) : 0;
            }
        }
    }


    /**
     * 合并数据 月份统计数据
     * @return void
     * @author lamkakyun
     * @date 2019-03-06 09:40:09
     */
    private function _addMonthlyDataForAccount(&$data)
    {
        $month = $this_month = date('Y-m');
        $before_month = date('Y-m', strtotime($this_month . ' -3 month'));
        $months = range_month($this_month, $before_month);

        $monthly_sales = OrderLib::getInstance()->getMonthlySaleForAccount($months);

        $default_data = [];
        foreach ($months as $m) {
            $default_data[$m] = [
                'sales'  => 0,
                'totals' => 0,
            ];
        }

        foreach ($data['list'] as $key => $value) {
            $data['list'][$key]['monthly_data'] = $default_data;
            foreach ($monthly_sales as $k => $v) {
                if ($k == $key) {
                    foreach ($v as $_k => $_v) {
                        $data['list'][$key]['monthly_data'][$_k]['sales'] += $_v['sales'];
                        $data['list'][$key]['monthly_data'][$_k]['totals'] += $_v['totals'];
                    }
                }
            }
        }

        // 计算当月平均
        foreach ($data['list'] as $key => $value) {
            foreach ($value['monthly_data'] as $k => $v) {
                $tmp_arr = explode('-', $k);
                $tmp_year = $tmp_arr[0];
                $tmp_month = $tmp_arr[1];
                // 获取当月天数，如果当月，不计算今天
                $days_amount = (date('Y-m') == $k) ? (date('d') - 1) : get_day_of_month($tmp_month);

                $data['list'][$key]['monthly_data'][$k]['day_amount'] = $days_amount;
                $data['list'][$key]['monthly_data'][$k]['month_avg'] = $days_amount > 0 ? round($v['sales'] / $days_amount, 0) : 0;
            }
        }

    }


    /**
     *  设置 目标 值
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-24 04:30:11
     */
    public function setTarget()
    {
        $params = array_merge(input('get.'), input('post.'));
        $params['p'] = $params['p'] ?? 1;
        $params['ps'] = $params['p'] ?? 50;
        $params['type'] = $params['type'] ?? 'organ';
        $params['platform'] = $params['platform'] ?? null; // null 似乎好用一点
        $params['year'] = $params['year'] ?? date('Y');
        $months = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
        $this->assign('months', $months);

        switch ($params['type']) {
            case 'organ':
                return $this->_setTargetForOrgan($params);
                break;
            case 'seller':
                return $this->_setTargetForSeller($params);
                break;
            case 'account':
                return $this->_setTargetForAccount($params);
                break;
        }
    }


    /**
     * 设置 目标 值（组织架构）
     * @return void
     * @author lamkakyun
     * @date 2019-03-04 10:08:35
     */
    private function _setTargetForOrgan($params)
    {
        $model = new OrderSellerTarget();
        if (request()->method() == 'GET') {
            // todo: 根据年份，获取所有组织架构的 每个月的目标
            $target_list = [];
            $tmp_data = $model->where(['year' => $params['year']])->select()->toArray();
            foreach ($tmp_data as $value) {
                $target_list[$value['org_id']][$value['month']] = $value['target_value'];
            }
            $this->assign('target_list', $target_list);

            $target_analyze = TargetLib::getInstance()->targetAnalyze($target_list);
            // echo '<pre>';var_dump($target_analyze);echo '</pre>';
            // exit;
            if (!empty($target_analyze['keys']))
            {
                $_start_time = "{$params['year']}-{$target_analyze['start_month']}";
                $_end_time = "{$params['year']}-{$target_analyze['end_month']}";
                $_range = range_month($_start_time, $_end_time);
                // echo '<pre>';var_dump($_range);echo '</pre>';
                // exit;
                $_type = 'organ';
                $_sale_params = [
                    'type' => $_type,
                    'model' => 'table',
                    'checkDate' => 'month',
                    // 'seller' => array_map(function($v){$tmp = explode('___', $v);return $tmp[0];}, $target_analyze['keys']),
                    'scandate_start' => $_start_time,
                    'scandate_end' => $_end_time,
                    'sort' => 'desc',
                    'sort_field' => 'year, month, days',
                ];

                // echo '<pre>';var_dump($_sale_params);echo '</pre>';
                // exit;

                $sales_data = OrderLib::getInstance()->getOrderSellerList($_sale_params, $_type);
                $this->_index_organ($_sale_params, $sales_data, $_range);
                // echo '<pre>';var_dump($sales_data);echo '</pre>';
                // exit;
                $sales_data = $sales_data['list'];

                $_tmp_data = [];
                foreach ($sales_data as $key => $value)
                {
                    foreach ($value['dates'] as $k => $v)
                    {
                        $_tmp_data[$key][substr($k, 5,2)] = $v['sum_sales'];
                    }
                }
                $sales_data = $_tmp_data;
                unset($_tmp_data);
            }
            else
            {
                $sales_data = [];
            }
            // echo '<pre>';var_dump($sales_data);echo '</pre>';
            //     exit;

            $org_tree = ToolsLib::getInstance()->getBusinessOrgTree();
            $org_arr = ToolsLib::getInstance()->treeToArray($org_tree);
            $data = [];
            foreach ($org_arr as $value) {
                $data[$value['id']] = $value;
            }

            // todo: 年份生成，前5年和后10年
            $year_list = [];
            $this_year = date('Y');
            for ($i = $this_year - 5; $i <= $this_year + 10; $i++) {
                $year_list[$i] = "{$i}年";
            }
            $this->assign('year_list', $year_list);
            // echo '<pre>';var_dump($target_list);echo '</pre>';
            // exit;


            $all_target = $model->select()->toArray();
            foreach ($all_target as $value) {
                if (!isset($data[$value['org_id']])) continue;
                $data[$value['org_id']]['target_value'] = $value['target_value'];
                $data[$value['org_id']]['update_user'] = $value['update_user'];
                $data[$value['org_id']]['update_time'] = $value['update_time'];
            }

            if (isset($params['is_export']) && $params['is_export'] == 1)
            {
                $this->_exportTarget($data, $target_list, $sales_data, $params['type']);
                exit;
            }

            $this->assign('sales_data', $sales_data);
            $this->assign('list', $data);
            $this->assign('list_total', count($data));
            $this->assign('params', $params);

            // echo '<pre>';var_dump($data);echo '</pre>';
            // exit;
            return $this->fetch('set_target');

        } else {
            if (!preg_match('/^\d+$/', $params['org_id'])) return json(['code' => -1, 'msg' => '组织架构不存在']);
            if (!preg_match('/(^[1-9]\d*(\.\d{1,2})?$)|(^0(\.\d{1,2})?$)/', $params['target_value'])) return json(['code' => -1, 'msg' => '参数错误']);
            if (!preg_match('/^\d{4}$/', $params['year']) || !preg_match('/^\d+$/', $params['month'])) return json(['code' => -1, 'msg' => '参数错误2']);

            $where = ['org_id' => $params['org_id'], 'year' => $params['year'], 'month' => $params['month']];
            $count = $model->where($where)->count();
            $save_data = [
                'org_id'       => $params['org_id'],
                'year'         => $params['year'],
                'month'        => $params['month'],
                'target_value' => $params['target_value'],
                'update_user'  => $_SESSION['truename'] ?? '林嘉权',
            ];
            if ($count > 0) {
                $model->where($where)->update($save_data);
            } else {
                $model->insert($save_data);
            }

            return json(['code' => 0, 'msg' => '添加成功']);
        }

    }


    /**
     * 设置 目标 值 (销售员)
     * @return void
     * @author lamkakyun
     * @date 2019-03-04 10:08:52
     */
    private function _setTargetForSeller($params)
    {
        $model = new OrderTargetSeller();
        if (request()->method() == 'GET') {

            $org_list = OrgLib::getInstance()->getTopBussinessOrgs($this->manage_info);

            // todo: 需求， 默认展示第一个部门
            if ($params['type'] == 'seller' && !isset($params['organ'])) {
                $params['organ'][] = array_column($org_list, 'id')[0];
            }

            $sub_org_ids = [];
            if (isset($params['organ'][0]) && !empty($params['organ'][0])) {
                $sub_org_ids = OrgLib::getInstance()->getSubOrgIds($params['organ'][0]);
            }

            $selected_sellers = $params['seller'] ?? [];

            if ($this->auth->erp_id) {
                $manage_info = $this->manage_info;
                $sellers = array_unique(array_merge($manage_info['manage_users'], [$manage_info['current_user_info']['username']]));
            } else {
                $sellers = !empty($params['organ']) ? ToolsLib::getInstance()->getSellerByOrg(array_column(ToolsLib::getInstance()->getOrgById($params['organ']), 'name')) : [];
            }

            $this->assign('org_list', $org_list);
            $this->assign('sellers', $sellers);

            $all_orgs = ToolsLib::getInstance()->getAllOrg(1);
            $org_users = OrgLib::getInstance()->getAllOrgUsers();
            $org_parent_name_map = ToolsLib::getInstance()->getAllOrgParentNameMap();

            foreach ($org_users as $key => $value) {
                $_tmp_org_name = $all_orgs[$value['organize_id']]['name'] ?? '';
                $org_users[$key]['org_name'] = $_tmp_org_name;
                $org_users[$key]['org_parent_name'] = $_tmp_org_name ? ($org_parent_name_map[$_tmp_org_name] ?? '-') : '-';
            }

            $data = [];
            foreach ($org_users as $key => $value) {
                if ($sub_org_ids && !in_array($value['organize_id'], $sub_org_ids)) continue;
                if ($selected_sellers && !in_array($value['user_name'], $selected_sellers)) continue;

                $tmp_key = "{$value['user_name']}___{$value['organize_id']}";
                $value['seller'] = $tmp_key;
                $data[$tmp_key] = $value;
            }

            // todo: 根据年份，获取所有组织架构的 每个月的目标
            $target_list = [];

            $where_target = ['year' => $params['year']];

            if ($sub_org_ids) $where_target['org_id'] = ['IN', $sub_org_ids];
            if (isset($params['seller']) && !empty($params['seller'])) $where_target['seller'] = ['IN', $params['seller']];

            $tmp_data = $model->where($where_target)->select()->toArray();
            foreach ($tmp_data as $value) {
                $_tmp_key = $value['seller'] . '___' . $value['org_id'];
                $target_list[$_tmp_key][$value['month']] = $value['target_value'];
            }
            $this->assign('target_list', $target_list);

            // echo '<pre>';var_dump($target_list);echo '</pre>';
            // exit;
            $target_analyze = TargetLib::getInstance()->targetAnalyze($target_list);
            // echo '<pre>';var_dump($target_analyze);echo '</pre>';
            // exit;
            if (!empty($target_analyze['keys']))
            {
                $_start_time = "{$params['year']}-{$target_analyze['start_month']}";
                $_end_time = "{$params['year']}-{$target_analyze['end_month']}";
                $_type = 'seller';
                $_sale_params = [
                    'type' => $_type,
                    'checkDate' => 'month',
                    'seller' => array_map(function($v){$tmp = explode('___', $v);return $tmp[0];}, $target_analyze['keys']),
                    'scandate_start' => $_start_time,
                    'scandate_end' => $_end_time,
                    'sort' => 'desc',
                    'sort_field' => 'year, month, days',
                ];

                // echo '<pre>';var_dump($_sale_params);echo '</pre>';
                // exit;

                $sales_data = OrderLib::getInstance()->getOrderSellerList($_sale_params, $_type);
                $sales_data = $sales_data['list'];

                $_tmp_data = [];
                foreach ($sales_data as $key => $value)
                {
                    foreach ($value as $k => $v)
                    {
                        $_tmp_data[$key][substr($k, 5,2)] = $v['sum_sales'];
                    }
                }
                $sales_data = $_tmp_data;
                unset($_tmp_data);
            }
            else
            {
                $sales_data = [];
            }

            // echo '<pre>';var_dump($sales_data);echo '</pre>';
            // exit;

            $all_target = $model->select()->toArray();
            foreach ($all_target as $value) {
                $_tmp_key = $value['seller'] . '___' . $value['org_id'];

                if (!isset($data[$_tmp_key])) continue;
                $data[$_tmp_key]['target_value'] = $value['target_value'];
                $data[$_tmp_key]['update_user'] = $value['update_user'];
                $data[$_tmp_key]['update_time'] = $value['update_time'];
            }

            // todo: 年份生成，前5年和后10年
            $year_list = [];
            $this_year = date('Y');
            for ($i = $this_year - 5; $i <= $this_year + 10; $i++) {
                $year_list[$i] = "{$i}年";
            }

            if (isset($params['is_export']) && $params['is_export'] == 1)
            {
                $this->_exportTarget($data, $target_list, $sales_data, $params['type']);
                exit;
            }
            
            $this->assign('year_list', $year_list);

            // echo '<pre>';var_dump($data);echo '</pre>';
            // exit;
            $this->assign('sales_data', $sales_data);
            $this->assign('list', $data);
            $this->assign('list_total', count($data));
            $this->assign('params', $params);

            return $this->fetch('set_target');
        } else {
            if (!isset($params['seller']) || empty($params['seller'])) return json(['code' => -1, 'msg' => '参数错误']);

            if (!preg_match('/(^[1-9]\d*(\.\d{1,2})?$)|(^0(\.\d{1,2})?$)/', $params['target_value'])) return json(['code' => -1, 'msg' => '参数无效']);

            if (!preg_match('/^\d{4}$/', $params['year']) || !preg_match('/^\d+$/', $params['month'])) return json(['code' => -1, 'msg' => '参数异常']);

            $tmp_arr = explode('___', $params['seller']);
            $seller = $tmp_arr[0];
            $org_id = $tmp_arr[1];

            $where = ['org_id' => $org_id, 'seller' => $seller, 'year' => $params['year'], 'month' => $params['month']];
            $count = $model->where($where)->count();
            $save_data = [
                'org_id'       => $org_id,
                'seller'       => $seller,
                'year'         => $params['year'],
                'month'        => $params['month'],
                'target_value' => $params['target_value'],
                'update_user'  => $_SESSION['truename'] ?? '林嘉权',
            ];
            if ($count > 0) {
                $model->where($where)->update($save_data);
            } else {
                $model->insert($save_data);
            }

            return json(['code' => 0, 'msg' => '添加成功']);
        }
    }


    /**
     * 设置 目标 值 (账户)
     * @return void
     * @author lamkakyun
     * @date 2019-03-04 10:09:06
     */
    private function _setTargetForAccount($params)
    {
        $model = new OrderTargetAccount();
        if (request()->method() == 'GET') {

            if ($this->auth->erp_id) {
                $platforms = $this->manage_info['manage_platforms'];
            } else {
                $platforms = ToolsLib::getInstance()->getAllPlatforms($_SESSION['truename'] ?? '');
            }

            // 默认展示第一个平台
            if ($params['type'] == 'account' && !isset($params['platform']) && !empty($platforms)) {
                $params['platform'] = $platforms[0];
            }

            // todo: 根据年份，获取所有组织架构的 每个月的目标
            $target_list = [];

            $where_target = ['year' => $params['year']];
            if (isset($params['platform']) && !empty($params['platform'])) $where_target['platform'] = $params['platform'];
            if (isset($params['account']) && !empty($params['account'])) $where_target['platform_account'] = ['IN', $params['account']];

            $tmp_data = $model->where($where_target)->select()->toArray();
            foreach ($tmp_data as $value) {
                $target_list[$value['platform_account']][$value['month']] = $value['target_value'];
            }
            $this->assign('target_list', $target_list);

            $target_analyze = TargetLib::getInstance()->targetAnalyze($target_list);
            if (!empty($target_analyze['keys']))
            {
                $_start_time = "{$params['year']}-{$target_analyze['start_month']}";
                $_end_time = "{$params['year']}-{$target_analyze['end_month']}";
                $_type = 'account';
                $_sale_params = [
                    'type' => $_type,
                    'checkDate' => 'month',
                    'platform' => $params['platform'],
                    'account' => $target_analyze['keys'],
                    'scandate_start' => $_start_time,
                    'scandate_end' => $_end_time,
                    'sort' => 'desc',
                    'sort_field' => 'year, month, days',
                ];

                $sales_data = OrderLib::getInstance()->getOrderSaleList($_sale_params, $_type);
                $sales_data = $sales_data['list'];

                $_tmp_data = [];
                foreach ($sales_data as $key => $value)
                {
                    foreach ($value['dates'] as $k => $v)
                    {
                        $_tmp_data[$key][substr($k, 5,2)] = $v['sum_sales'];
                    }
                }
                $sales_data = $_tmp_data;
                unset($_tmp_data);
            }
            else
            {
                $sales_data = [];
            }

            // todo: 年份生成，前5年和后10年
            $year_list = [];
            $this_year = date('Y');
            for ($i = $this_year - 5; $i <= $this_year + 10; $i++) {
                $year_list[$i] = "{$i}年";
            }
            $this->assign('year_list', $year_list);

            $all_accounts = ToolsLib::getInstance()->getAllAccounts(3);
            $account_list = [];
            if (!empty($params['platform']) && $params['type'] == 'account') $account_list = $all_accounts[$params['platform']];

            $_tmp_account_list = $account_list;
            if (isset($params['account']) && !empty($params['account'])) $_tmp_account_list = $params['account'];

            $this->assign('account_list', $account_list);
            $this->assign('platforms', $platforms);

            // TODO:组合数据
            $tmp = ToolsLib::getInstance()->getAllAccounts(2);
            $data = [];
            foreach ($tmp as $key => $value) {
                if (in_array($key, $_tmp_account_list)) $data[$key] = $value;
            }

            $all_target = $model->select()->toArray();
            foreach ($all_target as $value) {
                if (!isset($data[$value['platform_account']])) continue;
                $data[$value['platform_account']]['target_value'] = $value['target_value'];
                $data[$value['platform_account']]['update_user'] = $value['update_user'];
                $data[$value['platform_account']]['update_time'] = $value['update_time'];
            }

            // echo '<pre>';var_dump($target_list);echo '</pre>';
            // exit;

            if (isset($params['is_export']) && $params['is_export'] == 1)
            {
                $this->_exportTarget($data, $target_list, $sales_data, $params['type']);
                exit;
            }

            $this->assign('sales_data', $sales_data);
            $this->assign('list', $data);
            $this->assign('list_total', count($data));
            $this->assign('params', $params);

            return $this->fetch('set_target');
        } else {
            if (!isset($params['ebay_account']) || empty($params['ebay_account'])) return json(['code' => -1, 'msg' => '参数错误']);

            if (!preg_match('/(^[1-9]\d*(\.\d{1,2})?$)|(^0(\.\d{1,2})?$)/', $params['target_value'])) return json(['code' => -1, 'msg' => '参数无效']);

            if (!preg_match('/^\d{4}$/', $params['year']) || !preg_match('/^\d+$/', $params['month'])) return json(['code' => -1, 'msg' => '参数异常']);

            $account_platform_map = ToolsLib::getInstance()->getAllAccounts(4);

            $where = ['platform_account' => $params['ebay_account'], 'year' => $params['year'], 'month' => $params['month']];
            $count = $model->where($where)->count();
            $save_data = [
                'platform_account' => $params['ebay_account'],
                'platform'         => $account_platform_map[$params['ebay_account']],
                'year'             => $params['year'],
                'month'            => $params['month'],
                'target_value'     => $params['target_value'],
                'update_user'      => $_SESSION['truename'] ?? '林嘉权',
            ];
            if ($count > 0) {
                $model->where($where)->update($save_data);
            } else {
                $model->insert($save_data);
            }

            return json(['code' => 0, 'msg' => '添加成功']);
        }
    }


    /**
     * 导出 目标
     * 全部使用引用参数，速度更快,只要无修改即可
     * @author lamkakyun
     * @date 2019-04-30 14:55:44
     * @return void
     */
    private function _exportTarget(&$data, &$target_list, &$sales_data, &$type)
    {
        // echo '<pre>';var_dump($target_list);echo '</pre>';
        // exit;
        // switch ($type)
        // {
        //     case 'organ':
        //         $filename    = "销售额目标(按组织架构)-" . date('Y-m-d H:i:s');

        //         $header = [
        //             'name' => '组织架构',
        //             'manage' => '负责人',
        //         ];

        //         for ($i = 1 ;$i <= 12 ; $i++)
        //         {
        //             $_tmp_key = str_pad($i, 2, '0', STR_PAD_LEFT);
        //             $header[$_tmp_key] = $i . "月目标";
        //             $header[$_tmp_key] = $i . "达标率";
        //         }

        //         $export_data = [];
        //         foreach ($data as $key => $value)
        //         {
        //             $tmp_data = [];
        //             $tmp_data['name'] = ($value['level'] > 2 ? str_repeat('---', ($item['level'] - 1)) : '') . $value['name'];
        //             $tmp_data['manage'] = $value['manage'];

        //             for ($i = 1 ;$i <= 12 ; $i++)
        //             {
        //                 $_tmp_key = str_pad($i, 2, '0', STR_PAD_LEFT);
        //             }
        //         }
        //         break;
        //     case 'seller':
        //         $filename    = "销售额目标(按销售员)-" . date('Y-m-d H:i:s');
        //         break;
        //     case 'account':
        //         $filename    = "销售额目标(按账号)-" . date('Y-m-d H:i:s');
        //         break;
        // }
    }

    /**
     * 导入目标
     * @author lamkakyun
     * @date 2019-04-30 14:54:54
     * @return void
     */
    public function importTarget()
    {
        $params = array_merge(input('get.'), input('post.'));
        $params['p'] = $params['p'] ?? 1;
        $params['ps'] = $params['p'] ?? 50;
        $params['type'] = $params['type'] ?? 'seller';
        $params['platform'] = $params['platform'] ?? null; // null 似乎好用一点
        $params['year'] = $params['year'] ?? date('Y');

        // todo: 年份生成，前5年和后10年
        $year_list = [];
        $this_year = date('Y');
        for ($i = $this_year - 5; $i <= $this_year + 10; $i++) {
            $year_list[$i] = "{$i}年";
        }
        $this->assign('year_list', $year_list);

        $all_platforms = ToolsLib::getInstance()->getPlatformList();
        $all_organs = OrgLib::getInstance()->getBussinessOrgArray();
        $all_accounts = ToolsLib::getInstance()->getAllAccounts(3);

        // TODO: 下载模板
        if (isset($params['is_download']) && $params['is_download'] == 1) {
            $download_path = ROOT_PATH . "public/download_templates/import_target_for_{$params['type']}.xlsx";
            ToolsLib::getInstance()->downloadFile($download_path);
        }

        if (request()->method() == 'GET') {
            unset($all_organs[19]);
            foreach ($all_organs as $key => $value) {
                unset($all_organs[$key]['org_full_user_list']);
                unset($all_organs[$key]['org_full_user_account_list']);
                unset($all_organs[$key]['child_ids']);
                unset($all_organs[$key]['full_child_ids']);
            }

            $this->assign('params', $params);
            $this->assign('all_organs', $all_organs);
            $this->assign('all_platforms', $all_platforms);
            return $this->fetch('import_target');
        } else {
            // TODO: 检测数据是否异常
            switch ($params['type']) {
                case 'account':
                    if (!isset($params['platform']) || !in_array($params['platform'], $all_platforms)) return json(['code' => -1, 'msg' => '请选择平台']);
                    break;
                case 'seller':
                    if (!isset($params['organ']) || !in_array($params['organ'], array_column($all_organs, 'id'))) return json(['code' => -1, 'msg' => '请选择部门']);
                    break;
            }

            if (!isset($_FILES) || !isset($_FILES['file'])) return json(['code' => -1, 'msg' => '上传文件失败']);

            $file_info = $_FILES['file'];
            $ext = get_file_exention($file_info['name']);

            if (!in_array($ext, ['xls', 'xlsx'])) return json(['code' => -1, 'msg' => '请上传excel文件']);

            $ret_import = ToolsLib::getInstance()->getImportExcelData(true);

            if (!$ret_import['success']) return json(['code' => -1, 'msg' => '获取EXCEL数据失败']);
            $excel_data = $ret_import['data'];
            array_shift($excel_data);

            switch ($params['type']) {
                case 'account':
                    $model = new OrderTargetAccount();

                    // 检测账号
                    $accounts = array_column($excel_data, '0');
                    $accounts = array_map('trim', $accounts);
                    $platform_accounts = $all_accounts[$params['platform']];
                    if (count($accounts) != count(array_intersect($platform_accounts, $accounts))) {
                        $tmp_accounts = array_diff($accounts, $platform_accounts);
                        $tmp_str = count($tmp_accounts) > 10 ? substr(implode(',', $tmp_accounts), 0, 80) . '...' : implode(',', $tmp_accounts);
                        return json(['code' => -1, 'msg' => "【{$params['platform']}】账户:" . $tmp_str . '不存在']);
                    }

                    foreach ($excel_data as $value) {
                        foreach (range(1, 12) as $month) {
                            $month = str_pad($month, 2, '0', STR_PAD_LEFT);
                            $_target_value = $value[intval($month)];
                            if (!$_target_value) continue;
                            $where = ['platform' => $params['platform'], 'platform_account' => trim($value[0]), 'year' => $params['year'], 'month' => $month];

                            $save_data = ['platform' => $params['platform'], 'platform_account' => trim($value[0]), 'year' => $params['year'], 'month' => $month, 'target_value' => $_target_value, 'update_user' => $_SESSION['truename'] ?? 'system',];

                            $count = $model->where($where)->count();
                            if ($count > 0) $model->where($where)->update($save_data);
                            else $model->insert($save_data);
                        }
                    }
                    break;
                case 'seller':
                    $model = new OrderTargetSeller();

                    // 检测 组织架构 销售员
                    $sellers = array_column($excel_data, '0');
                    $sellers = array_map('trim', $sellers);

                    $all_org_users = OrgLib::getInstance()->getAllOrgUsers();
                    $org_sellers = [];

                    foreach ($all_org_users as $value) {
                        if ($value['organize_id'] == $params['organ']) $org_sellers[] = $value['user_name'];
                    }

                    if (count($sellers) != count(array_intersect($org_sellers, $sellers))) {
                        $tmp_sellers = array_diff($sellers, $org_sellers);
                        $tmp_str = count($tmp_sellers) > 10 ? mb_substr(implode(',', $tmp_sellers), 0, 80, 'utf-8') . '...' : implode(',', $tmp_sellers);
                        return json(['code' => -1, 'msg' => "【{$all_organs[$params['organ']]['name']}】账户:" . $tmp_str . '不存在']);
                    }

                    foreach ($excel_data as $value) {
                        foreach (range(1, 12) as $month) {
                            $month = str_pad($month, 2, '0', STR_PAD_LEFT);
                            $_target_value = $value[intval($month)];
                            if (!$_target_value) continue;

                            $where = ['org_id' => $params['organ'], 'seller' => trim($value[0]), 'year' => $params['year'], 'month' => $month];
                            $save_data = ['org_id' => $params['organ'], 'seller' => trim($value[0]), 'year' => $params['year'], 'month' => $month, 'target_value' => $_target_value, 'update_user' => $_SESSION['truename'] ?? 'system',];

                            $count = $model->where($where)->count();
                            if ($count > 0) $model->where($where)->update($save_data);
                            else $model->insert($save_data);
                        }
                    }

                    break;
            }


            return json(['code' => 0, 'msg' => '导入成功']);
        }
    }

    /**
     * 环比增长走势
     * @description  (使用另一张数据表实现)
     * @return void
     * @author lamkakyun
     * @date 2018-12-14 10:11:42
     */
    public function organTrendency()
    {
        $params = input('get.');
        $params['type'] = 'organ';

        $params['checkDate'] = 'month';
        $params['scandate_start'] = $params['scandate_start'] ?? date('Y-m', strtotime('-6 month'));
        $params['scandate_end'] = $params['scandate_end'] ?? date('Y-m');
        $this->_index_init($params);

        // 为了环比计算，需要多一天的数据，因此将日期的开始时间延长
        $range = range_month($params['scandate_start'], $params['scandate_end']);
        $params['scandate_start'] = date('Y-m', strtotime($params['scandate_start'] . ' -1 month'));
        $range_extend = range_month($params['scandate_start'], $params['scandate_end']);

        $data = OrderLib::getInstance()->getMonthlySaleForSeller($range_extend);

        $org_tree = ToolsLib::getInstance()->getBusinessOrgTree();
        $org_arr = ToolsLib::getInstance()->treeToArray($org_tree);
        // 只显示最顶级的组织架构
        $tmp = $org_arr;
        $org_arr = [];
        foreach ($tmp as $v) {
            if ($v['level'] == 2) $org_arr[] = $v;
        }

        $default_data = [];
        foreach ($range_extend as $m) {
            $default_data[$m] = [
                'sales'  => 0,
                'totals' => 0,
            ];
        }

        // step 1: 需要3个foreach 才能计算出 每个组织架构 的 每月的总销售额和总销量
        foreach ($org_arr as $key => $value) {
            $org_arr[$key]['monthly_data'] = $default_data;
            foreach ($data as $k => $v) {
                if (in_array($k, $value['seller_list'])) {
                    foreach ($v as $_k => $_v) {
                        $org_arr[$key]['monthly_data'][$_k]['sales'] += $_v['sales'];
                        $org_arr[$key]['monthly_data'][$_k]['totals'] += $_v['totals'];
                    }
                }
            }
        }

        // step 2: 计算当月平均
        foreach ($org_arr as $key => $value) {
            foreach ($value['monthly_data'] as $k => $v) {
                $tmp_arr = explode('-', $k);

                $tmp_year = $tmp_arr[0];
                $tmp_month = $tmp_arr[1];

                $days_amount = (date('Y-m') == $k) ? (date('d') - 1) : get_day_of_month($tmp_month);
                $org_arr[$key]['monthly_data'][$k]['day_amount'] = $days_amount;
                $org_arr[$key]['monthly_data'][$k]['month_avg'] = round($v['sales'] / $days_amount, 4);
            }
        }

        // step3: 环比增长率，并把多出的数据删除
        foreach ($org_arr as $key => $value) {
            foreach ($value['monthly_data'] as $k => $v) {
                $current_avg_sales = $v['month_avg'];
                $last_key = date('Y-m', strtotime($k . ' -1 month'));
                $last_avg_sales = isset($value['monthly_data'][$last_key]) ? $value['monthly_data'][$last_key]['month_avg'] : false;

                if ($last_avg_sales === false) {
                    unset($org_arr[$key]['monthly_data'][$k]);
                } else {
                    if ($last_avg_sales == $current_avg_sales) {
                        $org_arr[$key]['monthly_data'][$k]['grow_percent'] = '0%';
                    } else {
                        if ($last_avg_sales == 0) $org_arr[$key]['monthly_data'][$k]['grow_percent'] = '100%';
                        elseif ($current_avg_sales == 0) $org_arr[$key]['monthly_data'][$k]['grow_percent'] = '-100%';
                        else
                            $org_arr[$key]['monthly_data'][$k]['grow_percent'] = round(($current_avg_sales - $last_avg_sales) / $last_avg_sales * 100, 2) . '%';
                    }
                }
            }
        }

        // echo '<pre>';var_dump($params, $range,$org_arr);echo '</pre>';
        // exit;

        $this->assign('range', $range);
        $this->_assignPagerData($this, $params, count($org_arr));
        $this->assign('list', $org_arr);
        $this->assign('list_total', count($org_arr));

        return $this->view->fetch('organ_trendency');

    }
}