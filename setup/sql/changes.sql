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

	/*Start of batch : 1 */
INSERT INTO `contents` VALUES ('/Contents/main/text/list1/item1/text/p1', '', '0', '0', '7', '1', '/Library/content_samples/Paragraph', 'Хей-хей-хей! Я - пункт меню!', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `contents` VALUES ('/Library/content_widgets/RichText/switch_views/case_list', '', '0', '0', '5', '1', '/Library/views/SwitchCase', '/Library/content_samples/lists/List', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `contents` VALUES ('/Contents/main/text/list1/item3/list1/item2/text/p1', '', '0', '0', '9', '1', '/Library/content_samples/Paragraph', 'вложенного меню', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `contents` VALUES ('/Contents/main/text/list1/item3/list1/item2/text', '', '0', '0', '8', '1', '/Library/content_samples/Page/text', NULL, '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `contents` VALUES ('/Contents/main/text/list1/item3/list1/item2', '', '0', '0', '7', '2', '/Library/content_samples/lists/Item', NULL, '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `contents` VALUES ('/Contents/main/text/list1/item3/list1/item1/text/p1', '', '0', '0', '9', '1', '/Library/content_samples/Paragraph', 'А вот пункт', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `contents` VALUES ('/Contents/main/text/list1/item3/list1/item1/text', '', '0', '0', '8', '1', '/Library/content_samples/Page/text', NULL, '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `contents` VALUES ('/Contents/main/text/list1/item3/list1/item1', '', '0', '0', '7', '1', '/Library/content_samples/lists/Item', NULL, '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `contents` VALUES ('/Contents/main/text/list1/item3/list1', '', '0', '0', '6', '1', '/Library/content_samples/lists/List', NULL, '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `contents` VALUES ('/Contents/main/text/list1/item3', '', '0', '0', '5', '3', '/Library/content_samples/lists/Item', NULL, '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `contents` VALUES ('/Contents/main/text/list1/item2/text/p1', '', '0', '0', '7', '2', '/Library/content_samples/Paragraph', 'Ля-ля-ля, ля-ля-ля!', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `contents` VALUES ('/Contents/main/text/list1/item2/text/img1', '', '0', '0', '7', '1', '/Library/content_samples/Image', 'nota.png', '0', '1', '0', '0', '0', '0', '0');
INSERT INTO `contents` VALUES ('/Contents/main/text/list1/item2/text', '', '0', '0', '6', '1', '/Library/content_samples/RichText', NULL, '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `contents` VALUES ('/Contents/main/text/list1/item2', '', '0', '0', '5', '2', '/Library/content_samples/lists/Item', NULL, '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `contents` VALUES ('/Contents/main/text/list1/item1/text/p2', '', '0', '0', '7', '2', '/Library/content_samples/Paragraph', 'А я - второй параграф =)', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `contents` VALUES ('/Library/content_widgets/RichText/switch_views/case_list/ListView', '', '0', '0', '6', '1', '/Library/content_widgets/ListView', NULL, '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `contents` VALUES ('/Contents/main/text/list1/item1/text', '', '0', '0', '6', '1', '/Library/content_samples/RichText', NULL, '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `contents` VALUES ('/Contents/main/text/list1/item1', '', '0', '0', '5', '1', '/Library/content_samples/lists/Item', NULL, '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `contents` VALUES ('/Contents/main/text/list1', '', '0', '0', '4', '5', '/Library/content_samples/lists/List', NULL, '0', '0', '0', '0', '0', '0', '0');
	/*End   of batch : 1 */
/* SYNC TABLE : `interfaces` */

	/*Start of batch : 1 */
UPDATE `interfaces` SET `uri`='/Interfaces/html', `lang`='', `owner`='0', `date`='0', `level`='2', `order`='1', `proto`='/Library/views/Html', `value`=NULL, `is_logic`='0', `is_file`='0', `is_history`='0', `is_delete`='0', `is_hidden`='0', `is_link`='0', `override`='0'  WHERE (`uri` = '/Interfaces/html' AND `lang` = '' AND `owner` = 0 AND `date` = 0) ;
	/*End   of batch : 1 */
/* SYNC TABLE : `keywords` */

	/*Start of batch : 1 */
	/*End   of batch : 1 */
/* SYNC TABLE : `library` */

	/*Start of batch : 1 */
INSERT INTO `library` VALUES ('/Library/content_widgets/ListView/switch_views', '', '0', '0', '4', '1', '/Library/views/SwitchViews', NULL, '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `library` VALUES ('/Library/content_widgets/RichText/switch_views/case_list/List', '', '0', '0', '6', '1', '/Library/content_widgets/ListView', NULL, '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `library` VALUES ('/Library/content_widgets/RichText/switch_views/case_list', '', '0', '0', '5', '1', '/Library/views/SwitchCase', '/Library/content_samples/lists/List', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `library` VALUES ('/Library/content_widgets/ListView/switch_views/case_item', '', '0', '0', '5', '1', '/Library/views/SwitchCase', '/Library/content_samples/lists/Item', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `library` VALUES ('/Library/content_widgets/ListView/switch_views/case_item/Item/switch_views/case_richtext/RichText', '', '0', '0', '9', '1', '/Library/content_widgets/RichText', NULL, '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `library` VALUES ('/Library/content_widgets/ListView/switch_views/case_item/Item/switch_views/case_richtext', '', '0', '0', '8', '1', '/Library/views/SwitchCase', '/Library/content_samples/RichText', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `library` VALUES ('/Library/content_widgets/ListView/switch_views/case_item/Item/switch_views/case_list/ListView', '', '0', '0', '9', '1', '/Library/content_widgets/ListView', NULL, '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `library` VALUES ('/Library/content_widgets/ListView/switch_views/case_item/Item/switch_views/case_list', '', '0', '0', '8', '1', '/Library/views/SwitchCase', '/Library/content_samples/lists/List', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `library` VALUES ('/Library/content_widgets/ListView/switch_views/case_item/Item/switch_views', '', '0', '0', '7', '1', '/Library/views/SwitchViews', NULL, '0', '0', '0', '0', '0', '0', '0');
INSERT INTO `library` VALUES ('/Library/content_widgets/ListView/switch_views/case_item/Item', '', '0', '0', '6', '1', '/Library/views/AutoWidgetList', 'Item.tpl', '0', '1', '0', '0', '0', '0', '0');
INSERT INTO `library` VALUES ('/Library/content_widgets/ListView', '', '0', '0', '3', '1', '/Library/views/AutoWidgetList', 'ListView.tpl', '0', '1', '0', '0', '0', '0', '0');

COMMIT;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
