<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    jason
 */

namespace app\count\library\sku;

use think\Db;
use think\Config;
use app\count\model\Sku;
use app\count\model\SkuDate;
use app\count\library\OrgLib;
use app\count\model\SkuStore;
use think\cache\driver\Redis;
use app\common\model\ErpGoods;
use app\count\model\SkuSeller;
use app\count\model\SkuCountry;
use app\common\library\ToolsLib;
use app\count\model\SkuCategory;
use app\count\model\SkuPlatform;
use app\count\model\SkuDeveloper;
use app\common\model\ErpGoodsCombine;
use app\count\library\order\OrderLib;

/*
 * sku销量
 * @author jason
 * @date 2018/9/13
 */

class NewSkuLib
{
    private static $instance = null;
    
    /**
     * single pattern
     * @AUTHOR: Lamkakyun
     * @DATE: 2019-02-18 14:57:16
     */
    public static function getInstance(): NewSkuLib
    {
        if (!static::$instance) {
            static::$instance = new NewSkuLib();
        }
        return static::$instance;
    }

    public function __construct()
    {
        $this->skuModel = new Sku();
        $this->skuDateModel = new SkuDate();
        $this->skuCategoryModel = new SkuCategory();
        $this->skuCountryModel = new SkuCountry();
        $this->skuSellerModel = new SkuSeller();
        $this->skuDeveloperModel = new SkuDeveloper();
        $this->skuStoreModel = new SkuStore();
        $this->skuPlatformModel = new SkuPlatform();

        $this->erpGoodsCombineModel = new ErpGoodsCombine();
        $this->erpGoods = new ErpGoods();
    }

    /**
     * 用到的地方比较多，封装一下，处理时间维度 的方法
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-09-19 05:52:56
     */
    private function _handleQueryDate($params, $where = [])
    {
        if (isset($params['scantime_start'])) $params['scantime_start'] .= ' 00:00:00';
        if (isset($params['scantime_end'])) $params['scantime_end'] .= ' 23:59:59';
        if (isset($params['scandate_start'])) $params['scandate_start'] .= '-01';
        if (isset($params['scandate_end'])) $params['scandate_end'] .= '-' . get_day_of_month(date('m', strtotime($params['scandate_end']))) . " 23:59:59";

        if ($params['checkDate'] == 'day') {
            if (isset($params['scantime_start'])) $where['datetime'][] = ['EGT', strtotime($params['scantime_start'])];
            if (isset($params['scantime_end'])) $where['datetime'][] = ['LT', strtotime($params['scantime_end'])];
        } else {
            if (isset($params['scandate_start'])) $where['datetime'][] = ['EGT', strtotime($params['scandate_start'])];
            if (isset($params['scandate_end'])) $where['datetime'][] = ['LT', strtotime($params['scandate_end'])];
        }

        return $where;
    }


    /**
     * 获取sku销售-按日期
     * @author lamkakyun
     * @date 2019-02-19 14:04:00
     * @return void
     */
    public function getSkuDateSaleList($params)
    {
        $type = $params['type'];
        $where = $this->_handleQueryDate($params, []);
        if (!empty($_SESSION['truename'])) {
            // $platform = ToolsLib::getInstance()->getCanViewPlatform($_SESSION['truename']);
            // if ($platform) $where['platform'] = ['IN', $platform];
        }

        // if (isset($params['platform']) && !empty($params['platform'])) $where['platform'] = ['IN', $params['platform']];
        // if (isset($params['account']) && !empty($params['account'])) $where['platform_account'] = ['IN', $params['account']];

        $sort_arr = explode(',', $params['sort_field']);
        $order_by = implode(',', array_map(function ($val) use ($params) {
            return "{$val} {$params['sort']}";
        }, $sort_arr));

        $start = ($params['p'] - 1) * $params['ps'];

        $count = $this->skuDateModel->where($where)->count();

        if ($params['checkDate'] == 'day') 
        {
            $range = range_day($params['scantime_end'], $params['scantime_start'], true);

            $_fields = '*';
            $data = $this->skuDateModel->field($_fields)->where($where)->select()->toArray();
        }
        else 
        {
            $range = range_month($params['scandate_end'], $params['scandate_start']);

            $_fields = 'year, month, SUM(counts) as counts, SUM(totals) as totals, SUM(costs) as costs, SUM(sales) as sales';
            $group_by = 'year, month';
            $data = $this->skuDateModel->field($_fields)->where($where)->group($group_by)->select()->toArray();
        }
        
        if (isset($params['debug']) == 'sql') 
        {
            echo '<pre>';var_dump($this->skuDateModel->getLastSql());echo '</pre>';
        }

        $ret_data = ['list' => $data, 'count' => $count];

        // todo：将 date 放到 key 的位置上
        $tmp              = $ret_data['list'];
        $ret_data['list'] = [];
        foreach ($tmp as $key => $value) {
            $tmp_key                    = $params['checkDate'] == 'day' ? "{$value['year']}-{$value['month']}-{$value['days']}" : "{$value['year']}-{$value['month']}";
            $ret_data['list'][$tmp_key] = $value;
        }

        return $ret_data;
    }

    /**
     * 获取sku销售-按账号 (废弃)
     * @author lamkakyun
     * @date 2019-02-19 14:04:00
     * @return void
     */
    /*
    public function getSkuAccountSaleList($params)
    {
        $where = $this->_handleQueryDate($params, []);
        if (!empty($_SESSION['truename'])) {
            $platform = ToolsLib::getInstance()->getCanViewPlatform($_SESSION['truename']);
            if ($platform) $where['platform'] = ['IN', $platform];
        }

        if (isset($params['platform']) && !empty($params['platform'])) $where['platform'] = ['IN', $params['platform']];
        if (isset($params['account']) && !empty($params['account'])) $where['platform_account'] = ['IN', $params['account']];

        $sort_arr = explode(',', $params['sort_field']);
        $order_by = implode(',', array_map(function ($val) use ($params) {
            return "{$val} {$params['sort']}";
        }, $sort_arr));

        $start = ($params['p'] - 1) * $params['ps'];

        if ($params['checkDate'] == 'day') $range = range_day($params['scantime_end'], $params['scantime_start'], true);
        else $range = range_month($params['scandate_end'], $params['scandate_start']);

        $_group_by = $params['checkDate'] == 'month' ? 'sku, month, year' : 'sku, days, month, year'; // 4 个字段的唯一性
        $_fields = "{$_group_by}, sku_combine, parent, name, thumb, SUM(qty) as sum_qty";

        $ret_data = OrderLib::getInstance()->_getGroupByCountAndList($this->skuPlatformModel, $where, $_group_by, $_fields, $start, $params['ps'], $order_by);

        // echo '<pre>';var_dump($ret_data);echo '</pre>';
        // exit;

        // todo:重组数据 (就算没有数据 也要默认给 空数组)
        $ret_data_reshape = [];
        foreach ($ret_data['list'] as $value) {
            foreach ($range as $v) {
                $ret_data_reshape[$value['sku']][$v] = ['sum_qty' => '0', 'sku' => $value['sku'], 'name' => $value['name'], 'thumb' => $value['thumb']];
            }
        }

        // echo '<pre>';var_dump($ret_data_reshape);echo '</pre>';
        // exit;

        foreach ($ret_data['list'] as $value) {
            if ($params['checkDate'] == 'month') $ret_data_reshape[$value['sku']]["{$value['year']}-{$value['month']}"] = $value;
            else $ret_data_reshape[$value['sku']]["{$value['year']}-{$value['month']}-{$value['days']}"] = $value;
        }

        $ret_data['list']  = $ret_data_reshape;
        $ret_data['count'] = count($ret_data_reshape);
        return $ret_data;
    }
    */


    /**
     *  获取sku销售 (合并 所有的 sku 销售的 方法)
     * @author lamkakyun
     * @date 2019-02-27 11:49:25
     * @return void
     */
    public function getSkuSaleList($params)
    {
        $where = $this->_handleQueryDate($params, []);
        if (!empty($_SESSION['truename'])) {
            // $platform = ToolsLib::getInstance()->getCanViewPlatform($_SESSION['truename']);
            // if ($platform) $where['platform'] = ['IN', $platform];
        }

        switch ($params['type'])
        {
            case 'account':
                // 处理平台
                if (isset($params['platform']) && !empty($params['platform'])) $where['platform'] = ['IN', $params['platform']];
                if (isset($params['account']) && !empty($params['account'])) $where['platform_account'] = ['IN', $params['account']];

                $model = $this->skuPlatformModel;

                break;
            
            case 'cat':
                // 处理分类
                if (isset($params['cat_id']) && !empty($params['cat_id'])) $where['category_id'] = $params['cat_id'];
                if (isset($params['sub_cat_id']) && !empty($params['sub_cat_id'])) $where['category_child_id'] = ['IN', $params['sub_cat_id']];

                $model = $this->skuCategoryModel;

                break;

            case 'seller':
                // 处理销售员
                if (isset($params['seller']) && !empty($params['seller'])) $where['seller'] = ['IN', $params['seller']];
                if (isset($params['organ']) && !empty($params['organ'])) 
                {
                    if (!(count($params['organ']) == 1 && $params['organ'][0] == '')) {
                        $all_sub_org_ids = ToolsLib::getInstance()->getSubOrgIds($params['organ'][0]);
                        if ($all_sub_org_ids) $where['sales_branch_id'] = ['IN', $all_sub_org_ids];
                    }
                }

                $model = $this->skuSellerModel;

                break;

            case 'developer':
                // 处理开发员
                if (isset($params['developer']) && !empty($params['developer'])) $where['developer'] = ['IN', $params['developer']];

                $model = $this->skuDeveloperModel;

                break;

            case 'country':
                // 处理国家
                if (isset($params['country']) && !empty($params['country'])) $where['couny'] = ['IN', $params['country']];

                $model = $this->skuCountryModel;

                break;

            case 'store':
                // 处理仓库
                if (isset($params['store_id']) && !empty($params['store_id'])) $where['store_id'] = ['IN', $params['store_id']];

                $model = $this->skuStoreModel;

                break;
        }

        $sort_arr = explode(',', $params['sort_field']);
        $order_by = implode(',', array_map(function ($val) use ($params) {
            return "{$val} {$params['sort']}";
        }, $sort_arr));

        $sort_more = [
            'select_fields' => 'sku, SUM(qty) as sum_qty',
            'sort_field' => 'sum_qty',
            'sort_date' => $params['sort_date'] ?? '',
            'sort' => $params['sort_more'] ?? '',
        ];

        if ($sort_more['sort'] && $sort_more['sort_date'])
        {
            $_tmp_where = $where;
            $_tmp_where2 = $where;
            if ($sort_more['sort_date'] != 'all') 
            {
                if ($params['checkDate'] == 'day') 
                {
                    $_tmp_where['datetime'] = strtotime($sort_more['sort_date']);
                    $_tmp_where2['datetime'][] = ['NEQ', strtotime($sort_more['sort_date'])];
                }
                else
                {
                    $_tmp_where['datetime'][] = ['EGT', strtotime($sort_more['sort_date'])];
                    $_tmp_where['datetime'][] = ['LT', strtotime($sort_more['sort_date'] . ' +1 month')];
                    // $_tmp_where2['datetime'][] = ['LT', strtotime($sort_more['sort_date'])];
                    // $_tmp_where2['datetime'][] = ['EGT', strtotime($sort_more['sort_date'] . ' +1 month')];
                    $_tmp_where2['datetime'][] = [['LT', strtotime($sort_more['sort_date'])],['EGT', strtotime($sort_more['sort_date'] . ' +1 month')], 'OR'];
                }
            }
            $sort_more['tmp_where'] = $_tmp_where;

            $sort_more['tmp_where2'] = $_tmp_where2;
        }

        if (isset($params['is_export']) && $params['is_export'] == 1) $sort_more = [];

        $sku_keyword_arr = preg_split('/[\s,，]+/', trim($params['sku_keyword']));
        foreach ($sku_keyword_arr as $key => $value)
        {
            if (empty($value)) unset($sku_keyword_arr[$key]);
        }

        if ($sku_keyword_arr) $where['sku'] = ['IN', $sku_keyword_arr];

        $start = ($params['p'] - 1) * $params['ps'];

        if ($params['checkDate'] == 'day') $range = range_day($params['scantime_end'], $params['scantime_start'], true);
        else $range = range_month($params['scandate_end'], $params['scandate_start']);

        $_group_by1 = 'sku';
        $_group_by2 = $params['checkDate'] == 'month' ? 'sku, month, year' : 'sku, days, month, year'; // 4 个字段的唯一性
        $_fields = "{$_group_by2}, sku_combine, parent, name, thumb, SUM(qty) as sum_qty";

        $ret_data = OrderLib::getInstance()->_getGroupByCountAndList2($model, $where, $_group_by1, $_group_by2, $_fields, $start, $params['ps'], $order_by, $sort_more);

        // echo '<pre>';var_dump($ret_data);echo '</pre>';
        // exit;

        // todo:重组数据 (就算没有数据 也要默认给 空数组)
        $ret_data_reshape = [];
        foreach ($ret_data['list'] as $value) {
            foreach ($range as $v) {
                $ret_data_reshape[$value['sku']][$v] = ['sum_qty' => '0', 'sku' => $value['sku'], 'name' => $value['name'], 'thumb' => $value['thumb']];
            }
        }

        foreach ($ret_data['list'] as $value) {
            if ($params['checkDate'] == 'month') $ret_data_reshape[$value['sku']]["{$value['year']}-{$value['month']}"] = $value;
            else $ret_data_reshape[$value['sku']]["{$value['year']}-{$value['month']}-{$value['days']}"] = $value;
        }

        $ret_data['list']  = $ret_data_reshape;
        return $ret_data;
    }


    /**
     * 查找 SKU 统计 数据(使用表 erp_sku_date)
     * @author lamkakyun
     * @date 2019-02-20 15:05:27
     * @return void
     */
    public function getSkuTotal($start_time, $end_time, $check_date  = 'day')
    {
        $group_by = $check_date == 'day' ? 'days,month,year' : 'month, year';
        $fields = "{$group_by}, SUM(totals) as sum_qty";

        $where = ['datetime' => [['EGT', $start_time], ['ELT', $end_time]]];
        $tmp = $this->skuDateModel->field($fields)->where($where)->group($group_by)->select()->toArray();

        // echo '<pre>';var_dump($this->skuDateModel->getLastSql());echo '</pre>';
        // exit;
        $ret_data = [];
        foreach($tmp as $value)
        {
            $tmp_key = $value['year'] . '-' . $value['month'];
            if ($check_date == 'day') $tmp_key .= '-' . $value['days'];

            $ret_data[$tmp_key] = $value;
        }

        return $ret_data;
    }


    /**
     * 查找 SKU 统计 数据 合并 account, cat, seller, developer, country, store
     * @author lamkakyun
     * @date 2019-02-27 17:38:54
     * @return void
     */
    public function getSkuTotalAll($start_time, $end_time, $params = [])
    {
        $group_by = $params['checkDate'] == 'day' ? 'days,month,year' : 'month, year';
        $where = ['datetime' => [['EGT', $start_time], ['ELT', $end_time]]];
        $fields = "{$group_by}, SUM(qty) as sum_qty";

        switch ($params['type'])
        {
            case 'account':
                if (isset($params['platform']) && !empty($params['platform'])) $where['platform'] = ['IN', $params['platform']];
                if (isset($params['account']) && !empty($params['account'])) $where['platform_account'] = ['IN', $params['account']];

                $model = $this->skuPlatformModel;
                break;
            case 'cat':
                if (isset($params['cat_id']) && !empty($params['cat_id'])) $where['category_id'] = $params['cat_id'];
                if (isset($params['sub_cat_id']) && !empty($params['sub_cat_id'])) $where['category_child_id'] = ['IN', $params['sub_cat_id']];

                $model = $this->skuCategoryModel;
                break;
            case 'seller':
                if (isset($params['seller']) && !empty($params['seller'])) $where['seller'] = ['IN', $params['seller']];
                if (isset($params['organ']) && !empty($params['organ'])) 
                {
                    if (!(count($params['organ']) == 1 && $params['organ'][0] == '')) {
                        $all_sub_org_ids = ToolsLib::getInstance()->getSubOrgIds($params['organ'][0]);
                        if ($all_sub_org_ids) $where['sales_branch_id'] = ['IN', $all_sub_org_ids];
                    }
                }

                $model = $this->skuSellerModel;
                break;
            case 'developer':
                if (isset($params['developer']) && !empty($params['developer'])) $where['developer'] = ['IN', $params['developer']];

                $model = $this->skuDeveloperModel;
                break;
            case 'country': 
                if (isset($params['country']) && !empty($params['country'])) $where['couny'] = ['IN', $params['country']];

                $model = $this->skuCountryModel;
                break;
            case 'store':
                if (isset($params['store_id']) && !empty($params['store_id'])) $where['store_id'] = ['IN', $params['store_id']];

                $model = $this->skuStoreModel;
                break;
        }

        $sku_keyword_arr = preg_split('/[\s,，]+/', trim($params['sku_keyword']));
        foreach ($sku_keyword_arr as $key => $value)
        {
            if (empty($value)) unset($sku_keyword_arr[$key]);
        }
        if ($sku_keyword_arr) $where['sku'] = ['IN', $sku_keyword_arr];


        $tmp = $model->field($fields)->where($where)->group($group_by)->select()->toArray();
        
        $ret_data = [];
        foreach($tmp as $value)
        {
            $tmp_key = $value['year'] . '-' . $value['month'];
            if ($params['checkDate'] == 'day') $tmp_key .= '-' . $value['days'];

            $ret_data[$tmp_key] = $value;
        }

        return $ret_data;
    }



    /**
     * 获取 某一天，某个sku 的 平台 和账户 对应的销售情况
     * @author lamkakyun
     * @date 2019-02-22 16:52:30
     * @return void
     */
    public function getSkuPlatformAndAccountStat($date, $sku)
    {
        $year = date('Y', strtotime($date));
        $month = date('m', strtotime($date));
       
        $where = ['year' => $year, 'month' => $month, 'sku' => $sku];

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date))
        {
            $day = date('d', strtotime($date));
            $where['days'] = $day;
        }

        $group_by = 'platform';
        $fields = "SUM(qty) AS sum_qty,{$group_by}";

        $platform_data = [];
        $tmp = $this->skuPlatformModel->field($fields)->where($where)->group($group_by)->select()->toArray();
        foreach ($tmp as $k => $v)
        {
            $platform_data[$v[$group_by]] = $v;
        }        

        $group_by = 'platform_account';
        $fields = "SUM(qty) AS sum_qty,{$group_by}, platform";

        $account_data = [];
        $tmp = $this->skuPlatformModel->field($fields)->where($where)->group($group_by)->select()->toArray();

        if (isset($_REQUEST['debug']) && $_REQUEST['debug'] == 'sql')
        {
            echo '<pre>';var_dump($this->skuPlatformModel->getLastSql());echo '</pre>';
            exit;
        }

        foreach ($tmp as $k => $v)
        {
            $account_data[$v[$group_by]] = $v;
        }

        return ['platform_data' => $platform_data, 'account_data' => $account_data];
    }
    


    /**
     * 获取 组合sku - 独立 sku 的映射
     * 数据库有 35000- 个 组合sku，这给方法将占用内存， 30 * 35000 / 1024 / 1024 = 1 mb
     * @author lamkakyun
     * @date 2019-03-19 14:45:12
     * @return void
     */
    public function getCombineSkuMap($combine_sku_list = [])
    {

        if (!$combine_sku_list)
        {
            $key = config('redis.combine_sku_map');

            $ret_data = ToolsLib::getInstance()->getRedis()->get($key);
            if (!$ret_data)
            {
                $data = $this->erpGoodsCombineModel->field('goods_sn, goods_combine')->select()->toArray();
    
                $ret_data = [];
                foreach ($data as $value)
                {
                    $ret_data[$value['goods_sn']] = explode(',', $value['goods_combine']);
                }
    
                ToolsLib::getInstance()->getRedis()->set($key, $ret_data, 5 * 60);
            }
        }
        else
        {
            $where = ['goods_sn' => ['IN', $combine_sku_list]];
            $data = $this->erpGoodsCombineModel->field('goods_sn, goods_combine')->where($where)->select()->toArray();

            $ret_data = [];
            foreach ($data as $value)
            {
                $ret_data[$value['goods_sn']] = explode(',', $value['goods_combine']);
            }
        }

        return $ret_data;
    }


    /**
     * 获取父SKU 映射
     * @author lamkakyun
     * @date 2019-03-19 16:42:55
     * @return void
     */
    public function getSkuInfo(&$sku_list)
    {
        $ret_data = [];

        $where = ['goods_sn' => ['IN', $sku_list]];
        $data = $this->erpGoods->field('goods_sn, goods_parent, goods_name, goods_iamge')->where($where)->select()->toArray();

        $map = [];
        foreach ($data as $value)
        {
            $map[strtoupper($value['goods_sn'])] = $value;
        }

        return $map;
    }
}