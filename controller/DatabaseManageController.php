<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018/12/4
 * Time: 6:30 PM
 */

namespace sinri\databasehub\controller;


use sinri\databasehub\core\AbstractAuthController;
use sinri\databasehub\entity\DatabaseEntity;
use sinri\databasehub\model\AccountModel;
use sinri\databasehub\model\DatabaseModel;

class DatabaseManageController extends AbstractAuthController
{
    /**
     * @param $database_info
     * @return array
     * @throws \Exception
     */
    private function parseAndCheckDatabaseInfo($database_info)
    {
        $keys = [
            "database_name",
            "host",
            "port",
            "status",
            "engine",
        ];

        $result = [];
        foreach ($keys as $key) {
            if (!isset($database_info[$key])) {
                throw new \Exception("Database Info is lack of field " . $key);
            }
            $result[$key] = $database_info[$key];
        }
        return $result;
    }

    /**
     * @throws \Exception
     */
    public function add()
    {
        $this->onlyAdminCanDoThis();
        $database_info = $this->_readRequest("database_info");
        $database_info = $this->parseAndCheckDatabaseInfo($database_info);
        $database_id = (new DatabaseModel())->insert($database_info);
        if (empty($database_id)) {
            throw new \Exception("Cannot add database item.");
        }
        $this->_sayOK(['database_id' => $database_id]);
    }

    /**
     * @throws \Exception
     */
    public function edit()
    {
        $this->onlyAdminCanDoThis();
        $database_id = $this->_readRequest("database_id", '', '/^[\d]+$/');
        $database_info = $this->_readRequest("database_info");
        $database_info = $this->parseAndCheckDatabaseInfo($database_info);
        $afx = (new DatabaseModel())->update(['database_id' => $database_id], $database_info);
        if (empty($afx)) {
            throw new \Exception("Cannot edit database item.");
        }
        $this->_sayOK(['updated' => $afx, 'database_id' => $database_id]);
    }

    /**
     * @throws \Exception
     */
    public function remove()
    {
        $this->onlyAdminCanDoThis();
        $database_id = $this->_readRequest("database_id", '', '/^[\d]+$/');
        $afx = (new DatabaseModel())->delete(['database_id' => $database_id]);
        if (empty($afx)) {
            throw new \Exception("Cannot edit database item.");
        }
        $this->_sayOK(['deleted' => $afx, 'database_id' => $database_id]);
    }

    public function commonList()
    {
        $list = (new DatabaseModel())->selectRows(['status' => DatabaseModel::STATUS_NORMAL]);
        $this->_sayOK(['list' => $list]);
    }

    /**
     * @throws \Exception
     */
    public function advanceList()
    {
        $this->onlyAdminCanDoThis();
        $list = (new DatabaseModel())->selectRows([
//            'status'=>DatabaseModel::USER_STATUS_NORMAL
        ]);
        $databases = [];
        foreach ($list as $item) {
            $databases[] = DatabaseEntity::instanceByRow($item);
        }
        $this->_sayOK(['list' => $databases]);
    }

    /**
     * @throws \Exception
     */
    public function editAccount()
    {
        $this->onlyAdminCanDoThis();
        $database_id = $this->_readRequest("database_id", '', '/^[\d]+$/');
        $databaseRow = (new DatabaseModel())->selectRow(['database_id' => $database_id]);
        if (empty($databaseRow)) {
            throw new \Exception("No such database");
        }
        $username = $this->_readRequest("username", '', '/^[\S]+$/');
        $password = $this->_readRequest("password");
        $afx = (new AccountModel())->replace([
            'database_id' => $database_id,
            'username' => $username,
            'password' => $password,
        ]);
        if (empty($afx)) {
            throw new \Exception("Cannot create account.");
        }

        $this->_sayOK(['afx' => $afx]);
    }

    /**
     * @throws \Exception
     */
    public function removeAccount()
    {
        $this->onlyAdminCanDoThis();
        $database_id = $this->_readRequest("database_id", '', '/^[\d]+$/');
        $account_id = $this->_readRequest("account_id", '', '/^[\d]+$/');
        $afx = (new AccountModel())->delete(['database_id' => $database_id, 'account_id' => $account_id]);
        if (empty($afx)) {
            throw new \Exception("Cannot remove account.");
        }
        (new DatabaseModel())->update(['database_id' => $database_id, 'default_account_id' => $account_id], ['default_account_id' => 0]);

        $this->_sayOK(['afx' => $afx]);
    }

    /**
     * @throws \Exception
     */
    public function setDefaultAccount()
    {
        $this->onlyAdminCanDoThis();
        $account_id = $this->_readRequest("account_id", '', '/^[\d]+$/');
        $database_id = $this->_readRequest("database_id", '', '/^[\d]+$/');

        $databaseRow = (new DatabaseModel())->selectRow(['database_id' => $database_id]);
        if (empty($databaseRow) || $databaseRow['status'] !== DatabaseModel::STATUS_NORMAL) {
            throw new \Exception("It is not a normal database.");
        }
        $accountRow = (new AccountModel())->selectRow(['account_id' => $account_id, 'database_id' => $database_id]);
        if (empty($accountRow)) {
            throw new \Exception("It is not a correct account.");
        }
    }

    /**
     * @throws \Exception
     */
    public function databaseAccountList()
    {
        $this->onlyAdminCanDoThis();
        $database_id = $this->_readRequest("database_id", '', '/^[\d]+$/');

        $databaseEntity = DatabaseEntity::instanceById($database_id);
        $accounts = $databaseEntity->getAccountList();
        $default = $databaseEntity->getDefaultAccount();

        $this->_sayOK(['accounts' => $accounts, 'default' => $default]);
    }
}