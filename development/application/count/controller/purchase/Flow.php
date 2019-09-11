<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    leo
 */

namespace app\count\controller\Purchase;

use think\Config;
use app\common\model\User;
use app\count\model\Order;
use think\cache\driver\Redis;
use app\common\library\ToolsLib;
use app\common\controller\Common;
use app\count\library\purchase\EnusmLib;
use app\common\controller\AuthController;
use app\count\library\purchase\CgpaydataLib;

/**
 * 付款流水
 * @package app\count\controller\order
 */
class Flow extends AuthController
{
    protected $relationSearch = true;

    /**
     * @var \app\count\model\Order
     */
    protected $model = null;

    /**
     * 查看
     * @access auth
     * @return string
     * @throws \think\Exception
     */
    public function index()
    {
        $type                           = input('get.type', 'ordersn');
        $params                         = input('get.');
        $params['checkDate']            = $params['checkDate'] ?? 'today';
        $params['partner_id']           = $params['partner_id'] ?? '';
        $params['p']                    = $params['p'] ?? 1;
        $params['ps']                   = $params['ps'] ?? 50;
        $params['sort']                 = $params['sort'] ?? 'sorting';
        $params['sortkey']              = $params['sortkey'] ?? '';
        $payRevenueStatisticsOrdersnLib = new CgpaydataLib();
        $data                           = $payRevenueStatisticsOrdersnLib->getList($params);
        //导出
        if (isset($params['is_export']) && $params['is_export'] == 1) $this->_index_export($data['data'], $type);
        // --------- 分页 start -------------
        $current_url = url('/count/purchase/flow', '', 'html');
        if ($params) $current_url = $current_url . '?' . http_build_query(array_filter($params, function ($val) {
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
        $this->assign('data', $data['data']);
        $this->assign('total', $data['total']);
        $this->assign('params', $data['params']);
        $this->assign('module', 'purchase');

        $userModel = new User();
        $allCguser = $userModel->getAllCguser();
        $this->assign('allCguser', $allCguser);
        return $this->view->fetch("index_data");
    }

    /**
     * 付款流水按账号
     * @access auth
     * @return string
     * @throws \think\Exception
     */
    public function byAccount()
    {
        $params                         = input('get.');
        $params['checkDate']            = $params['checkDate'] ?? 'today';
        $payRevenueStatisticsOrdersnLib = new EnusmLib();
        $data                           = $payRevenueStatisticsOrdersnLib->getList($params);
        if (isset($params['is_export']) && $params['is_export'] == 1) $this->_index_export($data['data'], '');
        $this->assign('data', $data['data']);
        $this->assign('params', $data['params']);
        $this->assign('module', 'purchase');
        return $this->view->fetch("byAccount_data");
    }

    /**
     * 付款流水按账号 查看明细
     * @access auth
     * @return string
     * @throws \think\Exception
     */
    public function detailed()
    {
        $params = input('get.');
        if (empty($params)) {
            echo "请求异常！";
            exit;
        }
        $payRevenueStatisticsOrdersnLib = new EnusmLib();
        $data                           = $payRevenueStatisticsOrdersnLib->getDetiledList($params);
        $this->assign('data', $data['data']);
        $this->assign('params', $data['params']);
        return $this->view->config([
            'layout_name' => $this->layout_fluid
        ])->fetch("detailed_table");
    }

    /**
     * 首页报表 导出
     * @AUTHOR: leo
     * @DATE: 2018-09-21 10:59:03
     */
    private function _index_export($data, $type)
    {

        if ($type == 'ordersn') {
            $headers  = [
                'id'          => '款项编号',
                'dateTime'    => '款项时间',
                'payType'     => '款项类型',
                'partnerName' => '供应商',
                'ordersn'     => '采购单',
                'ship_fee'    => '运费',
                'adduser'     => '经手人',
                'cguser'      => '采购员',
                'payamount'   => '金额',
                'paywayName'  => '账号',
            ];
            $filename = "付款流水-" . date('Y-m-d');
        } else {
            $headers  = [
                'id'      => '款项编号',
                'payName' => '账号',
                'amount5' => '收入',
                'amount1' => '支出',
            ];
            $filename = "付款流水账号汇总-" . date('Y-m-d');
        }
        ToolsLib::getInstance()->exportExcel($filename, $headers, $data, $is_seq = false);
    }

}
