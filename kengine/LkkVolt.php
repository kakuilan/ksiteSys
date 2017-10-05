<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/9/17
 * Time: 12:37
 * Desc: -lkk Volt扩展
 */


namespace Kengine;

use Phalcon\Mvc\View\Engine\Volt;
use Phalcon\DiInterface;
use Phalcon\Mvc\View\Exception;

class LkkVolt extends Volt {

    /**
     * Dependency Injector
     *
     * @var null|DiInterface
     * @access protected
     */
    protected $_dependencyInjector;


    /**
     * Sets the dependency injector
     *
     * @param DiInterface $dependencyInjector
     * @throws Exception
     */
    public function setDI(DiInterface $dependencyInjector) {
        if (!is_object($dependencyInjector)||
            $dependencyInjector instanceof DiInterface === false) {
            throw new Exception('Dependency Injector is invalid');
        }

        $this->_dependencyInjector = $dependencyInjector;
    }

    /**
     * Returns the internal dependency injector
     *
     * @return DiInterface|null
     */
    public function getDI() {
        return $this->_dependencyInjector;
    }



    /**
     * 获取静态资源版本号
     * @return array
     */
    public static function staticVersion() {
        $res = [
            'name' => '_sv',
            'version' => date('ymdhis'),
        ];
        return $res;
    }


    /**
     * 添加扩展函数(供模板里面使用)
     */
    public function extendFuncs() {
        $compiler = $this->getCompiler();

        //添加PHP自带的explode函数
        $compiler->addFunction('explode', 'explode');

    }



}