<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/12/27
 * Time: 18:27
 * Desc:
 */

namespace Apps\Modules\Cli\Tasks;

use Apps\Models\AdmUser;
use Apps\Models\Site;
use Apps\Models\UserBase;
use Apps\Modules\Cli\BaseTask;
use Apps\Services\Ip2RegionService;
use Lkk\Helpers\StringHelper;

class TestTask extends BaseTask {

    /**
     * @title -默认动作
     * @desc  -默认动作
     */
    public function mainAction() {
        echo "test/main\r\n";
    }


    public function testAction() {
        $where = "";
        $binds = [];
        $order = '';
        $paginator = AdmUser::getAdminPages($this->modelsManager, $where, $binds, $order, 2, 1);
        $pageObj = $paginator->getPaginate();
        $list = $pageObj->items->toArray();

        var_dump($list, $paginator);
    }


    /**
     * @title -测试IP转为地址
     * @desc  -测试IP转为地址
     */
    public function ip2addrAction() {
        $ip1 = '116.24.96.197';
        $ip2 = '127.0.0.1';
        $ip3 = '192.168.128.1';

        $ipServ = new Ip2RegionService();
        $info1 = $ipServ->btreeSearch($ip1);
        $info2 = $ipServ->btreeSearch($ip2);
        $info3 = $ipServ->btreeSearch($ip3);
        $info4 = $ipServ->getCityName($ip1);
        var_dump($info1, $info2, $info3, $info4);

    }







}