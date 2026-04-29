-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: mysql:3306
-- Tiempo de generación: 30-01-2026 a las 14:50:17
-- Versión del servidor: 8.0.44
-- Versión de PHP: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `distribuidora`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vnt_warehouses`
--

CREATE TABLE `vnt_warehouses` (
  `id` int NOT NULL,
  `companyId` int NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `postcode` varchar(10) DEFAULT NULL,
  `cityId` int DEFAULT NULL,
  `billingFormat` int NOT NULL DEFAULT '16',
  `is_credit` int NOT NULL DEFAULT '0',
  `termId` int NOT NULL DEFAULT '1' COMMENT 'forma de pago',
  `creditLimit` varchar(20) NOT NULL DEFAULT '0' COMMENT 'cupo de credito',
  `priceList` int NOT NULL DEFAULT '1' COMMENT 'lista de precio asignada',
  `status` tinyint DEFAULT '1',
  `district` varchar(100) NOT NULL,
  `api_data_id` int DEFAULT NULL,
  `main` tinyint DEFAULT '1',
  `branch_type` enum('FIJA','DESPACHO') DEFAULT 'FIJA' COMMENT 'DESPACHO = se crea en un pedido ',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `vnt_warehouses`
--

INSERT INTO `vnt_warehouses` (`id`, `companyId`, `name`, `address`, `postcode`, `cityId`, `billingFormat`, `is_credit`, `termId`, `creditLimit`, `priceList`, `status`, `district`, `api_data_id`, `main`, `branch_type`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Soacha', 'calle 25a45a-20', '124523', 19851, 16, 0, 1, '0', 1, 1, '', NULL, 1, 'FIJA', '2025-11-12 16:21:49', '2025-11-13 16:33:03', NULL),
(2, 1, 'otra sucursal', 'calle 5#6a-20', '1154', 20375, 16, 0, 1, '0', 1, 1, '', NULL, 0, 'DESPACHO', '2025-11-12 17:10:22', '2025-11-25 16:27:01', NULL),
(3, 2, 'guafalara', 'casdas55', '45643', 19712, 16, 0, 1, '0', 1, 1, 'Galerias', NULL, 1, 'FIJA', '2025-11-12 17:12:13', '2026-01-15 19:13:32', NULL),
(4, 3, 'Calle 36G No 11A', 'Calle 36G No 11A -77', '1100011', 20237, 16, 0, 1, '0', 1, 1, '', NULL, 1, 'FIJA', '2025-11-12 20:43:22', '2025-11-12 20:44:38', NULL),
(5, 3, 'CASA', 'KRA 12 A', '1100112', 19562, 16, 0, 1, '0', 1, 1, '', NULL, 0, 'FIJA', '2025-11-12 20:43:47', '2025-11-12 20:44:38', '2025-11-12 20:44:38'),
(6, 4, 'Miercoles', 'calle 20·20a-5', '11101', 20201, 16, 0, 1, '0', 1, 1, 'Galerias', NULL, 1, 'FIJA', '2025-11-12 21:31:33', '2026-01-15 19:15:09', NULL),
(7, 5, 'marsella', 'calle 24a·3b-2', '110254', 20283, 16, 0, 1, '0', 1, 1, '', NULL, 1, 'FIJA', '2025-11-12 21:39:13', '2025-11-12 21:39:13', NULL),
(8, 8, 'Fontibon', 'calle 4#4-5', '555', 20071, 16, 0, 1, '0', 1, 1, '', NULL, 0, 'FIJA', '2025-11-12 21:42:37', '2025-11-13 14:33:07', '2025-11-13 14:33:07'),
(9, 6, 'Lima', 'calle 130 #4a-20', '11204', 20071, 16, 0, 1, '0', 1, 1, 'Antonio Nariño', NULL, 1, 'FIJA', '2025-11-13 14:29:23', '2026-01-15 14:08:17', NULL),
(11, 9, 'Albania', 'calle 4#20a9', '45644', 19908, 16, 0, 1, '0', 1, 1, '', NULL, 1, 'FIJA', '2025-11-13 16:15:53', '2025-11-13 17:41:39', NULL),
(12, 10, 'Principal', 'calle 123#4a6', '110211', 20614, 16, 0, 1, '0', 1, 1, '', NULL, 1, 'FIJA', '2025-11-13 17:05:29', '2025-11-13 17:05:29', NULL),
(13, 10, 'Chico', 'calle 100 # 10a-1', '11011', 20078, 16, 0, 1, '0', 1, 1, '', NULL, 0, 'FIJA', '2025-11-13 20:01:56', '2025-11-13 20:04:46', NULL),
(14, 10, 'Japon', 'calle 123 # 7a-20', '12486', 20377, 16, 0, 1, '0', 1, 1, '', NULL, 0, 'DESPACHO', '2025-11-14 16:01:35', '2025-11-14 16:02:07', NULL),
(15, 9, 'Kennedy', 'Calle 42', '110111', 19711, 16, 0, 1, '0', 1, 1, '', NULL, 0, 'DESPACHO', '2025-11-18 20:51:25', '2025-11-24 14:20:36', '2025-11-24 14:20:36'),
(16, 7, 'Sucursal Sur', 'Carrera 10 # 25 -11', '110011', 19711, 16, 0, 1, '0', 1, 1, '', NULL, 0, 'FIJA', '2025-11-18 21:53:50', '2025-11-18 21:53:50', NULL),
(17, 11, 'Principal', 'Calle 52a #18-53', '101012', 20614, 16, 0, 1, '0', 1, 1, 'Galerias', NULL, 1, 'FIJA', '2025-11-19 16:07:48', '2026-01-15 19:18:08', NULL),
(18, 12, 'Principal', 'calle 123', '11011', 19714, 16, 0, 1, '0', 1, 1, '', NULL, 1, 'FIJA', '2025-11-21 20:09:22', '2025-11-21 20:09:22', NULL),
(19, 13, 'Principal', 'calle 24 #73b12', '110244', NULL, 16, 0, 1, '0', 1, 1, '', NULL, 1, 'FIJA', '2025-11-24 16:51:31', '2025-11-24 16:51:31', NULL),
(20, 15, 'Principal', 'calle 19#20a15', '', NULL, 16, 0, 1, '0', 1, 1, '', NULL, 1, 'FIJA', '2025-11-24 17:23:32', '2025-11-24 17:50:37', NULL),
(21, 19, 'Principal', 'callle 45 sur ', '110111', 19711, 16, 0, 1, '0', 1, 1, 'Chapinero', NULL, 1, 'FIJA', '2025-11-24 19:06:06', '2026-01-15 14:07:45', NULL),
(22, 20, 'Principal', 'calle 45 sur ', '110111', 19711, 16, 0, 1, '0', 1, 1, '', NULL, 1, 'FIJA', '2025-11-24 19:09:23', '2025-11-24 19:09:23', NULL),
(23, 21, 'Principal', 'calle 45 ', '', 19711, 16, 0, 1, '0', 1, 1, '', NULL, 1, 'FIJA', '2025-11-24 19:13:17', '2025-11-24 19:13:17', NULL),
(24, 22, 'Principal', 'calle 3 # 51a10', '', NULL, 16, 0, 1, '0', 1, 1, '', NULL, 1, 'FIJA', '2025-11-25 13:54:54', '2025-11-25 13:54:54', NULL),
(25, 23, 'Principal', 'Ex error molestias u', '001', 19556, 16, 0, 1, '0', 1, 1, '', NULL, 1, 'FIJA', '2025-11-25 16:13:05', '2025-12-01 21:17:52', NULL),
(26, 24, 'Principal', 'calle 45 sur ', '', 19711, 16, 0, 1, '0', 1, 1, '', NULL, 1, 'FIJA', '2025-12-05 16:03:40', '2025-12-05 16:03:40', NULL),
(27, 25, 'Principal', 'calle 45 sur ', '', 20252, 16, 0, 1, '0', 1, 1, '', NULL, 1, 'FIJA', '2025-12-05 17:47:23', '2025-12-05 17:47:23', NULL),
(28, 26, 'Principal', '315494894', '', 19554, 16, 0, 1, '0', 1, 1, '', NULL, 1, 'FIJA', '2025-12-05 17:50:09', '2025-12-05 17:50:09', NULL),
(29, 27, 'Principal', '30012318495', '', 19554, 16, 0, 1, '0', 1, 1, '', NULL, 1, 'FIJA', '2025-12-05 17:52:59', '2025-12-05 17:52:59', NULL),
(30, 28, 'Principal', 'calle 50# 5d-15', '4444', 19711, 16, 0, 1, '0', 1, 1, '', NULL, 1, 'FIJA', '2025-12-09 16:10:37', '2025-12-09 16:10:37', NULL),
(31, 29, 'Principal', 'Ad ex ad quis eum do', '11244', 19554, 16, 0, 1, '0', 1, 1, '', NULL, 1, 'FIJA', '2025-12-09 16:19:40', '2025-12-09 16:19:40', NULL),
(32, 30, 'Principal', 'calle 127 a20', '4475', 19711, 16, 0, 1, '0', 1, 1, '', NULL, 1, 'FIJA', '2025-12-09 16:23:16', '2025-12-09 16:23:16', NULL),
(33, 31, 'Principal', 'calle 45s ur ', '110111', 19554, 16, 0, 1, '0', 1, 1, '', NULL, 1, 'FIJA', '2025-12-09 16:27:32', '2025-12-09 16:27:32', NULL),
(34, 32, 'Principal', 'calle 80 # 68a10', '221145', 19711, 16, 0, 1, '0', 1, 1, '', NULL, 1, 'FIJA', '2025-12-09 16:34:14', '2025-12-09 16:34:14', NULL),
(35, 33, 'Principal', 'calle 24 #73b12', '1101', 19554, 16, 0, 1, '0', 1, 1, 'Galán', NULL, 1, 'FIJA', '2025-12-10 17:08:56', '2025-12-10 17:08:56', NULL),
(36, 34, 'Principal', 'calle 24a#3b-2', '11014', 19555, 16, 0, 1, '0', 1, 1, 'Uyala', NULL, 1, 'FIJA', '2025-12-10 17:19:28', '2025-12-10 17:19:28', NULL),
(37, 35, 'Principal', 'calle 123 # 4b-20', '11011', 20375, 16, 0, 1, '0', 1, 1, 'Uyala', NULL, 1, 'FIJA', '2025-12-10 17:26:03', '2025-12-10 17:26:03', NULL),
(38, 36, 'Principal', 'calle 123 # 4b-20', '11011', 20282, 16, 0, 1, '0', 1, 1, 'Veritatis', NULL, 1, 'FIJA', '2025-12-10 17:28:13', '2025-12-10 17:28:13', NULL),
(39, 37, 'Principal', 'calle 20# 4a-20', '1101', 20311, 16, 0, 1, '0', 1, 1, 'Barcelona', NULL, 1, 'FIJA', '2025-12-10 17:41:28', '2025-12-10 17:41:28', NULL),
(40, 38, 'Principal', 'calle 4b sur #7', '11011', 20375, 16, 0, 1, '0', 1, 1, 'Villa provi', NULL, 1, 'FIJA', '2025-12-10 17:45:12', '2025-12-10 17:45:12', NULL),
(41, 39, 'Principal', 'calle 123 # 20a11', '11011', 20010, 16, 0, 1, '0', 1, 1, 'Olivella', NULL, 1, 'FIJA', '2025-12-10 17:48:46', '2025-12-10 17:48:46', NULL),
(42, 40, 'Principal', 'calle 123 # 20a11', '110111', 19555, 16, 0, 1, '0', 1, 1, 'Nostrud', NULL, 1, 'FIJA', '2025-12-10 17:54:09', '2025-12-10 17:54:09', NULL),
(43, 41, 'Principal', 'calle 20# 4a-20', '11011', 20252, 16, 0, 1, '0', 1, 0, 'Los monjes', NULL, 1, 'FIJA', '2025-12-10 21:31:43', '2026-01-09 17:08:08', NULL),
(44, 42, 'Principal', 'calle 123#4a20', '11011', 20010, 16, 0, 1, '0', 1, 0, 'Los monjes', NULL, 1, 'FIJA', '2025-12-11 13:59:03', '2026-01-09 17:08:11', NULL),
(45, 43, 'Principal', 'calle 123#4a20', '11011', 19556, 16, 0, 1, '0', 1, 1, 'Los monjes', NULL, 1, 'FIJA', '2025-12-11 14:56:26', '2025-12-11 14:56:26', NULL),
(46, 44, 'Principal', 'calle 123#4a20', '444', 19985, 16, 0, 1, '0', 1, 1, 'Galán', NULL, 1, 'FIJA', '2025-12-11 15:03:33', '2025-12-11 15:03:33', NULL),
(47, 45, 'Principal', 'calle 123#4a20', '444', 20311, 16, 0, 1, '0', 1, 1, 'Galán', NULL, 1, 'FIJA', '2025-12-11 15:12:31', '2025-12-11 15:12:31', NULL),
(48, 46, 'Principal', 'calle', '', 19554, 16, 0, 1, '0', 1, 1, 'central', NULL, 1, 'FIJA', '2025-12-11 17:40:50', '2025-12-11 17:40:50', NULL),
(49, 47, 'Principal', 'callee', '', 19554, 16, 0, 1, '0', 1, 1, 'sd', NULL, 1, 'FIJA', '2025-12-11 17:50:08', '2025-12-11 17:50:08', NULL),
(50, 48, 'Principal', 'callee', '', 20010, 16, 0, 1, '0', 1, 1, '5', NULL, 1, 'FIJA', '2025-12-11 17:51:01', '2025-12-11 17:51:01', NULL),
(51, 49, 'Principal', '8', '8', 19554, 16, 0, 1, '0', 1, 1, '8', NULL, 1, 'FIJA', '2025-12-11 17:57:36', '2025-12-11 17:57:36', NULL),
(52, 50, 'Principal', 'callee', '8', 19554, 16, 0, 1, '0', 1, 1, 'galan', NULL, 1, 'FIJA', '2025-12-11 19:17:15', '2026-01-09 17:48:09', NULL),
(53, 51, 'Principal', 'calle 45 sur n 75 b 17', '8', 19554, 16, 0, 1, '0', 1, 1, 'glana', NULL, 1, 'FIJA', '2025-12-11 19:23:17', '2025-12-11 19:23:17', NULL),
(54, 52, 'Principal', 'calle 23 # 4a-20', '11014', 19554, 16, 0, 1, '0', 1, 1, 'Aliqua Quia volupta', NULL, 1, 'FIJA', '2025-12-15 20:31:48', '2025-12-15 20:31:48', NULL),
(55, 53, 'Principal', 'calle 23 # 4a-20', '11014', 19560, 16, 0, 1, '0', 1, 1, 'Alqueria', NULL, 1, 'FIJA', '2025-12-15 20:43:59', '2025-12-15 20:43:59', NULL),
(56, 54, 'Principal', 'callel 3a #75a15', '11011', 19985, 16, 0, 1, '0', 1, 1, 'San elenita', NULL, 1, 'FIJA', '2025-12-16 13:16:54', '2025-12-16 13:16:54', NULL),
(57, 55, 'Principal', 'calle 8s # 24a15', '11011', 19759, 16, 0, 1, '0', 1, 1, 'Santa Elenita', NULL, 1, 'FIJA', '2025-12-16 14:03:15', '2025-12-16 14:03:15', NULL),
(58, 56, 'Principal', 'calle 20# 4a-20', '11011', 19556, 16, 0, 1, '0', 1, 1, 'Galicia', NULL, 1, 'FIJA', '2025-12-16 14:52:00', '2025-12-16 14:52:00', NULL),
(59, 1, 'ConDistrict', 'calle 11#4b3', '11011', 19554, 16, 0, 1, '0', 1, 1, '', NULL, 0, 'DESPACHO', '2025-12-16 15:19:30', '2025-12-16 15:19:30', NULL),
(60, 57, 'Principal', 'calle 1#4b20', '11011', 19711, 16, 0, 1, '0', 1, 1, 'Chico', NULL, 1, 'FIJA', '2025-12-16 16:09:20', '2025-12-16 16:09:20', NULL),
(61, 58, 'Principal', 'calle 451sur', '', 19554, 16, 0, 1, '0', 1, 1, 'galan', NULL, 1, 'FIJA', '2025-12-16 21:00:40', '2025-12-16 21:00:40', NULL),
(62, 60, 'Principal', 'calle 123', '110111', 20282, 16, 0, 1, '0', 1, 1, 'El refujio', NULL, 1, 'FIJA', '2025-12-19 15:11:48', '2025-12-19 15:11:48', NULL),
(63, 61, 'Principal', 'calle 4 bis # 41b-65', '110111', 19711, 16, 0, 1, '0', 1, 1, 'galan', NULL, 1, 'FIJA', '2025-12-19 16:07:34', '2025-12-19 16:07:34', NULL),
(64, 62, 'Principal', 'calle 123 # 15a20', '110111', 19711, 16, 0, 1, '0', 1, 1, 'Itaque est quia rep', NULL, 1, 'FIJA', '2025-12-22 12:22:46', '2025-12-22 12:22:46', NULL),
(65, 62, 'otra', 'calle 2 # 68b50', '110111', 19711, 16, 0, 1, '0', 1, 1, '', NULL, 0, 'DESPACHO', '2025-12-22 13:07:17', '2025-12-22 13:07:17', NULL),
(66, 63, 'Principal', 'calle 120 # 15b20', '110111', 19555, 16, 0, 1, '0', 1, 1, 'Autem ea itaque dist', NULL, 1, 'FIJA', '2025-12-22 17:36:38', '2025-12-22 17:36:38', NULL),
(67, 64, 'Principal', 'calle 5b#4c54', '770121', 19943, 16, 0, 1, '0', 1, 1, 'Jamundi', NULL, 1, 'FIJA', '2025-12-22 17:45:32', '2025-12-22 17:45:32', NULL),
(68, 65, 'Principal', 'carrera 70 # 3a20', '110111', 19882, 16, 0, 1, '0', 1, 1, 'mendoza', NULL, 1, 'FIJA', '2025-12-22 17:52:44', '2025-12-22 17:52:44', NULL),
(69, 66, 'Principal', 'carrera 70 # 3a20', '110111', 20614, 16, 0, 1, '0', 1, 1, 'mendoza', NULL, 1, 'FIJA', '2025-12-22 17:53:57', '2025-12-22 17:53:57', NULL),
(70, 67, 'Principal', 'carrera 70 # 3a20', '110111', 20252, 16, 0, 1, '0', 1, 1, 'mendoza', NULL, 1, 'FIJA', '2025-12-22 17:59:22', '2025-12-22 17:59:22', NULL),
(71, 68, 'Principal', 'calle 22 # 10a2', '110111', 20253, 16, 0, 1, '0', 1, 1, 'Vergel', NULL, 1, 'FIJA', '2025-12-22 18:01:41', '2025-12-22 18:01:41', NULL),
(72, 69, 'Principal', 'calle 20#4f2', '110111', 19561, 16, 0, 1, '0', 1, 1, 'Independencia', NULL, 1, 'FIJA', '2025-12-22 18:04:25', '2025-12-22 18:04:25', NULL),
(73, 70, 'Principal', 'calle 22#4a20', '110111', 20570, 16, 0, 1, '0', 1, 1, 'Gran estacion', NULL, 1, 'FIJA', '2025-12-22 18:07:18', '2025-12-22 18:07:18', NULL),
(74, 71, 'Principal', 'calle 45 sur ', '', 20282, 16, 0, 1, '0', 1, 1, '', NULL, 1, 'FIJA', '2025-12-23 12:28:53', '2025-12-23 12:28:53', NULL),
(75, 72, 'Principal', 'calle 124#4b20', '110111', 20200, 16, 0, 1, '0', 1, 1, 'Vergel', NULL, 1, 'FIJA', '2025-12-23 12:59:02', '2025-12-23 12:59:02', NULL),
(76, 73, 'Principal', 'calle 122 #4b20', '110111', 20202, 16, 0, 1, '0', 1, 1, 'Sopo', NULL, 1, 'FIJA', '2025-12-23 13:01:45', '2025-12-23 13:01:45', NULL),
(77, 74, 'Principal', 'cale 45 sur ', '', 19554, 16, 0, 1, '0', 1, 1, 'Galan', NULL, 1, 'FIJA', '2025-12-23 13:33:32', '2025-12-23 13:33:32', NULL),
(78, 75, 'Principal', 'calle 45 sur ', '', 19554, 16, 0, 1, '0', 1, 1, 'glan', NULL, 1, 'FIJA', '2025-12-23 14:38:31', '2025-12-23 14:38:31', NULL),
(79, 76, 'Principal', 'calle 45 sur ', '', 19554, 16, 0, 1, '0', 1, 1, 'glan', NULL, 1, 'FIJA', '2025-12-23 16:13:31', '2025-12-23 16:13:31', NULL),
(80, 77, 'Principal', 'calle 45 sur sdfsdf', '', 19554, 16, 0, 1, '0', 1, 1, 'galan', NULL, 1, 'FIJA', '2025-12-23 16:17:42', '2025-12-23 16:17:42', NULL),
(81, 78, 'Principal', 'calle 45 sur n 78 b 16 ', '', 19554, 16, 0, 1, '0', 1, 1, 'Galan', NULL, 1, 'FIJA', '2025-12-23 16:24:40', '2025-12-23 16:24:40', NULL),
(82, 79, 'Principal', 'calle 45 sur n 78 b 13', '', 20375, 16, 0, 1, '0', 1, 1, 'glan', NULL, 1, 'FIJA', '2025-12-23 16:33:37', '2025-12-23 16:33:37', NULL),
(83, 80, 'Principal', 'CALLE 45 SUR N 78 NB 16', '11011', 19554, 16, 0, 1, '0', 1, 1, 'GALAN', NULL, 1, 'FIJA', '2025-12-23 17:32:02', '2025-12-23 17:32:02', NULL),
(84, 81, 'Principal', 'calle 123#4d5', '110111', 19711, 16, 0, 1, '0', 1, 1, 'Galan', NULL, 1, 'FIJA', '2025-12-26 21:35:49', '2025-12-26 21:35:49', NULL),
(85, 82, 'Principal', 'calle 123#4k-5', '110111', 19554, 16, 0, 1, '0', 1, 1, 'Normandia', NULL, 1, 'FIJA', '2025-12-26 21:49:28', '2026-01-05 16:20:05', NULL),
(86, 83, 'Principal', 'calle 2#20-2', '110111', 19711, 16, 0, 1, '0', 1, 1, 'Los girasoles', NULL, 1, 'FIJA', '2026-01-06 14:47:13', '2026-01-06 14:47:13', NULL),
(87, 84, 'Principal', 'carrera 70 # 3a20', '110111', 19711, 16, 0, 1, '0', 1, 1, 'Venecia', NULL, 1, 'FIJA', '2026-01-06 14:57:10', '2026-01-06 14:57:10', NULL),
(88, 85, 'Principal', 'calle 4bsur 4este ', '110111', 19711, 16, 0, 1, '0', 1, 1, 'Las cruces', NULL, 1, 'FIJA', '2026-01-06 15:09:28', '2026-01-06 15:09:28', NULL),
(89, 86, 'Principal', 'carrera4#20sur', '110111', 19711, 16, 0, 1, '0', 1, 1, 'Tempor minus quo fug', NULL, 1, 'FIJA', '2026-01-06 15:12:48', '2026-01-06 15:12:48', NULL),
(90, 87, 'Principal', 'calle 21#4este ', '110111', 19711, 16, 0, 1, '0', 1, 1, 'El eden', NULL, 1, 'FIJA', '2026-01-06 16:14:47', '2026-01-06 16:14:47', NULL),
(91, 88, 'Principal', 'calle 75 #80b5', '110111', 19711, 16, 0, 1, '0', 1, 1, 'Bochica', NULL, 1, 'FIJA', '2026-01-06 16:38:14', '2026-01-09 17:07:55', NULL),
(92, 89, 'Principal', 'calle 120# 3a8', '110111', 19711, 16, 0, 1, '0', 1, 1, 'Primavera', NULL, 1, 'FIJA', '2026-01-08 13:34:48', '2026-01-08 13:34:48', NULL),
(93, 90, 'Principal', 'calle 45 sur n 78 b 13', '', 19711, 16, 0, 1, '0', 1, 1, 'glan', NULL, 1, 'FIJA', '2026-01-08 21:19:33', '2026-01-08 21:19:33', NULL),
(94, 91, 'Principal', 'calle 45 sur n 78 b 13', '11011', 19711, 16, 0, 1, '0', 1, 1, 'Quiroga', NULL, 1, 'FIJA', '2026-01-09 14:16:55', '2026-01-09 14:16:55', NULL),
(95, 92, 'Principal', 'calle 45 sur n 78 b 13', '11011', 19711, 16, 0, 1, '0', 1, 1, 'Restrepo', NULL, 1, 'FIJA', '2026-01-09 16:27:03', '2026-01-09 16:27:03', NULL),
(96, 93, 'Principal', 'calle 45 sur n 78 b 13', '11011', 19711, 16, 0, 1, '0', 1, 1, 'Restrepo', NULL, 1, 'FIJA', '2026-01-09 16:28:08', '2026-01-09 16:28:08', NULL),
(97, 94, 'Principal', 'calle 45 sur n 78 b 13', '11011', 19555, 16, 0, 1, '0', 1, 1, 'Restrepo', NULL, 1, 'FIJA', '2026-01-09 16:43:43', '2026-01-09 16:43:43', NULL),
(98, 95, 'Principal', 'calle 45 sur n 78 b 13', '11011', 19711, 16, 0, 1, '0', 1, 1, 'Quiroga', NULL, 1, 'FIJA', '2026-01-09 16:52:43', '2026-01-09 16:52:43', NULL),
(99, 96, 'Principal', 'calle 45 sur n 78 b 13', '11011', 19554, 16, 0, 1, '0', 1, 1, 'Restrepo', NULL, 1, 'FIJA', '2026-01-09 19:08:13', '2026-01-09 19:08:13', NULL),
(100, 97, 'Principal', 'calle 45 sur n 78 b 13', '11011', 20622, 16, 0, 1, '0', 1, 1, 'Engativa', NULL, 1, 'FIJA', '2026-01-09 19:12:17', '2026-01-09 19:12:17', NULL),
(101, 98, 'Principal', 'callle 45 sur ', '11011', 19711, 16, 0, 1, '0', 1, 1, '000', NULL, 1, 'FIJA', '2026-01-16 17:21:17', '2026-01-16 17:21:17', NULL),
(102, 99, 'Principal', 'casdas55', '1110011', 19711, 16, 0, 1, '0', 1, 1, '000', NULL, 1, 'FIJA', '2026-01-16 18:57:27', '2026-01-16 18:57:27', NULL),
(103, 100, 'Principal', 'calle 45 sur ', '110111', 19554, 16, 0, 1, '0', 1, 1, 'pedro', NULL, 1, 'FIJA', '2026-01-29 14:05:19', '2026-01-29 14:05:19', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `vnt_warehouses`
--
ALTER TABLE `vnt_warehouses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `companyId` (`companyId`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `vnt_warehouses`
--
ALTER TABLE `vnt_warehouses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
