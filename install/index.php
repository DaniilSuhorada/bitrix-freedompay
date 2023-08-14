<?php

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

if (!IsModuleInstalled('sale')) {
    return;
}

Loc::loadMessages(__FILE__);

class freedompay_pay extends CModule
{
    public $MODULE_ID = 'freedompay.pay';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $MODULE_GROUP_RIGHTS = 'N';

    public function __construct()
    {
        $moduleVersion = [];

        include(__DIR__ . '/version.php');

        $this->MODULE_NAME = Loc::getMessage('MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('MODULE_DESCRIPTION');
        $this->PARTNER_NAME = Loc::getMessage('PARTNER_NAME');
        $this->PARTNER_URI = Loc::getMessage('PARTNER_URI');

        if (!is_array($arModuleVersion)) {
            return;
        }

        if (array_key_exists('VERSION', $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        }

        if (array_key_exists('VERSION_DATE', $arModuleVersion)) {
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }
    }

    public function DoInstall(): bool
    {
        $this->installFiles();
        ModuleManager::registerModule($this->MODULE_ID);

        return true;
    }

    /**
     * @return bool
     * @throws ArgumentNullException
     */
    public function DoUninstall(): bool
    {
        $this->uninstallFiles();
        Option::delete($this->MODULE_ID);
        ModuleManager::unRegisterModule($this->MODULE_ID);

        return true;
    }

    public function installFiles(): bool
    {
        CopyDirFiles(
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/install/sale_payment/',
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/php_interface/include/sale_payment',
            true,
            true
        );

        CopyDirFiles(
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/install/callback/',
            $_SERVER['DOCUMENT_ROOT'] . "/$this->MODULE_ID",
            true,
            true
        );

        return true;
    }

    public function uninstallFiles(): bool
    {
        DeleteDirFilesEx('/bitrix/php_interface/include/sale_payment/freedompay');
        DeleteDirFilesEx("/$this->MODULE_ID");

        return true;
    }
}
