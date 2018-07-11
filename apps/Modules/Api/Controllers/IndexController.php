<?php
/**
 * Copyright (c) 2018 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2018/3/6
 * Time: 21:57
 * Desc: -首页控制器
 */


namespace Apps\Modules\Api\Controllers;

use Apps\Modules\Api\Controller;

class IndexController extends Controller {


    /**
     * @title -默认动作
     * @desc  -动作说明
     */
    public function indexAction(){
        $data = [
            'url' => 'api/index/index',
            'date' => date('Y-m-d H:i:s'),
        ];

        return $this->success($data);
    }


    /**
     * @title -notfound页面
     * @desc  -notfound页面
     */
    public function notfoundAction() {
        return $this->fail(404);
    }


    /**
     * @title -error页面
     * @desc  -error页面
     */
    public function errorAction() {
        return $this->fail();
    }



}