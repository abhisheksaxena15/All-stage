<?php

namespace App\Repositories;

use App\Models\ProductImage;

class ProductImageRepository extends BaseRepository
{
    protected string $table = 'product_images';

    protected string $model = ProductImage::class;

    public function create(ProductImage $image): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO product_images
            (
                product_id,
                image_path,
                alt_text,
                is_primary,
                sort_order
            )
            VALUES
            (
                :product_id,
                :image_path,
                :alt_text,
                :is_primary,
                :sort_order
            )
        ");

        $stmt->execute([

            ':product_id' => $image->getProductId(),

            ':image_path' => $image->getImagePath(),

            ':alt_text' => $image->getAltText(),

            ':is_primary' => $image->isPrimary(),

            ':sort_order' => $image->getSortOrder()

        ]);

        return (int)$this->db->lastInsertId();
    }
}