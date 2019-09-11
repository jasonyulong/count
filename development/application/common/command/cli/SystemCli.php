<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    mina
 */

namespace app\common\command\cli;

use think\Config;
use think\console\Input;
use think\console\Output;
use app\common\model\Store;
use think\cache\driver\Redis;
use app\common\library\ToolsLib;
use app\common\model\EbayConfig;
use app\common\library\CarrierLib;
use app\common\model\Organization;
use app\common\model\Config as CountConfig;

/**
 * 系统数据同步
 * Class SystemCli
 * @package app\common\command\cli
 */
class SystemCli
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
     * 同步所有仓库
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function store(): string
    {
        $model = new Store();
        $field = [
            'id',
            'store_name',
            'store_sn',
        ];
        $rows  = $model->field($field)->select();
        if (empty($rows)) {
            return "empty";
        }
        $data = [];
        foreach ($rows as $key => $value) {
            $data[$value['id']] = $value->toArray();
        }
        $status = $this->redis->set(Config::get('redis.store'), $data);
        return $status ? "success" : 'fail';
    }

    /**
     * 同步ERP ip 白名称
     * @author lamkakyun
     * @date 2019-02-16 11:19:28
     * @return void
     */
    public function ipWhiteList()
    {
        // config 表只有一条记录!>_<!
        $erp_config_model = new EbayConfig();
        $sys_config_model = new CountConfig();
        $iplist_id        = 'forbiddenip';

        $config = $erp_config_model->find()->toArray();


        $ip     = $config['white_list'];
        $ip_arr = explode(',', $ip);
        $ip_str = implode("\n", $ip_arr);

        $sys_config_model->where(['name' => $iplist_id])->update(['value' => $ip_str]);
        return "done\n";
    }

    /**
     * 汇率缓存
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function rate(): string
    {
        $model = new \app\common\model\Currency();
        $field = [
            'id',
            'currency',
            'rates',
        ];
        $rows  = $model->field($field)->select();
        if (empty($rows)) {
            return "empty";
        }
        $data = [];
        foreach ($rows as $key => $value) {
            $data[$value['currency']] = $value['rates'];
        }
        $status = $this->redis->set(Config::get('redis.rate'), $data);
        return $status ? "success" : 'fail';
    }

    /**
     * 订单状态
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function orderstatus(): string
    {
        $model = new \app\common\model\Topmenu();

        $rows = $model->field(['id', 'name'])->select();
        if (empty($rows)) return 'empty';

        $data = [1 => '待处理', 2 => '已经发货'];
        foreach ($rows as $key => $value) {
            $data[$value['id']] = $value['name'];
        }
        $status = $this->redis->set(Config::get('redis.order_status'), $data);
        return $status ? "success" : 'fail';
    }

    /**
     * 更新物流公司数据
     * @return string
     */
    public function company(): string
    {
        CarrierLib::init()->getCarrierCompany(true);

        return "success";
    }

    /**
     * 更新物流公司渠道
     * @return string
     */
    public function carrier(): string
    {
        CarrierLib::init()->getCarrier(true);
        return "success";
    }

    /**
     * 更新组织架构
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function organization(): string
    {
        // 需要模拟命令行，所以设置参数
        $_SERVER['argv'] = [];

        $input  = new Input();
        $output = new Output();

        $userCli    = new UserCli($input, $output);
        $accountCli = new AccountCli($input, $output);
        $sysCli     = new SystemCli($input, $output);

        $userCli->user();
        echo "user cache updated!\n";

        $userCli->org();
        echo "organization cache updated!\n";

        $userCli->orgUser();
        echo "organization user cache updated!\n";

        $accountCli->platformAccount();
        echo "platform account cache updated!\n";

        ToolsLib::getInstance()->getLevel1SellersMap(true);
        echo "level1 seller map cache updated\n";

        ToolsLib::getInstance()->getBusinessOrgTree(true);
        echo "bussiness organization tree cache updated\n";

        $sysCli->orderstatus();

        $this->ipWhiteList();
        echo "ip white list success\n";

        return "success";
    }
}
