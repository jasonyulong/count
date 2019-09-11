<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    kevin
 */

namespace app\count\model;

use think\Model;

/**
 * 预估利润模型
 * Class OrderPreProfit
 * @package app\count\model
 */
class OrderPreProfit extends Model
{
    protected $name = 'order_preprofit';
    public $connection = 'count';
}
