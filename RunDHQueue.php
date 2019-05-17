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
$delegate->clearRuntimeCommand();

HubCore::getLogger()->info("Queue Begins");

$daemon->loop();