<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/12/13
 * Time: 9:22
 * Desc: -[权限]模块管理控制器
 */

namespace Apps\Modules\Manage\Controllers;

use Apps\Modules\Manage\Controller;
use Apps\Models\Action;
use Apps\Models\AdmRoleFunc;
use Apps\Models\AdmModule;
use Apps\Services\RbacService;


/**
 * Class 后台模块管理控制器
 * @package Apps\Modules\Manage\Controllers
 */
class ModuleController extends Controller {

    public function initialize () {
        yield parent::initialize();

        $this->setHeaderSeo('管理后台', '关键词', '描述');

        //视图变量
        $this->view->setVars([
            'headerSeo' => $this->headerSeo,
        ]);

    }


    /**
     * @title -权限模块列表页
     * @desc  -管理后台权限模块列表页
     */
    public function indexAction(){
        if($this->request->isAjax()) {
            $treeData = AdmModule::getModuleTree(0, null, $this->siteId, false);
            return $this->success($treeData);
        }

        //视图变量
        $this->view->setVars([
            'listUrl' => makeUrl('manage/module/index'),
            'editUrl' => makeUrl('manage/module/edit'),
            'delUrl' => makeUrl('manage/module/del'),
            'operaListUrl' => makeUrl('manage/operation/index'),
            'operaEditUrl' => makeUrl('manage/operation/edit'),
            'operaDelUrl' => makeUrl('manage/operation/del'),
        ]);

        //设置静态资源
        $this->assets->addCss('statics/js/plugins/zTree/css/zTreeStyle/zTreeStyle.css');
        $this->assets->addJs('statics/js/plugins/zTree/js/jquery.ztree.all.js');
        $this->assets->addJs('statics/js/plugins/layer/layer.min.js');

        return null;
    }


    /**
     * @title -模块树形JSON
     * @desc  -后台角色授权的模块树形JSON
     */
    public function treeAction() {
        $rid = intval($this->request->get('rid')); //角色ID

        //该角色已选中的操作ID数组
        $roleChkOpids = [];
        $roleFuns = AdmRoleFunc::getList(['role_id'=>$rid]);
        if($roleFuns) $roleChkOpids = array_column($roleFuns->toArray(), 'operation_id');

        $treeData = RbacService::getModuleOperationTree(0, null, $this->siteId, $roleChkOpids);
        return $this->success($treeData);
    }


    /**
     * @title -新增编辑权限模块页
     * @desc  -后台权限模块新增/编辑页面
     * @return null
     */
    public function editAction() {
        $id = intval($this->request->get('id'));
        $parent = intval($this->request->get('parent'));

        $module = AdmModule::findFirst($id);
        $parentModule = AdmModule::findFirst($parent);

        //视图变量
        $this->view->setVars([
            'saveUrl' => makeUrl('manage/module/save'),
            'listUrl' => makeUrl('manage/module/index'),
            'id' => $id,
            'parent' => $parent,
            'module' => $module,
        ]);

        //设置静态资源
        $this->assets->addJs('statics/js/plugins/layer/layer.min.js');
        $this->assets->addJs('statics/js/plugins/validate/jquery.validate.min.js');
        $this->assets->addJs('statics/js/plugins/validate/localization/messages_zh.min.js');

        return null;
    }


    /**
     * @title -保存权限模块
     * @desc  -保存后台权限模块
     */
    public function saveAction() {
        $id = intval($this->request->get('id'));
        $parent = intval($this->request->get('parent'));
        $status = intval($this->request->get('status'));
        $name = trim($this->request->get('name'));
        $desc = trim($this->request->get('desc'));
        $sort = intval($this->request->get('sort'));
        $now = time();

        $module = AdmModule::findFirst($id);
        $parentModule = AdmModule::findFirst($parent);
        $level = $parentModule ? ($parentModule->level +1)  : 0;

        if($parent>0 && empty($parentModule)) {
            return $this->fail('参数错误，信息不存在');
        }elseif (empty($name)) {
            return $this->fail('模块名称不能为空');
        }

        $data = [
            'site_id' => $this->siteId,
            'is_del' => '0',
            'status' => $status,
            'level' => $level,
            'parent_id' => $parent,
            'sort' => $sort,
            'name' => $name,
            'desc' => $desc,
            'create_time' => $now,
            'create_by' => 0,
            'update_time' => $now,
            'update_by' => 0,
        ];

        //检查同级是否有相同的名称
        $checkModule = AdmModule::getRow([
            'and',
            ['site_id' => $this->siteId],
            ['parent_id' => $parent],
            ['name'=>$name],
            ['<>','id', $id],
        ]);
        if($checkModule && $checkModule->is_del==0) {
            return $this->fail('该模块名称已存在');
        }elseif ($checkModule && $id<=0) {
            $id = $checkModule->id;
        }

        if($id>0) {
            $res = AdmModule::upData($data, ['id'=>$id, 'site_id'=>$this->siteId]);
        }else{
            $res = AdmModule::addData($data);
            if($res) {
                $id = intval($res);
                if($data['sort']<=0) AdmModule::upData(['sort'=>$id ], ['id'=>$id]);
            }
        }
        $data['id'] = $id;
        $data['name'] = "[{$id}]". $data['name'] .($status ? '' : '[停用]');

        return $res ? $this->success($data, '操作成功') : $this->fail('操作失败');
    }


    /**
     * @title -删除权限模块
     * @desc  -删除后台权限模块
     */
    public function delAction() {
        $id = intval($this->request->get('id'));
        $data = [
            'is_del' => 1,
            'status' => 0,
            'update_time' => time(),
            'update_by' => 0,
        ];
        $res = AdmModule::upData($data, ['id'=>$id, 'site_id'=>$this->siteId]);

        return $res ? $this->success([], '操作成功') : $this->fail('操作失败');
    }


}