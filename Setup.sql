-- Run this once in phpMyAdmin (or the mysql CLI) before using the app.

CREATE DATABASE IF NOT EXISTS registration_demo;
USE registration_demo;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    mobile VARCHAR(10) NOT NULL,
    email VARCHAR(150) NOT NULL,
    college VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_mobile (mobile),
    UNIQUE KEY unique_email (email)
);