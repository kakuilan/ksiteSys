<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/12/26
 * Time: 14:04
 * Desc: 登录配置
 */

return [
    //通用
    'agentFpName'       => 'uafp', //浏览器指纹参数名称
    'tokenName'         => 'auth_token', //token参数名称,前台和API使用
    'tokenLife'         => 86400, //token有效期,秒
    'rootUids'          => [1], //超级管理员UID数组

    //前台会员
    'memberLoginSession'            =>  'loginMember', //已登录会员session名称
    'memberRolesSession'            =>  'memberRoleids', //已登录会员角色session名称
    'memberAuthCookie'              =>  'auth_token', //已登录会员验证cookie(后台使用,与tokenName相同)
    'memberUserCookie'              =>  'user', //已登录会员用户cookie(前端使用)
    'memberCookieLifetime'          =>  960, //已登录会员cookie生存期(大于redis-session的有效期),秒
    'memberRememberLife'            =>  604800, //会员选[记住我],cookie有效期,秒,7天
    'memberFailsTime'               =>  1800, //会员登录失败时间30分钟内
    'memberFailsLock'               =>  5, //会员超过N次登录失败即锁定账号


    //后台管理员
    'managerLoginSession'           =>  'loginManager', //已登录管理员session名称
    'managerRolesSession'           =>  'managerRoleids', //已登录管理员角色session名称
    'managerAuthCookie'             =>  'admn', //已登录管理员验证cookie(后台使用)
    'managerCookieLifetime'         =>  960, //已登录管理员cookie生存期,秒
    'managerRememberLife'           =>  86400, //管理员选[记住我],cookie有效期,秒,1天
    'managerFailsTime'              =>  1800, //管理员登录失败时间30分钟内
    'managerFailsLock'              =>  5, //管理员超过N次登录失败即锁定账号



];