<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/12/21
 * Time: 20:39
 * Desc: 清空-任务
 */

namespace Apps\Modules\Cli\Tasks;

use Kengine\LkkTask;
use Lkk\Helpers\DirectoryHelper;
use Lkk\Helpers\StringHelper;

class ClearTask extends LkkTask {

    /**
     * @title -清空运行时任务目录
     * @desc  -清空运行时任务目录
     */
    public function clearRuntimeAction() {
        $dirs = DirectoryHelper::getFileTree(RUNTDIR, 'dir', false);
        $fileSize = DirectoryHelper::getDirSize(RUNTDIR);
        $sizeStr = StringHelper::formatBytes($fileSize);

        if(!empty($dirs)) {
            foreach ($dirs as $dir) {
                DirectoryHelper::emptyDir($dir);
            }
        }

        echo "clear totoal filesize:{$sizeStr}\r\n";
    }



}