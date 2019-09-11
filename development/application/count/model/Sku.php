<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    yang
 */

namespace app\count\model;

use think\Model;

/**
 * 产品统计表
 * Class Sku
 * @package app\count\model
 */
class Sku extends Model
{
    // 表名
    protected $name = 'sku';

    // 连接句柄
    public $connection = 'count';
}