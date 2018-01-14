<?php
/**
 * Copyright (c) 2018 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2018/1/14
 * Time: 15:55
 * Desc: -
 */

namespace Apps\Timers;

use Apps\Models\MsgqueNames;
use Apps\Models\MsgqueDetail;
use Apps\Models\MsgqueSendLog;
use Apps\Services\NotifyService;
use Apps\Services\RedisQueueService;
use Lkk\LkkMacAddress;

class MessageQueue extends BaseTimer {


    /**
     * 失败的消息(全部)重新加入队列
     * @return mixed
     */
    public function failMsgReaddQueue() {
        $params = [
            'redisConf' => RedisQueueService::getDefultRedisCnf(),
            'transTime' => 10,
        ];
        $queue = new RedisQueueService($params);

        $date = date('Ymd H:i:s');
        $addr = LkkMacAddress::getMacAddress();
        $readdRes = $queue->loopTransQueue($addr);
        if($readdRes) {
            print_r("[READD MSG] server:[{$addr}] time:{$date} readd transfer msg Total:{$readdRes}\n");
        }else{
            $err = $queue->error();
            print_r("[READD MSG] server:[{$addr}] time:{$date} readd transfer msg Fail:{$err}\n");
        }

        return $readdRes;
    }


    /**
     * 拉取通知消息并处理
     */
    public function pullNotifyMsgHandling() {
        $params = [
            'redisConf' => RedisQueueService::getDefultRedisCnf(),
            'transTime' => 10,
        ];
        $queue = new RedisQueueService($params);
        $queue->newQueue(RedisQueueService::APP_NOTIFY_QUEUE_NAME);
        $len = $queue->len();
        if($len<=0) return false;
        $logger = getLogger('pullmsg');

        //拉取消息
        $allNum = $sucNum = $faiNum = 0;
        $notifyService = new NotifyService();
        $date = date('Ymd H:i:s');
        $addr = LkkMacAddress::getMacAddress();
        $queId = $notifyService->getQueIdByname(RedisQueueService::APP_NOTIFY_QUEUE_NAME);
        print_r("[PULL MSG] server:[{$addr}] time:{$date} queId:{$queId} queue size:{$len}.\n");

        while ($item = $queue->pop()) {
            $allNum++;
            $item = (array)$item;
            $msg_key = NotifyService::getMqDetailKey($item);
            $logger->info('receive a msg: [msg_key:'.$msg_key.']', $item);
            if(!isset($item['data']) || empty($item['data'])) {
                $logger->info('消息数据格式错误', $item);
                //丢弃
                $queue->confirm($item, true);
                continue;
            }

            $handlRes = $this->sendItem($item, $notifyService, $queue, $queId);
            if($handlRes) {
                $sucNum++;
                $logger->info('msg process success. [msg_key:'.$msg_key.']');
            }else{
                $faiNum++;
                $logger->info('msg process fail. [msg_key:'.$msg_key.']');
            }
        }

        print_r("[PULL MSG] server:[{$addr}] time:{$date} queId:{$queId} received Total:{$allNum} Success:{$sucNum} Fail:{$faiNum}\n");
        return true;
    }



    /**
     * 发送消息
     * @param $item
     * @param $notifyService
     * @param $queue
     * @param $queId
     * @return bool|\Generator
     */
    private function sendItem($item, $notifyService, $queue, $queId=0) {
        $lock = $queue->getItemProcessLock($item);
        if(!$lock) {
            return false;
        }

        $mqDetail = $notifyService->getMqDetail($item);
        $startTime = time();
        if($mqDetail) {
            $resendTime = min(300, ($mqDetail->exec_times +1)*5);
            if($mqDetail['status']==1 || $mqDetail->exec_times >=50) { //已发送或发送次数>50
                $queue->confirm($item, true, RedisQueueService::APP_NOTIFY_QUEUE_NAME);
                return true;
            }elseif(($startTime-$mqDetail->update_time) < $resendTime){ //重发时间限制
                $queue->confirm($item, false, RedisQueueService::APP_NOTIFY_QUEUE_NAME);
                return false;
            }
        }

        $detailId = empty($mqDetail) ? ($notifyService->addMqDetail($item, $queId)) : $mqDetail->id;
        $execTimes = empty($mqDetail) ? 0 :
            ($mqDetail->exec_times >RedisQueueService::APP_NOTIFY_RESEND_TIMES ? RedisQueueService::APP_NOTIFY_RESEND_TIMES : $mqDetail->exec_times);

        $type = strtolower($item['type']);
        $receiver = NotifyService::parseReceiver($item);
        switch ($type) {
            case 'msg' : //站内信息
                $handlRes = $notifyService->sendMsg($item['data']);
                break;
            case 'sms' : //短信
                $handlRes = $notifyService->sendSms($item['data']);
                break;
            case 'mail' : //邮件
                $handlRes = $notifyService->sendMail($item['data']);
                break;
            case 'wechat'://微信消息
                $handlRes = $notifyService->sendWechat($item['data']);
                break;
            default :
                $handlRes = false;
                break;
        }

        $endTime = time();
        $notifyService->addMqSendLog($detailId, $handlRes, $type, $receiver, $startTime, $endTime, $item['data'], $notifyService->error);
        $detailData = [
            'status' => empty($handlRes) ? -1 : 1,
            'exec_times' => $execTimes+1,
            'update_time' => $endTime,
        ];
        MsgqueDetail::upData($detailData, ['id'=>$detailId]);

        $queue->confirm($item, $handlRes, RedisQueueService::APP_NOTIFY_QUEUE_NAME);
        $queue->unlockItemProcess($item);

        return $handlRes;
    }



}