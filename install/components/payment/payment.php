<?php

use Bitrix\Sale\Order;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

include(GetLangFileName(__DIR__ . "/", "/payment.php"));

CModule::IncludeModule("sale");
CModule::IncludeModule("freedompay.pay");

$useNew = GetMessage("USE_NEW");
$newEmail = GetMessage("NEW_EMAIL");
$useNewEmail = GetMessage("USE_NEW_EMAIL");

$arrRequestMethods = array("POST", "GET");
$arrUserRedirectMethods = array("POST", "GET", "AUTOPOST", "AUTOGET");

$userID = $USER->GetID();
$rsUser = CUser::GetByID($userID);
$arrUser = $rsUser->Fetch();

$strCustomerEmail = $arrUser['EMAIL'];
$strCustomerPhone = FreedomPayIO::checkAndConvertUserPhone($arrUser['PERSONAL_MOBILE']);

if ($_SERVER["REQUEST_METHOD"] === "POST" && trim($_POST["SET_NEW_USER_DATA"]) != "") {
    if (!empty($_POST["NEW_EMAIL"])) {
        $strCustomerEmail = $_POST["NEW_EMAIL"];
    }
    if (!empty($_POST["NEW_PHONE"])) {
        $strCustomerPhone = $_POST["NEW_PHONE"];
    }
}

if (!empty($strCustomerEmail) && !FreedomPayIO::emailIsValid($strCustomerEmail)) {
    echo "
			<form method=\"POST\" action=\"" . POST_FORM_ACTION_URI . "\">
			<p><font color=\"Red\">$newEmail</font></p>
			<input type=\"text\" name=\"NEW_EMAIL\" size=\"30\" value=\"$strCustomerEmail\" />";
    echo "<br><br>
			<input type=\"submit\" name=\"SET_NEW_USER_DATA\" value=\"$useNew" .
        (!FreedomPayIO::emailIsValid($strCustomerEmail) ? "$useNewEmail" : "") .
        "\" />
	</form>";
    exit();
}

if (isset($GLOBALS['SALE_INPUT_PARAMS']['PROPERTY']['EMAIL'])) {
    $strCustomerEmail = $GLOBALS['SALE_INPUT_PARAMS']['PROPERTY']['EMAIL'];
}

$orderId = $_GET['ORDER_ID'];

if (!$orderId && !empty($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"])) {
    $orderId = $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"];
}

if (CSalePaySystemAction::GetParamValue('ORDER_ID_TYPE') === 'ORDER_NUMBER') {
    $order = Order::loadByAccountNumber($orderId);
} else {
    $order = Order::load($orderId);
}

if (empty($order)) {
    echo GetMessage('ORDER_NOT_FOUND');

    return;
}

$nAmount = $order->getPrice();
$nMerchantId = CSalePaySystemAction::GetParamValue("MERCHANT_ID");
$strSecretKey = CSalePaySystemAction::GetParamValue("SECRET_KEY");
$bTestingMode = CSalePaySystemAction::GetParamValue("TESTING_MODE") == "Y" ? 1 : 0;
$ofd = CSalePaySystemAction::GetParamValue("OFD");
$deliveryInOfd = CSalePaySystemAction::GetParamValue("DELIVERY_IN_OFD");
$taxType = CSalePaySystemAction::GetParamValue("TAX_TYPE");

$nPSId = $order->getPaySystemIdList()[0];

$arPaymentsCollection = $order->getPaymentCollection();

foreach ($arPaymentsCollection as $payment) {
    if ($payment->getField('PAY_SYSTEM_NAME') === 'FreedomPay') {
        $nAmount = $payment->getField('SUM');
        $nPSId = $payment->getField('PAY_SYSTEM_ID');

        break;
    }
}

$nAmount = number_format($nAmount, 2, '.', '');

$arrRequest['pg_salt'] = uniqid();
$arrRequest['pg_merchant_id'] = $nMerchantId;
$arrRequest['pg_order_id'] = $orderId;
$arrRequest['pg_lifetime'] = 3600 * 24;
$arrRequest['pg_amount'] = $nAmount;
$arrRequest['pg_currency'] = $order->getCurrency();
$arrRequest['pg_description'] = 'Order ID: ' . $orderId;
$arrRequest['pg_user_phone'] = $strCustomerPhone;
$arrRequest['pg_user_contact_email'] = $strCustomerEmail;
$arrRequest['pg_user_email'] = $strCustomerEmail;
$arrRequest['pg_user_ip'] = $_SERVER['REMOTE_ADDR'];
$arrRequest['pg_sire_url'] = "http://" . $_SERVER['HTTP_HOST'];
$arrRequest['pg_check_url'] = "https://" . $_SERVER['HTTP_HOST'] . "/freedompay/check.php?PAYMENT_SYSTEM=$nPSId";
$arrRequest['pg_result_url'] = "https://" . $_SERVER['HTTP_HOST'] . "/freedompay/result.php?PAYMENT_SYSTEM=$nPSId";
$arrRequest['pg_request_method'] = 'POST';
$arrRequest['pg_success_url'] = "https://" . $_SERVER['HTTP_HOST'] . "/freedompay/success.php?PAYMENT_SYSTEM=$nPSId";
$arrRequest['pg_refund_url'] = "https://" . $_SERVER['HTTP_HOST'] . "/freedompay/refund.php?PAYMENT_SYSTEM=$nPSId";
$arrRequest['pg_success_url_method'] = 'AUTOPOST';
$arrRequest['pg_failure_url'] = "https://" . $_SERVER['HTTP_HOST'] . "/freedompay/failure.php?PAYMENT_SYSTEM=$nPSId";
$arrRequest['pg_failure_url_method'] = 'AUTOPOST';
$arrRequest['pg_timeout_after_payment'] = 3600;

if ($bTestingMode) {
    $arrRequest['pg_testing_mode'] = '1';
}

$arrRequest['pg_encoding'] = 'windows-1251';

if ($arPaymentsCollection->count() === 1 && $ofd === 'Y') {
    $basketList = CSaleBasket::GetList(array(), array("ORDER_ID" => $order->getId()));
    $arrItems = [];
    $pgReceiptPositions = [];

    while ($arrItem = $basketList->Fetch()) {
        $arrItems[] = $arrItem['NAME'] . ', ';

        $pgReceiptPositions[] = [
            'count'    => $arrItem['QUANTITY'],
            'name'     => $arrItem['NAME'],
            'tax_type' => $taxType,
            'price'    => $arrItem['PRICE'],
        ];
    }

    $deliveryPrice = $order->getDeliveryPrice();

    if ($deliveryInOfd === 'Y' && $deliveryPrice > 0) {
        $pgReceiptPositions[] = [
            'count'    => 1,
            'name'     => GetMessage("DELIVERY"),
            'tax_type' => $taxType,
            'price'    => $deliveryPrice,
        ];
    }

    $arrRequest['pg_receipt_positions'] = $pgReceiptPositions;
}

$arrRequest['pg_sig'] = FreedomPaySignature::make('payment.php', $arrRequest, $strSecretKey);

/*
 * FreedomPay Request
 */
print "<form name=\"payment\" method='" . $strRequestMethod . "' action='https://api.paybox.money/payment.php' target=\"_blank\">";

foreach ($arrRequest as $key => $value) {
    if ($key === 'pg_receipt_positions') {
        foreach ($value as $itemPos => $item) {
            foreach ($item as $pgkey => $pgvalue) {
                print "<label for=''><input type='hidden' name='" . $key . "[" . $itemPos . "][" . $pgkey . "]' value='" . $pgvalue . "' /></label>";
            }
        }
    } else {
        print "<label for=''><input type='hidden' name='" . $key . "' value='" . $value . "' />			</label>";
    }
}

print "<button type=\"submit\">Перейти к оплате</button></form>";
