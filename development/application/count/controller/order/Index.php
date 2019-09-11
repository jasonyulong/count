<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    kevin
 */

namespace app\count\controller\order;

use think\Config;
use app\count\model\Order;
use app\count\library\OrgLib;
use think\cache\driver\Redis;
use app\common\library\ToolsLib;
use app\common\controller\Common;
use app\count\library\order\OrderLib;
use app\common\controller\AuthController;

/**
 * 订单状态报表
 * @package app\count\controller\order
 */
class Index extends AuthController
{
    protected $relationSearch = true;

    /**
     * @var \app\count\model\Order
     */
    protected $model = null;

    /**
     * 查看
     * @access auth
     * @return string
     * @throws \think\Exception
     */
    public function index()
    {
        $type   = input('get.type', 'date');
        $model  = input('get.model', 'table');
        $params = input('get.');

        // todo: 默认参数设置
        $params['checkDate']      = $params['checkDate'] ?? 'day';
        $params['scandate_start'] = $params['scandate_start'] ?? date('Y-m');
        $params['scandate_end']   = $params['scandate_end'] ?? date('Y-m');
        $params['type']           = $params['type'] ?? 'date';
        if ($params['type'] == 'date') {
            $params['scantime_start'] = $params['scantime_start'] ?? date('Y-m-d', strtotime('-15 day'));
            $params['scantime_end']   = $params['scantime_end'] ?? date('Y-m-d', strtotime('today'));
        } else {
            $params['scantime_start'] = $params['scantime_start'] ?? date('Y-m-d', strtotime('-1 day'));
            $params['scantime_end']   = $params['scantime_end'] ?? date('Y-m-d', strtotime('-1 day'));
        }

        $params['sort']       = $params['sort'] ?? 'desc';
        $params['sort_field'] = $params['sort_field'] ?? 'year, month, days';
        $params['p']          = $params['p'] ?? 1;
        if ($params['type'] == 'date') $params['ps'] = 100;
        $params['ps']    = $params['ps'] ?? 50;
        $params['model'] = $params['model'] ?? 'table';
        if (isset($params['is_export']) && $params['is_export'] == 1) $params['ps'] = 10000;

        $all_platform = ToolsLib::getInstance()->getAllPlatforms();

        // TODO: 如果是ERP 同步过来的用户(业务部)，只能看到自己的统计信息
        if ($this->auth->erp_id)
        {
            $manage_info = OrgLib::getInstance()->getManageInfo($this->auth->username);
            // $_tmp_platform_accounts = explode(',', $manage_info['current_user_info']['ebayaccounts']);
            $_tmp_platform_accounts = $manage_info['manage_accounts'];
            $params['platform_account'] = $_tmp_platform_accounts;
        }
        $data = OrderLib::getInstance()->getOrderStatusList($params, $type);

        // todo: 添加订单类型计算 数据
        foreach ($data['list'] as $key => $value) {
            $tmp_where = [];
            if ($params['type'] == 'date') {
                $tmp_where = ['year' => $value['year'], 'month' => $value['month']];
                if ($params['checkDate'] == 'day') $tmp_where['days'] = $value['days'];
            }
            if ($params['type'] == 'platform') {
                $tmp_where             = OrderLib::getInstance()->_handleQueryDate($params, $tmp_where);
                $tmp_where['platform'] = $value['platform'];
            }
            $order_type_group_data = OrderLib::getInstance()->getOrderTypeGroupInfo($tmp_where);

            $data['list'][$key]['type_list'] = $order_type_group_data;
        }

        // todo: 导出EXCEL
        if (isset($params['is_export']) && $params['is_export'] == 1) $this->_index_export($data, $params['type'], $params['checkDate']);

        // todo: 为图表 构造数据
        if ($params['model'] == 'chart') {
            $x_data     = [];
            $chart_type = 'bar';
            if ($params['type'] == 'platform') {
                $x_data = array_column($data['list'], 'platform');
            }
            if ($params['type'] == 'date') {
                $chart_type = 'line';
                $x_data     = [];
                foreach ($data['list'] as $value) {
                    $tmp_str = "{$value['year']}-{$value['month']}";
                    if ($params['checkDate'] == 'day') $tmp_str .= "-{$value['days']}";
                    $x_data[] = $tmp_str;
                }
            }

            $x_data_names = ['总订单数', '可发货数', '未发货数', '已发货数', '已完成数', '补发数', '退款数', '退货数', '手工作废数', '系统'];
            $y_data[]     = array_column($data['list'], 'sum_totals');
            $y_data[]     = array_column($data['list'], 'sum_can_send');
            $y_data[]     = array_column($data['list'], 'sum_noships');
            $y_data[]     = array_column($data['list'], 'sum_ships');
            $y_data[]     = array_column($data['list'], 'sum_overs');
            $y_data[]     = array_column($data['list'], 'sum_resends');
            $y_data[]     = array_column($data['list'], 'sum_refunds');
            $y_data[]     = array_column($data['list'], 'sum_returns');
            $y_data[]     = array_column($data['list'], 'sum_recycles');
            $y_data[]     = array_column($data['list'], 'sum_recycles_system');

            $this->assign('chart_type', $chart_type);
            $this->assign('x_data', json_encode($x_data));
            $this->assign('y_data', json_encode($y_data));
            $this->assign('x_data_names', json_encode($x_data_names));
        }

        // 匿名函数
        $sum_function = function ($v1, $v2) {
            return $v1 + $v2;
        };
        // todo: 计算总数
        $total_data                        = [];
        $total_data['sum_totals']          = array_reduce(array_column($data['list'], 'sum_totals'), $sum_function);
        $total_data['sum_can_send']        = array_reduce(array_column($data['list'], 'sum_can_send'), $sum_function);
        $total_data['sum_noships']         = array_reduce(array_column($data['list'], 'sum_noships'), $sum_function);
        $total_data['sum_ships']           = array_reduce(array_column($data['list'], 'sum_ships'), $sum_function);
        $total_data['sum_overs']           = array_reduce(array_column($data['list'], 'sum_overs'), $sum_function);
        $total_data['sum_resends']         = array_reduce(array_column($data['list'], 'sum_resends'), $sum_function);
        $total_data['sum_refunds']         = array_reduce(array_column($data['list'], 'sum_refunds'), $sum_function);
        $total_data['sum_returns']         = array_reduce(array_column($data['list'], 'sum_returns'), $sum_function);
        $total_data['sum_recycles']        = array_reduce(array_column($data['list'], 'sum_recycles'), $sum_function);
        $total_data['sum_recycles_system'] = array_reduce(array_column($data['list'], 'sum_recycles_system'), $sum_function);
        $total_data['sum_total_ship']      = array_reduce(array_column($data['list'], 'sum_total_ship'), $sum_function);

        $this->assign('total_data', $total_data);
        $this->_assignPagerData($this, $params, $data['count']);

        $this->assign('list', $data['list']);
        $this->assign('list_total', $data['count']);

        $this->assign('type', $type);
        $this->assign('model', $model);
        $this->assign('params', $params);
        $this->assign('platforms', $all_platform);
        $this->assign('contents', 'order');
        $this->assign('module', 'order');

        return $this->view->fetch("index_{$type}");
    }


    /**
     * 首页报表 导出
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-09-21 10:59:03
     */
    private function _index_export($data, $type, $checkDate = 'day')
    {
        $filename = "订单状态报表-" . date('Y-m-d');
        $data     = $data['list'];
        if ($type == 'date') {
            foreach ($data as $key => $value) {
                $tmp_str = "{$value['year']}-{$value['month']}";
                if ($checkDate == 'day') $tmp_str .= "-{$value['days']}";
                $data[$key]['date'] = $tmp_str;
            }
        }

        $headers = [];
        if ($type == 'date') $headers['date'] = '日期';
        if ($type == 'platform') $headers['platform'] = '平台';
        $headers = array_merge($headers, [
            'sum_totals'          => '总订单数',
            'sum_can_send'        => '可发货数',
            'sum_noships'         => '未发货数',
            'sum_ships'           => '已发货数',
            'sum_overs'           => '已完成数',
            'sum_resends'         => '补发数',
            'sum_refunds'         => '退款数',
            'sum_returns'         => '退货数',
            'sum_recycles'        => '手工作废数',
            'sum_recycles_system' => '系统作废数',
            'sum_total_ship'      => '仓库发货数',
        ]);


        ToolsLib::getInstance()->exportExcel($filename, $headers, $data);
    }


}
