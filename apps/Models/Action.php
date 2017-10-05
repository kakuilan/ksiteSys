<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/3/18
 * Time: 13:03
 * Desc: -数据表模型 action
 */


namespace Apps\Models;

class Action extends BaseModel {

    public function initialize() {
        parent::initialize();
    }


    /**
     * 获取动作记录信息
     * @param string $module 模块名称
     * @param string $controller 控制器名称
     * @param string $action 动作名称
     *
     * @return \Phalcon\Mvc\Model
     */
    public static function getInfo($module, $controller='', $action='') {
        $res = self::findFirst([
            "module=:module: AND controller=:controller: AND action=:action: ",
            "bind" => ['module'=>$module, 'controller'=>$controller, 'action'=>$action]
        ]);

        return $res;
    }


    /**
     * 统计可用的动作
     * @return mixed
     */
    public static function countEnable() {
        $res = self::count(['status'=>1]);
        return $res;
    }

    /**
     * 将action模型转为URL
     * @param $model
     *
     * @return string
     */
    public static function toUrl($model) {
        $res = '';
        if(empty($model) || (!is_object($model) && !is_array($model))) {
            return $res;
        }

        if (!is_object($model)) {
            $model = (object)$model;
        }

        if(!in_array($model->type, [1,2,3]) ){
            return $res;
        }

        $res = "/{$model->module}/{$model->controller}/{$model->action}";
        $res = rtrim($res, "/");

        return $res;
    }


    /**
     * 获取站点后台所有动作
     * @param int $siteId
     *
     * @return array
     */
    public static function getSiteMangeActions($siteId=0) {
        $res = [];
        $list = self::getList([
            'and',
            ['site_id' => $siteId],
            ['status' => 1],
            ['type' => 3],
            ['module' => 'manage'],
            ['<>','action',''],
        ]);
        if(!empty($list)) {
            foreach ($list as $action) {
                if(empty($action->title)) $action->title = $action->action;
                $action->url = self::toUrl($action);
                array_push($res, $action);
            }
        }

        return $res;
    }



}