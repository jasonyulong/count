<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    kevin
 */

namespace app\count\controller\sku;

use think\Request;
use app\count\library\sku;
use app\common\controller\Common;
use app\count\model\SkuPackageCate;
use app\common\controller\AuthController;

/**
 * SKU产品包销量
 * @package app\count\controller\seller
 */
class Packages extends AuthController
{
    /**
     * 查看
     * @access auth
     * @return string
     * @throws \think\Exception
     */
    public function index()
    {
        $skuLib      = new sku\SkuLib();
        $PackagesLib = new sku\PackagesLib();
        $model       = input('get.model', 'table');
        $params      = input('get.');

        $CategoryLib = new sku\CategoryLib();
        $Category123 = $CategoryLib->getCategory123();
        $this->assign('category123', $Category123);

        if (empty($params['time_start']) || empty($params['time_end'])) {
            //todo 如果没有选择时间就默认15天的sku的销量
            $half_month = strtotime('-15 day');
            $year       = date('Y', $half_month);//15天前的年份
            $month      = date('m', $half_month);//15天前的月份
            $day        = date('d', $half_month);//15天前的天
            $cur_year   = date('Y');//当前年份
            $cur_month  = date('m');//当前的月份
            $cur_day    = date('d');//当前的天

            $params['time_start'] = $year . '-' . $month . '-' . $day;
            $params['time_end']   = $cur_year . '-' . $cur_month . '-' . $cur_day;
        }

        //选择的时间区间大于20天的就默认20天的
        if (strtotime($params['time_end']) > strtotime($params['time_start'])) {
            $d2   = strtotime($params['time_end'] . '23:59:59');
            $d1   = strtotime($params['time_start'] . '00:00:00');
            $Days = round(($d2 - $d1) / 3600 / 24);

            if ($Days > 14) {
                //最大的时间往前推20天
                $start_days           = strtotime('-14 day', $d2);
                $start_year           = date('Y', $start_days);
                $start_month          = date('m', $start_days);
                $start_day            = date('d', $start_days);
                $params['time_start'] = $start_year . '-' . $start_month . '-' . $start_day;;

                $end_year           = date('Y', $d2);
                $end_month          = date('m', $d2);
                $end_day            = date('d', $d2);
                $params['time_end'] = $end_year . '-' . $end_month . '-' . $end_day;;
            }
        }


        //当前业务员只能看到自己添加的产品包
        $getAllPower       = getAllPower();
        $username          = $getAllPower['truename'] ?? '';
        $username          = getRolePower() == false ? $username : '';

        $params['adduser'] = !empty($params['adduser']) ? $params['adduser'] : '';
        //添加人
        $adduser = $PackagesLib->getAdduser($username);

        //产品包
        $groupsn = $PackagesLib->getGroupsn($username);

        //如果产品包为空就默认一个
        $params['group_sn'] = !empty($params['group_sn']) ? $params['group_sn'] : '';
        $params['keyword']  = !empty($params['keyword']) ? $params['keyword'] : '';
        if (empty($params['keyword']) && empty($params['adduser']) && empty($params['group_sn'])) {
            $params['group_sn'] = !empty($groupsn[0]['group_sn']) ? $groupsn[0]['group_sn'] : 'all';
        }

        //按关键字搜索
        $params['keys'] = (isset($params['keys']) && $params['keys']) ? $params['keys'] : '';
        if (!empty($params['keys'])) $params['group_sn'] = $params['keys'];

        $data  = $PackagesLib->getPackageIndex($params);
//        echo '<pre>';print_r($data);exit;
        $datas = [];
        foreach ($data['productGroupLists'] as $key => $value) {
            $datas[$key]          = $value;
            $large                = !empty($value['goods_pic']) ? str_replace('/small', '', $value['goods_pic']) : '';
            $datas[$key]['large'] = $large;
        }
        $qtyTotals = $data['qtyTotals'];

        $this->assign('dates', $data['dates']);
        $this->assign('total', $data['total']);
        $this->assign('qtyTotals', $qtyTotals);
        $this->assign('qtyTotals_keys', json_encode(array_keys($qtyTotals)));
        $this->assign('qtyTotals_vals', json_encode(array_values($qtyTotals)));
        $this->assign('groupsn', $groupsn);
        $this->assign('adduser', $adduser);
        $this->assign('model', $model);
        $this->assign('params', $params);
        $this->assign('module', 'sku');
        $this->assign('data', $datas);
        $this->assign('id', $data['id']);

        return $this->view->fetch();
    }

    /**
     * 子系列
     * @access auth
     * @return string
     * @throws \think\Exception
     */
    public function viewdetail()
    {
        $PackagesLib = new sku\PackagesLib();
        $model       = input('get.model', 'table');
        $params      = input('get.');

        $data   = $PackagesLib->viewdetail($params);
        $skuArr = [];
        foreach ($data['data'] as $list) {
            $skuArr[$list['sku']] = $list['qtySum'];
        }
        $key   = json_encode(array_keys($skuArr));
        $value = json_encode(array_values($skuArr));
        $this->assign('key', $key);
        $this->assign('value', $value);
        $this->assign('date', $data['date']);
        $this->assign('model', $model);
        $this->assign('params', $params);
        //        echo '<pre>';print_r($skuArr);exit;
        $this->assign('data', $data['data']);
        return $this->view->config([
            'layout_name' => $this->layout_fluid
        ])->fetch();
    }

    /**
     * 子系列中的合计 曲线图
     * @access auth
     * @return string
     * @throws \think\Exception
     */
    public function sontotal()
    {
        $model  = input('get.model');
        $params = input('get.');

        $this->assign('xkey', $params['keys']);
        $this->assign('yvalue', $params['values']);
        return $this->view->config([
            'layout_name' => $this->layout_fluid
        ])->fetch();
    }

    /**
     * 父sku销量
     * @access auth
     * @return string
     * @throws \think\Exception
     */
    public function btobnumberdetail()
    {
        $PackagesLib = new sku\PackagesLib();
        $model       = input('get.model', 'table');
        $params      = input('get.');

        $data = $PackagesLib->btobnumberdetail($params);
        //        echo '<pre>';print_r($data);exit;
        $plat_info = [];
        foreach ($data['plat_info'] as $val) {
            $info['platform']         = $val['platform'];
            $info['qty']              = $val['qty'];
            $info['sku']              = $val['sku'];
            $info['time']             = $val['time'];
            $plat_info['plat_info'][] = $info;
        }
        $data_15 = [];
        $n       = 0;
        $data_16 = [];
        $m       = 0;
        foreach ($plat_info['plat_info'] as $key => $val) {
            if ($key >= 0 && $key <= 10) {
                $data_15[$n]['platform'] = $val['platform'];
                $data_15[$n]['qty']      = $val['qty'];
                $data_15[$n]['sku']      = $val['sku'];
                $data_15[$n]['time']     = $val['time'];
                $n++;
            }
            if ($key >= 11) {
                $data_16[$m]['platform'] = $val['platform'];
                $data_16[$m]['qty']      = $val['qty'];
                $data_16[$m]['sku']      = $val['sku'];
                $data_16[$m]['time']     = $val['time'];
                $m++;
            }
        }

        $count15 = count($data_15);
        $count16 = count($data_16);
        $count   = ($count15 > $count16) ? $count15 : $count16;
        $data2   = [];
        for ($i = 0; $i < $count; $i++) {
            if (!empty($data_15[$i])) {
                $data2[$i][15] = $data_15[$i] ? $data_15[$i] : [];
            }
            if (!empty($data_16[$i])) {
                $data2[$i][16] = $data_16[$i] ? $data_16[$i] : [];
            }
        }
        $data['plat_info'] = $data2;
        //        echo '<pre>';print_r($data);exit;
        $this->assign('model', $model);
        $this->assign('params', $params);
        $this->assign('plat_info', $data['plat_info']);
        $this->assign('plat', json_encode($data['plat']));
        $this->assign('sale', json_encode($data['sale']));
        return $this->view->config([
            'layout_name' => $this->layout_fluid
        ])->fetch();
    }

    /*
     * 单个sku销量
     * @access auth
     * @return string
     * @throws \think\Exception
     */
    public function singledetail()
    {
        $PackagesLib = new sku\PackagesLib();
        $model       = input('get.model', 'table');
        $params      = input('get.');

        $data = $PackagesLib->singledetail($params);
        //        echo '<pre>';print_r($data);exit;
        $this->assign('keys', json_encode($data['keys']));
        $this->assign('values', json_encode($data['values']));
        $this->assign('model', $model);
        $this->assign('params', $params);
        $this->assign('data', $data['data']);
        return $this->view->config([
            'layout_name' => $this->layout_fluid
        ])->fetch();
    }


    /**
     * 子集 sku在各个平台的销量
     * @author jason
     * @date 2018/9/19
     * @return string
     */
    public function totalsumsale()
    {
        $PackagesLib = new sku\PackagesLib();
        $params      = input('get.');

        $this->assign('params', $params);

        return $this->view->config([
            'layout_name' => $this->layout_fluid
        ])->fetch();

    }

    /**
     * 某个日期的某个sku在某个平台的销量
     * @author jason
     * @date 2018/9/19
     * @return mixed
     */
    public function singlesku()
    {
        $PackagesLib = new sku\PackagesLib();
        $model       = input('get.model', 'table');
        $params      = input('get.');

        $data = $PackagesLib->singlesku($params);

        $this->assign('account', json_encode($data['account']));
        $this->assign('qty', json_encode($data['qty']));
        $this->assign('data', $data['plat_info']);
        $this->assign('model', $model);
        $this->assign('params', $params);
        return $this->view->config([
            'layout_name' => $this->layout_fluid
        ])->fetch("singlesku");
    }

    /**
     * 合计
     * @author jason
     * @date 2018/9/19
     * @return mixed
     */
    public function totaldetail()
    {
        $PackagesLib = new sku\PackagesLib();
        $model  = input('get.model', 'chart');
        $params = input('get.');
        $this->assign('model', $model);
        $this->assign('params', $params);

        $data = $PackagesLib->totaldetail($params);
        $this->assign('sale_platform', json_encode($data['sale_platform']));
        $this->assign('platform_sale', json_encode($data['platform_sale']));
        $this->assign('platform', json_encode($data['platform']));
        $this->assign('date', json_encode($data['date']));
        $this->assign('data', $data);

        return $this->view->config([
            'layout_name' => $this->layout_fluid
        ])->fetch();
    }


    /**
     * 修改(添加)产品
     * @access auth
     * @return string
     * @throws \think\Exception
     */
    public function updatePackage()
    {
        $packagesLib = new sku\PackagesLib();
        $CategoryLib = new sku\CategoryLib();
        $Category    = $CategoryLib->getCategory12();
        $Category3   = $CategoryLib->getCategory3();
        $this->assign('category', $Category);
        $this->assign('category3', $Category3);

        //产品包提交数据处理
        if (Request::instance()->isAjax()) {
            $data   = input('post.');
            $result = $packagesLib->updatePackage($data);
            return $result;
        }

        //页面数据展示
        $model  = input('get.model', 'table');
        $params = input('get.');
        if (isset($params['id'])) {
            $packageObj  = $packagesLib->getPackageInfo($params['id']);
            $packageInfo = replace_query($packageObj);
            $package     = ['id' => $packageInfo['id'], 'sku' => $packageInfo['sku_list'], 'title' => $packageInfo['title'], 'group_sn' => $packageInfo['group_sn'], 'cate_id' => $packageInfo['cate_id']];
            $this->assign('package', $package);
        } else {
            $exist_group_sn = $packagesLib->getPackageInfo();
            $package        = ['id' => 0, 'sku' => '', 'title' => '', 'group_sn' => get_group_sn($exist_group_sn), 'cate_id' => 0];
            $this->assign('package', $package);
        }
        $this->assign('model', $model);
        $this->assign('params', $params);
        return $this->view->config([
            'layout_name' => $this->layout_fluid
        ])->fetch();
    }

    /**
     * 新增产品包分类
     * @return string
     */
    public function addCategory()
    {
        $CategoryLib = new sku\CategoryLib();
        if (Request::instance()->isAjax()) {
            $data   = input('post.');
            $result = $CategoryLib->addCategory($data);
            return $result;
        }

        $this->assign('category', $CategoryLib->getCategory12());
        return $this->view->config([
            'layout_name' => $this->layout_fluid
        ])->fetch();
    }

    /**
     * 编辑产品包分类
     * @return string
     */
    public function editCategory()
    {
        $CategoryLib = new sku\CategoryLib();
        if (Request::instance()->isAjax()) {
            $data   = input('post.');
            $result = $CategoryLib->editCategory($data);
            return $result;
        }

        if (isset($_REQUEST['id']) && $_REQUEST['id']) {
            $SkuPackageCate = new SkuPackageCate();
            $obj            = $SkuPackageCate->where('id', $_REQUEST['id'])->find();
            $this->assign('info', replace_query($obj));
        }
        $this->assign('category', $CategoryLib->getCategory12());
        return $this->view->config([
            'layout_name' => $this->layout_fluid
        ])->fetch();
    }

    /**
     * 产品包分类管理
     * @return string
     */
    public function category()
    {
        $CategoryLib = new sku\CategoryLib();
        $Category    = $CategoryLib->getCategory();
        $Category3   = isset($Category['rank3']) ? $Category['rank3'] : [];
        unset($Category['rank3']);
        $this->assign('category', $Category);
        $this->assign('category3', $Category3);
        return $this->view->config([
            'layout_name' => $this->layout_fluid
        ])->fetch();
    }

    /**
     * 删除产品包
     * @access auth
     * @return string
     * @throws \think\Exception
     */
    public function deletePackage()
    {
        $packagesLib = new sku\PackagesLib();
        if (Request::instance()->isAjax()) {
            $id     = input('post.id'); //产品包主键
            $result = $packagesLib->deletePackage($id);
            if ($result) {
                return json(['status' => 1, 'msg' => '删除成功']);
            } else {
                return json(['status' => 2, 'msg' => '删除失败']);
            }
        }
    }

    /**
     * 删除产品包分类
     * @access auth
     * @return string
     * @throws \think\Exception
     */
    public function deleteCategory()
    {
        $CategoryLib = new sku\CategoryLib();
        if (Request::instance()->isAjax()) {
            $id     = input('post.id'); //主键
            $result = $CategoryLib->deleteCategory($id);
            return $result;
        }
    }
}