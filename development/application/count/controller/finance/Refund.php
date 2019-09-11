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
use app\common\library\CarrierLib;
use app\common\controller\AuthController;
use app\count\library\finance\FinanceRefundLib;

/**
 * 财务 售后报表
 * @package app\count\controller\seller
 */
class Refund extends AuthController
{
    /**
     * 查看
     * @access auth
     * @return string
     * @throws \think\Exception
     */
    public function index()
    {
        $type     = input('get.type', 'date');
        $model    = input('get.model', 'table');
        $params   = input('get.');
        $platform = input('get.platform');

        $params['platform'] = $platform;
        $params['p']        = $params['p'] ?? 1;
        $params['ps']       = $params['ps'] ?? 50;
        if (isset($params['is_export']) && $params['is_export'] == 1) $params['ps'] = $params['ps'] ?? 10000;

        $FinanceAmountLib = new FinanceRefundLib();
        $data             = $FinanceAmountLib->getList($params);

        $skuLib = new SkuLib();

        //获取开发人员
        if (isset($params['type']) && $params['type'] == 'kfuser') {
            $user = $skuLib->getUserInfo(2);
            $this->assign('kfuser', $user);
        }
        //销售员
        if (isset($params['type']) && $params['type'] == 'seller') {
            $seller = $skuLib->getUserInfo(3);
            $this->assign('seller', $seller);
        }
        //平台
        if (isset($params['type']) && ($params['type'] == 'platform' || $params['type'] == 'account')) {
            $platform_account = $skuLib->getPlatformAccount(1);
            $this->assign('platforms', $platform_account);
        }
        //国家
        if (isset($params['type']) && $params['type'] == 'country') {
            $country = $skuLib->getCountry();
            $this->assign('countrys', $country);
        }
        //物流
        if (isset($params['type']) && $params['type'] == 'trench') {
            $carrier = CarrierLib::init()->getCarrier();
            $this->assign('trenchs', array_keys($carrier));
        }
        //账号
        if (isset($params['type']) && $params['type'] == 'account') {
            if (empty($params['platform'])) $params['platform'] = $platform = 'ebay';
            $accounts = $skuLib->getPlatformAccount(3);
            $this->assign('accounts', $accounts[$platform] ?? []);
        }
        //导出
        if (isset($params['is_export']) && $params['is_export'] == 1) $this->_index_export($data['data'], $type);

        // --------- 分页 start -------------
        $current_url = url('/count/finance/refund', '', '');
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
        $this->assign('module', 'finance');
        $this->assign('data', $data['data']);
        $this->assign('total', $data['total']);
        $this->assign('jsonName', $data['jsonName']);
        $this->assign('jsonTotal', $data['jsonTotal']);
        $this->assign('jsonNum', $data['jsonNum']);
        $this->assign('jsonRefundNum', $data['jsonRefundNum']);
        $this->assign('jsonRefundTotal', $data['jsonRefundTotal']);

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
        }
        if ($type == 'account') {
            $title = '账号';
            $type  = 'platform_account';
        }
        if ($type == 'trench') {
            $title = '物流渠道';
            $type  = 'carrier';
        }
        if ($type == 'country') {
            $title = '国家';
            $type  = 'couny';
        }


        $filename = "售后报表-" . date('Y-m-d');

        $headers = [
            $type           => $title,
            'num'           => '订单数',
            'total'         => '订单总金额($)',
            'reissue_num'   => '补发数量',
            'reissue_total' => '补发金额($)',
            'return_num'    => '退货数量',
            'return_total'  => '退货金额($)',
            'refund_num'    => '退款订单数',
            'refund_total'  => '退款金额($)',
            'refund_rate'   => '退款占比%',
            'gift_num'      => '礼物单数量',
            'gift_total'    => '礼物单金额($)',
        ];

        ToolsLib::getInstance()->exportExcel($filename, $headers, $data, $is_seq = false);
    }
}