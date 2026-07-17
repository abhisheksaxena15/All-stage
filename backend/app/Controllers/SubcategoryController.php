<?php

namespace App\Controllers;

use App\Core\Request;
use App\Services\SubcategoryService;
use App\Validators\SubcategoryValidator;
use Exception;

class SubcategoryController extends BaseController
{
    private SubcategoryService $service;

    public function __construct()
    {
        $this->service = new SubcategoryService();
    }

    /**
     * GET /api/admin/subcategories
     */
    public function index(): void
    {
        $page = Request::query('page');
        $categoryId = Request::query('category_id');
        $categoryId = $categoryId !== null && $categoryId !== '' ? (int)$categoryId : null;

        $subcategories = array_map(
            fn($sub) => $sub->toArray(),
            $this->service->getAll($categoryId)
        );

        if ($page !== null && $page !== '') {
            $perPage = (int)Request::query('per_page', 25);
            $pageNum = (int)$page;

            $total = count($subcategories);
            $offset = ($pageNum - 1) * $perPage;
            $sliced = array_slice($subcategories, $offset, $perPage);

            $this->success([
                'data' => $sliced,
                'total' => $total,
                'page' => $pageNum,
                'per_page' => $perPage
            ]);
        } else {
            $this->success($subcategories);
        }
    }

    /**
     * POST /api/admin/subcategories
     */
    public function store(): void
    {
        $data = Request::body();

        $validator = new SubcategoryValidator();
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
            $subcategory = $this->service->create($data);
            $this->success(
                $subcategory->toArray(),
                "Subcategory Created",
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
