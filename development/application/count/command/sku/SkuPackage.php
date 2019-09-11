<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    yang
 */

namespace app\count\command\sku;

use app\common\model\Goods;
use app\common\model\SysProductGroupList;
use think\cache\driver\Redis;
use think\console\Input;
use think\console\Output;
use think\Config;

/**
 * 产品包数据同步
 * Class Orders
 * @package app\count\command\sync
 */
class SkuPackage
{
    /**
     * redis链接句柄
     * @var Redis
     */
    private $redis;

    /**
     * 产品包列表
     * @var object
     */
    private $sysProductGroupList;

    /**
     * sku组合列表
     * @var object
     */
    private $ebayGoods;

    /**
     * sku产品包统计列表
     * @var object
     */
    private $erpSkuPackage;

    /**
     * 构造函数
     * Orders constructor.
     * @param Input $input 输入对象
     * @param Output $output 输出对象
     */
    public function __construct(Input $input, Output $output)
    {
        $this->redis               = new Redis(Config::get('redis'));
        $this->sysProductGroupList = new sysProductGroupList();
        $this->ebayGoods           = new Goods();
        $this->erpSkuPackage       = new \app\count\model\SkuPackage();
    }


    /**
     * 拉取产品包数据库(同步erp系统)
     * @return string
     */
    public function getSkuPackage()
    {
        $packageObj = $this->sysProductGroupList->field('group_name,group_sn,sku,adduser')->select()->toArray();
        $packageArr = replace_query($packageObj);
        if (count($packageArr) < 1) {
            return "获取产品包数据失败\n";
        }

        $skuArr = [];
        foreach ($packageArr as $key => &$val) {
            $skuArr[$val['group_sn']]['title']      = $val['group_name'];
            $skuArr[$val['group_sn']]['group_sn']   = $val['group_sn'];
            $skuArr[$val['group_sn']]['adduser']    = $val['adduser'];
            $skuArr[$val['group_sn']]['sku_list'][] = $val['sku'];

            //查看是否为主sku
            $sku               = $val['sku'];
            $map               = [];
            $map['type']       = 1;
            $map['ebay_user']  = 'mutil_property';
            $map['BtoBnumber'] = array('eq', '');
            $map['goods_sn']   = $sku;
            $goods_sn          = $this->ebayGoods->where($map)->value('goods_sn');
            if ($goods_sn) {
                $goodsSnArr                                   = $this->ebayGoods->where('BtoBnumber', $goods_sn)->column('goods_sn');
                $skuArr[$val['group_sn']]['sku'][$val['sku']] = $goodsSnArr;
            } else {
                $map             = [];
                $map['type']     = array('neq', 1);
                $map['goods_sn'] = $sku;
                $BtoBNumber      = $this->ebayGoods->where($map)->value('BtoBnumber');
                if ($BtoBNumber) {
                    $skuArr[$val['group_sn']]['sku'][$BtoBNumber][] = $sku;
                } else {
                    $skuArr[$val['group_sn']]['sku'][] = $sku;
                }
            }
            unset($packageArr[$key]);
        }

        $erpSkuPackage = $this->erpSkuPackage;
        $str           = '';
        foreach ($skuArr as $key => &$val) {
            $val['sku']      = json_encode($val['sku']);
            $val['sku_list'] = implode(',', $val['sku_list']);
            //判断产品包是否存在
            $id = $erpSkuPackage->where('group_sn', $val['group_sn'])->value('id');

            //存在进行更新
            if ($id) {
                $result = $erpSkuPackage->save($val, ['id' => $id]);
            } else {
                $result = $erpSkuPackage->insert($val);
            }
            if ($result) {
                $str .= "产品包:{$val['group_sn']}--数据同步成功\n";
            } else {
                $str .= "产品包:{$val['group_sn']}--数据未进行同步\n";
            }
        }
        return $str;
    }
}