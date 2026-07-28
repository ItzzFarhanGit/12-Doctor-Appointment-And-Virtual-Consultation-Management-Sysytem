-- SAJEEFA Online Clinic System - Database Setup
-- Import this file in phpMyAdmin or run: mysql -u root -p < online_clinic.sql

CREATE DATABASE IF NOT EXISTS online_clinic CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE online_clinic;

-- =============================================
-- TABLE 1: Patients (Patient Register / Login)
-- =============================================
CREATE TABLE IF NOT EXISTS patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id VARCHAR(10) UNIQUE NOT NULL,   -- e.g. PID-0001
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,           -- password_hash() used in PHP
    phone VARCHAR(15),
    gender ENUM('Male','Female','Other'),
    registered_date DATE DEFAULT (CURDATE()),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- TABLE 2: Doctors (Doctor Login + Profile)
-- =============================================
CREATE TABLE IF NOT EXISTS doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id VARCHAR(10) UNIQUE NOT NULL,    -- e.g. DOC-001
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    specialty VARCHAR(100) DEFAULT 'General',
    virtual_fee INT DEFAULT 1500,
    physical_fee INT DEFAULT 2500,
    available_days VARCHAR(100) DEFAULT 'Monday - Friday',
    available_time VARCHAR(100) DEFAULT '08:00 - 06:00 PM',
    profile_image VARCHAR(255) DEFAULT 'drprofile.jpeg',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- TABLE 3: Appointments
-- =============================================
CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id VARCHAR(15) UNIQUE NOT NULL,  -- e.g. APP-1234
    patient_id VARCHAR(10),
    doctor_id VARCHAR(10),
    patient_name VARCHAR(100),
    doctor_name VARCHAR(100),
    appointment_date DATE,
    time_slot VARCHAR(20),
    consult_type ENUM('physical','virtual') DEFAULT 'physical',
    fee INT DEFAULT 0,
    payment_status ENUM('paid','unpaid') DEFAULT 'unpaid',
    doctor_approval ENUM('pending','approved','rejected') DEFAULT 'pending',
    meeting_link VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- SAJEEFA: slot_lock - auto-generated "doctor_id|date|time_slot" ONLY for non-rejected
    -- appointments (NULL when rejected). The UNIQUE index below makes it physically
    -- impossible for the same doctor to have two active bookings in the same time_slot on
    -- the same date - even if two patients submit at the exact same second. Rejected
    -- appointments become NULL here so that slot re-opens for someone else to book.
    slot_lock VARCHAR(80) GENERATED ALWAYS AS (
        CASE WHEN doctor_approval <> 'rejected'
             THEN CONCAT(doctor_id, '|', appointment_date, '|', time_slot)
             ELSE NULL END
    ) STORED,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE SET NULL,
    FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id) ON DELETE SET NULL,
    UNIQUE KEY ux_slot_lock (slot_lock)
);

-- =============================================
-- TABLE 4b: Admins (Admin Login)
-- =============================================
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id VARCHAR(10) UNIQUE NOT NULL,     -- e.g. ADM-001
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default admin account
-- Email: admin@gmail.com | Password: admin123
INSERT IGNORE INTO admins (admin_id, full_name, email, password)
VALUES (
    'ADM-001',
    'System Admin',
    'admin@gmail.com',
    '$2b$10$xc9W0K4/zR1XmZEb7yU9bOdiEzoFBfiggNS9jgPvINOKn02iF1sua'
);

-- =============================================
-- TABLE 4: Prescriptions
-- =============================================
CREATE TABLE IF NOT EXISTS prescriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_name VARCHAR(100),
    doctor_id VARCHAR(10),
    patient_name VARCHAR(100),
    patient_phone VARCHAR(15),
    diagnosis TEXT,
    medicines TEXT,
    instructions TEXT,
    prescription_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- SAMPLE DATA - 1 Doctor pre-loaded
-- password: Sarah123  (hashed below)
-- =============================================
INSERT IGNORE INTO doctors (doctor_id, full_name, email, password, specialty, virtual_fee, physical_fee)
VALUES (
    'DOC-001',
    'Dr. Sarah Johnson',
    'sarah@gmail.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password (change this!)
    'Cardiology',
    1500,
    2500
);

-- Note: After importing, go to doctor/login.php and login with:
-- Email: sarah@gmail.com  |  Password: Sarah123
-- Then update the password hash using: echo password_hash('Sarah123', PASSWORD_DEFAULT);

-- =============================================
-- MORE SAMPLE DOCTORS (so patient search has results to find)
-- password for all of the below: Doctor@123
-- =============================================
INSERT IGNORE INTO doctors (doctor_id, full_name, email, password, specialty, virtual_fee, physical_fee, available_days, available_time) VALUES (
    'DOC-002', 'Dr. Ravi Kumar', 'ravi.kumar@sajeefa.com',
    '$2y$10$TfJADP2ysKhWtRkLEP72qO5HS.fiSf3JBx.rnIQi4Uuo34/nQh1f2',
    'Dermatology', 1800, 2800, 'Monday - Saturday', '09:00 AM - 05:00 PM'
);

INSERT IGNORE INTO doctors (doctor_id, full_name, email, password, specialty, virtual_fee, physical_fee, available_days, available_time) VALUES (
    'DOC-003', 'Dr. Anjali Perera', 'anjali.perera@sajeefa.com',
    '$2y$10$TfJADP2ysKhWtRkLEP72qO5HS.fiSf3JBx.rnIQi4Uuo34/nQh1f2',
    'Pediatrics', 1200, 2000, 'Monday - Friday', '08:00 AM - 04:00 PM'
);

INSERT IGNORE INTO doctors (doctor_id, full_name, email, password, specialty, virtual_fee, physical_fee, available_days, available_time) VALUES (
    'DOC-004', 'Dr. Mohamed Farook', 'mohamed.farook@sajeefa.com',
    '$2y$10$TfJADP2ysKhWtRkLEP72qO5HS.fiSf3JBx.rnIQi4Uuo34/nQh1f2',
    'Orthopedics', 2000, 3000, 'Tuesday - Saturday', '10:00 AM - 06:00 PM'
);

INSERT IGNORE INTO doctors (doctor_id, full_name, email, password, specialty, virtual_fee, physical_fee, available_days, available_time) VALUES (
    'DOC-005', 'Dr. Nisha Fernando', 'nisha.fernando@sajeefa.com',
    '$2y$10$TfJADP2ysKhWtRkLEP72qO5HS.fiSf3JBx.rnIQi4Uuo34/nQh1f2',
    'ENT (Ear, Nose & Throat)', 1600, 2600, 'Monday - Friday', '09:00 AM - 05:00 PM'
);

INSERT IGNORE INTO doctors (doctor_id, full_name, email, password, specialty, virtual_fee, physical_fee, available_days, available_time) VALUES (
    'DOC-006', 'Dr. Arjun Patel', 'arjun.patel@sajeefa.com',
    '$2y$10$TfJADP2ysKhWtRkLEP72qO5HS.fiSf3JBx.rnIQi4Uuo34/nQh1f2',
    'Neurology', 2200, 3500, 'Monday, Wednesday, Friday', '11:00 AM - 07:00 PM'
);
-- Login for any of the doctors above: <their email> / Doctor@123
