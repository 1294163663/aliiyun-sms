<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/3
 * Time: 18:33
 */

require_once "./vendor/autoload.php";

$config = [
    'signature' => '签名', // 签名
    'key' => 'access key',
    'secret' => 'access secret',
    'SmsReport' => 'SmsReport id',
    'SmsUp' => 'SmsUp id',
];
$code = 'model';

$res = \Jim\Aliyun\SmsServer::sendSms('15384499806',$code,
    ['place'=>'asd', 'type'=>'das', 'log'=>'log'], $config);
print_r($res);

\Jim\Aliyun\MsgServer::receiveSmsUp(
    function ($message) {
        print_r($message);
        return false;
}, $config);
