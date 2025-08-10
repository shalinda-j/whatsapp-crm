-- WhatsApp CRM - Complete Database Setup
-- This file consolidates all SQL scripts for the WhatsApp CRM system
-- Run this on MySQL/MariaDB to create the complete database structure

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 01/06/2025 às 22:42
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Use existing database (remove CREATE DATABASE to avoid permission issues)
-- Make sure to select the correct database in phpMyAdmin before running this script
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `contact_number` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('user','admin','reseller','super_admin') NOT NULL DEFAULT 'user',
  `deleted` enum('yes','no') DEFAULT 'no',
  `status` enum('true','false') NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `expired_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `admin`
--

INSERT INTO `admin` (`id`, `username`, `name`, `contact_number`, `password`, `user_type`, `deleted`, `status`, `admin_id`, `start_date`, `expired_date`) VALUES
(1, 'admin', 'DROPE', '5582994229991', '67263a3e94380edb5f4aa246c28e25d690690e53', 'super_admin', 'no', 'true', 1, '2024-11-13', '2029-11-30');

-- --------------------------------------------------------

--
-- Estrutura para tabela `configurations`
--

CREATE TABLE `configurations` (
  `id` int(3) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `support_phone_number` varchar(15) NOT NULL,
  `trial_key_validity` tinyint(2) NOT NULL,
  `color_background` varchar(10) NOT NULL,
  `color_text` varchar(10) NOT NULL,
  `license_key` varchar(100) DEFAULT NULL,
  `license_response` text DEFAULT NULL,
  `license_last_check` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `configurations`
--

INSERT INTO `configurations` (`id`, `email`, `support_phone_number`, `trial_key_validity`, `color_background`, `color_text`, `license_key`, `license_response`, `license_last_check`) VALUES
(1, NULL, '558294229991', 3, '#000000', '#ffffff', NULL, NULL, '2025-06-01 16:38:41');

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `whatsapp_number` varchar(255) NOT NULL,
  `license_key` varchar(255) DEFAULT NULL,
  `act_date` varchar(255) NOT NULL,
  `end_date` varchar(255) NOT NULL,
  `deleted_key` enum('yes','no') NOT NULL DEFAULT 'no',
  `life_time` varchar(255) NOT NULL,
  `plan_type` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `pc_id` VARCHAR(255) DEFAULT NULL COMMENT 'Device/PC identifier',
  `skd_id` INT(11) DEFAULT NULL COMMENT 'SKD identifier for device tracking',
  `status` enum('true','false') NOT NULL DEFAULT 'true',
  `plan` enum('true','false') NOT NULL DEFAULT 'true',
  `user_id` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `configurations`
--
ALTER TABLE `configurations`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_license_key` (`license_key`),
  ADD UNIQUE KEY `uq_users_whatsapp_number` (`whatsapp_number`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT de tabela `configurations`
--
ALTER TABLE `configurations`
  MODIFY `id` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

-- --------------------------------------------------------

--
-- Additional Admin Users
--

-- Delete any existing superadmin user (if exists)
DELETE FROM admin WHERE username='superadmin';

-- Create new super_admin user
INSERT INTO admin (username, name, contact_number, password, user_type, deleted, status, admin_id, start_date, expired_date)
VALUES ('superadmin', 'Super Administrator', '5582999999999', SHA1('Super@2024'), 'super_admin', 'no', 'true', 1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 5 YEAR));

-- Delete any existing admin2 user (if exists)
DELETE FROM admin WHERE username='admin2';

-- Create admin2 user
INSERT INTO admin (username, name, contact_number, password, user_type, deleted, status, admin_id, start_date, expired_date)
VALUES ('admin2', 'Admin 2', '5582999999999', SHA1('Admin@123'), 'admin', 'no', 'true', 1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR));

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- Verify the setup
SELECT 'Database setup complete!' as status;
SELECT id, username, name, user_type, status FROM admin ORDER BY id;
DESCRIBE users;
