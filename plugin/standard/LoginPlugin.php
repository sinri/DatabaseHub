<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018/12/4
 * Time: 11:40 AM
 */

namespace sinri\databasehub\plugin\standard;


use sinri\databasehub\entity\SessionEntity;
use sinri\databasehub\entity\UserEntity;

abstract class LoginPlugin
{
    /**
     * @param $username
     * @param $password
     * @return SessionEntity
     */
    abstract public function validateAuthPair($username, $password);

    /**
     * @param UserEntity $user
     * @return SessionEntity
     * @throws \Exception
     */
    final public function createSession($user)
    {
        return SessionEntity::createSessionForUser($user);
    }
}