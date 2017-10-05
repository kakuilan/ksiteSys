<?php
/**
 * Copyright (c) 2016 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2016/12/25
 * Time: 21:29
 * Desc: -视图模板配置
 */


return [
    //禁用视图的模块
    'denyModules'   => [
        'api',
        'cli',
        'task',
    ],

    //编译目录
    'compiledPath' => RUNTDIR . 'volt/',

];