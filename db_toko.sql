/*
SQLyog Enterprise v13.1.1 (64 bit)
MySQL - 8.0.30 : Database - db_toko
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`db_toko` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `db_toko`;

/*Table structure for table `barang` */

DROP TABLE IF EXISTS `barang`;

CREATE TABLE `barang` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_barang` varchar(255) NOT NULL,
  `id_kategori` int NOT NULL,
  `nama_barang` text NOT NULL,
  `merk` varchar(255) NOT NULL,
  `harga_beli` varchar(255) NOT NULL,
  `harga_jual` varchar(255) NOT NULL,
  `satuan_barang` varchar(255) NOT NULL,
  `stok` text NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `expired` date NOT NULL,
  `tgl_input` varchar(255) NOT NULL,
  `tgl_update` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=latin1;

/*Data for the table `barang` */

insert  into `barang`(`id`,`id_barang`,`id_kategori`,`nama_barang`,`merk`,`harga_beli`,`harga_jual`,`satuan_barang`,`stok`,`foto`,`expired`,`tgl_input`,`tgl_update`) values 
(47,'BR001',1,'Jus Mangga','- ','10000','15000','PCS','49',NULL,'2025-01-18','18 December 2024, 9:50',NULL),
(48,'BR002',1,'Cocacola','-','5000','6000','PCS','50',NULL,'2025-02-26','18 December 2024, 9:52',NULL),
(49,'BR003',1,'Cleo','-','3000','4000','PCS','96',NULL,'2025-03-05','18 December 2024, 9:52',NULL),
(50,'BR004',2,'Nasi Goreng','-','10000','11000','Porsi','19',NULL,'2024-12-28','18 December 2024, 9:53',NULL),
(51,'BR005',2,'Nasi Nugget','-','10000','11000','Porsi','15',NULL,'2024-12-28','18 December 2024, 9:53',NULL),
(52,'BR006',2,'Nasi Chilipadi','-','10000','11000','Porsi','16',NULL,'2024-12-28','18 December 2024, 9:54',NULL),
(53,'BR007',3,'Chicato','-','2500','4000','PCS','30',NULL,'2025-01-31','18 December 2024, 9:55',NULL),
(54,'BR008',3,'Macaroni','-','200','500','PCS','45',NULL,'2025-02-01','18 December 2024, 9:56',NULL),
(55,'BR009',3,'Sosis','-','1000','2000','PCS','19',NULL,'2024-12-25','18 December 2024, 9:57','19 December 2024, 14:06'),
(56,'BR010',7,'risol','-','3000','5000','PCS','25',NULL,'2024-12-28','19 December 2024, 14:45','19 December 2024, 14:46'),
(57,'BR011',2,'Nasi Campur','-','0','8000','Porsi','12',NULL,'2025-03-29','24 March 2025, 11:15',NULL);

/*Table structure for table `daily_limit` */

DROP TABLE IF EXISTS `daily_limit`;

CREATE TABLE `daily_limit` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nim` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `limit_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nim` (`nim`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `daily_limit` */

insert  into `daily_limit`(`id`,`nim`,`limit_amount`,`created_at`,`updated_at`) values 
(1,'244107027008',50000.00,'2025-03-24 10:00:46','2025-03-24 10:01:00'),
(2,'12345678',100000.00,'2025-03-24 11:19:48','2025-03-24 11:19:48');

/*Table structure for table `emoney` */

DROP TABLE IF EXISTS `emoney`;

CREATE TABLE `emoney` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nim` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `foto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `saldo` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `emoney` */

insert  into `emoney`(`id`,`nim`,`nama`,`foto`,`saldo`) values 
(8,'244107027008','Frankie Steinlie',NULL,222000.00),
(16,'12345678','Stein',NULL,200000.00);

/*Table structure for table `food_restriction` */

DROP TABLE IF EXISTS `food_restriction`;

CREATE TABLE `food_restriction` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nim` varchar(20) NOT NULL,
  `id_barang` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nim` (`nim`,`id_barang`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `food_restriction` */

insert  into `food_restriction`(`id`,`nim`,`id_barang`) values 
(22,'12345678','BR002'),
(23,'12345678','BR008'),
(16,'244107027008','BR001'),
(15,'244107027008','BR003'),
(18,'244107027008','BR004'),
(21,'244107027008','BR005'),
(17,'244107027008','BR006'),
(14,'244107027008','BR007'),
(20,'244107027008','BR009'),
(19,'244107027008','BR010');

/*Table structure for table `history` */

DROP TABLE IF EXISTS `history`;

CREATE TABLE `history` (
  `id_h` int NOT NULL AUTO_INCREMENT,
  `nim` varchar(255) DEFAULT NULL,
  `totalharga` varchar(255) DEFAULT NULL,
  `id_barang` varchar(20) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `time` time DEFAULT NULL,
  PRIMARY KEY (`id_h`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `history` */

insert  into `history`(`id_h`,`nim`,`totalharga`,`id_barang`,`date`,`time`) values 
(3,'244107027008','10000','BR001','2024-12-03','16:05:26'),
(4,'244107027008','20000','BR001','2024-12-03','16:07:03'),
(5,'244107027008','20000','BR003','2024-12-03','16:23:53'),
(6,'244107027008','50000','BR004','2024-12-03','16:25:34'),
(7,'244107027008','30000','BR005','2024-12-03','16:46:22'),
(8,'244107027008','10000','BR006','2024-12-03','16:49:09'),
(9,'244107027008','10000','BR007','2024-12-03','16:49:54'),
(10,'244107027008','64000','BR009','2024-12-03','17:03:02'),
(12,'244107027008','4000','BR010','2024-12-19','14:10:58'),
(15,'244107027008','34000','BR010','2025-03-24','10:00:10');

/*Table structure for table `kategori` */

DROP TABLE IF EXISTS `kategori`;

CREATE TABLE `kategori` (
  `id_kategori` int NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(255) NOT NULL,
  `tgl_input` varchar(255) NOT NULL,
  PRIMARY KEY (`id_kategori`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

/*Data for the table `kategori` */

insert  into `kategori`(`id_kategori`,`nama_kategori`,`tgl_input`) values 
(1,'Beverage (Minuman)','23 October 2024, 18:19'),
(2,'Food (Makanan)','23 October 2024, 18:19'),
(3,'Snack','23 October 2024, 5:28'),
(7,'Gorengan','19 December 2024, 14:45');

/*Table structure for table `login` */

DROP TABLE IF EXISTS `login`;

CREATE TABLE `login` (
  `id_login` int NOT NULL AUTO_INCREMENT,
  `user` varchar(255) NOT NULL,
  `pass` char(32) NOT NULL,
  `id_member` int NOT NULL,
  PRIMARY KEY (`id_login`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

/*Data for the table `login` */

insert  into `login`(`id_login`,`user`,`pass`,`id_member`) values 
(1,'admin','21232f297a57a5a743894a0e4a801fc3',1);

/*Table structure for table `login_mhs` */

DROP TABLE IF EXISTS `login_mhs`;

CREATE TABLE `login_mhs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) DEFAULT NULL,
  `nim` varchar(255) DEFAULT NULL,
  `password` varbinary(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `nohp` bigint DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*Data for the table `login_mhs` */

insert  into `login_mhs`(`id`,`nama`,`nim`,`password`,`email`,`nohp`) values 
(2,'Frankie Steinlie','244107027008','$2y$10$Moh18pZ07ztaZtREjSwfD.HCWlZhWgFeRub91suzoWYF65CiLdB3a','frankie.steinlie@gmail.com',8883866931),
(10,'Stein','12345678','$2y$10$JvUVWMM8NEhsHV2MvE535OCJBoLOmhMuAqD7sRUZDCNvyyNIGQ/EG',NULL,NULL);

/*Table structure for table `member` */

DROP TABLE IF EXISTS `member`;

CREATE TABLE `member` (
  `id_member` int NOT NULL AUTO_INCREMENT,
  `nm_member` varchar(255) NOT NULL,
  `alamat_member` text NOT NULL,
  `telepon` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `gambar` text NOT NULL,
  `NIK` text NOT NULL,
  PRIMARY KEY (`id_member`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

/*Data for the table `member` */

insert  into `member`(`id_member`,`nm_member`,`alamat_member`,`telepon`,`email`,`gambar`,`NIK`) values 
(1,'Kantin','Kediri','00000000','kantin@gmail.com','1729766295Logo Polinema.png','0000000000');

/*Table structure for table `nota` */

DROP TABLE IF EXISTS `nota`;

CREATE TABLE `nota` (
  `id_nota` int NOT NULL AUTO_INCREMENT,
  `id_barang` varchar(255) NOT NULL,
  `id_member` int NOT NULL,
  `jumlah` varchar(255) NOT NULL,
  `total` varchar(255) NOT NULL,
  `tanggal_input` varchar(255) NOT NULL,
  `periode` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_nota`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=latin1;

/*Data for the table `nota` */

/*Table structure for table `penjualan` */

DROP TABLE IF EXISTS `penjualan`;

CREATE TABLE `penjualan` (
  `id_penjualan` int NOT NULL AUTO_INCREMENT,
  `id_barang` varchar(255) NOT NULL,
  `id_member` int NOT NULL,
  `jumlah` varchar(255) NOT NULL,
  `total` varchar(255) NOT NULL,
  `tanggal_input` varchar(255) NOT NULL,
  PRIMARY KEY (`id_penjualan`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=latin1;

/*Data for the table `penjualan` */

/*Table structure for table `toko` */

DROP TABLE IF EXISTS `toko`;

CREATE TABLE `toko` (
  `id_toko` int NOT NULL AUTO_INCREMENT,
  `nama_toko` varchar(255) NOT NULL,
  `alamat_toko` text NOT NULL,
  `tlp` varchar(255) NOT NULL,
  `nama_pemilik` varchar(255) NOT NULL,
  PRIMARY KEY (`id_toko`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

/*Data for the table `toko` */

insert  into `toko`(`id_toko`,`nama_toko`,`alamat_toko`,`tlp`,`nama_pemilik`) values 
(1,'Kantin','Kediri','00000000','admin');

/*Table structure for table `validasi` */

DROP TABLE IF EXISTS `validasi`;

CREATE TABLE `validasi` (
  `id_validasi` int NOT NULL AUTO_INCREMENT,
  `nim` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nama` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nominal` decimal(10,2) DEFAULT NULL,
  `fotobukti` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `valid` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id_validasi`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `validasi` */

insert  into `validasi`(`id_validasi`,`nim`,`nama`,`nominal`,`fotobukti`,`valid`) values 
(5,'244107027008','Frankie Steinlie',10000.00,'bukti_1742716215_244107027008.jpg',1),
(6,'244107027008','Frankie Steinlie',100000.00,'bukti_1742716215_244107027008.jpg',2),
(7,'244107027008','Frankie Steinlie',5000.00,'bukti_1742716215_244107027008.jpg',1),
(8,'244107027008','Frankie Steinlie',20000.00,'bukti_1742716215_244107027008.jpg',2),
(9,'244107027008','Frankie Steinlie',15000.00,'bukti_1742716215_244107027008.jpg',1),
(10,'244107027008','Frankie Steinlie',1000.00,'bukti_1742716215_244107027008.jpg',2),
(11,'244107027008','Frankie Steinlie',200000.00,'bukti_1742716215_244107027008.jpg',1),
(12,'244107027008','Frankie Steinlie',10000.00,'bukti_1742716215_244107027008.jpg',1),
(14,'244107027008','Frankie Steinlie',50000.00,'bukti_1742716215_244107027008.jpg',1),
(15,'244107027008','Frankie Steinlie',50000.00,'bukti_1742716215_244107027008.jpg',1),
(17,'244107027008','Frankie Steinlie',15000.00,'bukti_1742716215_244107027008.jpg',1),
(22,'244107027008','Frankie Steinlie',100000.00,'bukti_1742716215_244107027008.jpg',1),
(23,'244107027008','Frankie Steinlie',20000.00,'bukti_1742716215_244107027008.jpg',2),
(24,'244107027008','Frankie Steinlie',25000.00,'bukti_1742716215_244107027008.jpg',1),
(25,'244107027008','Frankie Steinlie',10000.00,'bukti_1742741264_244107027008.jpg',1),
(26,'12345678','Stein',200000.00,'bukti_1742790007_12345678.jpg',1);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
