<?php


namespace sinri\databasehub\model;


use sinri\ark\database\model\ArkDatabaseTableModel;
use sinri\databasehub\core\HubCore;

class BatchApplicationMappingModel extends ArkDatabaseTableModel
{

    public function mappingTableName()
    {
        return "batch_application_mapping";
    }

    public function db()
    {
        return HubCore::getDB();
    }
}