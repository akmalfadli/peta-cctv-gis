-- ==========================================================
-- MANUAL SETUP SCRIPT: PetaGIS MODULE FOR OpenSID
-- ==========================================================
-- Run this SQL script in your database manager (e.g. phpMyAdmin, DBeaver)
-- if you are NOT using "php artisan migrate".

-- 1. Create gis_categories Table
CREATE TABLE IF NOT EXISTS `gis_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `config_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `icon` varchar(100) NOT NULL DEFAULT 'fa-video',
  `color` varchar(50) NOT NULL DEFAULT '#10b981',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `gis_categories_config_id_foreign` (`config_id`),
  CONSTRAINT `gis_categories_config_id_foreign` FOREIGN KEY (`config_id`) REFERENCES `config` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- 2. Create gis_cameras Table
CREATE TABLE IF NOT EXISTS `gis_cameras` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `config_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `stream_url` text NOT NULL,
  `stream_type` varchar(50) NOT NULL COMMENT 'hls, youtube, iframe',
  `thumbnail` varchar(255) DEFAULT NULL,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `is_public` tinyint(4) NOT NULL DEFAULT 1,
  `is_active` tinyint(4) NOT NULL DEFAULT 1,
  `status` enum('online','offline') NOT NULL DEFAULT 'online',
  `last_online_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `gis_cameras_config_id_foreign` (`config_id`),
  KEY `gis_cameras_category_fk` (`category_id`),
  CONSTRAINT `gis_cameras_category_fk` FOREIGN KEY (`category_id`) REFERENCES `gis_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `gis_cameras_config_id_foreign` FOREIGN KEY (`config_id`) REFERENCES `config` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- 3. Create gis_pembangunans Table
CREATE TABLE IF NOT EXISTS `gis_pembangunans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `config_id` int(11) DEFAULT NULL,
  `jenis_kegiatan` varchar(255) NOT NULL,
  `kategori` varchar(50) NOT NULL DEFAULT 'infrastruktur',
  `tahun_anggaran` varchar(20) DEFAULT NULL,
  `sumber_dana` varchar(255) DEFAULT NULL,
  `anggaran` decimal(15,2) DEFAULT NULL,
  `lokasi` varchar(255) DEFAULT NULL,
  `volume` varchar(255) DEFAULT NULL,
  `pelaksana` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `type` enum('road','building') NOT NULL DEFAULT 'building',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `coordinates` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `gis_pembangunans_config_id_foreign` (`config_id`),
  CONSTRAINT `gis_pembangunans_config_id_foreign` FOREIGN KEY (`config_id`) REFERENCES `config` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- 4. Seed Default Categories (Assume config_id = 1)
INSERT INTO `gis_categories` (`id`, `config_id`, `name`, `icon`, `color`, `created_at`, `updated_at`) VALUES
(1, 1, 'Kantor Desa', 'fa-building', '#475569', NOW(), NOW()),
(2, 1, 'Sekolah', 'fa-graduation-cap', '#3b82f6', NOW(), NOW()),
(3, 1, 'Masjid/Mushola', 'fa-mosque', '#0d9488', NOW(), NOW()),
(4, 1, 'Jalan Raya', 'fa-road', '#ea580c', NOW(), NOW()),
(5, 1, 'Wisata', 'fa-mountain-sun', '#db2777', NOW(), NOW()),
(6, 1, 'Pasar', 'fa-store', '#ea580c', NOW(), NOW()),
(7, 1, 'Parkir', 'fa-square-parking', '#64748b', NOW(), NOW()),
(8, 1, 'Sungai', 'fa-water', '#06b6d4', NOW(), NOW())
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `icon` = VALUES(`icon`), `color` = VALUES(`color`);


-- 5. Seed Sample Active Cameras
INSERT INTO `gis_cameras` (`config_id`, `name`, `description`, `latitude`, `longitude`, `stream_url`, `stream_type`, `category_id`, `is_public`, `is_active`, `status`, `last_online_at`, `created_at`, `updated_at`)
SELECT 1, 'Simpang Kantor Desa', 'Kamera pengawas persimpangan jalan utama depan Kantor Desa Perwira.', -7.38204600, 109.36440600, 'https://test-streams.mux.dev/x36xhzz/x36xhzz.m3u8', 'hls', 1, 1, 1, 'online', NOW(), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `gis_cameras` WHERE `name` = 'Simpang Kantor Desa' AND `config_id` = 1);

INSERT INTO `gis_cameras` (`config_id`, `name`, `description`, `latitude`, `longitude`, `stream_url`, `stream_type`, `category_id`, `is_public`, `is_active`, `status`, `last_online_at`, `created_at`, `updated_at`)
SELECT 1, 'Kawasan Wisata Desa', 'Kamera pemantauan arus wisatawan pada pintu masuk agrowisata.', -7.38888000, 109.36111000, 'https://www.youtube.com/embed/jfKfPfyJRdk', 'youtube', 5, 1, 1, 'online', NOW(), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `gis_cameras` WHERE `name` = 'Kawasan Wisata Desa' AND `config_id` = 1);


-- 5.2 Seed Sample Pembangunans (Village Development Projects)
INSERT INTO `gis_pembangunans` (`config_id`, `jenis_kegiatan`, `kategori`, `tahun_anggaran`, `sumber_dana`, `anggaran`, `lokasi`, `volume`, `pelaksana`, `type`, `latitude`, `longitude`, `coordinates`, `created_at`, `updated_at`)
SELECT 1, 'Pembangunan Gedung PAUD Tunas Bangsa', 'pendidikan', '2026', 'Dana Desa (DD)', 125000000.00, 'RT 01 RW 03 Dusun I', '1 Unit (8m x 12m)', 'Tim Pelaksana Kegiatan (TPK) Desa', 'building', -7.38312000, 109.36551000, NULL, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `gis_pembangunans` WHERE `jenis_kegiatan` = 'Pembangunan Gedung PAUD Tunas Bangsa' AND `config_id` = 1);

INSERT INTO `gis_pembangunans` (`config_id`, `jenis_kegiatan`, `kategori`, `tahun_anggaran`, `sumber_dana`, `anggaran`, `lokasi`, `volume`, `pelaksana`, `type`, `latitude`, `longitude`, `coordinates`, `created_at`, `updated_at`)
SELECT 1, 'Pengaspalan Jalan Lingkungan Dusun II', 'infrastruktur', '2026', 'Alokasi Dana Desa (ADD)', 185000000.00, 'Dusun II (RT 03 ke RT 05)', 'P 550m x L 3m', 'Lembaga Pemberdayaan Masyarakat (LPM)', 'road', NULL, NULL, '[[-7.382046,109.364406],[-7.381200,109.364800],[-7.380500,109.365300],[-7.379800,109.366000]]', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `gis_pembangunans` WHERE `jenis_kegiatan` = 'Pengaspalan Jalan Lingkungan Dusun II' AND `config_id` = 1);

INSERT INTO `gis_pembangunans` (`config_id`, `jenis_kegiatan`, `kategori`, `tahun_anggaran`, `sumber_dana`, `anggaran`, `lokasi`, `volume`, `pelaksana`, `type`, `latitude`, `longitude`, `coordinates`, `created_at`, `updated_at`)
SELECT 1, 'Pembangunan Gedung Posyandu Melati', 'kesehatan', '2026', 'Dana Desa (DD)', 95000000.00, 'RT 04 RW 02 Dusun II', '1 Unit (6m x 8m)', 'Tim Pelaksana Kegiatan (TPK)', 'building', -7.38550000, 109.36220000, NULL, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `gis_pembangunans` WHERE `jenis_kegiatan` = 'Pembangunan Gedung Posyandu Melati' AND `config_id` = 1);

INSERT INTO `gis_pembangunans` (`config_id`, `jenis_kegiatan`, `kategori`, `tahun_anggaran`, `sumber_dana`, `anggaran`, `lokasi`, `volume`, `pelaksana`, `type`, `latitude`, `longitude`, `coordinates`, `created_at`, `updated_at`)
SELECT 1, 'Penyertaan Modal & Kios BUMDes Perwira', 'ekonomi', '2026', 'Bagi Hasil Pajak & Retribusi', 150000000.00, 'Kawasan Pasar Desa Perwira', '5 Kios Baru', 'Pengurus BUMDes Karya Perwira', 'building', -7.38000000, 109.36000000, NULL, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `gis_pembangunans` WHERE `jenis_kegiatan` = 'Penyertaan Modal & Kios BUMDes Perwira' AND `config_id` = 1);

INSERT INTO `gis_pembangunans` (`config_id`, `jenis_kegiatan`, `kategori`, `tahun_anggaran`, `sumber_dana`, `anggaran`, `lokasi`, `volume`, `pelaksana`, `type`, `latitude`, `longitude`, `coordinates`, `created_at`, `updated_at`)
SELECT 1, 'Pembangunan Talud & Saluran Drainase Mitigasi Banjir', 'lingkungan', '2026', 'Dana Desa (DD)', 110000000.00, 'Bantaran Sungai Dusun III', 'Panjang 250 meter', 'Tim Pelaksana Kegiatan (TPK)', 'road', NULL, NULL, '[[-7.387000,109.366000],[-7.388000,109.367000],[-7.389000,109.368000]]', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `gis_pembangunans` WHERE `jenis_kegiatan` = 'Pembangunan Talud & Saluran Drainase Mitigasi Banjir' AND `config_id` = 1);


-- 6. Register Sidebar Menu in setting_modul
-- 6.1 Add Parent Menu "Peta GIS"
INSERT INTO `setting_modul` (`config_id`, `modul`, `slug`, `url`, `aktif`, `ikon`, `urut`, `level`, `hidden`, `ikon_kecil`, `parent`)
SELECT 1, 'Peta GIS', 'gis', '', 1, 'fa-map', 190, 0, 0, 'fa-map', 0
WHERE NOT EXISTS (SELECT 1 FROM `setting_modul` WHERE `slug` = 'gis' AND `config_id` = 1);

-- 6.2 Add Sub-menu "Kamera CCTV"
INSERT INTO `setting_modul` (`config_id`, `modul`, `slug`, `url`, `aktif`, `ikon`, `urut`, `level`, `hidden`, `ikon_kecil`, `parent`)
SELECT 1, 'Kamera CCTV', 'gis-camera', 'gis_camera', 1, 'fa-video-camera', 1, 0, 0, 'fa-video-camera', (SELECT `id` FROM `setting_modul` WHERE `slug` = 'gis' AND `config_id` = 1 LIMIT 1)
WHERE NOT EXISTS (SELECT 1 FROM `setting_modul` WHERE `slug` = 'gis-camera' AND `config_id` = 1);

-- 6.3 Add Sub-menu "Pembangunan Desa"
INSERT INTO `setting_modul` (`config_id`, `modul`, `slug`, `url`, `aktif`, `ikon`, `urut`, `level`, `hidden`, `ikon_kecil`, `parent`)
SELECT 1, 'Pembangunan Desa', 'gis-pembangunan', 'gis_pembangunan', 1, 'fa-road', 2, 0, 0, 'fa-road', (SELECT `id` FROM `setting_modul` WHERE `slug` = 'gis' AND `config_id` = 1 LIMIT 1)
WHERE NOT EXISTS (SELECT 1 FROM `setting_modul` WHERE `slug` = 'gis-pembangunan' AND `config_id` = 1);

-- 6.4 Add Sub-menu "Kategori GIS"
INSERT INTO `setting_modul` (`config_id`, `modul`, `slug`, `url`, `aktif`, `ikon`, `urut`, `level`, `hidden`, `ikon_kecil`, `parent`)
SELECT 1, 'Kategori GIS', 'gis-category', 'gis_category', 1, 'fa-tags', 3, 0, 0, 'fa-tags', (SELECT `id` FROM `setting_modul` WHERE `slug` = 'gis' AND `config_id` = 1 LIMIT 1)
WHERE NOT EXISTS (SELECT 1 FROM `setting_modul` WHERE `slug` = 'gis-category' AND `config_id` = 1);


-- 7. Grant Permissions to Administrator Group (Group ID = 1)
-- Parent Menu Permission
INSERT INTO `grup_akses` (`config_id`, `id_grup`, `id_modul`, `akses`)
SELECT 1, 1, `id`, 7 FROM `setting_modul` WHERE `slug` = 'gis' AND `config_id` = 1
ON DUPLICATE KEY UPDATE `akses` = 7;

-- Submenus Permission
INSERT INTO `grup_akses` (`config_id`, `id_grup`, `id_modul`, `akses`)
SELECT 1, 1, `id`, 7 FROM `setting_modul` 
WHERE `slug` IN ('gis-camera', 'gis-pembangunan', 'gis-category') 
AND `config_id` = 1
ON DUPLICATE KEY UPDATE `akses` = 7;
