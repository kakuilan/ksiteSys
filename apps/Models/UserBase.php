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

    public static $joinAdmFields = [
        self::class .".*",
        "a.uid AS adm_uid",
        "a.level AS adm_level",
        "a.status AS adm_status",
        "a.logins AS adm_logins",
        "a.login_fails AS adm_login_fails",
        "a.last_login_ip AS adm_last_login_ip",
        "a.last_login_time AS adm_last_login_time",
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


    /**
     * 根据Username获取用户基本信息
     * @param string $str
     * @return \Phalcon\Mvc\Model|bool
     */
    public static function getInfoByUsername(string $str='') {
        $res = self::findFirst([
            'columns'    => '*',
            'conditions' => 'username = ?1 ',
            'bind'       => [
                1 => $str,
            ]
        ]);

        return $res;
    }


    /**
     * 根据Email获取用户基本信息
     * @param string $str
     * @return \Phalcon\Mvc\Model|bool
     */
    public static function getInfoByEmail(string $str='') {
        $res = self::findFirst([
            'columns'    => '*',
            'conditions' => 'email = ?1 ',
            'bind'       => [
                1 => $str,
            ]
        ]);

        return $res;
    }


    /**
     * 根据username联合获取管理员信息
     * @param string $str
     * @param bool $check 严格检查adm是否存在
     * @return bool|\Phalcon\Mvc\ModelInterface
     */
    public static function joinAdmInfoByUsername(string $str='', bool $check=false) {
        if(empty($str)) return false;

        $usr = self::class;
        $adm = AdmUser::class;

        $query = self::query()
            ->columns(self::$joinAdmFields)
            ->leftJoin($adm, "a.uid = {$usr}.uid", 'a');

        if($check) {
            $query->where("{$usr}.username = :username: AND a.uid>0 ", ['username'=>$str]);
        }else{
            $query->where("{$usr}.username = :username: ", ['username'=>$str]);
        }

        $result = $query->limit(1)->execute();

        return ($result->count()>0) ? $result->getFirst() : false;
    }


    /**
     * 根据username获取管理员信息
     * @param string $str
     * @return bool|\Phalcon\Mvc\ModelInterface
     */
    public static function getAdmByUsername(string $str='') {
        if(empty($str)) return false;

        return self::joinAdmInfoByUsername($str, true);
    }





}