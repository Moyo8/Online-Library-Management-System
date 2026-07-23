-- OLMS Database Schema
-- MySQL 8.0

CREATE DATABASE IF NOT EXISTS olms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE olms;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'librarian', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Books table
CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    author VARCHAR(100) NOT NULL,
    isbn VARCHAR(20) UNIQUE,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Transactions table
CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    issue_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE NULL,
    fine DECIMAL(5,2) DEFAULT 0.00,
    fine_paid DECIMAL(5,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (book_id) REFERENCES books(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Reservations table
CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    reservation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'fulfilled', 'cancelled') DEFAULT 'pending',
    fulfilled_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (book_id) REFERENCES books(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample admin user
INSERT IGNORE INTO users (name, email, password, role) VALUES
('Admin User', 'admin@olms.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'); -- password: password

-- Insert sample librarian user
INSERT IGNORE INTO users (name, email, password, role) VALUES
('Librarian User', 'librarian@olms.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'librarian'); -- password: password

-- Insert sample books
INSERT IGNORE INTO books (title, author, isbn, quantity) VALUES
('The Great Gatsby', 'F. Scott Fitzgerald', '9780743273565', 3),
('To Kill a Mockingbird', 'Harper Lee', '9780061120084', 2),
('1984', 'George Orwell', '9780451524935', 4),
('Pride and Prejudice', 'Jane Austen', '9780141439518', 3),
('The Catcher in the Rye', 'J.D. Salinger', '9780316769488', 2),
-- Best Books by Nigerian Authors
('Things Fall Apart', 'Chinua Achebe', '9780385473733', 5),
('Half of a Yellow Sun', 'Chimamanda Ngozi Adichie', '9781400095209', 4),
('The Famished Road', 'Ben Okri', '9780380725582', 3),
('Purple Hibiscus', 'Chimamanda Ngozi Adichie', '9781616202415', 3),
('Arrow of God', 'Chinua Achebe', '9780385260410', 3),
('Death and the King\'s Horseman', 'Wole Soyinka', '9780393323070', 2),
('The Joys of Motherhood', 'Buchi Emecheta', '9780807041198', 3),
('Efuru', 'Flora Nwapa', '9781592211623', 2),
('Americanah', 'Chimamanda Ngozi Adichie', '9780307944473', 4),
('The Palm-Wine Drinkard', 'Amos Tutuola', '9780571201403', 2);