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
 * 订单表模型
 * Class Order
 * @package app\count\model
 */
class Order extends Model
{
    // 表名
    protected $name = 'order';
    // 连接句柄
    public $connection = 'count';

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
