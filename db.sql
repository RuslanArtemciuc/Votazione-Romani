-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 12, 2025 at 09:40 AM
-- Server version: 8.0.32
-- PHP Version: 8.0.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `my_votazioneromani`
--
CREATE DATABASE IF NOT EXISTS `my_votazioneromani` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `my_votazioneromani`;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int NOT NULL,
  `username` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `password` char(60) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `indirizzo`
--

CREATE TABLE `indirizzo` (
  `id` int NOT NULL,
  `nome` varchar(40) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `opzioni`
--

CREATE TABLE `opzioni` (
  `id` int NOT NULL,
  `titolo` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `descrizione` varchar(300) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `durata` int NOT NULL DEFAULT '1',
  `posti` int NOT NULL DEFAULT '30',
  `fk_sondaggio` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `scelte`
--

CREATE TABLE `scelte` (
  `fk_studente` varchar(320) COLLATE utf8mb4_general_ci NOT NULL,
  `fk_opzione` int NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sondaggi`
--

CREATE TABLE `sondaggi` (
  `id` int NOT NULL,
  `titolo` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `descrizione` varchar(300) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `data` date DEFAULT NULL,
  `turni` int NOT NULL,
  `durata_turno` int NOT NULL,
  `min_voti` int NOT NULL,
  `max_voti` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `studenti`
--

CREATE TABLE `studenti` (
  `email` varchar(320) COLLATE utf8mb4_general_ci NOT NULL,
  `nome` varchar(40) COLLATE utf8mb4_general_ci NOT NULL,
  `cognome` varchar(40) COLLATE utf8mb4_general_ci NOT NULL,
  `classe` int NOT NULL,
  `sezione` char(1) COLLATE utf8mb4_general_ci NOT NULL,
  `fk_indirizzo` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `indirizzo`
--
ALTER TABLE `indirizzo`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `opzioni`
--
ALTER TABLE `opzioni`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sondaggio` (`fk_sondaggio`);

--
-- Indexes for table `scelte`
--
ALTER TABLE `scelte`
  ADD PRIMARY KEY (`fk_studente`,`fk_opzione`),
  ADD KEY `fk_opzione` (`fk_opzione`);

--
-- Indexes for table `sondaggi`
--
ALTER TABLE `sondaggi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `studenti`
--
ALTER TABLE `studenti`
  ADD PRIMARY KEY (`email`),
  ADD KEY `fk_indirizzo` (`fk_indirizzo`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `indirizzo`
--
ALTER TABLE `indirizzo`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `opzioni`
--
ALTER TABLE `opzioni`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sondaggi`
--
ALTER TABLE `sondaggi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `opzioni`
--
ALTER TABLE `opzioni`
  ADD CONSTRAINT `opzioni_ibfk_1` FOREIGN KEY (`fk_sondaggio`) REFERENCES `sondaggi` (`id`);

--
-- Constraints for table `scelte`
--
ALTER TABLE `scelte`
  ADD CONSTRAINT `scelte_ibfk_1` FOREIGN KEY (`fk_studente`) REFERENCES `studenti` (`email`),
  ADD CONSTRAINT `scelte_ibfk_2` FOREIGN KEY (`fk_opzione`) REFERENCES `opzioni` (`id`);

--
-- Constraints for table `studenti`
--
ALTER TABLE `studenti`
  ADD CONSTRAINT `fk_indirizzo` FOREIGN KEY (`fk_indirizzo`) REFERENCES `indirizzo` (`id`);
COMMIT;
