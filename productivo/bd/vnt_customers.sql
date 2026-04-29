-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: mysql:3306
-- Tiempo de generación: 30-01-2026 a las 14:50:39
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
-- Estructura de tabla para la tabla `vnt_customers`
--

CREATE TABLE `vnt_customers` (
  `id` int NOT NULL,
  `company_id` int DEFAULT NULL,
  `typePerson` varchar(255) DEFAULT NULL,
  `typeIdentificationId` int DEFAULT NULL,
  `identification` varchar(15) NOT NULL,
  `regimeId` int DEFAULT NULL,
  `cityId` int DEFAULT NULL,
  `businessName` varchar(255) DEFAULT NULL,
  `billingEmail` varchar(255) DEFAULT NULL,
  `firstName` varchar(255) DEFAULT NULL,
  `lastName` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `business_phone` varchar(100) DEFAULT NULL,
  `status` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `vnt_customers`
--

INSERT INTO `vnt_customers` (`id`, `company_id`, `typePerson`, `typeIdentificationId`, `identification`, `regimeId`, `cityId`, `businessName`, `billingEmail`, `firstName`, `lastName`, `address`, `business_phone`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(9, 8, 'Natural', 1, '8888888', NULL, 19554, NULL, 'prueba@gmail.com', 'pryebas', 'pruebas', 'calle 45 sur ', '4541310', 1, '2025-12-17 19:51:12', '2025-12-17 19:51:12', NULL),
(10, 8, 'Juridica', 1, '454534545345454', NULL, 20375, 'pruebas', 'prubas@gmail.com', NULL, NULL, 'calle 45 dur ', '34534534543', 1, '2025-12-17 19:56:00', '2025-12-17 19:56:00', NULL),
(11, 8, 'Juridica', 2, '5656456456', 4, 19555, 'dfgdfgdfg', 'fdgdfgdf@gmail.com', NULL, NULL, 'dfgdfgdfgdf', '56546456546', 1, '2025-12-17 19:59:54', '2025-12-17 19:59:54', NULL),
(12, 8, 'Natural', 1, '54645656', 3, 19554, NULL, 'pruebastat@gmail.com', 'pruebastat', 'pruebastat', 'calle 45 sur ', '4541310', 1, '2025-12-17 20:06:27', '2025-12-17 20:06:27', NULL),
(13, 8, 'Natural', 1, '575958', 3, 19554, NULL, 'tatpruebs@gmail.com', 'tatpruebs', 'tatpruebs', 'calle 4s sur ', '4541310', 1, '2025-12-17 20:08:25', '2025-12-17 20:08:25', NULL),
(14, 8, 'Juridica', 1, '8888888888888', 3, 19554, '88888', '8888@gmail.com', NULL, NULL, '888888', '88888888', 1, '2025-12-17 20:15:01', '2025-12-17 20:15:01', NULL),
(15, 8, 'Natural', 1, '123312312', 3, 20375, NULL, '123312312@gmil.com', '123312312', '123312312', '123312312', '123312312', 1, '2025-12-17 20:46:58', '2025-12-17 20:46:58', NULL),
(16, 8, 'Natural', 1, '785854', 4, 19555, NULL, 'bywybi@mailinator.com', 'Trevor', 'Christian', 'Delectus distinctio', '+1 (371) 508-8074', 1, '2025-12-18 14:52:08', '2025-12-18 14:52:08', NULL),
(17, 8, 'Juridica', 1, '44475555', 2, 20375, 'babilonia', 'kacus@mailinator.com', NULL, NULL, 'babilonia', '+1 (558) 578-2106', 1, '2025-12-18 14:58:05', '2025-12-18 14:58:05', NULL),
(18, 8, 'Juridica', 5, '784444', 4, 19557, 'pppa', 'xado@mailinator.com', NULL, NULL, 'calle 22 4', '+1 (864) 447-2397', 1, '2025-12-18 15:05:27', '2025-12-18 15:05:27', NULL),
(19, 8, 'Natural', 4, '47587865', 2, 20071, NULL, 'xejowyze@mailinator.com', 'Nichole', 'Lester', 'calle 24a20', '+1 (931) 356-1343', 1, '2025-12-18 15:08:28', '2025-12-18 15:08:28', NULL),
(20, 8, 'Juridica', 1, 'Reprehenderit ', 6, 20567, 'ju', 'paty@mailinator.com', NULL, NULL, 'callle 24', '+1 (715) 881-6979', 1, '2025-12-18 15:12:32', '2025-12-18 15:12:32', NULL),
(21, 8, 'Juridica', 4, '777776321456', 3, NULL, 'ddd', 'qymeruw@mailinator.com', NULL, NULL, 'calle 22', '+1 (274) 411-6639', 1, '2025-12-18 15:15:45', '2025-12-18 15:38:22', NULL),
(22, 8, 'Natural', 4, '7778566', 3, 20282, NULL, 'nope@mailinator.com', 'Carolyn', 'Arnold', 'calle 40', '+1 (285) 968-6801', 1, '2025-12-18 15:52:08', '2025-12-18 15:52:08', NULL),
(23, 8, 'Natural', 6, '7778564554', 1, 19554, NULL, 'cupita@mailinator.com', 'yo', 'tu', 'calle 123', '+1 (369) 202-4674', 1, '2025-12-18 16:13:00', '2025-12-18 16:13:00', NULL),
(24, 8, 'Natural', 6, '4545454', 4, 19554, NULL, 'fywilosagy@mailinator.com', 'Karyn', 'Pennington', 'carrera 70 # 3a20', '+1 (141) 373-2797', 0, '2025-12-18 16:15:10', '2025-12-18 19:15:24', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `vnt_customers`
--
ALTER TABLE `vnt_customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `vnt_customers_index_0` (`company_id`),
  ADD KEY `vnt_customers_index_1` (`typeIdentificationId`),
  ADD KEY `vnt_customers_index_2` (`regimeId`),
  ADD KEY `vnt_customers_index_3` (`cityId`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `vnt_customers`
--
ALTER TABLE `vnt_customers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
