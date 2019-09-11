<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    kevin
 */

namespace app\count\model;

use think\Config;
use think\Model;

/**
 * 订单表模型
 * Class Order
 * @package app\count\model
 */
class GoodsDevelop extends Model
{
    // 表名
    protected $name = 'goods_develop';
    // 连接句柄
    public $connection = 'count';

}
