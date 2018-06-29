<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/9/16
 * Time: 12:58
 * Desc: -连接池配置
 */
 
 
return [

    /**
     * MySQL 连接池-主库
     */
    'mysql_master' => [
        'type'  => 'mysql',                 // 连接池类型
        'size'  => 2,                       // 连接池大小
        'table_prefix'  => 'k_',            //表前缀
        'charset'       => 'utf8',          //字符集

        'args'  => [                        // 连接参数
            'host'      => '127.0.0.1',     // 主机名
            'port'      => 3306,            // 端口号
            'user'      => 'root',          // 用户名
            'password'  => 'root',          // 密码
            'database'  => 'test_ksys',     // 数据库名称
            'open_log'  => true,
            'slow_query' => 0,              //慢查询20毫秒
            'wait_timeout' => 345600,       //连接超时,4小时
        ]
    ],


    /**
     * MySQL 连接池-从库
     */
    'mysql_slave' => [
        'type'  => 'mysql',                 // 连接池类型
        'size'  => 4,                       // 连接池大小
        'table_prefix'  => 'k_',            //表前缀
        'charset'   => 'utf8',              //字符集

        'args'  => [                        // 连接参数
            'host'      => '127.0.0.1',     // 主机名
            'port'      => 3306,            // 端口号
            'user'      => 'root',          // 用户名
            'password'  => 'root',          // 密码
            'database'  => 'test_ksys',     // 数据库名称
            'open_log'  => true,
            'slow_query' => 0,              //慢查询20毫秒
            'wait_timeout' => 345600,       //连接超时,4小时
        ]
    ],


    /**
     * Redis 连接池,站群系统,无前缀
     */
    'redis_system' => [
        'type'  => 'redis',                 // 连接池类型
        'size' => 2,                        // 连接池大小

        'args'  => [
            'host'      => '127.0.0.1',     // 主机名
            'port'      => 6379,            // 端口号
            'auth'      => '123456',        // 口令
            'select'    => 0,               // 库编号
            'prefix'    => 'sys:',          // 前缀
        ]
    ],


    /**
     * Redis 连接池,仅是本站,有前缀
     */
    'redis_site' => [
        'type'  => 'redis',                 // 连接池类型
        'size' => 3,                        // 连接池大小

        'args'  => [
            'host'      => '127.0.0.1',     // 主机名
            'port'      => 6379,            // 端口号
            'auth'      => '123456',        // 口令
            'select'    => 0,               // 库编号
            'prefix'    => 'sit:',          // 前缀
        ]
    ],


    /**
     * Session的Redis 连接池,有前缀
     */
    'redis_session' => [
        'type'  => 'redis',                 // 连接池类型
        'size' => 3,                        // 连接池大小

        'args'  => [
            'host'      => '127.0.0.1',     // 主机名
            'port'      => 6379,            // 端口号
            'auth'      => '123456',        // 口令
            'select'    => REDIS_SESSION_DB, // 库编号
            'prefix'    => '',          // 前缀,具体前缀放在session配置
        ]
    ],


    /**
     * Queue的Redis 连接池,有前缀
     */
    'redis_queue' => [
        'type'  => 'redis',                 // 连接池类型
        'size' => 1,                        // 连接池大小

        'args'  => [
            'host'      => '127.0.0.1',     // 主机名
            'port'      => 6379,            // 端口号
            'auth'      => '123456',        // 口令
            'select'    => REDIS_QUEUE_DB, // 库编号
            'prefix'    => '',          // 前缀,具体前缀放在session配置
        ]
    ],


];