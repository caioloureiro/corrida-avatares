-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 24-Abr-2026 às 17:29
-- Versão do servidor: 8.0.31
-- versão do PHP: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `airbr_flow`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `corrida`
--

DROP TABLE IF EXISTS `corrida`;
CREATE TABLE IF NOT EXISTS `corrida` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ativo` int NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `seguidores` int DEFAULT NULL,
  `data` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Extraindo dados da tabela `corrida`
--

INSERT INTO `corrida` (`id`, `ativo`, `created_at`, `updated_at`, `nome`, `seguidores`, `data`) VALUES
(1, 1, '2026-04-24 14:21:51', '2026-04-24 14:21:51', 'Ana', 0, '2026-04-22 14:21:51'),
(2, 1, '2026-04-24 14:21:51', '2026-04-24 14:21:51', 'Megg', 0, '2026-04-22 14:21:51'),
(3, 1, '2026-04-24 14:21:51', '2026-04-24 14:21:51', 'Bia', 0, '2026-04-22 14:21:51'),
(4, 1, '2026-04-24 14:21:51', '2026-04-24 14:21:51', 'Luna', 0, '2026-04-22 14:21:51'),
(5, 1, '2026-04-24 14:21:51', '2026-04-24 14:21:51', 'Mel', 0, '2026-04-22 14:21:51'),
(6, 1, '2026-04-24 14:21:51', '2026-04-24 14:21:51', 'Ana', 105, '2026-04-23 23:21:51'),
(7, 1, '2026-04-24 14:21:51', '2026-04-24 14:21:51', 'Megg', 58, '2026-04-23 23:21:51'),
(8, 1, '2026-04-24 14:21:51', '2026-04-24 14:21:51', 'Bia', 86, '2026-04-23 23:21:51'),
(9, 1, '2026-04-24 14:21:51', '2026-04-24 14:21:51', 'Luna', 25, '2026-04-23 23:21:51'),
(10, 1, '2026-04-24 14:21:51', '2026-04-24 14:21:51', 'Mel', 61, '2026-04-23 23:21:51');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
