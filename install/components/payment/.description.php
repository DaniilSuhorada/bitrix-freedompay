<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

include(GetLangFileName(__DIR__ . "/", "/payment.php"));

$taxTypes = array(
    '0' => array('NAME' => 'Без налога'),
    '1' => array('NAME' => "ставка НДС 0%"),
    '2' => array('NAME' => "ставка НДС 12%"),
    '3' => array('NAME' => "ставка НДС 12/112"),
    '4' => array('NAME' => 'ставка НДС 18%'),
    '5' => array('NAME' => "ставка НДС 18/118"),
    '6' => array('NAME' => "ставка НДС 10%"),
    '7' => array('NAME' => "ставка НДС 10/110"),
    '8' => array('NAME' => "ставка НДС 20%"),
    '9' => array('NAME' => "ставка НДС 20/12"),
);

$psTitle = GetMessage("SPCP_DTITLE");
$psDescription = GetMessage("SPCP_DDESCR");

CModule::IncludeModule("sale");
$getList = CSaleStatus::GetList(array(), array("LID" => LANGUAGE_ID));
$arrStatusName = array();

while ($arrStatus = $getList->Fetch()) {
    $arrStatusName[] = $arrStatus;
}

$arrStatusIdAndName = array();

foreach ($arrStatusName as $key => $value) {
    $k = $value['ID'];
    $arrStatusIdAndName[$k] = array(
        'NAME' => $value['NAME']
    );
}

$arPSCorrespondence = array(
    'MERCHANT_ID'     => array(
        'NAME'  => GetMessage("SHOP_MERCHANT_ID"),
        'DESCR' => GetMessage("SHOP_MERCHANT_ID_DESCR"),
        'SORT'  => 200,
        "VALUE" => "",
        "TYPE"  => "",
        'GROUP' => 'GENERAL_SETTINGS',
    ),
    'SECRET_KEY'      => array(
        'NAME'  => GetMessage("SHOP_SECRET_KEY"),
        "DESCR" => GetMessage("SHOP_SECRET_KEY_DESCR"),
        'SORT'  => 300,
        "VALUE" => "",
        "TYPE"  => "",
        'GROUP' => 'GENERAL_SETTINGS',
    ),
    'STATUS_PAID'     => array(
        'NAME'  => GetMessage("STATUS_PAID"),
        "DESCR" => GetMessage("STATUS_PAID_DESCR"),
        'SORT'  => 400,
        "VALUE" => $arrStatusIdAndName,
        "TYPE"  => "SELECT",
        'GROUP' => 'GENERAL_SETTINGS',
    ),
    'STATUS_FAILED'   => array(
        'NAME'  => GetMessage("STATUS_FAILED"),
        "DESCR" => GetMessage("STATUS_FAILED_DESCR"),
        'SORT'  => 500,
        "VALUE" => $arrStatusIdAndName,
        "TYPE"  => "SELECT",
        'GROUP' => 'GENERAL_SETTINGS',
    ),
    'STATUS_REVOKED'  => array(
        'NAME'  => GetMessage("STATUS_REVOKED"),
        "DESCR" => GetMessage("STATUS_REVOKED_DESCR"),
        'SORT'  => 600,
        "VALUE" => $arrStatusIdAndName,
        "TYPE"  => "SELECT",
        'GROUP' => 'GENERAL_SETTINGS',
    ),
    'TESTING_MODE'    => array(
        "NAME"  => GetMessage("SHOP_TESTING_MODE"),
        "DESCR" => GetMessage("SHOP_TESTING_MODE_DESCR"),
        'SORT'  => 700,
        "VALUE" => "",
        "TYPE"  => "CHECKBOX",
        'GROUP' => 'GENERAL_SETTINGS',
    ),
    'OFD'             => array(
        'NAME'  => GetMessage("SHOP_OFD"),
        "DESCR" => GetMessage("SHOP_OFD_DESCR"),
        'SORT'  => 800,
        "VALUE" => '',
        "TYPE"  => "CHECKBOX",
        'GROUP' => 'GENERAL_SETTINGS',
    ),
    'DELIVERY_IN_OFD' => array(
        'NAME'  => GetMessage("SHOP_DELIVERY_IN_OFD"),
        "DESCR" => GetMessage("SHOP_DELIVERY_IN_OFD_DESCR"),
        'SORT'  => 900,
        "VALUE" => '',
        "TYPE"  => "CHECKBOX",
        'GROUP' => 'GENERAL_SETTINGS',
    ),
    'TAX_TYPE'        => array(
        'NAME'  => GetMessage("SHOP_TAX_TYPE"),
        "DESCR" => GetMessage("SHOP_TAX_TYPE_DESCR"),
        'SORT'  => 1000,
        "VALUE" => $taxTypes,
        "TYPE"  => "SELECT",
        'GROUP' => 'GENERAL_SETTINGS',
    ),
    'ORDER_ID_TYPE'   => array(
        'NAME'  => GetMessage("SHOP_ORDER_ID_TYPE"),
        "DESCR" => GetMessage("SHOP_ORDER_ID_TYPE_DESCR"),
        'SORT'  => 1100,
        'VALUE' => array(
            'ORDER_ID'     => array(
                'NAME' => GetMessage("SHOP_ORDER_ID")
            ),
            'ORDER_NUMBER' => array(
                'NAME' => GetMessage("SHOP_ORDER_NUMBER")
            )
        ),
        'TYPE'  => 'SELECT',
        'GROUP' => 'GENERAL_SETTINGS',
    ),
);
