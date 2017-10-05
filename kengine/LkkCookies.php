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
     * 获取cookie的值
     * @param      $name
     * @param null $encrypt
     *
     * @return mixed
     */
    public function getValue($name, $encrypt=null) {
        $cookie = parent::get($name, $encrypt);
        $res = $cookie->getValue();
        $len = strlen($res);
        if($len%2==0 && $len>=88 && substr($res,-1)=='=') {//加密的
            $crypt = $this->_dependencyInjector->getShared('crypt');
            $encryptValue = $crypt->encryptBase64((string)$res);
            if(!empty($encryptValue)) $res = $encryptValue;
        }

        return $res;
    }


    /**
     * 重写del方法
     * @param $name
     *
     * @return bool
     */
    public function del($name) {
        $res = false;
        $cookie = parent::get($name);
        if($cookie) {
            $res = parent::set($name, null, -86400,  $this->conf['path'], false, $this->conf['domain'], false, false)
                && $cookie->delete();
        }

        return $res;
    }

}