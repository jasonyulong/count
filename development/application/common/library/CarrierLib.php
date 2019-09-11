<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    mina
 */

namespace app\common\library;

use think\Config;
use think\cache\driver\Redis;
use app\common\model\Carrier;
use app\common\model\CarrierCompany;

class CarrierLib
{
    /**
     * @desc  是否实例化
     * @var   boolen
     */
    private static $instance;

    /**
     * @desc  redis对象
     * @var   object
     */
    private $redis;

    /**
     * CarrierLib constructor.
     */
    private function __construct()
    {
        $this->redis = new Redis(Config::get('redis'));
    }

    private function __clone()
    {
    }

    /**
     * @desc   静态实例化
     * @author mina
     * @param  void
     * @return object
     */
    public static function init()
    {
        if (!self::$instance instanceof CarrierLib) {
            self::$instance = new CarrierLib();
        }
        return self::$instance;
    }

    /**
     * 查询所有运输渠道公司
     * @param bool $has_update 是否更新缓存
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCarrierCompany($has_update = false)
    {
        $rediskey = Config::get('redis.carrier_company');
        $data     = $this->redis->get($rediskey);
        if (empty($data) || $has_update === true) {
            $companyModel = new CarrierCompany();
            $data         = $companyModel->field($companyModel->field)->select();
            $company      = [];
            foreach ($data as $key => $value) {
                $company[$value['id']] = $value->toArray();
            }
            $data = json_encode($company);
            $this->redis->set($rediskey, $data, 1800);
        }
        return json_decode($data, true);
    }

    /**
     * 查询所有运输渠道
     * @param bool $has_update
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCarrier($has_update = false)
    {
        $key  = Config::get('redis.carrier');
        $data = $this->redis->get($key);
        if (empty($data) || $has_update === true) {
            $model       = new Carrier();
            $data        = $model->field($model->field)->where(['status' => 1])->select();
            $companyList = $this->getCarrierCompany();

            $carrier = [];
            foreach ($data as $key => $value) {
                $value['company']        = $companyList[$value['CompanyName']]['sup_abbr'] ?? '';
                $carrier[$value['name']] = $value;
            }
            $data = json_encode($carrier);
            $this->redis->set($key, $data, 1800);
        }
        return json_decode($data, true);
    }
}
