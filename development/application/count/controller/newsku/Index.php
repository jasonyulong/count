<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    jason
 */

namespace app\count\controller\newsku;

use think\Config;
use app\count\model\sales;
use app\count\library\OrgLib;
use think\cache\driver\Redis;
use app\common\library\ToolsLib;
use app\common\controller\Common;
use app\count\library\sku\NewSkuLib;
use app\count\library\order\OrderLib;
use app\count\model\OrderSellerTarget;
use app\common\controller\AuthController;


/**
 * SKU销售报表,和 app\count\controller\order\sale.php 差不多
 * 还是熟悉的味道，熟悉的配方
 * @package app\count\controller\newsku
 */
class Index extends AuthController
{
    public function __construct()
    {
        parent::__construct();
        $this->manage_info = $this->auth->erp_id ? OrgLib::getInstance()->getManageInfo($this->auth->username) : false;
    }

    /**
     * 初始化，参数默认值
     * @AUTHOR: Lamkakyun
     * @DATE: 2019-02-19 11:40:48
     */
    private function _index_init(&$params)
    {
        $params['p']           = $params['p'] ?? 1;
        $params['sku_keyword'] = $params['sku_keyword'] ?? '';
        $params['cat_id']      = $params['cat_id'] ?? '';
        $params['sub_cat_id']  = $params['sub_cat_id'] ?? [];
        $params['developer']   = $params['developer'] ?? [];
        $params['country']     = $params['country'] ?? [];
        $params['store_id']    = $params['store_id'] ?? [];
        $params['ps']          = $params['ps'] ?? 20;
        $params['platform']    = $params['platform'] ?? null;

        $params['type']           = $params['type'] ?? 'date';
        $params['model']          = $params['model'] ?? 'table';
        $params['checkDate']      = $params['checkDate'] ?? 'day';
        $params['scandate_start'] = $params['scandate_start'] ?? date('Y-m');
        $params['scandate_end']   = $params['scandate_end'] ?? date('Y-m');
        if ($params['type'] == 'date') {
            $params['scantime_start'] = $params['scantime_start'] ?? date('Y-m-d', strtotime('-15 day'));
            $params['scantime_end']   = $params['scantime_end'] ?? date('Y-m-d', strtotime('today'));
        } else {
            $params['scantime_start'] = $params['scantime_start'] ?? date('Y-m-d', strtotime('-10 day'));
            $params['scantime_end']   = $params['scantime_end'] ?? date('Y-m-d', strtotime('-1 day'));
        }

        if ($params['checkDate'] == 'day' && strtotime($params['scantime_start']) > strtotime($params['scantime_end'])) return $this->error('开始时间不能大于结束时间');
        if ($params['checkDate'] == 'month' && strtotime($params['scandate_start']) > strtotime($params['scandate_end'])) return $this->error('开始时间不能大于结束时间');

        $params['sort']       = $params['sort'] ?? 'DESC';
        $params['sort_field'] = $params['sort_field'] ?? 'year, month, days';

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
        if ($params['type'] == 'account' && !isset($params['platform'])) {
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

        $this->assign('org_list', $org_list);

        $this->assign('account_list', $account_list);
        $this->assign('platforms', $platforms);
        $this->assign('type', $params['type']);
        $this->assign('model', $params['model']);
        $this->assign('params', $params);
        $this->assign('module', 'newsku');
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
            $range = range_day($params['scantime_end'], $params['scantime_start']);
        } else {
            $range = range_month($params['scandate_end'], $params['scandate_start']);
        }
        $this->assign('range', $range);

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

        if (isset($params['is_export']) && $params['is_export'] == 1) {
            ini_set("max_execution_time", "300");
            ini_set('memory_limit', '1024M');
            $params['ps'] = 1000000;
        }

        // 获取数据
        switch ($params['type']) {
            case 'date':
                // 为了计算环比增长数据，我们需要额外查多一天的数据，之后在将其删除即可
                $params['scantime_start'] = date('Y-m-d', strtotime($params['scantime_start'] . ' -1 day'));
                $params['scandate_start'] = date('Y-m', strtotime($params['scandate_start'] . ' -1 month'));

                $data = NewSkuLib::getInstance()->getSkuDateSaleList($params);
                break;
            case 'account':
            case 'cat':
            case 'seller':
            case 'developer':
            case 'country':
            case 'store':
                $data = NewSkuLib::getInstance()->getSkuSaleList($params);
                break;
        }

        // echo '<pre>';var_dump($data);echo '</pre>';
        // exit;

        switch ($params['type']) {
            case 'date':

                // TODO: 计算平均销量
                foreach ($data['list'] as $key => $value) {
                    $data['list'][$key]['aver_sale'] = $value['counts'] ? round($value['totals'] / $value['counts'], 2) : 0;
                }

                // TODO: 平均销量环比增长
                foreach ($data['list'] as $key => $value) {
                    $last_key = $params['checkDate'] == 'month' ? date('Y-m', strtotime($key . ' -1 month')) : date('Y-m-d', strtotime($key . ' -1 day'));

                    $last_aver_sale = isset($data['list'][$last_key]) ? $data['list'][$last_key]['aver_sale'] : false;
                    if ($last_aver_sale !== false) {
                        $data['list'][$key]['loop_growth'] = $last_aver_sale ? round(($value['aver_sale'] - $last_aver_sale) / $last_aver_sale, 4) * 100 . '%' : '-';
                    } else {
                        $data['list'][$key]['loop_growth'] = '-';
                    }
                }

                $this->_index_date($params, $data);

                unset($data['list'][$params['scantime_start']]);
                unset($data['list'][$params['scandate_start']]);
                break;

            case 'account':
            case 'cat':
            case 'seller':
            case 'developer':
            case 'country':
            case 'store':
                $this->_index_all($params, $data, $range);
                break;
        }

        // echo '<pre>';var_dump($data);echo '</pre>';
        // exit;

        $this->_assignPagerData($this, $params, $data['count']);
        $this->assign('list', $data['list']);
        $this->assign('list_total', $data['count']);
        $this->assign('is_top_manager', $is_top_manager);

        $unsort_url = url('', '', '');
        unset($_GET['sort_more']);
        unset($_GET['sort_date']);
        if ($_GET) $unsort_url = $unsort_url . '?' . http_build_query(array_filter($_GET, function ($val, $key) {
                if ($key == 'sku_keyword') return false;
                if ($key == 'platform') return true;
                return $val != '';
            }, ARRAY_FILTER_USE_BOTH));

        $this->assign('unsort_url', $unsort_url);

        return $this->view->fetch("index");
    }


    /**
     * 处理日期tab
     * @author lamkakyun
     * @date 2019-02-19 14:19:40
     * @return void
     */
    private function _index_date($params, &$data)
    {
        // todo: 合计数
        $sum_function = function ($v1, $v2) {
            return $v1 + $v2;
        };

        $total_data               = [];
        $total_data['sum_costs']  = array_reduce(array_column($data['list'], 'costs'), $sum_function);
        $total_data['sum_sales']  = array_reduce(array_column($data['list'], 'sales'), $sum_function);
        $total_data['sum_counts'] = array_reduce(array_column($data['list'], 'counts'), $sum_function);
        $total_data['sum_totals'] = array_reduce(array_column($data['list'], 'totals'), $sum_function);

        $this->assign('total_data', $total_data);

        // TODO: 导出EXCEL
        if (isset($params['is_export']) && $params['is_export'] == 1) {
            $filename    = "SKU销量报表(按日期)-" . date('YmdHis');
            $export_data = [];

            foreach ($data['list'] as $key => $value) {
                $export_data[$key] = $value;
            }

            foreach ($export_data as $key => $value) {
                $tmp_str = "{$value['year']}-{$value['month']}";
                if ($params['checkDate'] == 'day') $tmp_str .= "-{$value['days']}";
                $export_data[$key]['date']        = $tmp_str;
                $export_data[$key]['aver_totals'] = $value['counts'] ? round($value['totals'] / $value['counts'], 2) : 0;
            }

            $headers = [
                'date'        => '日期',
                'counts'      => '总数量',
                'totals'      => '总销量',
                'aver_totals' => '平均销量',
                'costs'       => '总成本($)',
                'sales'       => '总销售额($)',
                'loop_growth' => '平均销量环比增长',
            ];

            ToolsLib::getInstance()->exportExcel($filename, $headers, $export_data, false);
        }
    }


    /**
     * 处理 tab, 合并 平台，分类，开发员，销售员，国家，仓库
     * @author lamkakyun
     * @date 2019-02-27 16:08:58
     * @return void
     */
    private function _index_all($params, &$data, $range)
    {
        $start_time = strtotime($params['checkDate'] == 'day' ? $params['scantime_start'] : $params['scandate_start']);
        $end_time   = strtotime($params['checkDate'] == 'day' ? $params['scantime_end'] . ' 23:59:59' : date('Y-m', strtotime($params['scandate_end'] . ' +31 day')));

        switch ($params['type']) {
            case 'account':
                $filename = "SKU销量报表(按平台)-" . date('YmdHis');

                break;
            case 'cat':
                $goods_category     = ToolsLib::getInstance()->getGoodsCategory(1);
                $sub_goods_category = [];
                if ($params['sub_cat_id']) $sub_goods_category = $goods_category[$params['cat_id']]['sub_cat'];
                $this->assign('goods_category', $goods_category);
                $this->assign('sub_goods_category', $sub_goods_category);

                $filename = "SKU销量报表(按分类)-" . date('YmdHis');

                break;
            case 'seller':
                if ($this->auth->erp_id) {
                    $manage_info = $this->manage_info;
                    $sellers     = array_unique(array_merge($manage_info['manage_users'], [$manage_info['current_user_info']['username']]));
                } else {
                    $sellers = !empty($params['organ']) ? ToolsLib::getInstance()->getSellerByOrg(array_column(ToolsLib::getInstance()->getOrgById($params['organ']), 'name')) : [];
                }
                $this->assign('sellers', $sellers);

                $filename = "SKU销量报表(按销售员)-" . date('YmdHis');

                break;
            case 'developer':
                $developers = ToolsLib::getInstance()->getAllDevelopers();
                $this->assign('developers', $developers);

                $filename = "SKU销量报表(按开发员)-" . date('YmdHis');

                break;
            case 'country':
                $countries = ToolsLib::getInstance()->getAllCountries();
                $this->assign('countries', $countries);

                $filename = "SKU销量报表(按国家)-" . date('YmdHis');

                break;
            case 'store':
                $store_list = ToolsLib::getInstance()->getStoreCache();
                $this->assign('store_list', $store_list);

                $filename = "SKU销量报表(按仓库)-" . date('YmdHis');

                break;
        }

        $tmp_data = NewSkuLib::getInstance()->getSkuTotalAll($start_time, $end_time, $params);

        $total_data = [];
        foreach ($tmp_data as $k => $v) {
            $total_data[$k]['sum_qty'] = $v['sum_qty'];
        }

        $this->assign('total_data', $total_data);

        $date_total_map = [];
        $date_aver_map  = [];
        foreach ($data['list'] as $key => $value) {
            $date_total_map[$key]['sum_qty'] = 0;
            foreach ($value as $k => $v) {
                $date_total_map[$key]['sum_qty'] += $v['sum_qty'];
            }
        }
        foreach ($date_total_map as $key => $value) {
            $date_aver_map[$key]['average_qty'] = ceil($value['sum_qty'] / count($range));
        }

        // TODO: 导出EXCEL
        if (isset($params['is_export']) && $params['is_export'] == 1) {

            $export_data = [];

            foreach ($data['list'] as $key => $value) {
                $_tmp = [];

                foreach ($value as $k => $v) {
                    $_tmp['sku']           = $key;
                    $_tmp[$k . '_sum_qty'] = $v['sum_qty'];

                }
                $_tmp['sum_qty']     = $date_total_map[$key]['sum_qty'];
                $_tmp['average_qty'] = $date_aver_map[$key]['average_qty'];
                $export_data[]       = $_tmp;
            }

            $headers = ['sku' => 'SKU'];
            foreach ($range as $value) {
                $headers[$value . '_sum_qty'] = $value;
            }
            $headers['sum_qty']     = '合计';
            $headers['average_qty'] = '平均销量';

            ToolsLib::getInstance()->exportExcel($filename, $headers, $export_data, false);

        }

        $this->assign('date_total_map', $date_total_map);
        $this->assign('date_aver_map', $date_aver_map);
    }


    /**
     * 单个SKU 平台统计
     * @access auth
     * @author lamkakyun
     * @date 2019-02-22 11:39:07
     * @return void
     */
    public function skuPlatform()
    {
        $params = input('get.');

        if ((!isset($params['sku']) || empty($params['sku'])) || !isset($params['date']) || empty($params['date'])) {
            $this->error(__('params error'), null, null, 0);
        }

        // 用户只能查看自己管理的平台的信息
        if ($this->auth->erp_id) {
            $platforms = $this->manage_info['manage_platforms'];
        } else {
            $platforms = ToolsLib::getInstance()->getAllPlatforms($_SESSION['truename'] ?? '');
        }


        // 登陆用户只能看到自己管理的账户
        if ($this->auth->erp_id) {
            $all_accounts = $this->manage_info['manage_accounts'];
        } else {
            $all_accounts = ToolsLib::getInstance()->getAllAccounts();
            $all_accounts = array_column($all_accounts, 'ebay_account');
        }

        $stat_data = NewSkuLib::getInstance()->getSkuPlatformAndAccountStat($params['date'], $params['sku']);

        $platform_stat = [];
        foreach ($stat_data['platform_data'] as $k => $v) {
            if (in_array($k, $platforms)) $platform_stat[$k] = $v;
        }

        $account_stat = [];
        foreach ($stat_data['account_data'] as $k => $v) {
            if (in_array($k, $all_accounts) && in_array($v['platform'], $platforms)) $account_stat[$v['platform']][] = ['platform_account' => $v['platform_account'], 'sum_qty' => $v['sum_qty']];
        }

        $max_num = 0;
        foreach ($account_stat as $v) {
            $_tmp_num = count($v);
            if ($_tmp_num > $max_num) $max_num = $_tmp_num;
        }

        // 重构数据，否则页面不好显示
        $reform_data = [];
        $totals      = [];
        foreach ($platforms as $p) {
            $totals[] = isset($platform_stat[$p]) ? $platform_stat[$p]['sum_qty'] : 0;
        }
        ksort($platforms);
        $reform_data[] = $totals;
        $reform_data[] = $platforms;


        for ($i = 0; $i < $max_num; $i++) // 行数
        {
            $_tmp_arr = [];
            foreach ($platforms as $k => $v) // 列数
            {
                $_tmp_data  = $account_stat[$v][$i] ?? ['platform_account' => '', 'sum_qty' => ''];
                $_tmp_arr[] = $_tmp_data;
            }
            $reform_data[] = $_tmp_arr;
        }
        if (request()->get('debug')) {
            print_r($reform_data);
        }

        // 删除没有数据的平台
        $tmp_stat_data   = $reform_data[0];
        $tmp_reform_data = $reform_data;
        foreach ($tmp_reform_data as $key => $value) {
            foreach ($value as $k => $v) {
                if (isset($tmp_stat_data[$k]) && isset($reform_data[$key][$k]) && $tmp_stat_data[$k] == 0) unset($reform_data[$key][$k]);
            }
        }

        $this->assign('platforms', $platforms);
        $this->assign('reform_data', $reform_data);
        return $this->view->fetch();
    }
}