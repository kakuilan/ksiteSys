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
                "controllername": "config",
                "actionname": "edit",
                "jsname": "backend/site",
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
                            <input type="hidden" name="row[site_id]" id="id" value="{{id}}">

                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="site_name">站点名称</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="text" class="form-control" name="row[site_name]" id="site_name" value="{% if row %}{{row.site_name}}{% endif %}" maxlength="25" data-rule="required;length(1~50)">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="site_url">站点网址</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="text" class="form-control" name="row[site_url]" id="site_url" value="{% if row %}{{row.site_url}}{% endif %}" maxlength="25" data-rule="required;url" placeholder="以/结尾">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="sort">站点排序</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="number" class="form-control" name="row[sort]" id="sort" value="{% if row %}{{row.sort}}{% else %}99{% endif %}" oninput="if(value.length>4)value=value.slice(0,4)" maxlength="4" data-rule="required;digits">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="status">站点状态</label>
                                <div class="col-sm-8 col-xs-7">
                                    <label for="row[status]-close"><input id="row[status]-close" name="row[status]" type="radio" value="-1" {% if row and row.status == -1 %} checked {% endif %} />关闭</label>
                                    <label for="row[status]-service"><input id="row[status]-service" name="row[status]" type="radio" value="0" {% if row and row.status == 0 %} checked {% endif %} />维护</label>
                                    <label for="row[status]-open"><input id="row[status]-open" name="row[status]" type="radio" value="1" {% if row and row.status == 1 %} checked {% endif %} />开启</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="remark">备注</label>
                                <div class="col-sm-8 col-xs-7">
                                    <textarea name="row[remark]" id="remark" cols="30" rows="3" class="form-control" data-rule="length(1~128)">{% if row %}{{row.remark}}{% endif %}</textarea>
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