<?php

namespace App\Services;

use App\Models\Brand;
use App\Repositories\BrandRepository;
use Exception;

class BrandService
{
    private BrandRepository $repository;

    public function __construct()
    {
        $this->repository = new BrandRepository();
    }

    public function getAll(): array
    {
        return $this->repository->findAll();
    }

    public function create(array $data): Brand
    {
        if ($this->repository->findBySlug($data['slug'])) {
            throw new Exception("Brand slug already exists.");
        }

        $brand = new Brand();

        $brand->setName($data['name']);
        $brand->setSlug($data['slug']);
        $brand->setDescription($data['description'] ?? null);
        $brand->setLogo($data['logo'] ?? null);
        $brand->setWebsite($data['website'] ?? null);
        $brand->setSortOrder((int)($data['sort_order'] ?? 0));
        $brand->setStatus($data['status'] ?? 'ACTIVE');

        $id = $this->repository->create($brand);

        return $this->repository->findById($id);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}