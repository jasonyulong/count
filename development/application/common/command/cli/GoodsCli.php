<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    jason
 */

namespace app\common\command\cli;

use think\console\Input;
use think\console\Output;
use app\common\model\Goods;
use app\common\model\ErpGoods;
use app\common\model\GoodsAudit;
use app\count\model\GoodsDevelop;
use app\common\model\GoodsCombine;
use app\common\model\SysGoodsStep;
use app\common\model\ErpGoodsCombine;
use app\common\model\ErpModelFactory;
use think\Config;
use think\cache\driver\Redis;

/**
 * 商品表数据同步
 * Class Common
 * @package app\common\command\cli
 */
class GoodsCli
{
    /**
     * @name ebay_goods表所要查询的字段
     * @var string
     * @author jason
     * @date 2019/2/22
     */
    private $field = 'type,goods_sn,BtoBnumber,goods_name,goods_cost,goods_weight,goods_width,goods_length,goods_height,goods_price,propose_price,goods_pic,kfuser,cguser,audittime';
    private $developField = 'goods_id,type,goods_sn,BtoBnumber,status,goods_name,goods_cost,goods_weight,goods_width,goods_length,goods_height,goods_price,propose_price,goods_pic,kfuser,cguser,add_time,stop_time';
    private $input;
    private $output;
    private $erp_goods; //erp_goods
    private $ebay_goods;
    private $ebay_goods_audit;  //ebay_goods_audit

    private $ebay_goods_combine;
    private $erp_goods_combine;

    /**
     * redis链接句柄
     * @var Redis object
     */
    private $redis;

    /**
     * 构造函数
     * Common constructor.
     * @param Input $input 输入
     * @param Output $output 输出
     */
    public function __construct(Input $input, Output $output)
    {
        $this->input            = $input;
        $this->output           = $output;
        $this->erp_goods        = new ErpGoods();
        $this->ebay_goods       = new Goods();
        $this->goods_develop    = new GoodsDevelop();
        $this->sys_goods_step   = new SysGoodsStep();
        $this->ebay_goods_audit = new GoodsAudit();

        $this->erp_goods_combine  = new ErpGoodsCombine();
        $this->ebay_goods_combine = new GoodsCombine();

        $this->redis = new Redis(Config::get('redis'));
    }

    /**
     * @name 如果 传了开始时间就是从当前开始时间---结束时间【结束时间不给默认是当前的'Y-m-d 23:59:59'】------跟新某一天有在ebay_goods表更新过的SKU
     * 还有一种情况就是开始时间跟结束时间都不给，那么就是默认当前的结束时间【'Y-m-d 23:59:59'】;uptime小于等于这个结束时间------就是更新所有的
     * @author jason
     * @date 2019/2/22
     * @params $start 开始时间
     * @params $end 结束时间
     * @params $type 是同步erp_goods表还是erp_goods_develop表
     * @return string
     */
    public function sync()
    {
        ini_set('memory_limit', '2048M');
        $options = $this->input->getOptions();
        $type    = $options['type'] ?? '';     //type=develop就是同步ebay_goods_audit的数据，为空或不传就是跟新ebay_goods表的数据
        $start   = $options['start'] ?? '';   //开始时间

        if (!empty($start)) $start = strtotime($start);
        $end = $options['end'] ?? '';   //结束时间
        if (!empty($end)) $end = strtotime($end);

        // 写入本次运行时间
        $countdown = $this->redis->handler()->get(sprintf(Config::get('redis.command_goods_countdown'), $type));
        $this->redis->handler()->set(sprintf(Config::get('redis.command_goods_countdown'), $type), time());
        // 取上次更新时间作为开始时间
        if ($countdown && $countdown > strtotime(date('Y-m-d 00:00:00')) && empty($start)) {
            $start = $countdown - 30;
        }

        switch ($type) {
            case 'develop':
                $this->output->writeln("---start execute time:" . date('Y-m-d H:i:s') . "---");
                $this->setErpGoodsDevelop($start, $end);
                $this->output->writeln("---end execute time:" . date('Y-m-d H:i:s') . "---");
                break;
            case 'combine': // ebay_goods_combine
                $this->output->writeln("---start execute time:" . date('Y-m-d H:i:s') . "---");
                $this->setErpGoodsCombine($start, $end);
                $this->output->writeln("---end execute time:" . date('Y-m-d H:i:s') . "---");
                break;
            default: // ebay_goods 表
                $this->output->writeln("---start execute time:" . date('Y-m-d H:i:s') . "---");
                $this->setErpGoods($start, $end);
                $this->output->writeln("---end execute time:" . date('Y-m-d H:i:s') . "---");
                break;
        }

        return 'success';
    }

    /**
     * @name 把ebay_goods_audit表的数据更新或添加到erp_goods_develop表中
     * @param $start
     * @param $end
     * @return string
     */
    private function setErpGoodsDevelop($start, $end)
    {
        $goodsAuditModel   = $this->ebay_goods_audit;  //开发表
        $GoodsDevelopModel = $this->goods_develop;    //erp_goods_develop
        $SysGoodsStepModel = $this->sys_goods_step;    //sys_goods_step
        $developField      = $this->developField;    //开发表字段
        //开始时间是否存在
        if (!empty($start)) $start = date('Y-m-d 00:00:00', $start);
        //结束时间是否存在
        if (!empty($end)) {
            $end = date('Y-m-d 23:59:59', $end);
        } else {
            $end = date('Y-m-d 23:59:59');
        }
        //如果开始时间跟结束时间都有的话
        if (!empty($start) && !empty($end)) {
            $where['uptime'] = ['BETWEEN', [$start, $end]];
        }
        //如果没有开始时间的话
        if (empty($start) && !empty($end)) {
            $where['uptime'] = ['ELT', $end];
        }
        $where['status'] = ['NEQ', -1];
        //ebay_goods_audit表所有的个数
        $goodsCount = $goodsAuditModel->where($where)->count();

        $this->output->writeln($goodsAuditModel->getLastSql());

        if ($goodsCount == 0) {
            $str = "goods_count:" . $goodsCount . PHP_EOL;
            return $str;
        }
        $start_select = 0;
        $select_count = 1000;

        $loop = 1;
        // todo: ------------ while loop start -----------------
        while ($start_select < $goodsCount) {
            echo "round {$loop} start" . PHP_EOL;
            $exe_start_time = microtime(true);
            $limit          = "{$start_select}, {$select_count}";
            $goodsInfo      = $goodsAuditModel->where($where)->field($developField)->limit($limit)->select()->toArray();
            $goods_sn       = array_column($goodsInfo, 'goods_sn');
            //一次把erp_goods表中的数据查出来
            $is_goods = $GoodsDevelopModel->where(['goods_sn' => ['IN', $goods_sn]])->field('goods_sn,id')->column('id', 'goods_sn');
            $time     = date('Y-m-d H:i:s');
            foreach ($goodsInfo as $k => $v) {
                if (preg_match("/\s/", $v['goods_sn'])) {
                    continue;
                }
                $add                      = [];
                $add['uptime']            = $time;
                $add['type']              = intval($v['type'] ?? '0');   //0-普通产品 1-多属性产品 2-多属性单品
                $add['goods_sn']          = $v['goods_sn'] ?? '';    //商品编号
                $add['goods_parent']      = $v['BtoBnumber'] ?? '';  //父级编码
                $add['goods_name']        = $v['goods_name'] ?? '';    //商品名称
                $add['goods_cost']        = $v['goods_cost'] ?? '0.00';    //商品成本(￥)
                $add['goods_weight']      = $v['goods_weight'] ?? '';    //商品重量(g)
                $add['goods_length']      = $v['goods_length'] ?? '';    //商品长(cm)
                $add['goods_width']       = $v['goods_width'] ?? '';      //商品宽(cm)
                $add['goods_height']      = $v['goods_height'] ?? '';    //商品高(cm)
                $add['goods_price']       = $v['goods_price'] ?? '0.00';  //建议售价
                $add['goods_stock_price'] = $v['propose_price'] ?? '0.00';  //建议采购价
                $add['goods_iamge']       = $v['goods_pic'] ?? '';                  //商品图片
                $add['develop_user']      = $v['kfuser'] ?? '';                    //开发人员
                $add['stock_user']        = $v['cguser'] ?? '';                      //采购人员
                //同步开发表的数据
                $statu             = intval($v['status'] ?? 0);
                $add['status']     = $statu;       //开发状态
                $add['start_time'] = intval($v['add_time'] ?? 0);   //开发时间
                //审核时间
                $add['review_time'] = intval($v['stop_time']) ?? 0;
                //最后状态变更时间
                if (!empty($v['BtoBnumber'])) {
                    $statusTime = $SysGoodsStepModel->where(['sku' => $v['BtoBnumber'], 'step_id' => $statu])->field('sku,create_time')->find();
                } else {
                    $statusTime = $SysGoodsStepModel->where(['sku' => $v['goods_sn'], 'step_id' => $statu])->field('sku,create_time')->find();
                }
                if (!empty($statusTime)) {
                    $add['status_time'] = strtotime($statusTime['create_time']);
                } else {
                    $add['status_time'] = intval($v['add_time']) ?? 0;
                }
                //erp_goods_develop表中是否存在
                if (empty($is_goods[$v['goods_sn']])) {
                    $res = $GoodsDevelopModel->insert($add);
                } else {
                    $res = $GoodsDevelopModel->where(['goods_sn' => $v['goods_sn']])->update($add);
                }
                $resul = $res ? "success" : 'Failure';
                $this->output->writeln(sprintf("%s {$resul}", date('Y-m-d')));
            }
            $start_select += $select_count;
            echo "round {$loop} bingo!, " . (microtime(true) - $exe_start_time) . "s" . PHP_EOL;
            $loop++;
        }
        // todo: ------------ while loop end -----------------
    }

    /**
     * @name 把ebay_goods表的数据更新或添加到erp_goods表中
     * @param $data
     * @return string
     */
    private function setErpGoods($start, $end)
    {
        $goodsModel    = $this->ebay_goods;  //商品表
        $erpGoodsModel = $this->erp_goods;    //erp_goods
        $field         = $this->field;  //商品表的字段
        //开始时间是否存在
        if (!empty($start)) $start = date('Y-m-d 00:00:00', $start);
        //结束时间是否存在
        if (!empty($end)) {
            $end = date('Y-m-d 23:59:59', $end);
        } else {
            $end = date('Y-m-d 23:59:59');
        }
        //如果开始时间跟结束时间都有的话
        if (!empty($start) && !empty($end)) {
            $where['uptime'] = ['BETWEEN', [$start, $end]];
        }
        //如果没有开始时间的话
        if (empty($start) && !empty($end)) {
            $where['uptime'] = ['ELT', $end];
        }
        $goodsCount = $goodsModel->where($where)->count();


        if ($goodsCount == 0) {
            $str = "goods_count:" . $goodsCount . PHP_EOL;
            return $str;
        }
        $start_select = 0;
        $select_count = 1000;

        $loop = 1;
        // todo: ------------ while loop start -----------------
        while ($start_select < $goodsCount) {
            echo "round {$loop} start" . PHP_EOL;
            $exe_start_time = microtime(true);
            $limit          = "{$start_select}, {$select_count}";
            $goodsInfo      = $goodsModel->where($where)->field($field)->limit($limit)->select()->toArray();
            $goods_sn       = array_column($goodsInfo, 'goods_sn');
            //一次把erp_goods表中的数据查出来
            $is_goods = $erpGoodsModel->where(['goods_sn' => ['IN', $goods_sn]])->field('goods_sn,id')->column('id', 'goods_sn');
            $time     = date('Y-m-d H:i:s');
            foreach ($goodsInfo as $k => $v) {
                if (preg_match("/\s/", $v['goods_sn'])) {
                    continue;
                }
                $add                      = [];
                $add['uptime']            = $time;
                $add['type']              = intval($v['type'] ?? '0');   //0-普通产品 1-多属性产品 2-多属性单品
                $add['goods_sn']          = $v['goods_sn'] ?? '';    //商品编号
                $add['goods_parent']      = $v['BtoBnumber'] ?? '';  //父级编码
                $add['goods_name']        = $v['goods_name'] ?? '';    //商品名称
                $add['goods_cost']        = $v['goods_cost'] ?? '0.00';    //商品成本(￥)
                $add['goods_weight']      = $v['goods_weight'] ?? '';    //商品重量(g)
                $add['goods_length']      = $v['goods_length'] ?? '';    //商品长(cm)
                $add['goods_width']       = $v['goods_width'] ?? '';      //商品宽(cm)
                $add['goods_height']      = $v['goods_height'] ?? '';    //商品高(cm)
                $add['goods_price']       = $v['goods_price'] ?? '0.00';  //建议售价
                $add['goods_stock_price'] = $v['propose_price'] ?? '0.00';  //建议采购价
                $add['goods_iamge']       = $v['goods_pic'] ?? '';                  //商品图片
                $add['develop_user']      = $v['kfuser'] ?? '';                    //开发人员
                $add['stock_user']        = $v['cguser'] ?? '';                      //采购人员
                $add['review_time']       = intval($v['audittime'] ?? 0);           //审核时间
                if (empty($is_goods[$v['goods_sn']])) {
                    $res = $erpGoodsModel->insert($add);
                } else {
                    $res = $erpGoodsModel->where(['goods_sn' => $v['goods_sn']])->update($add);
                }
                $resul = $res ? "success" : 'Failure';
                $this->output->writeln(sprintf("%s {$resul}", date('Y-m-d' . '---' . $v['goods_sn'])));
            }
            $start_select += $select_count;
            echo "round {$loop} bingo!, " . (microtime(true) - $exe_start_time) . "s" . PHP_EOL;
            $loop++;
        }
        // todo: ------------ while loop end -----------------
    }


    /**
     * 把 ebay_productscombine 表的数据更新或添加到 erp_goods_combine 表中
     * @author lamkakyun
     * @date 2019-03-19 10:45:17
     * @return void
     */
    public function setErpGoodsCombine($start, $end)
    {
        $field = 'goods_sn, notes, goods_sncombine, salesuser, cguser, kfuser';

        $where = [];
        //如果开始时间跟结束时间都有的话
        if (!empty($start) && !empty($end)) {
            $where['add_time'] = ['BETWEEN', [$start, $end]];
        }
        //如果没有开始时间的话
        if (empty($start) && !empty($end)) {
            $where['add_time'] = ['ELT', $end];
        }
        //如果没有结束时间的话
        if (!empty($start) && empty($end)) {
            $where['add_time'] = ['EGT', $start];
        }

        $goods_combine_count = $this->ebay_goods_combine->where($where)->count();

        $str = "goods_combine_count:" . $goods_combine_count . PHP_EOL;

        $this->output->writeln($this->ebay_goods_combine->getLastSql());

        if ($goods_combine_count == 0) return $str;
        else echo $str;

        $start_select = 0;
        $select_count = 1000;

        $loop = 1;

        while ($start_select < $goods_combine_count) {
            echo "round {$loop} start" . PHP_EOL;

            $exe_start_time     = microtime(true);
            $limit              = "{$start_select}, {$select_count}";
            $goods_combine_info = $this->ebay_goods_combine->where($where)->field($field)->limit($limit)->select()->toArray();

            $goods_sn = array_column($goods_combine_info, 'goods_sn');
            //一次把erp_goods表中的数据查出来
            $is_goods = $this->erp_goods_combine->where(['goods_sn' => ['IN', $goods_sn]])->field('goods_sn,id')->column('id', 'goods_sn');

            $time = time();
            foreach ($goods_combine_info as $k => $v) {
                if (preg_match("/\s/", $v['goods_sn'])) continue;

                $add = [
                    'goods_sn'      => $v['goods_sn'],
                    'goods_combine' => $v['goods_sncombine'],
                    'goods_name'    => $v['notes'],
                    'sales_label'   => $v['salesuser'],
                    'develop_user'  => $v['kfuser'],
                    'stock_user'    => $v['cguser'],
                    'uptime'        => time(),
                ];

                if (empty($is_goods[$v['goods_sn']])) {
                    $res = $this->erp_goods_combine->insert($add);
                } else {
                    $res = $this->erp_goods_combine->where(['goods_sn' => $v['goods_sn']])->update($add);
                }

                $resul = $res !== false ? "success" : 'Failure';
                $this->output->writeln(sprintf("%-35s {$resul}", date('Y-m-d') . '---' . $v['goods_sn']));
            }

            $start_select += $select_count;
            echo "round {$loop} bingo!, " . (microtime(true) - $exe_start_time) . "s" . PHP_EOL;
            $loop++;
        }
    }
}