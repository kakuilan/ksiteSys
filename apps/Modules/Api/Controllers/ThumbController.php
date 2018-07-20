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
     * @title -生成缩略图
     * @desc  -生成缩略图
     */
    public function makeAction() {
        getLogger('thumb')->info('make', ['swooleRequest'=>$this->swooleRequest]);
        return $this->success($this->swooleRequest);
    }





}