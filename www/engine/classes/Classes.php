<?php
/**
 * Управление классами
 * Выполняет автоматическую загрузку классов по требованию
 * Информация о классах загружается из конфигурационного файла "engine/config.classes.php" и БД
 *
 * @version	2.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Engine;

use Engine\Error;

class Classes{
	/** @var array Именна классов с путями к их файлам */
	static public $classes;
	/** @var array Список активированных классов */
	static private $activated;
	/** @var array Список подключенных классов */
	static private $included;
	/** @var array Список классов из конфига ядра */
	static private $engine_classes;

	/**
	 * Активаация класса
	 * @param string $class_name - Имя класса
	 * @throws \Engine\Error
	 * @throws \ErrorException
	 */
	static function Activate($class_name){
		$class_name = ltrim($class_name, '\\');
		// Если ещё не подключен
		if (!self::IsIncluded($class_name)){
			if ($class_name == 'Engine\Classes'){
				// Актвация самого себя
				self::$classes = array();
				self::$activated = array();
				self::$included = array();
				self::$engine_classes = array();
				// Загрузка путей на классы ядра
				self::LoadEngineClasses(DIR_SERVER_ENGINE.'config.classes.php', DIR_WEB_ENGINE);
				// Регистрация метода-обработчика автозагрузки классов
				spl_autoload_register(array('\Engine\Classes', 'Activate'));
			}else{
				// Активация указанного класса
				if (!isset(self::$classes[$class_name])){
					// Система не знает о классе
					throw new Error(array('Модуль "%s" не установлен', $class_name));
				}else{
					include(DOCUMENT_ROOT.self::$classes[$class_name]);
					self::$included[$class_name] = $class_name;
					if (!isset(self::$activated[$class_name])){
						// Активация класса (модуля)
						if (method_exists($class_name, 'Activate')){
							call_user_func(array($class_name, 'Activate'));
							self::$activated[$class_name] = $class_name;
						}
					}
				}
			}
		}
	}

	/**
	 * Загрузка конфигурационного файла
	 * @param $config_file Имя файла конфигурации
	 * @param $base_dir Путь к имени файла конфигурации
	 */
	private static function LoadEngineClasses($config_file, $base_dir){
		include $config_file;
		if (isset($classes)){
			foreach ($classes as $key => $path){
				self::$classes[$key] = self::$engine_classes[$key] = $base_dir.$path;
			}
		}
	}

	/**
	 * Добавление класса проекта
	 * @param $path
	 * @param $name
	 * @throws Error
	 */
	public static function AddProjectClasse($path, $name){
		$path = DIR_WEB_PROJECT.trim($path, ' /\\');
		if (isset(self::$classes[$name]) && self::$classes[$name] != $path){
			throw new Error(array('Classes: class name %s is already exist', $name));
		}
		self::$classes[$name] = $path;
	}

	/**
	 * Путь на файл класса относительно корня сайта
	 * @param $class_name Имя класса
	 * @return mixed
	 */
	public static function GetPath($class_name){
		if (isset(self::$classes[$class_name])){
			return self::$classes[$class_name];
		}
		return false;
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
	 * Список установленных классов. Классы, о которых знает система.
	 * @return array Названия классов
	 */
	public static function GetLoaded(){
		return array_keys(self::$classes);
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
		if (isset(self::$classes[$class_name])){
			return class_exists($class_name, true);
		}
		return false;
	}

	/**
	 * Проверка, принадлежит ли указанный класс ядру
	 * @param string $class_name Имя класса с учетом namespace
	 * @return bool
	 */
	public static function IsEngine($class_name){
		return isset(self::$engine_classes[$class_name]);
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
