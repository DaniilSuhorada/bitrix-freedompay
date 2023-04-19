<?php

use Bitrix\Sale\Order;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

CModule::IncludeModule("sale");
CModule::IncludeModule("freedompay.pay");

/*
 * Configuration and parameters
 */
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
$nOrderId = (int)$arrRequest["pg_order_id"];

$strStatusPaid = $arrShopParams['STATUS_PAID']['VALUE'];
$strStatusFailed = $arrShopParams['STATUS_FAILED']['VALUE'];

if ($arrShopParams['ORDER_ID_TYPE']['VALUE'] === 'ORDER_NUMBER') {
    $nOrderId = Order::loadByAccountNumber($arrRequest['pg_order_id'])->getId();
} else {
    $nOrderId = (int)$arrRequest['pg_order_id'];
}

/*
 * Signature
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

if (CSalePaySystemAction::GetParamValue('ORDER_ID_TYPE') === 'ORDER_NUMBER') {
    $order = Order::loadByAccountNumber($nOrderId);
} else {
    $order = Order::load($nOrderId);
}

$paymentsCollection = $order->getPaymentCollection();

foreach ($paymentsCollection as $payment) {
    if ($payment->getField('PAY_SYSTEM_NAME') === 'FreedomPay') {
        $currentPayment = $payment;

        break;
    }
}

if ($nOrderAmount != $currentPayment->getField('SUM')) {
    FreedomPayIO::makeResponse(
        $strScriptName,
        $strSecretKey,
        'error',
        'amount is not correct',
        $strSalt
    );
}

if ($arrRequest["pg_result"] == 1) {
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
        CSaleOrder::Update($nOrderId, array(
            'STATUS_ID'        => $strStatusFailed,
            'PS_STATUS'        => $strStatusFailed,
            'PS_STATUS_CODE'   => "0",
            'PS_SUM'           => $arrRequest['pg_amount'],
            'PS_CURRENCY'      => $arrRequest['pg_currency'],
            'PS_RESPONSE_DATE' => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG))),
        ));

        FreedomPayIO::makeResponse($strScriptName, $strSecretKey, 'rejected', 'Order canceled', $strSalt);

        return false;
    }

    $currentPayment->setPaid('Y');

    if ($order->getPrice() === $paymentsCollection->getPaidSum()) {
        if (!CSaleOrder::PayOrder($nOrderId, "Y")) {
            FreedomPayIO::makeResponse($strScriptName, $strSecretKey, "error", "Order can\'t be payed", $strSalt);
        } else {
            CSaleOrder::Update($nOrderId, array(
                'SUM_PAID'  => $paymentsCollection->getPaidSum(),
                'STATUS_ID' => $strStatusPaid
            ));

            $currentPayment->delete();

            FreedomPayIO::makeResponse($strScriptName, $strSecretKey, "ok", "Order payed", $strSalt);
        }
    }

    CSaleOrder::Update($nOrderId, array(
        'SUM_PAID' => $paymentsCollection->getPaidSum(),
    ));

    $paymentsCollection->save();

    FreedomPayIO::makeResponse($strScriptName, $strSecretKey, "ok", 'FreedomPay payment paid', $strSalt);
}

/*
 * Order cancel
 */
else {
    if ($arrOrder['CANCELED'] === "Y") {
        FreedomPayIO::makeResponse(
            $strScriptName,
            $strSecretKey,
            'ok',
            'Order alredy canceled',
            $strSalt
        );
    }

    if ($arrOrder['PAYED'] === "Y") {
        FreedomPayIO::makeResponse(
            $strScriptName,
            $strSecretKey,
            "error",
            "Order already paid",
            $strSalt
        );
    }

    CSaleOrder::Update($nOrderId, array(
        'STATUS_ID'        => $strStatusFailed,
        'PS_STATUS'        => $strStatusFailed,
        'PS_STATUS_CODE'   => "1",
        'PS_SUM'           => $arrRequest['pg_amount'],
        'PS_CURRENCY'      => $arrRequest['pg_currency'],
        'PS_RESPONSE_DATE' => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG))),
    ));

    FreedomPayIO::makeResponse(
        $strScriptName,
        $strSecretKey,
        "ok",
        "Payment failed",
        $strSalt
    );
}
