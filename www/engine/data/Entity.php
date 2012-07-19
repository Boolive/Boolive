<?php
/**
 * Сущность
 * Базовая логика для объектов модели данных.
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Engine;

use ArrayAccess, IteratorAggregate, ArrayIterator, Countable,
	Engine\Values,
	Engine\Error,
	Engine\Data,
	Engine\Trace,
	Engine\File,
	Exception;

class Entity implements ITrace, IteratorAggregate, ArrayAccess, Countable{
	/** @var array Атрибуты */
	protected $_attribs;
	/** @var array Подчиненные объекты (выгруженные из бд или новые, то есть не обязательно все существующие) */
	protected $_children = array();
	/** @var \Engine\Section Экземпляр секции, которой реализуется сохранение и выбор себя (атрибутов) */
	protected $_section_self;
	/** @var \Engine\Section Экземпляр секции, которой реализуется сохранение и выбор подчиенных объектов */
	protected $_section_children;
	/** @var \Engine\Rule Правило для проверки атрибутов */
	protected $_rule;
	/** @var \Engine\Entity Экземпляр прототипа */
	protected $_proto = false;
	/** @var \Engine\Entity Экземпляр родителя */
	protected $_parent = false;
	/** @var bool Принзнак, объект в процессе сохранения? */
	protected $_is_saved = false;
	/** @var bool Признак, виртуальный объект или нет. Если объект не сохранен в секции, то он виртуальный */
	protected $_virtual = true;
	/** @var bool Признак, изменены ли атрибуты объекта */
	protected $_changed = false;
	/** @var bool Признак, проверен ли объект или нет */
	protected $_checked = false;
	/** @var string uri родителя */
	protected $_parent_uri = null;
	/** @var string Имя объекта, определенное по uri */
	protected $_name = null;
	/**
	 * Признак, требуется ли подобрать уникальное имя перед сохранением или нет?
	 * Также означает, что текущее имя (uri) объекта временное
	 * Если строка, то определяет базовое имя, к кторому будут подбираться числа для уникальности
	 * @var bool|string */
	public $_rename = false;

	public function __construct($attribs = array(), $section = null){
		$this->_attribs = $attribs;
		$this->_section_self = $section;
	}

	/**
	 * Установка правила на атрибуты
	 */
	protected function defineRule(){
		$this->_rule = Rule::arrays(array(
				'uri'		 => Rule::uri()->max(255)->required(), // URI - идентификатор объекта. Уникален в рамках проекта
				'lang'		 => Rule::string()->max(3)->default(0)->required(), // Язык (код)
				'owner'		 => Rule::int()->default(0)->required(), // Владелец (код)
				'date'		 => Rule::int(), // Дата создания в секундах
				'order'		 => Rule::any(Rule::null(), Rule::int()), // Порядковый номер. Уникален в рамках родителя
				'is_history' => Rule::bool()->int()->default(0)->required(), // В истории или нет
				'is_delete'	 => Rule::bool()->int(), // Удаленный или нет
				'is_hidden'	 => Rule::bool()->int(), // Скрытый или нет
				'is_logic'	 => Rule::bool()->int(), // Имеется ли у объекта свой класс в его директории. Имя класса по uri
				'is_file'	 => Rule::bool()->int(), // Связан ли с файлом (значение = имя файла). Путь на файл по uri
				'proto'		 => Rule::any(Rule::uri()->max(255), Rule::null()), // URI прототипа
				'value'	 	 => Rule::any(Rule::null(),	Rule::string()), // Значение любой длины
				// Сведения о загружаемом файле. Не является атрибутом объекта
				'file'		 => Rule::arrays(array(
									'tmp_name'	=> Rule::string()->more(0)->required(), // Путь на связываемый файл
									'name'		=> Rule::ospatterns('*.*')->required(), // Имя файла, из которого будет взято расширение
									'size'		=> Rule::int(), // Размер в байтах
									'error'		=> Rule::in(0) // Код ошибки. Если 0, то ошибки нет
								)
				)
			)
		);
	}

	/**
	 * Возвращает правило на атрибуты
	 * @return Rule
	 */
	public function getRule(){
		if (!isset($this->_rule)) $this->defineRule();
		return $this->_rule;
	}

	/**
	 * Cекции объекта
	 * Секцией реализуется сохранение и выборка атрибутов объекта
	 * @return \Engine\Section|null
	 */
	public function sectionSelf(){
		if (empty($this->_section_self) && $parent = $this->parent()){
			$this->_section_self = $parent->sectionChildren();
		}
		return $this->_section_self;
	}

	/**
	 * Секция подчиненных объектов
	 * Секцией реализуется сохранение и выборка подчиненных объектов
	 * Секция подчиненных может совпадать с секцией самого объекта
	 * @return \Engine\Section
	 */
	public function sectionChildren(){
		if (empty($this->_section_children)){
			// Назначена ли секция для подчиненных?
			$this->_section_children = Data::Section($this['uri']);
			if (empty($this->_section_children)){
				// Используем свою секцию
				$this->_section_children = $this->sectionSelf();
			}
		}
		return $this->_section_children;
	}

	#################################################
	#                                               #
	#            Управление атрибутами              #
	#                                               #
	#################################################

	/**
	 * Получение атрибута или подчиненного объекта по имени
	 * Если объекта нет, то он НЕ выбирается из секции, а возвращается null
	 * @example $sub = $obj['sub'];
	 * @param string $name Имя атрибута
	 * @return mixed
	 */
	public function offsetGet($name){
		if ($this->offsetExists($name)){
			if ($name == 'value' && !empty($this->_attribs['is_file'])){
				if (mb_substr($this->_attribs[$name], mb_strlen($this->_attribs[$name])-6) == '.value'){
					try{
						$this->_attribs[$name] = file_get_contents(DIR_SERVER_PROJECT.$this['uri'].'/'.$this->_attribs[$name]);
					}catch(Exception $e){
						$this->_attribs[$name] = '';
					}
					$this->_attribs['is_file'] = false;
				}
			}
			return $this->_attribs[$name];
		}
		return null;
	}

	/**
	 * Установка значений атриубту
	 * @example $object[$name] = $value;
	 * @param string $name Имя атрибута
	 * @param mixed $value Значение
	 */
	public function offsetSet($name, $value){
		if (!$this->offsetExists($name) || $this->offsetGet($name)!=$value){
			// Если не виртуальный, то запретить менять uri, lang, owner, date
			//if (!$this->_virtual && in_array($name, array('uri', 'lang', 'owner', 'date'))) return;

			if ($name == 'proto'){
				$this->_proto = null;
			}else
			if ($name == 'uri'){
				// Обновление uri текущих подчиненных
				if ($this->offsetExists($name)){
					// Удаление себя из текущего родителя, так как родитель поменяется
					if ($this->_parent instanceof Entity){
						$this->_parent->offsetUnset($this->getName());
						$this->_parent = null;
					}
					$this->updateName($value);
				}
				$this->_section_children = null;
				$this->_section_self = null;
				$this->_name = null;
				$this->_parent_uri = null;
				$this->_virtual = true; // @todo Возможно, объект с указанным uri существует и он не виртуальный
			}
			$this->_attribs[$name] = $value;
			$this->_changed = true;
			$this->_checked = false;
		}
	}

	/**
	 * Удаление атрибута
	 * @param string $name Имя атрибута
	 */
	public function offsetUnset($name){
		if (!$this->offsetExists($name)) unset($this->_attribs[$name]);
		$this->_changed = true;
		$this->_checked = false;
	}

	/**
	 * Проверка существования атрибута
	 * @param string $name Имя атрибута
	 * @return bool
	 */
	public function offsetExists($name){
		return array_key_exists($name, $this->_attribs);
	}

    /**
     * Проверка, атрибут отсутсвует или его значение неопредлено?
     * @param string $name Имя атрибута
     * @return bool
     */
	public function offsetEmpty($name){
		return empty($this->_attribs[$name]);
	}

	/**
	 * Замена всех атрибутов новыми значениями
	 * @param array $attribs Новые значения атрибутов
	 */
	public function exchangeAttribs($attribs){
		$this->_attribs = array();
		$this->updateAttribs($attribs);
	}

	/**
	 * Обновление атриубтов на соответсвующие значения $input
	 * @param array $attribs Новые значения атрибутов
	 */
	public function updateAttribs($attribs){
		if (is_array($attribs)){
			foreach ($attribs as $key => $value){
				$this->offsetSet($key, $value);
			}
		}
	}

	/**
	 * Каскадное обновление URI подчиненных на основании своего uri
	 * Обновляются uri только выгруженных/присоединенных на данный момент подчиенных
	 * @param $uri Свой новый URI
	 */
	protected function updateName($uri){
		$this->_attribs['uri'] = $uri;
		foreach ($this->_children as $child_name => $child){
			/* @var \Engine\Entity $child */
			$child->updateName($uri.'/'.$child_name);
		}
	}

	#################################################
	#                                               #
	#     Управление подчиненными объектами         #
	#                                               #
	#################################################

	/**
	 * Получение подчиненного объекта по имени
	 * Если объекта нет, то будет выгружен из секции или создан виртуальный
	 * @example $sub = $obj->sub;
	 * @param string $name
	 * @return array|Values|void
	 */
	public function __get($name){
		if (isset($this->_children[$name])){
			return $this->_children[$name];
		}else{
			$uri = $this['uri'].'/'.$name;
			// Если объекта нет в секции, то создается виртуальный
			if (!($s = $this->sectionChildren())||!($obj = $s->read($uri, $this['lang'], $this['owner']))){
				// Поиск прототипа для объекта
				// Прототип тоже может оказаться виртуальным!
				if ($proto = $this->proto()){
					$proto = $proto->{$name};
				}
				if ($proto){
					$obj = $proto->birth();
				}else{
					$obj = new Entity(array('uri'=>$uri, 'lang'=>$this['lang'], 'owner'=>$this['owner']));
				}
			}
			$this->__set($name, $obj);
			return $obj;
		}
	}

	/**
	 * Установка подчиенного объекта
	 * Если $value не является объектом, то выполняется установка атрибута 'value' подчиненному объекту.
	 * Если подчиненного с именем $name ещё нет, то будет создан новый.
	 * Если $value является объектом, но uri отличяющийся от uri родителя + $name, то будет создан новый
	 * объект, прототипированием от $value.
	 * Если подчиенный с именем $name уже есть, он будет заменен
	 * @example $object->sub = $sub;
	 * @param $name
	 * @param $value
	 */
	public function __set($name, $value){
		if ($value instanceof Entity){
			/** @var \Engine\Entity $value */
			// Если имя неопределенно, то потрубуется подобрать уникальное автоматически при сохранении
			// Перед сохранением используется временное имя
			if (is_null($name)){
				$name = uniqid('rename');
				$rename = 'entity';
			}
			// Если у объект есть uri и он отличается от необходимого, то прототипируем объект
			if (isset($value->_attribs['uri']) && $value->_attribs['uri']!= $this->_attribs['uri'].'/'.$name){
				// В качестве базового имени - имя прототипа.
				if (isset($rename)) $rename = $value->getName();
				$value = $value->birth();
			}
			// Установка uri для объекта, если есть свой uri
			if (isset($this['uri'])) $value['uri'] = $this['uri'].'/'.$name;
			if (isset($rename)) $value->_rename = $rename;
			$value->_parent = $this;
			$this->_children[$name] = $value;
		}else{
			// Установка значения для подчиненного
			$this->__get($name)->offsetSet('value', $value);
			return;
		}
	}

	/**
	 * Проверка, имеется ли подчиенный с именем $name в списке выгруженных?
	 * @example $result = isset($object->sub);
	 * @param $name Имя подчиненного объекта
	 * @return bool
	 */
	public function __isset($name){
		return isset($this->_children[$name]);
	}

	/**
	 * Удаление из списка выгруженных подчиенного с именем $name
	 * @example unset($object->sub);
	 * @param $name Имя подчиненного объекта
	 */
	public function __unset($name){
		unset($this->_children[$name]);
	}

	/**
	 * Поиск подчиенных объектов
	 * @todo По умолчанию указать язык и владельца
	 * @param array $cond Услвоие поиска
	 * <code>
	 * $cond = array(
	 *   'where' => '', // Условие на атрибуты объекта. Условие как в SQL на колонки таблицы.
	 *   'values' => array(), // Массив значений для вставки в условие вместо "?"
	 *   'order' => '', // Способ сортировки. Задается как в SQL, например: `order` DESC, `value` ASC
	 *   'count' => 0, // Количество выбираемых объектов
	 *   'start' => 0 // Смещение от первого найденного объекта, с которого начинать выбор
	 * );
	 * </code>
	 * @param bool $load Признак, загрузить (true) иле нет (false) найденные объекты в список подчиненных?
	 * @return array
	 */
	public function find($cond = array(), $load = false){
		if ($s = $this->sectionChildren()){
			if (!empty($cond['where'])){
				$cond['where'].=' AND uri like ? AND level=?';
			}else{
				$cond['where'] = 'uri like ? AND level=?';
			}
			$cond['values'][] = $this['uri'].'/%';
			$cond['values'][] = $this->getLevel()+1;
			$results = $s->select($cond);
			if ($load)	$this->_children = $results;
			return $results;
		}
		return array();
	}

	/**
	 * Поиск подчиненных объектов с учетом унаследованных
	 * @todo Сортировка между своими и унаследованными подчиненными по любым атрибутам (сделано только по order)
	 * @todo Ограничение количества выборки..
	 * @param array $cond Услвоие поиска
	 * <code>
	 * $cond = array(
	 *   'where' => '', // Условие на атрибуты объекта. Условие как в SQL на колонки таблицы.
	 *   'values' => array(), // Массив значений для вставки в условие вместо "?"
	 *   'order' => '', // Способ сортировки. Задается как в SQL, например: `order` DESC, `value` ASC
	 *   'count' => 0, // Количество выбираемых объектов
	 *   'start' => 0 // Смещение от первого найденного объекта, с которого начинать выбор
	 * );
	 * </code>
	 * @param bool $load Признак, загрузить (true) иле нет (false) найденные объекты в список подчиненных?
	 * @return array
	 */
	public function findAll($cond = array(), $load = false){
		$results = array();
        $list = array();
        $object = $this;
        $names = array();
		$proto = array();
		$prototype = false;
        do{
            // Поиск у себя и у всех наследуемых по цепочке объектов
			$sub = $object->find($cond);
			foreach ($sub as $child){
				/** @var \Engine\Entity $child */
				$name = $child->getName();
				// Если не удален, не скрыт, существует, ещё не выбран с таким же именем и нет среди прототипов
				if (!isset($names[$name]) && !isset($proto[$child['uri']])){
                    if ($prototype){
						// Объект виртуальный для $this, поэтому прототипируем его
						$child = $child->birth();
						$child['uri'] = $this['uri'].'/'.$name;
					}
					// Добавляем с учетом порядкового номера
					$list[(int)$child['order']][] = $child;
                }
                $names[$name] = true;
				$proto[$child['proto']] = true;
			}
            $object = $object->proto();
			$prototype = true;
        }while($object);
		// Сортировка групп
		if (!empty($cond['order']) && preg_match('/`order`\s*(ASC|DESC)?/iu', $cond['order'], $math)){
			if (empty($math[1]) || strtolower($math[1])!='desc'){
				ksort($list, SORT_NUMERIC);
			}else{
				krsort($list, SORT_NUMERIC);
			}
		}
		// Слияние групп в один массив
        foreach ($list as $objects){
            for ($i=sizeof($objects)-1; $i>=0; --$i){
                $results[$objects[$i]->getName()] = $objects[$i];
            }
        }
		if ($load)	$this->_children = $results;
        return $results;
	}

	/**
	 * Количество подчиненных в списке выгруженных
	 * @example
	 * $cnt = count($object);
	 * $cnt = $object->count();
	 * @return int
	 */
	public function count(){
		return count($this->_children);
	}

	/**
	 * Итератор по выгруженным подчиненным объектам
	 * @return \ArrayIterator|\Traversable
	 */
	public function getIterator(){
        return new ArrayIterator($this->_children);
    }

	/**
	 * Список выгруженных подчиненных
	 * @return array
	 */
	public function getChildren(){
		return $this->_children;
	}

	#################################################
	#                                               #
	#            Управление объектом                #
	#                                               #
	#################################################

	/**
	 * Проверка объекта
	 * @param null $errors Возвращаемый объект ошибки
	 * @return bool Признак, корректен объект (true) или нет (false)
	 */
	public function check(&$errors = null){
		if ($this->_checked) return true;
		// "Контейнер" для ошибок по атрибутам и подчиненным объектам
		$errors = new Error('Неверный объект', $this['uri']);
		// Проверка и фильтр атрибутов
		$attribs = new Values($this->_attribs);
		$this->_attribs = $attribs->get($this->getRule(), $error);
		if ($error){
			$errors->_attribs->add($error->getAll());
		}
		// Проверка подчиненных
		foreach ($this->_children as $name => $child){
			$error = null;
			/** @var \Engine\Entity $child */
			if (!$child->check($error)){
				$errors->_children->add($error);
			}

		}
		// Проверка родителем.
		if ($p = $this->parent()) $p->checkChild($this, $errors);
		// Если ошибок нет, то удаляем контейнер для них
		if (!$errors->isExist()){
			$errors = null;
			return $this->_checked = true;
		}
		return false;
	}

	/**
	 * Проверка подчиненного в рамках его родителей
	 * Возможно обращение к родителям выше уровнем, чтобы объект проверялся в ещё более глобальном окружении,
	 * например для проверки уникальности значения по всему разделу/базе.
	 * @param \Engine\Entity $child Проверяемый подчиненный
	 * @param \Engine\Error $error Объект ошибок подчиненного
	 * @return bool Признак, корректен объект (true) или нет (false)
	 */
	protected function checkChild(Entity $child, Error $error){
		/** @example
		 * if ($child->getName() == 'bad_name'){
		 *     // Так как ошибка из-за атрибута, то добавляем в $error->_attribs
		 *     // Если бы проверяли подчиненного у $child, то ошибку записывали бы в $error->_children
		 *	   $error->_attribs->name = new Error('Недопустимое имя', 'impossible');
		 *     return false;
		 * }
		 */
		return true;
	}

	/**
	 * Сохранение объекта в секции
	 * @throws
	 */
	public function save($history = true, &$error = null){
		if (!$this->_is_saved && $this->check($error)){
			try{
				$this->_is_saved = true;
				// Если создаётся история, то нужна новая дата
				if ($history) $this->_attribs['date'] = time();
				// Сохранение себя
				if ($this->_changed){
					if ($s = $this->sectionSelf()){
						$s->put($this);
						$this->_virtual = false;
						$this->_changed = false;
					}
				}
				// @todo Если было переименование из-за _rename, то нужно обновить uri подчиненных
				// Сохранение подчиененных
				$children = $this->getChildren();
				foreach ($children as $child){
					/** @var \Engine\Entity $child */
					$child->save();
				}
				$this->_is_saved = false;
			}catch (Exception $e){
				$this->_is_saved = false;
				throw $e;
			}
		}
	}

	/**
	 * Удаление объекта
	 * Объект помечается как удаленный
	 * @todo Установка атрибута is_delete и сохранение в секции без проверок и прочих сохранений
	 */
	public function delete(){
		$this['is_delete'] = true;
		return $this;
	}

	/**
	 * Создание нового объекта прототипированием от себя
	 * @return \Engine\Entity
	 */
	public function birth(){
		$class = get_class($this);
		return new $class(array('proto'=>Data::makeURI($this['uri'], $this['lang'], $this['owner'])));
	}

	/**
	 * Родитель объекта
	 * @return \Engine\Entity|null
	 */
	public function parent(){
		if ($this->_parent === false){
			$this->_parent = Data::Object($this->getParentUri());
		}
		return $this->_parent;
	}

	/**
	 * Прототип объекта
	 * @return \Engine\Entity|null
	 */
	public function proto(){
		if ($this->_proto === false){
			if (isset($this['proto'])){
				$info = Data::getURIInfo($this['proto']);
				$this->_proto = Data::Object($info['uri'], $info['lang'], $info['owner']);
			}else{
				$this->_proto = null;
			}
		}
		return $this->_proto;
	}

	/**
	 * При обращении к объекту как к скалярному значению (строке), возвращается значение атрибута value
	 * @example
	 * print $object;
	 * $value = (string)$obgect;
	 * @return mixed
	 */
	public function __toString(){
		$value = $this->offsetGet('value');
		if (is_null($value) && ($proto = $this->proto())){
			$value = $proto->__toString();
		}
		return (string)$value;
	}

	/**
	 * Вызов несуществующего метода
	 * Если объект внешний, то вызов произведет модуль секции объекта
	 * @param string $method
	 * @param array $args
	 * @return null|void
	 */
	public function __call($method, $args){
		if ($s = $this->sectionSelf()){
			$s->call($method, $args);
		}
	}

	/**
	 * URI родителя
	 * @return string|null Если родителя нет, то null. Пустая строка является корректным uri
	 */
	public function getParentUri(){
		if (!isset($this->_parent_uri)){
			if (isset($this['uri'])){
				$names = F::SplitRight('/',$this['uri']);
				$this->_parent_uri = $names[0];
				$this->_name = $names[1];
			}else
			if (isset($this->_parent)){
				$this->_parent_uri = $this->_parent['uri'];
			}else{
				$this->_parent_uri = null;
			}
		}
		return $this->_parent_uri;
	}

	/**
	 * Имя объекта
	 * @return string|null
	 */
	public function getName(){
		if (!isset($this->_parent_uri)){
			if (isset($this['uri'])){
				$names = F::SplitRight('/',$this['uri']);
				$this->_parent_uri = $names[0];
				$this->_name = $names[1];
			}
		}
		return $this->_name;
	}

	/**
	 * Уровень вложенности
	 * Вычисляется по uri
	 */
	public function getLevel(){
		return mb_substr_count($this['uri'], '/')+1;
	}

	/**
	 * Дмректория объекта
	 * @param bool $root Признак, возвращать путь от корня сервера или от web директории (www)
	 * @return string
	 */
	public function getDir($root = false){
		if ($root){
			return DIR_SERVER_PROJECT.ltrim($this['uri'].'/','/');
		}else{
			return DIR_WEB_PROJECT.ltrim($this['uri'].'/','/');
		}
	}

	/**
	 * Путь на файл, если объект ассоциирован с файлом.
	 * Если значение null, то информация берется от прототипа
	 * @param bool $root
	 * @return null|string
	 */
	public function getFile($root = false){
		if ($this['is_file']){
			$file = $this->getDir($root);
			if ($this->_attribs['is_history']) $file.='_history_/'.$this->_attribs['date'].'_';
			return $file.$this->_attribs['value'];
		}else
		if (!isset($this->_attribs['value']) && $proto = $this->proto()){
			return $proto->getFile($root);
		}
		return null;
	}

	/**
	 * Проверка, является ли объект файлом.
	 * Проверяется с учетом прототипа
	 * @return bool
	 */
	public function isFile(){
		return !empty($this['is_file']) || (!isset($this['value']) && ($proto = $this->proto()) && $proto->isFile());
	}

	/**
	 * Сравнение объектов по uri
	 * @param \Engine\Entity $entity
	 * @return bool
	 */
	public function isEqual($entity){
		if (!$entity) return false;
		return ($this->offsetExists('uri') && $this->offsetGet('uri') == $entity->offsetGet('uri'));
	}

	/**
	 * Признак, изменены атрибуты объекта или нет
	 * @return bool
	 */
	public function isChenged(){
		return $this->_changed;
	}

	/**
	 * Признак, виртуальный объект или нет. Если объект не сохранен в секции, то он виртуальный
	 * @return bool
	 */
	public function isVirtual(){
		return $this->_virtual;
	}

	/**
	 * Признак, находится ли объект в процессе сохранения?
	 * @return bool
	 */
	public function isSaved(){
		return $this->_is_saved;
	}



//	/**
//	 * Выбор подчиненного по его относительному пути
//	 * @example $child = $obj->getSubByPath('sub/sub2/child'); Равноценно: $child = $obj->sub->sub2->child;
//	 * @param string $path Относительный путь на объект
//	 * @param bool $exist Признак, возвращать только сущесвтующий объект. Если false и подчиненного нет, то будет возвращен новый объект
//	 * @return Entity|null
//	 */
//	public function getSubByPath($path, $exist = false){
//		if (($path = trim($path, '/ '))){
//			$path = explode('/', $path);
//			$cnt = sizeof($path);
//			$i = 0;
//			$obj = $this;
//			while ($i < $cnt && $obj){
//				$obj = $obj->{$path[$i]};
//				if ($obj && $exist && $obj->isNew()) return null;
//				$i++;
//			}
//			return $obj;
//		}
//		return null;
//	}
//
//	/**
//	 * Проверка объекта
//	 * Проверяются атрибуты, подчиненные объекты и информируется родитель об изменении или удалении его подчиненного.
//	 * Если родитель при проверке вызовет исключение, то этот объект будет считаться с ошибкой.
//	 * Проверяются только подгруженные подчиненные, а не все имеющеся у объекта в БД.
//	 * @param bool $exchange_attr Признак, заменить ли атрибуты экземпляра на отфильтрованные после проверки?
//	 * @return bool False, если есть ошибки в атрибутах
//	 */
//	public function check($exchange_attr = true){
//		// @todo
//		$attr = (array)$this;
//		// Ссылка на родителя
//		if (($parent = $this->parent())&&!$parent->isNew()) $attr['parent'] = $parent['section'].':'.$parent['id'];
//		// Ссылка на прототип
//		if (($proto = $this->proto())&&!$proto->isNew()) $attr['proto'] = $proto['section'].':'.$proto['id'];
//		// Секция по умолчанию
//		if (empty($attr['section']) && $parent)	$attr['section'] = $parent['section'];
//
//		if ($this->isNew()){
//			// Имя объекта по умолчанию
//			if (empty($attr['name'])) $attr['name'] = 'entity';
//			// Даты создания, если не установлена вручную и объект новый
//			if (empty($attr['date_create'])) $attr['date_create'] = time();
//		}
//		// Даты изменения, если не установлена вручную
//		if (empty($attr['date_edit'])) $attr['date_edit'] = time();
//
//		// Атрибуты помещем в контейнер для фильтра
//		$attr = new Values($attr);
//		if (!($this->_errors instanceof Error)) $this->_errors = new Error(__CLASS__);
//		$this->_verified = $attr->getArray($this->_rule, $this->_errors);
//
//		// Проверка файла
//		if (isset($this->_verified['file'])){
////			$this['file'] = $attr['file']->getArray($this->_rule_file, $this->_errors);
//			// Ошибка загрузки файла
//			if ($this->_verified['file']['error'] !== 0){
//				$this->_errors->add(File::UploadErrorMmessage($this->_verified['file']['error']));
//			}else
//			if (!$this->_errors->isExist()){
//				// Проверка типа файла
//				if (!$this->isAssociated($this->_verified['file']['tmp_name'])){
//					$this->_errors->add(array('Неподдерживаемый тип файла (%s)', $this->_verified['file']['tmp_name']));
//				}
//			}
//		}
//		// Замена текущих атрибутов на отфильтрованные
//		if ($exchange_attr) $this->updateArray($this->_verified);
//
//		// Есть ли ошибки в атрибутах?
//		$no_errors = !$this->_errors->isExist();
//
//		// Проверка подчиненных
//		foreach ($this->_children as $sub){
//			/** @var $sub \Engine\Entity */
//			$no_errors = $sub->check() && $no_errors;
//		}
//		try{
//			// Проверка объекта родителем
//			if (!$this->isNew()){
//				// Если родитель изменен, то информирование прошлого об удалении его подчиненноо
//				$current = Data::Get($this['section'], $this['id'], false, false);
//				$curr_parent = $current->parent();
//				if ($curr_parent){
//					if ($this['parent']!=$current['parent'] || (!$this->offsetIsEmpty('is_delete') && !$current['id_delete'])){
//						$curr_parent->onRemoveChild($this);
//					}
//				}
//			}
//			// Информирование родителя об изменении его подчиенного
//			if ($parent = $this->parent()){
//				$parent->onChangeChild($this);
//			}
//		}catch(Error $e){
//			$this->_errors->add($e);
//			$no_errors = false;
//		}
//		return $no_errors;
//	}
//
//	/**
//	 * Сохранение объекта
//	 * @param bool $save_sub Сохранять ли подчиенных?
//	 * @throws Error|null
//	 * @throws \Exception
//	 * @return void
//	 */
//	public function save($save_sub = true){
//		if (!$this->_is_on_save && $this->_can_save){
////			try{
//				$this->_is_on_save = true;
//				// Старт транзакции
////				Transaction::Begin();
//				// Сохранение прототипа, если новый
//				if (($proto = $this->proto()) && $proto->isNew()){
//					$proto->save();
//				}else
//				if (!$proto){
//					// @todo Определение прототипа от наследуемого класса
//				}
//				// Сохранение родителя, если новый
//				if (($parent = $this->parent()) && $parent->isNew()){
//					$parent->save();
//				}
//
//				// Проверка объекта
//				if (!$this->check()) throw $this->_errors;
//
//				// Сохранение, если объект новый
//				if ($this->isNew()){
//					$this->updateArray(Data::Add($this));
//				}else{
//					$this->updateArray(Data::Edit($this));
//				}
//				if ($save_sub){
//					// Сохранение подчиненных
//					foreach ($this->_sub as $sub){
//						/** @var $sub \Engine\Entity */
//						$sub->save();
//					}
//				}
//				$this->_is_on_save = false;
//				// Завершение тразакции
////				Transaction::Commit();
////			}catch(Exception $e){
////				Transaction::RollBack();
////				$this->_is_on_save = false;
////				throw $e;
////			}
//		}
//	}
//	/**
//	 * Проверка, является ли объект новым (не сохраненным в БД)
//	 * @return bool
//	 */
//	public function isNew(){
//		return $this->offsetIsEmpty('id');
//	}
//
//	/**
//	 * Признак, можно ли объект сохранять
//	 * @param bool|null $can_save Если будево значение, то выполняется установка признака в соответсвующее значение
//	 * @return bool
//	 */
//	public function canSave($can_save = null){
//		if (is_bool($can_save)){
//			$this->_can_save = $can_save;
//			if ($can_save && ($parent = $this->parent())){
//				$parent->canSave(true);
//			}
//		}
//		return $this->_can_save;
//	}
//

//
//	/**
//	 * Проверка, является ли объект подчиенным для указанного?
//	 * @param \Engine\Entity $parent
//	 * @return bool
//	 * @todo
//	 */
//	public function isChildOf($parent){
//		if (!$parent) return false;
//		if ($this['section'] == $parent['section']){
//			$key = '_id'.$parent['_level'];
//			if (isset($this[$key]) && $this[$key] == $parent['id']){
//				return true;
//			}
//		}
//		return false;
//	}
//
//	/**
//	 * Проверка, является ли объект родителем для указанного?
//	 * @param \Engine\Entity child
//	 * @return bool
//	 */
//	public function isParentOf($child){
//		if (!$child) return false;
//		return $child->isChildOf($this);
//	}
//
//	/**
//	 * Проверка, наследуется ли объект от указанного (является ли налседником)?
//	 * @param $proto
//	 * @return bool
//	 */
//	public function isHeirOf($proto){
//		if (!$proto) return false;
//		if ($this->isEqual($proto)) return true;
//		$cur = $this;
//		while ($cur){
//			if (($cur = $cur->proto()) == $proto) return true;
//		}
//		return false;
//	}
//
//	/**
//	 * Проверка, является ли объект прототипом для указанного?
//	 * @param \Engine\Entity $heir
//	 * @return mixed
//	 */
//	public function isProtoOf($heir){
//		if (!$heir) return false;
//		return $heir->isHeirOf($this);
//	}
//
//	/**
//	 * Запуск сущности
//	 * @param \Engine\Commands $commands Команды для исполнения в соответствующих сущностях
//	 * @param bool $previous Признак, были ли объекты до текущего. Если false, то не были.
//	 * @param \Engine\Input $input Входящие данные
//	 * @return void|mixed Результат выполнения контроллера
//	 */
//	public function start($commands, $previous, $input){
//		$result = '';
//		//Проверка возможности работы
//		if ($this->canWork($commands, $previous, $input)){
//			$this->_start_args = array(
//				'commands' => $commands,
//				'prviews' => false,
//				'input' => $input->getShifted($this->_params_shift)
//			);
//			//Выполнение подчиненных
//			//$sub = $this->subStart($commands, $input);
//			ob_start();
//				// Выполненеие своей работы
//				$result = $this->work($commands, array(), $input);
//				$result = ob_get_contents().$result;
//			ob_end_clean();
//		}
//		return $result;
//	}
//
//	/**
//	 * Проверка возможности работы
//	 * @param \Engine\Commands $commands Команды для исполнения в соответствующих объектах
//	 * @param bool $previous Признак, были ли объекты до текущего. Если false, то не были.
//	 * @param \Engine\Input $input Входящие данные
//	 * @param bool $check_input Признак, работать если нет ошибок во входящих данных.
//	 * @return bool Признак, может ли работать объект или нет
//	 */
//	public function canWork($commands, $previous, $input, $check_input = true){
//		$result = true;
//		if ($input instanceof Values){
//			$this->loadAllSub();
//			// Условие на URL
//			$pattern = (string)$this->url_pattern;
//			if ($pattern){
//				// Сверка с текущим URL и выборка необходимых данных из него
//				$result = $input->match($pattern, $this->_params_shift);
//			}
//			// Проверка входящих данных
//			$this->_input_filtered = $input->getArray($this->_input_rule, $this->_input_errors);
//			if ($check_input && $this->_input_errors->isExist()){
//				return false;
//			}
//		}
//		return $result;
//	}
//
//	/**
//	 * Запуск подчиненного по имени
//	 * @param $path Имя подчиненного или путь на него
//	 * @return null
//	 */
//	public function startSub($path){
//		if ($sub = $this->getSubByPath($path)){
//			$result = $sub->start($this->_start_args['commands'], $this->_start_args['prviews'], clone $this->_start_args['input']);
//			if ($result){
//				$this->_start_args['prviews'] = true;
//				return $result;
//			}
//		}
//		return null;
//	}
//
//	/**
//	 * Работа подчиненных объектов
//	 * @param \Engine\Commands $commands Команды для исполнения в соответствующих объектах
//	 * @param \Engine\Input $input Входящие данные
//	 * @return array Результаты подчиненных объектов. Ключи массива - названия объектов.
//	 */
//	public function subStart($commands, $input){
//        $results = array();
//		$list = array();
//		$object = $this;
//		$names = array();
//		do{
//			$sub = $object->loadAllSub();
//			$cnt = sizeof($sub);
//			for ($i=0; $i<$cnt; ++$i){
//				if (!in_array($sub[$i]['name'], $this->_ignore_start) &&
//					!$sub[$i]['is_hidden'] && !$sub[$i]['is_delete'] && !$sub[$i]['is_temp'] &&
//					!$sub[$i]->isNew() && !isset($names[$sub[$i]['name']])){
//					$list[(int)$sub[$i]['order']][] = $sub[$i];
//				}
//				$names[$sub[$i]['name']] = true;
//			}
//			$object = $object->proto();
//		}while($object);
//
//		ksort($list, SORT_NUMERIC);
//		foreach ($list as $key => $objects){
//			// В обратном порядке, чтоб сначала исполнялись подчиенные прототипов, а уже потом свои
//			for ($i=sizeof($objects)-1; $i>=0; --$i){
//				$out = $objects[$i]->start($commands, !empty($results), $input->getShifted($this->_params_shift));
//				if ($out){
//					$results[$objects[$i]['name']] = $out;
//				}
//			}
//		}
//		return $results;
//	}
//
//	/**
//	 * Работа объекта. Обработка запроса и формирование вывода
//	 * Результат выводится функциями echo, print или другими. Или возвращается через return
//	 * @param \Engine\Commands $commands Команды для исполнения в соответствующих объектах
//	 * @param array $v Результаты подчиненных объектов
//	 * @param \Engine\Input $input Входящие данные
//	 * @return string|void Результат работы. Вместо return можно использовать вывод строк (echo, print,...)
//	 */
//	public function work($commands, $v = array(), $input){
//		return $this['value'].implode('', $v);
//	}
//
//	/**
//	 * Путь на объект от корня сайта
//	 * Используется для определения пути на файл объекта для его чтения или создания
//	 * @param string $file_name
//	 * @param bool $find_existing
//	 * @return bool|string
//	 */
//	public function getPath($file_name = '', $find_existing = true){
//		if (!$find_existing && $this->_dir) return $this->_dir.$file_name;
//		$object = $this;
//		do{
//			if ($parent = $object->parent()){
//				$root_dir = $parent->getPath('', false);
//			}else{
//				$root_dir = $object['section'].'/';
//			}
//
////			$parent = $object;
////			$path = '';
////			while ($parent){
////				$path = $parent['name'].'/'.$path;
////				$parent = $parent->parent();
////			}
////			$path = $root_dir.$path.$file_name;
//			$path = $root_dir.$object['name'].'/'.$file_name;
//			if ($find_existing && !file_exists(DIR_PROJECT.$path)){
//				$path = false;
//				$object = $object->proto();
////				if (get_class($object) == 'Engine\\Entity'){
////					$object = null;
////				}
//			}else{
//				$object = null;
//			}
//		}while($object);
//		if ($path){
//			return $path;
//		}
//		return false;
//	}
//
//	/**
//	 * Проверка возможности ассоциировать файл с объектом
//	 * @param $file Путь на файл
//	 * @return bool
//	 */
//	public function isAssociated($file){
//		// Если указаны возможные расширения файла
//		if (!empty($this->_valid_extensions)){
//			$info = File::FileInfo($file);
//			return in_array($info['ext'], $this->_valid_extensions);
//		}
//		return true;
//	}
//
//	/**
//	 * Установка класса
//	 * Инициализация объекта, который будет ассоциирован с данным классом
//	 * @param \Engine\Values $input Данные, полученные от пользователя, если он запрашивались методом self::InstallPrepare()
//	 * @return void
//	 */
//	public function install($input){
//		$keys = $input->getKeys();
//		foreach ($keys as $key){
//			$this->{$key} = $input->get($key);
//		}
//	}
//
	/**
	 * Клонирование объекта
	 */
	public function __clone(){
		foreach ($this->_children as $name => $child){
			$this->__set($name, clone $child);
		}
	}
	/**
	 * Значения внутренных свойств объекта для трасировки при отладки
	 * @return array
	 */
	public function trace(){
		//$trace['hash'] = spl_object_hash($this);
		$trace['_attribs'] = $this->_attribs;
		$trace['_changed'] = $this->_changed;
		$trace['_virtual'] = $this->_virtual;
		$trace['_checked'] = $this->_checked;
		/*if ($this->_rename)	*/$trace['_rename'] = $this->_rename;
		//$trace['_proto'] = $this->_proto;
		//$trace['_parent'] = $this->_parent;
		$trace['_children'] = $this->_children;
		return $trace;
	}
}
