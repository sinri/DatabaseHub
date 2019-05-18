<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018/12/4
 * Time: 10:14 AM
 */

use sinri\ark\io\ArkWebOutput;
use sinri\ark\web\ArkRouteErrorHandler;
use sinri\databasehub\filter\MainFilter;

require_once __DIR__ . '/autoload.php';

Ark()->webService()->getRouter()->setErrorHandler(
    ArkRouteErrorHandler::buildWithCallback(
        function ($error_message, $status_code) {
            // here status might be too large, such as from PDO exception with mysql error...
            Ark()->webOutput()->sendHTTPCode($status_code ? $status_code : 200);
            Ark()->webOutput()->setContentTypeHeader("application/json");
            Ark()->webOutput()->jsonForAjax(
                ArkWebOutput::AJAX_JSON_CODE_FAIL,
                [
                    "status" => $status_code,
                    "error" => $error_message,
                ]
            );
        }
    )
);

Ark()->webService()->getRouter()->loadAllControllersInDirectoryAsCI(
    __DIR__ . '/controller',
    "api/",
    "\\sinri\\databasehub\\controller\\",
    [
        MainFilter::class
    ]
);

Ark()->webService()->getRouter()->get("", function () {
    header("Location: frontend/index.html");
});

Ark()->webService()->handleRequestForWeb();