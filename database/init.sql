-- ==============================================================================
-- Script de Inicialización de Base de Datos MySQL 8.0
-- Ecosistema E-Commerce Kodigo
-- ==============================================================================

CREATE DATABASE IF NOT EXISTS `ecommerce_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ecommerce_db`;

-- Tabla de Usuarios
CREATE TABLE IF NOT EXISTS `users` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'client') NOT NULL DEFAULT 'client',
    `remember_token` VARCHAR(100) NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Productos
CREATE TABLE IF NOT EXISTS `products` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `price` DECIMAL(10, 2) NOT NULL,
    `stock` INT NOT NULL DEFAULT 0,
    `image_url` VARCHAR(500) NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Órdenes
CREATE TABLE IF NOT EXISTS `orders` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `total` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `status` ENUM('pending', 'paid', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Detalles de Orden
CREATE TABLE IF NOT EXISTS `order_items` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id` BIGINT UNSIGNED NOT NULL,
    `product_id` BIGINT UNSIGNED NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `unit_price` DECIMAL(10, 2) NOT NULL,
    `subtotal` DECIMAL(10, 2) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Pagos
CREATE TABLE IF NOT EXISTS `payments` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id` BIGINT UNSIGNED NOT NULL,
    `stripe_payment_id` VARCHAR(255) NULL,
    `amount` DECIMAL(10, 2) NOT NULL,
    `status` ENUM('pending', 'succeeded', 'failed') NOT NULL DEFAULT 'pending',
    `payment_method` VARCHAR(50) NOT NULL DEFAULT 'card',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Semillas Iniciales (Contraseña hash bcrypt para 'password123')
-- $2y$12$K1r6a5Zg9T5L.Kz10eYgUu5s2eRkH2kL4kR6w9Zg10eYgUu5s2eRk = 'password123'
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`) VALUES
(1, 'Administrador Demo', 'admin@ecommerce.com', '$2y$12$K1r6a5Zg9T5L.Kz10eYgUu5s2eRkH2kL4kR6w9Zg10eYgUu5s2eRk', 'admin'),
(2, 'Cliente Ejemplo', 'cliente@ecommerce.com', '$2y$12$K1r6a5Zg9T5L.Kz10eYgUu5s2eRkH2kL4kR6w9Zg10eYgUu5s2eRk', 'client')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

INSERT INTO `products` (`id`, `name`, `description`, `price`, `stock`, `image_url`) VALUES
(1, 'Laptop Gaming Victus 16', 'Procesador Intel Core i7, 16GB RAM, 1TB SSD, NVIDIA RTX 4060', 1199.99, 15, 'https://images.unsplash.com/photo-1603302576837-37561b2e2302'),
(2, 'Monitor Ultrawide 34" QHD', 'Monitor curvo 165Hz 1ms HDR400 para productividad y gaming', 449.50, 25, 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf'),
(3, 'Teclado Mecánico RGB Pro', 'Switches mecánicos táctiles, conectividad inalámbrica y Bluetooth', 89.90, 50, 'https://images.unsplash.com/photo-1587829741301-dc798b83add3'),
(4, 'Mouse Ergonómico Inalámbrico', 'Sensor óptico 26K DPI, ultraligero 58g, batería de 80 horas', 59.99, 40, 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7'),
(5, 'Auriculares Inalámbricos ANC', 'Cancelación activa de ruido, audio espacial y 40h de autonomía', 129.00, 30, 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e'),
(6, 'Silla Ergonómica Pro Desk', 'Soporte lumbar ajustable 3D, malla transpirable y reclinación 135°', 219.00, 10, 'https://images.unsplash.com/photo-1580481077195-73ab013f33d7')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);
