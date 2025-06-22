-- Create products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock INT DEFAULT 0,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert some sample products
INSERT INTO products (name, description, price, stock, image_url) VALUES
('Premium Sauna Rental', 'Full day rental of our premium sauna facility', 99.99, 1, NULL),
('Half-Day Sauna', 'Four-hour rental of standard sauna', 49.99, 1, NULL),
('Sauna Gift Card', 'Gift card for a full-day sauna experience', 89.99, 10, NULL),
('Towel Set', 'Premium set of 3 towels for sauna use', 24.99, 50, NULL),
('Sauna Aromatherapy Kit', 'Set of 5 essential oils for sauna use', 34.99, 25, NULL),
('Wooden Sauna Bucket', 'Traditional wooden bucket and ladle', 29.99, 15, NULL),
('Sauna Thermometer', 'High-quality sauna thermometer', 19.99, 20, NULL),
('Sauna Stones Set', 'Set of 20 premium sauna stones', 39.99, 10, NULL),
('Weekly Sauna Pass', '7 consecutive days of sauna access', 199.99, 5, NULL),
('Monthly Sauna Membership', 'Unlimited sauna access for 30 days', 399.99, 3, NULL);