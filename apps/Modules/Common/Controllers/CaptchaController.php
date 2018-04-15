<?php
/**
 * Copyright (c) 2018 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2018/1/1
 * Time: 16:30
 * Desc: -验证码控制器
 */


namespace Apps\Modules\Common\Controllers;

use Apps\Modules\Common\Controller;
use Apps\Services\CaptchaService;

class CaptchaController extends Controller {


    /**
     * @title -生成验证码json
     * @desc  -生成验证码json
     * @return array|string
     */
    public function createAction() {
        $len = intval($this->request->get('len', null, 6));
        $type = intval($this->request->get('type', null, 0));
        $width = intval($this->request->get('width', null, 100));
        $height = intval($this->request->get('height', null, 30));

        $this->jsonRes['data'] = CaptchaService::createCode($len, $type, $width, $height);

        return $this->success();
    }

}