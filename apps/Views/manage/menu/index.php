<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/5/7
 * Time: 14:22
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
                                <h4 class="widget-title lighter smaller">后台菜单管理</h4>
                                &nbsp;&nbsp;
                                <span class="glyphicon glyphicon-repeat" style="cursor:pointer;" id="updateActionBtn" title="更新系统动作">
                                </span>
                            </div>
                            <div class="widget-body">
                                <div class="widget-main padding-8 tree tree-unselectable">
                                    <div class="zmenuTreeBackground col-xs-12 left">
                                        <ul id="menuTree" class="ztree">
                                        </ul>
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
    var updatesysactUrl = "{{updatesysactUrl}}";
</script>
<?php $otherJsCont = <<<EOT
<script>
    $(function($) {
        function beforeDrag(treeId, treeNodes) {
            return false;
        }
        function beforeEditName(treeId, treeNode) {
            var zTree = $.fn.zTree.getZTreeObj("menuTree");
            zTree.selectNode(treeNode);

            currentNode = treeNode;
            openLayer(editUrl, treeNode.id, treeNode.pId, '编辑菜单');
            return false;
        }
        function beforeRemove(treeId, treeNode) {
            var zTree = $.fn.zTree.getZTreeObj("menuTree");
            zTree.selectNode(treeNode);
            layer.confirm("确认删除该菜单吗？", {icon: 3, title:'提示'}, function(index){
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
                    openLayer(editUrl, 0, treeNode.id, '新增菜单');
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
                position: 'absolute',
            });
            return indexL;
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
                onRename: onRename
            }
        };

        $.getJSON(listUrl, function (res) {
            zNodes = res.data;
            zNodes.unshift({id:0, pId:-1, name:"根菜单", open:true, isParent:true});
            zTreeObj = $.fn.zTree.init($("#menuTree"), setting, zNodes);
        });

    });
    
    //更新系统动作
    var updating = false;
    $('#updateActionBtn').click(function () {
        if(updating) return false;
        updating = true;
        var _this = $(this);
        _this.addClass('blue');
        $.getJSON(updatesysactUrl, function (res) {
            updating = false;
            layer.alert(res.msg);
            _this.removeClass('blue');
        });
    });

</script>
EOT;
?>
{{ partial("common/footer", ['FOOT_OTH_CONT': otherJsCont]) }}