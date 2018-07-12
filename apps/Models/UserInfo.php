<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/4/30
 * Time: 18:46
 * Desc: -数据表模型 user_info 用户信息表
 */


namespace Apps\Models;

class UserInfo extends BaseModel {

    //默认字段
    public static $defaultFields = 'uid,last_activ_time,create_time,follows,fans,posts,invites,visits,used_file_size,free_file_size,nickname,sign,profile,avatar';
    //基本字段
    public static $baseFields = 'uid,free_file_size,nickname,avatar';
    //分页字段
    public static $pageFields = 'uid';

    //连表用户基础字段
    public static $joinUserBaseFields = 'site_id,type,status AS user_status,mobile_status,email_status,mobile,email,username';
    //连表管理员字段
    public static $joinUserAdmFields = 'uid AS adm_uid,level AS adm_level,status AS adm_status';


    public function initialize() {
        parent::initialize();
    }


    /**
     * 获取用户连表字段
     * @param string $userFields UserInfo字段
     * @return array
     */
    public static function getJoinUserFields($userFields='') {
        if(empty($userFields)) $userFields = self::$defaultFields;

        $fieldsI = self::makeAliaFields($userFields, self::class);
        $fieldsU = self::makeAliaFields(self::$joinUserBaseFields, 'u');
        $fieldsA = self::makeAliaFields(self::$joinUserAdmFields, 'a');

        $fields = array_merge($fieldsI, $fieldsU, $fieldsA);

        return $fields;
    }


    /**
     * 获取连表查询对象或SQL数组
     * @param string $userFields UserInfo字段
     * @param string $where 条件
     * @param array $bindParam 绑定参数键值对
     * @param null|int $limit 条数,0为全部
     * @param bool $returnSql 是否返回经解析的SQL数组
     * @return \Phalcon\Mvc\Model\Criteria
     */
    public static function getJoinInfoQuery(string $userFields='', string $where, array $bindParam=[], $limit=1, $returnSql=false) {
        if(empty($userFields)) $userFields = self::$defaultFields;

        $info = self::class;
        $usr = UserBase::class;
        $adm = AdmUser::class;
        $fields = self::getJoinUserFields($userFields);

        $query = self::query()
            ->columns($fields)
            ->leftJoin($usr, "u.uid = {$info}.uid", 'u')
            ->leftJoin($adm, "a.uid = {$info}.uid", 'a')
            ->where($where, $bindParam);

        if($limit>0) $query->limit(intval($limit));

        if($returnSql) {
            $builder = $query->createBuilder();
            $res = $builder->getQuery()->getSql(); //返回数组['sql'=>'', 'bind'=>[], 'bindTypes'=>[] ]
        }else{
            $res = $query;
        }

        return $res;
    }


    /**
     * 根据条件连表查询用户信息
     * @param string $userFields UserInfo字段
     * @param string $where 条件
     * @param array $bindParam 绑定参数键值对
     * @param int $limit 条数,0为全部
     * @return array|bool|\Phalcon\Mvc\ModelInterface
     */
    public static function getJoinInfoByWhere(string $userFields='', string $where, array $bindParam=[], $limit=1) {
        $query = self::getJoinInfoQuery($userFields, $where, $bindParam, $limit, false);
        if(!is_object($query)) return false;

        $result = $query->execute();

        return ($result->count()>0) ? ($limit==1 ? $result->getFirst() : $result->toArray()) : false;
    }


    /**
     * 根据UID获取用户连表信息
     * @param int $uid 用户ID
     * @param string $userFields UserInfo字段
     * @return bool|\Phalcon\Mvc\ModelInterface
     */
    public static function getJoinInfoByUid(int $uid=0, $userFields='') {
        if(empty($uid)) return false;

        $info = self::class;
        $where = "{$info}.uid = :uid: ";
        $bindParam = ['uid'=>$uid];

        return self::getJoinInfoByWhere($userFields, $where, $bindParam, 1);
    }


    /**
     * 根据UID获取用户连表信息
     * 异步协程,使用yield
     * @param int $uid 用户ID
     * @param string $userFields UserInfo字段
     * @return bool
     */
    public static function getJoinInfoByUidAsync(int $uid=0, $userFields='') {
        if(empty($uid)) return false;

        $info = self::class;
        $where = "{$info}.uid = :uid: ";
        $bindParam = ['uid'=>$uid];

        $sqlArr = self::getJoinInfoQuery($userFields, $where, $bindParam, 1, true);
        $res = yield self::queryAsync($sqlArr);
        unset($where, $bindParam);

        return $res;
    }





}