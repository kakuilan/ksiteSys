define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'upload', 'bootstrap-datetimepicker','dragsort','lkkFunc'], function ($, undefined, Backend, Table, Form) {
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
                {field: 'is_del', title: __('is_del'), searchList: {'1': '已删', '0': '正常'}, formatter: Controller.api.formatter.isdel },
                {field: 'disable_del', title: '禁止删除', searchList: {'1': '是', '0': '否'}, formatter: Controller.api.formatter.isnot },
                {field: 'data_type', title: '数据类型', searchList: dataTypes },
                {field: 'key', title: '配置键'},
                {field: 'title', title: '配置名称', operate: false},
                {field: 'value', title: '配置值', operate: false},
                {field: 'extra', title: '扩展值', operate: false},
                {field: 'sort', title: '排序', operate: false, sortable:true},
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

            var oriRow = $.parseJSON(Config.extparam.row);
            var $dataType = $('#data_type');
            var $inputType = $('#input_type');
            var $valueDiv = $('#valueDiv');
            var $uploadDiv = $('#uploadDiv');
            var vueInputName = 'value';
            var dtype, itype, mesg, rule = '';

            //是否有配置值
            var hasVue = (oriRow!=='' && (typeof oriRow.id !== 'undefined') );
            if(hasVue) {
                dtype = oriRow.data_type;
                itype = oriRow.input_type;
            }
            console.log('oriRow:', oriRow, hasVue, dtype, itype);

            //绑定数组元素拖拽排序
            var bindDragsort = function (form) {
                $("dl.fieldlist", form).dragsort({
                    itemSelector: 'dd',
                    dragSelector: ".btn-dragsort",
                    dragEnd: function () {

                    },
                    placeHolderTemplate: "<dd></dd>"
                });
            };

            //绑定日期时间元素事件
            var form = $('form');
            var bindDatepicker = function (form) {
                $('.datetimepicker', form).parent().css('position', 'relative');
                $('.datetimepicker', form).datetimepicker({
                        format: 'YYYY-MM-DD HH:mm:ss',
                        icons: {
                            time: 'fa fa-clock-o',
                            date: 'fa fa-calendar',
                            up: 'fa fa-chevron-up',
                            down: 'fa fa-chevron-down',
                            previous: 'fa fa-chevron-left',
                            next: 'fa fa-chevron-right',
                            today: 'fa fa-history',
                            clear: 'fa fa-trash',
                            close: 'fa fa-remove'
                        },
                        showTodayButton: true,
                        showClose: true
                });
            };


            var getLength = function(o) {
                var count = 0;
                for(var i in o){
                    count++;
                }
                return count;
            };

            //绑定数据类型
            $dataType.change(function () {
                dtype = $(this).val();

                if(dtype==='bool') {
                    $inputType.val('radio');
                }else if(dtype==='integer' || dtype==='float') {
                    $inputType.val('number');
                }else if(dtype==='datetime') {
                    $inputType.val('datetime');
                }else if(dtype==='string') {
                    $inputType.val('input');
                }else if(dtype==='array') {
                    $inputType.val('input');
                }else if(dtype==='text' || dtype==='json') {
                    $inputType.val('textarea');
                }

                $inputType.change();
            });

            //绑定控件类型
            $inputType.change(function () {
                itype = $(this).val();
                mesg = '请选择控件类型';
                console.log('log 111:', oriRow, dtype, itype, mesg);

                $valueDiv.html('');
                $uploadDiv.hide();

                if(dtype==='bool' && itype!=='radio') {
                    itype = '';
                    mesg = '数据类型[布尔型]只能选择控件[单选框]';
                }else if($.inArray(dtype, ['integer','float'])!==-1 && itype!=='number') {
                    itype = '';
                    mesg = '数据类型[整型、浮点型]只能选择控件[数字框]';
                }else if(dtype==='datetime' && itype!=='datetime') {
                    itype = '';
                    mesg = '数据类型[日期时间]只能选择控件[日期时间框]';
                }else if(dtype==='string' && $.inArray(itype, ['input','file'])===-1) {
                    itype = '';
                    mesg = '数据类型[字符串]只能选择控件[文本框、文件域]';
                }else if(dtype==='array' && $.inArray(itype, ['number','datetime','input','file'])===-1 ) {
                    itype = '';
                    mesg = '数据类型[数组]只能选择控件[数字框、日期时间框、文本框、文件域]';
                }else if($.inArray(dtype, ['text','json'])!==-1 && itype!=='textarea') {
                    itype = '';
                    mesg = '数据类型[长文本、JSON]只能选择控件[文本域]';
                }

                console.log('log 222:', oriRow, dtype, itype, mesg);
                if(itype==='') {
                    $(this).val(itype);
                    Toastr.error(mesg);
                    return false;
                }

                makeVueFun(dtype, itype, oriRow);
            });

            var makeVueFun = function (dtype, itype, row) {
                var html = [];
                var defVue = hasVue ? row.value : '';
                console.log('row:', row, row.length, hasVue, defVue, dtype, itype);

                if(itype==='radio') {
                    html.push('<div class="radio">');
                    if(hasVue) {
                        html.push('<label for="row[value]-1"><input id="row[value]-1" name="row[value]" type="radio" value="1" '+(defVue==1?' checked ':'')+' />是</label>');
                        html.push('<label for="row[value]-0"><input id="row[value]-0" name="row[value]" type="radio" value="0" '+(defVue==0?' checked ':'')+' />否</label>');
                    }else{
                        html.push('<label for="row[value]-1"><input id="row[value]-1" name="row[value]" type="radio" value="1" />是</label>');
                        html.push('<label for="row[value]-0"><input id="row[value]-0" name="row[value]" type="radio" value="0" />否</label>');
                    }
                    html.push('</div>');
                }else if(itype==='textarea') {
                    defVue = hasVue ? row.extra : '';
                    html.push('<textarea name="row[value]" cols="30" rows="3" class="form-control">'+defVue+'</textarea>');
                }else if(dtype==='array') {
                    //defVue = hasVue ? row.extra : [];
                    defVue = hasVue ? (oriRow.data_type!=='array' ? [] : $.parseJSON(row.extra)) : '';
                    var arrLen = defVue==='' ? 0 : getLength(defVue);
                    console.log('arr', defVue, arrLen);
                    html.push('<dl class="fieldlist" data-name="row[value]" rel="'+arrLen+'"><dd><ins>键名</ins><ins>键值</ins></dd>');

                    //原数组或对象
                    if(arrLen>0) {
                        var item = null;
                        var i = 0;
                        for(var k in defVue) {
                            item = defVue[k];
                            html.push('<dd class="form-inline"><input type="text" name="row[value][field][' + i + ']" class="form-control" value="'+(isArray(defVue)?'':k)+'" size="10" /> <input type="text" name="row[value][value][' + i + ']" class="form-control" value="'+item+'" data-rule="required" /> <span class="btn btn-sm btn-danger btn-remove"><i class="fa fa-times"></i></span> <span class="btn btn-sm btn-primary btn-dragsort"><i class="fa fa-arrows"></i></span></dd>');
                            i++;
                        }
                    }

                    html.push('<dd><a href="javascript:;" class="btn btn-sm btn-success btn-append"><i class="fa fa-plus"></i>追加</a></dd></dl>');
                }else if(itype==='number') {
                    rule = (dtype==='integer') ? 'digits' : 'isFloat';
                    html.push('<input type="number" class="form-control" name="row[value]" value="'+(defVue===''?'':defVue)+'" data-rule="'+rule+'" />');
                }else if(itype==='datetime') {
                    rule = 'length(0~19)';
                    html.push('<input type="text" class="form-control datetimepicker" name="row[value]" value="'+(defVue===''?'':defVue)+'" data-rule="'+rule+'" />');
                }else if(itype==='file') {
                    html.push('<input type="text" name="row[value]" id="c-value" class="form-control" value="'+(defVue===''?'':defVue)+'" />');
                    $uploadDiv.show();
                }else{ //input
                    if(itype==='input') rule = 'length(0~125)';
                    html.push('<input type="text" class="form-control" name="row[value]" value="'+(defVue===''?'':defVue)+'" data-rule="'+rule+'" />');
                }

                $valueDiv.html(html.join(''));

                if(dtype==='array') {
                    bindDragsort(form);
                }
                if(itype==='datetime') {
                    bindDatepicker(form);
                }
            };

            //数组元素列表绑定
            $(document).on("click", ".fieldlist .btn-append", function () {
                var rel = parseInt($(this).closest("dl").attr("rel")) + 1;
                var name = $(this).closest("dl").data("name");
                $(this).closest("dl").attr("rel", rel);
                $('<dd class="form-inline"><input type="text" name="' + name + '[field][' + rel + ']" class="form-control" value="" size="10" /> <input type="text" name="' + name + '[value][' + rel + ']" class="form-control" value="" data-rule="required" /> <span class="btn btn-sm btn-danger btn-remove"><i class="fa fa-times"></i></span> <span class="btn btn-sm btn-primary btn-dragsort"><i class="fa fa-arrows"></i></span></dd>').insertBefore($(this).parent());
            });
            $(document).on("click", ".fieldlist dd .btn-remove", function () {
                $(this).parent().remove();
            });

            //触发原值点击
            if(hasVue) {
                $dataType.change();
                $inputType.change();
                makeVueFun(dtype, itype, oriRow);
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
                },
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