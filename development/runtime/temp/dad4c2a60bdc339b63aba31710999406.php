<?php if (!defined('THINK_PATH')) exit(); /*a:13:{s:86:"/opt/web/count/development/public/../application/count/view/order/preprofit/index.html";i:1544600061;s:69:"/opt/web/count/development/application/count/view/layout/default.html";i:1544600061;s:78:"/opt/web/count/development/application/count/view/order/preprofit/_layout.html";i:1547190894;s:66:"/opt/web/count/development/application/count/view/common/meta.html";i:1544600061;s:68:"/opt/web/count/development/application/count/view/common/header.html";i:1548384832;s:80:"/opt/web/count/development/application/count/view/order/preprofit/_tabs_bar.html";i:1547190894;s:76:"/opt/web/count/development/application/count/view/order/preprofit/_form.html";i:1544600061;s:81:"/opt/web/count/development/application/count/view/order/preprofit/table_date.html";i:1550224951;s:82:"/opt/web/count/development/application/count/view/order/preprofit/table_organ.html";i:1550224951;s:76:"/opt/web/count/development/application/count/view/order/preprofit/table.html";i:1550224951;s:76:"/opt/web/count/development/application/count/view/order/preprofit/chart.html";i:1544600061;s:68:"/opt/web/count/development/application/count/view/common/script.html";i:1548747211;s:69:"/opt/web/count/development/application/count/view/layout/btn_top.html";i:1544600061;}*/ ?>
<!DOCTYPE html>
<html lang="<?php echo $config['language']; ?>">
<head>
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
<!-- iCheck for checkboxes and radio inputs -->
<link rel="stylesheet" href="/assets/plugins/iCheck/all.css">
<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!-- Font -->
<link rel="stylesheet" href="/assets/dist/css/fontcss.css">
<link rel="stylesheet" href="/assets/dist/css/style.css">
<link rel="stylesheet" href="/assets/plugins/datatables/dataTables.bootstrap.css">
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
            background-color: white!important;
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
                                    <!--<div class="pull-right">-->
                                        <!--<a href="<?php echo url('/count/login/logout'); ?>" class="btn btn-danger"><i class="fa fa-sign-out"></i>-->
                                            <!--<?php echo __('Logout'); ?></a>-->
                                    <!--</div>-->
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
    <li class="<?php echo $type=='date'?'active' : ''; ?>"><a href="<?php echo url('/count/order/' . $profit_type, ['type' => 'date']); ?>">按日期</a></li>
    <?php if($is_top_manager): ?>
    <li class="<?php echo $type=='organ'?'active' : ''; ?>"><a href="<?php echo url('/count/order/' . $profit_type, ['type' => 'organ']); ?>">按组织架构</a></li>
    <?php endif; ?>
    <li class="<?php echo $type=='platform'?'active' : ''; ?>"><a href="<?php echo url('/count/order/' . $profit_type, ['type' => 'platform']); ?>">按平台</a></li>
    <li class="<?php echo $type=='account'?'active' : ''; ?>"><a href="<?php echo url('/count/order/' . $profit_type, ['type' => 'account']); ?>">按账号</a></li>
    <li class="<?php echo $type=='seller'?'active' : ''; ?>"><a href="<?php echo url('/count/order/' . $profit_type, ['type' => 'seller']); ?>">按销售员</a></li>
    <li class="pull-right">
        <div class="btn-toolbar" role="toolbar">
            <div class="btn-group padding-top8 paddint-right5">
                <?php if($params['type'] != 'organ'): ?>
                <a href="<?php echo url('/count/order/' . $profit_type, array_merge($params, ['type' => $type, 'model' => 'table'])); ?>" data-toggle="tooltip" class="btn btn-xs btn-default <?php echo $model=='table'?'active' : ''; ?>"
                   title="列表模式"><span class="fa fa-fw fa-th-large"></span></a>

                <a href="<?php echo url('/count/order/' . $profit_type, array_merge($params,['type' => $type, 'model' => 'chart'])); ?>" data-toggle="tooltip" class="btn btn-xs btn-default <?php echo $model=='chart'?'active' : ''; ?>" title="图表模式"><span class="fa fa-fw fa-bar-chart-o"></span></a>
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
                            <form action="<?php echo url('/count/order/' . $profit_type, array_merge($params,['type' => $type, 'model' => $model])); ?>" method="get" class="form-inline froms clearfix">

    <input type="hidden" name="type" value="<?php echo $params['type']; ?>">
    <input type="hidden" name="model" value="<?php echo $params['model']; ?>">
    <input type="hidden" name="ps" value="<?php echo $params['ps']; ?>">
    <input type="hidden" name="p" value="<?php echo $params['p']; ?>">


    <?php if(in_array($params['type'], ['account', 'platform'])): ?>
    <div class="form-group" style="width: 206px;">
        <label class="control-label text-right">平台：</label>
        <select class="selectpicker platform-choice" title="全部" name="platform" id="platform" data-actions-box="true" data-live-search="true">
            <option value="">全部</option>
            <?php foreach($platforms as $item): ?>
            <option value="<?php echo $item; ?>" <?php if($params['platform'] == $item): ?>selected<?php endif; ?>><?php echo $item; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; if(in_array($params['type'], ['account'])): ?>
    <div class="form-group" style="width: 206px;">
        <label class="control-label text-right">账号：</label>
        <select class="selectpicker account-choice" title="全部" name="account[]" id="account" data-actions-box="true" data-live-search="true" multiple>

            <?php foreach($account_list as $item): ?>
            <option value="<?php echo $item; ?>" <?php if(in_array($item, $params['account'] ?? [])): ?>selected<?php endif; ?>><?php echo $item; ?></option>
            <?php endforeach; ?>

        </select>
    </div>
    <?php endif; if(in_array($params['type'], ['seller'])): ?>
    <label class="control-label text-right">部门：</label>
    <select class="selectpicker" title="请选择" name="organ[]" id="organ" data-actions-box="false" data-live-search="true" onchange="order_model.change_org($(this))">
        <?php foreach($org_list as $item): ?>
        <option value="<?php echo $item['id']; ?>" <?php if(in_array($item['id'], $params['organ'] ?? [])): ?>selected<?php endif; ?>><?php echo $item['name']; ?></option>
        <?php echo $item['name']; ?>
        </option>
        <?php endforeach; ?>
    </select>

    <label class="control-label text-right">销售员：</label>
    <select class="selectpicker" title="全部" name="seller[]" id="seller" data-actions-box="false" data-live-search="true" data-max-options="20" multiple>
        <?php foreach($sellers as $item): ?>
        <option value="<?php echo $item; ?>" <?php if(in_array($item, $params['seller'] ?? [])): ?>selected<?php endif; ?>><?php echo $item; ?></option>
        <?php endforeach; ?>
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
            <input type="text" class="input-sm form-control input-date datepicker <?php if($params['checkDate'] != 'day'): ?>hide<?php endif; ?>" name="scantime_start"  placeholder="开始时间" value="<?php echo $params['scantime_start']; ?>" readonly/>
            <input type="text" class="input-sm form-control input-date monthpicker <?php if($params['checkDate'] != 'month'): ?>hide<?php endif; ?>" name="scandate_start"  placeholder="开始年月" value="<?php echo $params['scandate_start']; ?>" readonly/>
            <span class="input-group-addon">到</span>
            <input type="text" class="input-sm form-control input-date datepicker <?php if($params['checkDate'] != 'day'): ?>hide<?php endif; ?>" name="scantime_end" value="<?php echo $params['scantime_end']; ?>" placeholder="结束时间" readonly/>
            <input type="text" class="input-sm form-control input-date monthpicker <?php if($params['checkDate'] != 'month'): ?>hide<?php endif; ?>" name="scandate_end" value="<?php echo $params['scandate_end']; ?>" placeholder="结束年月" readonly/>
        </div>
    </div>
    <div class="checkbox shotcut_day_div" <?php if($params['checkDate'] == 'month'): ?>style="display:none;"<?php endif; ?>>
    <div class="input-group laydate-group">
        <label><input  name="short_cut_day" type="radio" value="1" class=""><small>本月</small></label>
        <label><input name="short_cut_day" type="radio" value="2" class=""><small>最近7天</small></label>
        <label><input name="short_cut_day" type="radio" value="3" class=""><small>最近15天</small></label>
    </div>
    </div>
    <div class="form-group">
        <button class="btn btn-primary btn-sm" type="submit" name="submit"><i class="glyphicon glyphicon-search"></i> 确定搜索&nbsp;</button>
        <a class="btn btn-warning btn-sm" href="javascript:void(0);" target="_blank" onclick="common_module.export_excel()"><i class="glyphicon glyphicon-save"></i> 导出Excel&nbsp;</a>
    </div>

</form>
                        </div>
                    </div>
                </div>

                <div class="tab-content">
                    <?php if($model == 'table'): if($params['type'] == 'date'): ?>
                    <div class="text-danger">
<?php if($profit_type == 'confirmprofit'): ?>
    <i class="icon fa fa-warning"></i> 说明：确认利润数据根据已发货订单的进系统时间统计
<?php else: ?>
    <i class="icon fa fa-warning"></i> 说明：预估利润数据根据订单进系统时间来统计
<?php endif; ?>
</div>
<table class="table table-bordered table-hover dataTable table-striped js-table" id="scroll_table">
    <thead>
    <tr>
        <td class="text-center table-total">合计</td>
        <td class="text-center table-total"><?php echo $total_data['sum_totals']; ?></td>
        <td class="text-center table-total"><?php echo $total_data['sum_ships']; ?></td>
        <?php if($profit_type == 'confirmprofit'): ?>
        <td class="text-center table-total"><?php echo $total_data['sum_profit_totals']; ?></td>
        <?php endif; ?>
        <td class="text-center table-total"><?php echo $total_data['sum_sales']; ?></td>
        <td class="text-center table-total"><?php echo $total_data['sum_profit']; ?></td>
        <td class="text-center table-total"></td>
        <?php if($params['checkDate'] == 'day'): ?>
        <td class="text-center table-total"></td>
        <?php endif; ?>
        <td class="text-center table-total"></td>
        <?php if($params['checkDate'] == 'day'): ?>
        <td class="text-center table-total"></td>
        <?php endif; ?>
        <td class="text-center table-total"></td>
        <td class="text-center table-total"></td>
    </tr>
    <tr id='scroll_table_head'>
        <th class="text-center">日期</th>
        <th class="text-center">总订单数 <a href="javascript:void(0);" data-toggle="tooltip" data-placement="bottom" title="总订单数=系统内所有订单数(包含了作废订单和补发订单)"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th class="text-center">已发货单量 <a href="javascript:void(0);" data-toggle="tooltip" data-placement="bottom" title="已发货单量=当天进系统并已发货的订单总数"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <?php if($profit_type == 'confirmprofit'): ?>
        <th class="text-center">已确认利润单量<a href="javascript:void(0);" data-toggle="tooltip" data-placement="bottom" title="确认利润单量=当天进系统已发货并已确认利润订单数"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <?php endif; ?>
        <th class="text-center">销售额 ($) <a href="javascript:void(0);" data-toggle="tooltip" data-placement="bottom" title="销售额=当天进系统并已发货的订单总金额"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <?php if($profit_type == 'confirmprofit'): ?>
        <th class="text-center">确认利润($) <a href="javascript:void(0);" data-toggle="tooltip" data-placement="bottom" title="确认利润=销售额-物品成本-包材成本-运输成本（线上运费+线下运费）-PayPal转换费-提款费-佣金-其它"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <?php else: ?>
        <th class="text-center">预估利润($) <a href="javascript:void(0);" data-toggle="tooltip" data-placement="bottom" title="预估利润=销售额-物品成本-包材成本-预估运输成本（线上运费+线下运费）-PayPal转换费-提款费-佣金-其它"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <?php endif; ?>
        <th class="text-center">利润率(%)</th>
        <?php if($params['checkDate'] == 'day'): ?>
        <th class="text-center">上周同期利润</th>
        <?php endif; ?>
        <th class="text-center">上月同日利润</th>
        <?php if($params['checkDate'] == 'day'): ?>
        <th class="text-center">上周同期利润率(%)</th>
        <?php endif; ?>
        <th class="text-center">上月同日利润率(%)</th>
        <th class="text-center">利润环比增长率(%)</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach($list as $key => $value): ?>
    <tr>
        <td class="text-center"><?php echo $key; ?></td>
        <td class="text-center"><?php echo $value['sum_totals']; ?></td>
        <td class="text-center"><?php echo $value['sum_ships']; ?></td>
        <?php if($profit_type == 'confirmprofit'): ?>
        <td class="text-center"><?php echo $value['sum_profit_totals']; ?></td>
        <?php endif; ?>
        <td class="text-center"><?php echo $value['sum_sales']; ?></td>
        <td class="text-center"><?php echo $value['sum_profit']; ?></td>
        <td class="text-center <?php if(intval($value['profit_rate']) > 0): ?>table-td-color-green<?php endif; if(intval($value['profit_rate']) < 0): ?>table-td-color-red<?php endif; ?>"><?php echo $value['profit_rate']; ?></td>
        <?php if($params['checkDate'] == 'day'): ?>
        <td class="text-center"><?php echo $value['last_week_profit']; ?></td>
        <?php endif; ?>
        <td class="text-center"><?php echo $value['last_month_profit']; ?></td>
        <?php if($params['checkDate'] == 'day'): ?>
        <td class="text-center <?php if(intval($value['last_week_profit_rate']) > 0): ?>table-td-color-green<?php endif; if(intval($value['last_week_profit_rate']) < 0): ?>table-td-color-red<?php endif; ?>"><?php echo $value['last_week_profit_rate']; ?></td>
        <?php endif; ?>
        <td class="text-center <?php if(intval($value['last_month_profit_rate']) > 0): ?>table-td-color-green<?php endif; if(intval($value['last_month_profit_rate']) < 0): ?>table-td-color-red<?php endif; ?>"><?php echo $value['last_month_profit_rate']; ?></td>
        <td class="text-center <?php if(intval($value['loop_profit_rate']) > 0): ?>table-td-color-green<?php endif; if(intval($value['loop_profit_rate']) < 0): ?>table-td-color-red<?php endif; ?>"><?php echo $value['loop_profit_rate']; ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>

                    <?php elseif($params['type'] == 'organ'): ?>
                    <table class="table table-bordered table-hover dataTable table-striped <?php if($params['type'] != 'organ'): ?>js-table<?php endif; ?>" id="scroll_table">
    <thead>

    <?php if($params['type'] == 'organ'  && !empty($total_data)): ?>
      <!--合计-->
      <tr>
        <td class="text-center table-total">合计</td>
        <td class="text-center table-total"></td>
        <?php foreach($range as $v): ?>
        <td class="text-center table-total">
            <span class="block" data-toggle="tooltip" data-placement="bottom" title="总订单数:<?php echo $total_data[$v]['sum_totals']; ?>&nbsp;&nbsp;已发货数:<?php echo $total_data[$v]['sum_ships']; ?>&nbsp;&nbsp;发货率:<?php echo $total_data[$v]['ship_rate']; ?>%&nbsp;&nbsp;销售额:$<?php echo $total_data[$v]['sum_sales']; ?>&nbsp;&nbsp;利润:$<?php echo $total_data[$v]['sum_profit']; ?>&nbsp;&nbsp;利润率:<?php echo $total_data[$v]['profit_rate']; ?>&nbsp;&nbsp;客单价$:<?php echo getRound($total_data[$v]['sum_sales'],$total_data[$v]['sum_totals']); ?>">
                <span class="text-primary"><?php echo $total_data[$v]['sum_sales']; ?></span>
                <!--<small class="text-primary small">(<?php echo $total_data[$v]['ship_rate']; ?>)</small>--> <br> <?php echo $total_data[$v]['sum_profit']; ?> <br> <span class="error small"><?php echo $total_data[$v]['profit_rate']; ?></span>
                </span>
        </td>
        <?php endforeach; ?>
        <td class="text-center table-total"></td>
        <td class="text-center table-total"></td>
    </tr>

    <?php endif; ?>
    <tr id='scroll_table_head'>
        
        <th class="<?php if($params['type'] == 'organ'): ?>text-left<?php else: ?>text-center<?php endif; ?>" style="width: 10%;">
            组织架构
        </th>
        <th class="text-center table-total">合计</th>

        <?php foreach($range as $item): ?>
        <th class="text-center"><?php echo $item; ?></th>
        <?php endforeach; ?>
        <th class="text-center">当前平均利润</th>
        <th class="text-center">当前平均利润率</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach($list as $key => $item): ?>
    <tr class="<?php if($params['type'] == 'organ' && $item['level']==2): ?>warning<?php endif; if($item['level'] == 3 && $item['lid'] >= 48 && $item['rid'] <= 71): ?>shallow_warning<?php endif; ?>">
        <td class="<?php if($params['type'] == 'organ'): ?>text-left<?php else: ?>text-center<?php endif; ?>">
            <?php if($params['type'] == 'organ'): if($item['level'] > 2): ?>
                |<?php echo str_repeat('---', ($item['level'] - 1)); endif; ?>
                <?php echo $item['organ_name']; else: ?>
                <?php echo $key; endif; ?>
        </td>
        <td class="text-center table-total"><b><?php echo $item['all_profit']; ?></b></td>

        <?php foreach($item['dates'] as $k => $sub_item): ?>
            <td class="text-center">
                    <span class="block" data-toggle="tooltip" data-placement="bottom" title="总订单数:<?php echo $sub_item['sum_totals']; ?>&nbsp;&nbsp;已发货数:<?php echo $sub_item['sum_ships']; ?>&nbsp;&nbsp;发货率:<?php echo $sub_item['ship_rate']; ?>%&nbsp;&nbsp;销售额:$<?php echo $sub_item['sum_sales']; ?>&nbsp;&nbsp;利润:$<?php echo $sub_item['sum_profit']; ?>&nbsp;&nbsp;利润率:<?php echo $sub_item['profit_rate']; ?>&nbsp;&nbsp;客单价$:<?php echo getRound($sub_item['sum_sales'],$sub_item['sum_totals']); ?>">
                    <span class="text-primary"><?php echo $sub_item['sum_sales']; ?></span>
                    <!--<small class="text-primary small">(<?php echo $sub_item['ship_rate']; ?>)</small>--> <br> <?php echo $sub_item['sum_profit']; ?> <br> <span class="error small"><?php echo $sub_item['profit_rate']; ?></span>
                    </span>
            </td>
        <?php endforeach; ?>

        <td class="text-center"><?php echo $item['average_profit']; ?></td>
        <td class="text-center"><?php echo $item['average_profit_rate']; ?></td>

    </tr>
    <?php endforeach; ?>

    </tbody>
</table>

                    <?php else: ?>
                    <table class="table table-bordered table-hover dataTable table-striped <?php if($params['type'] != 'organ'): ?>js-table<?php endif; ?>" id="scroll_table">
    <thead>
    <?php if(($params['type'] == 'platform' || $params['type'] == 'account') && !empty($total_data)): ?>
    <!--合计-->
    <tr>
        <td class="text-center table-total">合计</td>
        <?php foreach($range as $v): ?>
        <td class="text-center table-total">
            <span class="block" data-toggle="tooltip" data-placement="bottom" title="总订单数:<?php echo $total_data[$v]['sum_totals']; ?>&nbsp;&nbsp;已发货数:<?php echo $total_data[$v]['sum_ships']; ?>&nbsp;&nbsp;发货率:<?php echo $total_data[$v]['ship_rate']; ?>%&nbsp;&nbsp;销售额:$<?php echo $total_data[$v]['sum_sales']; ?>&nbsp;&nbsp;利润:$<?php echo $total_data[$v]['sum_profit']; ?>&nbsp;&nbsp;利润率:<?php echo $total_data[$v]['profit_rate']; ?>&nbsp;&nbsp;客单价$:<?php echo getRound($total_data[$v]['sum_sales'],$total_data[$v]['sum_totals']); ?>">
                <span class="text-primary"><?php echo $total_data[$v]['sum_sales']; ?></span>
                <!--<small class="text-primary small">(<?php echo $total_data[$v]['ship_rate']; ?>)</small>--> <br> <?php echo $total_data[$v]['sum_profit']; ?> <br> <span class="error small"><?php echo $total_data[$v]['profit_rate']; ?></span>
            </span>
        </td>
        <?php endforeach; ?>
        <td class="text-center table-total"></td>
        <td class="text-center table-total"></td>
        <td class="text-center table-total"></td>
    </tr>
    <?php endif; if($params['type'] == 'seller' && !empty($total_data)): ?>
    <!--合计-->
    <tr>
        <td class="text-center table-total">合计</td>
        <td class="text-center table-total"></td>
        <?php foreach($range as $v): ?>
        <td class="text-center table-total">
            <span class="block" data-toggle="tooltip" data-placement="bottom" title="总订单数:<?php echo $total_data[$v]['sum_totals']; ?>&nbsp;&nbsp;已发货数:<?php echo $total_data[$v]['sum_ships']; ?>&nbsp;&nbsp;发货率:<?php echo $total_data[$v]['ship_rate']; ?>%&nbsp;&nbsp;销售额:$<?php echo $total_data[$v]['sum_sales']; ?>&nbsp;&nbsp;利润:$<?php echo $total_data[$v]['sum_profit']; ?>&nbsp;&nbsp;利润率:<?php echo $total_data[$v]['profit_rate']; ?>&nbsp;&nbsp;客单价$:<?php echo getRound($total_data[$v]['sum_sales'],$total_data[$v]['sum_totals']); ?>">
                <span class="text-primary"><?php echo $total_data[$v]['sum_sales']; ?></span>
                <!--<small class="text-primary small">(<?php echo $total_data[$v]['ship_rate']; ?>)</small>--> <br> <?php echo $total_data[$v]['sum_profit']; ?> <br> <span class="error small"><?php echo $total_data[$v]['profit_rate']; ?></span>
                </span>
            </td>
        <?php endforeach; ?>
        <td class="text-center table-total"></td>
        <td class="text-center table-total"></td>
        <td class="text-center table-total"></td>
    </tr>
    <?php endif; ?>

    <tr id='scroll_table_head'>
        <?php if($params['type'] == 'seller'): ?>
        <th class="text-center">组织架构</th>
        <?php endif; ?>
        <th class="<?php if($params['type'] == 'organ'): ?>text-left<?php else: ?>text-center<?php endif; ?>" style="width: 10%;">
            <?php if($params['type'] == 'platform'): ?>
            平台
            <?php elseif($params['type'] == 'seller'): ?>
            销售员
            <?php elseif($params['type'] == 'organ'): ?>
            组织架构
            <?php else: ?>
            账号
            <?php endif; ?>
        </th>
        <?php foreach($range as $item): ?>
        <th class="text-center"><?php echo $item; ?></th>
        <?php endforeach; ?>
        <th class="text-center table-total">合计</th>
        <th class="text-center">当前平均利润</th>
        <th class="text-center">当前平均利润率</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach($list as $key => $item): ?>
    <tr class="<?php if($params['type'] == 'organ' && $item['level']==2): ?>warning<?php endif; ?>">
        <?php if($params['type'] == 'seller' && $params['checkDate'] == 'day'): ?>
        <td class="text-left"><?php echo (isset($item[$params['scantime_end']]['org_parent_name']) && ($item[$params['scantime_end']]['org_parent_name'] !== '')?$item[$params['scantime_end']]['org_parent_name']:'-'); ?>
            <span class="error"><?php echo (isset($item[$params['scantime_end']]['org_name']) && ($item[$params['scantime_end']]['org_name'] !== '')?$item[$params['scantime_end']]['org_name']:'-'); ?></span>
        </td>
        <?php endif; if($params['type'] == 'seller' && $params['checkDate'] == 'month'): ?>
        <td class="text-left"><?php echo (isset($item[$params['scandate_end']]['org_parent_name']) && ($item[$params['scandate_end']]['org_parent_name'] !== '')?$item[$params['scandate_end']]['org_parent_name']:'-'); ?>
        <span class="text-danger"><?php echo (isset($item[$params['scandate_end']]['org_name']) && ($item[$params['scandate_end']]['org_name'] !== '')?$item[$params['scandate_end']]['org_name']:'-'); ?></span>
        </td>
        <?php endif; ?>
        <td class="<?php if($params['type'] == 'organ'): ?>text-left<?php else: ?>text-center<?php endif; ?>">
            <?php if($params['type'] == 'seller'): ?>
            <?php echo trim(mb_substr($key, 0, 3), '_'); else: ?><?php echo $key; endif; ?>
        </td>

        <?php foreach($item as $k => $sub_item): ?>
        <td class="text-center">
            <span class="block" data-toggle="tooltip" data-placement="bottom" title="总订单数:<?php echo $sub_item['sum_totals']; ?>&nbsp;&nbsp;已发货数:<?php echo $sub_item['sum_ships']; ?>&nbsp;&nbsp;发货率:<?php echo $sub_item['ship_rate']; ?>%&nbsp;&nbsp;销售额:$<?php echo $sub_item['sum_sales']; ?>&nbsp;&nbsp;利润:$<?php echo $sub_item['sum_profit']; ?>&nbsp;&nbsp;利润率:<?php echo $sub_item['profit_rate']; ?>">
            <span class="text-primary"><?php echo $sub_item['sum_sales']; ?></span>
            <!--<small class="text-primary small">(<?php echo $sub_item['ship_rate']; ?>)</small>--> <br> <?php echo $sub_item['sum_profit']; ?> <br> <span class="error small"><?php echo $sub_item['profit_rate']; ?></span>
                </span>
            </td>
            <?php endforeach; ?>

            <td class="text-center table-total"><b><?php echo $date_total_map[$key]['sum_profit']; ?></b></td>
            <td class="text-center"><?php echo $date_aver_map[$key]['average_profit']; ?></td>
            <td class="text-center"><?php echo $date_aver_map[$key]['average_profit_rate']; ?></td>

        </tr>
        <?php endforeach; ?>

        </tbody>
    </table>

                    <?php endif; else: ?>
                    <script src="/assets/dist/js/common.js"></script>
<script src="/assets/plugins/echarts/echarts.min.js"></script>
<div id="chartmain" style="width:100%; height:780px;"></div>
<script type="application/javascript">
    // 基于准备好的dom，初始化echarts实例
    var x_data = '<?php echo $x_data; ?>';
    x_data = JSON.parse(x_data);
    var y_data = '<?php echo $y_data; ?>';
    y_data = JSON.parse(y_data);
    var x_data_names = '<?php echo $x_data_names; ?>';
    x_data_names = JSON.parse(x_data_names);

    var legend_data = x_data_names;
//    var y_name = '数量';
    var yAxis = [
        {type: 'value', name: '数量'},
        {type: 'value', name: '销售额'},
    ];

    var chart_type = '<?php echo $chart_type; ?>';

    var params_type = "<?php echo $params['type']; ?>";
    var controller_name = "<?php echo request()->controller(); ?>";

    common_module.show_colomn_chart(chart_type, x_data, y_data, legend_data, yAxis, 'chartmain', params_type, controller_name);
</script>

                    <?php endif; ?>
                </div>

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
<script src="/assets/plugins/datatables/jquery.dataTables.js"></script>
<script src="/assets/plugins/datatables/dataTables.bootstrap.js"></script>
<script src="/assets/dist/js/jquery.floatThead.min.js"></script>
<script src="/assets/dist/js/default.js"></script>
<script src="/assets/dist/js/common.js"></script>
<script src="/assets/dist/js/<?php echo $config['modulename']; ?>.js"></script>

<!-- 这个文件结构，有问题，不能给单个html文件加js -->
<script>
    // var page_str = '总共找到 <strong class="text-danger"><?php echo isset($list_total)?$list_total: ''; ?></strong> 条记录';
    var page_str = '';
    var order = [0, 'desc'];
//    组织架构和 平台不进行排序
    <?php if(isset($params['type']) && (in_array($params['type'], ['organ', 'store']))): ?>
        var order = [];
    <?php endif; ?>
    common_module.init_data_table(page_str, order);

    // 针对414 错误
    common_module.init_submit_form('manual_submit_form');

    <?php if(isset($params['type']) && !in_array($params['type'], ['platform'])): ?>
    $('#scroll_table').floatThead({
        autoReflow: true,
        zIndex: 0
    });
    <?php endif; ?>
</script>
<button id="btn_top" title="回到顶部"><span class="glyphicon glyphicon-chevron-up"></span></button>

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


<!-- 用来 添加自定义 的 js -->
<script>
    // table 的th 悬停
    // $(function () {
    //     var table = $("#scroll_table");//表格的id
    //     var table_head = $("#scroll_table_head");//表头
    //     var table_head_height = table_head.height();//表头高
    //     var table_head_offset = table_head.offset();
    //     var table_head_offset_top = table_head_offset.top;

    //     // 只运行一次
    //     var is_first_time = true;

    //     var clone_table = table.clone().attr('id', 'bb');// 更改复制的表格id
    //     clone_table.find('thead tr').first().remove();
    //     window.onscroll = function () {
    //         var scroll_top = document.body.scrollTop == 0 ? document.documentElement.scrollTop : document.body.scrollTop;

    //         var nav_head_height = $('.main-header').height();

    //         if (scroll_top - table_head_offset_top > 0) {
    //             if (is_first_time) {
    //                 $('body').append('<div id="shelter"></div>');//复制的表格所在的容器
    //                 $("#shelter").css({
    //                     'height': table_head_height,
    //                     'position': 'fixed',
    //                     'top': nav_head_height,
    //                     'overflow': 'hidden',
    //                     'margin-left': '230px',
    //                     'padding': '0 20px'
    //                 });

    //                 clone_table.appendTo('#shelter');
    //                 $('#shelter table').removeClass(); //删除table原来有的默认class，防止margin,padding等值影响样式
    //                 $('#shelter table').css({'width': '100%', 'background-color': '#d2d6de'});
    //                 $('#shelter table tr th').css({'height': table_head_height, 'width': '140px', 'border': '1px solid #fff'});//此处可以自行发挥
    //                 $('#shelter table tr td').css({'padding': '10px', 'text-align': 'center'});

    //                 var ths = table.find('th');
    //                 var clone_table_ths = clone_table.find('th');
    //                 for (var i = 0; i < ths.length; i++) {
    //                     if (clone_table_ths[i].offsetWidth != ths[i].offsetWidth) {
    //                         $(clone_table_ths[i]).css('width', ths[i].offsetWidth + 'px');
    //                     }
    //                 }
    //                 is_first_time = false;

    //             }

    //             $('#shelter').show();
    //         } else {
    //             $('#shelter').hide();
    //         }
    //     }

    // });

</script>

</body>
</html>