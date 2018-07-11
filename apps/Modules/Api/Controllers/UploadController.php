<?php
/**
 * Created by PhpStorm.
 * User: kakuilan@163.com
 * Date: 2018/3/15
 * Time: 18:24
 * Desc: 上传控制器
 */

namespace Apps\Modules\Api\Controllers;

use Apps\Models\AdmUser;
use Apps\Models\Attach;
use Apps\Models\UserBase;
use Apps\Models\UserInfo;
use Apps\Modules\Api\Controller;
use Apps\Services\AttachService;
use Apps\Services\ConfigService;
use Apps\Services\UploadService;
use Apps\Services\UserService;
use Lkk\Helpers\ValidateHelper;


class UploadController extends Controller {

    public $uploadSiteUrl;
    public $uploadFileSize;
    public $uploadFileExt;
    public $uploadImageSize;
    public $uploadImageExt;


    public function initialize () {
        parent::initialize();

        //解析token获取UID
        $agUuid = $this->di->getShared('userAgent')->getAgentUuidSimp();
        $token = $this->getAccessToken();
        $this->uid = UserService::parseAccessToken($token, $agUuid);

        if($this->uid >0) {
            //获取上传基本配置
            $uploadConf = yield ConfigService::getUploadConfigs();
            $this->uploadSiteUrl = $uploadConf['upload_site_url'] ?? '';
            $this->uploadFileSize = $uploadConf['upload_file_size'] ?? UploadService::$defaultMaxSize;
            $this->uploadFileExt = $uploadConf['upload_file_ext'] ?? UploadService::$defaultAllowType;
            $this->uploadImageSize = $uploadConf['upload_image_size'] ?? UploadService::$defaultMaxSize;
            $this->uploadImageExt = $uploadConf['upload_image_ext'] ?? UploadService::$defaultAllowType;

            if(empty($this->uploadSiteUrl)) $this->uploadSiteUrl = getSiteUrl();
        }else{
            return $this->fail(401);
        }

        unset($agUuid, $token, $uploadConf);
    }


    /**
     * @title -默认动作
     * @desc  -动作说明
     */
    public function indexAction(){
        $fileInfo = $this->swooleRequest->files['file'] ?? [];
        $data = [
            'uploadSiteUrl' => $this->uploadSiteUrl,
            'uploadFileSize' => $this->uploadFileSize,
            'uploadFileExt' => $this->uploadFileExt,
            'uploadImageSize' => $this->uploadImageSize,
            'uploadImageExt' => $this->uploadImageExt,
            'fileInfo' => $fileInfo,
        ];

        return $this->success($data);
    }


    /**
     * @title -上传用户头像
     * @desc  -上传用户头像
     * @api {post} /api/upload/avatar 上传用户头像,支持base64
     * @apiParam {string} [type=file] 上传类型,['file','base64'],默认是文件file
     * @apiParam {string} [input_name=file] 上传的文件域名称,默认file;或base64字符串的参数名,如input_name=file&file=xxx
     * @apiParam {int} uid 头像用户UID
     *
     */
    public function avatarAction() {
        //未验证身份,无权操作
        if(empty($this->uid) || $this->uid<=0) {
            return $this->fail(401);
        }

        $typeArr = ['file','base64'];
        $type = $this->getPost('type', 'file', false);
        if(!in_array($type, $typeArr)) {
            return $this->fail(20104, 'type类型错误');
        }

        $uid = intval($this->getRequest('uid'));
        if($uid<=0) $uid = $this->uid;
        $avatarUsr = yield UserBase::getInfoInAdmByUidAsync($uid);
        if(empty($avatarUsr)) {
            return $this->fail(401);
        }

        //不是会员本人,且不是管理员,无权修改头像
        $isAdmin = UserService::isAdmin($avatarUsr);
        if($this->uid!=$uid && !$isAdmin) {
            return $this->fail(401);
        }

        //自己传头像 or 管理员修改他人头像
        $newName = '';
        $savePath = UploadService::$savePathTemp; //先存放临时目录,审核后转到永久目录
        $allowTypes = ['gif','jpg','jpeg','bmp','png'];
        $inputName = $this->getPost('input_name', 'file', false);
        if($type==='file') {
            $serv = new UploadService();
            $serv->setOriginFiles($this->swooleRequest->files ?? [])
                ->setSavePath($savePath)
                ->setWebDir(WWWDIR)
                ->setWebUrl($this->uploadSiteUrl)
                ->setAllowSubDir(false)
                ->setOverwrite(false)
                ->setAllowType($allowTypes);

            $ret = $serv->uploadSingle($inputName, $newName);
            if(!$ret) {
                return $this->fail($serv->getError());
            }

            $data = $serv->getSingleResult();
        }else{
            $serv = new UploadService();
            $serv->setSavePath($savePath)
                ->setWebDir(WWWDIR)
                ->setWebUrl($this->uploadSiteUrl)
                ->setAllowSubDir(false)
                ->setOverwrite(false)
                ->setAllowType($allowTypes);

            $content = $this->getPost($inputName, '', false);
            $ret = $serv->uploadBase64Img($content, $newName);
            if(!$ret) {
                return $this->fail($serv->getError());
            }

            $data = $serv->getSingleResult();
        }

        //新增附件记录
        if($data['status']) {
            $now = time();
            $row = false;
            if($data['is_exists']) { //文件已存在
                //检查该文件是否有记录
                $where = [
                    'file_name' => $data['new_name'],
                ];
                $row = yield Attach::getRowAsync($where);
            }

            if($row) {
                yield Attach::upDataAsync(['is_del'=>0,'update_time'=>$now,'update_by'=>$uid], ['id'=>$row['id']]);
            }else{
                $other = [
                    'tag' => 'avatar',
                    'update_by' => $uid,
                ];
                $avatarData = AttachService::makeAttachDataByUploadResult($data, $avatarUsr, $other);

                yield Attach::addDataAsync($avatarData);
            }
        }
        unset($typeArr, $allowTypes, $avatarUsr, $serv, $content, $where, $row, $other, $avatarData);

        //屏蔽绝对路径,防止泄露服务器信息
        unset($data['absolute_path'], $data['tmp_name']);
        return $this->success($data);
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
        }
        unset($data['absolute_path'], $data['tmp_name']);

        return $this->success($data);
    }


    /**
     * @title -文件上传
     * @desc  -文件上传
     * @return array|string
     */
    public function fileAction() {
        $agUuid = $this->di->getShared('userAgent')->getAgentUuidSimp();
        $token = $this->getAccessToken();
        $loginUid = UserService::parseAccessToken($token, $agUuid);
        if(empty($loginUid) || $loginUid<=0) {
            return $this->fail(401);
        }

        $name = $this->getRequest('name', 'file', false);

        $newName = "";
        $savePath = UPLODIR . 'attach/';
        $allowTypes = ['rar','zip','gz','bz2','7z','txt','doc','docx','xls','xlsx','ppt','pptx','pdf','wps','gif','jpg','jpeg','bmp','png'];

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

        return $this->success($data);
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