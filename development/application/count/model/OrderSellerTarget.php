<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    kevin
 */

namespace app\count\model;

use think\Config;
use think\Model;

class OrderSellerTarget extends Model
{
    // 表名
    protected $name = 'order_seller_target';
    // 连接句柄
    public $connection = 'count';
}
