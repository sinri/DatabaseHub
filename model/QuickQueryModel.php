<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018-12-11
 * Time: 10:07
 */

namespace sinri\databasehub\model;


use Exception;
use sinri\ark\database\model\ArkDatabaseTableModel;
use sinri\ark\database\pdo\ArkPDO;
use sinri\databasehub\core\HubCore;

class QuickQueryModel extends ArkDatabaseTableModel
{
    const TYPE_SYNC = "SYNC";
    const TYPE_ASYNC = "ASYNC";

    /**
     * @return string
     */
    protected function mappingTableName()
    {
        return "quick_query";
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