-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 26, 2026 at 11:15 AM
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
-- Database: `fizz_and_bits_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `user_id`) VALUES
(1, 3),
(2, 4),
(3, 5),
(4, 6),
(5, 7),
(6, 8),
(7, 9),
(8, 10);

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `cart_item_id` int(11) NOT NULL,
  `cart_id` int(11) DEFAULT NULL,
  `menu_item_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`cart_item_id`, `cart_id`, `menu_item_id`, `quantity`) VALUES
(1, 1, 1, 1),
(2, 2, 3, 2),
(3, 3, 5, 1),
(4, 4, 2, 1),
(5, 5, 8, 2),
(6, 6, 3, 2);

-- --------------------------------------------------------

--
-- Table structure for table `customers_profile`
--

CREATE TABLE `customers_profile` (
  `user_id` int(11) NOT NULL,
  `email` varchar(50) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `address` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers_profile`
--

INSERT INTO `customers_profile` (`user_id`, `email`, `phone`, `address`) VALUES
(3, 'juan@email.com', '09171234561', 'Quezon City'),
(4, 'maria@email.com', '09171234562', 'Manila'),
(5, 'anna@email.com', '09171234563', 'Makati'),
(6, 'mark@email.com', '09171234564', 'Pasig'),
(7, 'bea@email.com', '09171234565', 'Taguig'),
(8, 'ryan@email.com', '09171234566', 'Caloocan'),
(9, 'karen@email.com', '09171234567', 'Marikina'),
(10, 'leo@email.com', '09171234568', 'San Juan');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `inventory_id` int(11) NOT NULL,
  `menu_item_id` int(11) DEFAULT NULL,
  `quantity_in_stock` int(11) DEFAULT NULL,
  `reorder_level` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`inventory_id`, `menu_item_id`, `quantity_in_stock`, `reorder_level`) VALUES
(1, 1, 120, 20),
(2, 2, 100, 20),
(3, 3, 150, 30),
(4, 4, 130, 20),
(5, 5, 200, 40),
(6, 6, 140, 25),
(7, 7, 250, 50),
(8, 8, 90, 20),
(9, 9, 300, 60),
(10, 10, 80, 15);

-- --------------------------------------------------------

--
-- Table structure for table `menu_categories`
--

CREATE TABLE `menu_categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_categories`
--

INSERT INTO `menu_categories` (`category_id`, `name`) VALUES
(1, 'Burgers'),
(2, 'Chicken'),
(3, 'Drinks'),
(4, 'Snacks'),
(5, 'Desserts');

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `menu_item_id` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `description` varchar(50) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `availability_status` enum('available','not available') DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`menu_item_id`, `name`, `description`, `category_id`, `price`, `availability_status`, `image_url`, `is_featured`) VALUES
(1, 'Fizz Classic Burger', 'Beef burger with cheese', 1, 120.00, 'available', 'burger1.jpg', 1),
(2, 'Double Fizz Burger', 'Double patty burger', 1, 160.00, 'available', 'burger2.jpg', 1),
(3, 'Crispy Chicken Meal', 'Fried chicken with rice', 2, 150.00, 'available', 'chicken1.jpg', 1),
(4, 'Chicken Wings', 'Spicy wings', 2, 140.00, 'available', 'wings.jpg', 0),
(5, 'Fizz Fries', 'Large fries', 4, 80.00, 'available', 'fries.jpg', 1),
(6, 'Mozza Sticks', 'Cheese sticks', 4, 95.00, 'available', 'mozza.jpg', 0),
(7, 'Iced Tea', 'Cold iced tea', 3, 50.00, 'available', 'icedtea.jpg', 0),
(8, 'Fizz Milkshake', 'Chocolate milkshake', 5, 90.00, 'available', 'milkshake.jpg', 1),
(9, 'Soft Drink', 'Regular soda', 3, 45.00, 'available', 'soda.jpg', 0),
(10, 'Ice Cream Sundae', 'Vanilla sundae', 5, 70.00, 'available', 'sundae.jpg', 0);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_date` datetime DEFAULT NULL,
  `order_status` enum('on process','canceled','completed') DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `delivery_city` varchar(50) DEFAULT NULL,
  `delivery_region` varchar(50) DEFAULT NULL,
  `postal_code` varchar(15) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `full_name` varchar(50) DEFAULT NULL,
  `address` varchar(50) DEFAULT NULL,
  `order_type` enum('delivery','pickup') NOT NULL DEFAULT 'delivery'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `order_date`, `order_status`, `total_amount`, `delivery_city`, `delivery_region`, `postal_code`, `phone`, `full_name`, `address`, `order_type`) VALUES
(1, 3, '2025-10-05 12:30:00', 'completed', 240.00, 'Quezon City', 'NCR', '1100', '09171234561', 'Juan Karding', 'Quezon City', 'delivery'),
(2, 4, '2025-10-10 18:15:00', 'completed', 160.00, 'Manila', 'NCR', '1000', '09171234562', 'Maria Leonor', 'Manila', 'pickup'),
(3, 5, '2025-10-18 14:20:00', 'completed', 200.00, 'Makati', 'NCR', '1200', '09171234563', 'Anna Lim', 'Makati', 'delivery'),
(4, 6, '2025-11-02 13:10:00', 'completed', 300.00, 'Pasig', 'NCR', '1600', '09171234564', 'Mark Tan', 'Pasig', 'delivery'),
(5, 7, '2025-11-08 19:30:00', 'completed', 190.00, 'Taguig', 'NCR', '1630', '09171234565', 'Bea Aquino', 'Taguig', 'pickup'),
(6, 8, '2025-11-15 11:00:00', 'completed', 210.00, 'Caloocan', 'NCR', '1400', '09171234566', 'Ryan Agoncillo', 'Caloocan', 'delivery'),
(7, 9, '2025-12-03 17:30:00', 'completed', 260.00, 'Marikina', 'NCR', '1800', '09171234567', 'Karen Navarro', 'Marikina', 'delivery'),
(8, 10, '2025-12-20 12:20:00', 'completed', 180.00, 'San Juan', 'NCR', '1500', '09171234568', 'Leo Reyes', 'San Juan', 'pickup'),
(9, 3, '2026-01-05 12:00:00', 'completed', 210.00, 'Quezon City', 'NCR', '1100', '09171234561', 'Juan Karding', 'Quezon City', 'delivery'),
(10, 4, '2026-01-18 19:00:00', 'completed', 150.00, 'Manila', 'NCR', '1000', '09171234562', 'Maria Leonor', 'Manila', 'delivery'),
(11, 5, '2026-02-02 14:40:00', 'completed', 230.00, 'Makati', 'NCR', '1200', '09171234563', 'Anna Lim', 'Makati', 'delivery'),
(12, 6, '2026-02-20 16:10:00', 'completed', 280.00, 'Pasig', 'NCR', '1600', '09171234564', 'Mark Tan', 'Pasig', 'pickup'),
(13, 7, '2026-03-05 11:30:00', 'completed', 200.00, 'Taguig', 'NCR', '1630', '09171234565', 'Bea Aquino', 'Taguig', 'delivery'),
(14, 8, '2026-03-15 18:20:00', 'completed', 260.00, 'Caloocan', 'NCR', '1400', '09171234566', 'Ryan Agoncillo', 'Caloocan', 'delivery');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `menu_item_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `menu_item_id`, `quantity`, `price`) VALUES
(1, 1, 1, 2, 120.00),
(2, 2, 2, 1, 160.00),
(3, 3, 3, 1, 150.00),
(4, 4, 2, 2, 160.00),
(5, 5, 5, 1, 80.00),
(6, 6, 3, 1, 150.00),
(7, 7, 8, 2, 90.00),
(8, 8, 1, 1, 120.00),
(9, 9, 3, 1, 150.00),
(10, 10, 4, 1, 140.00),
(11, 11, 2, 1, 160.00),
(12, 12, 1, 2, 120.00),
(13, 13, 5, 2, 80.00),
(14, 14, 8, 2, 90.00);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  `payment_method` enum('payMongo','COD','ccTerminal') DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `order_id`, `payment_date`, `payment_method`, `amount_paid`) VALUES
(1, 1, '2025-10-05 00:00:00', 'COD', 240.00),
(2, 2, '2025-10-10 00:00:00', 'payMongo', 160.00),
(3, 3, '2025-10-18 00:00:00', 'COD', 200.00),
(4, 4, '2025-11-02 00:00:00', 'COD', 300.00),
(5, 5, '2025-11-08 00:00:00', 'ccTerminal', 190.00),
(6, 6, '2025-11-15 00:00:00', 'COD', 210.00),
(7, 7, '2025-12-03 00:00:00', 'payMongo', 260.00),
(8, 8, '2025-12-20 00:00:00', 'COD', 180.00),
(9, 9, '2026-01-05 00:00:00', 'COD', 210.00),
(10, 10, '2026-01-18 00:00:00', 'payMongo', 150.00),
(11, 11, '2026-02-02 00:00:00', 'COD', 230.00),
(12, 12, '2026-02-20 00:00:00', 'COD', 280.00),
(13, 13, '2026-03-05 00:00:00', 'COD', 200.00),
(14, 14, '2026-03-15 00:00:00', 'payMongo', 260.00);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `menu_item_id` int(11) DEFAULT NULL,
  `rating` tinyint(4) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `user_id`, `menu_item_id`, `rating`, `comment`, `created_at`) VALUES
(1, 3, 1, 5, 'Very tasty burger!', '2025-10-05 00:00:00'),
(2, 4, 2, 4, 'Good but a bit salty', '2025-10-10 00:00:00'),
(3, 5, 3, 5, 'Crispy chicken was perfect', '2025-10-18 00:00:00'),
(4, 6, 2, 4, 'Loved the double burger', '2025-11-02 00:00:00'),
(5, 7, 5, 5, 'Fries were hot and crispy', '2025-11-08 00:00:00'),
(6, 8, 3, 4, 'Nice meal', '2025-11-15 00:00:00'),
(7, 9, 8, 5, 'Milkshake is amazing', '2025-12-03 00:00:00'),
(8, 10, 1, 4, 'Good classic burger', '2025-12-20 00:00:00'),
(9, 3, 3, 5, 'Chicken meal is my favorite', '2026-01-05 00:00:00'),
(10, 4, 4, 4, 'Wings were spicy', '2026-01-18 00:00:00'),
(11, 5, 2, 5, 'Very filling', '2026-02-02 00:00:00'),
(12, 6, 1, 5, 'Always fresh', '2026-02-20 00:00:00'),
(13, 7, 5, 4, 'Nice snack', '2026-03-05 00:00:00'),
(14, 8, 8, 5, 'Best milkshake', '2026-03-15 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `role` enum('administrator','customer') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `first_name`, `last_name`, `role`) VALUES
(1, 'admin', '$2y$10$BT4J1gMblEwWnpRAGrrgDeoWonOGSpdk5YJB2cP.7FjDEtsNaXF2O', 'Axel', 'Esguerra', 'administrator'),
(2, 'manager', '$2y$10$BT4J1gMblEwWnpRAGrrgDeoWonOGSpdk5YJB2cP.7FjDEtsNaXF2O', 'Mina', 'Santos', 'administrator'),
(3, 'juan01', '$2y$10$FPsSI7M8qsRQ0SNszm0tH.IwrMnhA2MD1I3Jix5txfgRrogwTW..W', 'Juan', 'Karding', 'customer'),
(4, 'maria01', '$2y$10$Pgy/Yc6BQEW3bwJDGx8QfOlMlwcMaDls7HmjK45onJjo6h9vvYxPW', 'Maria', 'Leonor', 'customer'),
(5, 'anna01', '$2y$10$VD1G9QmaKGD6NbyWM7TBo.SbGPMvx89Oo4zwkPdTS3BR8iQKeC9Ne', 'Anna', 'Lim', 'customer'),
(6, 'mark01', '$2y$10$PmheAiGNSAXIAfoQprxBLObbrqHkPY8811CaftHD5rCbLNmzF21au', 'Mark', 'Tan', 'customer'),
(7, 'bea01', '$2y$10$/M4Ld7z8WepglmR1HgGKU.VZU/xuDvoKvW94mafHMTgZMcMl0d0Dm', 'Bea', 'Aquino', 'customer'),
(8, 'ryan01', '$2y$10$GMMrmGzjuJvV1NiC9FLPj.aLN1BYPxwP3v1fMIjLSLCXWi9HTm9Ee', 'Ryan', 'Agoncillo', 'customer'),
(9, 'karen01', '$2y$10$xDa7UcCFlIO7VUVjsyMR7e/vwGUxzpXR2LRVWijEtSDGaIg3ejeOK', 'Karen', 'Navarro', 'customer'),
(10, 'leo01', '$2y$10$LLTk6yGQiswlmTagtojFC.587mrZTyMkAJgcrlNz.zRW/BWHo1p22', 'Leo', 'Reyes', 'customer');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`cart_item_id`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `menu_item_id` (`menu_item_id`);

--
-- Indexes for table `customers_profile`
--
ALTER TABLE `customers_profile`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`inventory_id`),
  ADD KEY `fk_menu_item` (`menu_item_id`);

--
-- Indexes for table `menu_categories`
--
ALTER TABLE `menu_categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`menu_item_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `menu_item_id` (`menu_item_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `menu_item_id` (`menu_item_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `cart_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `menu_categories`
--
ALTER TABLE `menu_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `menu_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `cart` (`cart_id`),
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`menu_item_id`);

--
-- Constraints for table `customers_profile`
--
ALTER TABLE `customers_profile`
  ADD CONSTRAINT `customers_profile_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `fk_menu_item` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`menu_item_id`) ON DELETE CASCADE;

--
-- Constraints for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `menu_items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `menu_categories` (`category_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`menu_item_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`menu_item_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
