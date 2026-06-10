-- CertGen Database Setup

CREATE DATABASE IF NOT EXISTS certgen;
USE certgen;

CREATE TABLE IF NOT EXISTS certificate_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    certificate_type VARCHAR(100) NOT NULL,
    student_name VARCHAR(255) NOT NULL,
    lrn VARCHAR(20) DEFAULT '',
    grade_level VARCHAR(50) DEFAULT '',
    section_track VARCHAR(100) DEFAULT '',
    curriculum VARCHAR(255) DEFAULT '',
    school_year VARCHAR(20) DEFAULT '',
    purpose VARCHAR(255) DEFAULT '',
    date_issued VARCHAR(50) NOT NULL,
    principal_name VARCHAR(100) DEFAULT '',
    generated_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS certificate_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ref_number VARCHAR(50) NOT NULL UNIQUE,
    student_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    contact_number VARCHAR(50) DEFAULT '',
    lrn VARCHAR(20) DEFAULT '',
    certificate_type VARCHAR(100) NOT NULL,
    grade_level VARCHAR(50) DEFAULT '',
    school_year VARCHAR(20) DEFAULT '',
    section_track VARCHAR(100) DEFAULT '',
    curriculum VARCHAR(255) DEFAULT '',
    purpose VARCHAR(255) DEFAULT '',
    id_image VARCHAR(255) DEFAULT '',
    selfie_image VARCHAR(255) DEFAULT '',
    place_of_birth VARCHAR(255) DEFAULT '',
    birth_date VARCHAR(100) DEFAULT '',
    address TEXT,
    status VARCHAR(20) DEFAULT 'Pending',
    remarks TEXT,
    processed_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (status),
    INDEX (ref_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    certificate_type VARCHAR(100) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Safely add status if it doesn't exist
SET @dbname = DATABASE();
SET @tablename = "templates";
SET @columnname = "status";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD ", @columnname, " VARCHAR(20) DEFAULT 'active';")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) DEFAULT '',
    role VARCHAR(50) DEFAULT 'admin',
    status VARCHAR(20) DEFAULT 'active',
    last_login DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Initial default user: admin / admin123
INSERT INTO users (username, password, full_name, role, status) 
SELECT 'admin', '$2y$10$gfbYJ6zs8hrywM6y02TuMeC9haoX/xeYp40DW.lfOQTou8Qs33K3a', 'System Administrator', 'super_admin', 'active'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'admin');
