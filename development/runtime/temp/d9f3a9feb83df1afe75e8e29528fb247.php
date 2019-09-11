<?php if (!defined('THINK_PATH')) exit(); /*a:8:{s:84:"/opt/web/count/development/public/../application/count/view/auth/adminlog/index.html";i:1547190894;s:69:"/opt/web/count/development/application/count/view/layout/default.html";i:1544600061;s:70:"/opt/web/count/development/application/count/view/layout/default2.html";i:1547190894;s:66:"/opt/web/count/development/application/count/view/common/meta.html";i:1544600061;s:68:"/opt/web/count/development/application/count/view/common/header.html";i:1548384832;s:65:"/opt/web/count/development/application/count/view/common/map.html";i:1547190894;s:68:"/opt/web/count/development/application/count/view/common/script.html";i:1547190894;s:69:"/opt/web/count/development/application/count/view/layout/btn_top.html";i:1544600061;}*/ ?>
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

    
    <!-- 用来添加自定义的 样式 -->
    
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

    <!-- Full Width Column -->
    <div class="content-wrapper">
        
<div class="container-full">
    <section class="content-header">
    <h4><?php if(isset($ruletitle)): ?><?php echo $ruletitle; else: ?><?php echo $rule_title; endif; ?></h4>
    <ol class="breadcrumb">
        <li><a href="/"><i class="fa fa-home"></i> <?php echo __('首页'); ?></a></li>
        <li><?php echo $rule_title; ?></li>
        <li class="active"><?php echo $method_title; ?></li>
    </ol>
</section>
    <!-- Main content -->
    <section class="content">
        <div class="box box-default color-palette-box">
            <div class="box-header with-border">
                <form id="add-form" class="form-inline" role="form" data-toggle="validator" method="get" action="">
                    <button type="button" class="btn btn-sm btn-refresh"><i class="fa fa-refresh"></i></button>
                    <input type="text" class="form-control input-sm" id="username" name="keywords" value="" placeholder="请输入管理员姓名"/>
                    <button type="submit" class="btn btn-sm btn-primary btn-embossed "><i class="fa fa-search"></i> <?php echo __('搜索'); ?></button>
                </form>
            </div>

            <div class="box-body">
                <table id="table" class="table table-bordered table-hover" width="100%">
                    <head>
                        <tr>
                            <th class="text-center">ID</th>
                            <th>管理员</th>
                            <th>标题</th>
                            <th>Url</th>
                            <th>IP</th>
                            <th>创建时间</th>
                            <th class="text-center">操作</th>
                        </tr>
                    </head>
                    <tbody>
                    <?php foreach($rows as $val): ?>
                    <tr>
                        <td class="text-center"><?php echo $val['id']; ?></td>
                        <td><?php echo $val['username']; ?></td>
                        <td><?php echo $val['title']; ?></td>
                        <td><?php echo substr($val['url'], 0, 60); ?></td>
                        <td><?php echo $val['ip']; ?></td>
                        <td><?php echo date('Y-m-d H:i:s',$val['createtime']); ?></td>
                        <td class="text-center">
                            <?php if($auth->check('count/auth/adminlog/del')): ?>
                            <button type="button"
                                    class="btn btn-danger btn-edit btn-ajax btn-xs"
                                    options="{confirm:'确定要删除此项吗？'}"
                                    title="<?php echo __('删除'); ?>"
                                    href="<?php echo url('/count/auth/adminlog/del', [$val['id']]); ?>"><i class="fa fa-trash"></i> <?php echo __('删除'); ?>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php echo $page; ?>
            </div>

        </div>
    </section>
    <!-- /.content -->
</div>

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
    <?php if(isset($params['type']) && ($params['type'] == 'organ')): ?>
        var order = [];
    <?php endif; ?>
    common_module.init_data_table(page_str, order);

    // 针对414 错误
    common_module.init_submit_form('manual_submit_form');

    <?php if(isset($params['type']) && ($params['type'] != 'platform')): ?>
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
<script src="/assets/dist/js/fms.js"></script>
<script src="/assets/dist/js/acl.js"></script>
<!-- 加载JS脚本 -->

<!-- 用来 添加自定义 的 js -->


</body>
</html>

