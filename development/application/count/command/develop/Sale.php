<?php
/**
 * @Copyright (C), ZhuoShi.
 * @Author: 杨能文
 * @Name: Sale.php
 * @Date: 2019/2/26
 * @Time: 17:01
 * @Description 开发员销售额统计
 */

namespace app\count\command\develop;

use app\count\model\DevelopSales;
use app\count\model\Order;
use think\cache\driver\Redis;
use think\Config;
use think\console\Input;
use think\console\Output;

class Sale
{
    /**
     * @var 开发员销售人模型
     */
    private $DevelopSalesModel;

    /**
     * @var 缓存
     */
    private $Redis;

    /**
     * @var 订单详情模型
     */
    private $Order;

    /**
     * Sale constructor.构造方法
     */
    public function __construct()
    {
        $this->DevelopSalesModel = new DevelopSales();
        $this->Redis             = new Redis(Config::get('redis'));
        $this->Order             = new Order();
    }

    /**
     * @desc 同步数据
     * @author 杨能文
     * @date 2019/2/26 17:16
     * @access public
     * @param Input $input
     * @param Output $output
     * @return string
     */
    public function sync(Input $input, Output $output)
    {
        $options = $input->getOptions();

        $day   = $options['day'] ?? date('Y-m-d');
        $start = $options['start'] ?? $day;
        $end   = $options['end'] ?? date('Y-m-d');

        $dayArr = $this->getDays($start, $end);
        foreach ($dayArr as $ymd) {
            $output->writeln(sprintf("%s %s", $ymd, 'start'));

            $this->getDevelopSaleData($ymd);

            $output->writeln(sprintf('%s - %s', $ymd, 'success'));
        }
        return "success\n\n";
    }

    /**
     * @desc 获取开发员销售额
     * @author 杨能文
     * @date 2019/2/26 17:19
     * @access public
     * @param $ymd
     * @return bool
     */
    private function getDevelopSaleData($ymd)
    {
        $year  = date('Y', strtotime($ymd));
        $month = date('m', strtotime($ymd));
        $day   = date('d', strtotime($ymd));
        $start = strtotime($ymd . ' 00:00:00');
        $end   = strtotime($ymd . ' 23:59:59');

        $fieldStr = "a.develop_user,a.type,sum(b.nums*b.price) as sale,count(b.sku) as counts";
        $groupStr = "a.develop_user,a.type";
        $where    = [
            'a.deliverytime' => ['between', [$start, $end]],        //时间维度搜索订单的进系统时间
            'a.status'       => ['eq', 2],                          //已发货订单
            'a.type'         => ['neq', 3],                         //排除补发订单
            'c.review_time'  => ['egt', strtotime('-6 month')],     //如果审核超过6个月，则忽略不计
        ];

        $list = $this->Order->alias('a')
            ->join('erp_order_detail b', 'a.id = b.order_id', 'left')
            ->join('erp_goods c ', 'b.sku = c.goods_sn')
            ->field($fieldStr)
            ->where($where)
            ->group($groupStr)
            ->select()->toArray();

        echo $this->Order->getLastSql() . PHP_EOL;

        if (empty($list)) return true;

        $data = [];
        //组装数据 type 4退款 7退货
        foreach ($list as $key => $val) {
            if (empty($val['develop_user'])) {
                continue;
            }
            $key  = md5($val['develop_user']);
            $type = $val['type'];
            if (isset($data[$key])) {
                if ($type == 4) {
                    $data[$key]['refunds']       += $val['sale'];
                    $data[$key]['refunds_count'] += $val['counts'];
                } elseif ($type == 5) {
                    $data[$key]['return_total'] += $val['sale'];
                    $data[$key]['return_num']   += $val['counts'];
                } else {
                    $data[$key]['sales']  += $val['sale'];
                    $data[$key]['counts'] += $val['counts'];
                }
            } else {
                $data[$key] = ['develop_user' => $val['develop_user'], 'counts' => 0, 'sales' => 0, 'refunds' => 0, 'refunds_count' => 0, 'return_num' => 0, 'return_total' => 0];
                if ($type == 4) {
                    $data[$key]['refunds']       = $val['sale'];
                    $data[$key]['refunds_count'] = $val['counts'];
                } elseif ($type == 5) {
                    $data[$key]['return_total'] = $val['sale'];
                    $data[$key]['return_num']   = $val['counts'];
                } else {
                    $data[$key]['sales']  = $val['sale'];
                    $data[$key]['counts'] = $val['counts'];
                }
            }
        }

        foreach ($data as $key => $val) {
            //唯一查询条件
            $unique = [
                'year'         => $year,
                'month'        => $month,
                'days'         => $day,
                'develop_user' => $val['develop_user'],
            ];

            //更新数据
            $saveData = [
                'counts'        => $val['counts'],
                'sales'         => $val['sales'],
                'refunds'       => $val['refunds'],
                'refunds_count' => $val['refunds_count'],
                'return_num'    => $val['return_num'],
                'return_total'  => $val['return_total'],
                'datetime'      => strtotime($ymd)
            ];

            $model     = $this->DevelopSalesModel;
            $hasRefund = $model->where($unique)->find();
            if (!empty($hasRefund)) {
                $model->update($saveData, $unique);
            } else {
                $model->insert(array_merge($unique, $saveData));
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