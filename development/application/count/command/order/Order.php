<?php

namespace app\count\command\order;

use think\Config;
use think\console\Input;
use app\count\model\Task;
use think\console\Output;
use think\cache\driver\Redis;
use app\count\model\OrderSales;
use app\common\library\ToolsLib;
use app\count\model\OrderDetail;
use app\count\model\OrderSeller;
use app\count\library\sku\SkuLib;
use app\count\model\OrderDetailFee;
use app\count\model\OrderSellerAvg;
use app\count\library\sku\NewSkuLib;
use app\count\model\OrderAccountAvg;
use \app\count\model\Sku as SkuModel;
use app\count\library\order\OrderLib;
use app\count\model\OrderSellerTarget;
use app\count\model\Order as OrderModel;

class Order
{
    /**
     * 从队列里获取日期执行
     * @author kevin
     * @param Input $input 输入
     * @param Output $output 输出
     * @return bool
     */
    public function runQueue(Input $input, Output $output)
    {
        $options = $input->getOptions();
        $queue = $options['queue'] ?? 'status';

        // $redisKeys = "command:order:queue:" . $queue;
        $redisKeys = sprintf(Config::get('redis.command_order_queue'), $queue);
        $redis = new Redis(Config::get('redis'));

        // 判断队列是否存在
        if (!$redis->handler()->exists($redisKeys)) {
            $output->writeln("不存在队列: {$redisKeys}");
            return false;
        }
        // 获取队列里的日期
        $blpop = $redis->handler()->blpop($redisKeys, 1);
        $day = is_array($blpop) ? $blpop[1] : $blpop;
        if (empty($day)) {
            $output->writeln("日期为空: {$day}");
            return false;
        }
        // 执行方法
        $function = "_count" . ucfirst($queue);
        if (!in_array($function, get_class_methods(__CLASS__))) {
            $output->writeln("不存在执行方法: {$function}");
            return false;
        }
        // sku销售额分开执行
        // if ($function == '_countSkuSale') {
        //     // sku需要运行的动作
        //     $runOnlys = ['sku_date', 'sku_date2', 'sku_account', 'sku_seller', 'sku_country', 'sku_cat', 'sku_store', 'sku_developer'];
        //     $action = 'countSaleAllDays';

        //     foreach ($runOnlys as $runOnly) {
        //         $this->toSystem($day, $action, $runOnly);
        //     }
        //     return true;
        // }

        $input->setOption('day', $day);
        $this->$function($input, $output);
        return true;
    }

    /**
     * 统计订单状态（多天）
     * php7 think  order -m Order -a countStatusAllDays --start=2018-09-24 --end=2018-10-01
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-09 10:39:08
     */
    public function countStatusAllDays(Input $input, Output $output)
    {
        $options = $input->getOptions();

        $start = $options['start'] ?? date('Y-m-d', strtotime('-1 month'));
        $end = $options['end'] ?? date('Y-m-d', strtotime('-1 day'));

        for ($i = strtotime($start); $i <= strtotime($end); $i += 86400) {
            $day = date('Y-m-d', $i);
            $input->setOption('day', $day);
            echo "$day\n";
            $this->_countStatus($input, $output);
        }
    }


    /**
     * 统计uptime 等于 今天 的订单 (不需要参数)
     * (php7 think  order -m Order -a countStatus --test=1)
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-09 02:34:59
     */
    public function countStatus(Input $input, Output $output)
    {
        // 上一次运行该脚本的时间（放到redis里面）
        // todo: 如果上次运行该脚本时间是 今天，那么统计之后的变化。这样避免了重复的运行不需要的统计日期
        $last_count_time = ToolsLib::getInstance()->getRedis()->get('count:count_status_time');
        ToolsLib::getInstance()->getRedis()->set('count:count_status_time', date('Y-m-d H:i:s'));

        echo "last run time: {$last_count_time}\n";

        // todo: 找出需要重新统计的 日期, 否则统计 今天
        if ($last_count_time && strtotime($last_count_time) > strtotime(date('Y-m-d'))) $where = ['uptime' => [['EGT', $last_count_time], ['LT', date('Y-m-d', strtotime('+1 day'))]]];
        else $where = ['uptime' => [['EGT', date('Y-m-d')], ['LT', date('Y-m-d', strtotime('+1 day'))]]];

        $where = ['uptime' => [['EGT', strtotime('-10 day')], ['LT', date('Y-m-d', strtotime('+1 day'))]]];

        $field = 'DISTINCT DATE(FROM_UNIXTIME(createdtime)) AS day';

        $data = OrderLib::getInstance()->orderModel->field($field)->where($where)->select()->toArray();
        if (empty($data)) {
            die("no new data!");
        }
        $days = array_column($data, 'day');

        if (!$days) die("no new data!");

        echo "--------------订单的入库日期如下---------------\n";
        echo implode(', ', $days) . "\n";
        rsort($days);

        foreach ($days as $day) {
            // 当更新天数大于$max天时就不更新了
            $max = 90;
            if (count($days) > $max && time() - strtotime($day) > ($max * 24 * 3600)) {
                continue;
            }
            // 启动新的进程处理
            $this->toQueue($day, 'status', $output);

            //$input->setOption('day', $day);
            //$this->_countStatus($input, $output);
        }
    }

    /**
     * 单个日期执行
     * @param $day 日期
     * @param $output 输出
     */
    private function toQueue($day, $action, $output)
    {
        $skuSaleKeys = sprintf(Config::get('redis.command_skusale'), date('Ymd'), $action);
        $redisKeys = sprintf(Config::get('redis.command_order_queue'), $action);
        $redis = new Redis(Config::get('redis'));

        // 判断是否存在
        $isExits = $redis->handler()->exists($skuSaleKeys);

        // SKU销售额计算不要这么快，一个小时一次就行了
        if ($action == 'skuSale') {
            if ($redis->handler()->sismember($skuSaleKeys, $day)) {
                if (!$isExits) {
                    $redis->handler()->expire($skuSaleKeys, (6 * 60 * 60));
                }
                return true;
            }
        }

        // 如果已经存在队列里了，就不加入了
        $redisList = $redis->handler()->lrange($redisKeys, 0, -1);
        if (!empty($redisList)) {
            if (!in_array($day, $redisList)) $redisList[] = $day;
            rsort($redisList);
        } else {
            $redisList[] = $day;
        }
        // 删除重新放入
        $redis->handler()->del($redisKeys);
        foreach ($redisList as $days) {
            if ($action == 'skuSale') {
                $redis->handler()->sadd($skuSaleKeys, $days);
                if (!$isExits) {
                    $redis->handler()->expire($skuSaleKeys, (6 * 60 * 60));
                }
            }
            $redis->handler()->rpush($redisKeys, $days);
        }
        return true;
    }

    /**
     * 单个日期执行
     * @param $day 日期
     * @param $output 输出
     * @param string $runOnly 运行类型
     * @return void
     */
    private function toSystem($day, $action, $runOnly = null)
    {
        // 第二方案, 启用多个系统进程, 弃用
        $path = dirname(APP_PATH);
        $php = PHP_BIN;
        $shell = "{$php} think order -m Order -a {$action} --start={$day} --end={$day}";
        if (!empty($runOnly)) {
            $shell .= " --run-only={$runOnly}";
        }
        $shell .= " > /dev/null 2>&1 &";
        system($shell);
    }

    /**
     * 统计指定 创建 时间 的 订单状态
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-09-18 03:22:05
     */
    private function _countStatus(Input $input, Output $output)
    {
        $options = $input->getOptions();
        $redis = new Redis(Config::get('redis'));

        $day = $options['day'] ?? date('Y-m-d', strtotime('-1 day'));

        // 以一天为统计
        $start_time = strtotime($day . ' 00:00:00');
        $end_time = strtotime($day . ' 23:59:59');

        $output->writeln("-----------------start statistics {$day}------------------");

        // 总订单数
        $where_order_total = ['createdtime' => [['EGT', $start_time], ['ELT', $end_time]]];
        // 未发货
        $where_order_unsend = array_merge($where_order_total, [
            'deliverytime' => 0,
            'status'       => [['GT', 0], ['NEQ', 1731], 'and'],
        ]);
        // 已发货
        $where_order_sended = array_merge($where_order_total, [
            'deliverytime' => ['GT', 0],
        ]);
        // 已完成
        $where_order_finish = array_merge($where_order_total, [
            'deliverytime'  => ['GT', 0],
            'profit_status' => 1,
        ]);
        // 已退款 用refund_time 代替 createdtime
        $where_order_refund = array_merge(['refund_time' => [['EGT', $start_time], ['ELT', $end_time]]], [
            'type' => 4,
        ]);
        // 已退货 用return_time 代替 createdtime
        $where_order_return = array_merge(['return_time' => [['EGT', $start_time], ['ELT', $end_time]]], [
            'type' => 7,
        ]);
        // 已重发
        $where_order_resend = array_merge($where_order_total, [
            'type' => 3,
        ]);
        // 手工转回收站
        $where_order_cancel_manual = array_merge($where_order_total, [
            'status'         => 1731,
            'recycle_reason' => ['GT', 0]
        ]);
        // 系统转回收站
        $where_order_cancel_sys = array_merge($where_order_total, [
            'status'         => 1731,
            'recycle_reason' => 0
        ]);
        // 总发货数: 发货时间是今天，但订单的创建时间不是今天
        $where_today_ships = [
            'deliverytime' => [['EGT', $start_time], ['ELT', $end_time]],
            'store_id'     => 196, // 只计算1号仓
        ];

        $total_data = OrderLib::getInstance()->getStatusTotalInfo($where_order_total);
        $unsend_total_data = OrderLib::getInstance()->getStatusTotalInfo($where_order_unsend);
        $sended_total_data = OrderLib::getInstance()->getStatusTotalInfo($where_order_sended);
        $finish_total_data = OrderLib::getInstance()->getStatusTotalInfo($where_order_finish);
        $refund_total_data = OrderLib::getInstance()->getStatusTotalInfo($where_order_refund);
        $return_total_data = OrderLib::getInstance()->getStatusTotalInfo($where_order_return);
        $resend_total_data = OrderLib::getInstance()->getStatusTotalInfo($where_order_resend);
        $cancel_manual_total_data = OrderLib::getInstance()->getStatusTotalInfo($where_order_cancel_manual);
        $cancel_sys_total_data = OrderLib::getInstance()->getStatusTotalInfo($where_order_cancel_sys);
        $today_ship_total_data = OrderLib::getInstance()->getStatusTotalInfo($where_today_ships);

        // todo:保存订单状态
        if ($total_data['total']) {
            // 循环所有账户，而不是 有统计数据的账户，因可以将原有的数据 更新为0
            $account_list = ToolsLib::getInstance()->getAllAccounts();

            // todo: 一次性查出所有的 账号 当前对应的记录数 (优化运行速度)
            $_where_group = ['year' => date('Y', strtotime($day)), 'month' => date('m', strtotime($day)), 'days' => date('d', strtotime($day))];
            $tmp_group_list = OrderLib::getInstance()->orderStatusModel->field('platform_account,platform_account_id, COUNT(*) as count')->where($_where_group)->group('platform_account_id')->select()->toArray();
            $count_group = [];
            foreach ($tmp_group_list as $group_info) {
                $count_group[$group_info['platform_account_id']] = $group_info['count'];
            }

            // TODO 针对 deadlock 的解决方案： 使用 【主键】 或者 【索引】, 一次性查出所有 ID
            $tmp_data = OrderLib::getInstance()->orderStatusModel->field('id, platform, platform_account')->where($_where_group)->select()->toArray();
            $id_map = [];
            foreach ($tmp_data as $v) {
                $_tmp_key = "{$day}_{$v['platform']}_{$v['platform_account']}";
                $id_map[$_tmp_key] = $v['id'];
            }

            foreach ($account_list as $account_info) {
                $platform = $account_info['platform'];
                $account_id = $account_info['id'];
                $account_name = trim($account_info['ebay_account']);
                // $_where       = array_merge($_where_group, ['platform_account_id' => $account_id]);
                $count = isset($count_group[$account_id]) ? $count_group[$account_id] : 0;

                // 假如数据库中不存在记录  而且  统计数据不存在，下一个循环（因为有count > 0 时，可以更新为0）
                if ($count == 0 && !isset($total_data['total_account_id_group'][$account_id]) && !isset($today_ship_total_data['total_account_id_group'][$account_id])) {
                    $output->writeln(sprintf("continue:%s, count: %s", $account_name, $count));
                    continue;
                }

                $add_data = [
                    'year'                => date('Y', strtotime($day)),
                    'month'               => date('m', strtotime($day)),
                    'days'                => date('d', strtotime($day)),
                    'platform_account'    => $account_name,
                    'platform_account_id' => $account_id,
                    'platform'            => $platform,
                    'datetime'            => strtotime($day),
                ];

                $add_data['totals'] = $total_data['total_account_id_group'][$account_id]['account_total'] ?? 0;
                $add_data['noships'] = $unsend_total_data['total_account_id_group'][$account_id]['account_total'] ?? 0;
                $add_data['ships'] = $sended_total_data['total_account_id_group'][$account_id]['account_total'] ?? 0;
                $add_data['overs'] = $finish_total_data['total_account_id_group'][$account_id]['account_total'] ?? 0;
                $add_data['refunds'] = $refund_total_data['total_account_id_group'][$account_id]['account_total'] ?? 0;
                $add_data['returns'] = $return_total_data['total_account_id_group'][$account_id]['account_total'] ?? 0;
                $add_data['resends'] = $resend_total_data['total_account_id_group'][$account_id]['account_total'] ?? 0;
                $add_data['recycles'] = $cancel_manual_total_data['total_account_id_group'][$account_id]['account_total'] ?? 0;
                $add_data['recycles_system'] = $cancel_sys_total_data['total_account_id_group'][$account_id]['account_total'] ?? 0;
                $add_data['totals_ships'] = $today_ship_total_data['total_account_id_group'][$account_id]['account_total'] ?? 0;

                // todo: 添加统计数据
                $_action_str = $count > 0 ? 'update' : 'add';
                if ($count > 0) {
                    $_tmp_key = "{$day}_{$platform}_{$account_name}";
                    $_tmp_id = $id_map[$_tmp_key];
                    $_tmp_where = ['id' => $_tmp_id];

                    $ret = OrderLib::getInstance()->orderStatusModel->where($_tmp_where)->update($add_data);
                    $ret = ($ret === false) ? false : true;
                } else {
                    $ret = OrderLib::getInstance()->orderStatusModel->insert($add_data);
                }
                $output->writeln($ret ? "{$_action_str} statistics success 【{$account_name}】" : "{$_action_str} statistics fail 【{$account_name}】");
            }
        }


        // todo: 保存 不同日期，订单类型，平台，条件 的 类型订单数
        if ($total_data['total']) {
            $_total_order_type_data = OrderLib::getInstance()->getOrderTypeTotalInfo($where_order_total);
            $_unsend_order_type_data = OrderLib::getInstance()->getOrderTypeTotalInfo($where_order_unsend);
            $_sended_order_type_data = OrderLib::getInstance()->getOrderTypeTotalInfo($where_order_sended);
            $_finish_order_type_data = OrderLib::getInstance()->getOrderTypeTotalInfo($where_order_finish);
            $_refund_order_type_data = OrderLib::getInstance()->getOrderTypeTotalInfo($where_order_refund);

            // todo: 一次性查出所有的条件对应当前对应的记录数 (优化运行速度)
            $_where_group = ['year' => date('Y', strtotime($day)), 'month' => date('m', strtotime($day)), 'days' => date('d', strtotime($day))];
            $tmp_group_list = OrderLib::getInstance()->orderStatusTypeModel->field('type, platform, COUNT(*) as count')->where($_where_group)->group('type, platform')->select()->toArray();

            $count_group = [];
            foreach ($tmp_group_list as $group_info) {
                $count_group["{$group_info['platform']}_{$group_info['type']}"] = $group_info['count'];
            }

            // TODO 针对 deadlock 的解决方案： 使用 【主键】 或者 【索引】, 一次性查出所有 ID
            $tmp_data = OrderLib::getInstance()->orderStatusTypeModel->field('id, type, platform')->where($_where_group)->select()->toArray();
            $id_map = [];
            foreach ($tmp_data as $v) {
                $_tmp_key = "{$day}_{$v['type']}_{$v['platform']}";
                $id_map[$_tmp_key] = $v['id'];
            }

            // paltform， 与type 的所有 组合
            $platform_ordertype_list = $this->_get_platform_type_list();

            foreach ($platform_ordertype_list as $value) {
                $platform = $value['platform'];
                $order_type = $value['order_type'];
                $_count_key = "{$platform}_{$order_type}";
                $count = isset($count_group[$_count_key]) ? $count_group[$_count_key] : 0;

                $_where = array_merge($_where_group, ['platform' => $platform, 'type' => $order_type]);
                // 假如数据库中不存在记录  而且  统计数据不存在，下一个循环（因为有count > 0 时，可以更新为0）
                if ($count == 0 && !isset($_total_order_type_data[$_count_key])) continue;

                $add_data = [
                    'year'     => date('Y', strtotime($day)),
                    'month'    => date('m', strtotime($day)),
                    'days'     => date('d', strtotime($day)),
                    'platform' => $platform,
                    'type'     => $order_type,
                    'datetime' => strtotime($day),
                ];
                $add_data['totals'] = $_total_order_type_data[$_count_key]['order_count'] ?? 0;
                $add_data['noships'] = $_unsend_order_type_data[$_count_key]['order_count'] ?? 0;
                $add_data['ships'] = $_sended_order_type_data[$_count_key]['order_count'] ?? 0;
                $add_data['overs'] = $_finish_order_type_data[$_count_key]['order_count'] ?? 0;
                $add_data['refunds'] = $_refund_order_type_data[$_count_key]['order_count'] ?? 0;

                // todo: 添加统计数据
                $_action_str = $count > 0 ? 'update' : 'add';

                if ($count > 0) {
                    $_tmp_key = "{$day}_{$order_type}_{$platform}";
                    $_tmp_id = $id_map[$_tmp_key];
                    $_tmp_where = ['id' => $_tmp_id];

                    $ret = OrderLib::getInstance()->orderStatusTypeModel->where($_tmp_where)->update($add_data);
                    $ret = ($ret === false) ? false : true;
                } else {
                    $ret = OrderLib::getInstance()->orderStatusTypeModel->insert($add_data);
                }
                $output->writeln($ret ? "{$_action_str} status type success 【{$_count_key}】" : "{$_action_str} status type fail 【{$_count_key}】");
            }
        }
        $output->writeln("-----------------finish statistics------------------");
    }


    /**
     * 获取所有  平台 和 订单类型 的 所有组合 (大概 130 个)
     * 因为统计，订单状态类型，不同平台的数量时需要用到
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-11-05 05:13:44
     */
    private function _get_platform_type_list()
    {
        $all_platform = ToolsLib::getInstance()->getPlatformList();
        $all_order_type = array_keys(ToolsLib::getInstance()->getOrderTypeList());

        $ret_data = [];
        foreach ($all_platform as $value) {
            foreach ($all_order_type as $v) {
                $ret_data[] = ['platform' => $value, 'order_type' => $v];
            }
        }

        return $ret_data;
    }


    /**
     * 统计销售额（多天）
     * @cmd: php7 think  order -m Order -a countSaleAllDays --start=2018-10-09 --end=2018-10-09
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-09 03:48:16
     */
    public function countSaleAllDays(Input $input, Output $output)
    {
        $options = $input->getOptions();

        $start = $options['start'] ?? date('Y-m-d', strtotime('-1 month'));
        $end = $options['end'] ?? date('Y-m-d', strtotime('-1 day'));

        for ($i = strtotime($start); $i <= strtotime($end); $i += 86400) {
            $day = date('Y-m-d', $i);
            $input->setOption('day', $day);
            echo "$day\n";
            $this->_countSale($input, $output);
        }
    }


    /**
     * 统计uptime 等于 今天 销售额 (大概每小时运行一次)
     * @cmd: php7 think  order -m Order -a countSale
     * @description: erp_order 中的订单可能会进行更新，比如更新成为回收站订单，那么我们就要删除这些销售数据，这时订单的uptime 会发生变化，且uptime等于今天，所以我们找出uptime 等于今天的订单，然后根据create_time 的日期 来更新销售数据
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-09 04:21:22
     */
    public function countSale(Input $input, Output $output)
    {
        // 上一次运行该脚本的时间（放到redis里面）
        // todo: 如果上次运行该脚本时间是 今天，那么统计之后的变化。这样避免了重复的运行不需要的日期
        $last_count_time = ToolsLib::getInstance()->getRedis()->get('count:count_sale_time');
        ToolsLib::getInstance()->getRedis()->set('count:count_sale_time', date('Y-m-d H:i:s'));

        echo "last run time: {$last_count_time}\n";

        // todo: 找出需要重新统计的 日期
        if ($last_count_time && strtotime($last_count_time) > strtotime(date('Y-m-d'))) {
            $where = ['uptime' => [['EGT', $last_count_time], ['LT', date('Y-m-d', strtotime('+1 day'))]]];
        } else {
            $where = ['uptime' => [['EGT', date('Y-m-d')], ['LT', date('Y-m-d', strtotime('+1 day'))]]];
        }

        $field = 'DISTINCT DATE(FROM_UNIXTIME(createdtime)) AS day';

        // 重新统计的日期
        $need_to_update_days = OrderLib::getInstance()->orderModel->field($field)->where($where)->select()->toArray();

        echo "--------------订单的入库日期如下---------------\n";
        echo implode(', ', array_column($need_to_update_days, 'day')) . "\n";

        foreach ($need_to_update_days as $d) {
            $day = $d['day'];
            // 当更新天数大于$max天时就不更新了
            $max = 90;
            if (count($need_to_update_days) > $max && time() - strtotime($day) > ($max * 24 * 3600)) {
                continue;
            }
            // 启动新的进程处理
            $this->toQueue($day, 'sale', $output);
            $this->toQueue($day, 'skuSale', $output);

            //$input->setOption('day', $day);
            //echo "$day\n";
            //$this->_countSale($input, $output);
        }
    }

    /**
     * 统计指定 创建 时间 销售额
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-09-18 04:22:34
     */
    private function _countSale(Input $input, Output $output)
    {
        $options = $input->getOptions();
        $day = $options['day'] ?? date('Y-m-d', strtotime('-1 day'));
        $start_time = strtotime($day . ' 00:00:00');
        $end_time = strtotime($day . ' 23:59:59');

        $output->writeln("-----------------start {$day} statistics------------------");

        $where_order_total = [
            'o.createdtime' => [['EGT', $start_time], ['ELT', $end_time]],
            'o.status'      => ['IN', $this->_getValidSaleOrderStatus()],
            'o.type'        => ['NOT IN', [3, 8]],
        ];
        if (!empty($options['run-only'])) {
            switch ($options['run-only']) {
                case 'account':
                    $this->_countAccountSale($input, $output);
                    break;
                case 'seller':
                    $this->_countSellerSale($input, $output);
                    break;
                case 'store':
                    $this->_countStoreSale($input, $output);
                    break;
                case 'location':
                    $this->_countLocationSale($input, $output);
                    break;
                case 'sku':
                    $this->_countSkuSale($input, $output);
                    break;
                // 将sku 分开运行
                case 'sku_date':
                    $this->_countSkuDateSale($start_time, $end_time, $day, $where_order_total);
                    break;
                case 'sku_date2':
                    $this->_countSkuDateSale2($start_time, $end_time, $day, $where_order_total);
                    break;
                case 'sku_cat':
                    $this->_countSkuCatSale($start_time, $end_time, $day, $where_order_total);
                    break;
                case 'sku_account':
                    $this->_countSkuAccount($start_time, $end_time, $day, $where_order_total);
                    break;
                case 'sku_seller':
                    $this->_countSkuSeller($start_time, $end_time, $day, $where_order_total);
                    break;
                case 'sku_developer':
                    $this->_countSkuDeveloper($start_time, $end_time, $day, $where_order_total);
                    break;
                case 'sku_country':
                    $this->_countSkuCountry($start_time, $end_time, $day, $where_order_total);
                    break;
                case 'sku_store':
                    $this->_countSkuStore($start_time, $end_time, $day, $where_order_total);
                    break;
            }
        } else {
            $this->_countAccountSale($input, $output);
            $this->_countSellerSale($input, $output);
            $this->_countStoreSale($input, $output);
            $this->_countLocationSale($input, $output);
        }

        $output->writeln("-----------------finish {$day} statistics------------------");
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
     * 统计平台账号的销售额
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-09-18 03:36:48
     */
    private function _countAccountSale(Input $input, Output $output)
    {
        $options = $input->getOptions();

        $day = $options['day'] ?? date('Y-m-d', strtotime('-1 day'));

        $start_time = strtotime($day . ' 00:00:00');
        $end_time = strtotime($day . ' 23:59:59');

        // 订单表的 type 和 status 其实是差不多的， type 对 status 进行了 优化
        $where_order_total = [
            'createdtime' => [['EGT', $start_time], ['ELT', $end_time]],
            'status'      => ['IN', $this->_getValidSaleOrderStatus()],
            // 'type'        => ['NEQ', 3],
            'type'        => ['NOT IN', [3, 8]], // 排除礼物单销售额统计
        ];

        $where_order_refund = array_merge($where_order_total, [
            'type' => ['IN', [4, 7]],
        ]);

        $where_order_cancel = array_merge($where_order_total, [
            'status' => 1731,
        ]);

        $total_data = OrderLib::getInstance()->getAccountSaleTotalInfo($where_order_total);
        $refund_total_data = OrderLib::getInstance()->getAccountSaleTotalInfo($where_order_refund);
        $cancel_total_data = OrderLib::getInstance()->getAccountSaleTotalInfo($where_order_cancel);

        // 有符合订单条件的账户列表
        $_order_account_list = array_keys($total_data['total_account_group']);

        $all_accounts = ToolsLib::getInstance()->getAllAccounts(2);

        // todo: 一次性查出所有的 账号 当前对应的记录数 (优化运行速度)
        $_where_group = ['year' => date('Y', strtotime($day)), 'month' => date('m', strtotime($day)), 'days' => date('d', strtotime($day))];
        $tmp_group_list = OrderLib::getInstance()->orderSalesModel->field('platform_account, COUNT(*) as count')->where($_where_group)->group('platform_account')->select()->toArray();
        $count_group = [];
        foreach ($tmp_group_list as $group_info) {

            $count_group[$group_info['platform_account']] = $group_info['count'];
        }

        // TODO 针对 deadlock 的解决方案： 使用 【主键】 或者 【索引】, 一次性查出所有 ID
        $tmp_data = OrderLib::getInstance()->orderSalesModel->field('id, platform, platform_account')->where($_where_group)->select()->toArray();
        $id_map = [];
        foreach ($tmp_data as $v) {
            $_tmp_key = "{$day}_{$v['platform']}_{$v['platform_account']}";
            $id_map[$_tmp_key] = $v['id'];
        }


        // 有销售统计的账户
        $_sales_account_list = array_keys($count_group);

        /************************************************
         * bug： 这里有一个bug，假如账号对应的所有订单，都被拉进回收站了，那么这个账号，就不会出现在 total_data 这个变量中，导致不会更新统计数据。
         * 情景1：用户将10-09日 账户为xxx的所有订单拉进回收站，那么xxx的销售统计就不会被更新。本该被更新为0，但实际上一直不变
         * 解决：使用union_account_list
         ************************************************/

        $union_account_list = array_unique(array_merge($_order_account_list, $_sales_account_list));

        $is_success = true;
        foreach ($union_account_list as $account) {
            if (empty($all_accounts[$account])) continue;

            $_platform = $all_accounts[$account]['platform'];
            // $_where    = array_merge($_where_group, ['platform' => $_platform, 'platform_account' => $account]);

            $count = $count_group[$account] ?? 0;

            $is_edit = $count > 0 ? true : false;

            // 假如数据库中不存在记录  而且  统计数据不存在，下一个循环（因为有count > 0 时，可以更新为0）
            if ($count == 0 && !isset($total_data['total_account_group'][$account])) continue;

            if ($is_edit) {
                $add_data = [
                    'totals'         => $total_data['total_account_group'][$account]['account_total'] ?? 0,
                    'sales'          => $total_data['total_account_group'][$account]['sale_total'] ?? 0,
                    'refunds_count'  => $refund_total_data['total_account_group'][$account]['account_total'] ?? 0,
                    'refunds'        => $refund_total_data['total_account_group'][$account]['sale_total'] ?? 0,
                    'recycles_count' => $cancel_total_data['total_account_group'][$account]['account_total'] ?? 0,
                    'recycles'       => $cancel_total_data['total_account_group'][$account]['sale_total'] ?? 0,
                    'datetime'       => strtotime($day)
                ];
            } else {
                $add_data = [
                    'year'             => date('Y', strtotime($day)),
                    'month'            => date('m', strtotime($day)),
                    'days'             => date('d', strtotime($day)),
                    'platform'         => $_platform,
                    'platform_account' => $account,
                    'totals'           => $total_data['total_account_group'][$account]['account_total'] ?? 0,
                    'sales'            => $total_data['total_account_group'][$account]['sale_total'] ?? 0,
                    'refunds_count'    => $refund_total_data['total_account_group'][$account]['account_total'] ?? 0,
                    'refunds'          => $refund_total_data['total_account_group'][$account]['sale_total'] ?? 0,
                    'recycles_count'   => $cancel_total_data['total_account_group'][$account]['account_total'] ?? 0,
                    'recycles'         => $cancel_total_data['total_account_group'][$account]['sale_total'] ?? 0,
                    'datetime'         => strtotime($day)
                ];
            }


            $_action_str = $is_edit ? 'update' : 'add';
            if ($is_edit) {
                $_tmp_key = "{$day}_{$_platform}_{$account}";
                $_tmp_id = $id_map[$_tmp_key];
                $_tmp_where = ['id' => $_tmp_id];

                $ret = OrderLib::getInstance()->orderSalesModel->where($_tmp_where)->update($add_data);
                $ret = ($ret === false) ? false : true;
            } else {
                $ret = OrderLib::getInstance()->orderSalesModel->insert($add_data);
            }
            if (!$ret) $is_success = false;
            $output->writeln($ret ? "{$_action_str} account sale success 【{$account}】" : "{$_action_str} account sale fail 【{$account}】");
        }

        if ($is_success) echo "add account sale statistics data success!\n";
    }


    /**
     * 统计销售员 的销售额
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-09-18 04:23:18
     */
    private function _countSellerSale(Input $input, Output $output)
    {
        $options = $input->getOptions();

        $day = $options['day'] ?? date('Y-m-d', strtotime('-1 day'));

        $start_time = strtotime($day . ' 00:00:00');
        $end_time = strtotime($day . ' 23:59:59');

        if (date('d') == date('d', strtotime($day))) {
            echo "不统计销售员当天销售额";
            return;
        }

        $all_seller = ToolsLib::getInstance()->getAllSaleUsers();

        $where_order_total = [
            'createdtime' => [['EGT', $start_time], ['ELT', $end_time]],
            //'sales_user'  => ['IN', $all_seller],
            'sales_user'  => ['NEQ', ''],
            'status'      => ['IN', $this->_getValidSaleOrderStatus()],
            'type'        => ['NOT IN', [3, 8]], // 排除礼物单销售额统计
        ];

        $where_order_refund = array_merge($where_order_total, [
            'type' => ['IN', [4, 7]],
        ]);
        $where_order_cancel = array_merge($where_order_total, [
            'status' => 1731,
        ]);
        $total_data = OrderLib::getInstance()->getSellerSaleTotalInfo($where_order_total);
        $refund_total_data = OrderLib::getInstance()->getSellerSaleTotalInfo($where_order_refund);
        $cancel_total_data = OrderLib::getInstance()->getSellerSaleTotalInfo($where_order_cancel);

        // 有符合订单条件的【销售员-组织id列表】
        $_order_seller_branch_list = array_keys($total_data['total_account_group']);

        // todo: 一次性查出所有的 账号 当前对应的记录数 (优化运行速度)
        $_where_group = ['year' => date('Y', strtotime($day)), 'month' => date('m', strtotime($day)), 'days' => date('d', strtotime($day))];
        $tmp_group_list = OrderLib::getInstance()->orderSellerModel->field('seller, branch_id, COUNT(*) as count')->where($_where_group)->group('seller, branch_id')->select()->toArray();
        $count_group = [];
        foreach ($tmp_group_list as $group_info) {
            // 因为，一个人可以属于多个组织架构的成员，所以  成员 + 组id 才是 唯一的
            $_tmp_key = trim($group_info['seller']) . '_' . intval($group_info['branch_id']);
            $count_group[$_tmp_key] = $group_info['count'];
        }

        // TODO 针对 deadlock 的解决方案： 使用 【主键】 或者 【索引】, 一次性查出所有 ID
        $tmp_data = OrderLib::getInstance()->orderSellerModel->field('id, branch_id, seller')->where($_where_group)->select()->toArray();
        $id_map = [];
        foreach ($tmp_data as $v) {
            $_tmp_key = "{$day}_{$v['branch_id']}_{$v['seller']}";
            $id_map[$_tmp_key] = $v['id'];
        }


        // 有销售统计的【销售员-组织id列表】
        $_sale_seller_branch_list = array_keys($count_group);

        $union_seller_branch_list = array_unique(array_merge($_order_seller_branch_list, $_sale_seller_branch_list));

        $is_success = true;

        foreach ($union_seller_branch_list as $seller_branch) {

            $tmp_arr = explode('_', $seller_branch);

            $_tmp_branch_id = $tmp_arr[count($tmp_arr) - 1];
            unset($tmp_arr[count($tmp_arr) - 1]);
            $_tmp_sale_user = implode('_', $tmp_arr);

            // $_where = array_merge($_where_group, ['seller' => $_tmp_sale_user, 'branch_id' => $_tmp_branch_id]);
            $count = $count_group[$seller_branch] ?? 0;

            $is_edit = $count > 0 ? true : false;

            // 假如数据库中不存在记录  而且  统计数据不存在，下一个循环（因为有count > 0 时，可以更新为0）
            if ($count == 0 && !isset($total_data['total_account_group'][$seller_branch])) continue;

            if ($is_edit) {
                $add_data = [
                    'totals'         => $total_data['total_account_group'][$seller_branch]['seller_total'] ?? 0,
                    'sales'          => $total_data['total_account_group'][$seller_branch]['sale_total'] ?? 0,
                    'refunds_count'  => $refund_total_data['total_account_group'][$seller_branch]['seller_total'] ?? 0,
                    'refunds'        => $refund_total_data['total_account_group'][$seller_branch]['sale_total'] ?? 0,
                    'recycles_count' => $cancel_total_data['total_account_group'][$seller_branch]['seller_total'] ?? 0,
                    'recycles'       => $cancel_total_data['total_account_group'][$seller_branch]['sale_total'] ?? 0,
                    'datetime'       => strtotime($day),
                ];
            } else {
                $add_data = [
                    'year'           => date('Y', strtotime($day)),
                    'month'          => date('m', strtotime($day)),
                    'days'           => date('d', strtotime($day)),
                    'seller'         => $total_data['total_account_group'][$seller_branch]['sales_user'],
                    'branch_id'      => $total_data['total_account_group'][$seller_branch]['sales_branch_id'],
                    'totals'         => $total_data['total_account_group'][$seller_branch]['seller_total'] ?? 0,
                    'sales'          => $total_data['total_account_group'][$seller_branch]['sale_total'] ?? 0,
                    'refunds_count'  => $refund_total_data['total_account_group'][$seller_branch]['seller_total'] ?? 0,
                    'refunds'        => $refund_total_data['total_account_group'][$seller_branch]['sale_total'] ?? 0,
                    'recycles_count' => $cancel_total_data['total_account_group'][$seller_branch]['seller_total'] ?? 0,
                    'recycles'       => $cancel_total_data['total_account_group'][$seller_branch]['sale_total'] ?? 0,
                    'datetime'       => strtotime($day),
                ];
            }

            $_action_str = $is_edit ? 'update' : 'add';
            if ($is_edit) {
                $_tmp_key = "{$day}_{$_tmp_branch_id}_{$_tmp_sale_user}";
                $_tmp_id = $id_map[$_tmp_key];
                $_tmp_where = ['id' => $_tmp_id];

                $ret = OrderLib::getInstance()->orderSellerModel->where($_tmp_where)->update($add_data);
                $ret = ($ret === false) ? false : true;
            } else {
                $ret = OrderLib::getInstance()->orderSellerModel->insert($add_data);
            }
            if (!$ret) $is_success = false;
            $output->writeln($ret ? "{$_action_str} seller sales success 【{$seller_branch}】" : "{$_action_str} seller sales fail 【{$seller_branch}】");
        }

        if ($is_success) echo "add seller sale statistics data success!\n";
    }


    /**
     * 统计仓库， 发货地 的销售额
     * @author lamkakyun
     * @date 2019-01-24 15:32:48
     * @return void
     */
    private function _countStoreSale(Input $input, Output $output)
    {
        $options = $input->getOptions();

        $day = $options['day'] ?? date('Y-m-d', strtotime('-1 day'));

        $start_time = strtotime($day . ' 00:00:00');
        $end_time = strtotime($day . ' 23:59:59');

        $all_stores = ToolsLib::getInstance()->getStoreCache();
        $all_accounts = ToolsLib::getInstance()->getAllAccounts(1);

        // 仓库 + 账户 的唯一性
        $where_order = [
            'createdtime' => [['EGT', $start_time], ['ELT', $end_time]],
            'status'      => ['IN', $this->_getValidSaleOrderStatus()],
            'type'        => ['NOT IN', [3, 8]], // 排除礼物单销售额统计
        ];

        $total_data = OrderLib::getInstance()->getStoreSaleTotalInfo($where_order);

        // 有符合订单条件的【账号id-仓库id列表】
        $_order_account_store_list = array_keys($total_data['total_group']);

        // todo: 一次性查出所有的 当前对应的记录数 (优化运行速度)
        $_where_group = ['year' => date('Y', strtotime($day)), 'month' => date('m', strtotime($day)), 'days' => date('d', strtotime($day))];

        $tmp_group_list = OrderLib::getInstance()->orderSellerStoreModel->field('store_id, platform_account_id, COUNT(*) as count')->where($_where_group)->group('store_id, platform_account_id')->select()->toArray();

        $count_group = [];
        foreach ($tmp_group_list as $group_info) {
            $_tmp_key = trim($group_info['platform_account_id']) . '_' . intval($group_info['store_id']);
            $count_group[$_tmp_key] = $group_info['count'];
        }

        // TODO 针对 deadlock 的解决方案： 使用 【主键】 或者 【索引】, 一次性查出所有 ID
        $tmp_data = OrderLib::getInstance()->orderSellerStoreModel->field('id, platform_account_id, store_id')->where($_where_group)->select()->toArray();
        $id_map = [];
        foreach ($tmp_data as $v) {
            $_tmp_key = "{$day}_{$v['platform_account_id']}_{$v['store_id']}";
            $id_map[$_tmp_key] = $v['id'];
        }

        // 有销售统计的【账号id-仓库id列表】
        $_sale_account_store_list = array_keys($count_group);

        $union_account_store_list = array_unique(array_merge($_order_account_store_list, $_sale_account_store_list));

        $is_success = true;
        foreach ($union_account_store_list as $account_store) {
            var_dump($account_store);
            $tmp_arr = explode('_', $account_store);
            $_tmp_account_id = $tmp_arr[0];
            $_tmp_store_id = $tmp_arr[1];

            if (!$_tmp_account_id || !$_tmp_store_id) continue;

            $_account = $all_accounts[$_tmp_account_id];

            // $_where = array_merge($_where_group, ['platform_account_id' => $_tmp_account_id, 'store_id' => $_tmp_store_id]);
            $count = $count_group[$account_store] ?? 0;

            $is_edit = $count > 0 ? true : false;

            // 假如数据库中不存在记录  而且  统计数据不存在，下一个循环（因为有count > 0 时，可以更新为0）
            if ($count == 0 && !isset($total_data['total_group'][$account_store])) continue;


            if ($is_edit) {
                $add_data = [
                    'totals'   => $total_data['total_group'][$account_store]['seller_total'] ?? 0,
                    'sales'    => $total_data['total_group'][$account_store]['sale_total'] ?? 0,
                    'datetime' => strtotime($day),
                ];
            } else {
                $add_data = [
                    'year'                => date('Y', strtotime($day)),
                    'month'               => date('m', strtotime($day)),
                    'days'                => date('d', strtotime($day)),
                    'platform'            => $_account['platform'],
                    'platform_account'    => $_account['ebay_account'],
                    'platform_account_id' => $_account['id'],
                    'store_id'            => $_tmp_store_id,
                    'totals'              => $total_data['total_group'][$account_store]['seller_total'] ?? 0,
                    'sales'               => $total_data['total_group'][$account_store]['sale_total'] ?? 0,
                    'datetime'            => strtotime($day),
                ];
            }

            $_action_str = $is_edit ? 'update' : 'add';
            if ($is_edit) {
                $_tmp_key = "{$day}_{$_tmp_account_id}_{$_tmp_store_id}";
                $_tmp_id = $id_map[$_tmp_key];
                $_tmp_where = ['id' => $_tmp_id];

                $ret = OrderLib::getInstance()->orderSellerStoreModel->where($_tmp_where)->update($add_data);
                $ret = ($ret === false) ? false : true;
            } else {
                $ret = OrderLib::getInstance()->orderSellerStoreModel->insert($add_data);
            }

            if (!$ret) $is_success = false;
            $output->writeln($ret ? "{$_action_str} account-store sale success 【{$_account['ebay_account']}】" : "{$_action_str} account-store sale fail 【{$_account['ebay_account']}】");
        }

        if ($is_success) echo "【{$day}】add account-store sale statistics data success!\n";
    }


    private function _countLocationSale(Input $input, Output $output)
    {
        $options = $input->getOptions();

        $day = $options['day'] ?? date('Y-m-d', strtotime('-1 day'));

        $start_time = strtotime($day . ' 00:00:00');
        $end_time = strtotime($day . ' 23:59:59');

        $all_accounts = ToolsLib::getInstance()->getAllAccounts(1);

        // 仓库 + 账户 的唯一性
        $where_order = [
            'createdtime' => [['EGT', $start_time], ['ELT', $end_time]],
            'status'      => ['IN', $this->_getValidSaleOrderStatus()],
            'type'        => ['NOT IN', [3, 8]], // 排除礼物单销售额统计
        ];

        $total_data = OrderLib::getInstance()->getLocationSaleTotalInfo($where_order);
        // 有符合订单条件的【账号id-location 列表】
        $_order_account_location_list = array_keys($total_data['total_group']);

        // todo: 一次性查出所有的 当前对应的记录数 (优化运行速度)
        $_where_group = ['year' => date('Y', strtotime($day)), 'month' => date('m', strtotime($day)), 'days' => date('d', strtotime($day))];

        $tmp_group_list = OrderLib::getInstance()->orderSellerLocationModel->field('location, platform_account_id, COUNT(*) as count')->where($_where_group)->group('location, platform_account_id')->select()->toArray();

        $count_group = [];
        foreach ($tmp_group_list as $group_info) {
            $_tmp_key = trim($group_info['platform_account_id']) . '_' . $group_info['location'];
            $count_group[$_tmp_key] = $group_info['count'];
        }

        // TODO 针对 deadlock 的解决方案： 使用 【主键】 或者 【索引】, 一次性查出所有 ID
        $tmp_data = OrderLib::getInstance()->orderSellerLocationModel->field('id, platform_account_id, location')->where($_where_group)->select()->toArray();
        $id_map = [];
        foreach ($tmp_data as $v) {
            $_tmp_key = "{$day}_{$v['platform_account_id']}_{$v['location']}";
            $id_map[$_tmp_key] = $v['id'];
        }

        // 有销售统计的【账号id-仓库id列表】
        $_sale_account_location_list = array_keys($count_group);

        $union_account_location_list = array_unique(array_merge($_order_account_location_list, $_sale_account_location_list));

        $is_success = true;
        foreach ($union_account_location_list as $account_location) {
            var_dump($account_location);
            $tmp_arr = explode('_', $account_location);
            $_tmp_account_id = $tmp_arr[0];
            $_tmp_location = $tmp_arr[1];

            if (!$_tmp_account_id || !$_tmp_location) continue;

            $_account = $all_accounts[$_tmp_account_id];

            // $_where = array_merge($_where_group, ['platform_account_id' => $_tmp_account_id, 'location' => $_tmp_location]);
            $count = $count_group[$account_location] ?? 0;

            $is_edit = $count > 0 ? true : false;

            // 假如数据库中不存在记录  而且  统计数据不存在，下一个循环（因为有count > 0 时，可以更新为0）
            if ($count == 0 && !isset($total_data['total_group'][$account_location])) continue;


            if ($is_edit) {
                $add_data = [
                    'totals'   => $total_data['total_group'][$account_location]['seller_total'] ?? 0,
                    'sales'    => $total_data['total_group'][$account_location]['sale_total'] ?? 0,
                    'datetime' => strtotime($day),
                ];
            } else {
                $add_data = [
                    'year'                => date('Y', strtotime($day)),
                    'month'               => date('m', strtotime($day)),
                    'days'                => date('d', strtotime($day)),
                    'platform'            => $_account['platform'],
                    'platform_account'    => $_account['ebay_account'],
                    'platform_account_id' => $_account['id'],
                    'location'            => $_tmp_location,
                    'totals'              => $total_data['total_group'][$account_location]['seller_total'] ?? 0,
                    'sales'               => $total_data['total_group'][$account_location]['sale_total'] ?? 0,
                    'datetime'            => strtotime($day),
                ];
            }

            $_action_str = $is_edit ? 'update' : 'add';
            if ($is_edit) {
                $_tmp_key = "{$day}_{$_tmp_account_id}_{$_tmp_location}";
                $_tmp_id = $id_map[$_tmp_key];
                $_tmp_where = ['id' => $_tmp_id];

                $ret = OrderLib::getInstance()->orderSellerLocationModel->where($_tmp_where)->update($add_data);
                $ret = ($ret === false) ? false : true;
            } else {
                $ret = OrderLib::getInstance()->orderSellerLocationModel->insert($add_data);
            }

            if (!$ret) $is_success = false;
            $output->writeln($ret ? "{$_action_str} account-location sale success 【{$_account['ebay_account']}】" : "{$_action_str} account-location sale fail 【{$_account['ebay_account']}】");
        }

        if ($is_success) echo "【{$day}】add account-location sale statistics data success!\n";
    }


    /**
     * 统计sku 的销售额
     * @author lamkakyun
     * @date 2019-02-18 13:46:25
     * @return void
     */
    private function _countSkuSale(Input $input, Output $output)
    {
        $options = $input->getOptions();
        $day = $options['day'] ?? date('Y-m-d', strtotime('-1 day'));
        $start_time = strtotime($day . ' 00:00:00');
        $end_time = strtotime($day . ' 23:59:59');

        $output->writeln("============== {$day} sku sale ===========");

        $where_order_total = [
            'o.createdtime' => [['EGT', $start_time], ['ELT', $end_time]],
            'o.status'      => ['IN', $this->_getValidSaleOrderStatus()],
            'o.type'        => ['NOT IN', [3, 8]],
        ];
        $this->_countSkuDateSale($start_time, $end_time, $day, $where_order_total);
        $this->_countSkuAccount($start_time, $end_time, $day, $where_order_total);
        $this->_countSkuSeller($start_time, $end_time, $day, $where_order_total);
        $this->_countSkuDateSale2($start_time, $end_time, $day, $where_order_total);
        $this->_countSkuDeveloper($start_time, $end_time, $day, $where_order_total);
        $this->_countSkuCountry($start_time, $end_time, $day, $where_order_total);
        $this->_countSkuStore($start_time, $end_time, $day, $where_order_total);
        $this->_countSkuCatSale($start_time, $end_time, $day, $where_order_total);
    }

    /**
     * (不保存 sku 信息, 只保存 日期和 所有sku 的销售量)
     * @param $start_time
     * @param $end_time
     * @param $day
     * @param $where_order_total
     */
    private function _countSkuDateSale($start_time, $end_time, $day, $where_order_total)
    {
        $total_data = OrderLib::getInstance()->getSkuSaleTotalInfo($where_order_total);

        // 找出是否有统计数据
        $_where = ['year' => date('Y', strtotime($day)), 'month' => date('m', strtotime($day)), 'days' => date('d', strtotime($day))];

        $count = NewSkuLib::getInstance()->skuDateModel->where($_where)->count();

        $save_data = [
            'year'     => date('Y', strtotime($day)),
            'month'    => date('m', strtotime($day)),
            'days'     => date('d', strtotime($day)),
            'counts'   => $total_data['sku_type_num'] ?? 0,
            'totals'   => $total_data['sku_nums_total'] ?? 0,
            'costs'    => $total_data['cost_total'] ?? 0,
            'sales'    => $total_data['sale_total'] ?? 0,
            'datetime' => strtotime($day),
        ];

        if ($count > 0) {
            $ret = NewSkuLib::getInstance()->skuDateModel->where($_where)->update($save_data);
            $ret = ($ret === false) ? false : true;
        } else {
            $ret = NewSkuLib::getInstance()->skuDateModel->insert($save_data);
        }
        $_action_str = $count > 0 ? 'update' : 'add';
        echo $ret ? "{$_action_str} sku sale success\n" : "{$_action_str} sku sale fail\n";
    }


    /**
     * 统计 sku 按日期 （和_countSkuDateSale 的不同在于，这个统计，按sku分组） (保存 sku 信息)
     * @author lamkakyun
     * @date 2019-03-20 15:40:53
     * @return void
     */
    private function _countSkuDateSale2($start_time, $end_time, $day, $where_order_total)
    {
        $group_by = 'sku';

        $total_data = OrderLib::getInstance()->getSkuSaleTotalInfo2($where_order_total);

        $_order_sku_list = array_keys($total_data['total_group']);
        $_where_group = ['year' => date('Y', strtotime($day)), 'month' => date('m', strtotime($day)), 'days' => date('d', strtotime($day))];

        $tmp_group_list = NewSkuLib::getInstance()->skuModel->field("{$group_by}, COUNT(*) as count")->where($_where_group)->group($group_by)->select()->toArray();

        $count_group = [];
        foreach ($tmp_group_list as $group_info) {
            $_tmp_key = trim($group_info['sku']);
            $count_group[$_tmp_key] = $group_info['count'];
        }

        // TODO 针对 deadlock 的解决方案： 使用 【主键】 或者 【索引】, 一次性查出所有 ID
        $tmp_data = NewSkuLib::getInstance()->skuModel->field('id, sku')->where($_where_group)->select()->toArray();

        $id_map = [];
        foreach ($tmp_data as $v) {
            $_tmp_key = "{$day}_{$v['sku']}";
            $id_map[$_tmp_key] = $v['id'];
        }

        // 有销售统计的【sku列表】
        $_sale_sku_list = array_keys($count_group);

        $union_sku_list = array_unique(array_merge($_order_sku_list, $_sale_sku_list));

        $is_success = true;
        foreach ($union_sku_list as $sku) {
            $tmp_arr = explode('___', $sku);
            $_tmp_sku = $tmp_arr[0];

            $count = $count_group[$sku] ?? 0;

            $is_edit = $count > 0 ? true : false;

            // 假如数据库中不存在记录  而且  统计数据不存在，下一个循环（因为有count > 0 时，可以更新为0）
            if ($count == 0 && !isset($total_data['total_group'][$sku])) continue;

            if ($is_edit) {
                $save_data = [
                    'qty'      => $total_data['total_group'][$sku]['sku_nums_total'] ?? 0,
                    'datetime' => strtotime($day),
                ];
            } else {
                $save_data = [
                    'year'        => date('Y', strtotime($day)),
                    'month'       => date('m', strtotime($day)),
                    'days'        => date('d', strtotime($day)),
                    'sku'         => trim($total_data['total_group'][$sku]['sku']),
                    'sku_combine' => $total_data['total_group'][$sku]['sku_combine'],
                    'parent'      => $total_data['total_group'][$sku]['parent'],
                    'name'        => $total_data['total_group'][$sku]['name'],
                    'thumb'       => str_replace('http://erp.spocoo.com/images/small/', '', $total_data['total_group'][$sku]['thumb']),
                    'qty'         => $total_data['total_group'][$sku]['sku_nums_total'],
                    'datetime'    => strtotime($day),
                ];
            }

            $_action_str = $is_edit ? 'update' : 'add';
            if ($is_edit) {
                $_tmp_key = "{$day}_{$_tmp_sku}";
                $_tmp_id = $id_map[$_tmp_key];
                $_tmp_where = ['id' => $_tmp_id];

                $ret = NewSkuLib::getInstance()->skuModel->where($_tmp_where)->update($save_data);
                $ret = ($ret === false) ? false : true;
            } else {
                $ret = NewSkuLib::getInstance()->skuModel->insert($save_data);
            }
            if (!$ret) $is_success = false;
            echo $ret ? "{$_action_str} sku sales success 【{$sku}】\n" : "{$_action_str} sku sales fail 【{$sku}】\n";
        }
    }


    /**
     * 按分类
     * @param $start_time
     * @param $end_time
     * @param $day
     * @param $where_order_total
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function _countSkuCatSale($start_time, $end_time, $day, $where_order_total)
    {
        $group_by = "sku, category_id, category_child_id";

        $total_data = OrderLib::getInstance()->getSkuCategorySaleTotalInfo($where_order_total);

        $_order_sku_cat_list = array_keys($total_data['total_group']);
        $_where_group = ['year' => date('Y', strtotime($day)), 'month' => date('m', strtotime($day)), 'days' => date('d', strtotime($day))];

        $tmp_group_list = NewSkuLib::getInstance()->skuCategoryModel->field("{$group_by}, COUNT(*) as count")->where($_where_group)->group($group_by)->select()->toArray();

        $count_group = [];
        foreach ($tmp_group_list as $group_info) {
            $_tmp_key = trim($group_info['sku']) . '___' . intval($group_info['category_id']) . '___' . intval($group_info['category_child_id']);
            $count_group[$_tmp_key] = $group_info['count'];
        }

        // TODO 针对 deadlock 的解决方案： 使用 【主键】 或者 【索引】, 一次性查出所有 ID
        $tmp_data = NewSkuLib::getInstance()->skuCategoryModel->field('id, sku, category_id, category_child_id')->where($_where_group)->select()->toArray();

        $id_map = [];
        foreach ($tmp_data as $v) {
            $_tmp_key = "{$day}_{$v['sku']}_{$v['category_id']}_{$v['category_child_id']}";
            $id_map[$_tmp_key] = $v['id'];
        }

        // 有销售统计的【sku-cat列表】
        $_sale_sku_cat_list = array_keys($count_group);

        $union_sku_cat_list = array_unique(array_merge($_order_sku_cat_list, $_sale_sku_cat_list));

        $is_success = true;
        foreach ($union_sku_cat_list as $sku_cat) {
            $tmp_arr = explode('___', $sku_cat);
            $_tmp_sku = $tmp_arr[0];
            $_tmp_cat_id = $tmp_arr[1];
            $_tmp_cat_child_id = $tmp_arr[2];

            // $_where = array_merge($_where_group, ['sku' => $_tmp_sku, 'category_id' => $_tmp_cat_id, 'category_child_id' => $_tmp_cat_child_id]);

            $count = $count_group[$sku_cat] ?? 0;

            $is_edit = $count > 0 ? true : false;

            // 假如数据库中不存在记录  而且  统计数据不存在，下一个循环（因为有count > 0 时，可以更新为0）
            if ($count == 0 && !isset($total_data['total_group'][$sku_cat])) continue;

            if ($is_edit) {
                $save_data = [
                    'qty'      => $total_data['total_group'][$sku_cat]['sku_nums_total'] ?? 0,
                    'datetime' => strtotime($day),
                ];
            } else {
                $save_data = [
                    'year'              => date('Y', strtotime($day)),
                    'month'             => date('m', strtotime($day)),
                    'days'              => date('d', strtotime($day)),
                    'sku'               => trim($total_data['total_group'][$sku_cat]['sku']),
                    'sku_combine'       => $total_data['total_group'][$sku_cat]['sku_combine'],
                    'parent'            => $total_data['total_group'][$sku_cat]['parent'],
                    'name'              => $total_data['total_group'][$sku_cat]['name'],
                    'thumb'             => str_replace('http://erp.spocoo.com/images/small/', '', $total_data['total_group'][$sku_cat]['thumb']),
                    'category_id'       => $total_data['total_group'][$sku_cat]['category_id'],
                    'category_child_id' => $total_data['total_group'][$sku_cat]['category_child_id'],
                    'qty'               => $total_data['total_group'][$sku_cat]['sku_nums_total'],
                    'datetime'          => strtotime($day),
                ];
            }

            $_action_str = $is_edit ? 'update' : 'add';
            if ($is_edit) {
                $_tmp_key = "{$day}_{$_tmp_sku}_{$_tmp_cat_id}_{$_tmp_cat_child_id}";
                $_tmp_id = $id_map[$_tmp_key];
                $_tmp_where = ['id' => $_tmp_id];

                $ret = NewSkuLib::getInstance()->skuCategoryModel->where($_tmp_where)->update($save_data);
                $ret = ($ret === false) ? false : true;
            } else {
                $ret = NewSkuLib::getInstance()->skuCategoryModel->insert($save_data);
            }
            if (!$ret) $is_success = false;
            echo $ret ? "{$_action_str} sku category sales success 【{$sku_cat}】\n" : "{$_action_str} sku category sales fail 【{$sku_cat}】\n";
        }
    }

    /**
     * 按帐号
     * @param $start_time
     * @param $end_time
     * @param $day
     * @param $where_order_total
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function _countSkuAccount($start_time, $end_time, $day, $where_order_total)
    {
        $group_by = "sku, platform_account";

        $total_data = OrderLib::getInstance()->getSkuAccountSaleTotalInfo($where_order_total);

        $_order_sku_account_list = array_keys($total_data['total_group']);
        $_where_group = ['year' => date('Y', strtotime($day)), 'month' => date('m', strtotime($day)), 'days' => date('d', strtotime($day))];

        $tmp_group_list = NewSkuLib::getInstance()->skuPlatformModel->field("{$group_by}, COUNT(*) as count")->where($_where_group)->group($group_by)->select()->toArray();

        $count_group = [];
        foreach ($tmp_group_list as $group_info) {
            $_tmp_key = trim($group_info['sku']) . '___' . $group_info['platform_account'];
            $count_group[$_tmp_key] = $group_info['count'];
        }

        // TODO 针对 deadlock 的解决方案： 使用 【主键】 或者 【索引】, 一次性查出所有 ID
        $tmp_data = NewSkuLib::getInstance()->skuPlatformModel->field('id, sku, platform_account')->where($_where_group)->select()->toArray();

        $id_map = [];
        foreach ($tmp_data as $v) {
            $_tmp_key = "{$day}_{$v['sku']}_{$v['platform_account']}";
            $id_map[$_tmp_key] = $v['id'];
        }

        // 有销售统计的【sku-account列表】
        $_sale_sku_account_list = array_keys($count_group);

        $union_sku_account_list = array_unique(array_merge($_order_sku_account_list, $_sale_sku_account_list));

        $is_success = true;
        foreach ($union_sku_account_list as $sku_account) {
            $tmp_arr = explode('___', $sku_account);
            $_tmp_sku = $tmp_arr[0];
            $_tmp_account = $tmp_arr[1];

            // $_where = array_merge($_where_group, ['sku' => $_tmp_sku, 'platform_account' => $_tmp_account]);

            $count = $count_group[$sku_account] ?? 0;

            $is_edit = $count > 0 ? true : false;

            // 假如数据库中不存在记录  而且  统计数据不存在，下一个循环（因为有count > 0 时，可以更新为0）
            if ($count == 0 && !isset($total_data['total_group'][$sku_account])) continue;

            if ($is_edit) {
                $save_data = [
                    'qty'      => $total_data['total_group'][$sku_account]['sku_nums_total'] ?? 0,
                    'datetime' => strtotime($day),
                ];
            } else {
                $save_data = [
                    'year'             => date('Y', strtotime($day)),
                    'month'            => date('m', strtotime($day)),
                    'days'             => date('d', strtotime($day)),
                    'sku'              => trim($total_data['total_group'][$sku_account]['sku']),
                    'sku_combine'      => $total_data['total_group'][$sku_account]['sku_combine'],
                    'parent'           => $total_data['total_group'][$sku_account]['parent'],
                    'name'             => $total_data['total_group'][$sku_account]['name'],
                    'thumb'            => str_replace('http://erp.spocoo.com/images/small/', '', $total_data['total_group'][$sku_account]['thumb']),
                    'platform'         => $total_data['total_group'][$sku_account]['platform'],
                    'platform_account' => $total_data['total_group'][$sku_account]['platform_account'],
                    'qty'              => $total_data['total_group'][$sku_account]['sku_nums_total'],
                    'datetime'         => strtotime($day),
                ];
            }

            $_action_str = $is_edit ? 'update' : 'add';
            if ($is_edit) {
                $_tmp_key = "{$day}_{$_tmp_sku}_{$_tmp_account}";
                $_tmp_id = $id_map[$_tmp_key];
                $_tmp_where = ['id' => $_tmp_id];

                $ret = NewSkuLib::getInstance()->skuPlatformModel->where($_tmp_where)->update($save_data);
                $ret = ($ret === false) ? false : true;
            } else {
                $ret = NewSkuLib::getInstance()->skuPlatformModel->insert($save_data);
            }
            if (!$ret) $is_success = false;
            echo $ret ? "{$_action_str} sku account sales success 【{$sku_account}】\n" : "{$_action_str} sku account sales fail 【{$sku_account}】\n";
        }
    }

    /**
     * 按销售人元
     * @param $start_time
     * @param $end_time
     * @param $day
     * @param $where_order_total
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function _countSkuSeller($start_time, $end_time, $day, $where_order_total)
    {
        $group_by = "sku, seller";

        $total_data = OrderLib::getInstance()->getSkuSellerSaleTotalInfo($where_order_total);
        $_order_sku_seller_list = array_keys($total_data['total_group']);
        $_where_group = ['year' => date('Y', strtotime($day)), 'month' => date('m', strtotime($day)), 'days' => date('d', strtotime($day))];

        $tmp_group_list = NewSkuLib::getInstance()->skuSellerModel->field("{$group_by}, COUNT(*) as count")->where($_where_group)->group($group_by)->select()->toArray();

        $count_group = [];
        foreach ($tmp_group_list as $group_info) {
            $_tmp_key = trim($group_info['sku']) . '___' . $group_info['seller'];
            $count_group[$_tmp_key] = $group_info['count'];
        }

        // TODO 针对 deadlock 的解决方案： 使用 【主键】 或者 【索引】, 一次性查出所有 ID
        $tmp_data = NewSkuLib::getInstance()->skuSellerModel->field('id, sku, seller')->where($_where_group)->select()->toArray();

        $id_map = [];
        foreach ($tmp_data as $v) {
            $_tmp_key = "{$day}_{$v['sku']}_{$v['seller']}";
            $id_map[$_tmp_key] = $v['id'];
        }

        // 有销售统计的【sku-seller列表】
        $_sale_sku_seller_list = array_keys($count_group);

        $union_sku_seller_list = array_unique(array_merge($_order_sku_seller_list, $_sale_sku_seller_list));

        $is_success = true;
        foreach ($union_sku_seller_list as $sku_seller) {
            $tmp_arr = explode('___', $sku_seller);
            $_tmp_sku = $tmp_arr[0];
            $_tmp_seller = $tmp_arr[1];

            // $_where = array_merge($_where_group, ['sku' => $_tmp_sku, 'seller' => $_tmp_seller]);

            $count = $count_group[$sku_seller] ?? 0;

            $is_edit = $count > 0 ? true : false;

            // 假如数据库中不存在记录  而且  统计数据不存在，下一个循环（因为有count > 0 时，可以更新为0）
            if ($count == 0 && !isset($total_data['total_group'][$sku_seller])) continue;

            if ($is_edit) {
                $save_data = [
                    'qty'      => $total_data['total_group'][$sku_seller]['sku_nums_total'] ?? 0,
                    'datetime' => strtotime($day),
                ];
            } else {
                $save_data = [
                    'year'            => date('Y', strtotime($day)),
                    'month'           => date('m', strtotime($day)),
                    'days'            => date('d', strtotime($day)),
                    'sku'             => trim($total_data['total_group'][$sku_seller]['sku']),
                    'sku_combine'     => $total_data['total_group'][$sku_seller]['sku_combine'],
                    'parent'          => $total_data['total_group'][$sku_seller]['parent'],
                    'name'            => $total_data['total_group'][$sku_seller]['name'],
                    'thumb'           => str_replace('http://erp.spocoo.com/images/small/', '', $total_data['total_group'][$sku_seller]['thumb']),
                    'seller'          => $total_data['total_group'][$sku_seller]['sales_user'],
                    'sales_branch_id' => $total_data['total_group'][$sku_seller]['sales_branch_id'],
                    'qty'             => $total_data['total_group'][$sku_seller]['sku_nums_total'],
                    'datetime'        => strtotime($day),
                ];
            }

            // echo '<pre>';var_dump($save_data);echo '</pre>';
            // return;

            $_action_str = $is_edit ? 'update' : 'add';
            if ($is_edit) {
                $_tmp_key = "{$day}_{$_tmp_sku}_{$_tmp_seller}";
                $_tmp_id = $id_map[$_tmp_key];
                $_tmp_where = ['id' => $_tmp_id];

                $ret = NewSkuLib::getInstance()->skuSellerModel->where($_tmp_where)->update($save_data);
                $ret = ($ret === false) ? false : true;
            } else {
                $ret = NewSkuLib::getInstance()->skuSellerModel->insert($save_data);
            }
            if (!$ret) $is_success = false;
            echo $ret ? "{$_action_str} sku seller sales success 【{$sku_seller}】\n" : "{$_action_str} sku seller sales fail 【{$sku_seller}】\n";
        }
    }

    /**
     * 按开发人员
     * @param $start_time
     * @param $end_time
     * @param $day
     * @param $where_order_total
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function _countSkuDeveloper($start_time, $end_time, $day, $where_order_total)
    {
        $group_by = "sku, developer";

        $total_data = OrderLib::getInstance()->getSkuDeveloperSaleTotalInfo($where_order_total);

        $_order_sku_developer_list = array_keys($total_data['total_group']);
        $_where_group = ['year' => date('Y', strtotime($day)), 'month' => date('m', strtotime($day)), 'days' => date('d', strtotime($day))];

        $tmp_group_list = NewSkuLib::getInstance()->skuDeveloperModel->field("{$group_by}, COUNT(*) as count")->where($_where_group)->group($group_by)->select()->toArray();

        $count_group = [];
        foreach ($tmp_group_list as $group_info) {
            $_tmp_key = trim($group_info['sku']) . '___' . $group_info['developer'];
            $count_group[$_tmp_key] = $group_info['count'];
        }

        // TODO 针对 deadlock 的解决方案： 使用 【主键】 或者 【索引】, 一次性查出所有 ID
        $tmp_data = NewSkuLib::getInstance()->skuDeveloperModel->field('id, sku, developer')->where($_where_group)->select()->toArray();

        $id_map = [];
        foreach ($tmp_data as $v) {
            $_tmp_key = "{$day}_{$v['sku']}_{$v['developer']}";
            $id_map[$_tmp_key] = $v['id'];
        }

        // 有销售统计的【sku-developer列表】
        $_sale_sku_developer_list = array_keys($count_group);

        $union_sku_developer_list = array_unique(array_merge($_order_sku_developer_list, $_sale_sku_developer_list));

        $is_success = true;
        foreach ($union_sku_developer_list as $sku_developer) {
            $tmp_arr = explode('___', $sku_developer);
            $_tmp_sku = $tmp_arr[0];
            $_tmp_developer = $tmp_arr[1];

            // $_where = array_merge($_where_group, ['sku' => $_tmp_sku, 'developer' => $_tmp_developer]);

            $count = $count_group[$sku_developer] ?? 0;

            $is_edit = $count > 0 ? true : false;

            // 假如数据库中不存在记录  而且  统计数据不存在，下一个循环（因为有count > 0 时，可以更新为0）
            if ($count == 0 && !isset($total_data['total_group'][$sku_developer])) continue;

            if ($is_edit) {
                $save_data = [
                    'qty'      => $total_data['total_group'][$sku_developer]['sku_nums_total'] ?? 0,
                    'datetime' => strtotime($day),
                ];
            } else {
                $save_data = [
                    'year'        => date('Y', strtotime($day)),
                    'month'       => date('m', strtotime($day)),
                    'days'        => date('d', strtotime($day)),
                    'sku'         => trim($total_data['total_group'][$sku_developer]['sku']),
                    'sku_combine' => $total_data['total_group'][$sku_developer]['sku_combine'],
                    'parent'      => $total_data['total_group'][$sku_developer]['parent'],
                    'name'        => $total_data['total_group'][$sku_developer]['name'],
                    'thumb'       => str_replace('http://erp.spocoo.com/images/small/', '', $total_data['total_group'][$sku_developer]['thumb']),
                    'developer'   => $total_data['total_group'][$sku_developer]['develop_user'],
                    'qty'         => $total_data['total_group'][$sku_developer]['sku_nums_total'],
                    'datetime'    => strtotime($day),
                ];
            }

            $_action_str = $is_edit ? 'update' : 'add';
            if ($is_edit) {
                $_tmp_key = "{$day}_{$_tmp_sku}_{$_tmp_developer}";
                $_tmp_id = $id_map[$_tmp_key];
                $_tmp_where = ['id' => $_tmp_id];

                $ret = NewSkuLib::getInstance()->skuDeveloperModel->where($_tmp_where)->update($save_data);
                $ret = ($ret === false) ? false : true;
            } else {
                $ret = NewSkuLib::getInstance()->skuDeveloperModel->insert($save_data);
            }
            if (!$ret) $is_success = false;
            echo $ret ? "{$_action_str} sku developer sales success 【{$sku_developer}】\n" : "{$_action_str} sku developer sales fail 【{$sku_developer}】\n";
        }
    }

    /**
     * 按目标国家
     * @param $start_time
     * @param $end_time
     * @param $day
     * @param $where_order_total
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function _countSkuCountry($start_time, $end_time, $day, $where_order_total)
    {
        $group_by = "sku, couny";

        $total_data = OrderLib::getInstance()->getSkuCountrySaleTotalInfo($where_order_total);

        $_order_sku_country_list = array_keys($total_data['total_group']);
        $_where_group = ['year' => date('Y', strtotime($day)), 'month' => date('m', strtotime($day)), 'days' => date('d', strtotime($day))];

        $tmp_group_list = NewSkuLib::getInstance()->skuCountryModel->field("{$group_by}, COUNT(*) as count")->where($_where_group)->group($group_by)->select()->toArray();

        $count_group = [];
        foreach ($tmp_group_list as $group_info) {
            $_tmp_key = trim($group_info['sku']) . '___' . $group_info['couny'];
            $count_group[$_tmp_key] = $group_info['count'];
        }

        // TODO 针对 deadlock 的解决方案： 使用 【主键】 或者 【索引】, 一次性查出所有 ID
        $tmp_data = NewSkuLib::getInstance()->skuCountryModel->field('id, sku, couny')->where($_where_group)->select()->toArray();

        $id_map = [];
        foreach ($tmp_data as $v) {
            $_tmp_key = "{$day}_{$v['sku']}_{$v['couny']}";
            $id_map[$_tmp_key] = $v['id'];
        }

        // 有销售统计的【sku-country列表】
        $_sale_sku_country_list = array_keys($count_group);

        $union_sku_country_list = array_unique(array_merge($_order_sku_country_list, $_sale_sku_country_list));

        $is_success = true;
        foreach ($union_sku_country_list as $sku_country) {
            $tmp_arr = explode('___', $sku_country);
            $_tmp_sku = $tmp_arr[0];
            $_tmp_country = $tmp_arr[1];

            // $_where = array_merge($_where_group, ['sku' => $_tmp_sku, 'couny' => $_tmp_country]);

            $count = $count_group[$sku_country] ?? 0;

            $is_edit = $count > 0 ? true : false;

            // 假如数据库中不存在记录  而且  统计数据不存在，下一个循环（因为有count > 0 时，可以更新为0）
            if ($count == 0 && !isset($total_data['total_group'][$sku_country])) continue;

            if ($is_edit) {
                $save_data = [
                    'qty'      => $total_data['total_group'][$sku_country]['sku_nums_total'] ?? 0,
                    'datetime' => strtotime($day),
                ];
            } else {
                $save_data = [
                    'year'        => date('Y', strtotime($day)),
                    'month'       => date('m', strtotime($day)),
                    'days'        => date('d', strtotime($day)),
                    'sku'         => trim($total_data['total_group'][$sku_country]['sku']),
                    'sku_combine' => $total_data['total_group'][$sku_country]['sku_combine'],
                    'parent'      => $total_data['total_group'][$sku_country]['parent'],
                    'name'        => $total_data['total_group'][$sku_country]['name'],
                    'thumb'       => str_replace('http://erp.spocoo.com/images/small/', '', $total_data['total_group'][$sku_country]['thumb']),
                    'couny'       => $total_data['total_group'][$sku_country]['couny'],
                    'qty'         => $total_data['total_group'][$sku_country]['sku_nums_total'],
                    'datetime'    => strtotime($day),
                ];
            }

            $_action_str = $is_edit ? 'update' : 'add';
            if ($is_edit) {
                $_tmp_key = "{$day}_{$_tmp_sku}_{$_tmp_country}";
                $_tmp_id = $id_map[$_tmp_key];
                $_tmp_where = ['id' => $_tmp_id];

                $ret = NewSkuLib::getInstance()->skuCountryModel->where($_tmp_where)->update($save_data);
                $ret = ($ret === false) ? false : true;
            } else {
                $ret = NewSkuLib::getInstance()->skuCountryModel->insert($save_data);
            }
            if (!$ret) $is_success = false;
            echo $ret ? "{$_action_str} sku country sales success 【{$sku_country}】\n" : "{$_action_str} sku country sales fail 【{$sku_country}】\n";
        }
    }

    /**
     * 按仓库
     * @param $start_time
     * @param $end_time
     * @param $day
     * @param $where_order_total
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function _countSkuStore($start_time, $end_time, $day, $where_order_total)
    {
        $group_by = "sku, store_id";

        $total_data = OrderLib::getInstance()->getSkuStoreSaleTotalInfo($where_order_total);

        $_order_sku_store_list = array_keys($total_data['total_group']);
        $_where_group = ['year' => date('Y', strtotime($day)), 'month' => date('m', strtotime($day)), 'days' => date('d', strtotime($day))];

        $tmp_group_list = NewSkuLib::getInstance()->skuStoreModel->field("{$group_by}, COUNT(*) as count")->where($_where_group)->group($group_by)->select()->toArray();

        $count_group = [];
        foreach ($tmp_group_list as $group_info) {
            $_tmp_key = trim($group_info['sku']) . '___' . $group_info['store_id'];
            $count_group[$_tmp_key] = $group_info['count'];
        }

        // TODO 针对 deadlock 的解决方案： 使用 【主键】 或者 【索引】, 一次性查出所有 ID
        $tmp_data = NewSkuLib::getInstance()->skuStoreModel->field('id, sku, store_id')->where($_where_group)->select()->toArray();

        $id_map = [];
        foreach ($tmp_data as $v) {
            $_tmp_key = "{$day}_{$v['sku']}_{$v['store_id']}";
            $id_map[$_tmp_key] = $v['id'];
        }

        // 有销售统计的【sku-store列表】
        $_sale_sku_store_list = array_keys($count_group);

        $union_sku_store_list = array_unique(array_merge($_order_sku_store_list, $_sale_sku_store_list));

        $is_success = true;
        foreach ($union_sku_store_list as $sku_store) {
            $tmp_arr = explode('___', $sku_store);
            $_tmp_sku = $tmp_arr[0];
            $_tmp_store = $tmp_arr[1];

            // $_where = array_merge($_where_group, ['sku' => $_tmp_sku, 'store_id' => $_tmp_store]);

            $count = $count_group[$sku_store] ?? 0;

            $is_edit = $count > 0 ? true : false;

            // 假如数据库中不存在记录  而且  统计数据不存在，下一个循环（因为有count > 0 时，可以更新为0）
            if ($count == 0 && !isset($total_data['total_group'][$sku_store])) continue;

            if ($is_edit) {
                $save_data = [
                    'qty'      => $total_data['total_group'][$sku_store]['sku_nums_total'] ?? 0,
                    'datetime' => strtotime($day),
                ];
            } else {
                $save_data = [
                    'year'        => date('Y', strtotime($day)),
                    'month'       => date('m', strtotime($day)),
                    'days'        => date('d', strtotime($day)),
                    'sku'         => trim($total_data['total_group'][$sku_store]['sku']),
                    'sku_combine' => $total_data['total_group'][$sku_store]['sku_combine'],
                    'parent'      => $total_data['total_group'][$sku_store]['parent'],
                    'name'        => $total_data['total_group'][$sku_store]['name'],
                    'thumb'       => str_replace('http://erp.spocoo.com/images/small/', '', $total_data['total_group'][$sku_store]['thumb']),
                    'store_id'    => $total_data['total_group'][$sku_store]['store_id'],
                    'qty'         => $total_data['total_group'][$sku_store]['sku_nums_total'],
                    'datetime'    => strtotime($day),
                ];
            }

            $_action_str = $is_edit ? 'update' : 'add';
            if ($is_edit) {
                $_tmp_key = "{$day}_{$_tmp_sku}_{$_tmp_store}";
                $_tmp_id = $id_map[$_tmp_key];
                $_tmp_where = ['id' => $_tmp_id];

                $ret = NewSkuLib::getInstance()->skuStoreModel->where($_tmp_where)->update($save_data);
                $ret = ($ret === false) ? false : true;
            } else {
                $ret = NewSkuLib::getInstance()->skuStoreModel->insert($save_data);
            }
            if (!$ret) $is_success = false;
            echo $ret ? "{$_action_str} sku store sales success 【{$sku_store}】\n" : "{$_action_str} sku store sales fail 【{$sku_store}】\n";
        }
    }


    /**
     * 运行订单导出任务
     * @author lamkakyun
     * @date 2018-12-12 17:53:20
     * @cmd php think  order -m Order -a runOrderExportTask
     * @return void
     */
    public function runOrderExportTask(Input $input, Output $output)
    {
        ini_set('memory_limit', '2048M');

        $task_model = new Task();
        $order_model = new OrderModel();
        $order_detail_fee_model = new OrderDetailFee();

        $task_list = $task_model->where(['status' => 1])->order('priority DESC')->limit(1)->select();
        if (count($task_list) == 0) die('all done!');

        $order_fields_conf = OrderLib::getInstance()->getOrderFieldsConf();
        $order_fee_fields_conf = OrderLib::getInstance()->getOrderFeeFieldsConf();
        $sku_fields_conf = OrderLib::getInstance()->getSkuFieldsConf();
        $auto_export_directory = rtrim(ROOT_PATH, '/') . '/' . 'export';

        $order_type_conf = OrderLib::getInstance()->getOrderTypeConf();
        $order_status_conf = OrderLib::getInstance()->getOrderStatusConf();
        $recycle_reson_conf = Config::get('site.recyclereason');

        $store_cache = ToolsLib::getInstance()->getStoreCache();
        $org_tree = ToolsLib::getInstance()->getBusinessOrgTree();
        $org_arr = ToolsLib::getInstance()->treeToArray($org_tree);

        $tmp_arr = $org_arr;
        $org_arr = [];
        foreach ($tmp_arr as $v) {
            $org_arr[$v['id']] = $v;
        }

        $org_parent_names = ToolsLib::getInstance()->getAllOrgParentNameMap();

        foreach ($task_list as $task) {
            echo "\n正在运行导出任务：【{$task['task_name']}】\n";
            $params = json_decode($task->params, true);

            $is_sku_export = isset($params['sku_export']) && $params['sku_export'] == 'on';

            $where = [];
            $fields = !empty($params['order_fields']) ? $params['order_fields'] : array_keys($order_fields_conf);
            $fields = addPrefixForArr($fields, 'o.');

            if ($is_sku_export) {
                $sku_fields = !empty($params['sku_fields']) ? $params['sku_fields'] : array_keys($sku_fields_conf);

                // 两个表中的字段同名，所以要重命名,否则取不出来。
                $new_sku_fields = [];
                foreach ($sku_fields as $k => $v) {
                    if ($v == 'uptime') $v = 'uptime as sku_uptime';
                    if ($v == 'profit') $v = 'profit as sku_profit';
                    if ($v == 'cost') $v = 'cost as sku_cost';
                    if ($v == 'pack_cost') $v = 'pack_cost as sku_pack_cost';
                    if ($v == 'sales_user') $v = 'sales_user as sku_sales_user';
                    if ($v == 'sales_branch_id') $v = 'sales_branch_id as sku_sales_branch_id';
                    if ($v == 'stock_user') $v = 'stock_user as sku_stock_user';
                    if ($v == 'develop_user') $v = 'develop_user as sku_develop_user';

                    $new_sku_fields[] = $v;
                }
                $sku_fields = addPrefixForArr($sku_fields, 'odf.');
                $new_sku_fields = addPrefixForArr($new_sku_fields, 'odf.');
            }

            if (isset($params['start_time']) && !empty($params['start_time'])) {
                $where['o.' . $params['time_type']][] = ['EGT', strtotime($params['start_time'] . ' 00:00:00')];
            }
            if (isset($params['end_time']) && !empty($params['end_time'])) {
                $where['o.' . $params['time_type']][] = ['LT', strtotime($params['end_time'] . ' 23:59:59')];
            }
            if (isset($params['order_type']) && !empty($params['order_type'])) {
                $where['o.type'] = ['IN', $params['order_type']];
            }
            if (isset($params['order_status']) && !empty($params['order_status'])) {
                $where['o.status'] = ['IN', $params['order_status']];
            }
            if (isset($params['platform']) && !empty($params['platform'])) {
                $where['o.platform'] = ['IN', $params['platform']];
            }
            if (isset($params['account']) && !empty($params['account'])) {
                $where['o.platform_account'] = ['IN', $params['account']];
            }
            if (isset($params['carrier_company']) && !empty($params['carrier_company'])) {
                $where['o.carrier_company'] = ['IN', $params['carrier_company']];
            }
            if (isset($params['carrier']) && !empty($params['carrier'])) {
                $where['o.carrier'] = ['IN', $params['carrier']];
            }
            if (isset($params['org_id']) && !empty($params['org_id'])) {
                // 获取子组织结构的id
                $sub_org_ids = [];
                foreach ($params['org_id'] as $_tmp_org_id) {
                    $sub_org_ids = array_merge($sub_org_ids, ToolsLib::getInstance()->getSubOrgIds($_tmp_org_id));
                }
                $where['o.sales_branch_id'] = ['IN', $sub_org_ids];
            }

            if (isset($params['seller']) && !empty($params['seller'])) $where['o.sales_user'] = ['IN', $params['seller']];

            $fields_comment = [];
            if ($is_sku_export) {
                foreach (removePrefixForArr($sku_fields, 'odf.') as $k => $v) {
                    $fields_comment[] = $sku_fields_conf[$v];
                }
                $feeFields = addPrefixForArr(array_keys($order_fee_fields_conf), 'o.');
                $fields = array_diff($fields, $feeFields);
            }
            foreach (removePrefixForArr($fields, 'o.') as $k => $v) {
                $fields_comment[] = $order_fields_conf[$v];
            }

            $where_save = ['id' => $task['id']];
            // 导出中
            $task_model->where($where_save)->update(['status' => 2]);

            $fp = null;
            try {
                if (!file_exists($auto_export_directory)) mkdir($auto_export_directory);

                // TODO: 写文件
                $tmp_data = $task->toArray();
                unset($tmp_data['done_time']);
                unset($tmp_data['status']);
                unset($tmp_data['result']);
                $file_name = "{$params['task_name']}." . md5(json_encode($tmp_data)) . '.csv';
                $full_path = $auto_export_directory . '/' . $file_name;

                if (file_exists($full_path)) unlink($full_path);
                $fp = fopen($full_path, 'a');

                $select_start = 0;
                $select_size = 10000;

                if ($is_sku_export) {
                    $count = $order_detail_fee_model->alias('odf')->join('erp_order o', 'o.id = odf.order_id')->where($where)->count();
                    $count_sql = $order_detail_fee_model->getLastSql();
                } else {
                    $count = $order_model->alias('o')->where($where)->count();
                    $count_sql = $order_model->getLastSql();
                }

                // 查询总数sql打印
                $output->writeln($count_sql);

                if ($is_sku_export) {
                    $output->writeln("fields:" . implode(",", array_merge($sku_fields, $fields)));
                } else {
                    $output->writeln("fields:" . implode(",", $fields));
                }
                $output->writeln("start:" . date('Y-m-d H:i:s'));

                fputcsv($fp, $fields_comment);

                while ($select_start < $count) {
                    if ($is_sku_export) {
                        $data = $order_detail_fee_model->alias('odf')->join('erp_order o', 'o.id = odf.order_id')->field(array_merge($new_sku_fields, $fields))->where($where)->limit($select_start, $select_size)->select();
                        $output->writeln($order_detail_fee_model->getLastSql());
                    } else {
                        $data = $order_model->alias('o')->field($fields)->where($where)->limit($select_start, $select_size)->select();
                    }

                    foreach ($data as $d) {
                        $d = $d->toArray();
                        foreach ($d as $_k => $_v) {
                            // csv 编码为UTF8 的时候显示乱码
                            // $d[$_k] = iconv('utf-8', 'gbk', $_v);
                            if (preg_match('/time$/', $_k) && !in_array($_k, ['uptime', 'sku_uptime'])) {
                                $d[$_k] = ($_v != 0) ? date('Y-m-d H:i:s', $_v) : '';
                            }
                            if ($_k == 'type') $d[$_k] = $order_type_conf[$_v] ?? '';
                            if ($_k == 'status') $d[$_k] = $order_status_conf[$_v] ?? '';
                            if ($_k == 'recycle_reason') $d[$_k] = $recycle_reson_conf[$_v] ?? '';
                            if ($_k == 'store_id') $d[$_k] = $store_cache[$_v]['store_name'] ?? '';
                            if ($_k == 'profit_status') $d[$_k] = ($_v == '1') ? '是' : '否';
                            // 获取组织架构信息
                            if ($_k == 'sales_branch_id' || $_k == 'sku_sales_branch_id') {
                                $_org_info = $org_arr[$_v] ?? [];
                                if ($_org_info) {
                                    if ($_org_info['level'] > 2) {
                                        $_parent_org_name = $org_parent_names[$_org_info['name']] ?? '';
                                        $d[$_k] = $_parent_org_name . $_org_info['name'];
                                    } else {
                                        $d[$_k] = $_org_info['name'];
                                    }
                                } else $d[$_k] = '';
                            }
                        }
                        fputcsv($fp, $d);
                    }

                    $select_start += $select_size;
                }

                $result = ['count_sql' => $count_sql, 'success' => true, 'file_name' => $full_path];
                $save_data = ['status' => 3, 'result' => json_encode($result), 'done_time' => time()];

                $ret_save = $task_model->where($where_save)->update($save_data);
                $output->writeln("end:" . date('Y-m-d H:i:s'));
                if ($ret_save === false) echo "更新任务状态失败\n";

                else echo "导出成功: {$full_path}\n";
            } catch (\Exception $e) {
                $result = ['success' => false, 'err' => $e->getMessage()];
                $save_data = ['status' => 3, 'result' => json_encode($result), 'done_time' => time()];
                $ret_save = $task_model->where($where_save)->update($save_data);

                echo "异常报错：第" . $e->getLine() . "行" . $e->getMessage() . "\n";
            } finally {
                if ($fp) fclose($fp);
            }
        }
    }


    /**
     * 计算多个月
     * @cmd: php7 think  order -m Order -a countMonthlySaleAll
     * @author lamkakyun
     * @date 2018-12-19 14:46:51
     * @return void
     */
    public function countMonthlySaleAll(Input $input, Output $output)
    {
        // 统计6个月前 到 当月 的数据
        $options = $input->getOptions();
        $month_num = $options['month-num'] ?? 6;
        $this_month = date('Y-m');
        $before_month = date('Y-m', strtotime($this_month . " -{$month_num} month"));
        $range = range_month($before_month, $this_month);

        echo "将统计以下月份的销售数据:\n" . implode("\n", $range) . "\n";

        foreach ($range as $month) {
            $input->setOption('month', $month);
            $this->countMonthlySale($input, $output);
            $this->countMonthlySaleForAccount($input, $output);
        }
    }


    /**
     * 组织架构-销售员 月平均销售额 统计 （只计算一个月）
     * @cmd: php7 think order -m Order -a countMonthlySale --month=2018-09
     * @author lamkakyun
     * @date 2018-12-19 11:53:47
     * @return void
     */
    public function countMonthlySale(Input $input, Output $output)
    {
        $options = $input->getOptions();

        $order_seller_model = new OrderSeller();
        $order_seller_avg_model = new OrderSellerAvg();

        $select_count = 1000;

        $count_month = $options['month'] ?? date('Y-m');
        if (!preg_match('/^\d{4}-\d{2}$/', $count_month)) {
            echo "参数错误: month={$count_month}\n";
            return;
        }

        echo "-----------start count monthly sale {$count_month}-------------\n";

        $range = [$count_month];

        $tmp_where = [];
        $tmp_where2 = [];
        foreach ($range as $v) {
            $tmp = explode('-', $v);
            $tmp_year = $tmp[0];
            $tmp_month = $tmp[1];

            // 如果是当月，就不计算今天
            if ($tmp_month == date('m')) {
                $today_num = date('d');
                $tmp_where2[] = "(year = {$tmp_year} AND month = {$tmp_month} AND days < {$today_num})";
            } else {
                $tmp_where2[] = "(year = {$tmp_year} AND month = {$tmp_month})";
            }
            $tmp_where[] = "(year = {$tmp_year} AND month = {$tmp_month})";
        }
        $where_avg = implode(' OR ', $tmp_where);
        $where_seller = implode(' OR ', $tmp_where2);

        // TODO: 获取月平均销售额 统计 信息
        $tmp_data = $order_seller_avg_model->where($where_avg)->select();
        $avg_map = [];
        foreach ($tmp_data as $v) {
            $tmp_key = "{$v['sales_branch_id']}_{$v['sales_user']}_{$v['year']}_{$v['month']}";
            $avg_map[$tmp_key] = $v;
        }

        $group_by = 'year, month, branch_id, seller';

        $count = $order_seller_model->where($where_seller)->group($group_by)->count();

        echo "records amount: {$count}\n";

        $start_select = 0;

        while ($start_select < $count) {
            $data = $order_seller_model->field("{$group_by}, sum(sales) as sum_sales, sum(totals) as sum_totals")->where($where_seller)->group($group_by)->limit($start_select, $select_count)->select();

            $start_select += $select_count;

            foreach ($data as $v) {
                // $target_key = "{$v['branch_id']}_{$v['year']}_{$v['month']}";
                $avg_key = "{$v['branch_id']}_{$v['seller']}_{$v['year']}_{$v['month']}";

                $is_edit = isset($avg_map[$avg_key]) ? true : false;

                if ($is_edit) {
                    $add_data = [
                        // 'target'          => $target_map[$target_key] ?? 0,
                        'sales'    => $v['sum_sales'],
                        'totals'   => $v['sum_totals'],
                        'datetime' => time(),
                    ];

                    $_avg_info = $avg_map[$avg_key];
                    $_where_avg = ['id' => $_avg_info['id']];
                    $ret = $order_seller_avg_model->where($_where_avg)->update($add_data);
                    $ret = ($ret === false) ? false : true;
                } else {
                    $add_data = [
                        'year'            => $v['year'],
                        'month'           => $v['month'],
                        'sales_branch_id' => $v['branch_id'],
                        'sales_user'      => $v['seller'],
                        // 'target'          => $target_map[$target_key] ?? 0,
                        'sales'           => $v['sum_sales'],
                        'totals'          => $v['sum_totals'],
                        'datetime'        => time(),
                    ];

                    $ret = $order_seller_avg_model->insert($add_data);
                }

                $_action_str = $is_edit ? 'update' : 'add';

                $output->writeln($ret ? "{$_action_str} monthly sales for seller success 【{$v['seller']}】" : "{$_action_str} monthly sales for seller fail 【{$v['seller']}】");
            }
        }

        echo "-----------bingo count monthly sale {$count_month}-------------\n";
    }


    /**
     * 组织平台-账号 月平均销售额 统计 （只计算一个月）
     * @cmd: php7 think order -m Order -a countMonthlySaleForAccount --month=2018-09
     * @author lamkakyun
     * @date 2018-12-19 11:53:47
     * @return void
     */
    public function countMonthlySaleForAccount(Input $input, Output $output)
    {
        $options = $input->getOptions();

        $order_sales_model = new OrderSales();
        $order_account_avg_model = new OrderAccountAvg();

        $select_count = 1000;

        $count_month = $options['month'] ?? date('Y-m');
        if (!preg_match('/^\d{4}-\d{2}$/', $count_month)) {
            echo "参数错误: month={$count_month}\n";
            return;
        }

        echo "-----------start count monthly sale {$count_month}-------------\n";
        $range = [$count_month];

        $tmp_where = [];
        $tmp_where2 = [];
        foreach ($range as $v) {
            $tmp = explode('-', $v);
            $tmp_year = $tmp[0];
            $tmp_month = $tmp[1];

            // 如果是当月，就不计算今天
            if ($tmp_month == date('m')) {
                $today_num = date('d');
                $tmp_where2[] = "(year = {$tmp_year} AND month = {$tmp_month} AND days < {$today_num})";
            } else {
                $tmp_where2[] = "(year = {$tmp_year} AND month = {$tmp_month})";
            }
            $tmp_where[] = "(year = {$tmp_year} AND month = {$tmp_month})";
        }
        $where_avg = implode(' OR ', $tmp_where);
        $where_sales = implode(' OR ', $tmp_where2);

        // TODO: 获取月平均销售额 统计 信息
        $tmp_data = $order_account_avg_model->where($where_avg)->select();
        $avg_map = [];

        foreach ($tmp_data as $v) {
            $tmp_key = "{$v['platform']}_{$v['platform_account']}_{$v['year']}_{$v['month']}";
            $avg_map[$tmp_key] = $v;
        }

        $group_by = 'year, month, platform, platform_account';
        $count = $order_sales_model->where($where_sales)->group($group_by)->count();

        echo "records amount: {$count}\n";

        $start_select = 0;

        while ($start_select < $count) {
            $data = $order_sales_model->field("{$group_by}, sum(sales) as sum_sales, sum(totals) as sum_totals")->where($where_sales)->group($group_by)->limit($start_select, $select_count)->select();

            $start_select += $select_count;

            foreach ($data as $v) {
                $avg_key = "{$v['platform']}_{$v['platform_account']}_{$v['year']}_{$v['month']}";

                $is_edit = isset($avg_map[$avg_key]) ? true : false;

                if ($is_edit) {
                    $add_data = [
                        'sales'    => $v['sum_sales'],
                        'totals'   => $v['sum_totals'],
                        'datetime' => time(),
                    ];

                    $_avg_info = $avg_map[$avg_key];
                    $_where_avg = ['id' => $_avg_info['id']];
                    $ret = $order_account_avg_model->where($_where_avg)->update($add_data);
                    $ret = ($ret === false) ? false : true;
                } else {
                    $add_data = [
                        'year'             => $v['year'],
                        'month'            => $v['month'],
                        'platform'         => $v['platform'],
                        'platform_account' => $v['platform_account'],
                        'sales'            => $v['sum_sales'],
                        'totals'           => $v['sum_totals'],
                        'datetime'         => time(),
                    ];

                    $ret = $order_account_avg_model->insert($add_data);
                }

                $_action_str = $is_edit ? 'update' : 'add';

                $output->writeln($ret ? "{$_action_str} monthly sales for account success 【{$v['platform_account']}】" : "{$_action_str} monthly sales for account fail 【{$v['platform_account']}】");
            }
        }

        echo "-----------bingo count monthly sale {$count_month}-------------\n";
    }
}
