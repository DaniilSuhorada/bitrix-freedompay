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

$application->IncludeComponent(
    'bitrix:sale.personal.order.detail',
    '',
    [
        'PATH_TO_LIST' => '/personal/orders/',
        'PATH_TO_CANCEL' => '/personal/cancel/' . $request['pg_order_id'],
        'PATH_TO_PAYMENT' => 'payment.php',
        'ID'              => Helper::getOrderIdFromResult(),
        'SET_TITLE'       => 'Y'
    ]
);

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
