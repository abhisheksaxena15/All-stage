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
}