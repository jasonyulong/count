<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    levin
 */

namespace app\count\command\finance;

use app\count\model\FinanceAmount;
use app\count\model\FinanceRefund;
use think\cache\driver\Redis;
use think\console\Input;
use think\console\Output;
use think\Config;

/**
 * 收支计算
 * Class Refund
 * @package app\count\command\finance
 */
class Funds
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
     * @var 输出
     */
    private $output;

    /**
     * 构造函数
     * Profit constructor.
     */
    public function __construct()
    {
        $this->redis      = new Redis(Config::get('redis'));
        $this->orderModel = new \app\count\model\Order();
    }

    /**
     * 运行收支
     * @param Input $input
     * @param Output $output
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function sync(Input $input, Output $output)
    {
        $this->output = $output;

        $options = $input->getOptions();

        $day   = $options['day'] ?? date('Y-m-d', strtotime("-1 day"));
        $start = $options['start'] ?? $day;
        $end   = $options['end'] ?? date('Y-m-d');

        $dayarr = $this->getDays($start, $end);
        // 如果没指定日期，则获取当天发生变化的订单
        /*
        if (empty($options['start']) && empty($options['end'])) {
            $whereDate = ['status' => 2, 'uptime' => [['EGT', $start . ' 00:00:00'], ['ELT', $end . ' 23:59:59']]];

            $upDay = $this->orderModel->where($whereDate)->field('DISTINCT DATE(FROM_UNIXTIME(deliverytime)) AS day')->select()->toArray();
            if (!empty($upDay)) {
                foreach ($upDay as $value) {
                    if ($value['day'] == '1970-01-01') continue;
                    $dayarr[] = $value['day'];
                }
                $dayarr = array_unique($dayarr);
            }
        }
        */
        foreach ($dayarr as $ymd) {
            // 标记开始
            $output->writeln(sprintf("%s %s", $ymd, 'start'));

            $this->getFundsOrders($ymd);

            $output->writeln(sprintf('%s - %s', $ymd, 'success'));
        }
        return "success\n\n";
    }

    /**
     * 查找售后数据并更新
     * @param $ymd
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function getFundsOrders($ymd)
    {
        $start = strtotime($ymd . ' 00:00:00');
        $end   = strtotime($ymd . ' 23:59:59');

        // 获取补发并已发货的总单量和总金额
        $where  = ['deliverytime' => ['BETWEEN', [$start, $end]], 'status' => 2];
        $fields = 'platform, platform_account, sales_branch_id, sales_user, develop_user,SUM(total) AS total, COUNT(id) as counts, SUM(cost) AS cost, SUM(carrier_freight) AS freight, SUM(package_fee) AS package_fee, SUM(platform_fee) AS platform_fee, SUM(paypal_fee) AS paypal_fee, SUM(brokerage_fee) AS brokerage_fee, SUM(refund_money) AS refund_money, SUM(profit) AS profit, SUM(onlinefee) as onlinefee';
        $select = $this->orderModel->where($where)->field($fields)->group('platform_account, sales_branch_id, sales_user, develop_user')->select();

        $this->output->writeln($ymd . ":" . $this->orderModel->getLastSql());
        if (!empty($select)) {
            $this->saveFunds($ymd, $select);
        }

        return true;
    }

    /**
     * 更新售后数据
     * @param $ymd 日期
     * @param $values 数据
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function saveFunds($ymd, $values)
    {
        if (empty($values)) {
            return false;
        }
        $year  = date('Y', strtotime($ymd));
        $month = date('m', strtotime($ymd));
        $day   = date('d', strtotime($ymd));

        $model = new FinanceAmount();
        foreach ($values as $val) {
            $unique    = md5($year . $month . $day . $val->platform . $val->platform_account . $val->sales_branch_id . $val->sales_user . $val->develop_user);
            $fieldData = [
                'total'            => round($val->total, 2),
                'cost'             => round($val->cost, 2),
                'freight'          => round($val->freight, 2),
                'material'         => round($val->package_fee, 2),
                'platform_fee'     => round($val->platform_fee, 2),
                'paypal'           => round($val->paypal_fee, 2),
                'commission'       => round($val->brokerage_fee, 2),
                'onlinefee'        => round($val->onlinefee, 2),
                //'refunds'          => $this->getRefunds($year, $month, $day, $val->platform, $val->platform_account, $val->sales_branch_id, $val->sales_user, $val->develop_user),
                'gross'            => round($val->profit, 2),
                'platform'         => $val->platform,
                'platform_account' => $val->platform_account,
                'sales_branch_id'  => $val->sales_branch_id,
                'sales_user'       => $val->sales_user,
                'develop_user'     => $val->develop_user,
            ];

            $hasRefund = $model->where(['unique' => $unique])->field('id')->find();
            if (!empty($hasRefund)) {
                $model->save($fieldData, ['unique' => $unique]);
            } else {
                $model->insert(array_merge([
                    'year'             => $year,
                    'month'            => $month,
                    'days'             => $day,
                    'unique'           => $unique,
                    'platform'         => $val->platform,
                    'platform_account' => $val->platform_account,
                    'datetime'         => strtotime($ymd),
                ], $fieldData));
            }
        }
        return true;
    }

    /**
     * 查找当前的退款金额
     * @param $year 年
     * @param $month 月
     * @param $day 日
     * @param $platform 平台
     * @param $platform_account 平台帐号
     * @param $sales_branch_id 销售员所在组织架构ID
     * @param $sales_user 销售员
     * @param $develop_user 开发员
     * @return int
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function getRefunds($year, $month, $day, $platform, $platform_account, $sales_branch_id, $sales_user, $develop_user)
    {
        $where = [
            'year'             => $year,
            'month'            => $month,
            'days'             => $day,
            'platform'         => $platform,
            'platform_account' => $platform_account,
            'sales_branch_id'  => $sales_branch_id,
            'sales_user'       => $sales_user,
            'develop_user'     => $develop_user,
        ];
        $find  = FinanceRefund::where($where)->field('SUM(refund_total) as refund_total')->find();
        return !empty($find) ? round($find['refund_total'], 2) : 0;
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
        $days = (strtotime($end) - strtotime($start)) / 86400;
        if ($days <= 0) return [$start];

        for ($i = 0; $i <= $days; $i++) {
            $default[] = date('Y-m-d', strtotime("+{$i} day", strtotime($start)));
        }
        return $default;
    }
}