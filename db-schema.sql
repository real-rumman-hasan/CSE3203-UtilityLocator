 CREATE DATABASE IF NOT EXISTS utility_locator CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE utility_locator;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  f_name VARCHAR(80) NOT NULL,
  l_name VARCHAR(80) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  phone VARCHAR(20) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin', 'customer', 'provider') NOT NULL DEFAULT 'customer',
  postal_code VARCHAR(20) DEFAULT NULL,
  district VARCHAR(100) DEFAULT NULL,
  area VARCHAR(120) DEFAULT NULL,
  lat DECIMAL(10,7) DEFAULT NULL,
  lng DECIMAL(10,7) DEFAULT NULL,
  image VARCHAR(255) DEFAULT NULL,
  is_verified TINYINT(1) NOT NULL DEFAULT 0,
  remember_token VARCHAR(64) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_users_role_verified (role, is_verified),
  INDEX idx_users_location (district, postal_code)
);

CREATE TABLE services (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL UNIQUE,
  `desc` TEXT NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE provider_services (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  provider_id INT UNSIGNED NOT NULL,
  service_id INT UNSIGNED NOT NULL,
  custom_price DECIMAL(10,2) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_provider_service (provider_id, service_id),
  CONSTRAINT fk_provider_services_provider FOREIGN KEY (provider_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_provider_services_service FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
);

CREATE TABLE bookings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_id INT UNSIGNED NOT NULL,
  provider_id INT UNSIGNED NOT NULL,
  service_id INT UNSIGNED NOT NULL,
  status ENUM('awaiting_assignment', 'pending', 'confirmed', 'cancelled', 'completed', 'expired', 'rejected') NOT NULL DEFAULT 'awaiting_assignment',
  payment_status ENUM('pending', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
  message TEXT DEFAULT NULL,
  transaction_id VARCHAR(100) DEFAULT NULL,
  expires_at DATETIME DEFAULT NULL,
  confirmed_at DATETIME DEFAULT NULL,
  assigned_by_admin_id INT UNSIGNED DEFAULT NULL,
  assigned_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_bookings_provider_status (provider_id, status),
  INDEX idx_bookings_customer_status (customer_id, status),
  CONSTRAINT fk_bookings_customer FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_bookings_provider FOREIGN KEY (provider_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_bookings_service FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
  CONSTRAINT fk_bookings_admin FOREIGN KEY (assigned_by_admin_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE reviews (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  booking_id INT UNSIGNED NOT NULL UNIQUE,
  provider_id INT UNSIGNED NOT NULL,
  customer_id INT UNSIGNED NOT NULL,
  rating TINYINT UNSIGNED NOT NULL CHECK (rating BETWEEN 1 AND 5),
  review_text VARCHAR(500) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_reviews_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
  CONSTRAINT fk_reviews_provider FOREIGN KEY (provider_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_reviews_customer FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT INTO services (name, `desc`, price) VALUES
('Gas', 'Gas line installation, burner checks, and urgent leak support.', 900.00),
('Sanitary', 'Drain cleaning, pipe repair, and bathroom fixture maintenance.', 800.00),
('Electrical', 'Safe wiring, switch repair, and home electrical troubleshooting.', 1200.00),
('Shifting', 'Home and office moving support with packing assistance.', 2500.00),
('Locksmith', 'Door lock repair, key duplication, and emergency lock opening.', 700.00)
ON DUPLICATE KEY UPDATE
  `desc` = VALUES(`desc`),
  price = VALUES(price);

INSERT INTO users (
  f_name,
  l_name,
  email,
  phone,
  password,
  role,
  postal_code,
  district,
  area,
  lat,
  lng,
  image,
  is_verified
) VALUES (
  'System',
  'Admin',
  'admin@utilitylocator.local',
  '8801000000000',
  '$2y$10$MBGP9iC621ZErP9lfBcdDe8D/hIQIrPtWdkRuvQ5Swgs4x9THjBy.',
  'admin',
  '1000',
  'Dhaka',
  'Dhanmondi',
  23.8103000,
  90.4125000,
  NULL,
  1
)
ON DUPLICATE KEY UPDATE
  role = 'admin',
  is_verified = 1;

INSERT INTO users (
  f_name,
  l_name,
  email,
  phone,
  password,
  role,
  postal_code,
  district,
  area,
  lat,
  lng,
  image,
  is_verified
) VALUES
('Abdur', 'Karim', 'abdul.karim@utilitylocator.local', '8801700000001', '$2y$10$P8ckJVYuM5ID6g6Q/C37fuUU476UaC3/a7H/epMpv3L8.yr9jtVly', 'provider', '1207', 'Dhaka', 'Lalbagh', NULL, NULL, 'uploads/providers/abdul-karim.jpg', 1),
('Rakib', 'Hasan', 'rakib.hasan@utilitylocator.local', '8801700000002', '$2y$10$P8ckJVYuM5ID6g6Q/C37fuUU476UaC3/a7H/epMpv3L8.yr9jtVly', 'provider', '1216', 'Dhaka', 'Mohammadpur', NULL, NULL, 'uploads/providers/rakib-hasan.jpg', 1),
('Sharmin', 'Sultana', 'sharmin.sultana@utilitylocator.local', '8801700000003', '$2y$10$P8ckJVYuM5ID6g6Q/C37fuUU476UaC3/a7H/epMpv3L8.yr9jtVly', 'provider', '1212', 'Dhaka', 'Dhanmondi', NULL, NULL, 'uploads/providers/sharmin-sultana.jpg', 1),
('Tanvir', 'Ahmed', 'tanvir.ahmed@utilitylocator.local', '8801700000004', '$2y$10$P8ckJVYuM5ID6g6Q/C37fuUU476UaC3/a7H/epMpv3L8.yr9jtVly', 'provider', '1229', 'Dhaka', 'Badda', NULL, NULL, 'uploads/providers/tanvir-ahmed.jpg', 1),
('Nasir', 'Uddin', 'nasir.uddin@utilitylocator.local', '8801700000005', '$2y$10$P8ckJVYuM5ID6g6Q/C37fuUU476UaC3/a7H/epMpv3L8.yr9jtVly', 'provider', '1230', 'Dhaka', 'Uttara', NULL, NULL, 'uploads/providers/nasir-uddin.jpg', 1),
('Jahidul', 'Islam', 'jahidul.islam@utilitylocator.local', '8801700000006', '$2y$10$P8ckJVYuM5ID6g6Q/C37fuUU476UaC3/a7H/epMpv3L8.yr9jtVly', 'provider', '1205', 'Dhaka', 'Shyamoli', NULL, NULL, 'uploads/providers/jahidul-islam.jpg', 1),
('Rubel', 'Mia', 'rubel.mia@utilitylocator.local', '8801700000007', '$2y$10$P8ckJVYuM5ID6g6Q/C37fuUU476UaC3/a7H/epMpv3L8.yr9jtVly', 'provider', '1215', 'Dhaka', 'Malibagh', NULL, NULL, 'uploads/providers/rubel-mia.jpg', 1),
('Farhana', 'Yasmin', 'farhana.yasmin@utilitylocator.local', '8801700000008', '$2y$10$P8ckJVYuM5ID6g6Q/C37fuUU476UaC3/a7H/epMpv3L8.yr9jtVly', 'provider', '1213', 'Dhaka', 'Rampura', NULL, NULL, 'uploads/providers/farhana-yasmin.jpg', 1),
('Sohel', 'Rana', 'sohel.rana@utilitylocator.local', '8801700000009', '$2y$10$P8ckJVYuM5ID6g6Q/C37fuUU476UaC3/a7H/epMpv3L8.yr9jtVly', 'provider', '1209', 'Dhaka', 'Mirpur', NULL, NULL, 'uploads/providers/sohel-rana.jpg', 1),
('Mahmud', 'Hosen', 'mahmud.hosen@utilitylocator.local', '8801700000010', '$2y$10$P8ckJVYuM5ID6g6Q/C37fuUU476UaC3/a7H/epMpv3L8.yr9jtVly', 'provider', '1206', 'Dhaka', 'Farmgate', NULL, NULL, 'uploads/providers/mahmud-hosen.jpg', 1)
ON DUPLICATE KEY UPDATE
  is_verified = VALUES(is_verified),
  district = VALUES(district),
  postal_code = VALUES(postal_code),
  lat = VALUES(lat),
  lng = VALUES(lng),
  image = VALUES(image);

INSERT INTO provider_services (provider_id, service_id, custom_price)
SELECT u.id, s.id, seeded.custom_price
FROM (
  SELECT 'abdul.karim@utilitylocator.local' AS email, 'Gas' AS service_name, 950.00 AS custom_price
  UNION ALL SELECT 'rakib.hasan@utilitylocator.local', 'Gas', 980.00
  UNION ALL SELECT 'sharmin.sultana@utilitylocator.local', 'Sanitary', 820.00
  UNION ALL SELECT 'tanvir.ahmed@utilitylocator.local', 'Electrical', 1250.00
  UNION ALL SELECT 'nasir.uddin@utilitylocator.local', 'Electrical', 1180.00
  UNION ALL SELECT 'jahidul.islam@utilitylocator.local', 'Shifting', 2600.00
  UNION ALL SELECT 'rubel.mia@utilitylocator.local', 'Shifting', 2450.00
  UNION ALL SELECT 'farhana.yasmin@utilitylocator.local', 'Locksmith', 720.00
  UNION ALL SELECT 'sohel.rana@utilitylocator.local', 'Sanitary', 840.00
  UNION ALL SELECT 'mahmud.hosen@utilitylocator.local', 'Locksmith', 760.00
) AS seeded
INNER JOIN users u ON u.email = seeded.email
INNER JOIN services s ON s.name = seeded.service_name
ON DUPLICATE KEY UPDATE
  custom_price = VALUES(custom_price);
