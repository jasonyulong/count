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
 * 运输渠道表
 * Class Carrier
 * @package app\common\model
 */
class Carrier extends Model
{
    // 表名
    protected $name = 'ebay_carrier';

    /**
     * @desc
     * @var
     */
    public $field = 'id,name,api_name,value,value3,country,city,username,tel,street,address,ebay_warehouse,CompanyName,stnames,upload_wight,sorting_code,completed_time';
}