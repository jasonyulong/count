<?php

namespace app\count\controller\order;

/**
 * 确认利润报表
 * Class Preprofit
 * @package app\count\controller\order
 */
class Confirmprofit extends Preprofit
{
    // todo: 直接继承预利润就好了,数据结构完全一样，还写个鸡腿啊,用个字段区分一下 预利润和 确定利润就行啦
    protected $profit_type = 'confirmprofit';

    /**
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-11-02 06:24:20
     */
    public function _initialize()
    {
        parent::_initialize();
        $this->assign('module', 'finance');

        // 用来确定是那个利润，预利润 还是 确定利润
        $this->assign('profit_type', $this->profit_type);
    }
}