<?php
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

if (class_exists('letsrock_bonus')) {
    return;
}

class letsrock_bonus extends CModule
{
    /** @var string */
    public $MODULE_ID;

    /** @var string */
    public $MODULE_VERSION;

    /** @var string */
    public $MODULE_VERSION_DATE;

    /** @var string */
    public $MODULE_NAME;

    /** @var string */
    public $MODULE_DESCRIPTION;

    /** @var string */
    public $MODULE_GROUP_RIGHTS;

    /** @var string */
    public $PARTNER_NAME;

    /** @var string */
    public $PARTNER_URI;

    public function __construct()
    {
        $this->MODULE_ID = 'letsrock.bonus';
        $this->MODULE_VERSION = '0.1';
        $this->MODULE_VERSION_DATE = '2019-11-28 00:00:00';
        $this->MODULE_NAME = Loc::getMessage('BONUS_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('BONUS_MODULE_DESCRIPTION');
        $this->MODULE_GROUP_RIGHTS = 'N';
        $this->PARTNER_NAME = "Letsrock";
        $this->PARTNER_URI = "letsrock.pro";
    }

    public function doInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);
    }

    public function doUninstall()
    {
        ModuleManager::unregisterModule($this->MODULE_ID);
    }
}
