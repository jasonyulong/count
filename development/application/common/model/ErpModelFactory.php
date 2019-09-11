<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    mina
 */

namespace app\common\model;

use think\Db;
use think\Model;

/**
 * 创建ERP 中的 ERP MODEL 实例
 * 使用说明：没必要ERP每个table都创建一个对应的model 文件，太多，一些小的表，用途比较小的表就在这个文件创建即可
 * 注意，不可以使用单例模式，因为每个函数返回的都是 一个 Query 实例，是不可以重复使用的，所以需要重复使用时，因独立创建文件
 * Class ErpModelFactory
 * @package app\common\model
 */
class ErpModelFactory
{
    private static $db_config = 'database';

    /**
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-08 11:41:12
     */
    public static function createSysOrganizationModel()
    {
        $table = 'sys_organization';
        return Db::connect(static::$db_config)->table($table);
    }


    /**
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-08 11:48:46
     */
    public static function createSysOrganizationUserModel()
    {
        $table = 'sys_organization_user';
        return Db::connect(static::$db_config)->table($table);
    }


    /**
     * @AUTHOR: Lamkakyun
     * @DATE: 2019-01-15 16:50:53
     */
    public static function createSysOrganizationPropertyModel()
    {
        $table = 'sys_organization_property';
        return Db::connect(static::$db_config)->table($table);
    }

    /**
     * @AUTHOR: Lamkakyun
     * @DATE: 2019-01-15 16:50:53
     */
    public static function createSysOrganizationEbayModel()
    {
        $table = 'sys_organization_ebay';
        return Db::connect(static::$db_config)->table($table);
    }

    /**
     * @name 商品表
     * @author jason
     * @data 2019-02-22
     */
    public static function createEbayGoodsModel()
    {
        $table = 'ebay_goods';
        return Db::connect(static::$db_config)->table($table);
    }


    public static function createEbayGoodsAuditModel()
    {
        $table = 'ebay_goods_audit';
        return Db::connect(static::$db_config)->table($table);
    }
}