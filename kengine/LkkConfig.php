<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/9/16
 * Time: 9:21
 * Desc: -lkk 配置类
 */


namespace Kengine;

use Phalcon\Config;

class LkkConfig {

    //配置信息数组
    private static $_configs = [];


    /**
     * 获取配置文件(或某个键)的值
     * @param string $file 配置文件(不含.php后缀)
     * @param string $key 配置键
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function get($file, $key = null, $default = null) {
        $file = ltrim(str_replace('\\', '/', $file), '/');
        if(! isset(self::$_configs[$file]) ) {
            $pos = strpos($file, DS);
            if($pos === false) {//系统级配置,例如 system => /config/system.php
                $filePath = CONFDIR . $file . PHPEXT;
            }else{//app下对应目录,例如 modules/frontend/test => /apps/modules/frontend/config/test.php
                $pathArr = explode(DS, $file);
                $fileName = array_pop($pathArr);
                $filePath = APPSDIR . implode(DS, $pathArr) . DS . 'config' . DS . $fileName . PHPEXT;
            }

            if( file_exists($filePath) ){
                self::$_configs[$file] = include $filePath;
            }else{
                self::$_configs[$file] = [];
            }
        }

        return is_null($key) ? (new Config(self::$_configs[$file])) : (self::$_configs[$file][$key] ?? $default);
    }


    /**
     * 写配置文件
     * @param string $file 配置文件(不含.php后缀)
     * @param array $array 配置内容,必须是数组形式
     * @param bool $overwrite 是否覆盖
     * @return bool|int
     */
    public static function write($file, $array=[], $overwrite=false){
        $file = ltrim(str_replace('\\', '/', $file), '/');
        $pos = strpos($file, DS);
        if($pos === false) {
            return false; //禁止写系统级配置
        }else{//app应用级目录
            $pathArr = explode(DS, $file);
            $fileName = array_pop($pathArr);
            $confDirectory = APPSDIR . implode(DS, $pathArr) . DS . 'config' . DS;
            $filePath = $confDirectory . $fileName . PHPEXT;

            if(!is_dir($confDirectory)){ //配置目录不存在
                return false;
            }elseif(!$overwrite && file_exists($filePath)) { //不能覆盖之前的配置
                return false;
            }elseif(!is_array($array)){ //配置内容必须是数组形式
                return false;
            }elseif($fh = fopen($filePath, 'w')){
                if(flock($fh,LOCK_EX)){ //写锁
                    $res = fwrite($fh, "<?php\r\nreturn " . var_export($array, true) . ';');
                    flock($fh,LOCK_UN);
                    fclose($fh);
                    $res && chmod($filePath, 0777);
                    return $res;
                }
            }
        }

        return false;
    }


}