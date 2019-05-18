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

        $this->charset = "utf8";
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

            $this->arkPDO->getAllAsStream($query, function ($row, $index) use ($charset, $csvFile) {
                if ($index === 1) {
                    //title row
                    fputcsv($csvFile, array_keys($row));
                }
                array_walk($row, 'self::transCharset', array($this->charset, $charset));
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
            return $this->arkPDO->executeInTransaction(function () use ($results, $sqlList) {
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
            $this->arkPDO->getAllAsStream($query, function ($row, $rowIndex) use ($data, $limit) {
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
}