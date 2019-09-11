<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    yang
 */

namespace app\count\controller\transport;


use app\count\model\transport;
use app\common\library\ToolsLib;
use app\common\controller\Common;
use app\count\library\sku\SkuLib;
use app\common\library\CarrierLib;
use app\common\controller\AuthController;
use app\count\library\transport\TransportOutlayLib;

/**
 * 物流支出报表
 * @package app\count\controller\transport
 */
class Expense extends AuthController
{
    /**
     * 列表
     * @author lamkakyun
     * @date 2019-01-03 15:58:42
     * @return void
     */
    public function index()
    {
        $type           = input('get.type', 'date');
        $model          = input('get.model', 'table');
        $params         = input('get.');
        $params['type'] = $params['type'] ?? 'date';


        $params['p'] =  $params['p'] ?? 1;
        $params['ps'] = $params['ps'] ?? 50;
        if(isset($params['is_export']) && $params['is_export'] == 1) $params['ps'] = $params['ps'] ?? 10000;

        $TransportOutlayLib = new TransportOutlayLib();
        $data = $TransportOutlayLib->getList($params);

        //物流公司
        if(isset($params['type']) && $params['type'] == 'company'){
            $carrier  = CarrierLib::init()->getCarrierCompany();
            $this->assign('company',$carrier);
        }

        if(isset($_REQUEST['is_export']))$this->_index_export($data['data'],$type);

        $this->assign('list',$data['data']);
        $this->assign('total',$data['total']);
        $this->assign('type', $type);
        $this->assign('model', $model);
        $this->assign('params', $params);
        $this->assign('contents', 'transport');
        $this->assign('module', 'transport');
        $this->assign('params', $data['params']);
        return $this->view->fetch("index_date");
    }

    /**
     * 导出
     * @AUTHOR: 杨能文
     * @param $data
     * @param $type
     * @DATE: 2018-11-06
     */
    private function _index_export($data, $type)
    {
        if ($type == 'date') $title = '日期';
        if ($type == 'company') $title = '物流公司';
        if ($type == 'carrier') $title = '物流渠道';
        if ($type == 'platform') $title = '平台';


        if ($type == 'date'){
            $headers = [
                'one'               => $title,
                'finish_orders'     => '已对账单量',
                'finish_money'      => '已对账运费($)',
                'bepaid_orders'     => '待对账总单量',
                'bepaid_money'      => '待对账预估运费($)',
                'apply_into'        => '申请预充值($)',
                'apply_end'         => '完成预充值($)',
                'apply_pay'         => '申请支付运费($)',
                'finish_pay'        => '已支付运费($)',
                'wait_pay'          => '待支付运费($)',
                'chase_money'       => '追款金额($)',
                'pay_sum'           => '支出合计($)',
            ];
        }else{
            $headers = [
                'one'               => $title,
                'finish_orders'     => '已对账单量',
                'finish_money'      => '已对账运费($)',
                'bepaid_orders'     => '待对账总单量',
                'bepaid_money'      => '待对账预估运费($)',
                'apply_into'        => '申请预充值($)',
                'apply_end'         => '完成预充值($)',
                'apply_pay'         => '申请支付运费($)',
                'finish_pay'        => '已支付运费($)',
                'wait_pay'          => '待支付运费($)',
                'total_pay'         => '历史总支付($)',
                'total_apply'       => '历史总充值($)',
                'pay_sum'           => '支出合计($)',
            ];
        }

        $filename = "物流对账报表-" . date('Y-m-d');
        ToolsLib::getInstance()->exportExcel($filename, $headers, $data, $is_seq = false);
    }
}