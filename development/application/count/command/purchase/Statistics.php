<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    leo
 */

namespace app\count\command\purchase;

use app\common\model\EbayCgarrivaldetail;
use app\common\model\EbayCgorder;
use app\common\model\EbayCgOrderDetail;
use app\common\model\EbayCgOrderNote;
use app\common\model\EbayCgpaydata;
use app\common\model\EbayCgreturn;
use app\count\model\PayRevenueStatisticsOrdersn;
use app\count\model\PayRevenueStatisticsSku;

use think\console\Input;
use think\console\Output;

/**
 * @name 应付款报表供应商付款统计
 * @package app\count\command\purchase
 */
class Statistics
{
    /**
     * @desc  采购单付款统计
     * @Author leo
     */
    public function payRevenueStatistics(Input $input)
    {
        ini_set('memory_limit', '512M');
        if (!IS_CLI) {
            echo "请求异常! \n ";
            return;
        }
        $cgorderModel         = new EbayCgorder();
        $cgpaydataModel       = new EbayCgpaydata();
        $cgarrivaldetailModel = new EbayCgarrivaldetail();
        $cgreturnModel        = new EbayCgreturn();
        $cgordernodeModel     = new EbayCgOrderNote();

        $statrtime = strtotime("-12 hour");

        $date = (int) $input->getArgument('date');

        if ($date > 0) {
            $statrtime = strtotime("-{$date} hour");
        }


        $map['addtime'] = ['egt', $statrtime];
        $map['status']  = ['in', [2, 3, 4, 5, 9]];
        $cgordersn      = [];
        $factoryArr     = [];
        //TODO:采购单表 取12小时
        $cgorderFactoryArr = $cgorderModel->field("from_unixtime(addtime, '%Y-%m-%d') as uptime,factory")
            ->where($map)->group("uptime,factory")->select()->toArray();
        $factoryArr        = array_merge($cgorderFactoryArr, $factoryArr);
        unset($map['status']);

        //TODO:付款数据表的付款时间 （取12小时）
        $cgpayOrdersn = $cgpaydataModel->field('ordersn')->where($map)->select()->toArray();
        $cgpayOrdersn = array_column($cgpayOrdersn, 'ordersn');
        if (!empty($cgpayOrdersn)) {
            $cgordersn = array_merge($cgordersn, $cgpayOrdersn);
        }

        //TODO:采购退换货表 更新了 按 overtime 时间 （取12小时）
        $cgreturnMap['overtime'] = ['egt', $statrtime];
        $cgreturnOrdersn         = $cgreturnModel->field('ordersn')->where($cgreturnMap)->select()->toArray();
        $cgreturnOrdersn         = array_column($cgreturnOrdersn, 'ordersn');
        if (!empty($cgreturnOrdersn)) {
            $cgordersn = array_merge($cgordersn, $cgreturnOrdersn);
        }

        //TODO:采购单日志表 更新了 type=2 按 addtime 时间 （取12小时）
        $map['type']        = '2';
        $cgordernodeOrdersn = $cgordernodeModel->field("DISTINCT(ordersn) as ordersn")->where($map)->select()->toArray();
        if (!empty($cgordernodeOrdersn)) {
            $cgordernodeOrdersn = array_column($cgordernodeOrdersn, 'ordersn');
            $cgordersn          = array_merge($cgordersn, $cgordernodeOrdersn);
        }

        //TODO:到货单表 质检时间 （如果没有则是添加时间） （取24小时）
        $statrtime                  = strtotime("-1 day");
        $arrivaldetailMap['qctime'] = ['egt', $statrtime];
        $cgarrivaldetailOrdersn     = $cgarrivaldetailModel->field('ordersn')->where($arrivaldetailMap)->select()->toArray();
        $cgarrivaldetailOrdersn     = array_column($cgarrivaldetailOrdersn, 'ordersn');
        if (!empty($cgarrivaldetailOrdersn)) {
            $cgordersn = array_merge($cgordersn, $cgarrivaldetailOrdersn);
        }

        $cgordersn = array_unique($cgordersn);
        if (!empty($cgordersn)) {
            $where['status']   = ['neq', '100'];
            $where['ordersn']  = ['in', $cgordersn];
            $cgorderFactoryArr = $cgorderModel->field("from_unixtime(addtime, '%Y-%m-%d') as uptime,factory")
                ->where($where)->group("uptime,factory")->select()->toArray();
            unset($cgordersn);
            $factoryArr = array_merge($cgorderFactoryArr, $factoryArr);
            $factoryArr = array_unique($factoryArr, SORT_REGULAR);
        }

        $this->p("Task start");
        $this->addRevenueStatistics($factoryArr);
        $this->p('End of the task');
    }

    public function payRevenueStatisticsByDay(Input $input)
    {
        ini_set('memory_limit', '1024M');
        $date      = $input->getArgument('date');
        $end_date  = $input->getArgument('end_date');
        $statrtime = $date ? strtotime($date) : strtotime("-60 day");

        if($end_date){
            $endtime = strtotime($end_date." 23:59:59");
            $map['addtime'][] = ['elt', $endtime];
        }

        $cgorderModel   = new EbayCgorder();
        $map['addtime'][] = ['egt', $statrtime];
        $map['status']  = ['in', [2, 3, 4, 5, 9]];

        $factoryArr = $cgorderModel->field("from_unixtime(addtime, '%Y-%m-%d') as uptime,factory")
            ->where($map)->group("uptime,factory")->order('addtime desc')->select()->toArray();
        $factoryArr = replace_query($factoryArr);
        $pageshow   = 200;
        $count      = count($factoryArr);
        $pCount     = ceil($count / $pageshow);
        $this->p("Task start count {$count}, Total {$pCount} batch,every {$pageshow} strip");
        for ($page = 0; $page <= $pCount; $page++) {
            if ($page <= 0) {
                $page = 1;
            }
            $pagesize = ($page - 1) * $pageshow;

            $newArray = array_slice($factoryArr, $pagesize, $pageshow);
            $this->addRevenueStatistics($newArray);

            $this->p("The {$page} batch complete,limit($pagesize,$pageshow)");
        }

        $this->p('End of the task');

    }

    public function addRevenueStatistics($newArray)
    {
        if (empty($newArray)) {
            return false;
        }
        $cgorderModel       = new EbayCgorder();
        $ebayCgpaydataModel = new EbayCgpaydata();
        $cgOrderDetilModel  = new EbayCgOrderDetail();
        $payRevenunSku      = new PayRevenueStatisticsSku();
        $payRevenunOrdersn  = new PayRevenueStatisticsOrdersn();
        foreach ($newArray as $key => $val) {

            $day                  = $val['uptime'];
            $where                = [];
            $where['a.addtime'][] = ['gt', strtotime($day . " 00:00:00")];
            $where['a.addtime'][] = ['elt', strtotime($day . " 23:59:59")];
            $where['a.factory']   = $val['factory'];
            $where['a.status']    = ['in', [2, 3, 4, 5, 9]];
            $field                = 'sum(b.goods_count*b.goods_price) as amount,a.shipfee,a.factory,a.storeid,a.ordersn,a.addtime,a.cguser,a.amount as tt,a.ordertype,a.status,a.getstatus,a.paytype';
            $mainData             = $cgorderModel->alias('a')->join('ebay_cgorderdetail b', 'a.ordersn=b.ordersn')
                ->field($field)->where($where)->group("a.ordersn,a.storeid")
                ->select()->toArray();
            if (empty($mainData)) {
                continue;
            }
            $mainData = replace_query($mainData);

            foreach ($mainData as $val) {

                $payable           = $ebayCgpaydataModel->field('sum(payamount) as paid')->where(['ordersn' => ['in', $val['ordersn']], 'type' => '1'])->find();
                $refund            = $ebayCgpaydataModel->field('sum(payamount) as paid')->where(['ordersn' => ['in', $val['ordersn']], 'type' => '5'])->find();
                $loss              = $ebayCgpaydataModel->field('sum(payamount) as loss')->where(['ordersn' => ['in', $val['ordersn']], 'type' => ['in', ['2', '3']]])->find();
                $equivalent_change = $ebayCgpaydataModel->field('sum(payamount) as equivalent')->where(['ordersn' => ['in', $val['ordersn']], 'type' => '6'])->find();

                $payable           = replace_query($payable);
                $refund            = replace_query($refund);
                $loss              = replace_query($loss);
                $loss['loss']      = abs($loss['loss']);
                $equivalent_change = replace_query($equivalent_change);

                $revenueds = 0;//损耗
                $revenueds += $loss['loss'];
                $paid      = $payable['paid'] ?: 0;
                $refund    = $refund['paid'] ?: 0;
                $total     = $val['amount'] + $val['shipfee'];
                $collected = 0;//代收款

                $saveOrdersn  = [
                    'ordersn'           => $val['ordersn'],
                    'partner_id'        => $val['factory'],
                    'storeid'           => $val['storeid'],
                    'amount'            => round($total, 3),
                    'paid'              => round($paid, 3),
                    'wait_pay'          => round($total - $paid, 3),
                    'revenued'          => $revenueds,
                    'refound'           => round($refund, 3),
                    'purchase_time'     => $val['addtime'],
                    'ordertype'         => $val['ordertype'],
                    'loss'              => $loss['loss'],
                    'equivalent_change' => $equivalent_change['equivalent'] ?? 0, //等值换
                    'collected'         => $collected,
                    'pay_type'          => $val['paytype'] ?: 0,
                    'cguser'            => $val['cguser'],
                    'ship_fee'          => round($val['shipfee'],3),
                ];
                $cgdeField    = 'id,goods_sn,goods_name,goods_price,goods_count,goods_count*goods_price as detiltotal,goods_count1,goods_count-goods_count1 as abnormal_qty';
                $cgOrderDetil = $cgOrderDetilModel->field($cgdeField)->where(['ordersn' => $val['ordersn']])->group('goods_sn')->select()->toArray();
                $goodsCount   = array_sum(array_column($cgOrderDetil, 'detiltotal'));
                $goodsqty     = array_sum(array_column($cgOrderDetil, 'goods_count'));
                $saveSku      = [];

                if ($goodsqty > 0) {
                    $goods_price_ff = $val['shipfee'] > 0 ? ($val['shipfee'] / $goodsqty) : 0;
                    $goods_price_ff = round($goods_price_ff, 3);
                } else {
                    $goods_price_ff = 0;
                }

                foreach ($cgOrderDetil as $detil) {
                    $goods_price = round($goods_price_ff + $detil['goods_price'], 3);
                    $amount      = round($detil['goods_count'] * $goods_price, 3);
                    if ($goodsCount > 0) {
                        $depaid = round($detil['detiltotal'] / $goodsCount * $paid, 3);
                        $deloss = round($detil['detiltotal'] / $goodsCount * $loss['loss'], 3);
                    } else {
                        $depaid = 0;
                        $deloss = 0;
                    }

                    $revenued    = round($detil['goods_count1'] * $goods_price, 3);
                    $revenueds   += $revenued;
                    $revenued    += $deloss;
                    $decollected = 0;
                    if ($val['status'] == '4') {
                        $decollected = 0;
                    } elseif ($val['getstatus'] == '3') {
                        $decollected = 0;
                    } elseif ($val['getstatus'] == '2') {
                        $decollected = round($amount - $revenued, 3);
                    } elseif ($val['getstatus'] == '1') {
                        $decollected = round($amount, 3);
                    }

                    $saveSku[] = [
                        'detailid'          => $detil['id'],
                        'ordersn'           => $val['ordersn'],
                        'partner_id'        => $val['factory'],
                        'sku'               => $detil['goods_sn'],
                        'storeid'           => $val['storeid'],
                        'purchase_time'     => $val['addtime'],
                        'goods_name'        => $detil['goods_name'],
                        'goods_price'       => $goods_price,
                        'qty'               => $detil['goods_count'],
                        'amount'            => $amount,
                        'paid'              => $depaid,
                        'wait_pay'          => round($amount - $depaid, 3),
                        'revenued'          => $revenued,
                        'refound'           => 0,
                        'abnormal_qty'      => $detil['abnormal_qty'],
                        'abnormaltotal'     => round($detil['abnormal_qty'] * $goods_price, 3),
                        'ordertype'         => $val['ordertype'],
                        'loss'              => $deloss,
                        'equivalent_change' => 0, //等值换
                        'collected'         => $decollected,
                        'cguser'            => $val['cguser'],
                        'pay_type'          => $val['paytype'] ?: 0,
                    ];
                }

                $saveOrdersn['revenued'] = round($revenueds, 3);

                if ($val['status'] == '4') {
                    $saveOrdersn['collected'] = 0;
                } elseif ($val['getstatus'] == '3') {
                    $saveOrdersn['collected'] = 0;
                } elseif ($val['getstatus'] == '2') {
                    $saveOrdersn['collected'] = round($total - $revenueds, 3);
                } elseif ($val['getstatus'] == '1') {
                    $saveOrdersn['collected'] = round($total, 3);
                }

                $id = $payRevenunOrdersn->field('id')->where(['partner_id' => $val['factory'], 'ordersn' => $val['ordersn']])->find();
                $id = replace_query($id);
                if ($id) {
                    $payRevenunOrdersn->where(['id' => $id['id']])->update($saveOrdersn);
                } else {
                    $payRevenunOrdersn->insert($saveOrdersn);
                }
                $countRef = array_sum(array_column($saveSku, 'abnormaltotal'));
                foreach ($saveSku as $key => $skuStr) {
                    if ($countRef > 0) {
                        $skuStr['refound']           = round($skuStr['abnormaltotal'] / $countRef * $refund, 3);
                        $skuStr['equivalent_change'] = round($skuStr['abnormaltotal'] / $countRef * $equivalent_change['equivalent'], 3);
                    } else {
                        $skuStr['refound']           = round($skuStr['amount'] / $total * $refund, 3);
                        $skuStr['equivalent_change'] = round($skuStr['amount'] / $total * $equivalent_change['equivalent'], 3);
                    }
                    unset($skuStr['abnormaltotal']);
                    $skuid = $payRevenunSku->field('id')->where(['ordersn' => $skuStr['ordersn'], 'sku' => $skuStr['sku']])->find();
                    $skuid = replace_query($skuid);
                    if ($skuid) {
                        $payRevenunSku->where(['id' => $skuid['id']])->update($skuStr);
                    } else {
                        $payRevenunSku->insert($skuStr);

                    }
                }
                $this->p("update success partnerId:{$val['factory']} ordersn:{$val['ordersn']}");
            }
        }
        return true;
    }

    /**
     * @desc 修复主表的采购员
     * @Author leo
     */
    public function repairCguser(){
        $payRevenunOrdersn  = new PayRevenueStatisticsOrdersn();
        $where = [
            'a.cguser' => ''
        ];
        $group = 'a.ordersn';
        $join[] = ['pay_revenue_statistics_sku b','a.ordersn = b.ordersn'];
        $count = $payRevenunOrdersn->alias('a')
            ->join($join)
            ->where($where)
            ->group($group)
            ->count();

        $field = ['a.ordersn','b.cguser'];
        $size = 500;
        $num = ceil($count/$size);
        for($i = 0; $i < $num; $i++)
        {
            echo $i.'组--'."\n";
            $data = $payRevenunOrdersn->alias('a')
                ->join($join)
                ->field($field)
                ->where($where)
                ->limit($i*$size, $size)
                ->group($group)
                ->select()->toArray();

            if (!$data)
            {
                continue;
            }

            foreach($data as $value)
            {
                $saveData  = ['cguser' => $value['cguser']];
                $payRevenunOrdersn->where(['ordersn' => $value['ordersn']])->update($saveData);
            }

            if ($i % 5 == 0)
            {
                sleep(1);
            }

            usleep(500000);

        }
        echo "success " . date('Y-m-d H:i:s');
        return true;
    }

    /**
     * @desc 修复运费
     * php7 think purchase -m statistics -a repairShip
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author Shawn
     * @date 2019/2/12
     */
    public function repairShip(){
        $payRevenunOrdersn  = new PayRevenueStatisticsOrdersn();
        $cgorderModel       = new EbayCgorder();
        $group = 'ordersn';
        $count = $payRevenunOrdersn
            ->group($group)
            ->count();
        $field = 'ordersn';
        $size = 500;
        $num = ceil($count/$size);
        echo "total page：".$num."\n";
        for($i = 0; $i < $num; $i++)
        {
            echo $i.'组--'."\n";
            $data = $payRevenunOrdersn->field($field)
                ->limit($i*$size, $size)
                ->group($group)
                ->order('id','desc')
                ->select()
                ->toArray();
            if (!$data)
            {
                continue;
            }

            foreach($data as $value)
            {
                $shipData = $cgorderModel->where('ordersn',$value['ordersn'])->group("ordersn")
                    ->column('shipfee');
                if (!$shipData)
                {
                    continue;
                }
                $saveData  = ['ship_fee' => $shipData[0]];
                $payRevenunOrdersn->where(['ordersn' => $value['ordersn']])->update($saveData);
            }

            if ($i % 5 == 0)
            {
                sleep(1);
            }

            usleep(500000);

        }
        echo "success " . date('Y-m-d H:i:s');exit;
    }
    /**
     * 打印函数
     */
    public function p($data)
    {
        //如果是命令行
        if (!empty($_SERVER['argv'])) {
            print_r($data);
            echo PHP_EOL;
            return;
        }
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        return;
    }
}