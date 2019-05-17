<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018-12-26
 * Time: 00:12
 */

use sinri\ark\queue\parallel\ParallelQueueDaemon;
use sinri\databasehub\core\HubCore;
use sinri\databasehub\queue\DHQueueDelegate;

require_once __DIR__ . '/autoload.php';

$delegate = new DHQueueDelegate();
$daemon = new ParallelQueueDaemon($delegate);
$delegate->setStopRuntimeCommand();

HubCore::getLogger()->info("Queue Stop Command Written.");

while (true) {
    sleep(2);
    if ($delegate->fetchRuntimeCommand() != DHQueueDelegate::COMMAND_STOP) {
        break;
    }
    HubCore::getLogger()->info("Waiting for queue stopping...");
}