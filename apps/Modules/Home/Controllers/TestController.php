<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/10/3
 * Time: 22:45
 * Desc: -
 */

namespace Apps\Modules\Home\Controllers;

use Kengine\LkkController;
use Lkk\Helpers\CommonHelper;
use Apps\Models\Test;
use Lkk\Phalwoo\Server\SwooleServer;
use Lkk\Phalwoo\Server\Concurrent\Promise;
use GuzzleHttp\Promise\Promise as GuzPomise;
use Apps\Models\UserBase;

class TestController extends  LkkController {

    /**
     * 默认动作
     * 动作说明
     */
    public function indexAction(){
        return 'HomeModule-TestController-IndexAction '.date('Ymd H:i:s'). ' ' .CommonHelper::getMillisecond();
    }


    public function testAction() {
        $row = Test::getRow()->toArray();
        $str = var_export($row, true);
        return $str;
    }


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


    public function getreqAction() {
        $get = $this->request->getQuery();
        $post = $this->request->getPost();
        $reque = $this->request->get();

        var_dump($get, $post, $reque);

    }


    public function testerrAction() {
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



}