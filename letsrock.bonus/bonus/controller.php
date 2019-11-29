<?php

namespace Letsrock\Bonus;

use Bitrix\Main\Loader;

/**
 * Контроллер бонусов
 *
 * Class BonusSystemController
 *
 * @package Bonus\Lib\Controllers
 */
class Controller
{
    /**
     * Обработчик события смены статуса
     *
     * @param \Bitrix\Main\Event $event
     *
     * @return \Bitrix\Main\EventResult
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     */
    public static function orderBonusHandler(\Bitrix\Main\Event $event)
    {
        $parameters = $event->getParameters();
        if ($parameters['VALUE'] === 'F') {
            try {
                $order = $parameters['ENTITY'];
                Loader::includeModule('letsrock.bonus');
                $transaction = new Deposit($order);

                return new \Bitrix\Main\EventResult(
                    \Bitrix\Main\EventResult::SUCCESS
                );
            } catch (\Exception $e) {
                AddMessage2Log($e->getMessage(), "letsrock.bonus");
            }
        }
        
        return new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::ERROR
        );
    }
}