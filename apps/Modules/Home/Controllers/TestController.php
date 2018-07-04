<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/10/3
 * Time: 22:45
 * Desc: -
 */

namespace Apps\Modules\Home\Controllers;

use Apps\Models\AdmUser;
use Apps\Models\Test;
use Apps\Models\UserBase;
use Apps\Models\UserInfo;
use Apps\Modules\Home\Controller;
use Apps\Services\RedisQueueService;
use Apps\Services\UserService;
use Lkk\Helpers\ArrayHelper;
use Lkk\Helpers\CommonHelper;
use Lkk\Helpers\StringHelper;
use Lkk\Helpers\ValidateHelper;
use Lkk\Phalwoo\Server\SwooleServer;


class TestController extends  Controller {

    /**
     * @title -默认动作
     * @desc  -动作说明
     */
    public function indexAction(){
        return 'HomeModule-TestController-IndexAction '.date('Ymd H:i:s'). ' ' .CommonHelper::getMillisecond();
    }


    /**
     * @title -测试
     * @desc  -测试
     */
    public function testAction() {
        $row = Test::getRow()->toArray();
        $str = var_export($row, true);
        return $str;
    }


    /**
     * @title -测试sessionpv
     * @desc  -测试sessionpv
     */
    public function sessionpvAction() {
        $pv = $this->session->getPv();
        $qps = $this->session->getQps();
        $online = $this->session->getSiteOnlineNum();

        $data = [
            'pv' => $pv,
            'qps' => $qps,
            'online' => $online,
        ];

        return $this->success($data);
    }


    /**
     * @title -测试异步协程
     * @desc  -测试异步协程
     */
    public function asyncAction() {
        //协程
        /*$prom = Promise::co(function(){
            $row = yield Test::getRowAsync();
            SwooleServer::getLogger()->error("ASYNC mysql result", $row);
            $str = var_export($row, true);
            echo $str;
        });

        $str = var_export($prom, true);*/

        $row = yield Test::getRowAsync();
        SwooleServer::getLogger()->error("ASYNC mysql result", $row);
        $str = var_export($row, true);
        echo $str;

        return $str;
    }


    /**
     * @title -测试获取请求参数
     * @desc  -测试获取请求参数
     */
    public function getreqAction() {
        $get = $this->request->getQuery();
        $post = $this->request->getPost();
        $reque = $this->request->get();

        var_dump($get, $post, $reque);
    }


    /**
     * @title -测试模型操作
     * @desc  -测试模型操作
     */
    public function testmodelAction() {
        //asdf = adsf;

        $data = [
            'uid' => 7,
            'update_time' => time(),
            'mobile' => '7890',
        ];
        $where = ['uid'=>7];
        $res = yield UserBase::upDataAsync($data, $where);


        /*$data = [
            'uid' => 7,
            'update_time' => time(),
            'mobile' => 'qqqww',
        ];
        $res = UserBase::addData($data);
        var_dump('sql-res', $data, $res);*/
    }


    /**
     * @title -测试获取redis配置
     * @desc  -测试获取redis配置
     */
    public function querediscnfAction() {
        $poolCnf = getConf('pool')->toArray();
        $redisCnf = $poolCnf['redis_queue']['args'];

        $item = [
            'type' => 'msg',
            'data' => [
                'uid' => '1',
                'touid' => mt_rand(10, 100),
                'content' => 'hello work',
            ],
        ];
        RedisQueueService::quickAddItem2AppNotifyMq($item);

        $this->success($redisCnf);
    }


    /**
     * @title -测试同步redis操作
     * @desc  -测试同步redis操作
     */
    public function redisAction() {
        $redis = SwooleServer::getPoolManager()->get('redis_site')->pop(true);
        $key = 'test';
        $res = $redis->set($key, date('Y-m-d H:i:s'));
        return $this->success($res);
    }


    /**
     * @title -测试多语言
     * @desc  -测试多语言
     */
    public function langAction() {
        $aa = "HELLOWORLD";
        $bb = lang($aa);
        $cc = "Hello :user";
        $dd = lang($cc, [':user'=>'lkk']);

        var_dump($bb, $dd);
    }

    /**
     * @title -测试生成密码
     * @desc  -测试生成密码
     */
    public function makepwdAction() {
        $pwd = 'mypw@123';
        $new = UserService::makePasswdHash(md5($pwd));
        return $this->success($new);
    }






}