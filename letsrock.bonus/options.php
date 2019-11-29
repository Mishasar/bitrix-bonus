<?

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();
defined('ADMIN_MODULE_NAME') or define('ADMIN_MODULE_NAME', 'letsrock.bonus');

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;

\Bitrix\Main\Loader::includeModule(ADMIN_MODULE_NAME);

$moduleRight = $APPLICATION->GetGroupRight(ADMIN_MODULE_NAME);

?>
