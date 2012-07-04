<?php
/**
 * Модуль данных
 *
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Engine;

use Engine\Classes;

class Data {
	const CONFIG_FILE = 'config.sections.php';
	private static $config;
	private static $sections;

	/**
	 * Конфигурация секций
	 * @param null $uri URI объекта, для которого требуется конфигурация секции
	 * @return bool|array Если $uri не указан, то вся конфигурация, иначе под указанный объект, если есть
	 */
	private static function GetConfig($uri = null){
		if (file_exists(DIR_SERVER.self::CONFIG_FILE)){
			include DIR_SERVER.self::CONFIG_FILE;
			if (isset($config)) self::$config = $config;
		}else{
			return false;
		}
		if (isset($uri)){
			if (isset(self::$config[$uri])){
				return self::$config[$uri];
			}else{
				return false;
			}
		}else{
			return self::$config;
		}
	}


	/**
	 * Взвращает экземпляр секции, назначенную для подчиенных объекта
	 * Если секции на указнный объект не назначена, то возвращается null
	 * @param $uri Путь на объект
	 * @return \Engine\Section|null Экземпляр секции, если имеется или null, если нет
	 */
	static function Section($uri){
		if (empty(self::$sections[$uri])){
			self::$sections[$uri] = null;
			if ($config = self::GetConfig($uri)){
				// Наследование конфигурации
				if (isset($config['extends'])){
					$extends = self::GetConfig($config['extends']);
					$config = array_replace($extends, $config);
				}
				// Создание экземпляара секции
				if (isset($config['class']) && Classes::IsExist(trim($config['class'],'\\'))){
					self::$sections[$uri] = new $config['class']($config);
				}
			}
		}
		return self::$sections[$uri];
	}

	/**
	 * Возвращает объект по пути на него
	 * Путь может указывать как на собственный объект, так и на любой внешний.
	 * @param $uri
	 * @return \Engine\Entity|null Экземпляр объекта, если найден или null, если не найден
	 */
	static function Object($uri){
		if (is_string($uri)){
			if ($uri==='') return self::Root();
			// Определеяем, в какой секции начать поиск.
			// Для этого берется начало адреса
			$names = explode('/', $uri, 2);
			if ($s = self::Section($names[0])){
				// Поиск выполнит секция
				return $s->read($uri);
			}
		}
		return null;

	}

	/**
	 * Возвращает корневой объект системы (объект проекта)
	 * @return \Engine\Entity Экземпляр объекта
	 */
	static function Root(){
		return new Root(array('uri'=>'', 'value'=>null));
	}

	/**
	 * Создание объекта данных из атрибутов
	 * @param $attribs
	 * @return \Engine\Entity
	 */
	static function MakeObject($attribs){
		if (isset($attribs['uri']) && !empty($attribs['logic'])){
			// Свой класс
			$names = F::SplitRight('/', $attribs['uri']);
			Classes::AddProjectClasse($attribs['uri'].'/'.$names[1].'.php', $attribs['logic']);
			$obj = new $attribs['logic']($attribs);
		}else
		if (!empty($attribs['proto']) && ($proto = self::Object($attribs['proto']))){
			// Класс прототипа
			$class = get_class($proto);
			$obj = new $class($attribs);
		}else{
			// Базовый класс
			$obj = new Entity($attribs);
		}
		return $obj;
	}
}
