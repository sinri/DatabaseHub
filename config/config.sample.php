<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018/12/4
 * Time: 11:37 AM
 */

$config = [
    'pdo' => [
        "host" => "",
        "port" => "",
        "username" => "",
        "password" => "",
        "database" => "",
        "charset" => \sinri\ark\database\pdo\ArkPDOConfig::CHARSET_UTF8,
        "engine" => \sinri\ark\database\pdo\ArkPDOConfig::ENGINE_MYSQL,
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
];