-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Vært: mariadb
-- Genereringstid: 15. 09 2025 kl. 07:54:01
-- Serverversion: 12.0.2-MariaDB-ubu2404
-- PHP-version: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bogbingo`
--

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `bingoKort`
--

CREATE TABLE `bingoKort` (
  `kortId` int(10) UNSIGNED NOT NULL,
  `pladeId` int(10) UNSIGNED NOT NULL,
  `promptId` tinyint(3) UNSIGNED NOT NULL,
  `titel` varchar(200) NOT NULL,
  `forfatter` varchar(200) DEFAULT NULL,
  `noter` text DEFAULT NULL,
  `coverUrl` varchar(500) DEFAULT NULL,
  `finished` tinyint(1) DEFAULT NULL,
  `opdateret` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `bingoPlade`
--

CREATE TABLE `bingoPlade` (
  `kortId` int(10) UNSIGNED NOT NULL,
  `loginId` int(10) UNSIGNED NOT NULL,
  `kortDato` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `bingoPrompts`
--

CREATE TABLE `bingoPrompts` (
  `promptId` tinyint(3) UNSIGNED NOT NULL,
  `label` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Data dump for tabellen `bingoPrompts`
--

INSERT INTO `bingoPrompts` (`promptId`, `label`) VALUES
(1, 'Fantasy'),
(2, 'Mystery'),
(3, 'Sci-Fi'),
(4, 'Historisk'),
(5, 'Nonfiktion'),
(6, 'Biografi'),
(7, 'Debut'),
(8, 'Oversættelse'),
(9, 'KLassiker'),
(10, 'Novellesamling'),
(11, 'Tegneserie'),
(12, 'Digtsamling'),
(13, 'Gyser'),
(14, 'Romantik'),
(15, 'Young Adult'),
(16, 'børnebog'),
(17, 'Priswinder'),
(18, 'på hylden for længe'),
(19, '500+ sider'),
(20, 'Under 200 sider'),
(21, 'Lydbog'),
(22, 'E-bog'),
(23, 'Lånt på biblioteket'),
(24, 'Vens anbefaling');

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `login`
--

CREATE TABLE `login` (
  `loginId` int(10) UNSIGNED NOT NULL,
  `loginNavn` varchar(100) NOT NULL,
  `loginKodeord` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Data dump for tabellen `login`
--

INSERT INTO `login` (`loginId`, `loginNavn`, `loginKodeord`) VALUES
(1, 'Line', '$2y$12$QpzlrmqPwDClIHaIjoiWH.DBbzZylabP4w27AFJEP2UV.9VuYBh2i'),
(2, 'Jacob', '$2y$12$ME3qbPFd8ktaOkxV4lROFu6ItXVD0SZ/plfHRxml0kcxv6YhBYIum'),
(3, 'Line98', '$2y$12$07icVhk1bhLYI6pNlNYRHu0PtZs7i1Hk9kCZBP6U78F6FMapwNRGO');

--
-- Begrænsninger for dumpede tabeller
--

--
-- Indeks for tabel `bingoKort`
--
ALTER TABLE `bingoKort`
  ADD PRIMARY KEY (`kortId`);

--
-- Indeks for tabel `bingoPlade`
--
ALTER TABLE `bingoPlade`
  ADD PRIMARY KEY (`kortId`),
  ADD UNIQUE KEY `kortId` (`kortId`,`loginId`),
  ADD KEY `fk_kort_login` (`loginId`);

--
-- Indeks for tabel `bingoPrompts`
--
ALTER TABLE `bingoPrompts`
  ADD PRIMARY KEY (`promptId`);

--
-- Indeks for tabel `login`
--
ALTER TABLE `login`
  ADD PRIMARY KEY (`loginId`);

--
-- Brug ikke AUTO_INCREMENT for slettede tabeller
--

--
-- Tilføj AUTO_INCREMENT i tabel `bingoKort`
--
ALTER TABLE `bingoKort`
  MODIFY `kortId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `bingoPlade`
--
ALTER TABLE `bingoPlade`
  MODIFY `kortId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `bingoPrompts`
--
ALTER TABLE `bingoPrompts`
  MODIFY `promptId` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Tilføj AUTO_INCREMENT i tabel `login`
--
ALTER TABLE `login`
  MODIFY `loginId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Begrænsninger for dumpede tabeller
--

--
-- Begrænsninger for tabel `bingoPlade`
--
ALTER TABLE `bingoPlade`
  ADD CONSTRAINT `fk_kort_login` FOREIGN KEY (`loginId`) REFERENCES `login` (`loginId`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
