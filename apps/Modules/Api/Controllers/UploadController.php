<?php
/**
 * Created by PhpStorm.
 * User: doubo
 * Date: 2018/3/15
 * Time: 18:24
 * Desc: 上传控制器
 */

namespace Apps\Modules\Api\Controllers;

use Kengine\LkkController;


class UploadController extends LkkController {


    /**
     * @title -默认动作
     * @desc  -动作说明
     */
    public function indexAction(){
        $fileInfo = $this->swooleRequest->files['file'] ?? [];

        return $this->success($fileInfo);
    }



    public function base64Action() {
        return $this->success();
    }


    public function imageAction() {
        return $this->success();
    }



    public function fileAction() {
        return $this->success();
    }




}