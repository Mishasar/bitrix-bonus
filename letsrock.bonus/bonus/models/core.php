<?php

namespace Letsrock\Bonus;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Loader;
use CIBlockElement;
use DateTime;

/**
 * Class Core
 *
 * @package Letsrock\Bonus
 */
abstract class Core
{
    const HL_BONUS_TRANSACTION = HL_BONUS; //TODO: Сделать настройки в панели администратора

    protected $userId;
    protected $month;
    protected $bonusSystemByMonth;

    /**
     * Core constructor.
     *
     * @param $userId
     */
    public function __construct($userId)
    {
        $this->userId = $userId;
        $this->month = Helper::getMonthByUser($userId);
        $this->bonusSystemByMonth = $this->getBonusesStructure();
    }

    /**
     * Возвращает массив бонусов со структурой:
     *
     *  array
     *      ["MONTH"]
     *          ["MINIMAL_COST" => "BONUS_COUNT"]
     *          ["MINIMAL_COST" => "BONUS_COUNT"]
     *          ["MINIMAL_COST" => "BONUS_COUNT"]
     *      ["MONTH"]
     *          ["MINIMAL_COST" => "BONUS_COUNT"]
     *          ["MINIMAL_COST" => "BONUS_COUNT"]
     *          ["MINIMAL_COST" => "BONUS_COUNT"]
     *
     * @return array
     */
    private function getBonusesStructure(): array
    {
        $arFields = [];
        $arResult = [];
        $section = [];
        $arSelect = [
            "ID",
            "NAME",
            "PROPERTY_COUNT_MONEY",
            "PROPERTY_COUNT_BONUSES",
            "IBLOCK_SECTION_ID"
        ];
        $arFilter = ["IBLOCK_ID" => IntVal(IB_BONUS_SYSTEM), "ACTIVE_DATE" => "Y", "ACTIVE" => "Y"];
        $res = CIBlockElement::GetList(["SORT" => "ASC"], $arFilter, false, ["nPageSize" => 50], $arSelect);

        while ($ob = $res->GetNextElement()) {
            $props = $ob->GetFields();
            $arFields[$props['IBLOCK_SECTION_ID']][(int)$props['PROPERTY_COUNT_MONEY_VALUE']] = $props;
        }

        $rsSect = \CIBlockSection::GetList(
            [],
            ['IBLOCK_ID' => IntVal(IB_BONUS_SYSTEM)],
            false,
            ['ID', 'NAME', 'CODE', 'UF_MONTH_NUMBER']);

        while ($arSect = $rsSect->GetNext()) {
            $section['ELEMENTS'] = $arFields[(int)$arSect['ID']];
            $section['SECTION_INFO'] = $arSect;
            $arResult[(int)$arSect['UF_MONTH_NUMBER']] = $section;
        }

        return $arResult;
    }

    /**
     * Возвращает количество бонусов накопленное пользователем за месяц
     *
     * @param \Bitrix\Main\Type\DateTime $date Дата начала месяца
     *
     * @return int
     */
    protected function getMonthDepositBonuses(\Bitrix\Main\Type\DateTime $date)
    {
        try {
            $bonusCount = 0;
            Loader::IncludeModule('highloadblock');
            $hlBlock = HighloadBlockTable::getById(self::HL_BONUS_TRANSACTION)->fetch();
            $entity = HighloadBlockTable::compileEntity($hlBlock);
            $entityDataClass = $entity->getDataClass();

            $transactions = $entityDataClass::getList([
                'select' => ['UF_USER', 'UF_SIGN', 'UF_BONUS', 'UF_DATE'],
                'filter' => [
                    ">UF_DATE" => $date,
                    '=UF_USER' => $this->userId,
                    "=UF_SIGN" => 1
                ]
            ])->fetchAll();

            foreach ($transactions as $transaction) {
                $bonusCount += $transaction['UF_BONUS'];
            }

            return $bonusCount;
        } catch (\Exception $e) {
            AddMessage2Log($e->getMessage(), "letsrock.bonus");
            return 0;
        }
    }

    /**
     * Возвращает количество бонусов потраченное пользователем за месяц
     *
     * @param \Bitrix\Main\Type\DateTime $date Дата начала месяца
     *
     * @return int
     */
    protected function getMonthWithdrawBonuses(\Bitrix\Main\Type\DateTime $date)
    {
        try {
            $bonusCount = 0;
            Loader::IncludeModule('highloadblock');
            $hlBlock = HighloadBlockTable::getById(self::HL_BONUS_TRANSACTION)->fetch();
            $entity = HighloadBlockTable::compileEntity($hlBlock);
            $entityDataClass = $entity->getDataClass();

            $transactions = $entityDataClass::getList([
                'select' => ['UF_USER', 'UF_SIGN', 'UF_BONUS', 'UF_DATE'],
                'filter' => [
                    ">UF_DATE" => $date,
                    '=UF_USER' => $this->userId,
                    "=UF_SIGN" => 0
                ]
            ])->fetchAll();

            foreach ($transactions as $transaction) {
                $bonusCount += $transaction['UF_BONUS'];
            }

            return $bonusCount;
        } catch (\Exception $e) {
            AddMessage2Log($e->getMessage(), "letsrock.bonus");
            return 0;
        }
    }

    /**
     * Возвращает сумму потраченную пользователем в текущем месяце
     *
     * @param \Bitrix\Main\Type\DateTime $date
     *
     * @return int|mixed
     */
    protected function getMonthOrdersCost(\Bitrix\Main\Type\DateTime $date)
    {
        try {
            $result = 0;
            $orders = Helper::getOrdersByUser($this->userId, $date, "F");

            foreach ($orders as $order) {
                $result += $order['PRICE'];
            }

            return $result;
        } catch (\Exception $e) {
            AddMessage2Log($e->getMessage(), "letsrock.bonus");
            return 0;
        }
    }

    /**
     * Возвращает текущий месяц пользователЯ
     *
     * @return \Bitrix\Main\Type\DateTime
     */
    protected function getUserRegData()
    {
        $month = Helper::getMonthByUser($this->userId);

        try {
            //Разрабатываемая система подразумевает двойной первый месяц
            if ($month < 2) {
                $date = new DateTime(date('m/1/Y', strtotime('-1 months')));
            } else {
                $date = new DateTime(date('m/1/Y', strtotime('today')));
            }

            return \Bitrix\Main\Type\DateTime::createFromPhp($date);
        } catch (\Exception $e) {
            AddMessage2Log($e->getMessage(), "letsrock.bonus");
            return \Bitrix\Main\Type\DateTime::createFromTimestamp(time());
        }
    }

    /**
     * Возвращает количество бонусов которое нужно добавить пользователю по сумме покупки
     *
     * @param int $moneyCount Сумма покупки
     *
     * @return int
     */
    public function getBonusCountByPrice(int $moneyCount)
    {
        $startDate = $this->getUserRegData();
        $monthOrdersCost = $this->getMonthOrdersCost($startDate);
        $monthOrdersCost += $moneyCount; //Новая покупка ещё не входит месячную сумму
        $monthDepositBonuses = $this->getMonthDepositBonuses($startDate);
        $bonusCount = Helper::getBonusCountByMoney(intval($monthOrdersCost), $this->month, $this->bonusSystemByMonth);
        $bonusDiff = $bonusCount - $monthDepositBonuses;

        if ($bonusDiff > 0) {
            return $bonusDiff;
        } else {
            return 0;
        }
    }
}