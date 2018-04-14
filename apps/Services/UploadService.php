<?php
/**
 * Created by PhpStorm.
 * User: doubo
 * Date: 2018/3/29
 * Time: 9:22
 * Desc: -上传服务类
 */

namespace Apps\Services;

use Lkk\Helpers\ArrayHelper;
use Lkk\Helpers\CommonHelper;
use Lkk\Helpers\DirectoryHelper;
use Lkk\Helpers\FileHelper;
use Lkk\Helpers\UrlHelper;
use Lkk\Helpers\ValidateHelper;
use Phalcon\Di;


class UploadService extends ServiceBase {

    public static $defaultAllowType = ['rar','zip','7z','txt','doc','docx','xls','xlsx','ppt','pptx','gif','jpg','jpeg','bmp','png'];	//允许文件类型
    public static $defaultMaxSize = 524288; //允许单个文件最大上传尺寸,单位字节
    public static $defaultMaxFile = 10; //每次最多允许上传N个文件
    public static $defaultResult   = [
        'status' => false, //上传结果
        'name' => '', //保存的文件名
        'type' => '', //文件类型
        'exte' => '', //文件扩展名
        'size' => 0, //文件大小,单位bit
        'info' => '', //提示信息
        'error' => '', //错误码
        'width' => 0, //图片宽度
        'height' => 0, //图片高度
        'absolute_path' => '', //绝对路径
        'relative_path' => '', //相对WEB目录路径
        'url'   => '', //文件URL地址
    ];

    public static $defaultErrorInfo = [ //错误消息
        //系统错误消息
        '0' => '没有错误发生',
        '1' => '上传文件大小超出系统限制', //php.ini中upload_max_filesize
        '2' => '上传文件大小超出网页表单限制', //HTML表单中规定的MAX_FILE_SIZE
        '3' => '文件只有部分被上传',
        '4' => '没有文件被上传',
        '5' => '上传文件大小为0',
        '6' => '找不到临时文件夹',
        '7' => '文件写入失败',

        //自定义错误消息
        '-1' => '未知错误',
        '-2' => '未找到相应的文件域',
        '-3' => '文件大小超出允许范围:',
        '-4' => '文件类型不在允许范围:',
        '-5' => '未指定上传目录',
        '-6' => '创建目录失败',
        '-7' => '目录不可写',
        '-8' => '临时文件不存在',
        '-9' => '存在同名文件,取消上传',
        '-10' => '文件移动失败',
        '-11' => '文件内容可能不安全',
        '-12' => '未设置原始上传源',
        '-13' => '上传文件数超出限制',
        '99'  => '上传成功',
    ];

    protected $originFiles = []; //上传的源文件数组,$_FILE或request->files
    protected $inputNames = [];
    protected $fileInfos = [];
    protected $results = [];

    //默认参数
    protected $webDir      = ''; //WEB目录
    protected $webUrl      = ''; //WEB URL
    protected $savePath    = null; //文件保存目录
    protected $allowType   = [];	//允许文件类型
    protected $isOverwrite = false; //是否允许覆盖同名文件
    protected $isRename    = true; //是否重命名(随机文件名),还是直接使用上传文件的名称
    protected $maxSize     = 0; //允许单个文件最大上传尺寸,单位字节
    protected $maxFile     = 0; //每次最多允许上传N个文件

    public function __construct(array $vars = []) {
        parent::__construct($vars);

        if(empty($this->allowType)) $this->allowType = self::$defaultAllowType;
        if(!is_numeric($this->maxSize) || $this->maxSize<=0) $this->maxSize = self::$defaultMaxSize;
        if(!is_numeric($this->maxFile) || $this->maxFile<=0) $this->maxFile = self::$defaultMaxFile;

    }


    /**
     * 根据错误码获取错误信息
     * @param int $errorCode
     * @return int|mixed
     */
    public static function getErrorInfoByCode($errorCode=-1) {
        return self::$defaultErrorInfo[$errorCode] ?? $errorCode;
    }


    /**
     * 设置保存目录
     * @param string $val
     */
    public function setSavePath($val='') {
        if(!empty($val)) $this->savePath = DirectoryHelper::formatDir($val);
        return $this;
    }


    /**
     * 设置web目录
     * @param string $val
     */
    public function setWebDir($val='') {
        if(!empty($val)) $this->webDir = DirectoryHelper::formatDir($val);
        return $this;
    }


    /**
     * 设置web Url
     * @param string $val
     * @return $this
     */
    public function setWebUrl($val='') {
        if(!empty($val)) $this->webUrl = rtrim(UrlHelper::formatUrl($val), '/');
        return $this;
    }

    /**
     * 设置允许上传的文件类型
     * @param array $val
     */
    public function setAllowType($val=[]) {
        if(!empty($val) && is_array($val)) $this->allowType = $val;
        return $this;
    }


    /**
     * 设置是否允许覆盖
     * @param bool $val
     */
    public function setOverwrite($val=false) {
        $this->isOverwrite = boolval($val);
        return $this;
    }


    /**
     * 设置是否允许重命名
     * @param bool $val
     */
    public function setRename($val=false) {
        $this->isRename = boolval($val);
        return $this;
    }


    /**
     * 设置允许上传的最大值
     * @param int $val
     */
    public function setMaxSize($val=0) {
        if(is_numeric($val) && $val>0) $this->maxSize = $val;
        return $this;
    }


    /**
     * 设置每次最多允许N个文件上传
     * @param int $val
     */
    public function setMaxFile($val=0) {
        if(is_numeric($val) && $val>0) $this->maxFile = $val;
        return $this;
    }


    /**
     * 设置上传源
     * @param null $val
     */
    public function setOriginFiles($val=null) {
        $this->originFiles = $val;
        return $this;
    }


    /**
     * 上传多个文件
     * @param array $inputNames 上传的文件域名称数组
     * @param array $newNames 文件保存的新名称数组
     * @param null $origin 上传源
     * @return bool
     */
    public function uploadMulti($inputNames = [], $newNames = [], $origin=null) {
        $attch = $this->attachInputs($inputNames, $newNames);
        if(!$attch) return false;

        $check = $this->doCheck();
        if(!$check) return false;

        $res = $this->doUpload();
        return $res;
    }


    /**
     * 上传单个文件
     * @param string $inputName 上传的文件域名称
     * @param string $newName 文件保存的新名称
     * @param null $origin 上传源
     * @return bool
     */
    public function uploadSingle($inputName='', $newName='', $origin=null) {
        $inputNames = [$inputName];
        $newNames = [$newName];

        $attch = $this->attachInputs($inputNames, $newNames);
        if(!$attch) return false;

        $check = $this->doCheck();
        if(!$check) return false;

        $res = $this->doUpload();
        return $res;
    }


    /**
     * 匹配文件域
     * @param array $inputNames
     * @param array $newNames
     * @return bool
     */
    protected function attachInputs($inputNames = [], $newNames = []) {
        $count = count($inputNames);
        if($count > $this->maxFile) {
            $this->setError("每次最多同时上传{$this->maxFile}个文件", -13);
            return false;
        }

        $inputNames = array_unique(array_filter($inputNames));
        $newNames = array_unique(array_filter($newNames));

        if(empty($inputNames)) {
            $this->setError('文件域不能为空', -2);
            return false;
        }

        if(empty($this->originFiles)) {
            $this->setError('未设置上传源或无上传文件', -12);
            return false;
        }

        $this->inputNames = $this->fileInfos = $this->results = [];
        foreach ($inputNames as $k=>$inputName) {
            if(empty($inputName)) continue;

            $fileInfo = $this->originFiles[$inputName] ?? [];
            if($fileInfo) {
                $fileInfo['new_name'] = $newNames[$k] ?? '';
            }

            array_push($this->inputNames, $inputName);
            $this->fileInfos[$inputName] = $fileInfo;
        }
        unset($inputNames, $newNames);

        return true;
    }


    /**
     * 执行检查
     * @return bool
     */
    protected function doCheck() {
        //检查保存目录
        if(empty($this->savePath)) {
            $this->setError('保存目录不能为空', -5);
            return false;
        }elseif (!is_dir($this->savePath)) {
            $chk = @mkdir($this->params['savePath'], 0755, true);
            if(!$chk) {
                $this->setError('保存目录创建失败', -6);
                return false;
            }
        }elseif (!CommonHelper::isReallyWritable($this->savePath)) {
            $this->setError('保存目录无写权限', -7);
            return false;
        }

        //检查临时目录
        $tmpDir = self::getTmpDir();
        if(empty($tmpDir) || !is_dir($tmpDir)) {
            $this->setError('临时目录不存在', 6);
            return false;
        }

        return true;
    }


    /**
     * 执行上传
     * @return bool
     */
    protected function doUpload() {
        if(empty($this->inputNames)) {
            $this->setError('无上传的文件', -2);
            return false;
        }

        $uploadErrs = range(1, 7);
        foreach ($this->inputNames as $inputName) {
            $fileInfo = $this->fileInfos[$inputName] ?? [];
            $error = $fileInfo['error'] ?? -1;
            $newName = $fileInfo['new_name'] ?? '';

            if(empty($fileInfo)) {
                $error = -2;
            }elseif(!in_array($fileInfo['error'], $uploadErrs)) { //上传中无错误
                $exte = FileHelper::getFileExt($fileInfo['name']);
                if($fileInfo['size'] > $this->maxSize) { //检查文件大小
                    $error = -3;
                }elseif (!in_array($exte, $this->allowType)) { //检查文件扩展名
                    $error = -4;
                }elseif (!file_exists($fileInfo['tmp_name'])) {
                    $error = -8;
                }else{
                    if(empty($newName) || !preg_match("/^[a-z0-9\-_]+$/i", $newName)) $newName = self::makeRandName($exte);
                    $newFilePath = $this->savePath . self::getSubpathByFilename($newName);

                    if(file_exists($newFilePath) && !$this->isOverwrite && $this->isRename) {
                        $newName = self::makeRandName($exte);
                        $newFilePath = $this->savePath . self::getSubpathByFilename($newName);
                    }

                    if(file_exists($newFilePath) && !$this->isOverwrite) {
                        $error = -9;
                    }else{
                        //检查图片
                        $imgInfo = in_array($exte, ['gif','jpg','jpeg','png','bmp']) ? self::getImageSize($fileInfo['tmp_name'], $exte) : true;
                        if(!$imgInfo) {
                            $error = -11;
                        }elseif (!self::saveFile($fileInfo['tmp_name'], $newFilePath)) {
                            $error = -10;
                        }else{
                            $error = 99; //成功
                            if($imgInfo && is_array($imgInfo)) $fileInfo = array_merge($fileInfo, $imgInfo);
                            $fileInfo['exte'] = $exte;
                            $fileInfo['new_name'] = $newName;
                            $fileInfo['absolute_path'] = $newFilePath;
                            $fileInfo['relative_path'] = '/'. ltrim(str_replace($this->webDir, '', $newFilePath), '/');
                            $fileInfo['url'] = $this->webUrl . $fileInfo['relative_path'];
                        }
                    }
                }
            }

            $result = array_merge(self::$defaultResult, $fileInfo);
            $result['error'] = $error;
            $result['status'] = ($error==99);
            $result['info'] = self::getErrorInfoByCode($error);

            $this->results[$inputName] = $result;
        }

        return true;
    }


    /**
     * 获取单个上传结果
     * @return mixed
     */
    public function getSingleResult() {
        return current($this->results);
    }


    /**
     * 获取多个上传结果
     * @return array
     */
    public function getMultiResult() {
        return $this->results;
    }


    /**
     * 生成随机文件名
     * @param string $ext 扩展名
     * @return string
     */
    public static function makeRandName($ext='') {
        $uniq = md5(uniqid(mt_rand(),true));
        $res = date('ymd'). substr($uniq, 8, 16);

        return $ext ? ($res . ".{$ext}") : $res;
    }


    /**
     * 根据文件名获取子路径
     * @param string $fileName
     *
     * @return string
     */
    public static function getSubpathByFilename($fileName='') {
        if(empty($fileName)) return '';

        $dir1 = substr($fileName, 0, 2);
        $dir2 = substr($fileName, 2, 2);
        $dir3 = substr($fileName, 4, 2);

        return $dir1.'/'.$dir2.'/'.$dir3.'/'.$fileName;
    }


    /**
     * 获取临时目录
     * @return string
     */
    public static function getTmpDir() {
        return ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir();
    }


    /**
     * 获取图片宽高信息
     * @param string $srcFile
     * @param null $srcExt
     * @return array|bool
     */
    public static function getImageSize($srcFile, $srcExt = null) {
        if(empty($srcFile)) return [];
        if(empty($srcExt)) $srcExt = FileHelper::getFileExt($srcFile);

        $srcdata = [];
        try {
            if (function_exists('read_exif_data') && in_array($srcExt, [
                    'jpg',
                    'jpeg',
                    'jpe',
                    'jfif'
                ])) {
                $datatemp = read_exif_data($srcFile);
                $srcdata['width'] = $datatemp['COMPUTED']['Width'];
                $srcdata['height'] = $datatemp['COMPUTED']['Height'];
                $srcdata['type'] = 2;
                unset($datatemp);
            }
            (!isset($srcdata['width']) || !$srcdata['width']) && list($srcdata['width'], $srcdata['height'], $srcdata['type']) = getimagesize($srcFile);
            if (!isset($srcdata['type']) || !$srcdata['type'] || ($srcdata['type'] == 1 && in_array($srcExt, [
                        'jpg',
                        'jpeg',
                        'jpe',
                        'jfif'
                    ]))) {
                return false;
            }
        }catch (\Throwable $e) {

        }
        unset($srcdata['type']);

        return $srcdata;
    }


    /**
     * 保存文件
     * @param string $tmpFilePath 临时文件路径
     * @param string $newFilePath 新文件路径
     * @return bool
     */
    public static function saveFile($tmpFilePath='', $newFilePath='') {
        if(empty($tmpFilePath) || empty($newFilePath)) return false;
        $dir = dirname($newFilePath);
        if(!is_dir($dir)) @mkdir($dir, 0755, true);

        $res = function_exists("move_uploaded_file") ? @move_uploaded_file($tmpFilePath, $newFilePath) : @copy($tmpFilePath, $newFilePath);
        if($res) @chmod($newFilePath, 0755);

        return $res;
    }


}