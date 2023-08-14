<?php

use FreedomPay\Helper;

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

CModule::IncludeModule('sale');
CModule::IncludeModule('freedompay.pay');

$application = $GLOBALS['APPLICATION'];

$request = Helper::getRequest();
$settings = Helper::getPaymentSystemSettings($request['PAYMENT_SYSTEM']);

if (!Helper::verifySignature(
    $request['pg_sig'],
    Helper::getScriptName(),
    $request,
    $settings['SECRET_KEY']['VALUE']
)) {
    Helper::makeResponse(
        'error',
        'Signature is not valid'
    );
}

CSaleOrder::Update(
    Helper::getOrderIdFromResult(),
    [
        'STATUS_ID' => $settings['STATUS_REVOKED']['VALUE'],
        'PS_STATUS' => $settings['STATUS_REVOKED']['VALUE'],
    ]
);

Helper::makeResponse(
    'ok',
    'Successful refund'
);
