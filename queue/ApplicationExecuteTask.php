<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018-12-10
 * Time: 18:04
 */

namespace sinri\databasehub\queue;


use sinri\ark\queue\parallel\ParallelQueueTask;
use sinri\databasehub\core\HubCore;
use sinri\databasehub\entity\ApplicationEntity;

class ApplicationExecuteTask extends ParallelQueueTask
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
        if ($this->isExclusive()) {
            return "Exclusive-Query";
        } else {
            return "Parallelable-Query";
        }
    }

    public function isExclusive()
    {
        return !$this->applicationEntity->parallelable;
    }

    /**
     * To prepare and lock task before executing.
     * You should update property $readyToExecute as the result of this method
     * @return bool
     * @throws \Exception
     */
    public function beforeExecute()
    {
        $afx = $this->applicationEntity->taskSeize();
        HubCore::getLogger()->info(__METHOD__, ["application_id" => $this->applicationEntity->applicationId, "afx" => $afx]);
        $this->readyToExecute = !!$afx;
        if ($this->readyToExecute) {
            $this->applicationEntity->refresh();
            HubCore::getLogger()->info("Refreshed Task Application Entity, to be", ["application_id" => $this->applicationEntity->applicationId, "status" => $this->applicationEntity->status]);
        }
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
        $this->done = $this->applicationEntity->taskExecute();
        $this->executeFeedback = ($this->done ? "Executed" : "Failed");
        return $this->done;
    }
}