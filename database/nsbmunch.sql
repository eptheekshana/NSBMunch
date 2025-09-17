-- NSBMunch Database 
-- Create database and use it
CREATE DATABASE IF NOT EXISTS nsbmunch;
USE nsbmunch;

-- Users table for students, lecturers, and staff
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL,
    campus_id VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    user_category ENUM('student', 'lecturer', 'staff') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Shop owners table
CREATE TABLE shop_owners (
    id INT PRIMARY KEY AUTO_INCREMENT,
    shop_id VARCHAR(50) UNIQUE NOT NULL,
    owner_name VARCHAR(100) NOT NULL,
    shop_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    location VARCHAR(255) DEFAULT 'NSBM Campus',
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Food items table
CREATE TABLE food_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    shop_id VARCHAR(50) NOT NULL,
    food_name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category VARCHAR(100) NOT NULL,
    image_path VARCHAR(255) DEFAULT 'default_food.jpg',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (shop_id) REFERENCES shop_owners(shop_id) ON DELETE CASCADE
);

-- Orders table
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id VARCHAR(50) UNIQUE NOT NULL,
    user_campus_id VARCHAR(50) NOT NULL,
    shop_id VARCHAR(50) NOT NULL,
    food_id INT NOT NULL,
    food_name VARCHAR(100) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    total_price DECIMAL(10,2) NOT NULL,
    ordering_time TIME NOT NULL,
    payment_method VARCHAR(50) DEFAULT 'pay_at_canteen',
    user_status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    shop_status ENUM('pending', 'confirmed', 'rejected', 'ready', 'pickup') DEFAULT 'pending',
    order_date DATE DEFAULT (CURRENT_DATE),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_campus_id) REFERENCES users(campus_id) ON DELETE CASCADE,
    FOREIGN KEY (shop_id) REFERENCES shop_owners(shop_id) ON DELETE CASCADE,
    FOREIGN KEY (food_id) REFERENCES food_items(id) ON DELETE CASCADE
);

-- Admin table
CREATE TABLE admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) UNIQUE NOT NULL DEFAULT 'nsbm@gmail.com',
    password VARCHAR(255) NOT NULL
);

-- Insert default admin user
INSERT INTO admin (email, password) VALUES ('nsbm@gmail.com', '123123');


-- Create indexes for better performance
CREATE INDEX idx_users_campus_id ON users(campus_id);
CREATE INDEX idx_shop_owners_shop_id ON shop_owners(shop_id);
CREATE INDEX idx_shop_owners_status ON shop_owners(status);
CREATE INDEX idx_food_items_shop_id ON food_items(shop_id);
CREATE INDEX idx_food_items_category ON food_items(category);
CREATE INDEX idx_orders_order_id ON orders(order_id);
CREATE INDEX idx_orders_user_campus_id ON orders(user_campus_id);
CREATE INDEX idx_orders_shop_id ON orders(shop_id);
CREATE INDEX idx_orders_user_status ON orders(user_status);
CREATE INDEX idx_orders_shop_status ON orders(shop_status);
CREATE INDEX idx_orders_order_date ON orders(order_date);