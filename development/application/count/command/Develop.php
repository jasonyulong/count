<?php
/**
 * @Copyright (C), ZhuoShi.
 * @Author: 杨能文
 * @Name: Develop.php
 * @Date: 2019/2/23
 * @Time: 14:13
 * @Description
 */


namespace app\count\command;

use app\common\controller\Commands;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use think\Exception;

class Develop extends Commands
{
    private $command_name = 'develop';

    protected function configure()
    {
        $desc = __('统计开发数据');
        $this->defaultConfigure($this->command_name, $desc);

        $this->addOption('day', null, Option::VALUE_OPTIONAL, __('统计日期(格式：2018-09-01)'));
        $this->addOption('start', null, Option::VALUE_OPTIONAL, __('统计开始日期(格式：2018-09-01)'));
        $this->addOption('end', null, Option::VALUE_OPTIONAL, __('统计结束日期(格式：2018-09-01)'));
        $this->addUsage('--day=2019-02-16');
    }

    /**
     * 运行
     * @param Input $input
     * @param Output $output
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