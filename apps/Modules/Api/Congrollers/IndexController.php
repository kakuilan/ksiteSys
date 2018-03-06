<?php
/**
 * Copyright (c) 2018 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2018/3/6
 * Time: 21:57
 * Desc: -首页控制器
 */


namespace Apps\Modules\Api\Controllers;

use Kengine\LkkController;


class IndexController extends LkkController {


    /**
     * @title -
     * @desc  -
     */
    public function indexAction(){
        $data = [
            'url' => 'api/index/index',
            'date' => date('Y-m-d H:i:s'),
        ];

        return $this->success($data);
    }


}