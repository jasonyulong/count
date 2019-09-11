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
use app\count\model\SkuStore;
use think\cache\driver\Redis;
use app\count\model\SkuSeller;
use app\count\model\SkuCountry;
use app\count\model\SkuCategory;
use app\count\model\SkuPlatform;
use app\count\model\SkuDeveloper;

/*
 * sku销量
 * @author jason
 * @date 2018/9/13
 */

class SkuLib
{
    private static $instance = null;
    
    /**
     * single pattern
     * @AUTHOR: Lamkakyun
     * @DATE: 2019-02-18 14:57:16
     */
    public static function getInstance(): SkuLib
    {
        if (!static::$instance) {
            static::$instance = new SkuLib();
        }
        return static::$instance;
    }

    public function __construct()
    {
        $this->skuDateModel = new SkuDate();
        $this->skuCategoryModel = new SkuCategory();
        $this->skuCountryModel = new SkuCountry();
        $this->skuSellerModel = new SkuSeller();
        $this->skuDeveloperModel = new SkuDeveloper();
        $this->skuStoreModel = new SkuStore();
        $this->skuPlatformModel = new SkuPlatform();
    }

    /**
     * @name 某个sku 某个平台下的所有账号 合计的销量
     * @author jason
     * @date 2018/9/15
     */

    public function totalskudetail($params)
    {
        $skuModel   = new Sku();
        $sku        = $params['sku'];
        $platform   = $params['platform'];
        $start_time = $params['paytime_start'];
        $end_time   = $params['paytime_end'];

        //查询销量

        $start_date = strtotime($start_time . '00:00:00');
        $end_date   = strtotime($end_time . '23:59:59');

        $where['datetime'] = ['between', [$start_date, $end_date]];

        if (!empty($platform)) {
            $where['platform'] = $platform;
        }
        if (!empty($params['store'])) {
            $where['store_id'] = $params['store'];
        }

        //销售人员
        if (isset($params['seller']) && !empty($params['seller'])) {
            $where['seller'] = ['IN', $params['seller']];
        }

        if (!empty($params['country'])) {
            $where['couny'] = $params['country'];
        }


        //分类
        if (!empty($params['single'])) {
            $where['category_child_id'] = $params['single'];
        } else {
            if (!empty($params['category'])) {
                $where['category_id'] = $params['category'];
            }
        }
        $where['sku'] = $sku;
        $sale         = [];
        $account      = [];
        $plat_info    = [];
        $info         = $skuModel->where($where)->field('platform,sum(qty) as qty,platform_account,sku')->group('platform,platform_account,sku')->select();

        $info = replace_query($info);
        foreach ($info as $key => $val) {
            $account[]   = $val['platform_account'];
            $sale[]      = $val['qty'];
            $plat_info[] = $val;
        }
        $return_data = [
            'account'   => $account,
            'qty'       => $sale,
            'plat_info' => $plat_info
        ];
        //        echo '<pre>';print_r($return_data);exit;
        return $return_data;
    }

    /**
     * @name sku销量合计
     * @author jason
     * @date 2018/9/15
     */
    public function showskudetail($params)
    {
        $skuModel   = new Sku();
        $start_time = $params['paytime_start'];
        $end_time   = $params['paytime_end'];
        $sku        = $params['sku'];
        //todo 所有平台
        $all_platform = $this->getPlatformAccount(1);

        $dates  = $this->getDates(1, $start_time, $end_time);
        $dates2 = $this->getDates(2, $start_time, $end_time);

        //查询销量
        $start_date = strtotime($start_time . '00:00:00');
        $end_date   = strtotime($end_time . '23:59:59');

        $store                      = !empty($params['store']) ? $params['store'] : '';//仓库
        $category                   = !empty($params['category']) ? $params['category'] : '';//一级分类
        $single                     = !empty($params['single']) ? $params['single'] : '';//二级分类
        $params['seller'] = !empty($params['seller']) ? $params['seller'] : '';//销售人员
        $country                    = !empty($params['country']) ? $params['country'] : '';//国家
        //仓库
        if (!empty($store)) {
            $where['store_id'] = $store;
            $wh['store_id']    = $store;
            $w['store_id']     = $store;
            $whe['store_id']   = $store;
        }
        //一级分类
        if (!empty($category)) {
            $where['category_id'] = $category;
            $wh['category_id']    = $category;
            $w['category_id']     = $category;
            $whe['category_id']   = $category;
        }
        //二级分类
        if (!empty($single)) {
            $where['category_child_id'] = $single;
            $wh['category_child_id']    = $single;
            $w['category_child_id']     = $single;
            $whe['category_child_id']   = $single;
        }

        //销售人员
        if (isset($params['seller']) && !empty($params['seller'])) {
            $where['seller'] = ['IN', $params['seller']];
            $wh['seller']    = ['IN', $params['seller']];
            $w['seller']     = ['IN', $params['seller']];;
            $whe['seller'] = ['IN', $params['seller']];;
        }


        //国家
        if (!empty($country)) {
            $where['couny'] = $country;
            $wh['couny']    = $country;
            $w['couny']     = $country;
            $whe['couny']   = $country;
        }
        //时间
        $where['datetime'] = ['between', [$start_date, $end_date]];
        $where['sku']      = $sku;

        $wh['datetime'] = ['between', [$start_date, $end_date]];
        $wh['sku']      = $sku;

        $w['datetime'] = ['between', [$start_date, $end_date]];
        $w['sku']      = $sku;

        $whe['datetime'] = ['between', [$start_date, $end_date]];
        $whe['sku']      = $sku;

        $sale_platform                 = [];
        $platform                      = [];
        $platform_sale                 = [];
        $params['platforms'] = [];
        if (!empty($params['platform'])) {
            foreach ($params['platform'] as $plat) {
                $params['platforms'][$plat] = $plat;
            }
        }
        unset($params['platform']);

        foreach ($all_platform as $key => $val) {
            if (empty($params['platforms'][0])) {
                foreach ($dates as $kk => $vv) {
                    $date                     = strtotime($vv);
                    $year2                    = date('Y', $date);//起始年份
                    $month2                   = date('m', $date);//起始月份
                    $day2                     = date('d', $date);//起始天
                    $where['platform']        = $val;
                    $where['year']            = $year2;
                    $where['month']           = $month2;
                    $where['days']            = $day2;
                    $skuInfo                  = $skuModel->where($where)->field('sum(qty) as qty,platform')->select();
                    $sale_platform[$val][$vv] = !empty($skuInfo[0]['qty']) ? $skuInfo[0]['qty'] : 0;
                    $platform_sale[$val][$kk] = !empty($skuInfo[0]['qty']) ? $skuInfo[0]['qty'] : 0;
                }
            } else {
                if (isset($params['platforms'][$val])) {
                    $plats = $params['platforms'][$val];
                }
                if ($plats == $val) {
                    foreach ($dates as $kk => $vv) {
                        $date                     = strtotime($vv);
                        $year2                    = date('Y', $date);//起始年份
                        $month2                   = date('m', $date);//起始月份
                        $day2                     = date('d', $date);//起始天
                        $where['platform']        = $val;
                        $where['year']            = $year2;
                        $where['month']           = $month2;
                        $where['days']            = $day2;
                        $skuInfo                  = $skuModel->where($where)->field('sum(qty) as qty,platform')->select();
                        $sale_platform[$val][$vv] = !empty($skuInfo[0]['qty']) ? $skuInfo[0]['qty'] : 0;
                        $platform_sale[$val][$kk] = !empty($skuInfo[0]['qty']) ? $skuInfo[0]['qty'] : 0;
                    }
                } else {
                    $qty = 0;
                    foreach ($dates as $kk => $vv) {
                        $sale_platform[$val][$vv] = $qty;
                        $platform_sale[$val][$kk] = $qty;
                    }
                }

            }
            $platform[] = $val;
        }

        //全平台
        $date_total_sale = [];
        $total_sale      = 0;
        foreach ($dates as $ke => $va) {
            if (empty($params['platforms'][0])) {
                $date                 = strtotime($va);
                $year2                = date('Y', $date);//起始年份
                $month2               = date('m', $date);//起始月份
                $day2                 = date('d', $date);//起始天
                $wh['year']           = $year2;
                $wh['month']          = $month2;
                $wh['days']           = $day2;
                $skuInfos             = $skuModel->where($wh)->field('sum(qty) as qty')->select();
                $date_total_sale[$va] = !empty($skuInfos[0]['qty']) ? $skuInfos[0]['qty'] : 0;
                $total_sale           += $date_total_sale[$va];
            } else {
                $date                 = strtotime($va);
                $year2                = date('Y', $date);//起始年份
                $month2               = date('m', $date);//起始月份
                $day2                 = date('d', $date);//起始天
                $wh['year']           = $year2;
                $wh['month']          = $month2;
                $wh['days']           = $day2;
                $wh['platform']       = ['IN', $params['platforms']];
                $skuInfos             = $skuModel->where($wh)->field('sum(qty) as qty')->select();
                $date_total_sale[$va] = !empty($skuInfos[0]['qty']) ? $skuInfos[0]['qty'] : 0;
                $total_sale           += $date_total_sale[$va];
            }
        }

        //todo 合计
        $total = [];
        foreach ($all_platform as $key => $val) {
            if (empty($params['platforms'][0])) {
                $w['platform'] = $val;
                $skuInfo       = $skuModel->where($w)->field('sum(qty) as qty')->select();
                $total[$val]   = !empty($skuInfo[0]['qty']) ? $skuInfo[0]['qty'] : 0;
            } else {
                if (isset($params['platforms'][$val])) {
                    $platss = $params['platforms'][$val];
                }
                if ($platss == $val) {
                    $w['platform'] = $val;
                    $skuInfo       = $skuModel->where($w)->field('sum(qty) as qty')->select();
                    $total[$val]   = !empty($skuInfo[0]['qty']) ? $skuInfo[0]['qty'] : 0;
                } else {
                    $total[$val] = 0;
                }
            }
        }

        $return_data = [
            'date_total_sale' => $date_total_sale,
            'sale_platform'   => $sale_platform,
            'total'           => $total,
            'date'            => $dates2,
            'total_sale'      => $total_sale,
            'platform'        => $platform,
            'platform_sale'   => $platform_sale,
        ];
        return $return_data;
    }

    /**
     * @name 获取所有的sku的销量
     * @param $params
     * @author jason
     * @date 2018/9/17
     * @return array
     */
    public function getSkuData($params)
    {
        $start = microtime(true);
        //实例化sku销量Model
        $skuModel = new Sku();

        $start_time = $params['paytime_start'];
        $end_time   = $params['paytime_end'];

        //排序
        $sort = $params['sort'];

        //查询销量
        $start_date = strtotime($start_time . '00:00:00');
        $end_date   = strtotime($end_time . '23:59:59');

        //判断搜索的keyword
        if (!empty($params['platform'][0])) {
            $where['platform'] = ['IN', $params['platform']];
            $wh['platform']    = ['IN', $params['platform']];
        }
        if (!empty($params['store'])) {
            $where['store_id'] = $params['store'];
            $wh['store_id']    = $params['store'];
        }

        //销售人员
        if (isset($params['seller']) && !empty($params['seller'])) {
            $where['seller'] = ['IN', $params['seller']];
            $wh['seller']    = ['IN', $params['seller']];
        }

        if (!empty($params['country'])) {
            $where['couny'] = $params['country'];
            $wh['couny']    = $params['country'];
        }


        //分类
        if (!empty($params['single'])) {
            $where['category_child_id'] = $params['single'];
            $wh['category_child_id']    = $params['single'];
        } else {
            if (!empty($params['category'])) {
                $where['category_id'] = $params['category'];
                $wh['category_id']    = $params['category'];
            }
        }

        //关键字搜索
        if (!empty($params['keyword'])) {
            if (strpos($params['keyword'], '，')) {
                $params['keyword'] = str_replace('，', ',', $params['keyword']);
            }
            $params['keyword'] = preg_replace('/\s{1,}/', ' ', trim($params['keyword']));
            $params['keyword'] = str_replace(' ', ',', $params['keyword']);
            $params['keyword'] = explode(',', $params['keyword']);
            $params['keyword'] = array_filter($params['keyword'], function ($param) {
                return $param !== '';
            });
            $where['sku']      = ['IN', $params['keyword']];
            $wh['sku']         = ['IN', $params['keyword']];
        }

        $dates  = $this->getDates($type = 2, $start_time, $end_time);
        $dates2 = $this->getDates($type = 1, $start_time, $end_time);

        $page_size = $params['ps'] ? $params['ps'] : 50;

        $current_page      = ($params['p'] && intval($params['p']) > 0) ? $params['p'] : 1;
        $select_start      = $page_size * ($current_page - 1);
        $where['datetime'] = ['between', [$start_date, $end_date]];

        //排序
        $order = '';
        if ($sort == 1) $order = "datetime asc";
        if ($sort == 2) $order = "datetime desc";
        if ($sort == 3) $order = "qtySum asc";
        if ($sort == 4) $order = "qtySum desc";
        $find  = $skuModel->where($where)->field('COUNT(DISTINCT sku) as totals')->find();
        $count = $find->totals;

        //时间段内按sku分组   查询出这个时间段内的所有的sku的销量
        $query = $skuModel->where($where)->group('sku')->field('sku,sum(qty) as qtySum')->limit($select_start, $page_size);
        if (!empty($order)) {
            $query = $query->order($order);
        }
        $skuInfo = $query->select();
        if (request()->get('debug') == 'sql') {
            echo $skuModel->getLastSql();
            exit;
        }

        $qtySum = $skuArr = [];
        foreach ($skuInfo as $k => $v) {
            $sku = trim($v->sku);

            $qtySum[$sku] = $v['qtySum'];
            $skuArr[]     = $sku;
        }

        $wh['datetime'] = ['between', [$start_date, $end_date]];
        $wh['sku']      = ['IN', $skuArr];
        $info           = $skuModel->where($wh)->field('qty,year,month,days,id,sku,thumb,name,datetime')->select();
        if (request()->get('debug') == 'sql2') {
            echo $skuModel->getLastSql();
            exit;
        }
        $data = [];
        foreach ($info as $key => $val) {
            $date = $val->year . '-' . $val->month . '-' . $val->days;
            $sku  = $val->sku;

            $data[$sku]['data']['name']     = $val->name;
            $data[$sku]['data']['thumb']    = $val->thumb;
            $data[$sku]['data']['large']    = str_replace('/small', '', $val->thumb);
            $data[$sku]['data']['datetime'] = $val->datetime;
            $data[$sku]['data']['qtySum']   = !empty($qtySum[$sku]) ? $qtySum[$sku] : 0;
            $data[$sku]['date'][$date][]    = $val->qty;
            $data[$sku]['sum']              = !empty($qtySum[$sku]) ? $qtySum[$sku] : 0;
            $data[$sku]['datetime']         = $val->datetime;
        }

        $skuData = [];
        foreach ($skuInfo as $val) {
            $sku = $val['sku'];
            if (!isset($data[$sku])) {
                continue;
            }
            $skuData[$sku] = $data[$sku] ?? [];
        }

        $return_data = [
            'data'  => $skuData,
            'date'  => $dates,
            'date2' => $dates2,
            'count' => $count
        ];

        return $return_data;
    }


    /**
     * 二维数组排序
     * @param array $array 排序的数组
     * @param string $key 排序主键
     * @param string $type 排序类型 asc|desc
     * @param bool $reset 是否返回原始主键
     * @return array
     */
    function array_order($array, $key, $type = 'asc', $reset = false)
    {
        if (empty($array) || !is_array($array)) {
            return $array;
        }
        foreach ($array as $k => $v) {
            $keysvalue[$k] = $v[$key];
        }
        if ($type == 'asc') {
            asort($keysvalue);
        } else {
            arsort($keysvalue);
        }
        $i = 0;
        foreach ($keysvalue as $k => $v) {
            if ($reset) {
                $new_array[$k] = $array[$k];
            } else {
                $new_array[$i] = $array[$k];
            }
            $i++;
        }
        return $new_array;
    }


    /**
     * @name 时间日期数组
     * @param $start_time
     * @param $end_time
     * @return array
     */
    private function getDates($type = 1, $start_time, $end_time)
    {
        $start = strtotime($start_time);
        $end   = strtotime($end_time);

        $result = [];
        $days   = intval(abs($end - $start) / 86400);
        if ($days > 60) {
            $days = 60;
        }
        for ($i = 0; $i <= $days; $i++) {
            if ($type == 1) {
                $result[] = date('Y-m-d', strtotime("+{$i} day", $start));
            } else {
                $result[] = date('m-d', strtotime("+{$i} day", $start));
            }
        }
        return $result;
    }

    /**
     * @name 时间对应的销量
     * @author jason
     * @date 2018/9/17
     * @param $dates
     * @param $qtyData
     * @return aray
     */
    private function getQtyData($dates, $qtyData)
    {
        $result = [];
        foreach ($dates as $date) {
            $result[$date] = isset($qtyData[$date]) ? $qtyData[$date] : 0;
        }
        return $result;
    }

    /**
     * @name 获取用户信息
     * @params type=1 全部 type=2 开发人员 type=3 销售人员      默认type=1
     * @author jason
     * @date 2018/9/14
     * @return array
     */
    public function getUserInfo($type = 1)
    {
        $redis = Config::get('redis');
        $Redis = new Redis($redis);

        //用户信息 ToDo:erp缓存数据太多、部分数据缓存(清除其中账号信息)
        if ($Redis->get("count:users:list")) {
            $user_arr = $Redis->get("count:users:list");
        } else {
            $userInfo = $Redis->get($redis['user_list']);
            $user_arr = [];
            foreach ($userInfo as $key => $val) {
                $user_arr[$key]['id']       = $val['id'];
                $user_arr[$key]['username'] = $val['username'];
                $user_arr[$key]['truename'] = $val['truename'];
                $user_arr[$key]['is_del']   = $val['is_del'];
            }
            //缓存24小时用户信息
            $Redis->set("count:users:list", $user_arr, 3600 * 24);
        }

        if ($type == 1) {
            $user = $Redis->get('username_redis_list');
            if (empty($user)) {
                $user = [];
                foreach ($user_arr as $key => $val) {
                    if ($val['is_del'] == 0) {
                        $user[$key] = $val;
                    }
                }
                $Redis->set('username_redis_list', $user);
            }

            return $user;
        }
        //开发员
        if ($type == 2) {
            $kfuser = [];
            foreach ($user_arr as $key => $val) {
                if (preg_match("/开发/", $val['truename']) && $val['is_del'] == 0) {
                    $kfuser[$key] = $val;
                }
            }
            return $kfuser;
        }
        //销售员
        if ($type == 3) {
            $seller = [];
            foreach ($user_arr as $key => $val) {
                if (preg_match("/销售/", $val['truename']) || preg_match("/业务/", $val['truename']) && $val['is_del'] == 0) {
                    $seller[$key] = $val;
                }
            }
            return $seller;
        }
    }

    /**
     * 获取平台或账号
     * @param type=1获取平台   type=2获取账号  type=3 平台对应账号 默认是type=1 获取平台
     * @return array
     * @author jason
     * @date 2018/9/14
     */
    public function getPlatformAccount($type = 1)
    {
        $redis = Config::get('redis');
        $Redis = new Redis($redis);

        //获取平台
        if ($type == 1) {
            $platform = $Redis->get('platform_list');
            if (empty($platform)) {
                $plat_acc = $Redis->get($redis['accounts_list']);
                $platform = [];
                foreach ($plat_acc as $key => $val) {
                    $platform[$key] = $val['platform'];
                }
                $platform = array_unique($platform);
                $Redis->set('platform_list', $platform, 24 * 60 * 60);
            }
            return $platform;
        }

        if ($type == 2) {
            $plat_acc = $Redis->get($redis['accounts_list']);
            return $plat_acc;
        }
        //平台对应账号
        if ($type == 3) {
            $plat_acc = $Redis->get($redis['accounts_list']);
            $acc_plat = [];
            foreach ($plat_acc as $kk => $vv) {
                $acc_plat[$vv['platform']][$kk] = $vv['ebay_account'];
            }
            return $acc_plat;
        }
        //账号对应平台
        if ($type == 4) {
            $account = $Redis->get('ebay_account_list');
            if (empty($account)) {
                $plat_acc = $Redis->get($redis['accounts_list']);
                $account  = [];
                foreach ($plat_acc as $kk => $vv) {
                    $account[$vv['ebay_account']] = $vv['platform'];
                }
                $Redis->set('ebay_account_list', $account, 3600 * 24);
            }
            return $account;
        }
    }


    public function getPlatformAccounts($platforms)
    {
        $redis = Config::get('redis');
        $Redis = new Redis($redis);
        if (empty($platforms[0])) {
            $platform = $Redis->get('ebay_account_lists');
            if (empty($platform)) {
                $plat_acc = $Redis->get($redis['accounts_list']);
                $platform = [];
                foreach ($plat_acc as $key => $val) {
                    $platform[$key] = $val['platform'];
                }
                $platform = array_unique($platform);
                $Redis->set('ebay_account_lists', $platform, 3600 * 24);
            }
        } else {
            $platform = $platforms;
        }
        return $platform;
    }

    /**
     * 获取所有国家
     * @return array
     * @author jason
     * @date 2018/9/14
     */
    public function getCountry()
    {
        $redis   = Config::get('redis');
        $redis   = new Redis($redis);
        $country = $redis->get('redis_country_list');
        if (empty($country)) {
            $countrys = $redis->get(Config::get('redis.countries'));
            if (!empty($countrys)) {
                $country = [];
                foreach ($countrys as $k => $v) {
                    $country[$k] = $k . ' ' . $v;
                }
            }
            $redis->set('redis_country_list', $country, 3600 * 24);
        }
        return $country;
    }

    /**
     * 获得商品分类
     * @param type=1 查父分类
     * @author jason
     * @date 2018/9/14
     */
    public function getCategory()
    {
        $redis    = new Redis(Config::get('redis'));
        $category = $redis->get(Config::get('redis.goods_category'));
        if (empty($category)) {
            return [];
        }
        $cate = $redis->get('redis_goods_category');
        if (empty($cate)) {
            $cate = [];
            foreach ($category as $key => $val) {
                if ($val['pid'] == 0) {
                    $cate[$key] = $val;
                }
            }
            $redis->set('redis_goods_category', $cate, 3600 * 24);
        }

        return $cate;
    }

    /**
     * @author jason
     * @date 2018/9/17
     * @name 二级分类
     * @param $parentId
     * @return array
     */
    public function getChild($parentId)
    {
        $redis    = Config::get('redis');
        $Redis    = new Redis($redis);
        $category = $Redis->get($redis['goods_category']);
        $child    = [];
        foreach ($category as $key => $val) {
            if ($val['pid'] == $parentId) {
                $child[$key] = $val;
            }
        }
        return $child;
    }

    /**
     * 获取仓库
     * @return array
     * @author jason
     * @2018/9/14
     */
    public function getStore()
    {
        $redis = new Redis(Config::get('redis'));
        $store = $redis->get('redis_store');
        if (empty($store)) {
            $store = $redis->get(Config::get('redis.store'));
            $redis->set('redis_store', $store, 3600 * 24);
        }
        return $store;
    }

    /**
     * @naem 某一个日期的sku的销量
     * @access author
     * @return string
     * @author jason
     * @date 2018/9/14
     */
    public function showskutotal($params)
    {
        $skuModel          = new Sku();
        $sku               = !empty($params['sku']) ? $params['sku'] : '';
        $time              = $params['time'];
        $time2             = $params['time'];
        $platform          = $this->getPlatformAccount(1);
        $start_date        = strtotime($time . '00:00:00');
        $end_date          = strtotime($time . '23:59:59');
        $where['datetime'] = ['between', [$start_date, $end_date]];
        $store             = !empty($params['store']) ? $params['store'] : '';//仓库
        $category          = !empty($params['category']) ? $params['category'] : '';//一级分类
        $single            = !empty($params['single']) ? $params['single'] : '';//二级分类
        $params['seller']  = !empty($params['seller']) ? $params['seller'] : '';//销售人员
        $country           = !empty($params['country']) ? $params['country'] : '';//国家
        $where['sku']      = $sku;//sku

        //仓库搜索
        if (!empty($store)) {
            $where['store_id'] = $store;
        }
        //一级分类搜索
        if (!empty($category)) {
            $where['category_id'] = $category;
        }
        //二级分类搜索
        if (!empty($single)) {
            $where['category_child_id'] = $single;
        }
        //销售人员
        if (isset($params['seller']) && !empty($params['seller'])) {
            $where['seller'] = ['IN', $params['seller']];
        }
        //国家搜索
        if (!empty($country)) {
            $where['couny'] = $country;
        }

        $sale = $plat = $plat_info = [];

        $params['platforms'] = [];
        if (!empty($params['platform'])) {
            $paramsPlatform = is_array($params['platform']) ? $params['platform'] : explode(',', $params['platform']);
            foreach ($paramsPlatform as $plat) {
                $params['platforms'][$plat] = $plat;
            }
        }

        $pla = [];
        foreach ($platform as $key => $val) {
            if (empty($params['platforms'])) {
                $where['platform'] = $val;
                $info              = $skuModel->where($where)->field('sum(qty) as qty')->select();

                if (!empty($info[0]['qty'])) {
                    $qty = $info[0]['qty'];
                } else {
                    $qty = 0;
                }
            } else {
                if (isset($params['platforms'][$val])) {
                    $plats = $params['platforms'][$val];
                } else {
                    $plats = '';
                }
                if ($plats == $val) {
                    $where['platform'] = $val;
                    $info              = $skuModel->where($where)->field('sum(qty) as qty')->select();
                    if (!empty($info)) {
                        $qty = $info[0]['qty'];
                    } else {
                        $qty = 0;
                    }
                } else {
                    $qty = 0;
                }
            }
            $sale[]                      = $qty;
            $plat_info[$key]['qty']      = $qty;
            $pla[$key]                   = $val;
            $plat_info[$key]['platform'] = $val;
            $plat_info[$key]['sku']      = $sku;
            $plat_info[$key]['time']     = $time2;
        }


        $return_data = [
            'sale'      => $sale,
            'plat'      => $pla,
            'plat_info' => $plat_info
        ];

        return $return_data;
    }

    /**
     * @name 单个sku在某个时间某个平台下所有账号对应的销量
     * @author 2018/9/15
     * @date 2018/9/15
     * @return array
     */
    public function singlesku($params)
    {
        $skuModel          = new Sku();
        $sku               = $params['sku'];
        $time              = $params['time'];
        $platform          = $params['platform'];
        $start_date        = strtotime($time . '00:00:00');
        $end_date          = strtotime($time . '23:59:59');
        $where['datetime'] = ['between', [$start_date, $end_date]];

        //平台
        if (!empty($platform)) {
            $where['platform'] = $platform;
        }
        //仓库
        if (!empty($params['store'])) {
            $where['store_id'] = $params['store'];
        }

        //销售人员
        if (isset($params['seller']) && !empty($params['seller'])) {
            $where['seller'] = ['IN', $params['seller']];
        }

        //国家
        if (!empty($params['country'])) {
            $where['couny'] = $params['country'];
        }


        //分类
        if (!empty($params['single'])) {
            $where['category_child_id'] = $params['single'];
        } else {
            if (!empty($params['category'])) {
                $where['category_id'] = $params['category'];
            }
        }

        $where['sku'] = $sku;
        $sale         = [];
        $account      = [];
        $plat_info    = [];
        $info         = $skuModel->where($where)->field('sum(qty) as qty,platform,platform_account,sku')->group('platform,platform_account,sku')->select();

        $info         = replace_query($info);

        foreach ($info as $key => $val) {
            $account[] = $val['platform_account'];
            $sale[]    = $val['qty'];
            $key       = $val['platform_account'];
            isset($plat_info[$key]) ? $plat_info[$key]['qty'] += $val['qty'] : $plat_info[$key] = $val;
        }

        $return_data = [
            'account'   => $account,
            'qty'       => $sale,
            'plat_info' => $plat_info
        ];

        return $return_data;
    }

    public function selectcategory($params)
    {
        $parentId    = $params['parentId'];
        $return_data = $this->getChild($parentId);
        return $return_data;
    }
}
