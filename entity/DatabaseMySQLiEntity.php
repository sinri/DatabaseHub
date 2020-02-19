<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018-12-10
 * Time: 21:03
 */

namespace sinri\databasehub\entity;


use Exception;
use sinri\ark\database\mysqli\ArkMySQLi;
use sinri\ark\database\mysqli\ArkMySQLiConfig;
use sinri\databasehub\core\HubCore;
use sinri\databasehub\model\ApplicationModel;

class DatabaseMySQLiEntity implements DatabaseWorkerEntity
{
    /**
     * @var ArkMySQLi
     */
    protected $mysqliAgent;

    private $charset;

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

        $this->charset = "UTF-8";

        $config = new ArkMySQLiConfig([
            ArkMySQLiConfig::CONFIG_TITLE => $database->databaseName,
            ArkMySQLiConfig::CONFIG_HOST => $database->host,
            ArkMySQLiConfig::CONFIG_PORT => $database->port,
            ArkMySQLiConfig::CONFIG_USERNAME => $account->username,
            ArkMySQLiConfig::CONFIG_PASSWORD => $account->getPassword(),
            //ArkMySQLiConfig::CONFIG_DATABASE => "",
            //ArkMySQLiConfig::CONFIG_CHARSET => "",
        ]);
        $this->mysqliAgent = (new ArkMySQLi($config));
        $this->mysqliAgent->connect();
    }

    public function close()
    {
        $this->mysqliAgent->getInstanceOfMySQLi()->close();
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
        $error = array();
        $sqlIdx = 1;


        try {
            $multiQueryDone = $this->mysqliAgent->getInstanceOfMySQLi()->multi_query($query);
            if (!$multiQueryDone) {
                throw new Exception("MySQLi multi_query cannot be done for query: " . $query);
            }
            $result = $this->mysqliAgent->getInstanceOfMySQLi()->store_result();
            if (!$result) {
                throw new Exception("MySQLi store_result failed, returned " . json_encode($result));
            }
        } catch (Exception $exception) {
            $error[$sqlIdx] = $this->mysqliAgent->getInstanceOfMySQLi()->error;
            if (!empty($result)) {
                $result->free();
            }
            $this->mysqliAgent->getInstanceOfMySQLi()->close();
            return false;
        }

        $csvFile = fopen($csvPath, 'w');
        if ($result->num_rows > 0) {
            //title
            $row = $result->fetch_array(MYSQLI_ASSOC);
            fputcsv($csvFile, array_keys($row));
        }
        do {
            array_walk($row, 'self::transCharset', array($this->charset, $charset));
            fputcsv($csvFile, array_values($row));
        } while ($row = $result->fetch_array(MYSQLI_ASSOC));

        fclose($csvFile);

        $result->free();
        $this->mysqliAgent->getInstanceOfMySQLi()->close();
        return true;
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
        $results = array();
        $error = array();

        $sqlIdx = 1;

        // 开启一个事务 保证中途任何语句发生错误都完全回滚
        // This function doesn't work with non transactional table types (like MyISAM or ISAM).
        $this->mysqliAgent->getInstanceOfMySQLi()->autocommit(false);
        try {
            if ($this->mysqliAgent->getInstanceOfMySQLi()->multi_query($query)) {
                do {
                    $result = [
                        'info' => $this->mysqliAgent->getInstanceOfMySQLi()->info,
                        'affected_rows' => $this->mysqliAgent->getInstanceOfMySQLi()->affected_rows,
                        'insert_id' => $this->mysqliAgent->getInstanceOfMySQLi()->insert_id,
                        'errno' => $this->mysqliAgent->getInstanceOfMySQLi()->errno,
                        'error' => $this->mysqliAgent->getInstanceOfMySQLi()->error,
                        'warning_count' => $this->mysqliAgent->getInstanceOfMySQLi()->warning_count,
                        'warnings' => [],
                    ];

                    if ($result['warning_count'] > 0) {
                        $w = $this->mysqliAgent->getInstanceOfMySQLi()->get_warnings();
                        do {
                            $result['warnings'][] = $w;//seems no use here
                        } while ($w->next());
                    }

                    $results[] = $result;

                    if (
                        $type == ApplicationModel::TYPE_MODIFY
                        && $result['affected_rows'] <= 0
                    ) {
                        $error[$sqlIdx] = "The No.{$sqlIdx} modify statement has no effect!";
                    }

                    if ($result['errno'] !== 0) {
                        $error[$sqlIdx] .= " MySQL Error: #" . $result['errno'] . " " . $result['error'];
                        HubCore::getLogger()->error(__METHOD__ . '@' . __LINE__ . " errno not zero and will ROLLBACK! " . $error[$sqlIdx]);
                        $this->mysqliAgent->getInstanceOfMySQLi()->rollback();
                        $this->mysqliAgent->getInstanceOfMySQLi()->close();
                        throw new Exception($error[$sqlIdx]);
                    }

                    $sqlIdx++;

                    // I wonder should the calls be multi called
                    //if ($type==ApplicationModel::TYPE_EXECUTE) {
                    //break;
                    //}
                } while (
                    $this->mysqliAgent->getInstanceOfMySQLi()->more_results()
                    && $this->mysqliAgent->getInstanceOfMySQLi()->next_result()
                    //&& !$this->mysqliAgent->getInstanceOfMySQLi()->errno
                );
                $fin_errno = $this->mysqliAgent->getInstanceOfMySQLi()->errno;
                $fin_error = $this->mysqliAgent->getInstanceOfMySQLi()->error;
                if ($fin_errno != 0) {
                    $error[$sqlIdx] = $fin_errno . " " . $fin_error;
                    throw new Exception($error[$sqlIdx]);
                }
            } else {
                HubCore::getLogger()->error(__METHOD__ . '@' . __LINE__ . " multi_query failed with unknown error", [
                    'errno' => $this->mysqliAgent->getInstanceOfMySQLi()->errno,
                    'error' => $this->mysqliAgent->getInstanceOfMySQLi()->error,
                ]);
                throw new Exception("multi_query failed. [" . $this->mysqliAgent->getInstanceOfMySQLi()->errno . "]" . $this->mysqliAgent->getInstanceOfMySQLi()->error);
            }

            HubCore::getLogger()->info(__METHOD__ . '@' . __LINE__ . " To commit");

            $this->mysqliAgent->getInstanceOfMySQLi()->commit();
            $this->mysqliAgent->getInstanceOfMySQLi()->close();
            return true;
        } catch (Exception $exception) {
            HubCore::getLogger()->error(__METHOD__ . '@' . __LINE__ . " Met exception, go to rollback and false would be returned. " . $exception->getMessage());
            //if ($this->mysqliAgent->getInstanceOfMySQLi()->errno) {
            $error[$sqlIdx] = $this->mysqliAgent->getInstanceOfMySQLi()->error;
            $this->mysqliAgent->getInstanceOfMySQLi()->rollback();
            $this->mysqliAgent->getInstanceOfMySQLi()->close();
            //}
        }
        return false;
    }

    /**
     * @param string $query
     * @param string[] $error
     * @return bool
     */
    public function executeCall($query, &$error)
    {
        $error = array();
        if (!$this->mysqliAgent->getInstanceOfMySQLi()->query($query)) {
            $error['1'] = "CALL failed: (" . $this->mysqliAgent->getInstanceOfMySQLi()->errno . ") " . $this->mysqliAgent->getInstanceOfMySQLi()->error;
            return false;
        }
        return true;
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

        //set execute timeout as 10 seconds
        //$this->mysqli->options(11 /*MYSQLI_OPT_READ_TIMEOUT*/, 10);

        try {
            $t1 = microtime(true);
            $multiQueryDone = $this->mysqliAgent->getInstanceOfMySQLi()->multi_query($query);
            $t2 = microtime(true);
            $duration = $t2 - $t1;
            if (!$multiQueryDone) {
                throw new Exception("MySQLi multi_query cannot be done for query: " . $query);
            }
            $result = $this->mysqliAgent->getInstanceOfMySQLi()->store_result();
            if (!$result) {
                throw new Exception("MySQLi store_result failed, returned " . json_encode($result));
            }

            HubCore::getLogger()->info("look here the result", ["result" => $result]);
            if ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                do {
                    if ($limit > 0 && count($data) >= $limit) {
                        $error[] = "SQL返回行数超过上限（{$limit}）！";
                        break;
                    }
                    $data[] = $row;
                } while ($row = $result->fetch_array(MYSQLI_ASSOC));
            }
            $result->free();
            $this->mysqliAgent->getInstanceOfMySQLi()->close();
            return true;
        } catch (Exception $exception) {
            $error[] = $this->mysqliAgent->getInstanceOfMySQLi()->error;
            if (!empty($result)) {
                $result->free();
            }
            $this->mysqliAgent->getInstanceOfMySQLi()->close();
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
     * @param int $tid
     * @return bool
     * @throws Exception
     */
    public function kill($tid)
    {
        return $this->mysqliAgent->getInstanceOfMySQLi()->kill($tid);
    }

    /**
     * @param string $sql
     * @return array
     * @throws Exception
     */
    public function selectRows($sql)
    {
        $error = [];
        $data = [];
        try {
            $multiQueryDone = $this->mysqliAgent->getInstanceOfMySQLi()->multi_query($sql);
            if (!$multiQueryDone) {
                throw new Exception("MySQLi multi_query cannot be done for query: " . $sql);
            }
            $result = $this->mysqliAgent->getInstanceOfMySQLi()->store_result();
            if (!$result) {
                throw new Exception("MySQLi store_result failed, returned " . json_encode($result));
            }

            HubCore::getLogger()->info("look here the result", ["result" => $result]);
            if ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                do {
                    $data[] = $row;
                } while ($row = $result->fetch_array(MYSQLI_ASSOC));
            }
            $result->free();
            $this->mysqliAgent->getInstanceOfMySQLi()->close();
            return $data;
        } catch (Exception $exception) {
            $error[] = $this->mysqliAgent->getInstanceOfMySQLi()->error;
            if (!empty($result)) {
                $result->free();
            }
            $this->mysqliAgent->getInstanceOfMySQLi()->close();
            throw new Exception(implode("|", $error));
        }
    }

    /**
     * @param $sql
     * @param null $field
     * @return array
     */
    public function getCol($sql, $field = null)
    {
        $stmt = $this->mysqliAgent->getInstanceOfMySQLi()->query($sql);
        if (!$stmt) return  [];
        $rows = $stmt->fetch_all();
        if ($field === null) $field = 0;
        $col = array_column($rows, $field);
        return $col;
    }

    /**
     * @param $sql
     * @param $column_index
     * @param $reset_auto_increment
     * @param $drop_if_exist
     * @param $drop_type
     * @param $drop_name
     * @return string
     */
    public function fetchSQLResult($sql, $column_index = 1, $reset_auto_increment = true, $drop_if_exist = true, $drop_type = '', $drop_name = '')
    {
        $lines = $this->getCol($sql, $column_index);
        if (empty($lines)) return "";
        $text = "-- BLOCK BEGIN --" . PHP_EOL;

        $text .= "-- " . $sql . PHP_EOL;

        $sql = "";
        foreach ($lines as $lineNo => $line) {
            if ($drop_type === 'TABLE' && $reset_auto_increment) {
                $line = preg_replace('/\s+AUTO_INCREMENT=\d+\s+/', ' ', $line);
            }
            $line = preg_replace('/\s+DEFINER=`[A-Za-z0-9_]+`@`[A-Za-z0-9_%]+`\s+/', ' ', $line);
            $sql .= $line . PHP_EOL;
        }
        if ($drop_if_exist && !empty($drop_type)) {
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
     */
    public function executeExportStructure($database, $conditions, $store_path, &$error)
    {
        $snapshot = '';
        $drop_if_exist = $conditions['drop_if_exist'];
        $error = array();
        if ($conditions['show_create_database']) {
            $snapshot .= $this->fetchSQLResult('show create database `' . $database . '`;');
        }

        $snapshot .= "use `" . $database . "`;" . PHP_EOL;

        // tables
        if (!empty($conditions['show_create_table'])) {
            if ($conditions['show_create_table'] != 'ALL') {
                $tableNames = $conditions['show_create_table'];
            } else {
                $sql = "show tables in `{$database}`;";
                $tableNames = $this->getCol($sql);
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
                $functionNames = $this->getCol($sql, 1);
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
                $procedureNames = $this->getCol($sql, 1);
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
                $triggerNames = $this->getCol($sql);
            }
            if (!empty($triggerNames)) {
                foreach ($triggerNames as $triggerName) {
                    $sql = "show create trigger `{$database}`.`{$triggerName}`;";
                    $snapshot .= $this->fetchSQLResult($sql, 2, false,
                        $drop_if_exist, 'TRIGGER', "`{$database}`.`{$triggerName}`");
                }
            }
        }

        $this->mysqliAgent->getInstanceOfMySQLi()->close();
        return file_put_contents($store_path, $snapshot);
    }

    /**
     * @param string $database
     * @return array
     */
    public function getStructureSimpleDetail($database)
    {
        $data = [];
        // 获取rds全部数据库
        if (empty($database)) {
            // schemas
            $sql = "show databases;";
            $data['schemas'] = array_diff($this->getCol($sql), ["information_schema", "mysql", "performance_schema", "sys"]);
            return $data;
        }

        // tables
        $sql = "show tables in `{$database}`;";
        $data['tables'] = $this->getCol($sql);

        // functions
        $sql = "SHOW FUNCTION STATUS where db='{$database}';";
        $data['functions'] = $this->getCol($sql, 1);

        // procedures
        $sql = "SHOW PROCEDURE STATUS where db='{$database}';";
        $data['procedures'] = $this->getCol($sql, 1);

        //triggers
        $sql = "show triggers in `{$database}`;";
        $data['triggers'] = $this->getCol($sql);

        return $data;
    }

}