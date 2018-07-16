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

            var sites = $.parseJSON(Config.extparam.sites);
            var authStatusArr = $.parseJSON(Config.extparam.authStatusArr);
            var persistentStatusArr = $.parseJSON(Config.extparam.persistentStatusArr);
            var hasThirdArr = $.parseJSON(Config.extparam.hasThirdArr);
            var belongTypeArr = $.parseJSON(Config.extparam.belongTypeArr);
            var fileTypeArr = $.parseJSON(Config.extparam.fileTypeArr);
            var tagArr = $.parseJSON(Config.extparam.tagArr);
            
            //表格
            var columns = [
                //该列为复选框字段,如果后台的返回state值将会默认选中
                {field: 'state', checkbox: true },
                {field: 'id', title: '附件ID', sortable:true},
                {field: 'site_id', title: '站点', operate: false},
                {field: 'is_del', title: '删除', searchList: {'1': '已删', '0': '正常'}, formatter: Controller.api.formatter.isdel },
                {field: 'url', title: __('Preview'), formatter: Controller.api.formatter.thumb, operate: false},
                {field: 'is_auth', title: '审核', searchList: $.extend({}, authStatusArr) },
                {field: 'is_persistent', title: '持久化', searchList: $.extend({}, persistentStatusArr) },
                {field: 'has_third', title: '第三方', searchList: $.extend({}, hasThirdArr) },
                {field: 'compress_enable', title: '可压缩', searchList: {'1': '是', '0': '否'}, formatter: Controller.api.formatter.isnot },
                {field: 'belong_type', title: '归属', searchList: $.extend({}, belongTypeArr) },
                {field: 'file_type', title: '类型', searchList: $.extend({}, fileTypeArr) },
                {field: 'tag', title: '标识', searchList: $.extend({}, tagArr) },
                {field: 'uid', title: 'UID' },
                {field: 'img_width', title: '图宽', operate: false},
                {field: 'img_height', title: '图高', operate: false},
                {field: 'quote_num', title: '引用', operate: false},
                {field: 'downl_num', title: '下载', operate: false},
                {field: 'file_size', title: '大小K', operate: false},
                {field: 'title', title: '标题'},
                {field: 'file_ext', title: '后缀'},
                {field: 'file_name', title: '文件名'},

                {field: 'create_time', title: __('create_time'), operate: false, formatter: Table.api.formatter.datetime },
                {field: 'update_time', title: __('update_time'), operate: false, formatter: Table.api.formatter.datetime },
                {field: 'username', title: '更新者', operate: false},

                {field: 'operate', title: __('Operate'),operate: false, events: Controller.api.events.operate, formatter: Controller.api.formatter.operate}
            ];

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
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
                isnot: function (value, row, index) {
                    return value=='0' ? '否' : '是';
                },
                isdel: function (value, row, index) {
                    value = (value=='1') ? '<span class="text-muted"><i class="fa fa-circle"></i>已删</span>' : '<span class="text-success"><i class="fa fa-circle"></i>正常</span>';
                    return value;
                },
                operate:function (value, row, index) {
                    var defOpr = Table.api.formatter.operate(value, row, index, $("#table"));
                    var extOpr = '';
                    return defOpr + extOpr;
                },
                thumb: function (value, row, index) {
                    if (row.file_type_vue ==3) {
                        var style = '';
                        return '<a href="' + row.url + '" target="_blank"><img src="' + row.url + style + '" alt="" style="max-height:80px;max-width:100px"></a>';
                    } else {
                        return '<a href="' + row.url + '" target="_blank">' + __('None') + '</a>';
                    }
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