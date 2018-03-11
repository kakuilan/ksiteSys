<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/4/30
 * Time: 20:52
 * Desc: -数据表模型 adm_menu 后台菜单表
 */


namespace Apps\Models;

use Phalcon\Mvc\Model\Query\Builder as QueryBuilder;
use Phalcon\Mvc\Model\Query;
use BlueM\Tree;
use Kengine\LkkCmponent;

class AdmMenu extends BaseModel {

    public function initialize() {
        parent::initialize();

        //reusable可重用的关联查询
        $this->hasOne('action_id', Action::class, 'ac_id', ['alias' => 'action', 'reusable' => true]);
    }


    /**
     * 为树形类获取数据
     * @param int $siteId 站点ID
     *
     * @return mixed
     */
    public static function getData4Tree($siteId=0) {
        $qryBuilder = new QueryBuilder();
        $res = $qryBuilder->addFrom(__CLASS__, 'm')
            ->columns(['m.id','m.status','m.parent_id AS parent','m.sort','m.title','m.tag',"CONCAT('/',a.module,'/',a.controller,'/',a.action) AS url"])
            ->leftJoin(Action::class, 'a.ac_id=m.action_id', 'a')
            ->where("m.is_del=0 AND m.site_id={$siteId}")
            ->orderBy('m.sort ASC,m.id ASC')
            ->getQuery()
            ->execute();

        if($res) {
            $res = $res->toArray();
            foreach ($res as &$re) {
                $re['name'] = "[{$re['id']}]". $re['title'] .($re['status'] ? '' : '[停用]');
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
     * 获取菜单树
     * @param int $parentId 父节点ID
     * @param null $parentNode 父节点对象
     * @param int $siteId 站点ID
     * @param bool $onlyEnable 仅取有效的:true取有小的,false取全部
     * @return array
     */
    public static function getMenuTree($parentId=0, $parentNode=null, $siteId=0, $onlyEnable=true) {
        if(empty($parentNode)) {
            $tree = self::getTreeObj([], $siteId);
            $parentNode = $tree->getNodeById($parentId);
        }

        $menuData = [];
        if($childrens = $parentNode->getChildren()) {
            $pinyin = LkkCmponent::pinyin();
            foreach ($childrens as $children) {
                if($onlyEnable && $children->status==0) continue;
                $menu = $children->toArray();
                $_children = self::getMenuTree($children->getId(), $children, $siteId, $onlyEnable);
                $menu['name'] = (isset($menu['name']) && !empty($menu['name'])) ? $menu['name'] : $menu['title'];
                $menu['pId'] = $parentId;
                $menu['children'] = $_children;
                $menu['open'] = empty($_children) ? false : true;
                $menu['isParent'] = empty($_children) ? false : true;
                $menu['url'] = empty($menu['url']) ? 'javascript:;' : $menu['url'];
                $menu['class'] = empty($menu['tag']) ? 'fa fa-list' : $menu['tag'];

                //拼音
                $menu['pinyin'] = $pinyin->permalink($menu['title']);
                $menu['py'] = $pinyin->abbr($menu['title']);

                array_push($menuData, $menu);
            }
        }

        return $menuData;
    }


    /**
     * 过滤菜单数据
     * @param array $data
     * @param null $filter
     * @param int $siteId 站点ID
     * @return array|bool|mixed
     */
    public static function filterMenus($data=[], $filter=null, $siteId=0) {
        if(!is_array($data)) return false;
        if(empty($data)) {
            $data = self::getData4Tree($siteId);
        }

        if(empty($filter)) {
            $data = array_filter($data, $filter);
        }

        foreach ($data as &$item) {
            $item['name'] = $item['title'];
            $item['pId'] = $item['parent'];
        }

        return $data;
    }




}