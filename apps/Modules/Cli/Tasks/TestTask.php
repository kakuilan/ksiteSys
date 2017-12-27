<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/12/27
 * Time: 18:27
 * Desc:
 */

namespace Apps\Modules\Cli\Tasks;

use Kengine\LkkTask;
use Apps\Models\UserBase;
use Apps\Models\AdmUser;

class TestTask extends LkkTask {

    /**
     * @title -默认动作
     * @desc  -默认动作
     */
    public function mainAction() {
        echo "test/main\r\n";
    }


    public function testAction() {
        $res = UserBase::joinAdmInfoByUsername('root');
        $obj = UserBase::rowToObject($res);
        var_dump($obj);
    }




}