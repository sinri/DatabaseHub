<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018-12-25
 * Time: 23:20
 */

namespace sinri\databasehub\queue;


use Exception;
use sinri\ark\queue\parallel\ParallelQueueDaemonDelegate;
use sinri\ark\queue\QueueTask;
use sinri\databasehub\core\HubCore;
use sinri\databasehub\entity\ApplicationEntity;
use sinri\databasehub\model\ApplicationModel;
use sinri\databasehub\model\RecordModel;

class DHQueueDelegate extends ParallelQueueDaemonDelegate
{
    const COMMAND_DEFAULT = "";
    const COMMAND_CONTINUE = "CONTINUE";
    const COMMAND_PAUSE = "PAUSE";
    const COMMAND_STOP = "STOP";

    /**
     * QueueDaemon constructor.
     * @param array $config Put any properties here
     */
    public function __construct($config = [])
    {
    }

    public function fetchRuntimeCommand()
    {
        $path = __DIR__ . '/../runtime/queue.command';
        if (file_exists($path)) return file_get_contents($path);
        else return "";
    }

    public function setStopRuntimeCommand()
    {
        $path = __DIR__ . '/../runtime/queue.command';
        if (!file_exists(__DIR__ . '/../runtime')) {
            mkdir(__DIR__ . '/../runtime', 0777, true);
        }
        return file_put_contents($path, self::COMMAND_STOP);
    }

    public function clearRuntimeCommand()
    {
        $path = __DIR__ . '/../runtime/queue.command';
        if (!file_exists(__DIR__ . '/../runtime')) {
            mkdir(__DIR__ . '/../runtime', 0777, true);
        }
        return file_put_contents($path, "");
    }

    /**
     * @param string $error
     */
    public function whenLoopReportError($error)
    {
        HubCore::getLogger()->error($error);
    }

    /**
     * If not runnable, the daemon loop would sleep.
     * @return bool
     */
    public function isRunnable()
    {
        $command = $this->fetchRuntimeCommand();
        return in_array($command, [self::COMMAND_DEFAULT, self::COMMAND_CONTINUE]);
    }

    /**
     * Tell daemon loop to exit.
     * @return bool
     */
    public function shouldTerminate()
    {
        $command = $this->fetchRuntimeCommand();
        return in_array($command, [self::COMMAND_STOP]);
    }

    /**
     * Sleep for a certain while.
     * @return void
     */
    public function whenLoopShouldNotRun()
    {
        sleep(60);
    }

    public function whenLoopTerminates()
    {
        HubCore::getLogger()->info("Stop command is confirmed.");
        $this->clearRuntimeCommand();
    }

    /**
     * When the loop cannot check for a task to do next, execute this
     */
    public function whenNoTaskToDo()
    {
        HubCore::getLogger()->info("whenNoTaskToDo, sleep 60s");
        sleep(10);
    }

    /**
     * @since 0.2.0 this is done before fork in pooled style
     * @param QueueTask $task
     */
    public function whenTaskNotExecutable($task)
    {
        HubCore::getLogger()->error("whenTaskNotExecutable", [
            "ID" => $task->getTaskReference(),
            "TYPE" => $task->getTaskType(),
        ]);
    }

    /**
     *
     * @param QueueTask $task
     */
    public function whenToExecuteTask($task)
    {
        // record the process id
        (new ApplicationModel())->update(['application_id' => $task->getTaskReference()], ['process_id' => getmypid()]);
        (new RecordModel())->insert([
            "application_id" => $task->getTaskReference(),
            "status" => ApplicationModel::STATUS_EXECUTING,
            "act_user" => 0,
            "action" => "FORK",
            "detail" => "Worker Process " . getmypid() . " Forked",
            "act_time" => RecordModel::now(),
        ]);
    }

    /**
     * @param QueueTask $task
     */
    public function whenTaskExecuted($task)
    {
        // record process work end
        (new RecordModel())->insert([
            "application_id" => $task->getTaskReference(),
            "status" => ApplicationModel::STATUS_DONE,
            "act_user" => 0,
            "action" => "FORK",
            "detail" => "Worker Process " . getmypid() . " Finished Task",
            "act_time" => RecordModel::now(),
        ]);
    }

    /**
     * @param QueueTask $task
     * @param Exception $exception
     */
    public function whenTaskRaisedException($task, $exception)
    {
        HubCore::getLogger()->error("whenTaskRaisedException", ['application_id' => $task->getTaskReference(), 'error' => $exception->getMessage()]);
        // make application ERROR
        $afx = (new ApplicationModel())->update(
            ['application_id' => $task->getTaskReference(), 'status' => ApplicationModel::STATUS_EXECUTING],
            ['status' => ApplicationModel::STATUS_ERROR, 'duration' => -1]
        );
        if ($afx) {
            try {
                ApplicationEntity::instanceById($task->getTaskReference())->writeRecord(0, 'EXECUTE', "Exception thrown when being executed: " . $exception->getMessage());
            } catch (Exception $e) {
                HubCore::getLogger()->error("whenTaskRaisedException writeRecord failed: " . $e->getMessage());
            }
        } else {
            HubCore::getLogger()->error("whenTaskRaisedException afx empty");
        }
    }

    /**
     * @return ApplicationExecuteTask|false
     */
    public function checkNextTaskImplement()
    {
        $row = (new ApplicationModel())->selectRow(['status' => ApplicationModel::STATUS_APPROVED]);
        if (empty($row)) return false;
        try {
            return ApplicationExecuteTask::createTask($row['application_id']);
        } catch (Exception $e) {
            HubCore::getLogger()->error("Error when checkNextTask: " . $e->getMessage());
            return false;
        }
    }

    /**
     * The daemon would fork child processes up to the certain number
     * @return int
     */
    public function maxChildProcessCountForSinglePooledStyle()
    {
        return HubCore::getConfig(['queue', 'max_worker'], 5);
    }

    /**
     * When a child process is forked
     * @param int $pid
     * @param string $note
     * @param null|int|string $taskReference
     */
    public function whenChildProcessForked($pid, $note = '', $taskReference = null)
    {
        HubCore::getLogger()->info("whenChildProcessForked", ["pid" => $pid, "note" => $note, 'taskReference' => $taskReference]);
    }

    /**
     * When a child process is observed dead by WAIT function
     * @param int $pid
     * @param array $detail
     */
    public function whenChildProcessConfirmedDead($pid, $detail = [])
    {
        $rows = (new ApplicationModel())->selectRows(['process_id' => $pid]);
        if (empty($rows)) {
            HubCore::getLogger()->error("Process [$pid] contains no records", ["rows" => $rows]);
            return;
        }

        if (count($rows) > 1) {
            HubCore::getLogger()->warning("Process [$pid] contains " . count($rows) . " records", ["rows" => $rows]);
        }

        foreach ($rows as $row) {
            if ($row['status'] == ApplicationModel::STATUS_EXECUTING) {
                $newStatus = ApplicationModel::STATUS_ERROR;
            } else {
                $newStatus = $row['status'];
            }

            $afx = (new ApplicationModel())->update(
                ['application_id' => $row['application_id'], 'process_id' => $pid],
                [
                    'process_id' => (0 - $pid),
                    'status' => $newStatus,
                ]
            );
            HubCore::getLogger()->info("whenChildProcessConfirmedDead", ["pid" => $pid, 'application_id' => $row['application_id'], 'afx' => $afx]);
        }
    }

    /**
     * When the daemon has made the pool full of child processes to work
     * It is recommended to take a sleep here
     */
    public function whenPoolIsFull()
    {
        HubCore::getLogger()->warning("whenPoolIsFull, sleep for 10 seconds");
        sleep(10);
    }

    /**
     * You can close all opened DB connection here
     * @param ApplicationExecuteTask $task
     * @return bool
     * @throws Exception
     */
    public function beforeFork($task = null)
    {
        if ($task) {
            $canExecuteItNow = $task->beforeExecute();
            HubCore::getLogger()->smartLogLite($canExecuteItNow, __METHOD__, ['task_id' => $task->getTaskReference()]);
            return $canExecuteItNow;
        }
        return true;
    }
}