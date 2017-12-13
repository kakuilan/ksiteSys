<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/7/1
 * Time: 15:22
 * Desc: -[权限]模块操作管理控制器
 */


namespace Apps\Modules\Manage\Controllers;

use Kengine\LkkController;
use Apps\Models\Action;
use Apps\Models\AdmModule;
use Apps\Models\AdmOperation;
use Apps\Models\AdmOperateAction;


class OperationController extends LkkController {

    public function initialize () {
        parent::initialize();

        $this->setHeaderSeo('管理后台', '关键词', '描述');

        //视图变量
        $this->view->setVars([
            'headerSeo' => $this->headerSeo,
        ]);

    }


    /**
     * @title -权限模块操作列表json
     * @desc  -后台权限模块操作列表json
     */
    public function indexAction(){
        //模块ID
        $mid = intval($this->request->get('mid'));
        $list = AdmOperation::getList(['is_del'=>0,'module_id'=>$mid], '*', 'sort ASC,id ASC');
        $list = $list ? $list->toArray() : [];
        foreach ($list as &$item) {
            $item['name'] .= ($item['status'] ? '' : '[停用]');
        }

        return $this->success(['data'=>$list]);
    }



    /**
     * @title -新增编辑权限模块操作页
     * @desc  -后台权限模块操作新增/编辑页面
     * @return null
     */
    public function editAction() {
        $id = intval($this->request->get('id'));
        $mid = intval($this->request->get('mid'));

        $selActions = [];
        $oprActions = AdmOperateAction::getList(['operation_id'=>$id]);
        $oprActiIds = $oprActions ? array_column($oprActions->toArray(), 'action_id') : [];

        $allActions = Action::getSiteMangeActions($this->siteId);
        foreach ($allActions as $k=>$action) {
            if(in_array($action->ac_id, $oprActiIds)) {
                array_push($selActions, $action);
                unset($allActions[$k]);
            }
        }

        $curInfo = AdmOperation::findFirst($id);

        //视图变量
        $this->view->setVars([
            'saveUrl' => makeUrl('manage/operation/save'),
            'listUrl' => makeUrl('manage/module/index'),
            'id' => $id,
            'mid' => $mid,
            'allActions' => $allActions,
            'selActions' => $selActions,
            'curInfo' => $curInfo,
        ]);

        //设置静态资源
        $this->assets->addCss('statics/css/plugins/multiselect/style.css');
        $this->assets->addJs('statics/js/plugins/layer/layer.min.js');
        $this->assets->addJs('statics/js/plugins/validate/jquery.validate.min.js');
        $this->assets->addJs('statics/js/plugins/validate/localization/messages_zh.min.js');
        $this->assets->addJs('statics/js/plugins/multiselect/multiselect.min.js');

        return null;
    }



    /**
     * @title -保存权限模块操作
     * @desc  -保存后台权限模块操作
     */
    public function saveAction() {
        $id = intval($this->request->get('id'));
        $mid = intval($this->request->get('mid'));
        $status = intval($this->request->get('status'));
        $name = trim($this->request->get('name'));
        $tag = strtolower(trim($this->request->get('tag')));
        $desc = trim($this->request->get('desc'));
        $sort = intval($this->request->get('sort'));
        $newAids = $this->request->get('new_aid');

        $module = AdmModule::findFirst($mid);
        if(empty($module)) {
            $this->fail('参数错误,模块不存在');
        }elseif (empty($name)) {
            return $this->fail('操作名称不能为空');
        }elseif (empty($tag)) {
            return $this->fail('操作标识不能为空');
        }elseif (empty($newAids)) {
            return $this->fail('包含的动作不能为空');
        }

        $newAids = array_unique(array_filter($newAids, function ($id) {
            return intval($id);
        }));
        $actionNum = count($newAids);
        if ($actionNum > AdmOperateAction::OPERATE_HAS_ACTION_MAXNUM) {
            return $this->fail('一个操作包含的动作至多' .AdmOperateAction::OPERATE_HAS_ACTION_MAXNUM.'个' );
        }

        $checkOprate = AdmOperation::getRow([
            'and',
            ['<>','id', $id],
            [
                'or',
                ['tag'=>$tag],
                [
                    'and',
                    ['module_id'=>$mid],
                    ['name'=>$name],
                ],
            ]
        ]);
        if($checkOprate && $checkOprate->is_del==0) {
            $error = ($checkOprate->tag == $tag) ? '该操作标识已存在' : '该操作名称已存在';
            return $this->fail($error);
        }elseif ($checkOprate && $id<=0) {
            $id = $checkOprate->id;
        }

        $now = time();
        $data = [
            'module_id' => $mid,
            'is_del' => 0,
            'status' => $status,
            'sort' => $sort,
            'name' => $name,
            'tag' => $tag,
            'desc' => $desc,
            'action_num' => $actionNum,
            'create_time' => $now,
            'create_by' => 0,
            'update_time' => $now,
            'update_by' => 0,
        ];

        //事务
        $this->dbMaster->begin();
        $res = false;
        if($id>0) {
            //删除旧关联动作
            AdmOperateAction::delData(['operation_id'=>$id]);
            $operRes = AdmOperation::upData($data, ['id'=>$id, 'module_id'=>$mid]);
        }else{
            $operRes = AdmOperation::addData($data);
            if($operRes) {
                $id = intval($operRes);
                if($data['sort']<=0) AdmOperation::upData(['sort'=>$id ], ['id'=>$id]);
            }
        }

        //新增关联动作
        $oprActDatas = [];
        foreach ($newAids as $newAid) {
            $oprActData = [
                'operation_id' => $id,
                'action_id' => $newAid,
            ];
            array_push($oprActDatas, $oprActData);
        }
        $refRes = AdmOperateAction::addMultiData($oprActDatas);

        if($operRes && $refRes) {
            $this->dbMaster->commit();
            $res = true;
        }else{
            $this->dbMaster->rollback();
        }

        $data['id'] = $id;
        $data['name'] = $data['name'] .($status ? '' : '[停用]');

        return $res ? $this->success(['msg'=>'操作成功', 'data'=>$data]) : $this->fail('操作失败');
    }


    /**
     * @title -删除权限模块操作
     * @desc  -删除后台权限模块操作
     */
    public function delAction() {
        $id = intval($this->request->get('id'));
        $mid = intval($this->request->get('mid'));
        $data = [
            'is_del' => 1,
            'status' => 0,
            'update_time' => time(),
            'update_by' => 0,
        ];

        //事务
        $this->dbMaster->begin();
        $res = AdmOperation::upData($data, ['id'=>$id, 'module_id'=>$mid]);
        AdmOperateAction::delData(['operation_id'=>$id]);//删除关联动作
        if($res) {
            $this->dbMaster->commit();
        }else{
            $this->dbMaster->rollback();
        }

        return $res ? $this->success('操作成功') : $this->fail('操作失败');
    }



}