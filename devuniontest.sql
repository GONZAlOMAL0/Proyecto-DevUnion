-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 52.0.219.41
-- Generation Time: Jul 29, 2025 at 11:14 PM
-- Server version: 8.0.13
-- PHP Version: 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `devuniontest`
--

-- --------------------------------------------------------

--
-- Table structure for table `usuario`
--

CREATE TABLE `usuario` (
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `apellido` varchar(100) DEFAULT NULL,
  `tel` char(9) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `contrasenia` varchar(100) DEFAULT NULL,
  `cedula` char(8) DEFAULT NULL,
  `direccion` varchar(100) DEFAULT NULL,
  `id` int(11) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estado` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `usuario`
--

INSERT INTO `usuario` (`nombre`, `apellido`, `tel`, `correo`, `contrasenia`, `cedula`, `direccion`, `id`, `fecha_registro`, `estado`) VALUES
('Juan', 'Pérez', '091377223', 'juan.perez@example.com', 'password123', '56602435', NULL, 1, '2025-07-27 03:10:10', '1'),
('mauro', 'mauro', '091377223', 'mauro@gmail.com', '5m66A9u249R50', '56692495', NULL, 3, '2025-07-27 03:18:28', '1'),
('mauro', 'mauro', '091377223', 'mauro@gmail.com', '5m66A9u249R50', '56692493', NULL, 4, '2025-07-27 03:20:41', '1'),
('mauro', 'mauro', '091377223', 'mauro@gmail.com', '5m66A9u249R50', '56692491', NULL, 5, '2025-07-27 03:27:58', '1'),
('sadf', 'asdf', '092334556', 'mauro@gmail.com', '5m66A9u249R50', '56692346', NULL, 6, '2025-07-27 03:28:36', '1'),
('sadf', 'asdf', '092334556', 'mauro@gmail.com', '5m66A9u249R50', '56292346', NULL, 7, '2025-07-27 03:31:50', '1'),
('sadf', 'asdf', '092334556', 'mauro@gmail.com', '5m66A9u249R50', '56222346', NULL, 8, '2025-07-27 03:32:02', '1'),
('sadf', 'asdf', '092334556', 'mauro@gmail.com', '5m66A9u249R50', '51222346', NULL, 9, '2025-07-27 03:33:07', '1'),
('juan', 'manuel', '098223644', 'panda@gay.com', '5m66A9u249R50', '56694573', NULL, 10, '2025-07-29 00:44:15', '1'),
('maurico', 'vasques', '098223644', 'panda@gay.com', '64d12117c3bf5', '56694570', NULL, 11, '2025-07-29 00:49:02', '0'),
('maurico', 'vasques', '098223644', 'panda@gay.com', '5m66A9u249R50', '56694520', NULL, 12, '2025-07-29 00:55:10', '0'),
('maurico', 'vasques', '098223644', 'panda@gay.com', '65f5cca0cf09d', '56694543', NULL, 13, '2025-07-29 00:57:24', '1'),
('asd', 'asd', '09233445', 'mauro@gmail.com', '5m66A9u249R50', '58829345', NULL, 15, '2025-07-29 02:12:31', '0'),
('gonzalo', 'malo', '097352912', 'gonzamalo27@gmail.com', 'holahola123', '57130230', NULL, 17, '2025-07-29 02:17:38', '1');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
