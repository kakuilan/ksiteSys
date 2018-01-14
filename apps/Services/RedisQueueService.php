<?php
/**
 * Copyright (c) 2018 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2018/1/14
 * Time: 13:14
 * Desc: -redis队列服务类
 */


namespace Apps\Services;

use Lkk\Phalwoo\Server\RedisQueue;

class RedisQueueService extends RedisQueue {


    /**
     * 获取默认的redis配置
     * @return array
     */
    public static function getDefultRedisCnf() {
        if(empty(parent::$defaultCnf)) {
            $poolCnf = getConf('pool')->toArray();
            $redisCnf = $poolCnf['redis_queue']['args'];
            $conf = [
                'host' => $redisCnf['host'] ?? '127.0.0.1',
                'port' => $redisCnf['port'] ?? '6379',
                'password' => $redisCnf['auth'] ?? '',
                'select' => $redisCnf['select'] ?? REDIS_QUEUE_DB,
            ];
        }else{
            $conf = parent::$defaultCnf;
        }

        return $conf;
    }







}