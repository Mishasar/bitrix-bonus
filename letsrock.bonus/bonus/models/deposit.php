<?php

namespace Letsrock\Bonus;

use Bitrix\Sale\Order;

/**
 * Class Deposit
 *
 * @package Bonus\Lib\Models
 */
class Deposit extends Transaction
{

    /**
     * Deposit constructor
     *
     * @param Order $order Заказ битрикса
     *
     * @return bool
     */
    public function __construct2(Order $order)
    {
        try {
            $userId = $order->getUserId();
            parent::__construct($userId);
            $dataInsert = $order->getDateInsert();
            $price = $order->getPrice();
            $bonus = $this->getBonusCountByPrice($price);
            Helper::changeBonusInUser($userId, $bonus);

            $resultAddTransaction = $this->createTransaction([
                'UF_SIGN' => 1,
                'UF_BONUS' => $bonus,
                'UF_USER' => $userId,
                'UF_ORDER' => $order->getId(),
                'UF_DATE' => $dataInsert->toString()
            ]);

            return $resultAddTransaction;
        } catch (\Exception $e) {
            AddMessage2Log($e->getMessage(), "letsrock.bonus");
            return false;
        }
    }
}