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


    /**
     * 测试基本用户信息验证
     */
    public function testValidateBaseUser() {
        $serv = new UserService();
        $faker = FakerFactory::create();

        //检查保留名
        $uname = 'test001';
        $chk = $serv->checkIsHoldName($uname);
        $err = $serv->error();
        $this->assertTrue(!$chk);
        $this->assertNotEmpty($err);

        //检查保留昵称
        $unick = '管理员';
        $chk = $serv->checkIsHoldNick($unick);
        $err = $serv->error();
        $this->assertTrue(!$chk);
        $this->assertNotEmpty($err);

        $uname = $faker->userName;
        $chk = $serv->validateUsername($uname);
        $err = $serv->error();
        if(!$chk) $this->assertNotEmpty($err);

        $email = $faker->email;
        $chk = $serv->validateEmail($email);
        $err = $serv->error();
        if(!$chk) $this->assertNotEmpty($err);

        $passwd = $faker->password;
        $chk = $serv->validateUserpwd($passwd);
        $err = $serv->error();
        if(!$chk) $this->assertNotEmpty($err);

    }


}