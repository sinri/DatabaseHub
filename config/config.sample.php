<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018/12/4
 * Time: 11:37 AM
 */

use sinri\ark\database\pdo\ArkPDOConfig;

$config = [
    'pdo' => [
        "host" => "",
        "port" => "",
        "username" => "",
        "password" => "",
        "database" => "",
        "charset" => ArkPDOConfig::CHARSET_UTF8,
        "engine" => ArkPDOConfig::ENGINE_MYSQL,
    ],
    "logger" => [
        "path" => __DIR__ . '/../log',
        "level" => "info",
    ],
    "store" => [
        "path" => __DIR__ . '/../store',
    ],
    "plugins" => [
        "login" => "LoginPluginStandalone",
    ],
    "queue" => [
        "max_worker" => 5,
    ],
    'dashboard' => [
        'doc_path' => __DIR__ . '/../docs/DashboardDoc_Leqee_CN.md',
        //'doc'=>"lalala", // this field would override the doc_path
    ]
];