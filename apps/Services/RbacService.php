<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/12/13
 * Time: 9:25
 * Desc: -RBAC权限服务类
 */

namespace Apps\Services;

use Lkk\Helpers\ArrayHelper;
use Apps\Models\Action;
use Apps\Models\AdmModule;
use Apps\Models\AdmOperateAction;
use Apps\Models\AdmOperation;

class RbacService extends ServiceBase {


    /**
     * 构造函数
     */
    public function __construct($vars=[]) {
        parent::__construct($vars);

    }


    /**
     * 获取[模块-操作]树
     * @param int   $parentId 父节点ID
     * @param null  $parentNode 父节点对象
     * @param int   $siteId 站点ID
     * @param array $roleChkOpids 角色已选中的操作ID数组
     *
     * @return array
     */
    public static function getModuleOperationTree($parentId=0, $parentNode=null, $siteId=0, $roleChkOpids=[]) {
        if(empty($parentNode)) {
            $tree = AdmModule::getTreeObj([], $siteId);
            $parentNode = $tree->getNodeById($parentId);
        }

        $treeData = [];
        if($childrens = $parentNode->getChildren()) {
            foreach ($childrens as $children) {
                if($children->status==0) continue;

                $module = $children->toArray();
                $moduleId = $children->getId();
                $childHasCheck = false;
                $_childrenModule = self::getModuleOperationTree($moduleId, $children, $siteId, $roleChkOpids);
                $_childrenOprate = [];

                //普通操作
                $operations = AdmOperation::getList(['is_del'=>0,'status'=>1,'module_id'=>$children->getId()],'*','sort ASC,id ASC');
                if(!empty($operations)) {
                    foreach ($operations as $operation) {
                        $isChk = in_array($operation->id, $roleChkOpids);
                        if($isChk) $childHasCheck = true;

                        $item = $operation->toArray();
                        $item['pId'] = $moduleId;
                        $item['children'] = [];
                        $item['open'] = false;
                        $item['isParent'] = false;
                        $item['checked'] = $isChk;
                        $item['type'] = 'operation';
                        $item['moduleId'] = 0;
                        $item['operationId'] = $operation->id;

                        array_push($_childrenOprate, $item);
                    }
                }
                $_childrens = array_merge($_childrenOprate, $_childrenModule);

                $module['name'] = $module['title'];
                $module['pId'] = $parentId;
                $module['children'] = $_childrens;
                $module['open'] = empty($_childrens) ? false : true;
                $module['isParent'] = empty($_childrens) ? true : false;
                $module['checked'] = $childHasCheck;
                $module['type'] = 'module';
                $module['moduleId'] = $children->getId();
                $module['operationId'] = 0;
                array_push($treeData, $module);
            }
        }

        return $treeData;
    }


}