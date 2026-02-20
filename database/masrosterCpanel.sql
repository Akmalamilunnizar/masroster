-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for masroster

-- Dumping structure for table masroster.addresses
CREATE TABLE IF NOT EXISTS `addresses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `recipient_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `phone_number` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `city` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `postal_code` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `full_address` text COLLATE utf8mb4_general_ci NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `addresses_user_id_foreign` (`user_id`),
  CONSTRAINT `addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table masroster.addresses: ~2 rows (approximately)
INSERT INTO `addresses` (`id`, `user_id`, `label`, `recipient_name`, `phone_number`, `city`, `postal_code`, `full_address`, `is_default`, `created_at`, `updated_at`) VALUES
	(1, 4, 'awd', 'Ahmad Muzakki', '19272342', 'Malang', 'Talok', 'Talok Malang Kecamatan Malang', 1, '2025-05-18 06:48:43', '2025-05-31 00:02:12'),
	(2, 4, 'Jember', 'Alan', '081238288', 'Jember', '190237', 'Jalan Kamilantan', 1, '2025-05-23 15:00:02', '2025-05-31 00:02:12');

-- Dumping structure for table masroster.barangkeluar
CREATE TABLE IF NOT EXISTS `barangkeluar` (
  `IdKeluar` varchar(6) COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tglKeluar` date DEFAULT NULL,
  PRIMARY KEY (`IdKeluar`),
  KEY `username` (`username`),
  CONSTRAINT `barangkeluar_ibfk_1` FOREIGN KEY (`username`) REFERENCES `users` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table masroster.barangkeluar: ~0 rows (approximately)

-- Dumping structure for table masroster.barangmasuk
CREATE TABLE IF NOT EXISTS `barangmasuk` (
  `IdMasuk` varchar(6) COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `tglMasuk` date DEFAULT NULL,
  PRIMARY KEY (`IdMasuk`),
  KEY `username` (`username`),
  CONSTRAINT `barangmasuk_ibfk_1` FOREIGN KEY (`username`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table masroster.barangmasuk: ~0 rows (approximately)

-- Dumping structure for table masroster.detail_barangkeluar
CREATE TABLE IF NOT EXISTS `detail_barangkeluar` (
  `IdKeluar` varchar(6) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `IdRoster` varchar(13) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `QtyKeluar` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `IdKeluar` (`IdKeluar`),
  KEY `IdBarang` (`IdRoster`) USING BTREE,
  CONSTRAINT `detail_barangkeluar_ibfk_2` FOREIGN KEY (`IdKeluar`) REFERENCES `barangkeluar` (`IdKeluar`),
  CONSTRAINT `detail_barangkeluar_ibfk_3` FOREIGN KEY (`IdRoster`) REFERENCES `produk` (`IdRoster`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table masroster.detail_barangkeluar: ~0 rows (approximately)

-- Dumping structure for table masroster.detail_barangmasuk
CREATE TABLE IF NOT EXISTS `detail_barangmasuk` (
  `IdMasuk` varchar(6) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `IdRoster` varchar(13) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `QtyMasuk` int DEFAULT NULL,
  `HargaSatuan` int NOT NULL,
  `SubTotal` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `IdMasuk` (`IdMasuk`),
  KEY `IdBarang` (`IdRoster`) USING BTREE,
  CONSTRAINT `detail_barangmasuk_ibfk_2` FOREIGN KEY (`IdMasuk`) REFERENCES `barangmasuk` (`IdMasuk`),
  CONSTRAINT `detail_barangmasuk_ibfk_4` FOREIGN KEY (`IdRoster`) REFERENCES `produk` (`IdRoster`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table masroster.detail_barangmasuk: ~0 rows (approximately)

-- Dumping structure for table masroster.detail_harga
CREATE TABLE IF NOT EXISTS `detail_harga` (
  `id_roster` varchar(13) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `id_user` bigint NOT NULL DEFAULT (0),
  `id_ukuran` int NOT NULL,
  `harga` int NOT NULL,
  KEY `Index 1` (`id_roster`,`id_user`) USING BTREE,
  KEY `detail_harga_id_ukuran_foreign` (`id_ukuran`),
  CONSTRAINT `detail_harga_id_ukuran_foreign` FOREIGN KEY (`id_ukuran`) REFERENCES `size` (`id_ukuran`) ON DELETE CASCADE,
  CONSTRAINT `FK_detail_harga_produk` FOREIGN KEY (`id_roster`) REFERENCES `produk` (`IdRoster`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='membedakan harga jual tiap transaksi setiap toko';

-- Dumping data for table masroster.detail_harga: ~3 rows (approximately)
INSERT INTO `detail_harga` (`id_roster`, `id_user`, `id_ukuran`, `harga`) VALUES
	('MAS001', 4, 1, 63000),
	('MAS001', 5, 1, 63000),
	('MAS002', 4, 10, 50000);

-- Dumping structure for table masroster.detail_motif
CREATE TABLE IF NOT EXISTS `detail_motif` (
  `id_tipe` int NOT NULL,
  `id_motif` int NOT NULL,
  UNIQUE KEY `detail_motif_id_tipe_id_motif_unique` (`id_tipe`,`id_motif`),
  KEY `detail_motif_id_motif_foreign` (`id_motif`),
  CONSTRAINT `detail_motif_id_motif_foreign` FOREIGN KEY (`id_motif`) REFERENCES `motif_roster` (`IdMotif`),
  CONSTRAINT `detail_motif_id_tipe_foreign` FOREIGN KEY (`id_tipe`) REFERENCES `tipe_roster` (`IdTipe`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table masroster.detail_motif: ~9 rows (approximately)
INSERT INTO `detail_motif` (`id_tipe`, `id_motif`) VALUES
	(1, 1),
	(2, 1),
	(1, 4),
	(2, 5),
	(1, 6),
	(2, 6),
	(5, 7),
	(6, 8),
	(6, 9);

-- Dumping structure for table masroster.detail_tipe
CREATE TABLE IF NOT EXISTS `detail_tipe` (
  `id_jenis` int DEFAULT NULL,
  `id_tipe` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='untuk mencegah tipe roster memiliki motif bovenlis';

-- Dumping data for table masroster.detail_tipe: ~2 rows (approximately)
INSERT INTO `detail_tipe` (`id_jenis`, `id_tipe`) VALUES
	(6, 1),
	(1, 1),
	(1, 2),
	(4, 5),
	(6, 6);

-- Dumping structure for table masroster.detail_transaksi
CREATE TABLE IF NOT EXISTS `detail_transaksi` (
  `IdTransaksi` varchar(8) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `IdRoster` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_ukuran` int DEFAULT NULL,
  `QtyProduk` int DEFAULT NULL,
  `SubTotal` int DEFAULT NULL,
  `CustomUkuran` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `design_file` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  KEY `IdTransaksi` (`IdTransaksi`),
  KEY `id_ukuran` (`id_ukuran`),
  KEY `IdProduk` (`IdRoster`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table masroster.detail_transaksi: ~5 rows (approximately)
INSERT INTO `detail_transaksi` (`IdTransaksi`, `IdRoster`, `id_ukuran`, `QtyProduk`, `SubTotal`, `CustomUkuran`, `design_file`) VALUES
	('TX000001', 'MAS001', 1, 20, 1260000, NULL, NULL),
	('TX000003', 'MAS001', 7, 10, 700000, NULL, NULL),
	('TX000004', 'MAS001', 1, 90, 5760000, NULL, NULL),
	('TX000005', 'MAS001', 1, 10, 630000, NULL, NULL),
	('TX000005', 'MAS002', 10, 40, 2000000, NULL, NULL);

-- Dumping structure for table masroster.jenisbarang
CREATE TABLE IF NOT EXISTS `jenisbarang` (
  `IdJenisBarang` int NOT NULL AUTO_INCREMENT,
  `JenisBarang` varchar(15) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`IdJenisBarang`),
  UNIQUE KEY `JenisBarang` (`JenisBarang`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table masroster.jenisbarang: ~3 rows (approximately)
INSERT INTO `jenisbarang` (`IdJenisBarang`, `JenisBarang`) VALUES
	(4, 'Bovenlis'),
	(6, 'Roster');

-- Dumping structure for table masroster.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table masroster.migrations: ~10 rows (approximately)
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '2025_05_03_103216_add_img_to_produk_table', 1),
	(2, '2025_05_06_134412_create_laporans_table', 1),
	(3, '2025_05_06_150856_create_laporan_transaksis_table', 1),
	(4, '2025_05_18_131611_create_addresses_table', 2),
	(5, '2025_05_18_131648_create_addresses_table', 3),
	(6, '2025_05_22_064504_alter_produk_columns_to_nullable', 4),
	(7, '2025_05_24_000000_modify_produk_table_structure', 5),
	(8, '2025_05_30_084741_add_design_file_to_detail_transaksi_table', 6),
	(9, '2025_06_02_093641_add_shipping_method_to_transaksi_table', 7),
	(10, '2025_06_02_095211_add_notes_to_transaksi_table', 8),
	(11, '2025_08_11_041402_fix_produk_foreign_keys', 9),
	(12, '2025_08_11_043608_modify_detail_motif_table', 10),
	(13, '2025_08_13_083650_remove_address_id_from_transaksi', 11),
	(14, '2025_08_24_121847_add_ongkir_to_transaksi_table', 12),
	(15, '2025_08_24_134114_add_id_ukuran_to_detail_harga_table', 13),
	(16, '2025_10_03_054801_add_nama_produk_to_produk_table', 14),
	(17, '2026_02_05_061812_add_forecast_columns_to_produk_table', 15);

-- Dumping structure for table masroster.motif_roster
CREATE TABLE IF NOT EXISTS `motif_roster` (
  `IdMotif` int NOT NULL AUTO_INCREMENT,
  `nama_motif` varchar(35) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`IdMotif`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table masroster.motif_roster: ~7 rows (approximately)
INSERT INTO `motif_roster` (`IdMotif`, `nama_motif`) VALUES
	(1, 'Classical Brown'),
	(4, 'Modern Gray'),
	(5, 'Elegant White'),
	(6, 'Burem'),
	(7, 'Beton'),
	(8, '3D'),
	(9, 'Ventalis Slip 2S');

-- Dumping structure for table masroster.password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table masroster.password_reset_tokens: ~1 rows (approximately)
INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
	('jasjus148@gmail.com', '$2y$12$4Q6l4BNrx8fPE3Dfxt87ge7FQfe2FU85OVS6heZrY/2JUHddvbPNO', '2024-06-10 05:50:08');

-- Dumping structure for table masroster.produk
CREATE TABLE IF NOT EXISTS `produk` (
  `IdRoster` varchar(13) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `NamaProduk` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_jenis` int NOT NULL,
  `id_tipe` int DEFAULT NULL,
  `id_motif` int DEFAULT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `Img` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `deskripsi` varchar(1500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `forecasted_demand` float DEFAULT NULL COMMENT 'Next month predicted demand from AI',
  `forecast_model` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Model used: lstm, prophet, or sma',
  `safety_stock` int NOT NULL DEFAULT '70' COMMENT 'Minimum stock threshold (1 batch = 70 pcs)',
  `forecast_status` enum('critical','low','safe','overstock') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'safe',
  `last_forecast_at` timestamp NULL DEFAULT NULL COMMENT 'When forecast was last calculated',
  PRIMARY KEY (`IdRoster`) USING BTREE,
  KEY `Index 5` (`id_tipe`),
  KEY `FK_produk_tipe_roster_2` (`id_motif`),
  KEY `IdJenisBarang` (`id_jenis`) USING BTREE,
  KEY `produk_forecast_status_index` (`forecast_status`),
  CONSTRAINT `motif` FOREIGN KEY (`id_motif`) REFERENCES `motif_roster` (`IdMotif`),
  CONSTRAINT `produk_id_jenis_foreign` FOREIGN KEY (`id_jenis`) REFERENCES `jenisbarang` (`IdJenisBarang`),
  CONSTRAINT `produk_id_tipe_foreign` FOREIGN KEY (`id_tipe`) REFERENCES `tipe_roster` (`IdTipe`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table masroster.produk: ~4 rows (approximately)
INSERT INTO `produk` (`IdRoster`, `NamaProduk`, `id_jenis`, `id_tipe`, `id_motif`, `stock`, `Img`, `deskripsi`, `created_at`, `updated_at`, `forecasted_demand`, `forecast_model`, `safety_stock`, `forecast_status`, `last_forecast_at`) VALUES
	('MAS001', 'Roster Mukura Classical Brown', 6, 1, 1, 500, 'produk/EFmsy8oJPHfZQvECJCcBRp2qmEQUJt6fB12DrVB6.png', 'a', '2025-08-16 21:56:25', '2026-02-05 00:03:36', 65, 'sma', 70, 'overstock', '2026-02-05 00:03:36'),
	('MAS002', 'Bovenlis Jendela Beton', 4, 5, 7, 500, 'produk/d7lZuq0ffD91M6SLjzMuc32p5AvRqqLhnisCkksC.png', 'BOVENLIS', '2025-09-17 17:11:16', '2026-02-05 00:03:36', 40, 'sma', 70, 'overstock', '2026-02-05 00:03:36'),
	('MAS003', 'Roster Biasa 3D', 6, 6, 8, 500, 'produk/Xu8TqzaoC8yaLlyRFDl495FPw51QvMjkTj3wVSIw.jpg', 'Bosan dengan dinding yang datar dan monoton? Saatnya beralih ke Roster Motif 3D kami. Ini bukan sekadar lubang angin biasa; ini adalah sebuah karya seni fungsional yang dirancang untuk mengubah fasad atau ruangan Anda menjadi sebuah statement desain yang memukau.\r\n\r\nDibuat dengan presisi tinggi, setiap motif timbul (3D) dirancang untuk "bermain" dengan cahaya. Seiring pergerakan matahari, Anda akan menyaksikan permainan bayangan yang dinamis dan selalu berubah, memberikan kesan "hidup" dan eksklusif pada bangunan Anda.\r\n\r\nKeunggulan Utama:\r\n\r\nVisual Tiga Dimensi: Motif yang menonjol memberikan kedalaman visual yang tidak bisa didapat dari roster datar.\r\n\r\nPermainan Bayangan Estetis: Menciptakan efek bayangan yang artistik dan dinamis, membuat dinding Anda tidak pernah membosankan.\r\n\r\nSirkulasi Udara Optimal: Tetap fungsional sebagai ventilasi untuk menjaga hunian tetap sejuk dan sehat.\r\n\r\nMaterial Berkualitas Tinggi: Terbuat dari [Sebutkan Bahan, misal: GRC / Beton Bertulang] yang kuat, tahan lama, dan tahan terhadap cuaca ekstrem.\r\n\r\nPrivasi Terjaga: Memberikan privasi tanpa menghalangi aliran udara dan cahaya alami.\r\n\r\nAplikasi Ideal: Fasad rumah minimalis, secondary skin bangunan, pagar modern, partisi interior (sekat ruangan), dinding dekoratif kafe, atau aksen pada area taman.\r\n\r\nPesan Sekarang dan transformasikan bangunan Anda dengan sentuhan tiga dimensi yang elegan.', '2025-11-02 08:09:40', '2026-02-05 00:03:36', 0, 'none', 70, 'safe', '2026-02-05 00:03:36'),
	('MAS004', 'Bovenlis Jendela Beton', 4, 5, 7, 20, 'produk/tFJX0vrCtpqHxmpWlpbsYhvb7Gq5S17BRWc6kJQz.jpg', 'Beton Jendela Bopen', '2026-01-29 08:25:53', '2026-02-05 00:03:36', 0, 'none', 70, 'safe', '2026-02-05 00:03:36');

-- Dumping structure for table masroster.produk_size
CREATE TABLE IF NOT EXISTS `produk_size` (
  `IdRoster` varchar(13) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `id_ukuran` int NOT NULL,
  `harga` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`IdRoster`,`id_ukuran`) USING BTREE,
  KEY `produk_size_id_ukuran_foreign` (`id_ukuran`),
  CONSTRAINT `FK_produk_size_produk` FOREIGN KEY (`IdRoster`) REFERENCES `produk` (`IdRoster`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_produk_size_size` FOREIGN KEY (`id_ukuran`) REFERENCES `size` (`id_ukuran`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table masroster.produk_size: ~5 rows (approximately)
INSERT INTO `produk_size` (`IdRoster`, `id_ukuran`, `harga`, `created_at`, `updated_at`) VALUES
	('MAS001', 1, 63000, '2025-08-16 21:56:25', '2025-11-02 08:53:46'),
	('MAS001', 7, 70000, '2025-08-16 21:56:25', '2025-11-02 08:53:46'),
	('MAS002', 10, 50000, '2025-09-17 17:11:16', '2025-11-02 08:52:21'),
	('MAS003', 1, 6000, '2025-11-02 08:09:40', '2025-11-02 08:09:40'),
	('MAS004', 7, 35000, '2026-01-29 08:25:53', '2026-01-29 08:46:22');

-- Dumping structure for table masroster.roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table masroster.roles: ~2 rows (approximately)
INSERT INTO `roles` (`id`, `name`, `display_name`, `description`, `created_at`, `updated_at`) VALUES
	(1, 'admin', 'Admin', 'Admin', '2024-02-28 02:12:01', '2024-02-28 02:12:01'),
	(2, 'user', 'User', 'User', '2024-02-28 02:12:01', '2024-02-28 02:12:01');

-- Dumping structure for table masroster.role_user
CREATE TABLE IF NOT EXISTS `role_user` (
  `role_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `user_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`,`user_type`),
  KEY `role_user_role_id_foreign` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table masroster.role_user: ~6 rows (approximately)
INSERT INTO `role_user` (`role_id`, `user_id`, `user_type`) VALUES
	(1, 1, 'App\\Models\\User'),
	(1, 2, 'App\\Models\\User'),
	(2, 5, 'App\\Models\\User'),
	(2, 6, 'App\\Models\\User'),
	(2, 81, 'App\\Models\\User'),
	(2, 83, 'App\\Models\\User'),
	(2, 84, 'App\\Models\\User');

-- Dumping structure for table masroster.size
CREATE TABLE IF NOT EXISTS `size` (
  `id_ukuran` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `panjang` int NOT NULL,
  `lebar` int NOT NULL,
  PRIMARY KEY (`id_ukuran`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table masroster.size: ~1 rows (approximately)
INSERT INTO `size` (`id_ukuran`, `nama`, `panjang`, `lebar`) VALUES
	(1, 'Standard', 20, 20),
	(7, 'Besar', 40, 60),
	(10, 'Besar', 40, 70);

-- Dumping structure for table masroster.tipe_roster
CREATE TABLE IF NOT EXISTS `tipe_roster` (
  `IdTipe` int NOT NULL AUTO_INCREMENT,
  `namaTipe` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`IdTipe`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table masroster.tipe_roster: ~3 rows (approximately)
INSERT INTO `tipe_roster` (`IdTipe`, `namaTipe`) VALUES
	(1, 'Mukura'),
	(2, 'Bata Merah'),
	(3, 'Bata Putih'),
	(4, 'Paving Block'),
	(5, 'Jendela'),
	(6, 'Biasa');

-- Dumping structure for table masroster.transaksi
CREATE TABLE IF NOT EXISTS `transaksi` (
  `IdTransaksi` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `id_admin` bigint NOT NULL DEFAULT (0),
  `id_customer` bigint NOT NULL,
  `address_id` bigint unsigned DEFAULT NULL,
  `Bayar` int NOT NULL,
  `GrandTotal` int NOT NULL,
  `tglTransaksi` datetime NOT NULL,
  `StatusPembayaran` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `StatusPesanan` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tglUpdate` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `shipping_method` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `delivery_method` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `shipping_type` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ongkir` int NOT NULL DEFAULT '0',
  `notes` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`IdTransaksi`),
  KEY `username` (`id_admin`) USING BTREE,
  KEY `IdCust` (`id_customer`) USING BTREE,
  KEY `Index 4` (`address_id`) USING BTREE,
  CONSTRAINT `FK_transaksi_addresses` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table masroster.transaksi: ~3 rows (approximately)
INSERT INTO `transaksi` (`IdTransaksi`, `id_admin`, `id_customer`, `address_id`, `Bayar`, `GrandTotal`, `tglTransaksi`, `StatusPembayaran`, `StatusPesanan`, `tglUpdate`, `created_at`, `updated_at`, `shipping_method`, `delivery_method`, `shipping_type`, `ongkir`, `notes`) VALUES
	('TX000001', 1, 4, 1, 1300000, 1260000, '2025-08-17 07:37:36', 'Paid', 'Pending', '2025-08-17 07:39:25', '2025-08-17 07:37:36', '2025-08-17 07:37:36', 'Online', NULL, 'Regular', 0, '1'),
	('TX000003', 1, 4, 2, 1000000, 700000, '2025-08-22 02:30:07', 'Paid', 'Pending', NULL, '2025-08-22 02:30:07', '2025-08-22 02:30:07', 'Online', NULL, 'awda', 0, 'a'),
	('TX000004', 1, 4, 2, 6006000, 6060000, '2025-08-27 06:59:46', 'Lunas', 'Diterima', '2025-08-31 12:59:59', '2025-08-27 06:59:46', '2025-08-27 06:59:46', 'Online', 'Delivery', 'Ongkir', 300000, 'Sing ngirim mas rujak'),
	('TX000005', 1, 4, 2, 3000000, 2630000, '2025-10-06 14:51:30', 'Lunas', 'Diterima', NULL, '2025-10-06 14:51:30', '2025-10-06 14:51:30', 'Offline', 'Pickup', 'Ongkir', 2000, 'wadwda');

-- Dumping structure for table masroster.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `f_name` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `nomor_telepon` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `email_verified_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `username` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `user` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `img` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`username`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table masroster.users: ~5 rows (approximately)
INSERT INTO `users` (`id`, `f_name`, `email`, `nomor_telepon`, `email_verified_at`, `username`, `password`, `user`, `remember_token`, `img`) VALUES
	(1, 'Admin 1', 'admin1@gmail.com', '', '2026-01-29 14:44:02', 'admin', '$2y$10$a5CeW7r8VeUPy2hQXI5xJuNhnPo8CWfDwJJQhauP0g1BJ/77olWh.', 'Admin', '', 'images/1815883516605523.jpeg'),
	(4, 'Ahmad Muzakki', 'jasjus841@gmail.com', '0879272342', '2026-01-17 06:33:34', 'jasjus841', '$2y$12$X4cGX1XP/QkWh9c5bVOrKO8b5a68gTdscbDHNGMEn/.KUmqf/ZCui', 'User', 'dARw1fMBnR39huvj0NqKSnpyumcGzjGc4X9P0RaDVuRaApCpGeRGIWYcDjSo', ''),
	(6, 'Mamat', 'kajeks841@gmail.com', '08161518497', '2025-11-04 17:14:45', 'mamat', '$2y$12$hnDb0KYGC0Dq6LzCHiu6qOXoSD8F8OzVmPcv4BbL7M3h2oTmabnlq', 'User', NULL, 'default-avatar.png'),
	(5, 'Ahmad Rojali', 'rojali@gmail.com', '08970833227', '2025-05-23 15:16:50', 'rojali', '$2y$12$0o0UcbPaQuotlWGvgAtXceAz.fzSfuIhfOXx8XRwJ8M6pNbhRPhYS', 'User', NULL, 'default-avatar.png'),
	(2, 'Admin Roster', 'admin@gmail.com', '082472332', '2026-01-29 14:43:59', 'tsy24', '$2y$12$X4cGX1XP/QkWh9c5bVOrKO8b5a68gTdscbDHNGMEn/.KUmqf/ZCui', 'Admin', 'yKhBeiCjxn4XfUe9wx7XFpggoz9LLHN2bbsMjxAJEhWuBeqqL53Wr8bi83js', 'images/1815883516605523.jpeg');

-- Dumping structure for trigger masroster.stokKeluar
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `stokKeluar` AFTER INSERT ON `detail_barangkeluar` FOR EACH ROW BEGIN
UPDATE produk 
SET Stok = Stok - NEW.QtyKeluar 
WHERE IdRoster = NEW.IdRoster;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger masroster.stokMasuk
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `stokMasuk` AFTER INSERT ON `detail_barangmasuk` FOR EACH ROW BEGIN
UPDATE produk 
SET Stok = Stok + NEW.QtyMasuk 
WHERE IdRoster = NEW.IdRoster;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger masroster.UpdateStokDeleteKeluar
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `UpdateStokDeleteKeluar` AFTER DELETE ON `detail_barangkeluar` FOR EACH ROW UPDATE databarang
SET databarang.JumlahStok = databarang.JumlahStok + OLD.QtyKeluar
WHERE databarang.IdBarang = OLD.IdBarang//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger masroster.UpdateStokDeleteMasuk
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `UpdateStokDeleteMasuk` AFTER DELETE ON `detail_barangmasuk` FOR EACH ROW UPDATE databarang
SET databarang.JumlahStok = databarang.JumlahStok - OLD.QtyMasuk
WHERE databarang.IdBarang = OLD.IdBarang//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
