-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Окт 07 2012 г., 14:33
-- Версия сервера: 5.00.15
-- Версия PHP: 5.3.9

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- База данных: `boolive-git`
--

-- --------------------------------------------------------

--
-- Структура таблицы `contents`
--

CREATE TABLE IF NOT EXISTS `contents` (
  `uri` varchar(255) collate utf8_bin NOT NULL default '' COMMENT 'Унифицированный идентификатор (путь на объект)',
  `lang` char(3) collate utf8_bin NOT NULL default '' COMMENT 'Код языка по ISO 639-3',
  `owner` int(11) NOT NULL default '0' COMMENT 'Владелец',
  `date` int(11) NOT NULL COMMENT 'Дата создания объекта',
  `level` int(11) NOT NULL default '1' COMMENT 'Уровень вложенности относительно корня',
  `order` int(11) NOT NULL default '1' COMMENT 'Порядковый номер',
  `proto` varchar(255) collate utf8_bin default NULL COMMENT 'uri прототипа',
  `value` varchar(255) collate utf8_bin default NULL COMMENT 'Значение',
  `is_logic` tinyint(4) NOT NULL default '0' COMMENT 'Признак, есть ли класс у объекта',
  `is_file` tinyint(4) NOT NULL default '0' COMMENT 'Признак, Является ли объект файлом',
  `is_history` tinyint(4) NOT NULL default '0' COMMENT 'Признак, является ли запись историей',
  `is_delete` tinyint(4) NOT NULL default '0' COMMENT 'Признак, удален объект или нет',
  `is_hidden` tinyint(4) NOT NULL default '0' COMMENT 'Признак, скрытый объект или нет',
  `is_link` tinyint(4) NOT NULL default '0' COMMENT 'Признак, является ли объект ссылкой',
  `override` tinyint(4) NOT NULL default '0' COMMENT 'Признак не использовать свойства прототипа',
  PRIMARY KEY  (`uri`,`lang`,`owner`,`date`),
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
('/Contents/contacts/text/head1', '', 0, 0, 4, 0, '/Library/content_samples/Head', 'Наш адрес!', 0, 0, 0, 0, 0, 0, 0),
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
('/Contents/main/text', '', 0, 0, 3, 2, '/Library/content_samples/Page/text', '', 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/text/head1', '', 0, 0, 4, 2, '/Library/content_samples/Head', 'Заголовок главной страницы', 0, 0, 0, 0, 0, 0, 0),
('/Contents/main/text/img1', '', 0, 0, 4, 3, '/Library/content_samples/Image', 'img1.jpg', 0, 1, 0, 0, 0, 0, 0),
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
('/Contents/news/news1', '', 0, 0, 3, 3, '/Library/content_samples/Page', '', 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/news1/author', '', 0, 0, 4, 3, '/Members/registered/admins/vova', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/news1/text', '', 0, 0, 4, 2, '/Library/content_samples/Page/text', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/news1/text/p1', '', 0, 0, 5, 1, '/Library/content_samples/Paragraph', 'Текст новости в виде одного абзаца', 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/news1/title', '', 0, 0, 4, 1, '/Library/content_samples/Page/title', 'Первая новость', 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/news2', '', 0, 0, 3, 4, '/Library/content_samples/Page', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/news2/author', '', 0, 0, 4, 3, '/Members/registered/admins/vova', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/news2/text', '', 0, 0, 4, 2, '/Library/content_samples/Page/text', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/news2/text/p1', '', 0, 0, 5, 1, '/Library/content_samples/Paragraph', 'Ноовсть создаётся как страница, то есть новость и есть страницой, просто она помещена в раздел Ленты новостей', 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/news2/title', '', 0, 0, 4, 1, '/Library/content_samples/Page/title', 'Вторая новость', 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/news3', '', 0, 0, 3, 5, '/Library/content_samples/Page', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/news3/author', '', 0, 0, 4, 3, '/Members/registered/admins/vova', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/news3/text', '', 0, 0, 4, 2, '/Library/content_samples/Page/text', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/news3/text/p1', '', 0, 0, 5, 1, '/Library/content_samples/Paragraph', 'Текст новости в виде одного абзаца', 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/news3/title', '', 0, 0, 4, 1, '/Library/content_samples/Page/title', 'Третья новость', 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/news4', '', 0, 0, 3, 6, '/Library/content_samples/Page', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/news4/author', '', 0, 0, 4, 3, '/Members/registered/admins/vova', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/news4/text', '', 0, 0, 4, 2, '/Library/content_samples/Page/text', NULL, 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/news4/text/p1', '', 0, 0, 5, 1, '/Library/content_samples/Paragraph', 'Текст новости в виде одного абзаца', 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/news4/title', '', 0, 0, 4, 1, '/Library/content_samples/Page/title', 'Четвертая новость', 0, 0, 0, 0, 0, 0, 0),
('/Contents/news/title', '', 0, 0, 3, 1, '/Library/content_samples/Part/title', 'Лента новостей', 0, 0, 0, 0, 0, 0, 0),
('/Contents/title', '', 0, 0, 2, 1, NULL, 'Содержимое', 0, 0, 0, 0, 1, 0, 0);
