-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 26, 2024 at 10:22 AM
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
-- Database: `mydata`
--

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `post_id`, `user_id`, `content`, `created_at`) VALUES
(1, 1, 3, '', '2024-09-25 08:49:08'),
(2, 1, 3, 'สวัสดีจ้า', '2024-09-25 08:49:20'),
(3, 2, 3, 'สวัสดีจ้า', '2024-09-25 08:50:05'),
(4, 1, 1, 'ดีงับ', '2024-09-25 08:52:54'),
(5, 8, 3, 'สวยจ้าา อุอิอุอิ', '2024-09-25 09:18:37'),
(6, 9, 7, 'สวยมากจ้า', '2024-09-25 16:36:19'),
(7, 8, 1, 'ขอบคุณจ้าา', '2024-09-25 17:18:47'),
(8, 2, 1, 'เหมือนเหงา งง?', '2024-09-25 17:19:13'),
(9, 11, 7, 'ถูกต้อง งงมาก อยากจะบ้าาาาาา!!!!!', '2024-09-25 18:19:49');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `content`, `created_at`, `image_path`) VALUES
(1, 1, 'สวัสดี', '2024-09-25 08:37:31', NULL),
(2, 3, 'ยินดีที่ได้รู้จักทุกคน', '2024-09-25 08:40:30', NULL),
(7, 1, '', '2024-09-25 09:09:23', 'uploads/pic_trulli.jpg'),
(8, 1, 'ทะเลสวยมั้ยทุกคน\r\n', '2024-09-25 09:10:04', 'uploads/pexels-francesco-ungaro-96377.jpg'),
(9, 1, 'สวยไหม', '2024-09-25 09:31:43', 'uploads/pexels-trupert-1032650.jpg'),
(10, 1, 'Blackpink!!!!!', '2024-09-25 17:03:04', 'uploads/296645900_5958254147535203_344127966623006815_n.jpg'),
(11, 1, 'นอยมาจะมาสร้างว็บอะไร สอนกะบ่รู้เรื่องยุ', '2024-09-25 18:18:19', ''),
(12, 7, 'birng', '2024-09-25 18:20:27', 'uploads/black.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `password`, `created_at`, `profile_image`) VALUES
(1, 'Nattawut', 'nattawut.hp@rmuti.ac.th', '$2y$10$/ZRmW0qRIvd3C4SmwnfPaOLK9N6PHxnS.HdTIdYegrjlo0lmFzLFq', '2024-09-22 11:06:45', 'uploads/lalisa.jpg'),
(3, 'Breem', 'Sirimas.mu@rmuti.ac.th', '$2y$10$YdTOTC2EPwIKKVW7jS7hku17F3SbA3KXgC3nkjOg6a4EYxaun2Ygq', '2024-09-23 06:22:33', NULL),
(7, 'Bas', 'basbas20145416@gmail.com', '$2y$10$AhdXK16vKiZD6U.19pFvKuApzIvH/meL2ixazPONwtuexuc2fLsWa', '2024-09-25 16:17:25', 'uploads/133792073_410895606699194_366504876662594185_o.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

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
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`),
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
