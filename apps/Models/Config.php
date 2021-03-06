<?php
/**
 * Copyright (c) 2018 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2018/3/4
 * Time: 20:33
 * Desc: -数据表模型 配置表config
 */


namespace Apps\Models;

class Config extends BaseModel {


    /**
     * 全局配置的key前缀
     * @var string
     */
    public static $globalConfPrefix = 'glo_';

    /**
     * 默认字段
     * @var string
     */
    public static $defaultFields = 'site_id,disable_del,data_type,input_type,key,title,remark,value,extra';


    /**
     * 基本字段
     * @var string
     */
    public static $baseFields = '`key`,`value`,extra,data_type';


    /**
     * 获取数据类型数组
     * @return array
     */
    public static function getDataTypeArr() {
        return [
            'bool' => '布尔型',
            'integer' => '整型',
            'float' => '浮点型',
            'datetime' => '日期时间',
            'string' => '字符串', //255字符以内
            'array' => '数组',
            'text' => '长文本', //255字符以外
            'json' => 'JSON',
        ];
    }


    /**
     * 获取控件类型数组
     * @return array
     */
    public static function getInputTypeArr() {
        return [
            'radio' => '单选框',
            'number' => '数字框',
            'datetime' => '日期时间框',
            'input' => '文本框',
            'file' => '文件域',
            'textarea' => '文本域',
        ];
    }


    public function initialize() {
        parent::initialize();
    }

}