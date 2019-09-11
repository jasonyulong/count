<?php

namespace app\count\library\order;

use think\Db;
use think\Model;
use think\Config;
use app\count\model\Order;
use app\count\model\OrderSales;
use app\common\library\ToolsLib;
use app\count\model\OrderDetail;
use app\count\model\OrderSeller;
use app\count\model\OrderStatus;
use app\count\model\OrderSellerAvg;
use app\count\library\sku\NewSkuLib;
use app\count\model\OrderAccountAvg;
use app\count\model\OrderStatusType;
use app\count\model\OrderSellerStore;
use app\count\model\OrderSellerLocation;

class OrderLib
{
    private static $instance = null;

    public function __construct()
    {
        $this->orderModel = new Order();
        $this->orderDetailModel = new OrderDetail();
        $this->orderStatusModel = new OrderStatus();
        $this->orderSalesModel = new OrderSales();
        $this->orderSellerModel = new OrderSeller();
        $this->orderStatusTypeModel = new OrderStatusType();
        $this->orderSellerStoreModel = new OrderSellerStore();
        $this->orderSellerLocationModel = new OrderSellerLocation();
    }

    /**
     * single pattern
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-09-12 12:02:35
     */
    public static function getInstance(): OrderLib
    {
        if (!static::$instance) {
            static::$instance = new OrderLib();
        }
        return static::$instance;
    }

    /**
     * 获取订单类型配置
     * @author lamkakyun
     * @date 2018-12-12 13:57:44
     * @return array
     */
    public function getOrderTypeConf()
    {
        return Config::get('site.ordertype');
    }

    /**
     * 获取订单平台配置
     * @author lamkakyun
     * @date 2018-12-12 14:01:30
     * @return array
     */
    public function getOrderPlatformConf()
    {
        return Config::get('site.platforms');
    }


    /**
     * 获取订单状态配置
     * @author lamkakyun
     * @date 2018-12-12 14:02:55
     * @return array
     */
    public function getOrderStatusConf()
    {
        return Config::get('site.orderstatus');
    }


    /**
     * 获取订单字段配置
     * @author lamkakyun
     * @date 2018-12-12 14:20:23
     * @return array
     */
    public function getOrderFieldsConf()
    {
        return Config::get('site.order_fields');
    }

    /**
     * 排除订单费用字段
     * @return mixed
     */
    public function getOrderFeeFieldsConf()
    {
        return Config::get('site.order_fee_fields');
    }

    /**
     * sku费用
     * @return mixed
     */
    public function getSkuFieldsConf()
    {
        return Config::get('site.sku_fields');
    }


    /**
     * 根据条件，获取订单状态的平台分组统计数据
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-09-12 02:07:49
     */
    public function getStatusTotalInfo($where)
    {
        $total = $this->orderModel->where($where)->count();

        if (IS_CLI) {
            echo json_encode($where) . ':' . $total . PHP_EOL;
        }

        $total_platform_group = $total_platform_group_id = [];
        if ($total) {
            $_total_platform_group = $this->orderModel->field('platform, platform_account,platform_account_id, COUNT(*) AS account_total')->where($where)->group('platform_account_id')->select()->toArray();
            $total_platform_group = [];
            foreach ($_total_platform_group as $value) {
                $total_platform_group[trim($value['platform_account'])] = $value;
                $total_platform_group_id[$value['platform_account_id']] = $value;
            }
        }
        return ['total' => $total, 'total_account_group' => $total_platform_group, 'total_account_id_group' => $total_platform_group_id];
    }


    /**
     * 根据条件，获取订单类型的平台分组统计数据
     * date, order_type, platform
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-11-05 03:46:15
     */
    public function getOrderTypeTotalInfo($where)
    {
        $group_by = 'type, platform';
        // 这一天的 不同平台和订单类型 的 总数, 需要根据两个字段，分组
        $_total_platform_group = $this->orderModel->field('platform, type, COUNT(*) AS order_count')->where($where)->group($group_by)->select()->toArray();

        $total_platform_group = [];
        foreach ($_total_platform_group as $value) {
            // 因为是按照 platform 和type 分组的，所以 这个 key 是唯一的
            $_tmp_key = "{$value['platform']}_{$value['type']}";
            $total_platform_group[$_tmp_key] = $value;
        }

        return $total_platform_group;
    }


    /**
     * 获取 根据 订单类型分组的 统计信息
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-11-05 06:29:19
     */
    public function getOrderTypeGroupInfo($where)
    {
        $group_by = 'type';
        $_total_platform_group = $this->orderStatusTypeModel->field('type, SUM(totals) AS sum_totals, SUM(noships) AS sum_noships, SUM(ships) as sum_ships, SUM(overs) as sum_overs, SUM(refunds) as sum_refunds')->where($where)->group($group_by)->select()->toArray();

        $order_type_list = ToolsLib::getInstance()->getOrderTypeList();
        $tmp = $_total_platform_group;

        $_total_platform_group = [];
        foreach ($tmp as $key => $value) {
            $value['type_name'] = $order_type_list[$value['type']];
            $_total_platform_group[$value['type']] = $value;
        }

        return $_total_platform_group;
    }


    /**
     * 根据条件，获取 平台账号的 销售统计的信息
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-09-14 10:03:17
     */
    public function getAccountSaleTotalInfo($where)
    {
        $total = $this->orderModel->where($where)->count();

        $total_account_group = [];
        if ($total) {
            $tmp = $this->orderModel->field('platform_account, COUNT(*) AS account_total, SUM(total) as sale_total')->where($where)->group('platform_account')->select();

            $total_account_group = [];
            foreach ($tmp as $value) {
                $total_account_group[$value['platform_account']] = $value->toArray();
            }
        }

        return ['total' => $total, 'total_account_group' => $total_account_group];
    }


    /**
     * 根据条件，获取 SKU的 销售统计的信息(无需分组)
     * @author lamkakyun
     * @date 2019-02-18 14:00:10
     * @return void
     */
    public function getSkuSaleTotalInfo($where)
    {
        // $total = $this->orderDetailModel->alias('od')->join('erp_order o','od.order_id = o.id')->where($where)->count();
        $sku_total_data = $this->orderDetailModel->alias('od')->join('erp_order o', 'od.order_id = o.id')->field("SUM(od.nums) sku_nums_total")->where($where)->find()->toArray();

        $sku_type_num = $this->orderDetailModel->alias('od')->join('erp_order o', 'od.order_id = o.id')->field('DISTINCT sku')->where($where)->count();


        $order_total_data = $this->orderModel->alias('o')->field('SUM(o.cost) cost_total, SUM(total) sale_total')->where($where)->find()->toArray();

        $data = $sku_total_data + $order_total_data;
        $data['sku_type_num'] = $sku_type_num;


        return $data;
    }


    /**
     * 根据条件，获取 SKU的 销售统计的信息
     * @author lamkakyun
     * @date 2019-02-18 14:00:10
     * @return void
     */
    public function getSkuSaleTotalInfo2($where)
    {
        $total = $this->orderDetailModel->alias('od')->join('erp_order o', 'od.order_id = o.id')->where($where)->count();

        $group_by = "od.sku";
        $fields = "{$group_by}, od.parent, od.sku_combine, od.thumb, od.name, SUM(od.nums) sku_nums_total";

        $total_group = [];
        if ($total) {
            $tmp = $this->orderDetailModel->alias('od')->join('erp_order o', 'od.order_id = o.id')->field($fields)->where($where)->group($group_by)->select()->toArray();

            // TODO: 复合SKU，获取下面的SKU 的信息，合并到一起
            $combine_sku_orders = array_filter($tmp, function ($val) {
                return $val['sku_combine'] == 1;
            });
            $combine_sku_maps = NewSkuLib::getInstance()->getCombineSkuMap(array_column($combine_sku_orders, 'sku'));

            $all_sub_skus = [];
            foreach ($combine_sku_maps as $value) {
                foreach ($value as $v) {
                    $_tmp_arr = explode('*', $v);
                    $all_sub_skus[] = trim($_tmp_arr[0]);
                }
            }

            $all_sub_skus_info = NewSkuLib::getInstance()->getSkuInfo($all_sub_skus);

            $combine_single_sku_orders = [];
            foreach ($combine_sku_orders as $value) {
                if (!isset($combine_sku_maps[strtoupper(trim($value['sku']))])) continue;
                $_tmp_sub_sku_arr = $combine_sku_maps[strtoupper(trim($value['sku']))];
                foreach ($_tmp_sub_sku_arr as $_sku_with_num) {
                    $_tmp_arr = explode('*', $_sku_with_num);

                    if (count($_tmp_arr) < 2) continue;

                    $_tmp_sku = strtoupper($_tmp_arr[0]);
                    $_tmp_num = intval($_tmp_arr[1]);

                    if (!isset($_tmp_sku_info)) continue;
                    $_tmp_sku_info = $all_sub_skus_info[$_tmp_sku];

                    $combine_single_sku_orders[] = [
                        'sku'            => $_tmp_sku,
                        'parent'         => $_tmp_sku_info['goods_parent'],
                        'sku_combine'    => 0,
                        'thumb'          => 'http://erp.spocoo.com/images/small/' . $_tmp_sku_info['goods_iamge'],
                        'name'           => $_tmp_sku_info['goods_name'],
                        'sku_nums_total' => $value['sku_nums_total'] * $_tmp_num,
                    ];
                }
            }
            // DONE
            // echo '<pre>';var_dump($combine_single_sku_orders);echo '</pre>';
            // exit;

            $single_sku_orders = array_filter($tmp, function ($val) {
                return $val['sku_combine'] == 0;
            });

            $tmp = array_merge($single_sku_orders, $combine_single_sku_orders, $combine_sku_orders);
            $total = count($tmp);

            $total_group = [];
            foreach ($tmp as $value) {
                // 唯一性
                $_tmp_key = strtoupper(trim($value['sku']));

                if (isset($total_group[$_tmp_key])) $total_group[$_tmp_key]['sku_nums_total'] += $value['sku_nums_total'];
                else $total_group[$_tmp_key] = $value;
            }
        }

        return ['total' => $total, 'total_group' => $total_group];
    }


    /**
     * 根据条件，获取 SKU 分类的 销售统计的信息(无需分组)
     * @author lamkakyun
     * @date 2019-02-18 14:00:10
     * @return void
     */
    public function getSkuCategorySaleTotalInfo($where)
    {
        $total = $this->orderDetailModel->alias('od')->join('erp_order o', 'od.order_id = o.id')->where($where)->count();

        $group_by = "od.sku, od.category_id, od.category_child_id";
        $fields = "{$group_by}, od.parent, od.sku_combine, od.thumb, od.name, SUM(od.nums) sku_nums_total";

        $total_group = [];
        if ($total) {
            $tmp = $this->orderDetailModel->alias('od')->join('erp_order o', 'od.order_id = o.id')->field($fields)->where($where)->group($group_by)->select()->toArray();

            // TODO: 复合SKU，获取下面的SKU 的信息，合并到一起
            $combine_sku_orders = array_filter($tmp, function ($val) {
                return $val['sku_combine'] == 1;
            });
            $combine_sku_maps = NewSkuLib::getInstance()->getCombineSkuMap(array_column($combine_sku_orders, 'sku'));

            $all_sub_skus = [];
            foreach ($combine_sku_maps as $value) {
                foreach ($value as $v) {
                    $_tmp_arr = explode('*', $v);
                    $all_sub_skus[] = trim($_tmp_arr[0]);
                }
            }

            $all_sub_skus_info = NewSkuLib::getInstance()->getSkuInfo($all_sub_skus);

            $combine_single_sku_orders = [];
            foreach ($combine_sku_orders as $value) {
                if (!isset($combine_sku_maps[strtoupper(trim($value['sku']))])) continue;
                $_tmp_sub_sku_arr = $combine_sku_maps[strtoupper(trim($value['sku']))];
                foreach ($_tmp_sub_sku_arr as $_sku_with_num) {
                    $_tmp_arr = explode('*', $_sku_with_num);

                    if (count($_tmp_arr) < 2) continue;

                    $_tmp_sku = strtoupper($_tmp_arr[0]);
                    $_tmp_num = intval($_tmp_arr[1]);

                    if (!isset($_tmp_sku_info)) continue;
                    $_tmp_sku_info = $all_sub_skus_info[$_tmp_sku];

                    $combine_single_sku_orders[] = [
                        'sku'               => $_tmp_sku,
                        'category_id'       => $value['category_id'],
                        'category_child_id' => $value['category_child_id'],
                        'parent'            => $_tmp_sku_info['goods_parent'],
                        'sku_combine'       => 0,
                        'thumb'             => 'http://erp.spocoo.com/images/small/' . $_tmp_sku_info['goods_iamge'],
                        'name'              => $_tmp_sku_info['goods_name'],
                        'sku_nums_total'    => $value['sku_nums_total'] * $_tmp_num,
                    ];
                }
            }
            // DONE
            // echo '<pre>';var_dump($combine_single_sku_orders);echo '</pre>';
            // exit;

            $single_sku_orders = array_filter($tmp, function ($val) {
                return $val['sku_combine'] == 0;
            });

            $tmp = array_merge($single_sku_orders, $combine_single_sku_orders, $combine_sku_orders);
            $total = count($tmp);

            $total_group = [];
            foreach ($tmp as $value) {
                // 唯一性
                $_tmp_key = strtoupper(trim($value['sku'])) . '___' . intval($value['category_id']) . '___' . intval($value['category_child_id']);

                if (isset($total_group[$_tmp_key])) $total_group[$_tmp_key]['sku_nums_total'] += $value['sku_nums_total'];
                else $total_group[$_tmp_key] = $value;
            }
        }

        return ['total' => $total, 'total_group' => $total_group];
    }


    /**
     * 根据条件，获取 SKU 销售员的 销售统计的信息(无需分组)
     * @author lamkakyun
     * @date 2019-02-18 14:00:10
     * @return void
     */
    public function getSkuSellerSaleTotalInfo($where)
    {
        $where['o.sales_user'] = ['NEQ', ''];

        $total = $this->orderDetailModel->alias('od')->join('erp_order o', 'od.order_id = o.id')->where($where)->count();

        $group_by = "od.sku, o.sales_user";
        $fields = "{$group_by}, o.sales_branch_id, od.parent, od.sku_combine, od.thumb, od.name, SUM(od.nums) sku_nums_total";

        $total_group = [];
        if ($total) {
            $tmp = $this->orderDetailModel->alias('od')->join('erp_order o', 'od.order_id = o.id')->field($fields)->where($where)->group($group_by)->select()->toArray();

            // TODO: 复合SKU，获取下面的SKU 的信息，合并到一起
            $combine_sku_orders = array_filter($tmp, function ($val) {
                return $val['sku_combine'] == 1;
            });
            $combine_sku_maps = NewSkuLib::getInstance()->getCombineSkuMap(array_column($combine_sku_orders, 'sku'));

            $all_sub_skus = [];
            foreach ($combine_sku_maps as $value) {
                foreach ($value as $v) {
                    $_tmp_arr = explode('*', $v);
                    $all_sub_skus[] = trim($_tmp_arr[0]);
                }
            }

            $all_sub_skus_info = NewSkuLib::getInstance()->getSkuInfo($all_sub_skus);

            $combine_single_sku_orders = [];
            foreach ($combine_sku_orders as $value) {
                if (!isset($combine_sku_maps[strtoupper(trim($value['sku']))])) continue;
                $_tmp_sub_sku_arr = $combine_sku_maps[strtoupper(trim($value['sku']))];
                foreach ($_tmp_sub_sku_arr as $_sku_with_num) {
                    $_tmp_arr = explode('*', $_sku_with_num);

                    if (count($_tmp_arr) < 2) continue;

                    $_tmp_sku = strtoupper($_tmp_arr[0]);
                    $_tmp_num = intval($_tmp_arr[1]);

                    if (!isset($_tmp_sku_info)) continue;
                    $_tmp_sku_info = $all_sub_skus_info[$_tmp_sku];

                    $combine_single_sku_orders[] = [
                        'sku'             => $_tmp_sku,
                        'sales_user'      => $value['sales_user'],
                        'sales_branch_id' => $value['sales_branch_id'],
                        'parent'          => $_tmp_sku_info['goods_parent'],
                        'sku_combine'     => 0,
                        'thumb'           => 'http://erp.spocoo.com/images/small/' . $_tmp_sku_info['goods_iamge'],
                        'name'            => $_tmp_sku_info['goods_name'],
                        'sku_nums_total'  => $value['sku_nums_total'] * $_tmp_num,
                    ];
                }
            }
            // DONE
            // echo '<pre>';var_dump($combine_single_sku_orders);echo '</pre>';
            // exit;

            $single_sku_orders = array_filter($tmp, function ($val) {
                return $val['sku_combine'] == 0;
            });

            $tmp = array_merge($single_sku_orders, $combine_single_sku_orders, $combine_sku_orders);
            $total = count($tmp);

            $total_group = [];
            foreach ($tmp as $value) {
                // 唯一性
                $_tmp_key = strtoupper(trim($value['sku'])) . '___' . $value['sales_user'];
                // $total_group[$_tmp_key] = $value->toArray();

                if (isset($total_group[$_tmp_key])) $total_group[$_tmp_key]['sku_nums_total'] += $value['sku_nums_total'];
                else $total_group[$_tmp_key] = $value;
            }
        }

        return ['total' => $total, 'total_group' => $total_group];
    }

    /**
     * 根据条件，获取 SKU 开发员 的 销售统计的信息(无需分组)
     * @author lamkakyun
     * @date 2019-02-18 14:00:10
     * @return void
     */
    public function getSkuDeveloperSaleTotalInfo($where)
    {
        $where['o.develop_user'] = ['NEQ', ''];

        $total = $this->orderDetailModel->alias('od')->join('erp_order o', 'od.order_id = o.id')->where($where)->count();

        $group_by = "od.sku, o.develop_user";
        $fields = "{$group_by}, od.parent, od.sku_combine, od.thumb, od.name, SUM(od.nums) sku_nums_total";

        $total_group = [];
        if ($total) {
            $tmp = $this->orderDetailModel->alias('od')->join('erp_order o', 'od.order_id = o.id')->field($fields)->where($where)->group($group_by)->select()->toArray();

            // TODO: 复合SKU，获取下面的SKU 的信息，合并到一起
            $combine_sku_orders = array_filter($tmp, function ($val) {
                return $val['sku_combine'] == 1;
            });
            $combine_sku_maps = NewSkuLib::getInstance()->getCombineSkuMap(array_column($combine_sku_orders, 'sku'));

            $all_sub_skus = [];
            foreach ($combine_sku_maps as $value) {
                foreach ($value as $v) {
                    $_tmp_arr = explode('*', $v);
                    $all_sub_skus[] = trim($_tmp_arr[0]);
                }
            }

            $all_sub_skus_info = NewSkuLib::getInstance()->getSkuInfo($all_sub_skus);

            $combine_single_sku_orders = [];
            foreach ($combine_sku_orders as $value) {
                if (!isset($combine_sku_maps[strtoupper(trim($value['sku']))])) continue;
                $_tmp_sub_sku_arr = $combine_sku_maps[strtoupper(trim($value['sku']))];
                foreach ($_tmp_sub_sku_arr as $_sku_with_num) {
                    $_tmp_arr = explode('*', $_sku_with_num);

                    if (count($_tmp_arr) < 2) continue;

                    $_tmp_sku = strtoupper($_tmp_arr[0]);
                    $_tmp_num = intval($_tmp_arr[1]);

                    if (!isset($_tmp_sku_info)) continue;
                    $_tmp_sku_info = $all_sub_skus_info[$_tmp_sku];

                    $combine_single_sku_orders[] = [
                        'sku'            => $_tmp_sku,
                        'develop_user'   => $value['develop_user'],
                        'parent'         => $_tmp_sku_info['goods_parent'],
                        'sku_combine'    => 0,
                        'thumb'          => 'http://erp.spocoo.com/images/small/' . $_tmp_sku_info['goods_iamge'],
                        'name'           => $_tmp_sku_info['goods_name'],
                        'sku_nums_total' => $value['sku_nums_total'] * $_tmp_num,
                    ];
                }
            }
            // DONE
            // echo '<pre>';var_dump($combine_single_sku_orders);echo '</pre>';
            // exit;

            $single_sku_orders = array_filter($tmp, function ($val) {
                return $val['sku_combine'] == 0;
            });

            $tmp = array_merge($single_sku_orders, $combine_single_sku_orders, $combine_sku_orders);
            $total = count($tmp);

            $total_group = [];
            foreach ($tmp as $value) {
                // 唯一性
                $_tmp_key = strtoupper(trim($value['sku'])) . '___' . $value['develop_user'];
                // $total_group[$_tmp_key] = $value->toArray();

                if (isset($total_group[$_tmp_key])) $total_group[$_tmp_key]['sku_nums_total'] += $value['sku_nums_total'];
                else $total_group[$_tmp_key] = $value;
            }
        }

        return ['total' => $total, 'total_group' => $total_group];
    }

    /**
     * 根据条件，获取 SKU 国家 的 销售统计的信息(无需分组)
     * @author lamkakyun
     * @date 2019-02-18 14:00:10
     * @return void
     */
    public function getSkuCountrySaleTotalInfo($where)
    {
        $where['o.couny'] = ['NEQ', ''];

        $total = $this->orderDetailModel->alias('od')->join('erp_order o', 'od.order_id = o.id')->where($where)->count();

        $group_by = "od.sku, o.couny";
        $fields = "{$group_by}, od.parent, od.sku_combine, od.thumb, od.name, SUM(od.nums) sku_nums_total";

        $total_group = [];
        if ($total) {
            $tmp = $this->orderDetailModel->alias('od')->join('erp_order o', 'od.order_id = o.id')->field($fields)->where($where)->group($group_by)->select()->toArray();

            // TODO: 复合SKU，获取下面的SKU 的信息，合并到一起
            $combine_sku_orders = array_filter($tmp, function ($val) {
                return $val['sku_combine'] == 1;
            });
            $combine_sku_maps = NewSkuLib::getInstance()->getCombineSkuMap(array_column($combine_sku_orders, 'sku'));

            $all_sub_skus = [];
            foreach ($combine_sku_maps as $value) {
                foreach ($value as $v) {
                    $_tmp_arr = explode('*', $v);
                    $all_sub_skus[] = trim($_tmp_arr[0]);
                }
            }

            $all_sub_skus_info = NewSkuLib::getInstance()->getSkuInfo($all_sub_skus);

            $combine_single_sku_orders = [];
            foreach ($combine_sku_orders as $value) {
                if (!isset($combine_sku_maps[strtoupper(trim($value['sku']))])) continue;
                $_tmp_sub_sku_arr = $combine_sku_maps[strtoupper(trim($value['sku']))];
                foreach ($_tmp_sub_sku_arr as $_sku_with_num) {
                    $_tmp_arr = explode('*', $_sku_with_num);

                    if (count($_tmp_arr) < 2) continue;

                    $_tmp_sku = strtoupper($_tmp_arr[0]);
                    $_tmp_num = intval($_tmp_arr[1]);

                    if (!isset($_tmp_sku_info)) continue;
                    $_tmp_sku_info = $all_sub_skus_info[$_tmp_sku];

                    $combine_single_sku_orders[] = [
                        'sku'            => $_tmp_sku,
                        'couny'          => $value['couny'],
                        'parent'         => $_tmp_sku_info['goods_parent'],
                        'sku_combine'    => 0,
                        'thumb'          => 'http://erp.spocoo.com/images/small/' . $_tmp_sku_info['goods_iamge'],
                        'name'           => $_tmp_sku_info['goods_name'],
                        'sku_nums_total' => $value['sku_nums_total'] * $_tmp_num,
                    ];
                }
            }
            // DONE
            // echo '<pre>';var_dump($combine_single_sku_orders);echo '</pre>';
            // exit;

            $single_sku_orders = array_filter($tmp, function ($val) {
                return $val['sku_combine'] == 0;
            });

            $tmp = array_merge($single_sku_orders, $combine_single_sku_orders, $combine_sku_orders);
            $total = count($tmp);

            $total_group = [];
            foreach ($tmp as $value) {
                // 唯一性
                $_tmp_key = strtoupper(trim($value['sku'])) . '___' . $value['couny'];
                // $total_group[$_tmp_key] = $value->toArray();

                if (isset($total_group[$_tmp_key])) $total_group[$_tmp_key]['sku_nums_total'] += $value['sku_nums_total'];
                else $total_group[$_tmp_key] = $value;
            }
        }

        return ['total' => $total, 'total_group' => $total_group];
    }

    /**
     * 根据条件，获取 SKU 仓库 的 销售统计的信息(无需分组)
     * @author lamkakyun
     * @date 2019-02-18 14:00:10
     * @return void
     */
    public function getSkuStoreSaleTotalInfo($where)
    {
        $total = $this->orderDetailModel->alias('od')->join('erp_order o', 'od.order_id = o.id')->where($where)->count();

        $group_by = "od.sku, o.store_id";
        $fields = "{$group_by}, od.parent, od.sku_combine, od.thumb, od.name, SUM(od.nums) sku_nums_total";

        $total_group = [];
        if ($total) {
            $tmp = $this->orderDetailModel->alias('od')->join('erp_order o', 'od.order_id = o.id')->field($fields)->where($where)->group($group_by)->select()->toArray();

            // TODO: 复合SKU，获取下面的SKU 的信息，合并到一起
            $combine_sku_orders = array_filter($tmp, function ($val) {
                return $val['sku_combine'] == 1;
            });
            $combine_sku_maps = NewSkuLib::getInstance()->getCombineSkuMap(array_column($combine_sku_orders, 'sku'));

            $all_sub_skus = [];
            foreach ($combine_sku_maps as $value) {
                foreach ($value as $v) {
                    $_tmp_arr = explode('*', $v);
                    $all_sub_skus[] = trim($_tmp_arr[0]);
                }
            }

            $all_sub_skus_info = NewSkuLib::getInstance()->getSkuInfo($all_sub_skus);

            $combine_single_sku_orders = [];
            foreach ($combine_sku_orders as $value) {
                if (!isset($combine_sku_maps[strtoupper(trim($value['sku']))])) continue;
                $_tmp_sub_sku_arr = $combine_sku_maps[strtoupper(trim($value['sku']))];
                foreach ($_tmp_sub_sku_arr as $_sku_with_num) {
                    $_tmp_arr = explode('*', $_sku_with_num);

                    if (count($_tmp_arr) < 2) continue;

                    $_tmp_sku = strtoupper($_tmp_arr[0]);
                    $_tmp_num = intval($_tmp_arr[1]);

                    if (!isset($_tmp_sku_info)) continue;
                    $_tmp_sku_info = $all_sub_skus_info[$_tmp_sku];

                    $combine_single_sku_orders[] = [
                        'sku'            => $_tmp_sku,
                        'store_id'       => $value['store_id'],
                        'parent'         => $_tmp_sku_info['goods_parent'],
                        'sku_combine'    => 0,
                        'thumb'          => 'http://erp.spocoo.com/images/small/' . $_tmp_sku_info['goods_iamge'],
                        'name'           => $_tmp_sku_info['goods_name'],
                        'sku_nums_total' => $value['sku_nums_total'] * $_tmp_num,
                    ];
                }
            }
            // DONE
            // echo '<pre>';var_dump($combine_single_sku_orders);echo '</pre>';
            // exit;

            $single_sku_orders = array_filter($tmp, function ($val) {
                return $val['sku_combine'] == 0;
            });

            $tmp = array_merge($single_sku_orders, $combine_single_sku_orders, $combine_sku_orders);
            $total = count($tmp);

            $total_group = [];
            foreach ($tmp as $value) {
                // 唯一性
                $_tmp_key = strtoupper(trim($value['sku'])) . '___' . $value['store_id'];
                // $total_group[$_tmp_key] = $value->toArray();

                if (isset($total_group[$_tmp_key])) $total_group[$_tmp_key]['sku_nums_total'] += $value['sku_nums_total'];
                else $total_group[$_tmp_key] = $value;
            }
        }

        return ['total' => $total, 'total_group' => $total_group];
    }

    /**
     * 根据条件，获取 SKU 分类的 销售统计的信息(无需分组)
     * @author lamkakyun
     * @date 2019-02-18 14:00:10
     * @return void
     */
    public function getSkuAccountSaleTotalInfo($where)
    {
        $total = $this->orderDetailModel->alias('od')->join('erp_order o', 'od.order_id = o.id')->where($where)->count();

        $group_by = "od.sku, od.platform_account";
        $fields = "{$group_by}, od.platform, od.parent, od.sku_combine, od.thumb, od.name, SUM(od.nums) sku_nums_total";

        $total_group = [];
        if ($total) {
            $tmp = $this->orderDetailModel->alias('od')->join('erp_order o', 'od.order_id = o.id')->field($fields)->where($where)->group($group_by)->select()->toArray();

            // TODO: 复合SKU，获取下面的SKU 的信息，合并到一起
            $combine_sku_orders = array_filter($tmp, function ($val) {
                return $val['sku_combine'] == 1;
            });
            $combine_sku_maps = NewSkuLib::getInstance()->getCombineSkuMap(array_column($combine_sku_orders, 'sku'));

            $all_sub_skus = [];
            foreach ($combine_sku_maps as $value) {
                foreach ($value as $v) {
                    $_tmp_arr = explode('*', $v);
                    $all_sub_skus[] = trim($_tmp_arr[0]);
                }
            }

            $all_sub_skus_info = NewSkuLib::getInstance()->getSkuInfo($all_sub_skus);

            $combine_single_sku_orders = [];
            foreach ($combine_sku_orders as $value) {
                if (!isset($combine_sku_maps[strtoupper(trim($value['sku']))])) continue;
                $_tmp_sub_sku_arr = $combine_sku_maps[strtoupper(trim($value['sku']))];
                foreach ($_tmp_sub_sku_arr as $_sku_with_num) {
                    $_tmp_arr = explode('*', $_sku_with_num);

                    if (count($_tmp_arr) < 2) continue;

                    $_tmp_sku = strtoupper($_tmp_arr[0]);
                    $_tmp_num = intval($_tmp_arr[1]);

                    if (!isset($_tmp_sku_info)) continue;
                    $_tmp_sku_info = $all_sub_skus_info[$_tmp_sku];

                    $combine_single_sku_orders[] = [
                        'sku'              => $_tmp_sku,
                        'platform_account' => $value['platform_account'],
                        'platform'         => $value['platform'],
                        'parent'           => $_tmp_sku_info['goods_parent'],
                        'sku_combine'      => 0,
                        'thumb'            => 'http://erp.spocoo.com/images/small/' . $_tmp_sku_info['goods_iamge'],
                        'name'             => $_tmp_sku_info['goods_name'],
                        'sku_nums_total'   => $value['sku_nums_total'] * $_tmp_num,
                    ];
                }
            }
            // DONE
            // echo '<pre>';var_dump($combine_single_sku_orders);echo '</pre>';
            // exit;

            $single_sku_orders = array_filter($tmp, function ($val) {
                return $val['sku_combine'] == 0;
            });

            $tmp = array_merge($single_sku_orders, $combine_single_sku_orders, $combine_sku_orders);
            $total = count($tmp);

            $total_group = [];
            foreach ($tmp as $value) {
                // 唯一性
                $_tmp_key = strtoupper(trim($value['sku'])) . '___' . $value['platform_account'];
                // $total_group[$_tmp_key] = $value->toArray();

                if (isset($total_group[$_tmp_key])) $total_group[$_tmp_key]['sku_nums_total'] += $value['sku_nums_total'];
                else $total_group[$_tmp_key] = $value;
            }
        }

        return ['total' => $total, 'total_group' => $total_group];
    }


    /**
     * 根据条件，获取 销售员的 销售统计的信息
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-09-18 04:34:29
     */
    public function getSellerSaleTotalInfo($where)
    {
        $total = $this->orderModel->where($where)->count();

        $total_account_group = [];
        if ($total) {
            $tmp = $this->orderModel->field('sales_user, sales_branch_id, COUNT(*) AS seller_total, SUM(total) as sale_total')->where($where)->group('sales_user, sales_branch_id')->select();

            $total_account_group = [];
            foreach ($tmp as $value) {
                // 因为，一个人可以属于多个组织架构的成员，所以  成员 + 组id 才是 唯一的
                $_tmp_key = trim($value['sales_user']) . '_' . intval($value['sales_branch_id']);

                $total_account_group[$_tmp_key] = $value->toArray();
            }
        }
        return ['total' => $total, 'total_account_group' => $total_account_group];
    }


    /**
     * 根据条件，获取 仓库 的 销售统计的信息
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-09-18 04:34:29
     */
    public function getStoreSaleTotalInfo($where)
    {
        $total = $this->orderModel->where($where)->count();
        $total_group = [];
        if ($total) {
            $tmp = $this->orderModel->field('store_id, platform_account_id, COUNT(*) AS seller_total, SUM(total) as sale_total')->where($where)->group('store_id, platform_account_id')->select();

            $total_group = [];
            foreach ($tmp as $value) {
                // account_storeid 才是唯一
                $_tmp_key = trim($value['platform_account_id']) . '_' . intval($value['store_id']);

                $total_group[$_tmp_key] = $value->toArray();
            }
        }
        return ['total' => $total, 'total_group' => $total_group];
    }


    /**
     * 根据条件，获取 发货地 的 销售统计的信息
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-09-18 04:34:29
     */
    public function getLocationSaleTotalInfo($where)
    {
        $total = $this->orderModel->where($where)->count();
        $total_group = [];
        if ($total) {
            $tmp = $this->orderModel->field('location, platform_account_id, COUNT(*) AS seller_total, SUM(total) as sale_total')->where($where)->group('location, platform_account_id')->select();

            $total_group = [];
            foreach ($tmp as $value) {
                // account_location 才是唯一
                $_tmp_key = trim($value['platform_account_id']) . '_' . $value['location'];

                $total_group[$_tmp_key] = $value->toArray();
            }
        }
        return ['total' => $total, 'total_group' => $total_group];
    }


    /**
     * 获取订单的统计的状态列表
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-09-19 09:36:05
     */
    public function getOrderStatusList($params, $type = 'date')
    {
        $where = $this->_handleQueryDate($params, []);

        if (isset($params['platform']) && !empty($params['platform'])) $where['platform'] = ['IN', $params['platform']];

        if (isset($params['platform_account']) && !empty($params['platform_account'])) $where['platform_account'] = ['IN', $params['platform_account']];

        $sort_arr = explode(',', $params['sort_field']);
        $order_by = implode(',', array_map(function ($val) use ($params) {
            return "{$val} {$params['sort']}";
        }, $sort_arr));

        $start = ($params['p'] - 1) * $params['ps'];

        $_group_by = '';
        $_fields = '';
        $default_field = 'SUM(totals) as sum_totals, SUM(noships) as sum_noships, SUM(ships) as sum_ships, SUM(overs) as sum_overs, SUM(refunds) as sum_refunds, SUM(recycles) as sum_recycles, SUM(recycles_system) as sum_recycles_system, SUM(resends) as sum_resends, SUM(returns) as sum_returns, sum(totals_ships) as sum_total_ship, SUM(totals-recycles-recycles_system) as sum_can_send';
        if ($type == 'date') {
            $_group_by = $params['checkDate'] == 'day' ? 'days, month, year' : 'month, year';
            $_fields = "{$_group_by}, {$default_field}";
        }

        if ($type == 'platform') {
            $_group_by = 'platform';
            $_fields = "{$_group_by}, {$default_field}";
        }

        // todo: 特殊：根据 账号 和日期分组
        if ($type == 'account_date') {
            $_group_by = $params['checkDate'] == 'day' ? 'days, month, year, platform_account' : 'month, year, platform_account';
            $_fields = "{$_group_by}, {$default_field}";
        }

        if ($type == 'platform_date') {
            $_group_by = $params['checkDate'] == 'day' ? 'days, month, year, platform' : 'month, year, platform';
            $_fields = "{$_group_by}, {$default_field}";
        }

        $ret_data = ['list' => [], 'count' => 0];
        if ($_group_by && $_fields) {
            $ret_data = $this->_getGroupByCountAndList($this->orderStatusModel, $where, $_group_by, $_fields, $start, $params['ps'], $order_by);
        }

        if (isset($params['debug']) && $params['debug'] == 'status_sql') var_dump($this->orderStatusModel->getLastSql());
        return $ret_data;
    }


    /**
     * 获取 订单销售额 报表 列表
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-09-19 04:13:01
     */
    public function getOrderSaleList($params, $type = 'date')
    {
        $where = $this->_handleQueryDate($params, []);
        if (!empty($_SESSION['truename'])) {
            $platform = ToolsLib::getInstance()->getCanViewPlatform($_SESSION['truename']);
            if ($platform) $where['platform'] = ['IN', $platform];
        }

        if (isset($params['platform']) && !empty($params['platform'])) $where['platform'] = ['IN', $params['platform']];
        if (isset($params['account']) && !empty($params['account'])) $where['platform_account'] = ['IN', $params['account']];

        $sort_arr = explode(',', $params['sort_field'] ?? '');
        $order_by = implode(',', array_map(function ($val) use ($params) {
            return "{$val} " . ($params['sort'] ?? '');
        }, $sort_arr));

        if ($params['checkDate'] == 'day') $range = range_day($params['scantime_end'], $params['scantime_start'], true);
        else $range = range_month($params['scandate_end'], $params['scandate_start']);

        if ($type == 'account') {
            $_group_by = 'platform_account,days, month, year'; // 4 个字段的唯一性
            if ($params['checkDate'] == 'month') $_group_by = 'platform_account, month, year';
            $_fields = "{$_group_by}, SUM(totals) as sum_totals, SUM(sales) as sum_sales";

            // echo '<pre>';var_dump($where);echo '</pre>';
            // exit;
            $ret_data = $this->_getGroupByCountAndList($this->orderSalesModel, $where, $_group_by, $_fields, false, false, $order_by);

            if (isset($params['debug']) && $params['debug'] == 'sql') {
                echo '<pre>';
                var_dump($this->orderSalesModel->getLastSql());
                echo '</pre>';
                exit;
            }

            // todo:重组数据 (就算没有数据 也要默认给 空数组)
            $ret_data_reshape = [];
            foreach ($ret_data['list'] as $value) {
                foreach ($range as $v) {
                    $ret_data_reshape[$value['platform_account']]['dates'][$v] = ['sum_totals' => '0', 'sum_sales' => '0.00'];
                }
            }

            foreach ($ret_data['list'] as $value) {
                if ($params['checkDate'] == 'month') $ret_data_reshape[$value['platform_account']]['dates']["{$value['year']}-{$value['month']}"] = $value;
                else $ret_data_reshape[$value['platform_account']]['dates']["{$value['year']}-{$value['month']}-{$value['days']}"] = $value;
            }
            $ret_data['list'] = $ret_data_reshape;
            $ret_data['count'] = count($ret_data_reshape);

            if (isset($params['debug']) && $params['debug'] == 'data') {
                echo '<pre>';
                var_dump($ret_data);
                echo '</pre>';
                exit;
            }

            return $ret_data;
        }

        if ($type == 'platform') {
            $_group_by = 'platform,days, month, year'; // 4 个字段的唯一性
            if ($params['checkDate'] == 'month') $_group_by = 'platform, month, year';
            $_fields = "{$_group_by}, SUM(totals) as sum_totals, SUM(sales) as sum_sales";

            $ret_data = $this->_getGroupByCountAndList($this->orderSalesModel, $where, $_group_by, $_fields, false, false, $order_by);

            // 重组数据 (就算没有数据 也要默认给 空数组)
            $ret_data_reshape = [];
            foreach ($ret_data['list'] as $value) {
                foreach ($range as $v) {
                    $ret_data_reshape[$value['platform']]['dates'][$v] = ['sum_totals' => '0', 'sum_sales' => '0.00'];
                }
            }
            foreach ($ret_data['list'] as $value) {
                if ($params['checkDate'] == 'month') $ret_data_reshape[$value['platform']]['dates']["{$value['year']}-{$value['month']}"] = $value;
                else $ret_data_reshape[$value['platform']]['dates']["{$value['year']}-{$value['month']}-{$value['days']}"] = $value;

            }
            $ret_data['list'] = $ret_data_reshape;
            $ret_data['count'] = count($ret_data_reshape);
            //            var_dump($ret_data);exit;
            return $ret_data;
        }

        if ($type == 'date') {
            $_group_by = $params['checkDate'] == 'day' ? 'days, month, year' : 'month, year';
            $_fields = "{$_group_by}, SUM(totals) as sum_totals, SUM(sales) as sum_sales, SUM(refunds_count) as sum_refunds_count, SUM(refunds) as sum_refunds, SUM(recycles) as sum_recycles, SUM(recycles_count) as sum_recycles_count";
            $ret_data = $this->_getGroupByCountAndList($this->orderSalesModel, $where, $_group_by, $_fields, false, false, $order_by);

            // todo：将 date 放到 key 的位置上
            $tmp = $ret_data['list'];
            $ret_data['list'] = [];
            foreach ($tmp as $key => $value) {
                $tmp_key = $params['checkDate'] == 'day' ? "{$value['year']}-{$value['month']}-{$value['days']}" : "{$value['year']}-{$value['month']}";
                $ret_data['list'][$tmp_key] = $value;
            }

            return $ret_data;
        }
    }


    /**
     * 获取 订单销售额 报表 列表 (仓库)
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-09-19 04:13:01
     */
    public function getOrderStoreSaleList($params)
    {
        // echo '<pre>';var_dump($params);echo '</pre>';
        // exit;
        $where = $this->_handleQueryDate($params, []);

        if (isset($params['account']) && !empty($params['account'])) $where['platform_account'] = ['IN', $params['account']];

        $start = ($params['p'] - 1) * $params['ps'];

        $sort_arr = explode(',', $params['sort_field']);
        $order_by = implode(',', array_map(function ($val) use ($params) {
            return "{$val} {$params['sort']}";
        }, $sort_arr));

        if ($params['checkDate'] == 'day') $range = range_day($params['scantime_end'], $params['scantime_start'], true);
        else $range = range_month($params['scandate_end'], $params['scandate_start']);

        $_group_by = 'platform_account_id, store_id,days, month, year';
        if ($params['checkDate'] == 'month') $_group_by = 'platform_account_id, store_id, month, year';


        $_fields = "{$_group_by}, SUM(totals) as sum_totals, SUM(sales) as sum_sales";

        $ret_data = $this->_getGroupByCountAndList($this->orderSellerStoreModel, $where, $_group_by, $_fields, $start, $params['ps'], $order_by);

        // echo '<pre>';var_dump($range,$ret_data);echo '</pre>';
        // exit;

        // todo:重组数据 (就算没有数据 也要默认给 空数组)
        $ret_data_reshape = [];
        foreach ($ret_data['list'] as $value) {
            foreach ($range as $v) {
                $ret_data_reshape[$value['store_id']]['dates'][$v] = ['sum_totals' => '0', 'sum_sales' => '0.00'];
            }
        }

        foreach ($ret_data['list'] as $value) {
            if ($params['checkDate'] == 'month') {
                $ret_data_reshape[$value['store_id']]['dates']["{$value['year']}-{$value['month']}"]['sum_totals'] += $value['sum_totals'];
                $ret_data_reshape[$value['store_id']]['dates']["{$value['year']}-{$value['month']}"]['sum_sales'] += $value['sum_sales'];
            } else {
                $ret_data_reshape[$value['store_id']]['dates']["{$value['year']}-{$value['month']}-{$value['days']}"]['sum_totals'] += $value['sum_totals'];
                $ret_data_reshape[$value['store_id']]['dates']["{$value['year']}-{$value['month']}-{$value['days']}"]['sum_sales'] += $value['sum_sales'];
            }
        }

        // echo '<pre>';var_dump($range,$ret_data_reshape);echo '</pre>';
        // exit;

        $ret_data['list'] = $ret_data_reshape;
        $ret_data['count'] = count($ret_data_reshape);
        return $ret_data;
    }


    /**
     * 获取 订单销售额 报表 列表 (Location)
     * @AUTHOR: Lamkakyun
     * @DATE: 2019-01-25 15:08:36
     */
    public function getOrderLocationSaleList($params)
    {
        $where = $this->_handleQueryDate($params, []);

        if (isset($params['account']) && !empty($params['account'])) $where['platform_account'] = ['IN', $params['account']];

        $start = ($params['p'] - 1) * $params['ps'];

        $sort_arr = explode(',', $params['sort_field']);
        $order_by = implode(',', array_map(function ($val) use ($params) {
            return "{$val} {$params['sort']}";
        }, $sort_arr));

        if ($params['checkDate'] == 'day') $range = range_day($params['scantime_end'], $params['scantime_start'], true);
        else $range = range_month($params['scandate_end'], $params['scandate_start']);

        $_group_by = 'platform_account_id, location,days, month, year';
        if ($params['checkDate'] == 'month') $_group_by = 'platform_account_id, location, month, year';


        $_fields = "{$_group_by}, SUM(totals) as sum_totals, SUM(sales) as sum_sales";

        $ret_data = $this->_getGroupByCountAndList($this->orderSellerLocationModel, $where, $_group_by, $_fields, $start, $params['ps'], $order_by);

        // todo:重组数据 (就算没有数据 也要默认给 空数组)
        $ret_data_reshape = [];
        foreach ($ret_data['list'] as $value) {
            foreach ($range as $v) {
                $ret_data_reshape[$value['location']]['dates'][$v] = ['sum_totals' => '0', 'sum_sales' => '0.00'];
            }
        }

        foreach ($ret_data['list'] as $value) {
            if ($params['checkDate'] == 'month') {
                $ret_data_reshape[$value['location']]['dates']["{$value['year']}-{$value['month']}"]['sum_totals'] += $value['sum_totals'];
                $ret_data_reshape[$value['location']]['dates']["{$value['year']}-{$value['month']}"]['sum_sales'] += $value['sum_sales'];
            } else {
                $ret_data_reshape[$value['location']]['dates']["{$value['year']}-{$value['month']}-{$value['days']}"]['sum_totals'] += $value['sum_totals'];
                $ret_data_reshape[$value['location']]['dates']["{$value['year']}-{$value['month']}-{$value['days']}"]['sum_sales'] += $value['sum_sales'];
            }
        }

        $ret_data['list'] = $ret_data_reshape;
        $ret_data['count'] = count($ret_data_reshape);
        return $ret_data;
    }


    /**
     * 用到的地方比较多，封装一下，处理时间维度 的方法
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-09-19 05:52:56
     */
    public function _handleQueryDate($params, $where = [])
    {
        if (isset($params['scantime_start'])) $params['scantime_start'] .= ' 00:00:00';
        if (isset($params['scantime_end'])) $params['scantime_end'] .= ' 23:59:59';
        if (isset($params['scandate_start'])) $params['scandate_start'] .= '-01';
        if (isset($params['scandate_end'])) $params['scandate_end'] .= '-' . get_day_of_month(date('m', strtotime($params['scandate_end']))) . " 23:59:59";

        if ($params['checkDate'] == 'day') {
            if (isset($params['scantime_start'])) $where['datetime'][] = ['EGT', strtotime($params['scantime_start'])];
            if (isset($params['scantime_end'])) $where['datetime'][] = ['LT', strtotime($params['scantime_end'])];
        } else {
            if (isset($params['scandate_start'])) $where['datetime'][] = ['EGT', strtotime($params['scandate_start'])];
            if (isset($params['scandate_end'])) $where['datetime'][] = ['LT', strtotime($params['scandate_end'])];
        }

        return $where;
    }


    /**
     * 获取 订单销售员 销售报表 列表
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-09-19 05:30:48
     */
    public function getOrderSellerList($params, $type = 'date')
    {
        $where = $this->_handleQueryDate($params, []);

        if (!empty($_SESSION['truename']) && !getRolePower()) {
            $org_list = ToolsLib::getInstance()->getLevel1Orgs($_SESSION['truename']);
            $sellers = ToolsLib::getInstance()->getSellerByOrg(array_column($org_list, 'name'));
            if ($sellers) $where['seller'] = ['IN', $sellers];
        }

        if (isset($params['organ']) && !empty($params['organ'])) {
            $all_sub_org_ids = ToolsLib::getInstance()->getSubOrgIds($params['organ'][0]);
            if ($all_sub_org_ids) $where['branch_id'] = ['IN', $all_sub_org_ids];
        }

        if (isset($params['seller']) && !empty($params['seller'])) $where['seller'] = ['IN', $params['seller']];

        if (isset($params['where_sql_for_seller']) && !empty($params['where_sql_for_seller'])) $where[] = ['EXP', Db::raw($params['where_sql_for_seller'])];

        $sort_arr = explode(',', $params['sort_field']);
        $order_by = implode(',', array_map(function ($val) use ($params) {
            return "{$val} {$params['sort']}";
        }, $sort_arr));

        if ($params['checkDate'] == 'day') $range = range_day($params['scantime_end'], $params['scantime_start'], true);
        else $range = range_month($params['scandate_end'], $params['scandate_start']);

        // 每一天的 seller+branch_id 是 唯一的，但 seller 不是唯一的，所以group by 还需要 加上 branch_id
        $_group_by = 'branch_id, seller,days, month, year';
        if ($params['checkDate'] == 'month') $_group_by = 'branch_id, seller, month, year';

        $_fields = "{$_group_by}, SUM(totals) as sum_totals, SUM(sales) as sum_sales";

        $ret_data = $this->_getGroupByCountAndList($this->orderSellerModel, $where, $_group_by, $_fields, false, false, $order_by);

        // todo:重组数据 (就算没有数据 也要默认给 空数组)
        $default_data = ['branch_id' => 0, 'sum_totals' => '0', 'sum_sales' => '0.00'];
        $ret_data_reshape = [];

        // 给予默认值
        foreach ($ret_data['list'] as $value) {
            foreach ($range as $v) {
                // 用3 个下划线 区分
                $tmp_key = trim($value['seller']) . "___{$value['branch_id']}";

                $tmp_default_data = $default_data;
                $tmp_default_data['branch_id'] = $value['branch_id'];
                $ret_data_reshape[$tmp_key][$v] = $tmp_default_data;
            }
        }

        // 设置实际值
        foreach ($ret_data['list'] as $value) {
            // 用3 个下划线 区分
            $tmp_key = trim($value['seller']) . "___{$value['branch_id']}";
            $_date_key = $params['checkDate'] == 'month' ? "{$value['year']}-{$value['month']}" : "{$value['year']}-{$value['month']}-{$value['days']}";

            $ret_data_reshape[$tmp_key][$_date_key] = $value;
        }
        $ret_data['list'] = $ret_data_reshape;
        $ret_data['count'] = count($ret_data_reshape);

        return $ret_data;
    }


    /**
     * 获取分组 数据
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-09-19 05:07:51
     */
    public function _getGroupByCountAndList($model, $where, $_group_by, $_fields, $start, $select_count, $order_by = '')
    {
        $tmp_sql = $model->field($_fields)->where($where)->group($_group_by)->buildSql();

        $sql = "SELECT COUNT(*) as total FROM {$tmp_sql} as tmp";

        $query_rs = $model->query($sql);
        $count = $query_rs[0]['total'] ?? 0;
        $sum_list = [];
        if ($count) {
            // $sum_list = $model->field($_fields)->where($where)->group($_group_by)->limit($start, $select_count)->order($order_by)->select()->toArray();
            // 分组用limit 会造成错误
            $sum_list = $model->field($_fields)->where($where)->group($_group_by)->order($order_by)->select()->toArray();
        }

        if (isset($_REQUEST['debug']) && $_REQUEST['debug'] == 'sql') {
            echo $model->getLastSql();
        }

        return ['list' => $sum_list, 'count' => $count];
    }

    /**
     * 获取分组 数据, _getGroupByCountAndList 没有进行分页,一次性查出所有数据，所以数据量少的时候可以使用,针对其进行优化
     * @param $group_by1 用来查询单个字段，比如sku, 并进行分页
     * @param $group_by2 用来真正的分组，其where 条件是 第一个group_by查出来的 sku_list
     * @AUTHOR: Lamkakyun
     * @DATE: 2018-09-19 05:07:51
     */
    public function _getGroupByCountAndList2($model, $where, $group_by1, $group_by2, $_fields, $start, $select_count, $order_by = '', $sort_more = [])
    {
        $is_sort_more = ($sort_more && $sort_more['sort'] && $sort_more['sort_date']) ? true : false;

        $tmp_sql = $model->field($group_by1)->where($where)->group($group_by1)->buildSql();
        $count_sql = "SELECT COUNT(*) as total FROM {$tmp_sql} as tmp";

        $query_rs = $model->query($count_sql);
        $count = $query_rs[0]['total'] ?? 0;

        if (isset($_REQUEST['debug']) && $_REQUEST['debug'] == 'more_data') {
            echo '<pre>';
            var_dump($sort_more);
            echo '</pre>';
            exit;
        }

        $sum_list = [];
        if ($count) {

            // TODO: 排序功能 (很复杂，但没有什么好办法)
            if ($is_sort_more) {
                $order_by_more = "{$sort_more['sort_field']} {$sort_more['sort']}, {$group_by1}";

                $_tmp_sql = $model->field($group_by1)->where($sort_more['tmp_where'])->group($group_by1)->buildSql();
                $_tmp_count_sql = "SELECT COUNT(*) as total FROM {$_tmp_sql} as tmp";

                $_tmp_query_rs = $model->query($_tmp_count_sql);
                // 选择的排序天数，group by 数据的 记录数 （很拗口，但就是这个意思）
                // 因为 group by ，只会找出有记录的数
                // 假如要按照，2018-10-31 的sku销量情况， 排序，那么要先找出当天的sku的排序
                $_tmp_offset = $_tmp_query_rs[0]['total'] ?? 0;

                if (isset($_REQUEST['debug']) && $_REQUEST['debug'] == 'offset') {
                    echo '<pre>';
                    var_dump($_tmp_count_sql, $_tmp_offset);
                    echo '</pre>';
                    exit;
                }

                //  TODO: 是否已经超出， group by 的记录数
                $is_out = 0; // 0：没出 1：全出 2：刚出
                if ($start >= $_tmp_offset) {
                    $start -= $_tmp_offset;
                    $is_out = 1; // 超出
                }
                $_end = $start + $select_count;
                if ($start < $_tmp_offset && $_end > $_tmp_offset) {
                    $is_out = 2; // start 没有超出，但end 超出了，需要合并了
                }

                // TODO: 超出记录数，就...(忘了)
                if ($is_out == 1) {
                    $_sort_more_data = $model->field($sort_more['select_fields'])->where($sort_more['tmp_where2'])->group($group_by1)->order($order_by_more)->limit($start, $select_count)->select()->toArray();

                    $_sort_more_all_values = array_column($_sort_more_data, $group_by1);

                    if (isset($_REQUEST['debug']) && $_REQUEST['debug'] == 'out1') {
                        echo '<pre>';
                        var_dump($model->getLastSql(), $_sort_more_all_values);
                        echo '</pre>';
                        exit;
                    }

                    $order_by = "FIELD(`{$group_by1}`, " . implode(',', array_map(function ($val) {
                            return "'{$val}'";
                        }, $_sort_more_all_values)) . ")";

                    $_tmp_where = $sort_more['tmp_where2'];
                    $_tmp_where[$group_by1] = ['IN', $_sort_more_all_values];


                    $sum_list = $model->field($_fields)->where($_tmp_where)->group($group_by2)->orderRaw($order_by)->select()->toArray();
                } elseif ($is_out == 2) {
                    // 第一个列表
                    $_tmp_where1 = $sort_more['tmp_where'];

                    $_sort_more_data1 = $model->field($sort_more['select_fields'])->where($_tmp_where1)->group($group_by1)->order($order_by_more)->limit($start, $select_count)->select()->toArray();

                    $_sort_more_all_values1 = array_column($_sort_more_data1, $group_by1);

                    $order_by1 = "FIELD(`{$group_by1}`, " . implode(',', array_map(function ($val) {
                            return "'{$val}'";
                        }, $_sort_more_all_values1)) . ")";

                    // $_tmp_where1[$group_by1] = ['IN', $_sort_more_all_values1];
                    $where[$group_by1] = ['IN', $_sort_more_all_values1];

                    $sum_list1 = $model->field($_fields)->where($where)->group($group_by2)->orderRaw($order_by1)->select()->toArray();

                    // 第二个列表
                    $_tmp_where2 = $sort_more['tmp_where2'];
                    $_sort_more_data2 = $model->field($sort_more['select_fields'])->where($_tmp_where2)->group($group_by1)->order($order_by_more)->limit($_tmp_offset, ($select_count - count($_sort_more_all_values1)))->select()->toArray();

                    $_sort_more_all_values2 = array_column($_sort_more_data2, $group_by1);

                    $order_by2 = "FIELD(`{$group_by1}`, " . implode(',', array_map(function ($val) {
                            return "'{$val}'";
                        }, $_sort_more_all_values2)) . ")";

                    $_tmp_where2[$group_by1] = ['IN', $_sort_more_all_values2];

                    $sum_list2 = $model->field($_fields)->where($_tmp_where2)->group($group_by2)->orderRaw($order_by2)->select()->toArray();

                    if (isset($_REQUEST['debug']) && $_REQUEST['debug'] == 'out2') {
                        echo '<pre>';
                        var_dump($model->getLastSql());
                        echo '</pre>';
                        exit;
                    }

                    $sum_list = array_merge($sum_list1, $sum_list2);

                } else // 没超出
                {
                    // step 1: 查出sku
                    $_sort_more_data = $model->field($sort_more['select_fields'])->where($sort_more['tmp_where'])->group($group_by1)->order($order_by_more)->limit($start, $select_count)->select()->toArray();
                    $_sort_more_all_values = array_column($_sort_more_data, $group_by1);

                    // step 2: 根据 查出的sku 排序
                    $order_by = "FIELD(`{$group_by1}`, " . implode(',', array_map(function ($val) {
                            return "'{$val}'";
                        }, $_sort_more_all_values)) . ")";

                    // $_tmp_where = $sort_more['tmp_where'];
                    // $_tmp_where[$group_by1] = ['IN', $_sort_more_all_values];

                    $where[$group_by1] = ['IN', $_sort_more_all_values];

                    $sum_list = $model->field($_fields)->where($where)->group($group_by2)->orderRaw($order_by)->select()->toArray();
                }

            } else // 不用排序就，简单很多了
            {
                $data = $model->field("DISTINCT {$group_by1}")->where($where)->limit($start, $select_count)->select()->toArray();

                $all_values = array_column($data, $group_by1);

                $where[$group_by1] = ['IN', $all_values];

                $sum_list = $model->field($_fields)->where($where)->group($group_by2)->orderRaw($order_by)->select()->toArray();
            }


        }

        if (isset($_REQUEST['debug']) && $_REQUEST['debug'] == 'sql') {
            var_dump($model->getLastSql());
            exit;
        }

        return ['list' => $sum_list, 'count' => $count];
    }


    /**
     * 获取月销售 数据
     * @author lamkakyun
     * @date 2018-12-19 16:14:28
     * @param $type 类型 'organ'|'seller'
     * @param $months 月份数组
     * @return array
     */
    public function getMonthlySaleForSeller($months = [])
    {
        $order_seller_avg_model = new OrderSellerAvg();
        $ret_data = [];

        if (empty($months)) return $ret_data;

        $tmp_where = [];
        foreach ($months as $v) {
            $tmp = explode('-', $v);
            $tmp_year = $tmp[0];
            $tmp_month = $tmp[1];

            $tmp_where[] = "(year = {$tmp_year} AND month = {$tmp_month})";
        }
        $where = 'sales_branch_id <> 0 AND (' . implode(' OR ', $tmp_where) . ')';

        $data = $order_seller_avg_model->where($where)->select()->toArray();

        $default_data = [
            'sales'  => 0,
            'totals' => 0,
        ];

        // 重组数组
        foreach ($data as $v) {
            $tmp_key = "{$v['sales_user']}___{$v['sales_branch_id']}";
            $tmp_sub_key = "{$v['year']}-{$v['month']}";
            $ret_data[$tmp_key][$tmp_sub_key] = $v;
        }

        // 添加默认数据
        foreach ($ret_data as $key => $value) {
            $exists_months = array_keys($value);
            $not_exists_months = array_diff($months, $exists_months);

            foreach ($not_exists_months as $m) {
                $ret_data[$key][$m] = $default_data;
            }

            ksort($ret_data[$key]);
        }

        return $ret_data;
    }


    /**
     * 获取月销售 数据
     * @author lamkakyun
     * @date 2018-12-19 16:14:28
     * @param $months 月份数组
     * @return array
     */
    public function getMonthlySaleForAccount($months = [])
    {
        $order_account_avg_model = new OrderAccountAvg();
        $ret_data = [];

        if (empty($months)) return $ret_data;

        $tmp_where = [];
        foreach ($months as $v) {
            $tmp = explode('-', $v);
            $tmp_year = $tmp[0];
            $tmp_month = $tmp[1];

            $tmp_where[] = "(year = {$tmp_year} AND month = {$tmp_month})";
        }
        $where = '(' . implode(' OR ', $tmp_where) . ')';

        $data = $order_account_avg_model->where($where)->select()->toArray();

        $default_data = [
            'sales'  => 0,
            'totals' => 0,
        ];

        // 重组数组
        foreach ($data as $v) {
            // $tmp_key = "{$v['platform']}___{$v['platform_account']}";
            $tmp_key = "{$v['platform_account']}";
            $tmp_sub_key = "{$v['year']}-{$v['month']}";
            $ret_data[$tmp_key][$tmp_sub_key] = $v;
        }

        // 添加默认数据
        foreach ($ret_data as $key => $value) {
            $exists_months = array_keys($value);
            $not_exists_months = array_diff($months, $exists_months);

            foreach ($not_exists_months as $m) {
                $ret_data[$key][$m] = $default_data;
            }

            ksort($ret_data[$key]);
        }

        return $ret_data;
    }
}