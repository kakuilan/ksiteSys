<?php
/**
 * Created by PhpStorm.
 * User: kakuilan@163.com
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

    //允许文件类型
    public static $defaultAllowType = [
        'gz','bz2','rar','zip','7z', //压缩包
        'txt','chm','pdf','doc','docx','xls','xlsx','ppt','pptx','wps', //文档
        'gif','jpg','jpeg','bmp','png', //图片
        'mp3','wma','wav', //音频
        'mp4','avi','mov','flv' //视频
        ];
    public static $defaultMaxSize = 524288; //允许单个文件最大上传尺寸,单位字节,默认512K
    public static $defaultMaxFile = 10; //每次最多允许上传N个文件

    public static $savePathTemp = UPLODIR . 'temp/'; //临时保存目录,微博/论坛图片只保存2个月;2个月后精选内容挪到永久目录,其他过期的删除
    public static $savePathLongAttach = UPLODIR . 'attach/'; //永久保存目录,附件
    public static $savePathLongAvatar = UPLODIR . 'avatar/'; //永久保存目录,头像
    public static $savePathLongPictur = UPLODIR . 'picture/'; //永久保存目录,图片

    public static $defaultResult   = [
        'status' => false, //上传结果
        'name' => '', //保存的文件名
        'type' => '', //文件类型
        'exte' => '', //文件扩展名
        'size' => 0, //文件大小,单位bit
        'info' => '', //提示信息
        'error' => '-1', //错误码
        'width' => 0, //图片宽度
        'height' => 0, //图片高度
        'absolute_path' => '', //绝对路径
        'relative_path' => '', //相对WEB目录路径
        'url'   => '', //文件URL地址
        'is_exists' => false, //根据文件md5检查是否已存在(之前已上传有相同的文件)
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
        '-14' => '不是有效的base64图片',
        '-15' => 'base64图片信息获取失败',
        '99'  => '上传成功',
    ];

    protected $originFiles = []; //上传的源文件数组,$_FILE或request->files
    protected $inputNames = [];
    protected $fileInfos = [];
    protected $results = [];

    //默认参数
    protected $webDir       = ''; //WEB目录
    protected $webUrl       = ''; //WEB URL
    protected $savePath     = null; //文件保存目录
    protected $allowType    = [];	//允许文件类型
    protected $isOverwrite  = false; //是否允许覆盖同名文件
    protected $isRename     = true; //是否重命名(随机文件名),还是直接使用上传文件的名称
    protected $maxSize      = 0; //允许单个文件最大上传尺寸,单位字节
    protected $maxFile      = 0; //每次最多允许上传N个文件
    protected $allowSubDir  = true; //允许自动创建目录
    protected $randNameSeed = ''; //随机名称种子

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
    public function getErrorInfoByCode($errorCode=-1) {
        $info = self::$defaultErrorInfo[$errorCode] ?? $errorCode;
        if($errorCode==-3) {
            $info .= intval($this->maxSize/1024) . 'kb';
        }elseif ($errorCode==-4) {
            $info .= implode(',', $this->allowType);
        }

        return $info;
    }


    /**
     * 设置保存目录
     * @param string $val
     * @return $this
     */
    public function setSavePath($val='') {
        if(!empty($val)) $this->savePath = DirectoryHelper::formatDir($val);
        return $this;
    }


    /**
     * 设置web目录
     * @param string $val
     * @return $this
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
     * @return $this
     */
    public function setAllowType($val=[]) {
        if(!empty($val) && is_array($val)) $this->allowType = $val;
        return $this;
    }


    /**
     * 设置是否允许创建子目录
     * @param bool $val
     *
     * @return $this
     */
    public function setAllowSubDir($val=false) {
        $this->allowSubDir = boolval($val);
        return $this;
    }


    /**
     * 设置是否允许覆盖
     * @param bool $val
     * @return $this
     */
    public function setOverwrite($val=false) {
        $this->isOverwrite = boolval($val);
        return $this;
    }


    /**
     * 设置是否允许重命名
     * @param bool $val
     * @return $this
     */
    public function setRename($val=false) {
        $this->isRename = boolval($val);
        return $this;
    }


    /**
     * 设置随机名称种子
     * @param string $val
     * @return $this
     */
    public function setRandNameSeed($val='') {
        $this->randNameSeed = strval($val);
        return $this;
    }



    /**
     * 设置允许上传的最大值
     * @param int $val
     * @return $this
     */
    public function setMaxSize($val=0) {
        if(is_numeric($val) && $val>0) $this->maxSize = $val;
        return $this;
    }


    /**
     * 设置每次最多允许N个文件上传
     * @param int $val
     * @return $this
     */
    public function setMaxFile($val=0) {
        if(is_numeric($val) && $val>0) $this->maxFile = $val;
        return $this;
    }


    /**
     * 设置上传源
     * @param null $val
     * @return $this
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
     * 上传base64图片
     * @param string $cont
     * @param string $newName
     *
     * @return bool
     */
    public function uploadBase64Img($cont='', $newName='') {
        $result = self::$defaultResult;
        $inputName = 'base64';
        $this->results[$inputName] = $result;

        if(empty($cont)) {
            $this->setError('base64为空', 4);
            return false;
        }
        $chkInfo = ValidateHelper::isBase64Image($cont);
        if(empty($chkInfo)) {
            $this->setError('base64图片错误', -14);
            return false;
        }

        $imgInfo = self::getBase64ImageSize($cont);
        $exte = $imgInfo['exte'] ?? '';
        if(empty($imgInfo)) {
            $this->setError('图片信息获取失败', -15);
            return false;
        }elseif (!in_array($exte, $this->allowType)) {
            $this->setError('文件类型不允许', -4);
            return false;
        }

        $cont = base64_decode(str_replace($chkInfo[1], '', $cont));
        $error = -1;
        if(empty($newName) || !preg_match("/^[a-z0-9\-_.]+$/i", $newName)) $newName = self::makeRandName($cont, $exte, $this->randNameSeed);
        $newFilePath = $this->savePath . ($this->allowSubDir ? self::getSubpathByFilename($newName) : $newName);

        if(file_exists($newFilePath) && !$this->isOverwrite && $this->isRename) {
            $newName = self::makeRandName(null, $exte, $this->randNameSeed);
            $newFilePath = $this->savePath . ($this->allowSubDir ? self::getSubpathByFilename($newName) : $newName);
        }


        $hasSameFile = file_exists($newFilePath) && $cont==file_get_contents($newFilePath);//文件内容相同
        if(file_exists($newFilePath) && !$hasSameFile && !$this->isOverwrite) { //不允许覆盖
            $error = -9;
        }else{
            //检查图片
            $saveRes = $hasSameFile ? true : (self::saveFile($cont, $newFilePath));
            if(!$imgInfo) {
                $error = -11;
            }elseif (!$saveRes) {
                $error = -10;
            }else{
                $error = 99; //成功
                $imgInfo['exte'] = $exte;
                $imgInfo['size'] = filesize($newFilePath);
                $imgInfo['new_name'] = $newName;
                $imgInfo['absolute_path'] = $newFilePath;
                $imgInfo['relative_path'] = '/'. ltrim(str_replace($this->webDir, '', $newFilePath), '/');
                $imgInfo['url'] = $this->webUrl . $imgInfo['relative_path'];
                $imgInfo['is_exists'] = $hasSameFile;
            }
        }

        $result = array_merge($result, $imgInfo);
        $result['error'] = $error;
        $result['status'] = ($error==99);
        $result['info'] = $this->getErrorInfoByCode($error);

        $this->results[$inputName] = $result;

        return true;
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
        $j = 0;
        foreach ($inputNames as $k=>$inputName) {
            if(empty($inputName)) continue;

            $fileInfos = $this->originFiles[$inputName] ?? [];
            if(empty($fileInfos)) continue;

            //处理同名文件域数组,如file[],file[]
            $fileInfos = isset($fileInfos[0]) ? $fileInfos : [0=>$fileInfos];
            $num = isset($fileInfos[0]) ? count($fileInfos) : 1;
            foreach ($fileInfos as $i=>$fileInfo) {
                $fileInfo['new_name'] = $newNames[$j] ?? '';
                $j++;

                $newInputName = $num==1 ? $inputName : "{$inputName}_{$i}";
                array_push($this->inputNames, $newInputName);
                $this->fileInfos[$newInputName] = $fileInfo;
            }
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
            $chk = @mkdir($this->savePath, 0755, true);
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
                    if(empty($newName) || !preg_match("/^[a-z0-9\-_.]+$/i", $newName)) $newName = self::makeRandName($fileInfo['tmp_name'], $exte, $this->randNameSeed);
                    $newFilePath = $this->savePath . ($this->allowSubDir ? self::getSubpathByFilename($newName) : $newName);

                    //重命名
                    if(file_exists($newFilePath) && !$this->isOverwrite && $this->isRename) {
                        $newName = self::makeRandName(null, $exte, $this->randNameSeed);
                        $newFilePath = $this->savePath . ($this->allowSubDir ? self::getSubpathByFilename($newName) : $newName);
                    }

                    $hasSameFile = file_exists($newFilePath) && md5_file($newFilePath)==md5_file($fileInfo['tmp_name']);//文件md5相同
                    if(file_exists($newFilePath) && !$hasSameFile && !$this->isOverwrite) { //不允许覆盖
                        $error = -9;
                    }else{
                        //检查图片
                        $imgInfo = in_array($exte, ['gif','jpg','jpeg','png','bmp']) ? self::getImageSize($fileInfo['tmp_name'], $exte) : true;
                        $saveRes = $hasSameFile ? true : (self::saveFile($fileInfo['tmp_name'], $newFilePath));
                        if(!$imgInfo) {
                            $error = -11;
                        }elseif (!$saveRes) {
                            $error = -10;
                        }else{
                            $error = 99; //成功
                            if($imgInfo && is_array($imgInfo)) $fileInfo = array_merge($fileInfo, $imgInfo);
                            $fileInfo['exte'] = $exte;
                            $fileInfo['new_name'] = $newName;
                            $fileInfo['absolute_path'] = $newFilePath;
                            $fileInfo['relative_path'] = '/'. ltrim(str_replace($this->webDir, '', $newFilePath), '/');
                            $fileInfo['url'] = $this->webUrl . $fileInfo['relative_path'];
                            $fileInfo['is_exists'] = $hasSameFile;
                        }
                    }
                }
            }

            $result = array_merge(self::$defaultResult, $fileInfo);
            $result['error'] = $error;
            $result['status'] = ($error==99);
            $result['info'] = $this->getErrorInfoByCode($error);

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
     * @param string $file 文件路径
     * @param string $ext 扩展名
     * @param string $randSeed 随机种子
     * @return string
     */
    public static function makeRandName($file='', $ext='', $randSeed='') {
        if(!empty($file)) {
            $uniq = strlen($file)>255 ? md5($file) : (file_exists($file) ? md5_file($file) : md5($file));
            $uniq = $randSeed ? md5($uniq . $randSeed) : $uniq;
        }else{
            $uniq = md5(uniqid(mt_rand(),true) . $randSeed);
        }

        $res = date('ymd'). substr($uniq, 8, 16);

        return strtolower($ext ? ($res . ".{$ext}") : $res);
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
     * 获取base64图片宽高信息
     * @param string $str
     *
     * @return array|bool
     */
    public static function getBase64ImageSize($str='') {
        if(!ValidateHelper::isBase64Image($str)) return false;
        $types = [
            1 => 'gif',
            2 => 'jpg',
            3 => 'png',
            4 => 'swf',
            5 => 'psd',
            6 => 'bmp',
        ];
        $arr = getimagesize($str);
        $res = [
            'width' => $arr[0] ?? 0,
            'height' => $arr[1] ?? 0,
            'type' => $arr[2] ?? 0,
            'exte' => $types[$arr[2]] ?? '',
        ];

        return $res;
    }


    /**
     * 保存文件
     * @param string $tmpFilePath 旧文件路径/文件内容
     * @param string $newFilePath 新文件路径
     * @return bool
     */
    public static function saveFile($tmpFilePath='', $newFilePath='') {
        if(empty($tmpFilePath) || empty($newFilePath)) return false;
        $dir = dirname($newFilePath);
        if(!is_dir($dir)) @mkdir($dir, 0755, true);

        if(strlen($tmpFilePath)<=512 && is_file($tmpFilePath)) {
            $res = function_exists("move_uploaded_file") ? @move_uploaded_file($tmpFilePath, $newFilePath) : @copy($tmpFilePath, $newFilePath);
        }else{
            $res = file_put_contents($newFilePath, $tmpFilePath); //以内容填充文件
        }

        if($res) @chmod($newFilePath, 0755);

        return $res;
    }


    /**
     * 获取上传文件总大小
     * @param array $files 上传的文件数组-swooleRequest->files
     * @param array $inputNames 要统计的文本域,为空则统计全部
     * @return int 字节
     */
    public static function getUploadFilesSize($files=[], $inputNames=[]) {
        if(empty($files)) return 0;
        $inputNames = empty($inputNames) ? array_keys($files) : (array)$inputNames;

        $size = 0;
        foreach ($inputNames as $inputName) {
            $fileInfos = $files[$inputName] ?? [];
            if(empty($fileInfos)) continue;

            //处理同名文件域数组,如file[],file[]
            $fileInfos = isset($fileInfos[0]) ? $fileInfos : [0=>$fileInfos];
            foreach ($fileInfos as $fileInfo) {
                $size += ($fileInfo['size'] ?? 0);
            }
        }
        unset($files, $inputNames, $fileInfos);

        return $size;
    }



    public static function moveAttach($oldFilePath='', $newDir='', $returnRelative=true) {
        if(empty($oldFilePath)) return false;



    }


}