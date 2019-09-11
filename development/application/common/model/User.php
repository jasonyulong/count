<?php
/**
 * 用户模型
 * @copyright Copyright (c) 2018
 * @license   
 * @version   Beta 1.0
 * @author    mina
 * @date      2018-09-13
 */
namespace app\common\model;

use think\Model;

/**
 * 用户
 * Class User
 * @package app\common\model
 */
class User extends Model
{
    // 表名
    protected $name = 'ebay_user';

    /**
     * @desc 获取相关的人员
     * @Author leo
     * @param $typestr
     * @return arr
     */
    public function getAllCguser($typestr='采购')
    {
        return $this->where("truename like '%$typestr%'")->field("id,username,truename")->order('py asc')->select()->toArray();
    }
}