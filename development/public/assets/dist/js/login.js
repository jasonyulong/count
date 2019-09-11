$(function () {
    var inits = {
        countdown: 60,
        sendsms: ".sendsms",
        submit: "#submit",
        loginForm: "#loginForm",
        locksform: "#locks-form",
        usernamelogin: ".usernamelogin",
        smslogin: ".smslogin",
        loginboxmsg: ".login-box-msg",
        formcaptcha: "#pd-form-captcha",
        imgcaptcha: "#img-captcha",
        alert: ".alert",
        admin: false
    };
    // 开始登录
    var startLogin = function () {
        $(inits.submit).attr('disable', true).html('Loding...');
        $(inits.submit).removeClass('bg-purple').addClass('bg-default');
    }
    //停止登录
    var endLogin = function () {
        $(inits.submit).attr('disable', false).html('Sign In');
        $(inits.submit).removeClass('bg-default').addClass('bg-purple');
    }
    // 登录
    $(inits.loginForm).on('valid.form', function () {
        var url = $(this).attr('action');
        var index = layer.load();
        var _this = $(this);
        startLogin();
        $.ajax({
            url: url,
            type: "post",
            dataType: "json",
            data: $(this).serialize(),
            success: function (data) {
                layer.close(index);
                endLogin();
                if (data.code != 1) {
                    return layer.alert(data.msg);
                }
                if (data.data.type == 'check') {
                    return smsLogin(data, _this);
                }
                if (data.url) {
                    location.href = data.url;
                    return;
                }
                layer.alert(data.msg, {closeBtn: 0}, function (index) {
                    parent.layer.close(parent.layer.getFrameIndex(window.name));
                    parent.layer.close(index);
                    parent.window.location.reload();
                    window.location.reload();
                });
            },
            error: function (error) {
                layer.close(index);
                endLogin();
                return layer.alert("请求或返回数据异常");
            }
        });
    });
    // 锁屏登录
    $(inits.locksform).on('valid.form', function () {
        var url = $(this).attr('action');
        var index = layer.load();
        $.ajax({
            url: url,
            type: "post",
            dataType: "json",
            data: $(this).serialize(),
            success: function (data) {
                layer.close(index);
                if (data.code != 1) {
                    layer.alert(data.msg, {closeBtn: 0}, function (index) {
                        if (data.url) {
                            location.href = data.url;
                        } else {
                            parent.layer.close(index);
                        }
                    });
                }
                if (data.url) {
                    location.href = data.url;
                    return;
                }
                layer.alert(data.msg, {closeBtn: 0}, function (index) {
                    parent.layer.close(index);
                    parent.window.location.reload();
                    window.location.reload();
                });
            },
            error: function (error) {
                layer.close(index);
                return layer.alert("请求或返回数据异常");
            }
        });
    });
})

