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

    public function getMappedSubApplicationIdList($batch_application_id)
    {
        $rows = $this->selectRows(['batch_id' => $batch_application_id]);
        if (empty($rows)) return [];
        return array_column($rows, 'application_id');
    }
}