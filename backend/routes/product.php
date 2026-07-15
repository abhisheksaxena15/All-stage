<?php

use App\Controllers\ProductController;

$router->get(
    '/api/admin/products',
    [ProductController::class, 'index']
);

$router->post(
    '/api/admin/products',
    [ProductController::class, 'store']
);