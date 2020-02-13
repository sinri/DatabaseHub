<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018/12/4
 * Time: 2:22 PM
 */

namespace sinri\databasehub\model;


use Exception;
use sinri\ark\database\model\ArkDatabaseTableModel;
use sinri\ark\database\pdo\ArkPDO;
use sinri\databasehub\core\HubCore;

class SessionModel extends ArkDatabaseTableModel
{

    /**
     * @return string
     */
    public function mappingTableName()
    {
        return "session";
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