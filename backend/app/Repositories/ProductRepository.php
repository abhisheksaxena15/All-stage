<?php

namespace App\Repositories;

use App\Models\Product;
use PDO;

class ProductRepository extends BaseRepository
{
    protected string $table = 'products';
    protected string $model = Product::class;

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
            ':featured' => $product->isFeatured() ? 1 : 0,
            ':new_arrival' => $product->isNewArrival() ? 1 : 0,
            ':best_seller' => $product->isBestSeller() ? 1 : 0,
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
            ':id' => $product->getId(),
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
            ':featured' => $product->isFeatured() ? 1 : 0,
            ':new_arrival' => $product->isNewArrival() ? 1 : 0,
            ':best_seller' => $product->isBestSeller() ? 1 : 0
        ]);
    }

    public function findById(int $id): ?Product
    {
        $stmt = $this->db->prepare("
            SELECT p.*, b.name as brand_name, c.name as category_name,
                   COALESCE(i.quantity, 0) as quantity, COALESCE(i.low_stock_threshold, 10) as low_stock_threshold
            FROM products p
            LEFT JOIN brands b ON p.brand_id = b.id
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN inventory i ON i.product_id = p.id
            WHERE p.id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        $product = new Product($row);

        // Load images
        $imgStmt = $this->db->prepare("
            SELECT * FROM product_images
            WHERE product_id = :product_id
            ORDER BY sort_order ASC, id ASC
        ");
        $imgStmt->execute([':product_id' => $id]);
        $imgRows = $imgStmt->fetchAll(PDO::FETCH_ASSOC);

        $images = [];
        $primaryUrl = null;
        foreach ($imgRows as $imgRow) {
            $img = new \App\Models\ProductImage($imgRow);
            $images[] = $img;
            if ($img->isPrimary()) {
                $primaryUrl = $img->getImagePath();
            }
        }

        // Fallback for primaryImageUrl if none is explicitly marked primary
        if (!$primaryUrl && !empty($images)) {
            $primaryUrl = $images[0]->getImagePath();
        }

        // Generate full URL
        if ($primaryUrl && !str_starts_with($primaryUrl, 'http')) {
            $appUrl = $_ENV['APP_URL'] ?? 'http://localhost/all-stage/All-stage/backend/public';
            $primaryUrl = rtrim($appUrl, '/') . '/' . ltrim($primaryUrl, '/');
        }

        $product->setImages($images);
        $product->setPrimaryImageUrl($primaryUrl);

        return $product;
    }

    public function findAll(array $filters = []): array
    {
        $sql = "
            SELECT p.*, b.name as brand_name, c.name as category_name,
                   COALESCE(i.quantity, 0) as quantity, COALESCE(i.low_stock_threshold, 10) as low_stock_threshold
            FROM products p
            LEFT JOIN brands b ON p.brand_id = b.id
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN inventory i ON i.product_id = p.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (p.name LIKE :search OR p.sku LIKE :search OR p.description LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['status'])) {
            $sql .= " AND p.status = :status";
            $params[':status'] = strtoupper($filters['status']);
        } else {
            $sql .= " AND p.status != 'ARCHIVED'";
        }

        if (!empty($filters['category_id'])) {
            $sql .= " AND p.category_id = :category_id";
            $params[':category_id'] = (int)$filters['category_id'];
        }

        if (!empty($filters['brand_id'])) {
            $sql .= " AND p.brand_id = :brand_id";
            $params[':brand_id'] = (int)$filters['brand_id'];
        }

        // Sorting
        $sortField = 'p.created_at';
        $sortDir = 'DESC';
        if (!empty($filters['sort'])) {
            $sort = $filters['sort'];
            if (str_starts_with($sort, '-')) {
                $sortDir = 'DESC';
                $field = substr($sort, 1);
            } else {
                $sortDir = 'ASC';
                $field = $sort;
            }

            // Map frontend sort keys to database columns
            $allowedSorts = [
                'name' => 'p.name',
                'sku' => 'p.sku',
                'selling_price' => 'p.selling_price',
                'cost_price' => 'p.cost_price',
                'status' => 'p.status',
                'created_at' => 'p.created_at',
            ];
            if (isset($allowedSorts[$field])) {
                $sortField = $allowedSorts[$field];
            }
        }
        $sql .= " ORDER BY {$sortField} {$sortDir}";

        // Pagination
        if (isset($filters['limit']) && isset($filters['offset'])) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $this->db->prepare($sql);

        // Bind parameters
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        if (isset($filters['limit']) && isset($filters['offset'])) {
            $stmt->bindValue(':limit', (int)$filters['limit'], PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$filters['offset'], PDO::PARAM_INT);
        }

        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $products = [];
        foreach ($rows as $row) {
            $product = new Product($row);

            // Fetch primary image (or first image) for listing
            $imgStmt = $this->db->prepare("
                SELECT image_path, is_primary FROM product_images
                WHERE product_id = :product_id
                ORDER BY is_primary DESC, sort_order ASC, id ASC
                LIMIT 1
            ");
            $imgStmt->execute([':product_id' => $product->getId()]);
            $imgRow = $imgStmt->fetch(PDO::FETCH_ASSOC);
            if ($imgRow) {
                $path = $imgRow['image_path'];
                if (!str_starts_with($path, 'http')) {
                    $appUrl = $_ENV['APP_URL'] ?? 'http://localhost/all-stage/All-stage/backend/public';
                    $path = rtrim($appUrl, '/') . '/' . ltrim($path, '/');
                }
                $product->setPrimaryImageUrl($path);
            }

            $products[] = $product;
        }

        return $products;
    }

    public function countFiltered(array $filters = []): int
    {
        $sql = "
            SELECT COUNT(*)
            FROM products p
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (p.name LIKE :search OR p.sku LIKE :search OR p.description LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['status'])) {
            $sql .= " AND p.status = :status";
            $params[':status'] = strtoupper($filters['status']);
        } else {
            $sql .= " AND p.status != 'ARCHIVED'";
        }

        if (!empty($filters['category_id'])) {
            $sql .= " AND p.category_id = :category_id";
            $params[':category_id'] = (int)$filters['category_id'];
        }

        if (!empty($filters['brand_id'])) {
            $sql .= " AND p.brand_id = :brand_id";
            $params[':brand_id'] = (int)$filters['brand_id'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function findBySlug(string $slug): ?Product
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM products WHERE slug=:slug LIMIT 1"
        );
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new Product($row) : null;
    }

    public function findBySku(string $sku): ?Product
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM products WHERE sku=:sku LIMIT 1"
        );
        $stmt->execute([':sku' => $sku]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new Product($row) : null;
    }
}