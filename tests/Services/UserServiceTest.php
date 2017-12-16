<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/12/16
 * Time: 17:03
 * Desc: -UserService服务测试
 */

namespace Tests\Services;

use PHPUnit\Framework\TestCase;
use Apps\Services\UserService;
use Faker\Factory as FakerFactory;

class UserServiceTest extends TestCase {


    public function testValidateBaseUser() {
        $serv = new UserService();

        //测试保留名
        $uname1 = 'test001';
        $chk = $serv->checkIsHoldName($uname1);
        $err = $serv->error();
        $this->assertTrue(!$chk);
        $this->assertNotEmpty($err);




    }


}