<?php
/**
 * Модуль данных
 *
 * @link http://boolive.ru/createcms/data-and-entity
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Engine;

use Engine\Classes;

class Data {
	/** @const  Файл конфигурации секци */
	const CONFIG_FILE = 'config.sections.php';
	/** @var array Загруженная конфигурация секция */
	private static $config;
	/** @var array Экземплярв используемых секций */
	private static $sections;
	/** @var array Загруженные объекты */
	private static $buffer = array();

	/**
	 * Возвращает объект по пути на него
	 * Путь может указывать как на собственный объект, так и на любой внешний.
	 * @todo Кодировать язык и владельца в URI
	 * @param string $uri URI объекта
	 * @param string $lang Код языка из 3 символов. Если не указан, то выбирается общий
	 * @param int $owner Код владельца. Если не указан, то выбирается общий
	 * @param null|int $date Дата создания (версия). Если не указана, то выбирается актуальная
	 * @param null|bool $is_history Объект в истории (true) или нет (false) или без разницы (null). Если указана дата, то всегда null
	 * @param bool $virtual
	 * @return \Engine\Entity|null Экземпляр объекта, если найден или null, если не найден
	 */
	static function Object($uri, $lang = '', $owner = 0, $date = null, $is_history = false, $virtual = false){
		$object = null;
		if (is_string($uri)){
			if ($uri==='') return self::MakeObject(array('uri'=>'', 'value'=>null, 'is_logic' => file_exists(DIR_SERVER_PROJECT.'site.php')));
			// Опредление секции объекта и поиск объекта в секции
			if ($s = self::Section($uri, true)){
				$object = $s->read($uri, $lang, $owner, $date, $is_history);
			}
			// Если объект не найден и нужно искать виртуального, то ищем
			if (!isset($object) && $virtual){
				// Поиск не виртуального родителя по всей глубине
				$parent_uri = $uri;
				do{
					$parent_uri = mb_substr($parent_uri, 0, mb_strrpos($parent_uri, '/'));
					if ($s = self::Section($parent_uri, true)){
						$parent = $s->read($parent_uri, '', 0, null);
					}
				}while(!isset($parent)&&!empty($parent_uri));
				// Если родитель найден и у него есть прототип, то ищем свой объект у прототипа
				if (isset($parent['proto'])){
					$proto_uri = $parent['proto'].mb_substr($uri, mb_strlen($parent_uri));
					if ($proto = self::Object($proto_uri, $lang, $owner, null, null, true)){
						$object = $proto->birth();
						$object['uri'] = $uri;
					}
				}
			}
		}
		return $object;
	}

	/**
	 * Взвращает экземпляр секции, назначенную для подчиенных объекта
	 * Если секции на указнный объект не назначена, то возвращается null
	 * @param $uri Путь на объект
	 * @param $self Признак, искать секцию объекта (true) или его подчиненных (false)?
	 * @param bool $strong Признак, искать точное указание на uri (true) или учитывать подчиенность (false)
	 * @return \Engine\Section|null Экземпляр секции, если имеется или null, если нет
	 */
	static function Section($uri, $self, $strong = false){
		if ($self) $uri = mb_substr($uri, 0, mb_strrpos($uri, '/'));
		if (empty(self::$sections[$uri])){
			self::$sections[$uri] = null;
			$find_uri = $uri;
			while (!($config = self::GetConfig($find_uri)) && !$strong && !empty($find_uri)){
				$find_uri = mb_substr($find_uri, 0, mb_strrpos($find_uri, '/'));
			}
			if ($config){
				if (empty(self::$sections[$find_uri])){
					// Наследование конфигурации
					if (isset($config['extends'])){
						$extends = self::GetConfig($config['extends']);
						$config = array_replace($extends, $config);
					}
					// Создание экземпляара секции
					if (isset($config['class']) && Classes::IsExist(trim($config['class'],'\\'))){
						self::$sections[$find_uri] = new $config['class']($config);
					}
				}
				self::$sections[$uri] = self::$sections[$find_uri];
			}
		}
		return self::$sections[$uri];
	}

	/**
	 * Создание объекта данных из атрибутов
	 * @param $attribs
	 * @throws \ErrorException
	 * @return \Engine\Entity
	 */
	static function MakeObject($attribs){
		if (isset($attribs['uri']) && !empty($attribs['is_logic'])){
			try{
				// Имеется свой класс?
				if ($attribs['uri']===''){
					$path = 'site.php';
					$class = 'site';
				}else{
					$names = F::splitRight('/', $attribs['uri']);
					$class = str_replace('/', '\\', trim($names[0],'/'));
					if (!empty($class)) $class.='\\';
					$class.=$names[1];
					$path = $attribs['uri'].'/'.$names[1].'.php';
				}
				Classes::AddProjectClasse($path, $class);
				// Проверяем существование класса
				if (Classes::IsExist($class)){
					return new $class($attribs);
				}else{

				}
			}catch(\ErrorException $e){
				// Если файл не найден, то будет использовать класс прототипа или Entity
				if ($e->getCode() != 2) throw $e;
			}
		}
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

	/**
	 * Парсинг URI.
	 * Возвращается чистый URI, язык и код владельца
	 * @param string $uri URI, в котором содержится язык и код владельца
	 * @return array
	 */
	static function getURIInfo($uri){
		$uri = F::splitRight('/', $uri);
		$names = F::explode('@', $uri[1], -3);
		return array(
			'uri' => $uri[0].'/'.$names[0],
			'owner' => isset($names[1]) && is_numeric($names[1]) ? $names[1] : 0,
			'lang' => isset($names[2])? $names[2]: (isset($names[1]) && !is_numeric($names[1])? $names[1] : '')
		);
	}

	/**
	 * Создание полного URI, в котором содержится сам uri, язык и код владельца
	 * @param $path Чистый URI
	 * @param string $lang Код языка из 3 символов
	 * @param int $owner Код владельцы числом
	 * @return string
	 */
	static function makeURI($path, $lang = '', $owner = 0){
		if ($owner) $path.='@'.$owner;
		if ($lang) $path.='@'.$lang;
		return $path;
	}

	/**
	 * Конфигурация всех секций или секции по URI
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

	public function BufferAdd($object){
		if (isset(self::$buffer['uri'])){
			self::$buffer[$object['uri']] = $object;
		}
	}

	public function BufferGet($uri){
		return isset(self::$buffer['uri'])? self::$buffer[$uri] : null;
	}

	public function BufferRemove($uri){
		unset(self::$buffer[$uri]);
	}
}
