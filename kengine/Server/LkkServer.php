<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/8/17
 * Time: 22:51
 * Desc: -
 */


namespace Kengine\Server;

use Lkk\Phalwoo\Server\SwooleServer;

class LkkServer extends SwooleServer {

    public function __construct(array $vars = []) {
        parent::__construct($vars);
    }


    public function onRequest($request, $response) {
        parent::onRequest($request, $response);

        try {
            $output = date('Y-m-d H:i:s') . ' Hello World.';
        } catch (\Exception $e) {
            $output = $e->getMessage();
        }
        $response->end($output);

        $this->afterResponse($request, $response);
    }


}