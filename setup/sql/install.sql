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

insert  into `contents`(`uri`,`lang`,`owner`,`date`,`level`,`order`,`proto`,`value`,`is_logic`,`is_file`,`is_history`,`is_delete`,`is_hidden`) values ('/contents/contacts','',0,0,3,2,'/library/basic/contents/Page',NULL,0,0,0,0,0),('/contents/contacts/text','',0,0,4,2,'/library/basic/contents/Page/text',NULL,0,0,0,0,0),('/contents/contacts/text/feedback','',0,0,5,3,'/library/basic/contents/Feedback',NULL,0,0,0,0,0),('/contents/contacts/text/feedback/email_from','',0,0,6,1,'/library/basic/contents/Feedback/email_from','',0,0,0,0,0),('/contents/contacts/text/feedback/email_to','',0,0,6,2,'/library/basic/contents/Feedback/email_to','info@boolive.ru',0,0,0,0,0),('/contents/contacts/text/feedback/message','',0,0,6,3,'/library/basic/contents/Feedback/mesage','',0,0,0,0,0),('/contents/contacts/text/head1','',0,0,5,0,'/library/basic/contents/Head','Наш адрес',0,0,0,0,0),('/contents/contacts/text/p1','',0,0,5,1,'/library/basic/contents/Paragraph','г. Екатеринбург, ул Ленина, дом 1, офис 999',0,0,0,0,0),('/contents/contacts/text/p2','',0,0,5,2,'/library/basic/contents/Paragraph','Работаем груглосуточно',0,0,0,0,0),('/contents/contacts/title','',0,0,4,1,'/library/basic/contents/Page/title','Контакты',0,0,0,0,0),('/contents/main','',0,0,3,1,'/library/basic/contents/Page',NULL,0,0,0,0,0),('/contents/main/author','',0,0,4,4,'/members/registered/admins/vova',NULL,0,0,0,0,0),('/contents/main/comments','',0,0,4,6,'/library/basic/contents/Page/comments',NULL,0,0,0,0,0),('/contents/main/comments/comment1','',0,0,5,1,'/library/basic/contents/Comment',NULL,0,0,0,0,0),('/contents/main/comments/comment1/author','',0,0,6,1,'/members/registered/admins/vova',NULL,0,0,0,0,0),('/contents/main/comments/comment1/comment11','',0,0,6,3,'/library/basic/contents/Comment',NULL,0,0,0,0,0),('/contents/main/comments/comment1/comment11/author','',0,0,7,1,'/members/registered/admins/vova',NULL,0,0,0,0,0),('/contents/main/comments/comment1/comment11/text','',0,0,7,2,'/library/basic/contents/Comment/text','Комментарий на первый комментарий',0,0,0,0,0),('/contents/main/comments/comment1/text','',0,0,6,2,'/library/basic/contents/Comment/text','Текст первого коммента',0,0,0,0,0),('/contents/main/comments/comment2','',0,0,5,2,'/library/basic/contents/Comment',NULL,0,0,0,0,0),('/contents/main/comments/comment2/author','',0,0,6,1,'/members/registered/admins/vova',NULL,0,0,0,0,0),('/contents/main/comments/comment2/text','',0,0,6,2,'/library/basic/contents/Comment/text','Текст второго комментария к главной странице сайта',0,0,0,0,0),('/contents/main/description','',0,0,4,3,'/library/basic/contents/Page/description','Главная страница первого простейшего сайта',0,0,0,0,0),('/contents/main/keywords','',0,0,4,5,'/library/basic/contents/Page/keywords',NULL,0,0,0,0,0),('/contents/main/keywords/cms','',0,0,5,1,'/keywords/cms',NULL,0,0,0,0,0),('/contents/main/keywords/php','',0,0,5,2,'/keywords/php',NULL,0,0,0,0,0),('/contents/main/sub_page','',0,0,4,7,'/library/basic/contents/Page',NULL,0,0,0,0,0),('/contents/main/sub_page/author','',0,0,5,4,'/members/registered/admins/vova',NULL,0,0,0,0,0),('/contents/main/sub_page/description','',0,0,5,3,'/library/basic/contents/Page/description','Подчиненная страница. Это её короткое описание для SEO',0,0,0,0,0),('/contents/main/sub_page/text','',0,0,5,2,'/library/basic/contents/Page/text',NULL,0,0,0,0,0),('/contents/main/sub_page/text/head1','',0,0,6,0,'/library/basic/contents/Head','Заголовок подчиненной страницы',0,0,0,0,0),('/contents/main/sub_page/text/head2','',0,0,6,2,'/library/basic/contents/Head','Подзаголовок подчиненной страницы',0,0,0,0,0),('/contents/main/sub_page/text/img1','',0,0,6,4,'/library/basic/contents/Image','img1.jpg',0,1,0,0,0),('/contents/main/sub_page/text/p1','',0,0,6,1,'/library/basic/contents/Paragraph','Первый абзац подчиенной страницы... текст... текст...',0,0,0,0,0),('/contents/main/sub_page/text/p2','',0,0,6,3,'/library/basic/contents/Paragraph','Второй абзац подчиенной страницы... текст... текст...',0,0,0,0,0),('/contents/main/sub_page/title','',0,0,5,1,'/library/basic/contents/Page/title','Подстраница',0,0,0,0,0),('/contents/main/text','',0,0,4,2,'/library/basic/contents/Page/text',NULL,0,0,0,0,0),('/contents/main/text/head1','',0,0,5,1,'/library/basic/contents/Head','Заголовок главной страницы',0,0,0,0,0),('/contents/main/text/img1','',0,0,5,3,'/library/basic/contents/Image','img1.jpg',0,1,0,0,0),('/contents/main/text/p1','',0,0,5,2,'/library/basic/contents/Paragraph','Добро пожаловать на тестовый сайт. Сайт работает на новой системе Boolive 2',0,0,0,0,0),('/contents/main/text/p2','',0,0,5,4,'/library/basic/contents/Paragraph','Hello World :)',0,0,0,0,0),('/contents/main/title','',0,0,4,1,'/library/basic/contents/Page/title','Главная',0,0,0,0,0),('/contents/news','',0,0,3,3,'/library/basic/contents/Part',NULL,0,0,0,0,0),('/contents/news/news1','',0,0,4,2,'/library/basic/contents/Page',NULL,0,0,0,0,0),('/contents/news/news1/author','',0,0,5,3,'/members/registered/admins/vova',NULL,0,0,0,0,0),('/contents/news/news1/text','',0,0,5,2,'/library/basic/contents/Page/text',NULL,0,0,0,0,0),('/contents/news/news1/text/p1','',0,0,6,1,'/library/basic/contents/Paragraph','Текст новости в виде одного абзаца',0,0,0,0,0),('/contents/news/news1/title','',0,0,5,1,'/library/basic/contents/Page/title','Первая новость',0,0,0,0,0),('/contents/news/news2','',0,0,4,3,'/library/basic/contents/Page',NULL,0,0,0,0,0),('/contents/news/news2/author','',0,0,5,3,'/members/registered/admins/vova',NULL,0,0,0,0,0),('/contents/news/news2/text','',0,0,5,2,'/library/basic/contents/Page/text',NULL,0,0,0,0,0),('/contents/news/news2/text/p1','',0,0,6,1,'/library/basic/contents/Paragraph','Ноовсть создаётся как страница, то есть новость и есть страницой, просто она помещена в раздел Ленты новостей',0,0,0,0,0),('/contents/news/news2/title','',0,0,5,1,'/library/basic/contents/Page/title','Вторая новость',0,0,0,0,0),('/contents/news/news3','',0,0,4,4,'/library/basic/contents/Page',NULL,0,0,0,0,0),('/contents/news/news3/author','',0,0,5,3,'/members/registered/admins/vova',NULL,0,0,0,0,0),('/contents/news/news3/text','',0,0,5,2,'/library/basic/contents/Page/text',NULL,0,0,0,0,0),('/contents/news/news3/text/p1','',0,0,6,1,'/library/basic/contents/Paragraph','Текст новости в виде одного абзаца',0,0,0,0,0),('/contents/news/news3/title','',0,0,5,1,'/library/basic/contents/Page/title','Третья новость',0,0,0,0,0),('/contents/news/news4','',0,0,4,5,'/library/basic/contents/Page',NULL,0,0,0,0,0),('/contents/news/news4/author','',0,0,5,3,'/members/registered/admins/vova',NULL,0,0,0,0,0),('/contents/news/news4/text','',0,0,5,2,'/library/basic/contents/Page/text',NULL,0,0,0,0,0),('/contents/news/news4/text/p1','',0,0,6,1,'/library/basic/contents/Paragraph','Текст новости в виде одного абзаца',0,0,0,0,0),('/contents/news/news4/title','',0,0,5,1,'/library/basic/contents/Page/title','Четвертая новость',0,0,0,0,0),('/contents/news/title','',0,0,4,1,'/library/basic/contents/Part/title','Лента новостей',0,0,0,0,0);

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

insert  into `interfaces`(`uri`,`lang`,`owner`,`date`,`level`,`order`,`proto`,`value`,`is_logic`,`is_file`,`is_history`,`is_delete`,`is_hidden`) values ('/interfaces/html','',0,0,3,1,'/library/basic/interfaces/widgets/Html',NULL,0,0,0,0,0),('/interfaces/html/body/boolive','',0,0,5,2,'/library/layouts/boolive',NULL,0,0,0,0,0),('/interfaces/html/body/boolive/center/hello5','',0,0,5,6,'/interfaces/html/body/hello',NULL,0,0,0,0,0),('/interfaces/html/body/hello','',0,0,5,2,'/library/basic/interfaces/widgets/Widget','hello.tpl',0,1,0,1,0),('/interfaces/html/body/hello/jquery.hello','',0,0,6,2,'/library/basic/interfaces/javascripts/jQueryScript','jquery.hello.js',0,1,0,1,0),('/interfaces/html/body/hello/style','',0,0,6,1,'/library/basic/interfaces/Css','style.css',0,1,0,1,0),('/interfaces/html/body/hello2','',0,0,5,3,'/interfaces/html/body/hello',NULL,0,0,0,1,0),('/interfaces/html/body/hello3','',0,0,5,4,'/interfaces/html/body/hello',NULL,0,0,0,1,0),('/interfaces/html/body/hello4','',0,0,5,5,'/interfaces/html/body/hello',NULL,0,0,0,1,0);

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

insert  into `library`(`uri`,`lang`,`owner`,`date`,`level`,`order`,`proto`,`value`,`is_logic`,`is_file`,`is_history`,`is_delete`,`is_hidden`) values ('/library/basic','',0,0,3,1,'/library/basic/Package',NULL,0,0,0,0,0),('/library/basic/contents','',0,0,4,1,'/library/basic/Package',NULL,0,0,0,0,0),('/library/basic/contents/Comment','',0,0,5,1,NULL,NULL,1,0,0,0,0),('/library/basic/contents/Comment/text','',0,0,6,1,NULL,NULL,0,0,0,0,0),('/library/basic/contents/Feedback','',0,0,5,2,NULL,NULL,0,0,0,0,0),('/library/basic/contents/Feedback/email_from','',0,0,6,1,'/library/basic/simple/Email',NULL,0,0,0,0,0),('/library/basic/contents/Feedback/email_to','',0,0,6,2,'/library/basic/simple/Email',NULL,0,0,0,0,0),('/library/basic/contents/Feedback/message','',0,0,6,3,NULL,NULL,0,0,0,0,0),('/library/basic/contents/Head','',0,0,5,3,NULL,NULL,0,0,0,0,0),('/library/basic/contents/Image','',0,0,5,4,NULL,NULL,1,0,0,0,0),('/library/basic/contents/Keyword','',0,0,5,5,NULL,NULL,1,0,0,0,0),('/library/basic/contents/lists','',0,0,5,6,'/library/basic/Package',NULL,0,0,0,0,0),('/library/basic/contents/lists/Item','',0,0,6,2,NULL,NULL,0,0,0,0,0),('/library/basic/contents/lists/List','',0,0,6,1,NULL,NULL,0,0,0,0,0),('/library/basic/contents/Page','',0,0,5,11,NULL,NULL,1,0,0,0,0),('/library/basic/contents/Page/comments','',0,0,6,1,NULL,NULL,0,0,0,0,0),('/library/basic/contents/Page/description','',0,0,6,2,NULL,NULL,0,0,0,0,0),('/library/basic/contents/Page/keywords','',0,0,6,3,NULL,NULL,0,0,0,0,0),('/library/basic/contents/Page/text','',0,0,6,4,'/library/basic/contents/RichText',NULL,0,0,0,0,0),('/library/basic/contents/Page/title','',0,0,6,0,NULL,'Страница',0,0,0,0,0),('/library/basic/contents/Paragraph','',0,0,5,8,NULL,NULL,0,0,0,0,0),('/library/basic/contents/Part','',0,0,5,10,NULL,NULL,0,0,0,0,0),('/library/basic/contents/Part/title','',0,0,6,0,NULL,'Раздел',0,0,0,0,0),('/library/basic/contents/RichText','',0,0,5,9,NULL,NULL,0,0,0,0,0),('/library/basic/contents/tables','',0,0,5,7,'/library/basic/Package',NULL,0,0,0,0,0),('/library/basic/contents/tables/Cell','',0,0,6,3,NULL,NULL,0,0,0,0,0),('/library/basic/contents/tables/Row','',0,0,6,2,NULL,NULL,0,0,0,0,0),('/library/basic/contents/tables/Table','',0,0,6,1,NULL,NULL,0,0,0,0,0),('/library/basic/interfaces','',0,0,4,2,'/library/basic/Package',NULL,0,0,0,0,0),('/library/basic/interfaces/Css','',0,0,5,1,NULL,NULL,1,0,0,0,0),('/library/basic/interfaces/Group','',0,0,5,2,NULL,NULL,1,0,0,0,0),('/library/basic/interfaces/javascripts','',0,0,5,3,'/library/basic/Package',NULL,0,0,0,0,0),('/library/basic/interfaces/javascripts/JavaScript','',0,0,6,1,NULL,NULL,1,0,0,0,0),('/library/basic/interfaces/javascripts/JavaScript/depends','',0,0,7,1,'/library/basic/interfaces/Group',NULL,0,0,0,0,0),('/library/basic/interfaces/javascripts/jQuery','',0,0,6,2,'/library/basic/interfaces/javascripts/JavaScript','jQuery.js',0,1,0,0,0),('/library/basic/interfaces/javascripts/jQueryScript','',0,0,6,3,'/library/basic/interfaces/javascripts/JavaScript',NULL,1,0,0,0,0),('/library/basic/interfaces/javascripts/jQueryScript/depends','',0,0,7,1,'/library/basic/interfaces/javascripts/JavaScript/depends',NULL,0,0,1,1,1),('/library/basic/interfaces/javascripts/jQueryScript/depends/jquery','',0,0,8,1,'/library/basic/interfaces/javascripts/jQuery',NULL,0,0,0,0,0),('/library/basic/interfaces/widgets','',0,0,5,4,'/library/basic/Package',NULL,0,0,0,0,0),('/library/basic/interfaces/widgets/Html','',0,0,6,1,NULL,'Html.tpl',1,1,0,0,0),('/library/basic/interfaces/widgets/Html/body','',0,0,7,1,'/library/basic/interfaces/Group',NULL,0,0,0,0,0),('/library/basic/interfaces/widgets/Widget','',0,0,6,2,NULL,NULL,1,0,0,0,0),('/library/basic/members','',0,0,4,3,'/library/basic/Package',NULL,0,0,0,0,0),('/library/basic/members/Group','',0,0,5,2,NULL,NULL,1,0,0,0,0),('/library/basic/members/Group/title','',0,0,6,1,NULL,'Группа пользователей',0,0,0,0,0),('/library/basic/members/User','',0,0,5,1,NULL,NULL,1,0,0,0,0),('/library/basic/members/User/email','',0,0,6,3,'/library/basic/simple/Email',NULL,0,0,0,0,0),('/library/basic/members/User/name','',0,0,6,2,NULL,NULL,0,0,0,0,0),('/library/basic/members/User/title','',0,0,6,1,NULL,'Пользователь',0,0,0,0,0),('/library/basic/Package','',0,0,4,5,NULL,NULL,0,0,0,0,0),('/library/basic/simple','',0,0,4,4,'/library/basic/Package',NULL,0,0,0,0,0),('/library/basic/simple/Email','',0,0,5,1,NULL,NULL,1,0,0,0,0),('/library/basic/simple/Number','',0,0,5,2,NULL,NULL,1,0,0,0,0),('/library/layouts','',0,0,3,2,'/library/basic/Package',NULL,0,0,0,0,0),('/library/layouts/boolive','',0,0,4,1,'/library/basic/interfaces/widgets/Widget','boolive.tpl',0,1,0,0,0),('/library/layouts/boolive/bottom','',0,0,5,1,'/library/basic/interfaces/Group',NULL,0,0,0,0,0),('/library/layouts/boolive/center','',0,0,5,1,'/library/basic/interfaces/Group',NULL,0,0,0,0,0),('/library/layouts/boolive/head','',0,0,5,1,'/library/basic/interfaces/Group',NULL,0,0,0,0,0),('/library/layouts/boolive/sidebar','',0,0,5,1,'/library/basic/interfaces/Group',NULL,0,0,0,0,0),('/library/layouts/boolive/style','',0,0,5,1,'/library/basic/interfaces/Css','style.css',0,1,0,0,0),('/library/layouts/boolive/top','',0,0,5,1,'/library/basic/interfaces/Group',NULL,0,0,0,0,0);

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
