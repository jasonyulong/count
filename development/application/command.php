<?php
// +----------------------------------------------------------------------
// | 命令执行应用
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: kevin
// +----------------------------------------------------------------------

return [
    // 数据同步处理
    'app\count\command\Sync',

    // 公共数据同步
    'app\common\command\Common',

    // 自动统计订单状态
    'app\count\command\AutoCountOrderStatus',

    // 自动统计销售额
    'app\count\command\AutoCountSales',

    //统计sku数据
    'app\count\command\Sku',

    //统计财务数据
    'app\count\command\Finance',

    // 统计订单数据
    'app\count\command\Order',

    // 统计物流数据
    'app\count\command\Transport',

    // 静态文件压缩[js|css]
    'app\common\command\Min',

    // 统计采购账款数据
    'app\count\command\Purchase',

    //sku统计
    'app\count\command\SkuSync',

    //开发统计
    'app\count\command\Develop'
];
