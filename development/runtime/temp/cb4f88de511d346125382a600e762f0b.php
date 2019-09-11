<?php if (!defined('THINK_PATH')) exit(); /*a:10:{s:90:"/opt/web/count/development/public/../application/count/view/purchase/index/index_data.html";i:1547190894;s:69:"/opt/web/count/development/application/count/view/layout/default.html";i:1544600061;s:66:"/opt/web/count/development/application/count/view/common/meta.html";i:1550824652;s:68:"/opt/web/count/development/application/count/view/common/header.html";i:1550824652;s:83:"/opt/web/count/development/application/count/view/purchase/index/ordersn_table.html";i:1552989433;s:83:"/opt/web/count/development/application/count/view/purchase/index/partner_table.html";i:1550224951;s:79:"/opt/web/count/development/application/count/view/purchase/index/sku_table.html";i:1544600061;s:66:"/opt/web/count/development/application/count/view/layout/page.html";i:1544600061;s:68:"/opt/web/count/development/application/count/view/common/script.html";i:1550824652;s:69:"/opt/web/count/development/application/count/view/layout/btn_top.html";i:1550824653;}*/ ?>
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
            <li class="<?php echo $type=='ordersn'?'active' : ''; ?>"><a href="<?php echo url('/count/Purchase/index', ['type' => 'ordersn']); ?>">按采购单</a></li>
            <li class="<?php echo $type=='partner'?'active' : ''; ?>"><a href="<?php echo url('/count/Purchase/index', ['type' => 'partner']); ?>">按供应商</a></li>
            <li class="<?php echo $type=='sku'?'active' : ''; ?>"><a href="<?php echo url('/count/Purchase/index', ['type' => 'sku']); ?>">按SKU</a></li>
            <li class="pull-right alert-tips">
                <i class="icon fa fa-warning"></i> 统计数据存在一定的延迟性, 请勿实时对比.
            </li>
        </ul>
        <div class="box-body">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <form action="<?php echo url('/count/Purchase/index',$params); ?>" method="get" class="form-inline froms clearfix">
                        <input type="hidden" name="type" value="<?php echo $type; ?>">
                        <input type="hidden" name="sort" value="<?php echo $params['sort']; ?>">
                        <input type="hidden" name="sortkey" value="<?php echo $params['sortkey']; ?>">

                        <div class="form-group">
                            <label class="control-label text-right">时间：</label>
                        </div>
                        <div class="form-group">
                            <div class="form-group shotcut_day_div">
                                <div class="checkbox">
                                    <label><input type="radio" name="checkDate" class="" onchange="order_model.checked_date(this)" value="today" <?php if($params['checkDate']=='today'): ?>checked<?php endif; ?>><small>当日</small></label>
                                    <label><input type="radio" name="checkDate" class="" onchange="order_model.checked_date(this)" value="month" <?php if($params['checkDate']=='month'): ?>checked<?php endif; ?>><small>当月</small></label>
                                </div>
                            </div>
                            <div class="input-group laydate-group">
                                <input type="text" class="input-sm form-control input-date datepicker start" name="day_start" value="<?php echo $params['day_start']; ?>" placeholder="开始时间"/>
                                <span class="input-group-addon">到</span>
                                <input type="text" class="input-sm form-control input-date datepicker end" name="day_end" value="<?php echo $params['day_end']; ?>" placeholder="结束时间"/>
                            </div>
                        </div>

                        <div class="form-group">
                            <select class="selectpicker show-tick " title="采购单类型" name="order_type" id="order_type" data-actions-box="true" data-live-search="true">
                                <option value="">选择选择采购单类型</option>
                                <option value="1" <?php if($params['order_type'] == '1'): ?>selected<?php endif; ?> >网络</option>
                                <option value="2" <?php if($params['order_type'] == '2'): ?>selected<?php endif; ?> >市场</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <select class="selectpicker show-tick " title="选择采购员" name="cguser" id="cguser" data-actions-box="true" data-live-search="true">
                                <option value="">选择采购员</option>
                                <?php if(!empty($allCguser)): foreach($allCguser as $user_list): ?>
                                <option value="<?php echo $user_list['username']; ?>" <?php if($params['cguser'] == $user_list['username']): ?>selected<?php endif; ?>><?php echo $user_list['username']; ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <select class="selectpicker show-tick " title="选择付款方式" name="paytype" id="paytype" data-actions-box="true" data-live-search="true">
                                <option value="">选择选择付款方式</option>
                                <?php if(!empty($all_pay_type)): foreach($all_pay_type as $key => $type_list): ?>
                                <option value="<?php echo $key; ?>" <?php if($params['paytype'] == $key): ?>selected<?php endif; ?>><?php echo $type_list; ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <input type="text" value="<?php echo $params['partner_id']; ?>" id="partner_id" name="partner_id" onkeyup="purchase_model.get_providers(this)" class="form-control input-sm" placeholder="选择供应商">
                            <input type="hidden" name="factory" id="factory" value="<?php echo $params['factory']; ?>">
                            <div class="c_list" style="display:none;float: left" id="searchProviderList">
                            <ul class="c_list"></ul>
                            </div>
                        </div>
                        <?php if($type == 'sku'): ?>
                        <div class="form-group">
                            <input type="text" value="<?php echo $params['sku']; ?>" id="sku" name="sku" class="form-control input-sm" placeholder="sku关键字">
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
            <?php if($type == 'ordersn'): ?>
            <table class="table table-bordered table-hover dataTable table-striped">
    <thead>
    <tr>
        <td class="text-center table-total">合计</td>
        <td class="text-center table-total"></td>
        <td class="text-center table-total"></td>
        <td class="text-center table-total"></td>
        <td class="text-center table-total"></td>
        <td class="text-center table-total"><?php echo $total['total_amount']; ?></td>
        <td class="text-center table-total"><?php echo $total['total_paid']; ?></td>
        <td class="text-center table-total"><?php echo $total['total_wait_pay']; ?></td>
        <td class="text-center table-total"><?php echo $total['total_revenued']; ?></td>
        <td class="text-center table-total"><?php echo $total['total_collected']; ?></td>
        <td class="text-center table-total"><?php echo $total['total_real_pay']; ?></td>
        <td class="text-center table-total"><?php echo $total['total_total_collected']; ?></td>
    </tr>
    <tr>
        <th class="text-center">序号</th>
        <th class="text-center">采购单</th>
        <th class="text-center">采购员</th>
        <th class="text-center">付款类型</th>
        <th class="text-center">供应商</th>
        <th data-id="amount" class="text-center custom <?php if($params['sortkey'] == 'amount'): ?>sorting_<?php echo $params['sort']; else: ?>sorting<?php endif; ?>">采购总金额</th>
        <th data-id="paid"  class="text-center custom <?php if($params['sortkey'] == 'paid'): ?>sorting_<?php echo $params['sort']; else: ?>sorting<?php endif; ?>">已付款<a href="javascript:void(0);" data-toggle="tooltip" title="已经付款记录"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th data-id="wait_pay" class="text-center custom <?php if($params['sortkey'] == 'wait_pay'): ?>sorting_<?php echo $params['sort']; else: ?>sorting<?php endif; ?>">待付款<a href="javascript:void(0);" data-toggle="tooltip" title="所有采购价值 - 已经付款"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th data-id="revenued" class="text-center custom <?php if($params['sortkey'] == 'revenued'): ?>sorting_<?php echo $params['sort']; else: ?>sorting<?php endif; ?>">已收<a href="javascript:void(0);" data-toggle="tooltip" title="所有入库 + 公司损失金额"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th data-id="collected" class="text-center custom <?php if($params['sortkey'] == 'collected'): ?>sorting_<?php echo $params['sort']; else: ?>sorting<?php endif; ?>">待收<a href="javascript:void(0);" data-toggle="tooltip" title="采购价值 - （正常入库+损耗）"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th data-id="real_pay" class="text-center custom <?php if($params['sortkey'] == 'real_pay'): ?>sorting_<?php echo $params['sort']; else: ?>sorting<?php endif; ?>">实付款<a href="javascript:void(0);" data-toggle="tooltip" title="已付款-退款"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th data-id="total_collected" class="text-center custom <?php if($params['sortkey'] == 'total_collected'): ?>sorting_<?php echo $params['sort']; else: ?>sorting<?php endif; ?>">总收<a href="javascript:void(0);" data-toggle="tooltip" title="已收+等值换"><i class="fa fa-fw fa-question-circle"></i></a></th>
    </tr>
    </thead>
    <tbody>
    <?php if(is_array($data) || $data instanceof \think\Collection || $data instanceof \think\Paginator): $i = 0; $__LIST__ = $data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
    <tr>
        <td class="text-center">
           <?php echo $key+1; ?>
        </td>
        <td class="text-center"><?php echo $vo['ordersn']; ?></td>
        <td class="text-center"><?php echo $vo['cguser']; ?></td>
        <td class="text-center"><?php echo $vo['paytype']; ?></td>
        <td class="text-center"><?php echo $vo['partnerName']; ?></td>
        <td class="text-center"><?php echo $vo['amount']; ?></td>
        <td class="text-center"><?php echo $vo['paid']; ?></td>
        <td class="text-center"><?php echo $vo['wait_pay']; ?></td>
        <td class="text-center"><?php echo $vo['revenued']; ?></td>
        <td class="text-center"><?php echo $vo['collected']; ?></td>
        <td class="text-center"><?php echo $vo['real_pay']; ?></td>
        <td class="text-center"><?php echo $vo['total_collected']; ?></td>
    </tr>
    <?php endforeach; endif; else: echo "" ;endif; ?>
    </tbody>
</table>
            <?php endif; if($type == 'partner'): ?>
            <table class="table table-bordered table-hover dataTable table-striped">
    <thead>
    <tr>
        <td class="text-center table-total">合计</td>
        <td class="text-center table-total"></td>
        <td class="text-center table-total"></td>
        <td class="text-center table-total"><?php echo $total['total_amount']; ?></td>
        <td class="text-center table-total"><?php echo $total['total_fee']; ?></td>
        <td class="text-center table-total"><?php echo $total['total_paid']; ?></td>
        <td class="text-center table-total"><?php echo $total['total_wait_pay']; ?></td>
        <td class="text-center table-total"><?php echo $total['total_revenued']; ?></td>
        <td class="text-center table-total"><?php echo $total['total_collected']; ?></td>
        <td class="text-center table-total"><?php echo $total['total_real_pay']; ?></td>
        <td class="text-center table-total"><?php echo $total['total_total_collected']; ?></td>
        <td class="text-center table-total"></td>
    </tr>

    <tr>
        <th class="text-center">序号</th>
        <th class="text-center">供应商</th>
        <th class="text-center">付款方式</th>
        <th data-id="amount" class="text-center custom <?php if($params['sortkey'] == 'amount'): ?>sorting_<?php echo $params['sort']; else: ?>sorting<?php endif; ?>">采购总金额</th>
        <th data-id="ship_fee" class="text-center custom <?php if($params['sortkey'] == 'ship_fee'): ?>sorting_<?php echo $params['sort']; else: ?>sorting<?php endif; ?>">运费总金额</th>
        <th data-id="paid"  class="text-center custom <?php if($params['sortkey'] == 'paid'): ?>sorting_<?php echo $params['sort']; else: ?>sorting<?php endif; ?>">已付款<a href="javascript:void(0);" data-toggle="tooltip" title="已经付款记录"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th data-id="wait_pay" class="text-center custom <?php if($params['sortkey'] == 'wait_pay'): ?>sorting_<?php echo $params['sort']; else: ?>sorting<?php endif; ?>">待付款<a href="javascript:void(0);" data-toggle="tooltip" title="所有采购价值 - 已经付款"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th data-id="revenued" class="text-center custom <?php if($params['sortkey'] == 'revenued'): ?>sorting_<?php echo $params['sort']; else: ?>sorting<?php endif; ?>">已收<a href="javascript:void(0);" data-toggle="tooltip" title="所有入库 + 公司损失金额"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th data-id="collected" class="text-center custom <?php if($params['sortkey'] == 'collected'): ?>sorting_<?php echo $params['sort']; else: ?>sorting<?php endif; ?>">待收<a href="javascript:void(0);" data-toggle="tooltip" title="采购价值 - （正常入库+损耗）"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th class="text-center">实付款<a href="javascript:void(0);" data-toggle="tooltip" title="已付款-退款"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th class="text-center">总收<a href="javascript:void(0);" data-toggle="tooltip" title="已收+等值换"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th class="text-center">操作</th>
    </tr>
    </thead>
    <tbody>
    <?php if(is_array($data) || $data instanceof \think\Collection || $data instanceof \think\Paginator): $i = 0; $__LIST__ = $data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
    <tr>
        <td class="text-center">
            <?php echo $key+1; ?>
        </td>
        <td class="text-center"><?php echo $vo['partnerName']; ?></td>
        <td class="text-center">
            <?php echo $vo['paytype']; ?>
        </td>
        <td class="text-center"><?php echo $vo['amount']; ?></td>
        <td class="text-center"><?php echo $vo['ship_fee']; ?></td>
        <td class="text-center"><?php echo $vo['paid']; ?></td>
        <td class="text-center"><?php echo $vo['wait_pay']; ?></td>
        <td class="text-center"><?php echo $vo['revenued']; ?></td>
        <td class="text-center"><?php echo $vo['collected']; ?></td>
        <td class="text-center"><?php echo $vo['real_pay']; ?></td>
        <td class="text-center"><?php echo $vo['total_collected']; ?></td>
        <td class="text-center">
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                 查看图表 <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a style="cursor:pointer" onclick="layer.open({type: 2,title: '供应商【<?php echo $vo['partnerName']; ?>】金额走势',shadeClose: true,shade: 0.8,area: ['70%', '70%'],maxmin:true,content: '<?php echo url('/count/Purchase/Index/partnertrend', ['partner_id' => $vo['partner_id'],'type'=>'amount']); ?>'});" >金额走势</a>
                    </li>
                    <li>
                        <a style="cursor:pointer" onclick="layer.open({type: 2,title: '供应商【<?php echo $vo['partnerName']; ?>】采购量走势',shadeClose: true,shade: 0.8,area: ['70%', '70%'],maxmin:true,content: '<?php echo url('/count/Purchase/Index/partnertrend', ['partner_id' => $vo['partner_id'],'type'=>'qty']); ?>'});" >采购量走势</a>
                    </li>
                </ul>
            </div>
        </td>
    </tr>
    <?php endforeach; endif; else: echo "" ;endif; ?>
    </tbody>
</table>


            <?php endif; if($type == 'sku'): ?>
            
<table class="table table-bordered table-hover dataTable table-striped">
    <thead>
    <tr>
        <td class="text-center table-total">合计</td>
        <td class="text-center table-total"></td>
        <td class="text-center table-total"></td>
        <td class="text-center table-total"></td>
        <td class="text-center table-total"></td>
        <td class="text-center table-total"></td>
        <td class="text-center table-total"></td>
        <td class="text-center table-total"></td>
        <td class="text-center table-total"><?php echo $total['total_qty']; ?></td>
        <td class="text-center table-total"><?php echo $total['total_amount']; ?></td>
        <td class="text-center table-total"><?php echo $total['total_paid']; ?></td>
        <td class="text-center table-total"><?php echo $total['total_wait_pay']; ?></td>
        <td class="text-center table-total"><?php echo $total['total_revenued']; ?></td>
        <td class="text-center table-total"><?php echo $total['total_collected']; ?></td>
        <td class="text-center table-total"><?php echo $total['total_real_pay']; ?></td>
        <td class="text-center table-total"><?php echo $total['total_total_collected']; ?></td>
    </tr>
    <tr>
        <th class="text-center">序号</th>
        <th class="text-center">sku</th>
        <th class="text-center">品名</th>
        <th class="text-center">采购单号</th>
        <th class="text-center">供应商</th>
        <th class="text-center">仓库</th>
        <th class="text-center">采购员</th>
        <th class="text-center">采购均摊价</th>
        <th class="text-center">订购量</th>
        <th data-id="amount" class="text-center custom <?php if($params['sortkey'] == 'amount'): ?>sorting_<?php echo $params['sort']; else: ?>sorting<?php endif; ?>">总金额</th>
        <th data-id="paid"  class="text-center custom <?php if($params['sortkey'] == 'paid'): ?>sorting_<?php echo $params['sort']; else: ?>sorting<?php endif; ?>">已付<a href="javascript:void(0);" data-toggle="tooltip" title="已经付款记录"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th data-id="wait_pay" class="text-center custom <?php if($params['sortkey'] == 'wait_pay'): ?>sorting_<?php echo $params['sort']; else: ?>sorting<?php endif; ?>">待付<a href="javascript:void(0);" data-toggle="tooltip" title="所有采购价值 - 已经付款"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th data-id="revenued" class="text-center custom <?php if($params['sortkey'] == 'revenued'): ?>sorting_<?php echo $params['sort']; else: ?>sorting<?php endif; ?>">已收<a href="javascript:void(0);" data-toggle="tooltip" title="所有入库 + 公司损失金额"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th data-id="collected" class="text-center custom <?php if($params['sortkey'] == 'collected'): ?>sorting_<?php echo $params['sort']; else: ?>sorting<?php endif; ?>">待收<a href="javascript:void(0);" data-toggle="tooltip" title="采购价值 - （正常入库+损耗）"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th data-id="real_pay" class="text-center custom <?php if($params['sortkey'] == 'real_pay'): ?>sorting_<?php echo $params['sort']; else: ?>sorting<?php endif; ?>">实付<a href="javascript:void(0);" data-toggle="tooltip" title="已付款-退款"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <th data-id="total_collected" class="text-center custom <?php if($params['sortkey'] == 'total_collected'): ?>sorting_<?php echo $params['sort']; else: ?>sorting<?php endif; ?>">实收<a href="javascript:void(0);" data-toggle="tooltip" title="已收+等值换"><i class="fa fa-fw fa-question-circle"></i></a></th>
        <!--<th class="text-center">操作</th>-->
    </tr>
    </thead>
    <tbody>
    <?php if(is_array($data) || $data instanceof \think\Collection || $data instanceof \think\Paginator): $i = 0; $__LIST__ = $data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
    <tr>
        <td class="text-center">
           <?php echo $key+1; ?>
        </td>
        <td class="text-center"><?php echo $vo['sku']; ?></td>
        <td class="text-center"><?php echo $vo['goods_name']; ?></td>
        <td class="text-center"><?php echo $vo['ordersn']; ?></td>
        <td class="text-center"><?php echo $vo['partnerName']; ?></td>
        <td class="text-center"><?php echo $vo['storeName']; ?></td>
        <td class="text-center"><?php echo $vo['cguser']; ?></td>
        <td class="text-center"><?php echo $vo['goods_price']; ?></td>
        <td class="text-center"><?php echo $vo['qty']; ?></td>
        <td class="text-center"><?php echo $vo['amount']; ?></td>
        <td class="text-center"><?php echo $vo['paid']; ?></td>
        <td class="text-center"><?php echo $vo['wait_pay']; ?></td>
        <td class="text-center"><?php echo $vo['revenued']; ?></td>
        <td class="text-center"><?php echo $vo['collected']; ?></td>
        <td class="text-center"><?php echo $vo['real_pay']; ?></td>
        <td class="text-center"><?php echo $vo['total_collected']; ?></td>
        <!--<td class="text-center">-->
            <!--查看图表-->
        <!--</td>-->
    </tr>
    <?php endforeach; endif; else: echo "" ;endif; ?>
    </tbody>
</table>


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