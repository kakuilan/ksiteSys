<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/9/17
 * Time: 12:30
 * Desc: -lkk 控制器类
 */


namespace Kengine;

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\View;
//use Phalcon\Http\Response;
use Lkk\Phalwoo\Phalcon\Http\Response as PwResponse;
use Lkk\Helpers\ArrayHelper;

class LkkController extends Controller {

    //当前站点ID
    public $siteId = 0;

    //头部SEO
    public $headerSeo;

    //要输出的json内容
    public $jsonRes = [
        'status' => false, //状态
        'code' => 200, //状态码
        'data' => [], //数据
        'msg' => '', //提示信息
    ];


    /**
     * 初始化
     */
    public function initialize(){
        $this->siteId = getSiteId();

        //TODO
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

        //包含debug调试信息
        $debug = '';
        if(getConf('common')['debug']) {
            $debug = ob_get_contents();
            ob_end_clean();
        }

        if(!empty($res)) {
            $output = array_merge($this->jsonRes, $res);
        }else{
            $output = $this->jsonRes;
        }

        $output = json_encode($output);

        if(empty($callback)) $callback = isset($_GET['callback']) ? trim($_GET['callback']) : '';
        if($callback) $output = "{$callback}($output)";

        //取消视图模板
        $this->view->setRenderLevel(View::LEVEL_NO_RENDER);
        $this->view->disable();

        $output = $debug . $output;

        //设置输出
        $response->setContent($output);
        // 返回响应到客户端
        //$response->send();

        return $output;
    }


    /**
     * ajax成功输出json
     * @param mixed  $msg
     * @param string $callback
     */
    public function success($msg='success', $callback='') {
        $data = $this->jsonRes;

        if(is_array($msg)) {
            $data = array_merge($data, $msg);
        }else{
            $data['msg'] = $msg;
        }
        $data['status'] = true;

        return $this->json($data, $callback);
    }


    /**
     * ajax失败输出json
     * @param mixed  $msg
     * @param string $callback
     */
    public function fail($msg='fail', $callback='') {
        $data = $this->jsonRes;

        if(is_array($msg)) {
            $data = array_merge($data, $msg);
        }else{
            $data['msg'] = $msg;
        }
        $data['status'] = false;

        return $this->json($data, $callback);
    }




}