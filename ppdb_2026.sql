CREATE DATABASE IF NOT EXISTS `ppdb_2026` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `ppdb_2026`;

CREATE TABLE IF NOT EXISTS `pengaturan` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(50) NOT NULL UNIQUE,
  `nilai` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `madrasah` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(150) NOT NULL,
  `alamat` text,
  `email` varchar(100),
  `website` varchar(100),
  `hp_kepala` varchar(30),
  `hp_panitia` varchar(30),
  `nama_panitia` varchar(150) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `pendaftar` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `no_pendaftaran` varchar(30) NOT NULL UNIQUE,
  `nama_lengkap` varchar(150) NOT NULL,
  `nik` varchar(50) NOT NULL,
  `kk` varchar(50) NOT NULL,
  `jenis_kelamin` varchar(20) NOT NULL,
  `tempat_lahir` varchar(100) NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `alamat` text NOT NULL,
  `status_keluarga` varchar(50) DEFAULT NULL,
  `anak_ke` int DEFAULT NULL,
  `jumlah_saudara` int DEFAULT NULL,
  `asal_tk` varchar(150) DEFAULT NULL,
  `nama_ayah` varchar(150) NOT NULL,
  `nama_ibu` varchar(150) NOT NULL,
  `pekerjaan_ayah` varchar(100) DEFAULT NULL,
  `pekerjaan_ibu` varchar(100) DEFAULT NULL,
  `nama_wali` varchar(150) DEFAULT NULL,
  `pekerjaan_wali` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `hp` varchar(30) NOT NULL,
  `akte` varchar(100) DEFAULT NULL,
  `kip` varchar(10) NOT NULL,
  `pkh` varchar(10) NOT NULL,
  `status_daftar` enum('proses','diterima','ditolak') NOT NULL DEFAULT 'proses',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `foto` varchar(255) DEFAULT NULL,
  `username` varchar(50) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `madrasah` (`nama`, `alamat`, `email`, `website`, `hp_kepala`, `hp_panitia`, `nama_panitia`, `logo`)
VALUES
('MI SULTAN FATTAH SUKOSONO', 'Alamat madrasah belum diatur', '-', '-', '-', '-', NULL, NULL);

INSERT INTO `pengaturan` (`nama`, `nilai`) VALUES
('status_pendaftaran', 'tutup'),
('info_pendaftaran', 'Informasi PPDB belum diatur.'),
('syarat_pendaftaran', ''),
('alur_pendaftaran', ''),
('header_background', '');
