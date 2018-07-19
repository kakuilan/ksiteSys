<?php
/**
 * Created by PhpStorm.
 * User: kakuilan@163.com
 * Date: 2018/7/18
 * Time: 16:55
 * Desc: 测试定时器
 */

namespace Apps\Timers;

use Kengine\Server\LkkServer;
use Apps\Models\UserLoginLog;

class Test extends BaseTimer {

    public function __construct(array $vars = []) {
        parent::__construct($vars);
    }


    /**
     * 测试调用静态方法
     */
    public static function staticTest() {
        $date = new \DateTime();
        $time = $date->format('Y-m-d H:i:s.u');
        $mesg = "timer task call static method [$time].\r\n";
        $rows = UserLoginLog::getList([], '*', '', 50);

        $syncRedis = LkkServer::getPoolManager()->get('redis_site')->pop(true);
        $syncRedis->set('staticTest', $time, 600);

        //echo $mesg;
        //getLogger('timer')->info($mesg);
    }


    /**
     * 测试动态方法
     * @return \Generator
     */
    public function dynamicTest() {
        $num = 0;
        for ($i=0;$i<=10;$i++) {
            //$rows = yield UserLoginLog::getListAsync([], '*', '', 50);
            $rows = UserLoginLog::getList([], '*', '', 50);
            $num += count($rows);
        }

        $date = new \DateTime();
        $time = $date->format('Y-m-d H:i:s.u');
        $mesg = "timer task call dynamic method. [$time]-[{$num}] \r\n";

        $asyncRedis = LkkServer::getPoolManager()->get('redis_site')->pop();
        yield $asyncRedis->set('dynamicTest', $time, 600);

        //echo $mesg;
        //getLogger('timer')->info($mesg);
    }




}