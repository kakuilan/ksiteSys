/**
 * Created by kakuilan@163.com/lianq.net on 2018/3/19.
 * Desc:
 */

define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'upload', 'md5'], function ($, undefined, Backend, Table, Form, Upload, md5) {

    var Controller = {
        index: function () {

            // 初始化表格参数配置
            Table.api.init({
                search: true,
                advancedSearch: true,
                pagination: true,
                extend: {
                    "index_url": Config.extparam.logsUrl,
                    "add_url": "",
                    "edit_url": "",
                    "del_url": "",
                    "multi_url": "",
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                showExport: true,
                exportDataType: "base", //basic' 导出当前页的数据, 'all' 导出所有满足条件的数据, 'selected' 导出勾选中的数据.
                exportTypes: ['json', 'xml', 'csv', 'txt', 'doc', 'excel'],
                exportOptions:{
                    ignoreColumn: [0],  //忽略某一列的索引
                    fileName: 'appUserList',  //文件名称设置
                    worksheetName: 'sheet1',  //表格工作区名称
                    tableName: 'appUserList'
                },
                pageSize: 10,
                pageList: [10, 25, 50, 100,500,'All'],

                //禁用默认搜索
                search: true,
                //启用普通表单搜索
                commonSearch: true,
                //可以控制是否默认显示搜索单表,false则隐藏,默认为false
                searchFormVisible: false,
                columns: [
                    [
                        {field: 'id', title: 'ID'},
                        {field: 'uid', title: 'UID'},
                        {field: 'url', title: __('Url'), align: 'left'},
                        {field: 'ip', title: __('ip')},
                        //{field: 'create_time', title: __('Createtime'), formatter: Table.api.formatter.datetime},
                        {field: 'create_time', title: __('Createtime'), formatter: Table.api.formatter.datetime, operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"' },
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);//当内容渲染完成后

            var $fpwd = $('#frontPassword');
            var $bpwd = $('#backPassword');
            Form.api.bindevent($("#update-form"), function () {
                //提交前
                var fpwd = $.trim($fpwd.val());
                var bpwd = $.trim($bpwd.val());
                if(fpwd!='') $fpwd.val(md5(fpwd));
                if(bpwd!='') $bpwd.val(md5(bpwd));
                return true;
            }, function (data,ret) {
                //提交后
                $("input[name='row[password]']").val('');
                if(ret.status) {
                    var url = Backend.api.cdnurl($("#c-avatar").val());
                    top.window.$(".user-panel .image img,.user-menu > a > img,.user-header > img").prop("src", url);
                    return true;
                }
            });
            Upload.api.custom.changeavatar = function (response) {
                var url = Backend.api.cdnurl(response.url);
                $(".profile-user-img").prop("src", url);
            };
        },
        api: {
            formatter: {
                url: function (value, row, index) {
                    return '<div class="input-group input-group-sm" style="width:250px;"><input type="text" class="form-control input-sm" value="' + value + '"><span class="input-group-btn input-group-sm"><a href="' + value + '" target="_blank" class="btn btn-default btn-sm"><i class="fa fa-link"></i></a></span></div>';
                },
            },
        }
    };
    return Controller;
});