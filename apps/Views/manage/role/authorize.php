<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/7/3
 * Time: 23:05
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
                        <div class="widget-box widget-color-blue2">
                            <div class="widget-header">
                                <h4 class="widget-title lighter smaller">角色授权:[{{role.name}}]</h4>
                            </div>
                            <div class="widget-body">
                                <div class="widget-main padding-8 tree tree-unselectable">
                                    <div class="row">
                                        <div class="ztreeBoxBackground col-xs-12 left">
                                            <ul id="treeBox" class="ztree">
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            <div class="col-sm-8 col-xs-9 col-sm-offset-2 col-xs-offset-3">
                                <button class="btn btn-primary btn-sm" type="submit" id="submit">保存</button>
                                <button class="btn btn-white" type="reset" id="reset">重置</button>
                                <button class="btn btn-white" type="button" id="chkAll">全选</button>
                                <button class="btn btn-white" type="button" id="chkNoAll">清空</button>
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
    var zNodes; //节点数据
    var zTreeObj; //树形对象
    var saveUrl = "{{saveUrl}}";
    var listUrl = "{{listUrl}}";
    var treeUrl = "{{treeUrl}}";
</script>
<?php $otherJsCont = <<<EOT

EOT;
?>
{{ partial("common/footer", ['FOOT_OTH_CONT': otherJsCont]) }}
<script>
    $(function($) {

        var setting = {
            check: {
                enable: true
            },
            data: {
                simpleData: {
                    enable: true
                }
            }
        };

        $.getJSON(treeUrl, function (res) {
            zNodes = res.data;
            zNodes.unshift({id:0, pId:-1, name:"根模块", open:true, isParent:true});
            zTreeObj = $.fn.zTree.init($("#treeBox"), setting, zNodes);
        });

        $('#reset').click(function () {
            zTreeObj.destroy();
            zTreeObj = $.fn.zTree.init($("#treeBox"), setting, zNodes);
        });
        $('#chkAll').click(function () {
            zTreeObj.checkAllNodes(true);
        });
        $('#chkNoAll').click(function () {
            zTreeObj.checkAllNodes(false);
        });

        //保存
        $('#submit').click(function () {
            var nodes = zTreeObj.getCheckedNodes(true),item = null;
            if(nodes.length==0) {
                layer.alert('请选择该角色可进行的操作', {icon:2});
                return false;
            }

            var op_ids = [];
            for(i in nodes) {
                item = nodes[i];
                if(item.type!=undefined && item.type=='operation') {
                    op_ids.push(item.operationId);
                }
            }

            $.post(saveUrl, {op_ids:op_ids}, function (res) {
                if(res.status) {
                    layer.alert(res.msg, {icon:1},function(){
                        if(window.top==window.self){
                            location.href = listUrl;
                        }else{//从父级页面打开
                            var index = parent.layer.getFrameIndex(window.name);
                            parent.layer.close(index);
                        }
                    });
                }else{
                    layer.alert(res.msg, {icon:2});
                }
            });
        });
    });



</script>