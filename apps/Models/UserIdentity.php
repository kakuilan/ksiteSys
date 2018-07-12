<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/4/30
 * Time: 18:48
 * Desc: -数据表模型 user_identity 用户身份表
 */


namespace Apps\Models;

class UserIdentity extends BaseModel {

    //默认字段
    public static $defaultFields = 'uid';


    /**
     * 获取性别数组
     * @return array
     */
    public static function getGenderArr() {
        return [
            '0' => '女',
            '1' => '男',
            '2' => '保密',
        ];
    }


    /**
     * 获取公开信息项数组
     * @return array
     */
    public static function getPublicItemsArr() {
        return [
            '1' => '性别',
            '2' => '生日',
            '4' => '真名',
            '8' => '地址',
            '16' => '邮箱',
            '32' => '手机',
        ];
    }


    public function initialize() {
        parent::initialize();
    }



}