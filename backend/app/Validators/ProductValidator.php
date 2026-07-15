<?php

namespace App\Validators;

class ProductValidator extends BaseValidator
{
    public function validate(array $data): array
    {
        $this->required('name', $data['name'] ?? '');

        $this->required('brand_id', $data['brand_id'] ?? '');

        $this->required('category_id', $data['category_id'] ?? '');

        $this->required('selling_price', $data['selling_price'] ?? '');

        $this->required('sku', $data['sku'] ?? '');

        return [
            'valid' => $this->passes(),
            'errors' => $this->errors()
        ];
    }
}