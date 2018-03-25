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
use Lkk\LkkUpload;

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
        $name = 'file';
        $fileInfo = $this->swooleRequest->files[$name] ?? [];
        if(empty($fileInfo)) {
            return $this->fail('没有上传的文件');
        }

        $data = [];
        $ext = LkkUpload::getExtention($fileInfo['name']);
        $newFile = LkkUpload::createRandName($fileInfo['name']) . ".{$ext}";
        $newPath = UPLODIR . $newFile;

        $res = move_uploaded_file($fileInfo['tmp_name'], $newPath);
        if($res) {
            $data = [
                'url' => makeUrl('/upload/'. $newFile),
            ];
        }

        return $res ? $this->success($data) : $this->fail('上传失败');
    }



    public function fileAction() {
        return $this->success();
    }




}