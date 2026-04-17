-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 24, 2026 at 05:42 PM
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
-- Database: `hostel_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `email`, `created_at`) VALUES
(1, 'admin', '$2y$10$WffXOo0GWbpOcWRS9R4zu.hgUL5srmjNSLy7KqWCmPBHy.m3eA4W2', 'admin@hostel.com', '2026-01-05 16:08:54');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('general','personal') NOT NULL DEFAULT 'general',
  `target_student_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `message`, `type`, `target_student_id`, `created_at`, `created_by`, `is_read`) VALUES
(1, 'Reminders', 'Please pay your fee', 'personal', 3, '2026-01-11 19:39:24', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `status` enum('Pending','In Progress','Resolved') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`id`, `student_id`, `subject`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Ac not working', 'Ac not working fix it as soon as possible.', 'Resolved', '2026-01-07 15:49:43', '2026-01-11 20:18:00'),
(2, 4, 'test', 'aeasdas', 'Resolved', '2026-01-12 18:11:56', '2026-01-24 11:55:40'),
(3, 6, 'Fee', 'I have already paid but didn\'t get any confirmation till now.', 'Resolved', '2026-01-24 12:10:56', '2026-01-24 12:11:49');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `phone`, `message`, `created_at`) VALUES
(1, 'utk', 'utk@gmail.com', '7777555500', 'test msg', '2026-01-09 20:22:08');

-- --------------------------------------------------------

--
-- Table structure for table `mess_menu`
--

CREATE TABLE `mess_menu` (
  `id` int(11) NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `breakfast` varchar(255) NOT NULL,
  `lunch` varchar(255) NOT NULL,
  `snacks` varchar(255) DEFAULT NULL,
  `dinner` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mess_menu`
--

INSERT INTO `mess_menu` (`id`, `day_of_week`, `breakfast`, `lunch`, `snacks`, `dinner`, `created_at`, `updated_at`) VALUES
(1, 'Monday', 'Poha, Tea, Bread', 'Dal, rice, aloo sabzi, roti', 'Biscuits', 'Veg fried rice, raita', '2026-01-07 16:05:45', '2026-01-09 20:53:21'),
(2, 'Tuesday', 'Idli, Sambar, Chutney, Coffee', 'Chicken curry, rice, salad', 'Samosa', 'Paneer butter masala, naan', '2026-01-07 16:06:32', '2026-01-09 20:53:21'),
(3, 'Wednesday', 'Upma, Chutney, Tea', 'Rajma, rice, bhindi fry', 'Fruit chaat', 'Chapati, mixed veg curry, curd', '2026-01-07 16:07:31', '2026-01-09 20:53:21'),
(4, 'Thursday', 'Paratha, Curd, Pickle, Tea', 'Fish curry, rice, papad', 'Pakora', 'Khichdi, kadhi', '2026-01-07 16:08:22', '2026-01-09 20:53:21'),
(5, 'Friday', 'Dosa, Chutney, Sambar, Coffee', 'Egg curry, roti, jeera rice', '', 'Dal makhani, tandoori roti', '2026-01-07 16:09:07', '2026-01-09 20:53:21'),
(6, 'Saturday', 'Aloo Paratha, Curd, Tea', 'Veg biryani, boondi raita', 'Sandwich', 'Aloo matar, puri', '2026-01-07 16:09:55', '2026-01-09 20:53:21'),
(7, 'Sunday', 'Puri Bhaji, Jalebi, Tea', 'Mutton curry, rice, kachumber', 'Cookies', 'Pasta in red sauce, garlic bread', '2026-01-07 16:11:23', '2026-01-09 20:53:21');

-- --------------------------------------------------------

--
-- Table structure for table `monthly_rent_payments`
--

CREATE TABLE `monthly_rent_payments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `room_request_id` int(11) NOT NULL COMMENT 'Links to approved room request',
  `payment_month` date NOT NULL COMMENT 'First day of the month for which rent is paid',
  `room_rent` decimal(10,2) NOT NULL COMMENT 'Room rent amount',
  `mess_fee` decimal(10,2) NOT NULL COMMENT 'Mess fee amount',
  `other_charges` decimal(10,2) DEFAULT 0.00 COMMENT 'Any additional charges',
  `total_amount` decimal(10,2) NOT NULL COMMENT 'Total amount paid',
  `payment_method` enum('Card','UPI','Wallet','Cash') NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_status` enum('Pending','Success','Failed') DEFAULT 'Pending',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `due_date` date NOT NULL COMMENT 'Date by which rent should be paid',
  `is_advance_payment` tinyint(1) DEFAULT 0 COMMENT '1 if payment is made in advance',
  `payment_details` text DEFAULT NULL COMMENT 'JSON data with payment method details',
  `receipt_number` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `monthly_rent_payments`
--

INSERT INTO `monthly_rent_payments` (`id`, `student_id`, `room_request_id`, `payment_month`, `room_rent`, `mess_fee`, `other_charges`, `total_amount`, `payment_method`, `transaction_id`, `payment_status`, `payment_date`, `due_date`, `is_advance_payment`, `payment_details`, `receipt_number`, `notes`) VALUES
(1, 1, 2, '2026-01-01', 8000.00, 0.00, 0.00, 8000.00, 'UPI', 'RENT_696543E5A58251768244197', 'Success', '2026-01-12 18:56:37', '2026-01-05', 0, '{\"upi_id\":\"karan@upi\"}', 'RCPT_0001_1768244197', NULL),
(2, 3, 4, '2026-01-01', 5500.00, 0.00, 0.00, 5500.00, 'Wallet', 'RENT_696544AA6367B1768244394', 'Success', '2026-01-12 18:59:54', '2026-01-05', 0, '{\"wallet_type\":\"Paytm\",\"wallet_mobile\":\"6346334634\"}', 'RCPT_0003_1768244394', NULL),
(3, 2, 3, '2026-01-01', 6500.00, 0.00, 0.00, 6500.00, 'Card', 'RENT_696544FB2CBE31768244475', 'Success', '2026-01-12 19:01:15', '2026-01-05', 0, '{\"card_last4\":\"4242\",\"card_holder\":\"Utkarsh\",\"card_type\":\"Test Visa\"}', 'RCPT_0002_1768244475', NULL),
(4, 2, 3, '2026-02-01', 6500.00, 0.00, 0.00, 6500.00, 'Card', 'RENT_696544FB2CBE31768244475', 'Success', '2026-01-12 19:01:15', '2026-02-05', 1, '{\"card_last4\":\"4242\",\"card_holder\":\"Utkarsh\",\"card_type\":\"Test Visa\"}', 'RCPT_0002_1768244475', NULL),
(5, 2, 3, '2026-03-01', 6500.00, 0.00, 0.00, 6500.00, 'Card', 'RENT_696544FB2CBE31768244475', 'Success', '2026-01-12 19:01:15', '2026-03-05', 1, '{\"card_last4\":\"4242\",\"card_holder\":\"Utkarsh\",\"card_type\":\"Test Visa\"}', 'RCPT_0002_1768244475', NULL),
(6, 2, 3, '2026-04-01', 6500.00, 0.00, 0.00, 6500.00, 'Card', 'RENT_696544FB2CBE31768244475', 'Success', '2026-01-12 19:01:15', '2026-04-05', 1, '{\"card_last4\":\"4242\",\"card_holder\":\"Utkarsh\",\"card_type\":\"Test Visa\"}', 'RCPT_0002_1768244475', NULL),
(7, 6, 8, '2026-01-01', 6500.00, 0.00, 0.00, 6500.00, 'UPI', 'RENT_6974B7DB77D6E1769256923', 'Success', '2026-01-24 12:15:23', '2026-01-05', 0, '{\"upi_id\":\"demo731315@apl\"}', 'RCPT_0006_1769256923', NULL),
(8, 6, 8, '2026-02-01', 6500.00, 0.00, 0.00, 6500.00, 'UPI', 'RENT_6974B7DB77D6E1769256923', 'Success', '2026-01-24 12:15:23', '2026-02-05', 1, '{\"upi_id\":\"demo731315@apl\"}', 'RCPT_0006_1769256923', NULL),
(9, 6, 8, '2026-03-01', 6500.00, 0.00, 0.00, 6500.00, 'UPI', 'RENT_6974B7DB77D6E1769256923', 'Success', '2026-01-24 12:15:23', '2026-03-05', 1, '{\"upi_id\":\"demo731315@apl\"}', 'RCPT_0006_1769256923', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `transaction_id` varchar(50) NOT NULL,
  `room_request_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('UPI','Card','Wallet') NOT NULL,
  `payment_details` text DEFAULT NULL,
  `status` enum('Pending','Success','Failed') DEFAULT 'Pending',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `receipt_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `transaction_id`, `room_request_id`, `student_id`, `amount`, `payment_method`, `payment_details`, `status`, `payment_date`, `receipt_url`) VALUES
(1, 'TXN6963E9214B1121768155425', 2, 1, 8000.00, 'UPI', '{\"upi_id\":\"test@paytm\"}', 'Success', '2026-01-11 18:17:05', NULL),
(2, 'TXN6963F4E792F4E1768158439', 3, 2, 6500.00, 'Card', '{\"card_last4\":\"4242\",\"card_holder\":\"Utkarsh \",\"card_type\":\"Test Visa\"}', 'Success', '2026-01-11 19:07:19', NULL),
(3, 'TXN6963F985330A01768159621', 4, 3, 5500.00, 'Wallet', '{\"wallet_type\":\"Google Pay\",\"wallet_mobile\":\"8558555888\"}', 'Success', '2026-01-11 19:27:01', NULL),
(4, 'TXN6965399F38C881768241567', 5, 4, 8000.00, 'Card', '{\"card_last4\":\"4242\",\"card_holder\":\"abc\",\"card_type\":\"Test Visa\"}', 'Success', '2026-01-12 18:12:47', NULL),
(7, 'TXN6974B787BCE371769256839', 8, 6, 6500.00, 'Wallet', '{\"wallet_type\":\"Paytm\",\"wallet_mobile\":\"1234561234\"}', 'Success', '2026-01-24 12:13:59', NULL),
(8, 'TXN6974CA8838C451769261704', 9, 7, 6500.00, 'UPI', '{\"upi_id\":\"abc@apl\"}', 'Success', '2026-01-24 13:35:04', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `payment_notifications`
--

CREATE TABLE `payment_notifications` (
  `id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `notification_type` enum('Student','Admin') NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_notifications`
--

INSERT INTO `payment_notifications` (`id`, `payment_id`, `notification_type`, `message`, `is_read`, `created_at`) VALUES
(1, 1, 'Admin', 'New payment received from Karan for room booking. Amount: ₹8,000.00 | Transaction ID: TXN6963E9214B1121768155425', 0, '2026-01-11 18:17:05'),
(2, 1, 'Student', 'Payment successful! Transaction ID: TXN6963E9214B1121768155425. Your room booking payment has been received.', 0, '2026-01-11 18:17:05'),
(3, 2, 'Admin', 'New payment received from Utkarsh for room booking. Amount: ₹6,500.00 | Transaction ID: TXN6963F4E792F4E1768158439', 0, '2026-01-11 19:07:19'),
(4, 2, 'Student', 'Payment successful! Transaction ID: TXN6963F4E792F4E1768158439. Your room booking payment has been received.', 0, '2026-01-11 19:07:19'),
(5, 3, 'Admin', 'New payment received from shub for room booking. Amount: ₹5,500.00 | Transaction ID: TXN6963F985330A01768159621', 0, '2026-01-11 19:27:01'),
(6, 3, 'Student', 'Payment successful! Transaction ID: TXN6963F985330A01768159621. Your room booking payment has been received.', 0, '2026-01-11 19:27:01'),
(7, 4, 'Admin', 'New payment received from abc for room booking. Amount: ₹8,000.00 | Transaction ID: TXN6965399F38C881768241567', 0, '2026-01-12 18:12:47'),
(8, 4, 'Student', 'Payment successful! Transaction ID: TXN6965399F38C881768241567. Your room booking payment has been received.', 0, '2026-01-12 18:12:47'),
(13, 7, 'Admin', 'New payment received from Manish for room booking. Amount: ₹6,500.00', 0, '2026-01-24 12:13:59'),
(14, 7, 'Student', 'Payment successful! Transaction ID: TXN6974B787BCE371769256839', 0, '2026-01-24 12:13:59'),
(15, 8, 'Admin', 'New payment received from qwe for room booking. Amount: ₹6,500.00', 0, '2026-01-24 13:35:04'),
(16, 8, 'Student', 'Payment successful! Transaction ID: TXN6974CA8838C451769261704', 0, '2026-01-24 13:35:04');

-- --------------------------------------------------------

--
-- Stand-in structure for view `pending_rent_summary`
-- (See below for the actual view)
--
CREATE TABLE `pending_rent_summary` (
`student_id` int(11)
,`student_name` varchar(100)
,`email` varchar(100)
,`phone` varchar(15)
,`room_request_id` int(11)
,`room_type` enum('Single','Double','Triple')
,`monthly_rent` decimal(10,2)
,`room_allocation_date` date
,`last_paid_month` date
,`pending_payments` bigint(21)
);

-- --------------------------------------------------------

--
-- Table structure for table `rent_payment_settings`
--

CREATE TABLE `rent_payment_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rent_payment_settings`
--

INSERT INTO `rent_payment_settings` (`id`, `setting_key`, `setting_value`, `description`, `updated_at`) VALUES
(1, 'rent_due_day', '5', 'Day of month when rent is due (1-28)', '2026-01-12 18:44:50'),
(2, 'advance_months_allowed', '6', 'Maximum number of months students can pay in advance', '2026-01-12 18:44:50'),
(3, 'late_payment_fine', '100', 'Late payment fine amount in rupees', '2026-01-12 18:44:50'),
(4, 'grace_period_days', '3', 'Number of days grace period after due date', '2026-01-12 18:44:50'),
(5, 'enable_advance_payment', '1', 'Allow students to pay rent in advance (1=Yes, 0=No)', '2026-01-12 18:44:50');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `room_number` varchar(10) NOT NULL,
  `room_type` enum('Single','Double','Triple') NOT NULL,
  `capacity` int(11) NOT NULL,
  `occupied` int(11) DEFAULT 0,
  `rent` decimal(10,2) NOT NULL,
  `status` enum('Available','Full','Maintenance') DEFAULT 'Available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `room_number`, `room_type`, `capacity`, `occupied`, `rent`, `status`, `created_at`) VALUES
(1, '101', 'Single', 1, 1, 5000.00, 'Available', '2026-01-05 16:08:54'),
(2, '102', 'Single', 1, 1, 5000.00, 'Available', '2026-01-05 16:08:54'),
(3, '201', 'Double', 2, 2, 3500.00, 'Full', '2026-01-05 16:08:54'),
(4, '202', 'Double', 2, 0, 3500.00, 'Available', '2026-01-05 16:08:54'),
(5, '203', 'Double', 2, 1, 3500.00, 'Full', '2026-01-05 16:08:54'),
(6, '301', 'Triple', 3, 0, 2500.00, 'Available', '2026-01-05 16:08:54'),
(7, '302', 'Triple', 3, 0, 2500.00, 'Available', '2026-01-05 16:08:54'),
(8, '303', 'Triple', 3, 2, 2500.00, 'Full', '2026-01-05 16:08:54'),
(9, '211', 'Single', 1, 1, 5000.00, 'Full', '2026-01-24 10:16:57'),
(10, '103', 'Single', 1, 0, 5000.00, 'Available', '2026-01-24 12:21:06'),
(11, '104', 'Single', 1, 0, 5000.00, 'Available', '2026-01-24 12:21:24'),
(12, '105', 'Single', 1, 0, 5000.00, 'Available', '2026-01-24 12:21:34'),
(13, '106', 'Single', 1, 0, 5000.00, 'Available', '2026-01-24 12:21:46'),
(14, '305', 'Triple', 3, 0, 2500.00, 'Available', '2026-01-24 13:37:10');

-- --------------------------------------------------------

--
-- Table structure for table `room_requests`
--

CREATE TABLE `room_requests` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `room_type` enum('Single','Double','Triple') NOT NULL,
  `message` text DEFAULT NULL,
  `mess_included` tinyint(1) DEFAULT 1,
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `monthly_rent_amount` decimal(10,2) DEFAULT 0.00 COMMENT 'Monthly rent (room + mess)',
  `payment_status` enum('Pending','Paid','Failed') DEFAULT 'Pending',
  `payment_id` varchar(100) DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `room_id` int(11) DEFAULT NULL,
  `request_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `response_date` timestamp NULL DEFAULT NULL,
  `room_allocation_date` date DEFAULT NULL COMMENT 'Date when room was allocated'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room_requests`
--

INSERT INTO `room_requests` (`id`, `student_id`, `room_type`, `message`, `mess_included`, `total_amount`, `monthly_rent_amount`, `payment_status`, `payment_id`, `status`, `room_id`, `request_date`, `response_date`, `room_allocation_date`) VALUES
(1, 1, 'Single', 'test 2', 1, 7000.00, 0.00, 'Pending', NULL, 'Rejected', 8, '2026-01-07 14:41:22', '2026-01-11 17:32:31', NULL),
(2, 1, 'Single', 'test3', 1, 8000.00, 8000.00, 'Paid', 'TXN6963E9214B1121768155425', 'Approved', 1, '2026-01-11 18:15:08', '2026-01-11 18:16:14', NULL),
(3, 2, 'Double', 'New', 1, 6500.00, 6500.00, 'Paid', 'TXN6963F4E792F4E1768158439', 'Approved', 5, '2026-01-11 19:00:55', '2026-01-11 19:18:15', NULL),
(4, 3, 'Triple', 'test', 1, 5500.00, 5500.00, 'Paid', 'TXN6963F985330A01768159621', 'Approved', 8, '2025-12-10 19:25:13', '2026-01-11 19:37:42', NULL),
(5, 4, 'Single', 'new', 1, 8000.00, 8000.00, 'Paid', 'TXN6965399F38C881768241567', 'Approved', 2, '2026-01-12 18:12:19', '2026-01-12 18:13:58', NULL),
(8, 6, 'Double', 'slience', 1, 6500.00, 0.00, 'Paid', 'TXN6974B787BCE371769256839', 'Approved', 3, '2026-01-24 12:13:32', '2026-01-24 12:14:28', NULL),
(9, 7, 'Double', 'Free wifi', 1, 6500.00, 0.00, 'Paid', 'TXN6974CA8838C451769261704', 'Approved', 3, '2026-01-24 13:34:30', '2026-01-24 13:36:40', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` varchar(255) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'mess_fee_monthly', '3000', '2026-01-09 21:42:16'),
(2, 'razorpay_key_id', '', '2026-01-09 20:55:46'),
(3, 'razorpay_key_secret', '', '2026-01-09 20:55:46'),
(5, 'payment_enabled', '1', '2026-01-11 18:06:26'),
(6, 'test_mode', '1', '2026-01-11 18:06:26');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `address` text NOT NULL,
  `guardian_name` varchar(100) NOT NULL,
  `guardian_phone` varchar(15) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `photo` varchar(255) DEFAULT 'default_user.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `name`, `email`, `password`, `phone`, `gender`, `address`, `guardian_name`, `guardian_phone`, `created_at`, `photo`) VALUES
(1, 'Karan', 'karan@gmail.com', '$2y$10$aeO1hKjVEM0Qww1QWe2VNe6pPCXBkvydJPad3C7awKfPRj1pGgBnS', '7004444778', 'Male', 'Patna', 'Vikram Rai', '7766662200', '2026-01-07 12:59:26', 'default_user.png'),
(2, 'Utkarsh', 'utk@gmail.com', '$2y$10$TtPxbpQnW26aZWcNk6lp2OLjhNI5z3E3/kNGW4xMH17qJD5Hw7NcG', '7777888800', 'Male', 'Patna', 'xyz', '1111111111', '2026-01-11 18:54:40', 'default_user.png'),
(3, 'shub', 'shub@gmail.com', '$2y$10$wt7h07z0ZPGIEuDwhoU4neySLqIv6FPJHHiN6dm6U3zXDc7MnRfOq', '9234554321', 'Male', 'Patna', 'abc', '5555666677', '2026-01-11 19:13:41', 'default_user.png'),
(4, 'abc', 'abc@gmail.com', '$2y$10$wpmypE83oPMCLl8vbTSATeZsnJ9o3Mj1r.ufGiu7iXEYFflQSrgGi', '7878787878', 'Male', 'Patna', 'xyz', '445454545454', '2026-01-12 18:11:25', 'default_user.png'),
(6, 'Manish', 'manish@gmail.com', '$2y$10$az2fyaqjdZVQMTgoBD7raeE04Ikv.7jv9Qbmdw75TijPhsgfNXHta', '7050358946', 'Male', 'Sahnaura', 'Naresh yadav', '7050358946', '2026-01-24 11:47:55', 'default_user.png'),
(7, 'qwe', 'qwe@gmail.com', '$2y$10$xtGS9K8/7k12HCsP77asMuq0yoGB/.RAnXAMgaDNkqCy2eG2IyYBe', '1234567825', 'Male', 'patna', 'asd', '9876543210', '2026-01-24 13:33:49', 'default_user.png'),
(8, 'Manish Kumar', 'manish.bcastudent23.27692@cimage.in', '$2y$10$GnUSNXLhS/XjKUUV4prnOuXKUM4ZOZ01EIOSwK7Bmdo9LNs6MbnJS', '07050358946', 'Male', 'Sahnaura, Barh, Patna', 'Naresh yadav', '123456789', '2026-01-24 15:42:29', 'student_6974e8656c8499.48808135.jpg');

-- --------------------------------------------------------

--
-- Structure for view `pending_rent_summary`
--
DROP TABLE IF EXISTS `pending_rent_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `pending_rent_summary`  AS SELECT `rr`.`student_id` AS `student_id`, `s`.`name` AS `student_name`, `s`.`email` AS `email`, `s`.`phone` AS `phone`, `rr`.`id` AS `room_request_id`, `rr`.`room_type` AS `room_type`, `rr`.`total_amount` AS `monthly_rent`, `rr`.`room_allocation_date` AS `room_allocation_date`, (select coalesce(max(`monthly_rent_payments`.`payment_month`),`rr`.`room_allocation_date`) from `monthly_rent_payments` where `monthly_rent_payments`.`student_id` = `rr`.`student_id` and `monthly_rent_payments`.`payment_status` = 'Success') AS `last_paid_month`, (select count(0) from `monthly_rent_payments` where `monthly_rent_payments`.`student_id` = `rr`.`student_id` and `monthly_rent_payments`.`payment_status` = 'Pending') AS `pending_payments` FROM (`room_requests` `rr` join `students` `s` on(`rr`.`student_id` = `s`.`id`)) WHERE `rr`.`status` = 'Approved' AND `rr`.`payment_status` = 'Paid' AND `rr`.`room_allocation_date` is not null ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `target_student_id` (`target_student_id`),
  ADD KEY `type` (`type`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mess_menu`
--
ALTER TABLE `mess_menu`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `day_of_week` (`day_of_week`);

--
-- Indexes for table `monthly_rent_payments`
--
ALTER TABLE `monthly_rent_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_student_month` (`student_id`,`payment_month`),
  ADD KEY `idx_student` (`student_id`),
  ADD KEY `idx_payment_month` (`payment_month`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_due_date` (`due_date`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`),
  ADD KEY `room_request_id` (`room_request_id`),
  ADD KEY `idx_transaction` (`transaction_id`),
  ADD KEY `idx_student` (`student_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `payment_notifications`
--
ALTER TABLE `payment_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_id` (`payment_id`);

--
-- Indexes for table `rent_payment_settings`
--
ALTER TABLE `rent_payment_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `room_number` (`room_number`);

--
-- Indexes for table `room_requests`
--
ALTER TABLE `room_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `mess_menu`
--
ALTER TABLE `mess_menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `monthly_rent_payments`
--
ALTER TABLE `monthly_rent_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `payment_notifications`
--
ALTER TABLE `payment_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `rent_payment_settings`
--
ALTER TABLE `rent_payment_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `room_requests`
--
ALTER TABLE `room_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `complaints_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`room_request_id`) REFERENCES `room_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_notifications`
--
ALTER TABLE `payment_notifications`
  ADD CONSTRAINT `payment_notifications_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `room_requests`
--
ALTER TABLE `room_requests`
  ADD CONSTRAINT `room_requests_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `room_requests_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
