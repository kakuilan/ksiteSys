<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/12/16
 * Time: 18:28
 * Desc:
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
                            <input type="hidden" name="uid" value="{{uid}}" />

                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="username">用户名</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="text" class="input-large" name="username" id="username" value="{% if info %}{{info.username}}{% endif %}" maxlength="25" {% if info %}readonly disabled{% endif %}>
                                    <span class="middle text-danger"></span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="password">密码</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="password" class="input-large" name="password" id="password" value="" maxlength="25">
                                    <span class="middle text-danger">*</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="passwordCfr">确认密码</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="password" class="input-large" name="passwordCfr" id="passwordCfr" value="" maxlength="25">
                                    <span class="middle text-danger">*</span>
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
    var uid = parseInt("{{uid}}");
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
                password : {
                    required : true,
                    isPwd : true,
                    rangelength : [5, 32]
                },
                passwordCfr : {
                    equalTo: "#password"
                }

            },
            messages : {
                password : {
                    required : e + "请输入密码",
                    isPwd : e + "只能是英文、数字和特殊字符",
                    rangelength : e + "限制{0}~{1}字符以内"
                },
                passwordCfr : {
                    equalTo: e + "2次密码不相同"
                }

            },
            //提交
            submitHandler : function(form){
                var formdata = $(form).serializeArray();
                var sendData = {};
                $(formdata).each(function(index, obj){
                    sendData[obj.name] = obj.value;
                });
                if(sendData.password.length>0) {
                    if(/^\d+$/.test(sendData.password)) {
                        layer.alert('密码不能全为数字');
                        return false;
                    }
                
                    sendData.password = md5(sendData.password);
                    sendData.passwordCfr = md5(sendData.passwordCfr);
                }
                $('#submit').attr("disabled","disabled");
                $.post(saveUrl, sendData, function(res){
                    $('#submit').removeAttr("disabled");
                    if(res.msg==null) res.msg = 'null';
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