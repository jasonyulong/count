<?php
/**
 * @Copyright (C), ZhuoShi.
 * @Author: 杨能文
 * @Name: Index.php
 * @Date: 2019/2/25
 * @Time: 10:30
 * @Description 开发产品报表
 */

namespace app\count\controller\develop;

use app\common\controller\AuthController;
use app\count\library\develop\DevelopTypeLib;
use app\common\library\ToolsLib;
use app\count\library\sku\SkuLib;

class Index extends AuthController
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
        $DevelopTypeLib = new DevelopTypeLib();
        $data           = $DevelopTypeLib->getList($params);

        if (isset($_REQUEST['is_export'])) $this->_index_export($data['data'], $type);

        //获取开发人员
        $skuLib = new SkuLib();
        if (isset($params['type']) && $params['type'] == 'develop') {
            $user = $skuLib->getUserInfo(2);
            $this->assign('kfuser', $user);
        }

        $this->assign('list', $data['data']);
        $this->assign('total', $data['total']);
        $this->assign('type', $type);
        $this->assign('model', $model);
        $this->assign('params', $params);
        $this->assign('contents', 'develop');
        $this->assign('module', 'develop');
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
        if ($type == 'develop') $title = '开发员';
        if (isset($data['month'])) unset($data['month']);

        if ($type == 'date') {
            $headers = [
                'one'    => $title,
                'type1'  => '待开发',
                'type12' => '文案',
                'type2'  => '初审',
                'type5'  => '采样',
                'type6'  => '拍图',
                'type8'  => '美工',
                'type9'  => '终审',
                'type10' => '已完成',
                'sum'    => '合计',
            ];
        } else {
            $headers = [
                'develop' => $title,
                'sum'     => '合计',
                'month'   => '本月',
            ];
            foreach ($data as $key => $val) {
                $data[$key]['develop'] = $key;
                foreach ($val as $k => $v) {
                    if (is_numeric($k)) {
                        $k1              = strlen($k) == 8 ? date('Y-m-d', strtotime($k)) : date('Y-m', strtotime($k . "01"));
                        $data[$key][$k1] = $v['type10'];
                        unset($data[$key][$k]);
                        $headers[$k1] = $k1;
                    }
                }
                if (!isset($val['month'])){
                    $data[$key]['month'] = 0;
                }else{
                    $data[$key]['month'] = $val['month']['type10'];
                }
            }
        }

        $filename = "开发产品报表-" . date('Y-m-d');
        ToolsLib::getInstance()->exportExcel($filename, $headers, $data, $is_seq = false);
    }
}