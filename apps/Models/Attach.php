<?php
/**
 * Copyright (c) 2018 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2018/3/25
 * Time: 22:23
 * Desc: -数据表模型 附件表attach
 */


namespace Apps\Models;

class Attach extends BaseModel {

    //默认字段
    public static $defaultFields = 'id,site_id,is_del,is_auth,is_persistent,has_third,compress_enable,belong_type,file_type,uid,img_width,img_height,quote_num,downl_num,file_size,tag,title,file_ext,file_name,file_path,qiniu_url,third_url';
    //基本字段
    public static $baseFields = 'id,uid,file_ext,file_name,file_path';


    /**
     * 获取审核状态数组
     * @return array
     */
    public static function getAuthStatusArr() {
        return [
            '-1' => '不通过',
            '0' => '待审核',
            '1' => '已通过',
        ];
    }


    /**
     * 获取持久化状态数组
     * @return array
     */
    public static function getPersistentStatusArr() {
        return [
            '0' => '非持久化',
            '1' => '待持久化',
            '2' => '已持久化',
        ];
    }


    /**
     * 获取第三方状态数组
     * @return array
     */
    public static function getHasThirdArr() {
        return [
            '0' => '无',
            '1' => '仅七牛',
            '2' => '仅第三方',
            '3' => '七牛和第三方',
        ];
    }


    /**
     * 获取归属类型数组
     * @return array
     */
    public static function getBelongTypeArr() {
        return [
            '0' => '系统',
            '1' => '后台',
            '2' => '用户',
        ];
    }


    /**
     * 获取文件类型数组
     * @return array
     */
    public static function getFileTypeArr() {
        return [
            '0' => '其他',
            '1' => '压缩包',
            '2' => '文档',
            '3' => '图片',
            '4' => '音频',
            '5' => '视频',
        ];
    }


    /**
     * 获取附件标识数组
     * @return array
     */
    public static function getTagArr() {
        return [
            'system' => '系统',
            'backend' => '后台',
            'ad' => '广告',
            'avatar' => '头像',
            'tweet' => '微博',
            'bbs' => '论坛',
            'album' => '相册',
            'blog' => '博客',
            'draft' => '投稿',
            'question' => '问答',
            'user' => '用户',
        ];
    }



    public function initialize() {
        parent::initialize();
    }







}