<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/10/2
 * Time: 10:41
 * Desc: -index控制器
 */


namespace Apps\Modules\Home\Controllers;

use Apps\Modules\Home\Controller;
use Lkk\Helpers\CommonHelper;

class IndexController extends Controller {

    /**
     * @title -默认动作
     * @desc  -动作说明
     */
    public function indexAction(){
        return $this->success('HomeModule-IndexController-IndexAction '.date('Ymd H:i:s'). ' ' .CommonHelper::getMillisecond());
    }


    /**
     * @title -测试
     * @desc  -测试
     */
    public function testAction() {
        return $this->success('HomeModule-IndexController-testAction');
    }



}