-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Окт 05 2012 г., 19:35
-- Версия сервера: 5.5.24
-- Версия PHP: 5.3.10-1ubuntu3.4

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
  `uri` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT 'Унифицированный идентификатор (путь на объект)',
  `lang` char(3) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT 'Код языка по ISO 639-3',
  `owner` int(11) NOT NULL DEFAULT '0' COMMENT 'Владелец',
  `date` int(11) NOT NULL COMMENT 'Дата создания объекта',
  `level` int(11) NOT NULL DEFAULT '1' COMMENT 'Уровень вложенности относительно корня',
  `order` int(11) NOT NULL DEFAULT '1' COMMENT 'Порядковый номер',
  `proto` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'uri прототипа',
  `value` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Значение',
  `is_logic` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, есть ли класс у объекта',
  `is_file` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, Является ли объект файлом',
  `is_history` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, является ли запись историей',
  `is_delete` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, удален объект или нет',
  `is_hidden` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, скрытый объект или нет',
  `is_link` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, является ли объект ссылкой',
  `override` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак не использовать свойства прототипа',
  PRIMARY KEY (`uri`,`lang`,`owner`,`date`),
  KEY `orders` (`level`,`order`),
  KEY `state` (`is_history`,`is_delete`,`is_hidden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Дамп данных таблицы `contents`
--

INSERT INTO `contents` (`uri`, `lang`, `owner`, `date`, `level`, `order`, `proto`, `value`, `is_logic`, `is_file`, `is_history`, `is_delete`, `is_hidden`, `is_link`, `override`) VALUES
('/Contents/contacts', '', 0, 0, 2, 2, '/Library/content_samples/Page', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/contacts/text', '', 0, 0, 3, 2, '/Library/content_samples/Page/text', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/contacts/text/feedback', '', 0, 0, 4, 3, '/Library/content_samples/Feedback', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/contacts/text/feedback/email_from', '', 0, 0, 5, 1, '/Library/content_samples/Feedback/email_from', '', 0, 0, 0, 0, 0, 0, 0),
('/Contents/contacts/text/feedback/email_to', '', 0, 0, 5, 2, '/Library/content_samples/Feedback/email_to', 'info@boolive.ru', 0, 0, 0, 0, 0, 0, 0),
('/Contents/contacts/text/feedback/message', '', 0, 0, 5, 3, '/Library/content_samples/Feedback/message', '', 0, 0, 0, 0, 0, 0, 0),
('/Contents/contacts/text/head1', '', 0, 0, 4, 0, '/Library/content_samples/Head', 'Наш адрес', 0, 0, 0, 0, 0, 0, 0),
('/Contents/contacts/text/p1', '', 0, 0, 4, 1, '/Library/content_samples/Paragraph', 'г. Екатеринбург, ул Ленина, дом 1, офис 999', 0, 0, 0, 0, 0, 0, 0),
('/Contents/contacts/text/p2', '', 0, 0, 4, 2, '/Library/content_samples/Paragraph', 'Работаем груглосуточно', 0, 0, 0, 0, 0, 0, 0),
('/Contents/contacts/title', '', 0, 0, 3, 1, '/Library/content_samples/Page/title', 'Контакты', 0, 0, 0, 0, 0, 0, 0),
('/Contents/main', '', 0, 0, 2, 1, '/Library/content_samples/Page', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/author', '', 0, 0, 3, 4, '/Members/registered/admins/vova', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/comments', '', 0, 0, 3, 6, '/Library/content_samples/Page/comments', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/comments/comment1', '', 0, 0, 4, 1, '/Library/content_samples/Comment', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/comments/comment1/author', '', 0, 0, 5, 1, '/Members/registered/admins/vova', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/comments/comment1/comment11', '', 0, 0, 5, 3, '/Library/content_samples/Comment', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/comments/comment1/comment11/author', '', 0, 0, 6, 1, '/Members/registered/admins/vova', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/comments/comment1/comment11/text', '', 0, 0, 6, 2, '/Library/content_samples/Comment/text', 'Комментарий на первый комментарий', 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/comments/comment1/text', '', 0, 0, 5, 2, '/Library/content_samples/Comment/text', 'Текст первого коммента', 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/comments/comment2', '', 0, 0, 4, 2, '/Library/content_samples/Comment', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/comments/comment2/author', '', 0, 0, 5, 1, '/Members/registered/admins/vova', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/comments/comment2/text', '', 0, 0, 5, 2, '/Library/content_samples/Comment/text', 'Текст второго комментария к главной странице сайта', 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/description', '', 0, 0, 3, 3, '/Library/content_samples/Page/description', 'Главная страница первого простейшего сайта', 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/keywords', '', 0, 0, 3, 5, '/Library/content_samples/Page/keywords', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/keywords/cms', '', 0, 0, 4, 1, '/Keywords/cms', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/keywords/php', '', 0, 0, 4, 2, '/Keywords/php', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/sub_page', '', 0, 0, 3, 7, '/Library/content_samples/Page', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/sub_page/author', '', 0, 0, 4, 3, '/Members/registered/admins/vova', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/sub_page/description', '', 0, 0, 4, 2, '/Library/content_samples/Page/description', 'Подчиненная страница. Это её короткое описание для SEO', 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/sub_page/text', '', 0, 0, 4, 1, '/Library/content_samples/Page/text', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/sub_page/text/head1', '', 0, 0, 5, 0, '/Library/content_samples/Head', 'Заголовок подчиненной страницы', 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/sub_page/text/head2', '', 0, 0, 5, 2, '/Library/content_samples/Head', 'Подзаголовок подчиненной страницы', 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/sub_page/text/img1', '', 0, 0, 5, 4, '/Library/content_samples/Image', 'img1.jpg', 0, 1, 0, 0, 0, 0, 0),
('/Contents/main/sub_page/text/p1', '', 0, 0, 5, 1, '/Library/content_samples/Paragraph', 'Первый абзац подчиенной страницы... текст... текст...', 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/sub_page/text/p2', '', 0, 0, 5, 3, '/Library/content_samples/Paragraph', 'Второй абзац подчиенной страницы... текст... текст...', 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/sub_page/title', '', 0, 0, 4, 0, '/Library/content_samples/Page/title', 'Подстраница', 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/text', '', 0, 0, 3, 2, '/Library/content_samples/Page/text', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/text/head1', '', 0, 0, 4, 2, '/Library/content_samples/Head', 'Заголовок главной страницы', 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/text/img1', '', 0, 0, 4, 3, '/Library/content_samples/Image', 'img1.jpg', 0, 1, 1, 0, 0, 0, 0),
('/Contents/main/text/img1', '', 0, 1349234641, 4, 1, '/Library/content_samples/Image', 'img1.jpg', 0, 1, 0, 0, 0, 0, 0),
('/Contents/main/text/list1', '', 0, 0, 4, 6, '/Library/content_samples/lists/List', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/text/list1/item1', '', 0, 0, 5, 1, '/Library/content_samples/lists/Item', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/text/list1/item1/text', '', 0, 0, 6, 1, '/Library/content_samples/RichText', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/text/list1/item1/text/p1', '', 0, 0, 7, 1, '/Library/content_samples/Paragraph', 'Хей-хей-хей! Я - пункт меню!', 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/text/list1/item1/text/p2', '', 0, 0, 7, 2, '/Library/content_samples/Paragraph', 'А я - второй параграф =)', 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/text/list1/item2', '', 0, 0, 5, 2, '/Library/content_samples/lists/Item', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/text/list1/item2/text', '', 0, 0, 6, 1, '/Library/content_samples/RichText', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/text/list1/item2/text/img1', '', 0, 0, 7, 1, '/Library/content_samples/Image', 'nota.png', 0, 1, 0, 0, 0, 0, 0),
('/Contents/main/text/list1/item2/text/p1', '', 0, 0, 7, 2, '/Library/content_samples/Paragraph', 'Ля-ля-ля, ля-ля-ля!', 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/text/list1/item3', '', 0, 0, 5, 3, '/Library/content_samples/lists/Item', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/text/list1/item3/list1', '', 0, 0, 6, 1, '/Library/content_samples/lists/List', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/text/list1/item3/list1/item1', '', 0, 0, 7, 1, '/Library/content_samples/lists/Item', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/text/list1/item3/list1/item1/text', '', 0, 0, 8, 1, '/Library/content_samples/Page/text', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/text/list1/item3/list1/item1/text/p1', '', 0, 0, 9, 1, '/Library/content_samples/Paragraph', 'А вот пункт', 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/text/list1/item3/list1/item2', '', 0, 0, 7, 2, '/Library/content_samples/lists/Item', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/text/list1/item3/list1/item2/text', '', 0, 0, 8, 1, '/Library/content_samples/Page/text', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/text/list1/item3/list1/item2/text/p1', '', 0, 0, 9, 1, '/Library/content_samples/Paragraph', 'вложенного меню', 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/text/p1', '', 0, 0, 4, 3, '/Library/content_samples/Paragraph', 'Добро пожаловать на тестовый сайт. Сайт работает на новой системе Boolive 2', 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/text/p2', '', 0, 0, 4, 5, '/Library/content_samples/Paragraph', 'p2.value', 0, 1, 0, 0, 0, 0, 0),
('/Contents/main/title', '', 0, 0, 3, 1, '/Library/content_samples/Page/title', 'Главная', 0, 0, 0, 0, 0, 0, 0),
('/Contents/news', '', 0, 0, 2, 3, '/Library/content_samples/Part', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/news1', '', 0, 0, 3, 2, '/Library/content_samples/Page', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/news1/author', '', 0, 0, 4, 3, '/Members/registered/admins/vova', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/news1/text', '', 0, 0, 4, 2, '/Library/content_samples/Page/text', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/news1/text/p1', '', 0, 0, 5, 1, '/Library/content_samples/Paragraph', 'Текст новости в виде одного абзаца', 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/news1/title', '', 0, 0, 4, 1, '/Library/content_samples/Page/title', 'Первая новость', 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/news2', '', 0, 0, 3, 3, '/Library/content_samples/Page', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/news2/author', '', 0, 0, 4, 3, '/Members/registered/admins/vova', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/news2/text', '', 0, 0, 4, 2, '/Library/content_samples/Page/text', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/news2/text/p1', '', 0, 0, 5, 1, '/Library/content_samples/Paragraph', 'Ноовсть создаётся как страница, то есть новость и есть страницой, просто она помещена в раздел Ленты новостей', 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/news2/title', '', 0, 0, 4, 1, '/Library/content_samples/Page/title', 'Вторая новость', 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/news3', '', 0, 0, 3, 4, '/Library/content_samples/Page', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/news3/author', '', 0, 0, 4, 3, '/Members/registered/admins/vova', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/news3/text', '', 0, 0, 4, 2, '/Library/content_samples/Page/text', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/news3/text/p1', '', 0, 0, 5, 1, '/Library/content_samples/Paragraph', 'Текст новости в виде одного абзаца', 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/news3/title', '', 0, 0, 4, 1, '/Library/content_samples/Page/title', 'Третья новость', 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/news4', '', 0, 0, 3, 5, '/Library/content_samples/Page', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/news4/author', '', 0, 0, 4, 3, '/Members/registered/admins/vova', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/news4/text', '', 0, 0, 4, 2, '/Library/content_samples/Page/text', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/news4/text/p1', '', 0, 0, 5, 1, '/Library/content_samples/Paragraph', 'Текст новости в виде одного абзаца', 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/news4/title', '', 0, 0, 4, 1, '/Library/content_samples/Page/title', 'Четвертая новость', 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/title', '', 0, 0, 3, 1, '/Library/content_samples/Part/title', 'Лента новостей', 0, 0, 0, 0, 0, 0, 0),
('/Contents/title', '', 0, 0, 2, 1, NULL, 'Содержимое', 0, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `interfaces`
--

CREATE TABLE IF NOT EXISTS `interfaces` (
  `uri` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT 'Унифицированный идентификатор (путь на объект)',
  `lang` char(3) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT 'Код языка по ISO 639-3',
  `owner` int(11) NOT NULL DEFAULT '0' COMMENT 'Владелец',
  `date` int(11) NOT NULL COMMENT 'Дата создания объекта',
  `level` int(11) NOT NULL DEFAULT '1' COMMENT 'Уровень вложенности относительно корня',
  `order` int(11) NOT NULL DEFAULT '1' COMMENT 'Порядковый номер',
  `proto` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'uri прототипа',
  `value` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Значение',
  `is_logic` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, есть ли класс у объекта',
  `is_file` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, Является ли объект файлом',
  `is_history` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, является ли запись историей',
  `is_delete` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, удален объект или нет',
  `is_hidden` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, скрытый объект или нет',
  `is_link` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, является ли объект ссылкой',
  `override` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uri`,`lang`,`owner`,`date`),
  KEY `orders` (`level`,`order`),
  KEY `state` (`is_history`,`is_delete`,`is_hidden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Дамп данных таблицы `interfaces`
--

INSERT INTO `interfaces` (`uri`, `lang`, `owner`, `date`, `level`, `order`, `proto`, `value`, `is_logic`, `is_file`, `is_history`, `is_delete`, `is_hidden`, `is_link`, `override`) VALUES
('/Interfaces/direct_handler', '', 0, 0, 2, 1, '/Library/views/DirectHandler', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Interfaces/html', '', 0, 0, 2, 2, '/Library/views/Html', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Interfaces/html/body/admin', '', 0, 0, 4, 1, '/Library/layouts/Admin', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Interfaces/html/body/boolive', '', 0, 0, 4, 2, '/Library/layouts/boolive', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Interfaces/title', '', 0, 0, 2, 3, NULL, 'Интерфейс', 0, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `keywords`
--

CREATE TABLE IF NOT EXISTS `keywords` (
  `uri` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT 'Унифицированный идентификатор (путь на объект)',
  `lang` char(3) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT 'Код языка по ISO 639-3',
  `owner` int(11) NOT NULL DEFAULT '0' COMMENT 'Владелец',
  `date` int(11) NOT NULL COMMENT 'Дата создания объекта',
  `level` int(11) NOT NULL DEFAULT '1' COMMENT 'Уровень вложенности относительно корня',
  `order` int(11) NOT NULL DEFAULT '1' COMMENT 'Порядковый номер',
  `proto` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'uri прототипа',
  `value` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Значение',
  `is_logic` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, есть ли класс у объекта',
  `is_file` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, Является ли объект файлом',
  `is_history` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, является ли запись историей',
  `is_delete` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, удален объект или нет',
  `is_hidden` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, скрытый объект или нет',
  `is_link` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, является ли объект ссылкой',
  `override` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uri`,`lang`,`owner`,`date`),
  KEY `orders` (`level`,`order`),
  KEY `state` (`is_history`,`is_delete`,`is_hidden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Дамп данных таблицы `keywords`
--

INSERT INTO `keywords` (`uri`, `lang`, `owner`, `date`, `level`, `order`, `proto`, `value`, `is_logic`, `is_file`, `is_history`, `is_delete`, `is_hidden`, `is_link`, `override`) VALUES
('/Keywords/cms', '', 0, 0, 2, 1, '/Library/content_samples/Keyword', '1', 0, 0, 0, 0, 0, 0, 0),
('/Keywords/framework', '', 0, 0, 2, 1, '/Library/content_samples/Keyword', '0', 0, 0, 0, 0, 0, 0, 0),
('/Keywords/php', '', 0, 0, 2, 1, '/Library/content_samples/Keyword', '1', 0, 0, 0, 0, 0, 0, 0),
('/Keywords/title', '', 0, 0, 2, 1, NULL, 'Ключевые слова', 0, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `library`
--

CREATE TABLE IF NOT EXISTS `library` (
  `uri` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT 'Унифицированный идентификатор (путь на объект)',
  `lang` char(3) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT 'Код языка по ISO 639-3',
  `owner` int(11) NOT NULL DEFAULT '0' COMMENT 'Владелец',
  `date` int(11) NOT NULL COMMENT 'Дата создания объекта',
  `level` int(11) NOT NULL DEFAULT '1' COMMENT 'Уровень вложенности относительно корня',
  `order` int(11) NOT NULL DEFAULT '1' COMMENT 'Порядковый номер',
  `proto` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'uri прототипа',
  `value` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Значение',
  `is_logic` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, есть ли класс у объекта',
  `is_file` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, Является ли объект файлом',
  `is_history` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, является ли запись историей',
  `is_delete` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, удален объект или нет',
  `is_hidden` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, скрытый объект или нет',
  `is_link` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, является ли объект ссылкой',
  `override` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uri`,`lang`,`owner`,`date`),
  KEY `orders` (`level`,`order`),
  KEY `state` (`is_history`,`is_delete`,`is_hidden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Дамп данных таблицы `library`
--

INSERT INTO `library` (`uri`, `lang`, `owner`, `date`, `level`, `order`, `proto`, `value`, `is_logic`, `is_file`, `is_history`, `is_delete`, `is_hidden`, `is_link`, `override`) VALUES
('/Library/admin_widgets', '', 0, 0, 2, 5, '/Library/basic/Package', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/admin_widgets/Add', '', 0, 0, 3, 4, '/Library/views/Widget', 'Add.tpl', 1, 1, 0, 0, 0, 0, 0),
('/Library/admin_widgets/Add/description', '', 0, 0, 4, 2, '/Library/views/Widget/description', 'Предоставляет выбор объекта для добавления его в отображаемый объект', 0, 0, 0, 0, 0, 0, 0),
('/Library/admin_widgets/Add/icon', '', 0, 0, 4, 3, '/Library/content_samples/Image', 'icon.png', 0, 1, 0, 0, 0, 0, 0),
('/Library/admin_widgets/Add/title', '', 0, 0, 4, 1, '/Library/views/Widget/title', 'Добавить', 0, 0, 0, 0, 0, 0, 0),
('/Library/admin_widgets/Attribs', '', 0, 0, 3, 6, '/Library/views/Widget', 'Attribs.tpl', 1, 1, 0, 0, 0, 0, 0),
('/Library/admin_widgets/Attribs/description', '', 0, 0, 4, 2, '/Library/views/Widget/description', 'Редактор атрибутов любого объекта', 0, 0, 0, 0, 0, 0, 0),
('/Library/admin_widgets/Attribs/icon', '', 0, 0, 4, 3, '/Library/content_samples/Image', 'icon.png', 0, 1, 0, 0, 0, 0, 0),
('/Library/admin_widgets/Attribs/res/jquery.form', '', 0, 0, 5, 4, '/Library/javascript_plugins/jquery.form', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/admin_widgets/Attribs/res/jquery.ui.Attribs', '', 0, 0, 5, 5, '/Library/javascript_plugins/jQueryAjaxWidget', 'jquery.ui.Attribs.js', 0, 1, 0, 0, 0, 0, 0),
('/Library/admin_widgets/Attribs/res/style', '', 0, 0, 5, 3, '/Library/views/Css', 'style.css', 0, 1, 0, 0, 0, 0, 0),
('/Library/admin_widgets/Attribs/title', '', 0, 0, 4, 1, '/Library/views/Widget/title', 'Атрибуты', 0, 0, 0, 0, 0, 0, 0),
('/Library/admin_widgets/Delete', '', 0, 0, 3, 5, '/Library/views/Widget', 'Delete.tpl', 1, 1, 0, 0, 0, 0, 0),
('/Library/admin_widgets/Delete/description', '', 0, 0, 4, 2, '/Library/views/Widget/description', 'Отображает диалоговое окно для подтверждения удаления и осуществляет удаление (пермещение в корзину)', 0, 0, 0, 0, 0, 0, 0),
('/Library/admin_widgets/Delete/icon', '', 0, 0, 4, 3, '/Library/content_samples/Image', 'icon.png', 0, 1, 0, 0, 0, 0, 0),
('/Library/admin_widgets/Delete/title', '', 0, 0, 4, 1, '/Library/views/Widget/title', 'Удалить', 0, 0, 0, 0, 0, 0, 0),
('/Library/admin_widgets/Explorer', '', 0, 0, 3, 2, '/Library/views/AutoWidgetList', 'Explorer.tpl', 1, 1, 0, 0, 0, 0, 0),
('/Library/admin_widgets/Explorer/description', '', 0, 0, 4, 2, '/Library/views/AutoWidgetList/description', 'Отображает списком свойства объекта', 0, 0, 0, 0, 0, 0, 0),
('/Library/admin_widgets/Explorer/icon', '', 0, 0, 4, 3, '/Library/content_samples/Image', 'icon.png', 0, 1, 0, 0, 0, 0, 0),
('/Library/admin_widgets/Explorer/res/jquery.ui.Explorer', '', 0, 0, 5, 4, '/Library/javascript_plugins/jQueryAjaxWidget', 'jquery.ui.Explorer.js', 0, 1, 0, 0, 0, 0, 0),
('/Library/admin_widgets/Explorer/res/style', '', 0, 0, 5, 1, '/Library/views/Css', 'style.css', 0, 1, 0, 0, 0, 0, 0),
('/Library/admin_widgets/Explorer/switch_views', '', 0, 0, 4, 3, '/Library/views/AutoWidgetList/switch_views', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/admin_widgets/Explorer/switch_views/case_default', '', 0, 0, 5, 1, '/Library/views/SwitchCase', 'all', 0, 0, 0, 0, 0, 0, 0),
('/Library/admin_widgets/Explorer/switch_views/case_default/ObjectItem', '', 0, 0, 6, 1, '/Library/admin_widgets/ObjectItem', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/admin_widgets/Explorer/title', '', 0, 0, 4, 1, '/Library/views/AutoWidgetList/title', 'Обозреватель', 0, 0, 0, 0, 0, 0, 0),
('/Library/admin_widgets/ObjectItem', '', 0, 0, 3, 1, '/Library/views/Widget', 'ObjectItem.tpl', 1, 1, 0, 0, 0, 0, 0),
('/Library/admin_widgets/ObjectItem/description', '', 0, 0, 4, 2, '/Library/views/Widget/description', 'Отображение объекта в виде пункта списка', 0, 0, 0, 0, 0, 0, 0),
('/Library/admin_widgets/ObjectItem/res/jquery.ui.ObjectItem', '', 0, 0, 5, 4, '/Library/javascript_plugins/jQueryAjaxWidget', 'jquery.ui.ObjectItem.js', 0, 1, 0, 0, 0, 0, 0),
('/Library/admin_widgets/ObjectItem/res/style', '', 0, 0, 5, 3, '/Library/views/Css', 'style.css', 0, 1, 0, 0, 0, 0, 0),
('/Library/admin_widgets/ObjectItem/title', '', 0, 0, 4, 1, '/Library/views/Widget/title', 'Виджет объекта', 0, 0, 0, 0, 0, 0, 0),
('/Library/basic', '', 0, 0, 2, 1, '/Library/basic/Package', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/basic/Package', '', 0, 0, 3, 5, NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/basic/members', '', 0, 0, 3, 3, '/Library/basic/Package', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/basic/members/User', '', 0, 0, 4, 1, NULL, NULL, 1, 0, 0, 0, 0, 0, 0),
('/Library/basic/members/User/email', '', 0, 0, 5, 3, '/Library/basic/simple/Email', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/basic/members/User/name', '', 0, 0, 5, 2, NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/basic/members/User/title', '', 0, 0, 5, 1, NULL, 'Пользователь', 0, 0, 0, 0, 0, 0, 0),
('/Library/basic/members/UserGroup', '', 0, 0, 4, 2, NULL, NULL, 1, 0, 0, 0, 0, 0, 0),
('/Library/basic/members/UserGroup/title', '', 0, 0, 5, 1, NULL, 'Группа пользователей', 0, 0, 0, 0, 0, 0, 0),
('/Library/basic/simple', '', 0, 0, 3, 4, '/Library/basic/Package', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/basic/simple/Email', '', 0, 0, 4, 3, NULL, NULL, 1, 0, 0, 0, 0, 0, 0),
('/Library/basic/simple/Number', '', 0, 0, 4, 4, NULL, NULL, 1, 0, 0, 0, 0, 0, 0),
('/Library/basic/simple/Text', '', 0, 0, 4, 5, NULL, NULL, 1, 0, 0, 0, 0, 0, 0),
('/Library/basic/simple/Text/description', '', 0, 0, 5, 2, NULL, 'Строковое значение длиной до 65535 символа (64Кбайт)', 0, 0, 0, 0, 0, 0, 0),
('/Library/basic/simple/Text/title', '', 0, 0, 5, 1, NULL, 'Текст', 0, 0, 0, 0, 0, 0, 0),
('/Library/content_samples', '', 0, 0, 2, 1, '/Library/basic/Package', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_samples/Comment', '', 0, 0, 3, 1, NULL, NULL, 1, 0, 0, 0, 0, 0, 0),
('/Library/content_samples/Comment/text', '', 0, 0, 4, 1, NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_samples/Feedback', '', 0, 0, 3, 2, NULL, NULL, 1, 0, 0, 0, 0, 0, 0),
('/Library/content_samples/Feedback/email_from', '', 0, 0, 4, 1, '/Library/basic/simple/Email', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_samples/Feedback/email_from/title', '', 0, 0, 5, 1, NULL, 'Email адрес', 0, 0, 0, 0, 0, 0, 0),
('/Library/content_samples/Feedback/email_to', '', 0, 0, 4, 2, '/Library/basic/simple/Email', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_samples/Feedback/error_message', '', 0, 0, 4, 7, '/Library/basic/simple/Text', 'Имеются ошибки', 0, 0, 0, 0, 0, 0, 0),
('/Library/content_samples/Feedback/message', '', 0, 0, 4, 3, '/Library/basic/simple/Text', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_samples/Feedback/message/title', '', 0, 0, 5, 1, NULL, 'Сообщение', 0, 0, 0, 0, 0, 0, 0),
('/Library/content_samples/Feedback/result_message', '', 0, 0, 4, 6, '/Library/basic/simple/Text', 'Спасибо, сообщение успешно отправлено', 0, 0, 0, 0, 0, 0, 0),
('/Library/content_samples/Feedback/title', '', 0, 0, 4, 1, NULL, 'Обратная связь', 0, 0, 0, 0, 0, 0, 0),
('/Library/content_samples/Head', '', 0, 0, 3, 3, NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_samples/Image', '', 0, 0, 3, 4, NULL, NULL, 1, 0, 0, 0, 0, 0, 0),
('/Library/content_samples/Keyword', '', 0, 0, 3, 5, NULL, NULL, 1, 0, 0, 0, 0, 0, 0),
('/Library/content_samples/Page', '', 0, 0, 3, 11, NULL, NULL, 1, 0, 0, 0, 0, 0, 0),
('/Library/content_samples/Page/comments', '', 0, 0, 4, 4, NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_samples/Page/description', '', 0, 0, 4, 2, NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_samples/Page/keywords', '', 0, 0, 4, 3, NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_samples/Page/text', '', 0, 0, 4, 1, '/Library/content_samples/RichText', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_samples/Page/title', '', 0, 0, 4, 0, NULL, 'Страница', 0, 0, 0, 0, 0, 0, 0),
('/Library/content_samples/Paragraph', '', 0, 0, 3, 8, NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_samples/Part', '', 0, 0, 3, 10, NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_samples/Part/title', '', 0, 0, 4, 1, NULL, 'Раздел', 0, 0, 0, 0, 0, 0, 0),
('/Library/content_samples/RichText', '', 0, 0, 3, 9, NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_samples/RichText/title', '', 0, 0, 4, 1, NULL, 'Форматированный текст', 0, 0, 0, 0, 0, 0, 0),
('/Library/content_samples/lists', '', 0, 0, 3, 6, '/Library/basic/Package', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_samples/lists/Item', '', 0, 0, 4, 2, NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_samples/lists/List', '', 0, 0, 4, 1, NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_samples/tables', '', 0, 0, 3, 7, '/Library/basic/Package', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_samples/tables/Cell', '', 0, 0, 4, 3, NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_samples/tables/Row', '', 0, 0, 4, 2, NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_samples/tables/Table', '', 0, 0, 4, 1, NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets', '', 0, 0, 2, 1, '/Library/basic/Package', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/Comment', '', 0, 0, 3, 1, '/Library/views/Widget', 'Comment.tpl', 1, 1, 0, 0, 0, 0, 0),
('/Library/content_widgets/Comments', '', 0, 0, 3, 1, '/Library/views/AutoWidgetList', 'Comments.tpl', 1, 1, 0, 0, 0, 0, 0),
('/Library/content_widgets/Comments/res/style', '', 0, 0, 5, 1, '/Library/views/Css', 'style.css', 0, 1, 0, 0, 0, 0, 0),
('/Library/content_widgets/Comments/switch_views', '', 0, 0, 4, 1, '/Library/views/SwitchViews', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/Comments/switch_views/case_comment', '', 0, 0, 5, 1, '/Library/views/SwitchCase', '/Library/content_samples/Comment', 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/Comments/switch_views/case_comment/Comment', '', 0, 0, 6, 1, '/Library/content_widgets/Comment', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/Content', '', 0, 0, 3, 1, '/Library/views/AutoWidget', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/Content/switch_views/case_page', '', 0, 0, 5, 1, '/Library/views/SwitchCase', '/Library/content_samples/Page', 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/Content/switch_views/case_page/page', '', 0, 0, 6, 1, '/Library/content_widgets/Page', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/Content/switch_views/case_part', '', 0, 0, 5, 2, '/Library/views/SwitchCase', '/Library/content_samples/Part', 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/Content/switch_views/case_part/part', '', 0, 0, 6, 1, '/Library/content_widgets/Part', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/Feedback', '', 0, 0, 3, 1, '/Library/views/AutoWidgetList', 'Feedback.tpl', 1, 1, 0, 0, 0, 0, 0),
('/Library/content_widgets/Feedback/res/style', '', 0, 0, 5, 5, '/Library/views/Css', 'style.css', 0, 1, 0, 0, 0, 0, 0),
('/Library/content_widgets/Feedback/switch_views/case_mail', '', 0, 0, 5, 1, '/Library/views/SwitchCase', '/Library/content_samples/Feedback/email_from', 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/Feedback/switch_views/case_mail/EmailField', '', 0, 0, 6, 1, '/Library/views/FormField', 'EmailField.tpl', 0, 1, 0, 0, 0, 0, 0),
('/Library/content_widgets/Feedback/switch_views/case_message', '', 0, 0, 5, 2, '/Library/views/SwitchCase', '/Library/content_samples/Feedback/message', 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/Feedback/switch_views/case_message/MessageField', '', 0, 0, 6, 1, '/Library/views/FormField', 'MessageField.tpl', 0, 1, 0, 0, 0, 0, 0),
('/Library/content_widgets/Head', '', 0, 0, 3, 1, '/Library/views/Widget', 'Head.tpl', 1, 1, 0, 0, 0, 0, 0),
('/Library/content_widgets/Image', '', 0, 0, 3, 1, '/Library/views/Widget', 'Image.tpl', 1, 1, 0, 0, 0, 0, 0),
('/Library/content_widgets/Keyword', '', 0, 0, 3, 1, '/Library/views/Widget', 'Keyword.tpl', 1, 1, 0, 0, 0, 0, 0),
('/Library/content_widgets/Keywords', '', 0, 0, 3, 1, '/Library/views/AutoWidgetList', 'Keywords.tpl', 1, 1, 0, 0, 0, 0, 0),
('/Library/content_widgets/Keywords/res', '', 0, 0, 4, 1, '/Library/views/Widget/res', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/Keywords/res/style', '', 0, 0, 5, 1, '/Library/views/Css', 'style.css', 0, 1, 0, 0, 0, 0, 0),
('/Library/content_widgets/Keywords/switch_views', '', 0, 0, 4, 1, '/Library/views/SwitchViews', 'switch_views.tpl', 0, 1, 0, 0, 0, 0, 0),
('/Library/content_widgets/Keywords/switch_views/case_keyword', '', 0, 0, 5, 1, '/Library/views/SwitchCase', '/Library/content_samples/Keyword', 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/Keywords/switch_views/case_keyword/Keyword', '', 0, 0, 6, 1, '/Library/content_widgets/Keyword', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/ListView', '', 0, 0, 3, 1, '/Library/views/AutoWidgetList', 'ListView.tpl', 0, 1, 0, 0, 0, 0, 0),
('/Library/content_widgets/ListView/switch_views', '', 0, 0, 4, 1, '/Library/views/SwitchViews', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/ListView/switch_views/case_item', '', 0, 0, 5, 1, '/Library/views/SwitchCase', '/Library/content_samples/lists/Item', 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/ListView/switch_views/case_item/Item', '', 0, 0, 6, 1, '/Library/views/AutoWidgetList', 'Item.tpl', 0, 1, 0, 0, 0, 0, 0),
('/Library/content_widgets/ListView/switch_views/case_item/Item/switch_views', '', 0, 0, 7, 1, '/Library/views/SwitchViews', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/ListView/switch_views/case_item/Item/switch_views/case_list', '', 0, 0, 8, 1, '/Library/views/SwitchCase', '/Library/content_samples/lists/List', 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/ListView/switch_views/case_item/Item/switch_views/case_list/ListView', '', 0, 0, 9, 1, '/Library/content_widgets/ListView', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/ListView/switch_views/case_item/Item/switch_views/case_richtext', '', 0, 0, 8, 1, '/Library/views/SwitchCase', '/Library/content_samples/RichText', 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/ListView/switch_views/case_item/Item/switch_views/case_richtext/RichText', '', 0, 0, 9, 1, '/Library/content_widgets/RichText', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/NextPrevPage', '', 0, 0, 3, 1, '/Library/views/Widget', 'NextPrevPage.tpl', 1, 1, 0, 0, 0, 0, 0),
('/Library/content_widgets/Page', '', 0, 0, 3, 1, '/Library/views/AutoWidgetList', 'Page.tpl', 1, 1, 0, 0, 0, 0, 0),
('/Library/content_widgets/Page/NextPrevPage', '', 0, 0, 1, 1, '/Library/content_widgets/NextPrevPage', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/Page/res/style', '', 0, 0, 5, 1, '/Library/views/Css', 'style.css', 0, 1, 0, 0, 0, 0, 0),
('/Library/content_widgets/Page/switch_views/case_comments', '', 0, 0, 5, 1, '/Library/views/SwitchCase', '/Library/content_samples/Page/comments', 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/Page/switch_views/case_comments/Comments', '', 0, 0, 6, 1, '/Library/content_widgets/Comments', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/Page/switch_views/case_keywords', '', 0, 0, 5, 2, '/Library/views/SwitchCase', '/Library/content_samples/Page/keywords', 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/Page/switch_views/case_keywords/Keywords', '', 0, 0, 6, 1, '/Library/content_widgets/Keywords', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/Page/switch_views/case_text', '', 0, 0, 5, 3, '/Library/views/SwitchCase', '/Library/content_samples/Page/text', 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/Page/switch_views/case_text/Text', '', 0, 0, 6, 1, '/Library/content_widgets/RichText', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/Page/switch_views/case_title', '', 0, 0, 5, 4, '/Library/views/SwitchCase', '/Library/content_samples/Page/title', 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/Page/switch_views/case_title/Title', '', 0, 0, 6, 1, '/Library/content_widgets/Title', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/PagePreview', '', 0, 0, 3, 1, '/Library/views/AutoWidgetList', 'PagePreview.tpl', 0, 1, 0, 0, 0, 0, 0),
('/Library/content_widgets/PagePreview/res/style', '', 0, 0, 5, 1, '/Library/views/Css', 'style.css', 0, 1, 0, 0, 0, 0, 0),
('/Library/content_widgets/PagePreview/switch_views/case_text', '', 0, 0, 5, 1, '/Library/views/SwitchCase', '/Library/content_samples/Page/text', 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/PagePreview/switch_views/case_text/Text', '', 0, 0, 6, 1, '/Library/content_widgets/RichText', NULL, 1, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/PagePreview/switch_views/case_title', '', 0, 0, 5, 2, '/Library/views/SwitchCase', '/Library/content_samples/Page/title', 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/PagePreview/switch_views/case_title/Title', '', 0, 0, 6, 1, '/Library/content_widgets/Title', 'Title.tpl', 0, 1, 0, 0, 0, 0, 0),
('/Library/content_widgets/PagePreview/title', '', 0, 1349349439, 4, 1, '/Library/views/AutoWidgetList/title', 'Превью страницы ', 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/Paragraph', '', 0, 0, 3, 1, '/Library/views/Widget', 'Paragraph.tpl', 1, 1, 0, 0, 0, 0, 0),
('/Library/content_widgets/Part', '', 0, 0, 3, 1, '/Library/views/AutoWidgetList', 'Part.tpl', 1, 1, 0, 0, 0, 0, 0),
('/Library/content_widgets/Part/count_per_page', '', 0, 0, 4, 9, NULL, '4', 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/Part/pagesnum', '', 0, 0, 4, 10, '/Library/views/PageNavigation', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/Part/switch_views/case_page', '', 0, 0, 5, 1, '/Library/views/SwitchCase', '/Library/content_samples/Page', 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/Part/switch_views/case_page/PagePreview', '', 0, 0, 6, 1, '/Library/content_widgets/PagePreview', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/Part/switch_views/case_part', '', 0, 0, 5, 2, '/Library/views/SwitchCase', '/Library/content_samples/Part', 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/Part/switch_views/case_part/PartPreview', '', 0, 0, 6, 1, '/Library/content_widgets/PartPreview', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/Part/switch_views/case_title', '', 0, 0, 5, 3, '/Library/views/SwitchCase', '/Library/content_samples/Part/title', 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/Part/switch_views/case_title/Title', '', 0, 0, 6, 1, '/Library/content_widgets/Title', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/PartPreview', '', 0, 0, 3, 1, '/Library/views/AutoWidgetList', 'PartPreview.tpl', 0, 1, 0, 0, 0, 0, 0),
('/Library/content_widgets/RichText', '', 0, 0, 3, 1, '/Library/views/AutoWidgetList', 'RichText.tpl', 1, 1, 0, 0, 0, 0, 0),
('/Library/content_widgets/RichText/switch_views/case_feedback', '', 0, 0, 5, 1, '/Library/views/SwitchCase', '/Library/content_samples/Feedback', 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/RichText/switch_views/case_feedback/feedback', '', 0, 0, 6, 1, '/Library/content_widgets/Feedback', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/RichText/switch_views/case_h', '', 0, 0, 5, 2, '/Library/views/SwitchCase', '/Library/content_samples/Head', 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/RichText/switch_views/case_h/head', '', 0, 0, 6, 1, '/Library/content_widgets/Head', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/RichText/switch_views/case_img', '', 0, 0, 5, 3, '/Library/views/SwitchCase', '/Library/content_samples/Image', 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/RichText/switch_views/case_img/img', '', 0, 0, 6, 1, '/Library/content_widgets/Image', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/RichText/switch_views/case_list', '', 0, 0, 5, 1, '/Library/views/SwitchCase', '/Library/content_samples/lists/List', 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/RichText/switch_views/case_list/List', '', 0, 0, 6, 1, '/Library/content_widgets/ListView', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/RichText/switch_views/case_p', '', 0, 0, 5, 4, '/Library/views/SwitchCase', '/Library/content_samples/Paragraph', 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/RichText/switch_views/case_p/paragraph', '', 0, 0, 6, 1, '/Library/content_widgets/Paragraph', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/content_widgets/Title', '', 0, 0, 3, 1, '/Library/views/Widget', 'Title.tpl', 1, 1, 0, 0, 0, 0, 0),
('/Library/javascript_plugins', '', 0, 0, 2, 7, '/Library/basic/Package', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/javascript_plugins/HistoryAPI', '', 0, 0, 3, 5, '/Library/views/JavaScript', 'HistoryAPI.js', 1, 1, 0, 0, 0, 0, 0),
('/Library/javascript_plugins/HistoryAPI/basepath', '', 0, 0, 4, 3, NULL, '/admin/', 0, 0, 0, 0, 0, 0, 0),
('/Library/javascript_plugins/HistoryAPI/description', '', 0, 0, 4, 2, '/Library/views/JavaScript/description', 'Библиотека эмулирует HTML5 History API в старых браузерах.', 0, 0, 0, 0, 0, 0, 0),
('/Library/javascript_plugins/HistoryAPI/redirect', '', 0, 0, 4, 6, NULL, '1', 0, 0, 0, 0, 0, 0, 0),
('/Library/javascript_plugins/HistoryAPI/title', '', 0, 0, 4, 1, '/Library/views/JavaScript/title', 'HTML5-History-API', 0, 0, 0, 0, 0, 0, 0),
('/Library/javascript_plugins/HistoryAPI/type', '', 0, 0, 4, 5, NULL, '', 0, 0, 0, 0, 0, 0, 0),
('/Library/javascript_plugins/description', '', 0, 0, 3, 2, '/Library/basic/Package/description', 'Пакет плагинов (скриптов) на JavaScript с использованием различный библиотек', 0, 0, 0, 0, 0, 0, 0),
('/Library/javascript_plugins/jQueryAjaxWidget', '', 0, 0, 3, 4, '/Library/views/jQueryUIScript', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/javascript_plugins/jQueryAjaxWidget/depends/jquery.include', '', 0, 0, 5, 1, '/Library/javascript_plugins/jquery.include', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/javascript_plugins/jQueryAjaxWidget/depends/jquery.ui.AjaxWidget', '', 0, 0, 5, 3, '/Library/views/JavaScript', 'jquery.ui.AjaxWidget.js', 0, 1, 0, 0, 0, 0, 0),
('/Library/javascript_plugins/jQueryAjaxWidget/description', '', 0, 0, 4, 2, '/Library/views/JavaScript/description', 'jQueryUI виджет с функцией обновления без полной перегрузки страницы', 0, 0, 0, 0, 0, 0, 0),
('/Library/javascript_plugins/jQueryAjaxWidget/title', '', 0, 0, 4, 1, '/Library/views/JavaScript/title', 'Виджет с обновлением по Ajax', 0, 0, 0, 0, 0, 0, 0),
('/Library/javascript_plugins/jquery.form', '', 0, 0, 3, 7, '/Library/views/jQueryScript', 'jquery.form.js', 0, 1, 0, 0, 0, 0, 0),
('/Library/javascript_plugins/jquery.form/description', '', 0, 0, 4, 1, '/Library/views/jQueryScript/description', 'Отправка форм', 0, 0, 0, 0, 0, 0, 0),
('/Library/javascript_plugins/jquery.form/title', '', 0, 0, 4, 1, '/Library/views/jQueryScript/title', 'Плагин для для отправки пользовательских форм по AJAX без перегрузки страницы', 0, 0, 0, 0, 0, 0, 0),
('/Library/javascript_plugins/jquery.include', '', 0, 0, 3, 3, '/Library/views/jQueryScript', 'jquery.include.js', 0, 1, 0, 0, 0, 0, 0),
('/Library/javascript_plugins/jquery.include/description', '', 0, 0, 4, 2, '/Library/views/jQueryScript/description', 'Плагин для динамической загрузки JavaScript и CSS файлов со стороны клиента (браузера)', 0, 0, 0, 0, 0, 0, 0),
('/Library/javascript_plugins/jquery.include/title', '', 0, 0, 4, 1, '/Library/views/jQueryScript/title', 'Загрузчик JS/CSS файлов', 0, 0, 0, 0, 0, 0, 0),
('/Library/javascript_plugins/title', '', 0, 0, 3, 1, '/Library/basic/Package/title', 'Плагины на JavaScript', 0, 0, 0, 0, 0, 0, 0),
('/Library/layouts', '', 0, 0, 2, 2, '/Library/basic/Package', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/layouts/Admin', '', 0, 0, 3, 2, '/Library/views/Focuser', 'Admin.tpl', 1, 1, 0, 0, 0, 0, 0),
('/Library/layouts/Admin/Programs', '', 0, 0, 4, 4, '/Library/views/AutoWidget', 'Programs.tpl', 1, 1, 0, 0, 0, 0, 0),
('/Library/layouts/Admin/Programs/description', '', 0, 0, 5, 2, '/Library/views/AutoWidget/description', 'Программами являются обозреватели и редакторы объектов. Выбираются автоматически по настройкам ассоциации на объекты', 0, 0, 0, 0, 0, 0, 0),
('/Library/layouts/Admin/Programs/res/jquery.ui.Programs', '', 0, 0, 6, 2, '/Library/javascript_plugins/jQueryAjaxWidget', 'jquery.ui.Programs.js', 0, 1, 0, 0, 0, 0, 0),
('/Library/layouts/Admin/Programs/switch_views', '', 0, 0, 5, 3, '/Library/views/AutoWidget/switch_views', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/layouts/Admin/Programs/switch_views/case_default', '', 0, 0, 6, 4, '/Library/views/SwitchCase', 'all', 0, 0, 0, 0, 0, 0, 0),
('/Library/layouts/Admin/Programs/switch_views/case_default/Add', '', 0, 0, 7, 5, '/Library/admin_widgets/Add', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/layouts/Admin/Programs/switch_views/case_default/Attribs', '', 0, 0, 7, 4, '/Library/admin_widgets/Attribs', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/layouts/Admin/Programs/switch_views/case_default/Delete', '', 0, 0, 7, 6, '/Library/admin_widgets/Delete', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/layouts/Admin/Programs/switch_views/case_default/Explorer', '', 0, 0, 7, 3, '/Library/admin_widgets/Explorer', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/layouts/Admin/Programs/title', '', 0, 0, 5, 1, '/Library/views/AutoWidget/title', 'Программы для работы с объектами', 0, 0, 0, 0, 0, 0, 0),
('/Library/layouts/Admin/ProgramsMenu', '', 0, 0, 4, 5, '/Library/views/Widget', 'ProgramsMenu.tpl', 1, 1, 0, 0, 0, 0, 0),
('/Library/layouts/Admin/ProgramsMenu/description', '', 0, 0, 5, 2, '/Library/views/Widget/description', 'Меню автоматически формируется в зависимости от отображаемого объекта и доступного для него программ', 0, 0, 0, 0, 0, 0, 0),
('/Library/layouts/Admin/ProgramsMenu/programs', '', 0, 0, 5, 4, '/Library/layouts/Admin/Programs', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/layouts/Admin/ProgramsMenu/res/jquery.ui.ProgramsMenu', '', 0, 0, 6, 2, '/Library/javascript_plugins/jQueryAjaxWidget', 'jquery.ui.ProgramsMenu.js', 0, 1, 0, 0, 0, 0, 0),
('/Library/layouts/Admin/ProgramsMenu/res/style', '', 0, 0, 6, 1, '/Library/views/Css', 'style.css', 0, 1, 0, 0, 0, 0, 0),
('/Library/layouts/Admin/ProgramsMenu/title', '', 0, 0, 5, 1, '/Library/views/Widget/title', 'Меню программ', 0, 0, 0, 0, 0, 0, 0),
('/Library/layouts/Admin/res/jquery.ui.Admin', '', 0, 0, 5, 2, '/Library/javascript_plugins/jQueryAjaxWidget', 'jquery.ui.Admin.js', 0, 1, 0, 0, 0, 0, 0),
('/Library/layouts/Admin/res/jquery.ui.Admin/depends/history_api', '', 0, 0, 7, 4, '/Library/javascript_plugins/HistoryAPI', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/layouts/Admin/res/style', '', 0, 0, 5, 1, '/Library/views/Css', 'style.css', 0, 1, 0, 0, 0, 0, 0),
('/Library/layouts/boolive', '', 0, 0, 3, 1, '/Library/views/Focuser', 'boolive.tpl', 1, 1, 0, 0, 0, 0, 0),
('/Library/layouts/boolive/bottom', '', 0, 0, 4, 1, '/Library/views/ViewGroup', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/layouts/boolive/center', '', 0, 0, 4, 1, '/Library/views/ViewGroup', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/layouts/boolive/center/Content', '', 0, 0, 5, 1, '/Library/content_widgets/Content', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/layouts/boolive/description', '', 0, 0, 4, 2, '/Library/views/Focuser/description', 'Макет сайта boolive.ru', 0, 0, 0, 0, 0, 0, 0),
('/Library/layouts/boolive/head', '', 0, 0, 4, 1, '/Library/views/ViewGroup', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/layouts/boolive/head/logo', '', 0, 0, 5, 1, '/Library/views/Logo', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/layouts/boolive/res/style', '', 0, 0, 5, 1, '/Library/views/Css', 'style.css', 0, 1, 0, 0, 0, 0, 0),
('/Library/layouts/boolive/sidebar', '', 0, 0, 4, 1, '/Library/views/ViewGroup', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/layouts/boolive/sidebar/menu', '', 0, 0, 5, 1, '/Library/views/Menu', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/layouts/boolive/sidebar/menu/objects/news/title', '', 0, 0, 8, 1, NULL, 'Новости!!', 0, 0, 0, 0, 0, 0, 0),
('/Library/layouts/boolive/sidebar/menu/res/style', '', 0, 0, 7, 1, '/Library/views/Css', 'style.css', 0, 1, 0, 0, 0, 0, 0),
('/Library/layouts/boolive/sidebar/menu/title', '', 0, 0, 6, 1, '/Library/basic/widgets/Menu/title', 'Заголовок меню', 0, 0, 0, 0, 0, 0, 0),
('/Library/layouts/boolive/title', '', 0, 0, 4, 1, '/Library/views/Focuser/title', 'Boolive!', 0, 0, 0, 0, 0, 0, 0),
('/Library/layouts/boolive/top', '', 0, 0, 4, 1, '/Library/views/ViewGroup', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/layouts/boolive/top/menu', '', 0, 0, 5, 1, '/Library/views/Menu', 'menu.tpl', 0, 1, 0, 0, 0, 0, 0),
('/Library/layouts/boolive/top/menu/object/contacts', '', 0, 0, 7, 3, '/Contents/contacts', NULL, 0, 0, 0, 0, 0, 1, 1),
('/Library/layouts/boolive/top/menu/object/main', '', 0, 0, 7, 1, '/Contents/main', NULL, 0, 0, 0, 0, 0, 1, 1),
('/Library/layouts/boolive/top/menu/object/news', '', 0, 0, 7, 2, '/Contents/news', NULL, 0, 0, 0, 0, 0, 1, 1),
('/Library/layouts/boolive/top/menu/res/style', '', 0, 0, 7, 3, '/Library/views/Css', 'style.css', 0, 1, 0, 0, 0, 0, 0),
('/Library/title', '', 0, 0, 2, 1, NULL, 'Библиотека', 0, 0, 0, 0, 0, 0, 0),
('/Library/views', '', 0, 0, 2, 1, '/Library/basic/Package', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/views/AutoWidget', '', 0, 0, 3, 9, '/Library/views/Widget', 'AutoWidget.tpl', 1, 1, 0, 0, 0, 0, 0),
('/Library/views/AutoWidget/description', '', 0, 0, 4, 2, '/Library/views/Widget/description', 'Отображает любой объект в соответсвии с установленными вараинтами', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/AutoWidget/switch_views', '', 0, 0, 4, 3, '/Library/views/SwitchViews', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/views/AutoWidget/switch_views/title', '', 0, 0, 5, 1, '/Library/views/SwitchViews/title', 'Варианты отображения', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/AutoWidget/title', '', 0, 0, 4, 1, '/Library/views/Widget/title', 'Автоматический виджет', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/AutoWidgetList', '', 0, 0, 3, 10, '/Library/views/Widget', 'AutoWidgetList.tpl', 1, 1, 0, 0, 0, 0, 0),
('/Library/views/AutoWidgetList/description', '', 0, 0, 4, 2, '/Library/views/Widget/description', 'Отображает все свойства объекта в соответсвии с установленными вараинтами отображения. Имеет настройки фильтра, какие свойства объекта отображать.', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/AutoWidgetList/switch_views', '', 0, 0, 4, 3, '/Library/views/SwitchViews', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/views/AutoWidgetList/switch_views/title', '', 0, 0, 5, 1, '/Library/views/SwitchViews/title', 'Варианты отображения', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/AutoWidgetList/title', '', 0, 0, 4, 1, '/Library/views/Widget/title', 'Автоматический список виджетов', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/Css', '', 0, 0, 3, 3, '/Library/views/View', NULL, 1, 0, 0, 0, 0, 0, 0),
('/Library/views/Css/description', '', 0, 0, 4, 2, '/Library/views/View/description', 'Каскадная таблица стилей для оформления HTML-документа', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/Css/title', '', 0, 0, 4, 1, '/Library/views/View/title', 'CSS', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/DirectHandler', '', 0, 0, 3, 17, '/Library/views/View', NULL, 1, 0, 0, 0, 0, 0, 0),
('/Library/views/DirectHandler/description', '', 0, 0, 4, 2, '/Library/views/View/description', 'По запросу клиента запускается требуемое представление и возвращается результат работы в JSON. Используется для обновления частей страницы без полной их перегрузки, а также для обработки форм и выполнения иных действий со стороны клиента.', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/DirectHandler/title', '', 0, 0, 4, 1, '/Library/views/View/title', 'Обработчик направленных запросов', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/Focuser', '', 0, 0, 3, 12, '/Library/views/Widget', NULL, 1, 0, 0, 0, 0, 0, 0),
('/Library/views/Focuser/description', '', 0, 0, 4, 2, '/Library/views/Widget/description', 'По URL запроса определяет объект и номер страницы для последующего оперирования ими подчиненными виджетами. Найденный объект и номер страницы помещаются во входящие данные для подчиненных виджетов. Может использоваться для макета сайта.', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/Focuser/title', '', 0, 0, 4, 1, '/Library/views/Widget/title', 'Фокусировщик', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/FormField', '', 0, 0, 3, 18, '/Library/views/Widget', 'FormField.tpl', 1, 1, 0, 0, 0, 0, 0),
('/Library/views/FormField/title', '', 0, 0, 4, 1, '/Library/views/Widget/title', 'Поле формы', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/Html', '', 0, 0, 3, 11, '/Library/views/Widget', 'Html.tpl', 1, 1, 0, 0, 0, 0, 0),
('/Library/views/Html/body', '', 0, 0, 4, 3, '/Library/views/ViewGroup', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/views/Html/body/title', '', 0, 0, 5, 1, '/Library/views/ViewGroup/title', 'Основная область вставки', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/Html/description', '', 0, 0, 4, 2, '/Library/views/Widget/description', 'Определяет заголовки HTML документа, подключая необходимые теги в <HEAD>, требуемые виджетами', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/Html/title', '', 0, 0, 4, 1, '/Library/views/Widget/title', 'HTML разметка', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/JavaScript', '', 0, 0, 3, 4, '/Library/views/View', NULL, 1, 0, 0, 0, 0, 0, 0),
('/Library/views/JavaScript/depends', '', 0, 0, 4, 3, '/Library/views/ViewGroup', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/views/JavaScript/depends/description', '', 0, 0, 5, 2, '/Library/views/ViewGroup/description', 'Скрипты и любые другие объекты, необходиыме для работы', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/JavaScript/depends/title', '', 0, 0, 5, 1, '/Library/views/ViewGroup/title', 'Используемые скрипты', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/JavaScript/description', '', 0, 0, 4, 2, '/Library/views/View/description', 'Клиентский скрипт на языке JavaScript', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/JavaScript/title', '', 0, 0, 4, 1, '/Library/views/View/title', 'JavaScript', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/Logo', '', 0, 0, 3, 14, '/Library/views/Widget', 'Logo.tpl', 1, 1, 0, 0, 0, 0, 0),
('/Library/views/Logo/object', '', 0, 0, 4, 2, '/Library/content_samples/Image', 'object.png', 0, 1, 0, 0, 0, 0, 0),
('/Library/views/Logo/title', '', 0, 0, 4, 1, '/Library/views/Widget/title', 'Логотип', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/Logo/use_object', '', 0, 0, 4, 1, NULL, '1', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/Menu', '', 0, 0, 3, 13, '/Library/views/Widget', 'Menu.tpl', 1, 1, 0, 0, 0, 0, 0),
('/Library/views/Menu/description', '', 0, 0, 4, 2, '/Library/views/View/description', 'Навигационное меню по сайту. Пункт меню может указывать на любой объект, если есть соответсвующий виджет для отображения пукта', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/Menu/object', '', 0, 0, 4, 3, '/Contents', NULL, 0, 0, 0, 0, 0, 1, 0),
('/Library/views/Menu/title', '', 0, 0, 4, 1, '/Library/views/Widget/title', 'Меню', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/Menu/view', '', 0, 0, 4, 4, '/Library/views/AutoWidgetList', 'view.tpl', 1, 1, 0, 0, 0, 0, 0),
('/Library/views/Menu/view/switch_views', '', 0, 0, 5, 3, '/Library/views/AutoWidgetList/switch_views', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/views/Menu/view/switch_views/case_page', '', 0, 0, 6, 1, '/Library/views/SwitchCase', '/Library/content_samples/Page', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/Menu/view/switch_views/case_page/ItemPage', '', 0, 0, 7, 1, '/Library/views/Menu/view', 'ItemPage.tpl', 1, 1, 0, 0, 0, 0, 0),
('/Library/views/Menu/view/switch_views/case_page/ItemPage/title', '', 0, 0, 8, 1, '/Library/views/Menu/view/title', 'Пункт меню страницы', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/Menu/view/switch_views/case_part', '', 0, 0, 6, 2, '/Library/views/SwitchCase', '/Library/content_samples/Part', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/Menu/view/switch_views/case_part/ItemPart', '', 0, 0, 7, 1, '/Library/views/Menu/view/switch_views/case_page/ItemPage', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/views/Menu/view/switch_views/case_part/ItemPart/title', '', 0, 0, 8, 1, '/Library/views/Menu/view/switch_views/case_page/ItemPage/title', 'Пункт меню раздела', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/PageNavigation', '', 0, 0, 3, 15, '/Library/views/Widget', 'PageNavigation.tpl', 1, 1, 0, 0, 0, 0, 0),
('/Library/views/PageNavigation/description', '', 0, 0, 4, 2, '/Library/views/Widget/description', 'Используется при отображении списков с постраничным разделением вывода', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/PageNavigation/res/style', '', 0, 0, 5, 1, '/Library/views/Css', 'style.css', 0, 1, 0, 0, 0, 0, 0),
('/Library/views/PageNavigation/title', '', 0, 0, 4, 1, '/Library/views/Widget/title', 'Меню постраничной навигации', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/SwitchCase', '', 0, 0, 3, 8, '/Library/views/Widget', NULL, 1, 0, 0, 0, 0, 0, 0),
('/Library/views/SwitchCase/description', '', 0, 0, 4, 2, '/Library/views/Widget/description', 'Используется в виджете-переключателе для автоматического выбора варианта по uri отображаемого объекта.', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/SwitchCase/title', '', 0, 0, 4, 1, '/Library/views/Widget/title', 'Вариант переключателя', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/SwitchViews', '', 0, 0, 3, 7, '/Library/views/Widget', 'SwitchViews.tpl', 1, 1, 0, 0, 0, 0, 0),
('/Library/views/SwitchViews/description', '', 0, 0, 4, 2, '/Library/views/Widget/description', 'Содержит варианты, значения которых - условие исполнения. Условие исполнение - это uri отображаемого объекта или uri прототипов отображаемого объекта. Может оказаться несколько вариантов с выполняемым условием, но выбирается только первый вариант. ', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/SwitchViews/title', '', 0, 0, 4, 1, '/Library/views/Widget/title', 'Переключатель вариантов отображения (исполнения)', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/View', '', 0, 0, 3, 1, NULL, NULL, 1, 0, 0, 0, 0, 0, 0),
('/Library/views/View/description', '', 0, 0, 4, 2, NULL, 'Базовый объект для создания элементов интерфейса', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/View/title', '', 0, 0, 4, 1, NULL, 'Вид', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/ViewGroup', '', 0, 0, 3, 2, '/Library/views/View', NULL, 1, 0, 0, 0, 0, 0, 0),
('/Library/views/ViewGroup/description', '', 0, 0, 4, 2, '/Library/views/View/description', 'Автоматичеки исполненяет и отображает все подчиенные объекты', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/ViewGroup/title', '', 0, 0, 4, 1, '/Library/views/View/title', 'Группа видов', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/Widget', '', 0, 0, 3, 6, '/Library/views/View', NULL, 1, 0, 0, 0, 0, 0, 0),
('/Library/views/Widget/description', '', 0, 0, 4, 2, '/Library/views/View/description', 'Формирует результат работы спомощью шаблонизации. Шаблоном является значение виджета', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/Widget/object', '', 0, 0, 4, 4, NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/views/Widget/object/description', '', 0, 0, 5, 2, NULL, 'Объект, который отображется и с кторым выполняются действия', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/Widget/object/title', '', 0, 0, 5, 1, NULL, 'Объект', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/Widget/res', '', 0, 0, 4, 3, '/Library/views/ViewGroup', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library/views/Widget/res/description', '', 0, 0, 5, 2, '/Library/views/ViewGroup/description', 'Автоматически подключаемые ресурсы, например CSS, JavaScript', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/Widget/res/title', '', 0, 0, 5, 1, '/Library/views/ViewGroup/title', 'Ресурсы', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/Widget/title', '', 0, 0, 4, 1, '/Library/views/View/title', 'Виджет', 0, 0, 0, 0, 0, 1, 0),
('/Library/views/jQueryScript', '', 0, 0, 3, 5, '/Library/views/JavaScript', NULL, 1, 0, 0, 0, 0, 0, 0),
('/Library/views/jQueryScript/depends/jquery-1.8.1.min', '', 0, 0, 5, 1, '/Library/views/JavaScript', 'jquery-1.8.1.min.js', 0, 1, 0, 0, 0, 0, 0),
('/Library/views/jQueryScript/depends/jquery-1.8.1.min/description', '', 0, 0, 6, 2, '/Library/views/JavaScript/description', 'JavaScript-библиотека для взаимодействия с HTML', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/jQueryScript/depends/jquery-1.8.1.min/title', '', 0, 0, 6, 1, '/Library/views/JavaScript/title', 'jQuery', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/jQueryScript/description', '', 0, 0, 4, 2, '/Library/views/JavaScript/description', 'JavaScript использующий библиотеку jQuery. Также применяется для создания плагинов для jQuery', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/jQueryScript/title', '', 0, 0, 4, 1, '/Library/views/JavaScript/title', 'jQuery скрипт', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/jQueryUIScript', '', 0, 0, 3, 16, '/Library/views/jQueryScript', NULL, 1, 0, 0, 0, 0, 0, 0),
('/Library/views/jQueryUIScript/depends/jquery-ui-1.8.23.min', '', 0, 0, 5, 1, '/Library/views/JavaScript', 'jquery-ui-1.8.23.min.js', 0, 1, 0, 0, 0, 0, 0),
('/Library/views/jQueryUIScript/depends/jquery-ui-1.8.23.min/description', '', 0, 0, 6, 2, '/Library/views/JavaScript/description', 'JavaScript-библиотека для пользовательского интерфейса на основе jQuery', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/jQueryUIScript/depends/jquery-ui-1.8.23.min/title', '', 0, 0, 6, 1, '/Library/views/JavaScript/title', 'jQuery UI', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/jQueryUIScript/description', '', 0, 0, 4, 2, '/Library/views/jQueryScript/description', 'JavaScript использующий библиотеку jQueryUI и jQuery. Также применяется для создания плагинов для jQueryUI', 0, 0, 0, 0, 0, 0, 0),
('/Library/views/jQueryUIScript/title', '', 0, 0, 4, 1, '/Library/views/jQueryScript/title', 'jQuery UI скрипт', 0, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `members`
--

CREATE TABLE IF NOT EXISTS `members` (
  `uri` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT 'Унифицированный идентификатор (путь на объект)',
  `lang` char(3) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT 'Код языка по ISO 639-3',
  `owner` int(11) NOT NULL DEFAULT '0' COMMENT 'Владелец',
  `date` int(11) NOT NULL COMMENT 'Дата создания объекта',
  `level` int(11) NOT NULL DEFAULT '1' COMMENT 'Уровень вложенности относительно корня',
  `order` int(11) NOT NULL DEFAULT '1' COMMENT 'Порядковый номер',
  `proto` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'uri прототипа',
  `value` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Значение',
  `is_logic` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, есть ли класс у объекта',
  `is_file` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, Является ли объект файлом',
  `is_history` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, является ли запись историей',
  `is_delete` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, удален объект или нет',
  `is_hidden` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, скрытый объект или нет',
  `is_link` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, является ли объект ссылкой',
  `override` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uri`,`lang`,`owner`,`date`),
  KEY `orders` (`level`,`order`),
  KEY `state` (`is_history`,`is_delete`,`is_hidden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Дамп данных таблицы `members`
--

INSERT INTO `members` (`uri`, `lang`, `owner`, `date`, `level`, `order`, `proto`, `value`, `is_logic`, `is_file`, `is_history`, `is_delete`, `is_hidden`, `is_link`, `override`) VALUES
('/Members/guests', '', 0, 0, 2, 1, '/Library/basic/members/UserGroup', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Members/guests/title', '', 0, 0, 3, 1, '/Library/basic/members/UserGroup/title', 'Гости', 0, 0, 0, 0, 0, 0, 0),
('/Members/registered', '', 0, 0, 2, 2, '/Library/basic/members/UserGroup', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Members/registered/admins', '', 0, 0, 3, 2, '/Library/basic/members/UserGroup', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Members/registered/admins/title', '', 0, 0, 4, 1, '/Library/basic/members/UserGroup/title', 'Администраторы', 0, 0, 0, 0, 0, 0, 0),
('/Members/registered/admins/vova', '', 0, 0, 4, 2, '/Library/basic/members/User', 'password_hash', 0, 0, 0, 0, 0, 0, 0),
('/Members/registered/admins/vova/email', '', 0, 0, 5, 2, '/Library/basic/members/User/email', 'boolive@yandex.ru', 0, 0, 0, 0, 0, 0, 0),
('/Members/registered/admins/vova/name', '', 0, 0, 5, 1, '/Library/basic/members/User/name', 'Вова', 0, 0, 0, 0, 0, 0, 0),
('/Members/registered/title', '', 0, 0, 3, 1, '/Library/basic/members/UserGroup/title', 'Зарегистрированные', 0, 0, 0, 0, 0, 0, 0),
('/Members/title', '', 0, 0, 2, 1, NULL, 'Пользователи и группы', 0, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `site`
--

CREATE TABLE IF NOT EXISTS `site` (
  `uri` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT 'Унифицированный идентификатор (путь на объект)',
  `lang` char(3) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT 'Код языка по ISO 639-3',
  `owner` int(11) NOT NULL DEFAULT '0' COMMENT 'Владелец',
  `date` int(11) NOT NULL COMMENT 'Дата создания объекта',
  `level` int(11) NOT NULL DEFAULT '1' COMMENT 'Уровень вложенности относительно корня',
  `order` int(11) NOT NULL DEFAULT '1' COMMENT 'Порядковый номер',
  `proto` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'uri прототипа',
  `value` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT 'Значение',
  `is_logic` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, есть ли класс у объекта',
  `is_file` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, Является ли объект файлом',
  `is_history` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, является ли запись историей',
  `is_delete` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, удален объект или нет',
  `is_hidden` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, скрытый объект или нет',
  `is_link` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак, является ли объект ссылкой',
  `override` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uri`,`lang`,`owner`,`date`),
  KEY `orders` (`level`,`order`),
  KEY `state` (`is_history`,`is_delete`,`is_hidden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Дамп данных таблицы `site`
--

INSERT INTO `site` (`uri`, `lang`, `owner`, `date`, `level`, `order`, `proto`, `value`, `is_logic`, `is_file`, `is_history`, `is_delete`, `is_hidden`, `is_link`, `override`) VALUES
('', '0', 0, 1349348313, 0, 1, NULL, '1', 0, 0, 0, 0, 0, 0, 0),
('/Contents', '', 0, 1342077181, 1, 1, NULL, 'Contents.png', 0, 1, 0, 0, 0, 0, 0),
('/Interfaces', '', 0, 1342082233, 1, 2, '/Library/views/ViewGroup', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Keywords', '', 0, 1342077181, 1, 2, NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
('/Library', '', 0, 1342077181, 1, 3, NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
('/Members', '', 0, 1342077181, 1, 2, '/Library/basic/members/UserGroup', NULL, 0, 0, 0, 0, 0, 0, 0),
('/title', '', 0, 0, 1, 2, NULL, 'Сайт', 0, 0, 0, 0, 0, 0, 0);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;