<?php

global $MESS;

$strPath2Lang = str_replace("\\", "/", __FILE__);
$strPath2Lang = substr($strPath2Lang, 0, strlen($strPath2Lang) - strlen("/install/index.php"));
include(GetLangFileName($strPath2Lang . "/lang/", "/install/index.php"));

class freedompay_pay extends CModule
{
    var $MODULE_ID = "freedompay.pay";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;
    var $MODULE_GROUP_RIGHTS = "Y";

    function freedompay_pay()
    {
        $arModuleVersion = array();

        $path = str_replace("\\", "/", __FILE__);
        $path = substr($path, 0, strlen($path) - strlen('/index.php'));
        include($path . '/version.php');

        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

        $this->PARTNER_NAME = ' FreedomPay';
        $this->PARTNER_URI = 'https://freedompay.money/';

        $this->MODULE_NAME = GetMessage("FREEDOMPAY_MODULE_NAME");
        $this->MODULE_DESCRIPTION = GetMessage("FREEDOMPAY_MODULE_DESC");
    }

    function InstallDB()
    {
        RegisterModule("freedompay.pay");
        return true;
    }

    function UnInstallDB()
    {
        UnRegisterModule("freedompay.pay");
        return true;
    }

    function InstallEvents()
    {
        return true;
    }

    function UnInstallEvents()
    {
        return true;
    }

    function InstallFiles()
    {
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/freedompay.pay/install/components/payment/",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/php_interface/include/sale_payment/freedompay/",
            true,
            true
        );
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/freedompay.pay/install/components/index/",
            $_SERVER["DOCUMENT_ROOT"] . "/freedompay/",
            true,
            true
        );

        return true;
    }

    function InstallPublic()
    {
    }

    function UnInstallFiles()
    {
        DeleteDirFiles(
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/freedompay.pay/install/components/payment/en/",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/php_interface/include/sale_payment/freedompay/en/"
        );
        DeleteDirFiles(
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/freedompay.pay/install/components/payment/ru/",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/php_interface/include/sale_payment/freedompay/ru/"
        );
        DeleteDirFiles(
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/freedompay.pay/install/components/payment/",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/php_interface/include/sale_payment/freedompay/"
        );
        DeleteDirFiles(
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/freedompay.pay/install/components/index/",
            $_SERVER["DOCUMENT_ROOT"] . "/freedompay/"
        );
        
        return true;
    }

    function DoInstall()
    {
        global $APPLICATION, $step;

        if (!IsModuleInstalled("freedompay.pay")) {
            $this->InstallFiles();
            $this->InstallDB(false);
            $this->InstallEvents();
            $this->InstallPublic();

            $APPLICATION->IncludeAdminFile(
                GetMessage("SCOM_INSTALL_TITLE"),
                $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/freedompay.pay/install/step.php"
            );
        }
    }

    function DoUninstall()
    {
        global $APPLICATION, $step;

        $this->UnInstallDB();
        $this->UnInstallFiles();
        $this->UnInstallEvents();
        $APPLICATION->IncludeAdminFile(
            GetMessage("SCOM_UNINSTALL_TITLE"),
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/freedompay.pay/install/unstep.php"
        );
    }
}
