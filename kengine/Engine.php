<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/9/17
 * Time: 10:20
 * Desc: -
 */


namespace Kengine;

use Phalcon\Loader;
use Phalcon\Mvc\View;
use Kengine\Server\LkkServer;
use Kengine\LkkVolt;
use Lkk\Helpers\CommonHelper;
use Lkk\Helpers\DirectoryHelper;


class Engine {

    /**
     * 初始化
     */
    public static function init() {
        $comConf = getConf('common');

        //开启调试
        if($comConf['debug']) {
            define('SYSDEBUG', true);
            error_reporting(E_ALL);
            ini_set('display_errors', 1);

        }else{
            define('SYSDEBUG', false);
            error_reporting(0); //关闭所有PHP错误报告
            ini_set('display_errors', 0); //禁止把错误输出到页面
        }

        //设置时区
        date_default_timezone_set($comConf['timezone']);
        mb_substitute_character('none');

        register_shutdown_function('\Lkk\Helpers\CommonHelper::errorHandler', LOGDIR.'phperr.log');

        self::defineAppConstant();
        self::loadNamespaces();
    }


    /**
     * 定义项目应用常量
     */
    public static function defineAppConstant() {
        //定义项目URL相关常量
        $siteConf = getConf('site');
        $url = $siteConf['sourceFullUrl'] ? getSiteUrl() : '/';

        define('SITE_URL',      $url );
        define('HTML_URL',      $url .'html'     . DS ); //url-html html生成目录
        define('STATIC_URL',    $url .'statics'  . DS ); //url-static 静态资源目录
        define('UPLOAD_URL',    $url .'upload'   . DS ); //url-upload 上传资源目录
        define('CSS_URL',       $url .'statics/css'   . DS ); //url-css css资源目录
        define('JS_URL',        $url .'statics/js'    . DS ); //url-js js资源目录

    }


    public static function loadNamespaces() {
        $loader = new Loader();
        $workNamespaces = [
            'Apps\Modules'      => MODULDIR,
            'Apps\Models'       => MODELDIR,
            'Apps\Services'     => APPSDIR . 'services/',
        ];

        //加载各模块目录
        $allmodules = getConf('modules');
        foreach ($allmodules as $name=>$module) {
            $moduleClassName = substr($module['className'], 0, strrpos ($module['className'],'\\'));
            $modulePath = str_replace('/Module.php', '', $module['path']);

            if('cli' ==$name) {
                $moduleNamespaces = [
                    "{$moduleClassName}" => "{$modulePath}",
                    "{$moduleClassName}\Tasks" => "{$modulePath}/Tasks/",
                ];
            }else{
                $moduleNamespaces = [
                    "{$moduleClassName}" => "{$modulePath}",
                    "{$moduleClassName}\Controllers" => "{$modulePath}/Controllers/",
                ];
            }

            $workNamespaces = array_merge($workNamespaces, $moduleNamespaces);
        }

        $loader->registerNamespaces($workNamespaces);
        $loader->register();
        return true;
    }



    /**
     * 设置模块的视图服务
     * @param        $di
     * @param string $moduleName
     *
     * @return bool
     */
    public static function setModuleView($di, $moduleName='') {
        if(empty($moduleName)) return false;

        //视图
        $view = new View();
        $viewConf = getConf('view')->toArray();
        if(in_array($moduleName, $viewConf['denyModules'])) {
            //设置渲染等级
            $view->setRenderLevel(View::LEVEL_NO_RENDER);
            $view->disable();
        }else{
            //视图模板目录
            $viewpath = APPSDIR . 'views/' . getConf('common','theme') . "/{$moduleName}/";
            $comppath = RUNTDIR . 'volt/';
            if(!file_exists($comppath)) {
                DirectoryHelper::mkdirDeep($comppath);
            }

            $view->setViewsDir($viewpath);
            $view->registerEngines([
                '.php' => function($view, $di) use($comppath) {
                    $volt = new LkkVolt($view, $di);
                    $volt->setOptions([
                        //模板缓存目录
                        'compiledPath' => $comppath,
                        //编译后的扩展名
                        'compiledExtension' => '',
                        //编译分隔符
                        'compiledSeparator' => '%',
                    ]);

                    //添加自定义模板函数
                    $volt->extendFuncs();
                    return $volt;
                }
            ]);
        }

        $di->setShared('view', $view);

        return true;
    }



    /**
     * 运行web应用
     */
    public static function runWebApp() {
        self::init();

        LkkServer::parseCommands();
        LkkServer::instance()->setConf(getConf('server')->toArray())->run();
    }








}