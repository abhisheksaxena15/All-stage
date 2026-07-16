<?php
/*
Brands
    GET
    POST
    DELETE

Categories
    GET
    POST

Products
    GET
    GET {id}
    POST
    PUT {id}
    DELETE {id}

Product Images
    POST
*/

use App\Controllers\BrandController;

$router->get(
    '/api/admin/brands',
    [BrandController::class, 'index']
);

$router->post(
    '/api/admin/brands',
    [BrandController::class, 'store']
);

$router->delete(
    '/api/admin/brands',
    [BrandController::class, 'destroy']
);

use App\Controllers\CategoryController;

$router->get(
    '/api/admin/categories',
    [CategoryController::class, 'index']
);

$router->post(
    '/api/admin/categories',
    [CategoryController::class, 'store']
);

use App\Controllers\SubcategoryController;

$router->get(
    '/api/admin/subcategories',
    [SubcategoryController::class, 'index']
);

$router->post(
    '/api/admin/subcategories',
    [SubcategoryController::class, 'store']
);

use App\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| Products
|--------------------------------------------------------------------------
*/

$router->get(
    '/api/admin/products',
    [ProductController::class, 'index']
);

$router->get(
    '/api/admin/products/{id}',
    [ProductController::class, 'show']
);

$router->post(
    '/api/admin/products',
    [ProductController::class, 'store']
);

$router->put(
    '/api/admin/products/{id}',
    [ProductController::class, 'update']
);

$router->delete(
    '/api/admin/products/{id}',
    [ProductController::class, 'destroy']
);
use App\Controllers\ProductImageController;

$router->post(
    '/api/admin/products/images',
    [ProductImageController::class,'upload']
);

use App\Controllers\DashboardController;

$router->get(
    '/api/admin/dashboard/stats',
    [DashboardController::class, 'stats']
);

$router->get(
    '/api/admin/dashboard/revenue',
    [DashboardController::class, 'revenue']
);

$router->get(
    '/api/admin/dashboard/categories',
    [DashboardController::class, 'categories']
);

$router->get(
    '/api/admin/orders',
    [DashboardController::class, 'recentOrders']
);

$router->get(
    '/api/admin/products/low-cost_price',
    [DashboardController::class, 'lowStock']
);