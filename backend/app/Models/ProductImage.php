<?php

namespace App\Models;

class ProductImage extends BaseModel
{
    private ?int $id = null;

    private int $productId = 0;

    private string $imagePath = '';

    private ?string $altText = null;

    private bool $isPrimary = false;

    private int $sortOrder = 0;

    public function __construct(array $data = [])
    {
        $this->fill($data);
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function setProductId(int $id): void
    {
        $this->productId = $id;
    }

    public function getImagePath(): string
    {
        return $this->imagePath;
    }

    public function setImagePath(string $path): void
    {
        $this->imagePath = $path;
    }

    public function getAltText(): ?string
    {
        return $this->altText;
    }

    public function setAltText(?string $text): void
    {
        $this->altText = $text;
    }

    public function isPrimary(): bool
    {
        return $this->isPrimary;
    }

    public function setPrimary(bool $primary): void
    {
        $this->isPrimary = $primary;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $order): void
    {
        $this->sortOrder = $order;
    }
}