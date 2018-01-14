<?php
/**
 * Copyright (c) 2018 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2018/1/14
 * Time: 15:30
 * Desc: -通知服务类
 */


namespace Apps\Services;

use Apps\Models\Message;
use Apps\Models\MsgqueNames;
use Apps\Models\MsgqueDetail;
use Apps\Models\MsgqueSendLog;

class NotifyService extends ServiceBase {

    private static $queueIds = [];

    /**
     * 根据队列名获取队列ID
     * @param string $queueName
     * @return int|mixed
     */
    public function getQueIdByname($queueName='') {
        if(empty($queueName)) return 0;
        if(!isset(self::$queueIds[$queueName])) {
            $queInfo = MsgqueNames::getRow(['queue_name'=>$queueName]);
            self::$queueIds[$queueName] = $queInfo ? $queInfo->id : 0;
        }

        return (int)self::$queueIds[$queueName];
    }


    /**
     * 获取详细消息的key
     * @param array $item
     * @return string
     */
    public static function getMqDetailKey($item=[]) {
        $key = md5(json_encode($item));
        return $key;
    }


    /**
     * 根据数据获取数据库的消息记录
     * @param array $item
     * @return mixed
     */
    public function getMqDetail($item=[]) {
        $res = false;
        if(empty($item)) {
            $this->setError('数据不能为空');
            return $res;
        }

        $msgKey = self::getMqDetailKey($item);
        $res = MsgqueDetail::getRow(['msg_key'=>$msgKey], 'id,que_id,status,exec_times,update_time,msg_type');

        return $res;
    }


    /**
     * 解析获取消息里的接收者
     * @param array $item 消息
     * @return int|string
     */
    public static function parseReceiver($item=[]) {
        $msgType = strtolower($item['type']);
        switch ($msgType) {
            case 'msg' :
                $receiver = isset($item['data']['uid']) ? intval($item['data']['uid']) : 0;
                break;
            case 'sms' :
                $receiver = isset($item['data']['mobile']) ? trim($item['data']['mobile']) : 0;
                break;
            case 'mail' :
                $receiver = isset($item['data']['email']) ? trim($item['data']['email']) : 0;
                break;
            case 'wechat' :
                $receiver = isset($item['data']['openid']) ? trim($item['data']['openid']) : 0;
                break;
            default :
                $receiver = 0;
                break;
        }

        return $receiver;
    }



    /**
     * 新增消息记录到数据库
     * @param array $item 消息
     * @param int $queId 队列ID
     * @return bool
     */
    public function addMqDetail($item=[], $queId=0) {
        $res = false;
        if(empty($item)) {
            $this->setError('数据不能为空');
            return $res;
        }

        $msg_type = strtolower($item['type']);
        $receiver = self::parseReceiver($item);

        $now = time();
        $msg_data = json_encode($item);
        $msg_key = md5($msg_data);
        $data = [
            'que_id' => $queId,
            'status' => '0',
            'exec_times' => '0',
            'create_time' => $now,
            'update_time' => $now,
            'msg_type' => $msg_type,
            'receiver' => $receiver,
            'msg_key' => $msg_key,
            'msg_data' => $msg_data,
            'remark' => '',
        ];
        $res = MsgqueDetail::addData($data);
        return $res;
    }


    /**
     * 新增发送日志
     * @param int $mid
     * @param int|bool $status
     * @param string $msgType
     * @param string $receiver
     * @param int $sendTime
     * @param int $responseTime
     * @param mixed $sendContent
     * @param mixed $responseContent
     * @return mixed
     */
    public function addMqSendLog($mid=0, $status=0, $msgType='default', $receiver='', $sendTime=0, $responseTime=0, $sendContent='', $responseContent='') {
        $status = empty($status) ? -1 : 1;
        $now = time();
        if(empty($sendTime)) $sendTime = $now;
        if(empty($responseTime)) $responseTime = $now;
        if(!is_string($sendContent)) $sendContent = json_encode((array)$sendContent);
        if(!is_string($responseContent)) $responseContent = json_encode((array)$responseContent);
        $data = [
            'mid' => $mid,
            'status' => $status,
            'msg_type' => $msgType,
            'receiver' => $receiver,
            'send_time' => $sendTime,
            'response_time' => $responseTime,
            'send_content' => $sendContent,
            'response_content' => $responseContent,
        ];

        $res = MsgqueSendLog::addData($data);
        return $res;
    }



    /**
     * 发送站内信
     * @param array $data
     * @return bool
     */
    public function sendMsg($data=[]) {
        $res = false;
        if(empty($data)) {
            $this->setError('数据不能为空');
            return $res;
        }

        $now = time();
        if(!isset($data['create_time'])) $data['create_time'] = $now;
        if(!isset($data['update_time'])) $data['update_time'] = $now;
        $res = Message::addData($data);

        return $res;
    }


    /**
     * 发送短信
     * @param array $data
     * @return bool
     */
    public function sendSms($data=[]) {
        $res = false;
        if(empty($data)) {
            $this->setError('数据不能为空');
            return $res;
        }


        return $res;
    }


    /**
     * 发送邮件
     * @param array $data
     * @return bool
     */
    public function sendMail($data=[]) {
        $res = false;
        if(empty($data)) {
            $this->setError('数据不能为空');
            return $res;
        }

        //TODO
        return $res;
    }


    /**
     * 发送微信消息
     * @param array $data
     * @return bool
     */
    public function sendWechat($data=[]) {
        $res = false;
        if(empty($data)) {
            $this->setError('数据不能为空');
            return $res;
        }

        //TODO
        return $res;
    }


}