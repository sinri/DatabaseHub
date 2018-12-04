<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018/12/4
 * Time: 4:28 PM
 */

namespace sinri\databasehub\command;


use sinri\ark\cli\ArkCliProgram;
use sinri\databasehub\model\UserModel;

class InitCommand extends ArkCliProgram
{
    public function actionCreateAdminUser($username = 'admin', $password = '123456')
    {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $user_id = (new UserModel())->insert([
            'username' => $username,
            'realname' => $username,
            'password' => $passwordHash,
            'user_type' => UserModel::USER_TYPE_ADMIN,
            'user_org' => UserModel::USER_ORG_FREE,
            'status' => UserModel::USER_STATUS_NORMAL,
        ]);
        echo "CREATED USER " . $user_id . PHP_EOL;
    }
}