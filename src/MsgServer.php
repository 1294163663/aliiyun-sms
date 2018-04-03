<?php
namespace Jim\Aliyun;
ini_set("display_errors", "on");

use Aliyun\TokenGetterForAlicom;
use Aliyun\TokenForAlicom;
use Aliyun\Core\Config;
use AliyunMNS\Exception\MnsException;

// 加载区域结点配置
Config::load();

/**
 * Class MsgDemo
 */
class MsgServer
{

    /**
     * @var TokenGetterForAlicom
     */
    static $tokenGetter = null;

    public static function getTokenGetter($config = []) {

        if ($config == []) {
            $config = require __DIR__ . "/../config.php";
        }

        $accountId = "1943695596114318"; // 此处不需要替换修改!

        $accessKeyId = $config['key']; // AccessKeyId

        $accessKeySecret = $config['secret']; // AccessKeySecret

        if(static::$tokenGetter == null) {
            static::$tokenGetter = new TokenGetterForAlicom(
                $accountId,
                $accessKeyId,
                $accessKeySecret);
        }
        return static::$tokenGetter;
    }

    /**
     * 获取消息
     * @param $config
     * @param string $messageType 消息类型
     * @param string $queueName 在云通信页面开通相应业务消息后，就能在页面上获得对应的queueName<br/>(e.g. Alicom-Queue-xxxxxx-xxxxxReport)
     * @param callable $callback <p>
     * 回调仅接受一个消息参数;
     * <br/>回调返回true，则工具类自动删除已拉取的消息;
     * <br/>回调返回false,消息不删除可以下次获取.
     * <br/>(e.g. function ($message) { return true; }
     * </p>
     */
    public static function receiveMsg($messageType, callable $callback, $config = [])
    {
        if ($config == []) {
            $config = require __DIR__ . "/../config.php";
        }
        $queueName = $config[$messageType];

        $i = 0;
        // 取回执消息失败3次则停止循环拉取
        while ( $i < 3)
        {
            try
            {
                // 取临时token
                $tokenForAlicom = static::getTokenGetter($config)->getTokenByMessageType($messageType, $queueName);

                // 使用MNSClient得到Queue
                $queue = $tokenForAlicom->getClient()->getQueueRef($queueName);

                // 接收消息，并根据实际情况设置超时时间
                $res = $queue->receiveMessage(2);

                // 计算消息体的摘要用作校验
                $bodyMD5 = strtoupper(md5(base64_encode($res->getMessageBody())));

                // 比对摘要，防止消息被截断或发生错误
                if ($bodyMD5 == $res->getMessageBodyMD5())
                {
                    // 执行回调
                    if(call_user_func($callback, json_decode($res->getMessageBody())))
                    {
                        // 当回调返回真值时，删除已接收的信息
                        $receiptHandle = $res->getReceiptHandle();
                        $queue->deleteMessage($receiptHandle);
                    }
                }

                return; // 整个取回执消息流程完成后退出
            }
            catch (MnsException $e)
            {
                $i++;
                echo "ex:{$e->getMnsErrorCode()}\n";
                echo "ReceiveMessage Failed: {$e}\n";
            }
        }
    }

    public static function receiveSmsReport(callable $callback, $config = [])
    {
        self::receiveMsg('SmsReport', $callback, $config);
    }

    public static function receiveSmsUp(callable $callback, $config = [])
    {
        self::receiveMsg('SmsUp', $callback, $config);
    }
}

// 调用示例：

//header('Content-Type: text/plain; charset=utf-8');
//
//echo "消息接口查阅短信状态报告返回结果:\n";
//MsgServer::receiveMsg(
//    // 消息类型，SmsReport: 短信状态报告
//    "SmsReport",
//
//    // 在云通信页面开通相应业务消息后，就能在页面上获得对应的queueName
//    "Alicom-Queue-1447497849932068-SmsReport",
//
//    /**
//     * 回调
//     * @param stdClass $message 消息数据
//     * @return bool 返回true，则工具类自动删除已拉取的消息。返回false，消息不删除可以下次获取
//     */
//    function ($message) {
//        print_r($message);
//        return false;
//    }
//);
//
//
//echo "消息接口查阅短信服务上行返回结果:\n";
//MsgServer::receiveMsg(
//    // 消息类型，SmsUp: 短信服务上行
//    "SmsUp",
//
//    // 在云通信页面开通相应业务消息后，就能在页面上获得对应的queueName
//    "Alicom-Queue-1447497849932068-SmsUp",
//
//    /**
//     * 回调
//     * @param stdClass $message 消息数据
//     * @return bool 返回true，则工具类自动删除已拉取的消息。返回false，消息不删除可以下次获取
//     */
//    function ($message) {
//        print_r($message);
//        return false;
//    }
//);