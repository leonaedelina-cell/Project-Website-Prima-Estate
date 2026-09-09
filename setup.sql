-- Database Setup File: estate_prima
-- Lengkap dengan fitur Jual/Sewa Properti, WhatsApp Integration Data, dan Triggers.

CREATE DATABASE IF NOT EXISTS `estate_prima`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `estate_prima`;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `no_hp` VARCHAR(20) DEFAULT NULL,
  `role` ENUM('admin', 'user') NOT NULL DEFAULT 'user',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Table structure for table `agen`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `agen` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `no_hp` VARCHAR(20) DEFAULT NULL,
  `email` VARCHAR(150) DEFAULT NULL,
  `foto_url` VARCHAR(500) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Table structure for table `properti`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `properti` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `judul` VARCHAR(150) NOT NULL,
  `deskripsi` TEXT,
  `harga` DECIMAL(15, 2) NOT NULL COMMENT 'Harga jual utama',
  `tipe_transaksi` ENUM('jual', 'sewa') NOT NULL DEFAULT 'jual',
  `durasi_minimal` ENUM('6 bulan', '1 tahun') DEFAULT NULL,
  `harga_sewa` DECIMAL(15, 2) DEFAULT NULL COMMENT 'Harga sewa per periode',
  `periode_sewa` ENUM('bulan', 'tahun') DEFAULT NULL,
  `minimal_sewa` INT DEFAULT 1,
  `tipe` ENUM('rumah', 'apartemen', 'tanah', 'ruko') NOT NULL DEFAULT 'rumah',
  `status` ENUM('tersedia', 'terjual') NOT NULL DEFAULT 'tersedia',
  `alamat` VARCHAR(255) NOT NULL,
  `kota` VARCHAR(100) NOT NULL,
  `lat` DECIMAL(10, 7) DEFAULT NULL,
  `lng` DECIMAL(10, 7) DEFAULT NULL,
  `luas_tanah` INT UNSIGNED DEFAULT NULL,
  `luas_bangunan` INT UNSIGNED DEFAULT NULL,
  `kamar_tidur` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `kamar_mandi` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `carport` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `fasilitas` TEXT DEFAULT NULL COMMENT 'Daftar fasilitas, dipisahkan koma',
  `status_hunian` ENUM('kosong', 'terisi') NOT NULL DEFAULT 'kosong',
  `gambar_url` VARCHAR(500) DEFAULT NULL,
  `agen_id` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_properti_agen` FOREIGN KEY (`agen_id`) REFERENCES `agen` (`id`) ON DELETE SET NULL,
  INDEX `idx_properti_transaksi` (`tipe_transaksi`, `status`),
  INDEX `idx_properti_tipe` (`tipe`),
  INDEX `idx_properti_kota` (`kota`)
) ENGINE=InnoDB;

ALTER TABLE `properti`
  ADD COLUMN IF NOT EXISTS `tipe_transaksi` ENUM('jual','sewa') NOT NULL DEFAULT 'jual' AFTER `harga`,
  ADD COLUMN IF NOT EXISTS `durasi_minimal` ENUM('6 bulan','1 tahun') DEFAULT NULL AFTER `tipe_transaksi`,
  ADD COLUMN IF NOT EXISTS `fasilitas` TEXT DEFAULT NULL AFTER `carport`,
  ADD COLUMN IF NOT EXISTS `status_hunian` ENUM('kosong','terisi') NOT NULL DEFAULT 'kosong' AFTER `fasilitas`;

-- --------------------------------------------------------
-- Table structure for table `galeri_properti`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `galeri_properti` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `properti_id` INT UNSIGNED NOT NULL,
  `gambar_url` VARCHAR(500) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_galeri_properti` FOREIGN KEY (`properti_id`) REFERENCES `properti` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Table structure for table `wishlist`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `wishlist` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `properti_id` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_wishlist_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_wishlist_properti` FOREIGN KEY (`properti_id`) REFERENCES `properti` (`id`) ON DELETE CASCADE,
  UNIQUE KEY `unik_wishlist` (`user_id`, `properti_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Table structure for table `transaksi`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `transaksi` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `properti_id` INT UNSIGNED NOT NULL,
  `tipe_transaksi` ENUM('jual', 'sewa') NOT NULL DEFAULT 'jual',
  `durasi_sewa` INT DEFAULT NULL,
  `tanggal_mulai` DATE DEFAULT NULL,
  `tanggal_selesai` DATE DEFAULT NULL,
  `total_harga` DECIMAL(15, 2) DEFAULT NULL,
  `metode_bayar` ENUM('transfer_bank', 'cicilan_kpr', 'tunai', 'e-wallet', 'qris') DEFAULT NULL,
  `bukti_bayar` VARCHAR(500) DEFAULT NULL,
  `status` ENUM('menunggu', 'diproses', 'disetujui', 'ditolak', 'selesai', 'lunas') NOT NULL DEFAULT 'menunggu',
  `catatan_admin` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_transaksi_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_transaksi_properti` FOREIGN KEY (`properti_id`) REFERENCES `properti` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Table structure for table `pesan_kontak`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pesan_kontak` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `no_hp` VARCHAR(20) DEFAULT NULL,
  `pesan` TEXT NOT NULL,
  `status_dibaca` ENUM('belum', 'sudah') NOT NULL DEFAULT 'belum',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- DATA SEEDING
-- --------------------------------------------------------
INSERT INTO `users` (`id`, `nama`, `email`, `password`, `no_hp`, `role`) VALUES
(1, 'Admin Estate Prima', 'admin@estateprima.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', '081234567890', 'admin'),
(2, 'Budi Santoso', 'budi@mail.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', '081298765432', 'user')
ON DUPLICATE KEY UPDATE `id`=`id`;

INSERT INTO `agen` (`id`, `nama`, `no_hp`, `email`) VALUES
(1, 'Sinta Marketing', '081234567890', 'sinta@estateprima.test'),
(2, 'Nci Marketing', '081345678910', 'nci@estateprima.test')
ON DUPLICATE KEY UPDATE `id`=`id`;

INSERT INTO `properti` (`id`, `judul`, `deskripsi`, `harga`, `tipe_transaksi`, `durasi_minimal`, `harga_sewa`, `periode_sewa`, `minimal_sewa`, `tipe`, `status`, `alamat`, `kota`, `luas_tanah`, `luas_bangunan`, `kamar_tidur`, `kamar_mandi`, `carport`, `fasilitas`, `status_hunian`, `gambar_url`, `agen_id`) VALUES
(1, 'Rumah Minimalis Green Valley', 'Rumah siap huni dekat area perkantoran.', 850000000.00, 'jual', NULL, NULL, NULL, 1, 'rumah', 'tersedia', 'Jl. Green Valley No. 12', 'Bekasi', 120, 90, 3, 2, 1, 'Taman, Garasi, Keamanan 24 Jam', 'terisi', 'https://images.unsplash.com/photo-1570129477492-45c003edd2be', 1),
(2, 'Apartemen Studio City View', 'Apartemen studio strategis.', 450000000.00, 'sewa', '6 bulan', 3500000.00, 'bulan', 6, 'apartemen', 'tersedia', 'Jl. Sudirman No. 5', 'Jakarta Selatan', NULL, 32, 1, 1, 0, 'Kolam Renang, Gym, Keamanan 24 Jam', 'kosong', 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688', 1),
(3, 'Rumah Sewa Nyaman Cempaka', 'Rumah nyaman untuk disewa dekat pusat kota.', 700000000.00, 'sewa', '1 tahun', 5000000.00, 'bulan', 12, 'rumah', 'tersedia', 'Jl. Cempaka No. 8', 'Depok', 100, 75, 2, 2, 1, 'Carport, Taman, Dapur Bersih', 'kosong', 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c', 2),
(4, 'Tanah Kavling Premium Sentosa', 'Kavling siap bangun di lokasi strategis.', 650000000.00, 'jual', NULL, NULL, NULL, 1, 'tanah', 'tersedia', 'Jl. Sentosa Raya No. 20', 'Bandung', 180, NULL, 0, 0, 0, 'Jalan Aspal, Air Bersih, Pemandangan', 'kosong', 'https://images.unsplash.com/photo-1500382017468-9049fed747ef', 2),
(5, 'Ruko 2 Lantai Strategis', 'Ruko siap operasional di pusat bisnis.', 1200000000.00, 'jual', NULL, NULL, NULL, 1, 'ruko', 'tersedia', 'Jl. Melati Indah No. 9', 'Surabaya', 90, 120, 0, 2, 2, 'Lokasi Strategis, Depan Jalan Utama, Parkir', 'terisi', 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab', 1),
(6, 'Villa Bali Tropis', 'Villa tropis dengan pemandangan alam dan fasilitas lengkap.', 2100000000.00, 'sewa', '1 tahun', 15000000.00, 'bulan', 12, 'rumah', 'tersedia', 'Jl. Ubud No. 88', 'Bali', 240, 180, 4, 3, 2, 'Kolam Renang, WiFi, Pantry, Taman', 'kosong', 'https://images.unsplash.com/photo-1494526585095-c41746248156', 2)
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Contoh properti sewa untuk pengujian filter dan detail.
UPDATE `properti` SET `tipe_transaksi` = 'sewa', `durasi_minimal` = '6 bulan',
    `harga_sewa` = COALESCE(`harga_sewa`, 3500000), `periode_sewa` = COALESCE(`periode_sewa`, 'bulan'),
    `minimal_sewa` = 6
WHERE `id` = 2;

UPDATE `properti` SET `tipe_transaksi` = 'sewa', `durasi_minimal` = '1 tahun',
    `minimal_sewa` = 12
WHERE `id` = 3;

UPDATE `properti` SET `tipe_transaksi` = 'sewa', `durasi_minimal` = '1 tahun',
    `harga_sewa` = 15000000, `periode_sewa` = 'bulan', `minimal_sewa` = 12
WHERE `id` = 6;