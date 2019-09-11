<?php if (!defined('THINK_PATH')) exit(); /*a:9:{s:87:"/opt/web/count/development/public/../application/count/view/order/index/index_date.html";i:1544600061;s:69:"/opt/web/count/development/application/count/view/layout/default.html";i:1544600061;s:66:"/opt/web/count/development/application/count/view/common/meta.html";i:1550824652;s:68:"/opt/web/count/development/application/count/view/common/header.html";i:1550824652;s:72:"/opt/web/count/development/application/count/view/order/index/table.html";i:1551349980;s:72:"/opt/web/count/development/application/count/view/order/index/chart.html";i:1544600061;s:66:"/opt/web/count/development/application/count/view/layout/page.html";i:1544600061;s:68:"/opt/web/count/development/application/count/view/common/script.html";i:1550824652;s:69:"/opt/web/count/development/application/count/view/layout/btn_top.html";i:1550824653;}*/ ?>
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
            <li class="<?php echo $type=='date'?'active' : ''; ?>"><a href="<?php echo url('/count/order/index', ['type' => 'date']); ?>">按日期</a></li>
            <li class="<?php echo $type=='platform'?'active' : ''; ?>"><a href="<?php echo url('/count/order/index', ['type' => 'platform']); ?>">按平台</a></li>
            <li class="pull-right">
                <div class="btn-toolbar" role="toolbar">
                    <div class="btn-group padding-top8 paddint-right5">
                        <a href="<?php echo url('/count/order/index', array_merge($params, ['type' => $type, 'model' => 'table'])); ?>" data-toggle="tooltip"
                           class="btn btn-xs btn-default <?php echo $model=='table'?'active' : ''; ?>"
                           title="列表模式"><span class="fa fa-fw fa-th-large"></span></a>
                        <a href="<?php echo url('/count/order/index', array_merge($params,['type' => $type, 'model' => 'chart'])); ?>" data-toggle="tooltip"
                           class="btn btn-xs btn-default <?php echo $model=='chart'?'active' : ''; ?>"
                           title="图表模式"><span class="fa fa-fw fa-bar-chart-o"></span></a>
                    </div>
                </div>
            </li>
            <li class="pull-right alert-tips">
                <i class="icon fa fa-warning"></i> 统计数据存在一定的延迟性, 请勿与订单实时对比.
            </li>
        </ul>
        <div class="box-body">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <form action="<?php echo url('/count/order/index', array_merge($params,['type' => $type, 'model' => $model]), ''); ?>" method="get" class="form-inline froms clearfix">

                        <input type="hidden" name="type" value="date">
                        <input type="hidden" name="ps" value="<?php echo $params['ps']; ?>">
                        <input type="hidden" name="p" value="<?php echo $params['p']; ?>">
                        <input type="hidden" name="model" value="<?php echo $model; ?>">

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
            <?php if($model == 'table'): ?>
                <div class="text-danger">
    <i class="icon fa fa-warning"></i> 说明：所有状态的统计都不包含因为拆分或者合并产生的作废单
</div>
<table class="table table-bordered table-hover dataTable display nowrap compact table-striped js-table">
    <thead>
    <tr>
        <td class="text-center table-total">合计</td>
        <td class="text-center table-total"><?php echo $total_data['sum_totals']; ?></td>
        <td class="text-center table-total"><?php echo $total_data['sum_can_send']; ?></td>
        <td class="text-center table-total"><?php echo $total_data['sum_noships']; ?></td>
        <td class="text-center table-total"><?php echo $total_data['sum_ships']; ?></td>
        <td class="text-center table-total"><?php echo $total_data['sum_overs']; ?></td>
        <td class="text-center table-total"><?php echo $total_data['sum_resends']; ?></td>
        <td class="text-center table-total"><?php echo $total_data['sum_refunds']; ?></td>
        <td class="text-center table-total"><?php echo $total_data['sum_returns']; ?></td>
        <td class="text-center table-total"><?php echo $total_data['sum_recycles']; ?></td>
        <td class="text-center table-total"><?php echo $total_data['sum_recycles_system']; ?></td>
        <td class="text-center table-total"><?php echo $total_data['sum_total_ship']; ?></td>
    </tr>
    <tr>
        <th class="text-center">
            <?php if($params['type'] == 'date'): ?>
            日期 <a href="javascript:void(0);" class="sort_toggle" onclick="common_module.sort_toggle()"></a>
            <?php else: ?>
            平台
            <?php endif; ?>
        </th>
        <th class="text-center">总订单数 <a href="javascript:void(0);" data-container='body' data-toggle="tooltip" title="总订单数=原始订单数+补发订单数+拆分创建订单数+手工订单数+合并后订单数+作废订单数"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th class="text-center">可发货数 <a href="javascript:void(0);" data-container='body' data-toggle="tooltip" title="可发货数=总订单数-作废订单数"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th class="text-center">未发货数 <a href="javascript:void(0);" data-container='body' data-toggle="tooltip" title="未发货数=进系统后但是并没有发货(排除了作废订单)"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th class="text-center">已发货数 <a href="javascript:void(0);" data-container='body' data-toggle="tooltip" title="已发货数=进系统并已发货订单数"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th class="text-center">已完成数 <a href="javascript:void(0);" data-container='body' data-toggle="tooltip" title="已完成数=进系统并已经确认利润订单数"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th class="text-center">补发数 <a href="javascript:void(0);" data-container='body' data-toggle="tooltip" title="补发数=手动创建的补发订单数"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th class="text-center">退款数 <a href="javascript:void(0);" data-container='body' data-toggle="tooltip" title="退款数=获取到已产生退款订单数"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th class="text-center">退货数 <a href="javascript:void(0);" data-container='body' data-toggle="tooltip" title="退货数=产生退货订单数"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th class="text-center">手工作废数 <a href="javascript:void(0);" data-container='body' data-toggle="tooltip" title="作废数=手工操作进回收站订单数"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th class="text-center">系统作废数 <a href="javascript:void(0);" data-container='body' data-toggle="tooltip" title="作废数=系统操作进回收站订单数"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th class="text-center">仓库发货数 <a href="javascript:void(0);" data-container='body' data-toggle="tooltip" title="仓库发货数=仓库实际发货订单总数"><i class="fa fa-fw fa-question-circle"></i></a></th>
    </tr>
    </thead>
    <tbody id='scroll_table_head'>
    <?php foreach($list as $item): ?>
    <tr>
        <td class="text-center">
            <?php if($params['type'] == 'platform'): ?>
                <?php echo $item['platform']; else: if($params['checkDate'] == 'day'): ?>
                    <?php echo $item['year']; ?>-<?php echo $item['month']; ?>-<?php echo $item['days']; else: ?>
                    <?php echo $item['year']; ?>-<?php echo $item['month']; endif; endif; ?>
        </td>
        <td class="text-center">
            <span class="block" data-container='body' data-toggle="tooltip" data-placement="bottom"  title="<?php foreach($item['type_list'] as $value): ?><?php echo $value['type_name']; ?>:<?php echo $value['sum_totals']; ?>&nbsp;&nbsp;<?php endforeach; ?>">
            <?php echo $item['sum_totals']; ?>
            </span>
        </td>
        <td class="text-center"><?php echo $item['sum_can_send']; ?></td>
        <td class="text-center">
            <span class="block" data-container='body' data-toggle="tooltip" data-placement="bottom"  title="<?php foreach($item['type_list'] as $value): ?><?php echo $value['type_name']; ?>:<?php echo $value['sum_noships']; ?>&nbsp;&nbsp;<?php endforeach; ?>">
            <?php echo $item['sum_noships']; ?>
            </span>
        </td>
        <td class="text-center">
            <span class="block" data-container='body' data-toggle="tooltip" data-placement="bottom"  title="<?php foreach($item['type_list'] as $value): ?><?php echo $value['type_name']; ?>:<?php echo $value['sum_ships']; ?>&nbsp;&nbsp;<?php endforeach; ?>">
            <?php echo $item['sum_ships']; ?>
            </span>
        </td>
        <td class="text-center">
            <span class="block" data-container='body' data-toggle="tooltip" data-placement="bottom"  title="<?php foreach($item['type_list'] as $value): ?><?php echo $value['type_name']; ?>:<?php echo $value['sum_overs']; ?>&nbsp;&nbsp;<?php endforeach; ?>">
            <?php echo $item['sum_overs']; ?>
            </span>
        </td>
        <td class="text-center"><?php echo $item['sum_resends']; ?></td>
        <td class="text-center"><?php echo $item['sum_refunds']; ?></td>
        <td class="text-center"><?php echo $item['sum_returns']; ?></td>
        <td class="text-center"><?php echo $item['sum_recycles']; ?></td>
        <td class="text-center"><?php echo $item['sum_recycles_system']; ?></td>
        <td class="text-center"><?php echo $item['sum_total_ship']; ?></td>
    </tr>
    <?php endforeach; ?>

    </tbody>
</table>

            <?php else: ?>
                <script src="/assets/dist/js/common.js"></script>
<script src="/assets/plugins/echarts/echarts.min.js"></script>
<div id="chartmain" style="width:100%; height:780px;"></div>
<script type="application/javascript">
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
            <!--
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





-->
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