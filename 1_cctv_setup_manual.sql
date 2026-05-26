-- ==========================================================
-- MANUAL SETUP SCRIPT: PetaCCTV MODULE FOR OpenSID
-- ==========================================================
-- Run this SQL script in your database manager (e.g. phpMyAdmin, DBeaver)
-- if you are NOT using "php artisan migrate".

-- 1. Create cctv_categories Table
CREATE TABLE IF NOT EXISTS `cctv_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `config_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cctv_categories_config_id_foreign` (`config_id`),
  CONSTRAINT `cctv_categories_config_id_foreign` FOREIGN KEY (`config_id`) REFERENCES `config` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- 2. Create cctv_cameras Table
CREATE TABLE IF NOT EXISTS `cctv_cameras` (
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
  KEY `cctv_cameras_config_id_foreign` (`config_id`),
  KEY `cctv_cameras_category_fk` (`category_id`),
  CONSTRAINT `cctv_cameras_category_fk` FOREIGN KEY (`category_id`) REFERENCES `cctv_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `cctv_cameras_config_id_foreign` FOREIGN KEY (`config_id`) REFERENCES `config` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- 3. Seed Default Categories (Assume config_id = 1)
INSERT INTO `cctv_categories` (`id`, `config_id`, `name`, `created_at`, `updated_at`) VALUES
(1, 1, 'Kantor Desa', NOW(), NOW()),
(2, 1, 'Sekolah', NOW(), NOW()),
(3, 1, 'Masjid/Mushola', NOW(), NOW()),
(4, 1, 'Jalan Raya', NOW(), NOW()),
(5, 1, 'Wisata', NOW(), NOW()),
(6, 1, 'Pasar', NOW(), NOW()),
(7, 1, 'Parkir', NOW(), NOW()),
(8, 1, 'Sungai', NOW(), NOW())
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);


-- 4. Seed Sample Active Cameras
INSERT INTO `cctv_cameras` (`config_id`, `name`, `description`, `latitude`, `longitude`, `stream_url`, `stream_type`, `category_id`, `is_public`, `is_active`, `status`, `last_online_at`, `created_at`, `updated_at`)
SELECT 1, 'Simpang Kantor Desa', 'Kamera pengawas persimpangan jalan utama depan Kantor Desa Perwira.', -7.38204600, 109.36440600, 'https://test-streams.mux.dev/x36xhzz/x36xhzz.m3u8', 'hls', 1, 1, 1, 'online', NOW(), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `cctv_cameras` WHERE `name` = 'Simpang Kantor Desa' AND `config_id` = 1);

INSERT INTO `cctv_cameras` (`config_id`, `name`, `description`, `latitude`, `longitude`, `stream_url`, `stream_type`, `category_id`, `is_public`, `is_active`, `status`, `last_online_at`, `created_at`, `updated_at`)
SELECT 1, 'Live Alun-alun Purbalingga (YouTube)', 'Uji coba livestream YouTube kawasan publik Kabupaten.', -7.38888000, 109.36111000, 'https://www.youtube.com/embed/jfKfPfyJRdk', 'youtube', 3, 1, 1, 'online', NOW(), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `cctv_cameras` WHERE `name` = 'Live Alun-alun Purbalingga (YouTube)' AND `config_id` = 1);


-- 5. Register Sidebar Menu in setting_modul
-- 5.1 Add Parent Menu "Peta CCTV"
INSERT INTO `setting_modul` (`config_id`, `modul`, `slug`, `url`, `aktif`, `ikon`, `urut`, `level`, `hidden`, `ikon_kecil`, `parent`)
SELECT 1, 'Peta CCTV', 'cctv', '', 1, 'fa-video-camera', 190, 0, 0, 'fa-video-camera', 0
WHERE NOT EXISTS (SELECT 1 FROM `setting_modul` WHERE `slug` = 'cctv' AND `config_id` = 1);

-- 5.2 Add Sub-menu "Kamera CCTV"
INSERT INTO `setting_modul` (`config_id`, `modul`, `slug`, `url`, `aktif`, `ikon`, `urut`, `level`, `hidden`, `ikon_kecil`, `parent`)
SELECT 1, 'Kamera CCTV', 'cctv-admin', 'cctv_admin', 1, 'fa-video-camera', 1, 0, 0, 'fa-video-camera', (SELECT `id` FROM `setting_modul` WHERE `slug` = 'cctv' AND `config_id` = 1 LIMIT 1)
WHERE NOT EXISTS (SELECT 1 FROM `setting_modul` WHERE `slug` = 'cctv-admin' AND `config_id` = 1);

-- 5.3 Add Sub-menu "Kategori CCTV"
INSERT INTO `setting_modul` (`config_id`, `modul`, `slug`, `url`, `aktif`, `ikon`, `urut`, `level`, `hidden`, `ikon_kecil`, `parent`)
SELECT 1, 'Kategori CCTV', 'cctv-category', 'cctv_category', 1, 'fa-tags', 2, 0, 0, 'fa-tags', (SELECT `id` FROM `setting_modul` WHERE `slug` = 'cctv' AND `config_id` = 1 LIMIT 1)
WHERE NOT EXISTS (SELECT 1 FROM `setting_modul` WHERE `slug` = 'cctv-category' AND `config_id` = 1);


-- 6. Grant Permissions to Administrator Group (Group ID = 1)
-- Parent Menu Permission
INSERT INTO `grup_akses` (`config_id`, `id_grup`, `id_modul`, `akses`)
SELECT 1, 1, `id`, 7 FROM `setting_modul` WHERE `slug` = 'cctv' AND `config_id` = 1
ON DUPLICATE KEY UPDATE `akses` = 7;

-- Submenus Permission
INSERT INTO `grup_akses` (`config_id`, `id_grup`, `id_modul`, `akses`)
SELECT 1, 1, `id`, 7 FROM `setting_modul` 
WHERE `slug` IN ('cctv-admin', 'cctv-category') 
AND `config_id` = 1
ON DUPLICATE KEY UPDATE `akses` = 7;
