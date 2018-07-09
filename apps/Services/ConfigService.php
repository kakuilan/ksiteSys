<?php
/**
 * Copyright (c) 2018 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2018/3/4
 * Time: 22:24
 * Desc: -配置服务类
 */


namespace Apps\Services;

use Apps\Models\Config;
use Lkk\Helpers\ArrayHelper;
use Lkk\Helpers\StringHelper;
use Lkk\Helpers\ValidateHelper;
use Kengine\Server\LkkServer;

class ConfigService extends ServiceBase {


    /**
     * 获取系统/全局配置key
     * @param string $key
     * @return string
     */
    public static function getGlobalKey(string $key) {
        return $key ? (stripos($key, Config::$globalConfPrefix)===0 ? $key : Config::$globalConfPrefix . $key) : '';
    }


    /**
     * 获取站点/局部配置key
     * @param string $key
     * @return bool|string
     */
    public static function getSiteKey(string $key) {
        return $key ? (stripos($key, Config::$globalConfPrefix)===0 ? substr($key, strlen(Config::$globalConfPrefix)) : $key) : '';
    }


    /**
     * 解析配置值
     * @param null $value 原值
     * @param string $dataType 数据类型
     * @return float|int|mixed|null
     */
    public static function parseValue($value=null, $dataType='') {
        if(is_null($value)) {
            return $value;
        }elseif (is_string($value) && empty($value)) {
            return '';
        }

        if(empty($dataType)) $dataType = 'string';
        $dataType = strtolower($dataType);

        switch ($dataType) {
            default :
                break;
            case 'bool' :case 'integer' :
                $value = intval($value);
                break;
            case 'float' :
                $value = floatval($value);
                break;
            case 'datetime' :case 'string' :case 'text' :
                break;
            case 'array' :case 'json' :
                $value = json_decode($value);
                break;
        }

        return $value;
    }


    /**
     * 获取系统配置
     * 异步协程,使用yield
     * @param bool $new 是否获取最新
     * @return array
     */
    public static function getGlobalConfigs($new=false) {
        $redis = LkkServer::getPoolManager()->get('redis_site')->pop();
        $key = ConstService::CACHE_CONFIG_ACTI_SYS;
        $ret = yield $redis->get($key);
        $res = promiseRedisResult($ret);
        if($res) $res = (array)json_decode($res);

        if($new || empty($res)) {
            $where = [
                'is_del' => 0,
                'site_id' => 0,
            ];
            $list = yield Config::getListAsync($where, Config::$baseFields, 'id asc');
            if($list) {
                $res = [];
                foreach ($list as $item) {
                    $dataType = strtolower($item['data_type']);
                    $val = self::parseValue((in_array($dataType, ['text','array','json']) ? $item['extra'] : $item['value']), $dataType);
                    $res[$item['key']] = $val;
                }

                ksort($res);
                yield $redis->set($key, json_encode($res));
            }
        }
        unset($redis, $key, $ret, $where, $list);

        return $res ? $res : [];
    }


    /**
     * 获取站点配置
     * 异步协程,使用yield
     * @param int|null $siteId 站点ID
     * @param bool $new 是否获取最新
     * @return array
     */
    public static function getSiteConfigs(int $siteId=null, $new=false) {
        if(is_null($siteId)) $siteId = getSiteId();

        $redis = LkkServer::getPoolManager()->get('redis_site')->pop();
        $key = ConstService::CACHE_CONFIG_ACTI_SITE . $siteId;
        $ret = yield $redis->get($key);
        $res = promiseRedisResult($ret);
        if($res) $res = (array)json_decode($res);

        if($new || empty($res)) {
            $where = [
                'is_del' => 0,
                'site_id' => $siteId,
            ];
            $list = yield Config::getListAsync($where, Config::$baseFields, 'id asc');
            if($list) {
                $res = [];
                foreach ($list as $item) {
                    $dataType = strtolower($item['data_type']);
                    $val = self::parseValue((in_array($dataType, ['text','array','json']) ? $item['extra'] : $item['value']), $dataType);
                    $res[$item['key']] = $val;
                }

                ksort($res);
                yield $redis->set($key, json_encode($res));
            }
        }
        unset($redis, $key, $ret, $where, $list);

        return $res ? $res : [];
    }


    /**
     * 获取全部配置(系统配置和当前站点配置)
     * 异步协程,使用yield
     * @param bool $new
     * @return array
     */
    public static function getAllConfigs($new=false) {
        $gloConf = yield self::getGlobalConfigs($new);
        $sitConf = yield self::getSiteConfigs(null, $new);

        $res = array_merge($gloConf, $sitConf);
        $res = array_unique($res);
        ksort($res);
        unset($gloConf, $sitConf);

        return $res;
    }


    /**
     * 根据key获取系统配置值
     * 异步协程,使用yield
     * @param string $key 配置键
     * @return null
     */
    public static function getGlobalValueByKey(string $key) {
        if(empty($key)) return null;

        $conf = yield self::getGlobalConfigs();
        $key = self::getGlobalKey($key);
        $res = $conf[$key] ?? null;
        unset($conf, $key);

        return $res;
    }


    /**
     * 根据key获取站点配置值
     * 异步协程,使用yield
     * @param string $key 配置键
     * @param int $siteId 站点ID,默认当前站点
     * @return null
     */
    public static function getSiteValueByKey(string $key, $siteId=null) {
        if(empty($key)) return null;

        $conf = yield self::getSiteConfigs($siteId);
        $key = self::getSiteKey($key);
        $res = $conf[$key] ?? null;
        unset($conf, $key);

        return $res;
    }


    /**
     * 检查配置键是否存在
     * 异步协程,使用yield
     * @param string $key 配置键
     * @param bool $forceDb 是否强制查询数据库
     * @return bool
     */
    public static function existsKey(string $key, $forceDb=false) {
        if(empty($key)) return false;

        if(!$forceDb) {
            $gloConf = yield self::getGlobalConfigs();
            $sitConf = yield self::getSiteConfigs(null);

            $res = isset($gloConf[$key]) || isset($sitConf[$key]);
            unset($gloConf, $sitConf);
        }else{
            $siteId = getSiteId();
            $where = [
                'is_del' => 0,
                'site_id' => [0, $siteId],
                '`key`' => $key,
            ];
            $res = yield Config::getCountAsync($where);
            $res = boolval($res);
            unset($siteId, $where);
        }

        return $res;
    }



}