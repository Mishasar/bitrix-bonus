<?php
namespace Letsrock\Bonus;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Loader;

/**
 * Базовый класс транзакции
 *
 * @package Bonus\Lib\Models
 */
abstract class Transaction extends Core
{
    /**
     * Transaction constructor.
     *
     * @param int $userId
     */
    public function __construct(int $userId)
    {
        parent::__construct($userId);
    }

    /**
     * В качестве аргумента принимает массив параметров со структурой:
     *
     * array
     *      ['SIGN']    Знак транзации. Значения 1 обозначает зачисление, 0 спимание.
     *      ['BONUS']   Количество бонусов
     *      ['USER']    Пользователь совершивший или отменивший заказ
     *      ['ORDER']   Номер заказа
     *
     * @param array $params
     * @param int $userId
     *
     * @return bool
     */
    protected function createTransaction(array $params)
    {

        try {
            Loader::IncludeModule('highloadblock');
            $hlBlock = HighloadBlockTable::getById(self::HL_BONUS_TRANSACTION)->fetch();
            $entity = HighloadBlockTable::compileEntity($hlBlock);
            $entityDataClass = $entity->getDataClass();
            $result = $entityDataClass::add($params);
        } catch (\Exception $e) {
            AddMessage2Log($e->getMessage(), "letsrock.bonus");
            return false;
        }

        return $result->isSuccess();
    }
}