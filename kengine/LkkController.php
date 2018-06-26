<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/9/17
 * Time: 12:30
 * Desc: -lkk 控制器类
 */


namespace Kengine;

use Kengine\LkkCmponent;
use Lkk\Helpers\ArrayHelper;
use Lkk\Helpers\EncryptHelper;
use Lkk\Helpers\StringHelper;
use Lkk\Phalwoo\Phalcon\Http\Response as PwResponse;
use Lkk\Phalwoo\Phalcon\Mvc\Controller;
use Lkk\Phalwoo\Server\SwooleServer;
use Phalcon\Mvc\View;

abstract class LkkController extends Controller {

    //当前站点ID
    public $siteId = 0;


    //登录UID
    protected $uid;


    //动作ID
    protected $actionId;


    //是否API
    public $isApi;


    //头部SEO
    public $headerSeo;



    /**
     * 初始化
     */
    public function initialize() {
        $this->siteId = getSiteId();

        //TODO
    }


    /**
     * 获取动作ID
     * @return mixed
     */
    abstract public function getActionId();


    /**
     * 获取登录用户UID
     * @return mixed
     */
    abstract public function getLoginUid();


    /**
     * 记录请求日志
     * @param mixed $out 输出的结果
     *
     * @return mixed
     */
    abstract public function addAccessLog($out);


    /**
     * 设置头部SEO
     * @param string $title
     * @param string $keywords
     * @param string $desc
     */
    public function setHeaderSeo($title='', $keywords='', $desc='') {
        $arr = [
            'title' => $title,
            'keywords' => $keywords,
            'desc' => $desc,
        ];

        $this->headerSeo = (object) $arr;
    }


    /**
     * 获取头部SEO
     * @return mixed
     */
    public function getHeaderSeo() {
        if(is_null($this->headerSeo)) {
            $this->setHeaderSeo('', '', '');
        }

        return $this->headerSeo;
    }


    /**
     * 提示警告页面
     * @param mixed  $msg 信息数组
     * @param string $type 提示类型: success-成功; info-一般; warning-警告; danger-危险
     * @param string $url 要跳转的url
     * @param int    $time 停留多少秒然后跳转
     * @param string $viewfile 视图模板文件
     */
    public function alert($msg, $type='warning', $url='', $time=5, $viewfile='public/alert') {
        $data = [
            'url' => $url,
            'time' => intval($time),
        ];

        if(is_array($msg)) {
            $data = array_merge($data, $msg);
        }else{
            $data['msg'] = $msg;
        }

        !isset($data['title']) && $data['title'] = '提示';
        !isset($data['msg']) && $data['msg'] = '发生错误';

        $type = strtolower($type);
        if(!ArrayHelper::dstrpos($type, array('success','info','warning','danger')) ) {
            $type = 'warning';
        }
        $data['type'] = $type;

        //seo
        $seo = $this->getHeaderSeo();
        $data['title'] = $seo->title . ' ' .$data['title'];
        $data['keywords'] = $seo->keywords;
        $data['desc'] = $seo->desc;

        $viewpath =  $this->view->getViewsDir();
        $this->view->setViewsDir(dirname($viewpath));

        //视图变量
        $this->view->setVars($data);
        $this->view->pick($viewfile);
    }


    /**
     * 递归处理数组中的数值元素,转换为字符串
     * @param array $data
     * @return array|object
     */
    public static function reParseArrayNumToStr($data = []) {
        if (is_object($data) && empty((array)$data)) { //空对象
            return $data;
        }elseif (empty($data)) {
            return [];
        }

        if(!is_array($data)) $data = (array)$data;
        foreach ($data as $k=> &$v) {
            if(is_scalar($v)) { //标量
                if(is_numeric($v)) {
                    $v = strval($v);
                }
            }elseif (is_object($v) || is_array($v)){
                $v = self::reParseArrayNumToStr($v);
            }else{
                $v = trim(strval($v));
            }
        }

        return $data;
    }


    /**
     * 输出json/jsonp
     * @param array  $res 要输出的结果数组
     * @param string $callback 是否有js回调
     */
    public function json($res=[], $callback='') {
        $response = $this->response;
        $response->setHeader("Content-Type", "application/json; charset=UTF-8");
        $response->setHeader("Access-Control-Allow-Origin", "*");
        $response->setHeader("Access-Control-Allow-Methods", "POST, GET, OPTIONS");
        $response->setHeader("Access-Control-Allow-Headers", "x-requested-with,content-type");
        $response->setHeader("Access-Control-Allow-Credentials", "true");

        $this->setJsonRes($res);
        $this->setJsonStatus(true);
        $output = $this->getJsonRes();
        $output = self::reParseArrayNumToStr($output);
        $output = json_encode($output);

        if(empty($callback)) $callback = $this->getRequest('callback', '');
        if($callback) $output = "{$callback}($output)";

        //设置输出
        $response->setContent($output);

        return $output;
    }


    /**
     * ajax成功输出json
     * @param array $data 数据
     * @param string $msg 消息
     * @param string $callback js回调
     * @return array|string
     */
    public function success($data=[], $msg='', $callback='') {
        if(!empty($data)) {
            if(is_array($data) || is_object($data)) {
                $this->jsonRes['data'] = array_merge($this->jsonRes['data'], (array)$data);
            }else{
                $this->jsonRes['data'] = strval($data);
            }
        }

        $this->jsonRes['status'] = true;
        $this->jsonRes['code'] = 200;
        $this->jsonRes['msg'] = $msg ? $msg : 'success';

        return $this->json($this->jsonRes, $callback);
    }


    /**
     * ajax失败输出json
     * @param string|array $code 错误码/错误消息
     * @param array $langParams 错误码中的变量,键值对,参考data\doc\error_code.php
     * @param array $data 数据
     * @param string $callback js回调
     * @return array|string
     */
    public function fail($code='', $langParams=[], $data=[], $callback='') {
        if(!empty($data)) {
            if(is_array($data) || is_object($data)) {
                $this->jsonRes['data'] = array_merge($this->jsonRes['data'], (array)$data);
            }else{
                $this->jsonRes['data'] = strval($data);
            }
        }

        if(is_array($code)) {
            $msg = end($code);
            $code = reset($code);
        }else{
            $msg = lang($code, $langParams);
        }

        $codeNo = ($code!=$msg && is_numeric($code)) ? $code : 400;

        $this->jsonRes['status'] = false;
        $this->jsonRes['msg'] = $msg ? $msg : 'error';
        $this->jsonRes['code'] = $codeNo;

        return $this->json($this->jsonRes, $callback);
    }



    /**
     * 递归去空格
     * @param array $data
     * @return array|string
     */
    public static function recursionTrim($data = []) {
        if(empty($data)) return $data;
        if(is_array($data)) {
            foreach ($data as &$item) {
                $item = self::recursionTrim($item);
            }
        }elseif (is_string($data)) {
            $data = trim($data);
        }

        return $data;
    }


    /**
     * 获取$_REQUEST参数
     * @param string $name 参数名,为空则取数组
     * @param null   $default 默认值
     * @param bool   $xssClean 是否过滤xss
     *
     * @return array|string
     */
    public function getRequest($name='', $default=null, $xssClean = true) {
        if($name=='') {
            $data = self::recursionTrim($this->request->get());
            if($xssClean && $data) {
                return LkkCmponent::xssClean()->xss_clean($data);
            }

            return $data;
        }

        $val = self::recursionTrim($this->request->get($name, null, $default));
        if($xssClean && $val && $val!=$default) {
            $val = LkkCmponent::xssClean()->xss_clean($val);
        }

        return $val;
    }



    /**
     * 获取$_GET参数
     * @param string $name 参数名,为空则取数组
     * @param null   $default 默认值
     * @param bool   $xssClean 是否过滤xss
     *
     * @return array|string
     */
    public function getGet($name='', $default=null, $xssClean = true) {
        if($name=='') {
            $data = self::recursionTrim($this->request->getQuery(''));
            if($xssClean && $data) {
                return LkkCmponent::xssClean()->xss_clean($data);
            }

            return $data;
        }

        $val = self::recursionTrim($this->request->getQuery($name, null, $default));
        if($xssClean && $val && $val!=$default) {
            $val = LkkCmponent::xssClean()->xss_clean($val);
        }

        return $val;
    }



    /**
     * 获取$_POST参数
     * @param string $name 参数名,为空则取数组
     * @param null   $default 默认值
     * @param bool   $xssClean 是否过滤xss
     *
     * @return array|string
     */
    public function getPost($name='', $default=null, $xssClean = true) {
        if($name=='') {
            $data = self::recursionTrim($this->request->getPost(''));
            if($xssClean && $data) {
                return LkkCmponent::xssClean()->xss_clean($data);
            }

            return $data;
        }

        $val = self::recursionTrim($this->request->getPost($name, null, $default));
        if($xssClean && $val && $val!=$default) {
            $val = LkkCmponent::xssClean()->xss_clean($val);
        }

        return $val;
    }



    /**
     * 生成CsrfToken(Form表单提交使用)
     * @param string $code 原始码
     * @param int $expiry
     * @return string
     */
    public function makeCsrfToken($code='', $expiry = 600) {
        if(empty($code)) $code = StringHelper::randString(9);
        $key = getConf('ctypt','key');
        $encode = EncryptHelper::ucAuthcode($code, 'ENCODE', $key, $expiry);

        return EncryptHelper::base64urlEncode($encode);
    }



    /**
     * 验证CsrfToken
     * @param string $encode 加密码
     * @param string $origin 原始码
     * @return bool
     */
    public function validateCsrfToken($encode='', $origin='') {
        if(empty($encode)) $encode = $this->getRequest(getConf('site','csrfToken'));
        if(empty($encode)) return false;

        $encode = EncryptHelper::base64urlDecode($encode);
        $key = getConf('ctypt','key');

        $code = EncryptHelper::ucAuthcode($encode, 'DECODE', $key);
        if(empty($code)) return false;

        if(!empty($origin) && $origin!=$code) return false;

        return true;
    }


    /**
     * 获取accessToken
     * @return array|string
     */
    public function getAccessToken() {
        $tokenName = getConf('login', 'tokenName');
        $tokenValu = $this->getRequest($tokenName);
        return $tokenValu;
    }




}