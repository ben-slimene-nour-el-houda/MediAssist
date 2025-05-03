-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3307
-- Généré le : sam. 03 mai 2025 à 22:35
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `mediassist`
--

-- --------------------------------------------------------

--
-- Structure de la table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `date_time` datetime NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `reminder_sent` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `appointments`
--

INSERT INTO `appointments` (`id`, `user_id`, `title`, `date_time`, `location`, `description`, `reminder_sent`, `created_at`) VALUES
(29, 13, 'Cardiology', '2025-05-01 17:05:00', 'Tunis', '', 0, '2025-05-01 12:18:38'),
(31, 15, 'dayday', '2025-05-01 21:00:00', 'Tunis', '', 0, '2025-05-01 18:52:27'),
(32, 16, 'Cardiology', '2025-05-02 20:00:00', 'Tunis', 'none', 0, '2025-05-02 13:59:15'),
(33, 16, 'Cardiology', '2025-05-02 08:00:00', 'Tunis', 'none', 0, '2025-05-02 13:59:35');

-- --------------------------------------------------------

--
-- Structure de la table `emergency_contacts`
--

CREATE TABLE `emergency_contacts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `contact_name` varchar(100) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `relationship` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `emergency_contacts`
--

INSERT INTO `emergency_contacts` (`id`, `user_id`, `contact_name`, `phone_number`, `relationship`, `created_at`) VALUES
(15, 14, 'nour', '54478170', 'pisi2', '2025-05-01 10:04:17'),
(18, 13, 'sami', '27410860', 'husband', '2025-05-01 12:56:03'),
(20, 15, 'wiem', '+21629079820', 'Gabes', '2025-05-01 18:55:51'),
(21, 15, 'insaf', '52700746', 'Sidi Bouzid', '2025-05-01 18:58:28'),
(22, 13, 'kmar', '28700103', 'friend', '2025-05-02 12:38:38'),
(23, 16, 'Adem', '27410869', 'Brotherr', '2025-05-02 14:01:00');

-- --------------------------------------------------------

--
-- Structure de la table `medications`
--

CREATE TABLE `medications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `dosage` varchar(50) NOT NULL,
  `frequency` varchar(100) NOT NULL,
  `time_of_day` time DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `medications`
--

INSERT INTO `medications` (`id`, `user_id`, `name`, `dosage`, `frequency`, `time_of_day`, `start_date`, `end_date`, `instructions`, `is_active`, `created_at`) VALUES
(11, 14, 'smile', '100', 'Every other day', '10:00:00', '2025-05-15', '2025-05-29', '', 1, '2025-05-01 10:07:44'),
(13, 15, 'smile', '100', 'Weekly', '20:00:00', '2025-05-01', '2025-05-30', '', 0, '2025-05-01 18:51:46'),
(14, 13, 'Doliprane', '500', 'Three times daily', '08:00:00', '2025-05-01', '2025-05-31', '', 1, '2025-05-01 21:18:46'),
(15, 16, 'Doliprane', '500', 'Twice daily', '08:00:00', '2025-05-02', '2025-05-16', 'none', 0, '2025-05-02 13:58:40');

-- --------------------------------------------------------

--
-- Structure de la table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires`, `created_at`) VALUES
(1, 'bsnour446@gmail.com', 'a3729bd412690965be4b72413688b10da4d92b70e434880db25dc6def6e31433', '2025-05-03 21:35:30', '2025-05-03 18:35:30'),
(2, 'bsnour446@gmail.com', 'eb6422c82d9b6cba9ca5647ac420793386d2d311a3af2ef9652e16b9b4c081c7', '2025-05-03 21:36:07', '2025-05-03 18:36:07'),
(3, 'bsnour446@gmail.com', '648493c54035780adf8373b88fa472f40770264ac9709a20ab067cd6fcac0fa6', '2025-05-03 21:37:30', '2025-05-03 18:37:30'),
(4, 'bsnour446@gmail.com', '3e007f1bff111e9be12ced1c509faef82767e079ecac2e506d315c55bf70ec54', '2025-05-03 21:38:18', '2025-05-03 18:38:18'),
(5, 'bsnour446@gmail.com', '700f197f15402f18aa5ce32da6b8967ee9b3a34b5cc5e3832dbcbfb079fccd3e', '2025-05-03 21:38:25', '2025-05-03 18:38:25'),
(6, 'bsnour446@gmail.com', 'bad3c9b97bb9e4f288d04c381d2ab61396ab32de968337108f50ebd3f80fec1f', '2025-05-03 21:41:39', '2025-05-03 18:41:39'),
(7, 'bsnour446@gmail.com', '83069f841f42d4deb7fe748d1a303557405b237f193161b4a2603b551df9dc11', '2025-05-03 21:42:37', '2025-05-03 18:42:37'),
(8, 'bsnour446@gmail.com', 'dffbaf711b935741635a6793c9bcb7c9c6be616d21120c913a05da082961095c', '2025-05-03 21:42:55', '2025-05-03 18:42:55'),
(9, 'bsnour446@gmail.com', 'd1a942fb92cbecfee213ee2227dd2e4270a7986c4a05acfe91e615991674b390', '2025-05-03 22:01:33', '2025-05-03 19:01:33'),
(10, 'bsnour446@gmail.com', 'b761fd9d73c93551873e5ff7d260e7a3c04ac9b078582f3a003db81bed937ca9', '2025-05-03 22:01:40', '2025-05-03 19:01:40'),
(11, 'bsnour446@gmail.com', '83726b30c0d95a932dee5fb148296efbdca05c78113a03d30fddc74629afce57', '2025-05-03 22:01:44', '2025-05-03 19:01:44'),
(12, 'bsnour446@gmail.com', '24f652499fb76b80dff3712c76bb2551f73abe005b6bb258fd31a03da7838163', '2025-05-03 22:01:47', '2025-05-03 19:01:47');

-- --------------------------------------------------------

--
-- Structure de la table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `medication_id` int(11) DEFAULT NULL,
  `doctor_name` varchar(100) NOT NULL,
  `medication_name` varchar(255) DEFAULT NULL,
  `date_prescribed` date NOT NULL,
  `expires_on` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `prescription_pdf` varchar(255) DEFAULT NULL,
  `dosage` varchar(30) NOT NULL,
  `quantity` varchar(30) NOT NULL,
  `instructions` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `age` int(3) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `username` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `age`, `gender`, `phone`, `profile_photo`, `created_at`, `username`) VALUES
(13, 'Nour El Houda Ben Slimène', 'bsnour446@gmail.com', '$2y$10$sevheuKF5EKC7HxadmGmpufpb6h/a9tI9qVW1DiS25y3hRjavivxG', 20, 'female', '54478171', 'Uploads/68136b27b6481_Screenshot 2025-04-30 211519.png', '2025-05-01 10:05:14', 'BenSlimeneNourElHouda'),
(14, 'nourhedhli', 'nourh@gmail.com', '$2y$10$1EigPMA55DXKvRBjPfk46eRBdkMuXZ2jOj7Rl1K.h/V4EG5zaXWXi', 19, 'female', '+21696544946', 'Uploads/681346e048813_Screenshot 2025-05-01 110043.png', '2025-05-01 11:03:12', 'nour'),
(15, 'Dadi ', 'dadi123@gmail.com', '$2y$10$jByaQlBtcZoW6B6tYadHVOQrU7V8dcP9LFRhE4PHR2wpsVaDrlPqq', 20, 'female', '29014785', 'Uploads/6813c27373a20_Screenshot 2025-04-25 182017.png', '2025-05-01 19:50:27', 'wiem'),
(16, 'Imen Ouali', 'imenouli@gmail.com', '$2y$10$cBlNMTrPBP2/UJoSiwrTi.To.m1NIO3FldJJmh2I2YkXubwdI6CRe', 32, 'female', '20159876', 'Uploads/6814cf6061e66_Screenshot 2025-04-25 182017.png', '2025-05-02 14:57:52', 'imen');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `emergency_contacts`
--
ALTER TABLE `emergency_contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `medications`
--
ALTER TABLE `medications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`);

--
-- Index pour la table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT pour la table `emergency_contacts`
--
ALTER TABLE `emergency_contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT pour la table `medications`
--
ALTER TABLE `medications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pour la table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `emergency_contacts`
--
ALTER TABLE `emergency_contacts`
  ADD CONSTRAINT `emergency_contacts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `medications`
--
ALTER TABLE `medications`
  ADD CONSTRAINT `medications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
