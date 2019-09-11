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
 * 运输渠道公司表
 * Class CarrierCompany
 * @package app\common\model
 */
class CarrierCompany extends Model
{
    // 表名
    protected $name = 'ebay_carrier_company';

    /**
     * @desc
     * @var
     */
    public $field = 'id,fre_type,sup_name,sup_abbr,tel,address,sup_code,contact_man,zip';
}