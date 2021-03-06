<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/7/11
 * Time: 23:16
 * Desc: -用户管理控制器
 */


namespace Apps\Modules\Manage\Controllers;

use Apps\Modules\Manage\Controller;
use Lkk\Helpers\ArrayHelper;
use Lkk\Helpers\CommonHelper;
use Lkk\Helpers\ValidateHelper;
use Apps\Models\UserBase;
use Apps\Models\UserInfo;
use Apps\Models\AdmUser;
use Apps\Services\UserService;


/**
 * Class 后台用户管理控制器
 * @package Apps\Modules\Manage\Controllers
 */
class UserController extends Controller {

    public function initialize () {
        yield parent::initialize();

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
            'listUrl' => makeUrl('manage/user/baselist'),
            'editUrl' => makeUrl('manage/user/baseedit'),
            'pwdUrl' => makeUrl('manage/user/basepwd'),
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
                //$keyCond = ['OR', ['like','username',"%{$keyword}%"], ['like','email',"%{$keyword}%"]];
                $keyCond = ['OR', ['like','username',"{$keyword}%"], ['like','email',"{$keyword}%"]];
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
            $mobileStatusArr = UserBase::getMobileStatusArr();
            $emailStatusArr = UserBase::getEmailStatusArr();
            $typesArr = UserBase::getTypesArr();
            foreach ($list as &$item) {
                $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
                $item['update_time'] = date('Y-m-d H:i:s', $item['update_time']);
                $item['status_desc'] = $statusArr[$item['status']];
                $item['mobile_status_desc'] = $mobileStatusArr[$item['mobile_status']];
                $item['email_status_desc'] = $emailStatusArr[$item['email_status']];
                $item['type_desc'] = $typesArr[$item['type']];

                unset($item['password']);
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
     * @title -基本用户编辑页
     * @desc  -基本用户编辑页
     * @return mixed
     */
    public function baseEditAction() {
        $uid = intval($this->request->get('uid'));
        $info = $uid ? UserBase::findFirst($uid) : [];

        $isAdmin = true;
        if($uid && empty($info)) {
            return $this->alert('信息不存在');
        }elseif($info && $info->site_id==0 && !$isAdmin) {
            return $this->alert('无权限编辑该信息');
        }

        //视图变量
        $this->view->setVars([
            'statusArr' => UserBase::getStatusArr(),
            'mobileStatusArr' => UserBase::getMobileStatusArr(),
            'emailStatusArr' => UserBase::getEmailStatusArr(),
            'typesArr' => UserBase::getTypesArr(),
            'saveUrl' => makeUrl('manage/user/basesave'),
            'listUrl' => makeUrl('manage/user/baselist'),
            'uid' => $uid,
            'info' => $info,
        ]);

        //设置静态资源
        $this->assets->addJs('statics/js/lkkFunc.js');
        $this->assets->addJs('statics/js/plugins/layer/layer.min.js');
        $this->assets->addJs('statics/js/plugins/validate/jquery.validate.min.js');
        $this->assets->addJs('statics/js/plugins/validate/localization/messages_zh.min.js');
        $this->assets->addJs('statics/js/md5.min.js');

        return null;
    }


    /**
     * @title -保存基本用户
     * @desc  -保存基本用户
     * @return mixed
     */
    public function baseSaveAction() {
        $uid = intval($this->request->get('uid'));
        $status = intval($this->request->get('status'));
        $email_status = intval($this->request->get('email_status'));
        $mobile_status = intval($this->request->get('mobile_status'));
        $type = intval($this->request->get('type'));

        $username = trim($this->request->get('username'));
        $email = trim($this->request->get('email'));
        $password = trim($this->request->get('password'));
        $passwordCfr = trim($this->request->get('passwordCfr'));
        $mobile = intval($this->request->get('mobile'));

        //TODO catch db  Exception
        //$mobile = trim($this->request->get('mobile'));

        $userServ = new UserService();
        $isAdmin = true;

        if ($password && $password != $passwordCfr) {
            return $this->fail('2次密码不相同');
        }elseif($mobile && !$userServ->validateMobile($mobile)) {
            return $this->fail($userServ->error());
        }

        $now = time();
        $data = [
            'site_id' => $this->siteId,
            'status' => $status,
            'mobile_status' => empty($mobile) ? -1 : $mobile_status,
            'email_status' => $email_status,
            'type' => $type,
            'mobile' => $mobile,
        ];

        if($uid<=0) {
            if(!$userServ->validateUsername($username) || !$userServ->validateEmail($email) || !$userServ->validateUserpwd($password)) {
                return $this->fail($userServ->error());
            }elseif ($type==0 && !$userServ->checkIsHoldName($username)){ //普通用户检查是否保留的名称
                return $this->fail($userServ->error());
            }elseif (!$userServ->checkUsernameExist($username, $uid)) {
                return $this->fail($userServ->error());
            }

            $data['username'] = $username;
            $data['create_time'] = $now;
            $data['create_ip'] = CommonHelper::ip2UnsignedInt($this->request->getClientAddress());
        }else{
            $user = UserBase::findFirst($uid);
            if(empty($user)) {
                return $this->fail('信息不存在');
            }elseif ($user->site_id==0 && !$isAdmin) {
                return $this->fail('无权限编辑该信息');
            }
        }

        //检查邮箱是否存在
        if(!$userServ->checkEmailExist($email, $uid)) {
            return $this->fail($userServ->error());
        }

        $data['email'] = $email;
        $data['update_time'] = $now;
        if($password) $data['password'] = UserService::makePasswdHash($password);

        if($uid>0) {
            $res = UserBase::upData($data, ['uid'=>$uid]);
        }else{
            $res = UserBase::addData($data);
        }

        return $res ? $this->success($data,'操作成功') : $this->fail('操作失败');
    }


    /**
     * @title -基本用户密码页
     * @desc  -基本用户密码页
     * @return mixed
     */
    public function basePwdAction() {
        $uid = intval($this->request->get('uid'));
        $info = $uid ? UserBase::findFirst($uid) : [];
        if(empty($info)) {
            return $this->alert('信息不存在');
        }

        $isAdmin = true;
        if($info && $info->site_id==0 && !$isAdmin) {
            return $this->alert('无权限编辑该信息');
        }

        //视图变量
        $this->view->setVars([
            'saveUrl' => makeUrl('manage/user/basepwdsave'),
            'listUrl' => makeUrl('manage/user/baselist'),
            'uid' => $uid,
            'info' => $info,
        ]);

        //设置静态资源
        $this->assets->addJs('statics/js/lkkFunc.js');
        $this->assets->addJs('statics/js/plugins/layer/layer.min.js');
        $this->assets->addJs('statics/js/plugins/validate/jquery.validate.min.js');
        $this->assets->addJs('statics/js/plugins/validate/localization/messages_zh.min.js');
        $this->assets->addJs('statics/js/md5.min.js');

        return null;
    }


    /**
     * @title -保存基本用户密码
     * @desc  -保存基本用户密码
     * @return mixed
     */
    public function basePwdsaveAction() {
        $uid = intval($this->request->get('uid'));
        $password = trim($this->request->get('password'));
        $passwordCfr = trim($this->request->get('passwordCfr'));
        $isAdmin = true; //TODO

        $user = UserBase::findFirst($uid);
        if(empty($user)) {
            return $this->fail('信息不存在');
        }elseif ($user->site_id==0 && !$isAdmin) {
            return $this->fail('无权限编辑该信息');
        }

        $userServ = new UserService();
        if(!$userServ->validateUserpwd($password)) {
            return $this->fail($userServ->error());
        }elseif ($password != $passwordCfr) {
            return $this->fail('2次密码不相同');
        }

        $now = time();
        $data = [
            'update_time' => $now,
            'password' => UserService::makePasswdHash($password),
        ];
        $res = UserBase::upData($data, ['uid'=>$uid]);

        return $res ? $this->success($data, '操作成功') : $this->fail('操作失败');
    }


    /**
     * @title -管理员列表页
     * @desc  -管理员列表页
     * @return mixed
     */
    public function managersAction() {
        //视图变量
        $this->view->setVars([
            'statusArr' => AdmUser::getStatusArr(),
            'levelArr' => AdmUser::getLevelArr(),
            'listUrl' => makeUrl('manage/user/managerlist'),
            'editUrl' => makeUrl('manage/user/manageredit'),
            'pwdUrl' => makeUrl('manage/user/managerpwd'),
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
     * @title -管理员列表页json
     * @desc  -管理员列表页json
     * @return mixed
     */
    public function managerListAction() {
        $page = (int)$this->request->get('page');
        $rows = (int)$this->request->get('rows');
        if($page<=0) $page = 1;
        if($rows<=0) $rows = 10;

        $keyword = trim($this->request->get('keyword'));
        $level = trim($this->request->get('level'));
        $status = trim($this->request->get('status'));

        //基本条件
        $isAdmin = true;
        $siteIds = [$this->siteId];
        if($isAdmin) array_push($siteIds, 0);

        $where = ' a.site_id IN ('. implode(',', $siteIds) .') ';
        $binds = [];

        //条件,管理员级别
        if(is_numeric($level)) {
            $where .= " AND a.level= :level: ";
            $binds['level'] = $level;
        }
        //条件,管理员状态
        if(is_numeric($status)) {
            $where .= " AND a.status= :status: ";
            $binds['status'] = $status;
        }


        //条件,关键词
        if(!empty($keyword)) {
            $where .= " AND (u.username LIKE :keyword: OR u.email LIKE :keyword:) ";
            $binds['keyword'] = "{$keyword}%";
        }

        //排序
        $orderFields = ['uid','create_time'];
        $sidx = trim($this->request->get('sidx'));
        $sord = trim($this->request->get('sord'));
        $order = " a.uid desc ";
        if(!empty($sidx) && ArrayHelper::dstrpos($sidx, $orderFields) && ArrayHelper::dstrpos($sord, ['asc', 'desc'])) {
            $order = " a.{$sidx} {$sord} ";
        }

        $paginator = AdmUser::getAdminPages($this->modelsManager, $where, $binds, $order, $rows, $page);
        $pageObj = $paginator->getPaginate();
        $list = $pageObj->items->toArray();

        if(!empty($list)) {
            $statusArr = AdmUser::getStatusArr();
            $levelArr = AdmUser::getLevelArr();
            $userStatusArr = UserBase::getStatusArr();
            $userTypeArr = UserBase::getTypesArr();

            foreach ($list as &$item) {
                $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
                $item['update_time'] = date('Y-m-d H:i:s', $item['update_time']);
                $item['last_login_time'] = date('Y-m-d H:i:s', $item['last_login_time']);
                $item['status_desc'] = $statusArr[$item['status']];
                $item['level_desc'] = $levelArr[$item['level']];
                $item['user_status_desc'] = $userStatusArr[$item['user_status']];
                $item['user_type_desc'] = $userTypeArr[$item['user_type']];
                unset($item['password']);
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
     * @title -管理员编辑页
     * @desc  -管理员编辑页
     * @return mixed
     */
    public function managerEditAction() {
        $uid = intval($this->request->get('uid'));
        $info = $uid ? AdmUser::getInfoByUid($uid) : [];

        $isAdmin = true;
        if($uid && empty($info)) {
            return $this->alert('信息不存在');
        }elseif($info && $info->site_id==0 && !$isAdmin) {
            return $this->alert('无权限编辑该信息');
        }

        //视图变量
        $this->view->setVars([
            'userStatusArr' => UserBase::getStatusArr(),
            'userTypesArr' => UserBase::getTypesArr(),
            'statusArr' => AdmUser::getStatusArr(),
            'levelArr' => AdmUser::getLevelArr(),
            'saveUrl' => makeUrl('manage/user/managersave'),
            'listUrl' => makeUrl('manage/user/managerlist'),
            'userTypeAdm' => UserBase::USER_TYPE_ADMNER,
            'uid' => $uid,
            'info' => $info ? AdmUser::rowToObject($info) : [],
        ]);

        //设置静态资源
        $this->assets->addJs('statics/js/lkkFunc.js');
        $this->assets->addJs('statics/js/plugins/layer/layer.min.js');
        $this->assets->addJs('statics/js/plugins/validate/jquery.validate.min.js');
        $this->assets->addJs('statics/js/plugins/validate/localization/messages_zh.min.js');
        $this->assets->addJs('statics/js/md5.min.js');

        return null;
    }


    /**
     * @title -保存管理员信息
     * @desc  -保存管理员信息
     * @return mixed
     */
    public function managerSaveAction() {
        $uid = intval($this->request->get('uid'));
        $user_status = intval($this->request->get('user_status'));
        $level = intval($this->request->get('level'));
        $status = intval($this->request->get('status'));
        $user_type = intval($this->request->get('user_type'));

        $username = trim($this->request->get('username'));
        $email = trim($this->request->get('email'));
        $frontPassword = trim($this->request->get('frontPassword'));
        $frontPassword2 = trim($this->request->get('frontPassword2'));
        $backPassword = trim($this->request->get('backPassword'));
        $backPassword2 = trim($this->request->get('backPassword2'));

        $userServ = new UserService();
        $isAdmin = true;

        if($frontPassword && $frontPassword!=$frontPassword2) {
            return $this->fail('前台密码2次不相同');
        }elseif ($backPassword && $backPassword!=$backPassword2) {
            return $this->fail('后台密码2次不相同');
        }elseif (empty($email)) {
            return $this->fail('邮箱不能为空');
        }elseif (!$userServ->validateEmail($email)) {
            return $this->fail($userServ->error());
        }

        $now = time();
        $baseData = [
            'status' => $user_status,
            'type' => $user_type,
        ];
        $admData = [
            'site_id' => $this->siteId,
            'level' => $level,
            'status' => $status,
        ];

        $res = false;
        if($uid<=0) { //新增
            if(!$userServ->validateUsername($username) || !$userServ->validateUserpwd($backPassword)) {
                return $this->fail($userServ->error());
            }elseif (!$userServ->checkAdminExist($username, 0)) {
                return $this->fail($userServ->error());
            }

            $user = UserBase::getInfoByUsername($username);
            if(!$userServ->checkEmailExist($email, ($user ? $user->uid : 0))) {
                return $this->fail($userServ->error());
            }

            $ip = CommonHelper::ip2UnsignedInt($this->request->getClientAddress());
            if(empty($user)) {
                $baseData = array_merge($baseData, [
                    'site_id' => $this->siteId,
                    'create_ip' => $ip,
                    'create_time' => $now,
                    'update_time' => $now,
                    'email' => $email,
                    'username' => $username,
                    'password' => UserService::makePasswdHash($frontPassword),
                ]);

                $admData = array_merge($admData, [
                    'password' => UserService::makePasswdHash($backPassword),
                    'create_time' => $now,
                    'update_time' => $now,
                    'create_by' => 0,
                    'update_by' => 0,
                ]);

                //开启事务
                $this->dbMaster->begin();

                $newId = UserBase::addData($baseData);
                $admData['uid'] = intval($newId);

                $admId = AdmUser::addData($admData);
                if($newId && $admId) {
                    $res = true;
                    $uid = $newId;
                    $this->dbMaster->commit();
                }else{
                    $this->dbMaster->rollback();
                }
            }else{
                $baseData['email'] = $email;
                $baseData['update_time'] = $now;
                if(!empty($frontPassword)) $baseData['password'] = UserService::makePasswdHash($frontPassword);

                $admData = array_merge($admData, [
                    'uid' => $user->uid,
                    'password' => UserService::makePasswdHash($backPassword),
                    'create_time' => $now,
                    'update_time' => $now,
                    'create_by' => 0,
                    'update_by' => 0,
                ]);

                //开启事务
                $this->dbMaster->begin();
                $usrRes = UserBase::upData($baseData, ['uid'=>$user->uid]);
                $admRes = AdmUser::addData($admData);
                if($usrRes && $admRes) {
                    $res = true;
                    $uid = $user->uid;
                    $this->dbMaster->commit();
                }else{
                    $this->dbMaster->rollback();
                }
            }
        }else{ //修改
            $user = UserBase::findFirst($uid);
            if(empty($user)) {
                return $this->fail('信息不存在');
            }elseif ($user->site_id==0 && !$isAdmin) {
                return $this->fail('无权限编辑该信息');
            }elseif(!$userServ->checkEmailExist($email, $uid)) {
                return $this->fail($userServ->error());
            }

            $baseData['email'] = $email;
            $baseData['update_time'] = $now;
            if(!empty($frontPassword)) $baseData['password'] = UserService::makePasswdHash($frontPassword);

            $admData['update_time'] = $now;
            $admData['update_by'] = 0;
            if(!empty($backPassword)) $admData['password'] = UserService::makePasswdHash($backPassword);

            //开启事务
            $this->dbMaster->begin();
            $usrRes = UserBase::upData($baseData, ['uid'=>$uid]);
            $admRes = AdmUser::upData($admData, ['uid'=>$uid]);
            if($usrRes && $admRes) {
                $res = true;
                $username = $user->username;
                if(empty($email)) $email = $user->email;
                $this->dbMaster->commit();
            }else{
                $this->dbMaster->rollback();
            }
        }

        $levelArr = AdmUser::getLevelArr();
        $statusArr = AdmUser::getStatusArr();
        $userStatusArr = UserBase::getStatusArr();
        $newData = [
            'uid' => $uid,
            'username' => $username,
            'email' => $email,
            'level_desc' => $levelArr[$level],
            'status_desc' => $statusArr[$status],
            'user_status_desc' => $userStatusArr[$user_status],
        ];

        return $res ? $this->success($newData, '操作成功') : $this->fail('操作失败');
    }


    /**
     * @title -管理员密码页
     * @desc  -管理员密码页
     * @return mixed
     */
    public function managerPwdAction() {
        $uid = intval($this->request->get('uid'));
        $info = $uid ? AdmUser::getInfoByUid($uid) : [];
        if(empty($info)) {
            return $this->alert('信息不存在');
        }

        $isAdmin = true;
        if($info && $info->site_id==0 && !$isAdmin) {
            return $this->alert('无权限编辑该信息');
        }

        //视图变量
        $this->view->setVars([
            'saveUrl' => makeUrl('manage/user/managerpwdsave'),
            'listUrl' => makeUrl('manage/user/managerlist'),
            'uid' => $uid,
            'info' => AdmUser::rowToObject($info),
        ]);

        //设置静态资源
        $this->assets->addJs('statics/js/lkkFunc.js');
        $this->assets->addJs('statics/js/plugins/layer/layer.min.js');
        $this->assets->addJs('statics/js/plugins/validate/jquery.validate.min.js');
        $this->assets->addJs('statics/js/plugins/validate/localization/messages_zh.min.js');
        $this->assets->addJs('statics/js/md5.min.js');

        return null;
    }


    /**
     * @title -保存管理员密码
     * @desc  -保存管理员密码
     * @return mixed
     */
    public function managerPwdsaveAction() {
        $uid = intval($this->request->get('uid'));
        $password = trim($this->request->get('password'));
        $passwordCfr = trim($this->request->get('passwordCfr'));
        $isAdmin = true; //TODO

        $user = AdmUser::getInfoByUid($uid);
        if(empty($user)) {
            return $this->fail('信息不存在');
        }elseif ($user->a->site_id==0 && !$isAdmin) {
            return $this->fail('无权限编辑该信息');
        }

        $userServ = new UserService();
        if(!$userServ->validateUserpwd($password)) {
            return $this->fail($userServ->error());
        }elseif ($password != $passwordCfr) {
            return $this->fail('2次密码不相同');
        }

        $now = time();
        $data = [
            'update_time' => $now,
            'password' => UserService::makePasswdHash($password),
        ];
        $res = AdmUser::upData($data, ['uid'=>$uid]);

        return $res ? $this->success($data, '操作成功') : $this->fail('操作失败');
    }








}