SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS php_mvc_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE php_mvc_shop;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'customer') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(160) NOT NULL,
    category VARCHAR(80) NOT NULL,
    description TEXT NOT NULL,
    price INT UNSIGNED NOT NULL,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    emoji VARCHAR(16) NOT NULL DEFAULT '📦',
    image_url VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    customer_name VARCHAR(120) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    address VARCHAR(500) NOT NULL,
    payment_method ENUM('cod', 'demo') NOT NULL DEFAULT 'cod',
    payment_status ENUM('unpaid', 'paid') NOT NULL DEFAULT 'unpaid',
    status ENUM('pending', 'processing', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    total BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED DEFAULT NULL,
    product_name VARCHAR(160) NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    price INT UNSIGNED NOT NULL,
    subtotal BIGINT UNSIGNED NOT NULL,
    CONSTRAINT fk_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO users (full_name, email, password, role) VALUES
('Quản trị viên', 'admin@nova.test', 'admin123', 'admin'),
('Khách hàng demo', 'khach@nova.test', '123456', 'customer');

INSERT INTO products (name, category, description, price, stock, emoji) VALUES
('Tai nghe Nova Sound Pro', 'Âm thanh', 'Tai nghe không dây với âm thanh cân bằng, đệm tai mềm và thời lượng pin dài.', 1290000, 24, '🎧'),
('Đồng hồ Orbit Watch', 'Công nghệ', 'Thiết kế tối giản, màn hình sắc nét và tính năng theo dõi vận động hằng ngày.', 1890000, 18, '⌚'),
('Bàn phím Mech K68', 'Công nghệ', 'Bàn phím cơ nhỏ gọn với cảm giác gõ rõ ràng và hệ thống đèn nền nổi bật.', 950000, 32, '⌨️'),
('Balo Urban Daily', 'Phụ kiện', 'Balo dành cho công việc và học tập, nhiều ngăn tiện dụng, chất liệu bền nhẹ.', 690000, 27, '🎒'),
('Đèn bàn Lumi Light', 'Đời sống', 'Đèn LED để bàn ánh sáng dịu, điều chỉnh linh hoạt, phù hợp góc làm việc.', 490000, 40, '💡');
