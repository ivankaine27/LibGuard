-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 26, 2024 at 02:07 PM
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
-- Database: `libguard`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(30) NOT NULL,
  `password` varchar(60) NOT NULL,
  `firstname` varchar(30) NOT NULL,
  `lastname` varchar(30) NOT NULL,
  `photo` varchar(200) NOT NULL,
  `created_on` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `firstname`, `lastname`, `photo`, `created_on`) VALUES
(1, 'ivankaine', '$2y$10$iY6xgNbO9sJ5UejY3ktKOux.eFu13yLOf5mrM4EmEWhNk73MHhpSO', 'Ivan Kaine ', 'Bulaun', '', '2024-01-23'),
(2, 'jm', '$2y$10$0VHUWahUX7OlHzvLCenvdeNTOc20EyPQXh1eqhh0SeZyjapb5tdbe', 'J', 'M', '', '2024-01-24');

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `isbn` varchar(20) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` text NOT NULL,
  `author` varchar(150) NOT NULL,
  `publisher` varchar(150) NOT NULL,
  `publish_date` date NOT NULL,
  `status` int(1) NOT NULL,
  `shelf_number` int(11) NOT NULL,
  `shelf_row` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `isbn`, `category_id`, `title`, `author`, `publisher`, `publish_date`, `status`, `shelf_number`, `shelf_row`) VALUES
(4, '0021', 5, 'Effective C++', 'Scott Meyers', 'Hoobstank Publishers', '2018-06-03', 0, 1, '8'),
(6, '002', 5, 'Python Cookbook', 'ABCDEFF', 'Jacobs Publisher', '2018-06-01', 0, 1, '9'),
(9, '123', 5, 'Java 2', 'Herbert ', 'Demo Publisher', '2018-05-15', 0, 1, '10'),
(11, '978-0471787020', 1, 'Introduction to Engineering Mechanics', 'Jenn Stroud Rossmann	', 'Wiley', '2007-03-26', 0, 1, '2'),
(12, '978-0190698616', 1, 'Fundamentals of Electrical Engineering', 'Giorgio Rizzoni', 'Oxford University Press', '2011-01-10', 0, 1, '3'),
(13, '978-0470239779', 1, 'Civil Engineering Materials', 'Shan Somayaji', 'Prentice Hall', '2007-01-30', 0, 1, '4'),
(16, '978-0471218536', 1, 'Principles of Environmental Engineering', 'Mackenzie Davis', 'Wiley', '2005-02-23', 0, 1, '1'),
(17, '978-0470418235', 1, 'Introduction to Aerospace Engineering', 'Travis S. Taylor', 'CRC Press', '2004-10-02', 0, 1, '5'),
(18, '689', 4, 'Diary ng Panget', 'Marcelo Santos', 'Cornhwa Inc.', '2024-02-05', 0, 1, '7'),
(19, '111', 1, 'Yes', 'Yes', 'Yes', '2024-02-20', 0, 1, '6'),
(20, '222', 2, 'No', 'No', 'No', '2024-02-01', 0, 3, '1');

-- --------------------------------------------------------

--
-- Table structure for table `borrow`
--

CREATE TABLE `borrow` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `date_borrow` date NOT NULL,
  `status` int(1) NOT NULL,
  `due_date` date DEFAULT NULL,
  `penalty` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `borrow`
--

INSERT INTO `borrow` (`id`, `student_id`, `book_id`, `date_borrow`, `status`, `due_date`, `penalty`) VALUES
(53, 25, 9, '2024-02-17', 1, '0000-00-00', 0.00),
(54, 25, 6, '2024-02-17', 1, '2024-02-27', 0.00),
(56, 25, 12, '2024-02-17', 1, '2024-02-23', 197779.58),
(57, 25, 13, '2024-02-17', 1, '2024-02-24', 197779.58),
(58, 25, 16, '2024-02-17', 1, '2024-02-22', 197779.58),
(63, 25, 9, '2024-02-17', 1, '2024-02-23', 0.00),
(64, 25, 6, '2024-02-17', 1, '2024-02-24', 0.00),
(65, 25, 9, '2024-02-18', 1, '2024-02-25', 0.00),
(66, 25, 9, '2024-02-18', 1, '2024-02-25', 0.00),
(67, 25, 9, '2024-02-20', 1, '2024-02-26', 0.00),
(68, 25, 9, '2024-02-20', 1, '2024-02-26', 0.00),
(69, 25, 9, '2024-02-20', 1, '2024-02-26', 0.00),
(70, 25, 9, '2024-02-20', 1, '2024-02-26', 0.00),
(71, 25, 9, '2024-02-20', 1, '2024-02-26', 0.00),
(72, 25, 9, '2024-02-20', 1, '2024-02-26', 0.00),
(73, 25, 9, '2024-02-20', 1, '2024-02-27', 0.00),
(74, 25, 9, '2024-02-20', 1, '2024-02-27', 0.00),
(80, 25, 9, '2024-02-23', 1, '2024-02-24', 0.00),
(82, 25, 20, '2024-02-23', 1, '2024-02-24', 0.00),
(83, 25, 9, '2024-02-25', 1, '2024-02-22', 197780.00),
(84, 25, 19, '2024-02-25', 1, '2024-02-21', 197779.58),
(85, 25, 20, '2024-02-25', 1, '2024-02-21', 197779.58),
(86, 25, 18, '2024-02-25', 1, '2024-02-21', 40.00),
(87, 25, 11, '2024-02-25', 1, '2024-02-21', 197779.58),
(88, 25, 19, '2024-02-26', 1, '2024-03-04', 50.00),
(89, 25, 19, '2024-02-26', 1, '2024-03-04', 50.00),
(90, 25, 20, '2024-02-26', 1, '2024-03-04', 20.00),
(91, 25, 19, '2024-02-26', 1, '2024-03-04', 50.00),
(92, 25, 19, '2024-02-26', 1, '2024-03-04', 0.00),
(93, 25, 20, '2024-02-26', 1, '2024-03-04', 0.00),
(94, 25, 9, '2024-02-26', 1, '2024-03-04', 0.00),
(95, 25, 19, '2024-02-26', 1, '2024-02-25', 5.00);

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`id`, `name`) VALUES
(1, 'Engineering'),
(2, 'Mathematics'),
(3, 'Science and Technology'),
(4, 'History'),
(5, 'IT Programming'),
(6, 'Cornhwa');

-- --------------------------------------------------------

--
-- Table structure for table `course`
--

CREATE TABLE `course` (
  `id` int(11) NOT NULL,
  `title` text NOT NULL,
  `code` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `course`
--

INSERT INTO `course` (`id`, `title`, `code`) VALUES
(1, 'Bachelor of Science in Information Systems', 'BSIS'),
(2, 'Bachelor of Science in Computer Science', 'BSCS'),
(3, 'Bachelor of Science in Information Technology', 'BSIT'),
(4, 'Bachelor of Science in Electronics Engineering', 'BSECE');

-- --------------------------------------------------------

--
-- Table structure for table `returns`
--

CREATE TABLE `returns` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `date_return` date NOT NULL,
  `due_date` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `returns`
--

INSERT INTO `returns` (`id`, `student_id`, `book_id`, `date_return`, `due_date`) VALUES
(22, 25, 18, '2024-02-17', NULL),
(23, 25, 9, '2024-02-17', NULL),
(24, 25, 9, '2024-02-17', NULL),
(25, 25, 6, '2024-02-17', NULL),
(26, 25, 9, '2024-02-18', NULL),
(27, 25, 9, '2024-02-18', NULL),
(28, 25, 9, '2024-02-20', NULL),
(29, 25, 9, '2024-02-20', NULL),
(30, 25, 9, '2024-02-20', NULL),
(31, 25, 9, '2024-02-20', NULL),
(32, 25, 9, '2024-02-20', NULL),
(33, 25, 9, '2024-02-20', NULL),
(34, 25, 9, '2024-02-20', NULL),
(35, 25, 19, '2024-02-25', NULL),
(36, 25, 19, '2024-02-25', NULL),
(37, 25, 9, '2024-02-25', NULL),
(38, 25, 6, '2024-02-25', NULL),
(39, 25, 19, '2024-02-25', NULL),
(40, 25, 9, '2024-02-25', NULL),
(41, 25, 20, '2024-02-25', NULL),
(42, 25, 12, '2024-02-25', NULL),
(43, 25, 16, '2024-02-25', NULL),
(44, 25, 13, '2024-02-25', NULL),
(45, 25, 9, '2024-02-25', NULL),
(46, 25, 9, '2024-02-25', NULL),
(47, 25, 19, '2024-02-25', NULL),
(48, 25, 20, '2024-02-25', NULL),
(49, 25, 11, '2024-02-25', NULL),
(50, 25, 18, '2024-02-25', NULL),
(51, 25, 19, '2024-02-26', NULL),
(52, 25, 19, '2024-02-26', NULL),
(53, 25, 19, '2024-02-26', NULL),
(54, 25, 20, '2024-02-26', NULL),
(55, 25, 19, '2024-02-26', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `photo` varchar(200) NOT NULL,
  `course_id` int(11) NOT NULL,
  `created_on` date NOT NULL,
  `email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `student_id`, `firstname`, `lastname`, `photo`, `course_id`, `created_on`, `email`) VALUES
(25, '2020101143', 'Ivan ', 'Pogi', '', 4, '2024-02-07', '2020101143@dhvsu.edu.ph'),
(26, '123456', 'John', 'Doe', '', 4, '2024-02-10', '123456@dhvsu.edu.ph'),
(27, '234567', 'Jane', 'Smithh', '', 3, '2024-02-10', '234567@dhvsu.edu.ph'),
(28, '345678', 'Emily', 'Johnson', '', 4, '2024-02-10', '345678@dhvsu.edu.ph'),
(29, '456789', 'Michael', 'Brown', '', 4, '2024-02-10', '456789@dhvsu.edu.ph'),
(30, '567890', 'Sarah', 'Williams', '', 4, '2024-02-10', '567890@dhvsu.edu.ph'),
(31, '678901', 'David', 'Miller', '', 1, '2024-02-10', '678901@dhvsu.edu.ph'),
(33, '890123', 'Matthew', 'Wilson', '', 2, '2024-02-10', '890123@dhvsu.edu.ph'),
(34, '901234', 'Ashley', 'Martinez', '', 4, '2024-02-10', '901234@dhvsu.edu.ph'),
(35, '912345', 'Christopher', 'Anderson', '', 3, '2024-02-10', '912345@dhvsu.edu.ph'),
(36, '923456', 'Amanda', 'Taylor', '', 4, '2024-02-10', '923456@dhvsu.edu.ph'),
(37, '934567', 'James', 'Thompson', '', 1, '2024-02-10', '934567@dhvsu.edu.ph'),
(38, '	945678', 'Lauren', 'Thomas', '', 3, '2024-02-10', '	945678@dhvsu.edu.ph'),
(39, '956789', 'Ryan', 'Hernandez', '', 2, '2024-02-10', '956789@dhvsu.edu.ph'),
(41, '967890', 'Elizabeth', 'Moore', '', 3, '2024-02-10', '967890@dhvsu.edu.ph'),
(45, '2020101141', 'Aldrei', 'Bucud', '', 4, '2024-02-10', '2020101141@dhvsu.edu.ph'),
(46, '2020101169', 'Chef Ray', 'Delizo', '', 4, '2024-02-10', '2020101169@dhvsu.edu.ph'),
(47, '2020101132', 'Justin', 'Balboa', '', 4, '2024-02-10', '2020101132@dhvsu.edu.ph'),
(49, '202000204', 'Bb', 'Bilog', '', 1, '2024-02-12', '202000204@dhvsu.edu.ph');

--
-- Triggers `students`
--
DELIMITER $$
CREATE TRIGGER `update_email_trigger` BEFORE INSERT ON `students` FOR EACH ROW BEGIN
    SET NEW.email = CONCAT(NEW.student_id, '@dhvsu.edu.ph');
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `borrow`
--
ALTER TABLE `borrow`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `course`
--
ALTER TABLE `course`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `returns`
--
ALTER TABLE `returns`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `borrow`
--
ALTER TABLE `borrow`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `course`
--
ALTER TABLE `course`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `returns`
--
ALTER TABLE `returns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
