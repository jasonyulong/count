<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    mina
 */

namespace app\common\command\cli;

use app\common\model\Account;
use think\cache\driver\Redis;
use think\console\Input;
use think\console\Output;
use think\Config;

/**
 * 账号数据同步
 * Class Common
 * @package app\common\command\cli
 */
class AccountCli
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
     * @desc   同步平台账号
     * @author mina
     * @param  void
     * @return string
     */
    public function platformAccount(): string
    {
        $model = new Account();
        $field = [
            'id',
            'ebay_account',
            'ebay_user',
            'platform',
        ];
        $rows  = $model->field($field)->select();
        if (empty($rows)) {
            return "empty";
        }
        $data = [];
        foreach ($rows as $key => $value) {
            $data[$value['id']] = $value->toArray();
        }
        $status = $this->redis->set(Config::get('redis.accounts_list'), $data);
        return $status ? "success" : 'fail';
    }
}
