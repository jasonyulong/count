<?php

namespace app\count\controller\check;


use app\count\library\OrgLib;
use app\common\library\FMSAuth;
use app\common\library\ToolsLib;
use app\count\model\FinanceCheck;
use app\common\library\CarrierLib;
use app\count\library\order\OrderLib;
use app\common\controller\AuthController;
use app\count\library\finance\FinanceCheckLib;

/**
 * 财务抽查
 */
class Finance extends AuthController
{

    /**
     * 列表
     * @access auth
     * @author lamkakyun
     * @date 2019-03-29 14:13:25
     * @return void
     */
    public function index()
    {
        $params = array_merge(input('get.'), input('post.'));

        if (!isset($params['p'])) $params['p']                       = 1;
        if (!isset($params['ps'])) $params['ps']                     = 20;
        if (!isset($params['check_type'])) $params['check_type']     = [];
        if (!isset($params['check_status'])) $params['check_status'] = [];
        if (!isset($params['start_time'])) $params['start_time']     = date('Y-m-d', strtotime('-10 days'));
        if (!isset($params['end_time'])) $params['end_time'] = date('Y-m-d');

        $check_types      = FinanceCheckLib::getInstance()->getCheckType();
        $check_status     = FinanceCheckLib::getInstance()->getCheckStatus();
        $check_time_types = FinanceCheckLib::getInstance()->getCheckTimeType();

        $data = FinanceCheckLib::getInstance()->getCheckList($params);

        $this->_assignPagerData($this, $params, $data['count']);

        $this->assign('params', $params);
        $this->assign('check_types', $check_types);
        $this->assign('check_status', $check_status);
        $this->assign('check_time_types', $check_time_types);
        $this->assign('list', $data['list']);
        $this->assign('count', $data['count']);
        $this->assign('list_total', $data['count']);
        $this->assign('uid', FMSAuth::instance()->id);

        return $this->view->fetch("index");
    }


    /**
     * 添加抽查
     * @access auth
     * @author lamkakyun
     * @date 2019-03-29 15:37:05
     * @return void
     */
    public function add()
    {
        $params = array_merge(input('get.'), input('post.'));
        $params['action'] = 'add';
        return $this->_edit($params);
    }
    

    /**
     * 添加抽查
     * @access auth
     * @author lamkakyun
     * @date 2019-03-29 15:37:05
     * @return void
     */
    public function edit()
    {
        $params = array_merge(input('get.'), input('post.'));
        $params['action'] = 'edit';
        return $this->_edit($params);
    }

    private function _edit($params)
    {
        $is_edit = $params['action'] == 'edit' ? true : false;
        if ($this->request->isGet()) {

            if ($is_edit)
            {
                $model = new FinanceCheck();

                $data = $model->where(['id' => $params['id']])->find()->toArray();
                $data['check_platform'] = explode(',', $data['check_platform']);
                $data['exception_platform'] = explode(',', $data['exception_platform']);
                $data['check_accounts'] = explode(',', $data['check_accounts']);
                $data['check_org_ids'] = explode(',', $data['check_org_ids']);
                $data['check_sellers'] = explode(',', $data['check_sellers']);
                $data['check_carrier_companys'] = explode(',', $data['check_carrier_companys']);
                $data['check_carriers'] = explode(',', $data['check_carriers']);
                $data['check_order_fields'] = explode(',', $data['check_order_fields']);
                $data['solution_log'] = json_decode($data['solution_log'], true) ?? [];

                $all_accounts = ToolsLib::getInstance()->getAllAccounts(3);
                $all_orgs = OrgLib::getInstance()->getBussinessOrgArray(true);
                $all_carriers = CarrierLib     ::init()->getCarrier();

                $data['check_platform'] = array_filter($data['check_platform'], function($v){return !empty($v);});

                $account_list = [];
                foreach ($data['check_platform'] as $v)
                {
                    $account_list = array_merge($account_list, $all_accounts[$v]);
                }

                $seller_list = [];
                $data['check_org_ids'] = array_filter($data['check_org_ids'], function($v){return !empty($v);});
                foreach ($data['check_org_ids'] as $v)
                {
                    if ($v != -1) $seller_list = array_merge($seller_list, array_column($all_orgs[$v]['org_full_user_list'], 'user_name'));
                }

                $carrier_list = [];
                foreach ($all_carriers as $k => $v)
                {
                    if (in_array($v['CompanyName'], $data['check_carrier_companys'])) $carrier_list[$k] = $v;
                }
               
                $this->assign('account_list', array_unique($account_list));
                $this->assign('seller_list', array_unique($seller_list));
                $this->assign('carrier_list', $carrier_list);
                $this->assign('data', $data);
            }

            $check_types          = FinanceCheckLib::getInstance()->getCheckType();
            $check_status         = FinanceCheckLib::getInstance()->getCheckStatus();
            $check_time_types     = FinanceCheckLib::getInstance()->getCheckTimeType();
            $order_platform_list  = OrderLib       ::getInstance()->getOrderPlatformConf();
            $org_list             = OrgLib         ::getInstance()->getBussinessOrgArray(true);
            $carrier_company_list = CarrierLib     ::init()->getCarrierCompany();
            $order_field_list     = OrderLib       ::getInstance()->getOrderFieldsConf();
    
            // 重组数据 格式
            $tmp = $org_list;
            $org_list = [];
            foreach ($tmp as $key => $value)
            {
                $_new_name  = $value['level'] > 2 ? '|' . str_repeat('---', $value['level'] - 2) . $value['name'] : $value['name'];
                $_tmp_org = [
                    'id' => $value['id'],
                    'parent_id'   => $value['parent_id'],
                    'name'        => $_new_name,
                    'seller_list' => array_map(function ($v) {
                        return $v['user_name'];
                    }, $value['org_full_user_list'])
                ];
                $org_list[] = $_tmp_org;
            }
    
            // var_dump($org_list);exit;
            $this->assign('params', $params);
            $this->assign('check_types', $check_types);
            $this->assign('check_status', $check_status);
            $this->assign('check_time_types', $check_time_types);
            $this->assign('order_platform_list', $order_platform_list);
            $this->assign('org_list', $org_list);
            $this->assign('carrier_company_list', $carrier_company_list);
            $this->assign('order_field_list', $order_field_list);
    
            return $this->view->fetch("add");
        } else {
            return json(FinanceCheckLib::getInstance()->addCheck($params, $is_edit));
        }
    }


    /**
     * 详情
     * @access auth
     * @author lamkakyun
     * @date 2019-03-30 09:33:15
     * @return void
     */
    public function detail()
    {
        $model = new FinanceCheck();
        $params     = array_merge(input('get.', '', 'trim'), input('post.', '', 'trim'));

        if (!isset($params['id']) || !preg_match('/^\d+$/', $params['id'])) return $this->error('参数错误');

        $data = $model->where(['id' => $params['id']])->find()->toArray();
        if (!$data) return $this->error('记录不存在');

        $check_types      = FinanceCheckLib::getInstance()->getCheckType();
        $check_status     = FinanceCheckLib::getInstance()->getCheckStatus();
        $check_time_types = FinanceCheckLib::getInstance()->getCheckTimeType();

        $carrier_company_list = CarrierLib     ::init()->getCarrierCompany();
        $carrier_list = CarrierLib     ::init()->getCarrier();

        $company_ids = array_filter(explode(',', $data['check_carrier_companys']), function($val) {return !empty($val);});
        $carrier_ids = array_filter(explode(',', $data['check_carriers']), function($val) {return !empty($val);});;

        $tmp_company_arr = [];
        $tmp_carrier_arr = [];

        foreach ($company_ids as $value)
        {
            $tmp_company_arr[] = $carrier_company_list[$value]['sup_abbr'] ?? '';
        }
        foreach ($carrier_list as $value)
        {
            if (in_array($value['id'], $carrier_ids)) $tmp_carrier_arr[] = $value['name'];
        }
        $company_str = implode(',', $tmp_company_arr);
        $carrier_str = implode(',', $tmp_carrier_arr);
        $data['company_str'] = $company_str;
        $data['carrier_str'] = $carrier_str;
        $data['solution_log'] = json_decode($data['solution_log'], true) ?? [];

        $check_org_ids = explode(',', $data['check_org_ids']);
        $check_org_ids = array_filter($check_org_ids, function($v){return !empty($v);});

        $org_list = OrgLib::getInstance()->getBussinessOrgArray();
        $check_org_arr = [];
        foreach ($check_org_ids as $v)
        {
            if ($v == -1) $check_org_arr[] = '其他';
            else $check_org_arr[] = $org_list[$v]['name'];
        }

        $data['check_org_str'] = implode(',', $check_org_arr);
        // echo '<pre>';var_dump($org_list, $check_org_ids);echo '</pre>';
        // exit;

        $order_field_list     = OrderLib       ::getInstance()->getOrderFieldsConf();

        $check_order_fields = array_filter(explode(',', $data['check_order_fields']), function($v){return !empty($v);});
        $check_order_fields_arr = [];
        foreach ($check_order_fields as $v)
        {
            $check_order_fields_arr[] = $order_field_list[$v] ?? '';
        }
        $data['check_order_fields_str'] = implode(',', $check_order_fields_arr);

        $this->assign('data', $data);
        $this->assign('check_types', $check_types);
        $this->assign('check_status', $check_status);
        $this->assign('check_time_types', $check_time_types);

        return $this->view->fetch("detail");
    }
}