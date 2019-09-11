<?php if (!defined('THINK_PATH')) exit(); /*a:9:{s:82:"E:\web\count\development\public/../application/count\view\sku\index\index_sku.html";i:1544600060;s:67:"E:\web\count\development\application\count\view\layout\default.html";i:1544600061;s:64:"E:\web\count\development\application\count\view\common\meta.html";i:1544600061;s:66:"E:\web\count\development\application\count\view\common\header.html";i:1544600061;s:68:"E:\web\count\development\application\count\view\sku\index\table.html";i:1544600060;s:68:"E:\web\count\development\application\count\view\sku\index\chart.html";i:1544600060;s:64:"E:\web\count\development\application\count\view\layout\page.html";i:1544600061;s:66:"E:\web\count\development\application\count\view\common\script.html";i:1544600061;s:67:"E:\web\count\development\application\count\view\layout\btn_top.html";i:1544600061;}*/ ?>
<!DOCTYPE html>
<html lang="<?php echo $config['language']; ?>">
<head>
    <!-- 加载样式及META信息 -->
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
        <!-- Sidebar toggle button-->
        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>
        <div class="collapse navbar-collapse pull-left" id="navbar-collapse">
            <ul class="nav navbar-nav">
                <li><a href="http://erp.spocoo.com/t.php?s=/Home/Main">首页</a></li>
                <li class="dropdown active">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" title="统计报表">报表 <span class="caret"></span></a>
                    <ul class="dropdown-menu" role="menu">
                        <?php echo build_menu('/count/order/index', '订单状态报表','fa fa-fw fa-angle-right'); ?>
                        <?php echo build_menu('/count/order/sale', '销售额报表','fa fa-fw fa-angle-right'); ?>
                        <?php echo build_menu('/count/order/preprofit', '预利润报表','fa fa-fw fa-angle-right'); ?>
                        <li class="divider"></li>
                        <?php echo build_menu('/count/sku/index', 'SKU销量报表','fa fa-fw fa-angle-right'); ?>
                        <?php echo build_menu('/count/sku/packages', 'SKU产品包销量报表','fa fa-fw fa-angle-right'); ?>
                        <li class="divider"></li>
                        <?php echo build_menu('/count/finance/index', '收支报表','fa fa-fw fa-angle-right'); ?>
                        <?php echo build_menu('/count/finance/refund', '售后报表','fa fa-fw fa-angle-right'); ?>
                        <?php echo build_menu('/count/order/confirmprofit', '确认利润报表','fa fa-fw fa-angle-right'); ?>
                        <li class="divider"></li>
                        <?php echo build_menu('/count/transport/index', '物流对账报表','fa fa-fw fa-angle-right'); ?>
                        <?php echo build_menu('/count/transport/expense', '物流支出报表','fa fa-fw fa-angle-right'); ?>
                        <li class="divider"></li>
                        <?php echo build_menu('/count/purchase/index', '应付款报表','fa fa-fw fa-angle-right'); ?>
                        <?php echo build_menu('/count/purchase/flow', '付款流水','fa fa-fw fa-angle-right'); ?>
                        <?php echo build_menu('/count/purchase/flow/byAccount', '付款流水账号汇总','fa fa-fw fa-angle-right'); ?>
                    </ul>
                </li>
            </ul>
        </div>
        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
                <!-- Messages: style can be found in dropdown.less-->
                <!--<li class="dropdown messages-menu">-->
                <!--<a href="#" class="dropdown-toggle" data-toggle="dropdown">-->
                <!--<i class="fa fa-envelope-o"></i>-->
                <!--<span class="label label-success">4</span>-->
                <!--</a>-->
                <!--<ul class="dropdown-menu">-->
                <!--<li class="header">You have 4 messages</li>-->
                <!--<li>-->
                <!--&lt;!&ndash; inner menu: contains the actual data &ndash;&gt;-->
                <!--<ul class="menu">-->
                <!--<li>&lt;!&ndash; start message &ndash;&gt;-->
                <!--<a href="#">-->
                <!--<div class="pull-left">-->
                <!--<img src="/assets/dist/img/user2-160x160.jpg" class="img-circle" alt="User Image">-->
                <!--</div>-->
                <!--<h4>-->
                <!--Support Team-->
                <!--<small><i class="fa fa-clock-o"></i> 5 mins</small>-->
                <!--</h4>-->
                <!--<p>Why not buy a new awesome theme?</p>-->
                <!--</a>-->
                <!--</li>-->
                <!--&lt;!&ndash; end message &ndash;&gt;-->
                <!--<li>-->
                <!--<a href="#">-->
                <!--<div class="pull-left">-->
                <!--<img src="/assets/dist/img/user3-128x128.jpg" class="img-circle" alt="User Image">-->
                <!--</div>-->
                <!--<h4>-->
                <!--AdminLTE Design Team-->
                <!--<small><i class="fa fa-clock-o"></i> 2 hours</small>-->
                <!--</h4>-->
                <!--<p>Why not buy a new awesome theme?</p>-->
                <!--</a>-->
                <!--</li>-->
                <!--<li>-->
                <!--<a href="#">-->
                <!--<div class="pull-left">-->
                <!--<img src="/assets/dist/img/user4-128x128.jpg" class="img-circle" alt="User Image">-->
                <!--</div>-->
                <!--<h4>-->
                <!--Developers-->
                <!--<small><i class="fa fa-clock-o"></i> Today</small>-->
                <!--</h4>-->
                <!--<p>Why not buy a new awesome theme?</p>-->
                <!--</a>-->
                <!--</li>-->
                <!--<li>-->
                <!--<a href="#">-->
                <!--<div class="pull-left">-->
                <!--<img src="/assets/dist/img/user3-128x128.jpg" class="img-circle" alt="User Image">-->
                <!--</div>-->
                <!--<h4>-->
                <!--Sales Department-->
                <!--<small><i class="fa fa-clock-o"></i> Yesterday</small>-->
                <!--</h4>-->
                <!--<p>Why not buy a new awesome theme?</p>-->
                <!--</a>-->
                <!--</li>-->
                <!--<li>-->
                <!--<a href="#">-->
                <!--<div class="pull-left">-->
                <!--<img src="/assets/dist/img/user4-128x128.jpg" class="img-circle" alt="User Image">-->
                <!--</div>-->
                <!--<h4>-->
                <!--Reviewers-->
                <!--<small><i class="fa fa-clock-o"></i> 2 days</small>-->
                <!--</h4>-->
                <!--<p>Why not buy a new awesome theme?</p>-->
                <!--</a>-->
                <!--</li>-->
                <!--</ul>-->
                <!--</li>-->
                <!--<li class="footer"><a href="#">See All Messages</a></li>-->
                <!--</ul>-->
                <!--</li>-->
                <!--&lt;!&ndash; Notifications: style can be found in dropdown.less &ndash;&gt;-->
                <!--<li class="dropdown notifications-menu">-->
                <!--<a href="#" class="dropdown-toggle" data-toggle="dropdown">-->
                <!--<i class="fa fa-bell-o"></i>-->
                <!--<span class="label label-warning">10</span>-->
                <!--</a>-->
                <!--<ul class="dropdown-menu">-->
                <!--<li class="header">You have 10 notifications</li>-->
                <!--<li>-->
                <!--&lt;!&ndash; inner menu: contains the actual data &ndash;&gt;-->
                <!--<ul class="menu">-->
                <!--<li>-->
                <!--<a href="#">-->
                <!--<i class="fa fa-users text-aqua"></i> 5 new members joined today-->
                <!--</a>-->
                <!--</li>-->
                <!--<li>-->
                <!--<a href="#">-->
                <!--<i class="fa fa-warning text-yellow"></i> Very long description here that may not fit into the-->
                <!--page and may cause design problems-->
                <!--</a>-->
                <!--</li>-->
                <!--<li>-->
                <!--<a href="#">-->
                <!--<i class="fa fa-users text-red"></i> 5 new members joined-->
                <!--</a>-->
                <!--</li>-->
                <!--<li>-->
                <!--<a href="#">-->
                <!--<i class="fa fa-shopping-cart text-green"></i> 25 sales made-->
                <!--</a>-->
                <!--</li>-->
                <!--<li>-->
                <!--<a href="#">-->
                <!--<i class="fa fa-user text-red"></i> You changed your username-->
                <!--</a>-->
                <!--</li>-->
                <!--</ul>-->
                <!--</li>-->
                <!--<li class="footer"><a href="#">View all</a></li>-->
                <!--</ul>-->
                <!--</li>-->
                <!--&lt;!&ndash; Tasks: style can be found in dropdown.less &ndash;&gt;-->
                <!--<li class="dropdown tasks-menu">-->
                <!--<a href="#" class="dropdown-toggle" data-toggle="dropdown">-->
                <!--<i class="fa fa-flag-o"></i>-->
                <!--<span class="label label-danger">9</span>-->
                <!--</a>-->
                <!--<ul class="dropdown-menu">-->
                <!--<li class="header">You have 9 tasks</li>-->
                <!--<li>-->
                <!--&lt;!&ndash; inner menu: contains the actual data &ndash;&gt;-->
                <!--<ul class="menu">-->
                <!--<li>&lt;!&ndash; Task item &ndash;&gt;-->
                <!--<a href="#">-->
                <!--<h3>-->
                <!--Design some buttons-->
                <!--<small class="pull-right">20%</small>-->
                <!--</h3>-->
                <!--<div class="progress xs">-->
                <!--<div class="progress-bar progress-bar-aqua" style="width: 20%" role="progressbar"-->
                <!--aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">-->
                <!--<span class="sr-only">20% Complete</span>-->
                <!--</div>-->
                <!--</div>-->
                <!--</a>-->
                <!--</li>-->
                <!--&lt;!&ndash; end task item &ndash;&gt;-->
                <!--<li>&lt;!&ndash; Task item &ndash;&gt;-->
                <!--<a href="#">-->
                <!--<h3>-->
                <!--Create a nice theme-->
                <!--<small class="pull-right">40%</small>-->
                <!--</h3>-->
                <!--<div class="progress xs">-->
                <!--<div class="progress-bar progress-bar-green" style="width: 40%" role="progressbar"-->
                <!--aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">-->
                <!--<span class="sr-only">40% Complete</span>-->
                <!--</div>-->
                <!--</div>-->
                <!--</a>-->
                <!--</li>-->
                <!--&lt;!&ndash; end task item &ndash;&gt;-->
                <!--<li>&lt;!&ndash; Task item &ndash;&gt;-->
                <!--<a href="#">-->
                <!--<h3>-->
                <!--Some task I need to do-->
                <!--<small class="pull-right">60%</small>-->
                <!--</h3>-->
                <!--<div class="progress xs">-->
                <!--<div class="progress-bar progress-bar-red" style="width: 60%" role="progressbar"-->
                <!--aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">-->
                <!--<span class="sr-only">60% Complete</span>-->
                <!--</div>-->
                <!--</div>-->
                <!--</a>-->
                <!--</li>-->
                <!--&lt;!&ndash; end task item &ndash;&gt;-->
                <!--<li>&lt;!&ndash; Task item &ndash;&gt;-->
                <!--<a href="#">-->
                <!--<h3>-->
                <!--Make beautiful transitions-->
                <!--<small class="pull-right">80%</small>-->
                <!--</h3>-->
                <!--<div class="progress xs">-->
                <!--<div class="progress-bar progress-bar-yellow" style="width: 80%" role="progressbar"-->
                <!--aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">-->
                <!--<span class="sr-only">80% Complete</span>-->
                <!--</div>-->
                <!--</div>-->
                <!--</a>-->
                <!--</li>-->
                <!--&lt;!&ndash; end task item &ndash;&gt;-->
                <!--</ul>-->
                <!--</li>-->
                <!--<li class="footer">-->
                <!--<a href="#">View all tasks</a>-->
                <!--</li>-->
                <!--</ul>-->
                <!--</li>-->
                <!--&lt;!&ndash; User Account: style can be found in dropdown.less &ndash;&gt;-->
                <!--<li class="dropdown user user-menu">-->
                <!--<a href="#" class="dropdown-toggle" data-toggle="dropdown">-->
                <!--<img src="/assets/dist/img/user2-160x160.jpg" class="user-image" alt="User Image">-->
                <!--<span class="hidden-xs">Alexander Pierce</span>-->
                <!--</a>-->
                <!--<ul class="dropdown-menu">-->
                <!--&lt;!&ndash; User image &ndash;&gt;-->
                <!--<li class="user-header">-->
                <!--<img src="/assets/dist/img/user2-160x160.jpg" class="img-circle" alt="User Image">-->

                <!--<p>-->
                <!--Alexander Pierce - Web Developer-->
                <!--<small>Member since Nov. 2012</small>-->
                <!--</p>-->
                <!--</li>-->
                <!--&lt;!&ndash; Menu Body &ndash;&gt;-->
                <!--<li class="user-body">-->
                <!--<div class="row">-->
                <!--<div class="col-xs-4 text-center">-->
                <!--<a href="#">Followers</a>-->
                <!--</div>-->
                <!--<div class="col-xs-4 text-center">-->
                <!--<a href="#">Sales</a>-->
                <!--</div>-->
                <!--<div class="col-xs-4 text-center">-->
                <!--<a href="#">Friends</a>-->
                <!--</div>-->
                <!--</div>-->
                <!--&lt;!&ndash; /.row &ndash;&gt;-->
                <!--</li>-->
                <!--&lt;!&ndash; Menu Footer&ndash;&gt;-->
                <!--<li class="user-footer">-->
                <!--<div class="pull-left">-->
                <!--<a href="#" class="btn btn-default btn-flat">Profile</a>-->
                <!--</div>-->
                <!--<div class="pull-right">-->
                <!--<a href="#" class="btn btn-default btn-flat">Sign out</a>-->
                <!--</div>-->
                <!--</li>-->
                <!--</ul>-->
                <!--</li>-->
                <!-- Control Sidebar Toggle Button -->
                <li>
                    <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
                </li>
            </ul>
        </div>
    </nav>
</header>
<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
        <!-- sidebar menu: : style can be found in sidebar.less -->
        <ul class="sidebar-menu" data-widget="tree">
            <li class="header"><?php echo __('功能菜单'); ?></li>
            <?php if(can('count_order')): ?>
            <li class="treeview <?php if($module == 'order'): ?>menu-open active<?php endif; ?>">
                <a href="#">
                    <i class="fa fa-fw fa-bar-chart-o"></i> <span><?php echo __('订单报表'); ?></span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu" <?php if($module == 'order'): ?>style="display:block"<?php else: ?>style="display:none"<?php endif; ?>>
                    <?php echo build_menu('/count/order/index', '订单状态报表','fa fa-circle-o'); ?>
                    <?php echo build_menu('/count/order/sale', '销售额报表','fa fa-circle-o'); ?>
                    <?php echo build_menu('/count/order/preprofit', '预利润报表','fa fa-circle-o'); ?>
                </ul>
            </li>
            <?php endif; if(can('count_sku')): ?>
            <li class="treeview <?php if($module == 'sku'): ?>menu-open active<?php endif; ?>" >
                <a href="#">
                    <i class="fa fa-fw fa-suitcase"></i> <span><?php echo __('SKU报表'); ?></span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu" <?php if($module == 'sku'): ?>style="display:block"<?php else: ?>style="display:none"<?php endif; ?>>
                    <?php echo build_menu('/count/sku/index', 'SKU销量报表','fa fa-circle-o'); ?>
                    <?php echo build_menu('/count/sku/packages', 'SKU产品包销量报表','fa fa-circle-o'); ?>
                </ul>
            </li>
            <?php endif; if(can('count_finance')): ?>
            <li class="treeview <?php if($module == 'finance'): ?>menu-open active<?php endif; ?>" >
                <a href="#">
                    <i class="fa fa-cc-visa"></i> <span><?php echo __('财务报表'); ?></span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu" <?php if($module == 'finance'): ?>style="display:block"<?php else: ?>style="display:none"<?php endif; ?>>
                    <?php echo build_menu('/count/finance/index', '收支报表','fa fa-circle-o'); ?>
                    <?php echo build_menu('/count/finance/refund', '售后报表','fa fa-circle-o'); ?>
                    <?php echo build_menu('/count/order/confirmprofit', '确认利润报表','fa fa-circle-o'); ?>
                </ul>
            </li>
            <?php endif; if(can('count_transport')): ?>
            <li class="treeview <?php if($module == 'transport'): ?>menu-open active<?php endif; ?>">
                <a href="#">
                    <i class="fa fa-fw fa-truck"></i> <span><?php echo __('物流报表'); ?></span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu" <?php if($module == 'transport'): ?>style="display:block"<?php else: ?>style="display:none"<?php endif; ?>>
                    <?php echo build_menu('/count/transport/index', '物流对账报表','fa fa-circle-o'); ?>
                    <?php echo build_menu('/count/transport/expense', '物流支出报表','fa fa-circle-o'); ?>
                </ul>
            </li>
            <?php endif; if(can('count_purchase')): ?>
            <li class="treeview <?php if($module == 'purchase'): ?>menu-open active<?php endif; ?>">
                <a href="#">
                    <i class="fa fa-fw fa-pie-chart"></i> <span><?php echo __('采购收付报表'); ?></span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu" <?php if($module == 'purchase'): ?>style="display:block"<?php else: ?>style="display:none"<?php endif; ?>>
                <?php echo build_menu('/count/purchase/index', '应付款报表','fa fa-circle-o'); ?>
                <?php echo build_menu('/count/purchase/flow', '付款流水','fa fa-circle-o'); ?>
                <?php echo build_menu('/count/purchase/flow/byAccount', '付款流水账号汇总','fa fa-circle-o'); ?>
                </ul>
            </li>
            <?php endif; ?>

        </ul>
    </section>
    <!-- /.sidebar -->
</aside>

    <!-- Full Width Column -->
    <div class="content-wrapper">
        <style>
    .bootstrap-select:not([class*="col-"]):not([class*="form-control"]):not(.input-group-btn){width:100px;}
</style>
<section class="content">
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li><a href="javascript:void(0);">SKU销量报表</a></li>
            <li class="pull-right">
                <div class="btn-toolbar" role="toolbar">
                    <div class="btn-group padding-top8 paddint-right5">
                        <!--<a href="<?php echo url('/count/sku/index', array_merge($params, ['type' => $type, 'model' => 'table'])); ?>" data-toggle="tooltip"-->
                           <!--class="btn btn-xs btn-default <?php echo $model=='table'?'active' : ''; ?>"-->
                           <!--title="列表模式"><span class="fa fa-fw fa-th-large"></span></a>-->
<!--                        <a href="<?php echo url('/count/sku/index', array_merge($params,['type' => $type, 'model' => 'chart'])); ?>" data-toggle="tooltip"
                           class="btn btn-xs btn-default <?php echo $model=='chart'?'active' : ''; ?>"
                           title="图表模式"><span class="fa fa-fw fa-bar-chart-o"></span></a>-->
                    </div>
                </div>
            </li>
            <li class="pull-right alert-tips">
                <i class="icon fa fa-warning"></i> 统计数据存在一定的延迟性, 请勿与订单实时对比.
            </li>
        </ul>
        <div class="box-body">
            <div class="box box-solid">
                <div class="box-header with-bsku">
                    <form action="<?php echo url('/count/sku/index', array_merge($params,['type' => $type, 'model' => $model])); ?>" method="get" class="form-inline froms clearfix">
                        <div class="form-group">
                            <select class="selectpicker show-tick " title="平台" name="platform" id="platform" data-actions-box="true" data-live-search="true">
                                <option value="">平台</option>
                                <?php if(!empty($platform)): foreach($platform as $platform_list): ?>
                                <option value="<?php echo $platform_list; ?>" <?php if($params['platform'] == $platform_list): ?>selected<?php endif; ?>><?php echo $platform_list; ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <select class="selectpicker show-tick" title="仓库" name="store" id="store" data-actions-box="true" data-live-search="true">
                                <option value="">仓库</option>
                                <?php if(!empty($store)): foreach($store as $store_list): ?>
                                <option value="<?php echo $store_list['id']; ?>" <?php if($params['store'] == $store_list['id']): ?>selected<?php endif; ?>><?php echo $store_list['store_name']; ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <select class="selectpicker show-tick" title="分类" name="category" onchange="order_model.selectCategory(this)" data-url="<?php echo url('/count/sku/index/selectcategory'); ?>" id="category" data-actions-box="true" data-live-search="true">
                                <option value="">分类</option>
                                <?php if(!empty($category)): foreach($category as $category_list): ?>
                                <option value="<?php echo $category_list['id']; ?>" <?php if($params['category'] == $category_list['id']): ?>selected<?php endif; ?>><?php echo $category_list['name']; ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>

                        <div class="form-group" id="child">
                            <?php if(!empty($params['category'])): ?>
                            <select class="selectpicker show-tick" name="single" data-actions-box="true" data-live-search="true">
                                <option value="">产品分类</option>
                                <?php foreach($child as $single_list): ?>
                                <option value="<?php echo $single_list['id']; ?>" <?php if($params['single'] == $single_list['id']): ?>selected<?php endif; ?>><?php echo $single_list['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <select class="selectpicker show-tick" title="部门" name="organ[]" id="organ" data-actions-box="false" data-live-search="true" onchange="order_model.change_org($(this))">
                                <option value="">部门</option>
                                <?php foreach($org_list as $item): ?>
                                <option value="<?php echo $item['id']; ?>" <?php if(in_array($item['id'], $params['organ'] ?? [])): ?>selected<?php endif; ?>><?php echo $item['name']; ?></option>
                                <?php echo $item['name']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>

                            <select class="selectpicker show-tick" title="销售员" name="seller[]" id="seller" data-actions-box="false" data-live-search="true" data-max-options="20" multiple>
                                <?php foreach($sellers as $item): ?>
                                <option value="<?php echo $item; ?>" <?php if(in_array($item, $params['seller'] ?? [])): ?>selected<?php endif; ?>><?php echo $item; ?></option>
                                <?php endforeach; ?>
                            </select>

                        </div>
                        <div class="form-group">
                            <select class="selectpicker show-tick" title="目标国家" name="country" id="country" data-actions-box="true" data-live-search="true">
                                <option value="">目标国家</option>
                                <?php if(!empty($country)): foreach($country as $country_key=>$country_list): ?>
                                <option value="<?php echo $country_key; ?>" <?php if($params['country'] == $country_key): ?>selected<?php endif; ?>><?php echo $country_list; ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>


                        <div class="form-group">
                            <div class="input-group laydate-group">
                                <input type="text" class="input-sm form-control input-date datepicker" name="paytime_start" value="<?php echo $params['paytime_start']; ?>" placeholder="开始年月日" size="10" readonly/>
                                <span class="input-group-addon">到</span>
                                <input type="text" class="input-sm form-control input-date datepicker" name="paytime_end" value="<?php echo $params['paytime_end']; ?>" placeholder="结束年月日" size="10" readonly/>
                            </div>
                        </div>

                        <div class="form-group">
                            <select class="selectpicker show-tick" title="时间正序" name="sort" id="sort" data-actions-box="true" data-live-search="true">
                                <option value="0" <?php if($params['sort'] == 0): ?>selected<?php endif; ?>>默认</option>
                                <option value="1" <?php if($params['sort'] == 1): ?>selected<?php endif; ?>>时间正序</option>
                                <option value="2" <?php if($params['sort'] == 2): ?>selected<?php endif; ?>>时间倒序</option>
                                <option value="3" <?php if($params['sort'] == 3): ?>selected<?php endif; ?>>销量正序</option>
                                <option value="4" <?php if($params['sort'] == 4): ?>selected<?php endif; ?>>销量倒序</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <input type="text" value="<?php echo $params['keyword']; ?>" name="keyword" class="form-control input-sm" placeholder="SKU关键字, 可支持批量" size="40">
                        </div>
                        <div class="form-group">
                            <button class="btn btn-primary btn-sm" type="submit" name="submit"><i class="glyphicon glyphicon-search"></i> 确定搜索&nbsp;</button>
                            <a class="btn btn-warning btn-sm" href="javascript:void(0);" target="_blank" onclick="order_model.export_sku()"><i class="glyphicon glyphicon-save"></i> 导出Excel&nbsp;</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="tab-content">
            <?php if($model == 'table'): ?>
            <div class="text-danger">
    <i class="icon fa fa-warning"></i> 说明：根据已发货的订单进系统时间来统计
</div>
<table class="table table-bordered table-hover dataTable table-striped">
    <thead class="fixed">
    <tr id="action">
        <th class="text-center y-center">SKU编号</th>
        <th class="text-center y-center">图片</th>
        <th class="text-center y-center">标题</th>
        <?php foreach($date as $times): ?>
        <th class="text-center y-center"><?php echo $times; ?></th>
        <?php endforeach; ?>
        <th class="text-center y-center table-total">合计</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach($data as $sku => $lists): ?>
    <tr>
        <td class="text-center y-center sku-td-width"><?php echo $sku; ?></td>
        <td class="text-center y-center pic-td-width">
            <img data-src="<?php if((!empty($lists['data']['thumb']))): ?><?php echo $lists['data']['thumb']; else: ?>/assets/dist/img/no_picture.gif<?php endif; ?>" class="img-src-size lazy-img" <?php if((!empty($lists['data']['large']))): ?>onclick="order_model.skuimage(this)"<?php endif; ?>
            bigsrc="<?php echo isset($lists['data']['large'])?$lists['data']['large']: ''; ?>">
        </td>
        <td class="text-center y-center title-td-width small"><?php echo isset($lists['data']['name'])?$lists['data']['name']: ''; ?></td>
        <?php foreach($date2 as $ke => $item): ?>
        <td class="text-center y-center">
            <a href="javascript:void(0)" onclick="order_model.showSkuDetail(this,1)" data-sku="<?php echo $sku; ?>" data-time="<?php echo $item; ?>"
               data-url="<?php echo url('/count/sku/index/showskutotal',
               array('organ'=>$params['organ'],'paytime_start'=>$params['paytime_start'],'paytime_end'=>$params['paytime_end'],'platform'=>$params['platform'],'store'=>$params['store'],'category'=>$params['category'],'single'=>$params['single'],'country'=>$params['country'],'seller'=>$params['seller'],'sku'=>$sku,'time'=>$item,'model' => 'table')); ?>">
                <?php echo isset($lists['date'][$item])?array_sum($lists['date'][$item]):0; ?>
            </a>
            <br>
            <a href="javascript:void(0)" onclick="order_model.showSkuDetail(this,1)" data-sku="<?php echo $sku; ?>" data-time="<?php echo $item; ?>"
               data-url="<?php echo url('/count/sku/index/showskutotal',
               array('organ'=>$params['organ'],'paytime_start'=>$params['paytime_start'],'paytime_end'=>$params['paytime_end'],'platform'=>$params['platform'],'store'=>$params['store'],'category'=>$params['category'],'single'=>$params['single'],'country'=>$params['country'],'seller'=>$params['seller'],'sku'=>$sku,'time'=>$item,'model' => 'chart')); ?>" data-toggle="tooltip"
               class="btn btn-xs btn-default <?php echo $model=='chart'?'active' : ''; ?>"
               title="图表模式"><span class="fa fa-fw fa-bar-chart-o"></span></a>
        </td>
        <?php endforeach; ?>
        <td class="text-center y-center table-total">
            <a href="javascript:void(0)" onclick="order_model.showSkuDetailTotal(this,2)" data-sku="<?php echo $sku; ?>" start-time="<?php echo $params['paytime_start']; ?>"
               end-time="<?php echo $params['paytime_end']; ?>"
               data-url="<?php echo url('/count/sku/index/showskudetail',
array('organ'=>$params['organ'],'paytime_start'=>$params['paytime_start'],'paytime_end'=>$params['paytime_end'],'platform'=>$params['platform'],'store'=>$params['store'],'category'=>$params['category'],'single'=>$params['single'],'country'=>$params['country'],'seller'=>$params['seller'],'sku'=>$sku,'time'=>$item,'model' => 'table')); ?>">
                <?php echo isset($lists['data']['qtySum'])?$lists['data']['qtySum']: 0; ?>
            </a>
            <br>
            <a href="javascript:void(0)" onclick="order_model.showSkuDetailTotal(this,2)" data-sku="<?php echo $sku; ?>" start-time="<?php echo $params['paytime_start']; ?>"
               end-time="<?php echo $params['paytime_end']; ?>"
               data-url="<?php echo url('/count/sku/index/showskudetail',
array('organ'=>$params['organ'],'paytime_start'=>$params['paytime_start'],'paytime_end'=>$params['paytime_end'],'platform'=>$params['platform'],'store'=>$params['store'],'category'=>$params['category'],'single'=>$params['single'],'country'=>$params['country'],'seller'=>$params['seller'],'sku'=>$sku,'time'=>$item,'model' => 'chart')); ?>"
               data-toggle="tooltip"
               class="btn btn-xs btn-default <?php echo $model=='chart'?'active' : ''; ?>"
               title="图表模式"><span class="fa fa-fw fa-bar-chart-o"></span></a>
        </td>
    </tr>
    <?php endforeach; ?>

    </tbody>
</table>

            <?php else: ?>
            <script src="/assets/plugins/echarts/echarts.min.js"></script>
<div id="chartmain" style="width:100%; height:780px;"></div>
<script type="application/javascript">
    // 基于准备好的dom，初始化echarts实例
    var myChart = echarts.init(document.getElementById('chartmain'));
    option = {
        title: {
            text: ''
        },
        tooltip: {
            trigger: 'axis'
        },
        legend: {
            data:['ZN70505_02','ZN70505_03']
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
        toolbox: {
            feature: {
                saveAsImage: {}
            }
        },
        xAxis: {
            type: 'category',
            boundaryGap: false,
            data: ['07-31','08-01','08-02','08-03','08-04','08-05']
        },
        yAxis: {
            type: 'value'
        },
        series: [
            {
                name:'ZN70505_02',
                type:'line',
                stack: '总量',
                data:[120, 132, 101, 390, 330, 320]
            },
            {
                name:'ZN70505_03',
                type:'line',
                stack: '总量',
                data:[120, 132, 101, 390, 330, 320]
            },

        ]
    };

    // 使用刚指定的配置项和数据显示图表。
    myChart.setOption(option);
</script>

            <?php endif; ?>
        </div>

        <div class="batch-bar clearfix">
            
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






        </div>
    </div>
</section>

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Create the tabs -->
            <ul class="nav nav-tabs nav-justified control-sidebar-tabs">
            </ul>
            <!-- Tab panes -->
            <div class="tab-content">
                <!-- Home tab content -->
                <div class="tab-pane" id="control-sidebar-home-tab"></div>
            </div>
        </aside>
        <!-- /.control-sidebar -->
        <!-- Add the sidebar's background. This div must be placed
             immediately after the control sidebar -->
        <div class="control-sidebar-bg"></div>
    </div>
</div>
<!-- 加载JS脚本 -->
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
<script src="/assets/dist/js/default.js"></script>
<script src="/assets/dist/js/common.js"></script>
<script src="/assets/dist/js/<?php echo $config['modulename']; ?>.js"></script>

<!-- 这个文件结构，有问题，不能给单个html文件加js -->
<script>
    // var page_str = '总共找到 <strong class="text-danger"><?php echo isset($list_total)?$list_total: ''; ?></strong> 条记录';
    var page_str = '';
    var order = [0, 'desc'];
//    组织架构和 平台不进行排序
    <?php if(isset($params['type']) && ($params['type'] == 'organ')): ?>
        var order = [];
    <?php endif; ?>
    common_module.init_data_table(page_str, order);
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
</body>
</html>