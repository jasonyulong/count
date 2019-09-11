<?php
/**
 * @Copyright (C), ZhuoShi.
 * @Author: 杨能文
 * @Name: Product.php
 * @Date: 2019/2/26
 * @Time: 13:52
 * @Description 开发产品统计脚本
 */

namespace app\count\command\develop;

use app\count\model\DevelopType;
use app\count\model\GoodsDevelop;
use think\console\Input;
use think\console\Output;

class Product
{
    /**
     * @var 商品开发模型
     */
    private $GoodsDevelopModel;

    /**
     * @var 开发类别模型
     */
    private $DevelopTypeModel;

    /**
     * product constructor. 构造函数
     */
    public function __construct()
    {
        $this->GoodsDevelopModel = new GoodsDevelop();
        $this->DevelopTypeModel  = new DevelopType();
    }

    /**
     * @desc 同步数据
     * @author 杨能文
     * @date 2019/2/26 14:23
     * @access public
     * @param Input $input
     * @param Output $output
     * @return string
     */
    public function sync(Input $input, Output $output)
    {
        $option = $input->getOptions();

        $day    = $option['day'] ?? date('Y-m-d');
        $start  = $option['start'] ?? $day;
        $end    = $option['end'] ?? date('Y-m-d');
        $dayArr = $this->getDays($start, $end);

        foreach ($dayArr as $ymd) {
            $output->writeln(sprintf("%s %s", $ymd, 'start'));

            $this->getDevelopTypeData($ymd);

            $output->writeln(sprintf('%s - %s', $ymd, 'success'));
        }
        return "success\n\n";
    }

    /**
     * @desc 获取开发产品数据
     * @author 杨能文
     * @date 2019/2/26 15:02
     * @access public
     * @param $ymd
     * @return bool
     */
    private function getDevelopTypeData($ymd)
    {
        $year     = date('Y', strtotime($ymd));
        $month    = date('m', strtotime($ymd));
        $day      = date('d', strtotime($ymd));
        $start    = strtotime($ymd . ' 00:00:00');
        $end      = strtotime($ymd . ' 23:59:59');
        $where    = ['status_time' => ['between', [$start, $end]], 'develop_user' => ['neq', ''], 'status' => ['in', [1, 2, 5, 6, 8, 9, 10, 12]]];
        $fieldStr = "status as type,develop_user,count(id) as counts";
        $groupStr = "status,develop_user";

        $data = $this->GoodsDevelopModel->where($where)->field($fieldStr)->group($groupStr)->select();

        if (empty($data)) return true;

        foreach ($data as $key => $val) {
            //唯一查询条件
            $unique = [
                'year'         => $year,
                'month'        => $month,
                'days'         => $day,
                'develop_user' => $val['develop_user'],
                'type'         => $val['type'],
            ];

            //更新数据
            $saveData = [
                'counts'   => $val['counts'],
                'datetime' => strtotime($ymd)
            ];

            $model     = $this->DevelopTypeModel;
            $hasRefund = $model->where($unique)->find();
            if (!empty($hasRefund)) {
                $model->update($saveData, $unique);
            } else {
                $model->insert(array_merge($unique, $saveData));
            }
        }
        return true;
    }

    /**
     * 获取两个区间的所有天
     * @param $start 开始时间
     * @param $end 结束时间
     * @param array $default
     * @return array
     */
    private function getDays($start, $end, $default = [])
    {
        $days = (strtotime($end) - strtotime($start)) / 86400;
        if ($days <= 0) return [$start];

        for ($i = 0; $i <= $days; $i++) {
            $default[] = date('Y-m-d', strtotime("+{$i} day", strtotime($start)));
        }
        return $default;
    }
}