<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    yang
 */


namespace app\count\model;
use think\Model;

/**
 * 统计收支表
 * Class FinanceAmount
 * @package app\count\model
 */
class FinanceAmount extends Model
{
    // 表名
    protected $name = 'finance_amount';
    // 连接句柄
    public $connection = 'count';


}