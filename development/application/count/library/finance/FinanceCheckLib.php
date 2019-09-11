<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    yang
 */

namespace app\count\library\finance;

use app\common\library\FMSAuth;
use app\common\library\ToolsLib;
use app\count\model\FinanceCheck;

/**
 * Class FinanceCheckLib
 * @package app\count\library\finance
 */
class FinanceCheckLib
{
    private static $instance = null;


    public function __construct()
    {
        $this->finance_check_model = new FinanceCheck();
    }

    /**
     * single
     * @AUTHOR: Lamkakyun
     * @DATE: 2019-03-29 14:16:32
     */
    public static function getInstance(): FinanceCheckLib
    {
        if (!static::$instance) {
            static::$instance = new FinanceCheckLib();
        }
        return static::$instance;
    }


    /**
     * get 抽查类型
     * @author lamkakyun
     * @date 2019-03-29 14:18:47
     * @return void
     */
    public function getCheckType()
    {
        // 抽查类型 1.退款 2.销售额 3.成本4.运费5.包材费6.paypal费7.佣金8预估利润9.平台杂费10.补发11.实际利润
        $data = [
            '1'  => '退款',
            '2'  => '销售额',
            '3'  => '成本',
            '4'  => '运费',
            '5'  => '包材费',
            '6'  => 'paypal费',
            '7'  => '佣金',
            '8'  => '预估利润',
            '9'  => '平台杂费',
            '10' => '补发',
            '11' => '实际利润',
        ];

        return $data;
    }


    /**
     * get 抽查状态
     * @author lamkakyun
     * @date 2019-03-29 14:19:26
     * @return void
     */
    public function getCheckStatus()
    {
        // 状态 1 正常 2 未解决 3 已解决
        $data = [
            '1' => '正常',
            '2' => '(异常)未解决',
            '3' => '(异常)已解决',
        ];
        return $data;
    }


    /**
     * get 抽查时间类型 
     * @author lamkakyun
     * @date 2019-03-29 14:21:05
     * @return void
     */
    public function getCheckTimeType()
    {
        // 抽查时间 类型 1 进系统时间 2 发货时间 3 退款时间 4 确定利润时间
        $data = [
            '1' => '进系统时间',
            '2' => '发货时间',
            '3' => '退款时间',
            '4' => '确定利润时间',
        ];
        return $data;
    }


    /**
     * 获取抽查列表
     * @author lamkakyun
     * @date 2019-03-29 14:25:35
     * @return void
     */
    public function getCheckList($params)
    {
        $select_start = ($params['p'] - 1) * $params['ps'];
        $limit = "{$select_start}, {$params['ps']}";
        $order = 'create_time DESC';
        $fields = '*';

        $where = [];
        if (isset($params['check_type']) && !empty(array_filter($params['check_type'], function($v) {return !empty($v);}))) $where['check_type'] = ['IN', $params['check_type']];
        if (isset($params['check_status']) && !empty(array_filter($params['check_status'], function($v) {return !empty($v);}))) $where['check_status'] = ['IN', $params['check_status']];

        $start_time = (isset($params['start_time']) && !empty($params['start_time'])) ? strtotime($params['start_time']) : false;
        $end_time = (isset($params['end_time']) && !empty($params['end_time'])) ? strtotime($params['end_time'] . ' 23:59:59') : false;
        if ($start_time && $end_time) $where['create_time'] = [['EGT', $start_time], ['ELT', $end_time]];
        else
        {
            if ($start_time) $where['create_time'] = ['EGT', $start_time];
            if ($end_time) $where['create_time'] = ['ELT', $end_time];
        }

        $count = $this->finance_check_model->where($where)->count();
        $list = $this->finance_check_model->where($where)->field($fields)->order($order)->limit($limit)->select()->toArray();

        if (isset($params['debug']) && $params['debug'] == 'sql') var_dump($this->finance_check_model->getLastSql());

        return ['list' => $list, 'count' => $count];
    }


    /**
     * 添加抽查
     * @author lamkakyun
     * @date 2019-03-29 16:26:32
     * @return void
     */
    public function addCheck($params, $is_edit)
    {
        if ($is_edit)
        {
            if (!isset($params['id']) || !preg_match('/^\d+$/', $params['id'])) return ['code' => -1, 'msg' => '参数错误:id'];
            $check_info = $this->finance_check_model->where(['id' => $params['id']])->find();
            if (!$check_info || $check_info['create_uid'] != FMSAuth::instance()->id) return ['code' => -1, 'msg' => '记录不存在'];

            $solution_log = json_decode($check_info['solution_log'], true) ?? [];
            $last_solution = $solution_log[0] ?? false;
        }
        if (!isset($params['name']) || empty($params['name'])) return ['code' => -1, 'msg' => '抽查名称不能为空'];
        if (!isset($params['check_type']) || !preg_match('/^\d+$/', $params['check_type'])) return ['code' => -1, 'msg' => '抽查类型不正确'];
        if (!isset($params['check_status']) || !preg_match('/^\d+$/', $params['check_status'])) return ['code' => -1, 'msg' => '抽查状态不正确'];
        if (!isset($params['time_type']) || !preg_match('/^\d+$/', $params['time_type'])) return ['code' => -1, 'msg' => '抽查时间类型不正确'];
        if (!isset($params['start_time']) || !preg_match('/^\d{4}-\d{2}-\d{2}+$/', $params['start_time'])) return ['code' => -1, 'msg' => '抽查开始时间不正确'];
        if (!isset($params['end_time']) || !preg_match('/^\d{4}-\d{2}-\d{2}+$/', $params['end_time'])) return ['code' => -1, 'msg' => '抽查结束时间不正确'];
        if (!isset($params['check_order_amount']) || !preg_match('/^\d+$/', $params['check_order_amount'])) return ['code' => -1, 'msg' => '抽查数量不正确'];
        if (isset($params['saving_money']) && !preg_match('/^(-?\d+)(\.\d+)?$/', $params['saving_money'])) return ['code' => -1, 'msg' => '减损额不正确'];

        $add_data = [
            'name'               => $params['name'],
            'check_type'         => $params['check_type'],
            'check_status'       => $params['check_status'],
            'check_time_type'    => $params['time_type'],
            'check_start_time'   => $params['start_time'],
            'check_end_time'     => $params['end_time'],
            'check_order_amount' => $params['check_order_amount'],
        ];

        if (!$is_edit)
        {
            $add_data['create_uid'] = FMSAuth::instance()->id ?? 0;
            $add_data['create_uname'] = FMSAuth::instance()->username ?? '系统默认';
            $add_data['create_time'] = time();
        }

        if (isset($params['check_platform'])) $add_data['check_platform'] = implode(',', array_map('trim', $params['check_platform']));
        if (isset($params['exception_platform'])) $add_data['exception_platform'] = implode(',', array_map('trim', $params['exception_platform']));
        if (isset($params['check_accounts'])) $add_data['check_accounts'] = implode(',', array_map('trim', $params['check_accounts']));
        if (isset($params['check_org_ids'])) $add_data['check_org_ids'] = implode(',', array_map('trim', $params['check_org_ids']));
        if (isset($params['check_sellers'])) $add_data['check_sellers'] = implode(',', array_map('trim', $params['check_sellers']));
        if (isset($params['check_carrier_companys'])) $add_data['check_carrier_companys'] = implode(',', array_map('trim', $params['check_carrier_companys']));
        if (isset($params['check_carriers'])) $add_data['check_carriers'] = implode(',', array_map('trim', $params['check_carriers']));
        if (isset($params['check_order_fields'])) $add_data['check_order_fields'] = implode(',', array_map('trim', $params['check_order_fields']));
        if (isset($params['problems'])) $add_data['problems'] = $params['problems'];
        if (isset($params['saving_money'])) $add_data['saving_money'] = $params['saving_money'];
        if (isset($params['solution'])) $add_data['solution'] = trim($params['solution']);

        if ($is_edit) 
        {
            if (!$last_solution || trim($last_solution['text']) != trim($params['solution']))
            {
                array_unshift($solution_log, ['time' => time(), 'text' => $params['solution']]);
                $add_data['solution_log'] = json_encode($solution_log);
            }
            
            $this->finance_check_model->where(['id' => $params['id']])->update($add_data);
        }
        else {
            if (!empty($params['solution'])) $add_data['solution_log'] = json_encode([['time' => time(), 'text' => $params['solution']]]);
            $this->finance_check_model->insert($add_data);
        }

        return ['code' => 0, 'msg' => '操作成功'];
    }
}