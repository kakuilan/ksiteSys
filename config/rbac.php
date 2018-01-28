<?php
/**
 * Copyright (c) 2018 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2018/1/7
 * Time: 18:11
 * Desc: -RBAC权限控制配置
 */


return [
    //前台会员权限控制
    'memberAuthGateway'             =>  'home/login', //会员验证网关
    'memberDefautlAction'           =>  'home/index/index', //会员成功登录默认跳转URL
    'memberNoauthModules'           =>  ['index'], //会员默认无需认证地的模块
    'memberNoauthActions'           =>  [ //会员默认无需认证的动作
        'home/login',
        'home/logout',
    ],

    //后台管理员权限控制
    'managerAuthGateway'            =>  'manage/index/login', //管理员验证网关,因为站点url带有'/',所以这里不用'/'开头
    'managerDefautlAction'          =>  'manage/index/index', //管理员成功登录默认跳转URL
    'managerNoauthModules'          =>  ['index'], //管理员默认无需认证地的模块
    'managerNoauthActions'          =>  [ //管理员默认无需认证的动作
        'manage/index/login',
        'manage/index/logout',
        'manage/index/loginsave',
    ],

];