<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 4/12/19
 * Time: 3:53 PM
 */

namespace sinri\databasehub\controller;


use sinri\ark\web\implement\ArkWebController;
use sinri\databasehub\core\HubCore;
use sinri\databasehub\entity\DingtalkScanLoginSessionEntity;
use sinri\databasehub\entity\SessionEntity;
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

    /**
     * 获取登录配置
     */
    public function getLoginConfig()
    {
        try {
            $tp_code = HubCore::getConfig(['aa', 'tp_code'], '');
            $cas_domain = HubCore::getConfig(['aa', 'domain'], 'https://account-auth-v3.leqee.com');
            $this->_sayOK(['cas_login_url' => $cas_domain . '/CAS/login?service=' . $tp_code ]);
        } catch (\Exception $e) {
            $this->_sayFail($e->getMessage());
        }
    }

    /**
     * 退出登录删除token
     */
    public function logoutCallback()
    {
        try {
            $ticket = $this->_readRequest("ticket", '');
            $tp_token = $this->_readRequest("tp_token", '');
            if (!empty($tp_token)) {
                $verify_session = (new DingtalkScanLoginSessionEntity())->getByToken($tp_token);
                if ($verify_session) {
                    SessionEntity::disableSession($verify_session->userSessionToken);
                }
            }
            $this->_sayOK();
        } catch (\Exception $e) {
            $this->_sayFail($e->getMessage());
        }
    }

}