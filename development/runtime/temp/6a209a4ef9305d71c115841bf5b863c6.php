<?php if (!defined('THINK_PATH')) exit(); /*a:10:{s:84:"/opt/web/count/development/public/../application/count/view/check/finance/index.html";i:1555296405;s:69:"/opt/web/count/development/application/count/view/layout/default.html";i:1544600061;s:76:"/opt/web/count/development/application/count/view/check/finance/_layout.html";i:1555296405;s:66:"/opt/web/count/development/application/count/view/common/meta.html";i:1550824652;s:68:"/opt/web/count/development/application/count/view/common/header.html";i:1550824652;s:74:"/opt/web/count/development/application/count/view/check/finance/_form.html";i:1555296405;s:74:"/opt/web/count/development/application/count/view/check/finance/table.html";i:1555296405;s:66:"/opt/web/count/development/application/count/view/layout/page.html";i:1544600061;s:69:"/opt/web/count/development/application/count/view/common/script2.html";i:1550824652;s:69:"/opt/web/count/development/application/count/view/layout/btn_top.html";i:1550824653;}*/ ?>
<!DOCTYPE html>
<html lang="<?php echo $config['language']; ?>">
<head>
    <title>财务抽查</title>
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
    .text-danger {
        color: #ff0000;
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
                <div class="box-body">
                    <div class="box box-solid">
                        <div class="box-header with-bsales">
                            <form action="<?php echo url('/count/check/finance'); ?>" method="get" class="form-inline froms clearfix">

    <input type="hidden" name="ps" value="<?php echo $params['ps']; ?>">
    <input type="hidden" name="p" value="<?php echo $params['p']; ?>">

    <div class="form-group">
        <label class="control-label text-right">抽查类型：</label>
        <select class="selectpicker" title="全部" name="check_type[]" id="check_type" data-actions-box="true" data-live-search="true" multiple >
            <option value="">全部</option>
            <?php foreach($check_types as $key => $item): ?>
            <option value="<?php echo $key; ?>" <?php if(in_array($key, $params['check_type'])): ?>selected<?php endif; ?>><?php echo $item; ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label class="control-label text-right">创建时间：</label>
        <div class="input-group laydate-group">
            <input type="text" class="input-sm form-control input-date datepicker start" name="start_time"  placeholder="开始时间" value="<?php echo $params['start_time']; ?>" readonly/>

            <span class="input-group-addon">到</span>
            <input type="text" class="input-sm form-control input-date datepicker end " name="end_time" value="<?php echo $params['end_time']; ?>" placeholder="结束时间" readonly/>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label text-right">状态：</label>
        <select class="selectpicker" title="全部" name="check_status[]" id="check_status" data-actions-box="true" data-live-search="true" multiple>
            <option value="">全部</option>
            <?php foreach($check_status as $key => $item): ?>
            <option value="<?php echo $key; ?>" <?php if(in_array($key, $params['check_status'])): ?>selected<?php endif; ?>><?php echo $item; ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <button class="btn btn-primary btn-sm" type="submit" name="submit"><i class="glyphicon glyphicon-search"></i> 确定搜索&nbsp;</button>
        <a class="btn btn-warning btn-sm" href="javascript:void(0);" data-url="<?php echo url('add'); ?>" onclick="order_model.add_finance_check($(this));">新建&nbsp;</a>
        <!-- <a class="btn btn-success btn-sm" href="javascript:void(0);" onclick="order_model.show_finance_check_action_log($(this))">操作日志&nbsp;</a> -->
    </div>
</form>
                        </div>
                    </div>
                </div>

                <div class="tab-content">
                    <table class="table table-bordered table-hover dataTable table-striped display nowrap compact js-table" id="scroll_table">
    <thead>
    <tr>
        <th class="text-center">序号</th>
        <th class="text-center">抽查名称</th>
        <th class="text-center">抽查类型</th>
        <th class="text-center">创建人</th>
        <th class="text-center">创建时间</th>
        <th class="text-center">状态</th>
        <th class="text-center">问题平台</th>
        <th class="text-center">问题点</th>
        <th class="text-center">减损额($)</th>
        <th class="text-center">操作</th>
    </tr>

    </thead>
    <tbody id="scroll_table_head">
        <?php foreach($list as $v): ?>
        <tr>
            <td class="text-center"><?php echo $v['id']; ?></td>
            <td class="text-center"><?php echo mb_substr($v['name'],0,30); if(mb_strlen($v['name']) > 30): ?>...<?php endif; ?></td>
            <td class="text-center"><?php echo $check_types[$v['check_type']]; ?></td>
            <td class="text-center"><?php echo $v['create_uname']; ?></td>
            <td class="text-center"><?php echo date('Y-m-d', $v['create_time']); ?></td>
            <td class="text-center <?php if(in_array($v['check_status'], ['2'])): ?>text-danger<?php endif; ?>" ><?php echo $check_status[$v['check_status']]; ?></td>
            <td class="text-center"><?php echo mb_substr($v['exception_platform'],0,30); if(mb_strlen($v['exception_platform']) > 30): ?>...<?php endif; ?></td>
            <td class="text-center"><?php echo mb_substr($v['problems'], 0, 30); if(mb_strlen($v['exception_platform']) > 30): ?>...<?php endif; ?></td>
            <td class="text-center <?php if(in_array($v['check_status'], ['2'])): ?>text-danger<?php endif; ?>"><?php echo $v['saving_money']; ?></td>
            <td class="text-center">
                <button type="button" class="btn btn-default btn-xs" data-id="<?php echo $v['id']; ?>" data-url="<?php echo url('detail', ['id' => $v['id']]); ?>" onclick="order_model.view_check($(this))">查看</button>
                
                <?php if($uid == $v['create_uid']): ?>
                <a class="btn btn-warning btn-xs" href="javascript:void(0);" data-url="<?php echo url('edit', ['id' => $v['id']]); ?>" <?php if(in_array($v['check_status'], ['1','3'])): ?>disabled<?php else: ?>onclick="order_model.add_finance_check($(this));"<?php endif; ?>>编辑&nbsp;</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
    
                </div>

                
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


<script>
var page_str = '';
    var order = [];
    var page_len = 20;
    var is_page = -1;
    var is_order = -1;
    
    common_module.init_data_table(page_str, order, page_len, is_page, is_order);

    // 针对414 错误
    // common_module.init_submit_form('manual_submit_form');

 
    </script>

</body>
</html>