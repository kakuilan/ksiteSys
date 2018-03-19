<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta charset="UTF-8">
    <title>{{headerSeo.title}}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <meta name="renderer" content="webkit">

    <link rel="shortcut icon" href="/favicon.ico" />
    <!-- Loading Bootstrap -->
    <link href="/assets/css/backend.min.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements. All other JS at the end of file. -->
    <!--[if lt IE 9]>
    <script src="/assets/js/html5shiv.js"></script>
    <script src="/assets/js/respond.min.js"></script>
    <![endif]-->
    <script type="text/javascript">
        var require = {
            "config": {
                "site": {
                    "name": "Admin",
                    "cdnurl": "{{siteUrl}}",
                    "version": "1.0.0",
                    "timezone": "Asia/Shanghai",
                    "languages": {
                        "backend": "zh-cn",
                        "frontend": "zh-cn"
                    }
                },
                "upload": {
                    "cdnurl": "",
                    "uploadurl": "{{uploadUrl}}",
                    "bucket": "",
                    "maxsize": "1mb",
                    "mimetype": "*",
                    "multipart": {
                        "policy": "",
                        "signature": "",
                        "bucket": "",
                        "save-key": "",
                        "expiration": 0,
                        "notify-url": ""
                    },
                    "multiple": false
                },
                "modulename": "admin",
                "controllername": "index",
                "actionname": "index",
                "jsname": "backend/profile",
                "moduleurl": "{{siteUrl}}",
                "language": "zh-cn",
                "referer": null,
                //扩展参数
                "extparam" : {
                    "saveUrl" : "{{saveUrl}}",
                    "logsUrl" : "{{logsUrl}}"
                }
            }
        };
    </script>
</head>

<body class="inside-header inside-aside ">
<div id="main" role="main">
    <div class="tab-content tab-addtabs">
        <div id="content">
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <div class="content">
                        <style>
                            .profile-avatar-container {
                                position:relative;
                                width:100px;margin:0 auto;
                            }
                            .profile-avatar-container .profile-user-img{
                                width:100px;
                                height:100px;
                            }
                            .profile-avatar-container .profile-avatar-text {
                                display:none;
                            }
                            .profile-avatar-container:hover .profile-avatar-text {
                                display:block;
                                position:absolute;
                                height:100px;
                                width:100px;
                                background:#444;
                                opacity: .6;
                                color: #fff;
                                top:0;
                                left:0;
                                line-height: 100px;
                                text-align: center;
                            }
                            .profile-avatar-container button{
                                position:absolute;
                                top:0;left:0;width:100px;height:100px;opacity: 0;
                            }
                        </style>
                        <div class="row animated fadeInRight">
                            <div class="col-md-4">
                                <div class="box box-success">
                                    <div class="panel-heading">
                                        个人资料
                                    </div>
                                    <div class="panel-body">

                                        <form id="update-form" role="form" data-toggle="validator" method="POST" action="{{saveUrl}}">
                                            <input type="hidden" id="c-avatar" name="row[avatar]" value="/assets/img/avatar.png" />
                                            <div class="box-body box-profile">

                                                <div class="profile-avatar-container">
                                                    <img class="profile-user-img img-responsive img-circle plupload" src="/assets/img/avatar.png" alt="">
                                                    <div class="profile-avatar-text img-circle">Click to edit</div>
                                                    <button id="plupload-avatar" class="plupload" data-input-id="c-avatar" data-after-upload="changeavatar"><i class="fa fa-upload"></i> 上传</button>
                                                </div>

                                                <h3 class="profile-username text-center">{{row.username}}</h3>

                                                <p class="text-muted text-center">{{row.email}}</p>
                                                <div class="form-group">
                                                    <label for="username" class="control-label">用户名:</label>
                                                    <input type="text" class="form-control" name="row[username]" value="{{row.username}}" disabled />
                                                </div>
                                                <div class="form-group">
                                                    <label for="email" class="control-label">Email:</label>
                                                    <input type="text" class="form-control" name="row[email]" value="{{row.email}}" data-rule="required;email" />
                                                </div>
                                                <div class="form-group">
                                                    <label for="nickname" class="control-label">手机:</label>
                                                    <input type="text" class="form-control" name="row[mobile]" value="{{row.mobile}}" data-rule="mobile" />
                                                </div>
                                                <div class="form-group">
                                                    <label for="password" class="control-label">密码:</label>
                                                    <input type="text" class="form-control" placeholder="不修改密码请留空" name="row[password]" value="" data-rule="password"/>
                                                </div>
                                                <div class="form-group">
                                                    <button type="submit" class="btn btn-success">提交</button>
                                                    <button type="reset" class="btn btn-default">重置</button>
                                                </div>

                                            </div>
                                        </form>
                                    </div>
                                </div>

                            </div>
                            <div class="col-md-8">
                                <div class="panel panel-default panel-intro panel-nav">
                                    <div class="panel-heading">
                                        <ul class="nav nav-tabs">
                                            <li class="active"><a href="#one" data-toggle="tab"><i class="fa fa-list"></i> 操作日志</a></li>
                                        </ul>
                                    </div>
                                    <div class="panel-body">
                                        <div id="myTabContent" class="tab-content">
                                            <div class="tab-pane fade active in" id="one">
                                                <div class="widget-body no-padding">
                                                    <div id="toolbar" class="toolbar">
                                                        <a href="javascript:;" class="btn btn-primary btn-refresh" ><i class="fa fa-refresh"></i> </a>                            </div>
                                                    <table id="table" class="table table-striped table-bordered table-hover" width="100%">

                                                    </table>

                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/assets/js/require.min.js" data-main="/assets/js/require-backend.min.js"></script>
</body>
</html>