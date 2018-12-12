<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018-12-12
 * Time: 10:42
 */

namespace sinri\databasehub\entity;


class RecordEntity
{
    public $recordId;
    public $applicationId;
    public $status;
    public $actUser;
    public $action;
    public $detail;
    public $actTime;

    /**
     * @param $row
     * @return RecordEntity
     */
    public static function instanceByRow($row)
    {
        $entity = new RecordEntity();
        $entity->recordId = $row['record_id'];
        $entity->applicationId = $row['application_id'];
        $entity->status = $row['status'];
        $entity->actUser = $row['act_user'];
        $entity->action = $row['action'];
        $entity->detail = $row['detail'];
        $entity->actTime = $row['act_time'];
        return $entity;
    }
}