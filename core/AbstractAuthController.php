<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018/12/4
 * Time: 6:32 PM
 */

namespace sinri\databasehub\core;


use sinri\ark\core\ArkHelper;
use sinri\ark\web\implement\ArkWebController;
use sinri\databasehub\entity\SessionEntity;
use sinri\databasehub\model\UserModel;

abstract class AbstractAuthController extends ArkWebController
{
    /**
     * @var SessionEntity|null
     */
    protected $session;

    public function __construct()
    {
        parent::__construct();
        $this->session = ArkHelper::readTarget($this->filterGeneratedData, ['session'], null);
    }

    /**
     * @throws \Exception
     */
    protected function onlyAdminCanDoThis()
    {
        if (
            !$this->session
            ||
            $this->session->user->status !== UserModel::USER_STATUS_NORMAL
            ||
            $this->session->user->userType !== UserModel::USER_TYPE_ADMIN
        ) {
            throw new \Exception("You do not hold the permission to access!", 401);
        }
//        ArkHelper::quickNotEmptyAssert(
//            "You are not admin!",
//            $this->session,
//            $this->session->user->status!==UserModel::USER_STATUS_NORMAL,
//            $this->session->user->userType!==UserModel::USER_TYPE_ADMIN
//        );
    }
}