<?php
/**
 * Created by PhpStorm.
 * User: kakuilan@163.com
 * Date: 2018/7/20
 * Time: 14:46
 * Desc: 缩略图控制器
 */


namespace Apps\Modules\Api\Controllers;

use Apps\Models\AdmUser;
use Apps\Models\Attach;
use Apps\Models\UserBase;
use Apps\Models\UserInfo;
use Apps\Modules\Api\Controller;
use Apps\Services\AttachService;
use Apps\Services\ConfigService;
use Apps\Services\ThumbService;
use Apps\Services\UploadService;
use Apps\Services\UserService;
use Kengine\LkkRoutes;
use Lkk\Helpers\ArrayHelper;
use Lkk\Helpers\CommonHelper;
use Lkk\Helpers\FileHelper;
use Lkk\Helpers\StringHelper;
use Lkk\Helpers\ValidateHelper;


class ThumbController extends Controller {

    public function initialize () {
        parent::initialize();

    }


    /**
     * @title -默认动作
     * @desc  -动作说明
     */
    public function indexAction(){
        return $this->success();
    }


    /**
     * 显示图片
     * @param string $path 图片路径
     *
     * @return array|mixed|object|string
     */
    protected function showImage($path='') {
        if(empty($path) || !file_exists($path)) $path = ThumbService::getDefaultImagePath();

        $mime = FileHelper::getFileMime($path);
        $mime .= ";text/html; charset=utf-8";
        $this->response->setHeader('Content-Type', $mime);

        $this->setHasView(false);
        return $this->output(file_get_contents($path));
    }



    /**
     * 显示默认图片
     * @return array|mixed|object|string
     */
    protected function showDefaultImage() {
        return $this->showImage(ThumbService::getDefaultImagePath());
    }



    /**
     * @title -生成缩略图
     * @desc  -生成缩略图
     */
    public function makeAction() {
        getLogger('thumb')->info('make', ['swooleRequest'=>$this->swooleRequest]);

        $origin = $this->getGet('origin'); //原图名称
        $target = $this->getGet('target'); //新图名称
        $vecode = $this->getGet('vecode'); //验证码
        if(empty($origin) || empty($target) || empty($vecode)) {
            return $this->showDefaultImage();
        }

        //原图是否存在,否则输出默认图片
        //再加图片生成任务
        $row = yield Attach::getRowAsync(['is_del'=>0, 'file_name'=>$origin], Attach::$baseFields);
        $path = $row ? UploadService::getAttachAbsolutePath($row['file_path']) : '';
        if(empty($row) || empty($path) || !file_exists($path)) {
            return $this->showDefaultImage();
        }

        //TODO 缩略图生成队列

        return $this->showImage($path);
    }





}