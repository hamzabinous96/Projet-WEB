-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 15, 2025 at 02:32 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `project_weconnect1`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `mot_de_passe` varchar(255) DEFAULT NULL,
  `date_creation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `participation`
--

CREATE TABLE `participation` (
  `id_participant` int(11) NOT NULL,
  `id_projet` int(11) NOT NULL,
  `date_participation` datetime DEFAULT current_timestamp(),
  `created_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projets`
--

CREATE TABLE `projets` (
  `id_projet` int(11) NOT NULL,
  `titre` varchar(150) NOT NULL,
  `association` int(11) NOT NULL,
  `lieu` varchar(150) DEFAULT NULL,
  `date_debut` date DEFAULT NULL,
  `date_fin` date DEFAULT NULL,
  `disponibilite` varchar(50) DEFAULT NULL,
  `descriptionp` text DEFAULT NULL,
  `categorie` varchar(100) DEFAULT NULL,
  `created_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `taches`
--

CREATE TABLE `taches` (
  `id_tache` int(11) NOT NULL,
  `nom_tache` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `id_projet` int(11) NOT NULL,
  `assignee` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `mot_de_passe` varchar(255) DEFAULT NULL,
  `type` enum('participant','association') NOT NULL,
  `date_inscription` datetime DEFAULT current_timestamp(),
  `created_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `participation`
--
ALTER TABLE `participation`
  ADD PRIMARY KEY (`id_participant`,`id_projet`),
  ADD KEY `fk_participation_projet` (`id_projet`),
  ADD KEY `fk_participation_admin` (`created_by`);

--
-- Indexes for table `projets`
--
ALTER TABLE `projets`
  ADD PRIMARY KEY (`id_projet`),
  ADD KEY `fk_projets_association` (`association`),
  ADD KEY `fk_projets_admin` (`created_by`);

--
-- Indexes for table `taches`
--
ALTER TABLE `taches`
  ADD PRIMARY KEY (`id_tache`),
  ADD KEY `fk_taches_projet` (`id_projet`),
  ADD KEY `fk_taches_assignee` (`assignee`),
  ADD KEY `fk_taches_admin` (`created_by`);

--
-- Indexes for table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_users_admin` (`created_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `projets`
--
ALTER TABLE `projets`
  MODIFY `id_projet` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `taches`
--
ALTER TABLE `taches`
  MODIFY `id_tache` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `participation`
--
ALTER TABLE `participation`
  ADD CONSTRAINT `fk_participation_admin` FOREIGN KEY (`created_by`) REFERENCES `admin` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_participation_participant` FOREIGN KEY (`id_participant`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_participation_projet` FOREIGN KEY (`id_projet`) REFERENCES `projets` (`id_projet`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `projets`
--
ALTER TABLE `projets`
  ADD CONSTRAINT `fk_projets_admin` FOREIGN KEY (`created_by`) REFERENCES `admin` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_projets_association` FOREIGN KEY (`association`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `taches`
--
ALTER TABLE `taches`
  ADD CONSTRAINT `fk_taches_admin` FOREIGN KEY (`created_by`) REFERENCES `admin` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_taches_assignee` FOREIGN KEY (`assignee`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_taches_projet` FOREIGN KEY (`id_projet`) REFERENCES `projets` (`id_projet`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD CONSTRAINT `fk_users_admin` FOREIGN KEY (`created_by`) REFERENCES `admin` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
