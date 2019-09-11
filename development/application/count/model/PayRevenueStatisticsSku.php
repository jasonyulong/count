<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    leo
 */

namespace app\count\model;


use think\Model;

/**
 * sku层面的付款表
 * Class PayRevenueStatisticsSku
 * @package app\count\model
 */
class PayRevenueStatisticsSku extends Model
{
    // 表名
    protected $name = 'pay_revenue_statistics_sku';
    public $connection = 'count';

}