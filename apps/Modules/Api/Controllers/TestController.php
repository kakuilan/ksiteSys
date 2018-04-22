<?php
/**
 * Copyright (c) 2018 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2018/4/17
 * Time: 22:47
 * Desc: -测试
 */


namespace Apps\Modules\Api\Controllers;

use Apps\Modules\Api\Controller;
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
        $res = yield $redis->set('test', date('Y-m-d H:i:s') .' hhe', 600);

        return $this->success($res);
    }






}