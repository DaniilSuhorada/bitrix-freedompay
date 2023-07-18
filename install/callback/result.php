<?php

use FreedomPay\Result;

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

CModule::IncludeModule('sale');
CModule::IncludeModule('freedompay.pay');

$resultHandler = new Result();
$resultHandler->handleResultRequest();
