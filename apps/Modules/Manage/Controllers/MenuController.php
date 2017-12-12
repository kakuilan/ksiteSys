<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/12/11
 * Time: 21:47
 * Desc: -
 */


namespace Apps\Modules\Manage\Controllers;

use Kengine\LkkController;
use Apps\Models\Action;
use Apps\Models\AdmMenu;
use Apps\Services\ActionService;

/**
 * Class 后台菜单控制器
 * @package Apps\Modules\Manage\Controllers
 */
class MenuController extends LkkController {

    public function initialize () {
        parent::initialize();

        $this->setHeaderSeo('管理后台', '关键词', '描述');

        //视图变量
        $this->view->setVars([
            'headerSeo' => $this->headerSeo,
        ]);

    }


    /**
     * @title -菜单列表页
     * @desc  -管理后台菜单列表页
     */
    public function indexAction(){
        if($this->request->isAjax()) {
            $menus = AdmMenu::getMenuTree(0, null, $this->siteId, false);
            return $this->success(['data'=>$menus]);
        }

        //视图变量
        $this->view->setVars([
            'listUrl' => makeUrl('manage/menu/index'),
            'editUrl' => makeUrl('manage/menu/edit'),
            'delUrl' => makeUrl('manage/menu/del'),
            'updatesysactUrl' => makeUrl('manage/menu/updatesysact'),
        ]);

        //设置静态资源
        $this->assets->addCss('statics/js/plugins/zTree/css/zTreeStyle/zTreeStyle.css');
        $this->assets->addJs('statics/js/plugins/zTree/js/jquery.ztree.all.js');
        $this->assets->addJs('statics/js/plugins/layer/layer.min.js');

        return null;
    }


    /**
     * @title -授权的菜单列表
     * @desc  -经系统授权的用户菜单列表,json格式
     */
    public function authListAction() {
        $menus = AdmMenu::getMenuTree(0, null, $this->siteId, true);
        //TODO 权限过滤菜单
        return $this->success(['data'=>$menus]);
    }


    /**
     * @title -新增编辑菜单页
     * @desc  -后台菜单新增/编辑页面
     * @return null
     */
    public function editAction() {
        $id = intval($this->request->get('id'));
        $parent = intval($this->request->get('parent'));

        $menu = AdmMenu::findFirst($id);
        $allActions = Action::getSiteMangeActions($this->siteId);

        //视图变量
        $this->view->setVars([
            'saveUrl' => makeUrl('manage/menu/save'),
            'listUrl' => makeUrl('manage/menu/index'),
            'allActions' => $allActions,
            'id' => $id,
            'parent' => $parent,
            'action_id' => $menu ? $menu->action_id : 0,
            'menu' => $menu,
        ]);

        //设置静态资源
        $this->assets->addJs('statics/js/plugins/layer/layer.min.js');
        $this->assets->addJs('statics/js/plugins/validate/jquery.validate.min.js');
        $this->assets->addJs('statics/js/plugins/validate/localization/messages_zh.min.js');

        return null;
    }


    /**
     * @title -保存菜单
     * @desc  -保存后台菜单
     */
    public function saveAction() {
        $id = intval($this->request->get('id'));
        $parent = intval($this->request->get('parent'));
        $action_id = intval($this->request->get('action_id'));
        $status = intval($this->request->get('status'));
        $title = trim($this->request->get('title'));
        $tag = trim($this->request->get('tag'));
        $sort = intval($this->request->get('sort'));
        $now = time();

        $menu = AdmMenu::findFirst($id);
        $parentMenu = AdmMenu::findFirst($parent);
        $level = $parentMenu ? ($parentMenu->level +1)  : 0;

        if($parent>0 && empty($parentMenu)) {
            return $this->fail('参数错误，信息不存在');
        }elseif (empty($title)) {
            return $this->fail('菜单名称不能为空');
        }


        $data = [
            'site_id' => $this->siteId,
            'is_del' => 0,
            'status' => $status,
            'level' => $level,
            'parent_id' => $parent,
            'sort' => $sort,
            'action_id' => $action_id,
            'title' => $title,
            'tag' => $tag,
            'create_time' => $now,
            'create_by' => 0,
            'update_time' => $now,
            'update_by' => 0,
        ];

        if ($id<=0) {
            $menu = AdmMenu::getRow([
                'site_id' => $this->siteId,
                'is_del' => 1,
                'action_id' => $action_id,
            ]);
            if($menu) {
                $id = $menu->id;
                $data['id'] = $menu->id;
            }
        }

        if($id>0) {
            $res = AdmMenu::upData($data, ['id'=>$id, 'site_id'=>$this->siteId]);
        }else{
            $res = AdmMenu::addData($data);
            if($res) {
                $id = intval($res);
                if($data['sort']<=0) AdmMenu::upData(['sort'=>$id ], ['id'=>$id]);
            }
        }
        $data['id'] = $id;
        $data['title'] = "[{$id}]". $data['title'] .($status ? '' : '[停用]');

        return $res ? $this->success(['msg'=>'操作成功', 'data'=>$data]) : $this->fail('操作失败');
    }


    /**
     * @title -删除菜单
     * @desc  -删除后台菜单
     */
    public function delAction() {
        $id = intval($this->request->get('id'));
        $data = [
            'is_del' => 1,
            'status' => 0,
            'update_time' => time(),
            'update_by' => 0,
        ];
        $res = AdmMenu::upData($data, ['id'=>$id, 'site_id'=>$this->siteId]);

        return $res ? $this->success('操作成功') : $this->fail('操作失败');
    }


    /**
     * @title -更新系统动作
     * @desc  -AJAX更新系统动作
     */
    public function updateSysActAction() {
        $actionService = new ActionService();
        $actionService->updateSystemAction();
        $num = Action::countEnable();
        $msg = "更新成功，当前系统共{$num}个动作";

        return $this->success($msg);
    }





}