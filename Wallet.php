<?php
/**
 * @creator David
 * @project Udictate
 */


namespace User;

require_once "DB.class.php";


class Wallet extends DB
{
    public $userId;
    public $amount;
    public $balance;
    public $walletData;
    public $transactionsTable;
    public $transactionId;
    public $dateOfTransaction;
    public $transactionAmount;
    public $action;

    /**
     * todo Continue when we have a working network
     */

}