<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/8/17
 * Time: 22:13
 * Desc: -系统函数
 */


use \Apps\Models\Site;
use \Apps\Services\ConstService;
use \Apps\Services\EmojiService;
use \Kengine\LkkCmponent;
use \Kengine\LkkConfig;
use \Kengine\LkkLang;
use \Kengine\Server\LkkServer;
use \Lkk\Concurrent\Promise;
use \Lkk\Helpers\CommonHelper;
use \Lkk\Helpers\UrlHelper;
use \Lkk\Helpers\ValidateHelper;
use \Lkk\Phalwoo\Server\ServerConst;
use \Lkk\Phalwoo\Server\SwooleServer;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler as MonoStreamHandler;
use \Monolog\Logger as Monologger;
use \voku\helper\AntiXSS;


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
 * 获取日志对象
 * @param string $logname
 * @param bool   $useServerLog 使用服务器异步日志
 *
 * @return mixed
 */
function getLogger($logname='', $useServerLog=false) {
    static $monLoggers;

    //在swoole服务里面,且在worker进程里面
    //因为SwooleServer绑定的日志对象使用了异步,但task不允许[can't use async-io in task process]
    if($useServerLog && is_object(SwooleServer::getServer()) && SwooleServer::isWorker()) {
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
            try {
                $handler = new MonoStreamHandler($file, Monologger::INFO);
            }catch (\Throwable $e) {
            }
            $handler->setFormatter($formatter);
            $logger->pushHandler($handler);
            $monLoggers[$logname] = $logger;
        }

        return $monLoggers[$logname];
    }
}


/**
 * 记录异常日志
 * @param object|string $e \Exception或字符串
 * @return bool
 */
function logException($e=null) {
    if(empty($e)) {
        return false;
    }

    $loger = getLogger('exception');
    if(is_string($e)) {
        $loger->error($e);
    }else{
        $msg = $e->getMessage() . ' ##code:' . $e->getCode() . ' ##file:' . $e->getFile() . ' ##line:' . $e->getLine();
        $loger->error($msg, $e->getTrace());
    }

    return true;
}


/**
 * 获取promise redis结果,参考Lkk\Phalwoo\Server\Component\Client\Redis
 * @param Promise|array $promise
 * @return bool
 */
function promiseRedisResult($promise) {
    $res = false;

    if(is_bool($promise)) {
        return $promise;
    }elseif($promise instanceof Promise) {
        $ret = $promise->getResult(); //同步redis结果
    }else{
        $ret = (array)$promise; //异步redis结果
    }

    if(!isset($ret['code'])) {
        logException(new Exception('redis promise result not valid:'. json_encode($ret)));
    }

    switch ($ret['code']) {
        default :
            logException(new Exception('redis promise unknow error:'. json_encode($ret)));
            break;
        case ServerConst::ERR_REDIS_CONNECT_FAILED :
            logException(new Exception('redis connect fail:'. json_encode($ret)));
            break;
        case ServerConst::ERR_REDIS_TIMEOUT :
            logException(new Exception('redis connect timeout:'. json_encode($ret)));
            break;
        case ServerConst::ERR_REDIS_ERROR :
            $res = false;
            break;
        case ServerConst::ERR_SUCCESS :
            $res = $ret['data'];
            break;
    }
    unset($promise);

    return $res;
}


/**
 * 获取站点信息
 * @param int $siteId 站点ID
 * @param bool|array $newInfo 新信息:false取缓存,true取数据库,array使用该信息作缓存
 * @return array|bool|\Phalcon\Mvc\Model|\Phalcon\Mvc\ModelInterface
 */
function getSiteInfo($siteId=null, $newInfo=false) {
    if(is_null($siteId)) $siteId = getConf('site')->siteId; //默认获取当前站点ID
    if(empty($siteId) || !ValidateHelper::isInteger($siteId)) return false;

    //获取同步redis
    $redis = LkkServer::getPoolManager()->get('redis_site')->pop(true);
    $key = ConstService::CACHE_SITE_INFO . $siteId;

    $info = promiseRedisResult($redis->get($key));
    if($info) $info = (array)json_decode($info);

    if($newInfo || empty($info)) {
        $info = Site::getBaseInfo($siteId);
        if($info) {
            $info = $info->toArray();
            if(is_array($newInfo)) $info = array_merge($info, $newInfo);
            $redis->set($key, json_encode($info));
        }
    }

    unset($redis);

    return $info;
}


/**
 * 获取站点URL[以/结尾]
 * @param null $param 站点ID;或request->header数组;或$_SERVER数组;或URL地址
 * @param bool|string $newInfo 新信息:false取缓存,true取数据库,string使用该信息作缓存
 * @return bool|mixed|string
 */
function getSiteUrl($param=null, $newInfo=false) {
    static $siteUrls;
    $url = '';
    if(empty($param) || is_numeric($param)) { //根据siteID
        $siteId = intval($param);
        if($siteId<=0) $siteId = getConf('site')->siteId;
        $url = $siteUrls[$siteId] ?? '';

        if(empty($url) || $newInfo) {
            if($newInfo && ValidateHelper::isUrl($newInfo)) {
                $url = $newInfo;
            }else{
                $url = getSiteInfo($siteId)['site_url'] ?? '';
            }

            $siteUrls[$siteId] = $url = rtrim(strtolower($url), '/') . '/';
        }
    }else{
        //根据request->header数组 或者 $_SERVER数组 或者 URL地址
        $param = $param['host'] ?? $param['HTTP_HOST'] ?? strval($param);
        $param = UrlHelper::formatUrl($param);
        if(ValidateHelper::isUrl($param)) {
            $arr = parse_url($param);
            $port = $arr['port'] ?? null;
            if(!empty($port) && !in_array($port, [80, 443])) {
                $url = "{$arr['scheme']}://{$arr['host']}:{$port}/";
            }else{
                $url = "{$arr['scheme']}://{$arr['host']}/";
            }
            $url = strtolower($url);
        }
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
    static $siteIds;

    if(empty($url)) {
        $siteConf = getConf('site');
        return $siteConf['siteId'] ?? 0;
    }else{
        $url = strtolower($url);
    }

    if(is_null($siteIds) || !isset($siteIds[$url])) {
        $siteIds[$url] = 0;
        $res = Site::getRow(['site_url'=>$url]);
        if(!empty($res)) {
            $siteIds[$url] = $res->site_id;
        }
    }

    return $siteIds[$url] ?? 0;
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
 * 移除emoji字符
 * @param string $str
 *
 * @return mixed|string
 */
function removeEmoji($str='') {
    return EmojiService::removEmoji($str);
}


/**
 * 翻译转换语言字符串
 * @param $string
 * @param array $values
 * @return string
 */
function lang($string, array $values = []) {
    if(empty($string) || (!is_numeric($string) && (!is_array($values) || empty($values)))) {
        return $string;
    }

    return LkkLang::getInstance()->translate(strval($string), $values);
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
    $urlObj = LkkCmponent::url();
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
 * 获取后台操作锁
 * @param string $operation 后台操作名称(英文)
 * @param int $dataId 数据ID,比如用户ID
 * @param int $admUid 后台UID
 * @param int $ttl 锁时间,秒
 * @return int
 */
function getlockBackendOperate($operation = '', $dataId = 0, $admUid = 0, $ttl = 120) {
    $res = 0;
    if (empty($operation) || empty($dataId) || empty($admUid)) {
        return $res;
    }

    if (!is_numeric($ttl) || $ttl <= 0) $expire = 60;
    $now = time();
    $expire = $now + $ttl;

    //同步的redis
    $redis = SwooleServer::getPoolManager()->get('redis_site')->pop(true);
    $key = ConstService::ADM_OPERATION_LOCK ."{$operation}_{$dataId}";
    $data = implode('|', [$admUid, $expire]);

    if ($ret = $redis->setnx($key, $data)) {
        $redis->expire($key, $ttl);
        $res = $admUid;
    } else {
        $val = $redis->get($key);
        $arr = $val ? explode('|', $val) : [];
        $uid = $arr[0] ?? 0;
        $exp = $arr[1] ?? 0;
        if(empty($val) || $uid==0) {
            $redis->set($key, $data, $ttl);
            $res = $admUid;
        }else{
            if($uid==$admUid || ($now>$exp)) {
                $redis->set($key, $data, $ttl);
                $res = $admUid;
            }else{
                $res = - abs($uid);
            }
        }
    }

    return $res;
}


/**
 * 解锁后台操作
 * @param string $operation 后台操作名称(英文)
 * @param int $dataId 数据ID,比如用户ID
 * @return bool
 */
function unlockBackendOperate($operation = '', $dataId = 0) {
    $res = false;
    if (empty($operation) || empty($dataId)) {
        return true;
    }

    $redis = SwooleServer::getPoolManager()->get('redis_site')->pop(true);
    $key = ConstService::ADM_OPERATION_LOCK ."{$operation}_{$dataId}";

    return $redis->delete($key);
}

