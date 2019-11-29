<?php


namespace Letsrock\Bonus;

/**
 * Class Information
 *
 * @package Letsrock\Bonus
 */
class Information extends Core
{
    /**
     * Information constructor.
     *
     * @param int $userId
     */
    public function __construct(int $userId)
    {
        parent::__construct($userId);
    }

    /**
     * Возвращает количество бонусов пользователя
     *
     * @return mixed
     */
    protected function getCountBonuses()
    {
        try {
            $result = \Bitrix\Main\UserTable::getList([
                'select' => ['NAME', 'ID', 'UF_BONUS_DATE'],
                'filter' => ['=ID' => $this->userId]
            ]);

            $arUser = $result->fetch();

            return $arUser['UF_BONUS'];
        } catch (\Exception $e) {
            return 0;
        }
    }


}