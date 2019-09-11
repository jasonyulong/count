<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    mina
 */

namespace app\common\model;

use think\Config;
use think\Model;

/**
 * 商品表
 * Class Goods
 * @package app\common\model
 */
class GoodsAudit extends Model
{
    // 表名
    protected $name = 'ebay_goods_audit';

    /**
     * @desc
     * @var
     */
    public $field = '*';
}