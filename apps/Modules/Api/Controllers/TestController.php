<?php
/**
 * Copyright (c) 2018 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2018/4/17
 * Time: 22:47
 * Desc: -测试
 */


namespace Apps\Modules\Api\Controllers;

use Apps\Modules\Api\Controller;

class TestController extends Controller {


    /**
     * @title -默认动作
     * @desc  -动作说明
     */
    public function indexAction(){
        $data = [
            'url' => 'api/test/index',
            'date' => date('Y-m-d H:i:s'),
        ];

        return $this->success($data);
    }


    public function langAction() {
        $aa = "HELLOWORLD";
        $bb = lang($aa);
        $cc = "Hello :user";
        $dd = lang($cc, [':user'=>'lkk']);
        $ee = lang(401);

        var_dump($bb, $dd, $ee);
    }




}