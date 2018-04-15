<?php
/**
 * Copyright (c) 2018 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2018/4/15
 * Time: 22:37
 * Desc: -模块基础控制器
 */


namespace Apps\Modules;

use Kengine\LkkController;
use Apps\Models\Action;

class BaseController extends LkkController {

    //客户端是否有加密数据
    protected $clientHasEncry = null;

    protected $hasAccessToken = null;


    /**
     * 初始化
     */
    public function initialize() {
        $this->siteId = getSiteId();

        //TODO
    }


    /**
     * 获取动作ID
     * @return mixed
     */
    public function getActionId() {
        if(is_null($this->actionId)) {
            $where = [
                'site_id' => getSiteId(),
                'type' => 3,
                'module' => strtolower($this->router->getModuleName()),
                'controller' => strtolower($this->router->getControllerName()),
                'action' => strtolower($this->router->getActionName()),
            ];

            $info = Action::getRow($where, 'ac_id');
            $this->actionId = empty($info) ? 0 : $info->ac_id;
            unset($info);
        }

        return $this->actionId;
    }


    /**
     * 获取登录用户UID
     * @return mixed
     */
    public function getLoginUid() {
        if(is_null($this->uid) || !is_numeric($this->uid)) {
            //TODO
        }

        return $this->uid;
    }


    /**
     * 记录请求日志
     * @param mixed $out 输出的结果
     *
     * @return mixed
     */
    public function addAccessLog($out) {
        //TODO
    }



}