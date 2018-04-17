<?php
/**
 * Copyright (c) 2018 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2018/4/15
 * Time: 22:27
 * Desc: -API模块基础控制器
 */


namespace Apps\Modules\Api;

use Apps\Modules\BaseController;
use Apps\Services\UserService;

class Controller extends BaseController {


    /**
     * 初始化
     */
    public function initialize() {
        $this->siteId = getSiteId();

        //TODO
    }


    /**
     * 获取登录用户UID
     * @return mixed
     */
    public function getLoginUid() {
        if(is_null($this->uid) || !is_numeric($this->uid)) {
            $this->hasAccessToken = false;
            $this->uid = 0;
            $accessToken = $this->getAccessToken();
            if(!empty($accessToken)) {
                $this->hasAccessToken = true;
                $this->uid = UserService::parseAccessToken($accessToken);
            }
        }

        return $this->uid;
    }




}