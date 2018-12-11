<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018-12-11
 * Time: 00:05
 */

namespace sinri\databasehub\controller;


use sinri\databasehub\core\AbstractAuthController;
use sinri\databasehub\core\SQLChecker;
use sinri\databasehub\entity\DatabaseEntity;
use sinri\databasehub\entity\DatabaseMySQLiEntity;
use sinri\databasehub\model\ApplicationModel;
use sinri\databasehub\model\DatabaseModel;
use sinri\databasehub\model\PermissionModel;
use sinri\databasehub\model\QuickQueryModel;
use sinri\databasehub\model\UserModel;

class QuickQueryController extends AbstractAuthController
{

    public function permittedDatabases()
    {
        $list = [];
        if ($this->session->user->userType === UserModel::USER_TYPE_ADMIN) {
            // admin see all
            $rows = (new DatabaseModel())->selectRows(['status' => DatabaseModel::STATUS_NORMAL]);
            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $list[] = DatabaseEntity::instanceByRow($row);
                }
            }
        } else {
            $rows = (new PermissionModel())->selectRows([
                'user_id' => $this->session->user->userId,
                'permission' => PermissionModel::PERMISSION_QUICK_QUERY
            ]);

            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $list[] = DatabaseEntity::instanceById($row['database_id']);
                }
            }
        }
        $this->_sayOK(['list' => $list]);
    }

    /**
     * @throws \Exception
     */
    public function syncExecute()
    {
        $database_id = $this->_readRequest("database_id", 0);
        $x = (new PermissionModel())->selectRowsForCount(['database_id' => $database_id, 'user_id' => $this->session->user->userId]);
        if (!$x) throw new \Exception("Not Permitted");

        $databaseEntity = DatabaseEntity::instanceById($database_id);

        $maxRows = 512;

        $sql = $this->_readRequest('sql', '');
        $processedSQL = SQLChecker::processSqlForQuickQuery($sql, $maxRows);
        $type = SQLChecker::getTypeOfSingleSql($processedSQL);
        if (!in_array($type, [ApplicationModel::TYPE_READ])) {
            throw new \Exception("Not a read statement");
        }

        $quickQueryId = (new QuickQueryModel())->insert([
            'database_id' => $databaseEntity->databaseId,
            'sql' => $processedSQL,
            'raw_sql' => $sql,
            'apply_user' => $this->session->user->userId,
            'apply_time' => QuickQueryModel::now(),
            'type' => QuickQueryModel::TYPE_SYNC,
        ]);
        if (empty($quickQueryId)) {
            throw new \Exception("Cannot register task");
        }

        $t1 = microtime(true);
        $done = (new DatabaseMySQLiEntity($databaseEntity))->quickQuery($processedSQL, $data, $error, $maxRows, $duration);
        $t2 = microtime(true);

        // record quick queries
        (new QuickQueryModel())->update(
            ['id' => $quickQueryId],
            [
                'duration' => ($t2 - $t1),
                'remark' => ($done ? "DONE, fetched " . count($data) . " rows." : "FAILED.") . PHP_EOL . implode(PHP_EOL, $error),
            ]
        );


        $this->_sayOK([
            'done' => $done,
            'data' => $data,
            'error' => $error,
            'query_time' => $duration,
            'total_time' => ($t2 - $t1),
        ]);
    }
}