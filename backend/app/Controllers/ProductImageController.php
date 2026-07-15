<?php

namespace App\Controllers;

use App\Services\ProductImageService;

class ProductImageController extends BaseController
{
    private ProductImageService $service;

    public function __construct()
    {
        $this->service = new ProductImageService();
    }

    public function upload(): void
    {
        $productId = (int)($_POST['product_id'] ?? 0);

        if (!$productId) {

            $this->error(
                "Product ID required",
                400
            );

            return;
        }

        if (!isset($_FILES['image'])) {

            $this->error(
                "Image required",
                400
            );

            return;
        }

        $image = $this->service->upload(
            $productId,
            $_FILES['image']
        );

        $this->success(
            $image->toArray(),
            "Image Uploaded"
        );
    }
}