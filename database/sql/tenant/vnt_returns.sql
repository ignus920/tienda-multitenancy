-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: mysql:3306
-- Tiempo de generación: 07-05-2026 a las 15:58:59
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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vnt_returns`
--

CREATE TABLE `vnt_returns` (
  `id` bigint UNSIGNED NOT NULL,
  `remission_id` int NOT NULL,
  `item_id` int NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `requested_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `original_qty` decimal(12,2) NOT NULL,
  `commercial_qty` decimal(12,2) NOT NULL,
  `lab_qty` decimal(12,2) DEFAULT '0.00',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '1:Comercial, 2:Laboratorio, 3:Bodega, 4:Contabilidad, 6:Total',
  `obs_commercial` text COLLATE utf8mb4_unicode_ci,
  `obs_lab` text COLLATE utf8mb4_unicode_ci,
  `obs_warehouse` text COLLATE utf8mb4_unicode_ci,
  `obs_accounting` text COLLATE utf8mb4_unicode_ci,
  `nc_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nc_file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lab_processed_at` timestamp NULL DEFAULT NULL,
  `accounting_processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `vnt_returns`
--

INSERT INTO `vnt_returns` (`id`, `remission_id`, `item_id`, `user_id`, `requested_at`, `original_qty`, `commercial_qty`, `lab_qty`, `status`, `obs_commercial`, `obs_lab`, `obs_warehouse`, `obs_accounting`, `nc_number`, `nc_file`, `lab_processed_at`, `accounting_processed_at`, `created_at`, `updated_at`) VALUES
(1, 58, 1122, 8, '2026-05-06 20:41:22', 1.00, 1.00, 0.00, 1, 'pruebas', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-06 20:41:22', '2026-05-06 20:41:22'),
(2, 56, 1123, 8, '2026-05-07 14:02:44', 2.00, 1.00, 0.00, 1, 'el cargador tiene un amperaje diferente se  calienta', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-07 14:02:44', '2026-05-07 14:02:44');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `vnt_returns`
--
ALTER TABLE `vnt_returns`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `vnt_returns`
--
ALTER TABLE `vnt_returns`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
