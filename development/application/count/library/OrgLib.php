<?php


namespace app\count\library;

use app\common\library\ToolsLib;

/**
 * 组织架构 相关的 库
 * @desc 之前的组织架构，都是写在 ToolsLib ，但组织架构很复杂，而且很容易乱，所以独立出一个文件
 * Class FinanceAmountLib
 * @package app\count\library\finance
 */
class OrgLib
{
    private static $instance = null;

    /**
     * single
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-09-12 12:02:35
     */
    public static function getInstance()
    {
        if (!static::$instance) {
            static::$instance = new OrgLib();
        }
        return static::$instance;
    }

    /**
     * 获取所有组织架构
     * @desc 直接从redis 中取出,本质是 sys_organization 中的全部数据
     * @author lamkakyun
     * @date 2019-01-09 15:00:18
     * @return void
     */
    public function getAllOrgs($format_type = 0)
    {
        $key  = config('redis.org_list');
        $data = ToolsLib::getInstance()->getRedis()->get($key);

        $tmp  = $data;
        $data = [];
        foreach ($tmp as $value) {
            // todo: 去除FBA这个部门
            if ($value['id'] == '20') continue;
            $data[] = $value;
        }

        switch ($format_type) {
            case '0':
                // do nothing
                break;
            case '1':// 将id 放在 key 的位置上
                $tmp  = $data;
                $data = [];
                foreach ($tmp as $value) {
                    $data[$value['id']] = $value;
                }
                break;
            case '2':  // 1.将id 放在 key 的位置上 2.只获取业务部的组织架构 

                $tmp  = $data;
                $data = [];
                foreach ($tmp as $value) {
                    if ($value['tid'] == 19)
                        $data[$value['id']] = $value;
                }
                break;
        }

        return $data;
    }


    /**
     * 直接从redis 中取出,本质是 sys_organization_user 中的全部数据
     * @author lamkakyun
     * @date 2019-01-09 15:07:23
     * @return void
     */
    public function getAllOrgUsers($format_type = 0)
    {
        $key  = config('redis.org_user_list');
        $data = ToolsLib::getInstance()->getRedis()->get($key);

        switch ($format_type) {
            case '0':
                // do nothing
                break;
            case '1': // 将id 放在 key 的位置上
                $tmp  = $data;
                $data = [];
                foreach ($tmp as $value) {
                    // if ($value['status'] == 0) continue;
                    $data[$value['id']] = $value;
                }
                break;
            case '2': // 用户按组织架构分组
                $tmp  = $data;
                $data = [];
                foreach ($tmp as $value) {
                    // if ($value['status'] == 0) continue;
                    $data[$value['organize_id']][$value['id']] = $value;
                }
                break;
            case '3': // 因为一个用户可以属于多个组织架构，所以可以根据用户名分组
                $tmp  = $data;
                $data = [];
                foreach ($tmp as $value) {
                    // if ($value['status'] == 0) continue;
                    $data[$value['user_name']][$value['id']] = $value;
                }
                break;
        }

        return $data;
    }


    /**
     * 将组织架构变成一个 无限分级树 (根树)
     * @author lamkakyun
     * @date 2019-01-09 15:09:33
     * @return void
     */
    public function getOrgRootTree()
    {
        $redis_key = config('redis.org_super_tree');
        $data      = ToolsLib::getInstance()->getRedis()->get($redis_key);
        if ($data) return $data;

        $all_orgs         = $this->getAllOrgs(1);
        $all_org_users    = $this->getAllOrgUsers(2);
        $all_org_props    = $this->getOrgProps(1);
        $org_ebay_data    = $this->getOrgEbay();
        $all_org_ebay     = $org_ebay_data['data'];
        $all_ebay_org_ids = $org_ebay_data['all_ebay_org_ids'];

        // 组织架构最高 4层，我们设置为5层，以防bug
        $current_level = 5;
        $min_level     = 1;

        while ($current_level > $min_level) {
            $tmp_orgs   = $all_orgs;
            $level_orgs = [];
            // 逐层级删除 组织架构，以构造树结构
            foreach ($tmp_orgs as $key => $value) {
                if ($value['level'] == $current_level) {
                    unset($all_orgs[$key]);
                    $level_orgs[$value['parent_id']][$value['id']] = $value;
                }
            }

            // 获取或统计子组织架构的信息
            foreach ($all_orgs as $key => $value) {
                $children = $level_orgs[$value['id']] ?? [];

                // TODO: 设置子ID，和多级的子ID
                $child_ids      = array_column($children, 'id');
                $full_child_ids = [$value['id']]; // 包含自己
                foreach ($children as $v) {
                    $full_child_ids = array_merge($v['child_ids'] ?? [], $full_child_ids);
                }
                $full_child_ids = array_merge($child_ids, $full_child_ids);

                // TODO: 添加，组织架构下面的销售员信息
                $tmp_all_user_list = [];
                foreach ($full_child_ids as $_cid) {
                    $_tmp_user_list    = $all_org_users[$_cid] ?? [];
                    $tmp_all_user_list = array_merge($_tmp_user_list, $tmp_all_user_list);
                }

                $_org_user_list         = [];
                $_org_user_account_list = [];
                foreach ($tmp_all_user_list as $v) {
                    $_org_user_list[] = $v;
                    $_tmp_key         = $v['user_id'] . '_' . $v['organize_id'];

                    // if (in_array($value['id'], $all_ebay_org_ids)) {
                    if (isset($all_org_ebay[$_tmp_key])) $_org_user_account_list = array_merge($all_org_ebay[$_tmp_key], $_org_user_account_list);
                    // } else {
                    if (isset($all_org_props[$_tmp_key])) $_org_user_account_list = array_merge($all_org_props[$_tmp_key], $_org_user_account_list);
                    // }
                }

                // 迭代的 用户列表（组织架构所有后代的用户）
                $all_orgs[$key]['org_full_user_list']         = $_org_user_list;
                $all_orgs[$key]['org_full_user_account_list'] = array_unique($_org_user_account_list);
                // 下级子Id
                $all_orgs[$key]['child_ids'] = $child_ids;
                // 迭代的 子ID （组织架构所有后代的子Id）
                $all_orgs[$key]['full_child_ids'] = $full_child_ids;
                $all_orgs[$key]['children']       = $children;
            }

            $current_level--;
        }

        ToolsLib::getInstance()->getRedis()->set($redis_key, $all_orgs, 5 * 60);
        return $all_orgs;
    }


    /**
     * 组织架构树转换为一维数组 (递归)
     * @author lamkakyun
     * @date 2019-01-09 17:06:40
     * @return void
     */
    public function _orgTreeToArray($tree, &$ret_data)
    {
        foreach ($tree as $key => $value) {
            $children = $value['children'] ?? [];
            if ($children) unset($value['children']);
            $ret_data[$value['id']] = $value;
            if ($children) $this->_orgTreeToArray($children, $ret_data);
        }
    }


    /**
     * 获取组织架构的一维数组
     * @author lamkakyun
     * @date 2019-01-09 17:13:26
     * @return void
     */
    public function getOrgRootArray()
    {
        $data = [];
        $tree = $this->getOrgRootTree();
        $this->_orgTreeToArray($tree, $data);
        return $data;
    }

    /**
     * 获取业务部的组织架构树
     * @author lamkakyun
     * @date 2019-01-09 15:44:37
     * @return void
     */
    public function getBussinessOrgTree()
    {
        $org_id = 19; // 业务部的 组织id
        $tree   = $this->getOrgRootTree();
        return [$org_id => $tree[$org_id]];
    }


    /**
     * 获取业务部的组织架构 一维数组
     * @author lamkakyun
     * @date 2019-01-09 17:22:01
     * @return void
     */
    public function getBussinessOrgArray($is_remove_top = false)
    {
        $data = [];
        $tree = $this->getBussinessOrgTree();
        $this->_orgTreeToArray($tree, $data);
        
        if ($is_remove_top && isset($data[19])) unset($data[19]);
        return $data;
    }


    /**
     * 获取所有业务员
     * @author lamkakyun
     * @date 2019-04-02 20:09:32
     * @return void
     */
    public function getAllSellers($format_type = 0)
    {
        $data = $this->getBussinessOrgArray();
        $data = $data[19]['org_full_user_list'];
        $ret_data = [];
        switch ($format_type)
        {
            case '0':
                $ret_data = $data; // do nothing
                break;
            case '1': // user_id -- user_name 映射
                foreach ($data as $value)
                {
                    $ret_data[$value['user_id']] = $value['user_name'];
                }
                break;
        }
        return $ret_data;
    }


    /**
     * GET 业务部的所有负责人(这个责任人不一定存在于 admin表中，只是ERP中的数据)
     * @author lamkakyun
     * @date 2019-01-10 10:00:21
     * @return void
     */
    public function getBussinessOrgManagers()
    {
        $data = $this->getBussinessOrgArray();
        return array_unique(array_column($data, 'manage'));
    }


    /**
     * 获取子组织架构 id
     * @author lamkakyun
     * @date 2019-03-02 10:12:36
     * @return void
     */
    public function getSubOrgIds($org_id)
    {
        $data = $this->getBussinessOrgArray();
        return $data[$org_id]['full_child_ids'] ?? [];
    }


    /**
     * 获取组织架构 属性
     * @author lamkakyun
     * @date 2019-01-15 17:05:35
     * @return void
     */
    public function getOrgProps($format_type = 0)
    {
        $key  = config('redis.org_prop_list');
        $data = ToolsLib::getInstance()->getRedis()->get($key);
        switch ($format_type) {
            // 将 user_id org_id 放到key 上
            case '1':
                $tmp  = $data;
                $data = [];
                foreach ($tmp as $k => $v) {
                    if (empty($v['value'])) continue;
                    $keys        = $v['user_id'] . '_' . $v['organize_id'];
                    $datas       = explode(',', $v['value']);
                    $data[$keys] = isset($data[$keys]) ? array_merge($data[$keys], $datas) : $datas;
                }
                break;
        }
        return $data;
    }


    /**
     * 通过account，获取管理他的业务员，和组织架构（非Ebay平台）
     * @author lamkakyun
     * @date 2019-04-04 14:11:36
     * @return void
     */
    public function getAccountToUserIdOrgIdMap()
    {
        $key  = config('redis.org_prop_account_to_userid_and_org_id_map');
        $map = ToolsLib::getInstance()->getRedis()->get($key);
        if (!$map)
        {
            $map = [];
            $org_props = $this->getOrgProps();
            foreach ($org_props as $value)
            {
                $account_list = array_map('trim', explode(',', $value['value']));
                foreach ($account_list as $v)
                {
                    $map[$v] = ['org_id' => $value['organize_id'], 'user_id' => $value['user_id']];
                }
                
            }

            ToolsLib::getInstance()->getRedis()->set($key, $map, 10 * 60);
        }
        return $map;
    }


    // public function getEbayAccountToUserIdOrgIdMap()
    // {
    //     $key  = config('redis.org_ebay_account_to_userid_and_org_id_map');
    //     $map = ToolsLib::getInstance()->getRedis()->get($key);
    //     if (!$map)
    //     {
    //         $map = [];
    //         $org_ebay = $this->getOrgEbay(2);
    //         echo '<pre>';var_dump($org_ebay);echo '</pre>';
    //         exit;
    //         $org_ebay = $org_ebay['data'];
    //         $all = [];
    //         foreach ($org_ebay as $value)
    //         {
    //             $all = array_merge($all, $value);
    //         }
    //         echo '<pre>';var_dump(\find_duplicates($all));echo '</pre>';
    //         exit;
    //     }
    //     return $map;
    // }

    /**
     * 根据【 账号 +业务员 + location + 仓库 + 销售标签 】获取组织架构 ID
     * 理论上， 只能对应 一个 org_id, 如果拥有,只能说明是 脏数据！
     * @author lamkakyun
     * @date 2019-04-04 15:36:00
     * @return void
     */
    public function getEbayOrgid($account, $seller_user_id, $location, $store_id, $sale_lable)
    {
        $org_ebay = $this->getOrgEbay(2);
        foreach ($org_ebay as $key => $value)
        {
            if ($value['account'] != $account) continue;
            if ($value['user_id'] != $seller_user_id) continue;
            if ($value['store_id'] != $store_id) continue;

            $tmp_locations = array_map(function($v){return strtoupper(trim($v));}, explode('*', $value['locations']));
            $tmp_locations = array_filter($tmp_locations, function($v) {return !empty($v);});

            $tmp_sale_lables = array_map('trim', explode(',', $value['sales_label']));
            $tmp_sale_lables = array_filter($tmp_sale_lables, function($v) {return !empty($v);});

            if (!in_array(strtoupper($location), $tmp_locations)) continue;
            if (!in_array($sale_lable, $tmp_sale_lables)) continue;

            return intval($value['organize_id']);
        }

        return 0;
    }


    /**
     * 获取组织架构 ebay 属性
     * @author lamkakyun
     * @date 2019-01-15 17:10:21
     * @return void
     */
    public function getOrgEbay($format_type = 0)
    {
        $key  = config('redis.org_ebay_list');
        $data = ToolsLib::getInstance()->getRedis()->get($key);

        $ret_data = [];
        switch($format_type)
        {
            // 销售员和 ebay 账户 的 map
            case '1':
                foreach ($data as $value)
                {
                    $ret_data[$value['user_id']][] = $value['account'];
                }
                break;
            case '2':
                $ret_data = $data;
                break;
            default:
                // 所有的ebay 相关的组织架构
                $all_ebay_org_ids = array_unique(array_column($data, 'organize_id'));

                $tmp  = $data;
                $data = [];
                foreach ($tmp as $key => $value) {
                    if (empty($value['account'])) continue;
                    $data[$value['user_id'] . '_' . $value['organize_id']][] = $value['account'];
                }

                foreach ($data as $key => $value) {
                    $data[$key] = array_unique($value);
                }

                $ret_data = ['all_ebay_org_ids' => $all_ebay_org_ids, 'data' => $data];

                break;
        }

        return $ret_data;
        
    }


    /**
     * 组织架构中用户能够管理或查看的信息
     * @author lamkakyun
     * @date 2019-01-10 10:14:03
     * @return void
     */
    public function getManageInfo($username = '林燕霞' /*= '洪艳纯' 林燕霞*/)
    {
        // 这是一个基于缓存的缓存
        $redis_key = config('redis.org_user_manage') . ':' . $username;
        $data      = ToolsLib::getInstance()->getRedis()->get($redis_key);
        if ($data) return $data;

        $top_bussiness_org_ids = $this->getTopBussinessOrgIds();
        $all_org_users         = $this->getAllOrgUsers(3);
        $all_erp_users         = ToolsLib::getInstance()->getAllUsers(2);

        $all_org_props    = $this->getOrgProps(1);
        $org_ebay_data    = $this->getOrgEbay();
        $all_org_ebay     = $org_ebay_data['data'];
        $all_ebay_org_ids = $org_ebay_data['all_ebay_org_ids'];

        $current_user_info = $all_erp_users[$username];
        $belong_org_ids    = array_column($all_org_users[$username], 'organize_id');

        $top_orgs_id_map    = $this->getTopBussinessOrgIdsMap();
        $belong_top_orgs_id = [];
        foreach ($belong_org_ids as $v) {
            if (isset($top_orgs_id_map[$v])) $belong_top_orgs_id[] = $top_orgs_id_map[$v];
        }
        $belong_top_orgs_id = array_unique($belong_top_orgs_id);

        // 管理的组织架构
        $managers   = $this->getBussinessOrgManagers();
        $orgs       = $this->getBussinessOrgArray();
        $is_manager = in_array($username, $managers);

        // $manage_orgs = []; // 用户管理的组织架构
        $manage_org_ids  = []; // 用户管理的组织架构的ID,包含下面的子ID
        $manage_users    = []; // 用户管理的用户
        $manage_accounts = []; // 用户管理的账号
        if ($is_manager) {
            foreach ($orgs as $value) {
                if ($value['manage'] == $username) {
                    $manage_org_ids  = array_merge($value['full_child_ids'], $manage_org_ids);
                    $manage_users    = array_merge(array_column($value['org_full_user_list'], 'user_name'), $manage_users);
                    $manage_accounts = array_merge($value['org_full_user_account_list'], $manage_accounts);
                    // $manage_orgs[] = $value;
                }
            }
        } else {
            $manage_users = [$username]; // 将自己加进去
            foreach ($belong_org_ids as $org_id) {
                $_tmp_key = $current_user_info['id'] . '_' . $org_id;
                if (in_array($org_id, $all_ebay_org_ids)) {
                    $manage_accounts = array_merge($all_org_ebay[$_tmp_key], $manage_accounts);
                }
                if (isset($all_org_props[$_tmp_key])) {
                    $manage_accounts = array_merge($all_org_props[$_tmp_key], $manage_accounts);
                }
            }
        }

        // 获取可操作的平台
        $manage_accounts      = array_unique($manage_accounts);
        $account_platform_map = ToolsLib::getInstance()->getAllAccounts(4);

        $manage_platforms = [];
        foreach ($manage_accounts as $v) {
            if (isset($account_platform_map[trim($v)])) $manage_platforms[] = $account_platform_map[trim($v)];
        }
        sort($manage_platforms);

        $data = [
            // 'manage_orgs' => $manage_orgs, 
            'is_manager'         => $is_manager,
            // 是否顶级的业务部管理者
            'is_top_manager'     => count(array_intersect($manage_org_ids, $top_bussiness_org_ids)) > 0,
            'manage_org_ids'     => array_unique($manage_org_ids),
            'manage_users'       => array_unique($manage_users),
            'manage_accounts'    => $manage_accounts,
            'manage_platforms'   => array_unique($manage_platforms),
            'belong_org_ids'     => $belong_org_ids,
            'belong_top_orgs_id' => $belong_top_orgs_id,
            'current_user_info'  => $current_user_info,
        ];

        ToolsLib::getInstance()->getRedis()->set($redis_key, $data, 10 * 60);
        return $data;
    }


    /**
     * 获取业务部 所有 顶级的组织架构的ID
     * @author lamkakyun
     * @date 2019-01-10 18:27:43
     * @return void
     */
    public function getTopBussinessOrgIds()
    {
        $orgs   = $this->getBussinessOrgTree();
        $org_id = 19;// 业务部ID

        $ret_data = [];
        foreach ($orgs[$org_id]['children'] as $v) {
            $ret_data[] = $v['id'];
        }
        return $ret_data;
    }


    /**
     * 根据 管理 信息， 获取顶级的业务部组织架构
     * @author lamkakyun
     * @date 2019-01-16 11:29:14
     * @return void
     */
    public function getTopBussinessOrgs($erp_manage_info = false)
    {
        $orgs   = $this->getBussinessOrgTree();
        $org_id = 19;// 业务部ID

        $belong_top_orgs_id = $erp_manage_info ? $erp_manage_info['belong_top_orgs_id'] : false;

        $tmp  = $orgs[$org_id]['children'];
        $orgs = [];
        foreach ($tmp as $k => $v) {
            unset($tmp[$k]['children']);
            unset($tmp[$k]['org_full_user_list']);
            unset($tmp[$k]['org_full_user_account_list']);

            if ($belong_top_orgs_id) {
                if (in_array($v['id'], $belong_top_orgs_id)) $orgs[$k] = $tmp[$k];
            } else {
                $orgs[$k] = $tmp[$k];
            }
        }

        return $orgs;
    }


    /**
     * 获取业务部 架构id => 顶级组织架构id 的映射
     * @author lamkakyun
     * @date 2019-01-16 10:41:52
     * @return void
     */
    public function getTopBussinessOrgIdsMap()
    {
        $orgs   = $this->getBussinessOrgTree();
        $org_id = 19;// 业务部ID

        $orgs = $orgs[$org_id]['children'];

        $map = [];
        foreach ($orgs as $value) {
            foreach ($value['full_child_ids'] as $v) {
                $map[$v] = $value['id'];
            }
        }

        return $map;
    }


    /**
     * 获取所有组织架构的全名 (只获取业务部)
     * @author: Lamkakyun
     * @date: 2019-03-22 09:34:00
     */
    public function getAllOrgParentNameMap()
    {
        $tree          = $this->getBussinessOrgTree();
        $full_name_map = [];
        $parent_name   = '';

        $this->_getOrgFullNameMapRecursive($full_name_map, $parent_name, $tree);
        return $full_name_map;
    }

    /**
     * 获取所有组织架构的全名 (只获取业务部)（递归）
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-11-05 11:52:51
     */
    private function _getOrgFullNameMapRecursive(&$parent_name_map, $parent_name, $tree)
    {
        foreach ($tree as $value) {
            $next_parent_name                = $parent_name . $value['name'] . '>';
            $parent_name_map[$value['name']] = $parent_name ? $parent_name : '';
            if (isset($value['children']) && !empty($value['children'])) {
                $sub_tree = $value['children'];
                $this->_getOrgFullNameMapRecursive($parent_name_map, $next_parent_name, $sub_tree);
            }
        }
    }
}