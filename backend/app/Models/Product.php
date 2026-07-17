<?php

namespace App\Models;

class Product extends BaseModel
{
    private ?int $id = null;

    private int $brandId = 0;

    private int $categoryId = 0;

    private ?int $subcategoryId = null;

    private string $name = '';

    private string $slug = '';

    private string $sku = '';

    private ?string $shortDescription = null;

    private ?string $description = null;

    private float $sellingPrice = 0;

    private float $comparePrice = 0;

    private float $costPrice = 0;

    private string $status = 'DRAFT';

    private bool $featured = false;

    private bool $newArrival = false;

    private bool $bestSeller = false;

    private ?string $brandName = null;

    private ?string $categoryName = null;

    private array $images = [];

    private ?string $primaryImageUrl = null;

    public function __construct(array $data = [])
{
    if (!empty($data)) {

        if (isset($data['id'])) {
            $this->id = (int)$data['id'];
        }

        if (isset($data['brand_id'])) {
            $this->brandId = (int)$data['brand_id'];
        }

        if (isset($data['category_id'])) {
            $this->categoryId = (int)$data['category_id'];
        }

        $this->subcategoryId = isset($data['subcategory_id'])
            ? (int)$data['subcategory_id']
            : null;

        $this->name = $data['name'] ?? '';

        $this->slug = $data['slug'] ?? '';

        $this->sku = $data['sku'] ?? '';

        $this->shortDescription = $data['short_description'] ?? null;

        $this->description = $data['description'] ?? null;

        $this->sellingPrice = (float)($data['selling_price'] ?? 0);

        $this->comparePrice = (float)($data['compare_price'] ?? 0);

        $this->costPrice = (float)($data['cost_price'] ?? 0);

        $this->status = $data['status'] ?? 'DRAFT';

        $this->featured = (bool)($data['featured'] ?? false);

        $this->newArrival = (bool)($data['new_arrival'] ?? false);

        $this->bestSeller = (bool)($data['best_seller'] ?? false);

        if (isset($data['brand_name'])) {
            $this->brandName = $data['brand_name'];
        }

        if (isset($data['category_name'])) {
            $this->categoryName = $data['category_name'];
        }

        if (isset($data['primary_image_url'])) {
            $this->primaryImageUrl = $data['primary_image_url'];
        }
    }
}

    public function getId(): ?int { return $this->id; }
    public function setId(int $id): void
{
    $this->id = $id;
}
    public function getBrandId(): int { return $this->brandId; }
    public function setBrandId(int $id): void { $this->brandId = $id; }

    public function getCategoryId(): int { return $this->categoryId; }
    public function setCategoryId(int $id): void { $this->categoryId = $id; }

    public function getSubcategoryId(): ?int { return $this->subcategoryId; }
    public function setSubcategoryId(?int $id): void { $this->subcategoryId = $id; }

    public function getName(): string { return $this->name; }
    public function setName(string $value): void { $this->name = trim($value); }

    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $value): void { $this->slug = $value; }

    public function getSku(): string { return $this->sku; }
    public function setSku(string $value): void { $this->sku = $value; }

    public function getShortDescription(): ?string { return $this->shortDescription; }
    public function setShortDescription(?string $value): void { $this->shortDescription = $value; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $value): void { $this->description = $value; }

    public function getSellingPrice(): float { return $this->sellingPrice; }
    public function setSellingPrice(float $value): void { $this->sellingPrice = $value; }

    public function getComparePrice(): float { return $this->comparePrice; }
    public function setComparePrice(float $value): void { $this->comparePrice = $value; }

    public function getCostPrice(): float { return $this->costPrice; }
    public function setCostPrice(float $value): void { $this->costPrice = $value; }

    public function getBrandName(): ?string { return $this->brandName; }
    public function setBrandName(?string $value): void { $this->brandName = $value; }

    public function getCategoryName(): ?string { return $this->categoryName; }
    public function setCategoryName(?string $value): void { $this->categoryName = $value; }

    public function getImages(): array { return $this->images; }
    public function setImages(array $value): void { $this->images = $value; }

    public function getPrimaryImageUrl(): ?string { return $this->primaryImageUrl; }
    public function setPrimaryImageUrl(?string $value): void { $this->primaryImageUrl = $value; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $value): void { $this->status = $value; }

    public function isFeatured(): bool { return $this->featured; }
    public function setFeatured(bool $value): void { $this->featured = $value; }

    public function isNewArrival(): bool { return $this->newArrival; }
    public function setNewArrival(bool $value): void { $this->newArrival = $value; }

    public function isBestSeller(): bool { return $this->bestSeller; }
    public function setBestSeller(bool $value): void { $this->bestSeller = $value; }

    public function toArray(): array
{
    return [

        'id' => $this->id,

        'brand_id' => $this->brandId,

        'category_id' => $this->categoryId,

        'subcategory_id' => $this->subcategoryId,

        'name' => $this->name,

        'slug' => $this->slug,

        'sku' => $this->sku,

        'short_description' => $this->shortDescription,

        'description' => $this->description,

        'selling_price' => $this->sellingPrice,

        'compare_price' => $this->comparePrice,

        'cost_price' => $this->costPrice,

        'status' => $this->status,

        'featured' => $this->featured,

        'new_arrival' => $this->newArrival,

        'best_seller' => $this->bestSeller,

        'brand_name' => $this->brandName,

        'category_name' => $this->categoryName,

        'images' => array_map(fn($img) => $img->toArray(), $this->images),

        'primary_image_url' => $this->primaryImageUrl

    ];
}
}