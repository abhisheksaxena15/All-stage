<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\ProductRepository;
use App\Helpers\SlugHelper;
use Exception;

class ProductService
{
    private ProductRepository $repository;

    public function __construct()
    {
        $this->repository = new ProductRepository();
    }

    /**
     * Create Product
     */
    public function create(array $data): Product
    {
        // Generate slug if empty
        $slug = !empty($data['slug'])
            ? $data['slug']
            : SlugHelper::generate($data['name']);

        // Duplicate slug check
        if ($this->repository->findBySlug($slug)) {
            throw new Exception("Product slug already exists.");
        }

        // Duplicate SKU check
        if ($this->repository->findBySku($data['sku'])) {
            throw new Exception("SKU already exists.");
        }

        $product = new Product();

        $product->setBrandId((int)$data['brand_id']);
        $product->setCategoryId((int)$data['category_id']);
        $product->setSubcategoryId(
            !empty($data['subcategory_id'])
                ? (int)$data['subcategory_id']
                : null
        );

        $product->setName($data['name']);
        $product->setSlug($data['slug']);
        $product->setSku($data['sku']);

        $product->setShortDescription(
            $data['short_description'] ?? null
        );

        $product->setDescription(
            $data['description'] ?? null
        );

        $product->setSellingPrice(
            (float)$data['selling_price']
        );

        $product->setComparePrice(
            (float)($data['compare_price'] ?? 0)
        );

        $product->setCostPrice(
            (float)($data['cost_price'] ?? 0)
        );

        $product->setStatus(
            $data['status'] ?? 'DRAFT'
        );

        $product->setFeatured(
            (bool)($data['featured'] ?? false)
        );

        $product->setNewArrival(
            (bool)($data['new_arrival'] ?? false)
        );

        $product->setBestSeller(
            (bool)($data['best_seller'] ?? false)
        );

        $id = $this->repository->create($product);

        return $this->repository->findById($id);
    }

    /**
     * Get All Products
     */
    public function getAll(): array
    {
        return $this->repository->findAll();
    }

    /**
     * Get Product By ID
     */
    public function update(int $id, array $data): bool
    {
        $product = $this->repository->findById($id);

        if (!$product) {
            throw new Exception("Product not found.");
        }

        $product->setBrandId((int)$data['brand_id']);
        $product->setCategoryId((int)$data['category_id']);

        $product->setSubcategoryId(
            !empty($data['subcategory_id'])
                ? (int)$data['subcategory_id']
                : null
        );

        $product->setName($data['name']);
        $product->setSlug($data['slug']);
        $product->setSku($data['sku']);

        $product->setShortDescription(
            $data['short_description'] ?? null
        );

        $product->setDescription(
            $data['description'] ?? null
        );

        $product->setSellingPrice(
            (float)$data['selling_price']
        );

        $product->setComparePrice(
            (float)($data['compare_price'] ?? 0)
        );

        $product->setCostPrice(
            (float)($data['cost_price'] ?? 0)
        );

        $product->setStatus(
            $data['status'] ?? 'DRAFT'
        );

        $product->setFeatured(
            (bool)($data['featured'] ?? false)
        );

        $product->setNewArrival(
            (bool)($data['new_arrival'] ?? false)
        );

        $product->setBestSeller(
            (bool)($data['best_seller'] ?? false)
        );

        return $this->repository->update($product);
    }

    /**
     * Delete Product
     */
    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}