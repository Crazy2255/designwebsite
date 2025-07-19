-- Create the main products table
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `category_id` int(11) NOT NULL,
  `subcategory_id` int(11) DEFAULT NULL,
  `description` text,
  `sku` varchar(50) NOT NULL UNIQUE,
  `price` decimal(10,2) DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `length` decimal(10,2) DEFAULT NULL COMMENT 'in cm',
  `width` decimal(10,2) DEFAULT NULL COMMENT 'in cm',
  `height` decimal(10,2) DEFAULT NULL COMMENT 'in cm',
  `weight` decimal(10,2) DEFAULT NULL COMMENT 'in kg',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `subcategory_id` (`subcategory_id`),
  CONSTRAINT `products_category_fk` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `products_subcategory_fk` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create product images table
CREATE TABLE IF NOT EXISTS `product_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_images_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create product colors relationship table
CREATE TABLE IF NOT EXISTS `product_colors` (
  `product_id` int(11) NOT NULL,
  `color_id` int(11) NOT NULL,
  PRIMARY KEY (`product_id`, `color_id`),
  KEY `color_id` (`color_id`),
  CONSTRAINT `product_colors_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `product_colors_color_fk` FOREIGN KEY (`color_id`) REFERENCES `colors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create product materials relationship table
CREATE TABLE IF NOT EXISTS `product_materials` (
  `product_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  PRIMARY KEY (`product_id`, `material_id`),
  KEY `material_id` (`material_id`),
  CONSTRAINT `product_materials_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `product_materials_material_fk` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create product marbles relationship table
CREATE TABLE IF NOT EXISTS `product_marbles` (
  `product_id` int(11) NOT NULL,
  `marble_id` int(11) NOT NULL,
  PRIMARY KEY (`product_id`, `marble_id`),
  KEY `marble_id` (`marble_id`),
  CONSTRAINT `product_marbles_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `product_marbles_marble_fk` FOREIGN KEY (`marble_id`) REFERENCES `marbles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create product wooden items relationship table
CREATE TABLE IF NOT EXISTS `product_wooden_items` (
  `product_id` int(11) NOT NULL,
  `wooden_item_id` int(11) NOT NULL,
  PRIMARY KEY (`product_id`, `wooden_item_id`),
  KEY `wooden_item_id` (`wooden_item_id`),
  CONSTRAINT `product_wooden_items_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `product_wooden_items_wooden_fk` FOREIGN KEY (`wooden_item_id`) REFERENCES `wooden_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 