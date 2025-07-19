CREATE TABLE design_ideas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL COMMENT 'Name of the design idea',
    description TEXT NOT NULL COMMENT 'Description with HTML formatting allowed',
    main_image VARCHAR(255) NOT NULL COMMENT 'Main/featured image filename',
    work_images TEXT COMMENT 'JSON array of work image filenames',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
