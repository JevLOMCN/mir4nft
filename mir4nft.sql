-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 01, 2023 at 09:22 AM
-- Server version: 10.5.16-MariaDB
-- PHP Version: 8.2.13
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */
;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */
;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */
;
/*!40101 SET NAMES utf8mb4 */
;
--
-- Database: `mir4nft`
--

-- --------------------------------------------------------
--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `transportID` bigint(20) UNSIGNED NOT NULL,
  `json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`json`))
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- --------------------------------------------------------
--
-- Table structure for table `building`
--

CREATE TABLE `building` (
  `transportID` bigint(20) UNSIGNED NOT NULL,
  `json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`json`))
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- --------------------------------------------------------
--
-- Table structure for table `codex`
--

CREATE TABLE `codex` (
  `transportID` bigint(20) UNSIGNED NOT NULL,
  `json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`json`))
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- --------------------------------------------------------
--
-- Table structure for table `holystuff`
--

CREATE TABLE `holystuff` (
  `transportID` bigint(20) UNSIGNED NOT NULL,
  `json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`json`))
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- --------------------------------------------------------
--
-- Table structure for table `inven`
--

CREATE TABLE `inven` (
  `transportID` bigint(20) UNSIGNED NOT NULL,
  `json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`json`))
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- --------------------------------------------------------
--
-- Table structure for table `magicorb`
--

CREATE TABLE `magicorb` (
  `transportID` bigint(20) UNSIGNED NOT NULL,
  `json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`json`))
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- --------------------------------------------------------
--
-- Table structure for table `magicstone`
--

CREATE TABLE `magicstone` (
  `transportID` bigint(20) UNSIGNED NOT NULL,
  `json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`json`))
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- --------------------------------------------------------
--
-- Table structure for table `mysticalpiece`
--

CREATE TABLE `mysticalpiece` (
  `transportID` bigint(20) UNSIGNED NOT NULL,
  `json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`json`))
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- --------------------------------------------------------
--
-- Table structure for table `potential`
--

CREATE TABLE `potential` (
  `transportID` bigint(20) UNSIGNED NOT NULL,
  `json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`json`))
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- --------------------------------------------------------
--
-- Table structure for table `sequence`
--

CREATE TABLE `sequence` (
  `seq` bigint(20) UNSIGNED NOT NULL,
  `transportID` bigint(20) UNSIGNED NOT NULL,
  `price` decimal(10, 2) UNSIGNED NOT NULL,
  `MirageScore` int(11) UNSIGNED NOT NULL,
  `MiraX` bigint(20) UNSIGNED NOT NULL,
  `Reinforce` tinyint(4) UNSIGNED NOT NULL,
  `tradeType` tinyint(4) UNSIGNED NOT NULL DEFAULT 1
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- --------------------------------------------------------
--
-- Table structure for table `skills`
--

CREATE TABLE `skills` (
  `transportID` bigint(20) UNSIGNED NOT NULL,
  `json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`json`))
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- --------------------------------------------------------
--
-- Table structure for table `spirit`
--

CREATE TABLE `spirit` (
  `transportID` bigint(20) UNSIGNED NOT NULL,
  `json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`json`))
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- --------------------------------------------------------
--
-- Table structure for table `stats`
--

CREATE TABLE `stats` (
  `transportID` bigint(20) UNSIGNED NOT NULL,
  `json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`json`))
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- --------------------------------------------------------
--
-- Table structure for table `summary`
--

CREATE TABLE `summary` (
  `seq` bigint(20) UNSIGNED NOT NULL,
  `json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`json`))
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- --------------------------------------------------------
--
-- Table structure for table `training`
--

CREATE TABLE `training` (
  `transportID` bigint(20) UNSIGNED NOT NULL,
  `json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`json`))
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- --------------------------------------------------------
--
-- Table structure for table `transports`
--

CREATE TABLE `transports` (
  `transportID` bigint(20) UNSIGNED NOT NULL,
  `nftID` varchar(20) NOT NULL,
  `sealedDT` bigint(20) UNSIGNED NOT NULL,
  `characterName` varchar(255) NOT NULL,
  `class` tinyint(3) UNSIGNED NOT NULL,
  `lv` smallint(5) UNSIGNED NOT NULL,
  `powerScore` int(10) UNSIGNED NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
--
-- Indexes for dumped tables
--

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
ADD PRIMARY KEY (`transportID`);
--
-- Indexes for table `building`
--
ALTER TABLE `building`
ADD PRIMARY KEY (`transportID`);
--
-- Indexes for table `codex`
--
ALTER TABLE `codex`
ADD PRIMARY KEY (`transportID`);
--
-- Indexes for table `holystuff`
--
ALTER TABLE `holystuff`
ADD PRIMARY KEY (`transportID`);
--
-- Indexes for table `inven`
--
ALTER TABLE `inven`
ADD PRIMARY KEY (`transportID`);
--
-- Indexes for table `magicorb`
--
ALTER TABLE `magicorb`
ADD PRIMARY KEY (`transportID`);
--
-- Indexes for table `magicstone`
--
ALTER TABLE `magicstone`
ADD PRIMARY KEY (`transportID`);
--
-- Indexes for table `mysticalpiece`
--
ALTER TABLE `mysticalpiece`
ADD PRIMARY KEY (`transportID`);
--
-- Indexes for table `potential`
--
ALTER TABLE `potential`
ADD PRIMARY KEY (`transportID`);
--
-- Indexes for table `sequence`
--
ALTER TABLE `sequence`
ADD PRIMARY KEY (`seq`),
  ADD KEY `fk_sequence_transportID` (`transportID`);
--
-- Indexes for table `skills`
--
ALTER TABLE `skills`
ADD PRIMARY KEY (`transportID`);
--
-- Indexes for table `spirit`
--
ALTER TABLE `spirit`
ADD PRIMARY KEY (`transportID`);
--
-- Indexes for table `stats`
--
ALTER TABLE `stats`
ADD PRIMARY KEY (`transportID`);
--
-- Indexes for table `summary`
--
ALTER TABLE `summary`
ADD PRIMARY KEY (`seq`);
--
-- Indexes for table `training`
--
ALTER TABLE `training`
ADD PRIMARY KEY (`transportID`);
--
-- Indexes for table `transports`
--
ALTER TABLE `transports`
ADD PRIMARY KEY (`transportID`);
--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
MODIFY `transportID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `building`
--
ALTER TABLE `building`
MODIFY `transportID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `codex`
--
ALTER TABLE `codex`
MODIFY `transportID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `holystuff`
--
ALTER TABLE `holystuff`
MODIFY `transportID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `inven`
--
ALTER TABLE `inven`
MODIFY `transportID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `magicorb`
--
ALTER TABLE `magicorb`
MODIFY `transportID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `magicstone`
--
ALTER TABLE `magicstone`
MODIFY `transportID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `mysticalpiece`
--
ALTER TABLE `mysticalpiece`
MODIFY `transportID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `potential`
--
ALTER TABLE `potential`
MODIFY `transportID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `sequence`
--
ALTER TABLE `sequence`
MODIFY `seq` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
MODIFY `transportID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `spirit`
--
ALTER TABLE `spirit`
MODIFY `transportID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `stats`
--
ALTER TABLE `stats`
MODIFY `transportID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `summary`
--
ALTER TABLE `summary`
MODIFY `seq` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `training`
--
ALTER TABLE `training`
MODIFY `transportID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `transports`
--
ALTER TABLE `transports`
MODIFY `transportID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `assets`
--
ALTER TABLE `assets`
ADD CONSTRAINT `fk_assets_transportID` FOREIGN KEY (`transportID`) REFERENCES `transports` (`transportID`);
--
-- Constraints for table `building`
--
ALTER TABLE `building`
ADD CONSTRAINT `fk_building_transportID` FOREIGN KEY (`transportID`) REFERENCES `transports` (`transportID`);
--
-- Constraints for table `codex`
--
ALTER TABLE `codex`
ADD CONSTRAINT `fk_codex_transportID` FOREIGN KEY (`transportID`) REFERENCES `transports` (`transportID`);
--
-- Constraints for table `holystuff`
--
ALTER TABLE `holystuff`
ADD CONSTRAINT `fk_holystuff_transportID` FOREIGN KEY (`transportID`) REFERENCES `transports` (`transportID`);
--
-- Constraints for table `inven`
--
ALTER TABLE `inven`
ADD CONSTRAINT `fk_inven_transportID` FOREIGN KEY (`transportID`) REFERENCES `transports` (`transportID`);
--
-- Constraints for table `magicorb`
--
ALTER TABLE `magicorb`
ADD CONSTRAINT `fk_magicorb_transportID` FOREIGN KEY (`transportID`) REFERENCES `transports` (`transportID`);
--
-- Constraints for table `magicstone`
--
ALTER TABLE `magicstone`
ADD CONSTRAINT `fk_magicstone_transportID` FOREIGN KEY (`transportID`) REFERENCES `transports` (`transportID`);
--
-- Constraints for table `mysticalpiece`
--
ALTER TABLE `mysticalpiece`
ADD CONSTRAINT `fk_mysticalpiece_transportID` FOREIGN KEY (`transportID`) REFERENCES `transports` (`transportID`);
--
-- Constraints for table `potential`
--
ALTER TABLE `potential`
ADD CONSTRAINT `fk_potential_transportID` FOREIGN KEY (`transportID`) REFERENCES `transports` (`transportID`);
--
-- Constraints for table `sequence`
--
ALTER TABLE `sequence`
ADD CONSTRAINT `fk_seq_transport` FOREIGN KEY (`transportID`) REFERENCES `transports` (`transportID`),
  ADD CONSTRAINT `fk_sequence_transportID` FOREIGN KEY (`transportID`) REFERENCES `transports` (`transportID`);
--
-- Constraints for table `skills`
--
ALTER TABLE `skills`
ADD CONSTRAINT `fk_skills_transportID` FOREIGN KEY (`transportID`) REFERENCES `transports` (`transportID`);
--
-- Constraints for table `spirit`
--
ALTER TABLE `spirit`
ADD CONSTRAINT `fk_spirit_transportID` FOREIGN KEY (`transportID`) REFERENCES `transports` (`transportID`);
--
-- Constraints for table `stats`
--
ALTER TABLE `stats`
ADD CONSTRAINT `fk_stats_transportID` FOREIGN KEY (`transportID`) REFERENCES `transports` (`transportID`);
--
-- Constraints for table `summary`
--
ALTER TABLE `summary`
ADD CONSTRAINT `fk_seq` FOREIGN KEY (`seq`) REFERENCES `sequence` (`seq`);
--
-- Constraints for table `training`
--
ALTER TABLE `training`
ADD CONSTRAINT `fk_training_transportID` FOREIGN KEY (`transportID`) REFERENCES `transports` (`transportID`);
COMMIT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */
;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */
;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */
;