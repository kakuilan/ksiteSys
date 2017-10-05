<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/10/2
 * Time: 12:11
 * Desc: -main任务
 */


namespace Apps\Modules\Cli\Tasks;

use Kengine\LkkTask;

/**
 * Class 主任务
 * @package Apps\Modules\Cli\Tasks
 */
class MainTask extends LkkTask {


    public function initialize() {
        parent::initialize();
    }


    /**
     * @title -默认动作
     * @desc  -默认动作
     */
    public function mainAction() {
        echo "Congratulations! You are now flying with Phalcon CLI!\r\n";
    }


    /**
     * @title -未发现动作
     * @desc  -未发现动作
     */
    public function notfoundAction() {
        echo "cli task not found!!\r\n";
    }


}