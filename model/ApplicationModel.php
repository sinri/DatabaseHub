<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018/12/6
 * Time: 5:03 PM
 */

namespace sinri\databasehub\model;


use Exception;
use sinri\ark\database\model\ArkDatabaseTableModel;
use sinri\ark\database\pdo\ArkPDO;
use sinri\databasehub\core\HubCore;

class ApplicationModel extends ArkDatabaseTableModel
{
    const TYPE_BATCH = "BATCH";// for Issue OC 842
    const TYPE_READ = "READ";
    const TYPE_MODIFY = "MODIFY";
    const TYPE_EXECUTE = "EXECUTE";
    const TYPE_DDL = "DDL";
    const TYPE_EXPORT_STRUCTURE = "EXPORT_STRUCTURE";
    const TYPE_DATABASE_COMPARE = "DATABASE_COMPARE";

    const STATUS_APPLIED = "APPLIED";
    const STATUS_DENIED = "DENIED";
    const STATUS_CANCELLED = "CANCELLED";
    const STATUS_APPROVED = "APPROVED";
    const STATUS_EXECUTING = "EXECUTING";
    const STATUS_DONE = "DONE";
    const STATUS_ERROR = "ERROR";
    const STATUS_STATIC = "STATIC";// for Issue OC 842

    const PARALLELABLE_YES = "YES";
    const PARALLELABLE_NO = "NO";

    /**
     * @return string
     */
    public function mappingTableName()
    {
        return "application";
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