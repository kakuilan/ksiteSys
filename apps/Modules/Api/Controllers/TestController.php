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
use Kengine\Server\LkkServer;
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




}