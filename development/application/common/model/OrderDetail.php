<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    mina
 */

namespace app\common\model;

use think\Config;
use think\Model;

/**
 * 订单详情表
 * Class Countries
 * @package app\common\model
 */
class OrderDetail extends Model
{
    // 表名
    protected $name = 'ebay_orderdetail';

    /**
     * @desc  查询字段
     * @var   string
     */
    public $field = 'goods_location,sku,ebay_itemurl,ebay_itemprice,ebay_amount,shipingfee,ebay_ordersn';

    /**
     * @desc   根据条件查询多条记录
     * @author mina
     * @param  array $where 查询条件
     * @param  string $orderBy 排序 默认订单id降序
     * @return array
     */
    public function getAll($where, $orderBy = 'ebay_id desc')
    {
    	return $this->where($where)->field($this->field)->order($orderBy)->select();
    }
}
