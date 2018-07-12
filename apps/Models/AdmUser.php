<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/4/30
 * Time: 20:51
 * Desc: -数据表模型 adm_user 管理员表
 */


namespace Apps\Models;

use Phalcon\Mvc\Model\Query\Builder as QueryBuilder;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Phalcon\Mvc\Model\ManagerInterface;

class AdmUser extends BaseModel {

    //默认字段
    public static $defaultFields = 'uid,site_id,level,status,password,logins,login_fails,last_login_ip,last_login_time';
    //分页字段
    public static $pageFields = 'uid,site_id,level,status,logins,login_fails,last_login_ip,last_login_time,create_time,update_time';

    //连表用户基础字段
    public static $joinUserBaseFields = 'status AS user_status,mobile_status,email_status,type AS user_type,mobile,email,username';
    //连表用户信息字段
    public static $joinUserInfoFields = 'nickname,sign,avatar';


    public function initialize() {
        parent::initialize();
    }


    /**
     * 获取状态数组
     * @return array
     */
    public static function getStatusArr() {
        return [
            '1' => '正常',
            '-1' => '禁登录',
            '0' => '锁定',
        ];
    }


    /**
     * 获取级别数组
     * @return array
     */
    public static function getLevelArr() {
        return [
            '0' => '普通管理员',
            '3' => '授权管理员',
            '6' => '站长',
            '9' => '开发者',
        ];
    }


    /**
     * 获取单个连表user的用户字段
     * @return array
     */
    public static function getJoinUserFields() {
        $fieldsA = self::makeAliaFields(self::$defaultFields, self::class);
        $fieldsU = self::makeAliaFields(self::$joinUserBaseFields, 'u');
        $fieldsI = self::makeAliaFields(self::$joinUserInfoFields, 'i');
        $fields = array_merge($fieldsA, $fieldsU, $fieldsI);

        return $fields;
    }


    /**
     * 获取分页连表user的用户字段
     * @return array
     */
    public static function getPageFields() {
        $fieldsA = self::makeAliaFields(self::$pageFields, 'a');
        $fieldsU = self::makeAliaFields(self::$joinUserBaseFields, 'u');
        $fieldsI = self::makeAliaFields(self::$joinUserInfoFields, 'i');
        $fields = array_merge($fieldsA, $fieldsU, $fieldsI);

        return $fields;
    }


    /**
     * 根据UID获取管理员信息(连表),返回的用户记录必定是管理员
     * @param int $uid
     * @return bool|\Phalcon\Mvc\ModelInterface
     */
    public static function getInfoByUid(int $uid=0) {
        if(empty($uid)) return false;

        $usr = UserBase::class;
        $info = UserInfo::class;
        $adm = self::class;
        $fields = self::getJoinUserFields();

        $result = self::query()
            ->columns($fields)
            ->leftJoin($usr, "u.uid = {$adm}.uid", 'u')
            ->leftJoin($info, "i.uid = {$adm}.uid", 'i')
            ->where("{$adm}.uid = :uid: ", ['uid'=>$uid])
            ->limit(1)
            ->execute();

        return ($result->count()>0) ? $result->getFirst() : false;
    }


    /**
     * 根据Username获取管理员信息(连表),返回的用户记录必定是管理员
     * @param string $str
     * @return bool|\Phalcon\Mvc\ModelInterface
     */
    public static function getInfoByUsername(string $str='') {
        if(empty($str)) return false;

        $usr = UserBase::class;
        $info = UserInfo::class;
        $adm = self::class;
        $fields = self::getJoinUserFields();

        $result = self::query()
            ->columns($fields)
            ->leftJoin($usr, "u.uid = {$adm}.uid", 'u')
            ->leftJoin($info, "i.uid = {$adm}.uid", 'i')
            ->where("u.username = :username: AND {$adm}.uid>0", ['username'=>$str])
            ->limit(1)
            ->execute();

        return ($result->count()>0) ? $result->getFirst() : false;
    }


    /**
     * 根据Email获取管理员信息(连表),返回的用户记录必定是管理员
     * @param string $str
     * @return bool|\Phalcon\Mvc\ModelInterface
     */
    public static function getInfoByEmail(string $str='') {
        if(empty($str)) return false;

        $usr = UserBase::class;
        $info = UserInfo::class;
        $adm = self::class;
        $fields = self::getJoinUserFields();

        $result = self::query()
            ->columns($fields)
            ->leftJoin($usr, "u.uid = {$adm}.uid", 'u')
            ->leftJoin($info, "i.uid = {$adm}.uid", 'i')
            ->where("u.email = :email: AND {$adm}.uid>0", ['email'=>$str])
            ->limit(1)
            ->execute();

        return ($result->count()>0) ? $result->getFirst() : false;
    }


    /**
     * 根据关键词[用户名或邮箱]获取管理员信息(连表),返回的用户记录必定是管理员
     * @param string $str
     *
     * @return bool|\Phalcon\Mvc\ModelInterface
     */
    public static function getInfoByKeyword(string $str='') {
        if(empty($str)) return false;

        $usr = UserBase::class;
        $info = UserInfo::class;
        $adm = self::class;
        $fields = self::getJoinUserFields();

        $result = self::query()
            ->columns($fields)
            ->leftJoin($usr, "u.uid = {$adm}.uid", 'u')
            ->leftJoin($info, "i.uid = {$adm}.uid", 'i')
            ->where("(u.username = :username:  OR u.email = :email: ) AND {$adm}.uid>0", ['username'=>$str, 'email'=>$str])
            ->orderBy('u.uid asc')
            ->limit(1)
            ->execute();

        return ($result->count()>0) ? $result->getFirst() : false;
    }

    /**
     * 获取管理员列表分页对象
     * @param ManagerInterface $modelsManager
     * @param string $where 条件
     * @param array $binds 绑定参数
     * @param string $order 排序
     * @param int $limit 每页数量
     * @param int $page 页码
     * @return PaginatorQueryBuilder
     */
    public static function getAdminPages(ManagerInterface $modelsManager, $where='', $binds=[], $order='', $limit=10, $page=1) {
        $fields = self::getPageFields();
        $builder = $modelsManager->createBuilder()
            ->columns($fields)
            ->addFrom(self::class, 'a')
            ->leftJoin(UserBase::class, 'u.uid = a.uid', 'u')
            ->leftJoin(UserInfo::class, 'i.uid = a.uid', 'i');

        if(!empty($where)) {
            $builder->where($where, $binds);
        }

        if(!empty($order)) $builder->orderBy($order);

        return new PaginatorQueryBuilder(
            [
                "builder" => $builder,
                "limit"   => $limit,
                "page"    => $page
            ]
        );
    }


    /**
     * 根据uid检查是否正常的管理员
     * @param int $uid
     * @return bool
     */
    public static function checkNormalAdminByUid(int $uid=0) {
        if(empty($uid)) return false;

        $where = [
            'status' => '1',
            'uid' => $uid,
        ];

        $count = self::getCount($where);
        return boolval($count);
    }






}