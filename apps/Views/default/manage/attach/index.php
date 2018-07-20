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
                    "cdnurl": "{{uploadUrl}}",
                    "uploadurl": "",
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
                "actionname": "index",
                "jsname": "backend/attach",
                "moduleurl": "{{siteUrl}}",
                "language": "zh-cn",
                "referer": null,
                //扩展参数
                "extparam" : {
                    "listUrl" : "{{listUrl}}",
                    "addUrl" : "{{editUrl}}",
                    "editUrl" : "{{editUrl}}",
                    "delUrl" : "{{delUrl}}",
                    "multiUrl" : "{{multiUrl}}",
                    "sites" : '{{sites}}',
                    "authStatusArr" : '{{authStatusArr}}',
                    "persistentStatusArr" : '{{persistentStatusArr}}',
                    "hasThirdArr" : '{{hasThirdArr}}',
                    "belongTypeArr" : '{{belongTypeArr}}',
                    "fileTypeArr" : '{{fileTypeArr}}',
                    "tagArr" : '{{tagArr}}'
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
                        <div class="panel panel-default panel-intro">

                            <div class="panel-body">
                                <div id="myTabContent" class="tab-content">
                                    <div class="tab-pane fade active in" id="one">
                                        <div class="widget-body no-padding">
                                            <div id="toolbar" class="toolbar">
                                                <a href="javascript:;" class="btn btn-primary btn-refresh" ><i class="fa fa-refresh"></i></a>
                                                <a href="javascript:;" class="btn btn-success btn-edit btn-disabled disabled" ><i class="fa fa-pencil"></i> 编辑</a>
                                                <a href="javascript:;" class="btn btn-danger btn-del btn-disabled disabled" ><i class="fa fa-trash"></i> 删除</a>
                                                <a href="javascript:;" class="btn btn-success btn-import" title="上传" id="btn-import-file" data-url="{{uploadUrl}}" data-mimetype="*" data-multiple="true"><i class="fa fa-upload"></i> 上传</a>
                                                <div class="dropdown btn-group">
                                                    <a class="btn btn-primary btn-more dropdown-toggle btn-disabled disabled" data-toggle="dropdown"><i class="fa fa-cog"></i> 更多</a>
                                                    <ul class="dropdown-menu text-left" role="menu">
                                                        <li><a class="btn btn-link btn-multi btn-disabled disabled" href="javascript:;" data-params="is_auth=1"><i class="fa fa-check"></i>通过</a></li>
                                                        <li><a class="btn btn-link btn-multi btn-disabled disabled" href="javascript:;" data-params="is_auth=-1"><i class="fa fa-close"></i>不通过</a></li>
                                                    </ul>
                                                </div>
                                            </div>
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
<script src="/assets/js/require.min.js" data-main="/assets/js/require-backend.min.js"></script>
</body>
</html>