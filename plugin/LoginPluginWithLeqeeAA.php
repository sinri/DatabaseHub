<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018/12/4
 * Time: 11:40 AM
 */

namespace sinri\databasehub\plugin;


use sinri\databasehub\entity\SessionEntity;
use sinri\databasehub\plugin\standard\LoginPlugin;

class LoginPluginWithLeqeeAA extends LoginPlugin
{

    /**
     * @param $username
     * @param $password
     * @return SessionEntity
     */
    public function validateAuthPair($username, $password)
    {
        // TODO: Implement validateAuthPair() method.
    }
}