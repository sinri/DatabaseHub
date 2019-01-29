<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018-12-10
 * Time: 21:03
 */

namespace sinri\databasehub\entity;


use sinri\ark\database\mysqli\ArkMySQLi;
use sinri\ark\database\mysqli\ArkMySQLiConfig;
use sinri\databasehub\core\HubCore;
use sinri\databasehub\model\ApplicationModel;

class DatabaseMySQLiEntity
{
    /**
     * @var ArkMySQLi
     */
    protected $mysqliAgent;

    private $charset;

    /**
     * @param DatabaseEntity $database
     * @param null|AccountEntity $account
     * @throws \Exception
     */
    public function __construct($database, $account = null)
    {
        if ($account === null) {
            $account = $database->getDefaultAccount();
        }

        $this->charset = "utf8";

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

        return $this->mysqliAgent;
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
                throw new \Exception("MySQLi multi_query cannot be done for query: " . $query);
            }
            $result = $this->mysqliAgent->getInstanceOfMySQLi()->store_result();
            if (!$result) {
                throw new \Exception("MySQLi store_result failed, returned " . json_encode($result));
            }
        } catch (\Exception $exception) {
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
     * @param int[] $affected
     * @param string[] $error
     * @return bool
     */
    public function executeMulti($query, $type, &$affected, &$error)
    {
        $affected = array();
        $error = array();

        $sqlIdx = 1;

        // 开启一个事务 保证中途任何语句发生错误都完全回滚
        // This function doesn't work with non transactional table types (like MyISAM or ISAM).
        $this->mysqliAgent->getInstanceOfMySQLi()->autocommit(false);
        try {
            if ($this->mysqliAgent->getInstanceOfMySQLi()->multi_query($query)) {
                do {
                    $affected[] = $this->mysqliAgent->getInstanceOfMySQLi()->affected_rows;
                    if (
                        $type == ApplicationModel::TYPE_MODIFY
                        && $this->mysqliAgent->getInstanceOfMySQLi()->affected_rows <= 0
                    ) {
                        $error[$sqlIdx] = 'The No.{$sqlIdx} statement has no effect!';
                        if ($this->mysqliAgent->getInstanceOfMySQLi()->errno !== 0) {
                            $error[$sqlIdx] .= " MySQL Error: #" . $this->mysqliAgent->getInstanceOfMySQLi()->errno . " " . $this->mysqliAgent->getInstanceOfMySQLi()->error;
                            $this->mysqliAgent->getInstanceOfMySQLi()->rollback();
                            $this->mysqliAgent->getInstanceOfMySQLi()->close();
                            throw new \Exception($error[$sqlIdx]);
                        }
                    }
                    $sqlIdx++;

                    // I wonder should the calls be multi called
                    //if ($type==ApplicationModel::TYPE_EXECUTE) {
                    //break;
                    //}
                } while (
                    $this->mysqliAgent->getInstanceOfMySQLi()->more_results()
                    && $this->mysqliAgent->getInstanceOfMySQLi()->next_result()
                    && !$this->mysqliAgent->getInstanceOfMySQLi()->errno
                );
            } else {
                throw new \Exception("multi_query failed. [" . $this->mysqliAgent->getInstanceOfMySQLi()->errno . "]" . $this->mysqliAgent->getInstanceOfMySQLi()->error);
            }

            $this->mysqliAgent->getInstanceOfMySQLi()->commit();
            $this->mysqliAgent->getInstanceOfMySQLi()->close();
            return true;
        } catch (\Exception $exception) {
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
                throw new \Exception("MySQLi multi_query cannot be done for query: " . $query);
            }
            $result = $this->mysqliAgent->getInstanceOfMySQLi()->store_result();
            if (!$result) {
                throw new \Exception("MySQLi store_result failed, returned " . json_encode($result));
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
        } catch (\Exception $exception) {
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
     * @param $tid
     * @return bool
     */
    public function kill($tid)
    {
        return $this->mysqliAgent->getInstanceOfMySQLi()->kill($tid);
    }
}