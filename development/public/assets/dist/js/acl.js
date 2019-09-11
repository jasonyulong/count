var indexController = {
    // 初始化
    inits: function () {
        // 根据登录时间判断是否要锁屏
    },
    // 首页
    index: function () {
        return true;
    },
    // 系统设置
    config: function () {
        var form = $(".dialog-form");
        //追加控制
        $(".fieldlist", form).on("click", ".btn-append,.append", function (e, row) {
            var container = $(this).closest("dl");
            var index = container.data("index");
            var name = container.data("name");
            var data = container.data();
            index = index ? parseInt(index) : 0;
            container.data("index", index + 1);
            var row = row ? row : {};
            var vars = {index: index, name: name, data: data, row: row};

            var html = '<dd class="form-inline"><input type="text" name="<%=name%>[field][]" class="form-control" value="" size="10" /> <input type="text" name="<%=name%>[value][]" class="form-control" value="" size="40" /> <span class="btn btn-sm btn-danger btn-remove"><i class="fa fa-times"></i></span></dd>';

            html = html.replace('<%=name%>', vars.name);
            html = html.replace('<%=name%>', vars.name);

            $(html).insertBefore($(this).closest("dd"));
            $(this).trigger("fa.event.appendfieldlist", $(this).closest("dd").prev());
        });
        //移除控制
        $(".fieldlist", form).on("click", "dd .btn-remove", function () {
            $(this).closest("dd").remove();
        });
    },
    // 菜单管理
    rule: function () {
        $(document).on('click', ".btn-search-icon", function () {
            window.open('http://adminlte.la998.com/pages/UI/icons.html');
        });
    },
    // 角色管理
    group: function () {
        var checkedAll = function () {
            var r = $("#treeview").jstree("get_all_checked");
            $("input[name='row[rules]']").val(r.join(','));
        };
        //读取选中的条目
        $.jstree.core.prototype.get_all_checked = function (full) {
            var obj = this.get_selected(), i, j;
            for (i = 0, j = obj.length; i < j; i++) {
                obj = obj.concat(this.get_node(obj[i]).parents);
            }
            obj = $.grep(obj, function (v, i, a) {
                return v != '#';
            });
            obj = obj.filter(function (itm, i, a) {
                return i == a.indexOf(itm);
            });
            return full ? $.map(obj, $.proxy(function (i) {
                return this.get_node(i);
            }, this)) : obj;
        };
        // 选中时间
        $('#treeview').bind("activate_node.jstree", function (obj, e) {
            checkedAll();
        });
        // 默认事件
        if ($("#treeview").length > 0) {
            checkedAll();
        }
        //全选和展开
        $(document).on("click", "#checkall", function () {
            $("#treeview").jstree($(this).prop("checked") ? "check_all" : "uncheck_all");
            checkedAll();
        });

        $(document).on("click", "#expandall", function () {
            $("#treeview").jstree($(this).prop("checked") ? "open_all" : "close_all");
        });

        $("select[name='row[pid]']").trigger("change");
    },


}
;

$(function () {
// 初始化
indexController.inits();
// 菜单管理
indexController.rule();
// 系统配置
indexController.config();
// 角色管理
indexController.group();
});