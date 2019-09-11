$(function () {
    var inits = {
        tips: '.tips',
        checkDate: '.checkDate',
        laydate: '.datepicker',
        laymonth: '.monthpicker',
        inputdate: '.input-date',
        postForm: '.postForm',
        postSubmit: '.postSubmit',
        widgetleft: '.widget-left',
        widgetadd: '.widget-add',
        jstree: '#jstree',
        content: '.content',
        // 初始化
        init: function () {
            // 日期选择器
            //Date picker
            $('.datepicker').datepicker({autoclose: true, format: "yyyy-mm-dd", language: 'zh-CN'});
            $('.monthpicker').datepicker({
                autoclose: true, format: "yyyy-mm", language: "zh-CN",
                todayHighlight: true, //是否今日高亮
                autoclose: true, //是否开启自动关闭
                startView: 'months', //开始视图层，为月视图层
                maxViewMode: 'years', //最大视图层，为年视图层
                minViewMode: 'months', //最小视图层，为月视图层
                startDate: moment().subtract(12, 'month').toDate(), //控制可选的最早月份，为前12个月（含当前月）
                endDate: moment().toDate() //控制可选的最晚月份，为当前月
            });
        },
        // 提交表单
        submitForm: function (form) {
            $.ajax({
                url: $(form).attr('action'),
                type: 'post',
                typeData: 'json',
                data: $(form).serialize(),
                beforeSend: function () {
                    layer.load();
                },
                success: function (res) {
                    layer.closeAll('loading');
                    if (res.status == 1) {
                        layer.msg(res.msg, {icon: 1, time: 2000}, function () {
                            parent.layer.closeAll();
                        });
                    } else {
                        //parent.layer.close(parent.layer.getFrameIndex(window.name));
                        var width = '50%';
                        var height = '50%';
                        parent.layer.open({
                            type: 1,
                            maxmin: true,
                            shadeClose: true, //点击遮罩关闭层
                            scrollbar: false,
                            area: [width, height],
                            content: '<div class="layer-content" style="margin: 20px">' + res.msg + '</div>'
                        });
                    }
                },
                error: function () {
                    layer.closeAll('loading');
                    layer.msg("AJAX提交数据失败!", {icon: 2, time: 2000});
                }
            });
        }
    };
    // 初始化
    inits.init();

    // 弹出Title锚点提示
    $(inits.tips).click(function () {
        var tipsData = $(this).attr('title');
        layer.tips(tipsData, this, {tips: [1, '#18a689'], time: 5000});
    });

    // 天 月 切换选择日期
    $(inits.checkDate).click(function () {
        var type = $(this).val() || 'day';
        // 清空选择数据
        $(inits.laydate).val('');
        $(inits.laymonth).val('');
        // 年月日选择器
        if (type == 'day') {
            $(inits.laydate).removeClass('hide').show();
            $(inits.laymonth).addClass('hide');
            $('.shotcut_day_div').show();
        }
        else {
            // 年月选择器
            $(inits.laydate).hide();
            $(inits.laymonth).removeClass('hide').show();
            $('.shotcut_day_div').hide();
        }
    });

    // 提交表单数据
    $(inits.postForm).on('valid.form', function () {
        return inits.submitForm(inits.postForm);
    });

    $('#theadScrollToFixed').scrollToFixed({marginTop: 50});

    // 折叠菜单
    if ($(inits.jstree).length > 0) {
        $(inits.jstree).jstree({
            "core": {"check_callback": true},
            "plugins": ["types", "dnd"],
            "types": {
                "default": {"icon": "fa fa-folder-open"},
                "html": {"icon": "fa fa-file-code-o"},
                "svg": {"icon": "fa fa-file-picture-o"},
                "css": {"icon": "fa fa-file-code-o"},
                "img": {"icon": "fa fa-file-image-o"},
                "js": {"icon": "fa fa-file-text-o"}
            }
        });
        $(inits.jstree).on("changed.jstree", function (e, item) {
            $(this).find(".jstree-anchor").unbind("click").on("click", function () {
                location.href = $(this).attr('href');
            });
        });
    }
    // 向左伸缩菜单
    if ($(inits.widgetleft).length > 0) {
        $(inits.widgetleft).click(function () {
            var d = $('#' + $(this).attr('data-id'));
            var r = d.next();
            d.find('.box-body').hide(200);
            d.find('.box-header').hide(200);
            d.find('.widget-add').removeClass('hide').show(200);
            d.removeClass('col-md-2').addClass('box-down-column');
            r.removeClass('col-md-10').attr('style', 'padding-left:25px;');
        });
    }
    // 弹出伸缩菜单
    if ($(inits.widgetadd).length > 0) {
        $(inits.widgetadd).click(function () {
            var d = $('#' + $(inits.widgetleft).attr('data-id'));
            var r = d.next();

            d.find('.box-body').show(200);
            d.find('.box-header').show(200);
            d.find('.widget-add').hide(200);
            d.removeClass('box-down-column').addClass('col-md-2');
            r.attr('style', '').addClass('col-md-10');
        });
    }

    // 平台选择下拉 和 账号选择下拉 进行 联动
    common_module.init_platform_relate_account();

    // 对图片进行懒加载,图片太多，加载时间太长，卡顿
    $('.lazy-img').lazyload();

    $('input[name=short_cut_day]').click(function () {
        var check_val = $(this).attr('value');
        var d = new Date(Date.now());
        if (check_val == 1) {
            // console.log(common_module.get_day_of_month(d.getMonth() + 1));
            $('input[name=scantime_start]').val(d.getFullYear() + '-' + (d.getMonth() + 1) + '-01');
            $('input[name=scantime_end]').val(d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + common_module.get_day_of_month(d.getMonth() + 1));
        }
        if (check_val == 2) {
            $('input[name=scantime_start]').val(common_module.get_date(-7));
            $('input[name=scantime_end]').val(common_module.get_date(0));
        }
        if (check_val == 3) {
            $('input[name=scantime_start]').val(common_module.get_date(-15));
            $('input[name=scantime_end]').val(common_module.get_date(0));
        }
        if (check_val == 4) {
            $('input[name=scantime_start]').val(d.getFullYear() + '-' + (d.getMonth()) + '-01');
            $('input[name=scantime_end]').val(d.getFullYear() + '-' + (d.getMonth()) + '-' + common_module.get_day_of_month(d.getMonth()));
        }
    });

    $('tr').find('.custom').click(function () {
        var classStr = $(this).attr('class');
        var keyName = $(this).attr('data-id');
        var array = classStr.split(" ");
        var sort = array[2];
        if (sort == 'sorting') {
            sort = 'asc';
        }
        if (sort == 'sorting_asc') {
            sort = 'desc';
        }
        if (sort == 'sorting_desc') {
            sort = 'asc';
        }
        $('input[name=sort]').val(sort);
        $('input[name=sortkey]').val(keyName);
        $('.froms').submit()
    });
});

//公共弹窗
var layers = {
    prompt: function (url, title, width, height) {
        layer.open({
            type: 2,
            title: title,
            maxmin: true,
            shadeClose: true, //点击遮罩关闭层
            scrollbar: false,
            area: [width, height],
            content: url,
        });
    }
}

var sku_module = (function () {
    var change_date_sort = function (thisobj) {
        var sort = thisobj.attr('data-sort');
        var url = thisobj.attr('data-url');
        var date = thisobj.attr('data-date');

        location.href = url + '&sort_more=' + sort + '&sort_date=' + date;
    };

    var show_platform_trendency = function (thisobj) {
        var sku = thisobj.attr('data-sku');
        var title = "SKU:" + sku + "的销量(折线图)";
        var elements = $('.ele-' + sku);

        var date_arr = [];
        var qty_arr = [];

        for (var e of elements) {
            date_arr.push($(e).attr('data-date'));
            qty_arr.push($(e).attr('data-qty'));
        }

        date_arr = date_arr.reverse();
        qty_arr = qty_arr.reverse();

        // console.log(date_arr, qty_arr);

        var x_data = date_arr;
        var legend_data = date_arr;

        var yAxis = [
            {type: 'value', name: '销量'},
        ];

        var series = [
            {type: 'line', name: title, yAxisIndex: 0, data: qty_arr},
        ];

        var option = {
            tooltip: {
                trigger: 'axis',
                axisPointer: {
                    type: 'shadow'
                }
            },
            grid: {
                top: '12%',
                left: '1%',
                right: '5%',
                containLabel: true
            },
            toolbox: {
                feature: {}
            },
            legend: {
                data: legend_data
            },
            xAxis: [
                {
                    name: '日期',
                    type: 'category',
                    axisTick: {
                        alignWithLabel: true
                    },
                    data: x_data
                }
            ],
            yAxis: yAxis,
            series: series
        };

        var myChart = echarts.init(document.getElementById('chartmain'));
        myChart.setOption(option);

        $('#myModalLabel').text(title);
        $('#myModal').modal();
    };

    var show_platform_date_sku_chart = function () {
        var title = "平台销量(柱状图)";

        var platform_arr = [];
        var qty_arr = [];

        for (var e of $('.ele-platform')) {
            platform_arr.push($(e).attr('data-platform'));
        }
        for (var e of $('.ele-total')) {
            qty_arr.push($(e).attr('data-qty'));
        }

        var x_data = platform_arr;
        var legend_data = platform_arr;

        var yAxis = [
            {type: 'value', name: '销量'},
        ];

        var series = [
            {type: 'bar', name: title, yAxisIndex: 0, data: qty_arr},
        ];

        var option = {
            tooltip: {
                trigger: 'axis',
                axisPointer: {
                    type: 'shadow'
                }
            },
            grid: {
                containLabel: true
            },
            toolbox: {
                feature: {}
            },
            legend: {
                data: legend_data
            },
            xAxis: [
                {
                    name: '平台',
                    type: 'category',
                    axisTick: {
                        alignWithLabel: true
                    },
                    data: x_data
                }
            ],
            yAxis: yAxis,
            series: series
        };

        var myChart = echarts.init(document.getElementById('chartmain'));
        myChart.setOption(option);

        $('#myModalLabel').text(title);
        $('#myModal').modal();
    };

    var showSkuPlatformStat = function (thisobj) {
        layer.open({
            type: 2,
            title: '日期:' + thisobj.attr('data-date') + ' SKU:' + thisobj.attr('data-sku'),
            scrollbar: true,
            maxmin: true,
            shadeClose: true, //点击遮罩关闭层
            area: ['1024px', '700px'],
            content: thisobj.attr('data-url'),
        });
    };

    var change_sku_cat = function (thisobj) {
        var sub_cat = thisobj.find('option:selected').attr('data-sub_cat');
        sub_cat = JSON.parse(atob(sub_cat));

        // console.log(sub_cat);
        $('#sub_cat_id').find('option').remove();
        for (var key in sub_cat) {
            var d = sub_cat[key];
            $('#sub_cat_id').append('<option value="' + d.id + '">' + d.name + '</option>');
        }
        $('#sub_cat_id').selectpicker('refresh');
    };

    return {
        change_date_sort: change_date_sort,
        show_platform_trendency: show_platform_trendency,
        showSkuPlatformStat: showSkuPlatformStat,
        show_platform_date_sku_chart: show_platform_date_sku_chart,
        change_sku_cat: change_sku_cat,
    };
})();

//todo 订单模块
var order_model = (function () {
    //todo sku销量报表
    var showSkuDetail = function showSkuDetail(this_obj) {
        var winWidth = window.innerWidth;
        if (winWidth > 1920) {
            var width = '1500px';
            var height = '754px';
        } else {
            var width = '80%';
            var height = '80%';
        }
        var url = $(this_obj).attr('data-url');
        var sku = $(this_obj).attr('data-sku');
        var time = $(this_obj).attr('data-time');
        layers.prompt(url, sku + '在' + time + ' 的销量', width, height);
    };

    //todo sku销量报表   合计
    var showSkuDetailTotal = function showSkuDetailTotal(this_obj, num) {
        var winWidth = window.innerWidth;
        if (winWidth > 1920) {
            var width = '1500px';
            var height = '754px';
        } else {
            var width = '80%';
            var height = '80%';
        }
        var url = $(this_obj).attr('data-url');
        var sku = $(this_obj).attr('data-sku');
        var start_time = $(this_obj).attr('start-time');
        var end_time = $(this_obj).attr('end-time');
        layers.prompt(url, sku + '在' + start_time + '到' + end_time + ' 的销量', width, height);
    }

    //TODO 单个sku销量报表
    var singleSkuDetail = function singleSkuDetail(this_obj) {
        var winWidth = window.innerWidth;
        if (winWidth > 1920) {
            var width = '1500px';
            var height = '754px';
        } else {
            var width = '80%';
            var height = '80%';
        }
        var sku = $(this_obj).attr('data-sku');
        var time = $(this_obj).attr('data-time');
        var url = $(this_obj).attr('data-url');
        layers.prompt(url, sku + ' 在 ' + time + '的销量', width, height);
    };

    //todo 某个sku 某个平台下的所有账号 合计的销量
    var totalSkuDetail = function totalSkuDetail(this_obj) {
        var winWidth = window.innerWidth;
        if (winWidth > 1920) {
            var width = '1500px';
            var height = '754px';
        } else {
            var width = '80%';
            var height = '80%';
        }
        var sku = $(this_obj).attr('data-sku');
        var url = $(this_obj).attr('data-url');
        layers.prompt(url, sku + ' 的销量', width, height);
    }

    /*产品包 start */
    //todo 添加产品包
    var addPackage = function addPackage(this_obj) {
        var winWidth = window.innerWidth;
        if (winWidth > 960) {
            var width = '500px';
            var height = '500px';
        } else {
            var width = '80%';
            var height = '50%';
        }
        var url = $(this_obj).attr('data-url');
        layer.open({
            type: 2,
            title: '编辑产品包',
            maxmin: true,
            shadeClose: true, //点击遮罩关闭层
            scrollbar: false,
            area: [width, height],
            content: url,
        });
    };

    //todo 子系列
    var viewDetail = function viewDetail(this_obj, sku) {
        var winWidth = window.innerWidth;
        if (winWidth > 1920) {
            var width = '1500px';
            var height = '848px';
        } else {
            var width = '90%';
            var height = '90%';
        }
        var url = $(this_obj).attr('data-url');
        layers.prompt(url, sku + ' 的销量', width, height);
    };

    //todo 父sku销量
    var btobnumberDetail = function btobnumberDetail(this_obj, sku, date) {
        var winWidth = window.innerWidth;
        if (winWidth > 1920) {
            var width = '1500px';
            var height = '754px';
        } else {
            var width = '80%';
            var height = '80%';
        }
        var url = $(this_obj).attr('data-url');
        layers.prompt(url, sku + ' 在 ' + date + '的销量', width, height);
    };

    //单个sku详情
    var singleDetail = function singleDetail(this_obj, sku) {
        var winWidth = window.innerWidth;
        if (winWidth > 1920) {
            var width = '1500px';
            var height = '754px';
        } else {
            var width = '80%';
            var height = '80%';
        }
        var url = $(this_obj).attr('data-url');
        layers.prompt(url, sku + ' 在 各个平台的销量', width, height);
    };

    //ajax删除数据
    var deleteData = function deleteData(id, that) {
        var url = $(that).attr('data-url');
        if (id == '' || id == null) {
            layer.msg('未设置参数');
            return false;
        }
        if (url == '' || url == null) {
            layer.msg('属性:data-url中未能获取url路径');
            return false;
        }
        layer.confirm('确认要删除数据吗?', {
            btn: ['确定', '取消']
        }, function () {
            $.ajax({
                url: url,
                type: 'post',
                typeData: 'json',
                data: {id: id},
                success: function (res) {
                    if (res.status == 1) {
                        layer.msg(res.msg, {icon: 1, time: 2000}, function () {
                            parent.location.reload();
                        });
                    } else {
                        layer.msg(res.msg, {icon: 2, time: 2000});
                    }
                },
                error: function () {
                    layer.msg("AJAX提交数据失败!", {icon: 2, time: 2000});
                }
            });
        });
    }

    //关闭所有弹窗
    var closeAll = function closeAll() {
        parent.layer.closeAll();
    }

    /*产品包 end */

    //查看sku大图
    var skuimage = function skuimage(this_obj) {
        var bigsrc = $(this_obj).attr('bigsrc');
        if (typeof bigsrc == 'undefined') {
            return false;
        }
        layer.open({
            type: 1,
            title: false,
            closeBtn: 0,
            area: ['500px', '500px'],
            skin: 'layui-layer-nobg', //没有背景色
            anim: 1, //0-6的动画形式，-1不开启
            shadeClose: true,
            content: '<img src="' + bigsrc + '" width="500" height="500">'
        });

    };

    //商品分类
    var selectCategory = function selectCategory(this_obj, child) {
        var parent = $(this_obj).val();
        var url = $(this_obj).attr('data-url');
        var child = $('#child');

        $.post(
            url,
            {parentId: parent},
            function (msg) {
                if (msg.status == 2) {
                    var _html = '';
                    child.html("");
                    _html += '<select class="selectpicker show-tick" name="single" id="single" data-actions-box="true" data-live-search="true">';
                    _html += '<option value="">产品分类</option>';
                    $.each(msg.data, function (key, item) {
                        _html += '<option value="' + item.id + '">' + item.name + '</option>';
                    });
                    _html += '</select>';
                    //console.log(_html);return false;
                    child.append(_html);
                    $("#single").selectpicker('refresh');
                }
            }, 'json'
        );

    };

    //产品包   sku的销量
    var singlePlatformDetail = function singlePlatformDetail(this_obj, sku, platform) {
        var winWidth = window.innerWidth;
        if (winWidth > 1920) {
            var width = '1500px';
            var height = '754px';
        } else {
            var width = '80%';
            var height = '80%';
        }
        var url = $(this_obj).attr('data-url');
        layers.prompt(url, sku + ' 在 ' + platform + '的销量', width, height);
    };

    //产品包 合计  某个时间到某个时间的sku的销量
    var totalDetail = function totalDetail(this_obj, sku) {
        var winWidth = window.innerWidth;
        if (winWidth > 1920) {
            var width = '1500px';
            var height = '754px';
        } else {
            var width = '80%';
            var height = '80%';
        }
        var url = $(this_obj).attr('data-url');
        layers.prompt(url, sku + ' 的销量合计', width, height);
    };

    //子集sku销量
    var skuDetail = function skuDetail(this_obj, sku) {
        var winWidth = window.innerWidth;
        if (winWidth > 1920) {
            var width = '1500px';
            var height = '848px';
        } else {
            var width = '90%';
            var height = '90%';
        }
        var url = $(this_obj).attr('data-url');
        layers.prompt(url, sku + ' 的销量合计', width, height);
    };

    //子集 sku在各个平台的销量
    var totalSumSale = function totalSumSale(this_obj, sku) {
        var winWidth = window.innerWidth;
        if (winWidth > 1920) {
            var width = '1500px';
            var height = '848px';
        } else {
            var width = '90%';
            var height = '90%';
        }
        var url = $(this_obj).attr('data-url');
        layers.prompt(url, sku + ' 在各个平台的销量合计', width, height);
    };


    /**
     * 导出sku销量
     *@author jason
     * @date 2018/9/21
     */
    var export_sku = function export_sku() {
        var current_url = window.location.href;
        var arg = 'is_export=1';
        current_url += (current_url.indexOf('?') == -1 ? '?' : '&') + arg;
        common_module.open_in_new_tab(current_url);
    };


    var change_org = function (thisobj) {
        var org_id = $(thisobj).val();
        $.ajax({
            url: '/count/Ajax/changeOrg',
            type: 'POST',
            data: {org_id: org_id},
            dataType: 'JSON',
            success: function (ret) {
                if (ret.code == 0) {
                    $('#seller').find('option').remove();
                    for (var d of ret.data) {
                        $('#seller').append('<option value="' + d + '">' + d + '</option>');
                    }
                    $('#seller').selectpicker('refresh');

                }
            }
        })
    };

    var checked_date = function (that) {
        var type = that.value;
        $.ajax({
            url: '/count/Ajax/getDate',
            type: 'POST',
            data: {type: type},
            dataType: 'JSON',
            success: function (ret) {
                if (ret) {
                    $(".start").val(ret.start);
                    $(".end").val(ret.end);
                }
            }
        })
    };


    var set_org_target = function (thisobj) {
        layer.open({
            type: 2,
            title: '目标设置',
            maxmin: true,
            shadeClose: true, //点击遮罩关闭层
            scrollbar: false,
            area: ['1280px', '650px'],
            content: thisobj.attr('data-url'),
        });
    };

    var update_org_target = function (thisobj, is_submit) {
        // console.log(is_submit)
        if (is_submit == 1) {
            thisobj.parent().find('span').show();
            thisobj.parent().find('input').hide();

            var url = thisobj.parent().attr('data-url');
            var org_id = thisobj.parent().attr('data-org_id') || 0;
            var ebay_account = thisobj.parent().attr('data-ebay_account') || '';
            var target_value = thisobj.val();
            var year = $('#year').val();
            var month = thisobj.parent().attr('data-month');
            var seller = thisobj.parent().attr('data-seller') || '';

            if ($.trim(target_value) == '') return false;

            thisobj.parent().find('span').text(target_value);
            $.ajax({
                url: url,
                data: {org_id: org_id, ebay_account: ebay_account, seller: seller, target_value: target_value, year: year, month: month},
                type: 'POST',
                dataType: 'JSON',
                success: function (ret) {
                    if (ret.code == 0) {
                        //location.reload();
                        //thisobj.parent().find('span').text(target_value);
                    }
                    else {
                        layer.alert(ret.msg);
                    }
                }
            });
        }
        else {
            thisobj.find('span').hide();
            thisobj.find('input').show().focus();
        }
    };

    var change_target_year = function (thisobj) {
        var year = thisobj.find('option:selected').val();
        layer.load();
        var protocol = window.location.protocol;
        var host = window.location.host;
        var path = window.location.pathname;
        var search = window.location.search;
        search = search.replace(/^\?/, '');
        new RegExp('/^/')
        let params = search.split('&');
        let newparams = [];
        for (d of params) 
        {
            let tmp_arr = d.split('=');
            if (tmp_arr[0] != 'year') newparams.push(d);
        }
        newparams.push('year=' + year);
        let url = protocol + '//' + host + path + '?' + newparams.join('&');
        window.location.href = url;
    };


    var showCreateExportTask = function (thisobj) {
        var url = $(thisobj).attr('data-url');
        layer.open({
            type: 2,
            title: '创建任务',
            maxmin: true,
            shadeClose: true, //点击遮罩关闭层
            scrollbar: false,
            area: ['750px', '780px'],
            content: url,
        });
    };

    var change_task_platform = function (thisobj) {
        var platform_list = thisobj.val();

        if (platform_list.length == 0) {
            $('#account').find('option').remove();
            $('#account').selectpicker('refresh');
        }
        else {
            $.ajax({
                url: '/count/Ajax/getPlatformUser',
                type: 'POST',
                data: {platform: platform_list},
                dataType: 'JSON',
                success: function (ret) {
                    if (ret.code == 0) {
                        $('#account').find('option').remove();
                        for (d of ret.data) {
                            $('#account').append('<option value="' + d + '">' + d + '</option>');
                        }

                        $('#account').selectpicker('refresh');
                    }
                }
            });
        }

    };

    var change_task_carrier_company = function (thisobj, show_id) {
        show_id = show_id || 0;
        var options = thisobj.find('option:selected');
        var comp_ids = [];
        for (var o of options) {
            comp_ids.push($(o).attr('data-id'));
        }

        if (comp_ids.length == 0) {
            $('#carrier').find('option').remove();
            $('#carrier').selectpicker('refresh');
        }
        else {
            $.ajax({
                url: '/count/Ajax/getCarrierList',
                type: 'POST',
                data: {company_ids: comp_ids},
                dataType: 'JSON',
                success: function (ret) {
                    if (ret.code == 0) {
                        $('#carrier').find('option').remove();
                        for (var d of ret.data) {
                            if (show_id) $('#carrier').append('<option value="' + d.id + '">' + d.name + '</option>');
                            else $('#carrier').append('<option value="' + d.name + '">' + d.name + '</option>');
                        }

                        $('#carrier').selectpicker('refresh');
                    }
                }
            });
        }
    };

    var cancel_task = function (thisobj) {
        layer.confirm('确认取消任务吗？', function () {
            layer.load();
            $.ajax({
                url: thisobj.attr('data-url'),
                type: 'POST',
                data: {id: thisobj.attr('data-id')},
                dataType: 'JSON',
                success: function (ret) {
                    layer.alert(ret.msg, function () {
                        if (ret.code == 0) {
                            layer.closeAll();
                            location.reload();
                        }
                    });
                }
            });
        });
    };
    var del_task = function (thisobj) {
        layer.confirm('确认删除任务吗？', function () {
            layer.load();
            $.ajax({
                url: thisobj.attr('data-url'),
                type: 'POST',
                data: {id: thisobj.attr('data-id')},
                dataType: 'JSON',
                success: function (ret) {
                    layer.alert(ret.msg, function () {
                        if (ret.code == 0) {
                            layer.closeAll();
                            location.reload();
                        }
                    });
                }
            });
        });
    };

    var view_task = function (thisobj) {
        var url = $(thisobj).attr('data-url');
        layer.open({
            type: 2,
            title: '查看任务',
            maxmin: true,
            shadeClose: true, //点击遮罩关闭层
            scrollbar: false,
            area: ['600px', '700px'],
            content: url,
        });
    };

    var organ_loop_trendency = function (thisobj) {
        var url = $(thisobj).attr('data-url');
        var index = layer.open({
            type: 2,
            title: '环比增长走势',
            maxmin: true,
            area: ['1000px', '700px'],
            content: url,
        });
        //layer.full(index);
        // 不知道是插件的问题，还是我们系统的问题，全屏时，总有点问题，加这个就没有问题了
        //$('.layui-layer-content').find('iframe').height($('.layui-layer-shade').height());
    };

    var change_task_organ = function (thisobj) {
        var options = thisobj.find('option:selected');
        $('#seller').find('option').remove();

        var seller_arr = [];
        for (var o of options) {
            var tmp = JSON.parse($(o).attr('data-seller'));
            seller_arr = seller_arr.concat(tmp);
        }
        // 数组去重，直接变成集合，这种方法真6666
        var set = new Set(seller_arr);
        for (var d of set) {
            $('#seller').append('<option value="' + d + '">' + d + '</option>');
        }
        $('#seller').selectpicker('refresh');
    };

    var show_org_trendency = function (thisobj) {
        var org_id = thisobj.attr('data-org_id');
        var org_name = thisobj.attr('data-org_name');
        var elements = $('.ele-org_' + org_id);

        var percent_arr = [];
        var sales_arr = [];
        var date_arr = [];
        for (var e of elements) {
            date_arr.push($(e).attr('data-date'));
            sales_arr.push($(e).attr('data-sales'));
            percent_arr.push($(e).attr('data-percent'));
        }

        var x_data = date_arr;
        var legend_data = date_arr;
        // console.log(legend_data)
        var yAxis = [
            {type: 'value', name: '平均销售额'},
            {
                type: 'value', name: '环比增长走势(%)', offset: 0, axisLabel: {
                    formatter: '{value} %'
                }
            },
        ];

        var series = [
            {type: 'bar', name: '平均销售额', yAxisIndex: 0, data: sales_arr},
            {type: 'line', name: '环比增长走势(%)', yAxisIndex: 1, data: percent_arr},
        ];

        var colors = ['grey', 'FireBrick', 'DarkOrange', 'Chartreuse', 'Crimson', 'Cyan', 'red', 'blue', 'yellow', 'green', 'BlueViolet '];

        var option = {
            color: colors,
            tooltip: {
                trigger: 'axis',
                axisPointer: {
                    type: 'shadow'
                }
            },
            grid: {
                top: '12%',
                left: '1%',
                right: '10%',
                containLabel: true
            },
            toolbox: {
                feature: {}
            },
            legend: {
                data: legend_data
            },
            xAxis: [
                {
                    type: 'category',
                    axisTick: {
                        alignWithLabel: true
                    },
                    data: x_data
                }
            ],
            yAxis: yAxis,
            series: series
        };

        var myChart = echarts.init(document.getElementById('chartmain'));
        myChart.setOption(option);

        $('#myModalLabel').text(org_name);
        $('#myModal').modal();
    };

    var add_finance_check = function(thisobj)
    {
        var url = $(thisobj).attr('data-url');
        layer.open({
            type: 2,
            title: thisobj.text(),
            maxmin: true,
            shadeClose: true, //点击遮罩关闭层
            scrollbar: false,
            area: ['750px', '780px'],
            content: url,
        });
    };

    var view_check = function(thisobj)
    {
        var url = $(thisobj).attr('data-url');
        layer.open({
            type: 2,
            title: '查看(为空即为全选)',
            maxmin: true,
            shadeClose: true, //点击遮罩关闭层
            scrollbar: false,
            area: ['800px', '700px'],
            content: url,
        });
    };

    var import_expend = function(thisobj)
    {
        var url = $(thisobj).attr('data-url');
        layer.open({
            type: 2,
            title: '导入',
            maxmin: true,
            shadeClose: true, //点击遮罩关闭层
            scrollbar: false,
            area: ['600px', '700px'],
            content: url,
        });
    };

    var change_download_tpl = function(thisobj)
    {
        var platform = thisobj.val();
        var url = $('#download_url').attr('data-url');
        url += '&platform=' + platform;
        $('#download_url').attr('href', url);
        $('#download_url').text(platform + ' excel 模板');
    };

    var change_expend_type = function(thisobj)
    {
        var platform = thisobj.val();
        // console.log(platform, global_expend_type, );
        var tmp = global_expend_type[platform];
        var type_list = [];
        for (var k in tmp)
        {
            type_list.push(tmp[k]);
        }
        var type_str = type_list.join(', ');
        $('.type_list').text(type_str);
    };

    var add_expend_type = function(thisobj)
    {
        var platform = $('select[name="platform"]').val();
        layer.prompt({
            'title': '请输入' + platform + '费用项名称',
        }, function(type_name){
            $.ajax({
                url: '/count/expend/Expend/addExpendType',
                type: 'POST',
                data: {type_name: type_name, platform: platform},
                dataType: 'JSON',
                success: function (ret) {
                    layer.alert(ret.msg, function(){location.reload();});
                }
            });
        });
        

    };

    return {
        showSkuDetail: showSkuDetail,
        singleSkuDetail: singleSkuDetail,
        addPackage: addPackage,
        viewDetail: viewDetail,
        btobnumberDetail: btobnumberDetail,
        singleDetail: singleDetail,
        closeAll: closeAll,
        deleteData: deleteData,
        showSkuDetailTotal: showSkuDetailTotal,
        totalSkuDetail: totalSkuDetail,
        skuimage: skuimage,
        selectCategory: selectCategory,
        singlePlatformDetail: singlePlatformDetail,
        totalDetail: totalDetail,
        skuDetail: skuDetail,
        totalSumSale: totalSumSale,
        export_sku: export_sku,
        change_org: change_org,
        checked_date: checked_date,
        set_org_target: set_org_target,
        update_org_target: update_org_target,
        change_target_year: change_target_year,
        showCreateExportTask: showCreateExportTask,
        change_task_platform: change_task_platform,
        change_task_carrier_company: change_task_carrier_company,
        cancel_task: cancel_task,
        del_task: del_task,
        view_task: view_task,
        view_check: view_check,
        organ_loop_trendency: organ_loop_trendency,
        change_task_organ: change_task_organ,
        show_org_trendency: show_org_trendency,
        add_finance_check: add_finance_check,
        import_expend: import_expend,
        change_download_tpl: change_download_tpl,
        change_expend_type: change_expend_type,
        add_expend_type: add_expend_type,
    };
})();

//todo 采购付款报表模块
var purchase_model = (function () {
    var get_providers = function get_providers(that) {
        var keywords = $.trim($(that).val());
        keywords = keywords.toUpperCase();
        if (keywords) {
            $.get(
                '/count/Purchase/index/getPartnerByKeyWord',
                {keywords: keywords},
                function (re) {
                    var options = "";
                    $(re).each(function (k, v) {
                        options += "<li onclick='purchase_model.selectThis(this)'>" + v.id + "  " + v.company_name + " </li>";
                    });
                    $("#searchProviderList").empty().html(options).css('display', 'block');
                },
                'json'
            );
        }
    };
    var selectThis = function selectThis(that) {
        var tex = $('#partner_id');
        var fac = $('#factory');
        var inHtml = $(that).html();
        var splitResult = $.trim(inHtml).split('  ');
        fac.val(splitResult[0]);
        tex.val(splitResult[1]);
        $("#searchProviderList").css('display', 'none');
    };
    return {
        get_providers: get_providers,
        selectThis: selectThis,
    };
})();

