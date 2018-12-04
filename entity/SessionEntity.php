<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018/12/4
 * Time: 11:42 AM
 */

namespace sinri\databasehub\entity;


use sinri\ark\core\ArkHelper;
use sinri\databasehub\model\SessionModel;

class SessionEntity
{
    public $sessionId;
    public $user;
    public $token;
    public $since;
    public $expire;

    /**
     * @param $token
     * @return SessionEntity
     * @throws \Exception
     */
    public static function instanceByToken($token)
    {
        $row = (new SessionModel())->selectRow(['token' => $token]);

        ArkHelper::quickNotEmptyAssert("Invalid Token!", $row);

        if ($row['expire'] <= time()) {
            throw new \Exception("Session Expired");
        }

        $session = new SessionEntity();
        $session->sessionId = $row['session_id'];
        $session->token = $row['token'];
        $session->since = $row['since'];
        $session->expire = $row['expire'];
        $session->user = UserEntity::instanceByUserId($row['user_id']);

        return $session;
    }

    /**
     * @param UserEntity $user
     * @return SessionEntity
     * @throws \Exception
     */
    public static function createSessionForUser($user)
    {
        $session = new SessionEntity();

        $session->token = uniqid(md5($user->userId . "@" . time()));
        $session->since = date('Y-m-d H:i:s');
        $session->expire = time() + 8 * 3600;
        $session->user = $user;

        $sessionId = (new SessionModel())->insert([
            'user_id' => $user->userId,
            'token' => $session->token,
            'since' => $session->since,
            'expire' => $session->expire,
        ]);
        $session->sessionId = $sessionId;

        ArkHelper::quickNotEmptyAssert("Cannot Create Session!", $sessionId);

        return $session;
    }
}