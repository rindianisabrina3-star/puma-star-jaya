-- ========================================================
-- DATABASE PUMA STAR JAYA TOUR (CLOUD READY)
-- Compatible with MySQL 8.0 & TiDB Cloud Serverless
-- ========================================================
SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;
SET TIME_ZONE = '+00:00';

CREATE DATABASE IF NOT EXISTS `pumatour`;
USE `pumatour`;

DROP TABLE IF EXISTS user;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `level` varchar(191) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'Staff Administrator','admin@pumatour.com','admin','2026-10-20 00:52:14','$2y$10$waK.JSAFUxqB9ouNAt/b6ecj.NhemMSUbOSVwVqoXhfnXMBnMqAFu',NULL,'2026-10-20 00:52:14','2026-10-20 00:52:14'),(2,'Bendahara Keuangan','bendahara@pumatour.com','bendahara','2026-10-20 00:52:14','$2y$10$r56sCT9HrfCE0Oyr/cy0QuW7wz5y5FR68CzF.Ngid3NlYRp0HGk/q',NULL,'2026-10-20 00:52:14','2026-10-20 00:52:14'),(3,'Direktur Utama','direktur@pumatour.com','direktur','2026-10-20 00:52:14','$2y$10$3o3ms46YeobTlM2NcHApPeTwhSu18GcB.FA4lxeTIpykz5A0Y2/Pu',NULL,'2026-10-20 00:52:14','2026-10-20 00:52:14'),(4,'Budi Santoso','konsumen@gmail.com','konsumen','2026-10-20 00:52:14','$2y$10$fiU7dh2gU4dVPdXZDo0bg.jTUykXL9i3O4AJx1wYhaPpKt8MYcwEC',NULL,'2026-10-20 00:52:14','2026-10-20 00:52:14'),(5,'rindi','rindianisabrina3@gmail.com','konsumen','2026-11-12 10:03:38','$2y$10$El3OLLm0qEIHyIsPYlKrBOQ1tV8z8MLGBEYVoBN7RObHYVy0fJ1WG',NULL,'2026-11-12 10:03:38','2026-11-12 10:03:38');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

DROP TABLE IF EXISTS paket;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `paket` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text NOT NULL,
  `destinasi` text NOT NULL,
  `fasilitas` varchar(255) DEFAULT NULL,
  `bus` varchar(255) NOT NULL,
  `harga` int(11) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `paket`
--

LOCK TABLES `paket` WRITE;
/*!40000 ALTER TABLE `paket` DISABLE KEYS */;
INSERT INTO `paket` VALUES (1,'Paket Wisata Bali Eksotis 3D2N','Nikmati keindahan Pulau Dewata Bali mengunjungi Pantai Pandawa, Pura Uluwatu, Tanah Lot, Kintamani, serta pertunjukan Tari Kecak spektakuler. Termasuk penginapan hotel bintang 3 dan makan khas Bali.','Denpasar, Kuta, Ubud, Uluwatu, Tanah Lot','Hotel Bintang 3, Makan 7x, Tiket Masuk Wisata, Guide Berpengalaman, Air Mineral & Snack','Bus Pariwisata Executive SHD 45 Seat (AC, Reclining Seat, Audio/Video, USB Port)',5000,'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=800&q=80','2026-10-20 00:52:14','2026-10-20 00:52:14'),(2,'Pesona Budaya Jogja & Candi Borobudur 2D1N','Eksplorasi warisan sejarah dan budaya Yogyakarta, Candi Borobudur, Tebing Breksi, Malioboro, serta sensasi seru Jeep Lava Tour Gunung Merapi yang memacu adrenalin.','Yogyakarta, Candi Borobudur, Merapi Lava Tour, Malioboro','Hotel Bintang 3, Makan 4x, Sewa Jeep Merapi, Tiket Masuk Objek Wisata, P3K','Jetbus 3+ HDD Pariwisata 50 Seat (AC Dingin, Karaoke, Toilet, Charger)',2000,'https://images.unsplash.com/photo-1596402184320-417e7178b2cd?auto=format&fit=crop&w=800&q=80','2026-10-20 00:52:14','2026-10-20 00:52:14'),(3,'Bromo Golden Sunrise & Malang Fantasy Tour 3D2N','Saksikan matahari terbit legendaris di Penanjakan Bromo, Kawah Bromo, Pasir Berbisik, dilanjutkan petik apel Kota Batu Malang, Museum Angkut, dan Jatim Park.','Taman Nasional Bromo Tengger Semeru, Batu Malang','Jeep Bromo 4WD, Villa/Hotel Bintang 3 Batu, Tiket Bromo & Jatim Park, Makan 6x','Big Bus Pariwisata Mercedes-Benz 48 Seat (Suspensi Udara, Full Entertainment)',3000,'https://images.unsplash.com/photo-1588668214407-6ea9a6d8c272?auto=format&fit=crop&w=800&q=80','2026-10-20 00:52:14','2026-10-20 00:52:14'),(4,'Bandung Heritage & Nuansa Alam Ciwidey 2D1N','Liburan sejuk menyegarkan di Kawah Putih Ciwidey, Perkebunan Teh Rancabali, Glamping Lakeside, serta wisata belanja dan kuliner legendaris Kota Kembang.','Bandung, Ciwidey, Kawah Putih, Glamping Lakeside, Paris Van Java','Penginapan Resort Alam, Makan 4x, Tiket Kawah Putih & Perahu Angsa, Tour Leader','Medium Bus Pariwisata 31 Seat Luxury Class (Captain Seat, Karaoke, Coolbox)',1000,'https://images.unsplash.com/photo-1584810359583-96fc3448beaa?auto=format&fit=crop&w=800&q=80','2026-10-20 00:52:14','2026-10-20 00:52:14');
/*!40000 ALTER TABLE `paket` ENABLE KEYS */;
UNLOCK TABLES;

DROP TABLE IF EXISTS kupon;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kupon` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `kode` varchar(255) DEFAULT NULL,
  `potongan` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_kupon_user` (`user_id`),
  CONSTRAINT `fk_kupon_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kupon`
--

LOCK TABLES `kupon` WRITE;
/*!40000 ALTER TABLE `kupon` DISABLE KEYS */;
INSERT INTO `kupon` VALUES (1,NULL,'PUMAJAYA25',500,'2026-10-20 00:52:14','2026-10-20 00:52:14'),(2,NULL,'LIBURANSERU',1000,'2026-10-20 00:52:14','2026-10-20 00:52:14'),(3,NULL,'PROMOHEMAT',500,'2026-10-20 00:52:14','2026-10-20 00:52:14'),(4,NULL,'ROMBONGAN2025',1500,'2026-10-20 00:52:14','2026-10-20 00:52:14');
/*!40000 ALTER TABLE `kupon` ENABLE KEYS */;
UNLOCK TABLES;

DROP TABLE IF EXISTS pemesanan;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pemesanan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `paket_id` bigint(20) unsigned NOT NULL,
  `harga` int(11) NOT NULL,
  `harga_nego` int(11) DEFAULT NULL,
  `status_nego` enum('None','Menunggu','Disetujui','Ditolak') DEFAULT 'None',
  `catatan_nego` text DEFAULT NULL,
  `tanggal_berangkat` date DEFAULT NULL,
  `nama_rombongan` varchar(255) NOT NULL,
  `jumlah_pax` int(11) DEFAULT NULL,
  `kursi_dipilih` varchar(255) DEFAULT NULL,
  `no_hp` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pemesanan_user` (`user_id`),
  KEY `fk_pemesanan_paket` (`paket_id`),
  CONSTRAINT `fk_pemesanan_paket` FOREIGN KEY (`paket_id`) REFERENCES `paket` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_pemesanan_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pemesanan`
--

LOCK TABLES `pemesanan` WRITE;
/*!40000 ALTER TABLE `pemesanan` DISABLE KEYS */;
INSERT INTO `pemesanan` VALUES (1,4,1,83250000,NULL,'None',NULL,'2026-11-03','SMA Negeri 1 Jaya Tour',45,NULL,'081234567890','2026-10-20 00:52:14','2026-10-20 00:52:14'),(2,1,2,17000000,NULL,'None',NULL,'2026-10-29','universitas muhammadiyah metro',20,NULL,'085669675820','2026-10-20 01:38:20','2026-10-20 01:38:20'),(3,4,4,20000,NULL,'None',NULL,'2026-11-06','universitas muhammadiyah metro',20,NULL,'085669675820','2026-11-05 12:57:47','2026-11-05 12:57:47'),(4,4,4,10000,NULL,'None',NULL,'2026-11-14','universitas muhammadiyah metro',10,NULL,'085669675820','2026-11-05 13:02:45','2026-11-05 13:02:45'),(5,4,3,500,500,'Disetujui','','2026-12-12','SMA Negri 1 METRO',1,'1A','085669675820','2026-11-12 11:07:45','2026-11-12 11:39:22'),(6,4,2,6000,NULL,'None',NULL,'2026-11-25','Rombongan Uji Coba Receipt',3,'1A, 1B, 1C','085835688224','2026-11-12 11:14:36','2026-11-12 11:14:36'),(7,4,4,2000,NULL,'None',NULL,'2026-12-04','universitas muhammadiyah metro',2,'1A, 1B','085669675820','2026-11-12 11:36:38','2026-11-12 11:36:38');
/*!40000 ALTER TABLE `pemesanan` ENABLE KEYS */;
UNLOCK TABLES;

DROP TABLE IF EXISTS transaksi;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transaksi` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `pemesanan_id` bigint(20) unsigned DEFAULT NULL,
  `no_invoice` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_transaksi_user` (`user_id`),
  KEY `fk_transaksi_pemesanan` (`pemesanan_id`),
  CONSTRAINT `fk_transaksi_pemesanan` FOREIGN KEY (`pemesanan_id`) REFERENCES `pemesanan` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_transaksi_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaksi`
--

LOCK TABLES `transaksi` WRITE;
/*!40000 ALTER TABLE `transaksi` DISABLE KEYS */;
INSERT INTO `transaksi` VALUES (1,4,1,'INV-20261020-001','Ditolak','BUKTI_AUTO_1_1794483844.png','2026-10-20 00:52:14','2026-11-05 13:15:05'),(2,4,4,'INV-20261105-0004','Menunggu Verifikasi','BUKTI_AUTO_4_1794483845.png','2026-11-05 13:03:48','2026-11-12 11:24:14'),(3,4,5,'INV-20261112-0005','Lunas','BUKTI_AUTO_5_1794483844.png','2026-11-12 11:08:08','2026-11-12 11:39:22'),(4,4,6,'INV-20261112-0006','Lunas','BUKTI_AUTO_6_1794483844.png','2026-11-12 11:14:36','2026-11-12 11:14:36'),(5,4,3,'INV-20261112-0003','Menunggu Verifikasi','BUKTI_AUTO_3_1794483845.png','2026-11-12 11:25:06','2026-11-12 11:25:06'),(6,4,7,'INV-20261112-0007','Menunggu Verifikasi','BUKTI_AUTO_7_1794483846.png','2026-11-12 11:36:47','2026-11-12 11:37:47');
/*!40000 ALTER TABLE `transaksi` ENABLE KEYS */;
UNLOCK TABLES;

DROP TABLE IF EXISTS ulasan;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ulasan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `paket_id` bigint(20) unsigned NOT NULL,
  `pemesanan_id` bigint(20) unsigned DEFAULT NULL,
  `bintang` int(11) NOT NULL DEFAULT 5,
  `komentar` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `paket_id` (`paket_id`),
  CONSTRAINT `ulasan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ulasan_ibfk_2` FOREIGN KEY (`paket_id`) REFERENCES `paket` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ulasan`
--

LOCK TABLES `ulasan` WRITE;
/*!40000 ALTER TABLE `ulasan` DISABLE KEYS */;
INSERT INTO `ulasan` VALUES (1,4,1,4,5,'Pelayanan Puma Star Jaya sangat luar biasa! Bus pariwisatanya bersih, nyaman, AC dingin, dan sopirnya ramah serta berpengalaman selama perjalanan ke Bali.','2026-11-07 10:47:23'),(2,4,2,2,5,'Tour Jogja bersama rombongan kampus sangat berkesan. Makanan enak dan tepat waktu, jadwal ke Candi Borobudur dan Malioboro teratur rapi!','2026-10-31 10:47:23');
/*!40000 ALTER TABLE `ulasan` ENABLE KEYS */;
UNLOCK TABLES;

SET FOREIGN_KEY_CHECKS = 1;
-- Dump Completed Successfully
