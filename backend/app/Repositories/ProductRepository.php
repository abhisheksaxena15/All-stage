<?php

namespace App\Repositories;

use App\Models\Product;
use PDO;

class ProductRepository extends BaseRepository
{
    protected string $table = 'products';

    public function create(Product $product): int
    {
        $sql = "INSERT INTO products
        (
            brand_id,
            category_id,
            subcategory_id,
            name,
            slug,
            sku,
            short_description,
            description,
            selling_price,
            compare_price,
            cost_price,
            status,
            featured,
            new_arrival,
            best_seller,
            sort_order
        )
        VALUES
        (
            :brand_id,
            :category_id,
            :subcategory_id,
            :name,
            :slug,
            :sku,
            :short_description,
            :description,
            :selling_price,
            :compare_price,
            :cost_price,
            :status,
            :featured,
            :new_arrival,
            :best_seller,
            :sort_order
        )";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([

            ':brand_id' => $product->getBrandId(),

            ':category_id' => $product->getCategoryId(),

            ':subcategory_id' => $product->getSubcategoryId(),

            ':name' => $product->getName(),

            ':slug' => $product->getSlug(),

            ':sku' => $product->getSku(),

            ':short_description' => $product->getShortDescription(),

            ':description' => $product->getDescription(),

            ':selling_price' => $product->getSellingPrice(),

            ':compare_price' => $product->getComparePrice(),

            ':cost_price' => $product->getCostPrice(),

            ':status' => $product->getStatus(),

            ':featured' => $product->isFeatured(),

            ':new_arrival' => $product->isNewArrival(),

            ':best_seller' => $product->isBestSeller(),

            ':sort_order' => 0

        ]);

        return (int)$this->db->lastInsertId();
    }

    public function update(Product $product): bool
    {
        $sql = "UPDATE products SET

            brand_id=:brand_id,

            category_id=:category_id,

            subcategory_id=:subcategory_id,

            name=:name,

            slug=:slug,

            sku=:sku,

            short_description=:short_description,

            description=:description,

            selling_price=:selling_price,

            compare_price=:compare_price,

            cost_price=:cost_price,

            status=:status,

            featured=:featured,

            new_arrival=:new_arrival,

            best_seller=:best_seller

            WHERE id=:id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([

            ':id'=>$product->getId(),

            ':brand_id'=>$product->getBrandId(),

            ':category_id'=>$product->getCategoryId(),

            ':subcategory_id'=>$product->getSubcategoryId(),

            ':name'=>$product->getName(),

            ':slug'=>$product->getSlug(),

            ':sku'=>$product->getSku(),

            ':short_description'=>$product->getShortDescription(),

            ':description'=>$product->getDescription(),

            ':selling_price'=>$product->getSellingPrice(),

            ':compare_price'=>$product->getComparePrice(),

            ':cost_price'=>$product->getCostPrice(),

            ':status'=>$product->getStatus(),

            ':featured'=>$product->isFeatured(),

            ':new_arrival'=>$product->isNewArrival(),

            ':best_seller'=>$product->isBestSeller()

        ]);
    }

    public function findBySlug(string $slug): ?Product
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM products WHERE slug=:slug LIMIT 1"
        );

        $stmt->execute([
            ':slug'=>$slug
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new Product($row) : null;
    }

    public function findBySku(string $sku): ?Product
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM products WHERE sku=:sku LIMIT 1"
        );

        $stmt->execute([
            ':sku'=>$sku
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new Product($row) : null;
    }
}