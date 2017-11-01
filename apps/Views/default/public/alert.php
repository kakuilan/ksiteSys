<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/5/1
 * Time: 14:34
 * Desc: -
 */
 
 
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <meta name="renderer" content="webkit">
    <title>{{sitetitle}}</title>
    <meta name="keywords" content="{{keywords}}">
    <meta name="description" content="{{desc}}">
    <link rel="shortcut icon" href="/favicon.ico">

    <!-- bootstrap & fontawesome -->
    <link rel="stylesheet" href="/statics/css/bootstrap.min.css" />
    <link rel="stylesheet" href="/statics/css/font-awesome.min.css" />

    <!-- page specific plugin styles -->

    <!-- text fonts -->
    <link rel="stylesheet" href="/statics/css/ace-fonts.min.css" />

    <!-- ace styles -->
    <link rel="stylesheet" href="/statics/css/ace.min.css" class="ace-main-stylesheet" id="main-ace-style" />

    <!--[if lte IE 9]>
    <link rel="stylesheet" href="/statics/css/ace-part2.min.css" class="ace-main-stylesheet" />
    <![endif]-->
    <link rel="stylesheet" href="/statics/css/ace-skins.min.css" />
    <link rel="stylesheet" href="/statics/css/ace-rtl.min.css" />

    <!--[if lte IE 9]>
    <link rel="stylesheet" href="/statics/css/ace-ie.min.css" />
    <![endif]-->

    <!-- inline styles related to this page -->

    <!-- ace settings handler -->
    <script src="/statics/js/ace-extra.min.js"></script>

    <!-- HTML5shiv and Respond.js for IE8 to support HTML5 elements and media queries -->

    <!--[if lte IE 8]>
    <script src="/statics/js/html5shiv.min.js"></script>
    <script src="/statics/js/respond.min.js"></script>
    <![endif]-->
</head>
<body>
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
                        <div class="alert alert-{{type}}" role="alert">
                            <h1>{{title}}</h1>
                            <p style="font-size:18px;">
                            {{msg}}
                            </p>

                            {% if url %}
                            <p style="font-size:18px;">
                                <span id="redTime" class="text-danger">{{time}}</span>
                                秒后自动跳转，或点击
                                <a href="{{url}}" class="alert-link">这里</a>！
                            </p>
                            {% endif %}
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



<!-- basic scripts -->

<!--[if !IE]> -->
<script src="/statics/js/jquery-2.1.4.min.js"></script>
<!-- <![endif]-->

<!--[if IE]>
<script src="/statics/js/jquery-1.11.3.min.js"></script>
<![endif]-->

<script type="text/javascript">
    if('ontouchstart' in document.documentElement) document.write("<script src='/statics/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
</script>
<script src="/statics/js/bootstrap.min.js"></script>

<!-- page specific plugin scripts -->

<!--[if lte IE 8]>
<script src="/statics/js/excanvas.min.js"></script>
<![endif]-->
<script src="/statics/js/jquery-ui.custom.min.js"></script>
<script src="/statics/js/jquery.ui.touch-punch.min.js"></script>


<!-- ace scripts -->
<script src="/statics/js/ace-elements.min.js"></script>
<script src="/statics/js/ace.min.js"></script>

<!-- inline scripts related to this page -->
<script type="text/javascript">
    jQuery(function($) {
        /(iPhone|iPad|iPod|iOS)/i.test(navigator.userAgent) && $("#main-container").css("overflow-y", "auto");

        var url = "{{url}}";
        var time = parseInt("{{time}}");

        if(url !=''){
            setTimeout(function(){
                location.href = url;
            }, time * 1000);

            setInterval(function(){
                var t = parseInt($('#redTime').text());
                if(t >0){
                    t--;
                    $('#redTime').text(t);
                }
            }, 1000);
        }
    })
</script>

</body>
</html>