-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: mysql:3306
-- Tiempo de generación: 13-04-2026 a las 20:11:18
-- Versión del servidor: 8.0.45
-- Versión de PHP: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `company_8_8fb35c7f_b3b6_4e6b_b240_a4acefb1ab9a`
--

DELIMITER $$
--
-- Funciones
--
CREATE DEFINER=`root`@`%` FUNCTION `label_to_date` (`label` VARCHAR(10)) RETURNS DATE DETERMINISTIC BEGIN
    DECLARE month_str VARCHAR(3);
    DECLARE year_str VARCHAR(2);
    DECLARE month_num INT;

    SET month_str = UPPER(LEFT(label, 3));
    SET year_str = RIGHT(label, 2);

    SET month_num = CASE month_str
        WHEN 'ENE' THEN 1
        WHEN 'FEB' THEN 2
        WHEN 'MAR' THEN 3
        WHEN 'ABR' THEN 4
        WHEN 'MAY' THEN 5
        WHEN 'JUN' THEN 6
        WHEN 'JUL' THEN 7
        WHEN 'AGO' THEN 8
        WHEN 'SEP' THEN 9
        WHEN 'OCT' THEN 10
        WHEN 'NOV' THEN 11
        WHEN 'DIC' THEN 12
        ELSE NULL
    END;

    RETURN STR_TO_DATE(CONCAT('20', year_str, '-', month_num, '-01'), '%Y-%m-%d');
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cmp_campaigns`
--

CREATE TABLE `cmp_campaigns` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('activo','pausado','anulado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activo',
  `gift_quantity` int NOT NULL DEFAULT '0',
  `gifts_sent` int NOT NULL DEFAULT '0',
  `max_per_order` int DEFAULT '1',
  `assignment_type` enum('todos','asesor','manual','todas_op','antiguos_frecuentes') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'todos',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cmp_campaigns`
--

INSERT INTO `cmp_campaigns` (`id`, `name`, `description`, `start_date`, `end_date`, `status`, `gift_quantity`, `gifts_sent`, `max_per_order`, `assignment_type`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'pruebas de campaña', 'campalña depruebas para regloss de clientes antiguos ', '2026-03-04', '2026-03-08', 'activo', 100, 0, 1, 'antiguos_frecuentes', '2026-03-04 14:41:26', '2026-03-04 14:41:26', NULL),
(2, 'prueba todos', 'pruebas para todos', '2026-03-04', '2026-03-17', 'activo', 200, 1, 1, 'todos', '2026-03-04 15:14:56', '2026-03-04 15:18:50', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cmp_campaign_customers`
--

CREATE TABLE `cmp_campaign_customers` (
  `id` bigint UNSIGNED NOT NULL,
  `campaign_id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cmp_campaign_customers`
--

INSERT INTO `cmp_campaign_customers` (`id`, `campaign_id`, `customer_id`, `delivered_at`, `created_at`, `updated_at`) VALUES
(1, 2, 64, '2026-03-04 15:18:50', '2026-03-04 15:18:50', '2026-03-04 15:18:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cnf_audit_status_log`
--

CREATE TABLE `cnf_audit_status_log` (
  `id` int NOT NULL,
  `warehouseId` int NOT NULL DEFAULT '1',
  `docId` int NOT NULL DEFAULT '0',
  `event` text NOT NULL,
  `campo1` text,
  `campo2` text,
  `campo3` text,
  `fecha_cambio` datetime DEFAULT (now()),
  `user` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cnf_buttons`
--

CREATE TABLE `cnf_buttons` (
  `id` int NOT NULL,
  `tittle` varchar(155) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'button',
  `status` tinyint DEFAULT '1',
  `color` varchar(155) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `module` int NOT NULL,
  `link` varchar(155) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cnf_company_options`
--

CREATE TABLE `cnf_company_options` (
  `id` int NOT NULL,
  `company_id` int DEFAULT NULL COMMENT 'ID de empresa (sin FK)',
  `option_id` int DEFAULT NULL COMMENT 'ID de opción (sin FK)',
  `value` int NOT NULL DEFAULT '0' COMMENT '1=habilitado, 0=deshabilitado',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Configuraciones de empresa por tenant - Sin FK';

--
-- Volcado de datos para la tabla `cnf_company_options`
--

INSERT INTO `cnf_company_options` (`id`, `company_id`, `option_id`, `value`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 8, 1, 10, '2025-11-12 17:53:20', '2025-11-12 17:53:20', NULL),
(2, 8, 2, 1, '2025-11-12 17:53:20', '2025-11-12 17:53:20', NULL),
(3, 8, 3, 1, '2025-11-12 17:53:20', '2026-03-26 14:48:42', NULL),
(4, 8, 5, 1, '2025-11-12 17:53:20', '2026-03-25 21:09:29', NULL),
(5, 8, 6, 1, '2025-11-12 17:53:20', '2025-11-12 17:53:20', NULL),
(6, 8, 7, 1, '2025-11-12 17:53:20', '2025-11-12 17:53:20', NULL),
(7, 8, 8, 1, '2025-11-12 17:53:20', '2025-11-12 17:53:20', NULL),
(8, 8, 10, 1, '2025-11-12 17:53:20', '2025-11-12 17:53:20', NULL),
(9, 8, 12, 1, '2025-11-12 17:53:20', '2025-11-12 17:53:20', NULL),
(10, 8, 13, 1, '2025-11-12 17:53:20', '2025-11-12 17:53:20', NULL),
(11, 8, 15, 1, '2025-11-12 17:53:20', '2025-11-12 17:53:20', NULL),
(12, 8, 16, 1, '2025-11-12 17:53:20', '2025-11-12 17:53:20', NULL),
(13, 8, 17, 0, '2025-11-12 17:53:20', '2025-11-12 17:53:20', NULL),
(14, 8, 18, 1, '2025-11-12 17:53:20', '2025-11-12 17:53:20', NULL),
(15, 8, 24, 1, '2025-11-12 17:53:20', '2025-11-12 17:53:20', NULL),
(16, 8, 25, 1, '2025-11-12 17:53:20', '2025-11-12 17:53:20', NULL),
(17, 8, 26, 5, '2025-11-12 17:53:20', '2025-11-12 17:53:20', NULL),
(18, 8, 27, 3, '2025-11-12 17:53:20', '2025-11-12 17:53:20', NULL),
(19, 8, 28, 1, '2025-11-12 17:53:20', '2025-11-12 17:53:20', NULL),
(20, 8, 29, 10, '2025-11-12 17:53:20', '2025-11-12 17:53:20', NULL),
(21, 8, 33, 1, '2025-11-12 17:53:20', '2025-11-12 17:53:20', NULL),
(22, 8, 35, 1, '2025-11-12 17:53:20', '2025-11-12 17:53:20', NULL),
(23, 8, 36, 1, '2025-11-12 17:53:20', '2025-11-12 17:53:20', NULL),
(24, 8, 45, 1, '2025-11-12 17:53:20', '2025-11-12 17:53:20', NULL),
(25, 8, 51, 1, '2025-11-12 17:53:20', '2025-11-12 17:53:20', NULL),
(26, 8, 54, 1, '2025-11-12 17:53:20', '2025-11-12 17:53:20', NULL),
(27, 8, 55, 1, '2025-11-12 17:53:20', '2025-11-12 17:53:20', NULL),
(28, 8, 57, 1, '2025-11-12 17:53:20', '2025-11-12 17:53:20', NULL),
(29, 8, 59, 1, '2025-11-12 17:53:20', '2025-11-12 17:53:20', NULL),
(32, 8, 4, 1, '2025-11-25 20:49:34', '2026-03-25 19:06:15', NULL),
(33, 8, 71, 0, '2025-11-28 14:14:45', '2025-11-28 14:14:45', NULL),
(106, 8, 75, 1, '2026-02-16 19:49:31', '2026-02-16 19:49:31', NULL),
(107, 8, 48, 1, '2026-02-18 21:04:01', '2026-02-18 21:04:01', NULL),
(108, 8, 32, 0, '2026-03-17 15:40:58', NULL, NULL),
(109, 8, 30, 1, '2026-03-25 19:31:39', '2026-03-26 14:18:27', NULL),
(110, 8, 31, 1, '2026-03-25 19:35:26', '2026-03-26 14:20:49', NULL),
(111, 8, 81, 1, '2026-03-25 19:35:26', '2026-03-26 14:20:49', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cnf_invoices`
--

CREATE TABLE `cnf_invoices` (
  `id` int NOT NULL,
  `token` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `id_warehouses` int NOT NULL,
  `numeracion` int NOT NULL,
  `facturador` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `cnf_invoices`
--

INSERT INTO `cnf_invoices` (`id`, `token`, `id_warehouses`, `numeracion`, `facturador`) VALUES
(1, 'dGljc2lhK2FsZWdyYUBhbGVncmEuY29tOmY3ODQyMTViNTgzYjk5NzU1MzBk', 8, 9999, 'http://fac.dosil.com.co/api'),
(2, 'dGljc2lhK2FsZWdyYUBhbGVncmEuY29tOmY3ODQyMTViNTgzYjk5NzU1MzBk', 96, 9999, 'http://fac.dosil.com.co/api'),
(3, 'cW1hcnRpbnorZGVtb0BleGFtcGxlLmNvbTo5YTI3NDY1YmQ4NzY0MzIxMGY=', 118, 20, 'http://fac.dosil.com.co/api');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cnf_positions`
--

CREATE TABLE `cnf_positions` (
  `id` int NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cnf_positions`
--

INSERT INTO `cnf_positions` (`id`, `name`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Gerente', 1, '2025-11-04 20:29:35', NULL, NULL),
(2, 'Administrador', 1, '2025-11-04 20:29:35', NULL, NULL),
(3, 'Ventas', 1, '2025-11-04 20:30:10', NULL, NULL),
(4, 'Mercadeo', 1, '2025-11-04 20:30:10', NULL, NULL),
(5, 'Almacen', 1, '2025-11-04 20:32:37', NULL, NULL),
(6, 'Despachos', 1, '2025-11-04 20:32:37', NULL, NULL),
(7, 'Compras', 1, '2025-11-04 20:32:55', NULL, NULL),
(8, 'Cartera', 1, '2025-11-04 20:32:55', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cnf_pricelist`
--

CREATE TABLE `cnf_pricelist` (
  `id` int NOT NULL,
  `title` varchar(10) NOT NULL,
  `value` float NOT NULL,
  `createAd` datetime NOT NULL,
  `updateAd` datetime DEFAULT NULL,
  `status` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `cnf_pricelist`
--

INSERT INTO `cnf_pricelist` (`id`, `title`, `value`, `createAd`, `updateAd`, `status`) VALUES
(1, 'Lista', 1, '2025-11-25 20:20:48', '2025-11-26 14:43:05', 1),
(2, '3%', 0.97, '2025-11-25 20:22:05', '2025-11-26 14:46:55', 1),
(3, '5%', 0.95, '2025-11-26 14:47:53', '2025-11-26 15:19:46', 1),
(4, '7%', 0.93, '2025-12-02 13:14:19', NULL, 1),
(5, 'P5', 0.8, '2025-12-04 20:34:04', NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cnf_priceprofile`
--

CREATE TABLE `cnf_priceprofile` (
  `id` int NOT NULL,
  `price` int NOT NULL,
  `profile` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cnf_taxes`
--

CREATE TABLE `cnf_taxes` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `percentage` double NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `api_data_id` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `inventoryAccount` int NOT NULL,
  `inventariablePurchaseAccount` int NOT NULL,
  `categoryAccount` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `cnf_taxes`
--

INSERT INTO `cnf_taxes` (`id`, `name`, `percentage`, `status`, `api_data_id`, `created_at`, `updated_at`, `deleted_at`, `inventoryAccount`, `inventariablePurchaseAccount`, `categoryAccount`) VALUES
(2, 'Iva 5%', 5, 1, NULL, '2024-05-25 07:54:30', '2026-02-06 20:20:37', NULL, 5023, 5098, 5063),
(3, 'Iva 19%', 19, 1, 3, '2024-05-25 07:54:33', '2026-02-06 20:20:37', NULL, 5023, 5098, 5063),
(6, 'Exento', 0, 1, NULL, '2024-05-25 07:54:37', '2026-02-06 20:20:38', NULL, 5023, 5098, 5063);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cnf_templates`
--

CREATE TABLE `cnf_templates` (
  `id` int NOT NULL,
  `quote` varchar(1) DEFAULT 'N',
  `remission` varchar(1) DEFAULT 'N',
  `text` text NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `imp_comments`
--

CREATE TABLE `imp_comments` (
  `id` int NOT NULL,
  `import_id` int DEFAULT NULL,
  `comment` mediumtext,
  `user_id` int DEFAULT NULL,
  `initiator` tinyint(1) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `imp_comments`
--

INSERT INTO `imp_comments` (`id`, `import_id`, `comment`, `user_id`, `initiator`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, 'comentario 1', 153, 1, '2026-02-26 16:23:41', '2026-02-26 16:23:41', NULL),
(2, 2, 'comentario 2', 153, 0, '2026-02-26 16:44:44', '2026-02-26 16:44:44', NULL),
(3, 16, 'Comentario1', 153, 1, '2026-02-26 16:52:23', '2026-02-26 16:52:23', NULL),
(4, 21, 'Cambio de precio', 8, 1, '2026-02-26 17:51:19', '2026-02-26 17:51:19', NULL),
(5, 21, 'Cambio de unidades', 8, 0, '2026-02-26 18:01:58', '2026-02-26 18:01:58', NULL),
(6, 2, 'Validar unidades', 118, 0, '2026-02-26 19:33:38', '2026-02-26 19:33:38', NULL),
(7, 7, 'Cambio de cantidad', 153, 1, '2026-02-26 19:42:45', '2026-02-26 19:42:45', NULL),
(8, 2, 'Comentario prueba', 153, 0, '2026-02-27 14:23:22', '2026-02-27 14:23:22', NULL),
(9, 1, 'Cambio de cantidad', 153, 1, '2026-02-27 15:58:19', '2026-02-27 15:58:19', NULL),
(10, 1, 'Se cambia el precio', 8, 0, '2026-02-27 16:24:47', '2026-02-27 16:24:47', NULL),
(13, 14, 'Registro de unidades nuevas', 153, 1, '2026-02-27 17:08:55', '2026-02-27 17:08:55', NULL),
(14, 21, 'Cambio del precio.', 153, 0, '2026-02-27 17:13:37', '2026-02-27 17:13:37', NULL),
(15, 19, '{\"type\":\"qty_change\",\"old\":1,\"new\":\"5\",\"note\":\"Se requiere mas unidades.\"}', 153, 1, '2026-03-03 20:05:41', '2026-03-03 20:05:41', NULL),
(16, 31, '{\"type\":\"qty_change\",\"old\":0,\"new\":\"12\",\"note\":\"Se actualiza cantidad.\"}', 153, 0, '2026-03-04 14:06:58', '2026-03-13 20:15:19', NULL),
(17, 31, 'Ok, se acepta el cambio de unidades.', 153, 0, '2026-03-04 16:03:31', '2026-03-04 16:03:31', NULL),
(18, 17, '{\"type\":\"qty_change\",\"old\":2,\"new\":\"5\",\"note\":\"Mas unidades\"}', 153, 1, '2026-03-04 19:44:58', '2026-03-04 19:44:58', NULL),
(19, 4, '{\"type\":\"price_change\",\"old\":5421,\"new\":\"750\",\"note\":\"Subio el impuesto.\"}', 181, 1, '2026-03-05 16:54:19', '2026-03-05 16:54:19', NULL),
(20, 19, '', 153, 0, '2026-03-06 15:49:17', '2026-03-06 15:49:17', NULL),
(21, 1, NULL, 153, 0, '2026-03-06 15:51:59', '2026-03-06 15:51:59', NULL),
(22, 15, '{\"type\":\"qty_change\",\"old\":3,\"new\":\"5\",\"note\":\"Cambio de cantidad.\"}', 153, 1, '2026-03-06 21:59:44', '2026-03-06 21:59:44', NULL),
(23, 27, '{\"type\":\"qty_change\",\"old\":0,\"new\":\"4\",\"note\":\"Cantidad inicial.\"}', 153, 0, '2026-03-06 22:00:26', '2026-03-18 14:49:36', NULL),
(24, 8, '{\"type\":\"qty_change\",\"old\":7,\"new\":\"10\",\"note\":\"Aumento de cantidades\"}', 153, 1, '2026-03-06 22:01:15', '2026-03-06 22:01:15', NULL),
(25, 10, '{\"type\":\"price_change\",\"old\":180,\"new\":\"189\",\"note\":\"Incremento impuesto.\"}', 181, 1, '2026-03-09 13:37:20', '2026-03-09 13:37:20', NULL),
(26, 8, '{\"type\":\"qty_change\",\"old\":10,\"new\":\"8\",\"note\":\"Solo hay 8 unidades\"}', 181, 0, '2026-03-10 14:44:03', '2026-03-10 14:44:03', NULL),
(27, 10, '{\"type\":\"qty_change\",\"old\":7,\"new\":\"5\",\"note\":\"Solo se env\\u00edan 5 cantidades.\"}', 181, 0, '2026-03-10 14:46:45', '2026-03-10 14:46:45', NULL),
(28, 2, 'Ok.', 153, 0, '2026-03-12 15:30:36', '2026-03-12 15:30:36', NULL),
(29, 14, 'Ok.', 153, 0, '2026-03-12 15:31:06', '2026-03-12 15:31:06', NULL),
(30, 22, '{\"type\":\"price_change\",\"old\":6,\"new\":\"60\",\"note\":\"Incremento en el impuesto.\"}', 181, 1, '2026-03-13 16:00:06', '2026-03-13 16:00:06', NULL),
(31, 21, 'OK.', 8, 0, '2026-03-13 17:01:46', '2026-03-13 17:01:46', NULL),
(32, 4, 'oK.', 181, 0, '2026-03-13 17:03:10', '2026-03-13 17:03:10', NULL),
(33, 7, 'Ok.', 153, 0, '2026-03-13 17:18:30', '2026-03-13 17:18:30', NULL),
(36, 31, 'Ok.', 153, 0, '2026-03-13 20:15:19', '2026-03-13 20:15:19', NULL),
(37, 12, '{\"type\":\"qty_change\",\"old\":7,\"new\":\"6\",\"note\":\"Se requieren mas unidades\"}', 153, 1, '2026-03-17 15:56:01', '2026-03-17 15:56:01', NULL),
(38, 27, 'Ok.', 153, 0, '2026-03-18 14:49:36', '2026-03-18 14:49:36', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `imp_imports`
--

CREATE TABLE `imp_imports` (
  `id` int NOT NULL,
  `item_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `label_id` int DEFAULT NULL,
  `qty_requested` int DEFAULT '0',
  `qty_shipped` int DEFAULT '0',
  `price` double DEFAULT NULL,
  `status` int DEFAULT '1',
  `packing_id` int DEFAULT NULL,
  `news` tinyint NOT NULL DEFAULT '0' COMMENT 'novedades',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `imp_imports`
--

INSERT INTO `imp_imports` (`id`, `item_id`, `user_id`, `label_id`, `qty_requested`, `qty_shipped`, `price`, `status`, `packing_id`, `news`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 18, 8, 13, 1, NULL, 550, 7, 6, 0, '2026-02-20 21:56:35', '2026-03-17 16:01:42', NULL),
(2, 36, 8, 13, 1, NULL, 6300, 7, 2, 0, '2026-02-23 14:08:43', '2026-03-12 16:46:32', NULL),
(3, 21, 8, 12, 4, 4, 5421, 7, 3, 0, '2026-02-23 17:31:49', '2026-03-12 16:48:50', NULL),
(4, 29, 8, 14, 4, NULL, 750, 5, NULL, 0, '2026-02-23 17:39:21', '2026-03-18 14:50:14', NULL),
(5, 291, 8, 10, 4, NULL, 5421, 3, NULL, 0, '2026-02-23 21:22:08', '2026-02-23 21:22:08', NULL),
(6, 1112, 8, 13, 4, NULL, 5421, 6, 4, 0, '2026-02-24 14:15:50', '2026-03-12 16:45:09', NULL),
(7, 1113, 8, 13, 12, 12, 12600, 5, NULL, 0, '2026-02-24 16:24:52', '2026-03-18 21:35:56', NULL),
(8, 1114, 8, 13, 10, 8, 15600, 7, 3, 1, '2026-02-24 16:24:52', '2026-03-12 16:48:50', NULL),
(9, 1095, 8, 13, 12, 0, 12600, 7, 3, 0, '2026-02-24 16:25:03', '2026-03-12 16:48:51', NULL),
(10, 1108, 8, 13, 7, 5, 189, 7, 1, 1, '2026-02-24 16:25:03', '2026-03-12 15:21:25', NULL),
(11, 1094, 8, 6, 12, 0, 2600, 7, 1, 0, '2026-02-24 16:25:44', '2026-03-12 15:21:26', NULL),
(12, 1105, 8, 13, 6, 0, 5600, 1, NULL, 1, '2026-02-24 16:25:44', '2026-03-17 15:56:02', NULL),
(13, 1115, 8, 14, 21, 0, 8800, 8, NULL, 0, '2026-02-24 16:25:44', '2026-02-24 16:25:44', NULL),
(14, 1116, 8, 14, 60, 0, 411, 7, 2, 0, '2026-02-24 16:25:44', '2026-03-12 16:46:33', NULL),
(15, 49, 8, 1, 5, NULL, 630, 6, 5, 0, '2026-02-24 17:11:46', '2026-03-13 17:40:52', NULL),
(16, 1110, 8, 8, 5, NULL, 0, 1, NULL, 1, '2026-02-24 17:17:25', '2026-02-24 17:17:25', NULL),
(17, 133, 8, 15, 5, NULL, 9650, 1, NULL, 1, '2026-02-24 17:58:02', '2026-03-04 19:44:58', NULL),
(18, 54, 8, 6, 2, NULL, 560, 6, 5, 0, '2026-02-24 17:58:45', '2026-03-13 19:21:30', NULL),
(19, 1109, 8, 15, 5, 5, 280, 7, 2, 0, '2026-02-24 18:17:06', '2026-03-12 16:46:34', NULL),
(20, 35, 8, 13, 5, NULL, 0, 1, NULL, 0, '2026-02-24 18:19:36', '2026-02-24 18:19:36', NULL),
(21, 27, 8, 15, 1, NULL, 250, 2, NULL, 0, '2026-02-24 19:34:21', '2026-03-13 17:01:46', NULL),
(22, 32, 8, 14, 1, NULL, 60, 2, NULL, 0, '2026-02-24 19:34:58', '2026-03-13 16:00:05', NULL),
(23, 74, 8, 15, 2, NULL, 129, 6, 4, 0, '2026-02-24 19:39:32', '2026-03-13 19:29:33', NULL),
(24, 58, 8, 10, 2, NULL, 0, 1, NULL, 0, '2026-02-24 19:40:27', '2026-02-24 19:40:27', NULL),
(25, 70, 8, 15, 1, NULL, 0, 1, NULL, 0, '2026-02-24 19:40:48', '2026-02-24 19:40:48', NULL),
(26, 1107, 8, 1, 1, NULL, 0, 1, NULL, 0, '2026-02-24 19:46:38', '2026-02-24 19:46:38', NULL),
(27, 1106, 8, 7, 4, NULL, 350, 5, 6, 0, '2026-02-24 19:48:14', '2026-03-18 14:50:18', NULL),
(28, 229, 8, 2, 0, NULL, 0, 1, NULL, 0, '2026-02-24 20:01:13', '2026-02-24 20:01:13', NULL),
(29, 284, 8, 14, 3, NULL, 0, 1, NULL, 0, '2026-02-24 20:01:41', '2026-02-24 20:01:41', NULL),
(30, 43, 8, 15, 5, NULL, 0, 1, NULL, 0, '2026-02-24 20:01:52', '2026-02-24 20:01:52', NULL),
(31, 333, 8, 6, 12, NULL, 0, 1, NULL, 0, '2026-02-24 21:56:36', '2026-03-13 20:15:20', NULL),
(32, 29, 153, 9, 5, NULL, 23450, 1, NULL, 0, '2026-03-17 15:21:57', '2026-03-17 15:21:57', NULL),
(33, 18, 153, 13, 0, NULL, 5421, 1, NULL, 0, '2026-03-18 21:47:01', '2026-03-18 21:47:01', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `imp_items_setup`
--

CREATE TABLE `imp_items_setup` (
  `id` int NOT NULL,
  `item_id` int DEFAULT NULL,
  `percentage` double NOT NULL DEFAULT '0',
  `cantidad_min` int NOT NULL DEFAULT '0',
  `supplier_id` int DEFAULT NULL COMMENT 'Proveedor',
  `factory_ref` varchar(255) DEFAULT NULL COMMENT 'Referencia del proveedor',
  `exw` decimal(10,0) DEFAULT '0' COMMENT 'Precio dado por el proveedor',
  `purchase_unit` int DEFAULT NULL COMMENT 'Unidad correspondiente al precio exw',
  `freight_increase` decimal(10,0) DEFAULT '0' COMMENT 'Incremento por fletes',
  `pvp_factor` decimal(10,0) DEFAULT '0',
  `pvp_min_factor` decimal(10,0) DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Caracteristicas de ítems para la compra o importacion';

--
-- Volcado de datos para la tabla `imp_items_setup`
--

INSERT INTO `imp_items_setup` (`id`, `item_id`, `percentage`, `cantidad_min`, `supplier_id`, `factory_ref`, `exw`, `purchase_unit`, `freight_increase`, `pvp_factor`, `pvp_min_factor`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1101, 1, 2, 107, '5', 6, NULL, 7, 8, 9, '2026-02-18 20:16:34', '2026-02-18 20:16:34', NULL),
(2, 18, 2, 1, 181, '中国布朗克斯区', 5421, 1, 2500, 0, 0, '2026-02-18 20:02:12', '2026-03-10 21:23:25', NULL),
(5, 36, 25, 2, 109, 'Referencias', 45900, NULL, 1, 1, 1, '2026-02-19 14:29:38', '2026-02-19 14:29:38', NULL),
(6, 21, 25, 2, 189, 'Referencias', 45900, NULL, 1, 1, 1, '2026-02-19 16:19:54', '2026-02-19 16:19:54', NULL),
(7, 29, 23, 2, 181, 'Proveedor', 23450, NULL, 2, 2, 2, '2026-02-19 17:21:39', '2026-02-19 17:21:39', NULL),
(8, 291, 5, 10, 108, 'Referencias', 6700, NULL, 2, 3, 1, '2026-02-19 19:05:23', '2026-02-19 19:05:23', NULL),
(9, 1112, 9, 5, 107, '2', 23400, NULL, 2, 2, 3, '2026-02-19 19:50:31', '2026-02-19 19:50:31', NULL),
(10, 1113, 4, 3, 181, 'Referencias', 34500, NULL, 3, 8, 8, '2026-02-20 13:09:32', '2026-02-20 13:09:32', NULL),
(11, 1114, 30, 2, 109, '3', 43700, NULL, 5, 5, 2, '2026-02-20 13:10:38', '2026-02-20 21:03:57', NULL),
(12, 1115, 5, 5, 189, 'Referencias', 11900, NULL, 3, 3, 1, '2026-02-20 13:37:10', '2026-02-20 13:37:10', NULL),
(13, 1116, 5, 5, 109, 'Referencias', 11900, NULL, 3, 3, 1, '2026-02-20 14:02:05', '2026-02-20 14:02:05', NULL),
(14, 1108, 30, 2, 107, 'Referencias', 12900, NULL, 2, 3, 1, '2026-02-25 13:54:43', '2026-02-25 13:54:43', NULL),
(15, 1095, 5, 3, 108, 'GiCL Alum-Profile 11035 Black, lenght 3 meter', 3650, NULL, 0, 0, 0, '2026-02-25 13:57:09', '2026-02-25 13:57:09', NULL),
(16, 1094, 16, 3, 109, 'GiCL Alum-Profile 3815 Black, lenght 3 meter', 955, NULL, 0, 0, 0, '2026-02-26 15:19:34', '2026-02-26 15:19:34', NULL),
(17, 1105, 23, 2, 181, 'RIYI Sign Alum-Profile accessory XM-2501A/B-3005A/B iron inside corner with screws', 5200, NULL, 5, 0, 0, '2026-02-26 15:37:29', '2026-02-26 15:37:29', NULL),
(18, 49, 32, 2, 181, 'RIYI Sign Alum-Profile accessory XM-2501A/B-3005A/B iron inside corner with screws', 6200, NULL, 5, 0, 0, '2026-02-27 13:26:27', '2026-02-27 13:26:27', NULL),
(19, 1110, 6, 9, 189, 'GiCL Alum-Profile 3815 Black, lenght 3 meter', 3500, NULL, 1, 1, 1, '2026-03-03 19:49:58', '2026-03-03 19:49:58', NULL),
(20, 1109, 12, 3, 107, 'GiCL Alum-Profile 3815 Black, lenght 3 meter', 6300, NULL, 1, 1, 1, '2026-03-03 19:53:54', '2026-03-03 19:53:54', NULL),
(21, 1107, 4, 4, 108, 'RIYI Sign Alum-Profile accessory XM-2501A/B-3005A/B iron inside corner with screws', 9600, NULL, 3, 3, 3, '2026-03-03 19:56:16', '2026-03-03 19:56:16', NULL),
(22, 1106, 21, 2, 181, 'RIYI Sign Alum-Profile accessory XM-2501A/B-3005A/B iron inside corner with screws', 5680, NULL, 1, 0, 0, '2026-03-03 19:59:33', '2026-03-03 19:59:33', NULL),
(23, 229, 13, 4, 189, 'RIYI Sign Alum-Profile accessory XM-2501A/B-3005A/B iron inside corner with screws', 5680, NULL, 1, 0, 0, '2026-03-03 20:03:38', '2026-03-03 20:03:38', NULL),
(24, 133, 3, 2, 181, 'RIYI Sign Alum-Profile accessory XM-2501A/B-3005A/B iron inside corner with screws', 7750, NULL, 1, 0, 0, '2026-03-04 13:52:04', '2026-03-04 13:52:04', NULL),
(25, 284, 5, 3, 107, 'RIYI Sign Alum-Profile accessory XM-2501A/B-3005A/B iron inside corner with screws', 8990, NULL, 1, 0, 0, '2026-03-04 13:56:42', '2026-03-04 13:56:42', NULL),
(26, 333, 19, 10, 181, 'GiCL Alum-Profile 3815 Black, lenght 3 meter', 11500, NULL, 1, 0, 0, '2026-03-04 14:02:11', '2026-03-04 14:02:11', NULL),
(27, 1119, 19, 10, 107, 'GiCL Alum-Profile 3815 Black, lenght 3 meter', 11500, NULL, 1, 0, 0, '2026-03-04 14:02:11', '2026-03-04 14:02:11', NULL),
(28, 27, 5, 1, 109, 'GiCL Alum-Profile 3815 Black, lenght 3 meter', 234, NULL, 4, 0, 0, '2026-03-13 16:31:34', '2026-03-13 16:31:34', NULL),
(29, 32, 12, 2, 107, 'RIYI Sign Alum-Profile accessory XM-2501A/B-3005A/B iron inside corner with screws', 59, NULL, 2, 0, 0, '2026-03-13 16:33:43', '2026-03-13 16:33:43', NULL),
(30, 35, 15, 1, 108, 'RIYI Sign Alum-Profile accessory XM-2501A/B-3005A/B iron inside corner with screws', 129, NULL, 2, 0, 0, '2026-03-13 16:34:58', '2026-03-13 16:34:58', NULL),
(31, 43, 8, 3, 189, 'GiCL Alum-Profile 3815 Black, lenght 3 meter', 67, NULL, 2, 1, 1, '2026-03-13 16:36:07', '2026-03-13 16:36:07', NULL),
(32, 54, 16, 4, 181, 'RIYI Sign Alum-Profile accessory XM-2501A/B-3005A/B iron inside corner with screws', 34, NULL, 4, 1, 1, '2026-03-13 16:38:53', '2026-03-13 16:38:53', NULL),
(33, 58, 19, 1, 107, 'RIYI Sign Alum-Profile accessory XM-2501A/B-3005A/B iron inside corner with screws', 45, NULL, 0, 0, 0, '2026-03-13 16:40:03', '2026-03-13 16:40:03', NULL),
(34, 70, 15, 2, 108, 'RIYI Sign Alum-Profile accessory XM-2501A/B-3005A/B iron inside corner with screws', 150, NULL, 0, 0, 0, '2026-03-13 16:50:04', '2026-03-13 16:50:04', NULL),
(35, 74, 16, 1, 181, 'RIYI Sign Alum-Profile accessory XM-2501A/B-3005A/B iron inside corner with screws', 199, NULL, 0, 0, 0, '2026-03-13 16:56:37', '2026-03-13 16:56:37', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `imp_labels`
--

CREATE TABLE `imp_labels` (
  `id` int NOT NULL,
  `name` varchar(255) DEFAULT '50',
  `asap` tinyint(1) DEFAULT '0',
  `estimated_date` date DEFAULT NULL,
  `description` mediumtext,
  `status` tinyint(1) DEFAULT '1',
  `user_id` int DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `imp_labels`
--

INSERT INTO `imp_labels` (`id`, `name`, `asap`, `estimated_date`, `description`, `status`, `user_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'ENE26', 0, NULL, 'prueba etiqueta #1', 1, 8, '2026-02-18 15:10:58', NULL, NULL),
(2, 'FEB26', 0, NULL, 'prueba etiqueta #2', 0, 8, '2026-02-18 15:10:58', '2026-02-25 19:42:59', NULL),
(6, 'MAR26', 0, NULL, 'Etiqueta generada para ENERO del 2019', 1, 153, '2026-02-20 17:54:05', '2026-02-20 21:21:30', NULL),
(7, 'ABR26', 0, NULL, 'Etiqueta generada para FEB del 2019', 1, 153, '2026-02-20 17:54:05', '2026-02-20 17:54:05', NULL),
(8, 'MAY26', 0, NULL, 'Etiqueta generada para MAR del 2019', 1, 153, '2026-02-20 17:54:05', '2026-02-20 17:54:05', NULL),
(9, 'JUN26', 0, NULL, 'Etiqueta generada para ABR del 2019', 1, 153, '2026-02-20 17:54:05', '2026-02-20 17:54:05', NULL),
(10, 'JUL26', 0, NULL, 'Etiqueta generada para MAY del 2019', 1, 153, '2026-02-20 17:54:05', '2026-02-20 17:54:05', NULL),
(11, 'AGO26', 0, NULL, 'Etiqueta generada para JUN del 2019', 1, 153, '2026-02-20 17:54:05', '2026-02-20 17:54:05', NULL),
(12, 'SEP26', 0, NULL, 'Etiqueta generada para JUL del 2019', 1, 153, '2026-02-20 17:54:05', '2026-02-20 17:54:05', NULL),
(13, 'OCT26', 0, NULL, 'Etiqueta generada para AGO del 2019', 1, 153, '2026-02-20 17:54:05', '2026-02-20 17:54:05', NULL),
(14, 'NOV26', 0, NULL, 'Etiqueta generada para SEP del 2019', 8, 153, '2026-02-20 17:54:05', '2026-02-20 17:54:05', NULL),
(15, 'DIC26', 0, NULL, 'Etiqueta generada para OCT del 2019', 8, 153, '2026-02-20 17:54:05', '2026-02-20 17:54:05', NULL),
(42, 'ASAP', 1, NULL, 'ASAP', 8, 153, '2026-02-23 13:43:57', '2026-02-23 13:43:57', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `imp_packing`
--

CREATE TABLE `imp_packing` (
  `id` int NOT NULL,
  `number_packing` varchar(100) NOT NULL,
  `shipping_id` int DEFAULT NULL COMMENT 'Envio',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `imp_packing`
--

INSERT INTO `imp_packing` (`id`, `number_packing`, `shipping_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'PACK1', 8, '2026-03-06 11:44:01', '2026-03-12 15:21:24', NULL),
(2, 'PACK2', 9, '2026-03-06 11:44:01', '2026-03-12 16:46:32', NULL),
(3, 'PACK3', 10, '2026-03-06 11:44:01', '2026-03-12 16:48:50', NULL),
(4, 'PACK4', NULL, '2026-03-12 15:21:26', '2026-03-12 15:21:26', NULL),
(5, 'PACK5', NULL, '2026-03-12 16:46:34', '2026-03-12 16:46:34', NULL),
(6, 'PACK6', 11, '2026-03-12 16:48:52', '2026-03-13 19:35:19', NULL),
(7, 'PACK7', NULL, '2026-03-13 19:35:20', '2026-03-13 19:35:20', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `imp_shippments`
--

CREATE TABLE `imp_shippments` (
  `id` int NOT NULL,
  `consecutive` int DEFAULT NULL,
  `etd` date DEFAULT NULL,
  `operation_number` varchar(100) DEFAULT NULL,
  `way` enum('Aérea','Maritima') DEFAULT 'Aérea',
  `conveyor` mediumtext COMMENT 'transportador',
  `obs` mediumtext,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `imp_shippments`
--

INSERT INTO `imp_shippments` (`id`, `consecutive`, `etd`, `operation_number`, `way`, `conveyor`, `obs`, `created_at`, `updated_at`, `deleted_at`) VALUES
(8, 1, '2026-04-16', 'DEL 78564', 'Maritima', 'AMAZON', 'Observación 1', '2026-03-12 15:21:24', '2026-03-12 15:21:24', NULL),
(9, 1, '2026-05-15', 'DEL 47850', 'Aérea', 'Mercado Libre', 'Observación 2', '2026-03-12 16:46:32', '2026-03-12 16:46:32', NULL),
(10, 2, '2026-06-30', 'DEL 69841', 'Maritima', 'AMAZON', 'Observación 3', '2026-03-12 16:48:50', '2026-03-12 16:48:50', NULL),
(11, 2, '2026-03-12', 'DEL 85410', 'Aérea', 'FEDEX', 'Observación', '2026-03-13 19:35:19', '2026-03-13 19:35:19', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `imp_status`
--

CREATE TABLE `imp_status` (
  `id` int NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `translated_name` varchar(50) DEFAULT NULL,
  `in_progress` tinyint(1) DEFAULT '1',
  `function` varchar(50) DEFAULT '0',
  `supplier` tinyint(1) DEFAULT '0',
  `edition` tinyint(1) DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `imp_status`
--

INSERT INTO `imp_status` (`id`, `name`, `translated_name`, `in_progress`, `function`, `supplier`, `edition`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Solicitado', 'Requested', 1, '0', 0, 1, '2026-02-19 14:18:01', NULL, NULL),
(2, 'Cotizado', 'Quoted', 1, '0', 1, 1, '2026-02-19 14:18:01', NULL, NULL),
(4, 'Aprobado', 'Approved', 1, '0', 0, 0, '2026-02-19 14:18:01', NULL, NULL),
(5, 'Produccion', 'Production', 1, '0', 1, 0, '2026-02-19 14:18:01', NULL, NULL),
(6, 'Packing', 'Packing', 1, 'en listar', 0, 1, '2026-02-19 14:18:01', NULL, NULL),
(7, 'En transito', ' In transit', 1, 'datos de envio', 1, 0, '2026-02-19 14:18:01', NULL, NULL),
(8, 'Recibido', 'Received', 0, 'datos recibido', 0, 0, '2026-02-19 14:18:01', NULL, NULL),
(9, 'Retrasado', 'Delayed', 1, 'en listar', 1, 1, '2026-02-19 14:18:01', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `imp_status_history`
--

CREATE TABLE `imp_status_history` (
  `id` int NOT NULL,
  `import_id` int DEFAULT NULL,
  `previous_state` int DEFAULT NULL,
  `new_state` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `imp_status_history`
--

INSERT INTO `imp_status_history` (`id`, `import_id`, `previous_state`, `new_state`, `user_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(3, 2, 1, 2, 181, '2026-03-04 17:48:47', '2026-03-04 17:48:47', NULL),
(4, 27, 1, 2, 181, '2026-03-04 19:43:54', '2026-03-04 19:43:54', NULL),
(5, 3, 2, 4, 153, '2026-03-05 14:07:51', '2026-03-05 14:07:51', NULL),
(6, 15, 1, 2, 181, '2026-03-05 15:43:16', '2026-03-05 15:43:16', NULL),
(12, 8, 4, 5, 181, '2026-03-05 22:46:48', '2026-03-05 22:46:48', NULL),
(13, 8, 5, 6, 181, '2026-03-06 21:13:16', '2026-03-06 21:13:16', NULL),
(14, 8, 5, 6, 181, '2026-03-06 21:15:08', '2026-03-06 21:15:08', NULL),
(15, 19, 1, 2, 181, '2026-03-06 21:21:54', '2026-03-06 21:21:54', NULL),
(16, 14, 1, 2, 181, '2026-03-06 21:21:59', '2026-03-06 21:21:59', NULL),
(17, 23, 1, 2, 181, '2026-03-06 21:22:11', '2026-03-06 21:22:11', NULL),
(18, 15, 2, 4, 153, '2026-03-06 21:23:48', '2026-03-06 21:23:48', NULL),
(19, 19, 2, 4, 153, '2026-03-06 21:24:03', '2026-03-06 21:24:03', NULL),
(20, 27, 2, 4, 153, '2026-03-06 21:24:09', '2026-03-06 21:24:09', NULL),
(21, 27, 4, 5, 181, '2026-03-06 21:27:38', '2026-03-06 21:27:38', NULL),
(22, 19, 4, 5, 181, '2026-03-06 21:27:44', '2026-03-06 21:27:44', NULL),
(23, 3, 4, 5, 181, '2026-03-06 21:27:51', '2026-03-06 21:27:51', NULL),
(24, 3, 5, 6, 181, '2026-03-06 21:31:32', '2026-03-06 21:31:32', NULL),
(25, 9, 5, 6, 181, '2026-03-06 21:31:35', '2026-03-06 21:31:35', NULL),
(26, 10, 5, 6, 181, '2026-03-09 13:29:14', '2026-03-09 13:29:14', NULL),
(27, 10, 6, 2, 181, '2026-03-09 13:33:03', '2026-03-09 13:33:03', NULL),
(28, 10, 2, 4, 153, '2026-03-09 13:35:41', '2026-03-09 13:35:41', NULL),
(29, 10, 4, 5, 181, '2026-03-09 13:36:37', '2026-03-09 13:36:37', NULL),
(30, 10, 5, 6, 181, '2026-03-09 13:36:50', '2026-03-09 13:36:50', NULL),
(31, 19, 5, 6, 181, '2026-03-09 17:05:29', '2026-03-09 17:05:29', NULL),
(32, 10, 6, 7, 181, '2026-03-12 14:17:55', '2026-03-12 14:17:55', NULL),
(33, 11, 6, 7, 181, '2026-03-12 14:17:56', '2026-03-12 14:17:56', NULL),
(34, 10, 6, 7, 181, '2026-03-12 15:21:25', '2026-03-12 15:21:25', NULL),
(35, 11, 6, 7, 181, '2026-03-12 15:21:26', '2026-03-12 15:21:26', NULL),
(36, 23, 2, 4, 153, '2026-03-12 15:31:17', '2026-03-12 15:31:17', NULL),
(37, 14, 2, 4, 153, '2026-03-12 15:31:23', '2026-03-12 15:31:23', NULL),
(38, 2, 2, 4, 153, '2026-03-12 15:31:28', '2026-03-12 15:31:28', NULL),
(39, 2, 4, 5, 181, '2026-03-12 15:32:12', '2026-03-12 15:32:12', NULL),
(40, 6, 4, 5, 181, '2026-03-12 15:32:17', '2026-03-12 15:32:17', NULL),
(41, 14, 4, 5, 181, '2026-03-12 15:32:22', '2026-03-12 15:32:22', NULL),
(42, 23, 4, 5, 181, '2026-03-12 15:32:26', '2026-03-12 15:32:26', NULL),
(43, 2, 5, 6, 181, '2026-03-12 15:32:55', '2026-03-12 15:32:55', NULL),
(44, 14, 5, 6, 181, '2026-03-12 15:37:00', '2026-03-12 15:37:00', NULL),
(45, 23, 5, 6, 181, '2026-03-12 16:45:07', '2026-03-12 16:45:07', NULL),
(46, 6, 5, 6, 181, '2026-03-12 16:45:09', '2026-03-12 16:45:09', NULL),
(47, 2, 6, 7, 181, '2026-03-12 16:46:33', '2026-03-12 16:46:33', NULL),
(48, 14, 6, 7, 181, '2026-03-12 16:46:33', '2026-03-12 16:46:33', NULL),
(49, 19, 6, 7, 181, '2026-03-12 16:46:34', '2026-03-12 16:46:34', NULL),
(50, 3, 6, 7, 181, '2026-03-12 16:48:50', '2026-03-12 16:48:50', NULL),
(51, 8, 6, 7, 181, '2026-03-12 16:48:51', '2026-03-12 16:48:51', NULL),
(52, 9, 6, 7, 181, '2026-03-12 16:48:52', '2026-03-12 16:48:52', NULL),
(53, 21, 1, 2, 181, '2026-03-13 15:21:12', '2026-03-13 15:21:12', NULL),
(54, 22, 1, 2, 181, '2026-03-13 15:40:22', '2026-03-13 15:40:22', NULL),
(55, 15, 4, 5, 181, '2026-03-13 17:38:53', '2026-03-13 17:38:53', NULL),
(56, 15, 5, 6, 181, '2026-03-13 17:40:52', '2026-03-13 17:40:52', NULL),
(57, 27, 5, 6, 181, '2026-03-13 17:50:20', '2026-03-13 17:50:20', NULL),
(58, 18, 1, 2, 181, '2026-03-13 19:19:55', '2026-03-13 19:19:55', NULL),
(59, 18, 2, 4, 153, '2026-03-13 19:20:40', '2026-03-13 19:20:40', NULL),
(60, 18, 4, 5, 181, '2026-03-13 19:21:17', '2026-03-13 19:21:17', NULL),
(61, 18, 5, 6, 181, '2026-03-13 19:21:30', '2026-03-13 19:21:30', NULL),
(62, 1, 1, 2, 181, '2026-03-13 19:23:38', '2026-03-13 19:23:38', NULL),
(63, 1, 2, 4, 153, '2026-03-13 19:24:22', '2026-03-13 19:24:22', NULL),
(64, 1, 4, 5, 181, '2026-03-13 19:25:00', '2026-03-13 19:25:00', NULL),
(65, 1, 5, 6, 181, '2026-03-13 19:25:13', '2026-03-13 19:25:13', NULL),
(66, 23, 5, 6, 181, '2026-03-13 19:29:33', '2026-03-13 19:29:33', NULL),
(67, 1, 6, 7, 181, '2026-03-13 19:35:19', '2026-03-13 19:35:19', NULL),
(68, 27, 6, 7, 181, '2026-03-13 19:35:20', '2026-03-13 19:35:20', NULL),
(69, 1, 7, 2, 181, '2026-03-17 16:01:42', '2026-03-17 16:01:42', NULL),
(70, 27, 7, 2, 181, '2026-03-17 16:04:58', '2026-03-17 16:04:58', NULL),
(71, 4, 2, 4, 153, '2026-03-18 14:49:24', '2026-03-18 14:49:24', NULL),
(72, 27, 2, 4, 153, '2026-03-18 14:49:43', '2026-03-18 14:49:43', NULL),
(73, 4, 4, 5, 181, '2026-03-18 14:50:14', '2026-03-18 14:50:14', NULL),
(74, 27, 4, 5, 181, '2026-03-18 14:50:18', '2026-03-18 14:50:18', NULL),
(75, 7, 4, 5, 181, '2026-03-18 21:35:56', '2026-03-18 21:35:56', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `imp_unconfirmed_qty`
--

CREATE TABLE `imp_unconfirmed_qty` (
  `id` int NOT NULL,
  `item_id` int DEFAULT NULL,
  `qty` int DEFAULT '0',
  `status` tinyint(1) DEFAULT '1',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Guarda las cantidades de producto que no han sido asignadas a una etiqueta';

--
-- Volcado de datos para la tabla `imp_unconfirmed_qty`
--

INSERT INTO `imp_unconfirmed_qty` (`id`, `item_id`, `qty`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 21, 3, 1, '2026-02-19 20:35:21', '2026-02-24 17:11:46', '2026-02-24 17:11:46'),
(3, 36, 1, 1, '2026-02-20 19:57:26', '2026-02-23 14:34:16', NULL),
(4, 21, 2, 1, '2026-02-24 17:57:50', '2026-02-24 17:58:02', '2026-02-24 17:58:02'),
(5, 21, 2, 1, '2026-02-24 17:58:34', '2026-02-24 17:58:45', '2026-02-24 17:58:45'),
(6, 18, 1, 1, '2026-02-24 18:16:58', '2026-02-24 18:17:06', '2026-02-24 18:17:06'),
(7, 291, 5, 1, '2026-02-24 18:19:29', '2026-02-24 18:19:36', '2026-02-24 18:19:36'),
(8, 29, 0, 1, '2026-02-24 19:34:11', '2026-03-17 15:21:57', NULL),
(9, 1116, 0, 1, '2026-02-24 19:39:05', '2026-02-24 19:39:32', NULL),
(10, 1115, 0, 1, '2026-02-24 19:40:18', '2026-02-24 19:40:48', NULL),
(11, 18, 3, 1, '2026-02-24 19:46:38', '2026-03-18 21:48:04', NULL),
(12, 21, 0, 1, '2026-02-24 20:01:41', '2026-02-24 20:01:41', NULL),
(13, 291, 0, 1, '2026-02-24 20:01:52', '2026-02-24 20:01:53', NULL),
(14, 1114, 12, 1, '2026-02-26 16:56:49', '2026-02-26 16:56:49', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_applications`
--

CREATE TABLE `inv_applications` (
  `id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '1',
  `icon_path` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_categories`
--

CREATE TABLE `inv_categories` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` tinyint DEFAULT '1',
  `api_data_id` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `inv_categories`
--

INSERT INTO `inv_categories` (`id`, `name`, `status`, `api_data_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'GENERICO CHINO', 1, '0', '2025-10-31 16:18:01', NULL, NULL),
(2, 'JUGUETERIA', 1, '0', '2025-11-12 17:52:01', '2025-11-12 17:52:01', NULL),
(3, 'ZAPATERIA', 1, '0', '2025-11-12 21:07:12', '2025-11-12 21:07:12', NULL),
(4, 'TECNOLOGÌA V2', 0, '0', '2025-11-12 21:16:13', '2025-11-12 21:16:27', NULL),
(5, 'HOGAR', 1, '0', '2025-11-13 21:24:56', '2025-11-18 15:02:20', NULL),
(6, 'PAPELERIA', 1, '0', '2025-11-20 21:58:17', '2025-11-20 21:58:17', NULL),
(35, 'FERRETERIA', 1, '0', '2025-11-21 16:58:14', '2025-11-21 16:58:14', NULL),
(36, 'VEHICULOS', 1, '0', '2025-11-21 16:59:41', '2025-11-21 16:59:41', NULL),
(37, 'LIBROS', 1, '0', '2025-11-21 17:03:09', '2025-11-21 17:03:09', NULL),
(38, 'TERMOS', 1, '0', '2025-11-21 17:32:01', '2025-11-21 17:32:01', NULL),
(39, 'GAFAS', 1, '0', '2025-11-21 17:33:54', '2025-11-21 17:33:54', NULL),
(40, 'AUDIFONOS BLUETOOTH', 1, '0', '2025-12-03 13:28:11', '2025-12-03 13:28:11', NULL),
(41, 'ALMOHADA', 1, '0', '2025-12-04 20:33:34', '2025-12-04 20:33:34', NULL),
(74, 'FRUTAS', 1, '77', '2026-01-20 19:04:58', '2026-01-20 19:05:03', NULL),
(81, 'SEBASTIAN', 1, '0', '2026-01-21 19:28:59', '2026-01-21 19:28:59', NULL),
(82, 'COMPUTADORES', 1, '78', '2026-01-21 19:31:38', '2026-01-21 19:31:42', NULL),
(83, 'flores', 1, '79', '2026-01-21 19:46:57', '2026-02-13 19:56:57', NULL),
(84, 'sillas', 1, '80', '2026-01-21 19:49:04', '2026-01-21 19:49:06', NULL),
(86, 'ACCESORIOS MASCOTAS', 1, '81', '2026-01-22 19:15:27', '2026-01-30 16:13:19', NULL),
(89, 'categoria nueva dos', 1, '82', '2026-01-22 19:53:01', '2026-01-22 19:53:05', NULL),
(93, 'CAFETERIAS', 1, '83', '2026-01-23 13:41:48', '2026-01-23 13:41:52', NULL),
(94, 'IMPRESORAS', 1, '84', '2026-01-23 16:21:01', '2026-01-23 16:21:05', NULL),
(95, 'MOTOS', 1, '0', '2026-01-23 16:26:01', '2026-01-23 16:26:01', NULL),
(96, 'ULTIMA', 1, '85', '2026-01-26 14:47:47', '2026-01-26 14:47:52', NULL),
(97, 'RELOG', 1, '0', '2026-01-27 14:13:34', '2026-01-27 14:13:34', NULL),
(98, 'MATAS', 1, '0', '2026-01-27 16:46:35', '2026-01-27 16:46:35', NULL),
(99, 'api produccion', 1, '0', '2026-02-02 17:08:00', '2026-02-02 17:08:00', NULL),
(100, 'MARSELLA productivo', 1, '0', '2026-02-02 17:13:46', '2026-02-02 17:13:46', NULL),
(101, 'marsella productivo tenant', 1, '86', '2026-02-02 17:17:10', '2026-02-13 19:58:52', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_command`
--

CREATE TABLE `inv_command` (
  `id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '1',
  `print_path` varchar(100) DEFAULT NULL,
  `status` tinyint DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `inv_command`
--

INSERT INTO `inv_command` (`id`, `name`, `print_path`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'COMANDA 1', 'http://127.0.0.1:8000/inventory/commands', 1, '2025-11-12 19:28:44', '2025-11-12 19:28:44', NULL),
(2, 'COMANDA 2', 'http://127.0.0.1:8000/inventory/commands', 1, '2025-11-12 21:07:51', '2025-11-12 21:07:51', NULL),
(3, 'COMANDA REST', 'http://ruta/impresion', 0, '2025-11-12 21:18:41', '2025-11-18 15:24:09', NULL),
(4, 'Impresora 1', 'http://impresora/1', 1, '2025-11-21 13:38:45', '2025-11-21 13:38:45', NULL),
(5, 'Impresora 2', 'http:/impresoraDESKJET20', 1, '2025-12-03 13:28:45', '2025-12-03 13:28:45', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_delivery_types`
--

CREATE TABLE `inv_delivery_types` (
  `id` int NOT NULL,
  `name` varchar(40) NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  `ask_details` int NOT NULL DEFAULT '0',
  `detail` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `inv_delivery_types`
--

INSERT INTO `inv_delivery_types` (`id`, `name`, `status`, `ask_details`, `detail`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'mensajero', 1, 0, '', '2026-02-19 16:51:03', NULL, NULL),
(2, 'Cliente recoge', 1, 0, '', '2026-02-19 16:51:03', NULL, NULL),
(3, 'Trans a cargo de Fervicom', 0, 0, '', '2026-02-19 16:51:03', NULL, NULL),
(4, 'Trans Contraentrega', 0, 1, 'Empresa transporte', '2026-02-19 16:51:03', NULL, NULL),
(7, 'Cliente en punto de venta', 1, 0, '', '2026-02-19 16:51:03', NULL, NULL),
(8, 'Envía paga Fervicom', 0, 0, '', '2026-02-19 16:51:03', NULL, NULL),
(9, 'Envía contraentrega', 0, 0, '', '2026-02-19 16:51:03', NULL, NULL),
(10, 'Interrapidisimo contra entrega', 1, 0, '', '2026-02-19 16:51:03', NULL, NULL),
(11, 'Deprisa contra entrega', 1, 0, '', '2026-02-19 16:51:03', NULL, NULL),
(12, 'Servientrega contra entrega', 0, 0, '', '2026-02-19 16:51:03', NULL, NULL),
(13, 'Otro', 1, 0, 'Describa otro', '2026-02-19 16:51:03', NULL, NULL),
(14, 'A tu puerta', 0, 0, '', '2026-02-19 16:51:03', NULL, NULL),
(15, 'Mercado libre', 1, 0, '', '2026-02-19 16:51:03', NULL, NULL),
(16, 'Mensajeros urbanos', 1, 0, '', '2026-02-19 16:51:03', NULL, NULL),
(17, 'Coordinadora paga fervicom', 0, 0, '', '2026-02-19 16:51:03', NULL, NULL),
(18, 'Coordinadora contraentrega', 1, 0, '', '2026-02-19 16:51:03', NULL, NULL),
(19, 'Mercado flex', 1, 0, '', '2026-02-19 16:51:03', NULL, NULL),
(20, 'COOR-SERVIEN paga Fervicom', 1, 0, '', '2026-02-19 16:51:03', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_detail_inventory`
--

CREATE TABLE `inv_detail_inventory` (
  `id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '0',
  `date` varchar(255) NOT NULL,
  `storeId` int DEFAULT NULL,
  `itemId` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_detail_inv_adjustments`
--

CREATE TABLE `inv_detail_inv_adjustments` (
  `id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '0',
  `cost` double DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `inventoryAdjustmentId` int DEFAULT NULL,
  `itemId` int DEFAULT NULL,
  `unitMeasurementId` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `inv_detail_inv_adjustments`
--

INSERT INTO `inv_detail_inv_adjustments` (`id`, `quantity`, `cost`, `created_at`, `updated_at`, `deleted_at`, `inventoryAdjustmentId`, `itemId`, `unitMeasurementId`) VALUES
(1, 4, 0, '2025-12-01 21:44:47', '2025-12-01 21:44:47', NULL, 1, 16, 35),
(2, 2, 0, '2025-12-03 13:56:39', '2025-12-03 13:56:39', NULL, 2, 11, 35),
(3, 5, 0, '2026-01-20 17:08:11', '2026-01-20 17:08:11', NULL, 3, 11, 35),
(4, 10, 54200, '2026-01-20 21:49:06', '2026-01-20 21:49:06', NULL, 4, 3, 35),
(5, 5, 20000, '2026-01-20 21:52:22', '2026-01-20 21:52:22', NULL, 5, 20, 35),
(6, 5, 3500, '2026-01-20 21:59:12', '2026-01-20 21:59:12', NULL, 6, 11, 35),
(7, 2, 5100, '2026-01-26 13:21:27', '2026-01-26 13:21:27', NULL, 7, 5, 35),
(8, 10, 5220, '2026-01-26 18:57:09', '2026-01-26 18:57:09', NULL, 8, 49, 35),
(9, 3, 0, '2026-01-29 14:45:28', '2026-01-29 14:45:28', NULL, 9, 30, 13),
(10, 1, 0, '2026-01-29 15:23:23', '2026-01-29 15:23:23', NULL, 10, 30, 35),
(11, 1, 0, '2026-01-29 17:21:05', '2026-01-29 17:21:05', NULL, 11, 1064, 35),
(12, 100, 29000, '2026-02-01 22:31:18', '2026-02-01 22:31:18', NULL, 12, 1093, 35),
(13, 100, 0, '2026-02-01 22:36:57', '2026-02-01 22:36:57', NULL, 13, 1083, 35),
(14, 2, 0, '2026-02-10 21:09:32', '2026-02-10 21:09:32', NULL, 14, 11, 35),
(15, 1, 0, '2026-02-11 13:55:07', '2026-02-11 13:55:07', NULL, 15, 25, 35),
(16, 1, 0, '2026-02-11 13:55:07', '2026-02-11 13:55:07', NULL, 15, 17, 14),
(17, 2, 0, '2026-02-11 13:55:07', '2026-02-11 13:55:07', NULL, 15, 43, 27),
(18, 2, 0, '2026-02-11 17:47:41', '2026-02-11 17:47:41', NULL, 16, 17, 6),
(19, 3, 0, '2026-02-11 17:47:41', '2026-02-11 17:47:41', NULL, 16, 25, 13),
(20, 5, 5200, '2026-02-18 19:02:08', '2026-02-18 19:02:08', NULL, 17, 18, 35),
(21, 5, 4500, '2026-02-18 19:02:08', '2026-02-18 19:02:08', NULL, 17, 21, 35),
(22, 20, 820000, '2026-02-18 19:02:08', '2026-02-18 19:02:08', NULL, 17, 29, 35),
(23, 6, 35000, '2026-02-18 19:02:08', '2026-02-18 19:02:08', NULL, 17, 35, 35),
(24, 5, 50000, '2026-02-18 19:02:08', '2026-02-18 19:02:08', NULL, 17, 36, 35),
(25, 5, 90500, '2026-02-18 19:23:57', '2026-02-18 19:23:57', NULL, 18, 18, 35),
(26, 6, 420000, '2026-02-18 19:23:57', '2026-02-18 19:23:57', NULL, 18, 21, 35),
(27, 6, 2300000, '2026-02-18 19:23:57', '2026-02-18 19:23:57', NULL, 18, 29, 35),
(28, 5, 75000, '2026-02-18 19:23:57', '2026-02-18 19:23:57', NULL, 18, 35, 35),
(29, 5, 85000, '2026-02-18 19:23:57', '2026-02-18 19:23:57', NULL, 18, 36, 35),
(30, 5, 0, '2026-02-18 20:29:56', '2026-02-18 20:29:56', NULL, 19, 1083, 35),
(31, 5, 0, '2026-02-18 20:35:07', '2026-02-18 20:35:07', NULL, 20, 1083, 35),
(32, 10, 0, '2026-02-18 20:48:03', '2026-02-18 20:48:03', NULL, 21, 1083, 35),
(33, 5, 0, '2026-02-18 21:05:01', '2026-02-18 21:05:01', NULL, 22, 1083, 35),
(34, 25, 0, '2026-03-09 16:04:43', '2026-03-09 16:04:43', NULL, 23, 1120, 35),
(35, 25, 2500, '2026-03-09 17:47:40', '2026-03-09 17:47:40', NULL, 24, 1120, 35),
(36, 25, 25000, '2026-03-09 17:47:40', '2026-03-09 17:47:40', NULL, 24, 1122, 35);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_detail_remissions`
--

CREATE TABLE `inv_detail_remissions` (
  `id` int NOT NULL,
  `quantity` int DEFAULT '0',
  `tax` int DEFAULT NULL,
  `value` int DEFAULT '0',
  `invoiceId` int DEFAULT NULL,
  `itemId` int DEFAULT NULL,
  `remissionId` int DEFAULT NULL,
  `cant_return` int DEFAULT NULL,
  `observations_return` text,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `inv_detail_remissions`
--

INSERT INTO `inv_detail_remissions` (`id`, `quantity`, `tax`, `value`, `invoiceId`, `itemId`, `remissionId`, `cant_return`, `observations_return`, `created_at`, `updated_at`) VALUES
(4, 1, 0, 2850, NULL, 1060, 1, NULL, NULL, '2026-01-21 16:28:15', '2026-01-21 16:28:15'),
(5, 1, 0, 86837, NULL, 1032, 1, NULL, NULL, '2026-01-21 16:28:15', '2026-01-21 16:28:15'),
(6, 1, 0, 18589, NULL, 1033, 1, NULL, NULL, '2026-01-21 16:28:15', '2026-01-21 16:28:15'),
(7, 12, 0, 144404, NULL, 1035, 1, NULL, NULL, '2026-01-21 16:28:15', '2026-01-21 16:28:15'),
(8, 3, 0, 60147, NULL, 1030, 2, NULL, NULL, '2026-01-29 20:20:27', '2026-01-29 20:20:27'),
(9, 2, 0, 108754, NULL, 30, 2, NULL, NULL, '2026-01-29 20:20:27', '2026-01-29 20:20:27'),
(10, 1, 0, 83400, NULL, 1064, 3, NULL, NULL, '2026-01-30 13:48:22', '2026-01-30 13:48:22'),
(11, 1, 0, 107509, NULL, 11, 4, NULL, NULL, '2026-01-30 14:23:57', '2026-01-30 14:23:57'),
(12, 1, 0, 83400, NULL, 1064, 5, NULL, NULL, '2026-01-30 14:33:12', '2026-01-30 14:33:12'),
(13, 1, 0, 96758, NULL, 11, 5, NULL, NULL, '2026-01-30 14:33:12', '2026-01-30 14:33:12'),
(14, 1, 0, 83400, NULL, 1064, 6, NULL, NULL, '2026-01-30 14:35:48', '2026-01-30 14:35:48'),
(15, 1, 0, 96758, NULL, 11, 6, NULL, NULL, '2026-01-30 14:35:48', '2026-01-30 14:35:48'),
(18, 3, 19, 17850, 47, 1083, 8, NULL, NULL, '2026-02-01 17:07:48', '2026-02-01 17:07:48'),
(19, 2, 0, 56700, 46, 1093, 9, NULL, NULL, '2026-02-01 22:32:53', '2026-02-01 22:32:53'),
(20, 3, 0, 63000, 46, 1093, 10, NULL, NULL, '2026-02-01 22:37:21', '2026-02-01 22:37:21'),
(21, 1, 5, 20528, 46, 1083, 10, NULL, NULL, '2026-02-01 22:37:21', '2026-02-01 22:37:21'),
(22, 5, 5, 16065, 48, 1083, 7, NULL, NULL, '2026-02-02 13:16:59', '2026-02-02 13:16:59'),
(27, 3, 0, 60147, NULL, 1030, 13, NULL, NULL, '2026-02-02 19:41:43', '2026-02-02 19:41:43'),
(28, 2, 0, 108754, NULL, 30, 13, NULL, NULL, '2026-02-02 19:41:43', '2026-02-02 19:41:43'),
(29, 6, 5, 164151, NULL, 30, 14, NULL, NULL, '2026-02-02 19:45:16', '2026-02-02 19:45:16'),
(30, 1, 5, 63154, NULL, 1030, 14, NULL, NULL, '2026-02-02 19:45:16', '2026-02-02 19:45:16'),
(31, 2, 5, 56700, 49, 1093, 15, NULL, NULL, '2026-02-04 13:41:02', '2026-02-04 13:42:31'),
(32, 1, 19, 14122, 49, 1083, 16, NULL, NULL, '2026-02-04 13:41:25', '2026-02-04 13:42:31'),
(33, 1, 5, 56700, 49, 1093, 16, NULL, NULL, '2026-02-04 13:41:25', '2026-02-04 13:42:31'),
(34, 1, 5, 56700, NULL, 1093, 17, NULL, NULL, '2026-02-05 15:37:14', '2026-02-05 15:37:14'),
(35, 1, 19, 42299, 83, 1083, 18, NULL, NULL, '2026-02-16 19:10:30', '2026-02-16 21:03:56'),
(36, 1, 5, 98470, 83, 1093, 18, NULL, NULL, '2026-02-16 19:10:30', '2026-02-16 21:03:56'),
(37, 3, 5, 125823, 84, 1093, 19, NULL, NULL, '2026-02-16 21:12:46', '2026-02-17 14:55:19'),
(38, 1, 5, 98470, 85, 1093, 20, NULL, NULL, '2026-02-17 17:19:20', '2026-02-17 17:30:24'),
(39, 1, 19, 46999, 85, 1083, 20, NULL, NULL, '2026-02-17 17:19:20', '2026-02-17 17:30:24'),
(40, 1, 5, 125823, 86, 1093, 21, NULL, NULL, '2026-02-17 17:20:47', '2026-02-17 17:34:23'),
(41, 2, 19, 42299, 95, 1083, 22, NULL, NULL, '2026-02-17 17:26:39', '2026-02-18 16:27:44'),
(42, 1, 19, 54049, 94, 1083, 23, NULL, NULL, '2026-02-17 17:58:22', '2026-02-18 16:23:46'),
(43, 1, 5, 87529, 94, 1093, 23, NULL, NULL, '2026-02-17 17:58:22', '2026-02-18 16:23:46'),
(44, 1, 5, 125823, 93, 1093, 24, NULL, NULL, '2026-02-18 13:11:30', '2026-02-18 15:26:17'),
(45, 1, 19, 46999, 93, 1083, 25, NULL, NULL, '2026-02-18 13:13:23', '2026-02-18 15:26:17'),
(46, 1, 5, 125823, 93, 1093, 26, NULL, NULL, '2026-02-18 13:53:11', '2026-02-18 15:26:18'),
(47, 1, 5, 125823, 92, 1093, 27, NULL, NULL, '2026-02-18 14:09:10', '2026-02-18 14:31:18'),
(51, 1, 5, 125823, 92, 1093, 28, NULL, NULL, '2026-02-18 14:25:52', '2026-02-18 14:31:18'),
(52, 1, 19, 54049, 92, 1083, 28, NULL, NULL, '2026-02-18 14:25:52', '2026-02-18 14:31:18'),
(53, 1, 5, 98470, 97, 1093, 29, NULL, NULL, '2026-02-19 17:30:33', '2026-03-11 13:39:49'),
(54, 2, 5, 98470, 97, 1093, 30, NULL, NULL, '2026-01-01 20:31:01', '2026-03-11 13:39:49'),
(55, 1, 5, 1323529, NULL, 291, 31, NULL, NULL, '2026-02-24 20:23:27', '2026-02-24 20:23:27'),
(56, 1, 5, 147705, NULL, 18, 31, NULL, NULL, '2026-02-24 20:23:27', '2026-02-24 20:23:27'),
(57, 1, 5, 158294, NULL, 21, 31, NULL, NULL, '2026-02-24 20:23:27', '2026-02-24 20:23:27'),
(58, 1, 5, 809117, NULL, 29, 31, NULL, NULL, '2026-02-24 20:23:27', '2026-02-24 20:23:27'),
(59, 1, 5, 503470, NULL, 36, 31, NULL, NULL, '2026-02-24 20:23:27', '2026-02-24 20:23:27'),
(60, 1, 5, 11800, NULL, 1114, 31, NULL, NULL, '2026-02-24 20:23:27', '2026-02-24 20:23:27'),
(61, 1, 5, 1191176, NULL, 291, 32, NULL, NULL, '2026-02-24 20:31:01', '2026-02-24 20:31:01'),
(62, 1, 5, 147705, NULL, 18, 32, NULL, NULL, '2026-02-24 20:31:01', '2026-02-24 20:31:01'),
(63, 8, 5, 137647, NULL, 21, 32, NULL, NULL, '2026-02-24 20:31:01', '2026-02-24 20:31:01'),
(64, 5, 5, 728206, NULL, 29, 32, NULL, NULL, '2026-02-24 20:31:01', '2026-02-24 20:31:01'),
(65, 2, 5, 559412, NULL, 36, 32, NULL, NULL, '2026-02-24 20:31:01', '2026-02-24 20:31:01'),
(66, 1, 5, 11800, NULL, 1114, 32, NULL, NULL, '2026-02-24 20:31:01', '2026-02-24 20:31:01'),
(67, 3, 19, 42299, 96, 1083, 33, NULL, NULL, '2026-03-10 22:05:09', '2026-03-10 22:06:09'),
(68, 3, 19, 42299, 98, 1083, 34, NULL, NULL, '2026-03-12 20:31:31', '2026-03-12 20:33:55'),
(69, 2, 5, 109411, 100, 1093, 35, NULL, NULL, '2026-03-13 14:07:58', '2026-03-13 14:13:00'),
(70, 3, 19, 42299, 99, 1083, 36, NULL, NULL, '2026-03-13 14:10:29', '2026-03-13 14:12:08'),
(71, 4, 19, 42299, 101, 1083, 37, NULL, NULL, '2026-03-13 15:35:51', '2026-03-13 15:57:54'),
(72, 3, 5, 98470, NULL, 1093, 38, NULL, NULL, '2026-04-10 13:36:26', '2026-04-10 13:36:26'),
(73, 1, 19, 54049, NULL, 1083, 39, NULL, NULL, '2026-04-10 13:38:47', '2026-04-10 13:38:47'),
(74, 1, 5, 125823, NULL, 1093, 40, NULL, NULL, '2026-04-10 13:40:11', '2026-04-10 13:40:11'),
(75, 1, 5, 125823, NULL, 1093, 41, NULL, NULL, '2026-04-10 13:41:17', '2026-04-10 13:41:17'),
(76, 1, 5, 125823, NULL, 1093, 42, NULL, NULL, '2026-04-10 13:43:57', '2026-04-10 13:43:57'),
(77, 1, 5, 98470, NULL, 1093, 43, NULL, NULL, '2026-04-10 13:44:54', '2026-04-10 13:44:54'),
(78, 1, 19, 46999, NULL, 1083, 43, NULL, NULL, '2026-04-10 13:44:54', '2026-04-10 13:44:54'),
(79, 3, 5, 125823, NULL, 1093, 44, NULL, NULL, '2026-04-10 13:51:57', '2026-04-10 13:51:57'),
(80, 1, 5, 98470, NULL, 1093, 45, NULL, NULL, '2026-04-10 13:52:48', '2026-04-10 13:52:48'),
(81, 1, 19, 42299, NULL, 1083, 46, NULL, NULL, '2026-04-10 13:53:33', '2026-04-10 13:53:33'),
(82, 1, 5, 98470, NULL, 1093, 46, NULL, NULL, '2026-04-10 13:53:33', '2026-04-10 13:53:33'),
(83, 6, 5, 98470, NULL, 1093, 47, NULL, NULL, '2026-04-10 13:54:18', '2026-04-10 13:54:18'),
(84, 6, 19, 54049, NULL, 1083, 47, NULL, NULL, '2026-04-10 13:54:18', '2026-04-10 13:54:18'),
(85, 6, 5, 98470, NULL, 1093, 48, NULL, NULL, '2026-04-10 13:55:00', '2026-04-10 13:55:00'),
(86, 6, 19, 54049, NULL, 1083, 48, NULL, NULL, '2026-04-10 13:55:00', '2026-04-10 13:55:00'),
(87, 5, 5, 98470, NULL, 1093, 49, NULL, NULL, '2026-04-10 13:55:50', '2026-04-10 13:55:50'),
(88, 6, 19, 54049, NULL, 1083, 49, NULL, NULL, '2026-04-10 13:55:50', '2026-04-10 13:55:50'),
(89, 8, 5, 87529, NULL, 1093, 50, NULL, NULL, '2026-04-12 18:04:05', '2026-04-12 18:04:05'),
(90, 2, 19, 137900, NULL, 1105, 50, NULL, NULL, '2026-04-12 18:04:05', '2026-04-12 18:04:05'),
(91, 7, 5, 19800, NULL, 1110, 50, NULL, NULL, '2026-04-12 18:04:05', '2026-04-12 18:04:05'),
(92, 5, 5, 53600, NULL, 1115, 50, NULL, NULL, '2026-04-12 18:04:05', '2026-04-12 18:04:05'),
(93, 6, 5, 24900, NULL, 1113, 50, NULL, NULL, '2026-04-12 18:04:05', '2026-04-12 18:04:05'),
(94, 5, 19, 1731, NULL, 1121, 50, NULL, NULL, '2026-04-12 18:04:05', '2026-04-12 18:04:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_detail_transfers`
--

CREATE TABLE `inv_detail_transfers` (
  `id` int NOT NULL,
  `quantity` int DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `transferId` int DEFAULT NULL,
  `itemId` int DEFAULT NULL,
  `amount_received` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `inv_detail_transfers`
--

INSERT INTO `inv_detail_transfers` (`id`, `quantity`, `created_at`, `updated_at`, `deleted_at`, `transferId`, `itemId`, `amount_received`) VALUES
(1, 9, '2026-02-12 21:49:22', '2026-02-12 22:01:45', NULL, 2, 11, 9),
(2, 10, '2026-02-12 21:49:39', '2026-02-12 21:56:58', NULL, 3, 17, 10),
(3, 5, '2026-02-12 21:49:55', '2026-02-12 21:56:28', NULL, 4, 25, 5),
(4, 2, '2026-02-13 13:58:37', '2026-02-13 13:58:37', NULL, 5, 11, 0),
(5, 10, '2026-02-13 14:10:52', '2026-02-13 14:10:52', NULL, 6, 17, 0),
(6, 2, '2026-02-13 14:12:25', '2026-02-13 14:12:25', NULL, 7, 25, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_detail_transfer_requests`
--

CREATE TABLE `inv_detail_transfer_requests` (
  `id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '0',
  `quantitySend` int NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `transferRequestId` int DEFAULT NULL,
  `itemId` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `inv_detail_transfer_requests`
--

INSERT INTO `inv_detail_transfer_requests` (`id`, `quantity`, `quantitySend`, `created_at`, `updated_at`, `deleted_at`, `transferRequestId`, `itemId`) VALUES
(1, 9, 2, '2026-02-12 21:44:24', '2026-02-13 13:58:37', NULL, 1, 11),
(2, 10, 10, '2026-02-12 21:44:25', '2026-02-13 14:10:52', NULL, 1, 17),
(3, 5, 2, '2026-02-12 21:44:25', '2026-02-13 14:12:25', NULL, 1, 25);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_image_gallery`
--

CREATE TABLE `inv_image_gallery` (
  `id` int NOT NULL,
  `itemId` int DEFAULT NULL,
  `img_path` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `type` enum('PRINCIPAL','GALERIA','PDF') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'PRINCIPAL',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `inv_image_gallery`
--

INSERT INTO `inv_image_gallery` (`id`, `itemId`, `img_path`, `type`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 11, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/6Z0b14RGAmPWf81LW3keA5UI7iZWYBkDbmEqNDxM.jpg', 'GALERIA', '2025-11-26 19:18:22', '2025-11-26 19:20:45', '2025-11-26 19:20:45'),
(3, 11, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/mpV4j0BkIV9vN9fuq0Dc4mN6bqkeSXYGaCxFp0bh.jpg', 'GALERIA', '2025-11-26 19:18:23', '2025-11-26 20:27:00', '2025-11-26 20:27:00'),
(4, 11, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/yTvg5ODNJAx1Ie5ojf5il4UhEYISf2D3mgpVH16N.jpg', 'GALERIA', '2025-11-26 19:18:23', '2025-11-26 20:26:57', '2025-11-26 20:26:57'),
(5, 11, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/znlAsuCiwWKaQ8CcE3Q5l4o4YGxmcc8EickUBHki.jpg', 'GALERIA', '2025-11-26 19:18:23', '2025-11-26 20:26:36', '2025-11-26 20:26:36'),
(6, 11, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/2Mg1AFnSTHR4mE5jtF0lRPuQKq3gjiSslo1bXoPH.jpg', 'GALERIA', '2025-11-26 19:18:24', '2025-11-26 19:18:24', NULL),
(7, 11, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/Fs8R18Eh15gFeT4XIkVif82v8NBMm5du7G5EEvEm.jpg', 'GALERIA', '2025-11-26 19:18:24', '2025-11-26 19:18:24', NULL),
(8, 11, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/Tr0d4foMQgqgWhEgVClwCLk7lXjdkMzJdJC8m57p.jpg', 'PRINCIPAL', '2025-11-26 19:25:43', '2025-11-26 19:25:43', NULL),
(9, 11, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/4nf14KORDNDDO0Qy8b3ziKji51juUoQYfqIXLGgk.jpg', 'GALERIA', '2025-11-26 19:28:43', '2025-11-26 19:28:43', NULL),
(11, 3, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/aGs5YOwx60rJHaDQLc15AIFSastk5TL8mj8T9xTO.jpg', 'GALERIA', '2025-11-26 19:44:32', '2025-11-26 19:44:32', NULL),
(12, 3, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/V95YnwTTsWV3E6pOK5B6aQ73zj11xJ4hgqKUSQiD.webp', 'GALERIA', '2025-11-26 19:44:32', '2025-11-26 19:44:32', NULL),
(13, 3, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/0vwLXtTmjYxGW2fM7XeLvUOD56B2oHKIOiWMjPf2.webp', 'GALERIA', '2025-11-26 19:44:32', '2025-11-26 19:44:32', NULL),
(14, 3, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/Hn8ukFDnrgiMmT3D5SUf4YqSkaCDExn9J3dnrnau.webp', 'GALERIA', '2025-11-26 19:44:32', '2025-11-26 19:44:32', NULL),
(15, 3, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/CPYPfKxyUFyqGPF4q6RkY5fv7mK4n028fxEiMBjr.webp', 'PRINCIPAL', '2025-11-26 19:45:02', '2025-11-26 19:45:02', NULL),
(16, 5, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/6MNk966ahcdyj4COMUjsblV08A7It8a5ISZIbNkk.jpg', 'PRINCIPAL', '2025-11-26 20:27:20', '2025-11-26 20:27:20', NULL),
(17, 5, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/aJEy2xhoJ0g6u1aVyhJq09AakSEjVM1CFS9ffvG3.jpg', 'GALERIA', '2025-11-26 20:28:20', '2025-11-26 20:28:20', NULL),
(18, 5, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/JOGgTC0fzdBgaepVT8DypcwmldrjVZAKUGthMUC8.webp', 'GALERIA', '2025-11-26 20:28:20', '2025-11-26 20:28:20', NULL),
(19, 5, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/q5aDPPfWlDJDUrMqFawniHAP0pfnuSASmc5Wly3W.webp', 'GALERIA', '2025-11-26 20:28:21', '2025-11-26 20:28:21', NULL),
(20, 5, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/0zQuXFChDOVO7RUX2VNC6aAI0kNp19lVqBMATi07.webp', 'GALERIA', '2025-11-26 20:28:21', '2025-11-26 20:28:21', NULL),
(21, 1060, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/DlSw1QOcnYYzy5Ot9xYtjo1GuCvLJidymJt85xVA.jpg', 'PRINCIPAL', '2025-12-01 16:30:05', '2025-12-01 16:30:05', NULL),
(22, 30, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/K5fiJ1e3RywaNWGA41yeNqfjIJ0lkXzeSJDkIRoU.png', 'PRINCIPAL', '2025-12-01 19:17:15', '2025-12-01 19:17:15', NULL),
(23, 30, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/OPVqWPgiqxbKKUmvwgbbVkqZoFyJlVjUUjixlR4t.png', 'GALERIA', '2025-12-01 19:17:49', '2025-12-01 19:17:49', NULL),
(24, 30, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/yoqsNVkHpJgazT5zekkxnUnNrP7jx30oPemrZ2Co.png', 'GALERIA', '2025-12-01 19:17:49', '2025-12-01 19:17:49', NULL),
(25, 30, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/80c1iD72HjlqdFueInggUeMbWA04hDdAHg64Nn0T.png', 'GALERIA', '2025-12-01 19:17:49', '2025-12-01 19:17:49', NULL),
(26, 30, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/XGZqZX3GMB9m48rx3qujDg1sNAO5OZre7VVO0oRT.png', 'GALERIA', '2025-12-01 19:17:49', '2025-12-01 19:17:49', NULL),
(27, 28, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/84xPro8ZbxrEvuxkxIQ1M2f9zzNc53WPAMLrlFM9.png', 'GALERIA', '2025-12-02 13:14:58', '2026-03-06 17:03:58', '2026-03-06 17:03:58'),
(28, 28, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/7yWuSWo0oRSPOqjpIeLhsTX10zuRHAbhbbFNPUZT.png', 'GALERIA', '2025-12-02 13:15:24', '2026-03-06 17:03:50', '2026-03-06 17:03:50'),
(29, 28, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/F1CPSGPgEIVYJH6pgdMcnXkLpDATmNZB8r9SUhE6.png', 'GALERIA', '2025-12-02 13:15:24', '2025-12-02 13:15:24', NULL),
(30, 28, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/cipPILpf6LT1MDZ4YZCWwnIMeLc9gVxProKzf7XR.png', 'GALERIA', '2025-12-02 13:15:24', '2026-03-06 17:04:40', NULL),
(31, 413, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/w5Ef2fkP0Zfjs0bJv81yIuVux7mp9xM20e42XEzZ.jpg', 'PRINCIPAL', '2026-01-20 16:24:04', '2026-01-20 16:24:04', NULL),
(32, 413, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/t0DUmwl4X4Bmto0TNM4rV6Pu0EmAfN69padR8gDX.jpg', 'GALERIA', '2026-01-20 16:25:20', '2026-01-20 16:25:20', NULL),
(33, 413, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/St3Td5lVqL1bni9wMlj1UsAHsgF7zuFI4ZifSTww.jpg', 'GALERIA', '2026-01-20 16:25:30', '2026-01-20 16:25:30', NULL),
(34, 18, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/0awvxL5I6paKZUVfkxwSR3wTagk4KH2HZjYH6NWN.pdf', 'PDF', '2026-02-23 16:45:46', '2026-02-23 18:04:10', NULL),
(35, 21, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/pajLz8x1aX2ksGeEVtyh3wjzwXUS7h51EWQLZt2Q.pdf', 'PDF', '2026-02-23 19:21:27', '2026-02-23 19:21:27', NULL),
(36, 27, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/FNccIZ0iJRxXnFcg3CKqL4sE3hZBLt66JFkgPHwE.pdf', 'PDF', '2026-02-27 20:25:50', '2026-02-27 20:25:50', NULL),
(37, 27, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/23g3AtCIE9jStohB1jjcqg1kz5lKCj4MRvC7gzoI.jpg', 'GALERIA', '2026-02-27 21:07:51', '2026-02-27 21:07:51', NULL),
(38, 27, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/CnI6d1zyblP7E53S1K9I8c6XljUAZC26fPG53ey0.webp', 'GALERIA', '2026-02-27 21:35:13', '2026-02-27 21:35:13', NULL),
(39, 1113, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/aZo2yOffJbs15AX19YdrS8LvIM8ZYrBqwO4hNBEw.pdf', 'PDF', '2026-03-02 18:27:40', '2026-03-02 18:27:40', NULL),
(40, 1113, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/9FIn33cyVjKha324QU2PTlc15FYLkBcswFZrg07s.jpg', 'GALERIA', '2026-03-02 19:26:09', '2026-03-02 19:26:09', NULL),
(41, 18, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/5fEt6vzer82prUDxNXGvzDHkgzPyNYrpCmNznHCT.pdf', 'PDF', '2026-03-02 19:38:14', '2026-03-02 19:38:14', NULL),
(42, 1062, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/MPtCMbyVLs6NFgSjHyU5poi1aMXEkJMf8kRoISyf.pdf', 'PDF', '2026-03-02 19:54:04', '2026-03-02 19:54:04', NULL),
(43, 1119, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/SKQ9qPwqnN0uCfHlUjIK7EBIBKBUFZPeniJGzHuD.png', 'GALERIA', '2026-03-05 14:55:09', '2026-03-05 15:24:19', NULL),
(44, 1119, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/Uq1PLEcex7jCv4wttgnQ5PU8DJuFbaSuN3vlML9A.webp', 'GALERIA', '2026-03-05 15:02:11', '2026-03-05 20:35:05', NULL),
(45, 1119, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/5hpau53oohsT6M32EnH2TVtezeoKmbbVXWvjJrEO.png', 'GALERIA', '2026-03-05 15:02:11', '2026-03-05 15:02:11', NULL),
(46, 1119, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/Ro4LgBwNHfhz5LXaIIJUOIlWM1JwFMMVWaVpMCVK.png', 'GALERIA', '2026-03-05 15:02:11', '2026-03-05 15:02:11', NULL),
(47, 1119, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/DKlNljn8U6PmFGp7QiwLURiOe7av6DwCVVdWZ3BP.png', 'GALERIA', '2026-03-05 15:02:11', '2026-03-05 15:02:11', NULL),
(48, 1119, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/xPB1A4cf22xPsrtRaMWeEu1rPoGby9oDVAu8rV43.png', 'GALERIA', '2026-03-05 15:02:12', '2026-03-16 20:29:16', '2026-03-16 20:29:16'),
(49, 1119, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/wxmxqf1HBaCcmKyIuwlyslE3soBih5amEzt3bDuK.jpg', 'GALERIA', '2026-03-05 15:02:12', '2026-03-16 20:35:58', '2026-03-16 20:35:58'),
(50, 1119, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/xEr8phif5PU5osjYKGHtGNX2kO2cG66mtiYXtwRi.jpg', 'PRINCIPAL', '2026-03-05 15:24:19', '2026-03-05 15:24:19', NULL),
(51, 28, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/DkbCXuo515h9Aw59efCRPg783hou2dFliFU9XhJI.jpg', 'GALERIA', '2026-03-06 17:02:31', '2026-03-06 17:02:31', NULL),
(52, 28, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/iHyTWLX55lsQbl6HszCAWZCheCAXauReGngL8jS8.jpg', 'GALERIA', '2026-03-06 17:02:31', '2026-03-06 17:02:31', NULL),
(53, 28, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/R04nEmRkORHjVUo9Ng8JdiMj63Md6TuQ7ODs5Wcn.jpg', 'GALERIA', '2026-03-06 17:02:31', '2026-03-06 17:02:31', NULL),
(54, 28, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/abm5nkR4ly4CEwhN247JVeGaDTcyy56CPS22YhYr.png', 'PRINCIPAL', '2026-03-06 17:04:40', '2026-03-06 17:04:40', NULL),
(55, 28, 'items/8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a/K8fNiRpl8MAYfvBus0mBxHi1VCmQScL7ds0V6vj0.pdf', 'PDF', '2026-03-09 20:19:09', '2026-03-09 20:19:09', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_inventory_adjustments`
--

CREATE TABLE `inv_inventory_adjustments` (
  `id` int NOT NULL,
  `date` datetime NOT NULL,
  `observations` text NOT NULL,
  `type` varchar(255) NOT NULL,
  `status` tinyint DEFAULT '1',
  `supplier` int NOT NULL DEFAULT '0',
  `api_data_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `storeId` int DEFAULT '1',
  `reasonId` int DEFAULT NULL,
  `consecutive` int NOT NULL,
  `userId` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `inv_inventory_adjustments`
--

INSERT INTO `inv_inventory_adjustments` (`id`, `date`, `observations`, `type`, `status`, `supplier`, `api_data_id`, `created_at`, `updated_at`, `deleted_at`, `storeId`, `reasonId`, `consecutive`, `userId`) VALUES
(1, '2025-12-01 00:00:00', '', 'entrada', 1, 0, NULL, '2025-12-01 21:44:47', '2025-12-01 21:44:47', NULL, NULL, 1, 1, 8),
(2, '2025-12-03 00:00:00', 'pruebas observaciones', 'entrada', 1, 0, NULL, '2025-12-03 13:56:39', '2025-12-03 13:56:39', NULL, NULL, 5, 1, 8),
(3, '2026-01-20 00:00:00', '', 'entrada', 1, 0, NULL, '2026-01-20 17:08:11', '2026-01-20 17:08:11', NULL, 2, 2, 1, 8),
(4, '2026-01-20 00:00:00', '', 'entrada', 1, 23, NULL, '2026-01-20 21:49:06', '2026-01-20 21:49:06', NULL, 1, 1, 1, 8),
(5, '2026-01-20 00:00:00', '', 'entrada', 1, 23, NULL, '2026-01-20 21:52:22', '2026-01-20 21:52:22', NULL, 2, 1, 2, 8),
(6, '2026-01-20 00:00:00', '', 'entrada', 1, 23, NULL, '2026-01-20 21:59:12', '2026-01-20 21:59:12', NULL, 2, 1, 3, 8),
(7, '2026-01-26 00:00:00', '', 'entrada', 1, 4, NULL, '2026-01-26 13:21:27', '2026-01-26 13:21:27', NULL, 1, 1, 2, 8),
(8, '2026-01-26 00:00:00', '', 'entrada', 1, 29, NULL, '2026-01-26 18:57:09', '2026-01-26 18:57:09', NULL, 2, 1, 4, 8),
(9, '2026-01-29 00:00:00', 'Ajuste', 'entrada', 1, 0, NULL, '2026-01-29 14:45:28', '2026-01-29 14:45:28', NULL, 4, 2, 1, 125),
(10, '2026-01-29 00:00:00', 'Consumo interno por carros transportadores', 'salida', 1, 0, NULL, '2026-01-29 15:23:23', '2026-01-29 15:23:23', NULL, 4, 6, 1, 125),
(11, '2026-01-29 00:00:00', '', 'entrada', 1, 0, NULL, '2026-01-29 17:21:05', '2026-01-29 17:21:05', NULL, 3, 5, 1, 8),
(12, '2026-02-01 00:00:00', '', 'entrada', 1, 4, NULL, '2026-02-01 22:31:18', '2026-02-01 22:31:18', NULL, 1, 1, 3, 153),
(13, '2026-02-01 00:00:00', '', 'entrada', 1, 0, NULL, '2026-02-01 22:36:57', '2026-02-01 22:36:57', NULL, 1, 2, 4, 153),
(14, '2026-02-10 00:00:00', 'AJUSTE', 'entrada', 1, 0, NULL, '2026-02-10 21:09:32', '2026-02-10 21:09:32', NULL, 6, 2, 1, 154),
(15, '2026-02-11 00:00:00', 'Ajuste.', 'entrada', 1, 0, NULL, '2026-02-11 13:55:07', '2026-02-11 13:55:07', NULL, 7, 2, 1, 154),
(16, '2026-02-11 00:00:00', '', 'entrada', 1, 0, NULL, '2026-02-11 17:47:41', '2026-02-11 17:47:41', NULL, 6, 2, 2, 154),
(17, '2026-02-18 00:00:00', '', 'entrada', 1, 23, NULL, '2026-02-18 19:02:08', '2026-02-18 19:02:08', NULL, 1, 1, 5, 8),
(18, '2026-02-18 00:00:00', '', 'entrada', 1, 29, NULL, '2026-02-18 19:23:57', '2026-02-18 19:23:57', NULL, 1, 1, 6, 8),
(19, '2026-02-18 00:00:00', '', 'entrada', 1, 0, '187', '2026-02-18 20:29:56', '2026-02-18 20:29:56', NULL, 1, 2, 7, 8),
(20, '2026-02-18 00:00:00', '', 'entrada', 1, 0, '188', '2026-02-18 20:35:07', '2026-02-18 20:35:07', NULL, 1, 2, 8, 8),
(21, '2026-02-18 00:00:00', 'salida', 'salida', 0, 0, '189', '2026-02-18 20:48:03', '2026-02-18 20:49:14', NULL, 1, 3, 1, 8),
(22, '2026-02-18 00:00:00', 'observ', 'entrada', 1, 0, NULL, '2026-02-18 21:05:01', '2026-02-18 21:05:01', NULL, 1, 2, 9, 8),
(23, '2026-03-09 00:00:00', 'ajuste', 'entrada', 1, 0, NULL, '2026-03-09 16:04:43', '2026-03-09 16:04:43', NULL, 1, 2, 10, 8),
(24, '2026-03-09 00:00:00', 'insumo', 'entrada', 1, 23, NULL, '2026-03-09 17:47:39', '2026-03-09 17:47:39', NULL, 1, 1, 11, 8);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_inventory_count`
--

CREATE TABLE `inv_inventory_count` (
  `id` int NOT NULL,
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0-Pendiente, 1-Registrado, 2- Anulado',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `warehouseId` int DEFAULT NULL,
  `consecutive` int NOT NULL,
  `userId` int NOT NULL,
  `itemId` int DEFAULT NULL,
  `quantityDig` int NOT NULL,
  `quantityCal` int NOT NULL,
  `quantityInv` int NOT NULL,
  `quantityTotal` int NOT NULL,
  `unitMeasurementId` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_items`
--

CREATE TABLE `inv_items` (
  `id` int NOT NULL,
  `api_data_id` int DEFAULT NULL COMMENT 'id de integracion',
  `categoryId` int DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `internal_code` varchar(100) NOT NULL,
  `sku` varchar(255) NOT NULL,
  `description` text,
  `type` enum('COMBO','COMPRA NACIONAL','IMPORTADO','PRODUCIDO','INSUMO') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `taxId` int NOT NULL DEFAULT '2',
  `commandId` int DEFAULT NULL,
  `brandId` int DEFAULT NULL,
  `houseId` int DEFAULT NULL,
  `inventoriable` tinyint NOT NULL DEFAULT '1' COMMENT '1=SI 0=NO',
  `purchasing_unit` int DEFAULT '0',
  `consumption_unit` int DEFAULT '0',
  `handles_serial` tinyint NOT NULL DEFAULT '0',
  `status` tinyint NOT NULL DEFAULT '1',
  `generic` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1=SI 0=NO',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `inv_items`
--

INSERT INTO `inv_items` (`id`, `api_data_id`, `categoryId`, `name`, `internal_code`, `sku`, `description`, `type`, `taxId`, `commandId`, `brandId`, `houseId`, `inventoriable`, `purchasing_unit`, `consumption_unit`, `handles_serial`, `status`, `generic`, `created_at`, `updated_at`, `deleted_at`) VALUES
(3, NULL, 6, 'BATMAN FIGURA PEQUEÑA', 'BT101', 'BT001', 'BATMAN FIGURA ', 'PRODUCIDO', 2, 1, 1, 1, 1, 4, 35, 1, 1, 0, '2025-11-12 19:32:29', '2025-11-21 17:14:06', NULL),
(4, NULL, 2, 'SUPERMAN FIGURA', 'SP001', 'SP001', 'SUPERMAN', 'COMPRA NACIONAL', 2, 1, 1, 1, 1, 35, 35, 0, 1, 1, '2025-11-12 20:02:33', '2025-11-13 16:55:57', '2025-11-13 16:55:57'),
(5, NULL, 3, 'BOTAS NEGRAS', 'BT002', 'BT002', 'BOTAS NEGRAS TIMBERLAND', 'COMPRA NACIONAL', 2, 2, 2, 2, 1, 12, 35, 0, 1, 0, '2025-11-12 21:09:35', '2025-11-12 21:09:35', NULL),
(6, NULL, 1, 'PARLANTE AZUL', 'PRL023', 'PRL023', 'PARLANTE AZUL', 'COMPRA NACIONAL', 2, 1, 1, 1, 1, 7, 35, 0, 1, 0, '2025-11-13 20:17:34', '2025-11-13 20:17:34', NULL),
(9, NULL, 2, 'CAJA DE POKER', 'CDP078', 'CDP078', 'descrip', 'COMPRA NACIONAL', 2, 1, 1, 1, 1, 35, 35, 0, 1, 1, '2025-11-13 21:24:21', '2025-11-13 21:24:21', NULL),
(10, NULL, 5, 'SALERO', 'SLR873', 'SLR873', 'SALERO ', 'COMPRA NACIONAL', 2, 3, 1, 1, 1, 17, 35, 0, 1, 1, '2025-11-13 21:25:44', '2025-11-13 21:25:44', NULL),
(11, NULL, 5, 'ALMOHADA', 'ALM001', 'ALM001', 'ALMOHADA', 'COMPRA NACIONAL', 2, 1, 1, 2, 1, 35, 35, 0, 1, 1, '2025-11-14 19:44:39', '2025-11-26 20:54:21', NULL),
(12, NULL, 3, 'TENIS NIKE TALLA 38', 'TN001', 'TN001', 'TENIS NIKE TALLA 38', 'IMPORTADO', 2, 1, 1, 1, 1, 35, 35, 0, 1, 1, '2025-11-20 14:30:53', '2025-11-20 14:30:53', NULL),
(13, NULL, 3, 'TENIS NIKE TALLA 39', 'TN002', 'TN002', 'TENIS NIKE TALLA 39', 'IMPORTADO', 2, 2, 2, 1, 1, 10, 35, 0, 1, 1, '2025-11-20 14:32:27', '2025-11-20 14:32:27', NULL),
(14, NULL, 3, 'TENIS NIKE TALLA 40', 'TN003', 'TN003', 'TENIS NIKE TALLA 40', 'IMPORTADO', 2, 2, 4, 2, 1, 1, 35, 0, 1, 1, '2025-11-20 15:10:05', '2025-11-20 15:10:05', NULL),
(15, NULL, 35, 'DESTORNILLADOR DE ESTRELLA', 'DTE056', 'DTE056', 'DESTORNILLADOR', 'COMPRA NACIONAL', 2, 4, 1, 1, 1, 35, 35, 0, 1, 1, '2025-11-21 17:21:21', '2025-11-21 17:21:21', NULL),
(16, NULL, 37, 'EL PRINCIPITO', '5555', '5555', 'LIBRO INFANTIL', 'COMPRA NACIONAL', 2, 1, 1, 1, 1, 35, 35, 0, 1, 1, '2025-11-21 17:28:16', '2025-11-21 17:28:16', NULL),
(17, NULL, 1, 'CARGADOR USB RAPIDO', 'CG001', 'CG001', 'CARGADOR 20W', 'COMPRA NACIONAL', 2, 1, 1, 1, 1, 7, 35, 0, 1, 0, '2025-11-27 14:33:18', NULL, NULL),
(18, NULL, 1, 'AUDIFONOS BLUETOOTH', 'AU001', 'AU001', 'AUDIFONOS INALAMBRICOS', 'IMPORTADO', 2, 1, 1, 1, 1, 20, 35, 0, 1, 0, '2025-11-27 14:33:18', NULL, NULL),
(19, NULL, 2, 'MUÑECO SPIDERMAN', 'MJ001', 'MJ001', 'FIGURA DE ACCIÓN', 'COMPRA NACIONAL', 2, 1, 1, 1, 1, 4, 35, 0, 1, 0, '2025-11-27 14:33:18', NULL, NULL),
(20, NULL, 2, 'ROMPECABEZAS 100 PIEZAS', 'RP001', 'RP001', 'ROMPECABEZAS INFANTIL', 'COMPRA NACIONAL', 2, 1, 1, 1, 1, 22, 35, 0, 1, 0, '2025-11-27 14:33:18', NULL, NULL),
(21, NULL, 3, 'BOTA DEPORTIVA TALLA 41', 'BT003', 'BT003', 'BOTA DEPORTIVA', 'IMPORTADO', 2, 2, 2, 2, 1, 35, 35, 0, 1, 0, '2025-11-27 14:33:18', NULL, NULL),
(22, NULL, 3, 'TACONES NEGROS T38', 'TC001', 'TC001', 'TACONES NEGROS', 'COMPRA NACIONAL', 2, 1, 1, 1, 1, 12, 35, 0, 1, 0, '2025-11-27 14:33:18', NULL, NULL),
(23, NULL, 5, 'VASO DE VIDRIO', 'VS001', 'VS001', 'VASO TRANSPARENTE', 'COMPRA NACIONAL', 2, 1, 1, 1, 1, 10, 35, 0, 1, 0, '2025-11-27 14:33:18', NULL, NULL),
(24, NULL, 5, 'CUCHILLO DE COCINA', 'CC001', 'CC001', 'CUCHILLO MULTIUSOS', 'COMPRA NACIONAL', 2, 1, 1, 1, 1, 17, 35, 0, 1, 0, '2025-11-27 14:33:18', NULL, NULL),
(25, NULL, 6, 'CUADERNO RAYADO 100H', 'CDR100', 'CDR100', 'CUADERNO ESCOLAR', 'COMPRA NACIONAL', 2, 1, 1, 1, 1, 21, 35, 0, 1, 0, '2025-11-27 14:33:18', NULL, NULL),
(26, NULL, 6, 'MARCADOR PERMANENTE NEGRO', 'MPN001', 'MPN001', 'MARCADOR PUNTA FINA', 'COMPRA NACIONAL', 2, 1, 1, 1, 1, 35, 35, 0, 1, 0, '2025-11-27 14:33:18', NULL, NULL),
(27, NULL, 35, 'LLAVE INGLESA 8\"', 'LI001', 'LI001', 'HERRAMIENTA METÁLICA', 'IMPORTADO', 2, 4, 1, 1, 1, 7, 35, 0, 1, 0, '2025-11-27 14:33:18', NULL, NULL),
(28, NULL, 35, 'ALICATE UNIVERSAL', 'ALU001', 'ALU001', 'ALICATE ANTIDESLIZANTE pa', 'COMPRA NACIONAL', 2, 4, 1, 1, 1, 15, 35, 0, 1, 0, '2025-11-27 14:33:18', '2026-01-22 21:10:14', NULL),
(29, NULL, 36, 'CASCO PARA MOTO', 'CSM001', 'CSM001', 'CASCO CERTIFICADO', 'IMPORTADO', 2, 1, 1, 1, 1, 30, 35, 0, 1, 0, '2025-11-27 14:33:18', NULL, NULL),
(30, NULL, 36, 'ACEITE DE MOTOR 20W50', 'ACM580', 'ACM580', 'LUBRICANTE VEHICULAR', 'COMPRA NACIONAL', 2, 1, 1, 1, 0, 36, 35, 1, 1, 0, '2025-11-27 14:33:18', '2026-01-22 21:06:07', NULL),
(31, NULL, 37, 'HARRY POTTER Y LA PIEDRA FILOSOFAL', 'HP001', 'HP001', 'LIBRO', 'COMPRA NACIONAL', 2, 1, 1, 1, 1, 35, 35, 0, 1, 0, '2025-11-27 14:33:18', NULL, NULL),
(32, NULL, 37, 'DON QUIJOTE DE LA MANCHA', 'DQ001', 'DQ001', 'LIBRO', 'IMPORTADO', 2, 1, 1, 1, 1, 35, 35, 0, 1, 0, '2025-11-27 14:33:18', NULL, NULL),
(33, NULL, 38, 'TERMO ACERO 500ML', 'TRM500', 'TRM500', 'TERMO ACERO INOX', 'COMPRA NACIONAL', 2, 1, 1, 1, 1, 8, 35, 0, 1, 0, '2025-11-27 14:33:18', NULL, NULL),
(34, NULL, 38, 'TERMO ACERO 750ML', 'TRM750', 'TRM750', 'TERMO ACERO INOX', 'COMPRA NACIONAL', 2, 1, 1, 1, 1, 8, 35, 0, 1, 0, '2025-11-27 14:33:18', NULL, NULL),
(35, NULL, 39, 'GAFAS DE SOL NEGRAS', 'GFN001', 'GFN001', 'LENTE UV400', 'IMPORTADO', 2, 1, 1, 1, 1, 25, 35, 0, 1, 0, '2025-11-27 14:33:18', NULL, NULL),
(36, NULL, 39, 'GAFAS DEPORTIVAS', 'GFD001', 'GFD001', 'GAFAS RESISTENTES', 'IMPORTADO', 2, 1, 1, 1, 1, 25, 35, 0, 1, 0, '2025-11-27 14:33:18', NULL, NULL),
(37, NULL, 1, 'Producto 890', 'INT-00890', 'SKU-00890', 'Descripción del producto 890', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 2, 33, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(38, NULL, 1, 'Producto 315', 'INT-00315', 'SKU-00315', 'Descripción del producto 315', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 22, 36, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(39, NULL, 1, 'Producto 470', 'INT-00470', 'SKU-00470', 'Descripción del producto 470', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 24, 25, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(40, NULL, 1, 'Producto 625', 'INT-00625', 'SKU-00625', 'Descripción del producto 625', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 18, 3, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(41, NULL, 1, 'Producto 383', 'INT-00383', 'SKU-00383', 'Descripción del producto 383', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 35, 20, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(42, NULL, 1, 'Producto 851', 'INT-00851', 'SKU-00851', 'Descripción del producto 851', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 16, 25, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(43, NULL, 1, 'Producto 340', 'INT-00340', 'SKU-00340', 'Descripción del producto 340', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 9, 2, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(44, NULL, 1, 'Producto 671', 'INT-00671', 'SKU-00671', 'Descripción del producto 671', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 9, 22, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(45, NULL, 1, 'Producto 699', 'INT-00699', 'SKU-00699', 'Descripción del producto 699', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 16, 27, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(46, NULL, 1, 'Producto 910', 'INT-00910', 'SKU-00910', 'Descripción del producto 910', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 16, 23, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(47, NULL, 1, 'Producto 424', 'INT-00424', 'SKU-00424', 'Descripción del producto 424', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 20, 34, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(48, NULL, 1, 'Producto 106', 'INT-00106', 'SKU-00106', 'Descripción del producto 106', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 4, 36, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(49, NULL, 1, 'Producto 1022', 'INT-001022', 'SKU-00102', 'Descripción del producto 102', 'IMPORTADO', 2, NULL, 8, 5, 1, 2, 4, 0, 1, 0, '2025-11-27 14:37:09', '2026-02-18 20:59:36', NULL),
(50, NULL, 1, 'Producto 982', 'INT-00982', 'SKU-00982', 'Descripción del producto 982', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 33, 31, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(51, NULL, 1, 'Producto 448', 'INT-00448', 'SKU-00448', 'Descripción del producto 448', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 26, 1, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(52, NULL, 1, 'Producto 984', 'INT-00984', 'SKU-00984', 'Descripción del producto 984', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 16, 3, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(53, NULL, 1, 'Producto 534', 'INT-00534', 'SKU-00534', 'Descripción del producto 534', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 26, 17, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(54, NULL, 1, 'Producto 408', 'INT-00408', 'SKU-00408', 'Descripción del producto 408', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 3, 8, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(55, NULL, 1, 'Producto 288', 'INT-00288', 'SKU-00288', 'Descripción del producto 288', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 22, 26, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(56, NULL, 1, 'Producto 49', 'INT-00049', 'SKU-00049', 'Descripción del producto 49', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 1, 14, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(57, NULL, 1, 'Producto 119', 'INT-00119', 'SKU-00119', 'Descripción del producto 119', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 1, 32, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(58, NULL, 1, 'Producto 748', 'INT-00748', 'SKU-00748', 'Descripción del producto 748', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 3, 33, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(59, NULL, 1, 'Producto 356', 'INT-00356', 'SKU-00356', 'Descripción del producto 356', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 16, 36, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(60, NULL, 1, 'Producto 282', 'INT-00282', 'SKU-00282', 'Descripción del producto 282', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 3, 23, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(61, NULL, 1, 'Producto 723', 'INT-00723', 'SKU-00723', 'Descripción del producto 723', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 28, 36, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(62, NULL, 1, 'Producto 315', 'INT-00315', 'SKU-00315', 'Descripción del producto 315', 'COMBO', 2, NULL, NULL, NULL, 1, 16, 6, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(63, NULL, 1, 'Producto 566', 'INT-00566', 'SKU-00566', 'Descripción del producto 566', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 26, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(64, NULL, 1, 'Producto 228', 'INT-00228', 'SKU-00228', 'Descripción del producto 228', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 6, 28, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(65, NULL, 1, 'Producto 773', 'INT-00773', 'SKU-00773', 'Descripción del producto 773', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 18, 21, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(66, NULL, 1, 'Producto 148', 'INT-00148', 'SKU-00148', 'Descripción del producto 148', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 27, 9, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(67, NULL, 1, 'Producto 326', 'INT-00326', 'SKU-00326', 'Descripción del producto 326', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 27, 14, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(68, NULL, 1, 'Producto 724', 'INT-00724', 'SKU-00724', 'Descripción del producto 724', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 13, 36, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(69, NULL, 1, 'Producto 227', 'INT-00227', 'SKU-00227', 'Descripción del producto 227', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 3, 3, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(70, NULL, 1, 'Producto 548', 'INT-00548', 'SKU-00548', 'Descripción del producto 548', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 14, 33, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(71, NULL, 1, 'Producto 479', 'INT-00479', 'SKU-00479', 'Descripción del producto 479', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 25, 7, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(72, NULL, 1, 'Producto 35', 'INT-00035', 'SKU-00035', 'Descripción del producto 35', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 27, 33, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(73, NULL, 1, 'Producto 598', 'INT-00598', 'SKU-00598', 'Descripción del producto 598', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 34, 15, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(74, NULL, 1, 'Producto 946', 'INT-00946', 'SKU-00946', 'Descripción del producto 946', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 26, 26, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(75, NULL, 1, 'Producto 373', 'INT-00373', 'SKU-00373', 'Descripción del producto 373', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 13, 31, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(76, NULL, 1, 'Producto 963', 'INT-00963', 'SKU-00963', 'Descripción del producto 963', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 6, 13, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(77, NULL, 1, 'Producto 435', 'INT-00435', 'SKU-00435', 'Descripción del producto 435', 'COMBO', 2, NULL, NULL, NULL, 1, 22, 15, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(78, NULL, 1, 'Producto 72', 'INT-00072', 'SKU-00072', 'Descripción del producto 72', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 24, 13, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(79, NULL, 1, 'Producto 915', 'INT-00915', 'SKU-00915', 'Descripción del producto 915', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 35, 31, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(80, NULL, 1, 'Producto 774', 'INT-00774', 'SKU-00774', 'Descripción del producto 774', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 33, 11, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(81, NULL, 1, 'Producto 5', 'INT-00005', 'SKU-00005', 'Descripción del producto 5', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 8, 11, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(82, NULL, 1, 'Producto 388', 'INT-00388', 'SKU-00388', 'Descripción del producto 388', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 7, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(83, NULL, 1, 'Producto 923', 'INT-00923', 'SKU-00923', 'Descripción del producto 923', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 13, 3, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(84, NULL, 1, 'Producto 750', 'INT-00750', 'SKU-00750', 'Descripción del producto 750', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 25, 24, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(85, NULL, 1, 'Producto 329', 'INT-00329', 'SKU-00329', 'Descripción del producto 329', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 10, 6, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(86, NULL, 1, 'Producto 716', 'INT-00716', 'SKU-00716', 'Descripción del producto 716', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 8, 29, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(87, NULL, 1, 'Producto 159', 'INT-00159', 'SKU-00159', 'Descripción del producto 159', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 1, 34, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(88, NULL, 1, 'Producto 276', 'INT-00276', 'SKU-00276', 'Descripción del producto 276', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 31, 9, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(89, NULL, 1, 'Producto 407', 'INT-00407', 'SKU-00407', 'Descripción del producto 407', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 11, 8, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(90, NULL, 1, 'Producto 365', 'INT-00365', 'SKU-00365', 'Descripción del producto 365', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 11, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(91, NULL, 1, 'Producto 982', 'INT-00982', 'SKU-00982', 'Descripción del producto 982', 'COMBO', 2, NULL, NULL, NULL, 1, 27, 24, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(92, NULL, 1, 'Producto 245', 'INT-00245', 'SKU-00245', 'Descripción del producto 245', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 7, 26, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(93, NULL, 1, 'Producto 798', 'INT-00798', 'SKU-00798', 'Descripción del producto 798', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 2, 24, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(94, NULL, 1, 'Producto 186', 'INT-00186', 'SKU-00186', 'Descripción del producto 186', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 15, 31, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(95, NULL, 1, 'Producto 931', 'INT-00931', 'SKU-00931', 'Descripción del producto 931', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 5, 9, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(96, NULL, 1, 'Producto 521', 'INT-00521', 'SKU-00521', 'Descripción del producto 521', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 24, 29, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(97, NULL, 1, 'Producto 450', 'INT-00450', 'SKU-00450', 'Descripción del producto 450', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 12, 6, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(98, NULL, 1, 'Producto 691', 'INT-00691', 'SKU-00691', 'Descripción del producto 691', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 4, 33, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(99, NULL, 1, 'Producto 953', 'INT-00953', 'SKU-00953', 'Descripción del producto 953', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 26, 32, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(100, NULL, 1, 'Producto 391', 'INT-00391', 'SKU-00391', 'Descripción del producto 391', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 12, 21, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(101, NULL, 1, 'Producto 236', 'INT-00236', 'SKU-00236', 'Descripción del producto 236', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 12, 22, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(102, NULL, 1, 'Producto 658', 'INT-00658', 'SKU-00658', 'Descripción del producto 658', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 22, 33, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(103, NULL, 1, 'Producto 792', 'INT-00792', 'SKU-00792', 'Descripción del producto 792', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 24, 33, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(104, NULL, 1, 'Producto 446', 'INT-00446', 'SKU-00446', 'Descripción del producto 446', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 8, 31, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(105, NULL, 1, 'Producto 94', 'INT-00094', 'SKU-00094', 'Descripción del producto 94', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 20, 26, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(106, NULL, 1, 'Producto 303', 'INT-00303', 'SKU-00303', 'Descripción del producto 303', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 3, 8, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(107, NULL, 1, 'Producto 771', 'INT-00771', 'SKU-00771', 'Descripción del producto 771', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 18, 17, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(108, NULL, 1, 'Producto 164', 'INT-00164', 'SKU-00164', 'Descripción del producto 164', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 36, 36, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(109, NULL, 1, 'Producto 743', 'INT-00743', 'SKU-00743', 'Descripción del producto 743', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 6, 18, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(110, NULL, 1, 'Producto 264', 'INT-00264', 'SKU-00264', 'Descripción del producto 264', 'COMBO', 2, NULL, NULL, NULL, 1, 18, 1, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(111, NULL, 1, 'Producto 848', 'INT-00848', 'SKU-00848', 'Descripción del producto 848', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 4, 14, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(112, NULL, 1, 'Producto 567', 'INT-00567', 'SKU-00567', 'Descripción del producto 567', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 28, 32, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(113, NULL, 1, 'Producto 773', 'INT-00773', 'SKU-00773', 'Descripción del producto 773', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 1, 20, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(114, NULL, 1, 'Producto 423', 'INT-00423', 'SKU-00423', 'Descripción del producto 423', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 15, 6, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(115, NULL, 1, 'Producto 272', 'INT-00272', 'SKU-00272', 'Descripción del producto 272', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 31, 29, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(116, NULL, 1, 'Producto 736', 'INT-00736', 'SKU-00736', 'Descripción del producto 736', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 18, 23, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(117, NULL, 1, 'Producto 102', 'INT-00102', 'SKU-00102', 'Descripción del producto 102', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 18, 16, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(118, NULL, 1, 'Producto 839', 'INT-00839', 'SKU-00839', 'Descripción del producto 839', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 15, 25, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(119, NULL, 1, 'Producto 199', 'INT-00199', 'SKU-00199', 'Descripción del producto 199', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 31, 2, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(120, NULL, 1, 'Producto 264', 'INT-00264', 'SKU-00264', 'Descripción del producto 264', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 29, 26, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(121, NULL, 1, 'Producto 670', 'INT-00670', 'SKU-00670', 'Descripción del producto 670', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 4, 23, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(122, NULL, 1, 'Producto 568', 'INT-00568', 'SKU-00568', 'Descripción del producto 568', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 13, 36, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(123, NULL, 1, 'Producto 73', 'INT-00073', 'SKU-00073', 'Descripción del producto 73', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 28, 30, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(124, NULL, 1, 'Producto 81', 'INT-00081', 'SKU-00081', 'Descripción del producto 81', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 4, 16, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(125, NULL, 1, 'Producto 608', 'INT-00608', 'SKU-00608', 'Descripción del producto 608', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 10, 9, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(126, NULL, 1, 'Producto 261', 'INT-00261', 'SKU-00261', 'Descripción del producto 261', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 29, 17, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(127, NULL, 1, 'Producto 706', 'INT-00706', 'SKU-00706', 'Descripción del producto 706', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 17, 1, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(128, NULL, 1, 'Producto 163', 'INT-00163', 'SKU-00163', 'Descripción del producto 163', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 35, 6, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(129, NULL, 1, 'Producto 197', 'INT-00197', 'SKU-00197', 'Descripción del producto 197', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 34, 36, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(130, NULL, 1, 'Producto 110', 'INT-00110', 'SKU-00110', 'Descripción del producto 110', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 11, 32, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(131, NULL, 1, 'Producto 163', 'INT-00163', 'SKU-00163', 'Descripción del producto 163', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 5, 5, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(132, NULL, 1, 'Producto 473', 'INT-00473', 'SKU-00473', 'Descripción del producto 473', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 25, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(133, NULL, 1, 'ESTUCHE GAFAS COLOR AZUL', 'INT-00121', 'SKU-00121', 'ESTUCHE GAFAS COLOR AZUL', 'IMPORTADO', 2, NULL, 4, 2, 1, 20, 10, 0, 1, 0, '2025-11-27 14:37:09', '2026-03-04 13:51:40', NULL),
(134, NULL, 1, 'Producto 25', 'INT-00025', 'SKU-00025', 'Descripción del producto 25', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 23, 18, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(135, NULL, 1, 'Producto 236', 'INT-00236', 'SKU-00236', 'Descripción del producto 236', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 8, 36, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(136, NULL, 1, 'Producto 622', 'INT-00622', 'SKU-00622', 'Descripción del producto 622', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 25, 21, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(137, NULL, 1, 'Producto 116', 'INT-00116', 'SKU-00116', 'Descripción del producto 116', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 31, 6, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(138, NULL, 1, 'Producto 613', 'INT-00613', 'SKU-00613', 'Descripción del producto 613', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 5, 29, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(139, NULL, 1, 'Producto 271', 'INT-00271', 'SKU-00271', 'Descripción del producto 271', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 12, 1, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(140, NULL, 1, 'Producto 170', 'INT-00170', 'SKU-00170', 'Descripción del producto 170', 'COMBO', 2, NULL, NULL, NULL, 1, 7, 10, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(141, NULL, 1, 'Producto 464', 'INT-00464', 'SKU-00464', 'Descripción del producto 464', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 18, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(142, NULL, 1, 'Producto 820', 'INT-00820', 'SKU-00820', 'Descripción del producto 820', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 23, 27, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(143, NULL, 1, 'Producto 286', 'INT-00286', 'SKU-00286', 'Descripción del producto 286', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 12, 33, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(144, NULL, 1, 'Producto 53', 'INT-00053', 'SKU-00053', 'Descripción del producto 53', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 24, 27, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(145, NULL, 1, 'Producto 658', 'INT-00658', 'SKU-00658', 'Descripción del producto 658', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 26, 21, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(146, NULL, 1, 'Producto 570', 'INT-00570', 'SKU-00570', 'Descripción del producto 570', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 30, 28, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(147, NULL, 1, 'Producto 57', 'INT-00057', 'SKU-00057', 'Descripción del producto 57', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 10, 27, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(148, NULL, 1, 'Producto 976', 'INT-00976', 'SKU-00976', 'Descripción del producto 976', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 12, 8, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(149, NULL, 1, 'Producto 163', 'INT-00163', 'SKU-00163', 'Descripción del producto 163', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 29, 27, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(150, NULL, 1, 'Producto 444', 'INT-00444', 'SKU-00444', 'Descripción del producto 444', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 26, 21, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(151, NULL, 1, 'Producto 347', 'INT-00347', 'SKU-00347', 'Descripción del producto 347', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 18, 31, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(152, NULL, 1, 'Producto 571', 'INT-00571', 'SKU-00571', 'Descripción del producto 571', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 23, 18, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(153, NULL, 1, 'Producto 174', 'INT-00174', 'SKU-00174', 'Descripción del producto 174', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 3, 11, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(154, NULL, 1, 'Producto 273', 'INT-00273', 'SKU-00273', 'Descripción del producto 273', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 9, 19, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(155, NULL, 1, 'Producto 995', 'INT-00995', 'SKU-00995', 'Descripción del producto 995', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 3, 5, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(156, NULL, 1, 'Producto 765', 'INT-00765', 'SKU-00765', 'Descripción del producto 765', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 31, 23, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(157, NULL, 1, 'Producto 542', 'INT-00542', 'SKU-00542', 'Descripción del producto 542', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 11, 12, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(158, NULL, 1, 'Producto 181', 'INT-00181', 'SKU-00181', 'Descripción del producto 181', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 29, 10, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(159, NULL, 1, 'Producto 20', 'INT-00020', 'SKU-00020', 'Descripción del producto 20', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 21, 16, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(160, NULL, 1, 'Producto 624', 'INT-00624', 'SKU-00624', 'Descripción del producto 624', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 22, 8, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(161, NULL, 1, 'Producto 558', 'INT-00558', 'SKU-00558', 'Descripción del producto 558', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 20, 10, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(162, NULL, 1, 'Producto 338', 'INT-00338', 'SKU-00338', 'Descripción del producto 338', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 26, 26, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(163, NULL, 1, 'Producto 753', 'INT-00753', 'SKU-00753', 'Descripción del producto 753', 'COMBO', 2, NULL, NULL, NULL, 1, 3, 12, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(164, NULL, 1, 'Producto 135', 'INT-00135', 'SKU-00135', 'Descripción del producto 135', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 11, 19, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(165, NULL, 1, 'Producto 363', 'INT-00363', 'SKU-00363', 'Descripción del producto 363', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 13, 7, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(166, NULL, 1, 'Producto 379', 'INT-00379', 'SKU-00379', 'Descripción del producto 379', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 35, 21, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(167, NULL, 1, 'Producto 953', 'INT-00953', 'SKU-00953', 'Descripción del producto 953', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 3, 8, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(168, NULL, 1, 'Producto 939', 'INT-00939', 'SKU-00939', 'Descripción del producto 939', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 12, 14, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(169, NULL, 1, 'Producto 227', 'INT-00227', 'SKU-00227', 'Descripción del producto 227', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 10, 17, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(170, NULL, 1, 'Producto 405', 'INT-00405', 'SKU-00405', 'Descripción del producto 405', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 34, 7, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(171, NULL, 1, 'Producto 338', 'INT-00338', 'SKU-00338', 'Descripción del producto 338', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 35, 23, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(172, NULL, 1, 'Producto 728', 'INT-00728', 'SKU-00728', 'Descripción del producto 728', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 27, 17, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(173, NULL, 1, 'Producto 538', 'INT-00538', 'SKU-00538', 'Descripción del producto 538', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 21, 30, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(174, NULL, 1, 'Producto 672', 'INT-00672', 'SKU-00672', 'Descripción del producto 672', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 7, 24, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(175, NULL, 1, 'Producto 336', 'INT-00336', 'SKU-00336', 'Descripción del producto 336', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 16, 34, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(176, NULL, 1, 'Producto 217', 'INT-00217', 'SKU-00217', 'Descripción del producto 217', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 32, 25, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(177, NULL, 1, 'Producto 715', 'INT-00715', 'SKU-00715', 'Descripción del producto 715', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 4, 13, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(178, NULL, 1, 'Producto 864', 'INT-00864', 'SKU-00864', 'Descripción del producto 864', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 8, 18, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(179, NULL, 1, 'Producto 634', 'INT-00634', 'SKU-00634', 'Descripción del producto 634', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 12, 11, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(180, NULL, 1, 'Producto 415', 'INT-00415', 'SKU-00415', 'Descripción del producto 415', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 5, 30, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(181, NULL, 1, 'Producto 104-I', 'INT-0104', 'SKU-0104', 'Descripción del producto 104', 'COMPRA NACIONAL', 2, NULL, 8, 3, 1, 26, 28, 0, 1, 0, '2025-11-27 14:37:09', '2026-02-16 14:39:34', NULL),
(182, NULL, 1, 'Producto 5', 'INT-00005', 'SKU-00005', 'Descripción del producto 5', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 32, 13, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(183, NULL, 1, 'Producto 460', 'INT-00460', 'SKU-00460', 'Descripción del producto 460', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 32, 20, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(184, NULL, 1, 'Producto 254', 'INT-00254', 'SKU-00254', 'Descripción del producto 254', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 33, 22, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(185, NULL, 1, 'Producto 255', 'INT-00255', 'SKU-00255', 'Descripción del producto 255', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 1, 15, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(186, NULL, 1, 'Producto 416', 'INT-00416', 'SKU-00416', 'Descripción del producto 416', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 23, 26, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(187, NULL, 1, 'Producto 244', 'INT-00244', 'SKU-00244', 'Descripción del producto 244', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 23, 15, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(188, NULL, 1, 'Producto 825', 'INT-00825', 'SKU-00825', 'Descripción del producto 825', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 31, 11, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(189, NULL, 1, 'Producto 30', 'INT-00030', 'SKU-00030', 'Descripción del producto 30', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 10, 2, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(190, NULL, 1, 'Producto 214', 'INT-00214', 'SKU-00214', 'Descripción del producto 214', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 14, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(191, NULL, 1, 'Producto 321', 'INT-00321', 'SKU-00321', 'Descripción del producto 321', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 2, 20, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(192, NULL, 1, 'Producto 561', 'INT-00561', 'SKU-00561', 'Descripción del producto 561', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 28, 21, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(193, NULL, 1, 'Producto 3', 'INT-00003', 'SKU-00003', 'Descripción del producto 3', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 10, 2, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(194, NULL, 1, 'Producto 264', 'INT-00264', 'SKU-00264', 'Descripción del producto 264', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 23, 2, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(195, NULL, 1, 'Producto 127', 'INT-00127', 'SKU-00127', 'Descripción del producto 127', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 33, 8, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(196, NULL, 1, 'Producto 629', 'INT-00629', 'SKU-00629', 'Descripción del producto 629', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 36, 16, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(197, NULL, 1, 'Producto 840', 'INT-00840', 'SKU-00840', 'Descripción del producto 840', 'COMBO', 2, NULL, NULL, NULL, 1, 36, 12, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(198, NULL, 1, 'Producto 201', 'INT-00201', 'SKU-00201', 'Descripción del producto 201', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 30, 1, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(199, NULL, 1, 'Producto 937', 'INT-00937', 'SKU-00937', 'Descripción del producto 937', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 25, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(200, NULL, 1, 'Producto 454', 'INT-00454', 'SKU-00454', 'Descripción del producto 454', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 13, 27, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(201, NULL, 1, 'Producto 505', 'INT-00505', 'SKU-00505', 'Descripción del producto 505', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 14, 15, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(202, NULL, 1, 'Producto 920', 'INT-00920', 'SKU-00920', 'Descripción del producto 920', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 17, 12, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(203, NULL, 1, 'Producto 587', 'INT-00587', 'SKU-00587', 'Descripción del producto 587', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 24, 25, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(204, NULL, 1, 'Producto 606', 'INT-00606', 'SKU-00606', 'Descripción del producto 606', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 7, 27, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(205, NULL, 1, 'Producto 951', 'INT-00951', 'SKU-00951', 'Descripción del producto 951', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 7, 17, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(206, NULL, 1, 'Producto 841', 'INT-00841', 'SKU-00841', 'Descripción del producto 841', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 14, 20, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(207, NULL, 1, 'Producto 862', 'INT-00862', 'SKU-00862', 'Descripción del producto 862', 'COMBO', 2, NULL, NULL, NULL, 1, 28, 20, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(208, NULL, 1, 'Producto 726', 'INT-00726', 'SKU-00726', 'Descripción del producto 726', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 26, 31, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(209, NULL, 1, 'Producto 909', 'INT-00909', 'SKU-00909', 'Descripción del producto 909', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 27, 24, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(210, NULL, 1, 'Producto 64', 'INT-00064', 'SKU-00064', 'Descripción del producto 64', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 27, 4, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(211, NULL, 1, 'Producto 300', 'INT-00300', 'SKU-00300', 'Descripción del producto 300', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 3, 8, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(212, NULL, 1, 'Producto 867', 'INT-00867', 'SKU-00867', 'Descripción del producto 867', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 32, 34, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(213, NULL, 1, 'Producto 655', 'INT-00655', 'SKU-00655', 'Descripción del producto 655', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 32, 2, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(214, NULL, 1, 'Producto 121', 'INT-00121', 'SKU-00121', 'Descripción del producto 121', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 32, 13, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(215, NULL, 1, 'Producto 332', 'INT-00332', 'SKU-00332', 'Descripción del producto 332', 'COMBO', 2, NULL, NULL, NULL, 1, 23, 36, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(216, NULL, 1, 'Producto 673', 'INT-00673', 'SKU-00673', 'Descripción del producto 673', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 10, 9, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(217, NULL, 1, 'Producto 990', 'INT-00990', 'SKU-00990', 'Descripción del producto 990', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 22, 3, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(218, NULL, 1, 'Producto 532', 'INT-00532', 'SKU-00532', 'Descripción del producto 532', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 25, 36, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(219, NULL, 1, 'Producto 745', 'INT-00745', 'SKU-00745', 'Descripción del producto 745', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 18, 19, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(220, NULL, 1, 'Producto 92', 'INT-00092', 'SKU-00092', 'Descripción del producto 92', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 6, 12, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(221, NULL, 1, 'Producto 997', 'INT-00997', 'SKU-00997', 'Descripción del producto 997', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 19, 6, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(222, NULL, 1, 'Producto 643', 'INT-00643', 'SKU-00643', 'Descripción del producto 643', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 33, 16, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(223, NULL, 1, 'Producto 861', 'INT-00861', 'SKU-00861', 'Descripción del producto 861', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 13, 34, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(224, NULL, 1, 'Producto 194', 'INT-00194', 'SKU-00194', 'Descripción del producto 194', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 11, 20, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(225, NULL, 1, 'Producto 399', 'INT-00399', 'SKU-00399', 'Descripción del producto 399', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 21, 31, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(226, NULL, 1, 'Producto 337', 'INT-00337', 'SKU-00337', 'Descripción del producto 337', 'COMBO', 2, NULL, NULL, NULL, 1, 26, 16, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(227, NULL, 1, 'Producto 823', 'INT-00823', 'SKU-00823', 'Descripción del producto 823', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 19, 34, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(228, NULL, 1, 'Producto 823', 'INT-00823', 'SKU-00823', 'Descripción del producto 823', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 25, 13, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(229, NULL, 1, 'Producto 119', 'INT-001192', 'SKU-001192', 'Descripción del producto 119', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 13, 2, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(230, NULL, 1, 'Producto 29', 'INT-00029', 'SKU-00029', 'Descripción del producto 29', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 21, 16, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(231, NULL, 1, 'Producto 448', 'INT-00448', 'SKU-00448', 'Descripción del producto 448', 'COMBO', 2, NULL, NULL, NULL, 1, 27, 12, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(232, NULL, 1, 'Producto 280', 'INT-00280', 'SKU-00280', 'Descripción del producto 280', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 1, 27, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(233, NULL, 1, 'Producto 289', 'INT-00289', 'SKU-00289', 'Descripción del producto 289', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 26, 22, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(234, NULL, 1, 'Producto 376', 'INT-00376', 'SKU-00376', 'Descripción del producto 376', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 29, 30, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(235, NULL, 1, 'Producto 839', 'INT-00839', 'SKU-00839', 'Descripción del producto 839', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 32, 23, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(236, NULL, 1, 'Producto 634', 'INT-00634', 'SKU-00634', 'Descripción del producto 634', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 2, 9, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(237, NULL, 1, 'Producto 210', 'INT-00210', 'SKU-00210', 'Descripción del producto 210', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 17, 24, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(238, NULL, 1, 'Producto 358', 'INT-00358', 'SKU-00358', 'Descripción del producto 358', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 4, 18, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(239, NULL, 1, 'Producto 436', 'INT-00436', 'SKU-00436', 'Descripción del producto 436', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 18, 20, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(240, NULL, 1, 'Producto 612', 'INT-00612', 'SKU-00612', 'Descripción del producto 612', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 30, 23, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(241, NULL, 1, 'Producto 586', 'INT-00586', 'SKU-00586', 'Descripción del producto 586', 'COMBO', 2, NULL, NULL, NULL, 1, 26, 23, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(242, NULL, 1, 'Producto 378', 'INT-00378', 'SKU-00378', 'Descripción del producto 378', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 33, 9, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(243, NULL, 1, 'Producto 567', 'INT-00567', 'SKU-00567', 'Descripción del producto 567', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 17, 10, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(244, NULL, 1, 'Producto 200', 'INT-00200', 'SKU-00200', 'Descripción del producto 200', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 23, 22, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(245, NULL, 1, 'Producto 45', 'INT-00045', 'SKU-00045', 'Descripción del producto 45', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 8, 19, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(246, NULL, 1, 'Producto 775', 'INT-00775', 'SKU-00775', 'Descripción del producto 775', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 30, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(247, NULL, 1, 'Producto 6', 'INT-00006', 'SKU-00006', 'Descripción del producto 6', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 8, 10, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(248, NULL, 1, 'Producto 684', 'INT-00684', 'SKU-00684', 'Descripción del producto 684', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 36, 6, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(249, NULL, 1, 'Producto 636', 'INT-00636', 'SKU-00636', 'Descripción del producto 636', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 8, 14, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(250, NULL, 1, 'Producto 512', 'INT-00512', 'SKU-00512', 'Descripción del producto 512', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 23, 32, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(251, NULL, 1, 'Producto 533', 'INT-00533', 'SKU-00533', 'Descripción del producto 533', 'COMBO', 2, NULL, NULL, NULL, 1, 17, 24, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(252, NULL, 1, 'Producto 813', 'INT-00813', 'SKU-00813', 'Descripción del producto 813', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 16, 30, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(253, NULL, 1, 'Producto 235', 'INT-00235', 'SKU-00235', 'Descripción del producto 235', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 10, 33, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(254, NULL, 1, 'Producto 583', 'INT-00583', 'SKU-00583', 'Descripción del producto 583', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 28, 6, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(255, NULL, 1, 'Producto 387', 'INT-00387', 'SKU-00387', 'Descripción del producto 387', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 13, 13, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(256, NULL, 1, 'Producto 73', 'INT-00073', 'SKU-00073', 'Descripción del producto 73', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 1, 3, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(257, NULL, 1, 'Producto 39', 'INT-00039', 'SKU-00039', 'Descripción del producto 39', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 7, 34, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(258, NULL, 1, 'Producto 675', 'INT-00675', 'SKU-00675', 'Descripción del producto 675', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 19, 2, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(259, NULL, 1, 'Producto 406', 'INT-00406', 'SKU-00406', 'Descripción del producto 406', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 6, 30, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(260, NULL, 1, 'Producto 96', 'INT-00096', 'SKU-00096', 'Descripción del producto 96', 'COMBO', 2, NULL, NULL, NULL, 1, 22, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(261, NULL, 1, 'Producto 643', 'INT-00643', 'SKU-00643', 'Descripción del producto 643', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 21, 36, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(262, NULL, 1, 'Producto 539', 'INT-00539', 'SKU-00539', 'Descripción del producto 539', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 6, 28, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(263, NULL, 1, 'Producto 211', 'INT-00211', 'SKU-00211', 'Descripción del producto 211', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 20, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(264, NULL, 1, 'Producto 598', 'INT-00598', 'SKU-00598', 'Descripción del producto 598', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 35, 16, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(265, NULL, 1, 'Producto 872', 'INT-00872', 'SKU-00872', 'Descripción del producto 872', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 33, 17, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(266, NULL, 1, 'Producto 597', 'INT-00597', 'SKU-00597', 'Descripción del producto 597', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 2, 21, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(267, NULL, 1, 'Producto 538', 'INT-00538', 'SKU-00538', 'Descripción del producto 538', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 11, 11, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(268, NULL, 1, 'Producto 724', 'INT-00724', 'SKU-00724', 'Descripción del producto 724', 'COMBO', 2, NULL, NULL, NULL, 1, 11, 6, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(269, NULL, 1, 'Producto 42', 'INT-00042', 'SKU-00042', 'Descripción del producto 42', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 17, 13, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(270, NULL, 1, 'Producto 737', 'INT-00737', 'SKU-00737', 'Descripción del producto 737', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 2, 5, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(271, NULL, 1, 'Producto 772', 'INT-00772', 'SKU-00772', 'Descripción del producto 772', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 30, 33, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(272, NULL, 1, 'Producto 996', 'INT-00996', 'SKU-00996', 'Descripción del producto 996', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 8, 13, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(273, NULL, 1, 'Producto 202', 'INT-00202', 'SKU-00202', 'Descripción del producto 202', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 14, 26, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(274, NULL, 1, 'Producto 212', 'INT-00212', 'SKU-00212', 'Descripción del producto 212', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 31, 32, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(275, NULL, 1, 'Producto 855', 'INT-00855', 'SKU-00855', 'Descripción del producto 855', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 7, 6, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(276, NULL, 1, 'Producto 584', 'INT-00584', 'SKU-00584', 'Descripción del producto 584', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 2, 16, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(277, NULL, 1, 'Producto 765', 'INT-00765', 'SKU-00765', 'Descripción del producto 765', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 4, 36, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(278, NULL, 1, 'Producto 932', 'INT-00932', 'SKU-00932', 'Descripción del producto 932', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 19, 16, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(279, NULL, 1, 'Producto 643', 'INT-00643', 'SKU-00643', 'Descripción del producto 643', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 5, 14, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(280, NULL, 1, 'Producto 781', 'INT-00781', 'SKU-00781', 'Descripción del producto 781', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 21, 24, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(281, NULL, 1, 'Producto 651', 'INT-00651', 'SKU-00651', 'Descripción del producto 651', 'COMBO', 2, NULL, NULL, NULL, 1, 9, 12, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(282, NULL, 1, 'Producto 633', 'INT-00633', 'SKU-00633', 'Descripción del producto 633', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 26, 5, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL);
INSERT INTO `inv_items` (`id`, `api_data_id`, `categoryId`, `name`, `internal_code`, `sku`, `description`, `type`, `taxId`, `commandId`, `brandId`, `houseId`, `inventoriable`, `purchasing_unit`, `consumption_unit`, `handles_serial`, `status`, `generic`, `created_at`, `updated_at`, `deleted_at`) VALUES
(283, NULL, 1, 'Producto 354', 'INT-00354', 'SKU-00354', 'Descripción del producto 354', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 7, 18, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(284, 374, 82, 'TECLADO INALAMBRICO COMPUTADOR', 'INT-001251', 'SKU-001251', 'Descripción del producto 125', 'IMPORTADO', 2, NULL, 8, 2, 1, 28, 22, 0, 1, 0, '2025-11-27 14:37:09', '2026-03-04 13:55:51', NULL),
(285, NULL, 1, 'Producto 903', 'INT-00903', 'SKU-00903', 'Descripción del producto 903', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 35, 18, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(286, NULL, 1, 'Producto 521', 'INT-00521', 'SKU-00521', 'Descripción del producto 521', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 4, 13, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(287, NULL, 1, 'Producto 29', 'INT-00029', 'SKU-00029', 'Descripción del producto 29', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 28, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(288, NULL, 1, 'Producto 942', 'INT-00942', 'SKU-00942', 'Descripción del producto 942', 'COMBO', 2, NULL, NULL, NULL, 1, 14, 29, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(289, NULL, 1, 'Producto 636', 'INT-00636', 'SKU-00636', 'Descripción del producto 636', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 14, 17, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(290, NULL, 1, 'Producto 313', 'INT-00313', 'SKU-00313', 'Descripción del producto 313', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 4, 34, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(291, NULL, 1, 'Producto 169', 'INT-00169', 'SKU-00169', 'Descripción del producto 169', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 14, 26, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(292, NULL, 1, 'Producto 979', 'INT-00979', 'SKU-00979', 'Descripción del producto 979', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 28, 13, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(293, NULL, 1, 'Producto 417', 'INT-00417', 'SKU-00417', 'Descripción del producto 417', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 27, 34, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(294, NULL, 1, 'Producto 852', 'INT-00852', 'SKU-00852', 'Descripción del producto 852', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 26, 29, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(295, NULL, 1, 'Producto 942', 'INT-00942', 'SKU-00942', 'Descripción del producto 942', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 10, 7, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(296, NULL, 1, 'Producto 878', 'INT-00878', 'SKU-00878', 'Descripción del producto 878', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 3, 17, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(297, NULL, 1, 'Producto 197', 'INT-00197', 'SKU-00197', 'Descripción del producto 197', 'COMBO', 2, NULL, NULL, NULL, 1, 4, 15, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(298, NULL, 1, 'Producto 732', 'INT-00732', 'SKU-00732', 'Descripción del producto 732', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 3, 26, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(299, NULL, 1, 'Producto 695', 'INT-00695', 'SKU-00695', 'Descripción del producto 695', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 24, 31, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(300, NULL, 1, 'Producto 663', 'INT-00663', 'SKU-00663', 'Descripción del producto 663', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 31, 9, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(301, NULL, 1, 'Producto 45', 'INT-00045', 'SKU-00045', 'Descripción del producto 45', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 13, 10, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(302, NULL, 1, 'Producto 150', 'INT-00150', 'SKU-00150', 'Descripción del producto 150', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 20, 26, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(303, NULL, 1, 'Producto 370', 'INT-00370', 'SKU-00370', 'Descripción del producto 370', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 16, 2, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(304, NULL, 1, 'Producto 576', 'INT-00576', 'SKU-00576', 'Descripción del producto 576', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 17, 18, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(305, NULL, 1, 'Producto 574', 'INT-00574', 'SKU-00574', 'Descripción del producto 574', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 10, 8, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(306, NULL, 1, 'Producto 610', 'INT-00610', 'SKU-00610', 'Descripción del producto 610', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 19, 5, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(307, NULL, 1, 'Producto 212', 'INT-00212', 'SKU-00212', 'Descripción del producto 212', 'COMBO', 2, NULL, NULL, NULL, 1, 9, 18, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(308, NULL, 1, 'Producto 49', 'INT-00049', 'SKU-00049', 'Descripción del producto 49', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 27, 23, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(309, NULL, 1, 'Producto 579', 'INT-00579', 'SKU-00579', 'Descripción del producto 579', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 19, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(310, NULL, 1, 'Producto 28', 'INT-00028', 'SKU-00028', 'Descripción del producto 28', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 8, 28, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(311, NULL, 1, 'Producto 627', 'INT-00627', 'SKU-00627', 'Descripción del producto 627', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 16, 31, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(312, NULL, 1, 'Producto 554', 'INT-00554', 'SKU-00554', 'Descripción del producto 554', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 5, 6, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(313, NULL, 1, 'Producto 891', 'INT-00891', 'SKU-00891', 'Descripción del producto 891', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 31, 9, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(314, NULL, 1, 'Producto 396', 'INT-00396', 'SKU-00396', 'Descripción del producto 396', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 34, 29, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(315, NULL, 1, 'Producto 520', 'INT-00520', 'SKU-00520', 'Descripción del producto 520', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 35, 9, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(316, NULL, 1, 'Producto 40', 'INT-00040', 'SKU-00040', 'Descripción del producto 40', 'COMBO', 2, NULL, NULL, NULL, 1, 25, 6, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(317, NULL, 1, 'Producto 314', 'INT-00314', 'SKU-00314', 'Descripción del producto 314', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 18, 20, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(318, NULL, 1, 'Producto 781', 'INT-00781', 'SKU-00781', 'Descripción del producto 781', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 33, 6, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(319, NULL, 1, 'Producto 727', 'INT-00727', 'SKU-00727', 'Descripción del producto 727', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 16, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(320, NULL, 1, 'Producto 807', 'INT-00807', 'SKU-00807', 'Descripción del producto 807', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 8, 26, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(321, NULL, 1, 'Producto 965', 'INT-00965', 'SKU-00965', 'Descripción del producto 965', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 17, 36, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(322, NULL, 1, 'Producto 382', 'INT-00382', 'SKU-00382', 'Descripción del producto 382', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 6, 2, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(323, NULL, 1, 'Producto 424', 'INT-00424', 'SKU-00424', 'Descripción del producto 424', 'COMBO', 2, NULL, NULL, NULL, 1, 18, 33, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(324, NULL, 1, 'Producto 294', 'INT-00294', 'SKU-00294', 'Descripción del producto 294', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 17, 28, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(325, NULL, 1, 'Producto 770', 'INT-00770', 'SKU-00770', 'Descripción del producto 770', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 16, 30, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(326, NULL, 1, 'Producto 415', 'INT-00415', 'SKU-00415', 'Descripción del producto 415', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 28, 9, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(327, NULL, 1, 'Producto 400', 'INT-00400', 'SKU-00400', 'Descripción del producto 400', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 6, 5, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(328, NULL, 1, 'Producto 774', 'INT-00774', 'SKU-00774', 'Descripción del producto 774', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 20, 24, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(329, NULL, 1, 'Producto 632', 'INT-00632', 'SKU-00632', 'Descripción del producto 632', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 27, 27, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(330, NULL, 1, 'Producto 589', 'INT-00589', 'SKU-00589', 'Descripción del producto 589', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 32, 3, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(331, NULL, 1, 'Producto 340', 'INT-00340', 'SKU-00340', 'Descripción del producto 340', 'COMBO', 2, NULL, NULL, NULL, 1, 24, 18, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(332, NULL, 1, 'Producto 792', 'INT-00792', 'SKU-00792', 'Descripción del producto 792', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 27, 28, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(333, NULL, 40, 'CARGADOR AUDIFONOS TIPO C', 'INT-001571', 'SKU-001571', 'CARGADOR AUDIFONOS TIPO C', 'IMPORTADO', 2, NULL, 3, 6, 1, 20, 25, 0, 1, 0, '2025-11-27 14:37:09', '2026-03-04 14:01:27', NULL),
(334, NULL, 1, 'Producto 873', 'INT-00873', 'SKU-00873', 'Descripción del producto 873', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 20, 9, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(335, NULL, 1, 'Producto 31', 'INT-00031', 'SKU-00031', 'Descripción del producto 31', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 1, 8, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(336, NULL, 1, 'Producto 129', 'INT-00129', 'SKU-00129', 'Descripción del producto 129', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 23, 1, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(337, NULL, 1, 'Producto 762', 'INT-00762', 'SKU-00762', 'Descripción del producto 762', 'INSUMO', 2, NULL, NULL, NULL, 1, 28, 19, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(338, NULL, 1, 'Producto 801', 'INT-00801', 'SKU-00801', 'Descripción del producto 801', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 36, 16, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(339, NULL, 1, 'Producto 191', 'INT-00191', 'SKU-00191', 'Descripción del producto 191', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 25, 16, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(340, NULL, 1, 'Producto 548', 'INT-00548', 'SKU-00548', 'Descripción del producto 548', 'COMBO', 2, NULL, NULL, NULL, 1, 12, 22, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(341, NULL, 1, 'Producto 37', 'INT-00037', 'SKU-00037', 'Descripción del producto 37', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 18, 23, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(342, NULL, 1, 'Producto 869', 'INT-00869', 'SKU-00869', 'Descripción del producto 869', 'COMBO', 2, NULL, NULL, NULL, 1, 28, 9, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(343, NULL, 1, 'Producto 457', 'INT-00457', 'SKU-00457', 'Descripción del producto 457', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 9, 19, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(344, NULL, 1, 'Producto 715', 'INT-00715', 'SKU-00715', 'Descripción del producto 715', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 8, 10, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(345, NULL, 1, 'Producto 961', 'INT-00961', 'SKU-00961', 'Descripción del producto 961', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 18, 23, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(346, NULL, 1, 'Producto 255', 'INT-00255', 'SKU-00255', 'Descripción del producto 255', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 13, 11, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(347, NULL, 1, 'Producto 641', 'INT-00641', 'SKU-00641', 'Descripción del producto 641', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 12, 20, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(348, NULL, 1, 'Producto 66', 'INT-00066', 'SKU-00066', 'Descripción del producto 66', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 13, 13, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(349, NULL, 1, 'Producto 620', 'INT-00620', 'SKU-00620', 'Descripción del producto 620', 'INSUMO', 2, NULL, NULL, NULL, 1, 23, 3, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(350, NULL, 1, 'Producto 749', 'INT-00749', 'SKU-00749', 'Descripción del producto 749', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 6, 13, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(351, NULL, 1, 'Producto 823', 'INT-00823', 'SKU-00823', 'Descripción del producto 823', 'COMBO', 2, NULL, NULL, NULL, 1, 10, 3, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(352, NULL, 1, 'Producto 932', 'INT-00932', 'SKU-00932', 'Descripción del producto 932', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 34, 11, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(353, NULL, 1, 'Producto 225', 'INT-00225', 'SKU-00225', 'Descripción del producto 225', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 23, 14, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(354, NULL, 1, 'Producto 704', 'INT-00704', 'SKU-00704', 'Descripción del producto 704', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 22, 27, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(355, NULL, 1, 'Producto 240', 'INT-00240', 'SKU-00240', 'Descripción del producto 240', 'COMBO', 2, NULL, NULL, NULL, 1, 14, 5, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(356, NULL, 1, 'Producto 437', 'INT-00437', 'SKU-00437', 'Descripción del producto 437', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 15, 18, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(357, NULL, 1, 'Producto 89', 'INT-00089', 'SKU-00089', 'Descripción del producto 89', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 8, 31, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(358, NULL, 1, 'Producto 272', 'INT-00272', 'SKU-00272', 'Descripción del producto 272', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 24, 8, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(359, NULL, 1, 'Producto 74', 'INT-00074', 'SKU-00074', 'Descripción del producto 74', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 9, 1, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(360, NULL, 1, 'Producto 998', 'INT-00998', 'SKU-00998', 'Descripción del producto 998', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 33, 31, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(361, NULL, 1, 'Producto 63', 'INT-00063', 'SKU-00063', 'Descripción del producto 63', 'INSUMO', 2, NULL, NULL, NULL, 1, 2, 30, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(362, NULL, 1, 'Producto 818', 'INT-00818', 'SKU-00818', 'Descripción del producto 818', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 8, 19, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(363, NULL, 1, 'Producto 598', 'INT-00598', 'SKU-00598', 'Descripción del producto 598', 'COMBO', 2, NULL, NULL, NULL, 1, 33, 14, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(364, NULL, 1, 'Producto 665', 'INT-00665', 'SKU-00665', 'Descripción del producto 665', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 32, 33, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(365, NULL, 1, 'Producto 324', 'INT-00324', 'SKU-00324', 'Descripción del producto 324', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 9, 19, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(366, NULL, 1, 'Producto 177', 'INT-00177', 'SKU-00177', 'Descripción del producto 177', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 14, 10, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(367, NULL, 1, 'Producto 378', 'INT-00378', 'SKU-00378', 'Descripción del producto 378', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 20, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(368, NULL, 1, 'Producto 972', 'INT-00972', 'SKU-00972', 'Descripción del producto 972', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 2, 3, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(369, NULL, 1, 'Producto 65', 'INT-00065', 'SKU-00065', 'Descripción del producto 65', 'INSUMO', 2, NULL, NULL, NULL, 1, 7, 14, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(370, NULL, 1, 'Producto 975', 'INT-00975', 'SKU-00975', 'Descripción del producto 975', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 29, 10, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(371, NULL, 1, 'Producto 864', 'INT-00864', 'SKU-00864', 'Descripción del producto 864', 'COMBO', 2, NULL, NULL, NULL, 1, 35, 10, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(372, NULL, 1, 'Producto 776', 'INT-00776', 'SKU-00776', 'Descripción del producto 776', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 12, 15, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(373, NULL, 1, 'Producto 288', 'INT-00288', 'SKU-00288', 'Descripción del producto 288', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 5, 30, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(374, NULL, 1, 'Producto 512', 'INT-00512', 'SKU-00512', 'Descripción del producto 512', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 13, 32, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(375, NULL, 1, 'Producto 944', 'INT-00944', 'SKU-00944', 'Descripción del producto 944', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 3, 10, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(376, NULL, 1, 'Producto 297', 'INT-00297', 'SKU-00297', 'Descripción del producto 297', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 20, 11, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(377, NULL, 1, 'Producto 455', 'INT-00455', 'SKU-00455', 'Descripción del producto 455', 'INSUMO', 2, NULL, NULL, NULL, 1, 30, 30, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(378, NULL, 1, 'Producto 970', 'INT-00970', 'SKU-00970', 'Descripción del producto 970', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 22, 7, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(379, NULL, 1, 'Producto 846', 'INT-00846', 'SKU-00846', 'Descripción del producto 846', 'COMBO', 2, NULL, NULL, NULL, 1, 28, 20, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(380, NULL, 1, 'Producto 662', 'INT-00662', 'SKU-00662', 'Descripción del producto 662', 'INSUMO', 2, NULL, NULL, NULL, 1, 21, 32, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(381, NULL, 1, 'Producto 927', 'INT-00927', 'SKU-00927', 'Descripción del producto 927', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 11, 29, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(382, NULL, 1, 'Producto 638', 'INT-00638', 'SKU-00638', 'Descripción del producto 638', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 35, 20, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(383, NULL, 1, 'Producto 566', 'INT-00566', 'SKU-00566', 'Descripción del producto 566', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 15, 12, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(384, NULL, 1, 'Producto 414', 'INT-00414', 'SKU-00414', 'Descripción del producto 414', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 27, 31, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(385, NULL, 1, 'Producto 800', 'INT-00800', 'SKU-00800', 'Descripción del producto 800', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 33, 4, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(386, NULL, 1, 'Producto 264', 'INT-00264', 'SKU-00264', 'Descripción del producto 264', 'COMBO', 2, NULL, NULL, NULL, 1, 23, 1, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(387, NULL, 1, 'Producto 981', 'INT-00981', 'SKU-00981', 'Descripción del producto 981', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 35, 28, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(388, NULL, 1, 'Producto 124', 'INT-00124', 'SKU-00124', 'Descripción del producto 124', 'INSUMO', 2, NULL, NULL, NULL, 1, 8, 16, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(389, NULL, 1, 'Producto 360', 'INT-00360', 'SKU-00360', 'Descripción del producto 360', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 24, 2, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(390, NULL, 1, 'Producto 400', 'INT-00400', 'SKU-00400', 'Descripción del producto 400', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 21, 16, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(391, NULL, 1, 'Producto 822', 'INT-00822', 'SKU-00822', 'Descripción del producto 822', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 12, 2, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(392, NULL, 1, 'Producto 19', 'INT-00019', 'SKU-00019', 'Descripción del producto 19', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 12, 4, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(393, NULL, 1, 'Producto 125', 'INT-00125', 'SKU-00125', 'Descripción del producto 125', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 27, 32, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(394, NULL, 1, 'Producto 634', 'INT-00634', 'SKU-00634', 'Descripción del producto 634', 'COMBO', 2, NULL, NULL, NULL, 1, 27, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(395, NULL, 1, 'Producto 423', 'INT-00423', 'SKU-00423', 'Descripción del producto 423', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 2, 19, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(396, NULL, 1, 'Producto 83', 'INT-00083', 'SKU-00083', 'Descripción del producto 83', 'INSUMO', 2, NULL, NULL, NULL, 1, 29, 24, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(397, NULL, 1, 'Producto 888', 'INT-00888', 'SKU-00888', 'Descripción del producto 888', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 30, 12, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(398, NULL, 1, 'Producto 61', 'INT-00061', 'SKU-00061', 'Descripción del producto 61', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 11, 33, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(399, NULL, 1, 'Producto 841', 'INT-00841', 'SKU-00841', 'Descripción del producto 841', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 36, 7, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(400, NULL, 1, 'Producto 545', 'INT-00545', 'SKU-00545', 'Descripción del producto 545', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 5, 9, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(401, NULL, 1, 'Producto 73', 'INT-00073', 'SKU-00073', 'Descripción del producto 73', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 5, 13, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(402, NULL, 1, 'Producto 773', 'INT-00773', 'SKU-00773', 'Descripción del producto 773', 'COMBO', 2, NULL, NULL, NULL, 1, 6, 18, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(403, NULL, 1, 'Producto 615', 'INT-00615', 'SKU-00615', 'Descripción del producto 615', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 23, 12, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(404, NULL, 1, 'Producto 913', 'INT-00913', 'SKU-00913', 'Descripción del producto 913', 'INSUMO', 2, NULL, NULL, NULL, 1, 32, 20, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(405, NULL, 1, 'Producto 550', 'INT-00550', 'SKU-00550', 'Descripción del producto 550', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 4, 16, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(406, NULL, 1, 'Producto 936', 'INT-00936', 'SKU-00936', 'Descripción del producto 936', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 33, 33, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(407, NULL, 1, 'Producto 663', 'INT-00663', 'SKU-00663', 'Descripción del producto 663', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 12, 11, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(408, NULL, 1, 'Producto 195', 'INT-00195', 'SKU-00195', 'Descripción del producto 195', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 20, 18, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(409, NULL, 1, 'Producto 321', 'INT-00321', 'SKU-00321', 'Descripción del producto 321', 'COMBO', 2, NULL, NULL, NULL, 1, 8, 12, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(410, NULL, 1, 'Producto 700', 'INT-00700', 'SKU-00700', 'Descripción del producto 700', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 22, 33, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(411, NULL, 1, 'Producto 476', 'INT-00476', 'SKU-00476', 'Descripción del producto 476', 'INSUMO', 2, NULL, NULL, NULL, 1, 31, 19, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(412, NULL, 1, 'Producto 27', 'INT-00027', 'SKU-00027', 'Descripción del producto 27', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 30, 12, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(413, NULL, 1, 'Producto 145', 'INT-00145', 'SKU-00145', 'Descripción del producto 145', 'COMBO', 2, 2, 3, 3, 1, 4, 25, 0, 1, 0, '2025-11-27 14:37:09', '2026-01-20 16:25:51', NULL),
(414, NULL, 1, 'Producto 617', 'INT-00617', 'SKU-00617', 'Descripción del producto 617', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 21, 23, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(415, NULL, 1, 'Producto 28', 'INT-00028', 'SKU-00028', 'Descripción del producto 28', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 12, 29, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(416, NULL, 1, 'Producto 848', 'INT-00848', 'SKU-00848', 'Descripción del producto 848', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 21, 29, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(417, NULL, 1, 'Producto 676', 'INT-00676', 'SKU-00676', 'Descripción del producto 676', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 12, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(418, NULL, 1, 'Producto 792', 'INT-00792', 'SKU-00792', 'Descripción del producto 792', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 4, 26, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(419, NULL, 1, 'Producto 93', 'INT-00093', 'SKU-00093', 'Descripción del producto 93', 'INSUMO', 2, NULL, NULL, NULL, 1, 5, 28, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(420, NULL, 1, 'Producto 229', 'INT-00229', 'SKU-00229', 'Descripción del producto 229', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 33, 26, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(421, NULL, 1, 'Producto 527', 'INT-00527', 'SKU-00527', 'Descripción del producto 527', 'COMBO', 2, NULL, NULL, NULL, 1, 28, 30, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(422, NULL, 1, 'Producto 511', 'INT-00511', 'SKU-00511', 'Descripción del producto 511', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 16, 12, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(423, NULL, 1, 'Producto 859', 'INT-00859', 'SKU-00859', 'Descripción del producto 859', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 16, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(424, NULL, 1, 'Producto 147', 'INT-00147', 'SKU-00147', 'Descripción del producto 147', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 29, 19, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(425, NULL, 1, 'Producto 215', 'INT-00215', 'SKU-00215', 'Descripción del producto 215', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 36, 22, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(426, NULL, 1, 'Producto 99', 'INT-00099', 'SKU-00099', 'Descripción del producto 99', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 19, 5, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(427, NULL, 1, 'Producto 751', 'INT-00751', 'SKU-00751', 'Descripción del producto 751', 'INSUMO', 2, NULL, NULL, NULL, 1, 15, 9, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(428, NULL, 1, 'Producto 816', 'INT-00816', 'SKU-00816', 'Descripción del producto 816', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 6, 21, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(429, NULL, 1, 'Producto 332', 'INT-00332', 'SKU-00332', 'Descripción del producto 332', 'COMBO', 2, NULL, NULL, NULL, 1, 1, 3, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(430, NULL, 1, 'Producto 995', 'INT-00995', 'SKU-00995', 'Descripción del producto 995', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 1, 5, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(431, NULL, 1, 'Producto 50', 'INT-00050', 'SKU-00050', 'Descripción del producto 50', 'COMBO', 2, NULL, NULL, NULL, 1, 8, 16, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(432, NULL, 1, 'Producto 205', 'INT-00205', 'SKU-00205', 'Descripción del producto 205', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 12, 27, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(433, NULL, 1, 'Producto 319', 'INT-00319', 'SKU-00319', 'Descripción del producto 319', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 1, 20, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(434, NULL, 1, 'Producto 744', 'INT-00744', 'SKU-00744', 'Descripción del producto 744', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 31, 22, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(435, NULL, 1, 'Producto 784', 'INT-00784', 'SKU-00784', 'Descripción del producto 784', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 27, 34, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(436, NULL, 1, 'Producto 547', 'INT-00547', 'SKU-00547', 'Descripción del producto 547', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 9, 14, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(437, NULL, 1, 'Producto 452', 'INT-00452', 'SKU-00452', 'Descripción del producto 452', 'INSUMO', 2, NULL, NULL, NULL, 1, 2, 2, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(438, NULL, 1, 'Producto 435', 'INT-00435', 'SKU-00435', 'Descripción del producto 435', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 20, 33, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(439, NULL, 1, 'Producto 567', 'INT-00567', 'SKU-00567', 'Descripción del producto 567', 'COMBO', 2, NULL, NULL, NULL, 1, 1, 29, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(440, NULL, 1, 'Producto 253', 'INT-00253', 'SKU-00253', 'Descripción del producto 253', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 7, 9, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(441, NULL, 1, 'Producto 258', 'INT-00258', 'SKU-00258', 'Descripción del producto 258', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 26, 2, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(442, NULL, 1, 'Producto 942', 'INT-00942', 'SKU-00942', 'Descripción del producto 942', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 34, 24, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(443, NULL, 1, 'Producto 727', 'INT-00727', 'SKU-00727', 'Descripción del producto 727', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 32, 15, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(444, NULL, 1, 'Producto 175', 'INT-00175', 'SKU-00175', 'Descripción del producto 175', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 5, 11, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(445, NULL, 1, 'Producto 997', 'INT-00997', 'SKU-00997', 'Descripción del producto 997', 'INSUMO', 2, NULL, NULL, NULL, 1, 19, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(446, NULL, 1, 'Producto 360', 'INT-00360', 'SKU-00360', 'Descripción del producto 360', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 17, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(447, NULL, 1, 'Producto 804', 'INT-00804', 'SKU-00804', 'Descripción del producto 804', 'COMBO', 2, NULL, NULL, NULL, 1, 19, 28, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(448, NULL, 1, 'Producto 277', 'INT-00277', 'SKU-00277', 'Descripción del producto 277', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 32, 29, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(449, NULL, 1, 'Producto 268', 'INT-00268', 'SKU-00268', 'Descripción del producto 268', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 30, 5, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(450, NULL, 1, 'Producto 74', 'INT-00074', 'SKU-00074', 'Descripción del producto 74', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 12, 21, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(451, NULL, 1, 'Producto 502', 'INT-00502', 'SKU-00502', 'Descripción del producto 502', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 14, 34, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(452, NULL, 1, 'Producto 547', 'INT-00547', 'SKU-00547', 'Descripción del producto 547', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 36, 3, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(453, NULL, 1, 'Producto 980', 'INT-00980', 'SKU-00980', 'Descripción del producto 980', 'INSUMO', 2, NULL, NULL, NULL, 1, 3, 33, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(454, NULL, 1, 'Producto 417', 'INT-00417', 'SKU-00417', 'Descripción del producto 417', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 18, 16, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(455, NULL, 1, 'Producto 954', 'INT-00954', 'SKU-00954', 'Descripción del producto 954', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 35, 4, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(456, NULL, 1, 'Producto 892', 'INT-00892', 'SKU-00892', 'Descripción del producto 892', 'INSUMO', 2, NULL, NULL, NULL, 1, 16, 4, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(457, NULL, 1, 'Producto 753', 'INT-00753', 'SKU-00753', 'Descripción del producto 753', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 26, 34, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(458, NULL, 1, 'Producto 277', 'INT-00277', 'SKU-00277', 'Descripción del producto 277', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 3, 11, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(459, NULL, 1, 'Producto 585', 'INT-00585', 'SKU-00585', 'Descripción del producto 585', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 5, 34, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(460, NULL, 1, 'Producto 330', 'INT-00330', 'SKU-00330', 'Descripción del producto 330', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 17, 13, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(461, NULL, 1, 'Producto 208', 'INT-00208', 'SKU-00208', 'Descripción del producto 208', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 15, 17, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(462, NULL, 1, 'Producto 550', 'INT-00550', 'SKU-00550', 'Descripción del producto 550', 'COMBO', 2, NULL, NULL, NULL, 1, 2, 30, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(463, NULL, 1, 'Producto 363', 'INT-00363', 'SKU-00363', 'Descripción del producto 363', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 5, 3, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(464, NULL, 1, 'Producto 655', 'INT-00655', 'SKU-00655', 'Descripción del producto 655', 'INSUMO', 2, NULL, NULL, NULL, 1, 17, 13, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(465, NULL, 1, 'Producto 719', 'INT-00719', 'SKU-00719', 'Descripción del producto 719', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 6, 25, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(466, NULL, 1, 'Producto 444', 'INT-00444', 'SKU-00444', 'Descripción del producto 444', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 24, 25, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(467, NULL, 1, 'Producto 304', 'INT-00304', 'SKU-00304', 'Descripción del producto 304', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 16, 5, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(468, NULL, 1, 'Producto 414', 'INT-00414', 'SKU-00414', 'Descripción del producto 414', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 29, 29, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(469, NULL, 1, 'Producto 923', 'INT-00923', 'SKU-00923', 'Descripción del producto 923', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 35, 2, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(470, NULL, 1, 'Producto 29', 'INT-00029', 'SKU-00029', 'Descripción del producto 29', 'COMBO', 2, NULL, NULL, NULL, 1, 33, 25, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(471, NULL, 1, 'Producto 566', 'INT-00566', 'SKU-00566', 'Descripción del producto 566', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 3, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(472, NULL, 1, 'Producto 976', 'INT-00976', 'SKU-00976', 'Descripción del producto 976', 'INSUMO', 2, NULL, NULL, NULL, 1, 34, 30, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(473, NULL, 1, 'Producto 886', 'INT-00886', 'SKU-00886', 'Descripción del producto 886', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 32, 26, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(474, NULL, 1, 'Producto 547', 'INT-00547', 'SKU-00547', 'Descripción del producto 547', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 25, 25, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(475, NULL, 1, 'Producto 959', 'INT-00959', 'SKU-00959', 'Descripción del producto 959', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 24, 29, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(476, NULL, 1, 'Producto 75', 'INT-00075', 'SKU-00075', 'Descripción del producto 75', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 13, 3, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(477, NULL, 1, 'Producto 194', 'INT-00194', 'SKU-00194', 'Descripción del producto 194', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 22, 36, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(478, NULL, 1, 'Producto 903', 'INT-00903', 'SKU-00903', 'Descripción del producto 903', 'COMBO', 2, NULL, NULL, NULL, 1, 7, 4, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(479, NULL, 1, 'Producto 636', 'INT-00636', 'SKU-00636', 'Descripción del producto 636', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 34, 24, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(480, NULL, 1, 'Producto 493', 'INT-00493', 'SKU-00493', 'Descripción del producto 493', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 16, 27, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(481, NULL, 1, 'Producto 657', 'INT-00657', 'SKU-00657', 'Descripción del producto 657', 'INSUMO', 2, NULL, NULL, NULL, 1, 15, 1, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(482, NULL, 1, 'Producto 277', 'INT-00277', 'SKU-00277', 'Descripción del producto 277', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 4, 33, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(483, NULL, 1, 'Producto 590', 'INT-00590', 'SKU-00590', 'Descripción del producto 590', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 34, 21, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(484, NULL, 1, 'Producto 748', 'INT-00748', 'SKU-00748', 'Descripción del producto 748', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 2, 19, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(485, NULL, 1, 'Producto 630', 'INT-00630', 'SKU-00630', 'Descripción del producto 630', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 12, 11, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(486, NULL, 1, 'Producto 119', 'INT-001193', 'SKU-001193', 'Descripción del producto 119', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 6, 25, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(487, NULL, 1, 'Producto 46', 'INT-00046', 'SKU-00046', 'Descripción del producto 46', 'COMBO', 2, NULL, NULL, NULL, 1, 26, 29, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(488, NULL, 1, 'Producto 926', 'INT-00926', 'SKU-00926', 'Descripción del producto 926', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 19, 24, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(489, NULL, 1, 'Producto 457', 'INT-00457', 'SKU-00457', 'Descripción del producto 457', 'INSUMO', 2, NULL, NULL, NULL, 1, 15, 36, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(490, NULL, 1, 'Producto 7', 'INT-00007', 'SKU-00007', 'Descripción del producto 7', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 2, 13, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(491, NULL, 1, 'Producto 773', 'INT-00773', 'SKU-00773', 'Descripción del producto 773', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 35, 1, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(492, NULL, 1, 'Producto 348', 'INT-00348', 'SKU-00348', 'Descripción del producto 348', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 6, 18, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(493, NULL, 1, 'Producto 847', 'INT-00847', 'SKU-00847', 'Descripción del producto 847', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 36, 8, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(494, NULL, 1, 'Producto 270', 'INT-00270', 'SKU-00270', 'Descripción del producto 270', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 4, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(495, NULL, 1, 'Producto 986', 'INT-00986', 'SKU-00986', 'Descripción del producto 986', 'COMBO', 2, NULL, NULL, NULL, 1, 35, 14, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(496, NULL, 1, 'Producto 503', 'INT-00503', 'SKU-00503', 'Descripción del producto 503', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 27, 20, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(497, NULL, 1, 'Producto 147', 'INT-00147', 'SKU-00147', 'Descripción del producto 147', 'INSUMO', 2, NULL, NULL, NULL, 1, 24, 31, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(498, NULL, 1, 'Producto 922', 'INT-00922', 'SKU-00922', 'Descripción del producto 922', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 10, 22, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(499, NULL, 1, 'Producto 416', 'INT-00416', 'SKU-00416', 'Descripción del producto 416', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 28, 24, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(500, NULL, 1, 'Producto 400', 'INT-00400', 'SKU-00400', 'Descripción del producto 400', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 22, 23, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(501, NULL, 1, 'Producto 622', 'INT-00622', 'SKU-00622', 'Descripción del producto 622', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 5, 27, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(502, NULL, 1, 'Producto 271', 'INT-00271', 'SKU-00271', 'Descripción del producto 271', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 14, 28, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(503, NULL, 1, 'Producto 803', 'INT-00803', 'SKU-00803', 'Descripción del producto 803', 'COMBO', 2, NULL, NULL, NULL, 1, 31, 15, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(504, NULL, 1, 'Producto 164', 'INT-00164', 'SKU-00164', 'Descripción del producto 164', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 24, 32, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(505, NULL, 1, 'Producto 680', 'INT-00680', 'SKU-00680', 'Descripción del producto 680', 'COMBO', 2, NULL, NULL, NULL, 1, 19, 10, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(506, NULL, 1, 'Producto 305', 'INT-00305', 'SKU-00305', 'Descripción del producto 305', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 29, 26, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(507, NULL, 1, 'Producto 662', 'INT-00662', 'SKU-00662', 'Descripción del producto 662', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 16, 16, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(508, NULL, 1, 'Producto 219', 'INT-00219', 'SKU-00219', 'Descripción del producto 219', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 17, 7, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(509, NULL, 1, 'Producto 175', 'INT-00175', 'SKU-00175', 'Descripción del producto 175', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 23, 26, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(510, NULL, 1, 'Producto 394', 'INT-00394', 'SKU-00394', 'Descripción del producto 394', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 12, 26, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(511, NULL, 1, 'Producto 464', 'INT-00464', 'SKU-00464', 'Descripción del producto 464', 'INSUMO', 2, NULL, NULL, NULL, 1, 8, 27, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(512, NULL, 1, 'Producto 636', 'INT-00636', 'SKU-00636', 'Descripción del producto 636', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 24, 9, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(513, NULL, 1, 'Producto 674', 'INT-00674', 'SKU-00674', 'Descripción del producto 674', 'COMBO', 2, NULL, NULL, NULL, 1, 7, 29, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(514, NULL, 1, 'Producto 396', 'INT-00396', 'SKU-00396', 'Descripción del producto 396', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 7, 5, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(515, NULL, 1, 'Producto 425', 'INT-00425', 'SKU-00425', 'Descripción del producto 425', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 10, 27, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(516, NULL, 1, 'Producto 437', 'INT-00437', 'SKU-00437', 'Descripción del producto 437', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 35, 11, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(517, NULL, 1, 'Producto 646', 'INT-00646', 'SKU-00646', 'Descripción del producto 646', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 33, 30, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(518, NULL, 1, 'Producto 946', 'INT-00946', 'SKU-00946', 'Descripción del producto 946', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 3, 8, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(519, NULL, 1, 'Producto 906', 'INT-00906', 'SKU-00906', 'Descripción del producto 906', 'INSUMO', 2, NULL, NULL, NULL, 1, 31, 9, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(520, NULL, 1, 'Producto 421', 'INT-00421', 'SKU-00421', 'Descripción del producto 421', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 35, 36, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(521, NULL, 1, 'Producto 629', 'INT-00629', 'SKU-00629', 'Descripción del producto 629', 'COMBO', 2, NULL, NULL, NULL, 1, 35, 18, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(522, NULL, 1, 'Producto 285', 'INT-00285', 'SKU-00285', 'Descripción del producto 285', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 3, 10, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(523, NULL, 1, 'Producto 821', 'INT-00821', 'SKU-00821', 'Descripción del producto 821', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 1, 2, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(524, NULL, 1, 'Producto 242', 'INT-00242', 'SKU-00242', 'Descripción del producto 242', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 9, 20, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(525, NULL, 1, 'Producto 713', 'INT-00713', 'SKU-00713', 'Descripción del producto 713', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 18, 9, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(526, NULL, 1, 'Producto 760', 'INT-00760', 'SKU-00760', 'Descripción del producto 760', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 33, 11, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(527, NULL, 1, 'Producto 296', 'INT-00296', 'SKU-00296', 'Descripción del producto 296', 'INSUMO', 2, NULL, NULL, NULL, 1, 25, 13, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(528, NULL, 1, 'Producto 25', 'INT-00025', 'SKU-00025', 'Descripción del producto 25', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 2, 15, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(529, NULL, 1, 'Producto 971', 'INT-00971', 'SKU-00971', 'Descripción del producto 971', 'COMBO', 2, NULL, NULL, NULL, 1, 1, 15, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(530, NULL, 1, 'Producto 778', 'INT-00778', 'SKU-00778', 'Descripción del producto 778', 'INSUMO', 2, NULL, NULL, NULL, 1, 31, 7, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(531, NULL, 1, 'Producto 35', 'INT-00035', 'SKU-00035', 'Descripción del producto 35', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 16, 15, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(532, NULL, 1, 'Producto 41', 'INT-00041', 'SKU-00041', 'Descripción del producto 41', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 28, 29, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(533, NULL, 1, 'Producto 85', 'INT-00085', 'SKU-00085', 'Descripción del producto 85', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 20, 36, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(534, NULL, 1, 'Producto 366', 'INT-00366', 'SKU-00366', 'Descripción del producto 366', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 32, 19, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(535, NULL, 1, 'Producto 369', 'INT-00369', 'SKU-00369', 'Descripción del producto 369', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 2, 29, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(536, NULL, 1, 'Producto 55', 'INT-00055', 'SKU-00055', 'Descripción del producto 55', 'COMBO', 2, NULL, NULL, NULL, 1, 2, 16, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(537, NULL, 1, 'Producto 679', 'INT-00679', 'SKU-00679', 'Descripción del producto 679', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 14, 15, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(538, NULL, 1, 'Producto 596', 'INT-00596', 'SKU-00596', 'Descripción del producto 596', 'INSUMO', 2, NULL, NULL, NULL, 1, 31, 17, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(539, NULL, 1, 'Producto 392', 'INT-00392', 'SKU-00392', 'Descripción del producto 392', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 8, 29, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(540, NULL, 1, 'Producto 621', 'INT-00621', 'SKU-00621', 'Descripción del producto 621', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 30, 7, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(541, NULL, 1, 'Producto 642', 'INT-00642', 'SKU-00642', 'Descripción del producto 642', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 1, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(542, NULL, 1, 'Producto 638', 'INT-00638', 'SKU-00638', 'Descripción del producto 638', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 26, 4, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(543, NULL, 1, 'Producto 193', 'INT-00193', 'SKU-00193', 'Descripción del producto 193', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 14, 17, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(544, NULL, 1, 'Producto 449', 'INT-00449', 'SKU-00449', 'Descripción del producto 449', 'COMBO', 2, NULL, NULL, NULL, 1, 2, 36, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(545, NULL, 1, 'Producto 556', 'INT-00556', 'SKU-00556', 'Descripción del producto 556', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 27, 29, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(546, NULL, 1, 'Producto 22', 'INT-00022', 'SKU-00022', 'Descripción del producto 22', 'INSUMO', 2, NULL, NULL, NULL, 1, 3, 26, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(547, NULL, 1, 'Producto 524', 'INT-00524', 'SKU-00524', 'Descripción del producto 524', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 34, 17, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(548, NULL, 1, 'Producto 701', 'INT-00701', 'SKU-00701', 'Descripción del producto 701', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 27, 2, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(549, NULL, 1, 'Producto 279', 'INT-00279', 'SKU-00279', 'Descripción del producto 279', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 27, 10, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(550, NULL, 1, 'Producto 162', 'INT-00162', 'SKU-00162', 'Descripción del producto 162', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 31, 22, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(551, NULL, 1, 'Producto 706', 'INT-00706', 'SKU-00706', 'Descripción del producto 706', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 3, 6, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(552, NULL, 1, 'Producto 270', 'INT-00270', 'SKU-00270', 'Descripción del producto 270', 'COMBO', 2, NULL, NULL, NULL, 1, 20, 22, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(553, NULL, 1, 'Producto 751', 'INT-00751', 'SKU-00751', 'Descripción del producto 751', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 18, 27, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(554, NULL, 1, 'Producto 14', 'INT-00014', 'SKU-00014', 'Descripción del producto 14', 'INSUMO', 2, NULL, NULL, NULL, 1, 5, 4, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(555, NULL, 1, 'Producto 621', 'INT-00621', 'SKU-00621', 'Descripción del producto 621', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 6, 4, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(556, NULL, 1, 'Producto 625', 'INT-00625', 'SKU-00625', 'Descripción del producto 625', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 5, 21, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(557, NULL, 1, 'Producto 283', 'INT-00283', 'SKU-00283', 'Descripción del producto 283', 'COMBO', 2, NULL, NULL, NULL, 1, 30, 34, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(558, NULL, 1, 'Producto 960', 'INT-00960', 'SKU-00960', 'Descripción del producto 960', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 8, 29, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(559, NULL, 1, 'Producto 901', 'INT-00901', 'SKU-00901', 'Descripción del producto 901', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 3, 16, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL);
INSERT INTO `inv_items` (`id`, `api_data_id`, `categoryId`, `name`, `internal_code`, `sku`, `description`, `type`, `taxId`, `commandId`, `brandId`, `houseId`, `inventoriable`, `purchasing_unit`, `consumption_unit`, `handles_serial`, `status`, `generic`, `created_at`, `updated_at`, `deleted_at`) VALUES
(560, NULL, 1, 'Producto 705', 'INT-00705', 'SKU-00705', 'Descripción del producto 705', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 18, 6, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(561, NULL, 1, 'Producto 866', 'INT-00866', 'SKU-00866', 'Descripción del producto 866', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 20, 33, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(562, NULL, 1, 'Producto 936', 'INT-00936', 'SKU-00936', 'Descripción del producto 936', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 32, 20, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(563, NULL, 1, 'Producto 630', 'INT-00630', 'SKU-00630', 'Descripción del producto 630', 'INSUMO', 2, NULL, NULL, NULL, 1, 36, 15, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(564, NULL, 1, 'Producto 349', 'INT-00349', 'SKU-00349', 'Descripción del producto 349', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 24, 32, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(565, NULL, 1, 'Producto 684', 'INT-00684', 'SKU-00684', 'Descripción del producto 684', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 27, 20, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(566, NULL, 1, 'Producto 268', 'INT-00268', 'SKU-00268', 'Descripción del producto 268', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 14, 9, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(567, NULL, 1, 'Producto 634', 'INT-00634', 'SKU-00634', 'Descripción del producto 634', 'COMBO', 2, NULL, NULL, NULL, 1, 21, 21, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(568, NULL, 1, 'Producto 926', 'INT-00926', 'SKU-00926', 'Descripción del producto 926', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 28, 26, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(569, NULL, 1, 'Producto 996', 'INT-00996', 'SKU-00996', 'Descripción del producto 996', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 4, 12, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(570, NULL, 1, 'Producto 445', 'INT-00445', 'SKU-00445', 'Descripción del producto 445', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 35, 5, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(571, NULL, 1, 'Producto 589', 'INT-00589', 'SKU-00589', 'Descripción del producto 589', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 20, 17, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(572, NULL, 1, 'Producto 642', 'INT-00642', 'SKU-00642', 'Descripción del producto 642', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 4, 6, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(573, NULL, 1, 'Producto 990', 'INT-00990', 'SKU-00990', 'Descripción del producto 990', 'COMBO', 2, NULL, NULL, NULL, 1, 15, 20, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(574, NULL, 1, 'Producto 426', 'INT-00426', 'SKU-00426', 'Descripción del producto 426', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 18, 8, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(575, NULL, 1, 'Producto 261', 'INT-00261', 'SKU-00261', 'Descripción del producto 261', 'INSUMO', 2, NULL, NULL, NULL, 1, 27, 17, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(576, NULL, 1, 'Producto 740', 'INT-00740', 'SKU-00740', 'Descripción del producto 740', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 36, 22, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(577, NULL, 1, 'Producto 185', 'INT-00185', 'SKU-00185', 'Descripción del producto 185', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 10, 15, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(578, NULL, 1, 'Producto 849', 'INT-00849', 'SKU-00849', 'Descripción del producto 849', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 28, 25, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(579, NULL, 1, 'Producto 696', 'INT-00696', 'SKU-00696', 'Descripción del producto 696', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 33, 30, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(580, NULL, 1, 'Producto 129', 'INT-00129', 'SKU-00129', 'Descripción del producto 129', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 5, 30, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(581, NULL, 1, 'Producto 137', 'INT-00137', 'SKU-00137', 'Descripción del producto 137', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 2, 22, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(582, NULL, 1, 'Producto 2', 'INT-00002', 'SKU-00002', 'Descripción del producto 2', 'COMBO', 2, NULL, NULL, NULL, 1, 26, 17, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(583, NULL, 1, 'Producto 159', 'INT-00159', 'SKU-00159', 'Descripción del producto 159', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 35, 32, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(584, NULL, 1, 'Producto 271', 'INT-00271', 'SKU-00271', 'Descripción del producto 271', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 16, 28, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(585, NULL, 1, 'Producto 38', 'INT-00038', 'SKU-00038', 'Descripción del producto 38', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 35, 22, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(586, NULL, 1, 'Producto 719', 'INT-00719', 'SKU-00719', 'Descripción del producto 719', 'INSUMO', 2, NULL, NULL, NULL, 1, 23, 12, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(587, NULL, 1, 'Producto 897', 'INT-00897', 'SKU-00897', 'Descripción del producto 897', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 18, 33, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(588, NULL, 1, 'Producto 337', 'INT-00337', 'SKU-00337', 'Descripción del producto 337', 'COMBO', 2, NULL, NULL, NULL, 1, 14, 29, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(589, NULL, 1, 'Producto 561', 'INT-00561', 'SKU-00561', 'Descripción del producto 561', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 23, 10, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(590, NULL, 1, 'Producto 104', 'INT-00104', 'SKU-00104', 'Descripción del producto 104', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 30, 28, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(591, NULL, 1, 'Producto 672', 'INT-00672', 'SKU-00672', 'Descripción del producto 672', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 27, 16, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(592, NULL, 1, 'Producto 840', 'INT-00840', 'SKU-00840', 'Descripción del producto 840', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 3, 20, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(593, NULL, 1, 'Producto 255', 'INT-00255', 'SKU-00255', 'Descripción del producto 255', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 9, 30, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(594, NULL, 1, 'Producto 758', 'INT-00758', 'SKU-00758', 'Descripción del producto 758', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 11, 1, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(595, NULL, 1, 'Producto 260', 'INT-00260', 'SKU-00260', 'Descripción del producto 260', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 14, 10, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(596, NULL, 1, 'Producto 478', 'INT-00478', 'SKU-00478', 'Descripción del producto 478', 'INSUMO', 2, NULL, NULL, NULL, 1, 20, 28, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(597, NULL, 1, 'Producto 964', 'INT-00964', 'SKU-00964', 'Descripción del producto 964', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 14, 18, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(598, NULL, 1, 'Producto 109', 'INT-00109', 'SKU-00109', 'Descripción del producto 109', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 28, 5, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(599, NULL, 1, 'Producto 967', 'INT-00967', 'SKU-00967', 'Descripción del producto 967', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 3, 4, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(600, NULL, 1, 'Producto 474', 'INT-00474', 'SKU-00474', 'Descripción del producto 474', 'COMBO', 2, NULL, NULL, NULL, 1, 34, 32, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(601, NULL, 1, 'Producto 259', 'INT-00259', 'SKU-00259', 'Descripción del producto 259', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 36, 9, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(602, NULL, 1, 'Producto 286', 'INT-00286', 'SKU-00286', 'Descripción del producto 286', 'INSUMO', 2, NULL, NULL, NULL, 1, 22, 26, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(603, NULL, 1, 'Producto 742', 'INT-00742', 'SKU-00742', 'Descripción del producto 742', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 4, 21, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(604, NULL, 1, 'Producto 186', 'INT-00186', 'SKU-00186', 'Descripción del producto 186', 'PRODUCIDO', 2, NULL, NULL, NULL, 1, 20, 24, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(605, NULL, 1, 'Producto 724', 'INT-00724', 'SKU-00724', 'Descripción del producto 724', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 17, 7, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(606, NULL, 1, 'Producto 631', 'INT-00631', 'SKU-00631', 'Descripción del producto 631', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 21, 8, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(607, NULL, 1, 'Producto 101', 'INT-00101', 'SKU-00101', 'Descripción del producto 101', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 29, 30, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(608, NULL, 1, 'Producto 986', 'INT-00986', 'SKU-00986', 'Descripción del producto 986', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 34, 1, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(609, NULL, 1, 'Producto 411', 'INT-00411', 'SKU-00411', 'Descripción del producto 411', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 8, 8, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(610, NULL, 1, 'Producto 913', 'INT-00913', 'SKU-00913', 'Descripción del producto 913', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 14, 15, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(611, NULL, 1, 'Producto 164', 'INT-00164', 'SKU-00164', 'Descripción del producto 164', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 27, 4, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(612, NULL, 1, 'Producto 630', 'INT-00630', 'SKU-00630', 'Descripción del producto 630', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 35, 36, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(613, NULL, 1, 'Producto 80', 'INT-00080', 'SKU-00080', 'Descripción del producto 80', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 13, 17, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(614, NULL, 1, 'Producto 273', 'INT-00273', 'SKU-00273', 'Descripción del producto 273', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 21, 23, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(615, NULL, 1, 'Producto 961', 'INT-00961', 'SKU-00961', 'Descripción del producto 961', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 19, 36, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(616, NULL, 1, 'Producto 392', 'INT-00392', 'SKU-00392', 'Descripción del producto 392', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 13, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(617, NULL, 1, 'Producto 601', 'INT-00601', 'SKU-00601', 'Descripción del producto 601', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 17, 12, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(618, NULL, 1, 'Producto 335', 'INT-00335', 'SKU-00335', 'Descripción del producto 335', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 36, 9, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(619, NULL, 1, 'Producto 124', 'INT-00124', 'SKU-00124', 'Descripción del producto 124', 'COMPRA NACIONAL', 2, NULL, 6, 2, 1, 36, 13, 0, 1, 0, '2025-11-27 14:37:09', '2026-01-19 20:58:10', NULL),
(620, NULL, 1, 'Producto 188', 'INT-00188', 'SKU-00188', 'Descripción del producto 188', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 20, 20, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(621, NULL, 1, 'Producto 155', 'INT-00155', 'SKU-00155', 'Descripción del producto 155', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 18, 2, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(622, NULL, 1, 'Producto 48', 'INT-00048', 'SKU-00048', 'Descripción del producto 48', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 30, 17, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(623, NULL, 1, 'Producto 855', 'INT-00855', 'SKU-00855', 'Descripción del producto 855', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 26, 24, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(624, NULL, 1, 'Producto 25', 'INT-00025', 'SKU-00025', 'Descripción del producto 25', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 28, 14, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(625, NULL, 1, 'Producto 186', 'INT-00186', 'SKU-00186', 'Descripción del producto 186', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 33, 9, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(626, NULL, 1, 'Producto 246', 'INT-00246', 'SKU-00246', 'Descripción del producto 246', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 2, 33, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(627, NULL, 1, 'Producto 607', 'INT-00607', 'SKU-00607', 'Descripción del producto 607', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 21, 21, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(628, NULL, 1, 'Producto 132', 'INT-00132', 'SKU-00132', 'Descripción del producto 132', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 1, 32, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(629, NULL, 1, 'Producto 975', 'INT-00975', 'SKU-00975', 'Descripción del producto 975', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 13, 34, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(630, NULL, 1, 'Producto 792', 'INT-00792', 'SKU-00792', 'Descripción del producto 792', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 2, 33, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(631, NULL, 1, 'Producto 746', 'INT-00746', 'SKU-00746', 'Descripción del producto 746', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 9, 13, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(632, NULL, 1, 'Producto 671', 'INT-00671', 'SKU-00671', 'Descripción del producto 671', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 31, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(633, NULL, 1, 'Producto 572', 'INT-00572', 'SKU-00572', 'Descripción del producto 572', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 30, 27, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(634, NULL, 1, 'Producto 905', 'INT-00905', 'SKU-00905', 'Descripción del producto 905', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 30, 3, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(635, NULL, 1, 'Producto 943', 'INT-00943', 'SKU-00943', 'Descripción del producto 943', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 18, 22, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(636, NULL, 1, 'Producto 479', 'INT-00479', 'SKU-00479', 'Descripción del producto 479', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 2, 28, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(637, NULL, 1, 'Producto 274', 'INT-00274', 'SKU-00274', 'Descripción del producto 274', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 16, 26, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(638, NULL, 1, 'Producto 221', 'INT-00221', 'SKU-00221', 'Descripción del producto 221', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 8, 28, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(639, NULL, 1, 'Producto 526', 'INT-00526', 'SKU-00526', 'Descripción del producto 526', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 30, 7, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(640, NULL, 1, 'Producto 880', 'INT-00880', 'SKU-00880', 'Descripción del producto 880', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 18, 29, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(641, NULL, 1, 'Producto 188', 'INT-00188', 'SKU-00188', 'Descripción del producto 188', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 19, 23, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(642, NULL, 1, 'Producto 30', 'INT-00030', 'SKU-00030', 'Descripción del producto 30', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 32, 32, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(643, NULL, 1, 'Producto 145', 'INT-00145', 'SKU-00145', 'Descripción del producto 145', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 24, 16, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(644, NULL, 1, 'Producto 517', 'INT-00517', 'SKU-00517', 'Descripción del producto 517', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 28, 23, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(645, NULL, 1, 'Producto 235', 'INT-00235', 'SKU-00235', 'Descripción del producto 235', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 16, 4, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(646, NULL, 1, 'Producto 337', 'INT-00337', 'SKU-00337', 'Descripción del producto 337', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 11, 8, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(647, NULL, 1, 'Producto 633', 'INT-00633', 'SKU-00633', 'Descripción del producto 633', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 8, 27, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(648, NULL, 1, 'Producto 897', 'INT-00897', 'SKU-00897', 'Descripción del producto 897', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 23, 23, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(649, NULL, 1, 'Producto 567', 'INT-00567', 'SKU-00567', 'Descripción del producto 567', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 17, 4, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(650, NULL, 1, 'Producto 519', 'INT-00519', 'SKU-00519', 'Descripción del producto 519', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 33, 18, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(651, NULL, 1, 'Producto 609', 'INT-00609', 'SKU-00609', 'Descripción del producto 609', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 12, 9, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(652, NULL, 1, 'Producto 472', 'INT-00472', 'SKU-00472', 'Descripción del producto 472', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 20, 31, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(653, NULL, 1, 'Producto 731', 'INT-00731', 'SKU-00731', 'Descripción del producto 731', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 32, 17, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(654, NULL, 1, 'Producto 131', 'INT-00131', 'SKU-00131', 'Descripción del producto 131', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 28, 27, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(655, NULL, 1, 'Producto 75', 'INT-00075', 'SKU-00075', 'Descripción del producto 75', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 30, 30, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(656, NULL, 1, 'Producto 411', 'INT-00411', 'SKU-00411', 'Descripción del producto 411', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 12, 24, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(657, NULL, 1, 'Producto 998', 'INT-00998', 'SKU-00998', 'Descripción del producto 998', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 1, 9, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(658, NULL, 1, 'Producto 206', 'INT-00206', 'SKU-00206', 'Descripción del producto 206', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 13, 13, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(659, NULL, 1, 'Producto 797', 'INT-00797', 'SKU-00797', 'Descripción del producto 797', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 20, 5, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(660, NULL, 1, 'Producto 613', 'INT-00613', 'SKU-00613', 'Descripción del producto 613', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 12, 23, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(661, NULL, 1, 'Producto 537', 'INT-00537', 'SKU-00537', 'Descripción del producto 537', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 27, 32, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(662, NULL, 1, 'Producto 994', 'INT-00994', 'SKU-00994', 'Descripción del producto 994', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 15, 19, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(663, NULL, 1, 'Producto 199', 'INT-00199', 'SKU-00199', 'Descripción del producto 199', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 14, 5, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(664, NULL, 1, 'Producto 689', 'INT-00689', 'SKU-00689', 'Descripción del producto 689', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 35, 36, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(665, NULL, 1, 'Producto 124', 'INT-00124', 'SKU-00124', 'Descripción del producto 124', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 33, 12, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(666, NULL, 1, 'Producto 297', 'INT-00297', 'SKU-00297', 'Descripción del producto 297', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 14, 4, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(667, NULL, 1, 'Producto 52', 'INT-00052', 'SKU-00052', 'Descripción del producto 52', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 15, 29, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(668, NULL, 1, 'Producto 953', 'INT-00953', 'SKU-00953', 'Descripción del producto 953', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 4, 28, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(669, NULL, 1, 'Producto 709', 'INT-00709', 'SKU-00709', 'Descripción del producto 709', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 1, 13, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(670, NULL, 1, 'Producto 317', 'INT-00317', 'SKU-00317', 'Descripción del producto 317', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 27, 17, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(671, NULL, 1, 'Producto 473', 'INT-00473', 'SKU-00473', 'Descripción del producto 473', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 4, 4, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(672, NULL, 1, 'Producto 999', 'INT-00999', 'SKU-00999', 'Descripción del producto 999', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 36, 9, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(673, NULL, 1, 'Producto 788', 'INT-00788', 'SKU-00788', 'Descripción del producto 788', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 36, 13, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(674, NULL, 1, 'Producto 231', 'INT-00231', 'SKU-00231', 'Descripción del producto 231', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 27, 17, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(675, NULL, 1, 'Producto 682', 'INT-00682', 'SKU-00682', 'Descripción del producto 682', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 32, 2, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(676, NULL, 1, 'Producto 294', 'INT-00294', 'SKU-00294', 'Descripción del producto 294', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 16, 7, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(677, NULL, 1, 'Producto 558', 'INT-00558', 'SKU-00558', 'Descripción del producto 558', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 17, 9, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(678, NULL, 1, 'Producto 890', 'INT-00890', 'SKU-00890', 'Descripción del producto 890', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 4, 13, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(679, NULL, 1, 'Producto 996', 'INT-00996', 'SKU-00996', 'Descripción del producto 996', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 26, 19, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(680, NULL, 1, 'Producto 755', 'INT-00755', 'SKU-00755', 'Descripción del producto 755', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 20, 36, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(681, NULL, 1, 'Producto 262', 'INT-00262', 'SKU-00262', 'Descripción del producto 262', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 8, 21, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(682, NULL, 1, 'Producto 184', 'INT-00184', 'SKU-00184', 'Descripción del producto 184', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 36, 2, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(683, NULL, 1, 'Producto 209', 'INT-00209', 'SKU-00209', 'Descripción del producto 209', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 36, 11, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(684, NULL, 1, 'Producto 687', 'INT-00687', 'SKU-00687', 'Descripción del producto 687', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 30, 21, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(685, NULL, 1, 'Producto 233', 'INT-00233', 'SKU-00233', 'Descripción del producto 233', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 15, 34, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(686, NULL, 1, 'Producto 788', 'INT-00788', 'SKU-00788', 'Descripción del producto 788', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 21, 24, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(687, NULL, 1, 'Producto 885', 'INT-00885', 'SKU-00885', 'Descripción del producto 885', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 28, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(688, NULL, 1, 'Producto 410', 'INT-00410', 'SKU-00410', 'Descripción del producto 410', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 33, 7, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(689, NULL, 1, 'Producto 346', 'INT-00346', 'SKU-00346', 'Descripción del producto 346', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 20, 14, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(690, NULL, 1, 'Producto 27', 'INT-00027', 'SKU-00027', 'Descripción del producto 27', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 29, 24, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(691, NULL, 1, 'Producto 933', 'INT-00933', 'SKU-00933', 'Descripción del producto 933', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 22, 21, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(692, NULL, 1, 'Producto 514', 'INT-00514', 'SKU-00514', 'Descripción del producto 514', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 31, 29, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(693, NULL, 1, 'Producto 790', 'INT-00790', 'SKU-00790', 'Descripción del producto 790', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 23, 23, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(694, NULL, 1, 'Producto 28', 'INT-00028', 'SKU-00028', 'Descripción del producto 28', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 4, 5, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(695, NULL, 1, 'Producto 987', 'INT-00987', 'SKU-00987', 'Descripción del producto 987', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 9, 18, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(696, NULL, 1, 'Producto 401', 'INT-00401', 'SKU-00401', 'Descripción del producto 401', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 21, 18, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(697, NULL, 1, 'Producto 992', 'INT-00992', 'SKU-00992', 'Descripción del producto 992', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 31, 11, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(698, NULL, 1, 'Producto 313', 'INT-00313', 'SKU-00313', 'Descripción del producto 313', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 3, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(699, NULL, 1, 'Producto 6', 'INT-00006', 'SKU-00006', 'Descripción del producto 6', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 22, 22, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(700, NULL, 1, 'Producto 238', 'INT-00238', 'SKU-00238', 'Descripción del producto 238', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 12, 2, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(701, NULL, 1, 'Producto 754', 'INT-00754', 'SKU-00754', 'Descripción del producto 754', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 21, 7, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(702, NULL, 1, 'Producto 56', 'INT-00056', 'SKU-00056', 'Descripción del producto 56', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 16, 2, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(703, NULL, 1, 'Producto 40', 'INT-00040', 'SKU-00040', 'Descripción del producto 40', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 21, 8, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(704, NULL, 1, 'Producto 680', 'INT-00680', 'SKU-00680', 'Descripción del producto 680', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 28, 5, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(705, NULL, 1, 'Producto 187', 'INT-00187', 'SKU-00187', 'Descripción del producto 187', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 30, 9, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(706, NULL, 1, 'Producto 389', 'INT-00389', 'SKU-00389', 'Descripción del producto 389', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 31, 25, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(707, NULL, 1, 'Producto 896', 'INT-00896', 'SKU-00896', 'Descripción del producto 896', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 2, 11, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(708, NULL, 1, 'Producto 20', 'INT-00020', 'SKU-00020', 'Descripción del producto 20', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 10, 26, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(709, NULL, 1, 'Producto 512', 'INT-00512', 'SKU-00512', 'Descripción del producto 512', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 9, 28, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(710, NULL, 1, 'Producto 719', 'INT-00719', 'SKU-00719', 'Descripción del producto 719', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 22, 4, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(711, NULL, 1, 'Producto 434', 'INT-00434', 'SKU-00434', 'Descripción del producto 434', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 27, 7, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(712, NULL, 1, 'Producto 475', 'INT-00475', 'SKU-00475', 'Descripción del producto 475', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 2, 16, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(713, NULL, 1, 'Producto 604', 'INT-00604', 'SKU-00604', 'Descripción del producto 604', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 30, 5, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(714, NULL, 1, 'Producto 775', 'INT-00775', 'SKU-00775', 'Descripción del producto 775', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 27, 11, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(715, NULL, 1, 'Producto 43', 'INT-00043', 'SKU-00043', 'Descripción del producto 43', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 22, 4, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(716, NULL, 1, 'Producto 766', 'INT-00766', 'SKU-00766', 'Descripción del producto 766', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 29, 1, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(717, NULL, 1, 'Producto 321', 'INT-00321', 'SKU-00321', 'Descripción del producto 321', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 33, 18, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(718, NULL, 1, 'Producto 221', 'INT-00221', 'SKU-00221', 'Descripción del producto 221', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 7, 13, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(719, NULL, 1, 'Producto 419', 'INT-00419', 'SKU-00419', 'Descripción del producto 419', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 26, 20, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(720, NULL, 1, 'Producto 135', 'INT-00135', 'SKU-00135', 'Descripción del producto 135', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 27, 12, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(721, NULL, 1, 'Producto 9', 'INT-00009', 'SKU-00009', 'Descripción del producto 9', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 11, 23, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(722, NULL, 1, 'Producto 840', 'INT-00840', 'SKU-00840', 'Descripción del producto 840', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 35, 2, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(723, NULL, 1, 'Producto 980', 'INT-00980', 'SKU-00980', 'Descripción del producto 980', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 29, 3, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(724, NULL, 1, 'Producto 423', 'INT-00423', 'SKU-00423', 'Descripción del producto 423', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 31, 20, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(725, NULL, 1, 'Producto 189', 'INT-00189', 'SKU-00189', 'Descripción del producto 189', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 3, 3, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(726, NULL, 1, 'Producto 7', 'INT-00007', 'SKU-00007', 'Descripción del producto 7', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 12, 26, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(727, NULL, 1, 'Producto 169', 'INT-00169', 'SKU-00169', 'Descripción del producto 169', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 21, 13, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(728, NULL, 1, 'Producto 562', 'INT-00562', 'SKU-00562', 'Descripción del producto 562', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 12, 26, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(729, NULL, 1, 'Producto 182', 'INT-00182', 'SKU-00182', 'Descripción del producto 182', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 32, 26, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(730, NULL, 1, 'Producto 23', 'INT-00023', 'SKU-00023', 'Descripción del producto 23', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 16, 32, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(731, NULL, 1, 'Producto 872', 'INT-00872', 'SKU-00872', 'Descripción del producto 872', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 26, 36, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(732, NULL, 1, 'Producto 497', 'INT-00497', 'SKU-00497', 'Descripción del producto 497', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 24, 22, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(733, NULL, 1, 'Producto 459', 'INT-00459', 'SKU-00459', 'Descripción del producto 459', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 14, 33, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(734, NULL, 1, 'Producto 304', 'INT-00304', 'SKU-00304', 'Descripción del producto 304', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 34, 13, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(735, NULL, 1, 'Producto 614', 'INT-00614', 'SKU-00614', 'Descripción del producto 614', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 15, 26, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(736, NULL, 1, 'Producto 245', 'INT-00245', 'SKU-00245', 'Descripción del producto 245', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 10, 33, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(737, NULL, 1, 'Producto 852', 'INT-00852', 'SKU-00852', 'Descripción del producto 852', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 8, 18, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(738, NULL, 1, 'Producto 351', 'INT-00351', 'SKU-00351', 'Descripción del producto 351', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 30, 5, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(739, NULL, 1, 'Producto 914', 'INT-00914', 'SKU-00914', 'Descripción del producto 914', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 18, 23, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(740, NULL, 1, 'Producto 387', 'INT-00387', 'SKU-00387', 'Descripción del producto 387', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 1, 5, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(741, NULL, 1, 'Producto 718', 'INT-00718', 'SKU-00718', 'Descripción del producto 718', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 8, 1, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(742, NULL, 1, 'Producto 648', 'INT-00648', 'SKU-00648', 'Descripción del producto 648', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 32, 15, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(743, NULL, 1, 'Producto 97', 'INT-00097', 'SKU-00097', 'Descripción del producto 97', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 31, 33, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(744, NULL, 1, 'Producto 781', 'INT-00781', 'SKU-00781', 'Descripción del producto 781', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 14, 24, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(745, NULL, 1, 'Producto 628', 'INT-00628', 'SKU-00628', 'Descripción del producto 628', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 24, 2, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(746, NULL, 1, 'Producto 375', 'INT-00375', 'SKU-00375', 'Descripción del producto 375', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 13, 2, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(747, NULL, 1, 'Producto 719', 'INT-00719', 'SKU-00719', 'Descripción del producto 719', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 8, 31, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(748, NULL, 1, 'Producto 291', 'INT-00291', 'SKU-00291', 'Descripción del producto 291', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 20, 31, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(749, NULL, 1, 'Producto 143', 'INT-00143', 'SKU-00143', 'Descripción del producto 143', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 27, 31, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(750, NULL, 1, 'Producto 361', 'INT-00361', 'SKU-00361', 'Descripción del producto 361', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 31, 8, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(751, NULL, 1, 'Producto 749', 'INT-00749', 'SKU-00749', 'Descripción del producto 749', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 1, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(752, NULL, 1, 'Producto 800', 'INT-00800', 'SKU-00800', 'Descripción del producto 800', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 7, 5, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(753, NULL, 1, 'Producto 140', 'INT-00140', 'SKU-00140', 'Descripción del producto 140', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 27, 26, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(754, NULL, 1, 'Producto 814', 'INT-00814', 'SKU-00814', 'Descripción del producto 814', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 3, 4, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(755, NULL, 1, 'Producto 331', 'INT-00331', 'SKU-00331', 'Descripción del producto 331', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 25, 12, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(756, NULL, 1, 'Producto 990', 'INT-00990', 'SKU-00990', 'Descripción del producto 990', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 7, 25, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(757, NULL, 1, 'Producto 796', 'INT-00796', 'SKU-00796', 'Descripción del producto 796', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 21, 10, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(758, NULL, 1, 'Producto 75', 'INT-00075', 'SKU-00075', 'Descripción del producto 75', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 19, 4, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(759, NULL, 1, 'Producto 764', 'INT-00764', 'SKU-00764', 'Descripción del producto 764', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 30, 8, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(760, NULL, 1, 'Producto 372', 'INT-00372', 'SKU-00372', 'Descripción del producto 372', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 14, 21, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(761, NULL, 1, 'Producto 103-I', 'INT-0103', 'SKU-0103', 'Descripción del producto 103', 'COMPRA NACIONAL', 2, NULL, 9, 3, 1, 12, 16, 0, 1, 0, '2025-11-27 14:37:09', '2026-02-16 14:39:04', NULL),
(762, NULL, 1, 'Producto 380', 'INT-00380', 'SKU-00380', 'Descripción del producto 380', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 27, 20, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(763, NULL, 1, 'Producto 524', 'INT-00524', 'SKU-00524', 'Descripción del producto 524', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 25, 15, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(764, NULL, 1, 'Producto 167', 'INT-00167', 'SKU-00167', 'Descripción del producto 167', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 16, 8, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(765, NULL, 1, 'Producto 157', 'INT-00157', 'SKU-00157', 'Descripción del producto 157', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 27, 9, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(766, NULL, 1, 'Producto 426', 'INT-00426', 'SKU-00426', 'Descripción del producto 426', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 31, 34, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(767, NULL, 1, 'Producto 217', 'INT-00217', 'SKU-00217', 'Descripción del producto 217', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 34, 17, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(768, NULL, 1, 'Producto 552', 'INT-00552', 'SKU-00552', 'Descripción del producto 552', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 10, 28, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(769, NULL, 1, 'Producto 85', 'INT-00085', 'SKU-00085', 'Descripción del producto 85', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 2, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(770, NULL, 1, 'Producto 280', 'INT-00280', 'SKU-00280', 'Descripción del producto 280', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 17, 24, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(771, NULL, 1, 'Producto 444', 'INT-00444', 'SKU-00444', 'Descripción del producto 444', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 12, 13, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(772, NULL, 1, 'Producto 965', 'INT-00965', 'SKU-00965', 'Descripción del producto 965', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 7, 27, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(773, NULL, 1, 'Producto 673', 'INT-00673', 'SKU-00673', 'Descripción del producto 673', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 21, 3, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(774, NULL, 1, 'Producto 378', 'INT-00378', 'SKU-00378', 'Descripción del producto 378', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 4, 31, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(775, NULL, 1, 'Producto 133', 'INT-00133', 'SKU-00133', 'Descripción del producto 133', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 17, 27, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(776, NULL, 1, 'Producto 825', 'INT-00825', 'SKU-00825', 'Descripción del producto 825', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 28, 27, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(777, NULL, 1, 'Producto 854', 'INT-00854', 'SKU-00854', 'Descripción del producto 854', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 7, 6, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(778, NULL, 1, 'Producto 530', 'INT-00530', 'SKU-00530', 'Descripción del producto 530', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 22, 33, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(779, NULL, 1, 'Producto 608', 'INT-00608', 'SKU-00608', 'Descripción del producto 608', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 1, 8, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(780, NULL, 1, 'Producto 954', 'INT-00954', 'SKU-00954', 'Descripción del producto 954', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 20, 12, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(781, NULL, 1, 'Producto 731', 'INT-00731', 'SKU-00731', 'Descripción del producto 731', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 8, 32, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(782, NULL, 1, 'Producto 561', 'INT-00561', 'SKU-00561', 'Descripción del producto 561', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 15, 10, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(783, NULL, 1, 'Producto 769', 'INT-00769', 'SKU-00769', 'Descripción del producto 769', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 28, 32, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(784, NULL, 1, 'Producto 731', 'INT-00731', 'SKU-00731', 'Descripción del producto 731', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 32, 24, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(785, NULL, 1, 'Producto 423', 'INT-00423', 'SKU-00423', 'Descripción del producto 423', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 16, 17, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(786, NULL, 1, 'Producto 496', 'INT-00496', 'SKU-00496', 'Descripción del producto 496', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 19, 31, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(787, NULL, 1, 'Producto 993', 'INT-00993', 'SKU-00993', 'Descripción del producto 993', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 2, 14, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(788, NULL, 1, 'Producto 798', 'INT-00798', 'SKU-00798', 'Descripción del producto 798', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 35, 21, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(789, NULL, 1, 'Producto 477', 'INT-00477', 'SKU-00477', 'Descripción del producto 477', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 23, 34, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(790, NULL, 1, 'Producto 150', 'INT-00150', 'SKU-00150', 'Descripción del producto 150', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 24, 19, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(791, NULL, 1, 'Producto 223', 'INT-00223', 'SKU-00223', 'Descripción del producto 223', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 27, 33, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(792, NULL, 1, 'Producto 733', 'INT-00733', 'SKU-00733', 'Descripción del producto 733', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 30, 11, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(793, NULL, 1, 'Producto 270', 'INT-00270', 'SKU-00270', 'Descripción del producto 270', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 15, 5, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(794, NULL, 1, 'Producto 774', 'INT-00774', 'SKU-00774', 'Descripción del producto 774', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 31, 2, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(795, NULL, 1, 'Producto 791', 'INT-00791', 'SKU-00791', 'Descripción del producto 791', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 35, 8, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(796, NULL, 1, 'Producto 762', 'INT-00762', 'SKU-00762', 'Descripción del producto 762', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 2, 15, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(797, NULL, 1, 'Producto 625', 'INT-00625', 'SKU-00625', 'Descripción del producto 625', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 30, 16, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(798, NULL, 1, 'Producto 9', 'INT-00009', 'SKU-00009', 'Descripción del producto 9', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 31, 32, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(799, NULL, 1, 'Producto 330', 'INT-00330', 'SKU-00330', 'Descripción del producto 330', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 30, 6, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(800, NULL, 1, 'Producto 108', 'INT-00108', 'SKU-00108', 'Descripción del producto 108', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 14, 22, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(801, NULL, 1, 'Producto 412', 'INT-00412', 'SKU-00412', 'Descripción del producto 412', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 21, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(802, NULL, 1, 'Producto 566', 'INT-00566', 'SKU-00566', 'Descripción del producto 566', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 7, 21, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(803, NULL, 1, 'Producto 929', 'INT-00929', 'SKU-00929', 'Descripción del producto 929', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 13, 15, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(804, NULL, 1, 'Producto 82', 'INT-00082', 'SKU-00082', 'Descripción del producto 82', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 16, 28, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(805, NULL, 1, 'Producto 238', 'INT-00238', 'SKU-00238', 'Descripción del producto 238', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 3, 4, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(806, NULL, 1, 'Producto 214', 'INT-00214', 'SKU-00214', 'Descripción del producto 214', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 32, 8, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(807, NULL, 1, 'Producto 492', 'INT-00492', 'SKU-00492', 'Descripción del producto 492', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 30, 21, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(808, NULL, 1, 'Producto 881', 'INT-00881', 'SKU-00881', 'Descripción del producto 881', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 21, 18, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(809, NULL, 1, 'Producto 774', 'INT-00774', 'SKU-00774', 'Descripción del producto 774', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 33, 24, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(810, NULL, 1, 'Producto 306', 'INT-00306', 'SKU-00306', 'Descripción del producto 306', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 29, 15, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(811, NULL, 1, 'Producto 598', 'INT-00598', 'SKU-00598', 'Descripción del producto 598', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 32, 1, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(812, NULL, 1, 'Producto 953', 'INT-00953', 'SKU-00953', 'Descripción del producto 953', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 6, 6, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(813, NULL, 1, 'Producto 719', 'INT-00719', 'SKU-00719', 'Descripción del producto 719', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 19, 7, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(814, NULL, 1, 'Producto 354', 'INT-00354', 'SKU-00354', 'Descripción del producto 354', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 19, 22, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(815, NULL, 1, 'Producto 452', 'INT-00452', 'SKU-00452', 'Descripción del producto 452', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 21, 3, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(816, NULL, 1, 'Producto 979', 'INT-00979', 'SKU-00979', 'Descripción del producto 979', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 24, 5, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(817, NULL, 1, 'Producto 797', 'INT-00797', 'SKU-00797', 'Descripción del producto 797', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 22, 17, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(818, NULL, 1, 'Producto 620', 'INT-00620', 'SKU-00620', 'Descripción del producto 620', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 21, 8, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(819, NULL, 1, 'Producto 163', 'INT-00163', 'SKU-00163', 'Descripción del producto 163', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 19, 14, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(820, NULL, 1, 'Producto 67', 'INT-00067', 'SKU-00067', 'Descripción del producto 67', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 16, 3, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(821, NULL, 1, 'Producto 895', 'INT-00895', 'SKU-00895', 'Descripción del producto 895', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 4, 26, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(822, NULL, 1, 'Producto 780', 'INT-00780', 'SKU-00780', 'Descripción del producto 780', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 36, 16, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(823, NULL, 1, 'Producto 650', 'INT-00650', 'SKU-00650', 'Descripción del producto 650', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 29, 9, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(824, NULL, 1, 'Producto 559', 'INT-00559', 'SKU-00559', 'Descripción del producto 559', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 19, 24, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(825, NULL, 1, 'Producto 795', 'INT-00795', 'SKU-00795', 'Descripción del producto 795', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 5, 17, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(826, NULL, 1, 'Producto 226', 'INT-00226', 'SKU-00226', 'Descripción del producto 226', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 8, 24, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(827, NULL, 1, 'Producto 895', 'INT-00895', 'SKU-00895', 'Descripción del producto 895', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 3, 31, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(828, NULL, 1, 'Producto 444', 'INT-00444', 'SKU-00444', 'Descripción del producto 444', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 3, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(829, NULL, 1, 'Producto 211', 'INT-00211', 'SKU-00211', 'Descripción del producto 211', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 28, 32, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(830, NULL, 1, 'Producto 15', 'INT-00015', 'SKU-00015', 'Descripción del producto 15', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 28, 3, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(831, NULL, 1, 'Producto 339', 'INT-00339', 'SKU-00339', 'Descripción del producto 339', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 17, 33, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(832, NULL, 1, 'Producto 59', 'INT-00059', 'SKU-00059', 'Descripción del producto 59', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 23, 32, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL);
INSERT INTO `inv_items` (`id`, `api_data_id`, `categoryId`, `name`, `internal_code`, `sku`, `description`, `type`, `taxId`, `commandId`, `brandId`, `houseId`, `inventoriable`, `purchasing_unit`, `consumption_unit`, `handles_serial`, `status`, `generic`, `created_at`, `updated_at`, `deleted_at`) VALUES
(833, NULL, 1, 'Producto 590', 'INT-00590', 'SKU-00590', 'Descripción del producto 590', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 1, 33, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(834, NULL, 1, 'Producto 628', 'INT-00628', 'SKU-00628', 'Descripción del producto 628', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 28, 28, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(835, NULL, 1, 'Producto 343', 'INT-00343', 'SKU-00343', 'Descripción del producto 343', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 6, 33, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(836, NULL, 1, 'Producto 41', 'INT-00041', 'SKU-00041', 'Descripción del producto 41', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 25, 34, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(837, NULL, 1, 'Producto 665', 'INT-00665', 'SKU-00665', 'Descripción del producto 665', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 5, 32, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(838, NULL, 1, 'Producto 671', 'INT-00671', 'SKU-00671', 'Descripción del producto 671', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 33, 34, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(839, NULL, 1, 'Producto 794', 'INT-00794', 'SKU-00794', 'Descripción del producto 794', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 7, 33, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(840, NULL, 1, 'Producto 188', 'INT-00188', 'SKU-00188', 'Descripción del producto 188', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 34, 2, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(841, NULL, 1, 'Producto 124', 'INT-00124', 'SKU-00124', 'Descripción del producto 124', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 20, 9, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(842, NULL, 1, 'Producto 759', 'INT-00759', 'SKU-00759', 'Descripción del producto 759', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 29, 32, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(843, NULL, 1, 'Producto 327', 'INT-00327', 'SKU-00327', 'Descripción del producto 327', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 29, 36, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(844, NULL, 1, 'Producto 215', 'INT-00215', 'SKU-00215', 'Descripción del producto 215', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 20, 33, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(845, NULL, 1, 'Producto 199', 'INT-00199', 'SKU-00199', 'Descripción del producto 199', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 16, 27, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(846, NULL, 1, 'Producto 355', 'INT-00355', 'SKU-00355', 'Descripción del producto 355', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 20, 26, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(847, NULL, 1, 'Producto 889', 'INT-00889', 'SKU-00889', 'Descripción del producto 889', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 20, 15, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(848, NULL, 1, 'Producto 692', 'INT-00692', 'SKU-00692', 'Descripción del producto 692', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 4, 36, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(849, NULL, 1, 'Producto 819', 'INT-00819', 'SKU-00819', 'Descripción del producto 819', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 3, 13, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(850, NULL, 1, 'Producto 57', 'INT-00057', 'SKU-00057', 'Descripción del producto 57', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 34, 10, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(851, NULL, 1, 'Producto 296', 'INT-00296', 'SKU-00296', 'Descripción del producto 296', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 7, 16, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(852, NULL, 1, 'Producto 478', 'INT-00478', 'SKU-00478', 'Descripción del producto 478', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 26, 2, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(853, NULL, 1, 'Producto 595', 'INT-00595', 'SKU-00595', 'Descripción del producto 595', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 35, 3, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(854, NULL, 1, 'Producto 681', 'INT-00681', 'SKU-00681', 'Descripción del producto 681', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 24, 17, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(855, NULL, 1, 'Producto 284', 'INT-00284', 'SKU-00284', 'Descripción del producto 284', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 9, 20, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(856, NULL, 1, 'Producto 53', 'INT-00053', 'SKU-00053', 'Descripción del producto 53', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 12, 8, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(857, NULL, 1, 'Producto 459', 'INT-00459', 'SKU-00459', 'Descripción del producto 459', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 7, 29, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(858, NULL, 1, 'Producto 472', 'INT-00472', 'SKU-00472', 'Descripción del producto 472', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 14, 33, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(859, NULL, 1, 'Producto 82', 'INT-00082', 'SKU-00082', 'Descripción del producto 82', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 30, 12, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(860, NULL, 1, 'Producto 55', 'INT-00055', 'SKU-00055', 'Descripción del producto 55', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 36, 11, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(861, NULL, 1, 'Producto 546', 'INT-00546', 'SKU-00546', 'Descripción del producto 546', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 1, 9, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(862, NULL, 1, 'Producto 707', 'INT-00707', 'SKU-00707', 'Descripción del producto 707', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 7, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(863, NULL, 1, 'Producto 833', 'INT-00833', 'SKU-00833', 'Descripción del producto 833', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 2, 16, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(864, NULL, 1, 'Producto 83', 'INT-00083', 'SKU-00083', 'Descripción del producto 83', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 36, 23, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(865, NULL, 1, 'Producto 227', 'INT-00227', 'SKU-00227', 'Descripción del producto 227', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 36, 15, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(866, NULL, 1, 'Producto 176', 'INT-00176', 'SKU-00176', 'Descripción del producto 176', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 19, 8, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(867, NULL, 1, 'Producto 171', 'INT-00171', 'SKU-00171', 'Descripción del producto 171', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 1, 18, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(868, NULL, 1, 'Producto 632', 'INT-00632', 'SKU-00632', 'Descripción del producto 632', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 25, 28, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(869, NULL, 1, 'Producto 538', 'INT-00538', 'SKU-00538', 'Descripción del producto 538', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 36, 31, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(870, NULL, 1, 'Producto 936', 'INT-00936', 'SKU-00936', 'Descripción del producto 936', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 13, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(871, NULL, 1, 'Producto 425', 'INT-00425', 'SKU-00425', 'Descripción del producto 425', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 29, 10, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(872, NULL, 1, 'Producto 797', 'INT-00797', 'SKU-00797', 'Descripción del producto 797', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 16, 17, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(873, NULL, 1, 'Producto 304', 'INT-00304', 'SKU-00304', 'Descripción del producto 304', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 4, 2, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(874, NULL, 1, 'Producto 875', 'INT-00875', 'SKU-00875', 'Descripción del producto 875', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 20, 33, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(875, NULL, 1, 'Producto 615', 'INT-00615', 'SKU-00615', 'Descripción del producto 615', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 10, 17, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(876, NULL, 1, 'Producto 192', 'INT-00192', 'SKU-00192', 'Descripción del producto 192', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 6, 6, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(877, NULL, 1, 'Producto 815', 'INT-00815', 'SKU-00815', 'Descripción del producto 815', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 6, 12, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(878, NULL, 1, 'Producto 433', 'INT-00433', 'SKU-00433', 'Descripción del producto 433', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 35, 6, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(879, NULL, 1, 'Producto 204', 'INT-00204', 'SKU-00204', 'Descripción del producto 204', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 33, 19, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(880, NULL, 1, 'Producto 287', 'INT-00287', 'SKU-00287', 'Descripción del producto 287', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 23, 36, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(881, NULL, 1, 'Producto 124', 'INT-00124', 'SKU-00124', 'Descripción del producto 124', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 24, 33, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(882, NULL, 1, 'Producto 606', 'INT-00606', 'SKU-00606', 'Descripción del producto 606', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 28, 25, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(883, NULL, 1, 'Producto 887', 'INT-00887', 'SKU-00887', 'Descripción del producto 887', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 4, 7, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(884, NULL, 1, 'Producto 358', 'INT-00358', 'SKU-00358', 'Descripción del producto 358', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 36, 19, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(885, NULL, 1, 'Producto 650', 'INT-00650', 'SKU-00650', 'Descripción del producto 650', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 18, 36, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(886, NULL, 1, 'Producto 744', 'INT-00744', 'SKU-00744', 'Descripción del producto 744', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 18, 20, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(887, NULL, 1, 'Producto 478', 'INT-00478', 'SKU-00478', 'Descripción del producto 478', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 11, 2, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(888, NULL, 1, 'Producto 642', 'INT-00642', 'SKU-00642', 'Descripción del producto 642', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 32, 32, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(889, NULL, 1, 'Producto 853', 'INT-00853', 'SKU-00853', 'Descripción del producto 853', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 32, 28, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(890, NULL, 1, 'Producto 780', 'INT-00780', 'SKU-00780', 'Descripción del producto 780', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 21, 19, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(891, NULL, 1, 'Producto 337', 'INT-00337', 'SKU-00337', 'Descripción del producto 337', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 6, 27, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(892, NULL, 1, 'Producto 214', 'INT-00214', 'SKU-00214', 'Descripción del producto 214', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 8, 23, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(893, NULL, 1, 'Producto 861', 'INT-00861', 'SKU-00861', 'Descripción del producto 861', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 16, 16, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(894, NULL, 1, 'Producto 988', 'INT-00988', 'SKU-00988', 'Descripción del producto 988', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 8, 2, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(895, NULL, 1, 'Producto 690', 'INT-00690', 'SKU-00690', 'Descripción del producto 690', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 27, 7, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(896, NULL, 1, 'Producto 929', 'INT-00929', 'SKU-00929', 'Descripción del producto 929', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 29, 2, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(897, NULL, 1, 'Producto 772', 'INT-00772', 'SKU-00772', 'Descripción del producto 772', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 3, 28, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(898, NULL, 1, 'Producto 98', 'INT-00098', 'SKU-00098', 'Descripción del producto 98', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 14, 25, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(899, NULL, 1, 'Producto 927', 'INT-00927', 'SKU-00927', 'Descripción del producto 927', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 1, 6, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(900, NULL, 1, 'Producto 157', 'INT-00157', 'SKU-00157', 'Descripción del producto 157', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 25, 7, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(901, NULL, 1, 'Producto 995', 'INT-00995', 'SKU-00995', 'Descripción del producto 995', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 35, 6, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(902, NULL, 1, 'Producto 510', 'INT-00510', 'SKU-00510', 'Descripción del producto 510', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 5, 24, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(903, NULL, 1, 'Producto 894', 'INT-00894', 'SKU-00894', 'Descripción del producto 894', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 3, 30, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(904, NULL, 1, 'Producto 729', 'INT-00729', 'SKU-00729', 'Descripción del producto 729', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 35, 36, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(905, NULL, 1, 'Producto 801', 'INT-00801', 'SKU-00801', 'Descripción del producto 801', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 26, 15, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(906, NULL, 1, 'Producto 717', 'INT-00717', 'SKU-00717', 'Descripción del producto 717', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 4, 10, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(907, NULL, 1, 'Producto 286', 'INT-00286', 'SKU-00286', 'Descripción del producto 286', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 18, 32, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(908, NULL, 1, 'Producto 81', 'INT-00081', 'SKU-00081', 'Descripción del producto 81', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 26, 7, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(909, NULL, 1, 'Producto 828', 'INT-00828', 'SKU-00828', 'Descripción del producto 828', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 25, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(910, NULL, 1, 'Producto 280', 'INT-00280', 'SKU-00280', 'Descripción del producto 280', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 20, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(911, NULL, 1, 'Producto 433', 'INT-00433', 'SKU-00433', 'Descripción del producto 433', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 32, 5, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(912, NULL, 1, 'Producto 227', 'INT-00227', 'SKU-00227', 'Descripción del producto 227', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 35, 6, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(913, NULL, 1, 'Producto 260', 'INT-00260', 'SKU-00260', 'Descripción del producto 260', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 17, 12, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(914, NULL, 1, 'Producto 67', 'INT-00067', 'SKU-00067', 'Descripción del producto 67', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 14, 2, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(915, NULL, 1, 'Producto 979', 'INT-00979', 'SKU-00979', 'Descripción del producto 979', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 19, 7, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(916, NULL, 1, 'Producto 490', 'INT-00490', 'SKU-00490', 'Descripción del producto 490', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 33, 3, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(917, NULL, 1, 'Producto 953', 'INT-00953', 'SKU-00953', 'Descripción del producto 953', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 28, 16, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(918, NULL, 1, 'Producto 814', 'INT-00814', 'SKU-00814', 'Descripción del producto 814', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 18, 10, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(919, NULL, 1, 'Producto 830', 'INT-00830', 'SKU-00830', 'Descripción del producto 830', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 30, 3, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(920, NULL, 1, 'Producto 731', 'INT-00731', 'SKU-00731', 'Descripción del producto 731', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 21, 22, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(921, NULL, 1, 'Producto 769', 'INT-00769', 'SKU-00769', 'Descripción del producto 769', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 25, 23, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(922, NULL, 1, 'Producto 597', 'INT-00597', 'SKU-00597', 'Descripción del producto 597', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 36, 19, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(923, NULL, 1, 'Producto 76', 'INT-00076', 'SKU-00076', 'Descripción del producto 76', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 25, 9, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(924, NULL, 1, 'Producto 753', 'INT-00753', 'SKU-00753', 'Descripción del producto 753', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 13, 34, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(925, NULL, 1, 'Producto 824', 'INT-00824', 'SKU-00824', 'Descripción del producto 824', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 24, 33, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(926, NULL, 1, 'Producto 801', 'INT-00801', 'SKU-00801', 'Descripción del producto 801', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 4, 31, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(927, NULL, 1, 'Producto 624', 'INT-00624', 'SKU-00624', 'Descripción del producto 624', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 25, 21, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(928, NULL, 1, 'Producto 670', 'INT-00670', 'SKU-00670', 'Descripción del producto 670', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 18, 1, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(929, NULL, 1, 'Producto 503', 'INT-00503', 'SKU-00503', 'Descripción del producto 503', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 24, 11, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(930, NULL, 1, 'Producto 170', 'INT-00170', 'SKU-00170', 'Descripción del producto 170', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 16, 36, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(931, NULL, 1, 'Producto 695', 'INT-00695', 'SKU-00695', 'Descripción del producto 695', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 26, 28, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(932, NULL, 1, 'Producto 677', 'INT-00677', 'SKU-00677', 'Descripción del producto 677', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 36, 23, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(933, NULL, 1, 'Producto 413', 'INT-00413', 'SKU-00413', 'Descripción del producto 413', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 18, 3, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(934, NULL, 1, 'Producto 527', 'INT-00527', 'SKU-00527', 'Descripción del producto 527', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 3, 21, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(935, NULL, 1, 'Producto 632', 'INT-00632', 'SKU-00632', 'Descripción del producto 632', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 29, 5, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(936, NULL, 1, 'Producto 351', 'INT-00351', 'SKU-00351', 'Descripción del producto 351', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 9, 26, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(937, NULL, 1, 'Producto 885', 'INT-00885', 'SKU-00885', 'Descripción del producto 885', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 34, 5, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(938, NULL, 1, 'Producto 510', 'INT-00510', 'SKU-00510', 'Descripción del producto 510', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 1, 7, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(939, NULL, 1, 'Producto 598', 'INT-00598', 'SKU-00598', 'Descripción del producto 598', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 34, 11, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(940, NULL, 1, 'Producto 106-II', 'INT-00106I', 'SKU-00106I', 'Descripción del producto 106', 'COMPRA NACIONAL', 2, NULL, 1, 3, 1, 9, 28, 0, 1, 0, '2025-11-27 14:37:09', '2026-02-16 14:43:25', NULL),
(941, NULL, 1, 'Producto 536', 'INT-00536', 'SKU-00536', 'Descripción del producto 536', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 3, 3, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(942, NULL, 1, 'Producto 487', 'INT-00487', 'SKU-00487', 'Descripción del producto 487', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 27, 11, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(943, NULL, 1, 'Producto 483', 'INT-00483', 'SKU-00483', 'Descripción del producto 483', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 3, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(944, NULL, 1, 'Producto 428', 'INT-00428', 'SKU-00428', 'Descripción del producto 428', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 9, 13, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(945, NULL, 1, 'Producto 31', 'INT-00031', 'SKU-00031', 'Descripción del producto 31', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 17, 11, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(946, NULL, 1, 'Producto 181', 'INT-00181', 'SKU-00181', 'Descripción del producto 181', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 22, 17, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(947, NULL, 1, 'Producto 805', 'INT-00805', 'SKU-00805', 'Descripción del producto 805', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 17, 17, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(948, NULL, 1, 'Producto 253', 'INT-00253', 'SKU-00253', 'Descripción del producto 253', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 16, 20, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(949, NULL, 1, 'Producto 731', 'INT-00731', 'SKU-00731', 'Descripción del producto 731', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 16, 3, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(950, NULL, 1, 'Producto 746', 'INT-00746', 'SKU-00746', 'Descripción del producto 746', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 28, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(951, NULL, 1, 'Producto 508', 'INT-00508', 'SKU-00508', 'Descripción del producto 508', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 26, 9, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(952, NULL, 1, 'Producto 53', 'INT-00053', 'SKU-00053', 'Descripción del producto 53', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 2, 32, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(953, NULL, 1, 'Producto 534', 'INT-00534', 'SKU-00534', 'Descripción del producto 534', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 26, 8, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(954, NULL, 1, 'Producto 328', 'INT-00328', 'SKU-00328', 'Descripción del producto 328', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 30, 6, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(955, NULL, 1, 'Producto 55', 'INT-00055', 'SKU-00055', 'Descripción del producto 55', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 28, 24, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(956, NULL, 1, 'Producto 407', 'INT-00407', 'SKU-00407', 'Descripción del producto 407', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 16, 29, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(957, NULL, 1, 'Producto 690', 'INT-00690', 'SKU-00690', 'Descripción del producto 690', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 27, 14, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(958, NULL, 1, 'Producto 652', 'INT-00652', 'SKU-00652', 'Descripción del producto 652', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 12, 16, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(959, NULL, 1, 'Producto 968', 'INT-00968', 'SKU-00968', 'Descripción del producto 968', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 35, 2, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(960, NULL, 1, 'Producto 330', 'INT-00330', 'SKU-00330', 'Descripción del producto 330', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 9, 4, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(961, NULL, 1, 'Producto 63', 'INT-00063', 'SKU-00063', 'Descripción del producto 63', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 14, 8, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(962, NULL, 1, 'Producto 589', 'INT-00589', 'SKU-00589', 'Descripción del producto 589', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 30, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(963, NULL, 1, 'Producto 350', 'INT-00350', 'SKU-00350', 'Descripción del producto 350', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 31, 21, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(964, NULL, 1, 'Producto 668', 'INT-00668', 'SKU-00668', 'Descripción del producto 668', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 20, 10, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(965, NULL, 1, 'Producto 926', 'INT-00926', 'SKU-00926', 'Descripción del producto 926', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 23, 2, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(966, NULL, 1, 'Producto 914', 'INT-00914', 'SKU-00914', 'Descripción del producto 914', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 33, 29, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(967, NULL, 1, 'Producto 106-I', 'INT-0106', 'SKU-0106', 'Descripción del producto 106', 'COMPRA NACIONAL', 2, NULL, 4, 5, 1, 19, 34, 0, 1, 0, '2025-11-27 14:37:09', '2026-02-16 14:42:41', NULL),
(968, NULL, 1, 'Producto 820', 'INT-00820', 'SKU-00820', 'Descripción del producto 820', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 17, 13, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(969, NULL, 1, 'Producto 870', 'INT-00870', 'SKU-00870', 'Descripción del producto 870', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 5, 28, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(970, NULL, 1, 'Producto 664', 'INT-00664', 'SKU-00664', 'Descripción del producto 664', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 20, 36, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(971, NULL, 1, 'Producto 45', 'INT-00045', 'SKU-00045', 'Descripción del producto 45', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 32, 20, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(972, NULL, 1, 'Producto 554', 'INT-00554', 'SKU-00554', 'Descripción del producto 554', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 19, 14, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(973, NULL, 1, 'Producto 285', 'INT-00285', 'SKU-00285', 'Descripción del producto 285', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 4, 18, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(974, NULL, 1, 'Producto 529', 'INT-00529', 'SKU-00529', 'Descripción del producto 529', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 35, 28, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(975, NULL, 1, 'Producto 157', 'INT-00157', 'SKU-00157', 'Descripción del producto 157', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 26, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(976, NULL, 1, 'Producto 545', 'INT-00545', 'SKU-00545', 'Descripción del producto 545', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 22, 2, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(977, NULL, 1, 'Producto 392', 'INT-00392', 'SKU-00392', 'Descripción del producto 392', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 35, 8, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(978, NULL, 1, 'Producto 188', 'INT-00188', 'SKU-00188', 'Descripción del producto 188', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 24, 4, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(979, NULL, 1, 'Producto 40', 'INT-00040', 'SKU-00040', 'Descripción del producto 40', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 36, 6, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(980, NULL, 1, 'Producto 241', 'INT-00241', 'SKU-00241', 'Descripción del producto 241', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 31, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(981, NULL, 1, 'Producto 581', 'INT-00581', 'SKU-00581', 'Descripción del producto 581', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 1, 24, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(982, NULL, 1, 'Producto 948', 'INT-00948', 'SKU-00948', 'Descripción del producto 948', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 15, 15, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(983, NULL, 1, 'Producto 403', 'INT-00403', 'SKU-00403', 'Descripción del producto 403', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 3, 29, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(984, NULL, 1, 'Producto 103', 'INT-00103', 'SKU-00103', 'Descripción del producto 103', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 16, 24, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(985, NULL, 1, 'Producto 597', 'INT-00597', 'SKU-00597', 'Descripción del producto 597', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 34, 1, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(986, NULL, 1, 'Producto 899', 'INT-00899', 'SKU-00899', 'Descripción del producto 899', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 22, 21, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(987, NULL, 1, 'Producto 368', 'INT-00368', 'SKU-00368', 'Descripción del producto 368', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 22, 10, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(988, NULL, 1, 'Producto 658', 'INT-00658', 'SKU-00658', 'Descripción del producto 658', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 2, 6, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(989, NULL, 1, 'Producto 330', 'INT-00330', 'SKU-00330', 'Descripción del producto 330', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 18, 34, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(990, NULL, 1, 'Producto 847', 'INT-00847', 'SKU-00847', 'Descripción del producto 847', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 30, 13, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(991, NULL, 1, 'Producto 887', 'INT-00887', 'SKU-00887', 'Descripción del producto 887', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 4, 23, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(992, NULL, 1, 'Producto 70', 'INT-00070', 'SKU-00070', 'Descripción del producto 70', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 4, 15, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(993, NULL, 1, 'Producto 354', 'INT-00354', 'SKU-00354', 'Descripción del producto 354', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 31, 26, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(994, NULL, 1, 'Producto 56', 'INT-00056', 'SKU-00056', 'Descripción del producto 56', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 18, 34, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(995, NULL, 1, 'Producto 994', 'INT-00994', 'SKU-00994', 'Descripción del producto 994', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 2, 16, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(996, NULL, 1, 'Producto 979', 'INT-00979', 'SKU-00979', 'Descripción del producto 979', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 1, 36, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(997, NULL, 1, 'Producto 892', 'INT-00892', 'SKU-00892', 'Descripción del producto 892', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 13, 29, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(998, NULL, 1, 'Producto 229', 'INT-00229', 'SKU-00229', 'Descripción del producto 229', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 34, 24, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(999, NULL, 1, 'Producto 736', 'INT-00736', 'SKU-00736', 'Descripción del producto 736', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 1, 15, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(1000, NULL, 1, 'Producto 760', 'INT-00760', 'SKU-00760', 'Descripción del producto 760', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 25, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(1001, NULL, 1, 'Producto 885', 'INT-00885', 'SKU-00885', 'Descripción del producto 885', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 35, 8, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(1002, NULL, 1, 'Producto 292', 'INT-00292', 'SKU-00292', 'Descripción del producto 292', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 12, 3, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(1003, NULL, 1, 'Producto 693', 'INT-00693', 'SKU-00693', 'Descripción del producto 693', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 16, 3, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(1004, NULL, 1, 'Producto 373', 'INT-00373', 'SKU-00373', 'Descripción del producto 373', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 19, 33, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(1005, NULL, 1, 'Producto 2', 'INT-00002', 'SKU-00002', 'Descripción del producto 2', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 1, 36, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(1006, NULL, 1, 'Producto 277', 'INT-00277', 'SKU-00277', 'Descripción del producto 277', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 33, 1, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(1007, NULL, 1, 'Producto 550', 'INT-00550', 'SKU-00550', 'Descripción del producto 550', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 32, 21, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(1008, NULL, 1, 'Producto 869', 'INT-00869', 'SKU-00869', 'Descripción del producto 869', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 2, 30, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(1009, NULL, 1, 'Producto 963', 'INT-00963', 'SKU-00963', 'Descripción del producto 963', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 30, 23, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(1010, NULL, 1, 'Producto 945', 'INT-00945', 'SKU-00945', 'Descripción del producto 945', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 1, 3, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(1011, NULL, 1, 'Producto 514', 'INT-00514', 'SKU-00514', 'Descripción del producto 514', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 17, 34, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(1012, NULL, 1, 'Producto 658', 'INT-00658', 'SKU-00658', 'Descripción del producto 658', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 31, 7, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(1013, NULL, 1, 'Producto 318', 'INT-00318', 'SKU-00318', 'Descripción del producto 318', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 20, 16, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(1014, NULL, 1, 'Producto 42', 'INT-00042', 'SKU-00042', 'Descripción del producto 42', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 5, 10, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(1015, NULL, 1, 'Producto 800', 'INT-00800', 'SKU-00800', 'Descripción del producto 800', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 1, 33, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(1016, NULL, 1, 'Producto 749', 'INT-00749', 'SKU-00749', 'Descripción del producto 749', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 8, 5, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(1017, NULL, 1, 'Producto 512', 'INT-00512', 'SKU-00512', 'Descripción del producto 512', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 15, 20, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(1018, NULL, 1, 'Producto 625', 'INT-00625', 'SKU-00625', 'Descripción del producto 625', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 2, 4, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(1019, NULL, 1, 'Producto 573', 'INT-00573', 'SKU-00573', 'Descripción del producto 573', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 13, 8, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(1020, NULL, 1, 'Producto 980', 'INT-00980', 'SKU-00980', 'Descripción del producto 980', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 31, 28, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(1021, NULL, 1, 'Producto 531', 'INT-00531', 'SKU-00531', 'Descripción del producto 531', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 12, 25, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(1022, NULL, 1, 'Producto 959', 'INT-00959', 'SKU-00959', 'Descripción del producto 959', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 30, 20, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(1023, NULL, 1, 'Producto 947', 'INT-00947', 'SKU-00947', 'Descripción del producto 947', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 8, 2, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(1024, NULL, 1, 'Producto 543', 'INT-00543', 'SKU-00543', 'Descripción del producto 543', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 1, 23, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(1025, NULL, 1, 'Producto 414', 'INT-00414', 'SKU-00414', 'Descripción del producto 414', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 20, 3, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(1026, NULL, 1, 'Producto 799', 'INT-00799', 'SKU-00799', 'Descripción del producto 799', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 32, 26, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(1027, NULL, 1, 'Producto 741', 'INT-00741', 'SKU-00741', 'Descripción del producto 741', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 5, 22, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(1028, NULL, 1, 'Producto 936', 'INT-00936', 'SKU-00936', 'Descripción del producto 936', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 25, 21, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(1029, NULL, 1, 'Producto 51', 'INT-00051', 'SKU-00051', 'Descripción del producto 51', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 9, 5, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(1030, NULL, 1, 'Producto 332', 'INT-00332', 'SKU-00332', 'Descripción del producto 332', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 31, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(1031, NULL, 1, 'Producto 895', 'INT-00895', 'SKU-00895', 'Descripción del producto 895', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 23, 33, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(1032, NULL, 1, 'Producto 435', 'INT-00435', 'SKU-00435', 'Descripción del producto 435', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 26, 7, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(1033, NULL, 1, 'Producto 4', 'INT-00004', 'SKU-00004', 'Descripción del producto 4', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 29, 22, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(1034, NULL, 1, 'Producto 402', 'INT-00402', 'SKU-00402', 'Descripción del producto 402', 'COMPRA NACIONAL', 2, NULL, NULL, NULL, 1, 5, 23, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(1035, NULL, 1, 'Producto 936', 'INT-00936', 'SKU-00936', 'Descripción del producto 936', 'COMPRA NACIONAL', 2, NULL, 3, NULL, 1, 5, 35, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(1036, NULL, 1, 'Producto 133', 'INT-00133', 'SKU-00133', 'Descripción del producto 133', 'COMPRA NACIONAL', 2, NULL, 3, NULL, 1, 2, 28, 0, 1, 0, '2025-11-27 14:37:09', NULL, NULL),
(1060, NULL, 6, 'CINTA TRANSPARENTE', 'CNT001', 'CNT001', 'Cinta transparente 1 metro', 'COMPRA NACIONAL', 2, 4, 1, 1, 1, 35, 35, 0, 1, 1, '2025-12-01 16:28:53', '2025-12-01 16:38:48', NULL),
(1061, NULL, 2, 'CAJA DE COLORES KORES 12 UND', 'CCK850', 'CCK850', 'Caja de colores Kores', 'PRODUCIDO', 1, 1, 2, 1, 1, 35, 35, 0, 1, 1, '2025-12-04 17:31:57', '2025-12-04 17:31:57', NULL),
(1062, NULL, 2, 'CAJA DE COLORES KORES 6 UND', 'CCK851', 'CCK851', 'Caja de colores kores 6 unidades', 'COMBO', 1, 2, 1, 1, 1, 35, 35, 0, 1, 1, '2025-12-04 17:38:50', '2025-12-04 17:38:50', NULL),
(1063, NULL, 2, 'ROMPEZABEZAS 100 PIEZAS ', 'ROP001', 'ROP001', 'ROMPECABEZAS 100 PIEZAS', 'COMBO', 1, 2, 2, 2, 1, 35, 35, 0, 1, 1, '2025-12-05 17:09:42', '2025-12-05 17:09:42', NULL),
(1064, NULL, 3, 'BOTAS SENDERISMO TALLA 39', 'BSS125', 'BSS125', '', 'PRODUCIDO', 1, NULL, 2, 3, 0, 35, 35, 0, 1, 1, '2026-01-19 19:59:37', '2026-01-19 19:59:37', NULL),
(1065, NULL, 6, 'BOLSA REGALO MEDIANA', 'BRM001', 'BRM001', '', 'PRODUCIDO', 1, NULL, 3, 1, 1, 35, 35, 0, 1, 1, '2026-01-19 20:04:24', '2026-01-19 20:04:24', NULL),
(1066, NULL, 3, 'prueba tenant', '1234', '1234', 'pryeba', 'COMBO', 1, NULL, 3, 1, 1, 35, 35, 0, 1, 1, '2026-01-21 21:22:53', '2026-01-21 21:22:53', NULL),
(1067, NULL, 36, 'MARSELLA TENANT', 'TENANT123', 'TENANT123', 'TENANT123', 'COMPRA NACIONAL', 1, NULL, 3, 3, 1, 35, 35, 0, 1, 1, '2026-01-21 21:27:20', '2026-01-21 21:27:20', NULL),
(1068, NULL, 3, 'MARSELLA', 'TEC103', 'BRU581', 'PRUEBA', 'COMBO', 1, NULL, 3, 3, 1, 35, 35, 0, 1, 1, '2026-01-21 21:46:29', '2026-01-21 21:46:29', NULL),
(1069, NULL, 36, 'pruebaTenant', '124124', '124124', 'prueba', 'COMBO', 1, NULL, 3, 3, 1, 35, 35, 0, 1, 1, '2026-01-22 13:11:58', '2026-01-22 13:11:58', NULL),
(1070, NULL, 36, 'prueba tenant 2026', 'prueba2026', 'pru2025', 'prueba', 'COMBO', 1, NULL, 3, 3, 1, 35, 35, 0, 1, 1, '2026-01-22 13:23:56', '2026-01-22 13:23:56', NULL),
(1071, NULL, 84, 'silla rimax tenant', 'sill123', 'sill123', 'prueba', 'COMBO', 1, NULL, 3, 3, 1, 35, 35, 0, 1, 1, '2026-01-22 13:47:07', '2026-01-22 13:47:07', NULL),
(1072, NULL, 84, 'sillas rimax 2', 'rim123', 'rim123', 'sillas rimax 2', 'COMBO', 1, NULL, 8, 3, 1, 35, 35, 0, 1, 1, '2026-01-22 13:55:32', '2026-01-22 13:55:32', NULL),
(1073, 347, 84, 'silla rimax tenant dos', 'rim231', 'rim231', 'silla rimax tenant dos', 'COMBO', 2, NULL, 3, 3, 1, 35, 35, 0, 1, 1, '2026-01-22 14:02:44', '2026-01-22 14:02:50', NULL),
(1074, NULL, 41, 'cojín para pies', '485QQ', '485QQ', 'prueba con estore', 'COMPRA NACIONAL', 1, NULL, 4, 3, 1, 35, 35, 0, 1, 1, '2026-01-22 14:05:54', '2026-01-22 14:12:18', NULL),
(1075, NULL, 84, 'Bateria Tenant', 'bat123', 'bat123', 'prueba', 'COMBO', 2, NULL, 3, 3, 1, 35, 35, 0, 1, 1, '2026-01-22 14:09:28', '2026-01-22 14:09:28', NULL),
(1076, NULL, 84, 'silla computador', 'com123', 'com123', 'pruaba', 'COMBO', 2, NULL, 3, 3, 1, 35, 35, 0, 1, 1, '2026-01-22 15:08:57', '2026-01-22 15:08:57', NULL),
(1077, NULL, 83, 'TECLADO 65%', 'teclado', 'TECT123', 'prueba', 'COMBO', 2, NULL, 3, 3, 1, 35, 35, 0, 1, 1, '2026-01-22 15:21:01', '2026-01-22 15:21:01', NULL),
(1078, NULL, 83, 'flores rosas', 'ros123', 'ros123', '1234 descripcion', 'COMBO', 2, NULL, 3, 3, 1, 35, 35, 0, 1, 1, '2026-01-22 15:42:04', '2026-01-22 15:42:04', NULL),
(1079, NULL, 83, 'GIRASOLES', 'GIR1', 'GIR1', '', 'COMBO', 2, NULL, 2, 3, 1, 35, 35, 0, 1, 1, '2026-01-22 15:46:59', '2026-01-22 15:46:59', NULL),
(1080, 348, 83, 'margarita', 'mar123', 'mar123', 'margarita', 'COMBO', 2, NULL, 3, 3, 1, 35, 35, 0, 1, 1, '2026-01-22 16:04:56', '2026-01-22 16:05:04', NULL),
(1081, 349, 83, 'tulipanes', 'til123', 'tul123', 'prueba', 'COMBO', 3, NULL, 3, 3, 0, 35, 35, 0, 1, 1, '2026-01-22 16:25:22', '2026-01-22 16:28:03', NULL),
(1082, NULL, 41, 'Jasper Mckay', '7777777a', '7777777a', 'Fugit commodi qui m', 'COMBO', 2, NULL, 1, 2, 1, 35, 35, 0, 1, 1, '2026-01-22 17:30:21', '2026-01-22 17:30:21', NULL),
(1083, 350, 83, 'MARGARITA', 'MAR452', 'MAR453', 'MARGARITA', 'COMBO', 3, NULL, 3, 3, 1, 35, 35, 0, 1, 1, '2026-01-22 17:34:19', '2026-01-22 17:34:25', NULL),
(1085, NULL, 94, 'Impresora EPSON 78K', 'IMP780', 'IMP780', '', 'COMPRA NACIONAL', 2, NULL, 3, 4, 1, 35, 35, 0, 1, 1, '2026-01-22 20:27:31', '2026-01-29 15:18:32', NULL),
(1086, NULL, 40, 'jjujujuju', 'tttteea', 'tttteea', 'ssss', 'COMPRA NACIONAL', 3, NULL, 3, 3, 1, 35, 35, 0, 1, 1, '2026-01-22 20:34:34', '2026-01-22 20:34:34', NULL),
(1087, NULL, 41, 'fdsfdsfds', 'logfh787', 'logfh787', '', 'COMBO', 2, NULL, 1, 2, 1, 35, 35, 0, 1, 1, '2026-01-22 20:58:26', '2026-01-22 20:58:26', NULL),
(1088, NULL, 41, 'anillo ', '5478d', '5478d', 'dddd', 'COMPRA NACIONAL', 2, NULL, 7, 2, 0, 35, 35, 0, 1, 1, '2026-01-22 21:29:29', '2026-01-22 21:29:29', NULL),
(1089, NULL, 40, 'AUDIFONOS BLUETOOTH COLOR ROJO', 'ADF201', 'ADF201', '', 'COMPRA NACIONAL', 2, NULL, 8, 3, 0, 35, 35, 0, 1, 1, '2026-01-29 20:52:12', '2026-01-29 20:52:12', NULL),
(1090, NULL, 41, 'MARSELLA', 'hht103', 'hht581', 'prueba', 'COMBO', 2, NULL, 3, 5, 1, 35, 35, 0, 1, 1, '2026-01-30 16:05:16', '2026-01-30 16:05:16', NULL),
(1091, NULL, 86, 'ARNES MASCOTA TALLA MEDIANA', 'AMT854', 'ATM854', 'ARNES MASCOTA TALLA MEDIANA', 'COMPRA NACIONAL', 3, NULL, 4, 5, 1, 35, 35, 0, 1, 1, '2026-01-30 16:16:36', '2026-01-30 16:16:36', NULL),
(1092, NULL, 41, 'ALMOHADA PLUMAS', 'ALAL', 'ALAL', 'ALMOHADA PLUMAS', 'IMPORTADO', 2, NULL, 10, 5, 0, 3, 2, 0, 1, 1, '2026-01-30 22:58:08', '2026-01-30 22:58:08', NULL),
(1093, 351, 84, 'silla rimax', 'rim541', 'rim541', 'silla rimax', 'COMBO', 2, NULL, 3, 5, 1, 35, 35, 0, 1, 1, '2026-02-01 22:20:58', '2026-02-01 22:21:09', NULL),
(1094, NULL, 94, 'CARTUCHO IMPRESORA EPSON / NEGRO', 'CRT055', 'CRT055', 'CARTUCHO IMPRESORA EPSON / NEGRO', 'IMPORTADO', 6, NULL, 8, 2, 1, 13, 35, 0, 1, 1, '2026-02-13 19:22:55', '2026-02-13 19:22:55', NULL),
(1095, 375, 94, 'CARTUCHO IMPRESORA EPSON COLOR', 'CRTC055', 'CRTC055', '', 'IMPORTADO', 3, NULL, 8, 2, 1, 6, 35, 0, 1, 0, '2026-02-13 20:34:35', '2026-03-13 19:40:34', NULL),
(1096, NULL, 93, 'FILTRO DE CAFE', 'FDC021', 'FDC021', 'FILTRO DE CAFE', 'PRODUCIDO', 2, NULL, 9, 4, 0, 35, 35, 0, 1, 1, '2026-02-16 14:32:25', '2026-02-16 14:32:25', NULL),
(1097, NULL, 93, 'CAFE 4 ONZAS', 'CNO004', 'CNO004', '', 'COMBO', 2, NULL, 9, 5, 0, 35, 35, 0, 1, 1, '2026-02-16 18:01:30', '2026-02-16 18:01:30', NULL),
(1098, NULL, 74, 'ENSALADA DE FRUTAS', 'EDF025', 'EDF025', 'ENSALADA DE FRUTAS', 'PRODUCIDO', 2, 2, 3, 2, 0, 35, 35, 0, 1, 1, '2026-02-16 19:01:51', '2026-02-16 19:01:51', NULL),
(1099, NULL, 40, 'HEADPHONES SONY', 'HDS342', 'HDS342', 'HEADPHONES SONY', 'PRODUCIDO', 3, NULL, 7, 3, 1, 4, 35, 0, 1, 1, '2026-02-16 19:04:23', '2026-02-16 19:04:23', NULL),
(1100, NULL, 86, 'Pruebas', 'ppp', 'ppp', '', 'COMBO', 2, NULL, 3, 3, 1, 1, 35, 0, 1, 1, '2026-02-16 19:38:41', '2026-02-16 19:38:41', NULL),
(1101, 361, 86, 'PAÑOLETA TALLA MEDIANA', 'PED444', 'PED44', 'PAÑOLETA TALLA MEDIANA', 'IMPORTADO', 2, NULL, 3, 3, 0, 35, 35, 0, 1, 1, '2026-02-17 17:42:46', '2026-02-17 17:42:54', NULL),
(1102, 362, 93, 'CAPSULA DE CAFE', 'CDC458', 'CDC458', 'CAPSULA DE CAFE', 'PRODUCIDO', 3, NULL, 3, 3, 0, 1, 1, 0, 1, 1, '2026-02-17 17:47:54', '2026-02-17 17:47:58', NULL),
(1103, 363, 86, 'AUDIFONOS TWS ', 'HDP269', 'HDP269', '', 'PRODUCIDO', 2, NULL, 8, 5, 0, 35, 35, 0, 1, 1, '2026-02-17 19:20:09', '2026-02-17 19:20:16', NULL),
(1104, NULL, 5, 'REPISA FLOTANTE', 'RFT201', 'RFT201', '', 'IMPORTADO', 3, NULL, 9, 3, 0, 4, 1, 0, 1, 1, '2026-02-17 19:23:38', '2026-02-17 19:23:38', NULL),
(1105, 364, 82, 'DISCO DURU 1T', 'DDT458', 'DDT458', '', 'IMPORTADO', 3, NULL, 1, 3, 0, 13, 35, 0, 1, 1, '2026-02-17 19:56:07', '2026-02-17 19:56:13', NULL),
(1106, 365, 86, 'PAÑOLETA TALLA PEQUEÑA', 'PTP154', 'PTP154', '', 'IMPORTADO', 3, NULL, 7, 5, 1, 24, 35, 0, 1, 0, '2026-02-18 21:01:58', '2026-02-18 21:02:05', NULL),
(1107, NULL, 86, 'PAÑOLETA ROJA TALLA PEQUEÑA', 'PRT985', 'PRT985', '', 'IMPORTADO', 2, NULL, 7, 3, 1, 21, 35, 0, 1, 0, '2026-02-18 21:07:04', '2026-02-18 21:07:04', NULL),
(1108, NULL, 86, 'CAMISETA PARA MASCOTA C GRIS', 'CPM785', 'CPM785', 'CAMISETA PARA MASCOTA COLOR GRIS', 'IMPORTADO', 6, NULL, 7, 3, 1, 20, 35, 0, 1, 1, '2026-02-18 21:24:45', '2026-02-18 21:24:45', NULL),
(1109, 373, 93, 'MOLEDOR DE CAFE PORTATIL', 'FGV343', 'FGV343', 'MOLEDOR DE CAFE PORTATIL', 'IMPORTADO', 2, NULL, 9, 3, 1, 1, 35, 0, 1, 0, '2026-02-19 19:22:37', '2026-03-03 19:53:16', NULL),
(1110, 372, 82, 'MOUSE PAD COLOR AZUL', 'KFG452', 'KFG452', 'MOUSE PAD COLOR AZUL', 'IMPORTADO', 2, NULL, 10, 6, 1, 22, 35, 0, 1, 0, '2026-02-19 19:28:45', '2026-03-03 19:49:21', NULL),
(1111, NULL, 6, 'TIJERAS DE PLASTICO', 'JVJ568', 'JVJ568', 'NEW_PRODUCT', 'IMPORTADO', 2, NULL, 4, 3, 1, 4, 35, 0, 1, 0, '2026-02-19 19:50:25', '2026-03-03 19:46:07', NULL),
(1112, NULL, NULL, 'NEW_PRODUCT', 'JVJ567', 'JVJ567', 'NEW_PRODUCT', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 0, 0, 0, 1, 1, '2026-02-19 19:50:31', '2026-02-19 19:50:31', NULL),
(1113, NULL, 41, 'ALMOHADA III', 'AMD0258', 'AMD0258', 'ALMOHADA III', 'IMPORTADO', 2, NULL, 7, 1, 1, 35, 35, 0, 1, 0, '2026-02-20 13:09:04', '2026-02-20 13:09:04', NULL),
(1114, NULL, 35, 'CHAZOS PLASTICO', 'DFGW34', 'DFGW34', 'CHAZOS PLASTICO', 'IMPORTADO', 2, NULL, 10, 2, 1, 35, 35, 0, 1, 0, '2026-02-20 13:10:37', '2026-02-20 20:43:25', NULL),
(1115, 371, 82, 'TECLADO ALAMBRICO COMPUTADOR', 'CGH109', '7800651', 'TECLADO ALAMBRICO COMPUTADOR', 'IMPORTADO', 2, NULL, 8, 2, 1, 2, 35, 0, 1, 0, '2026-02-20 13:37:10', '2026-03-03 19:39:29', NULL),
(1116, NULL, NULL, 'NEW_PRODUCT', '2400053', '2400053', 'NEW_PRODUCT', 'IMPORTADO', 2, NULL, NULL, NULL, 1, 0, 0, 0, 1, 1, '2026-02-20 14:02:04', '2026-02-20 14:02:04', NULL),
(1117, NULL, NULL, 'GENERICO', 'GEN-B04840', 'GEN-B04840', NULL, 'COMPRA NACIONAL', 3, NULL, NULL, NULL, 0, 0, 0, 0, 1, 1, '2026-02-25 14:27:39', '2026-02-25 14:27:39', NULL),
(1118, NULL, 86, 'TALONARIO', '99812', 'TAL123', 'TALONARIO', 'PRODUCIDO', 3, NULL, 8, 5, 0, 35, 35, 0, 1, 0, '2026-02-27 16:19:53', '2026-02-27 16:19:53', NULL),
(1119, NULL, 41, 'PAPEL', 'PAP213', '7800383', 'INSUMO', 'INSUMO', 3, NULL, 3, 5, 1, 35, 35, 0, 1, 0, '2026-02-27 16:45:52', '2026-02-27 16:45:52', NULL),
(1120, NULL, 86, 'INSUMO', 'INSUMO', 'INS123', 'PRUEBA', 'INSUMO', 6, NULL, 8, 5, 1, 35, 35, 0, 1, 0, '2026-03-09 15:47:47', '2026-03-09 15:47:47', NULL),
(1121, NULL, 41, 'insumo 2', 'ins222', 'ins222', 'maneja suministro 2', 'COMBO', 3, NULL, 3, 3, 1, 35, 35, 0, 1, 0, '2026-03-09 17:28:58', '2026-03-09 17:28:58', NULL),
(1122, NULL, 41, 'insumo 3', 'insu333', 'insu333', 'insumo', 'INSUMO', 6, NULL, 3, 2, 1, 35, 35, 0, 1, 0, '2026-03-09 17:37:02', '2026-03-09 17:37:02', NULL),
(1123, 378, 86, 'cargador', 'cargador', 'car1234', 'prueba', 'COMPRA NACIONAL', 3, NULL, 8, 5, 1, 35, 35, 0, 1, 0, '2026-04-09 20:50:49', '2026-04-09 20:50:58', NULL),
(1124, 382, 86, 'celular oppo x7 pro', 'OPO123', 'OPO123', 'PRUEB', 'COMBO', 3, NULL, 8, 5, 1, 35, 35, 0, 1, 0, '2026-04-10 20:44:13', '2026-04-10 20:44:25', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_items_accessories`
--

CREATE TABLE `inv_items_accessories` (
  `id` int NOT NULL,
  `item` int NOT NULL,
  `insumo` int NOT NULL,
  `observacion` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `inv_items_accessories`
--

INSERT INTO `inv_items_accessories` (`id`, `item`, `insumo`, `observacion`) VALUES
(1, 11, 575, ''),
(2, 11, 596, ''),
(3, 1113, 1119, 'prueba2004'),
(4, 1113, 538, 'prueba');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_items_cut_details`
--

CREATE TABLE `inv_items_cut_details` (
  `id` bigint UNSIGNED NOT NULL,
  `item_id` bigint UNSIGNED NOT NULL,
  `repeat_in` int NOT NULL,
  `plan_centimeters` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `plan_millimeters` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `production_order_id` bigint UNSIGNED NOT NULL,
  `accumulated` decimal(10,2) DEFAULT '0.00',
  `remaining` decimal(10,2) DEFAULT '0.00',
  `status` tinyint DEFAULT '1',
  `cut_id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED DEFAULT '0',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `justification` text COLLATE utf8mb4_unicode_ci,
  `accumulated_cm` decimal(10,2) DEFAULT '0.00',
  `remaining_cm` decimal(10,2) DEFAULT '0.00',
  `length_cm` decimal(10,2) DEFAULT '0.00',
  `length_mm` decimal(10,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `inv_items_cut_details`
--

INSERT INTO `inv_items_cut_details` (`id`, `item_id`, `repeat_in`, `plan_centimeters`, `plan_millimeters`, `production_order_id`, `accumulated`, `remaining`, `status`, `cut_id`, `customer_id`, `notes`, `created_by`, `justification`, `accumulated_cm`, `remaining_cm`, `length_cm`, `length_mm`, `created_at`, `updated_at`) VALUES
(1, 30, 1, '1.2, 1.2', '12, 12', 0, 34.00, 2966.00, 1, 1, 27, '', 'pruebas7 pruebas', 'por prueba no mas', 3.40, 296.60, 299.90, 2999.00, '2026-03-13 21:23:44', '2026-03-13 21:23:44'),
(2, 30, 1, '23.2, 23.2', '232, 232', 0, 474.00, 2526.00, 1, 2, 19, 'sdfsdfsdfsdfsf', 'pruebas7 pruebas', 'sdfsfsfsdf', 47.40, 252.60, 300.00, 3000.00, '2026-03-13 21:28:50', '2026-03-13 21:28:50'),
(3, 30, 1, '1.2, 1.2', '12, 12', 0, 34.00, 2966.00, 1, 3, 19, '', 'pruebas7 pruebas', 'dsdfsdfsf', 3.40, 296.60, 300.00, 3000.00, '2026-03-13 21:37:31', '2026-03-13 21:37:31'),
(4, 30, 1, '1.2', '12', 0, 17.00, 2983.00, 1, 4, 21, '', 'pruebas7 pruebas', '12wdqwdadad', 1.70, 298.30, 300.00, 3000.00, '2026-03-13 21:39:12', '2026-03-13 21:39:12'),
(5, 30, 1, '1.2, 2.2, 1.2', '12, 22, 12', 0, 61.00, 2939.00, 1, 5, 21, 'dasdasd', 'pruebas7 pruebas', 'dasdasd', 6.10, 293.90, 300.00, 3000.00, '2026-03-13 21:48:26', '2026-03-13 21:48:26'),
(6, 28, 1, '1.2, 1.2', '12, 12', 0, 34.00, 2966.00, 1, 5, 21, NULL, 'pruebas7 pruebas', 'dasdada', 3.40, 296.60, 300.00, 3000.00, '2026-03-13 21:48:49', '2026-03-13 21:48:49'),
(7, 1113, 1, '1.2', '12', 0, 17.00, 103.00, 1, 5, 21, NULL, 'pruebas7 pruebas', 'dqwdqdq', 1.70, 10.30, 12.00, 120.00, '2026-03-13 21:49:15', '2026-03-13 21:49:15'),
(8, 28, 1, '1.2', '12', 0, 17.00, 2983.00, 1, 5, 21, NULL, 'pruebas7 pruebas', 'wqqwdqwd', 1.70, 298.30, 300.00, 3000.00, '2026-03-13 21:49:33', '2026-03-13 21:49:33'),
(9, 30, 1, '2.3, 2.3, 2.3, 2.3, 2.3, 2.3, 23.2, 2.3, 23.3, 23.4', '23, 23, 23, 23, 23, 23, 232, 23, 233, 234', 0, 910.00, 3090.00, 1, 6, 21, 'prueba  opcionales', 'pruebas7 pruebas', 'pruebas de corte grandes ', 91.00, 309.00, 400.00, 4000.00, '2026-03-16 13:34:37', '2026-03-16 13:34:37'),
(10, 30, 1, '1.2', '12', 0, 17.00, 2983.00, 1, 7, 21, '', 'pruebas7 pruebas', 'sfsfsdfvsdfsd', 1.70, 298.30, 300.00, 3000.00, '2026-03-16 20:11:54', '2026-03-16 20:11:54'),
(11, 30, 1, '', '', 0, 0.00, 3000.00, 1, 8, 21, '', 'pruebas7 pruebas', 'sdfsf', 0.00, 300.00, 300.00, 3000.00, '2026-03-16 20:15:26', '2026-03-16 20:15:26'),
(12, 30, 1, '1.2', '12', 0, 17.00, 2983.00, 1, 8, 21, NULL, 'pruebas7 pruebas', 'sdfsdfsf', 1.70, 298.30, 300.00, 3000.00, '2026-03-16 20:16:16', '2026-03-16 20:16:16'),
(13, 30, 1, '12.3', '123', 0, 128.00, 2872.00, 1, 9, 19, '', 'pruebas7 pruebas', 'pruebas', 12.80, 287.20, 300.00, 3000.00, '2026-03-16 20:21:51', '2026-03-16 20:21:51');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_items_dimensions`
--

CREATE TABLE `inv_items_dimensions` (
  `id` int NOT NULL,
  `item_id` int DEFAULT NULL,
  `high` decimal(10,2) DEFAULT '0.00',
  `long` decimal(10,2) DEFAULT '0.00',
  `width` decimal(10,2) DEFAULT '0.00',
  `voltage` decimal(10,2) DEFAULT '0.00',
  `power` decimal(10,2) DEFAULT '0.00',
  `weight` decimal(10,2) DEFAULT '0.00',
  `quntityxbox` int NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `inv_items_dimensions`
--

INSERT INTO `inv_items_dimensions` (`id`, `item_id`, `high`, `long`, `width`, `voltage`, `power`, `weight`, `quntityxbox`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 21, 90.00, 52.00, 4.00, 4.00, 5.00, 5.00, 0, '2026-03-11 19:55:08', '2026-03-11 19:55:08', NULL),
(2, 1061, 7.80, 4.00, 0.00, 0.00, 0.00, 0.00, 0, '2026-03-11 19:59:26', '2026-03-11 19:59:26', NULL),
(4, 1091, 25.00, 45.20, 0.00, 0.00, 0.00, 0.00, 0, '2026-03-12 14:00:54', '2026-03-12 14:00:54', NULL),
(5, 1119, 12.00, 2.00, 2.00, 12.00, 12.00, 12.00, 0, '2026-03-13 16:01:19', '2026-03-13 16:01:19', NULL),
(6, 1113, 12.00, 12.00, 12.00, 12.00, 12.00, 12.00, 0, '2026-03-13 16:24:34', '2026-03-13 16:24:34', NULL),
(7, 1124, 14.00, 16.00, 12.00, 14.00, 15.00, 43.00, 14, '2026-04-10 21:08:17', '2026-04-10 21:08:17', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_items_import_price_calculations`
--

CREATE TABLE `inv_items_import_price_calculations` (
  `id` int UNSIGNED NOT NULL,
  `trm` decimal(10,2) NOT NULL,
  `price_per_kilo` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `inv_items_import_price_calculations`
--

INSERT INTO `inv_items_import_price_calculations` (`id`, `trm`, `price_per_kilo`, `created_at`, `updated_at`) VALUES
(1, 4100.00, 12.00, '2026-03-13 16:04:22', '2026-03-13 16:04:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_items_locations`
--

CREATE TABLE `inv_items_locations` (
  `id` int NOT NULL,
  `itemId` int DEFAULT NULL,
  `storeId` int DEFAULT NULL,
  `locationId` varchar(100) DEFAULT NULL,
  `stock_item_location` decimal(12,2) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `inv_items_locations`
--

INSERT INTO `inv_items_locations` (`id`, `itemId`, `storeId`, `locationId`, `stock_item_location`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1082, 1, NULL, 0.00, '2026-01-22 17:30:22', '2026-01-22 17:30:22', NULL),
(2, 30, 1, 'Estante 1, Piso 4', 0.00, '2026-01-22 18:21:19', '2026-01-22 18:21:19', NULL),
(3, 3, 3, 'Estante 2, segundo piso', 0.00, '2026-01-26 18:54:11', '2026-01-26 18:54:11', NULL),
(4, 1082, 4, 'Vitrina', 0.00, '2026-01-29 15:20:52', '2026-01-29 15:21:07', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_items_store`
--

CREATE TABLE `inv_items_store` (
  `id` int NOT NULL,
  `itemId` int DEFAULT NULL,
  `storeId` int DEFAULT NULL,
  `initial_stock` decimal(12,2) DEFAULT '0.00',
  `stock_items_store` decimal(12,2) DEFAULT '0.00',
  `stock_min` decimal(12,2) NOT NULL DEFAULT '0.00',
  `stock_max` decimal(12,2) NOT NULL DEFAULT '0.00',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `inv_items_store`
--

INSERT INTO `inv_items_store` (`id`, `itemId`, `storeId`, `initial_stock`, `stock_items_store`, `stock_min`, `stock_max`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 16, 6, 0.00, 4.00, 0.00, 0.00, '2025-12-01 21:44:47', '2025-12-01 21:44:47', NULL),
(2, 11, 7, 0.00, 11.00, 0.00, 0.00, '2025-12-03 13:56:39', '2026-02-12 22:01:45', NULL),
(7, 3, 1, 50.00, 16.00, 50.00, 0.00, '2024-01-01 00:00:00', '2026-01-20 21:49:06', NULL),
(8, 4, 1, 50.00, 6.00, 50.00, 0.00, '2024-01-02 00:00:00', NULL, NULL),
(9, 5, 1, 50.00, 8.00, 50.00, 0.00, '2024-01-03 00:00:00', '2026-01-26 13:21:27', NULL),
(13, 1083, 1, 0.00, 80.00, 0.00, 0.00, '2026-01-22 17:34:20', '2026-04-10 13:55:50', NULL),
(14, 1086, 1, 0.00, 0.00, 0.00, 0.00, '2026-01-22 20:34:35', '2026-01-22 20:34:35', NULL),
(15, 1087, 1, 0.00, 0.00, 0.00, 0.00, '2026-01-22 20:58:28', '2026-01-22 20:58:28', NULL),
(16, 28, 1, 0.00, 0.00, 0.00, 0.00, '2026-01-22 21:10:14', '2026-01-22 21:10:14', NULL),
(18, 1030, 4, 5.00, 3.00, 1.00, 0.00, NULL, '2026-02-02 19:45:16', NULL),
(20, 30, 4, 0.00, 4.00, 0.00, 0.00, '2026-01-29 14:45:29', '2026-02-02 19:45:16', NULL),
(21, 1085, 1, 0.00, 0.00, 0.00, 0.00, '2026-01-29 15:18:33', '2026-01-29 15:18:33', NULL),
(23, 1090, 1, 0.00, 0.00, 0.00, 0.00, '2026-01-30 16:05:18', '2026-01-30 16:05:18', NULL),
(24, 1091, 1, 0.00, 0.00, 0.00, 0.00, '2026-01-30 16:16:37', '2026-01-30 16:16:37', NULL),
(25, 1093, 1, 0.00, 41.00, 0.00, 0.00, '2026-02-01 22:21:00', '2026-04-12 18:04:05', NULL),
(26, 1083, 2, 0.00, 0.00, 0.00, 0.00, '2026-01-22 17:34:20', '2026-02-04 13:41:25', NULL),
(27, 1083, 3, 0.00, 10.00, 0.00, 0.00, '2026-01-22 17:34:20', '2026-02-04 13:41:25', NULL),
(28, 11, 6, 0.00, 0.00, 0.00, 0.00, '2026-02-10 21:09:32', '2026-02-13 13:58:37', NULL),
(29, 25, 7, 0.00, 6.00, 0.00, 0.00, '2026-02-11 13:55:07', '2026-02-12 21:56:28', NULL),
(30, 17, 7, 0.00, 15.00, 0.00, 0.00, '2026-02-11 13:55:07', '2026-02-12 21:56:58', NULL),
(31, 43, 7, 0.00, 6.00, 0.00, 0.00, '2026-02-11 13:55:07', '2026-02-11 13:55:07', NULL),
(32, 17, 6, 0.00, 0.00, 0.00, 0.00, '2026-02-11 17:47:41', '2026-02-13 14:10:52', NULL),
(33, 25, 6, 0.00, 0.00, 0.00, 0.00, '2026-02-11 17:47:41', '2026-02-13 14:12:25', NULL),
(34, 1094, 1, 0.00, 0.00, 0.00, 0.00, '2026-02-13 19:22:56', '2026-02-13 19:22:56', NULL),
(35, 1095, 1, 0.00, 0.00, 0.00, 0.00, '2026-02-13 20:34:37', '2026-02-13 20:34:37', NULL),
(36, 761, 1, 0.00, 0.00, 0.00, 0.00, '2026-02-13 20:46:46', '2026-02-13 20:46:46', NULL),
(37, 181, 1, 0.00, 0.00, 0.00, 0.00, '2026-02-16 14:39:34', '2026-02-16 14:39:34', NULL),
(38, 967, 1, 0.00, 0.00, 0.00, 0.00, '2026-02-16 14:42:42', '2026-02-16 14:42:42', NULL),
(39, 940, 1, 0.00, 0.00, 0.00, 0.00, '2026-02-16 14:43:25', '2026-02-16 14:43:25', NULL),
(40, 1099, 1, 0.00, 0.00, 0.00, 0.00, '2026-02-16 19:04:24', '2026-02-16 19:04:24', NULL),
(41, 1100, 1, 0.00, 0.00, 0.00, 0.00, '2026-02-16 19:38:43', '2026-02-16 19:38:43', NULL),
(42, 18, 1, 0.00, 8.00, 0.00, 0.00, '2026-02-18 19:02:08', '2026-02-24 20:31:01', NULL),
(43, 21, 1, 0.00, 2.00, 0.00, 0.00, '2026-02-18 19:02:08', '2026-02-24 20:31:01', NULL),
(44, 29, 1, 0.00, 20.00, 0.00, 0.00, '2026-02-18 19:02:08', '2026-02-24 20:31:01', NULL),
(45, 35, 1, 0.00, 11.00, 0.00, 0.00, '2026-02-18 19:02:08', '2026-02-18 19:23:57', NULL),
(46, 36, 1, 0.00, 7.00, 0.00, 0.00, '2026-02-18 19:02:08', '2026-02-24 20:31:01', NULL),
(47, 49, 1, 0.00, 0.00, 0.00, 0.00, '2026-02-18 20:59:37', '2026-02-18 20:59:37', NULL),
(48, 1106, 1, 0.00, 0.00, 0.00, 0.00, '2026-02-18 21:01:59', '2026-02-18 21:01:59', NULL),
(49, 1107, 1, 0.00, 0.00, 0.00, 0.00, '2026-02-18 21:07:06', '2026-02-18 21:07:06', NULL),
(50, 1108, 1, 0.00, 0.00, 0.00, 0.00, '2026-02-18 21:24:46', '2026-02-18 21:24:46', NULL),
(51, 1113, 1, 0.00, 3.00, 0.00, 0.00, '2026-02-20 13:09:06', '2026-04-12 18:04:05', NULL),
(52, 1114, 1, 0.00, 13.00, 0.00, 0.00, '2026-02-20 13:30:32', '2026-02-24 20:31:01', NULL),
(53, 1115, 1, 0.00, 5.00, 0.00, 0.00, '2026-02-20 13:37:10', '2026-04-12 18:04:05', NULL),
(54, 1116, 1, 0.00, 0.00, 0.00, 0.00, '2026-02-20 14:02:05', '2026-02-20 14:02:05', NULL),
(55, 291, 1, 5.00, 3.00, 1.00, 1000.00, '2026-02-24 11:34:31', '2026-02-24 20:31:01', NULL),
(56, 1119, 1, 0.00, 0.00, 0.00, 0.00, '2026-02-27 16:45:54', '2026-02-27 16:45:54', NULL),
(57, 1111, 1, 0.00, 0.00, 0.00, 0.00, '2026-03-03 19:46:08', '2026-03-03 19:46:08', NULL),
(58, 1110, 1, 0.00, 2.00, 0.00, 0.00, '2026-03-03 19:49:16', '2026-04-12 18:04:05', NULL),
(59, 1109, 1, 0.00, 0.00, 0.00, 0.00, '2026-03-03 19:53:13', '2026-03-03 19:53:13', NULL),
(60, 133, 1, 0.00, 0.00, 0.00, 0.00, '2026-03-04 13:51:40', '2026-03-04 13:51:40', NULL),
(61, 284, 1, 0.00, 0.00, 0.00, 0.00, '2026-03-04 13:55:45', '2026-03-04 13:55:45', NULL),
(62, 333, 1, 0.00, 0.00, 0.00, 0.00, '2026-03-04 14:01:28', '2026-03-04 14:01:28', NULL),
(63, 1120, 1, 0.00, 18.00, 0.00, 0.00, '2026-03-09 15:47:55', '2026-03-09 19:52:10', NULL),
(64, 1121, 1, 0.00, 4.00, 0.00, 0.00, '2026-03-09 17:29:07', '2026-04-12 18:04:05', NULL),
(65, 1122, 1, 0.00, 18.00, 0.00, 0.00, '2026-03-09 17:37:11', '2026-03-09 19:52:10', NULL),
(66, 1123, 1, 0.00, 0.00, 0.00, 0.00, '2026-04-09 20:50:51', '2026-04-09 20:50:51', NULL),
(67, 1124, 1, 0.00, 0.00, 0.00, 0.00, '2026-04-10 20:44:15', '2026-04-10 20:44:15', NULL),
(68, 1105, 1, 0.00, 3.00, 0.00, 0.00, '2026-04-12 17:05:17', '2026-04-12 18:04:05', NULL),
(69, 1107, 1, 0.00, 5.00, 0.00, 0.00, '2026-04-12 17:07:14', NULL, NULL),
(70, 1123, 1, 0.00, 5.00, 0.00, 0.00, '2026-04-12 17:09:57', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_item_applications`
--

CREATE TABLE `inv_item_applications` (
  `id` int NOT NULL,
  `itemId` int NOT NULL,
  `applicationsId` int NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_item_brand`
--

CREATE TABLE `inv_item_brand` (
  `id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '1',
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `inv_item_brand`
--

INSERT INTO `inv_item_brand` (`id`, `name`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'LEGO', 1, '2025-11-12 19:28:59', '2025-11-12 19:28:59', NULL),
(2, 'TIMBERLAND', 1, '2025-11-12 21:08:03', '2025-11-12 21:08:03', NULL),
(3, 'HASBRO', 1, '2025-11-18 15:15:48', '2025-11-26 14:24:58', NULL),
(4, 'NIKE', 1, '2025-11-20 15:09:44', '2025-11-20 15:09:44', NULL),
(6, 'RIMAX', 0, '2025-12-03 13:29:20', '2025-12-03 13:29:25', NULL),
(7, 'HyM', 1, '2026-01-20 13:42:38', '2026-01-20 13:42:38', NULL),
(8, 'EPSON', 1, '2026-01-29 15:18:15', '2026-01-29 15:18:15', NULL),
(9, 'OSTER', 1, '2026-01-29 20:31:06', '2026-01-29 20:31:22', NULL),
(10, 'PRIMAVERA', 1, '2026-01-29 20:31:56', '2026-01-29 20:31:56', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_item_house`
--

CREATE TABLE `inv_item_house` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '100',
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `inv_item_house`
--

INSERT INTO `inv_item_house` (`id`, `name`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'CASA NACIONAL', 1, '2025-11-12 19:29:10', '2025-11-18 15:27:19', NULL),
(2, 'CASA IMPORTADORA ', 1, '2025-11-12 21:08:16', '2025-11-12 21:08:16', NULL),
(3, 'CASA EDITORIAL ', 1, '2025-12-03 13:29:47', '2025-12-03 13:29:47', NULL),
(4, 'CENTRO', 1, '2026-01-20 15:25:32', '2026-01-20 15:25:32', NULL),
(5, 'CANDELARIA', 1, '2026-01-29 20:42:53', '2026-01-29 20:42:53', NULL),
(6, 'CASA TEXTIL', 1, '2026-01-29 20:43:29', '2026-01-29 20:43:29', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_item_observations`
--

CREATE TABLE `inv_item_observations` (
  `id` bigint UNSIGNED NOT NULL,
  `item_id` bigint UNSIGNED NOT NULL,
  `observations` text COLLATE utf8mb4_unicode_ci,
  `technical_specifications` text COLLATE utf8mb4_unicode_ci,
  `commercial_observations` text COLLATE utf8mb4_unicode_ci,
  `status` int NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `inv_item_observations`
--

INSERT INTO `inv_item_observations` (`id`, `item_id`, `observations`, `technical_specifications`, `commercial_observations`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1122, 'Garantía en la iluminación: 2 años\nEl chasis y frente vidrio no tiene garantía por uso inapropiado o golpes\n\nLa caja de Luz se vende sin adaptador para que el cliente tenga la opción de usar cualquiera de las fuentes que tenemos en stock. Desde adaptadores, fuentes genericas, CL o Mean Well.\nSe puede usar una sola fuente para varias cajas si el proyecto lo permite', 'VIDEO DE YOUTUBE: https://youtube.com/shorts/NmLN-gOKNNc?si=-X5EMo6BtFCBtsCt\n\nDimensiones:  A2: 43x60 cm  7,7 mm de espesor\nConsumo: 1,2 Amperios\nAdaptador sugerido: Adaptador de 2 Amperios 12VDC\n\nPuede usar también cualquier fuente MeanWell, CL , Genérica o Slim que tenga mas de 15 Watts o mas de 1,2 Amperios\nTambién puede conectar varias cajas de luz a una sola fuente haciendo el respectivo calculo de potencia según la cantidad de cajas de Luz a conectar', 'pruebas', 1, '2026-03-11 16:28:26', '2026-03-11 16:29:23', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_locations`
--

CREATE TABLE `inv_locations` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL DEFAULT '0',
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_purchase_orders`
--

CREATE TABLE `inv_purchase_orders` (
  `id` int NOT NULL,
  `consecutive` varchar(50) NOT NULL,
  `providerId` int DEFAULT NULL,
  `user` int DEFAULT NULL,
  `status` enum('PENDIENTE','EN_PROCESO','RECIBIDO','CANCELADO') NOT NULL,
  `total` decimal(11,2) NOT NULL,
  `observations` text NOT NULL,
  `expected_date` datetime NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_purchase_order_details`
--

CREATE TABLE `inv_purchase_order_details` (
  `id` int NOT NULL,
  `purchase_ordersId` int NOT NULL,
  `itemId` int DEFAULT NULL,
  `quantity_ordered` int DEFAULT NULL,
  `tax` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_purchase_requests`
--

CREATE TABLE `inv_purchase_requests` (
  `id` int NOT NULL,
  `consecutive` int NOT NULL,
  `userRealize` int NOT NULL,
  `userApprove` int DEFAULT NULL,
  `dateApprove` datetime DEFAULT NULL,
  `status` enum('PENDIENTE','APROBADA','RECHAZADA') NOT NULL,
  `observations` text NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_purchase_request_details`
--

CREATE TABLE `inv_purchase_request_details` (
  `id` int NOT NULL,
  `purchase_requestsId` int NOT NULL,
  `itemId` int DEFAULT NULL,
  `quantity_requested` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_reasons`
--

CREATE TABLE `inv_reasons` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `inv_reasons`
--

INSERT INTO `inv_reasons` (`id`, `name`, `type`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Compras', 'e', 1, NULL, NULL, NULL),
(2, 'Ajuste', 'e', 1, NULL, NULL, NULL),
(3, 'Ajuste', 's', 1, NULL, NULL, NULL),
(4, 'Deterioro', 's', 1, NULL, NULL, NULL),
(5, 'Consumo Interno', 'e', 1, NULL, NULL, NULL),
(6, 'Consumo Interno', 's', 1, NULL, NULL, NULL),
(7, 'Devolución nota crédito', 'e', 1, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_remissions`
--

CREATE TABLE `inv_remissions` (
  `id` int NOT NULL,
  `consecutive` int NOT NULL,
  `status` enum('REGISTRADO','ALISTAMIENTO','EMPACADO','EN RECORRIDO','ENTREGADO','DEVUELTO','ANULADO','VENCIDO') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'REGISTRADO',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `quoteId` int DEFAULT NULL,
  `warehouseId` int DEFAULT NULL COMMENT 'store sucursal',
  `deliveryTypeId` int DEFAULT NULL,
  `methodPaymentId` int DEFAULT NULL,
  `userId` int NOT NULL,
  `deliveryDate` datetime DEFAULT NULL,
  `delivery_id` int DEFAULT NULL COMMENT 'id del cargue',
  `expiration` int DEFAULT NULL,
  `modify` int DEFAULT NULL,
  `observations_return` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `inv_remissions`
--

INSERT INTO `inv_remissions` (`id`, `consecutive`, `status`, `created_at`, `updated_at`, `deleted_at`, `quoteId`, `warehouseId`, `deliveryTypeId`, `methodPaymentId`, `userId`, `deliveryDate`, `delivery_id`, `expiration`, `modify`, `observations_return`) VALUES
(1, 1, 'REGISTRADO', '2026-01-20 20:01:14', '2026-01-20 20:01:14', NULL, 41, 1, NULL, 1, 8, '2026-01-20 00:00:00', NULL, 0, 0, NULL),
(2, 2, 'REGISTRADO', '2026-01-29 20:20:27', '2026-01-29 20:20:27', NULL, 82, 4, NULL, 1, 125, '2026-01-29 00:00:00', NULL, 0, 0, NULL),
(3, 3, 'REGISTRADO', '2026-01-30 13:48:22', '2026-01-30 13:48:22', NULL, 85, 3, NULL, 1, 8, '2026-01-30 00:00:00', NULL, 0, 0, NULL),
(4, 4, 'REGISTRADO', '2026-01-30 14:23:57', '2026-01-30 14:23:57', NULL, 77, 3, NULL, 1, 8, '2026-01-30 00:00:00', NULL, 0, 0, NULL),
(5, 5, 'REGISTRADO', '2026-01-30 14:33:12', '2026-01-30 14:33:12', NULL, 86, 3, NULL, 1, 8, '2026-01-30 00:00:00', NULL, 0, 0, NULL),
(6, 6, 'REGISTRADO', '2026-01-30 14:35:48', '2026-01-30 14:35:48', NULL, 87, 3, NULL, 1, 8, '2026-01-30 00:00:00', NULL, 0, 0, NULL),
(7, 7, 'REGISTRADO', '2026-02-01 17:06:59', '2026-02-01 17:06:59', NULL, 93, 1, NULL, 1, 153, '2026-02-01 00:00:00', NULL, 0, 0, NULL),
(8, 8, 'REGISTRADO', '2026-02-01 17:07:48', '2026-02-01 17:07:48', NULL, 92, 1, NULL, 1, 153, '2026-02-01 00:00:00', NULL, 0, 0, NULL),
(9, 9, 'REGISTRADO', '2026-02-01 22:32:53', '2026-02-01 22:32:53', NULL, 97, 1, NULL, 1, 153, '2026-02-01 00:00:00', NULL, 0, 0, NULL),
(10, 10, 'REGISTRADO', '2026-02-01 22:37:21', '2026-02-01 22:37:21', NULL, 96, 1, NULL, 1, 153, '2026-02-01 00:00:00', NULL, 0, 0, NULL),
(13, 11, 'REGISTRADO', '2026-02-02 16:00:34', '2026-02-02 16:00:34', NULL, 79, 4, NULL, 1, 125, '2026-02-02 00:00:00', NULL, 0, 0, NULL),
(14, 12, 'REGISTRADO', '2026-02-02 19:45:16', '2026-02-02 19:45:16', NULL, 103, 4, NULL, 1, 125, '2026-02-02 00:00:00', NULL, 0, 0, NULL),
(15, 13, 'REGISTRADO', '2026-02-04 13:41:02', '2026-02-04 13:41:02', NULL, 113, 1, NULL, 1, 8, '2026-02-04 00:00:00', NULL, 0, 0, NULL),
(16, 14, 'REGISTRADO', '2026-02-04 13:41:25', '2026-02-04 13:41:25', NULL, 112, 1, NULL, 1, 8, '2026-02-04 00:00:00', NULL, 0, 0, NULL),
(17, 15, 'REGISTRADO', '2026-02-05 15:37:14', '2026-02-05 15:37:14', NULL, 115, 1, NULL, 1, 8, '2026-02-05 00:00:00', NULL, 0, 0, NULL),
(18, 16, 'REGISTRADO', '2026-02-16 19:10:30', '2026-02-16 19:10:30', NULL, 134, 1, NULL, 1, 8, '2026-02-16 00:00:00', NULL, 0, 0, NULL),
(19, 17, 'REGISTRADO', '2026-02-16 21:12:46', '2026-02-16 21:12:46', NULL, 136, 1, NULL, 1, 8, '2026-02-16 00:00:00', NULL, 0, 0, NULL),
(20, 18, 'REGISTRADO', '2026-02-17 17:19:20', '2026-02-17 17:19:20', NULL, 137, 1, NULL, 1, 8, '2026-02-17 00:00:00', NULL, 0, 0, NULL),
(21, 19, 'REGISTRADO', '2026-02-17 17:20:47', '2026-02-17 17:20:47', NULL, 138, 1, NULL, 1, 8, '2026-02-17 00:00:00', NULL, 0, 0, NULL),
(22, 20, 'REGISTRADO', '2026-02-17 17:26:39', '2026-02-17 17:26:39', NULL, 139, 1, NULL, 1, 8, '2026-02-17 00:00:00', NULL, 0, 0, NULL),
(23, 21, 'REGISTRADO', '2026-02-17 17:58:21', '2026-02-17 17:58:21', NULL, 140, 1, NULL, 1, 8, '2026-02-17 00:00:00', NULL, 0, 0, NULL),
(24, 22, 'REGISTRADO', '2026-02-18 13:11:30', '2026-02-18 13:11:30', NULL, 141, 1, NULL, 1, 8, '2026-02-18 00:00:00', NULL, 0, 0, NULL),
(25, 23, 'REGISTRADO', '2026-02-18 13:13:23', '2026-02-18 13:13:23', NULL, 142, 1, NULL, 1, 8, '2026-02-18 00:00:00', NULL, 0, 0, NULL),
(26, 24, 'ENTREGADO', '2026-02-18 13:53:11', '2026-02-19 16:48:19', NULL, 144, 1, NULL, 1, 8, '2026-02-18 00:00:00', NULL, 0, 0, NULL),
(27, 25, 'EMPACADO', '2026-02-18 14:09:10', '2026-02-19 16:48:56', NULL, 145, 1, NULL, 1, 8, '2026-02-18 00:00:00', NULL, 0, 0, NULL),
(28, 26, 'ENTREGADO', '2026-02-18 14:10:07', '2026-02-19 16:35:32', NULL, 146, 1, NULL, 1, 8, '2026-02-18 00:00:00', NULL, 0, 0, NULL),
(29, 27, 'REGISTRADO', '2026-02-19 17:30:33', '2026-02-19 17:30:33', NULL, 147, 1, 2, 1, 8, '2026-02-19 00:00:00', NULL, 0, 0, ''),
(30, 28, 'REGISTRADO', '2026-02-20 14:47:40', '2026-02-20 14:47:40', NULL, 148, 1, 13, 4, 8, '2026-02-20 00:00:00', NULL, 0, 0, 'recogida en la tarde por mensajero de la empresa'),
(31, 29, 'ENTREGADO', '2026-02-24 20:23:27', '2026-02-24 20:37:58', NULL, 149, 1, 2, 1, 8, '2026-02-24 00:00:00', NULL, 0, 0, ''),
(32, 30, 'ENTREGADO', '2026-01-01 20:31:01', '2026-01-01 20:31:01', NULL, 150, 1, 1, 3, 8, '2026-02-24 00:00:00', NULL, 0, 0, ''),
(33, 31, 'REGISTRADO', '2026-03-10 22:05:09', '2026-03-10 22:05:09', NULL, 151, 1, 1, 1, 8, '2026-03-10 00:00:00', NULL, 0, 0, ''),
(34, 32, 'REGISTRADO', '2026-03-12 20:31:31', '2026-03-12 20:31:31', NULL, 154, 1, 1, 1, 8, '2026-03-12 00:00:00', NULL, 0, 0, ''),
(35, 33, 'REGISTRADO', '2026-03-13 14:07:58', '2026-03-13 14:07:58', NULL, 156, 1, 2, 1, 8, '2026-03-13 00:00:00', NULL, 0, 0, ''),
(36, 34, 'REGISTRADO', '2026-03-13 14:10:29', '2026-03-13 14:10:29', NULL, 155, 1, 2, 1, 8, '2026-03-13 00:00:00', NULL, 0, 0, ''),
(37, 35, 'REGISTRADO', '2026-03-13 15:35:51', '2026-03-13 15:35:51', NULL, 157, 1, 2, 3, 8, '2026-03-13 00:00:00', NULL, 0, 0, ''),
(38, 36, 'REGISTRADO', '2026-04-10 13:36:26', '2026-04-10 13:36:26', NULL, 153, 1, 15, 2, 8, '2026-04-10 00:00:00', NULL, 0, 0, ''),
(39, 37, 'REGISTRADO', '2026-04-10 13:38:47', '2026-04-10 13:38:47', NULL, 146, 1, 1, 1, 8, '2026-04-10 00:00:00', NULL, 0, 0, ''),
(40, 38, 'REGISTRADO', '2026-04-10 13:40:11', '2026-04-10 13:40:11', NULL, 145, 1, 2, 3, 8, '2026-04-10 00:00:00', NULL, 0, 0, ''),
(41, 39, 'REGISTRADO', '2026-04-10 13:41:17', '2026-04-10 13:41:17', NULL, 143, 1, 1, 2, 8, '2026-04-10 00:00:00', NULL, 0, 0, ''),
(42, 40, 'REGISTRADO', '2026-04-10 13:43:57', '2026-04-10 13:43:57', NULL, 138, 1, 7, 4, 8, '2026-04-10 00:00:00', NULL, 0, 0, ''),
(43, 41, 'REGISTRADO', '2026-04-10 13:44:54', '2026-04-10 13:44:54', NULL, 137, 1, 10, 5, 8, '2026-04-10 00:00:00', NULL, 0, 0, ''),
(44, 42, 'REGISTRADO', '2026-04-10 13:51:57', '2026-04-10 13:51:57', NULL, 136, 1, 11, 6, 8, '2026-04-10 00:00:00', NULL, 0, 0, ''),
(45, 43, 'REGISTRADO', '2026-04-10 13:52:48', '2026-04-10 13:52:48', NULL, 135, 1, 13, 7, 8, '2026-04-10 00:00:00', NULL, 0, 0, 'Picap'),
(46, 44, 'REGISTRADO', '2026-04-10 13:53:33', '2026-04-10 13:53:33', NULL, 134, 1, 15, 8, 8, '2026-04-10 00:00:00', NULL, 0, 0, ''),
(47, 45, 'REGISTRADO', '2026-04-10 13:54:18', '2026-04-10 13:54:18', NULL, 132, 1, 16, 10, 8, '2026-04-10 00:00:00', NULL, 0, 0, ''),
(48, 46, 'REGISTRADO', '2026-04-10 13:55:00', '2026-04-10 13:55:00', NULL, 131, 1, 18, 11, 8, '2026-04-10 00:00:00', NULL, 0, 0, ''),
(49, 47, 'REGISTRADO', '2026-04-10 13:55:50', '2026-04-10 13:55:50', NULL, 130, 1, 20, 12, 8, '2026-04-10 00:00:00', NULL, 0, 0, ''),
(50, 48, 'ENTREGADO', '2026-04-12 18:04:04', '2026-04-12 18:06:24', NULL, 165, 1, 1, 2, 8, '2026-04-12 00:00:00', NULL, 0, 0, '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_seriales`
--

CREATE TABLE `inv_seriales` (
  `id` int NOT NULL,
  `itemId` int DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `serial` varchar(100) DEFAULT NULL,
  `status` tinyint DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_status`
--

CREATE TABLE `inv_status` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL DEFAULT 'name',
  `application` int NOT NULL COMMENT 'variable de aplicacion del estado',
  `status` tinyint NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_store`
--

CREATE TABLE `inv_store` (
  `id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '1',
  `warehouseId` int DEFAULT NULL,
  `store_manager` int DEFAULT NULL,
  `status` tinyint DEFAULT '1',
  `api_data_id` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `inv_store`
--

INSERT INTO `inv_store` (`id`, `name`, `warehouseId`, `store_manager`, `status`, `api_data_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'PRINCIPAL', 8, NULL, 1, '1', '2024-01-03 00:00:00', NULL, NULL),
(2, 'MARSELLA -Fervicom', 8, NULL, 0, '2', '2024-01-03 00:00:00', NULL, NULL),
(3, 'Bonanza', 8, NULL, 0, '3', '2024-01-03 00:00:00', NULL, NULL),
(4, 'PRINCIPAL - Marsella', 96, NULL, 1, '', '2026-01-26 10:43:13', '2026-01-29 15:43:31', NULL),
(6, 'PRINCIPAL - Normandia', 118, NULL, 1, NULL, '2026-01-26 16:48:15', '2026-01-26 16:48:15', NULL),
(7, 'Villa luz', 118, NULL, 1, NULL, '2026-01-26 16:48:16', '2026-01-26 16:48:16', NULL),
(8, 'Carrera 78B', 96, NULL, 1, NULL, '2026-01-29 15:43:31', '2026-01-29 15:43:31', NULL),
(9, 'PRINCIPAL', 128, NULL, 1, NULL, '2026-01-31 00:31:22', '2026-01-31 00:31:22', NULL),
(10, 'PRINCIPAL', 145, NULL, 1, NULL, '2026-02-06 20:20:20', '2026-02-06 20:20:20', NULL),
(11, 'PRINCIPAL', 156, NULL, 1, NULL, '2026-03-13 21:01:38', '2026-03-13 21:30:47', NULL),
(12, 'quinta', 156, NULL, 1, NULL, '2026-03-13 21:28:05', '2026-03-13 21:30:47', NULL),
(13, 'prueba', 156, NULL, 1, NULL, '2026-03-13 21:30:47', '2026-03-13 21:30:47', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_transfers`
--

CREATE TABLE `inv_transfers` (
  `id` int NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `observations` text NOT NULL,
  `status` enum('REGISTRADO','ENTREGADO','ANULADO','EN TRANSITO') NOT NULL DEFAULT 'REGISTRADO',
  `api_data_id` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `storeFromId` int DEFAULT NULL,
  `storeToId` int DEFAULT NULL,
  `consecutive` int NOT NULL,
  `userId` int NOT NULL,
  `packing` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `inv_transfers`
--

INSERT INTO `inv_transfers` (`id`, `date`, `observations`, `status`, `api_data_id`, `created_at`, `updated_at`, `deleted_at`, `storeFromId`, `storeToId`, `consecutive`, `userId`, `packing`) VALUES
(2, '2026-02-12 21:49:22', 'Transferencia desde solicitud #1', 'ENTREGADO', NULL, '2026-02-12 21:49:22', '2026-02-12 22:01:45', NULL, 6, 7, 1, 154, '{\"bolsas\":\"0\",\"canastas\":\"1\",\"cajas\":\"0\"}'),
(3, '2026-02-12 21:49:39', 'Transferencia desde solicitud #1', 'ENTREGADO', NULL, '2026-02-12 21:49:39', '2026-02-12 21:56:58', NULL, 6, 7, 2, 154, '{\"bolsas\":\"0\",\"canastas\":\"0\",\"cajas\":\"1\"}'),
(4, '2026-02-12 21:49:55', 'Transferencia desde solicitud #1', 'ENTREGADO', NULL, '2026-02-12 21:49:55', '2026-02-12 21:56:28', NULL, 6, 7, 3, 154, '{\"bolsas\":\"0\",\"canastas\":\"0\",\"cajas\":\"1\"}'),
(5, '2026-02-13 13:58:37', 'Transferencia desde solicitud #1', 'REGISTRADO', NULL, '2026-02-13 13:58:37', '2026-02-13 13:58:37', NULL, 6, 7, 4, 153, NULL),
(6, '2026-02-13 14:10:52', 'Transferencia desde solicitud #1', 'REGISTRADO', NULL, '2026-02-13 14:10:52', '2026-02-13 14:10:52', NULL, 6, 7, 5, 153, NULL),
(7, '2026-02-13 14:12:25', 'Transferencia desde solicitud #1', 'REGISTRADO', NULL, '2026-02-13 14:12:25', '2026-02-13 14:12:25', NULL, 6, 7, 6, 153, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_transfer_requests`
--

CREATE TABLE `inv_transfer_requests` (
  `id` int NOT NULL,
  `status` enum('REGISTRADO','EN PROGRESO','ENTREGADO') DEFAULT 'REGISTRADO',
  `date` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `quoteId` int DEFAULT NULL,
  `warehouseId` int DEFAULT '0',
  `observations` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `inv_transfer_requests`
--

INSERT INTO `inv_transfer_requests` (`id`, `status`, `date`, `created_at`, `updated_at`, `deleted_at`, `quoteId`, `warehouseId`, `observations`) VALUES
(1, 'EN PROGRESO', '2026-02-12 21:44:24', '2026-02-12 21:44:24', '2026-02-13 13:58:37', NULL, NULL, 7, 'Solicitud para surtir la bodega de Villa Luz');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_unit_measurements`
--

CREATE TABLE `inv_unit_measurements` (
  `id` int NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '1',
  `status` tinyint DEFAULT '1',
  `quantity` int NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `inv_unit_measurements`
--

INSERT INTO `inv_unit_measurements` (`id`, `description`, `status`, `quantity`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'CAJA X 10', 1, 10, '2025-11-12 19:30:00', NULL, NULL),
(2, 'CAJA X 100', 1, 100, '2025-11-12 19:30:00', NULL, NULL),
(3, 'CAJA X 1000', 1, 1000, '2025-11-12 19:30:00', NULL, NULL),
(4, 'CAJA X 12', 1, 12, '2025-11-12 19:30:00', NULL, NULL),
(5, 'CAJA X 13', 1, 13, '2025-11-12 19:30:01', NULL, NULL),
(6, 'CAJA X 15', 1, 15, '2025-11-12 19:30:01', NULL, NULL),
(7, 'CAJA X 20', 1, 20, '2025-11-12 19:30:01', NULL, NULL),
(8, 'CAJA X 200', 1, 200, '2025-11-12 19:30:01', NULL, NULL),
(9, 'CAJA X 24', 1, 24, '2025-11-12 19:30:02', NULL, NULL),
(10, 'CAJA X 25', 1, 25, '2025-11-12 19:30:02', NULL, NULL),
(11, 'CAJA X 28', 1, 28, '2025-11-12 19:30:02', NULL, NULL),
(12, 'CAJA X 30', 1, 30, '2025-11-12 19:30:03', NULL, NULL),
(13, 'CAJA X 4', 1, 4, '2025-11-12 19:30:03', NULL, NULL),
(14, 'CAJA X 5', 1, 5, '2025-11-12 19:30:03', NULL, NULL),
(15, 'CAJA X 50', 1, 50, '2025-11-12 19:30:03', NULL, NULL),
(16, 'CAJA X 500', 1, 500, '2025-11-12 19:30:04', NULL, NULL),
(17, 'CAJA X 6', 1, 6, '2025-11-12 19:30:04', NULL, NULL),
(18, 'CAJA X 8', 1, 8, '2025-11-12 19:30:04', NULL, NULL),
(19, 'CAJA X 9', 1, 9, '2025-11-12 19:30:04', NULL, NULL),
(20, 'PAQUETE X 10', 1, 10, '2025-11-12 19:30:04', NULL, NULL),
(21, 'PAQUETE X 100', 1, 100, '2025-11-12 19:30:04', NULL, NULL),
(22, 'PAQUETE X 12', 1, 12, '2025-11-12 19:30:05', NULL, NULL),
(23, 'PAQUETE X 150', 1, 150, '2025-11-12 19:30:05', NULL, NULL),
(24, 'PAQUETE X 16', 1, 16, '2025-11-12 19:30:05', NULL, NULL),
(25, 'PAQUETE X 20', 1, 20, '2025-11-12 19:30:05', NULL, NULL),
(26, 'PAQUETE X 25', 1, 25, '2025-11-12 19:30:05', NULL, NULL),
(27, 'PAQUETE X 3', 1, 3, '2025-11-12 19:30:05', NULL, NULL),
(28, 'PAQUETE X 4', 1, 4, '2025-11-12 19:30:06', NULL, NULL),
(29, 'PAQUETE X 4', 0, 4, '2025-11-12 19:30:06', '2025-11-18 15:39:05', NULL),
(30, 'PAQUETE X 40', 1, 40, '2025-11-12 19:30:06', NULL, NULL),
(31, 'PAQUETE X 5', 1, 5, '2025-11-12 19:30:06', NULL, NULL),
(32, 'PAQUETE X 50', 1, 50, '2025-11-12 19:30:06', NULL, NULL),
(33, 'PAQUETE X 6', 1, 6, '2025-11-12 19:30:06', NULL, NULL),
(34, 'PAQUETE X 80', 1, 80, '2025-11-12 19:30:07', NULL, NULL),
(35, 'UNIDAD', 1, 1, '2025-11-12 19:30:07', NULL, NULL),
(36, 'BOTELLA 600 ML', 1, 600, '2025-11-12 21:21:41', '2025-11-12 21:21:41', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_values`
--

CREATE TABLE `inv_values` (
  `id` int NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `values` double NOT NULL DEFAULT '0',
  `type` enum('costo','precio') DEFAULT NULL,
  `itemId` int DEFAULT NULL,
  `warehouseId` int DEFAULT NULL,
  `label` enum('Costo Inicial','Costo','Precio Base','Precio Regular','Precio Crédito','Precio unitario x caja') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `inv_values`
--

INSERT INTO `inv_values` (`id`, `date`, `values`, `type`, `itemId`, `warehouseId`, `label`, `created_at`, `updated_at`, `deleted_at`) VALUES
(3, '2026-02-05 00:00:00', 60782, 'costo', 3, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(4, '2026-02-05 00:00:00', 20790, 'costo', 4, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(5, '2026-02-05 00:00:00', 33546, 'costo', 5, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(6, '2026-02-05 00:00:00', 67810, 'costo', 6, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(9, '2026-02-05 00:00:00', 20771, 'costo', 9, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(10, '2026-02-05 00:00:00', 26413, 'costo', 10, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(11, '2026-02-05 00:00:00', 20771, 'costo', 11, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(12, '2026-02-05 00:00:00', 50641, 'costo', 12, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(13, '2026-02-05 00:00:00', 0, 'costo', 13, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(14, '2026-02-05 00:00:00', 137210, 'costo', 14, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(15, '2026-02-05 00:00:00', 183557, 'costo', 15, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(16, '2026-02-05 00:00:00', 373014, 'costo', 16, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(17, '2026-02-05 00:00:00', 327547, 'costo', 17, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(18, '2026-02-05 00:00:00', 67405, 'costo', 18, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(19, '2026-02-05 00:00:00', 304909, 'costo', 19, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(20, '2026-02-05 00:00:00', 218225, 'costo', 20, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(21, '2026-02-05 00:00:00', 53309, 'costo', 21, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(22, '2026-02-05 00:00:00', 74066, 'costo', 22, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(23, '2026-02-05 00:00:00', 100664, 'costo', 23, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(24, '2026-02-05 00:00:00', 276529, 'costo', 24, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(25, '2026-02-05 00:00:00', 104491, 'costo', 25, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(26, '2026-02-05 00:00:00', 191050, 'costo', 26, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(27, '2026-02-05 00:00:00', 638655, 'costo', 27, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(28, '2026-02-05 00:00:00', 0, 'costo', 28, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(29, '2026-02-05 00:00:00', 672268, 'costo', 29, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(30, '2026-02-05 00:00:00', 0, 'costo', 30, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(31, '2026-02-05 00:00:00', 293109, 'costo', 31, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(32, '2026-02-05 00:00:00', 0, 'costo', 32, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(33, '2026-02-05 00:00:00', 100455, 'costo', 33, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(34, '2026-02-05 00:00:00', 345378, 'costo', 34, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(35, '2026-02-05 00:00:00', 317770, 'costo', 35, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(36, '2026-02-05 00:00:00', 262688, 'costo', 36, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(37, '2026-02-05 00:00:00', 609243, 'costo', 37, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(38, '2026-02-05 00:00:00', 0, 'costo', 38, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(39, '2026-02-05 00:00:00', 0, 'costo', 39, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(40, '2026-02-05 00:00:00', 0, 'costo', 40, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(41, '2026-02-05 00:00:00', 163019, 'costo', 41, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(42, '2026-02-05 00:00:00', 100664, 'costo', 42, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(43, '2026-02-05 00:00:00', 294436, 'costo', 43, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(44, '2026-02-05 00:00:00', 296618, 'costo', 44, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(45, '2026-02-05 00:00:00', 507458, 'costo', 45, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(46, '2026-02-05 00:00:00', 1155462, 'costo', 46, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(47, '2026-02-05 00:00:00', 682911, 'costo', 47, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(48, '2026-02-05 00:00:00', 677592, 'costo', 48, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(49, '2026-02-05 00:00:00', 0, 'costo', 49, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(50, '2026-02-05 00:00:00', 405042, 'costo', 50, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(51, '2026-02-05 00:00:00', 285640, 'costo', 51, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(52, '2026-02-05 00:00:00', 512053, 'costo', 52, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(53, '2026-02-05 00:00:00', 563198, 'costo', 53, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(54, '2026-02-05 00:00:00', 904381, 'costo', 54, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(55, '2026-02-05 00:00:00', 733968, 'costo', 55, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(56, '2026-02-05 00:00:00', 552644, 'costo', 56, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(57, '2026-02-05 00:00:00', 1418053, 'costo', 57, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(58, '2026-02-05 00:00:00', 0, 'costo', 58, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(59, '2026-02-05 00:00:00', 973109, 'costo', 59, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(60, '2026-02-05 00:00:00', 588533, 'costo', 60, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(61, '2026-02-05 00:00:00', 2259831, 'costo', 61, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(62, '2026-02-05 00:00:00', 1803388, 'costo', 62, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(63, '2026-02-05 00:00:00', 526050, 'costo', 63, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(64, '2026-02-05 00:00:00', 201680, 'costo', 64, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(65, '2026-02-05 00:00:00', 1410654, 'costo', 65, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(66, '2026-02-05 00:00:00', 299159, 'costo', 66, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(67, '2026-02-05 00:00:00', 218137, 'costo', 67, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(68, '2026-02-05 00:00:00', 256589, 'costo', 68, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(69, '2026-02-05 00:00:00', 646218, 'costo', 69, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(70, '2026-02-05 00:00:00', 0, 'costo', 70, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(71, '2026-02-05 00:00:00', 225051, 'costo', 71, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(72, '2026-02-05 00:00:00', 897843, 'costo', 72, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(73, '2026-02-05 00:00:00', 0, 'costo', 73, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(74, '2026-02-05 00:00:00', 0, 'costo', 74, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(75, '2026-02-05 00:00:00', 0, 'costo', 75, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(76, '2026-02-05 00:00:00', 0, 'costo', 76, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(77, '2026-02-05 00:00:00', 0, 'costo', 77, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(78, '2026-02-05 00:00:00', 0, 'costo', 78, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(79, '2026-02-05 00:00:00', 308603, 'costo', 79, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(80, '2026-02-05 00:00:00', 0, 'costo', 80, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(81, '2026-02-05 00:00:00', 0, 'costo', 81, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(82, '2026-02-05 00:00:00', 0, 'costo', 82, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(83, '2026-02-05 00:00:00', 0, 'costo', 83, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(84, '2026-02-05 00:00:00', 0, 'costo', 84, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(85, '2026-02-05 00:00:00', 242622, 'costo', 85, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(86, '2026-02-05 00:00:00', 588235, 'costo', 86, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(87, '2026-02-05 00:00:00', 793729, 'costo', 87, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(88, '2026-02-05 00:00:00', 333310, 'costo', 88, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(89, '2026-02-05 00:00:00', 519731, 'costo', 89, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(90, '2026-02-05 00:00:00', 0, 'costo', 90, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(91, '2026-02-05 00:00:00', 0, 'costo', 91, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(92, '2026-02-05 00:00:00', 0, 'costo', 92, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(93, '2026-02-05 00:00:00', 280988, 'costo', 93, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(94, '2026-02-05 00:00:00', 0, 'costo', 94, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(95, '2026-02-05 00:00:00', 661602, 'costo', 95, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(96, '2026-02-05 00:00:00', 1594117, 'costo', 96, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(97, '2026-02-05 00:00:00', 0, 'costo', 97, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(98, '2026-02-05 00:00:00', 0, 'costo', 98, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(99, '2026-02-05 00:00:00', 655462, 'costo', 99, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(100, '2026-02-05 00:00:00', 539510, 'costo', 100, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(101, '2026-02-05 00:00:00', 65797, 'costo', 101, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(102, '2026-02-05 00:00:00', 327731, 'costo', 102, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(103, '2026-02-05 00:00:00', 0, 'costo', 103, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(104, '2026-02-05 00:00:00', 0, 'costo', 104, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(105, '2026-02-05 00:00:00', 0, 'costo', 105, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(106, '2026-02-05 00:00:00', 0, 'costo', 106, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(107, '2026-02-05 00:00:00', 0, 'costo', 107, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(108, '2026-02-05 00:00:00', 0, 'costo', 108, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(109, '2026-02-05 00:00:00', 0, 'costo', 109, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(110, '2026-02-05 00:00:00', 0, 'costo', 110, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(111, '2026-02-05 00:00:00', 0, 'costo', 111, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(112, '2026-02-05 00:00:00', 827731, 'costo', 112, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(113, '2026-02-05 00:00:00', 287394, 'costo', 113, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(114, '2026-02-05 00:00:00', 1200000, 'costo', 114, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(115, '2026-02-05 00:00:00', 433152, 'costo', 115, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(116, '2026-02-05 00:00:00', 323317, 'costo', 116, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(117, '2026-02-05 00:00:00', 0, 'costo', 117, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(118, '2026-02-05 00:00:00', 94972, 'costo', 118, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(119, '2026-02-05 00:00:00', 71219, 'costo', 119, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(120, '2026-02-05 00:00:00', 185419, 'costo', 120, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(121, '2026-02-05 00:00:00', 0, 'costo', 121, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(122, '2026-02-05 00:00:00', 0, 'costo', 122, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(123, '2026-02-05 00:00:00', 0, 'costo', 123, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(124, '2026-02-05 00:00:00', 0, 'costo', 124, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(125, '2026-02-05 00:00:00', 0, 'costo', 125, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(126, '2026-02-05 00:00:00', 0, 'costo', 126, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(127, '2026-02-05 00:00:00', 0, 'costo', 127, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(128, '2026-02-05 00:00:00', 0, 'costo', 128, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(129, '2026-02-05 00:00:00', 0, 'costo', 129, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(130, '2026-02-05 00:00:00', 0, 'costo', 130, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(131, '2026-02-05 00:00:00', 0, 'costo', 131, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(132, '2026-02-05 00:00:00', 0, 'costo', 132, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(133, '2026-02-05 00:00:00', 0, 'costo', 133, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(134, '2026-02-05 00:00:00', 0, 'costo', 134, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(135, '2026-02-05 00:00:00', 0, 'costo', 135, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(136, '2026-02-05 00:00:00', 0, 'costo', 136, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(137, '2026-02-05 00:00:00', 0, 'costo', 137, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(138, '2026-02-05 00:00:00', 0, 'costo', 138, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(139, '2026-02-05 00:00:00', 0, 'costo', 139, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(140, '2026-02-05 00:00:00', 0, 'costo', 140, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(141, '2026-02-05 00:00:00', 0, 'costo', 141, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(142, '2026-02-05 00:00:00', 0, 'costo', 142, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(143, '2026-02-05 00:00:00', 0, 'costo', 143, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(144, '2026-02-05 00:00:00', 0, 'costo', 144, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(145, '2026-02-05 00:00:00', 0, 'costo', 145, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(146, '2026-02-05 00:00:00', 0, 'costo', 146, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(147, '2026-02-05 00:00:00', 0, 'costo', 147, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(148, '2026-02-05 00:00:00', 0, 'costo', 148, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(149, '2026-02-05 00:00:00', 0, 'costo', 149, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(150, '2026-02-05 00:00:00', 0, 'costo', 150, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(151, '2026-02-05 00:00:00', 0, 'costo', 151, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(152, '2026-02-05 00:00:00', 0, 'costo', 152, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(153, '2026-02-05 00:00:00', 100455, 'costo', 153, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(154, '2026-02-05 00:00:00', 497028, 'costo', 154, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(155, '2026-02-05 00:00:00', 101372, 'costo', 155, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(156, '2026-02-05 00:00:00', 243697, 'costo', 156, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(157, '2026-02-05 00:00:00', 199877, 'costo', 157, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(158, '2026-02-05 00:00:00', 73359, 'costo', 158, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(159, '2026-02-05 00:00:00', 121662, 'costo', 159, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(160, '2026-02-05 00:00:00', 327731, 'costo', 160, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(161, '2026-02-05 00:00:00', 30000, 'costo', 161, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(162, '2026-02-05 00:00:00', 8630, 'costo', 162, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(163, '2026-02-05 00:00:00', 10235, 'costo', 163, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(164, '2026-02-05 00:00:00', 5153, 'costo', 164, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(165, '2026-02-05 00:00:00', 29932, 'costo', 165, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(166, '2026-02-05 00:00:00', 29932, 'costo', 166, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(167, '2026-02-05 00:00:00', 29932, 'costo', 167, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(168, '2026-02-05 00:00:00', 16844, 'costo', 168, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(169, '2026-02-05 00:00:00', 1190, 'costo', 169, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(170, '2026-02-05 00:00:00', 90000, 'costo', 170, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(171, '2026-02-05 00:00:00', 14828, 'costo', 171, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(172, '2026-02-05 00:00:00', 18987, 'costo', 172, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(173, '2026-02-05 00:00:00', 10390, 'costo', 173, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(174, '2026-02-05 00:00:00', 14828, 'costo', 174, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(175, '2026-02-05 00:00:00', 23960, 'costo', 175, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(176, '2026-02-05 00:00:00', 0, 'costo', 176, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(177, '2026-02-05 00:00:00', 26584, 'costo', 177, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(178, '2026-02-05 00:00:00', 31967, 'costo', 178, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(179, '2026-02-05 00:00:00', 0, 'costo', 179, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(180, '2026-02-05 00:00:00', 0, 'costo', 180, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(181, '2026-02-05 00:00:00', 0, 'costo', 181, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(182, '2026-02-05 00:00:00', 0, 'costo', 182, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(183, '2026-02-05 00:00:00', 0, 'costo', 183, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(184, '2026-02-05 00:00:00', 0, 'costo', 184, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(185, '2026-02-05 00:00:00', 22061, 'costo', 185, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(186, '2026-02-05 00:00:00', 28422, 'costo', 186, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(187, '2026-02-05 00:00:00', 0, 'costo', 187, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(188, '2026-02-05 00:00:00', 52351, 'costo', 188, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(189, '2026-02-05 00:00:00', 27575, 'costo', 189, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(190, '2026-02-05 00:00:00', 27575, 'costo', 190, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(191, '2026-02-05 00:00:00', 32459, 'costo', 191, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(192, '2026-02-05 00:00:00', 0, 'costo', 192, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(193, '2026-02-05 00:00:00', 14642, 'costo', 193, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(194, '2026-02-05 00:00:00', 18748, 'costo', 194, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(195, '2026-02-05 00:00:00', 11429, 'costo', 195, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(196, '2026-02-05 00:00:00', 14642, 'costo', 196, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(197, '2026-02-05 00:00:00', 26602, 'costo', 197, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(198, '2026-02-05 00:00:00', 29146, 'costo', 198, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(199, '2026-02-05 00:00:00', 21782, 'costo', 199, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(200, '2026-02-05 00:00:00', 30125, 'costo', 200, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(201, '2026-02-05 00:00:00', 26492, 'costo', 201, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(202, '2026-02-05 00:00:00', 32459, 'costo', 202, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(203, '2026-02-05 00:00:00', 28644, 'costo', 203, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(204, '2026-02-05 00:00:00', 45790, 'costo', 204, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(205, '2026-02-05 00:00:00', 34290, 'costo', 205, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(206, '2026-02-05 00:00:00', 62063, 'costo', 206, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(207, '2026-02-05 00:00:00', 34290, 'costo', 207, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(208, '2026-02-05 00:00:00', 40102, 'costo', 208, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(209, '2026-02-05 00:00:00', 40102, 'costo', 209, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(210, '2026-02-05 00:00:00', 64181, 'costo', 210, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(211, '2026-02-05 00:00:00', 73397, 'costo', 211, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(212, '2026-02-05 00:00:00', 98762, 'costo', 212, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(213, '2026-02-05 00:00:00', 61731, 'costo', 213, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(214, '2026-02-05 00:00:00', 70117, 'costo', 214, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(215, '2026-02-05 00:00:00', 60153, 'costo', 215, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(216, '2026-02-05 00:00:00', 43, 'costo', 216, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(217, '2026-02-05 00:00:00', 79, 'costo', 217, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(218, '2026-02-05 00:00:00', 92, 'costo', 218, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(219, '2026-02-05 00:00:00', 80, 'costo', 219, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(220, '2026-02-05 00:00:00', 37, 'costo', 220, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(221, '2026-02-05 00:00:00', 51, 'costo', 221, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(222, '2026-02-05 00:00:00', 34, 'costo', 222, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(223, '2026-02-05 00:00:00', 22, 'costo', 223, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(224, '2026-02-05 00:00:00', 18, 'costo', 224, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(225, '2026-02-05 00:00:00', 17, 'costo', 225, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(226, '2026-02-05 00:00:00', 93, 'costo', 226, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(227, '2026-02-05 00:00:00', 120, 'costo', 227, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(228, '2026-02-05 00:00:00', 75, 'costo', 228, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(229, '2026-02-05 00:00:00', 92, 'costo', 229, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(230, '2026-02-05 00:00:00', 75, 'costo', 230, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(231, '2026-02-05 00:00:00', 71, 'costo', 231, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(232, '2026-02-05 00:00:00', 39375, 'costo', 232, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(233, '2026-02-05 00:00:00', 49, 'costo', 233, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(234, '2026-02-05 00:00:00', 49, 'costo', 234, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(235, '2026-02-05 00:00:00', 88, 'costo', 235, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(236, '2026-02-05 00:00:00', 63, 'costo', 236, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(237, '2026-02-05 00:00:00', 148, 'costo', 237, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(238, '2026-02-05 00:00:00', 140, 'costo', 238, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(239, '2026-02-05 00:00:00', 139, 'costo', 239, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(240, '2026-02-05 00:00:00', 52, 'costo', 240, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(241, '2026-02-05 00:00:00', 33, 'costo', 241, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(242, '2026-02-05 00:00:00', 36, 'costo', 242, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(243, '2026-02-05 00:00:00', 36, 'costo', 243, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(244, '2026-02-05 00:00:00', 1314, 'costo', 244, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(245, '2026-02-05 00:00:00', 1314, 'costo', 245, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(246, '2026-02-05 00:00:00', 1152, 'costo', 246, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(247, '2026-02-05 00:00:00', 1012, 'costo', 247, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(248, '2026-02-05 00:00:00', 1211, 'costo', 248, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(249, '2026-02-05 00:00:00', 1141, 'costo', 249, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(250, '2026-02-05 00:00:00', 11989, 'costo', 250, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(251, '2026-02-05 00:00:00', 30462, 'costo', 251, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(252, '2026-02-05 00:00:00', 1613, 'costo', 252, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(253, '2026-02-05 00:00:00', 1274, 'costo', 253, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(254, '2026-02-05 00:00:00', 1613, 'costo', 254, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(255, '2026-02-05 00:00:00', 10, 'costo', 255, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(256, '2026-02-05 00:00:00', 77, 'costo', 256, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(257, '2026-02-05 00:00:00', 55, 'costo', 257, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(258, '2026-02-05 00:00:00', 55, 'costo', 258, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(259, '2026-02-05 00:00:00', 103, 'costo', 259, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(260, '2026-02-05 00:00:00', 84, 'costo', 260, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(261, '2026-02-05 00:00:00', 101, 'costo', 261, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(262, '2026-02-05 00:00:00', 234, 'costo', 262, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(263, '2026-02-05 00:00:00', 345, 'costo', 263, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(264, '2026-02-05 00:00:00', 85, 'costo', 264, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(265, '2026-02-05 00:00:00', 44, 'costo', 265, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(266, '2026-02-05 00:00:00', 45, 'costo', 266, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(267, '2026-02-05 00:00:00', 45, 'costo', 267, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(268, '2026-02-05 00:00:00', 44, 'costo', 268, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(269, '2026-02-05 00:00:00', 88, 'costo', 269, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(270, '2026-02-05 00:00:00', 222, 'costo', 270, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(271, '2026-02-05 00:00:00', 2620, 'costo', 271, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(272, '2026-02-05 00:00:00', 3145, 'costo', 272, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(273, '2026-02-05 00:00:00', 7932, 'costo', 273, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(274, '2026-02-05 00:00:00', 12748, 'costo', 274, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(275, '2026-02-05 00:00:00', 9865546, 'costo', 276, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(276, '2026-02-05 00:00:00', 1692, 'costo', 277, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(277, '2026-02-05 00:00:00', 1893, 'costo', 278, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(278, '2026-02-05 00:00:00', 1917, 'costo', 279, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(279, '2026-02-05 00:00:00', 5330, 'costo', 280, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(280, '2026-02-05 00:00:00', 4978, 'costo', 281, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(281, '2026-02-05 00:00:00', 5917, 'costo', 282, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(282, '2026-02-05 00:00:00', 6340, 'costo', 283, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(283, '2026-02-05 00:00:00', 7888, 'costo', 284, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(284, '2026-02-05 00:00:00', 4039, 'costo', 285, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(285, '2026-02-05 00:00:00', 1620, 'costo', 286, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(286, '2026-02-05 00:00:00', 392717, 'costo', 287, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(287, '2026-02-05 00:00:00', 503146, 'costo', 288, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(288, '2026-02-05 00:00:00', 347238, 'costo', 289, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(289, '2026-02-05 00:00:00', 43581, 'costo', 290, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(290, '2026-02-05 00:00:00', 392717, 'costo', 291, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(291, '2026-02-05 00:00:00', 8049, 'costo', 292, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(292, '2026-02-05 00:00:00', 3995, 'costo', 293, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(293, '2026-02-05 00:00:00', 5343, 'costo', 294, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(294, '2026-02-05 00:00:00', 7991, 'costo', 295, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(295, '2026-02-05 00:00:00', 0, 'costo', 296, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(296, '2026-02-05 00:00:00', 25937, 'costo', 297, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(297, '2026-02-05 00:00:00', 544443, 'costo', 298, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(298, '2026-02-05 00:00:00', 113196, 'costo', 299, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(299, '2026-02-05 00:00:00', 167431, 'costo', 300, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(300, '2026-02-05 00:00:00', 97487, 'costo', 301, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(301, '2026-02-05 00:00:00', 106086, 'costo', 302, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(302, '2026-02-05 00:00:00', 782113, 'costo', 303, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(303, '2026-02-05 00:00:00', 0, 'costo', 304, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(304, '2026-02-05 00:00:00', 267550, 'costo', 305, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(305, '2026-02-05 00:00:00', 471479, 'costo', 306, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(306, '2026-02-05 00:00:00', 466791, 'costo', 307, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(307, '2026-02-05 00:00:00', 466791, 'costo', 308, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(308, '2026-02-05 00:00:00', 439529, 'costo', 309, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(309, '2026-02-05 00:00:00', 15592, 'costo', 310, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(310, '2026-02-05 00:00:00', 284672, 'costo', 311, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(311, '2026-02-05 00:00:00', 275404, 'costo', 312, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(312, '2026-02-05 00:00:00', 0, 'costo', 313, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(313, '2026-02-05 00:00:00', 0, 'costo', 314, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(314, '2026-02-05 00:00:00', 49028, 'costo', 315, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(315, '2026-02-05 00:00:00', 19738, 'costo', 316, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(316, '2026-02-05 00:00:00', 372, 'costo', 317, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(317, '2026-02-05 00:00:00', 756, 'costo', 318, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(318, '2026-02-05 00:00:00', 304, 'costo', 319, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(319, '2026-02-05 00:00:00', 448, 'costo', 320, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(320, '2026-02-05 00:00:00', 502, 'costo', 321, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(321, '2026-02-05 00:00:00', 529, 'costo', 322, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(322, '2026-02-05 00:00:00', 477, 'costo', 323, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(323, '2026-02-05 00:00:00', 593, 'costo', 324, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(324, '2026-02-05 00:00:00', 501, 'costo', 325, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(325, '2026-02-05 00:00:00', 553, 'costo', 326, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(326, '2026-02-05 00:00:00', 836, 'costo', 327, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(327, '2026-02-05 00:00:00', 836, 'costo', 328, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(328, '2026-02-05 00:00:00', 477, 'costo', 329, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(329, '2026-02-05 00:00:00', 544, 'costo', 330, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(330, '2026-02-05 00:00:00', 649, 'costo', 331, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(331, '2026-02-05 00:00:00', 505, 'costo', 332, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(332, '2026-02-05 00:00:00', 613, 'costo', 333, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(333, '2026-02-05 00:00:00', 383, 'costo', 334, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(334, '2026-02-05 00:00:00', 378, 'costo', 335, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(335, '2026-02-05 00:00:00', 340, 'costo', 336, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(336, '2026-02-05 00:00:00', 357, 'costo', 337, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(337, '2026-02-05 00:00:00', 394, 'costo', 338, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(338, '2026-02-05 00:00:00', 358, 'costo', 339, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(339, '2026-02-05 00:00:00', 372, 'costo', 340, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(340, '2026-02-05 00:00:00', 457, 'costo', 341, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(341, '2026-02-05 00:00:00', 332, 'costo', 342, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(342, '2026-02-05 00:00:00', 346, 'costo', 343, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(343, '2026-02-05 00:00:00', 579, 'costo', 344, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(344, '2026-02-05 00:00:00', 382, 'costo', 345, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(345, '2026-02-05 00:00:00', 485, 'costo', 346, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(346, '2026-02-05 00:00:00', 349, 'costo', 347, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(347, '2026-02-05 00:00:00', 437, 'costo', 348, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(348, '2026-02-05 00:00:00', 424, 'costo', 349, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(349, '2026-02-05 00:00:00', 657, 'costo', 350, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(350, '2026-02-05 00:00:00', 492, 'costo', 351, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(351, '2026-02-05 00:00:00', 424, 'costo', 352, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(352, '2026-02-05 00:00:00', 383, 'costo', 353, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(353, '2026-02-05 00:00:00', 639, 'costo', 354, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(354, '2026-02-05 00:00:00', 601, 'costo', 355, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(355, '2026-02-05 00:00:00', 519, 'costo', 356, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(356, '2026-02-05 00:00:00', 545, 'costo', 357, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(357, '2026-02-05 00:00:00', 527, 'costo', 358, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(358, '2026-02-05 00:00:00', 462, 'costo', 359, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(359, '2026-02-05 00:00:00', 586, 'costo', 360, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(360, '2026-02-05 00:00:00', 304, 'costo', 361, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(361, '2026-02-05 00:00:00', 425, 'costo', 362, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(362, '2026-02-05 00:00:00', 469, 'costo', 363, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(363, '2026-02-05 00:00:00', 469, 'costo', 364, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(364, '2026-02-05 00:00:00', 469, 'costo', 365, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(365, '2026-02-05 00:00:00', 469, 'costo', 366, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(366, '2026-02-05 00:00:00', 469, 'costo', 367, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(367, '2026-02-05 00:00:00', 469, 'costo', 368, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(368, '2026-02-05 00:00:00', 469, 'costo', 369, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(369, '2026-02-05 00:00:00', 469, 'costo', 370, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(370, '2026-02-05 00:00:00', 512, 'costo', 371, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(371, '2026-02-05 00:00:00', 512, 'costo', 372, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(372, '2026-02-05 00:00:00', 512, 'costo', 373, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(373, '2026-02-05 00:00:00', 512, 'costo', 374, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(374, '2026-02-05 00:00:00', 512, 'costo', 375, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(375, '2026-02-05 00:00:00', 512, 'costo', 376, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(376, '2026-02-05 00:00:00', 512, 'costo', 377, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(377, '2026-02-05 00:00:00', 606, 'costo', 378, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(378, '2026-02-05 00:00:00', 606, 'costo', 379, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(379, '2026-02-05 00:00:00', 600, 'costo', 380, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(380, '2026-02-05 00:00:00', 606, 'costo', 381, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(381, '2026-02-05 00:00:00', 600, 'costo', 382, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(382, '2026-02-05 00:00:00', 1045, 'costo', 383, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(383, '2026-02-05 00:00:00', 652, 'costo', 384, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(384, '2026-02-05 00:00:00', 611, 'costo', 385, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(385, '2026-02-05 00:00:00', 606, 'costo', 386, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(386, '2026-02-05 00:00:00', 647, 'costo', 387, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(387, '2026-02-05 00:00:00', 639, 'costo', 388, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(388, '2026-02-05 00:00:00', 922, 'costo', 389, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(389, '2026-02-05 00:00:00', 653, 'costo', 390, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(390, '2026-02-05 00:00:00', 1088, 'costo', 391, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(391, '2026-02-05 00:00:00', 771, 'costo', 392, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(392, '2026-02-05 00:00:00', 655, 'costo', 393, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(393, '2026-02-05 00:00:00', 298, 'costo', 394, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(394, '2026-02-05 00:00:00', 736, 'costo', 395, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(395, '2026-02-05 00:00:00', 443, 'costo', 396, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(396, '2026-02-05 00:00:00', 407, 'costo', 397, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(397, '2026-02-05 00:00:00', 392, 'costo', 398, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(398, '2026-02-05 00:00:00', 392, 'costo', 399, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(399, '2026-02-05 00:00:00', 416, 'costo', 400, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(400, '2026-02-05 00:00:00', 392, 'costo', 401, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(401, '2026-02-05 00:00:00', 394, 'costo', 402, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(402, '2026-02-05 00:00:00', 438, 'costo', 403, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(403, '2026-02-05 00:00:00', 443, 'costo', 404, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(404, '2026-02-05 00:00:00', 443, 'costo', 405, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(405, '2026-02-05 00:00:00', 443, 'costo', 406, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(406, '2026-02-05 00:00:00', 440, 'costo', 407, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(407, '2026-02-05 00:00:00', 443, 'costo', 408, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(408, '2026-02-05 00:00:00', 440, 'costo', 409, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(409, '2026-02-05 00:00:00', 440, 'costo', 410, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(410, '2026-02-05 00:00:00', 443, 'costo', 411, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(411, '2026-02-05 00:00:00', 718, 'costo', 412, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(412, '2026-02-05 00:00:00', 758, 'costo', 413, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(413, '2026-02-05 00:00:00', 758, 'costo', 414, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(414, '2026-02-05 00:00:00', 758, 'costo', 415, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(415, '2026-02-05 00:00:00', 758, 'costo', 416, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(416, '2026-02-05 00:00:00', 745, 'costo', 417, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(417, '2026-02-05 00:00:00', 740, 'costo', 418, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(418, '2026-02-05 00:00:00', 798, 'costo', 419, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(419, '2026-02-05 00:00:00', 789, 'costo', 420, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(420, '2026-02-05 00:00:00', 783, 'costo', 421, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(421, '2026-02-05 00:00:00', 1058, 'costo', 422, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(422, '2026-02-05 00:00:00', 913, 'costo', 423, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL);
INSERT INTO `inv_values` (`id`, `date`, `values`, `type`, `itemId`, `warehouseId`, `label`, `created_at`, `updated_at`, `deleted_at`) VALUES
(423, '2026-02-05 00:00:00', 1863, 'costo', 424, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(424, '2026-02-05 00:00:00', 911, 'costo', 425, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(425, '2026-02-05 00:00:00', 1032, 'costo', 426, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(426, '2026-02-05 00:00:00', 1044, 'costo', 427, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(427, '2026-02-05 00:00:00', 1044, 'costo', 428, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(428, '2026-02-05 00:00:00', 1044, 'costo', 429, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(429, '2026-02-05 00:00:00', 0, 'costo', 430, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(430, '2026-02-05 00:00:00', 0, 'costo', 431, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(431, '2026-02-05 00:00:00', 653, 'costo', 432, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(432, '2026-02-05 00:00:00', 352, 'costo', 433, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(433, '2026-02-05 00:00:00', 371, 'costo', 434, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(434, '2026-02-05 00:00:00', 957, 'costo', 435, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(435, '2026-02-05 00:00:00', 899, 'costo', 436, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(436, '2026-02-05 00:00:00', 1675, 'costo', 437, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(437, '2026-02-05 00:00:00', 896, 'costo', 438, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(438, '2026-02-05 00:00:00', 1675, 'costo', 439, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(439, '2026-02-05 00:00:00', 906, 'costo', 440, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(440, '2026-02-05 00:00:00', 3303, 'costo', 441, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(441, '2026-02-05 00:00:00', 788, 'costo', 442, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(442, '2026-02-05 00:00:00', 791, 'costo', 443, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(443, '2026-02-05 00:00:00', 1039, 'costo', 444, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(444, '2026-02-05 00:00:00', 787, 'costo', 445, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(445, '2026-02-05 00:00:00', 961, 'costo', 446, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(446, '2026-02-05 00:00:00', 725, 'costo', 447, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(447, '2026-02-05 00:00:00', 0, 'costo', 448, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(448, '2026-02-05 00:00:00', 2691, 'costo', 449, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(449, '2026-02-05 00:00:00', 2901, 'costo', 450, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(450, '2026-02-05 00:00:00', 2878, 'costo', 451, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(451, '2026-02-05 00:00:00', 1384, 'costo', 452, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(452, '2026-02-05 00:00:00', 868, 'costo', 453, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(453, '2026-02-05 00:00:00', 942, 'costo', 454, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(454, '2026-02-05 00:00:00', 2615, 'costo', 455, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(455, '2026-02-05 00:00:00', 3568, 'costo', 456, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(456, '2026-02-05 00:00:00', 2767, 'costo', 457, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(457, '2026-02-05 00:00:00', 1570, 'costo', 458, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(458, '2026-02-05 00:00:00', 1093, 'costo', 459, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(459, '2026-02-05 00:00:00', 890, 'costo', 460, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(460, '2026-02-05 00:00:00', 1000, 'costo', 461, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(461, '2026-02-05 00:00:00', 1570, 'costo', 462, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(462, '2026-02-05 00:00:00', 687, 'costo', 463, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(463, '2026-02-05 00:00:00', 792, 'costo', 464, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(464, '2026-02-05 00:00:00', 560, 'costo', 465, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(465, '2026-02-05 00:00:00', 1670, 'costo', 466, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(466, '2026-02-05 00:00:00', 867, 'costo', 467, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(467, '2026-02-05 00:00:00', 2495, 'costo', 468, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(468, '2026-02-05 00:00:00', 3930, 'costo', 469, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(469, '2026-02-05 00:00:00', 3434, 'costo', 470, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(470, '2026-02-05 00:00:00', 3433, 'costo', 471, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(471, '2026-02-05 00:00:00', 3380, 'costo', 472, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(472, '2026-02-05 00:00:00', 3183, 'costo', 473, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(473, '2026-02-05 00:00:00', 2884, 'costo', 474, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(474, '2026-02-05 00:00:00', 645, 'costo', 475, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(475, '2026-02-05 00:00:00', 643, 'costo', 476, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(476, '2026-02-05 00:00:00', 496, 'costo', 477, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(477, '2026-02-05 00:00:00', 496, 'costo', 478, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(478, '2026-02-05 00:00:00', 787, 'costo', 479, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(479, '2026-02-05 00:00:00', 621, 'costo', 480, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(480, '2026-02-05 00:00:00', 787, 'costo', 481, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(481, '2026-02-05 00:00:00', 811, 'costo', 482, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(482, '2026-02-05 00:00:00', 814, 'costo', 483, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(483, '2026-02-05 00:00:00', 495, 'costo', 484, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(484, '2026-02-05 00:00:00', 2804, 'costo', 485, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(485, '2026-02-05 00:00:00', 2711, 'costo', 486, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(486, '2026-02-05 00:00:00', 0, 'costo', 487, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(487, '2026-02-05 00:00:00', 2826, 'costo', 488, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(488, '2026-02-05 00:00:00', 3767, 'costo', 489, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(489, '2026-02-05 00:00:00', 3506, 'costo', 490, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(490, '2026-02-05 00:00:00', 2616, 'costo', 491, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(491, '2026-02-05 00:00:00', 787, 'costo', 492, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(492, '2026-02-05 00:00:00', 897, 'costo', 493, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(493, '2026-02-05 00:00:00', 2775, 'costo', 494, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(494, '2026-02-05 00:00:00', 1227, 'costo', 495, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(495, '2026-02-05 00:00:00', 902, 'costo', 496, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(496, '2026-02-05 00:00:00', 2921, 'costo', 497, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(497, '2026-02-05 00:00:00', 904, 'costo', 498, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(498, '2026-02-05 00:00:00', 2969, 'costo', 499, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(499, '2026-02-05 00:00:00', 568, 'costo', 500, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(500, '2026-02-05 00:00:00', 9127, 'costo', 501, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(501, '2026-02-05 00:00:00', 7103, 'costo', 502, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(502, '2026-02-05 00:00:00', 10971, 'costo', 503, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(503, '2026-02-05 00:00:00', 8843, 'costo', 504, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(504, '2026-02-05 00:00:00', 34554, 'costo', 505, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(505, '2026-02-05 00:00:00', 8677, 'costo', 506, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(506, '2026-02-05 00:00:00', 7649, 'costo', 507, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(507, '2026-02-05 00:00:00', 7683, 'costo', 508, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(508, '2026-02-05 00:00:00', 7139, 'costo', 509, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(509, '2026-02-05 00:00:00', 7132, 'costo', 510, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(510, '2026-02-05 00:00:00', 3900, 'costo', 511, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(511, '2026-02-05 00:00:00', 7793, 'costo', 512, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(512, '2026-02-05 00:00:00', 7825, 'costo', 513, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(513, '2026-02-05 00:00:00', 7782, 'costo', 514, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(514, '2026-02-05 00:00:00', 8713, 'costo', 515, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(515, '2026-02-05 00:00:00', 8713, 'costo', 516, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(516, '2026-02-05 00:00:00', 8843, 'costo', 517, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(517, '2026-02-05 00:00:00', 35466, 'costo', 518, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(518, '2026-02-05 00:00:00', 32531, 'costo', 519, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(519, '2026-02-05 00:00:00', 24248, 'costo', 520, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(520, '2026-02-05 00:00:00', 16730, 'costo', 521, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(521, '2026-02-05 00:00:00', 16993, 'costo', 522, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(522, '2026-02-05 00:00:00', 15869, 'costo', 523, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(523, '2026-02-05 00:00:00', 11385, 'costo', 524, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(524, '2026-02-05 00:00:00', 16468, 'costo', 525, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(525, '2026-02-05 00:00:00', 9769, 'costo', 526, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(526, '2026-02-05 00:00:00', 8365, 'costo', 527, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(527, '2026-02-05 00:00:00', 13311, 'costo', 528, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(528, '2026-02-05 00:00:00', 8383, 'costo', 529, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(529, '2026-02-05 00:00:00', 2588, 'costo', 530, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(530, '2026-02-05 00:00:00', 2588, 'costo', 531, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(531, '2026-02-05 00:00:00', 2588, 'costo', 532, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(532, '2026-02-05 00:00:00', 10229, 'costo', 533, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(533, '2026-02-05 00:00:00', 17856, 'costo', 534, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(534, '2026-02-05 00:00:00', 21719, 'costo', 535, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(535, '2026-02-05 00:00:00', 28495, 'costo', 536, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(536, '2026-02-05 00:00:00', 18183, 'costo', 537, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(537, '2026-02-05 00:00:00', 19524, 'costo', 538, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(538, '2026-02-05 00:00:00', 16468, 'costo', 539, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(539, '2026-02-05 00:00:00', 18467, 'costo', 540, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(540, '2026-02-05 00:00:00', 15401, 'costo', 541, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(541, '2026-02-05 00:00:00', 15459, 'costo', 542, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(542, '2026-02-05 00:00:00', 15401, 'costo', 543, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(543, '2026-02-05 00:00:00', 31121, 'costo', 544, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(544, '2026-02-05 00:00:00', 40017, 'costo', 545, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(545, '2026-02-05 00:00:00', 29069, 'costo', 546, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(546, '2026-02-05 00:00:00', 11916, 'costo', 547, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(547, '2026-02-05 00:00:00', 9935, 'costo', 548, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(548, '2026-02-05 00:00:00', 10896, 'costo', 549, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(549, '2026-02-05 00:00:00', 11300, 'costo', 550, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(550, '2026-02-05 00:00:00', 10212, 'costo', 551, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(551, '2026-02-05 00:00:00', 21661, 'costo', 552, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(552, '2026-02-05 00:00:00', 212, 'costo', 553, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(553, '2026-02-05 00:00:00', 12533, 'costo', 554, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(554, '2026-02-05 00:00:00', 73508, 'costo', 555, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(555, '2026-02-05 00:00:00', 17258, 'costo', 556, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(556, '2026-02-05 00:00:00', 0, 'costo', 557, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(557, '2026-02-05 00:00:00', 126686, 'costo', 558, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(558, '2026-02-05 00:00:00', 183245, 'costo', 559, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(559, '2026-02-05 00:00:00', 299566, 'costo', 560, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(560, '2026-02-05 00:00:00', 432146, 'costo', 561, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(561, '2026-02-05 00:00:00', 25108, 'costo', 562, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(562, '2026-02-05 00:00:00', 9028, 'costo', 563, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(563, '2026-02-05 00:00:00', 11708, 'costo', 564, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(564, '2026-02-05 00:00:00', 10473, 'costo', 565, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(565, '2026-02-05 00:00:00', 24510, 'costo', 566, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(566, '2026-02-05 00:00:00', 73337, 'costo', 567, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(567, '2026-02-05 00:00:00', 99955, 'costo', 568, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(568, '2026-02-05 00:00:00', 83296, 'costo', 569, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(569, '2026-02-05 00:00:00', 3617, 'costo', 570, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(570, '2026-02-05 00:00:00', 13159, 'costo', 571, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(571, '2026-02-05 00:00:00', 18782, 'costo', 572, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(572, '2026-02-05 00:00:00', 14632, 'costo', 573, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(573, '2026-02-05 00:00:00', 9965, 'costo', 574, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(574, '2026-02-05 00:00:00', 15099, 'costo', 575, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(575, '2026-02-05 00:00:00', 10526, 'costo', 576, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(576, '2026-02-05 00:00:00', 10362, 'costo', 577, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(577, '2026-02-05 00:00:00', 22566, 'costo', 578, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(578, '2026-02-05 00:00:00', 234809, 'costo', 579, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(579, '2026-02-05 00:00:00', 58549, 'costo', 580, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(580, '2026-02-05 00:00:00', 483872, 'costo', 581, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(581, '2026-02-05 00:00:00', 16399, 'costo', 582, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(582, '2026-02-05 00:00:00', 13605, 'costo', 583, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(583, '2026-02-05 00:00:00', 20293, 'costo', 584, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(584, '2026-02-05 00:00:00', 30515, 'costo', 585, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(585, '2026-02-05 00:00:00', 16778, 'costo', 586, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(586, '2026-02-05 00:00:00', 65199, 'costo', 587, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(587, '2026-02-05 00:00:00', 65426, 'costo', 588, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(588, '2026-02-05 00:00:00', 109530, 'costo', 589, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(589, '2026-02-05 00:00:00', 137335, 'costo', 590, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(590, '2026-02-05 00:00:00', 200427, 'costo', 591, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(591, '2026-02-05 00:00:00', 0, 'costo', 592, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(592, '2026-02-05 00:00:00', 37024, 'costo', 593, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(593, '2026-02-05 00:00:00', 155076, 'costo', 594, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(594, '2026-02-05 00:00:00', 98318, 'costo', 595, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(595, '2026-02-05 00:00:00', 17840, 'costo', 596, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(596, '2026-02-05 00:00:00', 16062, 'costo', 597, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(597, '2026-02-05 00:00:00', 390306, 'costo', 598, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(598, '2026-02-05 00:00:00', 390301, 'costo', 599, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(599, '2026-02-05 00:00:00', 8112, 'costo', 600, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(600, '2026-02-05 00:00:00', 54388, 'costo', 601, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(601, '2026-02-05 00:00:00', 209592, 'costo', 602, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(602, '2026-02-05 00:00:00', 5384, 'costo', 603, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(603, '2026-02-05 00:00:00', 41539, 'costo', 604, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(604, '2026-02-05 00:00:00', 495497, 'costo', 605, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(605, '2026-02-05 00:00:00', 103212, 'costo', 606, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(606, '2026-02-05 00:00:00', 158782, 'costo', 607, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(607, '2026-02-05 00:00:00', 144077, 'costo', 608, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(608, '2026-02-05 00:00:00', 176317, 'costo', 609, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(609, '2026-02-05 00:00:00', 176316, 'costo', 610, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(610, '2026-02-05 00:00:00', 176316, 'costo', 611, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(611, '2026-02-05 00:00:00', 21034, 'costo', 612, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(612, '2026-02-05 00:00:00', 21034, 'costo', 613, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(613, '2026-02-05 00:00:00', 21034, 'costo', 614, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(614, '2026-02-05 00:00:00', 21034, 'costo', 615, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(615, '2026-02-05 00:00:00', 21034, 'costo', 616, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(616, '2026-02-05 00:00:00', 242622, 'costo', 617, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(617, '2026-02-05 00:00:00', 16094, 'costo', 618, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(618, '2026-02-05 00:00:00', 24758, 'costo', 619, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(619, '2026-02-05 00:00:00', 59617, 'costo', 620, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(620, '2026-02-05 00:00:00', 92420, 'costo', 621, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(621, '2026-02-05 00:00:00', 21252, 'costo', 622, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(622, '2026-02-05 00:00:00', 18112, 'costo', 623, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(623, '2026-02-05 00:00:00', 26071, 'costo', 624, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(624, '2026-02-05 00:00:00', 26047, 'costo', 625, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(625, '2026-02-05 00:00:00', 61936, 'costo', 626, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(626, '2026-02-05 00:00:00', 67992, 'costo', 627, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(627, '2026-02-05 00:00:00', 34394, 'costo', 628, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(628, '2026-02-05 00:00:00', 42821, 'costo', 629, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(629, '2026-02-05 00:00:00', 42821, 'costo', 630, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(630, '2026-02-05 00:00:00', 42821, 'costo', 631, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(631, '2026-02-05 00:00:00', 9395, 'costo', 632, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(632, '2026-02-05 00:00:00', 35432, 'costo', 633, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(633, '2026-02-05 00:00:00', 18664, 'costo', 634, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(634, '2026-02-05 00:00:00', 36564, 'costo', 635, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(635, '2026-02-05 00:00:00', 35704, 'costo', 636, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(636, '2026-02-05 00:00:00', 20683, 'costo', 637, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(637, '2026-02-05 00:00:00', 18390, 'costo', 638, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(638, '2026-02-05 00:00:00', 26328, 'costo', 639, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(639, '2026-02-05 00:00:00', 26361, 'costo', 640, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(640, '2026-02-05 00:00:00', 29556, 'costo', 641, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(641, '2026-02-05 00:00:00', 67645, 'costo', 642, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(642, '2026-02-05 00:00:00', 32841, 'costo', 643, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(643, '2026-02-05 00:00:00', 32841, 'costo', 644, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(644, '2026-02-05 00:00:00', 40694, 'costo', 645, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(645, '2026-02-05 00:00:00', 38656, 'costo', 646, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(646, '2026-02-05 00:00:00', 37658, 'costo', 647, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(647, '2026-02-05 00:00:00', 36827, 'costo', 648, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(648, '2026-02-05 00:00:00', 35119, 'costo', 649, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(649, '2026-02-05 00:00:00', 52307, 'costo', 650, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(650, '2026-02-05 00:00:00', 56519, 'costo', 651, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(651, '2026-02-05 00:00:00', 46575, 'costo', 652, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(652, '2026-02-05 00:00:00', 57918, 'costo', 653, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(653, '2026-02-05 00:00:00', 21194, 'costo', 654, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(654, '2026-02-05 00:00:00', 25738, 'costo', 655, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(655, '2026-02-05 00:00:00', 16145, 'costo', 656, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(656, '2026-02-05 00:00:00', 18585, 'costo', 657, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(657, '2026-02-05 00:00:00', 19425, 'costo', 658, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(658, '2026-02-05 00:00:00', 29428, 'costo', 659, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(659, '2026-02-05 00:00:00', 23693, 'costo', 660, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(660, '2026-02-05 00:00:00', 19755, 'costo', 661, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(661, '2026-02-05 00:00:00', 43010, 'costo', 662, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(662, '2026-02-05 00:00:00', 41005, 'costo', 663, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(663, '2026-02-05 00:00:00', 41348, 'costo', 664, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(664, '2026-02-05 00:00:00', 29957, 'costo', 665, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(665, '2026-02-05 00:00:00', 48302, 'costo', 666, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(666, '2026-02-05 00:00:00', 48362, 'costo', 667, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(667, '2026-02-05 00:00:00', 45208, 'costo', 668, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(668, '2026-02-05 00:00:00', 52157, 'costo', 669, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(669, '2026-02-05 00:00:00', 30742, 'costo', 670, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(670, '2026-02-05 00:00:00', 35845, 'costo', 671, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(671, '2026-02-05 00:00:00', 49698, 'costo', 672, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(672, '2026-02-05 00:00:00', 72888, 'costo', 673, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(673, '2026-02-05 00:00:00', 30742, 'costo', 674, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(674, '2026-02-05 00:00:00', 30742, 'costo', 675, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(675, '2026-02-05 00:00:00', 29068, 'costo', 676, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(676, '2026-02-05 00:00:00', 31666, 'costo', 677, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(677, '2026-02-05 00:00:00', 30214, 'costo', 678, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(678, '2026-02-05 00:00:00', 33766, 'costo', 679, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(679, '2026-02-05 00:00:00', 33056, 'costo', 680, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(680, '2026-02-05 00:00:00', 45208, 'costo', 681, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(681, '2026-02-05 00:00:00', 39311, 'costo', 682, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(682, '2026-02-05 00:00:00', 42504, 'costo', 683, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(683, '2026-02-05 00:00:00', 42516, 'costo', 684, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(684, '2026-02-05 00:00:00', 45296, 'costo', 685, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(685, '2026-02-05 00:00:00', 47606, 'costo', 686, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(686, '2026-02-05 00:00:00', 48302, 'costo', 687, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(687, '2026-02-05 00:00:00', 24505, 'costo', 688, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(688, '2026-02-05 00:00:00', 27960, 'costo', 689, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(689, '2026-02-05 00:00:00', 25172, 'costo', 690, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(690, '2026-02-05 00:00:00', 47067, 'costo', 691, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(691, '2026-02-05 00:00:00', 47067, 'costo', 692, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(692, '2026-02-05 00:00:00', 50127, 'costo', 693, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(693, '2026-02-05 00:00:00', 0, 'costo', 694, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(694, '2026-02-05 00:00:00', 104973, 'costo', 695, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(695, '2026-02-05 00:00:00', 103155, 'costo', 696, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(696, '2026-02-05 00:00:00', 95483, 'costo', 697, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(697, '2026-02-05 00:00:00', 29051, 'costo', 698, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(698, '2026-02-05 00:00:00', 25945, 'costo', 699, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(699, '2026-02-05 00:00:00', 69977, 'costo', 700, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(700, '2026-02-05 00:00:00', 152171, 'costo', 701, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(701, '2026-02-05 00:00:00', 152192, 'costo', 702, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(702, '2026-02-05 00:00:00', 49662, 'costo', 703, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(703, '2026-02-05 00:00:00', 25006, 'costo', 704, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(704, '2026-02-05 00:00:00', 58437, 'costo', 705, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(705, '2026-02-05 00:00:00', 28862, 'costo', 706, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(706, '2026-02-05 00:00:00', 24412, 'costo', 707, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(707, '2026-02-05 00:00:00', 26238, 'costo', 708, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(708, '2026-02-05 00:00:00', 26906, 'costo', 709, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(709, '2026-02-05 00:00:00', 31494, 'costo', 710, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(710, '2026-02-05 00:00:00', 31494, 'costo', 711, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(711, '2026-02-05 00:00:00', 31494, 'costo', 712, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(712, '2026-02-05 00:00:00', 41249, 'costo', 713, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(713, '2026-02-05 00:00:00', 51684, 'costo', 714, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(714, '2026-02-05 00:00:00', 49211, 'costo', 715, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(715, '2026-02-05 00:00:00', 22686, 'costo', 716, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(716, '2026-02-05 00:00:00', 49559, 'costo', 717, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(717, '2026-02-05 00:00:00', 43524, 'costo', 718, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(718, '2026-02-05 00:00:00', 56127, 'costo', 719, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(719, '2026-02-05 00:00:00', 47524, 'costo', 720, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(720, '2026-02-05 00:00:00', 37786, 'costo', 721, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(721, '2026-02-05 00:00:00', 88723, 'costo', 722, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(722, '2026-02-05 00:00:00', 20769, 'costo', 723, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(723, '2026-02-05 00:00:00', 37658, 'costo', 724, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(724, '2026-02-05 00:00:00', 38237, 'costo', 725, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(725, '2026-02-05 00:00:00', 79254, 'costo', 726, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(726, '2026-02-05 00:00:00', 38548, 'costo', 727, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(727, '2026-02-05 00:00:00', 64682, 'costo', 728, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(728, '2026-02-05 00:00:00', 66523, 'costo', 729, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(729, '2026-02-05 00:00:00', 76118, 'costo', 730, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(730, '2026-02-05 00:00:00', 8133, 'costo', 731, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(731, '2026-02-05 00:00:00', 8133, 'costo', 732, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(732, '2026-02-05 00:00:00', 8133, 'costo', 733, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(733, '2026-02-05 00:00:00', 8133, 'costo', 734, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(734, '2026-02-05 00:00:00', 8133, 'costo', 735, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(735, '2026-02-05 00:00:00', 8133, 'costo', 736, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(736, '2026-02-05 00:00:00', 12520, 'costo', 737, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(737, '2026-02-05 00:00:00', 12520, 'costo', 738, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(738, '2026-02-05 00:00:00', 8133, 'costo', 739, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(739, '2026-02-05 00:00:00', 134, 'costo', 740, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(740, '2026-02-05 00:00:00', 525, 'costo', 741, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(741, '2026-02-05 00:00:00', 3352, 'costo', 742, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(742, '2026-02-05 00:00:00', 3442, 'costo', 743, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(743, '2026-02-05 00:00:00', 8675, 'costo', 744, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(744, '2026-02-05 00:00:00', 3455, 'costo', 745, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(745, '2026-02-05 00:00:00', 2927, 'costo', 746, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(746, '2026-02-05 00:00:00', 2794, 'costo', 747, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(747, '2026-02-05 00:00:00', 3149, 'costo', 748, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(748, '2026-02-05 00:00:00', 3179, 'costo', 749, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(749, '2026-02-05 00:00:00', 2864, 'costo', 750, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(750, '2026-02-05 00:00:00', 1069, 'costo', 751, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(751, '2026-02-05 00:00:00', 1069, 'costo', 752, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(752, '2026-02-05 00:00:00', 1069, 'costo', 753, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(753, '2026-02-05 00:00:00', 1069, 'costo', 754, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(754, '2026-02-05 00:00:00', 1069, 'costo', 755, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(755, '2026-02-05 00:00:00', 1069, 'costo', 756, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(756, '2026-02-05 00:00:00', 1069, 'costo', 757, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(757, '2026-02-05 00:00:00', 1069, 'costo', 758, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(758, '2026-02-05 00:00:00', 61441, 'costo', 759, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(759, '2026-02-05 00:00:00', 4421, 'costo', 760, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(760, '2026-02-05 00:00:00', 59182, 'costo', 761, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(761, '2026-02-05 00:00:00', 0, 'costo', 762, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(762, '2026-02-05 00:00:00', 4245, 'costo', 763, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(763, '2026-02-05 00:00:00', 31447, 'costo', 764, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(764, '2026-02-05 00:00:00', 32432, 'costo', 765, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(765, '2026-02-05 00:00:00', 31433, 'costo', 766, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(766, '2026-02-05 00:00:00', 31997, 'costo', 767, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(767, '2026-02-05 00:00:00', 29742, 'costo', 768, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(768, '2026-02-05 00:00:00', 31866, 'costo', 769, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(769, '2026-02-05 00:00:00', 31723, 'costo', 770, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(770, '2026-02-05 00:00:00', 31898, 'costo', 771, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(771, '2026-02-05 00:00:00', 31371, 'costo', 772, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(772, '2026-02-05 00:00:00', 0, 'costo', 773, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(773, '2026-02-05 00:00:00', 0, 'costo', 774, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(774, '2026-02-05 00:00:00', 0, 'costo', 775, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(775, '2026-02-05 00:00:00', 18482, 'costo', 776, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(776, '2026-02-05 00:00:00', 40852, 'costo', 777, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(777, '2026-02-05 00:00:00', 90941, 'costo', 778, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(778, '2026-02-05 00:00:00', 4694, 'costo', 779, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(779, '2026-02-05 00:00:00', 39238, 'costo', 780, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(780, '2026-02-05 00:00:00', 31081, 'costo', 781, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(781, '2026-02-05 00:00:00', 28529, 'costo', 782, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(782, '2026-02-05 00:00:00', 29869, 'costo', 783, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(783, '2026-02-05 00:00:00', 10501, 'costo', 784, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(784, '2026-02-05 00:00:00', 61039, 'costo', 785, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(785, '2026-02-05 00:00:00', 75368, 'costo', 786, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(786, '2026-02-05 00:00:00', 132443, 'costo', 787, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(787, '2026-02-05 00:00:00', 85873, 'costo', 788, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(788, '2026-02-05 00:00:00', 23099, 'costo', 789, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(789, '2026-02-05 00:00:00', 3198, 'costo', 790, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(790, '2026-02-05 00:00:00', 4322, 'costo', 791, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(791, '2026-02-05 00:00:00', 2290, 'costo', 792, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(792, '2026-02-05 00:00:00', 45422, 'costo', 793, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(793, '2026-02-05 00:00:00', 60862, 'costo', 794, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(794, '2026-02-05 00:00:00', 30471, 'costo', 795, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(795, '2026-02-05 00:00:00', 64551, 'costo', 796, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(796, '2026-02-05 00:00:00', 55302, 'costo', 797, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(797, '2026-02-05 00:00:00', 0, 'costo', 798, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(798, '2026-02-05 00:00:00', 39164, 'costo', 799, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(799, '2026-02-05 00:00:00', 32997, 'costo', 800, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(800, '2026-02-05 00:00:00', 43973, 'costo', 801, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(801, '2026-02-05 00:00:00', 46281, 'costo', 802, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(802, '2026-02-05 00:00:00', 35283, 'costo', 803, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(803, '2026-02-05 00:00:00', 55234, 'costo', 804, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(804, '2026-02-05 00:00:00', 33080, 'costo', 805, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(805, '2026-02-05 00:00:00', 25368, 'costo', 806, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(806, '2026-02-05 00:00:00', 22832, 'costo', 807, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(807, '2026-02-05 00:00:00', 15230, 'costo', 808, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(808, '2026-02-05 00:00:00', 15230, 'costo', 809, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(809, '2026-02-05 00:00:00', 15973, 'costo', 810, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(810, '2026-02-05 00:00:00', 23871, 'costo', 811, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(811, '2026-02-05 00:00:00', 51915, 'costo', 812, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(812, '2026-02-05 00:00:00', 25260, 'costo', 813, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(813, '2026-02-05 00:00:00', 25260, 'costo', 814, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(814, '2026-02-05 00:00:00', 25260, 'costo', 815, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(815, '2026-02-05 00:00:00', 25260, 'costo', 816, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(816, '2026-02-05 00:00:00', 29718, 'costo', 817, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(817, '2026-02-05 00:00:00', 34373, 'costo', 818, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(818, '2026-02-05 00:00:00', 34591, 'costo', 819, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(819, '2026-02-05 00:00:00', 45843, 'costo', 820, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(820, '2026-02-05 00:00:00', 29385, 'costo', 821, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(821, '2026-02-05 00:00:00', 18573, 'costo', 822, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(822, '2026-02-05 00:00:00', 44141, 'costo', 823, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(823, '2026-02-05 00:00:00', 54820, 'costo', 824, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(824, '2026-02-05 00:00:00', 92058, 'costo', 825, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(825, '2026-02-05 00:00:00', 32132, 'costo', 826, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(826, '2026-02-05 00:00:00', 33211, 'costo', 827, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(827, '2026-02-05 00:00:00', 29222, 'costo', 828, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(828, '2026-02-05 00:00:00', 9286, 'costo', 829, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(829, '2026-02-05 00:00:00', 34622, 'costo', 830, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(830, '2026-02-05 00:00:00', 49729, 'costo', 831, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(831, '2026-02-05 00:00:00', 39465, 'costo', 832, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(832, '2026-02-05 00:00:00', 22832, 'costo', 833, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(833, '2026-02-05 00:00:00', 5424, 'costo', 834, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(834, '2026-02-05 00:00:00', 11578, 'costo', 835, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(835, '2026-02-05 00:00:00', 12180, 'costo', 836, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL);
INSERT INTO `inv_values` (`id`, `date`, `values`, `type`, `itemId`, `warehouseId`, `label`, `created_at`, `updated_at`, `deleted_at`) VALUES
(836, '2026-02-05 00:00:00', 37151, 'costo', 837, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(837, '2026-02-05 00:00:00', 36399, 'costo', 838, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(838, '2026-02-05 00:00:00', 8892, 'costo', 839, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(839, '2026-02-05 00:00:00', 11041, 'costo', 840, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(840, '2026-02-05 00:00:00', 22860, 'costo', 841, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(841, '2026-02-05 00:00:00', 22860, 'costo', 842, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(842, '2026-02-05 00:00:00', 35217, 'costo', 843, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(843, '2026-02-05 00:00:00', 35217, 'costo', 844, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(844, '2026-02-05 00:00:00', 17220, 'costo', 845, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(845, '2026-02-05 00:00:00', 18238, 'costo', 846, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(846, '2026-02-05 00:00:00', 12790, 'costo', 847, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(847, '2026-02-05 00:00:00', 44434, 'costo', 848, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(848, '2026-02-05 00:00:00', 37521, 'costo', 849, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(849, '2026-02-05 00:00:00', 34511, 'costo', 850, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(850, '2026-02-05 00:00:00', 41591, 'costo', 851, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(851, '2026-02-05 00:00:00', 25389, 'costo', 852, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(852, '2026-02-05 00:00:00', 8978, 'costo', 853, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(853, '2026-02-05 00:00:00', 5547, 'costo', 854, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(854, '2026-02-05 00:00:00', 9689, 'costo', 855, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(855, '2026-02-05 00:00:00', 9568, 'costo', 856, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(856, '2026-02-05 00:00:00', 143166, 'costo', 857, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(857, '2026-02-05 00:00:00', 9017, 'costo', 858, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(858, '2026-02-05 00:00:00', 39679, 'costo', 859, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(859, '2026-02-05 00:00:00', 26452, 'costo', 860, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(860, '2026-02-05 00:00:00', 39679, 'costo', 861, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(861, '2026-02-05 00:00:00', 62002, 'costo', 862, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(862, '2026-02-05 00:00:00', 12060, 'costo', 863, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(863, '2026-02-05 00:00:00', 57641, 'costo', 864, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(864, '2026-02-05 00:00:00', 44252, 'costo', 865, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(865, '2026-02-05 00:00:00', 44252, 'costo', 866, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(866, '2026-02-05 00:00:00', 40267, 'costo', 867, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(867, '2026-02-05 00:00:00', 93003, 'costo', 868, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(868, '2026-02-05 00:00:00', 7709, 'costo', 869, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(869, '2026-02-05 00:00:00', 6781, 'costo', 870, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(870, '2026-02-05 00:00:00', 9633, 'costo', 871, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(871, '2026-02-05 00:00:00', 19159, 'costo', 872, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(872, '2026-02-05 00:00:00', 7597, 'costo', 873, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(873, '2026-02-05 00:00:00', 8031, 'costo', 874, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(874, '2026-02-05 00:00:00', 7449, 'costo', 875, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(875, '2026-02-05 00:00:00', 8286, 'costo', 876, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(876, '2026-02-05 00:00:00', 82079, 'costo', 877, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(877, '2026-02-05 00:00:00', 61358, 'costo', 878, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(878, '2026-02-05 00:00:00', 23827, 'costo', 879, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(879, '2026-02-05 00:00:00', 24540, 'costo', 880, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(880, '2026-02-05 00:00:00', 47938, 'costo', 881, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(881, '2026-02-05 00:00:00', 54416, 'costo', 882, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(882, '2026-02-05 00:00:00', 67019, 'costo', 883, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(883, '2026-02-05 00:00:00', 9356, 'costo', 884, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(884, '2026-02-05 00:00:00', 13186, 'costo', 885, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(885, '2026-02-05 00:00:00', 24492, 'costo', 886, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(886, '2026-02-05 00:00:00', 29147, 'costo', 887, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(887, '2026-02-05 00:00:00', 30340, 'costo', 888, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(888, '2026-02-05 00:00:00', 6927, 'costo', 889, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(889, '2026-02-05 00:00:00', 3730, 'costo', 890, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(890, '2026-02-05 00:00:00', 3730, 'costo', 891, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(891, '2026-02-05 00:00:00', 6999, 'costo', 892, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(892, '2026-02-05 00:00:00', 260993, 'costo', 893, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(893, '2026-02-05 00:00:00', 6896, 'costo', 894, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(894, '2026-02-05 00:00:00', 12059, 'costo', 895, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(895, '2026-02-05 00:00:00', 11598, 'costo', 896, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(896, '2026-02-05 00:00:00', 4500, 'costo', 897, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(897, '2026-02-05 00:00:00', 12773, 'costo', 898, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(898, '2026-02-05 00:00:00', 130169, 'costo', 899, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(899, '2026-02-05 00:00:00', 143927, 'costo', 900, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(900, '2026-02-05 00:00:00', 168139, 'costo', 901, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(901, '2026-02-05 00:00:00', 195373, 'costo', 902, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(902, '2026-02-05 00:00:00', 116079, 'costo', 903, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(903, '2026-02-05 00:00:00', 135717, 'costo', 904, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(904, '2026-02-05 00:00:00', 162183, 'costo', 905, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(905, '2026-02-05 00:00:00', 174526, 'costo', 906, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(906, '2026-02-05 00:00:00', 163126, 'costo', 907, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(907, '2026-02-05 00:00:00', 18249, 'costo', 908, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(908, '2026-02-05 00:00:00', 26240, 'costo', 909, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(909, '2026-02-05 00:00:00', 32073, 'costo', 910, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(910, '2026-02-05 00:00:00', 0, 'costo', 911, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(911, '2026-02-05 00:00:00', 6329, 'costo', 912, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(912, '2026-02-05 00:00:00', 12132, 'costo', 913, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(913, '2026-02-05 00:00:00', 13186, 'costo', 914, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(914, '2026-02-05 00:00:00', 9312, 'costo', 915, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(915, '2026-02-05 00:00:00', 23343, 'costo', 916, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(916, '2026-02-05 00:00:00', 74532, 'costo', 917, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(917, '2026-02-05 00:00:00', 49261, 'costo', 918, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(918, '2026-02-05 00:00:00', 66007, 'costo', 919, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(919, '2026-02-05 00:00:00', 89303, 'costo', 920, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(920, '2026-02-05 00:00:00', 128355, 'costo', 921, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(921, '2026-02-05 00:00:00', 151263, 'costo', 922, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(922, '2026-02-05 00:00:00', 42678, 'costo', 923, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(923, '2026-02-05 00:00:00', 52806, 'costo', 924, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(924, '2026-02-05 00:00:00', 75286, 'costo', 925, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(925, '2026-02-05 00:00:00', 10000, 'costo', 926, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(926, '2026-02-05 00:00:00', 0, 'costo', 927, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(927, '2026-02-05 00:00:00', 103856, 'costo', 928, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(928, '2026-02-05 00:00:00', 4657, 'costo', 929, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(929, '2026-02-05 00:00:00', 8655, 'costo', 930, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(930, '2026-02-05 00:00:00', 9060, 'costo', 931, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(931, '2026-02-05 00:00:00', 5206, 'costo', 932, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(932, '2026-02-05 00:00:00', 7945, 'costo', 933, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(933, '2026-02-05 00:00:00', 2069, 'costo', 934, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(934, '2026-02-05 00:00:00', 10850, 'costo', 935, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(935, '2026-02-05 00:00:00', 10517, 'costo', 936, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(936, '2026-02-05 00:00:00', 120191, 'costo', 937, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(937, '2026-02-05 00:00:00', 0, 'costo', 938, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(938, '2026-02-05 00:00:00', 24535, 'costo', 939, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(939, '2026-02-05 00:00:00', 39727, 'costo', 940, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(940, '2026-02-05 00:00:00', 47187, 'costo', 941, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(941, '2026-02-05 00:00:00', 31322, 'costo', 942, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(942, '2026-02-05 00:00:00', 68150, 'costo', 943, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(943, '2026-02-05 00:00:00', 56873, 'costo', 944, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(944, '2026-02-05 00:00:00', 18695, 'costo', 945, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(945, '2026-02-05 00:00:00', 21943, 'costo', 946, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(946, '2026-02-05 00:00:00', 25015, 'costo', 947, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(947, '2026-02-05 00:00:00', 29769, 'costo', 948, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(948, '2026-02-05 00:00:00', 35057, 'costo', 949, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(949, '2026-02-05 00:00:00', 17835, 'costo', 950, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(950, '2026-02-05 00:00:00', 20880, 'costo', 951, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(951, '2026-02-05 00:00:00', 25015, 'costo', 952, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(952, '2026-02-05 00:00:00', 32036, 'costo', 953, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(953, '2026-02-05 00:00:00', 50469, 'costo', 954, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(954, '2026-02-05 00:00:00', 65160, 'costo', 955, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(955, '2026-02-05 00:00:00', 65160, 'costo', 956, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(956, '2026-02-05 00:00:00', 19328, 'costo', 957, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(957, '2026-02-05 00:00:00', 30456, 'costo', 958, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(958, '2026-02-05 00:00:00', 29741, 'costo', 959, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(959, '2026-02-05 00:00:00', 53821, 'costo', 960, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(960, '2026-02-05 00:00:00', 26925, 'costo', 961, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(961, '2026-02-05 00:00:00', 18528, 'costo', 962, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(962, '2026-02-05 00:00:00', 38744, 'costo', 963, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(963, '2026-02-05 00:00:00', 66648, 'costo', 964, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(964, '2026-02-05 00:00:00', 66648, 'costo', 965, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(965, '2026-02-05 00:00:00', 93307, 'costo', 966, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(966, '2026-02-05 00:00:00', 93307, 'costo', 967, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(967, '2026-02-05 00:00:00', 253885, 'costo', 968, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(968, '2026-02-05 00:00:00', 253885, 'costo', 969, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(969, '2026-02-05 00:00:00', 342125, 'costo', 970, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(970, '2026-02-05 00:00:00', 342125, 'costo', 971, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(971, '2026-02-05 00:00:00', 578062, 'costo', 972, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(972, '2026-02-05 00:00:00', 11774, 'costo', 973, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(973, '2026-02-05 00:00:00', 19105, 'costo', 974, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(974, '2026-02-05 00:00:00', 19105, 'costo', 975, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(975, '2026-02-05 00:00:00', 23549, 'costo', 976, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(976, '2026-02-05 00:00:00', 23549, 'costo', 977, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(977, '2026-02-05 00:00:00', 38211, 'costo', 978, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(978, '2026-02-05 00:00:00', 38211, 'costo', 979, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(979, '2026-02-05 00:00:00', 24430, 'costo', 980, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(980, '2026-02-05 00:00:00', 34491, 'costo', 981, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(981, '2026-02-05 00:00:00', 66817, 'costo', 982, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(982, '2026-02-05 00:00:00', 56728, 'costo', 983, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(983, '2026-02-05 00:00:00', 22500, 'costo', 984, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(984, '2026-02-05 00:00:00', 32709, 'costo', 985, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(985, '2026-02-05 00:00:00', 30633, 'costo', 986, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(986, '2026-02-05 00:00:00', 31755, 'costo', 987, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(987, '2026-02-05 00:00:00', 20187, 'costo', 988, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(988, '2026-02-05 00:00:00', 24655, 'costo', 989, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(989, '2026-02-05 00:00:00', 85389, 'costo', 990, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(990, '2026-02-05 00:00:00', 71331, 'costo', 991, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(991, '2026-02-05 00:00:00', 50590, 'costo', 992, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(992, '2026-02-05 00:00:00', 69902, 'costo', 993, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(993, '2026-02-05 00:00:00', 80089, 'costo', 994, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(994, '2026-02-05 00:00:00', 34467, 'costo', 995, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(995, '2026-02-05 00:00:00', 58302, 'costo', 996, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(996, '2026-02-05 00:00:00', 64762, 'costo', 997, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(997, '2026-02-05 00:00:00', 70360, 'costo', 998, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(998, '2026-02-05 00:00:00', 26407, 'costo', 999, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(999, '2026-02-05 00:00:00', 53990, 'costo', 1000, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1000, '2026-02-05 00:00:00', 23869, 'costo', 1001, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1001, '2026-02-05 00:00:00', 25591, 'costo', 1002, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1002, '2026-02-05 00:00:00', 39937, 'costo', 1003, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1003, '2026-02-05 00:00:00', 30142, 'costo', 1004, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1004, '2026-02-05 00:00:00', 22825, 'costo', 1005, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1005, '2026-02-05 00:00:00', 29691, 'costo', 1006, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1006, '2026-02-05 00:00:00', 42243, 'costo', 1007, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1007, '2026-02-05 00:00:00', 51892, 'costo', 1008, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1008, '2026-02-05 00:00:00', 24028, 'costo', 1009, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1009, '2026-02-05 00:00:00', 138000, 'costo', 1010, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1010, '2026-02-05 00:00:00', 25985, 'costo', 1011, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1011, '2026-02-05 00:00:00', 71508, 'costo', 1012, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1012, '2026-02-05 00:00:00', 49906, 'costo', 1013, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1013, '2026-02-05 00:00:00', 91432, 'costo', 1014, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1014, '2026-02-05 00:00:00', 33304, 'costo', 1015, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1015, '2026-02-05 00:00:00', 32267, 'costo', 1016, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1016, '2026-02-05 00:00:00', 64762, 'costo', 1017, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1017, '2026-02-05 00:00:00', 29691, 'costo', 1018, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1018, '2026-02-05 00:00:00', 67493, 'costo', 1019, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1019, '2026-02-05 00:00:00', 44834, 'costo', 1020, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1020, '2026-02-05 00:00:00', 45544, 'costo', 1021, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1021, '2026-02-05 00:00:00', 30116, 'costo', 1022, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1022, '2026-02-05 00:00:00', 14161, 'costo', 1023, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1023, '2026-02-05 00:00:00', 33185, 'costo', 1024, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1024, '2026-02-05 00:00:00', 64509, 'costo', 1025, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1025, '2026-02-05 00:00:00', 62869, 'costo', 1026, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1026, '2026-02-05 00:00:00', 98900, 'costo', 1027, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1027, '2026-02-05 00:00:00', 115900, 'costo', 1028, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1028, '2026-02-05 00:00:00', 69710, 'costo', 1029, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1029, '2026-02-05 00:00:00', 85073, 'costo', 1030, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1030, '2026-02-05 00:00:00', 143048, 'costo', 1031, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1031, '2026-02-05 00:00:00', 557408, 'costo', 1032, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1032, '2026-02-05 00:00:00', 29323, 'costo', 1033, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1033, '2026-02-05 00:00:00', 41826, 'costo', 1034, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1034, '2026-02-05 00:00:00', 87389, 'costo', 1035, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1035, '2026-02-05 00:00:00', 46567, 'costo', 1036, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1059, '2026-02-05 00:00:00', 151408, 'costo', 1060, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1060, '2026-02-05 00:00:00', 119561, 'costo', 1061, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1061, '2026-02-05 00:00:00', 111344, 'costo', 1062, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1062, '2026-02-05 00:00:00', 131846, 'costo', 1063, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1063, '2026-02-05 00:00:00', 685404, 'costo', 1064, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1064, '2026-02-05 00:00:00', 502322, 'costo', 1065, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1065, '2026-02-05 00:00:00', 634962, 'costo', 1066, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1066, '2026-02-05 00:00:00', 55339, 'costo', 1067, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1067, '2026-02-05 00:00:00', 55080, 'costo', 1068, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1068, '2026-02-05 00:00:00', 81638, 'costo', 1069, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1069, '2026-02-05 00:00:00', 134498, 'costo', 1070, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1070, '2026-02-05 00:00:00', 21758, 'costo', 1071, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1071, '2026-02-05 00:00:00', 22380, 'costo', 1072, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1072, '2026-02-05 00:00:00', 28141, 'costo', 1073, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1073, '2026-02-05 00:00:00', 93146, 'costo', 1074, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1074, '2026-02-05 00:00:00', 102332, 'costo', 1075, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1075, '2026-02-05 00:00:00', 105673, 'costo', 1076, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1076, '2026-02-05 00:00:00', 56806, 'costo', 1077, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1077, '2026-02-05 00:00:00', 47615, 'costo', 1078, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1078, '2026-02-05 00:00:00', 1040063, 'costo', 1079, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1079, '2026-02-05 00:00:00', 116031, 'costo', 1080, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1080, '2026-02-05 00:00:00', 38554, 'costo', 1081, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1081, '2026-02-05 00:00:00', 76316, 'costo', 1082, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1082, '2026-02-05 00:00:00', 171445, 'costo', 1083, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1084, '2026-02-05 00:00:00', 1831059, 'costo', 1085, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1085, '2026-02-05 00:00:00', 189875, 'costo', 1086, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1086, '2026-02-05 00:00:00', 189875, 'costo', 1087, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1087, '2026-02-05 00:00:00', 15102, 'costo', 1088, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1088, '2026-02-05 00:00:00', 17626, 'costo', 1089, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1089, '2026-02-05 00:00:00', 21152, 'costo', 1090, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1090, '2026-02-05 00:00:00', 39864, 'costo', 1091, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1091, '2026-02-05 00:00:00', 21152, 'costo', 1092, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1092, '2026-02-05 00:00:00', 64601, 'costo', 1093, 1, 'Costo Inicial', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1955, '2026-02-05 00:00:00', 101680, 'precio', 3, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1956, '2026-02-05 00:00:00', 115966, 'precio', 4, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1957, '2026-02-05 00:00:00', 133613, 'precio', 5, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1958, '2026-02-05 00:00:00', 172268, 'precio', 6, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1961, '2026-02-05 00:00:00', 50420, 'precio', 9, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1962, '2026-02-05 00:00:00', 68067, 'precio', 10, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1963, '2026-02-05 00:00:00', 142857, 'precio', 11, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1964, '2026-02-05 00:00:00', 142857, 'precio', 12, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1965, '2026-02-05 00:00:00', 126050, 'precio', 13, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1966, '2026-02-05 00:00:00', 280336, 'precio', 14, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1967, '2026-02-05 00:00:00', 456890, 'precio', 15, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1968, '2026-02-05 00:00:00', 499159, 'precio', 16, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1969, '2026-02-05 00:00:00', 1035126, 'precio', 17, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1970, '2026-02-05 00:00:00', 156302, 'precio', 18, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1971, '2026-02-05 00:00:00', 403361, 'precio', 19, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1972, '2026-02-05 00:00:00', 418487, 'precio', 20, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1973, '2026-02-05 00:00:00', 131092, 'precio', 21, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1974, '2026-02-05 00:00:00', 183193, 'precio', 22, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1975, '2026-02-05 00:00:00', 257142, 'precio', 23, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1976, '2026-02-05 00:00:00', 304201, 'precio', 24, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1977, '2026-02-05 00:00:00', 365546, 'precio', 25, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1978, '2026-02-05 00:00:00', 412605, 'precio', 26, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1979, '2026-02-05 00:00:00', 460504, 'precio', 27, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1980, '2026-02-05 00:00:00', 507563, 'precio', 28, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1981, '2026-02-05 00:00:00', 770588, 'precio', 29, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1982, '2026-02-05 00:00:00', 731092, 'precio', 30, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1983, '2026-02-05 00:00:00', 366386, 'precio', 31, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1984, '2026-02-05 00:00:00', 174789, 'precio', 32, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1985, '2026-02-05 00:00:00', 257983, 'precio', 33, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1986, '2026-02-05 00:00:00', 345378, 'precio', 34, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1987, '2026-02-05 00:00:00', 440336, 'precio', 35, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1988, '2026-02-05 00:00:00', 532773, 'precio', 36, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1989, '2026-02-05 00:00:00', 609243, 'precio', 37, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1990, '2026-02-05 00:00:00', 689075, 'precio', 38, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1991, '2026-02-05 00:00:00', 765546, 'precio', 39, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1992, '2026-02-05 00:00:00', 171428, 'precio', 40, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1993, '2026-02-05 00:00:00', 271428, 'precio', 41, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1994, '2026-02-05 00:00:00', 375630, 'precio', 42, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1995, '2026-02-05 00:00:00', 473949, 'precio', 43, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1996, '2026-02-05 00:00:00', 616806, 'precio', 44, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1997, '2026-02-05 00:00:00', 701680, 'precio', 45, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1998, '2026-02-05 00:00:00', 816806, 'precio', 46, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(1999, '2026-02-05 00:00:00', 903361, 'precio', 47, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2000, '2026-02-05 00:00:00', 1033613, 'precio', 48, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2001, '2026-02-05 00:00:00', 269747, 'precio', 49, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2002, '2026-02-05 00:00:00', 405042, 'precio', 50, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2003, '2026-02-05 00:00:00', 540336, 'precio', 51, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2004, '2026-02-05 00:00:00', 745378, 'precio', 52, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2005, '2026-02-05 00:00:00', 877310, 'precio', 53, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2006, '2026-02-05 00:00:00', 1009243, 'precio', 54, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2007, '2026-02-05 00:00:00', 1231932, 'precio', 55, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2008, '2026-02-05 00:00:00', 1367226, 'precio', 56, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2009, '2026-02-05 00:00:00', 1488235, 'precio', 57, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2010, '2026-02-05 00:00:00', 0, 'precio', 58, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2011, '2026-02-05 00:00:00', 1915969, 'precio', 59, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2012, '2026-02-05 00:00:00', 2299159, 'precio', 60, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2013, '2026-02-05 00:00:00', 2259831, 'precio', 61, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2014, '2026-02-05 00:00:00', 3146217, 'precio', 62, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2015, '2026-02-05 00:00:00', 526050, 'precio', 63, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2016, '2026-02-05 00:00:00', 201680, 'precio', 64, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2017, '2026-02-05 00:00:00', 2941176, 'precio', 65, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2018, '2026-02-05 00:00:00', 299159, 'precio', 66, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2019, '2026-02-05 00:00:00', 413445, 'precio', 67, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2020, '2026-02-05 00:00:00', 514285, 'precio', 68, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2021, '2026-02-05 00:00:00', 646218, 'precio', 69, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2022, '2026-02-05 00:00:00', 747058, 'precio', 70, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2023, '2026-02-05 00:00:00', 319327, 'precio', 71, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2024, '2026-02-05 00:00:00', 1210084, 'precio', 72, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2025, '2026-02-05 00:00:00', 579831, 'precio', 73, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2026, '2026-02-05 00:00:00', 180672, 'precio', 74, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2027, '2026-02-05 00:00:00', 848739, 'precio', 75, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2028, '2026-02-05 00:00:00', 949579, 'precio', 76, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2029, '2026-02-05 00:00:00', 186554, 'precio', 77, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2030, '2026-02-05 00:00:00', 310084, 'precio', 78, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2031, '2026-02-05 00:00:00', 429411, 'precio', 79, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2032, '2026-02-05 00:00:00', 535294, 'precio', 80, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2033, '2026-02-05 00:00:00', 673109, 'precio', 81, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2034, '2026-02-05 00:00:00', 778151, 'precio', 82, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2035, '2026-02-05 00:00:00', 885714, 'precio', 83, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2036, '2026-02-05 00:00:00', 990756, 'precio', 84, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2037, '2026-02-05 00:00:00', 378151, 'precio', 85, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2038, '2026-02-05 00:00:00', 588235, 'precio', 86, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2039, '2026-02-05 00:00:00', 793729, 'precio', 87, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2040, '2026-02-05 00:00:00', 333310, 'precio', 88, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2041, '2026-02-05 00:00:00', 519731, 'precio', 89, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2042, '2026-02-05 00:00:00', 305042, 'precio', 90, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2043, '2026-02-05 00:00:00', 512605, 'precio', 91, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2044, '2026-02-05 00:00:00', 724369, 'precio', 92, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2045, '2026-02-05 00:00:00', 930252, 'precio', 93, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2046, '2026-02-05 00:00:00', 1179831, 'precio', 94, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2047, '2026-02-05 00:00:00', 1371428, 'precio', 95, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2048, '2026-02-05 00:00:00', 1594117, 'precio', 96, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2049, '2026-02-05 00:00:00', 1787394, 'precio', 97, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2050, '2026-02-05 00:00:00', 2025210, 'precio', 98, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2051, '2026-02-05 00:00:00', 655462, 'precio', 99, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2052, '2026-02-05 00:00:00', 539510, 'precio', 100, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2053, '2026-02-05 00:00:00', 183193, 'precio', 101, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2054, '2026-02-05 00:00:00', 327731, 'precio', 102, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2055, '2026-02-05 00:00:00', 315966, 'precio', 103, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2056, '2026-02-05 00:00:00', 532773, 'precio', 104, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2057, '2026-02-05 00:00:00', 753781, 'precio', 105, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2058, '2026-02-05 00:00:00', 969747, 'precio', 106, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2059, '2026-02-05 00:00:00', 1228571, 'precio', 107, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2060, '2026-02-05 00:00:00', 1430252, 'precio', 108, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2061, '2026-02-05 00:00:00', 1663025, 'precio', 109, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2062, '2026-02-05 00:00:00', 1865546, 'precio', 110, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2063, '2026-02-05 00:00:00', 2112605, 'precio', 111, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2064, '2026-02-05 00:00:00', 827731, 'precio', 112, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2065, '2026-02-05 00:00:00', 287394, 'precio', 113, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2066, '2026-02-05 00:00:00', 1200000, 'precio', 114, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2067, '2026-02-05 00:00:00', 1176470, 'precio', 115, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2068, '2026-02-05 00:00:00', 1050420, 'precio', 116, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2069, '2026-02-05 00:00:00', 3469831, 'precio', 117, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2070, '2026-02-05 00:00:00', 183193, 'precio', 118, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2071, '2026-02-05 00:00:00', 246218, 'precio', 119, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2072, '2026-02-05 00:00:00', 400000, 'precio', 120, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2073, '2026-02-05 00:00:00', 146218, 'precio', 121, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2074, '2026-02-05 00:00:00', 339495, 'precio', 122, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2075, '2026-02-05 00:00:00', 447058, 'precio', 123, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2076, '2026-02-05 00:00:00', 542016, 'precio', 124, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2077, '2026-02-05 00:00:00', 589075, 'precio', 125, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2078, '2026-02-05 00:00:00', 683193, 'precio', 126, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2079, '2026-02-05 00:00:00', 189915, 'precio', 127, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2080, '2026-02-05 00:00:00', 324369, 'precio', 128, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2081, '2026-02-05 00:00:00', 410084, 'precio', 129, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2082, '2026-02-05 00:00:00', 555462, 'precio', 130, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2083, '2026-02-05 00:00:00', 686554, 'precio', 131, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2084, '2026-02-05 00:00:00', 771428, 'precio', 132, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2085, '2026-02-05 00:00:00', 932773, 'precio', 133, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2086, '2026-02-05 00:00:00', 1065546, 'precio', 134, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2087, '2026-02-05 00:00:00', 1137815, 'precio', 135, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2088, '2026-02-05 00:00:00', 174789, 'precio', 136, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2089, '2026-02-05 00:00:00', 244537, 'precio', 137, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2090, '2026-02-05 00:00:00', 357983, 'precio', 138, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2091, '2026-02-05 00:00:00', 424369, 'precio', 139, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2092, '2026-02-05 00:00:00', 550420, 'precio', 140, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2093, '2026-02-05 00:00:00', 615966, 'precio', 141, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2094, '2026-02-05 00:00:00', 729411, 'precio', 142, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2095, '2026-02-05 00:00:00', 794957, 'precio', 143, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2096, '2026-02-05 00:00:00', 275630, 'precio', 144, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2097, '2026-02-05 00:00:00', 447899, 'precio', 145, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2098, '2026-02-05 00:00:00', 618487, 'precio', 146, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2099, '2026-02-05 00:00:00', 800840, 'precio', 147, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2100, '2026-02-05 00:00:00', 969747, 'precio', 148, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2101, '2026-02-05 00:00:00', 1138655, 'precio', 149, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2102, '2026-02-05 00:00:00', 1338655, 'precio', 150, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2103, '2026-02-05 00:00:00', 1508403, 'precio', 151, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2104, '2026-02-05 00:00:00', 1665546, 'precio', 152, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2105, '2026-02-05 00:00:00', 378151, 'precio', 153, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2106, '2026-02-05 00:00:00', 579831, 'precio', 154, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2107, '2026-02-05 00:00:00', 319327, 'precio', 155, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2108, '2026-02-05 00:00:00', 243697, 'precio', 156, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2109, '2026-02-05 00:00:00', 588235, 'precio', 157, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2110, '2026-02-05 00:00:00', 168067, 'precio', 158, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2111, '2026-02-05 00:00:00', 0, 'precio', 159, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2112, '2026-02-05 00:00:00', 327731, 'precio', 160, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2113, '2026-02-05 00:00:00', 34831, 'precio', 161, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2114, '2026-02-05 00:00:00', 11764, 'precio', 162, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2115, '2026-02-05 00:00:00', 15546, 'precio', 163, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2116, '2026-02-05 00:00:00', 6722, 'precio', 164, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2117, '2026-02-05 00:00:00', 34789, 'precio', 165, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2118, '2026-02-05 00:00:00', 34789, 'precio', 166, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2119, '2026-02-05 00:00:00', 34789, 'precio', 167, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2120, '2026-02-05 00:00:00', 19747, 'precio', 168, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2121, '2026-02-05 00:00:00', 1386, 'precio', 169, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2122, '2026-02-05 00:00:00', 104400, 'precio', 170, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2123, '2026-02-05 00:00:00', 31092, 'precio', 171, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2124, '2026-02-05 00:00:00', 39495, 'precio', 172, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2125, '2026-02-05 00:00:00', 33613, 'precio', 173, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2126, '2026-02-05 00:00:00', 30672, 'precio', 174, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2127, '2026-02-05 00:00:00', 49159, 'precio', 175, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2128, '2026-02-05 00:00:00', 73949, 'precio', 176, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2129, '2026-02-05 00:00:00', 55462, 'precio', 177, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2130, '2026-02-05 00:00:00', 66386, 'precio', 178, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2131, '2026-02-05 00:00:00', 60504, 'precio', 179, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2132, '2026-02-05 00:00:00', 78151, 'precio', 180, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2133, '2026-02-05 00:00:00', 109243, 'precio', 181, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL);
INSERT INTO `inv_values` (`id`, `date`, `values`, `type`, `itemId`, `warehouseId`, `label`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2134, '2026-02-05 00:00:00', 126050, 'precio', 182, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2135, '2026-02-05 00:00:00', 79831, 'precio', 183, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2136, '2026-02-05 00:00:00', 109243, 'precio', 184, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2137, '2026-02-05 00:00:00', 45378, 'precio', 185, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2138, '2026-02-05 00:00:00', 79831, 'precio', 186, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2139, '2026-02-05 00:00:00', 92436, 'precio', 187, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2140, '2026-02-05 00:00:00', 107563, 'precio', 188, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2141, '2026-02-05 00:00:00', 57142, 'precio', 189, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2142, '2026-02-05 00:00:00', 57142, 'precio', 190, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2143, '2026-02-05 00:00:00', 66386, 'precio', 191, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2144, '2026-02-05 00:00:00', 105042, 'precio', 192, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2145, '2026-02-05 00:00:00', 50420, 'precio', 193, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2146, '2026-02-05 00:00:00', 58823, 'precio', 194, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2147, '2026-02-05 00:00:00', 33613, 'precio', 195, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2148, '2026-02-05 00:00:00', 50420, 'precio', 196, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2149, '2026-02-05 00:00:00', 75630, 'precio', 197, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2150, '2026-02-05 00:00:00', 92436, 'precio', 198, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2151, '2026-02-05 00:00:00', 63025, 'precio', 199, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2152, '2026-02-05 00:00:00', 79831, 'precio', 200, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2153, '2026-02-05 00:00:00', 54621, 'precio', 201, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2154, '2026-02-05 00:00:00', 66386, 'precio', 202, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2155, '2026-02-05 00:00:00', 56302, 'precio', 203, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2156, '2026-02-05 00:00:00', 90756, 'precio', 204, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2157, '2026-02-05 00:00:00', 68067, 'precio', 205, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2158, '2026-02-05 00:00:00', 122689, 'precio', 206, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2159, '2026-02-05 00:00:00', 68067, 'precio', 207, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2160, '2026-02-05 00:00:00', 78991, 'precio', 208, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2161, '2026-02-05 00:00:00', 78991, 'precio', 209, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2162, '2026-02-05 00:00:00', 126890, 'precio', 210, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2163, '2026-02-05 00:00:00', 144537, 'precio', 211, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2164, '2026-02-05 00:00:00', 194957, 'precio', 212, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2165, '2026-02-05 00:00:00', 121848, 'precio', 213, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2166, '2026-02-05 00:00:00', 138655, 'precio', 214, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2167, '2026-02-05 00:00:00', 118487, 'precio', 215, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2168, '2026-02-05 00:00:00', 78, 'precio', 216, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2169, '2026-02-05 00:00:00', 75, 'precio', 217, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2170, '2026-02-05 00:00:00', 134, 'precio', 218, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2171, '2026-02-05 00:00:00', 100, 'precio', 219, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2172, '2026-02-05 00:00:00', 124, 'precio', 220, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2173, '2026-02-05 00:00:00', 107, 'precio', 221, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2174, '2026-02-05 00:00:00', 59, 'precio', 222, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2175, '2026-02-05 00:00:00', 41, 'precio', 223, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2176, '2026-02-05 00:00:00', 34, 'precio', 224, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2177, '2026-02-05 00:00:00', 34, 'precio', 225, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2178, '2026-02-05 00:00:00', 148, 'precio', 226, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2179, '2026-02-05 00:00:00', 176, 'precio', 227, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2180, '2026-02-05 00:00:00', 154, 'precio', 228, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2181, '2026-02-05 00:00:00', 154, 'precio', 229, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2182, '2026-02-05 00:00:00', 98, 'precio', 230, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2183, '2026-02-05 00:00:00', 118, 'precio', 231, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2184, '2026-02-05 00:00:00', 42016, 'precio', 232, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2185, '2026-02-05 00:00:00', 85, 'precio', 233, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2186, '2026-02-05 00:00:00', 82, 'precio', 234, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2187, '2026-02-05 00:00:00', 117, 'precio', 235, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2188, '2026-02-05 00:00:00', 84, 'precio', 236, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2189, '2026-02-05 00:00:00', 235, 'precio', 237, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2190, '2026-02-05 00:00:00', 197, 'precio', 238, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2191, '2026-02-05 00:00:00', 235, 'precio', 239, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2192, '2026-02-05 00:00:00', 84, 'precio', 240, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2193, '2026-02-05 00:00:00', 51, 'precio', 241, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2194, '2026-02-05 00:00:00', 52, 'precio', 242, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2195, '2026-02-05 00:00:00', 52, 'precio', 243, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2196, '2026-02-05 00:00:00', 1344, 'precio', 244, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2197, '2026-02-05 00:00:00', 1344, 'precio', 245, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2198, '2026-02-05 00:00:00', 1176, 'precio', 246, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2199, '2026-02-05 00:00:00', 1092, 'precio', 247, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2200, '2026-02-05 00:00:00', 1260, 'precio', 248, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2201, '2026-02-05 00:00:00', 1176, 'precio', 249, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2202, '2026-02-05 00:00:00', 15966, 'precio', 250, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2203, '2026-02-05 00:00:00', 40084, 'precio', 251, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2204, '2026-02-05 00:00:00', 2268, 'precio', 252, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2205, '2026-02-05 00:00:00', 1806, 'precio', 253, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2206, '2026-02-05 00:00:00', 2310, 'precio', 254, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2207, '2026-02-05 00:00:00', 224, 'precio', 255, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2208, '2026-02-05 00:00:00', 124, 'precio', 256, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2209, '2026-02-05 00:00:00', 116, 'precio', 257, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2210, '2026-02-05 00:00:00', 116, 'precio', 258, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2211, '2026-02-05 00:00:00', 137, 'precio', 259, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2212, '2026-02-05 00:00:00', 180, 'precio', 260, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2213, '2026-02-05 00:00:00', 137, 'precio', 261, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2214, '2026-02-05 00:00:00', 394, 'precio', 262, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2215, '2026-02-05 00:00:00', 689, 'precio', 263, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2216, '2026-02-05 00:00:00', 133, 'precio', 264, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2217, '2026-02-05 00:00:00', 64, 'precio', 265, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2218, '2026-02-05 00:00:00', 64, 'precio', 266, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2219, '2026-02-05 00:00:00', 64, 'precio', 267, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2220, '2026-02-05 00:00:00', 65, 'precio', 268, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2221, '2026-02-05 00:00:00', 92, 'precio', 269, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2222, '2026-02-05 00:00:00', 92, 'precio', 270, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2223, '2026-02-05 00:00:00', 4201, 'precio', 271, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2224, '2026-02-05 00:00:00', 5882, 'precio', 272, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2225, '2026-02-05 00:00:00', 9243, 'precio', 273, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2226, '2026-02-05 00:00:00', 13445, 'precio', 274, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2227, '2026-02-05 00:00:00', 9865546, 'precio', 276, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2228, '2026-02-05 00:00:00', 1900, 'precio', 277, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2229, '2026-02-05 00:00:00', 3800, 'precio', 278, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2230, '2026-02-05 00:00:00', 3800, 'precio', 279, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2231, '2026-02-05 00:00:00', 7500, 'precio', 280, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2232, '2026-02-05 00:00:00', 7500, 'precio', 281, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2233, '2026-02-05 00:00:00', 7500, 'precio', 282, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2234, '2026-02-05 00:00:00', 7500, 'precio', 283, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2235, '2026-02-05 00:00:00', 7500, 'precio', 284, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2236, '2026-02-05 00:00:00', 6500, 'precio', 285, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2237, '2026-02-05 00:00:00', 1900, 'precio', 286, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2238, '2026-02-05 00:00:00', 689075, 'precio', 287, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2239, '2026-02-05 00:00:00', 924369, 'precio', 288, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2240, '2026-02-05 00:00:00', 605042, 'precio', 289, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2241, '2026-02-05 00:00:00', 75630, 'precio', 290, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2242, '2026-02-05 00:00:00', 1260504, 'precio', 291, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2243, '2026-02-05 00:00:00', 14285, 'precio', 292, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2244, '2026-02-05 00:00:00', 15126, 'precio', 293, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2245, '2026-02-05 00:00:00', 22689, 'precio', 294, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2246, '2026-02-05 00:00:00', 31932, 'precio', 295, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2247, '2026-02-05 00:00:00', 672, 'precio', 296, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2248, '2026-02-05 00:00:00', 45378, 'precio', 297, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2249, '2026-02-05 00:00:00', 924369, 'precio', 298, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2250, '2026-02-05 00:00:00', 184873, 'precio', 299, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2251, '2026-02-05 00:00:00', 273109, 'precio', 300, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2252, '2026-02-05 00:00:00', 159663, 'precio', 301, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2253, '2026-02-05 00:00:00', 173949, 'precio', 302, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2254, '2026-02-05 00:00:00', 1386554, 'precio', 303, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2255, '2026-02-05 00:00:00', 428571, 'precio', 304, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2256, '2026-02-05 00:00:00', 478991, 'precio', 305, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2257, '2026-02-05 00:00:00', 798319, 'precio', 306, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2258, '2026-02-05 00:00:00', 542016, 'precio', 307, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2259, '2026-02-05 00:00:00', 840336, 'precio', 308, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2260, '2026-02-05 00:00:00', 1084033, 'precio', 309, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2261, '2026-02-05 00:00:00', 26890, 'precio', 310, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2262, '2026-02-05 00:00:00', 441176, 'precio', 311, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2263, '2026-02-05 00:00:00', 462184, 'precio', 312, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2264, '2026-02-05 00:00:00', 0, 'precio', 313, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2265, '2026-02-05 00:00:00', 0, 'precio', 314, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2266, '2026-02-05 00:00:00', 63865, 'precio', 315, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2267, '2026-02-05 00:00:00', 25210, 'precio', 316, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2268, '2026-02-05 00:00:00', 756, 'precio', 317, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2269, '2026-02-05 00:00:00', 1008, 'precio', 318, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2270, '2026-02-05 00:00:00', 630, 'precio', 319, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2271, '2026-02-05 00:00:00', 840, 'precio', 320, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2272, '2026-02-05 00:00:00', 924, 'precio', 321, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2273, '2026-02-05 00:00:00', 924, 'precio', 322, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2274, '2026-02-05 00:00:00', 924, 'precio', 323, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2275, '2026-02-05 00:00:00', 924, 'precio', 324, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2276, '2026-02-05 00:00:00', 924, 'precio', 325, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2277, '2026-02-05 00:00:00', 924, 'precio', 326, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2278, '2026-02-05 00:00:00', 2436, 'precio', 327, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2279, '2026-02-05 00:00:00', 2436, 'precio', 328, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2280, '2026-02-05 00:00:00', 924, 'precio', 329, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2281, '2026-02-05 00:00:00', 924, 'precio', 330, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2282, '2026-02-05 00:00:00', 924, 'precio', 331, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2283, '2026-02-05 00:00:00', 924, 'precio', 332, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2284, '2026-02-05 00:00:00', 924, 'precio', 333, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2285, '2026-02-05 00:00:00', 714, 'precio', 334, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2286, '2026-02-05 00:00:00', 714, 'precio', 335, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2287, '2026-02-05 00:00:00', 714, 'precio', 336, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2288, '2026-02-05 00:00:00', 714, 'precio', 337, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2289, '2026-02-05 00:00:00', 714, 'precio', 338, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2290, '2026-02-05 00:00:00', 672, 'precio', 339, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2291, '2026-02-05 00:00:00', 714, 'precio', 340, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2292, '2026-02-05 00:00:00', 840, 'precio', 341, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2293, '2026-02-05 00:00:00', 630, 'precio', 342, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2294, '2026-02-05 00:00:00', 630, 'precio', 343, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2295, '2026-02-05 00:00:00', 840, 'precio', 344, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2296, '2026-02-05 00:00:00', 630, 'precio', 345, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2297, '2026-02-05 00:00:00', 630, 'precio', 346, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2298, '2026-02-05 00:00:00', 630, 'precio', 347, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2299, '2026-02-05 00:00:00', 630, 'precio', 348, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2300, '2026-02-05 00:00:00', 630, 'precio', 349, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2301, '2026-02-05 00:00:00', 840, 'precio', 350, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2302, '2026-02-05 00:00:00', 630, 'precio', 351, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2303, '2026-02-05 00:00:00', 630, 'precio', 352, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2304, '2026-02-05 00:00:00', 630, 'precio', 353, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2305, '2026-02-05 00:00:00', 630, 'precio', 354, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2306, '2026-02-05 00:00:00', 924, 'precio', 355, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2307, '2026-02-05 00:00:00', 924, 'precio', 356, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2308, '2026-02-05 00:00:00', 924, 'precio', 357, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2309, '2026-02-05 00:00:00', 924, 'precio', 358, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2310, '2026-02-05 00:00:00', 672, 'precio', 359, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2311, '2026-02-05 00:00:00', 630, 'precio', 360, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2312, '2026-02-05 00:00:00', 672, 'precio', 361, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2313, '2026-02-05 00:00:00', 588, 'precio', 362, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2314, '2026-02-05 00:00:00', 588, 'precio', 363, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2315, '2026-02-05 00:00:00', 588, 'precio', 364, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2316, '2026-02-05 00:00:00', 588, 'precio', 365, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2317, '2026-02-05 00:00:00', 588, 'precio', 366, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2318, '2026-02-05 00:00:00', 588, 'precio', 367, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2319, '2026-02-05 00:00:00', 588, 'precio', 368, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2320, '2026-02-05 00:00:00', 588, 'precio', 369, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2321, '2026-02-05 00:00:00', 588, 'precio', 370, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2322, '2026-02-05 00:00:00', 588, 'precio', 371, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2323, '2026-02-05 00:00:00', 588, 'precio', 372, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2324, '2026-02-05 00:00:00', 588, 'precio', 373, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2325, '2026-02-05 00:00:00', 588, 'precio', 374, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2326, '2026-02-05 00:00:00', 588, 'precio', 375, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2327, '2026-02-05 00:00:00', 588, 'precio', 376, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2328, '2026-02-05 00:00:00', 588, 'precio', 377, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2329, '2026-02-05 00:00:00', 1260, 'precio', 378, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2330, '2026-02-05 00:00:00', 1260, 'precio', 379, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2331, '2026-02-05 00:00:00', 1260, 'precio', 380, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2332, '2026-02-05 00:00:00', 1260, 'precio', 381, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2333, '2026-02-05 00:00:00', 1260, 'precio', 382, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2334, '2026-02-05 00:00:00', 1260, 'precio', 383, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2335, '2026-02-05 00:00:00', 1260, 'precio', 384, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2336, '2026-02-05 00:00:00', 1260, 'precio', 385, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2337, '2026-02-05 00:00:00', 1260, 'precio', 386, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2338, '2026-02-05 00:00:00', 1260, 'precio', 387, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2339, '2026-02-05 00:00:00', 1260, 'precio', 388, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2340, '2026-02-05 00:00:00', 1260, 'precio', 389, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2341, '2026-02-05 00:00:00', 1260, 'precio', 390, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2342, '2026-02-05 00:00:00', 1260, 'precio', 391, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2343, '2026-02-05 00:00:00', 1260, 'precio', 392, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2344, '2026-02-05 00:00:00', 1260, 'precio', 393, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2345, '2026-02-05 00:00:00', 630, 'precio', 394, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2346, '2026-02-05 00:00:00', 1596, 'precio', 395, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2347, '2026-02-05 00:00:00', 714, 'precio', 396, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2348, '2026-02-05 00:00:00', 714, 'precio', 397, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2349, '2026-02-05 00:00:00', 714, 'precio', 398, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2350, '2026-02-05 00:00:00', 714, 'precio', 399, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2351, '2026-02-05 00:00:00', 714, 'precio', 400, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2352, '2026-02-05 00:00:00', 714, 'precio', 401, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2353, '2026-02-05 00:00:00', 714, 'precio', 402, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2354, '2026-02-05 00:00:00', 714, 'precio', 403, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2355, '2026-02-05 00:00:00', 714, 'precio', 404, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2356, '2026-02-05 00:00:00', 714, 'precio', 405, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2357, '2026-02-05 00:00:00', 714, 'precio', 406, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2358, '2026-02-05 00:00:00', 714, 'precio', 407, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2359, '2026-02-05 00:00:00', 714, 'precio', 408, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2360, '2026-02-05 00:00:00', 714, 'precio', 409, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2361, '2026-02-05 00:00:00', 714, 'precio', 410, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2362, '2026-02-05 00:00:00', 714, 'precio', 411, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2363, '2026-02-05 00:00:00', 1344, 'precio', 412, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2364, '2026-02-05 00:00:00', 1344, 'precio', 413, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2365, '2026-02-05 00:00:00', 1344, 'precio', 414, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2366, '2026-02-05 00:00:00', 1344, 'precio', 415, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2367, '2026-02-05 00:00:00', 1344, 'precio', 416, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2368, '2026-02-05 00:00:00', 1344, 'precio', 417, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2369, '2026-02-05 00:00:00', 1344, 'precio', 418, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2370, '2026-02-05 00:00:00', 1344, 'precio', 419, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2371, '2026-02-05 00:00:00', 1344, 'precio', 420, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2372, '2026-02-05 00:00:00', 1344, 'precio', 421, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2373, '2026-02-05 00:00:00', 1764, 'precio', 422, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2374, '2026-02-05 00:00:00', 1764, 'precio', 423, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2375, '2026-02-05 00:00:00', 2941, 'precio', 424, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2376, '2026-02-05 00:00:00', 1512, 'precio', 425, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2377, '2026-02-05 00:00:00', 1848, 'precio', 426, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2378, '2026-02-05 00:00:00', 1764, 'precio', 427, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2379, '2026-02-05 00:00:00', 1764, 'precio', 428, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2380, '2026-02-05 00:00:00', 1764, 'precio', 429, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2381, '2026-02-05 00:00:00', 714, 'precio', 430, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2382, '2026-02-05 00:00:00', 630, 'precio', 431, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2383, '2026-02-05 00:00:00', 1134, 'precio', 432, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2384, '2026-02-05 00:00:00', 1470, 'precio', 433, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2385, '2026-02-05 00:00:00', 1470, 'precio', 434, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2386, '2026-02-05 00:00:00', 1470, 'precio', 435, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2387, '2026-02-05 00:00:00', 1470, 'precio', 436, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2388, '2026-02-05 00:00:00', 1470, 'precio', 437, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2389, '2026-02-05 00:00:00', 1470, 'precio', 438, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2390, '2026-02-05 00:00:00', 1470, 'precio', 439, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2391, '2026-02-05 00:00:00', 1470, 'precio', 440, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2392, '2026-02-05 00:00:00', 10084, 'precio', 441, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2393, '2026-02-05 00:00:00', 1470, 'precio', 442, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2394, '2026-02-05 00:00:00', 1470, 'precio', 443, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2395, '2026-02-05 00:00:00', 1470, 'precio', 444, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2396, '2026-02-05 00:00:00', 1470, 'precio', 445, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2397, '2026-02-05 00:00:00', 1470, 'precio', 446, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2398, '2026-02-05 00:00:00', 1134, 'precio', 447, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2399, '2026-02-05 00:00:00', 3361, 'precio', 448, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2400, '2026-02-05 00:00:00', 4705, 'precio', 449, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2401, '2026-02-05 00:00:00', 4705, 'precio', 450, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2402, '2026-02-05 00:00:00', 4705, 'precio', 451, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2403, '2026-02-05 00:00:00', 1806, 'precio', 452, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2404, '2026-02-05 00:00:00', 1176, 'precio', 453, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2405, '2026-02-05 00:00:00', 1512, 'precio', 454, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2406, '2026-02-05 00:00:00', 4705, 'precio', 455, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2407, '2026-02-05 00:00:00', 4705, 'precio', 456, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2408, '2026-02-05 00:00:00', 4705, 'precio', 457, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2409, '2026-02-05 00:00:00', 2436, 'precio', 458, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2410, '2026-02-05 00:00:00', 2100, 'precio', 459, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2411, '2026-02-05 00:00:00', 1176, 'precio', 460, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2412, '2026-02-05 00:00:00', 1638, 'precio', 461, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2413, '2026-02-05 00:00:00', 2436, 'precio', 462, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2414, '2026-02-05 00:00:00', 966, 'precio', 463, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2415, '2026-02-05 00:00:00', 1092, 'precio', 464, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2416, '2026-02-05 00:00:00', 756, 'precio', 465, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2417, '2026-02-05 00:00:00', 1764, 'precio', 466, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2418, '2026-02-05 00:00:00', 1176, 'precio', 467, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2419, '2026-02-05 00:00:00', 4453, 'precio', 468, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2420, '2026-02-05 00:00:00', 5462, 'precio', 469, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2421, '2026-02-05 00:00:00', 5630, 'precio', 470, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2422, '2026-02-05 00:00:00', 5630, 'precio', 471, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2423, '2026-02-05 00:00:00', 5630, 'precio', 472, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2424, '2026-02-05 00:00:00', 5630, 'precio', 473, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2425, '2026-02-05 00:00:00', 3361, 'precio', 474, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2426, '2026-02-05 00:00:00', 840, 'precio', 475, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2427, '2026-02-05 00:00:00', 840, 'precio', 476, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2428, '2026-02-05 00:00:00', 798, 'precio', 477, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2429, '2026-02-05 00:00:00', 840, 'precio', 478, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2430, '2026-02-05 00:00:00', 1050, 'precio', 479, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2431, '2026-02-05 00:00:00', 840, 'precio', 480, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2432, '2026-02-05 00:00:00', 1050, 'precio', 481, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2433, '2026-02-05 00:00:00', 1050, 'precio', 482, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2434, '2026-02-05 00:00:00', 1050, 'precio', 483, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2435, '2026-02-05 00:00:00', 840, 'precio', 484, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2436, '2026-02-05 00:00:00', 4117, 'precio', 485, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2437, '2026-02-05 00:00:00', 4705, 'precio', 486, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2438, '2026-02-05 00:00:00', 4705, 'precio', 487, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2439, '2026-02-05 00:00:00', 5630, 'precio', 488, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2440, '2026-02-05 00:00:00', 5630, 'precio', 489, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2441, '2026-02-05 00:00:00', 5630, 'precio', 490, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2442, '2026-02-05 00:00:00', 4705, 'precio', 491, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2443, '2026-02-05 00:00:00', 1470, 'precio', 492, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2444, '2026-02-05 00:00:00', 1470, 'precio', 493, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2445, '2026-02-05 00:00:00', 5042, 'precio', 494, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2446, '2026-02-05 00:00:00', 2100, 'precio', 495, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2447, '2026-02-05 00:00:00', 1470, 'precio', 496, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2448, '2026-02-05 00:00:00', 5042, 'precio', 497, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2449, '2026-02-05 00:00:00', 1512, 'precio', 498, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2450, '2026-02-05 00:00:00', 3781, 'precio', 499, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2451, '2026-02-05 00:00:00', 756, 'precio', 500, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2452, '2026-02-05 00:00:00', 14957, 'precio', 501, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2453, '2026-02-05 00:00:00', 9411, 'precio', 502, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2454, '2026-02-05 00:00:00', 12521, 'precio', 503, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2455, '2026-02-05 00:00:00', 12521, 'precio', 504, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2456, '2026-02-05 00:00:00', 48319, 'precio', 505, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2457, '2026-02-05 00:00:00', 10924, 'precio', 506, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2458, '2026-02-05 00:00:00', 12521, 'precio', 507, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2459, '2026-02-05 00:00:00', 12521, 'precio', 508, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2460, '2026-02-05 00:00:00', 9411, 'precio', 509, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2461, '2026-02-05 00:00:00', 9411, 'precio', 510, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2462, '2026-02-05 00:00:00', 5462, 'precio', 511, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2463, '2026-02-05 00:00:00', 10084, 'precio', 512, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2464, '2026-02-05 00:00:00', 10084, 'precio', 513, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2465, '2026-02-05 00:00:00', 10084, 'precio', 514, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2466, '2026-02-05 00:00:00', 11344, 'precio', 515, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2467, '2026-02-05 00:00:00', 11344, 'precio', 516, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2468, '2026-02-05 00:00:00', 12521, 'precio', 517, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2469, '2026-02-05 00:00:00', 50420, 'precio', 518, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2470, '2026-02-05 00:00:00', 50420, 'precio', 519, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2471, '2026-02-05 00:00:00', 48739, 'precio', 520, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2472, '2026-02-05 00:00:00', 26050, 'precio', 521, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2473, '2026-02-05 00:00:00', 26050, 'precio', 522, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2474, '2026-02-05 00:00:00', 26050, 'precio', 523, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2475, '2026-02-05 00:00:00', 17647, 'precio', 524, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2476, '2026-02-05 00:00:00', 17226, 'precio', 525, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2477, '2026-02-05 00:00:00', 17226, 'precio', 526, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2478, '2026-02-05 00:00:00', 13025, 'precio', 527, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2479, '2026-02-05 00:00:00', 12605, 'precio', 528, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2480, '2026-02-05 00:00:00', 12605, 'precio', 529, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2481, '2026-02-05 00:00:00', 4201, 'precio', 530, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2482, '2026-02-05 00:00:00', 4201, 'precio', 531, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2483, '2026-02-05 00:00:00', 4201, 'precio', 532, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2484, '2026-02-05 00:00:00', 16806, 'precio', 533, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2485, '2026-02-05 00:00:00', 37815, 'precio', 534, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2486, '2026-02-05 00:00:00', 37815, 'precio', 535, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2487, '2026-02-05 00:00:00', 37815, 'precio', 536, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2488, '2026-02-05 00:00:00', 37815, 'precio', 537, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2489, '2026-02-05 00:00:00', 37815, 'precio', 538, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2490, '2026-02-05 00:00:00', 29411, 'precio', 539, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2491, '2026-02-05 00:00:00', 33613, 'precio', 540, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2492, '2026-02-05 00:00:00', 29411, 'precio', 541, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2493, '2026-02-05 00:00:00', 29411, 'precio', 542, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2494, '2026-02-05 00:00:00', 29411, 'precio', 543, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2495, '2026-02-05 00:00:00', 42016, 'precio', 544, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2496, '2026-02-05 00:00:00', 59663, 'precio', 545, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2497, '2026-02-05 00:00:00', 47899, 'precio', 546, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2498, '2026-02-05 00:00:00', 25210, 'precio', 547, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2499, '2026-02-05 00:00:00', 21848, 'precio', 548, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2500, '2026-02-05 00:00:00', 21848, 'precio', 549, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2501, '2026-02-05 00:00:00', 25210, 'precio', 550, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2502, '2026-02-05 00:00:00', 21848, 'precio', 551, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2503, '2026-02-05 00:00:00', 38655, 'precio', 552, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2504, '2026-02-05 00:00:00', 168, 'precio', 553, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2505, '2026-02-05 00:00:00', 25210, 'precio', 554, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2506, '2026-02-05 00:00:00', 107563, 'precio', 555, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2507, '2026-02-05 00:00:00', 33613, 'precio', 556, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2508, '2026-02-05 00:00:00', 168, 'precio', 557, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2509, '2026-02-05 00:00:00', 147058, 'precio', 558, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2510, '2026-02-05 00:00:00', 293277, 'precio', 559, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2511, '2026-02-05 00:00:00', 457142, 'precio', 560, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2512, '2026-02-05 00:00:00', 603445, 'precio', 561, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2513, '2026-02-05 00:00:00', 39915, 'precio', 562, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2514, '2026-02-05 00:00:00', 17226, 'precio', 563, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2515, '2026-02-05 00:00:00', 21596, 'precio', 564, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2516, '2026-02-05 00:00:00', 21596, 'precio', 565, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2517, '2026-02-05 00:00:00', 101764, 'precio', 566, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2518, '2026-02-05 00:00:00', 133612, 'precio', 567, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2519, '2026-02-05 00:00:00', 159579, 'precio', 568, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2520, '2026-02-05 00:00:00', 112100, 'precio', 569, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2521, '2026-02-05 00:00:00', 12941, 'precio', 570, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2522, '2026-02-05 00:00:00', 14621, 'precio', 571, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2523, '2026-02-05 00:00:00', 31932, 'precio', 572, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2524, '2026-02-05 00:00:00', 12605, 'precio', 573, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2525, '2026-02-05 00:00:00', 15126, 'precio', 574, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2526, '2026-02-05 00:00:00', 16806, 'precio', 575, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2527, '2026-02-05 00:00:00', 33613, 'precio', 576, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2528, '2026-02-05 00:00:00', 25210, 'precio', 577, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2529, '2026-02-05 00:00:00', 36134, 'precio', 578, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2530, '2026-02-05 00:00:00', 487394, 'precio', 579, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2531, '2026-02-05 00:00:00', 83193, 'precio', 580, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2532, '2026-02-05 00:00:00', 840336, 'precio', 581, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2533, '2026-02-05 00:00:00', 21008, 'precio', 582, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2534, '2026-02-05 00:00:00', 23529, 'precio', 583, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2535, '2026-02-05 00:00:00', 37815, 'precio', 584, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2536, '2026-02-05 00:00:00', 49579, 'precio', 585, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2537, '2026-02-05 00:00:00', 50420, 'precio', 586, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2538, '2026-02-05 00:00:00', 116806, 'precio', 587, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2539, '2026-02-05 00:00:00', 117647, 'precio', 588, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2540, '2026-02-05 00:00:00', 147058, 'precio', 589, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2541, '2026-02-05 00:00:00', 204201, 'precio', 590, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2542, '2026-02-05 00:00:00', 290756, 'precio', 591, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2543, '2026-02-05 00:00:00', 35294, 'precio', 592, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2544, '2026-02-05 00:00:00', 65546, 'precio', 593, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2545, '2026-02-05 00:00:00', 240336, 'precio', 594, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2546, '2026-02-05 00:00:00', 152941, 'precio', 595, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2547, '2026-02-05 00:00:00', 29411, 'precio', 596, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2548, '2026-02-05 00:00:00', 26890, 'precio', 597, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL);
INSERT INTO `inv_values` (`id`, `date`, `values`, `type`, `itemId`, `warehouseId`, `label`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2549, '2026-02-05 00:00:00', 586554, 'precio', 598, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2550, '2026-02-05 00:00:00', 586554, 'precio', 599, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2551, '2026-02-05 00:00:00', 14705, 'precio', 600, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2552, '2026-02-05 00:00:00', 88235, 'precio', 601, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2553, '2026-02-05 00:00:00', 411764, 'precio', 602, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2554, '2026-02-05 00:00:00', 13445, 'precio', 603, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2555, '2026-02-05 00:00:00', 73109, 'precio', 604, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2556, '2026-02-05 00:00:00', 710084, 'precio', 605, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2557, '2026-02-05 00:00:00', 126050, 'precio', 606, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2558, '2026-02-05 00:00:00', 222689, 'precio', 607, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2559, '2026-02-05 00:00:00', 230252, 'precio', 608, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2560, '2026-02-05 00:00:00', 281512, 'precio', 609, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2561, '2026-02-05 00:00:00', 281512, 'precio', 610, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2562, '2026-02-05 00:00:00', 281512, 'precio', 611, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2563, '2026-02-05 00:00:00', 38655, 'precio', 612, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2564, '2026-02-05 00:00:00', 38655, 'precio', 613, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2565, '2026-02-05 00:00:00', 38655, 'precio', 614, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2566, '2026-02-05 00:00:00', 38655, 'precio', 615, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2567, '2026-02-05 00:00:00', 38655, 'precio', 616, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2568, '2026-02-05 00:00:00', 504201, 'precio', 617, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2569, '2026-02-05 00:00:00', 25210, 'precio', 618, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2570, '2026-02-05 00:00:00', 25210, 'precio', 619, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2571, '2026-02-05 00:00:00', 99159, 'precio', 620, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2572, '2026-02-05 00:00:00', 183193, 'precio', 621, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2573, '2026-02-05 00:00:00', 36554, 'precio', 622, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2574, '2026-02-05 00:00:00', 29411, 'precio', 623, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2575, '2026-02-05 00:00:00', 37815, 'precio', 624, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2576, '2026-02-05 00:00:00', 37815, 'precio', 625, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2577, '2026-02-05 00:00:00', 109243, 'precio', 626, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2578, '2026-02-05 00:00:00', 113445, 'precio', 627, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2579, '2026-02-05 00:00:00', 44117, 'precio', 628, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2580, '2026-02-05 00:00:00', 42016, 'precio', 629, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2581, '2026-02-05 00:00:00', 42016, 'precio', 630, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2582, '2026-02-05 00:00:00', 42016, 'precio', 631, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2583, '2026-02-05 00:00:00', 42016, 'precio', 632, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2584, '2026-02-05 00:00:00', 45378, 'precio', 633, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2585, '2026-02-05 00:00:00', 32773, 'precio', 634, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2586, '2026-02-05 00:00:00', 45378, 'precio', 635, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2587, '2026-02-05 00:00:00', 45378, 'precio', 636, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2588, '2026-02-05 00:00:00', 31932, 'precio', 637, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2589, '2026-02-05 00:00:00', 40336, 'precio', 638, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2590, '2026-02-05 00:00:00', 47058, 'precio', 639, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2591, '2026-02-05 00:00:00', 47058, 'precio', 640, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2592, '2026-02-05 00:00:00', 47899, 'precio', 641, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2593, '2026-02-05 00:00:00', 113445, 'precio', 642, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2594, '2026-02-05 00:00:00', 45378, 'precio', 643, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2595, '2026-02-05 00:00:00', 45378, 'precio', 644, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2596, '2026-02-05 00:00:00', 66386, 'precio', 645, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2597, '2026-02-05 00:00:00', 64705, 'precio', 646, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2598, '2026-02-05 00:00:00', 65546, 'precio', 647, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2599, '2026-02-05 00:00:00', 66386, 'precio', 648, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2600, '2026-02-05 00:00:00', 66386, 'precio', 649, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2601, '2026-02-05 00:00:00', 97478, 'precio', 650, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2602, '2026-02-05 00:00:00', 84033, 'precio', 651, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2603, '2026-02-05 00:00:00', 54621, 'precio', 652, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2604, '2026-02-05 00:00:00', 69747, 'precio', 653, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2605, '2026-02-05 00:00:00', 24369, 'precio', 654, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2606, '2026-02-05 00:00:00', 44537, 'precio', 655, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2607, '2026-02-05 00:00:00', 26890, 'precio', 656, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2608, '2026-02-05 00:00:00', 30252, 'precio', 657, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2609, '2026-02-05 00:00:00', 31512, 'precio', 658, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2610, '2026-02-05 00:00:00', 47899, 'precio', 659, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2611, '2026-02-05 00:00:00', 38655, 'precio', 660, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2612, '2026-02-05 00:00:00', 31512, 'precio', 661, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2613, '2026-02-05 00:00:00', 68907, 'precio', 662, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2614, '2026-02-05 00:00:00', 68907, 'precio', 663, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2615, '2026-02-05 00:00:00', 50420, 'precio', 664, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2616, '2026-02-05 00:00:00', 52100, 'precio', 665, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2617, '2026-02-05 00:00:00', 83193, 'precio', 666, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2618, '2026-02-05 00:00:00', 83193, 'precio', 667, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2619, '2026-02-05 00:00:00', 83193, 'precio', 668, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2620, '2026-02-05 00:00:00', 73949, 'precio', 669, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2621, '2026-02-05 00:00:00', 52100, 'precio', 670, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2622, '2026-02-05 00:00:00', 59663, 'precio', 671, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2623, '2026-02-05 00:00:00', 88235, 'precio', 672, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2624, '2026-02-05 00:00:00', 115966, 'precio', 673, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2625, '2026-02-05 00:00:00', 52941, 'precio', 674, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2626, '2026-02-05 00:00:00', 50420, 'precio', 675, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2627, '2026-02-05 00:00:00', 51260, 'precio', 676, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2628, '2026-02-05 00:00:00', 56302, 'precio', 677, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2629, '2026-02-05 00:00:00', 50420, 'precio', 678, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2630, '2026-02-05 00:00:00', 52100, 'precio', 679, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2631, '2026-02-05 00:00:00', 50420, 'precio', 680, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2632, '2026-02-05 00:00:00', 83193, 'precio', 681, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2633, '2026-02-05 00:00:00', 71428, 'precio', 682, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2634, '2026-02-05 00:00:00', 68907, 'precio', 683, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2635, '2026-02-05 00:00:00', 78151, 'precio', 684, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2636, '2026-02-05 00:00:00', 83193, 'precio', 685, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2637, '2026-02-05 00:00:00', 83193, 'precio', 686, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2638, '2026-02-05 00:00:00', 83193, 'precio', 687, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2639, '2026-02-05 00:00:00', 42857, 'precio', 688, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2640, '2026-02-05 00:00:00', 42857, 'precio', 689, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2641, '2026-02-05 00:00:00', 42857, 'precio', 690, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2642, '2026-02-05 00:00:00', 79831, 'precio', 691, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2643, '2026-02-05 00:00:00', 79831, 'precio', 692, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2644, '2026-02-05 00:00:00', 113445, 'precio', 693, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2645, '2026-02-05 00:00:00', 113445, 'precio', 694, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2646, '2026-02-05 00:00:00', 153781, 'precio', 695, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2647, '2026-02-05 00:00:00', 153781, 'precio', 696, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2648, '2026-02-05 00:00:00', 153781, 'precio', 697, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2649, '2026-02-05 00:00:00', 42857, 'precio', 698, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2650, '2026-02-05 00:00:00', 45378, 'precio', 699, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2651, '2026-02-05 00:00:00', 239495, 'precio', 700, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2652, '2026-02-05 00:00:00', 239495, 'precio', 701, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2653, '2026-02-05 00:00:00', 239495, 'precio', 702, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2654, '2026-02-05 00:00:00', 79831, 'precio', 703, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2655, '2026-02-05 00:00:00', 41176, 'precio', 704, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2656, '2026-02-05 00:00:00', 117647, 'precio', 705, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2657, '2026-02-05 00:00:00', 39495, 'precio', 706, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2658, '2026-02-05 00:00:00', 40756, 'precio', 707, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2659, '2026-02-05 00:00:00', 40756, 'precio', 708, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2660, '2026-02-05 00:00:00', 40756, 'precio', 709, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2661, '2026-02-05 00:00:00', 49579, 'precio', 710, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2662, '2026-02-05 00:00:00', 49579, 'precio', 711, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2663, '2026-02-05 00:00:00', 49579, 'precio', 712, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2664, '2026-02-05 00:00:00', 49579, 'precio', 713, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2665, '2026-02-05 00:00:00', 85714, 'precio', 714, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2666, '2026-02-05 00:00:00', 74789, 'precio', 715, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2667, '2026-02-05 00:00:00', 31932, 'precio', 716, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2668, '2026-02-05 00:00:00', 45378, 'precio', 717, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2669, '2026-02-05 00:00:00', 50420, 'precio', 718, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2670, '2026-02-05 00:00:00', 50420, 'precio', 719, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2671, '2026-02-05 00:00:00', 50420, 'precio', 720, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2672, '2026-02-05 00:00:00', 57142, 'precio', 721, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2673, '2026-02-05 00:00:00', 96638, 'precio', 722, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2674, '2026-02-05 00:00:00', 34453, 'precio', 723, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2675, '2026-02-05 00:00:00', 66386, 'precio', 724, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2676, '2026-02-05 00:00:00', 68907, 'precio', 725, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2677, '2026-02-05 00:00:00', 142857, 'precio', 726, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2678, '2026-02-05 00:00:00', 68907, 'precio', 727, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2679, '2026-02-05 00:00:00', 110084, 'precio', 728, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2680, '2026-02-05 00:00:00', 106722, 'precio', 729, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2681, '2026-02-05 00:00:00', 147058, 'precio', 730, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2682, '2026-02-05 00:00:00', 16806, 'precio', 731, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2683, '2026-02-05 00:00:00', 16806, 'precio', 732, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2684, '2026-02-05 00:00:00', 16806, 'precio', 733, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2685, '2026-02-05 00:00:00', 16806, 'precio', 734, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2686, '2026-02-05 00:00:00', 16806, 'precio', 735, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2687, '2026-02-05 00:00:00', 16806, 'precio', 736, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2688, '2026-02-05 00:00:00', 43697, 'precio', 737, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2689, '2026-02-05 00:00:00', 43697, 'precio', 738, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2690, '2026-02-05 00:00:00', 16806, 'precio', 739, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2691, '2026-02-05 00:00:00', 252, 'precio', 740, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2692, '2026-02-05 00:00:00', 252, 'precio', 741, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2693, '2026-02-05 00:00:00', 5630, 'precio', 742, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2694, '2026-02-05 00:00:00', 5630, 'precio', 743, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2695, '2026-02-05 00:00:00', 5630, 'precio', 744, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2696, '2026-02-05 00:00:00', 5630, 'precio', 745, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2697, '2026-02-05 00:00:00', 5630, 'precio', 746, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2698, '2026-02-05 00:00:00', 5630, 'precio', 747, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2699, '2026-02-05 00:00:00', 5630, 'precio', 748, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2700, '2026-02-05 00:00:00', 5630, 'precio', 749, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2701, '2026-02-05 00:00:00', 5630, 'precio', 750, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2702, '2026-02-05 00:00:00', 2521, 'precio', 751, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2703, '2026-02-05 00:00:00', 2521, 'precio', 752, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2704, '2026-02-05 00:00:00', 2521, 'precio', 753, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2705, '2026-02-05 00:00:00', 2521, 'precio', 754, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2706, '2026-02-05 00:00:00', 2521, 'precio', 755, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2707, '2026-02-05 00:00:00', 2521, 'precio', 756, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2708, '2026-02-05 00:00:00', 2521, 'precio', 757, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2709, '2026-02-05 00:00:00', 2521, 'precio', 758, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2710, '2026-02-05 00:00:00', 115126, 'precio', 759, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2711, '2026-02-05 00:00:00', 4453, 'precio', 760, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2712, '2026-02-05 00:00:00', 89075, 'precio', 761, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2713, '2026-02-05 00:00:00', 79831, 'precio', 762, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2714, '2026-02-05 00:00:00', 7563, 'precio', 763, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2715, '2026-02-05 00:00:00', 54621, 'precio', 764, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2716, '2026-02-05 00:00:00', 63025, 'precio', 765, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2717, '2026-02-05 00:00:00', 63025, 'precio', 766, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2718, '2026-02-05 00:00:00', 63025, 'precio', 767, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2719, '2026-02-05 00:00:00', 63025, 'precio', 768, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2720, '2026-02-05 00:00:00', 63025, 'precio', 769, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2721, '2026-02-05 00:00:00', 63025, 'precio', 770, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2722, '2026-02-05 00:00:00', 63025, 'precio', 771, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2723, '2026-02-05 00:00:00', 63025, 'precio', 772, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2724, '2026-02-05 00:00:00', 45378, 'precio', 773, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2725, '2026-02-05 00:00:00', 0, 'precio', 774, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2726, '2026-02-05 00:00:00', 52941, 'precio', 775, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2727, '2026-02-05 00:00:00', 37815, 'precio', 776, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2728, '2026-02-05 00:00:00', 55462, 'precio', 777, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2729, '2026-02-05 00:00:00', 134453, 'precio', 778, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2730, '2026-02-05 00:00:00', 10084, 'precio', 779, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2731, '2026-02-05 00:00:00', 57983, 'precio', 780, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2732, '2026-02-05 00:00:00', 49579, 'precio', 781, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2733, '2026-02-05 00:00:00', 31092, 'precio', 782, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2734, '2026-02-05 00:00:00', 50420, 'precio', 783, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2735, '2026-02-05 00:00:00', 10924, 'precio', 784, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2736, '2026-02-05 00:00:00', 108403, 'precio', 785, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2737, '2026-02-05 00:00:00', 133613, 'precio', 786, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2738, '2026-02-05 00:00:00', 226890, 'precio', 787, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2739, '2026-02-05 00:00:00', 125042, 'precio', 788, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2740, '2026-02-05 00:00:00', 25210, 'precio', 789, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2741, '2026-02-05 00:00:00', 9243, 'precio', 790, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2742, '2026-02-05 00:00:00', 6722, 'precio', 791, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2743, '2026-02-05 00:00:00', 3361, 'precio', 792, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2744, '2026-02-05 00:00:00', 68991, 'precio', 793, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2745, '2026-02-05 00:00:00', 426722, 'precio', 794, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2746, '2026-02-05 00:00:00', 37058, 'precio', 795, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2747, '2026-02-05 00:00:00', 301764, 'precio', 796, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2748, '2026-02-05 00:00:00', 424369, 'precio', 797, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2749, '2026-02-05 00:00:00', 801764, 'precio', 798, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2750, '2026-02-05 00:00:00', 54621, 'precio', 799, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2751, '2026-02-05 00:00:00', 54621, 'precio', 800, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2752, '2026-02-05 00:00:00', 79831, 'precio', 801, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2753, '2026-02-05 00:00:00', 68907, 'precio', 802, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2754, '2026-02-05 00:00:00', 61344, 'precio', 803, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2755, '2026-02-05 00:00:00', 79831, 'precio', 804, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2756, '2026-02-05 00:00:00', 47899, 'precio', 805, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2757, '2026-02-05 00:00:00', 42857, 'precio', 806, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2758, '2026-02-05 00:00:00', 40336, 'precio', 807, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2759, '2026-02-05 00:00:00', 28151, 'precio', 808, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2760, '2026-02-05 00:00:00', 28151, 'precio', 809, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2761, '2026-02-05 00:00:00', 29411, 'precio', 810, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2762, '2026-02-05 00:00:00', 35294, 'precio', 811, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2763, '2026-02-05 00:00:00', 73949, 'precio', 812, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2764, '2026-02-05 00:00:00', 46218, 'precio', 813, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2765, '2026-02-05 00:00:00', 46218, 'precio', 814, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2766, '2026-02-05 00:00:00', 46218, 'precio', 815, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2767, '2026-02-05 00:00:00', 46218, 'precio', 816, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2768, '2026-02-05 00:00:00', 54621, 'precio', 817, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2769, '2026-02-05 00:00:00', 54621, 'precio', 818, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2770, '2026-02-05 00:00:00', 54621, 'precio', 819, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2771, '2026-02-05 00:00:00', 79831, 'precio', 820, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2772, '2026-02-05 00:00:00', 47058, 'precio', 821, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2773, '2026-02-05 00:00:00', 34453, 'precio', 822, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2774, '2026-02-05 00:00:00', 68067, 'precio', 823, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2775, '2026-02-05 00:00:00', 84033, 'precio', 824, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2776, '2026-02-05 00:00:00', 151260, 'precio', 825, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2777, '2026-02-05 00:00:00', 58823, 'precio', 826, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2778, '2026-02-05 00:00:00', 57983, 'precio', 827, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2779, '2026-02-05 00:00:00', 42857, 'precio', 828, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2780, '2026-02-05 00:00:00', 17647, 'precio', 829, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2781, '2026-02-05 00:00:00', 52941, 'precio', 830, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2782, '2026-02-05 00:00:00', 77310, 'precio', 831, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2783, '2026-02-05 00:00:00', 64705, 'precio', 832, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2784, '2026-02-05 00:00:00', 40336, 'precio', 833, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2785, '2026-02-05 00:00:00', 12605, 'precio', 834, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2786, '2026-02-05 00:00:00', 16806, 'precio', 835, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2787, '2026-02-05 00:00:00', 16806, 'precio', 836, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2788, '2026-02-05 00:00:00', 56302, 'precio', 837, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2789, '2026-02-05 00:00:00', 54621, 'precio', 838, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2790, '2026-02-05 00:00:00', 11764, 'precio', 839, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2791, '2026-02-05 00:00:00', 15966, 'precio', 840, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2792, '2026-02-05 00:00:00', 29411, 'precio', 841, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2793, '2026-02-05 00:00:00', 29411, 'precio', 842, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2794, '2026-02-05 00:00:00', 40336, 'precio', 843, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2795, '2026-02-05 00:00:00', 40336, 'precio', 844, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2796, '2026-02-05 00:00:00', 25210, 'precio', 845, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2797, '2026-02-05 00:00:00', 16386, 'precio', 846, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2798, '2026-02-05 00:00:00', 18487, 'precio', 847, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2799, '2026-02-05 00:00:00', 65126, 'precio', 848, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2800, '2026-02-05 00:00:00', 62184, 'precio', 849, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2801, '2026-02-05 00:00:00', 56302, 'precio', 850, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2802, '2026-02-05 00:00:00', 67226, 'precio', 851, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2803, '2026-02-05 00:00:00', 36134, 'precio', 852, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2804, '2026-02-05 00:00:00', 18487, 'precio', 853, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2805, '2026-02-05 00:00:00', 12605, 'precio', 854, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2806, '2026-02-05 00:00:00', 12605, 'precio', 855, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2807, '2026-02-05 00:00:00', 12605, 'precio', 856, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2808, '2026-02-05 00:00:00', 218487, 'precio', 857, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2809, '2026-02-05 00:00:00', 11344, 'precio', 858, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2810, '2026-02-05 00:00:00', 47899, 'precio', 859, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2811, '2026-02-05 00:00:00', 32352, 'precio', 860, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2812, '2026-02-05 00:00:00', 47899, 'precio', 861, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2813, '2026-02-05 00:00:00', 80672, 'precio', 862, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2814, '2026-02-05 00:00:00', 24369, 'precio', 863, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2815, '2026-02-05 00:00:00', 92436, 'precio', 864, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2816, '2026-02-05 00:00:00', 64705, 'precio', 865, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2817, '2026-02-05 00:00:00', 64705, 'precio', 866, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2818, '2026-02-05 00:00:00', 70588, 'precio', 867, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2819, '2026-02-05 00:00:00', 147058, 'precio', 868, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2820, '2026-02-05 00:00:00', 11764, 'precio', 869, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2821, '2026-02-05 00:00:00', 12605, 'precio', 870, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2822, '2026-02-05 00:00:00', 17647, 'precio', 871, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2823, '2026-02-05 00:00:00', 30252, 'precio', 872, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2824, '2026-02-05 00:00:00', 14285, 'precio', 873, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2825, '2026-02-05 00:00:00', 15126, 'precio', 874, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2826, '2026-02-05 00:00:00', 13445, 'precio', 875, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2827, '2026-02-05 00:00:00', 14285, 'precio', 876, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2828, '2026-02-05 00:00:00', 141176, 'precio', 877, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2829, '2026-02-05 00:00:00', 104201, 'precio', 878, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2830, '2026-02-05 00:00:00', 37815, 'precio', 879, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2831, '2026-02-05 00:00:00', 44537, 'precio', 880, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2832, '2026-02-05 00:00:00', 80672, 'precio', 881, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2833, '2026-02-05 00:00:00', 80672, 'precio', 882, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2834, '2026-02-05 00:00:00', 104201, 'precio', 883, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2835, '2026-02-05 00:00:00', 12605, 'precio', 884, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2836, '2026-02-05 00:00:00', 23529, 'precio', 885, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2837, '2026-02-05 00:00:00', 43697, 'precio', 886, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2838, '2026-02-05 00:00:00', 42016, 'precio', 887, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2839, '2026-02-05 00:00:00', 94957, 'precio', 888, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2840, '2026-02-05 00:00:00', 12605, 'precio', 889, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2841, '2026-02-05 00:00:00', 5714, 'precio', 890, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2842, '2026-02-05 00:00:00', 5714, 'precio', 891, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2843, '2026-02-05 00:00:00', 9243, 'precio', 892, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2844, '2026-02-05 00:00:00', 410084, 'precio', 893, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2845, '2026-02-05 00:00:00', 5882, 'precio', 894, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2846, '2026-02-05 00:00:00', 14537, 'precio', 895, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2847, '2026-02-05 00:00:00', 20168, 'precio', 896, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2848, '2026-02-05 00:00:00', 11764, 'precio', 897, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2849, '2026-02-05 00:00:00', 10084, 'precio', 898, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2850, '2026-02-05 00:00:00', 205042, 'precio', 899, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2851, '2026-02-05 00:00:00', 226050, 'precio', 900, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2852, '2026-02-05 00:00:00', 263865, 'precio', 901, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2853, '2026-02-05 00:00:00', 306722, 'precio', 902, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2854, '2026-02-05 00:00:00', 183193, 'precio', 903, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2855, '2026-02-05 00:00:00', 221848, 'precio', 904, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2856, '2026-02-05 00:00:00', 264705, 'precio', 905, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2857, '2026-02-05 00:00:00', 300840, 'precio', 906, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2858, '2026-02-05 00:00:00', 280672, 'precio', 907, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2859, '2026-02-05 00:00:00', 32773, 'precio', 908, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2860, '2026-02-05 00:00:00', 47058, 'precio', 909, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2861, '2026-02-05 00:00:00', 57142, 'precio', 910, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2862, '2026-02-05 00:00:00', 18067, 'precio', 911, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2863, '2026-02-05 00:00:00', 10924, 'precio', 912, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2864, '2026-02-05 00:00:00', 16806, 'precio', 913, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2865, '2026-02-05 00:00:00', 18487, 'precio', 914, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2866, '2026-02-05 00:00:00', 0, 'precio', 915, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2867, '2026-02-05 00:00:00', 36974, 'precio', 916, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2868, '2026-02-05 00:00:00', 114285, 'precio', 917, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2869, '2026-02-05 00:00:00', 78151, 'precio', 918, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2870, '2026-02-05 00:00:00', 115966, 'precio', 919, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2871, '2026-02-05 00:00:00', 156302, 'precio', 920, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2872, '2026-02-05 00:00:00', 210084, 'precio', 921, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2873, '2026-02-05 00:00:00', 264705, 'precio', 922, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2874, '2026-02-05 00:00:00', 61344, 'precio', 923, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2875, '2026-02-05 00:00:00', 89075, 'precio', 924, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2876, '2026-02-05 00:00:00', 108403, 'precio', 925, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2877, '2026-02-05 00:00:00', 12605, 'precio', 926, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2878, '2026-02-05 00:00:00', 0, 'precio', 927, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2879, '2026-02-05 00:00:00', 117647, 'precio', 928, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2880, '2026-02-05 00:00:00', 10168, 'precio', 929, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2881, '2026-02-05 00:00:00', 12605, 'precio', 930, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2882, '2026-02-05 00:00:00', 15126, 'precio', 931, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2883, '2026-02-05 00:00:00', 7983, 'precio', 932, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2884, '2026-02-05 00:00:00', 13865, 'precio', 933, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2885, '2026-02-05 00:00:00', 3697, 'precio', 934, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2886, '2026-02-05 00:00:00', 16806, 'precio', 935, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2887, '2026-02-05 00:00:00', 16806, 'precio', 936, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2888, '2026-02-05 00:00:00', 155462, 'precio', 937, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2889, '2026-02-05 00:00:00', 125042, 'precio', 938, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2890, '2026-02-05 00:00:00', 41848, 'precio', 939, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2891, '2026-02-05 00:00:00', 66386, 'precio', 940, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2892, '2026-02-05 00:00:00', 80672, 'precio', 941, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2893, '2026-02-05 00:00:00', 37815, 'precio', 942, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2894, '2026-02-05 00:00:00', 115966, 'precio', 943, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2895, '2026-02-05 00:00:00', 97478, 'precio', 944, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2896, '2026-02-05 00:00:00', 31932, 'precio', 945, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2897, '2026-02-05 00:00:00', 38655, 'precio', 946, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2898, '2026-02-05 00:00:00', 44537, 'precio', 947, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2899, '2026-02-05 00:00:00', 51008, 'precio', 948, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2900, '2026-02-05 00:00:00', 59663, 'precio', 949, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2901, '2026-02-05 00:00:00', 31932, 'precio', 950, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2902, '2026-02-05 00:00:00', 35294, 'precio', 951, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2903, '2026-02-05 00:00:00', 44537, 'precio', 952, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2904, '2026-02-05 00:00:00', 56302, 'precio', 953, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2905, '2026-02-05 00:00:00', 89915, 'precio', 954, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2906, '2026-02-05 00:00:00', 102521, 'precio', 955, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2907, '2026-02-05 00:00:00', 102521, 'precio', 956, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2908, '2026-02-05 00:00:00', 33109, 'precio', 957, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2909, '2026-02-05 00:00:00', 48739, 'precio', 958, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2910, '2026-02-05 00:00:00', 50252, 'precio', 959, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2911, '2026-02-05 00:00:00', 86554, 'precio', 960, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2912, '2026-02-05 00:00:00', 41764, 'precio', 961, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2913, '2026-02-05 00:00:00', 31932, 'precio', 962, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2914, '2026-02-05 00:00:00', 66386, 'precio', 963, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2915, '2026-02-05 00:00:00', 113865, 'precio', 964, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2916, '2026-02-05 00:00:00', 113865, 'precio', 965, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2917, '2026-02-05 00:00:00', 159663, 'precio', 966, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2918, '2026-02-05 00:00:00', 159663, 'precio', 967, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2919, '2026-02-05 00:00:00', 434453, 'precio', 968, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2920, '2026-02-05 00:00:00', 434453, 'precio', 969, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2921, '2026-02-05 00:00:00', 585714, 'precio', 970, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2922, '2026-02-05 00:00:00', 585714, 'precio', 971, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2923, '2026-02-05 00:00:00', 991596, 'precio', 972, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2924, '2026-02-05 00:00:00', 20168, 'precio', 973, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2925, '2026-02-05 00:00:00', 32773, 'precio', 974, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2926, '2026-02-05 00:00:00', 32773, 'precio', 975, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2927, '2026-02-05 00:00:00', 40336, 'precio', 976, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2928, '2026-02-05 00:00:00', 40336, 'precio', 977, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2929, '2026-02-05 00:00:00', 65462, 'precio', 978, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2930, '2026-02-05 00:00:00', 65462, 'precio', 979, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2931, '2026-02-05 00:00:00', 36974, 'precio', 980, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2932, '2026-02-05 00:00:00', 54621, 'precio', 981, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2933, '2026-02-05 00:00:00', 110924, 'precio', 982, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2934, '2026-02-05 00:00:00', 89915, 'precio', 983, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2935, '2026-02-05 00:00:00', 33613, 'precio', 984, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2936, '2026-02-05 00:00:00', 49579, 'precio', 985, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2937, '2026-02-05 00:00:00', 47899, 'precio', 986, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2938, '2026-02-05 00:00:00', 50420, 'precio', 987, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2939, '2026-02-05 00:00:00', 29411, 'precio', 988, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2940, '2026-02-05 00:00:00', 39495, 'precio', 989, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2941, '2026-02-05 00:00:00', 135294, 'precio', 990, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2942, '2026-02-05 00:00:00', 84033, 'precio', 991, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2943, '2026-02-05 00:00:00', 83193, 'precio', 992, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2944, '2026-02-05 00:00:00', 108403, 'precio', 993, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2945, '2026-02-05 00:00:00', 94117, 'precio', 994, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2946, '2026-02-05 00:00:00', 53781, 'precio', 995, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2947, '2026-02-05 00:00:00', 88235, 'precio', 996, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2948, '2026-02-05 00:00:00', 108403, 'precio', 997, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2949, '2026-02-05 00:00:00', 113445, 'precio', 998, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2950, '2026-02-05 00:00:00', 42016, 'precio', 999, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2951, '2026-02-05 00:00:00', 83193, 'precio', 1000, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2952, '2026-02-05 00:00:00', 38235, 'precio', 1001, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2953, '2026-02-05 00:00:00', 40336, 'precio', 1002, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2954, '2026-02-05 00:00:00', 64705, 'precio', 1003, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2955, '2026-02-05 00:00:00', 47899, 'precio', 1004, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2956, '2026-02-05 00:00:00', 38655, 'precio', 1005, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2957, '2026-02-05 00:00:00', 47899, 'precio', 1006, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2958, '2026-02-05 00:00:00', 64705, 'precio', 1007, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2959, '2026-02-05 00:00:00', 88235, 'precio', 1008, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL);
INSERT INTO `inv_values` (`id`, `date`, `values`, `type`, `itemId`, `warehouseId`, `label`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2960, '2026-02-05 00:00:00', 30252, 'precio', 1009, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2961, '2026-02-05 00:00:00', 142857, 'precio', 1010, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2962, '2026-02-05 00:00:00', 42857, 'precio', 1011, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2963, '2026-02-05 00:00:00', 108403, 'precio', 1012, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2964, '2026-02-05 00:00:00', 78151, 'precio', 1013, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2965, '2026-02-05 00:00:00', 146218, 'precio', 1014, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2966, '2026-02-05 00:00:00', 54621, 'precio', 1015, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2967, '2026-02-05 00:00:00', 44117, 'precio', 1016, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2968, '2026-02-05 00:00:00', 107563, 'precio', 1017, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2969, '2026-02-05 00:00:00', 46218, 'precio', 1018, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2970, '2026-02-05 00:00:00', 103361, 'precio', 1019, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2971, '2026-02-05 00:00:00', 75630, 'precio', 1020, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2972, '2026-02-05 00:00:00', 68907, 'precio', 1021, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2973, '2026-02-05 00:00:00', 47058, 'precio', 1022, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2974, '2026-02-05 00:00:00', 19327, 'precio', 1023, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2975, '2026-02-05 00:00:00', 52941, 'precio', 1024, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2976, '2026-02-05 00:00:00', 87394, 'precio', 1025, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2977, '2026-02-05 00:00:00', 98319, 'precio', 1026, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2978, '2026-02-05 00:00:00', 110084, 'precio', 1027, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2979, '2026-02-05 00:00:00', 131932, 'precio', 1028, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2980, '2026-02-05 00:00:00', 110084, 'precio', 1029, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2981, '2026-02-05 00:00:00', 138655, 'precio', 1030, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2982, '2026-02-05 00:00:00', 210084, 'precio', 1031, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2983, '2026-02-05 00:00:00', 759663, 'precio', 1032, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2984, '2026-02-05 00:00:00', 44537, 'precio', 1033, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2985, '2026-02-05 00:00:00', 62184, 'precio', 1034, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2986, '2026-02-05 00:00:00', 136134, 'precio', 1035, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(2987, '2026-02-05 00:00:00', 76470, 'precio', 1036, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(3011, '2026-02-05 00:00:00', 138655, 'precio', 1060, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(3012, '2026-02-05 00:00:00', 198319, 'precio', 1061, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(3013, '2026-02-05 00:00:00', 178151, 'precio', 1062, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(3014, '2026-02-05 00:00:00', 203361, 'precio', 1063, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(3015, '2026-02-05 00:00:00', 1016806, 'precio', 1064, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(3016, '2026-02-05 00:00:00', 747899, 'precio', 1065, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(3017, '2026-02-05 00:00:00', 1142857, 'precio', 1066, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(3018, '2026-02-05 00:00:00', 78151, 'precio', 1067, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(3019, '2026-02-05 00:00:00', 78151, 'precio', 1068, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(3020, '2026-02-05 00:00:00', 119327, 'precio', 1069, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(3021, '2026-02-05 00:00:00', 222689, 'precio', 1070, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(3022, '2026-02-05 00:00:00', 35294, 'precio', 1071, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(3023, '2026-02-05 00:00:00', 36974, 'precio', 1072, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(3024, '2026-02-05 00:00:00', 46218, 'precio', 1073, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(3025, '2026-02-05 00:00:00', 154621, 'precio', 1074, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(3026, '2026-02-05 00:00:00', 169747, 'precio', 1075, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(3027, '2026-02-05 00:00:00', 176470, 'precio', 1076, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(3028, '2026-02-05 00:00:00', 100000, 'precio', 1077, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(3029, '2026-02-05 00:00:00', 78991, 'precio', 1078, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(3030, '2026-02-05 00:00:00', 1764705, 'precio', 1079, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(3031, '2026-02-05 00:00:00', 187394, 'precio', 1080, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(3032, '2026-02-05 00:00:00', 61344, 'precio', 1081, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(3033, '2026-02-05 00:00:00', 121848, 'precio', 1082, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(3034, '2026-02-05 00:00:00', 39495, 'precio', 1083, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(3036, '2026-02-05 00:00:00', 2504201, 'precio', 1085, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(3037, '2026-02-05 00:00:00', 295798, 'precio', 1086, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(3038, '2026-02-05 00:00:00', 295798, 'precio', 1087, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(3039, '2026-02-05 00:00:00', 22689, 'precio', 1088, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(3040, '2026-02-05 00:00:00', 26470, 'precio', 1089, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(3041, '2026-02-05 00:00:00', 31932, 'precio', 1090, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(3042, '2026-02-05 00:00:00', 59663, 'precio', 1091, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(3043, '2026-02-05 00:00:00', 31932, 'precio', 1092, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(3044, '2026-02-05 00:00:00', 104201, 'precio', 1093, 1, 'Precio Base', '2026-02-05 00:00:00', '2026-02-05 00:00:00', NULL),
(3749, '2026-02-13 19:22:55', 20900, 'costo', 1094, 0, 'Costo Inicial', '2026-02-13 19:22:55', '2026-02-13 19:22:55', NULL),
(3750, '2026-02-13 19:22:55', 25900, 'costo', 1094, 0, 'Costo', '2026-02-13 19:22:55', '2026-02-13 19:22:55', NULL),
(3751, '2026-02-13 19:22:56', 31900, 'precio', 1094, 0, 'Precio Base', '2026-02-13 19:22:56', '2026-02-13 19:22:56', NULL),
(3752, '2026-02-13 19:22:56', 36900, 'precio', 1094, 0, 'Precio Regular', '2026-02-13 19:22:56', '2026-02-13 19:22:56', NULL),
(3753, '2026-02-13 19:22:56', 45900, 'precio', 1094, 0, 'Precio Crédito', '2026-02-13 19:22:56', '2026-02-13 19:22:56', NULL),
(3754, '2026-02-13 20:34:36', 20900, 'costo', 1095, 0, 'Costo Inicial', '2026-02-13 20:34:36', '2026-02-13 20:34:36', NULL),
(3755, '2026-02-13 20:34:36', 25900, 'costo', 1095, 0, 'Costo', '2026-02-13 20:34:36', '2026-02-13 20:34:36', NULL),
(3756, '2026-02-13 20:34:36', 31900, 'precio', 1095, 0, 'Precio Base', '2026-02-13 20:34:36', '2026-02-13 20:34:36', NULL),
(3757, '2026-02-13 20:34:37', 36900, 'precio', 1095, 0, 'Precio Regular', '2026-02-13 20:34:37', '2026-02-13 20:34:37', NULL),
(3758, '2026-02-13 20:34:37', 45900, 'precio', 1095, 0, 'Precio Crédito', '2026-02-13 20:34:37', '2026-02-13 20:34:37', NULL),
(3759, '2026-02-16 14:32:26', 9500, 'costo', 1096, 0, 'Costo Inicial', '2026-02-16 14:32:26', '2026-02-16 14:32:26', NULL),
(3760, '2026-02-16 14:32:26', 11400, 'costo', 1096, 0, 'Costo', '2026-02-16 14:32:26', '2026-02-16 14:32:26', NULL),
(3761, '2026-02-16 14:32:26', 12900, 'precio', 1096, 0, 'Precio Base', '2026-02-16 14:32:26', '2026-02-16 14:32:26', NULL),
(3762, '2026-02-16 14:32:26', 14500, 'precio', 1096, 0, 'Precio Regular', '2026-02-16 14:32:26', '2026-02-16 14:32:26', NULL),
(3763, '2026-02-16 14:32:26', 16900, 'precio', 1096, 0, 'Precio Crédito', '2026-02-16 14:32:26', '2026-02-16 14:32:26', NULL),
(3764, '2026-02-16 18:01:31', 600, 'costo', 1097, 0, 'Costo Inicial', '2026-02-16 18:01:31', '2026-02-16 18:01:31', NULL),
(3765, '2026-02-16 18:01:31', 750, 'costo', 1097, 0, 'Costo', '2026-02-16 18:01:31', '2026-02-16 18:01:31', NULL),
(3766, '2026-02-16 18:01:31', 850, 'precio', 1097, 0, 'Precio Base', '2026-02-16 18:01:31', '2026-02-16 18:01:31', NULL),
(3767, '2026-02-16 18:01:32', 1200, 'precio', 1097, 0, 'Precio Regular', '2026-02-16 18:01:32', '2026-02-16 18:01:32', NULL),
(3768, '2026-02-16 18:01:32', 1500, 'precio', 1097, 0, 'Precio Crédito', '2026-02-16 18:01:32', '2026-02-16 18:01:32', NULL),
(3769, '2026-02-16 19:01:51', 7500, 'costo', 1098, 0, 'Costo Inicial', '2026-02-16 19:01:51', '2026-02-16 19:01:51', NULL),
(3770, '2026-02-16 19:01:51', 8100, 'costo', 1098, 0, 'Costo', '2026-02-16 19:01:51', '2026-02-16 19:01:51', NULL),
(3771, '2026-02-16 19:01:51', 8900, 'precio', 1098, 0, 'Precio Base', '2026-02-16 19:01:51', '2026-02-16 19:01:51', NULL),
(3772, '2026-02-16 19:01:51', 9600, 'precio', 1098, 0, 'Precio Regular', '2026-02-16 19:01:51', '2026-02-16 19:01:51', NULL),
(3773, '2026-02-16 19:01:52', 10000, 'precio', 1098, 0, 'Precio Crédito', '2026-02-16 19:01:52', '2026-02-16 19:01:52', NULL),
(3774, '2026-02-16 19:04:23', 35600, 'costo', 1099, 0, 'Costo Inicial', '2026-02-16 19:04:23', '2026-02-16 19:04:23', NULL),
(3775, '2026-02-16 19:04:23', 46800, 'costo', 1099, 0, 'Costo', '2026-02-16 19:04:23', '2026-02-16 19:04:23', NULL),
(3776, '2026-02-16 19:04:24', 52300, 'precio', 1099, 0, 'Precio Base', '2026-02-16 19:04:24', '2026-02-16 19:04:24', NULL),
(3777, '2026-02-16 19:04:24', 59900, 'precio', 1099, 0, 'Precio Regular', '2026-02-16 19:04:24', '2026-02-16 19:04:24', NULL),
(3778, '2026-02-16 19:04:24', 63600, 'precio', 1099, 0, 'Precio Crédito', '2026-02-16 19:04:24', '2026-02-16 19:04:24', NULL),
(3779, '2026-02-16 19:38:41', 1, 'costo', 1100, 0, 'Costo Inicial', '2026-02-16 19:38:41', '2026-02-16 19:38:41', NULL),
(3780, '2026-02-16 19:38:41', 1, 'costo', 1100, 0, 'Costo', '2026-02-16 19:38:41', '2026-02-16 19:38:41', NULL),
(3781, '2026-02-16 19:38:42', 1, 'precio', 1100, 0, 'Precio Base', '2026-02-16 19:38:42', '2026-02-16 19:38:42', NULL),
(3782, '2026-02-16 19:38:42', 1, 'precio', 1100, 0, 'Precio Regular', '2026-02-16 19:38:42', '2026-02-16 19:38:42', NULL),
(3783, '2026-02-16 19:38:42', 1, 'precio', 1100, 0, 'Precio Crédito', '2026-02-16 19:38:42', '2026-02-16 19:38:42', NULL),
(3784, '2026-02-17 17:42:46', 9600, 'costo', 1101, 0, 'Costo Inicial', '2026-02-17 17:42:46', '2026-02-17 17:42:46', NULL),
(3785, '2026-02-17 17:42:46', 10500, 'costo', 1101, 0, 'Costo', '2026-02-17 17:42:46', '2026-02-17 17:42:46', NULL),
(3786, '2026-02-17 17:42:47', 12500, 'precio', 1101, 0, 'Precio Base', '2026-02-17 17:42:47', '2026-02-17 17:42:47', NULL),
(3787, '2026-02-17 17:42:47', 14500, 'precio', 1101, 0, 'Precio Regular', '2026-02-17 17:42:47', '2026-02-17 17:42:47', NULL),
(3788, '2026-02-17 17:42:47', 16500, 'precio', 1101, 0, 'Precio Crédito', '2026-02-17 17:42:47', '2026-02-17 17:42:47', NULL),
(3789, '2026-02-17 17:47:54', 6900, 'costo', 1102, 0, 'Costo Inicial', '2026-02-17 17:47:54', '2026-02-17 17:47:54', NULL),
(3790, '2026-02-17 17:47:54', 8900, 'costo', 1102, 0, 'Costo', '2026-02-17 17:47:54', '2026-02-17 17:47:54', NULL),
(3791, '2026-02-17 17:47:54', 10900, 'precio', 1102, 0, 'Precio Base', '2026-02-17 17:47:54', '2026-02-17 17:47:54', NULL),
(3792, '2026-02-17 17:47:55', 12900, 'precio', 1102, 0, 'Precio Regular', '2026-02-17 17:47:55', '2026-02-17 17:47:55', NULL),
(3793, '2026-02-17 17:47:55', 14900, 'precio', 1102, 0, 'Precio Crédito', '2026-02-17 17:47:55', '2026-02-17 17:47:55', NULL),
(3794, '2026-02-17 19:20:10', 9600, 'costo', 1103, 0, 'Costo Inicial', '2026-02-17 19:20:10', '2026-02-17 19:20:10', NULL),
(3795, '2026-02-17 19:20:10', 4500, 'costo', 1103, 0, 'Costo', '2026-02-17 19:20:10', '2026-02-17 19:20:10', NULL),
(3796, '2026-02-17 19:20:10', 5420, 'precio', 1103, 0, 'Precio Base', '2026-02-17 19:20:10', '2026-02-17 19:20:10', NULL),
(3797, '2026-02-17 19:20:10', 65000, 'precio', 1103, 0, 'Precio Regular', '2026-02-17 19:20:10', '2026-02-17 19:20:10', NULL),
(3798, '2026-02-17 19:20:10', 114000, 'precio', 1103, 0, 'Precio Crédito', '2026-02-17 19:20:10', '2026-02-17 19:20:10', NULL),
(3799, '2026-02-17 19:23:38', 28900, 'costo', 1104, 0, 'Costo Inicial', '2026-02-17 19:23:38', '2026-02-17 19:23:38', NULL),
(3800, '2026-02-17 19:23:38', 35900, 'costo', 1104, 0, 'Costo', '2026-02-17 19:23:38', '2026-02-17 19:23:38', NULL),
(3801, '2026-02-17 19:23:38', 47900, 'precio', 1104, 0, 'Precio Base', '2026-02-17 19:23:38', '2026-02-17 19:23:38', NULL),
(3802, '2026-02-17 19:23:38', 56900, 'precio', 1104, 0, 'Precio Regular', '2026-02-17 19:23:38', '2026-02-17 19:23:38', NULL),
(3803, '2026-02-17 19:23:39', 68900, 'precio', 1104, 0, 'Precio Crédito', '2026-02-17 19:23:39', '2026-02-17 19:23:39', NULL),
(3804, '2026-02-17 19:56:07', 97900, 'costo', 1105, 0, 'Costo Inicial', '2026-02-17 19:56:07', '2026-02-17 19:56:07', NULL),
(3805, '2026-02-17 19:56:08', 115900, 'costo', 1105, 0, 'Costo', '2026-02-17 19:56:08', '2026-02-17 19:56:08', NULL),
(3806, '2026-02-17 19:56:08', 128900, 'precio', 1105, 0, 'Precio Base', '2026-02-17 19:56:08', '2026-02-17 19:56:08', NULL),
(3807, '2026-02-17 19:56:08', 137900, 'precio', 1105, 0, 'Precio Regular', '2026-02-17 19:56:08', '2026-02-17 19:56:08', NULL),
(3808, '2026-02-17 19:56:09', 150000, 'precio', 1105, 0, 'Precio Crédito', '2026-02-17 19:56:09', '2026-02-17 19:56:09', NULL),
(3809, '2026-02-18 21:01:58', 15600, 'costo', 1106, 0, 'Costo Inicial', '2026-02-18 21:01:58', '2026-02-18 21:01:58', NULL),
(3810, '2026-02-18 21:01:58', 19900, 'costo', 1106, 0, 'Costo', '2026-02-18 21:01:58', '2026-02-18 21:01:58', NULL),
(3811, '2026-02-18 21:01:58', 24800, 'precio', 1106, 0, 'Precio Base', '2026-02-18 21:01:58', '2026-02-18 21:01:58', NULL),
(3812, '2026-02-18 21:01:58', 28900, 'precio', 1106, 0, 'Precio Regular', '2026-02-18 21:01:58', '2026-02-18 21:01:58', NULL),
(3813, '2026-02-18 21:01:59', 30900, 'precio', 1106, 0, 'Precio Crédito', '2026-02-18 21:01:59', '2026-02-18 21:01:59', NULL),
(3814, '2026-02-18 21:07:04', 17900, 'costo', 1107, 0, 'Costo Inicial', '2026-02-18 21:07:04', '2026-02-18 21:07:04', NULL),
(3815, '2026-02-18 21:07:04', 20900, 'costo', 1107, 0, 'Costo', '2026-02-18 21:07:04', '2026-02-18 21:07:04', NULL),
(3816, '2026-02-18 21:07:05', 25900, 'precio', 1107, 0, 'Precio Base', '2026-02-18 21:07:05', '2026-02-18 21:07:05', NULL),
(3817, '2026-02-18 21:07:05', 24900, 'precio', 1107, 0, 'Precio Regular', '2026-02-18 21:07:05', '2026-02-18 21:07:05', NULL),
(3818, '2026-02-18 21:07:05', 28900, 'precio', 1107, 0, 'Precio Crédito', '2026-02-18 21:07:05', '2026-02-18 21:07:05', NULL),
(3819, '2026-02-18 21:24:45', 5200, 'costo', 1108, 0, 'Costo Inicial', '2026-02-18 21:24:45', '2026-02-18 21:24:45', NULL),
(3820, '2026-02-18 21:24:45', 4500, 'costo', 1108, 0, 'Costo', '2026-02-18 21:24:45', '2026-02-18 21:24:45', NULL),
(3821, '2026-02-18 21:24:45', 6300, 'precio', 1108, 0, 'Precio Base', '2026-02-18 21:24:45', '2026-02-18 21:24:45', NULL),
(3822, '2026-02-18 21:24:46', 7800, 'precio', 1108, 0, 'Precio Regular', '2026-02-18 21:24:46', '2026-02-18 21:24:46', NULL),
(3823, '2026-02-18 21:24:46', 9410, 'precio', 1108, 0, 'Precio Crédito', '2026-02-18 21:24:46', '2026-02-18 21:24:46', NULL),
(3824, '2026-02-20 13:09:04', 17900, 'costo', 1113, 0, 'Costo Inicial', '2026-02-20 13:09:04', '2026-02-20 13:09:04', NULL),
(3825, '2026-02-20 13:09:04', 20900, 'costo', 1113, 0, 'Costo', '2026-02-20 13:09:04', '2026-02-20 13:09:04', NULL),
(3826, '2026-02-20 13:09:04', 25900, 'precio', 1113, 0, 'Precio Base', '2026-02-20 13:09:04', '2026-02-20 13:09:04', NULL),
(3827, '2026-02-20 13:09:05', 24900, 'precio', 1113, 0, 'Precio Regular', '2026-02-20 13:09:05', '2026-02-20 13:09:05', NULL),
(3828, '2026-02-20 13:09:06', 28900, 'precio', 1113, 0, 'Precio Crédito', '2026-02-20 13:09:06', '2026-02-20 13:09:06', NULL),
(3829, '2026-02-20 20:42:19', 6300, 'costo', 1114, 0, 'Costo Inicial', '2026-02-20 20:42:19', '2026-02-20 20:42:19', NULL),
(3830, '2026-02-20 20:42:26', 7900, 'costo', 1114, 0, 'Costo', '2026-02-20 20:42:26', '2026-02-20 20:42:26', NULL),
(3831, '2026-02-20 20:42:48', 9500, 'precio', 1114, 0, 'Precio Base', '2026-02-20 20:42:48', '2026-02-20 20:42:48', NULL),
(3832, '2026-02-20 20:42:58', 11800, 'precio', 1114, 0, 'Precio Regular', '2026-02-20 20:42:58', '2026-02-20 20:42:58', NULL),
(3833, '2026-02-20 20:43:06', 12900, 'precio', 1114, 0, 'Precio Crédito', '2026-02-20 20:43:06', '2026-02-20 20:43:06', NULL),
(3834, '2026-02-25 14:27:39', 15000, 'precio', 1117, NULL, 'Precio Base', '2026-02-25 14:27:39', '2026-02-25 14:27:39', NULL),
(3835, '2026-02-27 16:19:53', 10000, 'costo', 1118, 0, 'Costo Inicial', '2026-02-27 16:19:53', '2026-02-27 16:19:53', NULL),
(3836, '2026-02-27 16:19:53', 10000, 'costo', 1118, 0, 'Costo', '2026-02-27 16:19:53', '2026-02-27 16:19:53', NULL),
(3837, '2026-02-27 16:19:54', 10000, 'precio', 1118, 0, 'Precio Base', '2026-02-27 16:19:54', '2026-02-27 16:19:54', NULL),
(3838, '2026-02-27 16:19:55', 10000, 'precio', 1118, 0, 'Precio Regular', '2026-02-27 16:19:55', '2026-02-27 16:19:55', NULL),
(3839, '2026-02-27 16:19:55', 10000, 'precio', 1118, 0, 'Precio Crédito', '2026-02-27 16:19:55', '2026-02-27 16:19:55', NULL),
(3840, '2026-02-27 16:45:52', 4000, 'costo', 1119, 0, 'Costo Inicial', '2026-02-27 16:45:52', '2026-02-27 16:45:52', NULL),
(3841, '2026-02-27 16:45:53', 5060, 'costo', 1119, 0, 'Costo', '2026-02-27 16:45:53', '2026-02-27 16:45:53', NULL),
(3842, '2026-02-27 16:45:53', 5400, 'precio', 1119, 0, 'Precio Base', '2026-02-27 16:45:53', '2026-02-27 16:45:53', NULL),
(3843, '2026-02-27 16:45:53', 12300, 'precio', 1119, 0, 'Precio Regular', '2026-02-27 16:45:53', '2026-02-27 16:45:53', NULL),
(3844, '2026-02-27 16:45:53', 21300, 'precio', 1119, 0, 'Precio Crédito', '2026-02-27 16:45:53', '2026-02-27 16:45:53', NULL),
(3845, '2026-03-03 19:38:47', 8900, 'costo', 1115, 0, 'Costo Inicial', '2026-03-03 19:38:47', '2026-03-03 19:38:47', NULL),
(3846, '2026-03-03 19:38:56', 4500, 'costo', 1115, 0, 'Costo', '2026-03-03 19:38:56', '2026-03-03 19:38:56', NULL),
(3847, '2026-03-03 19:38:58', 6300, 'precio', 1115, 0, 'Precio Base', '2026-03-03 19:38:58', '2026-03-03 19:38:58', NULL),
(3848, '2026-03-03 19:39:05', 53600, 'precio', 1115, 0, 'Precio Regular', '2026-03-03 19:39:05', '2026-03-03 19:39:05', NULL),
(3849, '2026-03-03 19:39:13', 21500, 'precio', 1115, 0, 'Precio Crédito', '2026-03-03 19:39:13', '2026-03-03 19:39:13', NULL),
(3850, '2026-03-03 19:45:13', 2100, 'costo', 1111, 0, 'Costo Inicial', '2026-03-03 19:45:13', '2026-03-03 19:45:13', NULL),
(3851, '2026-03-03 19:45:21', 3600, 'costo', 1111, 0, 'Costo', '2026-03-03 19:45:21', '2026-03-03 19:45:21', NULL),
(3852, '2026-03-03 19:45:27', 4500, 'precio', 1111, 0, 'Precio Base', '2026-03-03 19:45:27', '2026-03-03 19:45:27', NULL),
(3853, '2026-03-03 19:45:33', 6300, 'precio', 1111, 0, 'Precio Regular', '2026-03-03 19:45:33', '2026-03-03 19:45:33', NULL),
(3854, '2026-03-03 19:45:42', 7000, 'precio', 1111, 0, 'Precio Crédito', '2026-03-03 19:45:42', '2026-03-03 19:45:42', NULL),
(3855, '2026-03-03 19:48:18', 9600, 'costo', 1110, 0, 'Costo Inicial', '2026-03-03 19:48:18', '2026-03-03 19:48:18', NULL),
(3856, '2026-03-03 19:48:38', 11500, 'costo', 1110, 0, 'Costo', '2026-03-03 19:48:38', '2026-03-03 19:48:38', NULL),
(3857, '2026-03-03 19:48:42', 14500, 'precio', 1110, 0, 'Precio Base', '2026-03-03 19:48:42', '2026-03-03 19:48:42', NULL),
(3858, '2026-03-03 19:48:55', 19800, 'precio', 1110, 0, 'Precio Regular', '2026-03-03 19:48:55', '2026-03-03 19:48:55', NULL),
(3859, '2026-03-03 19:49:01', 21500, 'precio', 1110, 0, 'Precio Crédito', '2026-03-03 19:49:01', '2026-03-03 19:49:01', NULL),
(3860, '2026-03-03 19:52:28', 9500, 'costo', 1109, 0, 'Costo Inicial', '2026-03-03 19:52:28', '2026-03-03 19:52:28', NULL),
(3861, '2026-03-03 19:52:39', 11800, 'costo', 1109, 0, 'Costo', '2026-03-03 19:52:39', '2026-03-03 19:52:39', NULL),
(3862, '2026-03-03 19:52:47', 12800, 'precio', 1109, 0, 'Precio Base', '2026-03-03 19:52:47', '2026-03-03 19:52:47', NULL),
(3863, '2026-03-03 19:52:52', 14700, 'precio', 1109, 0, 'Precio Regular', '2026-03-03 19:52:52', '2026-03-03 19:52:52', NULL),
(3864, '2026-03-03 19:52:58', 18999, 'precio', 1109, 0, 'Precio Crédito', '2026-03-03 19:52:58', '2026-03-03 19:52:58', NULL),
(3865, '2026-03-09 15:47:47', 1500, 'costo', 1120, 0, 'Costo Inicial', '2026-03-09 15:47:47', '2026-03-09 15:47:47', NULL),
(3866, '2026-03-09 15:47:49', 2500, 'costo', 1120, 0, 'Costo', '2026-03-09 15:47:49', '2026-03-09 15:47:49', NULL),
(3867, '2026-03-09 15:47:50', 3500, 'precio', 1120, 0, 'Precio Base', '2026-03-09 15:47:50', '2026-03-09 15:47:50', NULL),
(3868, '2026-03-09 15:47:51', 4500, 'precio', 1120, 0, 'Precio Regular', '2026-03-09 15:47:51', '2026-03-09 15:47:51', NULL),
(3869, '2026-03-09 15:47:53', 5000, 'precio', 1120, 0, 'Precio Crédito', '2026-03-09 15:47:53', '2026-03-09 15:47:53', NULL),
(3870, '2026-03-09 17:28:58', 1500, 'costo', 1121, 0, 'Costo Inicial', '2026-03-09 17:28:58', '2026-03-09 17:28:58', NULL),
(3871, '2026-03-09 17:29:00', 1500, 'costo', 1121, 0, 'Costo', '2026-03-09 17:29:00', '2026-03-09 17:29:00', NULL),
(3872, '2026-03-09 17:29:01', 1500, 'precio', 1121, 0, 'Precio Base', '2026-03-09 17:29:01', '2026-03-09 17:29:01', NULL),
(3873, '2026-03-09 17:29:03', 1500, 'precio', 1121, 0, 'Precio Regular', '2026-03-09 17:29:03', '2026-03-09 17:29:03', NULL),
(3874, '2026-03-09 17:29:04', 1500, 'precio', 1121, 0, 'Precio Crédito', '2026-03-09 17:29:04', '2026-03-09 17:29:04', NULL),
(3875, '2026-03-09 17:37:03', 1500, 'costo', 1122, 0, 'Costo Inicial', '2026-03-09 17:37:03', '2026-03-09 17:37:03', NULL),
(3876, '2026-03-09 17:37:04', 1500, 'costo', 1122, 0, 'Costo', '2026-03-09 17:37:04', '2026-03-09 17:37:04', NULL),
(3877, '2026-03-09 17:37:06', 1500, 'precio', 1122, 0, 'Precio Base', '2026-03-09 17:37:06', '2026-03-09 17:37:06', NULL),
(3878, '2026-03-09 17:37:07', 1500, 'precio', 1122, 0, 'Precio Regular', '2026-03-09 17:37:07', '2026-03-09 17:37:07', NULL),
(3879, '2026-03-09 17:37:08', 12311, 'precio', 1122, 0, 'Precio Crédito', '2026-03-09 17:37:08', '2026-03-09 17:37:08', NULL),
(3880, '2026-04-09 20:50:49', 25000, 'costo', 1123, 0, 'Costo Inicial', '2026-04-09 20:50:49', '2026-04-09 20:50:49', NULL),
(3881, '2026-04-09 20:50:49', 25000, 'costo', 1123, 0, 'Costo', '2026-04-09 20:50:49', '2026-04-09 20:50:49', NULL),
(3882, '2026-04-09 20:50:50', 25000, 'precio', 1123, 0, 'Precio Base', '2026-04-09 20:50:50', '2026-04-09 20:50:50', NULL),
(3883, '2026-04-09 20:50:50', 25000, 'precio', 1123, 0, 'Precio Regular', '2026-04-09 20:50:50', '2026-04-09 20:50:50', NULL),
(3884, '2026-04-09 20:50:51', 25000, 'precio', 1123, 0, 'Precio Crédito', '2026-04-09 20:50:51', '2026-04-09 20:50:51', NULL),
(3885, '2026-04-10 20:44:13', 1599, 'costo', 1124, 0, 'Costo Inicial', '2026-04-10 20:44:13', '2026-04-10 20:44:13', NULL),
(3886, '2026-04-10 20:44:14', 2500, 'costo', 1124, 0, 'Costo', '2026-04-10 20:44:14', '2026-04-10 20:44:14', NULL),
(3887, '2026-04-10 20:44:14', 4599, 'precio', 1124, 0, 'Precio Base', '2026-04-10 20:44:14', '2026-04-10 20:44:14', NULL),
(3888, '2026-04-10 20:44:14', 5499, 'precio', 1124, 0, 'Precio Regular', '2026-04-10 20:44:14', '2026-04-10 20:44:14', NULL),
(3889, '2026-04-10 20:44:14', 29319, 'precio', 1124, 0, 'Precio Crédito', '2026-04-10 20:44:14', '2026-04-10 20:44:14', NULL),
(3890, '2026-04-10 20:44:15', 45911, 'costo', 1124, 0, 'Precio unitario x caja', '2026-04-10 20:44:15', '2026-04-10 20:44:15', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_wordpress_configs`
--

CREATE TABLE `inv_wordpress_configs` (
  `id` bigint UNSIGNED NOT NULL,
  `wp_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'URL de la tienda WordPress',
  `wp_user` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Usuario de la API',
  `wp_password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Application Password de WordPress',
  `use_wp_load` tinyint(1) NOT NULL DEFAULT '0' COMMENT '¿Usar carga interna de wp-load.php?',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Estado de la integración',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `inv_wordpress_configs`
--

INSERT INTO `inv_wordpress_configs` (`id`, `wp_url`, `wp_user`, `wp_password`, `use_wp_load`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'https://www.fervicom.com', 'fervicom', 'KNRJ kYEm Bau2 KSG7 CEHJ AhqC', 1, 1, '2026-03-05 11:15:28', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inv_wordpress_images`
--

CREATE TABLE `inv_wordpress_images` (
  `id` bigint UNSIGNED NOT NULL,
  `image_id` int UNSIGNED NOT NULL COMMENT 'ID de la imagen en inv_image_gallery',
  `itemId` int UNSIGNED NOT NULL COMMENT 'ID del producto en inv_items',
  `wp_media_id` bigint UNSIGNED DEFAULT NULL COMMENT 'ID de la imagen en WordPress',
  `sync_to_wp` tinyint(1) NOT NULL DEFAULT '0' COMMENT '¿Sincronizar esta imagen con WordPress?',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2025_11_04_144921_create_ven_delivery_types_table', 1),
(2, '2025_11_04_144922_create_vnt_companies_table', 1),
(3, '2025_11_04_144922_create_vnt_contacts_table', 1),
(4, '2025_11_04_144923_create_vnt_detail_petty_cash_table', 1),
(5, '2025_11_04_144923_create_vnt_detail_quotes_table', 1),
(6, '2025_11_04_144924_create_vnt_detail_reconciliations_table', 1),
(7, '2025_11_04_144924_create_vnt_invoice_payments_table', 1),
(8, '2025_11_04_144925_create_vnt_invoices_table', 1),
(9, '2025_11_04_144925_create_vnt_invoices_xsales_table', 1),
(10, '2025_11_04_144926_create_vnt_method_payments_table', 1),
(11, '2025_11_04_144926_create_vnt_petty_cash_table', 1),
(12, '2025_11_04_144927_create_vnt_quotes_table', 1),
(13, '2025_11_04_144927_create_vnt_reasons_petty_cash_table', 1),
(14, '2025_11_04_144928_create_vnt_reconciliations_table', 1),
(15, '2025_11_04_144928_create_vnt_terms_table', 1),
(16, '2025_11_04_144929_create_vnt_warehouses_table', 1),
(17, '2025_11_04_144906_create_inv_applications_table', 1),
(18, '2025_11_04_144907_create_inv_categories_table', 1),
(19, '2025_11_04_144907_create_inv_command_table', 1),
(20, '2025_11_04_144908_create_inv_detail_inv_adjustments_table', 1),
(21, '2025_11_04_144908_create_inv_detail_inventory_table', 1),
(22, '2025_11_04_144909_create_inv_detail_remissions_table', 1),
(23, '2025_11_04_144909_create_inv_detail_transfer_requests_table', 1),
(24, '2025_11_04_144910_create_inv_detail_transfers_table', 1),
(25, '2025_11_04_144910_create_inv_image_gallery_table', 1),
(26, '2025_11_04_144911_create_inv_inventory_adjustments_table', 1),
(27, '2025_11_04_144911_create_inv_inventory_count_table', 1),
(28, '2025_11_04_144912_create_inv_item_applications_table', 1),
(29, '2025_11_04_144912_create_inv_item_brand_table', 1),
(30, '2025_11_04_144913_create_inv_item_house_table', 1),
(31, '2025_11_04_144913_create_inv_items_table', 1),
(32, '2025_11_04_144914_create_inv_items_locations_table', 1),
(33, '2025_11_04_144914_create_inv_items_store_table', 1),
(34, '2025_11_04_144915_create_inv_purchase_order_details_table', 1),
(35, '2025_11_04_144915_create_inv_purchase_orders_table', 1),
(36, '2025_11_04_144916_create_inv_purchase_request_details_table', 1),
(37, '2025_11_04_144916_create_inv_purchase_requests_table', 1),
(38, '2025_11_04_144917_create_inv_reasons_table', 1),
(39, '2025_11_04_144917_create_inv_remissions_table', 1),
(40, '2025_11_04_144918_create_inv_seriales_table', 1),
(41, '2025_11_04_144918_create_inv_status_table', 1),
(42, '2025_11_04_144919_create_inv_store_table', 1),
(43, '2025_11_04_144919_create_inv_transfer_requests_table', 1),
(44, '2025_11_04_144920_create_inv_transfers_table', 1),
(45, '2025_11_04_144920_create_inv_unit_measurements_table', 1),
(46, '2025_11_04_144921_create_inv_values_table', 1),
(47, '2025_11_05_205701_create_cfg_positions_table', 1),
(48, '2025_11_06_211500_fix_position_foreign_key_in_vnt_contacts', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prd_data_process_order`
--

CREATE TABLE `prd_data_process_order` (
  `id` int NOT NULL,
  `production_order_id` int NOT NULL DEFAULT '0',
  `field_processId` int NOT NULL DEFAULT '0',
  `field_value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `prd_data_process_order`
--

INSERT INTO `prd_data_process_order` (`id`, `production_order_id`, `field_processId`, `field_value`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, 11, '2', '2026-03-03 14:10:52', '2026-03-03 14:10:52', NULL),
(2, 2, 12, 'Papel sin impresion', '2026-03-03 14:10:53', '2026-03-03 14:10:53', NULL),
(3, 2, 13, '2', '2026-03-03 14:10:53', '2026-03-03 14:10:53', NULL),
(4, 2, 14, '22', '2026-03-03 14:10:53', '2026-03-03 14:10:53', NULL),
(5, 2, 16, '22', '2026-03-03 14:10:54', '2026-03-03 14:10:54', NULL),
(6, 2, 1, 'dos', '2026-03-03 14:12:28', '2026-03-03 14:12:28', NULL),
(7, 2, 2, '2x2', '2026-03-03 14:12:28', '2026-03-03 14:12:28', NULL),
(8, 2, 3, 'a4', '2026-03-03 14:12:29', '2026-03-03 14:12:29', NULL),
(9, 2, 4, 'Gto', '2026-03-03 14:12:29', '2026-03-03 14:12:29', NULL),
(10, 2, 5, 'Media', '2026-03-03 14:12:30', '2026-03-03 14:12:30', NULL),
(11, 2, 6, 'Medio', '2026-03-03 14:12:30', '2026-03-03 14:12:30', NULL),
(12, 2, 7, '2', '2026-03-03 14:12:30', '2026-03-03 14:12:30', NULL),
(13, 2, 8, 'Lista de usuarios con perfil deseñador', '2026-03-03 14:12:30', '2026-03-03 14:12:30', NULL),
(14, 2, 9, 'prueba', '2026-03-03 14:12:30', '2026-03-03 14:12:30', NULL),
(15, 2, 10, '2026-03-04', '2026-03-03 14:12:31', '2026-03-03 14:12:31', NULL),
(16, 4, 11, '4', '2026-03-03 14:16:36', '2026-03-03 14:16:36', NULL),
(17, 4, 12, 'Papel sin impresion', '2026-03-03 14:16:37', '2026-03-03 14:16:37', NULL),
(18, 4, 13, '5', '2026-03-03 14:16:37', '2026-03-03 14:16:37', NULL),
(19, 4, 14, '12', '2026-03-03 14:16:37', '2026-03-03 14:16:37', NULL),
(20, 4, 16, '12', '2026-03-03 14:16:37', '2026-03-03 14:16:37', NULL),
(21, 4, 1, 'uno', '2026-03-03 14:17:21', '2026-03-03 14:17:21', NULL),
(22, 4, 2, '4', '2026-03-03 14:17:21', '2026-03-03 14:17:21', NULL),
(23, 4, 3, '3', '2026-03-03 14:17:21', '2026-03-03 14:17:21', NULL),
(24, 4, 4, 'Gto', '2026-03-03 14:17:21', '2026-03-03 14:17:21', NULL),
(25, 4, 5, 'Media', '2026-03-03 14:17:22', '2026-03-03 14:17:22', NULL),
(26, 4, 6, 'Octavo', '2026-03-03 14:17:22', '2026-03-03 14:17:22', NULL),
(27, 4, 7, '4', '2026-03-03 14:17:22', '2026-03-03 14:17:22', NULL),
(28, 4, 8, 'Lista de usuarios con perfil deseñador', '2026-03-03 14:17:23', '2026-03-03 14:17:23', NULL),
(29, 4, 9, '12', '2026-03-03 14:17:23', '2026-03-03 14:17:23', NULL),
(30, 4, 10, '2026-03-03', '2026-03-03 14:17:23', '2026-03-03 14:17:23', NULL),
(31, 3, 11, '4', '2026-03-03 15:25:06', '2026-03-03 15:25:06', NULL),
(32, 3, 12, 'servicio de corte', '2026-03-03 15:25:07', '2026-03-03 15:25:07', NULL),
(33, 3, 13, '23', '2026-03-03 15:25:07', '2026-03-03 15:25:07', NULL),
(34, 3, 14, '2', '2026-03-03 15:25:08', '2026-03-03 15:25:08', NULL),
(35, 3, 16, '2', '2026-03-03 15:25:08', '2026-03-03 15:25:08', NULL),
(36, 3, 1, 'dos', '2026-03-03 15:49:45', '2026-03-03 15:49:45', NULL),
(37, 3, 2, 'A4', '2026-03-03 15:49:50', '2026-03-03 15:49:50', NULL),
(38, 3, 3, 'A4', '2026-03-03 15:49:56', '2026-03-03 15:49:56', NULL),
(39, 3, 4, 'Gto', '2026-03-03 15:49:57', '2026-03-03 15:49:57', NULL),
(40, 3, 5, 'Media', '2026-03-03 15:49:57', '2026-03-03 15:49:57', NULL),
(41, 3, 6, 'Cuarto', '2026-03-03 15:49:58', '2026-03-03 15:49:58', NULL),
(42, 3, 7, '21', '2026-03-03 15:50:00', '2026-03-03 15:50:00', NULL),
(43, 3, 8, 'Lista de usuarios con perfil deseñador', '2026-03-03 15:50:01', '2026-03-03 15:50:01', NULL),
(44, 3, 9, 'Prueba', '2026-03-03 15:50:05', '2026-03-03 15:50:05', NULL),
(45, 3, 10, '2026-03-04', '2026-03-03 15:50:06', '2026-03-03 15:50:06', NULL),
(46, 1, 1, 'uno', '2026-03-03 21:48:17', '2026-03-03 21:48:17', NULL),
(47, 1, 2, '2', '2026-03-03 21:48:17', '2026-03-03 21:48:17', NULL),
(48, 1, 3, '3', '2026-03-03 21:48:17', '2026-03-03 21:48:17', NULL),
(49, 1, 4, 'Gto', '2026-03-03 21:48:17', '2026-03-03 21:48:17', NULL),
(50, 1, 5, 'Media', '2026-03-03 21:48:17', '2026-03-03 21:48:17', NULL),
(51, 1, 6, 'Cuarto', '2026-03-03 21:48:18', '2026-03-03 21:48:18', NULL),
(52, 1, 7, '2', '2026-03-03 21:48:18', '2026-03-03 21:48:18', NULL),
(53, 1, 8, 'Lista de usuarios con perfil deseñador', '2026-03-03 21:48:18', '2026-03-03 21:48:18', NULL),
(54, 1, 9, '1231', '2026-03-03 21:48:18', '2026-03-03 21:48:18', NULL),
(55, 1, 10, '2026-03-04', '2026-03-03 21:48:19', '2026-03-03 21:48:19', NULL),
(56, 3, 17, 'plastificado', '2026-03-09 16:18:15', '2026-03-09 16:18:15', NULL),
(57, 3, 18, 'Brillante', '2026-03-09 16:18:16', '2026-03-09 16:18:16', NULL),
(58, 3, 20, '25', '2026-03-09 16:18:17', '2026-03-09 16:18:17', NULL),
(59, 3, 21, '32', '2026-03-09 16:18:17', '2026-03-09 16:18:17', NULL),
(60, 3, 22, 'prueba', '2026-03-09 16:18:18', '2026-03-09 16:18:18', NULL),
(61, 3, 23, 'prueba', '2026-03-09 16:18:18', '2026-03-09 16:18:18', NULL),
(62, 6, 11, '2', '2026-03-09 17:55:46', '2026-03-09 17:55:46', NULL),
(63, 6, 12, 'Papel sin impresion', '2026-03-09 17:55:47', '2026-03-09 17:55:47', NULL),
(64, 6, 13, '2', '2026-03-09 17:55:47', '2026-03-09 17:55:47', NULL),
(65, 6, 14, '2', '2026-03-09 17:55:48', '2026-03-09 17:55:48', NULL),
(66, 6, 16, '2', '2026-03-09 17:55:48', '2026-03-09 17:55:48', NULL),
(67, 6, 1, 'dos', '2026-03-09 19:36:18', '2026-03-09 19:36:18', NULL),
(68, 6, 2, '2x2', '2026-03-09 19:36:19', '2026-03-09 19:36:19', NULL),
(69, 6, 3, '3x4', '2026-03-09 19:36:19', '2026-03-09 19:36:19', NULL),
(70, 6, 4, 'tok', '2026-03-09 19:36:19', '2026-03-09 19:36:19', NULL),
(71, 6, 5, 'Media', '2026-03-09 19:36:20', '2026-03-09 19:36:20', NULL),
(72, 6, 6, 'Cuarto', '2026-03-09 19:36:20', '2026-03-09 19:36:20', NULL),
(73, 6, 7, '4', '2026-03-09 19:36:20', '2026-03-09 19:36:20', NULL),
(74, 6, 8, 'Lista de usuarios con perfil deseñador', '2026-03-09 19:36:21', '2026-03-09 19:36:21', NULL),
(75, 6, 9, 'prueba', '2026-03-09 19:36:21', '2026-03-09 19:36:21', NULL),
(76, 6, 10, '2026-03-10', '2026-03-09 19:36:21', '2026-03-09 19:36:21', NULL),
(77, 6, 17, 'plastificado', '2026-03-09 19:43:06', '2026-03-09 19:43:06', NULL),
(78, 6, 18, 'Holografico', '2026-03-09 19:43:06', '2026-03-09 19:43:06', NULL),
(79, 6, 20, '2x4', '2026-03-09 19:43:07', '2026-03-09 19:43:07', NULL),
(80, 6, 21, '2', '2026-03-09 19:43:07', '2026-03-09 19:43:07', NULL),
(81, 6, 22, 'prueba', '2026-03-09 19:43:07', '2026-03-09 19:43:07', NULL),
(82, 6, 23, 'carlos', '2026-03-09 19:43:08', '2026-03-09 19:43:08', NULL),
(83, 6, 17, 'plastificado', '2026-03-09 19:43:53', '2026-03-09 19:43:53', NULL),
(84, 6, 18, 'Holografico', '2026-03-09 19:43:53', '2026-03-09 19:43:53', NULL),
(85, 6, 20, '2x4', '2026-03-09 19:43:54', '2026-03-09 19:43:54', NULL),
(86, 6, 21, '2', '2026-03-09 19:43:54', '2026-03-09 19:43:54', NULL),
(87, 6, 22, 'prueba', '2026-03-09 19:43:54', '2026-03-09 19:43:54', NULL),
(88, 6, 23, 'carlos', '2026-03-09 19:43:55', '2026-03-09 19:43:55', NULL),
(89, 6, 17, 'plastificado', '2026-03-09 19:47:47', '2026-03-09 19:47:47', NULL),
(90, 6, 18, 'Holografico', '2026-03-09 19:47:47', '2026-03-09 19:47:47', NULL),
(91, 6, 20, '2x4', '2026-03-09 19:47:47', '2026-03-09 19:47:47', NULL),
(92, 6, 21, '2', '2026-03-09 19:47:48', '2026-03-09 19:47:48', NULL),
(93, 6, 22, 'prueba', '2026-03-09 19:47:48', '2026-03-09 19:47:48', NULL),
(94, 6, 23, 'carlos', '2026-03-09 19:47:49', '2026-03-09 19:47:49', NULL),
(95, 6, 17, 'plastificado', '2026-03-09 19:49:15', '2026-03-09 19:49:15', NULL),
(96, 6, 18, 'Brillante', '2026-03-09 19:49:16', '2026-03-09 19:49:16', NULL),
(97, 6, 20, '2x2', '2026-03-09 19:49:16', '2026-03-09 19:49:16', NULL),
(98, 6, 21, '22', '2026-03-09 19:49:16', '2026-03-09 19:49:16', NULL),
(99, 6, 22, 'prueba', '2026-03-09 19:49:17', '2026-03-09 19:49:17', NULL),
(100, 6, 23, 'prueba', '2026-03-09 19:49:17', '2026-03-09 19:49:17', NULL),
(101, 6, 17, 'plastificado', '2026-03-09 19:52:07', '2026-03-09 19:52:07', NULL),
(102, 6, 18, 'Brillante', '2026-03-09 19:52:07', '2026-03-09 19:52:07', NULL),
(103, 6, 20, '2x2', '2026-03-09 19:52:08', '2026-03-09 19:52:08', NULL),
(104, 6, 21, '22', '2026-03-09 19:52:08', '2026-03-09 19:52:08', NULL),
(105, 6, 22, 'prueba', '2026-03-09 19:52:08', '2026-03-09 19:52:08', NULL),
(106, 6, 23, 'prueba', '2026-03-09 19:52:09', '2026-03-09 19:52:09', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prd_fields_process`
--

CREATE TABLE `prd_fields_process` (
  `id` int NOT NULL,
  `processId` int NOT NULL DEFAULT '0',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Nombre',
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `type` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `class` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `status` tinyint DEFAULT '1',
  `parent_field` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'nombre del campo que controla la visibilidad',
  `parent_value` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'valor que debe tener ese campo para mostrar este',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `prd_fields_process`
--

INSERT INTO `prd_fields_process` (`id`, `processId`, `name`, `label`, `type`, `class`, `options`, `status`, `parent_field`, `parent_value`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, 'montaje', 'Montaje', 'select', NULL, 'uno, dos, tres, cuatro', 1, NULL, '', '2026-01-09 22:11:00', NULL, NULL),
(2, 2, 'tamanio_disenio', 'Tamaño diseño', 'text', NULL, '<text>', 1, NULL, '', '2026-01-14 15:19:09', NULL, NULL),
(3, 2, 'tamanio_papel', 'Tamaño Papel', 'text', NULL, '<text>', 1, NULL, '', '2026-01-14 15:19:09', NULL, NULL),
(4, 2, 'maquina', 'Maquina', 'select', NULL, 'tok, Gto, Medio, Pliego', 1, NULL, '', '2026-01-09 22:11:00', NULL, NULL),
(5, 2, 'plancha_papel', 'Plancha Papel', 'select', NULL, 'Media, Completa', 1, NULL, '', '2026-01-09 22:11:00', NULL, NULL),
(6, 2, 'plancha_metal', 'Plancha Metalica', 'select', NULL, 'Octavo, Cuarto, Medio, Pliego', 1, NULL, '', '2026-01-09 22:11:00', NULL, NULL),
(7, 2, 'cant_planchas', 'Cantidad de planchas', 'text', NULL, '<text>', 1, NULL, '', '2026-01-14 15:19:09', NULL, NULL),
(8, 2, 'diseniador', 'Diseñador', 'select', NULL, 'Lista de usuarios con perfil deseñador', 1, NULL, '', '2026-01-09 22:11:00', NULL, NULL),
(9, 2, 'obs_disenio', 'Observaciones', 'text_area', NULL, '<text_area>', 1, NULL, '', '2026-01-14 15:19:09', NULL, NULL),
(10, 2, 'fecha_de_entrega', 'fecha de entrega', 'date', NULL, NULL, 1, NULL, '', '2026-02-27 20:26:34', '2026-02-27 20:26:34', NULL),
(11, 3, 'cant_pliegos', 'Cant. pliegos', 'number', NULL, NULL, 1, NULL, '', '2026-02-27 20:33:37', '2026-02-27 21:34:27', NULL),
(12, 3, 'tipo_de_servicio', 'Tipo de servicio', 'select', NULL, 'Papel sin impresion, servicio de corte', 1, NULL, '', '2026-02-27 21:37:01', '2026-02-27 21:37:05', NULL),
(13, 3, 'cantidad', 'Cantidad', 'number', NULL, NULL, 1, NULL, '', '2026-02-27 21:37:47', '2026-02-27 21:37:47', NULL),
(14, 3, 'tamano', 'Tamaño', 'text', NULL, NULL, 1, NULL, '', '2026-02-27 21:38:33', '2026-02-27 21:38:33', NULL),
(15, 5, 'tipo', 'TIPO', 'select', NULL, 'PINZA, TROQUELADORA, ESTAMPADORA', 1, NULL, '', '2026-02-27 21:44:45', '2026-02-27 21:44:45', NULL),
(16, 3, 'tamano', 'Tamaño', 'number', NULL, NULL, 1, NULL, '', '2026-02-27 21:49:32', '2026-02-27 21:49:32', NULL),
(17, 6, 'tipo_acabado', 'tipo de acabado', 'select', NULL, 'plastificado, brillo uv', 1, NULL, NULL, '2026-03-04 14:50:43', '2026-03-04 14:52:59', NULL),
(18, 6, 'tipo_2', 'tipo', 'select', NULL, 'Brillante, Holografico, Mate, Metalizado, Dry', 1, 'tipo_acabado', 'plastificado', '2026-03-04 14:52:18', '2026-03-04 15:02:16', NULL),
(19, 6, 'tipo', 'tipo', 'select', NULL, 'Total, Parcial, Escarcha, Scrash', 1, 'tipo_acabado', 'brillo uv', '2026-03-04 14:54:09', '2026-03-04 14:54:09', NULL),
(20, 6, 'tamano', 'tamaño', 'text', NULL, NULL, 1, NULL, NULL, '2026-03-04 14:56:47', '2026-03-04 14:56:47', NULL),
(21, 6, 'cantidad_total_realizada', 'cantidad total realizada', 'number', NULL, NULL, 1, NULL, NULL, '2026-03-04 14:57:08', '2026-03-04 14:57:08', NULL),
(22, 6, 'obs', 'Obs:', 'text', NULL, NULL, 1, NULL, NULL, '2026-03-04 14:57:39', '2026-03-04 14:57:39', NULL),
(23, 6, 'operarios', 'Operarios', 'text', NULL, NULL, 1, NULL, NULL, '2026-03-04 14:58:18', '2026-03-04 14:58:18', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prd_material_items`
--

CREATE TABLE `prd_material_items` (
  `id` int NOT NULL,
  `qty` double DEFAULT NULL,
  `material_itemId` int DEFAULT NULL,
  `production_order_id` int DEFAULT NULL,
  `process_id` int DEFAULT NULL,
  `unit_measurement` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `prd_material_items`
--

INSERT INTO `prd_material_items` (`id`, `qty`, `material_itemId`, `production_order_id`, `process_id`, `unit_measurement`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 4, 1119, 3, 3, '0', '2026-03-03 15:25:09', '2026-03-03 15:25:09', NULL),
(2, 3, 1120, 4, 6, '0', '2026-03-09 16:15:46', '2026-03-09 16:15:46', NULL),
(3, 5, 1120, 3, 6, '0', '2026-03-09 16:18:19', '2026-03-09 16:18:19', NULL),
(4, 18, 1120, 3, 6, '0', '2026-03-09 16:26:57', '2026-03-09 16:26:57', NULL),
(5, 3, 1120, 6, 3, '0', '2026-03-09 17:55:50', '2026-03-09 17:55:50', NULL),
(6, 4, 1122, 6, 3, '0', '2026-03-09 17:55:50', '2026-03-09 17:55:50', NULL),
(7, 4, 1120, 6, 6, '35', '2026-03-09 19:52:09', '2026-03-09 19:52:09', NULL),
(8, 3, 1122, 6, 6, '35', '2026-03-09 19:52:10', '2026-03-09 19:52:10', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prd_process`
--

CREATE TABLE `prd_process` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `previous_notes` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT 'totas que se muestran al operario para recordar tereas previas del proceso',
  `inventory_consumption` tinyint NOT NULL DEFAULT '0',
  `documents_generates` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT 'json con documentos que podria generar el proceso',
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `prd_process`
--

INSERT INTO `prd_process` (`id`, `name`, `previous_notes`, `inventory_consumption`, `documents_generates`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Sin iniciar', NULL, 0, NULL, 1, '2026-01-09 22:02:35', NULL, NULL),
(2, 'Diseño preprensa', 'Revisar el archivo según guia de diseño antes de enviar a planchas metálicas, las planchas de papel revisarlas mucho antes de pasar a impresión', 0, NULL, 1, '2026-01-09 22:02:35', NULL, NULL),
(3, 'Guillotina', 'Antes de realizar un corte de papel impreso verifique que no este fresco de impresión para evitar el repise\r\nTenga en cuenta las observaciones de corte de los trabajos impresos antes de realizarlo\r\nPasar a los impresores el papel separado y en el caso del papel químico revisado con guía de reacción\r\nComplete la orden de producción según información de impresión y pase el material al siguiente proceso', 1, NULL, 1, '2026-01-14 15:33:33', NULL, NULL),
(4, 'Impresion ofset', 'Verifique en la O.P los materiales necesarios (papel y planchas) para la correcta ejecucion del proceso', 0, '1', 1, '2026-02-27 19:43:55', '2026-02-27 19:43:55', NULL),
(5, 'PINZA - TROQUELADORA- ESTAMPADORA', NULL, 1, '1', 1, '2026-02-27 21:43:25', '2026-02-27 21:43:25', NULL),
(6, 'ACABADOS', NULL, 1, '1', 1, '2026-03-02 14:22:54', '2026-03-02 14:22:54', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prd_process_item`
--

CREATE TABLE `prd_process_item` (
  `id` int NOT NULL,
  `processId` int DEFAULT NULL,
  `itemId` int DEFAULT NULL,
  `process_route_order` int NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `prd_process_item`
--

INSERT INTO `prd_process_item` (`id`, `processId`, `itemId`, `process_route_order`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, 3, 1, '2026-01-15 20:41:06', NULL, NULL),
(2, 3, 3, 2, '2026-01-15 20:41:06', NULL, NULL),
(3, 2, 1118, 1, '2026-02-27 18:02:12', '2026-02-27 19:02:13', '2026-02-27 19:02:13'),
(4, 3, 1118, 2, '2026-02-27 18:02:20', '2026-02-27 19:02:48', '2026-02-27 19:02:48'),
(5, 1, 1118, 1, '2026-02-27 19:16:19', '2026-03-06 21:45:08', NULL),
(6, 3, 1118, 2, '2026-02-27 19:16:26', '2026-03-06 21:45:08', NULL),
(7, 2, 1118, 3, '2026-02-27 19:16:32', '2026-03-06 21:45:08', NULL),
(8, 4, 1118, 3, '2026-02-27 21:42:14', '2026-03-02 17:51:48', '2026-03-02 17:51:48'),
(9, 6, 1118, 4, '2026-03-02 17:45:38', '2026-03-06 21:45:08', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prd_production_order`
--

CREATE TABLE `prd_production_order` (
  `id` int NOT NULL,
  `date` datetime DEFAULT NULL,
  `item_id` int DEFAULT '0',
  `requested_qty` double DEFAULT '0',
  `warehouse_customer_id` int DEFAULT '0',
  `customer_order` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '# de orden',
  `delivery_date` datetime DEFAULT NULL,
  `productive_stage` int DEFAULT NULL,
  `status` int NOT NULL,
  `sales_order` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '# de pedido',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `prd_production_order`
--

INSERT INTO `prd_production_order` (`id`, `date`, `item_id`, `requested_qty`, `warehouse_customer_id`, `customer_order`, `delivery_date`, `productive_stage`, `status`, `sales_order`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '2026-01-15 15:45:40', 3, 2, 8, '# de orden', '2026-01-22 15:45:40', 2, 4, '# de pedido', '2026-01-15 20:46:22', '2026-03-03 21:48:19', NULL),
(2, '2026-03-02 00:00:00', 1118, 3, 50, 'OC12', '2026-03-09 00:00:00', 6, 5, 'PV12', '2026-03-02 16:33:08', '2026-03-03 14:45:45', NULL),
(3, '2026-03-03 00:00:00', 1118, 2, 67, 'oc2031', '2026-03-10 00:00:00', 6, 5, 'it123', '2026-03-03 14:13:45', '2026-03-09 16:26:58', NULL),
(4, '2026-03-03 00:00:00', 1118, 3, 10, 'oc123', '2026-03-10 00:00:00', 6, 5, 'pv234', '2026-03-03 14:14:51', '2026-03-09 16:15:47', NULL),
(5, '2026-03-03 00:00:00', 321, 123, 21, 'pru123', '2026-03-10 00:00:00', NULL, 3, 'pru123', '2026-03-03 21:44:36', '2026-03-03 21:44:36', NULL),
(6, '2026-03-09 00:00:00', 1118, 5, 5, 'OC-001', '2026-03-16 00:00:00', 6, 4, 'PV-001', '2026-03-09 17:54:45', '2026-03-09 19:52:10', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prd_status`
--

CREATE TABLE `prd_status` (
  `id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `application` int DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `detail_application` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT 'detalle de la aplicacion del estado',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `prd_status`
--

INSERT INTO `prd_status` (`id`, `name`, `application`, `status`, `detail_application`, `created_at`, `updated_at`, `deleted_at`) VALUES
(3, 'REGISTRADO', 1, 1, 'ORDEN DE PRODUCCION', '2026-01-15 20:11:50', NULL, NULL),
(4, 'EN PROCESO', 1, 1, 'ORDEN DE PRODUCCION', '2026-01-15 20:11:50', NULL, NULL),
(5, 'FINALIZADO', 1, 1, 'ORDEN DE PRODUCCION', '2026-01-15 20:12:29', NULL, NULL),
(6, 'ANULADO', 1, 1, 'ORDEN DE PRODUCCION', '2026-01-15 20:12:29', NULL, NULL),
(7, 'PAUSADO', 1, 1, 'ORDEN DE PRODUCCION', '2026-01-15 20:12:46', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prd_users_process`
--

CREATE TABLE `prd_users_process` (
  `id` int NOT NULL,
  `operator_user_id` int DEFAULT NULL,
  `process_id` int DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `prd_users_process`
--

INSERT INTO `prd_users_process` (`id`, `operator_user_id`, `process_id`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 123, 2, 1, '2026-01-09 22:07:47', NULL, NULL),
(2, 180, 6, 1, '2026-03-02 14:22:55', '2026-03-02 14:22:55', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prd_work_names`
--

CREATE TABLE `prd_work_names` (
  `id` int NOT NULL,
  `prd_order_id` int DEFAULT NULL,
  `work_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Razoń social',
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tick_departments`
--

CREATE TABLE `tick_departments` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` tinyint(1) DEFAULT '1',
  `order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tick_departments`
--

INSERT INTO `tick_departments` (`id`, `name`, `description`, `status`, `order`, `created_at`, `updated_at`) VALUES
(2, 'Mercadeo (fotos, Pagina Web)', 'Seleccione este departamento para solicitar fotos o temas relacionados al departamento de publicidad de Fervicom', 1, 0, '2026-03-04 17:06:44', '2026-03-04 17:21:24'),
(3, 'Laboratorio', 'Solicitudes a Laboratorio, sobre información de productos para posteriormente subirlo al ERP', 1, 0, '2026-03-05 13:28:22', '2026-03-05 13:28:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tick_department_user`
--

CREATE TABLE `tick_department_user` (
  `id` bigint UNSIGNED NOT NULL,
  `department_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `status` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tick_department_user`
--

INSERT INTO `tick_department_user` (`id`, `department_id`, `user_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 1, '2026-03-04 17:06:45', '2026-03-04 17:06:45'),
(2, 2, 2, 1, '2026-03-04 17:06:45', '2026-03-04 17:06:45'),
(3, 2, 3, 1, '2026-03-04 17:06:45', '2026-03-04 17:06:45'),
(4, 2, 4, 1, '2026-03-04 17:06:45', '2026-03-04 17:06:45'),
(5, 2, 5, 1, '2026-03-04 17:06:45', '2026-03-04 17:06:45'),
(6, 3, 8, 1, '2026-03-05 13:28:22', '2026-03-05 13:28:22'),
(7, 3, 9, 1, '2026-03-05 13:28:22', '2026-03-05 13:28:22'),
(8, 3, 11, 1, '2026-03-05 13:28:23', '2026-03-05 13:28:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tick_requests`
--

CREATE TABLE `tick_requests` (
  `id` bigint UNSIGNED NOT NULL,
  `department_id` bigint UNSIGNED NOT NULL,
  `status_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `assigned_to` bigint UNSIGNED DEFAULT NULL,
  `detail` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_path` text COLLATE utf8mb4_unicode_ci,
  `resolved_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tick_requests`
--

INSERT INTO `tick_requests` (`id`, `department_id`, `status_id`, `product_id`, `created_by`, `assigned_to`, `detail`, `image_path`, `resolved_at`, `created_at`, `updated_at`) VALUES
(3, 2, 3, 1119, 8, NULL, '<p>pruebas1</p>', NULL, NULL, '2026-03-04 21:22:21', '2026-03-04 21:23:00'),
(4, 2, 1, 1107, 8, NULL, '<p>por favor subir imagen sde este producto </p>', NULL, NULL, '2026-03-05 13:26:26', '2026-03-05 13:26:26'),
(5, 3, 1, 1119, 8, NULL, '<p>efwefwfef</p>', NULL, NULL, '2026-03-16 19:59:13', '2026-03-16 19:59:13'),
(6, 3, 1, 1122, 8, NULL, '<p>pruebas</p>', NULL, NULL, '2026-03-16 20:03:24', '2026-03-16 20:03:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tick_request_history`
--

CREATE TABLE `tick_request_history` (
  `id` bigint UNSIGNED NOT NULL,
  `request_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `from_status_id` bigint UNSIGNED DEFAULT NULL,
  `to_status_id` bigint UNSIGNED DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tick_request_history`
--

INSERT INTO `tick_request_history` (`id`, `request_id`, `user_id`, `from_status_id`, `to_status_id`, `message`, `created_at`) VALUES
(4, 3, 8, NULL, 1, 'Solicitud creada.', '2026-03-04 21:22:21'),
(5, 3, 8, 1, 3, '<p>ddasdasdasd</p>', '2026-03-04 21:23:00'),
(6, 4, 8, NULL, 1, '<p>por favor subir imagen sde este producto </p>', '2026-03-05 13:26:26'),
(7, 5, 8, NULL, 1, '<p>efwefwfef</p>', '2026-03-16 19:59:13'),
(8, 6, 8, NULL, 1, '<p>pruebas</p>', '2026-03-16 20:03:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tick_statuses`
--

CREATE TABLE `tick_statuses` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'gray',
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'heroicon-o-chat-bubble-left',
  `order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tick_statuses`
--

INSERT INTO `tick_statuses` (`id`, `name`, `slug`, `color`, `icon`, `order`, `created_at`, `updated_at`) VALUES
(1, 'Registrado', 'registrado', 'indigo', 'heroicon-o-document-plus', 1, '2026-03-04 11:15:41', '2026-03-04 11:15:41'),
(2, 'Reactivado', 'reactivado', 'green', 'heroicon-o-arrow-path', 2, '2026-03-04 11:15:41', '2026-03-04 11:15:41'),
(3, 'Solucionado', 'solucionado', 'blue', 'heroicon-o-check-circle', 3, '2026-03-04 11:15:41', '2026-03-04 11:15:41'),
(4, 'Imposibilidad', 'imposibilidad', 'red', 'heroicon-o-x-circle', 4, '2026-03-04 11:15:41', '2026-03-04 11:15:41');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vnt_companies`
--

CREATE TABLE `vnt_companies` (
  `id` int NOT NULL,
  `businessName` varchar(255) DEFAULT NULL,
  `api_data_id` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '0',
  `billingEmail` varchar(255) DEFAULT NULL,
  `firstName` varchar(255) DEFAULT NULL,
  `integrationDataId` int DEFAULT NULL,
  `identification` varchar(15) DEFAULT NULL,
  `checkDigit` int DEFAULT NULL COMMENT 'digito de verificacion',
  `lastName` varchar(255) DEFAULT NULL,
  `secondLastName` varchar(255) DEFAULT NULL,
  `secondName` varchar(255) DEFAULT NULL,
  `status` tinyint DEFAULT '1',
  `typePerson` varchar(255) DEFAULT NULL,
  `typeIdentificationId` int NOT NULL,
  `regimeId` int DEFAULT NULL,
  `code_ciiu` varchar(255) DEFAULT NULL,
  `fiscalResponsabilityId` int DEFAULT NULL,
  `type` enum('CLIENTE','PROVEEDOR') NOT NULL DEFAULT 'CLIENTE',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `vnt_companies`
--

INSERT INTO `vnt_companies` (`id`, `businessName`, `api_data_id`, `billingEmail`, `firstName`, `integrationDataId`, `identification`, `checkDigit`, `lastName`, `secondLastName`, `secondName`, `status`, `typePerson`, `typeIdentificationId`, `regimeId`, `code_ciiu`, `fiscalResponsabilityId`, `type`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '', '', 'yesipoveda@gmail.com', 'Yesi', NULL, '1018469203', NULL, 'Alexander', 'Yara', 'Poveda', 0, 'PERSON_ENTITY', 1, 2, '0', 1, 'CLIENTE', '2025-11-12 16:21:49', '2026-02-13 16:58:27', NULL),
(2, '', '', 'prueba@gmail.com', 'prueba', NULL, '12454654', NULL, 'prueba', 'prueba', 'prueba', 1, 'PERSON_ENTITY', 1, 2, '0', 1, 'CLIENTE', '2025-11-12 17:12:13', '2025-11-12 17:12:13', NULL),
(3, '', '', 'alejandrabarbosa2003@gmail.com', 'Maria ', NULL, '52031452', NULL, 'Alejandra', 'Marulanda', 'Barbosa ', 1, 'PERSON_ENTITY', 1, 2, '0', 1, 'CLIENTE', '2025-11-12 20:43:22', '2025-11-12 20:45:08', '2025-11-12 20:45:08'),
(4, '', '', 'Miercoles@gmail.com', 'Miercoles', NULL, '245465', NULL, 'Miercoles', 'Miercoles', 'Miercoles', 1, 'PERSON_ENTITY', 1, 2, '0', 1, 'PROVEEDOR', '2025-11-12 21:31:33', '2026-01-23 20:20:45', NULL),
(5, 'Ticsia', '', 'ticisa@ticsia.com', 'Ticsia', NULL, '5454645323', 4, NULL, NULL, NULL, 1, 'LEGAL_ENTITY', 2, 3, '45512', 4, 'CLIENTE', '2025-11-12 21:39:12', '2025-11-13 14:33:07', NULL),
(6, '', '', 'juan@gmail.com', 'Juan', NULL, '397006027', NULL, 'Manuel', 'Quintana', 'Montoya', 1, 'PERSON_ENTITY', 1, 2, '0', 1, 'CLIENTE', '2025-11-13 14:29:23', '2025-11-13 14:29:23', NULL),
(7, 'Bookbet', '', 'bookbet@bookbet.com', 'Bookbet', NULL, '555548', 4, NULL, NULL, NULL, 1, 'LEGAL_ENTITY', 2, 3, '4', 4, 'CLIENTE', '2025-11-13 15:55:42', '2025-11-13 15:55:42', NULL),
(8, 'Bictia', '', 'bictia@bictia.com', 'Bictia', NULL, '9655425', 4, NULL, NULL, NULL, 1, 'LEGAL_ENTITY', 2, 3, '45', 5, 'CLIENTE', '2025-11-13 16:07:21', '2025-11-13 16:07:21', NULL),
(9, 'Empresa', '', 'aa@gmail.com', 'Empresa', NULL, '99999999', 5, NULL, NULL, NULL, 1, 'LEGAL_ENTITY', 2, 1, '45', 3, 'CLIENTE', '2025-11-13 16:15:53', '2025-11-13 17:31:11', NULL),
(10, '', '', 'sucursal@gmail.com', 'Prueba', NULL, '5556647', NULL, 'Sucursal', 'Sucursal', 'Sucursal ', 1, 'PERSON_ENTITY', 1, 2, '0', 1, 'CLIENTE', '2025-11-13 17:05:28', '2025-11-13 17:05:28', NULL),
(11, '', '', 'dsad@df.com', 'dsa', NULL, '66664', NULL, 'dsadsa', 'dsad', 'dsadsa', 1, 'PERSON_ENTITY', 1, 2, '0', 1, 'CLIENTE', '2025-11-19 16:07:48', '2025-11-19 16:07:48', NULL),
(12, '', '', 'dsad@df.com', 'Laura', NULL, '78979874', NULL, 'Liliana', 'García', 'luke', 1, 'PERSON_ENTITY', 1, 2, '0', 1, 'CLIENTE', '2025-11-21 20:09:22', '2025-11-21 20:09:22', NULL),
(13, '', '', 'aroca@gmail.com', 'Alexis', NULL, '35555', NULL, 'Lazaro', 'Bermudes', 'Aroca', 1, 'PERSON_ENTITY', 1, 2, '0', 1, 'CLIENTE', '2025-11-24 16:51:31', '2025-11-24 16:51:31', NULL),
(15, '', '', 'eduardo@prueba.com', 'eduardo', NULL, '20164547', NULL, '', '', 'da', 1, 'PERSON_ENTITY', 1, 2, '0', 1, 'CLIENTE', '2025-11-24 17:23:32', '2025-11-24 17:50:37', NULL),
(19, '', '', 'p@gmail.com', 'Edwin gre', NULL, '80833673', NULL, 'gregorio', 'CONTRERAS', 'PRIETO', 1, 'PERSON_ENTITY', 1, 2, '0', 1, 'CLIENTE', '2025-11-24 19:06:06', '2025-12-02 13:48:00', NULL),
(20, '', '', 'ma@gmail.com', 'Roberto', NULL, '801785421', 8, 'ma', 'Gomez', 'Martinez', 1, 'LEGAL_ENTITY', 2, 2, '0', 1, 'PROVEEDOR', '2025-11-24 19:09:22', '2026-01-23 21:42:18', NULL),
(21, '', '', 'df@gmail.com', 'df', NULL, '12121212', NULL, 'df', 'df', 'df', 1, 'PERSON_ENTITY', 1, 2, '0', 1, 'CLIENTE', '2025-11-24 19:13:17', '2025-11-24 19:13:17', NULL),
(22, '', '', 'nueva@gmail.com', 'Prueba', NULL, '568887', NULL, 'Prueba', 'Prueba', 'Prueba', 1, 'PERSON_ENTITY', 1, 2, '0', 1, 'CLIENTE', '2025-11-25 13:54:53', '2025-11-25 13:54:53', NULL),
(23, '', '', 'jily@mailinator.com', 'Alexa', NULL, '9996325', NULL, 'Craig', 'Morgan', 'Burton', 1, 'PERSON_ENTITY', 6, 2, '0', 1, 'PROVEEDOR', '2025-11-25 16:13:05', '2025-12-01 21:17:52', NULL),
(24, '', '', 'lhuertas@gmail.com', 'Luisa', NULL, '1023452106', NULL, '', 'Ocampo', 'Huertas', 1, 'PERSON_ENTITY', 1, 2, '0', 1, 'CLIENTE', '2026-01-22 14:00:08', '2026-01-22 14:00:08', NULL),
(25, 'ACCESORIOS JKU', '', 'accesoriosjku@gmail.com', 'ACCESORIOS JKU', NULL, '930521001', 7, NULL, NULL, NULL, 1, 'LEGAL_ENTITY', 2, 3, '5230', 1, 'CLIENTE', '2026-01-22 14:35:54', '2026-01-22 14:35:54', NULL),
(26, 'Tienda TODOPETS', '', 'tiendaTodopets@gmail.com', 'Tienda TODOPETS', NULL, '809114144', 0, NULL, NULL, NULL, 1, 'LEGAL_ENTITY', 2, 4, '3601', 3, 'PROVEEDOR', '2026-01-22 21:49:22', '2026-01-22 21:49:22', NULL),
(27, '', '', 'gabrielBernal@gmail.com', 'Gabriel ', NULL, '1023121450', NULL, 'Tomas', 'Huertas', 'Bernal', 1, 'PERSON_ENTITY', 1, 2, '0', 1, 'CLIENTE', '2026-01-23 19:21:18', '2026-01-23 19:21:18', NULL),
(28, 'Tienda Genericos SAS', '', 'tienda_genericos@gmail.com', 'Tienda Genericos SAS', NULL, '807451892', 8, NULL, NULL, NULL, 1, 'LEGAL_ENTITY', 2, 4, '7890', 3, 'PROVEEDOR', '2026-01-23 20:20:08', '2026-02-13 14:26:07', NULL),
(29, '', '', 'ravila23@gmail.com', 'Raquel', NULL, '103269145452', NULL, '', 'Bermudes', 'Avila', 1, 'PERSON_ENTITY', 1, 2, '0', 1, 'PROVEEDOR', '2026-01-23 20:39:12', '2026-01-23 20:39:12', NULL),
(30, 'HEADPHONES ACC', '', 'headphonesEmpresy@gmail.com', 'HEADPHONES ACC', NULL, '845120631', 8, NULL, NULL, NULL, 1, 'LEGAL_ENTITY', 2, 3, '0', 1, 'CLIENTE', '2026-01-23 21:13:49', '2026-01-23 21:13:49', NULL),
(63, '', '0', 'juseloco213@gmail.com', 'Juan', NULL, '256154212', NULL, 'angela', 'Contreras', 'Johana', 1, 'PERSON_ENTITY', 1, 4, '0', 2, 'CLIENTE', '2026-01-28 13:57:47', '2026-01-28 13:57:47', NULL),
(64, '', '366', 'juseloco254@gmail.com', 'juan', NULL, '1018507004', NULL, 'angela', 'Contreras', 'Sebastian', 1, 'PERSON_ENTITY', 1, 4, '0', 2, 'CLIENTE', '2026-01-28 14:33:20', '2026-01-28 14:33:21', NULL),
(65, '', '367', 'juseloco24@gmail.com', 'juan camilo', NULL, '12312412', NULL, 'Sebastian', 'Contreras', 'angela', 1, 'PERSON_ENTITY', 1, 3, '0', 4, 'CLIENTE', '2026-01-28 14:40:16', '2026-01-28 15:50:02', NULL),
(66, '', '0', 'prueba123@gmail.com', 'Juan', NULL, '101859876', NULL, 'Sebastian', 'prueba', 'Lozano', 1, 'PERSON_ENTITY', 1, 2, '0', 2, 'CLIENTE', '2026-01-28 14:44:50', '2026-01-28 14:44:50', NULL),
(67, '', '368', 'juseloco4561@gmail.com', 'Juan', NULL, '98633312', NULL, 'Sebastian', 'Contreras', 'Lozano', 1, 'PERSON_ENTITY', 1, 4, '0', 2, 'CLIENTE', '2026-01-28 16:23:28', '2026-01-28 16:23:29', NULL),
(68, '', '369', 'proveedor@gmail.com', 'proveedor', NULL, '11234412', NULL, 'proveedor', 'proveedor', 'proveedor', 1, 'PERSON_ENTITY', 3, 3, '0', 3, 'PROVEEDOR', '2026-01-28 16:28:37', '2026-01-28 16:28:38', NULL),
(69, '', '370', 'juseloco217@gmail.com', 'Angela', NULL, '1234567891', NULL, 'Sebastian', 'Contreras', 'Sebastian', 1, 'PERSON_ENTITY', 1, 4, '0', 3, 'CLIENTE', '2026-01-28 17:50:09', '2026-01-28 17:50:10', NULL),
(70, '', '371', 'test@gmail.com', 'test', NULL, '123477819', NULL, 'jaime', 'gregorio', 'jaimers', 1, 'PERSON_ENTITY', 1, 6, '0', 1, 'CLIENTE', '2026-01-28 19:11:25', '2026-01-28 19:11:25', NULL),
(71, '', '0', 'sebastianlozano@gmail.com', 'juan', NULL, '1018507021', NULL, 'sebastian', 'contreras', 'lozano', 1, 'PERSON_ENTITY', 1, 3, '0', 2, 'CLIENTE', '2026-01-30 15:55:57', '2026-01-30 15:55:57', NULL),
(72, '', '0', 'carolina_duran@gmail.com', 'Ana', NULL, '1012458721', NULL, 'Carolina', 'Casas', 'Duran', 1, 'PERSON_ENTITY', 1, 4, '0', 1, 'CLIENTE', '2026-01-30 16:12:01', '2026-01-30 16:12:43', NULL),
(74, '', '375', 'samuel@gmail.com', 'primer', NULL, '9811106050', NULL, 'papellido', 'sapellido', 'segundo', 1, 'PERSON_ENTITY', 1, 3, '0', 2, 'CLIENTE', '2026-02-03 16:30:31', '2026-02-03 16:39:18', NULL),
(75, '', '376', 'primerNombre@gmail.com', 'Primer Nombre', NULL, '9812039812', NULL, 'Primer Apellido', 'Segundo Apellido', 'Segundo Nombre', 1, 'PERSON_ENTITY', 1, 4, '0', 1, 'CLIENTE', '2026-02-03 16:43:14', '2026-02-03 16:43:14', NULL),
(76, '', '377', 'amendez@gmail.com', 'Angela', NULL, '1022440047', NULL, 'Bolivar', 'Mendez', '', 1, 'PERSON_ENTITY', 1, 4, '0', 1, 'CLIENTE', '2026-02-03 16:52:32', '2026-02-03 16:52:32', NULL),
(77, '', '378', 'pruebaTenantLozano@gmail.com', 'Juan', NULL, '98111060401', NULL, 'tenant', 'Contreras', 'prueba', 1, 'PERSON_ENTITY', 1, 3, '0', 2, 'CLIENTE', '2026-02-04 15:36:58', '2026-02-04 15:36:58', NULL),
(78, '', '380', 'juselocUno@gmail.com', 'nombreU', NULL, '1234567895', NULL, 'Bolivar', 'prueba', 'Sebastian', 1, 'PERSON_ENTITY', 1, 3, '0', 2, 'CLIENTE', '2026-02-04 16:04:58', '2026-02-04 16:04:59', NULL),
(79, 'EMPRESA SAS', '0', 'empresajuridicasas@gmail.com', 'EMPRESA SAS', NULL, '805631475', 5, NULL, NULL, NULL, 1, 'LEGAL_ENTITY', 2, 3, '3601', 2, 'CLIENTE', '2026-02-13 14:00:51', '2026-02-13 14:00:51', NULL),
(80, 'AS ANALITICA', '0', 'asanalitica@gmail.com', 'AS ANALITICA', NULL, '861021784', 2, NULL, NULL, NULL, 1, 'LEGAL_ENTITY', 2, 5, '3601', 1, 'CLIENTE', '2026-02-13 14:17:30', '2026-02-13 14:17:30', NULL),
(81, 'CARPAINT', '0', 'comercial@carpaint.com.co', 'CARPAINT', NULL, '900017210', 1, NULL, NULL, NULL, 1, 'LEGAL_ENTITY', 2, 1, '5230', 2, 'PROVEEDOR', '2026-02-13 14:24:28', '2026-02-13 14:24:28', NULL),
(82, '', '0', 'brendaSuarez@outlook.es', 'Brenda', NULL, '1024814321', NULL, 'Suarez', '', '', 1, 'PERSON_ENTITY', 1, 2, '0', 1, 'CLIENTE', '2026-02-13 16:44:28', '2026-02-13 16:44:28', NULL),
(83, '', '391', 'Mariaquote@gmail.com', 'Maria', NULL, '1234567892', NULL, 'Barbosa', 'quote', 'Alejandra', 1, 'PERSON_ENTITY', 1, 3, '0', 1, 'CLIENTE', '2026-02-16 19:18:44', '2026-02-16 19:18:45', NULL),
(84, 'ILUMINARIA LTDA', '0', 'iluminaria@gmail.com', 'ILUMINARIA LTDA', NULL, '904521751', 8, NULL, NULL, NULL, 1, 'LEGAL_ENTITY', 2, 5, '9601', 1, 'CLIENTE', '2026-02-16 20:44:58', '2026-02-16 20:44:58', NULL),
(85, 'RightLeds', '394', 'proveedor_prueba@gmail.com', 'RightLeds', NULL, '906142012', 3, 'prueba', 'prueba', NULL, 1, 'LEGAL_ENTITY', 2, 4, '7845', 3, 'CLIENTE', '2026-03-02 17:05:27', '2026-03-16 17:08:12', NULL),
(86, '', '0', 'david_martinez@gmail.com', 'David', NULL, '1032504851', NULL, 'Martinez', '', '', 1, 'PERSON_ENTITY', 1, 3, '0', 1, 'CLIENTE', '2026-03-17 19:48:13', '2026-03-17 19:48:13', NULL),
(87, 'Lemon Company SAS', '0', 'lemon_company@outlook.com', 'Lemon Company SAS', NULL, '809414001', 2, NULL, NULL, NULL, 1, 'LEGAL_ENTITY', 2, 2, '7845', 2, 'CLIENTE', '2026-03-17 19:59:03', '2026-03-17 19:59:03', NULL),
(88, '', '0', 'jslozanoc98@gmail.com', 'Sebastian', NULL, '1018507098', NULL, 'Lozano', 'Contreras', 'Sebastian', 1, 'PERSON_ENTITY', 1, 3, '0', 1, 'CLIENTE', '2026-04-09 19:37:36', '2026-04-09 19:37:36', NULL),
(90, '', '0', 'jslozanoc75@gmail.com', 'Sebastian', NULL, '1018507076', NULL, 'Lozano', 'Contreras', 'Sebastian', 1, 'PERSON_ENTITY', 1, 3, '0', 1, 'CLIENTE', '2026-04-09 19:44:18', '2026-04-09 19:44:18', NULL),
(91, '', '0', 'jslozanoc68@gmail.com', 'Sebastian', NULL, '1018507068', NULL, 'maria', 'Contrtraras', 'Lozano', 1, 'PERSON_ENTITY', 1, 3, '0', 2, 'CLIENTE', '2026-04-09 19:52:24', '2026-04-09 19:52:24', NULL),
(92, '', '0', 'jslozanoc4@gmail.com', 'Maria', NULL, '1231241233', NULL, 'Fernanda', 'Contrerass', 'Fernanda', 1, 'PERSON_ENTITY', 3, 3, '0', 2, 'CLIENTE', '2026-04-09 20:05:37', '2026-04-09 20:05:37', NULL),
(93, '', '0', 'jslozanoc65@gmail.com', 'carlos', NULL, '1234178909', NULL, 'Alberto', 'Perez', 'alberto', 1, 'PERSON_ENTITY', 1, 4, '0', 2, 'CLIENTE', '2026-04-09 20:16:45', '2026-04-09 20:16:45', NULL),
(94, '', '395', 'jslozanoc129@gmail.com', 'Sebastian', NULL, '765657129', NULL, 'buitrado', 'perez', 'Alejandra', 1, 'PERSON_ENTITY', 1, 3, '0', 1, 'CLIENTE', '2026-04-09 20:24:43', '2026-04-09 20:24:44', NULL),
(95, '', '396', 'camilo@gmail.com', 'camilo', NULL, '986333128', NULL, 'romero', 'bergano', 'andres', 1, 'PERSON_ENTITY', 1, 3, '0', 2, 'CLIENTE', '2026-04-09 20:37:40', '2026-04-09 20:37:41', NULL),
(96, '', '398', 'diana_herrera@gmail.com', 'Diana', NULL, '1014562357', NULL, 'Herrera', 'Gonzales', 'Fernanda', 1, 'PERSON_ENTITY', 1, 3, '0', 1, 'CLIENTE', '2026-04-10 13:20:18', '2026-04-10 13:20:19', NULL),
(97, 'SOM & GRAND SAS', '399', 'comercial_som_grand@gmail.com', 'SOM & GRAND SAS', NULL, '789456123', 0, NULL, NULL, NULL, 1, 'LEGAL_ENTITY', 2, 2, '', 3, 'CLIENTE', '2026-04-10 14:06:12', '2026-04-10 14:06:12', NULL),
(98, '', '0', 'claudia_jimenez@outlook.es', 'Claudia', NULL, '456123789', NULL, 'Jimenez', 'Fernandez', 'Raquel', 1, 'PERSON_ENTITY', 1, 4, '0', 1, 'CLIENTE', '2026-04-10 17:40:16', '2026-04-10 17:40:16', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vnt_companies_routes`
--

CREATE TABLE `vnt_companies_routes` (
  `id` int NOT NULL,
  `company_id` int DEFAULT NULL COMMENT 'id del cliente',
  `route_id` int DEFAULT NULL COMMENT 'ruta',
  `sales_order` int DEFAULT NULL COMMENT 'orden en el que se hace el recorrido de ventas',
  `delivery_order` int DEFAULT NULL COMMENT 'orden en el que se hace el recorrido de entregas',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `vnt_companies_routes`
--

INSERT INTO `vnt_companies_routes` (`id`, `company_id`, `route_id`, `sales_order`, `delivery_order`, `created_at`, `updated_at`, `deleted_at`) VALUES
(63, 26, 9, 1, NULL, '2026-01-23 13:41:48', '2026-01-29 13:40:03', NULL),
(64, 25, 2, 1, NULL, '2026-01-23 13:46:12', '2026-01-23 13:46:12', NULL),
(65, 24, 8, 2, NULL, '2026-01-23 13:46:20', '2026-01-23 13:46:20', NULL),
(66, 21, 9, 1, NULL, '2026-01-23 13:46:50', '2026-01-26 20:14:08', NULL),
(67, 20, 2, 2, NULL, '2026-01-23 13:49:10', '2026-01-23 13:49:10', NULL),
(68, 19, 9, 2, NULL, '2026-01-23 13:49:23', '2026-01-26 20:14:08', NULL),
(69, 15, 2, 4, NULL, '2026-01-23 13:50:10', '2026-03-17 20:00:38', NULL),
(70, 12, 2, 3, NULL, '2026-01-23 13:51:45', '2026-03-17 20:00:38', NULL),
(71, 6, 2, 5, NULL, '2026-01-23 20:22:35', '2026-01-23 20:22:35', NULL),
(72, 30, 2, 6, NULL, '2026-01-23 21:13:50', '2026-01-23 21:13:50', NULL),
(98, 63, 2, 7, NULL, '2026-01-28 13:57:48', '2026-01-28 13:57:48', NULL),
(99, 64, 2, 8, NULL, '2026-01-28 14:33:21', '2026-01-28 14:33:21', NULL),
(100, 65, 2, 9, NULL, '2026-01-28 14:40:17', '2026-01-28 14:40:17', NULL),
(101, 66, 2, 10, NULL, '2026-01-28 14:44:50', '2026-01-28 14:44:50', NULL),
(102, 67, 9, 3, NULL, '2026-01-28 16:23:29', '2026-01-28 16:23:29', NULL),
(103, 68, 9, 4, NULL, '2026-01-28 16:28:38', '2026-01-28 16:28:38', NULL),
(104, 69, 9, 5, NULL, '2026-01-28 17:50:10', '2026-01-28 17:50:10', NULL),
(105, 70, 2, 11, NULL, '2026-01-28 19:11:25', '2026-01-28 19:11:25', NULL),
(106, 71, 2, 12, NULL, '2026-01-30 15:55:58', '2026-01-30 15:55:58', NULL),
(107, 72, 8, 3, NULL, '2026-01-30 16:12:02', '2026-01-30 16:12:02', NULL),
(109, 74, 2, 13, NULL, '2026-02-03 16:30:32', '2026-02-03 16:30:32', NULL),
(110, 75, 2, 14, NULL, '2026-02-03 16:43:14', '2026-02-03 16:43:14', NULL),
(111, 76, 2, 15, NULL, '2026-02-03 16:52:33', '2026-02-03 16:52:33', NULL),
(112, 77, 9, 6, NULL, '2026-02-04 15:36:58', '2026-02-04 15:36:58', NULL),
(113, 79, 9, 7, NULL, '2026-02-13 14:00:52', '2026-02-13 14:00:52', NULL),
(114, 80, 2, 16, NULL, '2026-02-13 14:17:30', '2026-02-13 14:17:30', NULL),
(115, 81, 2, 17, NULL, '2026-02-13 14:24:29', '2026-02-13 14:24:29', NULL),
(116, 83, 2, 18, NULL, '2026-02-16 19:18:45', '2026-02-16 19:18:45', NULL),
(117, 86, 10, 1, NULL, '2026-03-17 19:48:13', '2026-03-17 19:48:13', NULL),
(118, 87, 2, 19, NULL, '2026-03-17 19:59:04', '2026-03-17 19:59:04', NULL),
(119, 94, 2, 20, NULL, '2026-04-09 20:24:45', '2026-04-09 20:24:45', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vnt_contacts`
--

CREATE TABLE `vnt_contacts` (
  `id` int NOT NULL,
  `firstName` varchar(255) DEFAULT NULL,
  `secondName` varchar(255) DEFAULT NULL,
  `lastName` varchar(255) DEFAULT NULL,
  `secondLastName` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `business_phone` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `personal_phone` varchar(100) DEFAULT NULL,
  `status` tinyint DEFAULT '1',
  `api_data_id` int DEFAULT NULL,
  `warehouseId` bigint UNSIGNED DEFAULT '1',
  `positionId` int DEFAULT '1',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `vnt_contacts`
--

INSERT INTO `vnt_contacts` (`id`, `firstName`, `secondName`, `lastName`, `secondLastName`, `email`, `business_phone`, `personal_phone`, `status`, `api_data_id`, `warehouseId`, `positionId`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Yesi', 'Poveda', 'Alexander', 'Yara', 'yesipoveda@gmail.com', '3208614517', '', 0, NULL, 1, 1, '2025-11-12 17:11:01', '2026-02-13 16:58:28', NULL),
(2, 'prueba', 'prueba', 'prueba', 'prueba', 'prueba@gmail.com', '300000000', '445555555', 1, NULL, 3, 1, '2025-11-12 17:12:13', '2025-11-12 17:12:13', NULL),
(3, 'Maria ', 'Barbosa ', 'Alejandra', 'Marulanda', 'prueba@gmail.com', '+573192365396', '+573192365396', 1, NULL, 4, 1, '2025-11-12 20:43:22', '2025-11-12 20:44:39', NULL),
(4, 'Miercoles', 'Miercoles', 'Miercoles', 'Miercoles', 'Miercoles@gmail.com', '36045854654', '34541126565', 1, NULL, 6, 1, '2025-11-12 21:31:33', '2025-11-12 21:31:33', NULL),
(5, 'Ticsia', NULL, NULL, NULL, 'ticisa@ticsia.com', '455143435456', '5761453545545', 1, NULL, 7, 1, '2025-11-12 21:39:13', '2025-11-13 14:33:07', NULL),
(6, 'Juan', 'Montoya', 'Manuel', 'Quintana', 'juan@gmail.com', '320456814546', '3004546566', 1, NULL, 9, 1, '2025-11-13 14:29:23', '2025-11-13 14:29:23', NULL),
(7, 'Bictia', NULL, NULL, NULL, 'bictia@bictia.com', '350546465', '350546465', 1, NULL, 10, 1, '2025-11-13 16:07:22', '2025-11-13 16:07:22', NULL),
(8, 'Empresa', NULL, NULL, NULL, 'aa@gmail.com', '+5766644455', '+5766644455', 1, NULL, 11, 1, '2025-11-13 16:15:53', '2025-11-13 16:35:15', NULL),
(9, 'karen', 'Susana', 'Buya', 'Sucursal', 'sucursal@gmail.com', '45665456', '456456468', 1, NULL, 12, 1, '2025-11-13 17:05:29', '2025-11-13 20:25:29', NULL),
(10, 'Dayana', 'Sofía', 'Perez', 'García', 'dayana@prueba.com', '34468464', '3208614517', 1, NULL, 13, 3, '2025-11-13 20:17:06', '2025-11-13 20:29:18', NULL),
(11, 'dsa', 'dsadsa', 'dsadsa', 'dsad', 'dsad@df.com', '55784899', '6654849', 1, NULL, 17, 1, '2025-11-19 16:07:49', '2025-11-19 16:07:49', NULL),
(12, 'Laura', 'luke', 'Liliana', 'García', 'dsad@df.com', '3508614517', '3508614517', 1, NULL, 18, 1, '2025-11-21 20:09:22', '2025-11-21 20:09:22', NULL),
(13, 'Alexis', 'Aroca', 'Lazaro', 'Bermudes', 'aroca@gmail.com', '3548548456', '35485484532', 1, NULL, 19, 1, '2025-11-24 16:51:32', '2025-11-24 16:51:32', NULL),
(14, 'eduardo', 'da', '', '', 'eduardo@prueba.com', '3207784565', '', 1, NULL, 20, 1, '2025-11-24 17:23:32', '2026-01-23 13:50:10', NULL),
(15, 'Edwin gre', 'PRIETO', 'gregorio', 'CONTRERAS', 'p@gmail.com', '3123842021', '', 1, NULL, 21, 1, '2025-11-24 19:06:07', '2025-12-02 13:48:00', NULL),
(16, 'Roberto', NULL, NULL, NULL, 'ma@gmail.com', '3102329154', '', 1, NULL, 22, 1, '2025-11-24 19:09:23', '2026-01-23 13:49:10', NULL),
(17, 'df', 'df', 'df', 'df', 'df@gmail.com', '3123842021', '', 1, NULL, 23, 1, '2025-11-24 19:13:17', '2025-11-24 19:13:17', NULL),
(18, 'Prueba', 'Prueba', 'Prueba', 'Prueba', 'nueva@gmail.com', '32044477585', '', 1, NULL, 24, 1, '2025-11-25 13:54:54', '2025-11-25 13:54:54', NULL),
(19, 'Alexa', 'Burton', 'Craig', 'Morgan', 'jily@mailinator.com', '+1 (166) 464-8794', '+1 (832) 169-3342', 1, NULL, 25, 1, '2025-11-25 16:13:05', '2025-12-01 21:17:52', NULL),
(20, 'Luisa', 'Huertas', '', 'Ocampo', 'lhuertas@gmail.com', '3123842021', '3112028952', 1, NULL, 26, 1, '2026-01-22 14:00:09', '2026-01-22 14:00:09', NULL),
(21, 'ACCESORIOS JKU', NULL, NULL, NULL, 'accesoriosjku@gmail.com', '3120058914', '3158900714', 1, NULL, 27, 1, '2026-01-22 14:35:54', '2026-01-22 14:35:54', NULL),
(22, 'Tienda TODOPETS', NULL, NULL, NULL, 'tiendaTodopets@gmail.com', '3180453210', '3112028952', 1, NULL, 28, 1, '2026-01-22 21:49:22', '2026-01-22 21:49:22', NULL),
(23, 'Gabriel ', 'Bernal', 'Tomas', 'Huertas', 'gabrielBernal@gmail.com', '3124507845', '3157801421', 1, NULL, 29, 1, '2026-01-23 19:21:18', '2026-01-23 19:21:18', NULL),
(24, 'Tienda Genericos SAS', NULL, NULL, NULL, 'tienda_genericos@gmail.com', '3548548456', '3112028952', 1, NULL, 30, 1, '2026-01-23 20:20:09', '2026-02-13 14:26:08', NULL),
(25, 'Raquel', 'Avila', '', 'Bermudes', 'ravila23@gmail.com', '3548548456', '3004546566', 1, NULL, 31, 1, '2026-01-23 20:39:12', '2026-01-23 20:39:12', NULL),
(42, 'Juan', 'Lozano', 'Sebastian', 'Contreras', 'juseloco@gmail.com', '3004987205', '3004987205', 1, NULL, 50, 1, '2026-01-27 15:38:40', '2026-01-27 15:38:40', NULL),
(43, 'Angela', 'prueba', 'Bolivar', 'Contreras', 'abolivar@gmail.com', '3004987205', '3004987205', 1, NULL, 51, 1, '2026-01-27 16:07:21', '2026-01-27 16:07:21', NULL),
(44, 'prueba', 'prueba', 'prueba', 'tenant', 'pruebatenant@gmail.com', '3004987205', '3004987205', 1, NULL, 52, 1, '2026-01-27 16:32:36', '2026-01-27 16:32:36', NULL),
(45, 'CAMILO', 'CERON', 'MANUEL', 'CIFUENTES', 'soluciones@gmail.com', '3009283401', '3123124121', 1, NULL, 53, 1, '2026-01-27 16:48:53', '2026-01-27 16:48:53', NULL),
(46, 'edwin', 'tenant', 'tenant', 'tenant', 'edtenant@gmail.com', '3004987205', '309123912232', 1, NULL, 54, 1, '2026-01-27 17:07:11', '2026-01-27 17:07:11', NULL),
(47, 'Juan', 'prueba', 'Sebastian', 'prueba', 'juseloco21@gmail.com', '3004987205', '3004987205', 1, NULL, 55, 1, '2026-01-27 18:01:46', '2026-01-27 18:01:46', NULL),
(48, 'Juan', 'Lozano', 'Sebastian', 'Contreras', 'tenantJuan@gmail.com', '3004987205', '3004987205', 1, NULL, 56, 1, '2026-01-28 13:09:22', '2026-01-28 13:09:22', NULL),
(49, 'Angela', 'tenant', 'tenant', 'tenant', 'tenantang@gmail.com', '3004987205', '3004987205', 1, NULL, 57, 1, '2026-01-28 13:26:09', '2026-01-28 13:26:09', NULL),
(50, 'prueba', 'tenant', 'Bolivar', 'tenant', 'tenant@gmail.com', '30094351243', '30912391223', 1, NULL, 58, 1, '2026-01-28 13:34:16', '2026-01-28 13:34:16', NULL),
(51, 'Angela', 'Bolivar', 'Johana', 'Mendez', 'juan1234@gmail.com', '3009283401', '309123912232', 1, NULL, 59, 1, '2026-01-28 13:42:17', '2026-01-28 13:42:17', NULL),
(52, 'Angela', 'Sebastian', 'Bolivar', 'Contreras', 'ticsia@gmail.com', '3004987205', '12301230', 1, NULL, 60, 1, '2026-01-28 13:49:25', '2026-01-28 13:49:25', NULL),
(53, 'Juan', 'Johana', 'angela', 'Contreras', 'juseloco213@gmail.com', '3004987205', '3004987205', 1, NULL, 61, 1, '2026-01-28 13:57:47', '2026-01-28 13:57:47', NULL),
(54, 'juan', 'Sebastian', 'angela', 'Contreras', 'juseloco254@gmail.com', '3009283401', '30912391223', 1, NULL, 62, 1, '2026-01-28 14:33:21', '2026-01-28 14:33:21', NULL),
(55, 'juan camilo', 'angela', 'Sebastian', 'Contreras', 'juseloco24@gmail.com', '3004987205', '3004987205', 1, NULL, 63, 1, '2026-01-28 14:40:17', '2026-01-28 15:50:03', NULL),
(56, 'Juan', 'Lozano', 'Sebastian', 'prueba', 'prueba123@gmail.com', '3004987205', '3004987205', 1, NULL, 64, 1, '2026-01-28 14:44:50', '2026-01-28 14:44:50', NULL),
(57, 'Juan', 'Lozano', 'Sebastian', 'Contreras', 'juseloco4561@gmail.com', '3004987205', '3004987205', 1, NULL, 65, 1, '2026-01-28 16:23:28', '2026-01-28 16:23:28', NULL),
(58, 'proveedor', 'proveedor', 'proveedor', 'proveedor', 'proveedor@gmail.com', '3004987205', '3004987205', 1, NULL, 66, 1, '2026-01-28 16:28:38', '2026-01-28 16:28:38', NULL),
(59, 'Angela', 'Sebastian', 'Sebastian', 'Contreras', 'juseloco217@gmail.com', '3009283401', '3123124121', 1, NULL, 67, 1, '2026-01-28 17:50:09', '2026-01-28 17:50:09', NULL),
(60, 'jaimito', 'gregorio', 'julio', 'navas', 'test@gmail.com', '3004987205', '3004987205', 1, NULL, 68, 1, '2026-01-28 19:11:25', '2026-01-28 19:11:25', NULL),
(61, 'Bookbet', NULL, 'Cliente', NULL, 'bookbet@bookbet.com', NULL, NULL, 1, NULL, 3, 1, '2026-01-30 13:38:14', '2026-01-30 13:38:14', NULL),
(62, 'juan', 'lozano', 'sebastian', 'contreras', 'sebastianlozano@gmail.com', '3012300122', '3012300122', 1, NULL, 69, 1, '2026-01-30 15:55:58', '2026-01-30 15:55:58', NULL),
(63, 'Ana', 'Duran', 'Carolina', 'Casas', 'carolina_duran@gmail.com', '3123842021', '3154541918', 1, NULL, 70, 1, '2026-01-30 16:12:01', '2026-01-30 16:12:43', NULL),
(65, 'primer', 'segundo', 'papellido', 'sapellido', 'samuel@gmail.com', '3004987205', '3004987205', 1, NULL, 72, 1, '2026-02-03 16:30:31', '2026-02-03 16:39:18', NULL),
(66, 'Primer Nombre', 'Segundo Nombre', 'Primer Apellido', 'Segundo Apellido', 'primerNombre@gmail.com', '3004987205', '3004987205', 1, NULL, 73, 1, '2026-02-03 16:43:14', '2026-02-03 16:43:14', NULL),
(67, 'Angela', '', 'Bolivar', 'Mendez', 'amendez@gmail.com', '3004987205', '3004987205', 1, NULL, 74, 1, '2026-02-03 16:52:32', '2026-02-03 16:52:32', NULL),
(68, 'Juan', 'prueba', 'tenant', 'Contreras', 'pruebaTenantLozano@gmail.com', '3004987205', '3004987205', 1, NULL, 76, 1, '2026-02-04 15:36:58', '2026-02-04 15:36:58', NULL),
(69, 'nombreU', 'Sebastian', 'Bolivar', 'prueba', 'juselocUno@gmail.com', '', '', 1, NULL, 77, 1, '2026-02-04 16:04:59', '2026-02-04 16:04:59', NULL),
(70, 'EMPRESA SAS', NULL, NULL, NULL, 'empresajuridicasas@gmail.com', '3180453210', '3154541918', 1, NULL, 78, 1, '2026-02-13 14:00:51', '2026-02-13 14:00:51', NULL),
(71, 'AS ANALITICA', NULL, NULL, NULL, 'asanalitica@gmail.com', '3180453210', '3154541918', 1, NULL, 79, 1, '2026-02-13 14:17:30', '2026-02-13 14:17:30', NULL),
(72, 'CARPAINT', NULL, NULL, NULL, 'comercial@carpaint.com.co', '3548548456', '3112028952', 1, NULL, 80, 1, '2026-02-13 14:24:29', '2026-02-13 14:24:29', NULL),
(73, 'Valeria ', '', 'Jimenez', '', 'valeria_jimenez@gmail.com', '3125204521', '3004521414', 1, NULL, 39, 3, '2026-02-13 14:34:49', '2026-02-13 14:34:49', NULL),
(74, 'Brenda', '', 'Suarez', '', 'brendaSuarez@outlook.es', '', '', 1, NULL, 82, 1, '2026-02-13 16:44:29', '2026-02-13 16:44:29', NULL),
(75, 'Maria', 'Alejandra', 'Barbosa', 'quote', 'Mariaquote@gmail.com', '3004987205', '3004987205', 1, NULL, 83, 1, '2026-02-16 19:18:44', '2026-02-16 19:18:44', NULL),
(76, 'ILUMINARIA LTDA', NULL, NULL, NULL, 'iluminaria@gmail.com', '3152408514', '3198510101', 1, NULL, 84, 1, '2026-02-16 20:44:59', '2026-02-16 20:44:59', NULL),
(77, 'RightLeds', NULL, NULL, NULL, 'proveedor_prueba@gmail.com', '3120521215', '', 1, NULL, 85, 1, '2026-03-02 17:05:27', '2026-03-02 17:05:27', NULL),
(78, 'David', '', 'Martinez', '', 'david_martinez@gmail.com', '3120521215', '31250000', 1, NULL, 90, 1, '2026-03-17 19:48:13', '2026-03-17 19:48:13', NULL),
(79, 'Lemon Company SAS', NULL, NULL, NULL, 'lemon_company@outlook.com', '3120521215', '3125000045', 1, NULL, 91, 1, '2026-03-17 19:59:04', '2026-03-17 19:59:04', NULL),
(80, 'Sebastian', 'Sebastian', 'Lozano', 'Contreras', 'jslozanoc98@gmail.com', '3004987205', '30912391223', 1, NULL, 92, 1, '2026-04-09 19:37:36', '2026-04-09 19:37:36', NULL),
(81, 'Sebastian', 'Sebastian', 'Lozano', 'Contreras', 'jslozanoc75@gmail.com', '3004987205', '30912391223', 1, NULL, 93, 1, '2026-04-09 19:44:18', '2026-04-09 19:44:18', NULL),
(82, 'Sebastian', 'Lozano', 'maria', 'Contrtraras', 'jslozanoc68@gmail.com', '3004987205', '3004987205', 1, NULL, 94, 1, '2026-04-09 19:52:24', '2026-04-09 19:52:24', NULL),
(83, 'Maria', 'Fernanda', 'Fernanda', 'Contrerass', 'jslozanoc4@gmail.com', '3219802489', '3123124121', 1, NULL, 95, 1, '2026-04-09 20:05:38', '2026-04-09 20:05:38', NULL),
(84, 'carlos', 'alberto', 'Alberto', 'Perez', 'jslozanoc65@gmail.com', '3004987234', '3004987234', 1, NULL, 96, 1, '2026-04-09 20:16:46', '2026-04-09 20:16:46', NULL),
(85, 'Sebastian', 'Alejandra', 'buitrado', 'perez', 'jslozanoc129@gmail.com', '3004987205', '3004987205', 1, NULL, 97, 1, '2026-04-09 20:24:44', '2026-04-09 20:24:44', NULL),
(86, 'camilo', 'andres', 'romero', 'bergano', 'camilo@gmail.com', '', '', 1, NULL, 98, 1, '2026-04-09 20:37:41', '2026-04-09 20:37:41', NULL),
(87, 'Diana', 'Fernanda', 'Herrera', 'Gonzales', 'diana_herrera@gmail.com', '', '', 1, NULL, 99, 1, '2026-04-10 13:20:19', '2026-04-10 13:20:19', NULL),
(88, 'SOM & GRAND SAS', NULL, NULL, NULL, 'comercial_som_grand@gmail.com', '', '', 1, NULL, 100, 1, '2026-04-10 14:06:12', '2026-04-10 14:06:12', NULL),
(89, 'Claudia', 'Raquel', 'Jimenez', 'Fernandez', 'claudia_jimenez@outlook.es', '', '', 1, NULL, 101, 1, '2026-04-10 17:40:17', '2026-04-10 17:40:17', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vnt_delivery_types`
--

CREATE TABLE `vnt_delivery_types` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vnt_detail_petty_cash`
--

CREATE TABLE `vnt_detail_petty_cash` (
  `id` int NOT NULL,
  `status` tinyint DEFAULT '1',
  `value` decimal(11,2) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `pettyCashId` int DEFAULT NULL,
  `reasonPettyCashId` int DEFAULT NULL,
  `methodPaymentId` int DEFAULT NULL,
  `invoiceId` int DEFAULT NULL,
  `observations` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `vnt_detail_petty_cash`
--

INSERT INTO `vnt_detail_petty_cash` (`id`, `status`, `value`, `created_at`, `updated_at`, `deleted_at`, `pettyCashId`, `reasonPettyCashId`, `methodPaymentId`, `invoiceId`, `observations`) VALUES
(3, 1, 32000.00, '2025-11-20 21:01:52', NULL, NULL, 14, 5, 1, NULL, 'APERTURA'),
(4, 1, 250000.00, '2025-11-20 21:08:49', '2025-11-20 21:08:49', NULL, 16, 5, 1, NULL, 'Apertura de caja'),
(5, 0, 25600.00, '2025-11-24 21:56:46', '2025-11-28 21:12:01', NULL, 16, 1, 1, NULL, 'factura'),
(6, 0, 6520.00, '2025-11-24 21:58:16', '2025-11-28 21:00:04', NULL, 16, 2, 2, NULL, 'egreso'),
(7, 1, 25600.00, '2025-11-25 17:46:08', '2025-11-28 20:58:44', NULL, 16, 1, 4, NULL, 'VENTA '),
(8, 1, 9650.00, '2025-11-25 19:34:09', '2025-11-28 20:58:29', NULL, 16, 1, 1, NULL, 'Venta 2:34 pm'),
(9, 1, 63520.00, '2025-11-25 19:57:17', '2025-11-27 13:50:31', NULL, 16, 3, 3, NULL, 'PAGO'),
(10, 1, 63000.00, '2025-11-26 13:38:53', '2025-11-27 14:02:17', NULL, 16, 1, 10, NULL, 'VENTA'),
(11, 1, 600821.00, '2025-11-26 18:03:43', '2025-11-26 18:03:43', NULL, 16, 1, 1, NULL, 'Venta efectivo'),
(12, 1, 17695.00, '2025-11-26 18:04:55', '2025-11-26 18:04:55', NULL, 16, 2, 2, NULL, 'Devolución Vale empleados Transferencia.'),
(13, 1, 59997.00, '2025-11-26 18:07:01', '2025-11-27 20:57:28', NULL, 16, 6, 4, NULL, 'Anticipo tarjeta de crédito'),
(14, 1, 35247.00, '2025-11-26 18:07:51', '2025-11-26 18:07:51', NULL, 16, 1, 10, NULL, 'Venta Tarjeta de crédito'),
(15, 1, 7500.00, '2025-11-26 18:08:55', '2025-11-26 18:08:55', NULL, 16, 2, 11, NULL, 'Devolución de vale empleados - NEQUI'),
(16, 1, 62000.00, '2025-11-26 18:09:35', '2025-11-26 18:09:35', NULL, 16, 6, 12, NULL, 'Anticipo Daviplata'),
(17, 1, 12999.00, '2025-11-26 19:10:10', '2025-11-26 19:10:10', NULL, 16, 3, 1, NULL, 'Egreso Pago de factura.'),
(18, 1, 34000.00, '2025-11-26 19:11:05', '2025-11-26 19:11:05', NULL, 16, 4, 2, NULL, 'Egreso transferencia vale empleado.'),
(19, 1, 11885.00, '2025-11-26 19:11:58', '2025-11-26 19:11:58', NULL, 16, 3, 4, NULL, 'Pago de factura - tarjeta de crédito'),
(20, 1, 337757.00, '2025-11-26 19:13:01', '2025-11-27 13:51:37', NULL, 16, 4, 10, NULL, 'Vale empleado egreso - tarjeta debito.'),
(21, 1, 140498.00, '2025-11-26 19:13:53', '2025-11-26 21:51:40', NULL, 16, 3, 11, NULL, 'Pago factura NEQUI'),
(22, 1, 4300.00, '2025-11-26 19:14:37', '2025-11-26 20:55:33', NULL, 16, 4, 12, NULL, 'Egreso vale empleado - daviplata'),
(23, 1, 9541.00, '2025-11-27 17:48:27', '2025-11-27 17:48:27', NULL, 2, 1, 1, NULL, NULL),
(24, 1, 2306.00, '2025-11-27 17:48:27', '2025-11-27 17:48:27', NULL, 2, 1, 1, NULL, NULL),
(25, 1, 68094.00, '2025-11-27 17:48:27', '2025-11-27 17:48:27', NULL, 2, 3, 1, NULL, NULL),
(26, 1, 66815.00, '2025-11-27 17:48:27', '2025-11-27 17:48:27', NULL, 2, 1, 1, NULL, NULL),
(27, 1, 50000.00, '2025-11-27 17:48:27', '2025-11-27 17:48:27', NULL, 2, 2, 1, NULL, 'FLETE CALI INTERRAPIDISIMO'),
(28, 1, 50000.00, '2025-11-27 17:48:27', '2025-11-27 17:48:27', NULL, 2, 2, 1, NULL, NULL),
(29, 1, 80504.00, '2025-11-27 17:48:27', '2025-11-27 17:48:27', NULL, 2, 1, 1, NULL, NULL),
(30, 1, 19211.00, '2025-11-27 17:48:27', '2025-11-27 17:48:27', NULL, 2, 6, 2, NULL, NULL),
(31, 1, 6075.00, '2025-11-27 17:48:27', '2025-11-27 17:48:27', NULL, 2, 1, 5, NULL, NULL),
(32, 1, 26896.00, '2025-11-27 17:48:27', '2025-11-27 17:48:27', NULL, 2, 1, 1, NULL, NULL),
(33, 1, 39431.00, '2025-11-27 17:48:27', '2025-11-27 17:48:27', NULL, 2, 2, 4, NULL, NULL),
(34, 1, 22411.00, '2025-11-27 17:48:27', '2025-11-27 17:48:27', NULL, 2, 1, 1, NULL, NULL),
(35, 1, 4637.00, '2025-11-27 17:48:27', '2025-11-27 17:48:27', NULL, 2, 1, 1, NULL, NULL),
(36, 1, 10000.00, '2025-11-27 17:48:27', '2025-11-27 17:48:27', NULL, 2, 5, 1, NULL, 'HELADOS'),
(37, 1, 164403.00, '2025-11-27 17:48:27', '2025-11-27 17:48:27', NULL, 2, 1, 1, NULL, NULL),
(38, 1, 166926.00, '2025-11-27 17:48:27', '2025-11-27 17:48:27', NULL, 2, 1, 1, NULL, NULL),
(39, 1, 5998.00, '2025-11-27 17:48:27', '2025-11-27 17:48:27', NULL, 2, 1, 1, NULL, NULL),
(40, 1, 40599.00, '2025-11-27 17:48:27', '2025-11-27 17:48:27', NULL, 2, 1, 1, NULL, NULL),
(41, 1, 10000.00, '2025-11-27 17:48:27', '2025-11-27 17:48:27', NULL, 2, 2, 1, NULL, NULL),
(42, 1, 19994.00, '2025-11-27 17:48:27', '2025-11-27 17:48:27', NULL, 2, 1, 1, NULL, NULL),
(43, 1, 6500.00, '2025-11-27 17:48:27', '2025-11-27 17:48:27', NULL, 2, 1, 1, NULL, NULL),
(44, 1, 7499.00, '2025-11-27 17:48:27', '2025-11-27 17:48:27', NULL, 2, 4, 1, NULL, NULL),
(45, 1, 42704.00, '2025-11-27 17:48:27', '2025-11-27 17:48:27', NULL, 2, 1, 1, NULL, NULL),
(46, 1, 9002.00, '2025-11-27 17:48:27', '2025-11-27 17:48:27', NULL, 2, 1, 1, NULL, NULL),
(47, 1, 80999.00, '2025-11-27 17:48:27', '2025-11-27 17:48:27', NULL, 2, 1, 1, NULL, NULL),
(48, 1, 9001.00, '2025-11-27 17:48:27', '2025-11-27 17:48:27', NULL, 2, 1, 1, NULL, NULL),
(49, 1, 36000.00, '2025-11-27 17:48:27', '2025-11-27 17:48:27', NULL, 2, 1, 1, NULL, NULL),
(50, 1, 23800.00, '2025-11-27 17:48:27', '2025-11-27 17:48:27', NULL, 2, 2, 1, NULL, NULL),
(51, 1, 16949.00, '2025-11-27 17:48:27', '2025-11-27 17:48:27', NULL, 2, 2, 1, NULL, NULL),
(52, 1, 25000.00, '2025-11-27 17:48:27', '2025-11-27 17:48:27', NULL, 2, 2, 1, NULL, NULL),
(53, 1, 13502.00, '2025-11-27 17:48:27', '2025-11-27 17:48:27', NULL, 2, 1, 1, NULL, NULL),
(54, 1, 1000.00, '2025-11-27 17:48:27', '2025-11-27 17:48:27', NULL, 2, 1, 1, NULL, NULL),
(55, 1, 15000.00, '2025-11-27 17:48:27', '2025-11-27 17:48:27', NULL, 2, 3, 1, NULL, NULL),
(56, 1, 34998.00, '2025-11-27 17:48:27', '2025-11-27 17:48:27', NULL, 2, 1, 1, NULL, NULL),
(57, 1, 9998.00, '2025-11-27 17:48:27', '2025-11-27 17:48:27', NULL, 2, 4, 1, NULL, NULL),
(58, 1, 6500.00, '2025-11-27 17:48:27', '2025-11-27 17:48:27', NULL, 2, 1, 1, NULL, NULL),
(59, 1, 30000.00, '2025-11-27 17:48:27', '2025-11-27 17:48:27', NULL, 2, 1, 1, NULL, NULL),
(60, 1, 22200.00, '2025-11-27 17:48:27', '2025-11-27 17:48:27', NULL, 2, 3, 1, NULL, NULL),
(61, 1, 5000.00, '2025-11-27 17:48:27', '2025-11-27 17:48:27', NULL, 2, 1, 1, NULL, NULL),
(62, 1, 8000.00, '2025-11-27 17:48:27', '2025-11-27 17:48:27', NULL, 2, 1, 1, NULL, NULL),
(63, 1, 4471.00, '2025-11-27 19:09:53', '2025-11-27 19:09:53', NULL, 8, 1, 1, NULL, NULL),
(64, 1, 45000.00, '2025-11-27 19:09:54', '2025-11-27 19:09:54', NULL, 8, 1, 2, NULL, NULL),
(65, 1, 52703.00, '2025-11-27 19:09:54', '2025-11-27 19:09:54', NULL, 8, 1, 1, NULL, NULL),
(66, 1, 34113.00, '2025-11-27 19:09:54', '2025-11-27 19:09:54', NULL, 8, 1, 1, NULL, NULL),
(67, 1, 5000.00, '2025-11-27 19:09:54', '2025-11-27 19:09:54', NULL, 8, 1, 2, NULL, NULL),
(68, 1, 68920.00, '2025-11-27 19:09:54', '2025-11-27 19:09:54', NULL, 8, 1, 1, NULL, NULL),
(69, 1, 16501.00, '2025-11-27 19:09:54', '2025-11-27 19:09:54', NULL, 8, 1, 1, NULL, NULL),
(70, 1, 46410.00, '2025-11-27 19:09:55', '2025-11-27 19:09:55', NULL, 8, 1, 1, NULL, NULL),
(71, 1, 5000.00, '2025-11-27 19:09:55', '2025-11-27 19:09:55', NULL, 8, 2, 1, NULL, NULL),
(72, 1, 16001.00, '2025-11-27 19:09:55', '2025-11-27 19:09:55', NULL, 8, 2, 1, NULL, NULL),
(73, 1, 6038.00, '2025-11-27 19:09:56', '2025-11-27 19:09:56', NULL, 8, 2, 1, NULL, NULL),
(74, 1, 22134.00, '2025-11-27 19:09:56', '2025-11-27 19:09:56', NULL, 8, 2, 1, NULL, NULL),
(75, 1, 4000.00, '2025-11-27 19:09:56', '2025-11-27 19:09:56', NULL, 8, 2, 1, NULL, NULL),
(76, 1, 3521.00, '2025-11-27 19:09:56', '2025-11-27 19:09:56', NULL, 8, 2, 1, NULL, NULL),
(77, 1, 6562.00, '2025-11-27 19:09:56', '2025-11-27 19:09:56', NULL, 8, 2, 1, NULL, NULL),
(78, 1, 23988.00, '2025-11-27 19:09:57', '2025-11-27 19:09:57', NULL, 8, 2, 1, NULL, NULL),
(79, 1, 119988.00, '2025-11-27 19:09:57', '2025-11-27 19:09:57', NULL, 8, 2, 1, NULL, NULL),
(80, 1, 13000.00, '2025-11-27 19:09:57', '2025-11-27 19:09:57', NULL, 8, 2, 1, NULL, NULL),
(81, 1, 6838.00, '2025-11-27 19:09:57', '2025-11-27 19:09:57', NULL, 8, 2, 1, NULL, NULL),
(82, 1, 88792.00, '2025-11-27 19:09:57', '2025-11-27 19:09:57', NULL, 8, 2, 2, NULL, NULL),
(83, 1, 56569.00, '2025-11-27 19:09:57', '2025-11-27 19:09:57', NULL, 8, 2, 1, NULL, NULL),
(84, 1, 77061.00, '2025-11-27 19:09:58', '2025-11-27 19:09:58', NULL, 8, 2, 1, NULL, NULL),
(85, 1, 50500.00, '2025-11-27 19:09:58', '2025-11-27 19:09:58', NULL, 8, 3, 10, NULL, NULL),
(86, 1, 12024.00, '2025-11-27 19:09:58', '2025-11-27 19:09:58', NULL, 8, 3, 1, NULL, NULL),
(87, 1, 20002.00, '2025-11-27 19:09:58', '2025-11-27 19:09:58', NULL, 8, 3, 1, NULL, NULL),
(88, 1, 300000.00, '2025-11-27 19:09:58', '2025-11-27 19:09:58', NULL, 8, 5, 2, NULL, NULL),
(89, 1, 55002.00, '2025-11-27 19:09:58', '2025-11-27 19:09:58', NULL, 8, 3, 1, NULL, NULL),
(90, 1, 10001.00, '2025-11-27 19:09:58', '2025-11-27 19:09:58', NULL, 8, 3, 1, NULL, NULL),
(91, 1, 20999.00, '2025-11-27 19:09:58', '2025-11-27 19:09:58', NULL, 8, 3, 1, NULL, NULL),
(92, 1, 20999.00, '2025-11-27 19:09:59', '2025-11-27 19:09:59', NULL, 8, 3, 1, NULL, NULL),
(93, 1, 12000.00, '2025-11-27 19:09:59', '2025-11-27 19:09:59', NULL, 8, 3, 2, NULL, NULL),
(94, 1, 7000.00, '2025-11-27 19:09:59', '2025-11-27 19:09:59', NULL, 8, 4, 1, NULL, NULL),
(95, 1, 600.00, '2025-11-27 19:09:59', '2025-11-27 19:09:59', NULL, 8, 4, 1, NULL, NULL),
(96, 1, 7501.00, '2025-11-27 19:09:59', '2025-11-27 19:09:59', NULL, 8, 4, 1, NULL, NULL),
(97, 1, 13501.00, '2025-11-27 19:09:59', '2025-11-27 19:09:59', NULL, 8, 4, 1, NULL, NULL),
(98, 1, 8000.00, '2025-11-27 19:10:00', '2025-11-27 19:10:00', NULL, 8, 4, 1, NULL, NULL),
(99, 1, 9998.00, '2025-11-27 19:10:00', '2025-11-27 19:10:00', NULL, 8, 4, 2, NULL, NULL),
(100, 1, 54999.00, '2025-11-27 19:10:00', '2025-11-27 19:10:00', NULL, 8, 4, 2, NULL, NULL),
(101, 1, 25000.00, '2025-11-27 19:10:00', '2025-11-27 19:10:00', NULL, 8, 4, 1, NULL, NULL),
(102, 1, 5000.00, '2025-11-27 19:10:00', '2025-11-27 19:10:00', NULL, 8, 4, 1, NULL, NULL),
(103, 1, 203903.00, '2025-11-27 19:10:00', '2025-11-27 19:10:00', NULL, 8, 4, 2, NULL, NULL),
(104, 1, 10999.00, '2025-11-27 19:10:00', '2025-11-27 19:10:00', NULL, 8, 4, 1, NULL, NULL),
(105, 1, 67973.00, '2025-11-27 19:10:01', '2025-11-27 19:10:01', NULL, 8, 1, 1, NULL, NULL),
(106, 1, 9500.00, '2025-11-27 19:10:01', '2025-11-27 19:10:01', NULL, 8, 1, 1, NULL, NULL),
(107, 1, 22003.00, '2025-11-27 19:10:01', '2025-11-27 19:10:01', NULL, 8, 1, 1, NULL, NULL),
(108, 1, 3500.00, '2025-11-27 19:10:01', '2025-11-27 19:10:01', NULL, 8, 1, 1, NULL, NULL),
(109, 1, 29436.00, '2025-11-27 19:10:01', '2025-11-27 19:10:01', NULL, 8, 1, 1, NULL, NULL),
(110, 1, 57534.00, '2025-11-27 19:10:01', '2025-11-27 19:10:01', NULL, 8, 1, 1, NULL, NULL),
(111, 1, 1190.00, '2025-11-27 19:10:01', '2025-11-27 19:10:01', NULL, 8, 1, 1, NULL, NULL),
(112, 1, 71390.00, '2025-11-27 19:10:02', '2025-11-27 19:10:02', NULL, 8, 1, 4, NULL, NULL),
(113, 1, 64996.61, '2025-11-27 19:16:51', '2025-11-27 19:16:51', NULL, 14, 1, 1, NULL, NULL),
(114, 1, 5383.56, '2025-11-27 19:16:52', '2025-11-27 19:16:52', NULL, 14, 1, 1, NULL, NULL),
(115, 1, 27480.99, '2025-11-27 19:16:52', '2025-11-27 19:16:52', NULL, 14, 1, 1, NULL, NULL),
(116, 1, 10499.37, '2025-11-27 19:16:52', '2025-11-27 19:16:52', NULL, 14, 1, 1, NULL, NULL),
(117, 1, 57725.71, '2025-11-27 19:16:53', '2025-11-27 19:16:53', NULL, 14, 1, 10, NULL, NULL),
(118, 1, 2999.99, '2025-11-27 19:16:53', '2025-11-27 19:16:53', NULL, 14, 1, 1, NULL, NULL),
(119, 1, 47971.28, '2025-11-27 19:16:53', '2025-11-27 19:16:53', NULL, 14, 1, 1, NULL, NULL),
(120, 1, 94797.78, '2025-11-27 19:16:53', '2025-11-27 19:16:53', NULL, 14, 1, 1, NULL, NULL),
(121, 1, 27128.43, '2025-11-27 19:16:53', '2025-11-27 19:16:53', NULL, 14, 1, 1, NULL, NULL),
(122, 1, 3499.79, '2025-11-27 19:16:54', '2025-11-27 19:16:54', NULL, 14, 1, 1, NULL, NULL),
(123, 1, 42003.43, '2025-11-27 19:16:54', '2025-11-27 19:16:54', NULL, 14, 1, 2, NULL, NULL),
(124, 1, 39271.64, '2025-11-27 19:16:54', '2025-11-27 19:16:54', NULL, 14, 1, 1, NULL, NULL),
(125, 1, 6902.00, '2025-11-27 19:16:54', '2025-11-27 19:16:54', NULL, 14, 1, 1, NULL, NULL),
(126, 1, 4000.78, '2025-11-27 19:16:54', '2025-11-27 19:16:54', NULL, 14, 1, 1, NULL, NULL),
(127, 1, 31173.66, '2025-11-27 19:16:54', '2025-11-27 19:16:54', NULL, 14, 1, 1, NULL, NULL),
(128, 1, 23984.45, '2025-11-27 19:16:55', '2025-11-27 19:16:55', NULL, 14, 1, 1, NULL, NULL),
(129, 1, 240000.68, '2025-11-27 19:16:55', '2025-11-27 19:16:55', NULL, 14, 5, 1, NULL, NULL),
(130, 1, 48385.40, '2025-11-27 19:16:55', '2025-11-27 19:16:55', NULL, 14, 2, 1, NULL, NULL),
(131, 1, 13230.42, '2025-11-27 19:16:55', '2025-11-27 19:16:55', NULL, 14, 2, 1, NULL, NULL),
(132, 1, 5000.38, '2025-11-27 19:16:55', '2025-11-27 19:16:55', NULL, 14, 2, 1, NULL, NULL),
(133, 1, 28067.34, '2025-11-27 19:16:55', '2025-11-27 19:16:55', NULL, 14, 2, 1, NULL, NULL),
(134, 1, 21445.00, '2025-11-27 19:16:55', '2025-11-27 19:16:55', NULL, 14, 2, 1, NULL, NULL),
(135, 1, 10000.76, '2025-11-27 19:16:55', '2025-11-27 19:16:55', NULL, 14, 2, 1, NULL, NULL),
(136, 1, 8159.83, '2025-11-27 19:16:56', '2025-11-27 19:16:56', NULL, 14, 2, 1, NULL, NULL),
(137, 1, 2501.38, '2025-11-27 19:16:56', '2025-11-27 19:16:56', NULL, 14, 2, 1, NULL, NULL),
(138, 1, 12197.50, '2025-11-27 19:16:56', '2025-11-27 19:16:56', NULL, 14, 2, 1, NULL, NULL),
(139, 1, 18811.52, '2025-11-27 19:16:56', '2025-11-27 19:16:56', NULL, 14, 3, 1, NULL, NULL),
(140, 1, 29483.44, '2025-11-27 19:16:56', '2025-11-27 19:16:56', NULL, 14, 3, 1, NULL, NULL),
(141, 1, 5166.98, '2025-11-27 19:16:56', '2025-11-27 19:16:56', NULL, 14, 3, 1, NULL, NULL),
(142, 1, 24482.69, '2025-11-27 19:16:57', '2025-11-27 19:16:57', NULL, 14, 3, 1, NULL, NULL),
(143, 1, 7594.00, '2025-11-27 19:16:57', '2025-11-27 19:16:57', NULL, 14, 3, 1, NULL, NULL),
(144, 1, 37181.55, '2025-11-27 19:16:57', '2025-11-27 19:16:57', NULL, 14, 3, 1, NULL, NULL),
(145, 1, 42762.65, '2025-11-27 19:16:57', '2025-11-27 19:16:57', NULL, 14, 3, 1, NULL, NULL),
(146, 1, 5495.42, '2025-11-27 19:16:57', '2025-11-27 19:16:57', NULL, 14, 3, 1, NULL, NULL),
(147, 1, 24336.69, '2025-11-27 19:16:57', '2025-11-27 19:16:57', NULL, 14, 4, 1, NULL, NULL),
(148, 1, 32709.53, '2025-11-27 19:16:58', '2025-11-27 19:16:58', NULL, 14, 4, 1, NULL, NULL),
(149, 1, 52000.62, '2025-11-27 19:16:58', '2025-11-27 19:16:58', NULL, 14, 4, 2, NULL, NULL),
(150, 1, 2000.39, '2025-11-27 19:16:58', '2025-11-27 19:16:58', NULL, 14, 4, 1, NULL, NULL),
(151, 1, 5857.18, '2025-11-27 19:16:58', '2025-11-27 19:16:58', NULL, 14, 4, 1, NULL, NULL),
(152, 1, 18034.45, '2025-11-27 19:16:58', '2025-11-27 19:16:58', NULL, 14, 4, 1, NULL, NULL),
(153, 1, 4599.35, '2025-11-27 19:16:59', '2025-11-27 19:16:59', NULL, 14, 4, 1, NULL, NULL),
(154, 1, 255850.00, '2025-11-27 19:16:59', '2025-11-27 19:16:59', NULL, 14, 4, 1, NULL, NULL),
(155, 1, 7188.79, '2025-11-27 19:16:59', '2025-11-27 19:16:59', NULL, 14, 4, 1, NULL, NULL),
(156, 1, 12042.00, '2025-11-27 19:16:59', '2025-11-27 19:16:59', NULL, 14, 1, 1, NULL, NULL),
(157, 1, 35803.53, '2025-11-27 19:16:59', '2025-11-27 19:16:59', NULL, 14, 1, 10, NULL, NULL),
(158, 1, 9999.20, '2025-11-27 19:16:59', '2025-11-27 19:16:59', NULL, 14, 1, 1, NULL, NULL),
(159, 1, 11013.45, '2025-11-27 19:16:59', '2025-11-27 19:16:59', NULL, 14, 1, 1, NULL, NULL),
(160, 1, 28825.41, '2025-11-27 19:16:59', '2025-11-27 19:16:59', NULL, 14, 1, 2, NULL, NULL),
(161, 1, 4499.39, '2025-11-27 19:17:00', '2025-11-27 19:17:00', NULL, 14, 1, 1, NULL, NULL),
(162, 1, 24972.15, '2025-11-27 19:17:00', '2025-11-27 19:17:00', NULL, 14, 1, 1, NULL, NULL),
(163, 1, 19996.76, '2025-11-27 19:17:00', '2025-11-27 19:17:00', NULL, 14, 1, 1, NULL, NULL),
(164, 1, 18477.00, '2025-11-27 19:17:00', '2025-11-27 19:17:00', NULL, 14, 1, 2, NULL, NULL),
(165, 1, 224334.66, '2025-11-27 19:17:00', '2025-11-27 19:17:00', NULL, 14, 1, 2, NULL, NULL),
(166, 1, 15000.00, '2025-11-28 17:34:29', '2025-11-28 17:34:29', NULL, 16, 4, 1, NULL, 'Vale empleado'),
(167, 1, 4500.00, '2025-11-28 19:10:50', '2025-11-28 19:10:50', NULL, 16, 1, 2, NULL, 'Venta.'),
(168, 1, 6500.00, '2025-11-28 19:17:15', '2025-11-28 19:17:15', NULL, 16, 6, 2, NULL, 'Anticipo'),
(169, 1, 25300.00, '2025-11-28 19:19:04', '2025-11-28 19:19:04', NULL, 16, 1, 11, NULL, 'ventas'),
(170, 1, 5555.00, '2025-11-28 19:50:04', '2025-11-28 19:50:04', NULL, 16, 3, 1, NULL, 'll'),
(171, 1, 65200.00, '2025-11-28 20:00:41', '2025-11-28 20:00:41', NULL, 16, 6, 2, NULL, 'ANTICIPO '),
(172, 1, 78600.00, '2025-11-28 20:04:02', '2025-11-28 20:04:02', NULL, 16, 6, 10, NULL, 'Anticipo venta #4560'),
(173, 1, 75400.00, '2025-11-28 20:06:49', '2025-11-28 20:06:49', NULL, 16, 2, 1, NULL, 'iuiu'),
(174, 0, 11200.00, '2025-11-28 20:07:35', '2026-01-21 16:17:48', NULL, 16, 3, 1, NULL, '5555'),
(175, 1, 5600.00, '2025-11-28 20:12:02', '2025-11-28 20:12:02', NULL, 16, 3, 11, NULL, 'PAGO FACTURA'),
(176, 0, 550000.00, '2025-11-28 20:18:20', '2026-01-21 16:17:43', NULL, 16, 3, 12, NULL, 'Pago factura #456201482'),
(177, 1, 78000.00, '2025-11-28 21:12:51', '2025-11-28 21:12:51', NULL, 16, 6, 2, NULL, 'Anticipo venta'),
(178, 1, 19600.00, '2025-12-03 16:46:32', '2025-12-03 16:46:32', NULL, 16, 1, 10, NULL, 'Venta #1085'),
(180, 1, 8500.00, '2025-12-05 20:15:23', '2025-12-05 20:15:23', NULL, 16, 1, 10, NULL, 'VENTA'),
(182, 1, 11160.00, '2026-01-21 16:16:42', '2026-01-21 16:16:42', NULL, 16, 1, 12, NULL, 'Pago'),
(183, 1, 25555.00, '2026-01-21 19:59:54', '2026-01-21 19:59:54', NULL, 18, 5, 1, NULL, 'Apertura de caja'),
(184, 1, 56000.00, '2026-01-21 20:41:01', '2026-01-21 20:41:01', NULL, 18, 1, 2, NULL, 'VENTA #1'),
(185, 1, 6900.00, '2026-01-21 20:41:45', '2026-01-21 20:41:45', NULL, 18, 6, 1, NULL, 'Anticipo de venta #784'),
(186, 1, 28900.00, '2026-01-21 20:42:20', '2026-01-21 20:42:20', NULL, 18, 1, 4, NULL, 'Venta tarjeta de credito.'),
(187, 1, 32500.00, '2026-01-21 20:43:25', '2026-01-21 20:43:25', NULL, 18, 6, 10, NULL, 'Anticipo venta #093'),
(188, 1, 19800.00, '2026-01-21 20:44:02', '2026-01-21 20:44:02', NULL, 18, 1, 11, NULL, 'Venta desde nequi'),
(189, 0, 169000.00, '2026-01-29 15:11:55', '2026-01-29 15:12:09', NULL, 18, 1, 1, NULL, 'Venta #41'),
(190, 1, 15000.00, '2026-01-29 15:13:48', '2026-01-29 15:13:48', NULL, 18, 4, 1, NULL, 'Caja menor');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vnt_detail_quotes`
--

CREATE TABLE `vnt_detail_quotes` (
  `id` int NOT NULL,
  `quantity` int NOT NULL,
  `tax` int NOT NULL,
  `value` decimal(10,0) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `quoteId` int DEFAULT NULL,
  `itemId` int DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `priceList` int NOT NULL,
  `price_label` varchar(180) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `vnt_detail_quotes`
--

INSERT INTO `vnt_detail_quotes` (`id`, `quantity`, `tax`, `value`, `created_at`, `updated_at`, `quoteId`, `itemId`, `description`, `priceList`, `price_label`) VALUES
(1, 1, 0, 15000, '2025-11-24 20:26:15', '2025-11-24 20:26:15', 6, 16, 'LIBRO INFANTIL', 15000, NULL),
(2, 2, 0, 15000, '2025-11-24 20:26:15', '2025-11-24 20:26:15', 6, 15, 'DESTORNILLADOR', 15000, NULL),
(3, 2, 0, 15000, '2025-11-24 20:26:15', '2025-11-24 20:26:15', 6, 14, 'TENIS NIKE TALLA 40', 15000, NULL),
(4, 1, 0, 15000, '2025-11-24 20:26:16', '2025-11-24 20:26:16', 6, 13, 'TENIS NIKE TALLA 39', 15000, NULL),
(5, 1, 0, 15000, '2025-11-24 20:26:16', '2025-11-24 20:26:16', 6, 12, 'TENIS NIKE TALLA 38', 15000, NULL),
(6, 1, 0, 15000, '2025-11-24 20:27:22', '2025-11-24 20:27:22', 7, 16, 'LIBRO INFANTIL', 15000, NULL),
(7, 1, 0, 15000, '2025-11-24 20:27:22', '2025-11-24 20:27:22', 7, 15, 'DESTORNILLADOR', 15000, NULL),
(8, 1, 0, 15000, '2025-11-24 20:32:53', '2025-11-24 20:32:53', 8, 11, 'ALMOHADA', 15000, NULL),
(9, 1, 0, 15000, '2025-11-24 20:32:53', '2025-11-24 20:32:53', 8, 5, 'BOTAS NEGRAS TIMBERLAND', 15000, NULL),
(10, 1, 0, 15000, '2025-11-24 20:32:53', '2025-11-24 20:32:53', 8, 6, 'PARLANTE AZUL', 15000, NULL),
(11, 1, 0, 15000, '2025-11-24 20:32:54', '2025-11-24 20:32:54', 8, 14, 'TENIS NIKE TALLA 40', 15000, NULL),
(12, 1, 0, 15000, '2025-11-24 20:32:54', '2025-11-24 20:32:54', 8, 15, 'DESTORNILLADOR', 15000, NULL),
(13, 1, 0, 15000, '2025-11-24 20:35:19', '2025-11-24 20:35:19', 9, 12, 'TENIS NIKE TALLA 38', 15000, NULL),
(14, 1, 0, 15000, '2025-11-25 14:09:23', '2025-11-25 14:09:23', 15, 15, 'DESTORNILLADOR', 15000, NULL),
(15, 1, 0, 15000, '2025-11-25 14:09:23', '2025-11-25 14:09:23', 15, 14, 'TENIS NIKE TALLA 40', 15000, NULL),
(16, 1, 0, 15000, '2025-11-25 14:09:23', '2025-11-25 14:09:23', 15, 13, 'TENIS NIKE TALLA 39', 15000, NULL),
(17, 1, 0, 15000, '2025-11-25 14:09:23', '2025-11-25 14:09:23', 15, 6, 'PARLANTE AZUL', 15000, NULL),
(18, 1, 0, 15000, '2025-11-25 14:14:59', '2025-11-25 14:14:59', 17, 15, 'DESTORNILLADOR', 15000, NULL),
(19, 1, 0, 15000, '2025-11-25 14:14:59', '2025-11-25 14:14:59', 17, 6, 'PARLANTE AZUL', 15000, NULL),
(20, 1, 0, 15000, '2025-11-25 14:15:00', '2025-11-25 14:15:00', 17, 13, 'TENIS NIKE TALLA 39', 15000, NULL),
(21, 1, 0, 15000, '2025-11-25 14:16:59', '2025-11-25 14:16:59', 18, 12, 'TENIS NIKE TALLA 38', 15000, NULL),
(22, 1, 0, 15000, '2025-11-25 14:16:59', '2025-11-25 14:16:59', 18, 13, 'TENIS NIKE TALLA 39', 15000, NULL),
(23, 1, 0, 15000, '2025-11-25 14:16:59', '2025-11-25 14:16:59', 18, 14, 'TENIS NIKE TALLA 40', 15000, NULL),
(24, 1, 0, 15000, '2025-11-25 14:27:39', '2025-11-25 14:27:39', 19, 12, 'TENIS NIKE TALLA 38', 15000, NULL),
(25, 1, 0, 15000, '2025-11-25 14:27:39', '2025-11-25 14:27:39', 19, 13, 'TENIS NIKE TALLA 39', 15000, NULL),
(26, 1, 0, 15000, '2025-11-25 14:27:39', '2025-11-25 14:27:39', 19, 14, 'TENIS NIKE TALLA 40', 15000, NULL),
(27, 1, 0, 15000, '2025-11-25 14:33:11', '2025-11-25 14:33:11', 20, 13, 'TENIS NIKE TALLA 39', 15000, NULL),
(28, 1, 0, 15000, '2025-11-25 14:33:11', '2025-11-25 14:33:11', 20, 14, 'TENIS NIKE TALLA 40', 15000, NULL),
(29, 1, 0, 15000, '2025-11-25 14:33:12', '2025-11-25 14:33:12', 20, 6, 'PARLANTE AZUL', 15000, NULL),
(30, 1, 0, 15000, '2025-11-25 14:33:12', '2025-11-25 14:33:12', 20, 15, 'DESTORNILLADOR', 15000, NULL),
(31, 1, 0, 15000, '2025-11-25 14:36:53', '2025-11-25 14:36:53', 21, 13, 'TENIS NIKE TALLA 39', 15000, NULL),
(32, 1, 0, 15000, '2025-11-25 14:36:54', '2025-11-25 14:36:54', 21, 14, 'TENIS NIKE TALLA 40', 15000, NULL),
(33, 1, 0, 15000, '2025-11-25 14:38:21', '2025-11-25 14:38:21', 22, 12, 'TENIS NIKE TALLA 38', 15000, NULL),
(34, 1, 0, 15000, '2025-11-25 14:38:21', '2025-11-25 14:38:21', 22, 13, 'TENIS NIKE TALLA 39', 15000, NULL),
(35, 1, 0, 15000, '2025-11-25 14:41:24', '2025-11-25 14:41:24', 23, 12, 'TENIS NIKE TALLA 38', 15000, NULL),
(36, 1, 0, 15000, '2025-11-25 14:41:24', '2025-11-25 14:41:24', 23, 13, 'TENIS NIKE TALLA 39', 15000, NULL),
(37, 1, 0, 15000, '2025-11-25 14:46:29', '2025-11-25 14:46:29', 26, 13, 'TENIS NIKE TALLA 39', 15000, NULL),
(38, 1, 0, 15000, '2025-11-25 14:49:49', '2025-11-25 14:49:49', 27, 13, 'TENIS NIKE TALLA 39', 15000, NULL),
(39, 1, 0, 15000, '2025-11-25 14:49:49', '2025-11-25 14:49:49', 27, 14, 'TENIS NIKE TALLA 40', 15000, NULL),
(40, 1, 0, 15000, '2025-11-25 14:49:50', '2025-11-25 14:49:50', 27, 12, 'TENIS NIKE TALLA 38', 15000, NULL),
(41, 1, 0, 15000, '2025-11-25 14:57:36', '2025-11-25 14:57:36', 28, 13, 'TENIS NIKE TALLA 39', 15000, NULL),
(42, 1, 0, 15000, '2025-11-25 14:57:36', '2025-11-25 14:57:36', 28, 14, 'TENIS NIKE TALLA 40', 15000, NULL),
(43, 1, 0, 15000, '2025-11-25 14:57:36', '2025-11-25 14:57:36', 28, 15, 'DESTORNILLADOR', 15000, NULL),
(44, 1, 0, 15000, '2025-11-25 19:48:48', '2025-11-25 19:48:48', 31, 15, 'DESTORNILLADOR', 1, NULL),
(45, 1, 0, 15000, '2025-11-25 19:48:48', '2025-11-25 19:48:48', 31, 16, 'LIBRO INFANTIL', 1, NULL),
(46, 1, 0, 15000, '2025-11-25 19:48:48', '2025-11-25 19:48:48', 31, 12, 'TENIS NIKE TALLA 38', 1, NULL),
(49, 2, 0, 202410, '2025-11-26 16:05:49', '2025-11-26 16:05:49', 33, 13, 'TENIS NIKE TALLA 39', 202410, NULL),
(50, 2, 0, 107910, '2025-11-26 16:05:50', '2025-11-26 16:05:50', 33, 5, 'BOTAS NEGRAS', 107910, NULL),
(51, 2, 0, 8999, '2025-11-26 16:05:50', '2025-11-26 16:05:50', 33, 3, 'BATMAN FIGURA PEQUEÑA', 8999, NULL),
(52, 1, 0, 224900, '2025-11-26 16:11:27', '2025-11-26 16:11:27', 34, 13, 'TENIS NIKE TALLA 39', 224900, NULL),
(53, 1, 0, 78500, '2025-11-26 16:11:27', '2025-11-26 16:11:27', 34, 4, 'SUPERMAN FIGURA', 78500, NULL),
(54, 1, 0, 9999, '2025-11-26 16:11:27', '2025-11-26 16:11:27', 34, 3, 'BATMAN FIGURA PEQUEÑA', 9999, NULL),
(55, 1, 0, 119900, '2025-11-26 16:11:28', '2025-11-26 16:11:28', 34, 5, 'BOTAS NEGRAS', 119900, NULL),
(56, 1, 0, 202410, '2025-11-26 16:13:44', '2025-11-26 16:13:44', 35, 13, 'TENIS NIKE TALLA 39', 202410, NULL),
(57, 1, 0, 224900, '2025-11-26 16:19:30', '2025-11-26 16:19:30', 36, 13, 'TENIS NIKE TALLA 39', 224900, NULL),
(58, 1, 0, 119900, '2025-11-26 16:19:30', '2025-11-26 16:19:30', 36, 5, 'BOTAS NEGRAS', 119900, NULL),
(59, 1, 0, 78500, '2025-11-26 16:19:30', '2025-11-26 16:19:30', 36, 4, 'SUPERMAN FIGURA', 78500, NULL),
(60, 1, 0, 9999, '2025-11-26 16:19:30', '2025-11-26 16:19:30', 36, 3, 'BATMAN FIGURA PEQUEÑA', 9999, NULL),
(61, 1, 0, 11700, '2025-11-26 16:19:31', '2025-11-26 16:19:31', 36, 10, 'SALERO', 11700, NULL),
(67, 3, 0, 9999, '2025-11-26 16:25:40', '2025-11-26 16:25:40', 32, 3, 'BATMAN FIGURA PEQUEÑA', 9999, NULL),
(68, 1, 0, 224900, '2025-11-26 16:25:40', '2025-11-26 16:25:40', 32, 13, 'TENIS NIKE TALLA 39', 224900, NULL),
(69, 1, 0, 119900, '2025-11-26 21:55:11', '2025-11-26 21:55:11', 37, 5, 'BOTAS NEGRAS', 119900, NULL),
(70, 1, 0, 9999, '2025-11-26 21:55:11', '2025-11-26 21:55:11', 37, 3, 'BATMAN FIGURA PEQUEÑA', 9999, NULL),
(71, 1, 0, 78500, '2025-11-26 21:55:11', '2025-11-26 21:55:11', 37, 4, 'SUPERMAN FIGURA', 78500, NULL),
(72, 1, 0, 11700, '2025-11-26 21:55:11', '2025-11-26 21:55:11', 37, 10, 'SALERO', 11700, NULL),
(73, 1, 0, 119900, '2025-11-26 21:56:19', '2025-11-26 21:56:19', 38, 5, 'BOTAS NEGRAS', 119900, NULL),
(74, 1, 0, 224900, '2025-11-27 13:48:42', '2025-11-27 13:48:42', 39, 13, 'TENIS NIKE TALLA 39', 224900, NULL),
(75, 1, 0, 107910, '2025-11-27 13:48:42', '2025-11-27 13:48:42', 39, 5, 'BOTAS NEGRAS', 107910, NULL),
(76, 1, 0, 8999, '2025-11-27 13:48:43', '2025-11-27 13:48:43', 39, 3, 'BATMAN FIGURA PEQUEÑA', 8999, NULL),
(83, 1, 0, 258635, '2025-11-27 15:51:03', '2025-11-27 15:51:03', 40, 13, 'TENIS NIKE TALLA 39', 258635, NULL),
(84, 1, 0, 107910, '2025-11-27 15:51:03', '2025-11-27 15:51:03', 40, 5, 'BOTAS NEGRAS', 107910, NULL),
(85, 1, 0, 70650, '2025-11-27 15:51:04', '2025-11-27 15:51:04', 40, 4, 'SUPERMAN FIGURA', 70650, NULL),
(86, 1, 0, 8999, '2025-11-27 15:51:04', '2025-11-27 15:51:04', 40, 3, 'BATMAN FIGURA PEQUEÑA', 8999, NULL),
(87, 1, 0, 10530, '2025-11-27 15:51:04', '2025-11-27 15:51:04', 40, 10, 'SALERO', 10530, NULL),
(88, 1, 0, 119900, '2025-11-27 15:51:04', '2025-11-27 15:51:04', 40, 16, 'EL PRINCIPITO', 119900, NULL),
(90, 1, 0, 2850, '2026-01-20 17:40:13', '2026-01-20 17:40:13', 41, 1060, 'CINTA TRANSPARENTE', 2850, NULL),
(91, 1, 0, 86837, '2026-01-20 17:40:13', '2026-01-20 17:40:13', 41, 1032, 'PRODUCTO 435', 86837, NULL),
(92, 1, 0, 18589, '2026-01-20 17:40:14', '2026-01-20 17:40:14', 41, 1033, 'PRODUCTO 4', 18589, NULL),
(93, 1, 0, 4800, '2026-01-28 20:15:29', '2026-01-28 20:15:29', 53, 1085, 'JJUJUJUJU', 4800, NULL),
(94, 1, 0, 13500, '2026-01-28 20:15:29', '2026-01-28 20:15:29', 53, 1083, 'MARGARITA', 13500, NULL),
(95, 1, 0, 74, '2026-01-28 20:15:29', '2026-01-28 20:15:29', 53, 1082, 'JASPER MCKAY', 74, NULL),
(96, 1, 0, 67155, '2026-01-28 20:15:29', '2026-01-28 20:15:29', 53, 28, 'ALICATE UNIVERSAL', 67155, NULL),
(97, 1, 0, 67155, '2026-01-28 20:51:37', '2026-01-28 20:51:37', 55, 28, 'ALICATE UNIVERSAL', 67155, NULL),
(98, 1, 0, 15000, '2026-01-28 20:51:37', '2026-01-28 20:51:37', 55, 1083, 'MARGARITA', 15000, NULL),
(99, 1, 0, 107509, '2026-01-28 22:04:16', '2026-01-28 22:04:16', 65, 11, 'ALMOHADA', 107509, NULL),
(102, 1, 0, 107509, '2026-01-28 22:15:55', '2026-01-28 22:15:55', 72, 11, 'ALMOHADA', 107509, NULL),
(103, 1, 0, 15718, '2026-01-29 13:25:57', '2026-01-29 13:25:57', 74, 49, 'PRODUCTO 102', 15718, NULL),
(104, 1, 0, 56857, '2026-01-29 13:25:58', '2026-01-29 13:25:58', 74, 20, 'ROMPECABEZAS 100 PIEZAS', 56857, NULL),
(105, 1, 0, 107509, '2026-01-29 13:25:58', '2026-01-29 13:25:58', 74, 11, 'ALMOHADA', 107509, NULL),
(106, 1, 0, 63174, '2026-01-29 13:32:32', '2026-01-29 13:32:32', 75, 20, 'ROMPECABEZAS 100 PIEZAS', 63174, NULL),
(107, 1, 0, 96758, '2026-01-29 13:32:32', '2026-01-29 13:32:32', 75, 11, 'ALMOHADA', 96758, NULL),
(108, 1, 0, 7650, '2026-01-29 14:21:17', '2026-01-29 14:21:17', 76, 1087, 'FDSFDSFDS', 7650, NULL),
(109, 1, 0, 37151, '2026-01-29 14:21:18', '2026-01-29 14:21:18', 76, 5, 'BOTAS NEGRAS', 37151, NULL),
(110, 1, 0, 107509, '2026-01-29 14:25:17', '2026-01-29 14:25:17', 77, 11, 'ALMOHADA', 107509, NULL),
(111, 3, 0, 15000, '2026-01-29 15:05:37', '2026-01-29 15:05:37', 78, 1083, 'MARGARITA', 15000, NULL),
(114, 1, 0, 15000, '2026-01-29 15:18:46', '2026-01-29 15:18:46', 80, 1083, 'MARGARITA', 15000, NULL),
(117, 2, 0, 63174, '2026-01-29 17:36:03', '2026-01-29 17:36:03', 68, 20, 'ROMPECABEZAS 100 PIEZAS', 63174, NULL),
(118, 1, 0, 96758, '2026-01-29 17:36:03', '2026-01-29 17:36:03', 68, 11, 'ALMOHADA', 96758, NULL),
(119, 3, 0, 17250, '2026-01-29 19:36:20', '2026-01-29 19:36:20', 81, 1083, 'MARGARITA', 17250, NULL),
(120, 1, 0, 60147, '2026-01-29 19:35:13', '2026-01-29 19:35:13', 79, 1030, 'PRODUCTO 332', 60147, NULL),
(121, 1, 0, 108754, '2026-01-29 19:35:13', '2026-01-29 19:35:13', 79, 30, 'ACEITE DE MOTOR 20W50', 108754, NULL),
(124, 3, 0, 60147, '2026-01-29 20:19:53', '2026-01-29 20:19:53', 82, 1030, 'PRODUCTO 332', 60147, NULL),
(125, 2, 0, 108754, '2026-01-29 20:19:53', '2026-01-29 20:19:53', 82, 30, 'ACEITE DE MOTOR 20W50', 108754, NULL),
(126, 1, 0, 13500, '2026-01-29 21:11:14', '2026-01-29 21:11:14', 83, 1083, 'MARGARITA', 13500, NULL),
(127, 5, 0, 17250, '2026-01-29 21:18:29', '2026-01-29 21:18:29', 84, 1083, 'MARGARITA', 17250, NULL),
(128, 1, 0, 83400, '2026-01-30 13:38:14', '2026-01-30 13:38:14', 85, 1064, 'BOTAS SENDERISMO TALLA 39', 83400, NULL),
(129, 1, 0, 83400, '2026-01-30 14:27:36', '2026-01-30 14:27:36', 86, 1064, 'BOTAS SENDERISMO TALLA 39', 83400, NULL),
(130, 1, 0, 96758, '2026-01-30 14:27:36', '2026-01-30 14:27:36', 86, 11, 'ALMOHADA', 96758, NULL),
(131, 1, 0, 83400, '2026-01-30 14:35:18', '2026-01-30 14:35:18', 87, 1064, 'BOTAS SENDERISMO TALLA 39', 83400, NULL),
(132, 1, 0, 96758, '2026-01-30 14:35:19', '2026-01-30 14:35:19', 87, 11, 'ALMOHADA', 96758, NULL),
(133, 1, 0, 17250, '2026-01-30 15:20:14', '2026-01-30 15:20:14', 88, 1083, 'MARGARITA', 17250, NULL),
(134, 1, 19, 17250, '2026-01-30 16:16:08', '2026-01-30 16:16:08', 89, 1083, 'MARGARITA', 17250, NULL),
(135, 4, 0, 15000, '2026-01-30 17:32:13', '2026-01-30 17:32:13', 90, 1083, 'MARGARITA', 15000, NULL),
(136, 3, 0, 17250, '2026-01-30 20:38:11', '2026-01-30 20:38:11', 91, 1083, 'MARGARITA', 17250, NULL),
(137, 2, 0, 17850, '2026-01-31 00:12:48', '2026-01-31 00:12:48', 92, 1083, 'MARGARITA', 17850, NULL),
(138, 5, 0, 16065, '2026-01-31 00:17:13', '2026-01-31 00:17:13', 93, 1083, 'MARGARITA', 16065, NULL),
(139, 1, 0, 5796, '2026-01-31 00:17:14', '2026-01-31 00:17:14', 93, 1085, 'IMPRESORA EPSON 78K', 5796, NULL),
(140, 10, 0, 17850, '2026-02-01 15:28:47', '2026-02-01 15:28:47', 94, 1083, 'MARGARITA', 17850, NULL),
(141, 1, 0, 20528, '2026-02-01 15:39:56', '2026-02-01 15:39:56', 95, 1083, 'MARGARITA', 20528, NULL),
(142, 1, 0, 63000, '2026-02-01 22:25:11', '2026-02-01 22:25:11', 96, 1093, 'SILLA RIMAX', 63000, NULL),
(143, 1, 0, 20528, '2026-02-01 22:25:12', '2026-02-01 22:25:12', 96, 1083, 'MARGARITA', 20528, NULL),
(144, 2, 0, 56700, '2026-02-01 22:27:19', '2026-02-01 22:27:19', 97, 1093, 'SILLA RIMAX', 56700, NULL),
(149, 1, 0, 60000, '2026-02-02 17:06:11', '2026-02-02 17:06:11', 98, 1093, 'SILLA RIMAX', 60000, NULL),
(150, 2, 0, 37151, '2026-02-02 17:06:12', '2026-02-02 17:06:12', 98, 5, 'BOTAS NEGRAS', 37151, NULL),
(151, 1, 0, 180000, '2026-02-02 17:06:12', '2026-02-02 17:06:12', 98, 1090, 'MARSELLA', 180000, NULL),
(152, 6, 5, 164151, '2026-02-02 17:50:42', '2026-02-02 17:50:42', 103, 30, 'ACEITE DE MOTOR 20W50', 164151, NULL),
(153, 1, 5, 63154, '2026-02-02 17:50:42', '2026-02-02 17:50:42', 103, 1030, 'PRODUCTO 332', 63154, NULL),
(156, 1, 5, 56700, '2026-02-02 19:15:07', '2026-02-02 19:15:07', 104, 1093, 'SILLA RIMAX', 56700, NULL),
(159, 1, 5, 56700, '2026-02-02 19:22:51', '2026-02-02 19:22:51', 105, 1093, 'SILLA RIMAX', 56700, NULL),
(160, 1, 19, 6569, '2026-02-02 19:22:51', '2026-02-02 19:22:51', 105, 1086, 'JJUJUJUJU', 6569, NULL),
(161, 1, 5, 210000, '2026-02-02 19:22:51', '2026-02-02 19:22:51', 105, 1090, 'MARSELLA', 210000, NULL),
(163, 1, 5, 50400, '2026-02-02 20:02:53', '2026-02-02 20:02:53', 108, 1093, 'SILLA RIMAX', 50400, NULL),
(164, 1, 5, 4032, '2026-02-02 20:02:54', '2026-02-02 20:02:54', 108, 1085, 'IMPRESORA EPSON 78K', 4032, NULL),
(167, 1, 5, 63000, '2026-02-02 20:04:00', '2026-02-02 20:04:00', 109, 1093, 'SILLA RIMAX', 63000, NULL),
(168, 1, 19, 5141, '2026-02-02 20:04:00', '2026-02-02 20:04:00', 109, 1086, 'JJUJUJUJU', 5141, NULL),
(169, 1, 5, 189000, '2026-02-02 20:04:00', '2026-02-02 20:04:00', 109, 1090, 'MARSELLA', 189000, NULL),
(172, 1, 19, 14994, '2026-02-02 20:13:34', '2026-02-02 20:13:34', 110, 1091, 'ARNES MASCOTA TALLA MEDIANA', 14994, NULL),
(173, 1, 19, 6569, '2026-02-02 20:13:34', '2026-02-02 20:13:34', 110, 1086, 'JJUJUJUJU', 6569, NULL),
(174, 1, 5, 241500, '2026-02-02 20:13:34', '2026-02-02 20:13:34', 110, 1090, 'MARSELLA', 241500, NULL),
(175, 1, 5, 60000, '2026-02-03 19:04:14', '2026-02-03 19:04:14', 111, 1093, 'SILLA RIMAX', 60000, NULL),
(176, 1, 5, 8033, '2026-02-03 19:04:14', '2026-02-03 19:04:14', 111, 1087, 'FDSFDSFDS', 8033, NULL),
(177, 1, 5, 168000, '2026-02-03 19:04:14', '2026-02-03 19:04:14', 111, 1090, 'MARSELLA', 168000, NULL),
(178, 1, 19, 17850, '2026-02-03 19:04:14', '2026-02-03 19:04:14', 111, 1083, 'MARGARITA', 17850, NULL),
(179, 1, 19, 14122, '2026-02-04 13:39:03', '2026-02-04 13:39:03', 112, 1083, 'MARGARITA', 14122, NULL),
(180, 1, 5, 56700, '2026-02-04 13:39:03', '2026-02-04 13:39:03', 112, 1093, 'SILLA RIMAX', 56700, NULL),
(181, 2, 5, 56700, '2026-02-04 13:40:39', '2026-02-04 13:40:39', 113, 1093, 'SILLA RIMAX', 56700, NULL),
(182, 2, 19, 16065, '2026-02-04 21:55:18', '2026-02-04 21:55:18', 114, 1083, 'MARGARITA', 16065, NULL),
(183, 1, 5, 56700, '2026-02-05 15:35:33', '2026-02-05 15:35:33', 115, 1093, 'SILLA RIMAX', 56700, NULL),
(186, 1, 5, 109411, '2026-02-06 13:50:50', '2026-02-06 13:50:50', 116, 1093, 'SILLA RIMAX', 109411, NULL),
(187, 1, 19, 42299, '2026-02-06 13:50:50', '2026-02-06 13:50:50', 116, 1083, 'MARGARITA', 42299, NULL),
(188, 1, 5, 98470, '2026-02-06 14:24:34', '2026-02-06 14:24:34', 117, 1093, 'SILLA RIMAX', 98470, NULL),
(189, 1, 19, 42299, '2026-02-06 14:24:34', '2026-02-06 14:24:34', 117, 1083, 'MARGARITA', 42299, NULL),
(190, 1, 5, 125823, '2026-02-06 14:47:26', '2026-02-06 14:47:26', 118, 1093, 'SILLA RIMAX', 125823, NULL),
(191, 1, 19, 42299, '2026-02-06 14:47:26', '2026-02-06 14:47:26', 118, 1083, 'MARGARITA', 42299, NULL),
(192, 3, 5, 125823, '2026-02-06 15:04:12', '2026-02-06 15:04:12', 119, 1093, 'SILLA RIMAX', 125823, NULL),
(193, 3, 19, 42299, '2026-02-06 15:04:12', '2026-02-06 15:04:12', 119, 1083, 'MARGARITA', 42299, NULL),
(194, 4, 5, 109411, '2026-02-06 20:19:29', '2026-02-06 20:19:29', 120, 1093, 'SILLA RIMAX', 109411, NULL),
(195, 2, 19, 46999, '2026-02-06 20:19:29', '2026-02-06 20:19:29', 120, 1083, 'MARGARITA', 46999, NULL),
(196, 7, 5, 125823, '2026-02-06 21:46:58', '2026-02-06 21:46:58', 121, 1093, 'SILLA RIMAX', 125823, NULL),
(197, 6, 19, 54049, '2026-02-06 21:46:58', '2026-02-06 21:46:58', 121, 1083, 'MARGARITA', 54049, NULL),
(198, 7, 5, 109411, '2026-02-09 13:08:54', '2026-02-09 13:08:54', 122, 1093, 'SILLA RIMAX', 109411, NULL),
(199, 1, 19, 42299, '2026-02-09 13:08:54', '2026-02-09 13:08:54', 122, 1083, 'MARGARITA', 42299, NULL),
(200, 1, 5, 98470, '2026-02-09 15:54:56', '2026-02-09 15:54:56', 123, 1093, 'SILLA RIMAX', 98470, NULL),
(201, 1, 19, 42299, '2026-02-09 15:54:56', '2026-02-09 15:54:56', 123, 1083, 'MARGARITA', 42299, NULL),
(202, 5, 5, 125823, '2026-02-09 17:28:01', '2026-02-09 17:28:01', 124, 1093, 'SILLA RIMAX', 125823, NULL),
(203, 5, 5, 125823, '2026-02-09 17:40:26', '2026-02-09 17:40:26', 125, 1093, 'SILLA RIMAX', 125823, NULL),
(204, 1, 5, 125823, '2026-02-09 19:08:50', '2026-02-09 19:08:50', 126, 1093, 'SILLA RIMAX', 125823, NULL),
(207, 5, 5, 125823, '2026-02-09 19:37:44', '2026-02-09 19:37:44', 128, 1093, 'SILLA RIMAX', 125823, NULL),
(208, 5, 5, 125823, '2026-02-09 19:42:40', '2026-02-09 19:42:40', 127, 1093, 'SILLA RIMAX', 125823, NULL),
(211, 5, 5, 98470, '2026-02-09 20:17:30', '2026-02-09 20:17:30', 129, 1093, 'SILLA RIMAX', 98470, NULL),
(212, 3, 19, 54049, '2026-02-09 20:17:30', '2026-02-09 20:17:30', 129, 1083, 'MARGARITA', 54049, NULL),
(213, 5, 5, 98470, '2026-02-09 20:32:33', '2026-02-09 20:32:33', 130, 1093, 'SILLA RIMAX', 98470, NULL),
(214, 6, 19, 54049, '2026-02-09 20:32:33', '2026-02-09 20:32:33', 130, 1083, 'MARGARITA', 54049, NULL),
(215, 6, 5, 98470, '2026-02-09 21:49:24', '2026-02-09 21:49:24', 131, 1093, 'SILLA RIMAX', 98470, NULL),
(216, 6, 19, 54049, '2026-02-09 21:49:24', '2026-02-09 21:49:24', 131, 1083, 'MARGARITA', 54049, NULL),
(217, 6, 5, 98470, '2026-02-09 21:56:00', '2026-02-09 21:56:00', 132, 1093, 'SILLA RIMAX', 98470, NULL),
(218, 6, 19, 54049, '2026-02-09 21:56:00', '2026-02-09 21:56:00', 132, 1083, 'MARGARITA', 54049, NULL),
(219, 1, 5, 310588, '2026-02-13 16:45:20', '2026-02-13 16:45:20', 133, 1087, 'FDSFDSFDS', 310588, NULL),
(220, 1, 19, 42299, '2026-02-16 19:09:40', '2026-02-16 19:09:40', 134, 1083, 'MARGARITA', 42299, NULL),
(221, 1, 5, 98470, '2026-02-16 19:09:40', '2026-02-16 19:09:40', 134, 1093, 'SILLA RIMAX', 98470, NULL),
(222, 1, 5, 98470, '2026-02-16 20:22:58', '2026-02-16 20:22:58', 135, 1093, 'SILLA RIMAX', 98470, NULL),
(223, 3, 5, 125823, '2026-02-16 21:12:21', '2026-02-16 21:12:21', 136, 1093, 'SILLA RIMAX', 125823, NULL),
(224, 1, 5, 98470, '2026-02-17 17:13:33', '2026-02-17 17:13:33', 137, 1093, 'SILLA RIMAX', 98470, NULL),
(225, 1, 19, 46999, '2026-02-17 17:13:33', '2026-02-17 17:13:33', 137, 1083, 'MARGARITA', 46999, NULL),
(226, 1, 5, 125823, '2026-02-17 17:14:43', '2026-02-17 17:14:43', 138, 1093, 'SILLA RIMAX', 125823, NULL),
(227, 2, 19, 42299, '2026-02-17 17:18:05', '2026-02-17 17:18:05', 139, 1083, 'MARGARITA', 42299, NULL),
(228, 1, 19, 54049, '2026-02-17 17:57:42', '2026-02-17 17:57:42', 140, 1083, 'MARGARITA', 54049, NULL),
(229, 1, 5, 87529, '2026-02-17 17:57:42', '2026-02-17 17:57:42', 140, 1093, 'SILLA RIMAX', 87529, NULL),
(230, 1, 5, 125823, '2026-02-18 13:09:13', '2026-02-18 13:09:13', 141, 1093, 'SILLA RIMAX', 125823, NULL),
(231, 1, 19, 46999, '2026-02-18 13:11:00', '2026-02-18 13:11:00', 142, 1083, 'MARGARITA', 46999, NULL),
(232, 1, 5, 125823, '2026-02-18 13:43:40', '2026-02-18 13:43:40', 143, 1093, 'SILLA RIMAX', 125823, NULL),
(233, 1, 5, 125823, '2026-02-18 13:52:50', '2026-02-18 13:52:50', 144, 1093, 'SILLA RIMAX', 125823, NULL),
(234, 1, 5, 125823, '2026-02-18 14:04:35', '2026-02-18 14:04:35', 145, 1093, 'SILLA RIMAX', 125823, NULL),
(235, 1, 19, 54049, '2026-02-18 14:07:39', '2026-02-18 14:07:39', 146, 1083, 'MARGARITA', 54049, NULL),
(238, 1, 5, 98470, '2026-02-19 17:21:06', '2026-02-19 17:21:06', 147, 1093, 'SILLA RIMAX', 98470, NULL),
(239, 2, 5, 98470, '2026-02-20 14:44:51', '2026-02-20 14:44:51', 148, 1093, 'SILLA RIMAX', 98470, NULL),
(246, 1, 5, 1323529, '2026-02-24 20:21:59', '2026-02-24 20:21:59', 149, 291, 'PRODUCTO 169', 1323529, NULL),
(247, 1, 5, 147705, '2026-02-24 20:21:59', '2026-02-24 20:21:59', 149, 18, 'AUDIFONOS BLUETOOTH', 147705, NULL),
(248, 1, 5, 158294, '2026-02-24 20:21:59', '2026-02-24 20:21:59', 149, 21, 'BOTA DEPORTIVA TALLA 41', 158294, NULL),
(249, 1, 5, 809117, '2026-02-24 20:21:59', '2026-02-24 20:21:59', 149, 29, 'CASCO PARA MOTO', 809117, NULL),
(250, 1, 5, 503470, '2026-02-24 20:21:59', '2026-02-24 20:21:59', 149, 36, 'GAFAS DEPORTIVAS', 503470, NULL),
(251, 1, 5, 11800, '2026-02-24 20:21:59', '2026-02-24 20:21:59', 149, 1114, 'CHAZOS PLASTICO', 11800, NULL),
(252, 1, 5, 1191176, '2026-02-24 20:30:31', '2026-02-24 20:30:31', 150, 291, 'PRODUCTO 169', 1191176, NULL),
(253, 1, 5, 147705, '2026-02-24 20:30:31', '2026-02-24 20:30:31', 150, 18, 'AUDIFONOS BLUETOOTH', 147705, NULL),
(254, 8, 5, 137647, '2026-02-24 20:30:31', '2026-02-24 20:30:31', 150, 21, 'BOTA DEPORTIVA TALLA 41', 137647, NULL),
(255, 5, 5, 728206, '2026-02-24 20:30:31', '2026-02-24 20:30:31', 150, 29, 'CASCO PARA MOTO', 728206, NULL),
(256, 2, 5, 559412, '2026-02-24 20:30:31', '2026-02-24 20:30:31', 150, 36, 'GAFAS DEPORTIVAS', 559412, NULL),
(257, 1, 5, 11800, '2026-02-24 20:30:31', '2026-02-24 20:30:31', 150, 1114, 'CHAZOS PLASTICO', 11800, NULL),
(258, 3, 19, 42299, '2026-03-10 22:03:19', '2026-03-10 22:03:19', 151, 1083, 'MARGARITA', 42299, NULL),
(259, 3, 5, 98470, '2026-03-12 17:57:08', '2026-03-12 17:57:08', 152, 1093, 'SILLA RIMAX', 98470, NULL),
(260, 1, 19, 46999, '2026-03-12 17:57:08', '2026-03-12 17:57:08', 152, 1083, 'MARGARITA', 46999, NULL),
(263, 3, 19, 42299, '2026-03-12 19:35:48', '2026-03-12 19:35:48', 154, 1083, 'MARGARITA', 42299, NULL),
(264, 3, 19, 42299, '2026-03-13 13:47:53', '2026-03-13 13:47:53', 155, 1083, 'MARGARITA', 42299, NULL),
(265, 2, 5, 109411, '2026-03-13 13:51:21', '2026-03-13 13:51:21', 156, 1093, 'SILLA RIMAX', 109411, NULL),
(266, 4, 19, 42299, '2026-03-13 15:35:06', '2026-03-13 15:35:06', 157, 1083, 'MARGARITA', 42299, NULL),
(267, 4, 19, 42299, '2026-03-13 15:42:39', '2026-03-13 15:42:39', 158, 1083, 'MARGARITA', 42299, NULL),
(268, 1, 19, 1607, '2026-03-16 17:12:19', '2026-03-16 17:12:19', 159, 1121, 'INSUMO 2', 1607, NULL),
(269, 1, 0, 1500, '2026-03-16 17:12:19', '2026-03-16 17:12:19', 159, 1122, 'INSUMO 3', 1500, NULL),
(270, 1, 0, 1500, '2026-03-16 17:33:56', '2026-03-16 17:33:56', 160, 1122, 'INSUMO 3', 1500, NULL),
(271, 1, 0, 1500, '2026-03-16 17:39:10', '2026-03-16 17:39:10', 161, 1122, 'INSUMO 3', 1500, NULL),
(272, 3, 5, 98470, '2026-04-09 22:13:18', '2026-04-09 22:13:18', 153, 1093, 'SILLA RIMAX', 98470, NULL),
(276, 1, 19, 1500, '2026-04-10 13:24:32', '2026-04-10 13:24:32', 162, 1121, 'INSUMO 2', 1500, NULL),
(277, 1, 5, 24900, '2026-04-10 13:24:32', '2026-04-10 13:24:32', 162, 1113, 'ALMOHADA III', 24900, NULL),
(278, 1, 19, 10000, '2026-04-10 13:24:32', '2026-04-10 13:24:32', 162, 1118, 'TALONARIO', 10000, NULL),
(279, 1, 5, 18999, '2026-04-10 14:07:06', '2026-04-10 14:07:06', 163, 1109, 'MOLEDOR DE CAFE PORTATIL', 18999, NULL),
(280, 1, 19, 1500, '2026-04-10 15:39:30', '2026-04-10 15:39:30', 164, 1121, 'INSUMO 2', 1500, NULL),
(281, 1, 0, 12311, '2026-04-10 15:39:30', '2026-04-10 15:39:30', 164, 1122, 'INSUMO 3', 12311, NULL),
(282, 1, 19, 25000, '2026-04-10 15:39:30', '2026-04-10 15:39:30', 164, 1123, 'CARGADOR', 25000, NULL),
(317, 8, 5, 87529, '2026-04-12 18:03:03', '2026-04-12 18:03:03', 165, 1093, 'SILLA RIMAX', 87529, NULL),
(318, 2, 19, 137900, '2026-04-12 18:03:03', '2026-04-12 18:03:03', 165, 1105, 'DISCO DURU 1T', 137900, NULL),
(319, 7, 5, 19800, '2026-04-12 18:03:03', '2026-04-12 18:03:03', 165, 1110, 'MOUSE PAD COLOR AZUL', 19800, NULL),
(320, 5, 5, 53600, '2026-04-12 18:03:03', '2026-04-12 18:03:03', 165, 1115, 'TECLADO ALAMBRICO COMPUTADOR', 53600, NULL),
(321, 6, 5, 24900, '2026-04-12 18:03:03', '2026-04-12 18:03:03', 165, 1113, 'ALMOHADA III', 24900, NULL),
(322, 5, 19, 1731, '2026-04-12 18:03:03', '2026-04-12 18:03:03', 165, 1121, 'INSUMO 2', 1731, NULL),
(324, 1, 19, 1696, '2026-04-13 13:57:22', '2026-04-13 13:57:22', 168, 1121, 'INSUMO 2', 1696, NULL),
(325, 1, 19, 27668, '2026-04-13 13:57:22', '2026-04-13 13:57:22', 168, 1123, 'CARGADOR', 27668, NULL),
(326, 1, 19, 1660, '2026-04-13 15:02:00', '2026-04-13 15:02:00', 169, 1121, 'INSUMO 2', 1660, NULL),
(327, 1, 19, 28263, '2026-04-13 15:02:00', '2026-04-13 15:02:00', 169, 1123, 'CARGADOR', 28263, NULL),
(328, 1, 19, 1696, '2026-04-13 15:36:10', '2026-04-13 15:36:10', 170, 1121, 'INSUMO 2', 1696, NULL),
(329, 1, 5, 25835, '2026-04-13 15:38:43', '2026-04-13 15:38:43', 171, 1113, 'ALMOHADA III', 25835, '5%'),
(330, 1, 19, 11305, '2026-04-13 15:52:38', '2026-04-13 15:52:38', 172, 1118, 'TALONARIO', 11305, '5%'),
(331, 1, 19, 1660, '2026-04-13 15:52:38', '2026-04-13 15:52:38', 172, 1121, 'INSUMO 2', 1660, '7%'),
(332, 10, 5, 109411, '2026-04-13 16:26:28', '2026-04-13 16:26:28', 173, 1093, 'SILLA RIMAX', 109411, 'Lista'),
(333, 5, 5, 12900, '2026-04-13 16:26:28', '2026-04-13 16:26:28', 173, 1114, 'CHAZOS PLASTICO', 12900, 'Precio Crédito'),
(334, 7, 0, 5000, '2026-04-13 16:26:28', '2026-04-13 16:26:28', 173, 1120, 'INSUMO', 5000, 'Precio Crédito'),
(335, 1, 0, 12311, '2026-04-13 16:26:28', '2026-04-13 16:26:28', 173, 1122, 'INSUMO 3', 12311, 'Precio Crédito');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vnt_detail_reconciliations`
--

CREATE TABLE `vnt_detail_reconciliations` (
  `id` int NOT NULL,
  `value` int NOT NULL,
  `valueSystem` int NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `methodPaymentId` int DEFAULT NULL,
  `reconciliationId` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `vnt_detail_reconciliations`
--

INSERT INTO `vnt_detail_reconciliations` (`id`, `value`, `valueSystem`, `created_at`, `updated_at`, `deleted_at`, `methodPaymentId`, `reconciliationId`) VALUES
(14, 1500, 641117, '2025-12-01 15:38:33', '2025-12-01 15:38:33', NULL, 1, 13),
(15, 2900, 137895, '2025-12-01 15:38:33', '2025-12-01 15:38:33', NULL, 2, 13),
(16, 3500, 73712, '2025-12-01 15:38:33', '2025-12-01 15:38:33', NULL, 4, 13),
(17, 4700, -160910, '2025-12-01 15:38:33', '2025-12-01 15:38:33', NULL, 10, 13),
(18, 5800, -113298, '2025-12-01 15:38:34', '2025-12-01 15:38:34', NULL, 11, 13),
(19, 6700, -492300, '2025-12-01 15:38:34', '2025-12-01 15:38:34', NULL, 12, 13),
(20, 2600, 641117, '2025-12-02 17:52:06', '2025-12-02 17:52:06', NULL, 1, 14),
(21, 8500, 137895, '2025-12-02 17:52:07', '2025-12-02 17:52:07', NULL, 2, 14),
(22, 11400, 73712, '2025-12-02 17:52:07', '2025-12-02 17:52:07', NULL, 4, 14),
(23, 7800, -160910, '2025-12-02 17:52:07', '2025-12-02 17:52:07', NULL, 10, 14),
(24, 9500, -113298, '2025-12-02 17:52:07', '2025-12-02 17:52:07', NULL, 11, 14),
(25, 18700, -492300, '2025-12-02 17:52:07', '2025-12-02 17:52:07', NULL, 12, 14),
(26, 7800, 641117, '2025-12-02 18:06:02', '2025-12-02 18:06:02', NULL, 1, 15),
(27, 4500, 137895, '2025-12-02 18:06:02', '2025-12-02 18:06:02', NULL, 2, 15),
(28, 6200, 73712, '2025-12-02 18:06:02', '2025-12-02 18:06:02', NULL, 4, 15),
(29, 11400, -160910, '2025-12-02 18:06:02', '2025-12-02 18:06:02', NULL, 10, 15),
(30, 12300, -113298, '2025-12-02 18:06:03', '2025-12-02 18:06:03', NULL, 11, 15),
(31, 5900, -492300, '2025-12-02 18:06:03', '2025-12-02 18:06:03', NULL, 12, 15),
(32, 8500, 641117, '2025-12-02 19:10:33', '2025-12-02 19:10:33', NULL, 1, 16),
(33, 6300, 137895, '2025-12-02 19:10:33', '2025-12-02 19:10:33', NULL, 2, 16),
(34, 11200, 73712, '2025-12-02 19:10:33', '2025-12-02 19:10:33', NULL, 4, 16),
(35, 8250, -160910, '2025-12-02 19:10:33', '2025-12-02 19:10:33', NULL, 10, 16),
(36, 11900, -113298, '2025-12-02 19:10:34', '2025-12-02 19:10:34', NULL, 11, 16),
(37, 14500, -492300, '2025-12-02 19:10:34', '2025-12-02 19:10:34', NULL, 12, 16),
(38, 640000, 641117, '2025-12-02 19:13:53', '2025-12-02 19:13:53', NULL, 1, 17),
(39, 13500, 137895, '2025-12-02 19:13:53', '2025-12-02 19:13:53', NULL, 2, 17),
(40, 73000, 73712, '2025-12-02 19:13:54', '2025-12-02 19:13:54', NULL, 4, 17),
(41, 8500, -160910, '2025-12-02 19:13:54', '2025-12-02 19:13:54', NULL, 10, 17),
(42, 6300, -113298, '2025-12-02 19:13:54', '2025-12-02 19:13:54', NULL, 11, 17),
(43, 11200, -492300, '2025-12-02 19:13:54', '2025-12-02 19:13:54', NULL, 12, 17),
(44, 25000, 641117, '2025-12-04 17:26:20', '2025-12-04 17:26:20', NULL, 1, 18),
(45, 0, 137895, '2025-12-04 17:26:20', '2025-12-04 17:26:20', NULL, 2, 18),
(46, 30000, 73712, '2025-12-04 17:26:20', '2025-12-04 17:26:20', NULL, 4, 18),
(47, 0, -141310, '2025-12-04 17:26:21', '2025-12-04 17:26:21', NULL, 10, 18),
(48, 45000, -113298, '2025-12-04 17:26:21', '2025-12-04 17:26:21', NULL, 11, 18),
(49, 40000, -492300, '2025-12-04 17:26:21', '2025-12-04 17:26:21', NULL, 12, 18),
(50, 9600, 652317, '2026-01-21 16:19:13', '2026-01-21 16:19:13', NULL, 1, 19),
(51, 7800, 137895, '2026-01-21 16:19:13', '2026-01-21 16:19:13', NULL, 2, 19),
(52, 4500, 73712, '2026-01-21 16:19:14', '2026-01-21 16:19:14', NULL, 4, 19),
(53, 12360, -132810, '2026-01-21 16:19:14', '2026-01-21 16:19:14', NULL, 10, 19),
(54, 45600, -113298, '2026-01-21 16:19:14', '2026-01-21 16:19:14', NULL, 11, 19),
(55, 8900, 68860, '2026-01-21 16:19:14', '2026-01-21 16:19:14', NULL, 12, 19),
(56, 9600, 652317, '2026-01-21 16:44:56', '2026-01-21 16:44:56', NULL, 1, 20),
(57, 7800, 137895, '2026-01-21 16:44:57', '2026-01-21 16:44:57', NULL, 2, 20),
(58, 4500, 73712, '2026-01-21 16:44:57', '2026-01-21 16:44:57', NULL, 4, 20),
(59, 12360, -132810, '2026-01-21 16:44:57', '2026-01-21 16:44:57', NULL, 10, 20),
(60, 45600, -113298, '2026-01-21 16:44:57', '2026-01-21 16:44:57', NULL, 11, 20),
(61, 8900, 68860, '2026-01-21 16:44:57', '2026-01-21 16:44:57', NULL, 12, 20),
(62, 9600, 652317, '2026-01-21 16:45:18', '2026-01-21 16:45:18', NULL, 1, 21),
(63, 7800, 137895, '2026-01-21 16:45:18', '2026-01-21 16:45:18', NULL, 2, 21),
(64, 4500, 73712, '2026-01-21 16:45:18', '2026-01-21 16:45:18', NULL, 4, 21),
(65, 12360, -132810, '2026-01-21 16:45:18', '2026-01-21 16:45:18', NULL, 10, 21),
(66, 45600, -113298, '2026-01-21 16:45:19', '2026-01-21 16:45:19', NULL, 11, 21),
(67, 8900, 68860, '2026-01-21 16:45:19', '2026-01-21 16:45:19', NULL, 12, 21),
(68, 778, 652317, '2026-01-21 18:04:55', '2026-01-21 18:04:55', NULL, 1, 22),
(69, 78, 137895, '2026-01-21 18:04:55', '2026-01-21 18:04:55', NULL, 2, 22),
(70, 78, 73712, '2026-01-21 18:04:55', '2026-01-21 18:04:55', NULL, 4, 22),
(71, 78, -132810, '2026-01-21 18:04:55', '2026-01-21 18:04:55', NULL, 10, 22),
(72, 7, -113298, '2026-01-21 18:04:55', '2026-01-21 18:04:55', NULL, 11, 22),
(73, 87, 68860, '2026-01-21 18:04:56', '2026-01-21 18:04:56', NULL, 12, 22),
(74, 0, 6900, '2026-01-21 20:44:13', '2026-01-21 20:44:13', NULL, 1, 23),
(75, 0, 56000, '2026-01-21 20:44:13', '2026-01-21 20:44:13', NULL, 2, 23),
(76, 0, 28900, '2026-01-21 20:44:13', '2026-01-21 20:44:13', NULL, 4, 23),
(77, 0, 32500, '2026-01-21 20:44:13', '2026-01-21 20:44:13', NULL, 10, 23),
(78, 0, 19800, '2026-01-21 20:44:14', '2026-01-21 20:44:14', NULL, 11, 23),
(79, 7855, 6900, '2026-01-21 21:27:39', '2026-01-21 21:27:39', NULL, 1, 24),
(80, 4500, 56000, '2026-01-21 21:27:40', '2026-01-21 21:27:40', NULL, 2, 24),
(81, 6900, 28900, '2026-01-21 21:27:40', '2026-01-21 21:27:40', NULL, 4, 24),
(82, 4700, 32500, '2026-01-21 21:27:40', '2026-01-21 21:27:40', NULL, 10, 24),
(83, 3660, 19800, '2026-01-21 21:27:41', '2026-01-21 21:27:41', NULL, 11, 24),
(84, 1400, 0, '2026-01-21 21:27:41', '2026-01-21 21:27:41', NULL, 12, 24),
(85, 7800, 6900, '2026-01-29 15:12:50', '2026-01-29 15:12:50', NULL, 1, 25),
(86, 8880, 56000, '2026-01-29 15:12:50', '2026-01-29 15:12:50', NULL, 2, 25),
(87, 960, 28900, '2026-01-29 15:12:50', '2026-01-29 15:12:50', NULL, 4, 25),
(88, 4410, 32500, '2026-01-29 15:12:51', '2026-01-29 15:12:51', NULL, 10, 25),
(89, 5501, 19800, '2026-01-29 15:12:51', '2026-01-29 15:12:51', NULL, 11, 25),
(90, 85600, 0, '2026-01-29 15:12:51', '2026-01-29 15:12:51', NULL, 12, 25),
(91, 9000, -8100, '2026-01-29 15:15:24', '2026-01-29 15:15:24', NULL, 1, 26),
(92, 8800, 56000, '2026-01-29 15:15:24', '2026-01-29 15:15:24', NULL, 2, 26),
(93, 7400, 28900, '2026-01-29 15:15:25', '2026-01-29 15:15:25', NULL, 4, 26),
(94, 3600, 32500, '2026-01-29 15:15:25', '2026-01-29 15:15:25', NULL, 10, 26),
(95, 4500, 19800, '2026-01-29 15:15:25', '2026-01-29 15:15:25', NULL, 11, 26),
(96, 9700, 0, '2026-01-29 15:15:26', '2026-01-29 15:15:26', NULL, 12, 26),
(97, 7800, -8100, '2026-01-31 00:04:15', '2026-01-31 00:04:15', NULL, 1, 27),
(98, 8966, 56000, '2026-01-31 00:04:16', '2026-01-31 00:04:16', NULL, 2, 27),
(99, 666, 28900, '2026-01-31 00:04:16', '2026-01-31 00:04:16', NULL, 4, 27),
(100, 66, 32500, '2026-01-31 00:04:16', '2026-01-31 00:04:16', NULL, 10, 27),
(101, 6, 19800, '2026-01-31 00:04:16', '2026-01-31 00:04:16', NULL, 11, 27),
(102, 6, 0, '2026-01-31 00:04:16', '2026-01-31 00:04:16', NULL, 12, 27);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vnt_invoices`
--

CREATE TABLE `vnt_invoices` (
  `id` int NOT NULL,
  `consecutive` int NOT NULL,
  `status` enum('REGISTRADO','FACTURADO','ANULADO','SIN EMITIR') NOT NULL,
  `status_payment` enum('REGISTRADO','ABONO','PAGADO','ANULADO') NOT NULL,
  `api_data_id` int DEFAULT NULL,
  `api_data_id_pay` int DEFAULT NULL,
  `partialPayment` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `quoteId` int DEFAULT NULL,
  `warehouseId` int DEFAULT NULL,
  `remission` tinyint NOT NULL,
  `creditNoteId` int DEFAULT NULL,
  `invoiceNumber` varchar(100) NOT NULL,
  `retentionFuente` int DEFAULT NULL,
  `retentionIca` int DEFAULT NULL,
  `retentionIva` int DEFAULT NULL,
  `creditNote` int DEFAULT '0',
  `orderNumber` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `vnt_invoices`
--

INSERT INTO `vnt_invoices` (`id`, `consecutive`, `status`, `status_payment`, `api_data_id`, `api_data_id_pay`, `partialPayment`, `created_at`, `updated_at`, `deleted_at`, `quoteId`, `warehouseId`, `remission`, `creditNoteId`, `invoiceNumber`, `retentionFuente`, `retentionIca`, `retentionIva`, `creditNote`, `orderNumber`) VALUES
(92, 1862, 'FACTURADO', 'PAGADO', 1862, NULL, 0, '2026-02-18 14:31:18', '2026-02-18 14:32:32', NULL, 145, 1, 0, NULL, 'SETP990202634', 0, 0, 0, NULL, NULL),
(93, 1863, 'FACTURADO', 'PAGADO', 1863, NULL, 0, '2026-02-18 15:26:16', '2026-02-18 15:29:02', NULL, 141, 1, 0, NULL, 'SETP990202635', 0, 0, 0, NULL, NULL),
(94, 1864, 'FACTURADO', 'PAGADO', 1864, NULL, 0, '2026-02-18 16:23:46', '2026-02-18 18:22:01', NULL, 140, 1, 0, NULL, 'SETP990202636', 0, 0, 0, NULL, NULL),
(95, 1865, 'FACTURADO', 'PAGADO', 1865, NULL, 0, '2026-02-18 16:27:44', '2026-02-18 16:40:46', NULL, 139, 1, 0, NULL, 'SETP990202637', 0, 0, 0, NULL, NULL),
(96, 1886, 'FACTURADO', 'REGISTRADO', 1886, NULL, 0, '2026-03-10 22:06:09', '2026-03-11 17:00:28', NULL, 151, 1, 0, NULL, 'SETP990202658', 0, 0, 0, NULL, NULL),
(97, 1887, 'FACTURADO', 'REGISTRADO', 1887, NULL, 0, '2026-03-11 13:39:48', '2026-03-12 21:49:15', NULL, 147, 1, 0, NULL, 'SETP990202659', 0, 0, 0, NULL, NULL),
(98, 1888, 'FACTURADO', 'REGISTRADO', 1889, NULL, 0, '2026-03-12 20:33:54', '2026-03-12 22:08:35', NULL, 154, 1, 0, NULL, 'SETP990202661', 0, 0, 0, NULL, NULL),
(99, 1890, 'FACTURADO', 'REGISTRADO', 1890, NULL, 0, '2026-03-13 14:12:08', '2026-03-13 15:53:35', NULL, 155, 1, 0, 181, 'SETP990202662', 0, 0, 0, 126897, NULL),
(100, 1891, 'FACTURADO', 'REGISTRADO', 1891, NULL, 0, '2026-03-13 14:12:59', '2026-03-13 14:17:51', NULL, 156, 1, 0, 180, 'SETP990202663', 0, 0, 0, 218822, NULL),
(101, 1892, 'FACTURADO', 'REGISTRADO', 1892, NULL, 0, '2026-03-13 15:57:53', '2026-03-13 15:59:20', NULL, 157, 1, 0, 182, 'SETP990202664', 0, 0, 0, 84598, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vnt_invoicesXsales`
--

CREATE TABLE `vnt_invoicesXsales` (
  `id` int NOT NULL,
  `remissionId` int DEFAULT NULL,
  `quoteId` int DEFAULT NULL,
  `invoiceId` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `vnt_invoicesXsales`
--

INSERT INTO `vnt_invoicesXsales` (`id`, `remissionId`, `quoteId`, `invoiceId`) VALUES
(82, 27, 145, 92),
(83, 28, 146, 92),
(84, 24, 141, 93),
(85, 25, 142, 93),
(86, 26, 144, 93),
(87, 23, 140, 94),
(88, 22, 139, 95),
(89, 33, 151, 96),
(90, 29, 147, 97),
(91, 30, 148, 97),
(92, 34, 154, 98),
(93, 36, 155, 99),
(94, 35, 156, 100),
(95, 37, 157, 101);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vnt_invoice_payments`
--

CREATE TABLE `vnt_invoice_payments` (
  `id` int NOT NULL,
  `value` decimal(11,2) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `invoiceId` int DEFAULT NULL,
  `methodPaymentId` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vnt_method_payments`
--

CREATE TABLE `vnt_method_payments` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` tinyint DEFAULT '1',
  `description` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `type` int NOT NULL,
  `method` varchar(100) DEFAULT NULL,
  `bank` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `vnt_method_payments`
--

INSERT INTO `vnt_method_payments` (`id`, `name`, `status`, `description`, `created_at`, `updated_at`, `deleted_at`, `type`, `method`, `bank`) VALUES
(1, 'EFECTIVO', 1, 'CASH', '2023-03-15 16:17:29', '2023-03-15 16:17:29', NULL, 2, 'CASH', 2),
(2, 'TRANSFERENCIA', 1, 'CASH', '2023-03-15 16:17:29', '2023-03-15 16:17:29', NULL, 2, 'DEBIT_TRANSFER', 2),
(3, 'CONTRA ENTREGA', 1, 'CREDIT', '2023-03-15 16:17:29', '2023-03-15 16:17:29', NULL, 1, 'undefined', 2),
(4, 'TARJETA DE CREDITO', 1, 'CASH', '2023-03-15 16:17:29', '2023-03-15 16:17:29', NULL, 2, 'CREDIT_CARD', 2),
(5, 'CREDITO 8 DÍAS', 1, 'CREDIT', '2023-03-15 16:17:29', '2023-03-15 16:17:29', NULL, 1, 'undefined', 2),
(6, 'CREDITO 15 DÍAS', 1, 'CREDIT', '2023-03-15 16:17:29', '2023-03-15 16:17:29', NULL, 1, 'undefined', 2),
(7, 'CREDITO 30 DÍAS', 1, 'CREDIT', '2023-03-15 16:17:29', '2023-03-15 16:17:29', NULL, 1, 'undefined', 2),
(8, 'CREDITO 45 DÍAS', 1, 'CREDIT', '2023-03-15 16:17:29', '2023-03-15 16:17:29', NULL, 1, 'undefined', 2),
(9, 'CREDITO 60 DÍAS', 1, 'CREDIT', '2023-03-15 16:17:29', '2023-03-15 16:17:29', NULL, 1, 'undefined', 2),
(10, 'TARJETA DEBITO', 1, 'CASH', '2023-03-15 16:17:29', '2023-03-15 16:17:29', NULL, 2, 'DEBIT_CARD', 2),
(11, 'NEQUI', 1, 'CASH', '2023-03-15 16:17:29', '2023-03-15 16:17:29', NULL, 2, 'DEBIT_TRANSFER', 2),
(12, 'DAVIPLATA', 1, 'CASH', '2023-03-15 16:17:29', '2023-03-15 16:17:29', NULL, 2, 'DEBIT_TRANSFER', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vnt_petty_cash`
--

CREATE TABLE `vnt_petty_cash` (
  `id` int NOT NULL,
  `base` int NOT NULL,
  `consecutive` int NOT NULL,
  `status` tinyint DEFAULT '1',
  `dateClose` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `userIdClose` int DEFAULT NULL,
  `userIdOpen` int DEFAULT NULL,
  `warehouseId` int DEFAULT NULL,
  `cashier` int DEFAULT NULL COMMENT 'Cajero asignado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `vnt_petty_cash`
--

INSERT INTO `vnt_petty_cash` (`id`, `base`, `consecutive`, `status`, `dateClose`, `created_at`, `updated_at`, `deleted_at`, `userIdClose`, `userIdOpen`, `warehouseId`, `cashier`) VALUES
(2, 650000, 1, 0, NULL, '2025-11-19 19:08:53', '2025-11-19 19:08:53', NULL, NULL, 2, 4, 6),
(8, 35000, 1, 0, NULL, '2025-11-20 17:57:00', '2025-11-20 17:57:00', NULL, NULL, 8, 6, 6),
(14, 320000, 2, 0, NULL, '2025-11-20 20:46:42', '2025-11-20 20:46:42', NULL, NULL, 8, 6, 6),
(16, 250000, 3, 0, '2026-01-21 18:04:54', '2025-11-20 21:08:49', '2026-01-21 18:04:54', NULL, 8, 8, 6, 6),
(18, 25555, 1, 0, '2026-01-31 00:04:15', '2026-01-21 19:59:54', '2026-01-31 00:04:15', NULL, 125, 101, 96, 101);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vnt_quotes`
--

CREATE TABLE `vnt_quotes` (
  `id` int NOT NULL,
  `consecutive` int NOT NULL,
  `status` enum('REGISTRADO','ANULADO','FACTURADO','REMISIÓN') NOT NULL,
  `typeQuote` enum('POS','INSTITUCIONAL') NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `customerId` int DEFAULT NULL COMMENT 'la sucursal del cliente del tenat',
  `warehouseId` int DEFAULT NULL COMMENT 'la variable de sesion del rap store',
  `userId` int DEFAULT NULL,
  `observations` text,
  `branchId` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `vnt_quotes`
--

INSERT INTO `vnt_quotes` (`id`, `consecutive`, `status`, `typeQuote`, `created_at`, `updated_at`, `deleted_at`, `customerId`, `warehouseId`, `userId`, `observations`, `branchId`) VALUES
(6, 1, 'REGISTRADO', 'POS', '2025-11-24 20:26:15', '2025-11-24 20:26:15', NULL, NULL, NULL, 8, 'Cliente: edwin  ca', NULL),
(7, 2, 'REGISTRADO', 'POS', '2025-11-24 20:27:22', '2025-11-24 20:27:22', NULL, NULL, NULL, 8, 'Cliente: edwin  ca', NULL),
(8, 3, 'REGISTRADO', 'POS', '2025-11-24 20:32:53', '2025-11-24 20:32:53', NULL, NULL, NULL, 8, 'Cliente: edwin  ca', NULL),
(9, 4, 'REGISTRADO', 'POS', '2025-11-24 20:35:19', '2025-11-24 20:35:19', NULL, NULL, NULL, 8, 'Cliente: edwin  ca', NULL),
(15, 5, 'REGISTRADO', 'POS', '2025-11-25 14:09:23', '2025-11-25 14:09:23', NULL, NULL, NULL, 8, 'Cliente: edwin  ca | ContactID: 1 | WarehouseID: 19', NULL),
(17, 6, 'REGISTRADO', 'POS', '2025-11-25 14:14:59', '2025-11-25 14:14:59', NULL, 1, 19, 8, 'Cliente: edwin  ca', 19),
(18, 7, 'REGISTRADO', 'POS', '2025-11-25 14:16:58', '2025-11-25 14:16:58', NULL, 1, 19, 8, 'Cliente: edwin  ca', 19),
(19, 8, 'REGISTRADO', 'POS', '2025-11-25 14:27:38', '2025-11-25 14:27:38', NULL, 14, 19, 8, 'Cliente: edwin  ca', 19),
(20, 9, 'REGISTRADO', 'POS', '2025-11-25 14:33:11', '2025-11-25 14:33:11', NULL, 1, 1, 8, 'Cliente: Yesi Alexander', 1),
(21, 10, 'REGISTRADO', 'POS', '2025-11-25 14:36:53', '2025-11-25 14:36:53', NULL, 1, 19, 8, 'Cliente: edwin  ca', 19),
(22, 11, 'REGISTRADO', 'POS', '2025-11-25 14:38:20', '2025-11-25 14:38:20', NULL, 1, 19, 8, 'Cliente: edwin  ca', 19),
(23, 12, 'REGISTRADO', 'POS', '2025-11-25 14:41:24', '2025-11-25 14:41:24', NULL, 1, 19, 8, 'Cliente: edwin ca', 19),
(26, 13, 'REGISTRADO', 'POS', '2025-11-25 14:46:29', '2025-11-25 14:46:29', NULL, 1, 19, 8, 'Cliente: edwin ca', 19),
(27, 14, 'REGISTRADO', 'POS', '2025-11-25 14:49:49', '2025-11-25 14:49:49', NULL, 15, 19, 8, 'Cliente: edwin ca', 19),
(28, 15, 'REGISTRADO', 'POS', '2025-11-25 14:57:35', '2025-11-25 17:27:36', NULL, 1, 19, 8, 'Cliente: edwin ca', 19),
(29, 16, 'REGISTRADO', 'POS', '2025-11-25 19:44:10', '2025-11-25 19:44:10', NULL, 19, 1, 8, '', 1),
(30, 17, 'REGISTRADO', 'POS', '2025-11-25 19:46:37', '2025-11-25 19:46:37', NULL, 19, 1, 8, '', 1),
(31, 18, 'REGISTRADO', 'POS', '2025-11-25 19:48:48', '2025-11-25 19:48:48', NULL, 19, 1, 8, '', 1),
(32, 19, 'REGISTRADO', 'POS', '2025-11-26 15:41:20', '2025-11-26 15:41:20', NULL, 19, 1, 8, 'Cliente: edwin ca', 1),
(33, 20, 'REGISTRADO', 'POS', '2025-11-26 16:05:49', '2025-11-26 16:05:49', NULL, 19, 1, 8, 'Cliente: edwin ca', 1),
(34, 21, 'REGISTRADO', 'POS', '2025-11-26 16:11:26', '2025-11-26 16:11:26', NULL, 19, 1, 8, 'Cliente: edwin ca', 1),
(35, 22, 'REGISTRADO', 'POS', '2025-11-26 16:13:43', '2025-11-26 16:13:43', NULL, 19, 1, 8, 'Cliente: edwin ca', 1),
(36, 23, 'REGISTRADO', 'POS', '2025-11-26 16:19:30', '2025-11-26 16:19:30', NULL, 19, 1, 8, 'Cliente: edwin ca', 1),
(37, 24, 'REGISTRADO', 'POS', '2025-11-26 21:55:10', '2025-11-26 21:55:10', NULL, 19, 1, 8, 'Cliente: edwin ca', 1),
(38, 25, 'REGISTRADO', 'POS', '2025-11-26 21:56:19', '2025-11-26 21:56:19', NULL, 1, 1, 8, 'Cliente: Yesi Alexander', 1),
(39, 26, 'REGISTRADO', 'POS', '2025-11-27 13:48:42', '2025-11-27 13:48:42', NULL, 15, 1, 8, 'Cliente: eduardo ', 1),
(40, 27, 'REGISTRADO', 'POS', '2025-11-27 15:44:33', '2025-11-27 15:51:03', NULL, 19, 1, 8, 'pruebas de edicion', 1),
(41, 28, 'REMISIÓN', 'POS', '2026-01-20 17:34:04', '2026-01-20 20:01:14', NULL, 19, 1, 8, NULL, 1),
(52, 29, 'REGISTRADO', 'POS', '2026-01-28 20:11:28', '2026-01-28 20:11:28', NULL, 60, 1, 8, NULL, 1),
(53, 30, 'REGISTRADO', 'POS', '2026-01-28 20:15:29', '2026-01-28 20:15:29', NULL, 60, 1, 8, NULL, 1),
(54, 31, 'REGISTRADO', 'POS', '2026-01-28 20:33:08', '2026-01-28 20:33:08', NULL, 19, 1, 8, NULL, 1),
(55, 32, 'REGISTRADO', 'POS', '2026-01-28 20:51:37', '2026-01-28 20:51:37', NULL, 60, 1, 8, NULL, 1),
(65, 33, 'REGISTRADO', 'POS', '2026-01-28 22:04:16', '2026-01-28 22:04:16', NULL, 19, 3, 8, NULL, 3),
(68, 34, 'REGISTRADO', 'POS', '2026-01-28 22:08:48', '2026-01-28 22:08:48', NULL, 19, 1, 8, NULL, 1),
(72, 35, 'REGISTRADO', 'POS', '2026-01-28 22:15:55', '2026-01-28 22:15:55', NULL, 60, 2, 8, NULL, 2),
(74, 36, 'REGISTRADO', 'POS', '2026-01-29 13:25:57', '2026-01-29 13:25:57', NULL, 60, 2, 8, NULL, 2),
(75, 37, 'REGISTRADO', 'POS', '2026-01-29 13:32:32', '2026-01-29 13:32:32', NULL, 60, 2, 8, NULL, 2),
(76, 38, 'REGISTRADO', 'POS', '2026-01-29 14:21:17', '2026-01-29 14:21:17', NULL, 1, 1, 8, 'prueba para stockRoom ', 1),
(77, 39, 'REMISIÓN', 'POS', '2026-01-29 14:25:17', '2026-01-30 14:23:57', NULL, 1, 3, 8, 'Otra prueba', 3),
(78, 40, 'FACTURADO', 'POS', '2026-01-29 15:05:37', '2026-01-29 15:06:18', NULL, 60, 1, 8, NULL, 1),
(79, 41, 'REMISIÓN', 'POS', '2026-01-29 15:04:30', '2026-02-02 16:00:34', NULL, 20, 4, 125, NULL, 4),
(80, 42, 'FACTURADO', 'POS', '2026-01-29 15:18:45', '2026-01-29 20:31:18', NULL, 60, 1, 153, NULL, 1),
(81, 43, 'FACTURADO', 'POS', '2026-01-29 19:36:20', '2026-01-29 20:05:46', NULL, 60, 1, 153, NULL, 1),
(82, 44, 'REMISIÓN', 'POS', '2026-01-29 20:19:28', '2026-01-29 20:20:27', NULL, 6, 4, 125, NULL, 4),
(83, 45, 'REGISTRADO', 'POS', '2026-01-29 21:11:14', '2026-01-29 21:11:14', NULL, 60, 1, 153, NULL, 1),
(84, 46, 'FACTURADO', 'POS', '2026-01-29 21:18:29', '2026-01-30 14:21:35', NULL, 60, 1, 153, NULL, 1),
(85, 47, 'REMISIÓN', 'POS', '2026-01-30 13:38:14', '2026-01-30 13:48:22', NULL, 61, 3, 8, 'prueba completa', 3),
(86, 48, 'REMISIÓN', 'POS', '2026-01-30 14:27:36', '2026-01-30 14:33:12', NULL, 12, 3, 8, 'validaciones con stock disponible en el store ', 3),
(87, 49, 'REMISIÓN', 'POS', '2026-01-30 14:35:18', '2026-01-30 14:35:48', NULL, 25, 3, 8, NULL, 3),
(88, 50, 'FACTURADO', 'POS', '2026-01-30 15:20:14', '2026-01-30 15:41:48', NULL, 60, 1, 153, NULL, 1),
(89, 51, 'REGISTRADO', 'POS', '2026-01-30 16:16:08', '2026-01-30 20:27:04', NULL, 60, 1, 153, NULL, 1),
(90, 52, 'REGISTRADO', 'POS', '2026-01-30 17:32:13', '2026-01-30 20:30:17', NULL, 60, 1, 153, NULL, 1),
(91, 53, 'FACTURADO', 'POS', '2026-01-30 20:38:11', '2026-02-01 17:13:23', NULL, 60, 1, 153, NULL, 1),
(92, 54, 'REMISIÓN', 'POS', '2026-01-31 00:12:48', '2026-02-01 17:07:48', NULL, 60, 1, 153, NULL, 1),
(93, 55, 'REMISIÓN', 'POS', '2026-01-31 00:17:13', '2026-02-01 17:06:59', NULL, 60, 1, 153, NULL, 1),
(94, 56, 'FACTURADO', 'POS', '2026-02-01 15:28:46', '2026-02-01 17:05:10', NULL, 60, 1, 8, NULL, 1),
(95, 57, 'FACTURADO', 'POS', '2026-02-01 15:39:56', '2026-02-01 16:57:58', NULL, 60, 1, 8, NULL, 1),
(96, 58, 'REMISIÓN', 'POS', '2026-02-01 22:25:11', '2026-02-01 22:37:21', NULL, 60, 1, 153, NULL, 1),
(97, 59, 'REMISIÓN', 'POS', '2026-02-01 22:27:19', '2026-02-01 22:32:53', NULL, 60, 1, 153, NULL, 1),
(98, 60, 'REGISTRADO', 'POS', '2026-02-02 17:03:35', '2026-02-02 17:04:43', NULL, 1, 1, 8, 'Yesi Alexander', 1),
(99, 61, 'REGISTRADO', 'POS', '2026-02-02 17:12:10', '2026-02-02 17:12:10', NULL, 13, 1, 8, NULL, 1),
(100, 62, 'REGISTRADO', 'POS', '2026-02-02 17:12:17', '2026-02-02 17:12:17', NULL, 13, 1, 8, NULL, 1),
(101, 63, 'REGISTRADO', 'POS', '2026-02-02 17:39:02', '2026-02-02 17:39:02', NULL, 25, 1, 8, NULL, 1),
(102, 64, 'REGISTRADO', 'POS', '2026-02-02 17:39:13', '2026-02-02 17:39:13', NULL, 25, 1, 8, NULL, 1),
(103, 65, 'REMISIÓN', 'POS', '2026-02-02 17:50:42', '2026-02-02 19:45:16', NULL, 22, 4, 125, NULL, 4),
(104, 66, 'REGISTRADO', 'POS', '2026-02-02 17:55:49', '2026-02-02 19:15:07', NULL, 25, 1, 8, 'Raquel', 1),
(105, 67, 'REGISTRADO', 'POS', '2026-02-02 19:22:22', '2026-02-02 19:22:50', NULL, 63, 1, 8, 'Ana Carolina', 1),
(106, 68, 'REGISTRADO', 'POS', '2026-02-02 19:41:25', '2026-02-02 19:41:25', NULL, 23, 1, 8, NULL, 1),
(107, 69, 'REGISTRADO', 'POS', '2026-02-02 19:44:40', '2026-02-02 19:44:40', NULL, 14, 1, 8, NULL, 1),
(108, 70, 'REGISTRADO', 'POS', '2026-02-02 19:48:23', '2026-02-02 19:48:23', NULL, 6, 1, 8, NULL, 1),
(109, 71, 'REGISTRADO', 'POS', '2026-02-02 20:03:33', '2026-02-02 20:04:00', NULL, 55, 1, 8, 'juan camilo', 1),
(110, 72, 'REGISTRADO', 'POS', '2026-02-02 20:13:06', '2026-02-02 20:13:34', NULL, 54, 1, 8, 'ja', 1),
(111, 73, 'REGISTRADO', 'POS', '2026-02-03 19:04:14', '2026-02-03 19:04:14', NULL, 63, 1, 8, 'prueba precio regular', 1),
(112, 74, 'REMISIÓN', 'POS', '2026-02-04 13:39:03', '2026-02-04 13:41:25', NULL, 60, 1, 8, NULL, 1),
(113, 75, 'REMISIÓN', 'POS', '2026-02-04 13:40:39', '2026-02-04 13:41:02', NULL, 60, 1, 8, NULL, 1),
(114, 76, 'REGISTRADO', 'POS', '2026-02-04 21:55:18', '2026-02-06 14:08:28', NULL, 60, 1, 8, NULL, 1),
(115, 77, 'REGISTRADO', 'POS', '2026-02-05 15:35:33', '2026-02-05 15:37:14', NULL, 60, 1, 8, NULL, 1),
(116, 78, 'REGISTRADO', 'POS', '2026-02-05 17:09:15', '2026-02-06 14:02:21', NULL, 60, 1, 8, NULL, 1),
(117, 79, 'REGISTRADO', 'POS', '2026-02-06 14:24:34', '2026-02-06 14:24:34', NULL, 60, 1, 8, NULL, 1),
(118, 80, 'REGISTRADO', 'POS', '2026-02-06 14:47:26', '2026-02-06 14:52:26', NULL, 60, 1, 8, NULL, 1),
(119, 81, 'REGISTRADO', 'POS', '2026-02-06 15:04:12', '2026-02-06 15:14:04', NULL, 60, 1, 8, NULL, 1),
(120, 82, 'REGISTRADO', 'POS', '2026-02-06 20:19:29', '2026-02-06 21:29:30', NULL, 60, 1, 8, NULL, 1),
(121, 83, 'REGISTRADO', 'POS', '2026-02-06 21:46:58', '2026-02-06 22:23:56', NULL, 60, 1, 8, NULL, 1),
(122, 84, 'REGISTRADO', 'POS', '2026-02-09 13:08:54', '2026-02-09 15:44:33', NULL, 60, 1, 8, NULL, 1),
(123, 85, 'REGISTRADO', 'POS', '2026-02-09 15:54:56', '2026-02-09 15:56:22', NULL, 60, 1, 8, NULL, 1),
(124, 86, 'REGISTRADO', 'POS', '2026-02-09 17:28:01', '2026-02-09 17:30:46', NULL, 60, 1, 8, NULL, 1),
(125, 87, 'REGISTRADO', 'POS', '2026-02-09 17:40:26', '2026-02-09 17:45:17', NULL, 60, 1, 8, NULL, 1),
(126, 88, 'REGISTRADO', 'POS', '2026-02-09 19:08:50', '2026-02-09 19:11:46', NULL, 60, 1, 8, NULL, 1),
(127, 89, 'REGISTRADO', 'POS', '2026-02-09 19:19:35', '2026-02-09 20:11:01', NULL, 60, 1, 8, NULL, 1),
(128, 90, 'REGISTRADO', 'POS', '2026-02-09 19:21:58', '2026-02-09 19:41:18', NULL, 60, 1, 8, NULL, 1),
(129, 91, 'REGISTRADO', 'POS', '2026-02-09 20:15:17', '2026-02-09 20:19:06', NULL, 60, 1, 8, NULL, 1),
(130, 92, 'REMISIÓN', 'POS', '2026-02-09 20:32:33', '2026-04-10 13:55:50', NULL, 60, 1, 8, NULL, 1),
(131, 93, 'REMISIÓN', 'POS', '2026-02-09 21:49:24', '2026-04-10 13:55:00', NULL, 60, 1, 8, NULL, 1),
(132, 94, 'REMISIÓN', 'POS', '2026-02-09 21:56:00', '2026-04-10 13:54:18', NULL, 60, 1, 8, NULL, 1),
(133, 95, 'REGISTRADO', 'POS', '2026-02-13 16:45:20', '2026-02-13 16:45:20', NULL, 74, 8, 175, NULL, 8),
(134, 96, 'REMISIÓN', 'POS', '2026-02-16 19:09:40', '2026-04-10 13:53:33', NULL, 69, 1, 8, NULL, 1),
(135, 97, 'REMISIÓN', 'POS', '2026-02-16 20:22:58', '2026-04-10 13:52:48', NULL, 54, 1, 8, NULL, 1),
(136, 98, 'REMISIÓN', 'POS', '2026-02-16 21:12:21', '2026-04-10 13:51:57', NULL, 54, 1, 8, NULL, 1),
(137, 99, 'REMISIÓN', 'POS', '2026-02-17 17:13:33', '2026-04-10 13:44:54', NULL, 54, 1, 8, NULL, 1),
(138, 100, 'REMISIÓN', 'POS', '2026-02-17 17:14:43', '2026-04-10 13:43:57', NULL, 54, 1, 8, NULL, 1),
(139, 101, 'FACTURADO', 'POS', '2026-02-17 17:18:05', '2026-02-17 17:26:39', NULL, 54, 1, 8, NULL, 1),
(140, 102, 'FACTURADO', 'POS', '2026-02-17 17:57:42', '2026-02-18 16:23:55', NULL, 54, 1, 8, NULL, 1),
(141, 103, 'FACTURADO', 'POS', '2026-02-18 13:09:13', '2026-02-18 15:26:27', NULL, 54, 1, 8, NULL, 1),
(142, 104, 'FACTURADO', 'POS', '2026-02-18 13:11:00', '2026-02-18 15:26:27', NULL, 54, 1, 8, NULL, 1),
(143, 105, 'REMISIÓN', 'POS', '2026-02-18 13:43:40', '2026-04-10 13:41:17', NULL, 54, 1, 8, NULL, 1),
(144, 106, 'FACTURADO', 'POS', '2026-02-18 13:52:50', '2026-02-18 15:26:27', NULL, 54, 1, 8, NULL, 1),
(145, 107, 'REMISIÓN', 'POS', '2026-02-18 14:04:35', '2026-04-10 13:40:11', NULL, 54, 1, 8, NULL, 1),
(146, 108, 'REMISIÓN', 'POS', '2026-02-18 14:07:39', '2026-04-10 13:38:47', NULL, 54, 1, 8, NULL, 1),
(147, 109, 'FACTURADO', 'POS', '2026-02-19 16:50:33', '2026-03-11 13:39:59', NULL, 54, 1, 8, NULL, 1),
(148, 110, 'FACTURADO', 'POS', '2026-02-20 14:44:51', '2026-03-11 13:40:00', NULL, 54, 1, 8, NULL, 1),
(149, 111, 'REMISIÓN', 'POS', '2026-02-24 20:20:44', '2026-02-24 20:23:27', NULL, 53, 1, 8, 'prueba para ventas * mes', 1),
(150, 112, 'REMISIÓN', 'POS', '2026-02-24 20:30:31', '2026-02-24 20:31:01', NULL, 1, 1, 8, 'más registros x ventas', 1),
(151, 113, 'FACTURADO', 'POS', '2026-03-10 22:03:19', '2026-03-10 22:06:18', NULL, 54, 1, 8, NULL, 1),
(153, 114, 'REMISIÓN', 'POS', '2026-03-12 19:32:53', '2026-04-10 13:36:26', NULL, 62, 1, 8, NULL, 62),
(154, 115, 'FACTURADO', 'POS', '2026-03-12 19:35:48', '2026-03-12 20:34:06', NULL, 62, 1, 8, NULL, 1),
(155, 116, 'FACTURADO', 'POS', '2026-03-13 13:47:53', '2026-03-13 14:12:19', NULL, 62, 1, 8, NULL, 1),
(156, 117, 'FACTURADO', 'POS', '2026-03-13 13:51:21', '2026-03-13 14:13:07', NULL, 62, 1, 8, NULL, 1),
(157, 118, 'FACTURADO', 'POS', '2026-03-13 15:35:06', '2026-03-13 15:58:01', NULL, 62, 1, 8, NULL, 1),
(158, 119, 'REGISTRADO', 'POS', '2026-03-13 15:42:39', '2026-03-13 15:42:39', NULL, 62, 1, 8, NULL, 1),
(159, 120, 'REGISTRADO', 'POS', '2026-03-16 17:12:19', '2026-03-16 17:12:19', NULL, 85, 1, 8, NULL, 1),
(160, 121, 'REGISTRADO', 'POS', '2026-03-16 17:33:56', '2026-03-16 17:33:56', NULL, 88, 88, 8, NULL, 88),
(161, 122, 'REGISTRADO', 'POS', '2026-03-16 17:39:10', '2026-03-16 17:39:10', NULL, 89, 1, 8, NULL, 89),
(162, 123, 'REGISTRADO', 'POS', '2026-04-10 13:23:00', '2026-04-10 13:23:00', NULL, 98, 1, 8, NULL, 98),
(163, 124, 'REGISTRADO', 'POS', '2026-04-10 14:07:06', '2026-04-10 14:07:06', NULL, 100, 1, 8, NULL, 100),
(164, 125, 'REGISTRADO', 'POS', '2026-04-10 15:39:30', '2026-04-10 15:39:30', NULL, 21, 1, 8, NULL, 21),
(165, 126, 'REGISTRADO', 'POS', '2026-04-12 16:28:44', '2026-04-12 18:04:05', NULL, 78, 1, 8, NULL, 78),
(168, 127, 'REGISTRADO', 'POS', '2026-04-13 13:32:12', '2026-04-13 13:32:12', NULL, 18, 1, 8, NULL, 18),
(169, 128, 'REGISTRADO', 'POS', '2026-04-13 15:02:00', '2026-04-13 15:02:00', NULL, 99, 1, 8, NULL, 99),
(170, 129, 'REGISTRADO', 'POS', '2026-04-13 15:36:10', '2026-04-13 15:36:10', NULL, 10, 1, 8, NULL, 10),
(171, 130, 'REGISTRADO', 'POS', '2026-04-13 15:38:43', '2026-04-13 15:38:43', NULL, 82, 1, 8, NULL, 82),
(172, 131, 'REGISTRADO', 'POS', '2026-04-13 15:52:38', '2026-04-13 15:52:38', NULL, 91, 1, 8, NULL, 91),
(173, 132, 'REGISTRADO', 'POS', '2026-04-13 16:26:28', '2026-04-13 16:26:28', NULL, 77, 1, 8, NULL, 77);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vnt_reasons_petty_cash`
--

CREATE TABLE `vnt_reasons_petty_cash` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` tinyint DEFAULT '1',
  `type` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `vnt_reasons_petty_cash`
--

INSERT INTO `vnt_reasons_petty_cash` (`id`, `name`, `status`, `type`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Ventas', 1, 'i', '2025-11-16 18:14:47', NULL, NULL),
(2, 'Devolucion de vale empleados', 1, 'i', '2025-11-16 18:15:02', NULL, NULL),
(3, 'Pago de factura', 1, 'e', '2025-11-16 18:15:10', NULL, NULL),
(4, 'Vale empleado', 1, 'e', '2025-11-16 18:15:57', NULL, NULL),
(5, 'Apertura', 1, 'i', '2025-11-20 21:07:25', NULL, NULL),
(6, 'Anticipo', 1, 'i', '2025-11-26 14:06:53', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vnt_reconciliations`
--

CREATE TABLE `vnt_reconciliations` (
  `id` int NOT NULL,
  `reconciliation` tinyint NOT NULL,
  `observations` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `pettyCashId` int DEFAULT NULL,
  `userId` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `vnt_reconciliations`
--

INSERT INTO `vnt_reconciliations` (`id`, `reconciliation`, `observations`, `created_at`, `updated_at`, `deleted_at`, `pettyCashId`, `userId`) VALUES
(5, 1, 'FESU26674 PENDIENTE DEL 10/10/25,FEDS16098 CORRESPONDE A NUEVA FESU DE NOTA CREDITO', '2025-11-26 17:04:14', '2025-11-26 17:04:15', NULL, 16, 8),
(6, 1, 'cierre', '2025-11-26 19:48:34', '2025-11-26 19:48:35', NULL, 16, 8),
(7, 1, 'Cierre II', '2025-11-26 19:51:28', '2025-11-26 19:51:28', NULL, 16, 8),
(8, 1, 'CIERRE III', '2025-11-26 20:01:47', '2025-11-26 20:01:47', NULL, 16, 8),
(9, 1, '', '2025-11-27 19:48:12', '2025-11-27 19:48:13', NULL, 16, 8),
(10, 1, 'Cierre 3:12 pm', '2025-11-27 20:13:03', '2025-11-27 20:13:03', NULL, 16, 8),
(11, 1, '', '2025-11-27 20:34:29', '2025-11-27 20:34:29', NULL, 16, 8),
(12, 1, 'Cierre 28 noviembre', '2025-11-28 21:47:50', '2025-11-28 21:47:51', NULL, 16, 8),
(13, 0, 'Arqueo 10:30 a.m.', '2025-12-01 15:38:32', '2025-12-01 15:38:32', NULL, 16, 8),
(14, 0, '', '2025-12-02 17:52:06', '2025-12-02 17:52:06', NULL, 16, 8),
(15, 0, 'Arqueo 1:05 pm 02/12/2025', '2025-12-02 18:06:02', '2025-12-02 18:06:02', NULL, 16, 8),
(16, 0, 'Arqueo 2:09 pm 02-diciembre-2025, validación dinero en efectivo', '2025-12-02 19:10:32', '2025-12-02 19:10:32', NULL, 16, 8),
(17, 1, 'Cierre Caja ', '2025-12-02 19:13:53', '2025-12-02 19:13:53', NULL, 16, 8),
(18, 1, '', '2025-12-04 17:26:20', '2025-12-04 17:26:20', NULL, 16, 8),
(19, 0, '', '2026-01-21 16:19:13', '2026-01-21 16:19:13', NULL, 16, 8),
(20, 0, '', '2026-01-21 16:44:56', '2026-01-21 16:44:56', NULL, 16, 8),
(21, 1, '', '2026-01-21 16:45:17', '2026-01-21 16:45:17', NULL, 16, 8),
(22, 1, '8', '2026-01-21 18:04:54', '2026-01-21 18:04:54', NULL, 16, 8),
(23, 0, '', '2026-01-21 20:44:12', '2026-01-21 20:44:12', NULL, 18, 101),
(24, 0, '', '2026-01-21 21:27:39', '2026-01-21 21:27:39', NULL, 18, 8),
(25, 0, '', '2026-01-29 15:12:49', '2026-01-29 15:12:49', NULL, 18, 125),
(26, 1, '', '2026-01-29 15:15:24', '2026-01-29 15:15:24', NULL, 18, 125),
(27, 1, '6', '2026-01-31 00:04:15', '2026-01-31 00:04:15', NULL, 18, 125);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vnt_routes`
--

CREATE TABLE `vnt_routes` (
  `id` int NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `zone_id` int DEFAULT NULL,
  `salesman_id` int DEFAULT NULL COMMENT 'usuario vendedor',
  `sale_day` enum('lunes','martes','miercoles','jueves','viernes','sabado','domingo') DEFAULT NULL COMMENT 'dia de la semana en que se hace la ruta de ventas',
  `delivery_day` enum('lunes','martes','miercoles','jueves','viernes','sabado','domingo') DEFAULT NULL COMMENT 'dia de entrega',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `vnt_routes`
--

INSERT INTO `vnt_routes` (`id`, `name`, `zone_id`, `salesman_id`, `sale_day`, `delivery_day`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 'Ruta Chapinero', 3, 106, 'lunes', 'viernes', '2025-12-09 21:39:34', '2026-01-22 17:01:52', NULL),
(8, 'Ruta Teusaquillo', 3, 106, 'viernes', 'lunes', '2026-01-22 17:02:21', '2026-01-22 17:02:21', NULL),
(9, 'Ruta Restrepo', 2, 125, 'martes', 'viernes', '2026-01-22 17:09:03', '2026-01-22 17:09:03', NULL),
(10, 'Ruta Fontibon', 4, 125, 'miercoles', 'sabado', '2026-03-16 13:19:16', '2026-03-16 13:19:16', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vnt_terms`
--

CREATE TABLE `vnt_terms` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL,
  `days` int NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
  `district` bigint DEFAULT NULL,
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
(1, 1, 'Cartucho', 'calle 25a45a-20', '124523', 19851, 16, 0, 1, '0', 1, 0, NULL, NULL, 1, 'FIJA', '2025-11-12 16:21:49', '2026-02-13 16:58:27', NULL),
(2, 1, 'otra sucursal', 'calle 5#6a-20', '1154', 20375, 16, 0, 1, '0', 1, 0, NULL, NULL, 0, 'DESPACHO', '2025-11-12 17:10:22', '2026-02-13 16:58:28', NULL),
(3, 2, 'guafalara', 'casdas55', '4564', 19712, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2025-11-12 17:12:13', '2025-11-12 21:27:00', NULL),
(4, 3, 'Calle 36G No 11A', 'Calle 36G No 11A -77', '1100011', 20237, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2025-11-12 20:43:22', '2025-11-12 20:44:38', NULL),
(5, 3, 'CASA', 'KRA 12 A', '1100112', 19562, 16, 0, 1, '0', 1, 1, NULL, NULL, 0, 'FIJA', '2025-11-12 20:43:47', '2025-11-12 20:44:38', '2025-11-12 20:44:38'),
(6, 4, 'Miercoles', 'calle 20·20a-5', '11101', 20201, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2025-11-12 21:31:33', '2025-11-12 21:31:33', NULL),
(7, 5, 'marsella', 'calle 24a·3b-2', '110254', 20283, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2025-11-12 21:39:13', '2025-11-12 21:39:13', NULL),
(8, 5, 'Fontibon', 'calle 4#4-5', '555', 20071, 16, 0, 1, '0', 1, 1, NULL, NULL, 0, 'FIJA', '2025-11-12 21:42:37', '2025-11-13 14:33:07', '2025-11-13 14:33:07'),
(9, 6, 'Lima', 'calle 130 #4a-20', '11204', 20071, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2025-11-13 14:29:23', '2025-11-13 14:29:23', NULL),
(10, 8, 'Parque Nacional', 'calle 4#20a9', '4457', 20078, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2025-11-13 16:07:21', '2025-11-13 20:08:13', NULL),
(11, 9, 'Albania', 'calle 4#20a9', '45644', 19908, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2025-11-13 16:15:53', '2025-11-13 17:41:39', NULL),
(12, 10, 'Principal', 'calle 123#4a6', '110211', 20614, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2025-11-13 17:05:29', '2025-11-13 17:05:29', NULL),
(13, 10, 'Chico', 'calle 100 # 10a-1', '11011', 20078, 16, 0, 1, '0', 1, 1, NULL, NULL, 0, 'FIJA', '2025-11-13 20:01:56', '2025-11-13 20:04:46', NULL),
(14, 10, 'Japon', 'calle 123 # 7a-20', '12486', 20377, 16, 0, 1, '0', 1, 1, NULL, NULL, 0, 'DESPACHO', '2025-11-14 16:01:35', '2025-11-14 16:02:07', NULL),
(15, 9, 'Kennedy', 'Calle 42', '110111', 19711, 16, 0, 1, '0', 1, 1, NULL, NULL, 0, 'DESPACHO', '2025-11-18 20:51:25', '2025-11-24 14:20:36', '2025-11-24 14:20:36'),
(16, 7, 'Sucursal Sur', 'Carrera 10 # 25 -11', '110011', 19711, 16, 0, 1, '0', 1, 1, NULL, NULL, 0, 'FIJA', '2025-11-18 21:53:50', '2025-11-18 21:53:50', NULL),
(17, 11, 'Principal', 'dsa', '111', 20614, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2025-11-19 16:07:48', '2025-11-19 16:07:48', NULL),
(18, 12, 'Principal', 'calle 123', '11011', 19714, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2025-11-21 20:09:22', '2025-11-21 20:09:22', NULL),
(19, 13, 'Principal', 'calle 24 #73b12', '110244', NULL, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2025-11-24 16:51:31', '2025-11-24 16:51:31', NULL),
(20, 15, 'Chapinero', 'calle 19#20a15', '', 19711, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2025-11-24 17:23:32', '2026-01-23 13:50:10', NULL),
(21, 19, 'Principal', 'callle 45 sur ', '110111', 19711, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2025-11-24 19:06:06', '2025-11-24 19:06:06', NULL),
(22, 20, 'Principal', 'calle 45 sur ', '110111', 19711, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2025-11-24 19:09:23', '2025-11-24 19:09:23', NULL),
(23, 21, 'Principal', 'calle 45 ', '', 19711, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2025-11-24 19:13:17', '2025-11-24 19:13:17', NULL),
(24, 22, 'Principal', 'calle 3 # 51a10', '', NULL, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2025-11-25 13:54:54', '2025-11-25 13:54:54', NULL),
(25, 23, 'Principal', 'Ex error molestias u', '001', 19556, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2025-11-25 16:13:05', '2025-12-01 21:17:52', NULL),
(26, 24, 'Principal', 'calle 45 sur n 78 b 13', '110232', 20622, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-01-22 14:00:09', '2026-01-22 14:00:09', NULL),
(27, 25, 'Principal', 'calle 45 sur n 78 b 13', '110232', 19569, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-01-22 14:35:54', '2026-01-22 14:35:54', NULL),
(28, 26, 'Principal', 'Calle 51 #7-12', '110125', 19711, 16, 0, 1, '0', 1, 1, 1, NULL, 1, 'FIJA', '2026-01-22 21:49:22', '2026-01-26 16:53:02', NULL),
(29, 27, 'Principal', 'callle 45 sur ', '77777', 19554, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-01-23 19:21:18', '2026-01-23 19:21:18', NULL),
(30, 28, 'Principal', 'calle 24 #73b12', '110224', 19554, 16, 0, 1, '0', 1, 1, 0, NULL, 1, 'FIJA', '2026-01-23 20:20:09', '2026-02-13 14:26:07', NULL),
(31, 29, 'Principal', 'calle 24 #73b12', '110244', 19622, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-01-23 20:39:12', '2026-01-23 20:39:12', NULL),
(32, 30, 'Principal', 'calle 24 Bis #73b12', '110244', 20635, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-01-23 21:13:50', '2026-01-23 21:13:50', NULL),
(33, 31, 'Principal', 'Calle 51 #7-12', '11204', 20465, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-01-26 14:42:34', '2026-01-26 14:42:34', NULL),
(34, 32, 'Principal', 'calle 4 bis # 41b-65', '123313', 19711, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-01-26 14:55:14', '2026-01-26 14:55:14', NULL),
(35, 33, 'Principal', '123123', '123213', 19554, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-01-26 15:54:14', '2026-01-26 15:54:14', NULL),
(36, 34, 'Principal', 'calle 4 bis # 41b-65', '110129', 19711, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-01-26 15:58:53', '2026-01-26 15:58:53', NULL),
(37, 35, 'Principal', 'calle 4 bis # 41b-65', '11011', 19711, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-01-26 16:05:58', '2026-01-26 16:05:58', NULL),
(38, 30, 'Sucursal Bogotá', 'Kra 78 #12-45', '110111', 19711, 16, 0, 1, '0', 1, 1, 3, NULL, 0, 'DESPACHO', '2026-01-26 17:05:50', '2026-01-26 17:07:56', NULL),
(39, 25, 'Sucursal Planta', 'Calle 8B #7-12', '110254', 20462, 16, 0, 1, '0', 1, 1, 7, NULL, 0, 'DESPACHO', '2026-01-26 17:10:31', '2026-01-26 17:10:31', NULL),
(40, 36, 'Principal', 'calle 4 bis # 41b-65', '110111', 19711, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-01-26 17:14:04', '2026-01-26 17:14:04', NULL),
(41, 37, 'Principal', 'calle 4 bis # 41b-65', '110111', 19711, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-01-26 21:37:39', '2026-01-26 21:37:39', NULL),
(42, 38, 'Principal', '41242112', '110111', 19711, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-01-26 21:43:18', '2026-01-26 21:43:18', NULL),
(43, 39, 'Principal', 'calle 4 bis # 41b-65', '122112', 19554, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-01-26 21:48:22', '2026-01-26 21:48:22', NULL),
(44, 40, 'Principal', 'calle 4 bis # 41b-65', '110111', 19554, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-01-27 14:21:58', '2026-01-27 14:21:58', NULL),
(45, 41, 'Principal', 'calle 4 bis # 41b-65', '110120', 19554, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-01-27 14:38:16', '2026-01-27 14:38:16', NULL),
(46, 42, 'Principal', 'calle 4 bis # 41b-65', '110111', 19554, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-01-27 14:46:49', '2026-01-27 14:46:49', NULL),
(47, 43, 'Principal', 'calle 4 bis # 41b-65', '110111', 19554, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-01-27 14:57:15', '2026-01-27 14:57:15', NULL),
(48, 44, 'Principal', 'calle 4 bis # 41b-65', '110111', 19554, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-01-27 15:09:04', '2026-01-27 15:09:04', NULL),
(49, 45, 'Principal', 'calle 4 bis # 41b-65', '11011', 19554, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-01-27 15:16:49', '2026-01-27 15:16:49', NULL),
(50, 46, 'Principal', 'calle 4 bis # 41b-65', '110111', 20375, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-01-27 15:38:40', '2026-01-27 15:38:40', NULL),
(51, 47, 'Principal', 'calle 4 bis # 41b-65', '110111', 19711, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-01-27 16:07:21', '2026-01-27 16:07:21', NULL),
(52, 53, 'Principal', 'calle 4 bis # 41b-65', '110111', 19711, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-01-27 16:32:36', '2026-01-27 16:32:36', NULL),
(53, 55, 'Principal', 'calle 4 bis # 41b-65', '110111', 19554, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-01-27 16:48:53', '2026-01-27 16:48:53', NULL),
(54, 56, 'Principal', 'calle 4 bis # 41b-65', '110111', 19554, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-01-27 17:07:11', '2026-01-27 17:07:11', NULL),
(55, 57, 'Principal', 'calle 4 bis # 41b-65', '110111', 19554, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-01-27 18:01:46', '2026-01-27 18:01:46', NULL),
(56, 58, 'Principal', '41242112', '11011', 19711, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-01-28 13:09:22', '2026-01-28 13:09:22', NULL),
(57, 59, 'Principal', 'calle 4 bis # 41b-65', '110111', 19554, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-01-28 13:26:09', '2026-01-28 13:26:09', NULL),
(58, 60, 'Principal', 'calle  4 bis #41b-65', '110111', 19711, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-01-28 13:34:16', '2026-01-28 13:34:16', NULL),
(59, 61, 'Principal', 'calle 4 bis # 41b-65', '110111', 20375, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-01-28 13:42:16', '2026-01-28 13:42:16', NULL),
(60, 62, 'Principal', '41242112', '4546', 19554, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-01-28 13:49:25', '2026-01-28 13:49:25', NULL),
(61, 63, 'Principal', 'calle  4 bis #41b-65', '110111', 19554, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-01-28 13:57:47', '2026-01-28 13:57:47', NULL),
(62, 64, 'Principal', 'calle 4 bis # 41b-65', '110111', 20375, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-01-28 14:33:20', '2026-01-28 14:33:20', NULL),
(63, 65, 'Principal', 'calle 4 bis # 41b-65', '110111', 19711, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-01-28 14:40:16', '2026-01-28 14:40:16', NULL),
(64, 66, 'Principal', 'calle 4 bis # 41b-65', '110111', 20375, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-01-28 14:44:50', '2026-01-28 14:44:50', NULL),
(65, 67, 'Principal', 'calle 4 ·5b20', '110111', 20375, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-01-28 16:23:28', '2026-01-28 16:23:28', NULL),
(66, 68, 'Principal', 'calle 4 bis # 41b-65', '110111', 19711, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-01-28 16:28:37', '2026-01-28 16:28:37', NULL),
(67, 69, 'Principal', 'calle 4 bis # 41b-65', '110111', 19554, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-01-28 17:50:09', '2026-01-28 17:50:09', NULL),
(68, 70, 'Principal', 'calle 4 bis # 41b-65', '110111', 19711, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-01-28 19:11:25', '2026-01-28 19:11:25', NULL),
(69, 71, 'Principal', 'calle 4 bis # 41b-65', '120123', 19711, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-01-30 15:55:57', '2026-01-30 15:55:57', NULL),
(70, 72, 'Principal', 'Calle 85 #12B -12', '110232', 19622, 16, 0, 1, '0', 1, 1, 5, NULL, 1, 'FIJA', '2026-01-30 16:12:01', '2026-01-30 16:12:31', NULL),
(71, 73, 'Principal', 'calle 4 ·5b20', '110111', 19711, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-02-03 16:07:05', '2026-02-03 16:07:05', NULL),
(72, 74, 'Principal', 'calle 4 bis # 41b-65', '110111', 19711, 16, 0, 1, '0', 1, 1, 1, NULL, 1, 'FIJA', '2026-02-03 16:30:31', '2026-02-03 16:39:18', NULL),
(73, 75, 'Principal', 'calle 4 bis # 41b-65', '110111', 19711, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-02-03 16:43:14', '2026-02-03 16:43:14', NULL),
(74, 76, 'Principal', 'calle 4 bis # 41b-65 55', '110111', 19711, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-02-03 16:52:32', '2026-03-11 21:40:21', NULL),
(75, 76, 'otraa', 'calle 80', '110111', 19711, 16, 0, 1, '0', 1, 1, 1, NULL, 0, 'DESPACHO', '2026-02-03 20:39:49', '2026-03-11 21:40:21', '2026-03-11 21:40:21'),
(76, 77, 'Principal', 'calle 4 bis # 41b-65', '110111', 19711, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-02-04 15:36:58', '2026-02-04 15:36:58', NULL),
(77, 78, 'Principal', '', '', 19711, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-02-04 16:04:58', '2026-02-04 16:04:58', NULL),
(78, 79, 'Principal', 'Calle 85 #12B -12', '110244', 20449, 16, 0, 1, '0', 1, 1, 9, NULL, 1, 'FIJA', '2026-02-13 14:00:51', '2026-02-13 14:13:17', NULL),
(79, 80, 'Principal', 'Calle 85 #12B -12', '110244', 19711, 16, 0, 1, '0', 1, 1, 1, NULL, 1, 'FIJA', '2026-02-13 14:17:30', '2026-02-13 14:18:26', NULL),
(80, 81, 'Principal', 'Dg 78 #12-89', '110245', 19622, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-02-13 14:24:28', '2026-02-13 14:24:28', NULL),
(81, 28, 'Cali', 'Carrera 85 #78-01', '110145', 20622, 16, 0, 1, '0', 1, 1, NULL, NULL, 0, 'DESPACHO', '2026-02-13 14:31:26', '2026-02-13 14:31:26', NULL),
(82, 82, 'Principal', '', '', 19711, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-02-13 16:44:29', '2026-02-13 16:44:29', NULL),
(83, 83, 'Principal', 'calle 4 bis # 41b-65', '110111', 19711, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-02-16 19:18:44', '2026-02-16 19:18:44', NULL),
(84, 84, 'Principal', 'Calle 78B #67-09', '11475', 19622, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-02-16 20:44:59', '2026-02-16 20:44:59', NULL),
(85, 85, 'Principal', 'calle 45 sr ', '11205', 19622, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-03-02 17:05:27', '2026-03-16 17:08:13', NULL),
(88, 85, 'sucurslal norte', 'calle 123 n 78 b 12', '110111', 20206, 16, 0, 1, '0', 1, 1, NULL, NULL, 0, 'FIJA', '2026-03-16 17:09:59', '2026-03-16 17:09:59', NULL),
(89, 85, 'sucursal sur', 'calle 79b 16', '110111', 20206, 16, 0, 1, '0', 1, 1, NULL, NULL, 0, 'FIJA', '2026-03-16 17:11:02', '2026-03-16 17:11:02', NULL),
(90, 86, 'Principal', 'Calle 78 G #11 - 89', '11205', 19622, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-03-17 19:48:13', '2026-03-17 19:48:13', NULL),
(91, 87, 'Principal', 'Calle 78 G #11 - 89', '11205', 19711, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-03-17 19:59:03', '2026-03-17 19:59:03', NULL),
(92, 88, 'Principal', 'calle 4 bis # 41b-65', '110111', 19554, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-04-09 19:37:36', '2026-04-09 19:37:36', NULL),
(93, 90, 'Principal', 'calle 4 bis # 41b-65', '110111', 19554, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-04-09 19:44:18', '2026-04-09 19:44:18', NULL),
(94, 91, 'Principal', 'calle 4 bis # 41b-65', '110111', 19711, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-04-09 19:52:24', '2026-04-09 19:52:24', NULL),
(95, 92, 'Principal', 'cliente direccion', '110111', 19554, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-04-09 20:05:37', '2026-04-09 20:05:37', NULL),
(96, 93, 'Principal', 'calle 4 bis # 41b-65', '110111', 19711, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-04-09 20:16:46', '2026-04-09 20:16:46', NULL),
(97, 94, 'Principal', 'calle 4 bis # 41b-65', '110111', 19554, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-04-09 20:24:44', '2026-04-09 20:24:44', NULL),
(98, 95, 'Principal', '', '', 19711, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-04-09 20:37:40', '2026-04-09 20:37:40', NULL),
(99, 96, 'Principal', 'Carrera 15 #75-89 Bis', '', 19711, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-04-10 13:20:19', '2026-04-10 13:20:19', NULL),
(100, 97, 'Principal', 'Dg 145 #08 - 78', '', 20622, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-04-10 14:06:12', '2026-04-10 14:06:12', NULL),
(101, 98, 'Principal', 'Carrera 14 #63-78', '', 19711, 16, 0, 1, '0', 1, 1, NULL, NULL, 1, 'FIJA', '2026-04-10 17:40:16', '2026-04-10 17:40:16', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vnt_zones`
--

CREATE TABLE `vnt_zones` (
  `id` int NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `vnt_zones`
--

INSERT INTO `vnt_zones` (`id`, `name`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 'Zona Sur I', '2025-12-09 19:05:16', '2026-03-16 13:16:35', NULL),
(3, 'Zona Norte', '2025-12-09 19:47:56', '2025-12-09 19:47:56', NULL),
(4, 'Zona Occidente', '2025-12-10 14:30:43', '2025-12-10 14:36:15', NULL),
(5, 'Zona Oriente I', '2026-01-22 15:33:14', '2026-01-22 15:33:35', NULL),
(6, 'Zona Noroccidente', '2026-03-16 13:17:33', '2026-03-16 13:17:33', NULL),
(7, 'Zona prueba', '2026-04-10 19:10:29', '2026-04-10 19:10:29', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cmp_campaigns`
--
ALTER TABLE `cmp_campaigns`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cmp_campaign_customers`
--
ALTER TABLE `cmp_campaign_customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cmp_campaign_customer` (`campaign_id`,`customer_id`);

--
-- Indices de la tabla `cnf_audit_status_log`
--
ALTER TABLE `cnf_audit_status_log`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cnf_buttons`
--
ALTER TABLE `cnf_buttons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indices de la tabla `cnf_company_options`
--
ALTER TABLE `cnf_company_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_option_idx` (`company_id`,`option_id`),
  ADD KEY `option_idx` (`option_id`),
  ADD KEY `value_idx` (`value`);

--
-- Indices de la tabla `cnf_invoices`
--
ALTER TABLE `cnf_invoices`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cnf_positions`
--
ALTER TABLE `cnf_positions`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cnf_pricelist`
--
ALTER TABLE `cnf_pricelist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indices de la tabla `cnf_priceprofile`
--
ALTER TABLE `cnf_priceprofile`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indices de la tabla `cnf_taxes`
--
ALTER TABLE `cnf_taxes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indices de la tabla `cnf_templates`
--
ALTER TABLE `cnf_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indices de la tabla `imp_comments`
--
ALTER TABLE `imp_comments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `imp_comments_index_0` (`import_id`),
  ADD KEY `imp_comments_index_1` (`user_id`),
  ADD KEY `idx_import_created` (`import_id`,`created_at`);

--
-- Indices de la tabla `imp_imports`
--
ALTER TABLE `imp_imports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `imp_imports_index_0` (`item_id`),
  ADD KEY `imp_imports_index_1` (`user_id`),
  ADD KEY `imp_imports_index_2` (`label_id`),
  ADD KEY `imp_imports_index_3` (`status`),
  ADD KEY `imp_imports_index_4` (`packing_id`);

--
-- Indices de la tabla `imp_items_setup`
--
ALTER TABLE `imp_items_setup`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `imp_items_setup_index_0` (`item_id`),
  ADD KEY `imp_items_setup_index_1` (`supplier_id`),
  ADD KEY `imp_items_setup_index_2` (`purchase_unit`),
  ADD KEY `imp_items_setup_index_3` (`item_id`,`supplier_id`);

--
-- Indices de la tabla `imp_labels`
--
ALTER TABLE `imp_labels`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indices de la tabla `imp_packing`
--
ALTER TABLE `imp_packing`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `shipping_id` (`shipping_id`);

--
-- Indices de la tabla `imp_shippments`
--
ALTER TABLE `imp_shippments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indices de la tabla `imp_status`
--
ALTER TABLE `imp_status`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `imp_status_history`
--
ALTER TABLE `imp_status_history`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `imp_status_history_index_0` (`import_id`),
  ADD KEY `imp_status_history_index_1` (`previous_state`),
  ADD KEY `imp_status_history_index_2` (`new_state`),
  ADD KEY `imp_status_history_index_3` (`user_id`);

--
-- Indices de la tabla `imp_unconfirmed_qty`
--
ALTER TABLE `imp_unconfirmed_qty`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `inv_unconfirmed_qty_index_0` (`item_id`);

--
-- Indices de la tabla `inv_applications`
--
ALTER TABLE `inv_applications`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `inv_categories`
--
ALTER TABLE `inv_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indices de la tabla `inv_command`
--
ALTER TABLE `inv_command`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indices de la tabla `inv_delivery_types`
--
ALTER TABLE `inv_delivery_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_s_tipo_entrega_id` (`id`);

--
-- Indices de la tabla `inv_detail_inventory`
--
ALTER TABLE `inv_detail_inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `itemId` (`itemId`),
  ADD KEY `storeId` (`storeId`);

--
-- Indices de la tabla `inv_detail_inv_adjustments`
--
ALTER TABLE `inv_detail_inv_adjustments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `inventoryAdjustmentId` (`inventoryAdjustmentId`),
  ADD KEY `itemId` (`itemId`);

--
-- Indices de la tabla `inv_detail_remissions`
--
ALTER TABLE `inv_detail_remissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `remissionId` (`remissionId`),
  ADD KEY `itemId` (`itemId`),
  ADD KEY `invoiceId` (`invoiceId`),
  ADD KEY `tax` (`tax`);

--
-- Indices de la tabla `inv_detail_transfers`
--
ALTER TABLE `inv_detail_transfers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `transferId` (`transferId`),
  ADD KEY `itemId` (`itemId`);

--
-- Indices de la tabla `inv_detail_transfer_requests`
--
ALTER TABLE `inv_detail_transfer_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `transferRequestId` (`transferRequestId`),
  ADD KEY `itemId` (`itemId`);

--
-- Indices de la tabla `inv_image_gallery`
--
ALTER TABLE `inv_image_gallery`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `itemId` (`itemId`);

--
-- Indices de la tabla `inv_inventory_adjustments`
--
ALTER TABLE `inv_inventory_adjustments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `reasonId` (`reasonId`);

--
-- Indices de la tabla `inv_inventory_count`
--
ALTER TABLE `inv_inventory_count`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `itemId` (`itemId`);

--
-- Indices de la tabla `inv_items`
--
ALTER TABLE `inv_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `categoryId` (`categoryId`),
  ADD KEY `commandId` (`commandId`),
  ADD KEY `brandId` (`brandId`),
  ADD KEY `houseId` (`houseId`),
  ADD KEY `purchasing_unit` (`purchasing_unit`),
  ADD KEY `consumption_unit` (`consumption_unit`),
  ADD KEY `taxId` (`taxId`);

--
-- Indices de la tabla `inv_items_accessories`
--
ALTER TABLE `inv_items_accessories`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `inv_items_cut_details`
--
ALTER TABLE `inv_items_cut_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_item` (`item_id`),
  ADD KEY `idx_cut` (`cut_id`),
  ADD KEY `idx_customer` (`customer_id`);

--
-- Indices de la tabla `inv_items_dimensions`
--
ALTER TABLE `inv_items_dimensions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indices de la tabla `inv_items_import_price_calculations`
--
ALTER TABLE `inv_items_import_price_calculations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `inv_items_locations`
--
ALTER TABLE `inv_items_locations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `itemId` (`itemId`),
  ADD KEY `storeId` (`storeId`);

--
-- Indices de la tabla `inv_items_store`
--
ALTER TABLE `inv_items_store`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `itemId` (`itemId`),
  ADD KEY `storeId` (`storeId`);

--
-- Indices de la tabla `inv_item_applications`
--
ALTER TABLE `inv_item_applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `itemId` (`itemId`),
  ADD KEY `applicationsId` (`applicationsId`);

--
-- Indices de la tabla `inv_item_brand`
--
ALTER TABLE `inv_item_brand`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indices de la tabla `inv_item_house`
--
ALTER TABLE `inv_item_house`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indices de la tabla `inv_item_observations`
--
ALTER TABLE `inv_item_observations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inv_item_observations_item_id_idx` (`item_id`);

--
-- Indices de la tabla `inv_locations`
--
ALTER TABLE `inv_locations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `inv_purchase_orders`
--
ALTER TABLE `inv_purchase_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indices de la tabla `inv_purchase_order_details`
--
ALTER TABLE `inv_purchase_order_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `purchase_ordersId` (`purchase_ordersId`),
  ADD KEY `itemId` (`itemId`);

--
-- Indices de la tabla `inv_purchase_requests`
--
ALTER TABLE `inv_purchase_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indices de la tabla `inv_purchase_request_details`
--
ALTER TABLE `inv_purchase_request_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `purchase_requestsId` (`purchase_requestsId`),
  ADD KEY `itemId` (`itemId`);

--
-- Indices de la tabla `inv_reasons`
--
ALTER TABLE `inv_reasons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indices de la tabla `inv_remissions`
--
ALTER TABLE `inv_remissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_inv_remissions_id` (`id`),
  ADD KEY `idx_inv_remissions_quote` (`quoteId`),
  ADD KEY `idx_inv_remissions_warehouse` (`warehouseId`);

--
-- Indices de la tabla `inv_seriales`
--
ALTER TABLE `inv_seriales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `itemId` (`itemId`);

--
-- Indices de la tabla `inv_status`
--
ALTER TABLE `inv_status`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `inv_store`
--
ALTER TABLE `inv_store`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indices de la tabla `inv_transfers`
--
ALTER TABLE `inv_transfers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `warehouseFromId` (`storeFromId`),
  ADD KEY `warehouseToId` (`storeToId`);

--
-- Indices de la tabla `inv_transfer_requests`
--
ALTER TABLE `inv_transfer_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `quoteId` (`quoteId`);

--
-- Indices de la tabla `inv_unit_measurements`
--
ALTER TABLE `inv_unit_measurements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indices de la tabla `inv_values`
--
ALTER TABLE `inv_values`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `itemId` (`itemId`),
  ADD KEY `warehouseId` (`warehouseId`);

--
-- Indices de la tabla `inv_wordpress_configs`
--
ALTER TABLE `inv_wordpress_configs`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `inv_wordpress_images`
--
ALTER TABLE `inv_wordpress_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `image_id` (`image_id`),
  ADD KEY `itemId` (`itemId`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `prd_data_process_order`
--
ALTER TABLE `prd_data_process_order`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `prd_data_process_order_index_0` (`production_order_id`),
  ADD KEY `prd_data_process_order_index_1` (`field_processId`);

--
-- Indices de la tabla `prd_fields_process`
--
ALTER TABLE `prd_fields_process`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prd_fields_process_index_0` (`processId`);

--
-- Indices de la tabla `prd_material_items`
--
ALTER TABLE `prd_material_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prd_material_items_index_0` (`material_itemId`),
  ADD KEY `prd_material_items_index_1` (`production_order_id`),
  ADD KEY `prd_material_items_index_2` (`process_id`);

--
-- Indices de la tabla `prd_process`
--
ALTER TABLE `prd_process`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `prd_process_item`
--
ALTER TABLE `prd_process_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prd_process_item_index_0` (`processId`),
  ADD KEY `prd_process_item_index_1` (`itemId`);

--
-- Indices de la tabla `prd_production_order`
--
ALTER TABLE `prd_production_order`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `prd_production_order_index_0` (`item_id`),
  ADD KEY `prd_production_order_index_1` (`warehouse_customer_id`),
  ADD KEY `prd_production_order_index_2` (`status`);

--
-- Indices de la tabla `prd_status`
--
ALTER TABLE `prd_status`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `prd_users_process`
--
ALTER TABLE `prd_users_process`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD UNIQUE KEY `prd_users_process_index_0` (`operator_user_id`,`process_id`),
  ADD KEY `prd_users_process_index_1` (`process_id`);

--
-- Indices de la tabla `prd_work_names`
--
ALTER TABLE `prd_work_names`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `prd_work_names_index_0` (`prd_order_id`);

--
-- Indices de la tabla `tick_departments`
--
ALTER TABLE `tick_departments`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tick_department_user`
--
ALTER TABLE `tick_department_user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indices de la tabla `tick_requests`
--
ALTER TABLE `tick_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `status_id` (`status_id`);

--
-- Indices de la tabla `tick_request_history`
--
ALTER TABLE `tick_request_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_id` (`request_id`),
  ADD KEY `from_status_id` (`from_status_id`),
  ADD KEY `to_status_id` (`to_status_id`);

--
-- Indices de la tabla `tick_statuses`
--
ALTER TABLE `tick_statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indices de la tabla `vnt_companies`
--
ALTER TABLE `vnt_companies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `identification` (`identification`);

--
-- Indices de la tabla `vnt_companies_routes`
--
ALTER TABLE `vnt_companies_routes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `idx_company_id` (`company_id`),
  ADD KEY `idx_route_id` (`route_id`);

--
-- Indices de la tabla `vnt_contacts`
--
ALTER TABLE `vnt_contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `warehouseId` (`warehouseId`),
  ADD KEY `positionId` (`positionId`);

--
-- Indices de la tabla `vnt_delivery_types`
--
ALTER TABLE `vnt_delivery_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indices de la tabla `vnt_detail_petty_cash`
--
ALTER TABLE `vnt_detail_petty_cash`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `pettyCashId` (`pettyCashId`),
  ADD KEY `reasonPettyCashId` (`reasonPettyCashId`),
  ADD KEY `methodPaymentId` (`methodPaymentId`),
  ADD KEY `invoiceId` (`invoiceId`);

--
-- Indices de la tabla `vnt_detail_quotes`
--
ALTER TABLE `vnt_detail_quotes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `quoteId` (`quoteId`),
  ADD KEY `itemId` (`itemId`);

--
-- Indices de la tabla `vnt_detail_reconciliations`
--
ALTER TABLE `vnt_detail_reconciliations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `methodPaymentId` (`methodPaymentId`),
  ADD KEY `reconciliationId` (`reconciliationId`);

--
-- Indices de la tabla `vnt_invoices`
--
ALTER TABLE `vnt_invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indices de la tabla `vnt_invoicesXsales`
--
ALTER TABLE `vnt_invoicesXsales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indices de la tabla `vnt_invoice_payments`
--
ALTER TABLE `vnt_invoice_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `invoiceId` (`invoiceId`);

--
-- Indices de la tabla `vnt_method_payments`
--
ALTER TABLE `vnt_method_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indices de la tabla `vnt_petty_cash`
--
ALTER TABLE `vnt_petty_cash`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `warehouseId` (`warehouseId`);

--
-- Indices de la tabla `vnt_quotes`
--
ALTER TABLE `vnt_quotes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `fk_vnt_quotes_customer` (`customerId`),
  ADD KEY `fk_vnt_quotes_warehouse` (`warehouseId`),
  ADD KEY `fk_vnt_quotes_branch` (`branchId`);

--
-- Indices de la tabla `vnt_reasons_petty_cash`
--
ALTER TABLE `vnt_reasons_petty_cash`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indices de la tabla `vnt_reconciliations`
--
ALTER TABLE `vnt_reconciliations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `pettyCashId` (`pettyCashId`),
  ADD KEY `userId` (`userId`);

--
-- Indices de la tabla `vnt_routes`
--
ALTER TABLE `vnt_routes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `tat_routes_index_0` (`zone_id`),
  ADD KEY `tat_routes_index_1` (`salesman_id`);

--
-- Indices de la tabla `vnt_terms`
--
ALTER TABLE `vnt_terms`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `vnt_warehouses`
--
ALTER TABLE `vnt_warehouses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `companyId` (`companyId`);

--
-- Indices de la tabla `vnt_zones`
--
ALTER TABLE `vnt_zones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cmp_campaigns`
--
ALTER TABLE `cmp_campaigns`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `cmp_campaign_customers`
--
ALTER TABLE `cmp_campaign_customers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `cnf_audit_status_log`
--
ALTER TABLE `cnf_audit_status_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cnf_buttons`
--
ALTER TABLE `cnf_buttons`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cnf_company_options`
--
ALTER TABLE `cnf_company_options`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT de la tabla `cnf_invoices`
--
ALTER TABLE `cnf_invoices`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `cnf_positions`
--
ALTER TABLE `cnf_positions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `cnf_pricelist`
--
ALTER TABLE `cnf_pricelist`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `cnf_priceprofile`
--
ALTER TABLE `cnf_priceprofile`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cnf_taxes`
--
ALTER TABLE `cnf_taxes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `cnf_templates`
--
ALTER TABLE `cnf_templates`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `imp_comments`
--
ALTER TABLE `imp_comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT de la tabla `imp_imports`
--
ALTER TABLE `imp_imports`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de la tabla `imp_items_setup`
--
ALTER TABLE `imp_items_setup`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de la tabla `imp_labels`
--
ALTER TABLE `imp_labels`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT de la tabla `imp_packing`
--
ALTER TABLE `imp_packing`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `imp_shippments`
--
ALTER TABLE `imp_shippments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `imp_status`
--
ALTER TABLE `imp_status`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `imp_status_history`
--
ALTER TABLE `imp_status_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT de la tabla `imp_unconfirmed_qty`
--
ALTER TABLE `imp_unconfirmed_qty`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `inv_applications`
--
ALTER TABLE `inv_applications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inv_categories`
--
ALTER TABLE `inv_categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT de la tabla `inv_command`
--
ALTER TABLE `inv_command`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `inv_delivery_types`
--
ALTER TABLE `inv_delivery_types`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `inv_detail_inventory`
--
ALTER TABLE `inv_detail_inventory`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inv_detail_inv_adjustments`
--
ALTER TABLE `inv_detail_inv_adjustments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT de la tabla `inv_detail_remissions`
--
ALTER TABLE `inv_detail_remissions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT de la tabla `inv_detail_transfers`
--
ALTER TABLE `inv_detail_transfers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `inv_detail_transfer_requests`
--
ALTER TABLE `inv_detail_transfer_requests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `inv_image_gallery`
--
ALTER TABLE `inv_image_gallery`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT de la tabla `inv_inventory_adjustments`
--
ALTER TABLE `inv_inventory_adjustments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `inv_inventory_count`
--
ALTER TABLE `inv_inventory_count`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inv_items`
--
ALTER TABLE `inv_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1125;

--
-- AUTO_INCREMENT de la tabla `inv_items_accessories`
--
ALTER TABLE `inv_items_accessories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `inv_items_cut_details`
--
ALTER TABLE `inv_items_cut_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `inv_items_dimensions`
--
ALTER TABLE `inv_items_dimensions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `inv_items_import_price_calculations`
--
ALTER TABLE `inv_items_import_price_calculations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `inv_items_locations`
--
ALTER TABLE `inv_items_locations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `inv_items_store`
--
ALTER TABLE `inv_items_store`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT de la tabla `inv_item_applications`
--
ALTER TABLE `inv_item_applications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inv_item_brand`
--
ALTER TABLE `inv_item_brand`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `inv_item_house`
--
ALTER TABLE `inv_item_house`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `inv_item_observations`
--
ALTER TABLE `inv_item_observations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `inv_locations`
--
ALTER TABLE `inv_locations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inv_purchase_orders`
--
ALTER TABLE `inv_purchase_orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inv_purchase_order_details`
--
ALTER TABLE `inv_purchase_order_details`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inv_purchase_requests`
--
ALTER TABLE `inv_purchase_requests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inv_purchase_request_details`
--
ALTER TABLE `inv_purchase_request_details`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inv_reasons`
--
ALTER TABLE `inv_reasons`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `inv_remissions`
--
ALTER TABLE `inv_remissions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT de la tabla `inv_seriales`
--
ALTER TABLE `inv_seriales`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inv_status`
--
ALTER TABLE `inv_status`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inv_store`
--
ALTER TABLE `inv_store`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `inv_transfers`
--
ALTER TABLE `inv_transfers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `inv_transfer_requests`
--
ALTER TABLE `inv_transfer_requests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `inv_unit_measurements`
--
ALTER TABLE `inv_unit_measurements`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT de la tabla `inv_values`
--
ALTER TABLE `inv_values`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3891;

--
-- AUTO_INCREMENT de la tabla `inv_wordpress_configs`
--
ALTER TABLE `inv_wordpress_configs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `inv_wordpress_images`
--
ALTER TABLE `inv_wordpress_images`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT de la tabla `prd_data_process_order`
--
ALTER TABLE `prd_data_process_order`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT de la tabla `prd_fields_process`
--
ALTER TABLE `prd_fields_process`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `prd_material_items`
--
ALTER TABLE `prd_material_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `prd_process`
--
ALTER TABLE `prd_process`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `prd_process_item`
--
ALTER TABLE `prd_process_item`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `prd_production_order`
--
ALTER TABLE `prd_production_order`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `prd_status`
--
ALTER TABLE `prd_status`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `prd_users_process`
--
ALTER TABLE `prd_users_process`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `prd_work_names`
--
ALTER TABLE `prd_work_names`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tick_departments`
--
ALTER TABLE `tick_departments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tick_department_user`
--
ALTER TABLE `tick_department_user`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `tick_requests`
--
ALTER TABLE `tick_requests`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `tick_request_history`
--
ALTER TABLE `tick_request_history`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `tick_statuses`
--
ALTER TABLE `tick_statuses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `vnt_companies`
--
ALTER TABLE `vnt_companies`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT de la tabla `vnt_companies_routes`
--
ALTER TABLE `vnt_companies_routes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT de la tabla `vnt_contacts`
--
ALTER TABLE `vnt_contacts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT de la tabla `vnt_delivery_types`
--
ALTER TABLE `vnt_delivery_types`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `vnt_detail_petty_cash`
--
ALTER TABLE `vnt_detail_petty_cash`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=191;

--
-- AUTO_INCREMENT de la tabla `vnt_detail_quotes`
--
ALTER TABLE `vnt_detail_quotes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=336;

--
-- AUTO_INCREMENT de la tabla `vnt_detail_reconciliations`
--
ALTER TABLE `vnt_detail_reconciliations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT de la tabla `vnt_invoices`
--
ALTER TABLE `vnt_invoices`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT de la tabla `vnt_invoicesXsales`
--
ALTER TABLE `vnt_invoicesXsales`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT de la tabla `vnt_invoice_payments`
--
ALTER TABLE `vnt_invoice_payments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT de la tabla `vnt_method_payments`
--
ALTER TABLE `vnt_method_payments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `vnt_petty_cash`
--
ALTER TABLE `vnt_petty_cash`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `vnt_quotes`
--
ALTER TABLE `vnt_quotes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=174;

--
-- AUTO_INCREMENT de la tabla `vnt_reasons_petty_cash`
--
ALTER TABLE `vnt_reasons_petty_cash`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `vnt_reconciliations`
--
ALTER TABLE `vnt_reconciliations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de la tabla `vnt_routes`
--
ALTER TABLE `vnt_routes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `vnt_terms`
--
ALTER TABLE `vnt_terms`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `vnt_warehouses`
--
ALTER TABLE `vnt_warehouses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT de la tabla `vnt_zones`
--
ALTER TABLE `vnt_zones`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cmp_campaign_customers`
--
ALTER TABLE `cmp_campaign_customers`
  ADD CONSTRAINT `fk_cmp_campaign_customers_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `cmp_campaigns` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `imp_comments`
--
ALTER TABLE `imp_comments`
  ADD CONSTRAINT `imp_comments_import_id_foreign` FOREIGN KEY (`import_id`) REFERENCES `imp_imports` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Filtros para la tabla `imp_imports`
--
ALTER TABLE `imp_imports`
  ADD CONSTRAINT `imp_imports_label_id_foreign` FOREIGN KEY (`label_id`) REFERENCES `imp_labels` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `packing_id` FOREIGN KEY (`packing_id`) REFERENCES `imp_packing` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Filtros para la tabla `imp_packing`
--
ALTER TABLE `imp_packing`
  ADD CONSTRAINT `shipping_id` FOREIGN KEY (`shipping_id`) REFERENCES `imp_shippments` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Filtros para la tabla `imp_status_history`
--
ALTER TABLE `imp_status_history`
  ADD CONSTRAINT `imp_status_history_import_id_foreign` FOREIGN KEY (`import_id`) REFERENCES `imp_imports` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `imp_status_history_new_state_foreign` FOREIGN KEY (`new_state`) REFERENCES `imp_status` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `imp_status_history_previous_state_foreign` FOREIGN KEY (`previous_state`) REFERENCES `imp_status` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Filtros para la tabla `inv_detail_inventory`
--
ALTER TABLE `inv_detail_inventory`
  ADD CONSTRAINT `inv_detail_inventory_ibfk01` FOREIGN KEY (`itemId`) REFERENCES `inv_items` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `inv_detail_inventory_ibfk02` FOREIGN KEY (`storeId`) REFERENCES `inv_store` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Filtros para la tabla `inv_detail_inv_adjustments`
--
ALTER TABLE `inv_detail_inv_adjustments`
  ADD CONSTRAINT `inv_detail_inv_adjustments_ibfk_1` FOREIGN KEY (`inventoryAdjustmentId`) REFERENCES `inv_inventory_adjustments` (`id`),
  ADD CONSTRAINT `inv_detail_inv_adjustments_ibfk_2` FOREIGN KEY (`itemId`) REFERENCES `inv_items` (`id`);

--
-- Filtros para la tabla `inv_detail_remissions`
--
ALTER TABLE `inv_detail_remissions`
  ADD CONSTRAINT `inv_detail_remissions_ibfk_1` FOREIGN KEY (`remissionId`) REFERENCES `inv_remissions` (`id`),
  ADD CONSTRAINT `inv_detail_remissions_ibfk_2` FOREIGN KEY (`itemId`) REFERENCES `inv_items` (`id`);

--
-- Filtros para la tabla `inv_detail_transfers`
--
ALTER TABLE `inv_detail_transfers`
  ADD CONSTRAINT `inv_detail_transfers_ibfk_1` FOREIGN KEY (`transferId`) REFERENCES `inv_transfers` (`id`),
  ADD CONSTRAINT `inv_detail_transfers_ibfk_2` FOREIGN KEY (`itemId`) REFERENCES `inv_items` (`id`);

--
-- Filtros para la tabla `inv_detail_transfer_requests`
--
ALTER TABLE `inv_detail_transfer_requests`
  ADD CONSTRAINT `inv_detail_transfer_requests_ibfk_1` FOREIGN KEY (`transferRequestId`) REFERENCES `inv_transfer_requests` (`id`),
  ADD CONSTRAINT `inv_detail_transfer_requests_ibfk_2` FOREIGN KEY (`itemId`) REFERENCES `inv_items` (`id`);

--
-- Filtros para la tabla `inv_image_gallery`
--
ALTER TABLE `inv_image_gallery`
  ADD CONSTRAINT `inv_image_gallery_ibfk_1` FOREIGN KEY (`itemId`) REFERENCES `inv_items` (`id`);

--
-- Filtros para la tabla `inv_inventory_adjustments`
--
ALTER TABLE `inv_inventory_adjustments`
  ADD CONSTRAINT `inv_inventory_adjustments_ibfk_1` FOREIGN KEY (`reasonId`) REFERENCES `inv_reasons` (`id`);

--
-- Filtros para la tabla `inv_inventory_count`
--
ALTER TABLE `inv_inventory_count`
  ADD CONSTRAINT `inv_inventory_count_ibfk_1` FOREIGN KEY (`itemId`) REFERENCES `inv_items` (`id`);

--
-- Filtros para la tabla `inv_items_dimensions`
--
ALTER TABLE `inv_items_dimensions`
  ADD CONSTRAINT `item_id` FOREIGN KEY (`item_id`) REFERENCES `inv_items` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Filtros para la tabla `tick_department_user`
--
ALTER TABLE `tick_department_user`
  ADD CONSTRAINT `tick_department_user_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `tick_departments` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `tick_requests`
--
ALTER TABLE `tick_requests`
  ADD CONSTRAINT `tick_requests_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `tick_departments` (`id`),
  ADD CONSTRAINT `tick_requests_ibfk_2` FOREIGN KEY (`status_id`) REFERENCES `tick_statuses` (`id`);

--
-- Filtros para la tabla `tick_request_history`
--
ALTER TABLE `tick_request_history`
  ADD CONSTRAINT `tick_request_history_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `tick_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tick_request_history_ibfk_2` FOREIGN KEY (`from_status_id`) REFERENCES `tick_statuses` (`id`),
  ADD CONSTRAINT `tick_request_history_ibfk_3` FOREIGN KEY (`to_status_id`) REFERENCES `tick_statuses` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
