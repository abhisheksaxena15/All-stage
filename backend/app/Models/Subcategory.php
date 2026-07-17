<?php

namespace App\Models;

class Subcategory extends BaseModel
{
    private ?int $id = null;

    private int $categoryId;

    private string $name = '';

    private string $slug = '';

    private ?string $description = null;

    private ?string $image = null;

    private int $sortOrder = 0;

    private string $status = 'ACTIVE';
    private ?string $categoryName = null;
    private ?int $productsCount = null;

    public function __construct(array $data = [])
    {
        $this->fill($data);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->categoryId,
            'category_name' => $this->categoryName,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image' => $this->image,
            'sort_order' => $this->sortOrder,
            'status' => strtolower($this->status),
            'products_count' => $this->productsCount ?? 0
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setCategoryId(int $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    public function setName(string $name): void
    {
        $this->name = trim($name);
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function setImage(?string $image): void
    {
        $this->image = $image;
    }

    public function setSortOrder(int $sortOrder): void
    {
        $this->sortOrder = $sortOrder;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }
}