-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `recruitment_db`
--

--
DROP DATABASE IF EXISTS `recruitment_db`;
CREATE DATABASE IF NOT EXISTS `recruitment_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `recruitment_db`;



-- Cấu trúc bảng cho bảng `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(180) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('employer','jobseeker') NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `role`, `created_at`, `last_login`, `status`) VALUES
(1, 'user@gmail.com', '$2y$10$n9EtkjqCEKiDgBfmXFUdReFbt0a2/f7xLTSvGuSCWZgzmiaTCGqUi', 'jobseeker', '2026-05-01 23:37:32', '2026-05-02 00:06:11', 'active'),
(2, 'user2@gmail.com', '$2y$10$nWAb3hNmJjD2m4BP4BBR8.4LTO3s9eOyxcZ2a1VAryZcRFJz4xJmy', 'jobseeker', '2026-05-01 23:38:21', NULL, 'active'),
(3, 'user3@gmail.com', '$2y$10$5/swTIzl2SBmAKBZOrZMd.HeGjzImEs7aYz1mPMu5iQ6C54wHzy7S', 'jobseeker', '2026-05-01 23:45:51', NULL, 'active'),
(4, 'user4@gmail.com', '$2y$10$HB3WrfQ1MX.9KiCI7fQKzuuvxALjUiMhMYS5A1in45d2nqEm5OGhO', 'jobseeker', '2026-05-01 23:46:10', NULL, 'active'),
(5, 'user5@gmail.com', '$2y$10$S3jY0ogX9DlRgWFuK/oRN.2b0jUcGQeI8d3xj8oWeAko/lQS4Kc4u', 'jobseeker', '2026-05-01 23:46:41', NULL, 'active'),
(6, 'user6@gmail.com', '$2y$10$FO0WvsePeW0vmJV6hy0ygOGwjMUDF5qKCoYzAdzK4MxmsEqJ0z5bS', 'employer', '2026-05-01 23:47:01', NULL, 'active'),
(7, 'user7@gmail.com', '$2y$10$eBxfSYvh74MkysMcEerluediAIEo564L6rwI1ampXqGZiKcPBlKNu', 'employer', '2026-05-01 23:47:41', NULL, 'active'),
(8, 'user8@gmail.com', '$2y$10$Hsp8T7H2EQBcKWAAcvFg4.jsR7SrU36yFIxazP27tn6DfD.oRhia.', 'employer', '2026-05-01 23:48:10', NULL, 'active'),
(9, 'user9@gmail.com', '$2y$10$N6r5MAHgHuPrtl80bNOR9.pLjIqBRIy9tnqsIxIbyPK60Mus.XiEO', 'employer', '2026-05-01 23:48:39', NULL, 'active'),
(10, 'user10@gmail.com', '$2y$10$sHXPtb8R9oplMUdAXZyCDeVvx5f..ZsargnTZ13bkmJk8ABHIcRsq', 'employer', '2026-05-01 23:49:00', '2026-05-02 00:30:37', 'active'),
(11, 'user11@gmail.com', '$2y$10$aC6Od2IGU8.tZHjJYA2REeFSf2SyuORyzAe4BuDe3OLZOPtO0U40K', 'jobseeker', '2026-05-02 00:23:21', NULL, 'active'),
(12, 'user12@gmail.com', '$2y$10$JEY.85Tspns2HzM303YSWuHvb7p708KQY/2ltm0DGS3B6M/WLdxwm', 'jobseeker', '2026-05-02 00:23:41', NULL, 'active'),
(13, 'user13@gmail.com', '$2y$10$eSM/z1bg7n70AlAnQ6EFuuPf1lgJflmJLlCBX5CMdtSUcjKej7og.', 'jobseeker', '2026-05-02 00:24:05', NULL, 'active'),
(14, 'user14@gmail.com', '$2y$10$0WIHcS06CXJ2k/nfZGSZ6.uFTMlnvXiEL9RUya7vGhWEOf66Wm9bq', 'jobseeker', '2026-05-02 00:24:24', NULL, 'active'),
(15, 'user15@gmail.com', '$2y$10$fpofl3GN8EWAonbedghZX.qszn0WvMtv.0oHRYXKFsUmRdg9hFJuS', 'jobseeker', '2026-05-02 00:24:44', NULL, 'active'),
(16, 'user16@gmail.com', '$2y$10$Ktb4KnJ31UvSpXFVjWP/9ODoOGrWc3uwIku56FzpSQhBxNml1P6si', 'employer', '2026-05-02 00:25:07', NULL, 'active'),
(17, 'user17@gmail.com', '$2y$10$I3F5A1v6sn1Ie5KUmtkxRuITjprjvJbe5rSHPEMx4Qwt38lrxMXG2', 'employer', '2026-05-02 00:25:27', NULL, 'active'),
(18, 'user18@gmail.com', '$2y$10$LjbXRAr30x3wykwaVYt/TuUvpAGDQ6BVDPWHIEIqfXrG6Yv.bH4.6', 'employer', '2026-05-02 00:25:48', NULL, 'active'),
(19, 'user19@gmail.com', '$2y$10$rNkbbwJ2ztMti28mPdIRM.RKyLk9ZzazjN/uHSaeWkAXx6EO1CJau', 'employer', '2026-05-02 00:26:09', NULL, 'active'),
(20, 'user20@gmail.com', '$2y$10$/9Ffhy5YasbjTMoXkmjTJunG62weNxXSMSomz0owukznCXjpUWxTG', 'employer', '2026-05-02 00:26:34', NULL, 'active');

--




-- --------------------------------------------------------


-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `employer_profiles`
--

DROP TABLE IF EXISTS `employer_profiles`;
CREATE TABLE `employer_profiles` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `company_name` varchar(200) NOT NULL DEFAULT '',
  `description` text DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `no_employees` int(10) UNSIGNED DEFAULT NULL COMMENT 'Number of employees',
  `business_registration_no` varchar(50) DEFAULT NULL COMMENT 'Số đăng ký kinh doanh'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `employer_profiles`
--

INSERT INTO `employer_profiles` (`id`, `user_id`, `company_name`, `description`, `logo_path`, `website`, `address`, `no_employees`, `business_registration_no`) VALUES
(1, 6, 'TechCorp Solutions', 'Công ty hàng đầu về giải pháp Fintech và chuyển đổi số tại Việt Nam.', 'logos/techcorp.png', 'https://techcorp.vn', 'Quận 1, HCM', 500, '0101234567'),
(2, 7, 'VinFast JSC', 'Nhà sản xuất ô tô và xe máy điện thông minh đầu tiên của Việt Nam.', 'logos/vinfast.png', 'https://vinfastauto.com', 'Hải Phòng', 5000, '0107894561'),
(3, 8, 'FPT Software', 'Tập đoàn công nghệ hàng đầu, chuyên cung cấp dịch vụ phần mềm cho thị trường toàn cầu.', 'logos/fpt_soft.png', 'https://fpt-software.com', 'Quận 9, HCM', 15000, '0101248144'),
(4, 9, 'Shopee Việt Nam', 'Nền tảng thương mại điện tử hàng đầu tại Đông Nam Á và Đài Loan.', 'logos/shopee.png', 'https://shopee.vn', 'Quận 7, HCM', 2000, '0312776846'),
(5, 10, 'VNG Corporation', 'Kỳ lân công nghệ Việt Nam với hệ sinh thái Zalo, ZaloPay và game online.', 'logos/vng.png', 'https://vng.com.vn', 'Quận 7, HCM', 3000, '0303491142'),
(6, 16, 'Viettel Group', 'Tập đoàn viễn thông và công nghệ đa quốc gia, dẫn đầu trong việc cung cấp các giải pháp số và tài chính Fintech tại Việt Nam.', NULL, 'https://viettel.com.vn', 'Quận Cầu Giấy, Hà Nội', 50000, '0100109106'),
(7, 17, 'Grab Vietnam', 'Siêu ứng dụng hàng đầu Đông Nam Á, kết nối người dùng với các dịch vụ thiết yếu hàng ngày từ vận chuyển đến giao nhận đồ ăn.', NULL, 'https://grab.com', 'Quận 7, TP.HCM', 1000, '0312650437'),
(8, 18, 'Momo (M_Service)', 'Nền tảng ví điện tử và ứng dụng tài chính số hàng đầu, cung cấp trải nghiệm thanh toán một chạm cho mọi nhu cầu.', NULL, 'https://momo.vn', 'Quận 7, TP.HCM', 1500, '0303982862'),
(9, 19, 'Tiki Corporation', 'Hệ sinh thái thương mại điện tử uy tín nhất Việt Nam với hệ thống kho vận thông minh và dịch vụ giao hàng nhanh.', NULL, 'https://tiki.vn', 'Quận Tân Bình, TP.HCM', 3000, '0309532909'),
(10, 20, 'Samsung Electronics', 'Tập đoàn điện tử toàn cầu, chuyên sản xuất và kinh doanh các thiết bị công nghệ cao với quy mô sản xuất khổng lồ.', NULL, 'https://samsung.com', 'Thái Nguyên', 60000, '0100108712');

-- --------------------------------------------------------
--
-- Cấu trúc bảng cho bảng `jobseeker_profiles`
--

DROP TABLE IF EXISTS `jobseeker_profiles`;
CREATE TABLE `jobseeker_profiles` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `fullname` varchar(150) NOT NULL DEFAULT '',
  `phone` varchar(30) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `cv_path` varchar(255) DEFAULT NULL,
  `avatar_path` varchar(255) DEFAULT NULL,
  `last_update` datetime DEFAULT NULL,
  `skills` text DEFAULT NULL COMMENT 'Comma-separated list of skills',
  `education` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `jobseeker_profiles`
--

INSERT INTO `jobseeker_profiles` (`id`, `user_id`, `fullname`, `phone`, `bio`, `cv_path`, `avatar_path`, `last_update`, `skills`, `education`, `location`, `status`) VALUES
(1, 1, 'Nguyễn Văn A', '0901234001', 'Lập trình viên Java nhiệt huyết, đam mê xây dựng hệ thống backend hiệu năng cao.', 'assets/uploads/cv_69f4decee00eb8.07800665.pdf', 'avatars/user1.jpg', '2026-05-02 00:11:42', 'Java, Spring Boot, MySQL', 'Đại học Bách Khoa TP.HCM', 'Quận 10, TP.HCM', 'active'),
(2, 2, 'Trần Thị B', '0901234002', 'Chuyên gia Python với kinh nghiệm xử lý dữ liệu và xây dựng API RESTful.', 'CV.pdf', 'avatars/user2.jpg', '2026-05-02 00:05:23', 'Python, Django, MySQL', 'Đại học Khoa học Tự nhiên', 'Quận Thủ Đức, TP.HCM', 'active'),
(3, 3, 'Lê Văn C', '0901234003', 'Frontend Developer yêu thích ReactJS và việc tối ưu hóa trải nghiệm người dùng.', 'CV.pdf', 'avatars/user3.jpg', '2026-05-02 00:05:23', 'ReactJS, NodeJS, MongoDB', 'Đại học Công nghệ Thông tin (UIT)', 'Dĩ An, Bình Dương', 'active'),
(4, 4, 'Phạm Minh D', '0901234004', 'Fullstack Developer với thế mạnh về PHP và các framework hiện đại như Laravel.', 'CV.pdf', 'avatars/user4.jpg', '2026-05-02 00:05:23', 'PHP, Laravel, VueJS', 'Đại học Cần Thơ', 'Ninh Kiều, Cần Thơ', 'active'),
(5, 5, 'AnhTuna Dev', '0900000005', 'Backend Engineer tương lai, thủ khoa chuyên Lý, đang theo đuổi sự nghiệp lập trình chuyên nghiệp.', 'CV.pdf', 'avatars/anhtuna.jpg', '2026-05-02 00:05:23', 'Backend, Python, Git', 'Đại học Công nghệ Thông tin (UIT)', 'Quận 9, TP.HCM', 'active'),
(6, 11, 'Lê Thị T', '0901111111', 'Chuyên viên phân tích tài chính với 3 năm kinh nghiệm mảng Fintech.', NULL, NULL, '2026-05-02 00:39:27', 'CFA, Finance, Excel, SQL', 'Đại học Kinh tế HCM', 'Quận 1, TP.HCM', 'active'),
(7, 12, 'Hoàng ', '0902222222', 'Digital Marketing Specialist đam mê tối ưu hóa chuyển đổi.', NULL, NULL, '2026-05-02 00:39:27', 'Facebook Ads, Google Analytics, Content', 'Đại học RMIT', 'Quận 7, TP.HCM', 'active'),
(8, 13, 'Ngô N', '0903333333', 'Chuyên gia săn đầu người mảng công nghệ (Headhunter).', NULL, NULL, '2026-05-02 00:39:27', 'IT Recruitment, HR, Headhunt', 'Đại học KHXH&NV', 'Quận 3, TP.HCM', 'active'),
(9, 14, 'Đặng văn', '0904444444', 'Chuyên viên phát triển thị trường quốc tế.', NULL, NULL, '2026-05-02 00:39:27', 'Đàm phán, Tiếng Anh', 'Đại học Kinh tế - Luật', 'Hà Nội', 'active'),
(10, 15, 'Bùi Văn A', '0905555555', 'Quản lý vận hành và điều phối kho vận thông minh.', NULL, NULL, '2026-05-02 00:39:27', 'Logistics Management, Supply Chain', 'Đại học Giao thông Vận tải', 'Quận Thủ Đức, TP.HCM', 'active');

--


-- ----------------
-- Cấu trúc bảng cho bảng `job_posts`
--

DROP TABLE IF EXISTS `job_posts`;
CREATE TABLE `job_posts` (
  `id` int(10) UNSIGNED NOT NULL,
  `employer_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `requirements` text DEFAULT NULL,
  `salary` varchar(100) DEFAULT NULL COMMENT 'DEPRECATED — use salary_min/salary_max',
  `salary_min` enum('below_10M','10M_15M','15M_20M','20M_30M','30M_50M','above_50M') DEFAULT NULL,
  `salary_max` enum('below_10M','10M_15M','15M_20M','20M_30M','30M_50M','above_50M') DEFAULT NULL,
  `currency` enum('VND','USD','EUR') NOT NULL DEFAULT 'VND',
  `end_date` date DEFAULT NULL COMMENT 'Application deadline',
  `recruit_type` enum('fulltime','parttime','online','offline') NOT NULL DEFAULT 'fulltime',
  `location` varchar(150) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `status` enum('open','closed') NOT NULL DEFAULT 'open',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `job_posts`
--

INSERT INTO `job_posts` (`id`, `employer_id`, `title`, `description`, `requirements`, `salary`, `salary_min`, `salary_max`, `currency`, `end_date`, `recruit_type`, `location`, `category`, `status`, `created_at`) VALUES
(1, 1, 'Senior Java Developer', 'Làm dự án Fintech', 'Java, Spring Boot', NULL, '20M_30M', '30M_50M', 'VND', NULL, 'fulltime', 'HCM', NULL, 'open', '2026-05-01 23:53:38'),
(2, 1, 'Frontend Lead (React)', 'Phát triển UI/UX', 'ReactJS, JavaScript', NULL, '20M_30M', '30M_50M', 'VND', NULL, 'fulltime', 'HCM', NULL, 'open', '2026-05-01 23:53:38'),
(3, 3, 'Python AI Engineer', 'Hệ thống gợi ý', 'Python, MySQL', NULL, '15M_20M', '20M_30M', 'VND', NULL, 'fulltime', 'HCM', NULL, 'open', '2026-05-01 23:53:38'),
(4, 3, 'Database Administrator', 'Quản trị hệ thống SQL', 'SQL, MySQL', NULL, '15M_20M', '20M_30M', 'VND', NULL, 'fulltime', 'HCM', NULL, 'open', '2026-05-01 23:53:38'),
(5, 5, 'Backend Developer (Node)', 'Phát triển ZaloPay', 'NodeJS, MySQL', NULL, '20M_30M', '30M_50M', 'VND', NULL, 'fulltime', 'HCM', NULL, 'open', '2026-05-01 23:53:38'),
(6, 5, 'System Architect', 'Thiết kế hệ thống lớn', 'AWS, Microservices', NULL, '30M_50M', 'above_50M', 'VND', NULL, 'fulltime', 'HCM', NULL, 'open', '2026-05-01 23:53:38'),
(7, 2, 'Embedded Engineer', 'Hệ thống xe điện', 'C, C++', NULL, '15M_20M', '20M_30M', 'VND', NULL, 'fulltime', 'Hải Phòng', NULL, 'open', '2026-05-01 23:53:38'),
(8, 2, 'Automotive Tester', 'Test phần mềm ô tô', 'Manual Test', NULL, '10M_15M', '15M_20M', 'VND', NULL, 'fulltime', 'Hải Phòng', NULL, 'open', '2026-05-01 23:53:38'),
(9, 4, 'QC Automation Engineer', 'Kiểm thử Shopee Pay', 'Selenium, Java', NULL, '10M_15M', '15M_20M', 'VND', NULL, 'fulltime', 'HCM', NULL, 'open', '2026-05-01 23:53:38'),
(10, 4, 'Mobile App Dev (Flutter)', 'App Shopee Food', 'Flutter, Dart', NULL, '15M_20M', '20M_30M', 'VND', NULL, 'fulltime', 'HCM', NULL, 'open', '2026-05-01 23:53:38'),
(11, 6, 'Chuyên viên Phân tích Tài chính', 'Phân tích dự báo dòng tiền Fintech', 'Finance, Excel, SQL', NULL, '20M_30M', '30M_50M', 'VND', NULL, 'fulltime', 'Hà Nội', NULL, 'open', '2026-05-02 00:41:03'),
(12, 6, 'IT Recruiter Specialist', 'Tuyển dụng nhân sự AI/Cloud', 'IT Recruitment, HR', NULL, '15M_20M', '20M_30M', 'VND', NULL, 'fulltime', 'Hà Nội', NULL, 'open', '2026-05-02 00:41:03'),
(13, 7, 'Digital Marketing Lead', 'Tối ưu quảng cáo GrabFood', 'Facebook Ads, Marketing', NULL, '20M_30M', 'above_50M', 'VND', NULL, 'fulltime', 'HCM', NULL, 'open', '2026-05-02 00:41:03'),
(14, 7, 'Key Account Manager (B2B)', 'Phát triển đối tác GrabExpress', 'B2B Sale, Đàm phán', NULL, '15M_20M', '30M_50M', 'VND', NULL, 'fulltime', 'HCM', NULL, 'open', '2026-05-02 00:41:03'),
(15, 8, 'Performance Marketing', 'Chạy quảng cáo cho ví Momo', 'Google Analytics, Content', NULL, '15M_20M', '20M_30M', 'VND', NULL, 'fulltime', 'HCM', NULL, 'open', '2026-05-02 00:41:03'),
(16, 8, 'Kế toán tổng hợp', 'Đối soát giao dịch ví điện tử', 'Kế toán, Finance', NULL, '15M_20M', '20M_30M', 'VND', NULL, 'fulltime', 'HCM', NULL, 'open', '2026-05-02 00:41:03'),
(17, 9, 'Content Creator (TikiTikTok)', 'Sáng tạo video ngắn', 'TikTok Marketing, Creative', NULL, '10M_15M', '15M_20M', 'VND', NULL, 'fulltime', 'HCM', NULL, 'open', '2026-05-02 00:41:03'),
(18, 9, 'Warehouse Manager', 'Quản lý kho vận thông minh', 'Logistics Management', NULL, '20M_30M', '30M_50M', 'VND', NULL, 'fulltime', 'HCM', NULL, 'open', '2026-05-02 00:41:03'),
(19, 10, 'Chuyên viên Pháp chế', 'Rà soát hợp đồng kinh tế', 'Luật lao động, Tiếng Anh', NULL, '15M_20M', '20M_30M', 'VND', NULL, 'fulltime', 'Hà Nội', NULL, 'open', '2026-05-02 00:41:03'),
(20, 10, 'Sale Executive (Global)', 'Tìm kiếm đối tác linh kiện', 'Sale, Tiếng Anh', NULL, '20M_30M', 'above_50M', 'VND', NULL, 'fulltime', 'Thái Nguyên', NULL, 'open', '2026-05-02 00:41:03');

-- --------------------------------------------------------
-- Cấu trúc bảng cho bảng `followed_companies`
--

DROP TABLE IF EXISTS `followed_companies`;
CREATE TABLE `followed_companies` (
  `id` int(10) UNSIGNED NOT NULL,
  `jobseeker_id` int(10) UNSIGNED NOT NULL,
  `employer_id` int(10) UNSIGNED NOT NULL,
  `followed_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `followed_companies`
--

INSERT INTO `followed_companies` (`id`, `jobseeker_id`, `employer_id`, `followed_at`) VALUES
(1, 1, 1, '2026-05-01 23:59:08'),
(2, 1, 3, '2026-05-01 23:59:08'),
(3, 2, 3, '2026-05-01 23:59:08'),
(4, 3, 5, '2026-05-01 23:59:08'),
(5, 4, 2, '2026-05-01 23:59:08'),
(6, 5, 1, '2026-05-01 23:59:08'),
(7, 5, 3, '2026-05-01 23:59:08'),
(8, 5, 5, '2026-05-01 23:59:08'),
(9, 5, 4, '2026-05-01 23:59:08'),
(10, 3, 1, '2026-05-01 23:59:08');

-- --------------------------------------------------------
--
-- Cấu trúc bảng cho bảng `applications`
--

DROP TABLE IF EXISTS `applications`;
CREATE TABLE `applications` (
  `id` int(10) UNSIGNED NOT NULL,
  `job_id` int(10) UNSIGNED NOT NULL,
  `jobseeker_id` int(10) UNSIGNED NOT NULL,
  `cover_letter` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `applied_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `applications`
--

INSERT INTO `applications` (`id`, `job_id`, `jobseeker_id`, `cover_letter`, `status`, `applied_at`) VALUES
(22, 3, 1, 'Em cũng quan tâm đến AI tại VinFast', 'pending', '2026-05-01 23:58:32'),
(23, 4, 2, 'Em muốn thử sức làm Tester', 'pending', '2026-05-01 23:58:32'),
(24, 5, 2, 'Em có kỹ năng Python phù hợp AI', 'pending', '2026-05-01 23:58:32'),
(25, 6, 3, 'Em ứng tuyển vị trí Database Admin', 'pending', '2026-05-01 23:58:32'),
(26, 7, 3, 'Em muốn làm QC Automation', 'pending', '2026-05-01 23:58:32'),
(27, 8, 4, 'Em ứng tuyển Flutter Developer', 'pending', '2026-05-01 23:58:32'),
(28, 9, 4, 'Em muốn làm Backend tại VNG', 'pending', '2026-05-01 23:58:32'),
(29, 10, 5, 'AnhTuna ứng tuyển System Architect', 'pending', '2026-05-01 23:58:32'),
(30, 2, 5, 'AnhTuna muốn làm Frontend Lead', 'pending', '2026-05-01 23:58:32'),
(31, 1, 1, 'Em xin ứng tuyển ạ', 'pending', '2026-05-02 00:15:53');

--



-- --------------------------------------------------------

--


--
-- Cấu trúc bảng cho bảng `saved_jobs`
--

DROP TABLE IF EXISTS `saved_jobs`;
CREATE TABLE `saved_jobs` (
  `id` int(10) UNSIGNED NOT NULL,
  `jobseeker_id` int(10) UNSIGNED NOT NULL,
  `job_id` int(10) UNSIGNED NOT NULL,
  `saved_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `saved_jobs`
--

INSERT INTO `saved_jobs` (`id`, `jobseeker_id`, `job_id`, `saved_at`) VALUES
(1, 1, 1, '2026-05-01 23:59:08'),
(2, 1, 2, '2026-05-01 23:59:08'),
(3, 2, 3, '2026-05-01 23:59:08'),
(4, 3, 5, '2026-05-01 23:59:08'),
(5, 4, 6, '2026-05-01 23:59:08'),
(6, 5, 1, '2026-05-01 23:59:08'),
(7, 5, 3, '2026-05-01 23:59:08'),
(8, 5, 5, '2026-05-01 23:59:08'),
(9, 2, 10, '2026-05-01 23:59:08'),
(10, 3, 10, '2026-05-01 23:59:08');

-- --------------------------------------------------------

--

-- ----------------------------

DELIMITER $$
--
-- Thủ tục
--
DROP PROCEDURE IF EXISTS `sp_apply_job`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_apply_job` (IN `p_job_id` INT, IN `p_jobseeker_id` INT, IN `p_cover_letter` TEXT)   BEGIN
    DECLARE v_job_status ENUM('open', 'closed');
    DECLARE v_end_date DATE;

    -- Lấy thông tin trạng thái công việc
    SELECT status, end_date INTO v_job_status, v_end_date 
    FROM job_posts WHERE id = p_job_id;

    -- Kiểm tra điều kiện
    IF v_job_status = 'closed' OR (v_end_date IS NOT NULL AND v_end_date < CURDATE()) THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'This job is no longer accepting applications.';
    ELSE
        INSERT INTO applications (job_id, jobseeker_id, cover_letter, status)
        VALUES (p_job_id, p_jobseeker_id, p_cover_letter, 'pending');
    END IF;
END$$

DROP PROCEDURE IF EXISTS `sp_close_job_and_reject_others`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_close_job_and_reject_others` (IN `p_job_id` INT)   BEGIN
    -- 1. Cập nhật trạng thái bài đăng thành 'closed'
    UPDATE job_posts SET status = 'closed' WHERE id = p_job_id;

    -- 2. Từ chối tất cả các đơn ứng tuyển còn đang chờ (pending)
    UPDATE applications 
    SET status = 'rejected' 
    WHERE job_id = p_job_id AND status = 'pending';
    
    SELECT 'Công việc đã đóng và các đơn chờ đã được thông báo từ chối.' AS result;
END$$

DROP PROCEDURE IF EXISTS `sp_recommend_jobs_for_seeker`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_recommend_jobs_for_seeker` (IN `p_seeker_id` INT)   BEGIN
    DECLARE v_seeker_skills TEXT;
    
    -- Lấy danh sách kỹ năng của người tìm việc
    -- Giả sử định dạng: "Java, SQL, Python"
    SELECT skills INTO v_seeker_skills 
    FROM jobseeker_profiles 
    WHERE id = p_seeker_id;
    
    -- Tìm các công việc có 'requirements' chứa từ khóa kỹ năng của ứng viên
    SELECT 
        id, 
        title, 
        location, 
        salary_min, 
        salary_max, 
        requirements -- Cột yêu cầu công việc
    FROM job_posts
    WHERE status = 'open' 
      AND (
          -- Ép kiểu COLLATE để tránh lỗi #1267 (Illegal mix of collations)
          requirements COLLATE utf8mb4_unicode_ci LIKE CONCAT('%', v_seeker_skills COLLATE utf8mb4_unicode_ci, '%')
          OR 
          v_seeker_skills COLLATE utf8mb4_unicode_ci LIKE CONCAT('%', requirements COLLATE utf8mb4_unicode_ci, '%')
      )
    ORDER BY created_at DESC
    LIMIT 10;
END$$

--
-- Các hàm
--
DROP FUNCTION IF EXISTS `fn_count_open_jobs`$$
CREATE DEFINER=`root`@`localhost` FUNCTION `fn_count_open_jobs` (`p_employer_id` INT) RETURNS INT(11) DETERMINISTIC READS SQL DATA BEGIN
    DECLARE v_count INT DEFAULT 0;
    SELECT COUNT(*) INTO v_count
    FROM job_posts
    WHERE employer_id = p_employer_id AND status = 'open';
    return v_count;
END$$

DROP FUNCTION IF EXISTS `fn_jobseeker_has_active_application`$$
CREATE DEFINER=`root`@`localhost` FUNCTION `fn_jobseeker_has_active_application` (`p_jobseeker_id` INT, `p_employer_id` INT) RETURNS TINYINT(1) DETERMINISTIC READS SQL DATA BEGIN
    DECLARE v_count INT DEFAULT 0;
    SELECT COUNT(*) INTO v_count
    FROM   applications a
    JOIN   job_posts    jp ON jp.id = a.job_id
    WHERE  a.jobseeker_id = p_jobseeker_id
      AND  jp.employer_id = p_employer_id
      AND  a.status IN ('pending', 'approved');
    RETURN v_count > 0;
END$$

DELIMITER ;
-- --------------------------------------------------------
-- các bẫy
-- Bẫy `users`
--
DROP TRIGGER IF EXISTS `trg_check_user_last_login`;
DELIMITER $$
CREATE TRIGGER `trg_check_user_last_login` BEFORE UPDATE ON `users` FOR EACH ROW BEGIN
    IF NEW.last_login IS NOT NULL AND DATEDIFF(NOW(), NEW.last_login) > 1095 THEN
        SET NEW.status = 'inactive';
    END IF;
END
$$
DELIMITER ;

-- Bẫy `jobseeker_profiles`
--
DROP TRIGGER IF EXISTS `trg_jobseeker_last_update`;
DELIMITER $$
CREATE TRIGGER `trg_jobseeker_last_update` BEFORE UPDATE ON `jobseeker_profiles` FOR EACH ROW BEGIN
    SET NEW.last_update = NOW();
    IF OLD.last_update IS NOT NULL
       AND DATEDIFF(NOW(), OLD.last_update) > 1825 THEN
        UPDATE `users` SET `status` = 'inactive' WHERE `id` = NEW.user_id;
    END IF;
END
$$
DELIMITER ;

-- Bẫy `applications`
--
DROP TRIGGER IF EXISTS `trg_prevent_duplicate_application`;
DELIMITER $$
CREATE TRIGGER `trg_prevent_duplicate_application` BEFORE INSERT ON `applications` FOR EACH ROW BEGIN
    DECLARE v_employer_id INT;
    SELECT employer_id INTO v_employer_id
    FROM   job_posts WHERE id = NEW.job_id;

    IF fn_jobseeker_has_active_application(NEW.jobseeker_id, v_employer_id) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'You already have an active application with this employer.';
    END IF;
END
$$
DELIMITER ;



--
-- Cấu trúc đóng vai cho view `view_detailed_job_applications`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `view_detailed_job_applications`;
CREATE TABLE `view_detailed_job_applications` (
`application_id` int(10) unsigned
,`job_id` int(10) unsigned
,`job_title` varchar(200)
,`employer_id` int(10) unsigned
,`candidate_name` varchar(150)
,`candidate_email` varchar(180)
,`candidate_phone` varchar(30)
,`application_status` enum('pending','approved','rejected')
,`applied_at` datetime
,`cv_path` varchar(255)
);

-- --------------------------------------------------------

--
-- Cấu trúc đóng vai cho view `view_job_post_analytics`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `view_job_post_analytics`;
CREATE TABLE `view_job_post_analytics` (
`job_id` int(10) unsigned
,`title` varchar(200)
,`company_name` varchar(200)
,`job_status` enum('open','closed')
,`end_date` date
,`total_applications` bigint(21)
,`total_saves` bigint(21)
,`days_left` int(7)
);

-- --------------------------------------------------------

--
-- Cấu trúc cho view `view_detailed_job_applications`
--
DROP TABLE IF EXISTS `view_detailed_job_applications`;

DROP VIEW IF EXISTS `view_detailed_job_applications`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_detailed_job_applications`  AS SELECT `a`.`id` AS `application_id`, `jp`.`id` AS `job_id`, `jp`.`title` AS `job_title`, `jp`.`employer_id` AS `employer_id`, `jsp`.`fullname` AS `candidate_name`, `u`.`email` AS `candidate_email`, `jsp`.`phone` AS `candidate_phone`, `a`.`status` AS `application_status`, `a`.`applied_at` AS `applied_at`, `jsp`.`cv_path` AS `cv_path` FROM (((`applications` `a` join `job_posts` `jp` on(`a`.`job_id` = `jp`.`id`)) join `jobseeker_profiles` `jsp` on(`a`.`jobseeker_id` = `jsp`.`id`)) join `users` `u` on(`jsp`.`user_id` = `u`.`id`)) ;

-- --------------------------------------------------------

--
-- Cấu trúc cho view `view_job_post_analytics`
--
DROP TABLE IF EXISTS `view_job_post_analytics`;

DROP VIEW IF EXISTS `view_job_post_analytics`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_job_post_analytics`  AS SELECT `jp`.`id` AS `job_id`, `jp`.`title` AS `title`, `ep`.`company_name` AS `company_name`, `jp`.`status` AS `job_status`, `jp`.`end_date` AS `end_date`, count(distinct `a`.`id`) AS `total_applications`, count(distinct `sj`.`id`) AS `total_saves`, to_days(`jp`.`end_date`) - to_days(curdate()) AS `days_left` FROM (((`job_posts` `jp` join `employer_profiles` `ep` on(`jp`.`employer_id` = `ep`.`id`)) left join `applications` `a` on(`jp`.`id` = `a`.`job_id`)) left join `saved_jobs` `sj` on(`jp`.`id` = `sj`.`job_id`)) GROUP BY `jp`.`id` ;

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_jobseeker_employer_active` (`job_id`,`jobseeker_id`),
  ADD KEY `idx_app_jobseeker` (`jobseeker_id`);

--
-- Chỉ mục cho bảng `employer_profiles`
--
ALTER TABLE `employer_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_ep_user` (`user_id`);

--
-- Chỉ mục cho bảng `followed_companies`
--
ALTER TABLE `followed_companies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_fc_seeker_employer` (`jobseeker_id`,`employer_id`),
  ADD KEY `fk_fc_employer` (`employer_id`);

--
-- Chỉ mục cho bảng `jobseeker_profiles`
--
ALTER TABLE `jobseeker_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_jsp_user` (`user_id`);

--
-- Chỉ mục cho bảng `job_posts`
--
ALTER TABLE `job_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_jp_employer` (`employer_id`),
  ADD KEY `idx_jp_status` (`status`),
  ADD KEY `idx_jp_created` (`created_at`),
  ADD KEY `idx_jp_end_date` (`end_date`);

--
-- Chỉ mục cho bảng `saved_jobs`
--
ALTER TABLE `saved_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_sj_seeker_job` (`jobseeker_id`,`job_id`),
  ADD KEY `fk_sj_job` (`job_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_email` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT cho bảng `employer_profiles`
--
ALTER TABLE `employer_profiles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `followed_companies`
--
ALTER TABLE `followed_companies`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `jobseeker_profiles`
--
ALTER TABLE `jobseeker_profiles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `job_posts`
--
ALTER TABLE `job_posts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT cho bảng `saved_jobs`
--
ALTER TABLE `saved_jobs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `fk_app_job` FOREIGN KEY (`job_id`) REFERENCES `job_posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_app_seeker` FOREIGN KEY (`jobseeker_id`) REFERENCES `jobseeker_profiles` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `employer_profiles`
--
ALTER TABLE `employer_profiles`
  ADD CONSTRAINT `fk_ep_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `followed_companies`
--
ALTER TABLE `followed_companies`
  ADD CONSTRAINT `fk_fc_employer` FOREIGN KEY (`employer_id`) REFERENCES `employer_profiles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_fc_seeker` FOREIGN KEY (`jobseeker_id`) REFERENCES `jobseeker_profiles` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `jobseeker_profiles`
--
ALTER TABLE `jobseeker_profiles`
  ADD CONSTRAINT `fk_jsp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `job_posts`
--
ALTER TABLE `job_posts`
  ADD CONSTRAINT `fk_jp_employer` FOREIGN KEY (`employer_id`) REFERENCES `employer_profiles` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `saved_jobs`
--
ALTER TABLE `saved_jobs`
  ADD CONSTRAINT `fk_sj_job` FOREIGN KEY (`job_id`) REFERENCES `job_posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sj_seeker` FOREIGN KEY (`jobseeker_id`) REFERENCES `jobseeker_profiles` (`id`) ON DELETE CASCADE;

DELIMITER $$
--
-- Sự kiện
--
DROP EVENT IF EXISTS `evt_deactivate_inactive_users`$$
CREATE DEFINER=`root`@`localhost` EVENT `evt_deactivate_inactive_users` ON SCHEDULE EVERY 1 DAY STARTS '2026-05-01 23:31:26' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    UPDATE `users`
    SET    `status` = 'inactive'
    WHERE  `last_login` < DATE_SUB(NOW(), INTERVAL 3 YEAR)
      AND  `status`     = 'active';
END$$

DROP EVENT IF EXISTS `evt_deactivate_stale_jobseekers`$$
CREATE DEFINER=`root`@`localhost` EVENT `evt_deactivate_stale_jobseekers` ON SCHEDULE EVERY 1 DAY STARTS '2026-05-01 23:31:26' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    UPDATE `users` u
    JOIN   `jobseeker_profiles` jsp ON jsp.user_id = u.id
    SET    u.`status` = 'inactive'
    WHERE  jsp.`last_update` < DATE_SUB(NOW(), INTERVAL 5 YEAR)
      AND  u.`status` = 'active';
END$$

DELIMITER ;
COMMIT;

-- PHẦN 2: TẠO TÀI KHOẢN (LOGIN)
-- ===============================================================
CREATE USER IF NOT EXISTS 'admin_user'@'localhost' IDENTIFIED BY 'Admin@123';
CREATE USER IF NOT EXISTS 'employer_user'@'localhost' IDENTIFIED BY 'Employer@123';
CREATE USER IF NOT EXISTS 'jobseeker_user'@'localhost' IDENTIFIED BY 'Seeker@123';

-- ===============================================================
-- PHẦN 3: PHÂN QUYỀN CHI TIẾT
-- ===============================================================

-- A. ADMIN: Toàn quyền
GRANT ALL PRIVILEGES ON `recruitment_db`.* TO 'admin_user'@'localhost';

-- B. EMPLOYER: Nhà tuyển dụng
-- Quyền xem View báo cáo
GRANT SELECT ON `recruitment_db`.`view_job_post_analytics` TO 'employer_user'@'localhost';
GRANT SELECT ON `recruitment_db`.`view_detailed_job_applications` TO 'employer_user'@'localhost';
-- Quyền thực thi Procedure/Function logic
GRANT EXECUTE ON PROCEDURE `recruitment_db`.`sp_close_job_and_reject_others` TO 'employer_user'@'localhost';
GRANT EXECUTE ON FUNCTION `recruitment_db`.`fn_count_open_jobs` TO 'employer_user'@'localhost';
-- Quyền đọc các bảng liên quan (để Procedure/View có thể lấy dữ liệu)
GRANT SELECT ON `recruitment_db`.`job_posts` TO 'employer_user'@'localhost';
GRANT SELECT ON `recruitment_db`.`applications` TO 'employer_user'@'localhost';
GRANT SELECT ON `recruitment_db`.`employer_profiles` TO 'employer_user'@'localhost';

-- C. JOBSEEKER: Người tìm việc
-- Quyền thực thi Procedure ứng tuyển và gợi ý
GRANT EXECUTE ON PROCEDURE `recruitment_db`.`sp_apply_job` TO 'jobseeker_user'@'localhost';
GRANT EXECUTE ON PROCEDURE `recruitment_db`.`sp_recommend_jobs_for_seeker` TO 'jobseeker_user'@'localhost';
-- Quyền thao tác dữ liệu cá nhân
GRANT SELECT, UPDATE ON `recruitment_db`.`jobseeker_profiles` TO 'jobseeker_user'@'localhost';
GRANT INSERT ON `recruitment_db`.`applications` TO 'jobseeker_user'@'localhost';
-- Quyền đọc bảng job_posts để xem thông tin việc làm và gợi ý
GRANT SELECT ON `recruitment_db`.`job_posts` TO 'jobseeker_user'@'localhost';
--  Cấp quyền SELECT trên bảng applications (để ứng viên xem lại đơn đã nộp) GRANT SELECT ON `recruitment_db`.`applications` TO 'jobseeker_user'@'localhost'; 
--  Cấp quyền SELECT trên các bảng danh mục (nếu có dùng để join trong Procedure) -- Giả sử bạn có bảng ngành nghề hoặc công ty GRANT SELECT ON `recruitment_db`.`employer_profiles` TO 'jobseeker_user'@'localhost'; 
-- ===============================================================
-- PHẦN 4: THU HỒI QUYỀN (REVOKE) 
-- ===============================================================
-- Thu hồi quyền xóa trực tiếp của Employer trên bảng job_posts
-- (Ép họ phải dùng Procedure sp_close_job_and_reject_others)
REVOKE DELETE ON `recruitment_db`.`job_posts` FROM 'employer_user'@'localhost';

FLUSH PRIVILEGES;


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
