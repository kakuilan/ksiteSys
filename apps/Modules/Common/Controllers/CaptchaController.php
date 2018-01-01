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
use Gregwar\Captcha\CaptchaBuilder;
use Lkk\Helpers\StringHelper;

class CaptchaController extends LkkController {


    /**
     * @title -生成验证码json
     * @desc  -生成验证码json
     * @return array|string
     */
    public function createAction() {
        $len = intval($this->request->get('len', 4));
        if($len<4) $len = 4;
        if($len>10) $len = 10;

        $code = StringHelper::randString($len, 0);
        $encode = $this->crypt->encryptBase64($code);

        $builder = new CaptchaBuilder($code);
        $builder->build();
        $img = $builder->inline();

        $data = [
            'encode' => $encode,
            'img' => $img,
        ];
        $this->jsonRes['data'] = $data;
        return $this->success();
    }

}