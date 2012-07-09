<?php
/**
 * Значения, требующие проверки или фильтра.
 *
 * В объект помещается любое значение и правило по умолчанию на значение.
 * Из объекта значение возвращается отфильтрованным по указанному правилу.
 * Если значение является массивом, то можно обращаться к его элементам, но они будут возвращаться в виде объектов Values,
 * таким образом сохраняется полная изоляция опасных значений.
 *
 * @link http://boolive.ru/createcms/dangerous-data
 * @version	3.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Engine;

use ArrayAccess, IteratorAggregate, Countable, ArrayIterator,
	Engine\Rule, Engine\Error, Engine\Check;

class Values implements IteratorAggregate, ArrayAccess, Countable, ITrace{
	/** @var mixed|array Значение */
	protected $_value;
	/** @var \Engine\Rule Правило по умолчанию для значения */
	protected $_rule;
	/** @var bool Признак, отфильтрованы значения (true) или нет (false)? */
	protected $_filtered;
	/** @var array Объекты \Engine\Values для возвращения элементов при обращении к ним, если $this->_value массив*/
	private $_interfaces;
	/** @var \Engine\Values Родитель объекта для оповещения об изменениях значения. Используется при отложенном связывании */
	private $_maker;
	/** @var string Имя для элемента в родителе. Используется при отложенном связывании */
	private $_name;

	/**
	 * Конструктор
	 * @param null|mixed $value Значение
	 * @param null|\Engine\Rule $rule Правило проверки значений по умолчанию
	 */
	public function __construct($value = null, $rule = null){
		$this->_value = $value;
		$this->_rule = $rule;
		$this->_filtered = false;
	}

	/**
	 * Установка правила проверки по умолчанию
	 * Применяется в наследуемых классах для предопредления правила
	 * @return \Engine\Rule
	 */
	protected function defineRule(){
		// Одномерный ассоциативный массив строк
		$this->_rule = Rule::arrays(Rule::string(), true);
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
					$args = array(array(), null, false);
					foreach ($this->_rule->arrays as $arg){
						if (is_array($arg)){
							$args[0] = $arg;
						}else
						if ($arg instanceof Rule){
							$args[1] = $arg;
						}else
						if (is_bool($arg)){
							$args[2] = $arg;
						}
					}
					// Если элемент массив и правило рекурсивно, то отдаём всё правило
					if (isset($this->_value[$name]) && is_array($this->_value[$name]) && $args[2]){
						return $this->_rule;
					}
					// Выбор правила для элемента
					if (is_array($args[0]) && isset($args[0][$name])){
						// Правило на элемент
						return $args[0][$name];
					}
					// Правило по умолчанию, если есть
					return $args[1] instanceof Rule ? $args[1] : null;
				}
			}
			return null;
		}
		return $this->_rule;
	}

	/**
	 * Установка значения
	 * @param mixed $value
	 */
	public function set($value){
		$this->_value = $value;
		$this->_interfaces = array();
		$this->notFiltered();
	}

	/**
	 * Выбор значения с применением правила
	 * @param null $rule
	 * @param null $error
	 * @return array|mixed|null
	 */
	public function get($rule = null, &$error = null){
		// Если не указано правило и значение уже отфильтровано, то повторно фильтровать не нужно
		if (!$rule && $this->_filtered)	return $this->_value;
		// Если правило не указано, то берём по умолчанию
		if (!$rule) $rule = $this->getRule();
		// Если правило определено
		if ($rule instanceof Rule){
			return Check::Filter($this->_value, $rule, $error);
		}
		$error = new Error(array('Нет правила'), 'NO_RULE');
		return null;
	}

	/**
	 * Фильтр всех значений в соответствии с правилом по умолчанию.
	 * Текущее значение заменяется на отфильтрованное, если нет ошибок.
	 * Если ошибок нет, то возвращается true, иначе false.
	 * После успешного фильтра доступ к значениям будет выполняться без проверок, пока не произойдут какие-либо изменения.
	 * @param null $errors Ошибки после проверки
	 * @return bool
	 */
	public function filter(&$errors = null){
		$values = $this->get(null, $errors);
		if (!isset($errors)){
			$this->set($values);
			$this->_filtered = true;
		}else{
			$this->_filtered = false;
		}
		return $this->_filtered;
	}

	/**
	 * Проверка значения
	 * @param \Engine\Rule $rule Правило проверки
	 * @param \Engine\Error | null $error Ошибки после проверки
	 * @return bool
	 */
	public function check($rule = null, &$error = null){
		$this->get($rule, $error);
		return isset($error);
	}

	/**
	 * Удяляет все элементы, имена которых не указаны в аргументах этого метода
	 * Ключи элементов передаются в массиве или через запятую.
	 * Если текущее значение не массив, то оно будет заменено на пустой массив
	 * @example
	 * 1. choose(array('name1', 'name2'));
	 * 2. choose('name1', 'name2', 'name3');
	 * @return \Engine\Values Ссылка на себя
	 */
	public function choose(){
		if (!is_array($this->_value)) $this->_value = array();
		$arg = func_get_args();
		if (isset($arg[0]) && is_array($arg[0])) $arg = $arg[0];
		$list = array();
		foreach ($arg as $name){
			if (array_key_exists($name, $this->_value)) $list[$name] = $this->_value[$name];
		}
		$this->set($list);
		return $this;
	}

	/**
	 * Возвращает все значения в виде массива объектов Values
	 * Если текущее значение не массив, то оно будет возвращено в качестве нулевого элемента массива
	 * @return array Массив объектов Values
	 */
	public function getValues(){
		$v = is_array($this->_value)? $this->_value : array($this->_value);
		$r = array();
		foreach ($v as $name => $value){
			$r[$name] = new Values($value);
		}
		return $r;
	}

	/**
	 * Возвращает массив ключей
	 * @return array
	 */
	public function getKeys(){
		return array_keys((array)$this->_value);
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
		}
	}

	/**
	 * Смещение числовых ключей
	 * Числовие ключи уменьшаются на $shift. Для увеличения ключей $shift должен быть отрицательным
	 * @param int $shift Размер смещения
	 */
	public function shiftKeys($shift = 0){
		if (is_array($this->_value)){
			if ($shift){
				$list = array();
				foreach ((array)$this as $key => $value){
					if (is_numeric($key)){
						$list[-$shift + $key] = $this->_value[$key];
					}else{
						$list[$key] = $this->_value[$key];
					}
				}
				$this->set($list);
			}
		}
	}

	/**
	 * Установка признака наличия изменений
	 * Если объект изменился, то изменяют все родители
	 */
	public function notFiltered(){
		$this->_filtered = false;
		// Отложенное связывание с родителем
		if (isset($this->_maker)){
			if (isset($this->_name)){
				$v = &$this->_maker->_value;
				$v[$this->_name] = &$this->_value;
				unset($this->_name);
			}
			$this->_maker->notFiltered();
		}
	}

	/**
	 * Признак, отфильтрованы значения или нет
	 * @return bool
	 */
	public function isFiltered(){
		return $this->_filtered;
	}

	/**
	 * Количество элементов.
	 * Если значение не массив, то всегда возвращается 1
	 * @return int
	 */
	public function count(){
		return is_array($this->_value) ? count($this->_value) : 1;
	}

	/**
	 * Получение элемента массива в виде объекта Values
	 * Если значение не являются массивом, то оно будет заменено на пустой массив.
	 * Если элемента с указанным именем нет, то он будет создан со значением null
	 * @param mixed $name Ключ элемента
	 * @return \Engine\Values|mixed
	 */
	public function offsetGet($name){
		if (is_null($name)) return $this;
		if (!isset($this->_interfaces[$name])){
			// Создание объекта Values для запрашиваемого значения.
			// Объекту устанавливается правило в соответсвии с правилом данного объекта Values и запрашиваемого элемента
			$this->_interfaces[$name] = $interface = new Values(null, $this->getRule($name));
			$interface->_maker = $this;
			if (is_array($this->_value) && array_key_exists($name, $this->_value)){
				// Если элемент существует, то делаем ссылку на него из нового объекта Values
				$interface->_value = &$this->_value[$name];
			}else{
				// Если элемента нет, то фиксируем его название, чтобы при изменении значения
				// в новом Values связать его значение с ещё не сущесвтующим элементом в данном объекте Values.
				// Это необходимо сделать, чтобы новые элементы появлялись при явной их установке,
				// а не при обращении к ним, но оставляя возможность обращаться к несуществующим элементам.
				$interface->_value = null;
				$interface->_name = $name;
			}
		}
		return $this->_interfaces[$name];
	}

	/**
	 * Установка значения элементу массива
	 * Если текущее значение не является массивом, то оно будет заменено на пустой массив.
	 * @param mixed $name Ключ элемента
	 * @param mixed $value Новое значения элемента
	 */
	public function offsetSet($name, $value){
		if (!is_array($this->_value)) $this->_value = array();
		if (is_null($name)){
			$this->_value[] = $value;
		}else{
			$this->_value[$name] = $value;
			if (isset($this->_interfaces[$name])) unset($this->_interfaces[$name]);
		}
		$this->notFiltered();
	}

	/**
	 * Проверка существования элемента
	 * @param mixed $name
	 * @return bool
	 */
	public function offsetExists($name){
		return is_array($this->_value) && array_key_exists($name, $this->_value);
	}

	/**
	 * Проверка элемента, пустое у него значение или нет
	 * @param string $name Ключ проверяемого элемента
     * @param bool $number Признак, проверять как число или строку. Если $number==true, то '0' будет считаться пустым значенеим
	 * @return bool
	 */
	public function offsetEmpty($name, $number = false){
		if (is_array($this->_value) && $this->offsetExists($name)){
			if (is_string($this->_value[$name])){
				if ($number) {
                    $value = trim($this->_value[$name]);
                    return empty($value);
                }
                return trim($this->_value[$name]) == '';
			}
			return (empty($this->_value[$name]));
		}
		return true;
	}

	/**
	 * Удаление элемента
	 * @param mixed $name
	 */
	public function offsetUnset($name){
		if (is_array($this->_value) && $this->offsetExists($name)){
			$this->_interfaces[$name]->_maker = null;
			unset($this->_interfaces[$name]);
			unset($this->_value[$name]);
			$this->notFiltered();
		}
	}

	/**
	 * Удаление всех элементов.
	 * Обнуление значения.
	 */
	public function offsetUnsetAll(){
		foreach ($this->_interfaces as $i) $i->_maker = null;
		$this->_value = null;
		$this->_interfaces = array();
		$this->notFiltered();
	}

	/**
	 * Удаление элементов по именам
	 * Ключи передаются в массиве или через запятую.
	 * @example
	 * 1. offsetUnsetList(array('name1', 'name2'));
	 * 2. offsetUnsetList('name1', 'name2', 'name3');
	 */
	public function offsetUnsetList(){
		if (is_array($this->_value)){
			$arg = func_get_args();
			if (isset($arg[0]) && is_array($arg[0])) $arg = $arg[0];
			foreach ($arg as $name){
				$this->offsetUnset($name);
			}
		}
	}

	/**
	 * Итератор для циклов
	 * На каждый элемент массива (если текущее значение массив) создаётся объект Values.
	 * Возвращается итератор на массив объектов Values.
	 * @return \ArrayIterator|\Traversable
	 */
	public function getIterator(){
        return new ArrayIterator($this->getValues());
    }

	/**
	 * Перегрузка метода получения элемента как свойства
	 * Всегда возвращется Values, даже если нет запрашиваемого элемента (Values будет пустым тогда)
	 * @example $v = $values->v1;
	 * @param string $name Ключ элемента
	 * @return array|\Engine\Values
	 */
	public function __get($name){
		return $this->offsetGet($name);
	}

	/**
	 * Перегрузка установки значения элементу как свойства
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
		return is_array($this->_value) && isset($this->_value['values']);
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
	 * Отфильтрованное значение в виде строки
	 * Значение фильтруется правилом по умолчанию и конвертируется в строку
	 * @example
	 * echo $values->v1;
	 * $s = (string)$values->v1;
	 * @return string
	 */
	public function __toString(){
		return (string)$this->get();
	}

	/**
	 * Клонирование объекта
	 */
	public function __clone(){
		if (is_array($this->_value)){
			foreach ($this->_value as $key => $item){
				if (is_object($item)){
					$this->_value[$key] = clone $item;
				}
			}
		}else
		if (is_object($this->_value)){
			$this->_value = clone $this->_value;
		}
		$this->_interfaces = array();
		$this->_maker = null;
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

	/**
	 * Возвращает свойства объекта для трассировки
	 * @return array
	 */
	public function trace(){
		return array(
			'_value' => $this->_value,
			'_rule' => $this->_rule,
			'_filtered' => $this->_filtered,
			'_interfaces' => $this->_interfaces,
			'_maker' => $this->_maker,
			'_name' => $this->_name
		);
	}
}