<?php
/**
 * Created by PhpStorm.
 * User: caroltc
 * Date: 19-9-29
 * Time: 上午10:59
 */

namespace sinri\databasehub\model;


use Exception;
use sinri\ark\database\model\ArkDatabaseTableModel;
use sinri\ark\database\pdo\ArkPDO;
use sinri\databasehub\core\HubCore;

class QueryNotepadModel extends ArkDatabaseTableModel
{

    /**
     * @return string
     */
    public function mappingTableName()
    {
        return 'query_notepad';
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