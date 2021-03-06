<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 4/12/19
 * Time: 3:53 PM
 */

namespace sinri\databasehub\controller;


use Exception;
use sinri\ark\web\implement\ArkWebController;
use sinri\databasehub\core\HubCore;
use sinri\databasehub\entity\DingtalkScanLoginSessionEntity;
use sinri\databasehub\entity\SessionEntity;
use sinri\databasehub\plugin\LoginPluginWithLeqeeCAS;

class CasController extends ArkWebController
{
    protected $cas_url = '';
    protected $tp_code = '';

    public function __construct()
    {
        parent::__construct();
        $this->tp_code = HubCore::getConfig(['aa', 'tp_code'], '');
        $this->cas_url = HubCore::getConfig(['aa', 'domain'], 'https://account-auth-v3.leqee.com') . '/cas';

    }

    public function loginCallback()
    {
        $ticket = $this->_readRequest("ticket", '');
        try {
            if (empty($ticket)) {
                throw new Exception('ticket is empty');
            }
            $session_entity = (new LoginPluginWithLeqeeCAS())->validateAuthPair($ticket, null);
            setcookie('database_hub_token', $session_entity->token, $session_entity->expire, '/');
            setcookie('DatabaseHubUser', json_encode($session_entity->user), $session_entity->expire, '/');
            header('Location:/frontend/index.html');
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * 获取登录配置
     */
    public function getLoginConfig()
    {
        try {
            $this->_sayOK(['cas_login_url' => $this->cas_url . '/login?service=' . $this->tp_code ]);
        } catch (Exception $e) {
            $this->_sayFail($e->getMessage());
        }
    }

    /**
     * 退出登录删除token
     */
    public function logout()
    {
        $user_session_token = isset($_COOKIE['database_hub_token']) ? $_COOKIE['database_hub_token'] : '';
        setcookie('database_hub_token', "", time()-86400,'/');
        setcookie('DatabaseHubUser', "", time()-86400,'/');
        if (!empty($user_session_token)) {
            header('Location:' . $this->cas_url . '/logout?service=' . $this->tp_code);
            exit();
        }
        header('Location:/frontend/login.html');
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
                $verify_session = (new DingtalkScanLoginSessionEntity())->getByTokenWithoutCheck($tp_token);
                if ($verify_session) {
                    SessionEntity::disableSession($verify_session->userSessionToken);
                }
            }
            $this->_sayOK();
        } catch (Exception $e) {
            $this->_sayFail($e->getMessage());
        }
    }

}
