<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/app/Core/bootstrap.php';

$router = new App\Core\Router();

require BASE_PATH . '/app/Config/routes.php';

$router->dispatch();
