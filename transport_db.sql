DROP DATABASE IF EXISTS transport_db;
CREATE DATABASE transport_db;
USE transport_db;

CREATE TABLE associations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  association_name VARCHAR(150) NOT NULL UNIQUE,
  contact_person VARCHAR(100) NOT NULL,
  contact_number VARCHAR(30) NOT NULL
);

CREATE TABLE operators (
  id INT AUTO_INCREMENT PRIMARY KEY,
  registration_number VARCHAR(30) NOT NULL UNIQUE,
  full_name VARCHAR(100) NOT NULL,
  contact_number VARCHAR(30) NOT NULL,
  email VARCHAR(100) NULL,
  address VARCHAR(150) NOT NULL,
  association_id INT NULL,
  membership_number VARCHAR(30) NULL,
  member_since DATE NULL,
  date_registered DATE NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'Active',
  FOREIGN KEY (association_id) REFERENCES associations(id)
);

CREATE TABLE vehicles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  plate_number VARCHAR(20) NOT NULL UNIQUE,
  vehicle_type VARCHAR(30) NOT NULL,
  make VARCHAR(50) NOT NULL,
  model VARCHAR(50) NOT NULL,
  year_model INT NOT NULL,
  color VARCHAR(30) NOT NULL,
  capacity INT NOT NULL,
  engine_number VARCHAR(40) NOT NULL,
  chassis_number VARCHAR(40) NOT NULL,
  operator_id INT NOT NULL,
  date_registered DATE NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'Active',
  FOREIGN KEY (operator_id) REFERENCES operators(id)
);

CREATE TABLE routes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  route_code VARCHAR(20) NOT NULL UNIQUE,
  route_name VARCHAR(100) NOT NULL,
  origin VARCHAR(150) NOT NULL,
  destination VARCHAR(150) NOT NULL,
  description TEXT NULL,
  travel_mode VARCHAR(20) NOT NULL DEFAULT 'driving',
  fare DECIMAL(6,2) NOT NULL DEFAULT 0.00,
  status VARCHAR(20) NOT NULL DEFAULT 'Active'
);

CREATE TABLE route_stops (
  id INT AUTO_INCREMENT PRIMARY KEY,
  route_id INT NOT NULL,
  stop_order INT NOT NULL,
  stop_name VARCHAR(150) NOT NULL,
  FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE CASCADE
);

CREATE TABLE assignments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vehicle_id INT NOT NULL,
  operator_id INT NOT NULL,
  route_id INT NOT NULL,
  date_assigned DATE NOT NULL,
  date_ended DATE NULL,
  remarks VARCHAR(255) NULL,
  FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
  FOREIGN KEY (operator_id) REFERENCES operators(id),
  FOREIGN KEY (route_id) REFERENCES routes(id)
);

CREATE TABLE documents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  document_type VARCHAR(50) NOT NULL,
  document_number VARCHAR(50) NOT NULL,
  operator_id INT NULL,
  vehicle_id INT NULL,
  issued_date DATE NOT NULL,
  expiry_date DATE NOT NULL,
  verification_status VARCHAR(20) NOT NULL DEFAULT 'Pending',
  verified_date DATE NULL,
  remarks VARCHAR(255) NULL,
  FOREIGN KEY (operator_id) REFERENCES operators(id),
  FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
);

CREATE TABLE renewals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  document_id INT NOT NULL,
  renewal_date DATE NOT NULL,
  previous_expiry DATE NOT NULL,
  new_expiry DATE NOT NULL,
  remarks VARCHAR(255) NULL,
  FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE
);

CREATE TABLE inspections (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vehicle_id INT NOT NULL,
  schedule_date DATE NOT NULL,
  inspection_date DATE NULL,
  inspector VARCHAR(100) NULL,
  result VARCHAR(20) NOT NULL DEFAULT 'Pending',
  defects TEXT NULL,
  remarks TEXT NULL,
  FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
);

INSERT INTO associations (association_name, contact_person, contact_number) VALUES
('Asin Road Operators Association', 'Pedro Bautista', '09171112222'),
('Tadiangan Transport Cooperative', 'Lorna Dizon', '09183334444');

INSERT INTO operators (registration_number, full_name, contact_number, email, address, association_id, membership_number, member_since, date_registered, status) VALUES
('OPR-2024-001', 'Juan Dela Cruz', '09171234567', 'juan.delacruz@example.com', 'San Luis Village, Baguio City', 1, 'AROA-0012', '2019-03-15', DATE_SUB(CURDATE(), INTERVAL 700 DAY), 'Active'),
('OPR-2024-002', 'Maria Santos', '09189876543', 'maria.santos@example.com', 'Asin Road, Baguio City', 1, 'AROA-0027', '2020-06-01', DATE_SUB(CURDATE(), INTERVAL 650 DAY), 'Active'),
('OPR-2024-003', 'Transit Corp PH', '09224567890', 'info@transitcorp.example.com', 'Session Road, Baguio City', NULL, NULL, NULL, DATE_SUB(CURDATE(), INTERVAL 600 DAY), 'Active'),
('OPR-2025-004', 'Roberto Gomez', '09201112233', NULL, 'Tadiangan, Tuba, Benguet', 2, 'TTC-0105', '2021-01-20', DATE_SUB(CURDATE(), INTERVAL 300 DAY), 'Suspended'),
('OPR-2025-005', 'Elena Reyes', '09325558899', 'elena.reyes@example.com', 'Tadiangan, Tuba, Benguet', 2, 'TTC-0131', '2022-08-10', DATE_SUB(CURDATE(), INTERVAL 120 DAY), 'Active');

INSERT INTO vehicles (plate_number, vehicle_type, make, model, year_model, color, capacity, engine_number, chassis_number, operator_id, date_registered, status) VALUES
('ABC 1234', 'Jeepney', 'Sarao', 'Traditional', 2012, 'Silver', 18, 'ENG-4D56-10021', 'CHS-SAR-88123', 1, DATE_SUB(CURDATE(), INTERVAL 690 DAY), 'Active'),
('XYZ 9876', 'Modern Jeepney', 'Isuzu', 'QKR77', 2021, 'White', 22, 'ENG-4JH1-20011', 'CHS-ISZ-55210', 2, DATE_SUB(CURDATE(), INTERVAL 640 DAY), 'Active'),
('BUS 5421', 'Mini Bus', 'Hino', 'XZU', 2019, 'Blue', 30, 'ENG-N04C-30981', 'CHS-HNO-77302', 3, DATE_SUB(CURDATE(), INTERVAL 590 DAY), 'Active'),
('JEP 8832', 'Jeepney', 'Francisco Motors', 'Traditional', 2010, 'Red', 20, 'ENG-4D56-11872', 'CHS-FMC-33019', 4, DATE_SUB(CURDATE(), INTERVAL 290 DAY), 'Suspended'),
('UVX 3390', 'Modern UV', 'Toyota', 'Hiace Commuter', 2020, 'White', 15, 'ENG-1GD-40552', 'CHS-TYT-66104', 5, DATE_SUB(CURDATE(), INTERVAL 110 DAY), 'Active'),
('JPN 4471', 'Jeepney', 'Sarao', 'Traditional', 2014, 'Green', 22, 'ENG-4D56-12233', 'CHS-SAR-88541', 1, DATE_SUB(CURDATE(), INTERVAL 500 DAY), 'Under Repair'),
('CTY 7712', 'Mini Bus', 'Hino', 'XZU', 2020, 'Blue', 30, 'ENG-N04C-31555', 'CHS-HNO-77488', 3, DATE_SUB(CURDATE(), INTERVAL 400 DAY), 'Active'),
('MUV 1058', 'Modern UV', 'Nissan', 'NV350 Urvan', 2018, 'Gray', 15, 'ENG-YD25-50911', 'CHS-NSN-44120', 2, DATE_SUB(CURDATE(), INTERVAL 380 DAY), 'Active');

INSERT INTO routes (route_code, route_name, origin, destination, description, travel_mode, fare, status) VALUES
('ASN-01', 'Baguio - San Roque Village', 'Baguio City, Benguet', 'San Roque Village, Baguio City', 'Short city route from downtown Baguio to San Roque Village.', 'driving', 13.00, 'Active'),
('ASN-02', 'Baguio - San Luis Village', 'Baguio City, Benguet', 'San Luis Village, Baguio City', 'City route that enters Asin Road and ends at San Luis Village.', 'driving', 13.00, 'Active'),
('ASN-03', 'Baguio - Asin Road', 'Honor''s Ground Hobby and Toy Shop, Baguio', 'Asin Road, Baguio, Benguet', 'Main Asin Road line from downtown Baguio through San Roque and San Luis Village.', 'driving', 15.00, 'Active'),
('ASN-04', 'Baguio - Tadiangan', '7Q82CH7R+QX9', 'Tadiangan, Tuba, Benguet', 'Long route along Asin Road into Tuba, passing the BenCab Museum area.', 'driving', 25.00, 'Active');

INSERT INTO route_stops (route_id, stop_order, stop_name) VALUES
(1, 1, 'San Roque Village, Baguio City'),
(2, 1, 'San Roque Village, Baguio City'),
(2, 2, 'San Luis Village, Baguio City'),
(3, 1, 'San Roque Village, Baguio City'),
(3, 2, 'San Luis Village, Baguio City'),
(4, 1, 'San Luis Village, Baguio City'),
(4, 2, 'Asin Road, Baguio City'),
(4, 3, 'Ifugao Woodcarvers Village, Asin Road'),
(4, 4, 'BenCab Museum, Tuba, Benguet');

INSERT INTO assignments (vehicle_id, operator_id, route_id, date_assigned, date_ended, remarks) VALUES
(1, 1, 2, DATE_SUB(CURDATE(), INTERVAL 680 DAY), DATE_SUB(CURDATE(), INTERVAL 200 DAY), 'Moved to the Asin Road line'),
(1, 1, 3, DATE_SUB(CURDATE(), INTERVAL 200 DAY), NULL, NULL),
(2, 2, 2, DATE_SUB(CURDATE(), INTERVAL 630 DAY), NULL, NULL),
(3, 3, 4, DATE_SUB(CURDATE(), INTERVAL 580 DAY), NULL, NULL),
(4, 4, 4, DATE_SUB(CURDATE(), INTERVAL 280 DAY), DATE_SUB(CURDATE(), INTERVAL 30 DAY), 'Operator suspended'),
(5, 5, 4, DATE_SUB(CURDATE(), INTERVAL 100 DAY), NULL, NULL),
(6, 1, 1, DATE_SUB(CURDATE(), INTERVAL 490 DAY), NULL, NULL),
(7, 3, 3, DATE_SUB(CURDATE(), INTERVAL 390 DAY), NULL, NULL),
(8, 2, 1, DATE_SUB(CURDATE(), INTERVAL 370 DAY), NULL, NULL);

INSERT INTO documents (document_type, document_number, operator_id, vehicle_id, issued_date, expiry_date, verification_status, verified_date, remarks) VALUES
('Franchise (CPC)', 'CPC-2024-0381', 1, NULL, DATE_SUB(CURDATE(), INTERVAL 700 DAY), DATE_ADD(CURDATE(), INTERVAL 480 DAY), 'Verified', DATE_SUB(CURDATE(), INTERVAL 690 DAY), NULL),
('Franchise (CPC)', 'CPC-2023-0192', 2, NULL, DATE_SUB(CURDATE(), INTERVAL 650 DAY), DATE_ADD(CURDATE(), INTERVAL 300 DAY), 'Verified', DATE_SUB(CURDATE(), INTERVAL 640 DAY), NULL),
('Franchise (CPC)', 'CPC-2022-0054', 3, NULL, DATE_SUB(CURDATE(), INTERVAL 1000 DAY), DATE_ADD(CURDATE(), INTERVAL 20 DAY), 'Verified', DATE_SUB(CURDATE(), INTERVAL 990 DAY), 'Renewal needed soon'),
('Franchise (CPC)', 'CPC-2021-0812', 4, NULL, DATE_SUB(CURDATE(), INTERVAL 1100 DAY), DATE_SUB(CURDATE(), INTERVAL 30 DAY), 'Verified', DATE_SUB(CURDATE(), INTERVAL 1090 DAY), 'Expired'),
('Franchise (CPC)', 'CPC-2025-1109', 5, NULL, DATE_SUB(CURDATE(), INTERVAL 120 DAY), DATE_ADD(CURDATE(), INTERVAL 400 DAY), 'Pending', NULL, NULL),
('OR/CR', 'OR-99482103', NULL, 1, DATE_SUB(CURDATE(), INTERVAL 245 DAY), DATE_ADD(CURDATE(), INTERVAL 120 DAY), 'Verified', DATE_SUB(CURDATE(), INTERVAL 240 DAY), NULL),
('OR/CR', 'OR-99482104', NULL, 2, DATE_SUB(CURDATE(), INTERVAL 350 DAY), DATE_ADD(CURDATE(), INTERVAL 15 DAY), 'Verified', DATE_SUB(CURDATE(), INTERVAL 345 DAY), NULL),
('OR/CR', 'OR-99482105', NULL, 3, DATE_SUB(CURDATE(), INTERVAL 340 DAY), DATE_ADD(CURDATE(), INTERVAL 25 DAY), 'Verified', DATE_SUB(CURDATE(), INTERVAL 335 DAY), NULL),
('OR/CR', 'OR-99482106', NULL, 4, DATE_SUB(CURDATE(), INTERVAL 385 DAY), DATE_SUB(CURDATE(), INTERVAL 20 DAY), 'Verified', DATE_SUB(CURDATE(), INTERVAL 380 DAY), NULL),
('OR/CR', 'OR-99482107', NULL, 5, DATE_SUB(CURDATE(), INTERVAL 110 DAY), DATE_ADD(CURDATE(), INTERVAL 255 DAY), 'Pending', NULL, 'Waiting for LTO copy'),
('OR/CR', 'OR-99482108', NULL, 6, DATE_SUB(CURDATE(), INTERVAL 275 DAY), DATE_ADD(CURDATE(), INTERVAL 90 DAY), 'Verified', DATE_SUB(CURDATE(), INTERVAL 270 DAY), NULL),
('OR/CR', 'OR-99482109', NULL, 7, DATE_SUB(CURDATE(), INTERVAL 30 DAY), DATE_ADD(CURDATE(), INTERVAL 335 DAY), 'Verified', DATE_SUB(CURDATE(), INTERVAL 28 DAY), NULL),
('OR/CR', 'OR-99482110', NULL, 8, DATE_SUB(CURDATE(), INTERVAL 370 DAY), DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'Rejected', DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Blurry copy, resubmit'),
('Insurance (CTPL)', 'CTPL-77120', NULL, 1, DATE_SUB(CURDATE(), INTERVAL 300 DAY), DATE_ADD(CURDATE(), INTERVAL 65 DAY), 'Verified', DATE_SUB(CURDATE(), INTERVAL 298 DAY), NULL),
('Insurance (CTPL)', 'CTPL-77121', NULL, 3, DATE_SUB(CURDATE(), INTERVAL 350 DAY), DATE_ADD(CURDATE(), INTERVAL 10 DAY), 'Pending', NULL, NULL);

INSERT INTO renewals (document_id, renewal_date, previous_expiry, new_expiry, remarks) VALUES
(6, DATE_SUB(CURDATE(), INTERVAL 245 DAY), DATE_SUB(CURDATE(), INTERVAL 240 DAY), DATE_ADD(CURDATE(), INTERVAL 120 DAY), 'Yearly LTO renewal'),
(12, DATE_SUB(CURDATE(), INTERVAL 30 DAY), DATE_SUB(CURDATE(), INTERVAL 25 DAY), DATE_ADD(CURDATE(), INTERVAL 335 DAY), 'Yearly LTO renewal');

INSERT INTO inspections (vehicle_id, schedule_date, inspection_date, inspector, result, defects, remarks) VALUES
(1, DATE_SUB(CURDATE(), INTERVAL 60 DAY), DATE_SUB(CURDATE(), INTERVAL 60 DAY), 'Engr. Ramon Cruz', 'Passed', NULL, 'Good condition'),
(2, DATE_SUB(CURDATE(), INTERVAL 45 DAY), DATE_SUB(CURDATE(), INTERVAL 44 DAY), 'Engr. Ramon Cruz', 'Passed', NULL, NULL),
(3, DATE_SUB(CURDATE(), INTERVAL 40 DAY), DATE_SUB(CURDATE(), INTERVAL 40 DAY), 'Engr. Liza Mendoza', 'Failed', 'Worn rear brake pads; left signal light not working', 'Re-inspect after repair'),
(4, DATE_SUB(CURDATE(), INTERVAL 90 DAY), DATE_SUB(CURDATE(), INTERVAL 90 DAY), 'Engr. Liza Mendoza', 'Failed', 'Excessive smoke emission; cracked windshield', 'Vehicle suspended'),
(5, DATE_ADD(CURDATE(), INTERVAL 7 DAY), NULL, NULL, 'Pending', NULL, NULL),
(6, DATE_SUB(CURDATE(), INTERVAL 10 DAY), NULL, NULL, 'Pending', NULL, 'Vehicle under repair'),
(7, DATE_SUB(CURDATE(), INTERVAL 20 DAY), DATE_SUB(CURDATE(), INTERVAL 20 DAY), 'Engr. Ramon Cruz', 'Passed', NULL, NULL),
(8, DATE_ADD(CURDATE(), INTERVAL 14 DAY), NULL, NULL, 'Pending', NULL, NULL),
(3, DATE_ADD(CURDATE(), INTERVAL 3 DAY), NULL, NULL, 'Pending', NULL, 'Re-inspection');
