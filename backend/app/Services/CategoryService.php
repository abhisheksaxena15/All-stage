<?php

namespace App\Services;

use App\Helpers\UploadHelper;
use App\Helpers\SlugHelper;
use App\Models\Category;
use App\Repositories\CategoryRepository;
use Exception;

class CategoryService
{
    private CategoryRepository $repository;

    public function __construct()
    {
        $this->repository = new CategoryRepository();
    }

    /**
     * Create Category
     */
    public function create(array $data): Category
    {
        $slug = SlugHelper::generate($data['name']);

        if ($this->repository->findBySlug($slug)) {
            throw new Exception("Category already exists.");
        }

        $category = new Category();

        // Handle image upload if files are sent
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imagePath = UploadHelper::uploadCategoryImage($_FILES['image']);
        }

        $category->setName($data['name']);
        $category->setSlug($slug);
        $category->setDescription($data['description'] ?? null);
        $category->setImage($imagePath);
        $category->setBanner($data['banner'] ?? null);
        $category->setSortOrder((int)($data['sort_order'] ?? 0));
        $category->setStatus($data['status'] ?? 'ACTIVE');

        $id = $this->repository->create($category);

        $created = $this->repository->findById($id);

        if (!$created instanceof Category) {
            throw new Exception("Failed to load newly created category.");
        }

        return $created;
    }

    /**
     * Get All Categories
     */
    public function getAll(): array
    {
        return $this->repository->findAll();
    }

    /**
     * Get Category By ID
     */
    public function get(int $id): ?Category
    {
        $category = $this->repository->findById($id);

        return $category instanceof Category ? $category : null;
    }

    /**
     * Update Category
     */
    public function update(int $id, array $data): Category
    {
        $category = $this->repository->findById($id);
        if (!$category instanceof Category) {
            throw new Exception("Category not found.");
        }

        if (isset($data['name'])) {
            $category->setName($data['name']);
            $slug = SlugHelper::generate($data['name']);
            if ($slug !== $category->getSlug()) {
                if ($this->repository->findBySlug($slug)) {
                    throw new Exception("Category with this name already exists.");
                }
                $category->setSlug($slug);
            }
        }

        if (isset($data['description'])) $category->setDescription($data['description']);
        if (isset($data['banner'])) $category->setBanner($data['banner']);
        if (isset($data['sort_order'])) $category->setSortOrder((int)$data['sort_order']);
        if (isset($data['status'])) $category->setStatus($data['status']);

        // Handle removing image
        $imagePath = $category->getImage();
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

        $category->setImage($imagePath);

        $this->repository->update($category);
        return $this->repository->findById($id);
    }

    /**
     * Delete Category
     */
    public function delete(int $id): bool
    {
        $category = $this->repository->findById($id);
        if ($category && $category->getImage()) {
            $path = __DIR__ . '/../../public/' . ltrim($category->getImage(), '/');
            if (file_exists($path)) {
                @unlink($path);
            }
        }
        return $this->repository->delete($id);
    }
}