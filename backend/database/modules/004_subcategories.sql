CREATE TABLE subcategories (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    category_id INT UNSIGNED NOT NULL,

    name VARCHAR(120) NOT NULL,

    slug VARCHAR(150) NOT NULL UNIQUE,

    description TEXT NULL,

    image VARCHAR(255) NULL,

    sort_order INT DEFAULT 0,

    status ENUM(
        'ACTIVE',
        'INACTIVE'
    ) DEFAULT 'ACTIVE',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_subcategory_category
    FOREIGN KEY(category_id)
    REFERENCES categories(id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE

);

CREATE INDEX idx_subcategory_category
ON subcategories(category_id);

CREATE INDEX idx_subcategory_slug
ON subcategories(slug);

CREATE INDEX idx_subcategory_status
ON subcategories(status);