<?php

namespace App\Controllers;

use App\Core\Request;
use App\Services\ProductService;
use App\Validators\ProductValidator;
use Exception;

class ProductController extends BaseController
{
    private ProductService $service;

    public function __construct()
    {
        $this->service = new ProductService();
    }

    /**
     * GET /products
     */
    public function index(): void
    {
        $page = (int) Request::query('page');
        if ($page <= 0) $page = 1;

        $perPage = (int) Request::query('per_page');
        if ($perPage <= 0) $perPage = 25;

        $filters = [
            'page' => $page,
            'per_page' => $perPage,
            'limit' => $perPage,
            'offset' => ($page - 1) * $perPage,
            'search' => Request::query('search'),
            'status' => Request::query('status'),
            'category_id' => Request::query('category_id'),
            'brand_id' => Request::query('brand_id'),
            'sort' => Request::query('sort'),
        ];

        $productsList = $this->service->getAll($filters);
        $total = $this->service->getCount($filters);

        $products = array_map(
            fn($product) => $product->toArray(),
            $productsList
        );

        $this->success([
            'data' => $products,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage
        ]);
    }
/**
 * GET /products/{id}
 */
public function show(): void
{
    $id = (int) Request::param('id');

    $product = $this->service->getById($id);

    if (!$product) {

        $this->error(
            "Product not found",
            404
        );

        return;
    }

    $this->success(
        $product->toArray()
    );
}

/**
 * PUT /products/{id}
 */
public function update(): void
{
    $id = (int) Request::param('id');

    $data = Request::body();

    $validator = new ProductValidator();

    $validation = $validator->validate($data);

    if (!$validation['valid']) {

        $this->error(
            "Validation Failed",
            422,
            $validation['errors']
        );

        return;
    }

    try {

        $this->service->update(
            $id,
            $data
        );

        $this->success(
            [],
            "Product Updated"
        );

    } catch (Exception $e) {

        $this->error(
            $e->getMessage(),
            400
        );

    }
}

    /**
     * POST /products
     */
    public function store(): void
    {
        $data = Request::body();

        $validator = new ProductValidator();

        $validation = $validator->validate($data);

        if (!$validation['valid']) {
            $this->error(
                "Validation Failed",
                422,
                $validation['errors']
            );
            return;
        }

        try {

            $product = $this->service->create($data);

            $this->success(
                $product->toArray(),
                "Product Created",
                201
            );

        } catch (Exception $e) {

            $this->error(
                $e->getMessage(),
                400
            );

        }
    }

    /**
 * DELETE /products/{id}
 */
public function destroy(): void
{
    $id = (int) Request::param('id');

    try {

        $this->service->delete($id);

        $this->success(
            [],
            "Product Deleted"
        );

    } catch (Exception $e) {

        $this->error(
            $e->getMessage(),
            400
        );

    }
}

    /**
     * POST /api/admin/products/bulk-action
     */
    public function bulkAction(): void
    {
        $data = Request::body();
        $action = $data['action'] ?? null;
        $ids = $data['ids'] ?? [];

        if (!$action || empty($ids)) {
            $this->error("Invalid request parameters", 400);
            return;
        }

        try {
            if ($action === 'delete') {
                foreach ($ids as $id) {
                    $this->service->delete((int)$id);
                }
                $this->success([], "Products Deleted");
            } elseif ($action === 'activate') {
                foreach ($ids as $id) {
                    $this->service->updateStatus((int)$id, 'ACTIVE');
                }
                $this->success([], "Products Activated");
            } elseif ($action === 'deactivate') {
                foreach ($ids as $id) {
                    $this->service->updateStatus((int)$id, 'DRAFT');
                }
                $this->success([], "Products Deactivated");
            } else {
                $this->error("Invalid action", 400);
            }
        } catch (Exception $e) {
            $this->error($e->getMessage(), 400);
        }
    }

    /**
     * PUT /api/admin/products/{id}/inventory
     */
    public function updateInventory(int $id): void
    {
        $id = $id ?: (int) Request::param('id');
        $data = Request::body();

        $qty = isset($data['quantity']) ? (int)$data['quantity'] : null;
        $lowStock = isset($data['low_stock_threshold']) ? (int)$data['low_stock_threshold'] : null;

        if ($qty === null && $lowStock === null) {
            $this->error("No inventory details provided", 400);
            return;
        }

        try {
            $db = \App\Core\Database::connection();
            $updFields = [];
            $updParams = [':product_id' => $id];
            if ($qty !== null) {
                $updFields[] = "quantity = :quantity";
                $updParams[':quantity'] = $qty < 0 ? 0 : $qty;
            }
            if ($lowStock !== null) {
                $updFields[] = "low_stock_threshold = :low_stock_threshold";
                $updParams[':low_stock_threshold'] = $lowStock < 0 ? 0 : $lowStock;
            }

            if (!empty($updFields)) {
                $db->prepare("UPDATE inventory SET " . implode(", ", $updFields) . " WHERE product_id = :product_id")->execute($updParams);
            }

            $product = $this->service->getById($id);
            $this->success($product->toArray(), "Inventory Updated");
        } catch (Exception $e) {
            $this->error($e->getMessage(), 400);
        }
    }
}