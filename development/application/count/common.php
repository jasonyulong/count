<?php
// +----------------------------------------------------------------------
// | 本模块公共助手函数
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: kevin
// +----------------------------------------------------------------------

if (!function_exists('gen_pager_data')) {
    /**
     * 生成自定义的分页数据
     * @author: jason
     * @date: 2018-09-14 18:18:59
     */
    function gen_pager_data($current_page, $list_total, $page_size = 100)
    {
        $page_count = ($list_total != $page_size) ? intval($list_total / $page_size) + 1 : intval($list_total / $page_size);
        if ($current_page > $page_count) $current_page = $page_count;
        if ($current_page <= 3) {
            $all_page_num = range(1, $page_count > 5 ? 5 : $page_count);
        } else if ($page_count - $current_page <= 3) {
            $all_page_num = range($page_count - 5, $page_count);
        } else {
            $all_page_num = range($current_page - 2, $current_page + 2);
        }
        $last_page = ($current_page - 1) > 0 ? $current_page - 1 : 1;
        $next_page = ($current_page + 1) < $page_count ? ($current_page + 1) : $page_count;

        $all_page_num = array_filter($all_page_num, function ($val) {
            return $val > 0;
        });
        return ['all_page_num' => $all_page_num, 'last_page' => $last_page, 'next_page' => $next_page];
    }
}


if (!function_exists('getAllPower')) {
    /**
     * 获取我所有的权限
     * @return array
     */
    function getAllPower()
    {
        $session = \think\Session::get();
        if (empty($session)) {
            return ['power' => [], 'ebayaccounts' => [], 'vieworderstatus' => []];
        }

        $session['power']           = explode(',', $session['power'] ?? '');
        $session['ebayaccounts']    = explode(',', $session['ebayaccounts'] ?? '');
        $session['vieworderstatus'] = explode(',', $session['vieworderstatus'] ?? '');
        return $session;
    }
}

if (!function_exists('getRolePower')) {
    /**
     * 判断当前的用户是否是管理员或是IT技术部-程序员
     * @return bool
     */
    function getRolePower()
    {
        $tname = $_SESSION['tname'] ?? '';
        if (preg_match('/管理员/', $tname) || preg_match('/程序员/', $tname)) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('getRound')) {
    /**
     * 返回两个数相除的结果
     * @param $a
     * @param $b
     * @return float|int
     */
    function getRound($a, $b)
    {
        if ($b <= 0) return 0;
        if ($a <= 0) return 0;
        return round($a / $b, 2);
    }
}
