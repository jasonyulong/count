<?php if (!defined('THINK_PATH')) exit(); /*a:6:{s:74:"E:\web\count\development\public/../application/count\view\index\index.html";i:1544600061;s:67:"E:\web\count\development\application\count\view\layout\default.html";i:1544600061;s:64:"E:\web\count\development\application\count\view\common\meta.html";i:1544600061;s:66:"E:\web\count\development\application\count\view\common\header.html";i:1544694231;s:66:"E:\web\count\development\application\count\view\common\script.html";i:1544600061;s:67:"E:\web\count\development\application\count\view\layout\btn_top.html";i:1544600061;}*/ ?>
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
            <?php endif; if(can('data_export')): ?>
            <li class="treeview <?php if($module == 'export'): ?>menu-open active<?php endif; ?>">
                <a href="#">
                    <i class="fa fa-fw fa-pie-chart"></i> <span><?php echo __('数据明细导出'); ?></span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu" <?php if($module == 'export'): ?>style="display:block"<?php else: ?>style="display:none"<?php endif; ?>>
                <?php echo build_menu('/count/order/export/taskList', ' 任务列表','fa fa-circle-o'); ?>
                </ul>
            </li>
            <?php endif; ?>

        </ul>
    </section>
    <!-- /.sidebar -->
</aside>

    <!-- Full Width Column -->
    <div class="content-wrapper">
        <section class="content">

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