<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018-12-10
 * Time: 20:14
 */

namespace sinri\databasehub\queue;


use sinri\ark\queue\daemon\QueueDaemonConfiguration;
use sinri\ark\queue\daemon\QueueDaemonDelegate;
use sinri\ark\queue\QueueTask;
use sinri\databasehub\core\HubCore;
use sinri\databasehub\entity\ApplicationEntity;
use sinri\databasehub\model\ApplicationModel;

class SerialQueueDaemonDelegate extends QueueDaemonDelegate
{
    protected $config;

    /**
     * QueueDaemon constructor.
     * To make it more smooth to extend the config class, removed the config property definition.
     * @param QueueDaemonConfiguration $config
     */
    public function __construct($config)
    {
        parent::__construct($config);
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getDaemonStyle()
    {
        return $this->config->getDaemonStyle();
    }

    /**
     * @param string $error
     */
    public function whenLoopReportError($error)
    {
        HubCore::getLogger()->error(__METHOD__, ['error' => $error]);

        // Do you want to send alert on Dingtalk or Email? Append here.
    }

    /**
     * If not runnable, the daemon loop would sleep.
     * @return bool
     */
    public function isRunnable()
    {
        return true;
    }

    /**
     * Tell daemon loop to exit.
     * @return bool
     */
    public function shouldTerminate()
    {
        return false;
    }

    /**
     * Sleep for a certain while.
     * @return void
     */
    public function whenLoopShouldNotRun()
    {
        sleep(60);
    }

    /**
     * @return QueueTask|false
     */
    public function checkNextTask()
    {
        $row = (new ApplicationModel())->selectRow(['status' => ApplicationModel::STATUS_APPROVED]);
        if (empty($row)) return false;
        try {
            $task = ApplicationExecuteTask::createTask($row['application_id']);
            return $task;
        } catch (\Exception $e) {
            HubCore::getLogger()->error("Error when checkNextTask: " . $e->getMessage());
            return false;
        }
    }

    /**
     * When the loop cannot check for a task to do next, execute this
     */
    public function whenNoTaskToDo()
    {
        sleep(60);
    }

    /**
     * @since 0.2.0 this is done before fork in pooled style
     * @param QueueTask $task
     */
    public function whenTaskNotExecutable($task)
    {
        // when QueueTask::beforeExecute return false
        sleep(5);
    }

    /**
     *
     * @param QueueTask $task
     */
    public function whenToExecuteTask($task)
    {
        // Ga n ba re!
    }

    /**
     * @param QueueTask $task
     */
    public function whenTaskExecuted($task)
    {
        // O tsu ka re!
    }

    /**
     * @param QueueTask $task
     * @param \Exception $exception
     */
    public function whenTaskRaisedException($task, $exception)
    {
        HubCore::getLogger()->error("whenTaskRaisedException", ['application_id' => $task->getTaskReference(), 'error' => $exception->getMessage()]);
        // TODO make application ERROR
        $afx = (new ApplicationModel())->update(
            ['application_id' => $task->getTaskReference(), 'status' => ApplicationModel::STATUS_EXECUTING],
            ['status' => ApplicationModel::STATUS_ERROR, 'duration' => -1]
        );
        if ($afx) {
            try {
                ApplicationEntity::instanceById($task->getTaskReference())->writeRecord(0, 'EXECUTE', "Exception thrown when being executed: " . $exception->getMessage());
            } catch (\Exception $e) {
                HubCore::getLogger()->error("whenTaskRaisedException writeRecord failed: " . $e->getMessage());
            }
        } else {
            HubCore::getLogger()->error("whenTaskRaisedException afx empty");
        }
    }

    /**
     * When a child process is forked
     * @param int $pid
     * @param string $note
     */
    public function whenChildProcessForked($pid, $note = '')
    {
    }

    /**
     * When a child process is observed dead by WAIT function
     * @param int $pid
     */
    public function whenChildProcessConfirmedDead($pid)
    {
    }

    /**
     * When the daemon has made the pool full of child processes to work
     * It is recommended to take a sleep here
     */
    public function whenPoolIsFull()
    {
    }

    /**
     * 如果返回true，则在执行完whenPoolIsFull之后会进行阻塞wait子进程
     * @return bool
     */
    public function shouldWaitForAnyWorkerDone()
    {
        return false;
    }

    /**
     * You can close all opened DB connection here
     */
    public function beforeFork()
    {
    }

    /**
     * When the loop gets ready to terminate by shouldTerminate instructed, execute this
     */
    public function whenLoopTerminates()
    {
        HubCore::getLogger()->info("We die with honor!");
    }
}