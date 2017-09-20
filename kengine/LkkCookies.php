<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/9/17
 * Time: 15:00
 * Desc: -
 */


namespace Kengine;

use Lkk\Phalwoo\Phalcon\Http\Response\Cookies as PwCookies;
use Lkk\Phalwoo\Phalcon\Http\Cookie;

class LkkCookies extends PwCookies {


    /**
     * 获取带前缀的cookie名称
     * @param $name
     *
     * @return string
     */
    public static function getPrefixedName($name) {
        $conf = getConf('cookie');
        $prefixedName = $conf->pre .$name;
        return $prefixedName;
    }


    public function set($name, $value = null, $expire = 0, $path = "/", $secure = false, $domain = null, $httpOnly = false, $encrypt=null) {
        $conf = getConf('cookie');
        if(empty($path)) $path = $conf->path;
        if(empty($domain)) $domain = $conf->domain;
        if(empty($expire)) $expire = $conf->lifetime;

        return parent::set(self::getPrefixedName($name), $value, $expire, $path, $secure, $domain, $httpOnly, $encrypt);
    }


    public function get($name, $encrypt=null) {
        $cookie = parent::get(self::getPrefixedName($name), $encrypt);
        $res = $cookie->getValue();
        $len = strlen($res);
        if($len%2==0 && $len>=88 && substr($res,-1)=='=') {//加密的
            $crypt = $this->_dependencyInjector->getShared('crypt');
            $encryptValue = $crypt->encryptBase64((string)$res);
            if(!empty($encryptValue)) $res = $encryptValue;
        }

        return $res;
    }


    public function has($name) {
        return parent::has(self::getPrefixedName($name));
    }


    public function del($name) {
        $res = false;
        $prefixedName = self::getPrefixedName($name);
        $cookie = parent::get($prefixedName);
        if($cookie) {
            $conf = getConf('cookie');
            $res = parent::set($prefixedName, null, -86400,  $conf->path, false, $conf->domain, false, false)
                && $cookie->delete();
        }

        return $res;
    }

}