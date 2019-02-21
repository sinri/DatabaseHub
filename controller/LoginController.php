<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018/12/4
 * Time: 10:54 AM
 */

namespace sinri\databasehub\controller;


use sinri\ark\web\implement\ArkWebController;
use sinri\databasehub\core\HubCore;
use sinri\databasehub\entity\SessionEntity;
use sinri\databasehub\entity\UserEntity;
use sinri\databasehub\library\PrpcryptLibrary;
use sinri\databasehub\model\UserModel;
use sinri\databasehub\plugin\standard\LoginPlugin;

class LoginController extends ArkWebController
{
    /**
     * @var LoginPlugin
     */
    protected $plugin;

    public function __construct()
    {
        parent::__construct();

        $pluginName = HubCore::getConfig(['plugins', 'login'], 'LoginPluginStandalone');
        $className = "sinri\\databasehub\\plugin\\" . $pluginName;
        $this->plugin = new $className;
    }

    public function login()
    {
        try {
            $username = $this->_readRequest("username", "");
            $password = $this->_readRequest("password", "");
            $session = $this->plugin->validateAuthPair($username, $password);

            $this->_sayOK(['session' => $session]);
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }

    /**
     * OC实现自动登录
     */
    public function autoLogin()
    {
        try {
            $username = $this->_readRequest("username", "");
            if (empty($username)) {
                throw new \Exception('参数错误，请勿非法调用');
            }
            $username = (new PrpcryptLibrary(md5('databasehub')))->decrypt($username);
            if (empty($username)) {
                throw new \Exception('参数解析错误，请勿非法调用');
            }
            $row = (new UserModel())->selectRow(['username' => $username, "user_org" => "LEQEE"]);
            if (empty($row)) {
                throw new \Exception('用户未建档，首次使用请登录PC初始化帐号');
            }
            $userEntity = UserEntity::instanceByRow($row);
            $session =  SessionEntity::createSessionForUser($userEntity);
            $this->_sayOK(['session' => $session]);
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }

    /**
     * 获取全部用户
     */
    public function getAllUser()
    {
        try {
            $this->_sayOK(['list' => (new UserEntity())->getAllUser()]);
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }

    public function dashboardMeta()
    {
        $doc = HubCore::getConfig(['dashboard', 'doc'], null);
        if ($doc === null) {
            $doc_path = HubCore::getConfig(['dashboard', 'doc_path'], __DIR__ . '/../docs/DashboardDoc_Leqee_CN.md');
            $doc = file_get_contents($doc_path);
        }
        $this->_sayOK(['doc' => $doc]);
    }
}