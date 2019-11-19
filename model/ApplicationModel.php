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
    const TYPE_READ = "READ";
    const TYPE_MODIFY = "MODIFY";
    const TYPE_EXECUTE = "EXECUTE";
    const TYPE_DDL = "DDL";
    const TYPE_FILE = "FILE";

    const STATUS_APPLIED = "APPLIED";
    const STATUS_DENIED = "DENIED";
    const STATUS_CANCELLED = "CANCELLED";
    const STATUS_APPROVED = "APPROVED";
    const STATUS_EXECUTING = "EXECUTING";
    const STATUS_DONE = "DONE";
    const STATUS_ERROR = "ERROR";

    const PARALLELABLE_YES = "YES";
    const PARALLELABLE_NO = "NO";

    /**
     * @return string
     */
    protected function mappingTableName()
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