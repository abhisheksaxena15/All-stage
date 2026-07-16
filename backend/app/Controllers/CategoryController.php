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
        $categories = array_map(
            fn($category) => $category->toArray(),
            $this->service->getAll()
        );

        $this->success($categories);
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
}
