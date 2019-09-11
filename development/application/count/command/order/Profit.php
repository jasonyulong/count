<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    kevin
 */

namespace app\count\command\order;

use think\cache\driver\Redis;
use think\Config;
use think\console\Input;
use think\console\Output;
use app\common\library\ToolsLib;

/**
 * 利润相关任务
 * Class Profit
 * @package app\count\command\order
 */
class Profit
{
    /**
     * @var Redis
     */
    private $redis;
    /**
     * @var \app\count\model\Order
     */
    private $orderModel;

    /**
     * 构造函数
     * Profit constructor.
     */
    public function __construct()
    {
        $this->redis = new Redis(Config::get('redis'));
        $this->orderModel = new \app\count\model\Order();
    }

    /**
     * 运行预估利润运行
     * @param Input $input
     * @param Output $output
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function estimate(Input $input, Output $output)
    {
        $options = $input->getOptions();

        $day = $options['day'] ?? date('Y-m-d');
        $start = $options['start'] ?? $day;
        $end = $options['end'] ?? date('Y-m-d');
        $platform = $options['platform'] ?? '';
        // 平台不能为空
        if (empty($platform)) {
            $output->writeln("--platform参数不能为空");
            return;
        }
        // 标记开始
        $output->writeln(sprintf("%s %s", $platform, 'start'));

        $dayarr = $this->getDays($start, $end);
        $where = [
            'platform' => $platform,
            'status'   => 2,
            'type'     => ['NEQ', 3],
        ];
        // 如果没指定日期，则获取当天发生变化的订单
        if (empty($options['start']) && empty($options['end'])) {
            $whereDate = ['uptime' => [['EGT', $start . ' 00:00:00'], ['ELT', $end . ' 23:59:59']]];

            $upDay = $this->orderModel->where(array_merge($where, $whereDate))->field('DISTINCT DATE(FROM_UNIXTIME(createdtime)) AS day')->select()->toArray();
            if (!empty($upDay)) {
                foreach ($upDay as $value) {
                    if (empty($value['day']) || $value['day'] == '1970-01-01') continue;
                    $dayarr[] = $value['day'];
                }
                $dayarr = array_unique($dayarr);
            }
        }
        if (empty($dayarr)) {
            return;
        }
        $output->writeln(sprintf("days:%s", implode(",", $dayarr)));
        foreach ($dayarr as $ymd) {
            $createdtime = ['createdtime' => ['BETWEEN', [strtotime($ymd . ' 00:00:00'), strtotime($ymd . ' 23:59:59')]]];

            $profit = $this->getProfit(array_merge($where, $createdtime));
            $this->saveProfit($platform, $ymd, $profit);

            $output->writeln(sprintf('%s - %s - %s', $platform, $ymd, 'success'));
        }

        $output->writeln(sprintf("%s %s", $platform, 'end'));
        return;
    }

    /**
     * 确认利润运行
     * @param Input $input
     * @param Output $output
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function confirm(Input $input, Output $output)
    {
        $options = $input->getOptions();

        $day = $options['day'] ?? date('Y-m-d', strtotime("-1 day"));
        $start = $options['start'] ?? $day;
        $end = $options['end'] ?? date('Y-m-d');
        $platform = $options['platform'] ?? 'ebay';

        $output->writeln(sprintf("%s %s", $platform, 'start'));

        $dayarr = $this->getDays($start, $end);
        $where = [
            'platform'      => $platform,
            'status'        => 2,
            'profit_status' => 1,
            'type'          => ['NEQ', 3],
        ];
        // 如果没指定日期，则获取当天发生变化的订单
        if (empty($options['start']) && empty($options['end'])) {
            $whereDate = ['profit_time' => ['BETWEEN', [strtotime($start . ' 00:00:00'), strtotime($end . ' 23:59:59')]]];

            $upDay = $this->orderModel->where(array_merge($where, $whereDate))->field('DISTINCT DATE(FROM_UNIXTIME(createdtime)) AS day')->select()->toArray();
            if (!empty($upDay)) {
                foreach ($upDay as $value) {
                    if ($value['day'] == '1970-01-01') continue;
                    $dayarr[] = $value['day'];
                }
                $dayarr = array_unique($dayarr);
            }
            $output->writeln(sprintf("获取%s,%s改变订单所在的确认利润日期", $start, $end));
        }

        $output->writeln(sprintf("days:%s", implode(",", $dayarr)));
        foreach ($dayarr as $ymd) {
            $createdtime = ['createdtime' => ['BETWEEN', [strtotime($ymd . ' 00:00:00'), strtotime($ymd . ' 23:59:59')]]];

            $profit = $this->getProfit(array_merge($where, $createdtime), 1);
            $output->writeln(sprintf("%s查询到%s条记录", $ymd, count($profit)));

            $this->saveProfit($platform, $ymd, $profit, false);
            $output->writeln(sprintf('%s - %s - %s', $platform, $ymd, 'success'));
        }

        $output->writeln(sprintf("%s %s", $platform, 'end'));
        return;
    }

    /**
     * 更新利润数据
     * @param string $plarform 平台
     * @param string $date 日期
     * @param $profit 利润数据
     * @param bool $hasPre 是否预估利润
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function saveProfit(string $plarform, string $date, $profit, $hasPre = true): bool
    {
        if ($hasPre) {
            $model = new \app\count\model\OrderPreProfit();
        } else {
            $model = new \app\count\model\OrderProfit();
        }

        $year = date('Y', strtotime($date));
        $month = date('m', strtotime($date));
        $days = date('d', strtotime($date));
        if (empty($profit)) {
            $model->where([
                'platform' => $plarform,
                'year'     => $year,
                'month'    => $month,
                'days'     => $days,
            ])->delete();
            return true;
        }
        $unique = $year . $month . $days . $plarform;

        // TODO: 一次性获取 表中的记录数
        $group_by = 'platform_account, seller, branch_id';
        $group_fields = "{$group_by}, COUNT(*) as c";
        $where_group = ['year' => $year, 'month' => $month, 'days' => $days, 'platform' => $plarform];
        $tmp = $model->field($group_fields)->where($where_group)->group($group_by)->select()->toArray();

        $count_group = [];
        foreach ($tmp as $value) {
            $tmp_key = "{$value['branch_id']}___{$value['seller']}___{$value['platform_account']}";
            $count_group[$tmp_key] = $value['c'];
        }

        // TODO: 一次性获取 订单表中的 已发货订单数和总订单数
        // 查找总订单数
        $where_order_total = [
            'createdtime' => ['BETWEEN', [strtotime($date . ' 00:00:00'), strtotime($date . ' 23:59:59')]],
            'platform'    => $plarform,
            'status'      => ['IN', $this->_getValidSaleOrderStatus()],
            'type'        => ['NOT IN', [3]],
        ];
        // 查找已发货订单数
        $where_order_sended = array_merge($where_order_total, ['status' => 2]);
        $group_by_order = 'platform_account, sales_branch_id, sales_user';
        $group_order_fields = "{$group_by_order}, COUNT(id) as c";

        $tmp = $this->orderModel->field($group_order_fields)->where($where_order_total)->group($group_by_order)->select();

        $order_total_count_group = [];
        foreach ($tmp as $value) {
            $tmp_key = "{$value['sales_branch_id']}___{$value['sales_user']}___{$value['platform_account']}";
            $order_total_count_group[$tmp_key] = $value['c'];
        }

        $tmp = $this->orderModel->field($group_order_fields)->where($where_order_sended)->group($group_by_order)->select();

        $order_sended_count_group = [];
        foreach ($tmp as $value) {
            $tmp_key = "{$value['sales_branch_id']}___{$value['sales_user']}___{$value['platform_account']}";
            $order_sended_count_group[$tmp_key] = $value['c'];
        }

        foreach ($profit as $val) {
            $uniquekey = md5($unique . $val->sales_branch_id . $val->platform_account . $val->sales_user);
            $where = ['unique' => $uniquekey];

            // 更新数据
            $saveData = [
                'sales'           => $val->sales,
                'profit'          => ($hasPre ? $val->estimate_profit : $val->profit),
                'profit_totals'   => $val->profit_totals,
                'cost'            => $val->cost,
                'carrier_freight' => $val->carrier_freight,
                'onlinefee'       => $val->onlinefee,
                'package_fee'     => $val->package_fee,
                'platform_fee'    => $val->platform_fee,
                'paypal_fee'      => $val->paypal_fee,
                'brokerage_fee'   => $val->brokerage_fee,
            ];
            // 插入数据
            $insertData = [
                'year'             => $year,
                'month'            => $month,
                'days'             => $days,
                'platform'         => $plarform,
                'branch_id'        => $val->sales_branch_id,
                'platform_account' => $val->platform_account,
                'seller'           => $val->sales_user,
                'sales'            => $val->sales,
                'profit'           => ($hasPre ? $val->estimate_profit : $val->profit),
                'profit_totals'    => $val->profit_totals,
                'cost'             => $val->cost,
                'carrier_freight'  => $val->carrier_freight,
                'onlinefee'        => $val->onlinefee,
                'package_fee'      => $val->package_fee,
                'platform_fee'     => $val->platform_fee,
                'paypal_fee'       => $val->paypal_fee,
                'brokerage_fee'    => $val->brokerage_fee,
                'unique'           => $uniquekey,
                'datetime'         => strtotime($date),
            ];
            // 拼装数据
            $tmp_key = "{$val['sales_branch_id']}___{$val['sales_user']}___{$val['platform_account']}";

            $saveData['totals'] = $order_total_count_group[$tmp_key] ?? 0;
            $saveData['totals_ship'] = $order_sended_count_group[$tmp_key] ?? 0;

            $insertData['totals'] = $saveData['totals'];
            $insertData['totals_ship'] = $saveData['totals_ship'];

            $tmp_count = $count_group[$tmp_key] ?? 0;
            if ($tmp_count && $tmp_count > 0) {
                $model->where($where)->update($saveData);
            } else {
                $model->insert($insertData);
            }
        }

        return true;
    }

    /**
     * 获取计算销售额的订单状态
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-08 06:26:07
     */
    public function _getValidSaleOrderStatus()
    {
        $data = ToolsLib::getInstance()->getRedis()->get(Config::get('redis.order_status'));

        $all_order_status = array_keys($data);
        $except_order_status = [1731];

        return array_diff($all_order_status, $except_order_status);
    }

    /**
     * 查询订单数
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function getOrders($where)
    {
        $fields = 'COUNT(id) as totals';

        $totals = $this->orderModel->where($where)->field($fields)->find();
        //echo "查找总订单数：" . $this->orderModel->getLastSql() . PHP_EOL;

        $totals_ship = $this->orderModel->where(array_merge($where, ['status' => 2]))->field($fields)->find();
        //echo "查找已发货订单数：" . $this->orderModel->getLastSql() . PHP_EOL;

        return [
            'totals'      => intval($totals['totals']),
            'totals_ship' => intval($totals_ship['totals'])
        ];
    }

    /**
     * 查询利润和销售额
     * @param $where
     * @param $type 0=预估 1=确认
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function getProfit($where, $type = 0)
    {
        $freight = $type === 0 ? 'order_freight' : 'carrier_freight';
        $group_by = 'platform_account, sales_user, sales_branch_id';
        $fields = "{$group_by},SUM(total) AS sales, SUM(estimate_profit) AS estimate_profit, SUM(profit) AS profit, COUNT(id) AS profit_totals, SUM(cost) as cost, SUM({$freight}) as carrier_freight, SUM(onlinefee) as onlinefee, SUM(package_fee) as package_fee, SUM(platform_fee) as platform_fee, SUM(paypal_fee) as paypal_fee, SUM(brokerage_fee) as  brokerage_fee";
        $select = $this->orderModel->where($where)->field($fields)->group($group_by)->select();
        return $select;
    }

    /**
     * 获取两个区间的所有天
     * @param $start 开始时间
     * @param $end 结束时间
     * @param array $default
     * @return array
     */
    private function getDays($start, $end, $default = [])
    {
        if ($start == $end) {
            return [$start];
        }
        $days = (strtotime($end) - strtotime($start)) / 86400;
        if ($days <= 0) return [$start];

        for ($i = 0; $i <= $days; $i++) {
            $default[] = date('Y-m-d', strtotime("+{$i} day", strtotime($start)));
        }
        return $default;
    }
}
