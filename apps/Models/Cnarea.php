<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/5/6
 * Time: 14:50
 * Desc: -数据表模型 cnarea 中国行政区域表
 */


namespace Apps\Models;

class Cnarea extends BaseModel {

    public function initialize() {
        parent::initialize();

    }


    /**
     * 获取省份列表
     * @return array|\Phalcon\Mvc\Model
     */
    public static function getProvinces() {
        $res = self::getList([
            'level' => 0,
            'parent_id' => 0,
        ]);

        return $res;
    }


    /**
     * 获取子级区域列表
     * @param int   $parentId 父级ID
     * @param array $otherWhere 其他条件
     *
     * @return array|\Phalcon\Mvc\Model
     */
    public static function getChildAreas($parentId=0, $otherWhere=[]) {
        $where = [ 'parent_id' => $parentId ];
        if(!empty($otherWhere)) $where = array_merge($otherWhere, $where);

        $res = self::getList($where);
        return $res;
    }


    /**
     * 获取城市列表
     * @param int $provinceId 省份ID
     *
     * @return array|\Phalcon\Mvc\Model
     */
    public static function getCities($provinceId=0) {
        $res = self::getList([
            'level' => 1,
            'parent_id' => $provinceId,
        ]);

        return $res;
    }


    /**
     * 获取区县列表
     * @param int $cityId 城市ID
     *
     * @return array|\Phalcon\Mvc\Model
     */
    public static function getCounties($cityId=0) {
        $res = self::getList([
            'level' => 2,
            'parent_id' => $cityId,
        ]);

        return $res;
    }


    /**
     * 获取乡镇列表
     * @param int $countyId 区县ID
     *
     * @return array|\Phalcon\Mvc\Model
     */
    public static function getTowns($countyId=0) {
        $res = self::getList([
            'level' => 3,
            'parent_id' => $countyId,
        ]);

        return $res;
    }


    /**
     * 获取村/街列表
     * @param int $townId
     *
     * @return array|\Phalcon\Mvc\Model
     */
    public static function getVillages($townId=0) {
        $res = self::getList([
            'level' => 4,
            'parent_id' => $townId,
        ]);

        return $res;
    }





}