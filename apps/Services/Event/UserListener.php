<?php
/**
 * Copyright (c) 2018 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2018/1/6
 * Time: 23:32
 * Desc: -用户服务监听类
 */


namespace Apps\Services\Event;

use Apps\Models\UserLoginLog;
use Apps\Services\RedisQueueService;
use Apps\Services\ConstService;
use Apps\Models\AdmUser;
use Lkk\Helpers\CommonHelper;

class UserListener extends ListenerBase {

    /**
     * 管理员登录成功后事件处理
     * @param object $event 事件管理器
     * @param object $source 来源对象
     * @param mixed $admn 传递来的数据
     */
    public function afterManagerLoginSuccess($event, $source, $admn) {
        $uid = $admn ? $admn->uid : 0;
        if(empty($uid)) return false;

        //session和cookie
        $source->makeManagerSession($admn);

        $di = $source->getDI();
        $request = $di->getShared('request');
        $ip = $request->getClientAddress();
        $fingerprint = $di->getShared('userAgent')->getAgentFpValue();
        $platform = CommonHelper::getClientOS($request->getSwooleRequest()->server);
        $browser  = CommonHelper::getBrowser(false, $request->getSwooleRequest()->server);
        $ip2long = $ip ? CommonHelper::ip2UnsignedInt($ip) : 0;
        $now = time();

        $newData = [
            'uid' => $uid,
            'type' => '1',
            'status' => '1',
            'login_time' => $now,
            'login_ip' => $ip2long,
            'fingerprint' => $fingerprint ? $fingerprint : 0,
            'platform' => $platform,
            'browser' => $browser,
        ];
        $upData = [
            'logins' => min($admn->login_fails+1, 9999999),
            'login_fails' => '0',
            'last_login_ip' => $ip2long,
            'last_login_time' => $now,
            'update_time' => $now,
        ];
        AdmUser::upData($upData, ['uid'=>$uid]);

        $res = UserLoginLog::addData($newData);
        return $res;
    }



    /**
     * 管理员登录失败后事件处理
     * @param object $event 事件管理器
     * @param object $source 来源对象
     * @param mixed $admn 传递来的数据
     */
    public function afterManagerLoginFail($event, $source, $admn) {
        $uid = $admn ? $admn->uid : 0;
        if(empty($uid)) return false;

        $di = $source->getDI();
        $request = $di->getShared('request');
        $ip = $request->getClientAddress();
        $fingerprint = $di->getShared('userAgent')->getAgentFpValue();
        $platform = CommonHelper::getClientOS($request->server);
        $browser  = CommonHelper::getBrowser(false, $request->server);
        $now = time();

        $item = [
            'type' => ConstService::WFLOW_MANAGE_LOGINLOG,
            'data' => [
                'uid' => $uid,
                'type' => '1',
                'status' => '0',
                'login_time' => $now,
                'login_ip' => $ip ? CommonHelper::ip2UnsignedInt($ip) : 0,
                'fingerprint' => $fingerprint ? $fingerprint : 0,
                'platform' => $platform,
                'browser' => $browser,
            ]
        ];

        //UserLoginLog::addData($item['data']);
        RedisQueueService::quickAddItem2WorkflowMq($item);

        $loginFails = min($admn->login_fails+1, 99);
        $failMax = getConf('login', 'managerFailsLock');

        $upData = [
            'login_fails' => $loginFails,
            'update_time' => $now,
        ];

        if($loginFails >= $failMax) { //锁定
            $upData['status'] = 0;
        }

        AdmUser::upData($upData, ['uid'=>$uid]);
    }


}