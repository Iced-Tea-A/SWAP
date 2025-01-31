-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 31, 2025 at 09:29 AM
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
('', 'aizrylproject@gmail.com', 4, ''),
('A001', 'alice@example.com', 4, 'password123'),
('A002', 'bob@example.com', 3, 'password456'),
('A003', 'charlie@example.com', 1, '1'),
('T1', 'at@a.com', 1, '1'),
('T2', 'ft@a.com', 2, '1'),
('T3', 'st@a.com', 3, '1');

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
(5, 'Class B'),
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
(13, 'C23R04', 5, 2);

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

-- --------------------------------------------------------

--
-- Table structure for table `one_time_password`
--

CREATE TABLE `one_time_password` (
  `email` varchar(255) NOT NULL,
  `generatedpassword` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `one_time_password`
--

INSERT INTO `one_time_password` (`email`, `generatedpassword`, `created_at`) VALUES
('aizrylproject@gmail.com', 'e&mtK*GX', '2025-01-31 07:49:50'),
('st@a.com', '*OTo;Du2', '2025-01-31 07:19:36');

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
(1, 'Admin'),
(2, 'Faculty'),
(3, 'Student'),
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
(2, 'Completed');

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
('alice@example.com', 'Alice Johnson', '9876543210', '7146317A', 1),
('bob@example.com', 'Bob Smith', '9876543211', '7146317B', 2),
('charlie@example.com', 'Charlie Davis', '9876543212', '7146317C', 3);

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
  ADD UNIQUE KEY `course_class` (`course_code`,`classname_id`),
  ADD KEY `Course_Class_classname_id_fk` (`classname_id`),
  ADD KEY `Course_Class_type_id_fk` (`type_id`);

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
-- Indexes for table `one_time_password`
--
ALTER TABLE `one_time_password`
  ADD PRIMARY KEY (`email`);

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
  MODIFY `classname_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
  MODIFY `course_class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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
  MODIFY `enrollment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `status`
--
ALTER TABLE `status`
  MODIFY `status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
-- Constraints for table `one_time_password`
--
ALTER TABLE `one_time_password`
  ADD CONSTRAINT `fk_forget_email` FOREIGN KEY (`email`) REFERENCES `account` (`email`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `Student_department_id_fk` FOREIGN KEY (`department_id`) REFERENCES `department` (`department_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
