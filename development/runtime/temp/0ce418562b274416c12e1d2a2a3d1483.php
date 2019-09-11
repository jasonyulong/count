<?php if (!defined('THINK_PATH')) exit(); /*a:5:{s:84:"/opt/web/count/development/public/../application/count/view/order/export/detail.html";i:1547190894;s:69:"/opt/web/count/development/application/count/view/layout/default.html";i:1544600061;s:68:"/opt/web/count/development/application/count/view/layout/fluid2.html";i:1544694231;s:66:"/opt/web/count/development/application/count/view/common/meta.html";i:1544600061;s:68:"/opt/web/count/development/application/count/view/common/script.html";i:1548747211;}*/ ?>
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

<style>
.text-right {
    text-align: right;
}
</style>

<body class="hold-transition skin-blue sidebar-mini">

<div class="container-fluid">
    
<form class="form-horizontal" id="default_form">
    <div class="form-group">
        <label class="col-sm-3 col-xs-3 control-label text-right">任务名称：</label>
        <div class="col-sm-9 col-xs-9">
            <?php echo $task_data['task_name']; ?>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 col-xs-3 control-label text-right"><?php echo $order_field_list[$task_params['time_type']]; ?>：</label>
        <div class="col-sm-9 col-xs-9">
            <?php echo isset($task_params['start_time'])?$task_params['start_time']: ''; ?> - <?php echo isset($task_params['end_time'])?$task_params['end_time']: ''; ?> 
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 col-xs-3 control-label text-right">指定订单类型：</label>
        <div class="col-sm-9 col-xs-9">
            <?php foreach($task_params['order_type'] as $k => $v): ?>
                [<?php echo $order_type_list[$v]; ?>]
            <?php endforeach; ?>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 col-xs-3 control-label text-right">指定订单状态：</label>
        <div class="col-sm-9 col-xs-9">
            <?php foreach($task_params['order_status'] as $k => $v): ?>
                [<?php echo $order_status_list[$v]; ?>]
            <?php endforeach; ?>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 col-xs-3 control-label text-right">指定平台：</label>
        <div class="col-sm-4 col-xs-4">
            <?php foreach($task_params['platform'] as $k => $v): ?>
                [<?php echo $v; ?>]
            <?php endforeach; ?>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 col-xs-3 control-label text-right">指定平台帐号：</label>
        <div class="col-sm-4 col-xs-4">
            <?php foreach($task_params['account'] as $k => $v): ?>
                [<?php echo $v; ?>]
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="form-group">
        <label class="col-sm-3 col-xs-3 control-label text-right">指定组织结构：</label>
        <div class="col-sm-4 col-xs-4">
            <?php foreach($task_params['org_id'] as $k => $v): ?>
            [<?php echo $org_list[$v]['name']; ?>]
            <?php endforeach; ?>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 col-xs-3 control-label text-right">指定销售人员：</label>
        <div class="col-sm-4 col-xs-4">
            <?php foreach($task_params['seller'] as $k => $v): ?>
            [<?php echo $v; ?>]
            <?php endforeach; ?>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 col-xs-3 control-label text-right">指定物流公司：</label>
        <div class="col-sm-4 col-xs-4">
            <?php foreach($task_params['carrier_company'] as $k => $v): ?>
            [<?php echo $v; ?>]
            <?php endforeach; ?>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 col-xs-3 control-label text-right">指定物流渠道：</label>
        <div class="col-sm-4 col-xs-4">
            <?php foreach($task_params['carrier'] as $k => $v): ?>
            [<?php echo $v; ?>]
            <?php endforeach; ?>
        </div>
    </div>

    <div class="form-group">
            <label class="col-sm-3 col-xs-3 control-label text-right">指定字段：</label>
            <div class="col-sm-9 col-xs-9">
                <?php foreach($task_params['order_fields'] as $k => $v): ?>
                [<?php echo $order_field_list[$v]; ?>]
                <?php endforeach; ?>
            </div>
        </div>

    <div class="form-group">
        <label class="col-sm-3 col-xs-3 control-label text-right">优先级：</label>
        <div class="col-sm-9 col-xs-9">
            <?php echo $task_params['priority']; ?>
        </div>
    </div>
   
</form>


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
    <?php if(isset($params['type']) && (in_array($params['type'], ['organ', 'store']))): ?>
        var order = [];
    <?php endif; ?>
    common_module.init_data_table(page_str, order);

    // 针对414 错误
    common_module.init_submit_form('manual_submit_form');

    <?php if(isset($params['type']) && !in_array($params['type'], ['platform'])): ?>
    $('#scroll_table').floatThead({
        autoReflow: true,
        zIndex: 0
    });
    <?php endif; ?>
</script>


</body>
</html>