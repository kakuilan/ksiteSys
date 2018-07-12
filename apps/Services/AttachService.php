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
use Lkk\Helpers\ValidateHelper;

class AttachService extends ServiceBase {


    /**
     * 根据上传结果生成附件记录数据
     * @param array $uploadRes 上传结果
     * @param array|object $user 用户信息
     * @param array $other 其他附加信息,['is_auth','tag','compress_enable','title','create_time','update_time','update_by']
     * @return array|bool
     */
    public static function makeAttachDataByUploadResult($uploadRes=[], $user=null, $other=[]) {
        if(empty($uploadRes) || empty($user)) return false;

        if(!is_object($user)) $user = ArrayHelper::arrayToObject($user);

        $now = time();
        $isImg = ValidateHelper::isImage($uploadRes['new_name']);
        $compressEnable = 0;
        if($isImg) {
            $compressEnable = intval($other['compress_enable'] ?? 0);
        }

        $avatarData = [
            'site_id' => $user->site_id,
            'is_del' => '0',
            'is_img' => intval($isImg),
            'is_auth' => intval($other['is_auth'] ?? 0),
            'is_persistent' => '0',
            'has_third' => '0',
            'compress_enable' => $compressEnable,
            'uid' => $user->uid,
            'img_width' => ($uploadRes['width'] ?? 0),
            'img_height' => ($uploadRes['height'] ?? 0),
            'quote_num' => '0',
            'downl_num' => '0',
            'file_size' => ($uploadRes['size'] ?? 0),
            'tag' => ($other['tag'] ?? ''),
            'title' => ($other['title'] ?? ($uploadRes['new_name'] ?? '')),
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


}