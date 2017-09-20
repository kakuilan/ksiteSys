<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/9/17
 * Time: 12:29
 * Desc: -lkk Cli命令行应用类
 */


namespace Kengine;

use Phalcon\CLI\Console;
use Phalcon\DiInterface;
use Phalcon\Events\Manager as EventsManager;

class LkkConsole extends Console {

    public function __construct(DiInterface $di = null) {

    }


    public function __destruct() {
        $eventsManager = $this->getEventsManager();
        if(is_object($eventsManager)) {
            $eventsManager->fire('cliapp:finish', null);
        }

    }

}