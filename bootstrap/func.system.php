<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/8/17
 * Time: 22:13
 * Desc: -系统函数
 */
 

use \Kengine\LkkConfig;
use \Lkk\Helpers\CommonHelper;
use \Lkk\Phalwoo\Server\SwooleServer;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Logger as Monologger;
use \Monolog\Handler\StreamHandler as MonoStreamHandler;
use \voku\helper\AntiXSS;


function mytest() {
    //TODO
}


/**
 * 获取配置
 * @param string $file 配置文件
 * @param string $key 配置键
 * @param mixed $default 默认值
 * @return mixed
 */
function getConf($file, $key = null, $default=null) {
    return LkkConfig::get($file, $key, $default);
}


/**
 * 获取当前站点URL [后面加/]
 * @return string
 */
function getSiteUrl() {
    static $url;
    if(is_null($url)) {
        $siteConf = getConf('site');
        if(isset($siteConf['url']) && !empty($siteConf['url'])) {
            $url = $siteConf['url'];
        }elseif(isset($_SERVER['HTTP_HOST'])){
            $url = parse_url(CommonHelper::getUrl());
            $url = $url['scheme'] .'://' . $url['host'];
        }else{
            $url = '';
        }
        $url = rtrim(strtolower($url), '/') . '/';
    }

    return $url;
}


/**
 * 根据url获取站点ID
 * @param string $url
 *
 * @return mixed
 */
function getSiteId($url='') {
    if(empty($url)) $url = getSiteUrl();
    static $siteIds;
    if(is_null($siteIds) || !isset($siteIds[$url])) {
        $siteIds[$url] = 0;
        $res = \Apps\Models\Site::findFirst(['url'=>$url]);
        if(!empty($res)) {
            $siteIds[$url] = $res->site_id;
        }
    }

    return $siteIds[$url];
}


/**
 * 防跨站xss过滤
 * @param string $str
 *
 * @return array|bool|string
 */
function xssClean(string $str) {
    static $antiXss;
    if(empty($str)) return '';

    if(is_null($antiXss)) {
        $antiXss = new AntiXSS();
    }

    return $antiXss->xss_clean($str);
}


/**
 * 翻译转换语言字符串
 * @param $string
 * @param array $values
 * @return string
 */
function lang($string, array $values = []) {
    if(!is_array($values) || empty($values)) return $string;
    return \Kengine\LkkLang::getInstance()->translate($string, $values);
}


/**
 * 获取用户头像路径
 * <pre>
 * 如 $uid = 31 拼成如下路径
 * 000/00/00/31
 * </pre>
 * @param $uid
 * @param string $size 'big','middle','small'或其他
 * @return string
 */
function getAvatar($uid, $size = 'middle') {
    $uid = abs(intval($uid));
    $uid = sprintf("%09d", $uid);
    $dir1 = substr($uid, 0, 3);
    $dir2 = substr($uid, 3, 2);
    $dir3 = substr($uid, 5, 2);

    //TODO 具体网址

    return $dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2) ."_avatar_$size.jpg";
}


/**
 * 解析@到某某
 * @param $str
 * @return array
 */
function parseAt($str) {
    preg_match_all("/@([^@^\\s^:]{1,})([\\s\\:\\,\\;]{0,1})/", $str, $result);
    return $result;
}


/**
 * 生成url,在volt模板内可使用url函数
 * @param null $uri
 * @param null $args
 * @param null $local
 * @return mixed
 */
function makeUrl($uri = null, $args = null, $local = null) {
    $urlObj = \Kengine\LkkCmponent::url();
    $uri = ltrim($uri, '/');
    $url = $urlObj->get($uri, $args, $local);
    return $url;
}


/**
 * 异步CLI请求,服务器须配置php环境变量
 * @param $task
 * @param $action
 * @param array $params
 * @return bool
 */
function asyncCli($task, $action, $params=[]){
    if(!is_string($task) || !is_string($action)) return false;

    $tmp = [];
    foreach ($params as $k=>$v) {
        if(is_numeric($k)) {
            $tmp[] = trim($v);
        }else{
            $tmp[] = $k.'='. trim($v);
        }
    }
    $params = implode(' ', $tmp);

    $file = BINDIR . 'cli' . PHPEXT;
    $cmd = "php {$file} {$task} {$action} {$params}";
    if (substr(php_uname(), 0, 7) == "Windows"){
        $cmd = 'start /B ' . $cmd;
    }

    $res = pclose(popen("{$cmd} &", 'r'));
    return $res;
}


/**
 * 获取日志对象
 * @param string $logname
 * @param bool   $useServerLog 使用服务器异步日志
 *
 * @return mixed
 */
function getLogger($logname='', $useServerLog=false) {
    static $monLoggers;

    if($useServerLog && is_object(SwooleServer::getServer())) { //在swoole服务里面
        return SwooleServer::getLogger();
    }else{
        $logname = trim($logname);
        if($logname=='') $logname='commm';
        if(!isset($monLoggers[$logname])) {
            $file = LOGDIR . "{$logname}.log";
            //设置日期格式
            $dateFormat = "Y-m-d H:i:s.u";
            $formatter = new LineFormatter(null, $dateFormat);
            $logger = new Monologger($logname);
            $handler = new MonoStreamHandler($file, Monologger::INFO);
            $handler->setFormatter($formatter);
            $logger->pushHandler($handler);
            $monLoggers[$logname] = $logger;
        }

        return $monLoggers[$logname];
    }
}