<?php

namespace Letsrock\Bonus;

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

\CModule::AddAutoloadClasses(
    "letsrock.bonus",
    array(
        "Letsrock\\Bonus\\Helper" => "bonus/helper.php",
        "Letsrock\\Bonus\\Deposit" => "bonus/models/deposit.php",
        "Letsrock\\Bonus\\Withdraw" => "bonus/models/withdraw.php",
        "Letsrock\\Bonus\\Transaction" => "bonus/models/transaction.php",
        "Letsrock\\Bonus\\Core" => "bonus/models/core.php",
        "Letsrock\\Bonus\\Information" => "bonus/models/information.php",
        "Letsrock\\Bonus\\Controller" => "bonus/controller.php",
    )
);
