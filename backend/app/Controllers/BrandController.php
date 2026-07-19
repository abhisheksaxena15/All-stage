<?php

namespace App\Controllers;

use App\Core\Request;
use App\Services\BrandService;
use App\Validators\BrandValidator;
use Exception;

class BrandController extends BaseController
{
    private BrandService $service;

    public function __construct()
    {
        $this->service = new BrandService();
    }

    /**
     * GET /api/admin/brands
     */
    public function index(): void
    {
        $page = Request::query('page');

        $brands = array_map(
            fn($brand) => $brand->toArray(),
            $this->service->getAll()
        );

        if ($page !== null && $page !== '') {
            $perPage = (int)Request::query('per_page', 25);
            $pageNum = (int)$page;

            $total = count($brands);
            $offset = ($pageNum - 1) * $perPage;
            $sliced = array_slice($brands, $offset, $perPage);

            $this->success([
                'data' => $sliced,
                'total' => $total,
                'page' => $pageNum,
                'per_page' => $perPage
            ]);
        } else {
            $this->success($brands);
        }
    }

    /**
     * POST /api/admin/brands
     */
    public function store(): void
    {
        $data = Request::body();

        $validator = new BrandValidator();

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

            $brand = $this->service->create($data);

            $this->success(

                $brand->toArray(),

                "Brand Created",

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
     * DELETE /api/admin/brands/{id}
     */
    public function destroy(int $id): void
    {
        $this->service->delete($id);

        $this->success(
            [],
            "Brand Deleted"
        );
    }

    /**
     * PUT /api/admin/brands/{id}
     */
    public function update(int $id): void
    {
        $id = $id ?: (int) Request::param('id');
        $data = Request::body();

        $validator = new BrandValidator();
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
            $brand = $this->service->update($id, $data);
            $this->success(
                $brand->toArray(),
                "Brand Updated"
            );
        } catch (Exception $e) {
            $this->error(
                $e->getMessage(),
                400
            );
        }
    }
}