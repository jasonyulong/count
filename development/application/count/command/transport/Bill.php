<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    levin
 */

namespace app\count\command\transport;

use app\common\model\TransportPay;
use app\common\model\TransportReturn;
use app\count\model\FinanceAmount;
use app\count\model\FinanceRefund;
use app\count\model\TransportBill;
use app\count\model\TransportOutlay;
use think\cache\driver\Redis;
use think\console\Input;
use think\console\Output;
use think\Config;

/**
 * 物流对账
 * Class Refund
 * @package app\count\command\finance
 */
class Bill
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
        $this->orderModel = new \app\common\model\TransportOrder();
    }

    /**
     * 运行
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

        $day   = $options['day'] ?? date('Y-m-d');
        $start = $options['start'] ?? $day;
        $end   = $options['end'] ?? date('Y-m-d');

        $dayarr = $this->getDays($start, $end);
        foreach ($dayarr as $ymd) {
            // 标记开始
            $output->writeln(sprintf("%s %s", $ymd, 'start'));

            // 对账订单数据更新
            $this->setTransport($ymd);
            // 对账费用数据更新
            $this->setTransportOutlay($ymd);

            $output->writeln(sprintf('%s - %s', $ymd, 'success'));
        }
        return "success\n\n";
    }

    /**
     * 查找对账数据
     * @param $ymd
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function setTransport($ymd)
    {
        $start = strtotime($ymd . ' 00:00:00');
        $end   = strtotime($ymd . ' 23:59:59');

        // 已对账的总单量和总金额
        $where  = ['check_time' => ['BETWEEN', [$start, $end]], 'has_check' => 2];
        $fields = 'platform, carrier_company, carrier, SUM(settle_freight*rates) AS freight, COUNT(id) as counts, SUM(settle_weight) AS weight';
        $select = $this->orderModel->where($where)->field($fields)->group('platform, carrier_company, carrier')->select();
        if (!empty($select)) {
            // 更新到对账表里
            $this->saveTransportBill($ymd, $select, 1);
        }

        // 待对账的总单量和总运费、总重量
        $where  = ['delivery_time' => ['ELT', $end], 'transport_id' => 0, 'has_check' => 1];
        $fields = 'platform, carrier_company, carrier, SUM(order_freight*rates) AS freight, COUNT(id) as counts, SUM(settle_weight) AS weight';
        $select = $this->orderModel->where($where)->field($fields)->group('platform, carrier_company, carrier')->select();
        if (!empty($select)) {
            // 更新到对账表里
            $this->saveTransportBill($ymd, $select, 0);
        }

        // 对账中的总单量和总运费、总重量
        // $where  = ['delivery_time' => ['ELT', $end], 'transport_id' => ['GT', 0], 'has_check' => 1];
        $where  = ['transport_id' => ['GT', 0], 'has_check' => 1];
        $fields = 'platform, carrier_company, carrier, SUM(settle_freight*rates) AS freight, COUNT(id) as counts, SUM(settle_weight) AS weight';
        $select = $this->orderModel->where($where)->field($fields)->group('platform, carrier_company, carrier')->select();
        if (!empty($select)) {
            // 更新到对账表里
            $this->saveTransportBill($ymd, $select, 2);
        }
        return true;
    }

    /**
     * 查询物流对账费用信息
     * @param $ymd
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function setTransportOutlay($ymd)
    {
        $year  = date('Y', strtotime($ymd));
        $month = date('m', strtotime($ymd));
        $day   = date('d', strtotime($ymd));
        $start = strtotime($ymd . ' 00:00:00');
        $end   = strtotime($ymd . ' 23:59:59');

        $transportPayModel = new TransportPay();
        // 已对账的总单量和总金额
        $fields   = 'carrier_company,SUM(finish_orders) AS finish_orders, SUM(finish_money) AS finish_money, SUM(bepaid_orders) AS bepaid_orders, SUM(bepaid_money) AS bepaid_money ';
        $billData = TransportBill::where(['year' => $year, 'month' => $month, 'days' => $day])->field($fields)->group('carrier_company')->select();
        if (!empty($billData)) {
            // 更新到对账表里
            $this->saveTransportPay($ymd, $billData, 0);
        }

        // 申请充值金额($)
        $where      = ['apply_time' => ['BETWEEN', [$start, $end]], 'module' => 1, 'status' => ['IN', [1, 3, 5, 7, 8]]];
        $fields     = 'carrier_company,apply_money as money,currency';
        $apply_into = $transportPayModel->where($where)->field($fields)->select();
        // 全部计算成美元
        if (!empty($apply_into)) {
            // 更新到对账表里
            $this->saveTransportPayByFields($ymd, $apply_into, 'apply_into');
        }

        // 完成充值金额($)
        // 先获取今天支付的充值的申请时间
        $fields      = "DISTINCT FROM_UNIXTIME(apply_time, '%Y-%m-%d') AS apply_date";
        $where       = ['apply_time' => ['BETWEEN', [$start, $end]], 'module' => 1, 'status' => ['IN', 7]];
        $apply_times = $transportPayModel->where($where)->field($fields)->select();
        if (!empty($apply_times)) {
            foreach ($apply_times as $val) {
                $where     = [
                    'apply_time' => ['BETWEEN', [strtotime($val->apply_date . ' 00:00:00'), strtotime($val->apply_date . ' 23:59:59')]],
                    'module'     => 1,
                    'status'     => 7,
                ];
                $fields    = 'carrier_company,total_money as money,pay_currency as currency';
                $apply_end = $transportPayModel->where($where)->field($fields)->select();
                // 全部计算成美元
                if (!empty($apply_end)) {
                    // 更新到对账表里
                    $this->saveTransportPayByFields($ymd, $apply_end, 'apply_end');
                }
            }
        }

        // 申请支付金额($)
        $where     = ['apply_time' => ['BETWEEN', [$start, $end]], 'module' => 0, 'type' => 0, 'status' => ['IN', [1, 3, 5, 7, 8]]];
        $fields    = 'carrier_company,apply_money as money,currency';
        $apply_pay = $transportPayModel->where($where)->field($fields)->select();
        // 全部计算成美元
        if (!empty($apply_pay)) {
            // 更新到对账表里
            $this->saveTransportPayByFields($ymd, $apply_pay, 'apply_pay');
        }

        // 待支付金额($)
        $where    = ['apply_time' => ['BETWEEN', [$start, $end]], 'module' => 0, 'type' => 0, 'status' => ['IN', [1, 3, 5]]];
        $fields   = 'carrier_company,apply_money as money,currency';
        $wait_pay = $transportPayModel->where($where)->field($fields)->select();
        // 全部计算成美元
        if (!empty($wait_pay)) {
            // 更新到对账表里
            $this->saveTransportPayByFields($ymd, $wait_pay, 'wait_pay');
        }

        // 已支付金额($)
        // 先获取今天支付的订单的申请时间
        $fields      = "DISTINCT FROM_UNIXTIME(apply_time, '%Y-%m-%d') AS apply_date";
        $where       = ['apply_time' => ['BETWEEN', [$start, $end]], 'module' => 0, 'status' => ['IN', 7]];
        $apply_times = $transportPayModel->where($where)->field($fields)->select();
        if (!empty($apply_times)) {
            foreach ($apply_times as $val) {
                $where      = [
                    'apply_time' => ['BETWEEN', [strtotime($val->apply_date . ' 00:00:00'), strtotime($val->apply_date . ' 23:59:59')]],
                    'module'     => 0,
                    'status'     => 7,
                ];
                $fields     = "carrier_company,total_money as money, pay_currency as currency";
                $finish_pay = $transportPayModel->where($where)->field($fields)->select();
                // 全部计算成美元
                if (!empty($finish_pay)) {
                    // 更新到对账表里
                    $this->saveTransportPayByFields($val->apply_date, $finish_pay, 'finish_pay');
                }
            }
        }

        // 支出合计($)
        $where     = ['pay_time' => ['BETWEEN', [$start, $end]], 'status' => ['IN', 7]];
        $fields    = 'carrier_company,total_money as money, pay_currency as currency';
        $out_total = $transportPayModel->where($where)->field($fields)->select();
        // 全部计算成美元
        if (!empty($out_total)) {
            // 更新到对账表里
            $this->saveTransportPayByFields($ymd, $out_total, 'out_total');
        }

        $transportReturnModel = new TransportReturn();
        // 追款金额
        $where       = ['addtime' => ['BETWEEN', [$start, $end]]];
        $fields      = 'carrier_company,return_money as money,currency';
        $chase_money = $transportReturnModel->where($where)->field($fields)->select();
        // 全部计算成美元
        if (!empty($chase_money)) {
            // 更新到对账表里
            $this->saveTransportPayByFields($ymd, $chase_money, 'chase_money');
        }

        return true;
    }

    /**
     * 保存付款相关数据
     * @param $ymd 日期
     * @param $values 数据
     * @param $field 字段
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function saveTransportPayByFields($ymd, $values, $field)
    {
        $year  = date('Y', strtotime($ymd));
        $month = date('m', strtotime($ymd));
        $day   = date('d', strtotime($ymd));

        $model    = new TransportOutlay();
        $saveData = [];
        $detault  = 'USD';
        foreach ($values as $value) {
            $money = $value->currency != $detault ? replace_currency($value->money, $value->currency, $detault) : $value->money;
            if (!isset($saveData[$value->carrier_company])) {
                $saveData[$value->carrier_company] = 0;
            }
            $saveData[$value->carrier_company] += $money;
        }
        if (empty($saveData)) {
            return true;
        }
        foreach ($saveData as $carrier_company => $money) {
            // 唯一查询条件
            $unique = [
                'year'            => $year,
                'month'           => $month,
                'days'            => $day,
                'carrier_company' => $carrier_company,
            ];
            // 对账单量和待对账单量
            $fieldData = [$field => $money, 'datetime' => strtotime($ymd)];
            $hasRefund = $model->where($unique)->find();
            if (!empty($hasRefund)) {
                $model->update($fieldData, $unique);
            } else {
                $model->insert(array_merge($unique, $fieldData));
            }
        }

        return true;
    }

    /**
     * 更新物流费用信息
     * @param $ymd 日期
     * @param $values 数据
     * @param int $type
     */
    private function saveTransportPay($ymd, $values)
    {
        $year  = date('Y', strtotime($ymd));
        $month = date('m', strtotime($ymd));
        $day   = date('d', strtotime($ymd));

        $model = new TransportOutlay();
        foreach ($values as $val) {
            // 唯一查询条件
            $unique = [
                'year'            => $year,
                'month'           => $month,
                'days'            => $day,
                'carrier_company' => $val->carrier_company,
                'datetime'        => strtotime($ymd),
            ];
            // 对账单量和待对账单量
            $fieldData             = $val->toArray();
            $fieldData['datetime'] = strtotime($ymd);

            $hasRefund = $model->where($unique)->find();
            if (!empty($hasRefund)) {
                $model->update($fieldData, $unique);
            } else {
                $model->insert(array_merge($unique, $fieldData));
            }
        }
        return true;
    }

    /**
     * 更新售后数据
     * @param $ymd 日期
     * @param $values 数据
     * @param int $type
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function saveTransportBill($ymd, $values, $type = 0)
    {
        if (empty($values)) {
            return false;
        }
        $year  = date('Y', strtotime($ymd));
        $month = date('m', strtotime($ymd));
        $day   = date('d', strtotime($ymd));

        $model = new TransportBill();
        foreach ($values as $val) {
            // 唯一查询条件
            $unique = [
                'year'            => $year,
                'month'           => $month,
                'days'            => $day,
                'platform'        => $val->platform,
                'carrier_company' => $val->carrier_company,
                'carrier'         => $val->carrier
            ];

            $fieldData = [
                'carrier'         => $val->carrier,
                'carrier_company' => $val->carrier_company,
                'platform'        => $val->platform,
                'datetime'        => strtotime($ymd),
            ];
            // 待对账
            if ($type == 0) {
                $fieldData = array_merge($fieldData, [
                    'bepaid_orders' => $val->counts,
                    'bepaid_money'  => $val->freight,
                    'bepaid_weight' => $val->weight
                ]);
            } // 已对账
            elseif ($type == 1) {
                $fieldData = array_merge($fieldData, [
                    'finish_orders' => $val->counts,
                    'finish_money'  => $val->freight,
                    'finish_weight' => $val->weight
                ]);
            } // 对账中
            elseif ($type == 2) {
                $fieldData = array_merge($fieldData, [
                    'billing_orders' => $val->counts,
                    'billing_money'  => $val->freight,
                    'billing_weight' => $val->weight
                ]);
            }
            $hasRefund = $model->where($unique)->find();
            if (!empty($hasRefund)) {
                $model->update($fieldData, $unique);
            } else {
                $model->insert(array_merge($unique, $fieldData));
            }
        }
        return true;
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