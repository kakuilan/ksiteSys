<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/10/1
 * Time: 19:34
 * Desc: -定时任务基本类
 */


namespace Apps\Timers;

use Lkk\LkkService;

class BaseTimer extends LkkService {

    public function __construct(array $vars = []) {
        parent::__construct($vars);
    }

}