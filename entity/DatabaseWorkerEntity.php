<?php

namespace sinri\databasehub\entity;

use Exception;

interface DatabaseWorkerEntity
{
    /**
     * @param DatabaseEntity $database
     * @param null|AccountEntity $account
     * @throws Exception
     */
    public function __construct($database, $account = null);

    public function close();

    /**
     * @param string $query
     * @param string $csvPath
     * @param string[] $error
     * @param string $charset
     * @return bool
     */
    public function exportCSV($query, $csvPath, &$error, $charset = 'gbk');

    /**
     * @param string $query
     * @param string $type
     * @param array[] $results
     * @param string[] $error
     * @return bool
     */
    public function executeMulti($query, $type, &$results, &$error);

    /**
     * @param string $query
     * @param string[] $error
     * @return bool
     */
    public function executeCall($query, &$error);

    /**
     * @param string $database
     * @param array $conditions
     * @param string $store_path
     * @param string[] $error
     * @return bool
     */
    public function executeExportStructure($database, $conditions, $store_path, &$error);

    /**
     * @param string $query
     * @param array $data
     * @param null|string[] $error
     * @param int $limit
     * @param int $duration
     * @return bool
     */
    public function quickQuery($query, &$data = [], &$error = null, $limit = 512, &$duration = 0);

    /**
     * @return bool|array
     */
    public function showFullProcessList();

    /**
     * @param $tid
     * @return bool
     */
    public function kill($tid);

    public function selectRows($sql);

    /**
     * @param $database
     * @return array
     */
    public function getStructureSimpleDetail($database);
}