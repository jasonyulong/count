<?php
/**
 * @Copyright (C), ZhuoShi.
 * @Author: 杨能文
 * @Name: DevelopSalesLib.php
 * @Date: 2019/2/25
 * @Time: 11:55
 * @Description
 */

namespace app\count\library\develop;

use app\count\model\DevelopSales;

class DevelopSalesLib extends DevelopSales
{
    /**
     * @desc 获取列表数据
     * @author 杨能文
     * @date 2019/2/25 11:54
     * @access public
     * @param array $params 参数数组
     * @return array $return_data 返回数组
     */
    public function getList($params)
    {
        $map                       = [];
        $params['day_start']       = isset($params['day_start']) && $params['day_start'] ? $params['day_start'] : date('Y-m-d', strtotime('-15 day'));
        $params['day_end']         = isset($params['day_end']) && $params['day_end'] ? $params['day_end'] : date('Y-m-d');
        $params['month_start']     = isset($params['month_start']) && $params['month_start'] ? $params['month_start'] : date('Y-m', strtotime('-1 month'));
        $params['month_end']       = isset($params['month_end']) && $params['month_end'] ? $params['month_end'] : date('Y-m');
        $params['checkDate']       = isset($params['checkDate']) && $params['checkDate'] ? $params['checkDate'] : 'day';

        if ($params['checkDate'] == 'day') {
            $startTime = strtotime($params['day_start']);
            $endTime   = strtotime($params['day_end'] . ' 23:59:59');
        } elseif ($params['checkDate'] == 'today') {
            $startTime = strtotime(date('Y-m'."-01"));;
            $endTime   = strtotime(date('Y-m'."-01")."+1 month")-1;
        } elseif ($params['checkDate'] == 'yesterday') {
            $day       = date('Y-m-d', strtotime("- 1 day"));
            $startTime = strtotime($day);
            $endTime   = strtotime($day . " 23:59:59");
        } elseif ($params['checkDate'] == 'recently3day') {
            $day       = date('Y-m-d', strtotime("- 2 day"));
            $startTime = strtotime($day);
            $today     = date('Y-m-d');
            $endTime   = strtotime($today . " 23:59:59");
        } elseif ($params['checkDate'] == 'month') {
            $startTime = strtotime($params['month_start'] . "-01");
            $endTime   = strtotime($params['month_end'] . '-01+1 month') - 1;
        }
        if ($params['type'] == 'date') $group = ($params['checkDate'] == 'month') ? 'develop_user,month,year' : 'develop_user,days,month,year';

        $fieldStr = "$group,sum(sales) as counts,sum(refunds) as refunds,sum(return_total) as return_total";

        $params['develop_user']    = isset($params['develop_user']) && $params['develop_user'] ? $params['develop_user'] : [];
        if ($params['develop_user']) $map['develop_user'] = array('in', $params['develop_user']);

        $start = ($params['p'] - 1) * $params['ps'];
        $count = $this->where($map)->field($fieldStr)->whereTime('datetime', 'between', [$startTime, $endTime])->group($group)->count();
        $list  = $this->where($map)->field($fieldStr)->whereTime('datetime', 'between', [$startTime, $endTime])->group($group)->order('datetime desc')->select()->toArray();

        //当月数据
        $startTime1  = strtotime(date('Y-m'."-01"));
        $endTime2    = strtotime(date('Y-m'."-01")."+1 month")-1;
        $group1      = "develop_user,month,year";
        $monthData   = $this->where($map)->field('develop_user,sum(sales) as counts,month,year,sum(refunds) as refunds,sum(return_total) as return_total')->whereTime('datetime', 'between', [$startTime1, $endTime2])->group($group1)->order('datetime desc')->select()->toArray();
        foreach($monthData as &$val){
            $val['month'] = '00';
        }
        $list = array_merge($monthData,$list);
        $return_data = $this->setDataByDevelop($list,$params);
        ksort($return_data['total']);
        $return_data['count'] = $count;
        return $return_data;
    }

    /**
     * @desc 根据销售人员组装数据
     * @author 杨能文
     * @date 2019/2/25 16:47
     * @access public
     * @param $list
     * @param $params
     * @return array
     */
    public function setDataByDevelop($list,$params){

        //组装数据
        $totalArr = [];
        $data = [];
        foreach($list as $key=>$val){
            $keys = $val['develop_user'];
            $type = isset($val['days']) ? $val['year'].$val['month'].$val['days'] : $val['year'].$val['month'];
            if(isset($data[$keys])){
                $data[$keys][$type]['sales'] = isset($data[$keys][$type]['sales']) ? $data[$keys]['counts'] + $val['counts'] : $val['counts'];
                $data[$keys][$type]['refunds'] = isset($data[$keys][$type]['refunds']) ? $data[$keys]['refunds'] + $val['refunds'] : $val['refunds'];
                $data[$keys][$type]['return_total'] = isset($data[$keys][$type]['return_total']) ? $data[$keys]['return_total'] + $val['counts'] : $val['return_total'];
            } else{
                $data[$keys][$type]['sales']   = $val['counts'];
                $data[$keys][$type]['refunds']   = $val['refunds'];
                $data[$keys][$type]['return_total']   = $val['return_total'];
            }
        }

        foreach($data as $key=>$val){
            $data[$key]['sum'] = 0;
            foreach($val as $k=>$v){
                if(is_numeric($k)){
                    if(substr($k,4) != '00'){
                        $data[$key]['sum']          = $data[$key]['sum'] + $v['sales'];
                        $totalArr[$k]               = isset($totalArr[$k]) ? $totalArr[$k] + $v['sales'] : $v['sales'];
                    }else{
                        $data[$key]['month']        = $v;
                        $totalArr['month']          = isset($totalArr['month']) ? $totalArr['month'] + $v['sales'] : $v['sales'];
                        unset($data[$key][$k]);
                    }

                }
            }
        }

        $totalArr['sum'] = array_sum(array_column($data,'sum'));
        foreach($data as $key=>$val){
            foreach($totalArr as $k=>$v){
                if(!isset($val[$k]))$data[$key][$k] = [ 'sales'=>0, 'refunds'=>0, 'return_total'=>0 ];
            }
            if(!isset($val['month']))$data[$key]['month'] = [ 'sales'=>0, 'refunds'=>0, 'return_total'=>0 ];
        }
        if(!isset($totalArr['month']))$totalArr['month'] = 0;

        $return_data = [
            'data'   => $data,
            'params' => $params,
            'total'  => $totalArr,
        ];
        return $return_data;
    }
}