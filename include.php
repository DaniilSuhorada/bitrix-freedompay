<?php

use FreedomPay\ApiClient;
use FreedomPay\Helper;
use FreedomPay\Payment;
use FreedomPay\Result;

$libPath = '/bitrix/modules/freedompay.pay/lib/lib/FreedomPay';

$classes = [
    ApiClient::class => "$libPath/ApiClient.php",
    Helper::class    => "$libPath/Helper.php",
    Result::class => "$libPath/Result.php",
    Payment::class => "$libPath/Payment.php",
];

CModule::AddAutoloadClasses('', $classes);
