<?php if (!defined('THINK_PATH')) exit(); /*a:8:{s:89:"/opt/web/count/development/public/../application/count/view/develop/index/index_date.html";i:1551349980;s:69:"/opt/web/count/development/application/count/view/layout/default.html";i:1544600061;s:66:"/opt/web/count/development/application/count/view/common/meta.html";i:1550824652;s:68:"/opt/web/count/development/application/count/view/common/header.html";i:1550824652;s:74:"/opt/web/count/development/application/count/view/develop/index/table.html";i:1551423452;s:74:"/opt/web/count/development/application/count/view/develop/index/chart.html";i:1551349980;s:68:"/opt/web/count/development/application/count/view/common/script.html";i:1550824652;s:69:"/opt/web/count/development/application/count/view/layout/btn_top.html";i:1550824653;}*/ ?>
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

    <!-- Full Width Column -->
    <div class="content-wrapper">
        <section class="content">
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li class="<?php echo $type=='date'?'active' : ''; ?>"><a href="<?php echo url('/count/develop/index', ['type' => 'date']); ?>">按日期</a></li>
            <li class="<?php echo $type=='develop'?'active' : ''; ?>"><a href="<?php echo url('/count/develop/index', ['type' => 'develop']); ?>">按开发员</a></li>
            <li class="pull-right">
                <div class="btn-toolbar" role="toolbar">
                    <div class="btn-group padding-top8 paddint-right5">
                        <a href="<?php echo url('/count/develop/index', array_merge($params, ['type' => $type, 'model' => 'table'])); ?>" data-toggle="tooltip"
                           class="btn btn-xs btn-default <?php echo $model=='table'?'active' : ''; ?>"
                           title="列表模式"><span class="fa fa-fw fa-th-large"></span></a>
                        <a href="<?php echo url('/count/develop/index', array_merge($params,['type' => $type, 'model' => 'chart'])); ?>" data-toggle="tooltip"
                           class="btn btn-xs btn-default <?php echo $model=='chart'?'active' : ''; ?>"
                           title="图表模式"><span class="fa fa-fw fa-bar-chart-o"></span></a>
                    </div>
                </div>
            </li>
            <li class="pull-right alert-tips">
                <i class="icon fa fa-warning"></i> 统计数据存在一定的延迟性, 请勿实时对比.
            </li>
        </ul>
        <div class="box-body">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <form action="<?php echo url('/count/develop/index', array_merge($params,['type' => $type, 'model' => $model]), ''); ?>" method="get" class="form-inline froms clearfix">

                        <input type="hidden" name="type" value="<?php echo $type; ?>">
                        <input type="hidden" name="ps" value="<?php echo $params['ps']; ?>">
                        <input type="hidden" name="p" value="<?php echo $params['p']; ?>">
                        <input type="hidden" name="model" value="<?php echo $model; ?>">
                        
                        <div class="form-group">
                            <label class="control-label text-right">时间维度：</label>
                            <div class="checkbox">
                                <label><input type="radio" name="checkDate" class="checkDate" value="day" <?php if($params['checkDate']=='day'): ?>checked<?php endif; ?> ><small>天</small></label>
                                <label><input type="radio" name="checkDate" class="checkDate" value="month" <?php if($params['checkDate']=='month'): ?>checked<?php endif; ?> ><small>月</small></label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label text-right">区间：</label>
                            <div class="input-group laydate-group">
                                <?php if($params['checkDate'] == 'month'): ?>
                                <input type="text" class="input-sm form-control input-date datepicker hide start" name="day_start" value="<?php echo $params['day_start']; ?>" placeholder="开始时间"/>
                                <input type="text" class="input-sm form-control input-date monthpicker" name="month_start" value="<?php echo $params['month_start']; ?>" placeholder="开始年月"/>
                                <span class="input-group-addon">到</span>
                                <input type="text" class="input-sm form-control input-date datepicker hide end" name="day_end" value="<?php echo $params['day_end']; ?>" placeholder="结束时间"/>
                                <input type="text" class="input-sm form-control input-date monthpicker" name="month_end" value="<?php echo $params['month_end']; ?>" placeholder="结束年月"/>
                                <?php else: ?>
                                <input type="text" class="input-sm form-control input-date datepicker start" name="day_start" value="<?php echo $params['day_start']; ?>" placeholder="开始时间"/>
                                <input type="text" class="input-sm form-control input-date monthpicker hide" name="month_start" value="<?php echo $params['month_start']; ?>" placeholder="开始年月"/>
                                <span class="input-group-addon">到</span>
                                <input type="text" class="input-sm form-control input-date datepicker end" name="day_end" value="<?php echo $params['day_end']; ?>" placeholder="结束时间"/>
                                <input type="text" class="input-sm form-control input-date monthpicker hide" name="month_end" value="<?php echo $params['month_end']; ?>" placeholder="结束年月"/>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if($params['checkDate'] != 'month'): ?>
                        <div class="form-group shotcut_day_div">
                            <div class="checkbox">
                                <label><input type="radio" name="checkDate" class="" onchange="order_model.checked_date(this)" value="today" <?php if($params['checkDate']=='today'): ?>checked<?php endif; ?>><small>本月</small></label>
                                <label><input type="radio" name="checkDate" class="" onchange="order_model.checked_date(this)" value="yesterday" <?php if($params['checkDate']=='yesterday'): ?>checked<?php endif; ?>><small>昨天</small></label>
                                <label><input type="radio" name="checkDate" class="" onchange="order_model.checked_date(this)" value="recently3day" <?php if($params['checkDate']=='recently3day'): ?>checked<?php endif; ?>><small>最近三天</small></label>
                            </div>
                        </div>
                        <?php endif; ?>

                <div class="form-group">
                    <button class="btn btn-primary btn-sm" type="submit" name="submit"><i class="glyphicon glyphicon-search"></i> 确定搜索&nbsp;</button>
                    <a class="btn btn-warning btn-sm" href="javascript:void(0);" target="_blank" onclick="common_module.export_excel()"><i class="glyphicon glyphicon-save"></i> 导出Excel&nbsp;</a>
                </div>
                </form>
            </div>
        </div>
    </div>

    <div class="tab-content">
        <?php if($model == 'table'): ?>
        <div class="text-danger">
    <!--<i class="icon fa fa-warning"></i> 说明：-->
</div>
<table class="table table-bordered table-hover dataTable display nowrap compact table-striped js-table">

    <thead>
    <?php if($params['type'] == 'develop'): ?>
    <tr>
        <td class="text-center table-total" style="min-width: 50px">合计</td>
            <?php if(is_array($total) || $total instanceof \think\Collection || $total instanceof \think\Paginator): $i = 0; $__LIST__ = $total;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;if(is_numeric($key) || $key == 'month'): ?><td class="text-center table-total"><?php echo $v; ?></td><?php endif; endforeach; endif; else: echo "" ;endif; ?>
        <td class="text-center table-total"><?php echo $total['sum']; ?></td>
    </tr>

    <tr>
        <th class="text-center">
            开发员 <a href="javascript:void(0);" class="sort_toggle" onclick="common_module.sort_toggle()"></a>
        </th>
        <?php if(is_array($total) || $total instanceof \think\Collection || $total instanceof \think\Paginator): $i = 0; $__LIST__ = $total;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;if($key == 'month'): ?>
        <th class="text-center">本月</th>
        <?php endif; if(is_numeric($key)): if($params['checkDate'] == 'month'): ?>
            <th class="text-center"><?php echo date('Y-m',strtotime($key.'01')); ?></th>
            <?php else: ?>
                <th class="text-center"><?php echo date('m-d',strtotime($key)); ?></th>
            <?php endif; endif; endforeach; endif; else: echo "" ;endif; ?>
        <th class="text-center">合计</th>
    </tr>

    </thead>

    <tbody id="scroll_table_head">
    <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
    <tr>
        <td class="text-center"><?php echo $key; ?></td>
            <?php if(is_array($total) || $total instanceof \think\Collection || $total instanceof \think\Paginator): $i = 0; $__LIST__ = $total;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;if($key == 'month'): ?><td class="text-center"><?php if(isset($vo[$key])): ?><?php echo $vo[$key]; else: ?>0<?php endif; ?></td><?php endif; if(is_numeric($key)): ?><td class="text-center"><?php if(isset($vo[$key])): ?><?php echo $vo[$key]; else: ?>0<?php endif; ?></td><?php endif; endforeach; endif; else: echo "" ;endif; ?>
        <td class="text-center"><?php echo $vo['sum']; ?></td>
    </tr>
    <?php endforeach; endif; else: echo "" ;endif; ?>
    </tbody>

    <?php else: ?>
    <tr>
        <td class="text-center table-total">合计</td>
        <td class="text-center table-total"><?php echo $total['type1']; ?></td>
        <td class="text-center table-total"><?php echo $total['type12']; ?></td>
        <td class="text-center table-total"><?php echo $total['type2']; ?></td>
        <td class="text-center table-total"><?php echo $total['type5']; ?></td>
        <td class="text-center table-total"><?php echo $total['type6']; ?></td>
        <td class="text-center table-total"><?php echo $total['type8']; ?></td>
        <td class="text-center table-total"><?php echo $total['type9']; ?></td>
        <td class="text-center table-total"><?php echo $total['type10']; ?></td>
        <td class="text-center table-total"><?php echo $total['sum']; ?></td>
    </tr>

    <tr>
        <th class="text-center">
            日期 <a href="javascript:void(0);" class="sort_toggle" onclick="common_module.sort_toggle()"></a>
        </th>
        <th class="text-center">待开发<a href="javascript:void(0);" data-toggle="tooltip" title="编辑商品的基本信息"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th class="text-center">文案<a href="javascript:void(0);" data-toggle="tooltip" title="编辑商品的文案信息与仓库属性"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th class="text-center">初审<a href="javascript:void(0);" data-toggle="tooltip" title="初步审核商品信息"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th class="text-center">采样<a href="javascript:void(0);" data-toggle="tooltip" title="采集样品信息、创建样品单"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th class="text-center">拍图<a href="javascript:void(0);" data-toggle="tooltip" title="拍摄商品图片"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th class="text-center">美工<a href="javascript:void(0);" data-toggle="tooltip" title="美化并上传图片"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th class="text-center">终审<a href="javascript:void(0);" data-toggle="tooltip" title="最终审核商品信息"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th class="text-center">已完成<a href="javascript:void(0);" data-toggle="tooltip" title="已完成开发商品"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th class="text-center">合计</th>
    </tr>
    </thead>
    <tbody id="scroll_table_head">
    <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
    <tr>
        <td class="text-center"><?php echo $vo['one']; ?></td>
        <td class="text-center"><?php if(isset($vo['type1'])): ?><?php echo $vo['type1']; else: ?>0<?php endif; ?></td>
        <td class="text-center"><?php if(isset($vo['type12'])): ?><?php echo $vo['type12']; else: ?>0<?php endif; ?></td>
        <td class="text-center"><?php if(isset($vo['type2'])): ?><?php echo $vo['type2']; else: ?>0<?php endif; ?></td>
        <td class="text-center"><?php if(isset($vo['type5'])): ?><?php echo $vo['type5']; else: ?>0<?php endif; ?></td>
        <td class="text-center"><?php if(isset($vo['type6'])): ?><?php echo $vo['type6']; else: ?>0<?php endif; ?></td>
        <td class="text-center"><?php if(isset($vo['type8'])): ?><?php echo $vo['type8']; else: ?>0<?php endif; ?></td>
        <td class="text-center"><?php if(isset($vo['type9'])): ?><?php echo $vo['type9']; else: ?>0<?php endif; ?></td>
        <td class="text-center"><?php if(isset($vo['type10'])): ?><?php echo $vo['type10']; else: ?>0<?php endif; ?></td>
        <td class="text-center"><?php echo $vo['sum']; ?></td>
    </tr>
    <?php endforeach; endif; else: echo "" ;endif; ?>
    </tbody>
    <?php endif; ?>

</table>

        <?php else: ?>
        <script src="/assets/dist/js/common.js"></script>
<script src="/assets/plugins/echarts/echarts.min.js"></script>
<div id="chartmain" style="width:100%; height:780px;"></div>
<script type="application/javascript">

</script>

        <?php endif; ?>
    </div>

    <div class="batch-bar clearfix"></div>
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
<script src="/assets/components/datatables/datatables.min.js"></script>
<!-- iCheck 1.0.1 -->
<script src="/assets/plugins/iCheck/icheck.min.js"></script>
<!-- AdminLTE App -->
<script src="/assets/dist/js/adminlte.min.js"></script>
<script src="/assets/plugins/layer/layer.js"></script>
<script src="/assets/plugins/lazyload.js"></script>
<!-- datatables -->
<!-- <script src="/assets/dist/js/jquery.floatThead.min.js"></script> -->
<script src="/assets/dist/js/default.js"></script>
<script src="/assets/dist/js/common.js"></script>
<script src="/assets/dist/js/<?php echo $config['modulename']; ?>.js"></script>

<!-- 这个文件结构，有问题，不能给单个html文件加js -->
<script>
    // var page_str = '总共找到 <strong class="text-danger"><?php echo isset($list_total)?$list_total: ''; ?></strong> 条记录';
    var page_str = '';
    var order = [0, 'desc'];
    var page_len = 20;
    // 组织架构和 平台不进行排序
    <?php if(isset($params['type']) && (in_array($params['type'], ['organ', 'store']))): ?>
        var order = [];
        var page_len = 10000;
    <?php endif; ?>
    common_module.init_data_table(page_str, order, page_len);

    // 针对414 错误
    common_module.init_submit_form('manual_submit_form');

    <?php if(isset($params['type']) && !in_array($params['type'], ['platform'])): ?>
    // $('#scroll_table').floatThead({
    //     autoReflow: true,
    // });
    <?php endif; ?>
</script>
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
</body>
</html>