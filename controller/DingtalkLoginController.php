<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 2/27/19
 * Time: 10:57 AM
 */

namespace sinri\databasehub\controller;


use sinri\ark\web\implement\ArkWebController;
use sinri\databasehub\core\HubCore;
use sinri\databasehub\entity\DingtalkScanLoginSessionEntity;
use sinri\databasehub\entity\SessionEntity;
use sinri\databasehub\library\PrpcryptLibrary;
use sinri\databasehub\plugin\LoginPluginWithDingtalk;
use sinri\databasehub\plugin\standard\LoginPlugin;

class DingtalkLoginController extends ArkWebController
{
    /**
     * @var LoginPlugin
     */
    protected $plugin;

    public function __construct()
    {
        parent::__construct();
        $this->plugin = new LoginPluginWithDingtalk();
    }

    /**
     * 获取扫码登录token
     */
    public function getScanQRCodeLoginToken()
    {
        try {
            $dingtalkScanLoginSessionEntity = new DingtalkScanLoginSessionEntity();
            $insert_result = $dingtalkScanLoginSessionEntity->createToken();
            if (!$insert_result) {
                throw new \Exception('会话建立失败，请刷新页面重试');
            }
            $this->_sayOK(['token' => $dingtalkScanLoginSessionEntity->token, 'aa_domain' => HubCore::getConfig(['aa', 'domain'], 'https://account-auth-v3.leqee.com')]);
        } catch (\Exception $e) {
            $this->_sayFail($e->getMessage());
        }
    }

    /**
     * AA验证回调(写入user)
     */
    public function callbackForScanQRCodeLogin()
    {
        try {
            $token = $this->_readRequest("client_data", '');
            $callback_data = $this->_readRequest("callback_data", '');
            if (empty($callback_data)) {
                throw new \Exception('no data');
            }
            $tp_code = HubCore::getConfig(['aa', 'tp_code'], "");
            $tp_verification = HubCore::getConfig(['aa', 'tp_verification'], "");
            $secret_key = md5($tp_code . 'AA' . md5($tp_verification));
            $user_name = (new PrpcryptLibrary($secret_key))->decrypt($callback_data);
            if (empty($user_name)) {
                throw new \Exception('cannot decode data .' . $callback_data);
            }
            $dingtalkScanLoginSessionEntity = (new DingtalkScanLoginSessionEntity())->getByToken($token);
            if (!$dingtalkScanLoginSessionEntity) {
                throw new \Exception('not find valid token ' . $token);
            }
            $session = $this->plugin->validateAuthPair($user_name, '');
            if (empty($dingtalkScanLoginSessionEntity->coreUserId)) {
                $dingtalkScanLoginSessionEntity->setUser($session->user->userId);
                $dingtalkScanLoginSessionEntity->setUserSessionToken($session->token);
            }
            $this->_sayOK($session);
        } catch (\Exception $e) {
            $this->_sayFail($e->getMessage());
        }
    }

    /**
     * 钉钉自动登陆
     * @param $token
     */
    public function checkScanQRCodeLoginStatus($token)
    {
        try {
            $dingtalkScanLoginSessionEntity = (new DingtalkScanLoginSessionEntity())->getByToken($token);
            if (!$dingtalkScanLoginSessionEntity) {
                throw new \Exception('会话已过期，请刷新页面重试', 500); // 挂断
            }
            if (empty($dingtalkScanLoginSessionEntity->coreUserId)) {
                throw new \Exception('等待扫码验证', 200); // 继续等待
            }
            $session = SessionEntity::instanceByToken($dingtalkScanLoginSessionEntity->userSessionToken);

            $this->_sayOK(['session' => $session]);
        } catch (\Exception $e) {
            if ($e->getCode() === 200) {
                $this->_sayOK(['status' => 'WAIT', 'msg' => $e->getMessage()]);
            } else {
                $this->_sayFail($e->getMessage());
            }
        }
    }
}