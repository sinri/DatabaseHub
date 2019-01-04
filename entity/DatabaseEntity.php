<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018/12/4
 * Time: 6:16 PM
 */

namespace sinri\databasehub\entity;


use sinri\ark\core\ArkHelper;
use sinri\databasehub\model\AccountModel;
use sinri\databasehub\model\DatabaseModel;

class DatabaseEntity
{
    public $databaseId;
    public $databaseName;
    public $host;
    public $port;
    public $status;
    public $engine;
    /**
     * @var AccountEntity | null
     */
    protected $defaultAccount = null;
    /**
     * @var AccountEntity[]
     */
    protected $accounts;

    /**
     * @return AccountEntity[]
     */
    public function getAccountList()
    {
        return $this->accounts;
    }

    /**
     * @return AccountEntity|null
     */
    public function getDefaultAccount()
    {
        return $this->defaultAccount;
    }

    /**
     * @param $databaseId
     * @return DatabaseEntity
     * @throws \Exception
     */
    public static function instanceById($databaseId)
    {
        $row = (new DatabaseModel())->selectRow(['database_id' => $databaseId]);
        ArkHelper::quickNotEmptyAssert("No such database!", $row);
        return self::instanceByRow($row);
    }

    /**
     * @param $row
     * @return DatabaseEntity|null
     */
    public static function instanceByRow($row)
    {
        if (empty($row)) {
            return null;
        }
        $database = new DatabaseEntity();
        $database->databaseId = $row['database_id'];
        $database->databaseName = $row['database_name'];
        $database->host = $row['host'];
        $database->port = $row['port'];
        $database->status = $row['status'];
        $database->engine = $row['engine'];

        $database->defaultAccount = null;//$row['default_account_id'];

        $database->accounts = [];

        $accountRows = (new AccountModel())->selectRows(['database_id' => $database->databaseId]);
        if (!empty($accountRows)) {
            foreach ($accountRows as $accountRow) {
                $account = AccountEntity::instanceByRow($accountRow);
                $database->accounts[] = $account;
                if ($account->accountId == $row['default_account_id']) {
                    $database->defaultAccount = $account;
                }
            }
        }

        return $database;
    }
}