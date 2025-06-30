-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 27 يونيو 2025 الساعة 20:46
-- إصدار الخادم: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `courses`
--

-- --------------------------------------------------------

--
-- بنية الجدول `course`
--

CREATE TABLE `course` (
  `course_id` int(11) NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `course_code` varchar(20) NOT NULL,
  `category` varchar(50) NOT NULL,
  `description` mediumtext DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `course`
--

INSERT INTO `course` (`course_id`, `course_name`, `course_code`, `category`, `description`, `price`, `status`) VALUES
(14, 'Python', 'PY101', 'لغات البرمجة', 'دورة متكاملة لتعلم لغة Python من الأساسيات وحتى المستوى المتوسط', 350.00, 'active'),
(15, 'Java', 'JV201', 'لغات البرمجة', 'Java\r\n\"احتراف البرمجة بلغة Java لتطوير التطبيقات القوية\"\r\nابدأ رحلتك في البرمجة الكائنية وتعلم بناء تطبيقات Android وبرامج سطح المكتب.\r\n\r\n', 400.00, 'active'),
(16, 'JavaScript', 'JS301', 'لغات البرمجة', 'JavaScript\r\n\"إتقان JavaScript لتطوير واجهات ويب تفاعلية\"\r\nتغطي الدورة أساسيات JS، إطار عمل React، وإنشاء تطبيقات ويب سريعة.\r\n\r\n', 380.00, 'active'),
(17, 'C#', 'CS401', 'لغات البرمجة', 'C#\r\n\"تطوير تطبيقات Windows باستخدام C# و.NET\"\r\nتعلم البرمجة الكائنية وإنشاء تطبيقات قوية لنظام Windows.', 370.00, 'active'),
(18, 'MySQL', 'MYSQL101', 'قواعد البيانات', 'MySQL\r\n\"إدارة قواعد البيانات باستخدام MySQL باحترافية\"\r\nتعلم تصميم قواعد البيانات، كتابة استعلامات SQL متقدمة، وتحسين الأداء.\r\n', 450.00, 'active'),
(19, 'Oracle', 'ORC201', 'قواعد البيانات', ' Oracle\r\n\"احتراف إدارة قواعد البيانات Oracle للمحترفين\"\r\nشهادة معتمدة تغطي SQL، PL/SQL، وإدارة قواعد البيانات المؤسسية.\r\n', 600.00, 'active'),
(20, ' MongoDB', 'MDB301', 'قواعد البيانات', 'MongoDB\r\n\"تعلم قواعد بيانات NoSQL باستخدام MongoDB\"\r\nدورة عملية لبناء تطبيقات حديثة مع تخزين مرن للبيانات.', 500.00, 'active'),
(21, 'English', 'ENG101', 'لغات المحادثة', 'English\r\n\"دورة محادثة إنجليزية من المستوى المبتدئ إلى المتوسط\"\r\nتحسين النطق، القواعد، ومهارات المحادثة اليومية.\r\n\r\n', 300.00, 'active'),
(22, 'French', 'FRN201', 'لغات المحادثة', 'French\r\n\"تعلم الفرنسية بسهولة: دورة شاملة للمبتدئين\"\r\nتركيز على المحادثة الأساسية والقواعد البسيطة.\r\n\r\n', 280.00, 'active'),
(23, 'German', 'GRM301', 'لغات المحادثة', 'German\r\n\"الألمانية الأساسية: دليل المبتدئين الشامل\"\r\nتغطي المفردات الأساسية والقواعد الأولية للتواصل الفعال.', 320.00, 'active'),
(24, 'Italian', 'ITA401', 'لغات المحادثة', ' Italian\r\n\"الإيطالية للمسافرين: دورة مكثفة في 4 أسابيع\"\r\nتعلم العبارات الأساسية للسياحة والتواصل اليومي.\r\n\r\n', 290.00, 'active'),
(25, 'PHP', 'cs202', 'لغة برمجة', '\"تعلم بناء مواقع ديناميكية باستخدام PHP - من الصفر إلى الاحتراف\"\r\nدورة شاملة تغطي أساسيات PHP، التعامل مع قواعد البيانات، وإنشاء تطبيقات ويب كاملة.\r\n\r\n', 400.00, 'active');

-- --------------------------------------------------------

--
-- بنية الجدول `reservation`
--

CREATE TABLE `reservation` (
  `reservation_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `reservation_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `reservation`
--

INSERT INTO `reservation` (`reservation_id`, `student_id`, `course_id`, `reservation_date`, `status`) VALUES
(23, 1, 14, '2025-06-26 21:00:00', 'active'),
(26, 3, 14, '2025-06-26 21:00:00', 'active'),
(28, 3, 15, '2025-06-26 21:00:00', 'active'),
(30, 3, 18, '2025-06-26 21:00:00', 'active'),
(32, 3, 25, '2025-06-26 21:00:00', 'active'),
(36, 3, 16, '2025-06-26 21:00:00', 'active'),
(40, 3, 19, '2025-06-26 21:00:00', 'active'),
(49, 5, 14, '2025-06-26 21:00:00', 'active'),
(50, 5, 21, '2025-06-26 21:00:00', 'active'),
(51, 5, 24, '2025-06-26 21:00:00', 'active'),
(53, 2, 25, '2025-06-26 21:00:00', 'active'),
(54, 11, 14, '2025-06-26 21:00:00', 'active'),
(55, 11, 19, '2025-06-26 21:00:00', 'active'),
(56, 11, 21, '2025-06-26 21:00:00', 'active');

-- --------------------------------------------------------

--
-- بنية الجدول `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `student_name` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `username` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `password` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `email` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `mobile` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `regster_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `remember_token` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `students`
--

INSERT INTO `students` (`student_id`, `student_name`, `username`, `password`, `email`, `mobile`, `regster_date`, `remember_token`) VALUES
(1, 'mohamed', 'mohamed1', '$2y$10$21fSPJ2/rEJAebEqYJ7vIuU5ct1NI6E/Xx7tPh6XqsrmTdLBjgpG.', 'mohamed@yahoo.com', '123456', '2025-06-26 01:38:32', NULL),
(2, 'ahmed', 'ahmed9', '$2y$10$NfZZnVBmJDRsh7mVpxNC.ek.zmBd/SNA/lusncWSZAEJIbTYBcDie', 'ahmed@hotmail', '999999', '2025-06-26 11:27:21', NULL),
(3, 'aa', 'aa8', '$2y$10$UtA.zRum4CoyP/WU8p0Dier.ek8SFeQ0OyNb0gIvYUkrPeSclLAku', 'aa@yahoo', '8888', '2025-06-26 11:28:51', NULL),
(4, 'serag', 'serag7', '$2y$10$iApjkTIiRSU47uCDz0xa.eD78/GAWK89n2PK0njOY2YnwRMLiq0Dm', 'serag@yahoo', '77', '2025-06-26 19:23:31', NULL),
(5, 'Ø¹Ù„ÙŠ', 'ali6', '$2y$10$w51.2rb.fAkPsxz5L89nqeNsylhPI2oHO8HO.5aZMf8kiCcx.37.y', 'ali@hot', '66', '2025-06-26 19:28:41', NULL),
(6, 'assel', 'assel98', '$2y$10$KpHCgilhmnb7ubiaKfJYju1sszHCpjwYdVTCR75yY9qLhdmkXSVJG', 'assel@yahoo', '999999888', '2025-06-26 19:39:45', NULL),
(7, 'aa', 'aa888', '$2y$10$CcKmaGnRIOjtyFixBIjEVeYy10hvpnq.DbS.fGUAA86iZegIO9X/i', 'aa@yahh', '88888', '2025-06-26 19:47:46', NULL),
(8, 'bb', 'bb66', '$2y$10$rcDuVskGOiCpqskaMu1E/.D7IjJauYVpUv3RmwvbH72/vBavxAHoi', 'bb@hh', '766', '2025-06-26 19:53:16', NULL),
(9, 'ab', 'ab66', '$2y$10$Hkka.5F7Gw9rL67aagyMsOx0cMmFLeTw6ZeJ/tm1c1FUnTd1h5Tgm', 'ab@yahh', '66', '2025-06-26 19:55:22', NULL),
(10, 'asd', 'asd87', '$2y$10$K9NrS9.di94xI6QrFq3ziOrmJOM8O0JMP3/5sN1R/n4dm3fqO9fDG', 'asd@yahh', '87', '2025-06-26 20:06:23', NULL),
(11, 'msm', 'msm1', '$2y$10$us1wFzKl4AuftLOZpdKw8u.Ic/sG0FBtaKnrsbCkJkv6DuUHJjKhi', 'msm@yahh', '789', '2025-06-26 20:41:30', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `course`
--
ALTER TABLE `course`
  ADD PRIMARY KEY (`course_id`),
  ADD UNIQUE KEY `course_code` (`course_code`);

--
-- Indexes for table `reservation`
--
ALTER TABLE `reservation`
  ADD PRIMARY KEY (`reservation_id`),
  ADD UNIQUE KEY `reservation_id` (`reservation_id`),
  ADD UNIQUE KEY `student_id` (`student_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `student_id` (`student_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `course`
--
ALTER TABLE `course`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `reservation`
--
ALTER TABLE `reservation`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- قيود الجداول المُلقاة.
--

--
-- قيود الجداول `reservation`
--
ALTER TABLE `reservation`
  ADD CONSTRAINT `reservation_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `reservation_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
