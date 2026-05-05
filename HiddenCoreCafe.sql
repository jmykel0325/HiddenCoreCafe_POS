-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 21, 2026 at 09:52 PM
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
-- Database: `hiddencorecafe`
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
(3, 'Admin JM', '', 'User', 'jmcastillo1342@example.com', 'jmcastillo1342', '$2y$10$U5x/N6v3ewJV1nFZ8dklHO29aCc.kaVc3/YRgWY/UVyE4oJlaX2WO', 'Owner', '2026-03-16 20:08:49'),
(4, 'John Michael', 'Rodrigo', 'Castillo', 'jmykel0325@gmail.com', 'jmykel0325', '$2y$10$Oo.buQH5sLcYayiOkiGemeqYAm7SpJbDhCNDp4BqKANpeKUSXn/B.', 'Cashier', '2026-03-16 20:19:43');

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
(4, 'Rookie Series', 'Every drink that belongs here offers a perfect balance of sweetness and crunch.', 0),
(7, 'Flavored Latte', '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `daily_quota`
--

CREATE TABLE `daily_quota` (
  `id` int(11) NOT NULL,
  `quota_date` date NOT NULL,
  `target_cups` int(11) NOT NULL DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `daily_quota`
--

INSERT INTO `daily_quota` (`id`, `quota_date`, `target_cups`, `created_by`, `created_at`, `updated_at`) VALUES
(1, '2026-04-22', 100, 3, '2026-04-22 03:18:10', '2026-04-22 03:30:34');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `payment_mode` enum('Cash','GCash') NOT NULL,
  `order_status` varchar(20) NOT NULL DEFAULT 'pending',
  `discount_type` varchar(20) DEFAULT NULL,
  `discount_rate` decimal(5,2) DEFAULT 0.00,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `final_total` decimal(10,2) DEFAULT 0.00,
  `gcash_reference` varchar(100) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `cash_received` decimal(10,2) DEFAULT 0.00,
  `change_due` decimal(10,2) DEFAULT 0.00,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_name`, `payment_mode`, `order_status`, `discount_type`, `discount_rate`, `discount_amount`, `final_total`, `gcash_reference`, `total`, `cash_received`, `change_due`, `created_at`) VALUES
(54, 'jm', 'Cash', 'completed', NULL, 0.00, 0.00, 294.00, NULL, 294.00, 500.00, 206.00, '2026-04-17 01:25:42'),
(55, ' KAI', 'Cash', 'completed', NULL, 0.00, 0.00, 40.00, NULL, 40.00, 100.00, 60.00, '2026-04-17 01:40:54'),
(57, 'JM', 'GCash', 'completed', NULL, 0.00, 0.00, 117.00, 'ASdfhk123', 117.00, 0.00, 0.00, '2026-04-22 02:19:10'),
(58, 'Zaidie', 'GCash', 'cancelled', NULL, 0.00, 0.00, 235.00, '1asdxdsqs', 235.00, 0.00, 0.00, '2026-04-22 02:48:42'),
(59, 'jm', 'Cash', 'cancelled', NULL, 0.00, 0.00, 156.00, NULL, 156.00, 200.00, 44.00, '2026-04-22 03:37:07'),
(61, 'jm', 'Cash', 'pending', NULL, 0.00, 0.00, 117.00, NULL, 117.00, 200.00, 83.00, '2026-04-22 03:39:50');

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
(86, 54, 'Blueberry Latte', '', '12oz', 1, 39.00),
(87, 54, 'Green Apple Latte', '', '12oz', 1, 39.00),
(88, 54, 'Strawberry Latte', '', '12oz', 1, 39.00),
(89, 54, 'Green Apple Latte', '', '16oz', 1, 59.00),
(90, 54, 'Strawberry Latte', '', '16oz', 1, 59.00),
(91, 54, 'Blueberry Latte', '', '16oz', 1, 59.00),
(92, 55, 'Green Apple Latte', '', '12oz', 1, 39.00),
(93, 55, '1', '', '12oz', 1, 1.00),
(116, 57, 'Blueberry Latte', '', '12oz', 1, 39.00),
(117, 57, 'Green Apple Latte', '', '12oz', 1, 39.00),
(118, 57, 'Strawberry Latte', '', '12oz', 1, 39.00),
(119, 58, 'Blueberry Latte', '', '12oz', 1, 39.00),
(120, 58, 'Green Apple Latte', '', '12oz', 1, 39.00),
(121, 58, 'Strawberry Latte', '', '12oz', 1, 39.00),
(122, 58, 'Blueberry Latte', '', '16oz', 1, 59.00),
(123, 58, 'Green Apple Latte', '', '16oz', 1, 59.00),
(124, 59, 'Strawberry Latte', '', '12oz', 4, 39.00),
(143, 61, 'Blueberry Latte', 'Flavored Latte', '12oz', 1, 39.00),
(144, 61, 'Strawberry Latte', 'Flavored Latte', '12oz', 2, 39.00);

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
  `image` varchar(225) NOT NULL,
  `created_at` date NOT NULL DEFAULT current_timestamp(),
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `size`, `price_12oz`, `price_16oz`, `price`, `image`, `created_at`, `status`, `deleted_at`) VALUES
(31, 7, 'Blueberry Latte', '12oz', 39.00, 59.00, 39, 'assets/upload/products/1773838691.png', '2026-03-17', 0, NULL),
(32, 7, 'Green Apple Latte', '12oz', 39.00, 59.00, 39, 'assets/upload/products/1773839033.png', '2026-03-18', 0, NULL),
(33, 7, 'Strawberry Latte', '12oz', 39.00, 59.00, 39, 'assets/upload/products/1773839121.png', '2026-03-18', 0, NULL);

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
-- Indexes for table `daily_quota`
--
ALTER TABLE `daily_quota`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_quota_date` (`quota_date`);

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
  MODIFY `id` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `daily_quota`
--
ALTER TABLE `daily_quota`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=145;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

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
