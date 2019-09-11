<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    kevin
 */

namespace app\count\controller\transport;

use app\count\model\transport;
use app\common\library\ToolsLib;
use app\common\controller\Common;
use app\count\library\sku\SkuLib;
use app\common\library\CarrierLib;
use app\common\controller\AuthController;
use app\count\library\transport\TransportBillLib;

/**
 * 物流对账报表
 * @package app\count\controller\transport
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
        $type           = input('get.type', 'date');
        $model          = input('get.model', 'table');
        $params         = input('get.');
        $params['type'] = $params['type'] ?? 'date';


        $params['p'] =  $params['p'] ?? 1;
        $params['ps'] = $params['ps'] ?? 50;
        if(isset($params['is_export']) && $params['is_export'] == 1) $params['ps'] = $params['ps'] ?? 10000;

        $TransportBillLib = new TransportBillLib();
        $data = $TransportBillLib->getList($params);

        $skuLib = new SkuLib();
        //平台
        if(isset($params['type']) && $params['type'] == 'platform'){
            $platform_account = $skuLib->getPlatformAccount(1);
            $this->assign('platform', $platform_account);
        }

        //物流
        if(isset($params['type']) && $params['type'] == 'carrier'){
            $carrier  = CarrierLib::init()->getCarrier();
            if(isset($params['carrier_company'][0])){
                foreach($carrier as $key=>$val){
                    if($val['company'] != $params['carrier_company'][0]){
                        unset($carrier[$key]);
                    }
                }
            }
            $this->assign('carrier', array_keys($carrier));
        }

        //物流公司
        if(isset($params['type']) && ($params['type'] == 'company' || $params['type'] == 'carrier')){
            $company  = CarrierLib::init()->getCarrierCompany();
            $this->assign('company',$company);
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
     * @DATE: 2018-09-22
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
                'apply_into'        => '申请预充值金额($)',
                'apply_pay'         => '申请支付金额($)',
                'wait_pay'          => '待支付金额($)',
                'apply_ing'         => '充值中金额($)',
                'chase_money'       => '追款金额($)',
            ];
        }else{
            $headers = [
                'one'               => $title,
                'finish_orders'     => '已对账单量',
                'finish_money'      => '已对账运费($)',
                'bepaid_orders'     => '待对账总单量',
                'bepaid_money'      => '待对账预估运费($)',
                'finish_weight'     =>'已对账重量(KG)',
                'avg_weight'        =>'已对账平均重量(KG)',
                'bepaid_weight'     =>'待对账重量(KG)',
            ];
        }

        $filename = "物流对账报表-" . date('Y-m-d');
        ToolsLib::getInstance()->exportExcel($filename, $headers, $data, $is_seq = false);
    }
}
