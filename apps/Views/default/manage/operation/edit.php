<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/7/1
 * Time: 20:04
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
                            <input type="hidden" value="{{mid}}" name="mid" >
                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="sort">状态</label>
                                <div class="col-sm-8 col-xs-7">
                                    <select class="form-control m-b" id="status" name="status">
                                        <option value="1" title="启用" {% if curInfo and curInfo.status == 1 %} selected {% endif %}>启用</option>
                                        <option value="0" title="停用" {% if curInfo and curInfo.status == 0 %} selected {% endif %}>停用</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="title">操作名称</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="text" class="input-large" name="name" id="name" value="{% if curInfo %}{{curInfo.name}}{% endif %}" maxlength="50">
                                    <span class="middle text-danger">*</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="tag">操作标识</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="text" class="input-large" name="tag" id="tag" value="{% if curInfo %}{{curInfo.tag}}{% endif %}" placeholder="英文和下划线,不区分大小写" maxlength="50">
                                    <span class="middle text-danger">*</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="desc">说明</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="text" class="input-large" name="desc" id="desc" value="{% if curInfo %}{{curInfo.desc}}{% endif %}" maxlength="50">
                                    <span class="middle text-danger"></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="sort">排序</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="text" class="input-large" name="sort" id="sort" value="{% if curInfo %}{{curInfo.sort}}{% endif %}" placeholder="0" maxlength="5">
                                    <span class="middle text-muted">按ASC</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-xs-5">
                                    <select id="multi_d" class="form-control" size="5" multiple="multiple">
                                        {% if allActions %}
                                        {% for action in allActions %}
                                        <option value="{{action.ac_id}}" title="{{action.url}}">
                                            {{action.title}}
                                        </option>
                                        {% endfor %}
                                        {% endif %}
                                    </select>
                                </div>

                                <div class="col-xs-2">
                                    <button type="button" id="multi_d_rightAll" class="btn btn-default btn-block"><i class="glyphicon glyphicon-forward"></i></button>
                                    <button type="button" id="multi_d_rightSelected" class="btn btn-default btn-block"><i class="glyphicon glyphicon-chevron-right"></i></button>
                                    <button type="button" id="multi_d_leftSelected" class="btn btn-default btn-block"><i class="glyphicon glyphicon-chevron-left"></i></button>
                                    <button type="button" id="multi_d_leftAll" class="btn btn-default btn-block"><i class="glyphicon glyphicon-backward"></i></button>
                                </div>

                                <div class="col-xs-5">
                                    <b>请从左侧选择该操作包含的动作</b>
                                    <select name="new_aid[]" id="multi_d_to" class="form-control" size="5" multiple="multiple">
                                        {% if selActions %}
                                        {% for action in selActions %}
                                        <option value="{{action.ac_id}}" title="{{action.url}}">
                                            {{action.title}}
                                        </option>
                                        {% endfor %}
                                        {% endif %}
                                    </select>
                                </div>
                            </div>

                            <div class="row" style="min-height: 25px;"></div>
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
    var curInfoId = parseInt("{{id}}");
</script>
<?php $otherJsCont = <<<EOT
EOT;
?>
{{ partial("common/footer", ['FOOT_OTH_CONT': otherJsCont]) }}
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
                tag : {
                    required : true,
                    isWord : true,
                    maxlength : 30
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
                name : {
                    required : e + "请输入操作名称",
                    maxlength : e + "限制25字符以内"
                },
                tag : {
                    required : e + "请输入模块标识",
                    isWord : e + "只能输入英文、数字和下划线",
                    maxlength : e + "限制30字符以内"
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
                        var curInfo = res.data;
                        layer.alert(res.msg, {icon:1}, function(){
                            var index = parent.layer.getFrameIndex(window.name);
                            parent.getOperaLis();
                            parent.layer.close(index);
                        });
                    }else{
                        layer.alert(res.msg, {icon:2});
                        return false;
                    }
                }, 'json');

                return false;
            }
        });

        //多选
        $('#multi_d').multiselect({
            right: '#multi_d_to, #multi_d_to_2',
            rightSelected: '#multi_d_rightSelected, #multi_d_rightSelected_2',
            leftSelected: '#multi_d_leftSelected, #multi_d_leftSelected_2',
            rightAll: '#multi_d_rightAll, #multi_d_rightAll_2',
            leftAll: '#multi_d_leftAll, #multi_d_leftAll_2',

            search: {
                left: '<input type="text" class="form-control" placeholder="查找..." />'
            },

            moveToRight: function(Multiselect, vOptions, event, silent, skipStack) {
                var button = $(event.currentTarget).attr('id');

                if (button == 'multi_d_rightSelected') {
                    var vleft_options = Multiselect.$left.find('> option:selected');
                    Multiselect.$right.eq(0).append(vleft_options);

                    if ( typeof Multiselect.callbacks.sort == 'function' && !silent ) {
                        Multiselect.$right.eq(0).find('> option').sort(Multiselect.callbacks.sort).appendTo(Multiselect.$right.eq(0));
                    }
                } else if (button == 'multi_d_rightAll') {
                    var vleft_options = Multiselect.$left.children(':visible');
                    Multiselect.$right.eq(0).append(vleft_options);

                    if ( typeof Multiselect.callbacks.sort == 'function' && !silent ) {
                        Multiselect.$right.eq(0).find('> option').sort(Multiselect.callbacks.sort).appendTo(Multiselect.$right.eq(0));
                    }
                }
            },

            moveToLeft: function(Multiselect, vOptions, event, silent, skipStack) {
                var button = $(event.currentTarget).attr('id');

                if (button == 'multi_d_leftSelected') {
                    var $right_options = Multiselect.$right.eq(0).find('> option:selected');
                    Multiselect.$left.append($right_options);

                    if ( typeof Multiselect.callbacks.sort == 'function' && !silent ) {
                        Multiselect.$left.find('> option').sort(Multiselect.callbacks.sort).appendTo(Multiselect.$left);
                    }
                } else if (button == 'multi_d_leftAll') {
                    var $right_options = Multiselect.$right.eq(0).children(':visible');
                    Multiselect.$left.append($right_options);

                    if ( typeof Multiselect.callbacks.sort == 'function' && !silent ) {
                        Multiselect.$left.find('> option').sort(Multiselect.callbacks.sort).appendTo(Multiselect.$left);
                    }
                }
            }
        });

    });
</script>