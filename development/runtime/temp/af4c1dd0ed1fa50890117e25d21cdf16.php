<?php if (!defined('THINK_PATH')) exit(); /*a:5:{s:79:"/opt/web/count/development/public/../application/count/view/auth/rule/form.html";i:1548384832;s:69:"/opt/web/count/development/application/count/view/layout/default.html";i:1544600061;s:68:"/opt/web/count/development/application/count/view/layout/dialog.html";i:1547190894;s:66:"/opt/web/count/development/application/count/view/common/meta.html";i:1544600061;s:68:"/opt/web/count/development/application/count/view/common/script.html";i:1547190894;}*/ ?>
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
<body class="hold-transition skin-blue-light sidebar-mini <?php echo $bodyClass; ?>">
<div class="container-fluid">
    
<form id="add-form" class="form-horizontal dialog-form" role="form" data-toggle="validator" method="POST" action="">
    <div class="container-full">
        <section class="content">
            <?php if($menuid > 0): ?>
            <input type="hidden" name="row[pid]" value="<?php echo $menuid; ?>">
            <?php else: ?>
            <div class="form-group">
                <label class="control-label col-xs-12 col-sm-2"><?php echo __('父级'); ?>:</label>
                <div class="col-xs-12 col-sm-8">
                    <?php echo build_select('row[pid]', $ruledata, $row['pid'] ?? 0, ['class'=>'form-control', 'required'=>'']); ?>
                </div>
            </div>
            <?php endif; ?>
            <div class="form-group">
                <label for="name" class="control-label col-xs-12 col-sm-2"><?php echo __('标题'); ?>:</label>
                <div class="col-xs-12 col-sm-8">
                    <input type="text" class="form-control" id="title" name="row[title]"
                           data-placeholder-node="<?php echo __('Node tips'); ?>"
                           data-placeholder-menu="<?php echo __('Menu tips'); ?>"
                           value="<?php echo isset($row['title'])?$row['title']: ''; ?>"
                           data-rule="required"/>
                </div>
            </div>
            <?php if($ismenu == 1): ?>
            <div class="form-group">
                <label for="icon" class="control-label col-xs-12 col-sm-2"><?php echo __('图标'); ?>:</label>
                <div class="col-xs-12 col-sm-8">
                    <div class="input-group input-groupp-md">
                        <input type="text" class="form-control" id="icon" name="row[icon]" value="<?php echo isset($row['icon'])?$row['icon']: 'fa fa-circle-o'; ?>"/>
                        <a href="javascript:;" class="btn-search-icon input-group-addon"><?php echo __('Search icon'); ?></a>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="weigh" class="control-label col-xs-12 col-sm-2"><?php echo __('权重'); ?>:</label>
                <div class="col-xs-12 col-sm-8">
                    <input type="text" class="form-control" id="weigh" name="row[weigh]" value="<?php echo isset($row['weigh'])?$row['weigh']: '0'; ?>" data-rule="required"/>
                </div>
            </div>
            <?php endif; ?>
            <div class="form-group">
                <label for="remark" class="control-label col-xs-12 col-sm-2"><?php echo __('请求地址'); ?>:</label>
                <div class="col-xs-12 col-sm-8">
                    <textarea class="form-control" id="name" name="row[name]"><?php echo isset($row['name'])?$row['name']: ''; ?></textarea>
                </div>
            </div>
            <div class="form-group">
                <label for="remark" class="control-label col-xs-12 col-sm-2"><?php echo __('备注'); ?>:</label>
                <div class="col-xs-12 col-sm-8">
                    <textarea class="form-control" id="remark" name="row[remark]"><?php echo isset($row['remark'])?$row['remark']: ''; ?></textarea>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-xs-12 col-sm-2"><?php echo __('状态'); ?>:</label>
                <div class="col-xs-12 col-sm-8">
                    <?php echo build_radios('row[status]', ['禁用', '启用'], $row['status'] ?? 0); ?>
                </div>
            </div>
            <?php if($ismenu == 1): ?>
            <div class="form-group">
                <label class="control-label col-xs-12 col-sm-2"><?php echo __('是否菜单'); ?>:</label>
                <div class="col-xs-12 col-sm-8">
                    <?php echo build_radios('row[ismenu]', ['1'=>__('是')], $row['ismenu'] ?? 1); ?>
                </div>
            </div>
            <?php else: ?>
            <div class="form-group">
                <label class="control-label col-xs-12 col-sm-2"><?php echo __('是否菜单'); ?>:</label>
                <div class="col-xs-12 col-sm-8">
                    <?php echo build_radios('row[ismenu]', ['0'=>__('否')], $row['ismenu'] ?? 0); ?>
                </div>
            </div>
            <?php endif; ?>
        </section>
    </div>
    <div class=" layer-footer">
        <div class="col-xs-2"></div>
        <div class="col-xs-12 col-sm-8">
            <button type="submit" class="btn btn-success btn-embossed "><?php echo __('确定提交'); ?></button>
            <button type="reset" class="btn btn-default btn-embossed"><?php echo __('重置'); ?></button>
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
<script src="/assets/dist/js/fms.js"></script>
<script src="/assets/dist/js/acl.js"></script>

</body>
</html>