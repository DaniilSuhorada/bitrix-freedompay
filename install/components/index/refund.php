<?php

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

CModule::IncludeModule("sale");
CModule::IncludeModule("freedompay.pay");

$strScriptName = freedompaySignature::getOurScriptName();

$arrRequest = freedompayIO::getRequest();

$objShop = CSalePaySystemAction::GetList('', array("PAY_SYSTEM_ID" => $arrRequest['PAYMENT_SYSTEM']));
$arrShop = $objShop->Fetch();

if (!empty($arrShop)) {
    $arrShopParams = unserialize($arrShop['PARAMS']);
} else {
    freedompayIO::makeResponse(
        $strScriptName,
        '',
        'error',
        'Please re-configure the module Freedom Pay in Bitrix CMS. The payment system should have a name ' . $arrRequest['PAYMENT_SYSTEM']
    );
}


$strSecretKey = $arrShopParams['SECRET_KEY']['VALUE'];

if ($arrShopParams['ORDER_ID_TYPE']['VALUE'] === 'ORDER_NUMBER') {
    $nOrderId = \Bitrix\Sale\Order::loadByAccountNumber($arrRequest['pg_order_id'])->getId();
} else {
    $nOrderId = (int)$arrRequest['pg_order_id'];
}

if (!($arrOrder = CSaleOrder::GetByID($nOrderId))) {
    freedompayIO::makeResponse(
        $strScriptName,
        $strSecretKey,
        'error',
        'order not found',
        $strSalt
    );
}

if (!freedompaySignature::check($arrRequest['pg_sig'], $strScriptName, $arrRequest, $strSecretKey)) {
    freedompayIO::makeResponse(
        $strScriptName,
        $strSecretKey,
        'error',
        'signature is not valid',
        $strSalt
    );
}

$strStatusRevoked = $arrShopParams['STATUS_REVOKED']['VALUE'];

CSaleOrder::Update($nOrderId, array(
    'STATUS_ID' => $strStatusRevoked,
    'PS_STATUS' => $strStatusRevoked,
));

$arrRequest['pg_salt'] = uniqid();
$arrRequest['pg_status'] = 'ok';

$arrRequest['pg_sig'] = freedompaySignature::make('refund.php', $arrRequest, $strSecretKey);

freedompayIO::makeResponse($strScriptName, $strSecretKey, "ok", "", $arrRequest['pg_salt']);
