<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    jason
 */

namespace app\count\command\sku;

use app\count\model\OrderDetail;
use app\count\model\Order;
use \app\count\model\Sku as SkuModel;
use think\cache\driver\Redis;
use think\console\Input;
use think\console\Output;
use think\Config;

/**
 * @name SKU销量同步
 * Class SkuSync
 * @package app\count\command\sku
 */
class SkuSync
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
    private $orderDetail;

    /**
     * 统计订单表
     * @var object
     */
    private $skuModel;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var Output
     */
    private $output;

    /**
     * @var array 允许查询的订单状态
     */
    private $status = [2];

    /**
     * @var array 不允许查询的类型
     */
    private $notType = 3;

    /**
     * @var string 允许查询的字段
     */
    private $alowFields = 'a.sku,a.parent,a.sku_combine,a.name,a.thumb,a.category_id,a.category_child_id,a.platform,a.platform_account,b.store_id,b.sales_user,b.develop_user,b.couny,b.createdtime,sum(a.nums) as qty';

    /**
     * 构造函数
     * Orders constructor.
     * @param Input $input 输入对象
     * @param Output $output 输出对象
     */
    public function __construct(Input $input, Output $output)
    {
        $this->redis       = new Redis(Config::get('redis'));
        $this->orderDetail = new orderDetail();
        $this->skuModel    = new SkuModel();
        $this->order       = new Order();
        $this->output      = $output;
    }

    /**
     * @name 同步SKU的数据的运行脚本
     * @param Input $input
     * @param Output $output
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function sync(Input $input, Output $output)
    {
        ini_set('memory_limit', '2048M');

        $date    = $input->getArgument('date');
        $endDate = $input->getArgument('end_date');
        $date    = $date ?? date('Y-m-d', time());
        $endDate = $endDate ?? date('Y-m-d', time());

        //发货时间是今天的订单包含哪几天进系统的时间
        $output->writeln("---start {$date} execute time:" . date('H:i:s') . "---");
        $this->deliverytimeSelectCreatetime($date, $endDate, $input, $output);
        $output->writeln("---end {$endDate} execute time:" . date('H:i:s') . "---\n");
    }

    /**
     * 更新某一天的sku销量
     * @param Input $input
     * @param Output $output
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function createdtime(Input $input, Output $output)
    {
        $date = $input->getArgument('date');
        if (empty($date)) $date = date('Y-m-d');

        $this->syncCreatetime($date);
    }

    /**
     * @name 更新erp_sku的数据
     * @author jason
     * @param $detailArr 更新的数据
     * @param $datetime 更新的日期
     * @return string
     */
    function syncData($detailArr, $date)
    {
        if (empty($detailArr)) return false;

        $year  = $date ? date('Y', strtotime($date)) : date('Y');
        $month = $date ? date('m', strtotime($date)) : date('m');
        $days  = $date ? date('d', strtotime($date)) : date('d');

        // 对数据进行处理
        $exit_sku = $this->skuModel->where(['year' => $year, 'month' => $month, 'days' => $days])->column('md5_str');
        $new_md5  = [];

        foreach ($detailArr as $key => $val) {
            $keys = trim($date) .
                trim($val['sku']) .
                trim($val['platform']) .
                trim($val['store_id']) .
                trim($val['category_id']) .
                trim($val['sales_user']) .
                trim($val['platform_account']) .
                trim($val['category_child_id']) .
                trim($val['develop_user']) .
                trim($val['couny']);

            $md5_str  = md5($keys);
            $saveData = $val->toArray();
            $add1     = [
                'year'      => $year,
                'month'     => $month,
                'days'      => $days,
                'datetime'  => $saveData['createdtime'],
                'seller'    => $val['sales_user'],
                'developer' => $val['develop_user'],
                'sku'       => trim($val['sku']),
                'md5_str'   => $md5_str
            ];
            $saveData = array_merge($add1, $saveData);
            if (isset($saveData['sales_user'])) unset($saveData['sales_user']);
            if (isset($saveData['develop_user'])) unset($saveData['develop_user']);
            if (isset($saveData['createdtime'])) unset($saveData['createdtime']);

            $new_md5[] = $md5_str;
            //更新数据
            if (in_array($md5_str, $exit_sku)) {
                $qty  = $val['qty'];
                $save = ['qty' => $qty];
                if ($val['parent']) $save['parent'] = $val['parent'];
                if ($val['name']) $save['name'] = $val['name'];
                if ($val['thumb']) $save['thumb'] = $val['thumb'];
                if ($val['category_id']) $save['category_id'] = $val['category_id'];
                if ($val['category_child_id']) $save['category_child_id'] = $val['category_child_id'];
                $this->skuModel->where('md5_str', $md5_str)->update($save);
            } else {
                $this->skuModel->insert($saveData);
            }
            $this->output->writeln(sprintf("%s success", $keys));
        }
        return true;
    }

    /**
     * 根据发货时间更新sku销量
     * @param $date_time
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function syncCreatetime($date_time)
    {
        $output = $this->output;

        $start_time = strtotime($date_time . '00:00:00');
        $end_time   = strtotime($date_time . '23:59:59');

        $output->writeln(sprintf("%s createdtime start", $date_time));
        $map = [
            'b.createdtime' => ['between', [$start_time, $end_time]],
            'b.status'      => ['in', $this->status],
            'b.type'        => ['NEQ', $this->notType]
        ];
        //订单子表数据
        $order_detail = $this->orderDetail->alias('a')
            ->where($map)->join('order b', 'a.order_id = b.id')
            ->field($this->alowFields)
            ->group('a.sku,a.category_id,a.category_child_id,a.platform,a.platform_account,b.store_id,b.sales_user,b.develop_user,b.couny')
            ->select();

        // 保存或更新数据
        $result = $this->syncData($order_detail, $date_time);
        if ($result) {
            $output->writeln(sprintf("%s succes", $date_time));
        }
        $output->writeln(sprintf("%s createdtime end", $date_time));
    }

    /**
     * @name 发货时间是今天的订单包含哪几天进系统的时间
     * @author jason
     * @date 2018/12/11
     * @param $date 开始时间
     * @param $endDate  结束时间
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function deliverytimeSelectCreatetime($date, $endDate)
    {
        $Order  = $this->order;
        $output = $this->output;

        $start = $date;
        $end   = $endDate ? $endDate : $date;

        $where   = [
            'status' => ['in', $this->status],
            'type'   => ['neq', $this->notType],
        ];
        $getDate = $this->getDays($start, $end);
        foreach ($getDate as $time) {
            $start = strtotime($time . '00:00:00');
            $end   = strtotime($time . '23:59:59');

            $where['deliverytime'] = ['BETWEEN', [$start, $end]];
            // 写入日志
            $output->writeln(sprintf("%s delivery_time start", $time));

            // 查询发货当天的所有进系统时间
            $order = $Order->where($where)->field("DISTINCT FROM_UNIXTIME(createdtime,'%Y-%m-%d') as createdtime")->order('createdtime asc')->select();
            if (empty($order)) continue;

            $createtime = array_column($order->toArray(), 'createdtime');
            if (empty($createtime)) continue;

            //根据订单创建时间查询时间段内的SKU的销量
            foreach ($createtime as $date_time) {
                $this->output->writeln($date_time);
                if (ENVIRONMENT == 'production') {
                    $this->toSystem($date_time);
                } else {
                    $this->syncCreatetime($date_time);
                }
            }
            $output->writeln(sprintf("%s delivery_time end", $time));
        }
    }

    /**
     * linux 后台执行
     * @param $date_time
     */
    private function toSystem($date_time)
    {
        $path  = dirname(APP_PATH);
        $php   = PHP_BIN;
        $shell = "{$php} think sku -m SkuSync -a createdtime {$date_time} > /dev/null 2>&1 &";

        $this->output->writeln($shell);
        system($shell);
    }

    /**
     * @name 获取两个区间的所有天
     * @author jason
     * @date 2018/12/11
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