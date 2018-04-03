<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/3
 * Time: 18:33
 */

require_once "./vendor/autoload.php";

$config = [
    'signature' => '飞宇智能', // 签名
    'key' => 'LTAIyc6DuMRmXkwl',
    'secret' => 'OPeIWDQx8g4iFvz6DrtZdON3HfZyit',
    'SmsReport' => 'Alicom-Queue-1447497849932068-SmsReport',
    'SmsUp' => 'Alicom-Queue-1447497849932068-SmsUp',
];
$code = 'SMS_129741677';

$res = \Jim\Aliyun\SmsServer::sendSms('15384499806',$code,
    ['place'=>'asd', 'type'=>'das', 'log'=>'log'], $config);
print_r($res);

\Jim\Aliyun\MsgServer::receiveSmsUp(
    function ($message) {
        print_r($message);
        return false;
}, $config);
