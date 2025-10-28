-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 28-10-2025 a las 17:32:55
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
-- Estructura de tabla para la tabla `i_picking`
--

CREATE TABLE `i_picking` (
  `id` int(11) NOT NULL,
  `numero_packing` varchar(50) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `idenvio` int(11) NOT NULL,
  `observaciones` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `i_picking`
--

INSERT INTO `i_picking` (`id`, `numero_packing`, `fecha_creacion`, `idenvio`, `observaciones`) VALUES
(211, 'PACK001', '2025-09-11 08:20:14', 61, ''),
(212, 'PACK002', '2025-09-11 08:20:14', 0, ''),
(213, 'PACK003', '2025-09-11 08:20:14', 0, ''),
(214, 'PACK004', '2025-09-11 16:30:58', 0, ''),
(215, 'PACK005', '2025-09-11 16:40:52', 0, '');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `i_picking`
--
ALTER TABLE `i_picking`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `i_picking`
--
ALTER TABLE `i_picking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=216;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
