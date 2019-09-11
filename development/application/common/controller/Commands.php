<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    kevin
 */

namespace app\common\controller;

use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use think\Exception;
use think\Log;

/**
 * 任务控制基类
 * Class Commands
 * @package app\common\controller
 */
class Commands extends Command
{
    /**
     * @var array 配置信息
     */
    protected $options = [];

    /**
     * 写入到文件
     * @param string $name
     * @param array $data
     * @param string $pathname
     * @return mixed
     */
    protected function writeToFile($name, $data, $pathname)
    {
        $search = $replace = [];
        foreach ($data as $k => $v) {
            $search[]  = "{%{$k}%}";
            $replace[] = $v;
        }
        $stub    = file_get_contents($this->getStub($name));
        $content = str_replace($search, $replace, $stub);

        if (!is_dir(dirname($pathname))) {
            mkdir(strtolower(dirname($pathname)), 0755, true);
        }
        return file_put_contents($pathname, $content);
    }


    /**
     * 默认配置
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-09-18 02:28:19
     */
    protected function defaultConfigure($command_name, $desc = '')
    {
        $this->setName($command_name)
            ->addOption('module', 'm', Option::VALUE_REQUIRED, __('类名称, 对应你要操作的那个类文件'), null)
            ->addOption('action', 'a', Option::VALUE_REQUIRED, __('方法名称, 对应你要操作的类文件里的方法名称'), null)
            ->setDescription($desc);
    }


    /**
     * 默认的执行操作
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-09-18 02:31:40
     */
    public function defaultExecute(Input $input,Output $output, $class_dir, $ns_prefix)
    {
        $module = $input->getOption('module') ?: '';
        $action = $input->getOption('action') ?: '';
        $filename = $class_dir . DIRECTORY_SEPARATOR . ucfirst($module) . '.php';

        if (!$module) throw new Exception(__('请填写正确的类名称'));
        if (!$action) throw new Exception(__('请填写正确的方法名称'));
        if(!is_file($filename)) throw new Exception(__('填写的类名称错误'));

        $moduleName = $ns_prefix . ucfirst($module);

        $object = new $moduleName($input, $output);
        if (!method_exists($object, $action)) throw new Exception(__('操作方法不存在'));

        $result = $object->$action($input, $output);
        if ($result) $output->info(__($result));
    }
}