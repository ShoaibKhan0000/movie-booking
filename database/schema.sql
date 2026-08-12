SET NAMES utf8mb4;
SET time_zone = '+05:30';

CREATE DATABASE IF NOT EXISTS movie_booking_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE movie_booking_db;

DROP TABLE IF EXISTS booking_seats;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS shows;
DROP TABLE IF EXISTS theaters;
DROP TABLE IF EXISTS movies;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE movies (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(160) NOT NULL,
    genre VARCHAR(120) NOT NULL,
    duration VARCHAR(32) NOT NULL,
    language VARCHAR(64) NOT NULL,
    rating DECIMAL(2,1) NOT NULL,
    ticket_price DECIMAL(10,2) NOT NULL,
    poster_url VARCHAR(500) NOT NULL,
    description TEXT NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE theaters (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    city VARCHAR(80) NOT NULL,
    location VARCHAR(180) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_theater_location (name, location)
) ENGINE=InnoDB;

CREATE TABLE shows (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    movie_id INT UNSIGNED NOT NULL,
    theater_id INT UNSIGNED NOT NULL,
    show_time DATETIME NOT NULL,
    base_price DECIMAL(10,2) NOT NULL,
    total_seats SMALLINT UNSIGNED NOT NULL DEFAULT 40,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_shows_movie FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
    CONSTRAINT fk_shows_theater FOREIGN KEY (theater_id) REFERENCES theaters(id) ON DELETE CASCADE,
    INDEX idx_shows_movie_time (movie_id, show_time),
    INDEX idx_shows_theater_time (theater_id, show_time)
) ENGINE=InnoDB;

CREATE TABLE bookings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_reference VARCHAR(32) UNIQUE,
    reservation_token CHAR(64) NOT NULL UNIQUE,
    user_id INT UNSIGNED NOT NULL,
    show_id BIGINT UNSIGNED NOT NULL,
    seats VARCHAR(255) NOT NULL,
    seat_count SMALLINT UNSIGNED NOT NULL,
    base_amount DECIMAL(10,2) NOT NULL,
    gst_amount DECIMAL(10,2) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_id VARCHAR(120) DEFAULT NULL,
    payment_order_id VARCHAR(120) DEFAULT NULL,
    payment_signature VARCHAR(255) DEFAULT NULL,
    status ENUM('PENDING', 'CONFIRMED', 'FAILED', 'CANCELLED') NOT NULL DEFAULT 'PENDING',
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_bookings_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_bookings_show FOREIGN KEY (show_id) REFERENCES shows(id) ON DELETE CASCADE,
    INDEX idx_bookings_show_status (show_id, status),
    INDEX idx_bookings_user (user_id),
    INDEX idx_bookings_expires (expires_at)
) ENGINE=InnoDB;

CREATE TABLE booking_seats (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NOT NULL,
    show_id BIGINT UNSIGNED NOT NULL,
    seat_no VARCHAR(8) NOT NULL,
    status ENUM('HELD', 'CONFIRMED') NOT NULL DEFAULT 'HELD',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_booking_seats_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    CONSTRAINT fk_booking_seats_show FOREIGN KEY (show_id) REFERENCES shows(id) ON DELETE CASCADE,
    UNIQUE KEY unique_show_seat (show_id, seat_no),
    INDEX idx_booking_seats_status (show_id, status)
) ENGINE=InnoDB;

INSERT INTO users (name, email, password, role) VALUES
('Admin User', 'admin@cinepass.in', '$2y$10$Pc4QmYFQjFfD4aSnGOxwNOykwobJKWKv0fYzUKGwEuITdSb9VInuK', 'admin'),
('Demo User', 'demo@cinepass.in', '$2y$10$Pc4QmYFQjFfD4aSnGOxwNOykwobJKWKv0fYzUKGwEuITdSb9VInuK', 'customer');

INSERT INTO movies (title, genre, duration, language, rating, ticket_price, poster_url, description) VALUES
('Kalki 2898 AD', 'Sci-Fi/Action', '2h 56m', 'Hindi, Telugu', 8.1, 400.00, 'https://images.unsplash.com/photo-1534447677768-be436bb09401?auto=format&fit=crop&w=900&q=80', 'In a dystopian future, a warrior rises against oppressive forces in a mythic battle that spans science fiction and ancient prophecy.'),
('Stree 2', 'Horror/Comedy', '2h 24m', 'Hindi', 7.5, 300.00, 'https://images.unsplash.com/photo-1509281373149-e957c6296406?auto=format&fit=crop&w=900&q=80', 'The chilling legend returns to Chanderi as the gang faces a new supernatural terror with outrageous twists and dark humor.'),
('Fighter', 'Action/Drama', '2h 46m', 'Hindi', 7.0, 350.00, 'https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?auto=format&fit=crop&w=900&q=80', 'India''s top Air Force pilots confront high-stakes missions and personal sacrifices in this patriotic aerial action drama.'),
('Dune: Part Two', 'Sci-Fi/Adventure', '2h 46m', 'English', 8.8, 450.00, 'https://images.unsplash.com/photo-1478720568477-152d9b164e26?auto=format&fit=crop&w=900&q=80', 'Paul Atreides unites with the Fremen to wage war across Arrakis while destiny, power, and prophecy collide.'),
('Deadpool & Wolverine', 'Action/Comedy', '2h 08m', 'English', 8.2, 480.00, 'https://images.unsplash.com/photo-1524985069026-dd778a71c7b4?auto=format&fit=crop&w=900&q=80', 'A chaotic multiverse mission forces Deadpool and Wolverine into an explosive partnership loaded with irreverent humor.'),
('Animal', 'Action/Crime', '3h 21m', 'Hindi', 6.7, 320.00, 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=900&q=80', 'A violent father-son saga unfolds through revenge, loyalty, and psychological conflict in a gritty crime epic.'),
('Jawan', 'Action/Thriller', '2h 49m', 'Hindi', 7.3, 350.00, 'https://images.unsplash.com/photo-1626814026160-2237a95fc5a0?auto=format&fit=crop&w=900&q=80', 'A vigilante takes on corruption through daring heists and emotionally charged action in a social thriller.'),
('Oppenheimer', 'Biography/Drama', '3h 00m', 'English', 8.6, 500.00, 'https://images.unsplash.com/photo-1497032205916-ac775f0649ae?auto=format&fit=crop&w=900&q=80', 'Christopher Nolan''s biographical drama chronicles J. Robert Oppenheimer and the moral burden of the atomic age.'),
('Kung Fu Panda 4', 'Animation', '1h 34m', 'English, Hindi', 7.1, 280.00, 'https://images.unsplash.com/photo-1440404653325-ab127d49abc1?auto=format&fit=crop&w=900&q=80', 'Po embarks on a new journey to train the next Dragon Warrior while facing a shape-shifting villain.'),
('Shaitaan', 'Horror/Thriller', '2h 12m', 'Hindi', 6.8, 250.00, 'https://images.unsplash.com/photo-1509347528160-9a9e33742cdb?auto=format&fit=crop&w=900&q=80', 'A family''s peaceful getaway becomes a nightmare when a sinister stranger uses black magic to manipulate their daughter.');

INSERT INTO theaters (name, city, location) VALUES
('PVR', 'Noida', 'Logix City Center, Noida'),
('INOX', 'New Delhi', 'Select CITYWALK, Saket'),
('Cinepolis', 'New Delhi', 'DLF Avenue, Saket'),
('PVR', 'New Delhi', 'Vegas Mall, Dwarka');

INSERT INTO shows (movie_id, theater_id, show_time, base_price)
SELECT
    m.id,
    t.id,
    TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL ((m.id + t.id + s.day_offset) % 5) DAY), s.slot_time),
    m.ticket_price
FROM movies m
CROSS JOIN theaters t
CROSS JOIN (
    SELECT 0 AS day_offset, '10:30:00' AS slot_time
    UNION ALL SELECT 0, '13:45:00'
    UNION ALL SELECT 0, '17:15:00'
    UNION ALL SELECT 0, '20:30:00'
    UNION ALL SELECT 0, '23:00:00'
) s;
