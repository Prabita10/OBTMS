-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Aug 21, 2026 at 02:47 PM
-- Server version: 8.0.44
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `obtms`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `fullname` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `fullname`) VALUES
(1, 'admin', 'Admin@10', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `schedule_id` int NOT NULL,
  `seat_number` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('pending','confirmed','canceled') COLLATE utf8mb4_general_ci DEFAULT 'confirmed',
  `booking_time` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `username`, `schedule_id`, `seat_number`, `status`, `booking_time`) VALUES
(66, 'prabita', 7, '2', 'confirmed', '2026-08-15 00:25:24'),
(68, 'prabita', 8, '2', 'confirmed', '2026-08-15 00:41:48'),
(69, 'prabita', 8, '5', 'confirmed', '2026-08-15 00:41:48'),
(70, 'prabita', 8, '6', 'confirmed', '2026-08-15 00:41:48'),
(71, 'prabita', 8, '9', 'confirmed', '2026-08-15 00:41:48'),
(72, 'prabita', 8, '10', 'confirmed', '2026-08-15 00:41:48'),
(73, 'prabita', 8, '13', 'confirmed', '2026-08-15 00:41:48'),
(74, 'prabita', 8, '14', 'confirmed', '2026-08-15 00:41:48'),
(75, 'prabita', 8, '18', 'confirmed', '2026-08-15 00:41:48'),
(76, 'prabita', 8, '21', 'confirmed', '2026-08-15 00:41:48'),
(77, 'prabita', 8, '22', 'confirmed', '2026-08-15 00:41:48'),
(78, 'prabita', 8, '17', 'confirmed', '2026-08-15 00:41:48'),
(79, 'prabita', 8, '26', 'confirmed', '2026-08-15 00:41:48'),
(80, 'prabita', 8, '25', 'confirmed', '2026-08-15 00:41:48'),
(81, 'prabita', 8, '29', 'confirmed', '2026-08-15 00:41:48'),
(82, 'prabita', 8, '30', 'confirmed', '2026-08-15 00:41:48'),
(83, 'prabita', 8, '34', 'confirmed', '2026-08-15 00:41:48'),
(84, 'prabita', 8, '33', 'confirmed', '2026-08-15 00:41:48'),
(85, 'prabita', 8, '37', 'confirmed', '2026-08-15 00:41:48'),
(86, 'prabita', 8, '38', 'confirmed', '2026-08-15 00:41:48'),
(87, 'prabita', 8, '39', 'confirmed', '2026-08-15 00:41:48'),
(88, 'prabita', 8, '40', 'confirmed', '2026-08-15 00:41:48'),
(89, 'prabita', 8, '36', 'confirmed', '2026-08-15 00:41:48'),
(90, 'prabita', 8, '35', 'confirmed', '2026-08-15 00:41:48'),
(91, 'prabita', 8, '31', 'confirmed', '2026-08-15 00:41:48'),
(92, 'prabita', 8, '27', 'confirmed', '2026-08-15 00:41:48'),
(93, 'prabita', 8, '28', 'confirmed', '2026-08-15 00:41:48'),
(94, 'prabita', 8, '32', 'confirmed', '2026-08-15 00:41:48'),
(95, 'prabita', 8, '24', 'confirmed', '2026-08-15 00:41:48'),
(96, 'prabita', 8, '23', 'confirmed', '2026-08-15 00:41:48'),
(97, 'prabita', 8, '19', 'confirmed', '2026-08-15 00:41:48'),
(98, 'prabita', 8, '20', 'confirmed', '2026-08-15 00:41:48'),
(99, 'prabita', 8, '16', 'confirmed', '2026-08-15 00:41:48'),
(100, 'prabita', 8, '15', 'confirmed', '2026-08-15 00:41:48'),
(101, 'prabita', 8, '11', 'confirmed', '2026-08-15 00:41:48'),
(102, 'prabita', 8, '12', 'confirmed', '2026-08-15 00:41:48'),
(103, 'prabita', 8, '8', 'confirmed', '2026-08-15 00:41:48'),
(104, 'prabita', 8, '7', 'confirmed', '2026-08-15 00:41:48'),
(105, 'prabita', 8, '3', 'confirmed', '2026-08-15 00:41:48'),
(106, 'prabita', 8, '4', 'confirmed', '2026-08-15 00:41:48'),
(107, 'prabita', 9, '3', 'confirmed', '2026-08-15 00:51:14'),
(108, 'prabita', 10, '1', 'confirmed', '2026-08-16 21:40:12'),
(110, 'prabita', 10, '2', 'confirmed', '2026-08-16 21:42:14'),
(111, 'prabita', 10, '6', 'confirmed', '2026-08-16 21:42:14'),
(112, 'prabita', 10, '3', 'confirmed', '2026-08-17 10:08:26');

-- --------------------------------------------------------

--
-- Table structure for table `buses`
--

CREATE TABLE `buses` (
  `bus_id` int NOT NULL,
  `bus_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `total_seats` int NOT NULL,
  `fare` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `buses`
--

INSERT INTO `buses` (`bus_id`, `bus_name`, `type`, `total_seats`, `fare`) VALUES
(1, 'Express Lined', 'AC', 40, 1199.95),
(2, 'Mountain Rider', 'Non-AC', 30, 800.00),
(3, 'City Shuttle', 'AC', 50, 1000.00),
(7, 'My Travels', 'AC', 20, 300.00),
(8, 'Deluxe', 'AC', 30, 500.00);

-- --------------------------------------------------------

--
-- Table structure for table `cancel_feedback`
--

CREATE TABLE `cancel_feedback` (
  `id` int NOT NULL,
  `booking_id` int NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `feedback` text COLLATE utf8mb4_general_ci,
  `cancel_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cancel_feedback`
--

INSERT INTO `cancel_feedback` (`id`, `booking_id`, `username`, `feedback`, `cancel_time`) VALUES
(1, 64, 'prabita', 'i dont want this booking.', '2026-08-15 00:19:46'),
(2, 65, 'prabita', 'i dont want this .', '2026-08-15 00:27:01'),
(3, 67, 'prabita', 'not interested', '2026-08-16 12:20:01'),
(4, 109, 'prabita', 'misbooked', '2026-08-16 21:44:39');

-- --------------------------------------------------------

--
-- Table structure for table `routes`
--

CREATE TABLE `routes` (
  `route_id` int NOT NULL,
  `source` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `destination` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `stops` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `routes`
--

INSERT INTO `routes` (`route_id`, `source`, `destination`, `stops`) VALUES
(1, 'Kathmandu', 'Pokhara', 'Dhulikhel, Muglin'),
(2, 'Kathmandu', 'Chitwan', 'Hetauda, Narayangarh,Sarlahi'),
(3, 'Pokhara', 'Butwal', 'Tansen, Bhairahawa'),
(7, 'damauli', 'pokhara', ''),
(8, 'Dang', 'Mustang', ''),
(9, 'baglung', 'tanahun', ''),
(10, 'palpa', 'dolpa', ''),
(11, 'kathmandu', 'butwal', 'thankot,muglin');

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `schedule_id` int NOT NULL,
  `bus_id` int NOT NULL,
  `route_id` int NOT NULL,
  `departure_time` datetime NOT NULL,
  `arrival_time` datetime NOT NULL,
  `available_seats` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`schedule_id`, `bus_id`, `route_id`, `departure_time`, `arrival_time`, `available_seats`) VALUES
(2, 2, 2, '2026-06-28 14:24:00', '2026-06-28 12:30:00', 30),
(3, 3, 3, '2026-06-15 08:00:00', '2026-06-15 14:00:00', 43),
(6, 7, 7, '2026-06-20 05:00:00', '2026-06-20 09:00:00', 20),
(7, 1, 8, '2026-08-16 00:21:00', '2026-08-16 02:22:00', 24),
(8, 1, 9, '2026-08-16 00:34:00', '2026-08-17 00:35:00', 1),
(9, 1, 9, '2026-08-20 00:49:00', '2026-08-21 00:49:00', 39),
(10, 8, 10, '2026-08-20 21:32:00', '2026-08-21 21:32:00', 36);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int NOT NULL,
  `fullname` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `fullname`, `email`, `phone`, `username`, `password`) VALUES
(5, 'Pele', 'pele363@gmail.com', '9846098802', 'pele', 'Pele@10'),
(6, 'kiran Ghimire', 'kiranghimire363@gmail.com', '9843466364', 'kiran', 'Kiran@10'),
(7, 'Saugat', 'saugat@gmail.com', '9876543212', 'saugat', 'Saugat@10'),
(8, 'Prabita Adhikari', 'prabitadhikari792@gmail.com', '9816109990', 'prabita', '$2y$10$nyZ3Z1eJoUXNVnF0963uTeBPIUskbfM0ttfFW4aTdaujJU8axjLpa'),
(9, 'Evana1234', 'evana123@gmail.com', '9806520318', 'evana', 'Evana@10'),
(12, 'Shine Chhetri', 'shine@gmail.com', '9816109990', 'shine123', 'Shine@10'),
(13, 'Ramala Adhikari', 'ramala1@gmail.com', '9816109990', 'ramala', 'Ramala@10'),
(14, 'roman', 'roman@gmail.com', '9841234567', 'roman', 'Roman@1234');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD UNIQUE KEY `unique_seat_per_schedule` (`schedule_id`,`seat_number`),
  ADD KEY `username` (`username`);

--
-- Indexes for table `buses`
--
ALTER TABLE `buses`
  ADD PRIMARY KEY (`bus_id`);

--
-- Indexes for table `cancel_feedback`
--
ALTER TABLE `cancel_feedback`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `routes`
--
ALTER TABLE `routes`
  ADD PRIMARY KEY (`route_id`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `bus_id` (`bus_id`),
  ADD KEY `route_id` (`route_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT for table `buses`
--
ALTER TABLE `buses`
  MODIFY `bus_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `cancel_feedback`
--
ALTER TABLE `cancel_feedback`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `routes`
--
ALTER TABLE `routes`
  MODIFY `route_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `schedule_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`username`) REFERENCES `user` (`username`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`schedule_id`) ON DELETE CASCADE;

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`bus_id`) REFERENCES `buses` (`bus_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `schedules_ibfk_2` FOREIGN KEY (`route_id`) REFERENCES `routes` (`route_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
