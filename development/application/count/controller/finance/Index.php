<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    kevin
 */

namespace app\count\controller\finance;

use app\common\library\ToolsLib;
use app\common\controller\Common;
use app\count\library\sku\SkuLib;
use app\common\controller\AuthController;
use app\count\library\finance\FinanceAmountLib;

/**
 * 财务 收支报表
 * @package app\count\controller\seller
 */
class Index extends AuthController
{
    /**
     * 查看
     * @access auth
     * @return string
     * @throws \think\Exception
     */
    public function index()
    {
        $params = input('get.');
        $type   = input('get.type', 'date');
        $model  = input('get.model', 'table');
        $all_platform = ToolsLib::getInstance()->getAllPlatforms($_SESSION['truename'] ?? '');

        $params['p']  = $params['p'] ?? 1;
        $params['ps'] = $params['ps'] ?? 50;
        $params['sales_user'] = isset($params['sales_user']) ? explode(',', $params['sales_user']) : []; 
        if (isset($params['is_export']) && $params['is_export'] == 1) $params['ps'] = $params['ps'] ?? 10000;
        // 默认展示第一个平台
        if (isset($params['type']) && $params['type'] == 'account' && !isset($params['platform'])) $params['platform'] = $all_platform[0];

        if (isset($params['day_start']) && isset($params['day_end']) && strtotime($params['day_start']) > strtotime($params['day_end'])) return $this->error('开始时间不能大于结束时间');
        if (isset($params['month_start']) && isset($params['month_end']) && strtotime($params['month_start']) > strtotime($params['month_end'])) return $this->error('开始时间不能大于结束时间');
        // todo: 需求， 默认展示第一个部门
        $org_list = ToolsLib::getInstance()->getLevel1Orgs($_SESSION['truename'] ?? '林嘉权');
        if ($type == 'seller') {
            if (!isset($params['organ'])) $params['organ'][] = array_column($org_list, 'id')[0];
            else $params['organ'] = explode(',', $params['organ']);
        }

        $skuLib = new SkuLib();
        $FinanceAmountLib = new FinanceAmountLib();
        $data             = $FinanceAmountLib->getList($params);

        //获取开发人员
        if (isset($params['type']) && $params['type'] == 'kfuser') {
            $user = $skuLib->getUserInfo(2);
            $this->assign('developUser', $user);
        }

        //销售员
        if (isset($params['type']) && $params['type'] == 'seller') {
            $this->_reshapeSellerDataForSeller($data, $params);

            $seller = !empty($params['organ']) ? ToolsLib::getInstance()->getSellerByOrg(array_column(ToolsLib::getInstance()->getOrgById([$params['organ'][0]]), 'name')) : [];
            $this->assign('sellerUser', $seller);
            $this->assign('org_list', $org_list);
        }

        // 组织架构
        if (isset($params['type']) && $params['type'] == 'organ') {
             $this->_reshapeSellerDataForOrgan($data, $params);
        }

        //平台
        if (isset($params['type']) && $params['type'] == 'platform') {
            $platform_account = $skuLib->getPlatformAccount(1);
            $this->assign('platform', $platform_account);
        }

        //账号
        if (isset($params['type']) && $params['type'] == 'account') {
            $all_accounts = ToolsLib::getInstance()->getAllAccounts(3);
            
            
            $account_list = $all_accounts[$params['platform']] ?? [];

            $this->assign('account_list', $account_list);
            
        }

        //导出
        if (isset($params['is_export']) && $params['is_export'] == 1) $this->_index_export($data['data'], $type);

        // --------- 分页 start -------------
        $current_url = url('/count/finance/index', '', '');
        if ($_GET) $current_url = $current_url . '?' . http_build_query(array_filter($_GET, function ($val) {
                return $val != '';
            }));
        //分页
        $pager_data = gen_pager_data($params['p'], $data['count'], $params['ps']);
        $this->assign('all_page_num', $pager_data['all_page_num']);
        $this->assign('last_page', $pager_data['last_page']);
        $this->assign('next_page', $pager_data['next_page']);
        $this->assign('current_url', $current_url);
        $this->assign('list_total', $data['count']);
        // --------- 分页 end -------------

        $this->assign('type', $type);
        $this->assign('model', $model);
        $this->assign('params', $data['params']);
        $this->assign('platforms', $all_platform);
        $this->assign('kfuser', []);
        $this->assign('seller', []);
        $this->assign('module', 'finance');
        $this->assign('data', $data['data']);
        $this->assign('jsonName', $data['jsonName']);
        $this->assign('jsonTotal', $data['jsonTotal']);
        $this->assign('jsonGross', $data['jsonGross']);
        $this->assign('total', $data['total']);
        return $this->view->fetch("index_$type");
    }

    /**
     * 导出
     * @AUTHOR: 杨能文
     * @param $data
     * @param $type
     * @DATE: 2018-09-21 10:59:03
     */
    private function _index_export($data, $type)
    {
        if ($type == 'platform') $title = '平台';
        if ($type == 'date') {
            $title = '日期';
            $type  = 'date';
        }
        if ($type == 'kfuser') {
            $title = '开发人员';
            $type  = 'develop_user';
        }
        if ($type == 'seller') {
            $title = '销售人员';
            $type  = 'sales_user';

            foreach ($data as $k => $v)
            {
                $data[$k]['full_org_name'] = $v['org_parent_name'] . $v['org_name'];
            }
        }
        if ($type == 'organ') {
            $title = '组织架构';
            $type  = 'name';

            $default_data = [
                'total' => '0',
                'cost' => '0',
                'freight' => '0',
                'material' => '0',
                'platform_fee' => '0',
                'paypal' => '0',
                'commission' => '0',
                'refunds' => '0',
                'gross' => '0',
                'onlinefee' => '0',
                'refunds_rate' => '0',
                'gross_rate' => '0',
            ];

            foreach($data as $k => $v)
            {
                $finance_data = $v['finance_data'] ?: $default_data;
                unset($data[$k]['finance_data']);
                $data[$k] = array_merge($data[$k], $finance_data);
            }
        }

        if ($type == 'account') {
            $title = '账户';
            $type  = 'platform_account';
        }

        $filename = "收支报表-" . date('Y-m-d');

        $headers = [
            $type          => $title,
            'total'        => '总订金额($)',
            'cost'         => '商品成本($)',
            'freight'      => '物流运费($)',
            'onlinefee'    => '线上运费($)',
            'material'     => '包材费($)',
            'platform_fee' => '转换费($)',
            'paypal'       => 'Papal($)',
            'commission'   => '佣金($)',
            'gross'        => '毛利($)',
            'gross_rate'   => '毛利率%',
            //'refunds'      => '退款金额($)',
            //'refunds_rate' => '退款占比%',
        ];

        if ($type == 'sales_user') {
            $headers = array_merge(['full_org_name' => '组织架构'], $headers);
        }

        return ToolsLib::getInstance()->exportExcel($filename, $headers, $data, $is_seq = false);
    }


    /**
     * 需要重组数据，按照组织架构的排列方式显示
     * @author lamkakyun
     * @date 2018-12-18 11:39:44
     * @return void
     */
    private function _reshapeSellerDataForOrgan(&$data, $params = [])
    {
        $tmp_finance_data = [];
        foreach ($data['data'] as $value)
        {
            $tmp_key = "{$value['sales_user']}___{$value['sales_branch_id']}";
            $tmp_finance_data[$tmp_key] = $value;
        }

        // echo '<pre>';var_dump($tmp_finance_data);echo '</pre>';
        // exit;

        $org_tree    = ToolsLib::getInstance()->getBusinessOrgTree();
        $org_arr     = ToolsLib::getInstance()->treeToArray($org_tree);

        $default_data = [
            'total' => '0',
            'cost' => '0',
            'freight' => '0',
            'material' => '0',
            'platform_fee' => '0',
            'paypal' => '0',
            'commission' => '0',
            'refunds' => '0',
            'gross' => '0',
            'onlinefee' => '0',
            'refunds_rate' => '0%',
            'gross_rate' => '0%',
        ];

        foreach ($org_arr as $key => $value) 
        {
            $tmp_default_data = $default_data;
            $org_arr[$key]['finance_data'] = $tmp_default_data;

            foreach ($tmp_finance_data as $k => $v)
            {
                if (in_array($k, $value['seller_list']))
                {
                    $org_arr[$key]['finance_data']['total'] += $v['total'];
                    $org_arr[$key]['finance_data']['cost'] += $v['cost'];
                    $org_arr[$key]['finance_data']['freight'] += $v['freight'];
                    $org_arr[$key]['finance_data']['material'] += $v['material'];
                    $org_arr[$key]['finance_data']['platform_fee'] += $v['platform_fee'];
                    $org_arr[$key]['finance_data']['paypal'] += $v['paypal'];
                    $org_arr[$key]['finance_data']['commission'] += $v['commission'];
                    $org_arr[$key]['finance_data']['refunds'] += $v['refunds'];
                    $org_arr[$key]['finance_data']['gross'] += $v['gross'];
                    $org_arr[$key]['finance_data']['onlinefee'] += $v['onlinefee'];
                }
            }

            $_tmp_total = $org_arr[$key]['finance_data']['total'];
            $_tmp_refund = $org_arr[$key]['finance_data']['refunds'];
            $_tmp_gross = $org_arr[$key]['finance_data']['gross'];

            $org_arr[$key]['finance_data']['refunds_rate'] = $_tmp_total == 0 ? '0%' : round($_tmp_refund / $_tmp_total, 4) * 100 . "%";
            $org_arr[$key]['finance_data']['gross_rate'] = $_tmp_total == 0 ? '0%' : round($_tmp_gross / $_tmp_total, 4) * 100 . "%";
        }

        $data['data'] = $org_arr;
        $data['count'] = count($org_arr);

        return $data;
    }


    /**
     *  需要重组数据，按照组织架构的排列方式显示
     * @author lamkakyun
     * @date 2018-12-26 15:16:55
     * @return void
     */
    public function _reshapeSellerDataForSeller(&$data, $params)
    {
        // step 1: 将 seller___org_id 放到key 的位置上
        $tmp      = $data['data'];
        $data['data'] = [];
        foreach ($tmp as $key => $value) {
            $tmp_key = $value['sales_user'] . '___' . $value['sales_branch_id'];
            $data['data'][$tmp_key] = $value;
        }

        // step 2: 获取组织架构 父名称
        $org_parent_name_map = ToolsLib::getInstance()->getAllOrgParentNameMap();
        $all_orgs = ToolsLib::getInstance()->getAllOrg(1);

        $tmp      = $data['data'];
        $data['data'] = [];

        foreach ($tmp as $key => $value) {
            $seller_and_org_id = explode('___', $key);
            $tmp_seller        = $seller_and_org_id[0];
            $tmp_org_id        = $seller_and_org_id[1];
            $tmp_org_name      = '';
            if ($tmp_org_id != 0) {
                $_tmp_org     = $all_orgs[$tmp_org_id];
                $tmp_org_name = $_tmp_org['name'];
            }
            $value['org_name']        = $tmp_org_name;
            $value['org_parent_name'] = $tmp_org_name ? $org_parent_name_map[$tmp_org_name] : '-';
            $data['data'][$key]  = $value;
        }

        ksort($data['data']);

    }
}