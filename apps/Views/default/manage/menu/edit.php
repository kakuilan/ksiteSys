<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/5/30
 * Time: 19:12
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
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="parent">父级菜单ID</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="text" class="input-large" name="parent" id="parent" value="{{parent}}" maxlength="25">
                                    <span class="middle text-danger">*</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="action_id">动作</label>
                                <div class="col-sm-8 col-xs-7">
                                    <select class="form-control m-b" id="action_id" name="action_id">
                                        <option value="0" title="父菜单-无动作">无动作</option>
                                        {% if allActions %}
                                        {% for action in allActions %}
                                        <option value="{{action.ac_id}}" {% if action.ac_id === action_id %} selected {% endif %} title="{{action.url}}" >
                                            {{action.title}}
                                        </option>
                                        {% endfor %}
                                        {% endif %}
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="sort">状态</label>
                                <div class="col-sm-8 col-xs-7">
                                    <select class="form-control m-b" id="status" name="status">
                                        <option value="1" title="启用" {% if menu and menu.status == 1 %} selected {% endif %}>启用</option>
                                        <option value="0" title="停用" {% if menu and menu.status == 0 %} selected {% endif %}>停用</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="title">菜单名称</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="text" class="input-large" name="title" id="title" value="{% if menu %}{{menu.title}}{% endif %}" maxlength="50">
                                    <span class="middle text-danger">*</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="tag">样式tag</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="text" class="input-large" name="tag" id="tag" value="{% if menu %}{{menu.tag}}{% endif %}" placeholder="fa-list" maxlength="50">
                                    <span class="middle text-danger"></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="sort">排序</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="text" class="input-large" name="sort" id="sort" value="{% if menu %}{{menu.sort}}{% endif %}" placeholder="0" maxlength="5">
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
    var menuId = parseInt("{{id}}");
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
                action_id : {
                    required : true,
                    maxlength : 10
                },
                title : {
                    required : true,
                    maxlength : 25
                },
                tag : {
                    maxlength : 30
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
                action_id : {
                    number : e + "请填写数字",
                    maxlength : e + "限制10字符以内"
                },
                title : {
                    required : e + "请输入菜单名称",
                    maxlength : e + "限制25字符以内"
                },
                tag : {
                    maxlength : e + "限制30字符以内"
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
                        var menu = res.data;
                        layer.alert(res.msg, {icon:1},function(){
                            if(window.top==window.self){
                                location.href = listUrl;
                            }else{//从父级页面打开
                                var index = parent.layer.getFrameIndex(window.name);
                                var zTree = parent.$.fn.zTree.getZTreeObj("menuTree");

                                if(menuId==0) {
                                    zTree.addNodes(parent.currentNode, {id:menu.id, pId:menu.parent_id, name:menu.title});
                                }else{
                                    if(oldParent==menu.parent_id) {
                                        parent.currentNode.name = menu.title;
                                        zTree.updateNode(parent.currentNode);
                                    }else{
                                        var parentNode = zTree.getNodeByParam('id', menu.parent_id, null);
                                        zTree.moveNode(parentNode, parent.currentNode, 'inner', false);
                                    }
                                }
                                parent.layer.close(index);
                            }
                        });
                    }else{
                        layer.alert(res.msg,{icon:2});
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
