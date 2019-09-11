<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    kevin
 */

namespace app\count\model;

use think\Config;
use think\Model;

/**
 * 订单详情费用表
 * Class OrderDetail
 * @package app\count\model
 */
class OrderDetailFee extends Model
{
    // 表名
    protected $name = 'order_detail_fee';
    // 连接句柄
    public $connection = 'count';

    /**
     * @desc   根据条件查询订单的SKU
     * @author mina
     * @param  array $where 查询条件
     * @return array sku数组
     */
    public function getSkuByWhere($where)
    {
        $this->field = 'sku';
        return $this->where($where)->column($this->field);
    }

    /**
     * @desc   根据条件查询是否存在
     * @author mina
     * @param  array $where 查询条件
     * @return boolen true 存在  false不存在
     */
    public function isExists($where)
    {
        $id = $this->where($where)->value('id');
        return $id ? true : false;
    }
}
