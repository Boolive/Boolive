-- phpMyAdmin SQL Dump
-- version 2.9.2
-- http://www.phpmyadmin.net
-- 
-- Хост: localhost
-- Время создания: Авг 21 2012 г., 11:23
-- Версия сервера: 5.0.22
-- Версия PHP: 5.3.9
-- 
-- База данных: `boolive-git`
-- 

-- --------------------------------------------------------

-- 
-- Структура таблицы `contents`
-- 

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Дамп данных таблицы `contents`
-- 

INSERT INTO `contents` (`uri`, `lang`, `owner`, `date`, `level`, `order`, `proto`, `value`, `is_logic`, `is_file`, `is_history`, `is_delete`, `is_hidden`) VALUES 
('/Contents/contacts', '', 0, 0, 3, 2, '/Library/basic/contents/Page', NULL, 0, 0, 0, 0, 0),
('/Contents/contacts/text', '', 0, 0, 4, 2, '/Library/basic/contents/Page/text', NULL, 0, 0, 0, 0, 0),
('/Contents/contacts/text/feedback', '', 0, 0, 5, 3, '/Library/basic/contents/Feedback', NULL, 0, 0, 0, 0, 0),
('/Contents/contacts/text/feedback/email_from', '', 0, 0, 6, 1, '/Library/basic/contents/Feedback/email_from', '', 0, 0, 0, 0, 0),
('/Contents/contacts/text/feedback/email_to', '', 0, 0, 6, 2, '/Library/basic/contents/Feedback/email_to', 'info@boolive.ru', 0, 0, 0, 0, 0),
('/Contents/contacts/text/feedback/message', '', 0, 0, 6, 3, '/Library/basic/contents/Feedback/mesage', '', 0, 0, 0, 0, 0),
('/Contents/contacts/text/head1', '', 0, 0, 5, 0, '/Library/basic/contents/Head', 'Наш адрес', 0, 0, 0, 0, 0),
('/Contents/contacts/text/p1', '', 0, 0, 5, 1, '/Library/basic/contents/Paragraph', 'г. Екатеринбург, ул Ленина, дом 1, офис 999', 0, 0, 0, 0, 0),
('/Contents/contacts/text/p2', '', 0, 0, 5, 2, '/Library/basic/contents/Paragraph', 'Работаем груглосуточно', 0, 0, 0, 0, 0),
('/Contents/contacts/title', '', 0, 0, 4, 1, '/Library/basic/contents/Page/title', 'Контакты', 0, 0, 0, 0, 0),
('/Contents/main', '', 0, 0, 3, 1, '/Library/basic/contents/Page', NULL, 0, 0, 0, 0, 0),
('/Contents/main/author', '', 0, 0, 4, 4, '/Members/registered/admins/vova', NULL, 0, 0, 0, 0, 0),
('/Contents/main/comments', '', 0, 0, 4, 6, '/Library/basic/contents/Page/comments', NULL, 0, 0, 0, 0, 0),
('/Contents/main/comments/comment1', '', 0, 0, 5, 1, '/Library/basic/contents/Comment', NULL, 0, 0, 0, 0, 0),
('/Contents/main/comments/comment1/author', '', 0, 0, 6, 1, '/Members/registered/admins/vova', NULL, 0, 0, 0, 0, 0),
('/Contents/main/comments/comment1/comment11', '', 0, 0, 6, 3, '/Library/basic/contents/Comment', NULL, 0, 0, 0, 0, 0),
('/Contents/main/comments/comment1/comment11/author', '', 0, 0, 7, 1, '/Members/registered/admins/vova', NULL, 0, 0, 0, 0, 0),
('/Contents/main/comments/comment1/comment11/text', '', 0, 0, 7, 2, '/Library/basic/contents/Comment/text', 'Комментарий на первый комментарий', 0, 0, 0, 0, 0),
('/Contents/main/comments/comment1/text', '', 0, 0, 6, 2, '/Library/basic/contents/Comment/text', 'Текст первого коммента', 0, 0, 0, 0, 0),
('/Contents/main/comments/comment2', '', 0, 0, 5, 2, '/Library/basic/contents/Comment', NULL, 0, 0, 0, 0, 0),
('/Contents/main/comments/comment2/author', '', 0, 0, 6, 1, '/Members/registered/admins/vova', NULL, 0, 0, 0, 0, 0),
('/Contents/main/comments/comment2/text', '', 0, 0, 6, 2, '/Library/basic/contents/Comment/text', 'Текст второго комментария к главной странице сайта', 0, 0, 0, 0, 0),
('/Contents/main/description', '', 0, 0, 4, 3, '/Library/basic/contents/Page/description', 'Главная страница первого простейшего сайта', 0, 0, 0, 0, 0),
('/Contents/main/keywords', '', 0, 0, 4, 5, '/Library/basic/contents/Page/keywords', NULL, 0, 0, 0, 0, 0),
('/Contents/main/keywords/cms', '', 0, 0, 5, 1, '/Keywords/cms', NULL, 0, 0, 0, 0, 0),
('/Contents/main/keywords/php', '', 0, 0, 5, 2, '/Keywords/php', NULL, 0, 0, 0, 0, 0),
('/Contents/main/sub_page', '', 0, 0, 4, 7, '/Library/basic/contents/Page', NULL, 0, 0, 0, 0, 0),
('/Contents/main/sub_page/author', '', 0, 0, 5, 3, '/Members/registered/admins/vova', NULL, 0, 0, 0, 0, 0),
('/Contents/main/sub_page/description', '', 0, 0, 5, 2, '/Library/basic/contents/Page/description', 'Подчиненная страница. Это её короткое описание для SEO', 0, 0, 0, 0, 0),
('/Contents/main/sub_page/text', '', 0, 0, 5, 1, '/Library/basic/contents/Page/text', NULL, 0, 0, 0, 0, 0),
('/Contents/main/sub_page/text/head1', '', 0, 0, 6, 0, '/Library/basic/contents/Head', 'Заголовок подчиненной страницы', 0, 0, 0, 0, 0),
('/Contents/main/sub_page/text/head2', '', 0, 0, 6, 2, '/Library/basic/contents/Head', 'Подзаголовок подчиненной страницы', 0, 0, 0, 0, 0),
('/Contents/main/sub_page/text/img1', '', 0, 0, 6, 4, '/Library/basic/contents/Image', 'img1.jpg', 0, 1, 0, 0, 0),
('/Contents/main/sub_page/text/p1', '', 0, 0, 6, 1, '/Library/basic/contents/Paragraph', 'Первый абзац подчиенной страницы... текст... текст...', 0, 0, 0, 0, 0),
('/Contents/main/sub_page/text/p2', '', 0, 0, 6, 3, '/Library/basic/contents/Paragraph', 'Второй абзац подчиенной страницы... текст... текст...', 0, 0, 0, 0, 0),
('/Contents/main/sub_page/title', '', 0, 0, 5, 0, '/Library/basic/contents/Page/title', 'Подстраница', 0, 0, 0, 0, 0),
('/Contents/main/text', '', 0, 0, 4, 2, '/Library/basic/contents/Page/text', NULL, 0, 0, 0, 0, 0),
('/Contents/main/text/head1', '', 0, 0, 5, 1, '/Library/basic/contents/Head', 'Заголовок главной страницы', 0, 0, 0, 0, 0),
('/Contents/main/text/img1', '', 0, 0, 5, 3, '/Library/basic/contents/Image', 'img1.jpg', 0, 1, 0, 0, 0),
('/Contents/main/text/p1', '', 0, 0, 5, 2, '/Library/basic/contents/Paragraph', 'Добро пожаловать на тестовый сайт. Сайт работает на новой системе Boolive 2', 0, 0, 0, 0, 0),
('/Contents/main/text/p2', '', 0, 0, 5, 4, '/Library/basic/contents/Paragraph', 'Hello World :)', 0, 0, 0, 0, 0),
('/Contents/main/title', '', 0, 0, 4, 1, '/Library/basic/contents/Page/title', 'Главная', 0, 0, 0, 0, 0),
('/Contents/news', '', 0, 0, 3, 3, '/Library/basic/contents/Part', NULL, 0, 0, 0, 0, 0),
('/Contents/news/news1', '', 0, 0, 4, 2, '/Library/basic/contents/Page', NULL, 0, 0, 0, 0, 0),
('/Contents/news/news1/author', '', 0, 0, 5, 3, '/Members/registered/admins/vova', NULL, 0, 0, 0, 0, 0),
('/Contents/news/news1/text', '', 0, 0, 5, 2, '/Library/basic/contents/Page/text', NULL, 0, 0, 0, 0, 0),
('/Contents/news/news1/text/p1', '', 0, 0, 6, 1, '/Library/basic/contents/Paragraph', 'Текст новости в виде одного абзаца', 0, 0, 0, 0, 0),
('/Contents/news/news1/title', '', 0, 0, 5, 1, '/Library/basic/contents/Page/title', 'Первая новость', 0, 0, 0, 0, 0),
('/Contents/news/news2', '', 0, 0, 4, 3, '/Library/basic/contents/Page', NULL, 0, 0, 0, 0, 0),
('/Contents/news/news2/author', '', 0, 0, 5, 3, '/Members/registered/admins/vova', NULL, 0, 0, 0, 0, 0),
('/Contents/news/news2/text', '', 0, 0, 5, 2, '/Library/basic/contents/Page/text', NULL, 0, 0, 0, 0, 0),
('/Contents/news/news2/text/p1', '', 0, 0, 6, 1, '/Library/basic/contents/Paragraph', 'Ноовсть создаётся как страница, то есть новость и есть страницой, просто она помещена в раздел Ленты новостей', 0, 0, 0, 0, 0),
('/Contents/news/news2/title', '', 0, 0, 5, 1, '/Library/basic/contents/Page/title', 'Вторая новость', 0, 0, 0, 0, 0),
('/Contents/news/news3', '', 0, 0, 4, 4, '/Library/basic/contents/Page', NULL, 0, 0, 0, 0, 0),
('/Contents/news/news3/author', '', 0, 0, 5, 3, '/Members/registered/admins/vova', NULL, 0, 0, 0, 0, 0),
('/Contents/news/news3/text', '', 0, 0, 5, 2, '/Library/basic/contents/Page/text', NULL, 0, 0, 0, 0, 0),
('/Contents/news/news3/text/p1', '', 0, 0, 6, 1, '/Library/basic/contents/Paragraph', 'Текст новости в виде одного абзаца', 0, 0, 0, 0, 0),
('/Contents/news/news3/title', '', 0, 0, 5, 1, '/Library/basic/contents/Page/title', 'Третья новость', 0, 0, 0, 0, 0),
('/Contents/news/news4', '', 0, 0, 4, 5, '/Library/basic/contents/Page', NULL, 0, 0, 0, 0, 0),
('/Contents/news/news4/author', '', 0, 0, 5, 3, '/Members/registered/admins/vova', NULL, 0, 0, 0, 0, 0),
('/Contents/news/news4/text', '', 0, 0, 5, 2, '/Library/basic/contents/Page/text', NULL, 0, 0, 0, 0, 0),
('/Contents/news/news4/text/p1', '', 0, 0, 6, 1, '/Library/basic/contents/Paragraph', 'Текст новости в виде одного абзаца', 0, 0, 0, 0, 0),
('/Contents/news/news4/title', '', 0, 0, 5, 1, '/Library/basic/contents/Page/title', 'Четвертая новость', 0, 0, 0, 0, 0),
('/Contents/news/title', '', 0, 0, 4, 1, '/Library/basic/contents/Part/title', 'Лента новостей', 0, 0, 0, 0, 0);

-- --------------------------------------------------------

-- 
-- Структура таблицы `interfaces`
-- 

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Дамп данных таблицы `interfaces`
-- 

INSERT INTO `interfaces` (`uri`, `lang`, `owner`, `date`, `level`, `order`, `proto`, `value`, `is_logic`, `is_file`, `is_history`, `is_delete`, `is_hidden`) VALUES 
('/Interfaces/html', '', 0, 0, 3, 1, '/Library/basic/interfaces/widgets/Html', NULL, 0, 0, 0, 0, 0),
('/Interfaces/html/body/boolive', '', 0, 0, 5, 2, '/Library/layouts/boolive', NULL, 0, 0, 0, 0, 0),
('/Interfaces/html/body/boolive/center/hello5', '', 0, 0, 5, 6, '/Interfaces/html/body/hello', NULL, 0, 0, 0, 1, 0),
('/Interfaces/html/body/hello', '', 0, 0, 5, 2, '/Library/basic/interfaces/widgets/Widget', 'hello.tpl', 0, 1, 0, 1, 0),
('/Interfaces/html/body/hello/jquery.hello', '', 0, 0, 6, 2, '/Library/basic/interfaces/javascripts/jQueryScript', 'jquery.hello.js', 0, 1, 0, 1, 0),
('/Interfaces/html/body/hello/style', '', 0, 0, 6, 1, '/Library/basic/interfaces/Css', 'style.css', 0, 1, 0, 1, 0),
('/Interfaces/html/body/hello2', '', 0, 0, 5, 3, '/Interfaces/html/body/hello', NULL, 0, 0, 0, 1, 0),
('/Interfaces/html/body/hello3', '', 0, 0, 5, 4, '/Interfaces/html/body/hello', NULL, 0, 0, 0, 1, 0),
('/Interfaces/html/body/hello4', '', 0, 0, 5, 5, '/Interfaces/html/body/hello', NULL, 0, 0, 0, 1, 0);

-- --------------------------------------------------------

-- 
-- Структура таблицы `keywords`
-- 

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Дамп данных таблицы `keywords`
-- 

INSERT INTO `keywords` (`uri`, `lang`, `owner`, `date`, `level`, `order`, `proto`, `value`, `is_logic`, `is_file`, `is_history`, `is_delete`, `is_hidden`) VALUES 
('/Keywords/cms', '', 0, 0, 3, 1, '/Library/basic/contents/Keyword', '1', 0, 0, 0, 0, 0),
('/Keywords/framework', '', 0, 0, 3, 1, '/Library/basic/contents/Keyword', '0', 0, 0, 0, 0, 0),
('/Keywords/php', '', 0, 0, 3, 1, '/Library/basic/contents/Keyword', '1', 0, 0, 0, 0, 0);

-- --------------------------------------------------------

-- 
-- Структура таблицы `library`
-- 

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

-- 
-- Дамп данных таблицы `library`
-- 

INSERT INTO `library` (`uri`, `lang`, `owner`, `date`, `level`, `order`, `proto`, `value`, `is_logic`, `is_file`, `is_history`, `is_delete`, `is_hidden`) VALUES 
('/Library/basic', '', 0, 0, 3, 1, '/Library/basic/Package', NULL, 0, 0, 0, 0, 0),
('/Library/basic/contents', '', 0, 0, 4, 1, '/Library/basic/Package', NULL, 0, 0, 0, 0, 0),
('/Library/basic/contents/Comment', '', 0, 0, 5, 1, NULL, NULL, 1, 0, 0, 0, 0),
('/Library/basic/contents/Comment/text', '', 0, 0, 6, 1, NULL, NULL, 0, 0, 0, 0, 0),
('/Library/basic/contents/Feedback', '', 0, 0, 5, 2, NULL, NULL, 0, 0, 0, 0, 0),
('/Library/basic/contents/Feedback/email_from', '', 0, 0, 6, 1, '/Library/basic/simple/Email', NULL, 0, 0, 0, 0, 0),
('/Library/basic/contents/Feedback/email_to', '', 0, 0, 6, 2, '/Library/basic/simple/Email', NULL, 0, 0, 0, 0, 0),
('/Library/basic/contents/Feedback/message', '', 0, 0, 6, 3, NULL, NULL, 0, 0, 0, 0, 0),
('/Library/basic/contents/Head', '', 0, 0, 5, 3, NULL, NULL, 0, 0, 0, 0, 0),
('/Library/basic/contents/Image', '', 0, 0, 5, 4, NULL, NULL, 1, 0, 0, 0, 0),
('/Library/basic/contents/Keyword', '', 0, 0, 5, 5, NULL, NULL, 1, 0, 0, 0, 0),
('/Library/basic/contents/lists', '', 0, 0, 5, 6, '/Library/basic/Package', NULL, 0, 0, 0, 0, 0),
('/Library/basic/contents/lists/Item', '', 0, 0, 6, 2, NULL, NULL, 0, 0, 0, 0, 0),
('/Library/basic/contents/lists/List', '', 0, 0, 6, 1, NULL, NULL, 0, 0, 0, 0, 0),
('/Library/basic/contents/Page', '', 0, 0, 5, 11, NULL, NULL, 1, 0, 0, 0, 0),
('/Library/basic/contents/Page/comments', '', 0, 0, 6, 4, NULL, NULL, 0, 0, 0, 0, 0),
('/Library/basic/contents/Page/description', '', 0, 0, 6, 2, NULL, NULL, 0, 0, 0, 0, 0),
('/Library/basic/contents/Page/keywords', '', 0, 0, 6, 3, NULL, NULL, 0, 0, 0, 0, 0),
('/Library/basic/contents/Page/text', '', 0, 0, 6, 1, '/Library/basic/contents/RichText', NULL, 0, 0, 0, 0, 0),
('/Library/basic/contents/Page/title', '', 0, 0, 6, 0, NULL, 'Страница', 0, 0, 0, 0, 0),
('/Library/basic/contents/Paragraph', '', 0, 0, 5, 8, NULL, NULL, 0, 0, 0, 0, 0),
('/Library/basic/contents/Part', '', 0, 0, 5, 10, NULL, NULL, 0, 0, 0, 0, 0),
('/Library/basic/contents/Part/title', '', 0, 0, 6, 1, NULL, 'Раздел', 0, 0, 0, 0, 0),
('/Library/basic/contents/RichText', '', 0, 0, 5, 9, NULL, NULL, 0, 0, 0, 0, 0),
('/Library/basic/contents/RichText/title', '', 0, 0, 6, 1, NULL, 'Форматированный текст', 0, 0, 0, 0, 0),
('/Library/basic/contents/tables', '', 0, 0, 5, 7, '/Library/basic/Package', NULL, 0, 0, 0, 0, 0),
('/Library/basic/contents/tables/Cell', '', 0, 0, 6, 3, NULL, NULL, 0, 0, 0, 0, 0),
('/Library/basic/contents/tables/Row', '', 0, 0, 6, 2, NULL, NULL, 0, 0, 0, 0, 0),
('/Library/basic/contents/tables/Table', '', 0, 0, 6, 1, NULL, NULL, 0, 0, 0, 0, 0),
('/Library/basic/interfaces', '', 0, 0, 4, 2, '/Library/basic/Package', NULL, 0, 0, 0, 0, 0),
('/Library/basic/interfaces/Css', '', 0, 0, 5, 1, NULL, NULL, 1, 0, 0, 0, 0),
('/Library/basic/interfaces/Group', '', 0, 0, 5, 2, NULL, NULL, 1, 0, 0, 0, 0),
('/Library/basic/interfaces/javascripts', '', 0, 0, 5, 3, '/Library/basic/Package', NULL, 0, 0, 0, 0, 0),
('/Library/basic/interfaces/javascripts/JavaScript', '', 0, 0, 6, 1, NULL, NULL, 1, 0, 0, 0, 0),
('/Library/basic/interfaces/javascripts/JavaScript/depends', '', 0, 0, 7, 1, '/Library/basic/interfaces/Group', NULL, 0, 0, 0, 0, 0),
('/Library/basic/interfaces/javascripts/jQuery', '', 0, 0, 6, 2, '/Library/basic/interfaces/javascripts/JavaScript', 'jQuery.js', 0, 1, 0, 0, 0),
('/Library/basic/interfaces/javascripts/jQueryScript', '', 0, 0, 6, 3, '/Library/basic/interfaces/javascripts/JavaScript', NULL, 1, 0, 0, 0, 0),
('/Library/basic/interfaces/javascripts/jQueryScript/depends', '', 0, 0, 7, 1, '/Library/basic/interfaces/javascripts/JavaScript/depends', NULL, 0, 0, 1, 1, 1),
('/Library/basic/interfaces/javascripts/jQueryScript/depends/jquery', '', 0, 0, 8, 1, '/Library/basic/interfaces/javascripts/jQuery', NULL, 0, 0, 0, 0, 0),
('/Library/basic/interfaces/widgets', '', 0, 0, 5, 4, '/Library/basic/Package', NULL, 0, 0, 0, 0, 0),
('/Library/basic/interfaces/widgets/Focuser', '', 0, 0, 6, 5, '/Library/basic/interfaces/widgets/Widget', NULL, 1, 0, 0, 0, 0),
('/Library/basic/interfaces/widgets/Focuser/description', '', 0, 0, 7, 2, '/Library/basic/interfaces/widgets/Widget/description', 'По URL запроса определяет объект и номер страницы для последующего оперирования ими подчиненными виджетами', 0, 0, 0, 0, 0),
('/Library/basic/interfaces/widgets/Focuser/title', '', 0, 0, 7, 1, '/Library/basic/interfaces/widgets/Widget/title', 'Фокусировщик', 0, 0, 0, 0, 0),
('/Library/basic/interfaces/widgets/Html', '', 0, 0, 6, 1, NULL, 'Html.tpl', 1, 1, 0, 0, 0),
('/Library/basic/interfaces/widgets/Html/body', '', 0, 0, 7, 1, '/Library/basic/interfaces/Group', NULL, 0, 0, 0, 0, 0),
('/Library/basic/interfaces/widgets/Logo', '', 0, 0, 6, 1, '/Library/basic/interfaces/widgets/Widget', 'Logo.tpl', 1, 1, 0, 0, 0),
('/Library/basic/interfaces/widgets/Logo/image', '', 0, 0, 7, 1, '/Library/basic/contents/Image', 'logo.png', 0, 1, 0, 0, 0),
('/Library/basic/interfaces/widgets/Menu', '', 0, 0, 6, 7, '/Library/basic/interfaces/widgets/Widget', 'Menu.tpl', 1, 1, 0, 0, 0),
('/Library/basic/interfaces/widgets/Menu/items', '', 0, 0, 7, 2, '/Contents', NULL, 0, 0, 0, 0, 0),
('/Library/basic/interfaces/widgets/Menu/style', '', 0, 0, 7, 1, '/Library/basic/interfaces/Css', 'style.css', 0, 1, 0, 0, 0),
('/Library/basic/interfaces/widgets/Menu/title', '', 0, 0, 7, 1, '/Library/basic/interfaces/widgets/Widget/title', 'Меню', 0, 0, 0, 0, 0),
('/Library/basic/interfaces/widgets/Menu/views', '', 0, 0, 7, 3, '/Library/basic/interfaces/widgets/ViewObjectsList', 'views.tpl', 1, 1, 0, 0, 0),
('/Library/basic/interfaces/widgets/Menu/views/cond_page', '', 0, 0, 8, 1, '/Library/basic/interfaces/widgets/Option', '/Library/basic/contents/Page', 0, 0, 0, 0, 0),
('/Library/basic/interfaces/widgets/Menu/views/cond_page/ItemPage', '', 0, 0, 9, 1, '/Library/basic/interfaces/widgets/Menu/views', 'ItemPage.tpl', 1, 1, 0, 0, 0),
('/Library/basic/interfaces/widgets/Menu/views/cond_part', '', 0, 0, 8, 2, '/Library/basic/interfaces/widgets/Option', '/Library/basic/contents/Part', 0, 0, 0, 0, 0),
('/Library/basic/interfaces/widgets/Menu/views/cond_part/ItemPart', '', 0, 0, 9, 1, '/Library/basic/interfaces/widgets/Menu/views/cond_page/ItemPage', NULL, 0, 0, 0, 0, 0),
('/Library/basic/interfaces/widgets/Option', '', 0, 0, 6, 6, NULL, NULL, 1, 0, 0, 0, 0),
('/Library/basic/interfaces/widgets/Option/description', '', 0, 0, 7, 2, NULL, 'Используется для организации вариантов отображений родительского виджета', 0, 0, 0, 1, 0),
('/Library/basic/interfaces/widgets/Option/title', '', 0, 0, 7, 1, NULL, 'Вариант отображения', 0, 0, 0, 1, 0),
('/Library/basic/interfaces/widgets/PageNavigation', '', 0, 0, 6, 7, '/Library/basic/interfaces/widgets/Widget', 'PageNavigation.tpl', 1, 1, 0, 0, 0),
('/Library/basic/interfaces/widgets/PageNavigation/style', '', 0, 0, 7, 1, '/Library/basic/interfaces/Css', 'style.css', 0, 1, 0, 0, 0),
('/Library/basic/interfaces/widgets/PageNavigation/title', '', 0, 0, 7, 1, '/Library/basic/interfaces/widgets/Widget/title', 'Меню постраничной навигации', 0, 0, 0, 0, 0),
('/Library/basic/interfaces/widgets/ViewObject', '', 0, 0, 6, 3, '/Library/basic/interfaces/widgets/Widget', 'ViewObject.tpl', 1, 1, 0, 0, 0),
('/Library/basic/interfaces/widgets/ViewObject/description', '', 0, 0, 7, 2, '/Library/basic/interfaces/widgets/Widget/description', 'Виджет для отображения любого объекта.', 0, 0, 0, 0, 0),
('/Library/basic/interfaces/widgets/ViewObject/title', '', 0, 0, 7, 1, '/Library/basic/interfaces/widgets/Widget/title', 'Универсальный виджет', 0, 0, 0, 0, 0),
('/Library/basic/interfaces/widgets/ViewObjectsList', '', 0, 0, 6, 4, '/Library/basic/interfaces/widgets/Widget', 'ViewObjectsList.tpl', 1, 1, 0, 0, 0),
('/Library/basic/interfaces/widgets/ViewObjectsList/description', '', 0, 0, 7, 2, '/Library/basic/interfaces/widgets/Widget/description', 'Виджет для отображения списка любых объектов', 0, 0, 0, 0, 0),
('/Library/basic/interfaces/widgets/ViewObjectsList/title', '', 0, 0, 7, 1, '/Library/basic/interfaces/widgets/Widget/title', 'Универсальный виджет списка', 0, 0, 0, 0, 0),
('/Library/basic/interfaces/widgets/Widget', '', 0, 0, 6, 2, NULL, NULL, 1, 0, 0, 0, 0),
('/Library/basic/members', '', 0, 0, 4, 3, '/Library/basic/Package', NULL, 0, 0, 0, 0, 0),
('/Library/basic/members/Group', '', 0, 0, 5, 2, NULL, NULL, 1, 0, 0, 0, 0),
('/Library/basic/members/Group/title', '', 0, 0, 6, 1, NULL, 'Группа пользователей', 0, 0, 0, 0, 0),
('/Library/basic/members/User', '', 0, 0, 5, 1, NULL, NULL, 1, 0, 0, 0, 0),
('/Library/basic/members/User/email', '', 0, 0, 6, 3, '/Library/basic/simple/Email', NULL, 0, 0, 0, 0, 0),
('/Library/basic/members/User/name', '', 0, 0, 6, 2, NULL, NULL, 0, 0, 0, 0, 0),
('/Library/basic/members/User/title', '', 0, 0, 6, 1, NULL, 'Пользователь', 0, 0, 0, 0, 0),
('/Library/basic/Package', '', 0, 0, 4, 5, NULL, NULL, 0, 0, 0, 0, 0),
('/Library/basic/simple', '', 0, 0, 4, 4, '/Library/basic/Package', NULL, 0, 0, 0, 0, 0),
('/Library/basic/simple/Email', '', 0, 0, 5, 1, NULL, NULL, 1, 0, 0, 0, 0),
('/Library/basic/simple/Number', '', 0, 0, 5, 2, NULL, NULL, 1, 0, 0, 0, 0),
('/Library/layouts', '', 0, 0, 3, 2, '/Library/basic/Package', NULL, 0, 0, 0, 0, 0),
('/Library/layouts/boolive', '', 0, 0, 4, 1, '/Library/basic/interfaces/widgets/Focuser', 'boolive.tpl', 0, 1, 0, 0, 0),
('/Library/layouts/boolive/bottom', '', 0, 0, 5, 1, '/Library/basic/interfaces/Group', NULL, 0, 0, 0, 0, 0),
('/Library/layouts/boolive/center', '', 0, 0, 5, 1, '/Library/basic/interfaces/Group', NULL, 0, 0, 0, 0, 0),
('/Library/layouts/boolive/center/Content', '', 0, 0, 6, 1, '/Library/basic/interfaces/widgets/ViewObject', NULL, 0, 0, 0, 0, 0),
('/Library/layouts/boolive/center/Content/option_page', '', 0, 0, 7, 1, '/Library/basic/interfaces/widgets/Option', '/Library/basic/contents/Page', 0, 0, 0, 0, 0),
('/Library/layouts/boolive/center/Content/option_page/Page', '', 0, 0, 8, 1, '/Library/basic/interfaces/widgets/ViewObjectsList', 'Page.tpl', 1, 1, 0, 0, 0),
('/Library/layouts/boolive/center/Content/option_page/Page/option_comments', '', 0, 0, 9, 1, '/Library/basic/interfaces/widgets/Option', '/Library/basic/contents/Page/comments', 0, 0, 0, 0, 0),
('/Library/layouts/boolive/center/Content/option_page/Page/option_comments/Comments', '', 0, 0, 10, 1, NULL, NULL, 1, 0, 0, 0, 0),
('/Library/layouts/boolive/center/Content/option_page/Page/option_keywords', '', 0, 0, 9, 2, '/Library/basic/interfaces/widgets/Option', '/Library/basic/contents/Page/keywords', 0, 0, 0, 0, 0),
('/Library/layouts/boolive/center/Content/option_page/Page/option_keywords/Keywords', '', 0, 0, 10, 1, NULL, NULL, 1, 0, 0, 0, 0),
('/Library/layouts/boolive/center/Content/option_page/Page/option_text', '', 0, 0, 9, 3, '/Library/basic/interfaces/widgets/Option', '/Library/basic/contents/Page/text', 0, 0, 0, 0, 0),
('/Library/layouts/boolive/center/Content/option_page/Page/option_text/Text', '', 0, 0, 10, 1, NULL, NULL, 1, 0, 0, 0, 0),
('/Library/layouts/boolive/center/Content/option_page/Page/option_title', '', 0, 0, 9, 4, '/Library/basic/interfaces/widgets/Option', '/Library/basic/contents/Page/title', 0, 0, 0, 0, 0),
('/Library/layouts/boolive/center/Content/option_page/Page/option_title/Title', '', 0, 0, 10, 1, NULL, NULL, 1, 0, 0, 0, 0),
('/Library/layouts/boolive/center/Content/option_part', '', 0, 0, 7, 2, '/Library/basic/interfaces/widgets/Option', '/Library/basic/contents/Part', 0, 0, 0, 0, 0),
('/Library/layouts/boolive/center/Content/option_part/Part', '', 0, 0, 8, 1, '/Library/basic/interfaces/widgets/ViewObjectsList', 'Part.tpl', 1, 1, 0, 0, 0),
('/Library/layouts/boolive/center/Content/option_part/Part/count_per_page', '', 0, 0, 9, 9, NULL, '2', 0, 0, 0, 0, 0),
('/Library/layouts/boolive/center/Content/option_part/Part/option_page', '', 0, 0, 9, 1, '/Library/basic/interfaces/widgets/Option', '/Library/basic/contents/Page', 0, 0, 0, 0, 0),
('/Library/layouts/boolive/center/Content/option_part/Part/option_page/PagePreview', '', 0, 0, 10, 1, NULL, NULL, 1, 0, 0, 0, 0),
('/Library/layouts/boolive/center/Content/option_part/Part/option_part', '', 0, 0, 9, 2, '/Library/basic/interfaces/widgets/Option', '/Library/basic/contents/Part', 0, 0, 0, 0, 0),
('/Library/layouts/boolive/center/Content/option_part/Part/option_part/PartPreview', '', 0, 0, 10, 1, NULL, NULL, 1, 0, 0, 0, 0),
('/Library/layouts/boolive/center/Content/option_part/Part/option_title', '', 0, 0, 9, 3, '/Library/basic/interfaces/widgets/Option', '/Library/basic/contents/Part/title', 0, 0, 0, 0, 0),
('/Library/layouts/boolive/center/Content/option_part/Part/option_title/Title', '', 0, 0, 10, 1, '/Library/layouts/boolive/center/Content/option_page/Page/option_title/Title', NULL, 0, 0, 0, 0, 0),
('/Library/layouts/boolive/center/Content/option_part/Part/pagesnum', '', 0, 0, 9, 10, '/Library/basic/interfaces/widgets/PageNavigation', NULL, 0, 0, 0, 0, 0),
('/Library/layouts/boolive/head', '', 0, 0, 5, 1, '/Library/basic/interfaces/Group', NULL, 0, 0, 0, 0, 0),
('/Library/layouts/boolive/head/logo', '', 0, 0, 6, 1, '/Library/basic/interfaces/widgets/Logo', NULL, 0, 0, 0, 0, 0),
('/Library/layouts/boolive/sidebar', '', 0, 0, 5, 1, '/Library/basic/interfaces/Group', NULL, 0, 0, 0, 0, 0),
('/Library/layouts/boolive/sidebar/menu', '', 0, 0, 6, 1, '/Library/basic/interfaces/widgets/Menu', NULL, 0, 0, 0, 0, 0),
('/Library/layouts/boolive/sidebar/menu/items/news/not_auto', '', 0, 0, 9, 1, NULL, '0', 0, 0, 0, 0, 0),
('/Library/layouts/boolive/sidebar/menu/items/news/title', '', 0, 0, 9, 1, NULL, 'Новости!!', 0, 0, 0, 0, 0),
('/Library/layouts/boolive/sidebar/menu/style', '', 0, 0, 7, 1, '/Library/basic/interfaces/widgets/Menu/style', 'style.css', 0, 1, 0, 0, 0),
('/Library/layouts/boolive/sidebar/menu/title', '', 0, 0, 7, 1, '/Library/basic/interfaces/widgets/Menu/title', 'Заголовок меню', 0, 0, 0, 0, 0),
('/Library/layouts/boolive/style', '', 0, 0, 5, 1, '/Library/basic/interfaces/Css', 'style.css', 0, 1, 0, 0, 0),
('/Library/layouts/boolive/top', '', 0, 0, 5, 1, '/Library/basic/interfaces/Group', NULL, 0, 0, 0, 0, 0),
('/Library/layouts/boolive/top/menu', '', 0, 0, 6, 1, '/Library/basic/interfaces/widgets/Menu', 'menu.tpl', 0, 1, 0, 0, 0),
('/Library/layouts/boolive/top/menu/items/main/not_auto', '', 0, 0, 9, 1, NULL, '1', 0, 0, 0, 0, 0),
('/Library/layouts/boolive/top/menu/items/news/not_auto', '', 0, 0, 9, 1, NULL, '1', 0, 0, 0, 0, 0),
('/Library/layouts/boolive/top/menu/style', '', 0, 0, 7, 1, '/Library/basic/interfaces/Css', 'style.css', 0, 1, 0, 0, 0);

-- --------------------------------------------------------

-- 
-- Структура таблицы `members`
-- 

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Дамп данных таблицы `members`
-- 

INSERT INTO `members` (`uri`, `lang`, `owner`, `date`, `level`, `order`, `proto`, `value`, `is_logic`, `is_file`, `is_history`, `is_delete`, `is_hidden`) VALUES 
('/Members/guests', '', 0, 0, 3, 1, '/Library/basic/members/Group', NULL, 0, 0, 0, 0, 0),
('/Members/guests/title', '', 0, 0, 4, 1, '/Library/basic/members/Group/title', 'Гости', 0, 0, 0, 0, 0),
('/Members/registered', '', 0, 0, 3, 2, '/Library/basic/members/Group', NULL, 0, 0, 0, 0, 0),
('/Members/registered/admins', '', 0, 0, 4, 2, '/Library/basic/members/Group', NULL, 0, 0, 0, 0, 0),
('/Members/registered/admins/title', '', 0, 0, 5, 1, '/Library/basic/members/Group/title', 'Администраторы', 0, 0, 0, 0, 0),
('/Members/registered/admins/vova', '', 0, 0, 5, 2, '/Library/basic/members/User', 'password_hash', 0, 0, 0, 0, 0),
('/Members/registered/admins/vova/email', '', 0, 0, 6, 2, '/Library/basic/members/User/email', 'boolive@yandex.ru', 0, 0, 0, 0, 0),
('/Members/registered/admins/vova/name', '', 0, 0, 6, 1, '/Library/basic/members/User/name', 'Вова', 0, 0, 0, 0, 0),
('/Members/registered/title', '', 0, 0, 4, 1, '/Library/basic/members/Group/title', 'Зарегистрированные', 0, 0, 0, 0, 0);

-- --------------------------------------------------------

-- 
-- Структура таблицы `site`
-- 

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Дамп данных таблицы `site`
-- 

INSERT INTO `site` (`uri`, `lang`, `owner`, `date`, `level`, `order`, `proto`, `value`, `is_logic`, `is_file`, `is_history`, `is_delete`, `is_hidden`) VALUES 
('/Contents', '', 0, 1342077181, 2, 1, NULL, NULL, 0, 0, 0, 0, 0),
('/Interfaces', '', 0, 1342082233, 2, 1, '/Library/basic/interfaces/Group', NULL, 0, 0, 0, 0, 0),
('/Keywords', '', 0, 1342077181, 2, 1, NULL, NULL, 0, 0, 0, 0, 0),
('/Library', '', 0, 1342077181, 2, 2, NULL, NULL, 0, 0, 0, 0, 0),
('/Members', '', 0, 1342077181, 2, 1, '/Library/basic/members/Group', NULL, 0, 0, 0, 0, 0);
