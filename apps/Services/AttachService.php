<?php
/**
 * Created by PhpStorm.
 * User: kakuilan@163.com
 * Date: 2018/7/11
 * Time: 9:57
 * Desc: 附近服务类
 */

namespace Apps\Services;

use Apps\Models\Attach;
use Apps\Models\UserBase;
use Lkk\Helpers\ArrayHelper;
use Lkk\Helpers\FileHelper;
use Lkk\Helpers\UrlHelper;
use Lkk\Helpers\StringHelper;
use Lkk\Helpers\ValidateHelper;

class AttachService extends ServiceBase {


    /**
     * 匹配文件类型值
     * @param string $ext 扩展名
     * @return int
     */
    public static function matchFileTypeValue($ext='') {
        $ext = stripos($ext, '.') ? FileHelper::getFileExt($ext) : $ext;
        $res = 0;
        switch ($ext) {
            case '' :default:
                break;
            case in_array($ext, ['gz','rar','zip','7z']) :
                $res = 1;
                break;
            case in_array($ext, ['txt','chm','pdf','doc','docx','xls','xlsx','ppt','pptx']) :
                $res = 2;
                break;
            case in_array($ext, ['gif','jpg','jpeg','bmp','png']) :
                $res = 3;
                break;
            case in_array($ext, ['mp3','wma','wav']) :
                $res = 4;
                break;
            case in_array($ext, ['mp4','avi','mov','flv']) :
                $res = 5;
                break;
        }

        return $res;
    }


    /**
     * 根据上传结果生成附件记录数据
     * @param array $uploadRes 上传结果
     * @param array|object $user 用户信息
     * @param array $other 其他附加信息,['is_auth','tag','compress_enable','belong_type','title','use_title','create_time','update_time','update_by']
     * @return array|bool
     */
    public static function makeAttachDataByUploadResult($uploadRes=[], $user=null, $other=[]) {
        if(empty($uploadRes) || empty($user)) return false;

        if(!is_object($user)) $user = ArrayHelper::arrayToObject($user);

        $now = time();
        $fileType = self::matchFileTypeValue($uploadRes['exte']);
        $isImg = ($fileType==3);
        $compressEnable = 0;
        if($isImg) {
            $compressEnable = intval($other['compress_enable'] ?? 0);
        }

        //标题
        $title = $other['title'] ?? ($uploadRes['new_name'] ?? '');
        if(isset($other['use_title']) && $other['use_title']) {
            $title = $uploadRes['name'];
            if(mb_strlen($title) > 15) $title = StringHelper::cutStr($title, 20, 0, '');
        }

        $avatarData = [
            'site_id' => $user->site_id,
            'is_del' => '0',
            'is_auth' => intval($other['is_auth'] ?? 0),
            'is_persistent' => '0',
            'has_third' => '0',
            'compress_enable' => $compressEnable,
            'belong_type' => intval($other['belong_type'] ?? 2),
            'file_type' => intval($fileType),
            'uid' => $user->uid,
            'img_width' => ($uploadRes['width'] ?? 0),
            'img_height' => ($uploadRes['height'] ?? 0),
            'quote_num' => '0',
            'downl_num' => '0',
            'file_size' => ($uploadRes['size'] ?? 0),
            'tag' => ($other['tag'] ?? ''),
            'title' => $title,
            'file_ext' => ($uploadRes['exte'] ?? ''),
            'file_name' => ($uploadRes['new_name'] ?? ''),
            'file_path' => ($uploadRes['relative_path'] ?? ''),
            'qiniu_url' => '',
            'third_url' => '',
            'create_time' => ($other['create_time'] ?? $now),
            'update_time' => ($other['update_time'] ?? $now),
            'update_by' => ($other['update_by'] ?? 0),
        ];

        unset($uploadRes, $user, $other);
        return $avatarData;
    }


    /**
     * 格式化附件URL
     * @param string $path 附件路径
     * @param null $siteId 站点ID
     * @return mixed|string
     */
    public static function formatAttachUrl($path='', $siteId=null) {
        if(empty($path)) return '';

        $siteConf = yield ConfigService::getSiteConfigs($siteId);
        $uploadSiteUrl = empty($siteConf['upload_site_url']) ? getSiteUrl($siteId) : $siteConf['upload_site_url'];
        $url = "{$uploadSiteUrl}{$path}";
        unset($siteConf);

        return UrlHelper::formatUrl($url);
    }





}