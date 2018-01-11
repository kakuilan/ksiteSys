<?php
/**
 * Copyright (c) 2018 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2018/1/1
 * Time: 16:30
 * Desc: -验证码控制器
 */


namespace Apps\Modules\Common\Controllers;

use Kengine\LkkController;
use Apps\Services\CaptchaService;

class CaptchaController extends LkkController {


    /**
     * @title -生成验证码json
     * @desc  -生成验证码json
     * @return array|string
     */
    public function createAction() {
        $len = intval($this->request->get('len', null, 6));

        $this->jsonRes['data'] = CaptchaService::createCode($len);

        return $this->success();
    }

}