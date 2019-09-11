<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    lamkakyun
 */
namespace app\count\library\export;

use app\count\model\Task;
use app\common\library\ToolsLib;

/**
 * 导出任务 相关
 * Class TaskLib
 * @package app\count\library\export
 */
class TaskLib
{
    /**
     * 实例
     */
    private static $instance = null;
    
    /**
     * 单例
     * @author lamkakyun
     * @date 2018-12-12 10:00:00
     * @return TaskLib
     */
    public static function getInstance(): TaskLib
    {
        if (!static::$instance) {
            static::$instance = new TaskLib();
        }
        return static::$instance;
    }

    /**
     * 获取任务状态
     * @author lamkakyun
     * @date 2018-12-12 18:11:09
     * @return void
     */
    public function getStatusList()
    {
        // 1 待执行 2 执行中 3 已完成
        $data = [
            '1' => '待执行',
            '2' => '执行中',
            '3' => '已完成',
            '4' => '已取消',
            '5' => '已删除',
        ];

        return $data;
    }


    /**
     * 获取任务列表
     * @author lamkakyun
     * @date 2018-12-12 10:18:11
     * @return array
     */
    public function getTaskList($params)
    {
        $task_model = new Task();

        $start_select = ($params['p'] - 1) * $params['ps'];
        $sort_str = 'id DESC';

        $where = ['status' => ['NEQ', 5]];
        if (isset($_SESSION['id']) && !empty($_SESSION['id'])) $where['create_userid'] = $_SESSION['id'];
        if (isset($params['createtime_start']) && !empty($params['createtime_start']))
        {
            $where['create_time'][] = ['EGT', strtotime($params['createtime_start'])];
        }
        if (isset($params['createtime_end']) && !empty($params['createtime_end']))
        {
            $where['create_time'][] = ['LT', strtotime($params['createtime_end']) + 86400];
        }

        $count = $task_model->where($where)->count();
        if ($count == 0) return ['count' => $count, 'list' => []];
        $list = $task_model->where($where)->limit($start_select, $params['ps'])->order($sort_str)->select();

        return ['count' => $count, 'list' => $list];
    }


    /**
     * 添加任务
     * @author lamkakyun
     * @date 2018-12-12 15:09:33
     * @return array
     */
    public function addTask($params)
    {
        if (empty($params['task_name'])) return ['code' => -1, 'msg' => '任务名称不能为空'];
        if (!preg_match('/^\d+$/', $params['priority'])) return ['code' => -1, 'msg' => '优先级错误'];

        $task_model = new Task();
        $add_data = [
            'task_name'     => $params['task_name'],
            'params'        => json_encode($params),
            'priority'      => $params['priority'],
            'create_userid' => $_SESSION['id'] ?? '0',
            'create_user'   => $_SESSION['truename'] ?? '系统默认',
            'create_time'   => time(),
        ];

        $ret_add = $task_model->insert($add_data);
        if (!$ret_add) return ['code' => -1, 'msg' => '操作失败'];

        return ['code' => 0, 'msg' => '操作成功'];
    }

    
    /**
     * 更新任务状态
     * @author lamkakyun
     * @date 2018-12-13 13:45:05
     * @return array
     */
    public function updateTaskStatus($task_id, $status)
    {
        $task_model = new Task();
        $where = ['id' => $task_id];
        $save_data = ['status' => $status];
        $ret_save = $task_model->where($where)->update($save_data);

        if ($ret_save === false) return ['code' => -1, 'msg' => '操作失败'];
        return ['code' => 0, 'msg' => '操作成功'];
    }
}