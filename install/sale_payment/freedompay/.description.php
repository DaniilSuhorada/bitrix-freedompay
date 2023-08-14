<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;
use FreedomPay\Helper;

CModule::IncludeModule('sale');
CModule::IncludeModule('freedompay.pay');
Loc::loadMessages(__FILE__);

$psTitle = Loc::getMessage('TITLE');
$psDescription = Loc::getMessage('DESCRIPTION');

$statusIdAndNameArray = Helper::getSaleStatusList();

$arPSCorrespondence = [
    'MERCHANT_ID'               => [
        'NAME'  => GetMessage('MERCHANT_ID'),
        'DESCR' => GetMessage('MERCHANT_ID_DESCR'),
        'SORT'  => 200,
        'VALUE' => '',
        'TYPE'  => '',
        'GROUP' => GetMessage('PAY_SYSTEM_SECTION'),
    ],
    'SECRET_KEY'                => [
        'NAME'  => GetMessage('SECRET_KEY'),
        'DESCR' => GetMessage('SECRET_KEY_DESCR'),
        'SORT'  => 201,
        'VALUE' => '',
        'TYPE'  => '',
        'GROUP' => GetMessage('PAY_SYSTEM_SECTION'),
    ],
    'API_URL'               => [
        'NAME'  => GetMessage('API_URL'),
        'DESCR' => GetMessage('API_URL_DESCR'),
        'SORT'  => 202,
        'VALUE' => Helper::FREEDOMPAY_API_URL,
        'TYPE'  => 'SELECT',
        'GROUP' => GetMessage('PAY_SYSTEM_SECTION'),
    ],
    'STATUS_PAID'               => [
        'NAME'  => GetMessage('STATUS_PAID'),
        'DESCR' => GetMessage('STATUS_PAID_DESCR'),
        'SORT'  => 202,
        'VALUE' => $statusIdAndNameArray,
        'TYPE'  => 'SELECT',
        'GROUP' => GetMessage('PAY_SYSTEM_SECTION'),
    ],
    'STATUS_FAILED'             => [
        'NAME'  => GetMessage('STATUS_FAILED'),
        'DESCR' => GetMessage('STATUS_FAILED_DESCR'),
        'SORT'  => 203,
        'VALUE' => $statusIdAndNameArray,
        'TYPE'  => 'SELECT',
        'GROUP' => GetMessage('PAY_SYSTEM_SECTION'),
    ],
    'STATUS_REVOKED'            => [
        'NAME'  => GetMessage('STATUS_REVOKED'),
        'DESCR' => GetMessage('STATUS_REVOKED_DESCR'),
        'SORT'  => 204,
        'VALUE' => $statusIdAndNameArray,
        'TYPE'  => 'SELECT',
        'GROUP' => GetMessage('PAY_SYSTEM_SECTION'),
    ],
    'TESTING_MODE'              => [
        'NAME'  => GetMessage('TESTING_MODE'),
        'DESCR' => GetMessage('TESTING_MODE_DESCR'),
        'SORT'  => 205,
        'VALUE' => '',
        'TYPE'  => 'CHECKBOX',
        'GROUP' => GetMessage('PAY_SYSTEM_SECTION'),
    ],
    'ORDER_ID_TYPE'             => [
        'NAME'  => GetMessage('ORDER_ID_TYPE'),
        'DESCR' => GetMessage('ORDER_ID_TYPE_DESCR'),
        'SORT'  => 206,
        'VALUE' => Helper::ORDER_ID_TYPE_ARRAY,
        'TYPE'  => 'SELECT',
        'GROUP' => GetMessage('PAY_SYSTEM_SECTION'),
    ],
    'OFD'                       => [
        'NAME'  => GetMessage('OFD'),
        'DESCR' => GetMessage('OFD_DESCR'),
        'SORT'  => 300,
        'VALUE' => '',
        'TYPE'  => 'CHECKBOX',
        'GROUP' => GetMessage('OFD_SECTION'),
    ],
    'OFD_VERSION'               => [
        'NAME'  => GetMessage('OFD_VERSION'),
        'DESCR' => GetMessage('OFD_VERSION_DESCR'),
        'SORT'  => 301,
        'VALUE' => Helper::OFD_VERSION_ARRAY,
        'TYPE'  => 'SELECT',
        'GROUP' => GetMessage('OFD_SECTION'),
    ],
    'TAXATION_SYSTEM'           => [
        'NAME'  => GetMessage('TAXATION_SYSTEM'),
        'DESCR' => GetMessage('TAXATION_SYSTEM_DESCR'),
        'SORT'  => 301,
        'VALUE' => Helper::TAXATION_SYSTEM_ARRAY,
        'TYPE'  => 'SELECT',
        'GROUP' => GetMessage('OFD_SECTION'),
    ],
    'PAYMENT_METHOD'            => [
        'NAME'  => GetMessage('PAYMENT_METHOD'),
        'DESCR' => GetMessage('PAYMENT_METHOD_DESCR'),
        'SORT'  => 302,
        'VALUE' => Helper::PAYMENT_METHOD_ARRAY,
        'TYPE'  => 'SELECT',
        'GROUP' => GetMessage('OFD_SECTION'),
    ],
    'PAYMENT_OBJECT'            => [
        'NAME'  => GetMessage('PAYMENT_OBJECT'),
        'DESCR' => GetMessage('PAYMENT_OBJECT_DESCR'),
        'SORT'  => 303,
        'VALUE' => Helper::PAYMENT_OBJECT_ARRAY,
        'TYPE'  => 'SELECT',
        'GROUP' => GetMessage('OFD_SECTION'),
    ],
    'MEASURE'                   => [
        'NAME'  => GetMessage('MEASURE'),
        'DESCR' => GetMessage('MEASURE_DESCR'),
        'SORT'  => 304,
        'VALUE' => Helper::MEASURE_ARRAY,
        'TYPE'  => 'SELECT',
        'GROUP' => GetMessage('OFD_SECTION'),
    ],
    'TAX_TYPE'          => [
        'NAME'  => GetMessage('TAX_TYPE'),
        'DESCR' => GetMessage('TAX_TYPE_DESCR'),
        'SORT'  => 305,
        'VALUE' => Helper::TAX_TYPE_ARRAY,
        'TYPE'  => 'SELECT',
        'GROUP' => GetMessage('OFD_SECTION'),
    ],
    'NEW_TAX_TYPE'                  => [
        'NAME'  => GetMessage('NEW_TAX_TYPE'),
        'DESCR' => GetMessage('NEW_TAX_TYPE_DESCR'),
        'SORT'  => 306,
        'VALUE' => Helper::NEW_TAX_TYPE_ARRAY,
        'TYPE'  => 'SELECT',
        'GROUP' => GetMessage('OFD_SECTION'),
    ],
    'DELIVERY_IN_OFD'              => [
        'NAME'  => GetMessage('DELIVERY_IN_OFD'),
        'DESCR' => GetMessage('DELIVERY_IN_OFD_DESCR'),
        'SORT'  => 307,
        'VALUE' => '',
        'TYPE'  => 'CHECKBOX',
        'GROUP' => GetMessage('OFD_SECTION'),
    ],
    'DELIVERY_PAYMENT_OBJECT'   => [
        'NAME'  => GetMessage('DELIVERY_PAYMENT_OBJECT'),
        'DESCR' => GetMessage('DELIVERY_PAYMENT_OBJECT_DESCR'),
        'SORT'  => 308,
        'VALUE' => Helper::DELIVERY_PAYMENT_OBJECT_ARRAY,
        'TYPE'  => 'SELECT',
        'GROUP' => GetMessage('OFD_SECTION'),
    ],
    'DELIVERY_TAX_TYPE' => [
        'NAME'  => GetMessage('OLD_DELIVERY_TAX_TYPE'),
        'DESCR' => GetMessage('OLD_DELIVERY_TAX_TYPE_DESCR'),
        'SORT'  => 309,
        'VALUE' => Helper::TAX_TYPE_ARRAY,
        'TYPE'  => 'SELECT',
        'GROUP' => GetMessage('OFD_SECTION'),
    ],
    'NEW_DELIVERY_TAX_TYPE'         => [
        'NAME'  => GetMessage('NEW_DELIVERY_TAX_TYPE'),
        'DESCR' => GetMessage('NEW_DELIVERY_TAX_TYPE_DESCR'),
        'SORT'  => 310,
        'VALUE' => Helper::NEW_TAX_TYPE_ARRAY,
        'TYPE'  => 'SELECT',
        'GROUP' => GetMessage('OFD_SECTION'),
    ],
];
