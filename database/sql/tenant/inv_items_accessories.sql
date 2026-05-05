-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: mysql:3306
-- Tiempo de generación: 05-05-2026 a las 15:52:43
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
(4, 1113, 538, 'prueba'),
(5, 30, 546, 'ert-12');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `inv_items_accessories`
--
ALTER TABLE `inv_items_accessories`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `inv_items_accessories`
--
ALTER TABLE `inv_items_accessories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
