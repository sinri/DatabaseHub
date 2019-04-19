<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 2/27/19
 * Time: 10:54 AM
 */

namespace sinri\databasehub\entity;


use sinri\ark\database\model\ArkSQLCondition;
use sinri\databasehub\model\DingtalkScanLoginSessionModel;

class DingtalkScanLoginSessionEntity
{
    public $loginSessionId;
    public $coreUserId;
    public $token;
    public $userSessionToken;
    public $expire;
    public $updateTime;  // 更新时间
    public $createTime;  // 创建时间
    /**
     * @var  DingtalkScanLoginSessionModel
     */
    protected $dingtalkScanLoginSessionModel;

    const EXPIRE_SECONDS = 300; // 300s

    public function __construct()
    {
        $this->loginSessionId = null;
        $this->coreUserId = null;
        $this->token = null;
        $this->userSessionToken = null;
        $this->expire = null;
        $this->updateTime = null;
        $this->createTime = null;
        $this->dingtalkScanLoginSessionModel = new DingtalkScanLoginSessionModel();
    }

    /**
     * @return  int login_session_id
     */
    public function createToken()
    {
        $now = date('Y-m-d H:i:s');
        $this->token = uniqid(date('YmdHis') .'.'. rand(0, 100000));
        return $this->dingtalkScanLoginSessionModel->insert([
            'token' => $this->token,
            'expire' => (time() + static::EXPIRE_SECONDS),
            'update_time' => $now,
            'create_time' => $now
        ]);
    }

    /**
     * @param $core_user_id
     * @return  int afx
     */
    public function setUser($core_user_id)
    {
        $now = date('Y-m-d H:i:s');
        return $this->dingtalkScanLoginSessionModel->update(['login_session_id' => $this->loginSessionId], [
            'core_user_id' => $core_user_id,
            'update_time' => $now
        ]);
    }

    /**
     * @param $token
     * @return  int afx
     */
    public function setUserSessionToken($token)
    {
        $now = date('Y-m-d H:i:s');
        return $this->dingtalkScanLoginSessionModel->update(['login_session_id' => $this->loginSessionId], [
            'user_session_token' => $token,
            'update_time' => $now
        ]);
    }


    /**
     * @param  array $row
     * @return  bool|DingtalkScanLoginSessionEntity
     */
    public function loadEntity($row)
    {
        if (!$row) return false;
        $entity = new DingtalkScanLoginSessionEntity();
        $entity->loginSessionId = $row['login_session_id'];
        $entity->coreUserId = $row['core_user_id'];
        $entity->token = $row['token'];
        $entity->userSessionToken = $row['user_session_token'];
        $entity->expire = $row['expire'];
        $entity->updateTime = $row['update_time'];
        $entity->createTime = $row['create_time'];
        return $entity;
    }

    /**
     * @param    int $token
     * @return  bool|DingtalkScanLoginSessionEntity
     */
    public function getByToken($token)
    {
        $conditions = ['token' => $token];
        $conditions[] = new ArkSQLCondition('expire', ArkSQLCondition::OP_EGT, time());
        $row = $this->dingtalkScanLoginSessionModel->selectRow($conditions);
        return $this->loadEntity($row);
    }

    /**
     * @param    int $token
     * @return  bool|DingtalkScanLoginSessionEntity
     */
    public function getByTokenWithoutCheck($token)
    {
        $conditions = ['token' => $token];
        $row = $this->dingtalkScanLoginSessionModel->selectRow($conditions);
        return $this->loadEntity($row);
    }

    /**
     * @param    int $user_session_token
     * @return  bool|DingtalkScanLoginSessionEntity
     */
    public function getByUserSessionToken($user_session_token)
    {
        $conditions = ['user_session_token' => $user_session_token];
        $row = $this->dingtalkScanLoginSessionModel->selectRow($conditions);
        return $this->loadEntity($row);
    }
}