<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018/12/4
 * Time: 5:57 PM
 */

namespace sinri\databasehub\model;


use sinri\ark\database\model\ArkDatabaseTableModel;
use sinri\ark\database\pdo\ArkPDO;
use sinri\databasehub\core\HubCore;

class DatabaseModel extends ArkDatabaseTableModel
{
    const ENGINE_MYSQL = "MYSQL";

    const STATUS_NORMAL = "NORMAL";
    const STATUS_DISABLED = "DISABLED";

    /**
     * @return string
     */
    protected function mappingTableName()
    {
        return "database";
    }

    /**
     * @return ArkPDO
     * @throws \Exception
     */
    public function db()
    {
        return HubCore::getDB();
    }
}