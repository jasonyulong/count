<?php

namespace app\count\command;

use app\common\controller\Commands;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use think\Exception;

/**
 * 订单相关，自动脚本
 * Class Order
 * @package app\count\command
 */
class Order extends Commands
{
    private $command_name = 'order';

    /**
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-09-18 02:34:42
     */
    protected function configure()
    {
        $desc = __('统计订单数据');
        $this->defaultConfigure($this->command_name, $desc);

        $this->addOption('test', null, Option::VALUE_OPTIONAL, __('是否测试?'));
        $this->addOption('day', null, Option::VALUE_OPTIONAL, __('统计日期(格式：2018-09-01)'));
        $this->addOption('start', null, Option::VALUE_OPTIONAL, __('统计开始日期(格式：2018-09-01)'));
        $this->addOption('end', null, Option::VALUE_OPTIONAL, __('统计结束日期(格式：2018-09-01)'));
        $this->addOption('platform', null, Option::VALUE_OPTIONAL, __('平台名称(如：ebay)'));
        $this->addOption('month', null, Option::VALUE_OPTIONAL, __('指定月份'));
        $this->addOption('month-num', null, Option::VALUE_OPTIONAL, __('指定月份数'));

        $this->addOption('run-only', null, Option::VALUE_OPTIONAL, __('指定需要统计的销售额:account->账户销售额，seller->销售员销售额，store->仓库销售额，location->发货地销售额,sku->sku销售额'));
        $this->addOption('queue', null, Option::VALUE_OPTIONAL, __('执行队列'));
        $this->addUsage('--day=2018-09-15');
    }

    /**
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-09-18 02:34:42
     */
    protected function execute(Input $input, Output $output)
    {
        $ns_prefix = "\\" . __NAMESPACE__ . "\\" . $this->command_name . "\\";
        $class_dir = __DIR__ . DIRECTORY_SEPARATOR . $this->command_name;
        $this->defaultExecute($input, $output, $class_dir, $ns_prefix);
    }
}