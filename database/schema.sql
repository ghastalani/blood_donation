-- CREATE DATABASE IF NOT EXISTS blood_don;

-- USE blood_don;

-- -- Cities Table
-- CREATE TABLE IF NOT EXISTS cities (
--     id VARCHAR(36) PRIMARY KEY,
--     name_ar VARCHAR(255) NOT NULL,
--     name_en VARCHAR(255) NOT NULL,
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- );

-- -- Users Table (for credentials)
-- CREATE TABLE IF NOT EXISTS users (
--     id VARCHAR(36) PRIMARY KEY,
--     email VARCHAR(255) UNIQUE NOT NULL,
--     password_hash VARCHAR(255) NOT NULL,
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- );

-- -- Profiles Table
-- CREATE TABLE IF NOT EXISTS profiles (
--     id VARCHAR(36) PRIMARY KEY,
--     user_id VARCHAR(36) UNIQUE NOT NULL,
--     name VARCHAR(255) NOT NULL,
--     phone VARCHAR(20) NOT NULL,
--     email VARCHAR(255) NOT NULL,
--     city_id VARCHAR(36),
--     role ENUM('donor', 'requester', 'admin') NOT NULL DEFAULT 'donor',
--     blood_type ENUM(
--         'A+',
--         'A-',
--         'B+',
--         'B-',
--         'AB+',
--         'AB-',
--         'O+',
--         'O-'
--     ),
--     nni VARCHAR(10),
--     is_available BOOLEAN DEFAULT TRUE,
--     cooldown_end_date TIMESTAMP NULL,
--     is_blocked BOOLEAN DEFAULT FALSE,
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
--     FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
--     FOREIGN KEY (city_id) REFERENCES cities (id) ON DELETE SET NULL
-- );

-- -- Donation Requests Table
-- CREATE TABLE IF NOT EXISTS donation_requests (
--     id VARCHAR(36) PRIMARY KEY,
--     requester_id VARCHAR(36) NOT NULL,
--     donor_id VARCHAR(36) NOT NULL,
--     message TEXT,
--     status ENUM(
--         'pending',
--         'accepted',
--         'rejected'
--     ) DEFAULT 'pending',
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
--     FOREIGN KEY (requester_id) REFERENCES profiles (id) ON DELETE CASCADE,
--     FOREIGN KEY (donor_id) REFERENCES profiles (id) ON DELETE CASCADE
-- );

-- -- Notifications Table
-- CREATE TABLE IF NOT EXISTS notifications (
--     id VARCHAR(36) PRIMARY KEY,
--     user_id VARCHAR(36) NOT NULL,
--     title_ar VARCHAR(255) NOT NULL,
--     title_en VARCHAR(255) NOT NULL,
--     message_ar TEXT NOT NULL,
--     message_en TEXT NOT NULL,
--     type VARCHAR(50) DEFAULT 'info',
--     is_read BOOLEAN DEFAULT FALSE,
--     related_request_id VARCHAR(36),
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     FOREIGN KEY (user_id) REFERENCES profiles (id) ON DELETE CASCADE,
--     FOREIGN KEY (related_request_id) REFERENCES donation_requests (id) ON DELETE SET NULL
-- );

-- -- Contact Messages Table
-- CREATE TABLE IF NOT EXISTS contact_messages (
--     id VARCHAR(36) PRIMARY KEY,
--     name VARCHAR(255) NOT NULL,
--     email VARCHAR(255) NOT NULL,
--     message TEXT NOT NULL,
--     is_read BOOLEAN DEFAULT FALSE,
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- );

-- -- Seed Cities
-- INSERT INTO
--     cities (id, name_ar, name_en)
-- VALUES (UUID(), 'العيون', 'Laayoune'),
--     (UUID(), 'السمارة', 'Smara'),
--     (UUID(), 'بوجدور', 'Boujdour'),
--     (UUID(), 'الداخلة', 'Dakhla'),
--     (UUID(), 'الطرفاية', 'Tarfaya');

-- =========================
-- Création de la base
-- =========================

-- =========================
-- Création de la base
-- =========================

-- =========================
-- 1️⃣ التأكد من استخدام قاعدة البيانات
-- =========================
USE blood_don;

-- =========================
-- 1️⃣ جدول المستخدمين (Users)
-- =========================
CREATE TABLE IF NOT EXISTS users (
    id VARCHAR(36) PRIMARY KEY,
    email VARCHAR(191) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- =========================
-- 2️⃣ جدول الملفات الشخصية (Profiles)
-- =========================
CREATE TABLE IF NOT EXISTS profiles (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(191) NOT NULL,
    city_id VARCHAR(255), -- Stores city name (e.g. 'Nouakchott')
    role ENUM('donor', 'requester', 'admin') NOT NULL DEFAULT 'donor',
    blood_type ENUM(
        'A+',
        'A-',
        'B+',
        'B-',
        'AB+',
        'AB-',
        'O+',
        'O-'
    ),
    nni VARCHAR(10),
    is_available BOOLEAN DEFAULT TRUE,
    cooldown_end_date TIMESTAMP NULL,
    last_donation_date TIMESTAMP NULL,
    is_blocked BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- =========================
-- 2.5️⃣ جدول التبرعات القبول (Accepted Donations)
-- =========================
CREATE TABLE IF NOT EXISTS accepted_donations (
    id VARCHAR(36) PRIMARY KEY,
    donor_id VARCHAR(36) NOT NULL,
    request_id VARCHAR(36) NOT NULL,
    date_accepted TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES profiles (id) ON DELETE CASCADE,
    FOREIGN KEY (request_id) REFERENCES donation_requests (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- =========================
-- 3️⃣ جدول طلبات التبرع (Donation Requests)
-- =========================
CREATE TABLE IF NOT EXISTS donation_requests (
    id VARCHAR(36) PRIMARY KEY,
    requester_id VARCHAR(36) NOT NULL,
    donor_id VARCHAR(36) NOT NULL,
    message TEXT,
    status ENUM(
        'pending',
        'accepted',
        'rejected'
    ) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (requester_id) REFERENCES profiles (id) ON DELETE CASCADE,
    FOREIGN KEY (donor_id) REFERENCES profiles (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- =========================
-- 4️⃣ جدول الإشعارات (Notifications)
-- =========================
CREATE TABLE IF NOT EXISTS notifications (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    title_ar VARCHAR(255) NOT NULL,
    title_en VARCHAR(255) NOT NULL,
    message_ar TEXT NOT NULL,
    message_en TEXT NOT NULL,
    type VARCHAR(50) DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    related_request_id VARCHAR(36),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES profiles (id) ON DELETE CASCADE,
    FOREIGN KEY (related_request_id) REFERENCES donation_requests (id) ON DELETE SET NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- =========================
-- 5️⃣ جدول رسائل التواصل (Contact Messages)
-- =========================
CREATE TABLE IF NOT EXISTS contact_messages (
    id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(191) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;