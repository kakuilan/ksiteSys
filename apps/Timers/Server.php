<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/10/1
 * Time: 19:35
 * Desc: -服务相关定时任务
 */


namespace Apps\Timers;

use Kengine\Server\LkkServer;
use Lkk\Phalwoo\Server\SwooleServer;

class Server extends BaseTimer {

    public function __construct(array $vars = []) {
        parent::__construct($vars);
    }


    /**
     * 检测服务是否正停止
     */
    public function checkStopping() {
        //pid被删除,表明其他进程正试图停止服务
        if(!file_exists(LkkServer::getPidFile())) {
            LkkServer::onStopping();

            //TODO 更多
        }
    }


    /**
     * 将session队列写入redis
     */
    public function writeSession() {
        $sessionWork = SessionWork::getInstance();
        if($sessionWork->chkDoing('writeSession')) {
            if(SwooleServer::isOpenDebug()) {
                $mesg = "writeSession work is doing... \r\n";
                echo $mesg;
            }
        }else{
            $sessionWork->writeSession();
        }
    }


    /**
     * session数据清理
     */
    public function sessionGc() {
        $sessionWork = SessionWork::getInstance();
        if($sessionWork->chkDoing('gc')) {
            if(SwooleServer::isOpenDebug()) {
                $mesg = "session gc work is doing... \r\n";
                echo $mesg;
            }
        }else{
            $sessionWork->gc();
        }
    }




}