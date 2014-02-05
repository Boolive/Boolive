<?php
/**
 * Модуль-ядро Boolive
 *
 * Подготавливает услвоия для работы проекта.
 * Осуществляет автоматическую загрузку модулей (классов).
 * Генерирует события активации и деактивации.
 * Обрабатывает ошибки и исключения, сообщает о них генерацией события.
 * @author Vladimir Shestakov <boolive@yandex.ru>
 * @version 2.0
 */
namespace Boolive
{
    use Exception,
        ErrorException;

    class Boolive
    {
        /** @var array Список активированных классов */
        static private $activated;
        /** @var array Список подключенных классов */
        static private $included;
        /** @var Текущий уровень фиксации ошибок в настройках PHP */
        static private $error_reporting;

        /**
         * Активация ядра, если вызван без параметров
         * Активация указанного в аргументах класса (модуля).
         * @param string $class_name Имя подключаемого класса
         * @throws \ErrorException
         * @return bool
         */
        static function activate($class_name = ''){
            if (empty($class_name)){
                // Актвация самого себя
                self::$activated = array();
                self::$included = array();
                self::$error_reporting = error_reporting();
                if (IS_INSTALL){
                    self::init();
                    // При необходимости, каждый класс может автоматически подключиться и активироваться, обработав событие START.
                    \Boolive\events\Events::trigger('Boolive::activate');
                }else{
                    // Ядро не инициализировано
                    return false;
                }
            }else{
                $class_name = ltrim($class_name, '\\');
                // Если ещё не подключен
                if (!self::isIncluded($class_name)){
                    // Подключение и активация запрашиваемого модуля
                    $class_path = self::getClassFile($class_name);
                    if (is_file($class_path)){
                        include_once($class_path);
                    }else
                    if (mb_substr($class_name,0,7)=='Remote\\' && self::loadRemoteClass($class_name, $class_path)){
                        // Если класс из Remote и его получилось загрузить с его сервера
                        include_once($class_path);
                    }else{
                        throw new ErrorException('Class "'.$class_name.'" not found', 2);
                    }
                    if (!class_exists($class_name, false) && !interface_exists($class_name, false)){
                        throw new ErrorException('Class "'.$class_name.'" not found', 2);
                    }
                    self::$included[$class_name] = $class_name;
                    if (!isset(self::$activated[$class_name])) {
                        if (method_exists($class_name, 'activate')) {
                            $class_name::activate();
                        }
                    }
                }
            }
            return true;
        }

        /**
         * Завершение выполнения системы
         * Метод вызывается автоматически интерпретатором при завершение всех действий системы
         * или при разрыве соединения с клиентом
         */
        static function deactivate()
        {
            \Boolive\events\Events::trigger('Boolive::deactivate');
        }

        /**
         * Инициализация ядра
         */
        static function init()
        {
            // Регистрация метода-обработчика автозагрузки классов
            spl_autoload_register(array('\Boolive\Boolive', 'activate'));
            // Регистрация метода-обработчка завершения выполнения системы
            register_shutdown_function(array('\Boolive\Boolive', 'deactivate'));
            // Регистрация обработчика исключений
            set_exception_handler(array('\Boolive\Boolive', 'exception'));
            // Регистрация обработчика ошибок
            set_error_handler(array('\Boolive\Boolive', 'error'));
            // Временая зона
            date_default_timezone_set('UTC');
            // Настройка кодировки
            mb_internal_encoding('UTF-8');
            mb_regex_encoding('UTF-8');
            mb_http_output('UTF-8');
        }

        /**
         * Обработчик исключений
         * Вызывается автоматически при исключениях и ошибках
         * @param \Exception $e Обрабатываемое исключение
         * @return bool
         */
        static function exception($e)
        {
            // Если обработчики событий не вернут положительный результат, то
            // обрабатываем исключение по умолчанию
            if (!\Boolive\events\Events::trigger('Boolive::error', $e)->result){
                error_log(get_class($e).' ['.$e->getCode().']: '.$e->getMessage().' in '.$e->getFile().' on line '.$e->getLine());
                if (isset($e->xdebug_message)){
                    echo '<table cellspacing="0" cellpadding="1" border="1" dir="ltr">'.$e->xdebug_message.'</table>';
                }else{
                    trace($e, 'error');
                }
            };
        }

        /**
         * Обработчик ошбок PHP
         * Преобразование php ошибки в исключение для стандартизации их обработки
         * @param $errno Код ошибки
         * @param $errstr Сообщение
         * @param $errfile Файл ошибки
         * @param $errline Номер строки с ошибкой
         * @throws ErrorException Если ошибка не игнорируется, то превращается в исключение
         * @return bool
         */
        static function error($errno, $errstr, $errfile, $errline)
        {
            if (!(self::$error_reporting & $errno)){
                return false;
            }
            throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
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
         * Путь на файл класса
         * @param string $class_name Имя класса с namespace
         * @return string Путь к файлу от корня сервера
         */
        public static function getClassFile($class_name)
        {
            $names = explode('\\', $class_name, 2);
            $path = str_replace('\\', '/', $class_name);
            if ($names[0] == 'Boolive') {
                return DIR_SERVER_ENGINE.substr($path, 8).'.php';
            }else
            if ($names[0] == 'Remote'){
                return  DIR_SERVER_REMOTE.substr($path, 7).'.php';
            }else{
                return DIR_SERVER_PROJECT.$path.'.php';
            }
        }

        /**
         * Загрузка внешнего класса
         * @param $class_name Полное имя класса
         * @param $class_path Путь, куда сохранять класс
         * @return bool
         */
        public static function loadRemoteClass($class_name, $class_path)
        {
            $names = \Boolive\functions\F::splitRight('\\', $class_name, true);
            if (preg_match('/^Remote\\\\([^\\\\]+)(.*)$/u', $names[0], $match)){
                $namespace_pfx = 'Remote\\'.$match[1].'\\';
                $match[1] = str_replace('__','-',$match[1]);
                $match[1] = str_replace('_','.',$match[1]);
                $match[2] = str_replace('\\','/',$match[2]);
                $uri = 'http://'.$match[1].$match[2].'&class_content=1';
                $remote = \Boolive\data\Data::read($uri);
                $class = $remote->classContent();
                if (isset($class['content'])){
                    $content = base64_decode($class['content']);
                    // Название классов и пространств имен не ядра переименовываются - добавляется префикс
                    $content = preg_replace_callback('/((?:[A-Za-a0-9_]+\\\\)+[A-Za-a0-9_]+)/ui', function($m) use ($namespace_pfx){
                        if (mb_substr($m[1], 0, 8) == 'Boolive\\' || mb_substr($m[1], 0, 7) == 'Remote\\'){
                            return $m[1];
                        }else{
                            return $namespace_pfx.$m[1];
                        }
                    }, $content);
                    // Название классов как строковые значения в коде
                    $content = preg_replace_callback('/(\'|")(?:\\\\\\\\?[\w_]+)+(\'|")/ui', function($m) use ($namespace_pfx){
                        $x = trim($m[0],'\\\'"');
                        if (mb_substr($x, 0, 8) == 'Boolive\\' || mb_substr($m[1], 0, 7) == 'Remote\\'){
                            return $m[1].$x.$m[2];
                        }else{
                            return $m[1].'\\'.$namespace_pfx.$x.$m[2];
                        }
                    }, $content);
                    // Корневые namespace (без \)
                    $content = preg_replace('/namespace\s+([a-zA-Z_]+)([\s;$]+)/ui', 'namespace '.$namespace_pfx.'\\$1$2', $content);
                    \Boolive\file\File::create($content, $class_path);
                    return true;
                }
            }
            return false;
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
            if (class_exists($class_name, false) || interface_exists($class_name)){
                return true;
            }
            return is_file(self::getClassFile($class_name));
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
         * Проверка системных требований для установки класса Boolive
         * @return array Массив сообщений - требований для установки
         */
        static function systemRequirements()
        {
            $requirements = array();
            // Проверка совместимости версии PHP
            if (!version_compare(PHP_VERSION, "5.3.3", ">=")){
                $requirements[] = 'Несовместимая версия PHP. Установлена '.PHP_VERSION.' Требуется 5.3.3 или выше';
            }
            // Лимит памяти
            $memory_limit = ini_get('memory_limit');
            if (preg_match('/^(\d+)(.)$/', $memory_limit, $matches)) {
                switch ($matches[2]){
                    case 'G':$memory_limit = $matches[1] * 1073741824; break;
                    case 'M':$memory_limit = $matches[1] * 1048576; break;
                    case 'K':$memory_limit = $matches[1] * 1024; break;
                }
            }
            if ($memory_limit < 32 * 1048576){
                $requirements[] = 'В настройках PHP увеличьте лимит оперативной памяти до 32 Мегабайт <code>memory_limit 32MB</code>';
            }
            if (!ini_get('short_open_tag')){
                $requirements[] = 'В настройках PHP включите параметр "короткие теги" <code>short_open_tag On</code>';
            }
            if (ini_get('register_globals')){
                $requirements[] = 'В настройках PHP отключите "регистрацию глобальных переменных" <code>register_globals Off</code>';
            }
            if (ini_get('magic_quotes_runtime')){
                $requirements[] = 'В настройках PHP отключите "магические кавычки для функций" <code>magic_quotes_runtime Off</code>';
            }
//            if (ini_get('magic_quotes_gpc')){
//                $requirements[] = 'В настройках PHP отключите "магические кавычки для входящих данных" <code>magic_quotes_gpc Off</code>';
//            }
            // Проверка наличия модуля mod_rewrite
            if (function_exists('apache_get_modules') && !in_array('mod_rewrite', apache_get_modules())){
                $requirements[] = 'Требуется модуль <code>mod_rewrite</code> для сервера Apache. Обратитесь в тех. поддержку или настройте сервер самостоятельно.
                Включение выполняется в файле конфигурации <code>.../Apache/conf/httpd.conf</code> опцией <code>LoadModule rewrite_module modules/mod_rewrite.so</code>';
            }
            $file = DIR_SERVER.'config.php';
            if (!is_writable($file)){
                $requirements[] = 'Установите права на запись для файла <code>'.$file.'</code>. Необходимо для автоматической записи настроек системы';
            }
            $file = DIR_SERVER.'.htaccess';
            if (!is_writable($file)){
                $requirements[] = 'Установите права на запись для файла <code>'.$file.'</code>. Необходимо для автоматической записи настроек системы';
            }
            if (!is_writable(DIR_SERVER_PROJECT)){
                $requirements[] = 'Установите права на запись для директории <code>'.DIR_SERVER_PROJECT.'</code> и всех её вложенных директорий и файлов';
            }
            if (!extension_loaded('mbstring')){
                $requirements[] = 'Требуется расширение <code>mbstring</code> для PHP';
            }
            return $requirements;
        }

        /**
         * Установка ядра
         */
        static function install()
        {
            $file = DIR_SERVER.'.htaccess';
            if (is_writable($file)){
                $content = file_get_contents($file);
                // Прописывание базовой директории для mod_rewrite
                $content = preg_replace('/\n[ \t]*RewriteEngine[ \t\S]*/u', "\n    RewriteEngine On", $content);
                $content = preg_replace('/\n[ \t]*RewriteBase[ \t\S]*/u', "\n    RewriteBase ".DIR_WEB, $content);
                $fp = fopen($file, 'w');
                fwrite($fp, $content);
                fclose($fp);
            }
        }
    }
}

namespace {
    /**
     * Трассировка переменной с автоматическим выводом значения
     * Сделано из-за лени обращаться к классу Trace :)
     * @param mixed $var Значение для трассировки
     * @param null $key
     * @return \Boolive\develop\Trace Объект трассировки
     */
    function trace($var = null, $key = null)
    {
        return \Boolive\develop\Trace::groups('trace')->group($key)->set($var)->out();
    }
}
