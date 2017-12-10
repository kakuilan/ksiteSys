<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/8/17
 * Time: 22:51
 * Desc: -
 */


namespace Kengine\Server;

use Kengine\Engine;
use Kengine\LkkCmponent;
use Kengine\LkkCookies;
use Kengine\LkkModel;
use Lkk\Helpers\CommonHelper;
use Lkk\LkkService;
use Lkk\Phalwoo\Phalcon\Di as PwDi;
use Lkk\Phalwoo\Phalcon\Http\Request as PwRequest;
use Lkk\Phalwoo\Phalcon\Http\Response as PwResponse;
use Lkk\Phalwoo\Phalcon\Http\Response\Cookies as PwCookies;
use Lkk\Phalwoo\Phalcon\Session\Adapter\Redis as PwSession;
use Lkk\Phalwoo\Server\Component\Client\Mysql;
use Lkk\Phalwoo\Server\Component\Client\Redis;
use Lkk\Phalwoo\Server\Component\Log\Handler\AsyncStreamHandler;
use Lkk\Phalwoo\Server\Component\Log\SwooleLogger;
use Lkk\Phalwoo\Server\Component\Pool\PoolManager;
use Lkk\Phalwoo\Server\Concurrent\Promise;
use Lkk\Phalwoo\Server\DenyUserAgent;
use Lkk\Phalwoo\Server\SwooleServer;
use Phalcon\Mvc\Application;
use Phalcon\Debug as PhDebug;
use Lkk\Phalwoo\Phalcon\Debug as PwDebug;
use Lkk\Phalwoo\Phalcon\Mvc\Application as PwApplication;
use Lkk\Phalwoo\Phalcon\Mvc\Dispatcher as PwDispatcher;

class LkkServer extends SwooleServer {

    public function __construct(array $vars = []) {
        parent::__construct($vars);

    }


    /**
     * 获取实例[子类必须重写]
     * @param array $vars
     * @return mixed
     */
    public static function instance(array $vars = []) {
        if(is_null(parent::$instance) || !is_object(parent::$instance)) {
            parent::$instance = new self($vars);
        }

        return parent::$instance;
    }


    /**
     * 销毁实例化对象
     */
    public static function destroy() {
        parent::$instance = null;
        self::$_instance = null;
    }


    public function initServer() {
        //所有全局变量应在swoole事件绑定前设置好
        //否则swoole事件回调时进程间不共享变量
        //TODO 添加自定义的全局变量

        //TODO 读取单独的配置
        $this->setPoolManager(getConf('pool')->toArray());

        parent::initServer();

        $logger = self::getLogger();
        $logger->getDefaultHandler()->bindSwooleCloseEvent();

        return $this;
    }


    public function startServer() {
        //TODO 自定义逻辑

        parent::startServer();

        return $this;
    }


    public static function onSwooleStart($serv) {
        parent::onSwooleStart($serv); // TODO: Change the autogenerated stub

    }


    public static function onSwooleWorkerStart($serv, $workerId) {
        self::getPoolManager()->initAll();

        parent::onSwooleWorkerStart($serv, $workerId);

    }


    public static function onSwooleRequest($request, $response) {
        $sendRes = parent::onSwooleRequest($request, $response);
        if(!$sendRes) return $sendRes;

        $shareTable = SwooleServer::getShareTable();
        $stopping = intval($shareTable->get('server', 'stopping'));
        if($stopping) {
            $response->end('server stopping');
            return false;
        }

        //协程
        Promise::co(function() use ($request, $response){
            yield LkkServer::doSwooleRequest($request, $response);
        });

        return true;
    }


    /**
     * 是否开启xhprof
     * @param bool $useRatio 使用随机率
     *
     * @return bool
     */
    public static function isXhprofEnable($useRatio=false) {
        $res = false;
        $res = self::instance()->conf['xhprof_enable'] && function_exists('xhprof_enable');

        if($useRatio) $res = $res && mt_rand(1, self::instance()->conf['xhprof_ratio']) == self::instance()->conf['xhprof_ratio'];
        return $res;
    }


    public static function doSwooleRequest($request, $response) {
        //xhprof
        if(self::isXhprofEnable()) {
            // cpu:XHPROF_FLAGS_CPU 内存:XHPROF_FLAGS_MEMORY
            // 如果两个一起：XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY
            xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
        }

        $comConf = getConf('common');

        if($comConf->debug) {
            $debug = new PwDebug();
            $debug->setSwooleResponse($response);
            $debug->listen();
        }

        $di = new PwDi();
        //$app = new Application($di);
        $app = new PwApplication($di);
        $di->setShared('swooleRequest', $request);
        $di->setShared('swooleResponse', $response);

        /*$logger = self::getLogger();
        $logger->info('request:', [
            'header' => $request->header ?? '',
            'server' => $request->server ?? '',
            'get' => $request->get ?? '',
            'post' => $request->post ?? '',
            'cookie' => $request->cookie ?? '',
        ]);*/

        //加密组件放在cookie和denAgent前面
        $crypt = LkkCmponent::crypt();
        $di->setShared('crypt', $crypt);

        //TODO 检查客户端,防止爬虫和压力测试
        $denAgent = new DenyUserAgent();
        $denAgent->setRequest($request);
        $denAgent->setDI($di);

        //允许压测
        $denAgent->setAllowBench(true);
        $agentUuid = $denAgent->getAgentUuid();
        $di->setShared('denAgent', $denAgent);

        $chkAgen = $denAgent->checkAll();
        if(!$chkAgen) {
            return $response->end($denAgent->error());
        }

        self::resetRequestGlobal($request);
        $requestUuid = self::makeRequestUuid($request);

        $pwRequest = new PwRequest();
        $pwRequest->setDI($di);
        $di->setShared('request', $pwRequest);

        $pwResponse = new PwResponse();
        $pwResponse->setDi($di);
        $di->setShared('response', $pwResponse);

        //TODO 设置dispatcher

        $cookies = new LkkCookies();
        $cookies->setConf(getConf('cookie')->toArray());
        $cookies->useEncryption(false);
        $cookies->setDI($di);
        $di->setShared('cookies', $cookies);

        $sessionConf = getConf('session')->toArray();
        $sessionConf['cookie'] = getConf('cookie')->toArray();

        //注意下面这几个方法顺序不能改
        $session = new PwSession($sessionConf);
        $session->setDI($di);
        $di->setShared('session', $session);
        yield $session->start();

        //session的pv检查
        $userQps = $session->getQps();
        if($userQps>9) {
            return $response->end('访问过于频繁');
        }

        //注册各模块
        $moduleConf = getConf('modules')->toArray();
        $app->registerModules($moduleConf);

        //设置路由
        $router = Engine::setRouter();
        $di->setShared('router', $router);

        //设置分发器
        $dispatcher = new PwDispatcher();
        $di->setShared('dispatcher', $dispatcher);

        // URL设置
        $di->setShared('url', LkkCmponent::url());

        //多模块应用的视图设置
        $eventsManager = $di->get('eventsManager');
        $eventsManager->attach('application:afterStartModule',function($event,$app,$module) use($di){
            $router = $di->get('router');
            $curModule = $router->getModuleName();

            //$view = Engine::getModuleView($curModule);
            $view = Engine::setModuleViewer($curModule, $di);
            $di->setShared('view', $view);

        });
        $app->setEventsManager($eventsManager);
        //Phalcon\Tag::setDI($di);

        //缓存服务
        $di->setShared('cache', LkkCmponent::siteCache());

        //数据库-主从
        //压测时会出现SQLSTATE[08004] [1040] Too many connections
        $dbMaster = LkkCmponent::SyncDbMaster();
        $dbSlave = LkkCmponent::SyncDbSlave();
        $di->setShared('dbMaster', $dbMaster);
        $di->setShared('dbSlave', $dbSlave);

        //注入app,以便actioin里面访问
        $di->setShared('app', $app);
        $app->setDI($di);

        //phalcon处理
        $_uri = $request->get['_url'] ?? $request->server['request_uri'];
        //$resp = yield $app->handle($_uri);
        try {
            $resp = yield $app->handle($_uri);
        }catch (\Throwable $e) {
            $resp = "Error code: " . $e->getCode() . '<br>';
            $resp .= "Error message: " . $e->getMessage() . '<br>';
            $resp .= "Error file: " . $e->getFile() . '<br>';
            $resp .= "Error fileline: " . $e->getLine() . '<br>';
            $resp .= "Error trace: " . $e->getTraceAsString() . '<br>';
        }

        /*if($comConf->debug && $debug->hasError()) {

        }else{

        }*/

        if ($resp instanceof PwResponse) {
            if($resp->hasFile()) {
                $resp->sendFile();
                yield $response->end();
            }else{
                yield $resp->send();
                yield $response->end($resp->getContent());
            }
        } else if (is_string($resp)) {
            $response->end($resp);
        } else {
            $response->end('none');
        }
        //return $response->end('ok');

        //设置请求用时
        $useTime = CommonHelper::getMillisecond() - ($request->server['request_time_float'] ?? $request->server['request_time']) * 1000;
        $pwRequest->setUseMillisecond($useTime);

        if(self::isXhprofEnable(true) && $useTime > self::instance()->conf['sys_log']['slow_request']) {
            $xhprofData = xhprof_disable();
            $xhprofRuns = new \XHProfRuns_Default();

            $module = ucfirst($router->getModuleName());
            $controller = ucfirst($router->getControllerName());
            $action = ucfirst($router->getActionName());
            $reportFile = "{$module}{$controller}{$action}{$request->server['request_time_float']}";
            $runId = $xhprofRuns->save_run($xhprofData, 'profiler', str_replace('.','T',$reportFile));
            //$xhprofUrl = "http://127.0.0.1/monitor/xhprof/xhprof_html/index.php?run=" . $runId . '&source=profiler';
        }

        self::afterSwooleResponse($request, $pwRequest);
        yield self::logPv();

        unset($request, $response, $di, $app, $denAgent, $pwRequest, $pwResponse, $cookies, $session, $dispatcher);
        return true;
    }


    /**
     * 记录请求日志
     * @param \swoole_http_request $request
     *
     * @return bool
     */
    protected static function logRequest(\swoole_http_request $request, PwRequest $pwRequest) {
        $useTime = $pwRequest->getUseMillisecond();
        if($useTime > self::instance()->conf['sys_log']['slow_request']) {
            self::getLogger()->info("http request execute time[http_slow_request]:{$useTime}", $request->server);
        }

        //TODO 拆成队列，整除10,批量入库

        return true;
    }


    /**
     * 记录pv
     * @return int
     */
    protected static function logPv() {
        $res  = false;
        $key = self::instance()->conf['pv']['day_real_pv'];
        $redis = self::getPoolManager()->get('redis_site')->pop();
        if(is_object($redis) && ($redis instanceof Redis)) {
            $res = intval(yield $redis->incrBy($key, 1));
        }

        return $res;
    }



    protected static function afterSwooleResponse($swooleRequest, $phalconRequest) {
        self::logRequest($swooleRequest, $phalconRequest);

        $reqUuid = $phalconRequest->getRequestUuid();
        LkkCmponent::destroyRequests($reqUuid);
    }



    public static function onSwooleClose($serv, $fd, $fromId) {
        parent::onSwooleClose($serv, $fd, $fromId);

        //随机写日志
        if(mt_rand(0, 5)==1) {
            $di = SwooleServer::getServerDi();
            $eventManager = $di->get('eventsManager');
            $eventManager->fire('SwooleServer:onSwooleClose', self::instance());
        }

    }



    public static function onSwooleWorkerStop($serv, $workerId) {
        parent::onSwooleWorkerStop($serv, $workerId);

    }


    public static function onSwooleManagerStop($serv) {
        parent::onSwooleManagerStop($serv);

    }


    public static function onSwooleShutdown($serv) {
        parent::onSwooleShutdown($serv);

    }


    /**
     * 当检测到其他进程正停止服务时
     */
    public static function onStopping() {
        echo "onStopping ...\r\n";

        $shareTable = SwooleServer::getShareTable();
        $shareTable->setSubItem('server', ['stopping'=>1]);

        //写日志
        $di = SwooleServer::getServerDi();
        $eventManager = $di->get('eventsManager');
        $eventManager->fire('SwooleServer:onSwooleClose', self::instance());

    }






}