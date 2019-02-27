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

class LoginPluginWithDingtalk extends LoginPlugin
{
    protected function apiUrl($subUrl)
    {
        return HubCore::getConfig(['aa', 'domain'], "") . $subUrl;
    }

    /**
     * @param $username
     * @param $password
     * @return SessionEntity
     * @throws \Exception
     */
    public function validateAuthPair($username, $password = '')
    {
        $curl = new ArkCurl();
        $curl->setLogger(HubCore::getLogger());
        $tp_code = HubCore::getConfig(['aa', 'tp_code'], "");
        $tp_verification = HubCore::getConfig(['aa', 'tp_verification'], "");
        $result = $curl->prepareToRequestURL("POST", $this->apiUrl("/api/Delegate/getUserInfo"))
            ->setPostFormField("user_name", $username)
            ->setPostFormField("tp_code", $tp_code)
            ->setPostFormField("tp_verification", $tp_verification)
            ->execute(false);

        HubCore::getLogger()->info(__METHOD__ . '@' . __LINE__ . " AAv3 API Response:" . $result, ["req" => ['username' => $username,
            'tp_code' => $tp_code, 'tp_verification' => $tp_verification]]);

        ArkHelper::quickNotEmptyAssert("Leqee AAv3 API is sleeping.", $result);
        $json = json_decode($result, true);
        ArkHelper::quickNotEmptyAssert("Leqee AAv3 API responded wrong thing", !empty($json));

        $code = ArkHelper::readTarget($json, 'code');
        if ($code === 'OK') {
            //$token=ArkHelper::readTarget($json,['data','token']);
            //$token_life=ArkHelper::readTarget($json,['data','token_life']);
            $user_info = ArkHelper::readTarget($json, ['data']);

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
        } elseif ($code === 'FAIL') {
            throw new \Exception(ArkHelper::readTarget($json, ['data']));
        } else {
            throw new \Exception("Unknown AAv3 API Code: " . json_encode($code));
        }
    }
}