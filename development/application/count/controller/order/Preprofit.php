<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    kevin
 */


namespace app\count\controller\order;

use app\common\library\Auth;
use app\count\library\OrgLib;
use app\common\library\ToolsLib;
use app\common\controller\Common;
use app\count\library\export\TaskLib;
use app\count\library\order\OrderLib;
use app\common\controller\AuthController;
use app\count\library\order\OrderProfitLib;


/**
 * 预利润报表
 * Class Preprofit
 * @package app\count\controller\order
 */
class Preprofit extends AuthController
{
    protected $profit_type = 'preprofit';

    public function __construct()
    {
        parent::__construct();
        $this->manage_info = $this->auth->erp_id ? OrgLib::getInstance()->getManageInfo($this->auth->username) : false;
    }


    public function _initialize()
    {
        parent::_initialize();

        $contoller = explode(".", strtolower($this->request->controller()));
        $this->assign('module', $contoller[0] ?? '');

        // 用来确定是那个利润，预利润 还是 确定利润
        $this->assign('profit_type', $this->profit_type);
    }

    /**
     * 初始化，参数默认值
     * @AUTHOR: Lamkakyun
     * @DATE: 2019-02-23 11:31:07
     */
    private function _index_init(&$params)
    {
        // todo: 设置默认参数 【start】
        $params['type']           = $params['type'] ?? 'date';
        $params['model']          = $params['model'] ?? 'table';
        $params['sort']           = $params['sort'] ?? 'desc';
        $params['sort_field']     = $params['sort_field'] ?? 'year, month, days';
        $params['p']              = $params['p'] ?? 1;
        $params['checkDate']      = $params['checkDate'] ?? 'day';
        $params['scandate_start'] = $params['scandate_start'] ?? date('Y-m');
        $params['scandate_end']   = $params['scandate_end'] ?? date('Y-m');

        // TODO: 分页数，因为 之前，妥协用 js 来排序，导致，这里要尽量查出所有的数据, 要么在前端，加时间查询限制，这样就可以减少 查询的数量，那么速度就可以提高
        if ($params['type'] == 'date') $params['ps'] = 100;
        elseif (in_array($params['type'], ['account', 'platform', 'organ'])) $params['ps'] = 100000;
        $params['ps'] = $params['ps'] ?? 1500;

        $params['platform'] = $params['platform'] ?? null; // null 似乎好用一点
        if (isset($params['is_export']) && $params['is_export'] == 1) $params['ps'] = 100000;

        // backdoor:为了方便，设置登录的用户
        if (!empty($params['login_name'])) $_SESSION['truename'] = $params['login_name'];
        if (!empty($params['seller'])) {
            $params['seller'] = is_array($params['seller']) ? $params['seller'] : explode(',', $params['seller']);
            $params['seller'] = array_filter($params['seller'], function ($val) {
                return !empty($val);
            });
        }

        if ($params['type'] == 'date') {
            $params['scantime_start'] = $params['scantime_start'] ?? date('Y-m-d', strtotime('-20 day'));
            $params['scantime_end']   = $params['scantime_end'] ?? date('Y-m-d', strtotime('-10 day'));
        } else {
            $params['scantime_start'] = $params['scantime_start'] ?? date('Y-m-d', strtotime('-20 day'));
            $params['scantime_end']   = $params['scantime_end'] ?? date('Y-m-d', strtotime('-10 day'));
        }

        if ($params['checkDate'] == 'day' && strtotime($params['scantime_start']) > strtotime($params['scantime_end'])) return $this->error('开始时间不能大于结束时间');
        if ($params['checkDate'] == 'month' && strtotime($params['scandate_start']) > strtotime($params['scandate_end'])) return $this->error('开始时间不能大于结束时间');

        if ($this->auth->erp_id) {
            $platforms = $this->manage_info['manage_platforms'];
        } else {
            $platforms = ToolsLib::getInstance()->getAllPlatforms($_SESSION['truename'] ?? '');
        }

        // 默认展示第一个平台
        if ($params['type'] == 'account' && !isset($params['platform'])) {
            $params['platform'] = $platforms[0];
        }

        $all_accounts = ToolsLib::getInstance()->getAllAccounts(3);
        $account_list = [];
        if (!empty($params['platform']) && $params['type'] == 'account') $account_list = $all_accounts[$params['platform']];


        // todo: 设置默认参数 【end】

        // $org_list = ToolsLib::getInstance()->getLevel1Orgs($_SESSION['truename'] ?? '林嘉权');
        $org_list = OrgLib::getInstance()->getTopBussinessOrgs($this->manage_info);

        // todo: 需求， 默认展示第一个部门
        if ($params['type'] == 'seller' && !isset($params['organ'])) {
            $params['organ'][] = array_column($org_list, 'id')[0];
        }

        $this->assign('org_list', $org_list);
        $this->assign('params', $params);
        $this->assign('type', $params['type']);
        $this->assign('model', $params['model']);
        $this->assign('account_list', $account_list);
        $this->assign('platforms', $platforms);

    }


    /**
     * 查看
     * @description 参考 app\count\controller\sale.php 写出来，我也懒的在重复写了,也算是优化一个自己之前的代码结构吧
     * @access auth
     * @return string
     */
    public function index()
    {
        $params = input('get.');
        $this->_index_init($params);

        if ($params['checkDate'] == 'day') $range = range_day($params['scantime_end'], $params['scantime_start'], false);
        else $range = range_month($params['scandate_end'], $params['scandate_start']);

        // TODO: 如果是ERP 同步过来的用户(业务部)，只能看到自己的统计信息
        $is_top_manager = true; // 是否顶级的业务部管理者
        if ($this->auth->erp_id) {
            $manage_info    = OrgLib::getInstance()->getManageInfo($this->auth->username);
            $is_top_manager = $manage_info['is_top_manager'];
            $_current_user  = $manage_info['current_user_info'];
            // $_tmp_platform_accounts = explode(',', $manage_info['current_user_info']['ebayaccounts']);
            $_tmp_platform_accounts = $manage_info['manage_accounts'];
            $params['account']      = $params['account'] ?? $_tmp_platform_accounts;
            // $params['account'] = array_merge($params['account'], $_tmp_platform_accounts);

            // 为销售员报表 添加过滤
            $_is_manager = $manage_info['is_manager'];

            $_manage_users     = $manage_info['manage_users'];
            $_manage_users     = array_map(function ($v) {
                return "'{$v}'";
            }, $_manage_users);
            $_manage_users_str = implode(',', $_manage_users);

            $_manage_organ_ids     = $manage_info['manage_org_ids'];
            $_manage_organ_ids_str = implode(',', $_manage_organ_ids);

            $_belong_org_ids     = $manage_info['belong_org_ids'];
            $_belong_org_ids_str = implode(',', $_belong_org_ids);

            $_unmanage_org_ids     = array_diff($_belong_org_ids, $_manage_organ_ids);
            $_unmanage_org_ids_str = implode(',', $_unmanage_org_ids);

            $_tmp_sql_where = "seller = '{$_current_user['username']}'";
            if ($_unmanage_org_ids_str) $_tmp_sql_where .= " AND branch_id IN ({$_unmanage_org_ids_str})";
            if ($_is_manager) {
                $_tmp_sql_where .= " OR (branch_id IN ({$_manage_organ_ids_str}) AND seller IN ({$_manage_users_str}))";
            }
            // 调试使用
            if (request()->get('debug') == 'data') {
                print_r($manage_info);
                exit;
            }
            $params['where_sql_str'] = $_tmp_sql_where;
        }

        // todo: 获取统计数据
        if (in_array($params['type'], ['date', 'account', 'platform', 'seller', 'organ'])) {
            $data = OrderProfitLib::getInstance()->getProfitList($params, $params['type'], $this->profit_type == 'preprofit');
        }

        // todo: 针对不同的type，做不一样的处理
        if ($params['type'] == 'date') $this->_index_date($params, $data);
        if ($params['type'] == 'account' || $params['type'] == 'platform') $this->_index_account_or_platform($params, $data, $range);
        if ($params['type'] == 'seller') $this->_index_seller($params, $data, $range);

        // todo: 组织架构这个比较复杂，先获取所有销售员的数据，再手动统计，不然呢？
        if ($params['type'] == 'organ') $this->_index_organ($params, $data, $range);

        $this->_assignPagerData($this, $params, $data['count']);
        $this->assign('list', $data['list']);
        $this->assign('list_total', $data['count']);
        $this->assign('is_top_manager', $is_top_manager);
        $this->assign('range', $range);

        return $this->view->fetch("order/preprofit/index");
    }


    /**
     * 第二版 的 index，老板说 之前的 没什么用
     * @author lamkakyun
     * @date 2019-03-22 14:35:59
     * @return void
     */
    public function indexv2()
    {
        // var_dump($_SESSION);exit;   
        $rulelist = $this->auth->getRuleList();
        // echo '<pre>';var_dump($rulelist);echo '</pre>';
        // exit;

        $can_check_fee_detail = in_array('check_fee_detail', $rulelist);
        $this->assign('can_check_fee_detail', $can_check_fee_detail);

        $params = input('get.');
        $this->_index_init($params);

        // TODO: 需求， 按销售员 选择 其他部门，导出的是订单详情
        if ($params['type'] == 'seller' && isset($params['organ']) && $params['organ'][0] == '-1' && isset($params['is_export']) && $params['is_export'] == 1) 
        {
            return $this->_export_noseller_orders($params);
        }

        $range = range_day($params['scantime_end'], $params['scantime_start'], false);
        $day_count = count($range);

        // TODO: 如果是ERP 同步过来的用户(业务部)，只能看到自己的统计信息
        $is_top_manager = true; // 是否顶级的业务部管理者
        if ($this->auth->erp_id) {
            $manage_info    = OrgLib::getInstance()->getManageInfo($this->auth->username);
            $is_top_manager = $manage_info['is_top_manager'];
            $_current_user  = $manage_info['current_user_info'];
            // $_tmp_platform_accounts = explode(',', $manage_info['current_user_info']['ebayaccounts']);
            $_tmp_platform_accounts = $manage_info['manage_accounts'];
            $params['account']      = $params['account'] ?? $_tmp_platform_accounts;
            // $params['account'] = array_merge($params['account'], $_tmp_platform_accounts);

            // 为销售员报表 添加过滤
            $_is_manager = $manage_info['is_manager'];

            $_manage_users     = $manage_info['manage_users'];
            $_manage_users     = array_map(function ($v) {
                return "'{$v}'";
            }, $_manage_users);
            $_manage_users_str = implode(',', $_manage_users);

            $_manage_organ_ids     = $manage_info['manage_org_ids'];
            $_manage_organ_ids_str = implode(',', $_manage_organ_ids);

            $_belong_org_ids     = $manage_info['belong_org_ids'];
            $_belong_org_ids_str = implode(',', $_belong_org_ids);

            $_unmanage_org_ids     = array_diff($_belong_org_ids, $_manage_organ_ids);
            $_unmanage_org_ids_str = implode(',', $_unmanage_org_ids);

            $_tmp_sql_where = "seller = '{$_current_user['username']}'";
            if ($_unmanage_org_ids_str) $_tmp_sql_where .= " AND branch_id IN ({$_unmanage_org_ids_str})";
            if ($_is_manager) {
                $_tmp_sql_where .= " OR (branch_id IN ({$_manage_organ_ids_str}) AND seller IN ({$_manage_users_str}))";
            }
            // 调试使用
            if (request()->get('debug') == 'data') {
                print_r($manage_info);
                exit;
            }
            $params['where_sql_str'] = $_tmp_sql_where;
        }

         // todo: 获取统计数据
        $data = OrderProfitLib::getInstance()->getProfitListV2($params, $params['type'], $this->profit_type == 'preprofit');

        // if ($params['type'] == 'date') $this->_index_date($params, $data);

        switch ($params['type'])
        {
            case 'date':
                $total_key = 'days';
                $total_key_str = '日期';
                break;
            case 'account':
                $total_key = 'platform_account';
                $total_key_str = '账号';
                break;
            case 'platform':
                $total_key = 'platform';
                $total_key_str = '平台';
                break;
            case 'organ':
                $total_key = 'branch_id';
                $total_key_str = '组织架构';
                break;
            case 'seller':
                $total_key = 'seller';
                $total_key_str = '销售员';

                $org_parent_name_map = ToolsLib::getInstance()->getAllOrgParentNameMap();
                // todo:销售表 的前端需要的数据
                if ($this->auth->erp_id) {
                    $manage_info = OrgLib::getInstance()->getManageInfo($this->auth->username);
                    $sellers     = array_unique(array_merge($manage_info['manage_users'], [$manage_info['current_user_info']['username']]));
                } else {
                    $sellers = !empty($params['organ']) ? ToolsLib::getInstance()->getSellerByOrg(array_column(ToolsLib::getInstance()->getOrgById($params['organ']), 'name')) : [];
                }
                $this->assign('sellers', $sellers);

                break;
        }

        // echo '<pre>';var_dump($data['list']);echo '</pre>';
        // exit;

        switch ($params['type'])
        {
            case 'date':
                $tmp = $data['list'];
                $data['list'] = [];
                foreach ($tmp as $value)
                {
                    $tmp_key = "{$value['year']}-{$value['month']}-{$value['days']}";
                    $value['date'] = $tmp_key;
                    $data['list'][$tmp_key] = $value;
                }
                break;
                break;
            case 'account':
            case 'platform':
                $tmp = $data['list'];
                $data['list'] = [];
                foreach ($tmp as $value)
                {
                    $data['list'][$value[$total_key]] = $value;
                }
                break;
            case 'seller':
            case 'organ':
                $tmp = $data['list'];
                $data['list'] = [];

                $all_orgs = OrgLib::getInstance()->getBussinessOrgArray();
                $org_parent_name_map = ToolsLib::getInstance()->getAllOrgParentNameMap();

                foreach ($tmp as $value)
                {
                    $value['org_name'] = $all_orgs[$value['branch_id']]['name'] ?? '';
                    $value['org_parent_name'] = $org_parent_name_map[$value['org_name']] ?? '';
                    $value['full_org_name'] = "{$value['org_parent_name']}{$value['org_name']}";
                    $tmp_key = "{$value['seller']}___{$value['branch_id']}";
                    $data['list'][$tmp_key] = $value;
                }

                if ($params['type'] == 'organ')
                {
                    $data = $this->_reshapeSellerDataForOrganV2($params, $data);
                }

                break;
        }

        $total_data = [];
        foreach ($data['list'] as $key => $value)
        {
            // 发货率
            $data['list'][$key]['ship_rate'] = $data['list'][$key]['sum_totals'] == 0 ? '-' : round($data['list'][$key]['sum_ships'] / $data['list'][$key]['sum_totals'] * 100, 2) . '%';
            // 利润率
            $data['list'][$key]['profit_rate'] = $value['sum_sales'] == 0 ? '-' : round($value['sum_profit'] / $value['sum_sales'] * 100, 2) . '%';

            if ($params['type'] == 'organ' && $value['level'] != 2) continue;

            $total_data['sum_totals'] = isset($total_data['sum_totals']) ? $total_data['sum_totals'] += $value['sum_totals'] : $value['sum_totals'];
            $total_data['sum_ships'] = isset($total_data['sum_ships']) ? $total_data['sum_ships'] += $value['sum_ships'] : $value['sum_ships'];
            $total_data['sum_profit'] = isset($total_data['sum_profit']) ? $total_data['sum_profit'] += $value['sum_profit'] : $value['sum_profit'];
            $total_data['sum_sales'] = isset($total_data['sum_sales']) ? $total_data['sum_sales'] += $value['sum_sales'] : $value['sum_sales'];
            $total_data['sum_cost'] = isset($total_data['sum_cost']) ? $total_data['sum_cost'] += $value['sum_cost'] : $value['sum_cost'];
            $total_data['sum_carrier_freight'] = isset($total_data['sum_carrier_freight']) ? $total_data['sum_carrier_freight'] += $value['sum_carrier_freight'] : $value['sum_carrier_freight'];
            $total_data['sum_onlinefee'] = isset($total_data['sum_onlinefee']) ? $total_data['sum_onlinefee'] += $value['sum_onlinefee'] : $value['sum_onlinefee'];
            $total_data['sum_package_fee'] = isset($total_data['sum_package_fee']) ? $total_data['sum_package_fee'] += $value['sum_package_fee'] : $value['sum_package_fee'];
            $total_data['sum_platform_fee'] = isset($total_data['sum_platform_fee']) ? $total_data['sum_platform_fee'] += $value['sum_platform_fee'] : $value['sum_platform_fee'];
            $total_data['paypal_fee'] = isset($total_data['paypal_fee']) ? $total_data['paypal_fee'] += $value['paypal_fee'] : $value['paypal_fee'];
            $total_data['brokerage_fee'] = isset($total_data['brokerage_fee']) ? $total_data['brokerage_fee'] += $value['brokerage_fee'] : $value['brokerage_fee'];

            // $_s_t = $status_data[$value['platform']]['sum_totals'];
            // $_s_s = $status_data[$value['platform']]['sum_ships'];
            // $data['list'][$key]['sum_totals'] = $_s_t;
            // $data['list'][$key]['sum_ships'] = $_s_s;
        }
        if (!empty($total_data))
        {
            $total_data['ship_rate'] = (isset($total_data['sum_totals']) && $total_data['sum_totals'] == 0) ? '-' : round($total_data['sum_ships'] / $total_data['sum_totals'] * 100, 2) . '%';
            $total_data['profit_rate'] = (isset($total_data['sum_sales']) && $total_data['sum_sales'] == 0) ? '-' : round($total_data['sum_profit'] / $total_data['sum_sales'] * 100, 2) . '%';
        }
        
        $this->assign('total_data', $total_data);

        // todo: js 图表展示
        if ($params['model'] == 'chart') {

            $x_data       = array_keys($data['list']);
            if ($params['type'] == 'seller') $x_data = array_map(function($val){return trim(mb_substr($val, 0, 3), '_');}, $x_data);
            $chart_type   = 'bar';
            $x_data_names = ['总订单数', '已发货数', '发货率', '销售额($)', '成本($)', '物流运费($)', '线上运费($)', '包材费($)', '转换费($)', 'paypal费($)', '佣金($)', '利润', '利润率'];

            $y_data = [
                array_column($data['list'], 'sum_totals'),
                array_column($data['list'], 'sum_ships'),
                array_column($data['list'], 'ship_rate'),
                array_column($data['list'], 'sum_sales'),
                array_column($data['list'], 'sum_cost'),
                array_column($data['list'], 'sum_carrier_freight'),
                array_column($data['list'], 'sum_onlinefee'),
                
                
            ];
            if ($can_check_fee_detail)
            {
                $y_data[] = array_column($data['list'], 'sum_package_fee');
                $y_data[] = array_column($data['list'], 'sum_platform_fee');
                $y_data[] = array_column($data['list'], 'paypal_fee');
                $y_data[] = array_column($data['list'], 'brokerage_fee');
            }
            $y_data[] = array_column($data['list'], 'sum_profit');
            $y_data[] = array_column($data['list'], 'profit_rate');
            
            $this->assign('chart_type', $chart_type);
            $this->assign('x_data', json_encode($x_data));
            $this->assign('y_data', json_encode($y_data));
            $this->assign('x_data_names', json_encode($x_data_names));
        }

        // todo: 导出Excel
        if (isset($params['is_export']) && $params['is_export'] == 1)
        {
            $filename = "预利润报表(按" . $total_key_str . ")-" . date('Y-m-d');
            
            if ($params['type'] == 'seller')
            {
                $headers = ['full_org_name' => '组织架构', $total_key => $total_key_str];
            }
            elseif ($params['type'] == 'organ')
            {
                $headers = ['full_org_name' => '组织架构'];
            }
            elseif ($params['type'] == 'date')
            {
                $headers = ['date' => '日期'];
            }
            else
            {
                $headers = [$total_key => $total_key_str];
            }
            
            $headers['sum_totals']          = '总订单数';
            $headers['sum_ships']           = '已发货数';
            $headers['ship_rate']           = '发货率';
            $headers['sum_sales']           = '销售额($)';
            $headers['sum_cost']            = '成本($)';
            $headers['sum_carrier_freight'] = '物流运费($)';
            $headers['sum_onlinefee']       = '线上运费($)';
            if ($can_check_fee_detail)
            {
                $headers['sum_package_fee']     = '包材费($)';
                $headers['sum_platform_fee']    = '转换费($)';
                $headers['paypal_fee']          = 'paypal费($)';
                $headers['brokerage_fee']       = '佣金($)';
            }
            $headers['sum_profit']          = '利润';
            $headers['profit_rate']         = '利润率';
            ToolsLib::getInstance()->exportExcel($filename, $headers, $data['list'], false);
        }

        $this->_assignPagerData($this, $params, $data['count']);
        $this->assign('list', $data['list']);
        $this->assign('list_total', $data['count']);
        $this->assign('is_top_manager', $is_top_manager);
        
        // 按销售员 选择 其他部门，导出的是订单详情
        return $this->view->fetch("order/preprofit/indexv2");
    }


    /**
     * 
     * @author lamkakyun
     * @date 2019-03-26 17:11:41
     * @return void
     */
    private function _export_noseller_orders($params)
    {
        // 创建一个导出任务， 因为 订单 数据太多，不能直接导出，必须后台运行后，给予下载链接
        $task_data = [
            'task_name' => "导出预利润订单-部门【其他】-区间【{$params['scantime_start']}-{$params['scantime_end']}】" . date('Y-m-d H:i:s'),
            'start_time' => $params['scantime_start'],
            'end_time' => $params['scantime_end'],
            'order_status' => [2],
            'org_id' => [-1],
            'time_type' => 'createdtime',
            'priority' => 0,
            'create_userid' => $_SESSION['id'] ?? '0',
            'create_user'   => $_SESSION['truename'] ?? '系统默认',
            'create_time'   => time(),
        ];
        $ret = TaskLib::getInstance()->addTask($task_data);
        if ($ret['code'] == 0) $ret['msg'] = '创建导出任务成功，正在导出中，请稍后到数据明细导出下载';
        return json($ret);
    }


    /**
     * 获取上周的数据，或者，或者上个月的数据
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-31 02:55:25
     */
    private function _calc_data_day($params, &$data, $status_data)
    {
        $day_diff = day_diff($params['scantime_start'], $params['scantime_end']);

        // todo: 计算遗漏的单量增幅，销售额增幅，  获取上周同期单量， 因为最后的7天，获取不了，所以要单独获取
        // 上周的数据
        $_day_of_week = 7;
        if ($day_diff >= $_day_of_week) {
            $_7_start = date('Y-m-d', strtotime($params['scantime_start']) - $_day_of_week * 86400);
            $_7_end   = date('Y-m-d', strtotime($params['scantime_start']) - 1 * 86400);
        } else {
            $_7_start = date('Y-m-d', strtotime($params['scantime_start']) - $_day_of_week * 86400);
            $_7_end   = date('Y-m-d', strtotime($params['scantime_start']) - 1 * 86400);
        }
        $tmp_params                   = $params;
        $tmp_params['scantime_start'] = $_7_start;
        $tmp_params['scantime_end']   = $_7_end;
        $_7_day_data                  = OrderProfitLib::getInstance()->getProfitList($tmp_params, $tmp_params['type'], $this->profit_type == 'preprofit');
        $_7_day_data                  = $_7_day_data['list'];

        // 上一个月的数据
        $day_of_month = 31; // 默认一个月31 天，这样容错性比较高
        if ($day_diff >= $day_of_month) {
            $_31_start = date('Y-m-d', strtotime($params['scantime_start']) - $day_of_month * 86400);
            $_31_end   = date('Y-m-d', strtotime($params['scantime_start']) - 1 * 86400);
        } else {
            $_31_start = date('Y-m-d', strtotime($params['scantime_start']) - $day_of_month * 86400);
            $_31_end   = date('Y-m-d', strtotime($params['scantime_end']) - $day_of_month * 86400);
        }
        //            var_dump($_31_start, $_31_end);exit;
        $tmp_params                   = $params;
        $tmp_params['scantime_start'] = $_31_start;
        $tmp_params['scantime_end']   = $_31_end;
        $_31_day_data                 = OrderProfitLib::getInstance()->getProfitList($tmp_params, $tmp_params['type'], $this->profit_type == 'preprofit');
        $_31_day_data                 = $_31_day_data['list'];

        // todo: 计算利润率
        foreach ($data['list'] as $key => $value) {
            $_tmp_date = $value['year'] . '-' . $value['month'] . '-' . $value['days'];

            $data['list'][$key]['sum_totals']  = isset($status_data[$_tmp_date]) ? $status_data[$_tmp_date]['sum_totals'] : 0;
            $data['list'][$key]['sum_ships']   = isset($status_data[$_tmp_date]) ? $status_data[$_tmp_date]['sum_ships'] : 0;
            $data['list'][$key]['profit_rate'] = $value['sum_sales'] == 0 ? '-' : (round($value['sum_profit'] / $value['sum_sales'], 2) * 100) . '%';

            $_yesterday_key = date('Y-m-d', strtotime($key) - 1 * 86400);
            $_last_week_key = date('Y-m-d', strtotime($key) - 7 * 86400);

            $_last_month      = $value['month'] == 1 ? 12 : $value['month'] - 1;
            $_last_month_span = get_day_of_month($_last_month, $value['year']);

            $_last_month_key = date('Y-m-d', strtotime($key) - $_last_month_span * 86400);

            // todo: 计算 昨天
            $_yesterday = [];
            if (!isset($data['list'][$_yesterday_key])) {
                if (isset($_7_day_data[$_yesterday_key])) $_yesterday = $_7_day_data[$_yesterday_key];
            } else {
                $_yesterday = $data['list'][$_yesterday_key];
            }
            if ($_yesterday && $_yesterday['sum_profit']) {
                $data['list'][$key]['loop_profit_rate'] = (round(($value['sum_profit'] - $_yesterday['sum_profit']) / $_yesterday['sum_profit'], 2) * 100) . '%';
            } else {
                $data['list'][$key]['loop_profit_rate'] = '-';
            }

            // todo: 计算 上周
            $last_week = [];
            if (!isset($data['list'][$_last_week_key])) {
                if (isset($_7_day_data[$_last_week_key])) $last_week = $_7_day_data[$_last_week_key];
            } else {
                $last_week = $data['list'][$_last_week_key];
            }
            if ($last_week) {
                $data['list'][$key]['last_week_profit']      = $last_week['sum_profit'];
                $data['list'][$key]['last_week_profit_rate'] = $last_week['sum_sales'] == 0 ? '-' : (round($last_week['sum_profit'] / $last_week['sum_sales'], 2) * 100) . '%';
            } else {
                $data['list'][$key]['last_week_profit']      = '-';
                $data['list'][$key]['last_week_profit_rate'] = '-';
            }

            // todo: 计算 上月
            $last_month = [];
            if (!isset($data['list'][$_last_month_key])) {
                if (isset($_31_day_data[$_last_month_key])) $last_month = $_31_day_data[$_last_month_key];
            } else {
                $last_month = $data['list'][$_last_month_key];
            }
            if ($last_month) {
                $data['list'][$key]['last_month_profit']      = $last_month['sum_profit'];
                $data['list'][$key]['last_month_profit_rate'] = $last_month['sum_sales'] == 0 ? '-' : (round($last_month['sum_profit'] / $last_month['sum_sales'], 2) * 100) . '%';
            } else {
                $data['list'][$key]['last_month_profit']      = '-';
                $data['list'][$key]['last_month_profit_rate'] = '-';
            }
        }

    }

    /**
     * 获取上个月的数据
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-31 03:50:06
     */
    private function _calc_data_month($params, &$data, $status_data)
    {
        // todo: 获取最前面的一个月的数据
        $pre_month_date               = date('Y-m', strtotime($params['scandate_start']) - 86400);
        $tmp_params                   = $params;
        $tmp_params['scandate_start'] = $pre_month_date;
        $tmp_params['scandate_end']   = $pre_month_date;
        $pre_data                     = OrderProfitLib::getInstance()->getProfitList($tmp_params, $tmp_params['type'], $this->profit_type == 'preprofit');;
        // todo: 计算 各种 利润率
        foreach ($data['list'] as $key => $value) {
            $this_month_data = $value;
            $_tmp_date       = $value['year'] . '-' . $value['month'];

            $data['list'][$key]['profit_rate'] = $value['sum_sales'] == 0 ? '-' : (round($value['sum_profit'] / $value['sum_sales'], 2) * 100) . '%';
            $data['list'][$key]['sum_ships']   = isset($status_data[$_tmp_date]) ? $status_data[$_tmp_date]['sum_ships'] : 0;;
            $data['list'][$key]['sum_totals'] = isset($status_data[$_tmp_date]) ? $status_data[$_tmp_date]['sum_totals'] : 0;;

            $_last_month      = $value['month'] == 1 ? 12 : $value['month'] - 1;
            $_last_month_span = get_day_of_month($_last_month, $value['year']);
            $_last_month_key  = date('Y-m', strtotime($key) - $_last_month_span * 86400);
            $last_month_data  = [];
            if (!isset($data['list'][$_last_month_key])) {
                if (isset($pre_data[$_last_month_key])) $last_month_data = $pre_data[$_last_month_key];
            } else {
                $last_month_data = $data['list'][$_last_month_key];
            }

            // 因为一月份为计算，所以参考 _calc_data_day， yesterday 的写法
            if ($last_month_data) {
                $data['list'][$key]['loop_profit_rate']       = $last_month_data['sum_profit'] == 0 ? '-' : (round(($value['sum_profit'] - $last_month_data['sum_profit']) / $last_month_data['sum_profit'], 2) * 100) . '%';
                $data['list'][$key]['last_month_profit']      = $last_month_data['sum_profit'];
                $data['list'][$key]['last_month_profit_rate'] = $last_month_data['sum_sales'] == 0 ? '-' : (round($last_month_data['sum_profit'] / $last_month_data['sum_sales'], 2) * 100) . '%';
            } else {
                $data['list'][$key]['loop_profit_rate']       = '-';
                $data['list'][$key]['last_month_profit']      = '-';
                $data['list'][$key]['last_month_profit_rate'] = '-';
            }
        }
    }

    /**
     * 从index 方法，分出来的方法
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-29 12:00:20
     */
    private function _index_date($params, &$data)
    {
        if (isset($params['account'])) $params['platform_account'] = $params['account'];
        $tmp_data    = OrderLib::getInstance()->getOrderStatusList($params, $params['type']);
        $status_data = [];
        foreach ($tmp_data['list'] as $value) {
            $tmp_key               = $params['checkDate'] == 'day' ? $value['year'] . '-' . $value['month'] . '-' . $value['days'] : $value['year'] . '-' . $value['month'];
            $status_data[$tmp_key] = $value;
        }

        // todo: 1.需要添加 总单量 已发货单量
        // todo: 2.计算 上周同期利润，上月同日利润， 利润率(%)， 上周同期利润率(%)， 上月同日利润率(%)，利润环比增长率(%)
        if ($params['checkDate'] == 'day') $this->_calc_data_day($params, $data, $status_data);
        if ($params['checkDate'] == 'month') $this->_calc_data_month($params, $data, $status_data);

        // todo: 计算总量
        $sum_function                    = function ($v1, $v2) {
            return $v1 + $v2;
        };
        $total_data                      = [];
        $total_data['sum_totals']        = array_reduce(array_column($data['list'], 'sum_totals'), $sum_function);
        $total_data['sum_ships']         = array_reduce(array_column($data['list'], 'sum_ships'), $sum_function);
        $total_data['sum_sales']         = array_reduce(array_column($data['list'], 'sum_sales'), $sum_function);
        $total_data['sum_profit']        = array_reduce(array_column($data['list'], 'sum_profit'), $sum_function);
        $total_data['sum_profit_totals'] = array_reduce(array_column($data['list'], 'sum_profit_totals'), $sum_function);
        $this->assign('total_data', $total_data);

        // todo: 为图表 构造数据
        if ($params['model'] == 'chart') {
            $chart_type = 'line';
            $x_data     = [];
            foreach ($data['list'] as $value) {
                $tmp_str = "{$value['year']}-{$value['month']}";
                if ($params['checkDate'] == 'day') $tmp_str .= "-{$value['days']}";
                $x_data[] = $tmp_str;
            }
            $x_data_names = ['总单量', '已发货单量', '销售额($)', '利润($)'];
            $y_data[]     = array_column($data['list'], 'sum_totals');
            $y_data[]     = array_column($data['list'], 'sum_ships');
            $y_data[]     = array_column($data['list'], 'sum_sales');
            $y_data[]     = array_column($data['list'], 'sum_profit');

            $this->assign('chart_type', $chart_type);
            $this->assign('x_data', json_encode($x_data));
            $this->assign('y_data', json_encode($y_data));
            $this->assign('x_data_names', json_encode($x_data_names));
        }

        // todo: 导出报表
        if (isset($params['is_export']) && $params['is_export'] == 1) {
            $filename    = "预利润报表(按日期)-" . date('Y-m-d');
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
                'date'                   => '日期',
                'sum_totals'             => '总单量',
                'sum_ships'              => '已发货单量',
                'sum_sales'              => '销售额($)',
                'sum_profit'             => '利润($)',
                'last_month_profit'      => '上月同日利润',
                'profit_rate'            => '利润率(%)',
                'last_month_profit_rate' => '上月同日利润率(%)',
                'loop_profit_rate'       => '利润环比增长率(%)',
            ];
            if ($params['checkDate'] == 'day') {
                $headers['last_week_profit']      = '上周同期利润';
                $headers['last_week_profit_rate'] = '上周同期利润率(%)';
            }

            ToolsLib::getInstance()->exportExcel($filename, $headers, $export_data, false);
        }
    }


    /**
     * 从index 方法，分出来的方法
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-29 12:00:20
     */
    private function _index_account_or_platform($params, &$data, $range)
    {
        // todo: 合计数，平均数
        $date_total_map = [];
        $date_aver_map  = [];
        foreach ($data['list'] as $key => $value) {
            $date_total_map[$key]['sum_profit'] = 0;
            $date_total_map[$key]['sum_sales']  = 0;
            foreach ($value as $k => $v) {
                $date_total_map[$key]['sum_profit'] += $v['sum_profit'];
                $date_total_map[$key]['sum_sales']  += $v['sum_sales'];
            }
        }
        foreach ($date_total_map as $key => $value) {
            $date_aver_map[$key]['average_profit']      = round($value['sum_profit'] / count($range), 4);
            $date_aver_map[$key]['average_profit_rate'] = $value['sum_sales'] == 0 ? '-' : round($value['sum_profit'] / $value['sum_sales'] * 100, 4) . '%';
        }

        $this->assign('date_total_map', $date_total_map);
        $this->assign('date_aver_map', $date_aver_map);

        // todo: 合并重单量，发货单量
        $tmp_key  = $params['type'] == 'account' ? 'account_date' : 'platform_date';
        $tmp_data = OrderLib::getInstance()->getOrderStatusList($params, $tmp_key);
        //        var_dump($tmp_data);exit;
        $status_data = [];
        foreach ($tmp_data['list'] as $key => $value) {
            // 每一天，同一个用户 只有 唯一的一条数据, 所以用下面这个key
            $_tmp_key     = $params['type'] == 'account' ? 'platform_account' : 'platform';
            $_date_format = $params['checkDate'] == 'day' ? "{$value['year']}-{$value['month']}-{$value['days']}" : "{$value['year']}-{$value['month']}";

            $status_data["{$_date_format}-{$value[$_tmp_key]}"] = $value;
        }
        //        var_dump($status_data);exit;

        foreach ($data['list'] as $key => $value) {
            foreach ($value as $k => $v) {
                $tmp_key = "{$k}-{$key}";
                if (isset($status_data[$tmp_key])) {
                    $data['list'][$key][$k]['sum_totals'] = $status_data[$tmp_key]['sum_totals'];
                    $data['list'][$key][$k]['sum_ships']  = $status_data[$tmp_key]['sum_ships'];
                } else {
                    $data['list'][$key][$k]['sum_totals'] = 0;
                    $data['list'][$key][$k]['sum_ships']  = 0;
                }
                // 发货比率
                $data['list'][$key][$k]['ship_rate'] = $data['list'][$key][$k]['sum_totals'] == 0 ? '-' : round($data['list'][$key][$k]['sum_ships'] / $data['list'][$key][$k]['sum_totals'] * 100, 2) . '%';
                // 利润率
                $data['list'][$key][$k]['profit_rate'] = $data['list'][$key][$k]['sum_sales'] == 0 ? '-' : round($data['list'][$key][$k]['sum_profit'] / $data['list'][$key][$k]['sum_sales'] * 100, 2) . '%';
            }
        }

        // TODO: 平台需要 添加 合计栏 （task 1097）(task 1131)
        if ($params['type'] == 'platform' || $params['type'] == 'account') {

            $total_data = [];
            // todo: 计算合计数
            foreach ($data['list'] as $_tmp_platform_data) {
                foreach ($_tmp_platform_data as $_tmp_date => $_tmp_value) {
                    if ($params['checkDate'] == 'day') $_tmp_date = substr($_tmp_date, 5);
                    if (!isset($total_data[$_tmp_date]['sum_sales'])) $total_data[$_tmp_date]['sum_sales'] = 0;
                    if (!isset($total_data[$_tmp_date]['sum_profit'])) $total_data[$_tmp_date]['sum_profit'] = 0;
                    if (!isset($total_data[$_tmp_date]['sum_totals'])) $total_data[$_tmp_date]['sum_totals'] = 0;
                    if (!isset($total_data[$_tmp_date]['sum_ships'])) $total_data[$_tmp_date]['sum_ships'] = 0;

                    $total_data[$_tmp_date]['sum_sales']  += $_tmp_value['sum_sales'];
                    $total_data[$_tmp_date]['sum_profit'] += $_tmp_value['sum_profit'];
                    $total_data[$_tmp_date]['sum_totals'] += $_tmp_value['sum_totals'];
                    $total_data[$_tmp_date]['sum_ships']  += $_tmp_value['sum_ships'];
                }
            }

            foreach ($total_data as $key => $value) {
                $total_data[$key]['ship_rate']   = $value['sum_totals'] == 0 ? '-' : round($value['sum_ships'] / $value['sum_totals'] * 100, 2) . '%';
                $total_data[$key]['profit_rate'] = $value['sum_sales'] == 0 ? '-' : round($value['sum_profit'] / $value['sum_sales'] * 100, 2) . '%';
            }
            $this->assign('total_data', $total_data);
        }

        //        var_dump($data);exit;

        // todo: 为导出和图表做准备
        $export_data = [];
        if ($params['model'] == 'chart' || (isset($params['is_export']) && $params['is_export'] == 1)) {
            foreach ($data['list'] as $key => $value) {
                $_tmp                                                                 = [];
                $_tmp[$params['type'] == 'account' ? 'platform_account' : 'platform'] = $key;
                $date_format                                                          = $params['checkDate'] == 'day' ? 'm-d' : 'Y-m';
                foreach ($value as $k => $v) {
                    $_tmp_date = date($date_format, strtotime($k));
                    $_tmp[$_tmp_date . ' 总利润'] = $v['sum_profit'];
                    $_tmp[$_tmp_date . ' 利润率'] = $v['profit_rate'];
                    // $_tmp[date($date_format, strtotime($k))] = $v['sum_profit'];
                }
                $_tmp['sum_profit']          = $date_total_map[$key]['sum_profit'];
                $_tmp['average_profit']      = $date_aver_map[$key]['average_profit'];
                $_tmp['average_profit_rate'] = $date_aver_map[$key]['average_profit_rate'];
                $export_data[]               = $_tmp;
            }
        }

        // todo: js 图表展示
        if ($params['model'] == 'chart') {

            $x_data       = array_keys($data['list']);
            $chart_type   = 'bar';
            $x_data_names = array_merge($range, ['合计', '当前平均利润']);


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
            $filename = "预利润报表(按" . ($params['type'] == 'account' ? '账号' : '平台') . ")-" . date('Y-m-d');

            $tmp_key = $params['type'] == 'account' ? 'platform_account' : 'platform';
            $headers = [$tmp_key => ($params['type'] == 'account') ? '账号' : '平台'];
            foreach ($range as $value) {
                $_tmp_key1 = $value . ' 总利润';
                $_tmp_key2 = $value . ' 利润率';
                $headers[$_tmp_key1] = $_tmp_key1;
                $headers[$_tmp_key2] = $_tmp_key2;
                // $headers[$value] = $value;
            }
            $headers['sum_profit']          = '合计';
            $headers['average_profit']      = '当前平均利润';
            $headers['average_profit_rate'] = '当前平均利润率';

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
        // todo:销售表 的前端需要的数据
        if ($this->auth->erp_id) {
            $manage_info = OrgLib::getInstance()->getManageInfo($this->auth->username);
            $sellers     = array_unique(array_merge($manage_info['manage_users'], [$manage_info['current_user_info']['username']]));
        } else {
            $sellers = !empty($params['organ']) ? ToolsLib::getInstance()->getSellerByOrg(array_column(ToolsLib::getInstance()->getOrgById($params['organ']), 'name')) : [];
        }
        $this->assign('sellers', $sellers);

        // todo: 获取销售员的组织架构名称, 直接放到 key 后面（因为不清楚，是否有组织id，没有组织id，就没有组织架构，导致显示问题）
        $all_orgs     = ToolsLib::getInstance()->getAllOrg(1);
        $tmp          = $data['list'];
        $data['list'] = [];
        foreach ($tmp as $key => $value) {
            $seller_and_org_id = explode('___', $key);
            $tmp_seller        = $seller_and_org_id[0];
            $tmp_org_id        = $seller_and_org_id[1];
            $tmp_org_name      = '';
            if ($tmp_org_id != 0) {
                $_tmp_org     = $all_orgs[$tmp_org_id];
                $tmp_org_name = $_tmp_org['name'];
            }
            foreach ($value as $k => $v) {
                $value[$k]['org_name']        = $tmp_org_name;
                $value[$k]['org_parent_name'] = $tmp_org_name ? ($org_parent_name_map[$tmp_org_name] ?? '') : '';
            }
            $data['list'][$key] = $value;
        }

        ksort($data['list']);

        $total_data = [];
        // todo: 计算合计数(按日期)
        foreach ($data['list'] as $_tmp_platform_data) {
            foreach ($_tmp_platform_data as $_tmp_date => $_tmp_value) {
                if ($params['checkDate'] == 'day') $_tmp_date = substr($_tmp_date, 5);
                if (!isset($total_data[$_tmp_date]['sum_sales'])) $total_data[$_tmp_date]['sum_sales'] = 0;
                if (!isset($total_data[$_tmp_date]['sum_profit'])) $total_data[$_tmp_date]['sum_profit'] = 0;
                if (!isset($total_data[$_tmp_date]['sum_profit_totals'])) $total_data[$_tmp_date]['sum_profit_totals'] = 0;
                if (!isset($total_data[$_tmp_date]['sum_totals'])) $total_data[$_tmp_date]['sum_totals'] = 0;
                if (!isset($total_data[$_tmp_date]['sum_ships'])) $total_data[$_tmp_date]['sum_ships'] = 0;

                $total_data[$_tmp_date]['sum_sales']         += $_tmp_value['sum_sales'];
                $total_data[$_tmp_date]['sum_profit']        += $_tmp_value['sum_profit'];
                $total_data[$_tmp_date]['sum_profit_totals'] += $_tmp_value['sum_profit_totals'];
                $total_data[$_tmp_date]['sum_totals']        += $_tmp_value['sum_totals'];
                $total_data[$_tmp_date]['sum_ships']         += $_tmp_value['sum_ships'];
            }
        }

        foreach ($total_data as $key => $value) {
            $total_data[$key]['ship_rate']   = $value['sum_totals'] == 0 ? '-' : round($value['sum_ships'] / $value['sum_totals'] * 100, 2) . '%';
            $total_data[$key]['profit_rate'] = $value['sum_sales'] == 0 ? '-' : round($value['sum_profit'] / $value['sum_sales'] * 100, 2) . '%';
        }
        $this->assign('total_data', $total_data);

        // todo: 合计数，平均数 (按销售员)
        $date_total_map = [];
        $date_aver_map  = [];
        foreach ($data['list'] as $key => $value) {
            $date_total_map[$key]['sum_profit'] = 0;
            $date_total_map[$key]['sum_sales']  = 0;
            foreach ($value as $k => $v) {
                $date_total_map[$key]['sum_profit'] += $v['sum_profit'];
                $date_total_map[$key]['sum_sales']  += $v['sum_sales'];
            }
        }
        foreach ($date_total_map as $key => $value) {
            $date_aver_map[$key]['average_profit']      = round($value['sum_profit'] / count($range), 4);
            $date_aver_map[$key]['average_profit_rate'] = $value['sum_sales'] == 0 ? '-' : round($value['sum_profit'] / $value['sum_sales'] * 100, 4) . '%';
        }

        $this->assign('date_total_map', $date_total_map);
        $this->assign('date_aver_map', $date_aver_map);

        foreach ($data['list'] as $key => $value) {
            foreach ($value as $k => $v) {
                // 发货比率
                $data['list'][$key][$k]['ship_rate'] = $data['list'][$key][$k]['sum_totals'] == 0 ? '-' : round($data['list'][$key][$k]['sum_ships'] / $data['list'][$key][$k]['sum_totals'] * 100, 2) . '%';
                // 利润率
                $data['list'][$key][$k]['profit_rate'] = $data['list'][$key][$k]['sum_sales'] == 0 ? '-' : round($data['list'][$key][$k]['sum_profit'] / $data['list'][$key][$k]['sum_sales'] * 100, 2) . '%';
            }
        }

        // todo: 为导出和图表做准备
        $export_data = [];
        if ($params['model'] == 'chart' || (isset($params['is_export']) && $params['is_export'] == 1)) {
            foreach ($data['list'] as $key => $value) {
                $_tmp           = [];
                $_tmp['seller'] = preg_replace('/___\d+/', '', $key);
                // $_tmp['organ_name'] = 
                $date_format    = $params['checkDate'] == 'day' ? 'm-d' : 'Y-m';
                $_first_val = reset($value);
                $_tmp['full_organ_name'] = $_first_val['org_parent_name'] . $_first_val['org_name'];

                foreach ($value as $k => $v) {
                    $_tmp_date = date($date_format, strtotime($k));
                    $_tmp[$_tmp_date . ' 总利润'] = $v['sum_profit'];
                    $_tmp[$_tmp_date . ' 利润率'] = $v['profit_rate'];
                    // $_tmp[$_tmp_date] = "{$v['sum_profit']}/{$v['profit_rate']}";
                }
                $_tmp['sum_profit']          = $date_total_map[$key]['sum_profit'];
                $_tmp['average_profit']      = $date_aver_map[$key]['average_profit'];
                $_tmp['average_profit_rate'] = $date_aver_map[$key]['average_profit_rate'];
                $export_data[]               = $_tmp;
            }
        }

        // todo: js 图表展示
        if ($params['model'] == 'chart') {

            $x_data       = array_keys($data['list']);
            $x_data       = array_map(function($val) {return preg_replace('/___\d+/', '', $val);}, $x_data);
            $chart_type   = 'bar';
            $x_data_names = array_merge($range, ['合计', '当前平均利润']);


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
            $filename = "预利润报表(按销售员)-" . date('Y-m-d');

            $headers = ['full_organ_name' => '组织架构', 'seller' => '销售员'];
            foreach ($range as $value) {
                $_tmp_key1 = $value . ' 总利润';
                $_tmp_key2 = $value . ' 利润率';
                $headers[$_tmp_key1] = $_tmp_key1;
                $headers[$_tmp_key2] = $_tmp_key2;
                // $headers[$value] = $value;
            }

            $headers['sum_profit']          = '合计';
            $headers['average_profit']      = '当前平均利润';
            $headers['average_profit_rate'] = '当前平均利润率';

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

        $export_data = [];
        if ($params['model'] == 'chart' || (isset($params['is_export']) && $params['is_export'] == 1)) {
            foreach ($data['list'] as $key => $value) {
                $_tmp               = [];
                $_tmp['organ_name'] = $value['organ_name'];
                $date_format        = $params['checkDate'] == 'day' ? 'm-d' : 'Y-m';
                foreach ($value['dates'] as $k => $v) {
                    // $_tmp[date($date_format, strtotime($k))] = $v['sum_sales'];
                    // $_tmp[date($date_format, strtotime($k))] = $v['sum_profit'];
                    $_tmp_date = date($date_format, strtotime($k));
                    $_tmp[$_tmp_date . ' 总利润'] = $v['sum_profit'];
                    $_tmp[$_tmp_date . ' 利润率'] = $v['profit_rate'];
                }
                $_tmp['average_profit']      = $value['average_profit'];
                $_tmp['average_profit_rate'] = $value['average_profit_rate'];
                $_tmp['all_profit']          = $value['all_profit'];
                $export_data[]               = $_tmp;
            }
        }

        // TODO: 计算合计数
        $level1_orgs_ids = array_column(ToolsLib::getInstance()->getLevel1Orgs(), 'id');
        $total_data      = [];
        foreach ($data['list'] as $_tmp_organ_data) {
            if (!in_array($_tmp_organ_data['org_id'], $level1_orgs_ids)) continue;
            foreach ($_tmp_organ_data['dates'] as $_tmp_date => $value) {
                if ($params['checkDate'] == 'day') $_tmp_date = substr($_tmp_date, 5);
                if (!isset($total_data[$_tmp_date]['sum_totals'])) $total_data[$_tmp_date]['sum_totals'] = 0;
                if (!isset($total_data[$_tmp_date]['sum_sales'])) $total_data[$_tmp_date]['sum_sales'] = 0;
                if (!isset($total_data[$_tmp_date]['sum_profit'])) $total_data[$_tmp_date]['sum_profit'] = 0;
                if (!isset($total_data[$_tmp_date]['sum_ships'])) $total_data[$_tmp_date]['sum_ships'] = 0;
                // if (!isset($total_data[$_tmp_date]['ship_rate'])) $total_data[$_tmp_date]['ship_rate'] = 0;
                // if (!isset($total_data[$_tmp_date]['profit_rate'])) $total_data[$_tmp_date]['profit_rate'] = 0;
                $total_data[$_tmp_date]['sum_totals'] += $value['sum_totals'];
                $total_data[$_tmp_date]['sum_sales']  += $value['sum_sales'];
                $total_data[$_tmp_date]['sum_profit'] += $value['sum_profit'];
                $total_data[$_tmp_date]['sum_ships']  += $value['sum_ships'];
                // $total_data[$_tmp_date]['ship_rate'] += $value['ship_rate'];
                // $total_data[$_tmp_date]['profit_rate'] += $value['profit_rate'];
            }
        }
        foreach ($total_data as $k => $v) {
            $total_data[$k]['ship_rate']   = $v['sum_totals'] == 0 ? '-' : round($v['sum_ships'] / $v['sum_totals'], 2) * 100 . '%';
            $total_data[$k]['profit_rate'] = $v['sum_sales'] == 0 ? '-' : round($v['sum_profit'] / $v['sum_sales'], 2) * 100 . '%';
        }
        $this->assign('total_data', $total_data);


        // todo: js 图表展示
        if ($params['model'] == 'chart') {
            // 组织架构 的图表好像没什么用，不做了
        }

        // todo: 导出Excel
        if (isset($params['is_export']) && $params['is_export'] == 1) {
            $filename = "预利润报表(按组织架构)-" . date('Y-m-d');

            $headers = ['organ_name' => '组织架构'];
            foreach ($range as $value) {
                $_tmp_key1 = $value . ' 总利润';
                $_tmp_key2 = $value . ' 利润率';
                $headers[$_tmp_key1] = $_tmp_key1;
                $headers[$_tmp_key2] = $_tmp_key2;
                // $headers[$value] = $value;
            }
            $headers['all_profit']          = '合计';
            $headers['average_profit']      = '当前平均利润';
            $headers['average_profit_rate'] = '当前平均利润率';
            ToolsLib::getInstance()->exportExcel($filename, $headers, $export_data, false);
        }
    }


    



    /**
     * 将销售员数据重组，给组织架构用
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-26 06:38:06
     * $data array: erp_order_seller 取出来的数据
     * return : 返回以组织架构的销售数据
     */
    private function _reshapeSellerDataForOrgan($data, $params)
    {
        //        var_dump($data);exit;
        if ($params['checkDate'] == 'day') $tmp_range = range_day($params['scantime_end'], $params['scantime_start']);
        else $tmp_range = range_month($params['scandate_end'], $params['scandate_start']);

        $days_num = count($tmp_range);

        $zero_total = [];
        foreach ($tmp_range as $tmp_v) {
            $zero_total[$tmp_v] = ['sum_sales' => '0.00', 'sum_profit' => '0.00', 'sum_totals' => '0', 'sum_ships' => '0'];
        }

        $org_tree = ToolsLib::getInstance()->getBusinessOrgTree();
        $org_arr  = ToolsLib::getInstance()->treeToArray($org_tree);

        $all_sellers = array_keys($data['list']);

        $manage_org_ids = array_column($org_arr, 'id');
        if ($this->auth->erp_id) {
            $manage_info    = OrgLib::getInstance()->getManageInfo($this->auth->username);
            $manage_org_ids = $manage_info['manage_org_ids'];
        }

        $tmp = $org_arr;
        foreach ($tmp as $key => $value) {
            if (!in_array($value['id'], $manage_org_ids)) {
                unset($org_arr[$key]);
                continue;
            }
            // todo: 定义默认参数
            $org_arr[$key]['org_id']     = $value['id'];
            $org_arr[$key]['organ_name'] = $value['name'];
            $org_arr[$key]['username']   = $value['manage'];

            $org_arr[$key]['dates']               = $zero_total;
            $org_arr[$key]['all_profit']          = '0.00';
            $org_arr[$key]['average_profit']      = '0.00';
            $org_arr[$key]['average_profit_rate'] = '-';

            if (!$value['seller_list']) continue;
            $seller_intersect = array_intersect($all_sellers, $value['seller_list']);
            if (!$seller_intersect) continue;

            $tmp_total  = $zero_total;
            $all_profit = '0.00';
            $all_sales  = '0.00'; // 总销售额

            foreach ($seller_intersect as $v) {
                $seller_sale_data = $data['list'][$v];
                foreach ($seller_sale_data as $_date => $sale_data) {
                    $tmp_total[$_date]['sum_profit'] += $sale_data['sum_profit'];
                    $tmp_total[$_date]['sum_sales']  += $sale_data['sum_sales'];
                    $tmp_total[$_date]['sum_totals'] += $sale_data['sum_totals'];
                    $tmp_total[$_date]['sum_ships']  += $sale_data['sum_ships'];
                    $all_profit                      += $sale_data['sum_profit'];
                    $all_sales                       += $sale_data['sum_sales'];
                }
            }

            $org_arr[$key]['dates']               = $tmp_total;
            $org_arr[$key]['all_profit']          = $all_profit;
            $org_arr[$key]['average_profit']      = round($all_profit / $days_num, 4);
            $org_arr[$key]['average_profit_rate'] = $all_sales ? round($all_profit / $all_sales * 100, 4) . '%' : '-';
        }

        // todo: 计算发货率,利润率
        foreach ($org_arr as $key => $value) {
            foreach ($value['dates'] as $k => $v) {
                $org_arr[$key]['dates'][$k]['ship_rate']   = $v['sum_totals'] == 0 ? '-' : round($v['sum_ships'] / $v['sum_totals'], 2) * 100 . '%';
                $org_arr[$key]['dates'][$k]['profit_rate'] = $v['sum_sales'] == 0 ? '-' : round($v['sum_profit'] / $v['sum_sales'], 2) * 100 . '%';
            }
        }
        return ['list' => $org_arr, 'count' => count($org_arr)];
    }

    private function _reshapeSellerDataForOrganV2($params, $data)
    {
        $all_orgs = OrgLib::getInstance()->getBussinessOrgArray();
        $org_parent_name_map = ToolsLib::getInstance()->getAllOrgParentNameMap();

        $manage_org_ids = array_column($all_orgs, 'id');
        if ($this->auth->erp_id) {
            $manage_info    = OrgLib::getInstance()->getManageInfo($this->auth->username);
            $manage_org_ids = $manage_info['manage_org_ids'];
        }

        $full_child_ids = $all_orgs[19]['full_child_ids'];
        $ids = array_unique(array_column($data['list'], 'branch_id'));

        // var_dump($full_child_ids, $ids);exit;
        // var_dump($full_child_ids, array_diff($ids, $full_child_ids));exit;

        $default_data = [
            'sum_totals'          => 0,
            'sum_ships'           => 0,
            'sum_profit'          => 0,
            'sum_sales'           => 0,
            'sum_profit_totals'   => 0,
            'sum_cost'            => 0,
            'sum_carrier_freight' => 0,
            'sum_onlinefee'       => 0,
            'sum_package_fee'     => 0,
            'sum_platform_fee'     => 0,
            'paypal_fee'          => 0,
            'brokerage_fee'       =>  0,
        ];

        unset($all_orgs[19]);

        $all_orgs['-1'] = [
            'id' => '-1',
            'name' => '其他',
            'org_name' => '其他',
            'level' => 2,
            'org_parent_name' => '',
            'full_org_name' => '',

        ];

        // echo '<pre>';var_dump($data);echo '</pre>';
        // exit;
        $tmp = $all_orgs;
        foreach ($tmp as $key => $value)
        {
            if (!in_array($value['id'], $manage_org_ids))
            {
                unset($all_orgs[$key]);
                continue;
            }
            // 删除没必要的数据
            unset($all_orgs[$key]['org_full_user_account_list']);
            unset($all_orgs[$key]['child_ids']);
            unset($all_orgs[$key]['children']);

            $all_orgs[$key]['org_parent_name'] = $org_parent_name_map[$value['name']] ?? '';
            $all_orgs[$key]['full_org_name'] = $all_orgs[$key]['org_parent_name'] . $value['name'];
            $all_orgs[$key]['org_name'] = $value['name'];
            $all_orgs[$key] = array_merge($all_orgs[$key], $default_data);


            if ($value['id'] == '-1') 
            {
                foreach ($data['list'] as $k => $v)
                {
                    if ($v['branch_id'] != 0) continue;

                    $all_orgs['-1']['sum_totals']          += $v['sum_totals'];
                    $all_orgs['-1']['sum_ships']           += $v['sum_ships'];
                    $all_orgs['-1']['sum_profit']          += $v['sum_profit'];
                    $all_orgs['-1']['sum_sales']           += $v['sum_sales'];
                    $all_orgs['-1']['sum_profit_totals']   += $v['sum_profit_totals'];
                    $all_orgs['-1']['sum_cost']            += $v['sum_cost'];
                    $all_orgs['-1']['sum_carrier_freight'] += $v['sum_carrier_freight'];
                    $all_orgs['-1']['sum_onlinefee']       += $v['sum_onlinefee'];
                    $all_orgs['-1']['sum_package_fee']     += $v['sum_package_fee'];
                    $all_orgs['-1']['sum_platform_fee']    += $v['sum_platform_fee'];
                    $all_orgs['-1']['paypal_fee']          += $v['paypal_fee'];
                    $all_orgs['-1']['brokerage_fee']       += $v['brokerage_fee'];
                }
            }
            else
            {
                foreach ($value['org_full_user_list'] as $v)
                {
                    $tmp_key = "{$v['user_name']}___{$v['organize_id']}";
                    if (isset($data['list'][$tmp_key])) 
                    {
                        $all_orgs[$key]['sum_totals']          += $data['list'][$tmp_key]['sum_totals'];
                        $all_orgs[$key]['sum_ships']           += $data['list'][$tmp_key]['sum_ships'];
                        $all_orgs[$key]['sum_profit']          += $data['list'][$tmp_key]['sum_profit'];
                        $all_orgs[$key]['sum_sales']           += $data['list'][$tmp_key]['sum_sales'];
                        $all_orgs[$key]['sum_profit_totals']   += $data['list'][$tmp_key]['sum_profit_totals'];
                        $all_orgs[$key]['sum_cost']            += $data['list'][$tmp_key]['sum_cost'];
                        $all_orgs[$key]['sum_carrier_freight'] += $data['list'][$tmp_key]['sum_carrier_freight'];
                        $all_orgs[$key]['sum_onlinefee']       += $data['list'][$tmp_key]['sum_onlinefee'];
                        $all_orgs[$key]['sum_package_fee']     += $data['list'][$tmp_key]['sum_package_fee'];
                        $all_orgs[$key]['sum_platform_fee']    += $data['list'][$tmp_key]['sum_platform_fee'];
                        $all_orgs[$key]['paypal_fee']          += $data['list'][$tmp_key]['paypal_fee'];
                        $all_orgs[$key]['brokerage_fee']       += $data['list'][$tmp_key]['brokerage_fee'];
                    }
                }
            }

            
            unset($all_orgs[$key]['org_full_user_list']);
        }

        return ['list' => $all_orgs, 'count' => count($all_orgs)];
    }

}