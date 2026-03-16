-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 03, 2026 at 05:29 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


-- Database: `HiddenCoreCafe`
--

-- --------------------------------------------------------

--
-- Table structure for table `cashier_staff`
--

CREATE TABLE `cashier_staff` (
  `id` int(3) NOT NULL,
  `first_name` varchar(191) NOT NULL,
  `middle_name` varchar(191) NOT NULL,
  `last_name` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `username` varchar(191) NOT NULL,
  `password` varchar(191) NOT NULL,
  `position` varchar(191) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cashier_staff`
--

INSERT INTO `cashier_staff` (`id`, `first_name`, `middle_name`, `last_name`, `email`, `username`, `password`, `position`, `created_at`) VALUES
(1, 'User', 'User', 'User', 'Kopikuys@gmail.com', 'Kopikuys', '$2y$10$kDuHHqwwkWaN8tPMF32da.x4bdwS3..mmiAk8ukGssQ3EyqEjPWqa', 'Owner', '2025-03-12 14:03:27'),
(2, 'John Michael', 'Rodrigo', 'Castillo', 'jmykel1342@gmail.com', 'jmykel1342', '$2y$10$V1tIMS4vZzQlujxQhpm0DuXYyZ2dLYpAYEURmwegxu1dZstX.CRR6', 'Cashier', '2025-05-21 09:42:53');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(191) NOT NULL,
  `description` mediumtext DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=available,1=not available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `status`) VALUES
(1, 'Coffee Series', 'Every drink that belongs here has coffee.', 0),
(2, 'Non-Coffee Series', 'Every drink here is non-coffee.', 0),
(3, 'Choco-Ey Series', 'Every drink here is chocolate-based.', 0),
(4, 'Rookie Series', 'Every drink that belongs here offers a perfect balance of sweetness and crunch.', 0);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `payment_mode` enum('Cash','GCash') NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `cash_received` decimal(10,2) DEFAULT 0.00,
  `change_due` decimal(10,2) DEFAULT 0.00,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_name`, `payment_mode`, `total`, `cash_received`, `change_due`, `created_at`) VALUES
(24, 'Nicole', 'GCash', 78.00, 0.00, -78.00, '2025-05-18 21:20:23'),
(25, 'Jennie', 'Cash', 39.00, 50.00, 11.00, '2025-05-18 21:28:40'),
(26, 'Princess', 'GCash', 39.00, 0.00, -39.00, '2025-05-18 22:33:10'),
(27, 'Hannah', 'Cash', 117.00, 120.00, 3.00, '2025-05-18 22:37:02'),
(28, 'John', 'GCash', 39.00, 0.00, -39.00, '2025-05-18 22:38:14'),
(29, 'Naruto', 'Cash', 156.00, 170.00, 14.00, '2025-05-20 22:30:18'),
(30, 'Maria', 'Cash', 234.00, 500.00, 266.00, '2025-05-21 17:47:10'),
(31, 'Chris', 'GCash', 39.00, 0.00, -39.00, '2025-05-22 20:10:10'),
(33, 'Nicolai', '', 39.00, 0.00, 0.00, '2025-11-13 00:28:45'),
(34, 'Nicolai', '', 39.00, 0.00, 0.00, '2025-11-13 00:28:45'),
(35, 'Nicolai', '', 39.00, 0.00, 0.00, '2025-11-13 00:28:46'),
(36, 'HAHAHA', '', 39.00, 0.00, 0.00, '2025-11-13 00:30:14'),
(37, 'HAHAHA', '', 39.00, 0.00, 0.00, '2025-11-13 00:30:54'),
(38, 'HAHAHA', '', 39.00, 0.00, 0.00, '2025-11-13 00:31:05'),
(39, 'Wiser', '', 39.00, 0.00, 0.00, '2025-11-13 00:38:32'),
(40, 'Nicolai', '', 39.00, 0.00, 0.00, '2025-11-13 00:39:49'),
(41, 'MOlly', '', 78.00, 0.00, 0.00, '2025-11-13 00:43:01'),
(44, 'MOlly', 'GCash', 39.00, 0.00, 0.00, '2025-11-13 00:53:03'),
(45, 'Nicole', 'GCash', 78.00, 0.00, 0.00, '2025-11-13 00:55:25'),
(46, 'Jennie', 'GCash', 117.00, 0.00, 0.00, '2025-11-13 00:57:26'),
(47, 'Nicole', 'GCash', 156.00, 0.00, 0.00, '2025-11-13 00:59:56');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `size` varchar(20) NOT NULL DEFAULT '12oz',
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_name`, `category`, `size`, `quantity`, `price`) VALUES
(32, 24, 'Iced Coffee Latte', '', '12oz', 1, 39.00),
(33, 24, 'Cookies and Cream', '', '12oz', 1, 39.00),
(34, 25, 'Milky Strawberry', '', '12oz', 1, 39.00),
(35, 26, 'Milky Matcha', '', '12oz', 1, 39.00),
(36, 27, 'Spanish Latte', '', '12oz', 1, 39.00),
(37, 27, 'Milky Ube', '', '12oz', 1, 39.00),
(38, 27, 'Dark Choco-ey', '', '12oz', 1, 39.00),
(39, 28, 'Caramel Macchiato', '', '12oz', 1, 39.00),
(40, 29, 'Milky Matcha', '', '12oz', 1, 39.00),
(41, 29, 'White Choco-ey', '', '12oz', 1, 39.00),
(42, 29, 'Cookies and Cream', '', '12oz', 1, 39.00),
(43, 29, 'Java Chip', '', '12oz', 1, 39.00),
(44, 30, 'Iced Coffee Latte', '', '12oz', 1, 39.00),
(45, 30, 'Matcha Latte', '', '12oz', 1, 39.00),
(46, 30, 'Milky Strawberry', '', '12oz', 1, 39.00),
(47, 30, 'White Choco-ey', '', '12oz', 1, 39.00),
(48, 30, 'Dark Choco-ey', '', '12oz', 1, 39.00),
(49, 30, 'Java Chip', '', '12oz', 1, 39.00),
(50, 31, 'Java Chip', '', '12oz', 1, 39.00),
(53, 37, 'Spanish Latte', 'Coffee Series', '12oz', 1, 39.00),
(54, 38, 'Spanish Latte', 'Coffee Series', '12oz', 1, 39.00),
(55, 39, 'Caramel Macchiato', 'Coffee Series', '12oz', 1, 39.00),
(56, 40, 'Iced Coffee Latte', 'Coffee Series', '12oz', 1, 39.00),
(57, 41, 'Milky Strawberry', 'Non-Coffee Series', '12oz', 1, 39.00),
(58, 41, 'Milky Ube', 'Non-Coffee Series', '12oz', 1, 39.00),
(63, 44, 'Rocky Road', 'Rookie Series', '12oz', 1, 39.00),
(64, 45, 'Iced Coffee Latte', 'Coffee Series', '12oz', 1, 39.00),
(65, 45, 'Caramel Macchiato', 'Coffee Series', '12oz', 1, 39.00),
(66, 46, 'Milky Matcha', 'Non-Coffee Series', '12oz', 2, 39.00),
(67, 46, 'Milky Strawberry', 'Non-Coffee Series', '12oz', 1, 39.00),
(68, 47, 'Matcha Latte', 'Coffee Series', '12oz', 1, 39.00),
(69, 47, 'Milky Matcha', 'Non-Coffee Series', '12oz', 1, 39.00),
(70, 47, 'Milky Strawberry', 'Non-Coffee Series', '12oz', 1, 39.00),
(71, 47, 'White Choco-ey', 'Choco-Ey Series', '12oz', 1, 39.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(225) NOT NULL,
  `size` enum('12oz','16oz') NOT NULL DEFAULT '12oz',
  `price_12oz` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_16oz` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `image` varchar(225) NOT NULL,
  `created_at` date NOT NULL DEFAULT current_timestamp(),
  `status` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `size`, `price_12oz`, `price_16oz`, `price`, `quantity`, `image`, `created_at`, `status`) VALUES
(29, 1, 'Rocky Road', '12oz', 39.00, 49.00, 39, 100, 'assets/upload/products/1772121363.png', '2025-04-16', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cashier_staff`
--
ALTER TABLE `cashier_staff`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cashier_staff`
--
ALTER TABLE `cashier_staff`
  MODIFY `id` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
