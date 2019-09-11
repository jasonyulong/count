<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    yang
 */

namespace app\count\model;


use think\Model;

/**
 * 物流支出表
 * Class TransportBill
 * @package app\count\model
 */
class TransportOutlay extends Model
{
    // 表名
    protected $name = 'transport_outlay';

    // 连接句柄
    public $connection = 'count';
}