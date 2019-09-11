<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    kevin
 */

namespace app\count\controller;
use think\Controller;
use think\console\Input;
use think\console\Output;
use app\count\library\OrgLib;
use app\count\model\ExpendType;
use app\common\library\ToolsLib;
use app\common\command\cli\UserCli;
use app\common\command\cli\SystemCli;
use app\common\command\cli\AccountCli;

/**
 * 测试控制器
 * Class Index
 * @package app\count\controller
 */
class Test extends Controller
{
//    public function _initialize()
//    {
//        $params = array_merge(input('get.'), input('post.'));
//        $params['test_key'] = $params['test_key'] ?? '';
//
//        // todo: 创建测试密钥
//        $time = date('Y-m-d H');
//        $key = md5(md5($time));
//
//        // todo: 显示测试密钥
//        if (isset($params['show_key']) && $params['show_key']) var_dump($key);
//
//        //todo:需要测试密钥, 才可以运行方法
//        if ($params['test_key'] != $key) abort(401, 'forbidden, you have no permission to access!');
//    }


    /**
     * 这个系统，使用的缓存太多了，用户，账号，组织架构等等，都是从erp 缓存过来，有时可能需要手动更新 cache
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-10-26 10:41:17
     */
    public function updateCache()
    {
        echo "start update cache\n";

        // 需要模拟命令行，所以设置参数
        $_SERVER['argv'] = [];
        $input = new Input();
        $output = new Output();

        $userCli = new UserCli($input, $output);
        $accountCli = new AccountCli($input, $output);
        $sysCli = new SystemCli($input, $output);

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
        echo "order status cache updated!\n";

        $sysCli->rate();
        echo "rate cache updated!\n";

        ToolsLib::getInstance()->getAllOrgParentNameMap(true);
        echo "organization full name mapping updated!\n";

        echo "bingo\n";
        exit;
    }


    public function orgTest()
    {
        $method = $_REQUEST['method'] ?? '';
        if (!$method) die('方法不存在');

        echo '<pre>';var_dump(OrgLib::getInstance()->$method());echo '</pre>';
        exit;
    }


    public function addExpendType()
    {
        $model = new ExpendType();

        $data = [
            '账号',
            '店铺对接ERP',
            '联盟佣金',
            '刷单费',
            '入驻费',
            '店铺装修费',
            '第三方平台使用（店小秘）',
            '其他',
            '账号',
            '店租',
            '亚马逊物流库存仓储费',
            '广告费',
            '亚马逊物流弃置费用',
            '优惠券',
            '亚马逊退货库存-客户退货',
            '第三方平台使用费（赛合）',
            '其他',
            '账号',
            '市场订阅费',
            '履行服务费',
            '广告费',
            '索赔',
            '其他',
            '账号',
            '业务员',
            'location',
            '仓库',
            '销售标签',
            '店租',
            '广告费',
            '刊登费',
            '物品刊登费',
            '副标题功能费',
            '粗體字刊登費用',
            '特大圖片瀏覽費用',
            '圖片展示優惠套餐',
            '超大圖片費用',
            '特別天數的費用',
            '折扣',
            '不公開出價者刊登費',
            '退款保障方案',
            '第三方平台使用费(盘古)',
            '其他',
            '账号',
            '索赔',
            '罚款',
            '其他',
            '账号',
            '店租',
            '广告费',
            '其他',
            '账号',
            '店租',
            '罚款',
            '促销返点',
            '广告费',
            '其他',
            '账号',
            '店租',
            '违规罚款',
            '侵权及不合适罚款',
            '订单延迟履行及虚假单号',
            '取消订单罚款及未履行订单',
            'WE延时订单罚款及扣款订单',
            '侵权冻结账号罚款',
            '保证金',
            '侵权和解金和律师费',
            '其他',
            '补贴',
            '赞助产品费',
            '增值税',
            '所得税',
        ];

        $data = array_unique($data);

        echo count($data);
        foreach ($data as $value)
        {
            $count = $model->where(['type_name' => $value])->count();
            if ($count == 0)
            {
                $add_data = [
                    'type_name' => $value,
                ];
                $model->insert($add_data);
            }
        }

        echo "\nbingo!\n";
    }

    public function addExpendType2()
    {
        $model = new ExpendType();

        $data = [
            'ebay' => [
                '账号',
                '业务员',
                'location',
                '仓库',
                '销售标签',
                '店租',
                '广告费',
                '刊登费',
                '物品刊登费',
                '副标题功能费',
                '粗體字刊登費用',
                '特大圖片瀏覽費用',
                '圖片展示優惠套餐',
                '超大圖片費用',
                '特別天數的費用',
                '折扣',
                '不公開出價者刊登費',
                '退款保障方案',
                '第三方平台使用费(盘古)',
                '其他',
            ],
            'aliexpress' => [
                '账号',
                '店铺对接ERP',
                '联盟佣金',
                '刷单费',
                '入驻费',
                '店铺装修费',
                '第三方平台使用（店小秘）',
                '其他',
            ],
            'amazon' => [
                '账号',
                '店租',
                '亚马逊物流库存仓储费',
                '广告费',
                '亚马逊物流弃置费用',
                '优惠券',
                '亚马逊退货库存-客户退货',
                '第三方平台使用费（赛合）',
                '其他',
            ],
            'cdiscount' => [
                '账号',
                '市场订阅费',
                '履行服务费',
                '广告费',
                '索赔',
                '其他',
            ],
            'lazada' => [
                '账号',
                '索赔',
                '罚款',
                '补贴',
                '赞助产品费',
                '增值税',
                '所得税',
                '其他',
            ],
            'manomano' => [
                '账号',
                '店租',
                '广告费',
                '其他',
            ],
            'priceminister' => [
                '账号',
                '店租',
                '罚款',
                '促销返点',
                '广告费',
                '其他',
            ],
            'wish' => [
                '账号',
                '店租',
                '违规罚款',
                '侵权及不合适罚款',
                '订单延迟履行及虚假单号',
                '取消订单罚款及未履行订单',
                'WE延时订单罚款及扣款订单',
                '侵权冻结账号罚款',
                '保证金',
                '侵权和解金和律师费',
                '其他',
            ],
        ];

        foreach ($data as $key => $value)
        {
            foreach ($value as $v)
            {
                $count = $model->where(['type_name' => $v, 'platform' => $key])->count();
                if ($count == 0)
                {
                    $add_data = [
                        'type_name' => $v,
                        'platform' => $key
                    ];
                    $model->insert($add_data);
                }
            }
            
        }

        echo "\nbingo!\n";
    }


    public function testRedis()
    {
        $data = ToolsLib::getInstance()->getAllAccounts(3);
        var_dump($data);
    }
}