<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/10/28
 * Time: 16:20
 * Desc: -系统动作处理服务类,注意要关闭opcache才可获取注释
 */


namespace Apps\Services;

use Lkk\Helpers\DirectoryHelper;
use Apps\Models\Action;

class ActionService extends ServiceBase {

    private $siteId;
    private $controllerFiles;

    /**
     * 构造函数
     */
    public function __construct($vars=[]) {
        parent::__construct($vars);

        $this->siteId = getSiteId();

    }


    /**
     * 析构函数
     */
    public function __destruct() {

    }


    /**
     * 获取系统模块
     * @return array
     */
    public function getModules() {
        $modules = getConf('modules');
        if(!empty($modules)) {
            $modules = array_keys(getConf('modules')->toArray());
        }

        return $modules;
    }


    /**
     * 获取控制器php文件列表
     * @return array
     */
    public function getControllersFiles() {
        $this->controllerFiles = DirectoryHelper::getFileTree(MODULDIR,'file');
        return $this->controllerFiles;
    }


    /**
     * 更新系统动作表
     * @return bool
     */
    public function updateSystemAction() {
        $this->_updateModules();
        $this->_updateControllers();

        return true;
    }


    /**
     * 更新模块信息
     * @return int
     */
    private function _updateModules() {
        $modules = $this->getModules();
        $num = 0;
        $now  = time();
        foreach ($modules as $m) {
            $m = strtolower(trim($m));
            if(empty($m)) continue;
            $check = Action::getInfo($m);
            if(empty($check)) {
                $type = $m=='cli' ?  -1 : 1;
                $res = Action::addData([
                    'site_id' => $this->siteId,
                    'status' => 1,
                    'type' => $type,
                    'module' => $m,
                    'title' => $m .'模块',
                    'batch_time' => $now,
                    'create_time' => $now,
                    'update_time' => $now,
                ]);
                if($res) $num++;
            }
        }

        return $num;
    }


    /**
     * 更新控制器
     * @return int
     */
    private function _updateControllers() {
        $cfs = $this->getControllersFiles();
        $num = 0;
        $now  = time();
        foreach ($cfs as $f) {
            $arr = $this->analyseContrlFile($f);
            if(empty($arr)) continue;

            //控制器
            $moduleName = strtolower($arr['moduleName']);
            $contrlName = strtolower($arr['contrlName']);
            $isCli      = ($moduleName=='cli');
            $checkContrl = Action::getInfo($moduleName, $contrlName);
            if($checkContrl) {
                $data = array(
                    'status'        => 1,
                    'title'         => $arr['contrlTitle'],
                    'batch_time'    => $now,
                    'update_time'   => $now,
                );
                $res = Action::upData($data, "ac_id=" . $checkContrl->toArray()['ac_id']);
            }else{
                $res = Action::addData(array(
                    'site_id'       => $this->siteId,
                    'status'        => 1,
                    'type'          => ($isCli?-2:2),
                    'module'        => $moduleName,
                    'controller'    => $contrlName,
                    'title'         => $arr['contrlTitle'],
                    'batch_time'    => $now,
                    'create_time'   => $now,
                ));
            }
            if($res) $num++;


            //动作
            foreach ($arr['actionNames'] as $actionName=>$actionTitle) {
                $actionName = strtolower($actionName);
                $checkAction = Action::getInfo($moduleName, $contrlName, $actionName);
                if($checkAction) {
                    $data = array(
                        'status'        => 1,
                        'title'         => $actionTitle,
                        'batch_time'    => $now,
                        'update_time'   => $now,
                    );
                    $res = Action::upData($data, "ac_id=" . $checkAction->toArray()['ac_id']);
                }else{
                    $res = Action::addData(array(
                        'site_id'       => $this->siteId,
                        'status'        => 1,
                        'type'          => ($isCli?-3:3),
                        'module'        => $moduleName,
                        'controller'    => $contrlName,
                        'action'        => $actionName,
                        'title'         => $actionTitle,
                        'remart'        => isset($arr['actionDescs'][$actionName]) ? $arr['actionDescs'][$actionName] : '',
                        'batch_time'    => $now,
                        'create_time'   => $now,
                    ));
                }

                if($res) $num++;
            }


            //取消已不存在的动作
            Action::upData(array(
                'status'=>0, 'batch_time'=>$now
            ), "site_id=$this->siteId AND status=1 AND module='$moduleName' AND controller='$contrlName' AND batch_time!=".$now);

        }

        return $num;
    }


    /**
     * 分析控制器文件
     * @param string $filepath 文件路径
     *
     * @return array|bool
     */
    public function analyseContrlFile($filepath='') {
        if(empty($filepath) ||
            (stripos($filepath, 'Controller.php')===false && stripos($filepath, 'Task.php')===false)) return false;

        $pathArr = explode(DS, str_replace(array(MODULDIR, '.php'), '', $filepath));
        $moduleName = $pathArr[0];
        $contrlName = str_ireplace(['Controller', 'Task'], '', $pathArr[2]);
        $actionNames = $actionDescs = [];
        $isCli = (strtolower($moduleName)=='clie');

        $contrlClassName = "Apps\\Modules\\" .ucwords($moduleName) ."\\" .ucwords($pathArr[1]) ."\\" .$pathArr[2];
        $refContrlClass = new \ReflectionClass($contrlClassName);

        //获取控制器类注释
        $docomm = $refContrlClass->getDocComment();
        preg_match_all('/Class(.*)\n/i', $docomm, $match);
        $contrlTitle = empty($match[1]) ? $contrlName : trim($match[1][0]);
        //var_dump('$contrlTitle', $match, $contrlTitle, '--------------');

        $methods = $refContrlClass ->getMethods();
        if(!empty($methods)) {
            foreach($methods as $methodObj) {
                $methodName = $methodObj->name;
                if($methodObj->isPublic() && strrchr($methodName, 'Action')=='Action'){
                    $actionName = substr($methodName, 0, -6);
                    //var_dump($actionName);

                    //获取方法注释
                    $doc = $methodObj->getDocComment();
                    $actionTitle = $actionDesc = $actionName;
                    if($doc) {
                        preg_match_all('/(@title[ -]{0,3})(.*?\n)(.*)(@desc[ -]{0,3})(.*?\n)/is', $doc, $matchs);
                        if(!empty($matchs[0])) {
                            $actionTitle = trim($matchs[2][0]);
                            $actionDesc = trim($matchs[5][0]);
                        }
                    }

                    $actionNames[$actionName] = $actionTitle;
                    $actionDescs[$actionName] = $actionDesc;
                }
            }
        }


        $res = array(
            'moduleName'    => $moduleName,
            'contrlName'    => $contrlName,
            'contrlTitle'   => $contrlTitle,
            'actionNames'   => $actionNames,
            'actionDescs'   => $actionDescs,
        );

        return $res;
    }


    /**
     * 获取CLI下的tasks信息
     * @return array
     */
    public function getCliTasks() {
        $taskFiles = DirectoryHelper::getFileTree(CTASKDIR, 'file');
        $taskArr = [];
        if(!empty($taskFiles)) {
            foreach ($taskFiles as $taskFile) {
                $item = $this->analyseContrlFile($taskFile);
                if($item) array_push($taskArr, $item);
            }
        }

        return $taskArr;
    }



}