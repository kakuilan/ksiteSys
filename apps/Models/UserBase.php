<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/4/23
 * Time: 19:11
 * Desc: -数据表模型 user_base 用户基本表
 */


namespace Apps\Models;

class UserBase extends BaseModel {

    public function initialize() {
        parent::initialize();
    }


    /**
     * 获取状态数组
     * @return array
     */
    public static function getStatusArr() {
        return [
            '-1' => '禁登录',
            '0' => '待激活',
            '1' => '禁发布',
            '2' => '禁评论',
            '10' => '正常',
        ];
    }


    /**
     * 生成密码值
     * @param string $str
     *
     * @return bool|string
     */
    public static function makePasswdHash($str='') {
        $str = trim($str);
        if(empty($str)) return $str;
        $res = password_hash($str, PASSWORD_BCRYPT);
        return $res;
    }



}