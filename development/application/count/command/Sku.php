<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    yang
 */

namespace app\count\command;

use app\common\controller\Commands;
use think\console\Input;
use think\console\Output;
use think\console\input\Argument;
use think\Exception;

/**
 * -----------------------------
 * 使用方法:
 * 1. php think [命令行名称] -m [要调用的类名称] -a [要调用的方法]    DEMO: php think sku -m Sku -a getSkuData    同步sku数据
 * 2. php think [命令行名称] -m [要调用的类名称] -a [要调用的方法]    DEMO: php think sku -m SkuPackage -a getSkuPackage  拉取产品包(仅供初始化)
 * -----------------------------
 */

/**
 * 数据同步命令行任务
 * Class Sku
 * @package app\count\command
 */
class Sku extends Commands
{
    private $command_name = 'sku';
    /**
     * 初始化
     * @author kevin
     */
    protected function configure()
    {
        $desc = __('统计SKU数据');
        $this->defaultConfigure($this->command_name, $desc);
        $this->addArgument('date',Argument::OPTIONAL,'开始日期',null); //开始日期、非必填项(列:2018-09-26)
        $this->addArgument('end_date', Argument::OPTIONAL, '结束日期', null); //结束日期、非必填项、必须大于开始日期(列:2018-09-30)
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