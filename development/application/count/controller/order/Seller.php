<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    jason
 */

namespace app\count\controller\order;

use think\Config;
use app\count\model\sales;
use think\cache\driver\Redis;
use app\common\library\ToolsLib;
use app\common\controller\Common;
use app\count\library\order\OrderLib;
use app\common\controller\AuthController;

/**
 * @name 销售员报表
 * @package app\count\controller\seller
 */
class Seller extends AuthController
{
    /**
     * 查看 (作废)
     * @access auth
     * @return string
     * @throws \think\Exception
     */
//    public function index_delete()
//    {
////        var_dump(ToolsLib::getInstance()->getCanViewPlatform('林嘉权'));exit;
//        $type   = input('get.type', 'sellers');
//        $model  = input('get.model', 'table');
//        $params = input('get.');
//
//        if (!empty($params['login_name'])) $_SESSION['truename'] = $params['login_name'];
//
//        $params['checkDate'] = $params['checkDate'] ?? 'day';
//        $params['scantime_start'] = $params['scantime_start'] ?? date('Y-m-d', strtotime('-1 day'));
//        $params['scantime_end'] = $params['scantime_end'] ?? date('Y-m-d', strtotime('-1 day'));
//        $params['scandate_start'] = $params['scandate_start'] ?? date('Y-m');
//        $params['scandate_end'] = $params['scandate_end'] ?? date('Y-m');
//        $params['type'] = $params['type'] ?? 'date';
//        $params['model'] = $params['model'] ?? 'table';
//        $params['p'] = $params['p'] ?? 1;
//        $params['ps'] = $params['ps'] ?? 1500;
//        if (!empty($params['seller']))
//        {
//            $params['seller'] = is_array($params['seller']) ? $params['seller'] : explode(',', $params['seller']);
//            $params['seller'] = array_filter($params['seller'], function($val) {return !empty($val);});
//        }
//
//        if (isset($params['is_export']) && $params['is_export'] == 1) $params['ps'] = 10000;
//
//        $data = OrderLib::getInstance()->getOrderSellerList($params, $params['type']);
//
//        // 需求，搜索销售员时，没有数据的需要显示为0
//        if (!empty($params['seller']))
//        {
//            $_tmp_seller = array_column($data['list'], 'seller');
//            $diff_section = array_diff($params['seller'], $_tmp_seller);
//            foreach ($diff_section as $_v)
//            {
//                $data['list'][] = [
//                    'seller' => $_v,
//                ];
//            }
//        }
//
//        if (isset($params['is_export']) && $params['is_export'] == 1) $this->_index_export($data, $params['type']);
//
//        $sellers = !empty($params['organ']) ? ToolsLib::getInstance()->getSellerByOrg(array_column(ToolsLib::getInstance()->getOrgById($params['organ']), 'name')) : [];
//        $this->assign('sellers', $sellers);
//
////        $all_sellers = ToolsLib::getInstance()->getAllSaleUsers(1);
////        $all_sellers = ToolsLib::getInstance()->getLevel1SellersMap();
//        // 为图表 构造数据
//        if ($params['model'] == 'chart') {
//            $chart_type = 'bar';
//            $x_data     = array_column($data['list'], 'seller');
//
//            $x_data_names = ['总单数', '销售额', '退款单量', '退款金额', '作废单量', '作废金额'];
//            $y_data[]     = array_column($data['list'], 'sum_totals');
//            $y_data[]     = array_column($data['list'], 'sum_sales');
//            $y_data[]     = array_column($data['list'], 'sum_refunds_count');
//            $y_data[]     = array_column($data['list'], 'sum_refunds');
//            $y_data[]     = array_column($data['list'], 'sum_recycles_count');
//            $y_data[]     = array_column($data['list'], 'sum_recycles');
//
//            $this->assign('chart_type', $chart_type);
//            $this->assign('x_data', json_encode($x_data));
//            $this->assign('y_data', json_encode($y_data));
//            $this->assign('x_data_names', json_encode($x_data_names));
//        }
//
//        // 匿名函数
//        $sum_function = function($v1, $v2) {return $v1 + $v2;};
//        $total_data = [];
//        $total_data['sum_totals'] = array_reduce(array_column($data['list'], 'sum_totals'), $sum_function);
//        $total_data['sum_sales'] = array_reduce(array_column($data['list'], 'sum_sales'), $sum_function);
//        $total_data['sum_refunds_count'] = array_reduce(array_column($data['list'], 'sum_refunds_count'), $sum_function);
//        $total_data['sum_refunds'] = array_reduce(array_column($data['list'], 'sum_refunds'), $sum_function);
//        $total_data['sum_recycles_count'] = array_reduce(array_column($data['list'], 'sum_recycles_count'), $sum_function);
//        $total_data['sum_recycles'] = array_reduce(array_column($data['list'], 'sum_recycles'), $sum_function);
//        $this->assign('total_data', $total_data);
//
//        $this->_assignPagerData($this, $params, $data['count']);
//
//        $org_list = ToolsLib::getInstance()->getLevel1Orgs($_SESSION['truename'] ?? '林嘉权');
//
//        $this->assign('org_list', $org_list);
//        $this->assign('list', $data['list']);
//        $this->assign('list_total', $data['count']);
////        $this->assign('all_sellers', $all_sellers);
//        $this->assign('type', $type);
//        $this->assign('model', $model);
//        $this->assign('params', $params);
//        $this->assign('seller_arr', []);
//        $this->assign('module', 'order');
//
//        return $this->view->fetch("index_$type");
//    }


    /**
     * 首页报表 导出
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-09-21 10:59:03
     */
    private function _index_export($data)
    {
        $filename = "销售员报表-" . date('Y-m-d');
        $data = $data['list'];

        $headers = [
            'seller' => '销售员',
            'sum_totals' => '总单数',
            'sum_sales' => '销售额($)',
            'sum_refunds_count' => '退款单量',
            'sum_refunds' => '退款金额($)',
            'sum_recycles_count' => '作废单量',
            'sum_recycles' => '作废金额($)',
        ];

        ToolsLib::getInstance()->exportExcel($filename, $headers, $data);
    }


    
}