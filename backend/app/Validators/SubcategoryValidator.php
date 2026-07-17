<?php

namespace App\Validators;

class SubcategoryValidator extends BaseValidator
{
    public function validate(array $data): array
    {
        $this->required('name', $data['name'] ?? '');
        $this->required('category_id', $data['category_id'] ?? '');

        if (isset($data['description'])) {
            $this->maxLength('description', $data['description'], 1000);
        }

        return [
            'valid' => $this->passes(),
            'errors' => $this->errors()
        ];
    }
}
