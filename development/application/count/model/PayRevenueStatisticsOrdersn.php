<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    leo
 */

namespace app\count\model;


use think\Model;

/**
 * 采购单层面的付款表
 * Class PayRevenueStatisticsOrdersn
 * @package app\count\model
 */
class PayRevenueStatisticsOrdersn extends Model
{
    // 表名
    protected $name = 'pay_revenue_statistics_ordersn';
    public $connection = 'count';
}