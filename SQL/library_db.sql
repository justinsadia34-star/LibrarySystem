-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 10, 2025 at 09:24 AM
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
-- Database: `library_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `student_number` varchar(50) NOT NULL,
  `action` varchar(255) NOT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `student_number`, `action`, `timestamp`) VALUES
(12, '21-22-038', 'Logged in', '2025-11-10 15:57:51'),
(13, '21-22-038', 'Visited Student Purpose Page', '2025-11-10 15:57:52'),
(14, '21-22-038', 'Visited Student Purpose Page', '2025-11-10 16:08:32'),
(15, '21-22-038', 'Visited Student Purpose Page', '2025-11-10 16:09:51'),
(16, '21-22-038', 'Visited Student Purpose Page', '2025-11-10 16:09:59'),
(17, '21-22-031', 'Logged in', '2025-11-10 16:19:19'),
(18, '21-22-031', 'Visited Student Purpose Page', '2025-11-10 16:19:19'),
(19, '21-22-031', 'Visited Student Purpose Page', '2025-11-10 16:19:34'),
(20, '21-22-031', 'Visited Student Purpose Page', '2025-11-10 16:19:38');

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `author` varchar(255) DEFAULT NULL,
  `publisher` varchar(255) DEFAULT NULL,
  `publish_date` date DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `total_copies` int(11) DEFAULT 1,
  `available_copies` int(11) DEFAULT 1,
  `status` enum('Available','Borrowed') DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `title`, `author`, `publisher`, `publish_date`, `category`, `cover_image`, `total_copies`, `available_copies`, `status`) VALUES
(1, 'Crime and Punishment', 'Dostoyvsky', 'Penguin Publishing', '2025-11-10', 'Fiction', NULL, 4, 0, 'Available'),
(2, 'Metamorphosis', 'Kafka', 'Penguin Publishing', '2025-11-05', 'Fiction', NULL, 1, 0, 'Available');

-- --------------------------------------------------------

--
-- Table structure for table `borrowed_books`
--

CREATE TABLE `borrowed_books` (
  `id` int(11) NOT NULL,
  `book_id` int(11) DEFAULT NULL,
  `student_number` varchar(50) DEFAULT NULL,
  `borrow_date` datetime DEFAULT current_timestamp(),
  `return_date` datetime DEFAULT NULL,
  `status` enum('Borrowed','Returned') DEFAULT 'Borrowed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrowed_books`
--

INSERT INTO `borrowed_books` (`id`, `book_id`, `student_number`, `borrow_date`, `return_date`, `status`) VALUES
(2, 1, '21-22-031', '2025-11-10 16:19:28', NULL, 'Borrowed');

-- --------------------------------------------------------

--
-- Table structure for table `borrowed_thesis`
--

CREATE TABLE `borrowed_thesis` (
  `id` int(11) NOT NULL,
  `thesis_id` int(11) DEFAULT NULL,
  `student_number` varchar(20) DEFAULT NULL,
  `borrow_date` datetime DEFAULT NULL,
  `status` enum('Borrowed','Returned') DEFAULT 'Borrowed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login`
--

CREATE TABLE `login` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `age` int(11) NOT NULL,
  `course` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sign_up`
--

CREATE TABLE `sign_up` (
  `id` int(11) NOT NULL,
  `student_number` varchar(50) NOT NULL,
  `course` varchar(100) NOT NULL,
  `year_level` varchar(20) NOT NULL,
  `section` varchar(50) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_initial` varchar(10) DEFAULT NULL,
  `suffix` varchar(20) DEFAULT NULL,
  `birthday` date NOT NULL,
  `sex` enum('Male','Female') NOT NULL,
  `contact_no` varchar(20) NOT NULL,
  `home_address` text NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `confirm_password` varchar(255) NOT NULL,
  `date_registered` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sign_up`
--

INSERT INTO `sign_up` (`id`, `student_number`, `course`, `year_level`, `section`, `last_name`, `first_name`, `middle_initial`, `suffix`, `birthday`, `sex`, `contact_no`, `home_address`, `email`, `password`, `confirm_password`, `date_registered`) VALUES
(1, '21-22-038', 'COL', '2', '2', 'Sad', 'Jas', 'R', 'n/a', '2025-11-09', 'Male', '09123456789', 'tondo', 'jazzsad@gmail.com', '$2y$10$hCvJece5ikDxOX3a/m5PfeyaiIoXDexyepHRutWLlN8wuF3svgev6', '$2y$10$hCvJece5ikDxOX3a/m5PfeyaiIoXDexyepHRutWLlN8wuF3svgev6', '2025-11-09 13:56:51'),
(2, '21-22-031', 'CHS', '2', '1', 'Tag', 'Bry', 'A', 'n/a', '2004-01-10', 'Male', '09912345678', 'tondo', 'tag34@gmail.com', '$2y$10$CEgFwQchirFeoxBhmVB05.k3hQuHELGMgXmrvL6kM0mGCuVZC2ncy', '$2y$10$CEgFwQchirFeoxBhmVB05.k3hQuHELGMgXmrvL6kM0mGCuVZC2ncy', '2025-11-10 08:18:49');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `student_number` varchar(50) DEFAULT NULL,
  `college` varchar(50) DEFAULT NULL,
  `purpose` varchar(100) DEFAULT NULL,
  `time_duration` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `thesis`
--

CREATE TABLE `thesis` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `author` varchar(255) DEFAULT NULL,
  `publisher` varchar(255) DEFAULT NULL,
  `publish_date` date DEFAULT NULL,
  `available_copies` int(11) DEFAULT 1,
  `cover_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `borrowed_books`
--
ALTER TABLE `borrowed_books`
  ADD PRIMARY KEY (`id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `borrowed_thesis`
--
ALTER TABLE `borrowed_thesis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `thesis_id` (`thesis_id`);

--
-- Indexes for table `login`
--
ALTER TABLE `login`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sign_up`
--
ALTER TABLE `sign_up`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `thesis`
--
ALTER TABLE `thesis`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `borrowed_books`
--
ALTER TABLE `borrowed_books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `borrowed_thesis`
--
ALTER TABLE `borrowed_thesis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sign_up`
--
ALTER TABLE `sign_up`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `thesis`
--
ALTER TABLE `thesis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `borrowed_books`
--
ALTER TABLE `borrowed_books`
  ADD CONSTRAINT `borrowed_books_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`);

--
-- Constraints for table `borrowed_thesis`
--
ALTER TABLE `borrowed_thesis`
  ADD CONSTRAINT `borrowed_thesis_ibfk_1` FOREIGN KEY (`thesis_id`) REFERENCES `thesis` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
