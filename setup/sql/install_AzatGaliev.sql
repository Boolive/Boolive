-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Авг 26 2012 г., 18:26
-- Версия сервера: 5.5.24
-- Версия PHP: 5.3.10-1ubuntu3.2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `boolive-git`
--

-- --------------------------------------------------------

--
-- Структура таблицы `contents`
--

CREATE TABLE IF NOT EXISTS `contents` (
  `uri` varchar(255) NOT NULL DEFAULT '' COMMENT 'Унифицированный идентификатор (путь на объект)',
  `lang` char(3) NOT NULL DEFAULT '' COMMENT 'Код языка по ISO 639-3',
  `owner` int(11) NOT NULL DEFAULT '0' COMMENT 'Владелец',
  `date` int(11) NOT NULL COMMENT 'Дата создания объекта',
  `level` int(11) NOT NULL DEFAULT '1' COMMENT 'Уровень вложенности относительно корня',
  `order` int(11) NOT NULL DEFAULT '1' COMMENT 'Порядковый номер',
  `proto` varchar(255) DEFAULT NULL COMMENT 'uri прототипа',
  `value` varchar(255) DEFAULT NULL COMMENT 'Значение',
  `is_logic` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, есть ли класс у объекта',
  `is_file` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Является ли объект файлом',
  `is_history` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, является ли запись историей',
  `is_delete` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, удален объект или нет',
  `is_hidden` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, скрытый объект или нет',
  PRIMARY KEY (`uri`,`lang`,`owner`,`date`),
  KEY `orders` (`order`),
  KEY `sate` (`is_history`,`is_delete`,`is_hidden`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `contents`
--

REPLACE INTO `contents` (`uri`, `lang`, `owner`, `date`, `level`, `order`, `proto`, `value`, `is_logic`, `is_file`, `is_history`, `is_delete`, `is_hidden`) VALUES
('/Contents/contacts', '', 0, 0, 2, 2, '/Library/content_samples/Page', NULL, 0, 0, 0, 0, 0),
('/Contents/contacts/text', '', 0, 0, 3, 2, '/Library/content_samples/Page/text', NULL, 0, 0, 0, 0, 0),
('/Contents/contacts/text/feedback', '', 0, 0, 4, 3, '/Library/content_samples/Feedback', NULL, 0, 0, 0, 0, 0),
('/Contents/contacts/text/feedback/email_from', '', 0, 0, 5, 1, '/Library/content_samples/Feedback/email_from', '', 0, 0, 0, 0, 0),
('/Contents/contacts/text/feedback/email_to', '', 0, 0, 5, 2, '/Library/content_samples/Feedback/email_to', 'info@boolive.ru', 0, 0, 0, 0, 0),
('/Contents/contacts/text/feedback/message', '', 0, 0, 5, 3, '/Library/content_samples/Feedback/message', '', 0, 0, 0, 0, 0),
('/Contents/contacts/text/head1', '', 0, 0, 4, 0, '/Library/content_samples/Head', 'Наш адрес', 0, 0, 0, 0, 0),
('/Contents/contacts/text/p1', '', 0, 0, 4, 1, '/Library/content_samples/Paragraph', 'г. Екатеринбург, ул Ленина, дом 1, офис 999', 0, 0, 0, 0, 0),
('/Contents/contacts/text/p2', '', 0, 0, 4, 2, '/Library/content_samples/Paragraph', 'Работаем груглосуточно', 0, 0, 0, 0, 0),
('/Contents/contacts/title', '', 0, 0, 3, 1, '/Library/content_samples/Page/title', 'Контакты', 0, 0, 0, 0, 0),
('/Contents/main', '', 0, 0, 2, 1, '/Library/content_samples/Page', NULL, 0, 0, 0, 0, 0),
('/Contents/main/author', '', 0, 0, 3, 4, '/Members/registered/admins/vova', NULL, 0, 0, 0, 0, 0),
('/Contents/main/comments', '', 0, 0, 3, 6, '/Library/content_samples/Page/comments', NULL, 0, 0, 0, 0, 0),
('/Contents/main/comments/comment1', '', 0, 0, 4, 1, '/Library/content_samples/Comment', NULL, 0, 0, 0, 0, 0),
('/Contents/main/comments/comment1/author', '', 0, 0, 5, 1, '/Members/registered/admins/vova', NULL, 0, 0, 0, 0, 0),
('/Contents/main/comments/comment1/comment11', '', 0, 0, 5, 3, '/Library/content_samples/Comment', NULL, 0, 0, 0, 0, 0),
('/Contents/main/comments/comment1/comment11/author', '', 0, 0, 6, 1, '/Members/registered/admins/vova', NULL, 0, 0, 0, 0, 0),
('/Contents/main/comments/comment1/comment11/text', '', 0, 0, 6, 2, '/Library/content_samples/Comment/text', 'Комментарий на первый комментарий', 0, 0, 0, 0, 0),
('/Contents/main/comments/comment1/text', '', 0, 0, 5, 2, '/Library/content_samples/Comment/text', 'Текст первого коммента', 0, 0, 0, 0, 0),
('/Contents/main/comments/comment2', '', 0, 0, 4, 2, '/Library/content_samples/Comment', NULL, 0, 0, 0, 0, 0),
('/Contents/main/comments/comment2/author', '', 0, 0, 5, 1, '/Members/registered/admins/vova', NULL, 0, 0, 0, 0, 0),
('/Contents/main/comments/comment2/text', '', 0, 0, 5, 2, '/Library/content_samples/Comment/text', 'Текст второго комментария к главной странице сайта', 0, 0, 0, 0, 0),
('/Contents/main/description', '', 0, 0, 3, 3, '/Library/content_samples/Page/description', 'Главная страница первого простейшего сайта', 0, 0, 0, 0, 0),
('/Contents/main/keywords', '', 0, 0, 3, 5, '/Library/content_samples/Page/keywords', NULL, 0, 0, 0, 0, 0),
('/Contents/main/keywords/cms', '', 0, 0, 4, 1, '/Keywords/cms', NULL, 0, 0, 0, 0, 0),
('/Contents/main/keywords/php', '', 0, 0, 4, 2, '/Keywords/php', NULL, 0, 0, 0, 0, 0),
('/Contents/main/sub_page', '', 0, 0, 3, 7, '/Library/content_samples/Page', NULL, 0, 0, 0, 0, 0),
('/Contents/main/sub_page/author', '', 0, 0, 4, 3, '/Members/registered/admins/vova', NULL, 0, 0, 0, 0, 0),
('/Contents/main/sub_page/description', '', 0, 0, 4, 2, '/Library/content_samples/Page/description', 'Подчиненная страница. Это её короткое описание для SEO', 0, 0, 0, 0, 0),
('/Contents/main/sub_page/text', '', 0, 0, 4, 1, '/Library/content_samples/Page/text', NULL, 0, 0, 0, 0, 0),
('/Contents/main/sub_page/text/head1', '', 0, 0, 5, 0, '/Library/content_samples/Head', 'Заголовок подчиненной страницы', 0, 0, 0, 0, 0),
('/Contents/main/sub_page/text/head2', '', 0, 0, 5, 2, '/Library/content_samples/Head', 'Подзаголовок подчиненной страницы', 0, 0, 0, 0, 0),
('/Contents/main/sub_page/text/img1', '', 0, 0, 5, 4, '/Library/content_samples/Image', 'img1.jpg', 0, 1, 0, 0, 0),
('/Contents/main/sub_page/text/p1', '', 0, 0, 5, 1, '/Library/content_samples/Paragraph', 'Первый абзац подчиенной страницы... текст... текст...', 0, 0, 0, 0, 0),
('/Contents/main/sub_page/text/p2', '', 0, 0, 5, 3, '/Library/content_samples/Paragraph', 'Второй абзац подчиенной страницы... текст... текст...', 0, 0, 0, 0, 0),
('/Contents/main/sub_page/title', '', 0, 0, 4, 0, '/Library/content_samples/Page/title', 'Подстраница', 0, 0, 0, 0, 0),
('/Contents/main/text', '', 0, 0, 3, 2, '/Library/content_samples/Page/text', NULL, 0, 0, 0, 0, 0),
('/Contents/main/text/head1', '', 0, 0, 4, 1, '/Library/content_samples/Head', 'Заголовок главной страницы', 0, 0, 0, 0, 0),
('/Contents/main/text/img1', '', 0, 0, 4, 3, '/Library/content_samples/Image', 'img1.jpg', 0, 1, 0, 0, 0),
('/Contents/main/text/p1', '', 0, 0, 4, 2, '/Library/content_samples/Paragraph', 'Добро пожаловать на тестовый сайт. Сайт работает на новой системе Boolive 2', 0, 0, 0, 0, 0),
('/Contents/main/text/p2', '', 0, 0, 4, 4, '/Library/content_samples/Paragraph', 'Hello World :)', 0, 0, 0, 0, 0),
('/Contents/main/title', '', 0, 0, 3, 1, '/Library/content_samples/Page/title', 'Главная', 0, 0, 0, 0, 0),
('/Contents/news', '', 0, 0, 2, 3, '/Library/content_samples/Part', NULL, 0, 0, 0, 0, 0),
('/Contents/news/news1', '', 0, 0, 3, 2, '/Library/content_samples/Page', NULL, 0, 0, 0, 0, 0),
('/Contents/news/news1/author', '', 0, 0, 4, 3, '/Members/registered/admins/vova', NULL, 0, 0, 0, 0, 0),
('/Contents/news/news1/text', '', 0, 0, 4, 2, '/Library/content_samples/Page/text', NULL, 0, 0, 0, 0, 0),
('/Contents/news/news1/text/p1', '', 0, 0, 5, 1, '/Library/content_samples/Paragraph', 'Текст новости в виде одного абзаца', 0, 0, 0, 0, 0),
('/Contents/news/news1/title', '', 0, 0, 4, 1, '/Library/content_samples/Page/title', 'Первая новость', 0, 0, 0, 0, 0),
('/Contents/news/news2', '', 0, 0, 3, 3, '/Library/content_samples/Page', NULL, 0, 0, 0, 0, 0),
('/Contents/news/news2/author', '', 0, 0, 4, 3, '/Members/registered/admins/vova', NULL, 0, 0, 0, 0, 0),
('/Contents/news/news2/text', '', 0, 0, 4, 2, '/Library/content_samples/Page/text', NULL, 0, 0, 0, 0, 0),
('/Contents/news/news2/text/p1', '', 0, 0, 5, 1, '/Library/content_samples/Paragraph', 'Ноовсть создаётся как страница, то есть новость и есть страницой, просто она помещена в раздел Ленты новостей', 0, 0, 0, 0, 0),
('/Contents/news/news2/title', '', 0, 0, 4, 1, '/Library/content_samples/Page/title', 'Вторая новость', 0, 0, 0, 0, 0),
('/Contents/news/news3', '', 0, 0, 3, 4, '/Library/content_samples/Page', NULL, 0, 0, 0, 0, 0),
('/Contents/news/news3/author', '', 0, 0, 4, 3, '/Members/registered/admins/vova', NULL, 0, 0, 0, 0, 0),
('/Contents/news/news3/text', '', 0, 0, 4, 2, '/Library/content_samples/Page/text', NULL, 0, 0, 0, 0, 0),
('/Contents/news/news3/text/p1', '', 0, 0, 5, 1, '/Library/content_samples/Paragraph', 'Текст новости в виде одного абзаца', 0, 0, 0, 0, 0),
('/Contents/news/news3/title', '', 0, 0, 4, 1, '/Library/content_samples/Page/title', 'Третья новость', 0, 0, 0, 0, 0),
('/Contents/news/news4', '', 0, 0, 3, 5, '/Library/content_samples/Page', NULL, 0, 0, 0, 0, 0),
('/Contents/news/news4/author', '', 0, 0, 4, 3, '/Members/registered/admins/vova', NULL, 0, 0, 0, 0, 0),
('/Contents/news/news4/text', '', 0, 0, 4, 2, '/Library/content_samples/Page/text', NULL, 0, 0, 0, 0, 0),
('/Contents/news/news4/text/p1', '', 0, 0, 5, 1, '/Library/content_samples/Paragraph', 'Текст новости в виде одного абзаца', 0, 0, 0, 0, 0),
('/Contents/news/news4/title', '', 0, 0, 4, 1, '/Library/content_samples/Page/title', 'Четвертая новость', 0, 0, 0, 0, 0),
('/Contents/news/title', '', 0, 0, 3, 1, '/Library/content_samples/Part/title', 'Лента новостей', 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `interfaces`
--

CREATE TABLE IF NOT EXISTS `interfaces` (
  `uri` varchar(255) NOT NULL DEFAULT '' COMMENT 'Унифицированный идентификатор (путь на объект)',
  `lang` char(3) NOT NULL DEFAULT '' COMMENT 'Код языка по ISO 639-3',
  `owner` int(11) NOT NULL DEFAULT '0' COMMENT 'Владелец',
  `date` int(11) NOT NULL COMMENT 'Дата создания объекта',
  `level` int(11) NOT NULL DEFAULT '1' COMMENT 'Уровень вложенности относительно корня',
  `order` int(11) NOT NULL DEFAULT '1' COMMENT 'Порядковый номер',
  `proto` varchar(255) DEFAULT NULL COMMENT 'uri прототипа',
  `value` varchar(255) DEFAULT NULL COMMENT 'Значение',
  `is_logic` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, есть ли класс у объекта',
  `is_file` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Является ли объект файлом',
  `is_history` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, является ли запись историей',
  `is_delete` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, удален объект или нет',
  `is_hidden` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, скрытый объект или нет',
  PRIMARY KEY (`uri`,`lang`,`owner`,`date`),
  KEY `orders` (`order`),
  KEY `sate` (`is_history`,`is_delete`,`is_hidden`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `interfaces`
--

REPLACE INTO `interfaces` (`uri`, `lang`, `owner`, `date`, `level`, `order`, `proto`, `value`, `is_logic`, `is_file`, `is_history`, `is_delete`, `is_hidden`) VALUES
('/Interfaces/html', '', 0, 0, 2, 1, '/Library/basic/widgets/Html', NULL, 0, 0, 0, 0, 0),
('/Interfaces/html/body/boolive', '', 0, 0, 4, 2, '/Library/layouts/boolive', NULL, 0, 0, 0, 0, 0),
('/Interfaces/html/body/boolive/center/hello5', '', 0, 0, 4, 6, '/Interfaces/html/body/hello', NULL, 0, 0, 0, 1, 0),
('/Interfaces/html/body/hello', '', 0, 0, 4, 2, '/Library/basic/widgets/Widget', 'hello.tpl', 0, 1, 0, 1, 0),
('/Interfaces/html/body/hello/jquery.hello', '', 0, 0, 5, 2, '/Library/basic/javascripts/jQueryScript', 'jquery.hello.js', 0, 1, 0, 1, 0),
('/Interfaces/html/body/hello/style', '', 0, 0, 5, 1, '/Library/basic/Css', 'style.css', 0, 1, 0, 1, 0),
('/Interfaces/html/body/hello2', '', 0, 0, 4, 3, '/Interfaces/html/body/hello', NULL, 0, 0, 0, 1, 0),
('/Interfaces/html/body/hello3', '', 0, 0, 4, 4, '/Interfaces/html/body/hello', NULL, 0, 0, 0, 1, 0),
('/Interfaces/html/body/hello4', '', 0, 0, 4, 5, '/Interfaces/html/body/hello', NULL, 0, 0, 0, 1, 0),
('/Interfaces/form_handler', '', 0, 0, 2, 0, '/Library/basic/FormHandler', NULL, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `keywords`
--

CREATE TABLE IF NOT EXISTS `keywords` (
  `uri` varchar(255) NOT NULL DEFAULT '' COMMENT 'Унифицированный идентификатор (путь на объект)',
  `lang` char(3) NOT NULL DEFAULT '' COMMENT 'Код языка по ISO 639-3',
  `owner` int(11) NOT NULL DEFAULT '0' COMMENT 'Владелец',
  `date` int(11) NOT NULL COMMENT 'Дата создания объекта',
  `level` int(11) NOT NULL DEFAULT '1' COMMENT 'Уровень вложенности относительно корня',
  `order` int(11) NOT NULL DEFAULT '1' COMMENT 'Порядковый номер',
  `proto` varchar(255) DEFAULT NULL COMMENT 'uri прототипа',
  `value` varchar(255) DEFAULT NULL COMMENT 'Значение',
  `is_logic` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, есть ли класс у объекта',
  `is_file` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Является ли объект файлом',
  `is_history` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, является ли запись историей',
  `is_delete` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, удален объект или нет',
  `is_hidden` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, скрытый объект или нет',
  PRIMARY KEY (`uri`,`lang`,`owner`,`date`),
  KEY `orders` (`order`),
  KEY `sate` (`is_history`,`is_delete`,`is_hidden`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `keywords`
--

REPLACE INTO `keywords` (`uri`, `lang`, `owner`, `date`, `level`, `order`, `proto`, `value`, `is_logic`, `is_file`, `is_history`, `is_delete`, `is_hidden`) VALUES
('/Keywords/cms', '', 0, 0, 2, 1, '/Library/content_samples/Keyword', '1', 0, 0, 0, 0, 0),
('/Keywords/framework', '', 0, 0, 2, 1, '/Library/content_samples/Keyword', '0', 0, 0, 0, 0, 0),
('/Keywords/php', '', 0, 0, 2, 1, '/Library/content_samples/Keyword', '1', 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `library`
--

CREATE TABLE IF NOT EXISTS `library` (
  `uri` varchar(255) NOT NULL DEFAULT '' COMMENT 'Унифицированный идентификатор (путь на объект)',
  `lang` char(3) NOT NULL DEFAULT '' COMMENT 'Код языка по ISO 639-3',
  `owner` int(11) NOT NULL DEFAULT '0' COMMENT 'Владелец',
  `date` int(11) NOT NULL COMMENT 'Дата создания объекта',
  `level` int(11) NOT NULL DEFAULT '1' COMMENT 'Уровень вложенности относительно корня',
  `order` int(11) NOT NULL DEFAULT '1' COMMENT 'Порядковый номер',
  `proto` varchar(255) DEFAULT NULL COMMENT 'uri прототипа',
  `value` varchar(255) DEFAULT NULL COMMENT 'Значение',
  `is_logic` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, есть ли класс у объекта',
  `is_file` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Является ли объект файлом',
  `is_history` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, является ли запись историей',
  `is_delete` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, удален объект или нет',
  `is_hidden` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, скрытый объект или нет',
  PRIMARY KEY (`uri`,`lang`,`owner`,`date`),
  KEY `orders` (`order`),
  KEY `sate` (`is_history`,`is_delete`,`is_hidden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `library`
--

REPLACE INTO `library` (`uri`, `lang`, `owner`, `date`, `level`, `order`, `proto`, `value`, `is_logic`, `is_file`, `is_history`, `is_delete`, `is_hidden`) VALUES
('/Library/basic', '', 0, 0, 2, 1, '/Library/basic/Package', NULL, 0, 0, 0, 0, 0),
('/Library/basic/Css', '', 0, 0, 3, 1, NULL, NULL, 1, 0, 0, 0, 0),
('/Library/basic/FormHandler', '', 0, 0, 3, 1, NULL, NULL, 1, 0, 0, 0, 0),
('/Library/basic/FormHandler/title', '', 0, 0, 4, 1, NULL, 'Обработчик форм', 0, 0, 0, 0, 0),
('/Library/basic/Group', '', 0, 0, 3, 2, NULL, NULL, 1, 0, 0, 0, 0),
('/Library/basic/interfaces', '', 0, 0, 3, 2, '/Library/basic/Package', NULL, 0, 0, 0, 0, 0),
('/Library/basic/javascripts', '', 0, 0, 3, 3, '/Library/basic/Package', NULL, 0, 0, 0, 0, 0),
('/Library/basic/javascripts/JavaScript', '', 0, 0, 4, 1, NULL, NULL, 1, 0, 0, 0, 0),
('/Library/basic/javascripts/JavaScript/depends', '', 0, 0, 5, 1, '/Library/basic/Group', NULL, 0, 0, 0, 0, 0),
('/Library/basic/javascripts/jQuery', '', 0, 0, 4, 2, '/Library/basic/javascripts/JavaScript', 'jQuery.js', 0, 1, 0, 0, 0),
('/Library/basic/javascripts/jQueryScript', '', 0, 0, 4, 3, '/Library/basic/javascripts/JavaScript', NULL, 1, 0, 0, 0, 0),
('/Library/basic/javascripts/jQueryScript/depends', '', 0, 0, 5, 1, '/Library/basic/javascripts/JavaScript/depends', NULL, 0, 0, 1, 1, 1),
('/Library/basic/javascripts/jQueryScript/depends/jquery', '', 0, 0, 6, 1, '/Library/basic/javascripts/jQuery', NULL, 0, 0, 0, 0, 0),
('/Library/basic/members', '', 0, 0, 3, 3, '/Library/basic/Package', NULL, 0, 0, 0, 0, 0),
('/Library/basic/members/User', '', 0, 0, 4, 1, NULL, NULL, 1, 0, 0, 0, 0),
('/Library/basic/members/User/email', '', 0, 0, 5, 3, '/Library/basic/simple/Email', NULL, 0, 0, 0, 0, 0),
('/Library/basic/members/User/name', '', 0, 0, 5, 2, NULL, NULL, 0, 0, 0, 0, 0),
('/Library/basic/members/User/title', '', 0, 0, 5, 1, NULL, 'Пользователь', 0, 0, 0, 0, 0),
('/Library/basic/members/UserGroup', '', 0, 0, 4, 2, NULL, NULL, 1, 0, 0, 0, 0),
('/Library/basic/members/UserGroup/title', '', 0, 0, 5, 1, NULL, 'Группа пользователей', 0, 0, 0, 0, 0),
('/Library/basic/Package', '', 0, 0, 3, 5, NULL, NULL, 0, 0, 0, 0, 0),
('/Library/basic/simple', '', 0, 0, 3, 4, '/Library/basic/Package', NULL, 0, 0, 0, 0, 0),
('/Library/basic/simple/Email', '', 0, 0, 4, 1, NULL, NULL, 1, 0, 0, 0, 0),
('/Library/basic/simple/Number', '', 0, 0, 4, 2, NULL, NULL, 1, 0, 0, 0, 0),
('/Library/basic/widgets', '', 0, 0, 3, 4, '/Library/basic/Package', NULL, 0, 0, 0, 0, 0),
('/Library/basic/widgets/Focuser', '', 0, 0, 4, 5, '/Library/basic/widgets/Widget', NULL, 1, 0, 0, 0, 0),
('/Library/basic/widgets/Focuser/description', '', 0, 0, 5, 2, '/Library/basic/widgets/Widget/description', 'По URL запроса определяет объект и номер страницы для последующего оперирования ими подчиненными виджетами', 0, 0, 0, 0, 0),
('/Library/basic/widgets/Focuser/title', '', 0, 0, 5, 1, '/Library/basic/widgets/Widget/title', 'Фокусировщик', 0, 0, 0, 0, 0),
('/Library/basic/widgets/Html', '', 0, 0, 4, 1, NULL, 'Html.tpl', 1, 1, 0, 0, 0),
('/Library/basic/widgets/Html/body', '', 0, 0, 5, 1, '/Library/basic/Group', NULL, 0, 0, 0, 0, 0),
('/Library/basic/widgets/Logo', '', 0, 0, 4, 1, '/Library/basic/widgets/Widget', 'Logo.tpl', 1, 1, 0, 0, 0),
('/Library/basic/widgets/Logo/image', '', 0, 0, 5, 1, '/Library/content_samples/Image', 'logo.png', 0, 1, 0, 0, 0),
('/Library/basic/widgets/Menu', '', 0, 0, 4, 7, '/Library/basic/widgets/Widget', 'Menu.tpl', 1, 1, 0, 0, 0),
('/Library/basic/widgets/Menu/items', '', 0, 0, 5, 2, '/Contents', NULL, 0, 0, 0, 0, 0),
('/Library/basic/widgets/Menu/style', '', 0, 0, 5, 1, '/Library/basic/Css', 'style.css', 0, 1, 0, 0, 0),
('/Library/basic/widgets/Menu/title', '', 0, 0, 5, 1, '/Library/basic/widgets/Widget/title', 'Меню', 0, 0, 0, 0, 0),
('/Library/basic/widgets/Menu/views', '', 0, 0, 5, 3, '/Library/basic/widgets/ViewObjectsList', 'views.tpl', 1, 1, 0, 0, 0),
('/Library/basic/widgets/Menu/views/cond_page', '', 0, 0, 6, 1, '/Library/basic/widgets/Option', '/Library/content_samples/Page', 0, 0, 0, 0, 0),
('/Library/basic/widgets/Menu/views/cond_page/ItemPage', '', 0, 0, 7, 1, '/Library/basic/widgets/Menu/views', 'ItemPage.tpl', 1, 1, 0, 0, 0),
('/Library/basic/widgets/Menu/views/cond_part', '', 0, 0, 6, 2, '/Library/basic/widgets/Option', '/Library/content_samples/Part', 0, 0, 0, 0, 0),
('/Library/basic/widgets/Menu/views/cond_part/ItemPart', '', 0, 0, 7, 1, '/Library/basic/widgets/Menu/views/cond_page/ItemPage', NULL, 0, 0, 0, 0, 0),
('/Library/basic/widgets/Option', '', 0, 0, 4, 6, NULL, NULL, 1, 0, 0, 0, 0),
('/Library/basic/widgets/Option/description', '', 0, 0, 5, 2, NULL, 'Используется для организации вариантов отображений родительского виджета', 0, 0, 0, 1, 0),
('/Library/basic/widgets/Option/title', '', 0, 0, 5, 1, NULL, 'Вариант отображения', 0, 0, 0, 1, 0),
('/Library/basic/widgets/PageNavigation', '', 0, 0, 4, 7, '/Library/basic/widgets/Widget', 'PageNavigation.tpl', 1, 1, 0, 0, 0),
('/Library/basic/widgets/PageNavigation/style', '', 0, 0, 5, 1, '/Library/basic/Css', 'style.css', 0, 1, 0, 0, 0),
('/Library/basic/widgets/PageNavigation/title', '', 0, 0, 5, 1, '/Library/basic/widgets/Widget/title', 'Меню постраничной навигации', 0, 0, 0, 0, 0),
('/Library/basic/widgets/ViewObject', '', 0, 0, 4, 3, '/Library/basic/widgets/Widget', 'ViewObject.tpl', 1, 1, 0, 0, 0),
('/Library/basic/widgets/ViewObject/description', '', 0, 0, 5, 2, '/Library/basic/widgets/Widget/description', 'Виджет для отображения любого объекта.', 0, 0, 0, 0, 0),
('/Library/basic/widgets/ViewObject/title', '', 0, 0, 5, 1, '/Library/basic/widgets/Widget/title', 'Универсальный виджет', 0, 0, 0, 0, 0),
('/Library/basic/widgets/ViewObjectsList', '', 0, 0, 4, 4, '/Library/basic/widgets/Widget', 'ViewObjectsList.tpl', 1, 1, 0, 0, 0),
('/Library/basic/widgets/ViewObjectsList/description', '', 0, 0, 5, 2, '/Library/basic/widgets/Widget/description', 'Виджет для отображения списка любых объектов', 0, 0, 0, 0, 0),
('/Library/basic/widgets/ViewObjectsList/title', '', 0, 0, 5, 1, '/Library/basic/widgets/Widget/title', 'Универсальный виджет списка', 0, 0, 0, 0, 0),
('/Library/basic/widgets/Widget', '', 0, 0, 4, 2, NULL, NULL, 1, 0, 0, 0, 0),
('/Library/basic/widgets/Widget/res', '', 0, 0, 6, 1, '/Library/basic/Group', NULL, 0, 0, 0, 0, 0),
('/Library/content_samples', '', 0, 0, 2, 1, '/Library/basic/Package', NULL, 0, 0, 0, 0, 0),
('/Library/content_samples/Comment', '', 0, 0, 3, 1, NULL, NULL, 1, 0, 0, 0, 0),
('/Library/content_samples/Comment/text', '', 0, 0, 4, 1, NULL, NULL, 0, 0, 0, 0, 0),
('/Library/content_samples/Feedback', '', 0, 0, 3, 2, NULL, NULL, 0, 0, 0, 0, 0),
('/Library/content_samples/Feedback/email_from', '', 0, 0, 4, 1, '/Library/basic/simple/Email', NULL, 0, 0, 0, 0, 0),
('/Library/content_samples/Feedback/email_from/title', '', 0, 0, 5, 1, NULL, 'Email адрес', 0, 0, 0, 0, 0),
('/Library/content_samples/Feedback/email_to', '', 0, 0, 4, 2, '/Library/basic/simple/Email', NULL, 0, 0, 0, 0, 0),
('/Library/content_samples/Feedback/message', '', 0, 0, 4, 3, NULL, NULL, 0, 0, 0, 0, 0),
('/Library/content_samples/Feedback/message/title', '', 0, 0, 5, 1, NULL, 'Сообщение', 0, 0, 0, 0, 0),
('/Library/content_samples/Head', '', 0, 0, 3, 3, NULL, NULL, 0, 0, 0, 0, 0),
('/Library/content_samples/Image', '', 0, 0, 3, 4, NULL, NULL, 1, 0, 0, 0, 0),
('/Library/content_samples/Keyword', '', 0, 0, 3, 5, NULL, NULL, 1, 0, 0, 0, 0),
('/Library/content_samples/lists', '', 0, 0, 3, 6, '/Library/basic/Package', NULL, 0, 0, 0, 0, 0),
('/Library/content_samples/lists/Item', '', 0, 0, 4, 2, NULL, NULL, 0, 0, 0, 0, 0),
('/Library/content_samples/lists/List', '', 0, 0, 4, 1, NULL, NULL, 0, 0, 0, 0, 0),
('/Library/content_samples/Page', '', 0, 0, 3, 11, NULL, NULL, 1, 0, 0, 0, 0),
('/Library/content_samples/Page/comments', '', 0, 0, 4, 4, NULL, NULL, 0, 0, 0, 0, 0),
('/Library/content_samples/Page/description', '', 0, 0, 4, 2, NULL, NULL, 0, 0, 0, 0, 0),
('/Library/content_samples/Page/keywords', '', 0, 0, 4, 3, NULL, NULL, 0, 0, 0, 0, 0),
('/Library/content_samples/Page/text', '', 0, 0, 4, 1, '/Library/content_samples/RichText', NULL, 0, 0, 0, 0, 0),
('/Library/content_samples/Page/title', '', 0, 0, 4, 0, NULL, 'Страница', 0, 0, 0, 0, 0),
('/Library/content_samples/Paragraph', '', 0, 0, 3, 8, NULL, NULL, 0, 0, 0, 0, 0),
('/Library/content_samples/Part', '', 0, 0, 3, 10, NULL, NULL, 0, 0, 0, 0, 0),
('/Library/content_samples/Part/title', '', 0, 0, 4, 1, NULL, 'Раздел', 0, 0, 0, 0, 0),
('/Library/content_samples/RichText', '', 0, 0, 3, 9, NULL, NULL, 0, 0, 0, 0, 0),
('/Library/content_samples/RichText/title', '', 0, 0, 4, 1, NULL, 'Форматированный текст', 0, 0, 0, 0, 0),
('/Library/content_samples/tables', '', 0, 0, 3, 7, '/Library/basic/Package', NULL, 0, 0, 0, 0, 0),
('/Library/content_samples/tables/Cell', '', 0, 0, 4, 3, NULL, NULL, 0, 0, 0, 0, 0),
('/Library/content_samples/tables/Row', '', 0, 0, 4, 2, NULL, NULL, 0, 0, 0, 0, 0),
('/Library/content_samples/tables/Table', '', 0, 0, 4, 1, NULL, NULL, 0, 0, 0, 0, 0),
('/Library/content_widgets', '', 0, 0, 2, 1, '/Library/basic/Package', NULL, 0, 0, 0, 0, 0),
('/Library/content_widgets/Comments', '', 0, 0, 3, 1, NULL, NULL, 1, 0, 0, 0, 0),
('/Library/content_widgets/Content', '', 0, 0, 3, 1, '/Library/basic/widgets/ViewObject', NULL, 0, 0, 0, 0, 0),
('/Library/content_widgets/Content/option_page', '', 0, 0, 4, 1, '/Library/basic/widgets/Option', '/Library/content_samples/Page', 0, 0, 0, 0, 0),
('/Library/content_widgets/Content/option_page/page', '', 0, 0, 5, 1, '/Library/content_widgets/Page', NULL, 0, 0, 0, 0, 0),
('/Library/content_widgets/Content/option_part', '', 0, 0, 4, 2, '/Library/basic/widgets/Option', '/Library/content_samples/Part', 0, 0, 0, 0, 0),
('/Library/content_widgets/Content/option_part/part', '', 0, 0, 5, 1, '/Library/content_widgets/Part', NULL, 0, 0, 0, 0, 0),
('/Library/content_widgets/Feedback', '', 0, 0, 3, 1, '/Library/basic/widgets/ViewObjectsList', 'Feedback.tpl', 1, 1, 0, 0, 0),
('/Library/content_widgets/Feedback/option_mail', '', 0, 0, 4, 1, '/Library/basic/widgets/Option', '/Library/content_samples/Feedback/email_from', 0, 0, 0, 0, 0),
('/Library/content_widgets/Feedback/option_mail/EmailField', '', 0, 0, 5, 1, '/Library/basic/widgets/Widget', 'EmailField.tpl', 1, 1, 0, 0, 0),
('/Library/content_widgets/Feedback/option_message', '', 0, 0, 4, 2, '/Library/basic/widgets/Option', '/Library/content_samples/Feedback/message', 0, 0, 0, 0, 0),
('/Library/content_widgets/Feedback/option_message/MessageField', '', 0, 0, 5, 1, '/Library/basic/widgets/Widget', 'MessageField.tpl', 1, 1, 0, 0, 0),
('/Library/content_widgets/Feedback/style', '', 0, 0, 4, 5, '/Library/basic/Css', 'style.css', 0, 1, 0, 0, 0),
('/Library/content_widgets/Head', '', 0, 0, 3, 1, '/Library/basic/widgets/Widget', 'Head.tpl', 1, 1, 0, 0, 0),
('/Library/content_widgets/Image', '', 0, 0, 3, 1, '/Library/basic/widgets/Widget', 'Image.tpl', 1, 1, 0, 0, 0),
('/Library/content_widgets/Keywords', '', 0, 0, 3, 1, NULL, NULL, 1, 0, 0, 0, 0),
('/Library/content_widgets/Page', '', 0, 0, 3, 1, '/Library/basic/widgets/ViewObjectsList', 'Page.tpl', 1, 1, 0, 0, 0),
('/Library/content_widgets/Page/option_comments', '', 0, 0, 4, 1, '/Library/basic/widgets/Option', '/Library/content_samples/Page/comments', 0, 0, 0, 0, 0),
('/Library/content_widgets/Page/option_comments/Comments', '', 0, 0, 5, 1, '/Library/content_widgets/Comments', NULL, 0, 0, 0, 0, 0),
('/Library/content_widgets/Page/option_keywords', '', 0, 0, 4, 2, '/Library/basic/widgets/Option', '/Library/content_samples/Page/keywords', 0, 0, 0, 0, 0),
('/Library/content_widgets/Page/option_keywords/Keywords', '', 0, 0, 5, 1, '/Library/content_widgets/Keywords', NULL, 0, 0, 0, 0, 0),
('/Library/content_widgets/Page/option_text', '', 0, 0, 4, 3, '/Library/basic/widgets/Option', '/Library/content_samples/Page/text', 0, 0, 0, 0, 0),
('/Library/content_widgets/Page/option_text/Text', '', 0, 0, 5, 1, '/Library/content_widgets/RichText', NULL, 0, 0, 0, 0, 0),
('/Library/content_widgets/Page/option_title', '', 0, 0, 4, 4, '/Library/basic/widgets/Option', '/Library/content_samples/Page/title', 0, 0, 0, 0, 0),
('/Library/content_widgets/Page/option_title/Title', '', 0, 0, 5, 1, '/Library/content_widgets/Title', NULL, 0, 0, 0, 0, 0),
('/Library/content_widgets/Page/style', '', 0, 0, 4, 5, '/Library/basic/Css', 'style.css', 0, 1, 0, 0, 0),
('/Library/content_widgets/PagePreview', '', 0, 0, 3, 1, '/Library/basic/widgets/ViewObjectsList', 'PagePreview.tpl', 1, 1, 0, 0, 0),
('/Library/content_widgets/PagePreview/option_text', '', 0, 0, 4, 1, '/Library/basic/widgets/Option', '/Library/content_samples/Page/text', 0, 0, 0, 0, 0),
('/Library/content_widgets/PagePreview/option_text/Text', '', 0, 0, 5, 1, '/Library/content_widgets/RichText', NULL, 1, 0, 0, 0, 0),
('/Library/content_widgets/PagePreview/option_title', '', 0, 0, 4, 1, '/Library/basic/widgets/Option', '/Library/content_samples/Page/title', 0, 0, 0, 0, 0),
('/Library/content_widgets/PagePreview/option_title/Title', '', 0, 0, 5, 1, '/Library/content_widgets/Title', 'Title.tpl', 0, 1, 0, 0, 0),
('/Library/content_widgets/PagePreview/style', '', 0, 0, 4, 1, '/Library/basic/Css', 'style.css', 0, 1, 0, 0, 0),
('/Library/content_widgets/Paragraph', '', 0, 0, 3, 1, '/Library/basic/widgets/Widget', 'Paragraph.tpl', 1, 1, 0, 0, 0),
('/Library/content_widgets/Part', '', 0, 0, 3, 1, '/Library/basic/widgets/ViewObjectsList', 'Part.tpl', 1, 1, 0, 0, 0),
('/Library/content_widgets/Part/count_per_page', '', 0, 0, 4, 9, NULL, '4', 0, 0, 0, 0, 0),
('/Library/content_widgets/Part/option_page', '', 0, 0, 4, 1, '/Library/basic/widgets/Option', '/Library/content_samples/Page', 0, 0, 0, 0, 0),
('/Library/content_widgets/Part/option_page/PagePreview', '', 0, 0, 5, 1, '/Library/content_widgets/PagePreview', NULL, 0, 0, 0, 0, 0),
('/Library/content_widgets/Part/option_part', '', 0, 0, 4, 2, '/Library/basic/widgets/Option', '/Library/content_samples/Part', 0, 0, 0, 0, 0),
('/Library/content_widgets/Part/option_part/PartPreview', '', 0, 0, 5, 1, '/Library/content_widgets/PartPreview', NULL, 0, 0, 0, 0, 0),
('/Library/content_widgets/Part/option_title', '', 0, 0, 4, 3, '/Library/basic/widgets/Option', '/Library/content_samples/Part/title', 0, 0, 0, 0, 0),
('/Library/content_widgets/Part/option_title/Title', '', 0, 0, 5, 1, '/Library/content_widgets/Title', NULL, 0, 0, 0, 0, 0),
('/Library/content_widgets/Part/pagesnum', '', 0, 0, 4, 10, '/Library/basic/widgets/PageNavigation', NULL, 0, 0, 0, 0, 0),
('/Library/content_widgets/PartPreview', '', 0, 0, 3, 1, NULL, NULL, 1, 0, 0, 0, 0),
('/Library/content_widgets/RichText', '', 0, 0, 3, 1, '/Library/basic/widgets/ViewObjectsList', 'RichText.tpl', 1, 1, 0, 0, 0),
('/Library/content_widgets/RichText/option_feedback', '', 0, 0, 4, 1, '/Library/basic/widgets/Option', '/Library/content_samples/Feedback', 0, 0, 0, 0, 0),
('/Library/content_widgets/RichText/option_feedback/feedback', '', 0, 0, 5, 1, '/Library/content_widgets/Feedback', NULL, 0, 0, 0, 0, 0),
('/Library/content_widgets/RichText/option_h', '', 0, 0, 4, 1, '/Library/basic/widgets/Option', '/Library/content_samples/Head', 0, 0, 0, 0, 0),
('/Library/content_widgets/RichText/option_h/head', '', 0, 0, 5, 1, '/Library/content_widgets/Head', NULL, 0, 0, 0, 0, 0),
('/Library/content_widgets/RichText/option_img', '', 0, 0, 4, 1, '/Library/basic/widgets/Option', '/Library/content_samples/Image', 0, 0, 0, 0, 0),
('/Library/content_widgets/RichText/option_img/img', '', 0, 0, 5, 1, '/Library/content_widgets/Image', NULL, 0, 0, 0, 0, 0),
('/Library/content_widgets/RichText/option_p', '', 0, 0, 4, 1, '/Library/basic/widgets/Option', '/Library/content_samples/Paragraph', 0, 0, 0, 0, 0),
('/Library/content_widgets/RichText/option_p/paragraph', '', 0, 0, 5, 1, '/Library/content_widgets/Paragraph', NULL, 0, 0, 0, 0, 0),
('/Library/content_widgets/Title', '', 0, 0, 3, 1, NULL, 'Title.tpl', 1, 1, 0, 0, 0),
('/Library/layouts', '', 0, 0, 2, 2, '/Library/basic/Package', NULL, 0, 0, 0, 0, 0),
('/Library/layouts/boolive', '', 0, 0, 3, 1, '/Library/basic/widgets/Focuser', 'boolive.tpl', 0, 1, 0, 0, 0),
('/Library/layouts/boolive/bottom', '', 0, 0, 4, 1, '/Library/basic/Group', NULL, 0, 0, 0, 0, 0),
('/Library/layouts/boolive/center', '', 0, 0, 4, 1, '/Library/basic/Group', NULL, 0, 0, 0, 0, 0),
('/Library/layouts/boolive/center/Content', '', 0, 0, 5, 1, '/Library/content_widgets/Content', NULL, 0, 0, 0, 0, 0),
('/Library/layouts/boolive/head', '', 0, 0, 4, 1, '/Library/basic/Group', NULL, 0, 0, 0, 0, 0),
('/Library/layouts/boolive/head/logo', '', 0, 0, 5, 1, '/Library/basic/widgets/Logo', NULL, 0, 0, 0, 0, 0),
('/Library/layouts/boolive/sidebar', '', 0, 0, 4, 1, '/Library/basic/Group', NULL, 0, 0, 0, 0, 0),
('/Library/layouts/boolive/sidebar/menu', '', 0, 0, 5, 1, '/Library/basic/widgets/Menu', NULL, 0, 0, 0, 0, 0),
('/Library/layouts/boolive/sidebar/menu/items/news/not_auto', '', 0, 0, 8, 1, NULL, '0', 0, 0, 0, 0, 0),
('/Library/layouts/boolive/sidebar/menu/items/news/title', '', 0, 0, 8, 1, NULL, 'Новости!!', 0, 0, 0, 0, 0),
('/Library/layouts/boolive/sidebar/menu/style', '', 0, 0, 6, 1, '/Library/basic/widgets/Menu/style', 'style.css', 0, 1, 0, 0, 0),
('/Library/layouts/boolive/sidebar/menu/title', '', 0, 0, 6, 1, '/Library/basic/widgets/Menu/title', 'Заголовок меню', 0, 0, 0, 0, 0),
('/Library/layouts/boolive/style', '', 0, 0, 4, 1, '/Library/basic/Css', 'style.css', 0, 1, 0, 0, 0),
('/Library/layouts/boolive/top', '', 0, 0, 4, 1, '/Library/basic/Group', NULL, 0, 0, 0, 0, 0),
('/Library/layouts/boolive/top/menu', '', 0, 0, 5, 1, '/Library/basic/widgets/Menu', 'menu.tpl', 0, 1, 0, 0, 0),
('/Library/layouts/boolive/top/menu/items/main/not_auto', '', 0, 0, 8, 1, NULL, '1', 0, 0, 0, 0, 0),
('/Library/layouts/boolive/top/menu/items/news/not_auto', '', 0, 0, 8, 1, NULL, '1', 0, 0, 0, 0, 0),
('/Library/layouts/boolive/top/menu/style', '', 0, 0, 6, 1, '/Library/basic/Css', 'style.css', 0, 1, 0, 0, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `members`
--

CREATE TABLE IF NOT EXISTS `members` (
  `uri` varchar(255) NOT NULL DEFAULT '' COMMENT 'Унифицированный идентификатор (путь на объект)',
  `lang` char(3) NOT NULL DEFAULT '' COMMENT 'Код языка по ISO 639-3',
  `owner` int(11) NOT NULL DEFAULT '0' COMMENT 'Владелец',
  `date` int(11) NOT NULL COMMENT 'Дата создания объекта',
  `level` int(11) NOT NULL DEFAULT '1' COMMENT 'Уровень вложенности относительно корня',
  `order` int(11) NOT NULL DEFAULT '1' COMMENT 'Порядковый номер',
  `proto` varchar(255) DEFAULT NULL COMMENT 'uri прототипа',
  `value` varchar(255) DEFAULT NULL COMMENT 'Значение',
  `is_logic` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, есть ли класс у объекта',
  `is_file` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Является ли объект файлом',
  `is_history` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, является ли запись историей',
  `is_delete` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, удален объект или нет',
  `is_hidden` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, скрытый объект или нет',
  PRIMARY KEY (`uri`,`lang`,`owner`,`date`),
  KEY `orders` (`order`),
  KEY `sate` (`is_history`,`is_delete`,`is_hidden`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `members`
--

REPLACE INTO `members` (`uri`, `lang`, `owner`, `date`, `level`, `order`, `proto`, `value`, `is_logic`, `is_file`, `is_history`, `is_delete`, `is_hidden`) VALUES
('/Members/guests', '', 0, 0, 2, 1, '/Library/basic/members/UserGroup', NULL, 0, 0, 0, 0, 0),
('/Members/guests/title', '', 0, 0, 3, 1, '/Library/basic/members/UserGroup/title', 'Гости', 0, 0, 0, 0, 0),
('/Members/registered', '', 0, 0, 2, 2, '/Library/basic/members/UserGroup', NULL, 0, 0, 0, 0, 0),
('/Members/registered/admins', '', 0, 0, 3, 2, '/Library/basic/members/UserGroup', NULL, 0, 0, 0, 0, 0),
('/Members/registered/admins/title', '', 0, 0, 4, 1, '/Library/basic/members/UserGroup/title', 'Администраторы', 0, 0, 0, 0, 0),
('/Members/registered/admins/vova', '', 0, 0, 4, 2, '/Library/basic/members/User', 'password_hash', 0, 0, 0, 0, 0),
('/Members/registered/admins/vova/email', '', 0, 0, 5, 2, '/Library/basic/members/User/email', 'boolive@yandex.ru', 0, 0, 0, 0, 0),
('/Members/registered/admins/vova/name', '', 0, 0, 5, 1, '/Library/basic/members/User/name', 'Вова', 0, 0, 0, 0, 0),
('/Members/registered/title', '', 0, 0, 3, 1, '/Library/basic/members/UserGroup/title', 'Зарегистрированные', 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `site`
--

CREATE TABLE IF NOT EXISTS `site` (
  `uri` varchar(255) NOT NULL DEFAULT '' COMMENT 'Унифицированный идентификатор (путь на объект)',
  `lang` char(3) NOT NULL DEFAULT '' COMMENT 'Код языка по ISO 639-3',
  `owner` int(11) NOT NULL DEFAULT '0' COMMENT 'Владелец',
  `date` int(11) NOT NULL COMMENT 'Дата создания объекта',
  `level` int(11) NOT NULL DEFAULT '1' COMMENT 'Уровень вложенности относительно корня',
  `order` int(11) NOT NULL DEFAULT '1' COMMENT 'Порядковый номер',
  `proto` varchar(255) DEFAULT NULL COMMENT 'uri прототипа',
  `value` varchar(255) DEFAULT NULL COMMENT 'Значение',
  `is_logic` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, есть ли класс у объекта',
  `is_file` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Является ли объект файлом',
  `is_history` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, является ли запись историей',
  `is_delete` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, удален объект или нет',
  `is_hidden` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, скрытый объект или нет',
  PRIMARY KEY (`uri`,`lang`,`owner`,`date`),
  KEY `orders` (`order`),
  KEY `sate` (`is_history`,`is_delete`,`is_hidden`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `site`
--

REPLACE INTO `site` (`uri`, `lang`, `owner`, `date`, `level`, `order`, `proto`, `value`, `is_logic`, `is_file`, `is_history`, `is_delete`, `is_hidden`) VALUES
('/Contents', '', 0, 1342077181, 1, 1, NULL, NULL, 0, 0, 0, 0, 0),
('/Interfaces', '', 0, 1342082233, 1, 1, '/Library/basic/Group', NULL, 0, 0, 0, 0, 0),
('/Keywords', '', 0, 1342077181, 1, 1, NULL, NULL, 0, 0, 0, 0, 0),
('/Library', '', 0, 1342077181, 1, 2, NULL, NULL, 0, 0, 0, 0, 0),
('/Members', '', 0, 1342077181, 1, 1, '/Library/basic/members/UserGroup', NULL, 0, 0, 0, 0, 0);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

