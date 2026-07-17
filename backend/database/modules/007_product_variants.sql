CREATE TABLE product_variants (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    product_id INT UNSIGNED NOT NULL,

    sku VARCHAR(100) NOT NULL UNIQUE,

    color VARCHAR(100) NULL,

    size VARCHAR(100) NULL,

    selling_price DECIMAL(10,2) NOT NULL DEFAULT 0,

    compare_price DECIMAL(10,2) DEFAULT 0,

    cost_price DECIMAL(10,2) DEFAULT 0,

    weight DECIMAL(10,2) DEFAULT 0,

    status ENUM(
        'ACTIVE',
        'INACTIVE'
    ) DEFAULT 'ACTIVE',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_variant_product
        FOREIGN KEY (product_id)
        REFERENCES products(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE INDEX idx_variant_product
ON product_variants(product_id);

CREATE INDEX idx_variant_sku
ON product_variants(sku);