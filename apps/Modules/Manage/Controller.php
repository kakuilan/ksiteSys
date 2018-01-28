<?php
/**
 * Copyright (c) 2018 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2018/1/28
 * Time: 15:10
 * Desc: -后台基本控制器
 */


namespace Apps\Modules\Manage;

use Kengine\LkkController;

class Controller extends  LkkController {

    /**
     * 初始化
     */
    public function initialize() {
        $uid = $this->userService->checkManagerLogin();

        $module = $this->router->getModuleName();
        $contro = $this->router->getControllerName();
        $action = $this->router->getActionName();
        $curAct = strtolower("{$module}/{$contro}/{$action}");

        if($uid<=0 && !in_array($curAct, getConf('rbac')->managerNoauthActions->toArray()) ) {
            return $this->response->redirect(getConf('rbac')->managerAuthGateway);
        }

        $this->uid = $uid;
        $this->siteId = getSiteId();

        return null;
    }


    /**
     * 获取登录用户UID
     * @return mixed
     */
    public function getLoginUid() {
        if(is_null($this->uid) || !is_numeric($this->uid)) {
            $this->uid = $this->userService->checkManagerLogin();
        }

        return $this->uid;
    }





}