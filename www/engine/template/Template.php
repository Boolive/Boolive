<?php
/**
 * Шаблонизация
 *
 * @version 2.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Engine;

use Engine\Error,
	Engine\Data;

class Template{
	/** @const  Файл конфигурации секци */
	const CONFIG_FILE = 'config.templates.php';
	/** @var array Массив названий классов - шаблонизаторов */
	static private $engines;

	/**
	 * Загрузка шаблонизаторов
	 */
	static private function LoadEngines(){
		if (!isset(self::$engines)){
			self::$engines = array();
			if (file_exists(DIR_SERVER.self::CONFIG_FILE)){
				include DIR_SERVER.self::CONFIG_FILE;
				if (isset($config)) self::$engines = $config;
			}
		}
	}

	/**
	 * Возвращает шаблонизатор для указанного объекта (контроллера/виджета)
	 * @param \Engine\Entity $entity
	 * @return
	 */
	static function getEngine($entity){
		self::LoadEngines();
		$file = $entity->getFile();
		foreach (self::$engines as $pattern => $engine){
			if (fnmatch($pattern, $file)){
				if (is_string($engine)){
					self::$engines[$pattern] = new $engine();
				}
				return self::$engines[$pattern];
			}
		}
		return null;
	}

	/**
	 * Создание текста из шаблона
	 * В шаблон вставляются переданные значения
	 * При обработки шаблона могут довыбираться значения из $entity и создаваться команды в $commands
	 * @param \Engine\Entity $entity
	 * @param array $v
	 * @throws Error
	 * @return string
	 */
	static function Render($entity, $v = array()){
		if ($engine = self::getEngine($entity)){
			return $engine->render($entity, $v);
		}else{
			throw new Error(array('Template engine for entity "%s" not found ', $entity['uri']));
		}
	}
}