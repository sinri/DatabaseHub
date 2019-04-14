<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018/12/4
 * Time: 11:40 AM
 */

namespace sinri\databasehub\plugin;


use sinri\ark\core\ArkHelper;
use sinri\ark\io\curl\ArkCurl;
use sinri\databasehub\core\HubCore;
use sinri\databasehub\entity\SessionEntity;
use sinri\databasehub\entity\UserEntity;
use sinri\databasehub\model\UserModel;
use sinri\databasehub\plugin\standard\LoginPlugin;

class LoginPluginWithLeqeeCAS extends LoginPlugin
{
    protected function apiUrl($subUrl)
    {
        return HubCore::getConfig(['cas','leqee-cas-url'], "") . $subUrl;
    }

    /**
     * @param $username
     * @param $password
     * @return SessionEntity
     * @throws \Exception
     */
    public function validateAuthPair($username, $password)
    {
        $ticket = $username;
        $curl = new ArkCurl();
        $curl->setLogger(HubCore::getLogger());
        $result = $curl->prepareToRequestURL("POST", $this->apiUrl("validate"))
            ->setPostFormField("ticket", $ticket)
            ->setPostFormField("format", 'JSON')
            ->setPostFormField("service", HubCore::getConfig(['aa', 'tp_code'], "databasehub"))
            // tp_code is neglected now
            ->execute(true);

        //HubCore::getLogger()->info(__METHOD__ . '@' . __LINE__ . " AAv3 API Response:" . $result, ["req" => ['username' => $username, 'password' => $password]]);

        ArkHelper::quickNotEmptyAssert("Leqee AAv3 API is sleeping.", $result);
        $json = json_decode($result, true);
        ArkHelper::quickNotEmptyAssert("Leqee AAv3 API responded wrong thing", !empty($json));

        $response_data = ArkHelper::readTarget($json, 'serviceResponse');
        if (isset($response_data['authenticationSuccess'])) {
            $user_info = ArkHelper::readTarget($json, ['serviceResponse', 'authenticationSuccess', 'attributes']);
            $row = (new UserModel())->selectRow(['username' => $user_info['user_name'], "user_org" => "LEQEE"]);
            if (empty($row)) {
                $user_data = [
                    "username" => $user_info['user_name'],
                    "realname" => $user_info['real_name'],
                    "email" => $user_info['email'],
                    "user_type" => UserModel::USER_TYPE_USER,
                    "status" => $user_info['status'] === "ALIVE" ? UserModel::USER_STATUS_NORMAL : $user_info['status'],
                    "user_org" => 'LEQEE',
                ];
                $replaced = (new UserModel())->replace($user_data);
                HubCore::getLogger()->info("REPLACED USER CACHE", ['afx' => $replaced, 'data' => $user_data]);
                ArkHelper::quickNotEmptyAssert("Cannot update user from Leqee AAv3", $replaced);
                $row = (new UserModel())->selectRow(['username' => $user_info['user_name'], "user_org" => "LEQEE"]);
            }
            HubCore::getLogger()->info("validateAuthPair finally get user row", ["row" => $row]);
            $userEntity = UserEntity::instanceByRow($row);
            return SessionEntity::createSessionForUser($userEntity);
        }else {
            throw new \Exception($result);
        }
    }
}