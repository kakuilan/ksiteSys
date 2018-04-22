<?php
/**
 * Created by PhpStorm.
 * User: doubo
 * Date: 2018/3/15
 * Time: 18:24
 * Desc: 上传控制器
 */

namespace Apps\Modules\Api\Controllers;

use Apps\Modules\Api\Controller;
use Apps\Services\UploadService;
use Apps\Services\UserService;
use Lkk\Helpers\ValidateHelper;
use Lkk\LkkUpload;

class UploadController extends Controller {


    /**
     * @title -默认动作
     * @desc  -动作说明
     */
    public function indexAction(){
        $fileInfo = $this->swooleRequest->files['file'] ?? [];

        return $this->success($fileInfo);
    }


    /**
     * @title -上传图片
     * @desc  -上传图片
     * @return array|string
     */
    public function imageAction() {
        $agUuid = $this->di->getShared('userAgent')->getAgentUuidSimp();
        $token = $this->getAccessToken();
        $loginUid = UserService::parseAccessToken($token, $agUuid);
        if(empty($loginUid) || $loginUid<=0) {
            return $this->fail(401);
        }

        $typeArr = ['file','base64'];
        $name = $this->getRequest('name', 'file', false);
        $type = $this->getRequest('type', 'file', false);
        if(!in_array($type, $typeArr)) {
            return $this->fail(20104, 'type类型错误');
        }

        if(!is_numeric($loginUid) || $loginUid<=0) {
            return $this->fail(401);
        }

        $newName = "";
        $savePath = UPLODIR . 'picture/';
        $allowTypes = ['gif','jpg','jpeg','bmp','png'];
        if($type=='file') {
            $serv = new UploadService();
            $serv->setOriginFiles($this->swooleRequest->files ?? [])
                ->setSavePath($savePath)
                ->setWebDir(WWWDIR)
                ->setWebUrl(getSiteUrl())
                ->setAllowSubDir(false)
                ->setOverwrite(true)
                ->setAllowType($allowTypes);

            $ret = $serv->uploadSingle('file', $newName);
            if(!$ret) {
                return $this->fail($serv->getError());
            }

            $data = $serv->getSingleResult();
            unset($data['absolute_path'], $data['tmp_name']);
        }else{
            $serv = new UploadService();
            $serv->setSavePath($savePath)
                ->setWebDir(WWWDIR)
                ->setWebUrl(getSiteUrl())
                ->setAllowSubDir(false)
                ->setOverwrite(true)
                ->setAllowType($allowTypes);

            $content = $this->getRequest($name, '', false);
            $ret = $serv->uploadBase64Img($content, $newName);
            if(!$ret) {
                return $this->fail($serv->getError());
            }

            $data = $serv->getSingleResult();
            unset($data['absolute_path'], $data['tmp_name']);
        }

        return $this->success($data);
    }


    /**
     * @title -上传用户头像
     * @desc  -上传用户头像
     */
    public function avatarAction() {
        $agUuid = $this->di->getShared('userAgent')->getAgentUuidSimp();
        $token = $this->getAccessToken();
        $loginUid = UserService::parseAccessToken($token, $agUuid);
        if(empty($loginUid) || $loginUid<=0) {
            return $this->fail(401);
        }

        $typeArr = ['file','base64'];
        $name = $this->getRequest('name', 'file', false);
        $type = $this->getRequest('type', 'file', false);
        $uid = intval($this->getRequest('uid'));
        if(!in_array($type, $typeArr)) {
            return $this->fail(20104, 'type类型错误');
        }

        $isAdmin = false;
        if($uid<=0) $uid = $loginUid;

        if($loginUid!=$uid && !$isAdmin) {
            return $this->fail(401);
        }

        //自己传头像 or 管理员修改他人头像
        $newName = "{$uid}.jpg";
        $savePath = UPLODIR . 'avatar/' . UserService::makeAvatarPath($uid);
        $allowTypes = ['gif','jpg','jpeg','bmp','png'];
        if($type=='file') {
            $serv = new UploadService();
            $serv->setOriginFiles($this->swooleRequest->files ?? [])
                ->setSavePath($savePath)
                ->setWebDir(WWWDIR)
                ->setWebUrl(getSiteUrl())
                ->setAllowSubDir(false)
                ->setOverwrite(true)
                ->setAllowType($allowTypes);

            $ret = $serv->uploadSingle('file', $newName);
            if(!$ret) {
                return $this->fail($serv->getError());
            }

            $data = $serv->getSingleResult();
            unset($data['absolute_path'], $data['tmp_name']);
        }else{
            $serv = new UploadService();
            $serv->setSavePath($savePath)
                ->setWebDir(WWWDIR)
                ->setWebUrl(getSiteUrl())
                ->setAllowSubDir(false)
                ->setOverwrite(true)
                ->setAllowType($allowTypes);

            $content = $this->getRequest($name, '', false);
            $ret = $serv->uploadBase64Img($content, $newName);
            if(!$ret) {
                return $this->fail($serv->getError());
            }

            $data = $serv->getSingleResult();
            unset($data['absolute_path'], $data['tmp_name']);
        }

        return $this->success($data);
    }


    public function fileAction() {
        return $this->success();
    }


    /**
     * 单文件上传
     * @return array|string
     */
    public function singleAction() {
        $serv = new UploadService();
        $serv->setOriginFiles($this->swooleRequest->files)
            ->setSavePath(UPLODIR)->setWebDir(WWWDIR)->setWebUrl(getSiteUrl());

        $ret = $serv->uploadSingle('file');
        if(!$ret) {
            return $this->fail($serv->getError());
        }

        $arr = $serv->getSingleResult();
        unset($arr['absolute_path'], $arr['tmp_name']);

        return $this->success($arr);
    }


    /**
     * 多文件上传
     * @return array|string
     */
    public function multiAction() {
        $serv = new UploadService();
        $serv->setOriginFiles($this->swooleRequest->files)
            ->setSavePath(UPLODIR)->setWebDir(WWWDIR)->setWebUrl(getSiteUrl());

        $ret = $serv->uploadMulti(['img','doc','ppt','file','logo','desc']);
        if(!$ret) {
            return $this->fail($serv->getError());
        }

        $arr = $serv->getMultiResult();
        return $this->success($arr);
    }




}