<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018-12-10
 * Time: 22:56
 */

require_once __DIR__ . '/autoload.php';

$delegate = new \sinri\databasehub\queue\SerialQueueDaemonDelegate(new \sinri\databasehub\queue\SerialQueueDaemonConfiguration());
$daemon = new \sinri\ark\queue\daemon\QueueDaemon($delegate);
$daemon->loop();