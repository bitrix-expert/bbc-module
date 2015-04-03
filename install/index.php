<?php

IncludeModuleLangFile(__FILE__);

class bex_bbc extends CModule
{
    var $MODULE_ID = 'bex.bbc';
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $PARTNER_NAME;
    var $PARTNER_URI;

    function __construct()
    {
        $arModuleVersion = [];

        include(__DIR__ . '/version.php');

        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion))
        {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }

        $this->MODULE_NAME = GetMessage('BBC_MODULE_NAME');
        $this->MODULE_DESCRIPTION = GetMessage('BBC_MODULE_DESCRIPTION');
        $this->PARTNER_NAME = GetMessage('BBC_PARTNER_NAME');
        $this->PARTNER_URI = GetMessage('BBC_PARTNER_URI');
    }

    public function DoInstall()
    {
        global $APPLICATION;
        $APPLICATION->IncludeAdminFile(GetMessage('BBC_INSTALL_TITLE'), __DIR__ . '/install.php');
    }

    public function DoUninstall()
    {
        UnRegisterModule('bex.bbc');
    }
}