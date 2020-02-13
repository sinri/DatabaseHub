<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018/12/4
 * Time: 10:14 AM
 */

use sinri\ark\io\ArkWebOutput;
use sinri\ark\web\implement\ArkRouteErrorHandlerAsCallback;
use sinri\databasehub\filter\MainFilter;

require_once __DIR__ . '/autoload.php';

Ark()->webService()->getRouter()->setErrorHandler(
    new class extends ArkRouteErrorHandlerAsCallback {

        /**
         * @inheritDoc
         */
        public function requestErrorCallback($errorMessage, $httpCode)
        {
            // here status might be too large, such as from PDO exception with mysql error...
            Ark()->webOutput()->sendHTTPCode($httpCode ? $httpCode : 200);
            Ark()->webOutput()->setContentTypeHeader("application/json");
            Ark()->webOutput()->jsonForAjax(
                ArkWebOutput::AJAX_JSON_CODE_FAIL,
                [
                    "status" => $httpCode,
                    "error" => $errorMessage,
                ]
            );
        }
    }
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