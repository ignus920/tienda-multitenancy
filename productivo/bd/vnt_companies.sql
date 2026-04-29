-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: mysql:3306
-- Tiempo de generación: 30-01-2026 a las 14:49:54
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
  `type` enum('USUARIO','CLIENTE','PROVEEDOR') NOT NULL DEFAULT 'CLIENTE',
  `typePerson` varchar(255) DEFAULT NULL,
  `typeIdentificationId` int NOT NULL,
  `regimeId` int DEFAULT NULL,
  `code_ciiu` varchar(255) DEFAULT NULL,
  `fiscalResponsabilityId` int DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `vnt_companies`
--

INSERT INTO `vnt_companies` (`id`, `businessName`, `billingEmail`, `firstName`, `integrationDataId`, `identification`, `checkDigit`, `lastName`, `secondLastName`, `secondName`, `status`, `type`, `typePerson`, `typeIdentificationId`, `regimeId`, `code_ciiu`, `fiscalResponsabilityId`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '', 'yesipoveda@gmail.com', 'Yesi', NULL, '1018469203', NULL, 'Alexander', 'Yara', 'Poveda', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '0', 1, '2025-11-12 16:21:49', '2025-11-13 16:33:03', NULL),
(2, '', 'prueba@gmail.com', 'Arturo', NULL, '12454654', NULL, 'Fernando', 'Casas', 'Bernal', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '0', 1, '2025-11-12 17:12:13', '2026-01-15 19:13:32', NULL),
(3, '', 'alejandrabarbosa2003@gmail.com', 'Maria ', NULL, '52031452', NULL, 'Alejandra', 'Marulanda', 'Barbosa ', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '0', 1, '2025-11-12 20:43:22', '2025-11-12 20:45:08', '2025-11-12 20:45:08'),
(4, '', 'Miercoles@gmail.com', 'Ana', NULL, '245465', NULL, 'Carolina', 'Miercoles', 'Diaz', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '0', 1, '2025-11-12 21:31:33', '2026-01-15 19:15:09', NULL),
(5, 'Ticsia', 'ticisa@ticsia.com', 'Ticsia', NULL, '5454645323', 4, NULL, NULL, NULL, 1, 'CLIENTE', 'LEGAL_ENTITY', 2, 3, '45512', 4, '2025-11-12 21:39:12', '2025-11-13 14:33:07', NULL),
(6, '', 'juan@gmail.com', 'Juan', NULL, '397006027', NULL, 'Manuel', 'Quintana', 'Montoya', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '0', 1, '2025-11-13 14:29:23', '2025-11-13 14:29:23', NULL),
(7, 'Bookbet', 'bookbet@bookbet.com', 'Bookbet', NULL, '555548', 4, NULL, NULL, NULL, 1, 'CLIENTE', 'LEGAL_ENTITY', 2, 3, '4', 4, '2025-11-13 15:55:42', '2025-11-13 15:55:42', NULL),
(8, 'Bictia', 'bictia@bictia.com', 'Bictia', NULL, '9655425', 4, NULL, NULL, NULL, 1, 'CLIENTE', 'LEGAL_ENTITY', 2, 3, '45', 5, '2025-11-13 16:07:21', '2025-11-13 16:07:21', NULL),
(9, 'Empresa', 'aa@gmail.com', 'Empresa', NULL, '99999999', 5, NULL, NULL, NULL, 1, 'CLIENTE', 'LEGAL_ENTITY', 2, 1, '45', 3, '2025-11-13 16:15:53', '2025-11-13 17:31:11', NULL),
(10, '', 'sucursal@gmail.com', 'Prueba', NULL, '5556647', NULL, 'Sucursal', 'Sucursal', 'Sucursal ', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '0', 1, '2025-11-13 17:05:28', '2025-11-13 17:05:28', NULL),
(11, '', 'belenEstFenandez@df.com', 'Belen', NULL, '66664', NULL, '', 'Fernandez', 'Estrada', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '0', 1, '2025-11-19 16:07:48', '2026-01-15 19:18:07', NULL),
(12, '', 'dsad@df.com', 'Laura', NULL, '78979874', NULL, 'Liliana', 'García', 'luke', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '0', 1, '2025-11-21 20:09:22', '2025-11-21 20:09:22', NULL),
(13, '', 'aroca@gmail.com', 'Alexis', NULL, '35555', NULL, 'Lazaro', 'Bermudes', 'Aroca', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '0', 1, '2025-11-24 16:51:31', '2025-11-24 16:51:31', NULL),
(15, 'desarrollo7', 'desarrollo7@gmail.com', 'desarrollo7', NULL, '20164547', NULL, '', '', 'da', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '0', 1, '2025-11-24 17:23:32', '2025-11-24 17:50:37', NULL),
(19, '', 'p@gmail.com', 'Edwin gre', NULL, '80833673', NULL, 'gregorio', 'CONTRERAS', 'PRIETO', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '0', 1, '2025-11-24 19:06:06', '2025-12-02 13:48:00', NULL),
(20, '', 'ma@gmail.com', 'ma', NULL, '51562789', NULL, 'ma', 'ma', 'ma', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '0', 1, '2025-11-24 19:09:22', '2025-11-24 19:09:22', NULL),
(21, '', 'df@gmail.com', 'df', NULL, '12121212', NULL, 'df', 'df', 'df', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '0', 1, '2025-11-24 19:13:17', '2025-11-24 19:13:17', NULL),
(22, '', 'nueva@gmail.com', 'Prueba', NULL, '568887', NULL, 'Prueba', 'Prueba', 'Prueba', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '0', 1, '2025-11-25 13:54:53', '2025-11-25 13:54:53', NULL),
(23, '', 'jily@mailinator.com', 'Alexa', NULL, '9996325', NULL, 'Craig', 'Morgan', 'Burton', 1, 'CLIENTE', 'PERSON_ENTITY', 6, 2, '0', 1, '2025-11-25 16:13:05', '2025-12-01 21:17:52', NULL),
(24, '', 'prieto@gmail.com', 'edwin', NULL, '3123842021', NULL, 'gregorio', 'contreras', 'prieto', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '0', 1, '2025-12-05 16:03:40', '2025-12-05 16:03:40', NULL),
(25, '', 'maria@gmail.com', 'maria', NULL, '7984562', NULL, 'carmen', 'suarez', 'julio', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '0', 1, '2025-12-05 17:47:23', '2025-12-05 17:47:23', NULL),
(26, '', 'marimar@gmail.com', 'marimar', NULL, '97862232', NULL, 'marimar', 'marimar', 'marimar', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '0', 1, '2025-12-05 17:50:09', '2025-12-05 17:50:09', NULL),
(27, '', 'pedro@gmail.com', 'pedro', NULL, '34324234234324', NULL, 'pedro', 'pedro', 'pedro', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '0', 1, '2025-12-05 17:52:59', '2025-12-05 17:52:59', NULL),
(28, '', 'garcia@gmail.com', 'Antonio', NULL, '2023457', NULL, 'Carlos', 'garcia', 'duvan', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '0', 1, '2025-12-09 16:10:37', '2025-12-09 16:10:37', NULL),
(29, '', 'mejekidel@mailinator.com', 'Pepe', NULL, '66994442', NULL, 'Camacho', 'Morgan', 'Davidson', 1, 'CLIENTE', 'PERSON_ENTITY', 4, 2, '0', 1, '2025-12-09 16:19:39', '2025-12-09 16:19:39', NULL),
(30, '', 'pyjucebosi@mailinator.com', 'Harding', NULL, '1014865455', NULL, 'Francis', 'Mack', 'Cruz', 1, 'CLIENTE', 'PERSON_ENTITY', 6, 2, '0', 1, '2025-12-09 16:23:16', '2025-12-09 16:23:16', NULL),
(31, '', '645645@gmail.com', '5464564', NULL, '34534534534534', NULL, '56546456', '564564', '564564', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '0', 1, '2025-12-09 16:27:32', '2025-12-09 16:27:32', NULL),
(32, '', 'defop@mailinator.com', 'Gretchen', NULL, 'Nisi beatae ani', NULL, 'Jenkins', 'Wade', 'Henry', 1, 'CLIENTE', 'PERSON_ENTITY', 5, 2, '0', 1, '2025-12-09 16:34:14', '2025-12-09 16:34:14', NULL),
(33, '', 'dyvijuw@mailinator.com', 'Kessie', NULL, '787587', NULL, 'Gibbs', 'Bray', 'Acosta', 1, 'CLIENTE', 'PERSON_ENTITY', 6, 2, '0', 1, '2025-12-10 17:08:56', '2025-12-10 17:08:56', NULL),
(34, '', 'bosetytu@mailinator.com', 'Darrel', NULL, '555678845', NULL, 'Rodriquez', 'Atkins', 'Kidd', 1, 'CLIENTE', 'PERSON_ENTITY', 4, 2, '0', 1, '2025-12-10 17:19:28', '2025-12-10 17:19:28', NULL),
(35, '', 'bebyvekar@mailinator.com', 'Luke', NULL, '789545233', NULL, 'Kidd', 'Ochoa', 'Lynch', 1, 'CLIENTE', 'PERSON_ENTITY', 3, 2, '0', 1, '2025-12-10 17:26:03', '2025-12-10 17:26:03', NULL),
(36, '', 'jejip@mailinator.com', 'Tarik', NULL, '78885654', NULL, 'Waters', 'Crane', 'Howe', 1, 'CLIENTE', 'PERSON_ENTITY', 4, 2, '0', 1, '2025-12-10 17:28:13', '2025-12-10 17:28:13', NULL),
(37, '', 'xovajumexu@mailinator.com', 'Stephanie', NULL, '8554666666', NULL, 'Mclaughlin', 'Green', 'Clay', 1, 'CLIENTE', 'PERSON_ENTITY', 3, 2, '0', 1, '2025-12-10 17:41:28', '2025-12-10 17:41:28', NULL),
(38, '', 'kyvibujyb@mailinator.com', 'Wang', NULL, '4578899', NULL, 'Stone', 'Newman', 'Barlow', 1, 'CLIENTE', 'PERSON_ENTITY', 3, 2, '0', 1, '2025-12-10 17:45:12', '2025-12-10 17:45:12', NULL),
(39, '', 'fynys@mailinator.com', 'Ramona', NULL, '45788997777', NULL, 'Rodriquez', 'Herman', 'Boyd', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '0', 1, '2025-12-10 17:48:46', '2025-12-10 17:48:46', NULL),
(40, '', 'jone@mailinator.com', 'Hanae', NULL, '4449999', NULL, 'Cobb', 'Vincent', 'Short', 1, 'CLIENTE', 'PERSON_ENTITY', 5, 2, '0', 1, '2025-12-10 17:54:08', '2025-12-10 17:54:08', NULL),
(41, '', 'vasojosa@mailinator.com', 'Camilo', NULL, '3333335', NULL, 'Wynn', 'King', 'Torres', 0, 'CLIENTE', 'PERSON_ENTITY', 6, 2, '0', 1, '2025-12-10 21:31:43', '2026-01-09 17:08:08', NULL),
(42, '', 'dedyj@mailinator.com', 'Warren', NULL, '44457554', NULL, 'Obrien', 'Richmond', 'Hart', 0, 'CLIENTE', 'PERSON_ENTITY', 3, 2, '0', 1, '2025-12-11 13:59:03', '2026-01-09 17:08:11', NULL),
(43, '', 'orjuela@a.com', 'Alvaro', NULL, '55647789', NULL, 'jacinto', 'perez', 'orjuela', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '0', 1, '2025-12-11 14:56:26', '2025-12-11 14:56:26', NULL),
(44, '', 'qerexu@mailinator.com', 'Ruth', NULL, '77774568', NULL, 'Clemons', 'Alvarez', 'Vazquez', 1, 'CLIENTE', 'PERSON_ENTITY', 4, 2, '0', 1, '2025-12-11 15:03:33', '2025-12-11 15:03:33', NULL),
(45, '', 'ana@mailinator.com', 'Ana', NULL, '4787565465', NULL, 'Camila', 'Alvarez', 'Garcia', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '0', 1, '2025-12-11 15:12:31', '2025-12-11 15:12:31', NULL),
(46, '', '45@gmail.com', '45', NULL, '45', NULL, '45', '45', '45', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '0', 1, '2025-12-11 17:40:49', '2025-12-11 17:40:49', NULL),
(47, '', 'asd@gmail.com', 'as', NULL, '2323', NULL, 'sd', 'asd', 'sad', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '0', 1, '2025-12-11 17:50:08', '2025-12-11 17:50:08', NULL),
(48, '', '5@gmail.com', '5', NULL, '5', NULL, '5', '5', '5', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '0', 1, '2025-12-11 17:51:00', '2025-12-11 17:51:00', NULL),
(49, '', '8@gmail.com', '8', NULL, '8', NULL, '8', '8', '8', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '0', 1, '2025-12-11 17:57:36', '2025-12-11 17:57:36', NULL),
(50, '', 'martin@gmail.com', 'martin', NULL, '6786787687687', NULL, 'martin', 'martin', 'martin', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '0', 1, '2025-12-11 19:17:15', '2026-01-09 17:48:09', NULL),
(51, '', 'pruebasDos@gmail.com', 'German\n', NULL, '565656', NULL, 'Mauricio', 'garzon', 'suarez', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '0', 1, '2025-12-11 19:23:17', '2025-12-11 19:23:17', NULL),
(52, '', 'kamof@mailinator.com', 'Quin', NULL, '11111114', NULL, 'Klein', 'Mcintosh', 'Sharp', 1, 'CLIENTE', 'PERSON_ENTITY', 3, 2, '0', 1, '2025-12-15 20:31:48', '2025-12-15 20:31:48', NULL),
(53, '', 'olga@gmail.com', 'Olga', NULL, '101544477', NULL, 'Dina', 'Alvarez', 'Tino', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '0', 1, '2025-12-15 20:43:59', '2025-12-15 20:43:59', NULL),
(54, '', 'alberto@gmail.com', 'Alberto', NULL, '6666987', NULL, 'Dinosario', 'Aguilera', 'Garzon', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '0', 1, '2025-12-16 13:16:54', '2025-12-16 13:16:54', NULL),
(55, '', 'dairo@gmail.com', 'Dairo', NULL, '9999999123', NULL, 'Agelo', 'Acosta', 'Moreno', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '5711', 1, '2025-12-16 14:03:14', '2025-12-16 14:03:14', NULL),
(56, '', 'fyjamefu@mailinator.com', 'Karleigh', NULL, '4447586', NULL, 'Erickson', 'Kirkland', 'Golden', 1, 'CLIENTE', 'PERSON_ENTITY', 3, 2, '4711', 1, '2025-12-16 14:52:00', '2025-12-16 14:52:00', NULL),
(57, '', 'barros@gmail.com', 'Barros', NULL, '88888552', NULL, 'Maul', 'Detergen', 'Sogamoso', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '4711', 1, '2025-12-16 16:09:20', '2025-12-16 16:09:20', NULL),
(58, '', 'pruebasTAT@gmail.com', 'pruebas TAT', NULL, '87877884848', NULL, 'pruebas TAT', 'pruebas TAT', 'pruebas TAT', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '', 1, '2025-12-16 21:00:40', '2025-12-16 21:00:40', NULL),
(59, '', 'juseloco@gmail.com', 'Juan', NULL, '1018507004', NULL, 'Sebastian', 'Contreras', 'Lozano', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '0', 1, '2025-12-19 14:47:14', '2025-12-19 14:47:14', NULL),
(60, 'muebleschitiva', 'chitiva@chitiva.com', 'muebleschitiva', NULL, '444788963', 1, NULL, NULL, NULL, 1, 'CLIENTE', 'LEGAL_ENTITY', 2, 4, '4711', 1, '2025-12-19 15:11:48', '2025-12-19 15:11:48', NULL),
(61, '', 'samuel@gmail.com', 'samuel', NULL, '1022440047', NULL, '', 'Bolivar', 'Lozano', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '1230', 1, '2025-12-19 16:07:34', '2025-12-19 16:07:34', NULL),
(62, 'miles', 'penepa@mailinator.com', 'miles', NULL, '88962546', 0, NULL, NULL, NULL, 1, 'CLIENTE', 'LEGAL_ENTITY', 2, 6, '4711', 1, '2025-12-22 12:22:46', '2025-12-22 12:22:46', NULL),
(63, '', 'pybu@mailinator.com', 'Hyatt', NULL, '7777854', NULL, 'Vargas', 'Hines', 'Carver', 1, 'CLIENTE', 'PERSON_ENTITY', 4, 2, '7411', 1, '2025-12-22 17:36:37', '2025-12-22 17:36:37', NULL),
(64, 'imanesnarvaes', 'imanesnarvaes@gmail.com', 'imanesnarvaes', NULL, '9998745', 0, NULL, NULL, NULL, 1, 'CLIENTE', 'LEGAL_ENTITY', 2, 3, '4711', 1, '2025-12-22 17:45:31', '2025-12-22 17:45:31', NULL),
(65, 'eeeeee', 'visiguvy@mailinator.com', 'eeeeee', NULL, '4747555', 5, NULL, NULL, NULL, 1, 'CLIENTE', 'LEGAL_ENTITY', 2, 6, '4711', 2, '2025-12-22 17:52:44', '2025-12-22 17:52:44', NULL),
(66, '', 'dddd@mailinator.com', 'ddd', NULL, '666654798', NULL, 'ddd', 'ddd', 'ddd', 1, 'CLIENTE', 'PERSON_ENTITY', 3, 2, '4711', 1, '2025-12-22 17:53:57', '2025-12-22 17:53:57', NULL),
(67, '', 'kk@gmail.com', 'kk', NULL, '85669344', NULL, 'kk', 'kk', 'kk', 1, 'CLIENTE', 'PERSON_ENTITY', 3, 2, '4711', 1, '2025-12-22 17:59:22', '2025-12-22 17:59:22', NULL),
(68, '', 'qq@gmail.com', 'qq', NULL, '7775456', NULL, 'qq', 'qq', 'qq', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '4711', 1, '2025-12-22 18:01:40', '2025-12-22 18:01:40', NULL),
(69, 'pruebasdosis', 'pruebasdosis@gmail.com', 'pruebasdosis', NULL, '44457888999992', 4, NULL, NULL, NULL, 1, 'CLIENTE', 'LEGAL_ENTITY', 2, 4, '4711', 1, '2025-12-22 18:04:24', '2025-12-22 18:04:24', NULL),
(70, 'lala', 'lala@gmail.com', 'lala', NULL, '55547861', 0, NULL, NULL, NULL, 1, 'CLIENTE', 'LEGAL_ENTITY', 2, 4, '4711', 1, '2025-12-22 18:07:18', '2025-12-22 18:07:18', NULL),
(71, '', 'ereer@gmail.com', 'prer', NULL, '51565848', NULL, 'ererer', 'erer', 'er', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '', 1, '2025-12-23 12:28:52', '2025-12-23 12:28:52', NULL),
(72, '', 'tt@gmail.com', 'tt', NULL, '777845', NULL, 'tt', 'tt', 'tt', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '4711', 1, '2025-12-23 12:59:02', '2025-12-23 12:59:02', NULL),
(73, '', 'zz@gmail.com', 'zz', NULL, '999784', NULL, 'zz', 'zz', 'zz', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '4711', 1, '2025-12-23 13:01:45', '2025-12-23 13:01:45', NULL),
(74, '', 'prrr@gmail.com', 'prrrr', NULL, '51564515', NULL, 'prrr', 'prrr', 'prrr', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '', 1, '2025-12-23 13:33:32', '2025-12-23 13:33:32', NULL),
(75, '', 'sdfsdf@gmail.com', 'reererfsdf', NULL, '345345345453453', NULL, 'ffsdfsd', 'sdfsdf', 'fsdfsdf', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '47111', 1, '2025-12-23 14:38:31', '2025-12-23 14:38:31', NULL),
(76, '', 'pruenasdos@gmail.com', 'pruenas dos', NULL, '51554551615', NULL, 'pruenas dos', 'pruenas dos', 'pruenas dos', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '', 1, '2025-12-23 16:13:30', '2025-12-23 16:13:30', NULL),
(77, '', 'fghfghfg@gmail.com', 'fghfgh', NULL, '67867867867867', NULL, 'fghfgh', 'hfgh', 'fghfg', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '', 1, '2025-12-23 16:17:41', '2025-12-23 16:17:41', NULL),
(78, '', 'dfgdfgdf@gmail.com', 'dfgdfgdfg', NULL, '676788757456456', NULL, 'dfgdfg', 'dfgdfgdf', 'dfgdfg', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '', 1, '2025-12-23 16:24:40', '2025-12-23 16:24:40', NULL),
(79, '', 'ramiro@gmail.com', 'Fernando', NULL, '678756756765785', NULL, 'Ramiro', 'Ocaña', 'Paredes', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '', 1, '2025-12-23 16:33:37', '2026-01-08 21:02:38', NULL),
(80, '', 'GONZALES@GMAIL.COM', 'GONZALES', NULL, '111111111', NULL, 'GONZALES', 'GONZALES', 'GONZALES', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '4711', 1, '2025-12-23 17:32:02', '2025-12-23 17:32:02', NULL),
(81, '', 'par@gmail.com', 'par', NULL, '77789456', NULL, 'par', 'par', 'par', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '4711', 1, '2025-12-26 21:35:48', '2025-12-26 21:35:48', NULL),
(82, '', 'marisol@gmail.com', 'marisol', NULL, '78664421', NULL, 'marisol', 'marisol', 'marisol', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '4711', 1, '2025-12-26 21:49:28', '2026-01-05 16:20:05', NULL),
(83, '', 'nataly@gmail.com', 'Nataly', NULL, '123123', NULL, 'Antonia', 'Intenso', 'Perreo', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '4711', 1, '2026-01-06 14:47:13', '2026-01-06 14:47:13', NULL),
(84, '', 'ingrid@gmail.com', 'Ingrid', NULL, '456456', NULL, 'Xiomara', 'Urrea', 'Castillo', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '4711', 1, '2026-01-06 14:57:10', '2026-01-06 14:57:10', NULL),
(85, '', 'antoni@gmail.com', 'Antoni', NULL, '789789', NULL, 'natal', 'Perez', 'Garzon', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '4711', 1, '2026-01-06 15:09:27', '2026-01-06 15:09:27', NULL),
(86, '', 'xadetiw@mailinator.com', 'Marshall', NULL, '889944', NULL, 'Pearson', 'Beck', 'Nieves', 1, 'CLIENTE', 'PERSON_ENTITY', 5, 2, '4711', 1, '2026-01-06 15:12:48', '2026-01-06 15:12:48', NULL),
(87, '', 'norman@gmail.com', 'Norman', NULL, '4475869', NULL, 'Alejandro', 'Montero', 'Venites', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '4711', 1, '2026-01-06 16:14:47', '2026-01-06 16:14:47', NULL),
(88, '', 'omar@gmail.com', 'Omar', NULL, '741741', NULL, 'Bejarano', 'Benavides', 'Trujilles', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '4711', 1, '2026-01-06 16:38:14', '2026-01-09 17:07:55', NULL),
(89, '', 'reinaldo@gmail.com', 'Reinaldo', NULL, '159236', NULL, 'Anso', 'Martines', 'Rueda', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '4711', 1, '2026-01-08 13:34:47', '2026-01-08 13:34:47', NULL),
(90, '', 'KKK@GMAIL.COM', 'KKK', NULL, '565656565', NULL, 'KKK', 'KKK', 'KKK', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '2530', 1, '2026-01-08 21:19:33', '2026-01-08 21:19:33', NULL),
(91, '', 'qqq@gmail.com', 'QQQ', NULL, '1025678354', NULL, 'QQQ', 'QQQ', 'QQQ', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '2530', 1, '2026-01-09 14:16:55', '2026-01-09 14:16:55', NULL),
(92, '', 'rr@gmail.com', 'rrr', NULL, '11235689', NULL, 'rrrr', 'rrrr', 'rrrr', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '2530', 1, '2026-01-09 16:27:03', '2026-01-09 16:27:03', NULL),
(93, '', 'ttt@gmail.com', 'ttt', NULL, '10892017104', NULL, 'ttt', 'ttt', 'ttt', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '2530', 1, '2026-01-09 16:28:08', '2026-01-09 16:28:08', NULL),
(94, '', 'yyy@gmail.com', 'yyy', NULL, '1021231508', NULL, 'yyy', 'yyy', 'yyy', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '2530', 1, '2026-01-09 16:43:43', '2026-01-09 16:43:43', NULL),
(95, '', 'uuu@gmail.com', 'uuu', NULL, '1022901452', NULL, 'uuu', 'uu', 'uu', 1, 'USUARIO', 'PERSON_ENTITY', 1, 2, '2530', 1, '2026-01-09 16:52:43', '2026-01-09 16:52:43', NULL),
(96, '', 'ppgpp@gmail.com', 'aaaa', NULL, '1023456789', NULL, 'aaaa', 'aaaa', 'aaaa', 1, 'USUARIO', 'PERSON_ENTITY', 1, 2, '2530', 1, '2026-01-09 19:08:13', '2026-01-09 19:08:13', NULL),
(97, 'PRUEBA PROVEEDOR', 'pruebaProveedor@gmail.com', 'PRUEBA PROVEEDOR', NULL, '900562120', 9, NULL, NULL, NULL, 1, 'PROVEEDOR', 'LEGAL_ENTITY', 2, 2, '2530', 2, '2026-01-09 19:12:16', '2026-01-09 19:12:16', NULL),
(98, '', 'luisFernandoM@gmail.com', 'Luis', NULL, '1031185525', NULL, 'Fernando', '', 'Montoya', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '2530', 1, '2026-01-16 17:21:17', '2026-01-16 17:21:17', NULL),
(99, 'MATERIALES FERRTERIA', 'materialesFerreteria@gmail.com', 'MATERIALES FERRTERIA', NULL, '900658210', 6, NULL, NULL, NULL, 1, 'PROVEEDOR', 'LEGAL_ENTITY', 2, 4, '9631', 5, '2026-01-16 18:57:27', '2026-01-16 18:57:27', NULL),
(100, '', 'pedro45345345@gmail.com', 'pedrosdf', NULL, '5675675675', NULL, 'pedro', 'pedro', 'pedro', 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, '4711', 1, '2026-01-29 14:05:19', '2026-01-29 14:05:19', NULL),
(101, 'Wilfrid', 'wilfrido@gmail.com', 'Wilfrid', NULL, '9267368287383', NULL, NULL, NULL, NULL, 1, 'CLIENTE', 'PERSON_ENTITY', 1, 2, NULL, 1, '2026-01-30 14:42:42', '2026-01-30 14:42:42', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `vnt_companies`
--
ALTER TABLE `vnt_companies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `identification` (`identification`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `vnt_companies`
--
ALTER TABLE `vnt_companies`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
