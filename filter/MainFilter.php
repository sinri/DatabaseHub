<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018/12/4
 * Time: 10:20 AM
 */

namespace sinri\databasehub\filter;


use sinri\ark\web\ArkRequestFilter;
use sinri\databasehub\entity\SessionEntity;

class MainFilter extends ArkRequestFilter
{

    /**
     * Check request data with $_REQUEST, $_SESSION, $_SERVER, etc.
     * And decide if the request should be accepted.
     * If return false, the request would be thrown.
     * You can pass anything into $preparedData, that controller might use it (not sure, by the realization)
     * @param $path
     * @param $method
     * @param $params
     * @param mixed $preparedData
     * @param int $responseCode
     * @param null|string $error
     * @return bool
     */
    public function shouldAcceptRequest($path, $method, $params, &$preparedData = null, &$responseCode = 200, &$error = null)
    {
        //echo $path;
        if ($path === '/api/LoginController/login') return true;

        try {
            $token = Ark()->webInput()->readRequest("token", "");
            $preparedData['session'] = SessionEntity::instanceByToken($token);
            return true;
        } catch (\Exception $exception) {
            $responseCode = 403;
            $error = "Session Error. " . $exception->getMessage();
            return false;
        }


    }

    /**
     * Give filter a name for Error Report
     * @return string
     */
    public function filterTitle()
    {
        return "MainFilter";
    }
}