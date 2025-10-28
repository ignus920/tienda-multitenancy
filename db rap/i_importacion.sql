-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 28-10-2025 a las 16:44:32
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
-- Estructura de tabla para la tabla `i_importacion`
--

CREATE TABLE `i_importacion` (
  `id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `idproducto` varchar(255) NOT NULL,
  `login` varchar(100) NOT NULL,
  `id_etiqueta` int(11) NOT NULL,
  `cant_sol` int(11) NOT NULL,
  `cant_enviada` int(11) NOT NULL,
  `precio` decimal(11,3) DEFAULT NULL,
  `eta` date NOT NULL,
  `fecha_recibido` date NOT NULL,
  `estado` int(11) NOT NULL,
  `fecha_reg` datetime NOT NULL DEFAULT current_timestamp(),
  `comentario` text NOT NULL,
  `id_picking` int(11) DEFAULT NULL,
  `cant_recibida` int(11) DEFAULT 0,
  `comentario_recibido` text DEFAULT NULL,
  `novedades` tinyint(1) DEFAULT 0 COMMENT 'Indica si tiene novedades: 1=Sí, 0=No'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `i_importacion`
--

INSERT INTO `i_importacion` (`id`, `fecha`, `idproducto`, `login`, `id_etiqueta`, `cant_sol`, `cant_enviada`, `precio`, `eta`, `fecha_recibido`, `estado`, `fecha_reg`, `comentario`, `id_picking`, `cant_recibida`, `comentario_recibido`, `novedades`) VALUES
(541, '2025-10-20', '80833673', 'ticsia', 55, 12, 12, 10.000, '0000-00-00', '0000-00-00', 7, '2025-10-20 14:41:15', '', 211, 0, NULL, 0),
(542, '2025-10-20', '4010056', 'ticsia', 55, 12, 0, 10.000, '0000-00-00', '0000-00-00', 5, '2025-10-20 14:41:15', '', NULL, 0, NULL, 0),
(543, '2025-10-20', '4010057', 'ticsia', 55, 12, 0, 10.000, '0000-00-00', '0000-00-00', 5, '2025-10-20 14:41:15', '', NULL, 0, NULL, 0),
(544, '2025-10-20', '4010058', 'ticsia', 55, 12, 12, 10.000, '0000-00-00', '0000-00-00', 7, '2025-10-20 14:41:15', '', 211, 0, NULL, 0),
(545, '2025-10-20', '7800284', 'ticsia', 55, 12, 12, 10.000, '0000-00-00', '0000-00-00', 7, '2025-10-20 14:41:15', '', 211, 0, NULL, 0),
(546, '2025-10-22', '7010248', 'ticsia', 56, 12, 0, 0.170, '0000-00-00', '0000-00-00', 2, '2025-10-22 09:30:12', '', NULL, 0, NULL, 0),
(547, '2025-10-22', '7010170', 'ticsia', 56, 21, 0, 0.170, '0000-00-00', '0000-00-00', 2, '2025-10-22 09:30:12', '', NULL, 0, NULL, 0),
(548, '2025-10-22', '7010153', 'ticsia', 56, 21, 0, 0.165, '0000-00-00', '0000-00-00', 2, '2025-10-22 09:30:12', '', NULL, 0, NULL, 0),
(549, '2025-10-22', '5010069', 'ticsia', 56, 32, 0, 0.000, '0000-00-00', '0000-00-00', 1, '2025-10-22 09:30:12', '', NULL, 0, NULL, 0),
(550, '2025-10-22', '5010222', 'ticsia', 56, 20, 0, 0.000, '0000-00-00', '0000-00-00', 1, '2025-10-22 09:30:12', '', NULL, 0, NULL, 0),
(551, '2025-10-22', '5010194', 'ticsia', 56, 12, 0, 0.000, '0000-00-00', '0000-00-00', 1, '2025-10-22 09:30:12', '', NULL, 0, NULL, 0),
(552, '2025-10-22', '5010065', 'ticsia', 56, 12, 0, 0.000, '0000-00-00', '0000-00-00', 1, '2025-10-22 09:30:12', '', NULL, 0, NULL, 0),
(553, '2025-10-22', '5010180', 'ticsia', 56, 2, 0, 0.000, '0000-00-00', '0000-00-00', 1, '2025-10-22 09:30:12', '', NULL, 0, NULL, 0),
(554, '2025-10-22', '6010024', 'ticsia', 56, 12, 0, 0.000, '0000-00-00', '0000-00-00', 1, '2025-10-22 09:30:12', '', NULL, 0, NULL, 0),
(555, '2025-10-22', '6010002', 'ticsia', 56, 2, 100, 0.000, '0000-00-00', '0000-00-00', 7, '2025-10-22 09:30:12', '', NULL, 0, NULL, 0);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `i_importacion`
--
ALTER TABLE `i_importacion`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `i_importacion`
--
ALTER TABLE `i_importacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=556;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
