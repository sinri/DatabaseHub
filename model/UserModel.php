<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018/12/4
 * Time: 11:43 AM
 */

namespace sinri\databasehub\model;


use Exception;
use sinri\ark\database\model\ArkDatabaseTableModel;
use sinri\ark\database\pdo\ArkPDO;
use sinri\databasehub\core\HubCore;

class UserModel extends ArkDatabaseTableModel
{
    const USER_TYPE_ADMIN = "ADMIN";
    const USER_TYPE_USER = "USER";

    const USER_STATUS_NORMAL = "NORMAL";
    const USER_STATUS_DISABLED = "DISABLED";
    const USER_STATUS_FROZEN = "FROZEN";

    const USER_ORG_FREE = "FREE";

    /**
     * @return string
     */
    protected function mappingTableName()
    {
        return "user";
    }

    /**
     * @return ArkPDO
     * @throws Exception
     */
    public function db()
    {
        return HubCore::getDB();
    }
}