<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    mina
 */

namespace app\common\command;

use app\common\controller\Commands;
use think\console\Input;
use think\console\input\Option;
use think\console\input\Argument;
use think\console\Output;
use think\Exception;
use think\Log;

/**
 * -----------------------------
 * 使用方法:
 * 1. php think [命令行名称] -m [要调用的类名称] -a [要调用的方法]    DEMO: php think sync -m orders -a pull
 * 2. php think [命令行名称] -m [要调用的类名称] -a [要调用的方法] [参数值]   DEMO: php think sync -m orders -a pull
 * -----------------------------
 */

/**
 * 数据同步命令行任务
 * Class Sync
 * @package app\common\command
 */
class Common extends Commands
{
    /**
     * @desc   配置方法
     * @author mina
     * @param  void
     * @return void
     */
    protected function configure()
    {
        $this->setName('common')
            // 添加选项
            ->addOption('module', 'm', Option::VALUE_REQUIRED, __('类名称, 对应你要操作的那个类文件'), null)
            ->addOption('action', 'a', Option::VALUE_REQUIRED, __('方法名称, 对应你要操作的类文件里的方法名称'), null)
            ->addOption('day', null, Option::VALUE_OPTIONAL, __('日期(格式：2018-09-01)'))
            ->addOption('start', null, Option::VALUE_OPTIONAL, __('开始时间，日期(格式：2018-09-01)'))
            ->addOption('end', null, Option::VALUE_OPTIONAL, __('结束时间，日期(格式：2018-09-01)'))
            ->addOption('type', null, Option::VALUE_OPTIONAL, __('是开发数据还是商品数据'))
            ->setDescription(__('同步数据'));
    }

    /**
     * 执行命令
     * @author mina
     * @param Input $input 输入对象
     * @param Output $output 输出对象
     * @return int|null|void
     * @throws Exception
     */
    protected function execute(Input $input, Output $output)
    {
        $module = $input->getOption('module') ?: '';
        $action = $input->getOption('action') ?: '';

        if (!$module) {
            throw new Exception(__('请填写正确的类名称'));
        }
        if (!$action) {
            throw new Exception(__('请填写正确的方法名称'));
        }
        if (!is_file(__DIR__ . '/cli/' . ucfirst($module) . '.php')) {
            throw new Exception(__('填写的类名称错误'));
        }

        // 根据传入参数找到对应类
        $moduleName = "\app\common\command\cli\\" . ucfirst($module);
        // new 对象
        $class = new $moduleName($input, $output);
        if (!in_array($action, get_class_methods($class))) {
            throw new Exception(__('操作方法不存在'));
        }

        $result = $class->$action();
        Log::record(__($result));

        // 输出结果
        $output->info(__($result));
    }
}
