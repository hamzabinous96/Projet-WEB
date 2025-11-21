-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 21, 2025 at 06:22 PM
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
-- Database: `votre_base_de_donnees`
--

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
  `description` text DEFAULT NULL,
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
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `user_type` enum('citoyen','association','admin') NOT NULL DEFAULT 'citoyen',
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `status` enum('active','banned','pending') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `projets`
--
ALTER TABLE `projets`
  ADD PRIMARY KEY (`id_projet`),
  ADD KEY `association` (`association`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `taches`
--
ALTER TABLE `taches`
  ADD PRIMARY KEY (`id_tache`),
  ADD KEY `id_projet` (`id_projet`),
  ADD KEY `assignee` (`assignee`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

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
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `projets`
--
ALTER TABLE `projets`
  ADD CONSTRAINT `projets_ibfk_1` FOREIGN KEY (`association`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `projets_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `taches`
--
ALTER TABLE `taches`
  ADD CONSTRAINT `taches_ibfk_1` FOREIGN KEY (`id_projet`) REFERENCES `projets` (`id_projet`),
  ADD CONSTRAINT `taches_ibfk_2` FOREIGN KEY (`assignee`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `taches_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
