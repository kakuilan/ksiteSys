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
            var dataTypes = $.parseJSON(Config.extparam.dataTypes);
            var inputTypes = $.parseJSON(Config.extparam.inputTypes);

            //表格
            var columns = [
                //该列为复选框字段,如果后台的返回state值将会默认选中
                {field: 'state', checkbox: true },
                {field: 'id', title: 'ID', sortable:true},
                {field: 'site_id', title: '站点', searchList: sites },
                {field: 'is_del', title: __('is_del'), searchList: {'1': '已删', '0': '正常'}, formatter: Controller.api.formatter.is_del },
                {field: 'data_type', title: '数据类型', searchList: dataTypes },
                {field: 'input_type', title: '控件类型', searchList: inputTypes },
                {field: 'key', title: '配置键'},
                {field: 'title', title: '配置标题', operate: false},
                {field: 'sort', title: '排序', operate: false, sortable:true},

                {field: 'create_time', title: __('create_time'), operate: false, formatter: Table.api.formatter.datetime },
                {field: 'update_time', title: __('update_time'), operate: false, formatter: Table.api.formatter.datetime },
                {field: 'username', title: '更新者', operate: false},

                {field: 'operate', title: __('Operate'),operate: false, events: Controller.api.events.operate, formatter: Controller.api.formatter.operate}
            ];

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'slide_id',
                sortName: 'listorder',
                sortOrder: 'asc',
                showExport: false,
                exportDataType: "base", //basic' 导出当前页的数据, 'all' 导出所有满足条件的数据, 'selected' 导出勾选中的数据.
                exportTypes: ['json', 'xml', 'csv', 'txt', 'doc', 'excel'],
                exportOptions:{
                    ignoreColumn: [0],  //忽略某一列的索引
                    fileName: 'appUserList',  //文件名称设置
                    worksheetName: 'sheet1',  //表格工作区名称
                    tableName: 'appUserList'
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

            var oriRow = Config.extparam.row;
            var $dataType = $('#data_type');
            var $inputType = $('#input_type');
            var $valueDiv = $('#valueDiv');
            var vueInputName = 'value';
            var dtype, itype = null;

            //绑定数据类型
            $dataType.change(function () {
                dtype = $(this).val();

                if(dtype==='bool') {
                    $inputType.val('radio');
                }else if(dtype==='integer' || dtype==='float') {
                    $inputType.val('input');
                }else if(dtype==='text' || dtype==='array' || dtype==='json') {
                    $inputType.val('textarea');
                }
                $inputType.change();
            });
            
            //绑定控件类型
            $inputType.change(function () {
                itype = $(this).val();
                console.log('log:', oriRow, dtype, itype);

                $valueDiv.html('');
                if(itype==='radio' && dtype!=='bool') {
                    $(this).val('');
                    Toastr.error('请先修改数据类型-布尔型');
                    return false;
                }else if(itype==='textarea' && $.inArray(dtype, ['text', 'array', 'json'])===-1 ) {
                    $(this).val('');
                    Toastr.error('请先修改数据类型-长文本,数组,JSON');
                    return false;
                }else if(itype==='file' && dtype!=='string') {
                    $(this).val('');
                    Toastr.error('请先修改数据类型-字符串');
                    return false;
                }else if(itype==='input' && $.inArray(dtype, ['integer', 'float', 'string'])===-1 ) {
                    $(this).val('');
                    Toastr.error('请先修改数据类型-整型,浮点型,字符串');
                    return false;
                }

                makeVueFun(dtype, itype, oriRow);
            });

            var makeVueFun = function (dtype, itype, row) {
                var html = [];
                var hasVue = (row.length>0);
                var defVue = hasVue ? row.value : null;

                if(itype==='radio') {
                    if(hasVue) {
                        html.push('<label for="row[value]-1"><input id="row[value]-1" name="row[value]" type="radio" value="1" '+(defVue==1?' checked ':'')+' />是</label>');
                        html.push('<label for="row[value]-0"><input id="row[value]-0" name="row[value]" type="radio" value="0" '+(defVue==0?' checked ':'')+' />否</label>');
                    }else{
                        html.push('<label for="row[value]-1"><input id="row[value]-1" name="row[value]" type="radio" value="1" />是</label>');
                        html.push('<label for="row[value]-0"><input id="row[value]-0" name="row[value]" type="radio" value="0" />否</label>');
                    }
                }

                html = html.join('');
                $valueDiv.html(html);
            }



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