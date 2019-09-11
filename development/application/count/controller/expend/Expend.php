<?php

namespace app\count\controller\expend;

use app\count\library\OrgLib;
use app\common\library\ToolsLib;
use app\count\library\order\OrderLib;
use app\count\library\finance\ExpendLib;
use app\common\controller\AuthController;


/**
 * 费用管理
 */
class Expend extends AuthController
{
    public function __construct()
    {
        parent::__construct();
        $this->manage_info = $this->auth->erp_id ? OrgLib::getInstance()->getManageInfo($this->auth->username) : false;
    }

    /**
     * 列表 (这个方法和 Sale.php 的index 差不多，但又不一样,没办法，重新写)
     * @access auth
     * @author lamkakyun
     * @date 2019-04-01 20:06:13
     * @return void
     */
    public function index()
    {
        $params = array_merge(input('get.'), input('post.'));
        if (isset($params['s'])) unset($params['s']);

        if (!isset($params['type'])) $params['type'] = 'platform';
        if (!isset($params['checkDate'])) $params['checkDate'] = 'day';
        if (!isset($params['scandate_start'])) $params['scandate_start'] = date('Y-m');
        if (!isset($params['scandate_end'])) $params['scandate_end'] = date('Y-m');
        if (!isset($params['scantime_start'])) $params['scantime_start'] = date('Y-m-d', strtotime('-7 day'));
        if (!isset($params['scantime_end'])) $params['scantime_end'] = date('Y-m-d');

        if ($params['checkDate'] == 'day' && strtotime($params['scantime_start']) > strtotime($params['scantime_end'])) return $this->error('开始时间不能大于结束时间');
        if ($params['checkDate'] == 'month' && strtotime($params['scandate_start']) > strtotime($params['scandate_end'])) return $this->error('开始时间不能大于结束时间');

        $params['sort']       = $params['sort'] ?? 'desc';
        $params['sort_field'] = $params['sort_field'] ?? 'year, month, days';

        $params['platform'] = $params['platform'] ?? null; // null 似乎好用一点
        if (!empty($params['seller'])) {
            $params['seller'] = is_array($params['seller']) ? $params['seller'] : explode(',', $params['seller']);
            $params['seller'] = array_filter($params['seller'], function ($val) {
                return !empty($val);
            });
        }

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

        $org_list = OrgLib::getInstance()->getTopBussinessOrgs($this->manage_info);

        // todo: 需求， 默认展示第一个部门
        if ($params['type'] == 'seller' && !isset($params['organ'])) {
            $params['organ'][] = array_column($org_list, 'id')[0];
        }

        if ($params['checkDate'] == 'day') {
            $range = range_day($params['scantime_end'], $params['scantime_start'], false);
        } else {
            $range = range_month($params['scandate_end'], $params['scandate_start']);
        }

        // TODO: 如果是ERP 同步过来的用户(业务部)，只能看到自己的统计信息
        $is_top_manager = true; // 是否顶级的业务部管理者
        if ($this->auth->erp_id) {
            // 为销售报表 添加过滤
            $manage_info    = $this->manage_info;
            $is_top_manager = $manage_info['is_top_manager'];
            $_current_user  = $manage_info['current_user_info'];
            // $_tmp_platform_accounts = explode(',', $_current_user['ebayaccounts']);
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

            $params['where_sql_for_seller'] = $_tmp_sql_where;
        }

        $this->assign('org_list', $org_list);
        $this->assign('account_list', $account_list);
        $this->assign('platforms', $platforms);

        switch ($params['type']) {
            case 'account':
            case 'platform':
                $data = OrderLib::getInstance()->getOrderSaleList($params, $params['type']);
                break;
            case 'seller':
            case 'organ':
                $data = OrderLib::getInstance()->getOrderSellerList($params, $params['type']);
                break;
        }

        // echo '<pre>';var_dump(1111, $data);echo '</pre>';
        // exit;

        // 合并数据
        $expend_data = ExpendLib::getInstance()->getExpendList($params);
        // echo '<pre>';var_dump($expend_data);echo '</pre>';
        // exit;

        if (in_array($params['type'], ['seller', 'organ']))
        {
            foreach ($data['list'] as $key => $value)
            {
                foreach ($value as $k => $v)
                {
                    $_sum_expend_amount = $expend_data['list'][$key][$k]['sum_expend_amount'] ?? 0;
                    $data['list'][$key][$k]['sum_expend_amount'] = $_sum_expend_amount;
                    $data['list'][$key][$k]['expend_rate'] = $v['sum_sales'] == 0 ? '0%' : round($_sum_expend_amount / $v['sum_sales'] * 100, 2) . '%';
                }
            }
        }
        else
        {
            foreach ($data['list'] as $key => $value)
            {
                foreach ($value['dates'] as $k => $v)
                {
                    $_sum_expend_amount = $expend_data['list'][$key]['dates'][$k]['sum_expend_amount'] ?? 0;
                    $data['list'][$key]['dates'][$k]['sum_expend_amount'] = $_sum_expend_amount;
                    $data['list'][$key]['dates'][$k]['expend_rate'] = $v['sum_sales'] == 0 ? '0%' : round($_sum_expend_amount / $v['sum_sales'] * 100, 2) . '%';
                }
            }
        }


        // echo '<pre>';var_dump(22,$data);echo '</pre>';
        // exit;

        // 计算合计
        switch ($params['type']) {
            case 'account':
            case 'platform':
                $this->_index_account_or_platform($params, $data, $range);
                break;
            case 'seller':
                $this->_index_seller($params, $data, $range);
                break;
            case 'organ':
                // todo: 组织架构这个比较复杂，先获取所有销售员的数据，再手动统计，不然呢？
                $this->_index_organ($params, $data, $range);
                break;
        }

        // echo '<pre>';var_dump($data);echo '</pre>';
        // exit;

        // echo '<pre>';var_dump($data);echo '</pre>';
        // exit;
        
        $this->assign('allow_platforms', ExpendLib::getInstance()->getAllowImportPlatforms());
        $this->assign('params', $params);
        $this->assign('list', $data['list']);
        $this->assign('range', $range);

        return $this->view->fetch("index");
    }

    private function _index_account_or_platform($params, &$data, $range)
    {
        // TODO: 平台需要 添加 合计栏 （task 1097）
        $total_data = [];
        // todo: 计算合计数
        foreach ($data['list'] as $_tmp_platform_data) {
            foreach ($_tmp_platform_data['dates'] as $_tmp_date => $_tmp_value) {
                if ($params['checkDate'] == 'day') $_tmp_date = substr($_tmp_date, 5);
                if (!isset($total_data[$_tmp_date]['sum_sales'])) $total_data[$_tmp_date]['sum_sales'] = 0;
                if (!isset($total_data[$_tmp_date]['sum_totals'])) $total_data[$_tmp_date]['sum_totals'] = 0;
                if (!isset($total_data[$_tmp_date]['sum_expend_amount'])) $total_data[$_tmp_date]['sum_expend_amount'] = 0;
                $total_data[$_tmp_date]['sum_sales']  += $_tmp_value['sum_sales'];
                $total_data[$_tmp_date]['sum_totals'] += $_tmp_value['sum_totals'];
                $total_data[$_tmp_date]['sum_expend_amount'] += $_tmp_value['sum_expend_amount'];
            }
        }

        // echo '<pre>';var_dump($total_data);echo '</pre>';
        // exit;
        // 计算费用占比
        foreach ($total_data as $key => $value)
        {
            $total_data[$key]['expend_rate'] = $value['sum_sales'] == '0' ? '0%' : round($value['sum_expend_amount'] / $value['sum_sales'] * 100, 2) . '%';
        }

        $this->assign('total_data', $total_data);
    }

    private function _index_seller($params, &$data, $range)
    {
        $org_parent_name_map = ToolsLib::getInstance()->getAllOrgParentNameMap();
        $all_orgs = ToolsLib::getInstance()->getAllOrg(1);
        $tmp      = $data['list'];

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
                $value[$k]['org_parent_name'] = $tmp_org_name ? ($org_parent_name_map[$tmp_org_name] ?? '-') : '-';
            }
            // $data['list'][$tmp_seller] = $value;
            $data['list'][$key]['org_id']   = $tmp_org_id;
            $data['list'][$key]['org_name'] = $tmp_org_name;
            $data['list'][$key]['org_parent_name'] = $tmp_org_name ? ($org_parent_name_map[$tmp_org_name] ?? '-') : '-';
            $data['list'][$key]['dates']    = $value;
        }

        ksort($data['list']);

        $total_data = [];
        // todo: 计算合计数 (按日期)
        foreach ($data['list'] as $_tmp_platform_data) {
            foreach ($_tmp_platform_data['dates'] as $_tmp_date => $_tmp_value) {
                if ($params['checkDate'] == 'day') $_tmp_date = substr($_tmp_date, 5);
                if (!isset($total_data[$_tmp_date]['sum_sales'])) $total_data[$_tmp_date]['sum_sales'] = 0;
                if (!isset($total_data[$_tmp_date]['sum_totals'])) $total_data[$_tmp_date]['sum_totals'] = 0;
                if (!isset($total_data[$_tmp_date]['sum_expend_amount'])) $total_data[$_tmp_date]['sum_expend_amount'] = 0;
                $total_data[$_tmp_date]['sum_sales']  += $_tmp_value['sum_sales'];
                $total_data[$_tmp_date]['sum_totals'] += $_tmp_value['sum_totals'];
                $total_data[$_tmp_date]['sum_expend_amount'] += $_tmp_value['sum_expend_amount'];
            }
        }

        // 计算费用占比
        foreach ($total_data as $key => $value)
        {
            $total_data[$key]['expend_rate'] = $value['sum_sales'] == '0' ? '0%' : round($value['sum_expend_amount'] / $value['sum_sales'] * 100, 2) . '%';
        }

        $this->assign('total_data', $total_data);

        // 获取销售员
        if ($this->auth->erp_id) {
            $manage_info = $this->manage_info;
            $sellers     = array_unique(array_merge($manage_info['manage_users'], [$manage_info['current_user_info']['username']]));
        } else {
            $sellers = !empty($params['organ']) ? ToolsLib::getInstance()->getSellerByOrg(array_column(ToolsLib::getInstance()->getOrgById($params['organ']), 'name')) : [];
        }

        $this->assign('sellers', $sellers);
    }


    public function _index_organ($params, &$data, $range)
    {
        $data = $this->_reshapeSellerDataForOrgan($data, $params);

        // echo '<pre>';var_dump($data);echo '</pre>';
        // exit;

        // TODO: 计算合计数
        $level1_orgs_ids = array_column(ToolsLib::getInstance()->getLevel1Orgs(), 'id');

        $total_data = [];
        foreach ($data['list'] as $_tmp_organ_data) {
            if (!in_array($_tmp_organ_data['org_id'], $level1_orgs_ids)) continue;
            foreach ($_tmp_organ_data['dates'] as $_tmp_date => $value) {
                if ($params['checkDate'] == 'day') $_tmp_date = substr($_tmp_date, 5);
                if (!isset($total_data[$_tmp_date]['sum_totals'])) $total_data[$_tmp_date]['sum_totals'] = 0;
                if (!isset($total_data[$_tmp_date]['sum_sales'])) $total_data[$_tmp_date]['sum_sales'] = 0;
                if (!isset($total_data[$_tmp_date]['sum_expend_amount'])) $total_data[$_tmp_date]['sum_expend_amount'] = 0;
                $total_data[$_tmp_date]['sum_totals'] += $value['sum_totals'];
                $total_data[$_tmp_date]['sum_sales']  += $value['sum_sales'];
                $total_data[$_tmp_date]['sum_expend_amount']  += $value['sum_expend_amount'];
            }
        }

        // 计算费用占比
        foreach ($total_data as $key => $value)
        {
            $total_data[$key]['expend_rate'] = $value['sum_sales'] == '0' ? '0%' : round($value['sum_expend_amount'] / $value['sum_sales'] * 100, 2) . '%';
        }

        $this->assign('total_data', $total_data);
    }


    private function _reshapeSellerDataForOrgan($data, $params)
    {
        if ($params['checkDate'] == 'day') $tmp_range = range_day($params['scantime_end'], $params['scantime_start']);
        else $tmp_range = range_month($params['scandate_end'], $params['scandate_start']);

        $days_num = count($tmp_range);

        $zero_total = [];
        foreach ($tmp_range as $tmp_v) {
            $zero_total[$tmp_v] = ['sum_totals' => '0', 'sum_sales' => '0.00', 'sum_expend_amount' => '0.00', 'expend_rate' => '-'];
        }

        $org_arr = OrgLib::getInstance()->getBussinessOrgArray();
        if (isset($org_arr[19])) unset($org_arr[19]);

        foreach ($org_arr as $key => $value)
        {
            $_seller_list = [];
            foreach ($value['org_full_user_list'] as $k => $v)
            {
                $_seller_list[] = $v['user_name'] . '___' . $v['organize_id'];
            }
            $org_arr[$key]['seller_list'] = $_seller_list;
            unset($org_arr[$key]['org_full_user_list']);
        }

        // 只显示最顶级的组织架构
        if (isset($params['is_only_top']) && $params['is_only_top'] == true) {
            $tmp     = $org_arr;
            $org_arr = [];
            foreach ($tmp as $v) {
                if ($v['level'] == 2) $org_arr[] = $v;
            }
        }

        $all_sellers = array_keys($data['list']);

        $manage_org_ids = array_column($org_arr, 'id');
        if ($this->auth->erp_id) {
            $manage_info    = $this->manage_info;
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

            $org_arr[$key]['dates']          = $zero_total;
            $org_arr[$key]['all_sums']       = '0.00';
            $org_arr[$key]['all_total']      = '0';
            $org_arr[$key]['average_sales']  = '0.00';
            $org_arr[$key]['average_totals'] = '0';

            if (!$value['seller_list']) continue;

            // todo: 当前组织架构 与 有销售 额数据的 销售员 交集,如果有
            $seller_intersect = array_intersect($all_sellers, $value['seller_list']);

            if (!$seller_intersect) continue;

            $tmp_total = $zero_total;
            $all_sums  = '0.00';
            $all_total = '0';
            foreach ($seller_intersect as $v) {
                $seller_sale_data = $data['list'][$v];
                foreach ($seller_sale_data as $_date => $sale_data) {
                    $tmp_total[$_date]['sum_totals'] += $sale_data['sum_totals'];
                    $tmp_total[$_date]['sum_sales']  += $sale_data['sum_sales'];
                    $tmp_total[$_date]['sum_expend_amount'] += $sale_data['sum_expend_amount'];
                    $all_sums                        += $sale_data['sum_sales'];
                    $all_total                       += $sale_data['sum_totals'];
                }
                // $tmp_total[$_date]['expend_rate'] = $tmp_total[$_date]['sum_sales'] == 0 ? '0%' : round($tmp_total[$_date]['sum_expend_amount'] / $tmp_total[$_date]['sum_sales'] * 100, 2) . '%';;
            }

            foreach ($tmp_total as $k => $v)
            {
                $tmp_total[$k]['expend_rate'] = $v['sum_sales'] == 0 ? '0%' : round($v['sum_expend_amount'] / $v['sum_sales'] * 100, 2) . '%';
            }
            // echo '<pre>';var_dump($tmp_total);echo '</pre>';
            // exit;
            // foreach ($sel)

            $org_arr[$key]['dates']          = $tmp_total;
            $org_arr[$key]['all_sums']       = $all_sums;
            $org_arr[$key]['all_total']      = $all_total;
            $org_arr[$key]['average_sales']  = round($all_sums / $days_num, 4);
            $org_arr[$key]['average_totals'] = ceil($all_total / $days_num);
        }
        return ['list' => $org_arr, 'count' => count($org_arr)];
    }


    /**
     * 平台费用详情
     * @author lamkakyun
     * @date 2019-04-08 20:58:33
     * @return void
     */
    public function platformDetail()
    {
        $params = input('get.');
        if (isset($params['s'])) unset($params['s']);

        if (!isset($params['platform']) || empty($params['platform'])) return $this->error('参数错误');
        if (!isset($params['checkDate'])) $params['checkDate'] = 'day';
        if (!isset($params['scandate_start'])) $params['scandate_start'] = date('Y-m');
        if (!isset($params['scandate_end'])) $params['scandate_end'] = date('Y-m');
        if (!isset($params['scantime_start'])) $params['scantime_start'] = date('Y-m-d', strtotime('-7 day'));
        if (!isset($params['scantime_end'])) $params['scantime_end'] = date('Y-m-d');

        if ($params['checkDate'] == 'day' && strtotime($params['scantime_start']) > strtotime($params['scantime_end'])) return $this->error('开始时间不能大于结束时间');
        if ($params['checkDate'] == 'month' && strtotime($params['scandate_start']) > strtotime($params['scandate_end'])) return $this->error('开始时间不能大于结束时间');


        $type_map = ExpendLib::getInstance()->platformExpendTypeMap();
        $type_map = $type_map[$params['platform']];
        $type_map = array_filter($type_map, function ($v){return !in_array($v, ['账号','业务员','location', '仓库', '销售标签']);});

        $data = ExpendLib::getInstance()->getPlatformDetail($params);

        // 计算合计数
        $total_data = [];
        foreach ($type_map as $key => $value)
        {
            $total_data[$key] = ['sum_sales' => 0, 'sum_expend_amount' => 0];
        }

        foreach ($data as $key => $value)
        {
            foreach ($value as $k => $v)
            {
                $total_data[$v['type_id']]['sum_expend_amount'] += $v['sum_expend_amount'];
                $total_data[$v['type_id']]['sum_sales'] += $v['sum_sales'];
            }
        }
        
        foreach ($total_data as $key => $value)
        {
            $total_data[$key]['expend_rate'] = $value['sum_sales'] == 0 ? '-' : round($value['sum_expend_amount'] / $value['sum_sales'] * 100 , 2) . '%';
        }

        // echo '<pre>';var_dump($total_data, $type_map,$data);echo '</pre>';
        // exit;

        $params['type'] = 'platform';

        if ($this->auth->erp_id) {
            $platforms = $this->manage_info['manage_platforms'];
        } else {
            $platforms = ToolsLib::getInstance()->getAllPlatforms($_SESSION['truename'] ?? '');
        }

        $all_accounts = ToolsLib::getInstance()->getAllAccounts(3);
        $account_list = $all_accounts[$params['platform']];

        $this->assign('params', $params);
        $this->assign('platforms', $platforms);
        $this->assign('account_list', $account_list);
        $this->assign('type_map', $type_map);
        $this->assign('data', $data);
        $this->assign('total_data', $total_data);

        return $this->view->fetch("detail");
    }

    
    /**
     * 费用导入
     * @access auth
     * @author lamkakyun
     * @date 2019-04-01 20:06:17
     * @return void
     */
    public function import()
    {
        $params = array_map('trim', array_merge(input('get.'), input('post.')));

        if (isset($params['is_download'])) return $this->_tplDownload($params);

        if ($this->request->isGet()) {
            $type_map = ExpendLib::getInstance()->platformExpendTypeMap();
            
            $all_platforms = array_keys($type_map);
            $params['platform'] = $all_platforms[0];

            $this->assign('all_platforms', $all_platforms);

            $this->assign('params', $params);
            $this->assign('type_map', $type_map);
            return $this->view->fetch("import");
        } else {
            $ret = ExpendLib::getInstance()->importPlatformExpend($params);
            return json($ret);
        }
    }


    /**
     * 添加 消费性项目 
     * @author lamkakyun
     * @date 2019-04-08 16:40:48
     * @return void
     */
    public function addExpendType()
    {
        $ret = ExpendLib::getInstance()->addExpendType();
        return json($ret);
    }

    
    /**
     * 模板下载
     * @author lamkakyun
     * @date 2019-04-02 10:43:25
     * @return void
     */
    private function _tplDownload($params)
    {
        // $all_platforms = ToolsLib::getInstance()->getAllPlatforms();
        $all_platforms = ['aliexpress', 'amazon', 'cdiscount', 'ebay', 'lazada', 'manomano', 'priceminister', 'wish'];
        if (!isset($params['platform']) && !in_array($params['platform'], $all_platforms)) return $this->error('参数错误');

        $file = ROOT_PATH . "public/download_templates/expend/" . $params['platform'] . '.xlsx';
        ToolsLib::getInstance()->downloadFile($file);
    }
}