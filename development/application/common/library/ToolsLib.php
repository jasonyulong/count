<?php

namespace app\common\library;

use think\Config;
use think\cache\driver\Redis;
use app\common\model\ErpModelFactory;
use app\count\model\OrderTargetSeller;
use app\count\model\OrderTargetAccount;

class ToolsLib
{

    private static $instance = null;

    /**
     * single
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-09-12 12:02:35
     */
    public static function getInstance(): ToolsLib
    {
        if (!static::$instance) {
            static::$instance = new ToolsLib();
        }
        return static::$instance;
    }


    /**
     * 获取所有平台
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-17 01:43:47
     */
    public function getPlatformList()
    {
        return Config::get('site.platforms');
    }

    /**
     * 获取所有订单类型
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-11-05 05:11:38
     */
    public function getOrderTypeList()
    {
        return Config::get('site.ordertype');
    }


    private static $redisInstance = null;

    /**
     * 获取redis 实例
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-09-12 07:38:27
     */
    public function getRedis()
    {
        if (!static::$redisInstance) static::$redisInstance = new Redis(Config::get('redis'));
        return static::$redisInstance;
    }


    /**
     * 获取所有账号信息
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-09-12 08:26:38
     */
    public function getAllAccounts($format_type = 0)
    {
        $key = config('redis.accounts_list');

        $data = ToolsLib::getInstance()->getRedis()->get($key);

        switch ($format_type) {
            case '1':  // 将id 放在 key 的位置上
                $tmp  = $data;
                $data = [];
                foreach ($tmp as $value) {
                    $data[$value['id']] = $value;
                }
                break;
            case '2': // 将ebay_account 放到 key 的位置上
                $tmp  = $data;
                $data = [];
                foreach ($tmp as $value) {
                    $data[$value['ebay_account']] = $value;
                }
                break;
            case '3': // 以平台为分组
                $tmp  = $data;
                $data = [];
                foreach ($tmp as $value) {
                    $data[$value['platform']][] = $value['ebay_account'];
                }
                break;
            case '4': // 建立一个 ebay_account -> platform 的映射, (因为数据表中就是一对一的，所以没问题)
                $tmp  = $data;
                $data = [];
                foreach ($tmp as $value) {
                    $data[trim($value['ebay_account'])] = trim($value['platform']);
                }
                break;
        }

        return $data;
    }


    /**
     * 获取所有平台 (登录用户能看到的所有平台)
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-09-13 09:36:47
     */
    public function getAllPlatforms($username = '')
    {
        if ($username) return $this->getCanViewPlatform($username);
        $accounts = $this->getAllAccounts();
        if (!$accounts) return [];
        $platform_list = array_unique(array_column($accounts, 'platform'));
        sort($platform_list);

        return $platform_list;
    }


    /**
     * 获取所有ERP用户信息
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-09-13 09:48:50
     */
    public function getAllUsers($format_type = 0)
    {
        $key  = config('redis.user_list');
        $data = ToolsLib::getInstance()->getRedis()->get($key);

        switch ($format_type)
        {
            case '1': // 将id 放在 key 的位置上
                $tmp  = $data;
                $data = [];
                foreach ($tmp as $value) 
                {
                    $data[$value['id']] = $value;
                }
                break;
            case '2': // 将username 放在key 的位置上面
                $tmp  = $data;
                $data = [];
                foreach ($tmp as $value) 
                {
                    $data[$value['username']] = $value;
                }
                break;
            case '3': // [username - userid] map
                $tmp  = $data;
                $data = [];
                foreach ($tmp as $value) 
                {
                    $data[$value['username']] = $value['id'];
                }
                break;
            case '4': // [id - username] map
                $tmp = $data;
                foreach ($tmp as $value)
                {
                    $data[$value['id']] = $value['username'];
                }
                break;
        }

        return $data;
    }


    public function getAllDevelopers()
    {
        $data = $this->getRedis()->get(Config::get('redis.developer_list'));
        if (!$data) $data = $this->_getSpecificUsers(['产品开发']);

        foreach ($data as $key => $value) {
            unset($data[$key]['ebayaccounts']);
        }

        $this->getRedis()->set(Config::get('redis.developer_list'), $data, 24 * 60 * 60);
        return $data;
    }

    /**
     * 获取指定的用户
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-09-13 09:52:38
     */
    private function _getSpecificUsers($matches = [])
    {
        $all_user = $this->getAllUsers();
        if (!$matches) return $all_user;

        $ret = [];
        foreach ($all_user as $user)
            if (preg_match("/" . implode('|', $matches) . "/", $user['truename']) && $user['is_del'] == 0)
                $ret[] = $user;
        return $ret;
    }

    /**
     * 获取所有 销售市场相关人员
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-09-13 09:52:45
     */
    public function getAllSaleUsers($format_type = 0)
    {
        // todo: 修改为 org_user 表 下面的 所有username
        $org_user_list = ToolsLib::getInstance()->getAllOrgUser();
        $seller        = [];
        foreach ($org_user_list as $value) {
            //if ($value['status'] == 1) $seller[] = $value['user_name'];
            $seller[] = $value['user_name'];
        }

        return $seller;
    }

    /**
     * @desc   转美元
     * @author mina
     * @param  float $money 金额
     * @param  string $currency 原币种
     * @return float
     */
    public function toDollar($money, $currency)
    {
        $rate = $this->getRedis()->get(Config::get('redis.rate'));
        if (!isset($rate[$currency])) return $money;
        return number_format(($rate[$currency] * $money), 3);
    }


    /**
     * 导出excel 文件 (从ERP 系统复制过来的代码) （PHPEXCEL 官方说 自己过时了，要采用 phpspreads，但这个方法依然使用 PHPExcel）
     * @author: Lamkakyun
     * @date: 2018-06-12 08:43:05
     * @param array $headers
     * @param array $export_data
     * @param boolean $is_seq 是否打印序号
     * @desc 使用详情参考  application\count\controller\order\index.php  的 _index_export 方法
     */
    public function exportExcel($filename, $headers, $export_data, $is_seq = true)
    {
        if ($is_seq) {
            array_unshift($headers, '序号');
        }

        $header_keys   = array_keys($headers);
        $header_values = array_values($headers);

        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
            ->setLastModifiedBy("Maarten Balliauw")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("FILE");

        $title            = $filename;
        $export_file_name = $title . ".xls";

        $header_index = [];
        $char_list    = range('A', 'Z');

        // 建立足够多的 column index, (导出太长就被截断了)
        $char_more = ['A', 'B', 'C', 'D', 'E', 'F'];
        foreach ($char_more as $char) {
            $tmp_list = range('A', 'Z');
            foreach ($tmp_list as $value) {
                $char_list[] = $char . $value;
            }
        }
        foreach ($char_list as $value) {
            if (count($header_index) >= count($header_values)) break;
            $header_index[] = $value;
        }

        foreach ($header_index as $key => $value) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($value . '1', $header_values[$key]);
        }

        $i = 2;
        foreach ($export_data as $key => $value) {
            $tmp_keys         = $header_keys;
            $tmp_header_index = $header_index;
            if ($is_seq) {
                array_shift($tmp_keys);
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue(array_shift($tmp_header_index) . $i, $key + 1);
            }
            foreach ($tmp_header_index as $v) {
                $_key = array_shift($tmp_keys);
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($v . $i, $value[$_key]);
            }
            $i++;
        }

        $objPHPExcel->getActiveSheet()->setTitle($title);
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename={$export_file_name}");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }


    public function exportSkuData($datas, $date, $date2, $is_seq = true)
    {
        $export_data = $datas;
        $data        = [];
        //组装头
        foreach ($datas as $key => $value) {

            $tmp_data = $value['qtyData'];
            unset($datas[$key]['qtyData']);
            unset($datas[$key]['id']);
            unset($datas[$key]['qtySum']);
            $data[$key]['SKU'] = $value['sku'];
            $data[$key]['图片']  = $value['thumb'];
            $data[$key]['标题']  = $value['name'];
            $data[$key]['合计']  = $value['qtySum'];
            $data[$key]        = array_merge($data[$key], $tmp_data);


        }
        if (!$data && !$data[0]) return false;

        $tmp = array_keys($data[0]);

        $hearders = [];

        foreach ($tmp as $value) {
            $hearders[$value] = $value;
        }

        if ($is_seq) {
            array_unshift($hearders, '序号');
        }

        $header_keys   = array_keys($hearders);
        $header_values = array_values($hearders);

        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
            ->setLastModifiedBy("Maarten Balliauw")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("FILE");

        $title            = 'SKU销量导出-' . date('Y-m-d');
        $export_file_name = $title . ".xls";
        $header_index     = [];
        $char_list        = range('A', 'Z');

        // 建立足够多的 column index
        $char_more = ['A', 'B', 'C'];
        foreach ($char_more as $char) {
            $tmp_list = range('A', 'Z');
            foreach ($tmp_list as $value) {
                $char_list[] = $char . $value;
            }
        }

        foreach ($char_list as $value) {
            if (count($header_index) >= count($header_values)) break;
            $header_index[] = $value;
        }

        foreach ($header_index as $key => $value) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($value . '1', $header_values[$key]);
        }

        //将标题跟值保持一致
        $char_lists = range('F', 'Z');

        // 建立足够多的 column index
        $char_mores = ['A', 'B', 'C'];
        foreach ($char_mores as $chars) {
            $tmp_lists = range('A', 'Z');
            foreach ($tmp_lists as $values) {
                $char_lists[] = $chars . $values;
            }
        }

        $export_datas = [];
        foreach ($export_data as $k1 => $v1) {
            $qtyData = [];
            foreach ($v1['qtyData'] as $k2 => $v2) {
                $qtyData[] = $v2;
            }
            $export_datas[$k1]['id']      = $v1['id'];
            $export_datas[$k1]['sku']     = $v1['sku'];
            $export_datas[$k1]['thumb']   = $v1['thumb'];
            $export_datas[$k1]['name']    = $v1['name'];
            $export_datas[$k1]['qtySum']  = $v1['qtySum'];
            $export_datas[$k1]['qtyData'] = $qtyData;
        }
        //往excel中写入数据
        $c = 2;
        foreach ($export_datas as $id => $list) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A' . $c, $id + 1);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B' . $c, $list['sku']);

            //            if(!empty($list['thumb'])){
            //                $objDrawing[$c] = new \PHPExcel_Worksheet_Drawing();
            //                $objDrawing[$c]->setPath($list['thumb']);
            //                // 设置宽度高度
            //                $objDrawing[$c]->setHeight(50);//照片高度
            //                $objDrawing[$c]->setWidth(50); //照片宽度
            //                /*设置图片要插入的单元格*/
            //                $objDrawing[$c]->setCoordinates('C'.$c);
            //                // 图片偏移距离
            ////                $objDrawing[$c]->setOffsetX(12);
            ////                $objDrawing[$c]->setOffsetY(12);
            //                $objDrawing[$c]->setWorksheet($objPHPExcel->getActiveSheet());
            //            } else {
            //                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$c , '');
            //            }
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C' . $c, $list['thumb']);
            $objPHPExcel->setActiveSheetIndex(0)->getRowDimension($c)->setRowHeight(80);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D' . $c, $list['name']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E' . $c, $list['qtySum']);
            foreach ($list['qtyData'] as $ids => $qty) {
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($char_lists[$ids] . $c, $qty);
            }
            $c = $c + 1;
        }


        $objPHPExcel->getActiveSheet()->setTitle($title);
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename={$export_file_name}");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');

    }


    /**
     * 获取所有orgs
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-08 12:03:18
     */
    public function getAllOrg($format_type = 0)
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

        // 将id 放在 key 的位置上
        if ($format_type == 1) {
            $tmp  = $data;
            $data = [];
            foreach ($tmp as $value) $data[$value['id']] = $value;
        }

        // 只获取业务部的组织架构
        if ($format_type == 2) {
            $tmp  = $data;
            $data = [];
            foreach ($tmp as $value) {
                if ($value['tid'] == 19)
                    $data[$value['id']] = $value;
            }
        }

        return $data;
    }


    /**
     * 获取所有组织架构的全名 (只获取业务部)
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-11-05 11:39:49
     */
    public function getAllOrgParentNameMap($force_update = false)
    {
        $key      = config('redis.org_full_name_map');
        $ret_data = ToolsLib::getInstance()->getRedis()->get($key);
        if ($ret_data && !$force_update) return $ret_data;

        $tree          = $this->getBusinessOrgTree();
        $full_name_map = [];
        $parent_name   = '';

        $this->_getOrgFullNameMapRecursive($full_name_map, $parent_name, $tree);

        // 7 days
        ToolsLib::getInstance()->getRedis()->set($key, $full_name_map, 3 * 60 * 60);

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
            // $next_parent_name = $parent_name == '' ? $value['name'] : $parent_name . ' > ';
            $next_parent_name                = $parent_name . $value['name'] . '>';
            $parent_name_map[$value['name']] = $parent_name ?? '';
            if (isset($value['children']) && !empty($value['children'])) {
                $sub_tree = $value['children'];
                $this->_getOrgFullNameMapRecursive($parent_name_map, $next_parent_name, $sub_tree);
            }
        }
    }


    /**
     * 获取一级 组织 (组织架构改了...) (这个方法失去了最初的意思，用来 获取，业务部门的 下级 组织架构 )
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-08 01:57:55
     */
    public function getLevel1Orgs($username = '')
    {
        if (!$username) {
            $data = ToolsLib::getInstance()->getRedis()->get(config('redis.org_level1_list'));
            if ($data) return $data;
        }

        $data     = $this->getAllOrg(2);
        $ret_data = [];

        $org_list = [];
        if ($username) $org_list = $this->getUserLevel1Org($username);

        foreach ($data as $key => $value) {
            // todo: 去除FBA这个部门
            if ($value['id'] == '20') continue;

            if ($value['parent_id'] == 19 && $value['ishidden'] == 0 && $value['org_status'] == 1) {
                // 如果用户属于 某个 业务部的组织架构，那么，就进行限制，只能查看自己所属 架构 的销售情况
                if ($org_list && !getRolePower()) {
                    if (in_array($value['name'], $org_list)) $ret_data[$value['id']] = $value;
                } else {
                    $ret_data[$value['id']] = $value;
                }
            }
        }

        if (!$username && $ret_data) ToolsLib::getInstance()->getRedis()->set(config('redis.org_level1_list'), $ret_data, 24 * 60 * 60);

        return $ret_data;
    }


    /**
     * 获取组织架构用户
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-08 02:34:39
     */
    public function getAllOrgUser($format_type = 0)
    {
        $key  = config('redis.org_user_list');
        $data = ToolsLib::getInstance()->getRedis()->get($key);

        // 将id 放在 key 的位置上
        if ($format_type == 1) {
            $tmp  = $data;
            $data = [];
            foreach ($tmp as $value) {
                if ($value['status'] == 0) continue;
                $data[$value['id']] = $value;
            }
        }
        // 将 organize_id 放到 key 上(org 对 user 是 1 对 1， user 对 org 是 多 对 1，所以 org_id 是唯一的)
        if ($format_type == 2) {
            $tmp  = $data;
            $data = [];
            foreach ($tmp as $value) {
                if ($value['status'] == 0) continue;
                $data[$value['organize_id']][] = $value;
            }
        }

        return $data;
    }


    /**
     * 获取一级 组织 下面的销售人员(组织架构改了，这个也要跟着改)
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-29 09:52:27
     */
    public function getLevel1OrgSaleUser($org_id_arr)
    {
        $org_list    = $this->getAllOrg(2);
        $seller_list = [];
        $sub_org_ids = [];
        foreach ($org_id_arr as $org_id) {
            if (isset($org_list[$org_id])) {
                $tmp_org = $org_list[$org_id];
                foreach ($org_list as $value) {
                    if ($value['lid'] >= $tmp_org['lid'] && $value['rid'] <= $tmp_org['rid']) $sub_org_ids[] = $value['id'];
                }
            }
        }

        if ($sub_org_ids) {
            $org_user_list = $this->getAllOrgUser();
            foreach ($org_user_list as $key => $value) {
                if (in_array($value['organize_id'], $sub_org_ids)) $seller_list[] = $value;
            }
        }

        return $seller_list;
    }


    /**
     * 获取组织架构的 id 以及它下面的 子 的 id
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-11-05 02:19:43
     */
    public function getSubOrgIds($org_id)
    {
        $all_org = $this->getAllOrg(1);
        if (!isset($all_org[$org_id])) return [$org_id];
        $this_org = $all_org[$org_id];

        $ret_data = [];
        foreach ($all_org as $key => $value) {
            if ($value['lid'] >= $this_org['lid'] && $value['rid'] <= $this_org['rid']) $ret_data[] = $key;
        }

        return $ret_data;
    }


    /**
     * 获取第一级 组织 对应的销售
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-08 03:51:41
     */
    public function getLevel1SellersMap($force_update = false)
    {
        $data = ToolsLib::getInstance()->getRedis()->get(config('redis.org_level1_seller_map'));
        if ($data && !$force_update) return $data;

        $orgs = $this->getLevel1Orgs();

        $ret_data = [];
        foreach ($orgs as $org) {
            $_users = $this->getLevel1OrgSaleUser([$org['id']]);

            $ret_data[$org['name']] = array_column($_users, 'user_name');
        }

        $ret_data = array_filter($ret_data, function ($val) {
            return !empty($val);
        });

        if ($ret_data) ToolsLib::getInstance()->getRedis()->set(config('redis.org_level1_seller_map'), $ret_data, 24 * 60 * 60);
        return $ret_data;
    }


    /**
     * 获取用户 第一级的组织架构 (一个用户可以属于多个组织架构，如果部署任何组织架构，就是管理员,IT部分)
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-10 04:15:39
     */
    public function getUserLevel1Org($username)
    {
        $map = $this->getLevel1SellersMap();

        $org_arr = [];
        foreach ($map as $key => $value) {
            foreach ($value as $v) {
                if ($v == $username) $org_arr[] = $key;
            }
        }

        return $org_arr;
    }

    /**
     * 根据组织id，获取组织
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-11 04:28:30
     */
    public function getOrgById($org_id_arr)
    {
        $orgs     = $this->getLevel1Orgs();
        $ret_data = [];
        foreach ($org_id_arr as $org_id) {
            if (!empty($orgs[$org_id])) $ret_data[] = $orgs[$org_id];
        }

        return $ret_data;
    }


    /**
     * 根据组织架构，获取销售人员
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-10 04:50:19
     */
    public function getSellerByOrg($org_arr)
    {
        $map    = $this->getLevel1SellersMap();
        $seller = [];
        foreach ($map as $key => $value) {
            if (in_array($key, $org_arr)) $seller = array_merge($seller, $value);
        }

        // 去重，因为 一个销售员可以 在多个组织架构，导致了重复
        $seller = array_unique($seller);

        return $seller;
    }


    /**
     * 获取用户能够 查看的账号
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-10 06:07:00
     */
    public function getCanViewAccounts($username)
    {
        $all_users = $this->getAllUsers(2);
        $user_info = $all_users[$username];

        return explode(',', $user_info['ebayaccounts']);
    }


    /**
     * 获取用户能够查看的平台
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-10 06:07:24
     */
    public function getCanViewPlatform($username)
    {
        $all_accounts      = $this->getAllAccounts(2);
        $can_view_accounts = $this->getCanViewAccounts($username);

        $platform_list = [];
        foreach ($can_view_accounts as $value) {
            if (!empty($all_accounts[$value])) $platform_list[] = $all_accounts[$value]['platform'];
        }

        $platforms = array_unique($platform_list);
        sort($platforms);
        return $platforms;
    }


    /**
     * 获取组织架构的树 (废弃， 组织架构 改了，需要无限分级)
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-24 10:28:00
     */
    public function getBusinessOrgTree_delete($force_update = false)
    {
        $data = ToolsLib::getInstance()->getRedis()->get(config('redis.org_tree'));
        if ($data && !$force_update) return $data;

        $org_list = $this->getAllOrg(2);

        /*$l1_tree = */
        $l2_tree = $l3_tree = [];
        foreach ($org_list as $value) {
            if ($value['org_status'] != 1 || $value['ishidden'] == 1) continue;

            $value['user_id'] = $value['manage_uid'];
            $value['user']    = $value['manage'];

            if ($value['level'] == 2) $l2_tree[] = $value;
            if ($value['level'] == 3) $l3_tree[$value['parent_id']][] = $value;
        }

        $tmp_l2_tree = [];
        foreach ($l2_tree as $key => $value) {
            if (isset($l3_tree[$value['id']])) {
                $l2_tree[$key]['sub_org'] = $l3_tree[$value['id']];
            }
            $tmp_l2_tree[$value['parent_id']][] = $l2_tree[$key];
        }

        ToolsLib::getInstance()->getRedis()->set(config('redis.org_tree'), $l2_tree ?? [], 24 * 60 * 60);
        return $l2_tree;
    }


    /**
     * 无限分级组织架构的树 （只获取业务部的组织架构）
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-26 05:50:55
     */
    public function getBusinessOrgTree($force_update = false)
    {
        $data = ToolsLib::getInstance()->getRedis()->get(config('redis.org_tree'));
        if ($data && !$force_update) return $data;

        $org_list = $this->getAllOrg(2);
        unset($org_list[19]);

        foreach ($org_list as $key => $value) {
            if ($value['org_status'] != 1 || $value['ishidden'] == 1) continue;
            $org_list[$key]['seller_list'] = $this->_getOrgSellers($value['lid'], $value['rid']);
            $org_list[$key]['children']    = $this->_subOrgRecursive($value, $org_list);
        }

        // 删除子元素（嗯，简单粗暴）
        foreach ($org_list as $key => $value) {
            if ($value['level'] > 2) unset($org_list[$key]);
        }
        return $org_list;
    }


    /**
     * 递归求，组织架构的树
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-26 06:30:19
     */
    private function _subOrgRecursive($org_info, $org_list)
    {
        $parent_id = $org_info['id'];
        $sub_org   = [];
        foreach ($org_list as $value) {
            if ($value['parent_id'] == $parent_id) {
                $value['seller_list'] = $this->_getOrgSellers($value['lid'], $value['rid']);
                $value['children']    = $this->_subOrgRecursive($value, $org_list);
                $sub_org[]            = $value;
            }
        }

        return $sub_org;
    }


    /**
     * 获取组织架构的销售人员
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-27 10:20:40
     */
    private function _getOrgSellers($lid, $rid)
    {
        $org_list = $this->getAllOrg(2);

        $sub_org_ids = [];
        foreach ($org_list as $value) {
            if ($value['lid'] >= $lid && $value['rid'] <= $rid) $sub_org_ids[] = $value['id'];
        }

        $org_user_list = $this->getAllOrgUser();

        $ret_data = [];
        foreach ($org_user_list as $value) {
            if (in_array($value['organize_id'], $sub_org_ids)) $ret_data[] = trim($value['user_name']) . "___{$value['organize_id']}";
        }
        return $ret_data;
    }

    /**
     * (递归)将树，转换成 一维数组
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-27 10:21:10
     */
    public function treeToArray($tree, $top_id = 0)
    {
        $ret_data = [];
        foreach ($tree as $key => $value) {
            $_children = [];
            if (isset($value['children'])) {
                $_children = $value['children'];
            }
            unset($value['children']);

            // 用top_id 记录组织架构的最上级id（为了合计最上级的总数）
            if ($value['level'] == 2) {
                $value['top_id'] = 0;
                $top_id          = $value['id'];
            } else {
                $value['top_id'] = $top_id;
            }

            $ret_data[] = $value;

            // 递归 继续/终止 条件
            if ($_children) {
                $_ret     = $this->treeToArray($_children, $top_id);
                $ret_data = array_merge($ret_data, $_ret);
            }
        }

        return $ret_data;
    }


    /**
     * 下载文件
     * @author lamkakyun
     * @date 2018-12-13 14:17:19
     * @param $file 文件名（绝对路径）
     * @return void
     */
    public function downloadFile($file)
    {
        if (!file_exists($file)) abort(401, '文件不存在');
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));

        set_time_limit(0);
        $fhandle = @fopen($file, "rb");
        while (!feof($fhandle)) {
            print(@fread($fhandle, 1024 * 8));
            ob_flush();
            flush();
        }
        exit(0);
    }

    /**
     * 获取导入的excel 的数据
     * @author: Lamkakyun
     * @date: 2019-03-04 11:30:10
     */
    public function getImportExcelData($is_remove_empty_line = false)
    {
        vendor('PHPExcel.PHPExcel');

        $file = $_FILES['file'];
        if ($file['error']) return ['success' => false, 'msg' => '上传文件失败'];

        $excel      = \PHPExcel_IOFactory::load($file['tmp_name']);
        $sheet      = $excel->getSheet(0);
        $excel_data = $sheet->toArray();

        // 去掉空字符
        foreach ($excel_data as $key => $value)
        {
            $excel_data[$key] = array_map('trim', $value); 
        }

        $fileName   = $_FILES['file']['name'];

        // 删除空行
        if ($is_remove_empty_line) {
            $tmp = $excel_data;
            foreach ($tmp as $key => $value) {
                $is_empty_line = true;
                foreach ($value as $v) {
                    if (!empty($v)) $is_empty_line = false;
                }

                if ($is_empty_line) unset($excel_data[$key]);
            }
        }

        return ['success' => true, 'msg' => 'bingo', 'data' => $excel_data, 'extra' => ['filename' => $_FILES['file']['name']], 'fileName' => $fileName];
    }


    /**
     * 获取 仓库数据
     * @author lamkakyun
     * @date 2018-12-13 18:42:32
     * @return array
     */
    public function getStoreCache($format_type = 0)
    {
        $data = $this->getRedis()->get(Config::get('redis.store'));
        switch ($format_type)
        {
            case '1': // id - name 映射
                $tmp = $data;
                $data = [];
                foreach ($tmp as $key => $value)
                {
                    $data[$value['id']] = $value['store_name'];
                }
                break;
        }
        return $data;
    }


    /**
     * 获取 SKU 分类信息
     * @author lamkakyun
     * @date 2019-02-23 14:28:43
     * @return void
     */
    public function getGoodsCategory($format_type = 0)
    {
        $data = $this->getRedis()->get(Config::get('redis.goods_category'));

        switch ($format_type) {
            // 合并成tree结构
            case '1':
                $tmp  = $data;
                $data = [];
                foreach ($tmp as $k => $v) {
                    if ($v['pid'] == '0') {
                        $data[$k]            = $v;
                        $data[$k]['sub_cat'] = [];
                    }
                }

                foreach ($tmp as $k => $v) {
                    if ($v['pid'] != '0') {
                        if (isset($data[$v['pid']])) $data[$v['pid']]['sub_cat'][$k] = $v;
                    }
                }

                break;

        }

        return $data;
    }


    /**
     * 获取redis 同步的 国家信息
     * @author lamkakyun
     * @date 2019-02-25 13:50:13
     * @return void
     */
    public function getAllCountries()
    {
        $data = $this->getRedis()->get(Config::get('redis.countries'));
        return $data;
    }


    /**
     * 获取销售员 销售目标 (当月 + 前 3个月，共4个月)
     * @author lamkakyun
     * @date 2019-03-05 15:41:44
     * @return void
     */
    public function getSellersTarget($org_sellers)
    {
        $model  = new OrderTargetSeller();
        $months = [];
        foreach (range(0, 3) as $i) {
            $_time    = strtotime(date('Y-m') . " -{$i} month");
            $months[] = ['year' => date('Y', $_time), 'month' => date('m', $_time)];
        }

        $where_date = [];
        foreach ($months as $v) {
            $_tmp_str     = "(year = {$v['year']} AND month = {$v['month']})";
            $where_date[] = $_tmp_str;
        }
        $where_date = implode(' OR ', $where_date);

        // 如果一条条的 查，会很慢， 只能折中，按组织架构查出所有，让后，刷选
        $org_seller_map = [];
        foreach ($org_sellers as $value) {
            $_tmp_arr    = explode('___', $value);
            $_tmp_seller = $_tmp_arr[0];
            $_tmp_org_id = $_tmp_arr[1];

            $org_seller_map[$_tmp_org_id][] = $_tmp_seller;
        }

        $target_list = [];
        foreach ($org_seller_map as $org_id => $sellers) {
            $_where = ['org_id' => $org_id, 'seller' => ['IN', $sellers]];
            $_data  = $model->where($_where)->where($where_date)->select()->toArray();

            foreach ($_data as $v) {
                $_tmp_key               = $v['seller'] . '___' . $v['org_id'] . '___' . $v['year'] . '-' . str_pad($v['month'], 2, '0', STR_PAD_LEFT);
                $target_list[$_tmp_key] = $v['target_value'];
            }
        }

        return $target_list;
    }


    /**
     * 获取账号 销售目标 (当月 + 前 3个月，共4个月)
     * @author lamkakyun
     * @date 2019-03-05 15:41:44
     * @return void
     */
    public function getAccountsTarget($platform_accounts)
    {
        $model  = new OrderTargetAccount();
        $months = [];
        foreach (range(0, 3) as $i) {
            $_time    = strtotime(date('Y-m') . " -{$i} month");
            $months[] = ['year' => date('Y', $_time), 'month' => date('m', $_time)];
        }

        $where_date = [];
        foreach ($months as $v) {
            $_tmp_str     = "(year = {$v['year']} AND month = {$v['month']})";
            $where_date[] = $_tmp_str;
        }
        $where_date = implode(' OR ', $where_date);

        $where = ['platform_account' => ['IN', $platform_accounts]];
        $data  = $model->where($where)->where($where_date)->select()->toArray();

        $target_list = [];
        foreach ($data as $v) {
            $_tmp_key               = $v['platform_account'] . '___' . $v['year'] . '-' . str_pad($v['month'], 2, '0', STR_PAD_LEFT);
            $target_list[$_tmp_key] = $v['target_value'];
        }

        return $target_list;
    }
}