<?php
/**
 * Copyright (c) 2018 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2018/1/1
 * Time: 17:21
 * Desc: -验证码服务类
 */


namespace Apps\Services;

use Gregwar\Captcha\CaptchaBuilder;
use Lkk\Helpers\EncryptHelper;
use Lkk\Helpers\StringHelper;
use Kengine\LkkCmponent;

class CaptchaService extends ServiceBase {


    /**
     * 生成验证码数组
     * @param int $len 验证码长度
     * @param string $type 字符类型,参考StringHelper::randString
     * @param int $width 宽
     * @param int $height 高
     * @param bool $distortion 是否扭曲
     *
     * @return array
     */
    public static function createCode($len = 4, $type=0, $width=100, $height=30, $distortion=false) {
        if($len<4) $len = 4;
        if($len>10) $len = 10;

        $rang = range(0, 5);
        if(!in_array($type, $rang)) $type = 0;
        $code = StringHelper::randString($len, $type);

        $crypt = LkkCmponent::crypt();
        $encode = $crypt->encryptBase64($code);

        $builder = new CaptchaBuilder($code);
        $builder->setDistortion($distortion);
        $builder->build($width, $height);
        $img = $builder->inline();

        $data = [
            'encode' => EncryptHelper::base64urlEncode($encode),
            'img' => $img, //base64图片
        ];

        return $data;
    }


    /**
     * 检查验证码
     * @param string $code 待检查的验证码
     * @param string $encode 被加密的校验码
     *
     * @return bool
     */
    public function validateCode($code='', $encode='') {
        if(empty($code)) {
            $this->setError('验证码不能为空');
            return false;
        }elseif (empty($encode)) {
            $this->setError('校验码不能为空');
            return false;
        }

        $crypt = LkkCmponent::crypt();
        $encode = EncryptHelper::base64urlDecode($encode);
        $decode = $crypt->decryptBase64($encode);

        if(empty($decode) || strtolower($decode)!=strtolower($code)) {
            $this->setError('验证码错误');
            return false;
        }

        return true;
    }





}