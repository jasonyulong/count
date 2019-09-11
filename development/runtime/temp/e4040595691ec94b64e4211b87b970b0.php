<?php if (!defined('THINK_PATH')) exit(); /*a:9:{s:83:"/opt/web/count/development/public/../application/count/view/sku/packages/index.html";i:1544600060;s:69:"/opt/web/count/development/application/count/view/layout/default.html";i:1544600061;s:66:"/opt/web/count/development/application/count/view/common/meta.html";i:1550824652;s:68:"/opt/web/count/development/application/count/view/common/header.html";i:1550824652;s:78:"/opt/web/count/development/application/count/view/sku/packages/index_left.html";i:1544600060;s:73:"/opt/web/count/development/application/count/view/sku/packages/table.html";i:1544600060;s:73:"/opt/web/count/development/application/count/view/sku/packages/chart.html";i:1544600060;s:68:"/opt/web/count/development/application/count/view/common/script.html";i:1550824652;s:69:"/opt/web/count/development/application/count/view/layout/btn_top.html";i:1550824653;}*/ ?>
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
    <div class="col-md-2" id="box-left" style="padding:0px;">
        <div class="box box-solid">
    <div class="box-tools widget-add hide"><i class="fa fa-fw fa-chevron-right"></i></div>
    <div class="box-header with-border">
        <h3 class="box-title">产品包</h3>
        <div class="box-tools">
            <button type="button" onclick="layers.prompt('<?php echo url('/count/sku/packages/addCategory'); ?>', '新增分类', '400px', '300px')" data-url="" class="btn btn-box-tool"
                    aria-haspopup="true" aria-expanded="false"><i class="fa fa-plus-square-o"></i>
            </button>
            <button type="button" onclick="layers.prompt('<?php echo url('/count/sku/packages/category'); ?>', '分类管理', '700px', '600px')" data-url="" class="btn btn-box-tool"
                    aria-haspopup="true" aria-expanded="false"><i class="fa fa-gear"></i>
            </button>
            <button type="button" class="btn btn-box-tool widget-left" data-id="box-left"><i class="fa fa-fw fa-chevron-left"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
        </div>
    </div>
    <div class="box-body no-padding">
        <div id="jstree">
            <ul>
                <!--第一层 开始-->
                <?php if(is_array($category123) || $category123 instanceof \think\Collection || $category123 instanceof \think\Paginator): $i = 0; $__LIST__ = $category123;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$value): $mod = ($i % 2 );++$i;?>
                <li>
                    <?php echo $value['title']; if(isset($value['group'])): ?>
                    <ul>
                        <?php if(is_array($value['group']) || $value['group'] instanceof \think\Collection || $value['group'] instanceof \think\Paginator): $i = 0; $__LIST__ = $value['group'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v1): $mod = ($i % 2 );++$i;?>
                        <li><a href="<?php echo url('/count/sku/Packages'); ?>&keys=<?php echo $v1['group_sn']; ?>" <?php if($params['keys'] == $v1['group_sn']): ?>class="jstree-anchor jstree-clicked"<?php endif; ?>><?php echo $v1['group_sn']; ?>&#45;&#45;<?php echo $v1['title']; ?></a></li>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                    </ul>
                    <?php endif; ?>

                    <!--第二层 开始-->
                    <ul>
                        <?php if(isset($value['rank1'])): if(is_array($value['rank1']) || $value['rank1'] instanceof \think\Collection || $value['rank1'] instanceof \think\Paginator): $i = 0; $__LIST__ = $value['rank1'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?>
                        <li>
                            <?php echo $val['rank2']['title']; if(isset($val['rank2']['group'])): ?>
                            <ul>
                                <?php if(is_array($val['rank2']['group']) || $val['rank2']['group'] instanceof \think\Collection || $val['rank2']['group'] instanceof \think\Paginator): $i = 0; $__LIST__ = $val['rank2']['group'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v2): $mod = ($i % 2 );++$i;?>
                                <li><a href="<?php echo url('/count/sku/Packages'); ?>&keys=<?php echo $v2['group_sn']; ?>" <?php if($params['keys'] == $v2['group_sn']): ?>class="jstree-anchor jstree-clicked"<?php endif; ?>><?php echo $v2['group_sn']; ?>&#45;&#45;<?php echo $v2['title']; ?></a></li>
                                <?php endforeach; endif; else: echo "" ;endif; ?>
                            </ul>
                            <?php endif; ?>

                            <!--第三层 开始-->
                            <?php if(isset($val['rank3'])): ?>
                            <ul>
                                <?php if(is_array($val['rank3']) || $val['rank3'] instanceof \think\Collection || $val['rank3'] instanceof \think\Paginator): $i = 0; $__LIST__ = $val['rank3'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                                <li>
                                    <?php echo $vo['title']; if(isset($vo['group'])): ?>
                                    <ul>
                                        <?php if(is_array($vo['group']) || $vo['group'] instanceof \think\Collection || $vo['group'] instanceof \think\Paginator): $i = 0; $__LIST__ = $vo['group'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v3): $mod = ($i % 2 );++$i;?>
                                        <li><a href="<?php echo url('/count/sku/Packages'); ?>&keys=<?php echo $v3['group_sn']; ?>" <?php if($params['keys'] == $v3['group_sn']): ?>class="jstree-anchor jstree-clicked"<?php endif; ?>><?php echo $v3['group_sn']; ?>&#45;&#45;<?php echo $v3['title']; ?></a></li>
                                        <?php endforeach; endif; else: echo "" ;endif; ?>
                                    </ul>
                                    <?php endif; ?>
                                </li>
                                <?php endforeach; endif; else: echo "" ;endif; ?>
                            </ul>
                            <?php endif; ?>
                            <!--第三层 结束-->

                        </li>
                        <?php endforeach; endif; else: echo "" ;endif; endif; ?>
                    </ul>
                    <!--第二层 结束-->

                </li>
                <?php endforeach; endif; else: echo "" ;endif; ?>
                <!--第一层 结束-->
            </ul>
        </div>
    </div>
</div>
    </div>
    <div class="col-md-10" id="box-right">
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                <li><a href="javascript:void(0)">SKU产品包销量报表</a></li>
                <li class="pull-right">
                    <div class="btn-toolbar" role="toolbar">
                        <div class="btn-group padding-top8 paddint-right5">
                            <!--<a href="<?php echo url('/count/sku/Packages', array_merge($params, ['model' => 'table'])); ?>" data-toggle="tooltip"-->
                               <!--class="btn btn-xs btn-default <?php echo $model=='table'?'active' : ''; ?>"-->
                               <!--title="列表模式"><span class="fa fa-fw fa-th-large"></span></a>-->
                            <!--<a href="<?php echo url('/count/sku/Packages', array_merge($params,['model' => 'chart'])); ?>" data-toggle="tooltip"-->
                            <!--class="btn btn-xs btn-default <?php echo $model=='chart'?'active' : ''; ?>"-->
                            <!--title="图表模式"><span class="fa fa-fw fa-bar-chart-o"></span></a>-->
                        </div>
                    </div>
                </li>
            </ul>
            <div class="box-body">
                <form action="<?php echo url('/count/sku/Packages', array_merge($params,['model' => $model])); ?>" method="get" class="form-inline froms clearfix">
                    <div class="form-group">
                        <select class="selectpicker show-tick"  name="adduser" data-actions-box="true" data-live-search="true">
                            <option value="">添加人</option>
                            <?php if(!empty($adduser)): foreach($adduser as $key=>$adduser_list): ?>
                            <option value="<?php echo $adduser_list['adduser']; ?>" <?php if($params['adduser']== $adduser_list['adduser']): ?>selected<?php endif; ?>><?php echo $adduser_list['adduser']; ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <select class="selectpicker show-tick"  name="group_sn" data-actions-box="true" data-live-search="true">
                            <option value="">产品包</option>
                            <?php if(!empty($groupsn)): foreach($groupsn as $kk=>$groupsn_list): ?>
                            <option value="<?php echo $groupsn_list['group_sn']; ?>" <?php if($params['group_sn'] == $groupsn_list['group_sn']): ?>selected<?php endif; ?>><?php echo $groupsn_list['group_sn'].'--'.$groupsn_list['title']; ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <div class="input-group laydate-group">
                            <input type="text" class="input-sm form-control input-date datepicker" name="time_start" size="12" value="<?php echo $params['time_start']; ?>" placeholder="创建开始时间"/>
                            <span class="input-group-addon">到</span>
                            <input type="text" class="input-sm form-control input-date datepicker" name="time_end" size="12" value="<?php echo $params['time_end']; ?>" placeholder="创建结束时间"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <input class="form-control input-sm" value="<?php echo $params['keyword']; ?>" name="keyword" placeholder="产品包关键字"/>
                    </div>
                    <div class="form-group">
                        <button class="btn btn-primary btn-sm" type="submit" name="submit"><i class="glyphicon glyphicon-search"></i> 确定搜索&nbsp;</button>
                    </div>
                </form>
            </div>

            <div class="tab-content">
                <?php if($model == 'table'): ?>
                <table class="table table-bordered table-hover dataTable table-striped">
    <thead>
    <tr>
        <th class="add-background-color y-center">
            <div class="btn-group">
                <button type="button" onclick="order_model.addPackage(this)" data-url="<?php echo url('/count/sku/Packages/updatePackage', array($params,'model' => $model)); ?>" class="btn btn-success btn-sm" aria-haspopup="true" aria-expanded="false">添加产品包
                </button>
            </div>
        </th>
        <?php if($data): ?>
        <th colspan="1" class="add-background-color y-center" style="max-width: 200px;">
            <?php echo $data[0]['group_name']; ?>（<?php echo $data[0]['group_sn']; ?>)&nbsp;&nbsp;
            <a href="javascript:void(0)" onclick="order_model.addPackage(this)" data-url="<?php echo url('/count/sku/Packages/updatePackage', array($params,'id'=>$id)); ?>" title="编辑" class="glyphicon glyphicon-edit"></a>
            &nbsp;&nbsp;<a href="javascript:void(0)" onclick='order_model.deleteData("<?php echo $id; ?>",this)' data-url="<?php echo url('/count/sku/Packages/deletePackage'); ?>" title="删除" class="glyphicon glyphicon-remove-sign"></a>
        </th>
        <?php endif; foreach($qtyTotals as $key=>$qty_total_list): ?>
        <th class="add-background-color-border text-center y-center"><?php echo $qty_total_list; ?></th>
        <?php endforeach; if($total): ?>
        <th class="add-background-color-border text-center y-center"><?php echo $total; ?></th>
        <?php endif; ?>
    </tr>
    <tr>
        <th class="text-center y-center">SKU编号</th>
        <th class="text-center y-center">图片</th>
        <th class="text-center adaptive">标题</th>
        <?php foreach($dates as $dates_list): ?>
        <th class="text-center y-center"><?php echo $dates_list; ?></th>
        <?php endforeach; ?>
        <td class="text-center y-center">合计</td>
    </tr>
    </thead>
    <tbody>
    <?php foreach($data as $ke=>$data_list): ?>
    <tr>
        <td class="text-center y-center sku-td-width" title="<?php echo $data_list['title']; ?>"><?php echo $data_list['sku']; ?></td>
        <td class="text-center y-center pic-td-width" title="<?php echo $data_list['title']; ?>">
            <?php if($data_list['group_sku'] == 1): ?>
            <div class="font-color" onclick="order_model.viewDetail(this,'<?php echo $data_list['sku']; ?>')" data-sku=""
                 data-url="<?php echo url('/count/sku/Packages/viewdetail', array_merge(['time_start'=>$params['time_start'],'time_end'=>$params['time_end'],'goods_sn'=>$data_list['goods_sn'],'sku'=>$data_list['sku']],['model' => $model])); ?>">
                子系列
            </div>
            <?php else: ?>
            <img class="img-src-size skuimg" title="点击查看大图" <?php if((!empty($data_list['goods_pic']))): ?>onclick="order_model.skuimage(this)"<?php endif; ?> bigsrc="<?php echo $data_list['large']; ?>"
            src="<?php if((!empty($data_list['goods_pic']))): ?><?php echo $data_list['goods_pic']; else: ?>/assets/dist/img/no_picture.gif<?php endif; ?>" alt="<?php echo $data_list['title']; ?> ">
            <?php endif; ?>
        </td>
        <td class="text-center y-center title-td-width adaptive">
            <?php echo $data_list['title']; ?>
        </td>
        <?php foreach($data_list['qtyData'] as $kee=>$qty_data_list): ?>
        <td class="text-center y-center">
            <a href="javascript:void(0)" onclick="order_model.btobnumberDetail(this,'<?php echo $data_list['sku']; ?>','<?php echo $kee; ?>')"
               data-url="<?php echo url('/count/sku/Packages/btobnumberdetail', array_merge(['group_sku'=>$data_list['group_sku'],'time'=>$kee,'sku'=>$data_list['sku']],['model' => 'table'])); ?>"><?php echo $qty_data_list; ?></a>
            <br>
            <a href="javascript:void(0)" onclick="order_model.btobnumberDetail(this,'<?php echo $data_list['sku']; ?>','<?php echo $kee; ?>')"
               data-url="<?php echo url('/count/sku/Packages/btobnumberdetail', array_merge(['group_sku'=>$data_list['group_sku'],'time'=>$kee,'sku'=>$data_list['sku']],['model' => 'chart'])); ?>" data-toggle="tooltip"
               class="btn btn-xs btn-default <?php echo $model=='chart'?'active' : ''; ?>"
               title="图表模式"><span class="fa fa-fw fa-bar-chart-o"></span></a>
        </td>

        <?php endforeach; ?>
        <td class="text-center y-center">
            <a href="javascript:void(0)" onclick="order_model.totalDetail(this,'<?php echo $data_list['sku']; ?>')"
               data-url="<?php echo url('/count/sku/Packages/totaldetail', array_merge(['time_start'=>$params['time_start'],'time_end'=>$params['time_end'],'sku'=>$data_list['sku'],'group_sku'=>$data_list['group_sku'],'keys'=>$data_list['keys'],'values'=>$data_list['values']],['model' => 'table'])); ?>">
                <?php echo $data_list['qtySum']; ?>
            </a>
            <br>
            <a href="javascript:void(0)" onclick="order_model.totalDetail(this,'<?php echo $data_list['sku']; ?>')"
               data-url="<?php echo url('/count/sku/Packages/totaldetail', array_merge(['time_start'=>$params['time_start'],'time_end'=>$params['time_end'],'sku'=>$data_list['sku'],'group_sku'=>$data_list['group_sku'],'keys'=>$data_list['keys'],'values'=>$data_list['values']],['model' => 'chart'])); ?>" data-toggle="tooltip"
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
            data:['张三','李四','罗华','张磊','付华']
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
            data: ['08-11','08-12','08-13','08-14','08-15','08-16','08-17', '08-18','08-19','08-20','08-21','08-22','08-23','08-24']
        },
        yAxis: {
            type: 'value'
        },
        series: [
            {
                name:'张三',
                type:'line',
                stack: '总量',
                data:[120, 132, 101, 390, 330, 320, 210, 120, 132, 101, 134, 90, 230, 210]
            },
            {
                name:'李四',
                type:'line',
                stack: '总量',
                data:[120, 390, 330, 320, 90, 230, 210, 220, 182, 191, 234, 290, 330, 310]
            },
            {
                name:'罗华',
                type:'line',
                stack: '总量',
                data:[120, 132, 101, 134, 90, 230, 210, 150, 232, 201, 154, 190, 330, 410]
            },
            {
                name:'张磊',
                type:'line',
                stack: '总量',
                data:[390, 330, 320, 134, 90, 230, 210, 320, 332, 301, 334, 390, 330, 320]
            },
            {
                name:'付华',
                type:'line',
                stack: '总量',
                data:[120, 132, 101, 390, 330, 320, 820, 932, 901, 934, 1290, 1330, 1320,100]
            }
        ]
    };

    // 使用刚指定的配置项和数据显示图表。
    myChart.setOption(option);
</script>

                <?php endif; ?>
            </div>
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