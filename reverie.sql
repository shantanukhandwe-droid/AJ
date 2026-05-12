-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 12, 2026 at 09:45 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `reverie`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `email`, `created_at`) VALUES
(2, 'admin', '$2y$10$VGB96PxU6xeRWDGjQfv1mOGGow5nXj0X5pow.0JmgAgzmy3iYKgxa', 'admin@reverie.local', '2026-04-25 07:22:00');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `email`, `phone`, `password`, `created_at`) VALUES
(1, 'Shantanu khandwe', 'sunnykhandwe@gmail.com', '06260096745', '$2y$10$iIZCATgvgToWByu8nt.WB.ZN1KnX.rWN2Hi1K2FP8uG8DGj4TRh7i', '2026-04-25 07:51:41');

-- --------------------------------------------------------

--
-- Table structure for table `customer_addresses`
--

CREATE TABLE `customer_addresses` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address_line` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `pincode` varchar(10) NOT NULL,
  `address_type` enum('home','work','other') DEFAULT 'home',
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_addresses`
--

INSERT INTO `customer_addresses` (`id`, `customer_id`, `full_name`, `phone`, `address_line`, `city`, `state`, `pincode`, `address_type`, `is_default`, `created_at`) VALUES
(1, 1, 'Shantanu khandwe', '06260096745', 'PP 42 Lake pearl Garden\r\nPP 42 Lake pearl Garden', 'BHOPAL', 'MADHYA PRADESH', '462030', 'home', 1, '2026-04-27 07:40:35');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(20) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_address` text NOT NULL,
  `customer_city` varchar(100) DEFAULT NULL,
  `customer_state` varchar(100) DEFAULT NULL,
  `customer_pincode` varchar(10) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `shipping_fee` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `payment_method` enum('razorpay','cod') DEFAULT 'cod',
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `order_status` enum('pending','confirmed','shipped','delivered','cancelled') DEFAULT 'pending',
  `razorpay_order_id` varchar(255) DEFAULT NULL,
  `razorpay_payment_id` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `customer_id`, `customer_name`, `customer_email`, `customer_phone`, `customer_address`, `customer_city`, `customer_state`, `customer_pincode`, `subtotal`, `shipping_fee`, `total`, `payment_method`, `payment_status`, `order_status`, `razorpay_order_id`, `razorpay_payment_id`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'REV202604276959', NULL, 'Shantanu khandwe', 'sunnyk@gmail.com', '06260096745', 'sunnykhandwe@gmail.com', 'BHOPAL', 'MADHYA PRADESH', '462030', 899.00, 0.00, 899.00, 'cod', 'pending', 'pending', NULL, NULL, NULL, '2026-04-27 06:54:44', '2026-04-27 06:54:44'),
(2, 'REV202604273420', NULL, 'Shantanu khandwe', 'sunnyk@gmail.com', '06260096745', 'PP 42 Lake pearl Garden\r\nPP 42 Lake pearl Garden', 'BHOPAL', 'MADHYA PRADESH', '462030', 899.00, 0.00, 899.00, 'razorpay', 'pending', 'pending', NULL, NULL, NULL, '2026-04-27 06:55:52', '2026-04-27 06:55:52'),
(3, 'REV202604272341', NULL, 'Shantanu khandwe', 'sunnyk@gmail.com', '06260096745', 'pp', 'BHOPAL', 'MADHYA PRADESH', '462030', 899.00, 0.00, 899.00, 'cod', 'pending', 'pending', NULL, NULL, NULL, '2026-04-27 06:58:55', '2026-04-27 07:09:00'),
(4, 'REV202604278367', NULL, 'Shantanu khandwe', 'sunnyk@gmail.com', '06260096745', 'Pp42\r\nLake pearl garden', 'BHOPAL', 'MADHYA PRADESH', '462030', 1999.00, 0.00, 1999.00, 'cod', 'pending', 'pending', NULL, NULL, NULL, '2026-04-27 07:09:43', '2026-04-27 07:09:43'),
(5, 'REV202604278870', 1, 'Shantanu khandwe', 'sunnykhandwe@gmail.com', '06260096745', 'PP 42 Lake pearl Garden\r\nPP 42 Lake pearl Garden', 'BHOPAL', 'MADHYA PRADESH', '462030', 899.00, 0.00, 899.00, 'cod', 'pending', 'confirmed', NULL, NULL, NULL, '2026-04-27 07:40:43', '2026-04-27 07:41:22'),
(6, 'REV202605111054', 1, 'Shantanu khandwe', 'sunnykhandwe@gmail.com', '06260096745', 'PP 42 Lake pearl Garden\r\nPP 42 Lake pearl Garden', 'BHOPAL', 'MADHYA PRADESH', '462030', 3998.00, 0.00, 3998.00, 'cod', 'pending', 'pending', NULL, NULL, NULL, '2026-05-11 04:45:27', '2026-05-11 04:45:27'),
(7, 'REV202605127557', 1, 'Shantanu khandwe', 'sunnykhandwe@gmail.com', '06260096745', 'PP 42 Lake pearl Garden\r\nPP 42 Lake pearl Garden', 'BHOPAL', 'MADHYA PRADESH', '462030', 499.00, 0.00, 499.00, 'razorpay', 'pending', 'pending', NULL, NULL, NULL, '2026-05-12 06:38:07', '2026-05-12 06:38:07');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `variant_details` text DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `variant_info` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `variant_id`, `variant_details`, `product_name`, `variant_info`, `price`, `quantity`) VALUES
(1, 1, 1, NULL, NULL, 'Circle Hoops', NULL, 899.00, 1),
(2, 2, 1, NULL, NULL, 'Circle Hoops', NULL, 899.00, 1),
(3, 3, 1, NULL, NULL, 'Circle Hoops', NULL, 899.00, 1),
(4, 4, 8, NULL, NULL, 'Line Bangle', NULL, 1999.00, 1),
(5, 5, 1, NULL, NULL, 'Circle Hoops', NULL, 899.00, 1),
(6, 6, 8, NULL, NULL, 'Line Bangle', NULL, 1999.00, 2),
(7, 7, 5, NULL, NULL, 'Point Studs', NULL, 499.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `category` enum('earrings','necklaces','rings','bangles') NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `has_variants` tinyint(1) DEFAULT 0,
  `base_sku` varchar(100) DEFAULT NULL,
  `original_price` decimal(10,2) DEFAULT NULL,
  `discount_percent` int(11) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `image_main` varchar(255) DEFAULT NULL,
  `image_hover` varchar(255) DEFAULT NULL,
  `is_bestseller` tinyint(1) DEFAULT 0,
  `is_new` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `slug`, `category`, `description`, `price`, `has_variants`, `base_sku`, `original_price`, `discount_percent`, `stock`, `image_main`, `image_hover`, `is_bestseller`, `is_new`, `created_at`, `updated_at`) VALUES
(1, 'Circle Hoops', 'circle-hoops', 'earrings', 'Minimalist gold-plated hoops for everyday elegance. 18k gold plating, hypoallergenic, anti-tarnish.', 899.00, 1, 'CH-1', 1499.00, 40, 29, NULL, NULL, 1, 0, '2026-04-24 18:29:34', '2026-05-12 07:39:01'),
(2, 'Solitaire Drop', 'solitaire-drop', 'necklaces', 'Delicate pendant necklace with geometric charm. Perfect for layering or solo wear.', 1299.00, 1, 'SD-2', 1999.00, 35, 25, NULL, NULL, 0, 1, '2026-04-24 18:29:34', '2026-05-12 07:39:30'),
(3, 'Trio Bangles', 'trio-bangles', 'bangles', 'Set of three slim bangles. Wear together or separately. Designed to stack beautifully.', 1799.00, 0, NULL, 2499.00, 28, 12, NULL, NULL, 0, 0, '2026-04-24 18:29:34', '2026-04-24 18:29:34'),
(4, 'Solitaire Band', 'solitaire-band', 'rings', 'Classic ring with minimalist setting. Daily wear or special occasions.', 599.00, 0, NULL, 999.00, 40, 30, NULL, NULL, 1, 0, '2026-04-24 18:29:34', '2026-04-24 18:29:34'),
(5, 'Point Studs', 'point-studs', 'earrings', 'Tiny gold studs. Perfect for second piercings or solo wear.', 499.00, 0, NULL, 899.00, 44, 40, NULL, NULL, 0, 0, '2026-04-24 18:29:34', '2026-04-24 18:29:34'),
(6, 'Heart Chain', 'heart-chain', 'necklaces', 'Dainty heart pendant on fine chain. Modern take on a classic.', 1099.00, 0, NULL, 1699.00, 35, 22, NULL, NULL, 0, 1, '2026-04-24 18:29:34', '2026-04-24 18:29:34'),
(7, 'Halo Ring', 'halo-ring', 'rings', 'Statement ring with micro-set stones. Catches light beautifully.', 1499.00, 0, NULL, 2199.00, 32, 23, NULL, NULL, 0, 0, '2026-04-24 18:29:34', '2026-05-07 11:36:42'),
(8, 'Line Bangle', 'line-bangle', 'bangles', 'Single sculptural bangle. Bold but wearable. Designed in India.', 1999.00, 0, NULL, 2999.00, 33, 8, NULL, NULL, 1, 0, '2026-04-24 18:29:34', '2026-04-24 18:29:34');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `alt_text` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_options`
--

CREATE TABLE `product_options` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `option_type` enum('size','color','material') NOT NULL,
  `option_value` varchar(100) NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `sku` varchar(100) NOT NULL,
  `size` varchar(50) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `material` varchar(100) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `price_adjustment` decimal(10,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_key`, `setting_value`, `updated_at`) VALUES
('cod_enabled', '1', '2026-04-24 18:29:34'),
('razorpay_enabled', '1', '2026-04-27 06:55:29'),
('razorpay_key_id', 'sunnykhandwe@gmail.com', '2026-04-27 06:55:29'),
('razorpay_key_secret', 'Adasgupta#18', '2026-04-27 06:55:29'),
('shipping_fee', '0', '2026-04-24 18:29:34'),
('shipping_fee_threshold', '999', '2026-04-24 18:29:34'),
('site_name', 'Reverie', '2026-04-24 18:29:34'),
('whatsapp_number', '919876543210', '2026-04-24 18:29:34');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_customer_email` (`email`);

--
-- Indexes for table `customer_addresses`
--
ALTER TABLE `customer_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `fk_customer` (`customer_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `variant_id` (`variant_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Indexes for table `product_options`
--
ALTER TABLE `product_options`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_option` (`product_id`,`option_type`,`option_value`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_sku` (`sku`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_wishlist` (`customer_id`,`product_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customer_addresses`
--
ALTER TABLE `customer_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_options`
--
ALTER TABLE `product_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customer_addresses`
--
ALTER TABLE `customer_addresses`
  ADD CONSTRAINT `customer_addresses_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_options`
--
ALTER TABLE `product_options`
  ADD CONSTRAINT `product_options_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
