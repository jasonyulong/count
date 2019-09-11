<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    kevin
 */

namespace app\index\controller;

use app\common\controller\AuthController;
use app\common\controller\PublicController;
use app\common\controller\Publics;
use app\common\library\Token;
use app\common\library\ToolsLib;
use think\Config;
use Think\Hook;
use think\Session;

/**
 * 模块首页
 * Class Index
 * @package app\index\controller
 */
class Index extends AuthController
{
    protected $layout = '';

    public function _initialize()
    {
        $this->assign('title', Config::get('site.name') . '-Login');
        parent::_initialize();
    }

    /**
     * 登录
     */
    public function index()
    {
        return $this->view->fetch();
    }

    /**
     * 锁定
     */
    public function locks()
    {
        return $this->view->fetch();
    }
}
