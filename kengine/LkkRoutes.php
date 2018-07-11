<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/9/17
 * Time: 12:36
 * Desc: -lkk 路由设置 抽象类
 */


namespace Kengine;

use Lkk\Helpers\UrlHelper;
use Lkk\LkkObject;
use Phalcon\Mvc\Router\Group;

abstract class LkkRoutes extends LkkObject {

    //抽象方法
    abstract protected function add(Group &$rouGroups);


    /**
     * 根据URL获取路由信息数组
     * @param string $url
     * @return array
     */
    public static function getRouteInfoByUrl(string $url='') {
        $conf = getConf('site');
        $res = [
            'module' => $conf->defaultModule,
            'controller' => $conf->defaultController,
            'action' => $conf->defaultAction,
        ];

        $url = trim($url);
        if($url) {
            $url = UrlHelper::formatUrl($url);
            $arr = parse_url($url);

            $url = $arr['path'];
            $url = trim($url, '/');
            $arr = explode('/', $url);
            var_dump($arr);

        }

        unset($conf);
        return $res;
    }


}