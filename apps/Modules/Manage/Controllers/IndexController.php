<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/10/28
 * Time: 20:12
 * Desc: -index控制器
 */


namespace Apps\Modules\Manage\Controllers;

use Apps\Modules\Manage\Controller;
use Apps\Models\Action;
use Apps\Models\AdmUser;
use Apps\Models\AdmOperateLog;
use Apps\Models\UserBase;
use Apps\Models\UserInfo;
use Apps\Services\CaptchaService;
use Apps\Services\Ip2RegionService;
use Apps\Services\UserService;
use Lkk\Helpers\ArrayHelper;
use Lkk\Helpers\CommonHelper;
use Lkk\Helpers\ValidateHelper;

/**
 * Class 后台首页控制器
 * @package Apps\Modules\Manage\Controllers
 */
class IndexController extends Controller {


    public function initialize () {
        parent::initialize();

        $this->setHeaderSeo('管理后台', '关键词', '描述');

        //视图变量
        $this->view->setVars([
            'headerSeo' => $this->headerSeo,
        ]);

    }


    /**
     * @title -管理后台首页
     * @desc  -管理后台首页
     */
    public function index00Action(){
        //视图变量
        $this->view->setVars([
            'mainUrl' => makeUrl('manage/index/main'),
            'menuUrl' => makeUrl('manage/menu/authlist'),
            'logoutUrl' => makeUrl('manage/index/logout'),
        ]);

        //设置静态资源
        $this->assets->addCss('statics/css/adm-tab.css');
        $this->assets->addJs('statics/js/ace-elements.min.js');
        $this->assets->addJs('statics/js/ace.min.js');
        $this->assets->addJs('statics/js/lkkTabMenu.js');

        return null;
    }


    /**
     * @title -管理后台首页
     * @desc  -管理后台首页
     */
    public function indexAction() {
        $loginUid = $this->getLoginUid();
        $info = $this->userService->getManagerSession();
        $info['last_login_time'] = date('Y-m-d H:i:s', $info['last_login_time']);

        //视图变量
        $this->view->setVars([
            'siteUrl' => getSiteUrl(),
            'mainUrl' => makeUrl('manage/index/main'),
            'menuUrl' => makeUrl('manage/menu/authlist'),
            'logoutUrl' => makeUrl('manage/index/logout'),
            'row' => (object)$info,
        ]);

        return null;
    }


    /**
     * @title -管理后台登录页
     * @desc  -管理后台登录页
     */
    public function loginAction() {
        $uid = $this->getLoginUid();
        if($uid>0) return $this->response->redirect(getConf('rbac')->managerDefautlAction);

        //视图变量
        $this->view->setVars([
            'siteUrl' => getSiteUrl(),
            'saveUrl' => makeUrl('manage/index/loginsave'),
            'captchaUrl' => makeUrl('common/captcha/create'),

            //Cross-Site Request Forgery (CSRF)
            'tokenKey' => getConf('site','csrfToken'),
            'tokenVal' => $this->makeCsrfToken(__CLASS__),

            'year' => date('Y'),
            'system' => KSERVER_NAME,
        ]);

        return null;
    }


    /**
     * @title -后台登录保存
     * @desc  -后台登录保存
     */
    public function loginSaveAction() {
        //Csrf检查
        if(!$this->validateCsrfToken('', __CLASS__)) {
            return $this->fail('参数错误,请刷新页面');
        }

        $loginName = $this->getPost('loginName');
        $password = $this->getPost('password');
        $verifyCode = $this->getPost('verifyCode');
        $veriEncode = $this->getPost('veriEncode');
        $remember = intval($this->getPost('remember')); //24小时

        if(empty($verifyCode) || empty($veriEncode)) {
            return $this->fail(20103);
        }

        $captchaServ = new CaptchaService();
        $chkCapt = $captchaServ->validateCode($verifyCode, $veriEncode);
        if(!$chkCapt) {
            return $this->fail($captchaServ->error());
        }

        $this->userService->setRemember($remember);
        $admn = $this->userService->managerLogin($loginName, $password);
        if(!$admn) {
            return $this->fail($this->userService->error());
        }else{
            //$this->userService->makeManagerSession($admn);
            $rbacCnf = getConf('rbac');
            $data = [
                'username' => $loginName,
                'avatar' => '',
                'url' => makeUrl($rbacCnf->managerDefautlAction),
            ];

            return $this->success($data);
        }
    }



    /**
     * @title -管理后台退出
     * @desc  -管理后台退出
     */
    public function logoutAction() {
        $uid = $this->getLoginUid();
        if($uid) {
            $res = $this->userService->managerLogout($uid);
        }

        $loginUrl = makeUrl(getConf('rbac')->managerAuthGateway);
        if($this->request->isAjax()) {
            $data = [
                'loginUrl' => $loginUrl,
            ];
            return $this->success($data);
        }

        return $this->alert('退出成功','success', $loginUrl, 2);
    }


    /**
     * @title -后台主页
     * @desc  -后台主页
     */
    public function mainAction() {
        $loginUid = $this->getLoginUid();
        $agUuid = $this->di->getShared('userAgent')->getAgentUuidSimp();
        $accToken = UserService::makeAccessToken($loginUid, $agUuid, 1800);
        $tokenName = getConf('login', 'tokenName');

        $info = AdmUser::getInfoByUid($loginUid);

        $sessData = $this->userService->getManagerSession();
        $admLevelArr = AdmUser::getLevelArr();

        $info->level_desc = $admLevelArr[$info->level];
        if(empty($info->mobile)) $info->mobile = '';
        $info->last_login_time = date('Y-m-d H:i:s', ($sessData['last_login_time'] ?? $info->last_login_time) );
        $info->last_login_ip = long2ip(($sessData['last_login_ip'] ?? $info->last_login_ip) );

        $ipServ = new Ip2RegionService();
        $info->city = $ipServ->getCityName($info->last_login_ip);
        $info->avatar = $sessData['avatar'] ?? '/assets/img/avatar.png';

        //视图变量
        $this->view->setVars([
            'siteUrl' => getSiteUrl(),
            'saveUrl' => makeUrl('manage/index/saveprofile'),
            'logsUrl' => makeUrl('manage/index/admloglist'),
            'uploadUrl' => makeUrl('api/upload/image', [$tokenName=>$accToken]),
            'row' => AdmUser::rowToObject($info),
        ]);

        return null;
    }


    /**
     * @title -忘记密码页
     * @desc  -忘记密码
     */
    public function forgetpwdAction() {

    }


    /**
     * @title -管理员操作日志JSON
     * @desc  -管理员后台操作日志列表
     */
    public function admloglistAction() {
        list($pageNumber, $pageSize) = $this->getPageNumberNSize();
        $sortName = trim($this->getGet('sort'));
        $sortOrder = trim($this->getGet('order'));
        if($sortName && $sortOrder) {
            $order = "{$sortName} {$sortOrder}";
        }else{
            $order = 'id desc';
        }

        //基本条件
        $isAdmin = true;
        $siteIds = [$this->siteId];
        if($isAdmin) array_push($siteIds, 0);
        $where = [
            'and',
            ['site_id' => $siteIds],
        ];

        //搜索条件
        $filters = json_decode($this->getGet('filter'), true);
        $ops = json_decode($this->getGet('op'), true);
        if(!empty($ops)) {
            foreach ($ops as $field=>$op) {
                $value = trim($filters[$field]);
                if($value!=='') {
                    if($field=='uid') {
                        array_push($where, ['uid'=>intval($value)]);
                    }elseif ($field=='create_time') {
                        $arr = explode(',', $value);
                        $start = intval($arr[0]);
                        $end = intval($arr[1]);

                        array_push($where, ['BETWEEN', 'create_time', $start, $end]);
                    }elseif ($field=='username') {
                        $usr = UserBase::getInfoByUsername($value);
                        array_push($where, ['uid'=> $usr ? $usr->uid : '-1']);
                    }
                }
            }
        }

        $paginator = AdmOperateLog::getPaginator('*', $where, $order, $pageSize, $pageNumber);
        $pageObj = $paginator->getPaginate();

        $list = $pageObj->items->toArray();
        if(!empty($list)) {
            $ipServ = new Ip2RegionService();

            $uids = array_column($list, 'create_by');
            $admList = UserBase::getList(['uid'=>$uids]);
            if($admList) $admList = $admList->toArray();

            $actionIds = array_column($list, 'action_id');
            $actionList = Action::getList(['ac_id'=>$actionIds]);
            if($actionList) $actionList = $actionList->toArray();

            foreach ($list as &$item) {
                $usr = ArrayHelper::arraySearchItem($admList, ['uid'=>$item['create_by']]);
                $item['username'] = $usr['username']??'';

                $action = ArrayHelper::arraySearchItem($actionList, ['ac_id'=>$item['action_id']]);
                $item['action_name'] = $action['title']??'';

                $item['create_ip'] = long2ip($item['create_ip']);
                $item['city'] = $ipServ->getCityName($item['create_ip']);
            }
        }

        $res = [
            'total' => $pageObj->total_items, //总记录数
            'currPage' => $pageNumber, //当前页码
            'pageSize' => $pageSize, //每页数量
            'pageTotal' => $pageObj->total_pages, //总页数
            'rows' => $list, //分页列表数据
        ];

        return $this->success($res);
    }


    /**
     * @title -保存个人信息
     * @desc  -保存管理员个人信息(邮箱/密码)
     */
    public function saveprofileAction() {
        $loginUid = $this->getLoginUid();
        $row = $this->getPost('row');

        if(!empty($row['avatar']) && !ValidateHelper::isUrl($row['avatar'])) {
            return $this->fail('头像地址不正确');
        }elseif(!empty($row['email']) && !ValidateHelper::isEmail($row['email'])) {
            return $this->fail('邮箱格式不正确');
        }elseif (!empty($row['mobile']) && !ValidateHelper::isMobile($row['mobile'])) {
            return $this->fail('手机格式不正确');
        }

        $now = time();
        $resBase = $resInfo = $resAdm = true;

        //更新头像
        if(!empty($row['avatar'])) {
            $chkInfo = UserInfo::getRow(['uid'=>$loginUid]);
            if($chkInfo) {
                $resInfo = UserInfo::upData([
                    'avatar' => $row['avatar'],
                    'update_time' => $now,
                ], ['uid'=>$loginUid]);
            }else{
                $data = [
                    'uid' => $loginUid,
                    'create_time' => $now,
                    'update_time' => $now,
                    'avatar' => $row['avatar'],
                ];
                $resInfo = UserInfo::addData($data);
            }
        }

        //更新邮箱、手机和前台密码
        if(!empty($row['email']) || !empty($row['mobile']) || !empty($row['frontPassword'])) {
            $data = ['update_time'=>$now];

            if(!empty($row['email'])) $data['email'] = $row['email'];
            if(!empty($row['mobile'])) $data['mobile'] = $row['mobile'];
            if(!empty($row['frontPassword'])) $data['password'] = UserService::makePasswdHash($row['frontPassword']);
            $resBase = UserBase::upData($data, ['uid'=>$loginUid]);
        }

        //更新后台密码
        if(!empty($row['backPassword'])) {
            $data = [
                'update_time' => $now,
                'password' => UserService::makePasswdHash($row['backPassword']),
            ];
            $resAdm = AdmUser::upData($data, ['uid'=>$loginUid]);
        }

        $res = $resInfo && $resBase && $resAdm;
        return $res ? $this->success() : $this->fail('操作失败,请稍后再试');
    }



}