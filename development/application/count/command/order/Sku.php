<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    kevin
 */


namespace app\count\command\order;


use app\count\model\OrderDetail;
use think\cache\driver\Redis;
use think\Config;
use think\console\Input;
use think\console\Output;

class Sku
{
    /**
     * 查询数据数量
     * @var int
     */
    private $limit = 2000;

    /**
     * redis链接句柄
     * @var Redis
     */
    private $redis;

    /**
     * 统计订单详情表
     * @var object
     */
    private $orderModel;

    /**
     * 统计订单表
     * @var object
     */
    private $skuModel;

    /**
     * 构造函数
     * Orders constructor.
     * @param Input $input 输入对象
     * @param Output $output 输出对象
     */
    public function __construct()
    {
        $this->redis = new Redis(Config::get('redis'));
    }

    public function sync(Input $input, Output $output)
    {
        $options     = $input->getOptions();
        $detailModel = new OrderDetail();
        $skuModel    = new \app\count\model\Sku();

        $day   = $options['day'] ?? date('Y-m-d');
        $start = $options['start'] ?? '';
        $end   = $options['end'] ?? '';

        // 单独每天
        $where = [
            'b.createdtime' => ['between', [strtotime($day . ' 00:00:00'), strtotime($day . ' 23:59:59')]],
            'b.status'      => ['neq', 1731],
            'b.type'        => ['neq', 3],
        ];

        // 时间区间
        if (!empty($start) && !empty($end)) {
            $where = ['b.createdtime' => ['between', [strtotime($start . ' 00:00:00'), strtotime($end . ' 23:59:59')]]];
        }

        // 根据条件查找所有SKU
        $daysku = $detailModel->alias('a')->where($where)->join('order b', 'a.order_id = b.id')->field('a.sku')->group('a.sku')->select()->toArray();
        if (empty($daysku)) {
            return 'none sku data!!';
        }
        // 需要处理的SKU
        $daysku = array_column($daysku, 'sku');

        // 已经统计了的SKU
        $hasku = $skuModel->where(['datetime' => $where['b.createdtime']])->group('sku')->select()->toArray();
        $hasku = array_column($hasku, 'sku');
        $hasku = ['MQ86234', 'XQ85254_14JTY', 'ZW2803B'];

        // 需要删除的sku, 差集
        $delSku = array_diff($hasku, $daysku);
        // sku数量
        $count = count($daysku);


        for ($i = 0; $i <= $count; $i++) {
            $sku = $daysku[$i];


        }
    }
}