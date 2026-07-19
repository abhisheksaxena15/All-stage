<?php

namespace App\Services;

use App\Helpers\UploadHelper;
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

        // Handle logo upload if files are sent
        $logoPath = null;
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $logoPath = UploadHelper::uploadBrandLogo($_FILES['logo']);
        }

        $brand->setName($data['name']);
        $brand->setSlug($data['slug']);
        $brand->setDescription($data['description'] ?? null);
        $brand->setLogo($logoPath);
        $brand->setWebsite($data['website'] ?? null);
        $brand->setSortOrder((int)($data['sort_order'] ?? 0));
        $brand->setStatus($data['status'] ?? 'ACTIVE');

        $id = $this->repository->create($brand);

        return $this->repository->findById($id);
    }

    public function update(int $id, array $data): Brand
    {
        $brand = $this->repository->findById($id);
        if (!$brand instanceof Brand) {
            throw new Exception("Brand not found.");
        }

        if (!empty($data['slug']) && $data['slug'] !== $brand->getSlug()) {
            if ($this->repository->findBySlug($data['slug'])) {
                throw new Exception("Brand slug already exists.");
            }
            $brand->setSlug($data['slug']);
        }

        if (isset($data['name'])) $brand->setName($data['name']);
        if (isset($data['description'])) $brand->setDescription($data['description']);
        if (isset($data['website'])) $brand->setWebsite($data['website']);
        if (isset($data['sort_order'])) $brand->setSortOrder((int)$data['sort_order']);
        if (isset($data['status'])) $brand->setStatus($data['status']);

        // Handle removing logo
        $logoPath = $brand->getLogo();
        if (!empty($data['remove_logo'])) {
            if ($logoPath) {
                $path = __DIR__ . '/../../public/' . ltrim($logoPath, '/');
                if (file_exists($path)) {
                    @unlink($path);
                }
                $logoPath = null;
            }
        }

        // Handle updating logo file
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            if ($logoPath) {
                $path = __DIR__ . '/../../public/' . ltrim($logoPath, '/');
                if (file_exists($path)) {
                    @unlink($path);
                }
            }
            $logoPath = UploadHelper::uploadBrandLogo($_FILES['logo']);
        }

        $brand->setLogo($logoPath);

        $this->repository->update($brand);
        return $this->repository->findById($id);
    }

    public function delete(int $id): bool
    {
        // Delete logo file if exists before deleting brand record
        $brand = $this->repository->findById($id);
        if ($brand && $brand->getLogo()) {
            $path = __DIR__ . '/../../public/' . ltrim($brand->getLogo(), '/');
            if (file_exists($path)) {
                @unlink($path);
            }
        }
        return $this->repository->delete($id);
    }
}