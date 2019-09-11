<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    yang
 */

namespace app\count\model;


use think\Model;

/**
 * 物流统计表
 * Class TransportBill
 * @package app\count\model
 */
class TransportBill extends Model
{
    // 表名
    protected $name = 'transport_bill';

    // 连接句柄
    public $connection = 'count';
}