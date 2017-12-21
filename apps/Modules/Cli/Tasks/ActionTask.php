<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/10/28
 * Time: 15:15
 * Desc: -动作任务
 */


namespace Apps\Modules\Cli\Tasks;

use Kengine\LkkTask;
use Apps\Models\Action;
use Apps\Services\ActionService;

/**
 * Class 动作任务
 * @package Apps\Modules\Cli\Tasks
 */
class ActionTask extends LkkTask {

    /**
     * @title -默认任务
     * @desc  -统计当前系统可用动作
     */
    public function mainAction() {
        $num = Action::countEnable();
        echo "There are {$num} available actions!\r\n";
    }


    /**
     * @title -更新动作
     * @desc  -更新系统动作
     */
    public function updateAction() {
        $actionService = new ActionService();
        $actionService->updateSystemAction();
        $num = Action::countEnable();

        echo "System`s action update complete [{$num}] !\r\n";
    }

}