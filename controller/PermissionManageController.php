<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018/12/6
 * Time: 4:18 PM
 */

namespace sinri\databasehub\controller;


use sinri\databasehub\core\AbstractAuthController;
use sinri\databasehub\core\HubCore;
use sinri\databasehub\entity\UserEntity;
use sinri\databasehub\model\DatabaseModel;
use sinri\databasehub\model\PermissionModel;
use sinri\databasehub\model\UserModel;

class PermissionManageController extends AbstractAuthController
{
    /**
     * @throws \Exception
     */
    public function getUserPermission()
    {
        $this->onlyAdminCanDoThis();
        $user_id = $this->_readRequest("user_id", '', '/^[\d]+$/');
        $database_id_list = $this->_readRequest("database_id_list", []);

        if (empty($database_id_list)) throw new \Exception("No target databases given.");

        $dict = UserEntity::instanceByUserId($user_id)->getPermissionDictionary($database_id_list);

        $this->_sayOK(['dict' => array_values($dict)]);
    }

    /**
     * @throws \Exception
     */
    public function updateUserPermission()
    {
        $this->onlyAdminCanDoThis();
        $user_id = $this->_readRequest("user_id", '', '/^[\d]+$/');
        $database_id = $this->_readRequest("database_id", '', '/^[\d]+$/');
        $permissions = $this->_readRequest("permissions", []);

        $databaseRow = (new DatabaseModel())->selectRow(['database_id' => $database_id]);
        if (empty($databaseRow) || $databaseRow['status'] !== DatabaseModel::STATUS_NORMAL) {
            throw new \Exception("It is not a normal database.");
        }

        $userRow = (new UserModel())->selectRow(['user_id' => $user_id]);
        if (empty($userRow) || $userRow['status'] !== UserModel::USER_STATUS_NORMAL) {
            throw new \Exception("It is not a normal user.");
        }

        $insertion = [];
        if (!empty($permissions)) foreach ($permissions as $permission) {
            if (!in_array($permission, [
                PermissionModel::PERMISSION_APPROVE_DDL,
                PermissionModel::PERMISSION_APPROVE_MODIFY,
                PermissionModel::PERMISSION_APPROVE_READ,
                PermissionModel::PERMISSION_QUICK_QUERY,
                PermissionModel::PERMISSION_KILL,
            ])) {
                throw new \Exception("Unknown Permission");
            }
            $insertion[] = [
                'database_id' => $database_id,
                'user_id' => $user_id,
                'permission' => $permission,
            ];
        }

        HubCore::getDB()->beginTransaction();
        try {
            (new PermissionModel())->delete(['database_id' => $database_id, 'user_id' => $user_id]);
            if (!empty($insertion)) (new PermissionModel())->batchInsert($insertion);
            HubCore::getDB()->commit();
            $this->_sayOK();
        } catch (\Exception $exception) {
            HubCore::getDB()->rollBack();
            throw new \Exception("Cannot execute, rolled back.");
        }

    }

}