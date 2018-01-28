<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/6/3
 * Time: 13:07
 * Desc: -后台角色控制器
 */


namespace Apps\Modules\Manage\Controllers;

use Apps\Modules\Manage\Controller;
use Lkk\Helpers\ArrayHelper;
use Apps\Models\AdmModule;
use Apps\Models\AdmOperateAction;
use Apps\Models\AdmOperation;
use Apps\Models\AdmRole;
use Apps\Models\AdmRoleFunc;


/**
 * Class 后台角色控制器
 * @package Apps\Modules\Manage\Controllers
 */
class RoleController extends Controller {

    public function initialize () {
        parent::initialize();

        $this->setHeaderSeo('管理后台', '关键词', '描述');

        //视图变量
        $this->view->setVars([
            'headerSeo' => $this->headerSeo,
        ]);

    }


    /**
     * @title -角色管理首页
     * @desc  -后台角色管理首页
     */
    public function indexAction() {
        //视图变量
        $this->view->setVars([
            'listUrl' => makeUrl('manage/role/list'),
            'editUrl' => makeUrl('manage/role/edit'),
            'delUrl' => makeUrl('manage/role/del'),
            'authorizeUrl' => makeUrl('manage/role/authorize'),
        ]);

        //设置静态资源
        $this->assets->addCss('statics/css/jquery-ui.min.css');
        $this->assets->addCss('statics/css/plugins/jqgrid/ui.jqgrid-bootstrap.css');
        $this->assets->addCss('statics/css/plugins/chosen/chosen.min.css');
        $this->assets->addJs('statics/js/lkkFunc.js');
        $this->assets->addJs('statics/js/plugins/layer/layer.min.js');
        $this->assets->addJs('statics/js/plugins/jqgrid/jquery.jqGrid.min.js');
        $this->assets->addJs('statics/js/plugins/jqgrid/i18n/grid.locale-cn.js');
        $this->assets->addJs('statics/js/plugins/chosen/chosen.jquery.min.js');
    }


    /**
     * @title -角色列表json
     * @desc  -后台角色列表ajax数据
     */
    public function listAction() {
        $page = (int)$this->request->get('page');
        $rows = (int)$this->request->get('rows');
        if($page<=0) $page = 1;
        if($rows<=0) $rows = 10;

        $keyword = trim($this->request->get('keyword'));
        $status = trim($this->request->get('status'));

        //排序
        $orderFields = ['sort','create_time'];
        $sidx = trim($this->request->get('sidx'));
        $sord = trim($this->request->get('sord'));
        $order = " sort asc,create_time desc ";
        if(!empty($sidx) && ArrayHelper::dstrpos($sidx, $orderFields) && ArrayHelper::dstrpos($sord, ['asc', 'desc'])) {
            $order = " {$sidx} {$sord} ";
        }

        $where = [
            'and',
            ['site_id' => $this->siteId],
            ['is_del' => 0],
        ];

        if(is_numeric($status)) {
            array_push($where, ['status'=>$status]);
        }
        if(!empty($keyword)) {
            array_push($where, ['like','name',"%{$keyword}%"]);
        }

        $paginator = AdmRole::getPaginator('*', $where, $order, $rows, $page);
        $pageObj = $paginator->getPaginate();
        $list = $pageObj->items->toArray();

        if(!empty($list)) {
            foreach ($list as &$item) {
                $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
                $item['update_time'] = date('Y-m-d H:i:s', $item['update_time']);
                $item['status_desc'] = $item['status'] ? '启用' : '禁用';
            }
        }

        $data = [
            'list' => $list, //数据列表
            'page' => $page, //当前页码
            'total' => $pageObj->total_pages, //总页数
            'records' => $pageObj->total_items, //总记录数
        ];

        return $this->success($data);
    }


    /**
     * @title -新增编辑角色页
     * @desc  -后台角色新增/编辑页面
     */
    public function editAction() {
        $id = intval($this->request->get('id'));
        $role = AdmRole::findFirst($id);

        //视图变量
        $this->view->setVars([
            'saveUrl' => makeUrl('manage/role/save'),
            'listUrl' => makeUrl('manage/role/index'),
            'id' => $id,
            'role' => $role,
        ]);

        //设置静态资源
        $this->assets->addJs('statics/js/lkkFunc.js');
        $this->assets->addJs('statics/js/plugins/layer/layer.min.js');
        $this->assets->addJs('statics/js/plugins/validate/jquery.validate.min.js');
        $this->assets->addJs('statics/js/plugins/validate/localization/messages_zh.min.js');

        return null;
    }


    /**
     * @title -保存角色
     * @desc  -保存后台角色
     */
    public function saveAction() {
        $id = intval($this->request->get('id'));
        $name = trim($this->request->get('name'));
        $desc = trim($this->request->get('desc'));
        $sort = intval($this->request->get('sort'));
        $status = intval($this->request->get('status'));
        $now = time();

        if(empty($name)) {
            return $this->fail('角色名称不能为空');
        }

        $data = [
            'site_id' => $this->siteId,
            'is_del' => '0',
            'status' => $status,
            'sort' => $sort,
            'name' => $name,
            'desc' => $desc,
            'create_time' => $now,
            'create_by' => 0,
            'update_time' => $now,
            'update_by' => 0,
        ];

        if($id>0) {
            $res = AdmRole::upData($data, ['id'=>$id]);
        }else{
            $res = AdmRole::addData($data);
            if($res) {
                $id = intval($res);
                $data['id'] = $id;

                if($data['sort']<=0) AdmRole::upData(['sort'=>$id ], ['id'=>$id]);
            }
        }

        return $res ? $this->success($data, '操作成功') : $this->fail('操作失败');
    }


    /**
     * @title -删除角色
     * @desc  -删除后台角色
     */
    public function delAction() {
        $id = intval($this->request->get('id'));
        $data = [
            'is_del' => 1,
            'status' => 0,
            'update_time' => time(),
            'update_by' => 0,
        ];
        $res = AdmRole::upData($data, ['id'=>$id, 'site_id'=>$this->siteId]);

        return $res ? $this->success([], '操作成功') : $this->fail('操作失败');
    }


    /**
     * @title -角色授权页面
     * @desc  -后台角色授权页面
     * @return null
     */
    public function authorizeAction() {
        $rid = intval($this->request->get('rid'));
        $role = AdmRole::findFirst($rid);

        //视图变量
        $this->view->setVars([
            'listUrl' => makeUrl('manage/role/list'),
            'treeUrl' => makeUrl('manage/module/tree', ['rid'=>$rid]),
            'saveUrl' => makeUrl('manage/role/saveauthorize', ['rid'=>$rid]),
            'role' => $role,
        ]);

        //设置静态资源
        $this->assets->addCss('statics/js/plugins/zTree/css/zTreeStyle/zTreeStyle.css');
        $this->assets->addJs('statics/js/plugins/zTree/js/jquery.ztree.all.js');
        $this->assets->addJs('statics/js/plugins/layer/layer.min.js');

        return null;
    }


    /**
     * @title -保存角色授权
     * @desc  -保存角色授权
     */
    public function saveAuthorizeAction() {
        $rid = intval($this->request->get('rid'));
        $role = AdmRole::getRow(['id'=>$rid,'site_id'=>$this->siteId]);
        if(empty($role)) {
            return $this->fail('该角色不存在');
        }

        $opIds = (array)$this->request->get('op_ids');
        $opIds = array_unique(array_filter($opIds));
        if(empty($opIds)) {
            return $this->fail('请选择该角色可进行的操作');
        }

        $funDatas = [];
        $now = time();
        foreach ($opIds as $opId) {
            $item = [
                'role_id' => $rid,
                'operation_id' => $opId,
                'create_time' => $now,
                'create_by' => 0,
            ];
            array_push($funDatas, $item);
        }

        //事务
        $this->dbMaster->begin();

        //删除旧数据
        AdmRoleFunc::delData(['role_id'=>$rid]);
        //新增数据
        $res = AdmRoleFunc::addMultiData($funDatas);
        if($res) {
            $this->dbMaster->commit();
        }else{
            $this->dbMaster->rollback();
        }

        return $res ? $this->success([], '操作成功') : $this->fail('操作失败');
    }



}