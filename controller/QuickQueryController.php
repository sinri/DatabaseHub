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
use sinri\databasehub\model\PermissionModel;

class QuickQueryController extends AbstractAuthController
{

    public function permittedDatabases()
    {
        $rows = (new PermissionModel())->selectRows(['user_id' => $this->session->user->userId, 'permission' => PermissionModel::PERMISSION_QUICK_QUERY]);
        $list = [];
        if (!empty($rows)) {
            foreach ($rows as $row) {
                $list[] = DatabaseEntity::instanceByRow($row);
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

        $maxRows = 512;

        $sql = $this->_readRequest('sql', '');
        $processedSQL = SQLChecker::processSqlForQuickQuery($sql, $maxRows);
        $type = SQLChecker::getTypeOfSingleSql($processedSQL);
        if (!in_array($type, [ApplicationModel::TYPE_READ])) {
            throw new \Exception("Not a read statement");
        }

        $t1 = microtime(true);
        $done = (new DatabaseMySQLiEntity(DatabaseEntity::instanceById($database_id)))->quickQuery($processedSQL, $data, $error, $maxRows, $duration);
        $t2 = microtime(true);

        // TODO record quick queries

        $this->_sayOK([
            'done' => $done,
            'data' => $data,
            'error' => $error,
            'query_time' => $duration,
            'total_time' => ($t2 - $t1),
        ]);
    }
}