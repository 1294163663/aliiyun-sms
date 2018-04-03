<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/3
 * Time: 18:42
 */

return [
    'signature' => env('ALIYUN_SMS_SIGN_NAME'), // 签名
    'key' => env('ALIYUN_SMS_AK'),
    'secret' => env('ALIYUN_SMS_AS'),
    'queue' => env('ALIYUN_SMS_QUEUE'),
];