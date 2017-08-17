<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/8/14
 * Time: 23:54
 * Desc: -常量定义
 */


define('STARTTIME', microtime(true));
define('DS', str_replace('\\', '/', DIRECTORY_SEPARATOR));
define('PS', PATH_SEPARATOR);

define('KSERVER_NAME', 'KSS');
define('KSERVER_VERS', '0.0.0.1');

define('ROOTDIR', str_replace('\\', '/', dirname(__DIR__)) . DS ); //根目录
define('BINDIR',    ROOTDIR .'bin'          . DS ); //执行目录
define('WWWDIR',    ROOTDIR .'public'       . DS ); //WEB入口目录
define('BOOTDIR',   ROOTDIR .'bootstrap'    . DS );
define('APPSDIR',   ROOTDIR .'apps'         . DS );
define('CONFDIR',   ROOTDIR .'config'       . DS );
define('DATADIR',   ROOTDIR .'data'         . DS );
define('KENGDIR',   ROOTDIR .'kengine'      . DS );
define('RUNTDIR',   ROOTDIR .'runtime'      . DS );
define('TESTDIR',   ROOTDIR .'tests'        . DS );

define('LOGDIR',    RUNTDIR .'logs'         . DS );
define('PIDDIR',    RUNTDIR .'pids'         . DS );
define('MODULDIR',  APPSDIR .'Modules'      . DS ); //应用模块目录
define('CTASKDIR',  MODULDIR .'Cli/Tasks'   . DS ); //CLI任务目录
define('MODELDIR',  APPSDIR .'Models'       . DS ); //数据模型类目录
define('VIEWDIR',   APPSDIR .'Views'        . DS ); //视图目录
define('UPLODIR',   WWWDIR .'upload'        . DS ); //上传目录

define('REDIS_CACHE_DB', 0); //用于存储CACHE的redis库
define('REDIS_SESSION_DB', 1); //用于存储SESSION的redis库

