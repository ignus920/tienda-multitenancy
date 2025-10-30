-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: mysql:3306
-- Tiempo de generación: 28-10-2025 a las 22:09:17
-- Versión del servidor: 8.0.43
-- Versión de PHP: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `rap`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vnt_companies`
--

CREATE TABLE `vnt_companies` (
  `id` int NOT NULL,
  `businessName` varchar(255) DEFAULT NULL,
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
  `typeIdentificationId` int DEFAULT NULL,
  `regimeId` int DEFAULT NULL,
  `code_ciiu` varchar(255) DEFAULT NULL,
  `fiscalResponsabilityId` int DEFAULT NULL,
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime DEFAULT NULL,
  `deletedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `vnt_companies`
--
ALTER TABLE `vnt_companies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `identification` (`identification`),
  ADD KEY `typeIdentificationId` (`typeIdentificationId`),
  ADD KEY `regimeId` (`regimeId`),
  ADD KEY `fiscalResponsabilityId` (`fiscalResponsabilityId`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `vnt_companies`
--
ALTER TABLE `vnt_companies`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `vnt_companies`
--
ALTER TABLE `vnt_companies`
  ADD CONSTRAINT `vnt_companies_ibfk_1` FOREIGN KEY (`typeIdentificationId`) REFERENCES `cnf_type_identifications` (`id`),
  ADD CONSTRAINT `vnt_companies_ibfk_2` FOREIGN KEY (`regimeId`) REFERENCES `cnf_regime` (`id`),
  ADD CONSTRAINT `vnt_companies_ibfk_3` FOREIGN KEY (`fiscalResponsabilityId`) REFERENCES `cnf_fiscal_responsabilities` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
