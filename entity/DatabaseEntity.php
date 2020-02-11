<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018/12/4
 * Time: 6:16 PM
 */

namespace sinri\databasehub\entity;


use Exception;
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
     * @throws Exception
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

    public function advancedInfoForList()
    {
        $item = json_decode(json_encode($this), true);
        $item['accounts_count'] = count($this->accounts);
        $item['default_account'] = $this->defaultAccount ? $this->defaultAccount->username : "";
        return $item;
    }

    /**
     * @param AccountEntity $accountEntity
     * @return DatabaseWorkerEntity
     * @throws Exception
     */
    public function getWorkerEntity($accountEntity = null)
    {
        switch ($this->engine) {
            case DatabaseModel::ENGINE_ALIYUN_ADB:
                $worker = new DatabasePDOEntity($this, $accountEntity);
                break;
            case DatabaseModel::ENGINE_MYSQL:
            case DatabaseModel::ENGINE_ALIYUN_POLARDB:
            case DatabaseModel::ENGINE_ALIYUN_ADB3:
            default:
                $worker = new DatabaseMySQLiEntity($this, $accountEntity);
                break;
        }
        return $worker;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getMaxSQLLength()
    {
        $worker = $this->getWorkerEntity();
        $rows = $worker->selectRows("show variables like 'max_allowed_packet';");
        return $rows;
    }

    /**
     * @param $config
     * @return DatabaseEntity|null
     */
    public static function instanceByConfig($config)
    {
        if (empty($config)) {
            return null;
        }
        $database = new DatabaseEntity();
        $database->databaseId = 0;
        $database->databaseName = $config['database_name'];
        $database->host = $config['host'];
        $database->port = $config['port'];
        $database->status = 'NORMAL';
        $database->engine = $config['engine'];

        $database->defaultAccount = null;//$row['default_account_id'];

        $database->accounts = [];

        $account = AccountEntity::instanceByRow(['account_id' => 0, 'username' => $config['username'], 'password' => $config['password']]);
        $database->accounts[] = $account;
        $database->defaultAccount = $account;

        return $database;
    }
}