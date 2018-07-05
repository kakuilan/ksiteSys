define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'upload'], function ($, undefined, Backend, Table, Form) {
    var table = $("#table");
    var Controller = {
        //配置列表首页
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                queryParamsType: 'undefined',
                extend: {
                    index_url: Config.extparam.listUrl,
                    add_url: Config.extparam.addUrl,
                    edit_url: Config.extparam.editUrl,
                    del_url: Config.extparam.delUrl,
                    multi_url: Config.extparam.multiUrl
                }
            });

            var statusArr = $.parseJSON(Config.extparam.statusArr);

            //表格
            var columns = [
                //该列为复选框字段,如果后台的返回state值将会默认选中
                {field: 'state', checkbox: true },
                {field: 'site_id', title: '站点ID', sortable:true},
                {field: 'site_name', title: '站点名称' },
                {field: 'site_url', title: '网址' },
                {field: 'status', title: __('status'), searchList: statusArr },
                {field: 'sort', title: '排序', operate: false, sortable:true},
                {field: 'create_time', title: __('create_time'), operate: false, formatter: Table.api.formatter.datetime },
                {field: 'update_time', title: __('update_time'), operate: false, formatter: Table.api.formatter.datetime },
                {field: 'username', title: '更新者', operate: false},

                {field: 'operate', title: __('Operate'),operate: false, events: Controller.api.events.operate, formatter: Controller.api.formatter.operate}
            ];

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'site_id',
                sortName: 'site_id',
                sortOrder: 'desc',
                showExport: false,
                exportDataType: "base", //basic' 导出当前页的数据, 'all' 导出所有满足条件的数据, 'selected' 导出勾选中的数据.
                exportTypes: ['json', 'xml', 'csv', 'txt', 'doc', 'excel'],
                exportOptions:{
                    ignoreColumn: [0],  //忽略某一列的索引
                    fileName: 'list',  //文件名称设置
                    worksheetName: 'sheet1',  //表格工作区名称
                    tableName: 'list'
                },
                columns: [
                    columns
                ],
                pageSize: 10,
                pageList: [10, 25, 50, 100,500,'All'],

                //禁用默认搜索
                search: false,
                //启用普通表单搜索
                commonSearch: true,
                //可以控制是否默认显示搜索单表,false则隐藏,默认为false
                searchFormVisible: false
            });

            //在表格内容渲染完成后回调的事件
            table.on('post-body.bs.table', function (e, settings, json, xhr) {

            });

            // 为表格绑定事件
            Table.api.bindevent(table);

        },

        //编辑页
        edit: function () {
            Controller.api.bindevent();

        },

        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            formatter: {//渲染的方法
                operate:function (value, row, index) {
                    console.log('opp', value, row, index);
                    var defOpr = Table.api.formatter.operate(value, row, index, $("#table"));
                    var extOpr = '';
                    return defOpr + extOpr;
                }
            },
            events: {//绑定事件的方法
                operate: $.extend({

                }, Table.api.events.operate)
            }

        }
    };
    return Controller;
});