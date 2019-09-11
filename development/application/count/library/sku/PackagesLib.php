<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    jason
 */

namespace app\count\library\sku;

use app\count\model\SkuPackage;
use app\count\model\Sku;
use think\Db;
use think\cache\driver\Redis;
use think\Config;

/**
 * 产品包管理
 * Class PackagesLib
 * @package app\count\library\sku
 */
class PackagesLib extends SkuPackage
{
    /**
     * 产品包列表
     * @param $params 参数
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getPackageIndex($params)
    {
        $SkuPackageModel = new SkuPackage();
        $skuModel        = new Sku();

        $where = [];
        //按添加人搜索
        if (!empty($params['adduser'])) {
            $where['adduser'] = $params['adduser'];
        }
        //按关键字搜索
        if (!empty($params['keyword'])) {
            $where['title|group_sn'] = ['like', '%' . $params['keyword'] . '%'];
            $params['group_sn'] = '';
        }
        //按产品包搜索
        if (!empty($params['group_sn'])) {
            $where['group_sn'] = $params['group_sn'];
        }else{
            //$where['group_sn'] = 'all';
        }

        //如果where为空就给个默认为空数组

        //排序
        $order = 'uptime asc';
        //将时间组装成  年月日
        $dates  = $this->getDates(1, $params['time_start'], $params['time_end']);
        $dates2 = $this->getDates(2, $params['time_start'], $params['time_end']);

        //查询产品包数据
        $data = $SkuPackageModel->where($where)->order($order)->select()->toArray();


        if (empty($data)) {
            return [
                'productGroupLists' => [],
                'qtyTotals'         => [],
                'total'             => 0,
                'dates'             => $dates2,
                'id'                => 0
            ];
        }

        $id = !empty($data)?$data[0]['id']:0;
        foreach ($data as $key => $val) {
            $sku                  = json_decode($val['sku'], true);
            $data[$key]['sku']    = $sku;
            $data[$key]['uptime'] = date('Y-m-d', strtotime($val['uptime']));
        }

        //组装数据
        $productGroupLists = [];
        //将时间转换成时间戳
        $time_start = strtotime($params['time_start'] . '00:00:00');
        $time_end   = strtotime($params['time_end'] . '23:59:59');
//        echo '<pre>';print_r($dates);exit;
        foreach ($data as $kk => $list) {
            $item = [];
            foreach ($list['sku'] as $ke => $sku_list) {
                $qtyDatas = [];
                if (is_array($sku_list)) {
                    $items = [];

                    $whe['datetime'] = ['between', [$time_start, $time_end]];
                    $whe['parent'] = $ke;
                    $info_sku = $skuModel->where($whe)->group('sku,name,thumb,year,month,days')->field("sku,sum(qty) as qty,name,thumb,year,month,days")->select()->toArray();

                    $qtyData = [];
                    if (!empty($info_sku)) {

                        foreach ($info_sku as $k3 => $item_info_list) {
                            $time           = $item_info_list['year'] . '-' . $item_info_list['month'] . '-' . $item_info_list['days'];
                            $qtyData[$k3][$time] = $item_info_list['qty'];

                        }
                    }
                    $qtyDataArr = [];
                    foreach($dates as $k_date=>$v_date){
                        $qty_reduce = function($v1,$v2){
                            return $v1+$v2;
                        };
                        $qtyDataArr[$v_date] = array_reduce(array_column($qtyData,$v_date),$qty_reduce);
                    }
                    $qtyDataArr = array_filter($qtyDataArr,function ($par){
                        return !empty($par);
                    });

                    $qtyDataArr  = $this->getQtyData($dates, $qtyDataArr);

//                    echo '<pre>';print_r($qtyDatas);exit;
                    $qtyDatas = $this->sumQtyData($qtyDatas, $qtyDataArr);
                    $qtySum   = array_sum($qtyDatas);

                    $items['group_name'] = $list['title'];
                    $items['group_sn']   = $list['group_sn'];
                    $items['title']      = $info_sku[0]['name'] ?? '';
                    $items['sku']        = $ke;
                    $items['goods_pic']  = $info_sku[0]['thumb'] ?? '';
                    $items['group_sku']  = 1;
                    $items['qtySum']     = $qtySum;
                    $items['qtyData']    = $qtyDatas;
                    $items['goods_sn']   = $sku_list;

                    $productGroupLists[] = $items;
                } else {//如果是单属性sku就走这里
                    $wh['sku']      = $sku_list;
                    $wh['datetime'] = ['between', [$time_start, $time_end]];
                    $skuInfo        = $skuModel->where($wh)->group('sku,name,thumb,year,month,days')->field("sku,sum(qty) as qty,name,thumb,year,month,days")->select()->toArray();

                    $qtyData = [];
                    if (!empty($skuInfo)) {
                        foreach ($skuInfo as $k1 => $sku_info_list) {
                            $time           = $sku_info_list['year'] . '-' . $sku_info_list['month'] . '-' . $sku_info_list['days'];
                            $qtyData[$k1][$time] = $sku_info_list['qty'];
                        }
                    }
                    $qtyDataArr = [];
                    foreach($dates as $k_date=>$v_date){
                        $qty_reduce = function($v1,$v2){
                            return $v1+$v2;
                        };
                        $qtyDataArr[$v_date] = array_reduce(array_column($qtyData,$v_date),$qty_reduce);
                    }
                    $qtyDataArr = array_filter($qtyDataArr,function ($par){
                        return !empty($par);
                    });
                    $qtyDataArr = $this->getQtyData($dates, $qtyDataArr);
                    $qtySum  = array_sum($qtyDataArr);

                    $item['group_name'] = $list['title'];
                    $item['group_sn']   = $list['group_sn'];
                    $item['title']      = $skuInfo[0]['name'] ?? '';
                    $item['sku']        = $sku_list;
                    $item['goods_pic']  = $skuInfo[0]['thumb'] ?? '';
                    $item['group_sku']  = 0;
                    $item['qtySum']     = $qtySum;
                    $item['qtyData']    = $qtyDataArr;

                    $productGroupLists[] = $item;
                }

            }
        }

        $productGroupLists = array_filter($productGroupLists);
        $qtyTotals         = [];
        $productGroupList  = [];
        foreach ($productGroupLists as $k5 => $value) {
            $qtyTotals          = $this->sumQtyData($qtyTotals, $value['qtyData']);
            $productGroupList[] = $value;
        }

        if (empty($qtyTotals)) {
            foreach ($dates as $kkk => $vv) {
                $qtyTotals[$vv] = 0;
            }
        }

        foreach ($productGroupList as $k6 => $data_list) {
            $productGroupList[$k6]['keys']   = json_encode(array_keys($data_list['qtyData']));
            $productGroupList[$k6]['values'] = json_encode(array_values($data_list['qtyData']));
        }

        $total            = array_sum($qtyTotals);
        $productGroupList = [
            'productGroupLists' => $productGroupList,
            'qtyTotals'         => $qtyTotals,
            'total'             => $total,
            'dates'             => $dates2,
            'id'                => $id
        ];
        return $productGroupList;
    }


    /**
     * @name 父sku销量
     * @access auth
     * @return string
     * @throws \think\Exception
     */
    public function btobnumberdetail($params)
    {
        $skuModel          = new Sku();
        $sku               = $params['sku'];
        $time              = $params['time'];
        $time2             = $params['time'];
        $platform          = $this->getPlatformAccount(1);
        $start_date        = strtotime($time . '00:00:00');
        $end_date          = strtotime($time . '23:59:59');
        $where['datetime'] = ['between', [$start_date, $end_date]];
        //如果group_sku=1 说明是主sku, 如果group_sku=0 说明是单属性sku
        if ($params['group_sku'] == 1) {
            $where['parent'] = $sku;
        } else {
            $where['sku'] = $sku;
        }

        $sale      = [];
        $plat      = [];
        $plat_info = [];
        foreach ($platform as $key => $val) {
            $where['platform'] = $val;
            $info              = $skuModel->where($where)->field('sum(qty) as qty')->select();

            if (!empty($info[0]['qty'])) {
                $qty = $info[0]['qty'];
            } else {
                $qty = 0;
            }
            $plat[]                      = $val;
            $sale[]                      = $qty;
            $plat_info[$key]['platform'] = $val;
            $plat_info[$key]['qty']      = $qty;
            $plat_info[$key]['sku']      = $sku;
            $plat_info[$key]['time']     = $time2;
        }
        $return_data = [
            'sale'      => $sale,
            'plat'      => $plat,
            'plat_info' => $plat_info
        ];

        return $return_data;
    }

    /**
     * @name 某个日期的某个sku在某个平台的销量
     * @author jason
     * @date 2018/9/19
     * @return mixed
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
        //如果group_sku=1 说明是主sku, 如果group_sku=0 说明是单属性sku
        if ($params['group_sku'] == 1) {
            $where['parent'] = $sku;
        } else {
            $where['sku'] = $sku;
        }

        $where['platform'] = $platform;

        $sale      = [];
        $account   = [];
        $plat_info = [];
        $info      = $skuModel->where($where)->field('platform,sum(qty) as qty,platform_account,sku')->group('sku,platform,platform_account')->select();

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
     * @name 子系列
     * @author jason
     * @date 2018/9/19
     * @access auth
     * @return array
     */
    public function viewdetail($params)
    {
        //实例化sku销量Model
        $skuModel = new Sku();
        //时间
        $start_time = strtotime($params['time_start'] . '00:00:00');
        $end_time   = strtotime($params['time_end'] . '23:59:59');
        //sku
        $goods_sn = $params['goods_sn'];

        //组装时间
        $dates  = $this->getDates(2, $params['time_start'], $params['time_end']);
        $dates2 = $this->getDates(1, $params['time_start'], $params['time_end']);
        if (!empty($goods_sn)) {
            if (!empty($start_time) || !empty($end_time)) {
                $where['datetime'] = ['between', [$start_time, $end_time]];
            }

            $qtyList = [];
            //循环sku查询销量
            foreach ($goods_sn as $k => $sku) {
                $where['sku'] = $sku;
                $info_sku     = $skuModel->where($where)->group('sku,name,year,month,days')->field("sku,sum(qty) as qty,name,year,month,days")->select();
                $info_sku     = replace_query($info_sku);
                //拼装销量
                $qtyData = [];
                $item    = [];
                if (!empty($info_sku)) {
                    foreach ($info_sku as $key => $val) {
                        $time           = $val['year'] . '-' . $val['month'] . '-' . $val['days'];
                        $qtyData[$time] = $val['qty'];
                    }
                }
                $qtyData         = $this->getQtyData($dates2, $qtyData);
                $qtySum          = array_sum($qtyData);
                $item['keys']    = json_encode(array_keys($qtyData));
                $item['values']  = json_encode(array_values($qtyData));
                $item['sku']     = $sku;
                $item['name']    = $info_sku[0]['name'] ?? '';
                $item['qtyData'] = $qtyData;
                $item['qtySum']  = $qtySum;
                $qtyList[$k]     = $item;
            }
        }
        $return_data = [
            'data' => $qtyList,
            'date' => $dates,
        ];
        return $return_data;

    }


    public function singledetail($params)
    {
        //实例化sku的Model
        $skuModel = new Sku();

        $sku = $params['sku'];
        //时间
        $start_time = $params['time_start'];
        $end_time   = $params['time_end'];
        //将时间转化成时间戳
        $start_date = strtotime($start_time . '00:00:00');
        $end_date   = strtotime($end_time . '23:59:59');

        //所有的平台
        $platform = $this->getPlatformAccount(1);

        //拼接where条件
        $where['datetime'] = ['between', [$start_date, $end_date]];
        $where['sku']      = $sku;

        //开始查询数据
        $data_info = [];
        foreach ($platform as $k => $data_platform) {
            $where['platform'] = $data_platform;
            $account_sale      = [];
            $info              = $skuModel->where($where)->field('sum(qty) as qty,platform_account')->group('platform_account')->select();
            if (!empty($info)) {
                foreach ($info as $kk => $sku_list) {
                    //                $data_info[$data_platform][$kk] = $sku_list;
                    $account_sale[$sku_list['platform_account']] = $sku_list['qty'];
                    $data_info[$data_platform]['account_sale']   = $account_sale;
                }
            } else {
                $data_info[$data_platform]['account_sale'] = [];
            }

        }
        $data_info = replace_query($data_info);

        foreach ($data_info as $index => $item) {
            $qtySum                      = array_sum($item['account_sale']);
            $data_info[$index]['qtySum'] = $qtySum;
            $data_info[$index]['key']    = json_encode(array_keys($item['account_sale']));
            $data_info[$index]['value']  = json_encode(array_values($item['account_sale']));
        }

        $plat_qty = [];
        $qty      = [];
        foreach ($data_info as $k1 => $valu) {
            $qty[$k1]           = $valu['qtySum'];
            $plat_qty['qtySum'] = $qty;
        }
        $keys   = array_keys($plat_qty['qtySum']);
        $values = array_values($plat_qty['qtySum']);
        //        echo '<pre>';print_r($data_info);exit;
        $return_data = [
            'data'   => $data_info,
            'keys'   => $keys,
            'values' => $values,
        ];
        return $return_data;
    }


    /**
     * @name 添加人
     * @author jason
     * @date 2018/9/17
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getAdduser($username='')
    {
        $skuPackageModel = new SkuPackage();
        if($username == ''){
            $return_data     = $skuPackageModel->field('adduser')->group('adduser')->select();
            $return_data     = replace_query($return_data);
        }else{
            $return_data     = $skuPackageModel->where(['adduser'=>$username])->field('adduser')->group('adduser')->select();
            $return_data     = replace_query($return_data);
        }
        return $return_data;
    }

    /**
     * @name 产品包
     * @author jason
     * @date 2018/9/17
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getGroupsn($username = '')
    {
        $skuPackageModel = new SkuPackage();
        if($username == ''){
            $return_data     = $skuPackageModel->field('group_sn,title')->order('uptime desc')->select()->toArray();
        }else{
            $return_data     = $skuPackageModel->where(['adduser'=>$username])->field('group_sn,title')->order('uptime desc')->select()->toArray();
        }
        return $return_data;
    }


    /**
     * @name 时间日期数组
     * @param $start_time
     * @param $end_time
     * @return array
     */
    public function getDates($type = 1, $start_time, $end_time)
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
     * @return array
     */
    public function getQtyData($dates, $qtyData)
    {
        $result = [];
        foreach ($dates as $date) {
            $result[$date] = isset($qtyData[$date]) ? $qtyData[$date] : 0;
        }
        return $result;
    }


    /**
     * @name 主sku下面对应的销量
     * @author jason
     * @date 2018/9/18
     * @param $qtyData
     * @param $qtyDatas
     * @return mixed
     */
    public function sumQtyData($qtyData, $qtyDatas)
    {
        foreach ($qtyDatas as $key => $val) {
            $qtyDatas[$key] = $val + (isset($qtyData[$key]) ? $qtyData[$key] : 0);
        }
        return $qtyDatas;
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
        $redis    = Config::get('redis');
        $Redis    = new Redis($redis);
        $plat_acc = $Redis->get($redis['accounts_list']);
        //获取平台
        if ($type == 1) {
            $platform = [];
            foreach ($plat_acc as $key => $val) {
                $platform[$key] = $val['platform'];
            }
            $platform = array_unique($platform);
            return $platform;
        }
        if ($type == 2) {
            return $plat_acc;
        }
        //平台对应账号
        if ($type == 3) {
            $acc_plat = [];
            foreach ($plat_acc as $kk => $vv) {
                $acc_plat[$vv['platform']][$kk] = $vv['ebay_account'];
            }
            return $acc_plat;
        }
    }


    /**
     * @name 获取产品包信息
     * @author yang
     * @date 2018/9/20
     * @param int $id
     * @return mixed
     */
    public function getPackageInfo($id = 0)
    {
        if ($id) {
            $data = $this->where('id', $id)->find();
        } else {
            $data = $this->order('id desc')->value('group_sn');
        }
        return $data;
    }

    /**
     * @name 获取产品包信息
     * @author yang
     * @date 2018/9/20
     * @param array $data
     * @return mixed
     */
    public function updatePackage($data)
    {
        $skuModel = new Sku();
        $id       = $data['id'];              //产品包主键
        $title    = trim($data['title']);     //产品包标题
        $sku      = trim($data['sku']);       //sku字符串
        $group_sn = trim($data['group_sn']);  //产品包编号
        $cateId   = trim($data['cate_id']);

        //对sku字符串进行处理
        $skuArr   = array_unique(explode(',', $sku));
        $errorStr = '';
        $skuArray = [];
        $skuList  = [];
        foreach ($skuArr as $key => $val) {
            $sku       = trim($val);
            $skuList[] = $sku;
            $skuObj    = $skuModel->where(array('sku' => $sku))->field('sku,parent')->find();
            $skuInfo   = replace_query($skuObj);
            if (!$skuInfo) {
                $errorStr .= "sku:<span style='color: red'> {$sku} </span>(不存在!)<br>";
                continue;
            }
            if ($skuInfo['parent']) {
                $skuArray[$skuInfo['parent']] = $sku;
                continue;
            }
            //查看其是否用于子sku
            $sonSkuArr = $skuModel->where(array('parent' => $sku))->column('sku');
            if ($sonSkuArr) {
                $skuArray[$sku] = $sonSkuArr;
                continue;
            }
            $skuArray[] = $sku;
        }
        if ($errorStr) return json(['status' => 2, 'msg' => $errorStr]);

        $save             = [];
        $save['sku']      = json_encode($skuArray);
        $save['sku_list'] = implode(',', $skuList);
        $save['title']    = $title;
        $save['group_sn'] = $group_sn;
        $save['cate_id']  = $cateId;

        //如果id存在进行更新、不存在进行添加
        if ($id) {
            unset($save['group_sn']);
            $this->save($save, ['id' => $id]);
        } else {
            $save['adduser'] = session('truename') ? session('truename') :  'system';
            $this->insert($save);
        }
        return json(['status' => 1, 'msg' => '保存成功']);
    }

    /**
     * @name 删除产品包
     * @author yang
     * @date 2018/9/20
     * @param id $id
     * @return mixed
     */
    public function deletePackage($id)
    {
        if (!$id) return '';
        $result = $this->where('id', $id)->delete();
        return $result;
    }

    /**
     * @name 合计
     * @author jason
     * @param array
     * @return array
     */
    public function totaldetail($params){
        $skuModel   = new Sku();
        $start_time = $params['time_start'];
        $end_time   = $params['time_end'];
        $sku        = $params['sku'];
        $group_sku = $params['group_sku'];
        //todo 所有平台
        $all_platform = $this->getPlatformAccount(1);

        $dates  = $this->getDates(1, $start_time, $end_time);
        $dates2 = $this->getDates(2, $start_time, $end_time);

        //查询销量
        $start_date = strtotime($start_time . '00:00:00');
        $end_date   = strtotime($end_time . '23:59:59');

        //sku
        if($group_sku == 0){
            $where['sku']      = $sku;
            $wh['sku']      = $sku;
            $whe['sku']      = $sku;
            $w['sku']      = $sku;
        }else{
            $where['parent']      = $sku;
            $wh['parent']      = $sku;
            $whe['parent']      = $sku;
            $w['parent']      = $sku;
        }

        //时间
        $where['datetime'] = ['between', [$start_date, $end_date]];

        $wh['datetime'] = ['between', [$start_date, $end_date]];

        $w['datetime'] = ['between', [$start_date, $end_date]];

        $whe['datetime'] = ['between', [$start_date, $end_date]];


        $sale_platform = [];
        $platform      = [];
        $platform_sale = [];

        foreach ($all_platform as $key => $val) {
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
            $platform[] = $val;
        }

        //全平台
        $date_total_sale = [];
        $total_sale      = 0;
        foreach ($dates as $ke => $va) {
            $date                 = strtotime($va);
            $year2                = date('Y', $date);//起始年份
            $month2               = date('m', $date);//起始月份
            $day2                 = date('d', $date);//起始天
            $wh['year']           = $year2;
            $wh['month']          = $month2;
            $wh['days']           = $day2;
            $skuInfos             = $skuModel->where($wh)->field('sum(qty) as qty')->select();
            $date_total_sale[$va] = !empty($skuInfos[0]['qty']) ? $skuInfos[0]['qty'] : 0;
            $total_sale += $date_total_sale[$va];
        }

        //todo 合计
        $total = [];
        foreach ($all_platform as $key => $val) {
            $w['platform'] = $val;
            $skuInfo       = $skuModel->where($w)->field('sum(qty) as qty')->select();
            $total[$val]   = !empty($skuInfo[0]['qty']) ? $skuInfo[0]['qty'] : 0;
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
}