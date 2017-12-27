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
     * 获取手机号状态数组
     * @return array
     */
    public static function getMobileStatusArr( ){
        return [
            '-1' => '已解绑',
            '0' => '未验证',
            '1' => '已验证',
        ];
    }


    /**
     * 获取邮箱状态数组
     * @return array
     */
    public static function getEmailStatusArr( ){
        return [
            '-1' => '已解绑',
            '0' => '未验证',
            '1' => '已验证',
        ];
    }


    /**
     * 获取用户类型数组
     * @return array
     */
    public static function getTypesArr() {
        return [
            '0' => '普通用户',
            '1' => '测试用户',
            '2' => '后台用户',
            '3' => '接口用户',
        ];
    }






}