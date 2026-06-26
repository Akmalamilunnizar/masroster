-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Jun 25, 2026 at 01:05 PM
-- Server version: 11.4.9-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `masroster`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `label` varchar(255) NOT NULL,
  `recipient_name` varchar(255) NOT NULL,
  `phone_number` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `postal_code` varchar(255) NOT NULL,
  `full_address` text NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`id`, `user_id`, `label`, `recipient_name`, `phone_number`, `city`, `postal_code`, `full_address`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 4, 'awd', 'Ahmad Muzakki', '19272342', 'Malang', 'Talok', 'Talok Malang Kecamatan Malang', 1, '2025-05-18 06:48:43', '2025-05-31 00:02:12'),
(2, 4, 'Jember', 'Alan', '081238288', 'Jember', '190237', 'Jalan Kamilantan', 1, '2025-05-23 15:00:02', '2025-05-31 00:02:12');

-- --------------------------------------------------------

--
-- Table structure for table `detail_harga`
--

CREATE TABLE `detail_harga` (
  `id_roster` varchar(13) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `produk_id` bigint(20) UNSIGNED DEFAULT NULL,
  `id_user` bigint(20) NOT NULL DEFAULT 0,
  `id_ukuran` int(11) NOT NULL,
  `harga` int(11) NOT NULL,
  `address_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='membedakan harga jual tiap transaksi setiap toko';

--
-- Dumping data for table `detail_harga`
--

INSERT INTO `detail_harga` (`id_roster`, `produk_id`, `id_user`, `id_ukuran`, `harga`, `address_id`) VALUES
('MAS001', 1, 4, 1, 63000, NULL),
('MAS001', 1, 5, 1, 63000, NULL),
('MAS002', 2, 4, 10, 50000, NULL),
('MAS001', 1, 4, 1, 68000, NULL),
('MAS001', 1, 5, 1, 63000, NULL),
('MAS002', 2, 4, 10, 50000, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `detail_motif`
--

CREATE TABLE `detail_motif` (
  `id_tipe` int(11) NOT NULL,
  `id_motif` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_motif`
--

INSERT INTO `detail_motif` (`id_tipe`, `id_motif`) VALUES
(1, 1),
(2, 1),
(1, 4),
(2, 5),
(1, 6),
(2, 6),
(5, 7),
(6, 8),
(6, 9),
(7, 10),
(7, 11),
(7, 12);

-- --------------------------------------------------------

--
-- Table structure for table `detail_tipe`
--

CREATE TABLE `detail_tipe` (
  `id_jenis` int(11) NOT NULL,
  `id_tipe` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='untuk mencegah tipe roster memiliki motif bovenlis';

--
-- Dumping data for table `detail_tipe`
--

INSERT INTO `detail_tipe` (`id_jenis`, `id_tipe`) VALUES
(1, 1),
(1, 2),
(4, 5),
(4, 7),
(6, 1),
(6, 6),
(6, 7),
(7, 8),
(8, 7);

-- --------------------------------------------------------

--
-- Table structure for table `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `IdTransaksi` varchar(8) DEFAULT NULL,
  `IdRoster` varchar(6) DEFAULT NULL,
  `produk_id` bigint(20) UNSIGNED DEFAULT NULL,
  `id_ukuran` int(11) DEFAULT NULL,
  `harga_satuan` int(11) DEFAULT NULL COMMENT 'Immutable unit price snapshot at the time of checkout or approval',
  `QtyProduk` int(11) DEFAULT NULL,
  `data_type` varchar(20) NOT NULL DEFAULT 'Eceran' COMMENT 'Auto classified from QtyProduk (>100 Borongan, else Eceran)',
  `SubTotal` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`IdTransaksi`, `IdRoster`, `produk_id`, `id_ukuran`, `harga_satuan`, `QtyProduk`, `data_type`, `SubTotal`) VALUES
('TX000001', 'MAS001', 1, 1, NULL, 20, 'Eceran', 1260000),
('TX000003', 'MAS001', 1, 7, NULL, 10, 'Eceran', 700000),
('TX000004', 'MAS001', 1, 1, NULL, 90, 'Eceran', 5760000),
('TX000005', 'MAS001', 1, 1, NULL, 10, 'Eceran', 630000),
('TX000005', 'MAS002', 2, 10, NULL, 40, 'Eceran', 2000000),
('TX000001', 'MAS001', 1, 1, NULL, 20, 'Eceran', 1260000),
('TX000003', 'MAS001', 1, 7, NULL, 10, 'Eceran', 700000),
('TX000004', 'MAS001', 1, 1, NULL, 90, 'Eceran', 5760000),
('TX000005', 'MAS001', 1, 1, NULL, 10, 'Eceran', 630000),
('TX000005', 'MAS002', 2, 10, NULL, 40, 'Eceran', 2000000),
('TR000001', 'MAS001', 1, 1, NULL, 21, 'Eceran', 1323000),
('TR000001', 'MAS004', 4, 7, NULL, 15, 'Eceran', 525000),
('TR000002', 'MAS001', 1, 1, NULL, 20, 'Eceran', 1260000),
('TR000002', 'MAS003', 3, 1, NULL, 28, 'Eceran', 168000),
('TR000003', 'MAS001', 1, 1, NULL, 19, 'Eceran', 1197000),
('TR000003', 'MAS003', 3, 1, NULL, 44, 'Eceran', 264000),
('TR000004', 'MAS001', 1, 1, NULL, 23, 'Eceran', 1449000),
('TR000004', 'MAS003', 3, 1, NULL, 37, 'Eceran', 222000),
('TR000005', 'MAS001', 1, 1, NULL, 23, 'Eceran', 1449000),
('TR000005', 'MAS003', 3, 1, NULL, 40, 'Eceran', 240000),
('TR000006', 'MAS001', 1, 1, NULL, 24, 'Eceran', 1512000),
('TR000007', 'MAS001', 1, 1, NULL, 23, 'Eceran', 1449000),
('TR000007', 'MAS003', 3, 1, NULL, 15, 'Eceran', 90000),
('TR000008', 'MAS001', 1, 1, NULL, 18, 'Eceran', 1134000),
('TR000008', 'MAS003', 3, 1, NULL, 34, 'Eceran', 204000),
('TR000009', 'MAS001', 1, 1, NULL, 21, 'Eceran', 1323000),
('TR000009', 'MAS003', 3, 1, NULL, 43, 'Eceran', 258000),
('TR000009', 'MAS004', 4, 7, NULL, 8, 'Eceran', 280000),
('TR000010', 'MAS001', 1, 1, NULL, 23, 'Eceran', 1449000),
('TR000010', 'MAS003', 3, 1, NULL, 36, 'Eceran', 216000),
('TR000010', 'MAS002', 2, 10, NULL, 229, 'Borongan', 11450000),
('TR000011', 'MAS001', 1, 1, NULL, 22, 'Eceran', 1386000),
('TR000011', 'MAS003', 3, 1, NULL, 26, 'Eceran', 156000),
('TR000012', 'MAS001', 1, 1, NULL, 22, 'Eceran', 1386000),
('TR000012', 'MAS003', 3, 1, NULL, 41, 'Eceran', 246000),
('TR000012', 'MAS004', 4, 7, NULL, 18, 'Eceran', 630000),
('TR000013', 'MAS001', 1, 1, NULL, 23, 'Eceran', 1449000),
('TR000013', 'MAS003', 3, 1, NULL, 22, 'Eceran', 132000),
('TR000013', 'MAS004', 4, 7, NULL, 18, 'Eceran', 630000),
('TR000013', 'MAS002', 2, 10, NULL, 193, 'Borongan', 9650000),
('TR000014', 'MAS001', 1, 1, NULL, 25, 'Eceran', 1575000),
('TR000014', 'MAS004', 4, 7, NULL, 20, 'Eceran', 700000),
('TR000014', 'MAS002', 2, 10, NULL, 147, 'Borongan', 7350000),
('TR000015', 'MAS001', 1, 1, NULL, 28, 'Eceran', 1764000),
('TR000016', 'MAS001', 1, 1, NULL, 23, 'Eceran', 1449000),
('TR000016', 'MAS003', 3, 1, NULL, 45, 'Eceran', 270000),
('TR000016', 'MAS004', 4, 7, NULL, 17, 'Eceran', 595000),
('TR000017', 'MAS001', 1, 1, NULL, 23, 'Eceran', 1449000),
('TR000017', 'MAS003', 3, 1, NULL, 32, 'Eceran', 192000),
('TR000018', 'MAS001', 1, 1, NULL, 25, 'Eceran', 1575000),
('TR000018', 'MAS003', 3, 1, NULL, 52, 'Eceran', 312000),
('TR000019', 'MAS001', 1, 1, NULL, 30, 'Eceran', 1890000),
('TR000019', 'MAS003', 3, 1, NULL, 48, 'Eceran', 288000),
('TR000020', 'MAS001', 1, 1, NULL, 23, 'Eceran', 1449000),
('TR000020', 'MAS003', 3, 1, NULL, 40, 'Eceran', 240000),
('TR000020', 'MAS002', 2, 10, NULL, 204, 'Borongan', 10200000),
('TR000021', 'MAS001', 1, 1, NULL, 26, 'Eceran', 1638000),
('TR000021', 'MAS003', 3, 1, NULL, 19, 'Eceran', 114000),
('TR000021', 'MAS002', 2, 10, NULL, 226, 'Borongan', 11300000),
('TR000022', 'MAS001', 1, 1, NULL, 28, 'Eceran', 1764000),
('TR000022', 'MAS002', 2, 10, NULL, 120, 'Borongan', 6000000),
('TR000023', 'MAS001', 1, 1, NULL, 22, 'Eceran', 1386000),
('TR000023', 'MAS004', 4, 7, NULL, 16, 'Eceran', 560000),
('TR000024', 'MAS001', 1, 1, NULL, 29, 'Eceran', 1827000),
('TR000024', 'MAS004', 4, 7, NULL, 13, 'Eceran', 455000),
('TR000025', 'MAS001', 1, 1, NULL, 25, 'Eceran', 1575000),
('TR000025', 'MAS003', 3, 1, NULL, 26, 'Eceran', 156000),
('TR000026', 'MAS001', 1, 1, NULL, 29, 'Eceran', 1827000),
('TR000026', 'MAS004', 4, 7, NULL, 16, 'Eceran', 560000),
('TR000027', 'MAS001', 1, 1, NULL, 30, 'Eceran', 1890000),
('TR000027', 'MAS003', 3, 1, NULL, 42, 'Eceran', 252000),
('TR000028', 'MAS001', 1, 1, NULL, 28, 'Eceran', 1764000),
('TR000028', 'MAS003', 3, 1, NULL, 25, 'Eceran', 150000),
('TR000029', 'MAS001', 1, 1, NULL, 24, 'Eceran', 1512000),
('TR000029', 'MAS003', 3, 1, NULL, 48, 'Eceran', 288000),
('TR000029', 'MAS002', 2, 10, NULL, 163, 'Borongan', 8150000),
('TR000030', 'MAS001', 1, 1, NULL, 24, 'Eceran', 1512000),
('TR000030', 'MAS003', 3, 1, NULL, 36, 'Eceran', 216000),
('TR000031', 'MAS001', 1, 1, NULL, 24, 'Eceran', 1512000),
('TR000031', 'MAS003', 3, 1, NULL, 37, 'Eceran', 222000),
('TR000031', 'MAS004', 4, 7, NULL, 10, 'Eceran', 350000),
('TR000032', 'MAS001', 1, 1, NULL, 21, 'Eceran', 1323000),
('TR000032', 'MAS003', 3, 1, NULL, 32, 'Eceran', 192000),
('TR000032', 'MAS002', 2, 10, NULL, 223, 'Borongan', 11150000),
('TR000033', 'MAS001', 1, 1, NULL, 22, 'Eceran', 1386000),
('TR000033', 'MAS003', 3, 1, NULL, 18, 'Eceran', 108000),
('TR000034', 'MAS001', 1, 1, NULL, 24, 'Eceran', 1512000),
('TR000034', 'MAS004', 4, 7, NULL, 24, 'Eceran', 840000),
('TR000035', 'MAS001', 1, 1, NULL, 31, 'Eceran', 1953000),
('TR000035', 'MAS003', 3, 1, NULL, 21, 'Eceran', 126000),
('TR000036', 'MAS001', 1, 1, NULL, 25, 'Eceran', 1575000),
('TR000036', 'MAS004', 4, 7, NULL, 15, 'Eceran', 525000),
('TR000037', 'MAS001', 1, 1, NULL, 19, 'Eceran', 1197000),
('TR000038', 'MAS001', 1, 1, NULL, 19, 'Eceran', 1197000),
('TR000038', 'MAS003', 3, 1, NULL, 39, 'Eceran', 234000),
('TR000039', 'MAS001', 1, 1, NULL, 24, 'Eceran', 1512000),
('TR000039', 'MAS003', 3, 1, NULL, 15, 'Eceran', 90000),
('TR000040', 'MAS001', 1, 1, NULL, 23, 'Eceran', 1449000),
('TR000040', 'MAS003', 3, 1, NULL, 23, 'Eceran', 138000),
('TR000041', 'MAS001', 1, 1, NULL, 19, 'Eceran', 1197000),
('TR000041', 'MAS003', 3, 1, NULL, 36, 'Eceran', 216000),
('TR000042', 'MAS001', 1, 1, NULL, 21, 'Eceran', 1323000),
('TR000042', 'MAS003', 3, 1, NULL, 27, 'Eceran', 162000),
('TR000043', 'MAS001', 1, 1, NULL, 20, 'Eceran', 1260000),
('TR000043', 'MAS003', 3, 1, NULL, 45, 'Eceran', 270000),
('TR000044', 'MAS001', 1, 1, NULL, 26, 'Eceran', 1638000),
('TR000045', 'MAS001', 1, 1, NULL, 20, 'Eceran', 1260000),
('TR000045', 'MAS004', 4, 7, NULL, 15, 'Eceran', 525000),
('TR000046', 'MAS001', 1, 1, NULL, 20, 'Eceran', 1260000),
('TR000046', 'MAS003', 3, 1, NULL, 18, 'Eceran', 108000),
('TR000047', 'MAS001', 1, 1, NULL, 24, 'Eceran', 1512000),
('TR000047', 'MAS003', 3, 1, NULL, 29, 'Eceran', 174000),
('TR000047', 'MAS002', 2, 10, NULL, 167, 'Borongan', 8350000),
('TR000048', 'MAS001', 1, 1, NULL, 24, 'Eceran', 1512000),
('TR000048', 'MAS003', 3, 1, NULL, 40, 'Eceran', 240000),
('TR000048', 'MAS004', 4, 7, NULL, 14, 'Eceran', 490000),
('TR000049', 'MAS001', 1, 1, NULL, 24, 'Eceran', 1512000),
('TR000049', 'MAS003', 3, 1, NULL, 23, 'Eceran', 138000),
('TR000050', 'MAS001', 1, 1, NULL, 19, 'Eceran', 1197000),
('TR000050', 'MAS003', 3, 1, NULL, 44, 'Eceran', 264000),
('TR000051', 'MAS001', 1, 1, NULL, 20, 'Eceran', 1260000),
('TR000051', 'MAS004', 4, 7, NULL, 9, 'Eceran', 315000),
('TR000052', 'MAS001', 1, 1, NULL, 25, 'Eceran', 1575000),
('TR000052', 'MAS003', 3, 1, NULL, 36, 'Eceran', 216000),
('TR000053', 'MAS001', 1, 1, NULL, 28, 'Eceran', 1764000),
('TR000053', 'MAS003', 3, 1, NULL, 48, 'Eceran', 288000),
('TR000053', 'MAS004', 4, 7, NULL, 18, 'Eceran', 630000),
('TR000054', 'MAS001', 1, 1, NULL, 23, 'Eceran', 1449000),
('TR000054', 'MAS003', 3, 1, NULL, 50, 'Eceran', 300000),
('TR000055', 'MAS001', 1, 1, NULL, 27, 'Eceran', 1701000),
('TR000056', 'MAS001', 1, 1, NULL, 23, 'Eceran', 1449000),
('TR000057', 'MAS001', 1, 1, NULL, 28, 'Eceran', 1764000),
('TR000057', 'MAS003', 3, 1, NULL, 38, 'Eceran', 228000),
('TR000058', 'MAS001', 1, 1, NULL, 25, 'Eceran', 1575000),
('TR000058', 'MAS003', 3, 1, NULL, 38, 'Eceran', 228000),
('TR000058', 'MAS002', 2, 10, NULL, 170, 'Borongan', 8500000),
('TR000059', 'MAS001', 1, 1, NULL, 28, 'Eceran', 1764000),
('TR000059', 'MAS003', 3, 1, NULL, 29, 'Eceran', 174000),
('TR000059', 'MAS004', 4, 7, NULL, 12, 'Eceran', 420000),
('TR000060', 'MAS001', 1, 1, NULL, 27, 'Eceran', 1701000),
('TR000061', 'MAS001', 1, 1, NULL, 21, 'Eceran', 1323000),
('TR000061', 'MAS003', 3, 1, NULL, 42, 'Eceran', 252000),
('TR000062', 'MAS001', 1, 1, NULL, 22, 'Eceran', 1386000),
('TR000062', 'MAS003', 3, 1, NULL, 22, 'Eceran', 132000),
('TR000062', 'MAS004', 4, 7, NULL, 22, 'Eceran', 770000),
('TR000063', 'MAS001', 1, 1, NULL, 28, 'Eceran', 1764000),
('TR000063', 'MAS003', 3, 1, NULL, 27, 'Eceran', 162000),
('TR000064', 'MAS001', 1, 1, NULL, 29, 'Eceran', 1827000),
('TR000064', 'MAS003', 3, 1, NULL, 36, 'Eceran', 216000),
('TR000064', 'MAS002', 2, 10, NULL, 238, 'Borongan', 11900000),
('TR000065', 'MAS001', 1, 1, NULL, 30, 'Eceran', 1890000),
('TR000065', 'MAS003', 3, 1, NULL, 31, 'Eceran', 186000),
('TR000066', 'MAS001', 1, 1, NULL, 27, 'Eceran', 1701000),
('TR000066', 'MAS003', 3, 1, NULL, 27, 'Eceran', 162000),
('TR000066', 'MAS004', 4, 7, NULL, 13, 'Eceran', 455000),
('TR000067', 'MAS001', 1, 1, NULL, 27, 'Eceran', 1701000),
('TR000068', 'MAS001', 1, 1, NULL, 27, 'Eceran', 1701000),
('TR000069', 'MAS001', 1, 1, NULL, 29, 'Eceran', 1827000),
('TR000069', 'MAS003', 3, 1, NULL, 39, 'Eceran', 234000),
('TR000069', 'MAS004', 4, 7, NULL, 14, 'Eceran', 490000),
('TR000070', 'MAS001', 1, 1, NULL, 21, 'Eceran', 1323000),
('TR000070', 'MAS003', 3, 1, NULL, 25, 'Eceran', 150000),
('TR000070', 'MAS004', 4, 7, NULL, 16, 'Eceran', 560000),
('TR000070', 'MAS002', 2, 10, NULL, 166, 'Borongan', 8300000),
('TR000071', 'MAS001', 1, 1, NULL, 24, 'Eceran', 1512000),
('TR000071', 'MAS003', 3, 1, NULL, 17, 'Eceran', 102000),
('TR000071', 'MAS004', 4, 7, NULL, 19, 'Eceran', 665000),
('TR000072', 'MAS001', 1, 1, NULL, 24, 'Eceran', 1512000),
('TR000072', 'MAS003', 3, 1, NULL, 13, 'Eceran', 78000),
('TR000073', 'MAS001', 1, 1, NULL, 26, 'Eceran', 1638000),
('TR000073', 'MAS003', 3, 1, NULL, 30, 'Eceran', 180000),
('TR000074', 'MAS001', 1, 1, NULL, 26, 'Eceran', 1638000),
('TR000074', 'MAS003', 3, 1, NULL, 14, 'Eceran', 84000),
('TR000075', 'MAS001', 1, 1, NULL, 21, 'Eceran', 1323000),
('TR000075', 'MAS004', 4, 7, NULL, 17, 'Eceran', 595000),
('TR000076', 'MAS001', 1, 1, NULL, 24, 'Eceran', 1512000),
('TR000077', 'MAS001', 1, 1, NULL, 21, 'Eceran', 1323000),
('TR000077', 'MAS003', 3, 1, NULL, 23, 'Eceran', 138000),
('TR000078', 'MAS001', 1, 1, NULL, 26, 'Eceran', 1638000),
('TR000078', 'MAS003', 3, 1, NULL, 26, 'Eceran', 156000),
('TR000078', 'MAS004', 4, 7, NULL, 15, 'Eceran', 525000),
('TR000079', 'MAS001', 1, 1, NULL, 23, 'Eceran', 1449000),
('TR000079', 'MAS003', 3, 1, NULL, 43, 'Eceran', 258000),
('TR000079', 'MAS004', 4, 7, NULL, 8, 'Eceran', 280000),
('TR000080', 'MAS001', 1, 1, NULL, 22, 'Eceran', 1386000),
('TR000080', 'MAS003', 3, 1, NULL, 37, 'Eceran', 222000),
('TR000080', 'MAS004', 4, 7, NULL, 9, 'Eceran', 315000),
('TR000081', 'MAS001', 1, 1, NULL, 24, 'Eceran', 1512000),
('TR000081', 'MAS003', 3, 1, NULL, 34, 'Eceran', 204000),
('TR000082', 'MAS001', 1, 1, NULL, 22, 'Eceran', 1386000),
('TR000082', 'MAS003', 3, 1, NULL, 17, 'Eceran', 102000),
('TR000083', 'MAS001', 1, 1, NULL, 18, 'Eceran', 1134000),
('TR000083', 'MAS003', 3, 1, NULL, 16, 'Eceran', 96000),
('TR000083', 'MAS004', 4, 7, NULL, 8, 'Eceran', 280000),
('TR000084', 'MAS001', 1, 1, NULL, 18, 'Eceran', 1134000),
('TR000084', 'MAS003', 3, 1, NULL, 38, 'Eceran', 228000),
('TR000084', 'MAS004', 4, 7, NULL, 7, 'Eceran', 245000),
('TR000085', 'MAS001', 1, 1, NULL, 23, 'Eceran', 1449000),
('TR000086', 'MAS001', 1, 1, NULL, 25, 'Eceran', 1575000),
('TR000087', 'MAS001', 1, 1, NULL, 18, 'Eceran', 1134000),
('TR000087', 'MAS003', 3, 1, NULL, 17, 'Eceran', 102000),
('TR000088', 'MAS001', 1, 1, NULL, 18, 'Eceran', 1134000),
('TR000089', 'MAS001', 1, 1, NULL, 26, 'Eceran', 1638000),
('TR000089', 'MAS004', 4, 7, NULL, 10, 'Eceran', 350000),
('TR000090', 'MAS001', 1, 1, NULL, 21, 'Eceran', 1323000),
('TR000090', 'MAS003', 3, 1, NULL, 40, 'Eceran', 240000),
('TR000091', 'MAS001', 1, 1, NULL, 25, 'Eceran', 1575000),
('TR000091', 'MAS003', 3, 1, NULL, 17, 'Eceran', 102000),
('TR000092', 'MAS001', 1, 1, NULL, 20, 'Eceran', 1260000),
('TR000092', 'MAS003', 3, 1, NULL, 43, 'Eceran', 258000),
('TR000093', 'MAS001', 1, 1, NULL, 24, 'Eceran', 1512000),
('TR000093', 'MAS004', 4, 7, NULL, 12, 'Eceran', 420000),
('TR000094', 'MAS001', 1, 1, NULL, 22, 'Eceran', 1386000),
('TR000094', 'MAS003', 3, 1, NULL, 30, 'Eceran', 180000),
('TR000094', 'MAS002', 2, 10, NULL, 240, 'Borongan', 12000000),
('TR000095', 'MAS001', 1, 1, NULL, 19, 'Eceran', 1197000),
('TR000095', 'MAS003', 3, 1, NULL, 25, 'Eceran', 150000),
('TR000095', 'MAS004', 4, 7, NULL, 16, 'Eceran', 560000),
('TR000096', 'MAS001', 1, 1, NULL, 24, 'Eceran', 1512000),
('TR000096', 'MAS003', 3, 1, NULL, 14, 'Eceran', 84000),
('TR000096', 'MAS002', 2, 10, NULL, 129, 'Borongan', 6450000),
('TR000097', 'MAS001', 1, 1, NULL, 26, 'Eceran', 1638000),
('TR000097', 'MAS003', 3, 1, NULL, 30, 'Eceran', 180000),
('TR000097', 'MAS002', 2, 10, NULL, 129, 'Borongan', 6450000),
('TR000098', 'MAS001', 1, 1, NULL, 22, 'Eceran', 1386000),
('TR000098', 'MAS003', 3, 1, NULL, 31, 'Eceran', 186000),
('TR000098', 'MAS004', 4, 7, NULL, 12, 'Eceran', 420000),
('TR000099', 'MAS001', 1, 1, NULL, 19, 'Eceran', 1197000),
('TR000099', 'MAS003', 3, 1, NULL, 19, 'Eceran', 114000),
('TR000100', 'MAS001', 1, 1, NULL, 22, 'Eceran', 1386000),
('TR000101', 'MAS001', 1, 1, NULL, 22, 'Eceran', 1386000),
('TR000101', 'MAS003', 3, 1, NULL, 15, 'Eceran', 90000),
('TR000102', 'MAS001', 1, 1, NULL, 23, 'Eceran', 1449000),
('TR000102', 'MAS003', 3, 1, NULL, 37, 'Eceran', 222000),
('TR000102', 'MAS004', 4, 7, NULL, 10, 'Eceran', 350000),
('TR000103', 'MAS001', 1, 1, NULL, 20, 'Eceran', 1260000),
('TR000103', 'MAS003', 3, 1, NULL, 18, 'Eceran', 108000),
('TR000104', 'MAS001', 1, 1, NULL, 25, 'Eceran', 1575000),
('TR000105', 'MAS001', 1, 1, NULL, 19, 'Eceran', 1197000),
('TR000105', 'MAS004', 4, 7, NULL, 11, 'Eceran', 385000),
('TR000106', 'MAS001', 1, 1, NULL, 20, 'Eceran', 1260000),
('TR000106', 'MAS003', 3, 1, NULL, 19, 'Eceran', 114000),
('TR000107', 'MAS001', 1, 1, NULL, 24, 'Eceran', 1512000),
('TR000107', 'MAS003', 3, 1, NULL, 47, 'Eceran', 282000),
('TR000107', 'MAS004', 4, 7, NULL, 17, 'Eceran', 595000),
('TR000108', 'MAS001', 1, 1, NULL, 21, 'Eceran', 1323000),
('TR000108', 'MAS004', 4, 7, NULL, 21, 'Eceran', 735000),
('TR000109', 'MAS001', 1, 1, NULL, 24, 'Eceran', 1512000),
('TR000109', 'MAS003', 3, 1, NULL, 29, 'Eceran', 174000),
('TR000110', 'MAS001', 1, 1, NULL, 20, 'Eceran', 1260000),
('TR000110', 'MAS003', 3, 1, NULL, 39, 'Eceran', 234000),
('TR000111', 'MAS001', 1, 1, NULL, 27, 'Eceran', 1701000),
('TR000111', 'MAS003', 3, 1, NULL, 24, 'Eceran', 144000),
('TR000111', 'MAS004', 4, 7, NULL, 21, 'Eceran', 735000),
('TR000112', 'MAS001', 1, 1, NULL, 24, 'Eceran', 1512000),
('TR000112', 'MAS003', 3, 1, NULL, 24, 'Eceran', 144000),
('TR000112', 'MAS004', 4, 7, NULL, 16, 'Eceran', 560000),
('TR000112', 'MAS002', 2, 10, NULL, 117, 'Borongan', 5850000),
('TR000113', 'MAS001', 1, 1, NULL, 28, 'Eceran', 1764000),
('TR000113', 'MAS003', 3, 1, NULL, 42, 'Eceran', 252000),
('TR000114', 'MAS001', 1, 1, NULL, 27, 'Eceran', 1701000),
('TR000114', 'MAS003', 3, 1, NULL, 34, 'Eceran', 204000),
('TR000114', 'MAS002', 2, 10, NULL, 236, 'Borongan', 11800000),
('TR000115', 'MAS001', 1, 1, NULL, 27, 'Eceran', 1701000),
('TR000115', 'MAS003', 3, 1, NULL, 31, 'Eceran', 186000),
('TR000116', 'MAS001', 1, 1, NULL, 29, 'Eceran', 1827000),
('TR000116', 'MAS003', 3, 1, NULL, 19, 'Eceran', 114000),
('TR000117', 'MAS001', 1, 1, NULL, 30, 'Eceran', 1890000),
('TR000117', 'MAS003', 3, 1, NULL, 18, 'Eceran', 108000),
('TR000117', 'MAS004', 4, 7, NULL, 23, 'Eceran', 805000),
('TR000118', 'MAS001', 1, 1, NULL, 26, 'Eceran', 1638000),
('TR000119', 'MAS001', 1, 1, NULL, 26, 'Eceran', 1638000),
('TR000119', 'MAS004', 4, 7, NULL, 13, 'Eceran', 455000),
('TR000120', 'MAS001', 1, 1, NULL, 23, 'Eceran', 1449000),
('TR000120', 'MAS003', 3, 1, NULL, 42, 'Eceran', 252000),
('TR000120', 'MAS004', 4, 7, NULL, 20, 'Eceran', 700000),
('TR000121', 'MAS001', 1, 1, NULL, 22, 'Eceran', 1386000),
('TR000121', 'MAS002', 2, 10, NULL, 197, 'Borongan', 9850000),
('TR000122', 'MAS001', 1, 1, NULL, 24, 'Eceran', 1512000),
('TR000122', 'MAS003', 3, 1, NULL, 27, 'Eceran', 162000),
('TR000122', 'MAS004', 4, 7, NULL, 9, 'Eceran', 315000),
('TR000123', 'MAS001', 1, 1, NULL, 27, 'Eceran', 1701000),
('TR000124', 'MAS001', 1, 1, NULL, 26, 'Eceran', 1638000),
('TR000124', 'MAS003', 3, 1, NULL, 31, 'Eceran', 186000),
('TR000124', 'MAS004', 4, 7, NULL, 16, 'Eceran', 560000),
('TR000125', 'MAS001', 1, 1, NULL, 26, 'Eceran', 1638000),
('TR000125', 'MAS003', 3, 1, NULL, 42, 'Eceran', 252000),
('TR000125', 'MAS004', 4, 7, NULL, 22, 'Eceran', 770000),
('TR000126', 'MAS001', 1, 1, NULL, 25, 'Eceran', 1575000),
('TR000126', 'MAS003', 3, 1, NULL, 21, 'Eceran', 126000),
('TR000127', 'MAS001', 1, 1, NULL, 22, 'Eceran', 1386000),
('TR000128', 'MAS001', 1, 1, NULL, 25, 'Eceran', 1575000),
('TR000128', 'MAS003', 3, 1, NULL, 47, 'Eceran', 282000),
('TR000128', 'MAS004', 4, 7, NULL, 15, 'Eceran', 525000),
('TR000128', 'MAS002', 2, 10, NULL, 195, 'Borongan', 9750000),
('TR000129', 'MAS001', 1, 1, NULL, 24, 'Eceran', 1512000),
('TR000129', 'MAS003', 3, 1, NULL, 49, 'Eceran', 294000),
('TR000129', 'MAS004', 4, 7, NULL, 8, 'Eceran', 280000),
('TR000130', 'MAS001', 1, 1, NULL, 26, 'Eceran', 1638000),
('TR000130', 'MAS003', 3, 1, NULL, 42, 'Eceran', 252000),
('TR000130', 'MAS002', 2, 10, NULL, 170, 'Borongan', 8500000),
('TR000131', 'MAS001', 1, 1, NULL, 22, 'Eceran', 1386000),
('TR000131', 'MAS004', 4, 7, NULL, 20, 'Eceran', 700000),
('TR000132', 'MAS001', 1, 1, NULL, 23, 'Eceran', 1449000),
('TR000132', 'MAS004', 4, 7, NULL, 12, 'Eceran', 420000),
('TR000133', 'MAS001', 1, 1, NULL, 29, 'Eceran', 1827000),
('TR000133', 'MAS003', 3, 1, NULL, 42, 'Eceran', 252000),
('TR000133', 'MAS004', 4, 7, NULL, 10, 'Eceran', 350000),
('TR000133', 'MAS002', 2, 10, NULL, 175, 'Borongan', 8750000),
('TR000134', 'MAS001', 1, 1, NULL, 20, 'Eceran', 1260000),
('TR000134', 'MAS003', 3, 1, NULL, 46, 'Eceran', 276000),
('TR000134', 'MAS004', 4, 7, NULL, 8, 'Eceran', 280000),
('TR000135', 'MAS001', 1, 1, NULL, 28, 'Eceran', 1764000),
('TR000135', 'MAS003', 3, 1, NULL, 44, 'Eceran', 264000),
('TR000136', 'MAS001', 1, 1, NULL, 20, 'Eceran', 1260000),
('TR000136', 'MAS004', 4, 7, NULL, 20, 'Eceran', 700000),
('TR000137', 'MAS001', 1, 1, NULL, 28, 'Eceran', 1764000),
('TR000137', 'MAS003', 3, 1, NULL, 46, 'Eceran', 276000),
('TR000137', 'MAS004', 4, 7, NULL, 19, 'Eceran', 665000),
('TR000138', 'MAS001', 1, 1, NULL, 27, 'Eceran', 1701000),
('TR000138', 'MAS003', 3, 1, NULL, 30, 'Eceran', 180000),
('TR000138', 'MAS004', 4, 7, NULL, 16, 'Eceran', 560000),
('TR000139', 'MAS001', 1, 1, NULL, 21, 'Eceran', 1323000),
('TR000139', 'MAS003', 3, 1, NULL, 32, 'Eceran', 192000),
('TR000140', 'MAS001', 1, 1, NULL, 27, 'Eceran', 1701000),
('TR000140', 'MAS003', 3, 1, NULL, 42, 'Eceran', 252000),
('TR000141', 'MAS001', 1, 1, NULL, 22, 'Eceran', 1386000),
('TR000141', 'MAS003', 3, 1, NULL, 46, 'Eceran', 276000),
('TR000142', 'MAS001', 1, 1, NULL, 23, 'Eceran', 1449000),
('TR000143', 'MAS001', 1, 1, NULL, 22, 'Eceran', 1386000),
('TR000143', 'MAS003', 3, 1, NULL, 20, 'Eceran', 120000),
('TR000143', 'MAS004', 4, 7, NULL, 11, 'Eceran', 385000),
('TR000144', 'MAS001', 1, 1, NULL, 25, 'Eceran', 1575000),
('TR000144', 'MAS003', 3, 1, NULL, 35, 'Eceran', 210000),
('TR000145', 'MAS001', 1, 1, NULL, 24, 'Eceran', 1512000),
('TR000145', 'MAS003', 3, 1, NULL, 26, 'Eceran', 156000),
('TR000145', 'MAS004', 4, 7, NULL, 15, 'Eceran', 525000),
('TR000146', 'MAS001', 1, 1, NULL, 22, 'Eceran', 1386000),
('TR000146', 'MAS003', 3, 1, NULL, 18, 'Eceran', 108000),
('TR000147', 'MAS001', 1, 1, NULL, 25, 'Eceran', 1575000),
('TR000147', 'MAS003', 3, 1, NULL, 23, 'Eceran', 138000),
('TR000147', 'MAS004', 4, 7, NULL, 13, 'Eceran', 455000),
('TR000148', 'MAS001', 1, 1, NULL, 19, 'Eceran', 1197000),
('TR000148', 'MAS004', 4, 7, NULL, 15, 'Eceran', 525000),
('TR000149', 'MAS001', 1, 1, NULL, 19, 'Eceran', 1197000),
('TR000149', 'MAS003', 3, 1, NULL, 45, 'Eceran', 270000),
('TR000149', 'MAS004', 4, 7, NULL, 12, 'Eceran', 420000),
('TR000150', 'MAS001', 1, 1, NULL, 25, 'Eceran', 1575000),
('TR000150', 'MAS003', 3, 1, NULL, 39, 'Eceran', 234000),
('TR000150', 'MAS004', 4, 7, NULL, 10, 'Eceran', 350000),
('TR000151', 'MAS001', 1, 1, NULL, 19, 'Eceran', 1197000),
('TR000151', 'MAS003', 3, 1, NULL, 32, 'Eceran', 192000),
('TR000151', 'MAS004', 4, 7, NULL, 13, 'Eceran', 455000),
('TR000152', 'MAS001', 1, 1, NULL, 25, 'Eceran', 1575000),
('TR000152', 'MAS003', 3, 1, NULL, 37, 'Eceran', 222000),
('TR000153', 'MAS001', 1, 1, NULL, 18, 'Eceran', 1134000),
('TR000153', 'MAS003', 3, 1, NULL, 39, 'Eceran', 234000),
('TR000153', 'MAS004', 4, 7, NULL, 20, 'Eceran', 700000),
('TR000154', 'MAS001', 1, 1, NULL, 19, 'Eceran', 1197000),
('TR000154', 'MAS003', 3, 1, NULL, 41, 'Eceran', 246000),
('TR000154', 'MAS004', 4, 7, NULL, 11, 'Eceran', 385000),
('TR000155', 'MAS001', 1, 1, NULL, 20, 'Eceran', 1260000),
('TR000155', 'MAS003', 3, 1, NULL, 39, 'Eceran', 234000),
('TR000155', 'MAS004', 4, 7, NULL, 19, 'Eceran', 665000),
('TR000156', 'MAS001', 1, 1, NULL, 21, 'Eceran', 1323000),
('TR000156', 'MAS003', 3, 1, NULL, 26, 'Eceran', 156000),
('TR000157', 'MAS001', 1, 1, NULL, 27, 'Eceran', 1701000),
('TR000158', 'MAS001', 1, 1, NULL, 27, 'Eceran', 1701000),
('TR000159', 'MAS001', 1, 1, NULL, 28, 'Eceran', 1764000),
('TR000159', 'MAS003', 3, 1, NULL, 17, 'Eceran', 102000),
('TR000160', 'MAS001', 1, 1, NULL, 21, 'Eceran', 1323000),
('TR000161', 'MAS001', 1, 1, NULL, 28, 'Eceran', 1764000),
('TR000161', 'MAS003', 3, 1, NULL, 29, 'Eceran', 174000),
('TR000161', 'MAS004', 4, 7, NULL, 14, 'Eceran', 490000),
('TR000162', 'MAS001', 1, 1, NULL, 28, 'Eceran', 1764000),
('TR000163', 'MAS001', 1, 1, NULL, 20, 'Eceran', 1260000),
('TR000163', 'MAS003', 3, 1, NULL, 30, 'Eceran', 180000),
('TR000163', 'MAS004', 4, 7, NULL, 24, 'Eceran', 840000),
('TR000164', 'MAS001', 1, 1, NULL, 26, 'Eceran', 1638000),
('TR000164', 'MAS003', 3, 1, NULL, 32, 'Eceran', 192000),
('TR000164', 'MAS004', 4, 7, NULL, 23, 'Eceran', 805000),
('TR000165', 'MAS001', 1, 1, NULL, 25, 'Eceran', 1575000),
('TR000165', 'MAS003', 3, 1, NULL, 23, 'Eceran', 138000),
('TR000166', 'MAS001', 1, 1, NULL, 25, 'Eceran', 1575000),
('TR000166', 'MAS003', 3, 1, NULL, 29, 'Eceran', 174000),
('TR000166', 'MAS004', 4, 7, NULL, 9, 'Eceran', 315000),
('TR000167', 'MAS001', 1, 1, NULL, 28, 'Eceran', 1764000),
('TR000168', 'MAS001', 1, 1, NULL, 22, 'Eceran', 1386000),
('TR000168', 'MAS003', 3, 1, NULL, 41, 'Eceran', 246000),
('TR000169', 'MAS001', 1, 1, NULL, 25, 'Eceran', 1575000),
('TR000170', 'MAS001', 1, 1, NULL, 24, 'Eceran', 1512000),
('TR000170', 'MAS003', 3, 1, NULL, 17, 'Eceran', 102000),
('TR000170', 'MAS004', 4, 7, NULL, 10, 'Eceran', 350000),
('TR000171', 'MAS001', 1, 1, NULL, 24, 'Eceran', 1512000),
('TR000171', 'MAS003', 3, 1, NULL, 34, 'Eceran', 204000),
('TR000171', 'MAS004', 4, 7, NULL, 16, 'Eceran', 560000),
('TR000171', 'MAS002', 2, 10, NULL, 191, 'Borongan', 9550000),
('TR000172', 'MAS001', 1, 1, NULL, 27, 'Eceran', 1701000),
('TR000173', 'MAS001', 1, 1, NULL, 21, 'Eceran', 1323000),
('TR000173', 'MAS004', 4, 7, NULL, 21, 'Eceran', 735000),
('TR000174', 'MAS001', 1, 1, NULL, 23, 'Eceran', 1449000),
('TR000175', 'MAS001', 1, 1, NULL, 27, 'Eceran', 1701000),
('TR000175', 'MAS003', 3, 1, NULL, 31, 'Eceran', 186000),
('TR000176', 'MAS001', 1, 1, NULL, 22, 'Eceran', 1386000),
('TR000176', 'MAS004', 4, 7, NULL, 20, 'Eceran', 700000),
('TR000176', 'MAS002', 2, 10, NULL, 239, 'Borongan', 11950000),
('TR000177', 'MAS001', 1, 1, NULL, 23, 'Eceran', 1449000),
('TR000177', 'MAS003', 3, 1, NULL, 33, 'Eceran', 198000),
('TR000177', 'MAS004', 4, 7, NULL, 19, 'Eceran', 665000),
('TR000177', 'MAS002', 2, 10, NULL, 182, 'Borongan', 9100000),
('TR000178', 'MAS001', 1, 1, NULL, 22, 'Eceran', 1386000),
('TR000178', 'MAS003', 3, 1, NULL, 12, 'Eceran', 72000),
('TR000178', 'MAS004', 4, 7, NULL, 10, 'Eceran', 350000),
('TR000179', 'MAS001', 1, 1, NULL, 26, 'Eceran', 1638000),
('TR000179', 'MAS003', 3, 1, NULL, 36, 'Eceran', 216000),
('TR000180', 'MAS001', 1, 1, NULL, 19, 'Eceran', 1197000),
('TR000180', 'MAS003', 3, 1, NULL, 27, 'Eceran', 162000),
('TR000180', 'MAS004', 4, 7, NULL, 12, 'Eceran', 420000),
('TR000181', 'MAS001', 1, 1, NULL, 26, 'Eceran', 1638000),
('TR000181', 'MAS004', 4, 7, NULL, 13, 'Eceran', 455000),
('TR000182', 'MAS001', 1, 1, NULL, 24, 'Eceran', 1512000),
('TR000182', 'MAS003', 3, 1, NULL, 44, 'Eceran', 264000),
('TR000182', 'MAS004', 4, 7, NULL, 19, 'Eceran', 665000),
('TR000183', 'MAS001', 1, 1, NULL, 19, 'Eceran', 1197000),
('TR000183', 'MAS003', 3, 1, NULL, 28, 'Eceran', 168000),
('TR000183', 'MAS004', 4, 7, NULL, 19, 'Eceran', 665000),
('TR000184', 'MAS001', 1, 1, NULL, 19, 'Eceran', 1197000),
('TR000184', 'MAS003', 3, 1, NULL, 33, 'Eceran', 198000),
('TR000185', 'MAS001', 1, 1, NULL, 22, 'Eceran', 1386000),
('TR000186', 'MAS001', 1, 1, NULL, 25, 'Eceran', 1575000),
('TR000187', 'MAS001', 1, 1, NULL, 22, 'Eceran', 1386000),
('TR000187', 'MAS003', 3, 1, NULL, 25, 'Eceran', 150000),
('TR000187', 'MAS004', 4, 7, NULL, 20, 'Eceran', 700000),
('TR000188', 'MAS001', 1, 1, NULL, 19, 'Eceran', 1197000),
('TR000188', 'MAS003', 3, 1, NULL, 39, 'Eceran', 234000),
('TR000188', 'MAS004', 4, 7, NULL, 8, 'Eceran', 280000),
('TR000189', 'MAS001', 1, 1, NULL, 26, 'Eceran', 1638000),
('TR000189', 'MAS003', 3, 1, NULL, 34, 'Eceran', 204000),
('TR000189', 'MAS004', 4, 7, NULL, 8, 'Eceran', 280000),
('TR000190', 'MAS001', 1, 1, NULL, 25, 'Eceran', 1575000),
('TR000190', 'MAS003', 3, 1, NULL, 34, 'Eceran', 204000),
('TR000191', 'MAS001', 1, 1, NULL, 21, 'Eceran', 1323000),
('TR000191', 'MAS003', 3, 1, NULL, 31, 'Eceran', 186000),
('TR000192', 'MAS001', 1, 1, NULL, 22, 'Eceran', 1386000),
('TR000193', 'MAS001', 1, 1, NULL, 20, 'Eceran', 1260000),
('TR000193', 'MAS004', 4, 7, NULL, 9, 'Eceran', 315000),
('TR000194', 'MAS001', 1, 1, NULL, 21, 'Eceran', 1323000),
('TR000194', 'MAS002', 2, 10, NULL, 186, 'Borongan', 9300000),
('TR000195', 'MAS001', 1, 1, NULL, 24, 'Eceran', 1512000),
('TR000196', 'MAS001', 1, 1, NULL, 23, 'Eceran', 1449000),
('TR000196', 'MAS003', 3, 1, NULL, 27, 'Eceran', 162000),
('TR000197', 'MAS001', 1, 1, NULL, 20, 'Eceran', 1260000),
('TR000197', 'MAS003', 3, 1, NULL, 12, 'Eceran', 72000),
('TR000198', 'MAS001', 1, 1, NULL, 21, 'Eceran', 1323000),
('TR000198', 'MAS003', 3, 1, NULL, 31, 'Eceran', 186000),
('TR000199', 'MAS001', 1, 1, NULL, 25, 'Eceran', 1575000),
('TR000200', 'MAS001', 1, 1, NULL, 22, 'Eceran', 1386000),
('TR000200', 'MAS003', 3, 1, NULL, 29, 'Eceran', 174000),
('TR000201', 'MAS001', 1, 1, NULL, 18, 'Eceran', 1134000),
('TR000201', 'MAS003', 3, 1, NULL, 20, 'Eceran', 120000),
('TR000202', 'MAS001', 1, 1, NULL, 21, 'Eceran', 1323000),
('TR000203', 'MAS001', 1, 1, NULL, 24, 'Eceran', 1512000),
('TR000204', 'MAS001', 1, 1, NULL, 20, 'Eceran', 1260000),
('TR000204', 'MAS003', 3, 1, NULL, 17, 'Eceran', 102000),
('TR000204', 'MAS004', 4, 7, NULL, 17, 'Eceran', 595000),
('TR000205', 'MAS001', 1, 1, NULL, 19, 'Eceran', 1197000),
('TR000205', 'MAS003', 3, 1, NULL, 39, 'Eceran', 234000),
('TR000205', 'MAS004', 4, 7, NULL, 19, 'Eceran', 665000),
('TR000205', 'MAS002', 2, 10, NULL, 208, 'Borongan', 10400000),
('TR000206', 'MAS001', 1, 1, NULL, 22, 'Eceran', 1386000),
('TR000206', 'MAS003', 3, 1, NULL, 36, 'Eceran', 216000),
('TR000207', 'MAS001', 1, 1, NULL, 19, 'Eceran', 1197000),
('TR000207', 'MAS003', 3, 1, NULL, 45, 'Eceran', 270000),
('TR000207', 'MAS004', 4, 7, NULL, 16, 'Eceran', 560000),
('TR000207', 'MAS002', 2, 10, NULL, 210, 'Borongan', 10500000),
('TR000208', 'MAS001', 1, 1, NULL, 23, 'Eceran', 1449000),
('TR000208', 'MAS004', 4, 7, NULL, 14, 'Eceran', 490000),
('TR000209', 'MAS001', 1, 1, NULL, 24, 'Eceran', 1512000),
('TR000209', 'MAS003', 3, 1, NULL, 28, 'Eceran', 168000),
('TR000210', 'MAS001', 1, 1, NULL, 25, 'Eceran', 1575000),
('TR000210', 'MAS003', 3, 1, NULL, 42, 'Eceran', 252000),
('TR000211', 'MAS001', 1, 1, NULL, 25, 'Eceran', 1575000),
('TR000211', 'MAS003', 3, 1, NULL, 30, 'Eceran', 180000),
('TR000212', 'MAS001', 1, 1, NULL, 28, 'Eceran', 1764000),
('TR000212', 'MAS003', 3, 1, NULL, 54, 'Eceran', 324000),
('TR000212', 'MAS004', 4, 7, NULL, 10, 'Eceran', 350000),
('TR000212', 'MAS002', 2, 10, NULL, 138, 'Borongan', 6900000),
('TR000213', 'MAS001', 1, 1, NULL, 24, 'Eceran', 1512000),
('TR000213', 'MAS003', 3, 1, NULL, 44, 'Eceran', 264000),
('TR000214', 'MAS001', 1, 1, NULL, 26, 'Eceran', 1638000),
('TR000214', 'MAS003', 3, 1, NULL, 22, 'Eceran', 132000),
('TR000214', 'MAS004', 4, 7, NULL, 23, 'Eceran', 805000),
('TR000215', 'MAS001', 1, 1, NULL, 27, 'Eceran', 1701000),
('TR000215', 'MAS003', 3, 1, NULL, 40, 'Eceran', 240000),
('TR000216', 'MAS001', 1, 1, NULL, 24, 'Eceran', 1512000),
('TR000216', 'MAS004', 4, 7, NULL, 19, 'Eceran', 665000),
('TR000217', 'MAS001', 1, 1, NULL, 25, 'Eceran', 1575000),
('TR000217', 'MAS003', 3, 1, NULL, 28, 'Eceran', 168000),
('TR000217', 'MAS004', 4, 7, NULL, 12, 'Eceran', 420000),
('TR000217', 'MAS002', 2, 10, NULL, 193, 'Borongan', 9650000),
('TR000218', 'MAS001', 1, 1, NULL, 28, 'Eceran', 1764000),
('TR000218', 'MAS003', 3, 1, NULL, 23, 'Eceran', 138000),
('TR000219', 'MAS001', 1, 1, NULL, 28, 'Eceran', 1764000),
('TR000219', 'MAS003', 3, 1, NULL, 29, 'Eceran', 174000),
('TR000220', 'MAS001', 1, 1, NULL, 25, 'Eceran', 1575000),
('TR000220', 'MAS003', 3, 1, NULL, 25, 'Eceran', 150000),
('TR000221', 'MAS001', 1, 1, NULL, 22, 'Eceran', 1386000),
('TR000221', 'MAS003', 3, 1, NULL, 46, 'Eceran', 276000),
('TR000222', 'MAS001', 1, 1, NULL, 25, 'Eceran', 1575000),
('TR000223', 'MAS001', 1, 1, NULL, 25, 'Eceran', 1575000),
('TR000223', 'MAS003', 3, 1, NULL, 42, 'Eceran', 252000),
('TR000224', 'MAS001', 1, 1, NULL, 26, 'Eceran', 1638000),
('TR000224', 'MAS003', 3, 1, NULL, 46, 'Eceran', 276000),
('TR000225', 'MAS001', 1, 1, NULL, 26, 'Eceran', 1638000),
('TR000225', 'MAS002', 2, 10, NULL, 149, 'Borongan', 7450000),
('TR000226', 'MAS001', 1, 1, NULL, 26, 'Eceran', 1638000),
('TR000226', 'MAS003', 3, 1, NULL, 29, 'Eceran', 174000),
('TR000227', 'MAS001', 1, 1, NULL, 24, 'Eceran', 1512000),
('TR000227', 'MAS003', 3, 1, NULL, 25, 'Eceran', 150000),
('TR000227', 'MAS004', 4, 7, NULL, 22, 'Eceran', 770000),
('TR000228', 'MAS001', 1, 1, NULL, 21, 'Eceran', 1323000),
('TR000228', 'MAS003', 3, 1, NULL, 30, 'Eceran', 180000),
('TR000229', 'MAS001', 1, 1, NULL, 22, 'Eceran', 1386000),
('TR000229', 'MAS003', 3, 1, NULL, 36, 'Eceran', 216000),
('TR000230', 'MAS001', 1, 1, NULL, 21, 'Eceran', 1323000),
('TR000230', 'MAS003', 3, 1, NULL, 36, 'Eceran', 216000),
('TR0235', 'MAS001', 1, 1, NULL, 2, 'Eceran', 126000),
('TR0235', 'MAS002', 2, 10, NULL, 2, 'Eceran', 100000),
('TR0236', 'MAS001', 1, 1, NULL, 1, 'Eceran', 63000),
('TR0236', 'MAS002', 2, 10, NULL, 1, 'Eceran', 50000);

-- --------------------------------------------------------

--
-- Table structure for table `jenisbarang`
--

CREATE TABLE `jenisbarang` (
  `IdJenisBarang` int(11) NOT NULL,
  `JenisBarang` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jenisbarang`
--

INSERT INTO `jenisbarang` (`IdJenisBarang`, `JenisBarang`) VALUES
(4, 'Bovenlis'),
(7, 'Kusen'),
(6, 'Roster'),
(8, 'Test');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

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
(17, '2026_02_05_061812_add_forecast_columns_to_produk_table', 15),
(18, '2026_03_27_120000_add_data_type_to_detail_transaksi_table', 16),
(19, '2026_04_03_090000_drop_customukuran_from_detail_transaksi_table', 17),
(20, '2026_04_03_090100_drop_design_file_from_detail_transaksi_table', 18),
(21, '2026_04_03_100000_add_mae_score_and_wmape_score_to_produk_table', 19),
(22, '2026_04_03_110000_add_model_versions_and_rmse_to_produk_table', 20),
(23, '2026_04_04_000000_create_model_histories_table', 21),
(24, '2026_04_04_000100_drop_model_version_columns_from_produk_table', 22),
(25, '0001_01_01_000000_create_users_table', 23),
(26, '0001_01_01_000001_create_cache_table', 23),
(27, '0001_01_01_000002_create_jobs_table', 23),
(28, '2016_06_01_000001_create_oauth_auth_codes_table', 23),
(29, '2016_06_01_000002_create_oauth_access_tokens_table', 23),
(30, '2016_06_01_000003_create_oauth_refresh_tokens_table', 23),
(31, '2016_06_01_000004_create_oauth_clients_table', 23),
(32, '2024_06_01_000001_create_oauth_device_codes_table', 23),
(33, '2025_07_14_130114_laratrust_setup_tables', 23),
(34, '2025_08_11_041402_fix_produk_foreign_keys', 23),
(35, '2025_08_11_043608_modify_detail_motif_table', 23),
(36, '2025_08_13_083359_update_detail_harga_and_transaksi_tables', 23),
(37, '2025_08_13_083650_remove_address_id_from_transaksi', 23),
(38, '2025_08_24_121847_add_ongkir_to_transaksi_table', 23),
(39, '2025_08_24_134114_add_id_ukuran_to_detail_harga_table', 23),
(40, '2025_10_03_054801_add_nama_produk_to_produk_table', 23),
(41, '2026_02_05_061812_add_forecast_columns_to_produk_table', 23),
(42, '2026_03_27_120000_add_data_type_to_detail_transaksi_table', 23),
(43, '2026_04_03_090000_drop_customukuran_from_detail_transaksi_table', 23),
(44, '2026_04_03_090100_drop_design_file_from_detail_transaksi_table', 23),
(45, '2026_04_03_100000_add_mae_score_and_wmape_score_to_produk_table', 23),
(46, '2026_04_03_110000_add_model_versions_and_rmse_to_produk_table', 23),
(47, '2026_04_04_000000_create_model_histories_table', 23),
(48, '2026_04_04_000100_drop_model_version_columns_from_produk_table', 23),
(49, '2026_06_15_000001_drop_unused_barang_tables_and_triggers', 24),
(50, '2026_06_15_000002_add_id_column_to_produk_table', 24),
(51, '2026_06_15_000003_add_produk_id_to_child_tables', 24),
(52, '2026_06_15_000004_swap_primary_key_rename_idRoster_to_sku', 25),
(53, '2026_06_15_000005_reestablish_child_foreign_keys', 26),
(54, '2026_06_22_000001_add_b2b_b2c_fields_to_users_table', 26),
(55, '2026_06_22_000002_add_address_id_to_detail_harga_table', 26),
(56, '2026_06_22_000003_add_ceo_workflow_status_to_transaksi_table', 26),
(57, '2026_06_22_000004_add_harga_satuan_snapshot_to_detail_transaksi_table', 26);

-- --------------------------------------------------------

--
-- Table structure for table `model_histories`
--

CREATE TABLE `model_histories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_roster` varchar(13) NOT NULL,
  `produk_id` bigint(20) UNSIGNED DEFAULT NULL,
  `model_type` varchar(20) NOT NULL,
  `version_id` varchar(60) NOT NULL,
  `wmape_score` double DEFAULT NULL,
  `mae_score` double DEFAULT NULL,
  `rmse_score` double DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `model_histories`
--

INSERT INTO `model_histories` (`id`, `id_roster`, `produk_id`, `model_type`, `version_id`, `wmape_score`, `mae_score`, `rmse_score`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'MAS001', 1, 'lstm', 'v_original_lstm_lstm_eceran_original', NULL, NULL, NULL, 1, '2026-04-04 04:17:27', '2026-04-04 04:17:27'),
(2, 'MAS001', 1, 'prophet', 'v_legacy_prophet_prophet_eceran_tuned', NULL, NULL, NULL, 1, '2026-04-04 04:17:27', '2026-04-04 04:17:27'),
(3, 'MAS002', 2, 'lstm', 'v_original_lstm_lstm_eceran_original', NULL, NULL, NULL, 1, '2026-04-04 04:17:27', '2026-04-04 04:17:27'),
(4, 'MAS002', 2, 'prophet', 'v_legacy_prophet_prophet_eceran_tuned', NULL, NULL, NULL, 1, '2026-04-04 04:17:27', '2026-04-04 04:17:27'),
(5, 'MAS003', 3, 'lstm', 'v_original_lstm_lstm_eceran_original', NULL, NULL, NULL, 1, '2026-04-04 04:17:27', '2026-04-04 04:17:27'),
(6, 'MAS003', 3, 'prophet', 'v_legacy_prophet_prophet_eceran_tuned', NULL, NULL, NULL, 1, '2026-04-04 04:17:27', '2026-04-04 04:17:27');

-- --------------------------------------------------------

--
-- Table structure for table `motif_roster`
--

CREATE TABLE `motif_roster` (
  `IdMotif` int(11) NOT NULL,
  `nama_motif` varchar(35) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `motif_roster`
--

INSERT INTO `motif_roster` (`IdMotif`, `nama_motif`) VALUES
(1, 'Classical Brown'),
(4, 'Modern Gray'),
(5, 'Elegant White'),
(6, 'Burem'),
(7, 'Beton'),
(8, '3D'),
(9, 'Ventalis Slip 2S'),
(10, 'Krepyak'),
(11, 'Lubang 4'),
(12, 'Putih');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
('jasjus148@gmail.com', '$2y$12$4Q6l4BNrx8fPE3Dfxt87ge7FQfe2FU85OVS6heZrY/2JUHddvbPNO', '2024-06-10 05:50:08');

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sku` varchar(13) NOT NULL,
  `NamaProduk` varchar(255) DEFAULT NULL,
  `id_jenis` int(11) NOT NULL,
  `id_tipe` int(11) DEFAULT NULL,
  `id_motif` int(11) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `Img` varchar(255) DEFAULT NULL,
  `deskripsi` varchar(1500) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp(),
  `forecasted_demand` float DEFAULT NULL COMMENT 'Next month predicted demand from AI',
  `mae_score` double DEFAULT NULL,
  `rmse_score` double DEFAULT NULL,
  `wmape_score` double DEFAULT NULL,
  `forecast_model` varchar(20) DEFAULT NULL COMMENT 'Model used: lstm, prophet, or sma',
  `safety_stock` int(11) NOT NULL DEFAULT 70 COMMENT 'Minimum stock threshold (1 batch = 70 pcs)',
  `forecast_status` enum('critical','low','safe','overstock') NOT NULL DEFAULT 'safe',
  `last_forecast_at` timestamp NULL DEFAULT NULL COMMENT 'When forecast was last calculated'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id`, `sku`, `NamaProduk`, `id_jenis`, `id_tipe`, `id_motif`, `stock`, `Img`, `deskripsi`, `created_at`, `updated_at`, `forecasted_demand`, `mae_score`, `rmse_score`, `wmape_score`, `forecast_model`, `safety_stock`, `forecast_status`, `last_forecast_at`) VALUES
(1, 'MAS001', 'Roster Mukura Classical Brown', 6, 1, 1, 200, 'produk/EFmsy8oJPHfZQvECJCcBRp2qmEQUJt6fB12DrVB6.png', 'a', '2025-08-16 21:56:25', '2026-04-15 18:26:25', 161.77, 54.77, 54.77, 7.84, 'lstm', 70, 'low', '2026-04-15 18:26:25'),
(2, 'MAS002', 'Bovenlis Jendela Beton', 4, 5, 7, 500, 'produk/d7lZuq0ffD91M6SLjzMuc32p5AvRqqLhnisCkksC.png', 'BOVENLIS', '2025-09-17 17:11:16', '2026-04-15 18:26:26', 160.32, 54.77, 54.77, 7.84, 'lstm', 70, 'safe', '2026-04-15 18:26:26'),
(3, 'MAS003', 'Roster Biasa 3D', 6, 6, 8, 500, 'produk/Xu8TqzaoC8yaLlyRFDl495FPw51QvMjkTj3wVSIw.jpg', 'Bosan dengan dinding yang datar dan monoton? Saatnya beralih ke Roster Motif 3D kami. Ini bukan sekadar lubang angin biasa; ini adalah sebuah karya seni fungsional yang dirancang untuk mengubah fasad atau ruangan Anda menjadi sebuah statement desain yang memukau.\r\n\r\nDibuat dengan presisi tinggi, setiap motif timbul (3D) dirancang untuk \"bermain\" dengan cahaya. Seiring pergerakan matahari, Anda akan menyaksikan permainan bayangan yang dinamis dan selalu berubah, memberikan kesan \"hidup\" dan eksklusif pada bangunan Anda.\r\n\r\nKeunggulan Utama:\r\n\r\nVisual Tiga Dimensi: Motif yang menonjol memberikan kedalaman visual yang tidak bisa didapat dari roster datar.\r\n\r\nPermainan Bayangan Estetis: Menciptakan efek bayangan yang artistik dan dinamis, membuat dinding Anda tidak pernah membosankan.\r\n\r\nSirkulasi Udara Optimal: Tetap fungsional sebagai ventilasi untuk menjaga hunian tetap sejuk dan sehat.\r\n\r\nMaterial Berkualitas Tinggi: Terbuat dari [Sebutkan Bahan, misal: GRC / Beton Bertulang] yang kuat, tahan lama, dan tahan terhadap cuaca ekstrem.\r\n\r\nPrivasi Terjaga: Memberikan privasi tanpa menghalangi aliran udara dan cahaya alami.\r\n\r\nAplikasi Ideal: Fasad rumah minimalis, secondary skin bangunan, pagar modern, partisi interior (sekat ruangan), dinding dekoratif kafe, atau aksen pada area taman.\r\n\r\nPesan Sekarang dan transformasikan bangunan Anda dengan sentuhan tiga dimensi yang elegan.', '2025-11-02 08:09:40', '2026-04-15 18:26:27', 163.84, 54.77, 54.77, 7.84, 'lstm', 70, 'safe', '2026-04-15 18:26:27'),
(4, 'MAS004', 'Roster Beton Lubang 4', 6, 7, 11, 100, 'produk/m1bThGxz63g9lurr4KZVqCCoI64NBTuwDzX0JDNh.png', 'Tes1', '2026-04-06 03:44:14', '2026-04-06 03:44:14', NULL, NULL, NULL, NULL, NULL, 70, 'safe', NULL),
(5, 'MAS005', 'Bovenlis Beton Putih', 4, 7, 12, 10, 'produk/HmCzKaO3dE89yjXZLKcHCM6XecE9B90S7gv6gXuH.png', 'ww', '2026-06-06 00:34:19', '2026-06-06 00:34:19', NULL, NULL, NULL, NULL, NULL, 70, 'safe', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `produk_size`
--

CREATE TABLE `produk_size` (
  `IdRoster` varchar(13) NOT NULL,
  `produk_id` bigint(20) UNSIGNED DEFAULT NULL,
  `id_ukuran` int(11) NOT NULL,
  `harga` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk_size`
--

INSERT INTO `produk_size` (`IdRoster`, `produk_id`, `id_ukuran`, `harga`, `created_at`, `updated_at`) VALUES
('MAS001', 1, 1, 63000, '2025-08-16 21:56:25', '2026-04-06 19:50:52'),
('MAS001', 1, 7, 70000, '2025-08-16 21:56:25', '2026-04-06 19:50:52'),
('MAS002', 2, 10, 50000, '2025-09-17 17:11:16', '2025-11-02 08:52:21'),
('MAS003', 3, 1, 6000, '2025-11-02 08:09:40', '2025-11-02 08:09:40'),
('MAS004', 4, 1, 9000, '2026-04-06 03:44:14', '2026-04-06 03:44:14'),
('MAS005', 5, 1, 25000, '2026-06-06 00:34:19', '2026-06-06 00:34:19'),
('MAS005', 5, 7, 30000, '2026-06-06 00:34:19', '2026-06-06 00:34:19');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `display_name` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `display_name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'Admin', 'Admin', '2024-02-28 02:12:01', '2024-02-28 02:12:01'),
(2, 'user', 'User', 'User', '2024-02-28 02:12:01', '2024-02-28 02:12:01');

-- --------------------------------------------------------

--
-- Table structure for table `role_user`
--

CREATE TABLE `role_user` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `user_type` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_user`
--

INSERT INTO `role_user` (`role_id`, `user_id`, `user_type`) VALUES
(1, 1, 'App\\Models\\User'),
(1, 2, 'App\\Models\\User'),
(2, 5, 'App\\Models\\User'),
(2, 6, 'App\\Models\\User'),
(2, 81, 'App\\Models\\User'),
(2, 83, 'App\\Models\\User'),
(2, 84, 'App\\Models\\User');

-- --------------------------------------------------------

--
-- Table structure for table `size`
--

CREATE TABLE `size` (
  `id_ukuran` int(11) NOT NULL,
  `nama` varchar(50) NOT NULL,
  `panjang` int(11) NOT NULL,
  `lebar` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `size`
--

INSERT INTO `size` (`id_ukuran`, `nama`, `panjang`, `lebar`) VALUES
(1, 'Standard', 20, 20),
(7, 'Besar', 40, 60),
(10, 'Besar', 40, 70);

-- --------------------------------------------------------

--
-- Table structure for table `tipe_roster`
--

CREATE TABLE `tipe_roster` (
  `IdTipe` int(11) NOT NULL,
  `namaTipe` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tipe_roster`
--

INSERT INTO `tipe_roster` (`IdTipe`, `namaTipe`) VALUES
(1, 'Mukura'),
(2, 'Bata Merah'),
(3, 'Bata Putih'),
(4, 'Paving Block'),
(5, 'Jendela'),
(6, 'Biasa'),
(7, 'Beton'),
(8, 'Serut Pintu');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `IdTransaksi` varchar(10) NOT NULL,
  `id_admin` bigint(20) NOT NULL DEFAULT 0,
  `id_customer` bigint(20) NOT NULL,
  `address_id` bigint(20) UNSIGNED DEFAULT NULL,
  `Bayar` int(11) NOT NULL,
  `GrandTotal` int(11) NOT NULL,
  `tglTransaksi` datetime NOT NULL,
  `StatusPembayaran` varchar(20) NOT NULL,
  `StatusPesanan` varchar(20) DEFAULT NULL,
  `tglUpdate` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `shipping_method` varchar(255) DEFAULT NULL,
  `delivery_method` varchar(255) DEFAULT NULL,
  `shipping_type` varchar(255) DEFAULT NULL,
  `ongkir` int(11) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `workflow_status` varchar(30) NOT NULL DEFAULT 'Draft' COMMENT 'CEO approval and payment workflow state: Draft, Menunggu Pembayaran, Paid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`IdTransaksi`, `id_admin`, `id_customer`, `address_id`, `Bayar`, `GrandTotal`, `tglTransaksi`, `StatusPembayaran`, `StatusPesanan`, `tglUpdate`, `created_at`, `updated_at`, `shipping_method`, `delivery_method`, `shipping_type`, `ongkir`, `notes`, `workflow_status`) VALUES
('TR000001', 1, 4, 2, 1898000, 1898000, '2024-01-10 14:47:18', 'Lunas', 'Diterima', '2024-01-10 14:47:18', '2024-01-10 07:47:18', '2024-01-10 07:47:18', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000002', 1, 4, 2, 1478000, 1478000, '2024-01-14 16:54:19', 'Lunas', 'Diterima', '2024-01-14 16:54:19', '2024-01-14 09:54:19', '2024-01-14 09:54:19', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000003', 1, 4, 2, 1511000, 1511000, '2024-01-04 12:54:50', 'Lunas', 'Diterima', '2024-01-04 12:54:50', '2024-01-04 05:54:50', '2024-01-04 05:54:50', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000004', 1, 4, 2, 1721000, 1721000, '2024-01-31 11:26:21', 'Lunas', 'Diterima', '2024-01-31 11:26:21', '2024-01-31 04:26:21', '2024-01-31 04:26:21', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000005', 1, 4, 2, 1739000, 1739000, '2024-01-11 15:02:44', 'Lunas', 'Diterima', '2024-01-11 15:02:44', '2024-01-11 08:02:44', '2024-01-11 08:02:44', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000006', 1, 4, 2, 1562000, 1562000, '2024-01-05 12:00:21', 'Lunas', 'Diterima', '2024-01-05 12:00:21', '2024-01-05 05:00:21', '2024-01-05 05:00:21', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000007', 1, 4, 2, 1589000, 1589000, '2024-02-16 13:50:01', 'Lunas', 'Diterima', '2024-02-16 13:50:01', '2024-02-16 06:50:01', '2024-02-16 06:50:01', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000008', 1, 4, 2, 1388000, 1388000, '2024-02-01 16:20:12', 'Lunas', 'Diterima', '2024-02-01 16:20:12', '2024-02-01 09:20:12', '2024-02-01 09:20:12', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000009', 1, 4, 2, 1911000, 1911000, '2024-02-27 08:47:22', 'Lunas', 'Diterima', '2024-02-27 08:47:22', '2024-02-27 01:47:22', '2024-02-27 01:47:22', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000010', 1, 4, 2, 13165000, 13165000, '2024-02-18 10:29:36', 'Lunas', 'Diterima', '2024-02-18 10:29:36', '2024-02-18 03:29:36', '2024-02-18 03:29:36', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000011', 1, 4, 2, 1592000, 1592000, '2024-02-21 10:15:05', 'Lunas', 'Diterima', '2024-02-21 10:15:05', '2024-02-21 03:15:05', '2024-02-21 03:15:05', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000012', 1, 4, 2, 2312000, 2312000, '2024-02-25 09:00:44', 'Lunas', 'Diterima', '2024-02-25 09:00:44', '2024-02-25 02:00:44', '2024-02-25 02:00:44', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000013', 1, 4, 2, 11911000, 11911000, '2024-03-21 10:50:44', 'Lunas', 'Diterima', '2024-03-21 10:50:44', '2024-03-21 03:50:44', '2024-03-21 03:50:44', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000014', 1, 4, 2, 9675000, 9675000, '2024-03-23 12:45:28', 'Lunas', 'Diterima', '2024-03-23 12:45:28', '2024-03-23 05:45:28', '2024-03-23 05:45:28', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000015', 1, 4, 2, 1814000, 1814000, '2024-03-06 15:53:51', 'Lunas', 'Diterima', '2024-03-06 15:53:51', '2024-03-06 08:53:51', '2024-03-06 08:53:51', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000016', 1, 4, 2, 2364000, 2364000, '2024-03-24 09:55:43', 'Lunas', 'Diterima', '2024-03-24 09:55:43', '2024-03-24 02:55:43', '2024-03-24 02:55:43', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000017', 1, 4, 2, 1691000, 1691000, '2024-03-08 11:44:41', 'Lunas', 'Diterima', '2024-03-08 11:44:41', '2024-03-08 04:44:41', '2024-03-08 04:44:41', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000018', 1, 4, 2, 1937000, 1937000, '2024-03-12 08:36:39', 'Lunas', 'Diterima', '2024-03-12 08:36:39', '2024-03-12 01:36:39', '2024-03-12 01:36:39', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000019', 1, 4, 2, 2228000, 2228000, '2024-03-29 15:15:32', 'Lunas', 'Diterima', '2024-03-29 15:15:32', '2024-03-29 08:15:32', '2024-03-29 08:15:32', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000020', 1, 4, 2, 11939000, 11939000, '2024-03-09 13:16:50', 'Lunas', 'Diterima', '2024-03-09 13:16:50', '2024-03-09 06:16:50', '2024-03-09 06:16:50', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000021', 1, 4, 2, 13102000, 13102000, '2024-03-28 12:54:29', 'Lunas', 'Diterima', '2024-03-28 12:54:29', '2024-03-28 05:54:29', '2024-03-28 05:54:29', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000022', 1, 4, 2, 7814000, 7814000, '2024-03-27 12:30:17', 'Lunas', 'Diterima', '2024-03-27 12:30:17', '2024-03-27 05:30:17', '2024-03-27 05:30:17', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000023', 1, 4, 2, 1996000, 1996000, '2024-03-31 09:00:40', 'Lunas', 'Diterima', '2024-03-31 09:00:40', '2024-03-31 02:00:40', '2024-03-31 02:00:40', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000024', 1, 4, 2, 2332000, 2332000, '2024-03-19 16:49:46', 'Lunas', 'Diterima', '2024-03-19 16:49:46', '2024-03-19 09:49:46', '2024-03-19 09:49:46', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000025', 1, 4, 2, 1781000, 1781000, '2024-04-13 15:20:12', 'Lunas', 'Diterima', '2024-04-13 15:20:12', '2024-04-13 08:20:12', '2024-04-13 08:20:12', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000026', 1, 4, 2, 2437000, 2437000, '2024-04-04 15:16:14', 'Lunas', 'Diterima', '2024-04-04 15:16:14', '2024-04-04 08:16:14', '2024-04-04 08:16:14', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000027', 1, 4, 2, 2192000, 2192000, '2024-04-19 16:26:10', 'Lunas', 'Diterima', '2024-04-19 16:26:10', '2024-04-19 09:26:10', '2024-04-19 09:26:10', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000028', 1, 4, 2, 1964000, 1964000, '2024-04-07 12:24:09', 'Lunas', 'Diterima', '2024-04-07 12:24:09', '2024-04-07 05:24:09', '2024-04-07 05:24:09', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000029', 1, 4, 2, 10000000, 10000000, '2024-04-25 11:20:50', 'Lunas', 'Diterima', '2024-04-25 11:20:50', '2024-04-25 04:20:50', '2024-04-25 04:20:50', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000030', 1, 4, 2, 1778000, 1778000, '2024-04-11 14:14:07', 'Lunas', 'Diterima', '2024-04-11 14:14:07', '2024-04-11 07:14:07', '2024-04-11 07:14:07', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000031', 1, 4, 2, 2134000, 2134000, '2024-04-19 14:06:22', 'Lunas', 'Diterima', '2024-04-19 14:06:22', '2024-04-19 07:06:22', '2024-04-19 07:06:22', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000032', 1, 4, 2, 12715000, 12715000, '2024-04-23 14:32:47', 'Lunas', 'Diterima', '2024-04-23 14:32:47', '2024-04-23 07:32:47', '2024-04-23 07:32:47', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000033', 1, 4, 2, 1544000, 1544000, '2024-04-29 11:20:45', 'Lunas', 'Diterima', '2024-04-29 11:20:45', '2024-04-29 04:20:45', '2024-04-29 04:20:45', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000034', 1, 4, 2, 2402000, 2402000, '2024-04-03 08:17:38', 'Lunas', 'Diterima', '2024-04-03 08:17:38', '2024-04-03 01:17:38', '2024-04-03 01:17:38', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000035', 1, 4, 2, 2129000, 2129000, '2024-04-26 15:07:08', 'Lunas', 'Diterima', '2024-04-26 15:07:08', '2024-04-26 08:07:08', '2024-04-26 08:07:08', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000036', 1, 4, 2, 2150000, 2150000, '2024-04-25 10:09:16', 'Lunas', 'Diterima', '2024-04-25 10:09:16', '2024-04-25 03:09:16', '2024-04-25 03:09:16', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000037', 1, 4, 2, 1247000, 1247000, '2024-05-26 16:48:10', 'Lunas', 'Diterima', '2024-05-26 16:48:10', '2024-05-26 09:48:10', '2024-05-26 09:48:10', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000038', 1, 4, 2, 1481000, 1481000, '2024-05-01 16:22:11', 'Lunas', 'Diterima', '2024-05-01 16:22:11', '2024-05-01 09:22:11', '2024-05-01 09:22:11', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000039', 1, 4, 2, 1652000, 1652000, '2024-05-04 08:33:03', 'Lunas', 'Diterima', '2024-05-04 08:33:03', '2024-05-04 01:33:03', '2024-05-04 01:33:03', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000040', 1, 4, 2, 1637000, 1637000, '2024-05-05 13:34:52', 'Lunas', 'Diterima', '2024-05-05 13:34:52', '2024-05-05 06:34:52', '2024-05-05 06:34:52', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000041', 1, 4, 2, 1463000, 1463000, '2024-05-14 09:16:34', 'Lunas', 'Diterima', '2024-05-14 09:16:34', '2024-05-14 02:16:34', '2024-05-14 02:16:34', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000042', 1, 4, 2, 1535000, 1535000, '2024-05-30 09:42:11', 'Lunas', 'Diterima', '2024-05-30 09:42:11', '2024-05-30 02:42:11', '2024-05-30 02:42:11', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000043', 1, 4, 2, 1580000, 1580000, '2024-05-14 13:38:44', 'Lunas', 'Diterima', '2024-05-14 13:38:44', '2024-05-14 06:38:44', '2024-05-14 06:38:44', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000044', 1, 4, 2, 1688000, 1688000, '2024-05-28 09:08:56', 'Lunas', 'Diterima', '2024-05-28 09:08:56', '2024-05-28 02:08:56', '2024-05-28 02:08:56', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000045', 1, 4, 2, 1835000, 1835000, '2024-06-20 14:59:16', 'Lunas', 'Diterima', '2024-06-20 14:59:16', '2024-06-20 07:59:16', '2024-06-20 07:59:16', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000046', 1, 4, 2, 1418000, 1418000, '2024-06-19 11:28:39', 'Lunas', 'Diterima', '2024-06-19 11:28:39', '2024-06-19 04:28:39', '2024-06-19 04:28:39', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000047', 1, 4, 2, 10086000, 10086000, '2024-06-09 10:55:32', 'Lunas', 'Diterima', '2024-06-09 10:55:32', '2024-06-09 03:55:32', '2024-06-09 03:55:32', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000048', 1, 4, 2, 2292000, 2292000, '2024-06-17 10:08:43', 'Lunas', 'Diterima', '2024-06-17 10:08:43', '2024-06-17 03:08:43', '2024-06-17 03:08:43', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000049', 1, 4, 2, 1700000, 1700000, '2024-06-04 14:59:16', 'Lunas', 'Diterima', '2024-06-04 14:59:16', '2024-06-04 07:59:16', '2024-06-04 07:59:16', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000050', 1, 4, 2, 1511000, 1511000, '2024-06-15 16:53:29', 'Lunas', 'Diterima', '2024-06-15 16:53:29', '2024-06-15 09:53:29', '2024-06-15 09:53:29', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000051', 1, 4, 2, 1625000, 1625000, '2024-06-06 10:30:23', 'Lunas', 'Diterima', '2024-06-06 10:30:23', '2024-06-06 03:30:23', '2024-06-06 03:30:23', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000052', 1, 4, 2, 1841000, 1841000, '2024-06-09 08:18:57', 'Lunas', 'Diterima', '2024-06-09 08:18:57', '2024-06-09 01:18:57', '2024-06-09 01:18:57', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000053', 1, 4, 2, 2732000, 2732000, '2024-07-03 13:05:53', 'Lunas', 'Diterima', '2024-07-03 13:05:53', '2024-07-03 06:05:53', '2024-07-03 06:05:53', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000054', 1, 4, 2, 1799000, 1799000, '2024-07-26 08:49:45', 'Lunas', 'Diterima', '2024-07-26 08:49:45', '2024-07-26 01:49:45', '2024-07-26 01:49:45', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000055', 1, 4, 2, 1751000, 1751000, '2024-07-25 13:08:20', 'Lunas', 'Diterima', '2024-07-25 13:08:20', '2024-07-25 06:08:20', '2024-07-25 06:08:20', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000056', 1, 4, 2, 1499000, 1499000, '2024-07-08 11:01:00', 'Lunas', 'Diterima', '2024-07-08 11:01:00', '2024-07-08 04:01:00', '2024-07-08 04:01:00', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000057', 1, 4, 2, 2042000, 2042000, '2024-07-22 13:23:24', 'Lunas', 'Diterima', '2024-07-22 13:23:24', '2024-07-22 06:23:24', '2024-07-22 06:23:24', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000058', 1, 4, 2, 10353000, 10353000, '2024-07-29 08:43:25', 'Lunas', 'Diterima', '2024-07-29 08:43:25', '2024-07-29 01:43:25', '2024-07-29 01:43:25', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000059', 1, 4, 2, 2408000, 2408000, '2024-07-23 13:30:34', 'Lunas', 'Diterima', '2024-07-23 13:30:34', '2024-07-23 06:30:34', '2024-07-23 06:30:34', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000060', 1, 4, 2, 1751000, 1751000, '2024-07-20 16:44:26', 'Lunas', 'Diterima', '2024-07-20 16:44:26', '2024-07-20 09:44:26', '2024-07-20 09:44:26', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000061', 1, 4, 2, 1625000, 1625000, '2024-07-16 12:00:39', 'Lunas', 'Diterima', '2024-07-16 12:00:39', '2024-07-16 05:00:39', '2024-07-16 05:00:39', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000062', 1, 4, 2, 2338000, 2338000, '2024-08-13 09:39:37', 'Lunas', 'Diterima', '2024-08-13 09:39:37', '2024-08-13 02:39:37', '2024-08-13 02:39:37', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000063', 1, 4, 2, 1976000, 1976000, '2024-08-06 12:29:58', 'Lunas', 'Diterima', '2024-08-06 12:29:58', '2024-08-06 05:29:58', '2024-08-06 05:29:58', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000064', 1, 4, 2, 13993000, 13993000, '2024-08-04 15:22:26', 'Lunas', 'Diterima', '2024-08-04 15:22:26', '2024-08-04 08:22:26', '2024-08-04 08:22:26', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000065', 1, 4, 2, 2126000, 2126000, '2024-08-05 11:08:04', 'Lunas', 'Diterima', '2024-08-05 11:08:04', '2024-08-05 04:08:04', '2024-08-05 04:08:04', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000066', 1, 4, 2, 2368000, 2368000, '2024-08-11 10:57:46', 'Lunas', 'Diterima', '2024-08-11 10:57:46', '2024-08-11 03:57:46', '2024-08-11 03:57:46', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000067', 1, 4, 2, 1751000, 1751000, '2024-08-04 09:25:39', 'Lunas', 'Diterima', '2024-08-04 09:25:39', '2024-08-04 02:25:39', '2024-08-04 02:25:39', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000068', 1, 4, 2, 1751000, 1751000, '2024-08-22 09:32:54', 'Lunas', 'Diterima', '2024-08-22 09:32:54', '2024-08-22 02:32:54', '2024-08-22 02:32:54', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000069', 1, 4, 2, 2601000, 2601000, '2024-08-12 10:44:23', 'Lunas', 'Diterima', '2024-08-12 10:44:23', '2024-08-12 03:44:23', '2024-08-12 03:44:23', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000070', 1, 4, 2, 10383000, 10383000, '2024-08-15 10:09:35', 'Lunas', 'Diterima', '2024-08-15 10:09:35', '2024-08-15 03:09:35', '2024-08-15 03:09:35', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000071', 1, 4, 2, 2329000, 2329000, '2024-08-30 12:27:57', 'Lunas', 'Diterima', '2024-08-30 12:27:57', '2024-08-30 05:27:57', '2024-08-30 05:27:57', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000072', 1, 4, 2, 1640000, 1640000, '2024-09-15 16:37:04', 'Lunas', 'Diterima', '2024-09-15 16:37:04', '2024-09-15 09:37:04', '2024-09-15 09:37:04', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000073', 1, 4, 2, 1868000, 1868000, '2024-09-03 15:38:57', 'Lunas', 'Diterima', '2024-09-03 15:38:57', '2024-09-03 08:38:57', '2024-09-03 08:38:57', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000074', 1, 4, 2, 1772000, 1772000, '2024-09-16 10:09:47', 'Lunas', 'Diterima', '2024-09-16 10:09:47', '2024-09-16 03:09:47', '2024-09-16 03:09:47', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000075', 1, 4, 2, 1968000, 1968000, '2024-09-25 08:31:16', 'Lunas', 'Diterima', '2024-09-25 08:31:16', '2024-09-25 01:31:16', '2024-09-25 01:31:16', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000076', 1, 4, 2, 1562000, 1562000, '2024-09-12 15:46:52', 'Lunas', 'Diterima', '2024-09-12 15:46:52', '2024-09-12 08:46:52', '2024-09-12 08:46:52', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000077', 1, 4, 2, 1511000, 1511000, '2024-09-24 09:35:05', 'Lunas', 'Diterima', '2024-09-24 09:35:05', '2024-09-24 02:35:05', '2024-09-24 02:35:05', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000078', 1, 4, 2, 2369000, 2369000, '2024-09-22 12:40:40', 'Lunas', 'Diterima', '2024-09-22 12:40:40', '2024-09-22 05:40:40', '2024-09-22 05:40:40', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000079', 1, 4, 2, 2037000, 2037000, '2024-09-16 16:25:12', 'Lunas', 'Diterima', '2024-09-16 16:25:12', '2024-09-16 09:25:12', '2024-09-16 09:25:12', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000080', 1, 4, 2, 1973000, 1973000, '2024-10-07 12:52:43', 'Lunas', 'Diterima', '2024-10-07 12:52:43', '2024-10-07 05:52:43', '2024-10-07 05:52:43', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000081', 1, 4, 2, 1766000, 1766000, '2024-10-19 09:33:03', 'Lunas', 'Diterima', '2024-10-19 09:33:03', '2024-10-19 02:33:03', '2024-10-19 02:33:03', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000082', 1, 4, 2, 1538000, 1538000, '2024-10-30 12:02:53', 'Lunas', 'Diterima', '2024-10-30 12:02:53', '2024-10-30 05:02:53', '2024-10-30 05:02:53', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000083', 1, 4, 2, 1560000, 1560000, '2024-10-26 13:44:49', 'Lunas', 'Diterima', '2024-10-26 13:44:49', '2024-10-26 06:44:49', '2024-10-26 06:44:49', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000084', 1, 4, 2, 1657000, 1657000, '2024-10-25 08:32:11', 'Lunas', 'Diterima', '2024-10-25 08:32:11', '2024-10-25 01:32:11', '2024-10-25 01:32:11', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000085', 1, 4, 2, 1499000, 1499000, '2024-10-28 15:22:42', 'Lunas', 'Diterima', '2024-10-28 15:22:42', '2024-10-28 08:22:42', '2024-10-28 08:22:42', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000086', 1, 4, 2, 1625000, 1625000, '2024-11-29 14:14:44', 'Lunas', 'Diterima', '2024-11-29 14:14:44', '2024-11-29 07:14:44', '2024-11-29 07:14:44', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000087', 1, 4, 2, 1286000, 1286000, '2024-11-24 13:40:07', 'Lunas', 'Diterima', '2024-11-24 13:40:07', '2024-11-24 06:40:07', '2024-11-24 06:40:07', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000088', 1, 4, 2, 1184000, 1184000, '2024-11-27 14:43:36', 'Lunas', 'Diterima', '2024-11-27 14:43:36', '2024-11-27 07:43:36', '2024-11-27 07:43:36', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000089', 1, 4, 2, 2038000, 2038000, '2024-11-18 08:34:56', 'Lunas', 'Diterima', '2024-11-18 08:34:56', '2024-11-18 01:34:56', '2024-11-18 01:34:56', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000090', 1, 4, 2, 1613000, 1613000, '2024-11-01 11:19:30', 'Lunas', 'Diterima', '2024-11-01 11:19:30', '2024-11-01 04:19:30', '2024-11-01 04:19:30', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000091', 1, 4, 2, 1727000, 1727000, '2024-11-03 13:18:17', 'Lunas', 'Diterima', '2024-11-03 13:18:17', '2024-11-03 06:18:17', '2024-11-03 06:18:17', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000092', 1, 4, 2, 1568000, 1568000, '2024-11-16 11:11:24', 'Lunas', 'Diterima', '2024-11-16 11:11:24', '2024-11-16 04:11:24', '2024-11-16 04:11:24', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000093', 1, 4, 2, 1982000, 1982000, '2024-11-22 11:56:14', 'Lunas', 'Diterima', '2024-11-22 11:56:14', '2024-11-22 04:56:14', '2024-11-22 04:56:14', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000094', 1, 4, 2, 13616000, 13616000, '2024-12-28 10:24:23', 'Lunas', 'Diterima', '2024-12-28 10:24:23', '2024-12-28 03:24:23', '2024-12-28 03:24:23', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000095', 1, 4, 2, 1957000, 1957000, '2024-12-18 08:50:36', 'Lunas', 'Diterima', '2024-12-18 08:50:36', '2024-12-18 01:50:36', '2024-12-18 01:50:36', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000096', 1, 4, 2, 8096000, 8096000, '2024-12-30 10:52:52', 'Lunas', 'Diterima', '2024-12-30 10:52:52', '2024-12-30 03:52:52', '2024-12-30 03:52:52', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000097', 1, 4, 2, 8318000, 8318000, '2024-12-23 08:58:58', 'Lunas', 'Diterima', '2024-12-23 08:58:58', '2024-12-23 01:58:58', '2024-12-23 01:58:58', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000098', 1, 4, 2, 2042000, 2042000, '2024-12-15 09:36:06', 'Lunas', 'Diterima', '2024-12-15 09:36:06', '2024-12-15 02:36:06', '2024-12-15 02:36:06', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000099', 1, 4, 2, 1361000, 1361000, '2024-12-28 13:34:39', 'Lunas', 'Diterima', '2024-12-28 13:34:39', '2024-12-28 06:34:39', '2024-12-28 06:34:39', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000100', 1, 4, 2, 1436000, 1436000, '2024-12-14 08:21:06', 'Lunas', 'Diterima', '2024-12-14 08:21:06', '2024-12-14 01:21:06', '2024-12-14 01:21:06', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000101', 1, 4, 2, 1526000, 1526000, '2025-01-31 12:10:02', 'Lunas', 'Diterima', '2025-01-31 12:10:02', '2025-01-31 05:10:02', '2025-01-31 05:10:02', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000102', 1, 4, 2, 2071000, 2071000, '2025-01-28 09:06:14', 'Lunas', 'Diterima', '2025-01-28 09:06:14', '2025-01-28 02:06:14', '2025-01-28 02:06:14', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000103', 1, 4, 2, 1418000, 1418000, '2025-01-09 16:05:07', 'Lunas', 'Diterima', '2025-01-09 16:05:07', '2025-01-09 09:05:07', '2025-01-09 09:05:07', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000104', 1, 4, 2, 1625000, 1625000, '2025-01-12 10:34:23', 'Lunas', 'Diterima', '2025-01-12 10:34:23', '2025-01-12 03:34:23', '2025-01-12 03:34:23', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000105', 1, 4, 2, 1632000, 1632000, '2025-01-06 13:31:57', 'Lunas', 'Diterima', '2025-01-06 13:31:57', '2025-01-06 06:31:57', '2025-01-06 06:31:57', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000106', 1, 4, 2, 1424000, 1424000, '2025-01-23 08:29:42', 'Lunas', 'Diterima', '2025-01-23 08:29:42', '2025-01-23 01:29:42', '2025-01-23 01:29:42', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000107', 1, 4, 2, 2439000, 2439000, '2025-02-24 13:41:57', 'Lunas', 'Diterima', '2025-02-24 13:41:57', '2025-02-24 06:41:57', '2025-02-24 06:41:57', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000108', 1, 4, 2, 2108000, 2108000, '2025-02-26 08:41:32', 'Lunas', 'Diterima', '2025-02-26 08:41:32', '2025-02-26 01:41:32', '2025-02-26 01:41:32', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000109', 1, 4, 2, 1736000, 1736000, '2025-02-28 15:19:00', 'Lunas', 'Diterima', '2025-02-28 15:19:00', '2025-02-28 08:19:00', '2025-02-28 08:19:00', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000110', 1, 4, 2, 1544000, 1544000, '2025-02-06 14:23:09', 'Lunas', 'Diterima', '2025-02-06 14:23:09', '2025-02-06 07:23:09', '2025-02-06 07:23:09', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000111', 1, 4, 2, 2630000, 2630000, '2025-02-04 10:40:13', 'Lunas', 'Diterima', '2025-02-04 10:40:13', '2025-02-04 03:40:13', '2025-02-04 03:40:13', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000112', 1, 4, 2, 8116000, 8116000, '2025-02-05 13:00:39', 'Lunas', 'Diterima', '2025-02-05 13:00:39', '2025-02-05 06:00:39', '2025-02-05 06:00:39', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000113', 1, 4, 2, 2066000, 2066000, '2025-02-02 12:54:30', 'Lunas', 'Diterima', '2025-02-02 12:54:30', '2025-02-02 05:54:30', '2025-02-02 05:54:30', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000114', 1, 4, 2, 13755000, 13755000, '2025-02-08 15:05:57', 'Lunas', 'Diterima', '2025-02-08 15:05:57', '2025-02-08 08:05:57', '2025-02-08 08:05:57', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000115', 1, 4, 2, 1937000, 1937000, '2025-02-10 08:40:27', 'Lunas', 'Diterima', '2025-02-10 08:40:27', '2025-02-10 01:40:27', '2025-02-10 01:40:27', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000116', 1, 4, 2, 1991000, 1991000, '2025-02-27 09:27:11', 'Lunas', 'Diterima', '2025-02-27 09:27:11', '2025-02-27 02:27:11', '2025-02-27 02:27:11', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000117', 1, 4, 2, 2853000, 2853000, '2025-02-09 10:12:18', 'Lunas', 'Diterima', '2025-02-09 10:12:18', '2025-02-09 03:12:18', '2025-02-09 03:12:18', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000118', 1, 4, 2, 1688000, 1688000, '2025-02-17 10:42:26', 'Lunas', 'Diterima', '2025-02-17 10:42:26', '2025-02-17 03:42:26', '2025-02-17 03:42:26', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000119', 1, 4, 2, 2143000, 2143000, '2025-03-13 08:34:25', 'Lunas', 'Diterima', '2025-03-13 08:34:25', '2025-03-13 01:34:25', '2025-03-13 01:34:25', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000120', 1, 4, 2, 2451000, 2451000, '2025-03-14 08:18:14', 'Lunas', 'Diterima', '2025-03-14 08:18:14', '2025-03-14 01:18:14', '2025-03-14 01:18:14', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000121', 1, 4, 2, 11286000, 11286000, '2025-03-22 10:43:48', 'Lunas', 'Diterima', '2025-03-22 10:43:48', '2025-03-22 03:43:48', '2025-03-22 03:43:48', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000122', 1, 4, 2, 2039000, 2039000, '2025-03-02 15:11:20', 'Lunas', 'Diterima', '2025-03-02 15:11:20', '2025-03-02 08:11:20', '2025-03-02 08:11:20', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000123', 1, 4, 2, 1751000, 1751000, '2025-03-20 14:58:27', 'Lunas', 'Diterima', '2025-03-20 14:58:27', '2025-03-20 07:58:27', '2025-03-20 07:58:27', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000124', 1, 4, 2, 2434000, 2434000, '2025-03-13 13:05:28', 'Lunas', 'Diterima', '2025-03-13 13:05:28', '2025-03-13 06:05:28', '2025-03-13 06:05:28', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000125', 1, 4, 2, 2710000, 2710000, '2025-03-20 16:47:46', 'Lunas', 'Diterima', '2025-03-20 16:47:46', '2025-03-20 09:47:46', '2025-03-20 09:47:46', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000126', 1, 4, 2, 1751000, 1751000, '2025-03-17 11:10:29', 'Lunas', 'Diterima', '2025-03-17 11:10:29', '2025-03-17 04:10:29', '2025-03-17 04:10:29', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000127', 1, 4, 2, 1436000, 1436000, '2025-03-14 11:48:01', 'Lunas', 'Diterima', '2025-03-14 11:48:01', '2025-03-14 04:48:01', '2025-03-14 04:48:01', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000128', 1, 4, 2, 12182000, 12182000, '2025-03-18 13:05:19', 'Lunas', 'Diterima', '2025-03-18 13:05:19', '2025-03-18 06:05:19', '2025-03-18 06:05:19', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000129', 1, 4, 2, 2136000, 2136000, '2025-03-10 15:50:30', 'Lunas', 'Diterima', '2025-03-10 15:50:30', '2025-03-10 08:50:30', '2025-03-10 08:50:30', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000130', 1, 4, 2, 10440000, 10440000, '2025-03-16 09:42:53', 'Lunas', 'Diterima', '2025-03-16 09:42:53', '2025-03-16 02:42:53', '2025-03-16 02:42:53', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000131', 1, 4, 2, 2136000, 2136000, '2025-04-12 09:51:43', 'Lunas', 'Diterima', '2025-04-12 09:51:43', '2025-04-12 02:51:43', '2025-04-12 02:51:43', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000132', 1, 4, 2, 1919000, 1919000, '2025-04-06 12:02:26', 'Lunas', 'Diterima', '2025-04-06 12:02:26', '2025-04-06 05:02:26', '2025-04-06 05:02:26', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000133', 1, 4, 2, 11229000, 11229000, '2025-04-11 12:12:21', 'Lunas', 'Diterima', '2025-04-11 12:12:21', '2025-04-11 05:12:21', '2025-04-11 05:12:21', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000134', 1, 4, 2, 1866000, 1866000, '2025-04-03 16:55:55', 'Lunas', 'Diterima', '2025-04-03 16:55:55', '2025-04-03 09:55:55', '2025-04-03 09:55:55', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000135', 1, 4, 2, 2078000, 2078000, '2025-04-26 16:57:44', 'Lunas', 'Diterima', '2025-04-26 16:57:44', '2025-04-26 09:57:44', '2025-04-26 09:57:44', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000136', 1, 4, 2, 2010000, 2010000, '2025-04-05 12:29:13', 'Lunas', 'Diterima', '2025-04-05 12:29:13', '2025-04-05 05:29:13', '2025-04-05 05:29:13', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000137', 1, 4, 2, 2755000, 2755000, '2025-04-08 16:46:59', 'Lunas', 'Diterima', '2025-04-08 16:46:59', '2025-04-08 09:46:59', '2025-04-08 09:46:59', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000138', 1, 4, 2, 2491000, 2491000, '2025-04-21 12:13:27', 'Lunas', 'Diterima', '2025-04-21 12:13:27', '2025-04-21 05:13:27', '2025-04-21 05:13:27', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000139', 1, 4, 2, 1565000, 1565000, '2025-04-06 08:51:06', 'Lunas', 'Diterima', '2025-04-06 08:51:06', '2025-04-06 01:51:06', '2025-04-06 01:51:06', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000140', 1, 4, 2, 2003000, 2003000, '2025-04-12 11:35:17', 'Lunas', 'Diterima', '2025-04-12 11:35:17', '2025-04-12 04:35:17', '2025-04-12 04:35:17', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000141', 1, 4, 2, 1712000, 1712000, '2025-04-22 11:50:04', 'Lunas', 'Diterima', '2025-04-22 11:50:04', '2025-04-22 04:50:04', '2025-04-22 04:50:04', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000142', 1, 4, 2, 1499000, 1499000, '2025-05-10 10:06:53', 'Lunas', 'Diterima', '2025-05-10 10:06:53', '2025-05-10 03:06:53', '2025-05-10 03:06:53', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000143', 1, 4, 2, 1941000, 1941000, '2025-05-08 14:55:50', 'Lunas', 'Diterima', '2025-05-08 14:55:50', '2025-05-08 07:55:50', '2025-05-08 07:55:50', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000144', 1, 4, 2, 1835000, 1835000, '2025-05-19 11:05:42', 'Lunas', 'Diterima', '2025-05-19 11:05:42', '2025-05-19 04:05:42', '2025-05-19 04:05:42', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000145', 1, 4, 2, 2243000, 2243000, '2025-05-10 16:04:17', 'Lunas', 'Diterima', '2025-05-10 16:04:17', '2025-05-10 09:04:17', '2025-05-10 09:04:17', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000146', 1, 4, 2, 1544000, 1544000, '2025-05-16 08:56:57', 'Lunas', 'Diterima', '2025-05-16 08:56:57', '2025-05-16 01:56:57', '2025-05-16 01:56:57', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000147', 1, 4, 2, 2218000, 2218000, '2025-05-28 08:14:00', 'Lunas', 'Diterima', '2025-05-28 08:14:00', '2025-05-28 01:14:00', '2025-05-28 01:14:00', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000148', 1, 4, 2, 1772000, 1772000, '2025-05-28 15:12:12', 'Lunas', 'Diterima', '2025-05-28 15:12:12', '2025-05-28 08:12:12', '2025-05-28 08:12:12', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000149', 1, 4, 2, 1937000, 1937000, '2025-05-06 11:27:45', 'Lunas', 'Diterima', '2025-05-06 11:27:45', '2025-05-06 04:27:45', '2025-05-06 04:27:45', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000150', 1, 4, 2, 2209000, 2209000, '2025-06-30 14:49:18', 'Lunas', 'Diterima', '2025-06-30 14:49:18', '2025-06-30 07:49:18', '2025-06-30 07:49:18', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000151', 1, 4, 2, 1894000, 1894000, '2025-06-01 15:52:37', 'Lunas', 'Diterima', '2025-06-01 15:52:37', '2025-06-01 08:52:37', '2025-06-01 08:52:37', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000152', 1, 4, 2, 1847000, 1847000, '2025-06-16 08:26:43', 'Lunas', 'Diterima', '2025-06-16 08:26:43', '2025-06-16 01:26:43', '2025-06-16 01:26:43', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000153', 1, 4, 2, 2118000, 2118000, '2025-06-19 16:27:34', 'Lunas', 'Diterima', '2025-06-19 16:27:34', '2025-06-19 09:27:34', '2025-06-19 09:27:34', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000154', 1, 4, 2, 1878000, 1878000, '2025-06-26 09:39:25', 'Lunas', 'Diterima', '2025-06-26 09:39:25', '2025-06-26 02:39:25', '2025-06-26 02:39:25', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000155', 1, 4, 2, 2209000, 2209000, '2025-06-14 12:14:35', 'Lunas', 'Diterima', '2025-06-14 12:14:35', '2025-06-14 05:14:35', '2025-06-14 05:14:35', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000156', 1, 4, 2, 1529000, 1529000, '2025-06-15 12:01:02', 'Lunas', 'Diterima', '2025-06-15 12:01:02', '2025-06-15 05:01:02', '2025-06-15 05:01:02', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000157', 1, 4, 2, 1751000, 1751000, '2025-07-04 10:58:42', 'Lunas', 'Diterima', '2025-07-04 10:58:42', '2025-07-04 03:58:42', '2025-07-04 03:58:42', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000158', 1, 4, 2, 1751000, 1751000, '2025-07-07 15:10:49', 'Lunas', 'Diterima', '2025-07-07 15:10:49', '2025-07-07 08:10:49', '2025-07-07 08:10:49', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000159', 1, 4, 2, 1916000, 1916000, '2025-07-17 13:24:17', 'Lunas', 'Diterima', '2025-07-17 13:24:17', '2025-07-17 06:24:17', '2025-07-17 06:24:17', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000160', 1, 4, 2, 1373000, 1373000, '2025-07-06 08:30:04', 'Lunas', 'Diterima', '2025-07-06 08:30:04', '2025-07-06 01:30:04', '2025-07-06 01:30:04', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000161', 1, 4, 2, 2478000, 2478000, '2025-07-16 15:03:59', 'Lunas', 'Diterima', '2025-07-16 15:03:59', '2025-07-16 08:03:59', '2025-07-16 08:03:59', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000162', 1, 4, 2, 1814000, 1814000, '2025-07-24 13:35:31', 'Lunas', 'Diterima', '2025-07-24 13:35:31', '2025-07-24 06:35:31', '2025-07-24 06:35:31', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000163', 1, 4, 2, 2330000, 2330000, '2025-07-15 10:21:57', 'Lunas', 'Diterima', '2025-07-15 10:21:57', '2025-07-15 03:21:57', '2025-07-15 03:21:57', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000164', 1, 4, 2, 2685000, 2685000, '2025-07-27 10:39:52', 'Lunas', 'Diterima', '2025-07-27 10:39:52', '2025-07-27 03:39:52', '2025-07-27 03:39:52', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000165', 1, 4, 2, 1763000, 1763000, '2025-07-15 13:04:39', 'Lunas', 'Diterima', '2025-07-15 13:04:39', '2025-07-15 06:04:39', '2025-07-15 06:04:39', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000166', 1, 4, 2, 2114000, 2114000, '2025-07-27 15:52:25', 'Lunas', 'Diterima', '2025-07-27 15:52:25', '2025-07-27 08:52:25', '2025-07-27 08:52:25', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000167', 1, 4, 2, 1814000, 1814000, '2025-08-05 10:52:55', 'Lunas', 'Diterima', '2025-08-05 10:52:55', '2025-08-05 03:52:55', '2025-08-05 03:52:55', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000168', 1, 4, 2, 1682000, 1682000, '2025-08-24 09:20:11', 'Lunas', 'Diterima', '2025-08-24 09:20:11', '2025-08-24 02:20:11', '2025-08-24 02:20:11', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000169', 1, 4, 2, 1625000, 1625000, '2025-08-14 12:00:44', 'Lunas', 'Diterima', '2025-08-14 12:00:44', '2025-08-14 05:00:44', '2025-08-14 05:00:44', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000170', 1, 4, 2, 2014000, 2014000, '2025-08-16 13:15:48', 'Lunas', 'Diterima', '2025-08-16 13:15:48', '2025-08-16 06:15:48', '2025-08-16 06:15:48', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000171', 1, 4, 2, 11876000, 11876000, '2025-08-18 14:27:12', 'Lunas', 'Diterima', '2025-08-18 14:27:12', '2025-08-18 07:27:12', '2025-08-18 07:27:12', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000172', 1, 4, 2, 1751000, 1751000, '2025-08-29 08:36:04', 'Lunas', 'Diterima', '2025-08-29 08:36:04', '2025-08-29 01:36:04', '2025-08-29 01:36:04', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000173', 1, 4, 2, 2108000, 2108000, '2025-08-19 13:34:45', 'Lunas', 'Diterima', '2025-08-19 13:34:45', '2025-08-19 06:34:45', '2025-08-19 06:34:45', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000174', 1, 4, 2, 1499000, 1499000, '2025-08-21 13:08:40', 'Lunas', 'Diterima', '2025-08-21 13:08:40', '2025-08-21 06:08:40', '2025-08-21 06:08:40', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000175', 1, 4, 2, 1937000, 1937000, '2025-08-03 08:52:17', 'Lunas', 'Diterima', '2025-08-03 08:52:17', '2025-08-03 01:52:17', '2025-08-03 01:52:17', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000176', 1, 4, 2, 14086000, 14086000, '2025-08-26 16:37:17', 'Lunas', 'Diterima', '2025-08-26 16:37:17', '2025-08-26 09:37:17', '2025-08-26 09:37:17', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000177', 1, 4, 2, 11462000, 11462000, '2025-08-05 08:53:52', 'Lunas', 'Diterima', '2025-08-05 08:53:52', '2025-08-05 01:53:52', '2025-08-05 01:53:52', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000178', 1, 4, 2, 1858000, 1858000, '2025-09-10 12:25:51', 'Lunas', 'Diterima', '2025-09-10 12:25:51', '2025-09-10 05:25:51', '2025-09-10 05:25:51', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000179', 1, 4, 2, 1904000, 1904000, '2025-09-02 15:17:21', 'Lunas', 'Diterima', '2025-09-02 15:17:21', '2025-09-02 08:17:21', '2025-09-02 08:17:21', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000180', 1, 4, 2, 1829000, 1829000, '2025-09-02 16:11:26', 'Lunas', 'Diterima', '2025-09-02 16:11:26', '2025-09-02 09:11:26', '2025-09-02 09:11:26', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000181', 1, 4, 2, 2143000, 2143000, '2025-09-21 10:20:56', 'Lunas', 'Diterima', '2025-09-21 10:20:56', '2025-09-21 03:20:56', '2025-09-21 03:20:56', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000182', 1, 4, 2, 2491000, 2491000, '2025-09-22 10:27:18', 'Lunas', 'Diterima', '2025-09-22 10:27:18', '2025-09-22 03:27:18', '2025-09-22 03:27:18', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000183', 1, 4, 2, 2080000, 2080000, '2025-09-10 12:48:12', 'Lunas', 'Diterima', '2025-09-10 12:48:12', '2025-09-10 05:48:12', '2025-09-10 05:48:12', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000184', 1, 4, 2, 1445000, 1445000, '2025-09-27 09:07:17', 'Lunas', 'Diterima', '2025-09-27 09:07:17', '2025-09-27 02:07:17', '2025-09-27 02:07:17', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000185', 1, 4, 2, 1436000, 1436000, '2025-09-24 13:37:35', 'Lunas', 'Diterima', '2025-09-24 13:37:35', '2025-09-24 06:37:35', '2025-09-24 06:37:35', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000186', 1, 4, 2, 1625000, 1625000, '2025-10-26 15:18:52', 'Lunas', 'Diterima', '2025-10-26 15:18:52', '2025-10-26 08:18:52', '2025-10-26 08:18:52', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000187', 1, 4, 2, 2286000, 2286000, '2025-10-06 13:36:55', 'Lunas', 'Diterima', '2025-10-06 13:36:55', '2025-10-06 06:36:55', '2025-10-06 06:36:55', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000188', 1, 4, 2, 1761000, 1761000, '2025-10-15 11:28:30', 'Lunas', 'Diterima', '2025-10-15 11:28:30', '2025-10-15 04:28:30', '2025-10-15 04:28:30', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000189', 1, 4, 2, 2172000, 2172000, '2025-10-15 16:57:17', 'Lunas', 'Diterima', '2025-10-15 16:57:17', '2025-10-15 09:57:17', '2025-10-15 09:57:17', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000190', 1, 4, 2, 1829000, 1829000, '2025-10-21 14:36:03', 'Lunas', 'Diterima', '2025-10-21 14:36:03', '2025-10-21 07:36:03', '2025-10-21 07:36:03', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000191', 1, 4, 2, 1559000, 1559000, '2025-10-07 12:46:05', 'Lunas', 'Diterima', '2025-10-07 12:46:05', '2025-10-07 05:46:05', '2025-10-07 05:46:05', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000192', 1, 4, 2, 1436000, 1436000, '2025-10-23 10:12:42', 'Lunas', 'Diterima', '2025-10-23 10:12:42', '2025-10-23 03:12:42', '2025-10-23 03:12:42', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000193', 1, 4, 2, 1625000, 1625000, '2025-11-22 08:30:45', 'Lunas', 'Diterima', '2025-11-22 08:30:45', '2025-11-22 01:30:45', '2025-11-22 01:30:45', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000194', 1, 4, 2, 10673000, 10673000, '2025-11-22 15:31:57', 'Lunas', 'Diterima', '2025-11-22 15:31:57', '2025-11-22 08:31:57', '2025-11-22 08:31:57', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000195', 1, 4, 2, 1562000, 1562000, '2025-11-27 15:48:23', 'Lunas', 'Diterima', '2025-11-27 15:48:23', '2025-11-27 08:48:23', '2025-11-27 08:48:23', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000196', 1, 4, 2, 1661000, 1661000, '2025-11-09 11:05:10', 'Lunas', 'Diterima', '2025-11-09 11:05:10', '2025-11-09 04:05:10', '2025-11-09 04:05:10', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000197', 1, 4, 2, 1382000, 1382000, '2025-11-14 14:05:47', 'Lunas', 'Diterima', '2025-11-14 14:05:47', '2025-11-14 07:05:47', '2025-11-14 07:05:47', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000198', 1, 4, 2, 1559000, 1559000, '2025-11-23 13:52:17', 'Lunas', 'Diterima', '2025-11-23 13:52:17', '2025-11-23 06:52:17', '2025-11-23 06:52:17', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000199', 1, 4, 2, 1625000, 1625000, '2025-12-16 14:19:34', 'Lunas', 'Diterima', '2025-12-16 14:19:34', '2025-12-16 07:19:34', '2025-12-16 07:19:34', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000200', 1, 4, 2, 1610000, 1610000, '2025-12-02 12:14:07', 'Lunas', 'Diterima', '2025-12-02 12:14:07', '2025-12-02 05:14:07', '2025-12-02 05:14:07', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000201', 1, 4, 2, 1304000, 1304000, '2025-12-17 09:05:41', 'Lunas', 'Diterima', '2025-12-17 09:05:41', '2025-12-17 02:05:41', '2025-12-17 02:05:41', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000202', 1, 4, 2, 1373000, 1373000, '2025-12-06 08:00:07', 'Lunas', 'Diterima', '2025-12-06 08:00:07', '2025-12-06 01:00:07', '2025-12-06 01:00:07', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000203', 1, 4, 2, 1562000, 1562000, '2025-12-19 12:55:00', 'Lunas', 'Diterima', '2025-12-19 12:55:00', '2025-12-19 05:55:00', '2025-12-19 05:55:00', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000204', 1, 4, 2, 2007000, 2007000, '2025-12-14 16:42:09', 'Lunas', 'Diterima', '2025-12-14 16:42:09', '2025-12-14 09:42:09', '2025-12-14 09:42:09', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000205', 1, 4, 2, 12546000, 12546000, '2026-01-30 14:28:05', 'Lunas', 'Diterima', '2026-01-30 14:28:05', '2026-01-30 07:28:05', '2026-01-30 07:28:05', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000206', 1, 4, 2, 1652000, 1652000, '2026-01-15 16:13:53', 'Lunas', 'Diterima', '2026-01-15 16:13:53', '2026-01-15 09:13:53', '2026-01-15 09:13:53', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000207', 1, 4, 2, 12577000, 12577000, '2026-01-02 11:54:44', 'Lunas', 'Diterima', '2026-01-02 11:54:44', '2026-01-02 04:54:44', '2026-01-02 04:54:44', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000208', 1, 4, 2, 1989000, 1989000, '2026-01-29 10:21:21', 'Lunas', 'Diterima', '2026-01-29 10:21:21', '2026-01-29 03:21:21', '2026-01-29 03:21:21', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000209', 1, 4, 2, 1730000, 1730000, '2026-01-18 10:26:50', 'Lunas', 'Diterima', '2026-01-18 10:26:50', '2026-01-18 03:26:50', '2026-01-18 03:26:50', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000210', 1, 4, 2, 1877000, 1877000, '2026-01-15 10:36:11', 'Lunas', 'Diterima', '2026-01-15 10:36:11', '2026-01-15 03:36:11', '2026-01-15 03:36:11', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000211', 1, 4, 2, 1805000, 1805000, '2026-02-05 16:52:33', 'Lunas', 'Diterima', '2026-02-05 16:52:33', '2026-02-05 09:52:33', '2026-02-05 09:52:33', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000212', 1, 4, 2, 9388000, 9388000, '2026-02-23 11:23:14', 'Lunas', 'Diterima', '2026-02-23 11:23:14', '2026-02-23 04:23:14', '2026-02-23 04:23:14', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000213', 1, 4, 2, 1826000, 1826000, '2026-02-06 09:32:33', 'Lunas', 'Diterima', '2026-02-06 09:32:33', '2026-02-06 02:32:33', '2026-02-06 02:32:33', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000214', 1, 4, 2, 2625000, 2625000, '2026-02-04 12:56:40', 'Lunas', 'Diterima', '2026-02-04 12:56:40', '2026-02-04 05:56:40', '2026-02-04 05:56:40', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000215', 1, 4, 2, 1991000, 1991000, '2026-02-22 12:16:55', 'Lunas', 'Diterima', '2026-02-22 12:16:55', '2026-02-22 05:16:55', '2026-02-22 05:16:55', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000216', 1, 4, 2, 2227000, 2227000, '2026-02-07 13:19:03', 'Lunas', 'Diterima', '2026-02-07 13:19:03', '2026-02-07 06:19:03', '2026-02-07 06:19:03', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000217', 1, 4, 2, 11863000, 11863000, '2026-02-17 11:46:44', 'Lunas', 'Diterima', '2026-02-17 11:46:44', '2026-02-17 04:46:44', '2026-02-17 04:46:44', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000218', 1, 4, 2, 1952000, 1952000, '2026-02-14 14:18:49', 'Lunas', 'Diterima', '2026-02-14 14:18:49', '2026-02-14 07:18:49', '2026-02-14 07:18:49', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000219', 1, 4, 2, 1988000, 1988000, '2026-02-02 12:41:08', 'Lunas', 'Diterima', '2026-02-02 12:41:08', '2026-02-02 05:41:08', '2026-02-02 05:41:08', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000220', 1, 4, 2, 1775000, 1775000, '2026-02-26 08:11:15', 'Lunas', 'Diterima', '2026-02-26 08:11:15', '2026-02-26 01:11:15', '2026-02-26 01:11:15', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000221', 1, 4, 2, 1712000, 1712000, '2026-03-14 16:51:55', 'Lunas', 'Diterima', '2026-03-14 16:51:55', '2026-03-14 09:51:55', '2026-03-14 09:51:55', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000222', 1, 4, 2, 1625000, 1625000, '2026-03-09 10:13:43', 'Lunas', 'Diterima', '2026-03-09 10:13:43', '2026-03-09 03:13:43', '2026-03-09 03:13:43', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000223', 1, 4, 2, 1877000, 1877000, '2026-03-29 14:25:10', 'Lunas', 'Diterima', '2026-03-29 14:25:10', '2026-03-29 07:25:10', '2026-03-29 07:25:10', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000224', 1, 4, 2, 1964000, 1964000, '2026-03-31 13:47:11', 'Lunas', 'Diterima', '2026-03-31 13:47:11', '2026-03-31 06:47:11', '2026-03-31 06:47:11', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000225', 1, 4, 2, 9138000, 9138000, '2026-03-30 15:42:36', 'Lunas', 'Diterima', '2026-03-30 15:42:36', '2026-03-30 08:42:36', '2026-03-30 08:42:36', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000226', 1, 4, 2, 1862000, 1862000, '2026-03-06 15:40:47', 'Lunas', 'Diterima', '2026-03-06 15:40:47', '2026-03-06 08:40:47', '2026-03-06 08:40:47', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000227', 1, 4, 2, 2482000, 2482000, '2026-03-21 11:20:52', 'Lunas', 'Diterima', '2026-03-21 11:20:52', '2026-03-21 04:20:52', '2026-03-21 04:20:52', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000228', 1, 4, 2, 1553000, 1553000, '2026-03-22 13:46:34', 'Lunas', 'Diterima', '2026-03-22 13:46:34', '2026-03-22 06:46:34', '2026-03-22 06:46:34', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000229', 1, 4, 2, 1652000, 1652000, '2026-03-22 11:36:19', 'Lunas', 'Diterima', '2026-03-22 11:36:19', '2026-03-22 04:36:19', '2026-03-22 04:36:19', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR000230', 1, 4, 2, 1589000, 1589000, '2026-03-23 12:36:02', 'Lunas', 'Diterima', '2026-03-23 12:36:02', '2026-03-23 05:36:02', '2026-03-23 05:36:02', 'Online', 'Delivery', 'Ongkir', 50000, NULL, 'Draft'),
('TR0235', 0, 4, 1, 226000, 226000, '2026-04-15 09:53:44', 'Lunas', 'Menunggu Konfirmasi', '2026-04-15 09:53:44', '2026-04-15 09:53:44', '2026-04-15 09:53:44', 'pickup', NULL, NULL, 0, NULL, 'Draft'),
('TR0236', 0, 4, 1, 113000, 113000, '2026-04-16 01:29:17', 'Lunas', 'Diterima', '2026-04-16 01:30:12', '2026-04-16 01:29:17', '2026-04-16 01:29:17', 'Online', 'Pickup', 'Ongkir', 0, NULL, 'Draft'),
('TX000001', 1, 4, 1, 1300000, 1260000, '2025-08-17 07:37:36', 'Paid', 'Pending', '2025-08-17 07:39:25', '2025-08-17 07:37:36', '2025-08-17 07:37:36', 'Online', NULL, 'Regular', 0, '1', 'Draft'),
('TX000003', 1, 4, 2, 1000000, 700000, '2025-08-22 02:30:07', 'Paid', 'Pending', NULL, '2025-08-22 02:30:07', '2025-08-22 02:30:07', 'Online', NULL, 'awda', 0, 'a', 'Draft'),
('TX000004', 1, 4, 2, 6006000, 6060000, '2025-08-27 06:59:46', 'Lunas', 'Diterima', '2025-08-31 12:59:59', '2025-08-27 06:59:46', '2025-08-27 06:59:46', 'Online', 'Delivery', 'Ongkir', 300000, 'Sing ngirim mas rujak', 'Draft'),
('TX000005', 1, 4, 2, 3000000, 2630000, '2025-10-06 14:51:30', 'Lunas', 'Diterima', NULL, '2025-10-06 14:51:30', '2025-10-06 14:51:30', 'Offline', 'Pickup', 'Ongkir', 2000, 'wadwda', 'Draft');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) NOT NULL,
  `f_name` varchar(30) NOT NULL,
  `email` varchar(255) NOT NULL,
  `nomor_telepon` varchar(20) NOT NULL,
  `email_verified_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `username` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user` varchar(10) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `img` varchar(255) NOT NULL,
  `tipe_user` enum('end_customer','retailer') NOT NULL DEFAULT 'end_customer',
  `status_verifikasi` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `foto_toko` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `f_name`, `email`, `nomor_telepon`, `email_verified_at`, `username`, `password`, `user`, `remember_token`, `img`, `tipe_user`, `status_verifikasi`, `foto_toko`) VALUES
(1, 'Admin', 'admin1@gmail.com', '', '2025-04-30 08:50:56', 'admin', '$2y$10$a5CeW7r8VeUPy2hQXI5xJuNhnPo8CWfDwJJQhauP0g1BJ/77olWh.', 'Admin', '', 'images/1815883516605523.jpeg', 'end_customer', 'pending', NULL),
(4, 'Ahmad Muzakki', 'jasjus841@gmail.com', '0879272342', '2026-04-16 01:29:33', 'jasjus841', '$2y$12$X4cGX1XP/QkWh9c5bVOrKO8b5a68gTdscbDHNGMEn/.KUmqf/ZCui', 'User', 'DLyKQnrjfRT57BZPh8MygxeArbjzGrcjk3DyYZ6JqeQG5H6zQJ6EFFhShVfS', '', 'end_customer', 'pending', NULL),
(6, 'Mamat', 'kajeks841@gmail.com', '08161518497', '2025-11-04 17:14:45', 'mamat', '$2y$12$hnDb0KYGC0Dq6LzCHiu6qOXoSD8F8OzVmPcv4BbL7M3h2oTmabnlq', 'User', NULL, 'default-avatar.png', 'end_customer', 'pending', NULL),
(5, 'Ahmad Rojali', 'rojali@gmail.com', '08970833227', '2025-05-23 15:16:50', 'rojali', '$2y$12$0o0UcbPaQuotlWGvgAtXceAz.fzSfuIhfOXx8XRwJ8M6pNbhRPhYS', 'User', NULL, 'default-avatar.png', 'end_customer', 'pending', NULL),
(2, 'Fanidiya Tasya', 'admin@gmail.com', '082472332', '2026-04-16 01:27:04', 'tsy24', '$2y$12$X4cGX1XP/QkWh9c5bVOrKO8b5a68gTdscbDHNGMEn/.KUmqf/ZCui', 'Admin', 'GzCClxu3nhiQmmZ8YlR74Ac3xmIBraX1Nwxw995W5rcsFw0QRMoDoXioeYKL', 'images/1815883516605523.jpeg', 'end_customer', 'pending', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `addresses_user_id_foreign` (`user_id`);

--
-- Indexes for table `detail_harga`
--
ALTER TABLE `detail_harga`
  ADD KEY `Index 1` (`id_roster`,`id_user`) USING BTREE,
  ADD KEY `detail_harga_id_ukuran_foreign` (`id_ukuran`),
  ADD KEY `detail_harga_produk_id_fk` (`produk_id`),
  ADD KEY `detail_harga_address_id_foreign` (`address_id`);

--
-- Indexes for table `detail_motif`
--
ALTER TABLE `detail_motif`
  ADD UNIQUE KEY `detail_motif_id_tipe_id_motif_unique` (`id_tipe`,`id_motif`),
  ADD KEY `detail_motif_id_motif_foreign` (`id_motif`);

--
-- Indexes for table `detail_tipe`
--
ALTER TABLE `detail_tipe`
  ADD PRIMARY KEY (`id_jenis`,`id_tipe`);

--
-- Indexes for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD KEY `IdTransaksi` (`IdTransaksi`),
  ADD KEY `id_ukuran` (`id_ukuran`),
  ADD KEY `IdProduk` (`IdRoster`) USING BTREE,
  ADD KEY `detail_transaksi_data_type_index` (`data_type`),
  ADD KEY `detail_transaksi_produk_id_fk` (`produk_id`);

--
-- Indexes for table `jenisbarang`
--
ALTER TABLE `jenisbarang`
  ADD PRIMARY KEY (`IdJenisBarang`),
  ADD UNIQUE KEY `JenisBarang` (`JenisBarang`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_histories`
--
ALTER TABLE `model_histories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `model_histories_unique_version` (`id_roster`,`model_type`,`version_id`),
  ADD KEY `model_histories_roster_type_active_idx` (`id_roster`,`model_type`,`is_active`),
  ADD KEY `model_histories_type_created_idx` (`model_type`,`created_at`),
  ADD KEY `model_histories_version_idx` (`version_id`),
  ADD KEY `model_histories_produk_id_fk` (`produk_id`);

--
-- Indexes for table `motif_roster`
--
ALTER TABLE `motif_roster`
  ADD PRIMARY KEY (`IdMotif`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `produk_sku_unique` (`sku`),
  ADD KEY `Index 5` (`id_tipe`),
  ADD KEY `FK_produk_tipe_roster_2` (`id_motif`),
  ADD KEY `IdJenisBarang` (`id_jenis`) USING BTREE,
  ADD KEY `produk_forecast_status_index` (`forecast_status`);

--
-- Indexes for table `produk_size`
--
ALTER TABLE `produk_size`
  ADD PRIMARY KEY (`IdRoster`,`id_ukuran`) USING BTREE,
  ADD KEY `produk_size_id_ukuran_foreign` (`id_ukuran`),
  ADD KEY `produk_size_produk_id_fk` (`produk_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_unique` (`name`);

--
-- Indexes for table `role_user`
--
ALTER TABLE `role_user`
  ADD PRIMARY KEY (`user_id`,`role_id`,`user_type`),
  ADD KEY `role_user_role_id_foreign` (`role_id`);

--
-- Indexes for table `size`
--
ALTER TABLE `size`
  ADD PRIMARY KEY (`id_ukuran`);

--
-- Indexes for table `tipe_roster`
--
ALTER TABLE `tipe_roster`
  ADD PRIMARY KEY (`IdTipe`) USING BTREE;

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`IdTransaksi`),
  ADD KEY `username` (`id_admin`) USING BTREE,
  ADD KEY `IdCust` (`id_customer`) USING BTREE,
  ADD KEY `Index 4` (`address_id`) USING BTREE;

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`username`),
  ADD UNIQUE KEY `id` (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `jenisbarang`
--
ALTER TABLE `jenisbarang`
  MODIFY `IdJenisBarang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `model_histories`
--
ALTER TABLE `model_histories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `motif_roster`
--
ALTER TABLE `motif_roster`
  MODIFY `IdMotif` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `size`
--
ALTER TABLE `size`
  MODIFY `id_ukuran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tipe_roster`
--
ALTER TABLE `tipe_roster`
  MODIFY `IdTipe` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `detail_harga`
--
ALTER TABLE `detail_harga`
  ADD CONSTRAINT `detail_harga_address_id_foreign` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `detail_harga_id_ukuran_foreign` FOREIGN KEY (`id_ukuran`) REFERENCES `size` (`id_ukuran`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_harga_produk_id_fk` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `detail_motif`
--
ALTER TABLE `detail_motif`
  ADD CONSTRAINT `detail_motif_id_motif_foreign` FOREIGN KEY (`id_motif`) REFERENCES `motif_roster` (`IdMotif`),
  ADD CONSTRAINT `detail_motif_id_tipe_foreign` FOREIGN KEY (`id_tipe`) REFERENCES `tipe_roster` (`IdTipe`);

--
-- Constraints for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `detail_transaksi_produk_id_fk` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `model_histories`
--
ALTER TABLE `model_histories`
  ADD CONSTRAINT `model_histories_produk_id_fk` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `motif` FOREIGN KEY (`id_motif`) REFERENCES `motif_roster` (`IdMotif`),
  ADD CONSTRAINT `produk_id_jenis_foreign` FOREIGN KEY (`id_jenis`) REFERENCES `jenisbarang` (`IdJenisBarang`),
  ADD CONSTRAINT `produk_id_tipe_foreign` FOREIGN KEY (`id_tipe`) REFERENCES `tipe_roster` (`IdTipe`);

--
-- Constraints for table `produk_size`
--
ALTER TABLE `produk_size`
  ADD CONSTRAINT `FK_produk_size_size` FOREIGN KEY (`id_ukuran`) REFERENCES `size` (`id_ukuran`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `produk_size_produk_id_fk` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `FK_transaksi_addresses` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
