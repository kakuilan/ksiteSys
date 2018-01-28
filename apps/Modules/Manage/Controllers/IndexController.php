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
use Apps\Models\AdmUser;
use Apps\Services\CaptchaService;
use Lkk\Helpers\CommonHelper;

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
    public function indexAction(){
        //视图变量
        $this->view->setVars([
            'mainUrl' => makeUrl('manage/index/main'),
            'menuUrl' => makeUrl('manage/menu/authlist'),
        ]);

        //设置静态资源
        $this->assets->addCss('statics/css/adm-tab.css');
        $this->assets->addJs('statics/js/ace-elements.min.js');
        $this->assets->addJs('statics/js/ace.min.js');
        $this->assets->addJs('statics/js/lkkTabMenu.js');

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
            'saveUrl' => makeUrl('manage/index/loginsave'),
            'captchaUrl' => makeUrl('common/captcha/create'),
        ]);


        //设置静态资源
        $this->assets->addJs('statics/js/lkkFunc.js');
        $this->assets->addJs('statics/js/plugins/layer/layer.min.js');
        $this->assets->addJs('statics/js/plugins/validate/jquery.validate.min.js');
        $this->assets->addJs('statics/js/plugins/validate/localization/messages_zh.min.js');
        $this->assets->addJs('statics/js/md5.min.js');
        $this->assets->addJs('statics/js/fingerprint.min.js');

        return null;
    }


    /**
     * @title -后台登录保存
     * @desc  -后台登录保存
     */
    public function loginSaveAction() {
        $loginName = trim($this->request->get('loginName'));
        $password = trim($this->request->get('password'));
        $remember = intval($this->request->get('remember')); //24小时
        $password = trim($this->request->get('password'));
        $verifyCode = trim($this->request->get('verifyCode'));
        $veriEncode = trim($this->request->get('veriEncode'));

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
                'defaultUrl' => makeUrl($rbacCnf->managerDefautlAction),
                'admn' => $admn,
                'info' => [
                    'uid' => $admn->uid,
                    'username' => $admn->username,
                ],
            ];

            return $this->success($data);
        }
    }



    /**
     * @title -管理后台退出
     * @desc  -管理后台退出
     */
    public function logoutAction() {

    }


    /**
     * @title -后台主页
     * @desc  -后台主页
     */
    public function mainAction() {


    }


    /**
     * @title -忘记密码页
     * @desc  -忘记密码
     */
    public function forgetpwdAction() {

    }



}