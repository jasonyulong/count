<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    yang
 */

namespace app\count\command;

use app\common\controller\Commands;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use think\console\input\Argument;
use think\Exception;

/**
 * -----------------------------
 * 使用方法:
 * 1. php think [命令行名称] -m [要调用的类名称] -a [要调用的方法]    DEMO: php think finance -m IncomeExpenditure  -a   getIncomeExpenditureData  (收支数据同步)
 * 2. php think [命令行名称] -m [要调用的类名称] -a [要调用的方法]    DEMO: php think finance -m AfterSale -a getAfterSaleData  (退款数据同步)
 * -----------------------------
 */

/**
 * 财务数据同步命令行任务
 * Class Finance
 * @package app\count\command
 */
class Finance extends Commands
{
    private $command_name = 'finance';

    /**
     * 初始化
     * @author kevin
     */
    protected function configure()
    {
        $desc = __('统计财务数据');
        $this->defaultConfigure($this->command_name, $desc);

        $this->addOption('test', null, Option::VALUE_OPTIONAL, __('是否测试?'));
        $this->addOption('day', null, Option::VALUE_OPTIONAL, __('统计日期(格式：2018-09-01)'));
        $this->addOption('start', null, Option::VALUE_OPTIONAL, __('统计开始日期(格式：2018-09-01)'));
        $this->addOption('end', null, Option::VALUE_OPTIONAL, __('统计结束日期(格式：2018-09-01)'));
        $this->addOption('platform', null, Option::VALUE_OPTIONAL, __('平台名称(如：ebay)'));


//        $this->addArgument('date', Argument::OPTIONAL, '开始日期', null); //开始日期、非必填项(列:2018-09-26)
//        $this->addArgument('end_date', Argument::OPTIONAL, '结束日期', null); //结束日期、非必填项、必须大于开始日期(列:2018-09-30)
    }

    /**
     * 执行命令
     * @author kevin
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