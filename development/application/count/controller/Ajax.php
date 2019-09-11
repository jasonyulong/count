<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    kevin
 */

namespace app\count\controller;

use think\Lang;
use think\Config;
use app\count\library\OrgLib;
use app\common\library\ToolsLib;
use app\common\controller\Common;
use app\common\library\CarrierLib;

/**
 * Ajax异步请求接口
 * @internal
 */
class Ajax extends Common
{
    /**
     * 无需登录的方法,同时也就不需要鉴权了
     * @var array
     */
    protected $noNeedLogin = ['lang'];

    /**
     * 无需鉴权的方法,但需要登录
     * @var array
     */
    protected $noNeedRight = ['*'];

    /**
     * 视图布局文件
     * @var string
     */
    protected $layout = '';

    /**
     * 初始化执行
     */
    public function _initialize()
    {
        parent::_initialize();

        //设置过滤方法
        $this->request->filter(['strip_tags', 'htmlspecialchars']);
    }

    /**
     * 加载语言包
     * @return \think\response\Jsonp
     */
    public function lang()
    {
        header('Content-Type: application/javascript');
        $controllername = input("controllername");
        //默认只加载了控制器对应的语言名，你还根据控制器名来加载额外的语言包
        $this->loadlang($controllername);
        return jsonp(Lang::get(), 200, [], ['json_encode_param' => JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE]);
    }

    /**
     * 上传文件
     */
    public function upload()
    {
        Config::set('default_return_type', 'json');
        $file = $this->request->file('file');
        if (empty($file)) {
            $this->error(__('No file upload or server upload limit exceeded'));
        }

        //判断是否已经存在附件
        $sha1 = $file->hash();

        $upload = Config::get('upload');

        preg_match('/(\d+)(\w+)/', $upload['maxsize'], $matches);
        $type     = strtolower($matches[2]);
        $typeDict = ['b' => 0, 'k' => 1, 'kb' => 1, 'm' => 2, 'mb' => 2, 'gb' => 3, 'g' => 3];
        $size     = (int) $upload['maxsize'] * pow(1024, isset($typeDict[$type]) ? $typeDict[$type] : 0);
        $fileInfo = $file->getInfo();
        $suffix   = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
        $suffix   = $suffix ? $suffix : 'file';

        $mimetypeArr = explode(',', strtolower($upload['mimetype']));
        $typeArr     = explode('/', $fileInfo['type']);

        //验证文件后缀
        if ($upload['mimetype'] !== '*' &&
            (
                !in_array($suffix, $mimetypeArr)
                || (stripos($typeArr[0] . '/', $upload['mimetype']) !== false && (!in_array($fileInfo['type'], $mimetypeArr) && !in_array($typeArr[0] . '/*', $mimetypeArr)))
            )
        ) {
            $this->error(__('Uploaded file format is limited'));
        }
        $replaceArr = [
            '{year}'     => date("Y"),
            '{mon}'      => date("m"),
            '{day}'      => date("d"),
            '{hour}'     => date("H"),
            '{min}'      => date("i"),
            '{sec}'      => date("s"),
            '{random}'   => Random::alnum(16),
            '{random32}' => Random::alnum(32),
            '{filename}' => $suffix ? substr($fileInfo['name'], 0, strripos($fileInfo['name'], '.')) : $fileInfo['name'],
            '{suffix}'   => $suffix,
            '{.suffix}'  => $suffix ? '.' . $suffix : '',
            '{filemd5}'  => md5_file($fileInfo['tmp_name']),
        ];
        $savekey    = $upload['savekey'];
        $savekey    = str_replace(array_keys($replaceArr), array_values($replaceArr), $savekey);

        $uploadDir = substr($savekey, 0, strripos($savekey, '/') + 1);
        $fileName  = substr($savekey, strripos($savekey, '/') + 1);
        //
        $splInfo = $file->validate(['size' => $size])->move(ROOT_PATH . '/public' . $uploadDir, $fileName);
        if ($splInfo) {
            $this->success(__('Upload successful'), null, [
                'url' => $uploadDir . $splInfo->getSaveName()
            ]);
        } else {
            // 上传失败获取错误信息
            $this->error($file->getError());
        }
    }


    /**
     * 获取物流渠道
     * @author lamkakyun
     * @date 2018-12-12 15:58:53
     * @return array
     */
    public function getCarrierList()
    {
        $company_ids = $_POST['company_ids'];
        if (!$company_ids || !is_array($company_ids)) return ['code' => -1, 'msg' => __('参数错误')];
        
        // $key  = Config::get('redis.carrier');
        // $data = ToolsLib::getInstance()->getRedis()->get($key);
        $data = CarrierLib::init()->getCarrier();

        $carrier_list = [];
        foreach ($data as $v)
        {
            if (in_array($v['CompanyName'], $company_ids)) $carrier_list[] = $v;
        }

        return ['code' => 0, 'msg' => __('success'), 'data' => $carrier_list];
    }


    /**
     * 获取平台的所有 account
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-09-19 03:18:50
     */
    public function getPlatformUser()
    {
        $platform = $_POST['platform'];
        if (!$platform) return ['code' => -1, 'msg' => __('参数错误')];

        $all_accounts = ToolsLib::getInstance()->getAllAccounts(3);

        $account_list = [];
        if (is_array($platform))
        {
            foreach ($platform as $v)
            {
                $account_list = array_merge($account_list, $all_accounts[$v]);
            }
        }
        if (is_string($platform)) $account_list = $all_accounts[$platform];

        return ['code' => 0, 'msg' => __('success'), 'data' => $account_list];
    }

    /**
     * 获取日期
     * @AUTHOR: yang
     * @DATE: 2018-10-15
     */
    public function getDate(){
        $type = input('post.type');
        if($type == 'today')        $arr = ['start'=>date('Y-m-d'),'end'=>date('Y-m-d')];
        if($type == 'yesterday')    $arr = ['start'=>date('Y-m-d',strtotime('-1 day')),'end'=>date('Y-m-d',strtotime('-1 day'))];
        if($type == 'recently3day') $arr = ['start'=>date('Y-m-d',strtotime('-2 day')),'end'=>date('Y-m-d')];
        if($type == 'month') $arr = ['start'=>date('Y-m-d',strtotime(date('Y-m'))),'end'=>date('Y-m-d')];
        return json($arr);
    }

    /**
     * 下拉框
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-10 03:03:43
     */
    public function changeOrg()
    {
        $params = array_merge(input('get.'), input('post.'));
        $org_id = $params['org_id'];
        $seller = ToolsLib::getInstance()->getLevel1OrgSaleUser([$org_id]);

        $seller = array_column($seller, 'user_name');

        $auth_info = $_SESSION['think']['adminlogin'];
        if ($auth_info->erp_id)
        {
            $manage_info = OrgLib::getInstance()->getManageInfo($auth_info->username);
            $manage_users = $manage_info['manage_users'];
            $seller = array_values(array_intersect($seller, $manage_users));
        }

        return ['code' => 0, 'data' => array_values(array_unique($seller))];

    }
}
