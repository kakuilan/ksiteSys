<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/12/27
 * Time: 15:45
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
                                    <span class="middle text-danger">*</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="email">邮箱</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="text" class="input-large" name="email" id="email" value="{% if info %}{{info.email}}{% endif %}" maxlength="25">
                                    <span class="middle text-danger">*</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="password">前台密码</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="password" class="input-large" name="frontPassword" id="frontPassword" value="" maxlength="25">
                                    <span class="middle text-danger"></span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="passwordCfr">确认前台密码</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="password" class="input-large" name="frontPassword2" id="frontPassword2" value="" maxlength="25">
                                    <span class="middle text-danger"></span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="status">前台状态</label>
                                <div class="col-sm-6 col-xs-5">
                                    <select class="form-control m-b" id="user_status" name="user_status">
                                        {% if userStatusArr %}
                                        {% for vue,name in userStatusArr %}
                                        <option value="{{vue}}" {% if info and info.user_status==vue %} selected {% endif %} title="{{name}}" >
                                            {{name}}
                                        </option>
                                        {% endfor %}
                                        {% endif %}
                                    </select>
                                </div>
                            </div>


                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="password">后台密码</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="password" class="input-large" name="backPassword" id="backPassword" value="" maxlength="25">
                                    <span class="middle text-danger"></span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="passwordCfr">确认后台密码</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="password" class="input-large" name="backPassword2" id="backPassword2" value="" maxlength="25">
                                    <span class="middle text-danger"></span>
                                </div>
                            </div>


                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="email_status">管理员级别</label>
                                <div class="col-sm-6 col-xs-5">
                                    <select class="form-control m-b" id="level" name="level">
                                        {% if levelArr %}
                                        {% for vue,name in levelArr %}
                                        <option value="{{vue}}" {% if info and info.level==vue %} selected {% endif %} title="{{name}}" >
                                            {{name}}
                                        </option>
                                        {% endfor %}
                                        {% endif %}
                                    </select>
                                </div>
                            </div>


                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="status">后台状态</label>
                                <div class="col-sm-6 col-xs-5">
                                    <select class="form-control m-b" id="status" name="status">
                                        {% if statusArr %}
                                        {% for vue,name in statusArr %}
                                        <option value="{{vue}}" {% if info and info.status==vue %} selected {% endif %} title="{{name}}" >
                                            {{name}}
                                        </option>
                                        {% endfor %}
                                        {% endif %}
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="type">用户类型</label>
                                <div class="col-sm-6 col-xs-5">
                                    <select class="form-control m-b" id="user_type" name="user_type" readonly="readonly" onfocus="this.defOpt=this.selectedIndex" onchange="this.selectedIndex=this.defOpt;">
                                        {% if userTypesArr %}
                                        {% for vue,name in userTypesArr %}
                                        <option value="{{vue}}" {% if userTypeAdm==vue %} selected {% endif %} title="{{name}}" >
                                            {{name}}
                                        </option>
                                        {% endfor %}
                                        {% endif %}
                                    </select>
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
                username : {
                    required : true,
                    isUsrname : true,
                    rangelength : [5, 30]
                },
                email : {
                    required : true,
                    email : true,
                    rangelength : [5, 30]
                },
                frontPassword : {
                    required : (uid ? false : true),
                    isPwd : true,
                    rangelength : [5, 32]
                },
                frontPassword2 : {
                    equalTo: "#frontPassword"
                },
                backPassword : {
                    required : (uid ? false : true),
                    isPwd : true,
                    rangelength : [5, 32]
                },
                backPassword2 : {
                    equalTo: "#backPassword"
                }

            },
            messages : {
                username : {
                    required : e + "请输入用户名",
                    isUsrname : e + "只能是英文、数字、点和下划线",
                    rangelength : e + "限制{0}~{1}字符以内"
                },
                email : {
                    required : e + "请输入邮箱",
                    email : e + "无效的邮箱",
                    rangelength : e + "限制{0}~{1}字符以内"
                },
                frontPassword : {
                    required : e + "请输入前台密码",
                    isPwd : e + "只能是英文、数字和特殊字符",
                    rangelength : e + "限制{0}~{1}字符以内"
                },
                frontPassword2 : {
                    equalTo: e + "2次密码不相同"
                },
                backPassword : {
                    required : e + "请输入后台密码",
                    isPwd : e + "只能是英文、数字和特殊字符",
                    rangelength : e + "限制{0}~{1}字符以内"
                },
                backPassword2 : {
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
                
                if(/^\d+$/.test(sendData.frontPassword) || /^\d+$/.test(sendData.backPassword)) {
                    layer.alert('密码不能全为数字');
                    return false;
                }
                if(sendData.frontPassword.length>0) {
                    sendData.frontPassword = md5(sendData.frontPassword);
                    sendData.frontPassword2 = md5(sendData.frontPassword2);
                }
                if(sendData.backPassword.length>0) {
                    sendData.backPassword = md5(sendData.backPassword);
                    sendData.backPassword2 = md5(sendData.backPassword2);
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