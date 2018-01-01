<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/10/28
 * Time: 20:12
 * Desc: -index控制器
 */


namespace Apps\Modules\Manage\Controllers;

use Kengine\LkkController;
use Lkk\Helpers\CommonHelper;


/**
 * Class 后台首页控制器
 * @package Apps\Modules\Manage\Controllers
 */
class IndexController extends LkkController {


    public function initialize () {
        parent::initialize();

        $this->setHeaderSeo('管理后台', '关键词', '描述');

        //视图变量
        $this->view->setVars([
            'headerSeo' => $this->headerSeo,
        ]);

    }


    /**
     * @title -管理后台首页
     * @desc  -管理后台首页
     */
    public function indexAction(){
        //视图变量
        $this->view->setVars([
            'mainUrl' => makeUrl('manage/index/main'),
            'menuUrl' => makeUrl('manage/menu/authlist'),
        ]);

        //设置静态资源
        $this->assets->addCss('statics/css/adm-tab.css');
        $this->assets->addJs('statics/js/ace-elements.min.js');
        $this->assets->addJs('statics/js/ace.min.js');
        $this->assets->addJs('statics/js/lkkTabMenu.js');

        return null;
    }


    /**
     * @title -管理后台登录页
     * @desc  -管理后台登录页
     */
    public function loginAction() {

    }


    /**
     * @title -管理后台退出
     * @desc  -管理后台退出
     */
    public function logoutAction() {

    }


    /**
     * @title -后台主页
     * @desc  -后台主页
     */
    public function mainAction() {


    }



}