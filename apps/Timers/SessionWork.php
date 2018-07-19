<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/10/3
 * Time: 1:56
 * Desc: -Session工作流
 */


namespace Apps\Timers;

use Lkk\Phalwoo\Server\WorkFlow;
use Lkk\Phalwoo\Server\SwooleServer;
use Lkk\Phalwoo\Phalcon\Session\Adapter\Redis as RedisSession;

class SessionWork extends WorkFlow {


    protected $redis;
    protected $conf;


    public function __construct(array $vars = []) {
        parent::__construct($vars);

        $this->getRedis();
    }


    /**
     * 获取同步redis客户端
     * @return null|\Redis
     */
    public function getRedis() {
        if(is_null($this->redis)) {
            $this->redis = new \Redis();

            $sessionCnf = getConf('session')->toArray();
            $poolCnf = getConf('pool')->toArray();
            $redisCnf = $poolCnf[$sessionCnf['redis']]['args'];
            $this->conf = $redisCnf;

        }

        return $this->redis;
    }


    /**
     * 打开redis连接
     * @return bool
     */
    public function openRedis() {
        $res = $this->redis->pconnect($this->conf['host'], $this->conf['port'], 2.5);
        if(!$res) return false;

        if(isset($this->conf['auth']) && !empty($this->conf['auth'])) {
            $this->redis->auth($this->conf['auth']);
        }

        $this->redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
        $this->redis->select($this->conf['select'] ?? 1);

        return true;
    }


    /**
     * 关闭redis连接
     * @return mixed
     */
    public function closeRedis() {
        return $this->redis->close();
    }


    /**
     * ping redis
     * @return bool
     */
    public function pingRedis() {
        $res = false;
        try {
            $res = $this->redis->ping();
        }catch (\Exception $e) {
            $res = false;
        }

        return $res;
    }



    /**
     * 将队列中的session写入redis
     * @return bool
     */
    public function writeSession() {
        if($this->chkDoing(__FUNCTION__)) return false;

        if(!$this->pingRedis()) $this->openRedis();
        $this->setDoing(true, __FUNCTION__);

        $sessionQueue = SwooleServer::getSessionQueue();
        $state = $sessionQueue->stats();
        if($state['queue_num']<=0) {
            $this->setDoing(false, __FUNCTION__);
            return false;
        }

        $totalNum = 10000; //预计总共处理
        $batchMaX = 100; //每批最多
        $i = $succNum = 0;

        for (;$i < $totalNum; $i += $batchMaX) {
            if(!$this->chkDoing(__FUNCTION__)) break;

            $batchNum = 0;
            $this->redis->multi();
            while ($batchNum < $batchMaX) {
                $item = $sessionQueue->pop();
                if(empty($item)) {
                    $this->setDoing(false, __FUNCTION__);
                    break;
                }else{
                    if(empty($item['session'])) {
                        $res = $this->redis->del($item['key']);
                    }else{
                        $res = $this->redis->setex($item['key'], $item['lefttime'], $item['session']);
                        $succNum++;
                    }
                }

                $i++;
                $batchNum++;
            }
            $this->redis->exec();
        }

        if(SwooleServer::isOpenDebug()) {
            $mesg = "session write to redis done! succNum:[{$succNum}] \r\n";
            echo $mesg;
        }

        return true;
    }


    /**
     * gc优化,删除无效session
     * @return bool
     */
    public function gc() {
        if($this->chkDoing(__FUNCTION__)) return false;

        if(!$this->pingRedis()) $this->openRedis();
        $this->setDoing(true, __FUNCTION__);

        $conf = getConf('session');
        $keys = $this->redis->keys($conf->prefix.'*');
        if($keys) {
            $delArr = [];
            $now = time();
            foreach ($keys as $key) {
                $vue = $this->redis->get($key);
                if(is_array($vue) && isset($vue[RedisSession::LASTT_KEY])) {
                    //5分钟用户无操作,删除session
                    if(($now - $vue[RedisSession::LASTT_KEY])> 300) {
                        array_push($delArr, $key);
                    }
                }
            }

            if(!empty($delArr)) {
                //批量删除
                $slices = array_chunk($delArr, 10);
                $this->redis->multi();
                foreach ($slices as $slice) {
                    $this->redis->delete($slice);
                }
                $this->redis->exec();
            }
        }
        $this->setDoing(false, __FUNCTION__);

        return true;
    }



}