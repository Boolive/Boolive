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
INSERT INTO `library` VALUES ('/Library/content_widgets/Keywords/switch_views/case_keyword/Keyword', '', '0', '0', '6', '1', '/Library/content_widgets/Keyword', NULL, '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `library` VALUES ('/Library/content_widgets/Keywords/switch_views', '', '0', '0', '4', '1', '/Library/views/SwitchViews', 'switch_views.tpl', '0', '1', '0', '0', '0', '0', '0');
INSERT INTO `library` VALUES ('/Library/content_widgets/Keywords/res/style', '', '0', '0', '5', '1', '/Library/views/Css', 'style.css', '0', '1', '0', '0', '0', '0', '0');
INSERT INTO `library` VALUES ('/Library/content_widgets/Keywords/res', '', '0', '0', '4', '1', '/Library/views/Widget/res', NULL, '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `library` VALUES ('/Library/content_widgets/Keyword', '', '0', '0', '3', '1', '/Library/views/Widget', 'Keyword.tpl', '1', '1', '0', '0', '0', '0', '0');
INSERT INTO `library` VALUES ('/Library/content_widgets/Comments/switch_views/case_comment/Comment', '', '0', '0', '6', '1', '/Library/content_widgets/Comment', NULL, '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `library` VALUES ('/Library/content_widgets/Comments/switch_views/case_comment', '', '0', '0', '5', '1', '/Library/views/SwitchCase', '/Library/content_samples/Comment', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `library` VALUES ('/Library/content_widgets/Comments/switch_views', '', '0', '0', '4', '1', '/Library/views/SwitchViews', NULL, '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `library` VALUES ('/Library/content_widgets/Comments/res/style', '', '0', '0', '5', '1', '/Library/views/Css', 'style.css', '0', '1', '0', '0', '0', '0', '0');
INSERT INTO `library` VALUES ('/Library/content_widgets/Comment', '', '0', '0', '3', '1', '/Library/views/Widget', 'Comment.tpl', '1', '1', '0', '0', '0', '0', '0');
INSERT INTO `library` VALUES ('/Library/content_widgets/Keywords/switch_views/case_keyword', '', '0', '0', '5', '1', '/Library/views/SwitchCase', '/Library/content_samples/Keyword', '0', '0', '0', '0', '0', '0', '0');
UPDATE `library` SET `uri`='/Library/content_widgets/Keywords', `lang`='', `owner`='0', `date`='0', `level`='3', `order`='1', `proto`='/Library/views/AutoWidgetList', `value`='Keywords.tpl', `is_logic`='1', `is_file`='1', `is_history`='0', `is_delete`='0', `is_hidden`='0', `is_link`='0', `override`='0'  WHERE (`uri` = '/Library/content_widgets/Keywords' AND `lang` = '' AND `owner` = 0 AND `date` = 0) ;
UPDATE `library` SET `uri`='/Library/content_widgets/Comments', `lang`='', `owner`='0', `date`='0', `level`='3', `order`='1', `proto`='/Library/views/AutoWidgetList', `value`='Comments.tpl', `is_logic`='1', `is_file`='1', `is_history`='0', `is_delete`='0', `is_hidden`='0', `is_link`='0', `override`='0'  WHERE (`uri` = '/Library/content_widgets/Comments' AND `lang` = '' AND `owner` = 0 AND `date` = 0) ;
/*End   of batch : 1 */
/* SYNC TABLE : `members` */

/* SYNC TABLE : `site` */


COMMIT;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
