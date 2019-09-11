<?php
namespace app\common\model;

use think\Model;

/**
 * 商品组合表
 * @package app\count\model
 */
class ErpGoodsCombine extends Model
{
    // 表名
    protected $name = 'goods_combine';
    // 连接句柄
    public $connection = 'count';

}
