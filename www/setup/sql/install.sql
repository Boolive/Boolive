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

insert  into `interfaces`(`uri`,`lang`,`owner`,`date`,`level`,`order`,`is_history`,`is_delete`,`is_hidden`,`is_logic`,`is_file`,`proto`,`value`) values ('/interfaces/html','',0,0,3,1,0,0,0,0,0,'/library/basic/html',NULL),('/interfaces/html/body/content2','',0,0,5,2,0,0,0,0,0,NULL,' World!');

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

insert  into `library`(`uri`,`lang`,`owner`,`date`,`level`,`order`,`is_history`,`is_delete`,`is_hidden`,`is_logic`,`is_file`,`proto`,`value`) values ('/library/basic','',0,0,3,1,0,0,0,0,0,'/library/basic/package',NULL),('/library/basic/css','',0,0,3,1,0,0,0,1,0,NULL,NULL),('/library/basic/group','',0,0,4,2,0,0,0,1,0,NULL,NULL),('/library/basic/html','',0,0,4,3,0,0,0,1,1,NULL,'html.tpl'),('/library/basic/html/body','',0,0,5,1,0,0,0,1,0,'/library/basic/group',NULL),('/library/basic/html/body/content','',0,0,6,1,0,0,0,0,0,NULL,'Hello'),('/library/basic/javascripts','',0,0,4,4,0,0,0,0,0,'/library/basic/package',NULL),('/library/basic/javascripts/javascript','',0,0,5,1,0,0,0,1,0,NULL,NULL),('/library/basic/javascripts/javascript/depends','',0,0,6,1,0,0,0,0,0,'/library/basic/group',NULL),('/library/basic/javascripts/jquery','',0,0,5,2,0,0,0,0,1,'/library/basic/javascripts/javascript',NULL),('/library/basic/javascripts/jqueryscript','',0,0,5,3,0,0,0,1,0,'/library/basic/javascripts/javascript',NULL),('/library/basic/javascripts/jqueryscript/depends/jquery','',0,0,7,1,0,0,0,0,0,'/library/basic/javascripts/jquery',NULL),('/library/basic/package','',0,0,4,5,0,0,0,0,0,NULL,NULL);

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

insert  into `site`(`uri`,`lang`,`owner`,`date`,`level`,`order`,`is_history`,`is_delete`,`is_hidden`,`is_logic`,`is_file`,`proto`,`value`) values ('/interfaces','',0,1342082233,2,1,0,0,0,0,0,'/library/basic/group',NULL),('/library','',0,1342077181,2,2,0,0,0,0,0,NULL,NULL);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
