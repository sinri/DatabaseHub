<?php


namespace sinri\databasehub\entity;


use Exception;
use sinri\ark\database\pdo\ArkPDO;
use sinri\ark\database\pdo\ArkPDOConfig;
use sinri\databasehub\core\HubCore;
use sinri\databasehub\core\SQLChecker;
use sinri\databasehub\model\DatabaseModel;

class DatabasePDOEntity implements DatabaseWorkerEntity
{
    protected $arkPDO;
    protected $charset;

    /**
     * @param DatabaseEntity $database
     * @param null|AccountEntity $account
     * @throws Exception
     */
    public function __construct($database, $account = null)
    {
        if ($account === null) {
            $account = $database->getDefaultAccount();
        }

        $dict = [
            ArkPDOConfig::CONFIG_TITLE => $database->databaseName,
            ArkPDOConfig::CONFIG_HOST => $database->host,
            ArkPDOConfig::CONFIG_PORT => $database->port,
            ArkPDOConfig::CONFIG_USERNAME => $account->username,
            ArkPDOConfig::CONFIG_PASSWORD => $account->getPassword(),
        ];

        if ($database->engine === DatabaseModel::ENGINE_ALIYUN_ADB) {
            $dict[ArkPDOConfig::CONFIG_DATABASE] = $database->databaseName;
            $dict[ArkPDOConfig::CONFIG_OPTIONS] = [];
        }
        HubCore::getLogger()->debug(__METHOD__ . '@' . __LINE__, ['dict' => $dict]);

        $this->arkPDO = new ArkPDO(new ArkPDOConfig($dict));
        $this->charset = "UTF-8";
        $this->arkPDO->connect();
    }

    public function close()
    {
        // no need for pdo
    }

    /**
     * @param string $query
     * @param string $csvPath
     * @param string[] $error
     * @param string $charset
     * @return bool
     */
    public function exportCSV($query, $csvPath, &$error, $charset = 'gbk')
    {
        try {
            // streaming it!
            $csvFile = fopen($csvPath, 'w');

            $this->arkPDO->getAllAsStream($query, function ($row, $index) use ($charset, &$csvFile) {
                if ($index === 1) {
                    //title row
                    fputcsv($csvFile, array_keys($row));
                }
                if ($this->charset != $charset) {
                    array_walk($row, 'self::transCharset', array($this->charset, $charset));
                }
                fputcsv($csvFile, array_values($row));

                return true;
            });

            fclose($csvFile);
            return true;
        } catch (Exception $e) {
            $error[] = $e->getMessage();
            return false;
        }
    }

    private static function transCharset(&$item, $key, $charsets)
    {
        $srcCharset = $charsets[0];
        $dstCharset = $charsets[1];
        $item = mb_convert_encoding($item, $dstCharset, $srcCharset);
    }

    /**
     * @param string $query
     * @param string $type
     * @param array[] $results
     * @param string[] $error
     * @return bool
     */
    public function executeMulti($query, $type, &$results, &$error)
    {
        $results = [];
        $error = [];
        $sqlList = SQLChecker::split($query);
        if (empty($sqlList)) {
            $error = ["SQL EMPTY"];
            HubCore::getLogger()->error(__METHOD__ . '@' . __LINE__ . " SQL EMPTY");
            return false;
        }
        try {
            return $this->arkPDO->executeInTransaction(function () use (&$results, &$error, $sqlList) {
                foreach ($sqlList as $sqlIndex => $sql) {
                    $afx = $this->arkPDO->exec($sql);
                    if ($afx === false) {
                        $error[$sqlIndex] = $this->arkPDO->getPDOErrorDescription();
                        throw new Exception("One SQL Failed: " . $error[$sqlIndex], ['sql' => $sql]);
                    }
                    $results[$sqlIndex] = [
                        'info' => 'PDO NOT SUPPORT',
                        'affected_rows' => $afx,
                        'insert_id' => $this->arkPDO->getLastInsertID(),
                        'errno' => $this->arkPDO->getPDOErrorCode(),
                        'error' => $this->arkPDO->getPDOErrorInfo(),
                        'warning_count' => 'PDO NOT SUPPORT',
                        'warnings' => [],
                    ];
                }
                return true;
            });
        } catch (Exception $e) {
            HubCore::getLogger()->error(__METHOD__ . '@' . __LINE__ . " ERROR CAUGHT", ['e' => $e]);
            return false;
        }
    }

    /**
     * @param string $query
     * @param string[] $error
     * @return bool
     */
    public function executeCall($query, &$error)
    {
        $error = ['PDO NOT SUPPORT',];
        return false;
    }

    /**
     * @param string $query
     * @param array $data
     * @param null|string[] $error
     * @param int $limit
     * @param int $duration
     * @return bool
     */
    public function quickQuery($query, &$data = [], &$error = null, $limit = 512, &$duration = 0)
    {
        $error = [];
        $data = [];

        try {
            HubCore::getLogger()->info(__METHOD__ . '@' . __LINE__ . " QUICK QUERY SQL: " . $query);
            $this->arkPDO->getAllAsStream($query, function ($row, $rowIndex) use (&$data, &$error, $limit) {
                if ($limit > 0 && count($data) >= $limit) {
                    $error[] = "SQL返回行数超过上限（{$limit}）！";
                    return false;
                }
                HubCore::getLogger()->info(__METHOD__ . '@' . __LINE__ . ' fetched row ' . $rowIndex . ': ' . json_encode($row));
                $data[] = $row;
                return true;
            });
            return true;
        } catch (Exception $exception) {
            $error[] = $this->arkPDO->getPDOErrorDescription();
            return false;
        }
    }

    /**
     * @return bool|array
     */
    public function showFullProcessList()
    {
        $done = $this->quickQuery("show full processlist", $data, $error, 0, $duration);
        if (!$done) return false;

        return $data;
    }

    /**
     * @param $tid
     * @return bool
     */
    public function kill($tid)
    {
        return false;
    }

    /**
     * @param $sql
     * @return array
     * @throws Exception
     */
    public function selectRows($sql)
    {
        return $this->arkPDO->safeQueryAll($sql);
    }

    /**
     * @param $sql
     * @param $column_index
     * @param $reset_auto_increment
     * @param $drop_if_exist
     * @param $drop_type
     * @param $drop_name
     * @return string
     * @throws Exception
     */
    public function fetchSQLResult($sql, $column_index = 1, $reset_auto_increment = true, $drop_if_exist = true, $drop_type = '', $drop_name = '')
    {
        $lines = $this->arkPDO->getCol($sql, $column_index);
        if (empty($lines)) return "";
        $text = "-- BLOCK BEGIN --" . PHP_EOL;

        $text .= "-- " . $sql . PHP_EOL;

        $sql = "";
        foreach ($lines as $lineNo => $line) {
            if ($drop_type === 'TABLE' && $reset_auto_increment) {
                $line = preg_replace('/\s+AUTO_INCREMENT=\d+\s+/', ' ', $line);
            }
            $line = preg_replace('/\s+DEFINER=`[A-Za-z0-9_]+`@`[A-Za-z0-9_]+`\s+/', ' ', $line);
            $sql .= $line . PHP_EOL;
        }
        if ($drop_if_exist) {
            if ($drop_type == 'TABLE') {
                if (strpos($sql, 'CREATE TABLE') !== 0) {
                    $drop_type = 'VIEW';
                }
            }
            $text .= "DROP {$drop_type} IF EXISTS {$drop_name};" . PHP_EOL;
        }
        $text .= $sql;
        $text .= "-- BLOCK END --" . PHP_EOL . ";" . PHP_EOL;
        return $text;
    }

    /**
     * @param string $database
     * @param array $conditions
     * ['drop_if_exist'=> false,
     * 'show_create_database' => false,
     * 'reset_auto_increment' => false,
     * 'show_create_table' => [],
     * 'show_create_function' => [],
     * 'show_create_procedure' => [],
     * 'show_create_trigger' => []]
     * @param string $store_path
     * @param string[] $error
     * @return bool
     * @throws Exception
     */
    public function executeExportStructure($database, $conditions, $store_path, &$error)
    {
        $snapshot = '';
        $drop_if_exist = $conditions['drop_if_exist'];
        $error = array();
        if ($conditions['show_create_database']) {
            $snapshot .= $this->fetchSQLResult('show create database `' . $database . '`;');
        }

        $snapshot .= "use " . $database . ";" . PHP_EOL;

        // tables
        if (!empty($conditions['show_create_table'])) {
            if ($conditions['show_create_table'] != 'ALL') {
                $tableNames = $conditions['show_create_table'];
            } else {
                $sql = "show tables in `{$database}`;";
                $tableNames = $this->arkPDO->getCol($sql);
            }
            if (!empty($tableNames)) {
                foreach ($tableNames as $tableName) {
                    $sql = "show create table `{$database}`.`{$tableName}`;";
                    $snapshot .= $this->fetchSQLResult($sql, 1, $conditions['reset_auto_increment'],
                        $drop_if_exist, 'TABLE', "`{$database}`.`{$tableName}`");
                }
            }
        }

        // functions
        if (!empty($conditions['show_create_function'])) {
            if ($conditions['show_create_function'] != 'ALL') {
                $functionNames = $conditions['show_create_function'];
            } else {
                $sql = "SHOW FUNCTION STATUS where db='{$database}';";
                $functionNames = $this->arkPDO->getCol($sql, 1);
            }
            if (!empty($functionNames)) {
                foreach ($functionNames as $functionName) {
                    $sql = "show create function `{$database}`.`{$functionName}`;";
                    $snapshot .= $this->fetchSQLResult($sql,2, false,
                        $drop_if_exist, 'FUNCTION', "`{$database}`.`{$functionName}`");
                }
            }
        }

        // procedures
        if (!empty($conditions['show_create_procedure'])) {
            if ($conditions['show_create_procedure'] != 'ALL') {
                $procedureNames = $conditions['show_create_procedure'];
            } else {
                $sql = "SHOW PROCEDURE STATUS where db='{$database}';";
                $procedureNames = $this->arkPDO->getCol($sql, 1);
            }
            if (!empty($procedureNames)) {
                foreach ($procedureNames as $procedureName) {
                    $sql = "show create procedure `{$database}`.`{$procedureName}`;";
                    $snapshot .= $this->fetchSQLResult($sql, 2, false,
                        $drop_if_exist, 'PROCEDURE', "`{$database}`.`{$procedureName}`");
                }
            }
        }

        //triggers
        if (!empty($conditions['show_create_trigger'])) {
            if ($conditions['show_create_trigger'] != 'ALL') {
                $triggerNames = $conditions['show_create_trigger'];
            } else {
                $sql = "show triggers in `{$database}`;";
                $triggerNames = $this->arkPDO->getCol($sql);
            }
            if (!empty($triggerNames)) {
                foreach ($triggerNames as $triggerName) {
                    $sql = "show create trigger `{$database}`.`{$triggerName}`;";
                    $snapshot .= $this->fetchSQLResult($sql, 2, false,
                        $drop_if_exist, 'TRIGGER', "`{$database}`.`{$triggerName}`");
                }
            }
        }

        return file_put_contents($store_path, $snapshot);
    }

    /**
     * @param string $database
     * @return array
     * @throws Exception
     */
    public function getStructureSimpleDetail($database)
    {
        $data = [];
        // tables
        $sql = "show tables in `{$database}`;";
        $data['tables'] = $this->arkPDO->getCol($sql);

        // functions
        $sql = "SHOW FUNCTION STATUS where db='{$database}';";
        $data['functions'] = $this->arkPDO->getCol($sql, 1);

        // procedures
        $sql = "SHOW PROCEDURE STATUS where db='{$database}';";
        $data['procedures'] = $this->arkPDO->getCol($sql, 1);

        //triggers
        $sql = "show triggers in `{$database}`;";
        $data['triggers'] = $this->arkPDO->getCol($sql);

        return $data;
    }

}