<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
if (
    isset($_SERVER['SCRIPT_NAME'], $_SERVER['REQUEST_URI']) &&
    str_ends_with($_SERVER['SCRIPT_NAME'], '/public/index.php') &&
    !str_starts_with($_SERVER['REQUEST_URI'], substr($_SERVER['SCRIPT_NAME'], 0, -9))
) {
    $_SERVER['SCRIPT_NAME'] = str_replace('/public/index.php', '/index.php', $_SERVER['SCRIPT_NAME']);
    $_SERVER['PHP_SELF'] = str_replace('/public/index.php', '/index.php', $_SERVER['PHP_SELF'] ?? '');
}

/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
