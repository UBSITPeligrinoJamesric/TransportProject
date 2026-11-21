DROP DATABASE IF EXISTS transport_db;
CREATE DATABASE transport_db;
USE transport_db;

CREATE TABLE associations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  association_name VARCHAR(150) NOT NULL UNIQUE,
  contact_person VARCHAR(100) NOT NULL,
  contact_number VARCHAR(30) NOT NULL
);

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(100) NULL,
  contact_number VARCHAR(30) NULL,
  password VARCHAR(255) NOT NULL,
  role VARCHAR(20) NOT NULL DEFAULT 'Passenger',
  date_registered DATE NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'Active',
  must_change_password TINYINT(1) NOT NULL DEFAULT 0
);

CREATE TABLE login_attempts (
  login_key VARCHAR(100) PRIMARY KEY,
  attempts INT NOT NULL DEFAULT 0,
  last_attempt DATETIME NOT NULL,
  locked_until DATETIME NULL
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
  driver_id INT NULL,
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

CREATE TABLE complaints (
  id INT AUTO_INCREMENT PRIMARY KEY,
  complaint_number VARCHAR(30) NOT NULL UNIQUE,
  user_id INT NULL,
  complainant_name VARCHAR(100) NOT NULL,
  contact_number VARCHAR(30) NULL,
  complaint_type VARCHAR(30) NOT NULL,
  concern VARCHAR(100) NOT NULL,
  description TEXT NOT NULL,
  vehicle_id INT NULL,
  operator_id INT NULL,
  route_id INT NULL,
  incident_date DATE NOT NULL,
  incident_location VARCHAR(150) NULL,
  date_filed DATETIME NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'Pending',
  date_closed DATE NULL,
  mediation_officer VARCHAR(100) NULL,
  respondent VARCHAR(100) NULL,
  account_deleted TINYINT(1) NOT NULL DEFAULT 0,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL,
  FOREIGN KEY (operator_id) REFERENCES operators(id) ON DELETE SET NULL,
  FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE SET NULL
);

CREATE TABLE complaint_actions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  complaint_id INT NOT NULL,
  action_date DATETIME NOT NULL,
  status VARCHAR(30) NOT NULL,
  action_taken TEXT NOT NULL,
  handled_by VARCHAR(100) NOT NULL,
  FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE
);

CREATE TABLE drivers (
  driver_id INT AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(50) NOT NULL,
  middle_name VARCHAR(50) NULL,
  last_name VARCHAR(50) NOT NULL,
  license_number VARCHAR(20) NOT NULL UNIQUE,
  license_type VARCHAR(30) NOT NULL DEFAULT 'Professional',
  license_expiry DATE NOT NULL,
  contact_number VARCHAR(30) NOT NULL,
  operator_id INT NULL,
  vehicle_id INT NULL,
  date_registered DATE NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'Active',
  user_id INT NULL,
  FOREIGN KEY (operator_id) REFERENCES operators(id),
  FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

ALTER TABLE assignments ADD FOREIGN KEY (driver_id) REFERENCES drivers(driver_id) ON DELETE SET NULL;

CREATE TABLE driver_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  drive_date DATE NOT NULL,
  vehicle_id INT NOT NULL,
  driver_id INT NOT NULL,
  route_id INT NULL,
  remarks VARCHAR(255) NULL,
  fee DECIMAL(8,2) NOT NULL DEFAULT 100.00,
  fee_paid TINYINT(1) NOT NULL DEFAULT 0,
  paid_date DATE NULL,
  or_number VARCHAR(40) NULL,
  UNIQUE KEY one_driver_per_unit_per_day (vehicle_id, drive_date),
  UNIQUE KEY one_unit_per_driver_per_day (driver_id, drive_date),
  FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
  FOREIGN KEY (driver_id) REFERENCES drivers(driver_id) ON DELETE CASCADE
,
  FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE SET NULL
);

CREATE TABLE driver_expenses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  driver_id INT NOT NULL,
  expense_date DATE NOT NULL,
  amount DECIMAL(8,2) NOT NULL,
  reason VARCHAR(255) NOT NULL,
  recorded_by VARCHAR(100) NOT NULL,
  date_recorded DATETIME NOT NULL,
  updated_by VARCHAR(100) NULL,
  date_updated DATETIME NULL,
  previous_amount DECIMAL(8,2) NULL,
  FOREIGN KEY (driver_id) REFERENCES drivers(driver_id) ON DELETE CASCADE
);

CREATE TABLE log_book_days (
  drive_date DATE PRIMARY KEY,
  copied_from DATE NULL
);

CREATE TABLE violations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ticket_number VARCHAR(40) NOT NULL,
  violation_kind VARCHAR(10) NOT NULL,
  agency VARCHAR(10) NOT NULL,
  vehicle_id INT NULL,
  operator_id INT NULL,
  driver_id INT NULL,
  violation VARCHAR(150) NOT NULL,
  violation_date DATE NOT NULL,
  location VARCHAR(150) NULL,
  fine DECIMAL(10,2) NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'Pending',
  date_settled DATE NULL,
  or_number VARCHAR(40) NULL,
  license_action VARCHAR(60) NULL,
  permit_valid_until DATE NULL,
  complaint_id INT NULL,
  remarks TEXT NULL,
  date_recorded DATETIME NOT NULL,
  UNIQUE KEY one_ticket_per_agency (agency, ticket_number),
  FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL,
  FOREIGN KEY (operator_id) REFERENCES operators(id) ON DELETE SET NULL,
  FOREIGN KEY (driver_id) REFERENCES drivers(driver_id) ON DELETE SET NULL,
  FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE SET NULL
);


INSERT INTO associations (association_name, contact_person, contact_number) VALUES
('Asin Transport', 'Pedro Bautista', '09171112222'),
('San Luis Transport', 'Lorna Dizon', '09183334444');

INSERT INTO operators (registration_number, full_name, contact_number, email, address, association_id, membership_number, member_since, date_registered, status) VALUES
('OPR-2024-001', 'Juan Dela Cruz', '09171234567', 'juan.delacruz@example.com', 'San Luis Village, Baguio City', 1, 'AT-0012', '2019-03-15', DATE_SUB(CURDATE(), INTERVAL 700 DAY), 'Active'),
('OPR-2024-002', 'Maria Santos', '09189876543', 'maria.santos@example.com', 'Asin Road, Baguio City', 1, 'AT-0027', '2020-06-01', DATE_SUB(CURDATE(), INTERVAL 650 DAY), 'Active'),
('OPR-2025-003', 'Roberto Gomez', '09201112233', NULL, 'San Luis Village, Baguio City', 2, 'SLT-0105', '2021-01-20', DATE_SUB(CURDATE(), INTERVAL 300 DAY), 'Suspended'),
('OPR-2025-004', 'Elena Reyes', '09325558899', 'elena.reyes@example.com', 'San Luis Village, Baguio City', 2, 'SLT-0131', '2022-08-10', DATE_SUB(CURDATE(), INTERVAL 120 DAY), 'Active');

INSERT INTO vehicles (plate_number, vehicle_type, make, model, year_model, color, capacity, engine_number, chassis_number, operator_id, date_registered, status) VALUES
('ABC 1234', 'Jeepney', 'Sarao', 'Traditional', 2012, 'Silver', 18, 'ENG-4D56-10021', 'CHS-SAR-88123', 1, DATE_SUB(CURDATE(), INTERVAL 690 DAY), 'Active'),
('JPN 4471', 'Jeepney', 'Sarao', 'Traditional', 2014, 'Green', 22, 'ENG-4D56-12233', 'CHS-SAR-88541', 1, DATE_SUB(CURDATE(), INTERVAL 500 DAY), 'Under Repair'),
('XYZ 9876', 'Modern UV', 'Toyota', 'Hiace Commuter', 2021, 'White', 15, 'ENG-1GD-20011', 'CHS-TYT-55210', 2, DATE_SUB(CURDATE(), INTERVAL 640 DAY), 'Active'),
('MUV 1058', 'Modern UV', 'Nissan', 'NV350 Urvan', 2018, 'Gray', 15, 'ENG-YD25-50911', 'CHS-NSN-44120', 2, DATE_SUB(CURDATE(), INTERVAL 380 DAY), 'Active'),
('JEP 8832', 'Jeepney', 'Francisco Motors', 'Traditional', 2010, 'Red', 20, 'ENG-4D56-11872', 'CHS-FMC-33019', 3, DATE_SUB(CURDATE(), INTERVAL 290 DAY), 'Suspended'),
('UVX 3390', 'Modern UV', 'Toyota', 'Hiace Commuter', 2020, 'White', 15, 'ENG-1GD-40552', 'CHS-TYT-66104', 4, DATE_SUB(CURDATE(), INTERVAL 110 DAY), 'Active'),
('EVJ 2025', 'E-Vehicle', 'Star 8', 'E-Jeepney', 2024, 'Yellow', 20, 'MTR-E8-70031', 'CHS-ST8-91022', 4, DATE_SUB(CURDATE(), INTERVAL 60 DAY), 'Active'),
('EVX 3051', 'E-Vehicle', 'Star 8', 'E-Jeepney', 2025, 'Yellow', 20, 'MTR-E8-70388', 'CHS-ST8-91460', 4, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Pending');

INSERT INTO routes (route_code, route_name, origin, destination, description, travel_mode, fare, status) VALUES
('ASN-01', 'Asin Road - Long Route', 'Honor''s Ground Hobby and Toy Shop, Baguio', 'Km. 6, Asin Road, Baguio City', 'The longest Asin Road trip. Starts in downtown Baguio, passes San Roque Village, San Luis Village and the Ifugao Woodcarvers Village, and ends at Km. 6 Asin Road. Fare is P18.00.', 'driving', 18.00, 'Active'),
('ASN-02', 'Asin Road - Short Route', 'Honor''s Ground Hobby and Toy Shop, Baguio', 'Ifugao Woodcarvers Village, Asin Road, Baguio City', 'The shorter Asin Road trip. Starts in downtown Baguio, passes San Roque Village and San Luis Village, and turns back at the Ifugao Woodcarvers Village before Km. 6. Fare is P15.00.', 'driving', 15.00, 'Active'),
('SNL-01', 'San Luis Road', 'Honor''s Ground Hobby and Toy Shop, Baguio', 'San Luis Village, Baguio City', 'City trip from downtown Baguio through San Roque Village, ending at San Luis Village. It does not continue to the rest of Asin Road. Fare is P14.00.', 'driving', 14.00, 'Active');

INSERT INTO route_stops (route_id, stop_order, stop_name) VALUES
(1, 1, 'San Roque Village, Baguio City'),
(1, 2, 'San Luis Village, Baguio City'),
(1, 3, 'Ifugao Woodcarvers Village, Asin Road'),
(2, 1, 'San Roque Village, Baguio City'),
(2, 2, 'San Luis Village, Baguio City'),
(3, 1, 'San Roque Village, Baguio City');

INSERT INTO drivers (first_name, middle_name, last_name, license_number, license_type, license_expiry, contact_number, operator_id, vehicle_id, date_registered, status) VALUES
('Pedro', 'Reyes', 'Aquino', 'N01-12-345678', 'Professional', DATE_ADD(CURDATE(), INTERVAL 700 DAY), '09171230001', 1, 1, DATE_SUB(CURDATE(), INTERVAL 400 DAY), 'Active'),
('Jose', 'Mendoza', 'Ramos', 'N02-15-456789', 'Professional', DATE_ADD(CURDATE(), INTERVAL 20 DAY), '09181230002', 1, 2, DATE_SUB(CURDATE(), INTERVAL 350 DAY), 'Active'),
('Mark Anthony', 'Torres', 'Lopez', 'N03-18-567890', 'Professional', DATE_ADD(CURDATE(), INTERVAL 500 DAY), '09191230003', 2, 3, DATE_SUB(CURDATE(), INTERVAL 600 DAY), 'Active'),
('Ramil', 'Castro', 'Dizon', 'N04-20-678901', 'Professional', DATE_ADD(CURDATE(), INTERVAL 900 DAY), '09201230004', 4, 6, DATE_SUB(CURDATE(), INTERVAL 100 DAY), 'Active');

INSERT INTO assignments (vehicle_id, operator_id, driver_id, route_id, date_assigned, date_ended, remarks) VALUES
(1, 1, 1, 3, DATE_SUB(CURDATE(), INTERVAL 680 DAY), DATE_SUB(CURDATE(), INTERVAL 200 DAY), 'Moved to the Asin Road - Short Route'),
(1, 1, 1, 2, DATE_SUB(CURDATE(), INTERVAL 200 DAY), NULL, NULL),
(2, 1, NULL, 3, DATE_SUB(CURDATE(), INTERVAL 490 DAY), NULL, 'Under repair, no driver for now'),
(3, 2, 3, 3, DATE_SUB(CURDATE(), INTERVAL 630 DAY), NULL, NULL),
(4, 2, NULL, 3, DATE_SUB(CURDATE(), INTERVAL 370 DAY), NULL, NULL),
(5, 3, NULL, 1, DATE_SUB(CURDATE(), INTERVAL 280 DAY), DATE_SUB(CURDATE(), INTERVAL 30 DAY), 'Operator suspended'),
(6, 4, 4, 1, DATE_SUB(CURDATE(), INTERVAL 100 DAY), NULL, NULL),
(7, 4, 2, 2, DATE_SUB(CURDATE(), INTERVAL 55 DAY), NULL, 'Extra driver');

INSERT INTO documents (document_type, document_number, operator_id, vehicle_id, issued_date, expiry_date, verification_status, verified_date, remarks) VALUES
('Franchise (CPC)', 'CPC-2024-0381', 1, NULL, DATE_SUB(CURDATE(), INTERVAL 700 DAY), DATE_ADD(CURDATE(), INTERVAL 480 DAY), 'Verified', DATE_SUB(CURDATE(), INTERVAL 690 DAY), NULL),
('Franchise (CPC)', 'CPC-2023-0192', 2, NULL, DATE_SUB(CURDATE(), INTERVAL 650 DAY), DATE_ADD(CURDATE(), INTERVAL 300 DAY), 'Verified', DATE_SUB(CURDATE(), INTERVAL 640 DAY), NULL),
('Franchise (CPC)', 'CPC-2021-0812', 3, NULL, DATE_SUB(CURDATE(), INTERVAL 1100 DAY), DATE_SUB(CURDATE(), INTERVAL 30 DAY), 'Verified', DATE_SUB(CURDATE(), INTERVAL 1090 DAY), 'Expired'),
('Franchise (CPC)', 'CPC-2025-1109', 4, NULL, DATE_SUB(CURDATE(), INTERVAL 120 DAY), DATE_ADD(CURDATE(), INTERVAL 400 DAY), 'Verified', DATE_SUB(CURDATE(), INTERVAL 115 DAY), NULL),
('OR/CR', 'OR-99482103', NULL, 1, DATE_SUB(CURDATE(), INTERVAL 245 DAY), DATE_ADD(CURDATE(), INTERVAL 120 DAY), 'Verified', DATE_SUB(CURDATE(), INTERVAL 240 DAY), NULL),
('OR/CR', 'OR-99482108', NULL, 2, DATE_SUB(CURDATE(), INTERVAL 275 DAY), DATE_ADD(CURDATE(), INTERVAL 90 DAY), 'Verified', DATE_SUB(CURDATE(), INTERVAL 270 DAY), NULL),
('OR/CR', 'OR-99482104', NULL, 3, DATE_SUB(CURDATE(), INTERVAL 350 DAY), DATE_ADD(CURDATE(), INTERVAL 15 DAY), 'Verified', DATE_SUB(CURDATE(), INTERVAL 345 DAY), NULL),
('OR/CR', 'OR-99482110', NULL, 4, DATE_SUB(CURDATE(), INTERVAL 370 DAY), DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'Rejected', DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Blurry copy, resubmit'),
('OR/CR', 'OR-99482106', NULL, 5, DATE_SUB(CURDATE(), INTERVAL 385 DAY), DATE_SUB(CURDATE(), INTERVAL 20 DAY), 'Verified', DATE_SUB(CURDATE(), INTERVAL 380 DAY), NULL),
('OR/CR', 'OR-99482107', NULL, 6, DATE_SUB(CURDATE(), INTERVAL 110 DAY), DATE_ADD(CURDATE(), INTERVAL 255 DAY), 'Verified', DATE_SUB(CURDATE(), INTERVAL 105 DAY), NULL),
('OR/CR', 'OR-99482109', NULL, 7, DATE_SUB(CURDATE(), INTERVAL 60 DAY), DATE_ADD(CURDATE(), INTERVAL 305 DAY), 'Verified', DATE_SUB(CURDATE(), INTERVAL 58 DAY), NULL),
('OR/CR', 'OR-99482111', NULL, 8, DATE_SUB(CURDATE(), INTERVAL 1 DAY), DATE_ADD(CURDATE(), INTERVAL 364 DAY), 'Pending', NULL, 'Submitted at the transport office'),
('Insurance (CTPL)', 'CTPL-77120', NULL, 1, DATE_SUB(CURDATE(), INTERVAL 300 DAY), DATE_ADD(CURDATE(), INTERVAL 65 DAY), 'Verified', DATE_SUB(CURDATE(), INTERVAL 298 DAY), NULL),
('Insurance (CTPL)', 'CTPL-77121', NULL, 3, DATE_SUB(CURDATE(), INTERVAL 350 DAY), DATE_ADD(CURDATE(), INTERVAL 10 DAY), 'Pending', NULL, NULL),
('Insurance (CTPL)', 'CTPL-77122', NULL, 2, DATE_SUB(CURDATE(), INTERVAL 215 DAY), DATE_ADD(CURDATE(), INTERVAL 150 DAY), 'Verified', DATE_SUB(CURDATE(), INTERVAL 213 DAY), NULL),
('Insurance (CTPL)', 'CTPL-77123', NULL, 4, DATE_SUB(CURDATE(), INTERVAL 377 DAY), DATE_SUB(CURDATE(), INTERVAL 12 DAY), 'Verified', DATE_SUB(CURDATE(), INTERVAL 375 DAY), NULL),
('Insurance (CTPL)', 'CTPL-77124', NULL, 5, DATE_SUB(CURDATE(), INTERVAL 405 DAY), DATE_SUB(CURDATE(), INTERVAL 40 DAY), 'Verified', DATE_SUB(CURDATE(), INTERVAL 400 DAY), NULL),
('Insurance (CTPL)', 'CTPL-77125', NULL, 6, DATE_SUB(CURDATE(), INTERVAL 165 DAY), DATE_ADD(CURDATE(), INTERVAL 200 DAY), 'Verified', DATE_SUB(CURDATE(), INTERVAL 160 DAY), NULL),
('Insurance (CTPL)', 'CTPL-77126', NULL, 7, DATE_SUB(CURDATE(), INTERVAL 65 DAY), DATE_ADD(CURDATE(), INTERVAL 300 DAY), 'Verified', DATE_SUB(CURDATE(), INTERVAL 60 DAY), NULL);

INSERT INTO renewals (document_id, renewal_date, previous_expiry, new_expiry, remarks) VALUES
(5, DATE_SUB(CURDATE(), INTERVAL 245 DAY), DATE_SUB(CURDATE(), INTERVAL 240 DAY), DATE_ADD(CURDATE(), INTERVAL 120 DAY), 'Yearly LTO renewal'),
(11, DATE_SUB(CURDATE(), INTERVAL 60 DAY), DATE_SUB(CURDATE(), INTERVAL 55 DAY), DATE_ADD(CURDATE(), INTERVAL 305 DAY), 'Yearly LTO renewal');

INSERT INTO inspections (vehicle_id, schedule_date, inspection_date, inspector, result, defects, remarks) VALUES
(1, DATE_SUB(CURDATE(), INTERVAL 60 DAY), DATE_SUB(CURDATE(), INTERVAL 60 DAY), 'Engr. Ramon Cruz', 'Passed', NULL, 'Good condition'),
(3, DATE_SUB(CURDATE(), INTERVAL 45 DAY), DATE_SUB(CURDATE(), INTERVAL 44 DAY), 'Engr. Ramon Cruz', 'Passed', NULL, NULL),
(4, DATE_SUB(CURDATE(), INTERVAL 40 DAY), DATE_SUB(CURDATE(), INTERVAL 40 DAY), 'Engr. Liza Mendoza', 'Failed', 'Worn rear brake pads; left signal light not working', 'Re-inspect after repair'),
(5, DATE_SUB(CURDATE(), INTERVAL 90 DAY), DATE_SUB(CURDATE(), INTERVAL 90 DAY), 'Engr. Liza Mendoza', 'Failed', 'Excessive smoke emission; cracked windshield', 'Vehicle suspended'),
(6, DATE_ADD(CURDATE(), INTERVAL 7 DAY), NULL, NULL, 'Pending', NULL, NULL),
(2, DATE_SUB(CURDATE(), INTERVAL 10 DAY), NULL, NULL, 'Pending', NULL, 'Vehicle under repair'),
(7, DATE_SUB(CURDATE(), INTERVAL 20 DAY), DATE_SUB(CURDATE(), INTERVAL 20 DAY), 'Engr. Ramon Cruz', 'Passed', NULL, 'Battery and motor in good condition'),
(4, DATE_ADD(CURDATE(), INTERVAL 3 DAY), NULL, NULL, 'Pending', NULL, 'Re-inspection');

INSERT INTO driver_logs (drive_date, vehicle_id, driver_id, remarks) VALUES
(DATE_SUB(CURDATE(), INTERVAL 14 DAY), 1, 1, NULL),
(DATE_SUB(CURDATE(), INTERVAL 14 DAY), 3, 3, NULL),
(DATE_SUB(CURDATE(), INTERVAL 14 DAY), 6, 4, NULL),
(DATE_SUB(CURDATE(), INTERVAL 13 DAY), 1, 1, NULL),
(DATE_SUB(CURDATE(), INTERVAL 13 DAY), 3, 3, NULL),
(DATE_SUB(CURDATE(), INTERVAL 13 DAY), 6, 4, NULL),
(DATE_SUB(CURDATE(), INTERVAL 13 DAY), 7, 2, 'Extra driver'),
(DATE_SUB(CURDATE(), INTERVAL 12 DAY), 1, 1, NULL),
(DATE_SUB(CURDATE(), INTERVAL 12 DAY), 6, 4, NULL),
(DATE_SUB(CURDATE(), INTERVAL 11 DAY), 1, 1, NULL),
(DATE_SUB(CURDATE(), INTERVAL 11 DAY), 3, 3, NULL),
(DATE_SUB(CURDATE(), INTERVAL 11 DAY), 6, 4, NULL),
(DATE_SUB(CURDATE(), INTERVAL 11 DAY), 7, 2, 'Extra driver'),
(DATE_SUB(CURDATE(), INTERVAL 10 DAY), 1, 2, 'Relief driver'),
(DATE_SUB(CURDATE(), INTERVAL 10 DAY), 3, 3, NULL),
(DATE_SUB(CURDATE(), INTERVAL 10 DAY), 6, 4, NULL),
(DATE_SUB(CURDATE(), INTERVAL 9 DAY), 1, 1, NULL),
(DATE_SUB(CURDATE(), INTERVAL 9 DAY), 3, 3, NULL),
(DATE_SUB(CURDATE(), INTERVAL 9 DAY), 6, 4, NULL),
(DATE_SUB(CURDATE(), INTERVAL 9 DAY), 7, 2, 'Extra driver'),
(DATE_SUB(CURDATE(), INTERVAL 8 DAY), 1, 1, NULL),
(DATE_SUB(CURDATE(), INTERVAL 8 DAY), 3, 3, NULL),
(DATE_SUB(CURDATE(), INTERVAL 8 DAY), 6, 4, NULL),
(DATE_SUB(CURDATE(), INTERVAL 7 DAY), 1, 2, 'Relief driver'),
(DATE_SUB(CURDATE(), INTERVAL 7 DAY), 3, 3, NULL),
(DATE_SUB(CURDATE(), INTERVAL 7 DAY), 6, 4, NULL),
(DATE_SUB(CURDATE(), INTERVAL 6 DAY), 1, 1, NULL),
(DATE_SUB(CURDATE(), INTERVAL 6 DAY), 3, 3, NULL),
(DATE_SUB(CURDATE(), INTERVAL 6 DAY), 6, 4, NULL),
(DATE_SUB(CURDATE(), INTERVAL 5 DAY), 1, 1, NULL),
(DATE_SUB(CURDATE(), INTERVAL 5 DAY), 6, 4, NULL),
(DATE_SUB(CURDATE(), INTERVAL 5 DAY), 7, 2, 'Extra driver'),
(DATE_SUB(CURDATE(), INTERVAL 4 DAY), 1, 1, NULL),
(DATE_SUB(CURDATE(), INTERVAL 4 DAY), 3, 3, NULL),
(DATE_SUB(CURDATE(), INTERVAL 4 DAY), 6, 4, NULL),
(DATE_SUB(CURDATE(), INTERVAL 3 DAY), 1, 2, 'Relief driver'),
(DATE_SUB(CURDATE(), INTERVAL 3 DAY), 3, 3, NULL),
(DATE_SUB(CURDATE(), INTERVAL 3 DAY), 6, 4, NULL),
(DATE_SUB(CURDATE(), INTERVAL 2 DAY), 1, 1, NULL),
(DATE_SUB(CURDATE(), INTERVAL 2 DAY), 3, 3, NULL),
(DATE_SUB(CURDATE(), INTERVAL 2 DAY), 6, 4, NULL),
(DATE_SUB(CURDATE(), INTERVAL 1 DAY), 1, 1, NULL),
(DATE_SUB(CURDATE(), INTERVAL 1 DAY), 3, 3, NULL),
(DATE_SUB(CURDATE(), INTERVAL 1 DAY), 6, 4, NULL),
(DATE_SUB(CURDATE(), INTERVAL 1 DAY), 7, 2, 'Extra driver'),
(CURDATE(), 1, 1, NULL),
(CURDATE(), 3, 3, NULL),
(CURDATE(), 6, 4, NULL);

INSERT INTO users (full_name, username, email, contact_number, password, role, date_registered, status) VALUES
('System Administrator', 'admin', 'admin@asintransport.example.com', '09170000000', '$2y$10$zkIuX7zMbguSVDRaKUnEte8KCqnMjj9zw0Dpsg9BmTWoGikD6mhK.', 'Admin', DATE_SUB(CURDATE(), INTERVAL 700 DAY), 'Active'),
('Ana Villanueva', 'ana', 'ana.villanueva@example.com', '09175550101', '$2y$10$ymfDDhmo6.4XVDA9LgC8Re3rVIQPJf1tqIf6rPNlV0d8Lvp7yZ/8S', 'Passenger', DATE_SUB(CURDATE(), INTERVAL 40 DAY), 'Active'),
('Mark Ramos', 'mark', NULL, '09285550202', '$2y$10$ymfDDhmo6.4XVDA9LgC8Re3rVIQPJf1tqIf6rPNlV0d8Lvp7yZ/8S', 'Passenger', DATE_SUB(CURDATE(), INTERVAL 15 DAY), 'Active'),
('Pedro Reyes Aquino', 'pedro', NULL, '09171230001', '$2y$10$ymfDDhmo6.4XVDA9LgC8Re3rVIQPJf1tqIf6rPNlV0d8Lvp7yZ/8S', 'Driver', DATE_SUB(CURDATE(), INTERVAL 400 DAY), 'Active'),
('Jose Mendoza Ramos', 'jose', NULL, '09181230002', '$2y$10$ymfDDhmo6.4XVDA9LgC8Re3rVIQPJf1tqIf6rPNlV0d8Lvp7yZ/8S', 'Driver', DATE_SUB(CURDATE(), INTERVAL 350 DAY), 'Active'),
('Mark Anthony Torres Lopez', 'marklopez', NULL, '09191230003', '$2y$10$ymfDDhmo6.4XVDA9LgC8Re3rVIQPJf1tqIf6rPNlV0d8Lvp7yZ/8S', 'Driver', DATE_SUB(CURDATE(), INTERVAL 600 DAY), 'Active'),
('Ramil Castro Dizon', 'ramil', NULL, '09201230004', '$2y$10$ymfDDhmo6.4XVDA9LgC8Re3rVIQPJf1tqIf6rPNlV0d8Lvp7yZ/8S', 'Driver', DATE_SUB(CURDATE(), INTERVAL 100 DAY), 'Active');

UPDATE drivers SET user_id = 4 WHERE driver_id = 1;
UPDATE drivers SET user_id = 5 WHERE driver_id = 2;
UPDATE drivers SET user_id = 6 WHERE driver_id = 3;
UPDATE drivers SET user_id = 7 WHERE driver_id = 4;

INSERT INTO complaints (complaint_number, user_id, complainant_name, contact_number, complaint_type, concern, description, vehicle_id, operator_id, route_id, incident_date, incident_location, date_filed, status, date_closed) VALUES
('CMP-2026-0001', 2, 'Ana Villanueva', '09175550101', 'Passenger Complaint', 'Overcharging / wrong fare', 'I paid for a regular fare but the driver asked for P20 and did not give my change back.', 1, 1, 2, DATE_SUB(CURDATE(), INTERVAL 12 DAY), 'Near San Luis Village chapel', DATE_SUB(NOW(), INTERVAL 12 DAY), 'Resolved', DATE_SUB(CURDATE(), INTERVAL 5 DAY)),
('CMP-2026-0002', 3, 'Mark Ramos', '09285550202', 'Vehicle-Related', 'Smoke belching', 'The UV Express was giving off thick black smoke the whole trip going down Asin Road.', 3, 2, 3, DATE_SUB(CURDATE(), INTERVAL 6 DAY), 'Asin Road, near the Woodcarvers Village', DATE_SUB(NOW(), INTERVAL 6 DAY), 'Under Investigation', NULL),
('CMP-2026-0003', 2, 'Ana Villanueva', '09175550101', 'Route-Related', 'Cut trip / did not reach destination', 'The driver told all passengers to get off at San Roque and did not continue to San Luis Village.', 3, 2, 3, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'San Roque Village', DATE_SUB(NOW(), INTERVAL 2 DAY), 'Pending', NULL),
('CMP-2026-0004', NULL, 'Pedro Alcantara', '09391234567', 'Passenger Complaint', 'Reckless driving', 'Walk-in complaint. The e-jeepney was overtaking on a curve at high speed.', 7, 4, 2, DATE_SUB(CURDATE(), INTERVAL 20 DAY), 'Asin Road curve near BenCab', DATE_SUB(NOW(), INTERVAL 19 DAY), 'Dismissed', DATE_SUB(CURDATE(), INTERVAL 15 DAY)),
('CMP-2026-0005', 3, 'Mark Ramos', '09285550202', 'Route-Related', 'Long waiting time / no trips', 'Waited almost an hour for a Long Route trip to Km. 6 in the afternoon. No vehicles came.', NULL, NULL, 1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Downtown terminal', DATE_SUB(NOW(), INTERVAL 1 DAY), 'Pending', NULL);

INSERT INTO complaint_actions (complaint_id, action_date, status, action_taken, handled_by) VALUES
(1, DATE_SUB(NOW(), INTERVAL 10 DAY), 'Under Investigation', 'Called the operator, Juan Dela Cruz. The driver on duty, Pedro Aquino, was asked to report to the office.', 'System Administrator'),
(1, DATE_SUB(NOW(), INTERVAL 5 DAY), 'Resolved', 'Driver admitted the mistake and returned the change to the passenger. A written warning was given.', 'System Administrator'),
(2, DATE_SUB(NOW(), INTERVAL 4 DAY), 'Under Investigation', 'Emission test scheduled for the vehicle. Operator Maria Santos was informed.', 'System Administrator'),
(4, DATE_SUB(NOW(), INTERVAL 15 DAY), 'Dismissed', 'Checked the driver log. EVJ 2025 had no driver on duty on the reported date. Dismissed for lack of evidence.', 'System Administrator');


INSERT INTO violations (ticket_number, violation_kind, agency, vehicle_id, operator_id, driver_id, violation, violation_date, location, fine, status, date_settled, or_number, license_action, permit_valid_until, complaint_id, remarks, date_recorded) VALUES
('TOP-LTFRB-2026-0331', 'Vehicle', 'LTFRB', 1, 1, 1, 'Out of line', DATE_SUB(CURDATE(), INTERVAL 6 DAY), 'Km. 6, Asin Road', 5000.00, 'Pending', NULL, NULL, NULL, NULL, NULL, 'Hearing set at LTFRB-CAR. Operator was notified.', DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 6 DAY), INTERVAL 15 HOUR)),
('DOTR-2026-01142', 'Driver', 'DOTr', 6, 4, 4, 'Reckless driving', DATE_SUB(CURDATE(), INTERVAL 7 DAY), 'Asin Road, Km. 4', 2000.00, 'Pending', NULL, NULL, 'License confiscated, temporary permit issued', DATE_ADD(CURDATE(), INTERVAL 8 DAY), NULL, 'Driver must attend the DOTr seminar before the license is returned.', DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 7 DAY), INTERVAL 15 HOUR)),
('TOP-LTO-61907', 'Vehicle', 'LTO', 3, 2, 3, 'Smoke belching', DATE_SUB(CURDATE(), INTERVAL 8 DAY), 'Asin Road, near San Roque', 2000.00, 'Under Investigation', NULL, NULL, NULL, NULL, 2, 'Emission test scheduled. Same issue as the passenger complaint.', DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 8 DAY), INTERVAL 15 HOUR)),
('TOP-LTFRB-2026-0298', 'Vehicle', 'LTFRB', 5, 3, NULL, 'Operating with expired franchise', DATE_SUB(CURDATE(), INTERVAL 25 DAY), 'Downtown terminal', 5000.00, 'Pending', NULL, NULL, NULL, NULL, NULL, 'Unit impounded. Operator notified.', DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 25 DAY), INTERVAL 15 HOUR)),
('TOP-LTO-60455', 'Vehicle', 'LTO', 5, 3, NULL, 'Expired registration (OR/CR)', DATE_SUB(CURDATE(), INTERVAL 25 DAY), 'Downtown terminal', 10000.00, 'Pending', NULL, NULL, NULL, NULL, NULL, 'Same apprehension as TOP-LTFRB-2026-0298.', DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 25 DAY), INTERVAL 15 HOUR)),
('DOTR-2026-00987', 'Driver', 'DOTr', 3, 2, 3, 'Using phone while driving', DATE_SUB(CURDATE(), INTERVAL 11 DAY), 'San Luis Road', 5000.00, 'Settled', DATE_SUB(CURDATE(), INTERVAL 6 DAY), 'OR-4410287', NULL, NULL, NULL, 'Fine paid at the DOTr office.', DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 11 DAY), INTERVAL 15 HOUR)),
('TOP-LTO-59810', 'Vehicle', 'LTO', 7, 4, 2, 'Loading outside a stop', DATE_SUB(CURDATE(), INTERVAL 13 DAY), 'San Luis Village', 1000.00, 'Settled', DATE_SUB(CURDATE(), INTERVAL 8 DAY), 'LTO-7739021', NULL, NULL, NULL, 'Paid by the operator.', DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 13 DAY), INTERVAL 15 HOUR)),
('TOP-LTO-58821', 'Vehicle', 'LTO', 1, 1, 2, 'Failure to carry OR/CR', DATE_SUB(CURDATE(), INTERVAL 10 DAY), 'Session Road', 1000.00, 'Settled', DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'LTO-7712093', NULL, NULL, NULL, 'Relief driver forgot the OR/CR copy.', DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 10 DAY), INTERVAL 15 HOUR));

UPDATE driver_logs SET fee_paid = 1, paid_date = drive_date;
UPDATE driver_logs SET fee_paid = 0, paid_date = NULL WHERE driver_id = 2 AND drive_date >= DATE_SUB(CURDATE(), INTERVAL 3 DAY);

INSERT INTO driver_expenses (driver_id, expense_date, amount, reason, recorded_by, date_recorded) VALUES
(2, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 50.00, 'Bought brake fluid for ABC 1234', 'System Administrator', DATE_SUB(NOW(), INTERVAL 3 DAY));
