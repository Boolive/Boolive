<?php
/**
 * Базовая конфигурация.
 * @version 1.0
 */
/** @cont string Полный путь директории сайта на сервере. Без слеша на конце. */
define('DOCUMENT_ROOT', get_doc_root());

/** @cont string Директория сайта относительно домена (там, где файл index.php). Слеш в начале и конце обязателен! */
define('SITE_DIR', get_site_dir());
/** @cont string Директория ядра (движка) относительно домена. Слеш в конце обязателен! */
define('SITE_DIR_ENGINE', SITE_DIR.'engine/');
/** @cont string Директория проекта относительно домена. Слеш в конце обязателен! */
define('SITE_DIR_PROJECT', SITE_DIR.'project/');

/** @cont string Директория сайта на сервере. Слеш в конце обязателен! */
define('ROOT_DIR', DOCUMENT_ROOT.SITE_DIR);
/** @cont string Директория ядра на сервере. Слеш в конце обязателен! */
define('ROOT_DIR_ENGINE', DOCUMENT_ROOT.SITE_DIR_ENGINE);
/** @cont string Директория проекта на сервере. Слеш в конце обязателен! */
define('ROOT_DIR_PROJECT', DOCUMENT_ROOT.SITE_DIR_PROJECT);

/**
 * Определение корневой директории сервера
 * @return string
 */
function get_doc_root(){
	if (empty($_SERVER['DOCUMENT_ROOT'])){
		// Если переменной окружения нет, то вычисляем из пути на исполняемый файл
		$_SERVER['DOCUMENT_ROOT'] = dirname($_SERVER['SCRIPT_FILENAME']);
	}
	return rtrim($_SERVER['DOCUMENT_ROOT'],'/\\');
}
/**
 * Определение корневой директории относительно домена сайта
 * @return string
 */
function get_site_dir(){
	preg_match('|^'.preg_quote(DOCUMENT_ROOT,'|').'(.*)index\.php$|', $_SERVER['SCRIPT_FILENAME'], $find);
	return $find[1];
}
