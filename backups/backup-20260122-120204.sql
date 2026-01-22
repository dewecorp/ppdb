-- Backup database
-- Waktu: 2026-01-22 12:02:04

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
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Data untuk tabel `activity_logs`
--
INSERT INTO `activity_logs` (`id`,`user_id`,`action`,`message`,`created_at`) VALUES
('39','1','login','Login oleh Admin','2026-01-22 07:22:01'),
('40','1','pendaftaran_baru','Pendaftaran baru atas nama Santoso (PPDB2026004)','2026-01-22 08:02:33'),
('41','1','update_pendaftar_status','Ubah status pendaftar ID 8 menjadi diterima','2026-01-22 08:03:33'),
('42','1','backup','Membuat backup backup-20260122-080621.sql','2026-01-22 08:06:21'),
('43','1','pendaftaran_baru','Pendaftaran baru atas nama Atun (PPDB2026005)','2026-01-22 09:00:46'),
('44','1','pendaftaran_baru','Pendaftaran baru atas nama Dewi (PPDB2026006)','2026-01-22 09:05:02'),
('45','1','delete_backup','Hapus backup backup-20260118-071340.sql','2026-01-22 10:16:36'),
('46','1','delete_backup','Hapus backup backup-20260119-042020.sql','2026-01-22 10:16:44'),
('47','1','backup','Membuat backup backup-20260122-101649.sql','2026-01-22 10:16:49');

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
('1','MI Sultan Fattah Sukosono','Jln. Kauman RT. 10 RW. 03 Sukosono Jepara','misultanfattah@gmail.com','https://misultanfattah.sch.id/','-','083844483341','logo-20260117142115.png','Ali Yasin, S.Pd.I.'),
('2','MI SULTAN FATTAH SUKOSONO','Alamat madrasah belum diatur','-','-','-','-',NULL,NULL);

--
-- Struktur tabel `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL DEFAULT 'general',
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Data untuk tabel `notifications`
--
INSERT INTO `notifications` (`id`,`type`,`title`,`content`,`is_read`,`created_at`) VALUES
('1','registration','Pendaftaran Baru','{\"nama\":\"Nur Huda\",\"no_pendaftaran\":\"PPDB2026002\",\"waktu\":\"2026-01-22 00:47:33\"}','1','2026-01-22 07:47:33'),
('2','registration','Pendaftaran Baru','{\"nama\":\"siti\",\"no_pendaftaran\":\"PPDB2026003\",\"waktu\":\"2026-01-22 08:00:09\"}','1','2026-01-22 08:00:09'),
('3','registration','Pendaftaran Baru','{\"nama\":\"Santoso\",\"no_pendaftaran\":\"PPDB2026004\",\"waktu\":\"2026-01-22 08:02:33\"}','1','2026-01-22 08:02:33'),
('4','registration','Pendaftaran Baru','{\"nama\":\"Atun\",\"no_pendaftaran\":\"PPDB2026005\",\"waktu\":\"2026-01-22 09:00:46\"}','1','2026-01-22 09:00:46'),
('5','registration','Pendaftaran Baru','{\"nama\":\"Dewi\",\"no_pendaftaran\":\"PPDB2026006\",\"waktu\":\"2026-01-22 09:05:02\"}','1','2026-01-22 09:05:02');

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
  `kip` varchar(10) NOT NULL,
  `pkh` varchar(10) NOT NULL,
  `status_daftar` enum('proses','diterima','ditolak') NOT NULL DEFAULT 'proses',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `no_pendaftaran` (`no_pendaftaran`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Data untuk tabel `pendaftar`
--
INSERT INTO `pendaftar` (`id`,`no_pendaftaran`,`nama_lengkap`,`nik`,`kk`,`jenis_kelamin`,`tempat_lahir`,`tanggal_lahir`,`alamat`,`status_keluarga`,`anak_ke`,`jumlah_saudara`,`asal_tk`,`nama_ayah`,`nama_ibu`,`pekerjaan_ayah`,`pekerjaan_ibu`,`nama_wali`,`pekerjaan_wali`,`email`,`hp`,`kip`,`pkh`,`status_daftar`,`created_at`) VALUES
('7','PPDB2026001','Nur Ahwan','3320135408060001','332001','Laki-laki','Jepara','2010-01-18','Jalan Kauman RT. 10 RW. 03 Sukosono','Anak kandung','1','3','TK Al Huda','ayah','ibu','tukang','tukang','ayah','tukang','ibnuhasan3@gmail.com','082331838221','Ya','Ya','diterima','2026-01-19 04:54:14'),
('8','PPDB2026002','Nur Huda','3320135408060001','332001','Laki-laki','Jepara','2002-05-22','RT. 07 RW. 02 Sukosono Kedung','Anak kandung','2','5','TK Al Huda','ayah','ibu','tukang','tukang','ayah','tukang','ibnuhasan3@gmail.com','082331838221','Tidak','Ya','diterima','2026-01-22 07:47:33'),
('9','PPDB2026003','siti','3320135408060001','332001','Perempuan','Jepara','2010-01-18','RT. 07 RW. 02 Sukosono Kedung','Anak kandung','1','3','TK Al Huda','ayah','ibu','tukang','tukang','ayah','tukang','ibnuhasan3@gmail.com','082331838221','Ya','Tidak','proses','2026-01-22 08:00:08'),
('10','PPDB2026004','Santoso','3320135408060004','332001','Laki-laki','Jepara','2010-01-18','Jalan Kauman RT. 10 RW. 03 Sukosono','Anak kandung','1','3','TK Al Huda','ayah','ibu','tukang','tukang','ayah','tukang','ibnuhasan3@gmail.com','082331838221','Tidak','Ya','proses','2026-01-22 08:02:33'),
('11','PPDB2026005','Atun','3320135408060004','332001','Perempuan','Jepara','2010-01-18','RT. 07 RW. 02 Sukosono Kedung','Anak kandung','1','3','TK Al Huda','ayah','ibu','tukang','tukang','ayah','tukang','ibnuhasan3@gmail.com','082331838221','Ya','Ya','proses','2026-01-22 09:00:46'),
('12','PPDB2026006','Dewi','3320135408060004','332001','Perempuan','Jepara','2010-01-18','RT. 07 RW. 02 Sukosono Kedung','Anak kandung','1','3','TK Al Huda','ayah','ibu','tukang','tukang','ayah','tukang','ibnuhasan3@gmail.com','082331838221','Ya','Ya','proses','2026-01-22 09:05:02');

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
) ENGINE=InnoDB AUTO_INCREMENT=266 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Data untuk tabel `pengaturan`
--
INSERT INTO `pengaturan` (`id`,`nama`,`nilai`) VALUES
('1','status_pendaftaran','tutup'),
('2','info_pendaftaran','<p>MI SULTAN FATTAH JEPARA menyediakan PPDB secara online diharapkan proses PPDB dapat berjalan cepat dan bisa dilakukan dimanapun dan kapanpun selama sesi PPDB Online dibuka. Proses pendaftaran calon siswa baru di zaman serba digital sekarang mendaftar siswa baru tidak harus datang ke madrasah langsung, bisa mengakses website PPDB Online MI SULTAN FATTAH JEPARA. Pengisian form PPDB Online mohon diperhatikan data yang dibutuhkan yang nantinya akan dipakai dalam proses PPDB. Setelah proses pengisian form PPDB secara online berhasil dilakukan, calon siswa akan mendapat bukti daftar dengan nomor pendaftaran dan harus disimpan yang akan digunakan untuk proses selanjutnya.</p>\r\n'),
('3','syarat_pendaftaran','<ul><br><li>Mengisi formulir pendaftaran</li><li>Menyerahkan foto kopi akta kelahiran</li><li>Menyerahkan foto kopi kartu keluarga</li><li>Menyerahkan foto kopi KTP orangtua/wali</li><li>Menyerahkan foto kopi ijazah RA/TK</li><li>Menyerahkan foto kopi Kartu KIP atau PKH (jika ada)</li></ul>\r\n'),
('4','alur_pendaftaran','<ul><li>Pendaftaran dibuka mulai tanggal 19 Mei sampai dengan 13 Juli 2026</li><li>Isilah formulir dengan lengkap</li><li>Wajib menyertakan email orang tua/wali yang masih aktif</li><li>Cetak bukti pendaftaran dan diserahkan kepada Panitia PPDB ketika masuk pertama tanggal 14 Juli 2026</li><li>Jika terkendala pencetakan bukti pendaftaran, maka cukup ditunjukkan file bukti pendaftaran Dokumen pelengkap yaitu, foto copy KTP, foto copy KK, legalisir Ijazah, foto copy kartu PKH (jika punya) dan Akte kelahiran diserahkan ketika daftar ulang atau masuk pertama</li>\r\n</ul>\r\n'),
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
('121','email_from','misultanfattah@gmail.com'),
('156','sequence_ppdb_2026','6'),
('169','wa_token','admin123'),
('170','wa_phone_id','Admin'),
('171','pendaftaran_start_at','2026-01-01 11:13:00'),
('172','pendaftaran_end_at','2026-01-22 11:13:00'),
('228','fasilitas_siswa','<ul><li style=\"box-sizing: inherit;\">Gratis uang pendaftaran dan uang gedung</li><li style=\"box-sizing: inherit;\">Seragam olah raga 1 stel</li><li style=\"box-sizing: inherit;\">Baju seragam hari Rabu dan Kamis</li><li style=\"box-sizing: inherit;\">Peci Hitam untuk Putra</li><li style=\"box-sizing: inherit;\">Kerudung putih untuk Putri</li></ul>');

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
('1','user-20260117150327.png','Admin','$2y$10$4LFo5AlxqVu0jvzGgxq1.uQCBTOy/uJ2ZLHP6TjVQK9iHY.v2lh0.','2026-01-17 20:38:46');

