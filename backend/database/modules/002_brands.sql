CREATE TABLE brands (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(150) NOT NULL,

    slug VARCHAR(150) NOT NULL UNIQUE,

    description TEXT NULL,

    logo VARCHAR(255) NULL,

    website VARCHAR(255) NULL,

    sort_order INT DEFAULT 0,

    status ENUM(
        'ACTIVE',
        'INACTIVE'
    ) DEFAULT 'ACTIVE',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);

CREATE INDEX idx_brand_slug
ON brands(slug);

CREATE INDEX idx_brand_status
ON brands(status);