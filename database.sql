-- Create the database
CREATE DATABASE IF NOT EXISTS url_shortener;
USE url_shortener;

-- Users Table
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- URLs Table
CREATE TABLE urls (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED, -- Links to the user who created the URL (NULL for guests)
    original_url VARCHAR(1000) NOT NULL,
    short_code VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    hits INT UNSIGNED DEFAULT 0,
    guest TINYINT(1) DEFAULT 0, -- 1 for guest links, 0 for user links
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert Default Admin User
-- Username: admin, Password: admin (encrypted)
INSERT INTO users (username, email, password, role) 
VALUES (
    'admin', 
    'admin@example.com', 
    '$2y$10$jwqSIJHtfEH9qfGc2lmUt.Og45knOZTBY2j7.UYd1ChwClvPBgjj6', -- Password: admin
    'admin'
);

-- Optional: Add Indexes for Faster Queries
CREATE INDEX idx_user_id ON urls (user_id);
CREATE INDEX idx_short_code ON urls (short_code);