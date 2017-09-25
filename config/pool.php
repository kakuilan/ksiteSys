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
        'size'  => 20,                       // 连接池大小
        'table_prefix'  => 'lkk_',          //表前缀
        'charset'   => 'utf8',              //字符集

        'args'  => [                        // 连接参数
            'host'      => '127.0.0.1',     // 主机名
            'port'      => 3306,            // 端口号
            'user'      => 'root',          // 用户名
            'password'  => '123456',        // 密码
            'database'  => 'test',          // 数据库名称
            'open_log'  => true,
            'slow_query' => 20, //慢查询20毫秒
        ]
    ],


    /**
     * MySQL 连接池-从库
     */
    'mysql_slave' => [
        'type'  => 'mysql',                 // 连接池类型
        'size'  => 40,                       // 连接池大小
        'table_prefix'  => 'lkk_',          //表前缀
        'charset'   => 'utf8',              //字符集

        'args'  => [                        // 连接参数
            'host'      => '127.0.0.1',     // 主机名
            'port'      => 3306,            // 端口号
            'user'      => 'root',          // 用户名
            'password'  => '123456',        // 密码
            'database'  => 'test',          // 数据库名称
            'open_log'  => true,
            'slow_query' => 20, //慢查询20毫秒
        ]
    ],


    /**
     * Redis 连接池,站群系统,无前缀
     */
    'redis_system' => [
        'type'  => 'redis',                 // 连接池类型
        'size' => 10,                        // 默认为 1 连接, 无需设置

        'args'  => [
            'host'      => '127.0.0.1',     // 主机名
            'port'      => 6379,            // 端口号
            'auth'      => null,            // 口令
            'select'    => 0,               // 库编号
            'prefix'    => 'sys:',          // 前缀
        ]
    ],


    /**
     * Redis 连接池,仅是本站,有前缀
     */
    'redis_site' => [
        'type'  => 'redis',                 // 连接池类型
        'size' => 10,                        // 默认为 1 连接, 无需设置

        'args'  => [
            'host'      => '127.0.0.1',     // 主机名
            'port'      => 6379,            // 端口号
            'auth'      => null,            // 口令
            'select'    => 0,               // 库编号
            'prefix'    => 'sit:',          // 前缀
        ]
    ],


];