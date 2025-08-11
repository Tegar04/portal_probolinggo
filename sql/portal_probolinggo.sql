-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 11, 2025 at 01:40 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `portal_probolinggo`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`) VALUES
(1, 'admin@example.com', '$2y$10$/PEyiDH/UoW5R1wTH0mbKujYwWq3s7kMQkV0axyfgllQEKAjzgti2');

-- --------------------------------------------------------

--
-- Table structure for table `layanan`
--

CREATE TABLE `layanan` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `bidang` varchar(100) NOT NULL,
  `jenis` enum('publik','internal') NOT NULL,
  `url` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `logo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `highlight` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `layanan`
--

INSERT INTO `layanan` (`id`, `nama`, `bidang`, `jenis`, `url`, `logo`, `highlight`) VALUES
(1, 'Peta Tematik', 'Geo Spasial', 'publik', 'https://petatematik.probolinggokota.go.id/public/', 'peta tematik.png', 1),
(2, 'Satu Data', 'Statistik', 'publik', 'https://satudata.probolinggokota.go.id', 'satu data.png', 1),
(3, 'Simpan SPBE', 'Informatika', 'publik', 'https://simpan-spbe.probolinggokota.go.id./', 'simpan spbe.png', 1),
(4, 'Klinik Hoaks', 'Informatika', 'publik', 'https://klinikhoaks.probolinggokota.go.id/', 'klinik hoaks.png', 1),
(5, 'Sideka Pro', 'Kesehatan', 'publik', 'https://sideka.probolinggokota.go.id/', 'sideka pro.png', 1),
(6, 'UKM Pintar', 'UMKM', 'publik', 'https://ukmpintar.probolinggokota.go.id/', 'ukm pintar.png', 1),
(7, 'Umik Hebat', 'UMKM', 'publik', 'https://umikhebat.probolinggokota.go.id/', 'umik hebat.png', 1),
(11, 'Aplikasi LPPD', 'Sekretariat', 'internal', 'https://lppd.probolinggokota.go.id', 'elppd.png', 1),
(12, 'Aplikasi Dashboard', 'Informatika', 'internal', 'https://cc.probolinggokota.go.id//', 'dashboard.png', 1),
(13, 'CWP PDN', 'Informatika', 'internal', 'https://cwpanel.probolinggokota.go.id/login/index.php/', 'cwp pdn.png', 1),
(14, 'Portal Emas', 'Informatika', 'internal', 'https://portalemas.probolinggokota.go.id//', 'portal emas.png', 1),
(15, 'Uptime', 'Informatika', 'internal', 'https://uptime.probolinggokota.go.id/dashboard/', 'uptime.png', 1),
(16, 'Simakpro', 'Manajemen PKK', 'internal', 'https://simakpro.probolinggokota.go.id//', 'simakpro.png', 1),
(17, 'Manajemen Firewall', 'Informatika', 'internal', 'https://firewall.probolinggokota.go.id//', 'firewall.png', 1),
(18, 'Simpustronik', 'Kesehatan', 'internal', 'https://simpustronik.probolinggokota.go.id//', 'simpustronik.png', 1),
(19, 'Sijati', 'Sekretariat', 'internal', 'https://sijati.probolinggokota.go.id//', 'sijati.png', 1),
(20, 'MPP Digital Nasional', 'Digitalisasi', 'internal', 'https://admin.mppdigital.go.id/', 'mpp.png', 1),
(22, 'Sinergi Handal', 'Sekretariat', 'internal', 'https://sinergihandal.probolinggokota.go.id/', 'SINERGI HANDAL.drawio.png', 0),
(27, 'Layanan PUDAM', 'Penyediaan Air Minum', 'publik', 'https://perumdam.probolinggokota.go.id/', 'logo_68900fe2bc173.webp', 1),
(28, 'Iprobolinggo', 'Perpustakaan', 'publik', 'https://play.google.com/store/apps/details?id=mam.reader.iprobolinggo&pcampaignid=web_share', 'logo_6890148c0891a.jpg', 1),
(30, 'Siskia Pro Cantik', 'Kesehatan', 'publik', 'https://siskia.probolinggokota.go.id/', 'logo_689017ca7e31d.png', 0),
(31, 'E-Sultan', 'Sekretariat', 'publik', 'https://e-sultan.probolinggokota.go.id/login', 'logo_689018224a105.png', 0),
(32, 'PPDB', 'Pendidikan', 'publik', 'https://ppdb.probolinggokota.go.id/', 'logo_68901887bdcc4.png', 0),
(33, 'Layanan Kependudukan', 'Administrasi Kependudukan', 'publik', 'https://siak.probolinggokota.go.id/', 'logo_6890196b148a9.png', 0),
(34, 'SIAB MASPRO', 'Penanggulangan Bencana', 'publik', 'https://play.google.com/store/apps/details?id=build.siabmaspro.android6605247f5e83b', 'logo_689019f2364ae.webp', 0),
(35, 'Da_Ormas', 'Politik', 'publik', 'https://mybisnis.id/Da_Ormas', 'logo_68901a300e7b3.png', 0),
(36, 'SIMBG', 'Manajemen Pembangunan Gedung', 'publik', 'simbg.pu.go.id', 'logo_68901b6bed99a.png', 0),
(37, 'Aplikasi Pengaduan', 'Informatika', 'publik', 'https://www.lapor.go.id/', 'logo_68901ba1793e8.png', 0),
(39, 'OSS RBA', 'Penanaman Modal', 'publik', 'https://oss.go.id/', 'logo_68901eb102730.png', 0),
(40, 'Cloud', 'Informatika', 'internal', 'https://cloud.probolinggokota.go.id/index.php/login', 'logo_68901f1f33dbd.png', 0),
(41, 'Drive', 'Informatika', 'internal', 'https://drive.probolinggokota.go.id/login', 'logo_68901f747dddf.png', 0),
(42, 'Gateway', 'Informatika', 'internal', 'https://gateway.probolinggokota.go.id/login', 'logo_68901fbc6cc82.png', 0),
(43, 'CSIRT', 'Informatika', 'internal', 'https://layanan-csirt.probolinggokota.go.id/', 'logo_68901fe2c4d9a.png', 0),
(44, 'Observarium', 'Informatika', 'internal', 'https://observium.probolinggokota.go.id/', 'logo_6890202032c2b.png', 0),
(45, 'Backup', 'Informatika', 'internal', 'https://backup.probolinggokota.go.id/index.php/login', 'logo_6890206173f81.png', 0),
(46, 'MYULTPK', 'Sosial', 'internal', 'https://myultpk.probolinggokota.go.id/app/login', 'logo_689020b92149d.png', 0),
(47, 'Siji Online', 'Kesehatan', 'internal', 'https://sijionline.probolinggokota.go.id/', 'logo_68902104dcf3d.png', 1),
(48, 'Smart SIP', 'Kesehatan', 'internal', 'https://smartsip.probolinggokota.go.id/', 'logo_6890215345ec6.jpg', 0),
(49, 'E-BPHTB', 'Pengelolaan Keuangan', 'internal', 'https://e-bphtb.probolinggokota.go.id/', 'logo_68902191f26bd.png', 0),
(50, 'E-SPPT', 'Pengelolaan Keuangan', 'internal', 'https://e-sppt.probolinggokota.go.id/', 'logo_689021cb79522.png', 0),
(51, 'Simpatda', 'Pengelolaan Keuangan', 'internal', 'https://simpatda.probolinggokota.go.id/', 'logo_68902210a59da.png', 0),
(52, 'Simral', 'Pengelolaan Keuangan', 'internal', 'https://simral.probolinggokota.go.id/', 'logo_68902258d0dfe.png', 0),
(53, 'SIAP', 'Kepegawaian', 'internal', 'https://siap.probolinggokota.go.id/', 'logo_6890228acf45d.png', 0),
(54, 'Simpeg', 'Kepegawaian', 'internal', 'https://simpeg.probolinggokota.go.id/lite/4.4/', 'logo_689022b258b61.png', 0),
(55, 'E-RTLH', 'Pekerjaan Umum & Penataan Ruang', 'internal', 'https://e-rtlh.probolinggokota.go.id/', 'logo_6890230a485ad.png', 0),
(56, 'INSLISLITE', 'Perpustakaan', 'internal', 'https://perpustakaan.probolinggokota.go.id/opac/', 'logo_6890233353918.png', 0),
(57, 'Wisata Probolinggo', 'Pariwisata', 'internal', 'https://wisata.probolinggokota.go.id/', 'logo_6890236eaa374.png', 0),
(58, 'Salaman', 'Perlindungan Masyarakat', 'internal', 'https://salaman.probolinggokota.go.id./', 'logo_6890239abddcb.png', 0),
(59, 'E-Tamoy', 'Sekretariat', 'internal', 'https://etamoy.probolinggokota.go.id/', 'logo_689023d3e2223.png', 0),
(60, 'Xenter Mobile', 'Kesehatan', 'internal', 'https://play.google.com/store/apps/dev?id=8380621304905663224&hl=en', 'logo_6890240d95225.png', 0),
(61, 'SiDidik', 'Kesehatan', 'internal', 'http://103.186.0.43:3501/login.php', 'logo_6890242dadd84.png', 0),
(68, 'GOPOINT', 'Perencanaan dan Pembangunan Daerah', 'publik', 'https://gopoint.probolinggokota.go.id/', 'logo_689152400c67e.png', 0),
(69, 'INAPROC', 'Pengadaan Barang & Jasa', 'publik', 'https://lpse.probolinggokota.go.id/eproc4', 'logo_689152e0b5b80.png', 0);

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `attempts` int DEFAULT '0',
  `lock_stage` int DEFAULT '0',
  `lock_until` int DEFAULT '0',
  `last_attempt` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `username`, `ip_address`, `attempts`, `lock_stage`, `lock_until`, `last_attempt`) VALUES
(1, NULL, '127.0.0.1', 0, 0, 0, 1754874106),
(2, NULL, '192.168.10.201', 0, 0, 0, 1754621517);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('Doat9HNUyWGk2U9gV8BrJNuciiDt0OKwtBQETJAj', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiMHdqdnhFUVZyZkhHcVh5clVBbXhkcTVJa1prRE43amp4MmJYdkNrUyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9kYXNoYm9hcmQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjE1OiJhZG1pbl9sb2dnZWRfaW4iO2I6MTtzOjExOiJhZG1pbl9lbWFpbCI7czoxNzoiYWRtaW5AZXhhbXBsZS5jb20iO3M6MTQ6ImFkbWluX3VzZXJuYW1lIjtzOjE3OiJhZG1pbkBleGFtcGxlLmNvbSI7fQ==', 1753843706),
('qeGMMD0WTwejBm8KpuESbPOiQvDKEimcWSgRxXMc', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoia2RYWFBNbEJ3S2xlNE1RcEpoRlhGWldIUFNiaGhKT3lyZmpNOFQzVyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1753927217);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `layanan`
--
ALTER TABLE `layanan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `layanan`
--
ALTER TABLE `layanan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
