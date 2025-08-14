-- Restaurant Reservations Database Schema
-- MySQL 5.7/8.0 Compatible

SET FOREIGN_KEY_CHECKS = 0;

-- Drop tables if they exist
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS reservations;
DROP TABLE IF EXISTS shifts;
DROP TABLE IF EXISTS tables;
DROP TABLE IF EXISTS restaurants;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- Users table (clientes, administradores de restaurante, superadmin)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role ENUM('cliente', 'admin_restaurante', 'superadmin') NOT NULL DEFAULT 'cliente',
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
);

-- Restaurants table
CREATE TABLE restaurants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    address TEXT NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(255),
    cuisine_type VARCHAR(100),
    price_range ENUM('$', '$$', '$$$', '$$$$') DEFAULT '$$',
    rating DECIMAL(3,2) DEFAULT 0.00,
    total_reviews INT DEFAULT 0,
    opening_time TIME NOT NULL DEFAULT '09:00:00',
    closing_time TIME NOT NULL DEFAULT '22:00:00',
    max_capacity INT NOT NULL DEFAULT 50,
    auto_confirm BOOLEAN DEFAULT TRUE,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    image_url VARCHAR(500),
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_owner (owner_id),
    INDEX idx_cuisine (cuisine_type),
    INDEX idx_price_range (price_range),
    INDEX idx_rating (rating),
    INDEX idx_status (status)
);

-- Tables/Zones in restaurants
CREATE TABLE tables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    table_number VARCHAR(20) NOT NULL,
    capacity INT NOT NULL,
    zone VARCHAR(100) DEFAULT 'main',
    status ENUM('available', 'occupied', 'reserved', 'maintenance') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_table_restaurant (restaurant_id, table_number),
    INDEX idx_restaurant (restaurant_id),
    INDEX idx_capacity (capacity),
    INDEX idx_status (status)
);

-- Time shifts for reservations
CREATE TABLE shifts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    shift_name VARCHAR(100) NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    max_duration INT NOT NULL DEFAULT 120, -- minutes
    days_of_week SET('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    INDEX idx_restaurant (restaurant_id),
    INDEX idx_times (start_time, end_time)
);

-- Reservations
CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    restaurant_id INT NOT NULL,
    table_id INT NULL,
    shift_id INT NULL,
    reservation_date DATE NOT NULL,
    reservation_time TIME NOT NULL,
    end_time TIME NOT NULL,
    party_size INT NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed', 'no_show') DEFAULT 'pending',
    confirmation_code VARCHAR(20) UNIQUE,
    special_requests TEXT,
    customer_name VARCHAR(255),
    customer_phone VARCHAR(20),
    customer_email VARCHAR(255),
    payment_status ENUM('none', 'pending', 'paid', 'refunded') DEFAULT 'none',
    payment_amount DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (table_id) REFERENCES tables(id) ON DELETE SET NULL,
    FOREIGN KEY (shift_id) REFERENCES shifts(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_restaurant (restaurant_id),
    INDEX idx_date_time (reservation_date, reservation_time),
    INDEX idx_status (status),
    INDEX idx_confirmation (confirmation_code)
);

-- Reviews and ratings
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    restaurant_id INT NOT NULL,
    reservation_id INT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    food_rating INT CHECK (food_rating >= 1 AND food_rating <= 5),
    service_rating INT CHECK (service_rating >= 1 AND service_rating <= 5),
    ambiance_rating INT CHECK (ambiance_rating >= 1 AND ambiance_rating <= 5),
    response TEXT, -- Restaurant owner response
    response_date TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE SET NULL,
    UNIQUE KEY unique_user_restaurant_reservation (user_id, restaurant_id, reservation_id),
    INDEX idx_restaurant (restaurant_id),
    INDEX idx_rating (rating),
    INDEX idx_created (created_at)
);

-- Sample Data
-- Insert sample users
INSERT INTO users (name, email, phone, password, role) VALUES
('Juan Pérez', 'juan@cliente.com', '555-0101', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente'),
('María García', 'maria@cliente.com', '555-0102', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente'),
('Carlos López', 'carlos@restaurante.com', '555-0201', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin_restaurante'),
('Ana Rodríguez', 'ana@restaurante.com', '555-0202', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin_restaurante'),
('Admin Sistema', 'admin@sistema.com', '555-0301', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin');

-- Insert sample restaurants
INSERT INTO restaurants (owner_id, name, description, address, phone, email, cuisine_type, price_range, opening_time, closing_time, max_capacity, latitude, longitude) VALUES
(3, 'El Gordo de Closet', 'Auténtica comida mexicana en un ambiente acogedor', 'Av. Reforma 123, CDMX', '555-1001', 'info@elgordodecloset.com', 'Mexicana', '$$$', '12:00:00', '23:00:00', 80, 19.4326, -99.1332),
(4, 'La Cocina de Ana', 'Comida casera con el sazón de siempre', 'Calle Insurgentes 456, CDMX', '555-1002', 'reservas@lacocinadeana.com', 'Casera', '$$', '08:00:00', '22:00:00', 50, 19.4285, -99.1277);

-- Insert sample tables
INSERT INTO tables (restaurant_id, table_number, capacity, zone) VALUES
(1, 'M1', 2, 'terraza'),
(1, 'M2', 4, 'terraza'),
(1, 'M3', 6, 'terraza'),
(1, 'S1', 2, 'salon_principal'),
(1, 'S2', 4, 'salon_principal'),
(1, 'S3', 4, 'salon_principal'),
(1, 'S4', 6, 'salon_principal'),
(1, 'S5', 8, 'salon_principal'),
(2, 'T1', 2, 'interior'),
(2, 'T2', 4, 'interior'),
(2, 'T3', 4, 'interior'),
(2, 'T4', 6, 'interior'),
(2, 'P1', 4, 'patio'),
(2, 'P2', 6, 'patio');

-- Insert sample shifts
INSERT INTO shifts (restaurant_id, shift_name, start_time, end_time, max_duration, days_of_week) VALUES
(1, 'Almuerzo', '12:00:00', '16:00:00', 120, 'monday,tuesday,wednesday,thursday,friday,saturday,sunday'),
(1, 'Cena', '18:00:00', '23:00:00', 150, 'monday,tuesday,wednesday,thursday,friday,saturday,sunday'),
(2, 'Desayuno', '08:00:00', '11:00:00', 90, 'monday,tuesday,wednesday,thursday,friday,saturday,sunday'),
(2, 'Almuerzo', '12:00:00', '16:00:00', 120, 'monday,tuesday,wednesday,thursday,friday,saturday,sunday'),
(2, 'Cena', '18:00:00', '22:00:00', 120, 'friday,saturday,sunday');

-- Insert sample reservations
INSERT INTO reservations (user_id, restaurant_id, table_id, shift_id, reservation_date, reservation_time, end_time, party_size, status, confirmation_code, customer_name, customer_phone, customer_email) VALUES
(1, 1, 4, 2, '2024-08-20', '19:00:00', '21:00:00', 4, 'confirmed', 'RES001', 'Juan Pérez', '555-0101', 'juan@cliente.com'),
(2, 1, 6, 1, '2024-08-21', '13:00:00', '15:00:00', 2, 'pending', 'RES002', 'María García', '555-0102', 'maria@cliente.com'),
(1, 2, 10, 4, '2024-08-22', '12:30:00', '14:30:00', 4, 'confirmed', 'RES003', 'Juan Pérez', '555-0101', 'juan@cliente.com');

-- Insert sample reviews
INSERT INTO reviews (user_id, restaurant_id, reservation_id, rating, comment, food_rating, service_rating, ambiance_rating) VALUES
(1, 1, 1, 5, 'Excelente comida y servicio. El ambiente es muy agradable.', 5, 5, 4),
(2, 2, NULL, 4, 'Comida casera deliciosa, aunque el servicio fue un poco lento.', 5, 3, 4);

-- Update restaurant ratings based on reviews
UPDATE restaurants SET 
    rating = (SELECT AVG(rating) FROM reviews WHERE restaurant_id = restaurants.id),
    total_reviews = (SELECT COUNT(*) FROM reviews WHERE restaurant_id = restaurants.id);