<?php
/**
 * Значения, требующие проверки или фильтра
 * - Способ проверки определятся правилом, которое указывается при получении значения в методах get*
 * - Правилом является объект класса \Engine\Rule
 * - Если правило не указывается, то используется правило по умолчанию.
 * - Текущее правило по умолчанию определяет массив строк любой длины.
 * - Правило по умолчанию можно переопределить в конструкторе при создании экземпляра Values
 *   или в наследуемых классах методом defineRule.
 * @version	2.3
 * @link http://boolive.ru/createcms/filter-and-check-data
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Engine;

use ArrayObject,
	Engine\Rule,
	Engine\Error,
	Engine\Check;

class Values extends ArrayObject{
	/** @var \Engine\Rule Правило по умолчанию */
	protected $_rule;
	/** @var bool Признак, были изменения значений (true) или нет (false)? */
	protected $_changed;
	/** @var bool Признак, отфильтрованы значения (true) или нет (false)? */
	protected $_filtered;
	/**
	 * Конструктор
	 * @param array|mixed $list Значения. Если не массив, то значение будет обернуто в массив
	 * @param null|array|\Engine\Rule $rule Правило проверки значений по умолчанию
	 */
	public function __construct($list = array(), $rule = null){
		if (!is_array($list)) $list = array($list);
		parent::__construct($list);
		$this->_rule = $rule;
		$this->_changed = false;
		$this->_filtered = false;
	}

	/**
	 * Установка правила проверки по умолчанию
	 * Применяется в наследуемых классах для предопредления правила
	 * @return \Engine\Rule
	 */
	protected function defineRule(){
		// Одномерный ассоциативный массив строк
		$this->_rule = Rule::arrays(Rule::string());
	}

	/**
	 * Правило проверки по умолчанию
	 * Правило используется если в аргументах методов не указывается правило
	 * @param null $name Ключ элемента, для которого требуется правило. Если не указан, то возвращается общее правило
	 * @return \Engine\Rule
	 */
	public function getRule($name = null){
		// Если правила нет по умолчанию, то пробуем его установить
		if (!isset($this->_rule)) $this->defineRule();
		// Правило на элемент
		if (isset($name)){
			if ($this->_rule instanceof Rule){
				if (isset($this->_rule->arrays)){
					// Нормализация аргументов
					$args[0] = isset($this->_rule->arrays[0])? $this->_rule->arrays[0] : null;
					$args[1] = isset($this->_rule->arrays[1])? $this->_rule->arrays[1] : $this->_rule->arrays[0];
					// Выбор правила для элемента
					if (is_array($args[0]) && isset($this->_rule->arrays[0][$name])){
						// Правило на элемент
						return $args[0][$name];
					}else{
						// Правило по умолчанию, если есть
						return $args[1] instanceof Rule ? $args[1] : null;
					}
				}
			}
			return null;
		}
		return $this->_rule;
	}

	/**
	 * Получение значения c применением правила проверки
	 * @param string|null $name Ключ элемента. Если null, то выбирается весь массив значений
	 * @param \Engine\Rule|null $rule Правило фильра/проверки значения
	 * @param \Engine\Error|null $error Объект ошибки после проверки
	 * @return mixed Отфильтрованное значение (если нет ошибок)
	 */
	public function get($name, $rule = null, &$error = null){
		// Если не указано правило и значение уже отфильтровано, то повторно фильтровать не нужно
		if (!$rule && $this->_filtered && parent::offsetExists($name)){
			return parent::offsetGet($name);
		}
		// Если правило не указано, то берём по умолчанию
		if (!$rule) $rule = $this->getRule($name);
		// Если правило определено
		if ($rule instanceof Rule){
			// Значение, которое нужно проверить и отфильтровать
			$exist = true;
			if (isset($name)){
				if (parent::offsetExists($name)){
					$value = parent::offsetGet($name);
				}else{
					$exist = false;
					$value = null;
				}
			}else{
				$value = $this;
			}
			// Отсутствие значения
			if (isset($rule->forbidden)){
				if ($exist){
					$error = new Error('NOT_FORBIDDEN');
				}
			}else{
				return Check::Filter($value, $rule, $error);
			}
		}
		$error = new Error('NO_RULE');
		return null;
	}

	/**
	 * Получение массива значений c применением правила проверки.
	 * @param \Engine\Rule|null $rule Правило проверки. Если не указано, то используется правило по умолчанию
	 * @param \Engine\Error|null $error Объект ошибки после проверки
	 * @return array Отфильтрованные значения (если нет ошибок)
	 */
	public function getArray($rule = null, &$error = null){
		return $this->get(null, $rule, $error);
	}

	/**
	 * Возвращает все значения в виде массива объектов Values
	 * @param bool $assoc
	 * @return array Ассоциативный массив объектов Values
	 */
	public function getValuesList($assoc = true){
		$r = array();
		foreach ((array)$this as $name => $value){
			if (!$value instanceof Values){
				$value = new Values($value);
			}
			if ($assoc){
				$r[$name] = $value;
			}else{
				$r[] = $value;
			}
		}
		return $r;
	}

	/**
	 * Возвращает массив ключей
	 * @return array
	 */
	public function getKeys(){
		return array_keys((array)$this);
	}

	/**
	 * Возвращает объект класса Values с элементами, ключи которых указанны в аргументе метода
	 * Объявления аргументов в методе отсутствует, так как предполагается два варианта их указания:
	 * 1. Массивом, например getChosen(array('name1', 'name2'));
	 * 2. Любым количеством скалярных аргументов, например getChosen('name1', 'name2', 'name3');
	 * @return \Engine\Values Новый объект значений с выбранными элементами
	 */
	public function getChosen(){
		$arg = func_get_args();
		if (isset($arg[0]) && is_array($arg[0])) $arg = $arg[0];
		$values = new Values(array(), $this->_rule);
		foreach ($arg as $name){
			if (parent::offsetExists($name)) $values->offsetSet($name, parent::offsetGet($name));
		}
		return $values;
	}

	/**
	 * Удяляет все значения, имена которых не указаны в аргументах этого метода
	 * Объявления аргументов в методе отсутствует, так как предполагается два варианта их указания:
	 * 1. Массивом, например choose(array('name1', 'name2'));
	 * 2. Любым количеством скалярных аргументов, например choose('name1', 'name2', 'name3');
	 * @return \Engine\Values Ссылка на себя
	 */
	public function choose(){
		$arg = func_get_args();
		if (isset($arg[0]) && is_array($arg[0])) $arg = $arg[0];
		$list = array();
		foreach ($arg as $name){
			if (parent::offsetExists($name)) $list[$name] = parent::offsetGet($name);
		}
		$this->exchangeArray($list);
		return $this;
	}

	/**
	 * Применение правила к своим элементам для фильтра их значения
	 * @param array|\Engine\Rule $rule Правило проверки и фильтра
	 * @param null $errors Ошибки после проверки
	 */
	public function filterArray($rule = null, &$errors = null){
		$values = $this->getArray($rule, $errors);
		$this->exchangeArray($values);
	}

	/**
	 * Заменяет текущий массив на другой
	 * @param mixed $list
	 * @return array
	 */
	public function exchangeArray($list){
		$this->changed(true);
		$this->_filtered = false;
		return parent::exchangeArray($list);
	}

	/**
	 * Замещение значений своих элементов соответствующими по ключам значениями из переданного массива.
	 * Если ключ есть в переданном массиве, но отсутствует у себя, то он будет создан.
	 * Если ключ присутствует только у себя, то элемент с таким ключом сохранится как есть.
	 * @param array $list Новые значения
	 */
	public function replaceArray($list){
		if ((is_array($list)||$list instanceof \ArrayAccess) &&!empty($list)){
			foreach ($list as $key => $value){
				$this->offsetSet($key, $value);
			}
			$this->changed(true);
			$this->_filtered = false;
		}
	}

	/**
	 * Объединение элементов с переданным массивом или другим объектом Values.
	 * Объединение рекурсивное
	 * @param array|\Engine\Values $list Массив или объект Values добавляемых элементов
	 */
	public function unionArray($list){
		if ($list instanceof \ArrayAccess){
			$list = (array)$list;
		}
		if (!is_array($list)){
			$list = array($list);
		}
		foreach ($list as $name => $value){
			if (is_string($name)){
				if (parent::offsetExists($name) && !is_scalar(parent::offsetGet($name))){
					$self_value = parent::offsetGet($name);
					// объединение
					if (!$self_value instanceof Values){
						$this->offsetSet($name, $self_value = new Values($self_value));
					}
					if (is_array($value) || $value instanceof Values){
						// вложенное объединение
						$self_value->unionArray($value);
					}else{
						// добавление
						$self_value->append($value);
					}
				}else{
					// Добавление или обновление значения
					$this->offsetSet($name, $value);
				}
			}else{
				// @todo Смена родителя?
				$this->append($value);
			}
		}
	}

	/**
	 * Проверка значения
	 * @param $name Ключ элемента
	 * @param \Engine\Rule $rule Правило проверки
	 * @param \Engine\Error | null $error Ошибки после проверки
	 * @return bool
	 */
	public function check($name, $rule = null, &$error = null){
		$this->get($name, $rule, $error);
		if ($error){
			return false;
		}
		return true;
	}

	/**
	 * Проверка всех значений
	 * @param \Engine\Rule | array $rule Правило проверки
	 * @param \Engine\Error | null $error Ошибки после проверки
	 * @return bool
	 */
	public function checkArray($rule = null, &$error = null){
		$this->getArray($rule, $error);
		return !$error->isExist();
	}

	/**
	 * Проверка значения на равенство указанному значению $need_value
	 * @param string $name Ключ проверяемого значения
	 * @param mixed $need_value Значение с которым выполняется сверка
	 * @param bool $strict Тип сравнения. Если true, то сравнение без приведедния типов (строгое)
	 * @return bool
	 */
	public function compare($name, $need_value, $strict = false){
		return (parent::offsetExists($name)) && ($strict?(parent::offsetGet($name) === $need_value) : (parent::offsetGet($name) == $need_value));
	}

	/**
	 * Смещение числовых ключей
	 * Числовие ключи уменьшаются на $shift. Для увеличения ключей $shift должен быть отрицательным
	 * @param int $shift Размер смещения
	 */
	public function shiftKeys($shift = 0){
		if ($shift){
			$list = array();
			foreach ((array)$this as $key => $value){
				if (is_numeric($key)){
					$list[-$shift + $key] = parent::offsetGet($key);
				}else{
					$list[$key] = parent::offsetGet($key);
				}
			}
			parent::exchangeArray($list);
		}
		$this->changed(true);
		$this->_filtered = false;
	}

	/**
	 * Признак, отфильтрованы значения или нет
	 * @return bool
	 */
	public function isFiltered(){
		return $this->_filtered;
	}

	/**
	 * Признак, изменялись значения или нет
	 * @return bool
	 */
	public function isChanged(){
		return $this->_changed;
	}

	/**
	 * Установка признака наличия изменений
	 * Если объект изменился, то изменяют все родители
	 * @param bool $changed
	 */
	public function changed($changed = true){
		$this->_changed = $changed;
	}

	/**
	 * Добавление элемента с указанным значением
	 * @example $v[] = $value;
	 * @param mixed $value
	 */
	public function append($value){
		parent::append($value);
		$this->changed(true);
		$this->_filtered = false;
	}

	/**
	 * Получение значения с применением правила по умолчанию
	 * @param mixed $name Ключ элемента
	 * @return mixed Отфильтрованное значение
	 */
	public function offsetGet($name){
		return $this->get($name);
	}

	/**
	 * Установка значения
	 * @param mixed $name Ключ элемента
	 * @param mixed $value Новое значения элемента
	 */
	public function offsetSet($name, $value){
		if (is_null($name) || !parent::offsetExists($name) || parent::offsetGet($name)!=$value){
			parent::offsetSet($name, $value);
//			if ($value instanceof Values){
//				$value->_parent = $this;
//			}
			$this->changed(true);
			$this->_filtered = false;
		}
	}

	/**
	 * Проверка, значение элемента пустое либо элемент отсутсвует
	 * @param string $name Ключ проверяемого элемента
     * @param bool $number Признак, проверять как число или строку. Если $number==true, то '0' будет считаться пустым значенеим
	 * @return bool
	 */
	public function offsetEmpty($name, $number = false){
		if (parent::offsetExists($name)){
			$value = parent::offsetGet($name);
			if (is_string($value)){
				if ($number) {
                    $value = trim($value);
                    return empty($value);
                }
                return trim($value) == '';
			}
			return (empty($value));
		}
		return true;
	}

	/**
	 * Удаление всех элементов
	 */
	public function offsetUnsetAll(){
		$this->exchangeArray(array());
		$this->changed(true);
		$this->_filtered = false;
	}

	/**
	 * Удаление элементов по именам
	 * Объявления аргументов в методе отсутствует, так как предполагается два варианта их указания:
	 * 1. Массивом, например deleteList(array('name1', 'name2'));
	 * 2. Любым количеством скалярных аргументов, например deleteList('name1', 'name2', 'name3');
	 */
	public function offsetUnsetList(){
		$arg = func_get_args();
		if (isset($arg[0]) && is_array($arg[0])) $arg = $arg[0];
		foreach ($arg as $name){
			parent::offsetUnset($name);
		}
		$this->changed(true);
		$this->_filtered = false;
	}

	/**
	 * Перегрузка метода получения элемента
	 * Всегда возвращется Values, даже если нет запрашиваемого элемента (Values будет пустым тогда)
	 * @example $v = $values->v1;
	 * @param string $name Ключ элемента
	 * @return array|\Engine\Values
	 */
	public function __get($name){
		if (parent::offsetExists($name)){
			$value = parent::offsetGet($name);
			if (is_array($value)){
				parent::offsetSet($name, $value = new Values($value, $this->_rule));
//				$value->_parent = $this;
			}
			if ($value instanceof Values){
				return $value;
			}else{
				// @todo Если объект будет изменяться, то нужно установить связь с $this
				$value = new Values($value, $this->_rule);
//				$value->_parent = $this;
//				$value->_name = $name;
				return $value;
			}
		}else{
			// Создание временного объекта. Счиатется временным, пока не изменится
			parent::offsetSet($name, $value = new Values(array(), $this->_rule));
//			$value->_parent = $this;
			return $value;
		}
	}

	/**
	 * Перегрузка установки значения элементу
	 * @example $values->v1 = "value";
	 * @param string $name Ключ элемента
	 * @param mixed $value Значение
	 */
	public function __set($name, $value){
		$this->offsetSet($name, $value);
	}

	/**
	 * Перегрузка функции isset() для проверки существоания элемента
	 * @example isset($values->v1);
	 * @param string $name Ключ элемента
	 * @return bool
	 */
	public function __isset($name){
		$this->offsetExists($name);
	}

	/**
	 * Перегрузка функции unsset() для удаления элемента
	 * @example unsset($values->v1);
	 * @param string $name Ключ элемента
	 * @return bool
	 */
	public function __unset($name){
		$this->offsetUnset($name);
	}

	/**
	 * Перегрузка обращения к элементу для получения его строкового значения.
	 * При обращении к значению как к свойству, значение помещается в объект класса Values, поэтому значение
	 * необходимо достать из объекта
	 * Значение фильтруется правилом по умолчанию
	 * @example
	 * echo $values->v1;
	 * $s = (string)$values->v1;
	 * @return string
	 */
	public function __toString(){
		if ($this->count()>0){
			reset($this);
			$name = key($this);
			return $this->get($name);
		}else{
			return '';
		}
	}

	/**
	 * Клонирование объекта
	 */
	public function __clone(){
		foreach ((array)$this as $key => $item){
			if (is_object($item)){
				$this->offsetSet($key, clone $item);
			}
		}
	}

	/**
	 * Вызов неопределённого метода
	 * @param string $name Имя метода
	 * @param array $args Аргументы
	 * @return null
	 */
	public function __call($name, $args){
		return null;
	}
}