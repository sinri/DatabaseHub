<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 2/27/19
 * Time: 10:53 AM
 */

namespace sinri\databasehub\model;


use sinri\ark\database\model\ArkDatabaseTableModel;
use sinri\ark\database\pdo\ArkPDO;
use sinri\databasehub\core\HubCore;

class DingtalkScanLoginSessionModel extends ArkDatabaseTableModel
{
    /**
     * @return  string
     */
    protected function mappingTableName()
    {
        return "dingtalk_scan_login_session";
    }

    /**
     * @return ArkPDO
     */
    public function db()
    {
        return HubCore::getDB();
    }
}