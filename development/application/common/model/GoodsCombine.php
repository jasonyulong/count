<?php

namespace app\common\model;

use think\Model;

/**
 * 商品组合表
 * @package app\common\model
 */
class GoodsCombine extends Model
{
    // 表名
    protected $name = 'ebay_productscombine';

    /**
     * @desc  
     * @var 
     */
    public $field = '*';
}