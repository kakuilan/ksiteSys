<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/9/17
 * Time: 12:36
 * Desc: -lkk 路由设置 抽象类
 */


namespace Kengine;
use Phalcon\Mvc\Router\Group;
use Lkk\LkkObject;

abstract class LkkRoutes extends LkkObject {

    //抽象方法
    abstract protected function add(Group &$rouGroups);

}