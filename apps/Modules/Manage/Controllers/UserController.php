<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/7/11
 * Time: 23:16
 * Desc: -用户管理控制器
 */


namespace Apps\Modules\Manage\Controllers;

use Kengine\LkkController;
use Lkk\Helpers\ArrayHelper;
use Lkk\Helpers\ValidateHelper;
use Apps\Models\UserBase;
use Apps\Models\UserInfo;


/**
 * Class 后台用户管理控制器
 * @package Apps\Modules\Manage\Controllers
 */
class UserController extends LkkController {

    public function initialize () {
        parent::initialize();

        $this->setHeaderSeo('管理后台', '关键词', '描述');

        //视图变量
        $this->view->setVars([
            'headerSeo' => $this->headerSeo,
        ]);

    }


    /**
     * @title -基本用户列表页
     * @desc  -基本用户列表页
     */
    public function indexAction() {
        //视图变量
        $this->view->setVars([
            'statusArr' => UserBase::getStatusArr(),
            'mobileStatusArr' => UserBase::getMobileStatusArr(),
            'emailStatusArr' => UserBase::getEmailStatusArr(),
            'typesArr' => UserBase::getTypesArr(),
            'listUrl' => makeUrl('manage/user/list'),
            'editUrl' => makeUrl('manage/user/edit'),
            'pwdUrl' => makeUrl('manage/user/passwd'), //修改密码
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

        return null;
    }


    /**
     * @title -基本用户列表json
     * @desc  -基本用户列表json
     * @return mixed
     */
    public function baseListAction() {
        $page = (int)$this->request->get('page');
        $rows = (int)$this->request->get('rows');
        if($page<=0) $page = 1;
        if($rows<=0) $rows = 10;

        $keyword = trim($this->request->get('keyword'));
        $status = trim($this->request->get('status'));
        $mobile_status = trim($this->request->get('mobile_status'));
        $email_status = trim($this->request->get('email_status'));
        $type = trim($this->request->get('type'));

        //基本条件
        $isAdmin = true;
        $siteIds = [$this->siteId];
        if($isAdmin) array_push($siteIds, 0);
        $where = [
            'and',
            ['site_id' => $siteIds],
        ];

        //条件,用户状态
        if(is_numeric($status)) {
            array_push($where, ['status'=>$status]);
        }
        //条件,手机状态
        if(is_numeric($mobile_status)) {
            array_push($where, ['mobile_status'=>$mobile_status]);
        }
        //条件,邮箱状态
        if(is_numeric($email_status)) {
            array_push($where, ['email_status'=>$email_status]);
        }
        //条件,用户类型
        if(is_numeric($type)) {
            array_push($where, ['type'=>$type]);
        }

        //条件,关键词
        if(!empty($keyword)) {
            if(ValidateHelper::isMobile($keyword)) {
                array_push($where, ['mobile'=>$keyword]);
            }else{
                $keyCond = ['OR', ['like','username',"%{$keyword}%"], ['like','email',"%{$keyword}%"]];
                array_push($where, $keyCond);
            }
        }

        //排序
        $orderFields = ['uid','create_time'];
        $sidx = trim($this->request->get('sidx'));
        $sord = trim($this->request->get('sord'));
        $order = " uid asc,create_time desc ";
        if(!empty($sidx) && ArrayHelper::dstrpos($sidx, $orderFields) && ArrayHelper::dstrpos($sord, ['asc', 'desc'])) {
            $order = " {$sidx} {$sord} ";
        }

        $paginator = UserBase::getPaginator('*', $where, $order, $rows, $page);
        $pageObj = $paginator->getPaginate();
        $list = $pageObj->items->toArray();

        if(!empty($list)) {
            $statusArr = UserBase::getStatusArr();
            foreach ($list as &$item) {
                $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
                $item['update_time'] = date('Y-m-d H:i:s', $item['update_time']);
                $item['status_desc'] = $statusArr[$item['status']];
                $item['mobile_status_desc'] = $statusArr[$item['mobile_status']];
                $item['email_status_desc'] = $statusArr[$item['email_status']];
                $item['type_desc'] = $statusArr[$item['type']];
            }
        }

        $data = [
            'list' => $list, //数据列表
            'page' => $page, //当前页码
            'total' => $pageObj->total_pages, //总页数
            'records' => $pageObj->total_items, //总记录数
        ];

        return $this->success(['data'=>$data]);
    }


    public function baseEditAction() {

    }


    public function baseSaveAction() {

    }


    public function passwdAction() {

    }


    public function passwdSaveAction() {

    }







}