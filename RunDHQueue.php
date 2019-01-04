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
$delegate->clearRuntimeCommand();

\sinri\databasehub\core\HubCore::getLogger()->info("Queue Begins");

$daemon->loop();