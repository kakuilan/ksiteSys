<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/4/30
 * Time: 17:29
 * Desc: -后台公共头部模板
 */
 
 
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <meta name="renderer" content="webkit">
    <title>{{headerSeo.title}}</title>
    <meta name="keywords" content="{{headerSeo.keywords}}">
    <meta name="description" content="{{headerSeo.desc}}">
    <link rel="shortcut icon" href="/favicon.ico">

    <!-- bootstrap & fontawesome -->
    <link rel="stylesheet" href="/statics/css/bootstrap.min.css" />
    <link rel="stylesheet" href="/statics/css/font-awesome.min.css" />

    <!-- page specific plugin styles -->
    {{ assets.outputCss() }}

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

    <link rel="stylesheet" href="/statics/css/adm-comm.css" />

    {% if HEAD_OTH_CONT is defined %}
    {{HEAD_OTH_CONT}}
    {% endif %}
</head>

<body class="{% if BDCLASS is defined %}{{BDCLASS}}{% endif %}" style="{% if BDSTYLE is defined %}{{BDSTYLE}}{% endif %}">
