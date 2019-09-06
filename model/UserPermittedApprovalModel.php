<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018-12-10
 * Time: 23:51
 */

namespace sinri\databasehub\model;


use Exception;
use sinri\ark\database\model\ArkDatabaseTableModel;
use sinri\ark\database\pdo\ArkPDO;
use sinri\databasehub\core\HubCore;

/**
 * Class UserPermittedApprovalModel
 * THIS IS FOR THE VIEW!
 * 20190523 Need add field : `a`.`process_id` AS `process_id`, done in LEQEE.COM
 * @package sinri\databasehub\model
 */
class UserPermittedApprovalModel extends ArkDatabaseTableModel
{

    /**
     * @return string
     */
    protected function mappingTableName()
    {
        return "user_permitted_approval";
    }

    /**
     * @return ArkPDO
     * @throws Exception
     */
    public function db()
    {
        return HubCore::getDB();
    }

    /**
     * @param $conditions
     * @param $data
     * @return int|void
     * @throws Exception
     */
    public function update($conditions, $data)
    {
        throw new Exception("Cannot modify a view");
    }

    /**
     * @param $conditions
     * @return int|void
     * @throws Exception
     */
    public function delete($conditions)
    {
        throw new Exception("Cannot modify a view");
    }

    /**
     * @param array $data
     * @return bool|string|void
     * @throws Exception
     */
    public function replace($data)
    {
        throw new Exception("Cannot modify a view");
    }

    /**
     * @param array $dataList
     * @return bool|string|void
     * @throws Exception
     */
    public function batchReplace($dataList)
    {
        throw new Exception("Cannot modify a view");
    }

    /**
     * @param array $data
     * @param null $pk
     * @return bool|string|void
     * @throws Exception
     */
    public function insert($data, $pk = null)
    {
        throw new Exception("Cannot modify a view");
    }

    /**
     * @param array $dataList
     * @param null $pk
     * @return bool|string|void
     * @throws Exception
     */
    public function batchInsert($dataList, $pk = null)
    {
        throw new Exception("Cannot modify a view");
    }

}