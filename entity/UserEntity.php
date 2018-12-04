<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018/12/4
 * Time: 11:42 AM
 */

namespace sinri\databasehub\entity;


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

        return $user;
    }

    /**
     * @param int $userId
     * @return UserEntity
     */
    public static function instanceByUserId($userId)
    {
        $row = (new UserModel())->selectRow(['user_id' => $userId]);
        return self::instanceByRow($row);
    }
}