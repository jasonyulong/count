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
 * 订单预利润表
 * Class Lowprofit
 * @package app\common\model
 */
class Lowprofit extends Model
{
    // 表名
    protected $name = 'ebay_lowprofit';

    /**
     * @desc  查询字段
     * @var   string
     */
    public $field = 'ebay_id,account,total,commission,ppfee,optionfee,cost,packagecost,packagecost,shipfee,onlinefee,otherfee,calcweight,profit,profitbili';

    /**
     * @desc   根据条件查询多条记录
     * @param  array $where 查询条件
     * @param  string $orderBy 排序 默认订单id降序
     * @author mina
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAll($where, $orderBy = 'ebay_id desc')
    {
        return $this->where($where)->field($this->field)->order($orderBy)->select();
    }
}
