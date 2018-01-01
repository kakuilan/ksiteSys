#!/usr/bin/env php
<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/12/15
 * Time: 14:12
 * Desc: 代码热更新
 */

require __DIR__ . '/../bootstrap/define.php';
require __DIR__ . '/../bootstrap/func.system.php';

//载入命名空间
$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->addPsr4('Apps\\',      APPSDIR);
$loader->addPsr4('Kengine\\',   KENGDIR);
$loader->addPsr4('Tests\\',     TESTDIR);

use \Lkk\Phalwoo\Server\AutoReload;
use \Kengine\Server\LkkServer;
use \Kengine\Engine;

global $argv;
$paramPid = $isChild = 0;
foreach ($argv as $k=>$item) {
    if($k==0) continue;
    if(stripos($item, 'pid')===0) {
        $arr = explode('=', $item);
        $paramPid = isset($arr[1]) ? intval($arr[1]) : 0;
    }elseif (stripos($item,'isChild')===0) { //本进程是从popen打开的子进程
        $arr = explode('=', $item);
        $isChild = isset($arr[1]) ? intval($arr[1]) : 0;
    }
}

usleep(500);
$conf = getConf('server');
$serverPid = $paramPid ? $paramPid : LkkServer::getMasterPid($conf->toArray());
if(!$conf->server_reload) {
    die("please confirm conf['server_reload']=true \r\n");
}elseif ($serverPid<=0) {
    die("server is not running! \r\n");
}

AutoReload::setSelfPidPath($conf->inotify->pid_file);
AutoReload::setSelfLogPath($conf->inotify->log_file);
AutoReload::setPrcessTitle($conf->server_name.'-inotify_reload');
AutoReload::setRestartFunc(function (){
    Engine::openReloadCodesProcess(['pid'=>0,'isChild'=>1]);
});
AutoReload::log("begin serverPid[{$serverPid}]");
//设置服务程序的PID
$service = new AutoReload(['afterMillSeconds'=>100, 'serverPid'=>$serverPid]);
//设置要监听的源码目录
$service->watch($conf->inotify->watch_dir);
//监听后缀为.php的文件
$service->addFileType('.php');
$service->run();
