<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/7/13
 * Time: 23:32
 * Desc: -用户服务类
 */


namespace Apps\Services;

use Lkk\Helpers\ArrayHelper;
use Lkk\Helpers\ValidateHelper;

class UserService extends ServiceBase {

    const USER_NAME_MINLEN = 5;
    const USER_NAME_MAXLEN = 5;


    /**
     * 构造函数
     */
    public function __construct($vars=[]) {
        parent::__construct($vars);

    }





}