<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018/12/4
 * Time: 2:16 PM
 */

namespace sinri\databasehub\core;


use sinri\ark\core\ArkHelper;
use sinri\ark\database\pdo\ArkPDO;
use sinri\ark\database\pdo\ArkPDOConfig;

class HubCore
{
    /**
     * @param array|string $keychain
     * @param null $default
     * @return mixed|null
     */
    public static function getConfig($keychain, $default = null)
    {
        $config = [];
        require __DIR__ . '/../config/config.php';
        return ArkHelper::readTarget($config, $keychain, $default);
    }

    /**
     * @var ArkPDO
     */
    protected static $mainDB;

    /**
     * @return ArkPDO
     * @throws \Exception
     */
    public static function getDB()
    {
        if (!self::$mainDB) {
            $pdoConfig = new ArkPDOConfig(self::getConfig(['pdo']));
            self::$mainDB = new ArkPDO($pdoConfig);
            self::$mainDB->connect();
        }
        return self::$mainDB;
    }

}