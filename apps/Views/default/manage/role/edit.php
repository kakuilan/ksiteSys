<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/6/3
 * Time: 17:48
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
                        <form method="post" class="form-horizontal" id="myForm" role="form">
                            <input type="hidden" name="id" value="{{id}}" />
                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="name">角色名称</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="text" class="input-large" name="name" id="name" value="{% if role %}{{role.name}}{% endif %}" maxlength="25">
                                    <span class="middle text-danger">*</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="sort">状态</label>
                                <div class="col-sm-8 col-xs-7">
                                    <select class="form-control m-b" id="status" name="status">
                                        <option value="1" title="启用" {% if role and role.status == 1 %} selected {% endif %}>启用</option>
                                        <option value="0" title="停用" {% if role and role.status == 0 %} selected {% endif %}>停用</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="desc">角色说明</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="text" class="input-large" name="desc" id="desc" value="{% if role %}{{role.desc}}{% endif %}" maxlength="50">
                                    <span class="middle text-danger"></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="sort">排序</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="text" class="input-large" name="sort" id="sort" value="{% if role %}{{role.sort}}{% endif %}" maxlength="5">
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
    var saveUrl = "{{saveUrl}}";
    var listUrl = "{{listUrl}}";
</script>
<?php $otherJsCont = <<<EOT
<script>
    $(function($) {
        //验证
        var e = "<i class='fa fa-times-circle'></i> ";
        $("#myForm").validate({
            rules : {
                name : {
                    required : true,
                    maxlength : 25
                },
                desc : {
                    maxlength : 50
                },
                sort : {
                    number : true,
                    maxlength : 5
                }
            },
            messages : {
                name : {
                    required : e + "请输入角色名称",
                    maxlength : e + "限制25字符以内"
                },
                desc : {
                    maxlength : e + "限制50字符以内"
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
                        layer.alert(res.msg, function(){
                            if(window.top==window.self){
                                location.href = listUrl;
                            }else{//从父级页面打开
                                var index = parent.layer.getFrameIndex(window.name);
                                //刷新父级页面的JqGrid表格
                                parent.refreshJqGrid('#grid-table', {page:1, postData:{}});
                                parent.layer.close(index);
                            }
                        });
                    }else{
                        layer.alert(res.msg);
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