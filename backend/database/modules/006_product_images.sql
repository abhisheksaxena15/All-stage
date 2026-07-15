CREATE TABLE product_images (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    product_id INT UNSIGNED NOT NULL,

    image_path VARCHAR(255) NOT NULL,

    alt_text VARCHAR(255) NULL,

    is_primary TINYINT(1) DEFAULT 0,

    sort_order INT DEFAULT 0,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_product_images_product
        FOREIGN KEY(product_id)
        REFERENCES products(id)
        ON DELETE CASCADE

);

CREATE INDEX idx_product_images_product
ON product_images(product_id);