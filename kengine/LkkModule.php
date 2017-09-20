<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/9/17
 * Time: 12:33
 * Desc: -lkk 模块类
 */


namespace Kengine;

use Phalcon\DiInterface;
use Phalcon\Dispatcher;
use Phalcon\Loader;
use Phalcon\Mvc\ModuleDefinitionInterface;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Volt;
use Phalcon\Mvc\View\Engine\Php as PhpEngine;


class LkkModule implements ModuleDefinitionInterface {


    /**
     * 注册模块自动加载
     * @param DiInterface|null $di
     *
     * @return bool
     */
    public function registerAutoloaders(DiInterface $di = null) {
        return true;
    }


    /**
     * 注册本模块服务
     * @param DiInterface $di
     *
     * @return bool
     */
    public function registerServices(DiInterface $di) {
        //设置模块的默认分发和控制器命名空间
        $refClass = new \ReflectionClass($this);
        $namespace = $refClass->getNamespaceName();
        $moduleName = strtolower(pathinfo($namespace)['basename']);
        $namespace .= ('cli'!=$moduleName) ? '\Controllers' : '\Tasks';
        $dispatcher = $di->get('dispatcher');
        $dispatcher->setDefaultNamespace($namespace);

        //404 notfound处理
        $eventsManager = $di->get('eventsManager');
        $eventsManager->attach("dispatch", function ($event, $dispatcher, $exception) use ($moduleName, $di) {
            //controller or action doesn't exist
            if ($event->getType() == 'beforeException') {
                $errCode = $exception->getCode();
                switch ($errCode) {
                    case Dispatcher::EXCEPTION_NO_DI:
                        break;
                    case Dispatcher::EXCEPTION_CYCLIC_ROUTING:
                    case Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
                    case Dispatcher::EXCEPTION_ACTION_NOT_FOUND:
                    case ('cli'==$moduleName && PHP_SAPI !== 'cli') : //非命令行下不能运行cli模块
                        $notfoundConf = (PHP_SAPI == 'cli' && 'cli'==$moduleName) ?
                            [
                                'namespace' => 'Apps\Modules\Cli\Tasks',
                                'module' => 'cli',
                                'task' => 'main',
                                'action' => 'notfound',
                            ]
                            :
                            [
                                'namespace' => 'Apps\Modules\Common\Controllers',
                                'module' => 'common',
                                'controller' => 'error',
                                'action' => 'notfound',
                            ];

                        $dispatcher->forward($notfoundConf);
                        Engine::setModuleView($di, $notfoundConf['module']); //重新设置视图
                        return false;
                        break;
                    default:
                        break;
                }
            }
        });
        $dispatcher->setEventsManager($eventsManager);
        $di->set('dispatcher', $dispatcher, true);

        unset($refClass,$namespace);

        return true;
    }




}