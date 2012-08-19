/*
SQLyog Ultimate v8.32 
MySQL - 5.0.22-community-nt : Database - boolive-git
*********************************************************************
*/


/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*Table structure for table `contents` */

DROP TABLE IF EXISTS `contents`;

CREATE TABLE `contents` (
  `uri` varchar(255) NOT NULL default '' COMMENT 'Унифицированный идентификатор (путь на объект)',
  `lang` char(3) NOT NULL default '' COMMENT 'Код языка по ISO 639-3',
  `owner` int(11) NOT NULL default '0' COMMENT 'Владелец',
  `date` int(11) NOT NULL COMMENT 'Дата создания объекта',
  `level` int(11) NOT NULL default '1' COMMENT 'Уровень вложенности относительно корня',
  `order` int(11) NOT NULL default '1' COMMENT 'Порядковый номер',
  `proto` varchar(255) default NULL COMMENT 'uri прототипа',
  `value` varchar(255) default NULL COMMENT 'Значение',
  `is_logic` tinyint(4) NOT NULL default '0' COMMENT 'Признак, есть ли класс у объекта',
  `is_file` tinyint(4) NOT NULL default '0' COMMENT 'Является ли объект файлом',
  `is_history` tinyint(4) NOT NULL default '0' COMMENT 'Признак, является ли запись историей',
  `is_delete` tinyint(4) NOT NULL default '0' COMMENT 'Признак, удален объект или нет',
  `is_hidden` tinyint(4) NOT NULL default '0' COMMENT 'Признак, скрытый объект или нет',
  PRIMARY KEY  (`uri`,`lang`,`owner`,`date`),
  KEY `orders` (`order`),
  KEY `sate` (`is_history`,`is_delete`,`is_hidden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `contents` */

INSERT INTO `library` (`uri`, `lang`, `owner`, `date`, `level`, `order`, `proto`, `value`, `is_logic`, `is_file`, `is_history`, `is_delete`, `is_hidden`) VALUES ('/library/basic', '', 0, 0, 3, 1, '/library/basic/Package', NULL, 0, 0, 0, 0, 0), ('/library/basic/contents', '', 0, 0, 4, 1, '/library/basic/Package', NULL, 0, 0, 0, 0, 0), ('/library/basic/contents/Comment', '', 0, 0, 5, 1, NULL, NULL, 1, 0, 0, 0, 0), ('/library/basic/contents/Comment/text', '', 0, 0, 6, 1, NULL, NULL, 0, 0, 0, 0, 0), ('/library/basic/contents/Feedback', '', 0, 0, 5, 2, NULL, NULL, 0, 0, 0, 0, 0), ('/library/basic/contents/Feedback/email_from', '', 0, 0, 6, 1, '/library/basic/simple/Email', NULL, 0, 0, 0, 0, 0), ('/library/basic/contents/Feedback/email_to', '', 0, 0, 6, 2, '/library/basic/simple/Email', NULL, 0, 0, 0, 0, 0), ('/library/basic/contents/Feedback/message', '', 0, 0, 6, 3, NULL, NULL, 0, 0, 0, 0, 0), ('/library/basic/contents/Head', '', 0, 0, 5, 3, NULL, NULL, 0, 0, 0, 0, 0), ('/library/basic/contents/Image', '', 0, 0, 5, 4, NULL, NULL, 1, 0, 0, 0, 0), ('/library/basic/contents/Keyword', '', 0, 0, 5, 5, NULL, NULL, 1, 0, 0, 0, 0), ('/library/basic/contents/lists', '', 0, 0, 5, 6, '/library/basic/Package', NULL, 0, 0, 0, 0, 0), ('/library/basic/contents/lists/Item', '', 0, 0, 6, 2, NULL, NULL, 0, 0, 0, 0, 0), ('/library/basic/contents/lists/List', '', 0, 0, 6, 1, NULL, NULL, 0, 0, 0, 0, 0), ('/library/basic/contents/Page', '', 0, 0, 5, 11, NULL, NULL, 1, 0, 0, 0, 0), ('/library/basic/contents/Page/comments', '', 0, 0, 6, 4, NULL, NULL, 0, 0, 0, 0, 0), ('/library/basic/contents/Page/description', '', 0, 0, 6, 2, NULL, NULL, 0, 0, 0, 0, 0), ('/library/basic/contents/Page/keywords', '', 0, 0, 6, 3, NULL, NULL, 0, 0, 0, 0, 0), ('/library/basic/contents/Page/text', '', 0, 0, 6, 1, '/library/basic/contents/RichText', NULL, 0, 0, 0, 0, 0), ('/library/basic/contents/Page/title', '', 0, 0, 6, 0, NULL, 'Страница', 0, 0, 0, 0, 0), ('/library/basic/contents/Paragraph', '', 0, 0, 5, 8, NULL, NULL, 0, 0, 0, 0, 0), ('/library/basic/contents/Part', '', 0, 0, 5, 10, NULL, NULL, 0, 0, 0, 0, 0), ('/library/basic/contents/Part/title', '', 0, 0, 6, 1, NULL, 'Раздел', 0, 0, 0, 0, 0), ('/library/basic/contents/RichText', '', 0, 0, 5, 9, NULL, NULL, 0, 0, 0, 0, 0), ('/library/basic/contents/tables', '', 0, 0, 5, 7, '/library/basic/Package', NULL, 0, 0, 0, 0, 0), ('/library/basic/contents/tables/Cell', '', 0, 0, 6, 3, NULL, NULL, 0, 0, 0, 0, 0), ('/library/basic/contents/tables/Row', '', 0, 0, 6, 2, NULL, NULL, 0, 0, 0, 0, 0), ('/library/basic/contents/tables/Table', '', 0, 0, 6, 1, NULL, NULL, 0, 0, 0, 0, 0), ('/library/basic/interfaces', '', 0, 0, 4, 2, '/library/basic/Package', NULL, 0, 0, 0, 0, 0), ('/library/basic/interfaces/Css', '', 0, 0, 5, 1, NULL, NULL, 1, 0, 0, 0, 0), ('/library/basic/interfaces/Group', '', 0, 0, 5, 2, NULL, NULL, 1, 0, 0, 0, 0), ('/library/basic/interfaces/javascripts', '', 0, 0, 5, 3, '/library/basic/Package', NULL, 0, 0, 0, 0, 0), ('/library/basic/interfaces/javascripts/JavaScript', '', 0, 0, 6, 1, NULL, NULL, 1, 0, 0, 0, 0), ('/library/basic/interfaces/javascripts/JavaScript/depends', '', 0, 0, 7, 1, '/library/basic/interfaces/Group', NULL, 0, 0, 0, 0, 0), ('/library/basic/interfaces/javascripts/jQuery', '', 0, 0, 6, 2, '/library/basic/interfaces/javascripts/JavaScript', 'jQuery.js', 0, 1, 0, 0, 0), ('/library/basic/interfaces/javascripts/jQueryScript', '', 0, 0, 6, 3, '/library/basic/interfaces/javascripts/JavaScript', NULL, 1, 0, 0, 0, 0), ('/library/basic/interfaces/javascripts/jQueryScript/depends', '', 0, 0, 7, 1, '/library/basic/interfaces/javascripts/JavaScript/depends', NULL, 0, 0, 1, 1, 1), ('/library/basic/interfaces/javascripts/jQueryScript/depends/jquery', '', 0, 0, 8, 1, '/library/basic/interfaces/javascripts/jQuery', NULL, 0, 0, 0, 0, 0), ('/library/basic/interfaces/widgets', '', 0, 0, 5, 4, '/library/basic/Package', NULL, 0, 0, 0, 0, 0), ('/library/basic/interfaces/widgets/Focuser', '', 0, 0, 6, 5, '/library/basic/interfaces/widgets/Widget', NULL, 1, 0, 0, 0, 0), ('/library/basic/interfaces/widgets/Focuser/description', '', 0, 0, 7, 2, '/library/basic/interfaces/widgets/Widget/description', 'По URL запроса определяет объект и номер страницы для последующего оперирования ими подчиненными виджетами', 0, 0, 0, 0, 0), ('/library/basic/interfaces/widgets/Focuser/title', '', 0, 0, 7, 1, '/library/basic/interfaces/widgets/Widget/title', 'Фокусировщик', 0, 0, 0, 0, 0), ('/library/basic/interfaces/widgets/Html', '', 0, 0, 6, 1, NULL, 'Html.tpl', 1, 1, 0, 0, 0), ('/library/basic/interfaces/widgets/Html/body', '', 0, 0, 7, 1, '/library/basic/interfaces/Group', NULL, 0, 0, 0, 0, 0), ('/library/basic/interfaces/widgets/logo', '', 0, 0, 6, 1, '/library/basic/interfaces/widgets/Widget', 'logo.tpl', 1, 1, 0, 0, 0), ('/library/basic/interfaces/widgets/logo/image', '', 0, 0, 7, 1, '/library/basic/contents/Image', 'logo.png', 0, 1, 0, 0, 0), ('/library/basic/interfaces/widgets/Option', '', 0, 0, 6, 6, NULL, NULL, 1, 0, 0, 0, 0), ('/library/basic/interfaces/widgets/Option/description', '', 0, 0, 7, 2, NULL, 'Используется для организации вариантов отображений родительского виджета', 0, 0, 0, 0, 0), ('/library/basic/interfaces/widgets/Option/title', '', 0, 0, 7, 1, NULL, 'Вариант отображения', 0, 0, 0, 0, 0), ('/library/basic/interfaces/widgets/ViewObject', '', 0, 0, 6, 3, '/library/basic/interfaces/widgets/Widget', 'ViewObject.tpl', 1, 1, 0, 0, 0), ('/library/basic/interfaces/widgets/ViewObject/description', '', 0, 0, 7, 2, '/library/basic/interfaces/widgets/Widget/description', 'Виджет для отображения любого объекта.', 0, 0, 0, 0, 0), ('/library/basic/interfaces/widgets/ViewObject/title', '', 0, 0, 7, 1, '/library/basic/interfaces/widgets/Widget/title', 'Универсальный виджет', 0, 0, 0, 0, 0), ('/library/basic/interfaces/widgets/ViewObjectsList', '', 0, 0, 6, 4, '/library/basic/interfaces/widgets/Widget', 'ViewObjectsList.tpl', 1, 1, 0, 0, 0), ('/library/basic/interfaces/widgets/ViewObjectsList/description', '', 0, 0, 7, 2, '/library/basic/interfaces/widgets/Widget/description', 'Виджет для отображения списка любых объектов', 0, 0, 0, 0, 0), ('/library/basic/interfaces/widgets/ViewObjectsList/title', '', 0, 0, 7, 1, '/library/basic/interfaces/widgets/Widget/title', 'Универсальный виджет списка', 0, 0, 0, 0, 0), ('/library/basic/interfaces/widgets/Widget', '', 0, 0, 6, 2, NULL, NULL, 1, 0, 0, 0, 0), ('/library/basic/members', '', 0, 0, 4, 3, '/library/basic/Package', NULL, 0, 0, 0, 0, 0), ('/library/basic/members/Group', '', 0, 0, 5, 2, NULL, NULL, 1, 0, 0, 0, 0), ('/library/basic/members/Group/title', '', 0, 0, 6, 1, NULL, 'Группа пользователей', 0, 0, 0, 0, 0), ('/library/basic/members/User', '', 0, 0, 5, 1, NULL, NULL, 1, 0, 0, 0, 0), ('/library/basic/members/User/email', '', 0, 0, 6, 3, '/library/basic/simple/Email', NULL, 0, 0, 0, 0, 0), ('/library/basic/members/User/name', '', 0, 0, 6, 2, NULL, NULL, 0, 0, 0, 0, 0), ('/library/basic/members/User/title', '', 0, 0, 6, 1, NULL, 'Пользователь', 0, 0, 0, 0, 0), ('/library/basic/Package', '', 0, 0, 4, 5, NULL, NULL, 0, 0, 0, 0, 0), ('/library/basic/simple', '', 0, 0, 4, 4, '/library/basic/Package', NULL, 0, 0, 0, 0, 0), ('/library/basic/simple/Email', '', 0, 0, 5, 1, NULL, NULL, 1, 0, 0, 0, 0), ('/library/basic/simple/Number', '', 0, 0, 5, 2, NULL, NULL, 1, 0, 0, 0, 0), ('/library/layouts', '', 0, 0, 3, 2, '/library/basic/Package', NULL, 0, 0, 0, 0, 0), ('/library/layouts/boolive', '', 0, 0, 4, 1, '/library/basic/interfaces/widgets/Focuser', 'boolive.tpl', 0, 1, 0, 0, 0), ('/library/layouts/boolive/bottom', '', 0, 0, 5, 1, '/library/basic/interfaces/Group', NULL, 0, 0, 0, 0, 0), ('/library/layouts/boolive/center', '', 0, 0, 5, 1, '/library/basic/interfaces/Group', NULL, 0, 0, 0, 0, 0), ('/library/layouts/boolive/center/Content', '', 0, 0, 6, 1, '/library/basic/interfaces/widgets/ViewObject', NULL, 0, 0, 0, 0, 0), ('/library/layouts/boolive/center/Content/option_page', '', 0, 0, 7, 1, '/library/basic/interfaces/widgets/Option', '/library/basic/contents/Page', 0, 0, 0, 0, 0), ('/library/layouts/boolive/center/Content/option_page/Page', '', 0, 0, 8, 1, '/library/basic/interfaces/widgets/ViewObjectsList', NULL, 1, 0, 0, 0, 0), ('/library/layouts/boolive/center/Content/option_page/Page/option_comments', '', 0, 0, 9, 1, '/library/basic/interfaces/widgets/Option', '/library/basic/contents/Page/comments', 0, 0, 0, 0, 0), ('/library/layouts/boolive/center/Content/option_page/Page/option_comments/Comments', '', 0, 0, 10, 1, NULL, NULL, 1, 0, 0, 0, 0), ('/library/layouts/boolive/center/Content/option_page/Page/option_keywords', '', 0, 0, 9, 2, '/library/basic/interfaces/widgets/Option', '/library/basic/contents/Page/keywords', 0, 0, 0, 0, 0), ('/library/layouts/boolive/center/Content/option_page/Page/option_keywords/Keywords', '', 0, 0, 10, 1, NULL, NULL, 1, 0, 0, 0, 0), ('/library/layouts/boolive/center/Content/option_page/Page/option_text', '', 0, 0, 9, 3, '/library/basic/interfaces/widgets/Option', '/library/basic/contents/Page/text', 0, 0, 0, 0, 0), ('/library/layouts/boolive/center/Content/option_page/Page/option_text/Text', '', 0, 0, 10, 1, NULL, NULL, 1, 0, 0, 0, 0), ('/library/layouts/boolive/center/Content/option_page/Page/option_title', '', 0, 0, 9, 4, '/library/basic/interfaces/widgets/Option', '/library/basic/contents/Page/title', 0, 0, 0, 0, 0), ('/library/layouts/boolive/center/Content/option_page/Page/option_title/Title', '', 0, 0, 10, 1, NULL, NULL, 1, 0, 0, 0, 0), ('/library/layouts/boolive/center/Content/option_part', '', 0, 0, 7, 2, '/library/basic/interfaces/widgets/Option', '/library/basic/contents/Part', 0, 0, 0, 0, 0), ('/library/layouts/boolive/center/Content/option_part/Part', '', 0, 0, 8, 1, '/library/basic/interfaces/widgets/ViewObjectsList', NULL, 1, 0, 0, 0, 0), ('/library/layouts/boolive/center/Content/option_part/Part/option_page', '', 0, 0, 9, 1, '/library/basic/interfaces/widgets/Option', '/library/basic/contents/Page', 0, 0, 0, 0, 0), ('/library/layouts/boolive/center/Content/option_part/Part/option_page/PagePreview', '', 0, 0, 10, 1, NULL, NULL, 1, 0, 0, 0, 0), ('/library/layouts/boolive/center/Content/option_part/Part/option_part', '', 0, 0, 9, 2, '/library/basic/interfaces/widgets/Option', '/library/basic/contents/Part', 0, 0, 0, 0, 0), ('/library/layouts/boolive/center/Content/option_part/Part/option_part/PartPreview', '', 0, 0, 10, 1, NULL, NULL, 1, 0, 0, 0, 0), ('/library/layouts/boolive/center/Content/option_part/Part/option_title', '', 0, 0, 9, 3, '/library/basic/interfaces/widgets/Option', '/library/basic/contents/Part/title', 0, 0, 0, 0, 0), ('/library/layouts/boolive/center/Content/option_part/Part/option_title/Title', '', 0, 0, 10, 1, '/library/layouts/boolive/center/Content/option_page/Page/option_title/Title', NULL, 0, 0, 0, 0, 0), ('/library/layouts/boolive/head', '', 0, 0, 5, 1, '/library/basic/interfaces/Group', NULL, 0, 0, 0, 0, 0), ('/library/layouts/boolive/head/logo', '', 0, 0, 6, 1, '/library/basic/interfaces/widgets/logo', NULL, 0, 0, 0, 0, 0), ('/library/layouts/boolive/sidebar', '', 0, 0, 5, 1, '/library/basic/interfaces/Group', NULL, 0, 0, 0, 0, 0), ('/library/layouts/boolive/style', '', 0, 0, 5, 1, '/library/basic/interfaces/Css', 'style.css', 0, 1, 0, 0, 0),('/library/layouts/boolive/top', '', 0, 0, 5, 1, '/library/basic/interfaces/Group', NULL, 0, 0, 0, 0, 0);

/*Table structure for table `interfaces` */

DROP TABLE IF EXISTS `interfaces`;

CREATE TABLE `interfaces` (
  `uri` varchar(255) NOT NULL default '' COMMENT 'Унифицированный идентификатор (путь на объект)',
  `lang` char(3) NOT NULL default '' COMMENT 'Код языка по ISO 639-3',
  `owner` int(11) NOT NULL default '0' COMMENT 'Владелец',
  `date` int(11) NOT NULL COMMENT 'Дата создания объекта',
  `level` int(11) NOT NULL default '1' COMMENT 'Уровень вложенности относительно корня',
  `order` int(11) NOT NULL default '1' COMMENT 'Порядковый номер',
  `proto` varchar(255) default NULL COMMENT 'uri прототипа',
  `value` varchar(255) default NULL COMMENT 'Значение',
  `is_logic` tinyint(4) NOT NULL default '0' COMMENT 'Признак, есть ли класс у объекта',
  `is_file` tinyint(4) NOT NULL default '0' COMMENT 'Является ли объект файлом',
  `is_history` tinyint(4) NOT NULL default '0' COMMENT 'Признак, является ли запись историей',
  `is_delete` tinyint(4) NOT NULL default '0' COMMENT 'Признак, удален объект или нет',
  `is_hidden` tinyint(4) NOT NULL default '0' COMMENT 'Признак, скрытый объект или нет',
  PRIMARY KEY  (`uri`,`lang`,`owner`,`date`),
  KEY `orders` (`order`),
  KEY `sate` (`is_history`,`is_delete`,`is_hidden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `interfaces` */

insert  into `interfaces`(`uri`,`lang`,`owner`,`date`,`level`,`order`,`proto`,`value`,`is_logic`,`is_file`,`is_history`,`is_delete`,`is_hidden`) values ('/interfaces/html','',0,0,3,1,'/library/basic/interfaces/widgets/Html',NULL,0,0,0,0,0),('/interfaces/html/body/boolive','',0,0,5,2,'/library/layouts/boolive',NULL,0,0,0,0,0),('/interfaces/html/body/boolive/center/hello5','',0,0,5,6,'/interfaces/html/body/hello',NULL,0,0,0,1,0),('/interfaces/html/body/hello','',0,0,5,2,'/library/basic/interfaces/widgets/Widget','hello.tpl',0,1,0,1,0),('/interfaces/html/body/hello/jquery.hello','',0,0,6,2,'/library/basic/interfaces/javascripts/jQueryScript','jquery.hello.js',0,1,0,1,0),('/interfaces/html/body/hello/style','',0,0,6,1,'/library/basic/interfaces/Css','style.css',0,1,0,1,0),('/interfaces/html/body/hello2','',0,0,5,3,'/interfaces/html/body/hello',NULL,0,0,0,1,0),('/interfaces/html/body/hello3','',0,0,5,4,'/interfaces/html/body/hello',NULL,0,0,0,1,0),('/interfaces/html/body/hello4','',0,0,5,5,'/interfaces/html/body/hello',NULL,0,0,0,1,0);

/*Table structure for table `keywords` */

DROP TABLE IF EXISTS `keywords`;

CREATE TABLE `keywords` (
  `uri` varchar(255) NOT NULL default '' COMMENT 'Унифицированный идентификатор (путь на объект)',
  `lang` char(3) NOT NULL default '' COMMENT 'Код языка по ISO 639-3',
  `owner` int(11) NOT NULL default '0' COMMENT 'Владелец',
  `date` int(11) NOT NULL COMMENT 'Дата создания объекта',
  `level` int(11) NOT NULL default '1' COMMENT 'Уровень вложенности относительно корня',
  `order` int(11) NOT NULL default '1' COMMENT 'Порядковый номер',
  `proto` varchar(255) default NULL COMMENT 'uri прототипа',
  `value` varchar(255) default NULL COMMENT 'Значение',
  `is_logic` tinyint(4) NOT NULL default '0' COMMENT 'Признак, есть ли класс у объекта',
  `is_file` tinyint(4) NOT NULL default '0' COMMENT 'Является ли объект файлом',
  `is_history` tinyint(4) NOT NULL default '0' COMMENT 'Признак, является ли запись историей',
  `is_delete` tinyint(4) NOT NULL default '0' COMMENT 'Признак, удален объект или нет',
  `is_hidden` tinyint(4) NOT NULL default '0' COMMENT 'Признак, скрытый объект или нет',
  PRIMARY KEY  (`uri`,`lang`,`owner`,`date`),
  KEY `orders` (`order`),
  KEY `sate` (`is_history`,`is_delete`,`is_hidden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `keywords` */

insert  into `keywords`(`uri`,`lang`,`owner`,`date`,`level`,`order`,`proto`,`value`,`is_logic`,`is_file`,`is_history`,`is_delete`,`is_hidden`) values ('/keywords/cms','',0,0,3,1,'/library/basic/contents/Keyword','1',0,0,0,0,0),('/keywords/framework','',0,0,3,1,'/library/basic/contents/Keyword','0',0,0,0,0,0),('/keywords/php','',0,0,3,1,'/library/basic/contents/Keyword','1',0,0,0,0,0);

/*Table structure for table `library` */

DROP TABLE IF EXISTS `library`;

CREATE TABLE `library` (
  `uri` varchar(255) NOT NULL default '' COMMENT 'Унифицированный идентификатор (путь на объект)',
  `lang` char(3) NOT NULL default '' COMMENT 'Код языка по ISO 639-3',
  `owner` int(11) NOT NULL default '0' COMMENT 'Владелец',
  `date` int(11) NOT NULL COMMENT 'Дата создания объекта',
  `level` int(11) NOT NULL default '1' COMMENT 'Уровень вложенности относительно корня',
  `order` int(11) NOT NULL default '1' COMMENT 'Порядковый номер',
  `proto` varchar(255) default NULL COMMENT 'uri прототипа',
  `value` varchar(255) default NULL COMMENT 'Значение',
  `is_logic` tinyint(4) NOT NULL default '0' COMMENT 'Признак, есть ли класс у объекта',
  `is_file` tinyint(4) NOT NULL default '0' COMMENT 'Является ли объект файлом',
  `is_history` tinyint(4) NOT NULL default '0' COMMENT 'Признак, является ли запись историей',
  `is_delete` tinyint(4) NOT NULL default '0' COMMENT 'Признак, удален объект или нет',
  `is_hidden` tinyint(4) NOT NULL default '0' COMMENT 'Признак, скрытый объект или нет',
  PRIMARY KEY  (`uri`,`lang`,`owner`,`date`),
  KEY `orders` (`order`),
  KEY `sate` (`is_history`,`is_delete`,`is_hidden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `library` */

insert  into `library`(`uri`,`lang`,`owner`,`date`,`level`,`order`,`proto`,`value`,`is_logic`,`is_file`,`is_history`,`is_delete`,`is_hidden`) values ('/library/basic','',0,0,3,1,'/library/basic/Package',NULL,0,0,0,0,0),('/library/basic/contents','',0,0,4,1,'/library/basic/Package',NULL,0,0,0,0,0),('/library/basic/contents/Comment','',0,0,5,1,NULL,NULL,1,0,0,0,0),('/library/basic/contents/Comment/text','',0,0,6,1,NULL,NULL,0,0,0,0,0),('/library/basic/contents/Feedback','',0,0,5,2,NULL,NULL,0,0,0,0,0),('/library/basic/contents/Feedback/email_from','',0,0,6,1,'/library/basic/simple/Email',NULL,0,0,0,0,0),('/library/basic/contents/Feedback/email_to','',0,0,6,2,'/library/basic/simple/Email',NULL,0,0,0,0,0),('/library/basic/contents/Feedback/message','',0,0,6,3,NULL,NULL,0,0,0,0,0),('/library/basic/contents/Head','',0,0,5,3,NULL,NULL,0,0,0,0,0),('/library/basic/contents/Image','',0,0,5,4,NULL,NULL,1,0,0,0,0),('/library/basic/contents/Keyword','',0,0,5,5,NULL,NULL,1,0,0,0,0),('/library/basic/contents/lists','',0,0,5,6,'/library/basic/Package',NULL,0,0,0,0,0),('/library/basic/contents/lists/Item','',0,0,6,2,NULL,NULL,0,0,0,0,0),('/library/basic/contents/lists/List','',0,0,6,1,NULL,NULL,0,0,0,0,0),('/library/basic/contents/Page','',0,0,5,11,NULL,NULL,1,0,0,0,0),('/library/basic/contents/Page/comments','',0,0,6,4,NULL,NULL,0,0,0,0,0),('/library/basic/contents/Page/description','',0,0,6,2,NULL,NULL,0,0,0,0,0),('/library/basic/contents/Page/keywords','',0,0,6,3,NULL,NULL,0,0,0,0,0),('/library/basic/contents/Page/text','',0,0,6,1,'/library/basic/contents/RichText',NULL,0,0,0,0,0),('/library/basic/contents/Page/title','',0,0,6,0,NULL,'Страница',0,0,0,0,0),('/library/basic/contents/Paragraph','',0,0,5,8,NULL,NULL,0,0,0,0,0),('/library/basic/contents/Part','',0,0,5,10,NULL,NULL,0,0,0,0,0),('/library/basic/contents/Part/title','',0,0,6,1,NULL,'Раздел',0,0,0,0,0),('/library/basic/contents/RichText','',0,0,5,9,NULL,NULL,0,0,0,0,0),('/library/basic/contents/RichText/title','',0,0,6,1,NULL,'Форматированный текст',0,0,0,0,0),('/library/basic/contents/tables','',0,0,5,7,'/library/basic/Package',NULL,0,0,0,0,0),('/library/basic/contents/tables/Cell','',0,0,6,3,NULL,NULL,0,0,0,0,0),('/library/basic/contents/tables/Row','',0,0,6,2,NULL,NULL,0,0,0,0,0),('/library/basic/contents/tables/Table','',0,0,6,1,NULL,NULL,0,0,0,0,0),('/library/basic/interfaces','',0,0,4,2,'/library/basic/Package',NULL,0,0,0,0,0),('/library/basic/interfaces/Css','',0,0,5,1,NULL,NULL,1,0,0,0,0),('/library/basic/interfaces/Group','',0,0,5,2,NULL,NULL,1,0,0,0,0),('/library/basic/interfaces/javascripts','',0,0,5,3,'/library/basic/Package',NULL,0,0,0,0,0),('/library/basic/interfaces/javascripts/JavaScript','',0,0,6,1,NULL,NULL,1,0,0,0,0),('/library/basic/interfaces/javascripts/JavaScript/depends','',0,0,7,1,'/library/basic/interfaces/Group',NULL,0,0,0,0,0),('/library/basic/interfaces/javascripts/jQuery','',0,0,6,2,'/library/basic/interfaces/javascripts/JavaScript','jQuery.js',0,1,0,0,0),('/library/basic/interfaces/javascripts/jQueryScript','',0,0,6,3,'/library/basic/interfaces/javascripts/JavaScript',NULL,1,0,0,0,0),('/library/basic/interfaces/javascripts/jQueryScript/depends','',0,0,7,1,'/library/basic/interfaces/javascripts/JavaScript/depends',NULL,0,0,1,1,1),('/library/basic/interfaces/javascripts/jQueryScript/depends/jquery','',0,0,8,1,'/library/basic/interfaces/javascripts/jQuery',NULL,0,0,0,0,0),('/library/basic/interfaces/widgets','',0,0,5,4,'/library/basic/Package',NULL,0,0,0,0,0),('/library/basic/interfaces/widgets/Focuser','',0,0,6,5,'/library/basic/interfaces/widgets/Widget',NULL,1,0,0,0,0),('/library/basic/interfaces/widgets/Focuser/description','',0,0,7,2,'/library/basic/interfaces/widgets/Widget/description','По URL запроса определяет объект и номер страницы для последующего оперирования ими подчиненными виджетами',0,0,0,0,0),('/library/basic/interfaces/widgets/Focuser/title','',0,0,7,1,'/library/basic/interfaces/widgets/Widget/title','Фокусировщик',0,0,0,0,0),('/library/basic/interfaces/widgets/Html','',0,0,6,1,NULL,'Html.tpl',1,1,0,0,0),('/library/basic/interfaces/widgets/Html/body','',0,0,7,1,'/library/basic/interfaces/Group',NULL,0,0,0,0,0),('/library/basic/interfaces/widgets/Menu','',0,0,6,7,'/library/basic/interfaces/widgets/Widget','Menu.tpl',1,1,0,0,0),('/library/basic/interfaces/widgets/Menu/items','',0,0,7,2,'/contents',NULL,0,0,0,0,0),('/library/basic/interfaces/widgets/Menu/style','',0,0,7,1,'/library/basic/interfaces/Css','style.css',0,1,0,0,0),('/library/basic/interfaces/widgets/Menu/title','',0,0,7,1,'/library/basic/interfaces/widgets/Widget/title','Меню',0,0,0,0,0),('/library/basic/interfaces/widgets/Menu/views','',0,0,7,3,'/library/basic/interfaces/widgets/ViewObjectsList','views.tpl',1,1,0,0,0),('/library/basic/interfaces/widgets/Menu/views/cond_page','',0,0,8,1,'/library/basic/interfaces/widgets/Option','/library/basic/contents/Page',0,0,0,0,0),('/library/basic/interfaces/widgets/Menu/views/cond_page/ItemPage','',0,0,9,1,'/library/basic/interfaces/widgets/Menu/views','ItemPage.tpl',1,1,0,0,0),('/library/basic/interfaces/widgets/Menu/views/cond_part','',0,0,8,2,'/library/basic/interfaces/widgets/Option','/library/basic/contents/Part',0,0,0,0,0),('/library/basic/interfaces/widgets/Menu/views/cond_part/ItemPart','',0,0,9,1,'/library/basic/interfaces/widgets/Menu/views/cond_page/ItemPage',NULL,0,0,0,0,0),('/library/basic/interfaces/widgets/Option','',0,0,6,6,NULL,NULL,1,0,0,0,0),('/library/basic/interfaces/widgets/Option/description','',0,0,7,2,NULL,'Используется для организации вариантов отображений родительского виджета',0,0,0,1,0),('/library/basic/interfaces/widgets/Option/title','',0,0,7,1,NULL,'Вариант отображения',0,0,0,1,0),('/library/basic/interfaces/widgets/ViewObject','',0,0,6,3,'/library/basic/interfaces/widgets/Widget','ViewObject.tpl',1,1,0,0,0),('/library/basic/interfaces/widgets/ViewObject/description','',0,0,7,2,'/library/basic/interfaces/widgets/Widget/description','Виджет для отображения любого объекта.',0,0,0,0,0),('/library/basic/interfaces/widgets/ViewObject/title','',0,0,7,1,'/library/basic/interfaces/widgets/Widget/title','Универсальный виджет',0,0,0,0,0),('/library/basic/interfaces/widgets/ViewObjectsList','',0,0,6,4,'/library/basic/interfaces/widgets/Widget','ViewObjectsList.tpl',1,1,0,0,0),('/library/basic/interfaces/widgets/ViewObjectsList/description','',0,0,7,2,'/library/basic/interfaces/widgets/Widget/description','Виджет для отображения списка любых объектов',0,0,0,0,0),('/library/basic/interfaces/widgets/ViewObjectsList/title','',0,0,7,1,'/library/basic/interfaces/widgets/Widget/title','Универсальный виджет списка',0,0,0,0,0),('/library/basic/interfaces/widgets/Widget','',0,0,6,2,NULL,NULL,1,0,0,0,0),('/library/basic/members','',0,0,4,3,'/library/basic/Package',NULL,0,0,0,0,0),('/library/basic/members/Group','',0,0,5,2,NULL,NULL,1,0,0,0,0),('/library/basic/members/Group/title','',0,0,6,1,NULL,'Группа пользователей',0,0,0,0,0),('/library/basic/members/User','',0,0,5,1,NULL,NULL,1,0,0,0,0),('/library/basic/members/User/email','',0,0,6,3,'/library/basic/simple/Email',NULL,0,0,0,0,0),('/library/basic/members/User/name','',0,0,6,2,NULL,NULL,0,0,0,0,0),('/library/basic/members/User/title','',0,0,6,1,NULL,'Пользователь',0,0,0,0,0),('/library/basic/Package','',0,0,4,5,NULL,NULL,0,0,0,0,0),('/library/basic/simple','',0,0,4,4,'/library/basic/Package',NULL,0,0,0,0,0),('/library/basic/simple/Email','',0,0,5,1,NULL,NULL,1,0,0,0,0),('/library/basic/simple/Number','',0,0,5,2,NULL,NULL,1,0,0,0,0),('/library/layouts','',0,0,3,2,'/library/basic/Package',NULL,0,0,0,0,0),('/library/layouts/boolive','',0,0,4,1,'/library/basic/interfaces/widgets/Focuser','boolive.tpl',0,1,0,0,0),('/library/layouts/boolive/bottom','',0,0,5,1,'/library/basic/interfaces/Group',NULL,0,0,0,0,0),('/library/layouts/boolive/center','',0,0,5,1,'/library/basic/interfaces/Group',NULL,0,0,0,0,0),('/library/layouts/boolive/center/Content','',0,0,6,1,'/library/basic/interfaces/widgets/ViewObject',NULL,0,0,0,0,0),('/library/layouts/boolive/center/Content/option_page','',0,0,7,1,'/library/basic/interfaces/widgets/Option','/library/basic/contents/Page',0,0,0,0,0),('/library/layouts/boolive/center/Content/option_page/Page','',0,0,8,1,'/library/basic/interfaces/widgets/ViewObjectsList',NULL,1,0,0,0,0),('/library/layouts/boolive/center/Content/option_page/Page/option_comments','',0,0,9,1,'/library/basic/interfaces/widgets/Option','/library/basic/contents/Page/comments',0,0,0,0,0),('/library/layouts/boolive/center/Content/option_page/Page/option_comments/Comments','',0,0,10,1,NULL,NULL,1,0,0,0,0),('/library/layouts/boolive/center/Content/option_page/Page/option_keywords','',0,0,9,2,'/library/basic/interfaces/widgets/Option','/library/basic/contents/Page/keywords',0,0,0,0,0),('/library/layouts/boolive/center/Content/option_page/Page/option_keywords/Keywords','',0,0,10,1,NULL,NULL,1,0,0,0,0),('/library/layouts/boolive/center/Content/option_page/Page/option_text','',0,0,9,3,'/library/basic/interfaces/widgets/Option','/library/basic/contents/Page/text',0,0,0,0,0),('/library/layouts/boolive/center/Content/option_page/Page/option_text/Text','',0,0,10,1,NULL,NULL,1,0,0,0,0),('/library/layouts/boolive/center/Content/option_page/Page/option_title','',0,0,9,4,'/library/basic/interfaces/widgets/Option','/library/basic/contents/Page/title',0,0,0,0,0),('/library/layouts/boolive/center/Content/option_page/Page/option_title/Title','',0,0,10,1,NULL,NULL,1,0,0,0,0),('/library/layouts/boolive/center/Content/option_part','',0,0,7,2,'/library/basic/interfaces/widgets/Option','/library/basic/contents/Part',0,0,0,0,0),('/library/layouts/boolive/center/Content/option_part/Part','',0,0,8,1,'/library/basic/interfaces/widgets/ViewObjectsList',NULL,1,0,0,0,0),('/library/layouts/boolive/center/Content/option_part/Part/option_page','',0,0,9,1,'/library/basic/interfaces/widgets/Option','/library/basic/contents/Page',0,0,0,0,0),('/library/layouts/boolive/center/Content/option_part/Part/option_page/PagePreview','',0,0,10,1,NULL,NULL,1,0,0,0,0),('/library/layouts/boolive/center/Content/option_part/Part/option_part','',0,0,9,2,'/library/basic/interfaces/widgets/Option','/library/basic/contents/Part',0,0,0,0,0),('/library/layouts/boolive/center/Content/option_part/Part/option_part/PartPreview','',0,0,10,1,NULL,NULL,1,0,0,0,0),('/library/layouts/boolive/center/Content/option_part/Part/option_title','',0,0,9,3,'/library/basic/interfaces/widgets/Option','/library/basic/contents/Part/title',0,0,0,0,0),('/library/layouts/boolive/center/Content/option_part/Part/option_title/Title','',0,0,10,1,'/library/layouts/boolive/center/Content/option_page/Page/option_title/Title',NULL,0,0,0,0,0),('/library/layouts/boolive/head','',0,0,5,1,'/library/basic/interfaces/Group',NULL,0,0,0,0,0),('/library/layouts/boolive/sidebar','',0,0,5,1,'/library/basic/interfaces/Group',NULL,0,0,0,0,0),('/library/layouts/boolive/sidebar/menu','',0,0,6,1,'/library/basic/interfaces/widgets/Menu',NULL,0,0,0,0,0),('/library/layouts/boolive/sidebar/menu/items/news/not_auto','',0,0,9,1,NULL,'0',0,0,0,0,0),('/library/layouts/boolive/sidebar/menu/items/news/title','',0,0,9,1,NULL,'Новости!!',0,0,0,0,0),('/library/layouts/boolive/sidebar/menu/style','',0,0,7,1,'/library/basic/interfaces/widgets/Menu/style','style.css',0,1,0,0,0),('/library/layouts/boolive/sidebar/menu/title','',0,0,7,1,'/library/basic/interfaces/widgets/Menu/title','Заголовок меню',0,0,0,0,0),('/library/layouts/boolive/style','',0,0,5,1,'/library/basic/interfaces/Css','style.css',0,1,0,0,0),('/library/layouts/boolive/top','',0,0,5,1,'/library/basic/interfaces/Group',NULL,0,0,0,0,0),('/library/layouts/boolive/top/menu','',0,0,6,1,'/library/basic/interfaces/widgets/Menu','menu.tpl',0,1,0,0,0),('/library/layouts/boolive/top/menu/items/main/not_auto','',0,0,9,1,NULL,'1',0,0,0,0,0),('/library/layouts/boolive/top/menu/items/news/not_auto','',0,0,9,1,NULL,'1',0,0,0,0,0),('/library/layouts/boolive/top/menu/style','',0,0,7,1,'/library/basic/interfaces/Css','style.css',0,1,0,0,0);

/*Table structure for table `members` */

DROP TABLE IF EXISTS `members`;

CREATE TABLE `members` (
  `uri` varchar(255) NOT NULL default '' COMMENT 'Унифицированный идентификатор (путь на объект)',
  `lang` char(3) NOT NULL default '' COMMENT 'Код языка по ISO 639-3',
  `owner` int(11) NOT NULL default '0' COMMENT 'Владелец',
  `date` int(11) NOT NULL COMMENT 'Дата создания объекта',
  `level` int(11) NOT NULL default '1' COMMENT 'Уровень вложенности относительно корня',
  `order` int(11) NOT NULL default '1' COMMENT 'Порядковый номер',
  `proto` varchar(255) default NULL COMMENT 'uri прототипа',
  `value` varchar(255) default NULL COMMENT 'Значение',
  `is_logic` tinyint(4) NOT NULL default '0' COMMENT 'Признак, есть ли класс у объекта',
  `is_file` tinyint(4) NOT NULL default '0' COMMENT 'Является ли объект файлом',
  `is_history` tinyint(4) NOT NULL default '0' COMMENT 'Признак, является ли запись историей',
  `is_delete` tinyint(4) NOT NULL default '0' COMMENT 'Признак, удален объект или нет',
  `is_hidden` tinyint(4) NOT NULL default '0' COMMENT 'Признак, скрытый объект или нет',
  PRIMARY KEY  (`uri`,`lang`,`owner`,`date`),
  KEY `orders` (`order`),
  KEY `sate` (`is_history`,`is_delete`,`is_hidden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `members` */

insert  into `members`(`uri`,`lang`,`owner`,`date`,`level`,`order`,`proto`,`value`,`is_logic`,`is_file`,`is_history`,`is_delete`,`is_hidden`) values ('/members/guests','',0,0,3,1,'/library/basic/members/Group',NULL,0,0,0,0,0),('/members/guests/title','',0,0,4,1,'/library/basic/members/Group/title','Гости',0,0,0,0,0),('/members/registered','',0,0,3,2,'/library/basic/members/Group',NULL,0,0,0,0,0),('/members/registered/admins','',0,0,4,2,'/library/basic/members/Group',NULL,0,0,0,0,0),('/members/registered/admins/title','',0,0,5,1,'/library/basic/members/Group/title','Администраторы',0,0,0,0,0),('/members/registered/admins/vova','',0,0,5,2,'/library/basic/members/User','password_hash',0,0,0,0,0),('/members/registered/admins/vova/email','',0,0,6,2,'/library/basic/members/User/email','boolive@yandex.ru',0,0,0,0,0),('/members/registered/admins/vova/name','',0,0,6,1,'/library/basic/members/User/name','Вова',0,0,0,0,0),('/members/registered/title','',0,0,4,1,'/library/basic/members/Group/title','Зарегистрированные',0,0,0,0,0);

/*Table structure for table `site` */

DROP TABLE IF EXISTS `site`;

CREATE TABLE `site` (
  `uri` varchar(255) NOT NULL default '' COMMENT 'Унифицированный идентификатор (путь на объект)',
  `lang` char(3) NOT NULL default '' COMMENT 'Код языка по ISO 639-3',
  `owner` int(11) NOT NULL default '0' COMMENT 'Владелец',
  `date` int(11) NOT NULL COMMENT 'Дата создания объекта',
  `level` int(11) NOT NULL default '1' COMMENT 'Уровень вложенности относительно корня',
  `order` int(11) NOT NULL default '1' COMMENT 'Порядковый номер',
  `proto` varchar(255) default NULL COMMENT 'uri прототипа',
  `value` varchar(255) default NULL COMMENT 'Значение',
  `is_logic` tinyint(4) NOT NULL default '0' COMMENT 'Признак, есть ли класс у объекта',
  `is_file` tinyint(4) NOT NULL default '0' COMMENT 'Является ли объект файлом',
  `is_history` tinyint(4) NOT NULL default '0' COMMENT 'Признак, является ли запись историей',
  `is_delete` tinyint(4) NOT NULL default '0' COMMENT 'Признак, удален объект или нет',
  `is_hidden` tinyint(4) NOT NULL default '0' COMMENT 'Признак, скрытый объект или нет',
  PRIMARY KEY  (`uri`,`lang`,`owner`,`date`),
  KEY `orders` (`order`),
  KEY `sate` (`is_history`,`is_delete`,`is_hidden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `site` */

insert  into `site`(`uri`,`lang`,`owner`,`date`,`level`,`order`,`proto`,`value`,`is_logic`,`is_file`,`is_history`,`is_delete`,`is_hidden`) values ('/contents','',0,1342077181,2,1,NULL,NULL,0,0,0,0,0),('/interfaces','',0,1342082233,2,1,'/library/basic/interfaces/Group',NULL,0,0,0,0,0),('/keywords','',0,1342077181,2,1,NULL,NULL,0,0,0,0,0),('/library','',0,1342077181,2,2,NULL,NULL,0,0,0,0,0),('/members','',0,1342077181,2,1,'/library/basic/members/Group',NULL,0,0,0,0,0);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
