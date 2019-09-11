<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    kevin
 */

namespace app\common\model;

use think\Config;
use think\Model;

/**
 * 订单详情表模型
 * Class OrderDetail
 * @package app\count\model
 */
class ErpGoods extends Model
{
    // 表名
    protected $name = 'goods';
    // 连接句柄
    public $connection = 'count';

}
