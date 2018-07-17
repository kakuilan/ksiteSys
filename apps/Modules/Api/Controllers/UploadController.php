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
use Kengine\LkkRoutes;
use Lkk\Helpers\ArrayHelper;
use Lkk\Helpers\CommonHelper;
use Lkk\Helpers\StringHelper;
use Lkk\Helpers\ValidateHelper;


class UploadController extends Controller {

    public $uploadSiteUrl;
    public $uploadFileSize;
    public $uploadFileExt;
    public $uploadImageSize;
    public $uploadImageExt;
    public $userFreeFilesizeLimitOpen;


    public function initialize () {
        parent::initialize();

        //解析token获取UID
        $agUuid = $this->di->getShared('userAgent')->getAgentUuidSimp();
        $token = $this->getAccessToken();
        $this->uid = UserService::parseAccessToken($token, $agUuid);
        getLogger('token')->info('authToken', ['userAgent'=>$agUuid, 'token'=>$token]);

        if($this->uid > 0) {
            //获取上传基本配置
            $uploadConf = yield ConfigService::getUploadConfigs();
            $this->uploadSiteUrl = $uploadConf['upload_site_url'] ?? '';
            $this->uploadFileSize = $uploadConf['upload_file_size'] ?? UploadService::$defaultMaxSize;
            $this->uploadFileExt = $uploadConf['upload_file_ext'] ?? UploadService::$defaultAllowType;
            $this->uploadImageSize = $uploadConf['upload_image_size'] ?? UploadService::$defaultMaxSize;
            $this->uploadImageExt = $uploadConf['upload_image_ext'] ?? UploadService::$defaultAllowType;
            $this->userFreeFilesizeLimitOpen = $uploadConf['user_free_filesize_limit_open'] ?? 0;

            if(empty($this->uploadSiteUrl)) $this->uploadSiteUrl = getSiteUrl();
        }else{ //未验证身份,无权操作
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
        $inputName = $this->getPost('input_name', 'file', false);
        $tag = 'avatar';

        $serv = new UploadService();
        $serv->setSavePath($savePath)
            ->setWebDir(WWWDIR)
            ->setWebUrl($this->uploadSiteUrl)
            ->setAllowSubDir(true)
            ->setOverwrite(false)
            ->setRename(false)
            ->setRandNameSeed($tag)
            ->setMaxSize($this->uploadImageSize)
            ->setAllowType($this->uploadImageExt);

        if($type==='file') {
            $ret = $serv->setOriginFiles($this->swooleRequest->files ?? [])->uploadSingle($inputName, $newName);
        }else{
            $content = $this->getPost($inputName, '', false);
            $ret = $serv->uploadBase64Img($content, $newName);
        }

        $data = $serv->getSingleResult();
        if(!$ret) {
            return $this->fail($serv->getError());
        }elseif (!$data['status']) {
            return $this->fail($data['info']);
        }

        //新增附件记录
        $now = time();
        $row = false;

        //检查该文件是否有记录
        $where = ['file_name' => $data['new_name'] ];
        $row = yield Attach::getRowAsync($where, 'id,file_name');

        if($row) {
            yield Attach::upDataAsync(['is_del'=>0,'update_time'=>$now,'update_by'=>$uid], ['id'=>$row['id']]);
        }else{
            $other = [
                'belong_type' => 2,
                'tag' => $tag,
                'update_by' => $uid,
            ];
            $attData = AttachService::makeAttachDataByUploadResult($data, $avatarUsr, $other);

            yield Attach::addDataAsync($attData);
        }
        unset($typeArr, $avatarUsr, $serv, $content, $row, $other, $attData);

        //屏蔽绝对路径,防止泄露服务器信息
        unset($data['absolute_path'], $data['tmp_name']);

        return $this->success($data);
    }


    /**
     * @title -上传图片
     * @desc  -上传图片
     * @api {post} /api/upload/image 上传图片,支持base64
     * @apiParam {string} [type=file] 上传类型,['file','base64'],默认是文件file
     * @apiParam {string} [input_name=file] 上传的文件域名称,默认file;或base64字符串的参数名,如input_name=file&file=xxx
     * @apiParam {string} [tag=user] 附件标识,默认user
     *
     */
    public function imageAction() {
        $typeArr = ['file','base64'];
        $type = $this->getPost('type', 'file', false);
        if(!in_array($type, $typeArr)) {
            return $this->fail(20104, 'type类型错误');
        }

        $tag = $this->getPost('tag', '', false);
        $tagArr = array_keys(Attach::getTagArr());
        if(!empty($tag) && !in_array($tag, $tagArr)) {
            return $this->fail(20104, 'tag标识错误');
        }

        $userInfo = yield UserInfo::getJoinInfoByUidAsync($this->uid, UserInfo::$baseFields);
        if(empty($userInfo)) {
            return $this->fail(401);
        }

        $inputName = $this->getPost('input_name', 'file', false);
        $content = $this->getPost($inputName, '', false);

        $isRoot = UserService::isRoot($userInfo);
        $isAdmin = UserService::isAdmin($userInfo);
        $referer = $this->request->getHTTPReferer();
        $fromRou = LkkRoutes::getRouteInfoByUrl($referer);

        //无需审核,是管理员且来源后台,或者是超管
        $noneedAuth = (($isAdmin && $fromRou['module']=='manage') || $isRoot);
        if(empty($tag)) $tag = $noneedAuth ? 'backend' : 'user';
        if($noneedAuth) {

            //不检查用户剩余空间
        }elseif($this->userFreeFilesizeLimitOpen){
            //检查用户是否有剩余空间上传
            $fileSize = $type==='file' ? UploadService::getUploadFilesSize($this->swooleRequest->files, $inputName) : StringHelper::countBase64Byte($content);
            $fileSize = ceil($fileSize/1024);
            if($fileSize && $fileSize > $userInfo['free_file_size']) {
                return $this->fail("剩余空间KB:{$userInfo['free_file_size']},不足上传:{$fileSize}");
            }
        }

        $newName = '';
        $savePath = $noneedAuth ? UploadService::$savePathLongPictur : UploadService::$savePathTemp;

        $serv = new UploadService();
        $serv->setSavePath($savePath)
            ->setWebDir(WWWDIR)
            ->setWebUrl($this->uploadSiteUrl)
            ->setAllowSubDir(true)
            ->setOverwrite(false)
            ->setRename(false)
            ->setRandNameSeed($tag)
            ->setMaxSize($this->uploadImageSize)
            ->setAllowType($this->uploadImageExt);

        if($type==='file') {
            $ret = $serv->setOriginFiles($this->swooleRequest->files ?? [])->uploadSingle($inputName, $newName);
        }else{
            $ret = $serv->uploadBase64Img($content, $newName);
        }

        $data = $serv->getSingleResult();
        if(!$ret) {
            return $this->fail($serv->getError());
        }elseif (!$data['status']) {
            return $this->fail($data['info']);
        }

        //新增附件记录
        $now = time();
        $row = false;

        //检查该文件是否有记录
        $where = ['file_name' => $data['new_name'] ];
        $row = yield Attach::getRowAsync($where, 'id,file_name');

        if($row) {
            $ret = yield Attach::upDataAsync(['is_del'=>0,'update_time'=>$now,'update_by'=>$this->uid], ['id'=>$row['id']]);
        }else{
            $other = [
                'belong_type' => ($tag=='system' ? 0 : (in_array($tag, ['backend','ad']) ? 1: 2) ),
                'tag' => $tag,
                'update_by' => $this->uid,
            ];
            $attData = AttachService::makeAttachDataByUploadResult($data, $userInfo, $other);

            $ret = yield Attach::addDataAsync($attData);
        }

        //减去可用空间
        if($this->userFreeFilesizeLimitOpen && isset($fileSize) && $fileSize && $ret) {
            $userData = [
                'free_file_size' => abs(intval($userInfo['free_file_size'] - $fileSize)),
                'update_time' => $now,
                'last_activ_time' => $now,
            ];
            yield UserInfo::upDataAsync($userData, ['uid'=> $this->uid]);
        }

        unset($typeArr, $allowTypes, $avatarUsr, $serv, $content, $where, $row, $other, $attData, $userData);

        //屏蔽绝对路径,防止泄露服务器信息
        unset($data['absolute_path'], $data['tmp_name']);

        return $this->success($data);
    }



    /**
     * @title -单文件上传
     * @desc  -单文件上传
     * @api {post} /api/upload/single 单文件上传
     * @apiParam {string} [input_name=file] 上传的文件域名称,默认file
     * @apiParam {string} [tag=user] 附件标识,默认user
     * @apiParam {int} [use_title=0] 是否使用文件名作为title
     *
     */
    public function singleAction() {
        $tag = $this->getPost('tag', '', false);
        $tagArr = array_keys(Attach::getTagArr());
        if(!empty($tag) && !in_array($tag, $tagArr)) {
            return $this->fail(20104, 'tag标识错误');
        }

        $userInfo = yield UserInfo::getJoinInfoByUidAsync($this->uid, UserInfo::$baseFields);
        if(empty($userInfo)) {
            return $this->fail(401);
        }

        $inputName = $this->getPost('input_name', 'file', false);

        $isRoot = UserService::isRoot($userInfo);
        $isAdmin = UserService::isAdmin($userInfo);
        $referer = $this->request->getHTTPReferer();
        $fromRou = LkkRoutes::getRouteInfoByUrl($referer);

        //无需审核,是管理员且来源后台,或者是超管
        $noneedAuth = (($isAdmin && $fromRou['module']=='manage') || $isRoot);
        if(empty($tag)) $tag = $noneedAuth ? 'backend' : 'user';
        if($noneedAuth) {

            //不检查用户剩余空间
        }elseif($this->userFreeFilesizeLimitOpen){
            //检查用户是否有剩余空间上传
            $fileSize = UploadService::getUploadFilesSize($this->swooleRequest->files, $inputName);
            $fileSize = ceil($fileSize/1024);
            if($fileSize && $fileSize > $userInfo['free_file_size']) {
                return $this->fail("剩余空间KB:{$userInfo['free_file_size']},不足上传:{$fileSize}");
            }
        }

        $newName = '';
        $savePath = $noneedAuth ? UploadService::$savePathLongAttach : UploadService::$savePathTemp;

        $serv = new UploadService();
        $serv->setSavePath($savePath)
            ->setWebDir(WWWDIR)
            ->setWebUrl($this->uploadSiteUrl)
            ->setAllowSubDir(true)
            ->setOverwrite(false)
            ->setRename(false)
            ->setRandNameSeed($tag)
            ->setMaxSize($this->uploadFileSize)
            ->setAllowType($this->uploadFileExt);

        $ret = $serv->setOriginFiles($this->swooleRequest->files ?? [])->uploadSingle($inputName, $newName);
        $data = $serv->getSingleResult();
        if(!$ret) {
            return $this->fail($serv->getError());
        }elseif (!$data['status']) {
            return $this->fail($data['info']);
        }

        //新增附件记录
        $now = time();
        $row = false;

        //检查该文件是否有记录
        $where = ['file_name' => $data['new_name'] ];
        $row = yield Attach::getRowAsync($where, 'id,file_name');

        if($row) {
            $ret = yield Attach::upDataAsync(['is_del'=>0,'update_time'=>$now,'update_by'=>$this->uid], ['id'=>$row['id']]);
        }else{
            $useTitle = intval($this->getRequest('use_title', 0, false));
            $other = [
                'belong_type' => ($tag=='system' ? 0 : (in_array($tag, ['backend','ad']) ? 1: 2) ),
                'tag' => $tag,
                'use_title' => $useTitle,
                'update_by' => $this->uid,
            ];
            $attData = AttachService::makeAttachDataByUploadResult($data, $userInfo, $other);

            $ret = yield Attach::addDataAsync($attData);
        }

        //减去可用空间
        if($this->userFreeFilesizeLimitOpen && isset($fileSize) && $fileSize && $ret) {
            $userData = [
                'free_file_size' => abs(intval($userInfo['free_file_size'] - $fileSize)),
                'update_time' => $now,
                'last_activ_time' => $now,
            ];
            yield UserInfo::upDataAsync($userData, ['uid'=> $this->uid]);
        }

        unset($tagArr, $userInfo, $fromRou, $serv, $ret, $where, $other, $attData, $userData);

        //屏蔽绝对路径,防止泄露服务器信息
        unset($data['absolute_path'], $data['tmp_name']);

        return $this->success($data);
    }



    /**
     * @title -多文件上传
     * @desc  -多文件上传
     * @api {post} /api/upload/multi 多文件上传
     * @apiParam {string} [input_name=file] 上传的文件域名称,数组,如input_name[]=file&input_name[]=doc
     * @apiParam {string} [tag=user] 附件标识,默认user
     * @apiParam {int} [use_title=0] 是否使用文件名作为title
     *
     */
    public function multiAction() {
        $tag = $this->getPost('tag', '', false);
        $tagArr = array_keys(Attach::getTagArr());
        if(!empty($tag) && !in_array($tag, $tagArr)) {
            return $this->fail(20104, 'tag标识错误');
        }

        $userInfo = yield UserInfo::getJoinInfoByUidAsync($this->uid, UserInfo::$baseFields);
        if(empty($userInfo)) {
            return $this->fail(401);
        }

        $defaultInputs = ['file', 'img', 'image', 'attach', 'doc'];
        $inputNameArr = (array)$this->getPost('input_name', null, false);
        if(empty($inputNameArr)) $inputNameArr = $defaultInputs;

        $isRoot = UserService::isRoot($userInfo);
        $isAdmin = UserService::isAdmin($userInfo);
        $referer = $this->request->getHTTPReferer();
        $fromRou = LkkRoutes::getRouteInfoByUrl($referer);

        //无需审核,是管理员且来源后台,或者是超管
        $noneedAuth = (($isAdmin && $fromRou['module']=='manage') || $isRoot);
        if(empty($tag)) $tag = $noneedAuth ? 'backend' : 'user';
        if($noneedAuth) {

            //不检查用户剩余空间
        }elseif($this->userFreeFilesizeLimitOpen){
            //检查用户是否有剩余空间上传
            $fileSize = UploadService::getUploadFilesSize($this->swooleRequest->files, $inputNameArr);
            $fileSize = ceil($fileSize/1024);
            if($fileSize && $fileSize > $userInfo['free_file_size']) {
                return $this->fail("剩余空间KB:{$userInfo['free_file_size']},不足上传:{$fileSize}");
            }
        }

        $newNames = [];
        $savePath = $noneedAuth ? UploadService::$savePathLongAttach : UploadService::$savePathTemp;

        $serv = new UploadService();
        $serv->setSavePath($savePath)
            ->setWebDir(WWWDIR)
            ->setWebUrl($this->uploadSiteUrl)
            ->setAllowSubDir(true)
            ->setOverwrite(false)
            ->setRename(false)
            ->setRandNameSeed($tag)
            ->setMaxSize($this->uploadFileSize)
            ->setAllowType($this->uploadFileExt);

        $ret = $serv->setOriginFiles($this->swooleRequest->files ?? [])->uploadMulti($inputNameArr, $newNames);
        $data = $serv->getMultiResult();

        $succNum = 0;
        if($ret) {
            foreach ($data as &$item) {
                if($item['status']) $succNum++;

                //屏蔽绝对路径,防止泄露服务器信息
                unset($item['absolute_path'], $item['tmp_name']);
            }
            unset($item);
        }

        if(!$ret || $succNum==0) {
            return $this->fail($serv->getError());
        }

        //新增附件记录
        if($succNum) {
            $now = time();
            $fileSize = 0;
            $attDatas = [];
            $fileKeys = [];

            $rows = yield Attach::getListAsync(['file_name' => array_column($data, 'new_name')], Attach::$baseFields);
            $useTitle = intval($this->getRequest('use_title', 0, false));
            $other = [
                'belong_type' => ($tag=='system' ? 0 : (in_array($tag, ['backend','ad']) ? 1: 2) ),
                'tag' => $tag,
                'use_title' => $useTitle,
                'update_by' => $this->uid,
            ];

            foreach ($data as $item) {
                if(!$item['status']) continue;

                $chkExis = ArrayHelper::arraySearchItem($rows, ['file_name'=>$item['new_name']]);
                if($chkExis) continue;

                $attData = AttachService::makeAttachDataByUploadResult($item, $userInfo, $other);
                array_push($attDatas, $attData);

                $fileSize += ($item['size'] ?? 0);
            }

            //更新旧记录
            if($rows) yield Attach::upDataAsync(['is_del'=>0,'update_time'=>$now,'update_by'=>$this->uid], ['id'=>array_column($rows, 'id') ]);

            //插入新记录
            $ret = yield Attach::addMultiDataAsync($attDatas);

            //减去可用空间
            if($this->userFreeFilesizeLimitOpen && isset($fileSize) && $fileSize && $ret) {
                $userData = [
                    'free_file_size' => abs(intval($userInfo['free_file_size'] - $fileSize)),
                    'update_time' => $now,
                    'last_activ_time' => $now,
                ];
                yield UserInfo::upDataAsync($userData, ['uid'=> $this->uid]);
            }

        }

        unset($tagArr, $userInfo, $defaultInputs, $inputNameArr, $fromRou, $serv, $rows, $other, $attData, $attDatas, $userData);

        return $this->success($data);
    }




}