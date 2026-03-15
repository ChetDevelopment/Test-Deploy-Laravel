-- =====================================================
-- Attendance Management System - Sample Data SQL
-- =====================================================
-- This SQL file contains sample data for testing the 
-- attendance management system. Execute this after
-- running all migrations.
--
-- Usage: mysql -u username -p database_name < sample_data.sql
-- =====================================================

-- Set timezone for consistent timestamps
SET time_zone = '+07:00';

-- Disable foreign key checks for faster inserts
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================
-- APPEND MODE (keep existing records)
-- =====================================================
-- No DELETE/TRUNCATE here. This script only adds sample data.

-- =====================================================
-- ACADEMIC YEARS
-- =====================================================

INSERT IGNORE INTO academic_years (name, current_term, status, created_at, updated_at) VALUES
('2025-2026', 'Term2', 'Current', NOW(), NOW()),
('2024-2025', 'Term3', 'Close', DATE_SUB(NOW(), INTERVAL 1 YEAR), DATE_SUB(NOW(), INTERVAL 1 YEAR)),
('2026-2027', 'Term1', 'Current', DATE_ADD(NOW(), INTERVAL 1 YEAR), DATE_ADD(NOW(), INTERVAL 1 YEAR));

-- =====================================================
-- CLASSES
-- =====================================================

INSERT IGNORE INTO classes (academic_year_id, class_name, room_number, created_at, updated_at) VALUES
(1, 'G10-A', '101', NOW(), NOW()),
(1, 'G10-B', '102', NOW(), NOW()),
(1, 'G10-C', '103', NOW(), NOW()),
(1, 'G11-A', '201', NOW(), NOW()),
(1, 'G11-B', '202', NOW(), NOW()),
(1, 'G12-A', '301', NOW(), NOW()),
(1, 'G12-B', '302', NOW(), NOW());

-- =====================================================
-- SESSIONS
-- =====================================================

INSERT IGNORE INTO sessions (academic_year_id, name, start_time, end_time, `order`, created_at, updated_at) VALUES
(1, 'Session 1', '08:00:00', '10:00:00', 1, NOW(), NOW()),
(1, 'Session 2', '10:30:00', '12:30:00', 2, NOW(), NOW()),
(1, 'Session 3', '13:30:00', '15:30:00', 3, NOW(), NOW()),
(1, 'Session 4', '16:00:00', '18:00:00', 4, NOW(), NOW());

-- =====================================================
-- STUDENTS
-- =====================================================

INSERT IGNORE INTO students (fullname, username, email, generation, `class`, class_id, academic_year_id, profile, gender, parent_number, contact, password, card_id, fingerprint_enrolled, created_at, updated_at) VALUES
('Sok Chhem', 'sok.chhem1', 'sok.chhem1@student.pnc.edu', '2025', 'G10-A', 1, 1, NULL, 'Male', '85500000001', '85550000001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '00000001', 1, NOW(), NOW()),
('Vanna Thong', 'vanna.thong2', 'vanna.thong2@student.pnc.edu', '2025', 'G10-B', 2, 1, NULL, 'Male', '85500000002', '85550000002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '00000002', 1, NOW(), NOW()),
('Dara Sok', 'dara.sok3', 'dara.sok3@student.pnc.edu', '2025', 'G10-C', 3, 1, NULL, 'Male', '85500000003', '85550000003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '00000003', 0, NOW(), NOW()),
('Sothea Ly', 'sothea.ly4', 'sothea.ly4@student.pnc.edu', '2025', 'G11-A', 4, 1, NULL, 'Male', '85500000004', '85550000004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '00000004', 1, NOW(), NOW()),
('Rith Keo', 'rith.keo5', 'rith.keo5@student.pnc.edu', '2025', 'G11-B', 5, 1, NULL, 'Male', '85500000005', '85550000005', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '00000005', 1, NOW(), NOW()),
('Piseth Mao', 'piseth.mao6', 'piseth.mao6@student.pnc.edu', '2025', 'G12-A', 6, 1, NULL, 'Male', '85500000006', '85550000006', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '00000006', 0, NOW(), NOW()),
('Chandara Lim', 'chandara.lim7', 'chandara.lim7@student.pnc.edu', '2025', 'G12-B', 7, 1, NULL, 'Male', '85500000007', '85550000007', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '00000007', 1, NOW(), NOW()),
('Bona Chhay', 'bona.chhay8', 'bona.chhay8@student.pnc.edu', '2025', 'G10-A', 1, 1, NULL, 'Male', '85500000008', '85550000008', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '00000008', 1, NOW(), NOW()),
('Sokha Narin', 'sokha.narin9', 'sokha.narin9@student.pnc.edu', '2025', 'G10-B', 2, 1, NULL, 'Female', '85500000009', '85550000009', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '00000009', 1, NOW(), NOW()),
('Vichea Kim', 'vichea.kim10', 'vichea.kim10@student.pnc.edu', '2025', 'G10-C', 3, 1, NULL, 'Male', '85500000010', '85550000010', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '00000010', 0, NOW(), NOW()),
('Sotheara Steng', 'sotheara.steng11', 'sotheara.steng11@student.pnc.edu', '2025', 'G11-A', 4, 1, NULL, 'Female', '85500000011', '85550000011', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '00000011', 1, NOW(), NOW()),
('Kimsreang Bunthoeun', 'kimsreang.bunthoeun12', 'kimsreang.bunthoeun12@student.pnc.edu', '2025', 'G11-B', 5, 1, NULL, 'Male', '85500000012', '85550000012', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '00000012', 1, NOW(), NOW()),
('Virak Pich', 'virak.pich13', 'virak.pich13@student.pnc.edu', '2025', 'G12-A', 6, 1, NULL, 'Male', '85500000013', '85550000013', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '00000013', 1, NOW(), NOW()),
('Sokna Srun', 'sokna.srun14', 'sokna.srun14@student.pnc.edu', '2025', 'G12-B', 7, 1, NULL, 'Female', '85500000014', '85550000014', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '00000014', 0, NOW(), NOW()),
('Ratha Touch', 'ratha.touch15', 'ratha.touch15@student.pnc.edu', '2025', 'G10-A', 1, 1, NULL, 'Female', '85500000015', '85550000015', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '00000015', 1, NOW(), NOW()),
('Chansothea Mek', 'chansothea.mek16', 'chansothea.mek16@student.pnc.edu', '2025', 'G10-B', 2, 1, NULL, 'Female', '85500000016', '85550000016', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '00000016', 1, NOW(), NOW()),
('Pisal Oun', 'pisal.oun17', 'pisal.oun17@student.pnc.edu', '2025', 'G10-C', 3, 1, NULL, 'Male', '85500000017', '85550000017', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '00000017', 1, NOW(), NOW()),
('Sokheng Sin', 'sokheng.sin18', 'sokheng.sin18@student.pnc.edu', '2025', 'G11-A', 4, 1, NULL, 'Male', '85500000018', '85550000018', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '00000018', 0, NOW(), NOW()),
('Vibol Phan', 'vibol.phan19', 'vibol.phan19@student.pnc.edu', '2025', 'G11-B', 5, 1, NULL, 'Male', '85500000019', '85550000019', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '00000019', 1, NOW(), NOW()),
('Sokpol Chhoeun', 'sokpol.chhoeun20', 'sokpol.chhoeun20@student.pnc.edu', '2025', 'G12-A', 6, 1, NULL, 'Male', '85500000020', '85550000020', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '00000020', 1, NOW(), NOW()),
('Chansovannara San', 'chansovannara.san21', 'chansovannara.san21@student.pnc.edu', '2025', 'G12-B', 7, 1, NULL, 'Male', '85500000021', '85550000021', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '00000021', 1, NOW(), NOW()),
('Raksmey Nov', 'raksmey.nov22', 'raksmey.nov22@student.pnc.edu', '2025', 'G10-A', 1, 1, NULL, 'Female', '85500000022', '85550000022', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '00000022', 0, NOW(), NOW()),
('Sokkhoeun Kong', 'sokkhoeun.kong23', 'sokkhoeun.kong23@student.pnc.edu', '2025', 'G10-B', 2, 1, NULL, 'Male', '85500000023', '85550000023', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '00000023', 1, NOW(), NOW()),
('Sokchan Heng', 'sokchan.heng24', 'sokchan.heng24@student.pnc.edu', '2025', 'G10-C', 3, 1, NULL, 'Male', '85500000024', '85550000024', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '00000024', 1, NOW(), NOW()),
('Sokmony Chin', 'sokmony.chin25', 'sokmony.chin25@student.pnc.edu', '2025', 'G11-A', 4, 1, NULL, 'Male', '85500000025', '85550000025', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '00000025', 1, NOW(), NOW()),
('Vannak Seang', 'vannak.seang26', 'vannak.seang26@student.pnc.edu', '2025', 'G11-B', 5, 1, NULL, 'Male', '85500000026', '85550000026', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '00000026', 0, NOW(), NOW()),
('Rithy Lay', 'rithy.lay27', 'rithy.lay27@student.pnc.edu', '2025', 'G12-A', 6, 1, NULL, 'Male', '85500000027', '85550000027', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '00000027', 1, NOW(), NOW()),
('Sokunthea Chhuon', 'sokunthea.chhuon28', 'sokunthea.chhuon28@student.pnc.edu', '2025', 'G12-B', 7, 1, NULL, 'Female', '85500000028', '85550000028', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '00000028', 1, NOW(), NOW()),
('Chanserey Tea', 'chanserey.tea29', 'chanserey.tea29@student.pnc.edu', '2025', 'G10-A', 1, 1, NULL, 'Male', '85500000029', '85550000029', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '00000029', 1, NOW(), NOW()),
('Sokmey Nget', 'sokmey.nget30', 'sokmey.nget30@student.pnc.edu', '2025', 'G10-B', 2, 1, NULL, 'Male', '85500000030', '85550000030', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '00000030', 0, NOW(), NOW()),
('Vannarith Sarith', 'vannarith.sarith31', 'vannarith.sarith31@student.pnc.edu', '2025', 'G10-C', 3, 1, NULL, 'Male', '85500000031', '85550000031', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '00000031', 1, NOW(), NOW()),
('Sokkang Chhem', 'sokkang.chhem32', 'sokkang.chhem32@student.pnc.edu', '2025', 'G11-A', 4, 1, NULL, 'Male', '85500000032', '85550000032', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '00000032', 1, NOW(), NOW()),
('Raksmey Thong', 'raksmey.thong33', 'raksmey.thong33@student.pnc.edu', '2025', 'G11-B', 5, 1, NULL, 'Female', '85500000033', '85550000033', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '00000033', 1, NOW(), NOW()),
('Sokhour Sok', 'sokhour.sok34', 'sokhour.sok34@student.pnc.edu', '2025', 'G12-A', 6, 1, NULL, 'Male', '85500000034', '85550000034', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '00000034', 0, NOW(), NOW()),
('Vannara Ly', 'vannara.ly35', 'vannara.ly35@student.pnc.edu', '2025', 'G12-B', 7, 1, NULL, 'Male', '85500000035', '85550000035', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '00000035', 1, NOW(), NOW()),
('Sokphalla Keo', 'sokphalla.keo36', 'sokphalla.keo36@student.pnc.edu', '2025', 'G10-A', 1, 1, NULL, 'Male', '85500000036', '85550000036', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '00000036', 1, NOW(), NOW()),
('Rithisak Mao', 'rithisak.mao37', 'rithisak.mao37@student.pnc.edu', '2025', 'G10-B', 2, 1, NULL, 'Male', '85500000037', '85550000037', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '00000037', 1, NOW(), NOW()),
('Sokvannara Lim', 'sokvannara.lim38', 'sokvannara.lim38@student.pnc.edu', '2025', 'G10-C', 3, 1, NULL, 'Male', '85500000038', '85550000038', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '00000038', 0, NOW(), NOW()),
('Chanserey Chhay', 'chanserey.chhay39', 'chanserey.chhay39@student.pnc.edu', '2025', 'G11-A', 4, 1, NULL, 'Male', '85500000039', '85550000039', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '00000039', 1, NOW(), NOW()),
('Sokha Narin', 'sokha.narin40', 'sokha.narin40@student.pnc.edu', '2025', 'G11-B', 5, 1, NULL, 'Female', '85500000040', '85550000040', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '00000040', 1, NOW(), NOW());

-- =====================================================
-- USERS (Admin, Teachers, Students)
-- =====================================================

-- Roles
INSERT INTO roles (name, description, created_at, updated_at) VALUES
('admin', 'System administrator with full access', NOW(), NOW()),
('teacher', 'Teacher role with limited access', NOW(), NOW()),
('education', 'Education department staff with academic management access', NOW(), NOW()),
('student', 'Student role for attendance and dashboard access', NOW(), NOW())
ON DUPLICATE KEY UPDATE
description = VALUES(description),
updated_at = VALUES(updated_at);

-- Admin user
INSERT IGNORE INTO users (name, email, password, role_id, student_id, created_at, updated_at) VALUES
('Admin User', 'admin@pnc.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, NULL, NOW(), NOW());

-- Teacher users
INSERT IGNORE INTO users (name, email, password, role_id, student_id, created_at, updated_at) VALUES
('Chhay Bunthoeun', 'chhay.bunthoeun@pnc.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, NULL, NOW(), NOW()),
('Sokha Kin', 'sokha.kin@pnc.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, NULL, NOW(), NOW()),
('Vichea Mao', 'vichea.mao@pnc.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, NULL, NOW(), NOW()),
('Sothea Narin', 'sothea.narin@pnc.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, NULL, NOW(), NOW()),
('Pisey Steng', 'pisey.steng@pnc.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, NULL, NOW(), NOW()),
('Ratha Kim', 'ratha.kim@pnc.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, NULL, NOW(), NOW());

-- Student users (linking to students)
INSERT IGNORE INTO users (name, email, password, role_id, student_id, created_at, updated_at) 
SELECT 
    s.fullname,
    s.email,
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    4,
    s.id,
    NOW(),
    NOW()
FROM students s;

-- =====================================================
-- ATTENDANCE RECORDS
-- =====================================================

-- Generate attendance records for the past 30 days (excluding weekends)
-- This creates realistic test data with Present, Late, Absent, and Excused statuses

INSERT INTO attendance_records (student_id, session_id, status, location, submitted_by, created_at, updated_at) VALUES
-- Sample records for March 8, 2026 (Session 1)
(1, 1, 'Present', 'Classroom 101', 1, '2026-03-08 08:15:00', '2026-03-08 08:15:00'),
(2, 1, 'Present', 'Classroom 101', 1, '2026-03-08 08:10:00', '2026-03-08 08:10:00'),
(3, 1, 'Late', 'Classroom 101', 1, '2026-03-08 08:25:00', '2026-03-08 08:25:00'),
(4, 1, 'Present', 'Classroom 101', 1, '2026-03-08 08:05:00', '2026-03-08 08:05:00'),
(5, 1, 'Absent', 'Classroom 101', 1, '2026-03-08 09:00:00', '2026-03-08 09:00:00'),
(6, 1, 'Present', 'Classroom 101', 1, '2026-03-08 08:12:00', '2026-03-08 08:12:00'),
(7, 1, 'Excused', 'Classroom 101', 1, '2026-03-08 08:00:00', '2026-03-08 08:00:00'),
(8, 1, 'Present', 'Classroom 101', 1, '2026-03-08 08:08:00', '2026-03-08 08:08:00'),
(9, 1, 'Present', 'Classroom 101', 1, '2026-03-08 08:20:00', '2026-03-08 08:20:00'),
(10, 1, 'Late', 'Classroom 101', 1, '2026-03-08 08:30:00', '2026-03-08 08:30:00'),
-- Session 2
(1, 2, 'Present', 'Classroom 102', 1, '2026-03-08 10:35:00', '2026-03-08 10:35:00'),
(2, 2, 'Present', 'Classroom 102', 1, '2026-03-08 10:32:00', '2026-03-08 10:32:00'),
(3, 2, 'Absent', 'Classroom 102', 1, '2026-03-08 11:00:00', '2026-03-08 11:00:00'),
(4, 2, 'Present', 'Classroom 102', 1, '2026-03-08 10:30:00', '2026-03-08 10:30:00'),
(5, 2, 'Present', 'Classroom 102', 1, '2026-03-08 10:45:00', '2026-03-08 10:45:00'),
-- Session 3
(1, 3, 'Present', 'Lab 201', 1, '2026-03-08 13:35:00', '2026-03-08 13:35:00'),
(2, 3, 'Late', 'Lab 201', 1, '2026-03-08 13:45:00', '2026-03-08 13:45:00'),
(3, 3, 'Present', 'Lab 201', 1, '2026-03-08 13:32:00', '2026-03-08 13:32:00'),
(4, 3, 'Present', 'Lab 201', 1, '2026-03-08 13:30:00', '2026-03-08 13:30:00'),
(5, 3, 'Excused', 'Lab 201', 1, '2026-03-08 13:30:00', '2026-03-08 13:30:00'),
-- Session 4
(1, 4, 'Present', 'Library', 1, '2026-03-08 16:05:00', '2026-03-08 16:05:00'),
(2, 4, 'Present', 'Library', 1, '2026-03-08 16:02:00', '2026-03-08 16:02:00'),
(3, 4, 'Present', 'Library', 1, '2026-03-08 16:10:00', '2026-03-08 16:10:00'),
(4, 4, 'Absent', 'Library', 1, '2026-03-08 17:00:00', '2026-03-08 17:00:00'),
(5, 4, 'Present', 'Library', 1, '2026-03-08 16:08:00', '2026-03-08 16:08:00'),
-- March 7, 2026
(1, 1, 'Present', 'Classroom 101', 1, '2026-03-07 08:10:00', '2026-03-07 08:10:00'),
(2, 1, 'Present', 'Classroom 101', 1, '2026-03-07 08:05:00', '2026-03-07 08:05:00'),
(3, 1, 'Late', 'Classroom 101', 1, '2026-03-07 08:28:00', '2026-03-07 08:28:00'),
(4, 1, 'Present', 'Classroom 101', 1, '2026-03-07 08:12:00', '2026-03-07 08:12:00'),
(5, 1, 'Present', 'Classroom 101', 1, '2026-03-07 08:08:00', '2026-03-07 08:08:00'),
(6, 1, 'Present', 'Classroom 101', 1, '2026-03-07 08:15:00', '2026-03-07 08:15:00'),
(7, 1, 'Absent', 'Classroom 101', 1, '2026-03-07 09:00:00', '2026-03-07 09:00:00'),
(8, 1, 'Present', 'Classroom 101', 1, '2026-03-07 08:20:00', '2026-03-07 08:20:00'),
(9, 1, 'Excused', 'Classroom 101', 1, '2026-03-07 08:00:00', '2026-03-07 08:00:00'),
(10, 1, 'Present', 'Classroom 101', 1, '2026-03-07 08:18:00', '2026-03-07 08:18:00'),
-- March 6, 2026
(1, 1, 'Present', 'Classroom 103', 1, '2026-03-06 08:12:00', '2026-03-06 08:12:00'),
(2, 1, 'Present', 'Classroom 103', 1, '2026-03-06 08:08:00', '2026-03-06 08:08:00'),
(3, 1, 'Present', 'Classroom 103', 1, '2026-03-06 08:15:00', '2026-03-06 08:15:00'),
(4, 1, 'Late', 'Classroom 103', 1, '2026-03-06 08:35:00', '2026-03-06 08:35:00'),
(5, 1, 'Present', 'Classroom 103', 1, '2026-03-06 08:05:00', '2026-03-06 08:05:00'),
(6, 1, 'Present', 'Classroom 103', 1, '2026-03-06 08:10:00', '2026-03-06 08:10:00'),
(7, 1, 'Present', 'Classroom 103', 1, '2026-03-06 08:22:00', '2026-03-06 08:22:00'),
(8, 1, 'Absent', 'Classroom 103', 1, '2026-03-06 09:00:00', '2026-03-06 09:00:00'),
(9, 1, 'Present', 'Classroom 103', 1, '2026-03-06 08:18:00', '2026-03-06 08:18:00'),
(10, 1, 'Present', 'Classroom 103', 1, '2026-03-06 08:25:00', '2026-03-06 08:25:00'),
-- March 5, 2026
(1, 1, 'Present', 'Classroom 102', 1, '2026-03-05 08:10:00', '2026-03-05 08:10:00'),
(2, 1, 'Present', 'Classroom 102', 1, '2026-03-05 08:05:00', '2026-03-05 08:05:00'),
(3, 1, 'Present', 'Classroom 102', 1, '2026-03-05 08:12:00', '2026-03-05 08:12:00'),
(4, 1, 'Present', 'Classroom 102', 1, '2026-03-05 08:08:00', '2026-03-05 08:08:00'),
(5, 1, 'Excused', 'Classroom 102', 1, '2026-03-05 08:00:00', '2026-03-05 08:00:00'),
(6, 1, 'Present', 'Classroom 102', 1, '2026-03-05 08:15:00', '2026-03-05 08:15:00'),
(7, 1, 'Late', 'Classroom 102', 1, '2026-03-05 08:32:00', '2026-03-05 08:32:00'),
(8, 1, 'Present', 'Classroom 102', 1, '2026-03-05 08:20:00', '2026-03-05 08:20:00'),
(9, 1, 'Present', 'Classroom 102', 1, '2026-03-05 08:18:00', '2026-03-05 08:18:00'),
(10, 1, 'Present', 'Classroom 102', 1, '2026-03-05 08:22:00', '2026-03-05 08:22:00'),
-- March 4, 2026
(1, 1, 'Present', 'Classroom 101', 1, '2026-03-04 08:08:00', '2026-03-04 08:08:00'),
(2, 1, 'Present', 'Classroom 101', 1, '2026-03-04 08:12:00', '2026-03-04 08:12:00'),
(3, 1, 'Absent', 'Classroom 101', 1, '2026-03-04 09:00:00', '2026-03-04 09:00:00'),
(4, 1, 'Present', 'Classroom 101', 1, '2026-03-04 08:15:00', '2026-03-04 08:15:00'),
(5, 1, 'Present', 'Classroom 101', 1, '2026-03-04 08:10:00', '2026-03-04 08:10:00'),
(6, 1, 'Present', 'Classroom 101', 1, '2026-03-04 08:05:00', '2026-03-04 08:05:00'),
(7, 1, 'Present', 'Classroom 101', 1, '2026-03-04 08:20:00', '2026-03-04 08:20:00'),
(8, 1, 'Late', 'Classroom 101', 1, '2026-03-04 08:35:00', '2026-03-04 08:35:00'),
(9, 1, 'Present', 'Classroom 101', 1, '2026-03-04 08:18:00', '2026-03-04 08:18:00'),
(10, 1, 'Excused', 'Classroom 101', 1, '2026-03-04 08:00:00', '2026-03-04 08:00:00'),
-- March 3, 2026
(1, 1, 'Present', 'Classroom 201', 1, '2026-03-03 08:10:00', '2026-03-03 08:10:00'),
(2, 1, 'Present', 'Classroom 201', 1, '2026-03-03 08:15:00', '2026-03-03 08:15:00'),
(3, 1, 'Present', 'Classroom 201', 1, '2026-03-03 08:08:00', '2026-03-03 08:08:00'),
(4, 1, 'Present', 'Classroom 201', 1, '2026-03-03 08:12:00', '2026-03-03 08:12:00'),
(5, 1, 'Present', 'Classroom 201', 1, '2026-03-03 08:05:00', '2026-03-03 08:05:00'),
(6, 1, 'Late', 'Classroom 201', 1, '2026-03-03 08:30:00', '2026-03-03 08:30:00'),
(7, 1, 'Present', 'Classroom 201', 1, '2026-03-03 08:18:00', '2026-03-03 08:18:00'),
(8, 1, 'Present', 'Classroom 201', 1, '2026-03-03 08:22:00', '2026-03-03 08:22:00'),
(9, 1, 'Present', 'Classroom 201', 1, '2026-03-03 08:20:00', '2026-03-03 08:20:00'),
(10, 1, 'Present', 'Classroom 201', 1, '2026-03-03 08:25:00', '2026-03-03 08:25:00'),
-- March 2, 2026 (Sunday - no school)
-- March 1, 2026 (Saturday - no school)
-- Feb 28, 2026
(1, 1, 'Present', 'Classroom 102', 1, '2026-02-28 08:10:00', '2026-02-28 08:10:00'),
(2, 1, 'Present', 'Classroom 102', 1, '2026-02-28 08:08:00', '2026-02-28 08:08:00'),
(3, 1, 'Present', 'Classroom 102', 1, '2026-02-28 08:15:00', '2026-02-28 08:15:00'),
(4, 1, 'Present', 'Classroom 102', 1, '2026-02-28 08:12:00', '2026-02-28 08:12:00'),
(5, 1, 'Absent', 'Classroom 102', 1, '2026-02-28 09:00:00', '2026-02-28 09:00:00'),
(6, 1, 'Present', 'Classroom 102', 1, '2026-02-28 08:05:00', '2026-02-28 08:05:00'),
(7, 1, 'Present', 'Classroom 102', 1, '2026-02-28 08:20:00', '2026-02-28 08:20:00'),
(8, 1, 'Late', 'Classroom 102', 1, '2026-02-28 08:35:00', '2026-02-28 08:35:00'),
(9, 1, 'Present', 'Classroom 102', 1, '2026-02-28 08:18:00', '2026-02-28 08:18:00'),
(10, 1, 'Present', 'Classroom 102', 1, '2026-02-28 08:22:00', '2026-02-28 08:22:00'),
-- Feb 27, 2026
(1, 1, 'Present', 'Classroom 103', 1, '2026-02-27 08:12:00', '2026-02-27 08:12:00'),
(2, 1, 'Present', 'Classroom 103', 1, '2026-02-27 08:08:00', '2026-02-27 08:08:00'),
(3, 1, 'Present', 'Classroom 103', 1, '2026-02-27 08:10:00', '2026-02-27 08:10:00'),
(4, 1, 'Excused', 'Classroom 103', 1, '2026-02-27 08:00:00', '2026-02-27 08:00:00'),
(5, 1, 'Present', 'Classroom 103', 1, '2026-02-27 08:15:00', '2026-02-27 08:15:00'),
(6, 1, 'Present', 'Classroom 103', 1, '2026-02-27 08:18:00', '2026-02-27 08:18:00'),
(7, 1, 'Present', 'Classroom 103', 1, '2026-02-27 08:20:00', '2026-02-27 08:20:00'),
(8, 1, 'Present', 'Classroom 103', 1, '2026-02-27 08:22:00', '2026-02-27 08:22:00'),
(9, 1, 'Late', 'Classroom 103', 1, '2026-02-27 08:35:00', '2026-02-27 08:35:00'),
(10, 1, 'Present', 'Classroom 103', 1, '2026-02-27 08:25:00', '2026-02-27 08:25:00'),
-- Feb 26, 2026
(1, 1, 'Present', 'Classroom 101', 1, '2026-02-26 08:10:00', '2026-02-26 08:10:00'),
(2, 1, 'Present', 'Classroom 101', 1, '2026-02-26 08:08:00', '2026-02-26 08:08:00'),
(3, 1, 'Present', 'Classroom 101', 1, '2026-02-26 08:12:00', '2026-02-26 08:12:00'),
(4, 1, 'Present', 'Classroom 101', 1, '2026-02-26 08:15:00', '2026-02-26 08:15:00'),
(5, 1, 'Present', 'Classroom 101', 1, '2026-02-26 08:05:00', '2026-02-26 08:05:00'),
(6, 1, 'Absent', 'Classroom 101', 1, '2026-02-26 09:00:00', '2026-02-26 09:00:00'),
(7, 1, 'Present', 'Classroom 101', 1, '2026-02-26 08:20:00', '2026-02-26 08:20:00'),
(8, 1, 'Present', 'Classroom 101', 1, '2026-02-26 08:18:00', '2026-02-26 08:18:00'),
(9, 1, 'Present', 'Classroom 101', 1, '2026-02-26 08:22:00', '2026-02-26 08:22:00'),
(10, 1, 'Present', 'Classroom 101', 1, '2026-02-26 08:25:00', '2026-02-26 08:25:00'),
-- Feb 25, 2026
(1, 1, 'Present', 'Classroom 102', 1, '2026-02-25 08:10:00', '2026-02-25 08:10:00'),
(2, 1, 'Present', 'Classroom 102', 1, '2026-02-25 08:08:00', '2026-02-25 08:08:00'),
(3, 1, 'Present', 'Classroom 102', 1, '2026-02-25 08:15:00', '2026-02-25 08:15:00'),
(4, 1, 'Late', 'Classroom 102', 1, '2026-02-25 08:32:00', '2026-02-25 08:32:00'),
(5, 1, 'Present', 'Classroom 102', 1, '2026-02-25 08:12:00', '2026-02-25 08:12:00'),
(6, 1, 'Present', 'Classroom 102', 1, '2026-02-25 08:05:00', '2026-02-25 08:05:00'),
(7, 1, 'Present', 'Classroom 102', 1, '2026-02-25 08:20:00', '2026-02-25 08:20:00'),
(8, 1, 'Present', 'Classroom 102', 1, '2026-02-25 08:18:00', '2026-02-25 08:18:00'),
(9, 1, 'Excused', 'Classroom 102', 1, '2026-02-25 08:00:00', '2026-02-25 08:00:00'),
(10, 1, 'Present', 'Classroom 102', 1, '2026-02-25 08:22:00', '2026-02-25 08:22:00'),
-- Feb 24, 2026
(1, 1, 'Present', 'Classroom 201', 1, '2026-02-24 08:10:00', '2026-02-24 08:10:00'),
(2, 1, 'Present', 'Classroom 201', 1, '2026-02-24 08:12:00', '2026-02-24 08:12:00'),
(3, 1, 'Present', 'Classroom 201', 1, '2026-02-24 08:08:00', '2026-02-24 08:08:00'),
(4, 1, 'Present', 'Classroom 201', 1, '2026-02-24 08:15:00', '2026-02-24 08:15:00'),
(5, 1, 'Present', 'Classroom 201', 1, '2026-02-24 08:05:00', '2026-02-24 08:05:00'),
(6, 1, 'Present', 'Classroom 201', 1, '2026-02-24 08:18:00', '2026-02-24 08:18:00'),
(7, 1, 'Absent', 'Classroom 201', 1, '2026-02-24 09:00:00', '2026-02-24 09:00:00'),
(8, 1, 'Present', 'Classroom 201', 1, '2026-02-24 08:20:00', '2026-02-24 08:20:00'),
(9, 1, 'Present', 'Classroom 201', 1, '2026-02-24 08:22:00', '2026-02-24 08:22:00'),
(10, 1, 'Late', 'Classroom 201', 1, '2026-02-24 08:35:00', '2026-02-24 08:35:00');

-- =====================================================
-- BIOMETRIC SCANS
-- =====================================================

INSERT INTO biometric_scans (student_id, session_id, scan_type, scan_data, status, device_id, ip_address, created_at, updated_at) VALUES
-- Successful scans
(1, 1, 'card', '00000001', 'success', 'DEVICE-001', '192.168.1.10', '2026-03-08 08:05:15', '2026-03-08 08:05:15'),
(2, 1, 'card', '00000002', 'success', 'DEVICE-001', '192.168.1.11', '2026-03-08 08:05:22', '2026-03-08 08:05:22'),
(3, 1, 'card', '00000003', 'failed', 'DEVICE-001', '192.168.1.12', '2026-03-08 08:05:30', '2026-03-08 08:05:30'),
(4, 1, 'fingerprint', '00000004', 'success', 'DEVICE-001', '192.168.1.13', '2026-03-08 08:05:45', '2026-03-08 08:05:45'),
(5, 1, 'card', '00000005', 'success', 'DEVICE-001', '192.168.1.14', '2026-03-08 08:06:00', '2026-03-08 08:06:00'),
(6, 1, 'card', '00000006', 'duplicate', 'DEVICE-001', '192.168.1.15', '2026-03-08 08:06:15', '2026-03-08 08:06:15'),
(7, 1, 'fingerprint', '00000007', 'success', 'DEVICE-001', '192.168.1.16', '2026-03-08 08:06:30', '2026-03-08 08:06:30'),
(8, 1, 'card', '00000008', 'success', 'DEVICE-001', '192.168.1.17', '2026-03-08 08:06:45', '2026-03-08 08:06:45'),
(9, 1, 'card', '00000009', 'success', 'DEVICE-001', '192.168.1.18', '2026-03-08 08:07:00', '2026-03-08 08:07:00'),
(10, 1, 'fingerprint', '00000010', 'success', 'DEVICE-001', '192.168.1.19', '2026-03-08 08:07:15', '2026-03-08 08:07:15'),
-- Session 2 scans
(1, 2, 'card', '00000001', 'success', 'DEVICE-001', '192.168.1.10', '2026-03-08 10:30:10', '2026-03-08 10:30:10'),
(2, 2, 'card', '00000002', 'success', 'DEVICE-001', '192.168.1.11', '2026-03-08 10:30:25', '2026-03-08 10:30:25'),
(3, 2, 'card', '00000003', 'success', 'DEVICE-001', '192.168.1.12', '2026-03-08 10:30:40', '2026-03-08 10:30:40'),
(4, 2, 'fingerprint', '00000004', 'success', 'DEVICE-001', '192.168.1.13', '2026-03-08 10:30:55', '2026-03-08 10:30:55'),
(5, 2, 'card', '00000005', 'failed', 'DEVICE-001', '192.168.1.14', '2026-03-08 10:31:10', '2026-03-08 10:31:10'),
-- Session 3 scans
(1, 3, 'card', '00000001', 'success', 'DEVICE-001', '192.168.1.10', '2026-03-08 13:30:15', '2026-03-08 13:30:15'),
(2, 3, 'card', '00000002', 'success', 'DEVICE-001', '192.168.1.11', '2026-03-08 13:30:30', '2026-03-08 13:30:30'),
(3, 3, 'fingerprint', '00000003', 'success', 'DEVICE-001', '192.168.1.12', '2026-03-08 13:30:45', '2026-03-08 13:30:45'),
(4, 3, 'card', '00000004', 'success', 'DEVICE-001', '192.168.1.13', '2026-03-08 13:31:00', '2026-03-08 13:31:00'),
(5, 3, 'card', '00000005', 'success', 'DEVICE-001', '192.168.1.14', '2026-03-08 13:31:15', '2026-03-08 13:31:15'),
-- Session 4 scans
(1, 4, 'card', '00000001', 'success', 'DEVICE-001', '192.168.1.10', '2026-03-08 16:00:10', '2026-03-08 16:00:10'),
(2, 4, 'fingerprint', '00000002', 'success', 'DEVICE-001', '192.168.1.11', '2026-03-08 16:00:25', '2026-03-08 16:00:25'),
(3, 4, 'card', '00000003', 'success', 'DEVICE-001', '192.168.1.12', '2026-03-08 16:00:40', '2026-03-08 16:00:40'),
(4, 4, 'card', '00000004', 'failed', 'DEVICE-001', '192.168.1.13', '2026-03-08 16:00:55', '2026-03-08 16:00:55'),
(5, 4, 'card', '00000005', 'success', 'DEVICE-001', '192.168.1.14', '2026-03-08 16:01:10', '2026-03-08 16:01:10'),
-- March 7, 2026
(1, 1, 'card', '00000001', 'success', 'DEVICE-001', '192.168.1.10', '2026-03-07 08:05:20', '2026-03-07 08:05:20'),
(2, 1, 'card', '00000002', 'success', 'DEVICE-001', '192.168.1.11', '2026-03-07 08:05:35', '2026-03-07 08:05:35'),
(3, 1, 'fingerprint', '00000003', 'success', 'DEVICE-001', '192.168.1.12', '2026-03-07 08:05:50', '2026-03-07 08:05:50'),
(4, 1, 'card', '00000004', 'success', 'DEVICE-001', '192.168.1.13', '2026-03-07 08:06:05', '2026-03-07 08:06:05'),
(5, 1, 'card', '00000005', 'success', 'DEVICE-001', '192.168.1.14', '2026-03-07 08:06:20', '2026-03-07 08:06:20'),
-- March 6, 2026
(1, 1, 'card', '00000001', 'success', 'DEVICE-001', '192.168.1.10', '2026-03-06 08:05:15', '2026-03-06 08:05:15'),
(2, 1, 'card', '00000002', 'success', 'DEVICE-001', '192.168.1.11', '2026-03-06 08:05:30', '2026-03-06 08:05:30'),
(3, 1, 'card', '00000003', 'success', 'DEVICE-001', '192.168.1.12', '2026-03-06 08:05:45', '2026-03-06 08:05:45'),
(4, 1, 'fingerprint', '00000004', 'failed', 'DEVICE-001', '192.168.1.13', '2026-03-06 08:06:00', '2026-03-06 08:06:00'),
(5, 1, 'card', '00000005', 'success', 'DEVICE-001', '192.168.1.14', '2026-03-06 08:06:15', '2026-03-06 08:06:15'),
-- March 5, 2026
(1, 1, 'card', '00000001', 'success', 'DEVICE-001', '192.168.1.10', '2026-03-05 08:05:25', '2026-03-05 08:05:25'),
(2, 1, 'card', '00000002', 'success', 'DEVICE-001', '192.168.1.11', '2026-03-05 08:05:40', '2026-03-05 08:05:40'),
(3, 1, 'card', '00000003', 'duplicate', 'DEVICE-001', '192.168.1.12', '2026-03-05 08:05:55', '2026-03-05 08:05:55'),
(4, 1, 'card', '00000004', 'success', 'DEVICE-001', '192.168.1.13', '2026-03-05 08:06:10', '2026-03-05 08:06:10'),
(5, 1, 'fingerprint', '00000005', 'success', 'DEVICE-001', '192.168.1.14', '2026-03-05 08:06:25', '2026-03-05 08:06:25');

-- =====================================================
-- RE-ENABLE FOREIGN KEY CHECKS
-- =====================================================

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- SUMMARY
-- =====================================================

SELECT 'Data seeding completed successfully!' AS message;

-- Show record counts
SELECT 'academic_years' AS table_name, COUNT(*) AS record_count FROM academic_years
UNION ALL
SELECT 'classes', COUNT(*) FROM classes
UNION ALL
SELECT 'sessions', COUNT(*) FROM sessions
UNION ALL
SELECT 'students', COUNT(*) FROM students
UNION ALL
SELECT 'users', COUNT(*) FROM users
UNION ALL
SELECT 'attendance_records', COUNT(*) FROM attendance_records
UNION ALL
SELECT 'biometric_scans', COUNT(*) FROM biometric_scans;
