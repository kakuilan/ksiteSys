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
    public static $defaultFields = 'id,site_id,is_del,is_img,is_auth,is_persistent,has_third,compress_enable,uid,img_width,img_height,quote_num,downl_num,file_size,tag,title,file_ext,file_name,file_path,qiniu_url,third_url';
    //基本字段
    public static $baseFields = 'id,uid,file_ext,file_name,file_path';


    public function initialize() {
        parent::initialize();
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




}