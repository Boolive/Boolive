<?php
/**
 * Движок Boolive
 *
 * Активирует работу движка. Осуществляет автоматическую загрузку модулей (классов).
 * Генерирует события начала и завершения работы, чтобы другие модули могли что-либо сделать
 * @version 1.0
 */
namespace Boolive;

use Boolive\events\Events;

class Boolive
{
    /** @var array Список активированных классов */
    static private $activated;
    /** @var array Список подключенных классов */
    static private $included;

    static function activate($class_name = ''){
        $class_name = ltrim($class_name, '\\');
        // Если ещё не подключен
        if (!self::isIncluded($class_name)){
            if (empty($class_name)){
                // Актвация самого себя
                self::$activated = array();
                self::$included = array();
                // Регистрация метода-обработчика автозагрузки классов
                spl_autoload_register(array('\Boolive\Boolive', 'activate'));
                // Регистрация метода-обработчка завершения выполнения системы
                register_shutdown_function(array('\Boolive\Boolive', 'deactivate'));
                // Принудельная активация необходимых системе классов
                self::activate('Boolive\develop\Benchmark');
                self::activate('Boolive\develop\Trace');
                self::activate('Boolive\unicode\Unicode');
                self::activate('Boolive\errors\ErrorsHandler');
                // При необходимости, каждый класс может автоматически подключиться и активироваться, обработав событие START.
                Events::send('activate');
            }else{
                $names = explode('\\', $class_name, 2);
                $path = str_replace('\\', '/', $class_name);
                if ($names[0] == "Boolive") {
                    $path = DIR_SERVER_ENGINE . substr($path, 8) . '.php';
                } else {
                    $path = DIR_SERVER_PROJECT . $path . '.php';
                }
                include_once($path);
                self::$included[$class_name] = $class_name;
                if (!isset(self::$activated[$class_name])) {
                    if (method_exists($class_name, "activate")) {
                        $class_name::activate();
                    }
                }
            }
        }
    }

    /**
     * Завершение выполнения системы
     * Метод вызывается автоматически интерпретатором при завершение всех действий системы
     * или при разрыве соединения с клиентом
     */
    static function deactivate()
    {
        // Любой класс может выполнить завершающие действия при звершении работы системы, обработав событие deactivate
        Events::send('deactivate');
    }

    /**
     * Список активированных классов.
     * Классы, у которых был вызован метод Activate().
     * @return array Названия классов
     */
    public static function getActivated()
    {
        return self::$activated;
    }

    /**
     * Список подключенных классов
     * Классы, php-файлы которых подключены (include)
     * @return array Названия классов
     */
    public static function getIncluded()
    {
        return self::$included;
    }

    /**
     * Проверка, активирован ли класс. Был ли вызван у класса метод Activate()?
     * @param string $class Имя класса
     * @return bool
     */
    public static function isActivate($class)
    {
        $class = ltrim($class, '\\');
        return isset(self::$activated[$class]);
    }

    /**
     * Проверка, подключен ли файл класса.
     * @param string $class Имя класса
     * @return bool
     */
    public static function isIncluded($class)
    {
        $class = ltrim($class, '\\');
        return isset(self::$included[$class]);
    }

    /**
     * Проверка, установлен ли класс
     * Знает ли система о существовании указанного файла?
     * @param string $class_name Имя класса
     * @return bool
     */
    public static function isExist($class_name)
    {
        return true;
    }

    /**
     * Проверка существования не абстрактного класса
     * @param string $class_name Имя класса с учетом namespace
     * @return bool
     */
    public static function isCompleteClass($class_name)
    {
        $result = class_exists($class_name);
        if ($result){
            $testClass  = new \ReflectionClass($class_name);
            $result = !$testClass->isAbstract();
            unset($testClass);
        }
        return $result;
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
