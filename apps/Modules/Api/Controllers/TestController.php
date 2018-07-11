<?php
/**
 * Copyright (c) 2018 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2018/4/17
 * Time: 22:47
 * Desc: -测试
 */


namespace Apps\Modules\Api\Controllers;

use Apps\Models\Action;
use Apps\Models\AdmMenu;
use Apps\Models\AdmModule;
use Apps\Models\AdmOperateAction;
use Apps\Models\AdmOperateLog;
use Apps\Models\AdmOperation;
use Apps\Models\AdmRole;
use Apps\Models\AdmRoleFunc;
use Apps\Models\AdmUser;
use Apps\Models\AdmUserole;
use Apps\Models\Attach;
use Apps\Models\Cnarea;
use Apps\Models\Config;
use Apps\Models\Cron;
use Apps\Models\CronLog;
use Apps\Models\Message;
use Apps\Models\MessageOverallReceive;
use Apps\Models\MsgqueDetail;
use Apps\Models\MsgqueNames;
use Apps\Models\MsgqueSendLog;
use Apps\Models\Site;
use Apps\Models\Test;
use Apps\Models\UserBase;
use Apps\Models\UserIdentity;
use Apps\Models\UserInfo;
use Apps\Models\UserLoginLog;
use Apps\Modules\Api\Controller;
use Apps\Services\ActionService;
use Apps\Services\AttachService;
use Apps\Services\CaptchaService;
use Apps\Services\ConfigService;
use Apps\Services\ConstService;
use Apps\Services\EmojiService;
use Apps\Services\Ip2RegionService;
use Apps\Services\NotifyService;
use Apps\Services\RbacService;
use Apps\Services\RedisQueueService;
use Apps\Services\UploadService;
use Apps\Services\UserService;
use Kengine\LkkCmponent;
use Kengine\LkkRoutes;
use Kengine\Server\LkkServer;
use Lkk\Helpers\CommonHelper;
use Redis;

class TestController extends Controller {


    /**
     * @title -默认动作
     * @desc  -动作说明
     */
    public function indexAction(){
        $data = [
            'url' => 'api/test/index',
            'date' => date('Y-m-d H:i:s'),
        ];

        return $this->success($data);
    }


    /**
     * @title -语言转换
     * @desc  -语言转换
     * @return array|string
     */
    public function langAction() {
        $str1 = 'HELLOWORLD';
        $str2 = 'Hello :user';

        $res = [
            lang($str1),
            lang($str2, [':user'=>'lkk']),
            lang(401)
        ];

        return $this->success($res);
    }


    /**
     * @title -获取同步redis
     * @desc  -获取同步redis
     * @return array|string
     */
    public function getSyncRedisAction() {
        $redis = LkkServer::getPoolManager()->get('redis_site')->pop(true);
        $res = $redis->set('test', date('Y-m-d H:i:s') .' hhe', 600);

        return $this->success($res);
    }


    /**
     * @title -redis异步操作
     * @desc  -redis异步操作
     * @return array|string
     */
    public function asyncRedisAction() {
        $redis = LkkServer::getPoolManager()->get('redis_site')->pop();
        $key = 'async';
        yield $redis->set($key, date('Y-m-d H:i:s'), time()+120);
        $ret = yield $redis->get($key);
        $res = promiseRedisResult($ret);
        $data = [
            'ret' => $ret,
            'res' => $res,
        ];

        return $this->success($data);
    }


    /**
     * @title -站点缓存异步
     * @desc  -站点缓存异步
     * @return array|string
     */
    public function sitecacheAsyncAction() {
        $rang = range(1, 99);
        $cache = LkkCmponent::siteCache();

        $res = [];
        foreach ($rang as $item) {
            $key = 'test_' . $item;
            $vue = $item;
            $ret = yield $cache->save($key, $vue);
            array_push($res, intval($ret));
        }

        return $this->success($res);
    }


    /**
     * @title -获取站点信息
     * @desc  -获取站点信息
     * @return array|string
     */
    public function siteInfoAction() {
        $info = getSiteInfo($this->siteId);
        $url1 = getSiteUrl();
        $url2 = getSiteUrl('http://redisdoc.com/string/set.html');
        $data = [
            'site' => $info,
            'url1' => $url1,
            'url2' => $url2,
        ];

        return $this->success($data);
    }


    /**
     * @title -获取站点配置列表
     * @desc  -获取站点配置列表
     * @return array|string
     */
    public function getSiteConfAction() {
        $data = yield ConfigService::getSiteConfigs();
        return $this->success($data);
    }


    /**
     * @title -获取系统配置列表
     * @desc  -获取系统配置列表
     * @return array|string
     */
    public function getGlobalConfAction() {
        $data = yield ConfigService::getGlobalConfigs();
        return $this->success($data);
    }


    /**
     * @title -获取全部配置列表
     * @desc  -获取全部配置列表
     * @return array|string
     */
    public function getAllConfAction() {
        $data = yield ConfigService::getAllConfigs();
        return $this->success($data);
    }


    /**
     * @title -检查配置键
     * @desc  -检查配置键
     * @return array|string
     */
    public function chkConfKeyAction() {
        $key1 = 'glo_smtp_from';
        $key2 = 'upload_file_size';
        $key3 = 'heheda';

        $data = [
            'ret1' => yield ConfigService::getGlobalValueByKey($key1),
            'ret2' => yield ConfigService::getSiteValueByKey($key2),
            'ret3' => yield ConfigService::existsKey($key2),
            'ret4' => yield ConfigService::existsKey($key3),
            'ret5' => yield ConfigService::existsKey($key3, true),
        ];

        return $this->success($data);
    }


    /**
     * @title -获取用户基本信息
     * @desc  -获取用户基本信息
     * @return array|string
     */
    public function getUserInfoAction() {
        $info1 = UserBase::getInfoByUsername('imlkk');
        $info2 = UserBase::getInfoByUsername('imlkk', UserBase::$baseFields);
        $info3 = UserBase::getInfoByEmail('lusizeng@163.com');
        $info4 = UserBase::getInfoByKeyword('lusizeng@163.com');
        $data = [
            '$info1' => $info1->toArray(),
            '$info2' => $info2->toArray(),
            '$info3' => $info3->toArray(),
            '$info4' => $info4->toArray(),
        ];

        return $this->success($data);
    }


    /**
     * @title -获取管理员信息
     * @desc  -获取管理员信息
     * @return array|string
     */
    public function getInfoInAdmAction() {
        $info1 = UserBase::getInfoInAdmByUsername('vip');
        $info2 = UserBase::getInfoInAdmByUsername('admin', true);
        $info3 = UserBase::getInfoInAdmByUid(5);
        $info4 = UserBase::getInfoInAdmByUid(2, true);
        $info5 = AdmUser::getInfoByUid(2);
        $info6 = AdmUser::getInfoByUsername('test');
        $info7 = AdmUser::getInfoByEmail('kakuilan@qq.com');
        $info8 = AdmUser::getInfoByKeyword('kakuilan@qq.com');

        $data = [
            '$info1' => $info1->toArray(),
            '$info2' => $info2->toArray(),
            '$info3' => $info3->toArray(),
            '$info4' => $info4->toArray(),
            '$info5' => ($info5 ? $info5->toArray() : $info5),
            '$info6' => ($info6 ? $info6->toArray() : $info6),
            '$info7' => ($info7 ? $info7->toArray() : $info7),
            '$info8' => ($info8 ? $info8->toArray() : $info8),
        ];

        return $this->success($data);
    }


    /**
     * @title -数据库异步执行SQL
     * @desc  -数据库异步执行SQL
     * @return array|string
     */
    public function dbqueryAsyncAction() {
        $sql = "SELECT `k_user_base`.`uid` AS `uid`, `k_user_base`.`site_id` AS `site_id`, `k_user_base`.`status` AS `status`, `k_user_base`.`mobile_status` AS `mobile_status`, `k_user_base`.`email_status` AS `email_status`, `k_user_base`.`type` AS `type`, `k_user_base`.`mobile` AS `mobile`, `k_user_base`.`email` AS `email`, `k_user_base`.`username` AS `username`, `k_user_base`.`password` AS `password`, `k_user_base`.`create_time` AS `create_time`, `k_user_base`.`update_time` AS `update_time`, `a`.`uid` AS `adm_uid`, `a`.`level` AS `adm_level`, `a`.`status` AS `adm_status`, `a`.`logins` AS `adm_logins`, `a`.`login_fails` AS `adm_login_fails`, `a`.`last_login_ip` AS `adm_last_login_ip`, `a`.`last_login_time` AS `adm_last_login_time` FROM `k_user_base`  LEFT JOIN `k_adm_user` AS `a` ON `a`.`uid` = `k_user_base`.`uid` WHERE `k_user_base`.`uid` = 2 AND `a`.`uid` > 0 LIMIT 1";

        $res = yield UserBase::queryAsync($sql);

        return $this->success($res);
    }


    /**
     * @title -动作转发
     * @desc  -动作转发
     * @return array|string
     */
    public function forwardAction() {
        return $this->dispatcher->forward([
            'module' => 'api',
            'controller' => 'index',
            'action' => 'error',
        ]);

        return $this->success();
    }


    public function getRouteByUrlAction() {
        $url = $this->getRequest('url');
        if(empty($url)) $url = $this->request->getURL();
        $res = LkkRoutes::getRouteInfoByUrl($url);

        $data = [
            '$url' => $url,
            '$res' => $res,
        ];

        return $this->success($data);
    }


    public function avatarAction() {
        $path = UserService::getAvatarByUid(2);
        $info = pathinfo($path);

        $user1 = UserBase::getInfoInAdmByUid(1);
        $user2 = yield UserBase::getInfoInAdmByUidAsync(2);

        $data = [
            'path' => $path,
            'info' => $info,
            '$user1' => $user1,
            '$user2' => $user2,
        ];

        return $this->success($data);
    }


}