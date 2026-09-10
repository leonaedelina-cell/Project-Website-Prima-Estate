-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 10, 2026 at 05:03 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `estate_prima`
--

-- --------------------------------------------------------

--
-- Table structure for table `agen`
--

CREATE TABLE `agen` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `foto_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `agen`
--

INSERT INTO `agen` (`id`, `nama`, `no_hp`, `email`, `foto_url`) VALUES
(1, 'Sinta Marketing', '081234567890', 'Sinta@estateprima.test', ''),
(2, 'Nci Marketing', '081345678910', 'Nci@estateprima.test', ''),
(3, 'Lovea Marketing', '081345678910', 'Lovea@estateprima.test', '');

-- --------------------------------------------------------

--
-- Table structure for table `galeri_properti`
--

CREATE TABLE `galeri_properti` (
  `id` int NOT NULL,
  `properti_id` int NOT NULL,
  `gambar_url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pesan_kontak`
--

CREATE TABLE `pesan_kontak` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `pesan` text NOT NULL,
  `status_dibaca` enum('belum','sudah') NOT NULL DEFAULT 'belum',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pesan_kontak`
--

INSERT INTO `pesan_kontak` (`id`, `nama`, `email`, `no_hp`, `pesan`, `status_dibaca`, `created_at`) VALUES
(1, 'VINKA BRIGITTA PRINCESSA', 'vinkaprincessa19@gmail.com', '081345678910', 'apa ya', 'sudah', '2026-08-27 09:21:46');

-- --------------------------------------------------------

--
-- Table structure for table `properti`
--

CREATE TABLE `properti` (
  `id` int NOT NULL,
  `judul` varchar(150) NOT NULL,
  `deskripsi` text,
  `harga` decimal(15,2) NOT NULL COMMENT 'Harga jual utama atau patokan dasar',
  `tipe_transaksi` enum('jual','sewa','keduanya') NOT NULL DEFAULT 'jual',
  `durasi_minimal` enum('6 bulan','1 tahun') DEFAULT NULL,
  `harga_sewa` decimal(15,2) DEFAULT NULL,
  `periode_sewa` enum('bulan','tahun') DEFAULT NULL,
  `minimal_sewa` int DEFAULT '1',
  `tipe` enum('rumah','apartemen','tanah','ruko') NOT NULL DEFAULT 'rumah',
  `status` enum('tersedia','terjual') NOT NULL DEFAULT 'tersedia',
  `alamat` varchar(255) NOT NULL,
  `kota` varchar(100) NOT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  `luas_tanah` int DEFAULT NULL,
  `luas_bangunan` int DEFAULT NULL,
  `kamar_tidur` int DEFAULT '0',
  `kamar_mandi` int DEFAULT '0',
  `carport` int DEFAULT '0',
  `fasilitas` text,
  `status_hunian` enum('kosong','terisi') NOT NULL DEFAULT 'kosong',
  `gambar_url` varchar(255) DEFAULT NULL,
  `agen_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `properti`
--

INSERT INTO `properti` (`id`, `judul`, `deskripsi`, `harga`, `tipe_transaksi`, `durasi_minimal`, `harga_sewa`, `periode_sewa`, `minimal_sewa`, `tipe`, `status`, `alamat`, `kota`, `lat`, `lng`, `luas_tanah`, `luas_bangunan`, `kamar_tidur`, `kamar_mandi`, `carport`, `fasilitas`, `status_hunian`, `gambar_url`, `agen_id`, `created_at`, `updated_at`) VALUES
(1, 'Rumah Minimalis 2 Lantai Green Valley', 'Rumah nyaman dekat area perkantoran, siap huni.', 850000000.00, 'keduanya', NULL, 45000000.00, 'tahun', 1, 'rumah', 'terjual', 'Jl. Green Valley No. 12', 'Bekasi', NULL, NULL, 120, 90, 3, 2, 1, NULL, 'kosong', 'https://images.unsplash.com/photo-1570129477492-45c003edd2be', 1, '2026-08-11 21:05:49', '2026-08-28 02:47:16'),
(2, 'Apartemen Studio City View', 'Apartemen studio strategis, dekat mall & stasiun.', 450000000.00, 'sewa', NULL, 5000000.00, 'bulan', 6, 'apartemen', 'terjual', 'Jl. Sudirman No. 5', 'Jakarta Selatan', NULL, NULL, NULL, 32, 1, 1, 0, NULL, 'kosong', 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688', 1, '2026-08-11 21:05:49', '2026-08-28 02:47:16'),
(3, 'Rumah Keluarga Citra Garden', 'Hunian nyaman dengan halaman luas, dekat sekolah dan pusat belanja.', 1450000000.00, 'jual', NULL, NULL, NULL, 1, 'rumah', 'terjual', 'Jl. Citra Garden Blok C No. 12', 'Bekasi', -6.2382700, 106.9755700, 144, 130, 3, 2, 2, NULL, 'kosong', 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?auto=format&fit=crop&w=1200&q=80', 1, '2026-08-27 10:47:30', '2026-08-27 11:19:53'),
(4, 'Tanah Strategis Sentul', 'Lahan datar di kawasan berkembang, ideal untuk villa atau investasi jangka panjang.', 875000000.00, 'jual', NULL, NULL, NULL, 1, 'tanah', 'terjual', 'Jl. Raya Sentul KM 7', 'Bogor', -6.5648000, 106.8505000, 500, NULL, 0, 0, 0, NULL, 'kosong', 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1200&q=80', 3, '2026-08-27 10:47:30', '2026-08-27 11:25:59'),
(5, 'Ruko Niaga Kemang', 'Ruko tiga lantai di area komersial ramai, siap digunakan untuk usaha atau kantor.', 3200000000.00, 'jual', NULL, NULL, NULL, 1, 'ruko', 'tersedia', 'Jl. Kemang Raya No. 72', 'Jakarta Selatan', -6.2604800, 106.8136500, 80, 210, 0, 3, 3, NULL, 'kosong', 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1200&q=80', 2, '2026-08-27 10:47:30', '2026-08-27 10:47:30'),
(6, 'Rumah Minimalis Alam Sutera', 'Rumah minimalis siap huni di cluster dengan keamanan 24 jam dan taman lingkungan.', 2100000000.00, 'keduanya', NULL, 45000000.00, 'tahun', 1, 'rumah', 'tersedia', 'Jl. Alam Sutera Boulevard No. 9', 'Tangerang', -6.2400700, 106.6576900, 136, 160, 4, 3, 2, NULL, 'kosong', 'https://images.unsplash.com/photo-1600047509807-ba8f99d2cdde?auto=format&fit=crop&w=1200&q=80', 3, '2026-08-27 10:47:30', '2026-08-28 02:47:16'),
(7, 'Apartemen City View Bandung', 'Unit apartemen dengan balkon dan pemandangan kota, dekat kampus dan fasilitas umum.', 720000000.00, 'sewa', NULL, 5000000.00, 'bulan', 6, 'apartemen', 'tersedia', 'Jl. Pasteur No. 28', 'Bandung', -6.8915200, 107.5980500, 38, 38, 1, 1, 1, NULL, 'kosong', 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=1200&q=80', 2, '2026-08-27 10:47:30', '2026-08-28 02:47:16'),
(8, 'Rumah Klasik Pondok Indah', 'Rumah besar dengan taman depan dan ruang keluarga luas di kawasan premium.', 5600000000.00, 'jual', NULL, NULL, NULL, 1, 'rumah', 'tersedia', 'Jl. Metro Pondok Indah No. 30', 'Jakarta Selatan', -6.2651100, 106.7814300, 350, 420, 5, 4, 3, NULL, 'kosong', 'https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?auto=format&fit=crop&w=1200&q=80', 1, '2026-08-27 10:47:30', '2026-08-27 11:19:26'),
(9, 'Villa Pinus Riverside', 'Villa asri dengan suasana sejuk, halaman luas, dan akses langsung menuju aliran sungai.', 2750000000.00, 'jual', NULL, NULL, NULL, 1, 'rumah', 'tersedia', 'Jl. Puncak Raya No. 88', 'Bogor', -6.7021000, 106.9502000, 300, 220, 4, 3, 2, NULL, 'kosong', 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?auto=format&fit=crop&w=1200&q=80', 3, '2026-08-27 10:47:30', '2026-08-27 10:47:30'),
(10, 'Rumah Tropis Bintaro', 'Hunian tropis dengan taman belakang, ruang keluarga terbuka, dan lingkungan yang nyaman.', 2350000000.00, 'jual', NULL, NULL, NULL, 1, 'rumah', 'tersedia', 'Jl. Bintaro Utama No. 16', 'Tangerang Selatan', -6.2787000, 106.7176000, 150, 185, 4, 3, 2, NULL, 'kosong', 'https://images.unsplash.com/photo-1600585154526-990dced4db0d?auto=format&fit=crop&w=1200&q=80', 1, '2026-08-27 10:47:30', '2026-08-27 10:47:30'),
(11, 'Apartemen Garden Heights', 'Apartemen modern dengan area hijau, fasilitas lengkap, dan akses mudah ke pusat kota.', 1250000000.00, 'sewa', NULL, 5000000.00, 'bulan', 6, 'apartemen', 'tersedia', 'Jl. Gatot Subroto Kav. 21', 'Jakarta Selatan', -6.2297000, 106.8216000, 56, 56, 2, 1, 1, NULL, 'kosong', 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=1200&q=80', 2, '2026-08-27 10:47:30', '2026-08-28 02:47:16'),
(12, 'Apartemen Marina Bay View', 'Unit apartemen nyaman dengan pemandangan laut, balkon pribadi, dan keamanan 24 jam.', 1680000000.00, 'jual', NULL, NULL, NULL, 1, 'apartemen', 'tersedia', 'Jl. Pantai Indah Kapuk No. 5', 'Jakarta Utara', -6.1074000, 106.7401000, 72, 72, 2, 2, 1, NULL, 'kosong', 'https://images.unsplash.com/photo-1493809842364-78817add7ffb?auto=format&fit=crop&w=1200&q=80', 2, '2026-08-27 10:47:30', '2026-08-27 10:47:30'),
(13, 'Villa Senja Ubud', 'Villa bergaya Bali dengan kolam renang pribadi dan pemandangan hijau yang menenangkan.', 3200000000.00, 'jual', NULL, NULL, NULL, 1, 'rumah', 'tersedia', 'Jl. Raya Tegallalang No. 12', 'Ubud', -8.4312000, 115.2797000, 420, 260, 3, 3, 2, NULL, 'kosong', 'https://images.unsplash.com/photo-1582268611958-ebfd161ef9cf?auto=format&fit=crop&w=1200&q=80', 3, '2026-08-27 10:47:30', '2026-08-27 10:47:30'),
(14, 'Rumah Scandinavian Depok', 'Rumah minimalis bergaya Scandinavian dengan tata ruang efisien dan pencahayaan alami.', 1350000000.00, 'jual', NULL, NULL, NULL, 1, 'rumah', 'tersedia', 'Jl. Margonda Raya No. 42', 'Depok', -6.3907000, 106.8246000, 110, 130, 3, 2, 1, NULL, 'kosong', 'https://images.unsplash.com/photo-1600047509807-ba8f99d2cdde?auto=format&fit=crop&w=1200&q=80', 1, '2026-08-27 10:47:30', '2026-08-27 10:47:30'),
(15, 'Apartemen Central Park Suite', 'Apartemen premium dekat pusat perbelanjaan, perkantoran, dan transportasi umum.', 1950000000.00, 'jual', NULL, NULL, NULL, 1, 'apartemen', 'tersedia', 'Jl. Letjen S. Parman Kav. 28', 'Jakarta Barat', -6.1776000, 106.7908000, 68, 68, 2, 2, 1, NULL, 'kosong', 'https://images.unsplash.com/photo-1540518614846-7eded433c457?auto=format&fit=crop&w=1200&q=80', 2, '2026-08-27 10:47:30', '2026-08-27 10:47:30'),
(16, 'Rumah Keluarga Cibubur', 'Hunian keluarga dengan carport luas, taman depan, dan akses dekat sekolah.', 1600000000.00, 'jual', NULL, NULL, NULL, 1, 'rumah', 'tersedia', 'Jl. Alternatif Cibubur No. 27', 'Bekasi', -6.3683000, 106.9067000, 180, 150, 4, 3, 2, NULL, 'kosong', 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=1200&q=80', 1, '2026-08-27 10:47:30', '2026-08-27 10:47:30'),
(17, 'Apartemen Braga Heritage', 'Apartemen bergaya elegan di kawasan bersejarah dengan akses dekat kuliner dan hiburan.', 890000000.00, 'jual', NULL, NULL, NULL, 1, 'apartemen', 'tersedia', 'Jl. Braga No. 10', 'Bandung', -6.9175000, 107.6098000, 48, 48, 2, 1, 1, NULL, 'kosong', 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=1200&q=80', 2, '2026-08-27 10:47:30', '2026-08-27 10:47:30'),
(18, 'Villa Lembah Salak', 'Villa luas untuk tempat tinggal atau liburan dengan udara sejuk dan panorama pegunungan.', 2450000000.00, 'jual', NULL, NULL, NULL, 1, 'rumah', 'tersedia', 'Jl. Ciapus No. 7', 'Bogor', -6.6865000, 106.7569000, 360, 240, 4, 3, 2, NULL, 'kosong', 'https://images.unsplash.com/photo-1602343168117-bb8ffe3e2e9f?auto=format&fit=crop&w=1200&q=80', 3, '2026-08-27 10:47:30', '2026-08-27 10:47:30'),
(19, 'Apartemen Kemang Residence', 'Unit apartemen nyaman di kawasan lifestyle dengan fasilitas gym dan kolam renang.', 1100000000.00, 'jual', NULL, NULL, NULL, 1, 'apartemen', 'tersedia', 'Jl. Kemang Raya No. 99', 'Jakarta Selatan', -6.2609000, 106.8144000, 52, 52, 2, 1, 1, NULL, 'kosong', 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=1200&q=80', 2, '2026-08-27 10:47:30', '2026-08-27 10:47:30'),
(20, 'Rumah Modern Kota Baru', 'Rumah modern siap huni dengan ruang kerja, taman kecil, dan keamanan lingkungan.', 1780000000.00, 'jual', NULL, NULL, NULL, 1, 'rumah', 'tersedia', 'Jl. Kota Baru Parahyangan No. 20', 'Bandung Barat', -6.8596000, 107.4758000, 140, 165, 3, 2, 2, NULL, 'kosong', 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&w=1200&q=80', 1, '2026-08-27 10:47:30', '2026-08-27 10:47:30'),
(22, 'Rumah Keluarga', '', 50000000.00, 'jual', NULL, 0.00, NULL, 1, 'rumah', 'tersedia', 'komplek', 'Bekasi', NULL, NULL, NULL, NULL, 0, 0, 0, '', 'kosong', '/assets/uploads/properti/831b0d472b13fb3e9f8972fd4dde8e72.jpg', 3, '2026-09-04 02:20:07', '2026-09-04 02:20:07'),
(23, 'Rumah Keluarga', '', 50000000.00, 'jual', NULL, 0.00, NULL, 1, 'rumah', 'tersedia', 'komplek', 'Bekasi', NULL, NULL, NULL, NULL, 0, 0, 0, '', 'kosong', '', NULL, '2026-09-04 02:21:44', '2026-09-04 02:21:44');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `properti_id` int NOT NULL,
  `tipe_transaksi` enum('jual','sewa') NOT NULL DEFAULT 'jual',
  `durasi_sewa` int DEFAULT NULL COMMENT 'Jumlah bulan/tahun sewa',
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `total_harga` decimal(15,2) DEFAULT NULL,
  `metode_bayar` enum('transfer_bank','cicilan_kpr','tunai','e-wallet','qris') DEFAULT NULL,
  `bukti_bayar` varchar(255) DEFAULT NULL,
  `status` enum('menunggu','diproses','disetujui','ditolak','selesai','lunas') NOT NULL DEFAULT 'menunggu',
  `catatan_admin` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id`, `user_id`, `properti_id`, `tipe_transaksi`, `durasi_sewa`, `tanggal_mulai`, `tanggal_selesai`, `total_harga`, `metode_bayar`, `bukti_bayar`, `status`, `catatan_admin`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'jual', NULL, NULL, NULL, NULL, 'transfer_bank', NULL, 'ditolak', '', '2026-08-24 23:18:37', '2026-08-27 10:30:34'),
(2, 1, 2, 'jual', NULL, NULL, NULL, NULL, 'cicilan_kpr', NULL, 'selesai', '', '2026-08-24 23:24:18', '2026-08-27 10:30:24'),
(3, 2, 1, 'jual', NULL, NULL, NULL, NULL, 'transfer_bank', NULL, 'selesai', '', '2026-08-24 23:40:57', '2026-08-27 10:30:06'),
(4, 1, 5, 'jual', NULL, NULL, NULL, NULL, 'transfer_bank', NULL, 'diproses', '', '2026-08-27 13:15:07', '2026-08-27 13:15:48'),
(5, 2, 5, 'jual', NULL, NULL, NULL, NULL, 'transfer_bank', NULL, 'menunggu', NULL, '2026-08-28 02:57:56', '2026-08-28 02:57:56'),
(6, 1, 7, 'sewa', 6, '2026-09-09', '2027-03-09', 30000000.00, 'tunai', NULL, 'selesai', '', '2026-09-09 02:09:38', '2026-09-09 02:10:39');

--
-- Triggers `transaksi`
--
DELIMITER $$
CREATE TRIGGER `after_transaksi_update_status` AFTER UPDATE ON `transaksi` FOR EACH ROW BEGIN
    IF NEW.status IN ('disetujui', 'selesai', 'lunas') AND NEW.tipe_transaksi = 'jual' THEN
        UPDATE `properti` SET `status` = 'terjual' WHERE `id` = NEW.properti_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `password`, `no_hp`, `role`, `created_at`) VALUES
(1, 'Admin Estate Prima', 'admin@estateprima.test', '$2b$12$FlB0UTEGdGwPd4jf9uEhmueApFvoN8CU0fsuC2NRvuK5WC6QkMdPO', NULL, 'admin', '2026-08-11 21:04:46'),
(2, 'Budi Santoso', 'budi@mail.test', '$2b$12$FlB0UTEGdGwPd4jf9uEhmueApFvoN8CU0fsuC2NRvuK5WC6QkMdPO', NULL, 'user', '2026-08-11 21:04:46'),
(3, 'SInta Marketing', 'sinta@mail.test', '$2y$10$1i2dgtX35bt0vEOWMqu4DOJC6YZgr3OPLTXWEreIF3.vIWaHh5tzq', '', 'admin', '2026-08-27 10:10:00');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_detail_transaksi`
-- (See below for the actual view)
--
CREATE TABLE `v_detail_transaksi` (
`alamat` varchar(255)
,`bukti_bayar` varchar(255)
,`catatan_admin` varchar(255)
,`durasi_sewa` int
,`email_pembeli` varchar(150)
,`judul_properti` varchar(150)
,`kota` varchar(100)
,`metode_bayar` enum('transfer_bank','cicilan_kpr','tunai','e-wallet','qris')
,`nama_pembeli` varchar(100)
,`no_hp_pembeli` varchar(20)
,`status` enum('menunggu','diproses','disetujui','ditolak','selesai','lunas')
,`tanggal_mulai` date
,`tanggal_selesai` date
,`tanggal_transaksi` timestamp
,`tipe_transaksi` enum('jual','sewa')
,`total_harga` decimal(15,2)
,`transaksi_id` int
);

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `properti_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`id`, `user_id`, `properti_id`, `created_at`) VALUES
(1, 1, 1, '2026-08-24 22:55:32'),
(4, 2, 23, '2026-09-10 04:15:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agen`
--
ALTER TABLE `agen`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `galeri_properti`
--
ALTER TABLE `galeri_properti`
  ADD PRIMARY KEY (`id`),
  ADD KEY `properti_id` (`properti_id`);

--
-- Indexes for table `pesan_kontak`
--
ALTER TABLE `pesan_kontak`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `properti`
--
ALTER TABLE `properti`
  ADD PRIMARY KEY (`id`),
  ADD KEY `agen_id` (`agen_id`),
  ADD KEY `idx_properti_transaksi` (`tipe_transaksi`,`status`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `properti_id` (`properti_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unik_wishlist` (`user_id`,`properti_id`),
  ADD KEY `properti_id` (`properti_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agen`
--
ALTER TABLE `agen`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `galeri_properti`
--
ALTER TABLE `galeri_properti`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pesan_kontak`
--
ALTER TABLE `pesan_kontak`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `properti`
--
ALTER TABLE `properti`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

-- --------------------------------------------------------

--
-- Structure for view `v_detail_transaksi`
--
DROP TABLE IF EXISTS `v_detail_transaksi`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_detail_transaksi`  AS SELECT `t`.`id` AS `transaksi_id`, `u`.`nama` AS `nama_pembeli`, `u`.`email` AS `email_pembeli`, `u`.`no_hp` AS `no_hp_pembeli`, `p`.`judul` AS `judul_properti`, `p`.`alamat` AS `alamat`, `p`.`kota` AS `kota`, `t`.`tipe_transaksi` AS `tipe_transaksi`, `t`.`durasi_sewa` AS `durasi_sewa`, `t`.`tanggal_mulai` AS `tanggal_mulai`, `t`.`tanggal_selesai` AS `tanggal_selesai`, `t`.`total_harga` AS `total_harga`, `t`.`metode_bayar` AS `metode_bayar`, `t`.`bukti_bayar` AS `bukti_bayar`, `t`.`status` AS `status`, `t`.`catatan_admin` AS `catatan_admin`, `t`.`created_at` AS `tanggal_transaksi` FROM ((`transaksi` `t` join `users` `u` on((`t`.`user_id` = `u`.`id`))) join `properti` `p` on((`t`.`properti_id` = `p`.`id`))) ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `galeri_properti`
--
ALTER TABLE `galeri_properti`
  ADD CONSTRAINT `galeri_properti_ibfk_1` FOREIGN KEY (`properti_id`) REFERENCES `properti` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `properti`
--
ALTER TABLE `properti`
  ADD CONSTRAINT `properti_ibfk_1` FOREIGN KEY (`agen_id`) REFERENCES `agen` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`properti_id`) REFERENCES `properti` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`properti_id`) REFERENCES `properti` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
