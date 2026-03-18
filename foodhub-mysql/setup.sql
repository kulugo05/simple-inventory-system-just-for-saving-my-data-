-- ============================================================
--  Bron Michael's FoodHub — MySQL Schema
--  Run this file once to set up your database
--  Usage: mysql -u root -p < setup.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS foodhub_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE foodhub_db;

-- ── Categories ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS categories (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    name     VARCHAR(100) NOT NULL UNIQUE,
    icon     VARCHAR(10)  NOT NULL DEFAULT '📦',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default categories
INSERT IGNORE INTO categories (name, icon) VALUES
    ('Beverages',           '🥤'),
    ('Dairy',               '🥛'),
    ('Frozen',              '🧊'),
    ('Grains & Pasta',      '🌾'),
    ('Meat & Seafood',      '🥩'),
    ('Produce',             '🥦'),
    ('Snacks',              '🍿'),
    ('Spices & Condiments', '🧂'),
    ('Bakery',              '🥖'),
    ('Canned Goods',        '🥫');

-- ── Products ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS products (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(200)   NOT NULL,
    sku           VARCHAR(100)   DEFAULT NULL,
    category      VARCHAR(100)   DEFAULT 'Uncategorized',
    quantity      INT            NOT NULL DEFAULT 0,
    unit          VARCHAR(20)    NOT NULL DEFAULT 'pcs',
    price         DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    cost          DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    reorder_level INT            NOT NULL DEFAULT 10,
    supplier      VARCHAR(200)   DEFAULT NULL,
    description   TEXT           DEFAULT NULL,
    expiry_date   DATE           DEFAULT NULL,
    created_at    TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_sku      (sku),
    INDEX idx_quantity (quantity)
);

-- ── Stock Transactions ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS transactions (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    product_id   INT          NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    type         ENUM('stock_in','stock_out','adjustment') NOT NULL,
    quantity     INT          NOT NULL,
    delta        INT          NOT NULL,
    before_qty   INT          NOT NULL,
    after_qty    INT          NOT NULL,
    note         TEXT         DEFAULT NULL,
    created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product  (product_id),
    INDEX idx_type     (type),
    INDEX idx_created  (created_at)
);

-- ── Activity Log ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS activity_log (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    action     VARCHAR(50)  NOT NULL,
    item       VARCHAR(200) NOT NULL,
    detail     TEXT         DEFAULT NULL,
    ip_address VARCHAR(45)  DEFAULT NULL,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_action  (action),
    INDEX idx_created (created_at)
);
