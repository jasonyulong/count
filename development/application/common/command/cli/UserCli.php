<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    mina
 */

namespace app\common\command\cli;

use think\Db;
use think\Config;
use think\console\Input;
use think\console\Output;
use app\common\model\User;
use app\common\model\Admin;
use think\cache\driver\Redis;
use app\common\library\ToolsLib;
use app\common\model\AdminGroup;
use app\common\model\Organization;
use app\common\model\ErpModelFactory;
use app\common\model\AdminGroupAccess;

/**
 * 用户数据同步
 * Class Common
 * @package app\common\command\cli
 */
class UserCli
{
    /**
     * redis链接句柄
     * @var Redis object
     */
    private $redis;
    private $admin_group_model;
    private $admin_model;
    private $admin_group_access_model;
    private $businessIds = [];

    /**
     * 构造函数
     *
     * Common constructor.
     * @param Input $input 输入
     * @param Output $output 输出
     */
    public function __construct(Input $input, Output $output)
    {
        $this->redis                    = new Redis(Config::get('redis'));
        $this->admin_group_model        = new AdminGroup();
        $this->admin_model              = new Admin();
        $this->admin_group_access_model = new AdminGroupAccess();
    }

    /**
     * @desc   同步用户
     * @author mina
     * @param  void
     * @return string
     */
    public function user(): string
    {
        $model = new User();
        $field = [
            'id',
            'username',
            'truename',
            'ebayaccounts',
            'is_del',
        ];
        $rows  = $model->field($field)->select();
        if (empty($rows)) {
            return "empty";
        }
        $data = [];
        foreach ($rows as $key => $value) {
            $data[$value['id']] = $value->toArray();
        }
        $status = $this->redis->set(Config::get('redis.user_list'), $data);
        return $status ? "success" : 'fail';
    }


    /**
     * 同步用户组织架构
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-08 11:24:44
     */
    public function org(): string
    {
        $model = ErpModelFactory::createSysOrganizationModel();

        $field = '*';
        $rows  = $model->field($field)->select();

        if (empty($rows)) return 'empty';

        // todo: 将 id 放到 key 的位置上
        $tmp  = $rows;
        $rows = [];
        array_map(function ($val) use (&$rows) {
            $rows[$val['id']] = $val;
        }, $tmp);

        $status = $this->redis->set(Config::get('redis.org_list'), $rows);
        return $status ? "success" : 'fail';
    }


    /**
     * 同步组织架构用户表
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-08 02:29:44
     */
    public function orgUser(): string
    {
        $model = ErpModelFactory::createSysOrganizationUserModel();
        $field = 'id, user_id, user_name, organize_id, status';
        $rows  = $model->field($field)->select();

        if (empty($rows)) return 'empty';

        $status = $this->redis->set(Config::get('redis.org_user_list'), $rows);
        return $status ? "success" : 'fail';
    }


    /**
     * 组织架构属性 与 EBAY
     * @desc 业务部的的人，登陆系统，要判断，可以管理哪些账户信息，所以必须同步这个表
     * @author lamkakyun
     * @date 2019-01-15 16:27:58
     * @return void
     */
    public function orgPropAndOrgEbay()
    {
        $org_property_model = ErpModelFactory::createSysOrganizationPropertyModel();
        $org_ebay_model     = ErpModelFactory::createSysOrganizationEbayModel();

        $field = 'id,user_id, organize_id, value';
        // ebay 的数据是脏数据
        $rows  = $org_property_model->field($field)->where(['groups' => ['NEQ', 'ebay']])->select();
        $this->redis->set(Config::get('redis.org_prop_list'), $rows);

        $field = 'user_id, organize_id, account, store_id, sales_label,locations';
        $rows  = $org_ebay_model->field($field)->select();
        $this->redis->set(Config::get('redis.org_ebay_list'), $rows);

        return 'bingo!';
    }


    /**
     * 同不ERP 的用户到 count的 权限系统
     * @return bool|void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function syncUserToAdmin()
    {
        // 同步组织架构和用户
        $this->org();
        $this->orgUser();
        $this->user();
        $this->businessIds = [];

        $org_tree = ToolsLib::getInstance()->getBusinessOrgTree(true);
        $now_time = time();

        $erp_users = $this->_getSyncErpUsers(1);

        $top_group = $this->admin_group_model->where(['name' => '业务部', 'pid' => 0])->find();

        if (!$top_group) {
            $top_group    = [
                'pid'        => 0,
                'name'       => '业务部',
                'status'     => 1,
                'createtime' => $now_time,
                'updatetime' => $now_time,
            ];
            $top_group_id = $this->admin_group_model->insertGetId($top_group);
        } else {
            $top_group_id = $top_group['id'];
        }
        if (!$top_group_id) {
            echo "【ERP业务部】角色创建失败\n";
            return;
        }

        $usersIds = array_column($erp_users, 'id');

        $this->_syncUserToAdmin($top_group_id, $org_tree, $now_time, $erp_users);

        $otherIds = array_diff($usersIds, $this->businessIds);
        if (!empty($otherIds)) {
            $this->_syncOtherUserToAdmin($otherIds, $erp_users);
        }

        echo "bingo\n";
        return true;
    }

    /**
     * 获取erp的用户
     * @param int $format_type
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function _getSyncErpUsers($format_type = 0)
    {
        $model  = new User();
        $where  = ['is_del' => 0, 'is_count' => 1]; // 获取授权同意登陆count 的ERP用户
        $fields = 'id, username, truename, password, mail';
        $data   = $model->field($fields)->where($where)->order('username asc')->select()->toArray();

        // 将 username 放到 key上
        if ($format_type == 1) {
            $tmp  = $data;
            $data = [];
            foreach ($tmp as $k => $v) {
                $data[$v['username']] = $v;
            }
        }

        return $data;
    }

    /**
     * @param $ids
     * @param $erp_users
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function _syncOtherUserToAdmin($ids, $erp_users)
    {
        $now_time = time();
        foreach ($erp_users as $user) {
            $user_id  = $user['id'];
            $truename = $user['truename'];
            if (!in_array($user_id, $ids)) continue;

            echo "正在同步角色【{$truename}】\n";
            $where_org  = ['name' => $truename, 'pid' => 0];
            $group_data = $this->admin_group_model->where($where_org)->find();
            if (!$group_data) {
                $add_group_data = [
                    'pid'        => 0,
                    'name'       => $truename,
                    'status'     => 1,
                    'createtime' => $now_time,
                    'updatetime' => $now_time,
                ];

                $group_id = $this->admin_group_model->insertGetId($add_group_data);
            } else {
                $group_id = $group_data['id'];
            }
            if (!$group_id) {
                echo "【" . $truename . "】角色创建失败\n";
                continue;
            }

            $admin_id = $this->admin_model->where(['id' => $user_id])->value('id');
            if (!$admin_id) {
                $add_admin_data = [
                    'username'   => $user['username'],
                    'password'   => $user['password'],
                    'email'      => $user['mail'],
                    'status'     => 1,
                    'createtime' => $now_time,
                    'updatetime' => $now_time,
                    'erp_id'     => 0,
                    'id'         => $user['id'],
                ];

                try {
                    $admin_id = $this->admin_model->insertGetId($add_admin_data);
                } catch (\Exception $e) {
                    echo "重复键值对:ERP用户\n";
                    continue;
                }

            } else {
                $save_admin_data = [
                    'username'   => $user['username'],
                    'password'   => $user['password'],
                    'email'      => $user['mail'],
                    'updatetime' => $now_time,
                ];
                $ret_update      = $this->admin_model->where(['id' => $admin_id])->update($save_admin_data);
            }

            if (!$admin_id) {
                echo "ERP 用户{$user['username']}同步失败\n";
                continue;
            }

            $add_group_access_data = [
                'admin_id' => $admin_id,
                'group_id' => $group_id,
            ];

            try {
                $this->admin_group_access_model->insert($add_group_access_data);
            } catch (\Exception $e) {
                echo "重复键值对:角色\n";
                continue;
            }

        }
    }

    /**
     * 递归 组织架构树
     * @param $pid
     * @param $org_tree
     * @param $now_time
     * @param $erp_users
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function _syncUserToAdmin($pid, $org_tree, $now_time, &$erp_users)
    {
        foreach ($org_tree as $org) {
            echo "正在同步角色【{$org['name']}】\n";
            $where_org  = ['name' => $org['name'], 'pid' => $pid];
            $group_data = $this->admin_group_model->where($where_org)->find();
            if (!$group_data) {
                $add_group_data = [
                    'pid'        => $pid,
                    'name'       => $org['name'],
                    'status'     => 1,
                    'createtime' => $now_time,
                    'updatetime' => $now_time,
                ];

                $group_id = $this->admin_group_model->insertGetId($add_group_data);
            } else {
                $group_id = $group_data['id'];
            }
            if (!$group_id) {
                echo "【" . $org['name'] . "】角色创建失败\n";
                continue;
            }

            $username_list = array_keys($erp_users);
            foreach ($org['seller_list'] as $v) {
                $tmp_arr    = explode('___', $v);
                $tmp_seller = $tmp_arr[0];
                $tmp_org_id = $tmp_arr[1];


                $user_info = $erp_users[$tmp_seller] ?? [];

                // 业务部
                if ($org['id'] == $tmp_org_id && $user_info) {
                    if ($user_info['username'] == 'vipadmin') continue;

                    echo "正在同步用户【{$user_info['username']}】\n";

                    // $admin_id = $this->admin_model->where(['username' => $user_info['username']])->value('id');
                    $admin_id            = $this->admin_model->where(['id' => $user_info['id']])->value('id');
                    $this->businessIds[] = $user_info['id'];
                    if (!$admin_id) {
                        $add_admin_data = [
                            'username'   => $user_info['username'],
                            'password'   => $user_info['password'],
                            'email'      => $user_info['mail'],
                            'status'     => 1,
                            'createtime' => $now_time,
                            'updatetime' => $now_time,
                            'erp_id'     => $user_info['id'],
                            'id'         => $user_info['id'],
                        ];

                        try {
                            $admin_id = $this->admin_model->insertGetId($add_admin_data);
                        } catch (\Exception $e) {
                            echo "重复键值对:ERP用户\n";
                            continue;
                        }

                    } else {
                        $save_admin_data = [
                            'username'   => $user_info['username'],
                            'password'   => $user_info['password'],
                            'email'      => $user_info['mail'],
                            'updatetime' => $now_time,
                        ];
                        $ret_update      = $this->admin_model->where(['id' => $admin_id])->update($save_admin_data);
                    }

                    if (!$admin_id) {
                        echo "ERP 用户{$user_info['username']}同步失败\n";
                        continue;
                    }

                    $add_group_access_data = [
                        'admin_id' => $admin_id,
                        'group_id' => $group_id,
                    ];

                    try {
                        $this->admin_group_access_model->insert($add_group_access_data);
                    } catch (\Exception $e) {
                        echo "重复键值对:角色\n";
                        continue;
                    }
                }
            }
            // 递归的终止条件
            if (!empty($org['children'])) {
                $this->_syncUserToAdmin($group_id, $org['children'], $now_time, $erp_users);
            }
        }
    }
}
