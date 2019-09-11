<?php
// +----------------------------------------------------------------------
// | redis 键名配置
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: kevin
// +----------------------------------------------------------------------


return [
    // 商品分类 string
    'goods_category'        => 'erp:goods:categorys',

    /**
     * 新进系统的订单存储 hash, 分平台存储，如：erp:orderpull:ebay:201809
     * 数据格式：array(['order' => {订单数据}, 'detail' => {订单SKU详情数据}])
     */
    'ordes_pull'            => 'erp:orderpull:%s:%s',

    /**
     * 等待分配的订单队列
     * 数据格式：erp:orderallot:ebay:20180921
     * {ebay_id}
     */
    'order_waritallot'      => 'erp:orderallot:%s',

    /**
     * 已经发货的订单队列
     * 数据格式：{ebay_id}
     */
    'order_shipped'         => 'erp:ordershipped:%s:%s',

    /**
     * 订单sku详情数据
     */
    'order_goods'           => 'erp:ordergoods',

    /**
     * 确认订单队列
     * 数据格式：array(['profit' => {真实利润}, 'profit_margin' => {真实利润率}, 'carrier_weight' => {物流称重}, 'carrier_freight' => {物流运费}])
     */
    'order_finish'          => 'erp:orderfinish:%s:%s',

    /**
     * 拉回收站订单
     * 数据格式：erp:orderrecycle:20180921
     * {ebay_id}
     */
    'order_recycle'         => 'erp:orderrecycle',

    /**
     * 退款订单
     * 数据格式：erp:orderrefunding:平台
     * {"ebay_id":18553959,"RefundAmount":"8.1388","refundreason":"\u574f\u4e86","refundtime":1535472000}
     */
    'order_refunding'       => 'erp:orderrefunding:%s',

    /**
     * 订单更新
     * ebay_id队列
     */
    'order_changelist'      => 'erp:orders:changelist',
    /**
     * 订单状态表
     */
    'order_status'          => 'erp:orderstatus',

    // 国家二字码 string
    'countries'             => 'erp:countries',

    // 所有运输渠道缓存
    'carrier'               => 'erp:carrier:list',

    // 所有运输渠道公司缓存
    'carrier_company'       => 'erp:carrier:company',

    // 所有的账号信息
    'accounts_list'         => 'erp:accounts:list',
    'accounts_list_timeout' => 3 * 60 * 60,

    // 所有的用户信息
    'user_list'             => 'erp:users:list',
    'user_list_timeout'     => 3 * 60 * 60,

    'developer_list'      => 'erp:developer:list',

    // ------------------- 组织架构相关 -----------------------
    // 业务员所在组ID
    'organization_saleid' => 'org:id:%s',

    'org_list'                => 'erp:orgs:list',
    'org_user_list'           => 'erp:orgusers:list',
    'org_prop_list'           => 'erp:org_prop_list',
    'org_prop_account_to_userid_and_org_id_map' => 'erp:org_prop_account_to_userid_and_org_id_map',
    'org_ebay_account_to_userid_and_org_id_map' => 'erp:org_ebay_account_to_userid_and_org_id_map',
    'org_ebay_list'           => 'erp:org_ebay_list',
    'org_level1_seller_map'   => 'erp:org_level1_seller_map',
    'org_level1_list'         => 'erp:org_level1_list',
    'org_tree'                => 'erp:orgs:tree',
    'org_super_tree'          => 'erp:orgs:super_tree', // 之前的废弃
    'org_full_name_map'       => 'erp:org:full_nam_map',
    'org_user_manage'         => 'erp:org:org_user_manage',

    // 所有仓库
    'store'                   => 'erp:store',

    // 汇率
    'rate'                    => 'erp:rate',
    
    // 销售额计算队列
    'command_order_queue'     => 'command:order:queue:%s',
    'command_skusale'         => 'command:skusale:%s:%s',

    // 组合SKU 的映射
    'combine_sku_map'         => 'erp:sku:combine_sku_map',
    // 同步sku任务最后时间
    'command_goods_countdown' => 'command:goods:countdown:%s',
];
