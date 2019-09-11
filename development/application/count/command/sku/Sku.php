<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    yang
 */

namespace app\count\command\sku;

use app\count\model\OrderDetail;
use \app\count\model\Sku as SkuModel;
use think\cache\driver\Redis;
use think\console\Input;
use think\console\Output;
use think\Config;

/**
 * sku销量数据同步
 * Class Sku
 * @package app\count\command\sku
 */
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
    private $orderDetail;

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
    public function __construct(Input $input, Output $output)
    {
        $this->redis       = new Redis(Config::get('redis'));
        $this->orderDetail = new orderDetail();
        $this->skuModel    = new SkuModel();
    }

    /**
     * 同步订单sku数据
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @param Input $input 输入对象
     * @param Output $output 输出对象
     */
    public function sync(Input $input, Output $output)
    {
        $total   = 0;
        $date    = $input->getArgument('date');
        $endDate = $input->getArgument('end_date');

        $orderDetail = $this->orderDetail;
        $nowTime     = time();
        $fieldStr    = 'a.sku,a.parent,a.sku_combine,a.name,a.thumb,a.category_id,a.category_child_id,a.platform,a.platform_account,b.store_id,b.sales_user,b.develop_user,b.couny,a.nums,a.id,b.createdtime,a.uptime';
        $msg         = "---start execute time:" . date('Y-m-d H:i:s') . "---\n";

        $map           = ['b.status' => array('neq', 1731)];
        $map['b.type'] = ['neq', 3];
        //查询多天的更新数据
        if ($date) {
            $output->writeln("---start execute time:" . date('H:i:s') . "---");
            $start = $date;
            $end   = $endDate ? $endDate : $date;
            for ($i = strtotime($start); $i <= strtotime($end); $i += 86400) {
                $total = 0;
                $val   = date('Y-m-d', $i);
                if (!$val) continue;
                $start_time = strtotime($val . ' 00:00:00');
                $end_time   = strtotime($val . ' 23:59:59');
                //删除sku脏数据
                $this->deleteData($start_time, $end_time);
                if (isset($map['a.id'])) unset($map['a.id']);
                do {
                    //进系统时间
                    $map['b.createdtime'] = ['between', [$start_time, $end_time]];
                    $obj                  = $orderDetail->alias('a')
                        ->where($map)->join('order b', 'a.order_id = b.id')
                        ->field($fieldStr)
                        ->order('a.id desc')
                        ->limit($this->limit)
                        ->select()
                        ->toArray();

                    $detailArr = replace_query($obj);
                    if (count($detailArr) < 1) break;
                    $endElement  = end($detailArr);
                    $endId       = $endElement['id'];
                    $map['a.id'] = array('lt', $endId);
                    $this->disposeData($detailArr, $nowTime, 'createdtime');
                    $total += count($detailArr);
                } while (count($detailArr) == $this->limit);
                $output->writeln("\n{$val}--total:{$total}\n");
            }
            $output->writeln("---end execute time:" . date('H:i:s') . "---\n");
            return '';
        }


        //查询当天的sku数据
        $today     = date('Y-m-d');
        $startTime = strtotime($today . ' 00:00:00');
        $endTime   = strtotime($today . ' 23:59:59');
        //删除sku脏数据
        $this->deleteData($startTime, $endTime);
        do {
            $map['b.createdtime'] = array('between', [$startTime, $endTime]);
            $obj                  = $orderDetail->alias('a')
                ->join('order b', 'a.order_id = b.id')
                ->where($map)
                ->field($fieldStr)
                ->order('a.id desc')
                ->limit($this->limit)
                ->select()
                ->toArray();

            $detailArr = replace_query($obj);
            if (count($detailArr) < 1) break;
            $endElement  = end($detailArr);
            $endId       = $endElement['id'];
            $map['a.id'] = array('lt', $endId);
            $this->disposeData($detailArr, $nowTime, 'createdtime');
            $total += count($detailArr);
        } while (count($detailArr) == $this->limit);
        $msg .= "execute total {$total} \n---end execute time:" . date('Y-m-d H:i:s') . "---\n\n";
        return $msg;
    }

    /**
     * 处理数据
     * @param $detailArr 待处理数组
     * @param  string $dateType
     * @param $time 脚本开始执行时间
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function disposeData($detailArr, $time, $dateType)
    {
        //对数据进行处理
        $detailInfo = [];
        foreach ($detailArr as $key => $val) {
            if ($dateType) $date = $val[$dateType];
            if (!is_numeric($date) && $date) $date = strtotime($date);
            $dateKey = $date ? date('Y-m-d', $date) : date('Y-m-d');
            $keys    = trim($dateKey) .
                trim($val['sku']) .
                trim($val['platform']) .
                trim($val['store_id']) .
                trim($val['category_id']) .
                trim($val['sales_user']) .
                trim($val['platform_account']) .
                trim($val['category_child_id']) .
                trim($val['develop_user']) .
                trim($val['couny']);
            $keys    = md5($keys);
            if (isset($detailInfo[$keys]) && is_array($detailInfo[$keys])) {
                $detailInfo[$keys]['nums'] += $val['nums'];
            } else {
                $val['date']       = $date;
                $detailInfo[$keys] = $val;
            }
            unset($detailArr[$key]);
        }


        foreach ($detailInfo as $key => $val) {
            if (!$val['sku'] || $val['nums'] < 1) continue;
            $year  = $val['date'] ? date('Y', $val['date']) : date('Y');
            $month = $val['date'] ? date('m', $val['date']) : date('m');
            $days  = $val['date'] ? date('d', $val['date']) : date('d');
            $add1  = [
                'year'      => $year,
                'month'     => $month,
                'days'      => $days,
                'datetime'  => $val['date'],
                'seller'    => $val['sales_user'],
                'developer' => $val['develop_user'],
                'qty'       => $val['nums'],
                'md5_str'   => $key
            ];
            $add2  = get_field_data($val, 'sku,parent,sku_combine,name,thumb,category_id,category_child_id,platform,platform_account,store_id,couny');
            $add   = array_merge($add1, $add2);

            //查询当日sku销量是否存在(同一平台、账号、仓库、分类、销售员、国家)
            $map = ['md5_str' => $key];
            $obj = $this->skuModel->field('uptime,id,qty')->where($map)->find();
            $arr = replace_query($obj);
            $id  = $arr['id'];

            //删除重复数据
            if ($id && $time > strtotime($arr['uptime'])) {
                $this->skuModel->where('id', $id)->delete();
            }

            //更新数据
            if ($id && $time <= strtotime($arr['uptime'])) {
                $qty  = $val['nums'] + $arr['qty'];
                $save = ['qty' => $qty];
                if ($val['parent']) $save['parent'] = $val['parent'];
                if ($val['name']) $save['name'] = $val['name'];
                if ($val['thumb']) $save['thumb'] = $val['thumb'];
                if ($val['category_id']) $save['category_id'] = $val['category_id'];
                if ($val['category_child_id']) $save['category_child_id'] = $val['category_child_id'];
                $this->skuModel->where('id', $id)->update($save);
            } else {
                $this->skuModel->insert($add);
            }
            unset($detailInfo[$key]);
        }
        return '';
    }


    /**
     * 删除指定日期的sku脏数据
     * @param $startTime 开始时间
     * @param $endTime   结束时间
     * @return string $msg 操作信息
     */
    public function deleteData($startTime, $endTime)
    {
        $msg = "---开始删除脏数据 ---\n";
        if (!$startTime || !$endTime) {
            $msg .= "---缺少正确时间参数 ---\n";
            return $msg;
        }
        $map = [
            'a.datetime' => ['between', [$startTime, $endTime]]
        ];

        $skuArr = $this->skuModel->alias('a')
            ->join('erp_order_detail b', 'a.sku = b.sku', 'LEFT')
            ->distinct(true)
            ->where($map)
            ->where('b.sku is null')
            ->column('a.sku');

        $query   = ['datetime' => ['between', [$startTime, $endTime]]];
        $num     = count($skuArr);
        $success = 0;
        $error   = 0;
        for ($i = 0; $i < $num; $i++) {
            if (!$skuArr[$i]) continue;
            $query['sku'] = $skuArr[$i];
            $result       = $this->skuModel->where($query)->delete();
            if ($result) {
                $success++;
            } else {
                $error++;
            }
            unset($skuArr[$i]);
        }
        $msg .= "需要删除的数据:{$num},成功:{$success},失败:{$error}\n";
        $msg .= "---删除脏数据完毕 ---\n";
        return $msg;
    }
}