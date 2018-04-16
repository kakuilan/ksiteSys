<?php
/**
 * Copyright (c) 2018 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2018/1/28
 * Time: 15:10
 * Desc: -后台基本控制器
 */


namespace Apps\Modules\Manage;

use Apps\Models\Action;
use Apps\Modules\BaseController;
use Apps\Models\AdmOperateLog;
use Apps\Services\ConstService;
use Lkk\Helpers\ArrayHelper;
use Lkk\Helpers\CommonHelper;

class Controller extends  BaseController {

    //不记访问日志的动作
    public static $nologActions = [
        'login',
        'admloglist',
    ];


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
        $this->getActionId();

        if(!in_array($action, self::$nologActions)) {
            $this->addAccessLog();
        }

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


    /**
     * 获取动作ID
     * @return int|mixed
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
     * 新增后台操作日志
     * @param string $out
     */
    public function addAccessLog($out='') {
        $swooleRequest = $this->getDI()->getShared('swooleRequest');
        $params = [
            'get' => $swooleRequest->get ?? [],
            'post' => $swooleRequest->post ?? [],
        ];

        $data = [
            'site_id' => $this->siteId,
            'action_id' => $this->actionId,
            'create_time' => time(),
            'create_by' => $this->uid,
            'create_ip' => CommonHelper::ip2UnsignedInt($this->request->getClientAddress()),
            'url' => $this->request->getURI(),
            'parameter' => (empty($params['get']) && empty($params['post'])) ? '' : json_encode(self::filterQueryLogParam($params)),
        ];

        AdmOperateLog::addData($data);
        unset($swooleRequest, $params, $data);
    }


    /**
     * 过滤请求日志参数
     * @param array $p
     *
     * @return array
     */
    public static function filterQueryLogParam($p=[]) {
        if(empty($p)) return $p;

        foreach ($p as $k=>&$item) {
            if(is_array($item)) {
                $p[$k] = self::filterQueryLogParam($item);
            }elseif (ArrayHelper::dstrpos($k, ['_', '_csrf'])){
                unset($p[$k]);
                continue;
            }elseif(!empty($item)){
                if(ArrayHelper::dstrpos($k, ['password', 'passwd', 'pwd'])) {
                    $p[$k] = '*';
                }elseif (strlen($item)>100) {
                    $p[$k] = mb_substr($item, 0, 20, 'UTF-8');
                }
            }
        }

        return $p;
    }



    /**
     * 获取分页页码和每页数量
     * @param bool $detail
     * @return array
     */
    protected function getPageNumberNSize($detail=false) {
        //获取每页数量
        $pageSize = intval($this->getGet('pageSize', 0, false));
        if($pageSize<=0) {
            $limit = intval($this->getGet('limit', 0, false));
            $pageSize = $limit ? $limit : ConstService::BACKEND_PAGE_SIZE;
        }

        $pageNumber = intval($this->getGet('pageNumber', 0, false));
        if($pageNumber<=0) {
            $offset = intval($this->getGet('offset', 0, false));
            if($offset>0) {
                $pageNumber = intval($offset / $pageSize) +1;
            }else{
                $pageNumber = 1;
            }
        }

        return $detail ? ['pageNumber'=>$pageNumber, 'pageSize'=>$pageSize] : [$pageNumber, $pageSize];
    }





}