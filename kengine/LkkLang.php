<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/9/17
 * Time: 12:30
 * Desc: -lkk 语言类
 */


namespace Kengine;
use Phalcon\Translate\Adapter\NativeArray;

class LkkLang {

    private $_config = [];
    protected $_cache = [];
    private static $_instance;


    /**
     * 实例化-单例
     * @return LkkLang
     */
    public static function instance() {
        if(empty(self::$_instance)) {
            self::$_instance = new LkkLang();
        }

        return self::$_instance;
    }


    /**
     * 构造函数
     * LkkLang constructor.
     */
    public function __construct() {
        $this->_config = getConf('lang')->toArray();
    }


    /**
     * 克隆
     */
    private function __clone() {
        //TODO
    }


    /**
     * 设置使用的语言
     * @param string $lang 语言名
     *
     * @return mixed
     */
    public function lang($lang = '') {
        if ($lang) {
            $this->_config['lang'] = strtolower(str_replace(array(' ', '_'), '-', $lang));
        }

        return $this->_config['lang'];
    }


    private function load($lang='') {
        $content = [];

        //系统语言文件
        $sysfile = $this->_config['systemdir'] . $lang . PHPEXT;
        if( file_exists($sysfile)) {
            $messages = require $sysfile;
            $content = array_merge($content, $messages);
        }

        //模块语言文件
        $module = getModuleName();
        $modulefile = MODULDIR . $module . DS . $this->_config['moduledir']. DS . $lang . PHPEXT;
        if($module && file_exists($modulefile) ) {
            $messages = require $modulefile;
            $content = array_merge($content, $messages);
        }

        $translate = new NativeArray([
            "content" => $content
        ]);

        return $this->_cache[$lang] = $translate;
    }


    /**
     * 获取缓存的翻译对象
     * @return array
     */
    public function getCache() {
        return $this->_cache;
    }


    /**
     * 执行翻译
     * @param string $string 要翻译转换的字符串
     * @param array  $values 要替换的变量值
     *
     * @return string
     */
    public function translate($string, array $values = []) {
        $translate = $this->load($this->_config['lang']);
        $string = $translate->query($string, $values);

        return empty($values) ? $string : strtr($string, $values);
    }



}