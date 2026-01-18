-- Missing Children Pattern Detector Database Schema
-- MySQL 8.0+

CREATE DATABASE IF NOT EXISTS missing_children_db;
USE missing_children_db;

-- Admin/Investigator Accounts Table
CREATE TABLE IF NOT EXISTS admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    role ENUM('admin', 'investigator') DEFAULT 'investigator',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Missing Children Table
CREATE TABLE IF NOT EXISTS missing_children (
    child_id INT AUTO_INCREMENT PRIMARY KEY,
    case_number VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    age INT NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    date_of_birth DATE,
    missing_date DATETIME NOT NULL,
    missing_location_city VARCHAR(100) NOT NULL,
    missing_location_area VARCHAR(100),
    missing_location_landmark VARCHAR(200),
    missing_location_latitude DECIMAL(10, 8),
    missing_location_longitude DECIMAL(11, 8),
    physical_description TEXT,
    clothing_description TEXT,
    photo_url VARCHAR(255),
    school_name VARCHAR(100),
    parent_guardian_name VARCHAR(100),
    parent_guardian_contact VARCHAR(50),
    case_status ENUM('Open', 'Matched', 'Resolved') DEFAULT 'Open',
    reported_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (reported_by) REFERENCES admins(admin_id) ON DELETE SET NULL,
    INDEX idx_case_number (case_number),
    INDEX idx_name (first_name, last_name),
    INDEX idx_missing_date (missing_date),
    INDEX idx_city (missing_location_city),
    INDEX idx_status (case_status),
    INDEX idx_location (missing_location_latitude, missing_location_longitude)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Found Children Table
CREATE TABLE IF NOT EXISTS found_children (
    found_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    age INT,
    gender ENUM('Male', 'Female', 'Other'),
    found_date DATETIME NOT NULL,
    found_location_city VARCHAR(100) NOT NULL,
    found_location_area VARCHAR(100),
    found_location_landmark VARCHAR(200),
    found_location_latitude DECIMAL(10, 8),
    found_location_longitude DECIMAL(11, 8),
    physical_description TEXT,
    clothing_description TEXT,
    condition_description TEXT,
    matched_with_child_id INT NULL,
    matched_by INT NULL,
    matched_at TIMESTAMP NULL,
    reported_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (matched_with_child_id) REFERENCES missing_children(child_id) ON DELETE SET NULL,
    FOREIGN KEY (matched_by) REFERENCES admins(admin_id) ON DELETE SET NULL,
    FOREIGN KEY (reported_by) REFERENCES admins(admin_id) ON DELETE SET NULL,
    INDEX idx_found_date (found_date),
    INDEX idx_city (found_location_city),
    INDEX idx_matched (matched_with_child_id),
    INDEX idx_location (found_location_latitude, found_location_longitude)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Witness Reports Table
CREATE TABLE IF NOT EXISTS witness_reports (
    witness_id INT AUTO_INCREMENT PRIMARY KEY,
    child_id INT NOT NULL,
    witness_name VARCHAR(100),
    witness_contact VARCHAR(50),
    witness_address TEXT,
    report_date DATETIME NOT NULL,
    sighting_location_city VARCHAR(100),
    sighting_location_area VARCHAR(100),
    sighting_location_landmark VARCHAR(200),
    sighting_location_latitude DECIMAL(10, 8),
    sighting_location_longitude DECIMAL(11, 8),
    sighting_date_time DATETIME,
    description TEXT,
    credibility_score INT DEFAULT 5 CHECK (credibility_score BETWEEN 1 AND 10),
    reported_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (child_id) REFERENCES missing_children(child_id) ON DELETE CASCADE,
    FOREIGN KEY (reported_by) REFERENCES admins(admin_id) ON DELETE SET NULL,
    INDEX idx_child_id (child_id),
    INDEX idx_sighting_date (sighting_date_time),
    INDEX idx_location (sighting_location_latitude, sighting_location_longitude)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Suspects Table
CREATE TABLE IF NOT EXISTS suspects (
    suspect_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    alias VARCHAR(100),
    age INT,
    gender ENUM('Male', 'Female', 'Other'),
    physical_description TEXT,
    known_address TEXT,
    criminal_history TEXT,
    photo_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (first_name, last_name),
    INDEX idx_alias (alias)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Suspect-Case Association Table (Many-to-Many)
CREATE TABLE IF NOT EXISTS suspect_cases (
    association_id INT AUTO_INCREMENT PRIMARY KEY,
    suspect_id INT NOT NULL,
    child_id INT NOT NULL,
    association_type ENUM('Primary', 'Secondary', 'Suspected') DEFAULT 'Suspected',
    description TEXT,
    reported_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (suspect_id) REFERENCES suspects(suspect_id) ON DELETE CASCADE,
    FOREIGN KEY (child_id) REFERENCES missing_children(child_id) ON DELETE CASCADE,
    FOREIGN KEY (reported_by) REFERENCES admins(admin_id) ON DELETE SET NULL,
    UNIQUE KEY unique_association (suspect_id, child_id),
    INDEX idx_suspect (suspect_id),
    INDEX idx_child (child_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sightings/Location History Table
CREATE TABLE IF NOT EXISTS sightings (
    sighting_id INT AUTO_INCREMENT PRIMARY KEY,
    child_id INT NOT NULL,
    sighting_date_time DATETIME NOT NULL,
    location_city VARCHAR(100) NOT NULL,
    location_area VARCHAR(100),
    location_landmark VARCHAR(200),
    location_latitude DECIMAL(10, 8),
    location_longitude DECIMAL(11, 8),
    reported_by_witness VARCHAR(100),
    witness_contact VARCHAR(50),
    description TEXT,
    reliability_score INT DEFAULT 5 CHECK (reliability_score BETWEEN 1 AND 10),
    reported_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (child_id) REFERENCES missing_children(child_id) ON DELETE CASCADE,
    FOREIGN KEY (reported_by) REFERENCES admins(admin_id) ON DELETE SET NULL,
    INDEX idx_child_id (child_id),
    INDEX idx_sighting_date (sighting_date_time),
    INDEX idx_city (location_city),
    INDEX idx_location (location_latitude, location_longitude)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Case History/Status Tracking Table
CREATE TABLE IF NOT EXISTS case_history (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    child_id INT NOT NULL,
    status ENUM('Open', 'Matched', 'Resolved') NOT NULL,
    notes TEXT,
    updated_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (child_id) REFERENCES missing_children(child_id) ON DELETE CASCADE,
    FOREIGN KEY (updated_by) REFERENCES admins(admin_id) ON DELETE SET NULL,
    INDEX idx_child_id (child_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Alerts Table
CREATE TABLE IF NOT EXISTS alerts (
    alert_id INT AUTO_INCREMENT PRIMARY KEY,
    alert_type ENUM('Multiple_Missing_Same_Location', 'Found_Match', 'Repeat_Suspect', 'Suspicious_Zone') NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    related_child_id INT,
    related_suspect_id INT,
    related_location VARCHAR(200),
    severity ENUM('Low', 'Medium', 'High', 'Critical') DEFAULT 'Medium',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (related_child_id) REFERENCES missing_children(child_id) ON DELETE CASCADE,
    FOREIGN KEY (related_suspect_id) REFERENCES suspects(suspect_id) ON DELETE CASCADE,
    INDEX idx_type (alert_type),
    INDEX idx_severity (severity),
    INDEX idx_read (is_read),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert Sample Admin Account (password: admin123)
INSERT INTO admins (username, password_hash, full_name, email, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@missingchildren.local', 'admin'),
('investigator1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Investigator', 'investigator1@missingchildren.local', 'investigator');

-- Insert Sample Missing Children Data
INSERT INTO missing_children (case_number, first_name, last_name, age, gender, date_of_birth, missing_date, missing_location_city, missing_location_area, missing_location_landmark, missing_location_latitude, missing_location_longitude, physical_description, clothing_description, school_name, parent_guardian_name, parent_guardian_contact, case_status, reported_by) VALUES
('MC-2024-001', 'Ahmad', 'Khan', 8, 'Male', '2016-03-15', '2024-01-15 14:30:00', 'Karachi', 'Gulshan-e-Iqbal', 'Near School Gate', 24.9207, 67.0662, 'Height: 4ft, Brown hair, Brown eyes, Fair complexion', 'Blue school uniform, Black shoes', 'ABC Primary School', 'Ali Khan', '0300-1234567', 'Open', 1),
('MC-2024-002', 'Fatima', 'Ali', 10, 'Female', '2014-05-20', '2024-01-20 16:00:00', 'Lahore', 'Model Town', 'Park Area', 31.5204, 74.3587, 'Height: 4.5ft, Black hair, Brown eyes, Medium complexion', 'Pink dress, White sandals', 'XYZ Girls School', 'Hassan Ali', '0300-2345678', 'Open', 1),
('MC-2024-003', 'Hassan', 'Ahmed', 7, 'Male', '2017-08-10', '2024-02-05 10:15:00', 'Karachi', 'Gulshan-e-Iqbal', 'Near School Gate', 24.9207, 67.0662, 'Height: 3.8ft, Black hair, Black eyes, Fair complexion', 'Green shirt, Blue jeans', 'ABC Primary School', 'Ahmed Khan', '0300-3456789', 'Open', 1),
('MC-2024-004', 'Ayesha', 'Malik', 9, 'Female', '2015-02-14', '2024-02-10 13:45:00', 'Islamabad', 'F-7', 'Market Area', 33.6844, 73.0479, 'Height: 4.2ft, Brown hair, Green eyes, Fair complexion', 'Red dress, Black shoes', 'City School', 'Malik Shah', '0300-4567890', 'Matched', 1),
('MC-2024-005', 'Usman', 'Raza', 11, 'Male', '2013-11-25', '2024-02-15 15:30:00', 'Lahore', 'Model Town', 'Park Area', 31.5204, 74.3587, 'Height: 5ft, Brown hair, Brown eyes, Medium complexion', 'White shirt, Grey pants', 'Public School', 'Raza Ali', '0300-5678901', 'Open', 1);

-- Insert Sample Found Children Data
INSERT INTO found_children (first_name, last_name, age, gender, found_date, found_location_city, found_location_area, found_location_landmark, found_location_latitude, found_location_longitude, physical_description, clothing_description, condition_description, matched_with_child_id, matched_by, matched_at, reported_by) VALUES
('Ayesha', 'Malik', 9, 'Female', '2024-02-12 10:00:00', 'Islamabad', 'F-8', 'Police Station', 33.6844, 73.0479, 'Height: 4.2ft, Brown hair, Green eyes, Fair complexion', 'Red dress, Black shoes', 'Safe and healthy', 4, 1, '2024-02-12 11:00:00', 1),
('Unknown', 'Girl', 8, 'Female', '2024-02-20 09:30:00', 'Rawalpindi', 'Cantt', 'Hospital', 33.5651, 73.0169, 'Height: 4ft, Black hair, Brown eyes', 'Pink dress', 'Good condition, unable to speak', NULL, NULL, NULL, 1);

-- Insert Sample Suspects
INSERT INTO suspects (first_name, last_name, alias, age, gender, physical_description, known_address, criminal_history) VALUES
('Unknown', 'Suspect1', 'The Shadow', 35, 'Male', 'Height: 5.8ft, Medium build, Beard, Wears cap', 'Unknown', 'Suspected in multiple child abduction cases'),
('Ali', 'Bhatti', NULL, 42, 'Male', 'Height: 5.10ft, Heavy build, Bald, Scar on left cheek', 'Lahore, Model Town', 'Previous child-related offenses'),
('Unknown', 'Suspect2', 'The Watcher', 28, 'Male', 'Height: 5.6ft, Slim build, Glasses, Clean shaven', 'Unknown', 'Seen loitering near schools');

-- Insert Sample Suspect-Case Associations
INSERT INTO suspect_cases (suspect_id, child_id, association_type, description, reported_by) VALUES
(1, 1, 'Primary', 'Witness saw this suspect near the school at time of disappearance', 1),
(1, 3, 'Suspected', 'Similar description to suspect in case MC-2024-001', 1),
(2, 2, 'Secondary', 'Seen in the area around the time child went missing', 1),
(3, 5, 'Suspected', 'Reported by multiple witnesses in park area', 1);

-- Insert Sample Witness Reports
INSERT INTO witness_reports (child_id, witness_name, witness_contact, report_date, sighting_location_city, sighting_location_area, sighting_location_landmark, sighting_date_time, description, credibility_score, reported_by) VALUES
(1, 'Mohammad Saleem', '0300-1111111', '2024-01-16 10:00:00', 'Karachi', 'Gulshan-e-Iqbal', 'Near School Gate', '2024-01-15 14:35:00', 'Saw a child matching description being led away by an adult male', 8, 1),
(2, 'Sara Ahmed', '0300-2222222', '2024-01-21 09:00:00', 'Lahore', 'Model Town', 'Park Area', '2024-01-20 16:10:00', 'Saw a girl in pink dress being taken in a white car', 7, 1),
(3, 'Ali Raza', '0300-3333333', '2024-02-06 11:00:00', 'Karachi', 'Gulshan-e-Iqbal', 'Near School Gate', '2024-02-05 10:20:00', 'Child was seen with unknown person near school', 6, 1);

-- Insert Sample Sightings
INSERT INTO sightings (child_id, sighting_date_time, location_city, location_area, location_landmark, location_latitude, location_longitude, reported_by_witness, witness_contact, description, reliability_score, reported_by) VALUES
(1, '2024-01-16 08:00:00', 'Karachi', 'Gulshan-e-Iqbal', 'Bus Stop', 24.9210, 67.0665, 'Anonymous', 'N/A', 'Child seen at bus stop with adult', 5, 1),
(1, '2024-01-17 12:00:00', 'Karachi', 'PECHS', 'Market', 24.9000, 67.0500, 'Bilal Khan', '0300-4444444', 'Possible sighting at market', 6, 1),
(2, '2024-01-21 18:00:00', 'Lahore', 'Model Town', 'Residential Area', 31.5210, 74.3590, 'Zainab Ali', '0300-5555555', 'Girl matching description seen', 7, 1);

-- Insert Sample Case History
INSERT INTO case_history (child_id, status, notes, updated_by) VALUES
(1, 'Open', 'Case opened, initial investigation started', 1),
(2, 'Open', 'Case opened, witness reports collected', 1),
(3, 'Open', 'Case opened, similar pattern to case MC-2024-001', 1),
(4, 'Open', 'Case opened', 1),
(4, 'Matched', 'Matched with found child report on 2024-02-12', 1),
(5, 'Open', 'Case opened, multiple witnesses reported', 1);

-- Insert Sample Alerts
INSERT INTO alerts (alert_type, title, message, related_child_id, related_suspect_id, related_location, severity, is_read) VALUES
('Multiple_Missing_Same_Location', 'Multiple Children Missing from Same Location', 'Two children (MC-2024-001, MC-2024-003) have gone missing from the same location: Gulshan-e-Iqbal, Near School Gate', NULL, NULL, 'Gulshan-e-Iqbal, Near School Gate', 'High', FALSE),
('Repeat_Suspect', 'Suspect Linked to Multiple Cases', 'Suspect "The Shadow" (ID: 1) is now linked to 2 cases: MC-2024-001 and MC-2024-003', NULL, 1, NULL, 'High', FALSE),
('Found_Match', 'Potential Match Found', 'Found child report #2 may match missing child case MC-2024-002 based on age, gender, and location proximity', 2, NULL, NULL, 'Medium', FALSE);

