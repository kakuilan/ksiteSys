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
use Lkk\Helpers\DirectoryHelper;
use Phalcon\Di;


class UploadService extends ServiceBase {

    public static $defaultAllowType = ['rar','zip','7z','txt','doc','docx','xls','xlsx','ppt','pptx','gif','jpg','jpeg','bmp','png'];	//允许文件类型
    public static $defaultMaxSize = 512; //允许单个文件最大上传尺寸,单位KB
    public static $defaultResult   = [
        'status' => false, //上传结果
        'info' => '', //提示信息
        'absolute_path' => '', //绝对路径
        'relative_path' => '', //相对WEB目录路径
        'name' => '', //保存的文件名
        'size' => 0, //文件大小,单位bit
    ];

    protected $results = [];

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
        '99'  => '上传成功',
    ];

    protected $errorCodes = [];

    protected $inputNames = [];
    protected $fileInfos = [];

    //默认参数
    public $webDir         = ''; //WEB目录
    protected $savePath    = null; //文件保存目录
    protected $allowType   = [];	//允许文件类型
    protected $isOverwrite = false; //是否允许覆盖同名文件
    protected $isRename    = true; //是否重命名(随机文件名),还是直接使用上传文件的名称
    protected $maxSize     = 0; //允许单个文件最大上传尺寸,单位KB

    public function __construct(array $vars = []) {
        parent::__construct($vars);

        if(empty($this->allowType)) $this->allowType = self::$defaultAllowType;
        if(!is_numeric($this->maxSize) || $this->maxSize<=0) $this->maxSize = self::$defaultMaxSize;

    }


    /**
     * 根据错误码获取错误信息
     * @param int $errorCode
     * @return int|mixed
     */
    public static function getErrorInfoByCode($errorCode=-1) {
        return self::$defaultErrorInfo[$errorCode] ?? $errorCode;
    }


    public function setWebDir($val='') {
        if(!empty($val)) $this->webDir = DirectoryHelper::formatDir($val);
    }


    public function setSavePath($val='') {
        if(!empty($val)) $this->savePath = DirectoryHelper::formatDir($val);
    }


    public function setAllowType($val=[]) {
        if(!empty($val) && is_array($val)) $this->allowType = $val;
    }


    public function setOverwrite($val=false) {
        $this->isOverwrite = boolval($val);
    }


    public function setRename($val=false) {
        $this->isRename = boolval($val);
    }

    public function setMaxSize($val=0) {
        if(is_numeric($val) && $val>0) $this->maxSize = $val;
    }


    public function upload($inputNames = [], $newNames = []) {
        if(empty($inputNames)) {
            $this->setError('文件域不能为空');
            return false;
        }

        $inputNames = array_unique(array_filter($inputNames));
        




    }





}