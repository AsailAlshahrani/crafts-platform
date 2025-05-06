-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 06 مايو 2025 الساعة 14:10
-- إصدار الخادم: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `new_crafts_db`
--

-- --------------------------------------------------------

--
-- بنية الجدول `new_artisans`
--

CREATE TABLE `new_artisans` (
  `ArtisanId` int(11) NOT NULL,
  `FullName` varchar(255) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Region` varchar(100) NOT NULL,
  `CraftType` varchar(100) NOT NULL,
  `CraftDescription` text NOT NULL,
  `PortfolioFile` varchar(255) DEFAULT NULL,
  `CreatedDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `PasswordHash` varchar(255) DEFAULT NULL,
  `UserId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `new_artisans`
--

INSERT INTO `new_artisans` (`ArtisanId`, `FullName`, `Email`, `Region`, `CraftType`, `CraftDescription`, `PortfolioFile`, `CreatedDate`, `PasswordHash`, `UserId`) VALUES
(2, 'Noura Abdulaziz', 'noura@example.com', 'Jeddah', 'فخار', 'أعمال فنية بالطين', NULL, '2025-04-23 16:47:53', 'noura123', NULL),
(3, 'Abdullah Fahad', 'abdullah@example.com', 'Dammam', 'خياطة', 'مهارات الخياطة باليد والماكينة', NULL, '2025-04-23 16:47:53', 'abdullah123', NULL),
(4, 'Mona Saleh', 'mona@example.com', 'Khobar', 'كروشيه', 'أعمال يدوية بالصوف والخيوط', NULL, '2025-04-23 16:47:53', 'mona123', NULL),
(8, 'Khaled Hassan', 'khaled@hotmail.com', 'مكة', 'الرسم', 'تعلم الرسم بتقنيات جذابة ومختلفة', 'uploads/drawing basics.pdf', '2025-04-30 05:41:35', '$2y$10$uaFRJAZSmgWA.G8J8KeiQOYomPGpH2Bg.J5yLVCEQnhb3e8KsnPpy', 14);

-- --------------------------------------------------------

--
-- بنية الجدول `new_artisan_files`
--

CREATE TABLE `new_artisan_files` (
  `FileId` int(11) NOT NULL,
  `ArtisanId` int(11) DEFAULT NULL,
  `FilePath` varchar(255) DEFAULT NULL,
  `UploadedAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `new_coursematerials`
--

CREATE TABLE `new_coursematerials` (
  `MaterialId` int(11) NOT NULL,
  `CourseId` int(11) NOT NULL,
  `MaterialType` enum('Video','PDF','Image') NOT NULL,
  `URL` varchar(500) NOT NULL,
  `UploadedDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `new_coursematerials`
--

INSERT INTO `new_coursematerials` (`MaterialId`, `CourseId`, `MaterialType`, `URL`, `UploadedDate`) VALUES
(2, 2, 'PDF', 'http://localhost/uploads/pottery_guide.pdf', '2025-04-23 16:47:53'),
(3, 3, 'Video', 'http://localhost/uploads/sewing_tutorial.mp4', '2025-04-23 16:47:53'),
(4, 4, 'Image', 'http://localhost/uploads/crochet_pattern.png', '2025-04-23 16:47:53');

-- --------------------------------------------------------

--
-- بنية الجدول `new_courses`
--

CREATE TABLE `new_courses` (
  `CourseId` int(11) NOT NULL,
  `Title` varchar(255) NOT NULL,
  `Description` text NOT NULL,
  `CraftsmanId` int(11) NOT NULL,
  `CreatedDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `Price` decimal(10,2) DEFAULT NULL,
  `File` varchar(255) DEFAULT NULL,
  `Attachment` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `new_courses`
--

INSERT INTO `new_courses` (`CourseId`, `Title`, `Description`, `CraftsmanId`, `CreatedDate`, `Price`, `File`, `Attachment`) VALUES
(2, 'Pottery Making', 'دليل خطوة بخطوة لصنع القطع الخزفية باستخدام الطين.', 6, '2025-04-23 16:47:53', 150.00, '1746019911_pottery making.pdf', NULL),
(3, 'Sewing Techniques', 'إتقان فن الخياطة بأنواع الغرز المختلفة والتعامل مع الأقمشة', 5, '2025-04-23 16:47:53', 150.00, '1746019220_Sewing Techniques.pdf', NULL),
(4, 'Crochet for Beginners', 'تعلم أساسيات الكروشيه وصنع قطع بسيطة يدوياً.', 5, '2025-04-23 16:47:53', 100.00, '1746019522_Crochet for Beginners.pdf', NULL),
(11, 'الرسم بتقنيات مختلفة وجذابة', 'تعلم أساسيات الرسم ، بما في ذلك التظليل والمنظور', 14, '2025-04-30 06:43:12', 100.00, '1746266852_drawing basics (1).pdf', NULL);

-- --------------------------------------------------------

--
-- بنية الجدول `new_enrollments`
--

CREATE TABLE `new_enrollments` (
  `EnrollmentId` int(11) NOT NULL,
  `CraftspersonId` int(11) NOT NULL,
  `CourseId` int(11) NOT NULL,
  `EnrollmentDate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `new_enrollments`
--

INSERT INTO `new_enrollments` (`EnrollmentId`, `CraftspersonId`, `CourseId`, `EnrollmentDate`) VALUES
(3, 4, 3, '2025-04-23 19:47:53'),
(4, 5, 3, '2025-04-23 19:47:53'),
(5, 6, 4, '2025-04-23 19:47:53'),
(7, 8, 11, '2025-04-30 15:39:42'),
(12, 19, 11, '2025-05-01 12:11:51'),
(13, 8, 3, '2025-05-03 13:24:17'),
(14, 19, 3, '2025-05-06 10:40:57'),
(15, 19, 4, '2025-05-06 11:13:37');

-- --------------------------------------------------------

--
-- بنية الجدول `new_favorites`
--

CREATE TABLE `new_favorites` (
  `FavoriteId` int(11) NOT NULL,
  `CraftspersonId` int(11) NOT NULL,
  `CourseId` int(11) NOT NULL,
  `CreatedDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `new_favorites`
--

INSERT INTO `new_favorites` (`FavoriteId`, `CraftspersonId`, `CourseId`, `CreatedDate`) VALUES
(7, 8, 3, '2025-04-30 13:13:58');

-- --------------------------------------------------------

--
-- بنية الجدول `new_forumposts`
--

CREATE TABLE `new_forumposts` (
  `PostId` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `Content` text NOT NULL,
  `CreatedDate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `new_forumposts`
--

INSERT INTO `new_forumposts` (`PostId`, `UserId`, `Content`, `CreatedDate`) VALUES
(2, 5, 'هل لدى أحد نصائح للتطريز المتقدم؟', '2025-04-23 19:47:53'),
(4, 5, 'الخياطة مهارة ممتعة جداً، أنصح بها بشدة.', '2025-04-23 19:47:53');

-- --------------------------------------------------------

--
-- بنية الجدول `new_profiles`
--

CREATE TABLE `new_profiles` (
  `UserId` int(11) NOT NULL,
  `firstName` varchar(100) DEFAULT NULL,
  `lastName` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `interests` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `passwordHash` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `new_profiles`
--

INSERT INTO `new_profiles` (`UserId`, `firstName`, `lastName`, `email`, `region`, `interests`, `created_at`, `passwordHash`) VALUES
(1, 'Ahmed', 'Ali', 'ahmed@example.com', 'Riyadh', 'Management', '2025-04-23 16:47:53', '123456'),
(2, 'Sarah', 'Mohammed', 'sarah@example.com', 'Riyadh', 'Admin tasks', '2025-04-23 16:47:53', 'soso1020'),
(4, 'Noura', 'Abdulaziz', 'noura@example.com', 'Jeddah', 'Pottery', '2025-04-23 16:47:53', 'noura123'),
(5, 'Abdullah', 'Fahad', 'abdullah@example.com', 'Dammam', 'Sewing', '2025-04-23 16:47:53', 'abdullah123'),
(6, 'Mona', 'Saleh', 'mona@example.com', 'Khobar', 'Crochet', '2025-04-23 16:47:53', 'mona123'),
(8, 'Mohammed', 'Rashid', 'mohammed@example.com', 'Madinah', 'Woodwork', '2025-04-23 16:47:53', 'mohammed123'),
(19, 'Lama', 'Alshahrani', 'lama@gmail.com', 'عسير', '', '2025-05-01 09:09:03', '$2y$10$Tdvxin13EPtOPtcx/lzZtuZZzKWTKCXQNiHCkeuYrks96P7H2GuL2');

-- --------------------------------------------------------

--
-- بنية الجدول `new_users`
--

CREATE TABLE `new_users` (
  `UserId` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `PasswordHash` varchar(255) NOT NULL,
  `Role` enum('Admin','Craftsman','Craftsperson') NOT NULL,
  `CreatedDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `new_users`
--

INSERT INTO `new_users` (`UserId`, `Name`, `Email`, `PasswordHash`, `Role`, `CreatedDate`) VALUES
(1, 'ahmed', 'ahmed@example.com', '123456', 'Admin', '2025-04-23 16:47:53'),
(2, 'Sara', 'sarah@example.com', 'soso1020', 'Admin', '2025-04-23 16:47:53'),
(4, 'Noura', 'noura@example.com', 'noura123', 'Craftsman', '2025-04-23 16:47:53'),
(5, 'Abdullah Fahad', 'abdullah@example.com', 'abdullah123', 'Craftsman', '2025-04-23 16:47:53'),
(6, 'mona', 'mona@example.com', 'mona123', 'Craftsman', '2025-04-23 16:47:53'),
(8, 'mohammed', 'mohammed@example.com', 'mohammed123', 'Craftsperson', '2025-04-23 16:47:53'),
(14, ' Khaled hassan', 'khaled@hotmail.com', '$2y$10$uaFRJAZSmgWA.G8J8KeiQOYomPGpH2Bg.J5yLVCEQnhb3e8KsnPpy', 'Craftsman', '2025-04-30 05:41:35'),
(18, '', 'm@gmail.com', '$2y$10$3eKgKK5Qb/wugih5ex7hBuNlND0WOOTAQUqz8V1eQ7dMaWwM1pbVC', 'Craftsman', '2025-04-30 12:57:43'),
(19, '', 'lama@gmail.com', '$2y$10$Tdvxin13EPtOPtcx/lzZtuZZzKWTKCXQNiHCkeuYrks96P7H2GuL2', 'Craftsperson', '2025-05-01 09:09:03');

-- --------------------------------------------------------

--
-- بنية الجدول `new_workshops`
--

CREATE TABLE `new_workshops` (
  `WorkshopId` int(11) NOT NULL,
  `Title` varchar(255) NOT NULL,
  `Description` text NOT NULL,
  `CraftsmanId` int(11) NOT NULL,
  `ScheduledDate` datetime NOT NULL,
  `Duration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `new_workshops`
--

INSERT INTO `new_workshops` (`WorkshopId`, `Title`, `Description`, `CraftsmanId`, `ScheduledDate`, `Duration`) VALUES
(1, 'Pottery Basics', 'تعلم كيفية تشكيل وتشكيل الفخار', 5, '2025-04-10 10:00:00', 2),
(3, 'Sewing Workshop', 'تحسين مهارات الخياطة بالتطبيق العملي', 5, '2025-04-20 09:30:00', 2);

-- --------------------------------------------------------

--
-- بنية الجدول `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `card_holder` varchar(255) NOT NULL,
  `card_number` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `new_artisans`
--
ALTER TABLE `new_artisans`
  ADD PRIMARY KEY (`ArtisanId`),
  ADD KEY `UserId` (`UserId`);

--
-- Indexes for table `new_artisan_files`
--
ALTER TABLE `new_artisan_files`
  ADD PRIMARY KEY (`FileId`),
  ADD KEY `ArtisanId` (`ArtisanId`);

--
-- Indexes for table `new_coursematerials`
--
ALTER TABLE `new_coursematerials`
  ADD PRIMARY KEY (`MaterialId`),
  ADD KEY `CourseId` (`CourseId`);

--
-- Indexes for table `new_courses`
--
ALTER TABLE `new_courses`
  ADD PRIMARY KEY (`CourseId`),
  ADD KEY `CraftsmanId` (`CraftsmanId`);

--
-- Indexes for table `new_enrollments`
--
ALTER TABLE `new_enrollments`
  ADD PRIMARY KEY (`EnrollmentId`),
  ADD KEY `CraftspersonId` (`CraftspersonId`),
  ADD KEY `CourseId` (`CourseId`);

--
-- Indexes for table `new_favorites`
--
ALTER TABLE `new_favorites`
  ADD PRIMARY KEY (`FavoriteId`),
  ADD KEY `CraftspersonId` (`CraftspersonId`),
  ADD KEY `CourseId` (`CourseId`);

--
-- Indexes for table `new_forumposts`
--
ALTER TABLE `new_forumposts`
  ADD PRIMARY KEY (`PostId`),
  ADD KEY `UserId` (`UserId`);

--
-- Indexes for table `new_profiles`
--
ALTER TABLE `new_profiles`
  ADD PRIMARY KEY (`UserId`);

--
-- Indexes for table `new_users`
--
ALTER TABLE `new_users`
  ADD PRIMARY KEY (`UserId`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `new_workshops`
--
ALTER TABLE `new_workshops`
  ADD PRIMARY KEY (`WorkshopId`),
  ADD KEY `CraftsmanId` (`CraftsmanId`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `new_artisans`
--
ALTER TABLE `new_artisans`
  MODIFY `ArtisanId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `new_artisan_files`
--
ALTER TABLE `new_artisan_files`
  MODIFY `FileId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `new_coursematerials`
--
ALTER TABLE `new_coursematerials`
  MODIFY `MaterialId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `new_courses`
--
ALTER TABLE `new_courses`
  MODIFY `CourseId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `new_enrollments`
--
ALTER TABLE `new_enrollments`
  MODIFY `EnrollmentId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `new_favorites`
--
ALTER TABLE `new_favorites`
  MODIFY `FavoriteId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `new_forumposts`
--
ALTER TABLE `new_forumposts`
  MODIFY `PostId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `new_users`
--
ALTER TABLE `new_users`
  MODIFY `UserId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `new_workshops`
--
ALTER TABLE `new_workshops`
  MODIFY `WorkshopId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- قيود الجداول المُلقاة.
--

--
-- قيود الجداول `new_artisans`
--
ALTER TABLE `new_artisans`
  ADD CONSTRAINT `new_artisans_ibfk_1` FOREIGN KEY (`UserId`) REFERENCES `new_users` (`UserId`) ON DELETE SET NULL;

--
-- قيود الجداول `new_artisan_files`
--
ALTER TABLE `new_artisan_files`
  ADD CONSTRAINT `new_artisan_files_ibfk_1` FOREIGN KEY (`ArtisanId`) REFERENCES `new_artisans` (`ArtisanId`) ON DELETE CASCADE;

--
-- قيود الجداول `new_coursematerials`
--
ALTER TABLE `new_coursematerials`
  ADD CONSTRAINT `new_coursematerials_ibfk_1` FOREIGN KEY (`CourseId`) REFERENCES `new_courses` (`CourseId`) ON DELETE CASCADE;

--
-- قيود الجداول `new_courses`
--
ALTER TABLE `new_courses`
  ADD CONSTRAINT `new_courses_ibfk_1` FOREIGN KEY (`CraftsmanId`) REFERENCES `new_users` (`UserId`) ON DELETE CASCADE;

--
-- قيود الجداول `new_enrollments`
--
ALTER TABLE `new_enrollments`
  ADD CONSTRAINT `new_enrollments_ibfk_1` FOREIGN KEY (`CraftspersonId`) REFERENCES `new_users` (`UserId`) ON DELETE CASCADE,
  ADD CONSTRAINT `new_enrollments_ibfk_2` FOREIGN KEY (`CourseId`) REFERENCES `new_courses` (`CourseId`) ON DELETE CASCADE;

--
-- قيود الجداول `new_favorites`
--
ALTER TABLE `new_favorites`
  ADD CONSTRAINT `new_favorites_ibfk_1` FOREIGN KEY (`CraftspersonId`) REFERENCES `new_users` (`UserId`) ON DELETE CASCADE,
  ADD CONSTRAINT `new_favorites_ibfk_2` FOREIGN KEY (`CourseId`) REFERENCES `new_courses` (`CourseId`) ON DELETE CASCADE;

--
-- قيود الجداول `new_forumposts`
--
ALTER TABLE `new_forumposts`
  ADD CONSTRAINT `new_forumposts_ibfk_1` FOREIGN KEY (`UserId`) REFERENCES `new_users` (`UserId`) ON DELETE CASCADE;

--
-- قيود الجداول `new_profiles`
--
ALTER TABLE `new_profiles`
  ADD CONSTRAINT `new_profiles_ibfk_1` FOREIGN KEY (`UserId`) REFERENCES `new_users` (`UserId`) ON DELETE CASCADE;

--
-- قيود الجداول `new_workshops`
--
ALTER TABLE `new_workshops`
  ADD CONSTRAINT `new_workshops_ibfk_1` FOREIGN KEY (`CraftsmanId`) REFERENCES `new_users` (`UserId`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
