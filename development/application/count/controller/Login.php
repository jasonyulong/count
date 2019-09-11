<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    kevin
 */

namespace app\count\controller;

use app\common\controller\PublicController;
use app\common\library\Sms;
use app\index\model\Admin;
use app\index\model\AdminLog;
use think\Config;
use think\Cookie;
use think\Hook;
use think\Session;
use think\Validate;

/**
 * 登录页
 * @internal
 */
class Login extends PublicController
{
    protected $noNeedLogin = [];
    protected $noNeedRight = [];
    protected $layout = '';

    /**
     * 初始化
     */
    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 管理员登录
     * @return string
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $url      = $this->request->get('url', Config::get('site.apppath'));
        $fail_url = $this->request->get('url', 'login/index');
        if ($this->auth->isLogin()) {
            $this->success(__("You've logged in, do not login again"), $url);
        }
        if ($this->request->isPost()) {
            $username  = $this->request->post('username');
            $password  = $this->request->post('password');
            $keeplogin = $this->request->post('keeplogin');
            $token     = $this->request->post('__token__');
            $rule      = [
                'username' => 'require|length:1,30',
                'password' => 'require|length:3,30',
                //'__token__' => 'token',
            ];
            $data      = [
                'username' => $username,
                'password' => $password,
                //'__token__' => $token,
            ];
            if (Config::get('app.login_captcha')) {
                $rule['captcha'] = 'require|captcha';
                $data['captcha'] = $this->request->post('captcha');
            }

            $validate = new Validate($rule, [], ['username' => __('Username'), 'password' => __('Password'), 'captcha' => __('Captcha')]);
            $result   = $validate->check($data);
            if (!$result) {
                $this->error($validate->getError(), $fail_url, ['token' => $this->request->token()]);
            }
            AdminLog::setTitle(__('Login'));
            $keeplogin = Config::get('site.logintime');
            $result    = $this->auth->login($username, $password, $keeplogin ? $keeplogin * 60 : 86400);
            if ($result === true) {
                Hook::listen("admin_login_after", $this->request);
                $this->success(__('Login successful'), $url, ['type' => 'login', 'url' => $url, 'id' => $this->auth->id, 'username' => $username], 3);
            } else {
                $msg = $this->auth->getError();
                $msg = $msg ? $msg : __('Username or password is incorrect');
                $this->error($msg, $fail_url, ['token' => $this->request->token()]);
            }
        }

        // 根据客户端的cookie,判断是否可以自动登录
        if ($this->auth->autologin()) {
            $this->redirect($url);
        }
        $background = Config::get('app.login_background');
        $background = stripos($background, 'http') === 0 ? $background : config('site.cdnurl') . $background;
        $this->view->assign('background', $background);
        $this->view->assign('title', __('Login'));
        $this->assign('url', $url);
        return $this->view->fetch();
    }

    /**
     * 发送短信验证码
     * @throws \think\exception\DbException
     */
    public function sendsms()
    {
        if (!$this->request->isPost()) {
            $this->error("请求异常", '/count/login');
        }
        $username = $this->request->post('username');
        if (!$username) {
            $this->error("请求异常");
        }
        $admin = Admin::get(['username' => $username]);
        if (empty($admin)) {
            $this->error("账户异常,请联系管理员");
        }
        $mobile = $admin['mobile'] ?? '';
        if (empty($mobile)) {
            $this->error("手机号码为空,无法发送验证码");
        }
        if (ENVIRONMENT != 'production') {
            // 测试期间自动发送
            $this->success(__('短信发送成功,请注意查收'), null, ['type' => 'sendsms']);
        }
        if (Sms::send($mobile, null, 'login', $admin->id, $admin->username)) {
            $this->success(__('短信发送成功,请注意查收'), null, ['type' => 'sendsms']);
        } else {
            $this->error(__('短信发送失败,请联系管理员'));
        }
    }

    /**
     * 锁屏
     * @return string
     * @throws \think\Exception
     */
    public function locks()
    {
        // 访问时间
        Session::set('accesstime', time());

        if (!$this->auth->isLogin()) {
            $this->error(__("登录超时,请重新登录"), '/count/login/logout');
        }
        AdminLog::setTitle(__('Login'));
        $url = $this->request->get('url', 'index/index');

        $getKeeplogin = Cookie::get('keeplogin');
        list($id, $keeptime, $expiretime, $key) = explode('|', $getKeeplogin);
        $keeplogin = $expiretime - time();

        if ($this->request->isPost()) {
            $username = $this->auth->username;
            $password = $this->request->post('password');
            $rule     = ['password' => 'require|length:3,30'];
            $data     = ['password' => $password];

            $validate = new Validate($rule, [], ['password' => __('Password')]);
            $result   = $validate->check($data);
            if (!$result) {
                $this->error($validate->getError(), $url, ['token' => $this->request->token()]);
            }
            AdminLog::setTitle(__('Login'));
            $result = $this->auth->login($username, $password, $keeplogin, false);
            if ($result === true) {
                Hook::listen("admin_login_after", $this->request);
                $this->success(__('Login successful'), $url, [
                    'type'     => 'login',
                    'url'      => $url,
                    'id'       => $this->auth->id,
                    'username' => $username,
                    'avatar'   => $this->auth->avatar
                ]);
            } else {
                $msg = $this->auth->getError();
                $msg = $msg ? $msg : __('Username or password is incorrect');
                $this->error($msg, $url, ['token' => $this->request->token()]);
            }
        }
        $this->assign('title', __('Login'));
        $this->assign('url', $url);
        $this->assign('admin', $this->auth->getUser());
        return $this->view->fetch();
    }

    /**
     * 注销登录
     */
    public function logout()
    {
        $this->auth->logout();
        Hook::listen("admin_logout_after", $this->request);

        $url = ENVIRONMENT == 'production' ? ERP_DOMAIN . 'login.php' : '/count/login';
        $this->redirect($url);
    }

}
