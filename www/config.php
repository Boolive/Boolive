<?php
/**
 * Базовая конфигурация.
 * @version 1.0
 */
/** @cont string Полный путь директории сайта на сервере. Без слеша на конце. */
define('DOCUMENT_ROOT', get_doc_root());

/** @cont string Директория сайта относительно домена (там, где файл index.php). Слеш в начале и конце обязателен! */
define('DIR_WEB', get_web_dir());

/** @cont string Директория ядра (движка) относительно домена. Слеш в конце обязателен! */
define('DIR_WEB_ENGINE', DIR_WEB.'engine/');

/** @cont string Директория проекта относительно домена. Слеш в конце обязателен! */
define('DIR_WEB_PROJECT', DIR_WEB.'site/');

/** @cont string Директория сайта на сервере. Слеш в конце обязателен! */
define('DIR_SERVER', strtr(DOCUMENT_ROOT.DIR_WEB, array('\\' => DIRECTORY_SEPARATOR,
                                                        '/' => DIRECTORY_SEPARATOR)));
/** @cont string Директория ядра на сервере. Слеш в конце обязателен! */
define('DIR_SERVER_ENGINE', strtr(DOCUMENT_ROOT.DIR_WEB_ENGINE, array('\\' => DIRECTORY_SEPARATOR,
                                                                      '/' => DIRECTORY_SEPARATOR)));
/** @cont string Директория проекта на сервере. Слеш в конце обязателен! */
define('DIR_SERVER_PROJECT', strtr(DOCUMENT_ROOT.DIR_WEB_PROJECT, array('\\' => DIRECTORY_SEPARATOR,
                                                                        '/' => DIRECTORY_SEPARATOR)));

/** @cont string Временная метка для общей идентификации кэша (изменение сбрасывает кэш) */
define('TIMESTAMP', '1');

/**
 * Определение корневой директории сервера
 * @return string
 */
function get_doc_root(){
	if (empty($_SERVER['DOCUMENT_ROOT'])){
		// Если переменной окружения нет, то вычисляем из пути на исполняемый файл
		$_SERVER['DOCUMENT_ROOT'] = dirname($_SERVER['SCRIPT_FILENAME']);
	}
    $_SERVER['DOCUMENT_ROOT'] = strtr($_SERVER['DOCUMENT_ROOT'], array(
        '\\' => DIRECTORY_SEPARATOR,
        '/' => DIRECTORY_SEPARATOR,
    ));
	return rtrim($_SERVER['DOCUMENT_ROOT'],'/\\');
}

/**
 * Определение корневой директории относительно домена сайта
 * @return string
 */
function get_web_dir(){
	preg_match('|^'.preg_quote(DOCUMENT_ROOT,'|').'(.*)index\.php$|', $_SERVER['SCRIPT_FILENAME'], $find);
    if ($find[1] == null) {
        $find[1] = "/";
    }
	return $find[1];
}
