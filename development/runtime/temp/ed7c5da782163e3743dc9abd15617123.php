<?php if (!defined('THINK_PATH')) exit(); /*a:12:{s:83:"/opt/web/count/development/public/../application/count/view/newsku/index/index.html";i:1550824653;s:69:"/opt/web/count/development/application/count/view/layout/default.html";i:1544600061;s:75:"/opt/web/count/development/application/count/view/newsku/index/_layout.html";i:1551246498;s:66:"/opt/web/count/development/application/count/view/common/meta.html";i:1550824652;s:68:"/opt/web/count/development/application/count/view/common/header.html";i:1550824652;s:77:"/opt/web/count/development/application/count/view/newsku/index/_tabs_bar.html";i:1551159881;s:73:"/opt/web/count/development/application/count/view/newsku/index/_form.html";i:1552989433;s:78:"/opt/web/count/development/application/count/view/newsku/index/table_date.html";i:1551349980;s:73:"/opt/web/count/development/application/count/view/newsku/index/table.html";i:1552989433;s:66:"/opt/web/count/development/application/count/view/layout/page.html";i:1544600061;s:69:"/opt/web/count/development/application/count/view/common/script2.html";i:1550824652;s:69:"/opt/web/count/development/application/count/view/layout/btn_top.html";i:1550824653;}*/ ?>
<!DOCTYPE html>
<html lang="<?php echo $config['language']; ?>">
<head>
    <title>SKU销量报表</title>
    <meta charset="utf-8">
<title><?php echo (isset($title) && ($title !== '')?$title:''); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<meta name="renderer" content="webkit">
<link rel="shortcut icon" href="/assets/img/favicon.ico" />
<!-- Tell the browser to be responsive to screen width -->
<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
<!-- Bootstrap 3.3.7 -->
<link rel="stylesheet" href="/assets/components/bootstrap/dist/css/bootstrap.min.css">
<!-- Font Awesome -->
<link rel="stylesheet" href="/assets/components/font-awesome/css/font-awesome.min.css">
<!-- Ionicons -->
<link rel="stylesheet" href="/assets/components/Ionicons/css/ionicons.min.css">
<!-- Theme style -->
<link rel="stylesheet" href="/assets/dist/css/AdminLTE.css">
<!-- AdminLTE Skins. Choose a skin from the css/skins folder instead of downloading all of them to reduce the load. -->
<link rel="stylesheet" href="/assets/dist/css/skins/_all-skins.min.css">
<!-- Date Picker -->
<link rel="stylesheet" href="/assets/components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css">
<!-- Daterange picker -->
<link rel="stylesheet" href="/assets/components/bootstrap-daterangepicker/daterangepicker.css">
<!--bootstrap-select-->
<link rel="stylesheet" href="/assets/components/bootstrap-select/css/bootstrap-select.css">
<!--jstree-->
<link rel="stylesheet" href="/assets/components/jstree/themes/default/style.min.css"/>
<link rel="stylesheet" href="/assets/components/datatables/datatables.min.css"/>
<!-- iCheck for checkboxes and radio inputs -->
<link rel="stylesheet" href="/assets/plugins/iCheck/all.css">
<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!-- Font -->
<link rel="stylesheet" href="/assets/dist/css/fontcss.css">
<link rel="stylesheet" href="/assets/dist/css/style.css">
<!-- HTML5 shim, for IE6-8 support of HTML5 elements. All other JS at the end of file. -->
<!--[if lt IE 9]>
  <script src="/assets/dist/js/html5shiv.js"></script>
  <script src="/assets/dist/js/respond.min.js"></script>
<![endif]-->
<script type="text/javascript">
    var require = {
        config:  <?php echo json_encode($config); ?>
    };
</script>

<!-- 调整插件的样式 -->
<style>
    table.dataTable td,th {
        border-bottom: 1px solid #ddd !important;
    }
    table.dataTable.no-footer {
        border-bottom: 1px solid #ddd;
    }
    .c_list li:hover{background:#79a7d8;cursor:pointer;}
    div.c_list {width: 200px;margin-top: 1px;background: #fff;border: 1px #444 solid;overflow-x: hidden;overflow-y: scroll;height: auto;padding: 5px 0;max-height: 200px;position: absolute;}
</style>
    
    <!-- 用来添加自定义的 样式 -->
    
    <style>
        .tab-content {
            overflow: auto;
        }
        .table th {
            background-color: white !important;
        }
        .DTFC_LeftBodyLiner {
            overflow-x: hidden;
        }
    </style>
</head>
<body class="hold-transition skin-blue-light fixed sidebar-mini <?php echo $bodyClass; ?>">
<div class="wrapper">
    <header class="main-header">
    <!-- Logo -->
    <a href="#" class="logo">
        <!-- mini logo for sidebar mini 50x50 pixels -->
        <span class="logo-mini">ERP</span>
        <!-- logo for regular state and mobile devices -->
        <span class="logo-lg"><b>ERP</b><small><i> v5</i></small></span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
        <!--第一级菜单-->
        <div id="firstnav">
            <!-- 边栏切换按钮-->
            <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                <span class="sr-only">Toggle navigation</span>
            </a>
            <!--如果不想在顶部显示角标,则给ul加上disable-top-badge类即可-->
            <ul class="nav nav-tabs nav-addtabs disable-top-badge hidden-xs" role="tablist">
                <li class=""><a href="/t.php?s=/Home/Main" ><i class="fa fa-home"></i> <span>ERP首页</span> <span class="pull-right-container"> </span></a> </li>
                <?php echo $navlist; ?>
                <div class="navbar-custom-menu">
                    <ul class="nav navbar-nav">
                        <li class="footer">
                            <a href="http://erp.spocoo.com/t.php?s=/Home/Main" target="_blank" class="dropdown-toggle"><i class="fa fa-mail-reply"></i> ERP</a>
                        </li>
                        <!-- 账号信息下拉框 -->
                        <li class="dropdown user user-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <img src="/assets/dist/img/avatar.png" class="user-image" alt="<?php echo $admin['username']; ?>">
                                <span class="hidden-xs"><?php echo $admin['username']; ?></span>
                            </a>
                            <ul class="dropdown-menu">
                                <!-- User image -->
                                <li class="user-header">
                                    <img src="/assets/dist/img/avatar.png" class="img-circle" alt="">
                                    <p><?php echo $admin['username']; ?></p>
                                </li>
                                <!-- Menu Footer-->
                                <li class="user-footer">
                                    <!--<div class="pull-left">-->
                                    <!--<a href="#" class="btn btn-primary addtabsit"><i class="fa fa-user"></i>-->
                                    <!--<?php echo __('Profile'); ?></a>-->
                                    <!--</div>-->
                                    <div class="pull-right">
                                        <a href="<?php echo url('/count/login/logout'); ?>" class="btn btn-danger"><i class="fa fa-sign-out"></i>
                                            <?php echo __('Logout'); ?></a>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </ul>
        </div>
        <?php if($config['erp']['multiplenav']): ?>
        <!--第二级菜单,只有在multiplenav开启时才显示-->
        <div id="secondnav">
            <ul class="nav nav-tabs nav-addtabs disable-top-badge" role="tablist"></ul>
        </div>
        <?php endif; ?>
    </nav>
</header>
<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <!-- 左侧菜单栏 -->
    <section class="sidebar">
        <!-- 移动端一级菜单 -->
        <div class="mobilenav visible-xs"></div>
        <!--如果想始终显示子菜单,则给ul加上show-submenu类即可,当multiplenav开启的情况下默认为展开-->
        <ul class="sidebar-menu tree <?php if($config['erp']['multiplenav']): ?>show-submenu<?php endif; ?>" data-widget="tree">
            <!-- 菜单可以在 后台管理->权限管理->菜单规则 中进行增删改排序 -->
            <?php echo $menulist; ?>
        </ul>
    </section>
    <!-- /.sidebar -->
</aside>

    <div class="content-wrapper">
        <section class="content">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
    <li class="<?php echo $type=='date'?'active' : ''; ?>"><a href="<?php echo url('/count/newsku/index', ['type' => 'date']); ?>">按日期</a></li>
    <li class="<?php echo $type=='account'?'active' : ''; ?>"><a href="<?php echo url('/count/newsku/index', ['type' => 'account']); ?>">按平台</a></li>
    <li class="<?php echo $type=='cat'?'active' : ''; ?>"><a href="<?php echo url('/count/newsku/index', ['type' => 'cat']); ?>">按分类</a></li>
    <?php if($is_top_manager): ?>
    <!-- <li class="<?php echo $type=='organ'?'active' : ''; ?>"><a href="<?php echo url('/count/newsku/index', ['type' => 'organ']); ?>">按组织架构</a></li> -->
    <?php endif; ?>
    <li class="<?php echo $type=='seller'?'active' : ''; ?>"><a href="<?php echo url('/count/newsku/index', ['type' => 'seller']); ?>">按销售员</a></li>
    <li class="<?php echo $type=='developer'?'active' : ''; ?>"><a href="<?php echo url('/count/newsku/index', ['type' => 'developer']); ?>">按开发员</a></li>
    <li class="<?php echo $type=='country'?'active' : ''; ?>"><a href="<?php echo url('/count/newsku/index', ['type' => 'country']); ?>">按目标国家</a></li>
    <li class="<?php echo $type=='store'?'active' : ''; ?>"><a href="<?php echo url('/count/newsku/index', ['type' => 'store']); ?>">按仓库</a></li>

    
    <li class="pull-right">
        <div class="btn-toolbar" role="toolbar">
            <div class="btn-group padding-top8 paddint-right5">
                <?php if($params['type'] != 'organ'): ?>
                <!-- <a href="<?php echo url('/count/newsku/index', array_merge($params, ['type' => $type, 'model' => 'table'])); ?>" data-toggle="tooltip" class="btn btn-xs btn-default <?php echo $model=='table'?'active' : ''; ?>" title="列表模式"><span class="fa fa-fw fa-th-large"></span></a> -->

                <!-- <a href="<?php echo url('/count/newsku/index', array_merge($params,['type' => $type, 'model' => 'chart'])); ?>" data-toggle="tooltip" class="btn btn-xs btn-default <?php echo $model=='chart'?'active' : ''; ?>" title="图表模式"><span class="fa fa-fw fa-bar-chart-o"></span></a> -->
                <?php endif; ?>
            </div>
        </div>
    </li>
    <li class="pull-right alert-tips">
        <i class="icon fa fa-warning"></i> 统计数据存在一定的延迟性, 请勿与订单实时对比.
    </li>
</ul>
                <div class="box-body">
                    <div class="box box-solid">
                        <div class="box-header with-bsales">
                            <form action="<?php echo url('/count/newsku/index', array_merge($params,['type' => $type, 'model' => $model])); ?>" method="get" class="form-inline froms clearfix">

    <input type="hidden" name="type" value="<?php echo $params['type']; ?>">
    <input type="hidden" name="model" value="<?php echo $params['model']; ?>">
    <input type="hidden" name="ps" value="<?php echo $params['ps']; ?>">
    <input type="hidden" name="p" value="<?php echo $params['p']; ?>">

    <?php if(in_array($params['type'], ['account', 'platform'])): ?>
    <div class="form-group">
        <label class="control-label text-right">平台：</label>
        <select class="selectpicker platform-choice" title="全部" name="platform" id="platform" data-actions-box="true" data-live-search="true" >
            <option value="">全部</option>
            <?php foreach($platforms as $item): ?>
            <option value="<?php echo $item; ?>" <?php if($params['platform'] == $item): ?>selected<?php endif; ?>><?php echo $item; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; if(in_array($params['type'], ['account'])): ?>
    <div class="form-group">
        <label class="control-label text-right">账号：</label>
        <select class="selectpicker account-choice" title="全部" name="account[]" id="account" data-actions-box="true" data-live-search="true" multiple>

            <?php foreach($account_list as $item): ?>
            <option value="<?php echo $item; ?>" <?php if(in_array($item, $params['account'] ?? [])): ?>selected<?php endif; ?>><?php echo $item; ?></option>
            <?php endforeach; ?>

        </select>
    </div>
    <?php endif; if(in_array($params['type'], ['cat'])): ?>
    <div class="form-group" style="width: 206px;">
        <select class="selectpicker" title="请选择分类" name="cat_id" id="cat_id" data-actions-box="true" data-live-search="true" onchange="sku_module.change_sku_cat($(this))">

            <option value="">请选择分类</option>
            <?php if(is_array($goods_category) || $goods_category instanceof \think\Collection || $goods_category instanceof \think\Paginator): if( count($goods_category)==0 ) : echo "" ;else: foreach($goods_category as $key=>$item): ?>
            <option data-sub_cat='<?php echo base64_encode(json_encode($item["sub_cat"])); ?>' value="<?php echo $key; ?>" <?php if($params['cat_id'] == $key): ?>selected<?php endif; ?>><?php echo $item['name']; ?></option>
            <?php endforeach; endif; else: echo "" ;endif; ?>
        </select>
    </div>

    <div class="form-group" style="width: 206px;">
            <select class="selectpicker" title="请选择二级分类" name="sub_cat_id[]" id="sub_cat_id" data-actions-box="true" data-live-search="true" multiple>
                <?php if(is_array($sub_goods_category) || $sub_goods_category instanceof \think\Collection || $sub_goods_category instanceof \think\Paginator): if( count($sub_goods_category)==0 ) : echo "" ;else: foreach($sub_goods_category as $key=>$item): ?>
                <option value="<?php echo $key; ?>" <?php if(in_array($key, $params['sub_cat_id'])): ?>selected<?php endif; ?> ><?php echo $item['name']; ?></option>
                <?php endforeach; endif; else: echo "" ;endif; ?>
            </select>
        </div>
    <?php endif; if(in_array($params['type'], ['seller'])): ?>
    <label class="control-label text-right">部门：</label>
    <select class="selectpicker" name="organ[]" id="organ" data-actions-box="false" data-live-search="true" onchange="order_model.change_org($(this))">
        <option value="">全部</option>
        <?php foreach($org_list as $item): ?>
        <option value="<?php echo $item['id']; ?>" <?php if(in_array($item['id'], $params['organ'] ?? [])): ?>selected<?php endif; ?>><?php echo $item['name']; ?></option>
        <?php echo $item['name']; ?>
        </option>
        <?php endforeach; ?>
    </select>

    <label class="control-label text-right">销售员：</label>
    <select class="selectpicker" title="全部" name="seller[]" id="seller" data-actions-box="true" data-live-search="true" data-max-options="20" multiple>
        <?php foreach($sellers as $item): ?>
        <option value="<?php echo $item; ?>" <?php if(in_array($item, $params['seller'] ?? [])): ?>selected<?php endif; ?>><?php echo $item; ?></option>
        <?php endforeach; ?>
    </select>
    <?php endif; if(in_array($params['type'], ['developer'])): ?>
    <label class="control-label">开发员：</label>
    <select class="selectpicker " title="全部" name="developer[]" id="developer" data-actions-box="true" data-live-search="true" multiple>

        <?php foreach($developers as $item): ?>
        <option value="<?php echo $item['username']; ?>" <?php if(in_array($item['username'], $params['developer'])): ?>selected<?php endif; ?>><?php echo $item['username']; ?></option>
        <?php endforeach; ?>

    </select>
    <?php endif; if(in_array($params['type'], ['country'])): ?>
    <label class="control-label">目标国家：</label>
    <select class="selectpicker " title="全部" name="country[]" id="country" data-actions-box="true" data-live-search="true" multiple>

        <?php if(is_array($countries) || $countries instanceof \think\Collection || $countries instanceof \think\Paginator): if( count($countries)==0 ) : echo "" ;else: foreach($countries as $key=>$item): ?>
        <option value="<?php echo $key; ?>" <?php if(in_array($key, $params['country'])): ?>selected<?php endif; ?>>【<?php echo $key; ?>】<?php echo $item; ?></option>
        <?php endforeach; endif; else: echo "" ;endif; ?>

    </select>
    <?php endif; if(in_array($params['type'], ['store'])): ?>
    <label class="control-label">仓库：</label>
    <select class="selectpicker " title="全部" name="store_id[]" id="store_id" data-actions-box="true" data-live-search="true" multiple>

        <?php if(is_array($store_list) || $store_list instanceof \think\Collection || $store_list instanceof \think\Paginator): if( count($store_list)==0 ) : echo "" ;else: foreach($store_list as $key=>$item): ?>
        <option value="<?php echo $item['id']; ?>" <?php if(in_array($item['id'], $params['store_id'])): ?>selected<?php endif; ?>><?php echo $item['store_name']; ?></option>
        <?php endforeach; endif; else: echo "" ;endif; ?>
    </select>
    <?php endif; ?>

    <div class="form-group">
        <label class="control-label text-right">时间维度：</label>
        <div class="checkbox">
            <label><input type="radio" name="checkDate" class="checkDate" value="day" <?php if($params['checkDate'] == 'day'): ?>checked<?php endif; ?> ><small>天</small></label>
            <label><input type="radio" name="checkDate" class="checkDate" value="month" <?php if($params['checkDate'] == 'month'): ?>checked<?php endif; ?>><small>月</small></label>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label text-right">区间：</label>
        <div class="input-group laydate-group">
            <input type="text" class="input-sm form-control input-date datepicker start <?php if($params['checkDate'] != 'day'): ?>hide<?php endif; ?>" name="scantime_start"  placeholder="开始时间" value="<?php echo $params['scantime_start']; ?>" readonly/>
            <input type="text" class="input-sm form-control input-date monthpicker start <?php if($params['checkDate'] != 'month'): ?>hide<?php endif; ?>" name="scandate_start"  placeholder="开始年月" value="<?php echo $params['scandate_start']; ?>" readonly/>
            <span class="input-group-addon">到</span>
            <input type="text" class="input-sm form-control input-date datepicker end <?php if($params['checkDate'] != 'day'): ?>hide<?php endif; ?>" name="scantime_end" value="<?php echo $params['scantime_end']; ?>" placeholder="结束时间" readonly/>
            <input type="text" class="input-sm form-control input-date monthpicker end <?php if($params['checkDate'] != 'month'): ?>hide<?php endif; ?>" name="scandate_end" value="<?php echo $params['scandate_end']; ?>" placeholder="结束年月" readonly/>
        </div>
    </div>
    <!--<div class="checkbox shotcut_day_div" <?php if($params['checkDate'] == 'month'): ?>style="display:none;"<?php endif; ?>>-->
    <!--<div class="input-group laydate-group">-->
        <!--<div class="checkbox">-->
            <!--<label><input type="radio" class="" onchange="order_model.checked_date(this)" value="today"><small>今天</small></label>-->
            <!--<label><input type="radio" class="" onchange="order_model.checked_date(this)" value="yesterday"><small>昨天</small></label>-->
            <!--<label><input type="radio" class="" onchange="order_model.checked_date(this)" value="recently3day"><small>最近三天</small></label>-->
        <!--</div>-->
    <!--</div>-->
    <!--</div>-->

    <?php if(in_array($params['type'], ['account', 'cat', 'developer','seller','country', 'store'])): ?>
    <div class="form-group">
        <label class="control-label text-right">SKU</label>
        <div class="input-group laydate-group">
            <input type="text" class="input-sm form-control" name="sku_keyword"  placeholder="sku关键词, 支持批量" value="<?php echo $params['sku_keyword']; ?>"/>
        </div>
    </div>
    <?php endif; ?>

    <div class="form-group">
        <button class="btn btn-primary btn-sm" type="submit" name="submit"><i class="glyphicon glyphicon-search"></i> 确定搜索&nbsp;</button>
        <a class="btn btn-warning btn-sm" href="javascript:void(0);" target="_blank" onclick="common_module.export_excel()"><i class="glyphicon glyphicon-save"></i> 导出Excel&nbsp;</a>
    </div>
    <?php if($auth->erp_id == '0'): if(in_array($params['type'], ['organ'])): ?>
    <div class="form-group" style="float: right;">
        <?php if($auth->check('count/newsku/index/organTrendency')): ?>
        <a class="btn btn-info btn-sm" data-url="<?php echo url('organTrendency'); ?>" href="javascript:void(0);" onclick="order_model.organ_loop_trendency($(this));"> 环比增长走势&nbsp;</a>
        &nbsp;<?php endif; if($auth->check('count/newsku/index/setTarget')): ?>
        <a class="btn btn-success btn-sm" href="javascript:void(0);" data-url="<?php echo url('setTarget'); ?>"  onclick="order_model.set_org_target($(this));"> 目标设置&nbsp;</a>
        <?php endif; ?>
    </div>
    <?php endif; endif; ?>
</form>
                        </div>
                    </div>
                </div>

                <div class="tab-content">
                    <?php if($model == 'table'): if($params['type'] == 'date'): ?>
                    <div class="text-danger">
    <i class="icon fa fa-warning"></i> 说明：总成本为sku物品成本总和，sku销量根据已发货订单的进系统时间来统计
</div>
<table class="table table-bordered table-hover dataTable table-striped display nowrap compact js-table" id="scroll_table">
    <thead>
    <!--合计-->
    <tr>
        <td class="text-center table-total">合计</td>
        <td class="text-center table-total"><?php echo $total_data['sum_counts']; ?></td>
        <td class="text-center table-total"><?php echo $total_data['sum_totals']; ?></td>
        <td class="text-center table-total"></td>
        <td class="text-center table-total"><?php echo $total_data['sum_costs']; ?></td>
        <td class="text-center table-total"><?php echo $total_data['sum_totals']; ?></td>
        <td class="text-center table-total"></td>
    </tr>
    <tr>
        <th class="text-center">
            <?php if($params['type'] == 'date'): ?>
            日期
            <?php elseif($params['type'] == 'platform'): ?>
            平台
            <?php else: ?>
            账号
            <?php endif; ?>
        </th>
        <th class="text-center">总数量 <a href="javascript:void(0);" data-container='body' data-toggle="tooltip" data-placement="" title="销售SKU种类的总数量"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th class="text-center">总销量 <a href="javascript:void(0);" data-container='body' data-toggle="tooltip" data-placement="" title="销售SKU的总数量"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th class="text-center">平均销量 <a href="javascript:void(0);" data-container='body' data-toggle="tooltip" data-placement="" title="平均销量 = 总销量 / 总数量"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th class="text-center">总成本 ($)</th>
        <th class="text-center">总销售额 ($)</th>
        <th class="text-center">平均销量环比增长</th>
    </tr>

    </thead>
    <tbody id='scroll_table_head'>
    <?php foreach($list as $item): ?>
    <tr>
        <td class="text-center">
            <?php if($params['type'] == 'platform'): elseif($params['type'] == 'account'): else: if($params['checkDate'] == 'day'): ?>
                    <?php echo $item['year']; ?>-<?php echo $item['month']; ?>-<?php echo $item['days']; else: ?>
                    <?php echo $item['year']; ?>-<?php echo $item['month']; endif; endif; ?>
        </td>
        <td class="text-center"><?php echo $item['counts']; ?></td>
        <td class="text-center"><?php echo $item['totals']; ?></td>
        <td class="text-center"><?php echo $item['aver_sale']; ?></td>
        <td class="text-center" ><?php echo $item['costs']; ?></td>
        <td class="text-center" ><?php echo $item['sales']; ?></td>
        <td class="text-center" ><?php echo $item['loop_growth']; ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>

                    <?php else: ?>
                    <div class="text-danger">
    <i class="icon fa fa-warning"></i> 说明：总成本为sku物品成本总和，sku销量根据已发货订单的进系统时间来统计
</div>
<table class="table table-bordered table-hover dataTable table-striped display nowrap compact js-table" id="scroll_table">
    <thead>
    <!--合计-->
    <tr>
        <td class="text-center table-total">合计</td>
        <td class="text-center table-total"></td>
        <?php foreach($range as $v): ?>
        <td class="text-center table-total">
            <span class="block" data-container='body' data-toggle="tooltip" data-placement="">
                    <?php echo isset($total_data[$v]['sum_qty'])?$total_data[$v]['sum_qty']: 0; ?>
            </span>
            </td>
        <?php endforeach; ?>
        <td class="text-center table-total"></td>
        <td class="text-center table-total"></td>
        <td class="text-center table-total"></td>
    </tr>

    <tr>
        <th class="text-center">
            SKU
        </th>
        <th class="text-center">图片</th>
        <?php foreach($range as $item): ?>
        <th class="text-center"><a href="javascript:void(0)" onclick="sku_module.change_date_sort($(this))" data-url="<?php echo $unsort_url; ?>" data-date="<?php echo $item; ?>" data-sort="<?php if(isset($params['sort_more']) && $params['sort_more'] == 'DESC' && $params['sort_date'] == $item): ?>ASC<?php else: ?>DESC<?php endif; ?>"><?php if($params['checkDate'] == 'day'): ?><?php echo substr($item, 5); else: ?><?php echo $item; endif; if(isset($params['sort_more']) && $params['sort_date'] == $item): if($params['sort_more'] == 'DESC'): ?><span class="glyphicon glyphicon-sort-by-attributes-alt"></span><?php else: ?><span class="glyphicon glyphicon-sort-by-attributes"></span><?php endif; endif; ?></a></th>
        
        <?php endforeach; ?>
        <th class="text-center table-total"><a href="javascript:void(0)" onclick="sku_module.change_date_sort($(this))" data-url="<?php echo $unsort_url; ?>" data-date="all" data-sort="<?php if(isset($params['sort_more']) && $params['sort_more'] == 'DESC' && $params['sort_date'] == 'all'): ?>ASC<?php else: ?>DESC<?php endif; ?>">合计<?php if(isset($params['sort_more']) && $params['sort_date'] == 'all'): if($params['sort_more'] == 'DESC'): ?><span class="glyphicon glyphicon-sort-by-attributes-alt"></span><?php else: ?><span class="glyphicon glyphicon-sort-by-attributes"></span><?php endif; endif; ?></a></th>
        <th class="text-center">平均销量</th>
        <th class="text-center">走势图</th>
    </tr>

    </thead>
    <tbody id="scroll_table_head">
    <?php foreach($list as $key => $item): ?>
    <tr>
        <td class="text-center">
            <?php echo $key; ?>
        </td>
        <td class="text-center">
            <?php if($params['checkDate'] == 'day'): ?>
            <img class="img-src-size" data-src="http://image.spocoo.com/<?php echo $item[$params['scantime_end']]['thumb']; ?>" src="http://image.spocoo.com/<?php echo $item[$params['scantime_end']]['thumb']; ?>?w=50&h=50" bigsrc="http://image.spocoo.com/<?php echo $item[$params['scantime_end']]['thumb']; ?>?w=400&h=400" onclick="order_model.skuimage(this)">
            <?php else: ?>
            <img class="img-src-size" data-src="http://image.spocoo.com/<?php echo $item[$params['scandate_end']]['thumb']; ?>" src="http://image.spocoo.com/<?php echo $item[$params['scandate_end']]['thumb']; ?>?w=50&h=50" bigsrc="http://image.spocoo.com/<?php echo $item[$params['scandate_end']]['thumb']; ?>?w=400&h=400" onclick="order_model.skuimage(this)">
            <?php endif; ?>
        </td>
        <?php foreach($item as $k => $sub_item): ?>
        <td class="text-center">
            <span class="block ele-<?php echo $sub_item['sku']; ?>" data-date="<?php echo $k; ?>" data-qty="<?php echo $sub_item['sum_qty']; ?>">
                <?php if($params['type'] == 'account'): ?>
             <a href="javascript:void(0)" data-url="<?php echo url('skuPlatform', '', ''); ?>?date=<?php echo $k; ?>&sku=<?php echo $sub_item['sku']; ?>" data-date="<?php echo $k; ?>" data-sku="<?php echo $sub_item['sku']; ?>" onclick="sku_module.showSkuPlatformStat($(this))"><?php echo $sub_item['sum_qty']; ?></a>
             <?php else: ?>
             <?php echo $sub_item['sum_qty']; endif; ?>
            </span>
        </td>
        <?php endforeach; ?>
        <td class="text-center table-total">
             <span class="block" data-container='body' data-toggle="tooltip" data-placement="">
                <?php echo $date_total_map[$key]['sum_qty']; ?>
             </span>
        </td>
        <td class="text-center">
            <span class="block" data-container='body' data-toggle="tooltip" data-placement="">
                <?php echo $date_aver_map[$key]['average_qty']; ?>
            </span>
        </td>
        <td class="text-center">
            <a href="javascript:void(0)" class="btn btn-xs btn-default" data-sku="<?php echo $key; ?>" onclick="sku_module.show_platform_trendency($(this))"><span class="fa fa-fw fa-bar-chart-o"></span></a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">图表</h4>
    </div>
    <div class="modal-body">
    <div id="chartmain" style="width:800px; height:500px;"></div>
        
    </div>
    </div>
</div>
</div>
                    <?php endif; else: endif; ?>
                </div>

                <?php if(isset($params['type']) && (!in_array($params['type'], ['date']))): ?>
                
<div class="right pagination" id="pagination">
    <ul class="pagination">
        <li class="page-pre"><a href="<?php echo $current_url; ?>&p=<?php echo $last_page; ?>">上一页</a></li>
        <?php foreach($all_page_num as $page_list): ?>
        <li class="page-number <?php if($params['p'] == $page_list): ?>active<?php endif; ?>"><a href="<?php echo $current_url; ?>&p=<?php echo $page_list; ?>"><?php echo $page_list; ?></a></li>
        <?php endforeach; ?>
        <li class="page-next"><a href="<?php echo $current_url; ?>&p=<?php echo $next_page; ?>">下一页</a></li>
    </ul>
</div>

<span class="right-detail">
    <div class="pagination-detail">
        <span class="pagination-info">总共找到 <strong class="text-danger"><?php echo $list_total; ?></strong> 条记录</span>
        <span class="page-list">每页显示 <span class="btn-group dropup">
                <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    <span class="page-size"><?php echo $params['ps']; ?></span> <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" role="menu">
                    <li role="menuitem"><a href="<?php echo $current_url; ?>&ps=20&p=1">20</a></li>
                    <li role="menuitem"><a href="<?php echo $current_url; ?>&ps=50&p=1">50</a></li>
                    <li role="menuitem"><a href="<?php echo $current_url; ?>&ps=100&p=1">100</a></li>
                    <li role="menuitem"><a href="<?php echo $current_url; ?>&ps=200&p=1">200</a></li>
                    <li role="menuitem"><a href="<?php echo $current_url; ?>&ps=300&p=1">300</a></li>
                </ul>
        </span> 条记录</span>
    </div>
</span>






                <?php endif; ?>
                <div class="batch-bar clearfix">
                </div>
            </div>
        </section>
        <aside class="control-sidebar control-sidebar-dark">
            <ul class="nav nav-tabs nav-justified control-sidebar-tabs">
            </ul>
            <div class="tab-content">
                <div class="tab-pane" id="control-sidebar-home-tab"></div>
            </div>
        </aside>
        <div class="control-sidebar-bg"></div>
    </div>
</div>

<!-- jQuery 3 -->
<script src="/assets/components/jquery/dist/jquery.min.js"></script>
<!-- Bootstrap 3.3.7 -->
<script src="/assets/components/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- daterangepicker -->
<script src="/assets/components/moment/min/moment.min.js"></script>
<script src="/assets/components/bootstrap-daterangepicker/daterangepicker.js"></script>
<!-- datepicker -->
<script src="/assets/components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
<!--bootstrap-select-->
<script src="/assets/components/bootstrap-select/js/bootstrap-select.js"></script>
<!--jstree-->
<script src="/assets/components/jstree/jstree.min.js"></script>
<!-- ValidateForm -->
<script src="/assets/components/nice-validator/dist/jquery.validator.min.js?local=zh-CN"></script>
<!-- Slimscroll -->
<script src="/assets/components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
<!-- Scrolltofixed -->
<script src="/assets/components/scrolltofixed/jquery-scrolltofixed-min.js"></script>
<!-- iCheck 1.0.1 -->
<script src="/assets/plugins/iCheck/icheck.min.js"></script>
<!-- AdminLTE App -->
<script src="/assets/dist/js/adminlte.min.js"></script>
<script src="/assets/plugins/layer/layer.js"></script>
<script src="/assets/plugins/lazyload.js"></script>
<!-- datatables -->
<script type="text/javascript" src="/assets/components/datatables/datatables.min.js"></script>
<!-- <script src="/assets/dist/js/jquery.floatThead.min.js"></script> -->
<script src="/assets/dist/js/default.js"></script>
<script src="/assets/dist/js/common.js"></script>
<script src="/assets/dist/js/<?php echo $config['modulename']; ?>.js"></script>
<!--<button id="btn_top" title="回到顶部"><span class="glyphicon glyphicon-chevron-up"></span></button>-->
<script>
    $(function (){
        var btnTop = "#btn_top";
        $(window).scroll(function () {
            if ($(window).scrollTop() >= 50) {
                $(btnTop).fadeIn();
            }
            else {
                $(btnTop).fadeOut();
            }
        });
        $(btnTop).click(function () {
            $('html,body').animate({scrollTop: 0}, 500);
        });
        //在初始化和窗口改变大小时，重置main的高度
        var window_height = $(window).height();
        var header_height = $('#header').height();
        $('#main').height(window_height - header_height - 5);
        $(window).resize(function () {
            window_height = $(window).height();
            var header_height = $('#header').height();
            $('#main').height(window_height - header_height - 5);
        });
    });
</script>


<script src="/assets/plugins/echarts/echarts.min.js"></script>
<!-- 用来 添加自定义 的 js -->
<script>
    var page_str = '';
    var page_len = 20;
    var is_page = 1;
    var order = [0, 'desc'];
    var is_order = 1;
//    组织架构和 平台不进行排序
    <?php if(isset($params['type']) && (in_array($params['type'], ['organ']))): ?>
        var order = [];
    <?php endif; if(isset($params['type']) && (in_array($params['type'], ['account', 'cat', 'organ', 'seller', 'developer', 'country', 'store']))): ?>
        var is_page = -1;
        var is_order = -1;
    <?php endif; ?>

    common_module.init_data_table(page_str, order, page_len, is_page, is_order);

    // 针对414 错误
    common_module.init_submit_form('manual_submit_form');

    <?php if(isset($params['type']) && !in_array($params['type'], ['platform'])): ?>
    // $('#scroll_table').floatThead({
    //     autoReflow: true,
    //     zIndex: 0
    // });
    <?php endif; ?>
</script>

</body>
</html>