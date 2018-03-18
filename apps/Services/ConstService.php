<?php
/**
 * Copyright (c) 2018 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2018/1/14
 * Time: 13:44
 * Desc: -常量定义服务类
 */


namespace Apps\Services;

class ConstService extends ServiceBase {

    const API_PAGE_SIZE             = 10; //API默认分页数量
    const BACKEND_PAGE_SIZE         = 10; //后台默认分页数量


    //缓存key


    //工作流类型
    const WFLOW_MANAGE_LOGINLOG             = 'manage_login_log'; //后台登录日志



}