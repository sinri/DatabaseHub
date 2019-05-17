<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018/12/4
 * Time: 11:42 AM
 */

namespace sinri\databasehub\entity;


use Exception;
use sinri\ark\core\ArkHelper;
use sinri\databasehub\model\PermissionModel;
use sinri\databasehub\model\UserModel;

class UserEntity
{
    public $userId;
    public $username;
    public $realname;
    public $email;
    public $userType;
    protected $passwordHash;
    public $status;
    public $userOrg;
    public $asKiller;

    /**
     * @param array $row
     * @return UserEntity
     */
    public static function instanceByRow($row)
    {
        $user = new UserEntity();
        $user->userId = $row['user_id'];
        $user->username = $row['username'];
        $user->realname = $row['realname'];
        $user->email = $row['email'];
        $user->userType = $row['user_type'];
        $user->passwordHash = $row['password'];
        $user->status = $row['status'];
        $user->userOrg = $row['user_org'];

        // update asKiller
        $killableDatabaseIdList = (new PermissionModel())->selectRowsForFieldsWithSort(["database_id"], ["permission" => "KILL", "user_id" => $user->userId]);
        if (empty($killableDatabaseIdList)) {
            $user->asKiller = false;
        } else {
            $user->asKiller = true;
        }

        return $user;
    }

    /**
     * @param int $userId
     * @return UserEntity
     * @throws Exception
     */
    public static function instanceByUserId($userId)
    {
        $row = (new UserModel())->selectRow(['user_id' => $userId]);
        ArkHelper::quickNotEmptyAssert("No such user!", $row);
        return self::instanceByRow($row);
    }

    /**
     * @param null|int[] $databases
     * @return String[][] e.g. [ DATABASE_ID => [ "database_info"=>[...],"permissions"=>[PERMISSION_A, ...] ], ...]
     * @throws Exception
     */
    public function getPermissionDictionary($databases = null)
    {
        $conditions = ['user_id' => $this->userId];
        if ($databases != null) {
            $conditions['database_id'] = $databases;
        }
        $rows = (new PermissionModel())->selectRows($conditions);
        if (empty($rows)) return [];
        $dict = [];
        foreach ($rows as $row) {
            if (!isset($dict[$row['database_id']])) {
                $databaseEntity = DatabaseEntity::instanceById($row['database_id']);
                $dict[$row['database_id']] = [
                    'database_id' => $row['database_id'],
                    'database_info' => $databaseEntity,
                    'permissions' => [],
                ];
            }
            $dict[$row['database_id']]['permissions'][] = $row['permission'];
        }
        return $dict;
    }

    /**
     * 获取全部用户
     * @return array
     */
    public function getAllUser()
    {
        $all_user =  (new UserModel())->selectRows([]);
        return array_map(function ($row) {
            $entity = self::instanceByRow($row);
            $entity->passwordHash = null;
            return $entity;
        }, $all_user);
    }
}