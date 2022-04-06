-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 04, 2022 at 12:20 PM
-- Server version: 10.4.21-MariaDB
-- PHP Version: 8.0.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `days_project`
--

-- --------------------------------------------------------

--
-- Table structure for table `areas`
--

CREATE TABLE `areas` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `description` text NOT NULL,
  `place_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `areas`
--

INSERT INTO `areas` (`id`, `name`, `description`, `place_id`) VALUES
(2, 'Testing Area 2', 'This is testing area 2', 2),
(7, 'Testing Area 3', 'This is testing area 3', 4);

-- --------------------------------------------------------

--
-- Table structure for table `machines`
--

CREATE TABLE `machines` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `description` text NOT NULL,
  `area_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `machines`
--

INSERT INTO `machines` (`id`, `name`, `description`, `area_id`) VALUES
(3, 'Test Machine 1', 'Test machine 1', 2),
(4, 'API Test Machine', 'Test machine created by the API', 2);

-- --------------------------------------------------------

--
-- Table structure for table `parts`
--

CREATE TABLE `parts` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `description` text NOT NULL,
  `changed_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `best_before_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `part_type_id` int(11) NOT NULL,
  `machine_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `parts`
--

INSERT INTO `parts` (`id`, `name`, `description`, `changed_date`, `best_before_date`, `part_type_id`, `machine_id`) VALUES
(3, 'Testing part 1', 'Testing part 1, using part template 1', '2022-03-21 15:01:58', '2022-03-21 15:01:58', 1, 3),
(6, 'API Test Machine', 'Test machine created by the API', '2022-03-21 21:27:09', '2022-05-20 21:27:09', 1, 3);

-- --------------------------------------------------------

--
-- Table structure for table `places`
--

CREATE TABLE `places` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `places`
--

INSERT INTO `places` (`id`, `name`, `description`) VALUES
(2, 'Testing Place 5', 'This is testing place 5 and this place also has a pretty long description to read through'),
(3, 'Testing Place 3', 'This is a testing place description. Please be aware'),
(4, 'Testing Place 1', 'This is testing place 1'),
(5, 'Testing Place 4', 'This is testing place 4 and this place has a pretty long description to read through'),
(6, 'Testing Place 6', 'This is testing place 6 and this place has a pretty long description to read through');

-- --------------------------------------------------------

--
-- Table structure for table `types`
--

CREATE TABLE `types` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `description` text NOT NULL,
  `validity` int(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `types`
--

INSERT INTO `types` (`id`, `name`, `description`, `validity`) VALUES
(1, 'Part type 1', 'This is part type 1', 60);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` text NOT NULL,
  `email` text NOT NULL,
  `password` text NOT NULL,
  `admin` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `admin`) VALUES
(1, 'administrator', 'admin@daysproject.com', '$2y$10$KMGjI8.Vl.Mp8Iq48KByQe1DgBiEQ7O/C9pPhJLlWSwnZSyVlfZXm', 1),
(4, 'joseph222', 'facebooklee52@gmail.com', '$2y$10$mi6GK.fOEt46wDZ.OoCsrOKUumIek5uONm2lGKfRPpwVzp00dbhD6', 1),
(6, 'facebooklee52', 'facebooklee52@gmail.com', '$2y$10$sHAy4eXdOecEaGQA6OAIueDX3XKZa1FqFvfOaAT4V/KIotLrWrwEW', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `areas`
--
ALTER TABLE `areas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `place_id` (`place_id`);

--
-- Indexes for table `machines`
--
ALTER TABLE `machines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `area_id` (`area_id`);

--
-- Indexes for table `parts`
--
ALTER TABLE `parts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `machine_id` (`machine_id`),
  ADD KEY `part_type` (`part_type_id`);

--
-- Indexes for table `places`
--
ALTER TABLE `places`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `types`
--
ALTER TABLE `types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`) USING HASH;

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `areas`
--
ALTER TABLE `areas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `machines`
--
ALTER TABLE `machines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `parts`
--
ALTER TABLE `parts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `places`
--
ALTER TABLE `places`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `types`
--
ALTER TABLE `types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `areas`
--
ALTER TABLE `areas`
  ADD CONSTRAINT `areas_ibfk_1` FOREIGN KEY (`place_id`) REFERENCES `places` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `machines`
--
ALTER TABLE `machines`
  ADD CONSTRAINT `machines_ibfk_1` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `parts`
--
ALTER TABLE `parts`
  ADD CONSTRAINT `parts_ibfk_1` FOREIGN KEY (`machine_id`) REFERENCES `machines` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `parts_ibfk_2` FOREIGN KEY (`part_type_id`) REFERENCES `types` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
