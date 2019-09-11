<?php if (!defined('THINK_PATH')) exit(); /*a:9:{s:86:"/opt/web/count/development/public/../application/count/view/order/sale/set_target.html";i:1552989433;s:69:"/opt/web/count/development/application/count/view/layout/default.html";i:1544600061;s:84:"/opt/web/count/development/application/count/view/order/sale/_layout_for_target.html";i:1552989433;s:66:"/opt/web/count/development/application/count/view/common/meta.html";i:1550824652;s:68:"/opt/web/count/development/application/count/view/common/header.html";i:1550824652;s:86:"/opt/web/count/development/application/count/view/order/sale/_tabs_bar_for_target.html";i:1552989433;s:82:"/opt/web/count/development/application/count/view/order/sale/_form_for_target.html";i:1552989433;s:69:"/opt/web/count/development/application/count/view/common/script2.html";i:1550824652;s:69:"/opt/web/count/development/application/count/view/layout/btn_top.html";i:1550824653;}*/ ?>
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
    <style>
        .tab-content {
            overflow: auto;
        }
        .DTFC_LeftBodyLiner {
            overflow-x: hidden;
        }
        .tooltip-inner {
        white-space:nowrap;
        max-width:none;
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
    <li class="<?php echo $params['type']=='organ'?'active' : ''; ?>"><a href="<?php echo url('/count/order/Sale/setTarget', ['type' => 'organ']); ?>">按组织架构</a></li>
    <li class="<?php echo $params['type']=='seller'?'active' : ''; ?>"><a href="<?php echo url('/count/order/Sale/setTarget', ['type' => 'seller']); ?>">按销售员</a></li>
    <li class="<?php echo $params['type']=='account'?'active' : ''; ?>"><a href="<?php echo url('/count/order/Sale/setTarget', ['type' => 'account']); ?>">按账号</a></li>
</ul>
                <div class="box-body">
                    <div class="box box-solid">
                        <div class="box-header with-bsales">
                            <form action="<?php echo url('/count/order/Sale/settarget', array_merge($params,['type' => $params['type']])); ?>" method="get" class="form-inline froms clearfix">

    <input type="hidden" name="type" value="<?php echo $params['type']; ?>">
    <input type="hidden" name="ps" value="<?php echo $params['ps']; ?>">
    <input type="hidden" name="p" value="<?php echo $params['p']; ?>">

    <div class="form-group" style="width: 206px;">
        <select class="selectpicker" name="year" id="year" data-actions-box="true" data-live-search="true" onchange="order_model.change_target_year($(this));">
            <?php foreach($year_list as $key => $value): ?>
            <option value="<?php echo $key; ?>" <?php if($key == $params['year']): ?>selected<?php endif; ?>><?php echo $value; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
        
    <?php if(in_array($params['type'], ['account', 'platform'])): ?>
    <div class="form-group" style="width: 206px;">
        <label class="control-label text-right">平台：</label>
        <select class="selectpicker platform-choice" title="全部" name="platform" id="platform" data-actions-box="true" data-live-search="true">
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
    <select class="selectpicker" title="全部" name="organ[]" id="organ" data-actions-box="true" data-live-search="true" onchange="order_model.change_org($(this))">
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
    <?php endif; if($params['type'] != 'organ'): ?>
    <div class="form-group">
        <button class="btn btn-primary btn-sm" type="submit" name="submit"><i class="glyphicon glyphicon-search"></i> 确定搜索&nbsp;</button>
        <a class="btn btn-warning btn-sm" href="javascript:void(0);" onclick="common_module.import_excel($(this))" data-url="<?php echo url('importTarget', '', ''); ?>?type=<?php echo $params['type']; ?>"></i> 导入&nbsp;</a>
    </div>
    <?php endif; ?>
</form>
                        </div>
                    </div>
                </div>

                <div class="tab-content">
                    <table class="table table-bordered table-hover dataTable table-striped display nowrap compact js-table">
                        <thead>
                        <tr>
                            <?php if($params['type'] == 'organ'): ?>
                            <th class="text-left">组织架构</th>
                            <th class="text-center">负责人</th>
                            <?php elseif($params['type'] == 'account'): ?>
                            <th class="text-left">平台</th>
                            <th class="text-center">账户</th>
                            <?php elseif($params['type'] == 'seller'): ?>
                            <th class="text-left">组织架构</th>
                            <th class="text-center">销售员</th>
                            <?php endif; ?>
                            <!--<th class="text-center">添加人/时间</th>-->
                            <th class="text-center">1月</th>
                            <th class="text-center">2月</th>
                            <th class="text-center">3月</th>
                            <th class="text-center">4月</th>
                            <th class="text-center">5月</th>
                            <th class="text-center">6月</th>
                            <th class="text-center">7月</th>
                            <th class="text-center">8月</th>
                            <th class="text-center">9月</th>
                            <th class="text-center">10月</th>
                            <th class="text-center">11月</th>
                            <th class="text-center">12月</th>
                        </tr>
                    
                        </thead>
                        <tbody id="scroll_table_head">
                        <?php foreach($list as $key => $item): ?>
                        <tr>
                            <td class="text-left">
                                <?php if($params['type'] == 'organ'): if($item['level'] > 2): ?>
                                    |<?php echo str_repeat('---', ($item['level'] - 1)); endif; ?>
                                    <?php echo $item['name']; elseif($params['type'] == 'account'): ?>
                                <?php echo $item['platform']; elseif($params['type'] == 'seller'): ?>
                                <?php echo $item['org_parent_name']; ?><?php echo $item['org_name']; endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if($params['type'] == 'organ'): ?>
                                <?php echo $item['manage']; elseif($params['type'] == 'account'): ?>
                                <?php echo $item['ebay_account']; elseif($params['type'] == 'seller'): ?>
                                <?php echo $item['user_name']; endif; ?>
                            </td>
                            <?php foreach($months as $month): if($params['type'] == 'organ'): ?>
                            <td class="text-center" onclick="order_model.update_org_target($(this), 0)" data-url="<?php echo url('setTarget', '', ''); ?>?type=<?php echo $params['type']; ?>" data-type="<?php echo $params['type']; ?>" data-org_id="<?php echo $item['id']; ?>" data-month="<?php echo $month; ?>">
                                <span><?php echo isset($target_list[$item['id']][$month])?$target_list[$item['id']][$month]: ''; ?></span>
                                <input type="text" class="target_input input-sm form-control" style="width: 100%;display: none;height: 20px;" onblur="order_model.update_org_target($(this), 1)" value="<?php echo isset($target_list[$item['id']][$month])?$target_list[$item['id']][$month]: ''; ?>">
                            </td>
                            <?php elseif($params['type'] == 'account'): ?>
                            <td class="text-center" style="width: 100px;" onclick="order_model.update_org_target($(this), 0)" data-url="<?php echo url('setTarget', '', ''); ?>?type=<?php echo $params['type']; ?>" data-type="<?php echo $params['type']; ?>" data-ebay_account="<?php echo $item['ebay_account']; ?>" data-month="<?php echo $month; ?>">
                                <span><?php echo isset($target_list[$item['ebay_account']][$month])?$target_list[$item['ebay_account']][$month]: ''; ?></span>
                                <input type="text" class="target_input input-sm form-control" style="width: 100%;display: none;height: 20px;" onblur="order_model.update_org_target($(this), 1)" value="<?php echo isset($target_list[$item['ebay_account']][$month])?$target_list[$item['ebay_account']][$month]: ''; ?>">
                            </td>
                            <?php elseif($params['type'] == 'seller'): ?>
                            <td class="text-center" style="width: 100px;" onclick="order_model.update_org_target($(this), 0)" data-url="<?php echo url('setTarget', '', ''); ?>?type=<?php echo $params['type']; ?>" data-type="<?php echo $params['type']; ?>" data-seller="<?php echo $item['seller']; ?>" data-month="<?php echo $month; ?>">
                                <span><?php echo isset($target_list[$item['seller']][$month])?$target_list[$item['seller']][$month]: ''; ?></span>
                                <input type="text" class="target_input input-sm form-control" style="width: 100%;display: none;height: 20px;" onblur="order_model.update_org_target($(this), 1)" value="<?php echo isset($target_list[$item['seller']][$month])?$target_list[$item['seller']][$month]: ''; ?>">
                            </td>
                            <?php endif; endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
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


<!-- 用来 添加自定义 的 js -->
<script>
    var page_str = '';
    var page_len = 50;
    var is_page = 1;
    var order = [0, 'desc'];
    var is_order = -1;
    <?php if(isset($params['type']) && (in_array($params['type'], ['organ']))): ?>
    is_page = -1;
    <?php endif; ?>

    common_module.init_data_table(page_str, order, page_len, is_page, is_order);

    // 针对414 错误
    common_module.init_submit_form('manual_submit_form');

</script>

</body>
</html>