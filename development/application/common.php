<?php
// +----------------------------------------------------------------------
// | 公共助手函数
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: kevin
// +----------------------------------------------------------------------

use fast\Form;

// comment by lamkakyun
// TP5 的 warnning 变成了 error。导致 未定义索引，变量等报错。受影响最严重的是模板编写，因为模板存在很多未定义的变量，或者索引下标，如果，每个都个判断或者设置，那么模板写起来就他妈费劲。。。
// 只有在这里，或之后的文件，设置的错误级别才会生效，在public/index.php 中不生效.因为必须等 错误处理注册后，重置错误级别才生效
// error_reporting(E_ERROR | E_WARNING | E_PARSE);
error_reporting(E_ALL);

if (!function_exists('__')) {
    /**
     * 获取语言变量值
     * @param string $name 语言变量名
     * @param array $vars 动态变量值
     * @param string $lang 语言
     * @return mixed
     */
    function __($name, $vars = [], $lang = ''): string
    {
        if (is_numeric($name) || !$name)
            return $name;
        if (!is_array($vars)) {
            $vars = func_get_args();
            array_shift($vars);
            $lang = '';
        }
        return \think\Lang::get($name, $vars, $lang);
    }
}

if (!function_exists('datetime')) {

    /**
     * 将时间戳转换为日期时间
     * @param int $time 时间戳
     * @param string $format 日期时间格式
     * @return string
     */
    function datetime($time, $format = 'Y-m-d H:i:s')
    {
        $time = is_numeric($time) ? $time : strtotime($time);
        return date($format, $time);
    }

}

if (!function_exists('is_really_writable')) {

    /**
     * 判断文件或文件夹是否可写
     * @param    string $file 文件或目录
     * @return    bool
     */
    function is_really_writable($file)
    {
        if (DIRECTORY_SEPARATOR === '/') {
            return is_writable($file);
        }
        if (is_dir($file)) {
            $file = rtrim($file, '/') . '/' . md5(mt_rand());
            if (($fp = @fopen($file, 'ab')) === FALSE) {
                return FALSE;
            }
            fclose($fp);
            @chmod($file, 0777);
            @unlink($file);
            return TRUE;
        } elseif (!is_file($file) OR ($fp = @fopen($file, 'ab')) === FALSE) {
            return FALSE;
        }
        fclose($fp);
        return TRUE;
    }

}

if (!function_exists('rmdirs')) {

    /**
     * 删除文件夹
     * @param string $dirname 目录
     * @param bool $withself 是否删除自身
     * @return boolean
     */
    function rmdirs($dirname, $withself = true)
    {
        if (!is_dir($dirname))
            return false;
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dirname, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }
        if ($withself) {
            @rmdir($dirname);
        }
        return true;
    }

}

if (!function_exists('copydirs')) {

    /**
     * 复制文件夹
     * @param string $source 源文件夹
     * @param string $dest 目标文件夹
     */
    function copydirs($source, $dest)
    {
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }
        foreach (
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST) as $item
        ) {
            if ($item->isDir()) {
                $sontDir = $dest . DS . $iterator->getSubPathName();
                if (!is_dir($sontDir)) {
                    mkdir($sontDir, 0755, true);
                }
            } else {
                copy($item, $dest . DS . $iterator->getSubPathName());
            }
        }
    }
}

if (!function_exists('build_select')) {

    /**
     * 生成下拉列表
     * @param string $name
     * @param mixed $options
     * @param mixed $selected
     * @param mixed $attr
     * @return string
     */
    function build_select($name, $options, $selected = [], $attr = [])
    {
        $options  = is_array($options) ? $options : explode(',', $options);
        $selected = is_array($selected) ? $selected : explode(',', $selected);
        return Form::select($name, $options, $selected, $attr);
    }
}


if (!function_exists('build_radios')) {

    /**
     * 生成单选按钮组
     * @param string $name
     * @param array $list
     * @param mixed $selected
     * @return string
     */
    function build_radios($name, $list = [], $selected = null)
    {
        $html     = [];
        $selected = is_null($selected) ? key($list) : $selected;
        $selected = is_array($selected) ? $selected : explode(',', $selected);
        foreach ($list as $k => $v) {
            $html[] = sprintf(Form::label("{$name}-{$k}", "%s {$v}"), Form::radio($name, $k, in_array($k, $selected), ['id' => "{$name}-{$k}"]));
        }
        return '<div class="radio">' . implode(' ', $html) . '</div>';
    }
}

if (!function_exists('build_checkboxs')) {

    /**
     * 生成复选按钮组
     * @param string $name
     * @param array $list
     * @param mixed $selected
     * @return string
     */
    function build_checkboxs($name, $list = [], $selected = null)
    {
        $html     = [];
        $selected = is_null($selected) ? [] : $selected;
        $selected = is_array($selected) ? $selected : explode(',', $selected);
        foreach ($list as $k => $v) {
            $html[] = sprintf(Form::label("{$name}-{$k}", "%s {$v}"), Form::checkbox($name, $k, in_array($k, $selected), ['id' => "{$name}-{$k}"]));
        }
        return '<div class="checkbox">' . implode(' ', $html) . '</div>';
    }
}

if (!function_exists('can')) {
    /**
     * 判断当前用户是否具有参数指定的权限
     * @param string $operation
     * @return array|bool
     */
    function can($operation = '')
    {
        // 非生产环境排除权限管理
        if (ENVIRONMENT != 'production') {
            return true;
        }

        $tname = session('tname');
        if (!empty($tname) && (preg_match('/管理员/', $tname) || preg_match('/IT技术部/', $tname))) {
            return true;
        }

        $operations = session('power');
        $powers     = explode(',', $operations);

        if (!$operation) {
            return false;
        }

        return in_array(strtolower(trim($operation)), $powers);
    }
}

if (!function_exists('build_menu')) {
    /**
     * 生成菜单链接
     * @param $url
     * @param $title
     * @param string $class
     * @return string
     */
    function build_menu($url, $title, $class = '')
    {
        $request    = \think\Request::instance();
        $controller = explode(".", strtolower($request->controller()));

        // 授权信息, 目前测试
        $active = [$request->module(), $controller[0], $request->action()];
        $url    = str_replace('.html', '', $url);

        $urls = explode('/', trim($url, '/'));
        if (!isset($urls[2]) || empty($urls[2])) {
            $urls[2] = 'index';
        }
        $operation = strtolower(implode("_", $urls));
        if (!can($operation)) {
            return '';
        }

        return '<li><a href="' . url($url) . '"><i class="' . $class . '"></i> ' . __($title) . '</a></li>';
    }
}


if (!function_exists('build_json')) {
    /**
     * 解析json数据
     * @param $json
     * @return string
     */
    function build_json($json)
    {
        $arr = json_decode($json, true);
        if (empty($arr)) {
            return '';
        }

        $return = '';
        foreach ($arr as $key => $value) {
            $keyname = ucfirst($key);

            $return .= __($keyname) . ':' . $value . '&nbsp;&nbsp;';
        }
        return $return;
    }
}

if (!function_exists('replace_query')) {
    /**
     * 解析json数据
     * @param $arr
     * @return string
     */
    function replace_query($arr)
    {
        $arr = json_decode(json_encode($arr), true);
        return $arr;
    }
}

if (!function_exists('get_field_data')) {
    /**
     * 获取数组中的字段数据
     * @param $data 待处理数组(一维数组)
     * @param string $fieldStr 需要获取字段
     * @return array $returnArr 返回相应字段数组
     */
    function get_field_data($data, $fieldStr)
    {
        if (count($data) < 1) return [];
        if (!$fieldStr) return $data;
        $returnArr = [];
        foreach ($data as $key => $val) {
            if (in_array($key, explode(',', $fieldStr))) $returnArr[$key] = $val;
        }
        return $returnArr;
    }
}

if (!function_exists('replace_currency')) {
    /**
     * 金额币种转换
     * @param int $money 金额
     * @param string $currency 币种
     * @param string $replaceCurrency 转换后的币种(默认美元)
     * @return string
     */
    function replace_currency($money, $currency, $replaceCurrency = 'USD')
    {
        if (!$money || !$currency) return $money;
        $redis   = \think\Config::get('redis');
        $Redis   = new \think\cache\driver\Redis($redis);
        $rateArr = $Redis->get($redis['rate']);
        if (!isset($rateArr[$replaceCurrency]) || !isset($rateArr[$currency])) return 0;
        return round($money * $rateArr[$currency] / $rateArr[$replaceCurrency], 3);
    }
}

if (!function_exists('get_group_sn')) {
    /**
     * 获取产品包编号
     * @param $exist_group_sn
     * @return string
     */
    function get_group_sn($exist_group_sn)
    {
        if (empty($exist_group_sn)) {
            $groupNumber = 1;
        } else {
            $groupNumber = (int) substr($exist_group_sn, 1) + 1;
        }
        if ($groupNumber < 10) {
            return 'G0000' . $groupNumber;
        } else if ($groupNumber < 100) {
            return 'G000' . $groupNumber;
        } else if ($groupNumber < 1000) {
            return 'G00' . $groupNumber;
        } else if ($groupNumber < 10000) {
            return 'G0' . $groupNumber;
        }
        return 'G' . $groupNumber;
    }
}

if (!function_exists('get_day_of_month')) {
    /**
     * 获取一个月有多少天
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-22 10:47:53
     */
    function get_day_of_month($month, $year = false)
    {
        $year  = intval($year);
        $month = intval($month);

        if (!$year) $year = date('Y');

        if (!in_array($month, range(1, 12))) return false;

        if (in_array($month, [1, 3, 5, 7, 8, 10, 12])) return 31;
        if (in_array($month, [4, 6, 9, 11])) return 30;
        if ($month == 2) {
            // 闰年
            if (($year % 4 == 0 && $year % 100 != 0) || ($year % 400) == 0) return 29;
            else return 28;
        }
    }
}

if (!function_exists('range_day')) {
    /**
     * 获取一个范围的 天数 (可以递减，只要start > end)
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-22 02:49:59
     */
    function range_day($start, $end, $has_year = true)
    {
        $ret_data              = [];
        $date_format           = $has_year ? 'Y-m-d' : 'm-d';
        $one_day_total_seconds = 86400;

        // 默认值
        if (!$start) $start = $has_year ? date('Y-m-d') : date('m-d');
        if (!$end) $end = $has_year ? date('Y-m-d') : date('m-d');

        if ($start == $end) return [date($date_format, strtotime($start))];

        if (preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $start) && preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $end)) {
            $ret_data[] = date($date_format, strtotime($start));

            // 正向
            $tmp_start_time = strtotime($start) + $one_day_total_seconds;
            while ($tmp_start_time < strtotime($end)) {
                $ret_data[]     = date($date_format, $tmp_start_time);
                $tmp_start_time += $one_day_total_seconds;
            }

            // 反向
            $tmp_start_time = strtotime($start) - $one_day_total_seconds;
            while ($tmp_start_time > strtotime($end)) {
                $ret_data[]     = date($date_format, $tmp_start_time);
                $tmp_start_time -= $one_day_total_seconds;
            }

            $ret_data[] = date($date_format, strtotime($end));
        }

        return $ret_data;
    }
}


if (!function_exists('range_month')) {
    /**
     * 获取一个范围的 月份
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-22 03:21:39
     */
    function range_month($start, $end)
    {
        $ret_data    = [];
        $date_format = 'Y-m';

        // 默认值
        if (!$start) $start = date('Y-m');
        if (!$end) $end = date('Y-m');

        if ($start == $end) return [date($date_format, strtotime($start))];
        if (preg_match('/^\d{4}-\d{1,2}$/', $start) && preg_match('/^\d{4}-\d{1,2}$/', $end)) {
            $ret_data[] = date($date_format, strtotime($start));

            // 正向
            $tmp_start_time = strtotime($start . ' +1 month');
            while ($tmp_start_time < strtotime($end)) {
                $ret_data[]     = date($date_format, $tmp_start_time);
                $tmp_start_time = strtotime(date($date_format, $tmp_start_time) . ' +1 month');
            }

            // 反向
            $tmp_start_time = strtotime($start . ' -1 month');
            while ($tmp_start_time > strtotime($end)) {
                $ret_data[]     = date($date_format, $tmp_start_time);
                $tmp_start_time = strtotime(date($date_format, $tmp_start_time) . ' -1 month');
            }

            $ret_data[] = date($date_format, strtotime($end));
        }

        return $ret_data;
    }
}


if (!function_exists('china_num')) {
    /**
     * 转换成中国数字，非准确的
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-29 04:39:51
     */
    function china_num($num)
    {
        $ret_num = intval($num);

        if ($ret_num >= 10000) $ret_num = sprintf("%.2f", $ret_num / 10000) . '万+';
        elseif ($ret_num >= 1000) $ret_num = sprintf("%.2f", $ret_num / 1000) . '千+';
        return $ret_num;
    }
}


if (!function_exists('day_diff')) {
    /**
     * 求两个日期的时间间隔
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-31 02:52:30
     */
    function day_diff($start_date, $end_date)
    {
        return intval((strtotime($end_date) - strtotime($start_date)) / 86400);
    }
}

if (!function_exists('get_day_pass')) {
    /**
     * 求出今天过去了多少，或一天过去了多少
     * @description 假如你同一天运行这个方法，并且第二次运行的值，比第一次运行的值要少，那么表示你回到了过去
     * @author lamkakyun
     * @date 2018-12-21 14:55:25
     * @return float
     */
    function get_day_pass($hour = '', $min = '', $sec = '')
    {
        $hour = $hour ?: date('H');
        $min = $min ?: date('i');
        $sec = $sec ?: date('s');

        $seconds_pass = $hour * 60 * 60 + $min * 60 + $sec;
        return round($seconds_pass / 86400, 4);
    }
}

if (!function_exists('grepDocComment')) {
    /**
     * 匹配反射出来的注释
     * @param $doc
     * @param string $name
     * @param string $default
     * @return string
     */
    function grepDocComment($doc, $name = '', $default = '')
    {
        if (empty($doc)) return $default;
        if (preg_match('#^/\*\*(.*)\*/#s', $doc, $comment) === false)
            return $default;

        $comment = trim($comment[1]);
        if (preg_match_all('#^\s*\*(.*)#m', $comment, $lines) === false)
            return $default;

        $docLines = ($lines[1]);
        if (empty($name))
            return trim($docLines[0]) ?? $default;
    }
}

if (!function_exists('addPrefixForArr')) {
    /**
     * 给数组的元素添加前缀，主要是 连表操作查询时用到
     * @author lamkakyun
     * @date 2019-02-15 11:17:27
     * @return void
     */
    function addPrefixForArr($arr, $prefix)
    {
        return array_map(function($val) use ($prefix) {return $prefix . $val;}, $arr);
    }
}

if (!function_exists('removePrefixForArr')) {
    /**
     * 给数组的元素添加前缀，主要是 连表操作查询时用到
     * @author lamkakyun
     * @date 2019-02-15 11:17:27
     * @return void
     */
    function removePrefixForArr($arr, $prefix)
    {
        return array_map(function($val) use ($prefix) {return preg_replace("/^{$prefix}/", '', $val);}, $arr);
    }
}

if (!function_exists('get_file_exention')) {
    /**
     * 获取文件扩展(自动转换为小写， 如果没有文件扩展，返回空)
     * @author lamkakyun
     * @date 2018-12-03 10:16:00
     * @return string
     */
    function get_file_exention($file_name)
    {
        $arr = explode('.', $file_name);
        if (count($arr) == 1) return '';

        $ext = $arr[count($arr) - 1];
        return strtolower($ext);
    }
}


if (!function_exists('find_duplicates'))
{
    /**
     * 找出数组重复的元素
     * @author lamkakyun
     * @date 2019-04-02 15:27:00
     * @return void
     */
    function find_duplicates($arr)
    {
        $ret = [];
        foreach ($arr as $v)
        {
            isset($ret[$v]) ? $ret[$v] += 1 : $ret[$v] = 1;
        }

        return array_keys(array_filter($ret, function($v) {return $v > 1;}));
    }
}