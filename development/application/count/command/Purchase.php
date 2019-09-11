<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 1.0
 * @author    leo
 */

namespace app\count\command;

use app\common\controller\Commands;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;
use think\Exception;

/**
 * -----------------------------
 * 使用方法:
 * 1. php think [命令行名称] -m [要调用的类名称] -a [要调用的方法]    DEMO: php think purchase -m Statistics  -a payRevenueStatistics  采购账款统计按状态变化获取更新
 * 2. php think [命令行名称] -m [要调用的类名称] -a [要调用的方法] [参数值]   DEMO: php think purchase -m Statistics -a payRevenueStatisticsByDay 采购账款统计60天内
 * -----------------------------
 */

/**
 * 数据统计命令行任务
 * Class Purchase
 * @package app\count\command
 */
class Purchase extends Commands
{
    private $command_name = 'purchase';

    /**
     * 初始化
     * @author leo
     */
    protected function configure()
    {
        $desc = __('采购账款统计数据');
        $this->addArgument('date', Argument::OPTIONAL, '开始日期', null); //开始日期、非必填项(列:2018-09-26)
        $this->addArgument('end_date', Argument::OPTIONAL, '结束日期', null); //结束日期、非必填项、必须大于开始日期(列:2018-09-30)
        $this->defaultConfigure($this->command_name, $desc);
    }

    /**
     * 执行命令
     * @author leo
     * @param Input $input 输入对象
     * @param Output $output 输出对象
     * @return int|null|void
     * @throws Exception
     */
    protected function execute(Input $input, Output $output)
    {
        $ns_prefix = "\\" . __NAMESPACE__ . "\\" . $this->command_name . "\\";
        $class_dir = __DIR__ . DIRECTORY_SEPARATOR . $this->command_name;
        $this->defaultExecute($input, $output, $class_dir, $ns_prefix);
    }
}