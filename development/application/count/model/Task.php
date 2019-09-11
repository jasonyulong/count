<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    lamkakyun
 */
namespace app\count\model;

use think\Model;

/**
 * 导出任务
 * Class Task
 * @package app\common\model
 */
class Task extends Model
{
    protected $name = 'task';
    public $connection = 'count';
}