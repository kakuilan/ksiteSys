<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/6/26
 * Time: 21:37
 * Desc: -
 */


?>
{{ partial("common/header", ['BDCLASS':'','BDSTYLE':'']) }}

<!--主体容器 start-->
<div class="main-container" id="main-container">
    <!--主体内容 start-->
    <div class="main-content">
        <!--页面内容 start-->
        <div class="page-content">
            <!--页面区域 start-->
            <div class="page-content-area">
                <div class="row">
                    <div class="col-xs-12">
                        <form method="post" class="form-horizontal" id="myForm" role="form">
                            <input type="hidden" value="{{id}}" name="id" >
                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="parent">父级模块ID</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="text" class="input-large" name="parent" id="parent" value="{{parent}}" maxlength="25">
                                    <span class="middle text-danger">*</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="sort">状态</label>
                                <div class="col-sm-8 col-xs-7">
                                    <select class="form-control m-b" id="status" name="status">
                                        <option value="1" title="启用" {% if module and module.status == 1 %} selected {% endif %}>启用</option>
                                        <option value="0" title="停用" {% if module and module.status == 0 %} selected {% endif %}>停用</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="title">模块名称</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="text" class="input-large" name="name" id="name" value="{% if module %}{{module.name}}{% endif %}" maxlength="50">
                                    <span class="middle text-danger">*</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="desc">说明</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="text" class="input-large" name="desc" id="desc" value="{% if module %}{{module.desc}}{% endif %}" maxlength="50">
                                    <span class="middle text-danger"></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="sort">排序</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="text" class="input-large" name="sort" id="sort" value="{% if module %}{{module.sort}}{% endif %}" placeholder="0" maxlength="5">
                                    <span class="middle text-muted">按ASC</span>
                                </div>
                            </div>

                            <div class="hr-line-dashed"></div>
                            <div class="form-group">
                                <div class="col-sm-4 col-xs-5 col-sm-offset-2 col-xs-offset-3">
                                    <button class="btn btn-primary btn-sm" type="submit" id="submit">保存</button>
                                    <button class="btn btn-white" type="reset">重置</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!--页面区域 end-->
        </div>
        <!--页面内容 end-->
    </div>
    <!--主体内容 end-->
</div>
<!--主体容器 end-->

<script>
    var saveUrl = "{{saveUrl}}";
    var listUrl = "{{listUrl}}";
    var moduleId = parseInt("{{id}}");
    var oldParent = parseInt("{{parent}}");
</script>
<?php $otherJsCont = <<<EOT
<script>
    $(function($) {
        //验证
        var e = "<i class='fa fa-times-circle'></i> ";
        $("#myForm").validate({
            rules : {
                parent : {
                    required : true,
                    number : true,
                    maxlength : 5
                },
                name : {
                    required : true,
                    maxlength : 25
                },
                desc : {
                    maxlength : 100
                },
                sort : {
                    number : true,
                    maxlength : 5
                }
            },
            messages : {
                parent : {
                    number : e + "请填写数字",
                    maxlength : e + "限制5字符以内"
                },
                name : {
                    required : e + "请输入模块名称",
                    maxlength : e + "限制25字符以内"
                },
                desc : {
                    maxlength : e + "限制100字符以内"
                },
                sort : {
                    number : e + "请填写数字",
                    maxlength : e + "限制5字符以内"
                }
            },
            //提交
            submitHandler : function(form){
                var sendData = $(form).serialize();
                $('#submit').attr("disabled","disabled");
                $.post(saveUrl, sendData, function(res){
                    $('#submit').removeAttr("disabled");
                    if(res.status){
                        var module = res.data;
                        layer.alert(res.msg, {icon:1}, function(){
                            if(window.top==window.self){
                                location.href = listUrl;
                            }else{//从父级页面打开
                                var index = parent.layer.getFrameIndex(window.name);
                                var zTree = parent.$.fn.zTree.getZTreeObj("treeBox");

                                if(moduleId==0) {
                                    zTree.addNodes(parent.currentNode, {id:module.id, pId:module.parent_id, name:module.name});
                                }else{
                                    if(oldParent==module.parent_id) {
                                        parent.currentNode.name = module.name;
                                        zTree.updateNode(parent.currentNode);
                                    }else{
                                        var parentNode = zTree.getNodeByParam('id', module.parent_id, null);
                                        zTree.moveNode(parentNode, parent.currentNode, 'inner', false);
                                    }
                                }
                                parent.layer.close(index);
                            }
                        });
                    }else{
                        layer.alert(res.msg, {icon:2});
                        return false;
                    }
                }, 'json');

                return false;
            }
        });
    });
</script>
EOT;
?>
{{ partial("common/footer", ['FOOT_OTH_CONT': otherJsCont]) }}