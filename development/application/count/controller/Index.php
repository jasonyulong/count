<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    kevin
 */

namespace app\count\controller;

use app\common\library\ToolsLib;
use app\common\controller\Common;
use app\common\controller\AuthController;

/**
 * 模块首页
 * Class Index
 * @package app\admin\controller
 */
class Index extends AuthController
{
    /**
     * 无需鉴权的方法,但需要登录
     * @var array
     */
    protected $noNeedRight = ['index'];
    
    public function _initialize()
    {
        parent::_initialize();
        $this->assign('module', 'order');
    }

    /**
     * 模块首页
     * @return string
     * @throws \think\Exception
     */
    public function index()
    {
        $this->assign('module', '');
        return $this->view->fetch();
    }

    public function test()
    {
        phpinfo();
    }
}
