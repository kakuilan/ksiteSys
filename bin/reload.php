<?php
/**
 * Created by PhpStorm.
 * User: doubo
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

global $argv;
$paramPid = 0;
foreach ($argv as $k=>$item) {
    if($k==0) continue;
    if(stripos($item, 'pid')===0) {
        $arr = explode('=', $item);
        $paramPid = isset($arr[1]) ? intval($arr[1]) : 0;
    }
}

$conf = getConf('server');
$serverPid = $paramPid ? $paramPid : LkkServer::getMasterPid($conf->toArray());
if(!$conf->server_reload) {
    die("please confirm conf['server_reload']=true \r\n");
}elseif ($serverPid<=0) {
    die("server is not running! \r\n");
}

AutoReload::setSelfPidPath($conf->inotify->pid_file);
//设置服务程序的PID
$obj = new AutoReload($serverPid);
//设置要监听的源码目录
$obj->watch($conf->inotify->watch_dir);
//监听后缀为.php的文件
$obj->addFileType('.php');
$obj->run();
