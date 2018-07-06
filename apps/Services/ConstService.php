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

    const API_PAGE_SIZE                         = 10; //API默认分页数量
    const BACKEND_PAGE_SIZE                     = 10; //后台默认分页数量


    //缓存时间
    const CACHE_TTL_LONG                        = 0; //永久
    const CACHE_TTL_DEFAULT                     = 1800; //默认缓存时间30分钟
    const CACHE_TTL_ONE_DAY                     = 86400; //缓存时间1天
    const CACHE_TTL_HALF_DAY                    = 43200; //缓存时间0.5天
    const CACHE_TTL_ONE_HOUR                    = 3600; //缓存时间1小时
    const CACHE_TTL_HALF_HOUR                   = 1800; //缓存时间半小时
    const CACHE_TTL_FIF_MINUTE                  = 900; //缓存时间15分钟
    const CACHE_TTL_TEN_MINUTE                  = 600; //缓存时间10分钟
    const CACHE_TTL_FIV_MINUTE                  = 300; //缓存时间5分钟
    const CACHE_TTL_TWO_MINUTE                  = 120; //缓存时间2分钟
    const CACHE_TTL_ONE_MINUTE                  = 60; //缓存时间1分钟


    //缓存key常量定义,命名以 CACHE_ 开头
    const CACHE_CONFIG_ACTI_ALL                 = 'config_active_all'; //动态配置,数据表k_config,全部配置
    const CACHE_CONFIG_ACTI_SYS                 = 'config_active_sys'; //动态配置,数据表k_config,系统平台配置
    const CACHE_CONFIG_ACTI_SITE                = 'config_active_site'; //动态配置,数据表k_config,站点配置,后跟站点ID
    const CACHE_SITE_INFO                       = 'site_info'; //站点信息,后跟站点ID


    //消息流类型定义,命名以 MESSAGE_ 开头,值小写
    const MESSAGE_TYPE_DEFAULT                  =   'default'; //默认
    const MESSAGE_TYPE_MSG                      =   'msg'; //站内信
    const MESSAGE_TYPE_SMS                      =   'sms'; //短信
    const MESSAGE_TYPE_MAIL                     =   'mail'; //邮件
    const MESSAGE_TYPE_OTHER                    =   'other'; //其他


    //工作流类型定义,命名以 WFLOW_ 开头
    const WFLOW_MANAGE_LOGINLOG                 = 'manage_login_log'; //后台登录日志


    //管理后台相关,命名以 ADM_ 开头
    const ADM_OPERATION_LOCK                    =   'adm_lock_'; //后台操作锁,后跟具体操作名和uid

    //预警相关,命名以 WARN_ 开头


}