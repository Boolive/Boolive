/*
SQLyog Job Agent Version 8.32 Copyright(c) Webyog Softworks Pvt. Ltd. All Rights Reserved.

MySQL - 5.0.22-community-nt  
*********************************************************************
*/
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

/* SYNC DB : boolive-git */ 
SET AUTOCOMMIT = 0;
/* SYNC TABLE : `contents` */

/* SYNC TABLE : `interfaces` */

/* SYNC TABLE : `keywords` */

/* SYNC TABLE : `library` */

	/*Start of batch : 1 */
INSERT INTO `library` VALUES ('/library/layouts/boolive/head/logo', '', '0', '0', '6', '1', '/library/basic/interfaces/widgets/Logo', NULL, '0', '0', '0', '0', '0');
INSERT INTO `library` VALUES ('/library/basic/interfaces/widgets/Logo', '', '0', '0', '6', '1', '/library/basic/interfaces/widgets/Widget', 'Logo.tpl', '1', '1', '0', '0', '0');
INSERT INTO `library` VALUES ('/library/basic/interfaces/widgets/Logo/image', '', '0', '0', '7', '1', '/library/basic/contents/Image', 'Logo.png', '0', '1', '0', '0', '0');
	/*End   of batch : 1 */
/* SYNC TABLE : `members` */

/* SYNC TABLE : `site` */


COMMIT;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
