-- Sauna Booking System Database Schema

-- Drop existing tables if they exist
DROP TABLE IF EXISTS sauna_status;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS users;

-- Create users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('employee', 'admin') NOT NULL DEFAULT 'employee',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create bookings table
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guest_name VARCHAR(100) NOT NULL,
    date DATE NOT NULL,
    time TIME NOT NULL,
    duration INT NOT NULL DEFAULT 1,
    room_number VARCHAR(20) NOT NULL,
    people INT NOT NULL DEFAULT 1,
    status ENUM('active', 'in_use', 'completed', 'cancelled') NOT NULL DEFAULT 'active',
    created_by VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(username) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create sauna_status table
CREATE TABLE sauna_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    status ENUM('available', 'busy', 'out_of_order') NOT NULL DEFAULT 'available',
    reason TEXT NULL,
    booking_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert initial admin user
-- Password is 'admin123' hashed with password_hash()
INSERT INTO users (username, password, role) VALUES 
('admin', '$2y$10$Yp6Sbbm9UrABRGvGf.0zs.EElxj5MUQPkFsIcY1SvL0iU4/aWw8Py', 'admin');

-- Insert initial sauna status
INSERT INTO sauna_status (status) VALUES ('available');