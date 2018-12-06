<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018/12/4
 * Time: 6:14 PM
 */

namespace sinri\databasehub\model;


use sinri\ark\database\model\ArkDatabaseTableModel;
use sinri\ark\database\pdo\ArkPDO;
use sinri\databasehub\core\HubCore;

class AccountModel extends ArkDatabaseTableModel
{

    /**
     * @return string
     */
    protected function mappingTableName()
    {
        return "account";
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