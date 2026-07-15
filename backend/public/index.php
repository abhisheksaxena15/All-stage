<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;

$router = new Router();

/*
|--------------------------------------------------------------------------
| Load Routes
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../routes/admin.php';
require_once __DIR__ . '/../routes/product.php';

/*
|--------------------------------------------------------------------------
| Dispatch Request
|--------------------------------------------------------------------------
*/

$router->dispatch();