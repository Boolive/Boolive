<?php
/**
 * Входящие данные
 * Объединяет в одном контейнере суперглобальные массивы GET, POST, COOKIE, FILES, HTTP_RAW_POST_DATA, SERVER
 * Дополнительно обрабатывает их для нормализазии.
 * Из GET создаются path и args.
 * * path - элементы пути URI после адреса хоста: /param0/param1/param2
 * * args - именованные значения из части запроса URI - то, что после "?":  arg1=value&arg2=value2
 * Если система запущена из командной строки, то переданные аргументы окажутся в args
 * Для получения всех данных используется Input::all()
 * При выборки из контейнера значения фильтруются в соответствии с указанным правилом или правилом по умочланию
 * @link http://boolive.ru/createcms/processing-request
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Engine;

use Engine\Values;

/**
 * @property \Engine\Values path Элементы пути URI
 * @property \Engine\Values args Аргументы URI или командной строки
 * @property \Engine\Values GET GET данные
 * @property \Engine\Values POST POST данные
 * @property \Engine\Values FILES Загруженные файлы
 * @property \Engine\Values COOKIE Куки
 * @property \Engine\Values RAW Неформатированные данные
 * @property \Engine\Values SERVER Информация о сервере и среде исполнения
 */
class Input extends Values{
	/** @var \Engine\Input Общий контейнер всех входящих данных */
	private static $input;

	/**
	 * Активация модуля
	 * Создание общего контейнера входящих данных
	 */
	static function Activate(){
		$values = array(
			'GET' => isset($_GET)? $_GET : array(),
			'POST' => isset($_POST)? $_POST : array(),
			'FILES' => isset($_FILES)? self::normalizeFiles() : array(),
			'COOKIE' => isset($_COOKIE)? $_COOKIE : array(),
			'RAW' => empty($HTTP_RAW_POST_DATA)?'':$HTTP_RAW_POST_DATA, // Неформатированные данные
			'SERVER' => $_SERVER
		);
		// Элементы пути URI
		if (isset($values['GET']['path']) && ($path = trim($values['GET']['path'],'/ '))){
			$values['path'] = explode('/', $path);
		}else{
			$values['path'] = array();
		}
		// Аргументы из URI
		$values['args'] = array();
		if (!empty($values['GET'])){
			$list = $values['GET'];
			unset($list['path']);
			$i = 0;
			foreach ($list as $key => $value){
				if ($value !== ''){
					$values['args'][$key] = $value;
				}else{
					$values['args'][$i] = $key;
				}
				$i++;
			}
		}
		// Аргументы из консоли в get (режим CLI)
		if (empty($values['args']) && isset($_SERVER['argv'])) $values['args'] = $_SERVER['argv'];

		// Создание контейнера
		self::$input = new Input($values);
	}

	/**
	 * Все входящие данные
	 * @return \Engine\Input
	 */
	static function all(){
		return self::$input;
	}

	/**
	 * Элементы пути URI
	 * @return \Engine\Values
	 */
	static function path(){
		return self::$input->path;
	}

	/**
	 * Аргументы URI
	 * @return \Engine\Values
	 */
	static function args(){
		return self::$input->args;
	}

	/**
	 * POST данные
	 * @return \Engine\Values
	 */
	static function POST(){
		return self::$input->POST;
	}

	/**
	 * Загруженные файлы
	 * @return \Engine\Values
	 */
	static function FILES(){
		return self::$input->FILES;
	}

	/**
	 * Куки
	 * @return \Engine\Values
	 */
	static function COOKIE(){
		return self::$input->COOKIE;
	}

	/**
	 * Неформатированные данные
	 * @return \Engine\Values
	 */
	static function RAW(){
		return self::$input->RAW;
	}

	/**
	 * Информация о сервере и среде исполнения
	 * @return \Engine\Values
	 */
	static function SERVER(){
		return self::$input->SERVER;
	}

	/**
	 * Нормализация массива $_FILES в соответствии с именованием полей формы
	 * @return array
	 */
	private static function normalizeFiles(){
		// Перегруппировка элементов массива $_FILES
		$rec_to_array = function ($array, $name) use (&$rec_to_array){
			$result = array();
			foreach ($array as $key => $value){
				if (is_array($value)){
					$result[$key] = $rec_to_array($value, $name);
				}else{
					$result[$key][$name] = $value;
				}
			}
			return $result;
		};
		$result = array();
		foreach ($_FILES as $field => $data){
			$result[$field] = array();
			foreach ($data as $name => $value){
				if (is_array($value)){
					$result[$field] = F::arrayMergeRecursive($result[$field], $rec_to_array($value, $name));
				}else{
					$result[$field][$name] = $value;
				}
			}
		}
		return $result;
	}

	/**
	 * Правила по умолчанию
	 * Правило отсутствует, поэтому без явного указания правиал при выборки значений значения не получить
	 */
	protected function defineRule(){
		$this->_rule = null;
	}
}
