<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/6/25
 * Time: 18:47
 * Desc: -
 */
 
 
?>
{{ partial("common/header", ['BDCLASS':'','BDSTYLE':'']) }}

<style>
    .ztree li span.button.add {
        margin-left:2px; margin-right: -1px; background-position:-144px 0; vertical-align:top; *vertical-align:middle
    }
</style>

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
                        <div class="widget-box widget-color-blue2">
                            <div class="widget-header">
                                <h4 class="widget-title lighter smaller">后台模块管理</h4>
                            </div>
                            <div class="widget-body">
                                <div class="widget-main padding-8 tree tree-unselectable">
                                    <div class="row">
                                        <div class="ztreeBoxBackground col-xs-6 left">
                                            <ul id="treeBox" class="ztree">
                                            </ul>
                                        </div>

                                        <div class="col-xs-6 right">
                                            <div>
                                                <span>所选模块的可操作列表</span>&nbsp;&nbsp;
                                                <span class="glyphicon glyphicon-plus green" style="cursor:pointer;" id="addOprateBtn" title="新增操作">
                                                </span>
                                            </div>
                                            <div class="widget-box" style="min-height: 15px;">
                                                <table class="table table-hover table-condensed">
                                                    <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>操作名称</th>
                                                        <th>操作标识</th>
                                                        <th>状态</th>
                                                        <th>包含动作</th>
                                                        <th>编辑</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td colspan="5" style="display: none">
                                                                <button type="button" id="lodBtn" data-loading-text="加载中..." class="btn btn-primary" autocomplete="off">
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
    var zTreeObj;
    var zNodes;
    var currentNode;
    var listUrl = "{{listUrl}}";
    var editUrl = "{{editUrl}}";
    var delUrl = "{{delUrl}}";
    var operaListUrl = "{{operaListUrl}}";
    var operaEditUrl = "{{operaEditUrl}}";
    var operaDelUrl = "{{operaDelUrl}}";
    var getOperaLis;
</script>
<?php $otherJsCont = <<<EOT
<script>
    $(function($) {
        var vLodBtn = $('#lodBtn'),
            vFirsTr = vLodBtn.parents('tr'),
            vTable = vFirsTr.parent();

        function beforeDrag(treeId, treeNodes) {
            return false;
        }
        function beforeEditName(treeId, treeNode) {
            var zTree = $.fn.zTree.getZTreeObj("treeBox");
            zTree.selectNode(treeNode);
            currentNode = treeNode;
            openLayer(editUrl, treeNode.id, treeNode.pId, '编辑模块');
            return false;
        }
        function beforeRemove(treeId, treeNode) {
            var zTree = $.fn.zTree.getZTreeObj("treeBox");
            zTree.selectNode(treeNode);
            layer.confirm("确认删除该模块吗？", {icon: 3, title:'提示'}, function(index){
                $.getJSON(delUrl, {id: treeNode.id}, function(res){
                    if(res.status){
                        zTree.removeNode(treeNode);
                    }else{
                        layer.alert(res.msg);
                    }
                });
                layer.close(index);
            });
            return false;
        }
        function onRemove(e, treeId, treeNode) {
        }
        function beforeRename(treeId, treeNode, newName, isCancel) {
            return false;
        }
        function onRename(e, treeId, treeNode, isCancel) {
        }
        function showRemoveBtn(treeId, treeNode) {
            return !(treeNode.level==0)
        }
        function showRenameBtn(treeId, treeNode) {
            return !(treeNode.level==0);
        }

        function addHoverDom(treeId, treeNode) {
            var sObj = $("#" + treeNode.tId + "_span");
            if (treeNode.editNameFlag || $("#addBtn_"+treeNode.tId).length>0) return;
            var addStr = "<span class='button add' id='addBtn_" + treeNode.tId
                + "' title='add node' onfocus='this.blur();'></span>";
            sObj.after(addStr);
            var btn = $("#addBtn_"+treeNode.tId);
            if (btn) {
                btn.bind("click", function(){
                    currentNode = treeNode;
                    openLayer(editUrl, 0, treeNode.id, '新增模块');
                    return false;
                });
            }
        }

        function removeHoverDom(treeId, treeNode) {
        }

        function openLayer(url, id, parent, title) {
            var indexL = layer.open({
                type: 2,
                title: title,
                shadeClose: true,
                shade: false,
                maxmin: true, //开启最大化最小化按钮
                area: ['605px', '405px'],
                offset: '10px',
                content: url+'?id='+id+'&parent='+parent
            });
            layer.style(indexL,{
                position: 'absolute'
            });
            return indexL;
        }

        function onClick(event, treeId, treeNode) {
            currentNode = treeNode;
            getOperaLis();
        }

        var setting = {
            view: {
                addHoverDom: addHoverDom,
                removeHoverDom: removeHoverDom,
                selectedMulti: false
            },
            edit: {
                enable: true,
                editNameSelectAll: true,
                showRemoveBtn: showRemoveBtn,
                showRenameBtn: showRenameBtn
            },
            data: {
                simpleData: {
                    enable: true
                }
            },
            callback: {
                beforeDrag: beforeDrag,
                beforeEditName: beforeEditName,
                beforeRemove: beforeRemove,
                beforeRename: beforeRename,
                onRemove: onRemove,
                onRename: onRename,
                onClick: onClick
            }
        };

        $.getJSON(listUrl, function (res) {
            zNodes = res.data;
            zNodes.unshift({id:0, pId:-1, name:"根模块", open:true, isParent:true});
            zTreeObj = $.fn.zTree.init($("#treeBox"), setting, zNodes);
        });

        //新增操作页面
        $('#addOprateBtn').click(function () {
            if(currentNode==undefined || typeof currentNode.id==undefined || currentNode.id==0) {
                //icon: 1对,2错,3问号,4锁
                layer.alert('请先选中左侧的子模块', {icon:3});
            }else if(currentNode.id>0) {
                admOpenLayer(operaEditUrl, '新增操作', {mid:currentNode.id}, 750, 550);
            }
        });

        //获取/刷新模块操作列表
        getOperaLis = function () {
            vFirsTr.siblings('tr').remove();
            if(currentNode!=undefined && currentNode.id>0) {
                vLodBtn.parent().show();
                vLodBtn.button('loading');
                $.get(operaListUrl, {mid:currentNode.id}, function (res) {
                    var html = [], item = null;
                    if(res.data.length>0) {
                        for (i in res.data) {
                            item = res.data[i];
                            html.push('<tr>');
                            html.push('<td>'+item.id+'</td>');
                            html.push('<td>'+item.name+'</td>');
                            html.push('<td>'+item.tag+'</td>');
                            html.push('<td>'+(item.status==1 ? '启用' : '停用')+'</td>');
                            html.push('<td>'+item.action_num+'</td>');
                            html.push('<td data-id="'+item.id+'">');
                            html.push('<a class="green editOpr" href="javascript:;">');
                            html.push('<i class="ace-icon fa fa-pencil bigger-130"></i>');
                            html.push('</a>&nbsp;&nbsp;');
                            html.push('<a class="red delOpr" href="javascript:;">');
                            html.push('<i class="ace-icon fa fa-trash-o bigger-130"></i>');
                            html.push('</a></td></tr>');
                        }
                    }
                    html = html.join('');
                    vFirsTr.after(html);

                    vLodBtn.button('reset');
                    vLodBtn.parent().hide();
                });
            }
        };

        //操作列表的编辑/删除
        vTable.on('click', '.editOpr', function () {
            var id = $(this).parent('td').data('id'),
                mid = currentNode.id;
            admOpenLayer(operaEditUrl, '编辑操作', {id:id,mid:mid});
        });
        vTable.on('click', '.delOpr', function () {
            var id = $(this).parent('td').data('id'),
                mid = currentNode.id;

            layer.confirm('您确定要删除该角色吗？', {icon: 3, title:'提示'}, function(index){
                $.getJSON(operaDelUrl, {id:id, mid:mid}, function(res){
                    if(res.status){
                        layer.alert('操作成功！', {icon:1});
                        getOperaLis();
                    }else{
                        layer.alert(res.msg, {icon:2});
                    }
                });
                layer.close(index);
            });
        });


    }); //jq
</script>
EOT;
?>
{{ partial("common/footer", ['FOOT_OTH_CONT': otherJsCont]) }}