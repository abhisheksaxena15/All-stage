<?php

namespace App\Services;

use App\Helpers\UploadHelper;
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

        // Handle image upload if files are sent
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imagePath = UploadHelper::uploadCategoryImage($_FILES['image']);
        }

        $subcategory->setCategoryId((int)$data['category_id']);
        $subcategory->setName($data['name']);
        $subcategory->setSlug($slug);
        $subcategory->setDescription($data['description'] ?? null);
        $subcategory->setImage($imagePath);
        $subcategory->setSortOrder((int)($data['sort_order'] ?? 0));
        $subcategory->setStatus($data['status'] ?? 'ACTIVE');

        $id = $this->repository->create($subcategory);

        return $this->repository->findById($id);
    }

    public function update(int $id, array $data): Subcategory
    {
        $subcategory = $this->repository->findById($id);
        if (!$subcategory instanceof Subcategory) {
            throw new Exception("Subcategory not found.");
        }

        if (isset($data['category_id'])) $subcategory->setCategoryId((int)$data['category_id']);
        
        if (isset($data['name'])) {
            $subcategory->setName($data['name']);
            $slug = SlugHelper::generate($data['name']);
            if ($slug !== $subcategory->getSlug()) {
                if ($this->repository->findBySlug($slug)) {
                    throw new Exception("Subcategory with this name already exists.");
                }
                $subcategory->setSlug($slug);
            }
        }

        if (isset($data['description'])) $subcategory->setDescription($data['description']);
        if (isset($data['sort_order'])) $subcategory->setSortOrder((int)$data['sort_order']);
        if (isset($data['status'])) $subcategory->setStatus($data['status']);

        // Handle removing image
        $imagePath = $subcategory->getImage();
        if (!empty($data['remove_image'])) {
            if ($imagePath) {
                $path = __DIR__ . '/../../public/' . ltrim($imagePath, '/');
                if (file_exists($path)) {
                    @unlink($path);
                }
                $imagePath = null;
            }
        }

        // Handle updating image file
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            if ($imagePath) {
                $path = __DIR__ . '/../../public/' . ltrim($imagePath, '/');
                if (file_exists($path)) {
                    @unlink($path);
                }
            }
            $imagePath = UploadHelper::uploadCategoryImage($_FILES['image']);
        }

        $subcategory->setImage($imagePath);

        $this->repository->update($subcategory);
        return $this->repository->findById($id);
    }

    public function delete(int $id): bool
    {
        $subcategory = $this->repository->findById($id);
        if ($subcategory && $subcategory->getImage()) {
            $path = __DIR__ . '/../../public/' . ltrim($subcategory->getImage(), '/');
            if (file_exists($path)) {
                @unlink($path);
            }
        }
        return $this->repository->delete($id);
    }

    public function getAll(?int $categoryId = null): array
    {
        if ($categoryId !== null) {
            return $this->repository->findByCategoryId($categoryId);
        }
        return $this->repository->findAll();
    }
}