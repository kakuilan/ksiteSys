<?php
/**
 * Created by PhpStorm.
 * User: kakuilan@163.com
 * Date: 2018/7/13
 * Time: 9:57
 * Desc: 缩略图服务类
 */


namespace Apps\Services;

use Apps\Models\Attach;
use Apps\Models\UserBase;
use Lkk\Helpers\ArrayHelper;
use Lkk\Helpers\ValidateHelper;

class ThumbService extends ServiceBase {


    public static $savePathLongThumb = UPLODIR . 'thumb/'; //永久保存目录,缩略图


}