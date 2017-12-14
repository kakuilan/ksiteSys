<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/7/13
 * Time: 22:49
 * Desc: -
 */


?>
{{ partial("common/header", ['BDCLASS':'','BDSTYLE':'']) }}

<!--主容器 start-->
<div class="main-container ace-save-state" id="main-container">
    <!-- 页面容器 start -->
    <div class="main-content">
        <!--内页容器 start-->
        <div class="main-content-inner">
            <!-- /section:basics/content.breadcrumbs -->
            <div class="page-content">
                <!--正文容器 start-->
                <div class="row">
                    <div class="col-xs-12">
                        <div class="clearfix input-group " role="group">
                            <form class="form-inline">
                                <button type="button" class="btn btn-primary btn-sm btn-search addAction">
                                    <i class="ace-icon fa fa-plus"></i>
                                    新增
                                </button>

                                <label class="inline">
                                    <span class="lbl">&nbsp;&nbsp;</span>
                                </label>
                                <label class="inline">
                                    <span class="lbl">用户状态：</span>
                                </label>
                                <select class="chosen-select" data-placeholder="请选择状态..." name="status" id="status">
                                    <option value="" >全部</option>
                                    {% if statusArr %}
                                    {% for vue,name in statusArr %}
                                    <option value="{{vue}}" title="{{name}}" >
                                        {{name}}
                                    </option>
                                    {% endfor %}
                                    {% endif %}
                                </select>

                                <label class="inline">
                                    <span class="lbl">&nbsp;&nbsp;</span>
                                </label>
                                <label class="inline">
                                    <span class="lbl">手机状态：</span>
                                </label>
                                <select class="chosen-select" data-placeholder="请选择状态..." name="mobile_status" id="mobile_status">
                                    <option value="" >全部</option>
                                    {% if mobileStatusArr %}
                                    {% for vue,name in mobileStatusArr %}
                                    <option value="{{vue}}" title="{{name}}" >
                                        {{name}}
                                    </option>
                                    {% endfor %}
                                    {% endif %}
                                </select>

                                <label class="inline">
                                    <span class="lbl">&nbsp;&nbsp;</span>
                                </label>
                                <label class="inline">
                                    <span class="lbl">邮箱状态：</span>
                                </label>
                                <select class="chosen-select" data-placeholder="请选择状态..." name="email_status" id="email_status">
                                    <option value="" >全部</option>
                                    {% if emailStatusArr %}
                                    {% for vue,name in emailStatusArr %}
                                    <option value="{{vue}}" title="{{name}}" >
                                        {{name}}
                                    </option>
                                    {% endfor %}
                                    {% endif %}
                                </select>

                                <label class="inline">
                                    <span class="lbl">&nbsp;&nbsp;</span>
                                </label>
                                <label class="inline">
                                    <span class="lbl">用户类型：</span>
                                </label>
                                <select class="chosen-select" data-placeholder="请选择状态..." name="type" id="type">
                                    <option value="" >全部</option>
                                    {% if typesArr %}
                                    {% for vue,name in typesArr %}
                                    <option value="{{vue}}" title="{{name}}" >
                                        {{name}}
                                    </option>
                                    {% endfor %}
                                    {% endif %}
                                </select>

                                <label class="inline">
                                    <span class="lbl">&nbsp;&nbsp;</span>
                                </label>
                                <label class="inline">
                                    <span class="lbl">关键字：</span>
                                </label>
                                <input type="text" class="input-large input-search" placeholder="用户名" id="keyword" name="keyword" maxlength="50"/>
                                <button type="button" class="btn btn-primary btn-sm btn-search" title="搜索" id="searchBtn">
                                    查询
                                </button>

                            </form>
                        </div>
                        <table id="grid-table"></table>
                        <div id="grid-pager"></div>
                    </div>
                </div><!-- /.row -->
                <!--正文容器 end-->
            </div>
            <!-- /.page-content -->
        </div>
        <!--内页容器 end-->
    </div>
    <!-- 页面容器 end -->
</div>
<!--主容器 end-->

<script>
    var listUrl = "{{listUrl}}";
    var editUrl = "{{editUrl}}";
    var pwdUrl = "{{pwdUrl}}";
</script>
<?php $otherJsCont = <<<EOT
EOT;
?>
{{ partial("common/footer", ['FOOT_OTH_CONT': otherJsCont]) }}
<script>
    $(function($) {
        //手工新增
        $('.addAction').click(function(event){
            layer.open({
                type: 2,
                title: '新增用户',
                shadeClose: true,
                shade: false,
                maxmin: true, //开启最大化最小化按钮
                area: ['605px', '350px'],
                offset: '10px',
                content: editUrl
            });
            event.stopPropagation();
        });

        var grid_selector = "#grid-table";
        var pager_selector = "#grid-pager";

        //resize to fit page size
        $(window).on('resize.jqGrid', function () {
            $(grid_selector).jqGrid('setGridWidth', $(".page-content").width() );
        });
        //resize on sidebar collapse/expand
        var parent_column = $(grid_selector).closest('[class*="col-"]');
        $(document).on('settings.ace.jqGrid' , function(ev, event_name, collapsed) {
            if( event_name === 'sidebar_collapsed' || event_name === 'main_container_fixed' ) {
                //setTimeout is for webkit only to give time for DOM changes and then redraw!!!
                setTimeout(function() {
                    $(grid_selector).jqGrid( 'setGridWidth', parent_column.width() );
                }, 0);
            }
        });

        $(grid_selector).jqGrid({
            url : listUrl,
            datatype : "json",
            viewrecords : true,
            jsonReader : {
                repeatitems: false,
                root: "data.list", //数据列表
                page : "data.page", //当前页码
                total : "data.total", //总页数
                records: "data.records", //总记录数
            },
            caption: "数据列表",
            colNames:['操作','编号ID','用户名','邮箱','手机','用户状态','邮箱状态','手机状态','用户类型','创建时间','修改时间'],
            colModel: [
                { name: 'myact',index:'myact', width:60, fixed:true, sortable:false, resize:false,editable:false},
                { name: 'id', index: 'id', sortable: false, width:60, fixed:true},
                { name: 'name', index: 'name', sortable: false },
                { name: 'desc', index: 'desc', sortable: false },
                { name: 'status_desc', index: 'status_desc', sortable: false },
                { name: 'sort', index: 'sort', sortable: true },
                { name: 'create_time', index: 'create_time', sortable: true},
                { name: 'create_by', index: 'create_by', sortable: false},
                { name: 'update_time', index: 'update_time', sortable: true},
                { name: 'update_by', index: 'update_by', sortable: false}
            ],
            rowNum: 15,
            rowList:[2,10,15,20,30,50,100],
            pager : pager_selector,
            altRows: true,
            multiselect: true,
            multiboxonly: true,
            height: 'auto',
            gridComplete: function(){
                var _this = $(this),id=null,btns=null,rowDatas=null;
                var ids = _this.jqGrid('getDataIDs');
                for(var i=0;i<ids.length;i++){
                    id = ids[i];
                    rowDatas = _this.jqGrid('getRowData', id);
                    btns = '<a class="edit_info" href="javascript:;" title="编辑信息" data-id="'+id+'" style="margin-left:3px;"><i class="glyphicon glyphicon-edit"></i></a>';
                    btns += '<a class="auth_info" href="javascript:;" title="角色授权" data-id="'+id+'" style="margin-left:3px;"><i class="glyphicon glyphicon-filter"></i></a>';
                    btns += '<a class="del_info" href="javascript:;" title="删除信息" data-id="'+id+'" style="margin-left:3px;"><i class="glyphicon glyphicon-remove"></i></a>';
                    _this.jqGrid('setRowData',ids[i],{myact:btns});
                }
            },
            loadComplete : function(data) {
                var table = this, _this = $(this);
                setTimeout(function(){
                    updatePagerIcons(table);
                }, 0);

                //无数据时提示
                var records = _this.getGridParam('records');
                if($('.norecords').length==0) {
                    _this.parent().parent().append("<div class=\"norecords\" style=\"display:none\">没有符合条件的数据！</div>");
                }
                if(records=='undefined' || records==0 || records==null){
                    $('.norecords').show();
                }else{
                    $(".norecords").hide();
                }

                //调整宽度
                var width = $('.ui-jqgrid-bdiv').width();
                $('.ui-jqgrid-bdiv').width(width+1);
            }

        });
        $(window).triggerHandler('resize.jqGrid');//trigger window resize to make the grid get the correct size

        //replace icons with FontAwesome icons like above
        function updatePagerIcons(table) {
            var replacement =
                {
                    'ui-icon-seek-first' : 'ace-icon fa fa-angle-double-left bigger-140',
                    'ui-icon-seek-prev' : 'ace-icon fa fa-angle-left bigger-140',
                    'ui-icon-seek-next' : 'ace-icon fa fa-angle-right bigger-140',
                    'ui-icon-seek-end' : 'ace-icon fa fa-angle-double-right bigger-140'
                };
            $('.ui-pg-table:not(.navtable) > tbody > tr > .ui-pg-button > .ui-icon').each(function(){
                var icon = $(this);
                var vClass = $.trim(icon.attr('class').replace('ui-icon', ''));

                if(vClass in replacement) icon.attr('class', 'ui-icon '+replacement[vClass]);
            })
        }


        //操作
        $('table').on('click','.edit_info',function(){
            var idx = $(this).data('id');
            var row = $(grid_selector).jqGrid('getRowData', idx);
            var indexL = layer.open({
                type: 2,
                title: '修改信息',
                shadeClose: true,
                shade: false,
                maxmin: true, //开启最大化最小化按钮
                area: ['605px', '350px'],
                offset: '10px',
                content: editUrl +'?id='+row.id
            });
            layer.style(indexL,{
                position: 'absolute',
            });
        });
        $('table').on('click','.auth_info',function(){
            var idx = $(this).data('id');
            var row = $(grid_selector).jqGrid('getRowData', idx);
            var indexL = layer.open({
                type: 2,
                title: '角色授权',
                shadeClose: true,
                shade: false,
                maxmin: true, //开启最大化最小化按钮
                area: ['605px', '350px'],
                offset: '10px',
                content: authorizeUrl+'?rid='+row.id
            });
            layer.style(indexL,{
                position: 'absolute',
            });
        });
        $('table').on('click','.del_info',function(){
            var idx = $(this).data('id');
            var row = $(grid_selector).jqGrid('getRowData', idx);
            var msg = '您确定要删除该角色吗？';
            var new_status = (row.status==0) ? 1 : 0;
            layer.confirm(msg, {icon: 3, title:'提示'}, function(index){
                $.getJSON(delUrl, {id: row.id}, function(res){
                    if(res.status){
                        layer.alert('操作成功！');
                        refreshJqGrid('#grid-table');
                    }else{
                        layer.alert(res.msg);
                    }
                });
                layer.close(index);
            });
            return;
        });

        //搜索按钮
        $('#searchBtn').click(function(){
            $(".norecords").hide();
            var data = {
                status : $('#status').val() ,
                keyword : $.trim($('#keyword').val())
            };

            $(grid_selector).jqGrid('setGridParam',{
                datatype: 'json',
                postData: data, //发送数据
                page:1
            }).trigger("reloadGrid"); //重新载入
        });

        //下拉
        $('.chosen-select').chosen({allow_single_deselect:true, width:'120px'});

    });
</script>