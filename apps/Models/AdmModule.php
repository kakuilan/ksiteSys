<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/4/30
 * Time: 22:42
 * Desc: -数据表模型 adm_module 后台[权限]模块表
 */


namespace Apps\Models;
use BlueM\Tree;

class AdmModule extends BaseModel {

    public function initialize() {
        parent::initialize();
    }


    /**
     * 为树形类获取数据
     * @param int $siteId 站点ID
     *
     * @return mixed
     */
    public static function getData4Tree($siteId=0) {
        $where = [
            'is_del' => 0,
            'site_id' => $siteId,
        ];

        $res = self::getList($where, 'id,status,level,parent_id AS parent,sort,name', 'level ASC,sort ASC,id ASC', 0);
        if($res) {
            $res = $res->toArray();
            foreach ($res as &$re) {
                $re['title'] = $re['name'] ;
                $re['name'] = "[{$re['id']}]". $re['name'] .($re['status'] ? '' : '[停用]');
            }
        }
        return $res;
    }


    /**
     * 获取Tree对象
     * @param array $data
     * @param int $siteId 站点ID
     * @return Tree
     */
    public static function getTreeObj($data=[], $siteId=0) {
        if(!is_array($data) || empty($data)) $data = self::getData4Tree($siteId);
        $tree = new Tree($data);

        return $tree;
    }


    /**
     * 获取模块树
     * @param int $parentId 父节点ID
     * @param null $parentNode 父节点对象
     * @param int $siteId 站点ID
     * @param bool $onlyEnable 仅取有效的:true取有小的,false取全部
     * @return array
     */
    public static function getModuleTree($parentId=0, $parentNode=null, $siteId=0, $onlyEnable=true) {
        if(empty($parentNode)) {
            $tree = self::getTreeObj([], $siteId);
            $parentNode = $tree->getNodeById($parentId);
        }

        $treeData = [];
        if($childrens = $parentNode->getChildren()) {
            foreach ($childrens as $children) {
                if($onlyEnable && $children->status==0) continue;
                $module = $children->toArray();
                $_children = self::getModuleTree($children->getId(), $children, $siteId, $onlyEnable);
                $module['pId'] = $parentId;
                $module['children'] = $_children;
                $module['open'] = empty($_children) ? false : true;
                $module['isParent'] = empty($_children) ? true : false;
                array_push($treeData, $module);
            }
        }

        return $treeData;
    }



}