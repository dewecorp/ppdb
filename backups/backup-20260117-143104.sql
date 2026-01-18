-- Backup database
-- Waktu: 2026-01-17 14:31:04

--
-- Struktur tabel `madrasah`
--

DROP TABLE IF EXISTS `madrasah`;
CREATE TABLE `madrasah` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(150) NOT NULL,
  `alamat` text,
  `email` varchar(100) DEFAULT NULL,
  `website` varchar(100) DEFAULT NULL,
  `hp_kepala` varchar(30) DEFAULT NULL,
  `hp_panitia` varchar(30) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `nama_panitia` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Data untuk tabel `madrasah`
--
INSERT INTO `madrasah` (`id`,`nama`,`alamat`,`email`,`website`,`hp_kepala`,`hp_panitia`,`logo`,`nama_panitia`) VALUES
('1','MI SULTAN FATTAH SUKOSONO','Jln. Kauman RT. 10 RW. 03 Sukosono Jepara','misultanfattah@gmail.com','https://misultanfattah.sch.id/','-','082331838221','logo-20260117142115.png','Ali Yasin, S.Pd.I.'),
('2','MI SULTAN FATTAH SUKOSONO','Alamat madrasah belum diatur','-','-','-','-',NULL,NULL);

--
-- Struktur tabel `pendaftar`
--

DROP TABLE IF EXISTS `pendaftar`;
CREATE TABLE `pendaftar` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `no_pendaftaran` varchar(30) NOT NULL,
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `no_pendaftaran` (`no_pendaftaran`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Struktur tabel `pengaturan`
--

DROP TABLE IF EXISTS `pengaturan`;
CREATE TABLE `pengaturan` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(50) NOT NULL,
  `nilai` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nama` (`nama`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Data untuk tabel `pengaturan`
--
INSERT INTO `pengaturan` (`id`,`nama`,`nilai`) VALUES
('1','status_pendaftaran','buka'),
('2','info_pendaftaran','MI SULTAN FATTAH JEPARA menyediakan PPDB secara online diharapkan proses PPDB dapat berjalan cepat dan bisa dilakukan dimanapun dan kapanpun selama sesi PPDB Online dibuka. Proses pendaftaran calon siswa baru di masa pandemi Covid-19 ini dan terhambat oleh jarak jika datang ke madrasah langsung, bisa mengakses website PPDB Online MI SULTAN FATTAH JEPARA.\r\n\r\nPengisian form PPDB Online mohon diperhatikan data yang dibutuhkan yang nantinya akan dipakai dalam proses PPDB. Setelah proses pengisian form PPDB secara online berhasil dilakukan, calon siswa akan mendapat bukti daftar dengan nomor pendaftaran dan harus disimpan yang akan digunakan untuk proses selanjutnya.'),
('3','syarat_pendaftaran',''),
('4','alur_pendaftaran',''),
('5','header_background',''),
('19','tahun_ajaran','2026/2027');

--
-- Struktur tabel `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `foto` varchar(255) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Data untuk tabel `users`
--
INSERT INTO `users` (`id`,`foto`,`username`,`password`,`created_at`) VALUES
('1',NULL,'admin','$2y$10$tv5Pt74luStC9dQ85a003edZBImPf.EKXlHyEtTiTFlJ0VvJ9CB/6','2026-01-17 20:38:46');

