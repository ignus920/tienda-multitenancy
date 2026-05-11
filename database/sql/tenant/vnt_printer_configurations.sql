-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: mysql:3306
-- Tiempo de generación: 06-05-2026 a las 16:57:28
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
-- Estructura de tabla para la tabla `vnt_printer_configurations`
--

CREATE TABLE `vnt_printer_configurations` (
  `id` bigint UNSIGNED NOT NULL,
  `context` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ordenp, estacion_empaque, etc',
  `user_id` bigint UNSIGNED NOT NULL,
  `printer_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'IP o nombre de red',
  `proxy_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL del script local',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `vnt_printer_configurations`
--
ALTER TABLE `vnt_printer_configurations`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `vnt_printer_configurations`
--
ALTER TABLE `vnt_printer_configurations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
