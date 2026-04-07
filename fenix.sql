-- ============================================================
-- FENIX CAR HIRE — Full Database Setup
-- Import this in phpMyAdmin after creating fenix_db
-- ============================================================

CREATE DATABASE IF NOT EXISTS fenix_db;
USE fenix_db;

-- ── ADMIN USERS ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
-- Login: admin / fenix2026
INSERT INTO admin_users (username, password, full_name) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uXV/WuBiK', 'Fenix Admin');

-- ── VEHICLES ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category ENUM('SUV','Sedan','Double Cab','Single Cab','Van') NOT NULL,
    plate VARCHAR(20) NOT NULL UNIQUE,
    seats INT DEFAULT 5,
    transmission ENUM('Automatic','Manual') DEFAULT 'Automatic',
    fuel ENUM('Petrol','Diesel') DEFAULT 'Petrol',
    mileage INT DEFAULT 0,
    status ENUM('available','booked','maintenance') DEFAULT 'available',
    image VARCHAR(255) DEFAULT 'default.jpg',
    notes TEXT,
    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO vehicles (name, category, plate, seats, transmission, fuel, mileage, status) VALUES
('Toyota Fortuner',      'SUV',        'MK70HF8P',  7,  'Automatic', 'Diesel', 26552, 'available'),
('Hyundai Staria',       'Van',        'ML08AZGP',  11, 'Automatic', 'Diesel', 45200, 'available'),
('Toyota Corolla Cross', 'SUV',        'MK29PDGP',  5,  'Automatic', 'Petrol', 18300, 'available'),
('Kia Pegas Sedan',      'Sedan',      'SY77KZGP',  5,  'Automatic', 'Petrol', 9800,  'available'),
('Toyota Hilux D/Cab',   'Double Cab', 'NK82DJGP',  5,  'Manual',    'Diesel', 32400, 'available'),
('Isuzu D-Max 4x4',      'Double Cab', 'KDG014EC',  5,  'Manual',    'Diesel', 41000, 'available'),
('VW Polo Starlet',      'Sedan',      'AN60XPGP',  5,  'Automatic', 'Petrol', 15600, 'available'),
('Toyota Quantum',       'Van',        'NK82XPGP',  15, 'Manual',    'Diesel', 88000, 'available');

-- ── BOOKINGS ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_ref VARCHAR(20) UNIQUE,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100),
    customer_phone VARCHAR(30) NOT NULL,
    vehicle_id INT,
    pickup_date DATE NOT NULL,
    return_date DATE,
    mileage_out INT,
    mileage_in INT,
    excess_kms INT DEFAULT 0,
    amount_paid DECIMAL(10,2) DEFAULT 0,
    amount_pending DECIMAL(10,2) DEFAULT 0,
    bad_debt DECIMAL(10,2) DEFAULT 0,
    status ENUM('pending','active','completed','cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL
);
INSERT INTO bookings (booking_ref, customer_name, customer_phone, vehicle_id, pickup_date, return_date, mileage_out, mileage_in, amount_paid, status) VALUES
('BK-001', 'Ekhaya Funeral', '78068407', 2, '2026-03-09', '2026-03-10', 26354, 26552, 7798.15, 'completed'),
('BK-002', 'Wandile Maseko', '76029500', 1, '2026-03-06', '2026-03-13', 24800, NULL,  16385.00,'active');

-- ── INVOICES ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_no VARCHAR(20) UNIQUE,
    type ENUM('invoice','quotation') DEFAULT 'invoice',
    booking_id INT,
    customer_name VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(30),
    vehicle_name VARCHAR(100),
    rate_per_day DECIMAL(10,2) DEFAULT 0,
    quantity INT DEFAULT 1,
    days INT DEFAULT 1,
    kms_free_per_day INT DEFAULT 200,
    contract_fee DECIMAL(10,2) DEFAULT 200.00,
    excess_kms INT DEFAULT 0,
    excess_rate DECIMAL(10,2) DEFAULT 11.00,
    excess_total DECIMAL(10,2) DEFAULT 0,
    deposit DECIMAL(10,2) DEFAULT 0,
    subtotal DECIMAL(10,2) DEFAULT 0,
    vat DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) DEFAULT 0,
    status ENUM('pending','paid','cancelled') DEFAULT 'pending',
    invoice_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL
);
INSERT INTO invoices (invoice_no, type, customer_name, vehicle_name, rate_per_day, quantity, days, contract_fee, excess_kms, excess_total, subtotal, vat, total, status, invoice_date) VALUES
('Inv-06','invoice',  'Ekhaya Funeral','Staria x2',1800.00,2,1,200.00,271,2981.00,6781.00,1017.15,7798.15,'paid','2026-03-09'),
('Quo-01','quotation','Wandile Maseko','Fortuner', 1400.00,1,7,100.00,0,  0,      14900.00,1485.00,16385.00,'paid','2026-03-06');

-- ── CHECK SHEETS ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS checksheets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT,
    vehicle_id INT,
    plate VARCHAR(20),
    customer_name VARCHAR(100),
    mileage_out INT,
    mileage_in INT,
    fuel_out ENUM('E','1/4','1/2','3/4','Full') DEFAULT 'Full',
    fuel_in  ENUM('E','1/4','1/2','3/4','Full') DEFAULT 'Full',
    pre_windscreen TINYINT(1) DEFAULT 1,
    pre_headlights TINYINT(1) DEFAULT 1,
    pre_taillights TINYINT(1) DEFAULT 1,
    pre_mirrors    TINYINT(1) DEFAULT 1,
    pre_hub_caps   TINYINT(1) DEFAULT 1,
    pre_spare      TINYINT(1) DEFAULT 1,
    pre_jack       TINYINT(1) DEFAULT 1,
    pre_triangle   TINYINT(1) DEFAULT 1,
    pre_aerial     TINYINT(1) DEFAULT 1,
    pre_radio      TINYINT(1) DEFAULT 1,
    pre_mats       TINYINT(1) DEFAULT 1,
    pre_carpets    TINYINT(1) DEFAULT 1,
    post_windscreen TINYINT(1) DEFAULT 1,
    post_headlights TINYINT(1) DEFAULT 1,
    post_taillights TINYINT(1) DEFAULT 1,
    post_mirrors    TINYINT(1) DEFAULT 1,
    post_hub_caps   TINYINT(1) DEFAULT 1,
    post_spare      TINYINT(1) DEFAULT 1,
    post_jack       TINYINT(1) DEFAULT 1,
    post_triangle   TINYINT(1) DEFAULT 1,
    post_aerial     TINYINT(1) DEFAULT 1,
    post_radio      TINYINT(1) DEFAULT 1,
    post_mats       TINYINT(1) DEFAULT 1,
    post_carpets    TINYINT(1) DEFAULT 1,
    damage_notes TEXT,
    checked_by_out VARCHAR(100),
    checked_by_in  VARCHAR(100),
    renter_name VARCHAR(100),
    date_out DATE,
    date_in  DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL
);

-- ── NOTIFICATIONS ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message TEXT NOT NULL,
    type ENUM('booking','payment','system') DEFAULT 'booking',
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO notifications (message, type, is_read) VALUES
('New booking from Wandile Maseko — Fortuner', 'booking', 0),
('Invoice Inv-06 paid — E7,798.15', 'payment', 1);
