<?php

use FreedomPay\Payment;

CModule::IncludeModule('sale');
CModule::IncludeModule('freedompay.pay');

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

try {
    $paymentHandler = new Payment();
    $paymentUrl = $paymentHandler->getPaymentUrl();
} catch (Exception $e) {
    echo $e->getMessage();

    die();
}

?>

<input type="submit" value="<?= GetMessage('PAY') ?>" onClick="window.location.href = '<?= $paymentUrl ?>'"/>
