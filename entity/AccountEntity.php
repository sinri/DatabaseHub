<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018/12/4
 * Time: 6:17 PM
 */

namespace sinri\databasehub\entity;


class AccountEntity
{
    public $accountId;
    public $username;
    protected $password;

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param $row
     * @return null|AccountEntity
     */
    public static function instanceByRow($row)
    {
        if (empty($row)) {
            return null;
        }
        $account = new AccountEntity();
        $account->accountId = $row['account_id'];
        $account->username = $row['username'];
        $account->password = $row['password'];
        return $account;
    }
}