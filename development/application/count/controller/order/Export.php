<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    lamkakyun
 */

namespace app\count\controller\order;

use think\Config;
use app\count\model\Task;
use app\common\library\ToolsLib;
use app\common\controller\Common;
use app\common\library\CarrierLib;
use app\count\library\export\TaskLib;
use app\count\library\order\OrderLib;
use app\common\controller\AuthController;

/**
 * 数据明细导出
 * Class export
 * @package app\count\controller\order
 */
class Export extends AuthController
{
    /**
     * 查看
     * @author lamkakyun
     * @date 2018-12-12 09:45:01
     * @return void
     */
    public function index()
    {
        $params                     = array_merge(input('get.', '', 'trim'), input('post.', '', 'trim'));
        $params['p']                = $params['p'] ?? '1';
        $params['ps']               = $params['ps'] ?? '20';
        $params['createtime_start'] = $params['createtime_start'] ?? date('Y-m-d', strtotime('-30 days'));
        $params['createtime_end']   = $params['createtime_end'] ?? date('Y-m-d');

        $data  = TaskLib::getInstance()->getTaskList($params);
        $count = $data['count'];
        $list  = $data['list'];

        $this->assign('list_total', $count);
        $this->assign('list', $list);
        $this->assign('params', $params);
        $this->assign('status_list', TaskLib::getInstance()->getStatusList());
        $this->assign('module', 'export');
        $this->_assignPagerData($this, $params, $count);

        return $this->view->fetch("task_list");
    }


    /**
     * 新建任务
     * @author lamkakyun
     * @date 2018-12-12 09:45:54
     * @return void
     */
    public function addTask()
    {
        $params = array_merge(input('get.', '', 'trim'), input('post.', '', 'trim'));

        $params['start_time'] = $params['start_time'] ?? date('Y-m-d', strtotime('-15 days'));
        $params['end_time']   = $params['end_time'] ?? date('Y-m-d');

        if ($this->request->isGet()) {
            // 将组织结构变换格式
            $org_tree = ToolsLib::getInstance()->getBusinessOrgTree();
            $org_arr  = ToolsLib::getInstance()->treeToArray($org_tree);
            $org_list = [];
            foreach ($org_arr as $key => $value) {
                $_new_name  = $value['level'] > 2 ? '|' . str_repeat('---', $value['level'] - 2) . $value['name'] : $value['name'];
                $_tmp_org   = [
                    'id'          => $value['id'],
                    'parent_id'   => $value['parent_id'],
                    'name'        => $_new_name,
                    'seller_list' => array_map(function ($v) {
                        $tmp_arr = explode('___', $v);
                        return $tmp_arr[0];
                    }, $value['seller_list'])
                ];
                $org_list[] = $_tmp_org;
            }


            $this->assign('params', $params);
            $this->assign('order_type_list', OrderLib::getInstance()->getOrderTypeConf());
            // echo '<pre>';var_dump(OrderLib::getInstance()->getOrderTypeConf());echo '</pre>';
            // exit;
            $this->assign('order_platform_list', OrderLib::getInstance()->getOrderPlatformConf());
            $this->assign('order_status_list', OrderLib::getInstance()->getOrderStatusConf());
            $this->assign('order_field_list', OrderLib::getInstance()->getOrderFieldsConf());
            $this->assign('sku_field_list', OrderLib::getInstance()->getSkuFieldsConf());
            $this->assign('carrier_company_list', CarrierLib::init()->getCarrierCompany());
            $this->assign('org_list', $org_list);

            return $this->view->fetch("addTask");
        }

        $params['priority'] = $params['priority'] ?: 0;
        // post 操作
        return json(TaskLib::getInstance()->addTask($params));
    }


    /**
     * 下载导出的文件
     * @author lamkakyun
     * @date 2018-12-13 11:31:04
     * @return void
     */
    public function download()
    {
        $params     = array_merge(input('get.', '', 'trim'), input('post.', '', 'trim'));
        $task_model = new Task();

        if (!isset($params['id']) || !preg_match('/^\d+$/', $params['id'])) return $this->error('参数错误');

        $task_data = $task_model->where(['id' => $params['id']])->find();
        if (!$task_data) return $this->error('任务不存在');
        if ($task_data['status'] != 3) return $this->error('任务状态不正确，不能下载');

        $result = json_decode($task_data['result'], true);

        if ($result['success']) {
            $file = $result['file_name'];
            ToolsLib::getInstance()->downloadFile($file);
        } else {
            return $this->error('任务运行失败，' . $result['err']);
        }
    }


    /**
     * 删除任务
     * @author lamkakyun
     * @date 2018-12-13 13:43:43
     * @return void
     */
    public function delTask()
    {
        $params = array_merge(input('get.', '', 'trim'), input('post.', '', 'trim'));

        if (!isset($params['id']) || !preg_match('/^\d+$/', $params['id'])) return $this->error('参数错误');

        $status = 5; // 1 待执行 2 执行中 3 已完成 4 已取消 5 已删除

        return json(TaskLib::getInstance()->updateTaskStatus($params['id'], $status));
    }


    /**
     * 取消任务
     * @author lamkakyun
     * @date 2018-12-13 13:48:36
     * @return void
     */
    public function cancelTask()
    {
        $params = array_merge(input('get.', '', 'trim'), input('post.', '', 'trim'));

        if (!isset($params['id']) || !preg_match('/^\d+$/', $params['id'])) return $this->error('参数错误');

        $status = 4; // 1 待执行 2 执行中 3 已完成 4 已取消 5 已删除
        return json(TaskLib::getInstance()->updateTaskStatus($params['id'], $status));
    }


    /**
     * 查看任务
     * @author lamkakyun
     * @date 2018-12-13 14:11:34
     * @return void
     */
    public function detail()
    {
        $task_model = new Task();
        $params     = array_merge(input('get.', '', 'trim'), input('post.', '', 'trim'));

        if (!isset($params['id']) || !preg_match('/^\d+$/', $params['id'])) return $this->error('参数错误');

        $task_data = $task_model->where(['id' => $params['id']])->find();
        if (!$task_data) return $this->error('任务不存在');

        $task_params = json_decode($task_data['params'], true);

        $org_list = ToolsLib::getInstance()->getAllOrg(2);

        $task_params['order_type']      = $task_params['order_type'] ?? [];
        $task_params['order_status']    = $task_params['order_status'] ?? [];
        $task_params['platform']        = $task_params['platform'] ?? [];
        $task_params['account']         = $task_params['account'] ?? [];
        $task_params['carrier_company'] = $task_params['carrier_company'] ?? [];
        $task_params['carrier']         = $task_params['carrier'] ?? [];
        $task_params['org_id']          = $task_params['org_id'] ?? [];
        $task_params['seller']          = $task_params['seller'] ?? [];
        // $task_params['order_fields'] = $task_params['order_fields'] ?? array_keys(OrderLib::getInstance()->getOrderFieldsConf());
        $task_params['order_fields'] = $task_params['order_fields'] ?? [];

        $this->assign('order_type_list', OrderLib::getInstance()->getOrderTypeConf());
        $this->assign('order_platform_list', OrderLib::getInstance()->getOrderPlatformConf());
        $this->assign('order_status_list', OrderLib::getInstance()->getOrderStatusConf());
        $this->assign('order_field_list', OrderLib::getInstance()->getOrderFieldsConf());
        $this->assign('sku_field_list', OrderLib::getInstance()->getSkuFieldsConf());
        $this->assign('carrier_company_list', CarrierLib::init()->getCarrierCompany());
        $this->assign('task_params', $task_params);
        $this->assign('task_data', $task_data);
        $this->assign('org_list', $org_list);

        return $this->view->fetch("detail");

    }
}