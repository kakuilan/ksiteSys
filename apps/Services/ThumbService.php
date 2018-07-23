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
use Lkk\Helpers\FileHelper;
use Lkk\Helpers\ValidateHelper;
use Intervention\Image\ImageManager;
use Intervention\Image\ImageManagerStatic as Image;
use LasseRafn\InitialAvatarGenerator\InitialAvatar;


class ThumbService extends ServiceBase {


    //永久保存目录,缩略图
    public static $savePathLongThumb = UPLODIR . 'thumb/';

    //默认图片
    public static $defaultImage = WWWDIR . 'statics/img/gray.png';


    /**
     * 获取默认图片路径
     * @return string
     */
    public static function getDefaultImagePath() {
        return self::$defaultImage;
    }


    /**
     * 获取默认图片内容
     * @param bool $base64 是否用base64
     *
     * @return mixed
     */
    public static function getDefaultImageContent($base64=false) {
        $res = $base64 ? FileHelper::img2Base64(self::$defaultImage) : file_get_contents(self::$defaultImage);

        return $res;
    }






    public static function thumb() {
        //TODO
    }


    public static function watermark() {
        //TODO
    }





}