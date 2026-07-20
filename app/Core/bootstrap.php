<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', getenv('APP_DEBUG') === 'false' ? '0' : '1');

date_default_timezone_set('Asia/Jakarta');

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (str_starts_with($class, $prefix)) {
        $path = BASE_PATH . '/app/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($path)) {
            require $path;
        }
    }
});

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

require BASE_PATH . '/app/Core/helpers.php';
