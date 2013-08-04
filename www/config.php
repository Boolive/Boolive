<?php
/**
 * Конфигурация путей и опций ядра
 * @version 1.0
 */
/** @const Времея начала работы системы */
define('START_TIME', microtime(true));

/** @cont bool Установлена ли система Boolive? */
define('IS_INSTALL', false);

/** @cont string Полный путь директории сайта на сервере. Без слеша на конце. */
define('DOCUMENT_ROOT', get_root_dir());

/** @cont string Директория сайта относительно домена (там, где файл index.php). Слеш в начале и конце обязателен! */
define('DIR_WEB', get_web_dir());
/** @cont string Директория ядра (движка) относительно домена. Слеш в конце обязателен! */
define('DIR_WEB_ENGINE', DIR_WEB.'Boolive/');
/** @cont string Директория проекта относительно домена. Слеш в конце обязателен! */
define('DIR_WEB_PROJECT', DIR_WEB);

/** @cont string Директория сайта на сервере. Слеш в конце обязателен! */
define('DIR_SERVER', DOCUMENT_ROOT.DIR_WEB);
/** @cont string Директория ядра на сервере. Слеш в конце обязателен! */
define('DIR_SERVER_ENGINE', DOCUMENT_ROOT.DIR_WEB_ENGINE);
/** @cont string Директория проекта на сервере. Слеш в конце обязателен! */
define('DIR_SERVER_PROJECT', DOCUMENT_ROOT.DIR_WEB_PROJECT);

// Адрес сайта, например: boolive.ru. Значение по умолчанию для CLI режима
define('HTTP_HOST', empty($_SERVER['HTTP_HOST'])?'boolive.ru' : $_SERVER['HTTP_HOST']);
/** @cont string Временная метка для общей идентификации кэша (изменение сбрасывает кэш) */
define('TIMESTAMP', '1');

/* Признак, выводить всю трассировку?*/
define('GLOBAL_TRACE', true);
/* Признак, профилировать запросы к модулю даных?*/
define('PROFILE_DATA', false);
/**
 * Определение корневой директории сервера
 * @return string
 */
function get_root_dir()
{
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
function get_web_dir()
{
    preg_match('|^'.preg_quote(DOCUMENT_ROOT,'|').'(.*)index\.php$|', $_SERVER['SCRIPT_FILENAME'], $find);
    if ($find[1] == null) {
        $find[1] = "/";
    }
    return $find[1];
}