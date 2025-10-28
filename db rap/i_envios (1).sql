-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 28-10-2025 a las 17:33:15
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `fervicom`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `i_envios`
--

CREATE TABLE `i_envios` (
  `id` int(11) NOT NULL,
  `consecutivo` int(11) NOT NULL DEFAULT 0,
  `etd` date NOT NULL,
  `del` text NOT NULL,
  `via` varchar(30) NOT NULL,
  `transportadora` varchar(50) NOT NULL,
  `observaciones` text NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `i_envios`
--

INSERT INTO `i_envios` (`id`, `consecutivo`, `etd`, `del`, `via`, `transportadora`, `observaciones`, `fecha_creacion`) VALUES
(61, 1, '2025-10-25', '1233424123412', 'AEREA', '12', 'asdasdasd', '2025-10-22 09:24:49');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `i_envios`
--
ALTER TABLE `i_envios`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `i_envios`
--
ALTER TABLE `i_envios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
