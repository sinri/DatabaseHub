<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018-12-26
 * Time: 00:12
 */

require_once __DIR__ . '/autoload.php';

$delegate = new \sinri\databasehub\queue\DHQueueDelegate();
$daemon = new \sinri\ark\queue\parallel\ParallelQueueDaemon($delegate);
$delegate->setStopRuntimeCommand();

\sinri\databasehub\core\HubCore::getLogger()->info("Queue Stop Command Written.");

while (true) {
    sleep(2);
    if ($delegate->fetchRuntimeCommand() != \sinri\databasehub\queue\DHQueueDelegate::COMMAND_STOP) {
        break;
    }
    \sinri\databasehub\core\HubCore::getLogger()->info("Waiting for queue stopping...");
}