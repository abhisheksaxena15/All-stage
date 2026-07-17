<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\ProductRepository;
use App\Helpers\SlugHelper;
use App\Services\ProductImageService;
use App\Repositories\ProductImageRepository;
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
        if (!empty($data['sku']) && $this->repository->findBySku($data['sku'])) {
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
        $product->setSlug($slug);
        $product->setSku($data['sku'] ?? '');

        $product->setShortDescription(
            $data['short_description'] ?? null
        );

        $product->setDescription(
            $data['description'] ?? null
        );

        $product->setSellingPrice(
            (float)($data['selling_price'] ?? 0)
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

        $primaryIndex = isset($data['primary_image_index']) ? (int)$data['primary_image_index'] : 0;

        // Process image uploads if any
        if (isset($_FILES['images']) && is_array($_FILES['images']['tmp_name'])) {
            $imageService = new ProductImageService();
            $files = $_FILES['images'];
            $fileCount = count($files['tmp_name']);

            for ($i = 0; $i < $fileCount; $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $singleFile = [
                        'name' => $files['name'][$i],
                        'type' => $files['type'][$i],
                        'tmp_name' => $files['tmp_name'][$i],
                        'error' => $files['error'][$i],
                        'size' => $files['size'][$i],
                    ];
                    // Set primary based on user selection index
                    $isPrimary = ($i === $primaryIndex);
                    $imageService->upload($id, $singleFile, $isPrimary);
                }
            }
        }

        return $this->repository->findById($id);
    }

    /**
     * Get All Products with Filters
     */
    public function getAll(array $filters = []): array
    {
        return $this->repository->findAll($filters);
    }

    /**
     * Get Product Count with Filters
     */
    public function getCount(array $filters = []): int
    {
        return $this->repository->countFiltered($filters);
    }

    /**
     * Get Product By ID
     */
    public function getById(int $id): ?Product
    {
        return $this->repository->findById($id);
    }

    /**
     * Update Product
     */
    public function update(int $id, array $data): bool
    {
        $product = $this->repository->findById($id);

        if (!$product) {
            throw new Exception("Product not found.");
        }

        // Duplicate slug check
        if (!empty($data['slug']) && $data['slug'] !== $product->getSlug()) {
            if ($this->repository->findBySlug($data['slug'])) {
                throw new Exception("Product slug already exists.");
            }
        }

        // Duplicate SKU check
        if (!empty($data['sku']) && $data['sku'] !== $product->getSku()) {
            if ($this->repository->findBySku($data['sku'])) {
                throw new Exception("SKU already exists.");
            }
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
        $product->setSku($data['sku'] ?? '');

        $product->setShortDescription(
            $data['short_description'] ?? null
        );

        $product->setDescription(
            $data['description'] ?? null
        );

        $product->setSellingPrice(
            (float)($data['selling_price'] ?? 0)
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

        $updated = $this->repository->update($product);

        if ($updated) {
            $imageRepo = new ProductImageRepository();

            // Handle removed images
            if (!empty($data['removed_image_ids'])) {
                // Check if it's an array or string
                $removedIds = is_array($data['removed_image_ids'])
                    ? $data['removed_image_ids']
                    : explode(',', $data['removed_image_ids']);

                foreach ($removedIds as $imgId) {
                    $imgId = (int)$imgId;
                    if ($imgId > 0) {
                        $image = $imageRepo->findById($imgId);
                        if ($image) {
                            $path = __DIR__ . '/../../public/' . ltrim($image->getImagePath(), '/');
                            if (file_exists($path)) {
                                @unlink($path);
                            }
                            $imageRepo->delete($imgId);
                        }
                    }
                }
            }

            // Clear primary status if primary fields are provided
            if (isset($data['primary_image_id']) || isset($data['primary_image_index'])) {
                $imageRepo->clearPrimaryStatus($id);
            }

            // Set primary image for existing image
            if (!empty($data['primary_image_id'])) {
                $imageRepo->setPrimaryImage((int)$data['primary_image_id']);
            }

            // Process new image uploads
            if (isset($_FILES['images']) && is_array($_FILES['images']['tmp_name'])) {
                $imageService = new ProductImageService();
                $files = $_FILES['images'];
                $fileCount = count($files['tmp_name']);

                // Get primary index if set
                $primaryIndex = isset($data['primary_image_index']) ? (int)$data['primary_image_index'] : -1;

                for ($i = 0; $i < $fileCount; $i++) {
                    if ($files['error'][$i] === UPLOAD_ERR_OK) {
                        $singleFile = [
                            'name' => $files['name'][$i],
                            'type' => $files['type'][$i],
                            'tmp_name' => $files['tmp_name'][$i],
                            'error' => $files['error'][$i],
                            'size' => $files['size'][$i],
                        ];
                        // Mark as primary if index matches selection
                        $isPrimary = ($i === $primaryIndex);
                        $imageService->upload($id, $singleFile, $isPrimary);
                    }
                }
            }

            // Fallback: Make sure at least one image is marked primary
            $allImages = $imageRepo->db->query("
                SELECT id, is_primary FROM product_images
                WHERE product_id = " . (int)$id . "
                ORDER BY sort_order ASC, id ASC
            ")->fetchAll(PDO::FETCH_ASSOC);

            $hasPrimary = false;
            foreach ($allImages as $img) {
                if ($img['is_primary']) {
                    $hasPrimary = true;
                    break;
                }
            }

            if (!$hasPrimary && !empty($allImages)) {
                $imageRepo->setPrimaryImage((int)$allImages[0]['id']);
            }
        }

        return $updated;
    }

    /**
     * Delete Product
     */
    public function delete(int $id): bool
    {
        // Delete all images first
        $product = $this->repository->findById($id);
        if ($product && !empty($product->getImages())) {
            $imageRepo = new ProductImageRepository();
            foreach ($product->getImages() as $image) {
                $path = __DIR__ . '/../../public/' . ltrim($image->getImagePath(), '/');
                if (file_exists($path)) {
                    @unlink($path);
                }
                $imageRepo->delete((int)$image->getId());
            }
        }

        return $this->repository->delete($id);
    }

    /**
     * Update Product Status
     */
    public function updateStatus(int $id, string $status): bool
    {
        $product = $this->repository->findById($id);
        if (!$product) {
            throw new Exception("Product not found.");
        }
        $product->setStatus($status);
        return $this->repository->update($product);
    }
}