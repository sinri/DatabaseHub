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
    /**
     * php runner.php  command/InitCommand Default
     */
    public function actionDefault()
    {
        echo __METHOD__ . " HERE" . PHP_EOL;
    }

    /**
     * php runner.php command/InitCommand CreateUser [USERNAME] [PASSWORD] [USER|ADMIN]
     * @param $username
     * @param $password
     * @param string $type
     */
    public function actionCreateUser($username, $password, $type = UserModel::USER_TYPE_USER)
    {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $user_id = (new UserModel())->insert([
            'username' => $username,
            'realname' => $username,
            'password' => $passwordHash,
            'user_type' => $type,
            'user_org' => UserModel::USER_ORG_FREE,
            'status' => UserModel::USER_STATUS_NORMAL,
        ]);
        echo "CREATED USER " . $user_id . PHP_EOL;
    }
}