<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/10/2
 * Time: 10:57
 * Desc: -error控制器
 */


namespace Apps\Modules\Common\Controllers;

use Kengine\LkkController;


class ErrorController extends LkkController {


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