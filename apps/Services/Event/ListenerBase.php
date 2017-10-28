<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/10/28
 * Time: 16:40
 * Desc: -服务事件监听基类
 */


namespace Apps\Services\Event;

use Lkk\LkkObject;

class ListenerBase extends LkkObject {

    public function __construct(array $vars = []) {
        parent::__construct($vars);
    }

    public function __debugInfo() {
        // TODO: Implement __debugInfo() method.
    }



}

