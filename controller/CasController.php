<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 4/12/19
 * Time: 3:53 PM
 */

namespace sinri\databasehub\controller;


use sinri\ark\web\implement\ArkWebController;
use sinri\databasehub\plugin\LoginPluginWithLeqeeCAS;

class CasController extends ArkWebController
{
    public function loginCallback()
    {
        $ticket = $this->_readRequest("ticket", '');
        try {
            if (empty($ticket)) {
                throw new \Exception('ticket is empty');
            }
            $session_entity = (new LoginPluginWithLeqeeCAS())->validateAuthPair($ticket, null);
            setcookie('database_hub_token', $session_entity->token, $session_entity->expire, '/');
            setcookie('DatabaseHubUser', json_encode($session_entity->user), $session_entity->expire, '/');
            header('Location:/frontend/index.html');
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}