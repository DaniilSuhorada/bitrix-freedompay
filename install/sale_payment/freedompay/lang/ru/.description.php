<?php

//General settings
$MESS['TITLE'] = 'FreedomPay';
$MESS['DESCRIPTION'] = 'Универсальная система приема платежей FreedomPay';

$MESS['PAY_SYSTEM_SECTION'] = 'Общие настройки платежной системы';

$MESS['MERCHANT_ID'] = 'Идентификатор магазина в FreedomPay (Merchant ID)';
$MESS['MERCHANT_ID_DESCR'] = 'Используется для идентификации магазина при совершении платежей.';

$MESS['SECRET_KEY'] = 'Секретный ключ (Secret Key)';
$MESS['SECRET_KEY_DESCR'] = 'Используется для подтверждения идентификации магазина при совершении платежей.';

$MESS['API_URL'] = 'API URL платежной системы';
$MESS['API_URL_DESCR'] = 'API URL платежной системы.';

$MESS['TESTING_MODE'] = 'Тестовый режим';
$MESS['TESTING_MODE_DESCR'] = 'В случае если вы находитесь в боевом режиме, но вам нужно провести тестовые транзакции, установите флаг настройки и все транзакции будут создаваться по тестовым платежным системам.';

$MESS['STATUS_PAID'] = 'Статус после успешной оплаты';
$MESS['STATUS_PAID_DESCR'] = 'Статус после успешной оплаты';

$MESS['STATUS_FAILED'] = 'Статус после отказа';
$MESS['STATUS_FAILED_DESCR'] = 'Статус после отказа платежа.';

$MESS['STATUS_REVOKED'] = 'Статус после возврата';
$MESS['STATUS_REVOKED_DESCR'] = 'Статус после возврата платежа.';

$MESS['ORDER_ID_TYPE'] = 'Тип идентификатора заказа';
$MESS['ORDER_ID_TYPE_DESCR'] = 'Тип идентификатора заказа. По умолчанию Id заказа';

//OFD settings
$MESS['OFD_SECTION'] = 'Настройки отправки чеков';

$MESS['OFD_VERSION'] = 'Версия ФФД';
$MESS['OFD_VERSION_DESCR'] = 'Версия ФФД.';

$MESS['TAXATION_SYSTEM'] = 'Система налогообложения';
$MESS['TAXATION_SYSTEM_DESCR'] = 'Система налогообложения.';

$MESS['TAX_TYPE'] = 'НДС на товары для ФФД старой версии';
$MESS['TAX_TYPE_DESCR'] = 'НДС на товары для ФФД старой версии.';

$MESS['NEW_TAX_TYPE'] = 'НДС на товары';
$MESS['NEW_TAX_TYPE_DESCR'] = 'НДС на товары.';

$MESS['OLD_DELIVERY_TAX_TYPE'] = 'НДС на доставку для ФФД старой версии';
$MESS['OLD_DELIVERY_TAX_TYPE_DESCR'] = 'НДС на доставку для ФФД старой версии.';

$MESS['NEW_DELIVERY_TAX_TYPE'] = 'НДС на доставку';
$MESS['NEW_DELIVERY_TAX_TYPE_DESCR'] = 'НДС на доставку.';

$MESS['PAYMENT_METHOD'] = 'Признак способа расчета';
$MESS['PAYMENT_METHOD_DESCR'] = 'Признак способа расчета.';

$MESS['DELIVERY_TAX_TYPE'] = 'НДС на доставку';
$MESS['DELIVERY_TAX_TYPE_DESCR'] = 'НДС на доставку.';

$MESS['PAYMENT_OBJECT'] = 'Признак предмета расчета товара';
$MESS['PAYMENT_OBJECT_DESCR'] = 'Признак предмета расчета товара.';

$MESS['DELIVERY_PAYMENT_OBJECT'] = 'Признак предмета расчета доставки';
$MESS['DELIVERY_PAYMENT_OBJECT_DESCR'] = 'Признак предмета расчета доставки	.';

$MESS['OFD'] = 'Включить ОФД';
$MESS['OFD_DESCR'] = 'Включить ОФД.';

$MESS['DELIVERY_IN_OFD'] = 'Учитывать доставку в ОФД';
$MESS['DELIVERY_IN_OFD_DESCR'] = 'Учитывать доставку в ОФД.';

$MESS['MEASURE'] = 'Мера количества предмета расчета';
$MESS['MEASURE_DESCR'] = 'Мера количества предмета расчета. Учитывается только при выборе ФФД 1.2.';
