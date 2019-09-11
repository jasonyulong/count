<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    yang
 */

namespace app\count\model;
use think\Model;

/**
 * 统计退款表
 * Class FinanceRefund
 * @package app\count\model
 */
class FinanceRefund extends Model
{
    // 表名
    protected $name = 'finance_refund';
    // 连接句柄
    public $connection = 'count';
}