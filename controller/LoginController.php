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
use sinri\databasehub\entity\UserEntity;
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
}