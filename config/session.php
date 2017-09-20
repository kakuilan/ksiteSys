<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/9/16
 * Time: 13:55
 * Desc: -session配置
 */
 
 
return [
    'host'              => 'localhost',
    'port'              => 6379,
    'auth'              => null,
    'lifetime'          => '900', //秒,redis SESSION有效期
    'cookie_lifetime'   => 0, //秒,cookie PHPSESSID有效期,0为随浏览器
    'cookie_secure'     => true,
    'uniqueId'          => '_lkksys_', //隔离不同应用的会话数据
    'prefix'            => 'SESSION:',
    'name'              => null,
    'index'             => REDIS_SESSION_DB, //redis库号
    'cookie'            => null, //使用cookie的配置替换
];