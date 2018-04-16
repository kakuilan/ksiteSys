<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/10/2
 * Time: 12:11
 * Desc: -main任务
 */


namespace Apps\Modules\Cli\Tasks;

use Apps\Modules\Cli\BaseTask;
use Apps\Services\ActionService;
use Lkk\Helpers\ArrayHelper;

/**
 * Class 主任务
 * @package Apps\Modules\Cli\Tasks
 */
class MainTask extends BaseTask {


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
        global $cliArguments;

        $serv = new ActionService();
        $taskArr = $serv->getCliTasks();
        $taskNams = array_column($taskArr, 'contrlName');

        //增加notfound的相关提示
        if(in_array(ucfirst($cliArguments['task']), $taskNams)) {
            $info = ArrayHelper::arraySearchItem($taskArr, ['contrlName'=>ucfirst($cliArguments['task'])]);
            $actionNames = array_keys($info['actionNames']);
            echo "cli action not found!\r\n";
            echo "current task has actions:\r\n";
            foreach ($actionNames as $actionName) {
                echo "\t".strtolower($actionName) . "\r\n";
            }
        }else{ //task不对,显示所有task
            echo "cli task not found!\r\n";
            echo "current app has tasks:\r\n";
            foreach ($taskNams as $taskNam) {
                echo "\t".strtolower($taskNam) . "\r\n";
            }
        }

    }


}