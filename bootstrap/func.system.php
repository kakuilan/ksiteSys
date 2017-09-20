<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/8/17
 * Time: 22:13
 * Desc: -系统函数
 */
 

use \Kengine\LkkConfig;
use \Lkk\Helpers\CommonHelper;

function mytest() {
    //TODO
}


/**
 * 获取配置
 * @param string $file 配置文件
 * @param string $key 配置键
 * @param mixed $default 默认值
 * @return mixed
 */
function getConf($file, $key = null, $default=null) {
    return LkkConfig::get($file, $key, $default);
}


/**
 * 获取当前站点URL [后面加/]
 * @return string
 */
function getSiteUrl() {
    static $url;
    if(is_null($url)) {
        $siteConf = getConf('site');
        if(isset($siteConf['url']) && !empty($siteConf['url'])) {
            $url = $siteConf['url'];
        }elseif(isset($_SERVER['HTTP_HOST'])){
            $url = parse_url(CommonHelper::getUrl());
            $url = $url['scheme'] .'://' . $url['host'];
        }else{
            $url = '';
        }
        $url = rtrim(strtolower($url), '/') . '/';
    }

    return $url;
}

