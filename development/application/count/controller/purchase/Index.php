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
use app\common\model\EbayPartner;
use app\common\controller\AuthController;
use app\count\library\purchase\PayRevenueStatisticsSkuLib;
use app\count\library\purchase\PayRevenueStatisticsOrdersnLib;

/**
 * 应付款报表 （采购单）
 * @package app\count\controller\order
 */
class Index extends AuthController
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
        $type   = input('get.type', 'ordersn');
        $params = input('get.');

        $params['checkDate']  = $params['checkDate'] ?? 'today';
        $params['type']       = $params['type'] ?? 'ordersn';
        $params['partner_id'] = $params['partner_id'] ?? '';
        $params['sku']        = $params['sku'] ?? '';
        $params['p']          = $params['p'] ?? 1;
        $params['ps']         = $params['ps'] ?? 50;
        $params['sort']       = $params['sort'] ?? 'sorting';
        $params['sortkey']    = $params['sortkey'] ?? '';
        $params['cguser']     = $params['cguser'] ?? '';

        if ($params['type'] == 'ordersn' || $params['type'] == 'partner') {
            $payRevenueStatisticsOrdersnLib = new PayRevenueStatisticsOrdersnLib();
            $data                           = $payRevenueStatisticsOrdersnLib->getList($params);
        } elseif ($params['type'] == 'sku') {
            $payRevenueStatisticsSkuLib = new PayRevenueStatisticsSkuLib();
            $data                       = $payRevenueStatisticsSkuLib->getList($params);
        }

        //导出
        if (isset($params['is_export']) && $params['is_export'] == 1) $this->_index_export($data['data'], $type);
        // --------- 分页 start -------------
        $current_url = url('/count/Purchase/index', '', '');
        if ($_GET) $current_url = $current_url . '?' . http_build_query(array_filter($_GET, function ($val) {
                return $val != '';
            }));

        $userModel = new User();
        $allCguser = $userModel->getAllCguser();
        //分页
        $pager_data = gen_pager_data($params['p'], $data['count'], $params['ps']);
        $this->assign('all_page_num', $pager_data['all_page_num']);
        $this->assign('last_page', $pager_data['last_page']);
        $this->assign('next_page', $pager_data['next_page']);
        $this->assign('current_url', $current_url);
        $this->assign('list_total', $data['count']);
        // --------- 分页 end -------------
        $this->assign('type', $type);
        $this->assign('data', $data['data']);
        $this->assign('total', $data['total']);
        $this->assign('params', $data['params']);
        $this->assign('all_pay_type', $data['all_pay_type']);
        $this->assign('module', 'purchase');
        $this->assign('allCguser', $allCguser);
        return $this->view->fetch("index_data");
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
                'ordersn'         => '采购单',
                'cguser'          => '采购员',
                'paytype'         => '付款类型',
                'partnerName'     => '供应商',
                'amount'          => '采购总金额',
                'paid'            => '已付款',
                'wait_pay'        => '待付款',
                'revenued'        => '已收',
                'collected'       => '待收',
                'real_pay'        => '实付款',
                'total_collected' => '总收',
            ];
            $filename = "应付款报表-按采购单" . date('Y-m-d');
        }
        if ($type == 'partner') {
            $headers  = [
                'partnerName'     => '供应商',
                'paytype'         => '付款方式',
                'amount'          => '采购总金额',
                'ship_fee'        => '运费总金额',
                'paid'            => '已付款',
                'wait_pay'        => '待付款',
                'revenued'        => '已收',
                'collected'       => '待收',
                'real_pay'        => '实付款',
                'total_collected' => '总收',
            ];
            $filename = "应付款报表-按供应商" . date('Y-m-d');
        }
        if ($type == 'sku') {
            $headers  = [
                'sku'             => 'sku',
                'goods_name'      => '品名',
                'ordersn'         => '采购单号',
                'partnerName'     => '供应商',
                'storeName'       => '仓库',
                'cguser'          => '采购员',
                'goods_price'     => '采购均摊价',
                'qty'             => '订购量',
                'amount'          => '总金额',
                'paid'            => '已付',
                'wait_pay'        => '待付',
                'revenued'        => '已收',
                'collected'       => '待收',
                'real_pay'        => '实付',
                'total_collected' => '实收',
            ];
            $filename = "应付款报表-按SKU" . date('Y-m-d');
        }
        ToolsLib::getInstance()->exportExcel($filename, $headers, $data, $is_seq = true);
    }

    /**
     * 查看供应商交易走势
     * @access auth
     * @return string
     * @throws \think\Exception
     */
    public function partnertrend()
    {
        $params = input('get.');
        if (empty($params)) {
            echo "请求异常！";
            exit;
        }
        $type                           = $params['type'];
        $payRevenueStatisticsOrdersnLib = new PayRevenueStatisticsOrdersnLib();
        $data                           = $payRevenueStatisticsOrdersnLib->getPartnerTrend($params);
        $this->assign('jsonName', $data['jsonName']);
        $this->assign('jsonTotal', $data['jsonTotal']);
        $this->assign('jsonQty', $data['jsonQty']);
        $this->assign('parname', $data['parname']);
        return $this->view->config([
            'layout_name' => $this->layout_fluid
        ])->fetch("partner_{$type}_chart");
    }

    /**
     * 获取供应商
     * @AUTHOR: leo
     * @DATE: 2018-10-19
     */
    public function getPartnerByKeyWord()
    {
        $keywords = $_REQUEST['keywords'];
        $partner  = new EbayPartner();
        echo json_encode($partner->getPartnerByKeyWord($keywords));
    }

}
