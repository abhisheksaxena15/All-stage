<?php

namespace App\Repositories;

use App\Models\Category;
use PDO;

class CategoryRepository extends BaseRepository
{
    protected string $table = 'categories';
    protected string $model = Category::class;

    public function findAll(): array
    {
        $sql = "
            SELECT c.*, 
                   (SELECT COUNT(*) FROM subcategories s WHERE s.category_id = c.id) as subcategories_count,
                   (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) as products_count
            FROM categories c
            ORDER BY c.sort_order ASC, c.id DESC
        ";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function ($row) {
            return new Category($row);
        }, $rows);
    }
    public function create(Category $category): int
    {
        $sql = "INSERT INTO categories
        (
            name,
            slug,
            description,
            image,
            banner,
            sort_order,
            status
        )
        VALUES
        (
            :name,
            :slug,
            :description,
            :image,
            :banner,
            :sort_order,
            :status
        )";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([

            ':name'=>$category->getName(),

            ':slug'=>$category->getSlug(),

            ':description'=>$category->getDescription(),

            ':image'=>$category->getImage(),

            ':banner'=>$category->getBanner(),

            ':sort_order'=>$category->getSortOrder(),

            ':status'=>$category->getStatus()

        ]);

        return (int)$this->db->lastInsertId();
    }

    public function findBySlug(string $slug): ?Category
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM categories WHERE slug=:slug LIMIT 1"
        );

        $stmt->execute([
            ':slug'=>$slug
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new Category($row) : null;
    }
}