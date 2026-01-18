-- Backup database
-- Waktu: 2026-01-18 07:13:40

--
-- Struktur tabel `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `message` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `created_at` (`created_at`),
  KEY `action` (`action`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Data untuk tabel `activity_logs`
--
INSERT INTO `activity_logs` (`id`,`user_id`,`action`,`message`,`created_at`) VALUES
('1','1','update_madrasah','Perbarui data madrasah','2026-01-17 21:45:43'),
('2','1','login','Login oleh admin','2026-01-17 22:03:19'),
('3','1','update_user','Perbarui pengguna ID 1 (admin)','2026-01-17 22:03:27'),
('4','1','delete_backup','Hapus backup backup-20260117-142621.sql','2026-01-17 22:19:54'),
('5','1','update_pendaftar_status','Ubah status pendaftar ID 2 menjadi diterima','2026-01-18 10:13:46'),
('6','1','update_pendaftar_status','Ubah status pendaftar ID 1 menjadi ditolak','2026-01-18 10:18:07'),
('7','1','delete_pendaftar','Hapus pendaftar ID 1','2026-01-18 10:18:26'),
('8','1','update_user','Perbarui pengguna ID 1 (admin)','2026-01-18 13:00:09'),
('9','1','update_user','Perbarui pengguna ID 1 (Admin)','2026-01-18 13:00:25'),
('10','1','update_pendaftar_status','Ubah status pendaftar ID 2 menjadi ditolak','2026-01-18 13:31:48'),
('11','1','update_pendaftar_status','Ubah status pendaftar ID 2 menjadi diterima','2026-01-18 13:42:25'),
('12','1','send_whatsapp','Kirim WhatsApp ke 082331838221 status diterima hasil=fail','2026-01-18 13:42:25'),
('13','1','send_email','Kirim Email ke ibnuhasan3@gmail.com status diterima hasil=ok','2026-01-18 13:42:27'),
('14','1','update_pendaftar_status','Ubah status pendaftar ID 2 menjadi ditolak','2026-01-18 13:43:10'),
('15','1','send_whatsapp','Kirim WhatsApp ke 082331838221 status ditolak hasil=fail','2026-01-18 13:43:10'),
('16','1','send_email','Kirim Email ke ibnuhasan3@gmail.com status ditolak hasil=ok','2026-01-18 13:43:11'),
('17','1','update_pendaftar_status','Ubah status pendaftar ID 2 menjadi diterima','2026-01-18 13:45:27'),
('18','1','send_whatsapp','Kirim WhatsApp ke 082331838221 status diterima hasil=fail','2026-01-18 13:45:27'),
('19','1','send_email','Kirim Email ke ibnuhasan3@gmail.com status diterima hasil=ok','2026-01-18 13:45:27');

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
('1','MI Sultan Fattah Sukosono','Jln. Kauman RT. 10 RW. 03 Sukosono Jepara','misultanfattah@gmail.com','https://misultanfattah.sch.id/','-','082331838221','logo-20260117142115.png','Ali Yasin, S.Pd.I.'),
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Data untuk tabel `pendaftar`
--
INSERT INTO `pendaftar` (`id`,`no_pendaftaran`,`nama_lengkap`,`nik`,`kk`,`jenis_kelamin`,`tempat_lahir`,`tanggal_lahir`,`alamat`,`status_keluarga`,`anak_ke`,`jumlah_saudara`,`asal_tk`,`nama_ayah`,`nama_ibu`,`pekerjaan_ayah`,`pekerjaan_ibu`,`nama_wali`,`pekerjaan_wali`,`email`,`hp`,`akte`,`kip`,`pkh`,`status_daftar`,`created_at`) VALUES
('2','PPDB20260002','Nur Huda','3320010401790004','332001','Perempuan','Jepara','2001-02-18','RT. 07 RW. 02 Sukosono Kedung','Anak kandung','2','5','TK ABA','ayah','ibu','tukang','tukang','ayah','tukang','ibnuhasan3@gmail.com','082331838221',NULL,'Ya','Ya','diterima','2026-01-17 22:58:39');

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
) ENGINE=InnoDB AUTO_INCREMENT=156 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Data untuk tabel `pengaturan`
--
INSERT INTO `pengaturan` (`id`,`nama`,`nilai`) VALUES
('1','status_pendaftaran','buka'),
('2','info_pendaftaran','<p>MI SULTAN FATTAH JEPARA menyediakan PPDB secara online diharapkan proses PPDB dapat berjalan cepat dan bisa dilakukan dimanapun dan kapanpun selama sesi PPDB Online dibuka. Proses pendaftaran calon siswa baru di zaman serba digital sekarang mendaftar siswa baru tidak harus datang ke madrasah langsung, bisa mengakses website PPDB Online MI SULTAN FATTAH JEPARA. Pengisian form PPDB Online mohon diperhatikan data yang dibutuhkan yang nantinya akan dipakai dalam proses PPDB. Setelah proses pengisian form PPDB secara online berhasil dilakukan, calon siswa akan mendapat bukti daftar dengan nomor pendaftaran dan harus disimpan yang akan digunakan untuk proses selanjutnya.</p>\r\n'),
('3','syarat_pendaftaran','<ul>\r\n	<li>Mengisi formulir pendaftaran</li>\r\n	<li>Menyerahkan foto kopi akta kelahiran</li>\r\n	<li>Menyerahkan foto kopi kartu keluarga</li>\r\n	<li>Menyerahkan foto kopi KTP orangtua/wali</li>\r\n	<li>Menyerahkan foto kopi ijazah RA/TK</li>\r\n	<li>Menyerahkan foto kopi Kartu KIP atau PKH (jika ada)</li>\r\n</ul>\r\n'),
('4','alur_pendaftaran','<ul>\r\n	<li>Pendaftaran dibuka mulai tanggal 19 Mei sampai dengan 13 Juli 2026</li>\r\n	<li>Isilah formulir dengan lengkap</li>\r\n	<li>Wajib menyertakan email orang tua/wali yang masih aktif</li>\r\n	<li>Cetak bukti pendaftaran dan diserahkan kepada Panitia PPDB ketika masuk pertama tanggal 14 Juli 2026</li>\r\n	<li>Jika terkendala pencetakan bukti pendaftaran, maka cukup ditunjukkan file bukti pendaftaran Dokumen pelengkap yaitu, foto copy KTP, foto copy KK, legalisir Ijazah, foto copy kartu PKH (jika punya) dan Akte kelahiran diserahkan ketika daftar ulang atau masuk pertama</li>\r\n</ul>\r\n'),
('5','header_background','header-20260118135138.jpg'),
('19','tahun_ajaran','2026/2027'),
('95','whatsapp_enabled','0'),
('96','whatsapp_provider','whatsapp_cloud'),
('97','wa_tpl_diterima','Halo {nama}, pendaftaran Anda dengan nomor {no_pendaftaran} telah {status}. Terima kasih.'),
('98','wa_tpl_ditolak','Halo {nama}, pendaftaran Anda dengan nomor {no_pendaftaran} telah {status}. Hubungi panitia untuk info lebih lanjut.'),
('99','email_enabled','1'),
('100','email_from_name','Panitia PPDB MI Sultan Fattah Sukosono'),
('101','email_subject_diterima','Status Pendaftaran: Diterima'),
('102','email_subject_ditolak','Status Pendaftaran: Ditolak'),
('103','email_tpl_diterima','<p>Halo {nama},</p><p>Pendaftaran Anda dengan nomor {no_pendaftaran} telah <strong>{status}</strong>.</p><p>Terima kasih.</p>'),
('104','email_tpl_ditolak','<p>Halo {nama},</p><p>Pendaftaran Anda dengan nomor {no_pendaftaran} telah <strong>{status}</strong>.</p><p>Silakan hubungi panitia untuk informasi lebih lanjut.</p>'),
('105','email_provider','mail'),
('106','smtp_host',''),
('107','smtp_port','465'),
('108','smtp_secure','ssl'),
('109','smtp_user','Admin'),
('110','smtp_pass','admin123'),
('121','email_from','misultanfattah@gmail.com');

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
('1','user-20260117150327.png','Admin','$2y$10$uxDx3VS9pzn5VwvYw9bVO.5kC4TNhMFG9oUGGg5t./bvG7Gjo144O','2026-01-17 20:38:46');

