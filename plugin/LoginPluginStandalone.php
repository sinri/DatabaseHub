<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018/12/4
 * Time: 3:44 PM
 */

namespace sinri\databasehub\plugin;


use Exception;
use sinri\ark\core\ArkHelper;
use sinri\databasehub\entity\SessionEntity;
use sinri\databasehub\entity\UserEntity;
use sinri\databasehub\model\UserModel;
use sinri\databasehub\plugin\standard\LoginPlugin;

class LoginPluginStandalone extends LoginPlugin
{

    /**
     * @param $username
     * @param $password
     * @return SessionEntity
     * @throws Exception
     */
    public function validateAuthPair($username, $password)
    {
        $row = (new UserModel())->selectRow(['username' => $username]);
        ArkHelper::quickNotEmptyAssert("Cannot Find User!", $row);

        if (!password_verify($password, $row['password'])) {
            throw new Exception("Password Error!");
        }

        $session = $this->createSession(UserEntity::instanceByRow($row));

        return $session;
    }
}