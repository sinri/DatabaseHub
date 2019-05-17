<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018/12/6
 * Time: 4:13 PM
 */

namespace sinri\databasehub\model;


use Exception;
use sinri\ark\database\model\ArkDatabaseTableModel;
use sinri\ark\database\pdo\ArkPDO;
use sinri\databasehub\core\HubCore;

class PermissionModel extends ArkDatabaseTableModel
{

    const PERMISSION_APPROVE_READ = "READ";
    const PERMISSION_APPROVE_MODIFY = "MODIFY";
    const PERMISSION_APPROVE_DDL = "DDL";
    const PERMISSION_APPROVE_EXECUTE = "EXECUTE";
    const PERMISSION_QUICK_QUERY = "QUICK_QUERY";
    const PERMISSION_KILL = "KILL";

    /**
     * @return string
     */
    protected function mappingTableName()
    {
        return "permission";
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