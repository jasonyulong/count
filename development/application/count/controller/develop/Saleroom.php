<?php
/**
 * @Copyright (C), ZhuoShi.
 * @Author: 杨能文
 * @Name: Saleroom.php
 * @Date: 2019/2/25
 * @Time: 11:21
 * @Description
 */

namespace app\count\controller\develop;

use app\common\controller\AuthController;
use app\count\library\develop\DevelopSalesLib;
use app\common\library\ToolsLib;
use app\count\library\sku\SkuLib;

class Saleroom extends AuthController
{
    /**
     * @desc 开发产品表
     * @author 杨能文
     * @date 2019/2/25 10:31
     * @access public
     */
    public function index()
    {
        $type           = input('get.type', 'date');
        $model          = input('get.model', 'table');
        $params         = input('get.');
        $params['type'] = $params['type'] ?? 'date';

        $params['p']  = $params['p'] ?? 1;
        $params['ps'] = $params['ps'] ?? 50;
        if (isset($params['is_export']) && $params['is_export'] == 1) $params['ps'] = $params['ps'] ?? 10000;

        $DevelopTypeLib = new DevelopSalesLib();
        $data           = $DevelopTypeLib->getList($params);

        if (isset($_REQUEST['is_export'])) $this->_index_export($data['data'], $type);

        //获取开发人员
        $skuLib = new SkuLib();
        $user   = $skuLib->getUserInfo(2);
        $this->assign('kfuser', $user);

        $this->assign('list', $data['data']);
        $this->assign('total', $data['total']);
        $this->assign('type', $type);
        $this->assign('model', $model);
        $this->assign('params', $params);
        $this->assign('contents', 'develop');
        $this->assign('module', 'develop');
        $this->assign('params', $data['params']);
        return $this->view->fetch();
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
        $title   = '开发员';
        $headers = [
            'develop' => $title,
            'sum'     => '合计',
            'month'   => '本月',
        ];
        foreach ($data as $key => $val) {
            $data[$key]['develop'] = $key;
            foreach ($val as $k => $v) {
                if (is_numeric($k)) {
                    $k1 = strlen($k) == 8 ? date('Y-m-d', strtotime($k)) : date('Y-m', strtotime($k . "01"));
                    unset($data[$key][$k]);
                    $data[$key][$k1] = $v['sales'];
                    $headers[$k1]    = $k1;
                }
                if ($k == 'month') {
                    $data[$key][$k] = $v['sales'];
                }
            }
        }

        $filename = "开发员销售报表-" . date('Y-m-d');
        ToolsLib::getInstance()->exportExcel($filename, $headers, $data, $is_seq = false);
    }
}