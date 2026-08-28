-- Estate Prima: compatibility schema for the submitted PHP files.
-- Import this file in phpMyAdmin (SQL tab), then configure config/database.php
-- to use the database name below.
--
-- Seeded accounts (both use password: password):
--   admin@estateprima.test    role: admin
--   pelanggan@estateprima.test role: user

CREATE DATABASE IF NOT EXISTS estate_prima
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE estate_prima;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    no_hp VARCHAR(20) NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE agen (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    no_hp VARCHAR(20) NULL,
    email VARCHAR(150) NULL,
    foto_url VARCHAR(500) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE properti (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(150) NOT NULL,
    deskripsi TEXT NULL,
    harga DECIMAL(15, 2) NOT NULL,
    tipe ENUM('rumah', 'apartemen', 'tanah', 'ruko') NOT NULL DEFAULT 'rumah',
    status ENUM('tersedia', 'terjual') NOT NULL DEFAULT 'tersedia',
    alamat VARCHAR(255) NOT NULL,
    kota VARCHAR(100) NOT NULL,
    lat DECIMAL(10, 7) NULL,
    lng DECIMAL(10, 7) NULL,
    luas_tanah INT UNSIGNED NULL,
    luas_bangunan INT UNSIGNED NULL,
    kamar_tidur TINYINT UNSIGNED NOT NULL DEFAULT 0,
    kamar_mandi TINYINT UNSIGNED NOT NULL DEFAULT 0,
    carport TINYINT UNSIGNED NOT NULL DEFAULT 0,
    gambar_url VARCHAR(500) NULL,
    agen_id INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_properti_agen
        FOREIGN KEY (agen_id) REFERENCES agen(id)
        ON DELETE SET NULL,
    INDEX idx_properti_status_created (status, created_at),
    INDEX idx_properti_tipe (tipe),
    INDEX idx_properti_kota (kota)
) ENGINE=InnoDB;

CREATE TABLE galeri_properti (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    properti_id INT UNSIGNED NOT NULL,
    gambar_url VARCHAR(500) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_galeri_properti
        FOREIGN KEY (properti_id) REFERENCES properti(id)
        ON DELETE CASCADE,
    INDEX idx_galeri_properti_id (properti_id)
) ENGINE=InnoDB;

CREATE TABLE wishlist (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    properti_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_wishlist_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_wishlist_properti
        FOREIGN KEY (properti_id) REFERENCES properti(id)
        ON DELETE CASCADE,
    UNIQUE KEY unik_wishlist (user_id, properti_id)
) ENGINE=InnoDB;

CREATE TABLE transaksi (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    properti_id INT UNSIGNED NOT NULL,
    metode_bayar ENUM('transfer_bank', 'cicilan_kpr', 'tunai') NULL,
    bukti_bayar VARCHAR(500) NULL,
    status ENUM('menunggu', 'diproses', 'disetujui', 'ditolak', 'selesai')
        NOT NULL DEFAULT 'menunggu',
    catatan_admin TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_transaksi_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_transaksi_properti
        FOREIGN KEY (properti_id) REFERENCES properti(id)
        ON DELETE CASCADE,
    INDEX idx_transaksi_user (user_id),
    INDEX idx_transaksi_status_created (status, created_at)
) ENGINE=InnoDB;

CREATE TABLE pesan_kontak (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    no_hp VARCHAR(20) NULL,
    pesan TEXT NOT NULL,
    status_dibaca ENUM('belum', 'sudah') NOT NULL DEFAULT 'belum',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pesan_status_created (status_dibaca, created_at)
) ENGINE=InnoDB;

-- The hash below is compatible with password_verify('password', $hash).
INSERT INTO users (nama, email, password, no_hp, role) VALUES
('Administrator Estate Prima', 'admin@estateprima.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', '081234567890', 'admin'),
('Dewi Lestari', 'pelanggan@estateprima.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', '081298765432', 'user');

INSERT INTO agen (nama, no_hp, email, foto_url) VALUES
('Andi Pratama', '081212345678', 'andi@estateprima.test', 'https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=400&q=80'),
('Siti Rahma', '081298765431', 'siti@estateprima.test', 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=400&q=80'),
('Budi Santoso', '081376543210', 'budi@estateprima.test', 'https://images.unsplash.com/photo-1568602471122-7832951cc4c5?auto=format&fit=crop&w=400&q=80');

INSERT INTO properti
    (judul, deskripsi, harga, tipe, status, alamat, kota, lat, lng,
     luas_tanah, luas_bangunan, kamar_tidur, kamar_mandi, carport, gambar_url, agen_id)
VALUES
('Rumah Modern Green Valley', 'Rumah dua lantai dengan pencahayaan alami, lingkungan tenang, dan akses mudah ke pusat kota.', 1850000000, 'rumah', 'tersedia', 'Jl. Green Valley No. 18', 'Jakarta Selatan', -6.2614920, 106.8106000, 120, 180, 4, 3, 2, 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1200&q=80', 1),
('Apartemen Skyline Residence', 'Apartemen modern dekat perkantoran dan transportasi umum, cocok untuk profesional muda.', 980000000, 'apartemen', 'tersedia', 'Jl. Jenderal Sudirman Kav. 45', 'Jakarta Pusat', -6.2146200, 106.8229300, 45, 45, 2, 1, 1, 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=1200&q=80', 2),
('Rumah Keluarga Citra Garden', 'Hunian nyaman dengan halaman luas, dekat sekolah dan pusat belanja.', 1450000000, 'rumah', 'tersedia', 'Jl. Citra Garden Blok C No. 12', 'Bekasi', -6.2382700, 106.9755700, 144, 130, 3, 2, 2, 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?auto=format&fit=crop&w=1200&q=80', 1),
('Tanah Strategis Sentul', 'Lahan datar di kawasan berkembang, ideal untuk villa atau investasi jangka panjang.', 875000000, 'tanah', 'tersedia', 'Jl. Raya Sentul KM 7', 'Bogor', -6.5648000, 106.8505000, 500, NULL, 0, 0, 0, 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1200&q=80', 3),
('Ruko Niaga Kemang', 'Ruko tiga lantai di area komersial ramai, siap digunakan untuk usaha atau kantor.', 3200000000, 'ruko', 'tersedia', 'Jl. Kemang Raya No. 72', 'Jakarta Selatan', -6.2604800, 106.8136500, 80, 210, 0, 3, 3, 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1200&q=80', 2),
('Rumah Minimalis Alam Sutera', 'Rumah minimalis siap huni di cluster dengan keamanan 24 jam dan taman lingkungan.', 2100000000, 'rumah', 'tersedia', 'Jl. Alam Sutera Boulevard No. 9', 'Tangerang', -6.2400700, 106.6576900, 136, 160, 4, 3, 2, 'https://images.unsplash.com/photo-1600047509807-ba8f99d2cdde?auto=format&fit=crop&w=1200&q=80', 3),
('Apartemen City View Bandung', 'Unit apartemen dengan balkon dan pemandangan kota, dekat kampus dan fasilitas umum.', 720000000, 'apartemen', 'tersedia', 'Jl. Pasteur No. 28', 'Bandung', -6.8915200, 107.5980500, 38, 38, 1, 1, 1, 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=1200&q=80', 2),
('Rumah Klasik Pondok Indah', 'Rumah besar dengan taman depan dan ruang keluarga luas di kawasan premium.', 5600000000, 'rumah', 'terjual', 'Jl. Metro Pondok Indah No. 30', 'Jakarta Selatan', -6.2651100, 106.7814300, 350, 420, 5, 4, 3, 'https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?auto=format&fit=crop&w=1200&q=80', 1),
('Villa Pinus Riverside', 'Villa asri dengan suasana sejuk, halaman luas, dan akses langsung menuju aliran sungai.', 2750000000, 'rumah', 'tersedia', 'Jl. Puncak Raya No. 88', 'Bogor', -6.7021000, 106.9502000, 300, 220, 4, 3, 2, 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?auto=format&fit=crop&w=1200&q=80', 3),
('Rumah Tropis Bintaro', 'Hunian tropis dengan taman belakang, ruang keluarga terbuka, dan lingkungan yang nyaman.', 2350000000, 'rumah', 'tersedia', 'Jl. Bintaro Utama No. 16', 'Tangerang Selatan', -6.2787000, 106.7176000, 150, 185, 4, 3, 2, 'https://images.unsplash.com/photo-1600585154526-990dced4db0d?auto=format&fit=crop&w=1200&q=80', 1),
('Apartemen Garden Heights', 'Apartemen modern dengan area hijau, fasilitas lengkap, dan akses mudah ke pusat kota.', 1250000000, 'apartemen', 'tersedia', 'Jl. Gatot Subroto Kav. 21', 'Jakarta Selatan', -6.2297000, 106.8216000, 56, 56, 2, 1, 1, 'https://images.unsplash.com/photo-1502672023488-70e25813eb80?auto=format&fit=crop&w=1200&q=80', 2),
('Apartemen Marina Bay View', 'Unit apartemen nyaman dengan pemandangan laut, balkon pribadi, dan keamanan 24 jam.', 1680000000, 'apartemen', 'tersedia', 'Jl. Pantai Indah Kapuk No. 5', 'Jakarta Utara', -6.1074000, 106.7401000, 72, 72, 2, 2, 1, 'https://images.unsplash.com/photo-1493809842364-78817add7ffb?auto=format&fit=crop&w=1200&q=80', 2),
('Villa Senja Ubud', 'Villa bergaya Bali dengan kolam renang pribadi dan pemandangan hijau yang menenangkan.', 3200000000, 'rumah', 'tersedia', 'Jl. Raya Tegallalang No. 12', 'Ubud', -8.4312000, 115.2797000, 420, 260, 3, 3, 2, 'https://images.unsplash.com/photo-1582268611958-ebfd161ef9cf?auto=format&fit=crop&w=1200&q=80', 3),
('Rumah Scandinavian Depok', 'Rumah minimalis bergaya Scandinavian dengan tata ruang efisien dan pencahayaan alami.', 1350000000, 'rumah', 'tersedia', 'Jl. Margonda Raya No. 42', 'Depok', -6.3907000, 106.8246000, 110, 130, 3, 2, 1, 'https://images.unsplash.com/photo-1600047509807-ba8f99d2cdde?auto=format&fit=crop&w=1200&q=80', 1),
('Apartemen Central Park Suite', 'Apartemen premium dekat pusat perbelanjaan, perkantoran, dan transportasi umum.', 1950000000, 'apartemen', 'tersedia', 'Jl. Letjen S. Parman Kav. 28', 'Jakarta Barat', -6.1776000, 106.7908000, 68, 68, 2, 2, 1, 'https://images.unsplash.com/photo-1540518614846-7eded433c457?auto=format&fit=crop&w=1200&q=80', 2),
('Rumah Keluarga Cibubur', 'Hunian keluarga dengan carport luas, taman depan, dan akses dekat sekolah.', 1600000000, 'rumah', 'tersedia', 'Jl. Alternatif Cibubur No. 27', 'Bekasi', -6.3683000, 106.9067000, 180, 150, 4, 3, 2, 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=1200&q=80', 1),
('Apartemen Braga Heritage', 'Apartemen bergaya elegan di kawasan bersejarah dengan akses dekat kuliner dan hiburan.', 890000000, 'apartemen', 'tersedia', 'Jl. Braga No. 10', 'Bandung', -6.9175000, 107.6098000, 48, 48, 2, 1, 1, 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=1200&q=80', 2),
('Villa Lembah Salak', 'Villa luas untuk tempat tinggal atau liburan dengan udara sejuk dan panorama pegunungan.', 2450000000, 'rumah', 'tersedia', 'Jl. Ciapus No. 7', 'Bogor', -6.6865000, 106.7569000, 360, 240, 4, 3, 2, 'https://images.unsplash.com/photo-1602343168117-bb8ffe3e2e9f?auto=format&fit=crop&w=1200&q=80', 3),
('Apartemen Kemang Residence', 'Unit apartemen nyaman di kawasan lifestyle dengan fasilitas gym dan kolam renang.', 1100000000, 'apartemen', 'tersedia', 'Jl. Kemang Raya No. 99', 'Jakarta Selatan', -6.2609000, 106.8144000, 52, 52, 2, 1, 1, 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=1200&q=80', 2),
('Rumah Modern Kota Baru', 'Rumah modern siap huni dengan ruang kerja, taman kecil, dan keamanan lingkungan.', 1780000000, 'rumah', 'tersedia', 'Jl. Kota Baru Parahyangan No. 20', 'Bandung Barat', -6.8596000, 107.4758000, 140, 165, 3, 2, 2, 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&w=1200&q=80', 1);

INSERT INTO galeri_properti (properti_id, gambar_url) VALUES
(1, 'https://images.unsplash.com/photo-1600585152915-d208bec867a1?auto=format&fit=crop&w=1200&q=80'),
(1, 'https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?auto=format&fit=crop&w=1200&q=80'),
(1, 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&w=1200&q=80'),
(2, 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=1200&q=80'),
(6, 'https://images.unsplash.com/photo-1600573472592-401b489a3cdc?auto=format&fit=crop&w=1200&q=80');

INSERT INTO wishlist (user_id, properti_id) VALUES
(2, 1),
(2, 3);

INSERT INTO transaksi (user_id, properti_id, metode_bayar, status, catatan_admin) VALUES
(2, 8, 'transfer_bank', 'selesai', 'Pembayaran telah diterima dan transaksi selesai.');

INSERT INTO pesan_kontak (nama, email, no_hp, pesan, status_dibaca) VALUES
('Rina Putri', 'rina@example.com', '081355511122', 'Saya ingin menjadwalkan kunjungan untuk Rumah Modern Green Valley.', 'belum');
