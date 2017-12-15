#!/usr/bin/env php
<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/8/17
 * Time: 22:11
 * Desc: -服务器启动脚本
 */


require __DIR__ . '/../bootstrap/define.php';
require __DIR__ . '/../bootstrap/func.system.php';

//载入命名空间
$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->addPsr4('Apps\\',      APPSDIR);
$loader->addPsr4('Kengine\\',   KENGDIR);
$loader->addPsr4('Tests\\',     TESTDIR);

//TODO
$pid = getmypid();
echo "this pid:[{$pid}]\r\n";
Kengine\Engine::runWebApp();