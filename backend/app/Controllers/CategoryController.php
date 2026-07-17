<?php

namespace App\Controllers;

use App\Core\Request;
use App\Services\CategoryService;
use App\Validators\CategoryValidator;
use Exception;

class CategoryController extends BaseController
{
    private CategoryService $service;

    public function __construct()
    {
        $this->service = new CategoryService();
    }

    /**
     * GET /api/admin/categories
     */
    public function index(): void
    {
        $page = Request::query('page');

        $categories = array_map(
            fn($category) => $category->toArray(),
            $this->service->getAll()
        );

        if ($page !== null && $page !== '') {
            $perPage = (int)Request::query('per_page', 25);
            $pageNum = (int)$page;

            $total = count($categories);
            $offset = ($pageNum - 1) * $perPage;
            $sliced = array_slice($categories, $offset, $perPage);

            $this->success([
                'data' => $sliced,
                'total' => $total,
                'page' => $pageNum,
                'per_page' => $perPage
            ]);
        } else {
            $this->success($categories);
        }
    }

    /**
     * POST /api/admin/categories
     */
    public function store(): void
    {
        $data = Request::body();

        $validator = new CategoryValidator();
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
            $category = $this->service->create($data);
            $this->success(
                $category->toArray(),
                "Category Created",
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
     * DELETE /api/admin/categories/{id}
     */
    public function destroy(): void
    {
        $id = (int) Request::param('id');

        try {
            $this->service->delete($id);
            $this->success(
                [],
                "Category Deleted"
            );
        } catch (Exception $e) {
            $this->error(
                $e->getMessage(),
                400
            );
        }
    }
}
