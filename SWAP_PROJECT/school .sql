-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 02, 2025 at 03:51 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `school`
--

-- --------------------------------------------------------

--
-- Table structure for table `account`
--

CREATE TABLE `account` (
  `id` varchar(500) NOT NULL,
  `email` varchar(500) NOT NULL,
  `role_id` int(11) NOT NULL,
  `password` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account`
--

INSERT INTO `account` (`id`, `email`, `role_id`, `password`) VALUES
('000', 'admin@xyz.com', 1, '$2y$10$anFaiHx7ql8HE/cSe1Z5te4K2BLmC6dF.9QzcU0u9QIxmHkEI5wxO'),
('A0004', 'meandyou@xyz.com', 3, '$2y$10$I6MBuouyrewWcDpQUF4vT.oEhlH.HGcIkQMOR.Mrsu.R3TxWqUPYG'),
('A001', 'aizrylproject@gmail.com', 3, '$2y$10$bIarZLuzAuwhsFKlmI7cU.o/.roXdM54SlZ.zBqcc9I7Boob3Oqrq'),
('F283', 'johnwee@xyz.com', 2, '$2y$10$/RxgODfAsc4xVfu4TDCUCedw.95owUqwgdTvYaOduXeepMUGZJxu6');

-- --------------------------------------------------------

--
-- Table structure for table `classname`
--

CREATE TABLE `classname` (
  `classname_id` int(11) NOT NULL,
  `name` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classname`
--

INSERT INTO `classname` (`classname_id`, `name`) VALUES
(10, 'ASD'),
(4, 'Class D'),
(6, 'Class E');

-- --------------------------------------------------------

--
-- Table structure for table `classtype`
--

CREATE TABLE `classtype` (
  `classtype_id` int(11) NOT NULL,
  `type` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classtype`
--

INSERT INTO `classtype` (`classtype_id`, `type`) VALUES
(1, 'Semester'),
(2, 'Term');

-- --------------------------------------------------------

--
-- Table structure for table `course`
--

CREATE TABLE `course` (
  `course_code` varchar(500) NOT NULL,
  `name` varchar(500) NOT NULL,
  `date_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course`
--

INSERT INTO `course` (`course_code`, `name`, `date_id`, `status_id`, `course_id`) VALUES
('C23R04', 'Cybersecurity Fundamentals', 1, 1, 1),
('A23R1', 'Application Development', 2, 1, 2),
('M51R6', 'Mathematics and Algorithm', 1, 1, 4);

-- --------------------------------------------------------

--
-- Table structure for table `course_class`
--

CREATE TABLE `course_class` (
  `course_class_id` int(11) NOT NULL,
  `course_code` varchar(500) NOT NULL,
  `classname_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_class`
--

INSERT INTO `course_class` (`course_class_id`, `course_code`, `classname_id`, `type_id`) VALUES
(18, 'A23R1', 6, 1),
(23, 'M51R6', 6, 2),
(26, 'C23R04', 6, 2);

-- --------------------------------------------------------

--
-- Table structure for table `date`
--

CREATE TABLE `date` (
  `date_id` int(11) NOT NULL,
  `start` date NOT NULL,
  `end` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `date`
--

INSERT INTO `date` (`date_id`, `start`, `end`) VALUES
(1, '2024-04-15', '2024-06-23'),
(2, '2024-06-24', '2024-08-18'),
(3, '2024-04-15', '2024-08-18'),
(4, '2024-10-14', '2025-01-05'),
(5, '2025-01-06', '2025-02-23'),
(6, '2024-10-14', '2025-02-23'),
(7, '2025-04-21', '2025-06-29'),
(8, '2025-06-30', '2025-08-24'),
(9, '2025-04-21', '2025-08-24'),
(10, '2025-10-20', '2026-01-04'),
(11, '2026-01-05', '2026-02-22'),
(12, '2025-10-20', '2026-02-22');

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

CREATE TABLE `department` (
  `department_id` int(11) NOT NULL,
  `name` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `department`
--

INSERT INTO `department` (`department_id`, `name`) VALUES
(1, 'Computer Science'),
(2, 'Electrical Engineering'),
(3, 'Mechanical Engineering');

-- --------------------------------------------------------

--
-- Table structure for table `enrollment`
--

CREATE TABLE `enrollment` (
  `enrollment_id` int(11) NOT NULL,
  `student_email` varchar(500) NOT NULL,
  `course_class_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollment`
--

INSERT INTO `enrollment` (`enrollment_id`, `student_email`, `course_class_id`) VALUES
(15, 'zahid@gmail.com', 26),
(17, 'zahid@gmail.com', 23),
(19, 'payne@gmail.com', 26),
(21, 'maxim@gmail.com', 26),
(27, 'zabib123@gmail.com', 26);

-- --------------------------------------------------------

--
-- Table structure for table `one_time_password`
--

CREATE TABLE `one_time_password` (
  `email` varchar(100) NOT NULL,
  `generatedpassword` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `role_id` int(11) NOT NULL,
  `name` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`role_id`, `name`) VALUES
(1, 'admin'),
(2, 'faculty'),
(3, 'student'),
(4, 'OTP');

-- --------------------------------------------------------

--
-- Table structure for table `status`
--

CREATE TABLE `status` (
  `status_id` int(11) NOT NULL,
  `name` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `status`
--

INSERT INTO `status` (`status_id`, `name`) VALUES
(1, 'Active'),
(3, 'Cancelled'),
(2, 'Completed'),
(4, 'Inactive');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `student_email` varchar(500) NOT NULL,
  `name` varchar(500) NOT NULL,
  `mobile_number` varchar(15) NOT NULL,
  `metric_number` varchar(500) NOT NULL,
  `department_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`student_email`, `name`, `mobile_number`, `metric_number`, `department_id`) VALUES
('jeff@gmail.com', 'Jeff', '12344444', '1283713H', 2),
('zahid@gmail.com', 'Zahid', '12324745', '1284103H', 2),
('aizrylproject@gmail.com', 'Aiz', '12324445', '1284113H', 2),
('st@a.com', 'Jake', '12344445', '1284713H', 2),
('kate@gmail.com', 'Kate', '56142522', '1432423H', 1),
('justin@gmail.com', 'Justin', '32524223', '2313138V', 1),
('hubert@gmail.com', 'Hubert', '21346432', '3143114H', 3),
('lark@gmail.com', 'Lark', '35235141', '3213131H', 1),
('maxim@gmail.com', 'Maxim', '75462623', '3242432H', 2),
('jayson@gmail.com', 'Jayson', '24151231', '3612112H', 1),
('payson@gmail.com', 'Payson', '67235832', '4325251H', 3),
('max@gmail.com', 'Max', '15653861', '4635462H', 2),
('william@gmail.com', 'William', '64368473', '4636433H', 1),
('zabib123@gmail.com', 'Zabib', '34654624', '5636335H', 2),
('nabil@gmail.com', 'Nabil', '63245242', '7513131H', 1),
('payne@gmail.com', 'Payne', '36884576', '8469295H', 3);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account`
--
ALTER TABLE `account`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `Account_role_id_fk` (`role_id`);

--
-- Indexes for table `classname`
--
ALTER TABLE `classname`
  ADD PRIMARY KEY (`classname_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `classtype`
--
ALTER TABLE `classtype`
  ADD PRIMARY KEY (`classtype_id`),
  ADD UNIQUE KEY `type` (`type`);

--
-- Indexes for table `course`
--
ALTER TABLE `course`
  ADD PRIMARY KEY (`course_id`),
  ADD UNIQUE KEY `course_code` (`course_code`),
  ADD KEY `Courses_dates_id_fk` (`date_id`),
  ADD KEY `Courses_status_id_fk` (`status_id`);

--
-- Indexes for table `course_class`
--
ALTER TABLE `course_class`
  ADD PRIMARY KEY (`course_class_id`),
  ADD KEY `Course_Class_classname_id_fk` (`classname_id`),
  ADD KEY `Course_Class_type_id_fk` (`type_id`),
  ADD KEY `course_class` (`course_code`,`classname_id`) USING BTREE;

--
-- Indexes for table `date`
--
ALTER TABLE `date`
  ADD PRIMARY KEY (`date_id`);

--
-- Indexes for table `department`
--
ALTER TABLE `department`
  ADD PRIMARY KEY (`department_id`);

--
-- Indexes for table `enrollment`
--
ALTER TABLE `enrollment`
  ADD PRIMARY KEY (`enrollment_id`),
  ADD KEY `Enrollment_course_class_id_fk` (`course_class_id`),
  ADD KEY `Enrollment_student_email_fk` (`student_email`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `status`
--
ALTER TABLE `status`
  ADD PRIMARY KEY (`status_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`metric_number`),
  ADD UNIQUE KEY `student_email` (`student_email`),
  ADD UNIQUE KEY `mobile_number` (`mobile_number`),
  ADD UNIQUE KEY `mobile_number_2` (`mobile_number`),
  ADD KEY `Student_department_id_fk` (`department_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `classname`
--
ALTER TABLE `classname`
  MODIFY `classname_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `classtype`
--
ALTER TABLE `classtype`
  MODIFY `classtype_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `course`
--
ALTER TABLE `course`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `course_class`
--
ALTER TABLE `course_class`
  MODIFY `course_class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `date`
--
ALTER TABLE `date`
  MODIFY `date_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `department`
--
ALTER TABLE `department`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `enrollment`
--
ALTER TABLE `enrollment`
  MODIFY `enrollment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `status`
--
ALTER TABLE `status`
  MODIFY `status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `account`
--
ALTER TABLE `account`
  ADD CONSTRAINT `Account_role_id_fk` FOREIGN KEY (`role_id`) REFERENCES `role` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `course`
--
ALTER TABLE `course`
  ADD CONSTRAINT `Courses_dates_id_fk` FOREIGN KEY (`date_id`) REFERENCES `date` (`date_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `Courses_status_id_fk` FOREIGN KEY (`status_id`) REFERENCES `status` (`status_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `course_class`
--
ALTER TABLE `course_class`
  ADD CONSTRAINT `Course_Class_classname_id_fk` FOREIGN KEY (`classname_id`) REFERENCES `classname` (`classname_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `Course_Class_course_code_fk` FOREIGN KEY (`course_code`) REFERENCES `course` (`course_code`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `Course_Class_type_id_fk` FOREIGN KEY (`type_id`) REFERENCES `classtype` (`classtype_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `enrollment`
--
ALTER TABLE `enrollment`
  ADD CONSTRAINT `Enrollment_course_class_id_fk` FOREIGN KEY (`course_class_id`) REFERENCES `course_class` (`course_class_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `Enrollment_student_email_fk` FOREIGN KEY (`student_email`) REFERENCES `student` (`student_email`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `Student_department_id_fk` FOREIGN KEY (`department_id`) REFERENCES `department` (`department_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
