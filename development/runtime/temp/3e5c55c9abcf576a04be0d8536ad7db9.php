<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:76:"/opt/web/count/development/public/../application/index/view/login/index.html";i:1548384832;s:66:"/opt/web/count/development/application/index/view/common/meta.html";i:1544600057;s:68:"/opt/web/count/development/application/index/view/common/script.html";i:1547190895;}*/ ?>
<!DOCTYPE html>
<html lang="<?php echo $config['language']; ?>">
<head>
    <!-- 加载样式及META信息 -->
    <meta charset="utf-8">
<title><?php echo (isset($title) && ($title !== '')?$title:''); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<meta name="renderer" content="webkit">

<?php if(isset($keywords)): ?>
<meta name="keywords" content="<?php echo $keywords; ?>">
<?php endif; if(isset($description)): ?>
<meta name="description" content="<?php echo $description; ?>">
<?php endif; ?>
<meta name="author" content="ERPv5.0">
<link rel="shortcut icon" href="/assets/img/favicon.ico" />
<!-- Loading Bootstrap -->
<!-- Bootstrap 3.3.7 -->
<link rel="stylesheet" href="/assets/components/bootstrap/dist/css/bootstrap.min.css">
<!-- Font Awesome -->
<link rel="stylesheet" href="/assets/components/font-awesome/css/font-awesome.min.css">
<!-- Ionicons -->
<link rel="stylesheet" href="/assets/components/Ionicons/css/ionicons.min.css">
<!-- Theme style -->
<link rel="stylesheet" href="/assets/dist/css/AdminLTE.min.css">
<!-- iCheck -->
<link rel="stylesheet" href="/assets/plugins/iCheck/square/blue.css">
<!-- HTML5 shim, for IE6-8 support of HTML5 elements. All other JS at the end of file. -->
<!--[if lt IE 9]>
  <script src="/assets/js/html5shiv.js"></script>
  <script src="/assets/js/respond.min.js"></script>
<![endif]-->
<!-- Font -->
<link rel="stylesheet" href="/assets/dist/css/fontcss.css">
<link rel="stylesheet" href="/assets/dist/css/style.css">
<script type="text/javascript">
    var require = {
        config: <?php echo json_encode($config); ?>
    };
</script>

</head>
<body class="hold-transition login-page">
<div class="login-box">
    <div class="login-logo">
        <a href="javascript:void(0);"><b>COUNT </b>
        </a>
    </div>
    <!-- /.login-logo -->
    <div class="login-box-body">
        <p class="login-box-msg">Sign in to start your session</p>
        <form id="loginForm" action="<?php echo url('/count/login/index'); ?>" login-action="<?php echo url('/count/login', ['url' => $url]); ?>" method="post">
            <?php echo token(); ?>
            <div class="usernamelogin">
                <div class="form-group has-feedback">
                    <input type="text" class="form-control" id="pd-form-username" placeholder="<?php echo __('Username'); ?>" name="username" autocomplete="off" value=""
                           data-rule="required;"/>
                    <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                </div>
                <div class="form-group has-feedback">
                    <input type="password" class="form-control" id="pd-form-password" placeholder="<?php echo __('Password'); ?>" name="password" autocomplete="off" value=""
                           data-rule="<?php echo __('Password'); ?>:required;password"/>
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                </div>
            </div>
            <div class="smslogin form-group" style="display: none;">
                <div class="input-group">
                    <input type="text" class="form-control" id="pd-form-sms" placeholder="<?php echo __('Sms Captcha'); ?>" name="smscode" autocomplete="off"/>
                    <span class="input-group-addon" style="padding:5px 12px;">
                        <input type="button" sendurl="<?php echo url('/count/login/sendsms'); ?>" class="sendsms btn btn-xs" value="重新获取">
                    </span>
                </div>

            </div>
            <?php if($config['erp']['login_captcha']): ?>
            <div class="form-group has-feedback clearfix" style="margin-bottom:0px;">
                <input type="text" id="pd-form-captcha" name="captcha" class="form-control" placeholder="Captcha" data-rule="required;" style="width:50%;float:left">
                <span class="input-group-addon" style="padding:0;border:none;cursor:pointer;float:left;">
                    <img src="/captcha" id="img-captcha" onclick="this.src = '/captcha?r=' + Math.random();"/>
                </span>
                <span class="msg-box n-right" style="float: right" for="pd-form-captcha"></span>
            </div>
            <?php endif; ?>
            <div class="social-auth-links text-center">
                <p>- TO -</p>
                <button type="submit" id="submit" class="btn bg-purple btn-block btn-flat">Sign In</button>
            </div>
            <div class="alert alert-warning alert-dismissible margin-top10">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <i class="fa fa-fw fa-thumbs-o-down"></i><span> Please enter your name and password</span></div>

        </form>
    </div>
    <div class="lockscreen-footer text-center">
        Copyright &copy; 2018-2025 <b>Jeoshi</b><br>
        All rights reserved
    </div>
    <!-- /.login-box-body -->
</div>
<!-- 加载JS脚本 -->
<!-- jQuery 3 -->
<script src="/assets/components/jquery/dist/jquery.min.js"></script>
<!-- Bootstrap 3.3.7 -->
<script src="/assets/components/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- daterangepicker -->
<script src="/assets/components/moment/min/moment.min.js"></script>
<script src="/assets/components/bootstrap-daterangepicker/daterangepicker.js"></script>
<!-- ValidateForm -->
<script src="/assets/components/nice-validator/dist/jquery.validator.min.js?local=zh-CN"></script>
<!-- datepicker -->
<script src="/assets/components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
<!-- Slimscroll -->
<script src="/assets/components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
<!-- iCheck 1.0.1 -->
<script src="/assets/plugins/iCheck/icheck.min.js"></script>
<script src="/assets/plugins/lazyload.js"></script>
<!-- AdminLTE App -->
<script src="/assets/dist/js/adminlte.min.js"></script>
<script src="/assets/plugins/layer/layer.js"></script>
<script src="/assets/dist/js/default.js"></script>
<script>
    // 图片使用懒加载
    $("img.lazy").lazyload();
</script>
<script src="/assets/dist/js/login.js"></script>
</body>
</html>
