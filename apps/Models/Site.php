<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/10/1
 * Time: 18:29
 * Desc: -数据表模型 site
 */


namespace Apps\Models;

class Site extends  BaseModel {

    public function initialize() {
        parent::initialize();
    }


    /**
     * 获取站点状态数组
     * @return array
     */
    public static function getStatusArr() {
        return [
            '-1' => '关闭',
            '0' => '维护',
            '1' => '开启',
        ];
    }






}