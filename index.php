<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018/12/4
 * Time: 10:14 AM
 */

require_once __DIR__ . '/autoload.php';

Ark()->webService()->getRouter()->setErrorHandler(
    \sinri\ark\web\ArkRouteErrorHandler::buildWithCallback(
        function ($error_message, $status_code) {
            Ark()->webOutput()->sendHTTPCode($status_code);
            Ark()->webOutput()->setContentTypeHeader("application/json");
            Ark()->webOutput()->jsonForAjax(
                \sinri\ark\io\ArkWebOutput::AJAX_JSON_CODE_FAIL,
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
        \sinri\databasehub\filter\MainFilter::class
    ]
);

Ark()->webService()->getRouter()->get("", function () {
    header("Location: frontend/index.html");
});

Ark()->webService()->handleRequestForWeb();