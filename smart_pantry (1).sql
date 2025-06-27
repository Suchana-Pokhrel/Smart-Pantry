-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 27, 2025 at 04:32 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `smart_pantry`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `email`, `password`) VALUES
(1, 'admin@example.com', 'admin123');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` bigint(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `expiry_date` date DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `price` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `name`, `quantity`, `expiry_date`, `category`, `image`, `created_at`, `updated_at`, `price`) VALUES
(12, 'Meat', 6, '2025-06-30', 'Protein', 'meat.jpeg', '2025-06-27 18:14:17', NULL, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `items` text NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `order_date` datetime NOT NULL DEFAULT current_timestamp(),
  `cancel_reason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_name`, `address`, `phone`, `items`, `status`, `order_date`, `cancel_reason`) VALUES
(4, 'Suchana Pokhrel', 'Kapan', '9861212758', 'chilli (x1)', 'Processing', '2025-06-27 08:13:52', ''),
(5, 'Suchana Pokhrel', 'Kapan', '9861212758', 'Butter (x5)', 'Pending', '2025-06-27 19:43:12', NULL),
(6, 'Suchana Pokhrel', 'Kapan', '9861212758', 'Butter (x1)', 'Pending', '2025-06-27 19:45:10', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `image` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `quantity`, `image`) VALUES
(8, 'Butter', 3000.00, 3, 'butter.jpeg');

-- --------------------------------------------------------

--
-- Table structure for table `recipes`
--

CREATE TABLE `recipes` (
  `id` bigint(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `ingredients` text NOT NULL,
  `instructions` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipes`
--

INSERT INTO `recipes` (`id`, `name`, `ingredients`, `instructions`, `created_at`) VALUES
(1, 'Butter', 'Butter', 'dfghjk', '2025-06-19 12:33:47');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `confirm_password` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `confirm_password`, `created_at`) VALUES
(1, 'Suchana', 'Suchana@gmail.com', 'suchana', 'suchana', '2025-06-19 12:45:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `recipes`
--
ALTER TABLE `recipes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `recipes`
--
ALTER TABLE `recipes`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
