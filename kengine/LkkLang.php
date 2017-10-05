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
use Lkk\LkkService;

class LkkLang extends LkkService {

    private $_config = [];
    protected $_cache = [];


    public function __construct(array $vars = []) {
        parent::__construct($vars);

        $this->_config = getConf('lang')->toArray();
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


    /**
     * 加载语言文件
     * @param string $lang
     *
     * @return NativeArray
     */
    private function load($lang='') {
        $content = [];

        //系统语言文件
        $sysfile = $this->_config['systemdir'] . $lang . PHPEXT;
        if( file_exists($sysfile)) {
            $messages = require $sysfile;
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

