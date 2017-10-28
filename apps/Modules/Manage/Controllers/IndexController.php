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

class IndexController extends LkkController {


    /**
     * 默认动作
     * 动作说明
     */
    public function indexAction(){
        return 'ManageModule-IndexController-IndexAction '.date('Ymd H:i:s'). ' ' .CommonHelper::getMillisecond();
    }


    public function testAction() {
        return $this->fail('shibai4444');
        /*echo 3333;
        var_dump($this->response);*/
    }



}