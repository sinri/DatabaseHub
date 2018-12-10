<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018-12-10
 * Time: 20:12
 */

namespace sinri\databasehub\queue;


use sinri\ark\queue\daemon\QueueDaemon;
use sinri\ark\queue\daemon\QueueDaemonConfiguration;

class SerialQueueDaemonConfiguration extends QueueDaemonConfiguration
{

    /**
     * Decide in which style the daemon works, among the following constants:
     * QueueDaemon::DAEMON_STYLE_SINGLE_SYNCHRONIZED
     * @return string
     */
    public function getDaemonStyle()
    {
        return QueueDaemon::DAEMON_STYLE_SINGLE_SYNCHRONIZED;
    }
}