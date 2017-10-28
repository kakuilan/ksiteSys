<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/10/28
 * Time: 15:17
 * Desc: -服务基类
 */


namespace Apps\Services;

use Lkk\LkkService;
use Phalcon\Events\Manager as EventsManager;


class ServiceBase extends LkkService {

    public static $eventsManager;

    /**
     * 构造函数
     */
    public function __construct($vars=[]) {
        parent::__construct($vars);

        //读取本服务类的事件配置,并绑定
        $eventPrefix = $this->getClassShortName();
        $eventConf = getConf('services/servicevent', $eventPrefix);
        if(!empty($eventConf) && is_array($eventConf)) {
            foreach ($eventConf as $eventType=>$listeners) {
                if(empty($eventType) || is_numeric($eventType)) continue;
                $eventName = "{$eventPrefix}:{$eventType}";
                foreach ($listeners as $listener) {
                    if(class_exists($listener)) {
                        $obj = new $listener();
                        $this->attachEvent($eventName, $obj);
                    }else{
                        $logNam = getConf('site', 'runErrorLog');
                        $logObj = getLogger($logNam);
                        $logObj->warning($eventPrefix .'服务类事件绑定错误,监听类'.$listener.'不存在');
                    }
                }
            }
        }


    }


    /**
     * 析构函数
     */
    public function __destruct() {

    }


    /**
     * 设置事件管理区
     */
    public function setEventsManager() {
        self::$eventsManager = new EventsManager();
    }


    /**
     * 获取事件管理器
     * @return mixed
     */
    public function getEventsManager() {
        if(is_null(self::$eventsManager)) {
            $this->setEventsManager();
        }

        return self::$eventsManager;
    }


    /**
     * 绑定事件
     * @param string $eventName 事件名称,形式为'uniqueName:method',uniqueName为唯一名,method为事件监听类触发的方法名
     * @param object|callable $listenerClass 实例化的事件处理监听类
     */
    public final function attachEvent($eventName, $listenerClass) {
        $eventsManager = $this->getEventsManager();
        $eventsManager->attach($eventName, $listenerClass);
    }


    /**
     * 触发事件
     * @param string $eventName 事件名称
     * @param null $data 要传递给监听类的数据
     * @param bool $cancelable 事件是否可取消
     * @param null $sourceClass 事件的来源类
     */
    public final function fireEvent($eventName, $data=null, $cancelable=true, $sourceClass=null) {
        if(strpos($eventName,':')===false) {
            $eventPrefix = $this->getClassShortName();
            $eventName = "{$eventPrefix}:{$eventName}";
        }

        if(is_null($sourceClass)) $sourceClass = $this;
        $eventsManager = $this->getEventsManager();
        $eventsManager->fire($eventName, $sourceClass, $data, $cancelable);
    }



}