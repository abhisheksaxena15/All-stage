<?php
declare(strict_types=1);

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (!empty($origin)) {
    if (preg_match('/^http:\/\/localhost(:\d+)?$/', $origin)) {
        header("Access-Control-Allow-Origin: $origin");
    } else {
        $allowedOrigins = [
            "http://localhost:5173",
            "http://localhost:8081",
            "http://localhost:8082",
        ];
        if (in_array($origin, $allowedOrigins, true)) {
            header("Access-Control-Allow-Origin: $origin");
        }
    }
}

header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}



require_once __DIR__ . '/../vendor/autoload.php';


$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

if (isset($_ENV['TIMEZONE'])) {
    date_default_timezone_set($_ENV['TIMEZONE']);
}


use App\Core\Router;

$router = new Router();

/*
|--------------------------------------------------------------------------
| Load Routes
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../routes/admin.php';
require_once __DIR__ . '/../routes/product.php';
require_once __DIR__ . '/../routes/auth.php';
require_once __DIR__ . '/../routes/public.php';

/*
|--------------------------------------------------------------------------
| Dispatch Request
|--------------------------------------------------------------------------
*/

$router->dispatch();
