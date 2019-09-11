<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    kevin
 */

namespace app\count\model;

use think\Model;

/**
 * 利润相关统计模型
 * Class OrderProfit
 * @package app\count\model
 */
class OrderProfit extends Model
{
    protected $name = 'order_profit';
    public $connection = 'count';
}
