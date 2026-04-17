-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 17, 2026 at 12:34 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `online_food_delivery`
--

-- --------------------------------------------------------

--
-- Table structure for table `food`
--

CREATE TABLE `food` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('available','unavailable') NOT NULL DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `food`
--

INSERT INTO `food` (`id`, `name`, `price`, `description`, `status`, `created_at`) VALUES
(1, 'Chicken Burger', 150.00, 'Juicy grilled chicken patty with fresh lettuce, tomato, and creamy mayo sauce in a toasted bun.', 'available', '2026-04-17 10:09:43'),
(2, 'Beef Pizza', 350.00, 'Wood-fired pizza loaded with premium beef toppings, mozzarella cheese, and Italian herbs.', 'available', '2026-04-17 10:09:43'),
(3, 'Pasta Carbonara', 280.00, 'Creamy Italian pasta with crispy bacon, parmesan cheese, and a rich egg-based sauce.', 'available', '2026-04-17 10:09:43'),
(4, 'Veggie Wrap', 120.00, 'Healthy whole wheat wrap loaded with fresh vegetables, hummus, and tangy dressing.', 'available', '2026-04-17 10:09:43'),
(5, 'Fried Rice', 180.00, 'Special fried rice with egg, mixed vegetables, soy sauce, and aromatic spices.', 'available', '2026-04-17 10:09:43'),
(6, 'Fish and Chips', 250.00, 'Crispy golden-battered fish served with thick-cut fries and tartar sauce.', 'available', '2026-04-17 10:09:43'),
(7, 'Chocolate Cake', 200.00, 'Rich and moist chocolate cake with dark chocolate ganache topping and cocoa dusting.', 'available', '2026-04-17 10:09:43'),
(8, 'Mango Smoothie', 100.00, 'Fresh ripe mango blended with creamy yogurt, honey, and a hint of vanilla.', 'available', '2026-04-17 10:09:43'),
(9, 'Grilled Sandwich', 130.00, 'Toasted sandwich with grilled chicken, cheese, lettuce, and special sauce.', 'available', '2026-04-17 10:09:43'),
(10, 'Chicken Biryani', 220.00, 'Aromatic basmati rice cooked with tender chicken pieces, saffron, and traditional spices.', 'available', '2026-04-17 10:09:43'),
(11, 'Caesar Salad', 160.00, 'Fresh romaine lettuce with caesar dressing, croutons, parmesan cheese, and grilled chicken.', 'available', '2026-04-17 10:09:43'),
(12, 'Ice Cream Sundae', 140.00, 'Three scoops of premium ice cream with chocolate sauce, whipped cream, and cherry on top.', 'available', '2026-04-17 10:09:43');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `order_id`, `message`, `created_at`) VALUES
(1, 5, 7, 1, 'I am on the way', '2026-04-17 10:19:31');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `rider_id` int(11) DEFAULT NULL,
  `status` enum('Pending','Paid','Accepted','Picked Up','On the Way','Delivered') NOT NULL DEFAULT 'Pending',
  `total_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_id`, `rider_id`, `status`, `total_price`, `created_at`) VALUES
(1, 7, 5, 'Delivered', 730.00, '2026-04-17 10:17:26'),
(2, 8, 5, 'Delivered', 860.00, '2026-04-17 10:26:10');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `food_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `food_id`, `quantity`) VALUES
(1, 1, 2, 1),
(2, 1, 11, 1),
(3, 1, 10, 1),
(4, 2, 2, 1),
(5, 2, 11, 1),
(6, 2, 10, 1),
(7, 2, 9, 1);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payment_method` enum('Visa Card','bKash','Nagad') NOT NULL,
  `transaction_id` varchar(100) NOT NULL,
  `payment_status` enum('Pending','Completed') NOT NULL DEFAULT 'Completed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `transaction_id`, `payment_status`, `created_at`) VALUES
(1, 1, 'Visa Card', '872348732698477', 'Completed', '2026-04-17 10:17:33'),
(2, 2, 'bKash', '457657er', 'Completed', '2026-04-17 10:26:23');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','rider','owner') NOT NULL DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Admin Owner', 'admin@food.com', '$2y$10$8K1p/a0dL1LXMIgoEDFrwOfMQbF7s4YoTSFx5iA0rR6IG5xKylKHy', 'owner', '2026-04-17 10:09:43'),
(2, 'Rider One', 'rider@food.com', '$2y$10$8K1p/a0dL1LXMIgoEDFrwOfMQbF7s4YoTSFx5iA0rR6IG5xKylKHy', 'rider', '2026-04-17 10:09:43'),
(3, 'Rider Two', 'rider2@food.com', '$2y$10$8K1p/a0dL1LXMIgoEDFrwOfMQbF7s4YoTSFx5iA0rR6IG5xKylKHy', 'rider', '2026-04-17 10:09:43'),
(4, 'John Customer', 'customer@food.com', '$2y$10$8K1p/a0dL1LXMIgoEDFrwOfMQbF7s4YoTSFx5iA0rR6IG5xKylKHy', 'customer', '2026-04-17 10:09:43'),
(5, 'Jabin', 'jabin@gmail.com', '$2y$10$zAwueztuygwmJzMRniCx9OQFH/zMbyym0ywqYQ.uBXO98F3ql1zey', 'rider', '2026-04-17 10:12:26'),
(6, 'Nahian', 'nahian@gmail.com', '$2y$10$Jx518gSy69h6aY8mQkeA0uanMwvUvrfMiap8VDj2ETmhvytzHvgi.', 'owner', '2026-04-17 10:13:54'),
(7, 'jabin123', 'jabin123@gmail.com', '$2y$10$xwkIe3xLSmTKGYAgHjweuu9e6GgxOOdtSVACAvA7.dq00NUqS3eTi', 'customer', '2026-04-17 10:16:58'),
(8, 'Nahian', 'nahianjabin@gmail.com', '$2y$10$D3OTfOEGyR8eoSUcMz5MQuj2rkBJDotA7YmBaV7iuXC2.vuuptER6', 'customer', '2026-04-17 10:25:43');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `food`
--
ALTER TABLE `food`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `rider_id` (`rider_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `food_id` (`food_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `food`
--
ALTER TABLE `food`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`rider_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`food_id`) REFERENCES `food` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
