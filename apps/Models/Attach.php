<?php
/**
 * Copyright (c) 2018 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2018/3/25
 * Time: 22:23
 * Desc: -数据表模型 附件表attach
 */


namespace Apps\Models;

class Attach extends BaseModel {

    public function initialize() {
        parent::initialize();
    }


    /**
     * 获取附件标识数组
     * @return array
     */
    public static function getTagArr() {
        return [
            'system' => '系统',
            'backend' => '后台',
            'avatar' => '头像',
            'ad' => '广告',
            'user' => '用户',
        ];
    }

}