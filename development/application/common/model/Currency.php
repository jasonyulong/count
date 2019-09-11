<?php
/**
 * 模型
 * @copyright Copyright (c) 2018
 * @license   
 * @version   Beta 1.0
 * @author    mina
 * @date      2018-09-13
 */
namespace app\common\model;

use think\Model;

/**
 * 币种汇率
 * Class Currency
 * @package app\common\model
 */
class Currency extends Model
{
    /**
     * @desc  表名
     * @var   string
     */
    protected $name = 'ebay_currency';
}