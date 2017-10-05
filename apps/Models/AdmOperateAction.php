<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/4/30
 * Time: 22:44
 * Desc: -数据表模型 adm_operate_action 后台操作动作表
 */


namespace Apps\Models;

class AdmOperateAction extends BaseModel {

    //一个动作至多有N个动作
    const OPERATE_HAS_ACTION_MAXNUM = 20;

    public function initialize() {
        parent::initialize();
    }



}