<?php
/**
 * Значения, требующие проверки или фильтра
 *
 * @version	2.2
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
	/** @var \Engine\Values Родительский объект Values. Необходим для оповещения об изменениях */
	protected $_parent;
	/** @var Ключ объекта в родителе. Используется при отложенном связывании временных объектов после изменений */
	private $_name_in_parent;
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
		$this->_parent = null;
	}

	/**
	 * Установка правила проверки по умолчанию
	 * Применяется в наследуемых классах для предопредления правила
	 * @return \Engine\Rule
	 */
	protected function defineRule(){
		// Одномерный ассоциативный массив строк
		$this->_rule = Rule::ArrayList()->type(Rule::TYPE_STRING)->assoc(true);
	}

	/**
	 * Правило проверки по умолчанию
	 * Правило используется если в аргументах методов не указывается правило
	 * @param null $name Ключ элемента, для которого требуется правило. Если не указан, то возвращается общее правило
	 * @return \Engine\Rule
	 */
	public function getRule($name = null){
		if (!isset($this->_rule)) $this->defineRule();
		if (isset($name)){
			$rule = $this->_rule;
			if ($rule instanceof Rule){
				if ($rule->getKind()==Rule::KIND_ARRAY && $rule->getSubRule()){
					$rule = $rule->getSubRule();
				}
			}
			if (is_array($rule)){
				if (isset($rule[$name])){
					return $rule[$name];
				}else{
					return null;
				}
			}
			return $rule;
		}
		return $this->_rule;
	}

	/**
	 * Получение значения c применением правила проверки
	 * @param string $name Ключ элемента
	 * @param \Engine\Rule $rule Правило фильра/проверки значения
	 * @param \Engine\Error $error Ошибки после проверки
	 * @return mixed Отфильтрованное значение
	 */
	public function get($name, $rule = null, &$error = null){
		// Если не указано правило и значение уже отфильтровано, то повторно фильтровать не нужно
		if (!$rule && $this->_filtered && parent::offsetExists($name)){
			return parent::offsetGet($name);
		}
		// Результат фильтра
		$result = null;
		$used_filter = false;

		// Если не указано правило
		if (!$rule) $rule = $this->getRule($name);

		// Если правило на значение определено
		if ($rule instanceof Rule){
			// Требуется пустое значение
			if ($rule->getType() == Rule::TYPE_EMPTY){
				if (parent::offsetExists($name) && $this->offsetEmpty($name)){
					$used_filter = Rule::ERROR_EMPTY;
				}
			}else
			// Требуется отсутствие значения
			if ($rule->getType() == Rule::TYPE_NULL){
				if (parent::offsetExists($name)){
					if ($rule->getCanEmpty() && (is_null(parent::offsetGet($name)) || trim(parent::offsetGet($name))=='')){
						$result = null;
					}else
					if ($rule->isSetDefault()){
						$result = $rule->getDefault();
					}else{
						$used_filter = Rule::ERROR_NULL;
					}
				}
			}else
			if (parent::offsetExists($name)){
				$value = parent::offsetGet($name);
				if ($rule->getType() == Rule::TYPE_NO) return $value;
				// Если не требуется наличие элемента и элемент пуст
				if (!$rule->getExist() && is_null($value)){
					if ($rule->isSetDefault()){
						$result = $rule->getDefault();
					}
				}else{
					// Если значение - массив, то преобразовываем его в объект Values
					if (is_array($value)){
						parent::offsetSet($name, $value = new Values($value));
						$value->_parent = $this;
					}
					// Если правилом задана выборка массива значений
					if ($rule->getKind() != Rule::KIND_SINGLE && $value instanceof Values){
						if ($sub_rule = $rule->getSubRule()){
							$result = $value->getArray($sub_rule, $error);
						}else{
							$result = $value->getArray($rule, $error);
						}
					}else
					// Если указан тип Values и значение класса Values
					if ($rule->getType() == Rule::TYPE_VALUES && $value instanceof Values){
						$result = $value;
					}else{
						// Фильтруем и возращаем в соответствии с указанным типом
						$result = Check::Filter($value, $rule, $used_filter);
					}
				}
			}else
			// Должен существаться, но элемента нет
			if ($rule->getExist()){
				if ($rule->isSetDefault()){
					$result = $rule->getDefault();
				}else{
					// Элемент отсутствует
					$used_filter = Rule::ERROR_EXIST;
				}
			}
		}else{
			$rule = false;
			$used_filter = Rule::ERROR_NO_RULE;
		}
		// Филтр игнорируемых ошибок
		if ($used_filter && (!$rule || !in_array($used_filter, $rule->getIgnore()))){
			if ($rule && $rule->isSetDefault()) $result = $rule->getDefault();
			if ($error instanceof Error){
				$error->add($used_filter);
			}else{
				$error = new Error($used_filter);
			}
		}
		return $result;
	}

	/**
	 * Получение массива значений, отфильтрованных/проверенных заданным правилом.
	 * @param \Engine\Rule | array $rule Правило проверки. Если не указано, то используется правило по умолчанию
	 * @param \Engine\Error | null $errors Ошибки после проверки
	 * @return array Отфильтрованные значения
	 */
	public function getArray($rule = null, &$errors = null){
		// Контейнер для исключений
		if (!($errors instanceof Error)) $errors = new Error();
		// Если не указано правило и значение уже отфильтровано, то повторно фильтровать не нужно
		if (!$rule && $this->_filtered){
			return parent::getArrayCopy();
		}
		$result = array();
		// Если не указано правило
		if (!$rule) $rule = $this->getRule();

		if ($rule instanceof Rule){
			if ($rule->getType() == Rule::TYPE_NO && $rule->getAssoc()) return parent::getArrayCopy();
			if ($rule->getKind() != Rule::KIND_SINGLE){
				// Чтоб не было рекурсивной выборки и использовать этот же объект правиала для значений
				if ($rule->getKind() == Rule::KIND_ARRAY) $rule->kind(Rule::KIND_SINGLE);
				// Выборка всех элеменов
				$i = 0;
				$assoc = $rule->getAssoc();
				foreach ((array)$this as $key => $value){
					$e = null;
					$value = $this->get($key, $rule, $e);
					if (!$assoc) $key = $i++;
					if ($e) $errors->{$key} = $e;
					$result[$key] = $value;
				}
			}
		}else
		if (is_array($rule)){
			// Выборка указанных элементов
			foreach ($rule as $field_name => $field_rule){
				if (is_scalar($field_rule)){
					// Если указано имя элемента без правила
					$field_name = $field_rule;
					$field_rule = $this->getRule($field_name);
				}
				// Временный объект для ошибок
				$errors->add($field_name);
				// Если правило определено
				if (is_array($field_rule) || $field_rule instanceof Rule){
					if (parent::offsetExists($field_name)){
						// Правило на массив
						if (is_array($field_rule) || $field_rule->getKind() != Rule::KIND_SINGLE){
							$value = parent::offsetGet($field_name);
							// Продолжаем, если фильтруемый элемент является массивом
							if (is_array($value) || $value instanceof Values){
								if ($field_rule instanceof Rule && ($sub = $field_rule->getSubRule())){
									$field_rule = $sub;
								}
								$result[$field_name] = $this->{$field_name}->getArray($field_rule, $errors->{$field_name});
							}else{
								if ($field_rule instanceof Rule && $field_rule->getExist()){
									if ($field_rule->isSetDefault()){
										$result[$field_name] = $field_rule->getDefault();
									}else{
										$result[$field_name] = array();
									}
									$errors->{$field_name}->add(Rule::ERROR_TYPE);
								}
							}
						}else{
							$result[$field_name] = $this->get($field_name, $field_rule, $errors->{$field_name});
						}
					}else
					// Должен существать, но элемента нет
					if ($field_rule->getExist()){
						if ($field_rule->isSetDefault()){
							$result[$field_name] = $field_rule->getDefault();
						}else{
							// Элемент отсутствует
							$errors->{$field_name}->add(Rule::ERROR_EXIST);
						}
					}
				}else{
					$errors->{$field_name}->add(Rule::ERROR_NO_RULE);
				}
				// Если ошибок нет, то удаляем временный объект ошибки
				if (!$errors->{$field_name}->offsetExist()){
					$errors->delete($field_name);
				}
			}
		}
		return $result;
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
	 * @param mixed $list
	 * @return array
	 */
	public function exchangeArray($list){
		$this->changed(true);
		$this->_filtered = false;
		// Смена родителей для элментов $list класса Values
		foreach ($list as $value){
			if ($value instanceof Values) $value->_parent = $this;
		}
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
	 * @param \Engine\Error | null $errors Ошибки после проверки
	 * @return bool
	 */
	public function check($name, $rule = null, &$errors = null){
		$this->get($name, $rule, $e);
		if ($e){
			if (!$errors){
				$errors = $e;
			}else{
				$errors->{$name} = $e;
			}
			return false;
		}
		return true;
	}

	/**
	 * Проверка всех значений
	 * @param \Engine\Rule | array $rule Правило проверки
	 * @param \Engine\Error | null $errors Ошибки после проверки
	 * @return bool
	 */
	public function checkArray($rule = null, &$errors = null){
		$this->getArray($rule, $errors);
		return !$errors->isExist();
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
	 * Родительский объект Values
	 * @return Values|null
	 */
	public function parent(){
		return $this->_parent;
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
		if (!$this->_changed && $changed && !$this->_parent){
			if ($this->_name_in_parent){
				$this->_parent->offsetSet($this->_name_in_parent, $this);
				$this->_name_in_parent = null;
			}else{
				$this->_parent->changed($changed);
			}
		}
		$this->_changed = $changed;
	}

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
		if (!parent::offsetExists($name) || parent::offsetGet($name)!=$value){
			parent::offsetSet($name, $value);
			if ($value instanceof Values){
				$value->_parent = $this;
			}
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
				$value->_parent = $this;
			}
			if ($value instanceof Values){
				return $value;
			}else{
				// @todo Если объект будет изменяться, то нужно установить связь с $this
				$value = new Values($value, $this->_rule);
				$value->_parent = $this;
				$value->_name = $name;
				return $value;
			}
		}else{
			// Создание временного объекта. Счиатется временным, пока не изменится
			parent::offsetSet($name, $value = new Values(array(), $this->_rule));
			$value->_parent = $this;
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
		$this->_parent = null;
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
