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

class Classes{
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
	static function Activate($class_name){
		$class_name = ltrim($class_name, '\\');
		// Если ещё не подключен
		if (!self::IsIncluded($class_name)){
			if ($class_name == 'Boolive\classes\Classes'){
				// Актвация самого себя

				self::$activated = array();
				self::$included = array();

				// Регистрация метода-обработчика автозагрузки классов
				spl_autoload_register(array('\Boolive\classes\Classes', 'Activate'));
			}else{
                $rootNamespaceArray = explode('\\', $class_name);
                $rootNamespace = $rootNamespaceArray[0];
                if ($rootNamespace == "Boolive" || $rootNamespace == "Site") {
                    if ($rootNamespace == "Boolive") {
                        $rootNamespacePath = DIR_SERVER_ENGINE;
                    } else if ($rootNamespace == "Site") {
                        $rootNamespacePath = DIR_SERVER_PROJECT;
                    }
                    $path = $class_name;
                    $path = preg_replace('/^(' . preg_quote($rootNamespace . "\\", "/") . ')/i',
                        $rootNamespacePath, $path) . ".php";
                    include_once($path);
                    self::$included[$class_name] = $class_name;
                    if (self::$activated[$class_name] == null) {
                        if (method_exists($class_name, "Activate")) {
                            $class_name::Activate();
                        }
                    }
                } else {
                    throw new Error(array("Неизвестный корневой namespace - {$rootNamespace}"));
                }
			}
		}
	}

	/**
	 * Список активированных классов.
	 * Классы, у которых был вызован метод Activate().
	 * @return array Названия классов
	 */
	public static function GetActivated(){
		return self::$activated;
	}

	/**
	 * Список подключенных классов
	 * Классы, php-файлы которых подключены (include)
	 * @return array Названия классов
	 */
	public static function GetIncluded(){
		return self::$included;
	}

	/**
	 * Проверка, активирован ли класс. Был ли вызван у класса метод Activate()?
	 * @param string $class Имя класса
	 * @return bool
	 */
	public static function IsActivate($class){
		$class = ltrim($class, '\\');
		return isset(self::$activated[$class]);
	}

	/**
	 * Проверка, подключен ли файл класса.
	 * @param string $class Имя класса
	 * @return bool
	 */
	public static function IsIncluded($class){
		$class = ltrim($class, '\\');
		return isset(self::$included[$class]);
	}

	/**
	 * Проверка, установлен ли класс
	 * Знает ли система о существовании указанного файла?
	 * @param string $class_name Имя класса
	 * @return bool
	 */
	public static function IsExist($class_name){
		return true;
	}

	/**
	 * Проверка существования не абстрактного класса
	 * @param string $class_name Имя класса с учетом namespace
	 * @return bool
	 */
	public static function IsCompleteClass($class_name){
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
	static public function Install($input){

	}
}
