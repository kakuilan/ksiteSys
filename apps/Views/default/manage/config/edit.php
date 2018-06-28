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
                "jsname": "backend/config",
                "moduleurl": "{{siteUrl}}",
                "language": "zh-cn",
                "referer": null,
                //扩展参数
                "extparam" : {
                    "row" : '{{rowJson}}'
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
                            <input type="hidden" name="row[id]" id="id" value="{{id}}">

                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="sort">站点</label>
                                <div class="col-sm-8 col-xs-7">
                                    <select class="form-control m-b" id="site_id" name="row[site_id]" data-rule="required">
                                        {% if sites %}
                                        {% for vue,name in sites %}
                                        <option value="{{vue}}" {% if row and row.site_id==vue %} selected {% endif %} title="{{name}}" >
                                            {{name}}
                                        </option>
                                        {% endfor %}
                                        {% endif %}
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="sort">数据类型</label>
                                <div class="col-sm-8 col-xs-7">
                                    <select class="form-control m-b" id="data_type" name="row[data_type]" data-rule="required">
                                        <option value="">请选择</option>
                                        {% if dataTypes %}
                                        {% for vue,name in dataTypes %}
                                        <option value="{{vue}}" {% if row and row.data_type==vue %} selected {% endif %} title="{{name}}" >
                                            {{name}}
                                        </option>
                                        {% endfor %}
                                        {% endif %}
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="sort">控件类型</label>
                                <div class="col-sm-8 col-xs-7">
                                    <select class="form-control m-b" id="input_type" name="row[input_type]" data-rule="required">
                                        <option value="">请选择</option>
                                        {% if inputTypes %}
                                        {% for vue,name in inputTypes %}
                                        <option value="{{vue}}" {% if row and row.input_type==vue %} selected {% endif %} title="{{name}}" >
                                            {{name}}
                                        </option>
                                        {% endfor %}
                                        {% endif %}
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="title">配置名称</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="text" class="form-control" name="row[title]" id="title" value="{% if row %}{{row.title}}{% endif %}" maxlength="25" data-rule="required;length(1~50)">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="key">配置键</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="text" class="form-control" name="row[key]" id="key" value="{% if row %}{{row.key}}{% endif %}" maxlength="25" data-rule="required;isWord;length(1~30)">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="sort">排序</label>
                                <div class="col-sm-8 col-xs-7">
                                    <input type="number" class="form-control" name="row[sort]" id="sort" value="{% if row %}{{row.sort}}{% else %}99{% endif %}" maxlength="4" data-rule="required;digits">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="remark">备注</label>
                                <div class="col-sm-8 col-xs-7">
                                    <textarea name="row[remark]" id="remark" cols="30" rows="3" class="form-control" data-rule="length(1~100)">{% if row %}{{row.remark}}{% endif %}</textarea>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 col-xs-3 control-label no-padding-right text-right" for="value">配置值</label>
                                <div class="col-sm-8 col-xs-7" id="valueDiv"></div>
                            </div>

                            <div class="form-group" id="uploadDiv" style="display: none">
                                <label for="c-local" class="control-label col-xs-12 col-sm-2"></label>
                                <div class="col-xs-12 col-sm-8">
                                    <button id="plupload-local" class="btn btn-primary plupload" data-input-id="c-value" data-url="{{uploadUrl}}"><i class="fa fa-upload"></i>上传</button>
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