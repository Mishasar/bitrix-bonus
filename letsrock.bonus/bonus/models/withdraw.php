<?php

namespace Letsrock\Bonus;

use Bitrix\Sale\Order;

/**
 * Class Withdraw
 *
 * @package Bonus\Lib\Models
 */
class Withdraw extends Transaction
{
    /**
     * Deposit constructor.
     *
     * @param int $userId
     * @param int $orderId
     */
    public function __construct(int $userId, int $bonusCount)
    {
        try {
            parent::__construct($userId);
            Helper::changeBonusInUser($userId, $bonusCount, false);
            $resultAddTransaction = $this->createTransaction([
                'UF_SIGN' => 0,
                'UF_BONUS' => $bonusCount,
                'UF_USER' => $userId,
                'UF_DATE' => time()
            ]);

            return $resultAddTransaction;
        } catch (\Exception $e) {
            AddMessage2Log($e->getMessage(), "letsrock.bonus");
            return false;
        }
    }
}