<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    kevin
 */

namespace app\count\command\sync;

use app\common\model\GoodsCategory;
use app\common\model\Countries;
use think\cache\driver\Redis;
use think\console\Input;
use think\console\Output;
use think\Config;

/**
 * 基础数据同步
 * Class Common
 * @package app\count\command\sync
 */
class Common
{
    /**
     * redis链接句柄
     * @var Redis object
     */
    private $redis;

    /**
     * 构造函数
     * Common constructor.
     * @param Input $input 输入
     * @param Output $output 输出
     */
    public function __construct(Input $input, Output $output)
    {
        $this->redis = new Redis(Config::get('redis'));
    }

    /**
     * 同步商品分类数据到redis
     * @return string
     * @throws \think\exception\DbException
     */
    public function goodscategory(): string
    {
        $findAll = GoodsCategory::all(function ($query) {
            $query->field(['id', 'name', 'pid']);
        });
        if (empty($findAll)) {
            return "not select categorys!";
        }

        $default = [];
        foreach ($findAll as $val) {
            $default[$val->id] = $val->toArray();
        }

        $this->redis->set(Config::get('redis.goods_category'), $default);
        return 'Successed!';
    }

    /**
     * 同步国家名称和二字码到redis
     * @return string
     * @throws \think\exception\DbException
     */
    public function countries(): string
    {
        $findAll = Countries::all(function ($query) {
            $query->field(['id', 'char_code', 'name']);
        });
        if (empty($findAll)) {
            return "not select Countries!";
        }

        $default = [];
        foreach ($findAll as $val) {
            $default[$val->char_code] = $val->name;
        }

        $this->redis->set(Config::get('redis.countries'), $default);
        return 'Successed!';
    }
}
