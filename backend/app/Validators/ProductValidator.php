<?php

namespace App\Validators;

class ProductValidator extends BaseValidator
{
    public function validate(array $data): array
{
    $this->required('name', $data['name'] ?? '');

    $this->required('slug', $data['slug'] ?? '');

    $this->required('sku', $data['sku'] ?? '');

    $this->required('brand_id', $data['brand_id'] ?? '');

    $this->required('category_id', $data['category_id'] ?? '');

    $this->required(
        'selling_price',
        $data['selling_price'] ?? ''
    );

    return [

        'valid' => $this->passes(),

        'errors' => $this->errors()

    ];
}
}