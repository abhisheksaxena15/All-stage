<?php

namespace App\Helpers;

class UploadHelper
{
    public static function uploadProductImage(array $file): string
    {
        $directory = __DIR__ . '/../../public/uploads/products/';

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $extension = pathinfo(
            $file['name'],
            PATHINFO_EXTENSION
        );

        $filename = uniqid('product_') . '.' . $extension;

        move_uploaded_file(
            $file['tmp_name'],
            $directory . $filename
        );

        return 'uploads/products/' . $filename;
    }

    public static function uploadBrandLogo(array $file): string
    {
        $directory = __DIR__ . '/../../public/uploads/brands/';

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('brand_') . '.' . $extension;

        move_uploaded_file($file['tmp_name'], $directory . $filename);

        return 'uploads/brands/' . $filename;
    }

    public static function uploadCategoryImage(array $file): string
    {
        $directory = __DIR__ . '/../../public/uploads/categories/';

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('category_') . '.' . $extension;

        move_uploaded_file($file['tmp_name'], $directory . $filename);

        return 'uploads/categories/' . $filename;
    }
}