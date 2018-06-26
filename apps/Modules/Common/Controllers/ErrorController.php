<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/10/2
 * Time: 10:57
 * Desc: -error控制器
 */


namespace Apps\Modules\Common\Controllers;

use Apps\Modules\Common\Controller;

class ErrorController extends Controller {

    public function initialize () {
        parent::initialize();

        $this->setHeaderSeo('网站名称', '关键词', '描述');

        //视图变量
        $this->view->setVars([
            'headerSeo' => $this->headerSeo,
        ]);

    }

    /**
     * @title -
     * @desc  -
     */
    public function indexAction(){
        return 'ErrorModule-IndexController-IndexAction';
    }


    /**
     * @title -notfound页面
     * @desc  -notfound页面
     */
    public function notfoundAction() {
        $this->response->setStatusCode(404, "Not Found")->sendHeaders();

        $viewpath =  $this->view->getViewsDir();
        $this->view->setViewsDir(dirname($viewpath));

        $this->view->pick('public/404');
    }



}