-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 09, 2024 at 01:02 PM
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
(2, 'justin', '$2y$10$Q9npdC4ZRux65FPOnmJJwOx7SrGdq0.df2937l9ecMNZs86USYbp2', 'Justin', 'Balboa', '2X2.png', '2024-02-27'),
(3, 'aldrei', '$2y$10$85XzICn/SG8EkI7YHCXEpuN0D5kDI8x12F2gxvhNip5UUYF.F0Af6', 'Aldrei', 'Bucud', '', '2024-02-27'),
(4, 'chef', '$2y$10$5oA3Xf21vp0O.8eWejY1r.UJWdvhn8qKM.dBlxVfuA7VVODXG.n4O', 'Chef', 'Delizo', 'GHEKI0_aAAAUgDA.jpg', '2024-02-27'),
(5, 'jm', '$2y$10$S5Vr2SwRv0uPWFIn1.ClX.CrQlnNGZTA4NEwHnUy6lWmOPA1fK4UG', 'Johnrick', 'Estrada', '', '2024-02-27'),
(6, 'einnor', '$2y$10$IJPr2lBfq7/ST0xPuKSda.T0sh4JK7fwBSF5FgSVFP1YAJrKFlPGG', 'Einnor', 'Casupanan', '', '2024-02-27');

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
  `shelf_row` varchar(10) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `isbn`, `category_id`, `title`, `author`, `publisher`, `publish_date`, `status`, `shelf_number`, `shelf_row`, `quantity`) VALUES
(4, '0021', 5, 'Effective C++', 'Scott Meyers', 'Hoobstank Publishers', '2018-06-03', 0, 1, '8', 2),
(6, '002', 5, 'Python Cookbook', 'ABCDEFF', 'Jacobs Publisher', '2018-06-01', 0, 1, '9', 2),
(9, '123', 5, 'Java 2', 'Herbert ', 'Demo Publisher', '2018-05-15', 0, 1, '10', 2),
(11, '978-0471787020', 1, 'Introduction to Engineering Mechanics', 'Jenn Stroud Rossmann	', 'Wiley', '2007-03-26', 0, 1, '2', 1),
(12, '978-0190698616', 1, 'Fundamentals of Electrical Engineering', 'Giorgio Rizzoni', 'Oxford University Press', '2011-01-10', 0, 1, '3', 1),
(13, '978-0470239779', 1, 'Civil Engineering Materials', 'Shan Somayaji', 'Prentice Hall', '2007-01-30', 0, 1, '4', 1),
(16, '978-0471218536', 1, 'Principles of Environmental Engineering', 'Mackenzie Davis', 'Wiley', '2005-02-23', 0, 1, '1', 2),
(17, '978-0470418235', 1, 'Introduction to Aerospace Engineering', 'Travis S. Taylor', 'CRC Press', '2004-10-02', 0, 1, '5', 2),
(18, '689', 4, 'Diary ng Panget', 'Marcelo Santos', 'Cornhwa Inc.', '2024-02-05', 0, 1, '7', 0),
(19, '111', 1, 'Yes', 'Yes', 'Yes', '2024-02-20', 0, 1, '6', 1),
(20, '222', 2, 'No', 'No', 'No', '2024-02-01', 0, 3, '1', 1);

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
(117, 25, 19, '2024-02-27', 1, '2024-03-05', 0.00),
(118, 25, 20, '2024-02-27', 1, '2024-02-28', 5.00),
(119, 35, 18, '2024-02-28', 0, '2024-03-06', 0.00),
(120, 35, 18, '2024-02-28', 0, '2024-03-06', 0.00),
(121, 46, 4, '2024-02-28', 1, '2024-03-06', 0.00),
(122, 47, 12, '2024-02-29', 0, '2024-03-06', 0.00),
(123, 25, 11, '2024-03-08', 0, '2024-03-15', 0.00),
(124, 25, 13, '2024-03-08', 0, '2024-03-15', 0.00),
(125, 25, 19, '2024-03-08', 0, '2024-03-15', 0.00),
(126, 25, 20, '2024-03-08', 0, '2024-03-15', 0.00);

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
(7, 'Filipino');

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
-- Table structure for table `data`
--

CREATE TABLE `data` (
  `qrData` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(69, 25, 19, '2024-02-27', NULL),
(70, 46, 4, '2024-02-29', NULL),
(71, 25, 20, '2024-02-29', NULL);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `borrow`
--
ALTER TABLE `borrow`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=127;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `course`
--
ALTER TABLE `course`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `returns`
--
ALTER TABLE `returns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
