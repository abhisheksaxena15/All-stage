<?php

namespace App\Services;

use App\Helpers\UploadHelper;
use App\Models\ProductImage;
use App\Repositories\ProductImageRepository;

class ProductImageService
{
    private ProductImageRepository $repository;

    public function __construct()
    {
        $this->repository = new ProductImageRepository();
    }

    public function upload(
        int $productId,
        array $file
    ): ProductImage {

        $path = UploadHelper::uploadProductImage($file);

        $image = new ProductImage();

        $image->setProductId($productId);

        $image->setImagePath($path);

        $image->setPrimary(false);

        $image->setSortOrder(0);

        $this->repository->create($image);

        return $image;
    }
}