<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/4/30
 * Time: 20:51
 * Desc: -数据表模型 adm_user 管理员表
 */


namespace Apps\Models;

class AdmUser extends BaseModel {


    /**
     * 连表的用户字段
     * @var array
     */
    public static $joinUsrFields = [
        self::class . ".*",
        "u.status AS user_status",
        "u.mobile_status",
        "u.email_status",
        "u.type AS user_type",
        "u.mobile",
        "u.email",
        "u.username",
    ];

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
            '0' => '锁定',
            '1' => '正常',
        ];
    }


    /**
     * 根据UID获取管理员信息(连表)
     * @param int $uid
     * @return bool|\Phalcon\Mvc\ModelInterface
     */
    public static function getInfoByUid(int $uid=0) {
        if(empty($uid)) return false;

        $usr = UserBase::class;
        $adm = self::class;

        $result = self::query()
            ->columns(self::$joinUsrFields)
            ->leftJoin($usr, "u.uid = {$adm}.uid", 'u')
            ->where("{$adm}.uid = :uid: ", ['uid'=>$uid])
            ->limit(1)
            ->execute();

        return ($result->count()>0) ? $result->getFirst() : false;
    }


    /**
     * 根据Username获取管理员信息(连表)
     * @param string $str
     * @return bool|\Phalcon\Mvc\ModelInterface
     */
    public function getInfoByUsername(string $str='') {
        if(empty($str)) return false;

        $usr = UserBase::class;
        $adm = self::class;

        $result = self::query()
            ->columns(self::$joinUsrFields)
            ->leftJoin($usr, "u.uid = {$adm}.uid", 'u')
            ->where("{$usr}.username = :username: ", ['username'=>$str])
            ->limit(1)
            ->execute();

        return ($result->count()>0) ? $result->getFirst() : false;
    }


    /**
     * 根据Email获取管理员信息(连表)
     * @param string $str
     * @return bool|\Phalcon\Mvc\ModelInterface
     */
    public function getInfoByEmail(string $str='') {
        if(empty($str)) return false;

        $usr = UserBase::class;
        $adm = self::class;

        $result = self::query()
            ->columns(self::$joinUsrFields)
            ->leftJoin($usr, "u.uid = {$adm}.uid", 'u')
            ->where("{$usr}.email = :email: ", ['email'=>$str])
            ->limit(1)
            ->execute();

        return ($result->count()>0) ? $result->getFirst() : false;
    }





}