<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018-12-10
 * Time: 18:04
 */

namespace sinri\databasehub\queue;


use sinri\ark\queue\QueueTask;
use sinri\databasehub\core\HubCore;
use sinri\databasehub\entity\ApplicationEntity;

class ApplicationExecuteTask extends QueueTask
{
    /**
     * @var ApplicationEntity
     */
    protected $applicationEntity;

    /**
     * @param $application_id
     * @return ApplicationExecuteTask
     * @throws \Exception
     */
    public static function createTask($application_id)
    {
        $task = new ApplicationExecuteTask();
        $task->applicationEntity = ApplicationEntity::instanceById($application_id);
        return $task;
    }

    /**
     * Fetch the unique reference of this task, such as TASK_ID
     * @since 0.1.2
     * @return int|string
     */
    public function getTaskReference()
    {
        return $this->applicationEntity->applicationId;
    }

    /**
     * Fetch the type of this task
     * @since 0.1.7
     * @return string
     */
    public function getTaskType()
    {
        return "QUERY";
    }

    /**
     * To prepare and lock task before executing.
     * You should update property $readyToExecute as the result of this method
     * @return bool
     */
    public function beforeExecute()
    {
        $afx = $this->applicationEntity->taskSeize();
        HubCore::getLogger()->info(__METHOD__, ["application_id" => $this->applicationEntity->applicationId, "afx" => $afx]);
        $this->readyToExecute = !!$afx;
        return $this->readyToExecute;
    }

    /**
     * Execute a task then:
     * (1) store extra output data in property $executeResult
     * (2) give a feedback string in property $feedback
     * (3) give a boolean value in property $done and return
     * @return bool
     */
    public function execute()
    {
        $this->applicationEntity->writeRecord(0, "EXECUTE", "Task is ready to be executed");
        $this->done = $this->execute();
        $this->executeFeedback = ($this->done ? "Executed" : "Failed");
        return $this->done;
    }
}