<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/4/30
 * Time: 18:19
 * Desc: -
 */


?>
{{ partial("common/header", ['BDCLASS':'no-skin full-height-layout','BDSTYLE':'overflow: hidden;']) }}

<!-- #section:basics/navbar.layout -->
<div id="navbar" class="navbar navbar-default ace-save-state">
    <div class="navbar-container ace-save-state" id="navbar-container">
        <!-- #section:basics/sidebar.mobile.toggle -->
        <button type="button" class="navbar-toggle menu-toggler pull-left" id="menu-toggler" data-target="#sidebar">
            <span class="sr-only">Toggle sidebar</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>

        <!-- /section:basics/sidebar.mobile.toggle -->
        <div class="navbar-header pull-left">
            <!-- #section:basics/navbar.layout.brand -->
            <a href="#" class="navbar-brand">
                <small>
                    <i class="fa fa-leaf"></i>
                    Ace Admin
                </small>
            </a>
            <!-- /section:basics/navbar.layout.brand -->
            <!-- #section:basics/navbar.toggle -->
            <!-- /section:basics/navbar.toggle -->
        </div>

        <!-- #section:basics/navbar.dropdown -->
        <div class="navbar-buttons navbar-header pull-right" role="navigation">
            <ul class="nav ace-nav">
                <li class="purple">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="ace-icon fa fa-bell icon-animated-bell"></i>
                        <span class="badge badge-important">8</span>
                    </a>
                    <ul class="dropdown-menu-right dropdown-navbar navbar-pink dropdown-menu dropdown-caret dropdown-close">
                        <li class="dropdown-header">
                            <i class="ace-icon fa fa-exclamation-triangle"></i>
                            8 提醒
                        </li>
                    </ul>
                </li>

                <li class="green">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="ace-icon fa fa-envelope icon-animated-vertical"></i>
                        <span class="badge badge-success">5</span>
                    </a>

                    <ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
                        <li class="dropdown-header">
                            <i class="ace-icon fa fa-envelope-o"></i>
                            5 消息
                        </li>
                        <li class="dropdown-content ace-scroll" style="position: relative;">
                            <div class="scroll-track" style="display: block; height: 200px;">
                                <div class="scroll-bar" style="height: 111px; top: 0px;"></div>
                            </div>
                            <div class="scroll-content" style="max-height: 200px;"></div>
                        </li>
                    </ul>
                </li>

                <li class="light-blue">
                    <a data-toggle="dropdown" href="#" class="dropdown-toggle">
                        <img class="nav-user-photo" src="/statics/avatars/avatar.png" alt="Jason's Photo">
                        <span class="user-info">
									<small>欢迎,</small>
									Jason
								</span>

                        <i class="ace-icon fa fa-caret-down"></i>
                    </a>

                    <ul class="user-menu dropdown-menu-right dropdown-menu dropdown-yellow dropdown-caret dropdown-close">
                        <li>
                            <a href="#">
                                <i class="ace-icon fa fa-cog"></i>
                                设置
                            </a>
                        </li>

                        <li>
                            <a href="profile.html">
                                <i class="ace-icon fa fa-user"></i>
                                个人资料
                            </a>
                        </li>

                        <li class="divider"></li>

                        <li>
                            <a href="#">
                                <i class="ace-icon fa fa-power-off"></i>
                                退出
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>

    </div>
</div>
<!-- /section:basics/navbar.layout -->

<!--主容器 start-->
<div class="main-container ace-save-state" id="main-container">
    <script type="text/javascript">
        try{ace.settings.loadState('main-container')}catch(e){}
    </script>

    <!-- #section:basics/sidebar -->
    <div id="sidebar" class="sidebar responsive ace-save-state">
        <script type="text/javascript">
            try{ace.settings.loadState('sidebar')}catch(e){}
        </script>

        <!--sidebar-shortcuts start-->
        <div class="sidebar-shortcuts" id="sidebar-shortcuts">
            <div class="sidebar-shortcuts-large" id="sidebar-shortcuts-large">
                <button class="btn btn-success">
                    <i class="ace-icon fa fa-signal"></i>
                </button>
                <button class="btn btn-info">
                    <i class="ace-icon fa fa-pencil"></i>
                </button>
                <!-- #section:basics/sidebar.layout.shortcuts -->
                <button class="btn btn-warning">
                    <i class="ace-icon fa fa-users"></i>
                </button>
                <button class="btn btn-danger">
                    <i class="ace-icon fa fa-cogs"></i>
                </button>
                <!-- /section:basics/sidebar.layout.shortcuts -->
            </div>

            <div class="sidebar-shortcuts-mini" id="sidebar-shortcuts-mini">
                <span class="btn btn-success"></span>
                <span class="btn btn-info"></span>
                <span class="btn btn-warning"></span>
                <span class="btn btn-danger"></span>
            </div>
        </div>
        <!--sidebar-shortcuts end-->

        <!--左侧菜单 start-->
        <ul class="nav nav-list" id="menu">

        </ul>
        <!--左侧菜单 end-->

        <!-- #section:basics/sidebar.layout.minimize -->
        <div class="sidebar-toggle sidebar-collapse" id="sidebar-collapse">
            <i id="sidebar-toggle-icon" class="ace-icon fa fa-angle-double-left ace-save-state" data-icon1="ace-icon fa fa-angle-double-left" data-icon2="ace-icon fa fa-angle-double-right"></i>
        </div>
        <!-- /section:basics/sidebar.layout.minimize -->

    </div>
    <!-- /section:basics/sidebar -->

    <!-- 右侧容器 start -->
    <div class="main-content">
        <div class="main-content-inner">
            <!--tab容器 start-->
            <!-- #section:basics/content.breadcrumbs -->
            <div class="breadcrumbs ace-save-state content-tabs" id="breadcrumbs">
                <button class="roll-nav roll-left lkk_tabLeft"><i class="fa fa-backward"></i>
                </button>

                <nav class="page-tabs lkk_menuTabs">
                    <div class="page-tabs-content">
                        <a href="javascript:;" class="active lkk_menuTab" data-id="0">首页</a>
                    </div>
                </nav>

                <button class="roll-nav roll-right lkk_tabRight"><i class="fa fa-forward"></i>
                </button>

                <div class="btn-group roll-nav roll-right">
                    <button class="dropdown lkk_tabClose" data-toggle="dropdown">
                        关闭操作
                        <span class="caret"></span>
                    </button>
                    <ul role="menu" class="dropdown-menu dropdown-menu-right">
                        <li class="lkk_tabCloseOther"><a>关闭其他选项卡</a>
                        </li>
                        <li class="lkk_tabCloseAll"><a>关闭全部选项卡</a>
                        </li>
                        <li class="lkk_tabShowActive"><a>定位当前选项卡</a>
                        </li>
                    </ul>
                </div>
                <a href="#" class="roll-nav roll-right lkk_tabExit"><i class="fa fa fa-sign-out"></i> 退出</a>
            </div>
            <!--tab容器 end-->

            <!--页面内容 start-->
            <!-- /section:basics/content.breadcrumbs -->
            <div class="page-content">
                <!--设置 start-->
                <!-- #section:settings.box -->
                <!-- #section:settings.box -->
                <div class="ace-settings-container" id="ace-settings-container">
                    <div class="btn btn-app btn-xs btn-warning ace-settings-btn" id="ace-settings-btn">
                        <i class="ace-icon fa fa-cog bigger-130"></i>
                    </div>

                    <div class="ace-settings-box clearfix" id="ace-settings-box">
                        <div class="pull-left width-50">
                            <!-- #section:settings.skins -->
                            <div class="ace-settings-item">
                                <div class="pull-left">
                                    <select id="skin-colorpicker" class="hide">
                                        <option data-skin="no-skin" value="#438EB9">#438EB9</option>
                                        <option data-skin="skin-1" value="#222A2D">#222A2D</option>
                                        <option data-skin="skin-2" value="#C6487E">#C6487E</option>
                                        <option data-skin="skin-3" value="#D0D0D0">#D0D0D0</option>
                                    </select>
                                </div>
                                <span>&nbsp; 选择皮肤</span>
                            </div>
                            <!-- /section:settings.skins -->

                            <!-- #section:settings.rtl -->
                            <div class="ace-settings-item">
                                <input type="checkbox" class="ace ace-checkbox-2" id="ace-settings-rtl" autocomplete="off" />
                                <label class="lbl" for="ace-settings-rtl"> 左右切换</label>
                            </div>

                            <!-- /section:settings.rtl -->

                            <!-- #section:settings.container -->
                            <div class="ace-settings-item">
                                <input type="checkbox" class="ace ace-checkbox-2 ace-save-state" id="ace-settings-add-container" autocomplete="off" />
                                <label class="lbl" for="ace-settings-add-container">
                                    切换窄屏
                                </label>
                            </div>

                            <!-- /section:settings.container -->
                        </div><!-- /.pull-left -->

                        <div class="pull-left width-50">
                            <!-- #section:basics/sidebar.options -->
                            <div class="ace-settings-item">
                                <input type="checkbox" class="ace ace-checkbox-2" id="ace-settings-hover" autocomplete="off" />
                                <label class="lbl" for="ace-settings-hover"> 滑过显示子菜单</label>
                            </div>

                            <div class="ace-settings-item">
                                <input type="checkbox" class="ace ace-checkbox-2" id="ace-settings-compact" autocomplete="off" />
                                <label class="lbl" for="ace-settings-compact"> 压缩侧栏</label>
                            </div>

                            <div class="ace-settings-item">
                                <input type="checkbox" class="ace ace-checkbox-2" id="ace-settings-highlight" autocomplete="off" />
                                <label class="lbl" for="ace-settings-highlight"> 当前菜单</label>
                            </div>

                            <!-- /section:basics/sidebar.options -->
                        </div><!-- /.pull-left -->
                    </div><!-- /.ace-settings-box -->
                </div>
                <!-- /.ace-settings-container -->
                <!-- /section:settings.box -->
                <!--设置 end-->

                <!--正文容器 start-->
                <div class="row lkk_mainContent" id="content-main">
                    <iframe class="lkk_iframe" name="iframe0" width="100%" height="100%" src="{{mainUrl}}" frameborder="0" data-id="0" seamless></iframe>
                </div><!-- /.row -->
                <!--正文容器 end-->
            </div>
            <!-- /.page-content -->
            <!--页面内容 end-->

        </div>
    </div>
    <!-- 右侧容器 end -->
</div>
<!--主容器 end-->


<script>
    var menuUrl = "{{menuUrl}}";
</script>
<?php $otherJsCont = <<<EOT
<script type="text/javascript">
    jQuery(function($) {
        $.getJSON(menuUrl, function (res) {
            var menuData = res.data;
            $('#menu').lkkTabMenu({data: menuData, idField:'id', parentField:'parent', sortField: 'sort'});
        });
    })
</script>
EOT;
?>
{{ partial("common/footer", ['FOOT_OTH_CONT': otherJsCont]) }}