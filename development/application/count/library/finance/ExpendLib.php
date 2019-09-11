<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    yang
 */

namespace app\count\library\finance;

use app\count\model\Expend;
use app\count\library\OrgLib;
use app\common\library\FMSAuth;
use app\count\model\ExpendType;
use app\common\library\ToolsLib;
use app\count\model\ExpendDetail;
use app\count\model\ExpendImport;
use app\count\library\order\OrderLib;

/**
 * Class ExpendLib
 * @package app\count\library\finance
 */
class ExpendLib
{
    private static $instance = null;


    public function __construct()
    {
        $this->expend_model = new Expend();
        $this->expend_type_model = new ExpendType();
        $this->expend_import_model = new ExpendImport();
    }

    /**
     * single
     * @AUTHOR: Lamkakyun
     * @DATE: 2019-03-29 14:16:32
     */
    public static function getInstance(): ExpendLib
    {
        if (!static::$instance) {
            static::$instance = new ExpendLib();
        }
        return static::$instance;
    }


    /**
     * 获取消费列表
     * @author lamkakyun
     * @date 2019-04-02 09:33:49
     * @return void
     */
    public function getExpendList($params)
    {
        $where = OrderLib::getInstance()->_handleQueryDate($params, []);
        if (!empty($_SESSION['truename'])) {
            $platform = ToolsLib::getInstance()->getCanViewPlatform($_SESSION['truename']);
            if ($platform) $where['platform'] = ['IN', $platform];
        }

        if (isset($params['platform']) && !empty($params['platform'])) $where['platform'] = ['IN', $params['platform']];
        if (isset($params['account']) && !empty($params['account'])) $where['platform_account'] = ['IN', $params['account']];

        if ($params['checkDate'] == 'day') $range = range_day($params['scantime_end'], $params['scantime_start'], true);
        else $range = range_month($params['scandate_end'], $params['scandate_start']);

        if (isset($params['organ']) && !empty($params['organ'])) {
            $all_sub_org_ids = ToolsLib::getInstance()->getSubOrgIds($params['organ'][0]);
            if ($all_sub_org_ids) $where['org_id'] = ['IN', $all_sub_org_ids];
        }

        $all_users = ToolsLib::getInstance()->getAllUsers(3);

        if (isset($params['seller']) && !empty($params['seller'])) $where['seller_user_id'] = ['IN', array_map(function($v) use ($all_usres) {return $all_users[$v];}, $params['seller'])];

        switch ($params['type'])
        {
            case 'platform':
                $_group_by = 'platform,days, month, year'; // 4 个字段的唯一性
                if ($params['checkDate'] == 'month') $_group_by = 'platform, month, year';
                $_fields = "{$_group_by}, SUM(expend_amount) as sum_expend_amount";

                $ret_data = $this->_getGroupByCountAndList($this->expend_model, $where, $_group_by, $_fields);
              
                // 重组数据 (就算没有数据 也要默认给 空数组)
                $ret_data_reshape = [];
                foreach ($ret_data['list'] as $value) {
                    foreach ($range as $v) {
                        $ret_data_reshape[$value['platform']]['dates'][$v] = ['sum_expend_amount' => '0'];
                    }
                }
                foreach ($ret_data['list'] as $value) {
                    if ($params['checkDate'] == 'month') $ret_data_reshape[$value['platform']]['dates']["{$value['year']}-{$value['month']}"] = $value;
                    else $ret_data_reshape[$value['platform']]['dates']["{$value['year']}-{$value['month']}-{$value['days']}"] = $value;

                }
                $ret_data['list']  = $ret_data_reshape;
                $ret_data['count'] = count($ret_data_reshape);
               
                break;
            case 'account':
                $_group_by = 'platform_account,days, month, year'; // 4 个字段的唯一性
                if ($params['checkDate'] == 'month') $_group_by = 'platform_account, month, year';
                $_fields = "{$_group_by}, SUM(expend_amount) as sum_expend_amount";

                $ret_data = $this->_getGroupByCountAndList($this->expend_model, $where, $_group_by, $_fields);
                // 重组数据 (就算没有数据 也要默认给 空数组)
                $ret_data_reshape = [];
                foreach ($ret_data['list'] as $value) {
                    foreach ($range as $v) {
                        $ret_data_reshape[$value['platform_account']]['dates'][$v] = ['sum_expend_amount' => '0'];
                    }
                }
                foreach ($ret_data['list'] as $value) {
                    if ($params['checkDate'] == 'month') $ret_data_reshape[$value['platform_account']]['dates']["{$value['year']}-{$value['month']}"] = $value;
                    else $ret_data_reshape[$value['platform_account']]['dates']["{$value['year']}-{$value['month']}-{$value['days']}"] = $value;

                }
                $ret_data['list']  = $ret_data_reshape;
                $ret_data['count'] = count($ret_data_reshape);

                break;
            
            case 'seller':
            case 'organ':
                $all_users = ToolsLib::getInstance()->getAllUsers(4);
                $_group_by = 'seller_user_id, org_id,days, month, year'; // 4 个字段的唯一性
                if ($params['checkDate'] == 'month') $_group_by = 'seller_user_id, org_id, month, year';
                $_fields = "{$_group_by}, SUM(expend_amount) as sum_expend_amount";

                $ret_data = $this->_getGroupByCountAndList($this->expend_model, $where, $_group_by, $_fields);

               
                // 重组数据 (就算没有数据 也要默认给 空数组)
                $ret_data_reshape = [];
                foreach ($ret_data['list'] as $value) {
                    foreach ($range as $v) {
                        $tmp_key = trim($all_users[$value['seller_user_id']]) . "___{$value['org_id']}";

                        $ret_data_reshape[$tmp_key][$v] = ['sum_expend_amount' => '0'];
                    }
                }
                // echo '<pre>';var_dump($ret_data_reshape);echo '</pre>';
                // exit;

                foreach ($ret_data['list'] as $value) {
                    $tmp_key = trim($all_users[$value['seller_user_id']]) . "___{$value['org_id']}";

                    if ($params['checkDate'] == 'month') $ret_data_reshape[$tmp_key]["{$value['year']}-{$value['month']}"] = $value;
                    else $ret_data_reshape[$tmp_key]["{$value['year']}-{$value['month']}-{$value['days']}"] = $value;
                }
                $ret_data['list']  = $ret_data_reshape;

                break;
        }

        if (isset($params['debug']) && $params['debug'] == 'sql') var_dump($this->expend_model->getLastSql());

        return $ret_data;
    }


    /**
     * 获取分组 数据
     * @author lamkakyun
     * @date 2019-04-04 10:15:43
     * @return void
     */
    private function _getGroupByCountAndList($model, $where, $_group_by, $_fields)
    {
        $tmp_sql = $model->field($_fields)->where($where)->group($_group_by)->buildSql();

        $sql = "SELECT COUNT(*) as total FROM {$tmp_sql} as tmp";

        $query_rs = $model->query($sql);
        $count    = $query_rs[0]['total'] ?? 0;
        $sum_list = [];
        if ($count) {
            // 分组用limit 会造成错误
            $sum_list = $model->field($_fields)->where($where)->group($_group_by)->select()->toArray();
        }

        if (isset($_REQUEST['debug']) && $_REQUEST['debug'] == 'sql') {
            echo $model->getLastSql();
        }

        return ['list' => $sum_list, 'count' => $count];
    }


    /**
     * 平台导入字段映射
     * @author lamkakyun
     * @date 2019-04-02 15:59:01
     * @return void
     */
    public function platformExpendTypeMap()
    {
        $map = [];
        $data = $this->expend_type_model->order('id')->select();
        foreach ($data as $value)
        {
            $map[$value['platform']][$value['id']] = $value['type_name'];
        }
        // 按键值排序
        ksort($map);

        // 其他 排序在最后面
        foreach ($map as $key => $value)
        {
            $is_other = false;
            $tmp = $value;
            $tmp_k = null;
            $tmp_v = null;
            foreach ($tmp as $k => $v)
            {
                if ($v == '其他')
                {
                    $is_other = true;
                    $tmp_k = $k;
                    $tmp_v = $v;
                    unset($map[$key][$k]);
                }
            }
            if ($is_other) $map[$key][$tmp_k] = $tmp_v;
        }
        
        return $map;
    }

    /**
     * 允许上传 费用的 平台
     * @author lamkakyun
     * @date 2019-04-03 15:58:38
     * @return void
     */
    public function getAllowImportPlatforms()
    {
        $map = $this->platformExpendTypeMap();
        return array_keys($map);
    }


    /**
     * 允许上传 的文件名
     * 必须限制上传文件名，否则重复导入相同费用
     * @author lamkakyun
     * @date 2019-04-03 16:00:15
     * @return void
     */
    // public function getAllowImportFilenames()
    // {
    //     $data = $this->getAllowImportPlatforms();
    //     return array_map(function($val) {return $val . '.xlsx';}, $data);
    // }


    /**
     * 获取消费类型
     * @author lamkakyun
     * @date 2019-04-02 14:22:10
     * @return void
     */
    // public function getExpendTypeList()
    // {
    //     $tmp = $this->expend_type_model->select()->toArray();

    //     $data = [];
    //     foreach ($tmp as $key => $value)
    //     {
    //         $data[$value['id']] = $value['type_name'];
    //     }
    //     return $data;
    // }


    /**
     * 导入 平台 费用
     * @author lamkakyun
     * @date 2019-04-02 17:59:04
     * @return void
     */
    public function importPlatformExpend($params)
    {
        $ret_import = ToolsLib::getInstance()->getImportExcelData(true);
        if (!$ret_import['success']) return ['code' => -1, 'msg' => '获取上传文件失败'];

        if (!isset($params['start_time']) || empty($params['start_time']) || !isset($params['end_time']) || empty($params['end_time'])) return ['code' => -1, 'msg' => '费用均摊时间不能留空'];

        $account_userid_orgid_map = OrgLib::getInstance()->getAccountToUserIdOrgIdMap();
        $all_stores = ToolsLib::getInstance()->getStoreCache(1);
        $all_store_names = array_values($all_stores);
        $all_stores = array_flip($all_stores);
        // echo '<pre>';var_dump($all_store_names);echo '</pre>';
        // exit;

        // 检测导入的时间段是否允许
        $range = range_day($params['start_time'], $params['end_time']);
        $day_count = count($range);

        $tmp_data = $this->expend_import_model->field('start_time, end_time')->where(['platform' => $params['platform']])->select()->toArray();
        
        $import_dates = [];
        foreach ($tmp_data as $value)
        {
            $import_dates = array_merge($import_dates, range_day($value['start_time'], $value['end_time']));
        }
        $repeat_dates = array_intersect($import_dates, $range);
        if (count($repeat_dates) > 0) return ['code' => -1, 'msg' => '均摊时间重重合：' . implode(',', $repeat_dates)];

        $all_accounts = ToolsLib::getInstance()->getAllAccounts(3);
        $all_accounts = $all_accounts[$params['platform']];

        $excel_data = $ret_import['data'];
        $header = array_shift($excel_data);

        foreach ($excel_data as $key => $value)
        {
            $value[0] = preg_replace('/\(.*\)/', '', $value[0]);
            $excel_data[$key] = $value;
        }

        // 检测平台是否拥有该导入字段
        $expend_type_map = $this->platformExpendTypeMap();
        $platform_expend_types = $expend_type_map[$params['platform']];
        
        $diff = array_diff($header, $platform_expend_types);
        if (count($diff) > 0) return ['code' => -1, 'msg' => '费用类型不存在:' . implode(',', $diff)];

        $account_list = array_column($excel_data, 0);

        $diff = array_diff($account_list, $all_accounts);
        if (count($diff) > 0) return ['code' => -1, 'msg' => "账户不存在于[{$params['platform']}]:" . implode(',', array_unique($diff)), 'data' => [$account_list, $all_accounts]];

        $has_empty = false;
        foreach ($account_list as $value)
        {
            if (empty($value))
            {
                $has_empty = true;
                break;
            }
        }
        if ($has_empty) return ['code' => -1, 'msg' => '账号不能留空'];

        // 针对ebay 账号，需要特殊处理
        if ($params['platform'] == 'ebay')
        {
            $seller_list = array_column($excel_data, 1);

            $all_sellers = OrgLib::getInstance()->getAllSellers(1);
            $diff = array_diff($seller_list, $all_sellers);
            if (count($diff) > 0) return ['code' => -1, 'msg' => '业务部不存在:' . implode(',', array_unique($diff))];

            // $all_sellers = array_flip($all_sellers);

            $has_empty = false;
            foreach ($seller_list as $value)
            {
                if (empty($value))
                {
                    $has_empty = true;
                    break;
                }
            }
            if ($has_empty) return ['code' => -1, 'msg' => '业务员不能留空'];

            // TODO: 检测 账号 + 销售员唯一性
            $account_seller_list = array_map(function($v){return $v[0] . '   ' . $v[1];}, $excel_data);
            $duplicate_account_seller = find_duplicates($account_seller_list);

            if (count($duplicate_account_seller) > 0) return ['code' => -1, 'msg' => '存在重复[账号-业务员]:' . implode(',', $duplicate_account_seller)];

            // TODO: 检测账户 和 销售员是否有关联
            $org_ebay = OrgLib::getInstance()->getOrgEbay(1);
            $all_users = ToolsLib::getInstance()->getAllUsers(3);
            foreach ($seller_list as $key => $value)
            {
                $_user_id = $all_users[$value] ?? false;
                $_accounts = $org_ebay[$_user_id] ?? [];
                $_acc = $account_list[$key];
                if (!in_array($_acc, $_accounts)) return ['code' => -1, 'msg' => "账号：{$_acc}不在[{$value}]管理的账号范围", 'more' => $_accounts];
            }

            // TODO: 检测仓库 是否存在
            $store_list = array_unique(array_filter(array_column($excel_data, 3), function($v){return !empty($v);}));
            $diff = array_diff($store_list, $all_store_names);
            // if (count($diff) > 0) return ['code' => -1, 'msg' => '仓库不存在:' . implode(',', array_unique($diff))];

            // TODO: 检测 location (不做)
            // TODO: 检测 销售标签(不做)
        }
        else
        {
            // 检测账号
            $duplicate_accounts = find_duplicates($account_list);
            if (count($duplicate_accounts) > 0) return ['code' => -1, 'msg' => '存在重复[账号]:' . implode(',', $duplicate_accounts)];
        }
        
        $now_time = time();
        $add_import_data = [
            'file_name' => $ret_import['fileName'],
            'platform' => $params['platform'],
            'file_content' => json_encode($ret_import['data']),
            'start_time' => $params['start_time'],
            'end_time' => $params['end_time'],
            'create_time' => $now_time,
        ];

        // echo '<pre>';var_dump($add_import_data);echo '</pre>';
        // exit;
        
        // $expend_type_list = $this->getExpendTypeList();
        $expend_type_list_flip = array_flip($platform_expend_types);

        // echo '<pre>';var_dump($expend_type_list, $expend_type_list_flip);echo '</pre>';
        // exit;

        // 添加导入数据
        try {
            $this->expend_model->startTrans();
            
            $ret_import_id = $this->expend_import_model->insert($add_import_data);
            if (!$ret_import_id) 
            {
                $this->expend_model->rollback();
                return ['code' => 10000, 'msg' => '导入失败'];
            }

            foreach ($excel_data as $key => $row)
            {
                $tmp_platform_account = $row[0];
                
                $add_expend_data = [
                    'platform' => $params['platform'],
                    'platform_account' => $tmp_platform_account,
                    'create_time' => $now_time,
                    'import_id' => $ret_import_id,
                ];

                $all_add_expend_data = [];
                if ($params['platform'] == 'ebay')
                {
                    $tmp_seller_user_id = $all_users[$row[1]];
                    $tmp_location = $row[2];
                    $tmp_store_name = $row[3];
                    $tmp_store_id = $all_stores[$tmp_store_name];
                    $tmp_sale_lable = $row[4];

                    $tmp_org_id = OrgLib::getInstance()->getEbayOrgid($tmp_platform_account, $tmp_seller_user_id, $tmp_location, $tmp_store_id, $tmp_sale_lable);

                    if (!$tmp_org_id) return ['code' => 10005, 'msg' => '第' . ($key + 2) . "行数据异常。没有找到对应的组织架构"];

                    $add_expend_data['seller_user_id'] = $tmp_seller_user_id;
                    $add_expend_data['org_id'] = $tmp_org_id;
                    $add_expend_data['location'] = $tmp_location;
                    $add_expend_data['store_name'] = $tmp_store_name;
                    $add_expend_data['sale_lable'] = $tmp_sale_lable;

                    $tmp_header = array_slice($header, 5);
                    $tmp_arr = array_slice($row, 5);

                    foreach ($tmp_arr as $k => $v)
                    {
                        // 均摊费用
                        $_avg_expend = bcdiv($v, $day_count, 6);
                        $_type_name = $tmp_header[$k];
                        $_type_id = $expend_type_list_flip[$_type_name];
                        $_tmp_add_data = $add_expend_data;
                        $_tmp_add_data['type_id'] = $_type_id;
                        $_tmp_add_data['type_name'] = $_type_name;
                        
                        foreach ($range as $_day)
                        {
                            $_tmp_add_data['year'] = date('Y', strtotime($_day));
                            $_tmp_add_data['month'] = date('m', strtotime($_day));
                            $_tmp_add_data['days'] = date('d', strtotime($_day));
                            $_tmp_add_data['expend_amount'] = $_avg_expend;
                            $_tmp_add_data['datetime'] = strtotime($_day);

                            $all_add_expend_data[] = $_tmp_add_data;
                        }
                    }
                    
                    // echo '<pre>';var_dump($all_add_expend_data);echo '</pre>';
                    // exit;
                }
                else{
                    if (!isset($account_userid_orgid_map[$row[0]]))
                    {
                        $this->expend_model->rollback();
                        return ['code' => 10006, 'msg' => "{$row[0]}账号的组织架构找不到"];
                    }

                    $tmp_header = array_slice($header, 1);
                    $tmp_arr = array_slice($row, 1);
                    $tmp_map = $account_userid_orgid_map[$row[0]];

                    foreach ($tmp_arr as $k => $v)
                    {
                        // 均摊费用
                        $_avg_expend = bcdiv($v, $day_count, 6);
                        $_type_name = $tmp_header[$k];
                        $_type_id = $expend_type_list_flip[$_type_name];
                        $_tmp_add_data = $add_expend_data;
                        $_tmp_add_data['type_id'] = $_type_id;
                        $_tmp_add_data['type_name'] = $_type_name;
                        $_tmp_add_data['seller_user_id'] = $tmp_map['user_id'];
                        $_tmp_add_data['org_id'] = $tmp_map['org_id'];
                        
                        foreach ($range as $_day)
                        {
                            $_tmp_add_data['year'] = date('Y', strtotime($_day));
                            $_tmp_add_data['month'] = date('m', strtotime($_day));
                            $_tmp_add_data['days'] = date('d', strtotime($_day));
                            $_tmp_add_data['expend_amount'] = $_avg_expend;
                            $_tmp_add_data['datetime'] = strtotime($_day);

                            $all_add_expend_data[] = $_tmp_add_data;
                        }
                    }
                }

                // echo '<pre>';var_dump($all_add_expend_data);echo '</pre>';
                // exit;
                $ret_expend_id = $this->expend_model->insertAll($all_add_expend_data);
                if (!$ret_expend_id)
                {
                    $this->expend_model->rollback();
                    return ['code' => 10001, 'msg' => '导入失败'];
                }
            }
        } catch (\Exception $e)
        {
            $this->expend_model->rollback();

            $excption_info = $e->getMessage() . ' FILE: ' . $e->getFile() . ' line:' . $e->getLine();
            return ['code' => 10002, 'msg' => '导入失败', 'e' => $excption_info];
        }

        $this->expend_model->commit();
        return ['code' => 0, 'msg' => '导入成功'];
    }


    /**
     * 添加 消费性项目 
     * @author lamkakyun
     * @date 2019-04-08 16:40:48
     * @return void
     */
    public function addExpendType()
    {
        $params = array_map('trim', input('post.'));
        if (!isset($params['type_name']) || empty($params['type_name'])) return ['code' => -1, 'msg' => '名称不能为空'];
        if (!isset($params['platform']) || empty($params['platform'])) return ['code' => -1, 'msg' => '数据异常'];

        $where = $add_data = ['type_name' => $params['type_name'], 'platform' => $params['platform']];
        
        $count = $this->expend_type_model->where($where)->count();
        if ($count > 0) return ['code' => -1, 'msg' => '消费项已存在'];

        $ret_add = $this->expend_type_model->insert($add_data);
        if (!$ret_add) return ['code' => -1, 'msg' => '添加消费项失败'];

        // 修改 excel 模板
        $file_path = ROOT_PATH . "public/download_templates/expend/" . $params['platform'] . '.xlsx';
        if (!is_writable($file_path)) return ['code' => -1, 'msg' => '写操作异常'];

        $type_map = ExpendLib::getInstance()->platformExpendTypeMap();
        $types = array_values($type_map[$params['platform']]);

        $char_list    = range('A', 'Z');
        $char_more = ['A', 'B', 'C', 'D', 'E', 'F'];
        foreach ($char_more as $char) {
            $tmp_list = range('A', 'Z');
            foreach ($tmp_list as $value) {
                $char_list[] = $char . $value;
            }
        }

        $header_index = array_splice($char_list, 0, count($types));
        
        $file_type = \PHPExcel_IOFactory::identify($file_path);
        $excel_reader = \PHPExcel_IOFactory::createReader($file_type);
        $excel = $excel_reader->load($file_path);
        $excel->setActiveSheetIndex(0);

        foreach ($header_index as $key => $value)
        {
            $_index = $value . '1';
            $_value = $types[$key];
            $excel->getActiveSheet()->setCellValue($_index, $_value);
        }
        $objWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $objWriter->save($file_path);

        return ['code' => 0, 'msg' => '添加成功'];
    }


    /**
     * 获取费用详情
     * @author lamkakyun
     * @date 2019-04-08 21:02:06
     * @return void
     */
    public function getPlatformDetail($params)
    {
        if (!isset($params['platform']) || empty($params['platform'])) return ['code' => -1, 'msg' => '参数错误'];

        $where = ['platform' => $params['platform']];

        if (isset($params['account']) && !empty($params['account'])) $where['platform_account'] = ['IN', $params['account']];

        if ($params['checkDate'] == 'day')
        {
            $_start_time = strtotime($params['scantime_start']);
            $_end_time = strtotime($params['scantime_end'] . ' +1 day');
        }
        else
        {
            $_start_time = strtotime($params['scandate_start']);
            $_end_time = strtotime($params['scandate_end'] . ' +1 month');
        }
        $where['datetime'] = [['EGT', $_start_time], ['LT', $_end_time]];

        // 获取费用
        $group_by = 'type_id, platform_account';

        $fields = "{$group_by}, type_name, SUM(expend_amount) as sum_expend_amount";
        $data = $this->expend_model->field($fields)->where($where)->group($group_by)->select()->toArray();

        $account_list = array_column($data, 'platform_account');

        // 获取销售额
        $sale_where = array_merge($where, ['platform_account', ['IN', $account_list]]);
        $sale_group_by = 'platform_account';
        $sale_fields = "{$sale_group_by}, SUM(sales) as sum_sales";
        $tmp_sale = OrderLib::getInstance()->orderSalesModel->field($sale_fields)->where($where)->group($sale_group_by)->select()->toArray();

        $sale_data = [];
        foreach ($tmp_sale as $key => $value)
        {
            $sale_data[$value['platform_account']] = $value['sum_sales'];
        }

        // 合并数据
        $ret_data = [];
        foreach ($data as $key => $value)
        {
            $_sale = $sale_data[$value['platform_account']] ?? '0';
            $_expend_rate = $_sale == 0 ? '-' : round($value['sum_expend_amount'] / $_sale * 100, 2) . '%';
            $ret_data[$value['platform_account']][$value['type_id']] = [
                'type_id' => $value['type_id'],
                'type_name' => $value['type_name'],
                'sum_expend_amount' => $value['sum_expend_amount'],
                'sum_sales' => $_sale,
                'expend_rate' => $_expend_rate,
            ];
        }

        // echo '<pre>';var_dump($ret_data);echo '</pre>';
        // exit;

        return $ret_data;
    }
}