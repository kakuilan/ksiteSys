<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/9/17
 * Time: 11:51
 * Desc: -应用模块配置
 */


return [
    //API模块
    'api'       => [
        'className'    => 'Apps\Modules\Api\Module',
        'path'         => APPSDIR. 'Modules/Api/Module.php',
        'alias'        => 'api', //模块别名
    ],

    //命令行
    'cli'       => [
        'className'    => 'Apps\Modules\Cli\Module',
        'path'         => APPSDIR. 'Modules/Cli/Module.php',
        'alias'        => 'cli', //模块别名
    ],


    //公共模块
    'common'       => [
        'className'    => 'Apps\Modules\Common\Module',
        'path'         => APPSDIR. 'Modules/Common/Module.php',
        'alias'        => 'common', //模块别名
    ],


    //home模块(默认前台)
    'home'       => [
        'className'    => 'Apps\Modules\Home\Module',
        'path'         => APPSDIR. 'Modules/Home/Module.php',
        'alias'        => 'index', //模块别名
    ],


    //manage模块(后台管理)
    'manage'       => [
        'className'    => 'Apps\Modules\Manage\Module',
        'path'         => APPSDIR. 'Modules/Manage/Module.php',
        'alias'        => 'manage', //模块别名
    ],


    //会员模块
    'member'       => [
        'className'    => 'Apps\Modules\Member\Module',
        'path'         => APPSDIR. 'Modules/Member/Module.php',
        'alias'        => 'member', //模块别名
    ],


];