<?php
/**
 * @Copyright (C), ZhuoShi.
 * @Author: 杨能文
 * @Name: DevelopType.php
 * @Date: 2019/2/25
 * @Time: 11:34
 * @Description 开发产品类型表
 */


namespace app\count\model;


use think\Model;

class DevelopType extends Model
{
    //表名
    protected $name = 'develop_type';
    //连接句柄
    public $connection = 'count';
}