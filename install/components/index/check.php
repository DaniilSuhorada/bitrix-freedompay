<?php

use Bitrix\Sale\Order;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

CModule::IncludeModule("sale");
CModule::IncludeModule("freedompay.pay");

$strScriptName = FreedomPaySignature::getOurScriptName();
$arrRequest = FreedomPayIO::getRequest();
$objShop = CSalePaySystemAction::GetList('', array("PAY_SYSTEM_ID" => $arrRequest['PAYMENT_SYSTEM']));
$arrShop = $objShop->Fetch();

if (!empty($arrShop)) {
    $arrShopParams = unserialize($arrShop['PARAMS']);
} else {
    FreedomPayIO::makeResponse(
        $strScriptName,
        '',
        'error',
        'Please re-configure the module FreedomPay in Bitrix CMS. The payment system should have a name ' . $arrRequest['PAYMENT_SYSTEM']
    );
}

$strSecretKey = $arrShopParams['SECRET_KEY']['VALUE'];

$strSalt = $arrRequest["pg_salt"];

$nOrderAmount = $arrRequest["pg_amount"];

if ($arrShopParams['ORDER_ID_TYPE']['VALUE'] === 'ORDER_NUMBER') {
    $nOrderId = Order::loadByAccountNumber($arrRequest['pg_order_id'])->getId();
} else {
    $nOrderId = (int)$arrRequest['pg_order_id'];
}

/*
 * Signature check
 */
if (!FreedomPaySignature::check($arrRequest['pg_sig'], $strScriptName, $arrRequest, $strSecretKey)) {
    FreedomPayIO::makeResponse(
        $strScriptName,
        $strSecretKey,
        'error',
        'signature is not valid',
        $strSalt
    );
}

if (!($arrOrder = CSaleOrder::GetByID($nOrderId))) {
    FreedomPayIO::makeResponse(
        $strScriptName,
        $strSecretKey,
        'error',
        'order not found',
        $strSalt
    );
}

if ($nOrderAmount != $arrOrder['PRICE']) {
    FreedomPayIO::makeResponse(
        $strScriptName,
        $strSecretKey,
        'error',
        'amount is not correct',
        $strSalt
    );
}

if ($arrOrder['PAYED'] === "Y") {
    FreedomPayIO::makeResponse(
        $strScriptName,
        $strSecretKey,
        "ok",
        "Order already payed",
        $strSalt
    );
}

if ($arrOrder['CANCELED'] === "Y") {
    FreedomPayIO::makeResponse(
        $strScriptName,
        $strSecretKey,
        'error',
        'Order canceled',
        $strSalt
    );
}

FreedomPayIO::makeResponse(
    $strScriptName,
    $strSecretKey,
    "ok",
    "",
    $strSalt
);
