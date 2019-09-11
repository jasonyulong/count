<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    jason
 */

namespace app\count\controller\sku;

use think\Config;
use app\count\library\sku;
use think\cache\driver\Redis;
use app\common\library\ToolsLib;
use app\common\controller\Common;
use app\common\controller\AuthController;

/**
 * SKU销售报表
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
        $start     = microtime(true);
        $skuLib    = new sku\SkuLib();
        $type      = input('get.type', 'sku');
        $model     = input('get.model', 'table');
        $is_export = input('get.is_export', 0);
        $params    = input('get.');
        if (!isset($params['p'])) $params['p'] = 1;
        if (!isset($params['ps'])) $params['ps'] = 20;
        $params['platform'] = $params['platform'] ?? '';

        //当前业务员有权限的平台
        $all_platform = ToolsLib::getInstance()->getAllPlatforms($_SESSION['truename'] ?? '');

        $params['store']    = $params['store'] ?? '';
        $params['category'] = $params['category'] ?? '';
        $params['single']   = $params['single'] ?? '';
        $params['country']  = $params['country'] ?? '';
        $params['sort']     = $params['sort'] ?? 0;
        $params['organ']    = $params['organ'] ?? [];


        if (empty($params['paytime_start']) || empty($params['paytime_end'])) {
            //todo 如果没有选择时间就默认15天的sku的销量
            $half_month = strtotime('-15 day');
            $year       = date('Y', $half_month);//15天前的年份
            $month      = date('m', $half_month);//15天前的月份
            $day        = date('d', $half_month);//15天前的天
            $cur_year   = date('Y');//当前年份
            $cur_month  = date('m');//当前的月份
            $cur_day    = date('d');//当前的天

            $params['paytime_start'] = $year . '-' . $month . '-' . $day;
            $params['paytime_end']   = $cur_year . '-' . $cur_month . '-' . $cur_day;
        }

        //选择的时间区间大于20天的就默认20天的
        if (strtotime($params['paytime_end']) > strtotime($params['paytime_start'])) {
            $d2   = strtotime($params['paytime_end'] . '23:59:59');
            $d1   = strtotime($params['paytime_start'] . '00:00:00');
            $Days = round(($d2 - $d1) / 3600 / 24);

            if ($Days > 15) {
                //最大的时间往前推15天
                $start_days  = strtotime('-15 day', $d2);
                $start_year  = date('Y', $start_days);
                $start_month = date('m', $start_days);
                $start_day   = date('d', $start_days);

                $params['paytime_start'] = $start_year . '-' . $start_month . '-' . $start_day;;

                $end_year  = date('Y', $d2);
                $end_month = date('m', $d2);
                $end_day   = date('d', $d2);

                $params['paytime_end'] = $end_year . '-' . $end_month . '-' . $end_day;;
            }
        }

        //分类
        $child = !empty($params['category']) ? $skuLib->getChild($params['category']) : [];

        //关键字
        $params['keyword'] = $params['keyword'] ?? '';
        //仓库
        $store_arr = $skuLib->getStore();

        $org_list = ToolsLib::getInstance()->getLevel1Orgs($_SESSION['truename'] ?? '');

        //国家
        $country = $skuLib->getCountry();

        //商品父分类
        $category = $skuLib->getCategory();
        //销售员
        $sellers = !empty($params['organ'][0]) ? ToolsLib::getInstance()->getSellerByOrg(array_column(ToolsLib::getInstance()->getOrgById($params['organ']), 'name')) : [];

        if (!empty($params['seller'])) {
            $params['seller'] = is_array($params['seller']) ? $params['seller'] : explode(',', $params['seller']);
            $params['seller'] = array_filter($params['seller'], function ($val) {
                return !empty($val);
            });
        }
        $param = $params;
        //如果有选组织架构没有选择销售人员就默认组织架构下面所有的销售人员
        if(empty($params['seller']) && !empty($sellers)){
            $param['seller'] = $sellers;
            $params['seller'] = [];
        }
        //如果销售员跟组织架构都为空的话就默认为空
        if(empty($params['seller']) && empty($sellers)){
            $param['seller'] = [];
            $params['seller'] = [];
        }

        //获取数据
        $data = $skuLib->getSkuData($param);


        //导出sku销量
        if ($is_export == 1) $this->export_sku($data);
        $current_url = url('/count/sku/index', '', '');
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
        $this->assign('type', $type);
        $this->assign('model', $model);

        $this->assign('params', $params);
        $this->assign('platform', $all_platform);
        $this->assign('store', $store_arr);
        $this->assign('category', $category);
        $this->assign('sellers', $sellers);
        $this->assign('org_list', $org_list);
        $this->assign('country', $country);
        $this->assign('module', 'sku');

        $this->assign('child', $child);
        $this->assign('data', $data['data']);
        $this->assign('date', $data['date']);
        $this->assign('date2', $data['date2']);


        return $this->view->fetch("index_$type");
    }

    /**
     * sku销量合计
     * @access auth
     * @return string
     * @throws \think\EXCEPTION
     */
    public function showskudetail()
    {
        $skuLib = new sku\SkuLib();

        $model  = input('get.model', 'table');
        $params = input('get.');

        $data = $skuLib->showskudetail($params);

        $this->assign('sale_platform', json_encode($data['sale_platform']));
        $this->assign('platform_sale', json_encode($data['platform_sale']));
        $this->assign('select_platform',json_encode($data['total']));
        $this->assign('platform', json_encode($data['platform']));
        $this->assign('date', json_encode($data['date']));
        $this->assign('data', $data);
        $this->assign('model', $model);
        $this->assign('store', isset($params['store']) ? $params['store'] : '');
        $this->assign('category', isset($params['category']) ? $params['category'] : '');
        $this->assign('organ', isset($params['organ']) ? $params['organ'] : []);
        $this->assign('seller', isset($params['seller']) ? $params['seller'] : []);
        $this->assign('country', isset($params['country']) ? $params['country'] : '');
        $this->assign('single', isset($params['single']) ? $params['single'] : '');
        $this->assign('params', $params);

        return $this->view->config([
            'layout_name' => $this->layout_fluid
        ])->fetch();
    }

    /**
     * 账号合计销量
     * @desc 某个sku 某个平台下的所有账号 合计的销量
     * @author jason
     * @date 2018/9/15
     */
    public function totalskudetail()
    {
        $skuLib = new sku\SkuLib();

        $model  = input('get.model', 'table');
        $params = input('get.');
        $data   = $skuLib->totalskudetail($params);

        $this->assign('account', json_encode($data['account']));
        $this->assign('qty', json_encode($data['qty']));
        $this->assign('data', $data['plat_info']);
        $this->assign('model', $model);
        $this->assign('params', $params);

        return $this->view->config([
            'layout_name' => $this->layout_fluid
        ])->fetch();
    }

    /**
     * SKU日销量
     * @desc 某一个日期的sku的销量
     * @access author
     * @return string
     * @author jason
     * @date 2018/9/14
     */
    public function showskutotal()
    {
        $skuLib = new sku\SkuLib();
        $model  = input('get.model', 'table');
        $params = input('get.');
        $data   = $skuLib->showskutotal($params);

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
            $data2[$i][15] = $data_15[$i] ? $data_15[$i] : [];
            if (!empty($data_16[$i])) {
                $data2[$i][16] = $data_16[$i] ? $data_16[$i] : [];
            }

        }

        $data['plat_info'] = $data2;
        $plat              = array_values($data['plat']);
        $sale              = array_values($data['sale']);
        $this->assign('model', $model);
        $this->assign('store', isset($params['store']) ? $params['store'] : '');
        $this->assign('category', isset($params['category']) ? $params['category'] : '');
        $this->assign('organ', isset($params['organ']) ? $params['organ'] : []);
        $this->assign('seller', isset($params['seller']) ? $params['seller'] : []);
        $this->assign('country', isset($params['country']) ? $params['country'] : '');
        $this->assign('single', isset($params['single']) ? $params['single'] : '');
        $this->assign('paytime_start', $params['paytime_start']);
        $this->assign('paytime_end', $params['paytime_end']);
        $this->assign('params', $params);
        $this->assign('plat_info', $data['plat_info']);
        $this->assign('plat', json_encode($plat));
        $this->assign('sale', json_encode($sale));

        return $this->view->config([
            'layout_name' => $this->layout_fluid
        ])->fetch();
    }


    /**
     * 单个SKU销量
     * @access auth
     * @return string
     * @throws \think\EXCEPTION
     */
    public function singlesku()
    {
        $skuLib = new sku\SkuLib();
        $model  = input('get.model', 'table');
        $params = input('get.');

        $data = $skuLib->singlesku($params);

        $this->assign('account', json_encode($data['account']));
        $this->assign('qty', json_encode($data['qty']));
        $this->assign('data', $data['plat_info']);
        $this->assign('model', $model);
        $this->assign('store', isset($params['store']) ?? '');
        $this->assign('category', isset($params['category']) ?? '');
        $this->assign('organ', isset($params['organ']) ?? []);
        $this->assign('seller', isset($params['seller']) ?? []);
        $this->assign('country', isset($params['country']) ?? '');
        $this->assign('single', isset($params['single']) ?? '');
        $this->assign('paytime_start', $params['paytime_start']);
        $this->assign('paytime_end', $params['paytime_end']);
        $this->assign('params', $params);

        return $this->view->config([
            'layout_name' => $this->layout_fluid
        ])->fetch();
    }

    /**
     * 获取分类
     * @desc 获取子分类
     * @author jason
     * @date 2018/9/17
     * @return \think\response\Json
     *
     */
    public function selectcategory()
    {
        if (IS_AJAX) {
            $skuLib = new sku\SkuLib();
            $params = input('post.');
            $data   = $skuLib->selectcategory($params);
            if (empty($data)) {
                $return_data = ['status' => 2];
            } else {
                $return_data = ['status' => 2, 'data' => $data];
            }
            return json($return_data);
        }
    }


    /**
     * 导出
     * @author 玉龙哥哥
     * @date 2019-01-03 16:46:27
     * @return void
     */
    public function export_sku($data)
    {
        $filename = 'SKU销量导出-' . date('Y-m-d');
        $datas    = $data['data'];
        $date2    = $data['date2'];
        $count    = count($data['date']);
        if ($count > 31) {
            echo '<div style="font-size:18px;color:red;">导出的数据必须是31天以内的数据</div>';
            exit;
        }
        $header = [
            'sku'    => 'SKU',
            'thumb'  => '图片',
            'name'   => '标题',
            'qtySum' => '合计'
        ];
        $dates  = [];
        foreach ($date2 as $k => $v) {
            $dates[$v] = $v;
        }
        $header = array_merge($header, $dates);

        $data_arr = [];
        foreach ($datas as $sku => $list) {
            $data_arr[$sku]['sku']    = $sku;
            $data_arr[$sku]['thumb']  = $list['data']['thumb'];
            $data_arr[$sku]['name']   = $list['data']['name'];
            $data_arr[$sku]['qtySum'] = $list['data']['qtySum'];
            foreach ($date2 as $key => $val) {
                $va                   = isset($list['date'][$val]) ? array_sum($list['date'][$val]) : 0;
                $data_arr[$sku][$val] = $va;
            }
        }
        //        echo '<pre>';print_r($data_arr);exit;
        ToolsLib::getInstance()->exportExcel($filename, $header, $data_arr, false);
        exit;


        //        ToolsLib::getInstance()->exportSkuData($data, $data['date'],$data['date2']);
    }
}