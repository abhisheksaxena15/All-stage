<?php

namespace App\Repositories;

use App\Models\Brand;
use PDO;

class BrandRepository extends BaseRepository
{
    protected string $table = 'brands';
    protected string $model = Brand::class;

    public function create(Brand $brand): int
    {
        $sql = "INSERT INTO brands
        (
            name,
            slug,
            description,
            logo,
            website,
            sort_order,
            status
        )
        VALUES
        (
            :name,
            :slug,
            :description,
            :logo,
            :website,
            :sort_order,
            :status
        )";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([

            ':name'=>$brand->getName(),

            ':slug'=>$brand->getSlug(),

            ':description'=>$brand->getDescription(),

            ':logo'=>$brand->getLogo(),

            ':website'=>$brand->getWebsite(),

            ':sort_order'=>$brand->getSortOrder(),

            ':status'=>$brand->getStatus()

        ]);

        return (int)$this->db->lastInsertId();
    }

    public function findBySlug(string $slug): ?Brand
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM brands WHERE slug=:slug LIMIT 1"
        );

        $stmt->execute([
            ':slug'=>$slug
        ]);

        $row=$stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new Brand($row) : null;
    }

    public function findAll(): array
    {
        $sql = "
            SELECT b.*, 
                   (SELECT COUNT(*) FROM products p WHERE p.brand_id = b.id) as products_count
            FROM brands b
            ORDER BY b.sort_order ASC, b.id DESC
        ";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function ($row) {
            return new Brand($row);
        }, $rows);
    }
}