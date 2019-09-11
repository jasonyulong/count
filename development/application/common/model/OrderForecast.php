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
 * 确认利润明细表
 * Class OrderForecast
 * @package app\common\model
 */
class OrderForecast extends Model
{
    // 表名
    protected $name = 'ebay_order_forecast';

    /**
     * @desc  查询字段
     * @var   string
     */
    public $field = '*';

    /**
     * 根据条件查询多条记录
     * @param $where
     * @param string $orderBy
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAll($where, $orderBy = 'id desc')
    {
        return $this->where($where)->field($this->field)->order($orderBy)->select();
    }
}
