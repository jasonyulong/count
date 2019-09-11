<?php if (!defined('THINK_PATH')) exit(); /*a:9:{s:89:"/opt/web/count/development/public/../application/count/view/finance/index/index_date.html";i:1547190894;s:69:"/opt/web/count/development/application/count/view/layout/default.html";i:1544600061;s:66:"/opt/web/count/development/application/count/view/common/meta.html";i:1550824652;s:68:"/opt/web/count/development/application/count/view/common/header.html";i:1550824652;s:79:"/opt/web/count/development/application/count/view/finance/index/date_table.html";i:1550824652;s:79:"/opt/web/count/development/application/count/view/finance/index/date_chart.html";i:1544600061;s:66:"/opt/web/count/development/application/count/view/layout/page.html";i:1544600061;s:68:"/opt/web/count/development/application/count/view/common/script.html";i:1550824652;s:69:"/opt/web/count/development/application/count/view/layout/btn_top.html";i:1550824653;}*/ ?>
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
            <li class="<?php echo $type=='date'?'active' : ''; ?>"><a href="<?php echo url('/count/finance/index', ['type' => 'date']); ?>">按日期</a></li>
            <li class="<?php echo $type=='organ'?'active' : ''; ?>"><a href="<?php echo url('/count/finance/index', ['type' => 'organ']); ?>">按组织架构</a></li>
            <li class="<?php echo $type=='platform'?'active' : ''; ?>"><a href="<?php echo url('/count/finance/index', ['type' => 'platform']); ?>">按平台</a></li>
            <li class="<?php echo $type=='kfuser'?'active' : ''; ?>"><a href="<?php echo url('/count/finance/index', ['type' => 'kfuser']); ?>">按开发员</a></li>
            <li class="<?php echo $type=='seller'?'active' : ''; ?>"><a href="<?php echo url('/count/finance/index', ['type' => 'seller']); ?>">按销售员</a></li>
            <li class="<?php echo $type=='account'?'active' : ''; ?>"><a href="<?php echo url('/count/finance/index', ['type' => 'account']); ?>">按账号</a></li>
            <li class="pull-right">
                <div class="btn-toolbar" role="toolbar">
                    <div class="btn-group padding-top8 paddint-right5">
                        <a href="<?php echo url('/count/finance/index', array_merge($params, ['type' => $type, 'model' => 'table'])); ?>" data-toggle="tooltip"
                           class="btn btn-xs btn-default <?php echo $model=='table'?'active' : ''; ?>"
                           title="列表模式"><span class="fa fa-fw fa-th-large"></span></a>
                        <a href="<?php echo url('/count/finance/index', array_merge($params,['type' => $type, 'model' => 'chart'])); ?>" data-toggle="tooltip"
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
                <div class="box-header with-bfinanceamount">
                    <form action="<?php echo url('/count/finance/index',$params); ?>" method="get" class="form-inline froms clearfix">
                        <input type="hidden" name="type" value="<?php echo $type; ?>">
                        <input type="hidden" name="model" value="<?php echo $model; ?>">
                        <input type="hidden" name="p" value="<?php echo $params['p']; ?>">
                        <input type="hidden" name="ps" value="<?php echo $params['ps']; ?>">
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
                                <label><input type="radio" name="checkDate" class="" onchange="order_model.checked_date(this)" value="today" <?php if($params['checkDate']=='today'): ?>checked<?php endif; ?>><small>今天</small></label>
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
    <i class="icon fa fa-warning"></i> 说明：根据发货时间来统计订单金额和各种费用
</div>
<table class="table table-bordered table-hover dataTable table-striped js-table">
    <thead>
    <tr>
        <td class="text-center table-total">合计</td>
        <td class="text-center table-total"><?php echo $total['total']; ?></td>
        <td class="text-center table-total"><?php echo $total['cost']; ?></td>
        <td class="text-center table-total"><?php echo $total['freight']; ?></td>
        <td class="text-center table-total"><?php echo $total['onlinefee']; ?></td>
        <td class="text-center table-total"><?php echo $total['material']; ?></td>
        <td class="text-center table-total"><?php echo $total['platform_fee']; ?></td>
        <td class="text-center table-total"><?php echo $total['paypal']; ?></td>
        <td class="text-center table-total"><?php echo $total['commission']; ?></td>
        <td class="text-center table-total"><?php echo $total['gross']; ?></td>
        <td class="text-center table-total"></td>
        <!--<td class="text-center table-total"><?php echo $total['refunds']; ?></td>-->
        <!--<td class="text-center table-total"><?php echo $total['refunds_rate']; ?></td>-->
    </tr>
    <tr>
        <th class="text-center">日期</th>
        <th class="text-center">总金额($)<a href="javascript:void(0);" data-toggle="tooltip" title="已发货订单总金额"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th class="text-center">成本($)<a href="javascript:void(0);" data-toggle="tooltip" title="成本=商品实际成本+包材费"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th class="text-center">物流运费($)<a href="javascript:void(0);" data-toggle="tooltip" title="从物流商获取的运费"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th class="text-center">线上运费($)<a href="javascript:void(0);" data-toggle="tooltip" title="买家支付运费"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th class="text-center">包材费($)<a href="javascript:void(0);" data-toggle="tooltip" title="包装材料费用"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th class="text-center">转换费($)<a href="javascript:void(0);" data-toggle="tooltip" title="货币转换费"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th class="text-center">Papal($)<a href="javascript:void(0);" data-toggle="tooltip" title="Papal手续费"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th class="text-center">佣金($)<a href="javascript:void(0);" data-toggle="tooltip" title="平台收取的费用"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th class="text-center">毛利($)<a href="javascript:void(0);" data-toggle="tooltip" title="订单的利润"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th class="text-center">毛利率%<a href="javascript:void(0);" data-toggle="tooltip" title="订单的利润率"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <!--<th class="text-center">退款金额($)<a href="javascript:void(0);" data-toggle="tooltip" title="退款订单的总金额"><i class="fa fa-fw fa-question-circle"></i></a></th>-->
        <!--<th class="text-center">退款占比%<a href="javascript:void(0);" data-toggle="tooltip" title="退款占比=退款金额/总金额"><i class="fa fa-fw fa-question-circle"></i></a></th>-->
    </tr>
    </thead>
    <tbody id="scroll_table_head">
    <?php if(is_array($data) || $data instanceof \think\Collection || $data instanceof \think\Paginator): $i = 0; $__LIST__ = $data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
    <tr>
        <td class="text-center">
            <?php if($params['checkDate'] == 'month'): ?>
            <?php echo $vo['year']; ?>-<?php echo $vo['month']; else: ?>
            <?php echo $vo['year']; ?>-<?php echo $vo['month']; ?>-<?php echo $vo['days']; endif; ?>
        </td>
        <td class="text-center"><?php echo round($vo['total'], 2); ?></td>
        <td class="text-center"><?php echo round($vo['cost'], 2); ?></td>
        <td class="text-center"><?php echo round($vo['freight'], 2); ?></td>
        <td class="text-center"><?php echo round($vo['onlinefee'], 2); ?></td>
        <td class="text-center"><?php echo round($vo['material'],2); ?></td>
        <td class="text-center"><?php echo round($vo['platform_fee'], 2); ?></td>
        <td class="text-center"><?php echo round($vo['paypal'], 2); ?></td>
        <td class="text-center"><?php echo round($vo['commission'], 2); ?></td>
        <td class="text-center"><?php echo round($vo['gross'], 2); ?></td>
        <td class="text-center"><?php echo $vo['gross_rate']; ?> %</td>
        <!--<td class="text-center"><?php echo round($vo['refunds'], 2); ?></td>-->
        <!--<td class="text-center"><?php echo $vo['refunds_rate']; ?> %</td>-->
    </tr>
    <?php endforeach; endif; else: echo "" ;endif; ?>
    </tbody>
</table>

            <?php else: ?>
            <script src="/assets/plugins/echarts/echarts.min.js"></script>
<div id="chartmain" style="width:100%; height:600px;"></div>
<script type="application/javascript">
    // 基于准备好的dom，初始化echarts实例
    var myChart = echarts.init(document.getElementById('chartmain'));
   option = {
        title : {
            text: '',
            subtext: ''
        },
        tooltip : {
            trigger: 'axis'
        },
        legend: {
            data:['总金额($)','毛利($)']
        },
        toolbox: {
            show : true,
            feature : {
                dataView : {show: true, readOnly: false},
                magicType : {show: true, type: ['bar','line']},
                restore : {show: true},
                saveAsImage : {show: true}
            }
        },
        calculable : true,
        xAxis : [
            {
                type : 'category',
                data : <?php echo $jsonName; ?>
            }
        ],
        yAxis : [
            {
                type : 'value'
            }
        ],
        series : [
            {
                name:'总金额($)',
                type:'line',
                data:<?php echo $jsonTotal; ?>,
                markPoint : {
                    data : [
                        {type : 'max', name: '最大值'},
                        {type : 'min', name: '最小值'}
                    ]
                },
                markLine : {
                    data : [
                        {type : 'average', name: '平均值'}
                    ]
                }
            },
            {
                name:'毛利($)',
                type:'line',
                data:<?php echo $jsonGross; ?>,
                markPoint : {
                    data : [
                        {type : 'max', name: '最大值'},
                        {type : 'min', name: '最小值'}
                    ]
                },
                markLine : {
                    data : [
                        {type : 'average', name : '平均值'}
                    ]
                }
            }
        ]
    };

    // 使用刚指定的配置项和数据显示图表。
    myChart.setOption(option);
</script>

            <?php endif; ?>
        </div>
        <!--<div class="batch-bar clearfix">
            
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






        </div>-->
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