-- Update users table with new fields
USE tradingai;

ALTER TABLE users 
ADD COLUMN first_name VARCHAR(50) AFTER username,
ADD COLUMN last_name VARCHAR(50) AFTER first_name,
ADD COLUMN phone VARCHAR(20) AFTER email,
ADD COLUMN phone_verified TINYINT(1) DEFAULT 0 AFTER phone,
ADD COLUMN telegram_id VARCHAR(50) AFTER phone_verified,
ADD COLUMN telegram_verified TINYINT(1) DEFAULT 0 AFTER telegram_id;

-- Update existing admin user
UPDATE users SET 
    first_name = 'Admin',
    last_name = 'User',
    phone = '+39000000000',
    phone_verified = 1,
    telegram_id = '123456789',
    telegram_verified = 1
WHERE username = 'admin';

-- Create OTP verification table
CREATE TABLE IF NOT EXISTS otp_verifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(50) NOT NULL COMMENT 'Telegram ID or Phone',
    otp_code VARCHAR(6) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    verified TINYINT(1) DEFAULT 0,
    attempts INT DEFAULT 0,
    INDEX idx_phone (phone),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
