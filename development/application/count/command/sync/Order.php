<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    kevin
 */

namespace app\count\command\sync;

use app\common\library\CarrierLib;
use app\common\model\Goods;
use app\common\model\Lowprofit;
use app\common\model\OrderDetail;
use app\common\model\OrderFee;
use app\common\model\OrderForecast;
use app\common\model\Organization;
use app\common\model\OrganizationUser;
use app\common\model\ProductsCombine;
use app\common\model\RefundOrders;
use app\common\model\TransportOrder;
use app\count\model\Order as OrderModel;
use app\count\model\OrderDetail as OrderDetailModel;
use app\count\model\OrderDetailFee as OrderDetailFeeModel;
use think\cache\driver\Redis;
use think\console\Input;
use think\console\Output;
use think\Config;
use think\Log;

/**
 * 订单数据同步处理类
 * Class Order
 * @package app\count\command\sync
 */
class Order
{
    /**
     * redis链接句柄
     * @var Redis object
     */
    private $redis;

    /**
     * 平台
     * @var string
     */
    private $platform;

    /**
     * 输入对象
     * @var Input
     */
    private $input;

    /**
     * 输出对象
     * @var Output
     */
    private $output;

    /**
     * @var array 所有物流渠道
     */
    private $carriers = [];

    /**
     * @var int 获取订单数量
     */
    private $num = 500;

    /**
     * @var string 查询字段
     */
    private $alowFields = 'ebay_id,ebay_ordersn,ebay_status,recordnumber,ordertype,ebay_account,accountid,ebay_couny,ebay_city,ebay_state,ebay_total,ebay_shipfee,ebay_warehouse,ebay_carrier,ebay_tracknumber,ebay_paystatus,ebay_ordertype,ebay_currency,RefundAmount,ordercopst,ebay_total,ebay_combine,profitstatus,market,ishide,location,orderweight2,ebay_addtime,ebay_paidtime,scantime,refundtime,resendtime,canceltime,updateprofittime,cancelreason,totalprofit';

    /**
     * 构造函数
     * Orders constructor.
     * @param Input $input 输入对象
     * @param Output $output 输出对象
     */
    public function __construct(Input $input, Output $output)
    {
        $this->redis = new Redis(Config::get('redis'));
        // 平台
        $this->platform = $input->getArgument('platform');
        // 所有渠道数据
        $this->carriers = CarrierLib::init()->getCarrier();

        $this->input = $input;
        $this->output = $output;
    }

    /**
     * 拉单时插入数据
     * @return string
     */
    public function pull(): string
    {
        if (empty($this->platform)) {
            return "platform error";
        }

        $date = date('Ymd');
        $rediskey = sprintf(Config::get('redis.ordes_pull'), $this->platform, $date);

        $this->redis->handler()->select(REDIS_ERP);
        $getVals = $this->redis->handler()->hgetall($rediskey);

        // 获取redis数据
        if (empty($getVals)) {
            return "hgetall value empty!";
        }

        $model = new OrderModel();
        foreach ($getVals as $id => $value) {
            $this->output->writeln(sprintf("%s start", $id));
            $value = json_decode($value, true);
            if (empty($value) || !isset($value['order']) || $model->isExists(['id' => $id])) {
                // 删除已处理的订单
                $this->redis->handler()->select(REDIS_ERP);
                $this->redis->handler()->hdel($rediskey, $id);
                $this->redis->handler()->select(REDIS_ERP5);
                continue;
            }
            // 分解订单需要的数据
            $this->analysisOrder($value['order'], $value['detail']);
            // 删除已处理的订单
            $this->redis->handler()->select(REDIS_ERP);
            $this->redis->handler()->hdel($rediskey, $id);
            $this->redis->handler()->select(REDIS_ERP5);

            $this->output->writeln(sprintf("%s end", $id));
        }
        $this->redis->handler()->select(REDIS_ERP5);

        return "Successed!";
    }

    /**
     * 订单更新时
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function changes()
    {
        $redisKey = Config::get('redis.order_changelist');
        $this->redis->handler()->select(REDIS_ERP);
        for ($i = 0; $i < $this->num; $i++) {
            $ebayId[] = $this->redis->handler()->lpop($redisKey);
        }
        $this->redis->handler()->select(REDIS_ERP5);

        if (empty($ebayId)) {
            return "empty ebay_id";
        }
        $model = new \app\common\model\Order();
        $list = $model->where(['ebay_id' => ['in', $ebayId]])->field($this->alowFields)->select();
        if (empty($list)) {
            return "empty getall";
        }
        $list = $list->toArray();

        // 查询订单详情
        $ordersn = array_column($list, 'ebay_ordersn');
        $detailWhere = [
            'ebay_ordersn' => ['in', $ordersn]
        ];
        $detailRows = OrderDetail::all($detailWhere);
        if (empty($detailRows)) return 'empty detail';

        $detailList = [];
        foreach ($detailRows as $key => $value) {
            if (!empty($value)) {
                $detailList[$value['ebay_ordersn']][] = $value->toArray();
            }
        }
        if (empty($detailList)) return 'empty detail';

        // 同步订单
        $result = [];
        foreach ($list as $key => $value) {
            $state = $this->analysisOrder($value, $detailList[$value['ebay_ordersn']] ?? []);

            $result[(int)$state][] = $value['ebay_id'];
        }
        $success = isset($result[1]) ? count($result[1]) : 0;
        $fail = isset($result[0]) ? count($result[0]) : 0;
        $failStr = isset($result[0]) ? implode(',', $result[0]) : '';
        $returnStr = "success: {$success}, fail: {$fail}";
        if (isset($result[0])) {
            $returnStr .= " \r\nfaile list: {$failStr}";
        }
        return $returnStr;
    }

    /**
     * @desc   根据平台、开始时间、结束时间同步订单
     * php think sync -m orders -a times [平台名称] [订单号] [开始时间] [结束时间]
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author mina
     */
    public function times(): string
    {
        if (empty($this->platform)) {
            return "platform is not empty";
        }
        $times = time();
        $stime = $this->input->getArgument('stime');
        $etime = $this->input->getArgument('etime');
        $id = $this->input->getArgument('id');
        $field = $this->input->getArgument('timefield');

        $fieldArr = ['ebay_addtime', 'scantime', 'updateprofittime', 'refundtime', 'canceltime'];
        if (!in_array($field, $fieldArr)) {
            $field = 'ebay_addtime';
        }
        $redisKeys = sprintf('command:erp:order:times:%s', $field);
        $redisVals = $this->redis->handler()->get($redisKeys);

        $startTimes = !empty($redisVals) ? $redisVals : strtotime("-30 minute");
        $this->redis->handler()->set($redisKeys, $times);

        // 如果是更新退款订单
        $stime = !empty($stime) ? strtotime($stime . " 00:00:00") : $startTimes;
        $etime = !empty($etime) ? strtotime($etime . " 23:59:59") : $times;

        // 查询条件
        $where = [$field => ['BETWEEN', [$stime, $etime]], 'ordertype' => $this->platform];
        if (!empty($id)) {
            unset($where[$field]);
            $where['ebay_id'] = $id;
        }

        $model = new OrderModel();
        $orderModel = new \app\common\model\Order();
        $orderDetailModel = new OrderDetail();
        // 总数
        $counts = $orderModel->where($where)->count('ebay_id');

        $start = 0;
        $limit = 1000;
        $round = 1;

        echo "task start, where: " . json_encode($where) . ",totals:{$counts} \n";

        while ($start < $counts) {
            $list = $orderModel->where($where)->limit($start, $limit)->field($this->alowFields)->select();
            if (empty($list)) {
                return "nont data\n";
            }
            $list = $list->toArray();
            // 查询订单详情
            $ordersn = array_column($list, 'ebay_ordersn');
            $orderid = array_column($list, 'ebay_id');

            $detailWhere = ['ebay_ordersn' => ['in', $ordersn]];
            $detailRows = $orderDetailModel->getAll($detailWhere);
            if (empty($detailRows) || count($detailRows) <= 0) continue;

            $detailList = [];
            foreach ($detailRows as $key => $value) {
                if (empty($value) && !isset($value['ebay_ordersn'])) continue;
                $detailList[$value['ebay_ordersn']][] = $value;
            }
            if (empty($detailList)) continue;

            // 查询是否存在
            $isExits = !empty($orderid) ? $model->where(['id' => ['IN', $orderid]])->column('id') : [];

            // 同步订单
            $result = [];
            foreach ($list as $key => $value) {
                // 写入数据库
                $state = (int)$this->analysisOrder($value, $detailList[$value['ebay_ordersn']] ?? [], $isExits);
                // 获取结果
                $result[$state][] = $value['ebay_id'];
            }

            $success = isset($result[1]) ? count($result[1]) : 0;
            $fail = isset($result[0]) ? count($result[0]) : 0;
            $failStr = isset($result[0]) ? implode(',', $result[0]) : '';

            echo "success: {$success}, fail: {$fail}\n";
            if (isset($result[0])) {
                echo " \r\nfaile list: {$failStr}";
            }
            echo "round " . (++$round) . " bingo\n";
            $start += $limit;
        }

        return "success\n";
    }

    /**
     * 根据时间更新退款金额
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function refundtimes()
    {
        if (empty($this->platform)) {
            return "platform is not empty";
        }

        $stime = $this->input->getArgument('stime');
        $etime = $this->input->getArgument('etime');
        $id = $this->input->getArgument('id');

        $stime = !empty($stime) ? strtotime($stime . " 00:00:00") : strtotime("-3 day");
        $etime = !empty($etime) ? strtotime($etime . " 23:59:59") : time();

        $model = new RefundOrders();

        $where = ['sys_addtime' => ['BETWEEN', [$stime, $etime]], 'platform' => $this->platform];
        if (!empty($id)) {
            $where['ebay_id'] = $id;
        }

        // 总数
        $counts = $model->where($where)->count();
        $start = 0;
        $limit = 1000;
        $round = 1;

        echo "task start, where: " . json_encode($where) . ",totals:{$counts} \n";

        $this->redis->handler()->select(REDIS_ERP);
        while ($start < $counts) {
            $list = $model->where($where)->limit($start, $limit)->field('ebay_id,refund_amount,refund_time')->select()->toArray();
            if (empty($list)) {
                $this->redis->handler()->select(REDIS_ERP5);
                return "none data\n";
            }
            // 同步订单
            $result = [];
            foreach ($list as $key => $order) {
                if (empty($order) || $order['ebay_id'] == 0) {
                    continue;
                }
                $orderModels = new OrderModel();
                $orderModels->update([
                    'type'         => 4,
                    'refund_money' => $order['refund_amount'],
                    'refund_time'  => $order['refund_time'],
                ], ['id' => $order['ebay_id']]);

                $result[1][] = $order['ebay_id'];
                // 退款订单太久远了, 订单数据需要重新更新一遍
                $changeKey = Config::get('redis.order_changelist');
                $this->redis->handler()->rpush($changeKey, $order['ebay_id']);
            }

            $success = isset($result[1]) ? count($result[1]) : 0;
            $fail = isset($result[0]) ? count($result[0]) : 0;
            $failStr = isset($result[0]) ? implode(',', $result[0]) : '';

            echo "success: {$success}, fail: {$fail}\n";
            if (isset($result[0])) {
                echo " \r\nfaile list: {$failStr}";
            }
            echo "round " . (++$round) . " bingo\n";
            $start += $limit;
        }
        $this->redis->handler()->select(REDIS_ERP5);

        return "success\n";
    }

    /**
     * 同步运行自动规则后的订单
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function rules(): string
    {
        if (empty($this->platform)) {
            return "platform is not empty";
        }
        $date = date('Ymd');
        $redisKey = sprintf(Config::get('redis.order_waritallot'), $this->platform);
        $this->redis->handler()->select(REDIS_ERP);
        for ($i = 0; $i < $this->num; $i++) {
            $ebayId[] = $this->redis->handler()->lpop($redisKey);
        }
        $this->redis->handler()->select(REDIS_ERP5);

        if (empty($ebayId)) {
            return "empty ebay_id";
        }
        $model = new \app\common\model\Order();
        $list = $model->where(['ebay_id' => ['in', $ebayId]])->field($this->alowFields)->select();
        if (empty($list)) {
            return "empty getall";
        }
        $list = $list->toArray();

        // 查询订单详情
        $ordersn = array_column($list, 'ebay_ordersn');
        $detailWhere = [
            'ebay_ordersn' => ['in', $ordersn]
        ];
        $detailRows = OrderDetail::all($detailWhere);
        if (empty($detailRows)) return 'empty detail';

        $detailList = [];
        foreach ($detailRows as $key => $value) {
            if (!empty($value)) {
                $detailList[$value['ebay_ordersn']][] = $value->toArray();
            }
        }
        if (empty($detailList)) return 'empty detail';

        // 同步订单
        $result = [];
        foreach ($list as $key => $value) {
            $state = $this->analysisOrder($value, $detailList[$value['ebay_ordersn']] ?? []);

            $result[(int)$state][] = $value['ebay_id'];
        }
        $success = isset($result[1]) ? count($result[1]) : 0;
        $fail = isset($result[0]) ? count($result[0]) : 0;
        $failStr = isset($result[0]) ? implode(',', $result[0]) : '';
        $returnStr = "success: {$success}, fail: {$fail}";
        if (isset($result[0])) {
            $returnStr .= " \r\nfaile list: {$failStr}";
        }
        return $returnStr;
    }

    /**
     * 同步已经发货的订单
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function shipped(): string
    {
        if (empty($this->platform)) {
            return "platform is not empty";
        }

        $date = date('Ymd');
        $redisKey = sprintf(Config::get('redis.order_shipped'), $this->platform, $date);
        $this->redis->handler()->select(REDIS_ERP);
        for ($i = 0; $i < $this->num; $i++) {
            $ebayId[] = $this->redis->handler()->lpop($redisKey);
        }
        $this->redis->handler()->select(REDIS_ERP5);

        if (empty($ebayId)) {
            return "empty ebay_id";
        }
        $model = new \app\common\model\Order();
        $list = $model->where(['ebay_id' => ['in', $ebayId]])->field($this->alowFields)->select();
        if (empty($list)) {
            return "empty getall";
        }
        $list = $list->toArray();

        // 查询订单详情
        $ordersn = array_column($list, 'ebay_ordersn');
        $detailWhere = [
            'ebay_ordersn' => ['in', $ordersn]
        ];
        $detailRows = OrderDetail::all($detailWhere);
        if (empty($detailRows)) return 'orderdetaul empty';

        $detailList = [];
        foreach ($detailRows as $key => $value) {
            if (empty($value)) return 'orderdetaul empty';

            $detailList[$value['ebay_ordersn']][] = $value->toArray();
        }
        if (empty($detailList)) return 'orderdetaul empty';

        // 同步订单
        $result = [];
        foreach ($list as $key => $value) {
            $state = $this->analysisOrder($value, $detailList[$value['ebay_ordersn']] ?? []);

            $result[(int)$state][] = $value['ebay_id'];
        }
        $success = isset($result[1]) ? count($result[1]) : 0;
        $fail = isset($result[0]) ? count($result[0]) : 0;
        $failStr = isset($result[0]) ? implode(',', $result[0]) : '';
        $returnStr = "success: {$success}, fail: {$fail}";
        if (isset($result[0])) {
            $returnStr .= " \r\nfaile list: {$failStr}";
        }
        return $returnStr;
    }

    /**
     * 转回收站数据更新
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function recycle()
    {
        $ebayId = [];
        $date = date('Ymd');
        $redisKey = Config::get('redis.order_recycle');

        $this->redis->handler()->select(REDIS_ERP);
        for ($i = 0; $i < $this->num; $i++) {
            $ebayId[] = $this->redis->handler()->lpop($redisKey);
        }
        $this->redis->handler()->select(REDIS_ERP5);

        if (empty($ebayId)) {
            return "empty ebay_id";
        }
        $model = new \app\common\model\Order();
        $list = $model->where(['ebay_id' => ['in', $ebayId]])->field($this->alowFields)->select();
        if (empty($list)) {
            return "empty getall";
        }
        $list = $list->toArray();
        // 同步订单
        $result = [];
        $models = new OrderModel();
        // 回收站原因
        $recyclereason = array_flip(Config::get('site.recyclereason'));
        foreach ($list as $key => $value) {
            $cancelreason = $value['cancelreason'] ?? 0;
            $saveData = [
                'status'         => 1731,
                'recycle_reason' => $recyclereason[$cancelreason] ?? 0,
                'recycle_time'   => $value['canceltime'] ?? 0, // 进回收站时间
            ];
            // 更新
            $models->update($saveData, ['id' => $value['ebay_id']]);

            $result[1][] = $value['ebay_id'];
        }
        $success = isset($result[1]) ? count($result[1]) : 0;
        $fail = isset($result[0]) ? count($result[0]) : 0;
        $failStr = isset($result[0]) ? implode(',', $result[0]) : '';
        $returnStr = "success: {$success}, fail: {$fail}";
        if (isset($result[0])) {
            $returnStr .= " \r\nfaile list: {$failStr}";
        }
        return $returnStr;
    }

    /**
     * 确认利润
     * @return string
     * @throws \think\exception\PDOException
     */
    public function finish()
    {
        if (empty($this->platform)) {
            return "platform is not empty";
        }
        $date = date('Ymd');
        $rediskey = sprintf(Config::get('redis.order_finish'), $this->platform, $date);

        $this->redis->handler()->select(REDIS_ERP);
        $getVals = $this->redis->handler()->hgetall($rediskey);
        $this->redis->handler()->select(REDIS_ERP5);

        // 获取redis数据
        if (empty($getVals)) {
            return "hgetall value empty!";
        }

        foreach ($getVals as $key => $value) {
            $state = $this->finishOrderField($key, $value, []);

            $result[(int)$state][] = $key;
            // 删除已处理的订单
            $this->redis->handler()->hdel($rediskey, $key);
        }
        $success = isset($result[1]) ? count($result[1]) : 0;
        $fail = isset($result[0]) ? count($result[0]) : 0;
        $failStr = isset($result[0]) ? implode(',', $result[0]) : '';
        $returnStr = "success: {$success}, fail: {$fail}";
        if (isset($result[0])) {
            $returnStr .= " \r\nfaile list: {$failStr}";
        }
        return $returnStr;
    }

    /**
     * 退款退货订单同步
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function refund()
    {
        if (empty($this->platform)) {
            return "platform is not empty";
        }
        $result = [];
        $redisKey = sprintf(Config::get('redis.order_refunding'), $this->platform);
        $models = new OrderModel();
        $toolsLib = \app\common\library\ToolsLib::getInstance();

        $this->redis->handler()->select(REDIS_ERP);
        for ($i = 0; $i < $this->num; $i++) {
            $order = json_decode($this->redis->handler()->lpop($redisKey), false);
            if (empty($order)) {
                continue;
            }
            if (isset($order->currency) && $order->currency == 'USD') {
                $currency = $order->currency;
                $rates = 1;
            } else {
                $orderData = $models->where(['id' => $order->ebay_id])->field($this->alowFields)->find();
                if (empty($orderData)) {
                    continue;
                }
                $currency = $orderData['currency'];
                $rates = $orderData['rates'];
            }
            // 如果有更新退款金额
            $refund_money = !empty($rates) ? $order->RefundAmount * $rates : $toolsLib->toDollar($order->RefundAmount, $currency);

            $state = OrderModel::update([
                'type'         => 4,
                'refund_money' => $refund_money,
                'refund_time'  => $order->refundtime, // 退款时间
            ], ['id' => $order->ebay_id]);

            // 退款订单太久远了, 订单数据需要重新更新一遍
            $changeKey = Config::get('redis.order_changelist');
            $this->redis->handler()->rpush($changeKey, $order->ebay_id);

            $result[(int)$state][] = $order->ebay_id;
        }
        $this->redis->handler()->select(REDIS_ERP5);

        $success = isset($result[1]) ? count($result[1]) : 0;
        $fail = isset($result[0]) ? count($result[0]) : 0;
        $failStr = isset($result[0]) ? implode(',', $result[0]) : '';
        $returnStr = "success: {$success}, fail: {$fail}";
        if (isset($result[0])) {
            $returnStr .= " \r\nfaile list: {$failStr}";
        }
        return $returnStr;
    }

    /**
     * 更新确认订单的数据
     * @param $id 订单ID
     * @param $order 订单数据
     * @return bool
     * @throws \think\exception\PDOException
     */
    private function finishOrderField($id, $order): bool
    {
        $field = [
            'profit'          => $order['profit'],           // 真实利润
            'profit_margin'   => $order['profit_margin'],    // 真实利润率
            'profit_status'   => 1,                          // 是否确认利润 1=已确认
            'profit_time'     => $order['updateprofittime'] ?? 0, // 确认利润时间
            'carrier_weight'  => $order['carrier_weight'],   // 物流称重
            'carrier_freight' => $order['carrier_freight'],  // 物流运费
        ];
        if (isset($order['ebay_id'])) {
            $field = array_merge($field, $this->getOrderForecast($id, 1));
        }
        return $this->saveOrder($id, $field, []);
    }


    /**
     * 更新全部时间
     * @param $order
     * @param $detail
     * @param $isExits
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    private function analysisOrder($order, $detail, $isExits = []): bool
    {
        if (empty($order) || empty($detail)) return false;

        // 国家二字码
        $countries = $this->redis->get(Config::get('redis.countries'));
        // 汇率
        $rate = $this->redis->get(Config::get('redis.rate'));

        // 订单类型, 根据原订单类型转换而来
        $orderType = Config::get('site.erpordertype');
        // 回收站原因
        $recyclereason = array_flip(Config::get('site.recyclereason'));

        // 如果为手工单, 并且订单类型为空，则默认为手工单
        $ebay_paystatus = $order['ebay_paystatus'] ?? '';
        if ($ebay_paystatus == 'Create') {
            if (!isset($order['ebay_ordertype']) || empty($order['ebay_ordertype'])) $order['ebay_ordertype'] = 5;
            if (!isset($orderType[$order['ebay_ordertype']])) $order['ebay_ordertype'] = 5;
        }
        // 币种
        $currency = $order['ebay_currency'];
        // 退款金额
        $refund_money = $order['RefundAmount'] ?? 0;
        // 如果汇率为空，则直接获取汇率
        if (empty($order['ordercopst'])) {
            $order['ordercopst'] = $rate[$currency] ?? 1;
        }
        // 转成美元
        if ($currency != 'USD') {
            $currency = 'USD';
            if (!empty($refund_money) && $refund_money > 0) {
                $refund_money = $refund_money * $order['ordercopst'];
            }
            $order['ebay_total'] = $order['ebay_total'] * $order['ordercopst'];
        }
        // 是否是合并产生的作废单, todo: 不能加，加了之后更新不到订单
        /*
        $ebay_combine = $order['ebay_combine'] ?? '';
        if ($ebay_combine == '1') {
            return false;
        }*/
        // 获取商品信息
        $goods = $this->getOrderGoods($detail);
        // 订单ID
        $id = $order['ebay_id'] ?? 0;     // 订单ID
        $status = $order['ebay_status'] ?? 1; // 订单状态

        // 已发货
        //if ($status == 2) {
        //    // 获取已确认利润信息
        //    $profit = $this->getOrderForecast($id, ($order['profitstatus'] ?? 0));
        //} else {
        //    // 获取预估利润相关信息
        //    $profit = $this->getOrderProfit($id, $order);
        //}

        // 统一获取预估费用
        $profit = $this->getOrderForecast($id, ($order['profitstatus'] ?? 0));
        // 销售人员
        $market = $order['market'] ?? '';
        if ($market == '0') $market = '';
        // 销售人员所在组织架构ID
        $ishide = $order['ishide'] ?? 0;

        // 订单基础数据
        $orderData = [
            'recordnumber'        => $order['recordnumber'],     // 第三方单号
            'platform'            => $order['ordertype'],        // 平台
            'platform_account'    => $order['ebay_account'],     // 平台账号
            'platform_account_id' => $order['accountid'],        // 平台帐号ID
            'type'                => $orderType[($order['ebay_ordertype'] ?? 0)] ?? 0, // 订单类型 0=正常单 1=补发单 2=退货 3=退款
            'couny'               => $order['ebay_couny'] ?? '',              // 国家二字码
            'couny_name'          => $countries[$order['ebay_couny']] ?? '',  // 国家名称
            'city'                => $order['ebay_city'] ?? '',                    // 城市
            'state'               => $order['ebay_state'] ?? '',                  // 洲
            'total'               => $this->rounds(($order['ebay_total'] ?? 0), 2),        // 总金额
            'currency'            => $currency,                               // 币种
            'shipfee'             => $order['ebay_shipfee'] ?? 0,             // 客户付的运费
            'store_id'            => $order['ebay_warehouse'] ?? 0,           // 仓库ID

            'carrier'         => $order['ebay_carrier'] ?? '',             // 物流渠道
            'carrier_company' => $this->carriers[$order['ebay_carrier'] ?? '']['company'] ?? '',    // 物流公司
            'tracknumber'     => $order['ebay_tracknumber'] ?? '',         // 跟踪号
            'store_id'        => $order['ebay_warehouse'] ?? 0,            // 仓库ID
            'location'        => $order['location'] ?? '',                 // 发货地,ebay使用
            'order_weight'    => ($order['orderweight2'] ?? 0) / 1000,     // 电子称重(KG)

            'sales_branch_id' => $ishide > 0 ? $ishide : $this->getOrderSalesBranch($id, $market, $order['ordertype']), //销售人员所在组
            'sales_user'      => trim($market),                             // 销售员
            'stock_user'      => $goods[$detail[0]['sku']]['cguser'] ?? '', // 采购员
            'develop_user'    => $goods[$detail[0]['sku']]['kfuser'] ?? '', // 开发员
            'stock_user'      => $goods[$detail[0]['sku']]['cguser'] ?? '', // 采购员
            'develop_user'    => $goods[$detail[0]['sku']]['kfuser'] ?? '', // 开发员
            'status'          => $order['ebay_status'] ?? 1,              // 状态
            'createdtime'     => $order['ebay_addtime'] ?? 0,             // 进系统时间
            'paidtime'        => $order['ebay_paidtime'] ?? 0,            // 付款时间
            'deliverytime'    => $order['scantime'] ?? 0,                 // 发货时间
            'profit_status'   => $order['profitstatus'] ?? 0,             // 是否确认利润
            'refund_time'     => $order['refundtime'] ?? 0,               // 退款时间
            'refund_money'    => $refund_money,                           // 退款金额
            'return_time'     => $order['resendtime'] ?? 0,               // 退货时间
            'recycle_time'    => $order['canceltime'] ?? 0,               // 进回收站时间
            'profit_time'     => $order['updateprofittime'] ?? 0,         // 确认利润时间
            'recycle_reason'  => $recyclereason[($order['cancelreason'] ?? 0)] ?? 0, // 进入回收站原因
        ];
        $orderData = array_merge($orderData, $profit);
        // 如果有退款时间，将订单改成退款订单
        if ($orderData['refund_time'] > 0) {
            $orderData['type'] = 4;
        }

        // 详情数据
        $detailData = [];
        $detailArr = [
            'order_id'         => $id,
            'platform'         => $orderData['platform'],
            'platform_account' => $orderData['platform_account'],
        ];

        foreach ($detail as $key => $val) {
            $sku = $val['sku'];
            $vals = [
                'location'          => $val['goods_location'] ?? '',      // 发货地(ebay使用)
                'sku'               => $sku,                              // sku编号
                'sku_type'          => $orderData['type'],                // 0=正常 1=补发 2=退货 3=退款
                'sku_combine'       => $goods[$sku]['sku_combine'] ?? 0,  // 是否是组合SKU
                'parent'            => $goods[$sku]['parent'] ?? '',      // 父SKU编号
                'category_id'       => isset($goods[$sku]['category'][0]) ? intval($goods[$sku]['category'][0]) : 0,  // SKU分类ID
                'category_child_id' => isset($goods[$sku]['category'][1]) ? intval($goods[$sku]['category'][1]) : 0,  // SKU子分类ID
                'name'              => $goods[$sku]['goods_name'] ?? '',  // sku名称
                'thumb'             => $goods[$sku]['goods_pic'] ?? '',   // sku图片
                'price'             => (boolval($val['ebay_itemprice']) ?? 0) * $order['ordercopst'],       // 单价
                'nums'              => $val['ebay_amount'] ?? 0,                                   // 数量
                'shipingfee'        => (boolval($val['shipingfee']) ?? 0) * $order['ordercopst'],           // 运费
            ];
            // 如果订单的location为空， 则取第一个sku的location
            if ($key == 0 && !empty($vals['location']) && empty($orderData['location'])) {
                $orderData['location'] = $vals['location'];
            }
            $detailData[] = array_merge($vals, $detailArr);
        }
        $orderFee = [];
        // 已发货订单获取按sku拆分的费用数据
        if ($orderData['status'] == 2) {
            $orderFee = $this->getOrderFee($id, array_merge($detailArr, [
                'platform_account_id' => $orderData['platform_account_id'],
            ]), $detailData);
        }
        return $this->saveOrder($id, $orderData, $detailData, $orderFee, $isExits);
    }

    /**
     * 获取订单费用信息
     * @param $id
     * @param $order
     * @param array $default
     * @return array
     * @throws \think\exception\DbException
     */
    private function getOrderProfit($id, $order, $default = []): array
    {
        if (!$id || $id <= 0) return $default;

        $lowprofit = Lowprofit::get(['ebay_id' => $id]);
        if (empty($lowprofit)) return $default;

        return [
            'cost'                   => $lowprofit['cost'] ?? 0,        //成本
            'estimate_profit'        => $lowprofit['profit'],           // 预估利润
            'estimate_profit_margin' => $lowprofit['profitbili'],       // 预估利润率/1000
            'package_fee'            => $lowprofit['packagecost'],      // 包材费
            'platform_fee'           => $lowprofit['optionfee'],        // 转换费
            'paypal_fee'             => $lowprofit['ppfee'],            // paypal费
            'onlinefee'              => $lowprofit['onlinefee'],        // 线上运费/尾程费
            'otherfee'               => $lowprofit['otherfee'],         // 其他费用(备用)
            'brokerage_fee'          => $lowprofit['commission'],       // 佣金
            'gross_profit'           => $lowprofit['profit'],           // 毛利
            'order_freight'          => $lowprofit['shipfee'],          // 计算运费(￥)
        ];
    }

    /**
     * 获取销售人员所在组ID
     * @param $id 订单ID
     * @param $sales 销售人员
     * @param $platform 平台
     * @return int
     * @throws \think\exception\DbException
     */
    private function getOrderSalesBranch($id, $sales, $platform): int
    {
        if (empty($sales)) return 0;

        // 从缓存表查询
        $rediskey = sprintf(Config::get('redis.organization_saleid'), $platform);
        $organize_id = $this->redis->handler()->hget($rediskey, $id);
        if ($organize_id && !empty($organize_id)) {
            return $organize_id;
        }

        // 从数据库查询，不是很准，慎用
        $organizeIds = OrganizationUser::where(['user_name' => $sales])->column('organize_id');
        if (empty($organizeIds)) {
            return 0;
        }
        // 销售员只存在一个部门时，直接返回
        if (count($organizeIds) == 1) {
            return $organizeIds[0];
        }
        // 存在多个部门的人要关联平台
        $orgData = Organization::where(['platform' => $platform, 'id' => ['IN', $organizeIds]])->column('id');
        if (!empty($orgData)) {
            return $orgData[0];
        }
        return 0;
    }

    /**
     * 获取sku费用信息
     * @param $id 订单ID
     * @param $orderData 订单数据
     * @param array $default 默认
     * @return array
     * @throws \think\exception\DbException
     */
    private function getOrderFee($id, $orderData, $default = []): array
    {
        if (!$id || $id <= 0) return $default;

        $orderFee = OrderFee::all(['ebay_id' => $id]);
        if (empty($orderFee)) return $default;

        $return = [];
        foreach ($orderFee as $value) {
            // 计算下利润
            $profit = $value->total -
                $value->cost -
                $value->packcost -
                $value->poption -
                $value->pfee -
                $value->onlinefee -
                $value->otherfee -
                $value->sfee -
                $value->shippingfee;

            $return[] = array_merge($orderData, [
                'sku'             => $value->sku,
                'location'        => $value->location,
                'store_id'        => $value->storeid,
                'total'           => $value->total,       // 金额
                'cost'            => $value->cost,        // 物品成本
                'pack_cost'       => $value->packcost,    // 包材成本
                'package_fee'     => $value->packcost,    // 包材费用
                'platform_fee'    => $value->poption,     // 转换费
                'paypal_fee'      => $value->pfee,        // Paypal费
                'onlinefee'       => $value->onlinefee,   // 线上运费/尾程费
                'otherfee'        => $value->otherfee,    // 其它费
                'brokerage_fee'   => $value->sfee,        // 成缴费/平台费
                'freight'         => $value->shippingfee, // 实际运费
                'profit'          => $profit,             // 实际利润
                'sales_label'     => $value->saleuser ?? '',      // 销售标签
                'sales_user'      => $value->sales_user ?? '',    // 销售人员
                'sales_branch_id' => $value->sales_branch_id ?? 0, //销售人员所在部门
                'stock_user'      => $value->stock_user ?? '',    // 采购人员
                'develop_user'    => $value->develop_user ?? '',  // 开发人员
                'develop_time'    => $value->develop_time ?? 0, // 开发时间
                'instore_time'    => $value->store_time ?? 0, // 首次入库时间
            ]);
        }
        // 返回费用数据
        return $return;
    }

    /**
     * 获取确认利润信息
     * @param $id 订单ID
     * @param int $profitstatus 是否确认利润
     * @param array $default
     * @return array
     * @throws \think\exception\DbException
     */
    private function getOrderForecast($id, $profitstatus = 0, $default = []): array
    {
        if (!$id || $id <= 0) return $default;

        $forecast = OrderForecast::get(['ebay_id' => $id]);
        if (empty($forecast)) return $default;
        if ($forecast['total'] <= 0) return $default;

        $default = [
            'carrier_weight'  => $forecast['ship_company_weight'] > 0 ? $forecast['ship_company_weight'] : $forecast['weight'],        // 物流重量
            'carrier_freight' => $forecast['ship_company_shipfee'] > 0 ? $forecast['ship_company_shipfee'] : $forecast['shipfee'],    // 物流运费
        ];
        if (!empty($forecast['total'])) {
            $default['total'] = $this->rounds($forecast['total'], 2);     // 总销售额
        }
        // 确认利润的运费
        $actualShipfee = !empty($forecast['actual_shipfee']) ? $this->rounds($forecast['actual_shipfee'] ?? 0, 2) : $this->rounds($forecast['shipfee'] ?? 0, 2);
        // 确认利润重量
        if (!empty($forecast['ship_company_weight'])) {
            $shipweight = $forecast['ship_company_weight'];
        } elseif (!empty($forecast['true_weight'])) {
            $shipweight = $forecast['true_weight'];
        } else {
            $shipweight = $forecast['weight'];
        }
        $total = $this->rounds($forecast['total'], 2);
        $sfee = $this->rounds($forecast['sfee'], 2);
        $pfee = $this->rounds($forecast['pfee'], 2);
        $poption = $this->rounds($forecast['poption'], 2);
        $cost = $this->rounds($forecast['cost'], 2);
        $packcost = $this->rounds($forecast['packcost'], 2);
        $onlinefee = $this->rounds($forecast['onlinefee'], 2);
        $shipfee = $this->rounds($forecast['shipfee'], 2);
        $otherfee = $this->rounds($forecast['otherfee'], 2);

        // 预估利润=总金额-成交费-payapl费-提款费(pp转换费)-物品成本-包装材料-运输成本-利润
        $estimate_profit = $total - $sfee - $pfee - $poption - $cost - $packcost - $onlinefee - $shipfee - $otherfee;
        // 真实利润=总金额-成交费-payapl费-提款费(pp转换费)-物品成本-包装材料-运输成本-利润
        $actual_profit = $total - $sfee - $pfee - $poption - $cost - $packcost - $onlinefee - $actualShipfee - $otherfee;
        // 预估利润率
        $estimate_profit_margin = $total > 0 ? ($estimate_profit / $total) * 100 : 0;
        // 真实利润率
        $actual_profit_margin = $total > 0 ? ($actual_profit / $total) * 100 : 0;

        $result = [
            'cost'                   => $cost,      //成本
            'profit'                 => $profitstatus == 1 ? $this->rounds($actual_profit ?? 0, 2) : 0,              // 真实利润
            'profit_margin'          => $profitstatus == 1 ? $this->rounds($actual_profit_margin ?? 0, 3) : 0,       // 真实利润率
            'order_weight'           => $this->rounds($forecast['weight'] ?? 0, 3),         // 出库重量
            'order_freight'          => $this->rounds($forecast['shipfee'] ?? 0, 2),        // 出库计算运费
            'carrier_weight'         => $this->rounds($shipweight ?? 0, 2) ?? 0,            // 确认物流重量
            'carrier_freight'        => $this->rounds($actualShipfee ?? 0, 2),              // 确认物流运费
            'estimate_profit'        => $estimate_profit,                                   // 预估利润
            'estimate_profit_margin' => $estimate_profit_margin,                            // 预估利润率/1000
            'package_fee'            => $packcost,       // 包材费
            'platform_fee'           => $poption,        // 转换费
            'brokerage_fee'          => $sfee,           // 佣金/平台费
            'paypal_fee'             => $pfee,           // paypal费
            'onlinefee'              => $onlinefee,      // 线上运费/尾程费
            'otherfee'               => $otherfee,       // 其他费用(备用)
        ];
        return array_merge($result, $default);
    }

    /**
     * 四舍五入
     * @param $val
     * @param int $precision
     * @return float
     */
    private function rounds($val, $precision = 2)
    {
        if (empty($val) || $val == 0) return 0;
        return floatval(round($val, $precision));
    }

    /**
     * 获取商品信息
     * @param $detail
     * @param array $default
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function getOrderGoods($detail, $default = [])
    {
        if (empty($detail)) return $default;

        $skuArr = array_column($detail, 'sku');
        array_unique($skuArr);

        // 查看缓存是否有这个数据
        $hash = Config::get('redis.order_goods');
        $rekey = md5(json_encode($skuArr));
        $cache = $this->redis->handler()->hget($hash, $rekey);
        if ($cache) return json_decode($cache, true);

        // 设置过期时间
        if (!$this->redis->handler()->exists($hash)) {
            $this->redis->handler()->expire($hash, 600);
        }

        $model = new Goods();
        $combineModel = new ProductsCombine();

        $field = 'goods_sn,goods_name,salesuser,cguser,kfuser,BtoBnumber,category_tree, goods_pic';
        $rows = $model->field($field)->where(['goods_sn' => ['in', $skuArr]])->select();

        // 如果为空，查下组合表, 这种订单全是组合产品
        if (empty($rows)) {
            $rows = $combineModel->where(['goods_sn' => ['IN', $skuArr]])->select()->toArray();
            foreach ($rows as $val) {
                // 获取组合产品名称
                $goods_name = !empty($val->notes) ? $val->notes : $val->goods_sncombine;

                $default[$val->goods_sn] = [
                    'cguser'      => $val->cguser,
                    'kfuser'      => $val->kfuser,
                    'goods_name'  => $goods_name,
                    'sku_combine' => 1,
                    'goods_pic'   => '',
                    'category'    => [],
                ];
            }
            $this->redis->handler()->hset($hash, $rekey, json_encode($default));
            return $default;
        }

        // 遍历sku
        $rowsArr = [];
        foreach ($rows as $val) {
            $rowsArr[$val->goods_sn] = $val->toArray();
        }
        foreach ($skuArr as $sku) {
            $skuData = [];
            if (isset($rowsArr[$sku])) {
                $skuData = [
                    'cguser'      => $rowsArr[$sku]['cguser'],
                    'kfuser'      => $rowsArr[$sku]['kfuser'],
                    'goods_name'  => $rowsArr[$sku]['goods_name'],
                    'parent'      => trim($rowsArr[$sku]['BtoBnumber']),
                    'goods_pic'   => $rowsArr[$sku]['goods_pic'] ?? '',
                    'category'    => explode(",", $rowsArr[$sku]['category_tree']),
                    'sku_combine' => 0,
                ];
            } else {
                $combine = $combineModel->where(['goods_sn' => $sku])->find();
                if (!empty($combine)) {
                    // 获取组合产品名称
                    $goods_name = !empty($combine['notes']) ? $combine['notes'] : $combine['goods_sncombine'];
                    $skuData = [
                        'cguser'      => $combine['cguser'],
                        'kfuser'      => $combine['kfuser'],
                        'goods_name'  => $goods_name,
                        'sku_combine' => 1,
                        'goods_pic'   => '',
                        'category'    => [],
                    ];
                }
            }
            $default[$sku] = $skuData;
        }
        $this->redis->handler()->hset($hash, $rekey, json_encode($default));
        return $default;
    }

    /**
     * 保存订单数据
     * @param $id 订单ID
     * @param $orderData 订单数据
     * @param $detailData 详情数据
     * @param $feeData 费用数据
     * @param $isExits 是否存在
     * @return bool
     * @throws \think\exception\PDOException
     */
    private function saveOrder($id, $orderData, $detailData, $feeData = [], $isExits = []): bool
    {
        // 总金额转为美元
        if (isset($orderData['currency']) && $orderData['currency'] != 'USD') {
            $rates = $orderData['ordercopst'] ?? '';
            $toolsLib = \app\common\library\ToolsLib::getInstance();
            // 如果有更新订单总金额
            if (isset($orderData['total'])) {
                $orderData['total'] = !empty($rates) ? $orderData['total'] * $rates : $toolsLib->toDollar($orderData['total'], $orderData['currency']);
            }
            // 如果有更新退款金额
            if (isset($orderData['refund_money'])) {
                $orderData['refund_money'] = !empty($rates) ? $orderData['refund_money'] * $rates : $toolsLib->toDollar($orderData['refund_money'], $orderData['currency']);
            }
            $orderData['currency'] = 'USD';
        }

        $model = new OrderModel();
        $detailModel = new OrderDetailModel();
        $detailFeeModel = new OrderDetailFeeModel();

        $model->startTrans();
        try {
            $detailSave = true;
            $detailFeeSave = true;
            $orderSave = true;

            if (!empty($isExits)) {
                $exits = in_array($id, $isExits);
            } else {
                $exits = $model->isExists(['id' => $id]);
            }
            if ($exits) // 如何存在更新数据
            {
                // 如果更新状态是负数，则删除本订单
                if (isset($orderData['status']) && $orderData['status'] < 0) {
                    $model->where(['id' => $id])->delete();
                    $detailModel->where(['order_id' => $id])->delete();
                    $model->commit();
                    return true;
                }
                $orderSave = $model->where(['id' => $id])->update($orderData);
                if (!empty($detailData)) {
                    $newSku = array_column($detailData, 'sku');
                    $oldSku = $detailModel->getSkuByWhere(['order_id' => $id]);
                    $delSku = array_diff($oldSku, $newSku);

                    foreach ($detailData as $dk => $dv) {
                        $map = ['order_id' => $id, 'sku' => $dv['sku']];
                        if ($detailModel->isExists($map)) {
                            $status = $detailModel->where($map)->update($dv);
                        } else {
                            $status = $detailModel->insert($dv);
                        }
                        if ($status === false) {
                            $detailSave = false;
                        }
                    }

                    // 删除不存在的SKU
                    if (!empty($delSku)) {
                        $delWhere = ['order_id' => $id, 'sku' => ['in', $delSku]];
                        $delSave = $detailModel->where($delWhere)->delete();
                        if ($delSave === false) {
                            $detailSave = false;
                        }
                    }
                }
            } else {
                // 如果更新状态是负数，则删除本订单
                if (isset($orderData['status']) && $orderData['status'] < 0) {
                    $model->commit();
                    return true;
                }
                $orderData['id'] = $id;
                $orderSave = $model->insert($orderData);
                if (!empty($detailData)) {
                    $detailSave = $detailModel->insertAll($detailData);
                }
            }

            if (!empty($feeData)) {
                foreach ($feeData as $fee) {
                    if (empty($fee)) continue;
                    $map = [
                        'order_id' => $id,
                        'sku'      => $fee['sku'],
                    ];
                    if ($detailFeeModel->isExists($map)) {
                        $detailFeeModel->where($map)->update($fee);
                        $upFee = true;
                    } else {
                        $upFee = $detailFeeModel->insert($fee);
                    }
                    if ($upFee === false) {
                        $detailFeeSave = false;
                    }
                }
            }

            if ($orderSave !== false && $detailSave !== false) {
                $model->commit();
                return true;
            } else {
                $model->rollback();
                Log::error($model->getError());
                return false;
            }
        } catch (\Exception $e) {
            $model->rollback();
            Log::error($e->getMessage());
            return false;
        }
    }
}
