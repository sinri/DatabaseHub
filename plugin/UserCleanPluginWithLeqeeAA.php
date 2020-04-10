<?php


namespace sinri\databasehub\plugin;


use sinri\databasehub\core\HubCore;
use sinri\databasehub\model\UserModel;
use sinri\databasehub\plugin\standard\UserCleanPlugin;

class UserCleanPluginWithLeqeeAA extends UserCleanPlugin
{

    public function cleanUsers()
    {
        // TODO Scan AA table is not a good idea, AA TP API needed
        $sql = "SELECT du.`user_id` ,du.`username` ,du.`realname` 
            FROM `databasehub`.`user` du
            inner join `account_auth`.`aa_user` au on du.`username` =au.`user_name` 
            where du.`status` ='NORMAL' and au.`status` ='DISABLED'
        ";
        $list = HubCore::getDB()->safeQueryAll($sql);
        if (empty($list)) {
            HubCore::getLogger()->info('No one died');
        } else {
            HubCore::getLogger()->info('Users to be disabled', ['total' => count($list)]);
            foreach ($list as $item) {
                HubCore::getLogger()->info("The user died", ['user' => $item]);
            }

            $diedUserIds = array_column($list, 'user_id');
            $afx = (new UserModel())->delete(['user_id' => $diedUserIds]);
            HubCore::getLogger()->info("Killed Users Count", ['afx' => $afx]);
        }
    }
}