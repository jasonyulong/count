<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    kevin
 */

namespace app\common\controller;

use app\common\library\Auth;
use think\Config;
use think\Controller;
use think\Hook;
use think\Lang;
use think\Session;

/**
 * 应用内控制器基类, 需要校验登录授权
 * Class Common
 * @package app\common\controller
 */
class Common extends Controller
{

    /**
     * 无需登录的方法,同时也就不需要鉴权了
     * @var array
     */
    protected $noNeedLogin = [];

    /**
     * 无需鉴权的方法,但需要登录
     * @var array
     */
    protected $noNeedRight = [];

    /**
     * 布局模板
     * @var string
     */
    protected $layout = 'default';

    /**
     * 弹窗布局模板
     * @var string
     */
    protected $layout_fluid = 'layout/fluid';

    /**
     * 权限控制类
     * @var Auth
     */
    protected $auth = null;

    /**
     * 模型对象
     * @var \think\Model
     */
    protected $model = null;

    /**
     * 快速搜索时执行查找的字段
     */
    protected $searchFields = 'id';

    /**
     * 是否是关联查询
     */
    protected $relationSearch = false;

    /**
     * 是否开启数据限制
     * 支持auth/personal
     * 表示按权限判断/仅限个人
     * 默认为禁用,若启用请务必保证表中存在admin_id字段
     */
    protected $dataLimit = false;

    /**
     * 数据限制字段
     */
    protected $dataLimitField = 'admin_id';

    /**
     * 数据限制开启时自动填充限制字段值
     */
    protected $dataLimitFieldAutoFill = true;

    /**
     * 是否开启Validate验证
     */
    protected $modelValidate = false;

    /**
     * 是否开启模型场景验证
     */
    protected $modelSceneValidate = false;

    /**
     * Multi方法可批量修改的字段
     */
    protected $multiFields = 'status';

    /**
     * Selectpage可显示的字段
     */
    protected $selectpageFields = '*';

    /**
     * 导入文件首行类型
     * 支持comment/name
     * 表示注释或字段名
     */
    protected $importHeadType = 'comment';

    /**
     * 头部样式追加
     * @var string
     */
    protected $bodyClass = '';

    public function _initialize()
    {
        $modulename     = $this->request->module();
        $controllername = strtolower($this->request->controller());
        $actionname     = strtolower($this->request->action());

        $path = str_replace('.', '/', $controllername) . '/' . $actionname;

        // 定义是否Dialog请求
        !defined('IS_DIALOG') && define('IS_DIALOG', input("dialog") ? TRUE : FALSE);

        // 定义是否AJAX请求
        !defined('IS_AJAX') && define('IS_AJAX', $this->request->isAjax());

        $this->auth = Auth::instance();

        // 设置当前请求的URI
        $this->auth->setRequestUri($path);
        // 检测是否需要验证登录
        if (!$this->auth->match($this->noNeedLogin)) {
            //检测是否登录
            if (!$this->auth->isLogin()) {
                Hook::listen('admin_nologin', $this);
                $url = Session::get('referer');
                $url = $url ? $url : $this->request->url();
                // $this->error(__('Please login first'), Config::get('site.apppath').url('index/login', ['url' => $url]), null, 30);
                $this->error(__('Please login first'), 'http://erp.spocoo.com/login.php', null, 30);
            }
            // 判断是否需要验证权限
            if (!$this->auth->match($this->noNeedRight)) {
                // 判断控制器和方法判断是否有对应权限
                if (!$this->auth->check($path)) {
                    Hook::listen('admin_nopermission', $this);
                    $this->error(__('You have no permission'), '');
                }
            }
        }

        // 如果有使用模板布局
        if ($this->layout) {
            $this->view->engine->layout('layout/' . $this->layout);
        }

        // 语言检测
        $lang = strip_tags($this->request->langset());
        // 应用自定义配置
        $site = Config::get("site");
        // 上传组件
        $upload = [
            'cdnurl'    => Config::get('cdnurl'),
            'uploadurl' => Config::get('uploadurl'),
            'bucket'    => 'local',
            'maxsize'   => Config::get('maxsize'),
            'mimetype'  => Config::get('mimetype'),
            'multipart' => [],
            'multiple'  => Config::get('multiple'),
        ];

        // 上传信息配置后
        Hook::listen("upload_config_init", $upload);

        // 配置信息
        $config = [
            'site'           => array_intersect_key($site, array_flip(['name', 'indexurl', 'cdnurl', 'version', 'timezone', 'languages'])),
            'upload'         => $upload,
            'modulename'     => $modulename,
            'controllername' => $controllername,
            'actionname'     => $actionname,
            'moduleurl'      => rtrim(url("/{$modulename}", '', false), '/'),
            'language'       => $lang,
            'referer'        => Session::get("referer")
        ];
        $config = array_merge($config, Config::get("view_replace_str"));

        Config::set('upload', array_merge(Config::get('upload'), $upload));

        // 配置信息后
        Hook::listen("config_init", $config);
        //加载当前控制器语言包
        $this->loadlang($controllername);
        //渲染站点配置
        $this->assign('site', $site);
        //渲染配置信息
        $this->assign('config', $config);
        //渲染权限对象
        $this->assign('auth', $this->auth);
        //渲染管理员对象
        $this->assign('admin', Session::get('admin'));
        //默认标题
        $this->assign('title', Config::get('site.name'));
        //body class
        $this->assign('bodyClass', $this->bodyClass);
    }

    /**
     * 加载语言文件
     * @param string $name
     */
    protected function loadlang($name)
    {
        Lang::load(APP_PATH . $this->request->module() . '/lang/' . $this->request->langset() . '/' . str_replace('.', '/', $name) . '.php');
    }

    /**
     * 渲染配置信息
     * @param mixed $name 键名或数组
     * @param mixed $value 值
     */
    protected function assignconfig($name, $value = '')
    {
        $this->view->config = array_merge($this->view->config ? $this->view->config : [], is_array($name) ? $name : [$name => $value]);
    }

    /**
     * 生成查询所需要的条件,排序方式
     * @param mixed $searchfields 快速查询的字段
     * @param boolean $relationSearch 是否关联查询
     * @return array
     */
    protected function buildparams($searchfields = null, $relationSearch = null)
    {
        $searchfields   = is_null($searchfields) ? $this->searchFields : $searchfields;
        $relationSearch = is_null($relationSearch) ? $this->relationSearch : $relationSearch;
        $search         = $this->request->get("search", '');
        $filter         = $this->request->get("filter", '');
        $op             = $this->request->get("op", '', 'trim');
        $sort           = $this->request->get("sort", "id");
        $order          = $this->request->get("order", "DESC");
        $offset         = $this->request->get("offset", 0);
        $limit          = $this->request->get("limit", 0);
        $filter         = (array) json_decode($filter, TRUE);
        $op             = (array) json_decode($op, TRUE);
        $filter         = $filter ? $filter : [];
        $where          = [];
        $tableName      = '';
        if ($relationSearch) {
            if (!empty($this->model)) {
                $name      = \think\Loader::parseName(basename(str_replace('\\', '/', get_class($this->model))));
                $tableName = $name . '.';
            }
            $sortArr = explode(',', $sort);
            foreach ($sortArr as $index => & $item) {
                $item = stripos($item, ".") === false ? $tableName . trim($item) : $item;
            }
            unset($item);
            $sort = implode(',', $sortArr);
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            $where[] = [$tableName . $this->dataLimitField, 'in', $adminIds];
        }
        if ($search) {
            $searcharr = is_array($searchfields) ? $searchfields : explode(',', $searchfields);
            foreach ($searcharr as $k => &$v) {
                $v = stripos($v, ".") === false ? $tableName . $v : $v;
            }
            unset($v);
            $where[] = [implode("|", $searcharr), "LIKE", "%{$search}%"];
        }
        foreach ($filter as $k => $v) {
            $sym = isset($op[$k]) ? $op[$k] : '=';
            if (stripos($k, ".") === false) {
                $k = $tableName . $k;
            }
            $v   = !is_array($v) ? trim($v) : $v;
            $sym = strtoupper(isset($op[$k]) ? $op[$k] : $sym);
            switch ($sym) {
                case '=':
                case '!=':
                    $where[] = [$k, $sym, (string) $v];
                    break;
                case 'LIKE':
                case 'NOT LIKE':
                case 'LIKE %...%':
                case 'NOT LIKE %...%':
                    $where[] = [$k, trim(str_replace('%...%', '', $sym)), "%{$v}%"];
                    break;
                case '>':
                case '>=':
                case '<':
                case '<=':
                    $where[] = [$k, $sym, intval($v)];
                    break;
                case 'FINDIN':
                case 'FINDINSET':
                case 'FIND_IN_SET':
                    $where[] = "FIND_IN_SET('{$v}', " . ($relationSearch ? $k : '`' . str_replace('.', '`.`', $k) . '`') . ")";
                    break;
                case 'IN':
                case 'IN(...)':
                case 'NOT IN':
                case 'NOT IN(...)':
                    $where[] = [$k, str_replace('(...)', '', $sym), is_array($v) ? $v : explode(',', $v)];
                    break;
                case 'BETWEEN':
                case 'NOT BETWEEN':
                    $arr = array_slice(explode(',', $v), 0, 2);
                    if (stripos($v, ',') === false || !array_filter($arr))
                        continue;
                    //当出现一边为空时改变操作符
                    if ($arr[0] === '') {
                        $sym = $sym == 'BETWEEN' ? '<=' : '>';
                        $arr = $arr[1];
                    } else if ($arr[1] === '') {
                        $sym = $sym == 'BETWEEN' ? '>=' : '<';
                        $arr = $arr[0];
                    }
                    $where[] = [$k, $sym, $arr];
                    break;
                case 'RANGE':
                case 'NOT RANGE':
                    $v   = str_replace(' - ', ',', $v);
                    $arr = array_slice(explode(',', $v), 0, 2);
                    if (stripos($v, ',') === false || !array_filter($arr))
                        continue;
                    //当出现一边为空时改变操作符
                    if ($arr[0] === '') {
                        $sym = $sym == 'RANGE' ? '<=' : '>';
                        $arr = $arr[1];
                    } else if ($arr[1] === '') {
                        $sym = $sym == 'RANGE' ? '>=' : '<';
                        $arr = $arr[0];
                    }
                    $where[] = [$k, str_replace('RANGE', 'BETWEEN', $sym) . ' time', $arr];
                    break;
                case 'LIKE':
                case 'LIKE %...%':
                    $where[] = [$k, 'LIKE', "%{$v}%"];
                    break;
                case 'NULL':
                case 'IS NULL':
                case 'NOT NULL':
                case 'IS NOT NULL':
                    $where[] = [$k, strtolower(str_replace('IS ', '', $sym))];
                    break;
                default:
                    break;
            }
        }
        $where = function ($query) use ($where) {
            foreach ($where as $k => $v) {
                if (is_array($v)) {
                    call_user_func_array([$query, 'where'], $v);
                } else {
                    $query->where($v);
                }
            }
        };
        return [$where, $sort, $order, $offset, $limit];
    }


    /**
     * 发送分页 数据 到模板
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-10 06:30:20
     */
    protected function _assignPagerData($thisobj, $params, $total)
    {
        $current_url = url('', '', '');
        if ($_GET) $current_url = $current_url . '?' . http_build_query(array_filter($_GET, function ($val) {
                return $val != '';
            }));
        //分页
        $pager_data = gen_pager_data($params['p'], $total, $params['ps']);
        $thisobj->assign('all_page_num', $pager_data['all_page_num']);
        $thisobj->assign('last_page', $pager_data['last_page']);
        $thisobj->assign('next_page', $pager_data['next_page']);
        $thisobj->assign('current_url', $current_url);
    }
}