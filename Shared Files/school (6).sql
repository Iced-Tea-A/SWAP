-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 18, 2025 at 01:57 PM
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
CREATE DATABASE IF NOT EXISTS `school` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `school`;

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
('A001', 'alice@example.com', 3, 'password123'),
('A002', 'bob@example.com', 3, 'password456'),
('A003', 'charlie@example.com', 2, 'password789');

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
(1, 'Class A'),
(2, 'Class B'),
(3, 'Class C');

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
(3, 'Lab'),
(1, 'Lecture'),
(2, 'Tutorial');

-- --------------------------------------------------------

--
-- Table structure for table `course`
--

CREATE TABLE `course` (
  `course_code` varchar(500) NOT NULL,
  `name` varchar(500) NOT NULL,
  `date_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course`
--

INSERT INTO `course` (`course_code`, `name`, `date_id`, `status_id`) VALUES
('CS101', 'Introduction to Programming', 1, 1),
('EE202', 'Circuit Analysis', 1, 1),
('ME303', 'Thermodynamics', 2, 2);

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
(1, 'CS101', 1, 1),
(2, 'EE202', 2, 2),
(3, 'ME303', 3, 3);

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
(1, '2025-01-01', '2025-06-01'),
(2, '2025-07-01', '2025-12-01');

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
(1, 'alice@example.com', 1),
(2, 'bob@example.com', 2),
(3, 'charlie@example.com', 3);

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
(3, 'Student');

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
  ADD PRIMARY KEY (`course_code`),
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
  ADD PRIMARY KEY (`date_id`),
  ADD UNIQUE KEY `start` (`start`),
  ADD UNIQUE KEY `end` (`end`);

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
  MODIFY `classname_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `classtype`
--
ALTER TABLE `classtype`
  MODIFY `classtype_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `course_class`
--
ALTER TABLE `course_class`
  MODIFY `course_class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `date`
--
ALTER TABLE `date`
  MODIFY `date_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `department`
--
ALTER TABLE `department`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `enrollment`
--
ALTER TABLE `enrollment`
  MODIFY `enrollment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  ADD CONSTRAINT `Account_email_fk` FOREIGN KEY (`email`) REFERENCES `student` (`student_email`) ON DELETE CASCADE ON UPDATE CASCADE,
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
