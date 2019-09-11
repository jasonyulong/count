<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    levin
 */

namespace app\count\command\finance;

use app\count\model\FinanceRefund;
use think\cache\driver\Redis;
use think\console\Input;
use think\console\Output;
use think\Config;

/**
 * 退款订单计算
 * Class Refund
 * @package app\count\command\finance
 */
class Refund
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
        $this->redis      = new Redis(Config::get('redis'));
        $this->orderModel = new \app\count\model\Order();
    }

    /**
     * 运行售后
     * @param Input $input
     * @param Output $output
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function sync(Input $input, Output $output)
    {
        $options = $input->getOptions();

        $day   = $options['day'] ?? date('Y-m-d', strtotime("-3 day"));
        $start = $options['start'] ?? $day;
        $end   = $options['end'] ?? date('Y-m-d');

        // 如果没指定日期，则获取当天发生变化的订单
        $dayarr = $this->getDays($start, $end);
        foreach ($dayarr as $ymd) {
            // 标记开始
            $output->writeln(sprintf("%s %s", $ymd, 'start'));
            // 今天更新的记录
            $uniques = [];
            // 查询今天所有的记录
            $dayUniques = $this->getUniques($ymd);

            $uniquesCreate = $this->getCreatedrders($output, $ymd);
            $uniquesRefund = $this->getRefundOrders($output, $ymd);
            $uniquesGift   = $this->getGiftrders($output, $ymd);
            $uniquesReturn = $this->getReturnOrders($output, $ymd);

            if (is_array($uniquesCreate) && count($uniquesCreate) > 0) {
                $uniques = array_merge($uniques, $uniquesCreate);
            }
            if (is_array($uniquesRefund) && count($uniquesRefund) > 0) {
                $uniques = array_merge($uniques, $uniquesRefund);
            }
            if (is_array($uniquesReturn) && count($uniquesReturn) > 0) {
                $uniques = array_merge($uniques, $uniquesReturn);
            }
            if (is_array($uniquesGift) && count($uniquesGift) > 0) {
                $uniques = array_merge($uniques, $uniquesGift);
            }
            $diff = array_diff($dayUniques, $uniques);
            if (!empty($diff)) {
                $this->delUniques($diff);
            }

            $output->writeln(sprintf('%s - %s', $ymd, 'success'));
        }
        return "success\n\n";
    }

    /**
     * 获取今天的记录
     * @param $ymd
     * @return array
     */
    private function getUniques($ymd)
    {
        $year  = date('Y', strtotime($ymd));
        $month = date('m', strtotime($ymd));
        $day   = date('d', strtotime($ymd));

        $model = new FinanceRefund();
        return $model->where(['year' => $year, 'month' => $month, 'days' => $day])->column('unique');
    }

    /**
     * 删除记录
     * @param $uniques
     * @return int
     */
    private function delUniques($uniques)
    {
        $model = new FinanceRefund();
        return $model->where(['unique' => ['IN', $uniques]])->delete();
    }

    /**
     * 获取补发并已发货的总单量和总金额
     * @param $ymd
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function getCreatedrders($output, $ymd)
    {
        $whereDate = [
            'type'   => 3,
            'status' => 2,
            'uptime' => [['EGT', $ymd . ' 00:00:00'], ['ELT', $ymd . ' 23:59:59']]
        ];
        $upDay     = $this->orderModel->where($whereDate)->field('DISTINCT DATE(FROM_UNIXTIME(createdtime)) AS day')->select()->toArray();
        if (empty($upDay)) {
            return false;
        }
        $returns = [];
        foreach ($upDay as $value) {
            if ($value['day'] == '1970-01-01') continue;
            $day = $value['day'];

            $start = strtotime($day . ' 00:00:00');
            $end   = strtotime($day . ' 23:59:59');

            $output->writeln(sprintf('%s - %s', $day, 'created success'));

            // 获取补发并已发货的总单量和总金额
            $where  = ['createdtime' => ['BETWEEN', [$start, $end]], 'type' => 3, 'status' => 2];
            $fields = 'platform, platform_account, carrier, carrier_company, couny, couny_name, sales_branch_id, sales_user, develop_user, SUM(total) AS totals, COUNT(id) as counts';
            $select = $this->orderModel->where($where)->field($fields)->group('platform_account, carrier, couny, sales_user, develop_user')->select();
            if (!empty($select)) {
                $result = $this->saveRefunds($day, $select, 1);
                if (is_array($result) && count($result) > 0) $returns = array_merge($returns, $result);
            }
        }

        return $returns;
    }

    /**
     * 查找礼物订单的数量和销售额
     * @param $output
     * @param $ymd
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function getGiftrders($output, $ymd)
    {
        $whereDate = [
            'type'   => 8,
            'status' => 2,
            'uptime' => [['EGT', $ymd . ' 00:00:00'], ['ELT', $ymd . ' 23:59:59']]
        ];
        $upDay     = $this->orderModel->where($whereDate)->field('DISTINCT DATE(FROM_UNIXTIME(createdtime)) AS day')->select()->toArray();
        if (empty($upDay)) {
            return false;
        }
        $returns = [];
        foreach ($upDay as $value) {
            if ($value['day'] == '1970-01-01') continue;
            $day = $value['day'];

            $start = strtotime($day . ' 00:00:00');
            $end   = strtotime($day . ' 23:59:59');

            $output->writeln(sprintf('%s - %s', $day, 'gift success'));

            // 获取补发并已发货的总单量和总金额
            $where  = ['createdtime' => ['BETWEEN', [$start, $end]], 'type' => 8, 'status' => 2];
            $fields = 'platform, platform_account, carrier, carrier_company, couny, couny_name, sales_branch_id, sales_user, develop_user, SUM(total) AS totals, COUNT(id) as counts';
            $select = $this->orderModel->where($where)->field($fields)->group('platform_account, carrier, couny, sales_user, develop_user')->select();
            if (!empty($select)) {
                $result = $this->saveRefunds($day, $select, 4);
                if (is_array($result) && count($result) > 0) $returns = array_merge($returns, $result);
            }
        }

        return $returns;
    }

    /**
     * 获取退货并已发货的总单量和总金额
     * @param $ymd
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function getReturnOrders($output, $ymd)
    {
        $day   = $ymd;
        $start = strtotime($day . ' 00:00:00');
        $end   = strtotime($day . ' 23:59:59');

        $output->writeln(sprintf('%s - %s', $day, 'return success'));

        // 获取退货并已发货的总单量和总金额
        $where  = ['return_time' => ['BETWEEN', [$start, $end]], 'type' => 7, 'status' => 2];
        $fields = 'platform, platform_account, carrier, carrier_company, couny, couny_name, sales_branch_id, sales_user, develop_user, SUM(return_money) AS totals, COUNT(id) as counts';
        $select = $this->orderModel->where($where)->field($fields)->group('platform_account, carrier, couny, sales_user, develop_user')->select();

        if (!empty($select)) {
            return $this->saveRefunds($day, $select, 2);
        }
        return true;
    }

    /**
     * 获取退款并已发货的总单量和总金额
     * @param $ymd
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function getRefundOrders($output, $ymd)
    {
        $day   = $ymd;
        $start = strtotime($day . ' 00:00:00');
        $end   = strtotime($day . ' 23:59:59');

        $output->writeln(sprintf('%s - %s', $day, 'refund success'));

        // 获取退款并已发货的总单量和总金额
        $where  = ['refund_time' => ['BETWEEN', [$start, $end]], 'type' => 4, 'status' => 2];
        $fields = 'platform, platform_account, carrier, carrier_company, couny, couny_name, sales_branch_id, sales_user, develop_user, SUM(refund_money) AS totals, COUNT(id) as counts';
        $select = $this->orderModel->where($where)->field($fields)->group('platform_account, carrier, couny, sales_user, develop_user')->select();
        if (!empty($select)) {
            return $this->saveRefunds($day, $select, 3);
        }
    }

    /**
     * 更新售后数据
     * @param $ymd 日期
     * @param $values 数据
     * @param int $type 1=补发 2=退货 3=退款
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function saveRefunds($ymd, $values, $type = 1)
    {
        if (empty($values)) {
            return false;
        }
        $year  = date('Y', strtotime($ymd));
        $month = date('m', strtotime($ymd));
        $day   = date('d', strtotime($ymd));

        $model   = new FinanceRefund();
        $uniques = [];
        foreach ($values as $val) {
            $unique = md5($year . $month . $day . $val->platform . $val->platform_account . $val->couny . $val->carrier . $val->sales_user . $val->develop_user);

            $uniques[] = $unique;
            $fieldData = [];

            if ($type == 1) {
                // 更新补发订单数据
                $fieldData = ['reissue_num' => $val->counts, 'reissue_total' => $val->totals];
            } elseif ($type == 2) {
                // 更新退货订单数据
                $fieldData = ['return_num' => $val->counts, 'return_total' => $val->totals];
            } elseif ($type == 3) {
                // 更新退款订单数据
                $fieldData = ['refund_num' => $val->counts, 'refund_total' => $val->totals];
            } elseif ($type == 4) {
                // 更新礼物订单数据
                $fieldData = ['gift_num' => $val->counts, 'gift_total' => $val->totals];
            }
            if (empty($fieldData)) continue;

            $hasRefund = $model->where(['unique' => $unique])->find();
            if (!empty($hasRefund)) {
                $model->save($fieldData, ['unique' => $unique]);
            } else {
                $model->insert(array_merge([
                    'year'             => $year,
                    'month'            => $month,
                    'days'             => $day,
                    'platform'         => $val->platform,
                    'platform_account' => $val->platform_account,
                    'couny'            => $val->couny,
                    'carrier'          => $val->carrier,
                    'carrier_company'  => $val->carrier_company,
                    'sales_user'       => $val->sales_user,
                    'develop_user'     => $val->develop_user,
                    'unique'           => $unique,
                    'datetime'         => strtotime($ymd),
                ], $fieldData));
            }
        }
        return $uniques;
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