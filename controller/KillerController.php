<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018-12-11
 * Time: 14:46
 */

namespace sinri\databasehub\controller;


use sinri\ark\core\ArkHelper;
use sinri\databasehub\core\AbstractAuthController;
use sinri\databasehub\entity\AccountEntity;
use sinri\databasehub\entity\DatabaseEntity;
use sinri\databasehub\entity\DatabaseMySQLiEntity;
use sinri\databasehub\model\AccountModel;
use sinri\databasehub\model\DatabaseModel;
use sinri\databasehub\model\PermissionModel;
use sinri\databasehub\model\UserModel;

class KillerController extends AbstractAuthController
{
    /**
     * @throws \Exception
     */
    public function permittedDatabases()
    {
        $list = [];
        if ($this->session->user->userType === UserModel::USER_TYPE_ADMIN) {
            // admin see all
            $rows = (new DatabaseModel())->selectRows(['status' => DatabaseModel::STATUS_NORMAL]);
            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $list[] = DatabaseEntity::instanceByRow($row);
                }
            }
        } else {
            $rows = (new PermissionModel())->selectRows([
                'user_id' => $this->session->user->userId,
                'permission' => PermissionModel::PERMISSION_KILL
            ]);

            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $list[] = DatabaseEntity::instanceById($row['database_id']);
                }
            }
        }
        $this->_sayOK(['list' => $list]);
    }

    /**
     * @throws \Exception
     */
    public function showProcessList()
    {
        $database_id = $this->_readRequest("database_id", 0, '/^\d+$/');
        $databaseEntity = DatabaseEntity::instanceById($database_id);

        if ($this->session->user->userType !== UserModel::USER_TYPE_ADMIN) {
            $permissions = $this->session->user->getPermissionDictionary($database_id);
            $permissions = ArkHelper::readTarget($permissions, [$database_id, 'permissions']);
            if (!in_array(PermissionModel::PERMISSION_KILL, $permissions)) {
                throw new \Exception("You cannot kill!");
            }
        }

        $data = (new DatabaseMySQLiEntity($databaseEntity))->showFullProcessList();

        if (!$data) {
            throw new \Exception("Cannot list processes!");
        }
        $this->_sayOK([
            "list" => $data,
        ]);
    }

    /**
     * @throws \Exception
     */
    public function kill()
    {
        $database_id = $this->_readRequest("database_id", 0, '/^\d+$/');
        $username = $this->_readRequest("username", 0, '/^[\S]+$/');
        $tid = $this->_readRequest("tid", 0, '/^\d+$/');
        if (empty($tid)) throw new \Exception("tid should not be zero");

        $databaseEntity = DatabaseEntity::instanceById($database_id);

        $accountRow = (new AccountModel())->selectRow(['username' => $username, 'database_id' => $database_id]);
        if (empty($accountRow)) {
            throw new \Exception("No such account");
        }
        $accountEntity = AccountEntity::instanceByRow($accountRow);

        if ($this->session->user->userType !== UserModel::USER_TYPE_ADMIN) {
            $permissions = $this->session->user->getPermissionDictionary($database_id);
            $permissions = ArkHelper::readTarget($permissions, [$database_id, 'permissions']);
            if (!in_array(PermissionModel::PERMISSION_KILL, $permissions)) {
                throw new \Exception("You cannot kill!");
            }
        }

        $done = (new DatabaseMySQLiEntity($databaseEntity, $accountEntity))->kill($tid);
        if (!$done) throw new \Exception("Cannot kill " . $tid);
        $this->_sayOK(["done" => $done]);
    }
}