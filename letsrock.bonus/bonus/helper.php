<?php

namespace Letsrock\Bonus;

use Bitrix\Main\UserTable;
use DateTime;

/**
 * Class Helper
 *
 * @package Bonus\Lib
 */
class Helper
{
    /**
     * Возвращает левую границу интервала
     * при поиске в массиве по ключу
     *
     * @param array $array
     * @param int $currentKey
     *
     * @return bool|int|string
     */
    public static function findLeftBorderInArray(array $array, int $currentKey)
    {
        if (empty($array)) {
            return false;
        }

        ksort($array);
        $prevKey = 0;
        $foundKey = 0;
        $iterator = 1;
        $itemsCount = count($array);

        foreach ($array as $key => $item) {
            if ($currentKey >= $key && $iterator < $itemsCount) {
                $foundKey = $prevKey;
                $prevKey = $key;
            } elseif ($currentKey < $key && $iterator == $itemsCount) {
                $foundKey = $prevKey;
                break;
            } elseif ($iterator == $itemsCount) {
                $foundKey = $key;
                break;
            } else {
                $foundKey = $prevKey;
                break;
            }

            $iterator++;
        }

        return $foundKey;
    }

    /**
     * Изменяет количество бонусов у пользователя
     *
     * @param int $userId     ID пользователя
     * @param int $bonusCount Количество бонусов
     * @param bool $add       Добавить/ужвдить бонусы
     *
     * @return bool
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function changeBonusInUser(int $userId, int $bonusCount, bool $add = true)
    {
        $result = UserTable::getList([
            'select' => ['NAME', 'ID', 'UF_BONUS'],
            'filter' => ['=ID' => $userId]
        ]);

        $arUser = $result->fetch();
        $currentBonusCount = $arUser['UF_BONUS'];

        if ($add) {
            $currentBonusCount += $bonusCount;
        } else {
            $currentBonusCount -= $bonusCount;

        }

//        return \Bitrix\Main\UserTable::update($userId, ['UF_BONUS' => $currentBonusCount]); //TODO: битрикс по состоянию на конец 2019 года не умеет так делать, раскомментировать позднее

        $user = new \CUser;
        return $user->Update($userId, ['UF_BONUS' => $currentBonusCount]);
    }

    /**
     * Возвращает количество бонусов по стоимости покупки
     *
     * @param int $countMoney           Сумма покупки
     * @param int $monthUser            Месяц пользователя
     * @param array $bonusSystemByMonth Сортированные бонусы по месяцам
     *
     * @return int
     */
    public static function getBonusCountByMoney(int $countMoney, int $monthUser, array $bonusSystemByMonth): int
    {
        if (empty($countMoney) || empty($monthUser) || empty($bonusSystemByMonth)) {
            return 0;
        }

        $month = Helper::findLeftBorderInArray($bonusSystemByMonth, $monthUser);
        $bonusBorder = Helper::findLeftBorderInArray($bonusSystemByMonth[$month]['ELEMENTS'], $countMoney);

        if (!empty($bonusSystemByMonth[$month]['ELEMENTS'][$bonusBorder]['PROPERTY_COUNT_MONEY_VALUE'])) {
            return $bonusSystemByMonth[$month]['ELEMENTS'][$bonusBorder]['PROPERTY_COUNT_MONEY_VALUE'];
        } else {
            return 0;
        }
    }

    /**
     * Возвращает список заказов по ID пользователя
     *
     * @param string $userId
     * @param string $status
     * @param \Bitrix\Main\Type\DateTime $date
     *
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     */
    public static function getOrdersByUser(string $userId, \Bitrix\Main\Type\DateTime $date, string $status = 'F')
    {
        $arFilter = [
            "USER_ID" => $userId,
            "STATUS_ID" => $status,
            ">=DATE_INSERT" => $date
        ];

        $dbRes = \Bitrix\Sale\Order::getList([
            'order' => ['DATE_INSERT' => 'DESC'],
            'filter' => $arFilter
        ]);

        return $dbRes->fetchAll();
    }

    /**
     * Возвращает номер текущего месяца пользователя
     *
     * @param int $userId ID пользователя
     *
     * @return mixed
     */
    public static function getMonthByUser(int $userId)
    {
        try {
            $result = UserTable::getList([
                'select' => ['NAME', 'ID', 'UF_BONUS_DATE'],
                'filter' => ['=ID' => $userId]
            ]);

            $arUser = $result->fetch();

            if (empty($arUser['UF_BONUS_DATE'])) {
                throw new \Exception('Нет даты у бонусной транзакции');
            }
            /**
             * \Bitrix\Main\Type\DateTime $dateReg
             */
            $dateReg = $arUser['UF_BONUS_DATE']->getTimestamp();
            $dateForFirstMonth = new DateTime(date('m/1/Y', strtotime('-1 months')));

            if ($dateForFirstMonth->getTimestamp() < $dateReg) {
                return 1; //Первые 2 календырных месяца == 1 месяц
            }

            $dateDiff = time() - $dateReg;
            $monthsCount = intval($dateDiff / 60 / 60 / 24 / 30); //Количество месяцев


            return $monthsCount;
        } catch (\Exception $e) {
            AddMessage2Log($e->getMessage(), "letsrock.bonus");

            return 0;
        }
    }
}