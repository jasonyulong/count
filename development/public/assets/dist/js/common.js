/**
 * 公共函数库
 * -----------------------------
 * 这里面写的函数，都应该是可以给所有模块使用的 js
 */

var common_module = (function () {

    // 平台选择下拉 和 账号选择下拉 进行 联动
    var init_platform_relate_account = function () {
        $('.platform-choice').change(function () {
            var platform = $(this).val();
            $.ajax({
                url: '/count/Ajax/getPlatformUser',
                data: {platform: platform},
                type: 'POST',
                dataType: 'JSON',
                success: function (ret) {
                    if (ret.code == 0) {
                        $('.account-choice').find('option').remove();
                        for (var key in ret.data) {
                            $('#account').append('<option value="' + ret.data[key] + '">' + ret.data[key] + '</option>')
                        }
                        $('.account-choice').selectpicker('refresh');
                    }
                }
            });
        });
    };


    /**
     * 显示 图表
     * chart_type: bar 柱状图，line，现状图
     * x_data: x 轴数据
     */
    var show_colomn_chart = function (chart_type, x_data, y_data, legend_data, yAxis, element, params_type, controller_name) {
        if (typeof echarts == 'undefined') return false;

        element = element || 'chartmain';
        params_type = params_type || '';
        controller_name = controller_name || '';

        var myChart = echarts.init(document.getElementById(element));

        // console.log(params_type, controller_name);
        var series = [];
        for (var key in y_data) {
            var _name = legend_data[key];
            var _type = chart_type;
            var _yAxisIndex = 0; // 默认使用第0个y轴

            // console.log(_name);
            // 需求：销售额，用柱状图
            if (controller_name == 'Order.sale' && params_type == 'date' && _name == '销售额') _type = 'bar';
            if ((new RegExp("单量")).test(_name)) _yAxisIndex = 0;
            if ((new RegExp("销售额")).test(_name)) _yAxisIndex = 1;

            var tmp = {type: _type, data: y_data[key], name: _name, yAxisIndex: _yAxisIndex};
            series.push(tmp);
        }

        // 基于准备好的dom，初始化echarts实例
        var colors = ['grey', 'FireBrick', 'DarkOrange', 'Chartreuse', 'Crimson', 'Cyan', 'red', 'blue', 'yellow', 'green', 'BlueViolet '];

        option = {
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
                feature: {
                    dataView: {show: true, readOnly: false},
                    restore: {show: true},
                    saveAsImage: {show: true},
//                    magicType : {show: true, type: ['bar','line']}
                }
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
            // 定义y轴，可以有多条
            yAxis: yAxis,
            series: series
        };

        myChart.setOption(option);
    };


    /**
     * 在新 标签页 打开 url
     * @param url
     */
    var open_in_new_tab = function (url) {
        window.open(url, '_blank');
    };


    /**
     * 导出 excel ，其实就是添加一个参数，其他都在后台处理
     */
    var export_excel = function () {
        var current_url = window.location.href;
        var arg = 'is_export=1';
        current_url += (current_url.indexOf('?') == -1 ? '?' : '&') + arg;
        common_module.open_in_new_tab(current_url);
    };

    var create_export_order_task = function() {
        var current_url = window.location.href;
        var arg = 'is_export=1';
        current_url += (current_url.indexOf('?') == -1 ? '?' : '&') + arg;
        layer.load();
        $.ajax({
            url: current_url,
            type: 'POST',
            data: {},
            dataType: 'JSON',
            success: function(ret) {
                layer.closeAll();
                layer.alert(ret.msg);
                console.log(ret);
            }
        });
    };

    var import_excel = function(thisobj) {
        var url = thisobj.attr('data-url');
        layer.open({
            type: 2,
            title: '导入目标',
            shadeClose: true,
            shade: 0.8,
            maxmin: true,
            moveOut: true,
            zIndex: layer.zIndex,
            area: ['800px', '600px'],
            content: url,
        });
    };


    /**
     * 获取当前页面的url
     */
    var get_current_url = function (not_search) {
        not_search = not_search || 1;
        var protocol = window.location.protocol;
        var host = window.location.host;
        var path = window.location.pathname;
        var search = window.location.search;


        if (not_search == 1) return protocol + '//' + host + path;
        else return protocol + '//' + host + path + search;

        //if (search)
        //{
        //    // 去除 问号
        //    search = search.substr(1, search.length -1);
        //    var search_arr = search.split('&');
        //    for (var value of search_arr)
        //    {
        //        var tmp_arr = value.split('=');
        //        if (tmp_arr[0])
        //    }
        //}
    };

    /**
     * 排序 转换
     */
    var sort_toggle = function () {
        var protocol = window.location.protocol;
        var host = window.location.host;
        var path = window.location.pathname;
        var search = window.location.search;

        if (search) {
            // 去除 问号
            search = search.substr(1, search.length - 1);
            var search_arr = search.split('&');
            var has_sort = false;
            for (var key in search_arr) {
                var value = search_arr[key];
                var tmp_arr = value.split('=');
                if (tmp_arr[0] == 'sort') {
                    has_sort = true;

                    if (tmp_arr[1] == 'asc') search = search.replace('sort=asc', 'sort=desc');
                    else search = search.replace('sort=desc', 'sort=asc');

                    break;
                }
            }
            if (!has_sort) search += '&sort=desc';
        }
        else {
            search = 'sort=asc';
        }

        var new_url = protocol + '//' + host + path + '?' + search;
        console.log(new_url);
        window.location.href = new_url;
    };


    // 调用该方法，阻止 form 提交的默认行为， 变成手动提交
    // 如果提交的是数组，那么就用逗号将其连接起来（因为太长的url会414报错）
    var init_submit_form = function (form_id) {
        $('#' + form_id).submit(function (e) {
            var data = $(this).serializeArray();
            var arr = [];
            var tmp = [];
            for (d in data) {
                var _data = data[d];
                if ($.trim(_data['value'])) {

                    if (_data['name'].endsWith('[]')) {
                        var tmp_str = _data['name'].substr(0, _data['name'].length - 2);
                        if (!tmp[tmp_str]) tmp[tmp_str] = [];
                        tmp[tmp_str].push(_data['value']);
                    }
                    else {
                        arr.push(_data['name'] + '=' + _data['value']);
                    }
                }
            }
            var search_str = '';
            if (tmp) {
                for (d in tmp) {
                    arr.push(d + '=' + tmp[d].join(','));
                }
            }
            if (arr) search_str += arr.join('&')
            //console.log(search_str);
            var url = new URL(location.href);
            var new_url = url.origin + url.pathname + '?' + arr.join('&');
            window.location = new_url;
            e.preventDefault();
        });

    };


    // 获取日期
    var get_date = function (day_span) {
        var day_seconds = 86400000;
        day_span = day_span || 0;
        var d = new Date(Date.now() + day_span * day_seconds);

        return d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate();
    };

    // 获取一个月有多少天
    var get_day_of_month = function (month, year) {
        var d = new Date(Date.now());
        year = year || d.getFullYear();
        switch (month) {
            case 1:
            case 3:
            case 5:
            case 7:
            case 8:
            case 10:
            case 12:
                return 31;
                break;
            case 4:
            case 6:
            case 9:
            case 11:
                return 30;
                break;
            case 2:
                if ((year % 4 == 0 && year % 100 != 0) || (year % 400 == 0)) return 29;
                else return 28;
                break;
            default :
                return 0;
                break;
        }
    };


    var init_data_table = function (pagestr, order, page_len, is_page, is_order) {
        order = order || [0, 'desc']; // 默认对一定行进行排序
        pagestr = pagestr || '';
        page_len = page_len || 20;
        is_page = is_page || 1;
        is_page = is_page == -1 ? false : true;
        is_order = is_order || 1;
        is_order = is_order == -1 ? false : true;
        var head = "#scroll_table_head";
        var maxpage = parseInt(($(document).height() - 300) / 42);
        if (maxpage > page_len) {
            page_len = maxpage;
        }
        var scrollY = $(head).length > 0 ? document.body.clientHeight - $(head).offset().top - 100 : document.body.clientHeight - 100;

        if (pagestr != '') {
            pagestr + ' 每页显示 <select class="form-control input-sm" style="width: 60px;display: inline-block;">' +
            '<option value="20">20</option>' +
            '<option value="50">50</option>' +
            '<option value="100">100</option>' +
            '<option value="200">200</option>' +
            '<option value="300">300</option>' +
            '</select> 记录'
        }
        
        // todo: js table
        $('.js-table').DataTable({
            searching: false,
            order: order, // 公共方法，默认不排序,否则产生异常
            paging: is_page,
            info: is_page,
            ordering: is_order,
            pageLength: page_len,
            scrollX: true,
            scrollY: scrollY,
            // fixedColumns: true,
            // fixedHeader: true,
            // responsive: true,
            autoWidth: false,
            language: {
                lengthMenu: pagestr,
                emptyTable: '未找到相关数据',
                paginate: {
                    previous: '上一页',
                    next: '下一页',
                    first: '首页',
                    last: '末页',
                },
            }
        });
    };

    /**
     * table 元素 的header 悬浮
     * @author lamkakyun
     * @date 2018-12-20 17:43:44
     */
    // var init_table_header_hover = function () {
    //     var is_first_time = true;
    //     var table = $("#scroll_table");//表格的id
    //     var table_head = $("#scroll_table_head");//表头
    //     var table_head_height = table_head.height();//表头高
    //     var table_head_offset = table_head.offset();
    //     var table_head_offset_top = table_head_offset.top;

    //     var clone_table = table.clone().attr('id', 'clone_table');// 更改复制的表格id
    //     clone_table.find('thead tr').first().remove();
    //     window.onscroll = function () {
    //         var scroll_top = document.body.scrollTop == 0 ? document.documentElement.scrollTop : document.body.scrollTop;
    //         var nav_head_height = $('.main-header').height();

    //         if (scroll_top - table_head_offset_top > 0) {
    //             if (is_first_time) {
    //                 // console.log('your first time, happy?');
    //                 $('body').append('<div id="shelter"></div>');//复制的表格所在的容器
    //                 $("#shelter").css({
    //                     'height': table_head_height,
    //                     'position': 'fixed',
    //                     'top': nav_head_height,
    //                     'overflow': 'hidden',
    //                     'margin-left': table_head_offset.left + 'px',
    //                     'width': table.width() + 3 + 'px',
    //                 });

    //                 clone_table.appendTo('#shelter');
    //                 clone_table.removeClass(); //删除table原来有的默认class，防止margin,padding等值影响样式
    //                 clone_table.css({'background-color': '#d2d6de'});
    //                 $('#shelter table tr th').css({'height': table_head_height, 'width': '140px', 'border-right': '1px solid #fff'});//此处可以自行发挥
    //                 $('#shelter table tr td').css({'padding': '10px', 'text-align': 'center'});

    //                 var ths = table.find('th');
    //                 var clone_table_ths = clone_table.find('th');
    //                 for (var i = 0; i < ths.length; i++) {
    //                     $(clone_table_ths[i]).css('width', ths[i].offsetWidth + 'px');
    //                 }
    //                 is_first_time = false;

    //             }

    //             if ($('#shelter').css('display') == 'none') $('#shelter').show();
    //         } else {
    //             if ($('#shelter').css('display') != 'none') $('#shelter').hide();
    //         }
    //     }
    // };

    return {
        init_platform_relate_account: init_platform_relate_account,
        show_colomn_chart: show_colomn_chart,
        open_in_new_tab: open_in_new_tab,
        export_excel: export_excel,
        create_export_order_task: create_export_order_task,
        sort_toggle: sort_toggle,
        get_current_url: get_current_url,
        init_submit_form: init_submit_form,
        get_date: get_date,
        init_data_table: init_data_table,
        get_day_of_month: get_day_of_month,
        import_excel: import_excel,
        // init_table_header_hover: init_table_header_hover,
    };
})();