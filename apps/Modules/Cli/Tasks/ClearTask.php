<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/12/21
 * Time: 20:39
 * Desc: 清空目录-任务
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

        $fileSize = 0;
        if(!empty($dirs)) {
            foreach ($dirs as $dir) {
                if(stripos($dir, 'pids') || stripos($dir, 'session')) continue;
                $fileSize += DirectoryHelper::getDirSize(RUNTDIR);
                DirectoryHelper::emptyDir($dir);
            }
        }

        $sizeStr = StringHelper::formatBytes($fileSize);

        echo "clear totoal filesize:{$sizeStr}\r\n";
    }


    public function clearCacheDirAction() {
        $dir = RUNTDIR . 'cache';
        $fileSize = DirectoryHelper::getDirSize($dir);
        $sizeStr = StringHelper::formatBytes($fileSize);
        DirectoryHelper::emptyDir($dir);

        echo "clear totoal filesize:{$sizeStr}\r\n";
    }


    public function clearLogsDirAction() {
        $dir = RUNTDIR . 'logs';
        $fileSize = DirectoryHelper::getDirSize($dir);
        $sizeStr = StringHelper::formatBytes($fileSize);
        DirectoryHelper::emptyDir($dir);

        echo "clear totoal filesize:{$sizeStr}\r\n";
    }


    public function clearPidsDirAction() {
        $dir = RUNTDIR . 'pids';
        $fileSize = DirectoryHelper::getDirSize($dir);
        $sizeStr = StringHelper::formatBytes($fileSize);
        DirectoryHelper::emptyDir($dir);

        echo "clear totoal filesize:{$sizeStr}\r\n";
    }


    public function clearSessionDirAction() {
        $dir = RUNTDIR . 'session';
        $fileSize = DirectoryHelper::getDirSize($dir);
        $sizeStr = StringHelper::formatBytes($fileSize);
        DirectoryHelper::emptyDir($dir);

        echo "clear totoal filesize:{$sizeStr}\r\n";
    }


    public function clearTempDirAction() {
        $dir = RUNTDIR . 'temp';
        $fileSize = DirectoryHelper::getDirSize($dir);
        $sizeStr = StringHelper::formatBytes($fileSize);
        DirectoryHelper::emptyDir($dir);

        echo "clear totoal filesize:{$sizeStr}\r\n";
    }


    public function clearVoltDirAction() {
        $dir = RUNTDIR . 'volt';
        $fileSize = DirectoryHelper::getDirSize($dir);
        $sizeStr = StringHelper::formatBytes($fileSize);
        DirectoryHelper::emptyDir($dir);

        echo "clear totoal filesize:{$sizeStr}\r\n";
    }




}