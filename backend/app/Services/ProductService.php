<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\ProductRepository;
use App\Helpers\SlugHelper;
use App\Services\ProductImageService;
use App\Repositories\ProductImageRepository;
use Exception;
use PDO;

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
        $db = \App\Core\Database::connection();
        $db->beginTransaction();

        try {
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

            // Create inventory record
            $qty = isset($data['quantity']) ? (int)$data['quantity'] : 0;
            if ($qty < 0) $qty = 0;
            $lowStock = isset($data['low_stock_threshold']) ? (int)$data['low_stock_threshold'] : 10;
            if ($lowStock < 0) $lowStock = 0;

            $invStmt = $db->prepare("INSERT INTO inventory (product_id, quantity, low_stock_threshold, warehouse) VALUES (:product_id, :quantity, :low_stock_threshold, 'Main Warehouse')");
            $invStmt->execute([
                ':product_id' => $id,
                ':quantity' => $qty,
                ':low_stock_threshold' => $lowStock
            ]);

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

            $db->commit();
            return $this->repository->findById($id);
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
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
        $db = \App\Core\Database::connection();
        $db->beginTransaction();

        try {
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
                // Update inventory record
                if (isset($data['quantity']) || isset($data['low_stock_threshold'])) {
                    $qty = isset($data['quantity']) ? (int)$data['quantity'] : null;
                    $lowStock = isset($data['low_stock_threshold']) ? (int)$data['low_stock_threshold'] : null;

                    $stmt = $db->prepare("SELECT id FROM inventory WHERE product_id = :product_id LIMIT 1");
                    $stmt->execute([':product_id' => $id]);
                    $invId = $stmt->fetchColumn();

                    if ($invId) {
                        $updFields = [];
                        $updParams = [':product_id' => $id];
                        if ($qty !== null) {
                            $updFields[] = "quantity = :quantity";
                            $updParams[':quantity'] = $qty < 0 ? 0 : $qty;
                        }
                        if ($lowStock !== null) {
                            $updFields[] = "low_stock_threshold = :low_stock_threshold";
                            $updParams[':low_stock_threshold'] = $lowStock < 0 ? 0 : $lowStock;
                        }

                        if (!empty($updFields)) {
                            $db->prepare("UPDATE inventory SET " . implode(", ", $updFields) . " WHERE product_id = :product_id")->execute($updParams);
                        }
                    } else {
                        $insertStmt = $db->prepare("INSERT INTO inventory (product_id, quantity, low_stock_threshold, warehouse) VALUES (:product_id, :quantity, :low_stock_threshold, 'Main Warehouse')");
                        $insertStmt->execute([
                            ':product_id' => $id,
                            ':quantity' => $qty !== null && $qty >= 0 ? $qty : 100,
                            ':low_stock_threshold' => $lowStock !== null && $lowStock >= 0 ? $lowStock : 10
                        ]);
                    }
                }

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

            $db->commit();
            return $updated;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Delete Product
     */
    public function delete(int $id): bool
    {
        // Check if the product has ever been ordered
        $db = \App\Core\Database::connection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM order_items WHERE product_id = :product_id");
        $stmt->execute([':product_id' => $id]);
        $hasOrders = ((int)$stmt->fetchColumn()) > 0;

        if ($hasOrders) {
            // Soft delete: set status to ARCHIVED so order history references stay valid
            $updateStmt = $db->prepare("UPDATE products SET status = 'ARCHIVED' WHERE id = :id");
            return $updateStmt->execute([':id' => $id]);
        }

        // Hard delete (for products that haven't been ordered): delete images first
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