<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2018/12/4
 * Time: 9:56 AM
 */

use sinri\ark\core\ArkHelper;

require_once __DIR__ . '/vendor/autoload.php';

date_default_timezone_set("Asia/Shanghai");

ArkHelper::registerAutoload("sinri\\databasehub", __DIR__);