<?php

namespace App\Services;

use App\Helpers\SlugHelper;
use App\Models\Subcategory;
use App\Repositories\SubcategoryRepository;
use Exception;

class SubcategoryService
{
    private SubcategoryRepository $repository;

    public function __construct()
    {
        $this->repository = new SubcategoryRepository();
    }

    public function create(array $data): Subcategory
    {
        $slug = SlugHelper::generate($data['name']);

        if ($this->repository->findBySlug($slug)) {
            throw new Exception("Subcategory already exists.");
        }

        $subcategory = new Subcategory();

        $subcategory->setCategoryId((int)$data['category_id']);
        $subcategory->setName($data['name']);
        $subcategory->setSlug($slug);
        $subcategory->setDescription($data['description'] ?? null);
        $subcategory->setImage($data['image'] ?? null);
        $subcategory->setSortOrder((int)($data['sort_order'] ?? 0));
        $subcategory->setStatus($data['status'] ?? 'ACTIVE');

        $id = $this->repository->create($subcategory);

        return $this->repository->findById($id);
    }

    public function getAll(): array
    {
        return $this->repository->findAll();
    }
}