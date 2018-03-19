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
                    "uploadurl": "",
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
                "actionname": "login",
                "jsname": "backend/index",
                "moduleurl": "{{siteUrl}}",
                "captchaUrl": "{{captchaUrl}}",
                "language": "zh-cn",
                "referer": null
            }
        };
    </script>

    <style type="text/css">
        body {
            color:#999;
            background:#1a242f;
            background-size:cover;
        }
        a {
            color:#fff;
        }
        .login-panel{margin-top:150px;}
        .login-screen {
            max-width:400px;
            padding:0;
            margin:100px auto 0 auto;

        }
        .login-screen .well {
            border-radius: 3px;
            -webkit-box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background: rgba(255,255,255, 0.2);
        }
        .login-screen .copyright {
            text-align: center;
        }
        @media(max-width:767px) {
            .login-screen {
                padding:0 20px;
            }
        }
        .profile-img-card {
            width: 100px;
            height: 100px;
            margin: 10px auto;
            display: block;
            -moz-border-radius: 50%;
            -webkit-border-radius: 50%;
            border-radius: 50%;
        }
        .profile-name-card {
            text-align: center;
        }

        #login-form {
            margin-top:20px;
        }
        #login-form .input-group {
            margin-bottom:15px;
        }
        .vcode{display: block; height: 30px; width: 140px; overflow: hidden;}
        i.ace-icon a{
            display: block;
            position: absolute;
            top: 0;
            right: 0;
            z-index: 99;
            float: right;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="login-wrapper">
        <div class="login-screen">
            <div class="well">
                <div class="login-form">
                    <img id="profile-img" class="profile-img-card" src="/assets/img/avatar.png" />
                    <p id="profile-name" class="profile-name-card"></p>

                    <form action="{{saveUrl}}" method="post" id="login-form">
                        <div id="errtips" class="hide"></div>
                        <input type="hidden" name="{{tokenKey}}" value="{{tokenVal}}" />

                        <div class="input-group">
                            <div class="input-group-addon"><span class="glyphicon glyphicon-user" aria-hidden="true"></span></div>
                            <input type="text" class="form-control" id="loginName" placeholder="用户名" name="loginName" autocomplete="off" value="" data-rule="用户名:required;username" />
                        </div>

                        <div class="input-group">
                            <div class="input-group-addon"><span class="glyphicon glyphicon-lock" aria-hidden="true"></span></div>
                            <input type="password" class="form-control" id="password" placeholder="密码" name="password" autocomplete="off" value="" data-rule="密码:required;password" />
                        </div>

                        <div class="input-group">
                            <div class="input-group-addon"><span class="glyphicon glyphicon-copyright-mark" aria-hidden="true"></span></div>
                            <input type="hidden" id="veriEncode" name="veriEncode">

                            <input type="text" class="form-control" id="verifyCode" placeholder="验证码" name="verifyCode" autocomplete="off" value="" data-rule="验证码:required;verifyCode" />
                            <i class="ace-icon">
                                <a href="javascript:;">
                                    <img src="" id="verifyImg">
                                </a>
                            </i>
                        </div>

                        <div class="form-group">
                            <label class="inline" for="remember">
                                <input type="checkbox" name="remember" id="remember" value="1" />
                                记住我
                            </label>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-success btn-lg btn-block">登 录</button>
                        </div>
                    </form>
                </div>
            </div>
            <p class="copyright">Copyright &copy; {{year}} Lkk All Rights. Powered By {{system}}</p>
        </div>
    </div>
</div>
<script src="/assets/js/require.min.js" data-main="/assets/js/require-backend.min.js"></script>
</body>
</html>