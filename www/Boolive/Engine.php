<?php
/**
 * Ядро. Главный класс системы.
 * @version 1.0
 * @link http://boolive.ru/createcms/cms-engine
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Boolive;

use Boolive\data\Data,
    Boolive\input\Input,
    Boolive\events\Events,
    Boolive\classes\Classes,
    Boolive\commands\Commands;

class Engine
{
    /**
     * Запуск системы. Инициализация.
     */
    static function start()
    {
        // Регистрация метода-обработчка завершения выполнения системы
        register_shutdown_function(array('\Boolive\Engine', 'stop'));
        // Подключение файла класса для управления всеми классами.
        // Остальные файлы классов будут подключаться автоматически при первом обращении
        include_once DIR_SERVER_ENGINE.'classes/Classes.php';
        // Принудельная активация необходимых системе классов
        Classes::activate('Boolive\classes\Classes');
        Classes::activate('Boolive\develop\Benchmark');
        Classes::activate('Boolive\develop\Trace');
        Classes::activate('Boolive\unicode\Unicode');
        Classes::activate('Boolive\errors\ErrorsHandler');
        // При необходимости, каждый класс может автоматически подключиться и активироваться, обработав событие START.
        Events::send('START');
        Engine::work();
    }

    /**
     * Выполнение системы
     * Передача управления объектам интерфейса и вывод результатов их работы
     */
    static function work()
    {
        // Исполнение корневого объекта и вывод клиенту результата
        echo Data::object('')->start(new Commands(), Input::all()->any());
    }

    /**
     * Завершение выполнения системы
     * Метод вызывается автоматически интерпретатором при завершение всех действий системы
     * или при разрыве соединения с клиентом
     */
    static function stop()
    {
        // Любой класс может выполнить завершающие действия при звершении работы системы, обработав событие STOP
        Events::send('STOP');
    }

    /**
     * Проверка системных требований для установки класса Engine
     * @return array Массив сообщений - требований для установки
     */
    static function systemRequirements()
    {
        $requirements = array();
        // Проверка совместимости версии PHP
        if (!version_compare(PHP_VERSION, "5.3.3", ">=")){
            $requirements[] = 'Несовместимая версия PHP. Установлена '.PHP_VERSION.' Требуется 5.3.3 или выше';
        }
        if (!ini_get('short_open_tag')){
            //$requirements[] = 'В настройках PHP включите параметр "короткие теги" (short_open_tag On)';
        }
        if (ini_get('register_globals')){
            $requirements[] = 'В настройках PHP отключите "регистрацию глобальных переменных" (register_globals Off)';
        }
        if (ini_get('magic_quotes_runtime')){
            $requirements[] = 'В настройках PHP отключите "магические кавычки для функций" (magic_quotes_runtime Off)';
        }
        if (ini_get('magic_quotes_gpc')){
            $requirements[] = 'В настройках PHP отключите "магические кавычки для входящих данных" (magic_quotes_gpc Off)';
        }

        // Проверка наличия модуля mod_rewrite
        if (function_exists('apache_get_modules') && !in_array('mod_rewrite', apache_get_modules())){
            $requirements[] = 'Требуется включить модуль "mod_rewrite" для сервера Apache. Обратитесь в тех. поддержку или настройте сервер самостоятельно.
            Включение выполняется в файле конфигурации "...\Apache\conf\httpd.conf" опцией LoadModule';
        }
        $file = DIR_SERVER.'config.php';
        if (!is_writable($file)){
            $requirements[] = 'Установите права на запись для файла "'.$file.'". Необходимо для автоматической записи настроек системы';
        }
        $file = DIR_SERVER.'.htaccess';
        if (!is_writable($file)){
            $requirements[] = 'Установите права на запись для файла "'.$file.'". Необходимо для автоматической записи настроек системы';
        }
        if (!is_writable(DIR_SERVER)){
            $requirements[] = 'Установите права на запись для директории "'.DIR_SERVER_PROJECT.'" и всех её вложенных директорий и файлов';
        }
        return $requirements;
    }

    /**
     * Установка класса
     *
     */
    static function install()
    {
        $file = DIR_SERVER.'.htaccess';
        if (is_writable($file)){
            $content = file_get_contents($file);
            // Прописывание базовой директории для mod_rewrite
            $content = preg_replace('/\n[ \t]*RewriteBase[ \t\S]*/u', "\n\tRewriteBase ".DIR_WEB, $content);
            $fp = fopen($file, 'w');
            fwrite($fp, $content);
            fclose($fp);
        }
    }
}