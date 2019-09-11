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
 * 订单表
 * Class Countries
 * @package app\common\model
 */
class Order extends Model
{
    // 表名
    protected $name = 'ebay_order';

    /**
     * @desc  查询字段
     * @var   string
     */
    public $field = 'ebay_id,ordertype,ebay_account,recordnumber,ebay_couny,ebay_city,ebay_state,ebay_total,ebay_currency,ebay_shipfee,status,ebay_addtime,ebay_paidtime,ebay_ordersn,ebay_warehouse,market,ebay_ordertype,ebay_carrier,ebay_tracknumber,orderweight2,orderweight,ebay_status,ebay_paidtime,scantime,paypal_case,location,profitstatus,refundtime,resendtime,canceltime,updateprofittime';

    /**
     * @desc   根据条件查询多条记录
     * @author mina
     * @param  array $where 查询条件
     * @param  string $orderBy 排序 默认订单id降序
     * @return array
     */
    public function getAll($where, $orderBy = 'ebay_id desc')
    {
    	return $this->where($where)->field($this->field)->order($orderBy)->select();
    }
}
