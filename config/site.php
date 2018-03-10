<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/9/16
 * Time: 14:24
 * Desc: -站点配置
 */


return [
    'url'               => 'http://ksys.loc/', //网站完整URL,带http,小写,结尾包含/
    'srcFullUrl'        => false, //HTML源码中本站资源链接是否完整的URL:否-以/开头,是-则以完整站点URL开头
    'defaultModule'     => 'home', //默认模块
    'defaultController' => 'index', //默认控制
    'defaultAction'     => 'index', //默认动作
    'csrfToken'         => '__token__', //Csrf参数的名称,form表单
];