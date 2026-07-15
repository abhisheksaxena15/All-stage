CREATE TABLE products (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    brand_id INT UNSIGNED NOT NULL,

    category_id INT UNSIGNED NOT NULL,

    subcategory_id INT UNSIGNED NULL,

    name VARCHAR(255) NOT NULL,

    slug VARCHAR(255) NOT NULL UNIQUE,

    sku VARCHAR(100) NOT NULL UNIQUE,

    short_description TEXT NULL,

    description LONGTEXT NULL,

    status ENUM(
        'DRAFT',
        'ACTIVE',
        'ARCHIVED'
    ) DEFAULT 'DRAFT',

    featured TINYINT(1) DEFAULT 0,

    new_arrival TINYINT(1) DEFAULT 0,

    best_seller TINYINT(1) DEFAULT 0,

    sort_order INT DEFAULT 0,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_product_brand
        FOREIGN KEY (brand_id)
        REFERENCES brands(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_product_category
        FOREIGN KEY (category_id)
        REFERENCES categories(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_product_subcategory
        FOREIGN KEY (subcategory_id)
        REFERENCES subcategories(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
);

CREATE INDEX idx_product_brand ON products(brand_id);
CREATE INDEX idx_product_category ON products(category_id);
CREATE INDEX idx_product_subcategory ON products(subcategory_id);
CREATE INDEX idx_product_slug ON products(slug);
CREATE INDEX idx_product_sku ON products(sku);
CREATE INDEX idx_product_status ON products(status);