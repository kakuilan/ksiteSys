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
                    "cdnurl": "/",
                    "uploadurl": "{{uploadUrl}}",
                    "bucket": "",
                    "maxsize": "10mb",
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
                "modulename": "manage",
                "controllername": "attach",
                "actionname": "edit",
                "jsname": "backend/attach",
                "moduleurl": "{{siteUrl}}",
                "language": "zh-cn",
                "referer": null,
                //扩展参数
                "extparam" : {
                }
            }
        };
    </script>
    <style>
        dd.form-inline > span.n-right {
            margin-top: -20px;
        }
    </style>
</head>

<body class="inside-header inside-aside is-dialog">
<div id="main" role="main">
    <div class="tab-content tab-addtabs">
        <div id="content">
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <div class="content">
                        <form id="edit-form" class="form-horizontal form-ajax" role="form" data-toggle="validator" method="POST" action="{{saveUrl}}">
                            <input type="hidden" name="id" id="id" value="{{id}}">

                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right">站点</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="text" class="form-control" value="{% if row %}{{row.site_id}}{% endif %}" disabled readonly>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right">是否已删</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="text" class="form-control" value="{% if row %}{{row.is_del}}{% endif %}" disabled readonly>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right">文件名</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="text" class="form-control" value="{% if row %}{{row.file_name}}{% endif %}" disabled readonly>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right">是否审核</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="text" class="form-control" value="{% if row %}{{row.is_auth}}{% endif %}" disabled readonly>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right">是否持久化</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="text" class="form-control" value="{% if row %}{{row.is_persistent}}{% endif %}" disabled readonly>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right">第三方</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="text" class="form-control" value="{% if row %}{{row.has_third}}{% endif %}" disabled readonly>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right">可否压缩</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="text" class="form-control" value="{% if row %}{{row.compress_enable}}{% endif %}" disabled readonly>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right">用户ID</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="text" class="form-control" value="{% if row %}{{row.uid}}{% endif %}" disabled readonly>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right">图片宽度</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="text" class="form-control" value="{% if row %}{{row.img_width}}{% endif %}" disabled readonly>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right">图片高度</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="text" class="form-control" value="{% if row %}{{row.img_height}}{% endif %}" disabled readonly>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right">引用次数</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="text" class="form-control" value="{% if row %}{{row.quote_num}}{% endif %}" disabled readonly>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right">下载次数</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="text" class="form-control" value="{% if row %}{{row.downl_num}}{% endif %}" disabled readonly>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right">文件大小</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="text" class="form-control" value="{% if row %}{{row.file_size}}{% endif %}" disabled readonly>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right">标识</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="text" class="form-control" value="{% if row %}{{row.tag}}{% endif %}" disabled readonly>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="title">文件标题</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="text" class="form-control" name="title" id="title" value="{% if row %}{{row.title}}{% endif %}" maxlength="30" data-rule="required;length(1~30)">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right">保存路径</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="text" class="form-control" value="{% if row %}{{row.file_path}}{% endif %}" disabled readonly>
                                </div>
                            </div>

                            <div class="form-group hide layer-footer">
                                <label class="control-label col-xs-12 col-sm-2"></label>
                                <div class="col-xs-12 col-sm-8">
                                    <button type="submit" class="btn btn-success btn-embossed disabled">确定</button>
                                    <button type="reset" class="btn btn-default btn-embossed">重置</button>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/assets/js/require.min.js" data-main="/assets/js/require-backend.min.js"></script>
</body>
</html>