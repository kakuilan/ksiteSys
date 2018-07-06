<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/10/1
 * Time: 18:29
 * Desc: -数据表模型 site
 */


namespace Apps\Models;

class Site extends  BaseModel {


    public static $remarkMaxLength = 128; //备注最大长度

    /**
     * 默认字段
     * @var string
     */
    public static $defaultFields = 'site_id,site_name,site_url,status,remark';

    public function initialize() {
        parent::initialize();
    }


    /**
     * 获取站点状态数组
     * @return array
     */
    public static function getStatusArr() {
        return [
            '-1' => '关闭',
            '0' => '维护',
            '1' => '开启',
        ];
    }


    /**
     * 获取站点基本信息
     * @param int $siteId
     * @return array|\Phalcon\Mvc\Model|\Phalcon\Mvc\ModelInterface
     */
    public static function getBaseInfo($siteId=0) {
        $where = ['site_id' => $siteId];
        $res = self::getRow($where, self::$defaultFields);

        return $res;
    }



}