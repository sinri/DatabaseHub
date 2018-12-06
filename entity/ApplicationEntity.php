<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018/12/6
 * Time: 5:27 PM
 */

namespace sinri\databasehub\entity;


use sinri\databasehub\model\ApplicationModel;
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

    /**
     * @param array $row
     * @return ApplicationEntity
     * @throws \Exception
     */
    public static function instanceByRow($row)
    {
        $entity = new ApplicationEntity();
        $entity->applicationId = $row['application_id'];
        $entity->title = $row['title'];
        $entity->description = $row['description'];
        $entity->database = DatabaseEntity::instanceById($row['database']);
        $entity->sql = $row['sql'];
        $entity->type = $row['type'];
        $entity->status = $row['status'];
        $entity->applyUser = UserEntity::instanceByUserId($row['apply_user']);
        $entity->approveUser = UserEntity::instanceByUserId($row['approve_user']);
        $entity->createTime = $row['create_time'];
        $entity->editTime = $row['edit_time'];
        $entity->executeTime = $row['execute_time'];
        $entity->approveTime = $row['approve_time'];

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
}