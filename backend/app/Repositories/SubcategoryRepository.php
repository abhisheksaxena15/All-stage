<?php

namespace App\Repositories;

use App\Models\Subcategory;
use PDO;

class SubcategoryRepository extends BaseRepository
{
    protected string $table = 'subcategories';

    protected string $model = Subcategory::class;

    public function create(Subcategory $subcategory): int
    {
        $sql = "INSERT INTO subcategories
        (
            category_id,
            name,
            slug,
            description,
            image,
            sort_order,
            status
        )
        VALUES
        (
            :category_id,
            :name,
            :slug,
            :description,
            :image,
            :sort_order,
            :status
        )";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([

            ':category_id'=>$subcategory->getCategoryId(),

            ':name'=>$subcategory->getName(),

            ':slug'=>$subcategory->getSlug(),

            ':description'=>$subcategory->getDescription(),

            ':image'=>$subcategory->getImage(),

            ':sort_order'=>$subcategory->getSortOrder(),

            ':status'=>$subcategory->getStatus()

        ]);

        return (int)$this->db->lastInsertId();
    }

    public function findBySlug(string $slug): ?Subcategory
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM subcategories WHERE slug=:slug LIMIT 1"
        );

        $stmt->execute([
            ':slug'=>$slug
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new Subcategory($row) : null;
    }

    public function findByCategoryId(int $categoryId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM subcategories WHERE category_id=:category_id"
        );
        $stmt->execute([':category_id' => $categoryId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function ($row) {
            return new Subcategory($row);
        }, $rows);
    }

    public function findAll(): array
    {
        $sql = "
            SELECT s.*, c.name as category_name,
                   (SELECT COUNT(*) FROM products p WHERE p.subcategory_id = s.id) as products_count
            FROM subcategories s
            LEFT JOIN categories c ON c.id = s.category_id
            ORDER BY s.sort_order ASC, s.id DESC
        ";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function ($row) {
            return new Subcategory($row);
        }, $rows);
    }
}