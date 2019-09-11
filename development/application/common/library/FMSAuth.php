<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    kevin
 */

namespace app\common\library;

use fast\Rsa;
use fast\Tree;
use think\Env;
use think\Hook;
use fast\Random;
use think\Config;
use think\Cookie;
use think\Request;
use think\Session;
use app\index\model\Admin;
use app\common\library\Sms;
use app\common\library\Token;

/**
 * 权限校验 (从FMS 中复制过来的)
 * Class Auth
 * @package app\common\library
 */
class FMSAuth extends \fast\FMSAuth
{
    protected $_error = '';
    protected $requestUri = '';
    protected $breadcrumb = [];
    protected $logined = false; //登录状态
    protected $_user = [];

    /**
     * 析构函数
     * Auth constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 魔术方法
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return Session::get('admin.' . $name);
    }

    /**
     * 根据Token初始化
     * @param $token
     * @return bool
     * @throws \think\exception\DbException
     */
    public function init($token)
    {
        if ($this->_error)
            return FALSE;
        $data = Token::get($token);
        if (!$data) {
            return FALSE;
        }
        // 判断是否是ERP同步的用户
        $user_id = intval($data['user_id']);
        if ($this->_logined) {
            if ($user_id != $this->id) {
                $this->logout();
            } else {
                return TRUE;
            }
        }

        if (!empty($user_id)) {
            $admin = \app\common\model\Admin::get($user_id);
            if (!$admin) {
                $this->setError('Account not exist');
                return FALSE;
            }
            if ($admin->status != 1) {
                $this->setError('Account is locked');
                return FALSE;
            }

            $admin->loginfailure = 0;
            $admin->logintime    = time();
            $admin->token        = $token;
            $admin->save();

            Session::set("admin", \rsa\RSA::getInstance()->encrypt([
                'id'       => $admin->id,
                'username' => $admin->username,
                'token'    => $admin->token,
                'erp_id'   => $admin->erp_id,
            ]));
            Session::set("adminlogin", $admin, 'think');

            $this->_user    = $admin;
            $this->_token   = $admin->token;
            $this->id       = $admin->id;
            $this->username = $admin->username;
            $this->_logined = TRUE;
            $this->erp_id   = $admin->erp_id;

            Session::set('accesstime', time());
            Cookie::set('token', $admin->token);

            $logintime = Config::get('config.logintime');
            $keeptime  = !empty($logintime) ? (intval($logintime) * 60) : 3600;
            $this->keeplogin($keeptime);

            //初始化成功的事件
            Hook::listen("user_init_successed", $this->_user);
            return TRUE;
        } else {
            $this->setError('You are not logged in');
            return FALSE;
        }
    }

    /**
     * 管理员登录
     * @param   string $username 用户名
     * @param   string $password 密码
     * @param   int $keeptime 有效时长
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function login($username, $password, $keeptime = 0, $smscode = null)
    {
        $admin = $this->loginCheck($username, $password);
        if (!$admin) {
            return false;
        }

        $admin->loginfailure = 0;
        $admin->logintime    = time();
        $admin->token        = Random::uuid();
        $admin->save();

        Session::set("admin", \rsa\RSA::getInstance()->encrypt([
            'id'       => $admin->id,
            'username' => $admin->username,
            'token'    => $admin->token,
            'erp_id'   => $admin->erp_id,
        ]));

        Session::set("adminlogin", $admin, 'think');

        $this->_user    = $admin;
        $this->_token   = $admin->token;
        $this->id       = $admin->id;
        $this->username = $admin->username;
        $this->_logined = TRUE;
        $this->erp_id   = $admin->erp_id;

        Token::set($admin->token, $admin->id);
        Session::set('accesstime', time());
        Cookie::set('token', $admin->token);
        $this->keeplogin($keeptime);
        return true;
    }

    /**
     * 校验登录
     * @param $username
     * @param $password
     * @return bool
     * @throws \think\exception\DbException
     */
    public function loginCheck($username, $password)
    {
        $admin = Admin::get(['username' => trim($username)]);
        if (!$admin) {
            $this->setError('账号输入错误,请重新输入');
            return false;
        }
        if ($admin->status != 1) {
            $this->setError('账号已禁用,请联系管理员');
            return false;
        }
        if (Config::get('app.login_failure_retry') && $admin->loginfailure >= 10 && time() - $admin->updatetime < 86400) {
            $this->setError('登录失败超过10次,请1天后再尝试登录');
            return false;
        }
        if (ENVIRONMENT == 'production') {
            if ($admin->password != $admin->encryptPassword($password, $admin->salt)) {
                $admin->loginfailure++;
                $admin->save();
                $this->setError('请输入正确的密码');
                return false;
            }
        }

        return $admin;
    }

    /**
     * 注销登录
     * @return bool
     * @throws \think\exception\DbException
     */
    public function logout()
    {
        $this->_logined = false;

        $admin = Admin::get(intval($this->id));
        if ($admin) {
            $admin->token = '';
            $admin->save();
        }

        Session::delete("admin");
        Session::delete('adminlogin');
        Session::delete('accesstime');
        Session::delete('token');
        Cookie::delete("keeplogin");
        Cookie::delete('token');
        return true;
    }

    /**
     * 自动登录
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function autologin()
    {
        $keeplogin = Cookie::get('keeplogin');
        if (!$keeplogin) {
            return false;
        }
        list($id, $keeptime, $expiretime, $key) = explode('|', $keeplogin);
        if ($id && $keeptime && $expiretime && $key && $expiretime > time()) {
            $admin = \rsa\RSA::getInstance()->decrypt(Session::get('admin'))->toArray();
            if (!$admin || !$admin['token']) {
                return false;
            }
            //token有变更
            if ($key != md5(md5($id) . md5($keeptime) . md5($expiretime) . $admin['token'])) {
                return false;
            }
            $this->_token   = $admin['token'];
            $this->id       = $admin['id'];
            $this->username = $admin['username'];
            $this->_logined = TRUE;
            $this->erp_id   = $admin['erp_id'];

            //刷新自动登录的时效
            //$this->keeplogin($keeptime);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 刷新保持登录的Cookie
     * @param int $keeptime
     * @return bool
     */
    protected function keeplogin($keeptime = 0)
    {
        if ($keeptime) {
            $expiretime = time() + $keeptime;

            $key  = md5(md5($this->id) . md5($keeptime) . md5($expiretime) . $this->_token);
            $data = [$this->id, $keeptime, $expiretime, $key];
            Cookie::set('keeplogin', implode('|', $data), 86400 * 30);
            return true;
        }
        return false;
    }

    /**
     * 权限校验
     * @param array|string $name
     * @param string $uid
     * @param string $relation
     * @param string $mode
     * @return bool
     */
    public function check($name, $uid = '', $relation = 'or', $mode = 'url')
    {
        return parent::check($name, $this->id, $relation, $mode);
    }

    /**
     * 检测当前控制器和方法是否匹配传递的数组
     * @param array $arr 需要验证权限的数组
     * @return bool
     */
    public function match($arr = [])
    {
        $request = Request::instance();
        $arr     = is_array($arr) ? $arr : explode(',', $arr);
        if (!$arr) {
            return FALSE;
        }

        $arr = array_map('strtolower', $arr);
        // 是否存在
        if (in_array(strtolower($request->action()), $arr) || in_array('*', $arr)) {
            return TRUE;
        }

        // 没找到匹配
        return FALSE;
    }

    /**
     * 检测是否登录
     * @return bool
     * @throws \think\exception\DbException
     */
    public function isLogin()
    {
        if ($this->logined) {
            return true;
        }
        $admin = \rsa\RSA::getInstance()->decrypt(Session::get('admin'))->toArray();
        if (!$admin) {
            return false;
        }
        //判断是否同一时间同一账号只能在一个地方登录
        if (Config::get('app.login_unique')) {
            $my = Admin::get($admin['id']);
            if (!$my || $my['token'] != $admin['token']) {
                return false;
            }
        }

        $this->id       = $admin['id'];
        $this->username = $admin['username'];
        $this->_token   = $admin['token'];
        $this->erp_id   = $admin['erp_id'];
        $this->logined  = true;

        if (!$this->autologin()) {
            return false;
        }
        return true;
    }

    /**
     * 获取当前请求的URI
     * @return string
     */
    public function getRequestUri()
    {
        return $this->requestUri;
    }

    /**
     * 设置当前请求的URI
     * @param string $uri
     */
    public function setRequestUri($uri)
    {
        $this->requestUri = $uri;
    }

    /**
     * 获取角色信息
     * @param null $uid
     * @return array
     */
    public function getGroups($uid = null)
    {
        $uid = is_null($uid) ? $this->id : $uid;
        return parent::getGroups($uid);
    }

    /**
     * 获取菜单权限
     * @param null $uid
     * @return array
     */
    public function getRuleList($uid = null)
    {
        $uid = is_null($uid) ? $this->id : $uid;
        return parent::getRuleList($uid);
    }

    /**
     * 获取User模型
     * @return User
     */
    public function getUser()
    {
        if ($this->_user) {
            return $this->_user;
        }
        return Admin::get(intval($this->id));
    }

    /**
     * 获取管理员数据
     * @param null $uid
     * @return mixed|null|static
     * @throws \think\exception\DbException
     */
    public function getUserInfo($uid = null)
    {
        if (is_null($uid)) {
            $admin = \rsa\RSA::getInstance()->decrypt(Session::get('admin'))->toArray();
            return !empty($admin) ? $admin : [];
        } else {
            return Admin::get(intval($uid))->toArray();
        }
    }

    /**
     * 获取菜单IDS
     * @param null $uid
     * @return array
     */
    public function getRuleIds($uid = null)
    {
        $uid = is_null($uid) ? $this->id : $uid;
        return parent::getRuleIds($uid);
    }

    /**
     * 判断是否拥有通用权限
     * @return bool
     */
    public function isSuperAdmin()
    {
        return in_array('*', $this->getRuleIds()) ? TRUE : FALSE;
    }

    /**
     * 白名单校验
     * @return bool
     */
    public function forbiddenip()
    {
        if (Env::get('app.debug', true)) return true;

        $forbiddenips = explode("\n", Config::get('config.forbiddenip'));
        if (empty($forbiddenips)) {
            return true;
        }
        $ip = request()->ip();
        foreach ($forbiddenips as $ips) {
            if ($ip == trim($ips) || '*' == trim($ips)) {
                return true;
            }
        }
        return false;
    }


    /**
     * 获取全部菜单规则列表
     * @author lamkakyun
     * @date 2018-12-28 09:39:41
     * @return void
     */
    public function getAllMenuRuleList()
    {
        $tmp      = collection(\app\common\model\AdminRule::where('status', '1')->where('ismenu', 1)->order('weigh', 'desc')->select())->toArray();
        $ruleList = [];
        foreach ($tmp as $v) {
            $ruleList[$v['id']] = $v;
        }
        return $ruleList;
    }


    /**
     * 将数组转换成 tree
     * @author lamkakyun
     * @date 2018-12-28 09:40:01
     * @return void
     */
    private function _toTree($ruleList)
    {
        $loop_time = 4;
        $time      = 0;
        while ($time < $loop_time) {
            foreach ($ruleList as $key => $value) {
                if (isset($value['pid']) && intval($value['pid']) > 0) {
                    $ruleList[$value['pid']]['children'][$key] = $value;
                }
            }
            $time++;
        }

        foreach ($ruleList as $key => $value) {
            if ($value['pid'] != 0) unset($ruleList[$key]);
        }

        return $ruleList;
    }

    public function getTopSubRuleIds($rule_id, $ruleList)
    {
        $sub_ids = [];
        $tree    = $this->_toTree($ruleList);

        $subtree = $tree[$rule_id];

        if (isset($subtree['children'])) {
            $sub_ids = array_merge(array_keys($subtree['children']), $sub_ids);
            foreach ($subtree['children'] as $value) {
                if (isset($value['children'])) $sub_ids = array_merge(array_keys($value['children']), $sub_ids);
            }
        }

        return $sub_ids;
    }


    /**
     * 获取左侧和顶部菜单栏
     * @param array $params URL对应的badge数据
     * @param string $fixedPage 默认页
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getSidebar($params = [], $fixedPage = '/index/index/index')
    {
        $colorArr  = ['red', 'green', 'yellow', 'blue', 'teal', 'orange', 'purple'];
        $colorNums = count($colorArr);
        $badgeList = [];
        $module    = request()->module();
        // 生成菜单的badge
        foreach ($params as $k => $v) {

            $url = $k;

            if (is_array($v)) {
                $nums  = isset($v[0]) ? $v[0] : 0;
                $color = isset($v[1]) ? $v[1] : $colorArr[(is_numeric($nums) ? $nums : strlen($nums)) % $colorNums];
                $class = isset($v[2]) ? $v[2] : 'label';
            } else {
                $nums  = $v;
                $color = $colorArr[(is_numeric($nums) ? $nums : strlen($nums)) % $colorNums];
                $class = 'label';
            }
            //必须nums大于0才显示
            if ($nums) {
                $badgeList[$url] = '<small class="' . $class . ' pull-right bg-' . $color . '">' . $nums . '</small>';
            }
        }

        // 读取管理员当前拥有的权限节点
        $userRule = $this->getRuleList();

        $select_id = 0;
        // 必须将结果集转换为数组
        $ruleList = $this->getAllMenuRuleList();

        foreach ($ruleList as $k => &$v) {
            if (!in_array($v['name'], $userRule)) {
                unset($ruleList[$k]);
                continue;
            }
            $select_id  = (trim($v['name'], '/') == $fixedPage || trim($v['name'], '/') . '/index' == $fixedPage) ? $v['id'] : $select_id;
            $v['url']   = $v['name'];
            $v['badge'] = isset($badgeList[$v['name']]) ? $badgeList[$v['name']] : '';
            $v['title'] = __($v['title']);
        }

        $menu = $nav = '';
        if (Config::get('erp.multiplenav')) {
            $topList = [];
            foreach ($ruleList as $index => $item) {
                if (!$item['pid']) {
                    $topList[] = $item;
                }
            }
            if ($select_id == 0 && !empty($topList)) {
                $select_id = $topList[0]['id'] ?? 0;
            }

            $selectParentIds = [];
            $tree            = Tree::instance();
            $tree->init($ruleList);
            if ($select_id) {
                $selectParentIds = $tree->getParentsIds($select_id, true);
            }

            foreach ($topList as $index => $item) {
                // $sub_ids   = $this->getTopSubRuleIds($item['id'], $ruleList);
                $childList = Tree::instance()->getTreeMenu($item['id'], '<li class="@class" pid="@pid"><a href="@url@addtabs" addtabs="@id" url="@url"><i class="@icon"></i> <span>@title</span> <span class="pull-right-container">@caret @badge</span></a> @childlist</li>', $select_id, '', 'ul', 'class="treeview-menu"');

                $current   = in_array($item['id'], $selectParentIds);
                $url       = $childList ? 'javascript:;' : url('/' . trim($item['url'], '/'));
                $addtabs   = $childList || !$url ? "" : (stripos($url, "?") !== false ? "&" : "?") . "ref=addtabs";
                $childList = str_replace('" pid="' . $item['id'] . '"', ' treeview ' . ($current ? '' : 'hidden') . '" pid="' . $item['id'] . '"', $childList);
                $nav       .= '<li class="' . ($current ? 'active' : '') . '"><a href="' . $url . $addtabs . '" addtabs="' . $item['id'] . '" url="' . $url . '"><i class="' . $item['icon'] . '"></i> <span>' . $item['title'] . '</span> <span class="pull-right-container"> </span></a> </li>';
                $menu      .= $childList;
            }
        } else {
            // 构造菜单数据
            Tree::instance()->init($ruleList);
            $menu = Tree::instance()->getTreeMenu(0, '<li class="@class"><a href="@url@addtabs" addtabs="@id" url="@url"><i class="@icon"></i> <span>@title</span> <span class="pull-right-container">@caret @badge</span></a> @childlist</li>', $select_id, '', 'ul', 'class="treeview-menu"');
        }
        return [$menu, $nav, $ruleList[$select_id] ?? []];
    }

    /**
     * 获取管理员所属于的分组ID
     * @param int $uid
     * @return array
     */
    public function getGroupIds($uid = null)
    {
        $groups   = $this->getGroups($uid);
        $groupIds = [];
        foreach ($groups as $K => $v) {
            $groupIds[] = (int) $v['group_id'];
        }
        return $groupIds;
    }

    /**
     * 取出当前管理员所拥有权限的分组
     * @param boolean $withself 是否包含当前所在的分组
     * @return array
     */
    public function getChildrenGroupIds($withself = false, $all = false)
    {
        //取出当前管理员所有的分组
        $groups   = $this->getGroups();
        $groupIds = [];
        foreach ($groups as $k => $v) {
            $groupIds[] = $v['id'];
        }
        // 取出所有分组
        $where = $all ? [] : ['status' => 1];

        $groupList = \app\common\model\AdminGroup::where($where)->select();
        $objList   = [];
        foreach ($groups as $K => $v) {
            if ($v['rules'] === '*') {
                $objList = $groupList;
                break;
            }
            // 取出包含自己的所有子节点
            $childrenList = Tree::instance()->init($groupList)->getChildren($v['id'], true);
            $obj          = Tree::instance()->init($childrenList)->getTreeArray($v['pid']);
            $objList      = array_merge($objList, Tree::instance()->getTreeList($obj));
        }
        $childrenGroupIds = [];
        foreach ($objList as $k => $v) {
            $childrenGroupIds[] = $v['id'];
        }
        if (!$withself) {
            $childrenGroupIds = array_diff($childrenGroupIds, $groupIds);
        }
        return $childrenGroupIds;
    }

    /**
     * 取出当前管理员所拥有权限的管理员
     * @param boolean $withself 是否包含自身
     * @return array
     */
    public function getChildrenAdminIds($withself = false)
    {
        $childrenAdminIds = [];
        if (!$this->isSuperAdmin()) {
            $groupIds      = $this->getChildrenGroupIds(false);
            $authGroupList = \app\common\model\AdminGroupAccess::
            field('admin_id as uid,group_id')
                ->where('group_id', 'in', $groupIds)
                ->select();

            foreach ($authGroupList as $k => $v) {
                $childrenAdminIds[] = $v['uid'];
            }
        } else {
            //超级管理员拥有所有人的权限
            $childrenAdminIds = Admin::column('id');
        }
        if ($withself) {
            if (!in_array($this->id, $childrenAdminIds)) {
                $childrenAdminIds[] = $this->id;
            }
        } else {
            $childrenAdminIds = array_diff($childrenAdminIds, [$this->id]);
        }
        return $childrenAdminIds;
    }

    /**
     * 获得面包屑导航
     * @param string $path
     * @return array
     */
    public function getBreadCrumb($path = '')
    {
        if ($this->breadcrumb || !$path)
            return $this->breadcrumb;
        $path_rule_id = 0;
        foreach ($this->rules as $rule) {
            $path_rule_id = $rule['name'] == $path ? $rule['id'] : $path_rule_id;
        }
        if ($path_rule_id) {
            $this->breadcrumb = Tree::instance()->init($this->rules)->getParents($path_rule_id, true);
            foreach ($this->breadcrumb as $k => &$v) {
                $v['url']   = url($v['name']);
                $v['title'] = __($v['title']);
            }
        }
        return $this->breadcrumb;
    }

    /**
     * 设置错误信息
     * @param $error 错误信息
     * @return Auth
     */
    public function setError($error)
    {
        $this->_error = $error;
        return $this;
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getError()
    {
        return $this->_error ? __($this->_error) : '';
    }
}
