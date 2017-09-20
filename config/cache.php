<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/9/16
 * Time: 14:00
 * Desc: -缓存配置
 */


return [
    'prefix'    => 'kdm_', //key前缀
    'lifetime'  => 600, //默认缓存有效期
    'redis'     => 'redis_site', //redis连接池名称,参考pool配置
];