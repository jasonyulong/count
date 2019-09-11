<?php if (!defined('THINK_PATH')) exit(); /*a:6:{s:85:"E:\web\count\development\public/../application/count\view\sku\index\showskutotal.html";i:1544600060;s:65:"E:\web\count\development\application\count\view\layout\fluid.html";i:1544600061;s:64:"E:\web\count\development\application\count\view\common\meta.html";i:1544600061;s:74:"E:\web\count\development\application\count\view\sku\index\total_table.html";i:1544600060;s:74:"E:\web\count\development\application\count\view\sku\index\total_chart.html";i:1544600060;s:66:"E:\web\count\development\application\count\view\common\script.html";i:1544600061;}*/ ?>
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
<body class="hold-transition skin-blue sidebar-mini">

<div class="container-fluid">
    <section class="content">
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li><a href="javascript:void(0);"><?php echo $params['sku']; ?>在<?php echo $params['time']; ?>的销量</a></li>
            <li class="pull-right">
                <div class="btn-toolbar" role="toolbar">
                    <div class="btn-group padding-top8 paddint-right5">
                        <a href="<?php echo url('/count/sku/index/showskutotal', array_merge($params, ['model' => 'table'])); ?>" data-toggle="tooltip"
                           class="btn btn-xs btn-default <?php echo $model=='table'?'active' : ''; ?>"
                           title="列表模式"><span class="fa fa-fw fa-th-large"></span></a>
                        <a href="<?php echo url('/count/sku/index/showskutotal', array_merge($params,['model' => 'chart'])); ?>" data-toggle="tooltip"
                           class="btn btn-xs btn-default <?php echo $model=='chart'?'active' : ''; ?>"
                           title="图表模式"><span class="fa fa-fw fa-bar-chart-o"></span></a>
                    </div>
                </div>
            </li>
        </ul>


        <div class="tab-content">
            <?php if($model == 'table'): ?>
            <table class="table table-bordered table-hover dataTable table-striped">
    <thead>
    <tr>
        <th class="text-center">平台</th>
        <th class="text-center">销量</th>
        <th class="text-center">平台</th>
        <th class="text-center">销量</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach($plat_info as $key=>$val): ?>
    <tr>
        <td class="text-center"><?php echo isset($val[15]['platform'])?$val[15]['platform']:''; ?></td>
        <td class="text-center">
            <?php if(isset($val[15]['qty'])): ?>
            <a href="javascript:void(0)" onclick="<?php if($val[15]['qty'] !=0): ?>order_model.singleSkuDetail(this)<?php endif; ?>" class="<?php if($val[15]['qty'] ==0): ?>text-default<?php endif; ?>"
               data-sku="<?php echo $val[15]['sku']; ?>"
               data-time="<?php echo $val[15]['time']; ?>"
               data-url="<?php echo url('/count/sku/index/singlesku',array('store'=>$store,'category'=>$category,'organ'=>$organ,'seller'=>$seller,'country'=>$country,'paytime_start'=>$paytime_start,'paytime_end'=>$paytime_end,'single'=>$single,'platform'=>$val[15]['platform'],'time'=>$val[15]['time'],'sku'=>$val[15]['sku'],'model' => 'table')); ?>">
                <?php echo $val[15]['qty']; ?>
            </a>
            <?php endif; ?>
        </td>


        <td class="text-center"><?php echo isset($val[16]['platform'])?$val[16]['platform']:''; ?></td>
        <td class="text-center">
            <?php if(isset($val[16]['qty'])): ?>
            <a href="javascript:void(0)" onclick="<?php if($val[16]['qty'] !=0): ?>order_model.singleSkuDetail(this)<?php endif; ?>" class="<?php if($val[16]['qty'] ==0): ?>text-default<?php endif; ?>"
               data-sku="<?php echo $val[16]['sku']; ?>"
               data-time="<?php echo $val[16]['time']; ?>"
               data-url="<?php echo url('/count/sku/index/singlesku',array('store'=>$store,'category'=>$category,'organ'=>$organ,'seller'=>$seller,'country'=>$country,'paytime_start'=>$paytime_start,'paytime_end'=>$paytime_end,'single'=>$single,'platform'=>$val[16]['platform'],'time'=>$val[16]['time'],'sku'=>$val[16]['sku'],'model' => 'table')); ?>">
                <?php echo $val[16]['qty']; ?>
            </a>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>


    </tbody>
</table>

            <?php else: ?>
            <script src="/assets/plugins/echarts/echarts.min.js"></script>
<div id="chartmain" style="width:100%; height:580px;"></div>
<script type="application/javascript">
    // 基于准备好的dom，初始化echarts实例
    var myChart = echarts.init(document.getElementById('chartmain'));
    option = {
        color: ['#3398DB'],
        title: {
            text: ''
        },
        tooltip: {
            trigger: 'axis'
        },
        legend: {
            data:['销量']
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
            boundaryGap: true,
            data: <?php echo $plat; ?>
        },
        yAxis: {
            type: 'value'
        },
        series: [
            {
                name:'销量',
                type:'bar',
                barWidth: '10%',
                barCategoryGap:'10%',
                stack: '总量',
                data:<?php echo $sale; ?>
            }

        ]
    };

    // 使用刚指定的配置项和数据显示图表。
    myChart.setOption(option);
</script>

            <?php endif; ?>
        </div>
    </div>
</section>
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
</script>


</body>
</html>