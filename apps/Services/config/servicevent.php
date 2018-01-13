<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/10/28
 * Time: 16:39
 * Desc: -服务事件绑定配置
 */
 
 
return [
    'ActionService' => [],
    'RbacService' => [],

    //用户服务类事件
    'UserService' => [
        //管理员登录成功后事件
        'afterManagerLoginSuccess' => [
            'Apps\Services\Event\UserListener',
        ],
        //管理员登录失败后事件
        'afterManagerLoginFail' => [
            'Apps\Services\Event\UserListener',
        ],
        //管理员退出后事件
        'afterManagerLogout' => [
            'Apps\Services\Event\UserListener',
        ],
    ],
];