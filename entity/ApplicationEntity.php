<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018/12/6
 * Time: 5:27 PM
 */

namespace sinri\databasehub\entity;


use sinri\databasehub\core\HubCore;
use sinri\databasehub\model\ApplicationModel;
use sinri\databasehub\model\DatabaseModel;
use sinri\databasehub\model\RecordModel;

class ApplicationEntity
{
    public $applicationId;
    public $title;
    public $description;
    /**
     * @var DatabaseEntity
     */
    public $database;
    public $sql;
    public $type;
    public $status;
    /**
     * @var UserEntity
     */
    public $applyUser;
    /**
     * @var UserEntity
     */
    public $approveUser;
    public $createTime;
    public $editTime;
    public $executeTime;
    public $approveTime;

    public $parallelable;
    public $duration;

    /**
     * @param array $row
     * @return ApplicationEntity
     * @throws \Exception
     */
    public static function instanceByRow($row)
    {
        if (empty($row)) {
            return null;
        }
        $entity = new ApplicationEntity();
        $entity->applicationId = $row['application_id'];
        $entity->title = $row['title'];
        $entity->description = $row['description'];
        $entity->database = DatabaseEntity::instanceById($row['database_id']);
        $entity->sql = $row['sql'];
        $entity->type = $row['type'];
        $entity->status = $row['status'];
        $entity->applyUser = UserEntity::instanceByUserId($row['apply_user']);
        $entity->approveUser = empty($row['approve_user']) ? null : UserEntity::instanceByUserId($row['approve_user']);
        $entity->createTime = $row['create_time'];
        $entity->editTime = $row['edit_time'];
        $entity->executeTime = $row['execute_time'];
        $entity->approveTime = $row['approve_time'];
        $entity->duration = $row['duration'];
        $entity->parallelable = $row['parallelable'];

        return $entity;
    }

    /**
     * @param int $applicationId
     * @return ApplicationEntity
     * @throws \Exception
     */
    public static function instanceById($applicationId)
    {
        $row = (new ApplicationModel())->selectRow(['application_id' => $applicationId]);
        return self::instanceByRow($row);
    }

    /**
     * @throws \Exception
     */
    public function refresh()
    {
        $row = (new ApplicationModel())->selectRow(['application_id' => $this->applicationId]);
        $this->title = $row['title'];
        $this->description = $row['description'];
        $this->database = DatabaseEntity::instanceById($row['database_id']);
        $this->sql = $row['sql'];
        $this->type = $row['type'];
        $this->status = $row['status'];
        $this->applyUser = UserEntity::instanceByUserId($row['apply_user']);
        $this->approveUser = empty($row['approve_user']) ? null : UserEntity::instanceByUserId($row['approve_user']);
        $this->createTime = $row['create_time'];
        $this->editTime = $row['edit_time'];
        $this->executeTime = $row['execute_time'];
        $this->approveTime = $row['approve_time'];
        $this->duration = $row['duration'];
        $this->parallelable = $row['parallelable'];
    }

    /**
     * @param int $userId
     * @param String $action
     * @param String $detail
     * @return bool|string
     */
    public function writeRecord($userId, $action, $detail)
    {
        return (new RecordModel())->insert([
            'application_id' => $this->applicationId,
            'status' => $this->status,
            'act_user' => $userId,
            'action' => $action,
            'detail' => $detail,
            'act_time' => RecordModel::now(),
        ]);
    }

    /**
     * @return array
     */
    public function getRecords()
    {
        $rows = (new RecordModel())->selectRowsWithSort(['application_id' => $this->applicationId], "record_id desc");
        $records = [];
        if (!empty($rows)) {
            foreach ($rows as $row) {
                $records[] = RecordEntity::instanceByRow($row);
            }
        }
        return $records;
    }

    /**
     * @return array
     */
    public function getExportedFileInfo()
    {
        $should_have_file = ($this->type === ApplicationModel::TYPE_READ && $this->status === ApplicationModel::STATUS_DONE);
        $info = [
            "should_have_file" => $should_have_file,
        ];
        if (!$should_have_file) return $info;

        $path = $this->getExportedFilePath();
        if (file_exists($path)) {
            $info["path"] = $path;
            $info["size"] = filesize($path);
        } else {
            $info['error'] = "File Not Exist";
        }
        return $info;
    }

    public function getAbstractForList()
    {
        $abstract = (array)$this;
        return $abstract;
    }

    public function getDetail()
    {
        $detail = $this->getAbstractForList();
        $detail['preview_table'] = $this->getExportedContentPreview();
        $detail['history'] = $this->getRecords();
        $detail['result_file'] = $this->getExportedFileInfo();
        return $detail;
    }

    /// TASK

    /**
     * Called by QueueTask implementation, to make status to EXECUTING and ensure the original status is APPROVED.
     * @return int
     */
    public function taskSeize()
    {
        return (new ApplicationModel())->update(
            ['application_id' => $this->applicationId, 'status' => ApplicationModel::STATUS_APPROVED],
            ['status' => ApplicationModel::STATUS_EXECUTING, 'execute_time' => ApplicationModel::now()]
        );
    }

    /**
     * @return bool
     */
    public function taskExecute()
    {
        // database entity is $this->database
        if ($this->status !== ApplicationModel::STATUS_EXECUTING) {
            HubCore::getLogger()->warning("This application comes with a strange status", ["application_id" => $this->applicationId, "status" => $this->status]);
            return false;
        }
        if ($this->database->status !== DatabaseModel::STATUS_NORMAL) {
            HubCore::getLogger()->error("This application should be denied as the target database has been disabled.", ['application_id' => $this->applicationId]);
            return false;
        }

        $errorMessage = "";
        $recordInfo = "";
        $error = [];
        $duration = -1;
        $sqlBeginTime = microtime(true);
        try {
            if ($this->type == ApplicationModel::TYPE_READ) {
                $done = $this->taskExecuteReadSQL($error);
                $sqlEndTime = microtime(true);
                $duration = $sqlEndTime - $sqlBeginTime;
            } elseif ($this->type == ApplicationModel::TYPE_EXECUTE) {
                $done = $this->taskExecuteCallSQL($error);
                $sqlEndTime = microtime(true);
                $duration = $sqlEndTime - $sqlBeginTime;
            } else {
                $done = $this->taskExecuteModifySQL($affected, $error);

                $sqlEndTime = microtime(true);
                $duration = $sqlEndTime - $sqlBeginTime;

                $recordInfo = "Executed. Affected rows by each statement:" . PHP_EOL;
                $totalAffect = 0;
                $sqlIdx = 1;
                foreach ($affected as $singleAffect) {
                    $totalAffect += $singleAffect;
                    $recordInfo .= "No." . $sqlIdx . " Statement affected " . $singleAffect . " row(s);" . PHP_EOL;
                    $sqlIdx++;
                }
                $recordInfo .= "Totally affected" . $totalAffect . " row(s)." . PHP_EOL;
            }

            $recordInfo .= "Time Cost: " . number_format($duration, 4) . " seconds" . PHP_EOL;

            if (!$done) {
                throw new \Exception("Execute Failed");
            }

            $afx = (new ApplicationModel())->update(
                ['application_id' => $this->applicationId],
                ['duration' => $duration, 'status' => ApplicationModel::STATUS_DONE]
            );
            if (!$afx) {
                HubCore::getLogger()->warning("taskExecute Done but status not updated", ['application_id' => $this->applicationId, 'updated' => $afx]);
            } else {
                HubCore::getLogger()->info("taskExecute Done", ['application_id' => $this->applicationId, 'updated' => $afx]);
            }

            $this->refresh();
            $this->writeRecord(0, "EXECUTE", $recordInfo);

            HubCore::getLogger()->info("Recorded. " . $recordInfo, ['application_id' => $this->applicationId]);

            return true;
        } catch (\Exception $exception) {
            if ($duration < 0) {
                $duration = microtime(true) - $sqlBeginTime;
            }
            $errorMessage .= "ERROR: " . $exception->getMessage() . PHP_EOL;
            foreach ($error as $line => $text) {
                $errorMessage .= "No." . $line . " Statement reported: " . $text . PHP_EOL;
            }

            $afx = (new ApplicationModel())->update(
                ['application_id' => $this->applicationId],
                ['duration' => $duration, 'status' => ApplicationModel::STATUS_ERROR]
            );
            if (!$afx) {
                HubCore::getLogger()->warning("taskExecute Error but status not updated", ['application_id' => $this->applicationId, 'updated' => $afx]);
            } else {
                HubCore::getLogger()->info("taskExecute Error", ['application_id' => $this->applicationId, 'updated' => $afx]);
            }

            try {
                $this->refresh();
            } catch (\Exception $e) {
                HubCore::getLogger()->error("refresh application entity failed: " . $e->getMessage());
            }
            $this->writeRecord(0, "EXECUTE", $errorMessage);

            return false;
        }
    }

    /**
     * @param string[] $error
     * @return bool
     * @throws \Exception
     */
    protected function taskExecuteReadSQL(&$error)
    {
        HubCore::getLogger()->info("Begin SQL Export", ['application_id' => $this->applicationId]);
        HubCore::getLogger()->info($this->sql);
        $csv_path = $this->getExportedFilePath();
        $written = (new DatabaseMySQLiEntity($this->database))->exportCSV($this->sql, $csv_path, $error, 'UTF-8');
        return $written;
    }

    /**
     * @param string[] $error
     * @return bool
     * @throws \Exception
     */
    protected function taskExecuteCallSQL(&$error)
    {
        HubCore::getLogger()->info("Begin SQL CALL:");
        HubCore::getLogger()->info($this->sql);
        $done = (new DatabaseMySQLiEntity($this->database))->executeCall($this->sql, $error);
        return $done;
    }

    /**
     * @param $affected
     * @param $error
     * @return bool
     * @throws \Exception
     */
    protected function taskExecuteModifySQL(&$affected, &$error)
    {
        HubCore::getLogger()->info("Begin SQL Query:");
        HubCore::getLogger()->info($this->sql);
        $ret = (new DatabaseMySQLiEntity($this->database))->executeMulti($this->sql, $this->type, $affected, $error);
        return $ret;
    }

    /**
     * @return string
     */
    public function getExportedFilePath()
    {
        $csv_path = HubCore::getConfig(['store', 'path'], __DIR__ . '/../store') . '/app_' . $this->applicationId . ".csv";
        return $csv_path;
    }

    /**
     * @return string[][]
     */
    public function getExportedContentPreview()
    {
        $csv_path = $this->getExportedFilePath();

        if (!file_exists($csv_path)) {
            return [["Content Not Existed"]];
        }

        $handle = fopen($csv_path, "r");
        if ($handle === false) {
            return [["Cannot Read Content"]];
        }
        $maxRows = 10;
        $rows = [];
        for ($i = 0; $i < $maxRows; $i++) {
            $data = fgetcsv($handle, 1000, ",");
            if ($data === false) break;
            if (is_array($data)) {
                foreach ($data as $key => $value) {
                    $encode = @mb_detect_encoding($value);
                    if (!$value) {
                        $data[$key] = '';
                    } else if ($value !== 'UTF-8') {
                        $data[$key] = mb_convert_encoding($value, 'UTF-8', $encode);
                    } else {
                        $data[$key] = $value;
                    }
                }
            }
            $rows[] = $data;
        }
        fclose($handle);
        return $rows;
    }
}