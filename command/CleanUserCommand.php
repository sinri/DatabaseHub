<?php


namespace sinri\databasehub\command;


use sinri\ark\cli\ArkCliProgram;
use sinri\databasehub\core\HubCore;

class CleanUserCommand extends ArkCliProgram
{
    /**
     * php runner.php command/CleanUserCommand
     */
    public function actionDefault()
    {
        $pluginName = 'sinri\databasehub\plugin\\' . HubCore::getConfig(['plugins', 'user_clean']);
        $plugin = new $pluginName();
        $plugin->cleanUsers();
    }
}