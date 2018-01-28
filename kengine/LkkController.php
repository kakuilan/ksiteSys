<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/9/17
 * Time: 12:30
 * Desc: -lkk 控制器类
 */


namespace Kengine;

use Lkk\Helpers\ArrayHelper;
use Lkk\Phalwoo\Phalcon\Http\Response as PwResponse;
use Lkk\Phalwoo\Phalcon\Mvc\Controller;
use Lkk\Phalwoo\Server\SwooleServer;
use Phalcon\Mvc\View;

class LkkController extends Controller {

    //当前站点ID
    public $siteId = 0;

    //头部SEO
    public $headerSeo;


    //登录UID
    protected $uid;


    /**
     * 初始化
     */
    public function initialize() {
        $this->siteId = getSiteId();

        //TODO
    }


    /**
     * 获取登录用户UID
     * @return mixed
     */
    public function getLoginUid() {
        if(is_null($this->uid)) {
            //TODO
        }

        return $this->uid;
    }


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
        $data['sitetitle'] = $seo->title . ' ' .$data['title'];
        $data['keywords'] = $seo->keywords;
        $data['desc'] = $seo->desc;

        $viewpath =  $this->view->getViewsDir();
        $this->view->setViewsDir(dirname($viewpath));

        //视图变量
        $this->view->setVars($data);
        $this->view->pick($viewfile);
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
        $output = json_encode($output);

        $get = $this->request->getQuery();
        if(empty($callback)) $callback = isset($get['callback']) ? trim($get['callback']) : '';
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



}