-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 04, 2025 at 08:34 PM
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
-- Database: `pengumpulantugas`
--

-- --------------------------------------------------------

--
-- Table structure for table `daftar_prak`
--

CREATE TABLE `daftar_prak` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `mata_prak_id` int(11) NOT NULL,
  `tanggal_daftar` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `daftar_prak`
--

INSERT INTO `daftar_prak` (`id`, `user_id`, `mata_prak_id`, `tanggal_daftar`) VALUES
(1, 2, 1, '2025-07-04 15:18:49');

-- --------------------------------------------------------

--
-- Table structure for table `laporan_prak`
--

CREATE TABLE `laporan_prak` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `modul_id` int(11) NOT NULL,
  `file_lapor_path` varchar(255) NOT NULL,
  `tanggal_upload` timestamp NOT NULL DEFAULT current_timestamp(),
  `status_laporan` enum('submitted','late','dinilai') DEFAULT 'submitted',
  `nilai` int(11) DEFAULT NULL,
  `feedback_asisten` text DEFAULT NULL,
  `tanggal_dinilai` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `laporan_prak`
--

INSERT INTO `laporan_prak` (`id`, `user_id`, `modul_id`, `file_lapor_path`, `tanggal_upload`, `status_laporan`, `nilai`, `feedback_asisten`, `tanggal_dinilai`) VALUES
(1, 2, 1, '../uploads/laporan/laporan_2_1_1751647102.pdf', '2025-07-04 16:38:22', 'dinilai', 100, 'Bagus mantap asoy', '2025-07-04 17:05:30');

-- --------------------------------------------------------

--
-- Table structure for table `mata_prak`
--

CREATE TABLE `mata_prak` (
  `id` int(11) NOT NULL,
  `nama_prak` varchar(100) NOT NULL,
  `kode_prak` varchar(10) NOT NULL,
  `deskripsi` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mata_prak`
--

INSERT INTO `mata_prak` (`id`, `nama_prak`, `kode_prak`, `deskripsi`, `is_active`, `created_at`) VALUES
(1, 'Dasar Jaringan', 'TI301', 'Deskripsi Dasar Jaringan', 1, '2025-07-04 15:17:58'),
(2, 'Implementasi Basis Data', 'TI302', 'IBD Desc', 1, '2025-07-04 17:14:47');

-- --------------------------------------------------------

--
-- Table structure for table `modul_prak`
--

CREATE TABLE `modul_prak` (
  `id` int(11) NOT NULL,
  `mata_prak_id` int(11) NOT NULL,
  `nomor_modul` int(11) NOT NULL,
  `nama_modul` varchar(100) NOT NULL,
  `desc_modul` text DEFAULT NULL,
  `file_modul_path` varchar(255) DEFAULT NULL,
  `due_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `modul_prak`
--

INSERT INTO `modul_prak` (`id`, `mata_prak_id`, `nomor_modul`, `nama_modul`, `desc_modul`, `file_modul_path`, `due_date`, `created_at`) VALUES
(1, 1, 1, 'Modul Dasjar', 'Deskripsi Modul Dasjar', '../uploads/materi/materi_1_1751647060.pdf', '2025-07-10 16:59:00', '2025-07-04 16:37:40'),
(2, 1, 2, 'Modul 2', '', '../uploads/materi/materi_1_1751653392.pdf', '2025-07-17 13:22:00', '2025-07-04 18:23:12');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('mahasiswa','asisten') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `password`, `role`, `created_at`) VALUES
(2, 'Mhs nisrina', 'nisrinamhs@gmail.com', '$2y$10$pDE4lUw5aIbzMMNktWD4o.WxALzRN6y8LknBRi7BhNuzZMIcA0UMC', 'mahasiswa', '2025-07-03 15:49:47'),
(3, 'Assist nisrina', 'nisrinaasst@gmail.com', '$2y$10$VqkUF0xy/EVgRr.eyZzDBe7kmpoYYNrPbvJWdSlviBYJ8fwRpd4hq', 'asisten', '2025-07-03 15:51:05');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `daftar_prak`
--
ALTER TABLE `daftar_prak`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mata_prak_id` (`mata_prak_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `laporan_prak`
--
ALTER TABLE `laporan_prak`
  ADD PRIMARY KEY (`id`),
  ADD KEY `modul_id` (`modul_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `mata_prak`
--
ALTER TABLE `mata_prak`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_prak` (`kode_prak`);

--
-- Indexes for table `modul_prak`
--
ALTER TABLE `modul_prak`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mata_prak_id` (`mata_prak_id`,`nomor_modul`);

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
-- AUTO_INCREMENT for table `daftar_prak`
--
ALTER TABLE `daftar_prak`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `laporan_prak`
--
ALTER TABLE `laporan_prak`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `mata_prak`
--
ALTER TABLE `mata_prak`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `modul_prak`
--
ALTER TABLE `modul_prak`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `daftar_prak`
--
ALTER TABLE `daftar_prak`
  ADD CONSTRAINT `daftar_prak_ibfk_1` FOREIGN KEY (`mata_prak_id`) REFERENCES `mata_prak` (`id`),
  ADD CONSTRAINT `daftar_prak_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `laporan_prak`
--
ALTER TABLE `laporan_prak`
  ADD CONSTRAINT `laporan_prak_ibfk_1` FOREIGN KEY (`modul_id`) REFERENCES `modul_prak` (`id`),
  ADD CONSTRAINT `laporan_prak_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `modul_prak`
--
ALTER TABLE `modul_prak`
  ADD CONSTRAINT `modul_prak_ibfk_2` FOREIGN KEY (`mata_prak_id`) REFERENCES `mata_prak` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
