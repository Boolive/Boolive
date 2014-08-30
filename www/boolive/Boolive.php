<?php
/**
 * Модуль-ядро Boolive
 *
 * Подготавливает условия для работы проекта.
 * Осуществляет автоматическую загрузку свох классов и классов сущностей.
 * Генерирует события активации и деактивации.
 * Обрабатывает ошибки и исключения, сообщает о них генерацией события.
 * @author Vladimir Shestakov <boolive@yandex.ru>
 * @version 2.0
 */
namespace boolive
{
    use boolive\errors\Error;
    use Exception,
        ErrorException;

    class Boolive
    {
        /** @var array Список активированных классов */
        static private $activated;
        /** @var array Список подключенных классов */
        static private $included;
        /** @var int Текущий уровень фиксации ошибок в настройках PHP */
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
                self::$activated = array($class_name=>$class_name);
                self::$included = array($class_name=>$class_name);
                self::$error_reporting = error_reporting();
                if (IS_INSTALL){
                    self::init();
                    // При необходимости, каждый класс может автоматически подключиться и активироваться, обработав событие START.
                    \boolive\events\Events::trigger('Boolive::activate');
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
                        self::$included[$class_name] = $class_name;
                        include_once($class_path);
                        if (method_exists($class_name, 'activate')) {
                            self::$activated[$class_name] = $class_name;
                            $class_name::activate();
                        }
                    }else{
                        // Только для классов сущностей вызываем своё исключение, чтобы его можно было обоаботать
                        if (substr($class_name,0,5)=='site\\' && !class_exists($class_name, false) && !interface_exists($class_name, false)){
                            throw new ErrorException('Class "'.$class_name.'" not found', 2);
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
            \boolive\events\Events::trigger('Boolive::deactivate');
        }

        /**
         * Инициализация ядра
         */
        static function init()
        {
            // Регистрация метода-обработчика автозагрузки классов
            spl_autoload_register(array('\boolive\Boolive', 'activate'), true, true);
            // Регистрация метода-обработчка завершения выполнения системы
            register_shutdown_function(array('\boolive\Boolive', 'deactivate'));
            // Регистрация обработчика исключений
            set_exception_handler(array('\boolive\Boolive', 'exception'));
            // Регистрация обработчика ошибок
            set_error_handler(array('\boolive\Boolive', 'error'));
            // Временая зона
            date_default_timezone_set('UTC');
            // Настройка кодировки
            mb_internal_encoding('UTF-8');
            mb_regex_encoding('UTF-8');
            mb_http_output('UTF-8');
        }

        static function start()
        {
            if (self::activate()){
                // Запуск ядра, обработка запроса
                echo \boolive\data\Data2::read()->start(new \boolive\commands\Commands(), \boolive\input\Input::getSource());
            }else{
                // Запуск установщика, если Boolive не активирован
                include DIR.'boolive/installer/Installer.php';
                \boolive\installer\Installer::start();
            }
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
            if (!\boolive\events\Events::trigger('Boolive::error', $e)->result){
                trace_log(get_class($e).' ['.$e->getCode().']: '.$e->getMessage().' in '.$e->getFile().' on line '.$e->getLine());
                //trace_log()
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
         * @param int $errno Код ошибки
         * @param string $errstr Сообщение
         * @param string $errfile Файл ошибки
         * @param int $errline Номер строки с ошибкой
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
         * Путь на файл класса по стандарту PSR-0
         * @param string $class_name Имя класса с namespace
         * @return string Путь к файлу от корня сервера
         */
        public static function getClassFile($class_name)
        {
            return DIR.str_replace('\\', '/', $class_name).'.php';
        }

        /**
         * Загрузка внешнего класса
         * @param string $class_name Полное имя класса
         * @param string $class_path Путь, куда сохранять класс
         * @return bool
         */
        public static function loadRemoteClass($class_name, $class_path)
        {
            $names = \boolive\functions\F::splitRight('\\', $class_name, true);
            if (preg_match('/^remote\\\\([^\\\\]+)(.*)$/u', $names[0], $match)){
                $namespace_pfx = 'remote\\'.$match[1].'\\';
                $match[1] = str_replace('__','-',$match[1]);
                $match[1] = str_replace('_','.',$match[1]);
                $match[2] = str_replace('\\','/',$match[2]);
                $uri = 'http://'.$match[1].$match[2].'&class_content=1';
                $remote = \boolive\data\Data2::read($uri);
                $class = $remote->classContent();
                if (isset($class['content'])){
                    $content = base64_decode($class['content']);
                    // Название классов и пространств имен не ядра переименовываются - добавляется префикс
                    $content = preg_replace_callback('/((?:[A-Za-a0-9_]+\\\\)+[A-Za-a0-9_]+)/ui', function($m) use ($namespace_pfx){
                        if (mb_substr($m[1], 0, 8) == 'boolive\\' || mb_substr($m[1], 0, 7) == 'remote\\'){
                            return $m[1];
                        }else{
                            return $namespace_pfx.$m[1];
                        }
                    }, $content);
                    // Название классов как строковые значения в коде
                    $content = preg_replace_callback('/(\'|")(?:\\\\\\\\?[\w_]+)+(\'|")/ui', function($m) use ($namespace_pfx){
                        $x = trim($m[0],'\\\'"');
                        if (mb_substr($x, 0, 8) == 'boolive\\' || mb_substr($m[1], 0, 7) == 'remote\\'){
                            return $m[1].$x.$m[2];
                        }else{
                            return $m[1].'\\'.$namespace_pfx.$x.$m[2];
                        }
                    }, $content);
                    // Корневые namespace (без \)
                    $content = preg_replace('/namespace\s+([a-zA-Z_]+)([\s;$]+)/ui', 'namespace '.$namespace_pfx.'\\$1$2', $content);
                    \boolive\file\File::create($content, $class_path);
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
            if (!ini_get('short_open_tag') && version_compare(PHP_VERSION, "5.4.0", "<")){
                $requirements[] = 'В настройках PHP включите параметр "короткие теги" <code>short_open_tag On</code> или используйте php 5.4 и новее';
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
            $file = DIR.'index.php';
            if (!is_writable($file)){
                $requirements[] = 'Установите права на запись для файла <code>'.$file.'</code>. Необходимо для автоматической записи настроек системы';
            }
            $file = DIR.'.htaccess';
            if (!is_writable($file)){
                $requirements[] = 'Установите права на запись для файла <code>'.$file.'</code>. Необходимо для автоматической записи настроек системы';
            }
            if (!is_writable(DIR.'site')){
                $requirements[] = 'Установите права на запись для директории <code>'.DIR.'site</code> и всех её вложенных директорий и файлов';
            }
            if (!extension_loaded('mbstring')){
                $requirements[] = 'Требуется расширение <code>mbstring</code> для PHP';
            }
            return $requirements;
        }

        /**
         * Запрашиваемые данные для установки модуля
         * @return array
         */
//        static function installPrepare()
//        {
//            return array(
//                'title' => 'Настройка фоновых задач',
//                'descript' => 'Для выполнения фоновых задач необходим прямой доступ к интерпретатору PHP',
//                'fields' => array(
//                    'php' => array(
//                        'label' => 'Путь к PHP CLI',
//                        'descript' => 'Укажите полный путь до php.exe (php на *nix)',
//                        'value' => PHP,
//                        'input' => 'text',
//                        'style' => 'big',
//                        'required' => true,
//                    )
//                )
//            );
//        }
//
//        /**
//         * Установка ядра
//         */
//        static function install($input)
//        {
//            // Параметры доступа к БД
//            $errors = new Error('Некоректные параметры', 'boolive');
//            $new_config = $input->REQUEST->get(\boolive\values\Rule::arrays(array(
//                'php'	 => \boolive\values\Rule::string()->more(0)->max(255)->required()
//            )), $sub_errors);
//            // Если ошибочные данные от юзера
//            if ($sub_errors){
//                $errors->add($sub_errors->children());
//                throw $errors;
//            }
//            if (!is_executable($new_config['php'])){
//                $errors->php->not_exec = "Not executable";
//                throw $errors;
//            }
//            $file = DIR.'config.php';
//            if (is_writable($file)){
//                $content = file_get_contents($file);
//                $content = preg_replace('/["\']PHP[\'"],[^)]+/u', "'PHP', '".$new_config['php']."'", $content);
//                $fp = fopen($file, 'w');
//                fwrite($fp, $content);
//                fclose($fp);
//            }
//
////            $file = DIR.'.htaccess';
////            if (is_writable($file)){
////                $content = file_get_contents($file);
////                // Прописывание базовой директории для mod_rewrite
////                $content = preg_replace('/\n[ \t]*RewriteEngine[ \t\S]*/u', "\n    RewriteEngine On", $content);
////                $content = preg_replace('/\n[ \t]*RewriteBase[ \t\S]*/u', "\n    RewriteBase ".DIR_WEB, $content);
////                $fp = fopen($file, 'w');
////                fwrite($fp, $content);
////                fclose($fp);
////            }
//        }
    }
}

namespace {
    /**
     * Трассировка переменной с автоматическим выводом значения
     * Сделано из-за лени обращаться к классу Trace :)
     * @param mixed $var Значение для трассировки
     * @param null $key
     * @return \boolive\develop\Trace Объект трассировки
     */
    function trace($var = null, $key = null)
    {
        return \boolive\develop\Trace::groups('trace')->group($key)->set($var)->out();
    }

    function trace_log($var = null, $key = null)
    {
        \boolive\develop\Trace::groups('trace')->group($key)->set($var)->log();
    }
}
