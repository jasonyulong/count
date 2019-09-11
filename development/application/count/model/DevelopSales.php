<?php
/**
 * @Copyright (C), ZhuoShi.
 * @Author: 杨能文
 * @Name: DevelopSales.php
 * @Date: 2019/2/25
 * @Time: 11:36
 * @Description 开发员销售额表
 */

namespace app\count\model;

use think\Model;

class DevelopSales extends Model
{
    //表名
    protected $name = 'develop_sales';
    //连接句柄
    public $connection = 'count';
}