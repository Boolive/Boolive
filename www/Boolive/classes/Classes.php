<?php
/**
 * Управление классами
 * Выполняет автоматическую загрузку классов по требованию
 * Информация о классах загружается из конфигурационного файла "engine/config.classes.php" и БД
 *
 * @version	2.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Boolive\classes;

use Boolive\errors\Error;

class Classes
{
    /** @var array Список активированных классов */
    static private $activated;
    /** @var array Список подключенных классов */
    static private $included;

    /**
     * Активаация класса
     * @param string $class_name - Имя класса
     * @throws \Boolive\errors\Error
     * @throws \ErrorException
     */
    static function activate($class_name)
    {
        $class_name = ltrim($class_name, '\\');
        // Если ещё не подключен
        if (!self::isIncluded($class_name)){

            if ($class_name == 'Boolive\classes\Classes'){
                // Актвация самого себя
                self::$activated = array();
                self::$included = array();
                // Регистрация метода-обработчика автозагрузки классов
                spl_autoload_register(array('\Boolive\classes\Classes', 'activate'));
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
                    if (method_exists($class_name, "Activate")) {
                        $class_name::activate();
                    }
                }
            }
        }
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
     * Установка класса
     * @param $input
     */
    static public function install($input)
    {

    }
}