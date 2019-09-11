<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    leo
 */

namespace app\common\model;


use think\Model;

/**
 * 供应商表
 * Class EbayPartner
 * @package app\count\model
 */
class EbayPartner extends Model
{
    // 表名
    protected $name = 'ebay_partner';

    public function getPartnerByKeyWord($keywords)
    {
        return $this->field('id,company_name')->where("company_name LIKE '{$keywords}%'")->order('company_name asc')->select();
    }
}