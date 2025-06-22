-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS sauna_booking_system CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE sauna_booking_system;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'employee') NOT NULL DEFAULT 'employee',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guest_name VARCHAR(100) NOT NULL,
    date DATE NOT NULL,
    time TIME NOT NULL,
    duration INT NOT NULL DEFAULT 1,
    room_number INT NOT NULL,
    people INT NOT NULL DEFAULT 1,
    status ENUM('active', 'completed', 'cancelled') NOT NULL DEFAULT 'active',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Create sauna_status table
CREATE TABLE IF NOT EXISTS sauna_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_number INT NOT NULL UNIQUE,
    status ENUM('available', 'occupied', 'maintenance') NOT NULL DEFAULT 'available',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, role) VALUES 
('admin', '$2y$10$GvKBSj.1E7EQnH.KJyx7R.FylpTiiuA0Z1ZxqmXwfoZ2OeGPmqvGa', 'admin');

-- Insert default sauna rooms
INSERT INTO sauna_status (room_number, status) VALUES 
(1, 'available'),
(2, 'available'),
(3, 'available');